# Hosting — Login Context 404 Audit (Frontend)

**Fecha:** 2026-06-25  
**Alcance:** login.vue, login-context API, cookies, axios, cache, PWA. **Sin implementación** en esta entrega.

---

## 1. Flujo auditado

```
/login (onMounted)
  → auth.clearAuthOnly()        # limpia token/user, NO tenant/branch cookies
  → hydratePinStep()
       ├─ sin tenantSlug+branchCode cookies
       │    → pinStep = select-context
       │    → loadTenants() → GET /auth/login-context/tenants
       └─ con cookies guardadas
            → fetchLoginContextBranches(tenantSlug)
            → si OK → pinStep = pin (solo PIN)
            → si falla → mensaje amigable + loadTenants()
```

**Implicación:** navegador **con contexto** puede funcionar **sin** llamar a tenants. Navegador **nuevo** **siempre** llama tenants primero.

---

## 2. Archivos revisados

| Archivo | Rol |
|---------|-----|
| `src/pages/login.vue` | UI login, cookies, `hydratePinStep`, errores |
| `src/api/loginContext.js` | `GET /auth/login-context/tenants`, `GET /auth/login-context/branches?tenant_slug=` |
| `src/services/http.js` | `baseURL: VITE_API_BASE_URL \|\| '/api/v1'`, `classifyApiError` |
| `src/stores/auth.js` | login PIN/password (no login-context) |

### Cookies (30 días, `useCookie`)

| Cookie | Uso |
|--------|-----|
| `tenantSlug` | Slug empresa (ej. `C22`, no el nombre "CASA22") |
| `branchCode` | Código sucursal (ej. `1` para EL JEFE) |
| `tenantName` | Display |
| `branchName` | Display |
| `lastOperatorName` | "Super Admin" en pantalla PIN |

**No usa** localStorage para contexto login (solo cookies Pinia/useCookie).

**No envía** `device_key` en login-context.

---

## 3. URLs que construye el frontend

Con `VITE_API_BASE_URL=/api/v1` (build actual en repo):

| Llamada | URL final |
|---------|-----------|
| Tenants | `/api/v1/auth/login-context/tenants` |
| Branches | `/api/v1/auth/login-context/branches?tenant_slug={slug}` |

Build anterior en hosting/dist mezclado:

| Llamada | URL legacy |
|---------|------------|
| * | `/backend/public/api/v1/auth/login-context/...` |

**Evidencia dist local:** coexisten chunks con ambas bases (`printTicket-*.js` con `/api/v1` y `/backend/public/api/v1`). Mismo riesgo en servidor si deploy fue parcial.

---

## 4. Manejo de errores actual

| Función | Comportamiento |
|---------|----------------|
| `loadTenants()` catch | `errorMessage = getApiErrorMessage(error)` |
| `loadBranches()` catch | idem + `suggestContextChange = true` |
| `hydratePinStep()` catch (contexto inválido) | Mensaje amigable + `clearSavedContext()` + `loadTenants()` |

### Por qué se ve "Request failed with status code 404"

`classifyApiError()` **no tiene rama específica para 404**. Caída en:

```javascript
userMessage: serverMessage || error.message  // axios: "Request failed with status code 404"
```

Cuando el servidor devuelve **404 HTML** (no JSON Laravel), no hay `response.data.message` → mensaje crudo de axios.

Si fuera **404 JSON** del backend (tenant inexistente en branches):

- Mensaje sería `"Empresa no encontrada."` (no el texto axios)

**El síntoma reportado confirma 404 HTML de routing**, no error de negocio.

---

## 5. PWA / Service Worker / cache

| Tema | Estado |
|------|--------|
| `VITE_PWA_ENABLED=false` en `.env.production` | Build nuevo sin SW |
| SW viejo en navegador A | Puede servir **JS antiguo** con otra `VITE_API_BASE_URL` |
| `sw.js` MIME text/html | Ocurre si SPA fallback sirve index.html; regla 404 en `.htaccess` lo mitiga |

**Navegador A** puede tener: cookies + **bundle cacheado** `/backend/public/api/v1`.  
**Navegador B** carga **bundle nuevo** `/api/v1` → falla si rewrite no existe.

---

## 6. Comparación DevTools (obligatorio en hosting)

En **ambos** navegadores, en `/login` → Network:

| Campo | Navegador A (funciona) | Navegador B (falla) |
|-------|------------------------|---------------------|
| Request tenants | Probablemente **no aparece** al cargar | **Sí** — falla 404 |
| Request branches | **Sí** — 200 | Solo si eligió empresa |
| URL base | Anotar prefijo completo | Anotar prefijo completo |
| Content-Type respuesta | `application/json` | **`text/html`** si routing roto |
| JS principal | hash `index-XXXX.js` | comparar hash |

Si hashes difieren → **cache/build mezclado confirmado**.

---

## 7. Reglas esperadas vs estado actual

| Regla | Estado |
|-------|--------|
| Incógnito carga empresas | **Falla hoy** si `/api/v1` → 404 HTML |
| Contexto guardado + PIN | **Puede funcionar** vía `/backend/public/api/v1` + branches only |
| Mismo comportamiento todos los navegadores | **Roto** hasta URL API única y rewrite OK |

---

## 8. Plan de solución (frontend, sin codificar aún)

1. **No cambiar login.vue** hasta routing `/api/v1` OK en servidor  
2. **Deploy limpio** — un solo build, borrar assets viejos  
3. **Verificar** `index.html` → un solo `index-*.js`  
4. **Tras curl 200 en `/api/v1/.../tenants`:** mantener `VITE_API_BASE_URL=/api/v1`  
5. **Mientras `/api/v1` falle:** rebuild temporal con `/backend/public/api/v1` **en todos los clientes** (frontend + agente)  
6. **Futuro UX:** mensaje claro en 404 login-context (no axios crudo)  
7. **Futuro:** detectar `Content-Type: text/html` en respuesta API → "Error de configuración del servidor"

---

## 9. Pasos manuales navegador

1. Abrir `/login` en incógnito  
2. Network → filtrar `login-context`  
3. Si URL empieza por `/api/v1` y status 404 HTML → problema servidor `.htaccess`  
4. Si URL empieza por `/backend/public/api/v1` y status 200 → frontend cacheado legacy  
5. Application → Clear storage + unregister SW  
6. Hard reload (Ctrl+Shift+R)

---

## 10. Diagnóstico resumido

| Causa | Confirmada |
|-------|------------|
| `.htaccess` — `/api/v1` no llega a Laravel | **Sí** (probe producción) |
| Build/cache mixto `/api/v1` vs `/backend/public/api/v1` | **Sí** (dist repo + comportamiento) |
| Flujo distinto con/sin cookies | **Sí** (solo B llama tenants) |
| tenant_slug inválido como causa principal en B | **No** |
| Service worker | Posible amplificador en A, no causa raíz |

Ver `backend/HOSTING_LOGIN_CONTEXT_404_AUDIT.md` para pruebas curl y plan hosting.
