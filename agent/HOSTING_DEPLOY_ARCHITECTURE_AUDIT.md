# Hosting — Deploy Architecture Audit (Print Agent)

**Fecha:** 2026-06-25  
**URL API oficial:** `https://nightpos.ribersoft.com/api/v1`

---

## 1. Config oficial `config.json`

```json
{
  "backend_url": "https://nightpos.ribersoft.com/api/v1",
  "device_key": "npd_live_...",
  "printer_name": "POS-80",
  "poll_interval_ms": 1500,
  "dry_run": false,
  "log_level": "info"
}
```

**Reglas:**

- Incluir `/api/v1` — **sin** barra final  
- **No** usar `/backend/public/api/v1` en producción Ribersoft  
- Misma base URL que frontend (`VITE_API_BASE_URL`)

---

## 2. Por qué el agente recibe HTML

El agente (`agent/src/index.js`) hace `fetch(backend_url + route)` y parsea JSON.

Si la respuesta es HTML (`<!DOCTYPE html>`):

| Causa | Fix |
|-------|-----|
| `backend_url` apunta a dominio incorrecto | Corregir URL |
| `/api/` no reescribe a Laravel → SPA `index.html` | Desplegar `.htaccess` Opción A |
| Frontend y agente usan URLs distintas | Unificar en `/api/v1` |
| 502/404 página HTML del hosting | Revisar logs LiteSpeed |

Error típico: `SyntaxError: Unexpected token '<'` o `invalid character '<'`.

---

## 3. Endpoints agente (todas bajo `backend_url`)

| Método | Ruta relativa | Auth |
|--------|---------------|------|
| POST | `/print-devices/heartbeat` | Bearer `device_key` |
| GET | `/print-jobs/pending` | Bearer `device_key` |
| POST | `/print-jobs/{id}/claim` | Bearer |
| POST | `/print-jobs/{id}/complete` | Bearer |

Ejemplo completo:  
`https://nightpos.ribersoft.com/api/v1/print-devices/heartbeat`

---

## 4. Verificación desde PC del local

```powershell
# Debe devolver JSON (lista tenants — público)
curl.exe -sS https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants

# Heartbeat (reemplazar device_key)
curl.exe -sS -X POST https://nightpos.ribersoft.com/api/v1/print-devices/heartbeat `
  -H "Authorization: Bearer npd_live_XXXX" `
  -H "Content-Type: application/json" `
  -d "{}"
```

Si curl muestra `<!DOCTYPE html>`, el rewrite API **no está activo** en hosting.

---

## 5. Entornos

| Entorno | `backend_url` |
|---------|---------------|
| XAMPP vhost | `http://nightpos.test/api/v1` |
| XAMPP sin vhost (solo dev) | `http://localhost/nightpos/backend/public/api/v1` |
| **Producción cPanel** | `https://{dominio}/api/v1` |

---

## 6. Archivos agente ya alineados

- `agent/config.example.json` → `/api/v1`  
- `agent/README_WINDOWS.md` → producción `/api/v1`  
- `agent/TROUBLESHOOTING_GUIDE.md` → diagnóstico HTML  

---

Ver `agent/HOSTING_DEPLOY_ARCHITECTURE_FIX_REPORT.md`.
