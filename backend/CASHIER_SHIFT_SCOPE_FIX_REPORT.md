# CASHIER_SHIFT_SCOPE_FIX_REPORT.md (Backend)

**Fecha:** 2026-06-15  
**Estado:** Completado

---

## Problema

La cajera veía comandas y cobros mezclados con otros turnos, días o cajeras.

## Solución

### Filtros en `ListOrdersUseCase`

Con `cashier_scope=1`:

- `official_shift_id` = turno abierto actual (si no hay turno → lista vacía)
- Tenant + sucursal del contexto operativo

Con `current_session=1` (pestaña cobradas recientes):

- Join vía `sales.cash_session_id` = caja abierta del usuario
- `sales.cashier_user_id` = cajera logueada

### Scope `cashier_chargeable`

Estados: `OPEN`, `SENT_TO_BAR` únicamente.

### Mi Caja

Sin cambio de API: `GET /cash/session/current` ya devuelve solo la sesión del usuario. Ventas y movimientos vienen embebidos en esa sesión.

### Admin / fiscalización

Rutas `admin/cash-sessions/*` sin cambios — tests verifican listado admin intacto.

---

Ver también: `CASHIER_CLOSE_CHECK_REPORT.md`
