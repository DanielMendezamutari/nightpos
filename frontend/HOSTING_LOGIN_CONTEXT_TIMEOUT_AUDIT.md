# Hosting — Login Context Timeout Audit (Frontend)

**Fecha:** 2026-06-25  
**Síntoma:** en `/login`, *"El servidor tardó demasiado en responder. Verifique la conexión e intente de nuevo."*  
**Paso:** carga inicial de empresas (`loadTenants` → login-context tenants).

---

## 1. Origen del mensaje

Archivo: `src/services/http.js` → `classifyApiError()`

```javascript
if (code === 'ECONNABORTED' || /timeout/i.test(error.message || '')) {
  userMessage: 'El servidor tardó demasiado en responder. Verifique la conexión e intente de nuevo.'
}
```

Es el mensaje de **timeout axios**, no de 404 ni de network reset genérico.

| Error real | Mensaje actual |
|------------|----------------|
| Timeout (>15 s sin respuesta) | **"El servidor tardó demasiado..."** ← lo que ve el usuario |
| ERR_NETWORK / reset rápido | "Sin conexión con el servidor..." |
| 404 HTML | "Request failed with status code 404" (si no hay JSON message) |

**Interpretación:** el navegador **esperó hasta agotar timeout** (15 s por defecto) sin respuesta HTTP completa. Coherente con **cola PHP saturada** en hosting, no con query lenta de tenants (backend local: 117 ms).

---

## 2. Flujo al abrir `/login`

```
onMounted
  → auth.clearAuthOnly()           // quita token JWT, NO tenant/branch cookies
  → hydratePinStep()
       sin cookies tenant+branch
         → loadTenants()
             → GET {baseURL}/auth/login-context/tenants
```

Archivos:

- `src/pages/login.vue` — `loadTenants()`, `getApiErrorMessage()`  
- `src/api/loginContext.js` — path relativo `/auth/login-context/tenants`  
- `src/services/http.js` — `baseURL: VITE_API_BASE_URL || '/api/v1'`

---

## 3. Configuración API / timeout

| Variable | `.env.production` actual | Efecto |
|----------|--------------------------|--------|
| `VITE_API_BASE_URL` | `/api/v1` | URL final `/api/v1/auth/login-context/tenants` |
| `VITE_API_TIMEOUT_MS` | **no definido** | Default **15000 ms** en `http.js` |
| `VITE_PWA_ENABLED` | `false` | Sin SW en build nuevo |

Docs recomiendan `VITE_API_TIMEOUT_MS=30000` en hosting lento — **no aplicado en `.env.production`**.

---

## 4. Interceptor axios — riesgo secundario

`http.js` request interceptor:

- Si existe cookie `accessToken` y el token expira en <5 min, **espera `tryRefreshSession()`** antes de la request.
- `isAuthEndpoint()` incluye `/auth/login-` (**login-pin**, **login-password**) pero **NO** `/auth/login-context/`.

Si un usuario llega a `/login` con cookie JWT vieja (p. ej. no borrada correctamente):

1. Request tenants **espera refresh** primero  
2. Refresh puede colgar/timeout en hosting  
3. Mensaje timeout en pantalla de empresas  

`clearAuthOnly()` borra `accessToken` en mount, pero **race** o pestaña antigua podría tener token. **Hipótesis secundaria** — auditar en DevTools si hay request a `/auth/refresh` antes de tenants.

---

## 5. Producción vs local

| Entorno | login-context tenants |
|---------|------------------------|
| Local (benchmark) | 200 en **117 ms**, 1 query |
| Producción estable (probe anterior) | 200 JSON vía `/backend/public/api/v1` |
| Producción inestable (probe actual) | Connection reset ~700 ms en **todas** las URLs API |

El frontend no puede cargar empresas si **ninguna** URL API responde a tiempo.

---

## 6. Diagnóstico DevTools (obligatorio en hosting)

En `/login` → Network:

| Campo | Anotar |
|-------|--------|
| Request URL completa | ¿`/api/v1/...` o `/backend/public/api/v1/...`? |
| Time (duración) | ¿~15000 ms (timeout) o falla antes? |
| Status | pending → failed / 404 / 502 |
| Response Content-Type | `application/json` vs `text/html` |
| ¿Request previo a `/auth/refresh`? | Sí/No |

---

## 7. Respuestas resumidas

| # | Respuesta |
|---|-----------|
| SAAS-1.5 en frontend | **No** — login no llama Control Center |
| API base incorrecta | `/api/v1` OK si rewrite activo; si no, timeout/404 en cadena |
| Fix P0 | Estabilizar hosting + URL API única |
| Fix P1 frontend | `VITE_API_TIMEOUT_MS=30000`; excluir login-context del refresh; mensajes por tipo de error |
| Fix P2 | Ninguno urgente en login.vue |

---

## 8. Plan recomendado (sin implementar aún)

1. **Hosting primero** — curl tenants debe dar 200 JSON en <1 s  
2. Añadir `VITE_API_TIMEOUT_MS=30000` y rebuild  
3. Tratar login-context como endpoint público: skip refresh en interceptor  
4. Mejorar UX: distinguir timeout / reset / 404 HTML en `classifyApiError`  
5. Backend: query SQL mínima (opcional, no es el cuello de botella actual)

Ver `backend/HOSTING_LOGIN_CONTEXT_TIMEOUT_AUDIT.md`.

---

**Estado:** la lentitud percibida es **timeout de red/hosting**, no lógica pesada en login-context. Código backend auditado: **1 query, <300 ms local**.
