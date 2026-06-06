# SUPERADMIN_LOGIN_FIX_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Bug:** Login `superadmin` / `SuperAdmin123!` sin empresa  
**Fecha:** 2026-06-02

---

## 1. Causa exacta

En el entorno XAMPP del reporte, la causa principal fue **base de datos sin datos de demo** tras `php artisan migrate` sin ejecutar el seeder: el usuario `superadmin` no existía, por lo que `findByUsernameForLogin` devolvía `null` y el API respondía **401** con mensaje «Credenciales inválidas.»

Factores secundarios corregidos o endurecidos:

| Factor | Efecto |
| ------ | ------ |
| Usuario tenant sin `tenant_slug` | Debe fallar (401), no confundir con superadmin |
| `tenant_slug` vacío `""` en JSON | Se normaliza a `null` (no busca tenant inválido) |
| Username con mayúsculas (`SuperAdmin`) | Ahora se normaliza a minúsculas en backend |
| Superadmin con `tenant_slug` erróneo en body | Sigue autenticando (usuario `tenant_id = null`) |
| Password en seeder | `updateOrCreate` por username para reparar hash al re-sembrar |

La lógica `findByUsernameForLogin` ya priorizaba usuarios plataforma (`tenant_id = null`). El fallo visible era ausencia de fila en `users` o password desactualizado.

---

## 2. Archivos corregidos

| Archivo | Cambio |
| ------- | ------ |
| `app/Application/Auth/UseCases/LoginWithPasswordUseCase.php` | Username trim+lower; slug vacío ignorado; fallback plataforma; tenant obligatorio para usuarios tenant |
| `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentUserRepository.php` | `findByUsernameForLogin()` (sin cambio en este fix, ya existía) |
| `database/seeders/NightPosSeeder.php` | `superadmin` con `updateOrCreate` + password `SuperAdmin123!` |
| `tests/Feature/Api/V1/SuperadminLoginFixTest.php` | +3 casos (mayúsculas, slug vacío admin, PIN cajero) |
| `scripts/verify-superadmin.php` | Script verificación manual |

---

## 3. Reglas de login password

1. **Superadmin / `tenant_id = null`:** no exige `tenant_slug`; genera JWT; rol `super_admin`.
2. **Usuario tenant:** exige `tenant_slug` válido y activo; `tenant_id` del usuario debe coincidir.
3. **PIN:** sin cambios (cajero/garzón siguen con empresa + sucursal).

---

## 4. Comandos ejecutados

```bash
cd backend
php artisan db:seed
php scripts/verify-superadmin.php
php artisan test --filter=SuperadminLoginFix
php artisan test
```

Salida esperada de verificación:

```
username=superadmin
tenant_id=NULL
status=active
role=super_admin
password_hash_check=OK
```

---

## 5. Tests

`tests/Feature/Api/V1/SuperadminLoginFixTest.php` (10 casos):

1. Superadmin sin `tenant_slug` → 200 + JWT  
2. Superadmin con `tenant_slug` erróneo → OK  
3. `GET /admin/tenants` sin branch  
4. `GET /tenant/current` modo global  
5. Admin sin slug → 401  
6. Admin con slug → OK  
7. Username mayúsculas normalizado  
8. Admin con `tenant_slug` `""` → 401  
9. Cajero no es superadmin en password  
10. PIN cajero sigue OK  

**Suite completa:** 87 tests OK (tras este fix).

---

## 6. Verificación manual (Tinker / API)

```bash
php artisan tinker
```

```php
$user = \App\Infrastructure\Persistence\Eloquent\Models\UserModel::where('username', 'superadmin')->first();
\Illuminate\Support\Facades\Hash::check('SuperAdmin123!', $user->password); // true
```

```http
POST /api/v1/auth/login-password
{ "username": "superadmin", "password": "SuperAdmin123!" }
```

---

## 7. Cómo probar en XAMPP

1. `php artisan migrate` (si aplica)  
2. **`php artisan db:seed`** — obligatorio para crear `superadmin`  
3. `php scripts/verify-superadmin.php`  
4. Frontend: pestaña Usuario/contraseña, `superadmin` / `SuperAdmin123!`, sin empresa  

---

## 8. Pendiente

- Selector de empresa en navbar para superadmin (ya parcialmente en `PlatformContextSelector`).
- Comando Artisan dedicado `nightpos:verify-superadmin` (opcional; hoy está el script PHP).
