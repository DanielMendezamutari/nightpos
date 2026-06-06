# P1 — SSE OPERATIVO PIEZAS / HABITACIONES

**Fecha:** 2026-06-06  
**Estado:** ✅ Completado  
**Alcance:** SSE en pantallas de piezas/habitaciones + fix conexión `useOperationalEvents`  
**No incluye:** V1-97 (impresión)

---

## 1. Huecos cerrados (auditoría A-01)

Pantallas que **no** tenían SSE y ahora consumen eventos de piezas/limpieza:

| Pantalla | Ruta | Eventos |
|----------|------|---------|
| Control de piezas | `services/room-control/index` | todos §2 |
| Piezas turno | `services/room-services/index` | todos §2 |
| Limpieza habitaciones | `rooms/cleaning` | todos §2 |
| Dashboard habitaciones | `rooms/dashboard` | todos §2 |

**Ya tenían SSE (mantenido/mejorado):** `cleaning/index`, `shift-console`, `settlements/index`, `cash`, `cashier/orders`.

---

## 2. Eventos consumidos

| Evento | Efecto en UI |
|--------|----------------|
| `room_service.created` | `debouncedReload()` — nueva fila sin F5 |
| `room_service.due` | reload + toast warning (limpieza/control; silenciado en admin piezas) |
| `room_service.finished` | reload — pieza pasa a limpieza |
| `room.cleaned` | reload — habitación disponible |
| `cleaning.earnings.updated` | reload — KPI pago turno limpieza |

---

## 3. Composables

### `useOperationalEvents.js` (fix conexión)

Problemas corregidos:

| Antes | Después |
|-------|---------|
| `on('open')` / `on('error')` nunca disparaban (filtrados en `dispatchEvent`) | `es.onopen` y `handleDisconnect()` despachan `open` / `error` |
| `sseConnected` local siempre `true` inicial | `connected` y `reconnecting` refs reales exportados |
| `auth.isLoggedIn` inexistente | `auth.isAuthenticated` |
| Sin estado reconectando | `reconnecting` + evento `reconnecting` |

API exportada:

```js
const { on, off, start, stop, connected, reconnecting } = useOperationalEvents()
```

### `useRoomOperationalEvents.js` (nuevo)

Encapsula suscripción a los 5 eventos de piezas/limpieza + lifecycle `start/stop` + debounce 400ms.

### `NightPosSseBanner.vue` (nuevo)

Banner reutilizable: desconectado / reconectando.

---

## 4. Corrección C-04 — Terminar ACTIVE + DUE

**Archivo:** `services/room-services/index.vue`

- Botón **Terminar** visible para `ACTIVE`, `DUE` e `is_due`
- Guard en `onFinish` alineado con permiso `room_services.finish`
- Chip estado `DUE` en color error

---

## 5. Validación multi-pantalla (sin F5)

Escenario recomendado con **dos pestañas**:

1. Tab A: `/nightpos/services/room-services` (admin piezas)
2. Tab B: `/nightpos/services/room-control` o `/nightpos/rooms/dashboard`

| Acción en Tab A | Resultado esperado Tab B |
|-----------------|--------------------------|
| Crear pieza | Nueva fila / KPI ocupadas ↑ |
| Finalizar pieza | Pieza en limpieza; dashboard limpieza ↑ |
| Marcar limpia (limpieza) | Disponibles ↑ en dashboard |
| Pagar liquidación limpieza | `cleaning.earnings.updated` refresca earnings en limpieza móvil |

**Banner:** si se corta SSE, aparece "Tiempo real desconectado" o "Reconectando…"; polling 30s sigue como fallback en `cleaning` y `room-control`.

---

## 6. Páginas SSE totales post-P1

**10 pantallas** con `useOperationalEvents` / `useRoomOperationalEvents`:

1. `cleaning/index`
2. `services/room-control`
3. `services/room-services`
4. `rooms/cleaning`
5. `rooms/dashboard`
6. `settlements/index`
7. `shift-console`
8. `cash/index`
9. `cash/direct-sale`
10. `cashier/orders`

---

## 7. Tests

- Frontend: sin tests unitarios específicos de SSE (requiere mock EventSource); lógica de conexión validada manualmente + fix de handlers.
- Backend: **376 tests** sin cambios en P1.

---

*P1 cerrado. No iniciar V1-97 hasta QA operativo del flujo pieza → limpieza → liquidación en local.*
