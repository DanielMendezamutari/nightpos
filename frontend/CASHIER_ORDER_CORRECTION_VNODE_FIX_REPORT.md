# CASHIER_ORDER_CORRECTION_VNODE_FIX_REPORT

## 1. Causa exacta

Error en consola:

```
TypeError: Cannot destructure property 'type' of 'vnode' as it is null.
at unmount
```

**Causa principal:** `VMenu` dentro de celdas `<td>` de `VTable` en `OrderItemsTable.vue`.

Vuetify monta el overlay del menú vía **Teleport**. Al entrar en modo corrección de caja, se creaban varios `VMenu` (uno por fila) dentro de la tabla. Durante el ciclo de montaje/desmonte (navegación, `fetchMe()`, actualización de `order`), Vue intentaba desmontar un vnode del overlay que ya era `null`.

**Contribuyentes secundarios:**

- Apertura de sub-diálogo desde ítem de menú sin `nextTick` entre cierre de menú y apertura de diálogo.
- Panel debug DEV con `VAlert type="secondary"` (tipo inválido en Vuetify 3) y render condicional extra.
- `activeItem` podía quedar inconsistente mientras diálogos seguían montados.

---

## 2. Componente que rompía

`frontend/src/components/nightpos/orders/OrderItemsTable.vue`

Patrón problemático:

```vue
<td>
  <VMenu>
    <template #activator="{ props }">...</template>
    <VList>...</VList>
  </VMenu>
</td>
```

---

## 3. Patrón Vue/Vuetify corregido

| Antes | Después |
|-------|---------|
| `VMenu` por fila en `VTable` | `VBtn` «Corregir» → **un solo `VDialog` de acciones** |
| Cierre inmediato + `emit('updated')` | `closeAllDialogs()` → `await nextTick()` → `emit` |
| Diálogos sin guard de `activeItem` | `v-if="activeItem"` en contenido de cada diálogo |
| Raíz fragmentada sin wrapper | `<div class="order-items-table-root">` contenedor único |
| `onBeforeUnmount` | Cierra diálogos antes de desmontar |

Flujo seguro:

1. Clic **Corregir** → abre diálogo de acciones (sin Teleport en tabla).
2. Elegir acción → cierra diálogo acciones → `nextTick` → abre diálogo específico.
3. Guardar → cierra diálogos → `nextTick` → actualiza comanda en padre.

También en `orders/[id].vue`:

- Eliminado panel debug DEV.
- `fetchMe()` en paralelo con `loadOrder` (menos thrashing de layout).
- `onOrderItemsUpdated` con `nextTick` antes de asignar `order`.

---

## 4. Validación manual

Con `pnpm run dev`:

1. Login cajera.
2. Cobrar comandas → Ver / corregir.
3. Banner azul visible, **sin error** `vnode null` en consola.
4. Botón **Corregir** por línea → diálogo de acciones.
5. Cambiar producto → guardar / cancelar sin crash.
6. Repetir cantidad, modalidad, chica.
7. Consola limpia.

---

## 5. Archivos modificados

- `src/components/nightpos/orders/OrderItemsTable.vue`
- `src/components/nightpos/orders/ChangeOrderItemProductDialog.vue`
- `src/pages/nightpos/orders/[id].vue`

## 6. Seguimiento — acciones principales (post-fix)

El primer fix dejó **6 `VDialog` montados siempre** en `OrderItemsTable`, lo que generó **scrims huérfanos** y bloqueó Agregar/Cobrar/Cancelar.

Corrección adicional: diálogos con `v-if` lazy, sin `:key` en tabla, `correctionLoading` separado. Ver `ORDER_MAIN_ACTIONS_RESTORE_REPORT.md`.
