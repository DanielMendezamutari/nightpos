# Hosting — Deploy Architecture Fix Report (Frontend)

**Fecha:** 2026-06-25

---

## Cambios aplicados

| Archivo | Cambio |
|---------|--------|
| `frontend/.env.production` | `VITE_API_BASE_URL=/api/v1` |
| `frontend/.env.example` | Documentación Opción A |
| `frontend/public/.htaccess` | API rewrite, storage, SW→404, SPA fallback |
| `frontend/public/.htaccess.example` | Igual referencia |

## Rebuild obligatorio

```bash
cd frontend && npm run build
```

Deploy limpio a raíz hosting (ver audit backend §9).

## PWA

Permanece `VITE_PWA_ENABLED=false`. Reactivar solo en V1.1 tras hosting estable.

## Validación post-deploy

1. `dist/index.html` — sin script PWA  
2. Network: requests a `/api/v1/...`  
3. No errores SW en consola  
4. Login PIN/password OK  
