# Corrección modelo habitación vs servicio de pieza

**Fecha:** 2026-06-08  
**Actualización:** 2026-06-08 — cálculo por porcentaje chica/casa

## Problema

El sistema trataba la habitación como si definiera precio y duración obligatorios. El cobro y la liquidación deben definirse al **registrar el servicio de pieza**, no al crear el recurso físico.

Además, el monto de la chica no debe ingresarse manualmente como monto fijo principal: se calcula desde un **porcentaje** sobre el total cobrado.

## Regla de negocio

| Capa | Qué guarda |
|------|------------|
| **Habitación (`rooms`)** | Recurso físico: código, nombre, tipo, estado, notas. Precio/duración sugeridos **opcionales**. |
| **Servicio de pieza (`room_services`)** | Operación económica: habitación, chica, duración, total cobrado, **porcentaje chica**, montos calculados (snapshot), hora inicio, notas. |

### Cálculo al registrar

```
girl_amount = total_amount × girl_percent / 100
house_amount = total_amount − girl_amount
```

Ejemplo: total 200, porcentaje 50 → chica 100, casa 100.

**Porcentaje por defecto:** `config('nightpos.room_service.default_girl_percent')` = 50 (env `NIGHTPOS_DEFAULT_ROOM_GIRL_PERCENT`).

## Cambios backend

### Migraciones

- `2026_06_08_100030_room_service_pricing_model_fix` — `girl_amount`, `house_amount` (snapshots).
- `2026_06_08_100050_room_service_girl_percent` — `girl_percent` (snapshot del % usado al registrar).

### API habitaciones

- `POST/PUT /api/v1/rooms`: `default_duration_minutes` y `suggested_price` **nullable** (no obligatorios).

### API servicios de pieza

- `POST /api/v1/room-services` exige:
  - `total_amount` (o `unit_price` legacy)
  - `girl_percent` (0–100)
  - `duration_minutes`
- **No** acepta montos manuales de chica/casa como fuente de verdad (`girl_amount` / `house_amount` ignorados si se envían).
- `RoomServiceAmountCalculator` calcula y persiste snapshots `girl_percent`, `girl_amount`, `house_amount`.

### Liquidaciones

- `GIRL_ROOM` liquida **`girl_amount`** (calculado al registrar).
- `house_amount` queda como ingreso casa (sin ítem de liquidación de personal).
- Cambios futuros del % por defecto **no** alteran registros históricos.

## Tests

`tests/Feature/Api/V1/RoomServicePricingModelFixTest.php`:

- Total 200 + % 50 → chica 100, casa 100.
- % 60 → chica 120, casa 80.
- Rechaza % &lt; 0 y % &gt; 100.
- Ignora `girl_amount` / `house_amount` enviados manualmente.
- Liquidación usa `girl_amount` calculado.
- Habitación sin precio/duración obligatorios.

Helper global: `nightposRoomServicePayload()` usa `girl_percent` (default 50).

## Archivos tocados

- `CreateRoomServiceRequest`, `CreateRoomServiceInput`, `CreateRoomServiceUseCase`
- `RoomServiceAmountCalculator`, `GirlIncomeMapper`
- `EloquentRoomServiceRepository`, `RoomServiceModel`
- `config/nightpos.php`
