# Hosting — Deploy Architecture Audit (Backend)

**Fecha:** 2026-06-25  
**Dominio:** `https://nightpos.ribersoft.com`  
**Alcance:** arquitectura cPanel, URLs, `.htaccess`, `.env`, agente, PWA. Sin features nuevas.

---

## 1. Decisión oficial: **Opción A — API limpia**

| Rol | URL oficial |
|-----|-------------|
| Frontend SPA | `https://nightpos.ribersoft.com/` |
| API REST | `https://nightpos.ribersoft.com/api/v1` |
| Health Laravel | `GET /api/v1/health` |
| Health PHP puro | `GET /backend/public/health.php` (diagnóstico) |
| Laravel físico | `/home/.../nightpos.ribersoft.com/backend/public/index.php` |

**Opción B** (`/backend/public/api/v1`) queda solo como **legacy/transición** mientras exista acceso directo a `/backend/`. **No usar** en frontend, agente ni documentación nueva.

### ¿Opción A es viable en cPanel/LiteSpeed?

**Sí.** Es el patrón estándar: `.htaccess` en document root reescribe `/api/*` al front controller Laravel. NightPOS ya registra rutas con `apiPrefix: 'api'` + grupo `v1` → paths `/api/v1/...`.

---

## 2. Estructura física en hosting

```
/home/vnplktsg/nightpos.ribersoft.com/     ← document root cPanel
├── .htaccess                              ← rewrite API + SPA
├── index.html                             ← Vue dist
├── assets/
├── icons/
├── manifest.webmanifest                   ← inofensivo con PWA off
└── backend/                               ← Laravel (NO borrar en deploy frontend)
    ├── .env
    ├── artisan
    └── public/
        ├── index.php
        ├── health.php
        ├── .htaccess                      ← Laravel estándar
        └── storage/ → symlink
```

---

## 3. Variables backend (`.env` producción)

| Variable | Valor correcto | Incorrecto |
|----------|----------------|------------|
| `APP_URL` | `https://nightpos.ribersoft.com` | `.../backend/public` |
| `APP_ENV` | `production` | `local` |
| `APP_DEBUG` | `false` | `true` |
| `APP_KEY` | `base64:...` (`php artisan key:generate`) | vacío |
| `JWT_SECRET` | generado (`php artisan jwt:secret`) | vacío → "Key cannot be empty" |
| `DB_*` | credenciales cPanel | — |

**No existen** en repo: `FRONTEND_URL`, `CORS_ALLOWED_ORIGINS`, `SANCTUM_STATEFUL_DOMAINS` (API es JWT stateless, mismo origen).

`SESSION_DOMAIN` = `null` (cookies frontend son propias del SPA en Pinia/useCookie, no Sanctum session).

Tras editar `.env`:

```bash
cd backend
php artisan optimize:clear
# NO config:cache hasta JWT_SECRET y APP_KEY OK
```

---

## 4. `.htaccess`

### Raíz (`frontend/public/.htaccess` → dist)

1. Archivos reales primero  
2. `/api/` → `backend/public/index.php`  
3. `/storage/` → `backend/public/storage/`  
4. `/backend/` → sin SPA  
5. **404** para `sw.js` / `workbox` ausentes (evita MIME `text/html`)  
6. Resto → `index.html`  

### `backend/public/.htaccess`

Front controller Laravel estándar — **no modificar** bloque PHP cPanel.

---

## 5. Pruebas URL (probe 2026-06-25)

Desde entorno externo todas devolvieron **ERR_CONNECTION_RESET** (servidor saturado o caído). Tabla = comportamiento **esperado** tras deploy estable:

| URL | Status esperado | Content-Type | Body |
|-----|-----------------|--------------|------|
| `/` | 200 | `text/html` | SPA |
| `/login` | 200 | `text/html` | SPA |
| `/api/v1/auth/login-context/tenants` | 200 | `application/json` | `{ success, data.tenants }` |
| `/backend/public/api/v1/auth/login-context/tenants` | 200 | `application/json` | igual (legacy) |
| `/api/v1/health` | 200 | `application/json` | `{ ok, jwt, db }` |
| `/backend/public/health.php` | 200 | `text/plain` | `PHP OK` |
| `/sw.js` (PWA off) | **404** | — | no HTML |
| `/manifest.webmanifest` | 200 | `application/manifest+json` | JSON |
| `/icons/icon-192.svg` | 200 | `image/svg+xml` | SVG |

---

## 6. Causas raíz de síntomas

| Síntoma | Causa |
|---------|-------|
| ERR_CONNECTION_RESET | LiteSpeed/CloudLinux load alto, PHP killed — no es Vue |
| Network Error login | Mismo + JWT/config |
| Agente recibe HTML (`invalid character '<'`) | URL API cae en SPA `index.html` o 404 HTML — **rewrite `/api/` faltante o URL legacy distinta** |
| sw.js MIME text/html | `sw.js` inexistente pero SPA fallback devuelve `index.html` |
| Login "Key cannot be empty" | `JWT_SECRET` vacío — ver `HOSTING_LOGIN_PIN_DEVICE_KEY_FIX_REPORT.md` |

---

## 7. Agente impresión — URL oficial

```json
{
  "backend_url": "https://nightpos.ribersoft.com/api/v1"
}
```

Misma base que frontend (`VITE_API_BASE_URL=/api/v1`).

Verificar desde PC del local:

```powershell
curl.exe -sS https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants
curl.exe -sS -X POST https://nightpos.ribersoft.com/api/v1/print-devices/heartbeat -H "Authorization: Bearer npd_live_..."
```

Debe ser JSON, no HTML.

---

## 8. PWA en producción

**Decisión:** `VITE_PWA_ENABLED=false` hasta V1.1.

Sin `sw.js` / `registerSW.js` en dist. Regla `.htaccess` devuelve 404 si quedan archivos viejos.

---

## 9. Deploy checklist (orden)

```bash
# 1. Backend
cd backend && composer install --no-dev --optimize-autoloader
php artisan jwt:secret --force   # si falta
php artisan migrate --force
php artisan optimize:clear

# 2. Frontend
cd frontend && npm run build

# 3. Raíz hosting — conservar backend/
cd /home/vnplktsg/nightpos.ribersoft.com
rm -f sw.js registerSW.js workbox-*.js mockServiceWorker.js
find . -maxdepth 1 ! -name backend ! -name . ! -name .. -exec rm -rf {} +
cp -r /path/nightpos/frontend/dist/* .

# 4. Smoke
curl -sS https://nightpos.ribersoft.com/api/v1/health
curl -sS https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants
```

---

## 10. Fix aplicado en repo

Ver `backend/HOSTING_DEPLOY_ARCHITECTURE_FIX_REPORT.md`.

---

**Estado:** arquitectura Opción A documentada e implementada en repo. **Hosting:** pendiente deploy + `JWT_SECRET` + smoke tests.
