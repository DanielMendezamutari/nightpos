# ORDERS_SCOPE_KPI_FIX_REPORT (Frontend)

## 1. Problema anterior

`/nightpos/orders` llamaba `fetchOrders('OPEN')` y mostraba siempre *"No hay comandas abiertas"*, aunque existieran comandas en `SENT_TO_BAR`.

La cajera sí veía esas comandas vía `scope=cashier_chargeable`, generando inconsistencia entre roles.

Dashboard y consola de turno enlazaban al listado genérico (solo OPEN).

## 2. Scopes consumidos

Nueva función API: `fetchOrdersByScope(scope)` → `GET /orders?scope=...`

Tabs usan los mismos nombres que el backend (`operational_active`, `open`, `sent_to_bar`, etc.).

## 3. Nueva semántica en UI

| Tab / KPI | Significado |
|-----------|-------------|
| Activas | OPEN + SENT_TO_BAR (default en listado admin) |
| Abiertas | Solo OPEN |
| En barra | SENT_TO_BAR |
| Pendientes de cobro | SENT_TO_BAR (+ futuros READY/IN_PREPARATION) |
| Cobradas recientes | BILLED recientes |
| Canceladas | CANCELLED |

Mensajes vacíos específicos por tab (`useOrderListTabs.js`).

## 4. Cambios por pantalla

| Ruta | Cambio |
|------|--------|
| `/nightpos/orders` | VTabs con 6 pestañas; default Activas; query `?tab=`; mensajes vacíos correctos |
| `/nightpos/cashier/orders` | Tabs: Pendientes de cobro, Abiertas/en barra, Cobradas recientes |
| `/nightpos/dashboard` | KPI "Comandas activas" + desglose Abiertas / En barra |
| `/nightpos/shift-console` | Cards enlazan a `?tab=operational_active`, `sent_to_bar`, `pending_charge`; card "En barra" añadida |
| `/nightpos/waiter` | KPI "Pendientes cobro" oculto si el conteo es 0 (sin duplicar SENT_TO_BAR) |

## 5. KPIs corregidos

- **Dashboard:** `activeOrdersCount` vía `operational_active`; subtítulo con `openOrdersCount` y `sentToBarOrdersCount`.
- **Consola turno:** usa `active_orders`, `sent_to_bar_orders`, `pending_charge_orders` del backend.
- **Garzón:** no muestra tarjeta "Pendientes cobro" cuando solo hay SENT_TO_BAR (KPI backend = 0).

## 6. Validación manual

1. Garzón crea comanda → visible en Activas y Abiertas.
2. Envía a barra → visible en Activas y En barra; tab Activas ya no muestra mensaje de "abiertas".
3. Admin dashboard muestra total activas correcto.
4. Consola turno: card "En barra" → `/nightpos/orders?tab=sent_to_bar`.
5. Consola turno: card "Pendientes cobro" → `/nightpos/orders?tab=pending_charge`.
6. Cajera: tab Pendientes de cobro lista SENT_TO_BAR; tab Abiertas/en barra permite corregir.

## 7. Archivos tocados

- `src/api/orders.js` — `fetchOrdersByScope`
- `src/composables/useOrderListTabs.js` — tabs y mensajes
- `src/composables/useDashboardOperationalStats.js` — KPIs activas
- `src/pages/nightpos/orders/index.vue`
- `src/pages/nightpos/cashier/orders/index.vue`
- `src/pages/nightpos/dashboard.vue`
- `src/pages/nightpos/shift-console/index.vue`
- `src/pages/nightpos/waiter/index.vue`

## 8. Pendiente

- Venta directa de caja.
- Reportes e impresión de comandas.
- Integración barra real (IN_PREPARATION / READY en UI garzón cuando haya flujo).
