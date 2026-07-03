# GARZONES_SETTLEMENTS_REGRESSION_AUDIT.md

Fecha auditoria: 2026-07-02  
Alcance: regresion de pestaña Garzones vacia, sin modificar codigo.  
Fuente de datos: MySQL real local (misma base de desarrollo/hosting): `nigtpos`.

## 1. Resumen ejecutivo

Hallazgo principal:
- La pestaña Garzones esta vacia porque el backend no tiene liquidaciones `WAITER` para el turno/caja actual.
- El backend no genera liquidaciones WAITER porque las ventas del turno actual tienen `waiter_commission_amount_snapshot = 0.00` en todas las lineas.
- El frontend no esta descartando datos: renderiza exactamente `data.waiters`.

Causa mas probable en entorno actual (alta probabilidad):
- El garzon activo del turno actual (`users.id = 29`, `RAY`) tiene `staff_profiles.waiter_commission_percent = 0.00` desde su creacion (2026-06-30 09:43:10).
- Con ese perfil, `ChargeOrderUseCase` + `WaiterCommissionResolver` calculan comision 0 para todas las lineas, y `EloquentStaffSettlementRepository::generateForShift()` omite WAITER por condicion `waiterAmount > 0`.

Conclusiones de responsabilidad:
- Problema observado: backend/datos de negocio (comision en 0), no frontend.
- Endpoint de current-shift en este estado deberia devolver `girls` con datos y `waiters` vacio.

## 2. Flujo completo de liquidaciones (garzones)

### 2.1 API y rutas
- `GET /api/v1/settlements/current-shift` -> `SettlementController::currentShift()`.
- Rutas protegidas por:
  - `nightpos.branch:required`
  - `nightpos.branch.access`
  - `nightpos.permission:settlements.access`

Archivo:
- `backend/routes/api.php`
- `backend/app/Http/Controllers/Api/V1/SettlementController.php`

### 2.2 UseCase
`GetCurrentShiftSettlementsUseCase::execute()`:
1. Verifica permiso `settlements.access`.
2. Resuelve scope via `SettlementShiftScopeResolver`:
   - cajera normal: `my_cash_session`
   - admin/senior: `shift`
3. Pide overview al repo:
   - `getCurrentShiftOverview(tenant, branch, shiftId, onlyStaffUserId, cashSessionId?)`
4. Retorna `waiters`, `girls`, `cleaning`, `summary`, `context`.

Archivo:
- `backend/app/Application/StaffSettlement/UseCases/GetCurrentShiftSettlementsUseCase.php`

### 2.3 Repositorio
`EloquentStaffSettlementRepository::getCurrentShiftOverview()`:
1. Carga `staff_settlements` por `tenant_id + branch_id + official_shift_id`.
2. Si scope caja: aplica `where cash_session_id = ?`.
3. Mapea y separa:
   - `waiters = settlement_type === 'WAITER'`
   - `girls = settlement_type === 'GIRL'`

Generacion (`generateForShift()`):
- WAITER se crea solo si `sale_items.waiter_commission_amount_snapshot > 0`.

Archivo:
- `backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php`

### 2.4 SQL relevante
Fuentes WAITER salen de `sales` + `sale_items`:
- `waiter_commission_percent_snapshot`
- `waiter_commission_amount_snapshot`

Si monto snapshot = 0, no hay item de liquidacion WAITER.

### 2.5 Calculo de comision
`ChargeOrderUseCase`:
- Obtiene `%` con `WaiterCommissionResolver::resolvePercent(waiterUserId, tenantId)`.
- Calcula monto por linea `lineTotal * percent / 100`.

`WaiterCommissionResolver` lee:
- `staff_profiles.waiter_commission_percent`
- `where user_id = waiter_user_id and tenant_id and staff_role = 'WAITER'`

Archivos:
- `backend/app/Application/Sale/UseCases/ChargeOrderUseCase.php`
- `backend/app/Infrastructure/Services/WaiterCommissionResolver.php`

## 3. Flujo frontend (Vue)

### 3.1 Endpoints consumidos
- `fetchCurrentShiftSettlements()` -> `GET /settlements/current-shift`
- `fetchSettlementPendingSources()` -> `GET /settlements/current-shift/pending-sources`

Archivo:
- `frontend/src/api/settlements.js`

### 3.2 Composable
`useCurrentShiftSettlements()` asigna directo:
- `waiters.value = data.waiters ?? []`
- `girls.value = data.girls ?? []`

No hay filtro extra ni descarte por estado/tipo en frontend.

Archivo:
- `frontend/src/composables/useCurrentShiftSettlements.js`

### 3.3 DataTable
- `settlements/waiters.vue` usa `:items="waiters"`.
- `settlements/girls.vue` usa `:items="girls"`.

Si backend manda vacio en `waiters`, tabla vacia.

Archivos:
- `frontend/src/pages/nightpos/settlements/waiters.vue`
- `frontend/src/pages/nightpos/settlements/girls.vue`

## 4. Comparacion con liquidaciones de chicas

Estado real en DB:
- Existe liquidacion GIRL pendiente para shift actual (id 11, shift 10, cash 9).
- No existe liquidacion WAITER para shift actual.

Diferencia funcional:
- GIRL depende de `girl_amount_snapshot` / fuentes de chicas.
- WAITER depende estrictamente de `waiter_commission_amount_snapshot > 0`.

En turno actual:
- `girl_amount_snapshot` > 0 (si hay).
- `waiter_commission_amount_snapshot` = 0.00 en todas las lineas.

## 5. Evidencias (MySQL real)

### 5.1 Turnos y caja
- `official_shifts` recientes:
  - id 12 OPEN
  - id 11 OPEN
  - id 10 CLOSED
- `cash_sessions`:
  - id 9 OPEN, `official_shift_id = 10`

Observacion:
- hay inconsistencia operativa (2 OPEN shifts y caja abierta en shift cerrado), pero no explica por si sola que Garzones este vacio; Girls si devuelve datos en ese mismo scope.

### 5.2 Liquidaciones existentes
Consulta agregada `staff_settlements`:
- shift 10: solo `GIRL PENDING cash_session_id=9`, total 140.00.
- shift 10: `WAITER` = 0 filas.

### 5.3 Comisiones por turno/caja
Consulta `sales + sale_items`:
- shift 10 / cash 9: `waiter_total = 0.00`, `girl_total = 150.00`.
- shift 9 / cash 8: `waiter_total = 101.50`.

### 5.4 Detalle de snapshots en turno actual
Lineas de shift 10:
- `waiter_user_id = 29` en ventas.
- `waiter_commission_percent_snapshot = 0.00`.
- `waiter_commission_amount_snapshot = 0.00`.

### 5.5 Perfil del garzon activo
`staff_profiles`:
- `user_id = 29 (RAY)`, `staff_role = WAITER`, `waiter_commission_percent = 0.00`.
- `created_at = 2026-06-30 09:43:10`.

Comparativo historico:
- shift 9: `waiter_user_id = 14 (RANDI)`, perfil 5.00, waiter_total 101.50.
- shift 10: `waiter_user_id = 29 (RAY)`, perfil 0.00, waiter_total 0.00.

## 6. Punto exacto donde se rompe

Punto de ruptura tecnico:
1. En cobro de comanda (`ChargeOrderUseCase`), el resolver devuelve `% = 0.00` para el garzon actual.
2. Se guarda snapshot WAITER en 0.00 por cada `sale_item`.
3. En generacion de liquidaciones, el repo solo crea WAITER si `waiterAmount > 0`.
4. Resultado: no se crea ninguna `staff_settlement` tipo WAITER.
5. `GET /settlements/current-shift` retorna `waiters: []`.
6. `waiters.vue` muestra tabla vacia.

No se detecta descarte en mapper/composable/datatable frontend.

## 7. Commits recientes relacionados

Commits con cambios directos en flujo de settlements:
- `2b787b6` (2026-06-17) `sistema casi al 99%`
  - introduce scope `my_cash_session`, filtros por caja y resumen operacional.
- `3665b1e` (2026-06-20) `mejorando la interface del garzon`
  - endurece `applyCashSessionSettlementScope` a `where cash_session_id = ?` (sin fallback).
- `ba23454` (2026-06-25) `mejoras en garzon, mejora en reporte, y cierre de caja`
  - mejoras de UI/pago/ajustes, no evidencia de filtro frontend que descarte garzones.

Determinacion de “en que commit dejo de funcionar”:
- Para el sintoma actual (Garzones vacio con Chicas funcionando), la evidencia apunta mas a datos (comision garzon en 0) desde 2026-06-30, no a un commit puntual de frontend.
- Commites de riesgo funcional secundarios (scope por caja): `2b787b6` y `3665b1e`.

## 8. Veredicto por categoria solicitada

- API routes: OK, sin ruptura para este sintoma.
- Controllers: OK, passthrough al use case.
- UseCases: OK funcional; el resultado depende de datos de comision.
- Repository: OK segun regla actual (`waiterAmount > 0`), por eso no genera WAITER.
- SQL: evidencia de snapshots WAITER en 0.00 en turno actual.
- DTOs/mappers: sin descarte de garzones.
- Policies/permisos: no bloquean `settlements.access`; cajera tiene `settlements.pending_sources` en DB.
- Tenant/branch scope: consistente en consultas observadas.
- Official Shift: hay inconsistencia operativa (doble OPEN + caja en shift cerrado), riesgo colateral.
- Cash Session: scope por caja activo; no explica solo este caso porque GIRL si aparece.
- SettlementService: condicion de generacion WAITER depende de snapshot > 0.
- Frontend Vue/stores/composables/datatable/mappers: no descartan `waiters`.
- Endpoint consumido: correcto (`/settlements/current-shift`).
- Cambios git recientes: riesgo en scope/caja, pero sintoma actual mapea mas a datos de comision.

## 9. Hipotesis ordenadas por probabilidad

1. Muy alta: perfil de garzon activo con `waiter_commission_percent = 0.00`.
   - Provoca snapshots WAITER 0 y cero liquidaciones WAITER.
2. Media: degradacion operativa de shift/caja (2 turnos OPEN + caja en shift cerrado) puede confundir contexto de liquidaciones.
3. Media-baja: cambio de scope por caja (`2b787b6`/`3665b1e`) podria ocultar liquidaciones en escenarios multicaja, aunque no explica este caso especifico donde WAITER no existe en DB.
4. Baja: bug frontend (descartar filas) descartado por lectura de composable/pagina.

## 10. Que habria que corregir posteriormente (sin implementar aun)

1. Revisar y definir regla de negocio para garzones con comision 0:
   - impedir operacion con 0,
   - o mostrar alerta bloqueante,
   - o fallback controlado segun decision de negocio.
2. Reforzar QA de cobro para validar `waiter_commission_percent_snapshot > 0` cuando corresponda.
3. Revisar coherencia turno/caja (doble OPEN y caja ligada a turno cerrado).
4. Si se confirma caso multicaja, reevaluar el endurecimiento de scope introducido en `2b787b6`/`3665b1e`.

---

## Anexo A: archivos involucrados

Backend:
- `backend/routes/api.php`
- `backend/app/Http/Controllers/Api/V1/SettlementController.php`
- `backend/app/Application/StaffSettlement/UseCases/GetCurrentShiftSettlementsUseCase.php`
- `backend/app/Application/StaffSettlement/UseCases/GetSettlementPendingSourcesUseCase.php`
- `backend/app/Application/StaffSettlement/Services/SettlementShiftScopeResolver.php`
- `backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentStaffSettlementRepository.php`
- `backend/app/Application/Sale/UseCases/ChargeOrderUseCase.php`
- `backend/app/Infrastructure/Services/WaiterCommissionResolver.php`
- `backend/app/Application/Staff/UseCases/QuickCreateWaiterUseCase.php`
- `backend/app/Application/User/Support/StaffProfileRules.php`

Frontend:
- `frontend/src/api/settlements.js`
- `frontend/src/composables/useCurrentShiftSettlements.js`
- `frontend/src/composables/useSettlementPendingSources.js`
- `frontend/src/pages/nightpos/settlements/index.vue`
- `frontend/src/pages/nightpos/settlements/waiters.vue`
- `frontend/src/pages/nightpos/settlements/girls.vue`
- `frontend/src/composables/useSettlementSectionTabs.js`
