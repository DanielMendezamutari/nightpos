# PHASE_15_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Fase:** 15 — Servicios (manillas, piezas, shows)  
**Fecha:** 2026-06-04  
**Referencias:** `FRONTEND_GUIDELINES.md`, `backend/PHASE_15_REPORT.md`

---

## 1. Módulo `/nightpos/services`

| Ruta | Vista | Permiso |
| ---- | ----- | ------- |
| `/nightpos/services` | Redirige a manillas | `bracelets.access` |
| `/nightpos/services/bracelets` | Listado + KPIs | `bracelets.access` |
| `/nightpos/services/bracelets/create` | Registrar manillas | `bracelets.create` |
| `/nightpos/services/room-services` | Listado piezas | `room_services.access` |
| `/nightpos/services/room-services/create` | Registrar pieza | `room_services.create` |
| `/nightpos/services/shows` | Listado shows | `shows.access` |
| `/nightpos/services/shows/create` | Registrar show | `shows.create` |

Subpestañas: `NightPosSectionTabs` + `useFilteredServiceTabs()`.

---

## 2. API cliente

- `src/api/bracelets.js`
- `src/api/roomServices.js`
- `src/api/shows.js`

---

## 3. Componentes Materialize

- `NightPosPageHeader`, `NightPosSectionTabs`, `NightPosFormActions`
- `VCard`, `VDataTable`, `VChip`, `VAlert`, `VSelect`, `VTextField`, `VTextarea`
- `CardStatisticsVertical` (KPIs por turno)

Formularios en **páginas completas** (sin modales grandes).

---

## 4. Navegación

`nightpos-r4.js` → Operación → **Servicios** (Manillas, Piezas, Shows).

---

## 5. Personal (chicas / garzones)

`useGirlIncomeStaffOptions()` carga usuarios admin y filtra `staff_role` GIRL / WAITER para selects.

Chica demo: **Chica Demo** (`chica.centro`).

---

## 6. Turno

Alertas muestran turno actual (o mensaje de auto-creación). Chip **Auto** si `shift.auto_created`.

---

## 7. Preparación liquidaciones

Tarjetas en `/nightpos/settlements` actualizadas (piezas/shows ya no dicen «módulo futuro»). Totales en resumen API siguen en `0.00` hasta integración backend Fase 16.

---

## 8. Validación en dev

```bash
cd frontend && pnpm run dev
```

1. Login `admin.demo` / `AdminDemo123!` (tenant `casa-demo`, sucursal CENTRO).
2. Operación → Servicios → Manillas → Registrar manillas (chica + cantidad + precio).
3. Piezas → Registrar pieza (habitación opcional).
4. Shows → Registrar show (tipo + precio).
5. Ver historial y turno en alerta superior.
6. Consola sin errores críticos.

---

## 9. Próxima fase recomendada

Liquidaciones UI con desglose por fuente (consumo, manilla manual, pieza, show) y enlace desde tarjetas de servicios.

Detener implementación hasta nuevas instrucciones.
