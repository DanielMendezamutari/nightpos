# Cierre de caja por método de pago

**Fecha:** 2026-06-17

## Estado previo del API

`financial_summary` ya exponía:

- `income_by_method`, `expense_by_method`, `expected_by_method`
- `expected_cash`, `total_cash/qr/card`

Faltaban alias explícitos solicitados en la spec UX.

## Cambio backend

`CashSessionFinancialSummaryBuilder` — campos adicionales **retrocompatibles**:

| Campo nuevo | Descripción |
|-------------|-------------|
| `sales_by_method` | `{ cash, qr, card }` ventas cobradas |
| `opening_cash` | Fondo inicial de sesión |
| `income_cash/qr/card` | Ingresos manuales por método |
| `expense_cash/qr/card` | Egresos por método |
| `sales_cash/qr/card` | Alias de ventas |
| `expected_qr`, `expected_card` | Neto esperado QR/tarjeta |

Lógica de arqueo **sin cambios**:

- `expected_cash` = apertura + ingresos cash − egresos cash (incluye ventas efectivo vía movimientos de cobro).
- QR/card = ingresos − egresos por método (liquidaciones con `payment_method` ya verificadas).

## Close cash session

- Request sigue con `declared_closing_amount` (efectivo físico).
- No se agregó migración para `declared_qr` / `declared_card`.
- Frontend registra verificación QR/tarjeta en `closing_notes`.

## Tests

`SettlementPaymentMethodTest` — 10 passed (egresos por método correctos).
