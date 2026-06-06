# ROOM_SERVICE_CLEANING_DEDUCTION_REPORT.md — Backend
# Regla de Negocio: Descuento de Limpieza en Servicios de Pieza

**Fecha:** 2026-06-06  
**Versión:** V1-91.2 Ajuste regla de negocio  
**Estado:** COMPLETADO

---

## Problema

Antes de esta implementación, el pago de limpieza por piezas se calculaba siempre desde el perfil de la empleada limpieza (`staff_profiles.cleaning_room_amount`) o un valor de configuración por defecto. Esto era incorrecto para el flujo real del negocio donde el monto de limpieza es variable por pieza y debe descontarse del bruto de la chica.

---

## Nueva Regla de Negocio

Para servicios de pieza (`room_services`):

```
gross_girl_amount = total_amount × girl_percent / 100
cleaning_amount   = monto ingresado (o 0 si no se define)
girl_amount (neto) = gross_girl_amount - cleaning_amount
house_amount       = total_amount - gross_girl_amount
```

**Ejemplo:**
```
total_amount   = 200 Bs
girl_percent   = 50%
cleaning_amount = 10 Bs

gross_girl_amount = 100 Bs
girl_amount (neto) = 90 Bs
house_amount       = 100 Bs
cleaning_amount    = 10 Bs
```

**Validaciones:**
- `cleaning_amount` no puede superar `gross_girl_amount` → 422
- `cleaning_amount` nulo o 0 = sin descuento de limpieza

---

## Cambios en Base de Datos

### Migración: `2026_06_09_100070_add_cleaning_deduction_to_room_services`

Nuevas columnas en `room_services`:

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `gross_girl_amount` | decimal(12,2) | Bruto de la chica antes de descontar limpieza |
| `cleaning_amount` | decimal(12,2) | Monto de limpieza descontado del bruto |

**Backfill:** registros existentes → `gross_girl_amount = girl_amount`, `cleaning_amount = 0.00`

---

## Cambios en Código

### `RoomServiceAmountCalculator::split()`

Nueva firma:
```php
public static function split(float $totalAmount, float $girlPercent, float $cleaningAmount = 0.0): array
// Retorna: gross_girl_amount, girl_amount (neto), house_amount, cleaning_amount
```

### `CreateRoomServiceInput`
- Nuevo campo: `cleaningAmount: ?string`

### `CreateRoomServiceUseCase`
- Lee `cleaning_amount` del input
- Valida: `cleaning_amount <= gross_girl_amount`
- Lanza `GirlIncomeDomainException::cleaningExceedsGirlAmount()` si no pasa

### `GirlIncomeDomainException`
- Nuevo método: `cleaningExceedsGirlAmount()` → mensaje: "El monto de limpieza no puede superar el monto bruto de la chica."

### `RoomServiceRepositoryInterface::create()` y `EloquentRoomServiceRepository::create()`
- Nuevos parámetros: `grossGirlAmount`, `cleaningAmount`
- Se almacenan en la BD

### `RoomServiceModel`
- `gross_girl_amount` y `cleaning_amount` en `$fillable` y `$casts`

### `GirlIncomeMapper::roomService()`
- Expone `gross_girl_amount` y `cleaning_amount` en la respuesta de la API

### `CreateRoomServiceRequest`
- Nuevo campo opcional: `cleaning_amount` (nullable, numeric, min:0)

### `RoomServiceController::store()`
- Pasa `cleaning_amount` al DTO

### `MarkRoomCleanUseCase`
- **Prioridad de monto para cleaning_task:**
  1. `room_service.cleaning_amount` si > 0 (nueva regla)
  2. `staff_profile.cleaning_room_amount` (fallback)
  3. Config `nightpos.cleaning.default_room_amount` (fallback final)

### `EloquentStaffSettlementRepository` — GIRL_ROOM description
- Si hay `cleaning_amount > 0`, la descripción muestra: `"Pieza — Hab. X (limpieza -10.00)"`

---

## Flujo de Liquidaciones

### Chica (GIRL_ROOM)
- `amount` = `room_services.girl_amount` (YA es neto, no requiere cambio adicional)
- Descripción incluye el descuento si aplica

### Limpieza (CLEANING_ROOM)
- `amount` = `cleaning_tasks.amount`
- `cleaning_tasks.amount` viene de `room_service.cleaning_amount` (cuando > 0) o del perfil

### Sin duplicidad
- `cleaning_tasks` tiene `UNIQUE(room_service_id)` → imposible crear 2 tasks para la misma pieza
- Al re-usar una habitación se crea un nuevo `room_service_id`, generando un nuevo `cleaning_task`

---

## Contabilidad de Caja

La venta de pieza ingresa completa a caja al crearse el servicio:
- `total_amount` → INCOME movement en cash session

Al pagar liquidaciones:
- Chica recibe `girl_amount` (neto) → EXPENSE movement
- Limpieza recibe `cleaning_amount` → EXPENSE movement

La casa conserva `house_amount` = `total_amount - gross_girl_amount` (sin tocar limpieza).

---

## Tests Agregados

Archivo: `tests/Feature/Api/V1/RoomServiceCleaningDeductionTest.php`

| Test | Verificación |
|------|-------------|
| calculates amounts correctly | gross=100, net=90, cleaning=10, house=100 |
| without cleaning_amount = no deduction | girl_amount = gross_girl_amount |
| cleaning > gross → 422 | Validación de negocio |
| mark room clean uses room_service.cleaning_amount | cleaning_task.amount = 10 |
| girl settlement uses net amount | settlement item = 90 |
| cleaning settlement uses cleaning_task.amount | settlement item = 10 |
| two uses of same room = two cleaning_tasks | amounts independientes |
| zero cleaning_amount falls back to profile | cleaning_task.amount > 0 desde perfil |

**Suite total: 333 tests, 2258 assertions (100% verde)**

---

## API — Endpoint actualizado

### POST /api/v1/room-services

**Nuevo campo en payload:**
```json
{
  "room_id": 1,
  "girl_user_id": 10,
  "total_amount": 200,
  "girl_percent": 50,
  "cleaning_amount": 10,
  "payment_method": "CASH",
  "duration_minutes": 30
}
```

**Respuesta incluye:**
```json
{
  "gross_girl_amount": "100.00",
  "girl_amount": "90.00",
  "cleaning_amount": "10.00",
  "house_amount": "100.00"
}
```
