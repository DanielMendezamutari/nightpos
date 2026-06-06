# VENTA DIRECTA DESDE CAJA — BACKEND REPORT

**Fecha:** 2026-06-05  
**Endpoint:** `POST /api/v1/direct-sales`  
**Permiso:** `sales.direct_create`

---

## ¿Qué es la venta directa?

La venta directa permite a la cajera cobrar productos inmediatamente en caja **sin crear una comanda previa**. Es ideal para ventas rápidas de consumibles (galletas, agua, dulces, cigarros, etc.) que no requieren mozo, mesa ni flujo de comanda.

### Diferencias con cobro de comanda

| Aspecto | Venta directa | Cobro de comanda |
|---|---|---|
| Requiere comanda | No | Sí |
| Requiere mesa | No | Sí (opcional) |
| Requiere mozo | No | Sí (opcional) |
| `order_id` en la venta | `null` | `id` de la comanda |
| `order_item_id` en ítem | `null` | `id` del ítem de comanda |
| Cambia estado de comanda | No | Sí (→ `BILLED`) |
| Precio resuelto desde | `ProductPriceResolver` | Snapshot en ítems de comanda |
| Permiso | `sales.direct_create` | `sales.charge` |

---

## Endpoint

```
POST /api/v1/direct-sales
Authorization: Bearer {token}
X-Branch-Code: {branch_code}
Content-Type: application/json
```

### Payload

```json
{
  "items": [
    {
      "product_id": 1,
      "sale_mode": "SOLO_CLIENTE",
      "quantity": 1,
      "girl_user_id": null
    }
  ],
  "payments": [
    {
      "method": "CASH",
      "amount": 10.00
    }
  ],
  "notes": "Venta directa caja"
}
```

### Respuesta exitosa (201)

```json
{
  "success": true,
  "message": "Venta directa registrada correctamente.",
  "data": {
    "sale": {
      "id": 42,
      "order_id": null,
      "sale_number": "V-0042",
      "total": "10.00",
      "currency": "BOB",
      "payment_mode": "CASH",
      "status": "PAID",
      "items": [...],
      "payments": [...]
    }
  }
}
```

---

## Flujo de caja

1. Se verifica que la cajera tenga caja abierta (`OpenCashSessionResolver`)
2. Se asegura turno operativo (`EnsureOperationalShiftUseCase`)
3. Se resuelven precios desde `OrderItemPricing` → `ProductPriceResolver`
4. Se validan métodos de pago habilitados
5. Se verifica que la suma de pagos coincida con el total calculado (±0.01)
6. En transacción:
   - Se crea el registro `sale` con `order_id = null`
   - Se crean `sale_items` con `order_item_id = null`
   - Se crean `sale_payments`
   - Se crea un movimiento de caja `INCOME` con descripción `"Venta directa V-XXXX"`
7. Se registra en audit log como `sale.direct_created`

---

## Cambios al modelo `Sale` / repositorio

### `SaleRepositoryInterface::create()`

El parámetro `$orderId` cambió de `int` a `?int` para soportar ventas sin comanda. Esto es **retrocompatible**: `ChargeOrderUseCase` sigue pasando un `int`.

### `OrderItemPricing::resolve()`

Se agregó `currency` al array de retorno (no-breaking, los callers existentes no lo usaban).

---

## Permisos

| Permiso | Descripción |
|---|---|
| `sales.direct_create` | Venta directa desde caja |

### Roles que tienen este permiso

| Rol | ¿Tiene permiso? |
|---|---|
| `super_admin` | ✓ (todos los permisos) |
| `tenant_owner` | ✓ |
| `cashier` | ✓ |
| `cashier_senior` | ✓ |
| `waiter` | ✗ |
| `cleaning` | ✗ |
| `girl` | ✗ |

Se agregó la migración `2026_06_10_100071_add_direct_sale_permission.php` para asignar el permiso en bases de datos existentes.

---

## Impacto en liquidaciones

### CON_ACOMPANANTE
Si un ítem tiene `sale_mode = CON_ACOMPANANTE` y `girl_user_id` válido, la venta directa registra:
- `girl_amount_snapshot`: monto que le corresponde a la chica
- `house_amount_snapshot`: monto para la casa

Estos snapshots alimentan el proceso de liquidación de chicas (igual que en cobro de comanda).

### Sin comisión de mozo
Las ventas directas tienen `waiter_commission_percent_snapshot = null` y `waiter_commission_amount_snapshot = null` porque no hay mozo asignado.

---

## Pago mixto

El payload `payments[]` acepta **múltiples métodos**. Si la suma coincide con el total (±0.01):

- `payment_mode` = `MIXED` (o `CASH`/`QR`/`CARD` si es uno solo)
- Se crean varios `sale_payments`
- Se crean varios `cash_movements` INCOME (uno por método)

Ejemplo:

```json
"payments": [
  { "method": "CASH", "amount": 100 },
  { "method": "QR", "amount": 70 },
  { "method": "CARD", "amount": 30 }
]
```

Ver `backend/DIRECT_SALE_MIXED_PAYMENTS_REPORT.md`.

---

## Tests de feature

Archivo: `tests/Feature/Api/V1/DirectSaleApiTest.php` — **15/15 pasando**

| # | Escenario | Resultado |
|---|---|---|
| 1 | Cajera crea venta directa con caja abierta | ✓ |
| 2 | Venta directa crea `sale` con `order_id = null` | ✓ |
| 3 | Venta directa crea `sale_items` con `order_item_id = null` | ✓ |
| 4 | Venta directa crea payment record | ✓ |
| 5 | Venta directa crea movimiento INCOME en caja | ✓ |
| 6 | Rechaza si no hay caja abierta | ✓ |
| 7 | Garzón no puede venta directa (403) | ✓ |
| 8 | Rechaza CON_ACOMPANANTE sin `girl_user_id` | ✓ |
| 9 | Acepta CON_ACOMPANANTE con chica y guarda snapshots | ✓ |
| 10 | Aislamiento multi-tenant | ✓ |
| 11 | Producto sin precio → 422 | ✓ |
| 12 | Pago mixto CASH + QR | ✓ |
| 13 | Pago mixto CASH + QR + CARD | ✓ |
| 14 | Rechaza pagos < total | ✓ |
| 15 | Rechaza pagos > total | ✓ |

---

## Limitaciones

- No se admiten notas de ítem individual (solo notas globales de la venta)
- No hay integración con impresión de ticket (pendiente)
- El `sale_number` usa el mismo secuencial que ventas de comanda (`V-XXXX`)
- No soporta descuentos ni precios manuales (precio siempre desde `ProductPriceResolver`)
