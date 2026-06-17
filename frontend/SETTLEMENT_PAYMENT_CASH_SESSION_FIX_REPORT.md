# SETTLEMENT_PAYMENT_CASH_SESSION_FIX_REPORT.md (Frontend)

**Bugfix:** pago de liquidaciones + detección de caja abierta  
**Fecha:** 2026-06-16  
**Estado:** Completado

---

## 1. Problema

En pantallas de liquidaciones (garzón, chica, limpieza, detalle):

- Aparecía **«Debe abrir caja»** aunque Mi Caja / banner indicaban sesión abierta.
- La UI leía `cashSessionOpen` desde un `ref` del banner de forma frágil (`cashBanner.value?.cashSessionOpen`).
- `useServiceCashSession` consideraba abierta solo si `session.status === 'OPEN'`, pero la API ya devuelve `session: null` cuando no hay caja.

---

## 2. Solución

### Composable `useSettlementPayment.js`

Centraliza el flujo de pago:

1. `GET /cash/session/current` antes de pagar
2. Si no hay caja → aviso + abre `QuickOpenCashDialog`
3. `POST /settlements/{id}/mark-paid`
4. Snackbar: **«Liquidación pagada y egreso registrado en Caja #X.»**
5. Refresca liquidaciones (`onPaid`) y vuelve a cargar caja

### `useServiceCashSession.js`

```js
cashSessionOpen = computed(() => Boolean(cashSession.value))
```

La presencia de `session` en la respuesta es la fuente de verdad (igual que el backend).

---

## 3. Pantallas actualizadas

| Archivo | Cambio |
|---------|--------|
| `composables/useSettlementPayment.js` | **Nuevo** |
| `composables/useServiceCashSession.js` | Detección de caja corregida |
| `pages/nightpos/settlements/[id].vue` | Usa `useSettlementPayment` |
| `pages/nightpos/settlements/waiters.vue` | Idem + banner + diálogo abrir caja |
| `pages/nightpos/settlements/girls.vue` | Idem |
| `pages/nightpos/settlements/cleaning.vue` | Idem |

Errores del backend se muestran con el mensaje exacto (`getApiErrorMessage`).

---

## 4. Flujo UX

```
Usuario → Pagar liquidación
    ↓
loadCashSession()
    ↓
¿session? ──no──→ warning + QuickOpenCashDialog
    │
   sí
    ↓
mark-paid → success snackbar con Caja #id
    ↓
refresh liquidaciones + refresh caja
```

---

## 5. Validación manual

| # | Paso |
|---|------|
| 1 | Cajera abre caja |
| 2 | Genera liquidaciones |
| 3 | Paga garzón / chica / limpieza sin mensaje falso de caja |
| 4 | Mi Caja: `expected_cash` baja |
| 5 | Snackbar menciona número de caja |
| 6 | Sin caja abierta → bloqueo coherente con backend |

---

## 6. Referencias

- Backend: `backend/SETTLEMENT_PAYMENT_CASH_SESSION_FIX_REPORT.md`
- Scope cajera: `frontend/CASHIER_SHIFT_SCOPE_FIX_REPORT.md`
- Método de pago: `frontend/SETTLEMENT_PAYMENT_METHOD_REPORT.md`

---

## 7. Actualización método de pago (2026-06-16)

`SettlementPayDialog` pide método antes de confirmar. `useSettlementPayment` envía `payment_method` al backend. Ver también movimiento rápido en resumen de liquidaciones (`CashMovementDialog`).
