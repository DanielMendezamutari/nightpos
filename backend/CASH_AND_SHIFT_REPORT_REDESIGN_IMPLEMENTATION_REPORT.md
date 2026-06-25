# Cierre de caja y cierre de turno — Rediseño V1 (Backend)

**Fecha:** 2026-06-21  
**Alcance:** Evolución de reportes existentes (sin reportes nuevos ni consultas paralelas duplicadas).

## Objetivo

Separar definitivamente:

1. **Cierre de caja (cajera)** — arqueo, métodos de pago, movimientos, liquidaciones pagadas en sesión, ajustes, pendientes.
2. **Cierre de turno (administrador)** — resumen gerencial: resultado financiero, liquidaciones, productos, personal, comandas, incidencias, KPIs.

## Arquitectura

| Componente | Rol |
|------------|-----|
| `CashCloseReportSectionsBuilder` | Secciones operativas por `cash_session_id` (ventas, métodos, movimientos, liquidaciones pagadas, ajustes, pendientes) |
| `ShiftManagerialSummaryBuilder` | Resumen gerencial por `official_shift_id` reutilizando `ReportReadRepository` |
| `CashClosePrintPayloadEnricher` | Enriquece payload térmico/PDF cierre caja |
| `ShiftClosePrintPayloadEnricher` | Enriquece payload térmico/PDF cierre turno |
| `PrintTicketContentBuilder::buildCashClose()` | Ticket cajera 58/80 mm |
| `PrintTicketContentBuilder::buildShiftClose()` | Ticket gerencial 58/80 mm |

## Fuentes de datos (sin recálculo inventado)

- `cash_sessions`, `cash_movements`, `sales`, `sale_payments`
- `staff_settlements`, `staff_settlement_adjustments`
- `orders`, `room_services`, `shows`
- `shift_closures`, `print_jobs`
- Reportes existentes: `getSalesReport`, `getSettlementsReport`, `getServicesReport`, `getProductReconciliation`

## API extendida

| Endpoint | Campo nuevo |
|----------|-------------|
| `GET /cash/sessions/{id}` | `operational` |
| `GET /admin/cash-sessions/{id}` | `operational` |
| `GET /shifts/{id}/summary` | `managerial` |

Los print jobs `CASH_CLOSE` y `SHIFT_CLOSE` incluyen los mismos bloques en `payload`.

## Bloques cierre de caja

- Información general (empresa, sucursal, caja, turno, cajera, admin, duración)
- Resumen de ventas (total, cantidad, ticket promedio)
- Métodos de pago (efectivo/QR/tarjeta/mixto con cantidad y monto)
- Arqueo (inicial, esperado, declarado, diferencia, QR/tarjeta esperados)
- Movimientos de caja (ingresos/egresos + detalle)
- Liquidaciones pagadas (totales + detalle por persona: garzones/chicas/limpieza)
- Ajustes (multas, limpieza descontada, descuentos manuales)
- Pendientes (liquidaciones, comandas, piezas, shows)
- Incidencias + observaciones

## Bloques cierre de turno

- Información general + cajas cerradas + cajeros
- Resumen general + métodos de pago con %
- **Resultado financiero** (ventas − egresos = venta neta)
- Liquidaciones con detalle por persona
- Ajustes + top 20 productos + categorías
- Garzones (ranking) + piezas/shows + comandas + incidencias + KPIs

## Tests

`tests/Feature/Api/V1/CashMovementAndClosurePrintTest.php` — **11/11 PASS**

`tests/Feature/Api/V1/CashCloseSessionTimestampsTest.php` — **3/3 PASS** (apertura/cierre distintos en API, ticket y BD)

## Bugfix timestamps (2026-06-25)

**Causa:** MySQL agregó `ON UPDATE CURRENT_TIMESTAMP` a `cash_sessions.opened_at` (tipo TIMESTAMP), sobrescribiendo la hora de apertura al cerrar.

**Fix:** migración `2026_06_25_120000_fix_cash_sessions_opened_at_timestamp` (DATETIME + backfill desde `created_at`), `CashSessionTimestampsResolver` en mappers/API/print, `operational.general.opened_at/closed_at`.

**Ticket ASCII:** `PrintTicketContentBuilder::thermalSafe()` elimina em-dash, middle dot y otros Unicode en cierre de caja térmico.

## Branding

Footer configurable: `config('nightpos.printing.ticket_footer')` / `VITE_PRINT_TICKET_FOOTER`.
