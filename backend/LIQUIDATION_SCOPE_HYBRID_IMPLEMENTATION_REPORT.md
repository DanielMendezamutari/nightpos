# Reporte de Implementación: Scope Híbrido de Liquidaciones (Backend)

**Fecha:** 2026-07-08
**Estado:** Implementado y validado
**Alcance:** Ajuste de scope de liquidaciones bajo modelo híbrido (Modelo C)
**Restricciones respetadas:**

- No se tocó DocumentSequence.
- No se tocó PWA.
- No se tocó agente de impresión.
- No se modificó `dist`.
- No se hizo commit ni push.

## Objetivo funcional implementado

Se implementó el ajuste de alcance para que:

1. Cajera (`scope = my_cash_session`) vea y pague liquidaciones por `cash_session_id`, sin excluir por `official_shift_id`.
2. Admin/owner (`scope = shift`) mantenga vista por `official_shift_id`.
3. Histórico siga por `official_shift_id` (sin cambios funcionales de este ajuste).
4. Cierre de caja valide pendientes por caja (`cash_session_id`) aunque existan liquidaciones de esa caja en otro turno.
5. Cierre de turno mantenga validación por `official_shift_id`.

## Cambios aplicados

## 1) Scope de cajera por caja (session-primary)

Archivo principal:

- `backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php`

Ajustes clave:

- `getCurrentShiftOverview(...)`
  - Ahora permite `officialShiftId` nullable.
  - Si hay `cashSessionId`, el filtro principal es `cash_session_id` (mediante `applyCashSessionSettlementScope`).
  - En ese caso no se excluye por `official_shift_id`.
  - Si no hay `cashSessionId`, mantiene filtro por `official_shift_id`.

- `countPendingSettlements(...)`, `sumPendingSettlementAmount(...)`, `countGeneratedSettlements(...)`, `settlementScopeSummary(...)`
  - Mismo patrón híbrido:
    - con `cashSessionId`: scope por sesión,
    - sin `cashSessionId`: scope por turno.

- `countShiftSources(...)` y `countUnsettledShiftSources(...)`
  - Ajustadas para operar por caja cuando aplica `my_cash_session`.
  - Mantienen lógica por turno para scope admin/shift.

- `cashSessionHasActivity(...)`
  - Para scope de caja, permite resolver actividad por sesión sin depender de un único `official_shift_id`.

- `applyCashSessionSettlementScope(...)`
  - Se mantiene como filtro de caja, documentado explícitamente para el nuevo comportamiento híbrido.

## 2) Contexto enriquecido en endpoint current-shift

Archivos:

- `backend/app/Application/StaffSettlement/UseCases/GetCurrentShiftSettlementsUseCase.php`
- `backend/app/Application/StaffSettlement/Support/SettlementOperationalContextBuilder.php`

Se devuelve en `context` (scope `my_cash_session`) la metadata requerida:

- `cash_session_id`
- `session_official_shift_id`
- `settlement_official_shift_ids` (lista de turnos incluidos en las liquidaciones devueltas)

Además:

- `GetCurrentShiftSettlementsUseCase` calcula `settlement_official_shift_ids` desde la lista final de settlements.
- En respuestas vacías mantiene estructura consistente.

## 3) Resolver de scope

Archivo:

- `backend/app/Application/StaffSettlement/Services/SettlementShiftScopeResolver.php`

Ajuste clave:

- En `my_cash_session`, la detección de actividad de caja (`cashSessionHasActivity`) dejó de depender estrictamente del `sessionShiftId` y pasó a validarse por sesión.

## 4) Contrato de repositorio actualizado

Archivo:

- `backend/app/Domain/StaffSettlement/Repositories/StaffSettlementRepositoryInterface.php`

Se ajustaron firmas para permitir `officialShiftId` nullable en métodos usados por scope híbrido:

- `getCurrentShiftOverview`
- `cashSessionHasActivity`
- `countShiftSources`
- `countUnsettledShiftSources`
- `countPendingSettlements`
- `sumPendingSettlementAmount`
- `countGeneratedSettlements`
- `settlementScopeSummary`

## 5) Impacto en close-check

- **Cierre de caja** (`cash/session/current/close-check`): ahora considera pendientes por `cash_session_id` aunque estén en otro `official_shift_id`.
- **Cierre de turno** (`shifts/current/close-check`): mantiene validación por `official_shift_id` (sin migrar a session scope).

## Validación por pruebas

Suite ejecutada:

- `php artisan test tests/Feature/Api/V1/SettlementCashSessionScopeTest.php --testdox`

Resultado:

- **17 tests PASS**
- **167 assertions PASS**

Cobertura confirmada para los escenarios requeridos:

1. Cajera ve settlement de su caja aunque `official_shift_id` difiera.
2. Admin sigue filtrando por turno.
3. Cajera no ve settlements de otra caja.
4. Girls no se rompen en scope cajera.
5. Garzones con comisión 0 siguen apareciendo.
6. Cierre de caja detecta pendientes de su sesión.
7. Cierre de turno mantiene validación por turno oficial.

## Riesgos y notas

- Se priorizó cambio mínimo en capa de liquidaciones y close-check relacionado.
- No se alteró flujo de pagos, auditoría de pagos, ni secuencias documentales.
- El contexto híbrido agregado permite que frontend explique claramente por qué una caja incluye liquidaciones de más de un turno.

## Conclusión

La implementación backend del modelo híbrido quedó operativa y validada:

- Cajera opera por caja.
- Admin/owner mantienen gobierno por turno.
- Cierres respetan el doble contexto sin romper histórico por `official_shift_id`.
