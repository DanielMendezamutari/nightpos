# CASH_MOVEMENT_FROM_SETTLEMENTS_REPORT.md (Backend)

**Mejora:** movimientos manuales con método de pago obligatorio  
**Fecha:** 2026-06-16  
**Estado:** Completado

---

## API

`POST /api/v1/cash/movements` — `payment_method` ahora **requerido** (`CASH`, `QR`, `CARD`, `OTHER`).

Misma regla de caja abierta que liquidaciones (`OpenCashSessionResolver`).

---

## Casos de uso

Desde liquidaciones o Mi Caja, la cajera registra egresos como:

- Pago cajera (efectivo)
- Compra insumos (efectivo/QR)
- Taxi, adelantos, otros

Sin cambiar de pantalla durante el cierre.

---

## Tests

- Movimiento sin caja → 422
- Movimiento con caja → ligado a `cash_session_id` del usuario
- Ver `SettlementPaymentMethodTest.php` casos 9–10

---

## Referencias

- Frontend: `frontend/CASH_MOVEMENT_FROM_SETTLEMENTS_REPORT.md`
