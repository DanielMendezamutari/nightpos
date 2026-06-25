# SETTLEMENT_ADJUSTMENTS_ENGINE — Fase 1 Backend (Implementación)

**Fecha:** 2026-06-21  
**Estado:** Completado — Fase 1  
**Diseño:** `SETTLEMENT_ADJUSTMENTS_ENGINE_AUDIT.md`

---

## Resumen

Se implementó la **Fase 1** del motor de ajustes de liquidaciones:

- Columnas `gross_amount`, `adjustments_total`, `net_amount` en `staff_settlements`
- Tabla `staff_settlement_adjustments`
- `SettlementAdjustmentEngine` — limpieza única solo chicas (≥100 Bs → −10 Bs)
- `SettlementTotalsCalculator` — recálculo integrado en generación y detalle
- `total_amount` sincronizado con `net_amount` (compatibilidad API/caja existente)
- API detalle expone `gross_amount`, `net_amount`, `adjustments[]`

**Fuera de alcance Fase 1:** multas, descuento manual, ticket al pagar (Fases 2–4).

---

## Migración

`2026_06_21_100091_settlement_adjustments_engine_phase1.php`

- `staff_settlements`: `gross_amount`, `adjustments_total`, `net_amount`
- `staff_settlement_adjustments`: ajustes con `dedup_key` único (`ssa_dedup_unique`)
- Backfill: `gross_amount = net_amount = total_amount`, `adjustments_total = 0`

**Config:** `config/nightpos.php` → `girl_unique_cleaning.threshold` (100), `amount` (10).

---

## Servicios

| Clase | Rol |
|-------|-----|
| `SettlementAdjustmentEngine` | Sync `CLEANING_DEDUCTION` en settlements GIRL PENDING |
| `SettlementTotalsCalculator` | gross + ajustes → net; actualiza header |

**Dedup limpieza:** `cleaning:{shift_id}:{cash_session_id|0}:{staff_user_id}` — no repite en regeneración ni corte parcial post-PAID.

**Orden Fase 1:** Bruto → Limpieza → Neto (sin multas/descuento manual aún).

---

## Integración

- `EloquentStaffSettlementRepository::recalculateTotal()` delega en `SettlementTotalsCalculator`
- `findById()` recalcula settlements PENDING antes de responder
- `SettlementMapper` + `GetSettlementUseCase` incluyen campos y `adjustments`

---

## Tests

`tests/Feature/Api/V1/SettlementAdjustmentsEnginePhase1Test.php` — **6 passed**

1. Chica 80 Bs → sin limpieza  
2. Chica 100 Bs → limpieza −10, neto 90  
3. Regenerar no duplica ajuste  
4. Corte parcial no vuelve a cobrar limpieza  
5. Garzón sin limpieza chica  
6. API detalle expone gross/net/adjustments  

Regresión: `PartialSettlementsTest`, `SettlementsPhase14Test`, `SettlementsPhase16Test`, `CleaningSettlementsTest`, `SettlementPaymentMethodTest` — OK.

---

## Nota despliegue

Si una migración previa falló a medias (columnas sin tabla de ajustes), ejecutar rollback manual o completar:

```sql
-- Solo si staff_settlement_adjustments no existe
-- Re-ejecutar migrate o crear tabla manualmente
```

En entorno limpio (`RefreshDatabase` / migrate fresh) aplica sin conflictos.

---

## Próximo paso

**Fase 2:** ✅ Completada — ver `SETTLEMENT_ADJUSTMENTS_ENGINE_PHASE2_FINES_REPORT.md`

**Fase 3:** descuento manual (`MANUAL_DISCOUNT`).
