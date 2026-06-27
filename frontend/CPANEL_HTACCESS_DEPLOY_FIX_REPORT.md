# cPanel / LiteSpeed — .htaccess Deploy Fix (Frontend)

**Fecha:** 2026-06-25  
**Dominio:** `https://nightpos.ribersoft.com`  
**Document root:** `/home/vnplktsg/nightpos.ribersoft.com/`  
**Alcance:** `.htaccess` raíz — SPA Vue + API Laravel. Sin features nuevas.

---

## 1. Estructura real en hosting

```
/home/vnplktsg/nightpos.ribersoft.com/
├── .htaccess                 ← ESTE archivo (mixto Vue + API)
├── index.html                ← Vue dist
├── assets/
├── icons/
├── manifest.webmanifest
└── backend/                  ← Laravel (NO borrar en deploy frontend)
    └── public/
        ├── index.php
        ├── health.php
        ├── .htaccess         ← solo Laravel; NO usar en raíz
        └── storage/ → symlink
```

**Frontend build:** `frontend/public/.htaccess` se copia a `dist/` en `npm run build`.

---

## 2. Probe producción (2026-06-25)

Desde entorno externo, **todas** las URLs probadas devolvieron `curl: (35) Connection reset` (~300 ms):

| URL | Resultado |
|-----|-----------|
| `/` | reset |
| `/index.html` | reset |
| `/backend/public/health.php` | reset |
| `/backend/public/api/v1/auth/login-context/tenants` | reset |

**Conclusión Etapa 1:** con servidor inestable, **no sirve probar `.htaccess` a ciegas**. Primero estabilizar LiteSpeed/entry processes; luego aplicar etapas 2→4.

---

## 3. Método por etapas (obligatorio en servidor)

### Etapa 1 — Sin rewrite

```bash
cd /home/vnplktsg/nightpos.ribersoft.com
mv .htaccess .htaccess.bak
```

Probar en navegador y curl:

| URL | Esperado sin .htaccess |
|-----|------------------------|
| `/` o `/index.html` | 200 HTML (DirectoryIndex) |
| `/login` | **404** (normal sin SPA fallback) |
| `/backend/public/health.php` | 200 `PHP OK` |
| `/backend/public/api/v1/auth/login-context/tenants` | 200 JSON (Laravel `backend/public/.htaccess`) |

Si **Etapa 1** ya da ERR_CONNECTION_RESET → problema **hosting**, no reglas rewrite.

### Etapa 2 — Solo SPA

Copiar desde repo: `frontend/public/.htaccess.stage2-spa-only` → `.htaccess`  
**Conservar** bloque `# php -- BEGIN cPanel-generated handler` del `.htaccess.bak`.

Probar: `/`, `/login`, `/nightpos/test`, un archivo en `/assets/`.

| Resultado | Interpretación |
|-----------|----------------|
| reset en todo | LiteSpeed/config — escalar hosting |
| SPA OK, `/login` 200 | rewrite SPA funciona |
| `/backend/public/...` sigue OK | exclusión `!^/backend/` correcta |

### Etapa 3 — SPA + API limpia

Copiar: `frontend/public/.htaccess.stage3-api` → `.htaccess` (+ bloque PHP cPanel).

Probar:

```bash
curl -i https://nightpos.ribersoft.com/api/v1/health
curl -i https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants
```

Esperado: `Content-Type: application/json`.

Si **Etapa 3 rompe** (reset o 404 HTML): ver §6 fallback legacy.

### Etapa 4 — Final (Authorization)

Copiar: `frontend/public/.htaccess` → `.htaccess` en raíz (+ bloque PHP cPanel).

Probar agente heartbeat con header `Authorization: Bearer npd_live_...`.

---

## 4. `.htaccess` final (repo)

Archivo: `frontend/public/.htaccess`

Orden de reglas (crítico):

1. `Authorization` → env (agente + JWT)
2. Archivos reales `-f` / `-d` → servir directo
3. `^api/` → `backend/public/index.php`
4. `^storage/` → `backend/public/storage/`
5. `sw.js` etc. → **404** (no HTML)
6. SPA → `index.html` **solo si** URI no es `/api/`, `/backend/`, `/storage/`

### Errores evitados en plantillas incorrectas

| Regla mala | Problema |
|------------|----------|
| `RewriteRule ^.*$ index.html` sin exclusiones | `/backend/public/api/v1/*` devuelve **HTML** → agente falla |
| `RewriteRule ^backend/(.*)$ backend/$1` | No-op inútil |
| API **después** del fallback SPA | `/api/v1/*` nunca llega a Laravel |
| `<FilesMatch "^\.">` + headers en raíz | LiteSpeed/cPanel sensibles — **omitidos** en versión mínima |
| Bloque `^backend/(?!public/)` agresivo | Puede romper legacy; omitido hasta estabilizar |

---

## 5. Variables frontend

**Preferido (Opción A):**

```env
VITE_API_BASE_URL=/api/v1
```

**Fallback temporal** si Etapa 3 falla en hosting:

```env
VITE_API_BASE_URL=/backend/public/api/v1
```

Rebuild: `npm run build` y redeploy `dist/*` (sin borrar `backend/`).

---

## 6. Pruebas finales obligatorias

Con `.htaccess` final + servidor estable:

```bash
curl -I https://nightpos.ribersoft.com/
curl -I https://nightpos.ribersoft.com/login
curl -I https://nightpos.ribersoft.com/assets/
curl -i https://nightpos.ribersoft.com/api/v1/health
curl -i https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants
curl -i https://nightpos.ribersoft.com/backend/public/api/v1/auth/login-context/tenants
curl -I https://nightpos.ribersoft.com/sw.js
```

| URL | Esperado |
|-----|----------|
| `/` | 200 HTML |
| `/login` | 200 HTML |
| `/api/v1/health` | 200 JSON |
| `/api/v1/auth/login-context/tenants` | 200 JSON |
| `/backend/public/api/v1/...` | 200 JSON (legacy) |
| `/sw.js` | **404** (no `text/html`) |

---

## 7. Deploy checklist

1. `mv .htaccess .htaccess.bak` → Etapa 1 smoke
2. Etapa 2 → 3 → 4 según §3
3. Al pegar `.htaccess` nuevo: **fusionar** bloque PHP cPanel del backup
4. `cd frontend && npm run build`
5. Subir `dist/*` a raíz; **no** eliminar `backend/`
6. `rm -f sw.js registerSW.js workbox-*.js` en raíz si quedaron de build viejo
7. Ejecutar curls §6

---

## 8. Archivos en repo

| Archivo | Uso |
|---------|-----|
| `frontend/public/.htaccess` | Final producción |
| `frontend/public/.htaccess.stage2-spa-only` | Diagnóstico |
| `frontend/public/.htaccess.stage3-api` | Diagnóstico API |
| `frontend/public/.htaccess.example` | Referencia |

**Relacionados:** `backend/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`, `agent/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`
