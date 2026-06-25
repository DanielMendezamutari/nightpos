# Backend boot fix — Impresión movimientos / cierres

Fecha: 2026-06-22

## 1. Error exacto

```
ReflectionException: Class "App\Http\Controllers\Api\V1\GetCashMovementUseCase" does not exist
```

Aparecía al resolver `CashController` en cualquier request a rutas `/api/v1/cash/*` (p. ej. `GET /cash/movements/{id}`).

Registro en `storage/logs/laravel.log` (stack trace línea ~22507).

## 2. Causa raíz

En `CashController.php` el constructor inyectaba `GetCashMovementUseCase` **sin** la declaración `use`:

```php
private readonly GetCashMovementUseCase $getMovement,
```

Al no existir import, PHP resolvió la clase en el namespace del controller:

`App\Http\Controllers\Api\V1\GetCashMovementUseCase`

La clase real está en:

`App\Application\Cash\UseCases\GetCashMovementUseCase`

Esto ocurrió al añadir `GetCashSessionUseCase` y reemplazar accidentalmente el import de `GetCashMovementUseCase` por el de sesión.

## 3. Archivo responsable

`backend/app/Http/Controllers/Api/V1/CashController.php`

## 4. Fix aplicado

Añadido import faltante:

```php
use App\Application\Cash\UseCases\GetCashMovementUseCase;
```

Sin cambios de reglas de negocio ni eliminación de impresión.

## 5. Verificación

| Comando | Resultado |
|---------|-----------|
| `composer dump-autoload -o` | OK |
| `php artisan route:list --path=cash` | OK (19 rutas cash) |
| `php artisan test tests/Feature/Api/V1/CashMovementAndClosurePrintTest.php` | **10 passed** |

## 6. Descartado en diagnóstico

- Enums `PrintJobType` / `PrintJobSourceType` — OK
- `PrintTicketContentBuilder` — OK (tests de contenido pasan)
- PSR-4 / archivos use case — OK (cada clase en su archivo)
- Rutas duplicadas — OK (`/cash/session/current` no conflictúa con `/cash/sessions/{id}`)
- Service provider bindings — OK
- Migraciones — no relacionadas con el boot failure

**Nota:** `php artisan cache:clear` puede fallar si MySQL no está activo (cache driver DB). Eso es independiente de este bug.

## 7. Prevención

Al añadir dependencias en controllers, verificar siempre que cada type-hint tenga su `use` correspondiente. El síntoma típico es namespace `App\Http\Controllers\Api\V1\{ClassName}`.
