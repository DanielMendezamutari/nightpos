# PHASE_10_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Usuarios y personal  
**Fase:** 10 — Gestión admin de usuarios, staff profiles, sucursales  
**Fecha:** 2026-06-03  
**Referencias:** `DOMAIN_DESIGN.md`, `BOLICHE_RULES.md`, `FRONTEND_GUIDELINES.md`

---

## 1. Endpoints creados / ampliados

| Método | Ruta | Permiso | Descripción |
| ------ | ---- | ------- | ----------- |
| GET | `/api/v1/admin/users` | `admin.users.list` | Listado (existente, respuesta enriquecida) |
| POST | `/api/v1/admin/users` | `admin.users.create` | Crear usuario (existente, ampliado) |
| GET | `/api/v1/admin/users/{id}` | `admin.users.list` | Detalle usuario |
| PUT | `/api/v1/admin/users/{id}` | `admin.users.update` | Actualizar usuario y staff profile |
| POST | `/api/v1/admin/users/{id}/reset-pin` | `admin.users.create` | Reset PIN (hash + fingerprint) |
| POST | `/api/v1/admin/users/{id}/reset-password` | `admin.users.create` | Reset contraseña (hash) |
| POST | `/api/v1/admin/users/{id}/branches` | `admin.users.create` | Asignar sucursal |
| DELETE | `/api/v1/admin/users/{id}/branches/{branch_id}` | `admin.users.update` | Quitar sucursal |

---

## 2. Reglas implementadas

| # | Regla | Implementación |
| - | ----- | -------------- |
| 1 | Solo admin/superadmin crea usuarios | Middleware `admin.users.create` |
| 2 | Solo admin cambia PIN | `reset-pin` bajo `admin.users.create` |
| 3 | PIN nunca en plano en BD | `pin_hash` + `pin_fingerprint` |
| 4 | Password hasheado | Cast `hashed` en `UserModel` |
| 5 | Garzón con comisión variable | `StaffProfileRules` exige % para `WAITER` |
| 6 | Chica con `can_receive_girl_commissions` | Default `true` en `GIRL` |
| 7 | Cajera sin comisión | `CASHIER` fuerza comisión `null` |
| 8 | Acceso multi-sucursal | `user_branch_access` + `accessible_branch_ids` |
| 9 | Usuario inactivo no login | Filtro `status=active` + check post-PIN |
| 10 | Sin cruce de tenant | Queries con `tenant_id` del contexto |

**Staff profile:** `staff_role`, `waiter_commission_percent`, `can_receive_girl_commissions`, `status`.

**Resolución RBAC:** `StaffRoleToRoleResolver` mapea `CASHIER`→`cashier`, `WAITER`/`GIRL`→`waiter`, `MANAGER`→`tenant_owner`.

---

## 3. Archivos backend principales

- `app/Application/User/Support/UserAdminMapper.php`
- `app/Application/User/Support/StaffProfileRules.php`
- `app/Application/User/Support/StaffRoleToRoleResolver.php`
- Use cases: `GetUserAdmin`, `UpdateUserAdmin`, `ResetUserPin`, `ResetUserPassword`, `GrantUserBranchAccess`, `RevokeUserBranchAccess`
- `app/Http/Controllers/Api/V1/Admin/AdminUserController.php` (ampliado)
- Migraciones: `2026_06_03_100010_assign_sales_permissions_to_roles.php`, `2026_06_03_100011_add_admin_users_update_permission.php`
- Tests: `tests/Feature/Api/V1/AdminUsersPhase10Test.php`

---

## 4. Pruebas ejecutadas

```bash
php artisan test
# 57 passed (incluye AdminUsersPhase10Test: 8 casos)
```

Cubre: garzón 5%/6%, chica con comisiones, cajero sin crear, inactivo sin login, tenant aislado, PIN hasheado, multi-sucursal.

---

## 5. Validación manual recomendada

1. `php artisan migrate` (permiso `admin.users.update` en dueños existentes).
2. Login `admin.demo` / `AdminDemo123!` — menú **Usuarios / Personal**.
3. Crear garzón 5% y 6%, chica, cajera.
4. Reset PIN, desactivar usuario, intentar login con inactivo.
5. `pnpm run dev` — consola sin errores críticos.

**Credenciales demo:** tenant `casa-demo`, sucursal `CENTRO`, admin PIN `2468`.

---

## 6. Pendiente

| Tema | Nota |
| ---- | ---- |
| Rol `GIRL` dedicado en RBAC | Hoy usa rol `waiter` + `staff_role=GIRL` |
| Edición masiva / importación | Fuera de alcance |
| Auditoría de cambios en usuarios | Fase posterior |
| Listado de roles API | Resolver interno por `staff_role` |

---

## 7. Próxima fase recomendada

**Fase R3 / 11:** Comandas listado + barra + garzón móvil; o liquidaciones/comisiones reporte.
