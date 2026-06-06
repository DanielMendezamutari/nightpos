# Fase C3 — Datos maestros administrativos (Backend)

## 1. Tablas creadas

| Tabla | Descripción |
|-------|-------------|
| `cash_movement_reasons` | Motivos INCOME/EXPENSE por tenant (branch opcional) |
| `payment_methods` | Métodos configurables (code, type CASH/QR/CARD/OTHER) |
| `service_areas` | Ambientes/mesas por sucursal |
| `room_types` | Tipos de habitación configurables |

**Columnas añadidas**

- `cash_movements.cash_movement_reason_id`, `cash_movements.notes`
- `orders.service_area_id`

Migración: `2026_06_07_100029_phase_c3_master_data.php`

## 2. Endpoints creados

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/cash-movement-reasons` | `settings.cash_reasons` |
| POST | `/api/v1/cash-movement-reasons` | `settings.cash_reasons.manage` |
| PUT | `/api/v1/cash-movement-reasons/{id}` | `settings.cash_reasons.manage` |
| GET | `/api/v1/payment-methods` | `settings.payment_methods` |
| POST | `/api/v1/payment-methods` | `settings.payment_methods.manage` |
| PUT | `/api/v1/payment-methods/{id}` | `settings.payment_methods.manage` |
| GET | `/api/v1/service-areas` | `settings.service_areas` |
| POST | `/api/v1/service-areas` | `settings.service_areas.manage` |
| PUT | `/api/v1/service-areas/{id}` | `settings.service_areas.manage` |
| GET | `/api/v1/room-types` | `settings.room_types` |
| POST | `/api/v1/room-types` | `settings.room_types.manage` |
| PUT | `/api/v1/room-types/{id}` | `settings.room_types.manage` |
| GET | `/api/v1/settings/first-night-checklist` | `settings.checklist` |

Query params: `active_only`, `type` (motivos), `enabled_only` (métodos).

## 3. Integraciones

- **Caja:** `POST /cash/movements` exige `cash_movement_reason_id`; `notes` opcional; descripción generada desde catálogo.
- **Comandas:** `service_area_id` opcional; `table_label` sigue como fallback.
- **Habitaciones:** `room_type_id` o `room_type` enum legacy; `RoomTypeResolver` unifica código.
- **Cobros:** `ChargeOrderUseCase` valida métodos contra catálogo activo (`legacy_method` CASH/QR/CARD).

## 4. Datos maestros configurables

- Motivos demo: Limpieza, Taxi, Compra hielo, Compra comida, Multa, Otros (seeder).
- Métodos demo: CASH, QR, CARD (CASH obligatorio al deshabilitar).
- Tipos habitación demo: STANDARD, VIP, SUITE.
- Ambiente demo: Mesa 1 (M01).

## 5. Compatibilidad

- Enum de pago en ventas: CASH / QR / CARD / MIXED (compuesto).
- `room_type` en `rooms` sigue siendo string (STANDARD/VIP/SUITE o código de catálogo).
- Movimientos de venta en caja sin motivo (automáticos).

## 6. Tests

`tests/Feature\Api/V1/PhaseC3Test.php` — 10 casos. Suite total: **189** tests OK.

## 7. Validación manual

1. Login `admin.demo` / sucursal CENTRO.
2. CRUD motivos, métodos, ambientes, tipos habitación.
3. Egreso de caja con motivo + notas.
4. Comanda con ambiente y con `table_label` libre.
5. Habitación con `room_type_id`.
6. Checklist primera noche.

## 8. Próxima fase recomendada

**C4 — Reportes operativos básicos** (sin impresión): lecturas sobre catálogos C3 para cortes y resúmenes de turno.
