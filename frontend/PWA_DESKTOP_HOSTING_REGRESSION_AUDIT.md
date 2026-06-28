# PWA / Desktop / Hosting — Regresión (Auditoría Frontend)

**Fecha:** 2026-06-27  
**Dominio:** `https://nightpos.ribersoft.com`  
**Alcance:** diagnóstico raíz PWA + deploy — **sin implementación**

---

## 1. Causa raíz más probable

**Combinación de tres capas** (no una sola):

```
┌─────────────────────────────────────────────────────────────┐
│ 1. HOSTING LiteSpeed — ERR_CONNECTION_RESET (~300 ms)       │
│    Afecta TODO incluso health.php sin .htaccess raíz        │
└─────────────────────────────────────────────────────────────┘
                              +
┌─────────────────────────────────────────────────────────────┐
│ 2. DEPLOY / URL API — /backend/public/api/v1 → /api/v1      │
│    Nuevo .htaccess raíz requerido; si falta → 404 HTML      │
│    Navegador nuevo llama tenants → falla; viejo con cookies │
│    puede omitir tenants y usar branches legacy              │
└─────────────────────────────────────────────────────────────┘
                              +
┌─────────────────────────────────────────────────────────────┐
│ 3. PWA (Jun 2026) — SW + precache + sw.js MIME text/html    │
│    Agravó caché y confusión; rollback código hecho pero     │
│    hosting/browsers pueden conservar SW y archivos viejos   │
└─────────────────────────────────────────────────────────────┘
```

**PWA alone no explica** connection reset en `health.php`.  
**Hosting alone no explica** navegador viejo vs nuevo con mismos síntomas intermitentes.  
**Deploy + URL change** explica 404 HTML, agente con `<!DOCTYPE`, login-context sin empresas.

---

## 2. Qué se implementó (PWA / instalable / garzón)

| Entrega | Archivos / deps | Riesgo producción |
|---------|-----------------|-------------------|
| `vite-plugin-pwa` | `package.json`, `vite.config.js` | Genera `sw.js`, workbox, precache |
| Service worker | `registerSW.js` inyectado en `index.html` | Registro automático scope `/` |
| Workbox | `navigateFallback: index.html` | Rutas virtuales → shell SPA offline |
| Manifests | `manifest.webmanifest`, `manifest-waiter.webmanifest` | Bajo riesgo solo (sin SW) |
| Install UX | `InstallPwaBanner.vue`, `usePwaManifest.js` | Bajo |
| Offline UI | `OfflineBanner.vue` | Solo red — no SW |
| Desktop audit | `desktop/NIGHTPOS_DESKTOP_APP_AUDIT.md` | Propuesta PWA/Electron — no binario |
| Android garzón | Misma PWA + manifest waiter | Portrait, start_url waiter |

**Rollback repo (`ba2fc8f`, `VITE_PWA_ENABLED=false`):**

- Plugin PWA condicional off
- `main.js` → `unregisterServiceWorkersIfDisabled()`
- `index.html` build sin manifest/registerSW
- **Dist actual repo:** sin `sw.js` ✓

---

## 3. Estado actual repo vs hosting (evidencia)

### `frontend/.env.production` (repo)

```env
VITE_API_BASE_URL=/api/v1
VITE_API_TIMEOUT_MS=30000
VITE_USE_MSW=false
VITE_PWA_ENABLED=false
```

### Estado estable anterior (`a7f2578`)

```env
VITE_API_BASE_URL=/backend/public/api/v1
```

### `frontend/dist/index.html` (repo actual)

- Sin `registerSW.js` ✓
- Sin `<link rel="manifest">` ✓
- Entry: `/assets/index-BQ1dVA52.js`

### `frontend/dist/` — señales deploy mezclado

| Hallazgo | Detalle |
|----------|---------|
| **73 archivos** `index-*.js` en `assets/` | Solo **1** referenciado por `index.html` — resto huérfanos de builds anteriores **commiteados en git** |
| `sw.js` / `registerSW.js` | **Ausentes** en dist actual |
| Env baked | `VITE_API_BASE_URL:"/api/v1"` en chunks |
| `backend/public/api/v1` en dist | **0 matches** en assets actuales (build unificado) |

**Riesgo hosting:** si en servidor quedó dist **viejo** mezclado + SW files, síntomas persisten aunque repo esté limpio.

---

## 4. Archivos revisados

| Archivo | Estado / hallazgo |
|---------|-------------------|
| `vite.config.js` | PWA solo si `VITE_PWA_ENABLED !== 'false'`; strip MSW en build |
| `src/main.js` | Unregister SW al boot si PWA off |
| `src/utils/pwaEnabled.js` | Flag central |
| `src/plugins/pwa.js` | Registro SW (inactivo con flag off) |
| `src/services/http.js` | `baseURL` desde env; login-context skip refresh (P1) |
| `src/api/loginContext.js` | `/auth/login-context/tenants` |
| `src/pages/login.vue` | UX reintentar (P1) |
| `public/.htaccess` | Mínimo SPA+API — ver CPANEL report |
| `public/manifest*.webmanifest` | Estáticos — OK con PWA off |

---

## 5. PWA — respuestas detalladas

| Pregunta | Respuesta |
|----------|-----------|
| ¿SW en producción (repo build)? | **No** |
| ¿SW en browser usuario? | **Posible** si visitó durante periodo PWA — unregister manual |
| ¿Workbox interceptaba API? | Config tenía **NetworkOnly** para `/api/` — no cachea JSON pero **intercepta** |
| ¿sw.js MIME text/html? | Ocurre si **no** hay regla 404 y SPA fallback sirve `index.html` |
| ¿mockServiceWorker.js? | Commit `ba2fc8f` aún listaba en dist — **eliminar en hosting** |

---

## 6. `.htaccess` raíz — análisis

**Repo (`frontend/public/.htaccess`) — lógica correcta:**

1. Estáticos `-f`/`-d`
2. `^api/` → `backend/public/index.php`
3. `^storage/` → storage symlink
4. `sw.js` → 404
5. SPA → `index.html` excluyendo `/api/`, `/backend/`, `/storage/`

**Errores vistos en plantillas incorrectas:**

- SPA fallback sin excluir `/backend/` → legacy API devuelve HTML
- Reglas complejas (headers, FilesMatch, bloqueos) → LiteSpeed sensible — **removidas** en versión mínima
- Falta bloque PHP cPanel → PHP puede dejar de ejecutarse

**`/login` 404 al recargar:** típico de **SPA fallback ausente o mal ordenado** — no de Laravel.

---

## 7. Navegador viejo vs nuevo

| | Navegador con cookies | Navegador nuevo / incógnito |
|--|----------------------|----------------------------|
| Llama `tenants` | **Often NO** (va directo a PIN) | **SÍ** (select-context) |
| URL API | Puede usar cache/build legacy | Build `/api/v1` |
| SW | Puede tener registro viejo | Sin SW hasta primera visita post-PWA |
| Síntoma | Login PIN puede funcionar | "No carga empresas" / 404 |

Ver: `frontend/HOSTING_LOGIN_CONTEXT_404_AUDIT.md`

---

## 8. Pruebas por etapa (protocolo)

### Etapa 1 — `.htaccess.bak`

| URL | Esperado si hosting OK |
|-----|--------------------------|
| `/`, `/index.html` | 200 HTML |
| `/login` | **404** (normal) |
| `/backend/public/health.php` | PHP OK |
| `/backend/public/api/v1/.../tenants` | JSON |

**Probe 2026-06-27:** todo **reset** → **no pasar a Etapa 2** hasta estabilizar.

### Etapa 2 — SPA only (`.htaccess.stage2-spa-only`)

- `/login` → 200 HTML
- Si **reset** → problema LiteSpeed, no API

### Etapa 3 — + API (`.htaccess.stage3-api`)

- `/api/v1/health` → JSON

### Etapa 4 — final + Authorization

- Agente heartbeat

---

## 9. Recomendación producción hoy

### Opción A — Estabilizar rápido ✓

1. PWA **off** (hecho en repo)
2. Hosting: borrar SW artifacts
3. Browsers: unregister SW + clear storage
4. Rebuild con `VITE_API_BASE_URL=/backend/public/api/v1` **temporal**
5. Deploy limpio: `find . -maxdepth 1 ! -name backend -exec rm -rf {} +` luego `cp dist/*`
6. Verificar legacy tenants JSON antes de volver a `/api/v1`

### Opción B — Definitiva (post-estabilidad)

1. `.htaccess` final por etapas
2. `VITE_API_BASE_URL=/api/v1`
3. Smoke curls completos
4. PWA permanece off hasta V1.1

---

## 10. Qué pausar hasta V1.1

- `VITE_PWA_ENABLED=true`
- `InstallPwaBanner` activo en producción
- Desktop wrapper / Electron
- Segunda manifest waiter en producción instalable
- Headers/compresión agresivos en `.htaccess` raíz

---

## 11. Relacionados

- `backend/PWA_DESKTOP_HOSTING_REGRESSION_AUDIT.md`
- `agent/PWA_DESKTOP_HOSTING_REGRESSION_AUDIT.md`
- `frontend/PWA_IMPLEMENTATION_REPORT.md`
- `frontend/PWA_HOSTING_ROLLBACK_FIX_REPORT.md`
- `frontend/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`
