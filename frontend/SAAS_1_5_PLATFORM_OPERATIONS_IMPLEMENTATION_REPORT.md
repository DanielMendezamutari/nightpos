# SAAS-1.5 — Platform Operations / Ribersoft Control Center — Implementation Report (Frontend)

**Fecha:** 2026-06-25  
**Estado:** ✅ Implementado (V1)

---

## Ruta principal

`/nightpos/platform/control-center`

**Permiso página:** `platform.operations.view`

---

## Pantallas

| Ruta | Archivo | Descripción |
|------|---------|-------------|
| Control Center dashboard | `platform/control-center/index.vue` | Cards operativos, clientes con problemas, versiones |
| Clientes operativos | `platform/control-center/tenants/index.vue` | Tabla con filtros (estado, health, agente, ventas, búsqueda) |
| Detalle cliente | `platform/control-center/tenants/[id].vue` | Tabs: Resumen, Sucursales, Agentes, Checklist, Perfil técnico, Incidencias |
| Agentes impresión | `platform/control-center/agents/index.vue` | Listado global print devices |

---

## API client

`src/api/platformOperations.js` — wrappers para todos los endpoints `/admin/platform/operations/*`.

---

## Navegación

- Menú **Plataforma SaaS** → **Control Center** (`nightpos-r4.js`)
- Tabs sección plataforma → **Control Center** (`useStaffSectionTabs.js` → `PLATFORM_SECTION_TABS`)

Orden tabs: Dashboard · **Control Center** · Empresas · Sucursales · Planes · Config

---

## Build y acceso

```bash
cd frontend && npm run build
```

Tras migrar backend, **logout/login superadmin** para refrescar permisos (`platform.operations.view`).

### Bugfix migración + 404 (2026-06-25)

- Migración: índice `tenant_ops_chk_uq` (MySQL identifier too long).
- Menú Control Center: sin CASL extra (igual que Dashboard SaaS; sección ya es superadmin).
- Router guard: bypass superadmin + fallback `admin.tenants.list` en meta de páginas.
- Enlaces detalle tenant: ruta `nightpos-platform-control-center-tenants-id` (file-based router).

---

## UI V1

- Cards: clientes activos, online/offline/warning, impresoras, ventas hoy, tickets, comandas, errores críticos
- Panel clientes con problemas con enlace a detalle
- Detalle editable: checklist (PATCH) y perfil técnico (PUT, sin passwords)
- Estados con chips de color por `operational_status`

---

## Versiones frontend

Usar `VITE_APP_VERSION` en `.env` (mostrado vía API dashboard → `versions.frontend_version`).

---

## Siguiente paso

QA manual con tenants reales + agentes conectados. No iniciar SAAS-2 Billing hasta validar Control Center en producción interna Ribersoft.
