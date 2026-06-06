# CLEANING_MOBILE_MODE_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Feature:** Modo limpieza móvil + API dedicada  
**Fecha:** 2026-06-08

---

## 1. Permisos

Migración `2026_06_08_100061_cleaning_mobile_permissions.php`

| Permiso | Rol |
| ------- | --- |
| `cleaning.dashboard` | cleaning |
| `cleaning.room_services` | cleaning |
| `cleaning.check` | cleaning |
| `cleaning.finish` | cleaning |
| `cleaning.mark_clean` | cleaning |

Rol `cleaning` **solo** tiene permisos `cleaning.*` + `notifications.access` (sin admin, caja, ventas).

Usuario demo: `limpieza.demo` / PIN **3333**

---

## 2. Endpoints

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| GET | `/api/v1/cleaning/dashboard` | `cleaning.dashboard` |
| GET | `/api/v1/cleaning/rooms` | `cleaning.room_services` |
| GET | `/api/v1/cleaning/room-services/active` | `cleaning.room_services` |
| GET | `/api/v1/cleaning/room-services/due` | `cleaning.room_services` |
| POST | `/api/v1/cleaning/room-services/{id}/check` | `cleaning.check` |
| POST | `/api/v1/cleaning/room-services/{id}/finish` | `cleaning.finish` |
| POST | `/api/v1/cleaning/rooms/{id}/mark-clean` | `cleaning.mark_clean` |

Controller: `App\Http\Controllers\Api\V1\CleaningController`

---

## 3. Dashboard payload

Resumen: `active_count`, `due_count`, `cleaning_count`, `finished_today_count`

Listas: `active`, `due`, `cleaning`, `finished_today`

---

## 4. Flujo pieza (sin cambio de reglas)

| Evento | `room_service` | `room` |
| ------ | -------------- | ------ |
| Registro | `ACTIVE` | `OCCUPIED` |
| Tiempo cumplido (cron) | `DUE` | `OCCUPIED` (no pasa sola a limpieza) |
| Finalizar servicio | `FINISHED` | `CLEANING` |
| Marcar limpia | — | `AVAILABLE` |

---

## 5. Tests

- `ServicesCashAccountingTest` — acceso limpieza solo con permisos `cleaning.*`
- `RoomServiceFlowTest` — finish vía `/cleaning/room-services/{id}/finish`
- `RoomServicesPhase17Test` — rutas cleaning migradas
