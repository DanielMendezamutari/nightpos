# Liquidaciones de limpieza — Frontend

## Configuración por usuario (admin)

En **Usuarios → Crear/Editar**, si el rol operativo es **Limpieza** (`CLEANING`), la pestaña Comisión muestra la sección **Pago limpieza**:

- Base por turno (Bs)
- Pago por pieza limpiada (Bs)

Estos campos están ocultos para cajera, garzón, chica y administrador.

**Archivos:** `composables/useUserAdminForm.js`, `components/nightpos/forms/UserAdminFormFields.vue`

## Modo limpieza móvil

En `/nightpos/cleaning`, tarjeta **Mi pago del turno**:

- Base
- Piezas limpias
- Pago por pieza
- Total piezas
- Total acumulado

Datos desde `GET /api/v1/cleaning/shift-earnings` (`api/cleaning.js` → `fetchCleaningShiftEarnings`).

## Liquidaciones cajera / admin

Nueva pestaña **Limpieza** en Finanzas → Liquidaciones (`/nightpos/settlements/cleaning`):

| Columna | Fuente API |
|---------|------------|
| Personal limpieza | `staff_name` |
| Base | `cleaning_base_total` |
| Piezas limpias | `cleaning_rooms_count` |
| Pago por pieza | `cleaning_room_rate` |
| Total piezas | `cleaning_rooms_total` |
| Total a pagar | `total_amount` |
| Estado | `status` |

**Archivos:** `pages/nightpos/settlements/cleaning.vue`, `composables/useCurrentShiftSettlements.js`, `composables/useSettlementSectionTabs.js`

## Pago y caja

Al marcar pagada una liquidación de limpieza desde el detalle, el backend exige caja abierta. Si no hay sesión, el mensaje mostrado es: **«Debe abrir caja para pagar esta liquidación.»**

## Reglas resumidas

- El monto lo define el administrador por usuario de limpieza.
- Cada limpieza tras un servicio distinto se paga.
- No se duplica la misma limpieza del mismo `room_service_id`.
- La base se paga una vez por turno si hubo al menos una pieza limpia.
- El pago de liquidación genera egreso de caja.
