# cPanel / LiteSpeed — .htaccess Deploy Fix (Agente impresión)

**Fecha:** 2026-06-25  
**Dominio:** `https://nightpos.ribersoft.com`

---

## 1. Síntoma agente

```
invalid character '<' looking for beginning of value
```

**Causa:** el agente recibe **HTML** (`index.html` o 404 HTML) en lugar de JSON.

| URL mal enraizada | Respuesta típica |
|-------------------|------------------|
| `/api/v1/...` sin rewrite raíz | 404 HTML o SPA |
| SPA fallback captura `/backend/public/api/v1/...` | `index.html` |
| Servidor caído | connection reset (no HTML) |

---

## 2. URL oficial agente

**Opción A (preferida):**

```json
{
  "backend_url": "https://nightpos.ribersoft.com/api/v1"
}
```

Archivo local: `agent/config.json` o `%ProgramData%\NightPOS\agent\config.json`

**Fallback temporal** si rewrite `/api/` no funciona en hosting:

```json
{
  "backend_url": "https://nightpos.ribersoft.com/backend/public/api/v1"
}
```

Debe coincidir con `VITE_API_BASE_URL` del frontend (misma base API).

---

## 3. Dependencia del `.htaccess` raíz

El agente **no** lee `.htaccess`. Depende de que el servidor enrute bien:

| Requisito | Regla raíz |
|-----------|------------|
| `/api/v1/*` → JSON | `RewriteRule ^api/ backend/public/index.php` |
| Legacy OK | SPA excluye `!^/backend/` |
| Bearer token llega a PHP | `HTTP_AUTHORIZATION` env (Etapa 4) |

---

## 4. Verificación desde PC del local

```powershell
# Debe ser JSON (success / data), NO HTML
curl.exe -sS https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants

curl.exe -sS https://nightpos.ribersoft.com/api/v1/health

curl.exe -sS -X POST https://nightpos.ribersoft.com/api/v1/print-devices/heartbeat `
  -H "Authorization: Bearer npd_live_XXXX" `
  -H "Content-Type: application/json" `
  -d "{\"agent_version\":\"1.0.0\"}"
```

Si la respuesta empieza con `<!DOCTYPE` o `<html` → **deploy/htaccess**, no bug del agente Go.

---

## 5. Etapas de prueba (servidor)

1. **Etapa 1:** sin `.htaccess` raíz — probar legacy URL tenants (JSON)
2. **Etapa 3:** agregar rewrite `/api/` — probar `/api/v1/health`
3. **Etapa 4:** agregar Authorization — probar heartbeat

Si Etapa 1 legacy OK pero Etapa 3 falla → usar fallback `backend/public/api/v1` en agente hasta fix hosting.

---

## 6. Probe 2026-06-25

Todas las URLs devolvieron **ERR_CONNECTION_RESET** (~300 ms). El agente fallará igual que el browser hasta que LiteSpeed/PHP estén estables.

Orden operativo:

1. Estabilizar hosting (entry processes, load)
2. Aplicar `.htaccess` por etapas (`frontend/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`)
3. Confirmar JSON con curl
4. Reiniciar servicio agente Windows

---

## 7. Config ejemplo

Ver `agent/config.example.json`:

```json
{
  "backend_url": "https://nightpos.ribersoft.com/api/v1",
  "device_key": "npd_live_...",
  "poll_interval_seconds": 5
}
```

**Relacionados:** `agent/INSTALLATION_GUIDE.md`, `agent/DEPLOYMENT_CHECKLIST.md`
