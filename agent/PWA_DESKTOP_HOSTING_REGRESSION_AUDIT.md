# PWA / Desktop / Hosting — Regresión (Auditoría Agente)

**Fecha:** 2026-06-27  
**Dominio:** `https://nightpos.ribersoft.com`  
**Alcance:** diagnóstico agente impresión vs regresión PWA/deploy — **sin implementación**

---

## 1. Síntoma agente

```
invalid character '<' looking for beginning of value
```

El agente Go hace `fetch(backend_url + route)` y espera **JSON**. Recibe **HTML** cuando:

| Causa | HTML típico |
|-------|-------------|
| SPA fallback captura URL API | `<!DOCTYPE html>...` (index.html) |
| 404 hosting sin rewrite | Página 404 cPanel/LiteSpeed |
| URL base incorrecta | Login page o error page |
| Connection reset | No HTML — error red distinto |

**PWA/Workbox no afecta al agente Go directamente** (no usa browser SW). El agente sufre **misma raíz de routing/deploy** que el frontend.

---

## 2. URLs agente — timeline

| Periodo | URL oficial | Funcionaba si… |
|---------|-------------|----------------|
| v1 Beta estable | `https://nightpos.ribersoft.com/backend/public/api/v1` | Laravel public accesible (sin rewrite raíz) |
| Post arquitectura Opción A | `https://nightpos.ribersoft.com/api/v1` | `.htaccess` raíz rewrite `^api/` activo |
| Repo docs actual | `/api/v1` | Igual que frontend |

### Config en repo (desarrollo local)

`agent/config.json`:

```json
"backend_url": "http://nightpos.test/api/v1"
```

**Producción:** depende del `config.json` en cada PC Windows — **no** está en git con secretos prod.

### Endpoints críticos (bajo `backend_url`)

| Método | Ruta relativa |
|--------|---------------|
| POST | `/print-devices/heartbeat` |
| GET | `/print-devices/jobs/claim` |
| POST | `/print-devices/jobs/{id}/complete` |

Requieren header `Authorization: Bearer npd_live_...` → necesita regla `HTTP_AUTHORIZATION` en `.htaccess` raíz (Etapa 4).

---

## 3. ¿PWA rompió el agente?

| Mecanismo | ¿Afecta agente? |
|-----------|-----------------|
| Service worker browser | **No** — agente es proceso Go |
| Workbox precache | **No** |
| `mockServiceWorker.js` | **No** |
| API URL cambió a `/api/v1` sin rewrite | **Sí** — HTML/404 |
| SPA fallback en `/backend/public/api/v1` | **Sí** — si exclusión `!^/backend/` falta |
| Hosting reset | **Sí** — sin respuesta |
| Agente apunta a URL vieja/nueva inconsistente | **Sí** |

**Conclusión:** agente es **canario** del routing API; no es víctima directa del SW.

---

## 4. Alineación frontend ↔ agente

| Componente | Repo actual | Debe coincidir |
|------------|-------------|----------------|
| Frontend | `VITE_API_BASE_URL=/api/v1` | Misma base path |
| Agente docs | `https://nightpos.ribersoft.com/api/v1` | ✓ |
| Fallback temporal | `/backend/public/api/v1` | **Ambos** cambiar juntos |

**Error operativo:** frontend en `/api/v1`, agente en legacy (o viceversa) — uno funciona, otro no.

---

## 5. Pruebas obligatorias (PowerShell en PC del local)

```powershell
# 1. JSON tenants (sin auth)
curl.exe -sS https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants

# 2. Legacy (debe funcionar aunque /api/ falle)
curl.exe -sS https://nightpos.ribersoft.com/backend/public/api/v1/auth/login-context/tenants

# 3. Health
curl.exe -i https://nightpos.ribersoft.com/api/v1/health

# 4. Heartbeat agente
curl.exe -sS -X POST https://nightpos.ribersoft.com/api/v1/print-devices/heartbeat `
  -H "Authorization: Bearer npd_live_XXXX" `
  -H "Content-Type: application/json" `
  -d "{\"agent_version\":\"1.0.0\"}"
```

**Interpretación:**

- Respuesta empieza con `{` → OK
- Respuesta empieza con `<` → **deploy/htaccess/URL**
- Connection reset → **hosting**

**Probe 2026-06-27:** todas las URLs → **reset** (~300 ms).

---

## 6. Desktop app / PWA escritorio

Auditoría `desktop/NIGHTPOS_DESKTOP_APP_AUDIT.md`:

- Desktop V1 propuesto = **PWA instalada Windows** o acceso directo URL
- **No hay** Electron/Tauri en producción
- Desktop depende del **mismo origen** que web
- Si web rota por API URL o hosting, desktop/PWA instalada **también falla**

**Pausar:** instalación PWA caja/garzón en producción hasta V1.1.

---

## 7. Causa raíz (agente)

**Más probable:** combinación

1. **Hosting inestable** (reset)
2. **`backend_url=/api/v1`** sin rewrite funcional → HTML
3. **Fallback SPA** incorrecto en periodo sin exclusión `/backend/`
4. Config agente en PC no actualizada tras cambio arquitectura

**Menos probable:** bug agente Go, device_key, JWT impresión (requieren JSON de error, no HTML).

---

## 8. Recomendación — estabilizar agente

### Opción A — Rápida

1. Estabilizar hosting (`health.php` OK)
2. Config agente temporal:

```json
{
  "backend_url": "https://nightpos.ribersoft.com/backend/public/api/v1",
  "device_key": "npd_live_...",
  "poll_interval_ms": 1500
}
```

3. Reiniciar servicio Windows NightPOS Print Agent
4. Verificar curl legacy JSON antes de heartbeat

### Opción B — Definitiva

1. Validar `.htaccess` Etapa 3–4
2. `backend_url`: `https://nightpos.ribersoft.com/api/v1`
3. Smoke heartbeat + claim jobs
4. Documentar en `DEPLOYMENT_CHECKLIST.md` por sucursal

---

## 9. Checklist post-recuperación

- [ ] curl tenants → JSON (legacy o limpia, una sola oficial)
- [ ] curl heartbeat → JSON
- [ ] Agente `status.json` sin `last_error` HTML parse
- [ ] Misma base URL que frontend build desplegado
- [ ] Sin `sw.js` en docroot (irrelevante para agente pero señal deploy limpio)

---

## 10. Relacionados

- `backend/PWA_DESKTOP_HOSTING_REGRESSION_AUDIT.md`
- `frontend/PWA_DESKTOP_HOSTING_REGRESSION_AUDIT.md`
- `agent/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`
- `agent/INSTALLATION_GUIDE.md`
- `agent/DEPLOYMENT_CHECKLIST.md`
