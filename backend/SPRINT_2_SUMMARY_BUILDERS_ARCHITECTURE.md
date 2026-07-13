# Arquitectura de Summary Builders — Sprint 2 (Backend)

**Fecha:** 2026-07-10
**Estado:** Diseño definitivo de arquitectura — sin implementación
**Fuentes oficiales:**

- `FINANCIAL_ARCHITECTURE_MASTER.md`
- `FINANCIAL_IMPLEMENTATION_BLUEPRINT.md`
- `backend/SPRINT_1_INTEGRATION_AUDIT.md`

---

## 0. Principio rector

El objetivo del Sprint 2 no es construir más funciones.

El objetivo es reemplazar `CashSessionFinancialSummaryBuilder` como único punto monolítico de verdad financiera por **cinco builders especializados** con responsabilidad única, cada uno consultando directamente desde taxonomía estructurada (`movement_family`, `movement_category`) y no desde heurísticas de texto.

Regla de oro de este diseño:

> Ningún builder del Sprint 2 debe usar `description not like 'Cobro comanda%'` ni `movement_type = INCOME` como criterio de clasificación. Solo puede usar `movement_family` y `movement_category`.

---

## 1. Estado actual que este diseño reemplaza

### Problema central

`CashSessionFinancialSummaryBuilder` hoy produce un único payload grande que mezcla:

- ventas por método
- movimientos manuales (calculados por heurística de texto)
- totales de caja física
- expected cash
- contado y diferencia

Ese mismo payload es consumido por:

- `GetCurrentCashSessionUseCase`
- `GetCashSessionUseCase`
- `GetCashSessionAdminUseCase`
- `GetCashSessionsSummaryAdminUseCase`
- `GetCashSessionCloseCheckAdminUseCase`
- `ListCashSessionsAdminUseCase`
- `ForceCloseCashSessionAdminUseCase`

### Deuda secundaria

Además, `CashCloseReportSectionsBuilder`, `EloquentReportReadRepository`, y `EloquentOfficialShiftRepository` tienen sus propias heurísticas que duplican parte del mismo cálculo.

### Qué se elimina

Al terminar Sprint 2:

- `sumManualMovements()` ya no necesita el filtro por description
- `getDailySummary()` usa aggregation por `movement_category` en lugar de exclusiones
- `getCashReport()` expone taxonomía real en lugar de solo movement_type

---

## 2. Taxonomía de referencia para todos los builders

Todos los builders de Sprint 2 toman como axioma oficial la taxonomía producida en Sprint 1:

```
movement_family:
  SALE       → ingresos por ventas
  MANUAL     → ingresos/egresos manuales operativos
  SETTLEMENT → pagos al personal
  EXPENSE    → gastos del negocio
  ADJUSTMENT → ajustes contables (reservado)

movement_category:
  SALE_COLLECTION
  DIRECT_SALE_COLLECTION
  BRACELET_COLLECTION
  ROOM_SERVICE_COLLECTION
  SHOW_COLLECTION
  MANUAL_INCOME
  SETTLEMENT_GIRL_PAYMENT
  SETTLEMENT_WAITER_PAYMENT
  SETTLEMENT_CLEANING_PAYMENT
  OPERATING_EXPENSE
  PURCHASE
  OTHER_INCOME
  OTHER_EXPENSE
```

---

## 3. Definición de cada builder

---

## 3.1 SalesSummaryBuilder

### Responsabilidad única

Responder: ¿cuánto se vendió, cuándo, cómo y qué se vendió?

No toca ni caja física, ni movimientos manuales, ni liquidaciones.

### Entradas

```
tenantId: int
branchId: int
scope: array{
  cash_session_id?: int,
  shift_ids?: int[],
  date_from?: string,
  date_to?: string,
}
```

### Salidas — DTO `SalesSummaryData`

```
total_sales: string              // suma de sale_payments.amount
sales_count: int                 // count(sales)
average_ticket: string           // total_sales / sales_count
sales_by_method: {
  cash: string
  qr: string
  card: string
  mixed: string
}
sales_by_hour: [                 // agrupación por paid_at hora
  { bucket: string, count: int, total: string }
]
sales_by_category: {             // agrupación por movement_category via cash_movements
  SALE_COLLECTION: string
  DIRECT_SALE_COLLECTION: string
  BRACELET_COLLECTION: string
  ROOM_SERVICE_COLLECTION: string
  SHOW_COLLECTION: string
}
products_sold_count: int
services_sold_count: int         // bracelets + room_services + shows
```

### Dependencias

- `SaleRepositoryInterface::sumPaymentsByMethodForSession()`
- `SaleModel` (count, sum)
- `CashMovementModel` filtrado por `movement_family = SALE`
- `BraceletModel`, `RoomServiceModel`, `ShowModel` (count)

### Consultas SQL necesarias

```sql
-- Total de ventas
SELECT SUM(sp.amount)
FROM sale_payments sp
JOIN sales s ON s.id = sp.sale_id
WHERE s.cash_session_id = :sessionId

-- Ventas por método
SELECT sp.payment_method, SUM(sp.amount)
FROM sale_payments sp
JOIN sales s ON s.id = sp.sale_id
WHERE s.cash_session_id = :sessionId
GROUP BY sp.payment_method

-- Ventas por categoría de movimiento (nuevo, desde taxonomía)
SELECT movement_category, SUM(amount)
FROM cash_movements
WHERE cash_session_id = :sessionId
  AND movement_family = 'SALE'
GROUP BY movement_category

-- Cantidad de ventas
SELECT COUNT(*) FROM sales WHERE cash_session_id = :sessionId
```

### Qué lógica abandona el sistema legacy

- Ya no se deducen ventas de movimientos totales por `description not like 'Cobro comanda%'`
- `sumManualMovements()` deja de ser la fuente de "qué no es venta"
- `salesByMethod()` en `CashSessionFinancialSummaryBuilder` queda redundante

### Controladores que lo consumirán

- `CashController::current()` vía `GetCurrentCashSessionUseCase`
- `AdminCashSessionController::show()` vía `GetCashSessionAdminUseCase`
- `AdminCashSessionController::summary()` vía `GetCashSessionsSummaryAdminUseCase`
- `ReportController::daily()` vía `GetDailyReportUseCase`
- `ReportController::managerialDaily()` vía `GetManagerialDailyReportUseCase`

### Reportes que reutilizarán este summary

- Reporte diario
- Reporte gerencial
- Cierre de caja
- Cierre de turno

### Componentes frontend que dependerán de este summary

- `frontend/src/pages/nightpos/cash/index.vue` — KPI ventas
- `frontend/src/pages/nightpos/finance/reports/index.vue` — tab ventas
- `frontend/src/pages/nightpos/finance/reports/managerial-daily.vue` — KPI ventas
- `frontend/src/pages/nightpos/shift-console/index.vue` — resumen de turno

---

## 3.2 CashSummaryBuilder

### Responsabilidad única

Responder: ¿cuánto efectivo físico debería haber, cuánto se declaró y qué diferencia existe?

No incluye QR ni tarjeta como parte del arqueo principal. Los expone como conciliación de medios separada.

### Entradas

```
tenantId: int
branchId: int
sessionId: int
openingAmount: string
storedExpectedAmount?: string    // para sesiones ya cerradas
declaredClosingAmount?: string
differenceAmount?: string
status: string
```

### Salidas — DTO `CashSummaryData`

```
opening_cash: string             // fondo inicial

cash_income_from_sales: string   // cash_movements SALE + payment_method=CASH
cash_income_manual: string       // cash_movements MANUAL / INCOME + payment_method=CASH
cash_expense_settlements: string // cash_movements SETTLEMENT + payment_method=CASH
cash_expense_operational: string // cash_movements EXPENSE + payment_method=CASH

expected_cash: string            // fórmula oficial (ver abajo)
counted_cash: string|null
cash_difference: string|null

non_physical_by_method: {        // conciliación de medios no físicos (no es arqueo)
  qr: { income: string, expense: string, net: string }
  card: { income: string, expense: string, net: string }
}

cash_available_for_settlements: string   // expected_cash - settlement_expenses_uncommitted
cash_available_for_expenses: string
```

### Fórmula oficial de `expected_cash`

```
expected_cash =
  opening_cash
  + cash_income_from_sales
  + cash_income_manual
  - cash_expense_settlements
  - cash_expense_operational
```

Nota: esta es exactamente la misma lógica que hoy existe, pero ahora expresada por categorías estructuradas en lugar de heurísticas.

### Dependencias

- `CashMovementModel` filtrado por `cash_session_id` y `payment_method = CASH`
- agrupado por `movement_family` y `movement_category`
- `CashSessionModel` para `opening_amount`, `expected_amount`, `declared_closing_amount`, `difference_amount`

### Consultas SQL necesarias

```sql
-- Ingresos en efectivo por family
SELECT movement_family, movement_category, SUM(amount)
FROM cash_movements
WHERE cash_session_id = :sessionId
  AND movement_type = 'INCOME'
  AND payment_method = 'CASH'
GROUP BY movement_family, movement_category

-- Egresos en efectivo por family
SELECT movement_family, movement_category, SUM(amount)
FROM cash_movements
WHERE cash_session_id = :sessionId
  AND movement_type = 'EXPENSE'
  AND payment_method = 'CASH'
GROUP BY movement_family, movement_category

-- Conciliación de medios no físicos
SELECT payment_method, movement_type, SUM(amount)
FROM cash_movements
WHERE cash_session_id = :sessionId
  AND payment_method IN ('QR', 'CARD')
GROUP BY payment_method, movement_type
```

### Qué lógica abandona el sistema legacy

- `sumManualMovements()` queda obsoleto
- `sumMovements()` queda obsoleto como fuente principal
- `sumMovementsByMethod()` puede conservarse pero ya no como base de los summaries
- la fórmula de `computedExpectedCash` en `CashSessionFinancialSummaryBuilder` se elimina

### Controladores que lo consumirán

- `CashController::current()`
- `CashController::close()`
- `AdminCashSessionController::show()`
- `AdminCashSessionController::closeCheck()`
- `AdminCashSessionController::summary()`

### Reportes que reutilizarán este summary

- Cierre de caja
- Fiscalización admin
- Reporte diario
- Reporte gerencial

### Componentes frontend que dependerán de este summary

- `frontend/src/pages/nightpos/cash/index.vue` — bloque caja física
- Diálogo cierre de caja (expected_cash, counted_cash, difference)
- `frontend/src/pages/nightpos/finance/cash-sessions/[id].vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/summary.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/by-cashier.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/by-shift.vue`

---

## 3.3 MovementSummaryBuilder

### Responsabilidad única

Responder: ¿qué entradas y salidas financieras ocurrieron, agrupadas por categoría, método y tiempo?

No clasifica por ventas ni por arqueo. Es el libro operativo estructurado.

### Entradas

```
tenantId: int
branchId: int
sessionId: int
includeDetail: bool  // true = devuelve también la lista de movimientos individuales
```

### Salidas — DTO `MovementSummaryData`

```
income_count: int
expense_count: int
income_total: string
expense_total: string

by_family: {
  SALE: { income: string, expense: string }
  MANUAL: { income: string, expense: string }
  SETTLEMENT: { income: string, expense: string }
  EXPENSE: { income: string, expense: string }
}

by_category: {
  SALE_COLLECTION: string
  DIRECT_SALE_COLLECTION: string
  BRACELET_COLLECTION: string
  ROOM_SERVICE_COLLECTION: string
  SHOW_COLLECTION: string
  MANUAL_INCOME: string
  SETTLEMENT_GIRL_PAYMENT: string
  SETTLEMENT_WAITER_PAYMENT: string
  SETTLEMENT_CLEANING_PAYMENT: string
  OPERATING_EXPENSE: string
  PURCHASE: string
  OTHER_INCOME: string
  OTHER_EXPENSE: string
}

by_payment_method: {
  cash: { income: string, expense: string }
  qr:   { income: string, expense: string }
  card: { income: string, expense: string }
}

last_movement: {                 // útil para dashboard
  id: int
  movement_type: string
  movement_family: string
  movement_category: string
  amount: string
  created_at: string
}

max_movement: {                  // útil para control operativo
  id: int
  movement_type: string
  movement_family: string
  movement_category: string
  amount: string
}

movements: [...]                 // solo si includeDetail = true
```

### Dependencias

- `CashMovementModel` agrupado por `movement_family`, `movement_category`, `movement_type`, `payment_method`
- no necesita joins complejos

### Consultas SQL necesarias

```sql
-- Aggregated totals por family / category
SELECT movement_family, movement_category, movement_type, payment_method,
       COUNT(*) AS cnt, SUM(amount) AS total
FROM cash_movements
WHERE cash_session_id = :sessionId
GROUP BY movement_family, movement_category, movement_type, payment_method

-- Último movimiento
SELECT id, movement_type, movement_family, movement_category, amount, created_at
FROM cash_movements
WHERE cash_session_id = :sessionId
ORDER BY id DESC LIMIT 1

-- Movimiento mayor
SELECT id, movement_type, movement_family, movement_category, amount
FROM cash_movements
WHERE cash_session_id = :sessionId
ORDER BY amount DESC LIMIT 1
```

### Qué lógica abandona el sistema legacy

- `sumMovements()` como fuente principal de income_total / expense_total
- `movements_summary` con where por `movement_type` en `CashCloseReportSectionsBuilder`
- deduplication manual de income vs manual en reportes

### Controladores que lo consumirán

- `CashController::current()` — para movimientos recientes
- `AdminCashSessionController::show()` — para el libro de movimientos
- `ReportController::daily()` — para totales de movimientos manuales y gastos
- `ReportController::cash()` — para desglose de movimientos por sesión

### Reportes que reutilizarán este summary

- Cierre de caja
- Reporte caja
- Reporte diario
- Reporte gerencial (para calcular `outflows_cash_expenses`)

### Componentes frontend que dependerán de este summary

- Tabla de movimientos en `cash/index.vue`
- Tabla de movimientos en `cash-sessions/[id].vue`
- Bloque de movimientos en cierre browser `PrintableCashSessionReport.vue`

---

## 3.4 SettlementSummaryBuilder

### Responsabilidad única

Responder: ¿cuánto se le debe al personal, cuánto se ha pagado y cuánto está pendiente, separado por rol?

No toca caja ni ventas.

### Entradas

```
tenantId: int
branchId: int
scope: {
  cash_session_id?: int
  official_shift_id?: int
}
```

### Salidas — DTO `SettlementSummaryData`

```
pending_waiter_count: int
pending_waiter_amount: string
pending_girl_count: int
pending_girl_amount: string
pending_cleaning_count: int
pending_cleaning_amount: string

pending_total_count: int
pending_total_amount: string

paid_today_count: int
paid_today_amount: string

paid_by_role: {
  GIRL:     { count: int, amount: string }
  WAITER:   { count: int, amount: string }
  CLEANING: { count: int, amount: string }
}

adjustments_summary: {           // para cierre detallado
  fines:           { count: int, amount: string }
  cleaning_deductions: { count: int, amount: string }
  manual_discounts: { count: int, amount: string }
}

unsettled_sources_count: int     // fuentes aún no liquidadas
```

### Dependencias

- `StaffSettlementModel` con scope por `cash_session_id` o `official_shift_id`
- `StaffSettlementAdjustmentModel` para adjustments_summary
- `StaffSettlementRepositoryInterface::countPendingSettlements()` (ya existe)
- `StaffSettlementRepositoryInterface::sumPendingSettlementAmount()` (ya existe)
- `StaffSettlementRepositoryInterface::countUnsettledShiftSources()` (ya existe)

### Consultas SQL necesarias

```sql
-- Pendientes por rol
SELECT settlement_type, COUNT(*), SUM(net_amount)
FROM staff_settlements
WHERE tenant_id = :tenantId
  AND branch_id = :branchId
  AND cash_session_id = :sessionId
  AND status = 'PENDING'
GROUP BY settlement_type

-- Pagados hoy por rol
SELECT settlement_type, COUNT(*), SUM(net_amount)
FROM staff_settlements
WHERE tenant_id = :tenantId
  AND branch_id = :branchId
  AND cash_session_id = :sessionId
  AND status = 'PAID'
  AND DATE(paid_at) = CURDATE()
GROUP BY settlement_type

-- Ajustes sobre liquidaciones pagadas
SELECT adjustment_type, COUNT(*), SUM(amount)
FROM staff_settlement_adjustments sa
JOIN staff_settlements ss ON ss.id = sa.staff_settlement_id
WHERE ss.cash_session_id = :sessionId
  AND ss.status = 'PAID'
GROUP BY adjustment_type
```

### Qué lógica abandona el sistema legacy

- cálculos de liquidaciones separados en `CashCloseReportSectionsBuilder::groupPaidSettlements()`
- pendientes desde `CashSessionCloseCheckBuilder` que hoy llama por separado cada rol
- duplicación entre `settlement_summary` de `SettlementOperationalContextBuilder` y los totales en builder de cierre

### Controladores que lo consumirán

- `CashController::current()` — para pendientes visible en dashboard caja
- `CashController::closeCheck()` — para saber si se puede cerrar
- `AdminCashSessionController::show()` — detalle admin con liquidaciones
- `ReportController::daily()` — totales de liquidaciones en reporte
- `ReportController::settlements()` — reporte detallado

### Reportes que reutilizarán este summary

- Cierre de caja
- Cierre de turno
- Reporte diario
- Reporte gerencial
- Fiscal admin

### Componentes frontend que dependerán de este summary

- Bloque pendientes operativos en `cash/index.vue`
- `frontend/src/pages/nightpos/settlements/index.vue` — KPIs del hub
- Diálogo cierre de caja (pendientes alert)
- `PrintableCashSessionReport.vue` — sección liquidaciones

---

## 3.5 ScopeSummaryBuilder

### Responsabilidad única

Responder: ¿qué alcance temporal y de sesión aplica a esta consulta? ¿La caja y el turno coinciden?

No calcula dinero. Solo resuelve y describe el contexto operativo.

### Entradas

```
tenantId: int
branchId: int
cashSessionId?: int
officialShiftId?: int
openShiftId?: int
userId?: int
scope?: string  // 'shift' | 'my_cash_session'
```

### Salidas — DTO `ScopeSummaryData`

```
scope_type: string               // 'cash_session' | 'shift' | 'hybrid'
cash_session_id: int|null
session_official_shift_id: int|null
open_official_shift_id: int|null
included_official_shift_ids: int[]
shift_mismatch: bool             // true si la caja cruzó turnos
is_multi_shift: bool

session_opened_at: string|null
session_closed_at: string|null

scope_description: string        // texto para UI: "mi caja actual", "turno oficial"
```

### Dependencias

- `CashSessionModel` — para leer shift_id de apertura
- `SaleModel` — para detectar shift_ids con actividad
- `SettlementShiftScopeResolver` (ya existe, puede consumirse)
- no hace queries costosas; es descriptivo

### Consultas SQL necesarias

```sql
-- Shift ids con actividad en la caja
SELECT DISTINCT official_shift_id
FROM sales
WHERE cash_session_id = :sessionId
  AND official_shift_id IS NOT NULL

UNION

SELECT DISTINCT official_shift_id
FROM bracelets
WHERE cash_session_id = :sessionId
  AND official_shift_id IS NOT NULL
```

### Qué lógica abandona el sistema legacy

- lógica dispersa de scope en `SettlementOperationalContextBuilder::build()`
- duplicación del contexto de turno en múltiples use cases

### Controladores que lo consumirán

- `CashController::current()` — para exponer scope_summary al frontend
- `CashController::closeCheck()` — para contextualizar bloqueos
- cualquier endpoint que hoy construye contexto de turno/caja manualmente

### Reportes que reutilizarán este summary

- No alimenta reportes directamente.
- Alimenta el contexto de todos los demás builders.

### Componentes frontend que dependerán de este summary

- Alerta de scope en liquidaciones de cajera
- Indicador de sesión activa en `cash/index.vue`
- `frontend/src/pages/nightpos/settlements/*.vue` — el banner de contexto ya implementado

---

## 3.6 FinancialDashboardAssembler

### Responsabilidad única

Ensamblar los cinco summaries en un payload financiero compuesto y coherente para un endpoint específico, sin realizar ningún cálculo propio.

Es el único punto de entrada a los builders para los casos de uso.

### Entradas

```
tenantId: int
branchId: int
sessionId: int
userId?: int
openingAmount: string
storedExpectedAmount?: string
declaredClosingAmount?: string
differenceAmount?: string
status: string
includeMovementDetail: bool
filters: array  // para scope
```

### Salidas — payload compuesto

```json
{
  "sales_summary": { ... },        // SalesSummaryData
  "cash_summary": { ... },         // CashSummaryData
  "movement_summary": { ... },     // MovementSummaryData
  "settlement_summary": { ... },   // SettlementSummaryData
  "scope_summary": { ... },        // ScopeSummaryData
  "financial_summary": { ... }     // campo de compatibilidad legacy TRANSITORIO
}
```

El campo `financial_summary` deberá rellenarse con los mismos campos actuales durante la fase de compatibilidad, para que el frontend no se rompa hasta que migre a los nuevos summaries.

### Dependencias

- `SalesSummaryBuilder`
- `CashSummaryBuilder`
- `MovementSummaryBuilder`
- `SettlementSummaryBuilder`
- `ScopeSummaryBuilder`

### Qué lógica abandona el sistema legacy

- `CashSessionFinancialSummaryBuilder::build()` reemplazado por `FinancialDashboardAssembler::assemble()`
- los use cases ya no inyectan ni llaman directamente a `CashSessionFinancialSummaryBuilder`

### Controladores que lo consumirán directamente

- `GetCurrentCashSessionUseCase` → usa `FinancialDashboardAssembler`
- `GetCashSessionUseCase` → usa `FinancialDashboardAssembler`
- `GetCashSessionAdminUseCase` → usa `FinancialDashboardAssembler`
- `GetCashSessionsSummaryAdminUseCase` → itera sesiones y llama por cada una
- `ListCashSessionsAdminUseCase` → itera sesiones y llama por cada una
- `ForceCloseCashSessionAdminUseCase` → para snapshot

---

## 4. Qué hace cada builder y qué no hace

| Builder | Calcula | No calcula |
|---|---|---|
| `SalesSummaryBuilder` | ventas, métodos de cobro, servicios vendidos | caja física, movimientos, liquidaciones |
| `CashSummaryBuilder` | efectivo esperado, arqueo, conciliación métodos | ventas, desglose por categoría, liquidaciones |
| `MovementSummaryBuilder` | libro de movimientos por categoría | arqueo, ventas por método, settlement rol |
| `SettlementSummaryBuilder` | pendientes/pagados por rol, ajustes | caja, ventas, movimientos |
| `ScopeSummaryBuilder` | contexto temporal, alcance caja/turno | nada financiero |
| `FinancialDashboardAssembler` | nada propio, solo ensambla | nada propio |

---

## 5. Flujo completo de datos

```
Base de datos (cash_movements, sales, sale_payments, staff_settlements)
         │
         ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  Repositories                                                            │
│                                                                          │
│  CashSessionRepositoryInterface::addMovement()    ← YA integrado Sprint1│
│  SaleRepositoryInterface::sumPaymentsByMethodForSession()               │
│  StaffSettlementRepositoryInterface::{count*, sum*}()                   │
│  EloquentCashSessionRepository::sumMovementsByMethod()  ← wrapper       │
└─────────────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  Summary Builders (Sprint 2)                                             │
│                                                                          │
│  SalesSummaryBuilder      → venta, conteos, métodos de cobro            │
│  CashSummaryBuilder       → efectivo, arqueo, conciliación              │
│  MovementSummaryBuilder   → libro por categoría / family                │
│  SettlementSummaryBuilder → pendientes/pagados por rol                  │
│  ScopeSummaryBuilder      → contexto operativo caja/turno               │
└─────────────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  FinancialDashboardAssembler                                             │
│                                                                          │
│  Ensambla los 5 summaries + genera campo legacy financial_summary        │
│  de compatibilidad transitoria para frontend actual                      │
└─────────────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  Use Cases                                                               │
│                                                                          │
│  GetCurrentCashSessionUseCase         → llama assemble()                │
│  GetCashSessionUseCase               → llama assemble()                 │
│  GetCashSessionAdminUseCase          → llama assemble()                 │
│  GetCashSessionsSummaryAdminUseCase  → itera y llama assemble()         │
│  ListCashSessionsAdminUseCase        → itera y llama assemble()         │
│  ForceCloseCashSessionAdminUseCase   → llama assemble() para snapshot   │
└─────────────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  API (CashController, AdminCashSessionController, ReportController)     │
│                                                                          │
│  GET /cash/session/current                                               │
│  GET /cash/sessions/{id}                                                 │
│  GET /admin/cash-sessions/{id}                                           │
│  GET /admin/cash-sessions/summary                                        │
│  GET /admin/cash-sessions                                                │
│  GET /reports/daily                                                      │
│  GET /reports/cash                                                       │
│  GET /reports/managerial-daily                                           │
└─────────────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  Frontend                                                                │
│                                                                          │
│  Dashboard Caja → sales_summary + cash_summary + settlement_summary     │
│  Tabla movimientos → movement_summary                                    │
│  Scope alert → scope_summary                                             │
│  Reportes → sales_summary + movement_summary + settlement_summary       │
│  Impresión → cash_summary + settlement_summary + movement_summary       │
│                                                                          │
│  (campo financial_summary sigue funcionando durante la transición)       │
└─────────────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  Reportes / Cierre                                                       │
│                                                                          │
│  CashCloseReportSectionsBuilder → migra internamente a builders nuevos  │
│  ManagerialReportAssemblerService → reutiliza SalesSummaryBuilder       │
│                                   + SettlementSummaryBuilder           │
│  ShiftManagerialSummaryBuilder → reutiliza builders por scope de turno  │
└─────────────────────────────────────────────────────────────────────────┘
         │
         ▼
┌─────────────────────────────────────────────────────────────────────────┐
│  Impresión                                                               │
│                                                                          │
│  CashClosePrintPayloadEnricher → consume summaries nuevos               │
│  PrintTicketContentBuilder → listo para exponer movement_category       │
│                               en tickets cuando el sprint finalice      │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 6. Estrategia de compatibilidad durante la transición

Para no romper el frontend actual mientras se migra:

1. `FinancialDashboardAssembler` genera internamente el campo `financial_summary` usando los resultados ya calculados por los builders nuevos.
2. Ningún builder lee desde `financial_summary`. Solo lo genera el assembler para output.
3. El campo `financial_summary` se declara formalmente como deprecated en la API una vez que todos los endpoints frontend migren.
4. El campo `sales_by_method` heredado en `financial_summary` puede calcularse desde `SalesSummaryBuilder.sales_by_method`.
5. El campo `total_manual_income` heredado puede derivarse de `MovementSummaryBuilder.by_category.MANUAL_INCOME`.
6. El campo `total_manual_expense` heredado puede derivarse de `MovementSummaryBuilder.by_family.EXPENSE.expense`.

---

## 7. Clases que se vuelven obsoletas al terminar Sprint 2

### Candidatas a desaparecer

| Clase | Reemplazada por | Cuándo |
|---|---|---|
| `CashSessionFinancialSummaryBuilder` | `FinancialDashboardAssembler` | Final Sprint 2 |
| `EloquentCashSessionRepository::sumManualMovements()` | `MovementSummaryBuilder` agrupado por family | Final Sprint 2 |
| `EloquentCashSessionRepository::sumMovements()` | `MovementSummaryBuilder` totales | Sprint 3 |

### Candidatas a simplificarse

| Clase | Qué simplificar |
|---|---|
| `CashCloseReportSectionsBuilder` | mover cálculo de `movements_summary` al `MovementSummaryBuilder` |
| `EloquentReportReadRepository::getDailySummary()` | reemplazar heurísticas por GROUP BY movement_category |
| `ManagerialReportAssemblerService` | reutilizar builders en lugar de builders propios duplicados |

---

## 8. Riesgos técnicos de implementar este diseño

1. **Número de queries aumenta durante transición**
   - mitigación: los builders pueden compartir una misma consulta inicial y distribuir los resultados

2. **Costo de iterar sesiones en `ListCashSessionsAdminUseCase`**
   - hoy ya itera y llama `financials.build()` por cada sesión
   - el assembler nuevo no empeora eso, pero no lo mejora de inmediato
   - mitigación: agregar caché opcional de summaries en Sprint 3

3. **Campo `financial_summary` sigue siendo transitorio**
   - si el frontend no migra a tiempo, el campo legacy permanece activo más tiempo del esperado

4. **`SettlementSummaryBuilder` y `SettlementOperationalContextBuilder` se solapan**
   - deben coordinarse para no duplicar queries de pendientes
   - mitigación: el `SettlementSummaryBuilder` puede consumir desde el contexto existente o al revés

---

## 9. Orden de implementación del Sprint 2

### Etapa 1 — Value Objects y DTOs

Crear los DTOs formales:

- `SalesSummaryData`
- `CashSummaryData`
- `MovementSummaryData`
- `SettlementSummaryData`
- `ScopeSummaryData`
- `FinancialSnapshotData`

### Etapa 2 — Builders atomizados

En este orden por dependencia:

1. `ScopeSummaryBuilder`
2. `SalesSummaryBuilder`
3. `MovementSummaryBuilder`
4. `CashSummaryBuilder`
5. `SettlementSummaryBuilder`
6. `FinancialDashboardAssembler`

### Etapa 3 — Registro en el DI container

Agregar al `NightPosServiceProvider`.

### Etapa 4 — Migrar use cases

En este orden:

1. `GetCurrentCashSessionUseCase`
2. `GetCashSessionAdminUseCase`
3. `GetCashSessionUseCase`
4. `GetCashSessionsSummaryAdminUseCase` y `ListCashSessionsAdminUseCase`
5. `ForceCloseCashSessionAdminUseCase`

### Etapa 5 — Migrar builders de reportes y cierre

1. `CashCloseReportSectionsBuilder`
2. `EloquentReportReadRepository::getDailySummary()`

### Etapa 6 — Tests

Suite dedicada:

- cada builder con entradas conocidas
- assembler con consistencia viejo/nuevo
- expected_cash invariance
- regresión de uso de builders en use cases principales

---

## 10. Conclusión

Este diseño no agrega complejidad. La elimina.

Hoy existe un builder monolítico que mezcla cinco responsabilidades distintas y es inyectado por siete use cases diferentes.

Este diseño reemplaza ese builder por cinco builders con responsabilidad única, un assembler de coordinación, y una estrategia de compatibilidad que permite migrar el frontend de forma incremental sin romper nada.

El resultado final es una arquitectura donde cualquier pregunta financiera tiene un único lugar donde buscar la respuesta.

---

## 11. Actualización Sprint 2A (2026-07-11) — Validación real en MySQL local `nigtpos`

Se completó la validación operativa de `CashSummaryBuilder` sobre base local real (`APP_ENV=local`, `DB_CONNECTION=mysql`, `DB_DATABASE=nigtpos`) con migración dirigida y sin ejecutar `migrate` general.

### 11.1 Seguridad de ejecución

- Backup previo generado: `storage/backups/nigtpos_before_financial_taxonomy_2026_07_10.sql`.
- Tamaño backup verificado mayor a cero.
- Se aplicó solo la migración:
  - `database/migrations/2026_07_10_120000_add_financial_taxonomy_to_cash_movements_and_reasons.php`
- Post `migrate:status`: la migración anterior quedó en `Ran` (batch 4) y no se ejecutaron migraciones adicionales.

### 11.2 Integridad de datos y backfill

- `cash_movements` antes/después: `289` filas (sin variación).
- Filas sin taxonomía (`movement_family` o `movement_category` nulos): `0`.
- Backfill clasificado en familias/categorías esperadas:
  - `SALE` (`SALE_COLLECTION`, `DIRECT_SALE_COLLECTION`, `ROOM_SERVICE_COLLECTION`)
  - `MANUAL` (`MANUAL_INCOME`)
  - `SETTLEMENT` (`SETTLEMENT_GIRL_PAYMENT`, `SETTLEMENT_WAITER_PAYMENT`, `SETTLEMENT_CLEANING_PAYMENT`)
  - `EXPENSE` (`OPERATING_EXPENSE`, `PURCHASE`, `OTHER_EXPENSE`)

### 11.3 Validación CashSummaryBuilder (4 cajas representativas)

Sesiones usadas:

- abierta: `27`
- cerrada: `26`
- pago mixto: `19`
- liquidaciones pagadas: `25`

Resultado: comparación `CashSummaryBuilder` vs SQL independiente (agregaciones separadas, sin joins multiplicadores) en los 4 casos sin diferencias en buckets ni en fórmula de `expected_cash`.

### 11.4 Comparación nuevo vs legacy

- `expected_cash` nuevo y legacy coinciden en las 4 sesiones.
- Diferencias observadas en `total_income/total_expense` legacy:
  - legacy mezcla métodos (`CASH`, `QR`, `CARD`) en totales globales;
  - `total_manual_*` legacy usa heurística textual (`description not like ...`) y no taxonomía estructurada.
- El cálculo nuevo queda más estable al depender de `movement_family/movement_category` + filtro `payment_method = CASH` para caja física.

### 11.5 Regresión

Se ejecutaron suites financieras clave y quedaron en verde:

- `CashSummaryBuilderTest`
- `SalesSummaryBuilderTest`
- `CashApiTest`
- `AdminCashSessionsTest`
- `DirectSaleApiTest`
- `ChargeOrderApiTest`
- `SettlementPaymentCashSessionTest`
- `SettlementPaymentMethodTest`

Estado Sprint 2A (`CashSummaryBuilder`): **APROBADO CON OBSERVACIONES**

Observación principal: la consistencia del entorno real depende de mantener aplicada la migración de taxonomía en cualquier copia local usada para validaciones financieras.
