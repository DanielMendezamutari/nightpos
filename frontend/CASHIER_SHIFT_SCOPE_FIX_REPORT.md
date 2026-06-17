# CASHIER_SHIFT_SCOPE_FIX_REPORT.md (Frontend)

**Fecha:** 2026-06-15  
**Estado:** Completado

---

## Problema

En **Cobrar comandas**, la cajera podía ver comandas históricas o de otros contextos operativos.

## Solución

### `cashier/orders/index.vue`

- Tab por defecto: `cashier_chargeable`
- Carga vía `fetchCashierOrdersByScope()` con filtros de turno/caja

### Tabs reducidos

| Tab | Scope backend |
|-----|---------------|
| Pendientes de cobro | `cashier_chargeable` |
| Cobradas recientes | `billed_recent` + sesión actual |

Eliminados tabs redundantes `operational_active` / `pending_charge` en vista cajera.

### Mi Caja

Sigue mostrando solo la sesión del usuario (`fetchCurrentCashSession`). No lista ventas globales.

### Pago de liquidaciones (2026-06-16)

Las pantallas de liquidaciones usan `useSettlementPayment`, que consulta `GET /cash/session/current` antes de pagar (misma regla que Mi Caja). Tras pagar, refresca caja y muestra snackbar con el número de sesión.

Ver: `frontend/SETTLEMENT_PAYMENT_CASH_SESSION_FIX_REPORT.md`

---

Ver también: `CASHIER_CLOSE_CHECK_REPORT.md`
