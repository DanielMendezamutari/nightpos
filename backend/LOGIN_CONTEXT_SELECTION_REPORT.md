# Backend — Selección de contexto en login PIN

## Problema

El login PIN obligaba a escribir `tenant_slug` y `branch_code` manualmente. El contexto guardado podía quedar inválido y bloquear el ingreso.

## Endpoints públicos (sin autenticación)

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/v1/auth/login-context/tenants` | Empresas activas con suscripción válida |
| GET | `/api/v1/auth/login-context/branches?tenant_slug=` | Sucursales activas del tenant |

### Respuesta tenants

```json
{
  "tenants": [
    { "id": 1, "name": "Casa Demo NightPOS", "slug": "casa-demo" }
  ]
}
```

### Respuesta branches

```json
{
  "branches": [
    { "id": 1, "name": "Sucursal Centro", "code": "CENTRO" }
  ]
}
```

## Seguridad

- Sin autenticación requerida (pre-login).
- Solo `id`, `name`, `slug` / `code` — sin datos sensibles.
- Tenants `inactive` o con suscripción vencida **excluidos**.
- Branches `inactive` **excluidas**.
- Tenant inexistente → `404` (`TenantNotFoundException`).

## Archivos

| Archivo |
|---------|
| `ListLoginContextTenantsUseCase.php` |
| `ListLoginContextBranchesUseCase.php` |
| `AuthController.php` — `loginContextTenants`, `loginContextBranches` |
| `LoginContextSelectionTest.php` |

## Tests

```bash
php artisan test --filter=LoginContextSelectionTest
```

6 escenarios: tenants activos, exclusión inactivos, branches por tenant, aislamiento, 404, branches inactivas excluidas.

Suite completa: **404 tests verdes**.
