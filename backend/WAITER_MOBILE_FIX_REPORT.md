# WAITER_MOBILE_FIX_REPORT (Backend)

Alcance mínimo: validación confirmada + datos demo + tests. **Sin cambios en reglas de dominio.**

---

## 1. Causa (lado servidor)

No había bug de validación: `CreateOrderUseCase` ya exige `table_label` efectivo o `service_area_id` activo.

El problema reportado era **frontend** (parseo API de ambientes + UX).

---

## 2. Cambios aplicados

| Archivo | Cambio |
|---------|--------|
| `database/seeders/NightPosSeeder.php` | Ambientes `M01`, `M02`, `VIP`, `BAR` en sucursal demo |
| `tests/Feature/Api/V1/PhaseC4WaiterTest.php` | +2 tests garzón: `service_area_id` y rechazo sin datos |

---

## 3. Tests

```
PhaseC4WaiterTest — 7 passed
```

- Garzón crea con `service_area_id` → `table_label` = nombre ambiente.
- Garzón con body vacío → HTTP 422.
- Flujo crear → ítem → enviar barra → cajero cobra (sin regresión).

---

## 4. Validación manual

Misma matriz que `frontend/WAITER_MOBILE_FIX_REPORT.md` con PIN `5678` y contexto `casa-demo` / `CENTRO`.

---

## 5. Pendientes

- Endpoint opcional `GET /waiter/service-areas` si se quiere desacoplar permiso `settings.service_areas`.
- Notificaciones push al garzón.
