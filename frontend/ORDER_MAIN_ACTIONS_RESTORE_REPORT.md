# ORDER_MAIN_ACTIONS_RESTORE_REPORT

## 1. Qué se había roto

Tras el fix de VNode (reemplazo de `VMenu` por diálogos en `OrderItemsTable`), los botones principales dejaron de responder:

- Agregar producto
- Cobrar
- Cancelar comanda
- Enviar a barra (en vista no-cajera)

**No era un problema de emits renombrados** — `OrderActionsBar` seguía emitiendo `add`, `charge`, `send`, `cancel` correctamente.

## 2. Causa exacta

**Scrim invisible de Vuetify bloqueando clicks.**

`OrderItemsTable` montaba **6+ `VDialog` permanentemente** (aunque `v-model` fuera `false`). Al navegar, remontar con `:key`, o cerrar diálogos de corrección de forma asíncrona, quedaban **overlays huérfanos** en el DOM que interceptaban todos los clics de la página, incluida `OrderActionsBar`.

Contribuyentes:

- `:key` en `OrderItemsTable` forzaba remount y desincronizaba overlays.
- `actionLoading` compartido entre corrección y acciones principales.
- `onBeforeUnmount` async no cerraba overlays a tiempo.
- Diálogos con `v-model` false pero componente aún montado.

## 3. Botones restaurados

| Acción | Handler |
|--------|---------|
| Agregar | `openAddProductDialog` → cierra corrección → `showAddItem = true` |
| Cobrar | `openChargeDialog` → cierra corrección → `showChargeDialog = true` |
| Cancelar | `openCancelDialog` → cierra corrección → `showCancelConfirm = true` |
| Enviar barra | `openSendDialog` (sin cambio) |
| Corregir línea | Diálogo de acciones (sin `VMenu` en tabla) |

## 4. Cómo se evitó volver al crash VNode

- **Sin `VMenu` en tabla** — se mantiene botón «Corregir» → `VDialog` de acciones.
- Diálogos de corrección con **`v-if` lazy** — solo existen en DOM cuando están abiertos.
- `closeAllDialogs()` síncrono en `onBeforeUnmount`.
- `activeItem` se limpia después de `nextTick` cuando no hay diálogo abierto.
- Eliminado `:key` forzado en `OrderItemsTable`.
- `correctionLoading` separado de `actionLoading` de acciones principales.

## 5. Archivos modificados

- `src/components/nightpos/orders/OrderItemsTable.vue`
- `src/components/nightpos/orders/OrderActionsBar.vue` — `z-index` en barra sticky
- `src/pages/nightpos/orders/[id].vue` — handlers que cierran corrección antes de abrir modales principales; debug `console.debug` en DEV

## 6. Validación manual

### Cajera (OPEN)

1. Ver / corregir → sin error en consola.
2. **Agregar** → abre `OrderAddProductDialog`.
3. **Corregir** → cambiar producto/cantidad OK.
4. **Cobrar** → abre `ChargeOrderModal`.
5. **Cancelar** → abre confirmación.

### Garzón (`/waiter/orders/:id`)

1. + Producto funciona.
2. Enviar barra funciona.

Consola DEV muestra `[order-detail actions]` con flags al cambiar estado.
