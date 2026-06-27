# cPanel / LiteSpeed — .htaccess Deploy Fix (Backend)

**Fecha:** 2026-06-25  
**Dominio:** `https://nightpos.ribersoft.com`  
**Laravel root:** `/home/vnplktsg/nightpos.ribersoft.com/backend/`  
**Public:** `/home/vnplktsg/nightpos.ribersoft.com/backend/public/`

---

## 1. Dos `.htaccess` distintos — no mezclar

| Ubicación | Rol | Modificar |
|-----------|-----|-----------|
| **Raíz dominio** `/.htaccess` | Vue SPA + rewrite `/api/` → Laravel | Sí — ver `frontend/public/.htaccess` |
| **Laravel** `/backend/public/.htaccess` | Front controller Laravel estándar | **No** (salvo bloque PHP cPanel) |

El `.htaccess` de Laravel **no sirve** en la raíz: no tiene fallback SPA ni regla `/api/` hacia `index.php` desde docroot.

---

## 2. Cómo llega una request API

### Opción A — API limpia (objetivo)

```
GET /api/v1/health
  → raíz .htaccess: RewriteRule ^api/ backend/public/index.php
  → Laravel routes/api.php (prefix api + v1)
  → JSON
```

### Legacy — sin rewrite raíz

```
GET /backend/public/api/v1/auth/login-context/tenants
  → raíz .htaccess: excluido del SPA (!^/backend/)
  → backend/public/.htaccess: !-f → index.php
  → Laravel → JSON
```

**Importante:** si el fallback SPA de la raíz **no** excluye `/backend/`, el agente y el login reciben `index.html` en vez de JSON.

---

## 3. Etapa 1 — baseline sin `.htaccess` raíz

Con `.htaccess.bak` en raíz:

| URL | Backend involucrado | Esperado |
|-----|---------------------|----------|
| `/backend/public/health.php` | PHP directo | `PHP OK 8.x` |
| `/backend/public/api/v1/health` | Laravel | JSON `{ ok, jwt, db }` |
| `/api/v1/health` | **Sin rewrite raíz** | 404 HTML del servidor |

Confirma que Laravel funciona **antes** de probar rewrite `/api/`.

---

## 4. Etapa 3 — rewrite `/api/` en raíz

Regla mínima:

```apache
RewriteRule ^api/ backend/public/index.php [L,QSA]
```

Debe ir **después** de servir archivos reales y **antes** del fallback SPA.

Si falla:

- 404 HTML en `/api/v1/*` → rewrite no activo o `mod_rewrite` off
- reset → saturación LiteSpeed (no regla específica)
- 500 → `.env`, `JWT_SECRET`, permisos `storage/`

---

## 5. Authorization (Etapa 4)

PHP bajo CGI/FastCGi often strips `Authorization`. Regla en **raíz**:

```apache
RewriteCond %{HTTP:Authorization} .
RewriteRule ^ - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
```

Afecta requests que pasan por `backend/public/index.php` vía `/api/` y legacy `/backend/public/`.

Endpoints agente:

- `POST /api/v1/print-devices/heartbeat`
- Headers: `Authorization: Bearer npd_live_...`

---

## 6. Backend `.env` producción

| Variable | Valor |
|----------|-------|
| `APP_URL` | `https://nightpos.ribersoft.com` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `JWT_SECRET` | generado (`php artisan jwt:secret`) |

Tras cambios:

```bash
cd /home/vnplktsg/nightpos.ribersoft.com/backend
php artisan storage:link
php artisan optimize:clear
```

---

## 7. Fallback temporal API legacy

Si cPanel **no** permite rewrite limpio estable:

| Componente | URL |
|------------|-----|
| Frontend | `VITE_API_BASE_URL=/backend/public/api/v1` |
| Agente | `backend_url=https://nightpos.ribersoft.com/backend/public/api/v1` |

Laravel y rutas **no cambian** — solo la URL pública.

Volver a Opción A cuando `/api/v1/health` responda JSON estable.

---

## 8. Pruebas backend (curl)

```bash
curl -i https://nightpos.ribersoft.com/backend/public/health.php
curl -i https://nightpos.ribersoft.com/backend/public/api/v1/health
curl -i https://nightpos.ribersoft.com/api/v1/health
curl -i https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants
```

Probe 2026-06-25: **connection reset** en todas — estabilizar hosting antes de validar `.htaccess`.

---

## 9. Qué NO tocar ahora

- `backend/public/.htaccess` (Laravel estándar)
- Middleware, rutas, SAAS-2, POS
- Reglas complejas en raíz (FilesMatch dotfiles, headers, compresión) — reintroducir solo tras estabilidad

**Frontend:** `frontend/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`  
**Agente:** `agent/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`
