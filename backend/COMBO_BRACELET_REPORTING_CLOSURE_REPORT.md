# CBA-6 — Reportes, cierre y precuenta para combos con manillas

**Fecha:** 2026-06-16  
**Estado:** ✅ Implementado V1  
**Depende de:** CBA-1 a CBA-5 (`COMBO_BRACELET_ALLOCATION_IMPLEMENTATION_REPORT.md`)  
**Par frontend:** `../frontend/COMBO_BRACELET_REPORTING_CLOSURE_REPORT.md`

---

## Resumen

Los combos con reparto de manillas ahora son visibles en reportes, cierres de caja/turno, liquidaciones, tickets y precuenta — sin modificar la lógica de allocation/cobro de CBA-1 a CBA-5.

---

## Servicio central

`App\Application\Reports\Services\ComboBraceletReportingService`

| Método | Uso |
|--------|-----|
| `buildScopeSummary()` | Resumen combos/manillas por turno, caja o filtros de reporte |
| `enrichSaleItemRow()` | Campos allocation en ítems de venta |
| `enrichSettlementItem()` | Descripción legible para `GIRL_BRACELET_ALLOCATION` |
| `braceletUnitsSoldByProduct()` | Manillas por producto en conciliación |

---

## Endpoints extendidos

| Endpoint | Datos nuevos |
|----------|--------------|
| `GET reports/daily` | `combo_bracelets` |
| `GET reports/sales` | Por ítem: `requires_allocation`, `required/allocated_bracelet_units`, `allocations[]` |
| `GET reports/settlements` | `items[]` con `display_description`, `units`, `unit_amount` |
| `GET reports/services` | `combo_allocations[]` |
| `GET reports/product-reconciliation` | `bracelet_units_sold`, `combo_quantity`, `combo_bracelets`, `total_bracelet_units` |
| `GET reports/shift-closure` | `combo_bracelets` |
| `GET cash/session/current` | `session.combo_bracelets` |
| `GET cash/session/current/close-check` | `combo_bracelets` |
| `GET shifts/{id}/summary` | `combo_bracelets` |
| `GET sales/{id}` | Ítems con `allocations[]` (snapshot) |
| `GET orders/{id}/precheck` | Precuenta con allocations (nuevo) |

---

## Precuenta

`GET /api/v1/orders/{id}/precheck` — `GetOrderPrecheckUseCase`

- Solo comandas no cobradas/canceladas
- Payload: `{ precheck: { label: "PRECUENTA — NO PAGADO", is_precheck: true, order: {...} } }`
- Incluye allocations en ítems combo

---

## Liquidaciones legibles

Para `GIRL_BRACELET_ALLOCATION`:

```
display_description: "Combo 6 Cervezas — 3 manillas"
units, unit_amount, allocation_total_amount, sale_number, order_id
```

Enriquecido en reporte de liquidaciones y `GetSettlementUseCase`.

---

## Tests

`tests/Feature/Api/V1/ComboBraceletReportingTest.php` — 10 tests:

1. Reporte ventas incluye allocations
2. Conciliación suma manillas
3. Reporte liquidaciones con units y display_description
4. Cierre caja incluye manillas
5. Cierre turno incluye distribución por chica
6. Precheck incluye allocations
7. Detalle venta (ticket) incluye allocations
8. Productos simples sin cambios
9. Aislamiento tenant
10. Aislamiento branch

---

## Sin romper

- Productos simples CON_ACOMPAÑANTE
- Manillas legacy (`GIRL_BRACELET`)
- Liquidaciones parciales
- Venta directa normal (combos siguen bloqueados)
