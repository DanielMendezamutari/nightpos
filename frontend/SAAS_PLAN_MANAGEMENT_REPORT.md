# SAAS_PLAN_MANAGEMENT_REPORT.md (Frontend)

**Fase:** SAAS-1 — Planes y límites  
**Fecha:** 2026-06-14  
**Estado:** Completado (UI informativa, sin enforcement)

---

## 1. Rutas actualizadas

| Ruta | Cambio |
|------|--------|
| `/nightpos/platform/dashboard` | Cards: activas, suspendidas, vencidas, trial, total + top planes |
| `/nightpos/platform/plans` | Tabla CRUD planes (antes placeholder) |
| `/nightpos/platform/tenants/create` | Formulario completo: empresa + sucursal + admin |
| `/nightpos/platform/tenants/:id` | Plan actual + uso vs límites |
| `/nightpos/platform/tenants/:id/edit` | Selector `plan_id` desde catálogo |

---

## 2. API cliente

| Módulo | Funciones |
|--------|-----------|
| `src/api/plans.js` | `fetchPlatformPlans`, CRUD, límites, duplicar |
| `src/api/platform.js` | `fetchPlatformDashboard` |
| `src/api/tenants.js` | Sin cambios de firma; payload ampliado en create |

---

## 3. Componentes

| Componente | Uso |
|------------|-----|
| `TenantFormFields.vue` | Selector de plan (`plan_id`) cuando `showPlanSelect` |
| `TenantProvisionFields.vue` | Sucursal inicial + administrador (crear empresa) |

---

## 4. UI Planes (`platform/plans/index.vue`)

Tabla con columnas: Nombre, Código, Precios, Estado, Tenants usando plan.

Acciones:
- Editar
- Límites (diálogo con claves estándar, -1 = ilimitado)
- Duplicar
- Desactivar / Eliminar

---

## 5. Uso vs límites en ficha empresa

Muestra filas `key: current / limit` con chip de estado:
- `OK` (verde)
- `WARNING` (amarillo)
- `LIMIT_REACHED` (rojo)

Solo visual — no bloquea operación.

---

## 6. Crear empresa

Ya no permite tenant vacío. Requiere los mismos datos que el wizard:
- Datos tenant + plan
- Sucursal inicial
- Administrador (password + PIN opcional)

Redirige al detalle con `data.tenant.id`.

---

## 7. Qué sigue — SAAS-2

Pantallas de suscripción, estados de ciclo de vida y renovación. No iniciado.
