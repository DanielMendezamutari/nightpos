# SAAS_COMPLETE_AUDIT_REPORT.md

**Proyecto:** NightPOS  
**Tipo:** Auditoría SaaS completa (sin implementación)  
**Fecha:** 2026-06-14  
**Objetivo:** Inventariar el modo SaaS actual y definir roadmap hacia un SaaS administrable comercialmente.

**Fuentes revisadas:** `NIGHTPOS_V1_DEVELOPMENT_MAP.md`, `SAAS_ARCHITECTURE.md`, `DOMAIN_DESIGN.md`, `FRONTEND_GUIDELINES.md`, `PHASE_5_REPORT.md`, `PHASE_12_REPORT.md`, `PHASE_11_FRONTEND_REPORT.md`, `PHASE_R4_REPORT.md`, `ROLE_PERMISSION_MANAGEMENT_REPORT.md`, `LOGIN_CONTEXT_SELECTION_REPORT.md`, `QUICK_ACTIONS_PHASE_B_REPORT.md`, código backend (migraciones, middleware, use cases) y frontend (rutas `platform/*`).

---

## 1. Resumen ejecutivo

NightPOS **ya es un SaaS multi-tenant operativo**: una instalación sirve múltiples empresas con aislamiento por `tenant_id` / `branch_id`, superadmin global, wizard de alta de cliente, RBAC por tenant y bloqueo básico por estado/fecha de suscripción.

**No es aún un SaaS comercial administrable:** faltan planes reales, suscripciones como entidad, pagos, límites de uso, enforcement comercial, métricas de ingresos y ciclo de vida (trial → overdue → suspensión por mora).

| Dimensión | Estado | % estimado |
|-----------|--------|------------|
| Multi-tenant operativo (POS nocturno) | ✅ Completo | ~95% |
| Consola superadmin (CRUD básico) | ✅ Funcional | ~70% |
| Modelo comercial SaaS (planes, pagos, límites) | ❌ Ausente | ~15% |
| **SaaS administrable end-to-end** | ⏳ Parcial | **~35%** |

**Conclusión:** El núcleo técnico SaaS está listo para **piloto operativo** con pocos clientes gestionados manualmente. Para **vender y operar como SaaS** hace falta SAAS-1 → SAAS-5 como mínimo; automatización y facturación legal quedan en V2 (SAAS-6).

---

## 2. Estado actual del modo SaaS

### 2.1 Lo que SÍ existe y funciona

| Capa | Capacidad |
|------|-----------|
| **Identidad** | `super_admin` global (`tenant_id = null`); usuarios tenant con PIN/password |
| **Tenants** | CRUD listado/alta/edición (superadmin); campos plan y fechas en tabla |
| **Branches** | CRUD por tenant; código único por empresa; acceso por `user_branch_access` |
| **Provisioning** | Wizard `POST /admin/platform/setup` → tenant + branch + roles + admin |
| **RBAC** | Roles globales + roles por tenant; permisos granulares; módulo admin local de roles |
| **Contexto** | Cookies `tenantSlug` / `branchCode`; selector superadmin; login PIN con selectores |
| **Aislamiento** | Middleware `nightpos.tenant`, `nightpos.branch.access`, tests multi-tenant |
| **Bloqueo básico** | `status !== active` o `subscription_ends_at` vencido → login/API operativa bloqueada |
| **UI Plataforma** | Menú «Plataforma SaaS», dashboard básico, empresas, sucursales, setup wizard |

### 2.2 Lo que NO existe (huecos comerciales)

| Área | Estado |
|------|--------|
| Tabla `plans` | ❌ No existe |
| Tabla `subscriptions` / `tenant_subscriptions` | ❌ No existe (solo columnas en `tenants`) |
| Pagos SaaS / facturas / recibos | ❌ No existe |
| Límites por plan (`usage_limits`, `usage_counters`) | ❌ No existe |
| Estados trial / overdue / cancelled | ❌ No modelados |
| Historial de cambios de tenant (`tenant_status_history`) | ❌ No existe |
| Dashboard ingresos / vencimientos / uso | ❌ No existe |
| Enforcement al crear sucursales/usuarios/productos | ❌ No existe |
| Alertas pre-vencimiento / mora | ❌ No existe |
| Facturación automática / webhooks | ❌ No existe (V2) |

### 2.3 Discrepancia diseño vs implementación

`SAAS_ARCHITECTURE.md` y `DOMAIN_DESIGN.md` definen entidades `Plan`, `Subscription`, `subscription_status`, `legal_name`, `nit`. En código real:

- `plan_name` es **texto libre** en `tenants`, no FK.
- No hay `subscription_status` separado de `tenants.status`.
- No hay datos legales/fiscales del cliente SaaS.
- `CreateTenantAdminUseCase` (POST `/admin/tenants`) **no provisiona** roles/sucursal/admin; solo `platform/setup` lo hace → **dos caminos de alta inconsistentes**.

---

## 3. Tablas existentes (auditoría BD)

### 3.1 Tablas SaaS core — **EXISTEN**

| Tabla | Campos relevantes | Notas |
|-------|-------------------|-------|
| `tenants` | `id`, `name`, `slug`, `status`, `plan_name`, `subscription_starts_at`, `subscription_ends_at`, timestamps | Única fuente de “suscripción” hoy |
| `branches` | `id`, `tenant_id`, `name`, `code`, `address`, `status` | Sin `phone` (previsto en SAAS_ARCHITECTURE) |
| `users` | `tenant_id` (nullable superadmin), `branch_id`, `role_id`, `pin_hash`, `status` | SaaS-ready |
| `roles` | `tenant_id` (nullable = global), `name`, `slug` | `super_admin` global |
| `permissions` | `name`, `slug` | Catálogo global |
| `role_permission` | pivot | |
| `staff_profiles` | `staff_role`, comisiones, limpieza | Operativo |
| `user_branch_access` | pivot user ↔ branch | Control sucursal |

### 3.2 Tablas SaaS comerciales — **NO EXISTEN**

| Tabla esperada | Estado |
|----------------|--------|
| `plans` | ❌ |
| `plan_modules` / `plan_limits` | ❌ |
| `subscriptions` / `tenant_subscriptions` | ❌ |
| `invoices` / `saas_payments` | ❌ |
| `usage_limits` | ❌ |
| `usage_counters` | ❌ |
| `tenant_status_history` | ❌ |
| `billing_events` | ❌ |

### 3.3 Tablas relacionadas (no SaaS billing, pero útiles)

| Tabla | Uso |
|-------|-----|
| `audit_logs` | Auditoría operativa por tenant (no billing SaaS) |
| `operational_events` / `sse_tokens` | Tiempo real; sin límite por plan |

**Migraciones revisadas:** 54 archivos en `backend/database/migrations/` — ninguno crea planes, suscripciones ni facturación SaaS.

---

## 4. Endpoints existentes (backend)

### 4.1 Autenticación y contexto (público / pre-login)

| Método | Ruta | Función |
|--------|------|---------|
| POST | `/api/v1/auth/login-pin` | Login operativo; valida tenant activo + suscripción |
| POST | `/api/v1/auth/login-password` | Login admin; superadmin sin tenant |
| GET | `/api/v1/auth/login-context/tenants` | Empresas activas para selector PIN |
| GET | `/api/v1/auth/login-context/branches` | Sucursales activas por `tenant_slug` |

### 4.2 Plataforma / admin (auth + permisos)

| Método | Ruta | Permiso | Alcance |
|--------|------|---------|---------|
| GET/POST | `/api/v1/admin/tenants` | `admin.tenants.list` / `.create` | Listar / crear empresa (sin provisionar roles) |
| GET/PUT | `/api/v1/admin/tenants/{id}` | `admin.tenants.list` | Ver/editar (solo superadmin en use case) |
| POST | `/api/v1/admin/platform/setup` | `platform.setup` | Wizard completo: tenant + branch + roles + admin |
| GET/POST | `/api/v1/admin/branches` | `admin.branches.list` / `.create` | Por contexto tenant |
| GET/PUT | `/api/v1/admin/branches/{id}` | `admin.branches.list` | Edición sucursal |
| GET/POST/PUT | `/api/v1/admin/users` | `admin.users.*` | Usuarios del tenant en contexto |
| GET/POST/PUT/DELETE | `/api/v1/admin/roles` | `roles.*` | Roles operativos del tenant (no SaaS global) |

### 4.3 Operativos con enforcement de tenant

Todas las rutas bajo `auth:api` + `nightpos.tenant` pasan por `ResolveTenantMiddleware`:

- Rechaza tenant `status !== 'active'`.
- Rechaza si `subscription_ends_at < now()` (cuando está definido).
- Usuario normal: tenant fijado a `users.tenant_id`.
- Superadmin: tenant opcional vía header `X-Tenant-Slug`.

**No hay endpoints** para: planes, suscripciones, pagos SaaS, límites, métricas de plataforma, suspensión automática por mora.

### 4.4 Permisos de plataforma (catálogo)

| Slug | Quién lo tiene |
|------|----------------|
| `admin.tenants.list` | `super_admin` |
| `admin.tenants.create` | `super_admin` |
| `admin.branches.list` / `.create` | `super_admin`, `tenant_owner` |
| `admin.users.*` | `super_admin`, `tenant_owner` |
| `platform.setup` | Solo `super_admin` |
| `roles.*` / `permissions.access` | `super_admin`, `tenant_owner` (gestión local) |

---

## 5. Frontend existente (auditoría UI)

### 5.1 Módulo «Plataforma SaaS» (`nightpos-r4.js`)

| Ruta | Archivo | Estado | Notas |
|------|---------|--------|-------|
| `/nightpos/platform/dashboard` | `platform/dashboard.vue` | ✅ Real | Contadores empresas activas/suspendidas; accesos rápidos |
| `/nightpos/platform/setup` | `platform/setup.vue` | ✅ Real | Wizard 3 pasos: empresa, sucursal, admin |
| `/nightpos/platform/tenants` | `platform/tenants/index.vue` | ✅ Real | Tabla, KPIs, botón «Operar» |
| `/nightpos/platform/tenants/create` | `platform/tenants/create.vue` | ✅ Real | Formulario; **no provisiona** (solo API tenants) |
| `/nightpos/platform/tenants/:id` | `platform/tenants/[id]/index.vue` | ✅ Real | Detalle |
| `/nightpos/platform/tenants/:id/edit` | `platform/tenants/[id]/edit.vue` | ✅ Real | plan_name + fechas suscripción (texto libre) |
| `/nightpos/platform/branches` | `platform/branches/index.vue` | ✅ Real | Listado con contexto tenant |
| `/nightpos/platform/branches/create` | `platform/branches/create.vue` | ✅ Real | Alta sucursal |
| `/nightpos/platform/branches/:id` | `platform/branches/[id]/index.vue` | ✅ Real | Detalle |
| `/nightpos/platform/branches/:id/edit` | `platform/branches/[id]/edit.vue` | ✅ Real | Edición |
| `/nightpos/platform/plans` | `platform/plans/index.vue` | ⏳ **Placeholder** | `NightPosModulePlaceholder` |
| `/nightpos/platform/settings` | `platform/settings/index.vue` | ⏳ **Placeholder** | Parámetros globales sin API |

### 5.2 Componentes transversales SaaS

| Componente | Función | Estado |
|------------|---------|--------|
| `PlatformContextSelector.vue` | Superadmin elige empresa/sucursal en navbar | ✅ Real |
| `usePlatformContext.js` | Cookies + refresh contexto operativo | ✅ Real |
| `login.vue` (PIN) | Selectores empresa/sucursal desde API | ✅ Real (reciente) |
| `TenantFormFields.vue` | status, plan_name, fechas | ✅ Real (campos libres) |

### 5.3 Módulos relacionados (no plataforma, pero SaaS-relevantes)

| Módulo | Estado |
|--------|--------|
| Personal → Roles y permisos | ✅ Real (admin tenant, no superadmin global) |
| Configuración tenant (settings/*) | ✅ Operativo; no billing |
| Menú operativo | Requiere contexto tenant (superadmin) o `tenant_id` (resto) |

### 5.4 Placeholders / rutas sin backend

- `platform/plans` — sin API.
- `platform/settings` — sin API.
- Dashboard: sin ingresos, vencimientos próximos ni uso por tenant.
- `plan_name` en UI es campo texto, no selector de planes.

### 5.5 Duplicidades / deuda UX

| Issue | Impacto |
|-------|---------|
| Dos altas de empresa: «Crear empresa» vs «Setup empresa» | Empresa creada sin roles/usuarios si se usa solo create |
| `PLATFORM_SECTION_TABS` incluye «Planes» hacia placeholder | Expectativa rota para superadmin |
| Permiso meta `admin.tenants.list` en edición tenant | Funciona porque use case exige superadmin, no por permiso granular |

---

## 6. Qué está completo (para V1 operativo)

✅ **Listo para piloto con gestión manual del superadmin:**

1. Alta de cliente vía **Setup empresa** (recomendado).
2. Edición manual de `plan_name`, fechas y `status` en tenant.
3. Operar como cliente con selector de contexto.
4. Aislamiento multi-tenant verificado en tests.
5. Bloqueo de acceso si empresa inactiva/suspendida o suscripción vencida (por fecha).
6. RBAC completo operativo + gestión local de roles.
7. Login PIN usable en celular con selección de empresa/sucursal.

---

## 7. Qué falta (brecha hacia SaaS administrable)

### 7.1 Modelo de datos

- Entidad `Plan` con límites y módulos.
- Entidad `Subscription` con ciclo de vida propio.
- Pagos / facturas internas SaaS.
- Contadores de uso por tenant.
- Historial de suspensiones y cambios de plan.

### 7.2 Lógica de negocio

- Enforcement de límites al crear recursos.
- Estado `overdue` / `trial` con reglas distintas a `suspended`.
- Renovación automática de `subscription_ends_at` al registrar pago.
- Job/cron de vencimientos y alertas.
- Unificar alta de tenant (siempre provisionar o bloquear create simple).

### 7.3 UI superadmin

- CRUD planes.
- Vista suscripción por tenant (estado, trial, próximo pago).
- Registro de pagos manuales + historial.
- Dashboard: ingresos MRR, vencimientos 7/30 días, tenants en mora.
- Configuración global SaaS (email, trial default, grace period).

### 7.4 Observabilidad comercial

- Métricas uso: sucursales, usuarios, productos, sesiones caja.
- Reporte «clientes activos vs inactivos» con criterio de actividad operativa.

---

## 8. Respuestas a preguntas clave

### 8.1 Empresas / tenants

| # | Pregunta | Respuesta actual |
|---|----------|------------------|
| 1 | ¿Qué campos tiene tenant hoy? | `id`, `name`, `slug`, `status`, `plan_name`, `subscription_starts_at`, `subscription_ends_at`, timestamps. **No:** `legal_name`, `nit`, `plan_id`, `subscription_status`. |
| 2 | ¿Tiene estado activo/suspendido? | **Sí.** `status`: `active`, `inactive`, `suspended` (validado en forms). |
| 3 | ¿Tiene plan? | **Parcial.** Campo texto `plan_name` (ej. `pro`, `standard`). Sin tabla ni FK. |
| 4 | ¿Tiene fecha inicio/fin suscripción? | **Sí.** `subscription_starts_at`, `subscription_ends_at` (opcionales). |
| 5 | ¿Puede suspenderse? | **Sí**, manualmente vía PUT tenant `status = suspended` (superadmin). |
| 6 | ¿Puede reactivarse? | **Sí**, `status = active` + fechas vigentes. Sin workflow ni historial. |
| 7 | ¿Puede bloquear login por mora? | **Parcial.** Bloqueo por `!isActive()` o fecha vencida. **No** hay concepto «mora/overdue» ni grace period; `suspended` bloquea igual que `inactive`. |

**Enforcement actual:**

```php
// Tenant::isActive() → status === 'active'
// Tenant::hasValidSubscription() → subscription_ends_at null OR ends_at >= now
```

Aplicado en: `LoginWithPinUseCase`, `LoginWithPasswordUseCase`, `ResolveTenantMiddleware`, `ListLoginContextTenantsUseCase`.

---

### 8.2 Planes

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Existe tabla plans? | **No.** |
| 2 | ¿Existe UI de planes? | **Solo placeholder** (`platform/plans`). |
| 3 | ¿Se puede crear plan? | **No.** |
| 4 | ¿Se puede asignar plan a tenant? | **Solo texto libre** `plan_name` al crear/editar tenant. |
| 5 | ¿Qué límites debe tener cada plan? | **No definidos en sistema.** Propuesta de límites para SAAS-1: |

| Límite sugerido | Básico | Pro | Empresarial |
|-----------------|--------|-----|-------------|
| Sucursales | 1 | 3 | Ilimitado |
| Usuarios | 10 | 30 | Ilimitado |
| Cajas concurrentes | 2 | 5 | Ilimitado |
| Productos | 100 | 500 | Ilimitado |
| Reportes avanzados | No | Sí | Sí |
| SSE tiempo real | No | Sí | Sí |
| Impresión / tickets | Básico | Sí | Sí |
| Inventario (V2) | No | Limitado | Sí |
| Auditoría global | No | No | Sí |

---

### 8.3 Suscripciones

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Existe suscripción por tenant? | **Embutida en `tenants`**, no entidad separada. |
| 2 | ¿Tiene fecha inicio? | **Sí** (`subscription_starts_at`). |
| 3 | ¿Tiene fecha fin? | **Sí** (`subscription_ends_at`). |
| 4 | ¿Estados trial/active/overdue/suspended/cancelled? | **No.** Solo `tenants.status` (active/inactive/suspended). |
| 5 | ¿Qué pasa al vencer? | API operativa y login rechazan con `TenantNotAvailableException` / tenant excluido del login-context. **No** hay modo solo lectura ni pantalla de renovación. |

---

### 8.4 Pagos SaaS

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Se registran pagos del cliente SaaS? | **No.** |
| 2 | ¿Historial de pagos? | **No.** |
| 3 | ¿Marcar pagado manualmente? | **No.** (workaround: extender `subscription_ends_at` a mano) |
| 4 | ¿Próxima renovación? | **No calculada**; solo fecha fin manual. |
| 5 | ¿Recibo interno? | **No.** |

---

### 8.5 Límites de uso

| Recurso | ¿Limitado hoy? |
|---------|----------------|
| Crear sucursales | ❌ Sin límite |
| Crear usuarios | ❌ Sin límite |
| Crear productos | ❌ Sin límite |
| Reportes | ❌ Solo por permiso RBAC, no por plan |
| SSE | ❌ Sin gate por plan |
| Impresión/PDF | ❌ Sin gate por plan |

---

### 8.6 Superadmin dashboard

| Métrica requerida | Estado |
|-------------------|--------|
| Total empresas | ✅ |
| Activas | ✅ |
| Suspendidas | ✅ |
| Vencidas | ❌ (no se calcula; requiere cruzar `subscription_ends_at`) |
| Ingresos mensuales | ❌ |
| Próximos vencimientos | ❌ |
| Uso por tenant | ❌ |
| Acceso rápido a operar | ✅ |

---

## 9. Riesgos

| Riesgo | Severidad | Detalle |
|--------|-----------|---------|
| Alta de empresa sin provisionar | **Alto** | POST `/admin/tenants` deja tenant huérfano sin roles/admin |
| `plan_name` libre sin enforcement | **Alto** | No hay coherencia comercial ni límites |
| Suspensión solo manual | **Medio** | Sin cron de mora; depende de superadmin |
| Vencimiento = bloqueo total | **Medio** | Sin grace period ni modo lectura; puede cortar operación en pleno turno |
| Sin historial billing | **Medio** | Imposible auditar quién extendió suscripción y cuándo |
| Dashboard incompleto | **Bajo** | Superadmin no ve salud comercial del negocio SaaS |
| Diseño doc ≠ código | **Medio** | `SAAS_ARCHITECTURE.md` promete tablas que no existen |
| Dos fuentes de verdad suscripción | **Alto** | Cuando exista tabla `subscriptions`, migrar desde columnas `tenants` |

---

## 10. Roadmap SaaS propuesto

### SAAS-1 — Planes y límites (fundación comercial)

**Objetivo:** Dejar de usar `plan_name` texto libre.

| Entregable | Detalle |
|------------|---------|
| Tabla `plans` | `slug`, `name`, `price_monthly`, `is_active`, JSON `limits` |
| Tabla `plan_modules` (opcional) | Flags por módulo |
| CRUD planes | API + UI reemplazando placeholder `platform/plans` |
| `tenants.plan_id` | FK; migración desde `plan_name` |
| Servicio `PlanLimitGuard` | Consulta límites; sin enforcement aún |

**Límites iniciales en JSON:** `max_branches`, `max_users`, `max_products`, `sse_enabled`, `advanced_reports`, `printing_enabled`.

---

### SAAS-2 — Suscripciones

**Objetivo:** Ciclo de vida comercial explícito.

| Entregable | Detalle |
|------------|---------|
| Tabla `tenant_subscriptions` | `tenant_id`, `plan_id`, `status`, `starts_at`, `ends_at`, `trial_ends_at` |
| Estados | `trial`, `active`, `overdue`, `suspended`, `cancelled` |
| Reglas | Trial → active; overdue tras N días post `ends_at`; suspended bloquea login |
| UI tenant | Pestaña Suscripción en detalle empresa |
| Deprecar columnas | `tenants.subscription_*` → vista o sync temporal |

---

### SAAS-3 — Pagos SaaS manuales

**Objetivo:** Cobranza operada por superadmin sin pasarela (V1 comercial).

| Entregable | Detalle |
|------------|---------|
| Tabla `saas_payments` | `tenant_id`, `amount`, `currency`, `period_start`, `period_end`, `method`, `notes`, `recorded_by` |
| UI | Registrar pago, historial por tenant |
| Acción | Al marcar pagado → extender `ends_at` + estado `active` |
| Recibo | PDF/HTML simple interno (opcional V1) |

---

### SAAS-4 — Enforcement

**Objetivo:** El plan se siente en producción.

| Punto de control | Acción al exceder límite |
|----------------|--------------------------|
| POST `/admin/branches` | 422 «Límite de sucursales del plan» |
| POST `/admin/users` | 422 «Límite de usuarios» |
| POST `/products` | 422 o warning según plan |
| Rutas reportes avanzados | 403 si plan no incluye |
| SSE token | 403 si plan sin SSE |
| Login / middleware | Mensaje claro si `overdue` / `suspended` |
| Banner UI tenant | «Suscripción vence en X días» |

---

### SAAS-5 — Dashboard SaaS

**Objetivo:** Consola de salud del negocio NightPOS como SaaS.

| Widget | Fuente |
|--------|--------|
| Empresas activas / trial / mora | `tenant_subscriptions` |
| Vencimientos 7 y 30 días | query `ends_at` |
| MRR estimado | suma `plans.price_monthly` × activos |
| Ingresos cobrados mes | `saas_payments` |
| Top tenants por usuarios/sucursales | contadores |
| Acciones rápidas | Operar, registrar pago, suspender |

---

### SAAS-6 — Facturación y automatización (V2)

**Fuera de V1 comercial mínimo:**

- Pasarelas de pago (Stripe/MercadoPago/etc.).
- Factura legal / NIT cliente.
- Webhooks de pago.
- Planes públicos y self-service signup.
- Dunning automático (emails mora).
- Inventario limitado por plan.
- API pública documentada para partners.

---

## 11. Qué hacer primero (prioridad recomendada)

Orden sugerido para máximo impacto con mínimo riesgo:

1. **SAAS-1 Planes** — elimina `plan_name` libre; base para todo lo demás.
2. **SAAS-2 Suscripciones** — estados trial/overdue; unificar con middleware existente.
3. **SAAS-4 Enforcement (mínimo)** — límites sucursales + usuarios + bloqueo login por estado suscripción.
4. **Unificar alta tenant** — deprecar create simple o hacer que siempre llame a provisioner (como setup).
5. **SAAS-3 Pagos manuales** — operación comercial diaria del superadmin.
6. **SAAS-5 Dashboard** — visibilidad; puede ir en paralelo con SAAS-3.

**No empezar por:** pasarelas de pago, factura legal, webhooks (SAAS-6).

---

## 12. Qué queda para V1 vs V2

### V1 (SaaS administrable manual — objetivo post-auditoría)

| Incluir en V1 | Excluir de V1 |
|---------------|---------------|
| CRUD planes | Pasarela automática |
| Suscripciones con estados | Factura legal tributaria |
| Pagos manuales + extensión fecha | Self-service signup público |
| Límites sucursales/usuarios | Inventario por plan |
| Dashboard vencimientos + MRR básico | Webhooks |
| Enforcement login por mora | Dunning email automático |
| Historial pagos | API partners |
| Unificar wizard/setup como camino oficial | |

### V2 (SaaS maduro)

- SAAS-6 completo.
- `tenant_status_history` + billing_events.
- Modo solo lectura al vencer (opcional).
- Límites productos, SSE, impresión avanzada.
- Métricas de uso en tiempo real por tenant.
- Configuración SaaS global (`platform/settings` real).

---

## 13. Matriz de completitud por área

| Área SaaS | Backend | Frontend | Tests | Veredicto |
|-----------|---------|----------|-------|-----------|
| Multi-tenant core | ✅ | ✅ | ✅ | Completo |
| Superadmin CRUD tenants/branches | ✅ | ✅ | ✅ | Completo |
| Wizard setup | ✅ | ✅ | ✅ | Completo |
| Contexto operativo | ✅ | ✅ | ✅ | Completo |
| RBAC plataforma vs tenant | ✅ | ✅ | ✅ | Completo |
| Planes | ✅ | ✅ | ✅ | SAAS-1 completo |
| Límites uso (display) | ✅ | ✅ | ✅ | Informativo |
| Provisioning unificado | ✅ | ✅ | ✅ | Corregido |
| Suscripciones (entidad) | ❌ | ❌ | ❌ | Ausente |
| Pagos SaaS | ❌ | ❌ | ❌ | Ausente |
| Límites uso | ❌ | ❌ | ❌ | Ausente |
| Dashboard comercial | Parcial | Parcial | ❌ | ~25% |
| Bloqueo vencimiento | ✅ básico | N/A | Parcial | Parcial |
| Config global SaaS | ❌ | Placeholder | ❌ | Ausente |

---

## 14. Referencias cruzadas

| Documento | Relevancia SaaS |
|-----------|-----------------|
| `SAAS_ARCHITECTURE.md` | Visión objetivo (adelantada al código) |
| `DOMAIN_DESIGN.md` §3.1 Tenant | Plan/Subscription como agregados — no implementados |
| `PHASE_5_REPORT.md` | Middleware tenant/suscripción |
| `PHASE_12_REPORT.md` | Edición tenant con fechas |
| `QUICK_ACTIONS_PHASE_B_REPORT.md` | `platform/setup` |
| `LOGIN_CONTEXT_SELECTION_REPORT.md` | Pre-login tenant filtering |
| `ROLE_PERMISSION_MANAGEMENT_REPORT.md` | RBAC tenant (no billing) |
| `NIGHTPOS_V1_DEVELOPMENT_MAP.md` | ~83% hacia SaaS comercial |

---

## 15. Criterio de éxito post-roadmap

NightPOS será un **SaaS administrable** cuando el superadmin pueda, sin tocar BD:

1. Crear un plan con límites.
2. Asignar plan y suscripción a un tenant.
3. Ver cuándo vence y quién está en mora.
4. Registrar un pago y extender automáticamente el acceso.
5. Ver que el sistema **impide** exceder límites del plan.
6. Operar cualquier tenant con un clic (ya funciona hoy).

**Estado actual (post SAAS-1, 2026-06-14):** pasos 1 y 4 parciales ✅; pasos 2–3 y 5 ❌ (SAAS-2+).

---

## 16. Actualización SAAS-1 (2026-06-14)

### Corrección crítica aplicada
- `CreateTenantAdminUseCase` y `PlatformSetupUseCase` unificados vía `TenantProvisioner`.
- Eliminado el camino de tenant vacío en `POST /admin/tenants`.

### Implementado en SAAS-1
| Ítem | Estado |
|------|--------|
| Tablas `plans`, `plan_limits` | ✅ |
| `tenants.plan_id` FK | ✅ |
| CRUD planes (API) | ✅ |
| UI superadmin Planes | ✅ |
| Dashboard cards (activas/suspendidas/vencidas/trial/top planes) | ✅ |
| Uso vs límites (informativo) | ✅ |
| Enforcement comercial | ❌ (SAAS-4) |
| Suscripciones como entidad | ❌ (SAAS-2) |

### Matriz actualizada

| Área SaaS | Backend | Frontend | Tests | Veredicto |
|-----------|---------|----------|-------|-----------|
| Planes | ❌ | Placeholder | ❌ | Ausente |
| Límites uso (display) | ✅ | ✅ | ✅ | Informativo |
| Provisioning unificado | ✅ | ✅ | ✅ | Corregido |
| Suscripciones (entidad) | ❌ | ❌ | ❌ | SAAS-2 |
| Pagos SaaS | ❌ | ❌ | ❌ | SAAS-3 |
| Enforcement límites | ❌ | ❌ | ❌ | SAAS-4 |
| Dashboard comercial ingresos | ❌ | Parcial | ❌ | SAAS-3+ |

**Próximo paso:** SAAS-2 — Suscripciones.

---

*Auditoría original 2026-06-14. Actualizada tras SAAS-1.*
