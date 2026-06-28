# Print Agent — Deep Audit Backend: Heartbeat Connection Reset

**Fecha:** 2026-06-28  
**Síntoma:** POST heartbeat legacy URL → connection reset en agente (no JSON)  
**Alcance:** middleware, use case, migraciones, logs — **sin implementación**

---

## 1. Conclusión

Cuando el hosting está **estable**, el endpoint heartbeat **responde JSON**:

- Sin Bearer → **401** `Clave de dispositivo inválida.`
- Bearer inválido → **401** en ~0.7 s

Eso prueba que **Laravel recibe el POST** y el middleware `nightpos.print-device` ejecuta antes de resetear.

El reset del agente (`wsarecv`) ocurre **antes o durante** la entrega HTTP completa — típico de:

1. **LiteSpeed/cPanel** cerrando conexión (entry processes, timeout, WAF)
2. **HTTP/2** cancelado por el servidor (compatible con `stream CANCEL`)
3. **Inestabilidad intermitente** (GET health también resetea en ráfagas)

**Menos probable:** error PHP fatal en heartbeat — normalmente dejaría rastro en log o 500 JSON.

---

## 2. Ruta y middleware

`backend/routes/api.php`:

```php
Route::middleware('nightpos.print-device')->group(function () {
    Route::post('print-devices/heartbeat', [PrintDeviceController::class, 'heartbeat']);
    // ...
});
```

URL legacy: `/backend/public/api/v1/print-devices/heartbeat`

### `AuthenticatePrintDeviceMiddleware`

Archivo: `app/Infrastructure/Laravel/Http/Middleware/AuthenticatePrintDeviceMiddleware.php`

| Paso | Acción |
|------|--------|
| 1 | `$request->bearerToken()` |
| 2 | Validar prefijo `npd_live_` |
| 3 | `findByKeyPrefix(substr 12)` — SELECT indexado |
| 4 | **`Hash::check()` bcrypt** — CPU por request autenticado |
| 5 | Verificar `enabled` + `status=ACTIVE` |
| 6 | `$next($request)` |

**Bearer en legacy path:** `backend/public/.htaccess` reenvía `HTTP_AUTHORIZATION` — probado (401 JSON en POST).

Excepciones → JSON vía `PrintingDomainException` (401/403/422) en `bootstrap/app.php`.

---

## 3. Heartbeat use case

`PrintDeviceHeartbeatUseCase` → `recordHeartbeat()`:

Campos desde agente Go actual:

- `printer_name`, `agent_version`, `last_error` (vacío)

Campos SAAS opcionales (`host_name`, `os_name`, …) — **agente no los envía**.

### UPDATE en cada heartbeat exitoso

```php
$updates = [
    'last_seen_at' => now(),
    'printer_name' => $printerName,
    'agent_version' => $agentVersion,
    'last_error' => $lastError,
];
// + campos SAAS solo si !== null en input
```

**Siempre escribe DB** en heartbeat OK — mismo coste que estado estable anterior.

---

## 4. Migraciones — riesgo hosting

| Migración | Columnas nuevas en `print_devices` |
|-----------|--------------------------------------|
| `2026_06_17_100000_*` | Tabla base |
| `2026_06_25_150000_saas_15_platform_operations` | `host_name`, `os_name`, `os_version`, `arch`, `ip_address`, `printer_model`, `last_printed_at`, `installed_at` |
| `2026_06_25_160000_hosting_performance_indexes` | Índice `(tenant_id, enabled, last_seen_at)` |

### ¿Faltan columnas tumba heartbeat?

Con agente Go **actual**, el UPDATE solo toca columnas base — **no debería fallar** si falta migración SAAS.

**Riesgo** si en hosting:

- Código desplegado incluye campos SAAS en repo pero agente no los manda → OK
- Otro cliente (futuro) manda `host_name` y columna no existe → **500 SQL**
- Índice performance no aplicado → más lento, no reset

**Verificar en hosting:**

```bash
php artisan migrate:status | grep saas_15
# o revisar columnas print_devices en phpMyAdmin
```

Si heartbeat con **key válida** devuelve **500 JSON** (no reset) → revisar `storage/logs/laravel.log`.

---

## 5. ¿Laravel registra el request?

| Escenario | Qué buscar |
|-----------|------------|
| Reset antes de PHP | **Sin** entrada en `laravel.log` |
| 401 key inválida | Middleware exception — puede no loguear ERROR |
| 500 SQL | Stack trace en `laravel.log` |
| ModSecurity | Entrada en **error_log cPanel** / ModSecurity audit |

Comando (en servidor):

```bash
tail -n 100 storage/logs/laravel.log
tail -n 100 ~/public_html/error_log
# cPanel → Metrics → Errors
```

**Probe externo:** POST sin auth → 401 JSON → **request llegó a Laravel**.

Con **key válida** desde sucursal: si reset **sin** log → capa web, no aplicación.

---

## 6. Auth costosa

| Caso | bcrypt | Tiempo observado |
|------|--------|------------------|
| Sin token / prefijo mal | No | Rápido → 401 |
| Prefix no existe en DB | No | Rápido → 401 |
| Prefix existe, hash wrong | **Sí** | ~0.7 s → 401 |
| Prefix + hash OK | **Sí** + UPDATE | ~0.7–1.5 s esperado |

bcrypt **no explica reset** por sí solo — devuelve JSON.  
Solo contribuye a **carga CPU** si hay muchos requests (no el caso a 15 s).

---

## 7. Pruebas curl — resultados backend

| Prueba | HTTP | Body |
|--------|------|------|
| GET `/api/v1/health` | 200 | `{"ok":true,...}` |
| POST heartbeat `{}` | 401 | `Clave de dispositivo inválida.` |
| POST Bearer inválido | 401 | JSON |
| Ráfaga POST | 1×401, luego reset | Hosting saturado |

**No probado aquí:** POST con **device_key válida** (secreto — debe hacerse en sucursal).

---

## 8. ModSecurity / WAF — hipótesis B

Señales a favor:

- POST puede disparar reglas (JSON body, Bearer, ruta poco común)
- GET health más liviano — aunque **también resetea** cuando hosting mal

Señales en contra:

- POST sin auth devuelve 401 JSON (regla no bloquea todo POST)

**Acción:** revisar ModSecurity hit list en cPanel al momento exacto del heartbeat del agente.

Posibles reglas:

- Authorization Bearer largo
- User-Agent `Go-http-client/*`
- Rate limit por IP

---

## 9. HTTP/2 — hipótesis A

Servidor anuncia HTTP/1.1 200 en health con `alt-svc: h3=...`

Go `http.Client` negocia **HTTP/2** por defecto en TLS.

Si LiteSpeed cancela stream HTTP/2:

```text
stream error: stream ID 1; CANCEL; received from peer
```

→ Encaja con error del usuario (distinto wording de `wsarecv` reset, pero misma familia).

**Confirmación:** curl `--http2` vs `--http1.1` en PC sucursal con key real.

---

## 10. Respuestas cruzadas (preguntas auditoría)

| # | Respuesta |
|---|-----------|
| 6 | Con POST 401 probado → **sí llega a Laravel** cuando hosting OK |
| 7 | ModSecurity **posible**, no confirmado sin logs |
| 8 | Migración SAAS **verificar**; agente actual no debería romper por columnas SAAS |
| 14 | Fix mínimo: estabilizar hosting + probar HTTP/1.1 en agente; backend heartbeat **no requiere cambio urgente** |

---

## 11. Fixes backend (solo si pruebas lo exigen)

| Diagnóstico | Fix |
|-------------|-----|
| 500 SQL column missing | `php artisan migrate` |
| bcrypt lento bajo carga | Cache auth device 60 s (futuro) |
| UPDATE cada 15 s OK | Throttle `last_seen_at` (futuro, no urgente) |
| ModSecurity | Whitelist ruta heartbeat en hosting |

**No cambiar** rutas ni contrato heartbeat hasta tener curl con key válida.

---

## 12. Checklist servidor (manual)

- [ ] `migrate:status` incluye `saas_15` y `hosting_performance_indexes`
- [ ] `laravel.log` en timestamp 07:49:31 del agente
- [ ] cPanel Errors / ModSecurity
- [ ] CloudLinux CPU / EP / PMEM en ese minuto
- [ ] curl POST con key válida → JSON success

---

## 13. Relacionados

- `agent/PRINT_AGENT_HEARTBEAT_CONNECTION_RESET_DEEP_AUDIT.md`
- `backend/PRINT_AGENT_HOSTING_OVERLOAD_AUDIT.md`
- `fix agente de impresion/backend/PRINT_AGENT_REGRESSION_DIFF_AUDIT.md`
