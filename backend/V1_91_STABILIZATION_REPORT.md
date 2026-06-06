# V1_91_STABILIZATION_REPORT.md — Backend
# Reporte de Estabilización Pre-SSE — NightPOS V1-91.1

**Fecha:** 2026-06-06  
**Fase:** V1-91.1 — Estabilización Pre-SSE + Pago Limpieza en Caja  
**Estado:** COMPLETADA

---

## Resumen Ejecutivo

Esta fase corrigió inconsistencias operativas críticas detectadas durante la auditoría V1-91 PRE-SSE. El objetivo fue llegar a un estado verde y estable antes de iniciar SSE.

**Resultado:** 325 tests pasando, 2172 assertions. Suite 100% verde.

---

## Correcciones Realizadas

### P0 — Reparar Tests Fallando (15 tests → 0 fallos)

**Causa raíz:** Los tests de servicios (manillas, piezas, shows) y liquidaciones no abrían una sesión de caja, pero la regla de negocio exige caja abierta para registrar estos servicios.

**Solución:** Se corrigieron los setups de test para abrir caja antes de registrar servicios. NO se modificó la lógica de negocio.

**Archivos modificados:**

| Archivo | Cambio |
|---------|--------|
| `tests/Feature/Api/V1/GirlIncomeNoWaiterTest.php` | `nightposOpenCashSession()` antes de shows y room-services |
| `tests/Feature/Api/V1/QuickActionsPhaseBTest.php` | `nightposOpenCashSession()` antes de shows y room-services |
| `tests/Feature/Api/V1/QuickGirlCreateTest.php` | `nightposOpenCashSession()` antes de room-services |
| `tests/Feature/Api/V1/SettlementsPhase16Test.php` | `nightposOpenCashSession()` + `payment_method: CASH` en bracelets/shows |
| `database/seeders/Concerns/SeedsNightPosFoundation.php` | Permisos `settlements.access` para roles `waiter` y `girl` |
| `tests/Feature/Api/V1/SettlementsCashUiFixTest.php` | Test de permiso usa `cleaning` user (que no tiene settlements.access) |

### P0 — Pago Limpieza en Caja (verificación backend)

**MarkSettlementPaidUseCase verificado:**
- Soporta `WAITER`, `GIRL`, `CLEANING` correctamente.
- Requiere caja abierta para los 3 tipos.
- Crea `EXPENSE` movement con `source_type = CLEANING_SETTLEMENT`.
- Previene doble pago.
- Retorna 422 "Debe abrir caja para pagar esta liquidación." si no hay caja.

**No fue necesario modificar el UseCase** — ya estaba correcto.

### P0 — Estados de Comanda (KPI)

- `PENDING_CHARGE_BAR_ONLY` en `OrderListScopeResolver` ya excluye correctamente `IN_PREPARATION` y `READY` del KPI del garzón.
- Resultado: el KPI "Pendientes cobro" del garzón siempre es 0 en V1 (nunca hay esos estados).

---

## Tests Nuevos Agregados

Archivo: `tests/Feature/Api/V1/SettlementsCashUiFixTest.php`

| Test | Descripción |
|------|-------------|
| `paying cleaning settlement requires open cash session` | Exige 422 si no hay caja abierta |
| `paying cleaning settlement with open cash creates expense` | Verifica creación de EXPENSE movement |
| `expected_cash decreases after paying cleaning settlement` | Verifica que expected_cash baja al pagar |
| `cannot pay cleaning settlement twice` | Verifica que el segundo pago retorna 422 |
| `current shift settlements includes pending cleaning total` | Cleaning PENDING aparece en cierre turno |
| `cash session expenses include cleaning settlement payment` | total_manual_expense > 0 tras pagar |

**Total suite:** 325 tests, 2172 assertions (antes: 321 tests, ~2050 assertions)

---

## Decisiones de Dominio Documentadas

| Documento | Contenido |
|-----------|-----------|
| `BAR_MODULE_V1_DECISION.md` | Módulo Barra out-of-scope en V1; estados `IN_PREPARATION`/`READY` reservados para V2 |
| `ORDER_CHARGE_RULES_V1.md` | Reglas de cobro; cobrar OPEN permitido con aviso |

---

## V1-91.3 — Bugfix: Pieza Aparece Como "Tiempo Cumplido" al Crear

**Fecha:** 2026-06-06

### Causa Raíz

`APP_TIMEZONE=UTC` + frontend enviando datetime local sin offset → `Carbon::parse()` interpretaba hora Bolivia como hora UTC, dejando `expected_ends_at` 4 horas en el pasado.

### Cambios Backend

- `config/app.php`: `'timezone' => env('APP_TIMEZONE', 'America/La_Paz')`
- `.env.example`: `APP_TIMEZONE=America/La_Paz`
- `CreateRoomServiceUseCase`: `Carbon::parse($startedAt, $tz)` y `Carbon::now($tz)`
- `GirlIncomeMapper`: `Carbon::now($tz)` para cálculo de `is_due`
- `EloquentRoomServiceRepository`: `Carbon::now($tz)` en `listActive`, `listDue`, `findDueUnalerted`

### Tests nuevos

`RoomServiceTimeCalculationTest.php` — 7 casos de cálculo de tiempo. **340 tests, todos PASS.**

---

## Estado Pre-SSE

El backend está listo para iniciar V1-92 SSE-1.

**Áreas estables:**
- Auth y permisos RBAC
- Gestión de turnos y cajas
- Pedidos y cobros
- Servicios de chicas (manillas, piezas, shows)
- Liquidaciones (garzones, chicas, limpieza)
- Pago de liquidaciones con registro en caja
- Tests 100% verdes

**Siguiente fase:** V1-92 SSE-1 BASE
