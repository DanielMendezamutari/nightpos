# SETTLEMENT PAYMENT AUDITABLE — Implementation Report (Backend)

**Fecha:** 2026-06-21  
**Estado:** ✅ Implementado  
**Tests:** `SettlementPaymentAuditTest.php` — 15 passed

---

## Resumen

Se cerró el flujo auditable de pago de liquidaciones **sin tabla `staff_settlement_payments`**. Los campos de pago viven en `staff_settlements`. El snapshot operativo sigue en items + adjustments + gross/net congelados al pagar.

---

## Migración

`2026_06_21_100094_settlement_payment_auditable.php`

Campos agregados a `staff_settlements`:

| Campo | Tipo |
|-------|------|
| `payment_method` | nullable string |
| `cash_movement_id` | FK nullable |
| `ticket_number` | unique nullable |
| `print_count` | default 0 |
| `last_printed_at` | nullable timestamp |
| `last_printed_by_user_id` | FK nullable |
| `print_job_id` | FK nullable |
| `version` | default 1 |

---

## Servicios nuevos

| Servicio | Rol |
|----------|-----|
| `SettlementTicketNumberGenerator` | `{BRANCHCODE}-{YYYY}-{000001}` consecutivo |
| `SettlementManualDiscountService` | PERCENT/AMOUNT, base = bruto + limpieza |
| `SettlementPrintPresenter` | Payload ticket térmico |

---

## Use cases

| Use case | Endpoint |
|----------|----------|
| `MarkSettlementPaidUseCase` (extendido) | POST mark-paid |
| `ApplyManualDiscountUseCase` | POST manual-discount |
| `PreviewManualDiscountUseCase` | POST manual-discount/preview |
| `CancelManualDiscountUseCase` | DELETE manual-discount |
| `CreateSettlementPaymentPrintJobUseCase` | Interno al pagar/reimprimir |
| `PrintSettlementPaymentUseCase` | POST print |

---

## Flujo mark-paid

1. Aplicar multas seleccionadas (`SettlementFineApplier`)
2. Egreso caja EXPENSE por `net_amount`
3. Generar `ticket_number`
4. Guardar `payment_method`, `cash_movement_id`, PAID
5. Crear print job `SETTLEMENT_PAYMENT` (warning si sin impresora — no revierte pago)
6. Audit `SETTLEMENT_PAID`

---

## Descuento manual

- Adjustment `MANUAL_DISCOUNT` (único por liquidación, updateOrCreate)
- Orden: bruto → limpieza → descuento → multas al pagar → neto
- PERCENT: base = gross + cleaning adjustment
- Validaciones: motivo obligatorio, no PAID, no excede saldo

---

## Ticket térmico

`PrintTicketContentBuilder::buildSettlementPayment()` — tipo `SETTLEMENT_PAYMENT`

Contenido base: LIQUIDACIÓN PAGADA, persona, rol, caja, turno, corte, ticket #, ajustes, neto, método, pagador, fecha/hora, footer Ribersoft + WhatsApp.

**Garzón (`WAITER`):** bloque **VENTA GARZÓN** con venta total (suma `base_amount` ítems), porcentaje snapshot y comisión — snapshot desde ítems de liquidación (`SettlementWaiterSnapshotResolver`).

**Chica / limpieza:** mantiene línea BRUTO + desglose limpieza/descuento/multas.

Reimpresión: banner REIMPRESIÓN N° {print_count}.

**Tests:** `SettlementPaymentAuditTest` — 19 tests incl. bloque garzón y multa sin alterar venta total.

---

## Audit logs

| Evento | Cuándo |
|--------|--------|
| `SETTLEMENT_PAID` | mark-paid |
| `SETTLEMENT_REPRINTED` | POST print reprint |
| `SETTLEMENT_PRINT_FAILED` | sin impresora activa |
| `FINE_ADDED` | crear multa |
| `FINE_CANCELLED` | cancelar multa |
| `FINE_APPLIED` | multa al pagar |
| `MANUAL_DISCOUNT_CREATED` | primer descuento |
| `MANUAL_DISCOUNT_UPDATED` | actualizar descuento |
| `MANUAL_DISCOUNT_CANCELLED` | DELETE descuento |

---

## API enriquecida

`SettlementMapper` + `mapSettlementSummary` exponen: `payment_method`, `cash_movement_id`, `ticket_number`, `print_count`, `has_ticket`, `cut_label`, `paid_by_name`.

---

## Tests

```bash
php artisan test tests/Feature/Api/V1/SettlementPaymentAuditTest.php
```

16 escenarios cubiertos (15 tests) según spec de auditoría.
