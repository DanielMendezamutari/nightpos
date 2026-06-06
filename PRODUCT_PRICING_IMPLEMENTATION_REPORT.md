# PRODUCT_PRICING_IMPLEMENTATION_REPORT.md

**Proyecto:** NightPOS  
**Fecha:** 2026-06-02  
**Referencia:** `PRODUCT_PRICING_AUDIT.md` (F1–F8 frontend, B1–B5 backend)

---

## 1. Resumen

Se implementó el flujo unificado de **producto + precios** en catálogo: alta rápida con `POST /products/quick`, listado con precios embebidos (`?include=active_prices`), reemplazo de precio vigente sin duplicar modalidad, etiquetas humanas en UI y reducción de pantallas muertas.

**Tests catálogo:** 14/14 en `ProductCatalogTest`.  
**Suite completa:** 197 passed; 4 fallos preexistentes en `OfficialShiftsPhase13Test` (no relacionados con catálogo).

---

## 2. Backend

| Ítem auditoría | Estado | Detalle |
|----------------|--------|---------|
| B1 `include=active_prices` en listado | Hecho | `GetProductsListInput` + `ProductMapper::productWithActivePrices` |
| B3 Reemplazar precio activo | Hecho | `PUT /api/v1/products/{id}/prices/active`, `ReplaceActiveProductPriceUseCase` |
| B4 Agrupación precios activos | Hecho | `listActiveGroupedByProduct` en repositorio |
| B5 Detalle con precios | Hecho | `GET /products/{id}` siempre incluye `active_prices` |
| B2 `POST /products` con array `prices` | No | Sigue solo `quick` y flujo clásico |
| B6 Vigencia `starts_at`/`ends_at` en UI | No | API soporta; formularios no exponen fechas |

**Corrección técnica:** `GetProductsUseCase` era singleton con `Request` inyectado; el parámetro `include` quedaba “congelado”. Se movió a DTO desde el controlador.

**Archivos principales:**
- `app/Application/Product/DTOs/GetProductsListInput.php`
- `app/Application/Product/UseCases/GetProductsUseCase.php`
- `app/Application/Product/UseCases/ReplaceActiveProductPriceUseCase.php`
- `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentProductPriceRepository.php`
- `app/Http/Controllers/Api/V1/ProductController.php`
- `routes/api.php` — ruta `prices/active`
- `tests/Feature/Api/V1/ProductCatalogTest.php` — 3 tests nuevos

---

## 3. Frontend

| Ítem auditoría | Estado | Detalle |
|----------------|--------|---------|
| F1 Alta unificada | Hecho | `products/create.vue` → `quickCreateProduct` |
| F2 Producto rápido en listado | Hecho | `QuickProductCreateDialog` + KPI “Sin precio” |
| F3 Sin N+1 precios | Hecho | `fetchProducts({ include: 'active_prices' })` |
| F4 Etiquetas humanas | Hecho | `useProductSaleModeLabels.js` |
| F5 Editar precio vigente | Hecho | `replaceActiveProductPrice` en `prices.vue` |
| F6 Ficha con precios | Hecho | `products/[id]/index.vue` |
| F7 Vista precios catálogo | Hecho | `catalog/prices/index.vue` |
| F8 Quitar “Config. precios” | Hecho | Menú R4; `prices-config` redirige a productos |

**Componentes:**
- `components/nightpos/catalog/ProductPricingFields.vue`
- `api/products.js` — `replaceActiveProductPrice`, `fetchProducts` con params

**Permiso:** `products.quick_create` en seeder (owner, admin, cajero demo).

---

## 4. Cómo probar

1. `php artisan migrate:fresh --seed` en `backend`
2. Login: `admin.demo` / `AdminDemo123!`, tenant `casa-demo`, sucursal `CENTRO`
3. **Catálogo → Productos:** botón “Producto rápido” o “Crear producto” (tabs Básico / Avanzado)
4. Ver columnas precio cliente / con acompañante sin recargar precios por fila
5. Ficha producto → precios vigentes; **Precios** → “Actualizar precio vigente” vs crear modalidad nueva

---

## 5. Pendiente (fuera de este alcance)

- Roadmap `NIGHTPOS_MASTER_AUDIT.md`: garzón móvil restante, caja, liquidaciones, reportes C5
- `POST /products` con precios en un solo payload (B2)
- UI de vigencia por fechas (B6)
- Arreglar `OfficialShiftsPhase13Test` (4 tests)
