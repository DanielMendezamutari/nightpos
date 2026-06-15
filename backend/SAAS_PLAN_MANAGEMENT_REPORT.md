# SAAS_PLAN_MANAGEMENT_REPORT.md (Backend)

**Fase:** SAAS-1 — Planes y límites  
**Fecha:** 2026-06-14  
**Estado:** Completado (sin enforcement comercial)

---

## 1. Paso 0 — Corrección crítica

### Problema
Dos caminos de alta de empresa:
- `POST /api/v1/admin/platform/setup` — provisionaba tenant completo.
- `POST /api/v1/admin/tenants` — solo creaba tenant vacío.

### Solución
Toda creación pasa por `TenantProvisioner`:
- `PlatformSetupUseCase` delega al provisioner.
- `CreateTenantAdminUseCase` delega al provisioner.
- Payload de `POST /admin/tenants` exige `branch` + `admin`.

Provisionamiento en transacción:
1. Tenant (+ `plan_id` / `plan_name`)
2. Roles y permisos (`TenantRoleProvisioner`)
3. Sucursal inicial
4. Usuario administrador (`tenant_owner`)

---

## 2. Modelo de datos

### Tabla `plans`
| Campo | Tipo |
|-------|------|
| id | PK |
| name | string |
| code | string unique |
| description | text nullable |
| monthly_price | decimal |
| yearly_price | decimal |
| is_active | boolean |
| display_order | smallint |
| timestamps | |

### Tabla `plan_limits`
| Campo | Tipo |
|-------|------|
| id | PK |
| plan_id | FK |
| limit_key | string |
| limit_value | int (-1 = ilimitado) |

Claves: `branches`, `users`, `cashiers`, `waiters`, `products`, `rooms`.

### Tabla `tenants`
- Nuevo: `plan_id` FK nullable → `plans`
- Mantenido: `plan_name` (sincronizado desde `plans.code`)

### Planes sembrados
| Código | Sucursales | Usuarios | Productos |
|--------|------------|----------|-----------|
| FREE | 1 | 5 | 100 |
| STARTER | 3 | 20 | 500 |
| BUSINESS | 10 | 100 | 2000 |
| ENTERPRISE | ∞ | ∞ | ∞ |

`casa-demo` asignado a BUSINESS en migración.

---

## 3. API (superadmin)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/v1/admin/platform/dashboard` | Cards SaaS + top planes |
| GET | `/api/v1/admin/platform/plans` | Listar planes |
| POST | `/api/v1/admin/platform/plans` | Crear plan |
| PUT | `/api/v1/admin/platform/plans/{id}` | Editar plan |
| DELETE | `/api/v1/admin/platform/plans/{id}` | Eliminar o desactivar |
| GET | `/api/v1/admin/platform/plans/{id}/limits` | Límites del plan |
| PUT | `/api/v1/admin/platform/plans/{id}/limits` | Actualizar límites |
| POST | `/api/v1/admin/platform/plans/{id}/duplicate` | Duplicar plan |

Asignación de plan: `PUT /api/v1/admin/tenants/{id}` con `plan_id`.

---

## 4. Uso vs límites (solo informativo)

`TenantPlanUsageCalculator` cuenta uso real por tenant y devuelve por recurso:
- `current`, `limit`, `status` → `OK` | `WARNING` (≥80%) | `LIMIT_REACHED`

Incluido en `GET /api/v1/admin/tenants/{id}` bajo `plan_usage`.

**No hay bloqueo operativo** en SAAS-1.

---

## 5. Archivos principales

| Área | Archivos |
|------|----------|
| Provisionamiento | `TenantProvisioner`, `TenantProvisionInput` |
| Planes | `PlanModel`, `PlanLimitModel`, use cases en `Application/Plan/` |
| HTTP | `PlatformPlanController`, `PlatformDashboardController` |
| Migración | `2026_06_15_100001_create_plans_and_plan_limits_tables.php` |

---

## 6. Tests

| Archivo | Casos |
|---------|-------|
| `TenantProvisioningTest.php` | Wizard + empresas → tenant operable |
| `PlanManagementTest.php` | 8 casos (CRUD, asignación, límites, inactivo, aislamiento) |

**Suite:** `415 passed` (`php artisan test`).

---

## 7. Qué sigue — SAAS-2

Suscripciones como entidad (`subscriptions`, estados trial/overdue/cancelled), historial y renovación. Sin iniciar en esta fase.
