# PHASE_13_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Fase:** 13 — Turnos oficiales reales  
**Fecha:** 2026-06-02  
**Referencias:** `DOMAIN_DESIGN.md`, `PHASE_8_REPORT.md`, `PHASE_9_REPORT.md`, `PHASE_12_REPORT.md`

---

## 1. Tablas creadas

| Tabla | Descripción |
| ----- | ----------- |
| `official_shifts` | Turno oficial por sucursal (DAY/NIGHT, ventana horaria, `business_date`, estado OPEN/CLOSED) |
| `shift_closures` | Cierre con totales efectivo/QR/tarjeta, ventas, movimientos manuales, arqueo y diferencia |

**Columnas preparadas (sin lógica de liquidación):** `total_girl_payouts`, `total_waiter_payouts` en `shift_closures`.

**FK añadidas:** `official_shift_id` en `cash_sessions`, `orders`, `sales`.

Migraciones: `100012_create_official_shifts_tables`, `100013_add_official_shift_id_to_operational_tables`, `100014_assign_shifts_permissions_to_roles`.

**Compatibilidad MySQL/XAMPP (bugfix):** En `100012_create_official_shifts_tables`, las columnas operativas `starts_at`, `ends_at`, `opened_at`, `closed_at` (tablas `official_shifts` y `shift_closures`) usan `dateTime()` en lugar de `timestamp()`, porque MySQL con `sql_mode` estricto rechaza el default implícito de `TIMESTAMP NOT NULL` (error 1067). La lógica de negocio y los nombres de columna no cambian; Eloquent sigue mapeándolas como `datetime`.

---

## 2. Endpoints creados

| Método | Ruta | Permiso |
| ------ | ---- | ------- |
| GET | `/api/v1/shifts/current` | `shifts.access` |
| POST | `/api/v1/shifts/open` | `shifts.open` |
| POST | `/api/v1/shifts/{id}/close` | `shifts.close` |
| GET | `/api/v1/shifts` | `shifts.list` |
| GET | `/api/v1/shifts/{id}` | `shifts.list` |
| GET | `/api/v1/shifts/{id}/summary` | `shifts.list` |

---

## 3. Reglas implementadas

| # | Regla |
| - | ----- |
| 1 | Un solo turno OPEN por sucursal |
| 2 | Abrir caja, comanda y cobro **aseguran** turno (`EnsureOperationalShiftUseCase`; ver `SHIFT_REPORTING_MODE_REPORT.md`) |
| 3 | Apertura manual opcional (`shifts.open`); operación no bloqueada sin turno previo |
| 4 | `auto_created` en API si el turno fue creado por el sistema |
| 5–7 | `official_shift_id` en caja, comanda y venta |
| 8 | NIGHT: `ends_at` al día siguiente 09:00 |
| 9 | `business_date` fecha operativa explícita al abrir |
| 10 | Cierre calcula resumen y persiste `shift_closures` |
| 11 | Campos chicas/garzones null (preparados) |
| 12 | Caja existente intacta; solo exige turno previo |

**Ventanas:** DAY 09:00–21:00, NIGHT 21:00–09:00 (+1 día) vía `OfficialShiftWindowBuilder`.

**Cierre:** exige cajas cerradas (`hasOpenCashSessions`).

---

## 4. Integraciones

| Módulo | Cambio |
| ------ | ------ |
| Caja | `OpenCashSessionUseCase` + `official_shift_id` vía `ensure`; `GetCurrentCashSession` devuelve `shift` |
| Comandas | `CreateOrderUseCase` + FK; listado `?current_shift=1` |
| Ventas | `ChargeOrderUseCase` + FK; listado `?current_shift=1` |
| Permisos | `shifts.access`, `shifts.open`, `shifts.close`, `shifts.list` en seeder |

---

## 5. Tests

`tests/Feature/Api/V1/OfficialShiftsPhase13Test.php` (7 casos) + `ShiftReportingModeTest.php` (9 casos) + helpers `nightposOpenShift`, `nightposEnsureShiftOpen` en `Pest.php`.

**Suite:** 103 tests OK (incluye modo reportes).

---

## 6. Qué queda pendiente

- Liquidaciones finales de chicas y garzones.
- Reportes por `business_date` / turno en módulo finanzas.
- Bloqueo de operaciones si la hora actual sale de la ventana del turno (solo ventana declarada al abrir).
- Ver `SHIFT_REPORTING_MODE_REPORT.md` para auto-apertura por horario (implementado).

---

## 7. Próxima fase recomendada

**Fase 14 — Reportes y finanzas:** ventas por turno, exportación de cierre, o liquidación de comisiones según `DOMAIN_DESIGN.md`.

Detener implementación hasta nuevas instrucciones.
