# Frontend SSE-2 Report — Consumidores de Eventos Operativos

## Fase: V1-94 + P0 Fast Operation (2026-06-16)

**Fecha:** 2026-06-06

---

## Objetivo

Conectar `useOperationalEvents()` en las páginas operativas para que se actualicen automáticamente al recibir eventos del backend sin necesidad de F5.

---

## Páginas modificadas

### 1. `/nightpos/cleaning` — Limpieza móvil
**Archivo:** `src/pages/nightpos/cleaning/index.vue`

Eventos suscritos:
- `room_service.due` → `debouncedLoad()` + toast `"⏰ Pieza cumplida: ..."`
- `room_service.finished` → `debouncedLoad()`
- `room.cleaned` → `debouncedLoad()`
- `cleaning.earnings.updated` → `debouncedLoad()`

Extras:
- Indicador `"Tiempo real desconectado"` si SSE falla
- Polling cada 30s se mantiene como fallback

---

### 2. `/nightpos/cashier/orders` — Cajera: Cobrar comandas (P0 2026-06-16)
**Archivo:** `src/pages/nightpos/cashier/orders/index.vue`

Composable: `useOrderOperationalEvents` + `useOperationalPollingFallback` (30 s) + `NightPosSseBanner`

- `order.created` → reload 100 ms + toast «Nueva comanda recibida»
- `order.sent_to_bar` → reload + toast
- `order.updated` / `order.billed` / `order.cancelled` → reload 500 ms

---

### 2b. `/nightpos/orders` y `/nightpos/orders/:id` — Admin/cajera detalle (P0)
**Archivos:** `orders/index.vue`, `orders/[id].vue`

- SSE + polling + banner
- Detalle: filtro por `orderId`, toast «Comanda actualizada», aviso si cobrada/cancelada por otro

---

### 2c. `/nightpos/waiter/orders` — Garzón móvil (P0)
**Archivos:** `waiter/orders/index.vue`, `waiter/orders/[id].vue`

- Mismo composable de comandas + polling + banner

---

### 2d. SSE persistente en layout (P0)
**Archivos:** `DefaultLayoutWithVerticalNav.vue`, `DefaultLayoutWithHorizontalNav.vue`, `blank.vue`

`useOperationalSseHost()` — conexión activa al navegar entre pantallas operativas.

---

### 3. `/nightpos/cash` — Mi Caja
**Archivo:** `src/pages/nightpos/cash/index.vue`

Eventos suscritos:
- `cash.movement.created` → `debouncedCashLoad()`
- `cash.session.opened` → `debouncedCashLoad()`
- `cash.session.closed` → `debouncedCashLoad()`
- `sale.created` → `debouncedCashLoad()`
- `direct_sale.created` → `debouncedCashLoad()`
- `settlement.paid` → `debouncedCashLoad()`

---

### 4. `/nightpos/cash/direct-sale` — Ventas directas
**Archivo:** `src/pages/nightpos/cash/direct-sale.vue`

Eventos suscritos:
- `cash.session.opened` → `loadCash()`
- `cash.session.closed` → `loadCash()`

---

### 5. `/nightpos/shift-console` — Consola de turno
**Archivo:** `src/pages/nightpos/shift-console/index.vue`

Eventos suscritos (todos los operativos):
- `order.created`, `order.billed`, `order.cancelled`
- `sale.created`, `direct_sale.created`
- `cash.movement.created`, `cash.session.opened`, `cash.session.closed`
- `settlement.generated`, `settlement.paid`
- `room_service.created`, `room_service.finished`

---

### 6. `/nightpos/settlements` — Liquidaciones
**Archivo:** `src/pages/nightpos/settlements/index.vue`

Eventos suscritos:
- `settlement.generated` → `debouncedRefresh()`
- `settlement.paid` → `debouncedRefresh()`
- `cash.movement.created` → `debouncedRefresh()`

---

## Patrón de implementación

Todas las páginas siguen el mismo patrón:

```js
const { on, start: startSse, stop: stopSse } = useOperationalEvents()

let debounce = null
const debouncedLoad = () => {
  clearTimeout(debounce)
  debounce = setTimeout(loadData, 500)
}

on('event.type', debouncedLoad)

onMounted(() => { loadData(); startSse() })
onUnmounted(() => { stopSse() })
```

---

## Fallback

- Polling existente (cada 30s) se mantiene en páginas con lógica de `setInterval`
- Si SSE falla, la página muestra `"Tiempo real desconectado"` (solo en limpieza)
- Las demás páginas siguen funcionando con polling normal

---

## Debounce

Se aplica un debounce de 400–600ms para evitar recargas excesivas cuando múltiples eventos llegan en ráfaga (ej. crear y enviar comanda a barra casi al mismo tiempo).
