# MANAGERIAL DAILY REPORT IMPLEMENTATION REPORT (BACKEND)

Fecha: 2026-07-03
Estado: implementado Fase 1
Scope: Reportes gerenciales owner/admin

## 1. Objetivo implementado

Se implemento el endpoint de Reporte Gerencial Diario Fase 1 con calculos 100% backend, reusando repositorio de reportes y sin modificar caja operativa, liquidaciones operativas, DocumentSequence, impresion ni PWA.

## 2. Endpoint implementado

- GET /api/v1/reports/managerial-daily

Middleware aplicado (heredado del grupo de reportes):

- nightpos.branch:required
- nightpos.branch.access
- nightpos.permission:reports.access

## 3. Backend agregado

### 3.1 Use case

- App\Application\Reports\UseCases\GetManagerialDailyReportUseCase

Responsabilidad:

- resolver tenant/branch context
- extraer filtros
- delegar ensamblado al servicio gerencial

### 3.2 Servicio ensamblador

- App\Application\Reports\Services\ManagerialReportAssemblerService

Responsabilidad:

- construir payload gerencial final con secciones:
  - scope
  - kpis
  - waiter_rankings
  - girl_rankings
  - product_rankings
  - hourly_performance
  - room_performance
  - cash_health
  - alerts
  - managerial_formula
- aplicar formula neto en backend
- incluir garzones con compensacion 0 cuando tienen ventas

### 3.3 Reutilizacion de repositorio existente

Se reutilizan metodos existentes:

- getDailySummary
- getSalesReport
- getCashReport
- getServicesReport
- getSettlementsReport
- getRoomsReport
- getShiftClosureCheck
- getProductReconciliation

Se agregaron capacidades especificas para gerencial en el contrato y repositorio:

- getManagerialScopeShiftIds
- getManagerialHourlyPerformance

Tambien se enriquecieron filas para evitar calculos en frontend:

- sales report: waiter_user_id, waiter_name
- settlements report: staff_user_id, net_amount, compensation_mode, manual_amount_input

## 4. Estructura de payload entregada

El endpoint devuelve:

- scope: tenant/branch/rango/shift_ids
- kpis: ventas por metodo, caja, liquidaciones, neto
- waiter_rankings:
  - top_waiters_by_sales
  - top_waiters_by_compensation
- girl_rankings:
  - top_girls_by_generated_income
- product_rankings:
  - top_products_by_revenue
  - top_products_by_units
- hourly_performance:
  - granularity
  - best_hour_by_revenue
  - hourly_buckets
- room_performance:
  - top_rooms_by_revenue
  - rooms_summary
- cash_health:
  - sessions
  - differences
  - totals
- alerts:
  - blockers
  - warnings
  - pending_summary
- managerial_formula:
  - gross_revenue
  - outflows_settlements
  - outflows_cash_expenses
  - net_house_estimated
  - formula_text

## 5. Formula implementada

- gross_revenue = total_sales
- net_house_estimated = gross_revenue - settlements_paid_total - manual_expense

Expuesta como valores y texto de formula en managerial_formula.

## 6. Tests backend implementados

Archivo: tests/Feature/Api/V1/ReportsTest.php

Casos nuevos agregados y pasando:

1. managerial 1. carga reporte con fecha
2. managerial 2. respeta tenant branch
3. managerial 3. incluye ventas por metodo
4. managerial 4. incluye ranking garzones
5. managerial 5. incluye ranking chicas
6. managerial 6. incluye top productos
7. managerial 7. incluye rendimiento por hora
8. managerial 8. incluye alertas
9. managerial 9. incluye garzones con comision 0 si tuvieron ventas

Ejecucion validada:

- php artisan test tests/Feature/Api/V1/ReportsTest.php
- Resultado: 20 tests passed

## 7. Restricciones respetadas

- no cambios en caja operativa
- no cambios en logica operativa de liquidaciones
- no cambios en DocumentSequence
- no cambios en impresion backend
- no cambios en PWA
- no alcance de Fase 2
- no multi-sucursal consolidado

## 8. Riesgos pendientes

- El ensamblado reutiliza consultas existentes que en algunos escenarios pueden crecer en costo (N+1 historico en reportes base).
- Formula de neto en Fase 1 usa total_sales como fuente principal; si se requiere conciliacion contable fina por servicios externos no registrados en sales, queda para Fase 2.
- Alertas agregadas por shift se basan en close-check existente y su semantica operativa actual.
