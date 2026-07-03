# Health Center H1 — Implementation Report

**Fecha:** 2026-06-30  
**Alcance:** Diagnóstico read-only, score 0–100, UI semáforo superadmin/owner

## API

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/admin/health-center/platform/summary` | `health.platform.view` o `platform.operations.view` |
| GET | `/api/v1/health-center/summary` | `health.tenant.view` |

## Checks H1

**Infraestructura (global):** APP_DEBUG, APP_ENV, DB, JWT, storage link  

**Por sucursal:** secuencia documental, caja abierta antigua, turno abierto antigo, liquidaciones PENDING en turno cerrado, agente offline, print_jobs PENDING antiguos

## Frontend

- Plataforma: `nightpos/platform/health-center`
- Owner: `nightpos/settings/health`

## Despliegue

```bash
php artisan migrate --force
```

Asigna `health.tenant.view` a `tenant_owner` y `health.platform.view` a superadmin (+ roles con `platform.operations.view`).

## Tests

`php artisan test tests/Feature/Api/V1/HealthCenterH1Test.php` — 7 tests OK
