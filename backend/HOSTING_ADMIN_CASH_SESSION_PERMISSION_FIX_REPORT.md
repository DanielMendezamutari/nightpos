# HOSTING_ADMIN_CASH_SESSION_PERMISSION_FIX_REPORT.md (Backend)

**Bug:** Admin en hosting sin permiso `admin.cash_sessions.summary` en fiscalización de cajas  
**Fecha:** 2026-06-25  
**Estado:** Fix implementado

---

## Síntoma

En hosting (no en desktop local), al entrar como admin a **Fiscalización → Cajas**:

- Toast/error: `Permiso requerido: admin.cash_sessions.summary`
- No aparece botón de **cierre administrativo** (requiere `admin.cash_sessions.force_close`)

En local con seeders/migraciones al día, el mismo usuario (`admin.demo`, rol `tenant_owner`) funciona.

---

## Causa raíz

La base de datos del hosting **no tenía sincronizados** los permisos de fiscalización de caja ni sus asignaciones en `role_permissions`:

| Permiso | Uso |
|---------|-----|
| `admin.cash_sessions.list` | Listado admin |
| `admin.cash_sessions.view` | Detalle sesión |
| `admin.cash_sessions.summary` | KPIs resumen (`GET /admin/cash-sessions/summary`) |
| `admin.cash_sessions.force_close` | Cierre administrativo |

Estos permisos se crearon originalmente en:

- `2026_06_10_100063_admin_cash_sessions_permissions.php` — list, view, summary
- `2026_06_21_100070_add_force_close_to_cash_sessions.php` — force_close

Roles con acceso en diseño V1: `super_admin`, `tenant_owner`, `cashier_senior`.

**Nota:** No existe rol slug `admin`; el usuario demo admin usa **`tenant_owner`**.

Además, los permisos quedan **cacheados en la sesión JWT/cookie** al login. Si se corrige la BD pero el usuario no cierra sesión, `/auth/me` y la UI siguen sin los slugs nuevos.

---

## Fix aplicado en código

### Migración idempotente (hosting-safe)

`database/migrations/2026_06_25_130000_ensure_admin_cash_sessions_permissions.php`

- `firstOrCreate` de los 4 permisos (no duplica slugs)
- `syncWithoutDetaching` a `super_admin`, `tenant_owner`, `cashier_senior`
- `down()` vacío — no elimina permisos en rollback

### Catálogo administrable

`ManageablePermissionCatalog.php` — agregado `admin.cash_sessions.summary` para gestión de roles en UI.

### Test

`tests/Feature/Api/V1/AdminCashSessionsTest.php` — `auth/me` de `admin.demo` incluye los 4 permisos.

---

## Acciones en hosting (obligatorias)

```bash
cd /path/to/backend
php artisan optimize:clear
php artisan migrate --force
```

Verificar en MySQL:

```sql
SELECT slug FROM permissions
WHERE slug LIKE 'admin.cash_sessions.%';

SELECT r.slug, p.slug
FROM role_permissions rp
JOIN roles r ON r.id = rp.role_id
JOIN permissions p ON p.id = rp.permission_id
WHERE p.slug LIKE 'admin.cash_sessions.%'
ORDER BY r.slug, p.slug;
```

Debe haber 4 filas en `permissions` y asignación a `super_admin`, `tenant_owner`, `cashier_senior`.

### Cache / sesión

1. Logout del usuario admin en hosting
2. Volver a login
3. Validar API:

```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

Respuesta debe incluir en `data.user.permissions`:

- `admin.cash_sessions.summary`
- `admin.cash_sessions.force_close`

---

## Validación API

| Endpoint | Permiso requerido |
|----------|-------------------|
| `GET /api/v1/admin/cash-sessions/summary` | `admin.cash_sessions.summary` |
| `GET /api/v1/admin/cash-sessions/{id}/close-check` | `admin.cash_sessions.force_close` |
| `POST /api/v1/admin/cash-sessions/{id}/force-close` | `admin.cash_sessions.force_close` |

---

## Si persiste el error

1. Confirmar que la migración `2026_06_25_130000` aparece en `migrations`
2. Confirmar filas en `role_permissions` para el rol del usuario (no asumir slug `admin`)
3. Forzar refresh de permisos: logout + login (o llamar `/auth/me` y actualizar store frontend)
4. Revisar que no haya override manual de roles sin esos slugs en el tenant de producción

---

## Archivos tocados

- `database/migrations/2026_06_25_130000_ensure_admin_cash_sessions_permissions.php` (nuevo)
- `app/Application/Role/Support/ManageablePermissionCatalog.php`
- `tests/Feature/Api/V1/AdminCashSessionsTest.php`
