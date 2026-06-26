# SAAS / SUPERADMIN / SEGURIDAD — AUDITORÍA FRONTEND

**Fecha:** 2026-06-25  
**Estado:** Auditoría original — **P0 implementado 2026-06-25** → ver `SAAS_P0_SUPERADMIN_ONBOARDING_IMPLEMENTATION_REPORT.md`  
**Alcance:** UX superadmin, perfil, seguridad, onboarding, menú impresoras

---

## 1. Resumen ejecutivo

El frontend NightPOS operativo está **maduro para boliche demo**, pero la capa **SaaS + cuenta propia + seguridad** está incompleta. El superadmin navega con credenciales seed; no hay flujos de perfil real. El tenant creado por wizard **parece roto** en impresoras porque el **menú está condicionado por permisos** que el wizard no asigna.

---

## 2. Hallazgo impresoras — explicación UX

### Síntoma reportado

Tenant nuevo (wizard) → no aparece **Configuración → Impresoras**.  
Tenant demo → sí aparece.

### Causa (frontend)

Menú en `navigation/vertical/nightpos-r4.js`:

```js
{ title: 'Impresoras', to: 'nightpos-settings-printers', subject: 'settings.printers' }
```

Guard CASL + `useNightPosPermissions` ocultan ítem sin permiso.

Página `settings/printers/index.vue`:

```js
definePage({ meta: { permission: 'settings.printers' } })
```

### Causa (backend — ver audit backend)

Rol `tenant_owner` provisionado **sin** `settings.printers` en `TenantDefaultRolePermissions.php`.

### Lo que NO es el problema

- No falta ruta Vue — existe y funciona en demo
- No falta API client — `adminCashSessions` / print devices OK
- No requiere datos precargados en `print_devices` para **ver** la pantalla

### Otros ítems de menú ocultos en tenant nuevo (misma causa)

| Menú | Permiso faltante |
|------|------------------|
| Impresoras | `settings.printers` |
| Métodos de pago | `settings.payment_methods` |
| Motivos caja | `settings.cash_reasons` |
| Ambientes / Mesas | `settings.service_areas`, `settings.service_tables` |
| Asignar mesas | `settings.waiter_assignments` |
| Tipos habitación | `settings.room_types` |
| Checklist 1ª noche | `settings.checklist` |
| Bitácora auditoría | `audits.list` |
| Fiscalización cajas | `admin.cash_sessions.list` |
| Roles y permisos | `roles.access` |
| Consola turno (admin) | `shift_console.access` |

El admin del tenant nuevo ve un NightPOS **recortado** vs demo.

---

## 3. Respuestas a las 20 preguntas (perspectiva UI)

| # | Pregunta | Respuesta frontend |
|---|----------|-------------------|
| 1 | ¿Pantalla cambiar contraseña superadmin? | **No** — solo templates Materialize (`pages-account-settings`) no conectados |
| 2 | ¿Pantalla cambiar PIN superadmin? | **No** |
| 3 | ¿Crear otro superadmin? | **No** — no hay pantalla usuarios globales |
| 4 | ¿Desactivar superadmin? | **No** |
| 5 | ¿Recuperar acceso? | **No** — `forgot-password-v1/v2` son demo sin API |
| 6–8 | Endpoints propios / reset | Ver audit backend |
| 9 | Seeders producción | UI asume demo; login superadmin hardcoded en docs |
| 10 | Primer superadmin | Sin UI — solo CLI propuesto |
| 11 | Protección | Sin UI 2FA, sin aviso contraseña débil |
| 12 | Auditoría UI | Bitácora existe (`settings/audit-logs`) pero tenant nuevo sin `audits.list` |
| 13–15 | Política / lockout / 2FA | No en UI operativa |
| 16 | Planes SaaS | ✅ `platform/plans`, dashboard — funcional |
| 17 | Falta comercial | Perfil, seguridad, usuarios globales, onboarding completo |
| 18 | V1/V1.1/V2 | Ver sección 7 |
| 19 | Impresoras tenant nuevo | **Permiso no en JWT/cookie tras login** → menú oculto |
| 20 | Otras configs faltantes | Menú config casi entero oculto + sin wizard bootstrap UI |

---

## 4. Login y cuenta

### Login (`pages/login.vue`) — ✅ maduro

- Superadmin: password sin tenant
- Operativos: PIN + contexto guardado (cookies 30 d)
- Cambiar empresa/sucursal
- No guarda PIN

### Account menu (`layouts/components/UserProfile.vue`)

**NightPOS real:**

| Ítem | Comportamiento actual | Esperado V1 |
|------|----------------------|-------------|
| Mi perfil | Redirige a **home route** (`resolveHomeRoute`) — **no es perfil** | Página perfil |
| Cambiar sucursal | ✅ `BranchChangeDialog` | OK |
| Cerrar sesión | ✅ | OK |
| Cambiar contraseña | ❌ | Agregar |
| Cambiar PIN | ❌ | Agregar |
| Cambiar cuenta | ❌ explícito | Logout + link login |

**Demo Materialize** (solo rutas demo): Profile, Settings, Billing — **no usar en producción**.

---

## 5. Pantallas placeholder (deben implementarse V1)

| Ruta | Estado |
|------|--------|
| `settings/security/index.vue` | Placeholder: "Políticas de contraseña, 2FA — pendiente" |
| `platform/settings/index.vue` | Placeholder: "Parámetros globales — pendiente API" |

---

## 6. Gestión usuarios (tenant) — parcial ✅

| Pantalla | Función |
|----------|---------|
| `users/index.vue`, `create.vue`, `[id]/edit.vue` | CRUD tenant |
| `[id]/edit.vue` | Reset PIN + reset password **por admin** (campos plain en form — OK para admin reset) |

**Limitación:** superadmin operando **sin tenant context** no puede resetear usuarios desde UI tenant admin (API exige tenant).

**No hay** pantalla usuarios globales superadmin.

---

## 7. Plataforma SaaS — estado UI

### Implementado ✅

| Pantalla | Ruta | Notas |
|----------|------|-------|
| Dashboard SaaS | `platform/dashboard.vue` | Cards tenants/planes |
| Setup wizard | `platform/setup.vue` | Crea tenant completo vía API |
| Empresas | `platform/tenants/*` | CRUD |
| Sucursales | `platform/branches/*` | CRUD con header tenant |
| Planes | `platform/plans/index.vue` | CRUD límites |

### Menú Plataforma (`nightpos-r4.js` sección 7)

Actual:

- Dashboard SaaS
- Setup empresa
- Empresas / Sucursales
- Planes / Suscripciones
- Configuración SaaS (placeholder)

### Menú superadmin propuesto (V1)

```
Plataforma SaaS
├── Dashboard
├── Setup empresa
├── Empresas
├── Sucursales
├── Planes
├── Usuarios globales        ← NUEVO
├── Seguridad plataforma     ← NUEVO (políticas, 2FA futuro)
├── Auditoría plataforma     ← NUEVO
├── Configuración SaaS
└── (footer account) Mi perfil / Cambiar contraseña
```

Separar **Mi perfil** (cuenta Ribersoft) de **Configuración SaaS** (parámetros producto).

---

## 8. Wizard onboarding — gap UX

### Flujo actual (`platform/setup.vue`)

Superadmin completa formulario → tenant creado → mensaje éxito → puede entrar al tenant.

### Lo que el usuario espera vs realidad

| Expectativa | Realidad |
|-------------|----------|
| Tenant listo igual que demo | Permisos recortados |
| Configurar impresoras | Menú invisible |
| Cobrar / comandar | Posible si crea productos manualmente |
| Métodos pago QR/efectivo | Vacíos sin bootstrap |
| Mesas garzón | Vacías sin bootstrap |

### Mejora UX V1 (sin confundir)

Tras wizard exitoso, mostrar **checklist onboarding**:

1. ✅ Empresa y sucursal creadas
2. ⚙️ Configurar impresoras (link directo — requiere fix permisos backend)
3. 📦 Cargar catálogo (bootstrap o manual)
4. 👥 Crear cajera/garzón
5. 🖨️ Instalar Print Agent

---

## 9. Propuesta implementación frontend V1

### Prioridad P0

1. **`pages/account/profile.vue`** (o modal)
   - Ver datos usuario
   - Cambiar contraseña (form → `PATCH /auth/me/password`)
   - Cambiar PIN (form → `PATCH /auth/me/pin`)
2. **UserProfile.vue** — "Mi perfil" → ruta perfil real
3. **`platform/users/*`** — gestión superadmins (cuando exista API)
4. **Post-wizard checklist** component

### Prioridad P1

5. Implementar `settings/security` con políticas lectura + link cambio credenciales
6. **`platform/audit`** — logs acciones plataforma
7. Banner tenant suspendido / plan límite (cuando backend enforce)

### V2

- 2FA setup UI
- Forgot password flow conectado
- Branding tenant

---

## 10. Seguridad UX mínima V1

| Requisito | Frontend |
|-----------|----------|
| Confirmar contraseña actual | Form perfil |
| No mostrar PIN | Inputs password type; nunca persistir |
| Logout tras cambio password | Llamar logout tras PATCH exitoso |
| No depender seed | Documentar CLI create-superadmin en onboarding Ribersoft |
| Permisos actualizados | Forzar logout/login tras fix backend (documentado) |

---

## 11. Archivos clave

| Archivo | Rol |
|---------|-----|
| `navigation/vertical/nightpos-r4.js` | Menú + permisos CASL |
| `pages/nightpos/settings/printers/index.vue` | UI impresoras |
| `pages/nightpos/platform/setup.vue` | Wizard SaaS |
| `layouts/components/UserProfile.vue` | Account menu |
| `pages/nightpos/settings/security/index.vue` | Placeholder |
| `pages/nightpos/users/[id]/edit.vue` | Reset admin tenant |
| `stores/auth.js` | Permisos congelados en cookie |

---

## 12. Validación manual recomendada (post-fix)

1. Crear tenant vía wizard
2. Login como admin nuevo
3. DevTools → `/auth/me` → debe incluir `settings.printers`
4. Menú Configuración → Impresoras visible
5. Registrar device_key → agente imprime
6. Superadmin → Mi perfil → cambiar contraseña → re-login

---

## 13. No romper (constraints)

- Login PIN cajera/garzón — intacto
- Seeders demo — intactos
- Tenants existentes — migración permisos idempotente backend, no frontend hack
- Web operativa — perfil es adición, no reemplazo rutas
