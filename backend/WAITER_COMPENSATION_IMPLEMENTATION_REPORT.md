# WAITER COMPENSATION IMPLEMENTATION REPORT

Fecha: 2026-07-03
Estado: Implementado (Fase 1 y Fase 2)
Alcance: Backend liquidaciones de garzones

## Objetivo implementado

Se implemento compensacion de garzones con dos modos operativos:

- AUTO_PERCENT: usa porcentaje snapshot del item de venta.
- MANUAL: exige asignacion manual antes de pago.

Tambien se mantuvo la trazabilidad y compatibilidad con pago en caja, ticket y emision de eventos SSE.

## Cambios realizados

## 1) Persistencia

Se agrego migracion:

- database/migrations/2026_07_02_110000_add_waiter_compensation_fields_to_staff_settlements.php

Campos nuevos en staff_settlements:

- compensation_mode
- compensation_source
- manual_amount_input
- compensation_locked_at
- compensation_locked_by_user_id
- compensation_notes

Se actualizaron fillable/casts en StaffSettlementModel.

## 2) Generacion de liquidaciones WAITER

Archivo: app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php

Cambios:

- Ya no depende de waiter_commission_amount_snapshot > 0 para crear item WAITER.
- Ahora crea settlement/item WAITER_COMMISSION siempre que el sale_item tenga waiter asociado.
- Define compensation_mode en generacion:
  - AUTO_PERCENT cuando waiter_commission_percent_snapshot > 0
  - MANUAL cuando waiter_commission_percent_snapshot <= 0 o null
- Define compensation_source:
  - PROFILE_PERCENT en AUTO_PERCENT
  - REQUIRES_MANUAL_INPUT en MANUAL
- Expone campos de compensacion en mapSettlementSummary.
- Expone requires_manual_amount para frontend (MANUAL sin manual_amount_input).

## 3) Endpoint para asignacion manual

Nuevos archivos:

- app/Http/Requests/Api/V1/Settlement/UpdateManualCompensationRequest.php
- app/Application/StaffSettlement/UseCases/UpdateManualCompensationUseCase.php

Integracion:

- app/Http/Controllers/Api/V1/SettlementController.php
- routes/api.php
- app/Infrastructure/Providers/NightPosServiceProvider.php

Endpoint:

- PATCH /api/v1/settlements/{id}/manual-compensation

Reglas:

- Requiere permiso settlements.pay.
- Solo WAITER.
- Solo status PENDING.
- Solo compensation_mode MANUAL.
- amount >= 0.
- Guarda manual_amount_input y lock metadata.
- Registra ajuste tecnico MANUAL_COMPENSATION para cuadrar net_amount a monto manual.
- Recalcula totales con SettlementTotalsCalculator.
- Registra auditoria SETTLEMENT_MANUAL_COMPENSATION_SET.

## 4) Guarda de pago

Archivo: app/Application/StaffSettlement/UseCases/MarkSettlementPaidUseCase.php

Cambio:

- Si settlement WAITER esta en MANUAL y manual_amount_input es null, bloquea pago con error de dominio.

## 5) Contrato de salida DTO

Archivo: app/Application/StaffSettlement/Support/SettlementMapper.php

Se agregaron campos:

- compensation_mode
- compensation_source
- manual_amount_input
- compensation_locked_at
- compensation_locked_by_user_id
- compensation_notes
- requires_manual_amount

## Validacion

Pruebas ejecutadas:

- php artisan test tests/Feature/Api/V1/SettlementsPhase14Test.php

Resultado:

- 12 tests PASS
- 160 assertions

Incluye nuevos casos:

- Genera WAITER con modo MANUAL cuando porcentaje = 0.
- Bloquea mark-paid sin monto manual.
- Permite PATCH manual-compensation y luego pago exitoso.

## Compatibilidad y no regresion

Se preservo:

- Pago de liquidacion con egreso de caja.
- Ticket y correlativo via DocumentSequenceService.
- Emision de eventos settlement.paid y cash.movement.created.
- Logica GIRL y CLEANING sin cambios funcionales.

## Pendientes recomendados

- Agregar tests dedicados para historial y current-shift validando nuevos campos de compensacion en distintos roles.
- Agregar validacion de conflicto de asignacion manual concurrente (optimistic lock/version) si se habilita alta concurrencia.
