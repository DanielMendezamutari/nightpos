# Backend SSE-2 Report — Eventos Operativos Conectados

## Fase: V1-94 — SSE-2 EVENTOS OPERATIVOS LIMPIEZA / CAJERA / ADMIN

**Fecha:** 2026-06-06  
**Tests:** 365 pasando (12 nuevos para SSE-2)

---

## Objetivo

Conectar los eventos reales del negocio al stream SSE para que las pantallas operativas se actualicen sin requerir recarga manual (F5).

---

## Eventos implementados

### Room Services / Limpieza

| Evento | Use Case / Service | target_role |
|--------|-------------------|-------------|
| `room_service.created` | `CreateRoomServiceUseCase` | null (broadcast) |
| `room_service.due` | `RoomServiceDueNotifier` | null (broadcast) |
| `room_service.finished` | `FinishRoomServiceUseCase` | null (broadcast) |
| `room.cleaned` | `MarkRoomCleanUseCase` | null (broadcast) |
| `cleaning.earnings.updated` | `MarkRoomCleanUseCase` | `cleaning` (solo limpieza) |

### Comandas / Cajera

| Evento | Use Case | target_role |
|--------|---------|-------------|
| `order.created` | `CreateOrderUseCase` | null |
| `order.updated` | `AddOrderItemUseCase` | null |
| `order.sent_to_bar` | `SendOrderToBarUseCase` | null |
| `order.billed` | `ChargeOrderUseCase` | null |
| `order.cancelled` | `CancelOrderUseCase` | null |

### Caja / Ventas

| Evento | Use Case | target_role |
|--------|---------|-------------|
| `sale.created` | `ChargeOrderUseCase`, `CreateDirectSaleUseCase` | null |
| `direct_sale.created` | `CreateDirectSaleUseCase` | null |
| `cash.movement.created` | `RegisterCashMovementUseCase`, `ChargeOrderUseCase`, `CreateDirectSaleUseCase`, `MarkSettlementPaidUseCase` | null |
| `cash.session.opened` | `OpenCashSessionUseCase` | null |
| `cash.session.closed` | `CloseCashSessionUseCase` | null |

### Liquidaciones

| Evento | Use Case | target_role |
|--------|---------|-------------|
| `settlement.generated` | `GenerateCurrentShiftSettlementsUseCase` | null |
| `settlement.paid` | `MarkSettlementPaidUseCase` | null |

---

## Payload mínimo

Cada evento sigue la estructura:

```json
{
  "entity": { "type": "order", "id": 1 },
  "summary": "Texto breve descriptivo",
  "refresh": ["orders", "cash"]
}
```

---

## Corrección: findSince con admin (roleScope = null)

Se corrigió el método `EloquentOperationalEventRepository::findSince()` para que cuando `roleScope` es `null` (admin), no aplique ningún filtro por `target_role` y pueda ver TODOS los eventos. Para roles específicos, filtra broadcast (null) + propio rol.

```php
->when($roleScope !== null, function ($q) use ($roleScope) {
    $q->where(function ($inner) use ($roleScope) {
        $inner->whereNull('target_role')
            ->orWhere('target_role', $roleScope);
    });
})
```

---

## Mejoras adicionales al sistema de tests

- Se movieron `nightposSeedOrderProduct()` y `nightposCreateOrderWithItem()` de `OrderApiTest.php` a `Pest.php` para que estén globalmente disponibles (resuelve fallos en tests paralelos).

---

## Tests nuevos (Sse2OperativeEventsTest)

| # | Descripción | Verificación |
|---|-------------|-------------|
| 1 | Crear pieza emite `room_service.created` | Contador +1, entity.type correcto |
| 2 | Pieza vencida emite `room_service.due` | Artisan check-due → contador +1 |
| 3 | Marcar habitación limpia emite `room.cleaned` | Contador +1 |
| 4 | Marcar habitación limpia emite `cleaning.earnings.updated` | target_role = 'cleaning' |
| 5 | Enviar comanda a barra emite `order.sent_to_bar` | entity.id correcto |
| 6 | Cobrar comanda emite `order.billed` + `sale.created` | Ambos contadores +1 |
| 7 | Venta directa emite `direct_sale.created` + `cash.movement.created` | Ambos contadores +1 |
| 8 | Pagar liquidación emite `settlement.paid` + `cash.movement.created` | Ambos contadores +1 |
| 9 | Limpieza no recibe eventos de caja con target_role='cleaning' | Count = 0 eventos targeted |
| 10 | Cajera recibe `order.sent_to_bar` (broadcast) | Count > 0 |
| 11 | Admin recibe todos los eventos (null scope) | 3 tipos distintos visibles |
| 12 | Tenant/branch aislado | Eventos de otro tenant no visibles |

---

## Arquitectura de emisión

Los Use Cases inyectan `OperationalEventEmitter` vía DI constructor. La emisión ocurre **DESPUÉS** de la transacción, garantizando consistencia. Si la emisión falla, el error se propaga (no se silencia) para mantener observabilidad.

---

## Total tests: 365 pasando, 0 fallando
