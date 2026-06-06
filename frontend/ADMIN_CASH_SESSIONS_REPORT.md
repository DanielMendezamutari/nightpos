# Fiscalización de cajas (frontend)

## Diferencia operativa vs fiscalización

- **Caja actual** (`/nightpos/cash`): operación del usuario — cobros, movimientos, cierre.
- **Fiscalización de cajas** (`/nightpos/finance/cash-sessions`): vista admin — todas las cajas de la sucursal, sin operar cobros.

## Rutas

| Ruta | Nombre | Vista |
|------|--------|-------|
| `/nightpos/finance/cash-sessions` | `nightpos-finance-cash-sessions` | Cajas abiertas |
| `/nightpos/finance/cash-sessions/history` | `nightpos-finance-cash-sessions-history` | Historial |
| `/nightpos/finance/cash-sessions/summary` | `nightpos-finance-cash-sessions-summary` | Resumen |
| `/nightpos/finance/cash-sessions/by-cashier` | `nightpos-finance-cash-sessions-by-cashier` | Por cajera |
| `/nightpos/finance/cash-sessions/by-shift` | `nightpos-finance-cash-sessions-by-shift` | Por turno |
| `/nightpos/finance/cash-sessions/:id` | `nightpos-finance-cash-sessions-id` | Detalle |

## API

`src/api/adminCashSessions.js`

- `fetchAdminCashSessions(params)`
- `fetchAdminCashSession(id)`
- `fetchAdminCashSessionsSummary(params)`

## Permisos en UI

- Listado / historial / agrupaciones: `admin.cash_sessions.list`
- Resumen: `admin.cash_sessions.summary`
- Detalle: `admin.cash_sessions.view`

Menú: **Finanzas → Fiscalización de cajas** (`nightpos-r4.js`).

## Liquidaciones

En `SettlementsCashBanner`: texto con enlace a fiscalización (si tiene `admin.cash_sessions.list`):

> Para pagar se requiere tu caja abierta. Para revisar otras cajas usa Fiscalización de cajas.

## Cómo usarlo

1. Entrar con admin de sucursal (contexto operativo CENTRO).
2. Ir a Finanzas → Fiscalización de cajas.
3. Pestaña **Cajas abiertas** para control en vivo.
4. **Historial** con filtros fecha/cajera/turno/estado.
5. **Ver** en tabla → detalle con movimientos, ventas y liquidaciones pagadas.

## Validación manual

Ver `backend/ADMIN_CASH_SESSIONS_REPORT.md` pasos 1–7.

No hay suite E2E frontend; validar con `pnpm run dev`.
