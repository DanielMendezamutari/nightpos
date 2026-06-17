# CASHIER_CLOSE_CHECK_REPORT.md (Frontend)

**Bugfix operativo:** Enter para cobrar + scope cajera + bloqueo cierre caja  
**Fecha:** 2026-06-15  
**Estado:** Completado

---

## 1. Enter para cobrar

| Pantalla | Cambio |
|----------|--------|
| `ChargeOrderModal.vue` | `VForm` + `@submit.prevent`, botón `type="submit"`, `validate()` antes de emitir, anti doble submit vía `:loading` |
| `direct-sale.vue` | Formulario de pago envuelto en `VForm`; Enter ejecuta cobro si válido |
| `MixedPaymentForm.vue` | Sin cambios estructurales; expone `validate()` usado por padres |

Reglas: no cobra si `loading`, caja cerrada, montos inválidos o total desbalanceado.

---

## 2. Scope cajera — Cobrar comandas

Tabs (`useOrderListTabs.js`):

- **Pendientes de cobro** → `cashier_chargeable` + `cashier_scope=1`
- **Cobradas recientes** → `billed_recent` + `cashier_scope=1` + `current_session=1`

API: `fetchCashierOrdersByScope()` en `api/orders.js`.

La cajera ya no ve comandas de turnos cerrados ni cobros de otras cajas por defecto.

---

## 3. Mi Caja — cierre bloqueado

Flujo al pulsar **Cerrar caja**:

1. `GET /cash/session/current/close-check`
2. Si `can_close === false` → diálogo con lista de bloqueantes y botones de acción
3. Si OK → modal de arqueo (como antes)

API: `fetchCashSessionCloseCheck()` en `api/cash.js`.

Acciones sugeridas: Cobrar comandas, Control de piezas, Liquidaciones, **Generar liquidaciones** (fuentes huérfanas).

Nuevo bloqueante (2026-06-16): `unsettled_settlement_sources` — actividad liquidable sin `settlement_item` (ej. chica cobró y volvió con ventas nuevas sin regenerar).

---

## 4. Cierre de turno

`shifts/close.vue` usa `GET /shifts/current/close-check` (`fetchShiftCloseCheck`) en lugar de `/reports/shift-closure` (requería `reports.access` que la cajera no tenía).

Solo visible con permiso `shifts.close` (admin / cajera senior).

---

## 5. QA manual sugerido

1. Login cajera → abrir caja → crear comanda → intentar cerrar caja → bloqueo.
2. Cobrar comanda → crear pieza → intentar cerrar → bloqueo pieza.
3. Generar y pagar liquidaciones → cerrar caja OK.
4. Enter en modal de cobro y venta directa con formulario válido.
5. Cobradas recientes solo muestra cobros de la sesión actual.

---

## 6. Referencias

- Backend: `backend/CASHIER_CLOSE_CHECK_REPORT.md`
- Scope: `frontend/CASHIER_SHIFT_SCOPE_FIX_REPORT.md`
- Liquidaciones / turno: `frontend/SETTLEMENT_SHIFT_SCOPE_FIX_REPORT.md`
- Liquidaciones parciales: `frontend/PARTIAL_SETTLEMENTS_IMPLEMENTATION_REPORT.md`
- Método de pago / movimientos: `frontend/SETTLEMENT_PAYMENT_METHOD_REPORT.md`

---

## 7. Cierre con egresos por método (2026-06-16)

Mi Caja muestra ingresos/egresos/esperado por Efectivo, QR y Tarjeta. Al pagar liquidaciones con QR/tarjeta, el efectivo físico no baja; el cierre usa la fila **Efectivo** para arqueo.

---

## 8. Consistencia liquidaciones / close-check (2026-06-17)

- Blockers con botón **Ir** hacia tab de liquidaciones (garzones/chicas/limpieza).
- Liquidaciones: alertas diferenciadas «hay pendientes» vs «no hay liquidaciones».
- Generate: mensaje si no crea nuevas pero hay PENDING.

Ver: `frontend/SETTLEMENT_CLOSE_CHECK_CONSISTENCY_FIX_REPORT.md`
