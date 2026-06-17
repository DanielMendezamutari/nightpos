# SETTLEMENT_PAYMENT_METHOD_REPORT.md (Frontend)

**Mejora:** diálogo de pago con método de pago  
**Fecha:** 2026-06-16  
**Estado:** Completado

---

## Componente `SettlementPayDialog.vue`

Al pulsar **Pagar** en liquidaciones:

- Monto, persona, tipo
- Selector: Efectivo / QR / Tarjeta
- Notas opcionales
- Mensaje: *«Se registrará un egreso de X BOB por EFECTIVO en tu caja abierta.»*

Usado en:

- `settlements/waiters.vue`
- `settlements/girls.vue`
- `settlements/cleaning.vue`
- `settlements/[id].vue`

---

## `useSettlementPayment.js`

Envía `payment_method` y `notes` a `markSettlementPaid`. Snackbar incluye método y número de caja.

---

## Mi Caja — resumen por método

Tabla en `cash/index.vue`:

| Método | Ingresos | Egresos | Esperado/neto |
|--------|----------|---------|---------------|
| Efectivo | … | … | … |
| QR | … | … | … |
| Tarjeta | … | … | … |

Datos desde `financial_summary.income_by_method`, `expense_by_method`, `expected_by_method`.

---

## Referencias

- Backend: `backend/SETTLEMENT_PAYMENT_METHOD_REPORT.md`
- Movimientos rápidos: `frontend/CASH_MOVEMENT_FROM_SETTLEMENTS_REPORT.md`
