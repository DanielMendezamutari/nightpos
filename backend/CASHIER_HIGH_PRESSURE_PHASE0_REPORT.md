# Cajera alta presión — Fase 0 (Backend)

**Fecha:** 2026-06-16  
**Estado:** ✅ Implementado  
**Auditoría base:** `CASHIER_HIGH_PRESSURE_OPERATION_AUDIT.md`  
**Par frontend:** `frontend/CASHIER_HIGH_PRESSURE_PHASE0_REPORT.md`

---

## Objetivo Fase 0

Enriquecer `GET /orders?scope=cashier_chargeable` con señales operativas **sin romper clientes existentes**, usando la **misma lógica de cobro** que `ChargeOrderUseCase` (`OrderItemReadinessChecker`).

---

## Cambios implementados

### 1. `OrderItemReadinessChecker`

- Nuevo `assessOrder()` — evaluación no destructiva.
- `assertOrderReady()` / `assertItemReady()` refactorizados vía `checkItemReady()` compartido.
- Códigos de bloqueo: `GIRL_MISSING`, `ALLOCATION_INCOMPLETE`, `ORDER_EMPTY`.

### 2. `BraceletAllocationValidator`

- Nuevo `isComplete()` reutilizado por assess y assert.

### 3. `ListOrdersUseCase`

- Si `scope=cashier_chargeable`:
  - Carga ítems (`includeItems=true` en repositorio).
  - Agrega campos operativos + `waiting_minutes`.
  - Ordena con `OrderChargeQueueSorter`.

### 4. Campos nuevos en listado (solo `cashier_chargeable`)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `waiting_minutes` | int | Minutos desde `sent_to_bar_at` o `opened_at` |
| `has_companion_items` | bool | Línea `CON_ACOMPANANTE` activa |
| `has_combo_items` | bool | Producto con `requires_allocation` |
| `allocation_incomplete` | bool | Combo sin manillas completas |
| `girl_missing_count` | int | Líneas acompañante sin chica |
| `charge_blocked` | bool | No pasaría `assertOrderReady` |
| `charge_blockers` | string[] | Códigos de bloqueo |

**Compatibilidad:** otros scopes no incluyen estos campos. Campos legacy intactos.

### 5. Orden de cola

Prioridad (`OrderChargeQueueSorter`):

1. `waiting_minutes` DESC
2. Estado: `SENT_TO_BAR` → `IN_PREPARATION` → `READY` → `OPEN`
3. `opened_at` ASC
4. `id` ASC

### 6. Soporte repositorio

- `OrderRepositoryInterface::listForBranch(..., bool $includeItems = false)`
- `EloquentOrderRepository` carga relación `items` cuando aplica.

---

## Archivos tocados

| Archivo |
|---------|
| `app/Application/Order/Services/OrderItemReadinessChecker.php` |
| `app/Application/Order/Services/BraceletAllocationValidator.php` |
| `app/Application/Order/Support/OrderChargeQueueSorter.php` |
| `app/Application/Order/Support/OrderWaitingMinutesCalculator.php` |
| `app/Application/Order/Support/OrderMapper.php` |
| `app/Application/Order/UseCases/ListOrdersUseCase.php` |
| `app/Domain/Order/Repositories/OrderRepositoryInterface.php` |
| `app/Infrastructure/Persistence/Eloquent/Repositories/EloquentOrderRepository.php` |
| `tests/Feature/Api/V1/CashierChargeQueuePhase0Test.php` |

---

## Tests

`CashierChargeQueuePhase0Test` — **11/11 OK**

1. `waiting_minutes`
2. `has_companion_items`
3. `has_combo_items`
4. `allocation_incomplete`
5. `girl_missing_count`
6. `charge_blocked` true/false
7. Combo completado → desbloqueo
8. Campos legacy preservados
9. Aislamiento tenant
10. Aislamiento branch
11. Orden por urgencia

Suite completa: `php artisan test` — OK.

---

## No incluido (Fase 1+)

- Cobro desde card sin detalle
- `GET /cashier/snapshot`
- `payment_preset` en charge API
- Shell cajera

---

## QA manual sugerido

Ver checklist en `frontend/CASHIER_HIGH_PRESSURE_PHASE0_REPORT.md`.
