# Selector de productos — UX modo garzón

## 1. Textos técnicos ocultados

Helper `composables/useProductLabels.js`:

| Valor técnico | Texto usuario |
|---------------|---------------|
| `beverage` | Bebidas |
| `food` | Comida |
| `service` | Servicios |
| `SOLO_CLIENTE` | Solo / Solo cliente |
| `CON_ACOMPANANTE` | Con acompañante |
| `active` | Activo |
| `inactive` | Inactivo |

Funciones exportadas también desde `useOrderHelpers.js`: `formatProductType()`, `formatSaleMode()`, `formatStatus()`, `productCategoryLabel()`, `productActivePrice()`.

**Causa del bug “beverage”:** el mapa usaba claves en mayúsculas (`BEVERAGE`) pero la API devuelve `beverage` en minúsculas.

## 2. Categorías

- Prioridad: `category.name` (mapa desde `fetchCategories`) → si no hay, `formatProductType(product_type)`.
- Chips horizontales: **Todas** + categorías detectadas en el catálogo (ej. Cervezas, Whisky según datos reales).
- No se muestra `beverage` ni slugs en inglés.

## 3. Componentes plantilla (Vuetify)

- `VDialog` fullscreen + `VToolbar`
- `VTextField` buscador
- `VChip` favoritos, recientes y categorías
- `VCard` / `VRow` / `VCol` grid de productos (1 col móvil, 2 en `md+`)
- `VBtn` modalidad por producto (Solo / Con acompañante)
- `VIcon` favoritos
- Sin tablas en modo garzón

## 4. Datos

- Garzón carga productos con `include=active_prices` para mostrar precios en cards sin esperar preview.
- Categorías cargadas en paralelo para etiquetas legibles.

## 5. Validación móvil (`pnpm run dev`)

1. Login garzón PIN `5678`.
2. Abrir comanda → **+ Bebida**.
3. Confirmar título “Agregar bebida” y subtítulo.
4. No aparece “beverage”.
5. Productos en cards con precios Solo / Con acompañante.
6. Buscar producto.
7. Tocar **Solo** o **Con acompañante** en card → preview y cantidad.
8. **Agregar bebida**.

## 6. Pendientes

- Tabs fijas Cervezas/Whisky/Tequila si el catálogo demo no tiene esas categorías nombradas.
- Configurar precio inline desde garzón (solo si permiso `product_prices.quick_create`).
- Animación al agregar ítem.
