# Consolidación operativa P1 — Frontend (2026-06-21)

## 1. Cobro con aviso de impresión

`cashier/orders/index.vue` y `orders/[id].vue` muestran:

- `print_warning` (sin impresora)
- `print_job.status === FAILED` (fallo agente)

El cobro **nunca** se revierte.

## 2. Cola caja y SSE

| Archivo | Cambio |
|---------|--------|
| `useCashierShell.js` | Refresca en `order.sent_to_bar`, no en `order.created` |
| `cashier/orders/index.vue` | `refreshOnCreated: false`, toast solo en envío a barra |

## 3. Correcciones sin interrupción

`useOrderOperationalEvents.js`:

- `pauseRefresh` — acumula eventos mientras `correctionLoading`
- `flushPendingRefresh()` — una sincronización al salir de edición

`orders/[id].vue` conecta `correctionLoading` + `watch` al finalizar.

## 4. Login operativo PIN-only

`login.vue` + cookie `lastOperatorName`:

- Pantalla PIN: Empresa, Sucursal, Usuario (si recordado), solo PIN
- Botones: **Cambiar empresa**, **Cambiar sucursal**, **Cambiar usuario**
- Pasos `select-tenant` / `select-branch` sin obligar wizard completo

## 5. Settings impresoras

Toggle **Imprimir ticket al cobrar** (`auto_print_sale_receipt`) en `settings/printers/index.vue`.

## 6. Pieza/show — impresión operativa (2026-06-21)

Ver `ROOM_SERVICE_SHOW_PRINT_FIX_REPORT.md`:

- Mensajes post-registro con `print_job` / `print_warning`
- Botones Ver ticket / Reimprimir en create pieza y show
- Rutas `print/room-service/:id`, `print/show/:id`
- Default 60% chica en formulario pieza

## Pendiente P3
