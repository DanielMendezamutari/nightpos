# ORDER_CHARGE_RULES_V1.md
# Reglas de Cobro de Comandas — NightPOS V1

**Fecha:** 2026-06-06  
**Versión:** V1-91.1  
**Estado:** ACTIVO

---

## Estados cobrables en V1

| Estado | Cobrable | Condiciones |
|--------|----------|-------------|
| `OPEN` | ✓ Sí | Con aviso: "no fue enviada a barra" |
| `SENT_TO_BAR` | ✓ Sí | Normal — flujo estándar V1 |
| `IN_PREPARATION` | ✓ Sí | Reservado V2; si existe, cobrable sin restricción |
| `READY` | ✓ Sí | Reservado V2; si existe, cobrable sin restricción |
| `BILLED` | ✗ No | Ya cobrada |
| `CANCELLED` | ✗ No | Cancelada |

---

## Regla: Cobrar OPEN

**Se permite cobrar comandas `OPEN`** para flexibilidad operativa de caja en V1.

Escenario típico: cliente pide directamente en caja sin pasar por garzón, o garzón olvida enviar a barra.

**Comportamiento:**
- Al abrir el modal de cobro para una comanda `OPEN`, se muestra un aviso informativo:
  > *"Esta comanda aún no fue enviada a barra. Puede cobrarla de todas formas."*
- El aviso es de tipo `warning` (amarillo), no bloquea el cobro.
- Al confirmar el cobro, la comanda pasa directamente a `BILLED`.

**Implementación:**  
`frontend/src/components/nightpos/orders/ChargeOrderModal.vue` — alerta condicional `v-if="order?.status === 'OPEN'"`

---

## Regla: Caja abierta obligatoria

Para cobrar cualquier comanda se requiere:
- Una sesión de caja abierta (`cash_session` con `status = OPEN`).
- Si no hay caja, se muestra opción "Abrir caja ahora" en el modal.

---

## Regla: Pago mixto

Se soportan pagos mixtos (efectivo + transferencia + otros). Referencia: `MixedPaymentForm.vue`.

---

## Flujo normal V1

```
Garzón crea comanda (OPEN)
  → Garzón agrega productos
  → Garzón envía a barra (SENT_TO_BAR)
  → Cajera cobra (BILLED)
```

---

## Flujo alternativo V1 (cobro directo)

```
Garzón/cajera crea comanda (OPEN)
  → Agrega productos
  → Cajera cobra directamente (aviso: no enviada a barra)
  → BILLED
```

---

## Decisiones futuras (V2)

- Si se implementa módulo Barra en V2, cobrar `OPEN` sin enviar primero podría requerir confirmación adicional o bloqueo configurable.
- Los estados `IN_PREPARATION` y `READY` tendrán flujo específico en V2.

Referencia: `BAR_MODULE_V1_DECISION.md`
