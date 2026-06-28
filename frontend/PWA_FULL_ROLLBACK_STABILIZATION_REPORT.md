# PWA Full Rollback — Estabilización (Frontend)

**Fecha:** 2026-06-27  
**Decisión:** PWA / app instalable **OFF** hasta V1.1  
**Alcance:** rollback deploy + API legacy — **sin tocar POS/caja/comandas**

---

## 1. Cambios implementados en repo

### Producción (`.env.production`)

```env
VITE_PWA_ENABLED=false
VITE_API_BASE_URL=/backend/public/api/v1
VITE_API_TIMEOUT_MS=30000
VITE_USE_MSW=false
```

**No usar `/api/v1`** hasta validar `.htaccess` raíz en hosting estable.

### Build

| Cambio | Archivo |
|--------|---------|
| Sin `vite-plugin-pwa` en build prod | `vite.config.js` (condicional `VITE_PWA_ENABLED`) |
| Borra `sw.js`, `registerSW.js`, `workbox-*.js`, `mockServiceWorker.js` post-build | `vite.config.js` → `nightpos-strip-pwa-msw-artifacts` |
| Strip manifest/metas PWA de `index.html` | `nightpos-strip-pwa-html` |
| Limpieza SW legacy en browser | `main.js` → `unregisterServiceWorkersIfDisabled()` |
| InstallPwaBanner / manifest dinámico | **Inactivos** (`isPwaEnabled()` false) |
| App.vue | No carga `usePwaManifest` / `useSwUpdate` si PWA off |

### Build manual (tu flujo habitual)

```bash
cd frontend
npm run build -- --mode production
```

El plugin `nightpos-strip-pwa-msw-artifacts` en `vite.config.js` elimina `sw.js`, `registerSW.js`, `workbox-*.js` y `mockServiceWorker.js` al final del build cuando PWA está off.

---

## 2. Deploy hosting (obligatorio en servidor)

```bash
cd /home/vnplktsg/nightpos.ribersoft.com

# 1. Residuos PWA
rm -f sw.js registerSW.js workbox-*.js mockServiceWorker.js

# 2. Limpiar raíz SPA (conservar backend/)
find . -maxdepth 1 ! -name backend ! -name . ! -name .. -exec rm -rf {} +

# 3. Subir dist nuevo (git pull + copiar frontend/dist/*)
cp -r /path/nightpos/frontend/dist/* .

# 4. Verificar .htaccess raíz presente (SPA fallback para /login)
#    NO requiere rewrite /api/ mientras API sea /backend/public/api/v1
```

---

## 3. Navegadores (cada PC / garzón / caja)

1. DevTools → Application → Service Workers → **Unregister**
2. **Clear site data**
3. Ctrl + F5
4. Probar en **ventana incógnito**

---

## 4. Verificación post-deploy

| Prueba | Esperado |
|--------|----------|
| `/` | 200 HTML SPA |
| `/login` (F5) | 200 HTML (no 404) |
| `/backend/public/api/v1/auth/login-context/tenants` | JSON |
| `/sw.js` | 404 o ausente (no HTML) |
| Navegador nuevo → empresas en login | Lista tenants |
| `dist/index.html` | Sin `registerSW.js`, sin manifest link |

```bash
curl -I https://nightpos.ribersoft.com/login
curl -i https://nightpos.ribersoft.com/backend/public/api/v1/auth/login-context/tenants
curl -I https://nightpos.ribersoft.com/sw.js
```

---

## 5. Qué queda pausado hasta V1.1

- `VITE_PWA_ENABLED=true`
- `vite-plugin-pwa` / Workbox / precache / navigateFallback
- `InstallPwaBanner` en producción
- Manifest dinámico garzón/caja
- API limpia `/api/v1` como URL oficial

---

## 6. Relacionados

- `backend/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
- `agent/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
- `frontend/PWA_DESKTOP_HOSTING_REGRESSION_AUDIT.md`
