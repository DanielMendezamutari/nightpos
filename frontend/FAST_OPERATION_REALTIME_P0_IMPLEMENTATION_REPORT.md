# Fast Operation Mode P0 — Implementación Frontend

**Fecha:** 2026-06-16  
**Estado:** ✅ Completado  
**Auditoría base:** `FAST_OPERATION_REALTIME_AUDIT.md`

---

## Resumen

Se implementó tiempo real P0 en comandas para cajera, admin y garzón: composable unificado, banner SSE, polling fallback y conexión persistente en layouts operativos.

---

## Nuevos composables

| Archivo | Función |
|---------|---------|
| `useOrderOperationalEvents.js` | Escucha 5 eventos de comanda; filtro por `orderId`; toasts; debounce configurable |
| `useOperationalPollingFallback.js` | Recarga cada 30 s; opción solo si SSE desconectado |
| `useOperationalSseHost.js` | Mantiene SSE activo en layout (no detiene al navegar) |

---

## NightPosSseBanner

Estados no invasivos:

- **Conectado** — chip verde «Tiempo real activo»
- **Reconectando** — alerta compacta
- **Desconectado** — alerta + hint de polling

---

## SSE persistente (layout)

| Layout | Archivo |
|--------|---------|
| Vertical / horizontal NightPOS | `DefaultLayoutWithVerticalNav.vue`, `DefaultLayoutWithHorizontalNav.vue` |
| Blank (garzón/limpieza móvil) | `blank.vue` — rutas `/nightpos/*` |

Las páginas registran handlers vía composables; la conexión sobrevive al navegar entre pantallas operativas del mismo layout.

---

## Pantallas integradas

| Ruta | SSE | Polling 30s | Banner | Notas |
|------|-----|-------------|--------|-------|
| `/nightpos/cashier/orders` | `useOrderOperationalEvents` | ✅ | ✅ | `order.created` 100 ms + toast «Nueva comanda recibida» |
| `/nightpos/orders` | ✅ | ✅ | ✅ | Lista admin |
| `/nightpos/orders/:id` | ✅ filtro `orderId` | ✅ | ✅ | Toast «Comanda actualizada»; aviso si cobrada/cancelada por otro |
| `/nightpos/waiter/orders` | ✅ | ✅ | ✅ | Lista garzón |
| `/nightpos/waiter/orders/:id` | ✅ filtro `orderId` | ✅ | ✅ | Igual detalle cajera |
| `/nightpos/cash` | existente | — | ✅ | Sin cambio de listeners |
| `/nightpos/settlements` | existente | — | ✅ | Sin cambio de listeners |

Limpieza/habitaciones: sin cambios (siguen con `useRoomOperationalEvents`).

---

## Comportamiento cajera (caso reportado)

1. Garzón crea comanda → backend emite `order.created`
2. Cajera en lista recibe evento en ≤100 ms debounce + SSE layout persistente
3. Si SSE cae → banner + polling 30 s mantiene lista actualizada
4. Toast: «Nueva comanda recibida.»

---

## Build

`npm run build` — ✅ OK

---

## Referencias

- `FAST_OPERATION_REALTIME_AUDIT.md` (actualizado)
- `SSE_2_REPORT.md` (actualizado)
- `backend/FAST_OPERATION_REALTIME_P0_IMPLEMENTATION_REPORT.md`
