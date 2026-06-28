# Print Agent — Hosting Overload Audit (Backend)

**Fecha:** 2026-06-27  
**Alcance:** endpoints agente + coste por request — **sin implementación**

---

## 1. Endpoints agente (rutas públicas con device key)

Middleware: `nightpos.print-device` → `AuthenticatePrintDeviceMiddleware`

| Método | Ruta | Use case |
|--------|------|----------|
| POST | `/api/v1/print-devices/heartbeat` | `PrintDeviceHeartbeatUseCase` |
| GET | `/api/v1/print-jobs/pending` | `ListPendingPrintJobsUseCase` |
| POST | `/api/v1/print-jobs/{id}/claim` | Claim |
| POST | `/api/v1/print-jobs/{id}/printed` | Mark printed |
| POST | `/api/v1/print-jobs/{id}/failed` | Mark failed |

**URL legacy equivalente:** `/backend/public/api/v1/print-devices/heartbeat` (mismo Laravel).

---

## 2. Coste por request del agente

### Middleware auth (cada request agente)

`AuthenticatePrintDeviceMiddleware`:

1. `SELECT` print_devices por `device_key_prefix` (índice `device_key_prefix` ✓)
2. **`Hash::check()` bcrypt** sobre device_key — **costoso en CPU**
3. Carga contexto dispositivo

**No hay cache** de autenticación entre heartbeat y pending del mismo ciclo.

### Heartbeat (`recordHeartbeat`)

`EloquentPrintDeviceRepository::recordHeartbeat`:

```php
PrintDeviceModel::query()->...->update([
    'last_seen_at' => now(),
    'printer_name' => ...,
    'agent_version' => ...,
    'last_error' => ...,
]);
```

| Pregunta | Respuesta |
|----------|-----------|
| ¿Write DB cada heartbeat? | **Sí — UPDATE siempre** aunque nada cambie |
| ¿Throttle last_seen? | **No** |
| ¿Queries extra? | 1 UPDATE (+ auth arriba) |

### Pending jobs

`listPending(tenant_id, branch_id, limit)` — SELECT con índice `(tenant_id, branch_id, status, created_at)` ✓

Liviano si no hay jobs; **igual ejecuta auth bcrypt completo**.

---

## 3. Rate limiting

| Capa | Estado |
|------|--------|
| Laravel throttle en rutas print-device | **No** |
| Middleware tenant/branch en agente | No aplica — solo device key |
| CloudLinux / ModSecurity | **Desconocido** — revisar en cPanel al encender agente |

---

## 4. URL incorrecta `/api/v1`

Si rewrite raíz no funciona o hosting inestable:

| Respuesta | Efecto agente |
|-----------|---------------|
| Connection reset | Log `wsarecv`; agente reintenta al mismo intervalo |
| 404 HTML | `invalid API response` — no es `IsNetworkError` pero **sigue polling** |
| 503 / timeout | Tratado como error; **sin backoff** |

El backend **no recibe** el request en reset — el daño es **carga de conexiones** y saturación del servidor web.

---

## 5. Índices relevantes

| Tabla | Índice |
|-------|--------|
| print_devices | `device_key_prefix`, `(tenant_id, branch_id, status)` |
| print_devices | `(tenant_id, enabled, last_seen_at)` — migración performance |
| print_jobs | `(tenant_id, branch_id, status, created_at)` |

Esquema OK para volumen **normal**. No diseñado para **10+ req/s** del mismo cliente.

---

## 6. Probe externo 2026-06-27

```
/backend/public/health.php → connection reset (~300 ms)
POST /api/v1/print-devices/heartbeat → connection reset
```

Hosting ya colapsado en probe — confirma fragilidad base; agente empeora.

---

## 7. Fix recomendado (futuro — no implementado)

### Backend

1. **Throttle heartbeat DB:** actualizar `last_seen_at` como máximo cada 30–60 s si datos iguales
2. **Cache auth device** 30–60 s por device_key_prefix (evitar bcrypt cada 100 ms)
3. **Rate limit** suave: p.ej. 6 req/min por device_key en heartbeat
4. Respuesta heartbeat **204 liviana** sin side effects pesados

### Operación inmediata (sin código)

1. Detener agente en todas las PCs
2. Recuperar hosting
3. Agentes con URL legacy + intervalo ≥ 15 s

---

## 8. Respuestas cruzadas

| # | Respuesta |
|---|-----------|
| 4 | **Sí** — UPDATE en cada heartbeat exitoso |
| 5 | **Probable** bloqueo por entry processes; revisar error_log cPanel |
| 8 | Throttle write + cache auth + rate limit (futuro) |

---

## 9. Relacionados

- `agent/PRINT_AGENT_HOSTING_OVERLOAD_AUDIT.md`
- `tests/Feature/Api/V1/LocalPrintAgentTest.php`
