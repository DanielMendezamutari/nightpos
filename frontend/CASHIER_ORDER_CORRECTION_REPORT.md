# CASHIER_ORDER_CORRECTION_REPORT.md — Frontend

**Proyecto:** NightPOS SaaS — Frontend Vue 3 / Vuetify  
**Fase:** A — Corrección de comanda por cajera  
**Fecha:** 2026-06-02  
**Referencias:** `CASHIER_ORDER_AND_DIRECT_SALE_AUDIT.md`, `FRONTEND_GUIDELINES.md`, `OPERATIONAL_ROLE_FLOW_FIX_REPORT.md`

---

## 1. Flujo anterior

- `/nightpos/cashier/orders`: solo botón **Cobrar** por card.
- Detalle `/nightpos/orders/:id`: barra de acciones pensada para garzón; agregar producto solo en `OPEN`; ítems en solo lectura.
- Volver siempre a lista general `nightpos-orders`.

---

## 2. Flujo nuevo

```
Operación → Cobrar comandas (/cashier/orders)
  ├── Ver / corregir → /orders/:id?from=cashier&mode=correction
  └── Cobrar → /orders/:id?from=cashier&mode=correction&charge=1
```

En modo corrección (`from=cashier` o permiso `orders.update_items`):

- Agregar producto en `OPEN` y `SENT_TO_BAR`
- Menú por línea: **cambiar producto**, cantidad, modalidad, chica, quitar, cancelar con motivo
- Botón **Corregir mesa / ambiente** (solo `OPEN`)
- Cobrar y cancelar comanda (si permisos)
- Volver a **Cobrar comandas**

**Garzón:** sin cambios en rutas `/nightpos/waiter/*`; sin permisos de corrección; envío a barra oculto en modo cajera.

---

## 3. Archivos modificados / creados

| Archivo | Cambio |
|---------|--------|
| `src/pages/nightpos/cashier/orders/index.vue` | CTAs Ver/corregir + Cobrar |
| `src/pages/nightpos/orders/[id].vue` | Modo corrección, navegación, cabecera, acciones |
| `src/components/nightpos/orders/OrderItemsTable.vue` | Menú acciones por línea + diálogos |
| `src/components/nightpos/orders/ChangeOrderItemProductDialog.vue` | **Nuevo** — cambio Corona → Ice 51 |
| `src/components/nightpos/orders/OrderHeaderEditDialog.vue` | **Nuevo** — mesa, ambiente, notas |
| `src/api/orders.js` | `updateOrderItem`, `removeOrderItem`, `cancelOrderItem`, `updateOrderHeader` |
| `src/composables/useNightPosPermissions.js` | Flags corrección |
| `src/composables/useOrderHelpers.js` | `activeOrderItems`, `orderItemStatusLabel` |

---

## 4. UX por estado (tabla de ítems)

| Estado comanda | Acciones por línea |
|----------------|-------------------|
| `OPEN` | Cambiar producto, cantidad, modalidad, chica, quitar (PENDING) |
| `SENT_TO_BAR` | Cambiar producto (motivo si SENT), chica; cancelar línea (SENT) con motivo |
| `BILLED` / línea cancelada | Sin menú |

- Botón **Corregir** visible por fila (tabla con columna Acciones en modo cajera)  
- `fetchMe()` al abrir detalle desde cajera para refrescar permisos  
- Diálogos pequeños (max-width 360–400)  
- Snackbar en éxito / error  
- Líneas canceladas: tachado + chip + motivo

---

## 5. Permisos UI

| Permiso | Efecto en UI |
|---------|--------------|
| `orders.update_items` | Modo corrección + menú líneas |
| `orders.update_header` | Botón corregir mesa |
| `orders.cancel_item` | API cancel línea (vía menú si editable) |
| `orders.cancel` | Botón cancelar comanda |
| `orders.add_items` | Agregar producto |
| `sales.charge` | Cobrar |

---

## 6. API consumida

| Acción UI | Endpoint |
|-----------|----------|
| Cantidad / modalidad / chica | `PUT /orders/{id}/items/{item_id}` |
| Quitar | `DELETE /orders/{id}/items/{item_id}` |
| Cancelar línea | `POST /orders/{id}/items/{item_id}/cancel` |
| Mesa / ambiente | `PATCH /orders/{id}` |
| Agregar | `POST /orders/{id}/items` (existente) |
| Cobrar | `POST /orders/{id}/charge` (existente) |

---

## 7. Validación manual

1. PIN cajera `1234` → Cobrar comandas.  
2. **Ver / corregir** en una comanda OPEN.  
3. Menú línea → cambiar cantidad y modalidad.  
4. Asignar chica en CON_ACOMPANANTE.  
5. Quitar línea pendiente.  
6. **Corregir mesa / ambiente** → guardar.  
7. **Cobrar** → modal de pago → venta OK.  
8. PIN garzón `5678` → intentar editar comanda de otro garzón → error.  
9. Volver lleva a `/cashier/orders`, no a lista admin.

---

## 8. Pendiente

- **Venta directa de caja** (`/nightpos/cash/direct-sale`) — Fase B.  
- Reportes y placeholders finanzas — sin cambios.  
- Impresión al modificar comanda en barra — no implementado.
