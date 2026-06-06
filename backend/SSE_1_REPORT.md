# SSE-1 BASE — Infraestructura de Eventos en Tiempo Real
## Backend Report — V1-92

**Fecha:** 2026-06-06  
**Fase:** V1-92 — SSE-1 BASE  
**Estado:** COMPLETADA  
**Suite:** 353 tests, todos PASS

---

## Objetivo

Crear la infraestructura base de eventos en tiempo real sin acoplar aún a ningún módulo de negocio. Esta base será usada en V1-94 (SSE-2) para conectar limpieza, cajera, comandas y caja.

---

## Arquitectura

```
[Frontend] → POST /api/v1/events/token  (JWT auth)
           ← { token: "abc123", expires_in: 60 }

[Frontend] → GET /api/v1/events/stream?token=abc123&last_event_id=0
           ← text/event-stream (SSE)
              id: 42
              event: room_service.due
              data: { "room_service_id": 7 }
              : heartbeat
```

### Flujo de seguridad

- El JWT **nunca** viaja en la URL del stream.
- El frontend solicita un **token SSE** de corta duración (60s) vía endpoint autenticado.
- El stream valida el token SSE contra la tabla `sse_tokens`.
- El token lleva `tenant_id`, `branch_id`, `user_id`, `role_scope` — el stream solo sirve eventos para ese contexto.

---

## Tablas nuevas

### `operational_events`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT auto-increment | Event ID (usado como Last-Event-ID) |
| `tenant_id` | BIGINT | Aislamiento multi-tenant |
| `branch_id` | BIGINT | Aislamiento por sucursal |
| `type` | VARCHAR(80) | Tipo de evento (`room_service.due`, etc.) |
| `target_role` | VARCHAR(40), nullable | NULL = broadcast; 'cleaning', 'cashier', etc. = solo ese rol |
| `payload` | JSON | Datos del evento |
| `created_at` | TIMESTAMP | Hora en La Paz |

Índices compuestos para query eficiente: `(tenant_id, branch_id, id)` y `(tenant_id, branch_id, target_role, id)`.

### `sse_tokens`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | BIGINT | PK |
| `token` | VARCHAR(64) unique | String random 40 chars |
| `tenant_id` | BIGINT | Contexto del usuario |
| `branch_id` | BIGINT | Contexto del usuario |
| `user_id` | BIGINT | Quién pidió el token |
| `role_scope` | VARCHAR(40), nullable | Rol del usuario para filtrado |
| `expires_at` | TIMESTAMP | TTL 60 segundos |

---

## Archivos nuevos

### Dominio / Interfaces

| Archivo | Responsabilidad |
|---|---|
| `app/Domain/SSE/Repositories/OperationalEventRepositoryInterface.php` | Contratos: `create()`, `findSince()` |
| `app/Domain/SSE/Repositories/SseTokenRepositoryInterface.php` | Contratos: `create()`, `findValid()`, `purgeExpired()` |

### Infraestructura

| Archivo | Responsabilidad |
|---|---|
| `app/Infrastructure/Persistence/Eloquent/Models/OperationalEventModel.php` | Modelo Eloquent |
| `app/Infrastructure/Persistence/Eloquent/Models/SseTokenModel.php` | Modelo Eloquent + `isExpired()` |
| `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentOperationalEventRepository.php` | Impl. MySQL/SQLite |
| `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentSseTokenRepository.php` | Impl. MySQL/SQLite |

### Aplicación

| Archivo | Responsabilidad |
|---|---|
| `app/Application/SSE/Services/OperationalEventEmitter.php` | Entry point para emitir eventos desde cualquier Use Case |
| `app/Application/SSE/UseCases/IssueOperationalEventTokenUseCase.php` | Genera token SSE con TTL 60s, resuelve role_scope del usuario |

### HTTP

| Archivo | Responsabilidad |
|---|---|
| `app/Http/Controllers/Api/V1/EventsTokenController.php` | `POST /api/v1/events/token` |
| `app/Http/Controllers/Api/V1/EventsStreamController.php` | `GET /api/v1/events/stream` |

---

## Endpoints

### `POST /api/v1/events/token`

**Auth:** JWT requerido + branch context (X-Tenant-Slug + X-Branch-Code)

**Response:**
```json
{
  "data": {
    "token": "RaNdOmStRiNg40CharsLong...",
    "expires_in": 60
  }
}
```

### `GET /api/v1/events/stream?token=...&last_event_id=0`

**Auth:** Token SSE en query string (no JWT)

**Headers de respuesta:**
```
Content-Type: text/event-stream
Cache-Control: no-cache
X-Accel-Buffering: no
Connection: keep-alive
```

**Formato SSE:**
```
id: 42
event: room_service.due
data: {"room_service_id": 7, "room_label": "Hab. 3"}

: heartbeat
```

- Loop cada 2 segundos (configurable)
- Heartbeat cada ~30 segundos
- Reconnect automático via `Last-Event-ID` (EventSource del browser lo envía automáticamente)
- En `testing` env: loop 0 iteraciones → responde inmediatamente con `": connected"`

---

## Filtrado de eventos

Un evento es visible para un cliente si:

1. `tenant_id` coincide
2. `branch_id` coincide  
3. `target_role` ES NULL (broadcast) O `target_role` = `role_scope` del token

**Mapeo de roles a role_scope:**
- `tenant_owner` / admin → `null` (ve todos los eventos)
- `cashier` → `'cashier'`
- `waiter` → `'waiter'`
- `girl` → `'girl'`
- `cleaning` → `'cleaning'`

---

## Uso en Use Cases (V1-94)

Para emitir un evento desde cualquier Use Case:

```php
// Inyectar OperationalEventEmitter
public function __construct(
    private readonly OperationalEventEmitter $eventEmitter,
    // ... otras dependencias
) {}

// Emitir al terminar la lógica de negocio
$this->eventEmitter->emit(
    $tenantId,
    $branchId,
    'room_service.due',
    ['room_service_id' => $rs->id, 'room_label' => $rs->room_label],
    'cleaning'  // null = broadcast a todos
);
```

---

## Tests nuevos — `SseInfrastructureTest.php`

13 casos de prueba:

| # | Test | Cubre |
|---|---|---|
| 1 | Admin obtiene token SSE | Token endpoint funcional |
| 2 | Tenant distinto no lee eventos de otro tenant | Aislamiento multi-tenant |
| 3 | Stream sin token → 401 | Auth en stream |
| 4 | Stream con token inválido → 401 | Validación de token |
| 5 | Token expirado es rechazado | TTL de token |
| 6 | Stream con token válido → 200 text/event-stream | Stream funcional |
| 7 | `findSince` filtra por tenant | Aislamiento tenant en query |
| 8 | `findSince` filtra por branch | Aislamiento branch en query |
| 9 | `findSince` filtra por role (cleaning ve broadcast + cleaning, no cashier) | Filtrado por rol |
| 10 | `findSince` con `last_id` retorna solo eventos nuevos | Replay desde `Last-Event-ID` |
| 11 | `OperationalEventEmitter` persiste evento correctamente | Emitter funcional |
| 12 | Usuario cleaning recibe `role_scope = 'cleaning'` en token | Role scope correcto |
| 13 | Usuario admin recibe `role_scope = null` en token | Null scope para broadcast |

**353 tests, 2362 assertions, todos PASS.**

---

## Próximo paso: V1-94 SSE-2

Conectar el `OperationalEventEmitter` en los Use Cases de negocio:
- `CreateRoomServiceUseCase` → `room_service.created`
- `FinishRoomServiceUseCase` → `room_service.finished`
- `RoomServiceDueNotifier` → `room_service.due` (cleaning)
- `MarkRoomCleanUseCase` → `room.cleaned` (cajera)
- `SendOrderToBarUseCase` → `order.sent_to_bar`
