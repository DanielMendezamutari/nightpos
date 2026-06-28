# PWA Full Rollback — Estabilización (Backend)

**Fecha:** 2026-06-27  
**Alcance:** configuración API legacy + deploy — **sin cambios POS/SaaS operativo**

---

## 1. Decisión

Frontend y agente usan **temporalmente**:

```
https://nightpos.ribersoft.com/backend/public/api/v1
```

Laravel **no cambia** — mismas rutas `routes/api.php` con prefix `api/v1`.

El path físico `/backend/public/index.php` + `backend/public/.htaccess` es el front controller que **ya funcionaba** antes de PWA/Opción A.

---

## 2. Backend — sin cambios de código

| Componente | Acción |
|------------|--------|
| `routes/api.php` | Sin cambios |
| `AuthController` login-context | Sin cambios |
| `backend/public/.htaccess` | **No modificar** |
| `backend/public/health.php` | Smoke test hosting |
| JWT / `JWT_SECRET` | Mantener configurado (`php artisan jwt:secret`) |

### `.env` producción (recordatorio)

```env
APP_URL=https://nightpos.ribersoft.com
APP_ENV=production
APP_DEBUG=false
```

**No** usar `APP_URL=.../backend/public`.

Tras deploy:

```bash
cd backend
php artisan optimize:clear
```

---

## 3. Por qué no `/api/v1` ahora

| URL | Requiere |
|-----|----------|
| `/backend/public/api/v1/*` | Solo `backend/public/.htaccess` ✓ |
| `/api/v1/*` | `.htaccess` **raíz** con rewrite ✗ (no validado / hosting inestable) |

Usar legacy evita 404 HTML en login-context y agente recibiendo `index.html`.

---

## 4. Smoke tests backend

```bash
curl -i https://nightpos.ribersoft.com/backend/public/health.php
curl -i https://nightpos.ribersoft.com/backend/public/api/v1/health
curl -i https://nightpos.ribersoft.com/backend/public/api/v1/auth/login-context/tenants
```

Esperado:

- `health.php` → `PHP OK`
- API → `Content-Type: application/json`

Si **connection reset** en todo → hosting LiteSpeed (no PWA) — escalar cPanel antes de más cambios.

---

## 5. Vuelta a Opción A (futuro V1.1)

1. Hosting estable 48 h
2. Validar `.htaccess` raíz por etapas (`frontend/CPANEL_HTACCESS_DEPLOY_FIX_REPORT.md`)
3. Cambiar frontend a `VITE_API_BASE_URL=/api/v1`
4. Agente `backend_url=.../api/v1`
5. Rebuild + deploy limpio
6. Luego evaluar reactivar PWA

---

## 6. Relacionados

- `frontend/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
- `agent/PWA_FULL_ROLLBACK_STABILIZATION_REPORT.md`
