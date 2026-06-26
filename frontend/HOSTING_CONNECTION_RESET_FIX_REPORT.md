# Hosting — ERR_CONNECTION_RESET — Fix Report (Frontend)

**Fecha:** 2026-06-25  
**Contexto:** `ERR_CONNECTION_RESET` en `https://nightpos.ribersoft.com` y en API directa (`/api/v1/auth/login-context/tenants`).  
**Conclusión:** el reset en URL de API **no** lo causa Vue, JWT ni Service Worker; apunta a servidor PHP/LiteSpeed saturado. Este documento cubre despliegue frontend y descarte de PWA/cache.

---

## 1. Descarte PWA / Service Worker

El Service Worker **no puede** provocar `ERR_CONNECTION_RESET` cuando se abre la URL de API directamente en Chrome.

Aun así, tras estabilizar backend:

| Regla | Estado en repo |
|-------|----------------|
| API siempre red | `vite.config.js` — `NetworkOnly` para `/api/`, `/sanctum/`, `/broadcasting/`, events |
| No cachear HTML de API | Workbox `navigateFallbackDenylist` incluye rutas API |
| Bump de versión SW | `VITE_APP_VERSION` en build |

### Desregistrar SW temporalmente (dev / soporte)

- Pantalla debug: `NightPosStabilityDebug.vue` (botón desregistrar SW).
- Manual: DevTools → Application → Service Workers → Unregister + hard reload.
- Producción: subir build nuevo con versión distinta en manifest.

---

## 2. Deploy frontend limpio (evitar assets mezclados)

Git status muestra muchos hashes viejos/nuevos en `frontend/dist/` — señal de builds mezclados si se sube parcialmente.

**Procedimiento recomendado en hosting:**

1. **Borrar** carpeta `dist` (o `public_html` SPA) completa en servidor.
2. Local: `npm run build` en `frontend/`.
3. **Subir** todo el contenido de `frontend/dist/` de una sola vez (no copiar archivos sueltos).
4. Limpiar cache navegador + desregistrar SW en estaciones de prueba.
5. Verificar que `index.html` referencia hashes nuevos (un solo hash por chunk).

---

## 3. Variables de entorno (build)

```env
VITE_API_TIMEOUT_MS=30000
VITE_APP_VERSION=2026.06.25-hosting-stabilize
```

Tras cambiar versión, rebuild obligatorio para que el SW no sirva bundle anterior.

---

## 4. Orden de prueba post-deploy

1. `https://nightpos.ribersoft.com/health.php` (backend, sin SPA)
2. `https://nightpos.ribersoft.com/api/v1/health`
3. `https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants`
4. Homepage SPA `/`
5. Solo entonces: `/nightpos/platform/control-center` (superadmin)

Si 1–3 fallan con reset → **no** depurar frontend; ver `backend/HOSTING_CONNECTION_RESET_FIX_REPORT.md`.

---

## 5. Reducir presión al servidor desde cliente (operativo)

Mientras load hosting esté alto:

- Evitar muchas pestañas NightPOS abiertas (SSE + polling).
- No dejar Control Center abierto en auto-refresh si el dashboard es pesado.
- Garzón PWA: preferir una sesión por dispositivo.

Estos mitigan carga pero **no sustituyen** fix backend/hosting.

---

## 6. Relación con auth stability previo

Fixes ya en repo (complementarios, no causan reset):

- `http.js` — clasificación de errores, refresh proactivo JWT
- PWA NetworkOnly ampliado
- Docs: `HOSTING_AUTH_API_STABILITY_*`

---

## 7. Checklist resultado

| Pregunta | Acción |
|----------|--------|
| ¿SW cachea API? | No — NetworkOnly |
| ¿dist mezclado? | Redeploy limpio §2 |
| ¿Reset en API directa? | Backend/hosting, no Vue |
| ¿Homepage OK pero API no? | Backend |
| ¿Todo resetea? | LiteSpeed/CloudLinux + migrate + índices |

**Estado repo:** PWA y deploy doc listos. **Producción:** pendiente redeploy limpio y pruebas §4.
