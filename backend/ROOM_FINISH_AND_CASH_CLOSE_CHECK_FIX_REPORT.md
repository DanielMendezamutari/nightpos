# ROOM_FINISH_AND_CASH_CLOSE_CHECK_FIX_REPORT.md (Backend)

**Bugfix operativo:** terminar pieza libera habitación + close-check alineado con cola de cobro  
**Fecha:** 2026-06-22  
**Estado:** Completado

---

## Problema 1 — Terminar pieza debe liberar habitación

### Regla

`POST /api/v1/room-services/{id}/finish` (cajera) → habitación `AVAILABLE`, pieza `FINISHED`, SSE `room.updated`.

Limpieza opcional sigue en `POST /cleaning/room-services/{id}/finish` con `releaseRoomImmediately: false` → habitación `CLEANING`.

### Cambios

| Archivo | Cambio |
|---------|--------|
| `FinishRoomServiceUseCase.php` | Flag `releaseRoomImmediately` (default `true`); `releaseAfterService()` o `setCleaning()` |
| `RoomRepositoryInterface` / `EloquentRoomRepository` | `releaseAfterService()` OCCUPIED → AVAILABLE |
| `RoomServiceController::finish()` | `releaseRoomImmediately: true` |
| `CleaningController::finish()` | `releaseRoomImmediately: false` |

Mensaje OK cajera: *Pieza terminada. Habitación disponible para nueva pieza.*

### Tests

`tests/Feature/Api/V1/RoomFinishAndCashCloseCheckFixTest.php` (problema 1)  
Actualizado `RoomsPhase18Test.php` — finish cajera espera `AVAILABLE`.

---

## Problema 2 — Close-check vs cola de cobro

### Causa

- `CashSessionCloseCheckBuilder` contaba comandas con `official_shift_id` de la **sesión de caja**.
- `GET /orders?scope=cashier_chargeable&cashier_scope=1` usa el turno de `findOpenForBranch()`.
- Cuando divergían (sesión en turno viejo, turno abierto distinto), close-check mostraba pendientes que la cola no listaba.
- Además contaba `OPEN` además de `SENT_TO_BAR` (regresión corregida).

### Fix — fuente única de verdad

`CashierChargeableOrdersScope`:

- `STATUSES = ['SENT_TO_BAR']` (via `OrderListScopeResolver::CASHIER_CHARGEABLE`)
- `countForCashierScope(tenant, branch)` — resuelve turno como `ListOrdersUseCase` con `cashier_scope=1`

`CashSessionCloseCheckBuilder` usa `countForCashierScope()` para `active_orders`.

### Regla final

Si la cola de cobro devuelve 0 comandas `SENT_TO_BAR` del turno abierto, close-check devuelve 0 en `summary.active_orders`.

No bloquea por: `OPEN`, `BILLED`, `CANCELLED`, otro turno, otra sucursal.

### Tests

`RoomFinishAndCashCloseCheckFixTest.php` (problema 2) — 8 escenarios  
`CashierCloseCheckTest.php` — baseline sin `W-DEMO-03` del seeder; OPEN no bloquea.

---

## Archivos clave

| Archivo | Rol |
|---------|-----|
| `CashierChargeableOrdersScope.php` | Conteo compartido close-check + cola |
| `CashSessionCloseCheckBuilder.php` | Blocker `active_orders` alineado |
| `ListOrdersUseCase.php` | Cola `cashier_chargeable` (referencia) |
| `FinishRoomServiceUseCase.php` | Liberación inmediata habitación |

---

## Referencias

- Frontend: `frontend/ROOM_FINISH_AND_CASH_CLOSE_CHECK_FIX_REPORT.md`
- Close-check previo: `backend/CASHIER_CLOSE_CHECK_REPORT.md`
