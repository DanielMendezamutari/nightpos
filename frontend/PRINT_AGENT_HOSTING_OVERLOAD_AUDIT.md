# Print Agent — Hosting Overload Audit (Frontend)

**Fecha:** 2026-06-27  
**Alcance:** impacto indirecto frontend + alineación API — **sin implementación**

---

## 1. Relación con el frontend

El frontend **no ejecuta** heartbeat del agente. El colapso del hosting afecta al SPA porque **comparten el mismo dominio y pool PHP/LiteSpeed**.

Cuando el agente satura entry processes:

- Navegador → `ERR_CONNECTION_RESET`
- Login → no carga empresas
- API axios → timeout / reset

**No es bug de Vue** — es contención de recursos en el servidor.

---

## 2. Alineación API post-rollback

| Componente | URL temporal oficial |
|------------|----------------------|
| Frontend | `VITE_API_BASE_URL=/backend/public/api/v1` |
| Agente | `backend_url=https://nightpos.ribersoft.com/backend/public/api/v1` |

**Desalineación detectada:** agente en log usa `/api/v1` → mismas rutas lógicas pero **path distinto** en hosting actual.

El frontend desplegado con rollback correcto puede funcionar mientras el agente con `/api/v1` **martilla** un path roto o más pesado para el rewrite.

---

## 3. Síntoma “navegador viejo OK, nuevo no”

| Factor | Frontend | Agente |
|--------|----------|--------|
| Cookies / cache | Sí | No |
| PWA SW legacy | Sí | No |
| Polling agresivo | No | **Sí** |

Si el colapso coincide con **encender impresora/agente**, priorizar auditoría agente sobre cookies.

---

## 4. Prueba de correlación (manual)

1. Con sitio caído → **stop agente** en PC del local
2. Esperar 2 min → probar `/login` en navegador
3. Si mejora → frontend no requiere cambio; recuperar hosting + config agente
4. Con agente off, verificar frontend con build rollback (`/backend/public/api/v1`)

---

## 5. Frontend — sin cambios requeridos ahora

Rollback PWA ya aplicado en repo:

- `VITE_PWA_ENABLED=false`
- API legacy en `.env.production`

**No agregar** lógica de agente en frontend en esta fase.

Futuro V1.1: panel sucursal puede mostrar “agente offline” leyendo API admin — **no urgente**.

---

## 6. Pasos manuales para operador

1. Detener agente (`--stop`)
2. Confirmar web responde
3. Corregir `ProgramData\...\config.json` del agente
4. `poll_interval_ms` ≥ 15000
5. Reiniciar agente; monitorear web
6. Usuarios: unregister SW + hard refresh (si aplica)

---

## 7. Relacionados

- `agent/PRINT_AGENT_HOSTING_OVERLOAD_AUDIT.md`
- `backend/PRINT_AGENT_HOSTING_OVERLOAD_AUDIT.md`
- `frontend/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
