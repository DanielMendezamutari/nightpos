# PHASE_12_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 12 — Edición real de empresas, sucursales y categorías  
**Fecha:** 2026-06-02  
**Referencias:** `DOMAIN_DESIGN.md`, `PHASE_5_REPORT.md`, `PHASE_6_REPORT.md`

---

## 1. Endpoints creados

Middleware base: `auth:api`, `nightpos.tenant`, `nightpos.branch:optional`

| Método | Ruta | Permiso | Use case |
| ------ | ---- | ------- | -------- |
| GET | `/api/v1/admin/tenants/{id}` | `admin.tenants.list` | `GetTenantAdminUseCase` |
| PUT | `/api/v1/admin/tenants/{id}` | `admin.tenants.list` | `UpdateTenantAdminUseCase` |
| GET | `/api/v1/admin/branches/{id}` | `admin.branches.list` | `GetBranchAdminUseCase` |
| PUT | `/api/v1/admin/branches/{id}` | `admin.branches.list` | `UpdateBranchAdminUseCase` |
| GET | `/api/v1/product-categories/{id}` | `products.list` | `GetProductCategoryUseCase` |
| PUT | `/api/v1/product-categories/{id}` | `products.update` | `UpdateProductCategoryUseCase` |

**Form requests:** `UpdateTenantRequest`, `UpdateBranchRequest`, `UpdateProductCategoryRequest`.

**Repositorios ampliados:** `update()` + `slugExists()` (tenants), `update()` + `codeExistsForTenant()` (branches), `update()` (categorías).

---

## 2. Reglas implementadas

### Empresas (tenants)

| Regla | Implementación |
| ----- | -------------- |
| Solo superadmin edita | `GetTenantAdminUseCase` / `UpdateTenantAdminUseCase` → `isSuperAdmin()` o `PermissionDeniedException` |
| Slug único | Validación Laravel `unique:tenants,slug,{id}` + `TenantRepositoryInterface::slugExists()` |
| Nombre obligatorio | `TenantDomainException::emptyName()` |
| Rango de suscripción válido | Request `after_or_equal` + `TenantDomainException::invalidSubscriptionRange()` |
| Campos editables | `name`, `slug`, `status`, `plan_name`, `subscription_starts_at`, `subscription_ends_at` |

### Sucursales (branches)

| Regla | Implementación |
| ----- | -------------- |
| Contexto tenant obligatorio | `TenantContextInterface` en get/update |
| No editar sucursal de otro tenant | Comparación `branch.tenantId` vs tenant del contexto → `TenantAccessDeniedException` (403) |
| Código único por tenant | `BranchRepositoryInterface::codeExistsForTenant()` → `BranchDomainException::duplicateCode()` |
| Nombre obligatorio | `BranchDomainException::emptyName()` |
| Código normalizado a mayúsculas | `UpdateBranchAdminUseCase` |
| Campos editables | `name`, `code`, `address`, `status` |

### Categorías

| Regla | Implementación |
| ----- | -------------- |
| Admin con `products.update` | Ruta PUT bajo middleware `products.update` |
| Cajero / garzón sin update | Sin permiso → 403 |
| Aislamiento tenant | `findById($id, $tenantId)` y `update` con `where tenant_id` |
| Nombre obligatorio | `ProductDomainException::emptyCategoryName()` |
| Campos editables | `name`, `type`, `status` |

**Excepciones HTTP:** `TenantNotFoundException`, `BranchNotFoundException`, `ProductCategoryNotFoundException` → 404; dominio tenant/branch → 422.

---

## 3. Capa de aplicación añadida

- DTOs: `UpdateTenantInput`, `UpdateBranchInput`, `UpdateProductCategoryInput`
- Mappers: `TenantAdminMapper`, `BranchAdminMapper` (respuesta admin)
- Use cases: Get/Update para los tres agregados
- Controllers: `AdminTenantController@show|update`, `AdminBranchController@show|update`, `ProductCategoryController@show|update`

---

## 4. Validaciones

| Entidad | Validación HTTP | Validación dominio |
| ------- | ----------------- | ------------------ |
| Tenant | `name`, `slug`, `status` required; fechas opcionales | slug duplicado, nombre vacío, rango fechas |
| Branch | `name`, `code`, `status` required | código duplicado, nombre vacío |
| Categoría | `name`, `type`, `status` required | nombre vacío |

---

## 5. Tests

Archivo: `tests/Feature/Api/V1/AdminPhase12Test.php` (8 casos)

| Test | Resultado esperado |
| ---- | ------------------ |
| Superadmin GET/PUT tenant | 200, datos actualizados |
| Slug duplicado en PUT tenant | 422 |
| `admin.demo` PUT tenant global | 403 |
| Admin PUT sucursal propia (`CENTRO`) | 200 |
| Admin PUT sucursal de otro tenant | 403 |
| Admin GET/PUT categoría propia | 200 |
| Cajero PUT categoría | 403 |
| Admin PUT categoría de otro tenant | 404 |

**Suite completa:** 75 tests, 286 assertions — OK.

```bash
cd backend && php artisan test
```

---

## 6. Qué queda pendiente

- Permisos granulares opcionales (`admin.tenants.update`, `admin.branches.update`) — hoy el control fino está en use cases + permisos de listado.
- DELETE / desactivación masiva de tenants, branches o categorías.
- Auditoría de cambios (quién editó qué y cuándo).
- Validación de `type` de categoría contra catálogo cerrado (enum de dominio).
- Edición de productos ya existía en fase 6; no forma parte de esta fase.

---

## 7. Próxima fase recomendada

**Fase 13 — Plataforma SaaS ampliada:** módulos placeholder de R4 (planes/suscripciones reales, roles globales, facturación) o **Fase operativa:** comandas/caja con reglas de negocio completas según `DOMAIN_DESIGN.md`.

Detener implementación hasta nuevas instrucciones del product owner.
