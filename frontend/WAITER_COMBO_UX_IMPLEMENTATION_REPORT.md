# Implementación UX — Flujo híbrido garzón para combos (producción)

**Fecha:** 2026-06-16  
**Estado:** ✅ Implementado  
**Auditoría base:** conversación UX aprobada (flujo híbrido A+B)  
**Backend:** sin cambios — reutiliza CBA-1…CBA-6

---

## Objetivo

Optimizar el **caso estadístico más frecuente**: combo completo para **una sola chica** en 3–5 segundos, manteniendo reparto multichica táctil para el resto.

---

## Principios UX

| Antes | Después |
|-------|---------|
| Elegir Solo / Con acompañante en combos | **Eliminado** para `requires_allocation` |
| Selector de una chica + diálogo de reparto | Flujo combo dedicado |
| Autocomplete + filas dinámicas | **Todas las chicas del turno** visibles |
| Lenguaje técnico (modalidad, allocations) | **Manillas, reparto, chicas** |

Productos normales (Paceña, Huari, etc.) **sin cambios**.

---

## Flujo híbrido

### Entrada — `PosProductPicker`

- Combos: botón único **«Agregar combo»** (precio combo, badge «Combo N manillas»).
- Normales: botones Solo / Con acompañante (igual que antes).

### Paso 1 — `ComboAllocationDialog`

```
Este combo genera 6 manillas
[Cantidad opcional en alta]

¿Cómo quieres repartirlas?
○ Todas para una sola chica (Recomendado)
○ Repartir entre varias chicas
```

### Caso A — Una sola chica

- Grid de tarjetas grandes (María, Laura, Ana…).
- **Un toque** → asigna `N/N` manillas → guarda → cierra.
- Tiempo objetivo: **3–5 s**.

### Caso B — Varias chicas — `BraceletAllocationPanel`

- Lista fija de chicas del turno.
- Por chica: `[-] count [+]` (sin dropdown, sin escribir números).
- Indicador visual `●●●○○○` (`BraceletDotsIndicator`).
- Cantidad > 1: banner «2 combos = 12 manillas».
- Botón **Guardar** habilitado solo en `N/N` (verde).

---

## Componentes

| Componente | Rol |
|------------|-----|
| `ComboAllocationDialog.vue` | Orquestador híbrido (split → single / multi) |
| `BraceletAllocationPanel.vue` | Reparto táctil multichica |
| `BraceletDotsIndicator.vue` | Círculos de progreso |
| `useComboAllocation.js` | Helpers mapa unidades, payload API |
| `OrderAddProductDialog.vue` | Entrada combo vs normal |
| `PosProductPicker.vue` | Botón combo sin modalidad |

---

## Integración comanda

### Alta (`OrderAddProductDialog` → página garzón/cajera)

1. Tap combo → `ComboAllocationDialog`.
2. Usuario reparte.
3. `addOrderItem` + `syncOrderItemAllocations` en un flujo (payload con `allocations[]`).

### Vista línea (garzón móvil)

```
Combo 6 Cervezas × 2
Manillas: 12/12
Distribución
María ×6
Laura ×4
Ana ×2
[ Editar reparto ]
```

### Edición

- **Editar reparto** abre el mismo `ComboAllocationDialog` en `editMode` (panel multi directo).

### Cambio de cantidad (`OrderItemsTable`)

- Tras `updateOrderItem` con qty distinta en combo incompleto:
- Aviso: «Ahora debes repartir 12 manillas».
- Reabre editor de reparto automáticamente.

---

## Checklist manual (UX / regresión)

| # | Escenario | Resultado esperado |
|---|-----------|-------------------|
| 1 | Combo → una sola chica | 1 tap en chica, 6/6, línea completa |
| 2 | Combo → varias chicas (3+2+1) | Panel táctil, guardar en 6/6 |
| 3 | Cantidad 2 → 12 manillas | Banner y dots ×12 antes de repartir |
| 4 | Editar reparto | Mismo diálogo, datos precargados |
| 5 | Cambiar cantidad 1→2 | Aviso + re-reparto obligatorio |
| 6 | Enviar a barra | Bloqueo si reparto incompleto |
| 7 | Cobrar | Bloqueo si reparto incompleto (cajera) |
| 8 | Liquidar | `GIRL_BRACELET_ALLOCATION` por chica (backend existente) |
| 9 | Producto normal CON_ACOMPANANTE | Sin cambios (1 chica) |
| 10 | Venta directa combo | Sigue bloqueada (CBA-5) |

---

## Tiempos objetivo

| Caso | Clics | Tiempo |
|------|-------|--------|
| 6 manillas → María | ~4 | 3–5 s |
| 3+2+1 multichica | ~8 | 6–10 s |

---

## Archivos modificados

- `src/components/nightpos/orders/ComboAllocationDialog.vue`
- `src/components/nightpos/orders/BraceletAllocationPanel.vue`
- `src/components/nightpos/orders/BraceletDotsIndicator.vue` (nuevo)
- `src/components/nightpos/orders/OrderAddProductDialog.vue`
- `src/components/nightpos/catalog/PosProductPicker.vue`
- `src/components/nightpos/orders/OrderItemsTable.vue`
- `src/composables/useComboAllocation.js` (nuevo)
- `src/pages/nightpos/waiter/orders/[id].vue`
- `src/pages/nightpos/orders/[id].vue`

---

## Notas

- API: `PUT /orders/{id}/items/{itemId}/allocations` — sin cambios.
- `sale_mode` sigue siendo `CON_ACOMPANANTE` en backend para combos; la UI ya no lo expone al garzón.
