# ORDER_ITEM_PRODUCT_CHANGE_REPORT (Frontend)

## 1. Regla: garzón no corrige

Rutas `/nightpos/waiter/orders/:id` muestran líneas en **solo lectura** (sin menú de tres puntos).

Solo acciones: agregar producto, enviar a barra, ver detalle.

## 2. Regla: cajera/admin corrige

Modo corrección activo con `?from=cashier` o permiso `orders.update_items`.

`OrderItemsTable` con `editable=true` muestra menú por línea.

## 3. Flujo Corona → Ice 51

```
Cobrar comandas → Ver / corregir
  → /orders/{id}?from=cashier&mode=correction
  → botón «Corregir» por línea → Cambiar producto
```

`ChangeOrderItemProductDialog`:

- Producto actual (solo lectura)
- Autocomplete nuevo producto
- Modalidad (SOLO / CON_ACOMPANANTE)
- Chica si CON_ACOMPANANTE
- Motivo obligatorio si comanda `SENT_TO_BAR` y línea `SENT`
- Preview de precio antes de guardar

Al guardar: `PUT /orders/{id}/items/{item_id}` con `product_id` → snackbar + totales refrescados.

## 4. Menú por línea (orden UX)

1. Cambiar producto
2. Cambiar cantidad *(solo OPEN)*
3. Cambiar modalidad *(solo OPEN)*
4. Cambiar chica
5. Quitar línea / Cancelar línea

Botón visible **«Corregir»** (`ri-edit-line`) por fila; abre diálogo de acciones (sin `VMenu` en tabla — evita crash VNode). Ver `CASHIER_ORDER_CORRECTION_VNODE_FIX_REPORT.md`.

## 5. Archivos

| Archivo | Rol |
|---------|-----|
| `ChangeOrderItemProductDialog.vue` | Diálogo cambio producto |
| `OrderItemsTable.vue` | Menú + integración diálogo |
| `orders/[id].vue` | `canEditLines` solo modo cajera/admin |

## 6. Validación manual

1. Login garzón → comanda con Corona → enviar barra (opcional).
2. Login cajera → Cobrar comandas → Ver/corregir.
3. Cambiar producto a Ice 51; confirmar total.
4. Cobrar; verificar venta.
5. Login garzón → detalle comanda sin opciones de edición en líneas.

## 7. Pendiente

- Venta directa de caja (fuera de alcance).
- Atajo de búsqueda rápida por categoría en diálogo (mejora futura).
