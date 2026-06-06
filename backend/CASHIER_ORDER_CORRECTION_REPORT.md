# CASHIER_ORDER_CORRECTION_REPORT.md — Backend

**Proyecto:** NightPOS SaaS — Backend Laravel  
**Fase:** A — Corrección de comanda por cajera  
**Fecha:** 2026-06-02  
**Referencias:** `CASHIER_ORDER_AND_DIRECT_SALE_AUDIT.md`, `DOMAIN_DESIGN.md`, `OPERATIONAL_ROLE_FLOW_FIX_REPORT.md`

---

## 1. Flujo anterior

1. Cajera listaba comandas en `/cashier/orders` con CTA único **Cobrar**.
2. API permitía agregar ítems, asignar chica (PATCH) y cancelar comanda completa, pero **no** editar cantidad/modalidad, quitar líneas ni cancelar línea con motivo.
3. `POST /orders/{id}/cancel` usaba permiso `orders.access` (cualquier rol con acceso a comandas).
4. Totales y cobro incluían todas las líneas sin distinguir canceladas.

---

## 2. Flujo nuevo

1. Cajera (o admin) puede **corregir** comandas de la sucursal antes de cobrar según estado.
2. Garzón sigue restringido a **sus** comandas (`WaiterOrderAccessPolicy`).
3. Líneas enviadas a barra se **anulan** con motivo (soft cancel, `item_status = CANCELLED`).
4. Líneas pendientes en OPEN se **eliminan** físicamente.
5. Totales y cobro excluyen líneas `CANCELLED`.
6. Cambios registrados en bitácora (`audit_logs`).

---

## 3. Endpoints creados / actualizados

| Método | Ruta | Permiso | Acción |
|--------|------|---------|--------|
| `PUT` | `/api/v1/orders/{id}/items/{item_id}` | `orders.update_items` | Cantidad, modalidad, chica, **`product_id`**; precio recalculado en servidor |
| `DELETE` | `/api/v1/orders/{id}/items/{item_id}` | `orders.update_items` | Quitar línea (`OPEN` + `PENDING`) |
| `POST` | `/api/v1/orders/{id}/items/{item_id}/cancel` | `orders.cancel_item` | Cancelar línea enviada con `reason` obligatorio |
| `PATCH` | `/api/v1/orders/{id}` | `orders.update_header` | `table_label`, `service_area_id`, `notes` |
| `POST` | `/api/v1/orders/{id}/cancel` | `orders.cancel` *(antes `orders.access`)* | Cancelar comanda completa |

**Sin cambios:** `POST .../items`, `PATCH .../items/{id}` (chica legacy), `POST .../charge`.

---

## 4. Reglas por estado

| Estado comanda | Edición líneas | Cabecera | Cobro |
|----------------|----------------|----------|-------|
| `OPEN` | Completa: add, PUT, DELETE (PENDING), PATCH header | Sí | Sí |
| `SENT_TO_BAR` | Add extra; PUT chica o **cambio producto** (motivo si línea SENT); POST cancel línea SENT con motivo | No | Sí |
| `IN_PREPARATION` / `READY` | No | No | Sí |
| `BILLED` / `CANCELLED` | No | No | No |

**Ítem `CANCELLED`:** no editable; excluido de totales y cobro.

---

## 5. Permisos

| Slug | Roles (seeder + migración + SaaS) |
|------|-----------------------------------|
| `orders.update_items` | `tenant_owner`, `cashier`, `cashier_senior` |
| `orders.cancel_item` | idem |
| `orders.update_header` | idem |
| `orders.cancel` | idem |

**Garzón:** sin permisos de corrección; solo `orders.add_items` / `send_to_bar` en comandas propias.

**Migración:** `2026_06_10_100070_order_item_cancellation_and_correction_permissions.php`  
**Columnas nuevas en `order_items`:** `cancellation_reason`, `cancelled_at`, `cancelled_by_user_id`

---

## 6. Use cases y servicios

| Clase | Responsabilidad |
|-------|-----------------|
| `UpdateOrderItemUseCase` | PUT ítem |
| `RemoveOrderItemUseCase` | DELETE ítem |
| `CancelOrderItemUseCase` | POST cancel línea |
| `UpdateOrderHeaderUseCase` | PATCH cabecera |
| `OrderAccessGuard` | Tenant, branch, garzón ajeno, estados terminales |
| `OrderItemPricing` | `ProductPriceResolver` + totales de línea |

**Auditoría:** `order.item_updated`, `order.item_removed`, `order.item_cancelled`, `order.header_updated`, `order.cancelled`

---

## 7. Tests

Archivo: `tests/Feature/Api/V1/CashierOrderCorrectionTest.php` — **11/11 OK**

1. Cajera edita cantidad OPEN  
2. Cajera cambia modalidad OPEN  
3. Cajera cambia chica CON_ACOMPANANTE  
4. Cajera quita línea PENDING  
5. Cajera cancela línea enviada con motivo  
6. Rechaza cancel sin motivo (422 validación)  
7. Rechaza modificar BILLED (422 dominio)  
8. Garzón no agrega a comanda ajena (404)  
9. Cajera corrige mesa / notas  
10. Totales recalculados  
11. Tenant no modifica comanda ajena (404)

Regresión: `OperationalRoleFlowFixTest`, `OrderApiTest`, `ChargeOrderApiTest` — OK.

---

## 8. Validación manual sugerida

1. Login garzón → crear comanda con error de cantidad/modalidad.  
2. Login cajera → Operación → Cobrar comandas → **Ver / corregir**.  
3. Cambiar cantidad, modalidad, chica; quitar línea pendiente.  
4. Enviar a barra (garzón) → cajera cancela línea enviada con motivo.  
5. Corregir mesa en OPEN.  
6. Cobrar y verificar venta / total.  
7. Garzón 2 no puede operar comanda del garzón 1.

---

## 9. Pendiente

- **Fase B:** venta directa de caja (`POST /direct-sales`) — ver `CASHIER_ORDER_AND_DIRECT_SALE_AUDIT.md`.
- Anulación post-factura (fuera de alcance).
- Reimpresión automática al modificar comanda en barra.
