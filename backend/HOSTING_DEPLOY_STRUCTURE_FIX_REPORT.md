# Hosting — Deploy Structure Fix Report (Backend / raíz dominio)

**Fecha:** 2026-06-25  
**Dominio:** `nightpos.ribersoft.com`  
**Alcance:** estructura de archivos, rewrites, health checks. Sin cambios en lógica POS/auth/impresión.

---

## 1. Estructura actual en hosting

Documentado en `backend/database/pasos-para-modificaciones.md`:

```
/home/vnplktsg/nightpos.ribersoft.com/     ← document root
├── index.html                             ← frontend dist (SPA)
├── assets/                                ← chunks Vue
├── sw.js, registerSW.js                   ← PWA (eliminar en rollback)
├── manifest.webmanifest                   ← opcional en rollback
└── backend/                               ← Laravel
    └── public/
        ├── index.php
        ├── health.php                     ← PHP sin Laravel
        └── .htaccess                      ← front controller Laravel
```

**API producción (frontend):** `VITE_API_BASE_URL=/backend/public/api/v1`

URLs reales:

- Health PHP: `https://nightpos.ribersoft.com/backend/public/health.php`
- Health Laravel: `https://nightpos.ribersoft.com/backend/public/api/v1/health`
- Login context: `https://nightpos.ribersoft.com/backend/public/api/v1/auth/login-context/tenants`

---

## 2. ¿Por qué `/api/v1/...` puede fallar distinto?

Si se prueba `https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants`:

- Esa ruta **no es** la configurada en el frontend (`/backend/public/api/v1`).
- Sin rewrite en raíz, LiteSpeed puede:
  - devolver `index.html` (SPA) si hay fallback catch-all, o
  - 404, o
  - reset bajo carga si PHP intenta bootear Laravel por regla incorrecta.

**Acción:** usar siempre `/backend/public/api/v1/...` en pruebas, o añadir rewrite explícito (ver §4).

---

## 3. `.htaccess` Laravel (`backend/public/.htaccess`)

Ya correcto — front controller estándar:

```apache
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

`health.php` se sirve como archivo real (no pasa por Laravel).

---

## 4. `.htaccess` recomendado en raíz del dominio

Plantilla activa: `frontend/public/.htaccess` (incluida en `dist/` al hacer build).

Reglas clave:

1. **Archivos reales primero** (`-f`, `-d`)
2. **`/backend/`** no reescribir a SPA
3. **`/api/`** opcional → `backend/public/index.php` (solo si se quiere alias corto)
4. **Resto** → `index.html` (Vue SPA)
5. **Cache-Control no-cache** para `sw.js`, `registerSW.js`, `workbox-*.js` (cuando PWA vuelva)

### Anti-patrón (rompe API)

```apache
# MAL — manda todo a index.html incluyendo backend
RewriteRule ^ index.html [L]
```

---

## 5. Service worker vs API (diagnóstico)

El SW con scope `/` **no corre en el servidor**; corre en el navegador. No causa `ERR_CONNECTION_RESET` en curl o pestaña nueva sin SW registrado.

Sin embargo:

- SW viejo + navigateFallback puede confundir diagnóstico en el navegador.
- Deploy parcial (sw.js nuevo + assets viejos) genera loops de actualización y más requests.

**Rollback:** eliminar SW del servidor + `VITE_PWA_ENABLED=false` + unregister en cliente.

---

## 6. Limpieza post-deploy (checklist)

```bash
cd /home/vnplktsg/nightpos.ribersoft.com

# Backend intacto
php backend/artisan optimize:clear
php backend/artisan migrate --force

# Frontend limpio (conservar backend/)
find . -maxdepth 1 ! -name backend ! -name . ! -name .. -exec rm -rf {} +
cp -r frontend/dist/* .

# Rollback PWA — borrar si quedaron
rm -f sw.js registerSW.js workbox-*.js mockServiceWorker.js
```

---

## 7. Pruebas servidor (sin navegador)

```bash
curl -sS https://nightpos.ribersoft.com/backend/public/health.php
curl -sS https://nightpos.ribersoft.com/backend/public/api/v1/health
curl -sS https://nightpos.ribersoft.com/backend/public/api/v1/auth/login-context/tenants
```

Si curl también da reset → **LiteSpeed/CloudLinux/PHP**, no PWA ni Vue.

---

## 8. Logs hosting

Revisar cPanel → Errors y `error_log`:

- `LSAPI`, `process killed`, CPU/memory/entry process limits
- `PHP Fatal error`, timeout
- Rewrite loops

---

## 9. Relación con otros reportes

| Documento | Tema |
|-----------|------|
| `frontend/PWA_HOSTING_ROLLBACK_FIX_REPORT.md` | Desactivar PWA, limpiar SW |
| `backend/HOSTING_CONNECTION_RESET_FIX_REPORT.md` | Migraciones, índices, health API |
| `frontend/HOSTING_CONNECTION_RESET_FIX_REPORT.md` | Deploy dist limpio |

---

**Estado:** plantilla `.htaccess` raíz + rutas documentadas. **Pendiente:** copiar `.htaccess.example` a raíz hosting y confirmar curls §7.
