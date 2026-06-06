# SSE-1 BASE — Infraestructura de Eventos en Tiempo Real
## Frontend Report — V1-92

**Fecha:** 2026-06-06  
**Fase:** V1-92 — SSE-1 BASE  
**Estado:** COMPLETADA

---

## Composable: `useOperationalEvents`

`frontend/src/composables/useOperationalEvents.js`

### API pública

```javascript
const { on, off, start, stop } = useOperationalEvents()

// Suscribirse a un tipo de evento
on('room_service.due', (payload) => {
  console.log('Pieza vencida:', payload.room_service_id)
})

// Suscribirse a todos los eventos
on('*', ({ type, data }) => {
  console.log('Evento:', type, data)
})

// Desuscribirse
off('room_service.due', handler)

// Iniciar la conexión (llamar en onMounted)
start()

// Detener la conexión (llamar en onUnmounted o en logout)
stop()
```

### Ciclo de vida

1. **`start()`** → llama `POST /api/v1/events/token` → obtiene token SSE
2. Abre `EventSource("/api/v1/events/stream?token=...&last_event_id=N")`
3. Los eventos con `event: tipo_evento` se despachan a los handlers registrados con `on(tipo, fn)`
4. En caso de error → espera `backoff` milisegundos y reconecta (exponential, máx 30s)
5. Si el usuario hace logout (`auth.isLoggedIn` → false) → `stop()` automático
6. Si cambia tenant/sucursal (`context.version`) → `stop()` + reinicia con nuevo contexto y `last_event_id = 0`

### Detalles técnicos

| Aspecto | Comportamiento |
|---|---|
| **Token TTL** | 60 s — el token se obtiene antes de abrir EventSource |
| **Reconnect** | Automático en error; backoff inicial 2s, máximo 30s |
| **last_event_id** | El browser lo envía como header `Last-Event-ID` en reconexión; también como query param en token refresh |
| **Heartbeat** | `: heartbeat` del servidor mantiene la conexión viva |
| **Multi-tenancy** | Al cambiar contexto, se reinicia con new token + `last_event_id=0` |
| **Cleanup** | `stop()` cierra EventSource y cancela timer de reconexión |

---

## API helper: `fetchSseToken`

`frontend/src/api/events.js`

```javascript
import { fetchSseToken } from '@/api/events'

const { token, expires_in } = await fetchSseToken()
```

Llama `POST /api/v1/events/token` con las credenciales y el contexto (tenant/branch) del usuario autenticado.

---

## Ejemplo de uso en una página

```javascript
// En una página de control que necesita actualizaciones en tiempo real
import { useOperationalEvents } from '@/composables/useOperationalEvents'

const { on, start, stop } = useOperationalEvents()

onMounted(() => {
  on('room_service.due', (payload) => {
    // Mover la pieza de "activas" a "vencidas" sin recargar
    moveServiceToDue(payload.room_service_id)
  })

  on('room.cleaned', () => {
    // Recargar la lista de habitaciones
    loadRooms()
  })

  start()
})

onUnmounted(() => {
  stop()
})
```

---

## NO conectado aún (V1-94)

El composable está listo pero ninguna página lo usa todavía. La conexión a páginas específicas se hará en V1-94:

- Control piezas (limpieza) → `room_service.due`, `room_service.created`
- Cajera → `room.cleaned`, `order.sent_to_bar`
- Fiscalización multicaja → `cash.movement.created`, `sale.created`

---

## Archivos nuevos

| Archivo | Descripción |
|---|---|
| `src/api/events.js` | `fetchSseToken()` — llama POST /events/token |
| `src/composables/useOperationalEvents.js` | Composable completo de SSE |
