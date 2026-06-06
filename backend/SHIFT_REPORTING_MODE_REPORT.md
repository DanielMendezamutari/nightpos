# SHIFT_REPORTING_MODE_REPORT.md

**Proyecto:** NIGHTPOS SaaS — Backend  
**Cambio:** Turnos solo para reportes (no bloquean operación)  
**Fecha:** 2026-06-02  
**Referencias:** `PHASE_13_REPORT.md`, `OperationalShiftScheduleResolver`, `EnsureOperationalShiftUseCase`

---

## 1. Regla de negocio

Los turnos oficiales **clasifican** ventas, caja, comandas y liquidaciones para reportes y fiscalización. **No** es obligatorio que un admin abra turno manual antes de que cajeras/garzones operen.

Si no existe un turno `OPEN` en la sucursal, el sistema lo crea automáticamente en la primera operación que lo requiera (abrir caja, crear comanda, cobrar, generar liquidaciones). La apertura manual (`POST /shifts/open`) sigue disponible para admins con `shifts.open`.

**Notas del turno auto:** `Turno creado automáticamente para clasificación de reportes` (`EnsureOperationalShiftUseCase::AUTO_SHIFT_NOTES`).

---

## 2. Horario automático (`OperationalShiftScheduleResolver`)

| Hora local | Tipo | `business_date` |
| ---------- | ---- | --------------- |
| 09:00 – 20:59 | DAY | hoy |
| 21:00 – 23:59 | NIGHT | hoy |
| 00:00 – 08:59 | NIGHT | ayer |

Ventanas horarias: DAY 09:00–21:00, NIGHT 21:00–09:00 (+1 día) vía `OfficialShiftWindowBuilder` (sin cambios respecto a Fase 13).

---

## 3. Componentes nuevos / modificados

| Archivo | Rol |
| ------- | --- |
| `Application/Shift/Services/OperationalShiftScheduleResolver.php` | Tipo, fecha de negocio y ventana según hora |
| `Application/Shift/UseCases/EnsureOperationalShiftUseCase.php` | Busca OPEN o crea turno en transacción |
| `Application/Shift/Services/OfficialShiftGuard.php` | `@deprecated`; `requireOpen()` delega en `ensure` |
| `OpenCashSessionUseCase`, `CreateOrderUseCase`, `ChargeOrderUseCase`, `GenerateCurrentShiftSettlementsUseCase` | Usan `ensure` en lugar de bloquear |
| `Application/Shift/Support/ShiftMapper.php` | Campo `auto_created` si las notas contienen «automáticamente» |
| `NightPosServiceProvider.php` | Registra resolver + ensure |

---

## 4. Permisos

| Acción | Permiso |
| ------ | ------- |
| Ver turno actual / historial | `shifts.access`, `shifts.list` |
| Abrir turno **manual** | `shifts.open` (solo admin/gerente) |
| Cerrar turno | `shifts.close` |
| Operar caja/comandas/cobros | `cash.*`, `orders.*`, `sales.charge` — **sin** `shifts.open` |

---

## 5. Tests

`tests/Feature/Api/V1/ShiftReportingModeTest.php` — 9 casos:

- Auto-turno al abrir caja, crear comanda o cobrar (tras cierre previo + nueva caja).
- Ventanas NIGHT (21:00+ y madrugada con `business_date` anterior).
- Cajera sin `shifts.open` puede operar; apertura manual sigue prohibida sin permiso.
- Aislamiento multi-tenant.

`OfficialShiftsPhase13Test.php`: escenarios de bloqueo por falta de turno sustituidos por validación con turno auto.

**Suite:** 103 tests OK (2026-06-02).

---

## 6. Validación manual

1. Login cajera (PIN `1234`) **sin** abrir turno manual.
2. Caja → abrir sesión → comanda → cobrar.
3. `GET /api/v1/shifts/current` → turno OPEN, `auto_created: true`.
4. Admin puede cerrar turno; nueva operación recrea turno según hora.

---

## 7. Pendiente (fuera de este cambio)

- Bloqueo si la hora actual sale de la ventana del turno ya abierto (solo ventana declarada al crear).
- Reportes export PDF por `business_date` en módulo finanzas.
