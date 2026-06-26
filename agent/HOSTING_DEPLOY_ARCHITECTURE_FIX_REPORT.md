# Hosting — Deploy Architecture Fix Report (Print Agent)

**Fecha:** 2026-06-25

---

## Acción operativa (no cambio de código agente)

El agente ya concatena `backend_url + route`. Solo hay que configurar la URL correcta en cada PC:

```json
"backend_url": "https://nightpos.ribersoft.com/api/v1"
```

## Checklist por sucursal

1. Editar `C:\ProgramData\NightPOS\PrintAgent\config.json` (o ruta instalada)  
2. `backend_url` = dominio producción + `/api/v1`  
3. `curl.exe .../api/v1/auth/login-context/tenants` → JSON desde esa PC  
4. Reiniciar servicio agente  
5. Verificar heartbeat 200 en logs  

## Documentación actualizada

- `agent/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md`  
- Referencia cruzada en `backend/` y `frontend/` audits  

## No mezclar URLs

| Cliente | URL |
|---------|-----|
| Frontend build | `/api/v1` |
| Agente | `https://nightpos.ribersoft.com/api/v1` |
| ~~Deprecado~~ | ~~`/backend/public/api/v1`~~ |
