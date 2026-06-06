# PHASE_13_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Fase:** 13 — Turnos oficiales (UI)  
**Fecha:** 2026-06-02  
**Referencias:** `FRONTEND_GUIDELINES.md`, `PHASE_R4_REPORT.md`, `backend/PHASE_13_REPORT.md`

---

## 1. Módulo `/nightpos/shifts`

| Ruta | Vista | Permiso |
| ---- | ----- | ------- |
| `/nightpos/shifts` | Redirige a turno actual | `shifts.access` |
| `/nightpos/shifts/current` | Turno actual (cards + detalle) | `shifts.access` |
| `/nightpos/shifts/open` | Abrir turno (página completa) | `shifts.open` |
| `/nightpos/shifts/history` | Tabla historial | `shifts.list` |
| `/nightpos/shifts/close` | Cierre con KPI cards + formulario | `shifts.close` |

Subpestañas: `NightPosSectionTabs` + `useFilteredShiftTabs()` (`composables/useShiftSectionTabs.js`).

Rutas legacy redirigen: `operation/shifts`, `finance/shift-close` → módulo nuevo.

---

## 2. API cliente

`src/api/shifts.js`: `fetchCurrentShift`, `fetchShifts`, `fetchShift`, `fetchShiftSummary`, `openShift`, `closeShift`.

---

## 3. Componentes Materialize

- `NightPosPageHeader`, `NightPosSectionTabs`, `NightPosFormPageLayout`, `NightPosFormActions`
- `VCard`, `VDataTable`, `VChip`, `VAlert`, `VSnackbar`
- KPI cards en cierre (efectivo, QR, tarjeta, ventas, ingresos/egresos, esperado)

---

## 4. Navbar y dashboard

- **Navbar** (`NightPosNavbarContext.vue`): chip turno (tipo + fecha; sufijo `(auto)` si `auto_created`; o “Sin turno abierto” hasta primera operación).
- **Dashboard** (`useDashboardOperationalStats.js` + `dashboard.vue`): tarjeta “Turno actual” con datos de API.

---

## 5. Navegación

`nightpos-r4.js`: menú Turnos con subítems; Finanzas → Cierre de turno apunta a `nightpos-shifts-close`.

---

## 6. Validación en dev

```bash
cd frontend && pnpm run dev
```

1. Login cajera PIN `1234` o admin `admin.demo` / `AdminDemo123!` (tenant `casa-demo`).
2. Caja → abrir sesión (crea turno auto si no hay).
3. Comanda → crear y cobrar.
4. Opcional admin: Turnos → Abrir turno manual (DAY o NIGHT).
5. Ventas → ver venta con turno (vía API / listado).
6. Turnos → Cierre (efectivo contado).
7. Navbar y dashboard muestran turno.
8. Consola sin errores críticos.

---

## 7. Qué queda pendiente

- Enlace directo desde tarjeta dashboard a `/nightpos/shifts/current`.
- Filtro `current_shift` en listados de comandas/ventas en UI.
- Impresión / export PDF del cierre (`FRONTEND_GUIDELINES.md`).
- Campos liquidación chicas/garzones cuando exista backend.
- Detalle modo reportes: `SHIFT_REPORTING_MODE_REPORT.md`.

---

## 8. Próxima fase recomendada

Pantallas de reportes por turno y refinamiento del módulo Finanzas según cierre oficial.

Detener implementación hasta nuevas instrucciones.
