# Rediseño UX — Creación y edición de productos

## Resumen

Se unificó el alta y la edición de productos en **una sola pantalla** (`ProductForm.vue`). Ya no hace falta crear un producto, guardar, volver a editar y recién ahí configurar combo o precios.

---

## Antes

| Paso | Acción |
|------|--------|
| 1 | Crear producto (`create.vue`) — solo nombre, categoría y precios básicos |
| 2 | Guardar |
| 3 | Entrar a editar (`edit.vue` con `ProductFormFields.vue`) |
| 4 | Configurar liquidación / combo (sin precios en el mismo formulario) |
| 5 | Ir a otra pantalla para precios (`[id]/prices.vue`) |
| 6 | Guardar de nuevo |

**Problemas:**
- Dos formularios distintos (create vs edit)
- Combos invisibles en el alta
- Precios y datos de producto en flujos separados
- Tiempo estimado: **2–4 minutos** por producto con combo

---

## Después

| Paso | Acción |
|------|--------|
| 1 | Abrir **Nuevo producto** o **Editar producto** |
| 2 | Completar secciones visibles según el tipo |
| 3 | Guardar una vez |

**Flujo inteligente:**
- **Producto normal** → Información + Precios
- **Con acompañante** → aparecen montos chica/casa al ingresar precio acompañante
- **Combo con manillas** → radio «Combo con manillas» muestra cantidad, unidad y allocation

**Tiempo estimado:** **15–30 segundos** para productos simples; **30–45 segundos** para combos.

---

## Componentes reutilizados

| Componente | Rol |
|------------|-----|
| `ProductForm.vue` | Formulario unificado (create, edit, duplicate) |
| `useProductForm.js` | Estado por defecto, mapeo producto↔form, payloads API |
| `ProductPricingFields.vue` | Bloque de precios solo / acompañante (sin duplicar) |
| `QuickCategoryCreateDialog.vue` | Alta rápida de categoría desde el selector |

### Páginas

| Página | Uso |
|--------|-----|
| `products/create.vue` | Shell + `ProductForm` + `quickCreateProduct` |
| `products/[id]/edit.vue` | Shell + `ProductForm` + `updateProduct` + `replaceActiveProductPrice` |
| `products/create?duplicate={id}` | Duplicar: precarga datos y nombre «(copia)» |

### Eliminación de duplicación

- `create.vue` ya no define campos inline ni tabs «Básico / Avanzado»
- `edit.vue` dejó de usar `ProductFormFields.vue` para el flujo principal
- `ProductFormFields.vue` permanece en el repo por si otros módulos lo referencian; el catálogo usa `ProductForm.vue`

---

## API (sin cambiar reglas de negocio)

- **Crear:** `POST /products/quick` — producto + precios + settlement/combo en una transacción
- **Editar:** `PUT /products/{id}` + `PUT /products/{id}/prices/active` por modo de venta

Mapeo UX → backend:
- `is_combo = true` → `settlement_behavior: GIRL_BRACELET_ALLOCATION` + `bracelet_units_per_line`
- `is_combo = false` → `settlement_behavior: GIRL_LINE`

---

## Vista previa

Banner superior en el formulario:
- «Paceña — Producto normal»
- «Combo 6 Cervezas — Combo 6 manillas»
- «Trago — Con acompañante»

---

## Duplicar producto

Desde la ficha del producto: **Duplicar producto** → abre create con datos precargados (precios, combo, categoría). El usuario cambia el nombre y guarda.

Ideal para variantes: Combo 6 → Combo 8 → Combo 10.

---

## Beneficios UX

1. **Un solo guardado** para producto, precios y combo
2. **Campos progresivos** — solo lo necesario según el tipo
3. **Mismo formulario** en crear y editar — menos curva de aprendizaje
4. **Vista previa** inmediata del tipo de producto
5. **Duplicar** acelera familias de combos
6. **Sin cambios** en liquidaciones, allocations ni reglas de combo en backend

---

## Casos de uso cubiertos

| Caso | Campos visibles | Guardado |
|------|-----------------|----------|
| Paceña (normal) | Nombre, categoría, precio solo, precio acompañante opcional | 1× quick |
| Combo 6 Cervezas | + radio combo, 6 manillas | 1× quick |
| Promo Whisky combo | Nombre, categoría, combo 10 manillas, precios | 1× quick |

---

## Archivos tocados

**Frontend**
- `src/components/nightpos/products/ProductForm.vue` (nuevo)
- `src/composables/useProductForm.js` (nuevo)
- `src/pages/nightpos/products/create.vue`
- `src/pages/nightpos/products/[id]/edit.vue`
- `src/pages/nightpos/products/[id]/index.vue`

**Backend**
- `QuickCreateProductInput`, `QuickCreateProductRequest`, `ProductController::quickStore`
- `QuickCreateProductUseCase` — settlement en alta rápida
- Test: `PhaseC1Test` — combo en un paso

---

## Notas

- Permiso de alta con precios: `products.quick_create`
- La pantalla `[id]/prices.vue` sigue disponible para historial avanzado de precios
- SKU opcional en create/edit unificado (quick create acepta `sku`)
