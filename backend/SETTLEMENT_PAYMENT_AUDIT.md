# AUDITORÍA — FLUJO DE PAGO DE LIQUIDACIONES (Backend)

**Fecha:** 2026-06-21  
**Alcance:** Análisis — **implementado 2026-06-21**  
**Implementación:** `backend/SETTLEMENT_PAYMENT_AUDITABLE_IMPLEMENTATION_REPORT.md`  
**Contexto:** Fases 1–4 + descuento manual ✅

**Objetivo:** Definir el flujo de pago **totalmente auditable** antes de implementar descuento manual y ticket definitivo.

---

## 1. Resumen ejecutivo

Hoy el pago de liquidaciones **no es solo cambiar un estado**, pero tampoco es un **comprobante auditable completo**. El sistema:

- Calcula neto (bruto + ajustes + multas seleccionadas al pagar)
- Registra egreso en caja con método de pago
- Marca settlement `PAID` con `paid_by` / `paid_at`
- Persiste multas aplicadas y ajustes `MANUAL_FINE`

**Faltaban** evidencias clave — **implementadas** en Fase 4A–4D + Fase 3: ticket consecutivo, `payment_method`, `cash_movement_id`, print job, reimpresión, audit logs.

**No existe** `SettlementPaymentService` — la lógica vive en `MarkSettlementPaidUseCase` + `SettlementFineApplier`.

---

## 2. Inventario de componentes analizados

| Componente | Existe | Rol actual |
|------------|--------|------------|
| `SettlementPaymentService` | ❌ | — |
| `MarkSettlementPaidUseCase` | ✅ | Orquestador único de pago |
| `SettlementAdjustmentEngine` | ✅ | Limpieza automática (solo al generar/recalc PENDING) |
| `SettlementTotalsCalculator` | ✅ | gross + adjustments → net; `total_amount = net` |
| `SettlementFineApplier` | ✅ | Preview + aplicar multas al pagar |
| `staff_settlements` | ✅ | Header liquidación |
| `staff_settlement_items` | ✅ | Líneas de ingreso (congeladas al generar) |
| `staff_settlement_adjustments` | ✅ | Limpieza + multas aplicadas al pagar |
| `staff_fines` | ✅ | Multas independientes → APPLIED al pagar |
| `CashSession` / `cash_movements` | ✅ | Egreso EXPENSE al pagar |
| `print_jobs` | ✅ | **No usado** para liquidaciones |
| `PrintTicketContentBuilder` | ✅ | Sin `buildSettlementPayment()` |
| `PrintableSettlementTicket` | ❌ | — |
| `CreateSettlementPaymentPrintJobUseCase` | ❌ | — |
| `PrintJobType::Settlement` | ⚠️ | Enum definido, **nunca usado** |

---

## 3. Qué ocurre hoy al pagar (`POST /settlements/{id}/mark-paid`)

### 3.1 Secuencia

```
1. Validar permiso settlements.pay
2. Validar settlement PENDING (rechaza PAID / CANCELLED)
3. Validar caja abierta + scope cajera (my_cash_session)
4. DB::transaction:
   a. Resolver caja abierta del pagador
   b. SettlementFineApplier::applySelectedFines(applied_fine_ids)
      → crea staff_settlement_adjustments MANUAL_FINE
      → marca staff_fines APPLIED
      → recalcula net si hay multas
   c. cash_movements::addMovement(EXPENSE, amount = total_amount/net)
   d. UPDATE staff_settlements.cash_session_id = sesión pagadora
   e. markPaid → status PAID, paid_by, paid_at, notes
5. SSE: settlement.paid, cash.movement.created
6. Response: { settlement, cash_session_id }  ← sin print_job, sin cash_movement_id
```

### 3.2 Qué se guarda

| Dato | ¿Persistido? | Dónde |
|------|--------------|-------|
| Hora exacta de pago | ✅ | `staff_settlements.paid_at` |
| Usuario que pagó | ✅ | `staff_settlements.paid_by_user_id` |
| Caja | ⚠️ Parcial | `staff_settlements.cash_session_id` (sobreescrito al pagar) |
| Turno | ✅ | `staff_settlements.official_shift_id` (desde generación) |
| Método de pago | ⚠️ Solo caja | `cash_movements.payment_method` — **no en settlement** |
| Bruto | ✅ | `staff_settlements.gross_amount` (no reescrito al pagar) |
| Limpieza | ✅ | `staff_settlement_adjustments` CLEANING_DEDUCTION |
| Multas aplicadas | ✅ | adjustments MANUAL_FINE + fines APPLIED |
| Descuento manual | ❌ | Fase 3 — no implementado |
| Neto pagado | ✅ | `net_amount`, `total_amount`; egreso = mismo monto |
| Observaciones | ⚠️ | `staff_settlements.notes` — **sobrescribe** notas previas si se envían |
| Número consecutivo ticket | ❌ | No existe |
| Ticket imprimible | ❌ | No se crea print_job |
| Reimpresión trazada | ❌ | No hay endpoint |
| Snapshot inmutable del pago | ❌ | No hay tabla de pago |
| Entrada audit_logs | ❌ | No se escribe |

### 3.3 Enlace settlement ↔ caja

- Movimiento: `source_type = STAFF_SETTLEMENT`, `source_id = settlement.id`
- **No hay** `cash_movement_id` en `staff_settlements` (a diferencia de bracelets/room_services/shows)
- Recuperar movimiento: query inversa por source — frágil si hubiera duplicados (hoy no debería)

### 3.4 Números: corte vs ticket

| Concepto | Estado |
|----------|--------|
| `cut_number` / `cut_label` (`Corte #N`) | ✅ Calculado en repositorio (ordinal por persona/tipo/turno) |
| Expuesto en API detalle | ❌ `SettlementMapper` **omite** cut_number/cut_label |
| Número ticket consecutivo sucursal | ❌ No existe (ej. 000234) |
| Contador reimpresiones | ❌ No persistido en settlement |

---

## 4. Motor de ajustes en el momento del pago

### Fase 1 — Limpieza (`SettlementAdjustmentEngine`)

- Solo `GIRL` + `PENDING`
- Se sincroniza en **generación** y en `findById` (recalc PENDING)
- **No se ejecuta al pagar** — ya debe existir en adjustments si aplica
- Dedup: `cleaning:{shift}:{cash_session}:{staff_user_id}`

### Fase 2 — Multas (`SettlementFineApplier`)

- Multas **no** entran en generación
- Preview: `GET pay-preview` (ephemeral)
- Al pagar: solo `applied_fine_ids[]` seleccionados → `MANUAL_FINE` + fine `APPLIED`
- Dedup ajuste: `fine:{fine_id}`

### Fase 3 — Descuento manual (pendiente)

- Diseño: `MANUAL_DISCOUNT` en adjustments mientras PENDING
- Orden cálculo objetivo: Bruto → Limpieza → Descuento → Multas → Neto
- **No implementado** — no alterar mark-paid hasta definir snapshot

### `SettlementTotalsCalculator`

```
gross = SUM(items)
if PENDING → syncCleaningDeduction()
adjustments_total = SUM(adjustments)
net = gross + adjustments_total
total_amount = net
```

Al pagar **sin multas**: no recalcula (net ya correcto post-limpieza).  
Al pagar **con multas**: recalcula incluyendo MANUAL_FINE.

**Gap:** No hay congelación explícita post-PAID más allá de `status !== PENDING` (engine no toca PAID). Ajustes existentes quedan como están — correcto para V1 si no se permite editar PAID.

---

## 5. Impresión hoy

### Lo que existe (reutilizable)

| Pipeline | Uso settlement |
|----------|----------------|
| `CreateCashMovementPrintJobUseCase` | ❌ mark-paid no lo invoca |
| `PrintCashMovementUseCase` + `{ reprint: true }` | Patrón reimpresión |
| `PrintTicketContentBuilder::buildCashMovement()` | Ticket genérico egreso — **sin desglose liquidación** |
| `PrintTicketContentBuilder::buildCashClose()` | Solo totales agregados settlement en cierre caja |
| `PrintJobType::Settlement` | Enum huérfano |
| Idempotency + reprint timestamp | Patrón en show/room/cash-close/shift-close |

### Lo que falta

- `buildSettlementPayment()` con layout profesional (ver §7)
- `CreateSettlementPaymentPrintJobUseCase`
- `PrintSettlementPaymentUseCase` (reprint)
- `PrintJobSourceType::StaffSettlement` (o similar)
- Invocación post mark-paid (como `RegisterCashMovementUseCase`)
- Payload enriquecido: persona, rol, turno, caja, bruto, cada ajuste, neto, método, pagador, ticket #, cut_label

### Atajo descartable (no recomendado V1)

Imprimir el movimiento de caja genérico post-pago — muestra monto y motivo, **no** multas/limpieza/neto desglosado.

---

## 6. Historial y consultas

### `GET /settlements/history`

Expone: gross, net, status, paid_by_name, paid_at, cut_label (vía repo), shift  
**No expone:** payment_method, cash_movement_id, ticket_number, has_print_job, adjustment breakdown

### `GET /settlements/{id}`

Expone: items, adjustments (con fine_id), gross/net, paid metadata  
**No expone:** cut_number, payment_method, cash_movement_id, shift_name en mapper estándar

### Estados

| Estado | Implementado |
|--------|--------------|
| PENDING | ✅ |
| PAID | ✅ |
| CANCELLED | ⚠️ Columna/filtro existe — **sin use case de anulación** documentado |

---

## 7. Flujo objetivo (V1 perfecto) — diseño propuesto

### 7.1 Principio: pago = evento auditable

Introducir **`staff_settlement_payments`** (1:1 con settlement PAID) o ampliar `staff_settlements` con columnas de pago snapshot. Recomendación: **tabla de pago** para no sobrecargar header y permitir re-auditoría.

```sql
staff_settlement_payments  -- propuesta
  id
  staff_settlement_id       UNIQUE
  tenant_id, branch_id
  official_shift_id
  cash_session_id
  cash_movement_id          FK
  payment_method
  paid_by_user_id
  paid_at
  gross_amount
  adjustments_total
  net_amount
  notes
  ticket_number             -- consecutivo sucursal
  print_job_id              nullable
  print_count               default 0
  last_reprinted_at
  last_reprinted_by_user_id
  payment_snapshot_json     -- opcional: líneas ajuste congeladas
```

Alternativa mínima V1: columnas en `staff_settlements`: `payment_method`, `cash_movement_id`, `ticket_number`, `paid_gross`, `paid_net`, `paid_adjustments_total`.

### 7.2 Ticket objetivo

```
==============================
     LIQUIDACIÓN PAGADA
==============================
Persona:    María López
Rol:        Chica
Caja:       #12
Turno:      Noche 21/06
Fecha:      21/06/2026
Hora:       02:15

BRUTO              300.00 Bs
Limpieza           -10.00 Bs
Multas:
  · Vaso roto      -30.00 Bs
  · Llegada tarde  -20.00 Bs
Descuento manual   -15.00 Bs   ← Fase 3

NETO PAGADO        225.00 Bs

Método:     EFECTIVO
Pagado por: Cajera Ana
Corte:      Corte #2
Ticket:     000234

==============================
Powered by Ribersoft
WhatsApp 67369293
==============================
```

### 7.3 Reimpresión objetivo

```
==============================
       REIMPRESIÓN
       N° 2
==============================
Fecha reimpresión: 21/06/2026 02:20
Reimpreso por:     Admin Demo
...
(mismo cuerpo del ticket original)
```

Patrón existente: `PrintCashMovementUseCase` idempotency `...:reprint:{timestamp}` + header `REIMPRESION` en builder.

Trazabilidad: incrementar `print_count`, guardar `last_reprinted_*`, opcional fila en `audit_logs`.

### 7.4 Orden de implementación recomendado

| Paso | Entrega | Depende de |
|------|---------|------------|
| **4A** | Snapshot pago + FK movimiento + payment_method en settlement/payment | — |
| **4B** | ticket_number consecutivo (branch sequence) | 4A |
| **4C** | `buildSettlementPayment` + print job al pagar | 4A |
| **4D** | Reprint endpoint + contador | 4C |
| **4E** | audit_logs en pay + reprint | 4A |
| **3** | Descuento manual (MANUAL_DISCOUNT) | 4A (snapshot incluye descuento) |

**Rationale:** Definir snapshot de pago **antes** de descuento manual evita recalcular historial cuando se agregue Fase 3.

---

## 8. Matriz: existe / falta / reutilizar / eliminar / refactor / V1.1

### 8.1 Qué ya existe (mantener)

- `MarkSettlementPaidUseCase` como orquestador (extender, no reemplazar por servicio nuevo obligatorio)
- `SettlementFineApplier` + pay-preview
- `SettlementTotalsCalculator` + `SettlementAdjustmentEngine`
- Egreso caja con `source_type STAFF_SETTLEMENT`
- SSE operativos
- Tests Fase 1 + Fase 2
- `cut_number` en repositorio (exponer en API)

### 8.2 Qué falta (V1 pago auditable)

| Prioridad | Falta |
|-----------|-------|
| P0 | Snapshot pago (`payment_method`, `cash_movement_id`, montos congelados al pagar) |
| P0 | Ticket impresión settlement payment |
| P0 | Número ticket consecutivo |
| P1 | Reprint trazado (contador + usuario + fecha) |
| P1 | `audit_logs` en pay/reprint |
| P1 | API history/detail: método, movimiento, ticket #, “con ticket” |
| P2 | Tabla `staff_settlement_payments` vs columnas en header (decisión arquitectura) |
| P2 | Anulación settlement (`CANCELLED`) con reglas — hoy solo enum |

### 8.3 Qué reutilizar

| De | Para |
|----|------|
| `RegisterCashMovementUseCase` | Post-pay print job + SSE `print_job.created` |
| `CreateCashClosePrintJobUseCase` | Presenter + enricher pattern |
| `PrintCashCloseUseCase` / `PrintShowUseCase` | Reprint con idempotency timestamp |
| `PrintTicketContentBuilder` helpers | `row()`, `center()`, `formatDateTime()`, footer Ribersoft |
| `CashClosePrintPayloadEnricher` | Inspiración para `SettlementPaymentPrintPayloadEnricher` |
| `print_jobs` + agent | Entrega física |

### 8.4 Qué eliminar

- **Nada crítico.** `PrintJobType::Settlement` huérfano se **usa** al implementar — no borrar.
- Evitar duplicar lógica de neto en un segundo servicio paralelo.

### 8.5 Qué refactorizar

| Área | Refactor |
|------|----------|
| `MarkSettlementPaidUseCase` | Extraer `SettlementPaymentRecorder` (snapshot + movement + markPaid + print) |
| `SettlementMapper` | Incluir cut_label, payment_method, ticket_number, cash_movement_id |
| `markPaid` response | Devolver `{ settlement, cash_movement_id, print_job?, ticket_number }` |
| Notes en mark-paid | **Merge** notas pago vs sobrescribir (auditoría) |
| `cash_session_id` | Diferenciar sesión generación vs sesión pago si aplica scope |

### 8.6 Dejar para V1.1

- Export PDF/Excel historial liquidaciones
- Anulación PAID con reverso contable automático
- Multi-ticket por partial settlement batch pay
- Firma digital / hash ticket
- Dashboard auditoría dedicado
- Descuentos automáticos por origen
- Ticket fiscal legal (solo operativo hoy)

---

## 9. Tests obligatorios (post-implementación pago auditable)

1. mark-paid persiste payment_method + cash_movement_id  
2. ticket_number consecutivo por sucursal  
3. print job creado al pagar (con device activo)  
4. ticket content incluye bruto/limpieza/multas/neto/método  
5. reprint incrementa print_count + header REIMPRESIÓN  
6. PAID inmutable — no recalc cleaning  
7. history expone campos audit  
8. audit_logs entrada en pay y reprint  
9. idempotencia mark-paid no duplica movement  
10. Regresión Fase 2 multas parciales  

---

## 10. Referencias de código

| Archivo |
|---------|
| `app/Application/StaffSettlement/UseCases/MarkSettlementPaidUseCase.php` |
| `app/Application/StaffSettlement/Services/SettlementFineApplier.php` |
| `app/Application/StaffSettlement/Services/SettlementTotalsCalculator.php` |
| `app/Application/StaffSettlement/Services/SettlementAdjustmentEngine.php` |
| `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php` |
| `app/Application/StaffSettlement/Support/SettlementMapper.php` |
| `app/Application/Printing/Services/PrintTicketContentBuilder.php` |
| `app/Application/Printing/UseCases/CreateCashMovementPrintJobUseCase.php` |
| `app/Application/Printing/UseCases/PrintCashCloseUseCase.php` |
| `tests/Feature/Api/V1/SettlementAdjustmentsEnginePhase2FinesTest.php` |
| `tests/Feature/Api/V1/SettlementPaymentMethodTest.php` |

---

## 11. Conclusión

El motor de neto (Fase 1 + 2) es sólido. El **gap principal** no es calcular mal el monto, sino **no cerrar el ciclo auditable**: comprobante, número, enlace contable explícito, reimpresión trazada y snapshot del instante de pago.

**Próximo paso recomendado:** implementar **Fase 4A–4D** (snapshot + ticket + reprint) **antes o en paralelo con Fase 3** descuento manual, para que el descuento quede incluido en el snapshot desde el primer día.

**No programar hasta aprobar este diseño.**
