# Sesión operativa — Frontend refresh y expiración

**Fecha:** 2026-06-17

## Cambios

### `stores/auth.js`

- Cookie de sesión: **14 días** (`SESSION_COOKIE_MAX_AGE`) alineado con `JWT_REFRESH_TTL`.
- `persistToken(token)` — actualiza solo access token tras refresh.
- `refreshSession()` — `POST /auth/refresh` con `_skipAuthRefresh`.

### `services/http.js`

Interceptor 401:

1. Si no es login/refresh y no se reintentó → `refreshSession()` una vez.
2. Si refresh OK → reintenta request original con nuevo token.
3. Si falla → `clearSession()` + redirect `/login?reason=session_expired`.

### Login

Mensaje al expirar: **«Tu sesión expiró. Vuelve a ingresar.»**

### SSE

`useOperationalEvents` ya detiene SSE cuando `auth.isAuthenticated` es false (logout).
Tras refresh, SSE reconecta en el próximo ciclo (fetchSseToken usa token actualizado).

## Flujo operativo esperado

1. Cajera entra con PIN → token válido 12 h.
2. Durante el turno, requests API renuevan token silenciosamente si expira access.
3. Si refresh falla (fin de ventana 14 días o logout remoto) → login claro, sin pantalla congelada.

## Sin romper

- Login PIN / password
- Superadmin / admin
- Guards y permisos
