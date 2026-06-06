# Corrección frontend — habitación vs servicio de pieza

**Fecha:** 2026-06-08  
**Actualización:** 2026-06-08 — porcentaje chica / cálculo automático

## Problema

Los formularios de habitación pedían precio y duración como obligatorios, y el registro de pieza pedía montos chica/casa manuales. Eso confundía el recurso físico con la operación económica y permitía errores de reparto.

## Regla de UI

| Pantalla | Campos |
|----------|--------|
| **Crear/editar habitación** | Código, nombre, tipo, notas. Switch opcional “Valores sugeridos opcionales”. |
| **Registrar pieza** | Chica, habitación, duración, hora inicio, **monto total**, **porcentaje chica**. Cálculo en vivo chica/casa (solo lectura). |

## Cambios

### Habitaciones

- `rooms/create.vue`, `rooms/[id]/edit.vue`, `QuickRoomCreateDialog.vue` — solo datos físicos.
- `rooms/list.vue`, `rooms/available.vue` — sin columnas de precio obligatorio.

### Servicios de pieza

- `services/room-services/create.vue`:
  - Campos `total_amount`, `girl_percent` (default 50%).
  - Tarjeta “Cálculo automático”: para chica / para casa (en vivo).
  - **Sin** campos editables `girl_amount` / `house_amount`.
  - Solo envía `girl_percent` al API; el backend calcula montos.
- `services/room-services/index.vue` — columnas % Chica, Chica, Casa.

## Flujo operativo esperado

1. Admin crea **Pieza VIP 1** (solo código, nombre, tipo).
2. Cajero registra **servicio**: María, 30 min, total 200, porcentaje chica 50%.
3. UI muestra: chica 100 BOB, casa 100 BOB.
4. Al liquidar, la chica recibe **100 BOB** (`girl_amount` snapshot), no el total.

## Configuración

Porcentaje inicial del formulario: **50%** (alineado con `NIGHTPOS_DEFAULT_ROOM_GIRL_PERCENT` en backend). Ajuste manual de montos queda para fase futura con permiso especial.
