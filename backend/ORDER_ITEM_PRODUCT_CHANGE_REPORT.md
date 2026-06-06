# ORDER_ITEM_PRODUCT_CHANGE_REPORT (Backend)

## 1. Regla: garzón no corrige

El garzón **no** tiene permiso `orders.update_items`. Puede crear comanda, agregar productos y enviar a barra, pero no puede:

- Cambiar producto en una línea existente
- Editar cantidad, modalidad o quitar líneas ya cargadas

El middleware `nightpos.permission:orders.update_items` devuelve **403** si el garzón intenta `PUT /orders/{id}/items/{item_id}`.

## 2. Regla: cajera/admin corrige

Roles con `orders.update_items`: `tenant_owner`, `cashier`, `cashier_senior`.

Pueden corregir líneas antes del cobro, incluyendo **cambio de producto** (`product_id`).

## 3. Flujo Corona → Ice 51

```
PUT /api/v1/orders/{order_id}/items/{item_id}
{
  "product_id": <ice_51_id>,
  "sale_mode": "SOLO_CLIENTE"   // opcional, mantiene o cambia modalidad
}
```

1. Valida producto activo del mismo tenant.
2. `ProductPriceResolver` obtiene precio según `sale_mode` y sucursal.
3. Actualiza `product_id`, `product_name`, precios y totales de línea.
4. `recalculateTotals` actualiza subtotal/total de comanda.
5. Auditoría `order.item_product_changed` con nombres anterior/nuevo.

## 4. Recálculo de precios

`OrderItemPricing::resolve()` devuelve `unit_price`, `line_total`, `girl_amount`, `house_amount`.

Si el producto no tiene precio para la modalidad → `ProductDomainException::priceNotFoundForMode()` (422).

## 5. Restricciones por estado

| Estado | Cambio producto |
|--------|-----------------|
| `OPEN` | Permitido sin motivo |
| `SENT_TO_BAR` + línea `SENT` | Permitido con `reason` obligatorio |
| `SENT_TO_BAR` | Cantidad/modalidad sin cambio producto: solo chica |
| `BILLED` / `CANCELLED` | Rechazado |

## 6. Auditoría

Acción: `order.item_product_changed`

Metadata: `previous_product_id`, `previous_product_name`, `new_product_id`, `new_product_name`, `reason`, `changed_by_user_id`, `item_id`.

## 7. Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `UpdateOrderItemUseCase.php` | Soporte `product_id`, validación, auditoría |
| `UpdateOrderItemInput.php` | `product_id`, `reason` |
| `UpdateOrderItemRequest.php` | Validación request |
| `OrderController.php` | Pasa nuevos campos |
| `EloquentOrderRepository.php` | `updateItem` persiste producto; `recalculateTotals` recarga ítems |
| `OrderDomainException.php` | `changeReasonRequired()` |

## 8. Tests

`tests/Feature/Api/V1/OrderItemProductChangeTest.php` — 7 escenarios.

## 9. Validación manual

1. Garzón crea comanda con Corona.
2. Cajera → Cobrar comandas → Ver/corregir.
3. Menú línea → Cambiar producto → Ice 51.
4. Total actualizado; cobrar; venta refleja Ice 51.
5. Garzón no tiene menú de corrección ni acceso API.
