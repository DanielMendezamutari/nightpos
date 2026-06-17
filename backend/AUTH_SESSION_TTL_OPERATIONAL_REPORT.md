# Sesión operativa — TTL y refresh JWT

**Fecha:** 2026-06-17

## Problema

La sesión JWT expiraba en **60 minutos** (default `JWT_TTL=60`), molesto para cajeras en turnos de 12+ horas.

## Cambios backend

| Archivo | Cambio |
|---------|--------|
| `config/jwt.php` | Default `JWT_TTL` → **720** (12 h) |
| `config/jwt.php` | `JWT_REFRESH_IAT` default → **true** (ventana rolling) |
| `.env.example` | Documenta `JWT_TTL=720`, `JWT_REFRESH_TTL=20160`, `JWT_REFRESH_IAT=true` |
| `RefreshTokenUseCase` | Nuevo caso de uso |
| `JwtAuthRepository::refreshCurrentToken()` | `JWTAuth::parseToken()->refresh()` |
| `POST /api/v1/auth/refresh` | Renueva token dentro de ventana refresh (14 días) |

## Modelo de sesión

| Token | Duración default | Uso |
|-------|------------------|-----|
| Access (`JWT_TTL`) | 720 min (12 h) | Requests API |
| Refresh window (`JWT_REFRESH_TTL`) | 20160 min (14 días) | Renovar access con `/auth/refresh` |
| `refresh_iat=true` | Rolling | Cada refresh extiende ventana desde última actividad |

## Seguridad

- No es sesión infinita: tras 14 días sin refresh válido → re-login.
- Logout invalida token (blacklist JWT).
- Login PIN y password sin cambios.

## Tests

`AuthApiTest`:

- `refreshes an authenticated token within refresh window`
- `uses operational jwt ttl default of twelve hours`

## Configuración producción

En `.env` del servidor:

```
JWT_TTL=720
JWT_REFRESH_TTL=20160
JWT_REFRESH_IAT=true
```

Para turnos de 16 h: `JWT_TTL=960`.
