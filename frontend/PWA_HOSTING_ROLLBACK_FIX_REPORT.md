# PWA Hosting Rollback — Fix Report (Frontend)

**Fecha:** 2026-06-25  
**Prioridad:** P0 — estabilizar producción antes de reactivar app instalable.  
**Alcance:** solo PWA / deploy / frontend. Sin cambios en POS, auth JWT, impresión ni backend operativo.

---

## 1. Diagnóstico (respuestas)

| # | Pregunta | Respuesta |
|---|----------|-----------|
| 1 | ¿El service worker estaba activo en producción? | **Sí, muy probable.** El build actual incluye `registerSW.js` → registra `/sw.js` con scope `/` en cada visita. |
| 2 | ¿`mockServiceWorker.js` estaba siendo servido? | **Sí, en `dist/`** (artefacto viejo). MSW en código solo arranca si `VITE_USE_MSW=true`; producción tiene `false`. El archivo en raíz **no debería estar** — eliminar en hosting. |
| 3 | ¿Workbox interceptaba `/api/*`? | **Fetch sí (scope `/`), handler NetworkOnly** para rutas con `/api/` en pathname (incluye `/backend/public/api/v1`). No cachea respuestas API, pero **sí intercepta** cada request (overhead + bugs si SW viejo). |
| 4 | ¿`.htaccess` mandaba `/api` a `index.html`? | **Riesgo si falta regla.** Sin `.htaccess` en raíz, LiteSpeed puede devolver SPA o 404 según config. Ver `backend/HOSTING_DEPLOY_STRUCTURE_FIX_REPORT.md`. |
| 5 | ¿Tras desactivar PWA desaparece el error? | **Pendiente confirmar en hosting.** El reset en URL API directa apunta también a saturación PHP (load ~24); PWA puede agravar pero no es la única causa. |
| 6 | ¿Qué eliminar del hosting? | Ver §4 abajo. |
| 7 | ¿Cómo reactivar PWA seguro? | Ver §6 abajo. |

### Conclusión técnica

- **PWA activa** = `sw.js` + precache + navigateFallback SPA. Puede amplificar carga y dejar SW obsoleto tras deploys parciales.
- **`mockServiceWorker.js` en producción** = error de deploy (archivo de desarrollo MSW).
- **`ERR_CONNECTION_RESET` en API directa** no es “solo caché Vue”; pero desactivar SW elimina una capa de riesgo y reduce requests interceptados.

---

## 2. Fix implementado: `VITE_PWA_ENABLED=false`

### Comportamiento cuando `false` (`.env.production` ya configurado)

| Componente | Efecto |
|------------|--------|
| `vite-plugin-pwa` | **No se incluye** en build → sin `sw.js`, sin `registerSW.js`, sin workbox |
| `index.html` | Se eliminan manifest link y metas PWA/iOS en build |
| `usePwaManifest.js` | No-op |
| `useSwUpdate.js` | No-op |
| `InstallPwaBanner.vue` | Oculto |
| `main.js` | Desregistra SW legacy al cargar la app |
| `OfflineBanner.vue` | **Sigue activo** (solo red; no es SW) |

### Archivos tocados

- `frontend/vite.config.js` — PWA condicional + strip HTML
- `frontend/src/utils/pwaEnabled.js` — flag central + unregister
- `frontend/src/App.vue`, `main.js`
- `frontend/src/composables/usePwaManifest.js`, `useSwUpdate.js`
- `frontend/src/components/nightpos/layout/InstallPwaBanner.vue`
- `frontend/.env.production` → `VITE_PWA_ENABLED=false`
- `frontend/.env.example` — documentación
- `frontend/public/.htaccess.example` — SPA + backend sin capturar API

---

## 3. Workbox (cuando PWA estaba activa)

Config previa en `vite.config.js`:

- `/api/*`, `/backend/public/api/*`, `/events/*`, `/sanctum/*`, `/broadcasting/*`, `/storage/*` en **navigateFallbackDenylist**
- Runtime **NetworkOnly** para paths con `/api/`, events, sanctum, broadcasting
- Duplicado `/events/` en denylist corregido al refactor

Con PWA desactivada, Workbox **no se genera**.

---

## 4. Limpieza obligatoria en hosting

Después de `npm run build` con `VITE_PWA_ENABLED=false`:

```bash
cd /home/vnplktsg/nightpos.ribersoft.com

# Borrar SW y artefactos PWA viejos
rm -f sw.js registerSW.js workbox-*.js mockServiceWorker.js

# Limpiar dist anterior (conservar backend/)
find . -maxdepth 1 ! -name backend ! -name . ! -name .. -exec rm -rf {} +

# Subir dist nuevo completo
cp -r /path/to/nightpos/frontend/dist/* .
```

**En cada navegador de prueba:**

1. DevTools → Application → Service Workers → **Unregister**
2. Clear site data / storage
3. Probar en **ventana incógnito**

### Archivos que NO deben quedar en raíz (modo SPA)

- `sw.js`
- `registerSW.js`
- `workbox-*.js`
- `mockServiceWorker.js`

---

## 5. Pruebas post-rollback

| # | URL | Esperado |
|---|-----|----------|
| 1 | `/backend/public/health.php` | `PHP OK` |
| 2 | `/backend/public/api/v1/health` | JSON `{ ok: true }` |
| 3 | `/backend/public/api/v1/auth/login-context/tenants` | JSON lista tenants |
| 4 | `/` | SPA login |
| 5 | `/login` | SPA |
| 6 | `/nightpos/platform/control-center` | SPA (superadmin) |

> Nota: la API en producción usa `VITE_API_BASE_URL=/backend/public/api/v1`. Probar también `/api/v1/...` solo si existe rewrite en `.htaccess`.

---

## 6. Reactivar PWA de forma segura (V1.1)

1. Confirmar hosting estable 48 h (health + login-context + load normal).
2. En `.env.production`: `VITE_PWA_ENABLED=true`
3. `npm run build` — verificar que existen `sw.js` + `registerSW.js` y **no** `mockServiceWorker.js`
4. Deploy limpio §4
5. QA: instalar garzón, offline shell, update prompt, API sigue NetworkOnly
6. Documentar bump `VITE_APP_VERSION` en cada release

---

## 7. Build local

```bash
cd frontend
npm run build
# Verificar dist/index.html NO contiene registerSW.js
# Verificar dist/ NO contiene sw.js ni workbox-*.js
```

**Estado repo:** rollback PWA listo. **Producción:** pendiente rebuild + deploy limpio + limpieza SW en hosting.
