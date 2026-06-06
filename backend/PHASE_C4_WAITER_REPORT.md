# Fase C4 — Modo garzón móvil (Backend)

## 1. Flujo actual del garzón

- Login PIN (`garzon.demo` / `5678`) con contexto tenant + sucursal.
- Redirección frontend a `/nightpos/waiter` (no dashboard administrativo).
- El garzón abre comandas propias (auto-asignación como `waiter_user_id`).
- Agrega ítems (`SOLO_CLIENTE` / `CON_ACOMPANANTE`, opcional `girl_user_id`).
- Envía a barra; la cajera cobra con `sales.charge`.

## 2. Mejoras implementadas

| Área | Detalle |
|------|---------|
| API garzón | `GET /api/v1/waiter/dashboard`, `GET /api/v1/waiter/orders`, `GET /api/v1/waiter/orders/active` |
| Aislamiento | `WaiterOrderAccessPolicy` — solo comandas con `waiter_user_id` = usuario actual |
| Permisos | `waiter.dashboard`, `waiter.orders`, `orders.create`, `orders.add_items`, `orders.send_to_bar` |
| Rutas comanda | POST ítems / send-to-bar con permisos granulares |

## 3. Endpoints

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/waiter/dashboard` | `waiter.dashboard` |
| GET | `/api/v1/waiter/orders?scope=` | `waiter.orders` |
| GET | `/api/v1/waiter/orders/active` | `waiter.orders` |
| POST | `/api/v1/orders` | `orders.create` |
| POST | `/api/v1/orders/{id}/items` | `orders.add_items` |
| POST | `/api/v1/orders/{id}/send-to-bar` | `orders.send_to_bar` |

Scopes: `active`, `open`, `sent_to_bar`, `pending_charge`.

## 4. Reglas de negocio

- Garzón no cobra (`sales.charge` no asignado al rol waiter).
- Garzón no accede a caja (`cash.access` ausente).
- Admin/cajera ven todas las comandas vía `GET /orders`.
- Chica en ítem: opcional al agregar; obligatoria antes de enviar a barra si `CON_ACOMPANANTE`.

## 5. Tests

`tests/Feature\Api/V1/PhaseC4WaiterTest.php` — **9** casos (incl. `service_area_id`, `waiter/girls` y rechazo sin mesa/ambiente). Ver `backend/WAITER_MOBILE_FIX_REPORT.md`.

## 6. Seed demo garzón (`NightPosSeeder`)

Tras `php artisan migrate:fresh --seed`:

| Dato | Detalle |
|------|---------|
| Garzón | `garzon.demo` — PIN **5678** |
| Turno | **Turno Demo Garzón** (abierto) |
| Bebidas | 13 productos (Bebidas, Tragos, Cócteles) con precio SOLO y CON_ACOMPANANTE |
| Ambientes | Mesa 1, Mesa 2, VIP, Barra |
| Chicas | `chica.centro`, `chica2.demo`, `chica3.demo` (CON_ACOMPANANTE) |
| Comandas `garzon.demo` | `W-DEMO-01` abierta con cervezas · `W-DEMO-02` VIP vacía · `W-DEMO-03` en barra · `W-DEMO-04` lista cobro · `W-DEMO-05` en preparación |
| Otra mesa | `W-DEMO-G2` de `garzon2.demo` (no visible para garzón 1) |

Numeración operativa nueva sigue `C-0001`, `C-0002`… (las `W-DEMO-*` no consumen correlativo).

## 7. Validación manual

1. PIN `5678` → modo garzón.
2. Dashboard con contadores.
3. Nueva comanda + ambiente rápido.
4. Agregar bebida SOLO y CON_ACOMPANANTE.
5. Enviar a barra.
6. Cajero PIN `1234` cobra la misma comanda.

## 8. Pendientes

- Notificaciones push al garzón cuando comanda lista para cobro.
- Categorías de producto en API dedicada garzón (hoy usa `products.list`).
- Modo offline / PWA instalable.
