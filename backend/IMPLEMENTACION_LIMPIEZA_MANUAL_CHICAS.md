# Implementacion — Limpieza Manual para Chicas (Backend)

Fecha: 2026-07-10
Estado: Implementado y validado

## Decision funcional aplicada

Se elimina la aplicacion automatica del cobro de limpieza para liquidaciones tipo GIRL.

- Se mantiene el tipo de ajuste `CLEANING_DEDUCTION` para compatibilidad historica y de reportes.
- El cobro de limpieza pasa a ser un ajuste manual dedicado (set, update, remove).

## Endpoint implementado

- Metodo: PATCH
- Ruta: /api/v1/settlements/{id}/cleaning-deduction
- Controlador: SettlementController::updateCleaningDeduction
- Request: UpdateSettlementCleaningDeductionRequest
- Use case: UpdateSettlementCleaningDeductionUseCase
- Servicio de dominio: SettlementManualCleaningService

Payload:

```json
{
  "amount": 20
}
```

Regla de remocion:

- Si amount = 0, se elimina el registro de limpieza existente y se recalculan totales.

## Permiso utilizado

No se creo permiso nuevo.

- Permiso requerido: settlements.pay
- Middleware: nightpos.permission:settlements.pay

## Regla eliminada

Se desactiva la aplicacion automatica durante recalculo:

- Se removio la ejecucion efectiva de SettlementAdjustmentEngine::syncCleaningDeduction(...).
- El metodo syncCleaningDeduction queda en no-op para evitar recreacion automatica.

## Validaciones implementadas

Validaciones de request:

- amount obligatorio
- numerico
- minimo 0

Validaciones de dominio (SettlementManualCleaningService):

- Solo settlement_type = GIRL
- Solo status = PENDING
- amount >= 0
- amount <= gross_amount
- El neto resultante no puede quedar negativo
- Evita duplicados: un unico CLEANING_DEDUCTION por settlement (dedup key manual)

## Auditoria implementada

El use case registra auditoria de negocio con before/after:

- Acciones:
  - settlement.cleaning_deduction.set
  - settlement.cleaning_deduction.updated
  - settlement.cleaning_deduction.removed
- Se registra monto previo y nuevo, ajuste previo/posterior y totales previos/posteriores.

## Compatibilidad preservada

- Caja: mantiene impacto via adjustments_total/net_amount.
- Reportes: CLEANING_DEDUCTION sigue apareciendo en resumen gerencial de ajustes.
- Historial e impresion: no se rompen contratos existentes al conservar tipo de ajuste.

## Archivos backend modificados

- app/Application/StaffSettlement/Services/SettlementTotalsCalculator.php
- app/Application/StaffSettlement/Services/SettlementAdjustmentEngine.php
- app/Application/StaffSettlement/Services/SettlementManualCleaningService.php (nuevo)
- app/Application/StaffSettlement/UseCases/UpdateSettlementCleaningDeductionUseCase.php (nuevo)
- app/Http/Requests/Api/V1/Settlement/UpdateSettlementCleaningDeductionRequest.php (nuevo)
- app/Domain/StaffSettlement/Exceptions/StaffSettlementDomainException.php
- app/Http/Controllers/Api/V1/SettlementController.php
- routes/api.php

## Pruebas ejecutadas

Suites actualizadas al modelo manual (sin auto-aplicacion):

- tests/Feature/Api/V1/SettlementAdjustmentsEnginePhase1Test.php
- tests/Feature/Api/V1/SettlementAdjustmentsEnginePhase2FinesTest.php
- tests/Feature/Api/V1/SettlementsPhase16Test.php
- tests/Feature/Api/V1/SettlementPaymentAuditTest.php
- tests/Feature/Api/V1/ReportsTest.php

Resultado: todas en verde en ejecucion local con PHP de XAMPP.

## Validacion contra MySQL real (DB nigtpos)

Se ejecuto script transaccional con rollback para no persistir cambios reales:

- Script temporal: C:/xampp/htdocs/nightpos/_tmp_manual_cleaning_real_db.php
- Conexion: MySQL real nigtpos
- Settlement probado: id 23 (GIRL, PENDING)

Snapshots observados:

1) before
- gross_amount: 80.00
- adjustments_total: 0.00
- net_amount: 80.00
- cleaning_rows: []

2) after_twenty (apply 20)
- adjustments_total: -20.00
- net_amount: 60.00
- cleaning_rows: [{ amount: -20.00, dedup_key: manual_cleaning:23 }]

3) after_twenty_recalc
- Se mantiene igual (no alteracion inesperada)

4) after_zero (apply 0)
- adjustments_total: 0.00
- net_amount: 80.00
- cleaning_rows: []

5) after_zero_recalc
- cleaning_rows sigue []
- No se recrea CLEANING_DEDUCTION automaticamente

Conclusion: comportamiento manual confirmado en MySQL real sin cambios persistentes (rollback aplicado).
