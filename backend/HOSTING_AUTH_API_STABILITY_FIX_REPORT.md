# HOSTING — Auth / API Stability Fix Report (Backend)

**Fecha:** 2026-06-25

## Cambios

### ApiJwtExceptionRenderer (nuevo)

Convierte excepciones JWT y `AuthenticationException` en respuestas JSON 401 uniformes:

```json
{ "success": false, "message": "...", "data": { "code": "token_expired" }, "errors": {} }
```

Códigos: `token_expired`, `token_invalid`, `token_blacklisted`, `jwt_error`, `unauthenticated`.

### JWT blacklist grace period

Default **30s** (`JWT_BLACKLIST_GRACE_PERIOD`) para evitar carreras cuando el frontend refresca y otras requests aún usan el token anterior.

### Refresh logging

`RefreshTokenUseCase` escribe en log:

- `auth.refresh.success` (+ user_id)
- `auth.refresh.failed` (+ reason, sin token)

### .env.example

Documentados `JWT_BLACKLIST_ENABLED`, `JWT_BLACKLIST_GRACE_PERIOD`, `JWT_LEEWAY`.

## Deploy hosting

```bash
php artisan optimize:clear
# Verificar TTL efectivo:
php artisan tinker --execute="echo config('jwt.ttl');"
```

Asegurar `.env` con TTL 720 / refresh 20160 / grace 30.

## Tests

10 tests en `AuthApiTest.php` — OK.
