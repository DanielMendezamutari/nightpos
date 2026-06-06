# CASHIER_ORDER_CORRECTION_VISIBILITY_FIX_REPORT

## 1. Causa exacta de por qué no se veía

Había **dos causas** combinadas:

### A) Condición demasiado estricta en `orders/[id].vue`

`canEditLines` exigía **simultáneamente**:

- `isCorrectionMode` (OK con `?from=cashier`)
- `canUpdateOrderItems` (`orders.update_items` en `userData`)
- `modifiable` (OPEN / SENT_TO_BAR)

Si la cajera tenía cookie `userData` **anterior** a la migración de permisos de corrección, `orders.update_items` no estaba en el array → `editable=false` → **sin menú**.

### B) UX: acciones solo como ícono `ri-more-2-line`

El menú existía en código pero era un botón **icon-only** pequeño en `#append` de `VListItem`, poco visible en vista cajera/desktop.

---

## 2. Ruta corregida

Desde **Cobrar comandas**:

```
/nightpos/orders/{id}?from=cashier&mode=correction
```

Antes solo `?from=cashier`.

`mode=correction` fuerza modo cajera aunque falten flags derivados.

---

## 3. Permisos verificados

Seeder (`SeedsNightPosFoundation.php`) asigna a rol `cashier`:

- `orders.update_items`
- `orders.cancel_item`
- `orders.update_header`
- `orders.cancel`

**Si el usuario inició sesión antes de correr migración/seeder actualizado**, `userData` en cookie no incluye esos slugs.

**Corrección frontend:** al entrar en detalle con `from=cashier` o `mode=correction`, se llama `auth.fetchMe()` para refrescar permisos desde API.

**Si persiste el problema:** cerrar sesión y volver a entrar (limpia cookie `userData`).

---

## 4. Props / componentes corregidos

### `orders/[id].vue`

| Computed / acción | Cambio |
|-------------------|--------|
| `cashierCorrectionMode` | `from=cashier` **o** `mode=correction` |
| `canEditLines` | `cashierCorrectionMode && modifiable` (ya no exige permiso para **mostrar** UI) |
| `missingCorrectionPermission` | Alerta si falta `orders.update_items` |
| `onMounted` | `fetchMe()` en modo cajera |
| Debug DEV | Panel con role, permissions, flags |

### `OrderItemsTable.vue`

Nuevas props:

- `cashierCorrectionMode`
- `canUpdateItems`
- `canCancelItems`

Cambios UX:

- Botón visible **«Corregir»** (texto + ícono), no solo tres puntos
- Tabla con columnas Producto | Cantidad | Modalidad | Total | **Acciones** en modo cajera
- Guard de permisos al ejecutar acción con mensaje claro

### Fix vnode (post-visibilidad)

`VMenu` dentro de `VTable` causaba crash al desmontar Teleport. Reemplazado por diálogo único de acciones. Ver `CASHIER_ORDER_CORRECTION_VNODE_FIX_REPORT.md`.

### `cashier/orders/index.vue`

`goCorrect` / `goCharge` navegan con `mode=correction`.

---

## 5. Validación manual

1. Login garzón → comanda con Corona.
2. Login cajera (cerrar sesión antes si sesión vieja).
3. Operación → Comandas → Cobrar comandas.
4. **Ver / corregir**.
5. Banner «Modo corrección de caja».
6. Tabla con columna **Acciones** y botón **Corregir** en línea Corona.
7. Corregir → Cambiar producto → Ice 51.
8. Total actualizado → Cobrar.

En desarrollo (`npm run dev`): panel debug muestra `canEditLines`, `canUpdateItems`, permisos `orders.*`.

---

## 6. ¿Requiere cerrar sesión?

| Situación | Acción |
|-----------|--------|
| Primera vez tras deploy de permisos | **Cerrar sesión y volver a entrar** (recomendado) |
| Con fix `fetchMe()` al abrir detalle cajera | Suele bastar abrir de nuevo Ver/corregir |
| Cookie `userData` sin `orders.update_items` | Alerta amarilla en pantalla + mensaje al tocar Corregir |

---

## 7. Archivos modificados

- `src/pages/nightpos/orders/[id].vue`
- `src/pages/nightpos/cashier/orders/index.vue`
- `src/components/nightpos/orders/OrderItemsTable.vue`
