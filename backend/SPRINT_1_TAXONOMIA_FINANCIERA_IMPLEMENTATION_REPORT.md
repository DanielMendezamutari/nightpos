# Sprint 1 — Taxonomía financiera estructurada (Backend)

**Fecha:** 2026-07-10
**Estado:** Implementado
**Fuente oficial funcional:** `FINANCIAL_ARCHITECTURE_MASTER.md`
**Fuente oficial técnica:** `FINANCIAL_IMPLEMENTATION_BLUEPRINT.md`

## 1. Objetivo ejecutado

Implementar la taxonomía financiera estructurada de `cash_movements` sin rediseñar todavía el dashboard de Caja, eliminando la dependencia de texto libre para clasificar movimientos nuevos y dejando backfill controlado para históricos.

## 2. Auditoría previa realizada

Se auditó la estructura real previa de:

- `cash_movements`
- `cash_movement_reasons`
- `source_type`
- `cash_movement_reason_id`
- descripciones históricas
- movimientos de liquidaciones por rol

Resultado previo en MySQL real `nigtpos`:

- `cash_movements` tenant 2 / branch 2: **124** registros
- ingresos históricos de cobro de comanda: `source_type = NULL`, clasificados antes por `description`
- movimientos de liquidaciones: mezcla entre `source_type = STAFF_SETTLEMENT` y descripciones históricas como `Pago chicas ...` o `CENA — RANDI`
- razones reales relevantes:
  - `Otro ingreso`
  - `Pago chicas`
  - `PAGO LIMPIEZA`
  - `PEDIDO DE PUNTO FRIO`
  - `COMPRA DE HIELO`
  - `PAGO DJ`
  - `Pago cajera`
  - `CENA`
  - `COMISION DE TAXI`

## 3. Categorías definitivas implementadas

### movement_family

- `SALE`
- `MANUAL`
- `SETTLEMENT`
- `EXPENSE`
- `ADJUSTMENT`

### movement_category

- `SALE_COLLECTION`
- `DIRECT_SALE_COLLECTION`
- `BRACELET_COLLECTION`
- `ROOM_SERVICE_COLLECTION`
- `SHOW_COLLECTION`
- `MANUAL_INCOME`
- `SETTLEMENT_GIRL_PAYMENT`
- `SETTLEMENT_WAITER_PAYMENT`
- `SETTLEMENT_CLEANING_PAYMENT`
- `OPERATING_EXPENSE`
- `PURCHASE`
- `OTHER_INCOME`
- `OTHER_EXPENSE`

## 4. Implementación técnica aplicada

## 4.1 Dominio

Se agregaron value objects:

- `app/Domain/Cash/ValueObjects/CashMovementFamily.php`
- `app/Domain/Cash/ValueObjects/CashMovementCategory.php`

## 4.2 Servicios nuevos

Se agregaron:

- `app/Application/Cash/Services/CashMovementTaxonomyResolver.php`
- `app/Application/Cash/Services/CashMovementTaxonomyBackfillService.php`

### Resolver

Orden efectivo de clasificación usado:

1. `source_type`
2. `source_id`
3. `cash_movement_reason_id`
4. `settlement_type` relacionado
5. `description` como fallback histórico
6. fallback explícito por `movement_type`

## 4.3 Persistencia

Se creó la migración:

- `backend/database/migrations/2026_07_10_120000_add_financial_taxonomy_to_cash_movements_and_reasons.php`

### Cambios de esquema

#### `cash_movements`
- nuevo `movement_family` nullable
- nuevo `movement_category` nullable
- índices:
  - `tenant_id, branch_id, movement_family`
  - `tenant_id, branch_id, movement_category`
  - `cash_session_id, movement_category`

#### `cash_movement_reasons`
- nuevo `default_movement_family` nullable
- nuevo `default_movement_category` nullable

### Rollback

La migración elimina únicamente:

- columnas nuevas
- índices nuevos

No elimina ni altera registros históricos.

## 4.4 Creación centralizada de movimientos

No se creó un big-bang refactor de use cases.

La centralización quedó implementada en el punto común real del sistema:

- `CashSessionRepositoryInterface::addMovement()`
- `EloquentCashSessionRepository::addMovement()`

Toda creación de movimiento nuevo pasa por este punto y recibe taxonomía estructurada desde `CashMovementTaxonomyResolver`.

## 4.5 Flujos actualizados

Se cubrieron estos flujos:

- cobro de comanda
- venta directa
- pago chica
- pago garzón
- pago limpieza
- movimientos manuales
- gastos operativos
- compras históricas por reason/description
- servicios con ingreso de caja:
  - manillas
  - piezas
  - shows

### Ajuste importante

`ChargeOrderUseCase` ahora también guarda `sourceType = SALE` y `sourceId = sale->id` para movimientos nuevos de cobro de comanda.

`CreateDirectSaleUseCase` normaliza también `sourceType = SALE`.

## 4.6 Compatibilidad preservada

Se mantuvieron:

- `description`
- `notes`
- `cash_movement_reason_id`
- `source_type`
- `source_id`
- `movement_type`
- `payment_method`

No se eliminaron campos del payload actual.

## 4.7 Endpoints / payloads extendidos

Se exponen `movement_family` y `movement_category` en:

- current cash session
- movimientos de sesión
- detalle administrativo de caja
- payload de movimiento imprimible
- reportes internos que ya devuelven lista de movimientos (`CashCloseReportSectionsBuilder`)

## 5. Archivos modificados

### Nuevos

- `backend/app/Domain/Cash/ValueObjects/CashMovementFamily.php`
- `backend/app/Domain/Cash/ValueObjects/CashMovementCategory.php`
- `backend/app/Application/Cash/Services/CashMovementTaxonomyResolver.php`
- `backend/app/Application/Cash/Services/CashMovementTaxonomyBackfillService.php`
- `backend/database/migrations/2026_07_10_120000_add_financial_taxonomy_to_cash_movements_and_reasons.php`

### Modificados

- `backend/app/Infrastructure/Persistence/Eloquent/Models/CashMovementModel.php`
- `backend/app/Infrastructure/Persistence/Eloquent/Models/CashMovementReasonModel.php`
- `backend/app/Domain/Cash/Entities/CashMovement.php`
- `backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentCashSessionRepository.php`
- `backend/app/Application/Cash/Support/CashMapper.php`
- `backend/app/Application/Cash/Services/CashPrintPresenter.php`
- `backend/app/Application/Reports/Services/CashCloseReportSectionsBuilder.php`
- `backend/app/Application/Sale/UseCases/ChargeOrderUseCase.php`
- `backend/app/Application/Sale/UseCases/CreateDirectSaleUseCase.php`
- `backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentCashMovementReasonRepository.php`
- `backend/app/Infrastructure/Providers/NightPosServiceProvider.php`
- `backend/tests/Feature/Api/V1/CashMovementFinancialTaxonomyTest.php`

## 6. Resultado del backfill en MySQL real

### Conteo total pre-migración
- `cash_movements` tenant 2 / branch 2: **124**

### Conteo total post-migración
- `cash_movements` tenant 2 / branch 2: **124**

### Integridad de taxonomía post-migración
- movimientos sin `movement_family` o `movement_category`: **0**

### Agrupación resultante observada

- `EXPENSE / OPERATING_EXPENSE` → 27 registros → `1828.00`
- `EXPENSE / OTHER_EXPENSE` → 6 registros → `3891.00`
- `EXPENSE / PURCHASE` → 5 registros → `128.00`
- `SALE / SALE_COLLECTION` → 66 registros → `13090.00`
- `SETTLEMENT / SETTLEMENT_CLEANING_PAYMENT` → 1 registro → `50.00`
- `SETTLEMENT / SETTLEMENT_GIRL_PAYMENT` → 16 registros → `2568.00`
- `SETTLEMENT / SETTLEMENT_WAITER_PAYMENT` → 3 registros → `243.00`

### Verificación de ventas no clasificadas como ingreso manual

Consulta post-validación:

- movimientos `INCOME` con descripción `Cobro comanda%` o `Venta directa%` y `movement_family <> SALE`
- resultado: **0 registros**

### Verificación de pagos de liquidaciones por rol

Validado en base real:

- movimientos con `source_type = STAFF_SETTLEMENT` y `settlement_type = GIRL` → `SETTLEMENT_GIRL_PAYMENT`
- movimientos con `source_type = STAFF_SETTLEMENT` y `settlement_type = WAITER` → `SETTLEMENT_WAITER_PAYMENT`
- movimientos con `source_type = STAFF_SETTLEMENT` y `settlement_type = CLEANING` → `SETTLEMENT_CLEANING_PAYMENT`

Además, históricos sin `source_type` como:

- `Pago chicas — ...`
- `PAGO LIMPIEZA`
- descripciones waiter con reason `CENA`

quedaron reclasificados por reason / fallback según las reglas implementadas.

## 7. Tests ejecutados

### Suite nueva Sprint 1

- `tests/Feature/Api/V1/CashMovementFinancialTaxonomyTest.php`
- Resultado: **11 PASS / 125 assertions**

Cobertura:

1. cobro de comanda → `SALE / SALE_COLLECTION`
2. venta directa → `SALE / DIRECT_SALE_COLLECTION`
3. pago chica → `SETTLEMENT / SETTLEMENT_GIRL_PAYMENT`
4. pago garzón → `SETTLEMENT / SETTLEMENT_WAITER_PAYMENT`
5. pago limpieza → `SETTLEMENT / SETTLEMENT_CLEANING_PAYMENT`
6. ingreso manual → `MANUAL / MANUAL_INCOME`
7. gasto operativo → `EXPENSE / OPERATING_EXPENSE`
8. fallback `OTHER_INCOME / OTHER_EXPENSE`
9. backfill sin pérdida + aislamiento tenant/branch
10. compatibilidad de endpoints
11. `expected_cash` sin cambio de cálculo

### Regresión operativa

- `CashApiTest` → PASS
- `ChargeOrderApiTest` → PASS
- `DirectSaleApiTest` → PASS
- `SettlementPaymentCashSessionTest` → PASS
- `SettlementPaymentMethodTest` → PASS
- `AdminCashSessionsTest` → PASS

## 8. Riesgos encontrados

1. **Razones nuevas sin defaults**
- Si se crea una razón nueva y no se le asigna metadata estructurada, la clasificación caerá en fallback `OTHER_INCOME` o `OTHER_EXPENSE`.

2. **Históricos antiguos de comanda**
- Los cobros históricos de comanda quedaron bien por fallback de `description`, pero eso sigue siendo una heurística para registros ya existentes.

3. **Service incomes**
- Se agregaron categorías explícitas para `BRACELET_COLLECTION`, `ROOM_SERVICE_COLLECTION` y `SHOW_COLLECTION`, aunque el alcance mínimo del Sprint no las exigía. Esto mejora trazabilidad pero aumenta el catálogo taxonómico.

4. **No se cambió todavía el summary legado**
- `sumManualMovements()` y `financial_summary` siguen con semántica heredada. La taxonomía ya existe, pero el summary viejo no fue rediseñado en este Sprint por restricción de alcance.

## 9. Qué queda pendiente para Sprint 2

1. reemplazar summaries heredados por summaries oficiales separados:
- `sales_summary`
- `cash_summary`
- `movement_summary`
- `settlement_summary`
- `scope_summary`

2. eliminar heurísticas de texto en summaries legacy donde todavía existan.

3. rebasear fiscalización admin sobre la taxonomía estructurada.

4. rebasear cierres y reportes para usar agregados por categoría/family.

5. empezar transición de frontend para que consuma summaries nuevos en lugar de `financial_summary` monolítico.

## 10. Conclusión

Sprint 1 quedó cumplido dentro del alcance pedido:

- taxonomía financiera estructurada implementada
- backfill histórico ejecutado en MySQL real `nigtpos`
- cero movimientos sin categoría
- compatibilidad de payloads preservada
- cálculos actuales de `expected_cash` intactos
- UI existente funcional con labels legibles mínimos
