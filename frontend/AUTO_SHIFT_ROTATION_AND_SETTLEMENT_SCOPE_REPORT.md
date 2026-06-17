# Auto shift rotation & settlement scope — Frontend report

## Resumen

La UI de liquidaciones refleja el alcance devuelto por `GET /api/v1/settlements/current-shift` (`context.scope`) y avisa cuando el backend rota un turno AUTO obsoleto.

## Cambios

### `useCurrentShiftSettlements.js`

- Expone `context` y `sourcesSummary` del API.
- Snackbar informativo cuando `context.shift_rotated === true`: *"Se inició un nuevo turno automático"*.

### `settlements/index.vue`

- Etiqueta de alcance:
  - `my_cash_session` → "Mostrando: Mi caja actual"
  - `shift` → "Mostrando: Turno completo de sucursal"
- Mensaje cuando `context.empty_overview` o sin datos: *"No hay liquidaciones para este turno/caja"*.

### `settlements/history.vue`

- Pre-filtro por turno actual cuando aplica (desde trabajo previo en la rama).

## Comportamiento esperado (cajera)

1. Abre caja en turno nuevo → KPIs en 0 si no hubo actividad en **su** sesión.
2. Turno AUTO viejo ya no mezcla liquidaciones PENDING de días anteriores en la vista actual.
3. Admin/senior sigue viendo el turno completo de sucursal.

## Sin cambios de API adicionales

El frontend consume los campos ya expuestos en `data.context`; no requiere query `scope` salvo que se quiera selector manual en V2.
