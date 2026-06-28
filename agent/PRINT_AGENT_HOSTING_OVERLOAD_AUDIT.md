# Print Agent — Hosting Overload Audit (Agente)

**Fecha:** 2026-06-27  
**Síntoma:** al encender agente → `ERR_CONNECTION_RESET` en navegador; log `wsarecv: conexión interrumpida por el host remoto` en heartbeat.  
**Alcance:** auditoría — **sin implementación**

---

## 1. Conclusión ejecutiva

**Sí — el agente es un detonante muy probable** del colapso en hosting compartido, por **combinación**:

| Factor | Severidad | Evidencia |
|--------|-----------|-----------|
| URL incorrecta `/api/v1` vs rollback `/backend/public/api/v1` | **Alta** | Log del usuario apunta a `/api/v1/print-devices/heartbeat` |
| `poll_interval_ms` muy bajo (100 ms en repo; default código 1500 ms) | **Crítica** | 100 ms = **~10 heartbeats/seg** sin backoff |
| Sin backoff en errores | **Alta** | Mismo intervalo aunque falle red o hosting |
| 2 requests HTTP por ciclo exitoso (heartbeat + pending) | **Media** | Duplica carga PHP |
| Sin rate limit en agente ni throttling adaptativo | **Alta** | Bucle agresivo indefinido |

El hosting ya era frágil (resets sin agente). El agente mal configurado **amplifica** entry processes LiteSpeed/CloudLinux hasta tumbar el dominio.

---

## 2. Config real vs repo

| Ubicación | ¿La usa el servicio? |
|-----------|----------------------|
| `C:\ProgramData\NightPOS\PrintAgent\config.json` | **SÍ** — `config.Load()` → `paths.ConfigPath()` |
| `agent/config.json` del repo | **NO** — solo referencia dev |

**Repo `agent/config.json` (no es la config del servicio):**

```json
{
  "backend_url": "http://nightpos.test/api/v1",
  "poll_interval_ms": 100
}
```

El valor **100 ms** es peligroso para producción. Si se copió a ProgramData, explica el colapso.

**Log producción del usuario:**

```
POST https://nightpos.ribersoft.com/api/v1/print-devices/heartbeat
```

**Rollback actual exige:**

```
https://nightpos.ribersoft.com/backend/public/api/v1
```

→ **URL incorrecta confirmada** respecto al estado post-rollback PWA.

---

## 3. Ciclo del agente (código)

Archivo: `internal/agent/runtime.go`

```
cada poll_interval_ms:
  1. POST /print-devices/heartbeat
  2. si OK → GET /print-jobs/pending?limit=5
  3. por cada job → claim + printed/failed
```

| Pregunta | Respuesta |
|----------|-----------|
| ¿Nueva conexión HTTP cada ciclo? | Sí — `http.Client` por request (`client.go`); timeout **30 s** |
| ¿Backoff si falla? | **No** — sigue al mismo intervalo |
| ¿Retry agresivo? | **Sí** — ticker fijo sin pausa |
| ¿Claim si heartbeat falló? | **No** — `return` tras error heartbeat |
| ¿Múltiples loops en un EXE? | **No** — solo el servicio Windows ejecuta `Runtime.loop`; `--tray` es UI |

**Servicio Windows:** `NightPOSPrintAgent` (`paths.ServiceName`). Recovery: reinicio automático a 60 s si cae (`install.go`).

---

## 4. Carga estimada (requests/minuto)

| poll_interval_ms | Heartbeats/min | + pending/min (si OK) | Total HTTP/min |
|------------------|----------------|------------------------|----------------|
| **100** | **600** | **600** | **~1200** |
| 1500 (default código) | 40 | 40 | ~80 |
| 10000 (recomendado hosting) | 6 | 6 | ~12 |
| 15000 | 4 | 4 | ~8 |

En hosting compartido, **cada request** ≈ 1 proceso PHP (lsphp) + bootstrap Laravel + **bcrypt verify** en middleware.

**600 heartbeats/min** puede agotar entry processes aunque el backend responda bien.

---

## 5. Prueba A/B obligatoria (manual)

### A — Apagar agente

```powershell
NightPOSPrintAgent.exe --stop
# o: sc stop NightPOSPrintAgent
```

Esperar 1–2 min. Probar:

```powershell
curl.exe -i https://nightpos.ribersoft.com/backend/public/health.php
curl.exe -i https://nightpos.ribersoft.com/backend/public/api/v1/auth/login-context/tenants
```

**Si el sitio vuelve:** agente es detonante (o contribuye de forma decisiva).

### B — Config segura + encender

Editar **`C:\ProgramData\NightPOS\PrintAgent\config.json`**:

```json
{
  "backend_url": "https://nightpos.ribersoft.com/backend/public/api/v1",
  "device_key": "npd_live_...",
  "printer_name": "...",
  "poll_interval_ms": 15000,
  "dry_run": true,
  "log_level": "info"
}
```

```powershell
NightPOSPrintAgent.exe --restart
```

Monitorear sitio 5 min. Subir intervalo antes de bajar `dry_run`.

### C — Instancias duplicadas

```powershell
Get-Service NightPOSPrintAgent
Get-Process | Where-Object { $_.ProcessName -like "*NightPOS*" }
tasklist | findstr /I NightPOS
```

Debe haber **1 servicio RUNNING** + opcional tray; **no** dos servicios ni `--run` en consola simultáneo.

### D — Medir 2 minutos

Contar líneas en `C:\ProgramData\NightPOS\PrintAgent\logs\agent.log`:

- `Device online`
- `Error conexión backend`

Con `poll_interval_ms=15000` → ~8 heartbeats en 2 min.

---

## 6. Respuestas directas (1–10)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿URL incorrecta? | **Sí** — log usa `/api/v1`; rollback exige `/backend/public/api/v1` |
| 2 | ¿Golpea demasiado seguido? | **Sí** si `poll_interval_ms` ≤ 1500; **crítico** a 100 ms |
| 3 | ¿Múltiples instancias? | Verificar manualmente; código no lanza doble loop en un servicio |
| 4 | ¿Heartbeat escribe DB siempre? | Backend: **UPDATE en cada heartbeat exitoso** (ver audit backend) |
| 5 | ¿Hosting bloquea por rate? | **Probable** en CloudLinux/LiteSpeed; no hay evidencia de logs aquí |
| 6 | Config segura hosting | `backend_url` legacy; `poll_interval_ms` **≥ 10000**; `dry_run` para prueba |
| 7 | Cambios agente (futuro) | Backoff exponencial; mínimo 10 s; no pending si heartbeat falló (ya); cap requests |
| 8 | Cambios backend (futuro) | Throttle heartbeat writes; cache auth device |
| 9 | Pasos manuales ahora | **Stop agente** → recuperar sitio → corregir config → restart con 15 s |
| 10 | Plan definitivo | URL unificada + intervalo ≥10 s + backoff + heartbeat DB throttled |

---

## 7. Config recomendada inmediata (manual)

```json
{
  "backend_url": "https://nightpos.ribersoft.com/backend/public/api/v1",
  "poll_interval_ms": 15000,
  "dry_run": false,
  "log_level": "info"
}
```

Plantilla: `agent/config.production.example.json`

**No usar** `poll_interval_ms: 100` ni `1500` en hosting compartido.

---

## 8. Relacionados

- `backend/PRINT_AGENT_HOSTING_OVERLOAD_AUDIT.md`
- `frontend/PRINT_AGENT_HOSTING_OVERLOAD_AUDIT.md`
- `agent/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
