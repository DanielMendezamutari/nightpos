# ORDERS_SCOPE_KPI_FIX_REPORT (Backend)

## 1. Problema anterior

`GET /api/v1/orders` solo filtraba por `status` individual. El frontend admin usaba `status=OPEN`, por lo que comandas en `SENT_TO_BAR` (activas operativamente) no aparecían.

Además:

- KPI garzón `pending_charge` incluía `SENT_TO_BAR`, duplicando el conteo de `sent_to_bar`.
- Consola de turno y dashboard contaban solo `OPEN` como comandas operativas.
- `IN_PREPARATION` y `READY` existen en modelo/seed pero no hay flujo de barra que los asigne en runtime.

## 2. Scopes nuevos en GET /orders

Parámetro: `scope=<nombre>`

| Scope | Estados incluidos |
|-------|-------------------|
| `operational_active` | OPEN, SENT_TO_BAR |
| `open` | OPEN |
| `sent_to_bar` | SENT_TO_BAR |
| `pending_charge` | SENT_TO_BAR, IN_PREPARATION, READY |
| `billed_recent` | BILLED (máx. 50, más recientes) |
| `cancelled` | CANCELLED |
| `cashier_chargeable` | OPEN, SENT_TO_BAR, IN_PREPARATION, READY *(sin cambio)* |

Compatibilidad: `status=OPEN` sigue funcionando cuando no se envía `scope`.

Implementación: `OrderListScopeResolver` + `ListOrdersUseCase`.

## 3. Nueva semántica de estados

| Concepto operativo | Estados |
|--------------------|---------|
| Comandas activas | OPEN, SENT_TO_BAR |
| Pendientes de cobro (caja/admin) | SENT_TO_BAR (+ IN_PREPARATION/READY cuando existan) |
| Pendientes cobro garzón (KPI) | Solo IN_PREPARATION, READY (evita duplicar SENT_TO_BAR) |
| Cobradas | BILLED |
| Canceladas | CANCELLED |

Estados fantasma (`IN_PREPARATION`, `READY`): soportados en scopes, no protagonistas hasta integración de barra.

## 4. Cambios por componente backend

| Archivo | Cambio |
|---------|--------|
| `OrderListScopeResolver.php` | Constantes y resolución de scopes |
| `ListOrdersUseCase.php` | Usa resolver; límite para `billed_recent` |
| `ListOrdersInput.php` | Campo opcional `limit` |
| `GetWaiterDashboardUseCase.php` | `pending_charge` = solo IN_PREPARATION/READY |
| `ListWaiterOrdersUseCase.php` | Scope `pending_charge` alineado |
| `GetCurrentShiftConsoleUseCase.php` | Contador `active`, KPI `pending_charge` = SENT_TO_BAR, cards `active_orders` / `sent_to_bar_orders` |

## 5. KPIs corregidos

- **Consola turno:** `active_orders` (OPEN+SENT_TO_BAR), `sent_to_bar_orders`, `pending_charge_orders` (SENT_TO_BAR por ahora).
- **Garzón dashboard:** `sent_to_bar` cuenta SENT_TO_BAR; `pending_charge` ya no suma SENT_TO_BAR.

## 6. Validación manual

1. Crear comanda OPEN → `scope=operational_active` y `scope=open` la incluyen.
2. Enviar a barra → aparece en `operational_active`, `sent_to_bar`, `pending_charge`; no en `open`.
3. `status=OPEN` legacy sigue devolviendo solo abiertas.
4. `scope=cashier_chargeable` incluye OPEN y SENT_TO_BAR.
5. Cambiar tenant/sucursal en headers → listados aislados.

## 7. Tests

`tests/Feature/Api/V1/OrdersScopeKpiFixTest.php` — 10 escenarios (scopes, compatibilidad, tenant/branch, KPI garzón).

## 8. Pendiente

- Venta directa de caja (fuera de alcance P0/P1).
- Flujo real de barra que asigne IN_PREPARATION / READY.
- Reportes e impresión (bloqueados hasta cerrar esta fase).
