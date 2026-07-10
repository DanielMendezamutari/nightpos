# MANAGERIAL REPORTS PHASE 1 PROPOSAL (BACKEND)

Fecha: 2026-07-03
Estado: propuesta funcional y tecnica
Modo: sin implementacion
Enfoque: owner/administracion, no cajera

## 1. Objetivo de Fase 1

Diseñar un Reporte Gerencial Diario para responder, en una sola vista y en un solo payload, estas 13 preguntas:

1. Cuanto vendio la sucursal
2. Cuanto entro por efectivo, QR y tarjeta
3. Cuanto se pago en liquidaciones
4. Cuanto quedo realmente en caja
5. Que garzones vendieron mas
6. Que garzones recibieron mas pago/comision/manual
7. Que chicas generaron mas
8. Que productos se vendieron mas
9. Que horario vendio mejor
10. Que habitaciones/piezas generaron mas
11. Que pendientes o alertas quedaron
12. Que diferencias de caja hubo
13. Cual fue el neto estimado para la casa

## 2. Endpoint propuesto

Ruta propuesta:

- GET /api/v1/reports/managerial-daily

Permisos propuestos:

- reports.access (base)
- (opcional hardening) reports.managerial.access para separar analitica gerencial de reportes operativos

Middleware esperado:

- nightpos.branch:required
- nightpos.branch.access
- nightpos.permission:reports.access (o reports.managerial.access si se crea)

## 3. Filtros propuestos

Query params de Fase 1:

- date_from (opcional)
- date_to (opcional)
- official_shift_id (opcional)
- include_rankings_limit (opcional, default 10)
- include_hours_granularity (opcional: hour_24, hour_block)

Reglas de scope:

- Si llega official_shift_id, prevalece sobre date range.
- Si no llega official_shift_id ni fechas, usar ultimo turno disponible (o ultimos 1-3 dias segun politica de producto).
- Siempre scoped por tenant + branch de contexto.

## 4. Payload propuesto

Estructura propuesta:

- scope
  - tenant_id
  - branch_id
  - official_shift_ids
  - date_from
  - date_to
- kpis
  - total_sales
  - total_cash
  - total_qr
  - total_card
  - total_mixed
  - settlements_paid_total
  - settlements_pending_total
  - expected_cash_total
  - declared_cash_total
  - cash_difference_total
  - net_house_estimated
- waiter_rankings
  - top_waiters_by_sales[]
  - top_waiters_by_compensation[]
- girl_rankings
  - top_girls_by_generated_income[]
- product_rankings
  - top_products_by_revenue[]
  - top_products_by_units[]
- hourly_performance
  - best_hour_by_revenue
  - hourly_buckets[]
- room_performance
  - top_rooms_by_revenue[]
  - rooms_summary
- cash_health
  - sessions[]
  - differences[]
- alerts
  - blockers[]
  - warnings[]
  - pending_summary
- managerial_formula
  - gross_revenue
  - outflows_settlements
  - outflows_cash_expenses
  - net_house_estimated
  - formula_text

## 5. Use case propuesto

Caso de uso nuevo:

- GetManagerialDailyReportUseCase

Responsabilidades:

- validar contexto tenant/branch
- resolver scope (shift o rango)
- orquestar agregados del repositorio
- armar payload final gerencial
- no incluir logica de presentacion UI

No debe hacer:

- logica de impresion
- logica de cierre de caja
- reglas de settlement/payment operativas

## 6. Repositorio y reutilizacion (sin duplicar logica)

Estrategia propuesta:

Extender ReportReadRepositoryInterface con metodos gerenciales atomicos, reutilizando piezas ya existentes en EloquentReportReadRepository y servicios actuales.

Metodos reusables directos ya existentes:

- getDailySummary
- getSalesReport
- getServicesReport
- getSettlementsReport
- getRoomsReport
- getShiftClosureCheck
- getProductReconciliation

Servicios reusables existentes:

- ShiftManagerialSummaryBuilder (ya agrega bloques clave de gerencia)
- CashCloseReportSectionsBuilder (bloques de caja y liquidaciones pagadas)
- ComboBraceletReportingService (si aplica datos de combos/manillas)

Nueva capa recomendada para no duplicar:

- ManagerialReportAssemblerService
  - combina metodos existentes + nuevas consultas agregadas faltantes
  - centraliza formula gerencial

## 7. Queries necesarias de Fase 1

Las siguientes consultas faltan o deben consolidarse para responder las 13 preguntas en una sola respuesta:

### 7.1 Ventas globales y por metodo

- SUM sales.total por scope
- SUM sale_payments.amount agrupado por payment_method

### 7.2 Liquidaciones pagadas y pendientes

- SUM net_amount o total_amount de staff_settlements por status
- breakdown por settlement_type y por staff

### 7.3 Efectivo real y diferencia de caja

- SUM cash_sessions.expected_amount
- SUM cash_sessions.declared_closing_amount
- SUM cash_sessions.difference_amount
- detalle por sesion para trazabilidad

### 7.4 Ranking garzones

- top_waiters_by_sales:
  - SUM sales.total group by waiter_user_id
- top_waiters_by_compensation:
  - SUM staff_settlements total/net para settlement_type WAITER status PAID
  - incluir compensation_mode y manual_amount_input para separar auto/manual

### 7.5 Ranking chicas

- top_girls_by_generated_income:
  - SUM room_services.girl_amount
  - SUM shows total ligado a girl
  - SUM allocations/manillas cuando existan
  - SUM settlement GIRL pagado como metrica secundaria

### 7.6 Ranking productos

- top_products_by_revenue:
  - SUM sale_items.line_total group by product_id
- top_products_by_units:
  - SUM sale_items.quantity group by product_id

### 7.7 Horario de mejor venta

- agrupacion por hora sobre sales.paid_at:
  - SUM total por hour bucket
  - COUNT ventas por bucket

### 7.8 Habitaciones/piezas de mayor ingreso

- SUM room_services.total_amount group by room_id o room_label
- COUNT usos por room
- avg duration por room

### 7.9 Pendientes y alertas

- reutilizar getShiftClosureCheck para blockers/warnings
- agregar pending_settlements, active_orders, active_room_services

### 7.10 Neto estimado de la casa

Formula propuesta para Fase 1:

- gross_revenue = total_sales + total_services_externos_si_no_duplicados
- outflows = settlements_paid_total + manual_expenses
- net_house_estimated = gross_revenue - outflows

Nota de consistencia:

- Evitar doble conteo de servicios si room_services/shows/bracelets ya estan incluidos en sales segun flujo real de cobro.
- En Fase 1, documentar explicitamente la fuente que se usa para revenue principal (sales) y considerar servicios fuera de sales solo si existen fuentes efectivamente no reflejadas en sales.

## 8. Indices necesarios (si aplica)

Con base en auditoria actual, ya existe buena cobertura en varias tablas. Para Fase 1 gerencial, indices adicionales recomendados:

- sales (tenant_id, branch_id, official_shift_id, waiter_user_id, paid_at)
- sales (tenant_id, branch_id, paid_at)
- staff_settlements (tenant_id, branch_id, official_shift_id, settlement_type, status, staff_user_id)
- room_services (tenant_id, branch_id, official_shift_id, room_id, status)
- sale_items (tenant_id, branch_id, product_id, sale_id)
- cash_sessions (tenant_id, branch_id, official_shift_id, status, opened_at)

Objetivo:

- reducir scans y filesort en agregaciones por ranking y hourly buckets.

## 9. Calculos que deben ir en backend

Todos los calculos gerenciales deben resolverse en backend para consistencia:

- KPIs monetarios finales
- rankings (top N)
- hourly buckets y mejor hora
- net_house_estimated
- bloques de alertas y pendientes
- normalizacion de montos y porcentajes

Frontend no debe recalcular formula contable ni rankings; solo renderizar.

## 10. Reportes actuales que se reutilizan

Reutilizables directos:

- daily
- sales
- settlements
- rooms
- shift-closure
- product-reconciliation
- managerial block de shift summary

Reutilizables parciales:

- cash report (sirve como insumo, no como vista gerencial final)
- cash close sections (sirve para bloques operativos y ajustes)

## 11. Reportes actuales que no sirven como salida gerencial final

No suficientes por si solos:

- cash (demasiado operativo por sesion)
- services (detalle operativo, no lectura ejecutiva unificada)
- settlements (detalle transaccional sin narrativa gerencial final)

## 12. Datos faltantes hoy para gerencia

Faltantes funcionales detectados:

- hourly performance consolidado en endpoint de reportes (hoy no existe como salida directa).
- separacion explicita de compensacion waiter auto vs manual en ranking gerencial.
- net_house_estimated explicito y trazable en payload de reportes (hoy aparece en summary de cierre, no en endpoint gerencial dedicado).
- normalizacion unica de fuente de revenue para evitar doble conteo entre sales y servicios.

Faltantes de datos reales en replica auditada:

- sale_item_allocations sin datos
- bracelets sin datos
- shows sin datos
- shift_closures sin datos

## 13. Riesgos de performance

Riesgos actuales a considerar en diseno Fase 1:

- N+1 en cash report y rooms report existentes.
- agregaciones repetidas sobre mismas tablas en diferentes metodos.
- whereDate en grandes volumenes puede degradar uso de indice.
- payload grande si se incluye detalle excesivo en un endpoint gerencial.

Mitigacion en diseno:

- endpoint gerencial con secciones agregadas y top N limitados.
- query batching por dominio (sales/settlements/cash/rooms).
- evitar traer detalle de filas no necesarias para KPIs.

## 14. Alcance fuera de Fase 1 (dejar para Fase 2)

Dejar para Fase 2:

- comparativos inter-dia, WoW, MoM y tendencia historica.
- presupuestos/targets y cumplimiento.
- margenes avanzados por categoria con costo real.
- alertas proactivas y umbrales configurables.
- dashboards multi-sucursal consolidados cross-branch.
- read models/materialized snapshots para alto volumen.
- auditoria avanzada de anomalas y scoring de riesgo.

## 15. Resumen de propuesta backend Fase 1

- crear endpoint gerencial dedicado para owner/administracion
- reutilizar repositorio y servicios actuales
- agregar ensamblador gerencial para evitar duplicacion
- centralizar formula de neto en backend
- entregar payload ejecutivo listo para UI, export e impresion
- mantener caja sin cambios funcionales en esta fase
