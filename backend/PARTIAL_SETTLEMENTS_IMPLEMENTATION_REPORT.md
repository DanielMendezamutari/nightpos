# Liquidaciones parciales / múltiples cortes — Implementación (Backend)

**Fecha:** 2026-06-16  
**Estado:** Completado  
**Auditoría base:** `PARTIAL_SETTLEMENTS_AUDIT.md`

---

## Resumen

El sistema ahora permite **múltiples liquidaciones (cortes) por persona y turno**, manteniendo deduplicación por fuente vía `staff_settlement_items`.

Caso María resuelto: pagar corte #1 → nueva actividad → generar → **corte #2 PENDING** con solo fuentes nuevas.

---

## Cambios de esquema

**Migración:** `2026_06_16_100010_allow_multiple_settlements_per_shift_staff.php`

- Eliminado unique `(official_shift_id, staff_user_id, settlement_type)`.
- Agregado índice `(official_shift_id, staff_user_id, settlement_type, status)`.
- Índice auxiliar `(official_shift_id, staff_user_id, settlement_type)` para FKs MySQL.

---

## Lógica de generación

### `ensureSettlement()` — regla nueva

1. Buscar settlement **PENDING** para `(shift, staff_user_id, settlement_type)`.
2. Si existe → reutilizar.
3. Si no → **crear nuevo PENDING** (aunque existan settlements PAID del mismo turno/persona).

### Deduplicación (sin cambios)

- `sourceAlreadySettled()` / `saleItemAlreadySettled()` siguen siendo la fuente de verdad.
- Uniques en `staff_settlement_items` impiden duplicar fuentes.

### `CLEANING_BASE`

- `source_id` compuesto: `(official_shift_id * 100000) + cleaning_user_id`.
- Base **una vez por turno por persona** (no por corte).
- Segundo corte de limpieza incluye solo `CLEANING_ROOM` nuevas.

### API — campos nuevos en settlement

- `cut_number` (int): ordinal del corte por persona/tipo/turno.
- `cut_label` (string): ej. `"Corte #2"`.

---

## Cierre operativo

### Caja — `CashSessionCloseCheckBuilder`

Nuevo blocker:

| Código | Condición |
|--------|-----------|
| `unsettled_settlement_sources` | Fuentes liquidables del turno sin `staff_settlement_item` |

Método: `countUnsettledShiftSources()` en `StaffSettlementRepositoryInterface`.

### Turno — `EloquentReportReadRepository::getShiftClosureCheck`

Mismo blocker `unsettled_settlement_sources` antes de cerrar turno.

---

## Tests

**Nuevo:** `tests/Feature/Api/V1/PartialSettlementsTest.php` (10 casos)

**Actualizado:** `SettlementsPhase16Test` — paid settlement no se modifica, pero se crea nuevo PENDING.

**Suite:** 482 tests OK.

---

## Archivos modificados

| Archivo | Cambio |
|---------|--------|
| `EloquentStaffSettlementRepository.php` | ensureSettlement, cut_number, CLEANING_BASE, countUnsettled |
| `StaffSettlementRepositoryInterface.php` | countUnsettledShiftSources |
| `CashSessionCloseCheckBuilder.php` | blocker fuentes huérfanas |
| `EloquentReportReadRepository.php` | shift close + DI settlements repo |
| Migración allow_multiple_settlements | schema |

---

## Despliegue

```bash
php artisan migrate
```

No modifica settlements PAID existentes. Datos históricos conservados.
