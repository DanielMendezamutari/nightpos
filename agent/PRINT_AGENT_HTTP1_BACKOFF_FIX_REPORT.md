# Print Agent — HTTP/1.1 + Backoff Fix Report

**Fecha:** 2026-06-28  
**Alcance:** solo agente Go — compatibilidad cPanel/LiteSpeed  
**Versión agente:** 2.0.0 (mismo número; rebuild EXE requerido)

---

## 1. Problema

Con URL legacy correcta (`/backend/public/api/v1`) y `poll_interval_ms=15000`, el agente seguía recibiendo:

- `wsarecv: conexión interrumpida por el host remoto`
- `stream error: CANCEL` (HTTP/2)

Causa probable: cliente Go con transport por defecto (HTTP/2 + User-Agent Go) + hosting LiteSpeed inestable.

---

## 2. Cambios implementados

### HTTP/1.1 forzado (`internal/api/client.go`)

- `ForceAttemptHTTP2: false`
- `TLSNextProto` vacío (sin ALPN h2)
- Keep-alive: dial 30 s, idle 90 s, max 2 conexiones idle por host
- Timeout request: 30 s

### User-Agent propio

Todas las requests:

```http
User-Agent: NightPOSPrintAgent/2.0
```

### `IsNetworkError` ampliado

Detecta: stream error, CANCEL, forcibly closed, wsarecv, empty reply, connection reset, EOF, timeout, etc.

### Backoff en fallos de red (`internal/agent/runtime.go`)

Secuencia tras error de red en heartbeat o pending:

| Fallo consecutivo | Espera antes del siguiente intento |
|-------------------|-------------------------------------|
| 1 | 30 s |
| 2 | 60 s |
| 3 | 120 s |
| 4+ | 300 s (máximo) |

- Primer heartbeat **exitoso** → reset backoff → vuelve a `poll_interval_ms`
- Errores 401/403/config **no** activan backoff (solo red)

### Config producción

`config.production.example.json`:

- `backend_url`: `https://nightpos.ribersoft.com/backend/public/api/v1`
- `poll_interval_ms`: **15000**

---

## 3. Sin cambios

- Flujo claim / print / failed
- Backend Laravel
- Frontend / PWA / POS

---

## 4. Compilar e instalar

```powershell
cd C:\xampp\htdocs\nightpos\agent
go build -ldflags "-H=windowsgui" -o NightPOSPrintAgent.exe .
```

Copiar EXE a `C:\Program Files\NightPOS\PrintAgent\` (o ruta instalada).

Config en `C:\ProgramData\NightPOS\PrintAgent\config.json`:

```json
{
  "backend_url": "https://nightpos.ribersoft.com/backend/public/api/v1",
  "device_key": "npd_live_...",
  "printer_name": "POS-80",
  "poll_interval_ms": 15000,
  "dry_run": false,
  "log_level": "info"
}
```

Reiniciar:

```powershell
NightPOSPrintAgent.exe --restart
```

---

## 5. Validación (10 minutos)

Revisar `C:\ProgramData\NightPOS\PrintAgent\logs\agent.log`:

**Esperado si OK:**

```text
[INFO] dry_run=false poll_interval_ms=15000
[DEBUG] Device online — backend OK
```

**Si hosting falla temporalmente:**

```text
[WARN] Error conexión backend: ...
[INFO] Backoff hosting: próximo intento en 30s (fallo de red #1)
```

**No debe repetirse** cada 15 s el mismo wsarecv sin línea de backoff.

Tras recuperación:

```text
[DEBUG] Device online — backend OK
```

(sin backoff hasta el próximo fallo)

---

## 6. Relacionados

- `agent/PRINT_AGENT_HEARTBEAT_CONNECTION_RESET_DEEP_AUDIT.md`
- `agent/INSTALLATION_GUIDE.md`
- `agent/TROUBLESHOOTING_GUIDE.md`
