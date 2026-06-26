# Checklist de despliegue NightPOS (F1.1)

**Objetivo:** operar en servidor real con HTTPS y contexto multi-tenant.

## Servidor

- [ ] PHP 8.2+, extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `json`, `bcmath`
- [ ] MySQL/MariaDB 10.4+
- [ ] Node 20+ (solo build frontend)
- [ ] `mysqldump` en PATH (backups `php artisan nightpos:backup-database`)

## Backend (`backend/`)

- [ ] Copiar `.env.example` → `.env`
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://tu-dominio` (**sin** `/backend/public`)
- [ ] `DB_*` credenciales producción
- [ ] `JWT_SECRET` generado (`php artisan jwt:secret`)
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache` y `route:cache` (post-deploy)
- [ ] Scheduler: `* * * * * php artisan schedule:run` (backups diarios si se programan)
- [ ] Queue worker si se activan jobs (`php artisan queue:work`)

## Frontend (`frontend/`)

- [ ] `pnpm install && pnpm run build` (`.env.production`: `VITE_API_BASE_URL=/api/v1`, `VITE_PWA_ENABLED=false`)
- [ ] Copiar **todo** `dist/` a document root (conservar `backend/`)
- [ ] Verificar `.htaccess` en raíz (rewrite `/api/` → Laravel)
- [ ] API oficial: `https://{dominio}/api/v1` (no `/backend/public/api/v1`)

## Red y seguridad

- [ ] HTTPS obligatorio (certificado válido)
- [ ] CORS: solo orígenes del frontend en producción
- [ ] Firewall: MySQL no expuesto públicamente
- [ ] Backups: `php artisan nightpos:backup-database` diario + prueba de restore mensual

## Post-instalación tenant

- [ ] Superadmin: wizard `/nightpos/platform/setup`
- [ ] Admin sucursal: **Checklist 1ª noche** → **Cargar datos iniciales** si catálogo vacío
- [ ] Capacitar: caja por usuario (`CASH_OPERATIONS.md`)

## Smoke mínimo

- [ ] Login PIN cajera y garzón
- [ ] Comanda → enviar barra → cobrar
- [ ] Cerrar caja → cerrar turno
- [ ] Imprimir ticket barra y resumen turno (navegador)
