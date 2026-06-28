# Print Agent — Deep Audit: Connection Reset on Heartbeat

**Fecha:** 2026-06-28  
**Síntoma:** agente con URL legacy correcta y `poll_interval_ms=15000` sigue fallando:

```text
Post ".../backend/public/api/v1/print-devices/heartbeat":
read tcp 192.168.1.3:64926->216.246.47.153:443:
wsarecv: Se ha forzado la interrupción de una conexión existente por el host remoto.
```

**Alcance:** agente Go + pruebas curl — **sin implementación**

---

## 1. Conclusión ejecutiva

**Ya no es un problema de URL ni de poll_interval.**

Con la config correcta, el agente hace **un POST cada 15 s** y el hosting **corta la conexión TCP/TLS durante la lectura de la respuesta** (`wsarecv` reset).

Evidencia externa (probes 2026-06-28):

| Prueba | Resultado |
|--------|-----------|
| GET `/backend/public/api/v1/health` | **200 JSON** cuando hosting estable; **reset intermitente** |
| POST heartbeat **sin** auth | **401 JSON** — Laravel responde, no reset |
| POST heartbeat auth inválida | **401 JSON** en ~0.7 s — middleware OK |
| Ráfaga POST (2–5 s aparte) | 1º **401**, siguientes **connection reset** |
| GET health ×8 cada 15 s | 3 primeros **reset**, 5 siguientes **200** |

→ El patrón encaja con **hosting inestable / entry processes / WAF**, no con bug de intervalo del agente.

**Hipótesis HTTP/2 (Go): plausible pero no confirmada** desde esta máquina (curl Windows sin `--http2`). El cliente Go **sí usa HTTP/2 por defecto** en HTTPS.

---

## 2. Config confirmada del usuario

```json
{
  "backend_url": "https://nightpos.ribersoft.com/backend/public/api/v1",
  "poll_interval_ms": 15000,
  "dry_run": false
}
```

Log: solo `[WARN] Error conexión backend` cada **15 s** (07:49:30 → 07:49:45).  
→ **Un request por tick** (heartbeat falla → no hay `pending`).

---

## 3. Cliente HTTP del agente (código)

Archivo: `agent/internal/api/client.go`

| Aspecto | Estado actual |
|---------|---------------|
| Cliente | `http.Client{ Timeout: 30 * time.Second }` — **transport por defecto** |
| HTTP/2 | **Habilitado automáticamente** en HTTPS (Go `DefaultTransport`) |
| HTTP/1.1 forzado | **No** — no hay `TLSNextProto` ni `ForceAttemptHTTP2: false` |
| Keep-alive | Default (keep-alive **activo**) |
| User-Agent | **No se setea** → `Go-http-client/1.1` o `Go-http-client/2.0` |
| Authorization | `Bearer {device_key}` |
| Content-Type | `application/json` en POST |
| Retry | **Ninguno** — un `Do()` por tick |
| Backoff | **Ninguno** |

### Payload heartbeat real del agente

```json
{
  "printer_name": "<config>",
  "agent_version": "2.0.0"
}
```

Pequeño, sin campos SAAS (`host_name`, etc.).

### Detección de error de red

`IsNetworkError()` busca: `timeout`, `connection refused`, `connection reset`, etc.

**No incluye:**

- `stream error` / `CANCEL` (HTTP/2)
- `forcibly closed` / mensaje wsarecv en español sin "connection reset"
- `empty reply from server`

---

## 4. Ciclo runtime — ¿más requests de los visibles?

`agent/internal/agent/runtime.go`:

1. `Heartbeat` POST
2. Si error → **return** (no `Pending`)
3. Si OK → GET `pending?limit=5`

Con reset en heartbeat → **solo 1 POST/15 s**. No hay retries internos.

### Procesos Windows (verificar en PC sucursal)

```powershell
Get-Service NightPOSPrintAgent
Get-Process | Where-Object { $_.ProcessName -like "*NightPOS*" }
netstat -ano | findstr ":443"
```

Esperado: **1 servicio RUNNING** + opcional tray (tray **no** hace polling).

---

## 5. Pruebas curl obligatorias — resultados

Ejecutadas desde entorno de auditoría hacia producción.

### 5.1 GET health

```text
HTTP/1.1 200 OK
Content-Type: application/json
alt-svc: h3=":443"; ma=2592000, ...
```

Tiempo ~0.65–1.1 s cuando responde.

### 5.2 POST sin auth

```bash
curl -X POST .../print-devices/heartbeat -H "Content-Type: application/json" -d "{}"
```

```json
{"success":false,"message":"Clave de dispositivo inválida.","data":{},"errors":{}}
HTTP 401 — ~1.2 s
```

**No reset** → POST llega a Laravel cuando hosting estable.

### 5.3 POST auth inválida (HTTP/1.1)

```text
401 JSON — ~0.71 s
```

Bearer malformado → middleware responde JSON rápido.

### 5.4 HTTP/2 vs HTTP/1.1

- `--http1.1`: probado, funciona cuando hosting estable
- `--http2`: **curl Windows de auditoría no soporta `--http2`**
- **Pendiente en PC sucursal** (Git Bash / WSL / curl reciente)

### 5.5 User-Agent Go

```bash
curl -A "Go-http-client/2.0" -X POST .../heartbeat ...
```

Un intento: **`curl: (52) Empty reply from server`** — mismo síntoma que reset (servidor cierra sin HTTP).

### 5.6 Estabilidad temporal (GET ×8 / 15 s)

| Intento | Resultado |
|---------|-----------|
| 1–3 | connection reset (~0.29 s) |
| 4–8 | 200 OK (~0.67 s) |

→ Hosting **intermitente**; el agente puede fallar aunque el navegador funcione momentos después.

---

## 6. Respuestas a las 14 preguntas

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿GET health estable? | **Intermitente** — OK cuando hosting despejado |
| 2 | ¿POST sin auth JSON o reset? | **JSON 401** cuando hosting responde |
| 3 | ¿POST con auth JSON o reset? | Inválida: **401 JSON**. Válida: **no probada aquí** (requiere key real en PC sucursal) |
| 4 | ¿Falla solo HTTP/2? | **No confirmado** — falta curl --http2 en sucursal |
| 5 | ¿Falla solo User-Agent Go? | **Un empty reply** observado; no conclusivo solo |
| 6 | ¿Laravel registra error? | Sin acceso cPanel — POST sin auth **sí llega** (401) |
| 7 | ¿ModSecurity bloquea POST? | **Posible** en saturación; sin logs no confirmado |
| 8 | ¿Faltan migraciones? | Riesgo si key válida + UPDATE falla → ver backend audit |
| 9 | ¿Múltiples procesos agente? | Verificar manualmente en PC |
| 10 | ¿Cliente Go usa HTTP/2? | **Sí, por defecto** en HTTPS |
| 11 | ¿Forzar HTTP/1.1? | **Recomendado probar** — fix candidato #1 |
| 12 | ¿User-Agent propio? | **Recomendado probar** — fix candidato #2 |
| 13 | ¿Desactivar keep-alive? | Opcional secundario; probar tras HTTP/1.1 |
| 14 | ¿Cambio mínimo seguro? | Ver sección 8 |

---

## 7. Diagnóstico por capa

```
┌─────────────┐     reset en read      ┌──────────────────┐
│ Agente Go   │ ─────────────────────► │ LiteSpeed/cPanel │
│ HTTP/2?     │                        │ Entry processes  │
│ 1 POST/15s  │                        │ ModSecurity WAF  │
└─────────────┘                        └────────┬─────────┘
                                                │
                     401 JSON cuando OK ◄───────┘
                     Laravel + middleware
```

| Capa | Probabilidad | Evidencia |
|------|--------------|-----------|
| **Hosting inestable** | **Alta** | GET health también resetea; ráfagas fallan |
| **HTTP/2 Go + LiteSpeed** | **Media** | Go usa HTTP/2; usuario vio `stream CANCEL` |
| **ModSecurity POST** | **Media** | Sin logs; POST OK cuando hosting estable |
| **Laravel heartbeat bug** | **Baja** | 401/422 llegan como JSON |
| **Poll / URL** | **Descartado** | Config correcta + mismo síntoma |

---

## 8. Fixes candidatos (NO implementados — orden sugerido)

### P0 — Manual (ahora, sin código)

1. Desde **PC sucursal** (192.168.1.3), ejecutar las 5 pruebas curl con **device_key real**
2. Revisar cPanel: **Metrics → Errors**, **ModSecurity**, **Resource Usage**
3. Confirmar migración `2026_06_25_150000_saas_15_platform_operations.php` en hosting
4. Verificar **una sola instancia** del servicio

### P1 — Agente (cuando se programe)

1. **Forzar HTTP/1.1:**

```go
transport := &http.Transport{
    ForceAttemptHTTP2: false,
    TLSNextProto:      map[string]func(string, *tls.Conn) http.RoundTripper{},
}
```

2. **User-Agent:** `NightPOSPrintAgent/2.0`
3. **Ampliar `IsNetworkError`:** `stream error`, `CANCEL`, `forcibly closed`, `empty reply`
4. **Backoff exponencial** en cualquier error de transporte (30s → 5min)
5. Log de protocolo usado en debug (opcional)

### P2 — Hosting

- Whitelist ModSecurity para `POST .../print-devices/heartbeat`
- Subir límites entry processes / revisar procesos zombie lsphp
- Desactivar HTTP/2 en vhost si confirma prueba curl --http2

---

## 9. Pruebas pendientes en PC sucursal

Copiar y ejecutar en PowerShell de la sucursal (reemplazar `npd_live_XXXX`):

```powershell
# 1. GET
curl.exe -i https://nightpos.ribersoft.com/backend/public/api/v1/health

# 2. POST sin auth
curl.exe -i -X POST https://nightpos.ribersoft.com/backend/public/api/v1/print-devices/heartbeat -H "Content-Type: application/json" -d "{}"

# 3. POST con key real
curl.exe -i -X POST https://nightpos.ribersoft.com/backend/public/api/v1/print-devices/heartbeat `
  -H "Authorization: Bearer npd_live_XXXX" `
  -H "Content-Type: application/json" `
  -d "{\"agent_version\":\"2.0.0\",\"printer_name\":\"CAJA\"}"

# 4. HTTP/1.1 explícito (si curl lo soporta)
curl.exe -i --http1.1 -X POST ... (igual que 3)

# 5. User-Agent Go
curl.exe -i -A "Go-http-client/2.0" -X POST ... (igual que 3)
```

**Interpretación:**

| Resultado 3 | Diagnóstico |
|-------------|-------------|
| JSON success | Agente debería funcionar → culpa HTTP/2/UA/transport Go |
| 401/403 JSON | Key/dispositivo — no reset |
| Reset | Hosting/WAF — no culpa agente |
| 500 JSON | Laravel/DB — ver backend audit |

---

## 10. Relacionados

- `backend/PRINT_AGENT_HEARTBEAT_CONNECTION_RESET_DEEP_AUDIT.md`
- `fix agente de impresion/agent/PRINT_AGENT_REGRESSION_DIFF_AUDIT.md`
- `agent/PRINT_AGENT_HOSTING_OVERLOAD_AUDIT.md`
