# SETTLEMENT_PAYMENT_CASH_SESSION_FIX_REPORT.md (Backend)

**Bugfix:** pago de liquidaciones no detectaba caja abierta del usuario  
**Fecha:** 2026-06-16  
**Estado:** Completado

---

## 1. Problema

Al pagar liquidaciones (garzón, chica, limpieza), `POST /settlements/{id}/mark-paid` respondía **422 — Debe abrir caja** aunque `GET /cash/session/current` sí devolvía sesión `OPEN` para la cajera.

Causas identificadas:

1. **Mismo resolver, distinto contexto de usuario** — en algunos flujos el `userId` resuelto no coincidía con `opened_by_user_id` de la caja (p. ej. admin intentando pagar con caja de otra cajera).
2. **Mensaje engañoso** — si faltaba motivo de egreso configurado, se lanzaba `cashRequiredForPayment()` en lugar de un error de motivo.
3. **Sin diagnóstico** — no había forma de comparar sesiones OPEN en sucursal vs. usuario autenticado.

---

## 2. Regla de negocio (sin cambios)

Para pagar liquidaciones sigue siendo obligatorio:

- Usuario con permiso `settlements.pay`
- Caja **OPEN** del **mismo usuario** (`opened_by_user_id = auth user`) en tenant/sucursal actual
- Crear movimiento `EXPENSE`, descontar `expected_cash`, marcar `PAID`, registrar `paid_by_user_id` y `paid_at`

---

## 3. Fuente única de verdad

`OpenCashSessionResolver::resolveOpenCashSessionForUser(tenant_id, branch_id, user_id)`

Usada por:

| Use case | Método |
|----------|--------|
| `GetCurrentCashSessionUseCase` | `findOpenForCurrentUser()` (alias) |
| `MarkSettlementPaidUseCase` | `requireOpenCashSession()` |
| Servicios con caja (`ServiceIncomeCashRecorder`, etc.) | mismo resolver |

Filtro en repositorio:

- `tenant_id`, `branch_id`, `opened_by_user_id`, `status = OPEN`

---

## 4. Cambios implementados

| Archivo | Cambio |
|---------|--------|
| `OpenCashSessionResolver.php` | Alias `resolveOpenCashSessionForUser()` |
| `CashSessionRepositoryInterface` + `EloquentCashSessionRepository` | `listOpenSessionsForBranch()` para debug |
| `MarkSettlementPaidUseCase.php` | `requireOpenCashSession()` + debug; respuesta incluye `cash_session_id`; motivo faltante → `expenseReasonRequired()` |
| `SettlementCashSessionRequiredException.php` | Nueva excepción con `debugContext` |
| `StaffSettlementDomainException.php` | `expenseReasonRequired()` |
| `bootstrap/app.php` | En `app.debug`, 422 incluye `data.cash_session_debug` |

### Debug en dev (`app.debug = true`)

Cuando mark-paid falla por caja cerrada:

```json
{
  "message": "Debe abrir caja para pagar esta liquidación.",
  "data": {
    "cash_session_debug": {
      "auth_user_id": 3,
      "tenant_id": 1,
      "branch_id": 1,
      "open_cash_session_found": false,
      "open_cash_sessions_same_branch": [
        { "id": 7, "opened_by_user_id": 3, "status": "OPEN", "official_shift_id": 2 }
      ]
    }
  }
}
```

---

## 5. Endpoint

| Método | Ruta | Permiso |
|--------|------|---------|
| POST | `/api/v1/settlements/{id}/mark-paid` | `settlements.pay` |

Respuesta OK incluye:

```json
{
  "data": {
    "settlement": { "status": "PAID", ... },
    "cash_session_id": 7
  }
}
```

---

## 6. Tests

`tests/Feature/Api/V1/SettlementPaymentCashSessionTest.php` — 11 casos:

1. mark-paid usa la misma caja que `/cash/session/current`
2. Cajera con caja puede pagar garzón
3. Cajera con caja puede pagar chica
4. Cajera con caja puede pagar limpieza
5. Sin caja → 422
6. Admin sin caja propia no puede pagar con caja de otra cajera
7. Liquidación de otra sucursal → 403 (sin acceso a sucursal)
8. `expected_cash` baja tras el pago
9. `cash_movement` ligado al `cash_session_id` correcto
10. `paid_by_user_id` = cajera autenticada
11. Debug `cash_session_debug` cuando falla en modo debug

Suite completa: **462 tests OK**.

---

## 7. Referencias

- Frontend: `frontend/SETTLEMENT_PAYMENT_CASH_SESSION_FIX_REPORT.md`
- Pago mixto UX: `frontend/MIXED_PAYMENT_UX_SIMPLIFICATION_REPORT.md`
- Método de pago en liquidaciones: `backend/SETTLEMENT_PAYMENT_METHOD_REPORT.md`

---

## 8. Actualización método de pago (2026-06-16)

`mark-paid` exige `payment_method`. El egreso se registra con `source_type=STAFF_SETTLEMENT` y afecta el neto del método correspondiente (solo CASH baja `expected_cash` físico).
- Cierre caja: `backend/CASHIER_CLOSE_CHECK_REPORT.md`
