# Fast Operation Mode P0 — Implementación Backend

**Fecha:** 2026-06-16  
**Estado:** ✅ Completado  
**Auditoría base:** `FAST_OPERATION_REALTIME_AUDIT.md`

---

## Resumen

Se cerró el P0 de tiempo real en comandas emitiendo `order.updated` en todos los use cases de edición y estandarizando el payload SSE de eventos de comanda.

---

## Cambios

### Helper de payload

`app/Application/Order/Support/OrderOperationalEventPayload.php`

Payload mínimo:

```json
{
  "order_id": 123,
  "entity": { "type": "order", "id": 123 },
  "refresh": ["orders"],
  "status": "OPEN",
  "source": "update_order_item",
  "summary": "opcional"
}
```

### Emisión `order.updated` (nuevo)

| Use case | source |
|----------|--------|
| `UpdateOrderItemUseCase` | `update_order_item` |
| `RemoveOrderItemUseCase` | `remove_order_item` |
| `AssignOrderItemGirlUseCase` | `assign_order_item_girl` |
| `CancelOrderItemUseCase` | `cancel_order_item` |
| `UpdateOrderHeaderUseCase` | `update_order_header` |

### Payload estandarizado (existentes actualizados)

| Use case | Evento | source |
|----------|--------|--------|
| `CreateOrderUseCase` | `order.created` | `create_order` |
| `AddOrderItemUseCase` | `order.updated` | `add_order_item` |
| `SyncOrderItemAllocationsUseCase` | `order.updated` | `sync_order_item_allocations` |
| `SendOrderToBarUseCase` | `order.sent_to_bar` | `send_order_to_bar` |
| `CancelOrderUseCase` | `order.cancelled` | `cancel_order` |
| `ChargeOrderUseCase` | `order.billed` | `charge_order` |

Combos: sin cambios de reglas — `sync_order_item_allocations` ya emitía y se mantuvo.

---

## Tests

Nuevo archivo: `tests/Feature/Api/V1/SseOrderEventsP0Test.php` — 10 escenarios:

1. crear comanda → `order.created`
2. agregar producto → `order.updated`
3. editar cantidad → `order.updated`
4. cambiar producto → `order.updated`
5. asignar chica → `order.updated`
6. editar cabecera → `order.updated`
7. quitar línea → `order.updated`
8. cancelar línea → `order.updated`
9. enviar a barra → `order.sent_to_bar`
10. cobrar → `order.billed`

**Suite completa:** `php artisan test` — ✅ PASS

---

## Sin cambios

- Caja, liquidaciones, habitaciones, limpieza — sin regresiones
- Reglas de negocio de comandas/combos intactas

---

## Referencias

- `FAST_OPERATION_REALTIME_AUDIT.md` (actualizado)
- `SSE_2_REPORT.md` (actualizado)
- `frontend/FAST_OPERATION_REALTIME_P0_IMPLEMENTATION_REPORT.md`
