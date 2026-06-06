# Backend — Conciliación de Productos (Vendidos vs. Comandados)

## Fase: Control de Cierre — Productos Vendidos vs. Comandados

**Objetivo:** comparar lo que los garzones comandaron (y se cobró) contra lo que
realmente quedó registrado como venta, identificando ventas directas y diferencias.
**No es Kardex** — no toca stock, compras, mermas, costos ni inventario físico (eso es V2).

---

## Endpoint

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/reports/product-reconciliation` | `reports.access` |

### Filtros

- `date_from` — fecha desde (YYYY-MM-DD)
- `date_to` — fecha hasta (YYYY-MM-DD)
- `official_shift_id` — ID de turno
- `cash_session_id` — ID de sesión de caja
- `waiter_user_id` — ID del garzón

Ámbito siempre acotado por `tenant_id` + `branch_id` del contexto operativo.

---

## Respuesta

```json
{
  "sold": [
    {
      "product_id": 12,
      "product_name": "Corona",
      "quantity_sold": 2,
      "total_amount": "50.00",
      "solo_quantity": 2,
      "companion_quantity": 0,
      "direct_sale_quantity": 0,
      "order_sale_quantity": 2
    }
  ],
  "ordered": [
    {
      "product_id": 12,
      "product_name": "Corona",
      "quantity_ordered": 2,
      "open_quantity": 0,
      "sent_to_bar_quantity": 0,
      "billed_quantity": 2,
      "cancelled_quantity": 0
    }
  ],
  "comparison": [
    {
      "product_id": 12,
      "product_name": "Corona",
      "ordered_quantity": 2,
      "sold_quantity": 2,
      "difference_quantity": 0,
      "status": "OK"
    }
  ],
  "summary": {
    "total_products": 1,
    "ok_count": 1,
    "mismatch_count": 0,
    "pending_count": 0,
    "cancelled_count": 0,
    "direct_only_count": 0,
    "has_differences": false,
    "total_quantity_sold": 2,
    "total_amount_sold": "50.00"
  }
}
```

---

## Definición de coincidencia

Para productos provenientes de comandas se compara:

- **`order_items` válidos cobrados** (orden en estado `BILLED`, línea no `CANCELLED`)
- contra **`sale_items` creados desde esos `order_items`** mediante `sale_items.order_item_id`.

Reglas:

- Si `sale_items.order_item_id` existe → es venta de comanda (`order_sale_quantity` / `linked_qty`).
- Si es `NULL` → es **venta directa** (`direct_sale_quantity`), no se considera error.
- `ordered_quantity` en la comparación = cantidad **facturada** (`billed_quantity`).
- `difference_quantity` = `sold_quantity − ordered_quantity` (informativo; incluye venta directa).

### Estados (`status`)

| Estado | Significado |
|--------|-------------|
| `OK` | Lo cobrado de comanda coincide con lo facturado (`linked == billed`). |
| `QUANTITY_MISMATCH` | Hay cantidad comandada y vendida pero no coinciden. |
| `MISSING_IN_SALE` | Hay comanda facturada pero no hay venta registrada. |
| `SOLD_WITHOUT_ORDER` | Vendido vinculado a comanda pero sin comanda facturada. |
| `PENDING_NOT_SOLD` | Comanda abierta / enviada a barra, aún no cobrada. |
| `CANCELLED` | Solo hay cantidad cancelada (comanda u orden cancelada). |
| `DIRECT_SALE_ONLY` | Solo venta directa, sin comanda asociada. |

### Corrección antes de cobrar

Si la cajera corrige una comanda antes de cobrar (cancela una línea y agrega otra),
el reporte toma como válido **lo finalmente cobrado**: el producto cancelado queda
como `CANCELLED` (no cuenta como vendido) y el producto final queda `OK`.

---

## Arquitectura

- `Domain/Reports/Repositories/ReportReadRepositoryInterface::getProductReconciliation()`
- `Infrastructure/Persistence/Eloquent/Repositories/EloquentReportReadRepository::getProductReconciliation()`
  - Agregaciones SQL sobre `sale_items` (join `sales`) y `order_items` (join `orders`).
  - Helpers privados: `resolveReconciliationStatus()`, `applySaleScope()`, `applyOrderScope()`.
- `Application/Reports/UseCases/GetProductReconciliationReportUseCase`
- `Http/Controllers/Api/V1/ReportController::productReconciliation()`
- Binding en `NightPosServiceProvider`.

---

## Tests

`tests/Feature/Api/V1/ProductReconciliationTest.php` (10 pruebas):

1. Comanda cobrada normal → `OK`.
2. Venta directa → `DIRECT_SALE_ONLY`.
3. Comanda abierta sin cobrar → `PENDING_NOT_SOLD`.
4. Línea cancelada no cuenta como vendida → `CANCELLED`.
5. Producto corregido antes de cobrar refleja el producto final.
6. `sale_item` con `order_item_id` cuenta como venta de comanda.
7. Diferencia de cantidad → `QUANTITY_MISMATCH`.
8. Aislamiento por tenant.
9. Aislamiento por sucursal.
10. Filtro por `cash_session_id`.

---

## Fuera de alcance (V2 — Inventario/Kardex)

Stock, compras, mermas, costos, inventario físico y descuento de existencias.
