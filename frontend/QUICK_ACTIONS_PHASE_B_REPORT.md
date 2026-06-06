# Quick Actions — Fase B (Frontend)

**Fecha:** 2026-06-04  
**Referencia:** `SYSTEM_QUICK_ACTIONS_AUDIT.md`

Fase A intacta: mismos diálogos (`QuickGirlCreateDialog`, `QuickRoomCreateDialog`, etc.).

## Componentes quick action nuevos

| Componente | Archivo | API |
|------------|---------|-----|
| `QuickWaiterCreateDialog` | `staff/QuickWaiterCreateDialog.vue` | `quickCreateWaiter` |
| `QuickShowTypeCreateDialog` | `shows/QuickShowTypeCreateDialog.vue` | `createShowType` |
| `QuickProductPriceCreateDialog` | `catalog/QuickProductPriceCreateDialog.vue` | `createQuickProductPrice` |

## Wizard SaaS (QB-01)

- Ruta: `/nightpos/platform/setup` (`nightpos-platform-setup`)  
- Permiso: `platform.setup` (solo superadmin)  
- `VStepper`: empresa → sucursal → admin → confirmación  
- API: `platformSetup()` → `POST /admin/platform/setup`  
- Botón final: contexto tenant/branch + redirect `nightpos-dashboard`  
- Nav: Plataforma SaaS → **Setup empresa**

## Integraciones

### QB-02 — Garzón rápido

- `pages/nightpos/services/bracelets/create.vue` — selector garzón + `QuickWaiterCreateDialog`  
- Composable: `useOperationalWaiters.js` (`loadOperationalWaitersForSelect`, `appendWaiterToSelectList`)  
- `useGirlIncomeStaffOptions.js` usa el mismo listado de garzones

### QB-03 — Tipos de show

- `shows/create.vue`: tipos desde `fetchShowTypes`, precio sugerido al seleccionar, `QuickShowTypeCreateDialog`  
- Elimina enum fijo PRIVATE/STAGE/SPECIAL en UI

### QB-04 — Precio en comanda

- `OrderAddProductDialog.vue`: alerta si falta precio + botón «Configurar precio ahora»  
- `orders/[id].vue`: `QuickProductPriceCreateDialog`, refresco preview tras crear  
- Permiso UI: `product_prices.quick_create` o `products.update`

### QB-05 — Banner liquidaciones

- `settlements/index.vue`: banner si `active_room_services_count > 0`  
- Link a control de piezas (`nightpos-services-room-control`)  
- API: `fetchSettlementPendingSources()`

## APIs frontend

- `api/platform.js` — `platformSetup`  
- `api/showTypes.js` — `fetchShowTypes`, `createShowType`  
- `api/staff.js` — `fetchStaffWaiters`, `quickCreateWaiter`  
- `api/products.js` — `createQuickProductPrice`  
- `api/settlements.js` — `fetchSettlementPendingSources`

## Validación manual (`pnpm run dev`)

1. Superadmin → Setup empresa (4 pasos) → Operar en esta empresa.  
2. Manillas → + Nuevo garzón → seleccionar y guardar.  
3. Registrar show → + Nuevo tipo de show → precio sugerido precargado.  
4. Comanda → agregar producto sin precio → alerta → configurar precio → agregar línea.  
5. Liquidaciones → banner piezas activas (si hay ACTIVE).  
6. Consola sin errores críticos.

## Fase C (pendiente)

Cliente, mesas, métodos de pago, producto rápido minimal, garzón en nueva comanda con selector, habitaciones alternativas en error 422.
