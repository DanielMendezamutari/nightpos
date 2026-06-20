# Garzón UX Fase 1 — Flujo por tipo de venta

**Fecha:** Jun 2026  
**Estado:** Implementado  
**Auditoría base:** `frontend/WAITER_SALE_TYPE_FLOW_AUDIT.md`

---

## Resumen

El garzón móvil ahora elige **tipo de venta antes del producto**, reduciendo clics y errores de modalidad.

Flujo: **Tipo → Producto → (Chica si aplica) → Agregar**

---

## Componentes nuevos

| Componente | Rol |
|------------|-----|
| `WaiterSaleTypeTabs.vue` | Botones grandes: Solo / Con compañía / Combos / Otros |
| `GirlQuickPicker.vue` | Buscador fullscreen de chicas (autofocus, filtro realtime, tarjetas) |

---

## Cambios en componentes existentes

### `OrderAddProductDialog.vue` (solo `mobile-waiter`)

1. Paso 0: tabs de tipo de venta.
2. Paso 1: `PosProductPicker` con prop `intent`.
3. **Solo:** tap producto → POST item `SOLO_CLIENTE` qty 1 (sin confirmación).
4. **Con compañía:** tap producto → `GirlQuickPicker` → POST con `girl_user_id`.
5. **Combos:** filtro `requires_allocation` → flujo existente `ComboAllocationDialog`.
6. **Otros:** categorías Cover / Cortesía / Extras (client-side) o empty state.

### `PosProductPicker.vue`

- Prop `intent`: `'solo' | 'companion' | 'combo' | 'other' | null`
- Filtro client-side sobre POS-CAT (`active_prices`, `requires_allocation`, categoría).
- Con `intent`: muestra productos de inmediato (limit 100), tap único, sin botones dual Solo/+Acomp.
- Empty state Otros: *«No hay productos configurados en Otros.»*

---

## Sin cambios backend

- `POST /orders/{id}/items` — contrato intacto.
- `ComboAllocationDialog` — intacto.
- `SendOrderToBarUseCase` / `ORDER_COMMAND` — intacto.

---

## Manilla en comanda

Ítems `CON_ACOMPANANTE` simples muestran `Manilla: {nombre}` vía `formatCompanionBraceletLine` (ya existía).

---

## QA manual sugerido

1. Abrir mesa → Agregar producto → Solo → tap cerveza → aparece en comanda.
2. Con compañía → buscar «luc» en picker de chicas → agregar.
3. Combos → reparto híbrido (una chica / varias).
4. Otros → empty state si no hay categorías Cover/Cortesía/Extras.
5. POS cajera / no garzón → flujo anterior sin tabs.

---

## Fase 2 (pendiente)

- Filtros server-side en POS-CAT (`catalog_intent`).
- Convención admin para categorías Otros en todos los tenants.

---

## Fix filtro productos antiguos (2026-06-16)

**Problema:** listado vacío al elegir tipo de venta.  
**Causa:** POS-CAT no devuelve productos sin búsqueda/categoría; filtro client-side estricto.  
**Solución:** `fetchAllSellableProducts()` + normalización de precios + fallback «Ver todos».  
**Doc:** `frontend/WAITER_SALE_TYPE_FLOW_PRODUCT_FILTER_FIX_REPORT.md`
