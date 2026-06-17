# MIXED_PAYMENT_UX_SIMPLIFICATION_REPORT.md

**Bugfix UX:** eliminar selector redundante en pago mixto  
**Fecha:** 2026-06-16  
**Estado:** Completado

---

## 1. Problema

En cobro de comanda y venta directa, al escribir monto en **Efectivo** el usuario debía volver a seleccionar el método «Efectivo» en un dropdown. Redundante y confuso.

---

## 2. Regla UX

El método se **infiere del campo** donde se ingresa el monto:

| Campo | method |
|-------|--------|
| Efectivo | `CASH` |
| QR | `QR` |
| Tarjeta | `CARD` |

No se envían métodos con monto 0. No hay duplicados.

Ejemplo total 200:

```
Efectivo: 100, QR: 100
→ payments: [{ method: "CASH", amount: 100 }, { method: "QR", amount: 100 }]
```

Pago solo efectivo 200 → un solo ítem `CASH` sin selector adicional.

---

## 3. Archivos

| Archivo | Cambio |
|---------|--------|
| `composables/useMixedPayments.js` | Siempre infiere `payments[]` desde campos; eliminado modo `method` / `MIXED` |
| `components/nightpos/payments/MixedPaymentForm.vue` | Sin `VSelect`; 3 campos + botones rápidos |
| `components/nightpos/orders/ChargeOrderModal.vue` | Usa formulario simplificado |
| `pages/nightpos/cash/direct-sale.vue` | Idem |

### Botones rápidos (solo llenan campos)

- Todo efectivo
- Todo QR
- Todo tarjeta
- Limpiar

No activan ningún selector de método.

---

## 4. Validaciones

| Condición | Comportamiento |
|-----------|----------------|
| Suma = total | Permite cobrar |
| Suma < total | «Faltan X BOB…» |
| Suma > total | «Ajuste los montos» (mixto); cambio solo si aplica efectivo recibido |
| Sin montos | No cobra |

---

## 5. Validación manual

| # | Escenario |
|---|-----------|
| 1 | Comanda 200 → efectivo 200 → Enter/cobrar sin elegir método |
| 2 | Comanda 200 → efectivo 100 + QR 100 → cobrar |
| 3 | Venta directa igual |
| 4 | Payload sin métodos duplicados ni amount 0 |
| 5 | Mi Caja refleja ingresos por método |

---

## 6. Referencias

- Histórico pago mixto venta directa: `frontend/DIRECT_SALE_MIXED_PAYMENTS_REPORT.md`
