# Fase 18 — Gestión de habitaciones (piezas) como recurso operativo

**Fecha:** 2026-06-02

---

## 1. Modelo habitaciones

Tabla `rooms` por tenant y sucursal:

| Campo | Descripción |
|-------|-------------|
| `code` | Código único en sucursal |
| `name` | Nombre visible |
| `room_type` | `STANDARD`, `VIP`, `SUITE` |
| `status` | Ciclo operativo |
| `default_duration_minutes` | Duración sugerida **opcional** (referencia interna) |
| `suggested_price` | Precio sugerido **opcional** (no es el cobro real) |
| `notes` | Opcional |

Enums: `App\Domain\Room\Enums\RoomType`, `RoomStatus`.

---

## 2. Estados

| Estado | Puede asignar pieza |
|--------|---------------------|
| `AVAILABLE` | Sí |
| `OCCUPIED` | No |
| `CLEANING` | No |
| `MAINTENANCE` | No |

Transiciones automáticas vía piezas:

- Registrar pieza con `room_id` → `AVAILABLE` → `OCCUPIED`
- Finalizar pieza → `OCCUPIED` → `CLEANING` (no vuelve solo a `AVAILABLE`)

Transiciones manuales:

- `POST /rooms/{id}/mark-clean` → `CLEANING` → `AVAILABLE` (permiso `rooms.clean`)
- `POST /rooms/{id}/mark-maintenance` → `AVAILABLE` o `CLEANING` → `MAINTENANCE`
- `POST /rooms/{id}/mark-available` → `MAINTENANCE` → `AVAILABLE`

---

## 3. Integración con piezas

- `room_services.room_id` nullable (compatibilidad con `room_label` libre).
- Crear pieza con `room_id`: valida disponibilidad, ocupa habitación, copia nombre/código.
- Sin `room_id`: flujo legacy con `room_label` / `room_number`.

Liquidaciones sin cambio: `GIRL_ROOM` solo en piezas `FINISHED`.

---

## 4. Flujo limpieza

1. Cajera/admin finaliza pieza.
2. Habitación pasa a `CLEANING`.
3. Usuario limpieza (`limpieza.demo`, PIN `3333`) ve `GET /rooms/cleaning`.
4. `POST /rooms/{id}/mark-clean` → `AVAILABLE`.

---

## 5. Flujo mantenimiento

Admin con `rooms.maintenance` envía habitación a mantenimiento y la libera con `mark-available`.

Mientras esté en `MAINTENANCE`, no se puede asignar pieza.

---

## 6. API

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/rooms` | `rooms.access` |
| GET | `/api/v1/rooms/available` | `rooms.access` |
| GET | `/api/v1/rooms/cleaning` | `rooms.access` |
| GET | `/api/v1/rooms/{id}` | `rooms.access` |
| POST | `/api/v1/rooms` | `rooms.create` |
| PUT | `/api/v1/rooms/{id}` | `rooms.update` |
| POST | `/api/v1/rooms/{id}/mark-clean` | `rooms.clean` |
| POST | `/api/v1/rooms/{id}/mark-maintenance` | `rooms.maintenance` |
| POST | `/api/v1/rooms/{id}/mark-available` | `rooms.maintenance` |

---

## 7. Permisos y seeder

Slugs: `rooms.access`, `rooms.create`, `rooms.update`, `rooms.clean`, `rooms.maintenance`.

Demo: Pieza 1–4 (STANDARD), VIP 1–2, todas `AVAILABLE`.

---

## 8. Tests

`tests/Feature/Api/V1/RoomsPhase18Test.php` — 10 casos (CRUD, ocupación, limpieza, mantenimiento, aislamiento).

---

## 9. Validación manual

1. `php artisan migrate` + `php artisan test`
2. `pnpm run dev` — login `admin.demo`
3. Operación → Habitaciones → dashboard y listado
4. Registrar pieza con selector de habitación
5. Finalizar pieza → habitación en limpieza
6. Login limpieza PIN `3333` → marcar limpia → `AVAILABLE`

---

## 10. Alta rápida de chica

Desde registrar pieza (y reutilizable en otros flujos):

- `POST /api/v1/staff/quick-girls` — permiso `staff.quick_create_girl`
- `GET /api/v1/staff/girls` — listado operativo de chicas
- Ver `backend/QUICK_GIRL_CREATE_REPORT.md`

---

## 11. Corrección modelo precios (2026-06-08)

Ver `ROOM_SERVICE_PRICING_MODEL_FIX_REPORT.md`:

- Habitación no define cobro ni liquidación.
- `room_services` guarda `total_amount`, `girl_amount`, `house_amount`.
- Liquidación `GIRL_ROOM` usa `girl_amount`.

---

## 12. Próxima fase recomendada

**Fase 19 — Reportes por habitación:** ingresos, ocupación, tiempo promedio, uso por turno, ranking de habitaciones (campos ya preparados en `room_id`).
