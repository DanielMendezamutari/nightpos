# Diagnóstico — Liquidaciones parciales no aparecen tras flujo Mis mesas

**Fecha:** 2026-06-16  
**Modo:** Solo auditoría — **sin cambios de código**  
**Síntoma:** Tras cobrar una segunda comanda (CON ACOMPAÑANTE / combo) en el mismo turno, ya pagada la primera liquidación, no aparece nueva liquidación PENDING.

---

## Resumen ejecutivo

| Pregunta | Respuesta |
|----------|-----------|
| ¿Mis mesas cambia el pipeline de liquidaciones? | **No directamente.** Tras el cobro, el flujo es idéntico al legacy (`ChargeOrderUseCase` → `sale_items` → `generateForShift`). |
| ¿Backend o frontend? | **Probablemente backend / datos**, o **scope de cajera** que oculta resultados. El frontend **no filtra** PENDING cuando ya existe PAID. |
| ¿Simple, combo o ambos? | **Ambos pueden fallar** si falta `girl_user_id` / `sale_item_allocations` en la venta, o si `sales.official_shift_id` no coincide con el turno al generar. |
| Causa más probable | Ver ranking §6 — depende de si la segunda chica es **nueva** o **misma chica (corte #2)**. |

---

## Respuestas a los 10 puntos

### 1. ¿La venta nueva se creó?

**Verificar en BD** tras el paso 8 (segundo cobro):

```sql
SELECT s.id, s.order_id, s.official_shift_id, s.cash_session_id, s.created_at
FROM sales s
JOIN orders o ON o.id = s.order_id
WHERE o.service_table_id IS NOT NULL   -- opcional: filtrar Mis mesas
ORDER BY s.id DESC LIMIT 5;
```

Si **no hay fila nueva** → el problema está antes de liquidaciones (cobro no ejecutado o falló).

---

### 2. ¿La venta nueva tiene `official_shift_id` correcto?

**Código relevante:** `ChargeOrderUseCase` usa el turno del **cajero al momento del cobro**, no el de la orden:

```php
$shift = $this->ensureOperationalShift->execute($tenant->id, $branch->id, $cashierId);
// ...
$sale = $this->sales->create(..., officialShiftId: $shift->id, ...);
```

`generateForShift` solo lee ventas donde `sales.official_shift_id = $officialShiftId` del turno OPEN al generar.

| Escenario | Efecto |
|-----------|--------|
| Orden abierta en turno A, cobro en turno B (auto-rotación) | `orders.official_shift_id ≠ sales.official_shift_id`. Generate en turno B **no ve** la venta si se busca mal, o la venta queda “huérfana” respecto al turno que mira la UI. |
| Orden Mis mesas + cobro mismo turno | **Debe coincidir** — `OpenWaiterTableUseCase` y cobro usan el mismo `EnsureOperationalShiftUseCase`. |

**Comparar:**

```sql
SELECT o.id AS order_id, o.official_shift_id AS order_shift,
       s.id AS sale_id, s.official_shift_id AS sale_shift
FROM orders o
JOIN sales s ON s.order_id = o.id
WHERE o.id = :order_id;
```

---

### 3. ¿La venta nueva tiene `cash_session_id` correcto?

Debe ser la caja abierta de la cajera que cobró. Afecta **scope `my_cash_session`** de la cajera (ver hipótesis G / §5), **no** la generación (`GenerateCurrentShiftSettlementsUseCase` opera a nivel turno completo).

---

### 4. ¿El `sale_item` nuevo tiene `girl_user_id` o allocation?

**Condiciones en `generateForShift` para chica simple:**

```php
!$hasAllocations && $girlAmount > 0 && $line->girl_user_id && $line->sale_mode === 'CON_ACOMPANANTE'
```

**Combo / producto con allocations:**

- `sale_items.girl_user_id` = **null** (correcto)
- Fuente = `sale_item_allocations` → `GIRL_BRACELET_ALLOCATION`

```sql
SELECT si.id, si.sale_mode, si.girl_user_id, si.girl_amount_snapshot, si.product_name_snapshot
FROM sale_items si
WHERE si.sale_id = :sale_id;

SELECT sia.* FROM sale_item_allocations sia
WHERE sia.sale_item_id = :sale_item_id;
```

Si cobro **exitoso**, `OrderItemReadinessChecker` exige chica o allocations completas — en teoría los datos deben existir. Si faltan en `sale_items`/`sale_item_allocations`, hay bug en `ChargeOrderUseCase::snapshotFromOrderItem` o datos inconsistentes.

---

### 5. ¿La fuente nueva aparece en `pending-sources`?

**Limitación importante:** `GetSettlementPendingSourcesUseCase` **no cuenta** líneas `GIRL_CONSUMPTION` / `WAITER_COMMISSION` / `GIRL_BRACELET_ALLOCATION` unsettled. Solo:

- piezas, manillas manuales, shows, limpieza
- flags “chicas/garzones sin comisión” (lectura de `sale_items.girl_user_id`)

Un pending-sources “limpio” **no descarta** que existan ventas sin liquidar.

Para fuentes de venta, usar `countUnsettledShiftSources()` (close-check caja) o consulta manual:

```sql
SELECT si.id, si.girl_user_id, si.girl_amount_snapshot
FROM sale_items si
JOIN sales s ON s.id = si.sale_id
WHERE s.official_shift_id = :shift_id
  AND si.sale_mode = 'CON_ACOMPANANTE'
  AND si.girl_user_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM staff_settlement_items ssi
    WHERE ssi.sale_item_id = si.id AND ssi.source_type = 'GIRL_CONSUMPTION'
  );
```

---

### 6. ¿Generate settlements intenta crearla?

`POST /api/v1/settlements/generate-current-shift` → `generateForShift($tenant, $branch, $openShiftId)`.

Respuesta incluye:

```json
{ "created_items": N, "settlements_touched": M, "shift_id": ... }
```

| `created_items` | Interpretación |
|-----------------|----------------|
| `0` | No encontró fuentes elegibles sin liquidar, o no pudo agregar (header PAID sin fix, shift incorrecto, etc.) |
| `≥ 1` | Backend **sí creó** ítems — si UI no muestra, ver §9 / frontend |

Si migration `2026_06_16_100010` **no aplicada**, segundo `create()` en `ensureSettlement` puede lanzar **duplicate key** → HTTP 500 (no silencioso).

---

### 7. ¿Existe `staff_settlement` PENDING nuevo?

```sql
SELECT id, staff_user_id, settlement_type, status, total_amount, created_at
FROM staff_settlements
WHERE official_shift_id = :shift_id
  AND settlement_type = 'GIRL'
ORDER BY staff_user_id, id;
```

**Comportamiento esperado (post-fix parciales):**

| Caso | Resultado |
|------|-----------|
| Misma chica, corte #1 PAID, nueva actividad | **Nuevo** header PENDING (id distinto) |
| Chica nueva en el turno | Primer header PENDING |
| Misma chica, corte #1 aún PENDING | Reutiliza mismo header, acumula ítems |

`ensureSettlement` (líneas 961–971) busca **solo PENDING**; si solo hay PAID, crea nuevo.

---

### 8. ¿Existe `staff_settlement_item` nuevo?

```sql
SELECT ssi.*, ss.status, ss.staff_user_id
FROM staff_settlement_items ssi
JOIN staff_settlements ss ON ss.id = ssi.staff_settlement_id
WHERE ssi.sale_item_id = :sale_item_id;
```

Unique `(sale_item_id, source_type)` impide duplicar la **misma** línea de venta — correcto. Segunda comanda = **nuevo** `sale_item_id` = debe poder liquidarse.

---

### 9. ¿Frontend recibe la liquidación y no la muestra?

Solo si:

1. `GET /settlements/current-shift` → `girls[]` **incluye** el PENDING y la tabla no refrescó (sin SSE en tab Chicas).
2. `context.empty_overview = true` → API devuelve `girls: []` aunque existan liquidaciones en BD para el turno (scope cajera).
3. Usuario en **Historial** con filtro `status=PAID`.

Si Network muestra el PENDING en `girls[]` → bug UI (poco probable; no hay filtro). Si Network vacío → backend/scope.

---

### 10. ¿Mis mesas afectó la creación de orden?

| Campo | Mis mesas (`OpenWaiterTableUseCase`) | Legacy (`CreateOrderUseCase`) |
|-------|--------------------------------------|-------------------------------|
| `official_shift_id` | `ensureOperationalShift` | Igual |
| `waiter_user_id` | Garzón autenticado | Garzón o input |
| `service_table_id` | Sí | null |
| Add item / charge | Mismos endpoints | Mismos endpoints |

**Conclusión:** Mis mesas **no altera** el modelo de liquidación. La correlación temporal con el deploy de Fase C es probablemente **operativa o de entorno**, no un hook directo en settlements.

**Gap de tests:** `PartialSettlementsTest` usa `POST /orders` + `table_label`. **No hay test** que reproduzca: Mis mesas → open → add CON_ACOMPANANTE → charge → pay → segunda ronda → generate.

---

## Validación de hipótesis

### A — `official_shift_id` incorrecto

| Entidad | Riesgo |
|---------|--------|
| `orders` | Shift al **abrir** mesa |
| `sales` | Shift al **cobrar** (cajero) |
| `staff_settlements` | Shift al **generar** |

**Riesgo real:** desalineación orden/venta si hubo **auto-rotación de turno** entre abrir mesa y cobrar. Mis mesas no introduce lógica distinta; amplía el tiempo entre open y charge (mesa ocupada más tiempo).

**Estado:** ⚠️ Validar en caso real con query §2.

---

### B — `waiter_user_id` incorrecto

Mis mesas fija `waiter_user_id = garzón autenticado`. Afecta comisión **WAITER**, no liquidación **GIRL** por consumo.  
**Estado:** ❌ Poco relevante para el caso CON ACOMPAÑANTE / chica.

---

### C — CON ACOMPAÑANTE simple sin `girl_user_id`

Charge **bloquea** si falta chica (`OrderItemReadinessChecker`). Si cobró, debería estar en `order_items` y copiarse a `sale_items`.  
**Estado:** ⚠️ Verificar snapshot post-cobro; posible si hubo bypass manual en BD o error en charge antiguo.

---

### D — Combo sin snapshot en `sale_item_allocations`

`ChargeOrderUseCase` llama `snapshotFromOrderItem` por cada línea. Generate usa join `sale_item_allocations` + `GIRL_BRACELET_ALLOCATION`.  
**Estado:** ⚠️ Verificar allocations post-cobro; si garzón no completó reparto, charge falla antes.

---

### E — Generate no crea segundo PENDING tras PAID

**En código actual (repo):** fix aplicado — `ensureSettlement` solo reutiliza PENDING; migration elimina unique `(official_shift_id, staff_user_id, settlement_type)`.

**En producción sin migration:** **reproduce exactamente el bug María** documentado en `PARTIAL_SETTLEMENTS_AUDIT.md` — `created_items: 0`, fuentes huérfanas.

**Estado:** 🔴 **Alta prioridad verificar** `php artisan migrate:status` / `SHOW INDEX FROM staff_settlements`.

---

### F — Fuente marcada como ya liquidada

`sourceAlreadySettled` / `saleItemAlreadySettled` buscan en **cualquier** settlement (PAID o PENDING). Solo aplica al **mismo** `sale_item_id` / `source_id`.  
Segunda comanda = nuevos IDs → no debería marcar indebidamente.  
**Estado:** ❌ Improbable salvo re-cobro duplicado del mismo sale_item.

---

### G — Frontend / scope no muestra

**Cajera** (`SettlementShiftScopeResolver`):

- Scope forzado `my_cash_session`
- Si `cash_session.official_shift_id ≠ open_shift_id` → `empty_overview: true` → **listas vacías**
- Si caja sin actividad en ese turno → idem

**Admin / cajera senior:** scope `shift` → ve todo el turno.

Generate **siempre** usa turno OPEN completo (admin path en use case).

**Estado:** ⚠️ Si admin genera y cajera no ve → scope UI, no generación. Si nadie ve → generación o datos.

---

## ¿Qué cambió después de Mis mesas?

| Cambio | Impacto en liquidaciones |
|--------|--------------------------|
| `OpenWaiterTableUseCase` + `service_table_id` | Ninguno post-cobro |
| UX garzón mobile (chica al enviar a barra) | Indirecto: flujo más largo open → send → charge; más ventana para rotación de turno |
| Asignación mesas por turno (`waiter_table_assignments`) | Solo apertura de mesa, no settlements |
| Fix liquidaciones parciales + migration (misma época) | Si no desplegado junto, sí explica el síntoma |
| `SettlementShiftScopeResolver` (scope cajera) | Puede ocultar liquidaciones existentes |

**No hay commit lógico** que conecte `generateForShift` con `service_table_id`.

---

## Ranking de causas probables

| # | Causa | Tipo | Evidencia |
|---|-------|------|-----------|
| 1 | Migration `2026_06_16_100010` no aplicada (misma chica, corte #2) | Backend / DB | Unique constraint; `PARTIAL_SETTLEMENTS_AUDIT.md`; `created_items: 0` |
| 2 | `sales.official_shift_id` ≠ turno OPEN al generar | Backend / datos | Charge usa shift cajero; generate filtra por shift OPEN |
| 3 | Segunda chica: venta OK pero sin `girl_user_id`/allocations en snapshot | Backend / datos | Generate ignora línea; charge debería impedirlo |
| 4 | Cajera con `empty_overview` (caja en turno anterior post-rotación) | Backend scope → UI vacía | `SettlementShiftScopeResolver` L167–177 |
| 5 | UI stale en tab Chicas (sin SSE) | Frontend | PENDING en API pero pantalla no refrescada |
| 6 | Mis mesas como causa directa | Descartada | Mismo charge/generate path |

---

## Plan de corrección (NO implementado)

### Fase 1 — Confirmación en caso real (15 min)

1. Capturar `order_id`, `sale_id`, `sale_item_id` del segundo cobro.
2. Ejecutar queries §1–§8.
3. Network: `POST generate-current-shift` → `created_items`.
4. Network: `GET current-shift` → `girls[]`, `context.empty_overview`.
5. `SHOW INDEX FROM staff_settlements` → confirmar ausencia de `staff_settlements_shift_staff_type_unique`.

### Fase 2 — Fix según hallazgo

| Hallazgo | Acción propuesta |
|----------|------------------|
| Migration pendiente | `php artisan migrate` en entorno afectado |
| Shift mismatch orden/venta | Alinear `sales.official_shift_id` con política de negocio (usar order shift o re-abrir turno); evaluar stamp en charge |
| Datos girl faltantes | Auditar `ChargeOrderUseCase` snapshot; test integración Mis mesas + partial |
| Scope cajera vacío | Ajustar resolver post-rotación o forzar refresh caja; documentar para admin |
| UI stale | SSE en `girls.vue` / reload post-generate |

### Fase 3 — Tests faltantes

- `PartialSettlementsTest` vía `POST /waiter/my-tables/{id}/open`
- Escenario: misma chica corte #2 + chica distinta misma ronda
- Simple CON_ACOMPANANTE + combo con allocations

---

## Riesgos del fix

| Riesgo | Mitigación |
|--------|------------|
| Re-aplicar migration en prod | Backup; ventana de mantenimiento |
| Cambiar shift en charge | Afecta reportes históricos; requiere regla de negocio clara |
| Relajar scope cajera | Podría mostrar liquidaciones de otras cajas |
| Tocar `ensureSettlement` sin migration | Duplicar headers fallaría en DB antigua |

---

## Archivos revisados

| Archivo | Rol |
|---------|-----|
| `OpenWaiterTableUseCase.php` | Creación orden Mis mesas |
| `ChargeOrderUseCase.php` | Venta + snapshots |
| `EloquentStaffSettlementRepository.php` | `generateForShift`, `ensureSettlement` |
| `GenerateCurrentShiftSettlementsUseCase.php` | Orquestación generate |
| `GetCurrentShiftSettlementsUseCase.php` | Listado + empty_overview |
| `GetSettlementPendingSourcesUseCase.php` | Fuentes pendientes (limitado) |
| `SettlementShiftScopeResolver.php` | Scope cajera vs turno |
| `MarkSettlementPaidUseCase.php` | Pago corte |
| `2026_06_16_100010_allow_multiple_settlements_per_shift_staff.php` | Fix unique |
| `PartialSettlementsTest.php` | Cobertura parciales (legacy orders) |
| `WaiterTablesPhaseBTest.php` | Mis mesas sin escenario parcial |

---

## Endpoints afectados

| Método | Ruta | Rol en diagnóstico |
|--------|------|-------------------|
| POST | `/waiter/my-tables/{id}/open` | Creación orden Mis mesas |
| POST | `/orders/{id}/items` | Ítems + chica |
| POST | `/orders/{id}/charge` | Venta |
| POST | `/settlements/generate-current-shift` | Generación |
| GET | `/settlements/current-shift` | Listado UI |
| GET | `/settlements/current-shift/pending-sources` | Alertas (no exhaustivo ventas) |
| POST | `/settlements/{id}/mark-paid` | Pago primer corte |

---

*Documento de diagnóstico. No se modificó código de liquidaciones, Mis mesas, CBA ni SSE.*

---

## Resolución (2026-06-16)

| Item | Resultado |
|------|-----------|
| Migración `2026_06_16_100010` | Aplicada en dev/test |
| Código partial settlements | Correcto post-migración |
| Mis mesas | No causa el bug |
| Tests regresión | `PartialSettlementsAfterTablesTest` 9/9 |
| Fix prod si migración pendiente | `php artisan migrate` |

Ver: `backend/PARTIAL_SETTLEMENTS_AFTER_TABLES_FIX_REPORT.md`
