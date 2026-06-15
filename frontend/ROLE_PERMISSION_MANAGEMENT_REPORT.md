# Frontend — Gestión de Roles y Permisos

## Rutas

| Ruta | Permiso | Descripción |
|------|---------|-------------|
| `/nightpos/staff/roles` | `roles.access` | Listado de roles + pestaña catálogo de permisos |
| `/nightpos/staff/roles/:id/permissions` | `roles.permissions.update` | Edición de permisos por módulo |

---

## API client

`src/api/roles.js`:

- `fetchAdminRoles()`
- `fetchAdminRole(id)`
- `fetchManageablePermissions()`
- `createAdminRole(payload)`
- `updateAdminRole(id, payload)`
- `updateAdminRolePermissions(id, permissionSlugs)`
- `deleteAdminRole(id)`

---

## UI — Roles

Pestaña **Roles** en `staff/roles/index.vue`:

- Tabla: nombre, slug, usuarios asignados, cantidad de permisos administrables.
- Chip **Protegido** en `tenant_owner`.
- Acciones: Permisos, Editar, Eliminar (según permisos RBAC del usuario).
- Diálogos crear/editar/eliminar rol local.

---

## UI — Permisos

Pestaña **Permisos** (solo lectura): catálogo agrupado con label legible + slug técnico.

Pantalla `staff/roles/[id]/permissions.vue`:

- Permisos agrupados por módulo (misma agrupación que el backend).
- `VSwitch` por permiso con label legible.
- Botón **Guardar** → `PUT /admin/roles/{id}/permissions`.

---

## Navegación

`nightpos-r4.js` — ítem **Personal → Roles y permisos** requiere `roles.access` (antes usaba `admin.users.list`).

`useNightPosPermissions.js` — helpers `canAccessRoles`, `canCreateRole`, `canUpdateRole`, `canDeleteRole`, `canUpdateRolePermissions`, `canAccessPermissionsCatalog`.

---

## Validación manual

1. Login como `admin.demo`.
2. Personal → Roles y permisos.
3. Editar permisos de **Cajero** (quitar/agregar un permiso operativo).
4. Guardar y verificar con sesión de cajera que el menú refleja el cambio.
5. Login garzón / limpieza → no debe aparecer el módulo ni responder la API.

---

## Archivos

| Archivo |
|---------|
| `src/api/roles.js` |
| `src/pages/nightpos/staff/roles/index.vue` |
| `src/pages/nightpos/staff/roles/[id]/permissions.vue` |
| `src/composables/useNightPosPermissions.js` |
| `src/navigation/vertical/nightpos-r4.js` |
