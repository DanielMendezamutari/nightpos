# SPRINT 4A REDISENO DASHBOARD CAJA IMPLEMENTATION REPORT

Fecha: 2026-07-12
Modulo: Frontend caja (`/nightpos/cash`)

## 1) Objetivo
Implementar el rediseño Sprint 4A del dashboard de caja para cajera priorizando `financial_dashboard` como fuente canónica y usando `financial_summary` solo como fallback temporal.

## 2) Alcance implementado
- Se reescribió la pantalla principal de caja para consumir:
  - `financial_dashboard.cash_summary`
  - `financial_dashboard.sales_summary`
  - `financial_dashboard.movement_summary`
  - `financial_dashboard.settlement_summary`
  - `financial_dashboard.scope_summary`
- Se mantuvo fallback legacy controlado mediante advertencia visible cuando `financial_dashboard` no existe.
- Se preservaron acciones operativas existentes:
  - abrir caja
  - ingreso/egreso manual
  - cerrar caja
  - venta directa
  - imprimir arqueo y comprobante de cierre
- Se preservó integración con SSE para refresco operativo.

## 3) Componentes Sprint 4A usados
1. `CashPhysicalSummaryCard`
2. `CashPendingOperationsPanel`
3. `CashSalesSummaryPanel`
4. `CashMovementSummaryPanel`
5. `CashScopeContextAlert`

Orden en UI (prioridad cajera):
1. Caja fisica
2. Pendientes operativos
3. Ventas
4. Movimientos
5. Contexto caja/turno

## 4) Cambios principales en la pantalla
Archivo: `src/pages/nightpos/cash/index.vue`

- Se eliminaron bloques KPI legacy y tabla de movimientos legacy no alineados al nuevo diseño.
- Se agregaron `computed` para normalizar payload canónico/fallback sin modificar lógica backend.
- Se añadió estado de error API con acción de reintento en la pantalla sin caja abierta.
- Se estandarizó el diálogo de cierre para declarar efectivo/QR/tarjeta y mostrar diferencias por método.

## 5) Evidencia de pruebas frontend
Se añadió spec de comportamiento para Sprint 4A:
- `src/pages/nightpos/cash/__tests__/index.dashboard4a.spec.js`

Escenarios cubiertos (10):
1. render de caja física
2. expected_cash centrado en cash_summary (sin mezclar QR/Tarjeta)
3. pendientes por rol (garzón/chica/limpieza)
4. ventas por método
5. movimientos por categoría
6. advertencias de contexto/multiturno
7. fallback legacy explícito
8. error API visible con reintento
9. estado sin caja abierta
10. acciones existentes disponibles

Resultado de ejecución:
- Test Files: 1 passed
- Tests: 10 passed

Comando ejecutado:
- `npm.cmd test -- src/pages/nightpos/cash/__tests__/index.dashboard4a.spec.js`

## 6) Restricciones respetadas
- No se cambió lógica financiera backend.
- No se cambiaron endpoints ni permisos/policies.
- No se tocó impresión agente/hosting/PWA.
- No se ejecutó build por instrucción del usuario.

## 7) Pendientes de validación
1. Validación funcional con casos MySQL reales solicitados por usuario (cajas 27/25/19/26).
2. Build final de frontend a ejecutar por el usuario.
3. Ajustes finos de UX según feedback de operación real.
