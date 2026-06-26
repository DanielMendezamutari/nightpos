# HOSTING — Auth / API Stability Audit (Backend)

**Fecha:** 2026-06-25  
**Prioridad:** Urgente — hosting deja de responder / sesión cae  
**Estado:** Auditado + fixes aplicados

---

## Síntoma reportado

Tras usar el sistema un rato en hosting aparece mensaje tipo **“servidor no responde”** o la API deja de responder. Sospecha: token vencido, refresh fallido, blacklist, cache config, PWA.

---

## Arquitectura auth actual

| Componente | Valor / comportamiento |
|------------|------------------------|
| Guard | `auth:api` (JWT) |
| TTL access token | `JWT_TTL` default **720 min (12 h)** |
| Refresh window | `JWT_REFRESH_TTL` default **20160 min (14 días)** |
| Rolling refresh | `JWT_REFRESH_IAT=true` |
| Blacklist | `JWT_BLACKLIST_ENABLED` default **true** |
| Refresh endpoint | `POST /api/v1/auth/refresh` (requiere Bearer válido dentro de ventana) |
| Logout | invalida token en blacklist |

---

## Hallazgos (causa raíz probable)

### 1. JWT exceptions sin JSON 401 (ALTO)

Antes del fix, `TokenExpiredException`, `TokenBlacklistedException`, etc. **no tenían renderer API**. Laravel podía devolver HTML/500 genérico → axios interpretaba **Network Error** o mensaje genérico **“Error de comunicación con el servidor”**.

**Fix:** `ApiJwtExceptionRenderer` registrado en `bootstrap/app.php` → siempre JSON 401 con `data.code` (`token_expired`, `token_blacklisted`, …).

### 2. Blacklist grace period = 0 (ALTO)

Con `JWT_BLACKLIST_GRACE_PERIOD=0`, cuando el frontend refresca el token, **requests paralelas con el token viejo** reciben `TokenBlacklistedException` → cascada de 401 → logout forzado.

**Fix:** default **30 segundos** en `config/jwt.php` y `.env.example`.

### 3. Config cache en hosting (MEDIO)

Si en hosting se ejecutó `php artisan config:cache` con `.env` viejo (ej. `JWT_TTL=60`), el token expira en 1 h aunque el `.env` actual diga 720.

**Verificar en hosting:**

```bash
php artisan tinker --execute="echo config('jwt.ttl');"
php artisan tinker --execute="echo config('jwt.blacklist_grace_period');"
```

Si no coincide con `.env`:

```bash
php artisan optimize:clear
php artisan config:cache   # solo si usan config cache en prod
```

### 4. Refresh sin logging (BAJO — diagnóstico)

No había trazas claras en `storage/logs/laravel.log` para refresh fallido.

**Fix:** `RefreshTokenUseCase` loguea `auth.refresh.success` / `auth.refresh.failed` (sin tokens).

### 5. No es CORS en setup típico NightPOS

Frontend y API en **mismo origen** (`/backend/public/api/v1` o proxy). CORS solo aplica si API está en subdominio distinto — documentar en hosting si usan cross-origin.

### 6. No es Sanctum

NightPOS usa JWT (`php-open-source-saver/jwt-auth`), no Sanctum session para API operativa.

---

## Matriz de diagnóstico (DevTools → Network)

| Señal | Interpretación |
|-------|----------------|
| Status **401** + `data.code=token_expired` | Access token vencido; frontend debe refresh |
| **401** en `/auth/refresh` | Fuera de ventana refresh → re-login normal |
| **401** + `token_blacklisted` | Logout previo o race refresh → grace period ayuda |
| **403** | Permiso/contexto tenant — **no** es token |
| **419** | CSRF web (poco común en API JSON) |
| **500** | PHP/MySQL/backend caído — revisar `laravel.log` |
| **(failed) timeout** | Hosting lento / PHP max execution — no es JWT |
| **Network Error** sin status | DNS, SSL, Apache down, o respuesta no-JSON |

---

## Checklist hosting (.env)

```env
APP_URL=https://tu-dominio.com
JWT_SECRET=<mismo valor estable, no rotar sin plan>
JWT_TTL=720
JWT_REFRESH_TTL=20160
JWT_REFRESH_IAT=true
JWT_BLACKLIST_ENABLED=true
JWT_BLACKLIST_GRACE_PERIOD=30
```

Tras cambiar `.env`:

```bash
php artisan optimize:clear
php artisan migrate --force   # si aplica
```

---

## Logs a revisar

`storage/logs/laravel.log`:

- `auth.refresh.success`
- `auth.refresh.failed`
- `auth.token.blacklisted`
- `auth.jwt.error`

---

## Tests

`tests/Feature/Api/V1/AuthApiTest.php` — incluye refresh, JSON 401 en `/auth/me`, grace period ≥ 30.

---

## Archivos modificados (fix)

- `app/Infrastructure/Laravel/Http/ApiJwtExceptionRenderer.php` (nuevo)
- `bootstrap/app.php`
- `config/jwt.php`
- `app/Application/Auth/UseCases/RefreshTokenUseCase.php`
- `.env.example`

Ver `backend/HOSTING_AUTH_API_STABILITY_FIX_REPORT.md`.
