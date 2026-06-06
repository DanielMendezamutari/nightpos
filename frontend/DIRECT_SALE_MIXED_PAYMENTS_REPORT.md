# DIRECT_SALE_MIXED_PAYMENTS_REPORT.md

**Fase:** DSP — Pago mixto en Venta directa
**Fecha:** 2026-06-05
**Archivos:** `direct-sale.vue`, `MixedPaymentForm.vue`, `useMixedPayments.js`

---

## 1. Problema

Venta directa solo permitía un método de pago (selector Efectivo / QR / Tarjeta). No había pago mixto como en el cobro de comandas.

## 2. Solución

Se extrajo la lógica de pagos mixtos a componentes reutilizables y se integró en Venta directa con UI inline + atajos.

### Archivos nuevos

| Archivo | Rol |
|---|---|
| `composables/useMixedPayments.js` | Lógica: montos, suma, faltante, cambio, validación |
| `components/nightpos/payments/MixedPaymentForm.vue` | UI reutilizable (variant `inline` o `selector`) |

### Archivos modificados

| Archivo | Cambio |
|---|---|
| `pages/nightpos/cash/direct-sale.vue` | `MixedPaymentForm` inline + validación antes de cobrar |
| `components/nightpos/orders/ChargeOrderModal.vue` | Refactorizado para usar `MixedPaymentForm` (sin duplicar lógica) |

## 3. UI en Venta directa (variant `inline`)

En el panel carrito:

- Campos: **Efectivo**, **QR**, **Tarjeta**
- Resumen: Total ingresado / Faltante / Excedente
- Atajos: **Todo efectivo**, **Todo QR**, **Todo tarjeta**, **Limpiar**
- Campo opcional: Monto recibido (efectivo) → muestra **Cambio**
- Botón **Cobrar** envía array `payments` al backend

## 4. Validaciones frontend

| Regla | Comportamiento |
|---|---|
| Sin montos | No cobra — «Indique al menos un monto de pago» |
| Suma < total | No cobra — «Faltan X BOB…» |
| Suma > total | No cobra — «La suma de pagos supera el total» |
| Efectivo recibido < parte efectivo | No cobra (si se ingresó monto recibido) |
| Todo cuadra | `POST /direct-sales` con `payments[]` |

## 5. Payload ejemplo

```json
{
  "items": [{ "product_id": 1, "sale_mode": "SOLO_CLIENTE", "quantity": 1 }],
  "payments": [
    { "method": "CASH", "amount": 100 },
    { "method": "QR", "amount": 70 },
    { "method": "CARD", "amount": 30 }
  ]
}
```

## 6. Validación manual

| # | Paso |
|---|------|
| 1 | Venta directa, total 200 Bs |
| 2 | Efectivo 100 + QR 100 → Cobrar |
| 3 | Ver venta con `payment_mode: MIXED` |
| 4 | Mi caja: ingresos reflejan efectivo y QR por separado |
| 5 | Probar CASH + QR + CARD (100+70+30) |
| 6 | Probar pago incompleto → botón cobra pero muestra aviso |
| 7 | «Todo efectivo» llena el campo y permite cobrar |

---

*Backend sin cambios — ya soportaba `payments[]` múltiples.*
