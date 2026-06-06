# Fase C4 — Modo garzón móvil (Frontend)

## 1. Flujo del garzón

1. Login PIN → redirección automática a `/nightpos/waiter`.
2. Panel con cards táctiles (nueva comanda, abiertas, barra, pendiente cobro).
3. Listado en cards por scope.
4. Nueva comanda: ambientes rápidos **o** texto libre (móvil).
5. Detalle: agregar bebida (fullscreen) y enviar a barra.
6. Sin menú lateral administrativo (nav vacío + guard de rutas).

## 2. Mejoras implementadas

| Pantalla | Ruta | Descripción |
|----------|------|-------------|
| Inicio | `/nightpos/waiter` | Cards acción + recientes + `WaiterMobileHeader` |
| Comandas | `/nightpos/waiter/orders` | Cards por `?scope=` |
| Nueva | `/nightpos/waiter/orders/new` | Ambientes + texto libre + payload correcto |
| Detalle | `/nightpos/waiter/orders/:id` | Ítems, + bebida, enviar barra |

Componentes: `WaiterBottomNav`, `WaiterOrderCard`, `WaiterMobileHeader`, `WaiterKpiCard`, `WaiterOrderActions`, `waiterOrderPayload.js`, `useWaiterOrderStatus.js`.

## 3. Corrección y refinamiento UX (Jun 2026)

**Problema inicial:** mensaje “Indique mesa o ambiente” al comandar; 403 en consola; pantallas densas.

**Fix funcional:** `WAITER_MOBILE_AUDIT.md`, `WAITER_MOBILE_FIX_REPORT.md`.

**Refinamiento visual:** `WAITER_MOBILE_UX_REFINEMENT_REPORT.md` — KPI cards, listados en cards, cabecera mínima, diálogo `mobile-waiter`, endpoints `/waiter/*`.

## 4. Endpoints cliente

- `api/waiter.js` — dashboard, listados, `service-areas`, `girls`.
- `api/orders`, `api/products`.

## 5. Experiencia móvil

- Layout `blank` (sin sidebar).
- Header compacto garzón (nombre, sucursal, turno, conexión, salir).
- Botones `x-large`, bottom navigation fija.
- Sin tablas administrativas.
- Guard: garzón solo navega bajo `/nightpos/waiter/*`.

## 6. Validación manual (`pnpm run dev`)

1. Login garzón `5678`.
2. Nueva comanda con ambiente **o** texto libre.
3. Agregar bebidas SOLO / CON_ACOMPANANTE, enviar barra.
4. Cajero `1234` cobra.
5. Vista móvil DevTools.
6. Consola sin 403 críticos ni error de bottom-navigation.

## 7. Selector de productos (Jun 2026)

`OrderAddProductDialog` en modo `mobile-waiter`:

- Cards compactas con precios Solo / Con acompañante.
- Chips de categoría (sin textos técnicos en inglés).
- Helper `useProductLabels.js` — ver `WAITER_PRODUCT_SELECTOR_UX_REPORT.md`.

## 8. Pendientes

- Sonido/vibración al cambiar estado.
- PWA “Agregar a pantalla de inicio”.
- Turno oficial por nombre en header.
