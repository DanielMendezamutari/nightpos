# Auditoría — Impresión movimientos de caja y cierres (backend)

Fecha: 2026-06-21

## Resumen

Antes del fix, los movimientos manuales de caja y los cierres operaban sin `print_jobs`. Solo existían vistas imprimibles por navegador (`PrintableCashSessionReport`, `PrintableShiftClosureReport`).

## Parte 1 — Movimientos de caja

| Item | Estado previo |
|------|---------------|
| `PrintJobType::CASH_MOVEMENT` | ❌ No existía |
| `PrintJobSourceType::cash_movement` | ❌ No existía |
| `PrintTicketContentBuilder::buildCashMovement()` | ❌ No existía |
| `CreateCashMovementPrintJobUseCase` | ❌ No existía |
| `RegisterCashMovementUseCase` crea print_job | ❌ Solo SSE |
| `POST /cash/movements/{id}/print` | ❌ No existía |

## Parte 2 — Cierre de caja

| Pregunta | Respuesta previa |
|----------|------------------|
| ¿Existe enum `CASH_CLOSE`? | ✅ Sí, pero stub en builder |
| ¿Existe `buildCashClose()` real? | ❌ Solo placeholder |
| ¿Existe `CreateCashClosePrintJobUseCase`? | ❌ No |
| ¿`CloseCashSessionUseCase` crea print_job? | ❌ No |
| ¿`ForceCloseCashSessionAdminUseCase` crea print_job? | ❌ No |
| ¿Botón reimprimir por agente? | ❌ No |
| ¿Solo navegador? | ✅ `PrintableCashSessionReport` + `window.print()` |

## Parte 3 — Cierre de turno

| Pregunta | Respuesta previa |
|----------|------------------|
| ¿Existe enum `SHIFT_CLOSE`? | ❌ No |
| ¿Existe builder? | ❌ No |
| ¿`CloseOfficialShiftUseCase` crea print_job? | ❌ No (bajo demanda) |
| ¿Endpoint reimpresión? | ❌ No |
| ¿Reporte gerencial? | Parcial — summary API + CSV; ticket térmico ausente |

## Pipeline reutilizado

- `print_jobs` + agente Go
- `PrintTicketContentBuilder`
- `OperationalEventEmitter` (`print_job.created`)
- Idempotencia por clave (`cash_movement:{id}:v1`, `cash_close:{id}:v1`, `shift_close:{id}:v1`)

## Referencias

- `RegisterCashMovementUseCase.php`
- `CloseCashSessionUseCase.php`
- `ForceCloseCashSessionAdminUseCase.php`
- `CloseOfficialShiftUseCase.php`
- `PrintTicketContentBuilder.php`
