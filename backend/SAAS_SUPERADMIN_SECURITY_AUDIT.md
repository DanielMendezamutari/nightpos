# SAAS / SUPERADMIN / SEGURIDAD — AUDITORÍA BACKEND

**Fecha:** 2026-06-25  
**Estado:** Auditoría original — **P0 implementado 2026-06-25** → ver `SAAS_P0_SUPERADMIN_ONBOARDING_IMPLEMENTATION_REPORT.md`  
**Alcance:** Operabilidad SaaS comercial, superadmin, seguridad, onboarding tenant

---

## 1. Resumen ejecutivo

NightPOS tiene **base SaaS funcional** (tenants, planes, wizard, provisioner unificado), pero **no está listo para producción comercial** como plataforma operada por Ribersoft sin depender del seeder demo.

### Hallazgo crítico — tenant nuevo sin menú Impresoras

**Causa raíz confirmada:** no es ausencia de `printer_settings` ni de `print_devices`. Es **drift de permisos** entre:

| Fuente | `settings.printers` en `tenant_owner` |
|--------|--------------------------------------|
| Seeder demo (`SeedsNightPosFoundation`) | ✅ Sí |
| Wizard / `TenantDefaultRolePermissions` | ❌ **No** |

El menú frontend exige permiso `settings.printers`. Sin él, la pantalla no aparece aunque la API de impresión exista y la sucursal tenga columnas `auto_print_*` en `true` por defecto.

**Conclusión:** el wizard **sí deja incompleto el tenant** respecto al demo — no por tablas de impresión, sino por **matriz de permisos desalineada** y **bootstrap operativo no automático**.

---

## 2. Respuestas a las 20 preguntas clave

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Pantalla superadmin cambiar contraseña? | **No** — no hay endpoint ni UI NightPOS |
| 2 | ¿Pantalla superadmin cambiar PIN? | **No** |
| 3 | ¿Crear otro superadmin? | **No** — solo seeder / SQL manual |
| 4 | ¿Desactivar otro superadmin? | **No** — no hay API usuarios globales |
| 5 | ¿Recuperar acceso si pierde contraseña? | **No** — páginas template frontend sin backend |
| 6 | ¿Endpoint cambio contraseña propia? | **No** — solo `POST /admin/users/{id}/reset-password` (admin, requiere tenant) |
| 7 | ¿Endpoint cambio PIN propio? | **No** — solo `POST /admin/users/{id}/reset-pin` (admin, requiere tenant) |
| 8 | ¿Reset PIN por admin? | **Sí** — `ResetUserPinAdminUseCase`, permiso `admin.users.create`, scope tenant |
| 9 | ¿Seeders necesarios en producción? | **No para credenciales** — hoy sí para permisos globales y demo; producción debe usar migraciones + comando bootstrap |
| 10 | ¿Primer superadmin en producción? | **Hoy:** `php artisan db:seed` o insert manual — **Recomendado V1:** `php artisan nightpos:create-superadmin` |
| 11 | ¿Protección superadmin? | JWT + rol `super_admin` bypass permisos; sin 2FA, sin lockout, sin rotación forzada |
| 12 | ¿Auditoría cambio contraseña/PIN? | **No** — `AuditLogRecorder` no registra resets de credenciales |
| 13 | ¿Política mínima contraseña? | **Débil** — login `min:4`, reset/create `min:6`, sin complejidad |
| 14 | ¿Bloqueo por intentos fallidos? | **No** |
| 15 | ¿2FA? | **No** — placeholder frontend; **V2** |
| 16 | ¿Planes/límites SaaS suficiente? | **Parcial** — CRUD planes + usage informativo; **sin enforcement** ni suscripciones |
| 17 | ¿Qué falta comercialmente? | Perfil/seguridad, bootstrap prod, permisos onboarding, enforcement plan, billing, auditoría global |
| 18 | ¿V1 vs V1.1/V2? | Ver sección 8 |
| 19 | ¿Por qué tenant nuevo sin impresoras? | **Permiso `settings.printers` no asignado** en `TenantRoleProvisioner` |
| 20 | ¿Qué más falta en onboarding? | Ver sección 4 |

---

## 3. Auth y sesión (estado actual)

### Endpoints existentes (`routes/api.php`)

| Método | Ruta | Función |
|--------|------|---------|
| POST | `/auth/login-pin` | PIN + tenant/branch |
| POST | `/auth/login-password` | Usuario/contraseña |
| GET | `/auth/login-context/tenants` | Empresas para login |
| GET | `/auth/login-context/branches` | Sucursales por tenant |
| GET | `/auth/me` | Usuario + permisos |
| POST | `/auth/refresh` | Renueva JWT (**no devuelve permisos**) |
| POST | `/auth/logout` | Invalida token (blacklist si habilitada) |

### Ausentes (requeridos V1)

| Endpoint propuesto | Uso |
|------------------|-----|
| `PATCH /auth/me/password` | Cambio contraseña propia (confirmar actual) |
| `PATCH /auth/me/pin` | Cambio PIN propio (confirmar password o PIN actual) |
| `GET /admin/platform/users` | Listar superadmins |
| `POST /admin/platform/users` | Crear superadmin |
| `PATCH /admin/platform/users/{id}` | Activar/desactivar |
| `POST /admin/platform/users/{id}/reset-password` | Reset por superadmin primario |
| `POST /auth/forgot-password` | V2 — email recovery |

### Superadmin

- Usuario `superadmin` creado en `SeedsNightPosFoundation` (`tenant_id = null`, rol global `super_admin`).
- `RequestOperationalContext::hasPermission()` → **bypass total** si `superAdmin === true`.
- Script utilitario: `backend/scripts/verify-superadmin.php` (solo verifica hash demo).
- **No existe** use case para CRUD de usuarios globales.

### JWT

- TTL operacional ~12h (`config/jwt.ttl`).
- Refresh window 14 días alineado con cookie frontend.
- Logout invalida token actual; **no hay revocación masiva** al cambiar contraseña (pendiente V1).

---

## 4. Onboarding tenant — análisis completo

### Flujo actual (correcto en arquitectura)

```
POST /admin/platform/setup  ──┐
POST /admin/tenants         ──┼──► TenantProvisioner (transacción)
                              │      1. Tenant + plan
                              │      2. TenantRoleProvisioner (5 roles)
                              │      3. Branch
                              │      4. Admin tenant_owner + PIN/password
```

Archivos: `TenantProvisioner.php`, `TenantRoleProvisioner.php`, `TenantDefaultRolePermissions.php`.

### ¿Tablas que solo crea el seeder demo?

| Recurso | Seeder demo | Wizard | Impacto |
|---------|-------------|--------|---------|
| Permisos globales (`permissions`) | Migraciones + seed sync | Usa catálogo existente | OK si migraciones corridas |
| Roles tenant (`roles`) | 6 roles incl. `cashier_senior` | **5 roles** — sin `cashier_senior` | Fiscalización senior incompleta |
| `role_permissions` tenant_owner | ~75 slugs | **~55 slugs** | **Menú config/fiscalización/roles oculto** |
| `payment_methods` | Sí (tenant) | **No automático** | Cobro mixto puede fallar |
| `cash_movement_reasons` | Parcial | **No automático** | Movimientos caja limitados |
| `service_areas` / mesas | Demo + bootstrap API | **No automático** | Garzón sin mesas |
| `product_categories` / productos | Demo extenso | **No automático** | Catálogo vacío |
| `cash_registers` | Sí (`CAJA-01`) | **No** | Apertura caja OK (`cash_register_id` nullable) |
| `print_devices` | No en seed (registro manual) | No | OK — se crea al registrar device |
| `branches.auto_print_*` | Default DB `true` | Default DB `true` | **Impresión funciona si hay permiso + device** |
| `staff_profiles` | Completos demo | Admin MANAGER sí | OK para admin |
| `user_branch_access` | Sí | Sí vía `accessibleBranchIds` | OK |

### Permisos en demo `tenant_owner` que faltan en wizard

**Críticos operación/config:**

- `settings.printers`, `settings.printers.manage` ← **Impresoras**
- `settings.bootstrap` ← bootstrap manual
- `settings.payment_methods`, `.manage`
- `settings.cash_reasons`, `.manage`
- `settings.service_areas`, `.manage`
- `settings.service_tables`, `.manage`
- `settings.waiter_assignments`, `.manage`
- `settings.room_types`, `.manage`
- `settings.checklist`
- `printing.reprint`
- `audits.list`
- `admin.cash_sessions.*` (4 slugs)
- `roles.*`, `permissions.access`
- `shift_console.access`
- `products.quick_create`

### Bootstrap operativo (`POST /settings/bootstrap-operational`)

- Use case: `BootstrapBranchOperationalDataUseCase` — crea categorías, productos base, áreas, métodos pago, show types, room types.
- Requiere permiso `settings.bootstrap` — **tampoco asignado** a tenant nuevo.
- **No se invoca** desde `TenantProvisioner`.

### Veredicto onboarding

| Pregunta | Respuesta |
|----------|-----------|
| ¿Wizard deja tenant incompleto? | **Sí** — permisos y datos operativos base |
| ¿Solo seeder demo? | No solo — migraciones crean permisos globales; **asignación por rol** difiere |
| ¿Setup debería crear automáticamente? | **Sí** — permisos alineados + bootstrap branch en provisioner |
| ¿Impresoras dependen de datos no creados? | **No tablas** — dependen de **permiso UI** + registro manual device (esperado) |

---

## 5. Impresión — aclaración técnica

No existe tabla `printer_settings`. Configuración en:

- `branches.auto_print_order_command` (default `true`)
- `branches.auto_print_sale_receipt` (default `true`)
- `print_devices` — creado al registrar agente (`RegisterPrintDeviceUseCase`)
- `print_jobs` — runtime

**Arquitectura correcta.** El bug reportado es **100% permisos/menú**, no infraestructura de impresión.

---

## 6. Gestión usuarios tenant (existente)

| Acción | API | Permiso | Scope |
|--------|-----|---------|-------|
| Listar/ver | `GET /admin/users` | `admin.users.list` | Tenant context |
| Crear | `POST /admin/users` | `admin.users.create` | Tenant |
| Reset PIN | `POST /admin/users/{id}/reset-pin` | `admin.users.create` | Tenant |
| Reset password | `POST /admin/users/{id}/reset-password` | `admin.users.create` | Tenant |
| Desactivar | `PUT /admin/users/{id}` status | `admin.users.update` | Tenant |

**Superadmin sin tenant:** reset password/pin **falla** (`TenantContextInterface` null en use cases).

Reglas PIN admin: `regex:/^\d{4,6}$/` en `CreateUserRequest`.

---

## 7. SaaS comercial (estado)

### Implementado (SAAS-1)

- `plans`, `plan_limits`, asignación `plan_id` en tenant
- Dashboard cards (activos, suspendidos, vencidos, trial)
- CRUD planes (superadmin)
- `TenantPlanUsageCalculator` — **solo informativo**, sin bloqueo
- Tenant status field + subscription dates en update

### Pendiente comercial

| Área | Estado |
|------|--------|
| Enforcement límites (users, branches, products) | ❌ |
| Bloqueo login tenant suspendido | ❌ |
| Entidad `subscriptions` + historial | ❌ SAAS-2 |
| Trial automático / vencimiento | ❌ |
| Datos facturación cliente (NIT, razón social) | ❌ |
| Notas internas Ribersoft por tenant | ❌ |
| Branding white-label | ❌ |
| Email transaccional (bienvenida, vencimiento) | ❌ |
| Auditoría acciones superadmin plataforma | ❌ |

Ref: `backend/SAAS_PLAN_MANAGEMENT_REPORT.md`.

---

## 8. Propuesta V1 / V1.1 / V2

### V1 — Bloqueante producción Ribersoft

1. **`php artisan nightpos:create-superadmin`** — interactivo, hash bcrypt, sin seed
2. **Unificar permisos onboarding** — `TenantDefaultRolePermissions` = fuente única alineada con demo (o extraer lista compartida con seeder)
3. **Provisioner ampliado:**
   - Rol `cashier_senior`
   - Llamar bootstrap operativo en transacción post-branch
   - Opcional: `cash_registers` default
4. **Auth self-service:**
   - `PATCH /auth/me/password` + invalidar tokens
   - `PATCH /auth/me/pin`
5. **Platform users API** — CRUD superadmins secundarios
6. **Auditoría:** `user.password.changed`, `user.pin.changed`, `user.password.reset`
7. **Política contraseña:** min 8, mayúscula+número (configurable)

### V1.1

- UI perfil NightPOS (no template Materialize)
- Reset password superadmin secundario desde plataforma
- Enforcement soft límites plan (warning UI)
- Bloqueo tenant `suspended` en middleware login

### V2

- 2FA TOTP superadmin + tenant_owner
- Forgot password email
- Suscripciones SAAS-2
- SSO / API keys

---

## 9. Bootstrap producción — recomendación

| Opción | Veredicto |
|--------|-----------|
| Seeder en producción | ❌ **Nunca** — credenciales demo, datos ficticios |
| Wizard inicial si no hay superadmin | ⚠️ Riesgo seguridad si URL pública |
| **`php artisan nightpos:create-superadmin`** | ✅ **Recomendado V1** — idempotente, auditable, CI/CD friendly |

Flujo producción:

```bash
php artisan migrate --force
php artisan nightpos:create-superadmin   # una vez
# NO db:seed en producción
```

Demo/desarrollo: `NightPosSeeder` intacto.

---

## 10. Menú superadmin propuesto (backend permisos)

Permisos globales existentes relevantes:

- `platform.setup`, `admin.tenants.*`, `admin.branches.*` (con contexto tenant)
- Falta: `platform.users.*`, `platform.audit.*`, `platform.security.*`

---

## 11. Archivos clave referencia

| Archivo | Relevancia |
|---------|------------|
| `TenantDefaultRolePermissions.php` | **Drift permisos wizard** |
| `SeedsNightPosFoundation.php` | Matriz demo completa |
| `TenantProvisioner.php` | Onboarding sin bootstrap |
| `BootstrapBranchOperationalDataUseCase.php` | Datos iniciales sucursal |
| `AuthController.php` | Sin self-service credenciales |
| `ResetUserPinAdminUseCase.php` | Reset admin scope tenant |
| `RequestOperationalContext.php` | Superadmin bypass |
| `routes/api.php` L551 | Bootstrap manual |
| `migration 2026_06_17_100002` | Permisos printing en roles demo vía migración |

---

## 12. Tests existentes vs gap

`TenantProvisioningTest.php` valida:

- Tenant + branch + admin + roles con **algún** permiso

**No valida:**

- Paridad permisos demo vs wizard
- Visibilidad menú impresoras
- Bootstrap operativo automático
- tenant_owner puede `GET /print-devices/settings`

**Test propuesto V1:** tras wizard, login admin nuevo → `/auth/me` incluye `settings.printers`.

---

## 13. Principio rector fix futuro

**Una sola fuente de verdad** para permisos default por rol:

```php
// Propuesta: TenantDefaultRolePermissions extends o imports SeedsNightPosFoundation matrix
// TenantProvisioner siempre llama BranchOperationalBootstrap
// Seeder demo solo agrega datos ficticios, no redefine permisos
```

Sin parches manuales SQL post-wizard.
