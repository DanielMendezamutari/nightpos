# Fix — Impresión movimientos de caja y cierres (backend)

Fecha: 2026-06-21

## Implementado

### Enums

- `PrintJobType::CashMovement` (`CASH_MOVEMENT`)
- `PrintJobType::ShiftClose` (`SHIFT_CLOSE`)
- `PrintJobSourceType::CashMovement` (`cash_movement`)

### Builder (`PrintTicketContentBuilder`)

- `buildCashMovement()` — MOVIMIENTO DE CAJA, tipo, método, monto, motivo, cajera, caja, fecha, no fiscal
- `buildCashClose()` — CIERRE DE CAJA / CIERRE ADMINISTRATIVO con totales, diferencia, motivo admin, pendientes
- `buildShiftClose()` — CIERRE DE TURNO con ventas, liquidaciones, arqueo

### Use cases

| Use case | Rol |
|----------|-----|
| `CreateCashMovementPrintJobUseCase` | Auto al registrar movimiento manual |
| `CreateCashClosePrintJobUseCase` | Auto al cerrar caja (normal y admin) |
| `CreateShiftClosePrintJobUseCase` | Bajo demanda |
| `PrintCashMovementUseCase` | Reimpresión movimiento |
| `PrintCashCloseUseCase` | Reimpresión cierre caja |
| `PrintShiftCloseUseCase` | Impresión cierre turno |
| `GetCashMovementUseCase` | Vista imprimible movimiento |
| `GetCashSessionUseCase` | Vista imprimible cierre (cajera propia) |
| `CashPrintPresenter` | Payloads movement / close / shift |

### Hooks

- `RegisterCashMovementUseCase` → `movement`, `print_job`, `print_warning`
- `CloseCashSessionUseCase` → `print_job`, `print_warning`
- `ForceCloseCashSessionAdminUseCase` → `print_job`, `print_warning`
- `CloseOfficialShiftUseCase` → sin auto (spec: bajo demanda)

### Rutas API

| Método | Ruta |
|--------|------|
| GET | `/cash/movements/{id}` |
| POST | `/cash/movements/{id}/print` |
| GET | `/cash/sessions/{id}` |
| POST | `/cash/sessions/{id}/print-close` |
| POST | `/shifts/{id}/print-closure` |

Permiso: `cash.access` / `shifts.close`

### Tests

`tests/Feature/Api/V1/CashMovementAndClosurePrintTest.php` — **10 tests ✅**

1. Ingreso crea `CASH_MOVEMENT`
2. Egreso crea `CASH_MOVEMENT`
3. Sin impresora → movimiento OK + `print_warning`
4. Ticket incluye tipo, método, monto, motivo
5. Reimpresión crea nuevo job
6. Cierre caja crea `CASH_CLOSE`
7. Cierre sin impresora no falla
8. Cierre admin incluye motivo y admin
9. Cierre turno bajo demanda → `SHIFT_CLOSE`
10. Sin duplicados sin flag reprint

## Reglas operativas

- Movimientos automáticos de cobro/venta **no** pasan por `RegisterCashMovementUseCase` manual
- Idempotencia: `cash_movement:{id}:v1`, `cash_close:{id}:v1`, `shift_close:{id}:v1`
- Reimpresión: clave con `:reprint:{timestamp}`

## Boot fix (2026-06-22)

Import faltante de `GetCashMovementUseCase` en `CashController.php` provocaba `ReflectionException` al boot de rutas cash. Ver `CASH_MOVEMENT_CLOSURE_PRINT_BACKEND_BOOT_FIX_REPORT.md`.
