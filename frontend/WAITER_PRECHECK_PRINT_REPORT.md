# Precuenta local garzón — Frontend

**Fecha:** Jun 2026  
**Estado:** Implementado

---

## Botón en detalle comanda

**Pantalla:** `/nightpos/waiter/orders/:id`

**Botón:** «Imprimir precuenta»

**Visible cuando:**

- Hay ítems en la comanda
- Estado ≠ `BILLED` / `CANCELLED` (incluye pendiente de cobro en barra)

---

## Flujo UX

1. Garzón toca **Imprimir precuenta**
2. Loading en botón
3. `POST /orders/{id}/precheck/print` vía `printOrderPrecheck()` en `api/orders.js`
4. Éxito → toast *«Precuenta enviada a impresora.»* (sin navegar)
5. Error (sin agente / sin device) → toast error + botón **Ver precuenta**

---

## Fallback manual

Ruta existente: `/nightpos/print/precheck/order/:id`  
Componente: `PrintablePrecheckTicket.vue` + `window.print()`

---

## API

```js
import { printOrderPrecheck } from '@/api/orders'

await printOrderPrecheck(orderId)
```

---

## QA manual

1. Comanda con productos → Imprimir precuenta → agente recibe job `PRECHECK`
2. Sin agente/dispositivo → mensaje error + enlace Ver precuenta
3. Comanda cobrada → botón no visible
4. Enviar a barra sigue imprimiendo `ORDER_COMMAND` (sin cambios)

---

## Relacionado

- Flujo tipo de venta: `frontend/WAITER_SALE_TYPE_FLOW_IMPLEMENTATION_REPORT.md`
- Backend: `backend/WAITER_PRECHECK_PRINT_REPORT.md`
