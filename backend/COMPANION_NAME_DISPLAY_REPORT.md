# Fase A — Nombre de acompañante visible (Backend)

**Fecha:** 2026-06-16  
**Estado:** ✅ Completado  
**Par frontend:** `frontend/COMPANION_NAME_DISPLAY_REPORT.md`  
**Auditoría origen:** `backend/WAITER_TABLES_COMPANION_BRACELET_AUDIT.md` (Parte 2)

---

## Problema

Los ítems simples `CON_ACOMPANANTE` guardaban `girl_user_id` pero la API no devolvía el nombre de la chica. Combos ya exponían `girl_name` en `allocations[]`.

## Solución

### `girl_name` en ítems simples

- **`OrderMapper::item()`** — acepta `?string $girlName`; incluye `girl_name` solo cuando `sale_mode = CON_ACOMPANANTE`, `!requires_allocation` y `girl_user_id` presente.
- **`OrderPresentationService`** — batch load de nombres vía `UserRepositoryInterface::findDisplayNamesByIds()` (sin N+1).
- Use cases que devolvían mapper directo migrados a presentation service: `RemoveOrderItemUseCase`, `CancelOrderItemUseCase`, `UpdateOrderHeaderUseCase`.

### Ventas / tickets

- **`SaleAllocationPresenter`** — enriquece ítems de venta simples `CON_ACOMPANANTE` con `girl_name` usando el mismo batch load.

### Liquidaciones (mejora opcional incluida)

- **`EloquentStaffSettlementRepository`** — descripción `GIRL_CONSUMPTION`: `{producto} — 1 manilla — {nombre chica}`.

### Repositorio

- **`UserRepositoryInterface::findDisplayNamesByIds(array $userIds): array<int, string>`**
- **`EloquentUserRepository`** — implementación con una sola query `whereIn`.

---

## Contrato JSON (ítem simple CON_ACOMPANANTE)

```json
{
  "sale_mode": "CON_ACOMPANANTE",
  "girl_user_id": 42,
  "girl_name": "María",
  "requires_allocation": false
}
```

- **SOLO_CLIENTE:** no incluye `girl_name`.
- **Combo (`requires_allocation`):** sin `girl_name` a nivel ítem; sigue en `allocations[].girl_name`.

---

## Endpoints afectados

| Endpoint | Campo nuevo |
|----------|-------------|
| `GET /api/v1/orders/{id}` | `items[].girl_name` |
| `GET /api/v1/orders/{id}/precheck` | `order.items[].girl_name` |
| `GET /api/v1/sales/{id}` | `items[].girl_name` |
| Respuestas mutación de comanda (add/update/remove/cancel ítem) | ítems enriquecidos |

---

## Tests

`tests/Feature/Api/V1/CompanionNameDisplayTest.php` (5 escenarios):

1. Detalle comanda `CON_ACOMPANANTE` → `girl_name`
2. Precheck → `girl_name`
3. Venta cobrada → `girl_name`
4. `SOLO_CLIENTE` → sin clave `girl_name`
5. Combo → `allocations[].girl_name` intacto, sin `girl_name` en ítem

**Suite completa:** 519 tests passing.

---

## Sin romper

- Combos CBA / allocations
- `table_label` histórico
- Liquidaciones parciales
- SSE P0

---

## Pendiente (Fases B–D)

Ver `WAITER_TABLES_COMPANION_BRACELET_AUDIT.md`: mesas MVP, UI «Mis mesas», unificación copy manillas.
