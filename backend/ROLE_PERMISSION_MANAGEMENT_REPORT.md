# Backend — Gestión de Roles y Permisos (Admin de Sucursal)

## Objetivo

Permitir que el administrador del tenant gestione roles operativos y sus permisos sin acceder a permisos globales SaaS.

---

## Endpoints

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/admin/roles` | `roles.access` |
| GET | `/api/v1/admin/permissions` | `permissions.access` |
| GET | `/api/v1/admin/roles/{id}` | `roles.access` |
| POST | `/api/v1/admin/roles` | `roles.create` |
| PUT | `/api/v1/admin/roles/{id}` | `roles.update` |
| PUT | `/api/v1/admin/roles/{id}/permissions` | `roles.permissions.update` |
| DELETE | `/api/v1/admin/roles/{id}` | `roles.delete` |

---

## Permisos nuevos

- `roles.access`
- `roles.create`
- `roles.update`
- `roles.delete`
- `roles.permissions.update`
- `permissions.access`

Asignados a `super_admin` y `tenant_owner` (migración `2026_06_14_100001_add_role_management_permissions.php`).

---

## Whitelist administrable

`App\Application\Role\Support\ManageablePermissionCatalog` define:

- Grupos: Operación, Caja, Servicios, Habitaciones, Limpieza, Liquidaciones, Reportes, Catálogo, Personal, Turnos, Modos operativos, Configuración.
- Labels legibles por slug para el frontend.
- **Excluye** por diseño: `platform.*`, `admin.tenants.*`, `billing.*`, `superadmin.*`, `settings.bootstrap`, etc.

Al actualizar permisos se **preservan** permisos no administrables ya asignados al rol (p. ej. `admin.branches.*` en `tenant_owner`).

---

## Reglas de seguridad

1. **Tenant isolation** — solo roles con `tenant_id` del contexto actual.
2. **Roles globales** (`tenant_id = null`) no gestionables.
3. **`super_admin`** nunca editable desde este módulo.
4. **`tenant_owner`** protegido: no eliminable; slug fijo.
5. **Whitelist** — solo slugs en catálogo asignables vía API.
6. **No borrar** rol con usuarios asignados.
7. **Mínimo un rol** con `roles.permissions.update` en el tenant.
8. **Auto-revocación** — el admin no puede quitarse el último acceso a gestión de roles.
9. **Auditoría** — `role.created`, `role.updated`, `role.permissions.updated`, `role.deleted`.

---

## Archivos principales

| Archivo | Rol |
|---------|-----|
| `ManageablePermissionCatalog.php` | Whitelist, grupos, labels |
| `RoleAdminGuard.php` | Validaciones y mapeo |
| `AdminRoleController.php` | HTTP |
| `Application/Role/UseCases/*` | Casos de uso |
| `RolePermissionManagementTest.php` | 12 tests |

---

## Tests

```bash
php artisan test --filter=RolePermissionManagementTest
```

12 escenarios: listado tenant, aislamiento, whitelist, CRUD, protecciones, superadmin con contexto, denegación garzón/limpieza.

Suite completa: **398 tests verdes**.
