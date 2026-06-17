# SETTLEMENT_PAYMENT_METHOD_REPORT.md (Backend)

**Mejora:** método de pago obligatorio al pagar liquidaciones  
**Fecha:** 2026-06-16  
**Estado:** Completado

---

## Cambio

`POST /api/v1/settlements/{id}/mark-paid` ahora requiere:

```json
{
  "payment_method": "CASH",
  "notes": "Pagado al cierre"
}
```

Valores: `CASH`, `QR`, `CARD`.

---

## Cash movement

Al pagar liquidación se crea `cash_movements`:

| Campo | Valor |
|-------|--------|
| `movement_type` | `EXPENSE` |
| `amount` | total liquidación |
| `payment_method` | recibido en request |
| `source_type` | `STAFF_SETTLEMENT` |
| `source_id` | id liquidación |
| `cash_movement_reason_id` | según tipo (Comisión garzón / Pago chicas / Limpieza) |

---

## Resumen financiero por método

`CashSessionFinancialSummaryBuilder` expone:

- `income_by_method` — ingresos (cash/qr/card)
- `expense_by_method` — egresos por método
- `expected_by_method` — neto por método

Reglas:

- **Efectivo esperado** = apertura + ingresos CASH − egresos CASH
- **QR neto** = ingresos QR − egresos QR
- **Tarjeta neta** = ingresos CARD − egresos CARD

Solo el efectivo afecta el dinero físico en caja; QR/tarjeta quedan en resumen para conciliación.

`close()` de sesión usa egresos/ingresos **CASH** para `expected_amount`.

---

## Motivos de caja

Migración `2026_06_16_120000_add_settlement_cash_movement_reasons.php` y seeder:

- Pago cajera
- Adelanto personal

(Comisión garzón, Pago chicas, Limpieza, Compra insumos, Pago taxi, Otro egreso/ingreso ya existían.)

---

## Tests

`tests/Feature/Api/V1/SettlementPaymentMethodTest.php` — 10 casos.

Suite: **472 tests OK**.

---

## Referencias

- Frontend: `frontend/SETTLEMENT_PAYMENT_METHOD_REPORT.md`
- Movimientos desde liquidaciones: `backend/CASH_MOVEMENT_FROM_SETTLEMENTS_REPORT.md`
