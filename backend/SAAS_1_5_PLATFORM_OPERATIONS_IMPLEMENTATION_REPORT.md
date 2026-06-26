# SAAS-1.5 — Platform Operations / Ribersoft Control Center — Implementation Report (Backend)

**Fecha:** 2026-06-25  
**Estado:** ✅ Implementado (V1)  
**Alcance:** Monitoreo operativo interno Ribersoft — **NO billing, NO suscripciones**

---

## Resumen

Capa de observabilidad sobre datos operativos existentes (tenants, sales, orders, cash_sessions, shifts, print_devices, print_jobs, audit_logs, operational_events, bootstrap).

**Permiso:** `platform.operations.view` (superadmin bypass vía middleware existente)

---

## Endpoints

| Método | Ruta |
|--------|------|
| GET | `/api/v1/admin/platform/operations/dashboard` |
| GET | `/api/v1/admin/platform/operations/tenants` |
| GET | `/api/v1/admin/platform/operations/tenants/{tenantId}` |
| GET | `/api/v1/admin/platform/operations/print-agents` |
| GET/PUT | `/api/v1/admin/platform/operations/tenants/{tenantId}/technical-profile` |
| GET | `/api/v1/admin/platform/operations/tenants/{tenantId}/checklist` |
| PATCH | `/api/v1/admin/platform/operations/tenants/{tenantId}/checklist/{key}` |

---

## Servicios (Application layer)

| Clase | Rol |
|-------|-----|
| `PlatformOperationsMetricsReader` | Lectura agregada de métricas por tenant / plataforma |
| `PlatformOperationsTenantAnalyzer` | Health score, estado operativo, incidencias |
| `PlatformOperationsDashboardBuilder` | Dashboard, listado, detalle tenant |
| `PlatformOperationsChecklistService` | Catálogo + CRUD checklist instalación |
| `PlatformOperationsAccessGuard` | `platform.operations.view` o superadmin |

---

## Estados operativos

`ONLINE` · `WARNING` · `DEGRADED` · `OFFLINE` · `CRITICAL`

Umbrales configurables en `config/nightpos.php` → `platform_operations.*` y `printing.agent_online_seconds` (default 120s para Control Center).

---

## Migración MySQL

Índices UNIQUE con nombre explícito corto (límite 64 chars MySQL):

- `tenant_technical_profiles` → `tenant_tech_profile_uq`
- `tenant_operation_checklist_items` → `tenant_ops_chk_uq`

La migración es **idempotente** (`hasColumn` / `hasTable`) para reintentar si falló a mitad en local.

Si quedó estado parcial antes del fix, basta con:

```bash
php artisan migrate
```

(o eliminar tablas parciales `tenant_technical_profiles` / `tenant_operation_checklist_items` si existían sin el índice).

---

## Heartbeat agente (compatibilidad gradual)

Campos opcionales en POST heartbeat: `host_name`, `os_name`, `os_version`, `arch`, `ip_address`, `printer_model`. Agente legacy sin estos campos sigue funcionando.

---

## Tests

`tests/Feature/Api/V1/Saas15PlatformOperationsTest.php` — **16 tests** (14 del spec + checklist + perfil técnico OK).

```bash
php artisan test tests/Feature/Api/V1/Saas15PlatformOperationsTest.php
```

---

## Configuración

```env
NIGHTPOS_BACKEND_VERSION=1.0.0
NIGHTPOS_AGENT_ONLINE_SECONDS=120
NIGHTPOS_OPS_ACTIVITY_ONLINE_MINUTES=15
NIGHTPOS_OPS_ACTIVITY_WARNING_HOURS=2
NIGHTPOS_OPS_ACTIVITY_OFFLINE_HOURS=24
NIGHTPOS_OPS_CASH_WARNING_HOURS=14
NIGHTPOS_OPS_SHIFT_WARNING_HOURS=14
NIGHTPOS_OPS_NO_SALES_WARNING_DAYS=2
NIGHTPOS_OPS_PRINT_FAIL_WARNING=3
```

---

## Principio

No se tocó lógica operativa del boliche. Solo lectura + tablas auxiliares de soporte/instalación.

**Siguiente fase bloqueada hasta cerrar QA:** SAAS-2 Billing.
