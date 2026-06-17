# Cajera alta presión — Fase 0 (Frontend)

**Fecha:** 2026-06-16  
**Estado:** ✅ Implementado  
**Auditoría base:** `CASHIER_HIGH_PRESSURE_OPERATION_AUDIT.md`  
**Par backend:** `backend/CASHIER_HIGH_PRESSURE_PHASE0_REPORT.md`

---

## Objetivo Fase 0

Quick wins operativos **sin rediseñar layout cajera** ni quitar el flujo actual de cobro vía detalle.

---

## Cambios implementados

### 1. Cola de cobro (`/nightpos/cashier/orders`)

- Tiempo esperando: «X min esperando» (warning si ≥ 10 min).
- Chips: Acompañante, Combo, Falta manilla, Falta chica, No cobrable.
- Botón **Cobrar** deshabilitado si `charge_blocked`.
- Toast si intenta cobrar bloqueada.
- Composable `useCashierOrderQueue.js`.

### 2. Enter / Esc en modales

Composable `useDialogKeyboardShortcuts.js`:

| Componente | Enter | Esc |
|------------|-------|-----|
| `ChargeOrderModal` | Confirmar cobro | Cerrar |
| `QuickOpenCashDialog` | Abrir caja | Cerrar |
| `CashMovementDialog` | Registrar | Cerrar |
| `SettlementPayDialog` | Confirmar pago | Cerrar |
| `cash/index.vue` — Abrir caja | Abrir | Cerrar |
| `cash/index.vue` — Cerrar caja | Confirmar cierre | Cerrar |

- No confirma si `loading` activo.
- Enter ignorado en `<textarea>`.

### 3. `?open=1` en Mi caja

- `/nightpos/cash?open=1` abre diálogo de apertura si no hay sesión.
- Query `open` se limpia al mostrar el modal (no repite al recargar).

### 4. Ventas del turno (`/nightpos/sales`)

- SSE: `sale.created`, `direct_sale.created`.
- Polling fallback 30 s (`useOperationalPollingFallback`).
- Banner `NightPosSseBanner`.
- Botón **Reimprimir última venta** → `nightpos-print-sale-id`.

### 5. No implementado (Fase 1)

- Shell cajera / bottom nav
- Cobro inline desde card
- Pago por chips avanzado
- Vista única liquidaciones

---

## Archivos tocados

| Archivo |
|---------|
| `src/pages/nightpos/cashier/orders/index.vue` |
| `src/composables/useCashierOrderQueue.js` |
| `src/composables/useDialogKeyboardShortcuts.js` |
| `src/components/nightpos/orders/ChargeOrderModal.vue` |
| `src/components/nightpos/cash/QuickOpenCashDialog.vue` |
| `src/components/nightpos/cash/CashMovementDialog.vue` |
| `src/components/nightpos/settlements/SettlementPayDialog.vue` |
| `src/pages/nightpos/cash/index.vue` |
| `src/pages/nightpos/sales/index.vue` |

---

## Build

`npm run build` — OK.

---

## Checklist QA manual

| # | Escenario | Resultado esperado |
|---|-----------|-------------------|
| 1 | Comanda normal | Card sin chips extra; Cobrar habilitado |
| 2 | Comanda CON_ACOMPANANTE sin chica | Chip Acompañante + Falta chica; Cobrar disabled |
| 3 | Combo incompleto | Chips Combo + Falta manilla; Cobrar disabled |
| 4 | Completar combo/manillas | Chips de bloqueo desaparecen; Cobrar enabled |
| 5 | Esperar 2+ min | «2 min esperando» visible |
| 6 | Modal cobro | Enter confirma; Esc cierra |
| 7 | `/nightpos/cash?open=1` sin caja | Modal abrir caja automático |
| 8 | Cobrar comanda | Ventas del turno se actualiza sin F5 |
| 9 | Reimprimir última venta | Abre ticket de la venta más reciente |

---

## Próximo paso recomendado (Fase 1)

~~Cobro desde card (modal pago sin navegar a detalle) + shell cajera simplificado.~~

**Fase 1 completada** — ver `CASHIER_HIGH_PRESSURE_PHASE1_REPORT.md`.

**Próximo:** Fase 2 — shell cajera o cobro «Todo efectivo» en un clic.
