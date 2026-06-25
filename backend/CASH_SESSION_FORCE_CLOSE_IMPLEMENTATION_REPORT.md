# CASH_SESSION_FORCE_CLOSE_IMPLEMENTATION_REPORT.md (Backend)

**Feature:** Cierre administrativo de caja  
**Fecha:** 2026-06-21  
**Estado:** Implementado

---

## Resumen

Permite a `tenant_owner`, `cashier_senior` y `super_admin` cerrar una `cash_session` `OPEN` de su sucursal sin resolver blockers, guardando trazabilidad completa y sin pagar liquidaciones.

---

## Migración

`database/migrations/2026_06_21_100070_add_force_close_to_cash_sessions.php`

Campos en `cash_sessions`:

- `is_forced_close`
- `forced_closed_by_user_id`
- `forced_closed_at`
- `forced_close_reason`
- `forced_close_notes`
- `close_blockers_snapshot` (json)
- `financial_summary_snapshot` (json)

Permiso creado: `admin.cash_sessions.force_close`

---

## API

| Método | Ruta | Permiso |
|--------|------|---------|
| GET | `/api/v1/admin/cash-sessions/{id}/close-check` | `admin.cash_sessions.force_close` |
| POST | `/api/v1/admin/cash-sessions/{id}/force-close` | `admin.cash_sessions.force_close` |

Body force-close:

```json
{
  "forced_close_reason": "cashier_left",
  "forced_close_notes": "Texto obligatorio",
  "declared_closing_amount": null
}
```

Motivos: `cashier_left`, `operational_error`, `blockers_unresolved`, `shift_change`, `other`

---

## Use cases

- `GetCashSessionCloseCheckAdminUseCase` — preview para modal
- `ForceCloseCashSessionAdminUseCase` — ejecuta cierre

Reglas V1 arqueo:

- `declared_closing_amount = null`
- `difference_amount = null`
- `expected_amount` desde snapshot financiero

Audit: `cash_session.force_closed`  
SSE: `cash.session.closed` con `forced: true`

---

## Tests

`tests/Feature/Api/V1/AdminCashSessionForceCloseTest.php` — 14 tests (129 assertions)

---

## Archivos principales

- `app/Application/Cash/UseCases/ForceCloseCashSessionAdminUseCase.php`
- `app/Application/Cash/UseCases/GetCashSessionCloseCheckAdminUseCase.php`
- `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentCashSessionRepository.php` → `forceClose()`
- `app/Application/Cash/Support/AdminCashSessionMapper.php`
- `app/Http/Controllers/Api/V1/Admin/AdminCashSessionController.php`
