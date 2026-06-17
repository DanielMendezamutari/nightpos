# CBA-6 Frontend — Reportes, cierre y precuenta combos

**Fecha:** 2026-06-16  
**Estado:** ✅ Implementado V1  
**Par backend:** `../backend/COMBO_BRACELET_REPORTING_CLOSURE_REPORT.md`

---

## Componentes nuevos

| Componente | Uso |
|------------|-----|
| `ComboBraceletSummaryPanel.vue` | Resumen combos/manillas en caja y cierre turno |
| `PrintablePrecheckTicket.vue` | Ticket precuenta con distribución |
| `pages/nightpos/print/precheck/order/[id].vue` | Ruta `/nightpos/print/precheck/order/:id` |

---

## API

- `fetchOrderPrecheck(id)` → `GET /orders/{id}/precheck`

---

## Garzón móvil

`waiter/orders/[id].vue` — botón **Ver precuenta** abre print route con auto-print.

---

## Reportes

`finance/reports/index.vue`:

- Tab **Ventas**: bloque «Combos con manillas» por ítem
- Tab **Liquidaciones**: tabla manillas combo por chica con `display_description`

`ProductReconciliationPanel.vue` — columna **Manillas** en detalle vendido.

---

## Cierre caja / turno

- `cash/index.vue` — `ComboBraceletSummaryPanel` en conciliación y diálogo cierre
- `shifts/close.vue` — panel combos desde `summary.combo_bracelets`

---

## Tickets imprimibles

- `PrintableSaleTicket.vue` — manillas y reparto por chica
- `PrintableOrderTicket.vue` — allocations en comanda barra
- `PrintablePrecheckTicket.vue` — banner PRECUENTA + distribución

---

## Liquidaciones detalle

`settlements/[id].vue` — muestra `display_description`, units y montos para allocations combo.

---

## Checklist manual

1. Combo repartido → Ver precuenta garzón
2. Cobrar → ticket con distribución
3. Generar liquidaciones → descripción legible por chica
4. Reportes ventas/liquidaciones/conciliación
5. Cierre caja y turno muestran manillas
