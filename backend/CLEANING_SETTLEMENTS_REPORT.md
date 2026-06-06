# Liquidaciones de limpieza — Backend

## Regla de negocio

El pago de limpieza se configura **por usuario** con rol `CLEANING` en `staff_profiles`:

| Campo | Descripción |
|-------|-------------|
| `cleaning_base_amount` | Base pagada una vez por turno si realizó al menos una limpieza |
| `cleaning_room_amount` | Pago por cada pieza marcada como limpia |

Si el administrador no define valores, se usan defaults de `config/nightpos.php` (`default_base_amount` = 30, `default_room_amount` = 10).

## Flujo operativo

1. Finaliza un `room_service` → habitación pasa a `CLEANING`.
2. Personal de limpieza marca la habitación limpia (`POST /api/v1/cleaning/rooms/{id}/mark-clean`).
3. Se crea un registro en `cleaning_tasks` con `amount` del perfil del usuario que marcó limpia.
4. Al generar liquidaciones del turno (`POST /api/v1/settlements/generate-current-shift`):
   - `CLEANING_ROOM` por cada `cleaning_task` no liquidado.
   - `CLEANING_BASE` una sola vez por usuario si tuvo tareas en el turno.
5. Al marcar pagada una liquidación `CLEANING` (`POST /api/v1/settlements/{id}/mark-paid`):
   - Requiere caja abierta del cajero que paga.
   - Registra egreso (`EXPENSE`) con motivo «Limpieza».

## Anti-duplicados

- Tabla `cleaning_tasks`: índice único en `room_service_id`.
- Liquidación: `source_type` + `source_id` en `staff_settlement_items`.
- Base: no se vuelve a crear si el settlement pendiente ya tiene `CLEANING_BASE`.

## Misma habitación, distintos servicios

Cada `room_service_id` nuevo genera su propia `cleaning_task` y su propio pago, aunque sea la misma habitación.

## API relevante

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/v1/cleaning/shift-earnings` | Acumulado del turno para el usuario de limpieza autenticado |
| POST | `/api/v1/cleaning/rooms/{id}/mark-clean` | Marca limpia y crea `cleaning_task` |
| GET | `/api/v1/settlements/current-shift` | Incluye arreglo `cleaning` |
| POST | `/api/v1/settlements/{id}/mark-paid` | Paga liquidación; en `CLEANING` crea egreso de caja |

## Usuarios admin

`staff_role=CLEANING` en crear/editar usuario. Campos `cleaning_base_amount` y `cleaning_room_amount` (≥ 0). No aplican a otros roles.

## Tests

`tests/Feature/Api/V1/CleaningSettlementsTest.php` — 11 casos del spec.
