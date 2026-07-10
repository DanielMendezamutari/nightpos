# Auditoría de Rotación de Turnos

**Estado:** Auditoría completada, sin implementar cambios
**Alcance:** Cómo NightPOS rota turnos oficiales, quién los crea, quién los cierra y cómo una misma caja puede seguir operando mientras cambia el `official_shift_id`
**Fuente de verdad:** Código fuente backend y base MySQL real `nigtpos`

## Resumen ejecutivo

NightPOS no rota turnos mediante un scheduler ni mediante un cron propio. La rotación es **reactiva**: ocurre cuando una solicitud operativa llama a `EnsureOperationalShiftUseCase`, que compara la ventana horaria actual con el turno abierto y, si ese turno es un turno AUTO vencido, lo cierra y abre otro.

La evidencia real muestra que la caja `cash_session_id = 11` fue abierta con `official_shift_id = 13`, pero las ventas posteriores de HUGO quedaron en `official_shift_id = 19`. Eso no ocurrió por un cambio manual de caja: ocurrió porque el sistema abrió un nuevo turno operativo para la ventana del día y la caja siguió abierta. En otras palabras, en NightPOS la **Caja (Cash Session)** y el **Turno Oficial (Official Shift)** están desacoplados por diseño.

Ese desacoplamiento es funcionalmente intencional, pero hoy genera un punto de fricción: una caja abierta puede seguir operando mientras el turno oficial cambia, y eso afecta la visibilidad de liquidaciones y reportes si la UI o el backend filtran por un scope distinto al de la caja.

## Evidencia encontrada

### Código backend

- `EnsureOperationalShiftUseCase` garantiza un turno operacional abierto y rota turnos AUTO vencidos.
- `OperationalShiftScheduleResolver` define la ventana horaria:
  - 00:00–08:59 = turno Noche del día anterior
  - 09:00–20:59 = turno Día del día actual
  - 21:00–23:59 = turno Noche del día actual
- `OpenCashSessionUseCase` abre caja usando el turno que devuelve `EnsureOperationalShiftUseCase`.
- `CreateOrderUseCase`, `ChargeOrderUseCase`, `CreateDirectSaleUseCase`, `CreateBraceletUseCase`, `CreateRoomServiceUseCase`, `CreateShowUseCase`, `GenerateCurrentShiftSettlementsUseCase` y varios flujos de consulta llaman a `EnsureOperationalShiftUseCase`.
- `routes/console.php` solo contiene el comando `inspire`; no hay comandos programados de rotación.
- `bootstrap/app.php` registra `routes/console.php`, pero no hay tareas de scheduler/cron para abrir o cerrar turnos automáticamente.

### Evidencia de base de datos

- `official_shifts.id = 13`:
  - `business_date = 2026-07-03`
  - `status = CLOSED`
  - `opened_by_user_id = 28`
  - `opened_at = 2026-07-03 09:49:22`
  - `closed_at = 2026-07-03 21:12:44`
  - `notes = Turno creado automáticamente para clasificación de reportes` + cierre automático por rotación
- `cash_sessions.id = 11`:
  - `official_shift_id = 13`
  - `status = OPEN`
  - `opened_by_user_id = 28`
  - `opened_at = 2026-07-03 09:49:22`
- `official_shifts.id = 19`:
  - `business_date = 2026-07-04`
  - `status = OPEN`
  - `opened_by_user_id = 23` (`lizvania`)
  - `opened_at = 2026-07-04 09:51:34`
  - `starts_at = 2026-07-04 09:00:00`
  - `ends_at = 2026-07-04 21:00:00`
  - `notes = Turno creado automáticamente para clasificación de reportes`
- Ventas de HUGO con `cash_session_id = 11` pero `official_shift_id = 19`:
  - ventas `V-0059` a `V-0063`
  - creadas entre `2026-07-04 13:36:04` y `2026-07-04 20:03:57`
- Liquidaciones de HUGO y otros usuarios en el mismo scope:
  - `staff_settlements.id = 21`
  - `official_shift_id = 19`
  - `cash_session_id = 11`
  - `status = PENDING`

### Evidencia de rotaciones múltiples

La base real muestra más de un turno día abierto en la misma sucursal (`id = 19`, `20`, `21`, `22`). Eso indica que la rotación automática no está protegida contra solicitudes concurrentes o repetidas alrededor del cambio de ventana. No es un scheduler: es un efecto de varias llamadas a la lógica de turno AUTO.

## Análisis técnico

### 1. Quién crea un `OfficialShift`

Hay dos caminos principales:

1. `OpenOfficialShiftUseCase` crea un turno manual desde el endpoint de apertura de turno.
2. `EnsureOperationalShiftUseCase` crea un turno AUTO cuando detecta que el turno abierto está vencido o ya no corresponde a la ventana horaria actual.

En la operación diaria normal, el camino importante es el segundo. El sistema no depende de un cron externo para abrir el turno siguiente: lo hace la primera solicitud operativa que toca el flujo y necesita un turno vigente.

### 2. Cuándo se crea

`EnsureOperationalShiftUseCase` evalúa la hora actual con `OperationalShiftScheduleResolver`:

- Antes de las 09:00, opera como turno Noche.
- Desde las 09:00 hasta antes de las 21:00, opera como turno Día.
- Desde las 21:00, vuelve a turno Noche.

Si el turno abierto es un turno AUTO y su ventana ya no corresponde, se cierra con `markAutoClosed()` y se abre uno nuevo con `open()`.

### 3. Quién lo cierra

Hay dos cierres distintos:

- Cierre manual: `CloseOfficialShiftUseCase`, ejecutado desde el endpoint de cierre de turno.
- Cierre automático por rotación: `EnsureOperationalShiftUseCase` llama a `markAutoClosed()` cuando el turno AUTO está vencido.

Por diseño, los turnos manuales no se cierran solos. Solo los turnos AUTO pueden rotarse automáticamente.

### 4. Si existe rotación automática

Sí. Existe y está codificada explícitamente en `EnsureOperationalShiftUseCase`.

La rotación automática ocurre cuando:

- el turno abierto es de tipo AUTO,
- la ventana horaria actual ya no coincide con la del turno,
- o el turno ya superó su `ends_at`.

### 5. Si existe una tarea programada (Scheduler/Cron)

No encontré un scheduler propio para rotación de turnos.

- `routes/console.php` no registra comandos de rotación.
- No hay un `Kernel` de consola del proyecto con tareas programadas de turno.
- No hay cron interno que cree o cierre turnos por tiempo.

La rotación es por solicitud, no por cron.

### 6. Si el cierre de caja crea un nuevo turno

No.

`CloseCashSessionUseCase` cierra la caja, pero no abre un turno nuevo. El cierre de caja solo valida el estado operativo y guarda el cierre de la sesión.

### 7. Si el cierre de turno crea un nuevo turno

No.

`CloseOfficialShiftUseCase` cierra el turno actual y crea el cierre del turno, pero no abre automáticamente el siguiente. El siguiente turno aparece cuando alguna operación vuelve a llamar a `EnsureOperationalShiftUseCase` y detecta que ya cambió la ventana horaria.

### 8. Si existe algún proceso que reabra turnos

Sí, pero no como “reapertura” manual.

El proceso real es la **apertura automática de un nuevo turno operativo** en `EnsureOperationalShiftUseCase`. Si el turno abierto está vencido y es AUTO, se marca como cerrado y se crea el siguiente.

### 9. Cómo una misma `cash_session` puede terminar asociada a ventas de otro `official_shift`

Porque la caja y el turno son dos resoluciones distintas:

- `OpenCashSessionUseCase` fija la caja abierta al turno vigente en ese instante.
- Las operaciones posteriores (`CreateOrderUseCase`, `ChargeOrderUseCase`, `CreateDirectSaleUseCase`, etc.) vuelven a resolver el turno operacional en cada solicitud.
- Si cambió la ventana horaria, el turno operativo cambia, pero la caja sigue siendo la misma mientras continúe abierta.

Por eso `cash_session_id = 11` pudo seguir abierta mientras las ventas nuevas quedaron en `official_shift_id = 19`.

### 10. Si ese comportamiento es intencional o es un defecto de diseño

La separación entre Caja y Turno Oficial es **intencional** en la arquitectura actual.

Lo que sí muestra un defecto de diseño es esto:

- la rotación no está blindada contra concurrencia,
- pueden coexistir varios turnos AUTO abiertos en la misma sucursal,
- y el scope de liquidaciones puede quedar desalineado entre caja activa y turno oficial.

## Causa raíz

La causa raíz no es una sola línea de código. Es una combinación de tres decisiones de arquitectura:

1. El turno operativo se resuelve dinámicamente en cada request.
2. La caja permanece abierta aunque el turno cambie.
3. No existe un bloqueo fuerte o una restricción única que impida que varias solicitudes creen más de un turno AUTO para la misma ventana.

En términos prácticos: NightPOS permite que una caja abierta continúe operando mientras el turno oficial rota, y esa rotación puede repetirse varias veces si llegan solicitudes cercanas entre sí o concurrentes. En la evidencia real, el primer turno día visible tras la ventana nocturna fue el `id = 19`, abierto a las `09:51:34` por `lizvania`.

## Riesgos

- Una caja abierta puede “migrar” de turno sin que el usuario lo perciba claramente.
- Los reportes y liquidaciones pueden filtrarse por un turno distinto al de la caja abierta.
- Pueden coexistir varios turnos AUTO abiertos para la misma sucursal y ventana horaria.
- La conciliación operativa entre caja, ventas y liquidaciones puede volverse confusa para cajeras y administradores.
- El historial de turnos puede contaminarse con rotaciones duplicadas.

## Conclusión

El comportamiento base de rotación es **correcto según la arquitectura actual de NightPOS**: el sistema está diseñado para abrir un nuevo turno automático cuando cambia la ventana horaria y para dejar que la caja siga operando.

Sin embargo, también existe una **inconsistencia funcional real** entre Caja (`Cash Session`) y Turno Oficial (`Official Shift`) en dos sentidos:

1. La misma caja puede seguir abierta mientras el turno cambia.
2. La rotación automática puede producir más de un turno abierto si varias solicitudes la disparan casi al mismo tiempo.

Por eso, el problema no es solo “cuándo se abrió el turno 19”; el problema es que el modelo actual permite que la caja y el turno se desalineen y que la rotación no sea estrictamente idempotente.

## Recomendación

No implementar cambios todavía.

Antes de corregir nada, NightPOS debería definir una regla explícita:

1. Si la caja debe seguir independiente del turno, entonces los reportes y liquidaciones deben resolver su scope por caja activa y no solo por `official_shift_id`.
2. Si la caja debe quedar ligada al turno, entonces hay que rediseñar la resolución operacional para evitar que una caja abierta siga operando tras la rotación horaria.
3. Si se mantiene la rotación automática, debe existir control de concurrencia para que solo un turno AUTO quede abierto por sucursal y ventana.

## Próximos pasos

1. Revisar el flujo de requests justo después de las 09:00 del `2026-07-04` para identificar la primera operación que disparó `EnsureOperationalShiftUseCase`.
2. Inspeccionar qué pantalla o endpoint estaba usando el usuario `23` cuando se creó el turno `19`.
3. Verificar si hay otra capa de concurrencia o polling que esté provocando turnos duplicados (`19`, `20`, `21`, `22`).
4. Definir una regla de negocio formal para la relación entre caja y turno antes de tocar código.
