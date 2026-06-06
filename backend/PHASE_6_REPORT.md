# PHASE_6_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 6 — Productos y precios SOLO / CON_ACOMPANANTE  
**Fecha:** 2026-06-02  
**Referencias:** `DOMAIN_DESIGN.md`, `PHASE_5_REPORT.md`

---

## 1. Tablas creadas

Migración `2026_06_03_100005_create_product_catalog_tables.php`:

| Tabla | Campos clave |
| ----- | ------------ |
| `product_categories` | `tenant_id`, `branch_id` (nullable), `name`, `type`, `status` |
| `products` | `tenant_id`, `branch_id` (nullable), `category_id`, `name`, `sku`, `barcode`, `product_type`, `unit`, `track_inventory`, `status` |
| `product_prices` | `tenant_id`, `branch_id` (nullable), `product_id`, `sale_mode`, `price`, `girl_amount`, `house_amount`, `currency`, `status`, `starts_at`, `ends_at` |

---

## 2. Endpoints creados

Middleware: `auth:api`, `nightpos.tenant`, `nightpos.branch:optional`

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| GET | `/api/v1/products` | `products.list` |
| POST | `/api/v1/products` | `products.create` |
| GET | `/api/v1/products/{id}` | `products.list` |
| PUT | `/api/v1/products/{id}` | `products.update` |
| GET | `/api/v1/products/{id}/prices` | `products.list` |
| POST | `/api/v1/products/{id}/prices` | `products.create` |
| GET | `/api/v1/product-categories` | `products.list` |
| POST | `/api/v1/product-categories` | `products.create` |

**Roles (seeder):**

- `tenant_owner` → list/create/update productos y categorías
- `cashier` / `waiter` → `products.list` (+ categorías)
- `super_admin` → todos los permisos

---

## 3. Reglas implementadas

| Regla | Implementación |
| ----- | -------------- |
| Producto con `tenant_id` obligatorio | `CreateProductUseCase` + contexto tenant |
| `branch_id` null = ámbito tenant; con valor = sucursal | Repositorios y DTOs |
| Nombre obligatorio | `ProductDomainException::emptyName()` |
| Precios no negativos | `ProductPriceValidator` |
| `SOLO_CLIENTE` sin split chica/casa | Validador de dominio |
| `CON_ACOMPANANTE`: `girl_amount + house_amount = price` | Cuando ambos están definidos |
| Sin `sale_mode` activo duplicado (mismo producto/tenant/branch) | `ProductPriceRepositoryInterface::hasActiveSaleMode()` |
| Aislamiento por tenant | Consultas filtradas por `tenant_id` del contexto |
| Garzón solo ve productos `active` | `GetProductsUseCase` si `staff_role = WAITER` |
| Sin comisiones ni ventas | Solo persistencia de reglas de precio |

---

## 4. Precio SOLO_CLIENTE

- `sale_mode = SOLO_CLIENTE`
- Un solo monto en `price` (ej. Paceña 40 Bs)
- `girl_amount` y `house_amount` deben ser null
- Listo para que comandas futuras cobren al cliente sin acompañante

---

## 5. Precio CON_ACOMPANANTE

- `sale_mode = CON_ACOMPANANTE`
- `price` = total cobrado (ej. 80 Bs)
- `girl_amount` + `house_amount` = `price` cuando ambos se envían (ej. 40 + 40)
- La parte `girl_amount` queda almacenada para **futuras comisiones/manillas**; no se calcula ni liquida en esta fase

---

## 6. Preparación para comisiones de chicas

Los campos `girl_amount` y `house_amount` en `product_prices` modelan el reparto boliche sin ejecutar lógica de liquidación:

- Fase futura (comandas/ventas): leer precio por `sale_mode`, asignar `girl_id`, generar `GirlBraceletEntry` / comisiones al `SalePaid`
- Fase futura (liquidaciones): agregar reglas sobre montos ya persistidos en la venta

---

## 7. Arquitectura

```
Domain/Product/
  Entities, ValueObjects/SaleMode, Services/ProductPriceValidator
  Repositories (interfaces), Exceptions

Application/Product/
  DTOs, UseCases, Support/ProductMapper, BranchScopeResolver

Infrastructure/
  Eloquent models/repositories
  Http/Controllers Api/V1 ProductController, ProductCategoryController
```

`RequestOperationalContext::reset()` al inicio de cada request en `ResolveTenantMiddleware` evita fugas de contexto entre peticiones en tests.

---

## 8. Tests ejecutados

```bash
php artisan test
```

**Resultado:** 30 passed

Archivo: `tests/Feature/Api/V1/ProductCatalogTest.php`

- Admin crea producto
- Cajero no crea producto (403)
- Garzón lista solo activos
- Tenant requerido para crear (superadmin sin tenant → 422)
- Precio SOLO_CLIENTE
- Validación split CON_ACOMPANANTE
- Precio negativo rechazado
- `sale_mode` duplicado rechazado
- Tenant ajeno no visible (404)
- Precio por sucursal

Helpers de test: `auth('api')->forgetUser()` en login y headers operativos.

---

## 9. Qué queda pendiente

| Ítem | Fase |
| ---- | ---- |
| `ProductPriceResolver` en comandas | 7 |
| Update/delete precios y categorías | 6.1 |
| Combos, recetas, inventario | 8+ |
| Promos / VIP (`ProductSaleMode`) | 8+ |
| Vigencia `starts_at` / `ends_at` en UI | 6.1 |

---

## 10. Próxima fase recomendada

**Fase 7 — Comandas (órdenes) base**

1. Migraciones `orders`, `order_items`
2. Ítems con `product_id`, `quantity`, `sale_mode` (precio resuelto en backend)
3. Regla CON_ACOMPANANTE exige `girl_id` antes de cobrar
4. Integración con `TenantContext`, `BranchContext` y catálogo de Fase 6

---

*Fase 6 completada. Legacy y frontend sin cambios.*
