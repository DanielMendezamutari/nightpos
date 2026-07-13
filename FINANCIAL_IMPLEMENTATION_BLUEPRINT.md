# Financial Implementation Blueprint — NightPOS

**Fecha:** 2026-07-10
**Estado:** Plano técnico definitivo de implementación financiera
**Fuente funcional oficial:** `FINANCIAL_ARCHITECTURE_MASTER.md`
**Objetivo:** traducir la arquitectura funcional financiera a una arquitectura técnica implementable por fases, sin escribir todavía código de negocio.

---

## 0. Principio rector de este blueprint

Este documento no redefine el modelo funcional. Lo operacionaliza técnicamente.

A partir de este punto:

- `FINANCIAL_ARCHITECTURE_MASTER.md` define la verdad funcional.
- `FINANCIAL_IMPLEMENTATION_BLUEPRINT.md` define la verdad técnica de migración.

Toda decisión técnica debe respetar estas reglas:

1. No romper la operación actual durante la transición.
2. No hacer big-bang refactor.
3. Mantener compatibilidad temporal de payloads.
4. Introducir primero taxonomía estructurada y luego nuevos summaries.
5. No rediseñar UI antes de estabilizar contratos backend.
6. No cambiar la política caja vs turno hasta la fase explícita de decisión.

---

## 1. Arquitectura Backend

## 1.1 Capas del sistema que cambiarán

### Capa Application

Será la más impactada.

Módulos que cambiarán:

- `Application/Cash`
- `Application/Sale`
- `Application/StaffSettlement`
- `Application/Reports`
- `Application/Printing`

Motivo:

- hoy concentran creación de `cash_movements`, summaries financieros y consolidación de caja/reportes.
- la migración financiera requiere separar explícitamente dominios: ventas, caja física, movimientos y liquidaciones.

### Capa Domain

Cambios moderados pero críticos.

Módulos que cambiarán:

- `Domain/Cash`
- `Domain/Sale`
- `Domain/StaffSettlement`
- `Domain/Reports`

Motivo:

- introducir vocabulario financiero oficial en enums/value objects y contratos de repositorio.

### Capa Infrastructure

Cambios altos en persistencia y mapeo.

Módulos que cambiarán:

- `Infrastructure/Persistence/Eloquent/Repositories`
- `Infrastructure/Persistence/Eloquent/Models`

Motivo:

- soportar nuevas columnas estructuradas,
- backfill,
- nuevos summaries,
- compatibilidad temporal de queries antiguas y nuevas.

### Capa Presentation

Cambios controlados.

Módulos que cambiarán:

- `Http/Controllers/Api/V1/CashController.php`
- `Http/Controllers/Api/V1/ReportController.php`
- endpoints relacionados a settlement y admin cash sessions.

Motivo:

- exponer payloads nuevos sin romper los actuales.

---

## 1.2 Casos de uso que se crearán

## Fase base: taxonomía financiera

### Cash / Financial taxonomy

- `ClassifyCashMovementUseCase`
  - propósito: clasificar y validar una creación puntual de movimiento.
- `BackfillCashMovementTaxonomyUseCase`
  - propósito: backfill histórico por lotes o comando interno.
- `GetCashMovementTaxonomyDictionaryUseCase`
  - propósito: exponer labels/metadatos de families/categories si hiciera falta.

## Fase summaries separados

- `GetCurrentFinancialSnapshotUseCase`
  - payload compuesto de `sales_summary`, `cash_summary`, `movement_summary`, `settlement_summary`, `scope_summary`.
- `GetCashSessionFinancialSnapshotUseCase`
  - detalle estructurado de una sesión.
- `GetAdminCashSessionsFinancialSummaryUseCase`
  - agregados admin por nuevo modelo.
- `GetManagerialFinancialSummaryUseCase`
  - unificación futura del reporte gerencial sobre taxonomía oficial.

## Fase reporting

- `BuildSalesSummaryService`
- `BuildCashSummaryService`
- `BuildMovementSummaryService`
- `BuildSettlementSummaryService`
- `BuildScopeSummaryService`

Nota:
- pueden ser use cases o services; la decisión recomendada es service de ensamblado + use case del endpoint.

---

## 1.3 Servicios que desaparecerán

No deben desaparecer de inmediato. Deben eliminarse solo al final de la migración.

### Candidatos a desaparición final

- `CashSessionFinancialSummaryBuilder`
  - desaparecer como builder monolítico único.
- partes de `CashCloseReportSectionsBuilder`
  - hoy mezcla ventas, caja, movimientos y liquidaciones en un mismo ensamblado.
- lógica financiera difusa en `ManagerialReportAssemblerService`
  - debe rebasarse sobre summaries oficiales.

### Regla

En transición, estos servicios no se eliminan. Se envuelven o se desacoplan.

---

## 1.4 Servicios que deberán dividirse

### `CashSessionFinancialSummaryBuilder`

Hoy mezcla:

- ventas por método,
- ingresos manuales,
- egresos,
- expected cash,
- expected qr/card,
- movement totals,
- cash difference.

Debe dividirse en:

- `SalesSummaryBuilder`
- `CashPhysicalSummaryBuilder`
- `MovementSummaryBuilder`
- `SettlementSummaryBuilder`
- `FinancialScopeSummaryBuilder`

### `CashCloseReportSectionsBuilder`

Hoy agrupa:

- sales count / ticket promedio,
- payment method stats,
- movements,
- settlements paid,
- adjustments,
- pending summary.

Debe dividirse para que el cierre consuma los mismos summaries canónicos del resto del sistema.

### `ManagerialReportAssemblerService`

Debe dejar de leer datos semánticamente mixtos y ensamblar sobre:

- `sales_summary`
- `cash_summary`
- `movement_summary`
- `settlement_summary`
- `managerial_summary`

---

## 1.5 Repositorios que se modificarán

### `CashSessionRepositoryInterface`

Cambios:

- ampliar `addMovement()` para recibir taxonomía financiera estructurada.
- agregar queries de summaries por categoría/family.
- agregar métodos de backfill y conteo de integridad si se decide dejarlos en repo.

### `EloquentCashSessionRepository`

Cambios:

- persistir `movement_family` y `movement_category`.
- reemplazar `sumManualMovements()` basada en descripción por agregados estructurados.
- incorporar summaries por categoría/family y por payment_method.

### `SaleRepositoryInterface` / `EloquentSaleRepository`

Cambios:

- probablemente mínimos en Sprint 1.
- a partir de Sprint 2/3 alimentar `sales_summary` canónico.

### `StaffSettlementRepositoryInterface` / `EloquentStaffSettlementRepository`

Cambios:

- exponer agregados por rol más claros para `settlement_summary`.
- no tocar su lógica principal en Sprint 1 salvo consumo de taxonomía al pagar.

### `ReportReadRepositoryInterface` / `EloquentReportReadRepository`

Cambios:

- rebase progresivo sobre taxonomía estructurada.
- transición sin romper reportes actuales.

---

## 1.6 DTOs que cambiarán

## DTOs actuales a extender

### Cash

- `CashDto`
- `CloseCashSessionInput`
- `RegisterCashMovementInput`
- `ListCashSessionsAdminInput`

### Sale

- `ChargeOrderInput`
- `DirectSaleInput`
- `SaleDto`

### Reports

- `ReportsDto`

### Settlements

- `StaffSettlementDto`

## DTOs nuevos recomendados

- `FinancialMovementTaxonomyDto`
- `SalesSummaryDto`
- `CashSummaryDto`
- `MovementSummaryDto`
- `SettlementSummaryDto`
- `ScopeSummaryDto`
- `ManagerialSummaryDto`
- `FinancialSnapshotDto`

---

## 1.7 Mappers que deberán adaptarse

### Cash

- `CashMapper`
- `AdminCashSessionMapper`

### Sale

- `SaleMapper`

### Settlements

- `SettlementMapper`

### Reports / print payloads

- `CashClosePrintPayloadEnricher`
- `ShiftClosePrintPayloadEnricher`
- `CashPrintPresenter`
- `PrintTicketContentBuilder`

Regla:
- ningún mapper debe seguir presentando “manual income” o “manual expense” sobre heurísticas por descripción una vez terminada la migración.

---

## 1.8 Eventos financieros oficiales

No todos deben implementarse en Sprint 1, pero sí fijarse ya.

### Ventas

- `financial.sale.collected`
- `financial.direct_sale.collected`

### Caja

- `financial.cash_session.opened`
- `financial.cash_session.closed`
- `financial.cash_session.force_closed`

### Movimientos

- `financial.movement.created`
- `financial.movement.reclassified`

### Liquidaciones

- `financial.settlement.generated`
- `financial.settlement.paid`
- `financial.settlement.adjusted`

### Reportes / control

- `financial.snapshot.rebuilt`

Nota:
- los eventos actuales SSE (`cash.movement.created`, `sale.created`, `settlement.paid`) pueden convivir con estos como semántica futura, sin introducirlos todavía en frontend.

---

## 1.9 Dependencias entre módulos

### Dependencias críticas

- `Sale` depende de `Cash` para registrar ingreso.
- `StaffSettlement` depende de `Cash` para registrar egreso.
- `Reports` depende de `Sale`, `Cash`, `StaffSettlement`, `RoomService`, `Bracelet`, `Show`.
- `Printing` depende de `Reports`, `Cash`, `StaffSettlement`, `Sale`.
- `Shift` depende de resúmenes de `Cash` y `Reports`.

### Restricción técnica

La taxonomía de `cash_movements` debe implementarse primero porque:

- afecta venta,
- afecta liquidaciones,
- afecta reportes,
- afecta impresión,
- afecta fiscalización.

---

## 2. Contrato oficial de APIs

## 2.1 Regla de transición

Los endpoints actuales no se eliminan.

Se introducirán payloads estructurados nuevos dentro de las respuestas actuales, preservando campos existentes.

## 2.2 Contrato oficial base: Financial Snapshot

```json
{
  "data": {
    "sales_summary": {
      "total_sales": "2020.00",
      "sales_count": 12,
      "average_ticket": "168.33",
      "sales_by_method": {
        "cash": "560.00",
        "qr": "1460.00",
        "card": "0.00",
        "mixed": "0.00"
      },
      "sales_by_hour": [
        { "bucket": "13:00", "sales_count": 2, "revenue_total": "160.00" }
      ],
      "products_sold_count": 18,
      "services_sold_count": 0
    },
    "cash_summary": {
      "opening_cash": "54.00",
      "cash_income_sales": "560.00",
      "cash_income_manual": "0.00",
      "cash_expense_settlements": "0.00",
      "cash_expense_operational": "230.00",
      "cash_expense_purchases": "0.00",
      "expected_cash": "384.00",
      "counted_cash": null,
      "cash_difference": null,
      "cash_available_for_settlements": "384.00",
      "cash_available_for_expenses": "384.00"
    },
    "movement_summary": {
      "movement_income_count": 12,
      "movement_expense_count": 5,
      "movement_income_total": "2020.00",
      "movement_expense_total": "230.00",
      "movement_total_by_category": {
        "SALE_COLLECTION": "560.00",
        "DIRECT_SALE_COLLECTION": "0.00",
        "MANUAL_INCOME": "0.00",
        "OPERATING_EXPENSE": "230.00"
      },
      "movement_total_by_payment_method": {
        "cash": { "income": "560.00", "expense": "230.00" },
        "qr": { "income": "1460.00", "expense": "0.00" },
        "card": { "income": "0.00", "expense": "0.00" }
      },
      "movement_last": {
        "id": 150,
        "movement_type": "INCOME",
        "movement_family": "SALE",
        "movement_category": "SALE_COLLECTION",
        "amount": "280.00",
        "payment_method": "QR",
        "description": "Cobro comanda C-0072",
        "created_at": "2026-07-09 22:04:25"
      },
      "movement_max": {
        "id": 134,
        "movement_type": "EXPENSE",
        "movement_family": "SETTLEMENT",
        "movement_category": "SETTLEMENT_GIRL_PAYMENT",
        "amount": "710.00"
      }
    },
    "settlement_summary": {
      "pending_waiter_count": 5,
      "pending_waiter_amount": "22.00",
      "pending_girl_count": 7,
      "pending_girl_amount": "730.00",
      "pending_cleaning_count": 0,
      "pending_cleaning_amount": "0.00",
      "pending_total_count": 12,
      "pending_total_amount": "752.00",
      "paid_today_count": 0,
      "paid_today_amount": "0.00"
    },
    "scope_summary": {
      "scope_type": "cash_session",
      "cash_session_id": 11,
      "session_official_shift_id": 13,
      "open_official_shift_id": 33,
      "included_official_shift_ids": [13, 19, 23, 28],
      "shift_mismatch": true
    }
  }
}
```

## 2.3 `managerial_summary`

```json
{
  "data": {
    "managerial_summary": {
      "gross_revenue": "2020.00",
      "settlements_paid_total": "0.00",
      "operating_expenses_total": "230.00",
      "cash_difference_total": "0.00",
      "net_house_estimated": "1790.00",
      "best_hour_by_revenue": {
        "bucket": "22:00",
        "sales_count": 1,
        "revenue_total": "280.00"
      },
      "top_waiters_by_sales": [
        { "waiter_user_id": 39, "waiter_name": "EMERSON", "sales_total": "440.00" }
      ],
      "top_girls_by_generated_income": [],
      "top_products_by_revenue": []
    }
  }
}
```

## 2.4 Endpoints a adaptar

### Caja operativa

- `GET /cash/session/current`
- `GET /cash/session/current/close-check`
- `GET /cash/sessions/{id}`

### Admin fiscalización

- `GET /admin/cash-sessions`
- `GET /admin/cash-sessions/{id}`
- `GET /admin/cash-sessions/summary`

### Reportes

- `GET /reports/daily`
- `GET /reports/cash`
- `GET /reports/settlements`
- `GET /reports/managerial-daily`

### Turnos

- `GET /shifts/{id}/summary`
- `GET /shifts/current/close-check`

---

## 3. Plan de compatibilidad

## 3.1 Objetivo

Permitir convivencia entre:

- APIs actuales
- payloads nuevos
- DTOs antiguos
- DTOs nuevos

sin romper frontend actual ni impresión actual.

## 3.2 Estrategia general

### Fase de convivencia A

- mantener `financial_summary` actual
- agregar nuevos bloques `sales_summary`, `cash_summary`, `movement_summary`, `settlement_summary`, `scope_summary`

### Fase de convivencia B

- frontend nuevo consume summaries nuevos
- frontend viejo sigue usando campos antiguos

### Fase de convivencia C

- reportes e impresión migran internamente a summaries nuevos
- payload antiguo se mantiene como alias transitorio

### Fase de convivencia D

- una vez migradas todas las pantallas y tickets, marcar payloads viejos como deprecated

## 3.3 Compatibilidad DTO

### Antiguos que siguen viviendo temporalmente

- `financial_summary`
- `sales_by_method`
- `income_total`
- `expense_total`
- `expected_amount`
- `total_manual_income`
- `total_manual_expense`

### Nuevos a introducir sin ruptura

- `movement_family`
- `movement_category`
- `sales_summary`
- `cash_summary`
- `movement_summary`
- `settlement_summary`
- `scope_summary`
- `managerial_summary`

## 3.4 Compatibilidad de impresión

No romper:

- tickets de caja
- tickets de liquidación
- tickets de venta
- reportes imprimibles

Regla:
- la impresión debe seguir funcionando con payload viejo hasta que cada builder sea migrado explícitamente.

---

## 4. Estrategia de migraciones

## 4.1 Tabla `cash_movements`

### Estado
- **cambia**

### Nuevos campos
- `movement_family`
- `movement_category`

### Índices recomendados
- índice por `tenant_id, branch_id, movement_family`
- índice por `tenant_id, branch_id, movement_category`
- índice por `cash_session_id, movement_category`
- opcional: `source_type, source_id`

### Migración
- agregar columnas nullable al inicio
- backfill histórico
- luego endurecer nullability si la transición lo permite

### Backfill
Orden oficial:
1. `source_type`
2. `source_id`
3. `cash_movement_reason_id`
4. `settlement_type` relacionado
5. `description` como último fallback

### Rollback
- eliminar columnas nuevas
- no borrar movimientos históricos

## 4.2 Tabla `cash_movement_reasons`

### Estado
- **puede cambiar**

### Nuevos campos recomendados
- `default_movement_family`
- `default_movement_category`

### Backfill
- mapear razones existentes a categorías nuevas cuando corresponda

### Rollback
- safe, eliminar columnas nuevas

## 4.3 Tabla `cash_sessions`

### Estado
- **no cambia en Sprint 1**

### Futuro posible
- `declared_qr_amount`
- `declared_card_amount`
- `difference_qr_amount`
- `difference_card_amount`
- política caja-turno si se aprobara

## 4.4 Tabla `staff_settlements`

### Estado
- no cambia en Sprint 1
- puede necesitar mejoras de summary en Sprint 4/5, no de esquema inicial

## 4.5 Tablas `sales`, `sale_payments`

### Estado
- no cambian en Sprint 1
- sí cambian sus summaries y mappers en Sprint 2/3

## 4.6 Otras tablas

| Tabla | Sprint 1 | Futuro |
|---|---|---|
| `room_services` | no cambia | quizá summary adicional |
| `bracelets` | no cambia | quizá summary adicional |
| `shows` | no cambia | quizá summary adicional |
| `official_shifts` | no cambia | depende de política final caja-turno |
| `print_jobs` | no cambia | builders sí cambian más adelante |

---

## 5. Refactor del Backend

## 5.1 Archivos nuevos (prioridad estimada)

### Prioridad 1 — Dominio base

- `backend/app/Domain/Cash/ValueObjects/CashMovementFamily.php`
- `backend/app/Domain/Cash/ValueObjects/CashMovementCategory.php`
- `backend/app/Application/Cash/DTOs/FinancialMovementTaxonomyDto.php`
- `backend/app/Application/Cash/Services/CashMovementTaxonomyResolver.php`
- `backend/app/Application/Cash/Services/CashMovementFactory.php`

### Prioridad 2 — Summaries

- `backend/app/Application/Cash/Services/SalesSummaryBuilder.php`
- `backend/app/Application/Cash/Services/CashPhysicalSummaryBuilder.php`
- `backend/app/Application/Cash/Services/MovementSummaryBuilder.php`
- `backend/app/Application/Cash/Services/SettlementSummaryBuilder.php`
- `backend/app/Application/Cash/Services/FinancialScopeSummaryBuilder.php`
- `backend/app/Application/Cash/DTOs/SalesSummaryDto.php`
- `backend/app/Application/Cash/DTOs/CashSummaryDto.php`
- `backend/app/Application/Cash/DTOs/MovementSummaryDto.php`
- `backend/app/Application/Cash/DTOs/SettlementSummaryDto.php`
- `backend/app/Application/Cash/DTOs/ScopeSummaryDto.php`
- `backend/app/Application/Cash/DTOs/FinancialSnapshotDto.php`

### Prioridad 3 — Backfill / tooling

- migración `cash_movements` taxonomía
- posible migración `cash_movement_reasons` defaults
- comando o servicio interno de validación de taxonomía

## 5.2 Archivos a modificar

### Sprint 1 directo

- `backend/app/Infrastructure/Persistence/Eloquent/Models/CashMovementModel.php`
- `backend/app/Infrastructure/Persistence/Eloquent/Models/CashMovementReasonModel.php`
- `backend/app/Domain/Cash/Repositories/CashSessionRepositoryInterface.php`
- `backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentCashSessionRepository.php`
- `backend/app/Application/Cash/UseCases/RegisterCashMovementUseCase.php`
- `backend/app/Application/Sale/UseCases/ChargeOrderUseCase.php`
- `backend/app/Application/Sale/UseCases/CreateDirectSaleUseCase.php`
- `backend/app/Application/StaffSettlement/UseCases/MarkSettlementPaidUseCase.php`
- `backend/app/Application/Cash/Support/CashMapper.php`
- `backend/app/Application/Cash/UseCases/GetCurrentCashSessionUseCase.php`
- `backend/app/Application/Cash/UseCases/GetCashSessionUseCase.php`
- `backend/app/Application/Cash/UseCases/GetCashSessionAdminUseCase.php`
- `backend/app/Application/Cash/UseCases/ListCashSessionsAdminUseCase.php`
- `backend/app/Application/Reports/Services/CashCloseReportSectionsBuilder.php`

### Sprint 2+

- `CashSessionFinancialSummaryBuilder.php`
- `AdminCashSessionMapper.php`
- `ManagerialReportAssemblerService.php`
- `EloquentReportReadRepository.php`
- `ShiftManagerialSummaryBuilder.php`
- print enrichers/presenters

## 5.3 Archivos a eliminar

### Sprint 1
- ninguno

### Eliminables solo al final
- `CashSessionFinancialSummaryBuilder.php` como servicio monolítico
- helpers o alias legacy cuando todas las pantallas hayan migrado

---

## 6. Refactor del Frontend

## 6.1 Nuevos composables

- `useFinancialMovementLabels`
- `useCashFinancialSnapshot`
- `useAdminCashFinancialSnapshot`
- `useSettlementFinancialSummary`

## 6.2 Nuevos stores recomendados

No obligatorios en Sprint 1, pero útiles desde Sprint 3:

- `financialTaxonomyStore`
- `cashSnapshotStore`

## 6.3 Nuevos componentes

### Sprint 1

- `FinancialMovementCategoryChip.vue`
- `FinancialMovementFamilyChip.vue`

### Sprint 3+

- `CashPhysicalSummaryCard.vue`
- `SalesSummaryCard.vue`
- `MovementSummaryCard.vue`
- `SettlementSummaryCard.vue`
- `CashScopeAlert.vue`

## 6.4 Componentes que desaparecerán o perderán responsabilidad

- partes de `CashMovementDialog.vue` con lógica de clasificación implícita
- partes de `nightpos/cash/index.vue` que lean heurísticas legacy directamente

## 6.5 Pantallas que se reestructuran

### Sprint 1

- `frontend/src/pages/nightpos/cash/index.vue` solo adaptación mínima
- `frontend/src/pages/nightpos/finance/cash-sessions/[id].vue` adaptación mínima
- componentes imprimibles y tablas de movimientos: solo agregar labels nuevos

### Sprint 3+

- `frontend/src/pages/nightpos/cash/index.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/index.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/summary.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/by-cashier.vue`
- `frontend/src/pages/nightpos/finance/cash-sessions/by-shift.vue`

---

## 7. Plan de implementación por Sprint

## Sprint 1 — Taxonomía financiera

### Objetivo
Introducir `movement_family` y `movement_category` en `cash_movements` con backfill histórico y creación centralizada de movimientos.

### Módulos afectados
- Cash
- Sale
- StaffSettlement
- Reports mínimos que ya devuelven movimientos

### Archivos afectados
Ver sección 5.2 Sprint 1 directo.

### Riesgos
- clasificación errónea de históricos
- afectar expected cash sin querer
- romper payloads que hoy esperan campos antiguos solamente

### Criterios de aceptación
- ningún movimiento nuevo queda sin categoría
- el conteo de movimientos históricos no cambia
- expected cash sigue igual antes y después
- endpoints actuales siguen funcionando
- movimientos de venta ya no dependen de `description` para clasificación futura

### Pruebas necesarias
- cobro comanda
- venta directa
- pago chica
- pago garzón
- pago limpieza
- ingreso manual
- gasto operativo
- fallback categories
- backfill integridad

## Sprint 2 — Backend summaries oficiales

### Objetivo
Agregar `sales_summary`, `cash_summary`, `movement_summary`, `settlement_summary`, `scope_summary` sin romper `financial_summary`.

### Módulos afectados
- Cash
- Reports
- Admin cash sessions
- Shift summaries

### Riesgos
- duplicación de cálculo durante transición
- inconsistencias entre summary viejo y nuevo

### Criterios de aceptación
- endpoints actuales devuelven payload nuevo y viejo simultáneamente
- summaries oficiales tienen tests de consistencia

### Pruebas necesarias
- comparación viejo vs nuevo
- tenant/branch isolation
- sesión con múltiples shift ids

## Sprint 3 — Caja física

### Objetivo
Rearmar el dominio visual y backend de Caja física sobre `cash_summary`.

### Módulos afectados
- cash current session
- cash close dialog
- print cash close

### Riesgos
- confundir arqueo efectivo con medios no físicos

### Criterios de aceptación
- saldo esperado efectivo claro
- diferencia cash clara
- QR/Tarjeta sin ambigüedad de arqueo

### Pruebas necesarias
- cierres con cash/qr/card
- expected cash invariance

## Sprint 4 — Movimientos

### Objetivo
Rehacer listados, agrupaciones y admin views sobre `movement_family` y `movement_category`.

### Módulos afectados
- movement tables
- admin cash session detail
- movement reports

### Riesgos
- categorías mal mapeadas en históricos

### Criterios de aceptación
- movimientos agrupables por categoría sin heurística de texto

### Pruebas necesarias
- filtros por family/category
- compatibilidad de reason y description

## Sprint 5 — Liquidaciones

### Objetivo
Separar definitivamente pagos al personal dentro del dominio financiero y de reporting.

### Módulos afectados
- settlement pay
- summaries cash/admin
- managerial report

### Riesgos
- doble conteo de egresos
- inconsistencias por rol

### Criterios de aceptación
- pagos a chica/garzón/limpieza aparecen explícitos

### Pruebas necesarias
- role split totals
- settlement payments preserve net logic

## Sprint 6 — Dashboard

### Objetivo
Rediseñar Caja frontend usando summaries oficiales.

### Módulos afectados
- cash dashboard
- supervisor/admin summaries

### Riesgos
- cambio UX fuerte

### Criterios de aceptación
- bloques claros y sin duplicidad conceptual

### Pruebas necesarias
- smoke UI
- fallback labels

## Sprint 7 — Reportes

### Objetivo
Rebase de reportes diarios, cash y gerenciales sobre taxonomía oficial.

### Módulos afectados
- reports daily/cash/managerial
- print payloads

### Riesgos
- divergencia histórica de reportes

### Criterios de aceptación
- KPIs gerenciales alineados con definiciones oficiales

### Pruebas necesarias
- report regression suite
- print regression

## Sprint 8 — Migración final

### Objetivo
Eliminar dependencias legacy y decidir política final caja vs turno.

### Módulos afectados
- cash policy
- shift policy
- legacy payloads

### Riesgos
- cambio operativo profundo

### Criterios de aceptación
- sin payload ambiguo restante
- política oficial activada

### Pruebas necesarias
- e2e operativo completo
- validación con MySQL real

---

## 8. Riesgos técnicos

## 8.1 Compatibilidad

- frontend actual espera `financial_summary`
- impresión actual espera payloads legacy
- reportes admin agrupan indicadores ambiguos

## 8.2 Rendimiento

- riesgo de multiplicar queries durante convivencia viejo/nuevo
- summaries separados podrían duplicar agregaciones si no se comparte ensamblado

## 8.3 Datos históricos

- históricos carecen de categoría explícita
- habrá que reclasificar con reglas derivadas

## 8.4 Reportes

- daily/cash/managerial pueden divergir si se migran parcialmente

## 8.5 Impresión

- tickets y reportes impresos deben mantener numeración y totales actuales
- no deben romperse por cambio de payload interno

## 8.6 SSE

- eventos actuales no llevan taxonomía financiera explícita
- Sprint 1 no debe requerir cambio de eventos, solo de datos recargados

## 8.7 Cierres de caja

- cierre actual persiste solo `declared_closing_amount`
- QR/Tarjeta hoy solo quedan en notas
- no mezclar este rediseño con política final de arqueo no físico en Sprint 1

## 8.8 Liquidaciones

- pagos al personal influyen en expected cash
- cualquier refactor debe preservar exactamente ese comportamiento

---

## 9. Orden definitivo de implementación

## 9.1 Qué desarrollar primero

1. Taxonomía de movimientos
2. Creación centralizada de movimientos
3. Exposición de taxonomía en payloads actuales
4. Summaries backend por dominio
5. Adaptación mínima frontend
6. Rediseño de dashboard
7. Rebase de reportes
8. Migración final de política caja-turno

## 9.2 Qué depende de qué

- Summaries nuevos dependen de taxonomía estructurada
- Dashboard nuevo depende de summaries nuevos
- Reportes nuevos dependen de summaries nuevos
- Política final caja-turno debe ir al final porque afecta scope, cierres y operación diaria

## 9.3 Qué puede hacerse en paralelo

Una vez terminado Sprint 1:

- backend summaries
- labels frontend
- pruebas de impresión

pueden avanzar parcialmente en paralelo.

## 9.4 Qué nunca debe implementarse antes que otra cosa

- no rediseñar dashboard antes de tener summaries oficiales
- no rebasear reportes antes de estabilizar taxonomía
- no tocar política caja-turno antes de que Caja física y scope estén claros

---

## 10. Checklist final por sprint

## Sprint 1 checklist

- [ ] columnas nuevas creadas en `cash_movements`
- [ ] backfill ejecutable y reversible
- [ ] enums/value objects creados
- [ ] creación de movimientos centralizada
- [ ] endpoints exponen `movement_family` y `movement_category`
- [ ] no cambió el cálculo de expected cash
- [ ] no se perdió ningún movimiento histórico
- [ ] tests de clasificación y compatibilidad en verde

## Sprint 2 checklist

- [ ] summaries nuevos creados
- [ ] payload viejo conservado
- [ ] tests de consistencia summary viejo/nuevo
- [ ] endpoints financieros actualizados

## Sprint 3 checklist

- [ ] cash_summary operativo
- [ ] cierre caja alineado con caja física
- [ ] no ruptura de impresión

## Sprint 4 checklist

- [ ] movimientos agrupables por categoría
- [ ] tablas admin actualizadas
- [ ] compatibilidad de labels

## Sprint 5 checklist

- [ ] settlement_summary oficial
- [ ] pagos por rol visibles
- [ ] expected cash sigue correcto

## Sprint 6 checklist

- [ ] dashboard nuevo sin duplicidad conceptual
- [ ] caja física priorizada
- [ ] pendientes visibles

## Sprint 7 checklist

- [ ] reportes diarios migrados
- [ ] reporte cash migrado
- [ ] reporte gerencial migrado
- [ ] impresión financiera alineada

## Sprint 8 checklist

- [ ] legacy marcado o removido
- [ ] política caja-turno decidida
- [ ] smoke operativo completo PASS

---

## 11. Conclusión técnica oficial

La migración financiera no debe empezar por la UI ni por los reportes.

Debe empezar por el punto más atómico y de mayor propagación:

**la taxonomía estructurada de `cash_movements`.**

Ese es el primer ladrillo técnico correcto porque:

- afecta ventas,
- afecta caja,
- afecta liquidaciones,
- afecta cierres,
- afecta reportes,
- afecta impresión,
- y puede introducirse sin romper la operación actual si se hace con compatibilidad controlada.

Por lo tanto, el Sprint 1 oficial de implementación financiera debe comenzar ahí.
