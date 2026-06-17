# Settlement / Close-Check Consistency Fix (Backend)

**Fecha:** 2026-06-17  
**Estado:** ✅ Implementado  
**Tipo:** Bugfix — alinear scope liquidaciones vs close-check caja

---

## Problema

Cajera con caja abierta:

- **Close-check** bloqueaba: «Hay pagos pendientes.»
- **Liquidaciones → Generar** respondía: «No hay liquidaciones para este turno.»

Causa: fuentes distintas y scopes desalineados.

| Endpoint | Turno usado | Scope settlements |
|----------|-------------|-------------------|
| `close-check` | `cash_session.official_shift_id` | Todo el turno (sin filtrar caja) |
| `current-shift` (cajera) | A veces `open_shift` + `empty_overview=true` | Vacío aunque existían PENDING |
| `generate-current-shift` | `EnsureOperationalShift` (turno OPEN) | Turno completo |

---

## Causa raíz (hipótesis confirmadas)

### B — Scope `my_cash_session` ocultaba PENDING

`SettlementShiftScopeResolver` devolvía `empty_overview=true` si no había ventas en la caja, **aunque existieran liquidaciones PENDING** que bloqueaban el cierre.

### A — Desalineación turno en generate

Cajera generaba sobre turno OPEN global; close-check usaba turno de la sesión de caja. Ahora generate de cajera usa `cash_session.official_shift_id`.

### Close-check sin filtro de caja

Contaba **todos** los PENDING del turno, incluyendo liquidaciones de otra cajera en el mismo turno.

---

## Solución

### 1. `SettlementShiftScopeResolver`

- Cajera básica → siempre `my_cash_session` (antes de full-scope admin).
- `shift_id` = turno de la **sesión de caja** (no el OPEN global si difieren).
- `empty_overview=false` si hay actividad, PENDING en scope, o fuentes sin liquidar.

### 2. `EloquentStaffSettlementRepository`

Nuevos métodos:

- `countPendingSettlements($tenant, $branch, $shift, $cashSessionId?, $staffRole?)`
- `sumPendingSettlementAmount(...)`
- `countGeneratedSettlements(...)`
- `settlementScopeSummary(...)`

Filtro caja: `cash_session_id = X` **o** limpieza turno (`cash_session_id` null + `staff_role=CLEANING`).

`getCurrentShiftOverview` aplica el mismo filtro cuando `cashSessionId` está presente.

### 3. `CashSessionCloseCheckBuilder`

- Pasa `cashSessionId` a conteos de fuentes y PENDING.
- Blockers tipados:
  - `SETTLEMENTS_NOT_GENERATED` — debe generar
  - `SETTLEMENTS_PENDING_PAYMENT` — debe pagar (con `route` a settlements/waiters/girls/cleaning)
- Summary ampliado: `generated_pending_count`, `generated_pending_amount`, `unsettled_sources_count`, etc.

### 4. `GenerateCurrentShiftSettlementsUseCase`

- Cajera con caja abierta → `generateForShift` sobre `cash_session.official_shift_id`.
- Mensaje si `created_items=0` pero hay PENDING: «No hay nuevas liquidaciones… Ya existen pagos pendientes.»

### 5. Contexto API (`SettlementOperationalContextBuilder`)

Todas las respuestas de liquidaciones/close-check incluyen:

```json
{
  "context": {
    "current_shift_id": 12,
    "open_shift_id": 12,
    "cash_session_official_shift_id": 12,
    "resolved_settlement_shift_id": 12,
    "scope": "my_cash_session",
    "empty_overview": false
  },
  "settlement_summary": {
    "generated_pending_count": 2,
    "generated_pending_amount": "45.00",
    "unsettled_sources_count": 0,
    "already_generated_count": 2,
    "already_generated_pending_count": 2
  }
}
```

---

## Tests

`tests/Feature/Api/V1/SettlementCloseCheckConsistencyTest.php` — 7 escenarios:

1. Fuentes sin liquidar → `SETTLEMENTS_NOT_GENERATED`
2. Generate → close-check `SETTLEMENTS_PENDING_PAYMENT` + current-shift muestra PENDING
3. Re-generar con PENDING → `created_items=0`, summary con pendientes
4. Pagar todos → sin blocker de pago
5. Mismo `official_shift_id` en close-check, generate y caja
6. PENDING garzón sin `cash_session_id` no bloquea caja actual
7. Sin fuentes ni PENDING → sin blockers de liquidación

Actualizado: `CashierCloseCheckTest` (código `settlements_not_generated`).

---

## Archivos modificados

| Archivo |
|---------|
| `SettlementShiftScopeResolver.php` |
| `EloquentStaffSettlementRepository.php` |
| `StaffSettlementRepositoryInterface.php` |
| `CashSessionCloseCheckBuilder.php` |
| `GenerateCurrentShiftSettlementsUseCase.php` |
| `GetCurrentShiftSettlementsUseCase.php` |
| `SettlementOperationalContextBuilder.php` |
| `GetCashSessionCloseCheckUseCase.php` |

---

## Validación manual

1. Venta con acompañante → cobrar.
2. Generar liquidaciones → no pagar.
3. Cerrar caja → blocker `SETTLEMENTS_PENDING_PAYMENT`.
4. Liquidaciones → ver PENDING en Chicas/Garzones.
5. Generar otra vez → mensaje «no hay nuevas… hay pendientes».
6. Pagar → close-check ya no bloquea por pagos.
