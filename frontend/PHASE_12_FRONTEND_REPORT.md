# PHASE_12_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Fase:** 12 — Conexión de edición empresas, sucursales y categorías  
**Fecha:** 2026-06-02  
**Referencias:** `FRONTEND_GUIDELINES.md`, `PHASE_R4_REPORT.md`, `backend/PHASE_12_REPORT.md`

---

## 1. APIs del cliente

| Archivo | Funciones nuevas |
| ------- | ---------------- |
| `src/api/tenants.js` | `fetchAdminTenant(id)`, `updateAdminTenant(id, payload)` |
| `src/api/branches.js` | `fetchAdminBranch(id, tenantSlug?)`, `updateAdminBranch(id, payload, tenantSlug?)` |
| `src/api/categories.js` | `fetchCategory(id)`, `updateCategory(id, payload)` |

Todas usan `unwrapNightPosResponse` y el interceptor HTTP existente (token, tenant, branch).

---

## 2. Vistas conectadas

| Ruta | Archivo | Cambio |
| ---- | ------- | ------ |
| `/nightpos/platform/tenants/:id/edit` | `platform/tenants/[id]/edit.vue` | GET/PUT real; sin aviso “pendiente backend” |
| `/nightpos/platform/branches/:id/edit` | `platform/branches/[id]/edit.vue` | GET/PUT con `usePlatformContext().tenantSlug` |
| `/nightpos/categories/:id/edit` | `categories/[id]/edit.vue` | GET/PUT; permiso `products.update` |

**Layout (Materialize / R4):**

- `NightPosPageHeader` + breadcrumbs
- `NightPosFormPageLayout` + card de formulario
- `NightPosFormActions` (Guardar / Cancelar, estados `loading` / `saving`)
- `VSnackbar` vía `useNightPosNotify`
- Formularios: `TenantFormFields`, `BranchFormFields`, `CategoryFormFields`

**Sin modales** para estas acciones (coherente con R3/R4).

---

## 3. Permisos en frontend

| Vista | `definePage` meta |
| ----- | ----------------- |
| Editar empresa | `admin.tenants.list` (solo superadmin en menú) |
| Editar sucursal | `admin.branches.list` |
| Editar categoría | `products.update` + redirect si `!canUpdateProduct` |

Errores 403/404/422 se muestran con `getApiErrorMessage`.

---

## 4. Validación manual (dev)

```bash
cd frontend && pnpm run dev
```

| Flujo | Usuario sugerido | Verificar |
| ----- | ---------------- | --------- |
| Editar empresa | `superadmin` / `SuperAdmin123!` | Carga fechas de suscripción; guardar; slug duplicado muestra error |
| Editar sucursal | superadmin con contexto `casa-demo` o `admin.demo` | Dirección y código; guardar |
| Editar categoría | `admin.demo` | Cambio de nombre; guardar vuelve al listado |
| Permisos | `cajero.demo` PIN `1234` | No debe entrar a edición de categoría (ruta protegida) |
| Consola | — | Sin errores críticos en red o Vue |

---

## 5. Qué queda pendiente

- Páginas detalle (`tenants/:id`, `branches/:id`) pueden seguir mostrando datos de listado; opcional refrescar tras editar.
- Módulos placeholder R4 (planes, finanzas, config SaaS) sin API.
- Tests E2E automatizados en frontend (no solicitados en fase 12).

---

## 6. Próxima fase recomendada

Conectar **detalle** y **listados** con invalidación de caché tras guardar, o avanzar **Fase 13** en módulos Plataforma (planes reales) según prioridad de producto.

Detener implementación hasta nuevas instrucciones.
