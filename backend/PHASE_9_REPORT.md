# PHASE_9_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend Ventas / Cobro  
**Fase:** 9 — Cobro de comandas, ventas y movimientos de caja  
**Fecha:** 2026-06-03  
**Referencias:** `DOMAIN_DESIGN.md`, `PHASE_7_REPORT.md`, `PHASE_8_REPORT.md`, `BOLICHE_RULES.md`

---

## 1. Tablas creadas

Migración `2026_06_03_100009_create_sales_tables.php`:

| Tabla | Descripción |
| ----- | ----------- |
| `sales` | Venta por comanda: tenant, branch, `cash_session_id`, `order_id`, cajero, garzón, totales, `payment_mode`, estado `PAID` |
| `sale_items` | Líneas con snapshot de producto, precio, chica, comisión garzón |
| `sale_payments` | Desglose por método (`CASH`, `QR`, `CARD`) |

---

## 2. Endpoints API

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| POST | `/api/v1/orders/{id}/charge` | `sales.charge` |
| GET | `/api/v1/sales` | `sales.list` |
| GET | `/api/v1/sales/{id}` | `sales.list` |

**Payload cobro (mixto):**

```json
{
  "payments": [
    { "method": "CASH", "amount": 100 },
    { "method": "QR", "amount": 80 }
  ]
}
```

---

## 3. Reglas implementadas (`ChargeOrderUseCase`)

| # | Regla |
| - | ----- |
| 1 | Caja abierta del cajero que cobra |
| 2 | No cobrar comanda cancelada |
| 3 | No cobrar comanda sin ítems |
| 4 | No cobrar dos veces (venta existente o estado `BILLED`) |
| 5 | Venta acotada a tenant, branch y `cash_session_id` |
| 6 | Precios desde `order_items` (snapshots), no `product_prices` |
| 7 | Movimiento de caja `INCOME` por cada línea de pago |
| 8 | `payment_mode`: `CASH`, `QR`, `CARD` o `MIXED` |
| 9 | Pago mixto: un registro en `sale_payments` por método |
| 10 | `CON_ACOMPANANTE` exige `girl_user_id` en ítems |
| 11 | Comisión garzón desde `staff_profiles.waiter_commission_percent` al cobrar |
| 12 | Snapshot de comisión por línea en `sale_items` |
| 13 | Comanda pasa a `BILLED` |
| 14 | Transacción DB única |

---

## 4. Caja — resumen de ventas

`GET /api/v1/cash/session/current` incluye `sales_by_method` (`cash`, `qr`, `card`) sumando `sale_payments` de la sesión abierta.

---

## 5. Permisos (seeder)

| Rol | `sales.list` | `sales.charge` |
| --- | ------------ | -------------- |
| tenant_owner | sí | sí |
| cashier | sí | sí |
| waiter | no | no |

---

## 6. Tests

`tests/Feature/Api/V1/ChargeOrderApiTest.php` — cobro efectivo/mixto, rechazos (sin caja, vacía, cancelada, doble cobro), garzón sin permiso, listado por sesión.

**Correcciones transversales en Fase 9:**

- Login PIN por `pin_fingerprint` (evita ambigüedad con `Hash::check` en bucle).
- Emisión JWT con `auth('api')->login($user)` tras `logout` (token alineado al usuario autenticado).

---

## 7. Comandos

```bash
cd backend
php artisan migrate
php artisan test
```

Demo: cajero PIN `1234`, garzón `5678`, sucursal `CENTRO`. Cobrar requiere caja abierta del cajero.
