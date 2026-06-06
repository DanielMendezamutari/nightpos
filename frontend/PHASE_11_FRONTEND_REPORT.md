# PHASE_11_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Consola plataforma (frontend)  
**Fase:** 11 — UI superadmin: empresas, sucursales, contexto operativo  
**Fecha:** 2026-06-03  
**Referencias:** `PHASE_5_REPORT.md`, `PHASE_10_FRONTEND_REPORT.md`, backend `admin/tenants` y `admin/branches`

---

## 1. Pantallas creadas

| Ruta | Archivo | Permiso meta |
| ---- | ------- | ------------- |
| `/nightpos/platform/tenants` | `src/pages/nightpos/platform/tenants/index.vue` | `admin.tenants.list` |
| `/nightpos/platform/branches` | `src/pages/nightpos/platform/branches/index.vue` | `admin.branches.list` |

**Menú:** sección «Plataforma SaaS» en `src/navigation/vertical/platform.js` (antes del bloque operativo).

---

## 2. Funcionalidad UI

### Empresas (SaaS)

- Tabla: nombre, slug, estado, plan.
- Tarjetas resumen: total, activas, suspendidas.
- Alta de empresa: nombre, slug, estado, plan, fechas de suscripción opcionales.
- Botón **Operar**: fija cookie `tenantSlug` y refresca contexto (superadmin).

### Sucursales

- Listado y alta por empresa en contexto (`X-Tenant-Slug` / cookie).
- Superadmin sin empresa: aviso + selector de contexto.
- **Operar aquí**: fija `tenantSlug` + `branchCode`.

### Contexto operativo (navbar)

- `PlatformContextSelector.vue`: elige empresa/sucursal o vuelve a modo global.
- `usePlatformContext.js`: cookies 30 días + `operational.refreshContext()`.

### Dashboard superadmin global

- Accesos rápidos a Empresas / Sucursales.
- Operación (caja, productos) deshabilitada hasta elegir empresa.

---

## 3. API frontend

| Archivo | Métodos |
| ------- | ------- |
| `src/api/tenants.js` | `fetchAdminTenants`, `createAdminTenant` |
| `src/api/branches.js` | `fetchAdminBranches(tenantSlug?)`, `createAdminBranch` |

`useNightPosPermissions`: `canListAdminTenants`, `canCreateAdminTenant`, `canCreateAdminBranch`.

---

## 4. Pruebas backend (Fase 11)

`tests/Feature/Api/V1/AdminPlatformApiTest.php`:

- Crear tenant como superadmin.
- Crear y listar sucursal con header `X-Tenant-Slug`.

---

## 5. Qué queda pendiente (próximas fases)

| Ítem | Notas |
| ---- | ----- |
| Editar / suspender empresa | Solo create/list en API |
| Editar sucursales | Solo create/list |
| UI planes y facturación | Campos en create tenant, sin módulo billing |
| Selector de contexto para owner | Owner ya tiene tenant fijo; sucursal vía login PIN |
| Reportes SaaS | `reports.access` sin pantalla |
| Suscripción solo lectura | Middleware 5.1 pendiente |

---

## 6. Uso recomendado (superadmin)

1. Login **Usuario / contraseña** → `superadmin` / `SuperAdmin123!`
2. Menú **Plataforma SaaS → Empresas** (listar / crear).
3. **Elegir empresa** en barra superior → opcional sucursal.
4. Menú operativo (Caja, Comandas, …) con contexto del local.

---

*Fase 11 frontend completada. Backend admin de Fase 5 reutilizado sin cambios de contrato.*
