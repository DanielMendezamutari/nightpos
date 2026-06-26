# Deploy hosting — nightpos.ribersoft.com (Opción A)

Document root cPanel:

```
/home/vnplktsg/nightpos.ribersoft.com
```

## Arquitectura oficial

| Componente | Ubicación / URL |
|--------------|-----------------|
| Frontend SPA | Raíz del dominio (`index.html`, `assets/`) |
| API | `https://nightpos.ribersoft.com/api/v1` |
| Laravel | `/backend/public/index.php` (no exponer en clientes) |
| Agente | `backend_url`: `https://nightpos.ribersoft.com/api/v1` |

---

## 1. Backend (`backend/.env`)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://nightpos.ribersoft.com
APP_KEY=base64:...
JWT_SECRET=...
```

```bash
cd backend
composer install --no-dev --optimize-autoloader
php artisan key:generate --force    # si falta APP_KEY
php artisan jwt:secret --force      # si falta JWT_SECRET
php artisan migrate --force
php artisan optimize:clear
```

---

## 2. Frontend

```bash
cd frontend
# .env.production ya tiene VITE_API_BASE_URL=/api/v1 y VITE_PWA_ENABLED=false
npm run build
```

---

## 3. Publicar en raíz (conservar `backend/`)

```bash
cd /home/vnplktsg/nightpos.ribersoft.com

# Limpiar artefactos PWA viejos
rm -f sw.js registerSW.js workbox-*.js mockServiceWorker.js

# Limpiar frontend anterior (NO borrar backend/)
find . -maxdepth 1 ! -name backend ! -name . ! -name .. -exec rm -rf {} +

# Copiar dist
cp -r /home/vnplktsg/nightpos/frontend/dist/* .
```

---

## 4. Smoke tests

```bash
curl -sS https://nightpos.ribersoft.com/api/v1/health
curl -sS https://nightpos.ribersoft.com/api/v1/auth/login-context/tenants
curl -sS -o /dev/null -w "%{http_code}" https://nightpos.ribersoft.com/sw.js   # debe ser 404
```

Navegador: login PIN, login password, refresh en `/nightpos/cashier`.

---

## 5. Agente en PC del local

`config.json`:

```json
"backend_url": "https://nightpos.ribersoft.com/api/v1"
```

Ver `agent/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md`.

---

## Documentación completa

- `backend/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md`
- `frontend/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md`
- `agent/HOSTING_DEPLOY_ARCHITECTURE_AUDIT.md`
