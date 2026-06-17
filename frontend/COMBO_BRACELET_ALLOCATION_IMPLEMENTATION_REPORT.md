# Implementación frontend — Combos con manillas multichica (CBA-5)

**Fecha:** 2026-06-16  
**Estado:** ✅ Implementado V1 + **UX híbrido garzón (2026-06-16)**  
**Auditoría base:** `COMBO_BRACELET_ALLOCATION_AUDIT.md`  
**UX garzón:** `WAITER_COMBO_UX_IMPLEMENTATION_REPORT.md`  
**Par backend:** `../backend/COMBO_BRACELET_ALLOCATION_IMPLEMENTATION_REPORT.md`

---

## Resumen

UI para repartir manillas de combos entre chicas en garzón móvil y cajera, con bloqueos de envío/cobro y venta directa bloqueada para combos.

**Actualización UX (producción):** flujo híbrido — atajo «todas para una chica» + reparto táctil multichica. Combos ya no muestran Solo/Con acompañante en picker.

---

## Componentes

### `BraceletAllocationPanel.vue`

Panel reutilizable (modo multichica):

- Todas las chicas del turno visibles (sin autocomplete ni filas dinámicas)
- `[-] unidades [+]` por chica
- Indicador `●●●○○○` vía `BraceletDotsIndicator`
- Banner «2 combos = 12 manillas» cuando qty > 1
- Emite allocations `{ girl_user_id, units }[]`

### `ComboAllocationDialog.vue`

Wrapper fullscreen híbrido:

1. **Paso split:** ¿una chica (recomendado) o varias?
2. **Caso A:** grid de chicas — un toque asigna todas las manillas
3. **Caso B:** `BraceletAllocationPanel`
4. **editMode:** abre panel multi directo (editar reparto)

### `BraceletDotsIndicator.vue`

Círculos de progreso N/N (verde al completar).

### `useComboAllocation.js`

Helpers: normalizar chicas, mapa unidades, payload API.

---

## API cliente

`syncOrderItemAllocations(orderId, itemId, allocations)` → `PUT .../allocations`

---

## Garzón móvil

**`pages/nightpos/waiter/orders/[id].vue`**

1. Tap **Agregar combo** en picker (sin Solo/Acomp.)
2. `ComboAllocationDialog`: atajo una chica o reparto táctil
3. `addOrderItem` + `syncOrderItemAllocations` al confirmar
4. Bloquea envío a barra si `itemsNeedingAllocation(order).length`
5. Línea muestra manillas, distribución, **Editar reparto**

---

## Cajera / corrección comanda

**`pages/nightpos/orders/[id].vue`**

- Tras agregar combo → abre editor de reparto vía `OrderItemsTable.openAllocationForItem`
- Bloqueo envío barra y cobro si falta reparto
- Modo corrección de caja mantiene flujo existente

**`OrderItemsTable.vue`**

- Columna/resumen de allocations en línea
- Acción «Repartir manillas» → dialog con `BraceletAllocationPanel`
- Recalcula al cambiar cantidad (backend limpia allocations; UI obliga re-reparto)

---

## Catálogo

**`ProductFormFields.vue`** — pestaña avanzada en edición:

- `settlement_behavior` (GIRL_LINE | GIRL_BRACELET_ALLOCATION | NONE)
- `bracelet_units_per_line` cuando es combo

**`products/[id]/edit.vue`** — carga y guarda campos settlement.

**`PosProductPicker.vue`** — badge «Combo N manillas» si `requires_allocation`.

---

## Venta directa

**`cash/direct-sale.vue`** — bloquea productos con `requires_allocation`:

> Este combo debe venderse por comanda para asignar manillas.

---

## Composables

**`useOrderHelpers.js`**

- `itemsNeedingAllocation(order)` — líneas combo incompletas
- `formatAllocationSummary(item)` — texto multichica
- `itemsNeedingGirl(order)` — excluye combos (usan allocations)

---

## Checklist manual V1

| # | Escenario | Estado |
|---|-----------|--------|
| 1 | Garzón agrega combo | ✅ |
| 2 | Reparte 6 manillas | ✅ |
| 3 | Ve distribución en detalle | ✅ |
| 4 | Envía a barra | ✅ |
| 5 | Cajera cobra | ✅ |
| 6 | Genera liquidaciones | ✅ (backend) |
| 7 | Pago correcto por chica | ✅ (backend) |
| 8 | Venta directa bloquea combo | ✅ |

---

## Pendiente V2

- Venta directa con allocator
- Campos settlement en alta rápida (`products/create.vue` quick path)

**CBA-6 implementado:** ver `COMBO_BRACELET_REPORTING_CLOSURE_REPORT.md`.
