# Print Agent — Auditoría comparativa backend (estable vs actual)

**Fecha:** 2026-06-27  
**Commit estable:** `121fade94c1257fbd81809c8c121070aa398bb1d`  
**Alcance:** endpoints print-device + diff git — **sin implementación**

---

## 1. Conclusión

El backend de impresión **no regresó funcionalmente** entre el commit estable y HEAD para el flujo operativo del agente (heartbeat → pending → claim → printed/failed).

Los cambios posteriores son:

- Campos opcionales en heartbeat (perfil técnico SAAS-1.5) — el agente Go **no los envía**
- Rutas platform/operations (admin) — **no usadas por agente**
- Middleware `AuthenticatePrintDeviceMiddleware` — **sin cambios**

La saturación de hosting **no viene de un cambio de lógica heartbeat en backend**, sino de:

1. URL incorrecta en el agente (`/api/v1` sin rewrite)
2. Mismo volumen de requests que antes (~80/min con poll 1500)
3. Requests fallidos que igual consumen recursos del servidor web

---

## 2. Rutas agente — sin cambio estructural

Grupo `nightpos.print-device` en `backend/routes/api.php` (estable y actual):

| Método | Ruta relativa | Uso |
|--------|---------------|-----|
| POST | `print-devices/heartbeat` | Heartbeat |
| GET | `print-jobs/pending` | Listar jobs |
| POST | `print-jobs/{id}/claim` | Reclamar |
| POST | `print-jobs/{id}/printed` | OK |
| POST | `print-jobs/{id}/failed` | Error |

**Paths absolutos:**

| Entorno estable | Entorno roto (agente PC) |
|-----------------|--------------------------|
| `/backend/public/api/v1/print-devices/heartbeat` | `/api/v1/print-devices/heartbeat` |

Mismo Laravel; distinto enrutamiento web.

---

## 3. Diff git backend print (121fade..HEAD)

### Sin cambios

- `AuthenticatePrintDeviceMiddleware.php` — diff vacío
- Rutas print-device del agente — mismas rutas

### Cambios menores (no afectan agente Go actual)

**`PrintDeviceHeartbeatUseCase` + `PrintDeviceController`:**

Aceptan campos opcionales nuevos: `host_name`, `os_name`, `os_version`, `arch`, `ip_address`, `printer_model`.

El agente Go envía solo:

```json
{
  "printer_name": "...",
  "agent_version": "2.0.0",
  "last_error": ""
}
```

→ Comportamiento efectivo **igual** que en 121fade.

**`EloquentPrintDeviceRepository::recordHeartbeat`:**

- Antes: UPDATE fijo de 4 campos
- Ahora: UPDATE de los mismos 4 + campos opcionales si vienen en request

Con agente actual → **mismo UPDATE** que antes (campos SAAS quedan null/no enviados).

---

## 4. Coste por request — estable vs actual

| Paso | Estable | Actual |
|------|---------|--------|
| Auth middleware | SELECT + bcrypt | **Igual** |
| Heartbeat OK | UPDATE print_devices | **Igual** (mismos campos desde agente) |
| Pending | SELECT print_jobs | **Igual** |
| Rate limit | Ninguno | **Igual** |
| Throttle last_seen | Ninguno | **Igual** |

**Hash::check por request:** no aumentó. Sigue siendo **2× por ciclo** (heartbeat + pending).

---

## 5. Frontend / deploy — lo que sí cambió (contexto backend)

### `frontend/.env.production`

| Commit | Valor |
|--------|-------|
| `121fade` (estable) | `/backend/public/api/v1` |
| `256f3a8` (*fix de impresion*) | `/api/v1` ← **regresión arquitectura** |
| `6f2f91f` (*stabilidad*) | `/backend/public/api/v1` ← rollback repo |

### `frontend/public/.htaccess`

| Commit | Estado |
|--------|--------|
| `121fade` | **No existía** en git |
| `256f3a8` | Introducido — rewrite `^api/` → `backend/public/index.php` |

El agente con `/api/v1` **depende** de ese `.htaccess` desplegado en docroot. El estado estable **no lo necesitaba** (path legacy directo).

---

## 6. Commit que introdujo la regresión

**`256f3a842ead5d88d6bf3c70ee9fcfd056198611`** — *fix de impresion* — 2026-06-26

Archivos relevantes:

- `frontend/.env.production` → `/api/v1`
- `frontend/public/.htaccess` → Opción A
- `agent/INSTALLATION_GUIDE.md` → producción `ribersoft.com/api/v1`
- `agent/HOSTING_DEPLOY_ARCHITECTURE_*.md` (nuevos)

Ironía: commit titulado "fix de impresion" movió la arquitectura lejos del path que **funcionaba**.

Rollback parcial en repo: `6f2f91f stabilidad` (frontend `.env` legacy). **La PC del agente no se actualiza sola.**

---

## 7. Respuesta cuando backend_url es incorrecta

| Respuesta HTTP | Backend ejecuta | Efecto agente |
|----------------|-----------------|---------------|
| Connection reset | No llega a Laravel | Warn cada 1500 ms; no pending |
| 404 HTML | No JSON | `invalid API response`; sigue polling |
| 200 JSON heartbeat | UPDATE + auth | Normal |

En reset masivo, LiteSpeed puede dejar de responder **todo el dominio** — frontend y agente caen juntos.

---

## 8. Plan recomendado

### Restaurar estable (sin cambiar backend)

1. Agente → URL legacy (manual en ProgramData)
2. Frontend desplegado con `/backend/public/api/v1` (repo ya OK en `6f2f91f`)
3. **Mantener poll_interval_ms=1500** — probado estable con URL correcta

### Protecciones backend futuras (opcional, no urgente)

- Throttle UPDATE `last_seen_at` (30–60 s)
- Cache auth device_key_prefix 30–60 s
- Rate limit suave por device

**No requeridas** para volver al estado que imprimía bien.

---

## 9. Pruebas de confirmación

```powershell
# Path estable (debe JSON)
curl.exe -sS https://nightpos.ribersoft.com/backend/public/api/v1/auth/login-context/tenants

# Path agente roto (reset o HTML si rewrite falla)
curl.exe -sS -X POST https://nightpos.ribersoft.com/api/v1/print-devices/heartbeat `
  -H "Authorization: Bearer npd_live_XXXX" `
  -H "Content-Type: application/json" `
  -d "{\"agent_version\":\"2.0.0\",\"printer_name\":\"TEST\"}"

# Path estable heartbeat
curl.exe -sS -X POST https://nightpos.ribersoft.com/backend/public/api/v1/print-devices/heartbeat `
  -H "Authorization: Bearer npd_live_XXXX" `
  -H "Content-Type: application/json" `
  -d "{\"agent_version\":\"2.0.0\",\"printer_name\":\"TEST\"}"
```

Con agente detenido: si legacy responde y `/api/v1` no → confirma hipótesis URL.

---

## 10. Relacionados

- `../agent/PRINT_AGENT_REGRESSION_DIFF_AUDIT.md`
- `backend/PRINT_AGENT_HOSTING_OVERLOAD_AUDIT.md`
- `backend/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
