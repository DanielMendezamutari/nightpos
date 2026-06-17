# Auditoría — Liquidaciones parciales en el mismo turno

**Fecha:** 2026-06-16  
**Alcance:** Análisis de código y esquema (auditoría original).  
**Implementación:** Completada — ver `PARTIAL_SETTLEMENTS_IMPLEMENTATION_REPORT.md`  
**Caso operativo:** María genera liquidación, cobra, se va, vuelve al mismo turno con actividad nueva → no aparece nueva liquidación (o aparece incompleta). **Corregido.**

---

## Resumen ejecutivo

El sistema **sí tiene** un mecanismo de deduplicación por fuente (`staff_settlement_items`), pero **está bloqueado** por dos decisiones de diseño incompatibles con liquidaciones incrementales:

1. **Un solo header por persona/turno/tipo** — constraint único `(official_shift_id, staff_user_id, settlement_type)` y `ensureSettlement()` que siempre reutiliza ese registro.
2. **Solo se agregan ítems a liquidaciones PENDING** — `canAddItemsToSettlement()` rechaza liquidaciones `PAID`.

Resultado: después de pagar a María, la actividad nueva **no genera ítems ni una segunda liquidación**. Las fuentes quedan **huérfanas** (sin `settlement_item`), aunque el endpoint de fuentes pendientes las detecta correctamente.

**Hipótesis principal del usuario (A):** Parcialmente confirmada — no descarta fuentes por “ya existe settlement”, pero **sí impide crear/agregar** porque reutiliza el settlement PAID existente.

---

## 1. Cómo funciona hoy

### Flujo de generación

```
POST /api/v1/settlements/generate-current-shift
  → GenerateCurrentShiftSettlementsUseCase
  → EnsureOperationalShiftUseCase (asegura turno operativo)
  → EloquentStaffSettlementRepository::generateForShift(tenant, branch, official_shift_id)
```

`generateForShift()` recorre todas las fuentes del turno:

| Origen | Tabla | Filtro principal |
|--------|-------|------------------|
| Comisiones garzón | `sale_items` + `sales` | `sales.official_shift_id`, `waiter_commission_amount_snapshot > 0` |
| Consumos chica | `sale_items` + `sales` | `sale_mode = CON_ACOMPANANTE`, `girl_amount_snapshot > 0` |
| Manillas | `bracelets` | `official_shift_id` |
| Piezas | `room_services` | `official_shift_id`, `status = FINISHED` |
| Shows | `shows` | `official_shift_id` |
| Limpieza por pieza | `cleaning_tasks` | `official_shift_id`, `status = DONE` |
| Base limpieza | perfil staff | Una vez por usuario con tareas en el turno |

Por cada fuente elegible:

1. Verifica si **ya existe ítem** (`saleItemAlreadySettled` o `sourceAlreadySettled`).
2. Obtiene o crea **header** con `ensureSettlement(shift, staff_user_id, settlement_type)`.
3. Si el header está `PENDING`, crea `staff_settlement_item` y recalcula total.

### Flujo de pago

```
POST /api/v1/settlements/{id}/mark-paid
  → markPaid() cambia status a PAID, setea paid_at / paid_by_user_id
  → RegisterCashMovement (egreso por método de pago)
```

El pago es **atómico a nivel de header**: se paga el settlement completo, no ítems individuales.

### Escenario que SÍ funciona hoy (liquidación parcial sin pago intermedio)

```
20:00 — Actividad A → Generar → Settlement #1 PENDING [101,102,103,50]
22:00 — Actividad B → Generar → mismos ítems en Settlement #1 PENDING [101…50,104,105]
23:00 — Pagar Settlement #1 → PAID con todos los ítems
```

Mientras el settlement siga `PENDING`, las regeneraciones **acumulan** ítems nuevos en el mismo header.

### Escenario que FALLA (caso María)

```
20:00 — Actividad A → Generar → Settlement #1 PENDING [101,102,103,50]
20:30 — Pagar Settlement #1 → PAID
22:30 — Actividad B (104,105) → Generar →
         • sourceAlreadySettled(104) = false ✓
         • ensureSettlement() → devuelve Settlement #1 (PAID)
         • canAddItemsToSettlement(#1) = false ✗
         • ítems 104,105 NO se crean
```

Test que documenta este comportamiento como **intencional**:

- `SettlementsPhase16Test.php` → `does not modify paid settlement on regenerate`
- Tras pagar y registrar manilla nueva, el settlement PAID no cambia y `created_items = 0`.

---

## 2. Cómo identifica fuentes liquidadas

### Mecanismo central: `staff_settlement_items`

**No existe** `liquidated_at`, `settled_at`, `source_settled` ni flags equivalentes en tablas origen.

| Fuente | Tabla origen | ¿Campo liquidado en origen? | Mecanismo de “ya liquidada” |
|--------|--------------|----------------------------|-----------------------------|
| Consumo chica | `sale_items` | No | `staff_settlement_items` WHERE `sale_item_id` + `source_type = GIRL_CONSUMPTION` (unique DB) |
| Comisión garzón | `sale_items` | No | `staff_settlement_items` WHERE `sale_item_id` + `source_type = WAITER_COMMISSION` (unique DB) |
| Manilla | `bracelets` | No | `staff_settlement_items` WHERE `source_id` + `source_type = GIRL_BRACELET` (unique DB) |
| Pieza | `room_services` | No | `staff_settlement_items` WHERE `source_id` + `source_type = GIRL_ROOM` (unique DB) |
| Show | `shows` | No | `staff_settlement_items` WHERE `source_id` + `source_type = GIRL_SHOW` (unique DB) |
| Limpieza pieza | `cleaning_tasks` | No | `staff_settlement_items` WHERE `source_id` + `source_type = CLEANING_ROOM` (unique DB) |
| Base limpieza | — | No | `staff_settlement_items` WHERE `source_id = official_shift_id` + `source_type = CLEANING_BASE` + check por settlement |

### Código de deduplicación

```php
// sale_items (consumo / comisión)
saleItemAlreadySettled($saleItemId, $sourceType)
  → EXISTS staff_settlement_items WHERE sale_item_id AND source_type

// bracelets, room_services, shows, cleaning_tasks
sourceAlreadySettled($sourceId, $sourceType)
  → EXISTS staff_settlement_items WHERE source_id AND source_type
```

### Constraints de base de datos

**`staff_settlements`:**
```sql
UNIQUE (official_shift_id, staff_user_id, settlement_type)
```
→ Máximo **1 liquidación header** por María + turno + GIRL.

**`staff_settlement_items`:**
```sql
UNIQUE (sale_item_id, source_type)      -- ventas
UNIQUE (source_id, source_type)           -- manillas, piezas, shows, limpieza
```

Estos uniques **protegen contra duplicación de fuentes** y son correctos para el modelo incremental.

### Estado del header vs estado de la fuente

| Concepto | Dónde vive | Valores |
|----------|-----------|---------|
| Fuente incluida en alguna liquidación | `staff_settlement_items` | Presencia de fila |
| Liquidación pagada al personal | `staff_settlements.status` | `PENDING` / `PAID` |

**Importante:** Una fuente puede estar en un ítem de un settlement PAID (correctamente pagada) o **no tener ítem** (nunca liquidada). No hay estado intermedio en la fuente origen.

---

## 3. Por qué falla el caso descrito

### Causa raíz (confirmada)

Combinación de tres piezas en `EloquentStaffSettlementRepository`:

**A) `ensureSettlement()` — reutiliza cualquier header existente**

```php
$existing = StaffSettlementModel::query()
    ->where('official_shift_id', $officialShiftId)
    ->where('staff_user_id', $staffUserId)
    ->where('settlement_type', $settlementType)
    ->first();  // ← no filtra por status PENDING
```

**B) `canAddItemsToSettlement()` — solo PENDING**

```php
return StaffSettlementModel::query()
    ->where('id', $settlementId)
    ->where('status', 'PENDING')
    ->exists();
```

**C) Unique constraint** — impide crear Settlement #2 para la misma María/turno/GIRL aunque se cambiara la lógica de creación.

### Validación de hipótesis del usuario

| Hipótesis | Veredicto | Evidencia |
|-----------|-----------|-----------|
| **A** — “Si ya existe settlement para chica en turno, descarta todo” | **Parcial** | No descarta por header; descarta **silenciosamente** al no poder agregar ítems al header PAID |
| **B** — Fuentes sin estado liquidado | **Confirmada como diseño** | Correcto usar `settlement_items`; el problema no es ausencia de flag sino bloqueo post-pago |
| **C** — `generated_at` / `last_settlement_at` incorrectos | **Rechazada** | No existen en el codebase |
| **D** — Items consumieron todas las fuentes y nuevas no se detectan | **Rechazada** | Nuevas fuentes pasan `sourceAlreadySettled = false`; fallan después en `canAddItemsToSettlement` |

### Síntomas en UI

| Pantalla | Comportamiento |
|----------|----------------|
| Liquidaciones (girls/waiters/cleaning) | María aparece como **PAID**; no hay fila PENDING nueva |
| `GET /settlements/pending-sources` | `unpaid_*_count` **sí sube** (usa `sourceAlreadySettled`, no status del header) |
| App móvil chica (`GetGirlShiftEarningsUseCase`) | Puede mostrar **pendiente** por fuentes sin ítem, pero **sin liquidación pagable** en caja |
| Regenerar liquidaciones | `created_items: 0`, `settlements_touched: 0` — silencioso |

### Caso especial: base de limpieza (`CLEANING_BASE`)

- `source_id = official_shift_id` (ID del turno, no del usuario).
- Unique `(source_id, source_type)` → **solo una base por turno en toda la sucursal**, no por persona.
- Tras pagar settlement de limpieza, nuevas piezas limpiadas quedan huérfanas igual que el caso María.
- Si el settlement PENDING nunca se pagó, la base ya fue agregada y no se repite (`settlementHasItemType`).

---

## 4. Riesgo de duplicación

### Con el diseño actual (post-pago)

**Riesgo bajo de doble pago por la misma fuente** gracias a:

- `sourceAlreadySettled()` / `saleItemAlreadySettled()` antes de crear ítem.
- Uniques en `staff_settlement_items`.

Si se “arregla” solo quitando `canAddItemsToSettlement` **sin** permitir múltiples headers, se intentarían agregar ítems a settlement PAID (requeriría cambiar esa guarda de todas formas).

### Con el modelo correcto (múltiples settlements)

**Riesgo controlable** si se mantiene:

- Deduplicación por `(source_id, source_type)` y `(sale_item_id, source_type)`.
- Nunca crear ítem si ya existe en **cualquier** settlement del tenant (comportamiento actual de `sourceAlreadySettled`).

**Riesgo a vigilar:** `CLEANING_BASE` con `source_id = shift_id` impediría base en segundo corte de limpieza; habría que usar `source_id = cleaning_user_id` o `(shift_id, staff_user_id)` compuesto.

---

## 5. Riesgo de pérdida de comisión

### Severidad: ALTA — bug activo en producción

| Escenario | Consecuencia |
|-----------|--------------|
| Chica cobra y vuelve (caso María) | Consumos/manillas/piezas nuevas **nunca entran** a liquidación |
| Garzón cobra comisión parcial y sigue vendiendo | Mismas ventas nuevas **huérfanas** |
| Limpieza cobra y limpia más piezas | Tareas nuevas **sin settlement_item** |
| Cajera cierra caja / turno | Puede cerrar **sin bloqueo** por fuentes huérfanas |

### Brecha de cierre

`CashSessionCloseCheckBuilder` y cierre de turno verifican:

- `pending_settlements` (headers PENDING)
- `settlements_generated > 0`

**No verifican** fuentes del turno sin `staff_settlement_item`. Un turno puede cerrarse con comisiones impagas invisiblemente.

### Inconsistencia operativa

`GetSettlementPendingSourcesUseCase` y `GetGirlShiftEarningsUseCase` **sí detectan** fuentes sin liquidar, pero el flujo de generación **no las materializa** tras un pago previo → la cajera ve contadores pero no puede pagar.

---

## 6. Modelo correcto

### Principio

> **1 turno ≠ 1 liquidación.**  
> **1 fuente = máximo 1 settlement_item en toda la historia.**  
> **1 persona puede tener N liquidaciones por turno** (cortes incrementales).

### Diagrama objetivo

```
Turno N — María (GIRL)

Fuentes: 101, 102, 103, 50
  → Generar → Settlement #1 PENDING [101,102,103,50]
  → Pagar   → Settlement #1 PAID

Fuentes: 104, 105 (nuevas)
  → Generar → Settlement #2 PENDING [104,105]   ← nuevo header
  → Pagar   → Settlement #2 PAID
```

### Registro de fuente liquidada (sin cambiar tablas origen)

Mantener **`staff_settlement_items` como única fuente de verdad**:

```
¿Fuente X liquidada?
  EXISTS (
    SELECT 1 FROM staff_settlement_items
    WHERE source_type = :type
      AND (source_id = :id OR sale_item_id = :id)
  )
```

El header (`staff_settlements`) es solo **agrupación contable para un corte de pago**, no identificador de exclusividad por turno.

### Cambios conceptuales requeridos

| Componente | Hoy | Correcto |
|------------|-----|----------|
| Header por persona/turno | Único (DB + código) | **Múltiples**; buscar PENDING o crear nuevo |
| `ensureSettlement()` | Primer match sin filtrar status | `WHERE status = 'PENDING'` o **siempre crear nuevo** si el existente está PAID |
| `canAddItemsToSettlement()` | Bloquea PAID | Mantener: solo agregar a PENDING |
| Pago | Todo el header | OK para cortes; cada corte es un header |
| `CLEANING_BASE` | `source_id = shift_id` | `source_id = staff_user_id` o clave compuesta por turno+usuario |

### Nombre sugerido para el patrón

**SettlementItemSourceReference** — ya implementado parcialmente; falta alinear el lifecycle del header.

No hace falta `SettlementSourceTracker` separado si `staff_settlement_items` + uniques se mantienen.

---

## 7. Plan de corrección (sin implementar)

### Fase 1 — Esquema

1. **Eliminar** unique `staff_settlements_shift_staff_type_unique`.
2. **Agregar** índice no único `(official_shift_id, staff_user_id, settlement_type, status)` para consultas.
3. Opcional: `settlement_batch` o `generated_at` en header para ordenar cortes (no sustituye deduplicación por ítem).
4. Revisar unique de `CLEANING_BASE`: cambiar `source_id` a identificador por usuario+turno.

### Fase 2 — Repositorio

1. Refactor `ensureSettlement()`:
   ```text
   Buscar settlement PENDING para (shift, staff, type)
   Si existe → reutilizar
   Si no existe (o solo hay PAID) → crear nuevo PENDING
   ```
2. Mantener `canAddItemsToSettlement()` (solo PENDING).
3. Mantener `sourceAlreadySettled()` / `saleItemAlreadySettled()` sin cambios.
4. Considerar respuesta explícita cuando hay fuentes huérfanas: `orphaned_sources_count` en generate.

### Fase 3 — Tests

1. **Nuevo:** `generates second settlement for same girl after first is paid`.
2. **Nuevo:** garzón — comisión parcial, ventas nuevas, segundo settlement.
3. **Nuevo:** limpieza — múltiples cortes con base y piezas.
4. **Actualizar:** `does not modify paid settlement on regenerate` → debe crear **nuevo** settlement, no modificar el PAID.
5. **Regresión:** `does not duplicate settlement items on second generate` (mismo settlement PENDING).

### Fase 4 — Cierre operativo

1. `CashSessionCloseCheckBuilder`: blocker/warning si `unpaid_*_count > 0` desde pending-sources lógica.
2. Cierre de turno: igual verificación de fuentes huérfanas.
3. UI: banner cuando pending-sources > 0 pero no hay settlement PENDING para esa persona.

### Fase 5 — Frontend

1. Listados: mostrar **múltiples filas** por persona (PAID + PENDING).
2. Historial: agrupar por persona con cortes numerados.
3. KPIs: `total_pending` suma todos los headers PENDING; `total_paid` suma PAID.

---

## 8. Impacto en reportes

| Reporte | Impacto | Notas |
|---------|---------|-------|
| `getSettlementsReport` | **Positivo** | Pasará de 1 fila/persona/turno a N filas; totales siguen siendo suma correcta |
| Dashboard KPIs (`getBranchOverview`) | **Neutro** | Ya suma `settlements.paid` / `pending` por status; múltiples headers no distorsionan |
| `getCurrentShiftOverview` / summary | **Mejora** | Hoy subestima pending post-pago; con fix reflejará cortes reales |
| Reporte ingresos chica/casa | **Neutro** | Lee tablas origen (`room_services`, `sales`), no settlements |
| `GetGirlShiftEarningsUseCase` | **Alineación** | Hoy ya calcula unsettled por ítem; convergerá con liquidaciones visibles |

**Atención:** Reportes históricos que asumían “1 liquidación = turno completo de María” necesitan documentación de que ahora son cortes.

---

## 9. Impacto en cierre de caja

| Check actual | Comportamiento hoy | Con fix + mejoras |
|--------------|-------------------|-------------------|
| `pending_settlements > 0` | Bloquea cierre | Correcto para headers PENDING |
| `settlements_generated > 0` | Pasa si hubo al menos una generación | Insuficiente si hay fuentes huérfanas |
| Fuentes sin settlement_item | **No se valida** | Debe agregarse warning/blocker |

**Egresos de caja:** Cada `mark-paid` genera un movimiento por settlement. Múltiples cortes = múltiples egresos (correcto operativamente).

**expected_by_method:** Ya descuenta egresos por método; múltiples pagos incrementan el total de egresos de forma lineal.

---

## 10. Impacto en cierre de turno

| Aspecto | Hoy | Recomendado |
|---------|-----|-------------|
| Blocker `no_settlements_generated` | Solo cuenta headers | Mantener |
| Warning `pending_settlements` | Headers PENDING | Mantener |
| Fuentes huérfanas | Ignoradas | **Nuevo warning/blocker** |
| Rotación AUTO de turno | Independiente | Tras rotación, fuentes nuevas van al turno nuevo (scope correcto) |

Cerrar turno con liquidaciones PAID parciales **no debe** considerarse “todo liquidado” si quedan fuentes sin ítem.

---

## Archivos auditados

| Archivo | Rol |
|---------|-----|
| `GenerateCurrentShiftSettlementsUseCase.php` | Orquestación generate |
| `GetSettlementPendingSourcesUseCase.php` | Conteo fuentes no liquidadas (correcto) |
| `EloquentStaffSettlementRepository.php` | **Lógica core — bug aquí** |
| `StaffSettlementRepositoryInterface.php` | Contrato deduplicación |
| `GetGirlShiftEarningsUseCase.php` | Detección unsettled en app móvil |
| `CashSessionCloseCheckBuilder.php` | Cierre caja — gap fuentes huérfanas |
| `EloquentReportReadRepository.php` | Reportes y cierre turno |
| Migraciones `staff_settlements`, `staff_settlement_items` | Uniques determinantes |

---

## Conclusión

El sistema **casi** implementa liquidaciones incrementales a nivel de ítem, pero **rompe el flujo** al exigir un único header por persona/turno/tipo y al congelar ese header al pagarlo.

**Respuesta a la pregunta clave:**

> ¿Dónde marca el sistema que una fuente ya fue liquidada?

En **`staff_settlement_items`** (por `sale_item_id` o `source_id` + `source_type`). Las tablas origen **no** tienen marca. El fallo del caso María **no** es falta de mecanismo de deduplicación, sino **imposibilidad de crear un segundo header PENDING** después del primer pago.

**Próximo paso recomendado:** Aprobar plan Fase 1–2, implementar con tests de regresión, luego endurecer cierres de caja/turno (Fase 4).

---

*Documento generado en auditoría read-only. Sin migraciones ni cambios de código.*
