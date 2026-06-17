# CASHIER_CLOSE_CHECK_REPORT.md (Backend)

**Bugfix operativo:** bloqueos antes de cerrar caja + scope cajera  
**Fecha:** 2026-06-15  
**Estado:** Completado

---

## 1. Endpoint pre-cierre de caja

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/cash/session/current/close-check` | `cash.access` |

Respuesta:

```json
{
  "can_close": false,
  "blockers": [
    { "type": "ACTIVE_ORDERS", "code": "active_orders", "count": 2, "message": "..." }
  ],
  "warnings": [],
  "actions": [
    { "label": "Ir a cobrar comandas", "route": "nightpos-cashier-orders" }
  ],
  "summary": { ... }
}
```

---

## 2. Bloqueantes (cierre de caja)

| Código | Condición |
|--------|-----------|
| `active_orders` | Comandas `OPEN` o `SENT_TO_BAR` en turno actual |
| `active_room_services` | Piezas `ACTIVE` / `DUE` |
| `settlements_not_generated` | Hay fuentes sin liquidar o liquidaciones no generadas (`SETTLEMENTS_NOT_GENERATED`) |
| `settlements_pending_payment` | Liquidaciones `PENDING` en scope de caja (`SETTLEMENTS_PENDING_PAYMENT`) |
| `pending_waiter_settlements` | Pagos garzón pendientes |
| `pending_girl_settlements` | Pagos chica pendientes |
| `pending_cleaning_settlements` | Pagos limpieza pendientes |
| `unsettled_settlement_sources` | Fuentes liquidables sin `staff_settlement_item` (ej. actividad post-pago sin regenerar) |

`POST /cash/session/close` **revalida** los bloqueantes server-side (`CloseCashSessionUseCase`).

---

## 3. Cierre de turno (mejoras)

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/shifts/current/close-check` | `shifts.close` |

Reutiliza `GetShiftClosureCheckUseCase`. Bloqueantes ampliados:

- Cajas abiertas
- Piezas activas/vencidas
- **Comandas activas** (`OPEN`, `SENT_TO_BAR`) — nuevo
- **Liquidaciones no generadas** — pasó de warning a blocker
- **Fuentes sin liquidar** (`unsettled_settlement_sources`) — blocker si hay actividad sin `settlement_item`

Warnings: liquidaciones pendientes de pago, habitaciones en limpieza, diferencia de caja.

---

## 4. Scope cajera (órdenes)

Query params en `GET /orders`:

| Param | Efecto |
|-------|--------|
| `cashier_scope=1` | Filtra por turno oficial abierto |
| `current_session=1` | En `billed_recent`, filtra ventas de la caja/cajera actual |

Scope `cashier_chargeable`: estados `OPEN`, `SENT_TO_BAR` (sin `IN_PREPARATION`/`READY`).

---

## 5. Permisos

- **Cajera básica:** se quitó `shifts.close` (solo cierra su caja, no turno general).
- **Cajera senior / admin:** mantiene `shifts.close`.

---

## 6. Archivos clave

| Archivo | Rol |
|---------|-----|
| `CashSessionCloseCheckBuilder.php` | Reglas de bloqueo caja |
| `GetCashSessionCloseCheckUseCase.php` | API close-check caja (usa `cash_session.official_shift_id`) |
| `CloseCashSessionUseCase.php` | Enforcement server-side |
| `ListOrdersUseCase.php` | Scope turno/caja cajera |
| `EloquentReportReadRepository.php` | Blockers turno ampliados |

---

## 7. Tests

`tests/Feature/Api/V1/CashierCloseCheckTest.php` — 9 tests, 62 assertions.

---

## 8. Referencias

- Frontend: `frontend/CASHIER_CLOSE_CHECK_REPORT.md`
- Scope: `backend/CASHIER_SHIFT_SCOPE_FIX_REPORT.md`
- Liquidaciones / turno: `backend/SETTLEMENT_SHIFT_SCOPE_FIX_REPORT.md`
- Pago liquidaciones / caja: `backend/SETTLEMENT_PAYMENT_CASH_SESSION_FIX_REPORT.md`

---

## 9. Pago de liquidaciones y caja (2026-06-16)

`MarkSettlementPaidUseCase` usa el mismo resolver que `GET /cash/session/current` (`OpenCashSessionResolver::resolveOpenCashSessionForUser`).

Al pagar liquidaciones pendientes antes del cierre, la cajera debe tener **su** caja abierta; el egreso se registra en esa sesión y reduce `expected_cash`.

Ver: `backend/SETTLEMENT_PAYMENT_CASH_SESSION_FIX_REPORT.md`

---

## 10. Método de pago en liquidaciones (2026-06-16)

- `mark-paid` requiere `payment_method`; egresos QR/CARD no reducen efectivo físico.
- Resumen de caja incluye `expected_by_method` para conciliación al cierre.
- Ver: `backend/SETTLEMENT_PAYMENT_METHOD_REPORT.md`

---

## 11. Consistencia liquidaciones / close-check (2026-06-17)

Close-check y liquidaciones usan el mismo scope de caja (`cash_session_id` + turno de sesión).

Blockers tipados: `SETTLEMENTS_NOT_GENERATED` vs `SETTLEMENTS_PENDING_PAYMENT` con `route` a pantallas de pago.

Ver: `backend/SETTLEMENT_CLOSE_CHECK_CONSISTENCY_FIX_REPORT.md`
