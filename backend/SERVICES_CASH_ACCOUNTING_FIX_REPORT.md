# SERVICES_CASH_ACCOUNTING_FIX_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Corrección:** Servicios requieren caja abierta + movimiento INCOME  
**Fecha:** 2026-06-08

> **Bugfix 2026-06-08:** Ver `SERVICE_CASH_SESSION_RESOLUTION_FIX_REPORT.md` — resolver único `OpenCashSessionResolver`; frontend corregía lectura de `/cash/session/current`.

---

## 1. Regla de negocio

Manilla, pieza y show son **ingresos de caja**. No se pueden registrar sin sesión de caja abierta del usuario operador (mismo modelo que cobro de venta).

Si no hay caja abierta → **422** con mensaje:

`Debe abrir caja antes de registrar este servicio.`

---

## 2. Migración

`2026_06_08_100060_services_cash_accounting.php`

| Tabla | Campos nuevos |
| ----- | ------------- |
| `bracelets` | `cash_session_id`, `payment_method`, `cash_movement_id` |
| `room_services` | `cash_session_id`, `payment_method`, `cash_movement_id` |
| `shows` | `cash_session_id`, `payment_method`, `cash_movement_id` |
| `cash_movements` | `source_type`, `source_id` |

---

## 3. Servicio central

`App\Application\Cash\Services\ServiceIncomeCashRecorder`

- `requireOpenSession()` — valida caja abierta del usuario (`findOpenForUser`)
- `normalizePaymentMethod()` — valida método habilitado en sucursal
- `recordIncome()` — crea movimiento `INCOME` con trazabilidad

**Descripciones:**

| Servicio | `source_type` | Descripción |
| -------- | ------------- | ----------- |
| Manilla | `BRACELET` | `Manilla - {chica}` |
| Pieza | `ROOM_SERVICE` | `Pieza - {habitación} - {chica}` |
| Show | `SHOW` | `Show - {tipo} - {chica}` |

---

## 4. Use cases actualizados

- `CreateBraceletUseCase`
- `CreateRoomServiceUseCase`
- `CreateShowUseCase`

Flujo transaccional: crear servicio → movimiento caja → asociar `cash_session_id`, `cash_movement_id`, `official_shift_id`.

---

## 5. API — campo obligatorio

`payment_method` requerido en:

- `POST /api/v1/bracelets`
- `POST /api/v1/room-services`
- `POST /api/v1/shows`

---

## 6. Pieza — cálculo por porcentaje

El backend calcula (no confía en montos enviados):

```
girl_amount = total_amount * girl_percent / 100
house_amount = total_amount - girl_amount
```

Liquidación `GIRL_ROOM` usa `girl_amount` persistido.

---

## 7. Tests

`tests/Feature/Api/V1/ServicesCashAccountingTest.php`

- Rechazo sin caja (manilla, pieza, show)
- Registro con movimiento INCOME
- Split 50/50 en pieza 200
- Flujo DUE → finish → mark-clean

---

## 8. Validación dev

1. Login cajera → intentar pieza sin caja → 422
2. Abrir caja → registrar pieza 200 / 50% → movimiento 200 en caja
3. Verificar `cash_movements.source_type` y `source_id`
