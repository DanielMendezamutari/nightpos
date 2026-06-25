# ROOM_FINISH_AND_CASH_CLOSE_CHECK_FIX_REPORT.md (Frontend)

**Bugfix operativo:** UX terminar pieza + consistencia close-check / cola de cobro  
**Fecha:** 2026-06-22  
**Estado:** Completado

---

## Problema 1 — Terminar pieza libera habitación

### UX esperada

Al pulsar **Terminar** en control de piezas:

- Snackbar: *Pieza terminada. Habitación disponible para nueva pieza.*
- Habitación pasa a disponible (no queda en limpieza).
- Se puede registrar otra pieza en la misma habitación de inmediato.

### Pantallas actualizadas

| Archivo | Cambio |
|---------|--------|
| `services/room-services/index.vue` | Mensaje notify tras `finishRoomService` |
| `services/room-control/index.vue` | Mismo mensaje |
| `cashier/piezas` | Reutiliza `room-services/index.vue` (sin cambio de ruta) |

### API

`POST /room-services/{id}/finish` — respuesta `message` del backend mostrada en notify.

Limpieza móvil sigue usando `/cleaning/room-services/{id}/finish` (flujo CLEANING opcional).

---

## Problema 2 — Close-check vs cola de cobro

### Síntoma corregido

Mi Caja → Cerrar caja mostraba *Hay N comandas pendientes* mientras Cobrar → Pendientes de cobro estaba vacío.

### Comportamiento frontend (sin cambio de contrato)

- Cola: `GET /orders?scope=cashier_chargeable&cashier_scope=1` (`api/orders.js`)
- Close-check: `GET /cash/session/current/close-check` (`api/cash.js`)

Tras el fix backend, ambos conteos coinciden. El botón **Ir** del blocker `active_orders` lleva a `nightpos-cashier-orders`, donde las mismas comandas deben estar visibles.

### QA manual

1. Cobrar → Pendientes: anotar cantidad (debe ser solo `SENT_TO_BAR` del turno abierto).
2. Mi Caja → Cerrar caja: `summary.active_orders` debe ser igual.
3. Si close-check bloquea, **Ir a cobrar** debe listar esas comandas.

---

## Referencias

- Backend: `backend/ROOM_FINISH_AND_CASH_CLOSE_CHECK_FIX_REPORT.md`
- Close-check: `frontend/CASHIER_CLOSE_CHECK_REPORT.md`
