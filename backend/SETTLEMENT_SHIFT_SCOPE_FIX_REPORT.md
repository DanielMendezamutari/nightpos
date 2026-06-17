# SETTLEMENT_SHIFT_SCOPE_FIX_REPORT.md (Backend)

**Bugfix crítico:** liquidaciones mezclando turnos  
**Fecha:** 2026-06-15  
**Estado:** Completado

---

## 1. Problema

Al abrir caja en un turno nuevo, **Finanzas → Liquidaciones** mostraba ventas/liquidaciones del turno anterior o de otra cajera.

**Causa raíz:** `resolveOverviewShiftId()` tenía fallback que, sin turno OPEN, devolvía el último turno con liquidaciones o el último turno de la sucursal — mezclando datos históricos con la operación actual.

---

## 2. Solución

### Turno operativo = solo OPEN

| Endpoint | Resolución de turno |
|----------|---------------------|
| `GET /settlements/current-shift` | `resolveOpenShiftId()` — solo turno OPEN |
| `GET /settlements/current-shift/pending-sources` | Idem |
| `POST /settlements/generate-current-shift` | `EnsureOperationalShiftUseCase` (sin cambio) |

`resolveOverviewShiftId()` mantiene fallback **solo** para vistas de earnings/consola post-cierre (chica, limpieza, shift console).

### Fuentes de liquidación

`generateForShift()` ya filtraba por `official_shift_id` en:

- `sales` (join sale_items)
- `bracelets`, `room_services`, `shows`, `cleaning_tasks`

Se añadió `countShiftSources()` para diagnóstico.

### Debug en API

Respuestas incluyen:

```json
{
  "context": {
    "tenant_id": 1,
    "branch_id": 1,
    "current_shift_id": 15,
    "cash_session_id": 40,
    "cashier_user_id": 8
  },
  "sources_summary": {
    "sales": 0,
    "bracelets": 0,
    "rooms": 0,
    "shows": 0,
    "cleaning_tasks": 0
  }
}
```

### Close-check de caja

Usa **únicamente** `cash_session.official_shift_id` (sin fallback al turno global).

---

## 3. Archivos clave

| Archivo | Cambio |
|---------|--------|
| `EloquentStaffSettlementRepository.php` | `resolveOpenShiftId`, `countShiftSources` |
| `GetCurrentShiftSettlementsUseCase.php` | Turno OPEN + context |
| `GetSettlementPendingSourcesUseCase.php` | Turno OPEN + cleaning_tasks count + context |
| `GenerateCurrentShiftSettlementsUseCase.php` | Context en respuesta |
| `SettlementOperationalContextBuilder.php` | Nuevo |
| `GetCashSessionCloseCheckUseCase.php` | Shift desde sesión de caja |

---

## 4. Tests

`tests/Feature\Api/V1/SettlementShiftScopeTest.php` — 5 tests:

1. Generate no toma ventas de turno cerrado
2. Current-shift solo turno OPEN
3. Close-check usa official_shift_id de caja
4. Historial sí muestra turnos anteriores
5. Turno nuevo sin ventas → overview vacío

Actualizado: `SettlementsPhase16Test` (post-cierre → current-shift vacío, historial con datos).

---

## 5. Referencias

- Frontend: `frontend/SETTLEMENT_SHIFT_SCOPE_FIX_REPORT.md`
- Cierre caja: `CASHIER_CLOSE_CHECK_REPORT.md`
