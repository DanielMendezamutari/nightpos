# PHASE_14_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Fase:** 14 — Liquidaciones  
**Fecha:** 2026-06-02

---

## 1. Módulo creado

Ruta base: `/nightpos/settlements`

| Pantalla | Archivo | Ruta |
| -------- | ------- | ---- |
| Resumen + tablas | `pages/nightpos/settlements/index.vue` | `nightpos-settlements` |
| Historial | `pages/nightpos/settlements/history.vue` | `nightpos-settlements-history` |
| Detalle | `pages/nightpos/settlements/[id].vue` | `nightpos-settlements-id` |

API: `src/api/settlements.js`  
Tabs: `src/composables/useSettlementSectionTabs.js`

---

## 2. Menú

**Finanzas → Liquidaciones**

- Resumen turno (`settlements.access`)
- Historial (`settlements.history`)

Archivo: `navigation/vertical/nightpos-r4.js`

---

## 3. Componentes Materialize

| Componente | Uso |
| ---------- | --- |
| `NightPosPageHeader` | Título, breadcrumbs, acción generar |
| `NightPosSectionTabs` | Resumen / Historial |
| `CardStatisticsVertical` | 7 KPIs (garzones, chicas, manillas, piezas, shows, pendiente, pagado) |
| `VDataTable` | Tablas garzones, chicas, historial, detalle |
| `VChip` / badges | Estado PENDING / PAID |
| `VTabs` / `VWindow` | Pestañas Garzones / Chicas |
| `VDialog` | Confirmar pago (solo en detalle) |
| `VAlert` | Sin turno abierto |
| `VCard` | Bloques resumen y detalle |

Sin formularios sueltos; sin eliminar demos Materialize.

---

## 4. Acciones

- **Generar liquidaciones del turno actual** — botón en header (`settlements.generate`).
- **Ver detalle** — navegación a `[id]`.
- **Marcar pagado** — diálogo en detalle (`settlements.pay`).

`useOnContextChange` recarga datos al cambiar empresa/sucursal.

---

## 5. Permisos frontend

`useNightPosPermissions.js`:

- `canAccessSettlements`
- `canGenerateSettlements`
- `canPaySettlements`
- `canListSettlementHistory`

Rutas: `definePage({ meta: { permission: '...' } })`.

---

## 6. Validación manual (`pnpm run dev`)

1. Login `admin.demo` / `AdminDemo123!` con `casa-demo` + `CENTRO`.
2. Abrir turno y caja.
3. Comanda SOLO_CLIENTE (garzón) y CON_ACOMPANANTE con chica → cobrar.
4. **Finanzas → Liquidaciones → Resumen turno**.
5. **Generar turno actual** → tablas garzón (5%, comisión) y chica (manilla).
6. **Ver detalle** → líneas con producto, modalidad, montos snapshot.
7. **Marcar pagado** → estado PAID.
8. **Historial** → listado de liquidaciones.
9. Consola sin errores críticos.

---

## 7. Pendientes UI

- Columnas descuentos/bonos (cuando existan en API).
- Filtros por fecha en historial.
- Impresión / export PDF.
- Acceso garzón/chica solo lectura de su fila (depende de login PIN y permiso `settlements.access`).

---

## 8. Próxima fase recomendada

Integrar totales de liquidación en pantalla de **cierre de turno** (cards pagos chicas/garzones según `FRONTEND_GUIDELINES.md`).
