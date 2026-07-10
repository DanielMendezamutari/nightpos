# REPORTS MODULE DISCOVERY AUDIT (FRONTEND)

Fecha de auditoria: 2026-07-03
Modo: solo descubrimiento e ingenieria inversa
Alcance: UI, consumo API, filtros, permisos, export, impresion, utilidades y UX por rol

## 1. Arquitectura actual

El consumo frontend de reportes se divide en 3 capas:

- API adapter:
  - frontend/src/api/reports.js
- Pantalla principal de reportes:
  - frontend/src/pages/nightpos/finance/reports/index.vue
- Componentes de soporte:
  - frontend/src/components/nightpos/reports/ProductReconciliationPanel.vue
  - frontend/src/components/nightpos/reports/ComboBraceletSummaryPanel.vue

Consumo transversal (fuera de la pantalla principal, pero dependiente de reportes):

- Caja actual:
  - frontend/src/pages/nightpos/cash/index.vue
- Cierre de turno:
  - frontend/src/pages/nightpos/shifts/close.vue
- Impresion cierre turno/caja:
  - PrintableShiftClosureReport
  - PrintableCashSessionReport
  - paginas print/*
- Fiscalizacion admin de caja:
  - frontend/src/pages/nightpos/finance/cash-sessions/[id].vue

No existe store dedicado a reportes.
No existe composable dedicado a reportes.
El estado de reportes en UI es local por pagina (refs).

## 2. Flujo completo de datos

Flujo principal de Reportes (pantalla finance/reports):

1. Ruta de pagina con definePage permission reports.access.
2. Filtros locales en estado:
   - dateFrom
   - dateTo
   - officialShiftId
   - cashSessionId
   - waiterUserId
3. buildParams en api/reports.js transforma a snake_case.
4. Llamada a endpoint segun tab activa.
5. unwrapNightPosResponse extrae data.
6. Render con VCard, VTable, VChip y paneles de conciliacion/combo.
7. Export CSV client-side desde tabla cargada.

Flujos transversales:

- Caja (cash/index): consulta product-reconciliation por cashSessionId y muestra ProductReconciliationPanel + ComboBraceletSummaryPanel.
- Cierre turno (shifts/close): consulta product-reconciliation por officialShiftId y muestra paneles.
- Impresion turno/caja: renderiza payload de summary/managerial/operational derivado de backend de reportes y builders.

## 3. Diagrama de dependencias

```mermaid
flowchart TD
  P[finance/reports/index.vue] --> A[api/reports.js]
  A --> B1[/reports/daily]
  A --> B2[/reports/sales]
  A --> B3[/reports/cash]
  A --> B4[/reports/services]
  A --> B5[/reports/settlements]
  A --> B6[/reports/rooms]
  A --> B7[/reports/shift-closure]
  A --> B8[/reports/product-reconciliation]

  P --> C1[ProductReconciliationPanel]
  P --> C2[ComboBraceletSummaryPanel]

  CashPage[cash/index.vue] --> A
  ShiftClose[shifts/close.vue] --> A

  PrintShift[print/shift/[id].vue] --> ShiftApi[api/shifts.js]
  ShiftApi --> ShiftSummary[/shifts/{id}/summary]
  PrintShift --> PrintableShiftClosureReport

  PrintCash[print/cash.vue and print/cash-session/[id].vue] --> CashApi[api/cash.js]
  CashApi --> CashSession[/cash/sessions/{id}]
  PrintCash --> PrintableCashSessionReport
```

## 4. Inventario completo de reportes (frontend)

### 4.1 Resumen diario (tab daily)

- Pantalla: nightpos/finance/reports/index.vue
- Endpoint: GET /reports/daily
- Filtros UI: dateFrom, dateTo, officialShiftId, cashSessionId, waiterUserId
- Render:
  - KPIs ventas por metodo
  - KPIs servicios
  - KPIs liquidaciones paid/pending
  - KPI efectivo esperado
  - bloques manillas/piezas/shows/habitaciones usadas

### 4.2 Ventas (tab sales)

- Endpoint: GET /reports/sales
- Render:
  - KPIs total y cantidad
  - tabla de ventas (tipo, cajero, metodo, total, items, fecha)
  - bloque combos con asignaciones
- Export CSV: client-side sobre sales.sales

### 4.3 Productos (tab products)

- Endpoint: GET /reports/product-reconciliation
- Render:
  - ProductReconciliationPanel
  - resumen de diferencias
  - detalle sold/comparison/direct sales
- Export CSV: client-side sobre comparison

### 4.4 Caja (tab cash)

- Endpoint: GET /reports/cash
- Render:
  - KPIs open_count y closed_count
  - cards por session con montos por metodo y movimientos

### 4.5 Servicios (tab services)

- Endpoint: GET /reports/services
- Render:
  - KPIs totals, house, girl, cleaning
  - tabla piezas
  - tabla manillas
  - tabla shows
- Export CSV: client-side merge room_services + bracelets + shows

### 4.6 Liquidaciones (tab settlements)

- Endpoint: GET /reports/settlements
- Render:
  - KPIs generated/paid/pending/count
  - tabla de settlements
  - tabla adicional para GIRL_BRACELET_ALLOCATION cuando existe
- Export CSV: client-side sobre settlements.settlements

### 4.7 Habitaciones (tab rooms)

- Endpoint: GET /reports/rooms
- Render:
  - KPIs rooms_count, rooms_used, total_services, total_income
  - tabla habitaciones con estado/servicios/ingresos
- Export CSV: client-side sobre rooms.rooms

## 5. Inventario de endpoints

### 5.1 Endpoints usados por api/reports.js

- GET /reports/daily
- GET /reports/sales
- GET /reports/cash
- GET /reports/services
- GET /reports/settlements
- GET /reports/rooms
- GET /reports/shift-closure
- GET /reports/product-reconciliation

### 5.2 Endpoints relacionados para vistas e impresion dependientes

- GET /shifts/current/close-check
- GET /shifts/{id}/summary
- GET /shifts/{id}/export.csv
- POST /shifts/{id}/print-closure
- GET /cash/session/current
- GET /cash/session/current/close-check
- GET /cash/sessions/{id}
- POST /cash/sessions/{id}/print-close
- GET /admin/cash-sessions/{id}

## 6. Inventario de consultas (vista frontend)

El frontend no ejecuta SQL directo.
Inventario de consultas HTTP relevantes:

- fetchDailyReport
- fetchSalesReport
- fetchCashReport
- fetchServicesReport
- fetchSettlementsReport
- fetchRoomsReport
- fetchShiftClosureCheck
- fetchProductReconciliation
- fetchShiftSummary
- fetchCashSession / fetchCurrentCashSession
- fetchAdminCashSession

## 7. Inventario de componentes Vue

Componentes principales de reportes:

- ProductReconciliationPanel.vue
- ComboBraceletSummaryPanel.vue

Componentes de impresion que muestran datos de reportes:

- PrintableShiftClosureReport.vue
- PrintableCashSessionReport.vue

Paginas que muestran reportes o derivados:

- pages/nightpos/finance/reports/index.vue
- pages/nightpos/cash/index.vue
- pages/nightpos/shifts/close.vue
- pages/nightpos/print/shift/[id].vue
- pages/nightpos/print/cash.vue
- pages/nightpos/print/cash-session/[id].vue
- pages/nightpos/print/my-cash-session/[id].vue
- pages/nightpos/finance/cash-sessions/[id].vue
- pages/nightpos/finance/cash-sessions/summary.vue

## 8. Inventario de permisos

Permisos de entrada de vistas:

- reports.access:
  - pantalla principal finance/reports
  - visibilidad en navegacion de reportes
- cash.access:
  - vista caja con panel de conciliacion embebido
  - vistas print cash
- shifts.close:
  - vista cierre de turno con conciliacion
- shifts.list:
  - vista print de cierre de turno
- admin.cash_sessions.view:
  - vista detalle fiscalizacion admin de caja
- admin.cash_sessions.summary:
  - vista resumen fiscalizacion

Observacion:

- El menu de reportes se publica por reports.access.
- Aunque un rol no tenga reports.access, puede recibir fragmentos de analitica en caja/cierre si tiene cash.access o shifts.close.

## 9. Inventario de filtros

Filtros visibles en pantalla de reportes:

- Desde (dateFrom)
- Hasta (dateTo)
- ID Turno (officialShiftId)
- ID Caja (cashSessionId)
- ID Garzon (waiterUserId)

Mapeo a query params:

- date_from
- date_to
- official_shift_id
- cash_session_id
- waiter_user_id
- cashier_user_id (soportado en API adapter)
- girl_user_id (soportado en API adapter)
- payment_method (soportado en API adapter)

## 10. Que datos existen (desde perspectiva UI)

Datos con carga visible en replica auditada:

- ventas, caja, liquidaciones, ordenes, productos, categorias.

Datos esperados por UI pero con carga cero en replica auditada:

- combo allocations por manillas
- registros de manillas
- registros de shows
- cierres persistidos de turno

Efecto visual actual:

- varias secciones se muestran con 0 o no se renderizan por condiciones v-if.

## 11. Que datos consume cada reporte (frontend)

Consumo por tab:

- daily:
  - sales.total, total_cash, total_qr, total_card
  - services.total, bracelets_count, room_services_count, shows_count
  - settlements.paid/pending
  - cash.expected_cash
  - rooms.used
- sales:
  - sales[] con items[] y allocations[]
  - totals
- products:
  - comparison[]
  - sold[]
  - summary
- cash:
  - sessions[]
  - open_count / closed_count
- services:
  - room_services[]
  - bracelets[]
  - shows[]
  - totals
- settlements:
  - settlements[]
  - settlements[].items[]
  - totals
- rooms:
  - rooms[]
  - totals

## 12. Que datos nunca se usan (frontend, estado actual)

Detectado en frontend principal de reportes:

- No se consumen stores dedicados para cachear o compartir reportes.
- No hay composables de query/caching para reportes.
- No hay uso de graficos/charts en reportes (solo cards/tablas/chips).

Detectado por replica de datos:

- UI tiene bloques para combo allocations/manillas/shows que actualmente quedan vacios por ausencia de datos en BD auditada.

## 13. Que modulos dependen de Reportes

Dependencias de UI detectadas:

- Finanzas > Reportes (modulo principal)
- Caja (panel conciliacion y combo summary embebido)
- Cierre de turno (panel conciliacion y combo summary embebido)
- Impresion de cierre de turno y cierre de caja
- Fiscalizacion admin de cajas (detalle y resumen operativos)

## 14. Riesgos encontrados (estado actual)

Riesgos frontend observados:

- Export CSV es client-side con shape dependiente del primer row (headers dinamicos).
- No existe paginacion en tablas del modulo principal de reportes.
- Carga de tabs es bajo demanda y sin cache compartido.
- No hay visualizaciones graficas; toda lectura es tabular/KPI textual.
- La pantalla principal expone filtros globales que no siempre aplican a todos los endpoints de la misma forma (backend ignora campos no soportados por use case).

## 15. Deuda tecnica (estado actual)

Deuda de implementacion observada:

- Logica de export CSV repetida localmente en la pagina de reportes.
- Formateo monetario disperso (fmtMoney local y formatMoney helper en multiples vistas).
- Reuso de paneles de conciliacion correcto, pero sin composable de datos report reutilizable.
- Acoplamiento alto entre vistas operativas y payloads amplios de cierre/managerial.

## 16. Estado general del modulo

Estado funcional UI:

- Reportes principal funciona por tabs, filtros globales, tablas y KPIs.
- Integracion transversal activa en caja, cierre turno e impresion.

Estado de acceso por rol (lectura actual):

- Cajero:
  - no tiene reports.access por default.
  - si tiene cash.access puede ver conciliacion embebida en caja.
  - no accede a pantalla principal de reportes por default.
- Administrador/Owner (tenant_owner):
  - tiene reports.access.
  - ve pantalla completa de reportes y, ademas, summary/cierre/fiscalizacion segun permisos incluidos.
- Superadmin:
  - tiene todos los permisos en seed demo.
  - acceso funcional depende de tener contexto tenant/sucursal activo por middleware de branch.
- Admin de fiscalizacion (roles con admin.cash_sessions.*):
  - accede a detalle y resumen de cajas, incluyendo payload operacional derivado de report builders.

Sin propuestas ni cambios en este documento.
