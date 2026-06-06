# DIRECT_SALE_MIXED_PAYMENTS_REPORT.md

**Fase:** DSP — Pago mixto en Venta directa
**Fecha:** 2026-06-05
**Endpoint:** `POST /api/v1/direct-sales`

---

## 1. Veredicto backend

**No se requirieron cambios de código.** `CreateDirectSaleUseCase` ya soportaba pagos mixtos:

- Itera `payments[]` del payload
- Valida métodos habilitados y montos > 0
- Exige `sum(payments) === total` (±0.01)
- `payment_mode = MIXED` si hay más de un pago
- Crea un `sale_payment` por fila
- Crea un `cash_movement` INCOME por cada pago (con `payment_method` CASH/QR/CARD)

## 2. Flujo contable (sin cambios)

```
POST /direct-sales
  payments: [
    { method: CASH, amount: 100 },
    { method: QR, amount: 70 },
    { method: CARD, amount: 30 }
  ]
  total venta: 200

→ sale_payments: 3 filas
→ cash_movements: 3 INCOME (uno por método)
→ Mi caja / fiscalización: sumPaymentsByMethod refleja cada método
```

## 3. Tests (`DirectSaleApiTest.php`) — 15/15

| # | Escenario |
|---|-----------|
| 12 | Pago mixto CASH + QR (200 Bs) → `MIXED`, 2 payments, 2 movimientos |
| 13 | CASH + QR + CARD (100+70+30) → 3 payments |
| 14 | Pagos menores al total → 422 |
| 15 | Pagos mayores al total → 422 |

```
Tests: 15 passed (81 assertions)
```

## 4. Mensaje de error pago incorrecto

`SaleDomainException::paymentMismatch()`:

> «La suma de pagos no coincide con el total de la comanda.»

*(Texto heredado de cobro de comanda; aplica igual a venta directa.)*

## 5. Impacto en caja

Cada `sale_payment` alimenta:

- `CashSessionFinancialSummaryBuilder` → `total_cash`, `total_qr`, `total_card`
- Movimientos de sesión con `payment_method` discriminado

Sin cambios adicionales necesarios para Mi caja ni fiscalización.

---

*Solo tests nuevos en backend; lógica de negocio ya existía.*
