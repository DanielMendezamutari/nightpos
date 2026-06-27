# Hosting — Login Context Timeout Fix (Backend P1)

**Fecha:** 2026-06-25  
**Basado en:** `backend/HOSTING_LOGIN_CONTEXT_TIMEOUT_AUDIT.md`  
**Alcance:** query mínima login-context — **no reemplaza P0 hosting**

---

## Contexto

La auditoría confirmó que `GET /api/v1/auth/login-context/tenants` ya era liviano (~117 ms local, **1 query**). La lentitud en producción proviene de **hosting/LiteSpeed/cola PHP**, no de SAAS-1.5 ni de lógica pesada en el use case.

Este P1 deja el endpoint **más correcto** filtrando en SQL y seleccionando columnas mínimas.

---

## Cambios implementados

### `TenantRepositoryInterface::listActiveForLogin()`

Nuevo método dedicado al login público.

### `EloquentTenantRepository::listActiveForLogin()`

```sql
SELECT id, name, slug, status, subscription_ends_at
FROM tenants
WHERE status = 'active'
  AND (subscription_ends_at IS NULL OR subscription_ends_at >= NOW())
ORDER BY name
```

- Sin relaciones Eloquent
- Sin plan usage
- Sin SAAS-1.5 / PlatformOperations

### `ListLoginContextTenantsUseCase`

Usa `listActiveForLogin()` en lugar de `listAll()` + filtro PHP.

Respuesta sin cambio de contrato: `{ id, name, slug }[]`.

---

## Tests

**Archivo:** `tests/Feature/Api/V1/LoginContextSelectionTest.php`

| # | Caso |
|---|------|
| 7 | Excluye tenant activo con suscripción vencida |
| 8 | Una sola query; filtro `status` en SQL; sin campos extra en JSON |

```bash
cd backend && php artisan test --filter=LoginContextSelectionTest
```

---

## P0 hosting — sigue obligatorio

| Acción | Motivo |
|--------|--------|
| Estabilizar LiteSpeed / entry processes | Resets intermitentes en producción |
| Activar rewrite `/api/v1` → `backend/public` | 404 HTML cuando falta `.htaccess` |
| Smoke `curl /api/v1/health` y `.../login-context/tenants` | Confirmar JSON antes de culpar frontend |
| `php artisan jwt:secret` + `optimize:clear` | Evitar errores auth paralelos |

Ver: `backend/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md`
