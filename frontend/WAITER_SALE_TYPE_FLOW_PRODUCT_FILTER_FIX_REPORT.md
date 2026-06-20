# Fix — Productos antiguos no aparecen en garzón (filtro por tipo)

**Fecha:** Jun 2026  
**Estado:** Corregido (frontend)

---

## Síntoma

Tras implementar Solo | Con compañía | Combos | Otros, productos que antes aparecían en comanda (ej. Paceña) dejaban de listarse al elegir un tipo de venta.

---

## Causa raíz

**No era solo el filtro client-side.** Había dos problemas:

### 1. POS-CAT no devuelve productos sin criterio de búsqueda (principal)

`GET /products/pos-catalog` solo incluye `products[]` cuando hay:

- búsqueda ≥ 2 caracteres, **o**
- `category_id`, **o**
- `product_ids`

El flujo garzón con `intent` activaba `effectiveShowResults = true` en UI pero llamaba `fetchResults()` **sin** search/categoría → el API respondía `products: []`.

Antes el garzón escribía 2+ letras o elegía categoría; con el nuevo flujo se esperaba listado inmediato.

### 2. Filtro client-side demasiado estricto (secundario)

- `productActivePrice()` exigía `status === 'active'` exacto y no normalizaba `sale_mode`.
- Combos solo por `requires_allocation === true` (sin `settlement_behavior === GIRL_BRACELET_ALLOCATION`).
- No se usaba `has_active_pricing` ni `category_name` del payload POS-CAT.

---

## Corrección

### `usePosCatalog.js`

Nuevo método **`fetchAllSellableProducts()`**:

- Carga meta + categorías.
- Pide productos por cada categoría (y `category_id=0` sin categoría) en paralelo, `limit=50`.
- Fusiona y ordena por nombre.

### `useProductLabels.js`

- `normalizeActivePrices()` — array u objeto legacy.
- `isActivePriceRow()` — status case-insensitive, valida precio.
- `productActivePrice()` / `productHasActivePrice()` — normalización de `sale_mode`.
- `isComboCatalogProduct()` — `requires_allocation` **o** `GIRL_BRACELET_ALLOCATION`.
- `isSellableCatalogProduct()` — `has_active_pricing` o precios activos normalizados.

### `PosProductPicker.vue`

- Con `intent` activo → `fetchAllSellableProducts()` al montar/cambiar intent.
- Búsqueda ≥ 2 chars sigue usando `fetchResults()` + filtro intent.
- Filtros solo/compañía/combo/otros con helpers normalizados.
- Mensaje vacío: *«No hay productos con precio configurado para esta opción.»*
- Intent `all` → catálogo mixto con botones Solo/+Acomp. (fallback).

### `WaiterSaleTypeTabs.vue`

- Botón secundario **Ver todos** (`intent=all`).

---

## Sin cambios

- Backend POS-CAT.
- Cajera, venta directa, liquidaciones, combos backend.

---

## Validación manual

| Caso | Esperado |
|------|----------|
| Paceña SOLO_CLIENTE en Solo | Visible |
| Paceña CON_ACOMPANANTE en Con compañía | Visible |
| Combo antiguo en Combos | Visible |
| Sin precio activo | Oculto |
| Ver todos | Catálogo mixto legacy |
| POS cajera | Sin cambios |

---

## Archivos tocados

- `frontend/src/composables/usePosCatalog.js`
- `frontend/src/composables/useProductLabels.js`
- `frontend/src/composables/useOrderHelpers.js`
- `frontend/src/components/nightpos/catalog/PosProductPicker.vue`
- `frontend/src/components/nightpos/waiter/WaiterSaleTypeTabs.vue`
- `frontend/src/components/nightpos/orders/OrderAddProductDialog.vue`
