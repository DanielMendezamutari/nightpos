# HOSTING — Auth / API Stability Fix Report (Frontend)

**Fecha:** 2026-06-25

## Cambios en `src/services/http.js`

1. **`classifyApiError()`** — mensajes distintos para timeout, red, 401, 403, 419, 500
2. **Refresh proactivo** — si el JWT expira en < 5 min, refresh antes del request
3. **Logging dev** — `[nightpos:api]` + hook `window.__nightposStability.pushApiEvent`
4. **`VITE_API_TIMEOUT_MS`** — timeout configurable (recomendado 30000 en hosting lento)

## PWA (`vite.config.js`)

- `NetworkOnly` para `/api/`, `/events/`, `/sanctum/`, `/broadcasting/`
- `navigateFallbackDenylist` ampliado para no servir `index.html` en rutas API/events

## Debug dev

`NightPosStabilityDebug`: últimos eventos API + botón limpiar SW/cache.

## Deploy

```bash
npm run build
```

Incrementar `VITE_APP_VERSION` en cada release para forzar update PWA.

Usuarios con sesión antigua: logout/login una vez tras deploy.

## Comportamiento esperado

| Escenario | UX |
|-----------|-----|
| Token vencido pero refresh OK | Operación continúa sin mensaje |
| Refresh imposible | Redirect login “Tu sesión expiró” |
| Timeout hosting | “El servidor tardó demasiado…” (no “sesión”) |
| Backend caído | “Sin conexión con el servidor…” |
