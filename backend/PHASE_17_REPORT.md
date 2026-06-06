# PHASE_17_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 17 — Servicios separados + control de piezas + notificaciones limpieza  
**Fecha:** 2026-06-02

> **Nota de negocio:** Piezas y shows no pertenecen al flujo de garzones. Son servicios directos administrados por caja/admin y liquidados únicamente a la chica correspondiente. El garzón solo participa en comandas, ventas, consumos y comisión por venta (`WAITER_COMMISSION`).

> **Actualización 2026-06-08:** Ver `SERVICES_CASH_ACCOUNTING_FIX_REPORT.md` (caja obligatoria en servicios) y `CLEANING_MOBILE_MODE_REPORT.md` (API `/api/v1/cleaning/*`, permisos `cleaning.*`, finish vía limpieza).

---

## 1. Diferencia manilla / pieza / show

| Concepto | Tabla | Liquidación | Tiempo |
| -------- | ----- | ------------- | ------ |
| Manilla | `bracelets` | `GIRL_BRACELET` | No |
| Pieza | `room_services` (sin garzón) | `GIRL_ROOM` (solo `FINISHED`) | Sí (`ACTIVE` → control) |
| Show | `shows` (sin garzón) | `GIRL_SHOW` | Solo hora registro |

---

## 2. Tablas

### `room_services` (ampliada)

`room_label`, `started_at`, `duration_minutes`, `expected_ends_at`, `ended_at`, `status` (`ACTIVE`/`FINISHED`/`CANCELLED`), `cleaning_user_id`, `checked_by_user_id`, `checked_at`, `alert_sent_at`.

Migraciones: `100020_extend_room_services_phase17`, `100023_drop_waiter_from_room_services_and_shows` (elimina `waiter_user_id` en piezas y shows).

### `notifications`

Campos según spec. Migración: `2026_06_04_100021_create_notifications_table.php`

---

## 3. Endpoints piezas

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| POST | `/api/v1/room-services` | `room_services.create` (+ `duration_minutes`, `girl_percent`) |
| GET | `/api/v1/room-services/active` | `room_services.cleaning_view` |
| GET | `/api/v1/room-services/due` | `room_services.cleaning_view` |
| GET | `/api/v1/room-services/control` | `room_services.cleaning_view` |
| POST | `/api/v1/room-services/{id}/finish` | `room_services.finish` |
| POST | `/api/v1/room-services/{id}/check` | `room_services.check` |

## 4. Endpoints notificaciones

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| GET | `/api/v1/notifications` | `notifications.access` |
| GET | `/api/v1/notifications/unread-count` | `notifications.access` |
| POST | `/api/v1/notifications/{id}/read` | `notifications.access` |
| POST | `/api/v1/notifications/read-all` | `notifications.access` |

Limpieza ve `role_target = CLEANING`. Admin/cajera con `notifications.read` ven todas del branch.

---

## 5. Comando y cron

```bash
php artisan room-services:check-due
```

Cron sugerido (cada minuto):

```
* * * * * cd /ruta/backend && php artisan room-services:check-due >> /dev/null 2>&1
```

---

## 6. Notificaciones piezas vencidas

- Condición: `status = ACTIVE` y `expected_ends_at <= now()`
- Crea `ROOM_SERVICE_DUE` para `CLEANING`, prioridad `HIGH`
- No duplica si ya existe UNREAD/READ para mismo `source_id`
- Marca `alert_sent_at` en la pieza

---

## 7. Liquidaciones (actualizado Fase 16)

- `GIRL_ROOM` solo piezas `FINISHED`
- `CANCELLED` y `ACTIVE` no entran
- Manillas y shows sin cambio de criterio

---

## 8. WhatsApp (preparado)

- `NotificationChannelInterface`
- `DatabaseNotificationChannel` (activo)
- `WhatsAppNotificationChannel` (placeholder, log only)
- Config `config/nightpos.php` + env `NIGHTPOS_WHATSAPP_*`

**Para activar WhatsApp real:** proveedor con API oficial (Twilio, Meta Cloud API, etc.), implementar adapter y `whatsapp_enabled=true`.

---

## 9. Usuario demo limpieza

- `limpieza.demo` / PIN `3333`
- `staff_role = CLEANING`

---

## 10. Tests

`tests/Feature/Api/V1/RoomServicesPhase17Test.php` — 8 casos. Suite: **129 tests OK**.

---

## 11. Validación manual

Ver `ROOM_SERVICE_NOTIFICATIONS_REPORT.md` y checklist en `PHASE_17_FRONTEND_REPORT.md`.

---

## 12. Reparto chica/casa (actualización 2026-06-08)

Ver `ROOM_SERVICE_PRICING_MODEL_FIX_REPORT.md`.

- `room_services.girl_percent` — snapshot del % al registrar.
- `girl_amount` y `house_amount` calculados en servidor; no confiar en valores del cliente.
- Default: `config('nightpos.room_service.default_girl_percent')` = 50.

---

## 13. Próxima fase

Reportes gerenciales exportables y dashboard consolidado (Fase 18 sugerida).
