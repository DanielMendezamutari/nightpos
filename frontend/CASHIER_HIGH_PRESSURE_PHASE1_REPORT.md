# Cajera alta presión — Fase 1 (Frontend)

**Fecha:** 2026-06-16  
**Estado:** ✅ Implementado  
**Depende de:** Fase 0 (`CASHIER_HIGH_PRESSURE_PHASE0_REPORT.md`)

---

## Objetivo

Cobrar comandas **desde la cola sin navegar al detalle**.

Flujo:

```
Cobrar comandas → card Mesa 12 → Cobrar → modal pago → Enter → cobrada
```

---

## Cambios implementados

### Cola de cobro (`/nightpos/cashier/orders`)

| Antes (Fase 0) | Ahora (Fase 1) |
|----------------|----------------|
| Cobrar → navega a `/orders/:id?charge=1` | Cobrar → `ChargeOrderModal` en la misma pantalla |
| Ver / corregir primario | **Cobrar** primario (x-large, success) |
| — | **Corregir** secundario (outlined) → detalle modo corrección |

### Flujo `openCharge`

1. Valida `charge_blocked` y caja abierta.
2. `fetchOrder(id)` con overlay loading en la card.
3. Abre `ChargeOrderModal` sin cambiar ruta.
4. Enter confirma (Fase 0); Esc cierra.

### Flujo `onChargeConfirm`

1. Validaciones locales (pagos, manillas, efectivo recibido).
2. `POST /orders/{id}/charge` — backend fuente de verdad.
3. Éxito: cierra modal, snackbar **«Comanda cobrada.»**, `loadOrders()` + `loadCashSession()`.
4. Error: mensaje API + `loadOrders()` para refrescar chips/`charge_blocked`.

### UX card

- Overlay `VProgressCircular` mientras carga detalle para cobrar.
- Cobrar deshabilitado si `charge_blocked`.
- Chips Fase 0 visibles (Falta chica, Falta manilla, No cobrable, etc.).

### Modal

- Título ampliado: mesa + número comanda.

### Caja cerrada

- Banner y botón «Abrir caja ahora» abren `QuickOpenCashDialog` inline (sin navegar).

---

## Archivos tocados

| Archivo |
|---------|
| `src/pages/nightpos/cashier/orders/index.vue` |
| `src/components/nightpos/orders/ChargeOrderModal.vue` |

**Backend:** sin cambios (Fase 0 ya entrega flags + validación en charge).

---

## Clics estimados — cobro simple

| Paso | Clics |
|------|-------|
| Cobrar comandas (ya en pantalla) | 0 |
| Cobrar en card | 1 |
| Todo efectivo | 1 |
| Enter confirmar | 0 |
| **Total** | **2** |

Antes (con detalle): 5–6 clics.

---

## Checklist QA manual

| # | Escenario | Esperado |
|---|-----------|----------|
| 1 | Comanda lista | Cobrar abre modal sin cambiar URL |
| 2 | Todo efectivo + Enter | Cobrada, snackbar, card sale de Pendientes |
| 3 | Tab Cobradas recientes | Comanda aparece tras cobro / SSE |
| 4 | Combo incompleto | Cobrar disabled, chips, Corregir → detalle |
| 5 | Error backend al cobrar | Mensaje claro + lista refrescada |
| 6 | Caja cerrada | Abrir caja inline desde banner/botón |

---

## Build

`npm run build` — OK.

---

## No incluido (Fase 2+)

- ~~Shell cajera dedicado / bottom nav~~ → **Fase 2A completada** — `CASHIER_HIGH_PRESSURE_PHASE2A_SHELL_REPORT.md`
- Pago por chips avanzado con auto-confirm
- Doble clic en card para cobrar efectivo directo

---

## Próximo paso sugerido

Fase 2B: cobro «Todo efectivo» en un clic desde card, o sticky footer venta directa.
