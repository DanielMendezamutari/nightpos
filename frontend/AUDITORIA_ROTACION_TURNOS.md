# Auditoría de Rotación de Turnos

**Estado:** Auditoría completada, sin implementar cambios
**Alcance:** Comportamiento de la interfaz NightPOS al mostrar turno activo, caja abierta y liquidaciones del turno actual

## Resumen ejecutivo

La interfaz frontend no crea ni cierra turnos. Su función es consumir el estado que el backend le entrega: turno actual, caja actual, liquidaciones del turno y resumen de operación. Por eso, desde frontend solo se ve el resultado del modelo de rotación, no la lógica que lo produce.

En la práctica, la interfaz refleja que una caja abierta puede seguir operando aunque el turno oficial cambie. Eso se observa en los componentes de liquidaciones y reportes, donde el backend decide si el alcance es `my_cash_session` o `shift`. El frontend solo renderiza el scope que recibe.

## Evidencia encontrada

### Pantallas y composables relevantes

- `frontend/src/composables/useCurrentShiftSettlements.js`
  - consume `GET /settlements/current-shift`
  - guarda `shift`, `summary`, `context`, `sourcesSummary`, `waiters`, `girls` y `cleaning`
- `frontend/src/pages/nightpos/settlements/index.vue`
  - muestra el scope actual con textos como “mi caja actual” o “el turno”
  - no calcula el turno por su cuenta
  - depende de `context.scope`
- `frontend/src/pages/nightpos/settlements/history.vue`
  - permite consultar turnos pasados con filtro por `official_shift_id`
  - sirve como historia, no como estado operativo actual
- `frontend/src/api/shifts.js`
  - consulta el turno actual, el resumen del turno y el cierre de turno
- `frontend/src/api/settlements.js`
  - consulta el estado de liquidaciones del turno actual

### Evidencia funcional

- El frontend no registra tareas programadas.
- El frontend no abre turnos.
- El frontend no cierra turnos.
- El frontend no rota turnos.
- El frontend no escribe en `official_shifts` ni en `cash_sessions`.

## Análisis técnico

### 1. Quién crea el turno desde la perspectiva UI

Nadie en frontend.

La UI solo provoca solicitudes HTTP. Cuando una de esas solicitudes llega a un endpoint que usa `EnsureOperationalShiftUseCase`, el backend puede abrir o rotar el turno. El frontend no ejecuta esa lógica.

### 2. Cuándo aparece el cambio de turno en la interfaz

La interfaz se entera del cambio cuando refresca datos y el backend ya devolvió un turno distinto.

Esto pasa especialmente en:

- caja
- liquidaciones
- cierre de turno
- reportes operativos

### 3. Si existe una tarea programada visible en frontend

No.

No hay cron, scheduler ni worker de frontend que cambie el turno.

### 4. Si la caja abierta puede seguir mostrando información mientras cambia el turno

Sí.

El frontend lo muestra de manera indirecta porque la caja sigue activa y el backend devuelve un turno nuevo para la operación, pero la sesión de caja no necesariamente se cierra al mismo tiempo.

### 5. Si la UI puede explicar por sí sola por qué cambió el turno

Parcialmente.

La UI muestra el `scope`, el turno actual y los resúmenes, pero no tiene visibilidad completa sobre la decisión interna del backend, ni sobre si la rotación fue por ventana horaria, por concurrencia o por la primera solicitud que tocó el turno AUTO vencido.

## Causa raíz

La causa raíz visible en frontend es un desacoplamiento de responsabilidades:

- la interfaz consume el resultado,
- el backend decide el turno operativo,
- la caja puede seguir abierta,
- y el turno puede cambiar sin que exista una “acción de rotación” explícita en la UI.

Eso hace que para el usuario final parezca que “la misma caja cambió de turno sola”, aunque técnicamente el cambio vino de la lógica backend al resolver el turno vigente.

## Riesgos

- Confusión operativa para cajeras y administradores.
- Dificultad para entender por qué una liquidación ya no aparece en el scope esperado.
- Sensación de inconsistencia entre “caja abierta” y “turno cambiado”.
- Posible duplicidad visual si el backend devuelve varios turnos AUTO abiertos y la UI solo muestra el último que recibió.

## Conclusión

El frontend está actuando de forma correcta respecto a su contrato: renderiza lo que el backend decide.

La inconsistencia no nace en la interfaz, sino en el modelo operativo que permite que la caja siga abierta mientras cambia el `official_shift_id`.

Por lo tanto, la interfaz no es la causa del problema, pero sí es el lugar donde el problema se vuelve visible para el usuario.

## Recomendación

No implementar cambios todavía.

Si se quiere evitar la confusión visual, primero hay que definir la regla de negocio entre caja y turno en backend. Solo después tendría sentido ajustar la UI para explicar mejor el scope activo.

## Próximos pasos

1. Revisar si la vista de liquidaciones debería mostrar explícitamente la diferencia entre `cash_session_id` y `official_shift_id`.
2. Verificar si el usuario necesita ver una advertencia cuando la caja sigue abierta pero el turno ya cambió.
3. Confirmar si el frontend debe solo informar el scope o si debe enfatizar el turno operativo nuevo cuando el backend lo rota.
