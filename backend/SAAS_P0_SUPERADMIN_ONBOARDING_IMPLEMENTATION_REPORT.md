# SAAS P0 — SUPERADMIN + ONBOARDING — REPORTE BACKEND

**Fecha:** 2026-06-25  
**Estado:** Implementado (P0)

---

## Resumen

NightPOS queda preparado para producción SaaS sin depender del seeder demo para superadmin ni para permisos/bootstrap del wizard.

---

## Parte 1 — Comando superadmin

```bash
php artisan nightpos:create-superadmin
```

- Opciones: `--name`, `--username`, `--password`, `--pin`, `--force-create`
- Crea usuario `tenant_id = null`, rol `super_admin`, activo
- Validaciones: contraseña min 8, PIN 4–6 dígitos, username único
- Idempotente: si ya hay superadmin, pregunta antes de crear otro; nunca borra existentes
- Audit: `SUPERADMIN_CREATED` vía `AuditLogRecorder::recordPlatform()`

**Archivo:** `app/Console/Commands/CreateSuperadminCommand.php`

---

## Parte 2 — Permisos unificados

Fuente única: `TenantDefaultRolePermissions.php` — alineada con demo operativo.

Incluye para `tenant_owner`: `settings.printers`, `settings.printers.manage`, `admin.cash_sessions.*`, `roles.*`, `permissions.access`, `shift_console.access`, `products.quick_create`, etc.

Seeder demo refactorizado para usar la misma clase (`SeedsNightPosFoundation.php`).

Migración idempotente repara tenants existentes: `2026_06_25_140000_saas_p0_audit_logs_and_tenant_permissions.php`

---

## Parte 3 — Rol cashier_senior

Provisionado en `TenantRoleProvisioner` con permisos de fiscalización/caja limitada (igual demo).

---

## Parte 4 — Bootstrap operativo automático

`BranchOperationalBootstrapService` extrae lógica de bootstrap sucursal.

`TenantProvisioner` lo ejecuta al crear empresa/sucursal:

- Métodos de pago, motivos caja, áreas, categorías, productos base, show types, room types, caja default
- **No** crea `print_devices`

---

## Parte 5 — Self-service cuenta

| Método | Ruta | Audit |
|--------|------|-------|
| PATCH | `/api/v1/auth/me/password` | `USER_PASSWORD_CHANGED` |
| PATCH | `/api/v1/auth/me/pin` | `USER_PIN_CHANGED` |

- Confirma contraseña actual
- Contraseña nueva min 8; PIN 4–6 dígitos
- No devuelve PIN
- Cambio contraseña invalida JWT (si blacklist habilitada)
- Funciona superadmin y usuarios tenant

**Archivos:** `ChangeOwnPasswordUseCase`, `ChangeOwnPinUseCase`, `AuthController`, requests, `UserRepository::updatePasswordById/updatePinById`

---

## Parte 6 — Auditoría plataforma

- `audit_logs.tenant_id` nullable (MySQL + SQLite tests)
- `AuditLogRecorder::recordPlatform()` y `recordForUser()` para eventos sin tenant activo

---

## Parte 8 — Tests

`tests/Feature/Api/V1/SaasP0SuperadminOnboardingTest.php` — **14 tests, todos verdes**.

---

## Producción — checklist

1. `php artisan migrate`
2. `php artisan nightpos:create-superadmin` (sin `db:seed`)
3. Crear tenant vía Setup plataforma
4. Verificar checklist post-wizard + menú Impresoras visible

---

## Archivos principales

| Archivo | Cambio |
|---------|--------|
| `CreateSuperadminCommand.php` | Nuevo |
| `TenantDefaultRolePermissions.php` | Reescrito |
| `TenantRoleProvisioner.php` | +cashier_senior |
| `TenantProvisioner.php` | +bootstrap |
| `BranchOperationalBootstrapService.php` | Nuevo |
| `AuditLogRecorder.php` | Platform audit |
| `JwtAuthRepository.php` | Invalidate solo si blacklist ON |
| `routes/api.php` | PATCH me/password, me/pin |
