# PHASE_R4_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Reorganización modular del frontend  
**Fase:** R4 — Módulos, submenús y layouts Materialize  
**Fecha:** 2026-06-03  
**Referencias:** `PHASE_R3_REPORT.md`, `FRONTEND_MODAL_AUDIT.md`, Materialize vertical nav (Ecommerce)

---

## 1. Nuevo árbol de navegación

Archivo: `src/navigation/vertical/nightpos-r4.js`  
Filtrado dinámico: `src/composables/useNightPosNavItems.js`

```
Plataforma SaaS (superadmin)
└── Plataforma SaaS
    ├── Dashboard SaaS
    ├── Empresas → Listado / Crear
    ├── Sucursales → Listado / Crear
    ├── Planes / Suscripciones
    └── Configuración SaaS

Operación (contexto tenant)
└── Operación NightPOS
    ├── Dashboard operativo
    ├── Comandas → Listado / Nueva
    ├── Caja, Ventas, Turnos

Catálogo
└── Productos, Categorías, Precios, Config. precios

Personal
└── Usuarios, Garzones, Cajeras, Chicas, Roles

Finanzas
└── Caja actual, Ventas, Movimientos, Cierres, Reportes

Configuración
└── Sucursal, Pagos, Impresoras, Preferencias, Seguridad
```

**Reglas de visibilidad:**

- `requiresSuperAdmin` — solo rol `super_admin`
- `requiresOperationalContext` — superadmin necesita cookie `tenantSlug`; demás roles con `tenant_id` siempre operan
- `action` + `subject` — CASL / permisos API

`DefaultLayoutWithVerticalNav.vue` usa `navItems` computado (no el menú plano anterior).

---

## 2. Módulos creados

| Módulo | Descripción |
| ------ | ----------- |
| Plataforma SaaS | Multi-tenant global |
| Operación NightPOS | Dashboard, comandas, caja, ventas |
| Catálogo | Productos, categorías, precios |
| Personal | Usuarios y filtros por rol |
| Finanzas | Caja, ventas, reportes |
| Configuración | Sucursal y preferencias |

---

## 3. Subpestañas creadas

**Menú lateral:** grupos anidados `children` (patrón Ecommerce Materialize).

**Tabs horizontales (módulo):** `NightPosSectionTabs.vue` + `useStaffSectionTabs.js`:

- `PLATFORM_SECTION_TABS`
- `CATALOG_SECTION_TABS`
- `STAFF_SECTION_TABS`

Presentes en listados principales y dashboard SaaS.

---

## 4. Vistas reconstruidas / nuevas

| Vista | Tipo |
| ----- | ---- |
| `platform/dashboard.vue` | Dashboard SaaS + KPIs |
| `platform/plans`, `platform/settings` | Placeholder |
| `staff/waiters`, `cashiers`, `girls` | Listado filtrado (`UsersListPanel`) |
| `staff/roles` | Placeholder RBAC |
| `catalog/prices` | Índice → enlace a precios por producto |
| `catalog/prices-config` | Placeholder reglas |
| `operation/shifts` | Placeholder turnos |
| `finance/*` | Movimientos, cierres, reportes |
| `settings/*` | Sucursal actual + placeholders |
| `users/[id]/edit` | **Tabs** Materialize (personal, acceso, comisión, seguridad) |
| `platform/tenants/create` | **Layout 2 columnas** `NightPosFormPageLayout` |

Vistas R3 (create/edit/detail) conservadas y enlazadas desde menú y tabs.

---

## 5. Vistas preparadas (backend pendiente)

| Vista | Motivo |
| ----- | ------ |
| Planes / Suscripciones | Sin API billing |
| Config SaaS global | Sin API settings plataforma |
| Roles y permisos | Sin CRUD roles |
| Config. precios global | Reglas por producto vía API actual |
| Turnos / Cierre turno | Sin API turnos |
| Reportes financieros | Permiso existe; endpoints reportes pendientes |
| Métodos pago, impresoras, preferencias, seguridad | Config tenant pendiente |
| Editar empresa/sucursal/categoría (guardar) | Sin PUT (vistas edit con aviso R3) |

Componente: `NightPosModulePlaceholder.vue`

---

## 6. Componentes Materialize reutilizados

| Componente | Uso |
| ---------- | --- |
| Vertical nav groups + children | Menú R4 |
| `VTabs` / `VWindow` | Edición usuario, tabs módulo |
| `VCard`, `VRow`, `VCol` | Layouts y formularios |
| `VDataTable` | Listados |
| `VBreadcrumbs` | `NightPosPageHeader` |
| `CardStatisticsVertical` | Dashboards y listas |
| `VList`, `VAlert`, `VChip` | Detalle y placeholders |

**Wrappers NightPOS nuevos:**

- `NightPosFormPageLayout` — formulario + aside
- `NightPosSectionTabs` — subpestañas de módulo
- `NightPosContextCards` — empresa / sucursal / sesión
- `NightPosModulePlaceholder` — próximamente
- `UsersListPanel` — listado personal reutilizable

---

## 7. Modales que se mantienen

Sin cambios respecto a R3: reset PIN/password, confirmar desactivar usuario, cobro comanda, caja, detalle venta, selector contexto, etc. Ver `FRONTEND_MODAL_AUDIT.md`.

---

## 8. Validación en modo dev

| Rol | Esperado |
| --- | -------- |
| Superadmin sin tenant | Menú Plataforma SaaS; sin Operación/Catálogo |
| Superadmin con tenant elegido | Todos los módulos operativos |
| Admin tenant | Operación, Catálogo, Personal, Finanzas (sin Empresas globales) |
| Cajera | Caja, Ventas (permisos CASL) |
| Garzón | Comandas |

Comando: `pnpm run dev`

---

## 9. Próxima fase recomendada

1. **Backend:** `PUT` tenants, branches, categories; API reportes y turnos.  
2. **Frontend R5:** Cierre de caja y turno como **vistas completas** (no modal).  
3. **RBAC UI:** pantalla roles cuando exista API.  
4. **Homogeneizar** `users/create` y `products/create` con `NightPosFormPageLayout` + tabs.

---

*Demos Materialize (`apps-and-pages`, `charts`, etc.) permanecen en repo; no se importan en `navigation/vertical/index.js`.*
