# Auditoría y diseño — Motor de liquidaciones con ajustes (V1 simplificado)

**Fecha:** 2026-06-21  
**Revisión:** 2026-06-21 — decisiones V1 simplificadas aprobadas  
**Revisión Fase 2:** 2026-06-21 — multas independientes, aplicación opcional al pagar  
**Alcance:** Backend NightPOS — auditoría + diseño aprobado  
**Estado:** Fase 1 **implementada** — Fase 2 **implementada** — Fase 3 descuento manual **implementada** — Fase 4 pago auditable **implementada**

**Implementación Fase 1:** `SETTLEMENT_ADJUSTMENTS_ENGINE_IMPLEMENTATION_REPORT.md`

---

## Resumen ejecutivo

NightPOS liquida hoy **solo ingresos brutos** (`total_amount` = suma de ítems positivos). La lógica vive en `EloquentStaffSettlementRepository::generateForShift()`. No hay motor de ajustes, multas, descuentos manuales ni ticket al pagar.

**V1 simplificado (aprobado):** motor de ajustes con tres tipos únicos:

| Tipo | Cuándo | Alcance |
|------|--------|---------|
| `CLEANING_DEDUCTION` | Al generar/sync | Solo chicas — 100/10 (Fase 1 ✅) |
| `MANUAL_FINE` | Al **pagar** (cajera elige) | Multas independientes (Fase 2) |
| `MANUAL_DISCOUNT` | Settlement PENDING | ✅ Fase 3 implementada |

**Fuera de V1 (documentado para V1.1/V2):** garzón manillando, manillas multi-rol, descuentos automáticos por origen (admin 3%, garzón 5%), `income_origin`, reglas configurables avanzadas.

---

## Parte A — Auditoría del estado actual

### A.1 Flujo de generación

```
POST /api/v1/settlements/generate-current-shift
  → GenerateCurrentShiftSettlementsUseCase
  → SettlementShiftScopeResolver (my_cash_session | shift)
  → EloquentStaffSettlementRepository::generateForShift()
  → recalculateTotal()
```

**No existe** `SettlementGenerationService`.

### A.2 Fuentes de ingreso

| source_type | settlement_type | Beneficiario |
|-------------|-----------------|--------------|
| `WAITER_COMMISSION` | `WAITER` | `sales.waiter_user_id` |
| `GIRL_CONSUMPTION` | `GIRL` | `sale_items.girl_user_id` |
| `GIRL_BRACELET_ALLOCATION` | `GIRL` | `allocation.girl_user_id` |
| `GIRL_BRACELET` | `GIRL` | `bracelet.girl_user_id` |
| `GIRL_ROOM` | `GIRL` | `room.girl_user_id` |
| `GIRL_SHOW` | `GIRL` | `show.girl_user_id` |
| `CLEANING_ROOM` / `CLEANING_BASE` | `CLEANING` | `cleaning_user_id` |

### A.3 Pago actual

`MarkSettlementPaidUseCase` → egreso `EXPENSE` por `total_amount` (bruto) → `source_type = STAFF_SETTLEMENT`. Sin print job.

### A.4 Limpieza hoy vs regla V1

| Hoy | V1 |
|-----|-----|
| `CLEANING_BASE` paga a personal limpieza (default 30 Bs) | Sin cambio en settlement CLEANING |
| Pieza descuenta `cleaning_amount` del neto chica en `GIRL_ROOM` | **Además:** limpieza única −10 Bs si chica generó ≥100 Bs en el turno/caja |
| No hay regla 100/10 global | Nueva regla vía `CLEANING_DEDUCTION` |

### A.5 Idempotencia existente (preservar)

- `(sale_item_id, source_type)` / `(source_id, source_type)` — no duplica ingresos.
- Cortes parciales post-PAID — nuevo header PENDING.
- `cleaningBaseAlreadySettled` — solo para ingreso limpieza, **no** para deducción chica.

---

## Parte B — Decisiones finales V1

| # | Decisión | Valor |
|---|----------|-------|
| 1 | Limpieza única | Solo **chicas** (`settlement_type = GIRL`) |
| 2 | Umbral / monto | `< 100 Bs → 0` · `≥ 100 Bs → −10 Bs` |
| 3 | Alcance dedup limpieza | Una vez por **turno + caja + persona** |
| 4 | Multas | Manuales; chica, garzón, limpieza u otro personal |
| 5 | Descuentos automáticos por origen | **Eliminados de V1** |
| 6 | Descuento manual | Cajera elige **PERCENT** o **AMOUNT** con motivo obligatorio |
| 7 | Base descuento % | **Bruto − limpieza** (no bruto puro) |
| 8 | Orden de cálculo | Bruto → Limpieza → Descuento manual → Multas → Neto |
| 9 | Pago y caja | Usar **net_amount** |
| 10 | Ticket | Al pagar — desglose completo |
| 11 | Garzón manillando | **Fuera de V1** |
| 12 | Multi-rol allocations | **Fuera de V1** |

---

## Parte C — Motor de ajustes V1

### C.1 Arquitectura

```
SettlementGenerationService
  ├─ SettlementSourceCollector      ← lógica actual generateForShift (ingresos)
  ├─ SettlementAdjustmentEngine     ← limpieza, multas, descuento manual
  └─ SettlementTotalsCalculator     ← gross / adjustments / net

StaffSettlementRepository           ← persistencia + queries
```

Invocar `SettlementAdjustmentEngine::sync(settlementId)` después de agregar ítems de ingreso y al vincular multas/descuentos.

### C.2 Tabla `staff_settlement_adjustments`

Solo tres `adjustment_type` en V1:

| adjustment_type | Origen | amount |
|-----------------|--------|--------|
| `CLEANING_DEDUCTION` | Automático al sync (solo GIRL) | −10.00 o 0 |
| `MANUAL_FINE` | Multa PENDING vinculada | negativo |
| `MANUAL_DISCOUNT` | Acción cajera | negativo |

Campos:

```
id, tenant_id, branch_id, staff_settlement_id,
adjustment_type,
amount                    -- signed; deductions ≤ 0
discount_mode             -- NULL | PERCENT | AMOUNT  (solo MANUAL_DISCOUNT)
discount_value            -- 5.00 (% o Bs según mode)
calculation_base          -- base usada (auditoría)
notes                     -- motivo obligatorio en manual
staff_fine_id             -- FK nullable
dedup_key                 -- unique where settlement PENDING
created_by_user_id,
created_at, updated_at
```

**Eliminado de V1:** `income_origin`, `rule_code` automático, `PERCENTAGE_DISCOUNT` automático.

### C.3 Header `staff_settlements`

Columnas nuevas:

```
gross_amount        -- Σ ítems ingreso
adjustments_total   -- Σ adjustments (≤ 0)
net_amount          -- gross + adjustments_total
payment_method      -- snapshot al pagar
```

Migración: `gross_amount = net_amount = total_amount`, `adjustments_total = 0`.

`total_amount` puede mantenerse como alias de `net_amount` por compatibilidad API temporal.

---

## Parte D — Limpieza única (solo chicas)

### D.1 Regla

```
eligible_gross = gross_amount del settlement GIRL
                 (suma de todos los ítems ingreso de esa liquidación)

IF settlement_type != GIRL → no aplica

IF eligible_gross < 100.00 → cleaning = 0 (sin fila o fila 0)

IF eligible_gross >= 100.00 → cleaning = -10.00
```

Umbrales en `config/nightpos.php`:

```php
'girl_unique_cleaning' => [
    'threshold' => 100.00,
    'amount' => 10.00,
],
```

### D.2 Dedup — turno + caja + persona

```
dedup_key = "cleaning:{official_shift_id}:{cash_session_id}:{staff_user_id}"
```

Buscar ajuste `CLEANING_DEDUCTION` con esa clave en **cualquier** settlement del mismo turno/caja/persona (PENDING o PAID).

- Si ya existe → **no volver a cobrar** (corte parcial #2, regeneración).
- Si gross baja de 100 en un corte nuevo pero ya se cobró en corte PAID anterior → **no revertir** (limpieza ya consumida).

### D.3 Sync automático

Mientras settlement **PENDING**: recalcular `CLEANING_DEDUCTION` en cada generate/sync.  
Si **PAID**: ajustes congelados — inmutables.

---

## Parte E — Multas (`staff_fines`) — diseño Fase 2 revisado

### E.0 Principio de responsabilidad separada

| Ajuste | Cuándo | Quién decide |
|--------|--------|--------------|
| Limpieza | Al **generar/sync** settlement PENDING | Automático (Fase 1 ✅) |
| Multas | Al **pagar** settlement PENDING | Cajera elige cuáles aplicar |
| Descuento manual | Fase 3 — al editar settlement PENDING | Cajera (no implementar aún) |

**Las multas NO son parte de la generación de liquidaciones.** Pueden existir días/horas antes de que exista un settlement PENDING para esa persona.

### E.1 Entidad independiente: `staff_fines`

Representa una **multa pendiente de cobro**, no un renglón de liquidación.

```sql
staff_fines
  id
  tenant_id
  branch_id
  official_shift_id          -- turno operativo
  cash_session_id            -- nullable; caja donde se registró
  staff_user_id
  staff_role                 -- snapshot al crear (GIRL | WAITER | CLEANING | …)
  amount                     -- positivo en tabla
  reason                     -- obligatorio (ej. "Vaso roto")
  notes                      -- nullable
  status                     -- PENDING | APPLIED | CANCELLED
  created_by_user_id
  applied_settlement_id      -- nullable; se llena solo al pagar
  applied_at                 -- nullable
  applied_by_user_id         -- nullable; cajera que pagó
  cancelled_at               -- nullable
  cancelled_by_user_id       -- nullable
  cancel_reason              -- nullable; obligatorio al cancelar
  created_at, updated_at
```

**Sin FK a liquidación al crear.** La relación `applied_settlement_id` se establece **solo al confirmar pago** con esa multa marcada.

**Reglas:**

- `APPLIED` → inmutable; nunca borrar.
- `CANCELLED` solo desde `PENDING`; requiere `cancel_reason` + `cancelled_by`.
- Multa puede crearse **sin** settlement existente para esa persona.
- Scope al listar/aplicar: mismo `staff_user_id` + `official_shift_id` (+ scope caja según rol cajera).

### E.2 Al generar liquidaciones — NO tocar multas

`generateForShift()` y `SettlementTotalsCalculator` (Fase 1) **solo**:

1. Recolectan ítems de ingreso → `gross_amount`
2. Sync `CLEANING_DEDUCTION` (solo GIRL)
3. `net_amount = gross + adjustments_total` (hoy solo limpieza)

**No** buscar multas PENDING.  
**No** crear `MANUAL_FINE` en adjustments.  
**No** cambiar status de multas.

### E.3 Al pagar — selección opcional por cajera

Flujo en `MarkSettlementPaidUseCase` (Fase 2):

```
1. GET pay-preview (o payload en modal) lista multas PENDING de staff_user_id + turno
2. Cajera marca/desmarca cada multa (checkbox ☑ Aplicar)
3. Frontend recalcula neto en tiempo real
4. POST mark-paid { payment_method, applied_fine_ids: [1, 3], notes? }
5. Backend en transacción:
   a. Validar settlement PENDING + permisos + caja abierta
   b. Validar applied_fine_ids ⊆ multas PENDING elegibles
   c. Por cada multa seleccionada:
      - Crear staff_settlement_adjustments MANUAL_FINE (dedup fine:{id})
      - Marcar fine → APPLIED (applied_settlement_id, applied_at, applied_by)
   d. Recalcular net_amount incluyendo multas aplicadas
   e. Registrar EXPENSE por net_amount final
   f. Marcar settlement PAID
```

Multas **no marcadas** permanecen `PENDING` para un pago futuro.

### E.4 Cálculo de neto al pagar (Fase 2, sin descuento manual)

```
net_preview = gross_amount
            + adjustments_total_limpieza    -- ya en settlement (≤ 0)
            - Σ(amount de multas seleccionadas)
```

Ejemplo:

```
Bruto:              300.00
Limpieza:           -10.00
Multas aplicadas:   -50.00   (Vaso 30 + Tarde 20)
Neto a pagar:       240.00
```

Orden definitivo V1 completo (cuando existan todas las fases):

1. Bruto  
2. Limpieza (automática, ya en settlement)  
3. Descuento manual (Fase 3, opcional)  
4. Multas seleccionadas (Fase 2, opcional al pagar)  
5. Neto  

### E.5 Persistencia en `staff_settlement_adjustments`

Los ajustes `MANUAL_FINE` se **crean al confirmar pago**, no al generar:

| Campo | Valor |
|-------|-------|
| `adjustment_type` | `MANUAL_FINE` |
| `staff_fine_id` | FK |
| `amount` | negativo |
| `dedup_key` | `fine:{fine_id}` |
| `notes` | copia de `reason` |

Esto congela el desglose en el ticket y en settlement PAID.

### E.6 API Fase 2

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/staff-fines` | Crear multa PENDING (sin settlement) |
| GET | `/staff-fines` | Listar por turno/persona/status |
| POST | `/staff-fines/{id}/cancel` | PENDING → CANCELLED |
| GET | `/settlements/{id}/pay-preview` | Bruto, limpieza, multas PENDING, neto simulado |
| POST | `/settlements/{id}/mark-paid` | Body ampliado: `applied_fine_ids: int[]` |

**Pay-preview** acepta query `applied_fine_ids[]` para simular neto sin persistir.

Permisos:

- `settlements.fines.manage` — crear/cancelar multas
- `settlements.pay` — pagar con selección de multas

### E.7 Trazabilidad

Cada multa `APPLIED` registra:

- `applied_settlement_id`
- `applied_at`
- `applied_by_user_id`

Ajuste `MANUAL_FINE` en settlement PAID = snapshot auditable.  
Nunca DELETE; cancelación solo en `PENDING`.

### E.8 Tests Fase 2 (obligatorios)

| # | Caso |
|---|------|
| 1 | Multa creada **antes** de generar liquidación → existe PENDING |
| 2 | Generar liquidación no cambia status de multas |
| 3 | Multa no seleccionada al pagar → sigue PENDING |
| 4 | Multa seleccionada → APPLIED + adjustment + settlement link |
| 5 | Varias multas parciales (aplicar 1 de 2) |
| 6 | Cancelar multa PENDING → CANCELLED con motivo |
| 7 | Pay-preview recalcula neto al marcar/desmarcar |
| 8 | Ticket (Fase 4) muestra solo multas aplicadas |
| 9 | No romper limpieza única, cortes parciales, scope caja, SSE |

Archivo sugerido: `SettlementFinesPhase2Test.php`

---

## Parte E-bis — Descuento manual (Fase 3 — NO implementar aún)

### F.1 Fuera de V1

- Descuento automático administración 3%
- Descuento automático garzón 5%
- `income_origin` obligatorio
- Reglas configurables por origen
- Campo `settlement_discount_percent` en perfil

### F.2 En V1 — acción de cajera

**Un descuento manual por liquidación PENDING** (V1: máximo uno activo; si necesita otro, anular el PENDING primero).

| discount_mode | discount_value | Cálculo |
|---------------|----------------|---------|
| `PERCENT` | 5 | `round((gross - cleaning) * 5 / 100, 2)` |
| `AMOUNT` | 20 | `min(20, saldo_disponible)` |

Donde:

```
saldo_disponible = gross + cleaning_deduction   -- cleaning ya es negativo
                 = gross - |cleaning|
```

**Ejemplo PERCENT:**

```
Bruto:           300.00
Limpieza:        -10.00
Base descuento:  290.00
Descuento 5%:    -14.50
Multa:           -50.00
Neto:            225.50
```

**Ejemplo del brief operativo (5% sobre 300):** si la cajera ingresa 5% y la base es bruto−limpieza, el neto con multa 50 es **225.50**, no 225. Documentar en UI la base real.

### F.3 Validaciones

| Regla | Error |
|-------|-------|
| Settlement PAID | No crear/modificar descuento |
| Valor ≤ 0 | Rechazar |
| PERCENT > 100 | Rechazar |
| AMOUNT > saldo_disponible | Rechazar |
| Motivo/nota vacío | Rechazar |
| Descuento resultante > saldo | Rechazar |

Corrección post-pago: no editar; requiere flujo V1.1 (reversión o ajuste contable).

### F.4 API V1

| Método | Ruta | Body |
|--------|------|------|
| POST | `/settlements/{id}/manual-discount` | `{ mode: PERCENT\|AMOUNT, value, notes }` |
| DELETE | `/settlements/{id}/manual-discount` | Solo si PENDING |

Permiso: `settlements.discount.manage` (o incluir en `settlements.pay`).

---

## Parte G — Orden de cálculo por fase

### G.1 Fase 1 (implementada)

| Paso | Operación |
|------|-----------|
| 1 | GROSS — Σ ítems ingreso |
| 2 | Limpieza — `CLEANING_DEDUCTION` automática (solo GIRL) |
| 3 | NET — `gross + adjustments_total` |

### G.2 Fase 2 (al pagar, multas opcionales)

Settlement PENDING mantiene neto **sin multas** hasta el pago.

```
net_pago = gross + cleaning_adjustments - Σ(multas_seleccionadas)
```

Multas no seleccionadas **no** afectan `net_amount` del settlement hasta que se paguen en otro corte.

### G.3 Fase 3 (futuro — descuento manual)

Entre limpieza y multas:

```
net = gross + cleaning + manual_discount - multas_seleccionadas
```

---

## Parte H — Dedup y regeneración

| Artefacto | dedup_key |
|-----------|-----------|
| Ingresos | Sin cambio — keys actuales |
| Limpieza chica | `cleaning:{shift}:{cash_session}:{staff_user_id}` |
| Descuento manual | `discount:manual:{settlement_id}` — uno por settlement |
| Multa | `fine:{fine_id}` — creado **al pagar**, no al generar |
| Ticket pago | `settlement_payment:{settlement_id}:v1` |

Al regenerar con settlement PENDING: `syncAdjustments()` upsert por dedup_key.  
Corte parcial post-PAID: limpieza ya dedup → skip; multas nuevas PENDING → aplican al nuevo corte.

---

## Parte I — Pago, caja e impresión

### I.1 MarkSettlementPaidUseCase

- Validar `net_amount > 0` (o permitir 0 con confirmación).
- `addMovement(EXPENSE, amount = net_amount)`.
- Marcar multas vinculadas → `APPLIED`.
- Congelar todos los adjustments.
- Encolar print job.

### I.2 Ticket `SETTLEMENT_PAYMENT`

`CreateSettlementPaymentPrintJobUseCase` + `PrintTicketContentBuilder::buildSettlementPayment()`:

```
LIQUIDACIÓN PAGADA
Persona / Rol / Turno / Caja
Bruto
Limpieza
Descuento manual
Multas
Neto pagado
Método / Pagado por / Fecha
Powered by Ribersoft · WhatsApp 67369293
```

Idempotencia print: no duplicar en retry mark-paid; reprint explícito.

---

## Parte J — Plan de fases V1

| Fase | Entrega |
|------|---------|
| **1** | Motor ajustes + `gross_amount` + `CLEANING_DEDUCTION` + `net_amount` + tests limpieza |
| **2** | `staff_fines` + API + `MANUAL_FINE` + tests multas | **✅ Backend** |
| **3** | Descuento manual API + `MANUAL_DISCOUNT` + validaciones + tests |
| **4** | Pago por `net_amount` + ticket settlement payment + tests pago/print |
| **5** | Frontend (ver doc frontend) |

---

## Parte K — Tests obligatorios V1

| # | Caso |
|---|------|
| 1 | Chica 80 Bs bruto → sin limpieza |
| 2 | Chica 100 Bs bruto → limpieza −10 |
| 3 | Regenerar no duplica limpieza |
| 4 | Corte parcial no vuelve a cobrar limpieza |
| 5 | Multa descuenta del neto |
| 6 | Descuento % aplica sobre bruto − limpieza |
| 7 | Descuento monto fijo descuenta correctamente |
| 8 | No permite descuento mayor al saldo |
| 9 | Liquidación PAID no permite modificar ajustes |
| 10 | Pago usa net_amount en cash movement |
| 11 | Ticket muestra bruto, limpieza, descuento, multa y neto |

Archivo sugerido: `tests/Feature/Api/V1/SettlementAdjustmentsEngineTest.php`

---

## Parte L — V1.1 / V2 (documentado, no implementar)

| Feature | Versión |
|---------|---------|
| Garzón manillando | V1.1 |
| Manillas multi-rol (`recipient_user_id`) | V1.1 |
| Descuentos automáticos por origen (admin 3%, garzón 5%) | V1.1 |
| `income_origin` en ítems | V1.1 |
| Reglas configurables en admin UI | V1.1 |
| Reversión settlement PAID | V2 |
| Unificar WAITER+GIRL en un solo settlement | V2 |

---

## Referencias

| Tema | Archivo |
|------|---------|
| Generación actual | `EloquentStaffSettlementRepository.php` |
| Pago | `MarkSettlementPaidUseCase.php` |
| Cortes parciales | `PARTIAL_SETTLEMENTS_IMPLEMENTATION_REPORT.md` |
| Caja | `CASH_MOVEMENT_FROM_SETTLEMENTS_REPORT.md` |
| Print pipeline | `CreateCashClosePrintJobUseCase.php`, `PrintTicketContentBuilder.php` |
| Frontend diseño | `frontend/SETTLEMENT_ADJUSTMENTS_ENGINE_AUDIT.md` |
| Mapa V1 | `NIGHTPOS_V1_DEVELOPMENT_MAP.md` |

---

**Próximo paso:** implementar **Fase 3 backend** (descuento manual).
