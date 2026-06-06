# PHASE_9_FRONTEND_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Frontend Ventas / Cobro  
**Fase:** 9 — UI Materialize para cobro y listado de ventas  
**Fecha:** 2026-06-03  
**Referencias:** `FRONTEND_GUIDELINES.md`, `PHASE_8` caja, `backend/PHASE_9_REPORT.md`

---

## 1. Rutas y pantallas

| Ruta | Archivo | Permiso |
| ---- | ------- | ------- |
| `/nightpos/orders/:id` | `src/pages/nightpos/orders/[id].vue` | `orders.access` + botón cobro si `sales.charge` |
| `/nightpos/sales` | `src/pages/nightpos/sales/index.vue` | `sales.list` |
| `/nightpos/cash` | `src/pages/nightpos/cash/index.vue` | `cash.access` (resumen ventas) |

---

## 2. API cliente

`src/api/sales.js`:

- `fetchSales(currentSession)`
- `fetchSale(id)`
- `chargeOrder(orderId, payments)`

---

## 3. Detalle de comanda — cobro

- Botón **Cobrar comanda** (visible si `canChargeOrders` y comanda cobrable).
- Modal Materialize (`VDialog`): total, método (efectivo / QR / tarjeta / mixto), montos parciales, efectivo recibido y cambio, confirmación.
- POST `/orders/{id}/charge` con array `payments`.

---

## 4. Ventas del turno

Listado en `VDataTable`: número de venta, comanda, modo de pago, cajero, garzón, total, estado. Filtra por sesión de caja abierta (`current_session=1`).

---

## 5. Caja

Tarjeta de sesión ampliada con totales de ventas cobradas por efectivo, QR y tarjeta (`session.sales_by_method`).

---

## 6. Navegación y permisos

- Menú **Ventas** en `src/navigation/vertical/nightpos.js` (`sales.list`).
- `useNightPosPermissions`: `canListSales`, `canChargeOrders`.

---

## 7. Build

```bash
cd frontend
npm run build
```

Layout Materialize oficial; sin pantallas fuera del shell NightPOS.
