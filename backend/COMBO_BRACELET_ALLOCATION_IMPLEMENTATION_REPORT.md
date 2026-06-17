# Implementación — Combos con manillas multichica (CBA-1 a CBA-4)

**Fecha:** 2026-06-16  
**Estado:** ✅ Implementado V1  
**Auditoría base:** `COMBO_BRACELET_ALLOCATION_AUDIT.md`  
**Par frontend:** `../frontend/COMBO_BRACELET_ALLOCATION_IMPLEMENTATION_REPORT.md`

---

## Resumen

Se implementó el flujo completo de combos con reparto obligatorio de manillas entre una o más chicas:

- Catálogo declarativo (sin hardcode por nombre)
- Persistencia en comanda (`order_item_allocations`)
- Snapshot al cobrar (`sale_item_allocations`)
- Liquidación por chica vía `GIRL_BRACELET_ALLOCATION`
- Bloqueo de venta directa para combos con allocation (V2 pendiente)

**Regla:** `SUM(units) = quantity × bracelet_units_per_line`

**Suite:** 493 tests passing (`ComboBraceletAllocationTest`: 11 tests).

---

## CBA-1 — Catálogo combo

### Migración

`database/migrations/2026_06_16_140000_combo_bracelet_allocation.php`

| Campo | Tipo | Ejemplo combo 6 cervezas |
|-------|------|--------------------------|
| `settlement_behavior` | string | `GIRL_BRACELET_ALLOCATION` |
| `bracelet_units_per_line` | int | `6` |
| `requires_allocation` | bool | `true` |
| `allocation_type` | string | `GIRL_BRACELET_UNITS` |

Valores de dominio: `SettlementBehavior`, `AllocationType`.

`ProductSettlementNormalizer` deriva flags automáticamente cuando `settlement_behavior = GIRL_BRACELET_ALLOCATION`.

### API productos

Create/Update/Show exponen los cuatro campos. Sin lógica por nombre de producto.

---

## CBA-2 — Allocations en comanda

### Tabla `order_item_allocations`

| Columna | Descripción |
|---------|-------------|
| `order_item_id` | Línea de comanda |
| `girl_user_id` | Chica activa rol GIRL, mismo tenant/sucursal |
| `units` | Manillas asignadas (> 0) |
| `unit_amount` | `girl_amount / bracelet_units_per_line` |
| `total_amount` | `units × unit_amount` |
| `allocation_type` | `GIRL_BRACELET_UNITS` |

### Validación

`BraceletAllocationValidator`:

- Chica activa, rol GIRL, scope tenant/sucursal
- Suma exacta de unidades
- Rechaza asignación directa de chica en línea combo (`AssignOrderItemGirlUseCase`)

### Endpoint

```
PUT /api/v1/orders/{orderId}/items/{itemId}/allocations
Body: { "allocations": [{ "girl_user_id": 1, "units": 3 }] }
Permiso: orders.add_items
```

`SyncOrderItemAllocationsUseCase` reemplaza allocations atómicamente.

### Readiness

`OrderItemReadinessChecker` + `SendOrderToBarUseCase` / `ChargeOrderUseCase` bloquean si falta reparto.

`OrderMapper::item()` enriquece respuesta:

- `requires_allocation`, `required_bracelet_units`, `allocated_bracelet_units`
- `allocation_complete`, `allocations[]`

Al cambiar cantidad o producto en línea combo, se limpian allocations (`UpdateOrderItemUseCase`).

---

## CBA-3 — Cobro y snapshot

### Tabla `sale_item_allocations`

Copia congelada al cobrar en `ChargeOrderUseCase`:

- `units`, `unit_amount_snapshot`, `total_amount_snapshot`, `girl_user_id`
- No depende de `order_items` post-cobro

Combo en línea: `girl_user_id = null` en `sale_items`; reparto solo vía allocations.

---

## CBA-4 — Liquidaciones

### Nuevo `source_type`

`GIRL_BRACELET_ALLOCATION` — distinto de:

- `GIRL_CONSUMPTION` (línea simple CON_ACOMPAÑANTE)
- `GIRL_BRACELET` (manillas manuales legacy)

### Generación

`EloquentStaffSettlementRepository::generateForShift`:

- Itera `sale_item_allocations` del turno
- Crea un ítem de liquidación por allocation/chica
- Omite `GIRL_CONSUMPTION` si el `sale_item` tiene allocations
- `source_id = sale_item_allocations.id`
- `sale_item_id = null` en settlement item (evita unique `(sale_item_id, source_type)` con múltiples allocations)

Deduplicación por `(source_type, source_id)` — compatible con liquidaciones parciales existentes.

---

## Venta directa (V1)

`CreateDirectSaleUseCase` rechaza productos con `requires_allocation`:

> Este combo debe venderse por comanda para asignar manillas.

---

## Archivos principales

| Área | Archivos |
|------|----------|
| Dominio | `Product`, `OrderItemAllocation`, `SettlementBehavior`, `AllocationType` |
| Validación | `BraceletAllocationValidator`, `OrderItemReadinessChecker` |
| Use cases | `SyncOrderItemAllocationsUseCase`, cambios en `Add/Update/Send/Charge` |
| Repos | `EloquentOrderItemAllocationRepository`, `EloquentSaleItemAllocationRepository` |
| Liquidaciones | `EloquentStaffSettlementRepository` |
| Tests | `tests/Feature/Api/V1/ComboBraceletAllocationTest.php` |

---

## Tests obligatorios (11/11 ✅)

1. Producto combo expone flags de allocation en catálogo
2. Combo sin allocations no envía a barra
3. 5 manillas → 422
4. 7 manillas → 422
5. 6 manillas → OK + envío barra
6. 2 combos → 12 manillas obligatorias
7. Cobro crea `sale_item_allocations`
8. Liquidación genera `GIRL_BRACELET_ALLOCATION` por chica
9. Regenerar no duplica por allocation
10. Producto simple CON_ACOMPAÑANTE sin cambios
11. Venta directa bloqueada para combo

---

## No modificado (compatibilidad)

- Productos simples SOLO / CON_ACOMPAÑANTE (`GIRL_LINE`, `GIRL_CONSUMPTION`)
- Manillas manuales legacy (`bracelets`, `GIRL_BRACELET`)
- Liquidaciones parciales / múltiples cortes
- Piezas, shows, inventario/kardex

---

## Pendiente V2

- Venta directa con allocator
- Alta rápida de producto combo en `quickStore`

**CBA-6 implementado:** ver `COMBO_BRACELET_REPORTING_CLOSURE_REPORT.md`.
