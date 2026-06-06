# PHASE_16_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend  
**Fase:** 16 — Módulo Finanzas → Liquidaciones  
**Fecha:** 2026-06-02

> **Actualización 2026-06-08:** Registrar pieza pide total, monto chica y monto casa. Ver `ROOM_SERVICE_PRICING_MODEL_FIX_REPORT.md`.

> **Actualización 2026-06-08 (caja):** Ver `SERVICES_CASH_ACCOUNTING_FIX_REPORT.md` — gate de caja + `payment_method` en crear manilla/pieza/show.

> **Nota:** En liquidaciones de chicas, piezas y shows no intervienen garzones; solo consumos/ventas generan comisión de garzón.

---

## 1. Rutas y páginas

| Subpestaña | Archivo | Ruta | Nombre ruta |
| ---------- | ------- | ---- | ----------- |
| Resumen | `pages/nightpos/settlements/index.vue` | `/nightpos/settlements` | `nightpos-settlements` |
| Garzones | `pages/nightpos/settlements/waiters.vue` | `/nightpos/settlements/waiters` | `nightpos-settlements-waiters` |
| Chicas | `pages/nightpos/settlements/girls.vue` | `/nightpos/settlements/girls` | `nightpos-settlements-girls` |
| Historial | `pages/nightpos/settlements/history.vue` | `/nightpos/settlements/history` | `nightpos-settlements-history` |
| Detalle | `pages/nightpos/settlements/[id].vue` | `/nightpos/settlements/:id` | `nightpos-settlements-id` |

Navegación: `navigation/vertical/nightpos-r4.js` → Finanzas → Liquidaciones.

---

## 2. API cliente

`src/api/settlements.js`:

- `fetchCurrentShiftSettlements`
- `generateCurrentShiftSettlements`
- `fetchSettlement`
- `markSettlementPaid`
- `fetchSettlementHistory(params)` — filtros query alineados con backend

---

## 3. Composables

| Archivo | Uso |
| ------- | --- |
| `useSettlementSectionTabs.js` | Tabs Resumen / Garzones / Chicas / Historial |
| `useCurrentShiftSettlements.js` | Carga turno, resumen, tablas garzones/chicas |
| `useNightPosPermissions.js` | `settlements.access`, `generate`, `pay`, `history` |

---

## 4. UI por sección

### Resumen

- `NightPosPageHeader` + breadcrumbs
- `NightPosSectionTabs`
- Cards (`CardStatisticsVertical`): garzones, chicas, consumos, manillas, piezas, shows, pendiente, pagado
- Botón «Generar liquidaciones del turno actual» (`settlements.generate`)

### Garzones / Chicas

- Tablas `VDataTable` con chips de estado y enlace a detalle
- Chicas: columnas consumos, manillas, piezas, shows

### Historial

- Filtros: fechas, turno (`fetchShifts`), ID personal, tipo, estado
- Tabla: fecha, turno, personal, tipo, total, estado, pagado por, fecha pago

### Detalle

- Cabecera con acción «Marcar pagado» (`VDialog` confirmación)
- Tabla dinámica: garzón (venta, comanda, base, %, comisión) vs chica (fuente, descripción, monto, hora)
- Etiquetas `GIRL_CONSUMPTION`, `GIRL_BRACELET`, `GIRL_ROOM`, `GIRL_SHOW`

---

## 5. Componentes Materialize

- `CardStatisticsVertical` (resumen)
- `VDataTable`, `VTabs` / `NightPosSectionTabs`
- `VChip`, `VBadge` (estado/tipo)
- `VDialog` (solo confirmar pago)
- `NightPosPageHeader`, breadcrumbs
- `VAlert`, `VProgressLinear`, `VCard`, `VRow`/`VCol`

Sin modales para vistas principales (detalle en página dedicada).

---

## 6. Permisos en UI

- Resumen / Garzones / Chicas: `settlements.access`
- Generar: `settlements.generate`
- Historial: `settlements.history`
- Pagar: `settlements.pay` en detalle

---

## 7. Validación manual

Con `pnpm run dev`:

1. Login admin → abrir caja → venta CON_ACOMPANANTE → cobrar
2. Registrar manilla, pieza, show
3. Finanzas → Liquidaciones → Generar
4. Revisar subpestañas y detalle
5. Marcar pagado y revisar historial con filtros
6. Consola sin errores críticos

---

## 8. Próxima fase recomendada

- Selector de chica/garzón en filtros de historial (lista desde API admin)
- Impresión / PDF de liquidación individual
- Integración con reportes financieros (Fase 17)
