# Hosting — Deploy Architecture Fix Report (Backend)

**Fecha:** 2026-06-25  
**Decisión:** Opción A — `/api/v1` oficial.

---

## Cambios aplicados

| Archivo | Cambio |
|---------|--------|
| `backend/.env.example` | Comentarios hosting: `APP_URL=https://nightpos.ribersoft.com` (sin `/backend/public`) |
| `backend/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md` | Arquitectura oficial + checklist |
| `backend/database/pasos-para-modificaciones.md` | Playbook deploy actualizado |

## Sin cambios de código Laravel

Las rutas ya usan prefix `api` + `v1`. No requiere modificar `routes/api.php`.

## Acciones obligatorias en servidor

1. `APP_URL=https://nightpos.ribersoft.com`
2. `JWT_SECRET` + `APP_KEY` configurados
3. `php artisan optimize:clear`
4. Subir `.htaccess` raíz desde `frontend/dist`
5. Smoke `/api/v1/health` y `/api/v1/auth/login-context/tenants`

## Legacy Opción B

`/backend/public/api/v1/...` sigue funcionando si `/backend/` no pasa por SPA. Deprecar en docs; unificar clientes en `/api/v1`.
