# HOSTING — Auth / API Stability Audit (Frontend)

**Fecha:** 2026-06-25  
**Prioridad:** Urgente  
**Estado:** Auditado + fixes aplicados

---

## Síntoma

Usuario ve **“Error de comunicación con el servidor”** o **“No se pudo conectar con el servidor”** (login timeout) cuando el problema real puede ser:

- token vencido sin refresh exitoso
- timeout 15s en hosting lento
- respuesta no-JSON del backend (antes del fix JWT)
- confusión entre error de red real y 401

---

## Flujo auth frontend (axios)

Archivo: `src/services/http.js`

1. Request interceptor añade `Authorization`, `X-Tenant-Slug`, `X-Branch-Code`
2. Si token expira en **< 5 min** → refresh proactivo (deduplicado)
3. Response interceptor ante **401**:
   - intenta `POST /auth/refresh` **una vez** (`_retried`)
   - si OK → reintenta request original
   - si falla → `clearSession()` + redirect `/login?reason=session_expired`
4. Timeout axios: **15s** (`VITE_API_TIMEOUT_MS` configurable)

Cookies: `accessToken`, `userData` (14 días maxAge alineado a refresh window).

---

## Hallazgos

### 1. Mensaje genérico en todos los errores (ALTO)

`getApiErrorMessage()` devolvía siempre fallback **“Error de comunicación con el servidor”** para timeouts/red/401 sin cuerpo JSON.

**Fix:** `classifyApiError()` distingue:

| kind | Mensaje usuario |
|------|-----------------|
| timeout | servidor tardó demasiado |
| network | sin conexión |
| unauthorized | sesión expirada |
| forbidden | permiso |
| server | error 500 |

### 2. Sin refresh proactivo (MEDIO)

Solo se refrescaba **después** del primer 401 → race con blacklist en requests paralelas.

**Fix:** refresh silencioso si `exp - now < 5 min`.

### 3. PWA / Service Worker (BAJO — ya bien configurado)

`vite.config.js` Workbox:

- `NetworkOnly` para rutas `/api/`, `/events/`, `/sanctum/`, `/broadcasting/`
- `navigateFallbackDenylist` incluye API y events

**Riesgo residual:** build viejo en cache del navegador → usar prompt update (`useSwUpdate`) o limpiar SW.

**Dev:** botón “Limpiar SW + cache” en `NightPosStabilityDebug`.

### 4. Login vs operación

- Login timeout → mensaje explícito Apache/MySQL (`login.vue`)
- Operación → toast vía `getApiErrorMessage()` — ahora más preciso

### 5. Router guard

403 por permiso ≠ 401 auth. Guard no intenta refresh en 403 (solo axios en 401).

Superadmin bypass ya aplicado en guards de plataforma.

---

## Diagnóstico obligatorio (DevTools)

Cuando falle:

1. Network → request roja → **Status**, **Response**, **Timing**
2. ¿Se llamó `/auth/refresh`?
3. ¿Refresh status 200 o 401?
4. ¿Request original se reintentó?
5. Console (dev): `[nightpos:api] refresh_attempt|refresh_success|refresh_fail`

Panel **DBG** (solo dev): últimos eventos API.

---

## Config producción

`.env.production`:

```env
VITE_API_BASE_URL=/backend/public/api/v1
VITE_API_TIMEOUT_MS=30000
VITE_APP_VERSION=1.0.1
```

Tras deploy:

```bash
npm run build
```

Logout/login en clientes con sesión vieja.

---

## Casos de prueba (manual hosting)

1. Admin 30 min idle → acción → debe refresh o seguir operando
2. Cajera PIN → misma prueba
3. Garzón PWA instalada vs navegador
4. Con SW desactivado (dev botón o Application → Unregister)
5. Simular token viejo: borrar `accessToken` cookie → debe ir a login claro

---

## Archivos modificados (fix)

- `src/services/http.js` — classifier, refresh proactivo, logging dev
- `vite.config.js` — PWA denylist ampliado
- `src/components/nightpos/dev/NightPosStabilityDebug.vue` — eventos API + limpiar SW

Ver `frontend/HOSTING_AUTH_API_STABILITY_FIX_REPORT.md`.
