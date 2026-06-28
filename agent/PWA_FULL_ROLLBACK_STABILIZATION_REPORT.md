# PWA Full Rollback — Estabilización (Agente)

**Fecha:** 2026-06-27  
**Alcance:** alinear URL agente con frontend legacy — **sin cambios código Go**

---

## 1. Config producción temporal

En cada PC con NightPOS Print Agent (`config.json` o `%ProgramData%\NightPOS\agent\config.json`):

```json
{
  "backend_url": "https://nightpos.ribersoft.com/backend/public/api/v1",
  "device_key": "npd_live_...",
  "printer_name": "POS-80",
  "poll_interval_ms": 1500
}
```

**Debe coincidir** con `VITE_API_BASE_URL=/backend/public/api/v1` del frontend desplegado.

Plantilla repo: `agent/config.production.example.json`

---

## 2. Por qué el agente recibía HTML

| Causa | Síntoma |
|-------|---------|
| `backend_url=.../api/v1` sin rewrite raíz | 404 HTML o SPA `index.html` |
| SPA fallback capturando API | `invalid character '<'` |
| Hosting reset | Error red (no HTML) |

El agente **no usa** service worker — su fallo era **misma URL rota** que el frontend.

---

## 3. Verificación

```powershell
curl.exe -sS https://nightpos.ribersoft.com/backend/public/api/v1/auth/login-context/tenants

curl.exe -sS -X POST https://nightpos.ribersoft.com/backend/public/api/v1/print-devices/heartbeat `
  -H "Authorization: Bearer npd_live_XXXX" `
  -H "Content-Type: application/json" `
  -d "{\"agent_version\":\"1.0.0\"}"
```

Respuesta debe empezar con `{`, no `<!DOCTYPE`.

Reiniciar servicio agente tras cambiar `config.json`.

---

## 4. No tocar

- Código Go del agente
- Endpoints impresión operativa
- `device_key` en backend

---

## 5. Futuro (V1.1)

Cuando `/api/v1` esté estable:

```json
"backend_url": "https://nightpos.ribersoft.com/api/v1"
```

Actualizar **frontend y agente juntos** en la misma ventana de mantenimiento.

---

## 6. Relacionados

- `frontend/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
- `backend/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
- `agent/INSTALLATION_GUIDE.md`
