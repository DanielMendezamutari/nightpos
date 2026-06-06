# PHASE_5_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 5 — Middleware multi-tenant + gestión admin base  
**Fecha:** 2026-06-02  
**Referencias:** `DOMAIN_DESIGN.md`, `PHASE_4_REPORT.md`, `ARCHITECTURE_REPORT.md`

---

## 1. Middleware creados

| Alias | Clase | Función |
| ----- | ----- | ------- |
| `nightpos.tenant` | `ResolveTenantMiddleware` | Carga usuario en contexto, resuelve tenant, valida estado/suscripción |
| `nightpos.branch` | `ResolveBranchMiddleware` | Resuelve sucursal (`optional` o `required`) |
| `nightpos.branch.access` | `EnsureUserHasBranchAccessMiddleware` | Valida `user_branch_access` + sucursal por defecto |
| `nightpos.permission` | `EnsureRolePermissionMiddleware` | Exige permiso por slug (ej. `admin.tenants.list`) |

Registrados en `bootstrap/app.php`.

---

## 2. Cómo se resuelve el tenant

1. Usuario autenticado vía JWT (`auth:api`).
2. `OperationalContextBootstrapper` carga staff, permisos y tenant del usuario (si tiene `tenant_id`).
3. `ResolveTenantMiddleware`:
   - **Usuario normal:** tenant fijado a `users.tenant_id` (ignora headers ajenos).
   - **Superadmin:** tenant desde `tenant_id`, `tenant_slug`, `X-Tenant-Id` o `X-Tenant-Slug` (opcional para rutas globales).
4. Se valida `status = active` y suscripción vigente.
5. Resultado en `RequestOperationalContext` → expuesto por `TenantContext`.

---

## 3. Cómo se resuelve la sucursal

1. Requiere tenant en contexto.
2. Orden de resolución:
   - Query: `branch_id`, `branch_code`
   - Headers: `X-Branch-Id`, `X-Branch-Code`
   - JWT / usuario: `users.branch_id`
3. Se valida que la sucursal pertenezca al tenant y esté activa.
4. Rutas con `nightpos.branch:required` exigen sucursal (ej. `branches/current`).
5. Rutas sin sucursal obligatoria: `branches/available`, `tenant/current`.

---

## 4. Cómo se valida el acceso

| Validación | Implementación |
| ---------- | -------------- |
| Usuario activo | `users.status = active` |
| Tenant ajeno | `TenantAccessDeniedException` si intenta otro `tenant_id` |
| Headers tenant ajenos (usuario normal) | Ignorados; permanece su tenant |
| Sucursal | `BranchAccessGuard` + tabla `user_branch_access` |
| Superadmin | Acceso a cualquier sucursal del tenant en contexto |
| Permisos admin | Slugs en `permissions` vía rol; superadmin bypass total |

Excepciones de dominio → HTTP 403 en API.

---

## 5. Cómo funcionan los permisos

**Catálogo (seeder):**

| Slug | Uso |
| ---- | --- |
| `admin.tenants.list` | GET `/admin/tenants` |
| `admin.tenants.create` | POST `/admin/tenants` |
| `admin.branches.list` | GET `/admin/branches` |
| `admin.branches.create` | POST `/admin/branches` |
| `admin.users.list` | GET `/admin/users` |
| `admin.users.create` | POST `/admin/users` |

**Asignación:**

- `super_admin` → todos los permisos.
- `tenant_owner` → admin branches/users (no tenants globales).
- `cashier` / `waiter` → operativos (`cash.access`, `orders.access`, etc.).

`EnsureRolePermissionMiddleware` consulta `AuthenticatedStaffContext::hasPermission()`.

---

## 6. Contextos inyectables

| Interfaz | Implementación | Contenido |
| -------- | -------------- | --------- |
| `TenantContextInterface` | `TenantContext` | Tenant actual, IDs |
| `BranchContextInterface` | `BranchContext` | Sucursal actual |
| `AuthenticatedStaffContextInterface` | `AuthenticatedStaffContext` | `user_id`, `staff_role`, permisos, `isSuperAdmin()` |

Estado central en `RequestOperationalContext` (singleton por request).

Los casos de uso futuros deben inyectar estas interfaces, no leer `Request` directamente.

---

## 7. Endpoints creados

### Operativos (auth + `nightpos.tenant`)

| Método | Ruta |
| ------ | ---- |
| GET | `/api/v1/tenant/current` |
| GET | `/api/v1/branches/available` |
| GET | `/api/v1/branches/current` (+ branch required + access) |

### Admin (permisos por ruta)

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| GET | `/api/v1/admin/tenants` | `admin.tenants.list` |
| POST | `/api/v1/admin/tenants` | `admin.tenants.create` |
| GET | `/api/v1/admin/branches` | `admin.branches.list` |
| POST | `/api/v1/admin/branches` | `admin.branches.create` |
| GET | `/api/v1/admin/users` | `admin.users.list` |
| POST | `/api/v1/admin/users` | `admin.users.create` |

**Headers recomendados en operación:**

```http
Authorization: Bearer {token}
X-Branch-Code: CENTRO
```

Superadmin adicional:

```http
X-Tenant-Slug: casa-demo
```

---

## 8. Tests ejecutados

```bash
php artisan test
```

**Resultado:** 20 passed (56 assertions)

Incluye:

- `tests/Feature/Api/V1/AuthApiTest.php` (Fase 4 intacta)
- `tests/Feature/Api/V1/MultiTenantSecurityTest.php` (nuevo)

Cobertura Fase 5:

- Aislamiento tenant (header ajeno ignorado)
- Sucursal no permitida → 403
- Superadmin lista tenants
- Cajero no lista tenants globales
- `branches/available` solo sucursales permitidas
- Login + contexto tenant/branch
- Owner lista users, no tenants globales

---

## 9. Qué queda pendiente

| Ítem | Fase |
| ---- | ---- |
| Middleware suscripción bloqueante en modo solo lectura | 5.1 |
| Policies granulares por recurso | 6 |
| CRUD completo update/delete admin | 6 |
| Asignación `user_branch_access` vía API | 6 |
| Refresh token JWT | 5.1 |
| Rate limiting login | 5.1 |
| Productos / precios SOLO-CON_ACOMPANANTE | 6 |
| Caja, comandas, ventas | 7+ |

---

## 10. Próxima fase recomendada

**Fase 6 — Productos y precios boliche**

1. Migraciones `categories`, `products`, `product_prices`, `product_price_rules`.
2. `ResolveProductPriceUseCase` usando `TenantContext` + `BranchContext`.
3. Endpoints CRUD productos con permisos (`admin.products.*` o módulo catálogo).
4. Tests precio SOLO / CON_ACOMPANANTE.

---

*Fase 5 completada. Heredado y frontend sin cambios.*
