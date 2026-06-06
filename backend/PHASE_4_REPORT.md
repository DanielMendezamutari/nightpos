# PHASE_4_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 4 — Migraciones base SaaS + Auth / Tenant / Branch  
**Fecha:** 2026-06-02  
**Referencias:** `DOMAIN_DESIGN.md`, `ARCHITECTURE_REPORT.md`, `DATABASE_GUIDELINES.md`

---

## 1. Tablas creadas

| Tabla | Descripción |
| ----- | ----------- |
| `tenants` | Empresa SaaS (`name`, `slug`, `status`, `plan_name`, suscripción) |
| `branches` | Sucursales/casas (`tenant_id`, `name`, `code`, `address`, `status`) |
| `roles` | Roles por tenant o globales (`tenant_id` nullable) |
| `permissions` | Catálogo global de permisos |
| `role_permission` | Pivot rol ↔ permiso |
| `users` | Usuarios SaaS (reemplaza estructura Laravel default) |
| `staff_profiles` | Perfil operativo (`staff_role`, `waiter_commission_percent`, etc.) |
| `user_branch_access` | Acceso a múltiples sucursales |
| `sessions` | Recreada tras refactor de `users` |

**Migraciones:**

- `2026_06_03_100000_create_tenants_table.php`
- `2026_06_03_100001_create_branches_table.php`
- `2026_06_03_100002_create_roles_and_permissions_tables.php`
- `2026_06_03_100003_recreate_users_table_for_saas.php`
- `2026_06_03_100004_create_staff_profiles_and_user_branch_access_tables.php`

**Paquete JWT:** `php-open-source-saver/jwt-auth` v2.8 + `config/jwt.php`.

---

## 2. Decisiones de diseño

| Decisión | Motivo |
| -------- | ------ |
| `users.tenant_id` nullable | Permite **Super Admin SaaS** sin empresa |
| `username` único por tenant | Evita colisiones multi-tenant |
| `password` y `pin_hash` nullable | Cajero/garzón solo PIN; admin solo password |
| PIN en campo `pin_hash` separado | No mezclar con `password`; nunca texto plano |
| `staff_profiles` 1:1 con `user_id` | Comisión garzón y rol operativo sin contaminar `users` |
| `user_branch_access` | Usuarios con más de una casa (owner, encargado) |
| Roles + permissions simples | Sin sobreingeniería; extensible por tenant |
| `AuthRepositoryInterface` | JWT aislado en Infrastructure |
| Use cases en Application | Controllers solo delegan |
| Login PIN con scope tenant/branch | Alineado a operación en boliche por casa |
| Guard por defecto `api` + JWT | API-first para Vue |

---

## 3. Login por PIN (primera opción)

**Endpoint:** `POST /api/v1/auth/login-pin`

**Body ejemplo:**

```json
{
  "pin": "1234",
  "tenant_slug": "casa-demo",
  "branch_code": "CENTRO"
}
```

**Flujo:**

1. Validar formato PIN (4–6 dígitos).
2. Resolver tenant (`tenant_id` o `tenant_slug`) y validar estado/suscripción.
3. Resolver sucursal (`branch_id` o `branch_code`).
4. Buscar candidatos activos con `pin_hash` en el scope tenant/sucursal (`user_branch_access` incluido).
5. Verificar PIN con `Hash::check()` contra `pin_hash` (bcrypt).
6. Emitir JWT vía `JwtAuthRepository`.
7. Actualizar `last_login_at`.
8. Devolver token + datos de usuario (rol, permisos, sucursales accesibles, `staff_role`, comisión configurada).

**Seeder demo:** usuario `cajero.demo` — PIN `1234` en sucursal `CENTRO`.

---

## 4. Login por usuario/contraseña (segunda opción)

**Endpoint:** `POST /api/v1/auth/login-password`

**Body ejemplo:**

```json
{
  "username": "admin.demo",
  "password": "AdminDemo123!",
  "tenant_slug": "casa-demo"
}
```

**Flujo:**

1. Resolver tenant y validar suscripción.
2. Buscar usuario por `username` + scope tenant (o super admin sin tenant).
3. Verificar `password` con hash almacenado (`password` cast `hashed` en modelo).
4. Emitir JWT y registrar `last_login_at`.

**Seeder demo:** `admin.demo` / `AdminDemo123!`, `superadmin` / `SuperAdmin123!` (sin tenant).

---

## 5. Manejo de tenant y sucursal

| Concepto | Implementación |
| -------- | -------------- |
| Tenant | Tabla `tenants`; login acepta `tenant_id` o `tenant_slug` |
| Sucursal | Tabla `branches`; login PIN acepta `branch_id` o `branch_code` |
| Aislamiento | Usuarios filtrados por `tenant_id`; sucursal por `branch_id` o `user_branch_access` |
| Suscripción | `TenantAccessGuard` valida `status` y `subscription_ends_at` |
| JWT claims | `tenant_id`, `branch_id`, `username` en payload custom |
| Contexto futuro | `TenantContextInterface` + `RequestContextTenantResolver` registrados (middleware Fase 5) |

---

## 6. Endpoints implementados

| Método | Ruta | Auth |
| ------ | ---- | ---- |
| POST | `/api/v1/auth/login-pin` | No |
| POST | `/api/v1/auth/login-password` | No |
| GET | `/api/v1/auth/me` | JWT `auth:api` |
| POST | `/api/v1/auth/logout` | JWT `auth:api` |

**Respuesta JSON:**

```json
{
  "success": true,
  "message": "...",
  "data": {},
  "errors": {}
}
```

---

## 7. Estructura hexagonal añadida

| Capa | Archivos clave |
| ---- | -------------- |
| Domain | `Tenant`, `Branch`, `AuthenticatedUser`, repositorios, excepciones |
| Application | `LoginWithPinUseCase`, `LoginWithPasswordUseCase`, `GetAuthenticatedUserUseCase`, `LogoutUseCase`, DTOs |
| Infrastructure | Modelos Eloquent, repositorios, `JwtAuthRepository`, `ApiResponsePresenter` |
| Http | `AuthController`, `LoginPinRequest`, `LoginPasswordRequest` |

**Bindings:** `NightPosServiceProvider`.

---

## 8. Tests

```text
tests/Feature/Api/V1/AuthApiTest.php — 6 tests
Total suite: 13 passed
```

Cubre: login PIN OK, PIN inválido 401, login password, `/me`, logout, hash de PIN.

**Ejecutar:**

```bash
cd backend
php artisan migrate --seed
php artisan test
```

---

## 9. Qué queda pendiente

| Ítem | Fase |
| ---- | ---- |
| Middleware tenant/branch en cada request | 5 |
| CRUD tenants/branches/users | 5 |
| Productos, precios SOLO/CON_ACOMPANANTE | 6 |
| Caja, comandas, ventas | 7+ |
| Refresh token, blacklist JWT en producción | 5 |
| Políticas de autorización por permiso en rutas | 5 |
| Eliminar o redirigir `App\Models\User` legacy | Limpieza opcional |
| Planes/subscriptions tablas separadas | Futuro (ahora `plan_name` en tenant) |

---

## 10. Próxima fase recomendada

**Fase 5 — Gestión Tenant/Branch/User + middleware multi-tenant**

1. Middleware que resuelva `TenantContext` desde JWT.
2. Endpoints admin: listar sucursales del tenant, asignar `user_branch_access`.
3. Validar suscripción en rutas operativas.
4. Iniciar módulo **Product** (categorías + `product_prices` SOLO/CON_ACOMPANANTE).

---

## 11. Datos demo (seeder)

| Usuario | Método | Credencial | Tenant |
| ------- | ------ | ---------- | ------ |
| `cajero.demo` | PIN | `1234` | casa-demo / CENTRO |
| `garzon.demo` | PIN | `5678` | casa-demo / CENTRO |
| `admin.demo` | Password | `AdminDemo123!` | casa-demo |
| `superadmin` | Password | `SuperAdmin123!` | — |

---

*Fase 4 completada. Sistema heredado y frontend sin cambios.*
