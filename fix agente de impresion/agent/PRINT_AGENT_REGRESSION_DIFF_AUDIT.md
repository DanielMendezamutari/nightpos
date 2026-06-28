# Print Agent — Auditoría comparativa (estable vs actual)

**Fecha:** 2026-06-27  
**Commit estable de referencia:** `121fade94c1257fbd81809c8c121070aa398bb1d`  
  *"agregando el fornten para poder ver el cierre administrativo"* — 2026-06-25 14:36  
**HEAD comparado:** estado actual del repo (post-rollback PWA parcial)  
**Alcance:** diff git + análisis — **sin implementación**

---

## 1. Veredicto ejecutivo

**La hipótesis del usuario se confirma con evidencia git.**

El agente **Go no cambió** su lógica de polling entre el commit estable y HEAD. Con `poll_interval_ms=1500` imprimía bien porque la URL apuntaba al path **directo a Laravel** (`/backend/public/api/v1`), alineado con el frontend estable.

La regresión operativa vino de la **cadena deploy/PWA/arquitectura URL**:

1. Commit `256f3a8` (*fix de impresion*, 2026-06-26) cambió frontend y docs a **Opción A** `/api/v1` + `.htaccess` raíz.
2. El agente en PC de producción quedó (o se instaló) con `backend_url=.../api/v1`.
3. Sin rewrite raíz estable → heartbeat recibe **connection reset** o HTML.
4. El agente **reintenta cada 1500 ms sin backoff** (igual que antes) → amplifica colapso LiteSpeed/CloudLinux.

**1500 ms no es la causa raíz.** Es el mismo intervalo que funcionaba. Lo que cambió fue la **URL efectiva** y la **estabilidad del routing**.

---

## 2. Diff git — agente (`121fade..HEAD`)

```bash
git diff 121fade94c1257fbd81809c8c121070aa398bb1d..HEAD -- agent
```

### Código Go — **sin cambios**

| Archivo | ¿Cambió? |
|---------|----------|
| `internal/config/config.go` | **No** |
| `internal/agent/runtime.go` | **No** |
| `internal/api/client.go` | **No** |
| `config.example.json` | **No** |

### Solo cambios operativos / docs / CLI

| Cambio | Impacto |
|--------|---------|
| `config.production.example.json` (nuevo) | URL legacy `/backend/public/api/v1` — rollback doc |
| `edit-config.bat` (nuevo) | Plantilla con `/api/v1` |
| `INSTALLATION_GUIDE.md` | Ejemplo producción: `nightpos.cliente.com/api/v1` → `nightpos.ribersoft.com/api/v1` |
| `main.go` | Flag `--open-config` |
| `internal/cli/install.go` | `OpenConfig()` llama `WriteExampleIfMissing()` |
| Varios `.md` de auditoría PWA/hosting | Recomiendan `/api/v1` en periodo intermedio |

**Commits que tocaron `agent/` desde 121fade:**

```
794ba65 plataforma saas de ribersoft
256f3a8 fix de impresion
507d7a3 htacces
6f2f91f stabilidad
```

Ninguno modificó `runtime.go`, `config.go` ni `client.go`.

---

## 3. Agente ANTES (commit `121fade`)

| Parámetro | Valor estable |
|-----------|---------------|
| **backend_url en repo dev** | `http://nightpos.test/api/v1` (`agent/config.json`) |
| **backend_url producción efectiva** | `/backend/public/api/v1` — alineado con `frontend/.env.production` del mismo commit |
| **WriteExampleIfMissing()** | `https://tu-dominio-nightpos.com/api/v1` (siempre fue así) |
| **poll_interval_ms default** | **1500** (`config.Default()`) |
| **poll_interval_ms repo dev** | 100 (solo dev local — no usado por servicio) |
| **Heartbeat** | POST `/print-devices/heartbeat` cada tick |
| **Pending** | GET `/print-jobs/pending?limit=5` solo si heartbeat OK |
| **Claim** | POST claim → print → printed/failed por job |
| **Backoff** | **No** — nunca existió |
| **HTTP client** | Nueva request por llamada; timeout 30 s |
| **`.htaccess` raíz** | **No existía** en repo en 121fade |

### Frecuencia con poll=1500 (estable)

~40 heartbeats/min + ~40 pending/min ≈ **80 HTTP/min** cuando todo OK.

Con URL legacy directa a `backend/public/index.php`, cada request bootstrap Laravel + bcrypt auth — **aceptable** en hosting compartido moderado.

---

## 4. Agente ACTUAL (HEAD repo)

| Parámetro | Valor actual |
|-----------|--------------|
| **Código loop** | **Idéntico** a 121fade |
| **poll_interval default** | **1500** (sin cambio) |
| **WriteExampleIfMissing()** | **Sin cambio** — sigue `/api/v1` |
| **config.production.example.json** | Legacy `/backend/public/api/v1` (rollback doc) |
| **INSTALLATION_GUIDE producción** | `https://nightpos.ribersoft.com/api/v1` |
| **Config real servicio Windows** | `C:\ProgramData\NightPOS\PrintAgent\config.json` — **independiente del repo** |

### Log producción del usuario

```
POST https://nightpos.ribersoft.com/api/v1/print-devices/heartbeat
wsarecv: conexión interrumpida por el host remoto
```

→ URL **Opción A** en PC; frontend repo ya revertido a legacy.

---

## 5. Respuestas a las 13 preguntas

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿Cambió código agente? | **No** — runtime/config/client idénticos |
| 2 | ¿Cambió default backend_url? | **No en código** — `WriteExampleIfMissing` siempre `/api/v1` |
| 3 | ¿Cambió WriteExampleIfMissing()? | **No** |
| 4 | ¿Cambió default poll_interval_ms? | **No** — sigue 1500 |
| 5 | ¿Cambió flujo heartbeat+pending+claim? | **No** |
| 6 | ¿Antes legacy y ahora /api/v1? | **Frontend sí** (121fade legacy → 256f3a8 /api/v1 → 6f2f91f legacy). **Agente en PC** probablemente `/api/v1` por install/docs |
| 7 | ¿Cambió backend heartbeat? | Campos opcionales SAAS (host_name, os, etc.) — **mismo UPDATE cada request** |
| 8 | ¿Cambió middleware device_key? | **No** — `AuthenticatePrintDeviceMiddleware` sin diff |
| 9 | ¿Más Hash::check? | **No** — 1 bcrypt por request agente, igual que antes |
| 10 | ¿Cambió frecuencia requests? | **No en código** — misma lógica; solo cambia si config PC tiene poll distinto |
| 11 | ¿Backoff antes? | **No** — nunca hubo |
| 12 | ¿Commit regresión? | **`256f3a8`** (*fix de impresion*) — cambió `.env.production` a `/api/v1`, añadió `.htaccess` raíz, docs agente a Opción A |
| 13 | ¿Cambio mínimo estable? | Corregir `backend_url` en ProgramData a legacy; **mantener 1500**; backoff opcional como protección futura |

---

## 6. Diferencias críticas (no es el EXE)

| Área | Estable (121fade) | Post-PWA (256f3a8+) | Efecto en agente |
|------|-------------------|---------------------|------------------|
| `VITE_API_BASE_URL` | `/backend/public/api/v1` | `/api/v1` | Desalineación si agente no se actualiza junto |
| `frontend/public/.htaccess` | No en git | Rewrite `/api/` → Laravel | Agente `/api/v1` **depende** de deploy correcto |
| Docs INSTALLATION_GUIDE | `cliente.com/api/v1` | `ribersoft.com/api/v1` | Técnicos configuran path roto |
| Código agente | v2.0.0 loop | **Igual** | — |

---

## 7. Cambio exacto que rompió producción

**Primario:** commit `256f3a842ead5d88d6bf3c70ee9fcfd056198611` — arquitectura URL Opción A sin garantizar rewrite estable en hosting.

**Secundario (amplificador):** agente en PC con `backend_url=/api/v1` + mismo poll 1500 + sin backoff → martillo sobre hosting ya frágil.

**No es:** cambio de intervalo 1500→100 en producción (eso sería peor, pero no es el diff de código estable→actual).

---

## 8. Plan recomendado

### Inmediato (manual — restaurar comportamiento estable)

```json
{
  "backend_url": "https://nightpos.ribersoft.com/backend/public/api/v1",
  "device_key": "npd_live_...",
  "printer_name": "...",
  "poll_interval_ms": 1500,
  "dry_run": false
}
```

1. `NightPOSPrintAgent.exe --stop`
2. Editar `C:\ProgramData\NightPOS\PrintAgent\config.json`
3. Verificar curl legacy JSON antes de reiniciar
4. `NightPOSPrintAgent.exe --restart`
5. Una sola instancia del servicio

### Protección futura (cuando se programe)

- Backoff exponencial solo en **error de red** (30s→60s→120s→max 5min)
- **No bajar** poll por defecto de 1500 si URL es correcta
- Actualizar `WriteExampleIfMissing()` y `edit-config.bat` a URL legacy hasta V1.1
- Frontend + agente + deploy **siempre juntos** al cambiar base API

### No tocar ahora

- Lógica claim/print
- Endpoints operativos
- SAAS / PWA / features nuevas

---

## 9. Comandos git de referencia

```bash
git diff 121fade94c1257fbd81809c8c121070aa398bb1d..HEAD -- agent/internal/agent/runtime.go
# (vacío — sin cambios)

git log --oneline 121fade..HEAD -- frontend/.env.production
# 256f3a8 fix de impresion  → /api/v1
# 6f2f91f stabilidad        → rollback /backend/public/api/v1

git log --oneline 121fade..HEAD -- frontend/public/.htaccess
# 256f3a8, 507d7a3, 83e72ba — introducen/modifican htaccess raíz
```

---

## 10. Relacionados

- `../backend/PRINT_AGENT_REGRESSION_DIFF_AUDIT.md`
- `agent/PRINT_AGENT_HOSTING_OVERLOAD_AUDIT.md`
- `agent/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
