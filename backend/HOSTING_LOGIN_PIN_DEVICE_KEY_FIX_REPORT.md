# Hosting — Login PIN "Key cannot be empty" — Fix Report (Backend)

**Fecha:** 2026-06-25  
**Síntoma:** Login PIN con empresa/sucursal correctas muestra `Key cannot be empty`.  
**Causa real:** **`JWT_SECRET` vacío** en hosting (no `device_key` del frontend).

---

## 1. Diagnóstico

| Hipótesis | Resultado |
|-----------|-----------|
| Frontend no envía `device_key` | **Irrelevante** — `POST /auth/login-pin` **no valida** `device_key` |
| `device_key` del agente de impresión | Solo para `Authorization: Bearer npd_live_...` en print agent |
| Error en payload PIN/tenant/branch | Devolvería 422 de validación o 401 credenciales |
| **`JWT_SECRET` vacío** | **Causa confirmada** |

El mensaje `Key cannot be empty` proviene de:

`Lcobucci\JWT\Signer\InvalidKeyProvided::cannotBeEmpty()`

Ocurre **después** de validar PIN correctamente, al firmar el token JWT en `JwtAuthRepository::issueTokenForUserId()`.

---

## 2. Campos que espera `POST /api/v1/auth/login-pin`

Archivo: `LoginPinRequest.php`

| Campo | Reglas |
|-------|--------|
| `pin` | required, 4–6 dígitos |
| `tenant_slug` | nullable string |
| `tenant_id` | nullable integer |
| `branch_code` | nullable string |
| `branch_id` | nullable integer |

**No existe** `device_key`, `terminal_key` ni `client_key` en login PIN.

---

## 3. Fix en código (repo)

| Cambio | Archivo |
|--------|---------|
| Excepción `JwtNotConfiguredException` | `app/Domain/Auth/Exceptions/JwtNotConfiguredException.php` |
| Validar `JWT_SECRET` antes de emitir/refresh token | `JwtAuthRepository.php` |
| HTTP 503 + `data.code: jwt_not_configured` | `bootstrap/app.php` |
| Captura `InvalidKeyProvided` → mensaje claro | `ApiJwtExceptionRenderer.php` |
| Health check incluye `jwt: up/down` | `HealthController.php` |
| Tests pin/password sin JWT secret | `AuthApiTest.php` |

Mensaje API:

> La autenticación del servidor no está configurada. Contacte al administrador del sistema.

---

## 4. Fix en hosting (obligatorio)

En el servidor, dentro de `backend/`:

```bash
cd /home/vnplktsg/nightpos.ribersoft.com/backend

# 1. Generar secret si falta
php artisan jwt:secret --force

# 2. Verificar APP_KEY también (PIN fingerprint usa app.key)
php artisan key:generate --force   # solo si APP_KEY vacío

# 3. Limpiar cache de config (muy común tras editar .env)
php artisan optimize:clear

# 4. Verificar
php artisan about
curl -sS https://nightpos.ribersoft.com/backend/public/api/v1/health
# Debe mostrar: "jwt":"up"
```

En `.env` de producción deben existir:

```env
APP_KEY=base64:...
JWT_SECRET=...   # generado por jwt:secret
```

**No** ejecutar `php artisan config:cache` hasta que `.env` tenga ambos valores.

---

## 5. Verificación Network (DevTools)

Request esperado:

```json
{
  "pin": "1234",
  "tenant_slug": "casa-demo",
  "branch_code": "CENTRO"
}
```

No se requiere `device_key`.

Si PIN es correcto pero JWT mal configurado → **503** con `jwt_not_configured` (antes: 500 con texto crudo).

---

## 6. Tests

```bash
php artisan test --filter=AuthApiTest
php artisan test --filter=HealthEndpointTest
```

- login-pin con JWT secret vacío → 503 + código claro  
- login-password igual  
- `/api/v1/health` → campo `jwt: up`

---

**Estado repo:** fix aplicado. **Hosting:** ejecutar `jwt:secret` + `optimize:clear`.
