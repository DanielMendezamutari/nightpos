# AUDITORÍA OPERATIVA — CAJERA ALTA PRESIÓN (Backend)

**Fecha:** 2026-06-16  
**Alcance:** Capacidades del sistema que habilitan o frenan la operación de cajera  
**Tipo:** Auditoría operativa (impacto en UX, no arquitectura)  
**Par frontend:** `frontend/CASHIER_HIGH_PRESSURE_OPERATION_AUDIT.md`

---

## Resumen ejecutivo

El backend **soporta bien** los flujos core: abrir caja, cobrar comandas (mixto), venta directa, movimientos, liquidaciones por turno/caja, cierre con blockers y arqueo. Sin embargo, la **API de listado para cajera devuelve pocos campos operativos** — la cajera no puede ver desde la cola si hay combo sin manilla, acompañante sin chica, ni minutos de espera. Eso obliga al frontend a navegar al detalle y multiplica clics bajo presión.

Comparado con Garzón — que hoy recibe en `GET my-tables` estado LIBRE/OCUPADA, área y acción open idempotente — la cajera recibe un listado **tipo administrativo** (`table_label`, `status`, `items_count`) sin semáforos operativos.

**Veredicto:** reglas de negocio sólidas; **capa de presentación operativa insuficiente** en endpoints de listado y consola.

> **Fase 0 implementada 2026-06-16** — `listBrief` enriquecido en `cashier_chargeable`, orden por urgencia, tests `CashierChargeQueuePhase0Test`. Ver `CASHIER_HIGH_PRESSURE_PHASE0_REPORT.md`.  
> **Fase 1 implementada 2026-06-16** — frontend cobra inline; backend sin cambios (`fetchOrder` + `POST charge`). Ver `frontend/CASHIER_HIGH_PRESSURE_PHASE1_REPORT.md`.

---

## 1. Estado actual — qué entrega el sistema a la cajera

### 1.1 Flujo completo soportado

| Etapa | Endpoint / caso de uso | ¿Funciona? | Notas operativas |
|-------|------------------------|------------|------------------|
| Abrir caja | `POST /cash-sessions/open` | ✅ | Requiere monto inicial |
| Cola cobro | `GET /orders?scope=cashier_chargeable` | ✅ | Estados OPEN, SENT_TO_BAR (+ IN_PREPARATION/READY según config scope) |
| Detalle comanda | `GET /orders/{id}` | ✅ | Ítems con flags allocation, girl_name |
| Cobrar | `POST /sales/charge-order` | ✅ | Pago mixto, validación caja abierta |
| Venta directa | `POST /sales/direct` | ✅ | Bloquea combos con allocation en dominio |
| Movimientos | `POST /cash-movements` | ✅ | Motivos catalogados |
| Generar liquidaciones | `POST /settlements/generate-current` | ✅ | Scope cajera vs turno |
| Pagar liquidación | `POST /settlements/{id}/pay` | ✅ | Un método; egreso en caja |
| Pre-check cierre | `GET /cash-sessions/close-check` | ✅ | Blockers accionables |
| Cerrar caja | `POST /cash-sessions/close` | ✅ | Arqueo declarado vs esperado |
| Consola turno | `GET /shift-console/current` | ✅ | Agregados caja, comandas, habitaciones |
| SSE eventos | `/events/stream` | ✅ parcial | Gaps en `order.updated` según tipo edición |

### 1.2 Campos en listado cajera (`OrderMapper::listBrief`)

Hoy la API expone por comanda en cola:

| Campo | Útil para cajera |
|-------|------------------|
| `table_label` | ✅ |
| `order_number` | ⚠️ secundario |
| `status` | ✅ |
| `waiter_name` | ✅ |
| `opened_at` / `sent_to_bar_at` | ⚠️ timestamp, no «esperando X min» |
| `items_count` | ✅ |
| `total` | ✅ |
| `currency` | ✅ |

**No expone (pero la cajera necesita sin entrar al detalle):**

| Campo operativo | ¿Existe en dominio? | ¿En list API? |
|-----------------|---------------------|---------------|
| `waiting_minutes` | Calculable | ❌ |
| `has_companion_items` | Sí (sale_mode) | ❌ |
| `has_combo_items` | Sí (requires_allocation) | ❌ |
| `allocation_incomplete` | Sí por ítem | ❌ |
| `girl_missing_count` | Sí | ❌ |
| `is_chargeable` | Inferible | ❌ |
| `charge_blockers[]` | Parcial en detalle | ❌ |
| `has_room_service_link` | Opcional | ❌ |
| `bar_pending` (IN_PREPARATION) | Sí | ❌ explícito |

### 1.3 Consola de turno (`GetCurrentShiftConsoleUseCase`)

Entrega agregados útiles:

- Caja abierta/cerrada, totales efectivo/QR/tarjeta, esperado.
- Conteos comandas (abiertas, barra, pendientes cobro).
- Habitaciones ocupadas/limpieza.
- Piezas vencidas, liquidaciones pendientes (count + monto).
- Alertas estructuradas.
- Tabla comandas recientes (limitada).

**No entrega:**

- Tiempo promedio espera cobro.
- Garzones activos en turno.
- Total BOB en cola pendiente de cobro (solo count).
- Comanda más antigua (quick action).

---

## 2. Pantalla por pantalla — impacto backend

---

### 2.1 Dashboard / Consola de turno

| Pregunta | Respuesta backend |
|----------|-------------------|
| ¿Qué hace hoy? | Un request agrega turno, caja, comandas, rooms, settlements, alerts. |
| ¿Cuántos clics? | N/A backend — 1 request ~200–500 ms según carga. |
| ¿Info necesaria? | Parcialmente cubierta. |
| ¿Info sobra? | `services_summary` detallado para vista «3 segundos». |
| ¿Info falta? | `pending_charge_total_amount`, `avg_wait_minutes`, `oldest_pending_order_id`, `active_waiters_count`. |
| ¿Un toque? | Requiere endpoint o ampliar payload con IDs accionables. |
| ¿Pierde tiempo? | Frontend hace polling 30 s + SSE debounce 600 ms — latencia percibida 0,6–2 s post evento. |
| ¿Escribir? | No. |
| ¿Automático? | SSE cubre eventos principales; falta `order.sent_to_bar` en algunos listeners frontend. |
| ¿Más rápido? | Endpoint ligero `GET /cashier/snapshot` solo chips críticos. |

---

### 2.2 Cobrar comandas

| Pregunta | Respuesta backend |
|----------|-------------------|
| ¿Qué hace hoy? | Scope `cashier_chargeable` filtra por estados cobrables y scope cajera (`cashier_scope=1`). |
| Scope | `OrderListScopeResolver`: OPEN, SENT_TO_BAR, IN_PREPARATION, READY en pending/chargeable según versión desplegada. |
| ¿Clics? | Frontend multiplica; backend cobro es 1 POST. |
| ¿Info necesaria? | **Gap principal** — listBrief incompleto. |
| ¿Automático? | `order.created`, `order.billed` vía SSE — OK. |
| ¿Errores? | Cobro sin caja → 422; ítems cancelados — validación en ChargeOrderUseCase. |

**Problema operativo crítico:** la cajera descubre en detalle (o en error al cobrar) que falta manilla/chica — debería verlo en listado.

**Propuesta API (operativa, no implementada):**

```json
{
  "id": 42,
  "table_label": "Mesa 12",
  "total": "450.00",
  "waiting_minutes": 18,
  "operational_flags": {
    "has_companion": true,
    "has_combo": true,
    "allocation_incomplete": true,
    "girl_missing": 1,
    "charge_blocked": true,
    "charge_block_reason": "COMBO_ALLOCATION_INCOMPLETE"
  }
}
```

---

### 2.3 Detalle comanda

| Pregunta | Respuesta backend |
|----------|-------------------|
| ¿Qué hace hoy? | GET order con ítems, allocation_complete, girl_name, requires_allocation. |
| ¿Scroll? | N/A — muchos ítems = payload grande. |
| ¿Entiende rápido? | Datos existen pero sin agrupación «requires_action». |
| ¿Fijo primero? | Backend podría incluir `action_required_items[]` top-level. |
| SSE | `order.updated` — emisión parcial según tipo edición (ver FAST_OPERATION audit). |

**Gap:** ediciones qty/mode/girl no siempre emiten SSE → cajera ve datos viejos en detalle si otra persona edita.

---

### 2.4 Pago

| Aspecto | Backend |
|---------|---------|
| Pago simple | ✅ un método en array payments |
| Pago mixto | ✅ suma debe igual total |
| Cambio | Frontend calcula; backend registra pagos |
| QR / Tarjeta | ✅ enum CASH/QR/CARD |
| Enter | N/A backend |
| Errores | Mensajes dominio claros; deberían mapear a `charge_block_reason` en listado |
| Idempotencia | Re-cobro comanda BILLED → error — correcto |
| Tiempo respuesta | Típico < 300 ms; aceptable |

**Oportunidad operativa:** `POST charge-order` podría aceptar `payment_preset: "ALL_CASH" | "ALL_QR" | "ALL_CARD"` para evitar payload verbose desde UI rápida.

---

### 2.5 Venta directa

| Aspecto | Backend |
|---------|---------|
| Crear venta | ✅ |
| Combo bloqueado | ✅ requires_allocation |
| CON_ACOMPANANTE | Requiere girl_user_id en ítem |
| Caja cerrada | 422 |
| SSE | `direct_sale.created` — consola/caja escuchan |

Sin gaps críticos de negocio.

---

### 2.6 Mi caja / Movimientos / Cierre / Arqueo

| Aspecto | Backend |
|---------|---------|
| Sesión actual | `GET /cash-sessions/current` — financial_summary completo |
| Movimientos | Paginados; motivos activos filtrados |
| Close check | `CashSessionCloseCheckBuilder` — blockers: comandas activas, piezas, settlements pending, sources unsettled |
| Arqueo | expected vs declared; combo reconciliation en check |
| Imprimir | GET session o print route |

**Fortaleza operativa:** close-check con `actions[]` y rutas — alinea con UX de «no dejar cerrar si hay pendientes».

**Fricción:** blockers cuentan comandas OPEN/SENT_TO_BAR del **turno**, no solo de la cajera — correcto operativamente pero puede sorprender si otra cajera tiene cola.

**Gap menor:** no hay endpoint «motivos frecuentes» ordenados por uso en sucursal.

---

### 2.7 Liquidaciones

| Aspecto | Backend |
|---------|---------|
| Scope cajera | `SettlementShiftScopeResolver` — my_cash_session vs shift |
| Generate | Crea PENDING por fuentes del turno/corte |
| Pay | Un método; mueve caja |
| Partial settlements | Cortes múltiples por turno — soportado |
| SSE | settlement.generated, settlement.paid |

**Operativamente:** cajera debe entender scope «Mi caja» vs «Turno» — el backend lo expone en context; UI lo muestra pequeño.

**Gap UX-backend:** no hay listado unificado `GET /settlements/pending` cross-role — frontend navega 3 scopes.

---

### 2.8 Precuentas / Tickets

| Aspecto | Backend |
|---------|---------|
| Precheck | `GET /orders/{id}/precheck` |
| Ticket venta | sale receipt data |

Funcional; acceso solo desde detalle hoy.

---

## 3. Problemas encontrados

### 3.1 Críticos

| ID | Problema | Impacto operativo |
|----|----------|-------------------|
| B-C01 | Listado cajera **sin flags operativos** | Entrada obligada al detalle |
| B-C02 | **Sin `waiting_minutes`** en API | Cola no priorizable |
| B-C03 | **`charge_blocked` invisible** en listado | Error al cobrar bajo presión |
| B-C04 | SSE **`order.updated` incompleto** en ediciones | Dos cajeras desincronizadas |
| B-C05 | Consola sin **total BOB pendiente cobro** | KPI incompleto para «3 segundos» |
| B-C06 | Close-check no distingue **chargeable vs open** | Blocker genérico «comandas pendientes» |

### 3.2 Menores

| ID | Problema |
|----|----------|
| B-M01 | listBrief sin `service_table_id` / área visible |
| B-M02 | Shift console orders table sin flags |
| B-M03 | Ventas list sin SSE event dedicado |
| B-M04 | No preset pago en charge API |
| B-M05 | Motivos movimiento sin orden frecuencia uso |

---

## 4. Flujo ideal — contrato operativo API

### 4.1 Snapshot cajera (nuevo endpoint propuesto)

`GET /api/v1/cashier/snapshot`

```json
{
  "cash_session": { "open": true, "expected_cash": "1240.00", "expected_qr": "380.00", "expected_card": "920.00" },
  "queue": {
    "pending_count": 7,
    "pending_total_amount": "3240.00",
    "avg_wait_minutes": 8,
    "oldest_minutes": 18
  },
  "settlements_pending_count": 3,
  "settlements_pending_amount": "540.00",
  "due_room_services": 2,
  "alerts": []
}
```

**Beneficio:** 1 request, < 100 ms, alimenta shell cajera.

### 4.2 Listado enriquecido

Extender `listBrief` o crear `scope=cashier_chargeable_enriched` sin romper clientes actuales.

### 4.3 Cobro express

`POST /sales/charge-order` con body mínimo:

```json
{ "order_id": 42, "payment_preset": "ALL_CASH", "received_amount": 500 }
```

---

## 5. Wireframes de datos (vista cajera)

### Cola — payload ideal por fila

```
┌─ OrderListItem (API) ────────────────────────┐
│ table_label: "Mesa 12"                       │
│ waiter_name: "Juan"                          │
│ waiting_minutes: 18                          │
│ total: 450.00                                │
│ items_count: 6                               │
│ flags: [COMPANION, COMBO, ALLOCATION_PENDING]│
│ charge_blocked: true                         │
│ charge_blockers: ["COMBO_ALLOCATION"]        │
└──────────────────────────────────────────────┘
```

### Cierre — close-check ideal ampliado

```
blockers:
  - type: CHARGEABLE_ORDERS, count: 3, total: 890.00 BOB
  - type: PENDING_SETTLEMENTS, count: 2
actions:
  - label: Cobrar 3 comandas, route: nightpos-cashier-orders
  - label: Pagar liquidaciones, route: nightpos-settlements
```

---

## 6. Cantidad de clics — contribución backend

| Fricción | Causa backend | Fix propuesto | Δ clics frontend |
|----------|---------------|---------------|------------------|
| Entrar al detalle para ver manillas | listBrief incompleto | flags en listado | −2 |
| Reintentar cobro fallido | charge_blocked tarde | prevalidación GET | −1 |
| F5 consola | snapshot + SSE | snapshot ligero | −1 |
| 3 pantallas liquidaciones | sin pending unificado | GET pending | −2 |
| Escribir mixto siempre | sin preset | payment_preset | −1 |

**Total potencial:** 2–3 clics menos por cobro × volumen alto = minutos salvados por noche.

---

## 7. Tiempo real — backend

| Evento | ¿Emitido? | ¿Cajera lo necesita? |
|--------|-----------|----------------------|
| order.created | ✅ | ✅ |
| order.sent_to_bar | ✅ | ✅ |
| order.billed | ✅ | ✅ |
| order.updated (qty/mode) | ⚠️ parcial | ✅ |
| order.updated (header) | ⚠️ | ⚠️ |
| sale.created | ✅ | ✅ |
| direct_sale.created | ✅ | ⚠️ |
| cash.session.* | ✅ | ✅ |
| settlement.* | ✅ | ✅ |
| room_service.* | ✅ | Consola |

**Latencia:** poll SSE backend ~2 s + debounce frontend 500–600 ms = hasta **2,5 s** antes de ver comanda nueva — acceptable con banner; mejorable a 300 ms debounce en cola cobro.

**Propuesta:** evento `cashier.queue_changed` agregado tras operaciones que afecten cola — 1 evento vs múltiples order.*.

---

## 8. Errores — prevención desde reglas/datos

| Error | Regla backend hoy | Mejora operativa |
|-------|-------------------|------------------|
| Cobrar sin manilla | Falla en charge | Validar en listBrief + 409 con código |
| Cobrar comanda otro turno | Scope filtra | OK |
| Cierre con cola | close-check | Separar mensajes por tipo |
| Liquidar sin generate | pay falla | OK |
| Pago mixto desbalanceado | validación suma | OK |
| Venta combo directa | bloqueado | OK — mensaje claro |

**Principio:** mismo validador en **read** (listado) y **write** (charge) — DRY operativo.

---

## 9. Comparación con Garzón (backend)

| Capacidad | Garzón API | Cajera API |
|-----------|------------|------------|
| Vista asignada | `GET /waiter/my-tables` — estado mesa | Cola genérica orders |
| Acción 1 paso | `POST /waiter/tables/{id}/open` | Cobro requiere GET detail + POST charge |
| Idempotencia | open table → same order | charge idempotente en BILLED |
| Flags visuales | FREE/OCCUPIED en response | Solo status comanda |
| Scope usuario | Solo mesas asignadas | cashier_scope en orders |

**Meta:** `GET /cashier/charge-queue` con semántica similar a `my-tables`: **cada fila lista para decidir en 1 segundo**.

---

## 10. Rendimiento bajo carga

| Operación | Riesgo sábado | Observación |
|-----------|---------------|-------------|
| GET orders chargeable | Medio | Sin paginación frontend — OK hasta ~50 filas |
| GET order detail | Alto | Payload ítems grande — N+1 allocations |
| GET shift-console | Medio-alto | Muchos agregados en 1 request |
| POST charge | Bajo | Transaccional |
| GET close-check | Medio | Varias queries — acceptable al cierre |
| Generate settlements | Medio | Solo fin de corte |

**Recomendaciones operativas:**

1. Índices en `(branch_id, official_shift_id, status)` — probablemente OK.
2. listBrief enriquecido debe calcular flags en **1 query agregada**, no N detalles.
3. Snapshot cajera cacheable 5 s en Redis opcional — solo si hay dolor medido.

---

## 11. Roadmap por fases (backend)

### Fase 0 — Sin breaking changes

- Calcular `waiting_minutes` en `listBrief`.
- Añadir flags booleanos agregados (`has_companion`, `has_combo`, `allocation_incomplete`, `girl_missing`).
- Completar emisión SSE `order.updated` en todas las ediciones de ítem.
- close-check: desglose blocker `CHARGEABLE_ORDERS` con total BOB.

### Fase 1 — Cola alta presión

- `GET /cashier/snapshot` o ampliar shift-console.
- `charge_blocked` + `charge_blockers[]` en listado.
- Validador compartido list/write para cobro.

### Fase 2 — Pago express

- `payment_preset` en charge-order y direct-sale.
- `GET /settlements/pending-summary` unificado.

### Fase 3 — Cierre y arqueo

- close-check acciones con deep links query (?auto=charge).
- Endpoint motivos movimiento con `usage_count`.

### Fase 4 — Opcional escala

- Evento agregado `cashier.queue_changed`.
- Paginación cursor en cola si > 100 comandas.

---

## 12. Qué implementar primero

1. **Flags operativos en listBrief** — desbloquea Fase 1 frontend sin nuevo endpoint.
2. **`waiting_minutes`** — cero riesgo, alto impacto visual.
3. **SSE order.updated completo** — seguridad multi-cajera.
4. **snapshot / pending totals en consola** — dashboard 3 segundos.
5. **payment_preset** — después de UI chips.

---

## 13. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| listBrief más lento | Subquery agregada; benchmark con 100 órdenes |
| charge_blocked desincronizado | Mismo servicio validación que ChargeOrderUseCase |
| Romper clientes existentes | Campos adicionales opcionales |
| Scope liquidaciones | No cambiar SettlementShiftScopeResolver |
| Falsos positivos blocker | Tests Pest escenarios cobro/combo/manilla |

---

## 14. Beneficio operativo esperado

| Métrica | Hoy | Con Fase 0–1 |
|---------|-----|--------------|
| Requests por cobro simple | 2+ (list + detail + charge) | 1 list + 1 charge |
| Errores cobro sorpresa | Frecuentes en combo/manilla | Visibles antes de tap |
| Latencia info cola | SSE 0,5–2,5 s | + flags inmediatos en payload |
| Tiempo cierre | Blockers genéricos | Acciones con contexto BOB |
| Confianza multi-cajera | Media | Alta con SSE completo |

---

## 15. Restricciones de negocio a preservar

No romper en implementación futura:

- CBA / combos / asignación manillas.
- Liquidaciones core y scope cajera vs turno.
- Fast Operation / SSE existente.
- Venta directa sin combo allocation.
- `table_label` histórico en tickets.

---

## 16. Matriz resumen — gaps API vs necesidad cajera

| Necesidad cajera (< 3 s) | API hoy | Gap |
|--------------------------|---------|-----|
| Caja abierta | ✅ | — |
| Efectivo/QR/tarjeta esperado | ✅ consola/caja | — |
| Comandas pendientes (count) | ✅ | — |
| Comandas pendientes (BOB) | ❌ | **Sí** |
| Clientes esperando (tiempo) | ❌ | **Sí** |
| Liquidaciones pendientes | ✅ count+amount | — |
| Habitaciones ocupadas | ✅ | — |
| Garzones activos | ❌ | Menor |
| Tiempo promedio espera | ❌ | **Sí** |
| Combo/acompañante/pendiente | ❌ en listado | **Sí** |

---

*Auditoría operativa backend — sin cambios de código. Alineada con frontend/CASHIER_HIGH_PRESSURE_OPERATION_AUDIT.md para planificación por fases.*
