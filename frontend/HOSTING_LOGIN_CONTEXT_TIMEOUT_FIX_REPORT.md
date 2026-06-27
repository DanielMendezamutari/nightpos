# Hosting — Login Context Timeout Fix (Frontend P1)

**Fecha:** 2026-06-25  
**Basado en:** `frontend/HOSTING_LOGIN_CONTEXT_TIMEOUT_AUDIT.md`  
**Alcance:** resiliencia frontend — **no reemplaza P0 hosting**

---

## Problema

En `/login`, la carga de empresas (`GET /auth/login-context/tenants`) fallaba con mensaje de timeout axios (15 s) cuando el hosting compartido (LiteSpeed/cola PHP) respondía lento o no respondía. Además, rutas login-context podían esperar refresh JWT proactivo si existía cookie `accessToken` vieja.

---

## Cambios implementados

### 1. Timeout producción — 30 s

**Archivo:** `.env.production`

```env
VITE_API_TIMEOUT_MS=30000
```

**Lectura:** `src/services/http.js` → `Number(import.meta.env.VITE_API_TIMEOUT_MS || 15000)`

Rebuild obligatorio: `npm run build`

### 2. Excluir login-context del refresh JWT

**Archivo:** `src/services/http.js`

`isAuthEndpoint()` ahora incluye explícitamente `/auth/login-context/` (tenants y branches). Esas rutas **no** disparan `tryRefreshSession()` aunque exista JWT viejo en cookie.

### 3. Mensajes de error (`classifyApiError`)

| Tipo | kind | Mensaje usuario |
|------|------|-----------------|
| Timeout | `timeout` | El servidor está tardando más de lo normal. Intente nuevamente en unos segundos. |
| Network / reset | `network` | No se pudo conectar con el servidor. Verifique internet o hosting. |
| 404 HTML (rewrite roto) | `api_routing` | La API no está respondiendo correctamente. Verifique configuración del hosting. |
| JSON backend 4xx | `client` | `message` del API |
| Sin respuesta HTTP | `no_response` | El servidor no responde. Verifique internet o hosting. |

Ya no se muestran mensajes crudos de Axios (`Request failed with status code 404`, etc.) en login.

### 4. UX login (`src/pages/login.vue`)

Cuando falla cargar empresas:

- Botón **Reintentar** (vuelve a llamar tenants)
- Botón **Limpiar contexto local** (cookies tenant/branch + token)
- Si hay contexto guardado: **Usar contexto guardado** / **Cambiar empresa** / **Cambiar sucursal**
- Pestaña Usuario/contraseña sigue disponible (no bloquea toda la pantalla)
- Selects de empresa/sucursal no quedan bloqueados tras error (solo durante carga activa)

---

## Tests

| Archivo | Caso |
|---------|------|
| `src/services/__tests__/http.spec.js` | login-context excluido de refresh; timeout; 404 HTML; network; JSON backend |
| `src/api/__tests__/loginContext.spec.js` | reintento tenants tras fallo simulado |

```bash
cd frontend && npm test
```

---

## P0 hosting — sigue obligatorio

Este P1 **no sustituye** estabilizar el servidor:

1. LiteSpeed / CloudLinux — entry processes y load
2. Confirmar `GET /api/v1/health` → JSON
3. Confirmar `GET /api/v1/auth/login-context/tenants` → JSON (rewrite `.htaccess` Opción A)
4. Deploy limpio de `dist/` + `php artisan optimize:clear` + `jwt:secret`

Ver: `backend/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md`, `frontend/public/.htaccess.example`
