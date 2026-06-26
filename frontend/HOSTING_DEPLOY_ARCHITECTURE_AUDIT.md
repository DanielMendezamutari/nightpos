# Hosting — Deploy Architecture Audit (Frontend)

**Fecha:** 2026-06-25  
**Decisión:** Opción A — `VITE_API_BASE_URL=/api/v1`

---

## 1. URLs oficiales

| Uso | URL |
|-----|-----|
| SPA | `https://nightpos.ribersoft.com` |
| API axios | `/api/v1` (relativo, mismo origin) |
| SSE events | `${VITE_API_BASE_URL}/events/...` |

Clientes HTTP:

- `src/services/http.js` → `baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1'`
- `src/composables/useApi.js` → mismo env
- `src/composables/useOperationalEvents.js` → mismo env

---

## 2. `.env.production` (oficial)

```env
VITE_API_BASE_URL=/api/v1
VITE_USE_MSW=false
VITE_PWA_ENABLED=false
```

**Antes (incorrecto para producción):** `/backend/public/api/v1` — expone estructura interna y complica SW/rewrites.

---

## 3. PWA

| Estado | Detalle |
|--------|---------|
| `VITE_PWA_ENABLED=false` | Sin `sw.js`, `registerSW.js`, Workbox en build |
| `.htaccess` raíz | 404 explícito si browser pide `sw.js` viejo |
| `main.js` | Desregistra SW legacy al cargar |

**Problema sw.js MIME text/html:** SPA fallback servía `index.html` para `/sw.js` inexistente → Chrome rechaza SW. Fix: regla 404 en `.htaccess` + no generar SW en build.

---

## 4. Build y deploy

```bash
cd frontend
npm run build
```

Verificar `dist/`:

- ✅ `.htaccess` presente
- ✅ `index.html` sin `registerSW.js`
- ❌ no `sw.js`, `workbox-*.js`, `mockServiceWorker.js`

Copiar **todo** `dist/*` a raíz hosting (conservar `backend/`).

---

## 5. Pruebas frontend

| Prueba | Esperado |
|--------|----------|
| DevTools → login-context | `POST/GET` a `/api/v1/...`, JSON |
| Login PIN | payload `{ pin, tenant_slug, branch_code }` |
| Refresh `/nightpos/cashier` | 200 HTML SPA |
| Incógnito | sin SW registrado |
| Network Error | desaparece cuando servidor responde + JWT OK |

---

## 6. Opción A vs B

| | Opción A `/api/v1` | Opción B `/backend/public/api/v1` |
|--|-------------------|-----------------------------------|
| Profesional | ✅ | ❌ |
| Agente + frontend unificados | ✅ | ⚠️ fácil divergir |
| Riesgo SPA captura API | Bajo (rewrite dedicado) | Medio |
| APP_URL limpio | ✅ | ❌ |

**Recomendación adoptada:** Opción A.

---

Ver `frontend/HOSTING_DEPLOY_ARCHITECTURE_FIX_REPORT.md`.
