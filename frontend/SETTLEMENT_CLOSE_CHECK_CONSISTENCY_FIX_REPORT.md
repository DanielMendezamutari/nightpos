# Settlement / Close-Check Consistency Fix (Frontend)

**Fecha:** 2026-06-17  
**Estado:** ✅ Implementado  
**Backend:** `backend/SETTLEMENT_CLOSE_CHECK_CONSISTENCY_FIX_REPORT.md`

---

## Problema UX

Tras close-check con «pagos pendientes», la pantalla Liquidaciones mostraba:

> No hay liquidaciones para este turno/caja.

aunque existían liquidaciones PENDING que bloqueaban el cierre.

---

## Cambios

### `settlements/index.vue`

| Antes | Ahora |
|-------|-------|
| `summaryHasData` ignoraba `total_pending` | Incluye `total_pending` y `settlement_summary` |
| Generate siempre: «Liquidaciones generadas (N líneas)» | Si `created_items=0` y hay PENDING → «No hay nuevas… Tienes pagos pendientes por pagar.» |
| Un solo alert «no hay liquidaciones» | Alert separado si **hay pendientes** vs **sin datos** |

Nuevo alert cuando `hasPendingPayments`:

> Hay liquidaciones pendientes de pago por **X BOB**. Vaya a Garzones, Chicas o Limpieza para pagarlas.

### `cash/index.vue` (diálogo cierre)

Cada blocker del close-check muestra botón **Ir** si trae `route` (ej. `nightpos-settlements-girls`).

---

## API consumida (sin cambios de rutas)

- `GET /cash/session/current/close-check` — blockers con `type`, `route`
- `GET /settlements/current-shift` — `settlement_summary`, `context.empty_overview`
- `POST /settlements/generate-current-shift` — mensaje diferenciado + `settlement_summary`

---

## Validación manual

| # | Paso | Esperado |
|---|------|----------|
| 1 | Cobrar venta con chica | OK |
| 2 | Generar liquidaciones | Creadas |
| 3 | No pagar, intentar cerrar caja | Blocker pagos pendientes + botón Ir |
| 4 | Liquidaciones | Alert pendientes + lista en tabs |
| 5 | Generar otra vez | Snackbar «no hay nuevas… pendientes» |
| 6 | Pagar | Close-check sin blocker liquidaciones |

---

## Archivos

| Archivo |
|---------|
| `src/pages/nightpos/settlements/index.vue` |
| `src/pages/nightpos/cash/index.vue` |
