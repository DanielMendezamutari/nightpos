# ROOM SERVICE TIME CALCULATION FIX — V1-91.3

## Problema

Al crear una pieza nueva, inmediatamente aparecía como **"Tiempo cumplido"** en el control de piezas.

## Causa Raíz

**Desincronización de timezone entre frontend y backend.**

- La app Laravel tenía `APP_TIMEZONE=UTC`.
- El frontend (`datetime-local` HTML input) envía datetime en hora local Bolivia **sin offset de timezone**, e.g. `"2026-06-06T03:00"`.
- `Carbon::parse("2026-06-06T03:00")` en Laravel (UTC) interpreta esto como **UTC**.
- Pero la hora real en UTC es `2026-06-06T07:00` (Bolivia es UTC-4).
- Resultado: el backend cree que la pieza **empezó hace 4 horas**, y `expected_ends_at` queda en el pasado → `is_due = true` inmediatamente.

### Ejemplo concreto

```
Hora actual Bolivia:  03:00 (UTC-4)
Hora actual UTC:      07:00

Usuario envía started_at: "2026-06-06T03:00"
Backend (UTC) interpreta: 2026-06-06 03:00 UTC  ← INCORRECTO
expected_ends_at:         2026-06-06 03:30 UTC
Carbon::now():            2026-06-06 07:00 UTC
is_due = 03:30 <= 07:00 = TRUE  ← pieza recién creada aparece vencida
```

## Solución

### 1. `config/app.php` — Timezone a America/La_Paz

```php
'timezone' => env('APP_TIMEZONE', 'America/La_Paz'),
```

La app ahora interpreta y almacena todos los datetimes en hora Bolivia (UTC-4).

### 2. `.env.example` — Nueva variable

```
APP_TIMEZONE=America/La_Paz
```

### 3. `CreateRoomServiceUseCase.php` — Parse con timezone explícito

```php
$tz = config('app.timezone', 'America/La_Paz');
$started = $input->startedAt !== null
    ? Carbon::parse($input->startedAt, $tz)
    : Carbon::now($tz);
```

Garantiza que incluso si la app.php cambia, el use case siempre respeta la timezone configurada.

### 4. `GirlIncomeMapper.php` — Carbon::now() con timezone

```php
$now = Carbon::now(config('app.timezone', 'America/La_Paz'));
```

`is_due` y `remaining_minutes` se calculan en la misma timezone que `expected_ends_at`.

### 5. `EloquentRoomServiceRepository.php` — Queries con timezone

`listActive`, `listDue`, `findDueUnalerted` usan `Carbon::now(config('app.timezone'))` para comparaciones correctas.

## Reglas de tiempo (definitivas)

```
started_at      = hora ingresada (La Paz) o Carbon::now(La Paz)
duration_minutes = número entero, mínimo 1
expected_ends_at = started_at + duration_minutes
is_due           = expected_ends_at <= Carbon::now(La_Paz)
remaining_minutes = is_due ? 0 : diff(now, expected_ends_at)
alert_sent_at    = null al crear
status inicial   = ACTIVE
```

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `config/app.php` | `timezone` → `env('APP_TIMEZONE', 'America/La_Paz')` |
| `.env.example` | `APP_TIMEZONE=America/La_Paz` |
| `CreateRoomServiceUseCase.php` | Parse `started_at` con `$tz` explícito |
| `GirlIncomeMapper.php` | `Carbon::now($tz)` para cálculo de `is_due` |
| `EloquentRoomServiceRepository.php` | `Carbon::now($tz)` en `listActive`, `listDue`, `findDueUnalerted` |

## Tests nuevos

`tests/Feature/Api/V1/RoomServiceTimeCalculationTest.php` — 7 casos:

1. Pieza creada ahora (30 min) → `ACTIVE`, `is_due=false`, `remaining > 0`
2. Pieza creada ahora → no aparece en `/due`
3. Pieza iniciada hace 40 min (duración 30) → `is_due=true`
4. Pieza con `started_at` futuro → `is_due=false`, `remaining > 30`
5. `duration_minutes = 0` → HTTP 422
6. `alert_sent_at` nulo al crear
7. `expected_ends_at = started_at + 45 min` (verificación directa)

**Resultado: 340 tests, todos PASS.**

## Importante para SSE

Con este fix, el módulo SSE podrá emitir eventos `room_service.due` correctamente:
- Solo cuando `expected_ends_at <= now(La_Paz)` sea verdadero.
- Nunca al momento de creación de una pieza normal.
