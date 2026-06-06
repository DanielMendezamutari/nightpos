# POS-CAT — Frontend: selector de catálogo vendible

## Objetivo

Evitar mostrar 200+ productos de golpe en caja, venta directa y modo garzón. El usuario ve primero favoritos, recientes, categorías y buscador; el grid/lista aparece solo con intención explícita.

## Componente central

`src/components/nightpos/catalog/PosProductPicker.vue`

- Búsqueda (mín. 2 letras vía API)
- Chips de favoritos y recientes (`useOrderProductShortcuts`)
- Chips de categorías con conteo desde API
- Grid (venta directa / garzón) o lista (cajera en comanda)
- Límite 20 resultados + aviso `has_more`
- Botones Solo / Con acompañante deshabilitados sin precio
- Configurar precio inline cuando hay permiso

## Composable

`src/composables/usePosCatalog.js`

- Consume `GET /products/pos-catalog`
- `showResults`: controla cuándo pedir productos
- `fetchByIds`: carga favoritos/recientes por ID
- Debounce 300 ms en búsqueda

## API

`fetchPosCatalog(params)` en `src/api/products.js`

## Pantallas integradas

| Pantalla | Cambio |
|----------|--------|
| `/nightpos/cash/direct-sale` | Catálogo vía `PosProductPicker`; sin `fetchProducts` masivo |
| `OrderAddProductDialog` | Usa picker internamente; sin props `products` / `categories` |
| `waiter/orders/[id]` | Ya no precarga catálogo completo |
| `orders/[id]` | Igual; refresco del picker tras crear precio/producto |

## Productos sin precio (admin)

`src/pages/nightpos/products/unpriced.vue`

- Tab **Sin precio** en `CATALOG_SECTION_TABS`
- Lista activos sin precio (`unpriced_only=1`)
- Acción **Configurar precio** con `QuickProductPriceCreateDialog`
- Tras guardar precio → producto deja de aparecer aquí y pasa a ser vendible en POS

## Reglas UX (cumplidas)

1. No grid inicial vacío de 200 tarjetas
2. Favoritos / recientes visibles sin búsqueda
3. Categorías visibles con conteo
4. Resultados solo con ≥ 2 letras, categoría o favoritos/recientes
5. Productos sin precio no se pueden agregar al carrito/comanda (botones disabled + backend 422)

## Validación manual sugerida

| Escenario | Esperado |
|-----------|----------|
| 20 / 100 / 200 productos | Sin scroll largo al abrir; búsqueda fluida en móvil |
| Garzón agrega bebida | Categoría o favorito → máx. 20 ítems |
| Venta directa caja | Misma UX; recientes al vender |
| Producto sin precio | No vendible; pantalla Sin precio + configurar → vendible |

## Archivos nuevos / tocados

- `src/components/nightpos/catalog/PosProductPicker.vue` (nuevo)
- `src/composables/usePosCatalog.js` (nuevo)
- `src/pages/nightpos/products/unpriced.vue` (nuevo)
- `src/pages/nightpos/cash/direct-sale.vue`
- `src/components/nightpos/orders/OrderAddProductDialog.vue`
- `src/pages/nightpos/waiter/orders/[id].vue`
- `src/pages/nightpos/orders/[id].vue`
- `src/composables/useStaffSectionTabs.js`
- `src/api/products.js`

## Próximo paso

**SSE-1** y **SSE-2** — pendientes tras POS-CAT.
