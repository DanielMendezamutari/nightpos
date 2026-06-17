# Auditoría — Fast Operation Mode (tiempo real)

**Fecha:** 2026-06-16  
**Estado:** Auditoría completada — **P0 implementado 2026-06-16**  
**Implementación:** `FAST_OPERATION_REALTIME_P0_IMPLEMENTATION_REPORT.md`  
**Regla de negocio:** Toda acción operativa debe reflejarse en otros usuarios vía SSE, sin F5.  
**Par frontend:** `frontend/FAST_OPERATION_REALTIME_AUDIT.md`

---

## Resumen ejecutivo

NightPOS tiene **infraestructura SSE funcional** (V1-92 / V1-94): tabla `operational_events`, token efímero, stream `GET /events/stream`, `OperationalEventEmitter` en 15+ use cases.

El caso reportado (**cajera no ve comanda nueva en «Cobrar comandas»**) es **parcialmente cubierto en código** (`order.created` + listener en `cashier/orders`), pero la operación real falla por una **combinación de gaps**:

1. SSE **solo activo mientras la página está montada** — al navegar, la conexión se detiene.
2. Varios use cases de comanda **no emiten** `order.updated`.
3. Latencia del stream (**poll cada 2 s** en PHP) + debounce frontend (500 ms).
4. Cajera **sin banner de desconexión** ni polling de respaldo en esa pantalla.
5. Filtro `cashier_scope=1` puede devolver lista vacía si no hay turno oficial abierto (aunque el garzón sí creó comanda).

**Conclusión:** el backend emite eventos clave, pero **no cumple** la regla «100 % tiempo real sin intervención» en todos los flujos operativos.

---

## Infraestructura SSE (backend)

| Componente | Ubicación | Notas |
|------------|-----------|-------|
| Emisor único | `OperationalEventEmitter` | `emit(tenant, branch, type, payload, targetRole?)` |
| Persistencia | `operational_events` | Poll por `findSince(lastId)` |
| Stream | `EventsStreamController` | `sleep(2)` entre iteraciones; heartbeat cada ~30 s |
| Token | `POST /events/token` | TTL **60 s** (solo establece conexión inicial) |
| Filtro rol | `IssueOperationalEventTokenUseCase` | `cashier`, `waiter`, `cleaning`, `girl`; admin → `null` (ve todo) |
| Broadcast | `target_role = null` | Visible para todos los roles operativos |

Tests: `SseInfrastructureTest`, `Sse2OperativeEventsTest` (21+ escenarios de emisión).

---

## Mapa de eventos emitidos (backend)

### Comandas

| Acción | Use case | Evento | Estado |
|--------|----------|--------|--------|
| Crear comanda | `CreateOrderUseCase` | `order.created` | ✅ Emitido |
| Agregar producto | `AddOrderItemUseCase` | `order.updated` | ✅ Emitido |
| Repartir manillas combo | `SyncOrderItemAllocationsUseCase` | `order.updated` | ✅ Emitido |
| Editar línea (qty, modo, producto) | `UpdateOrderItemUseCase` | `order.updated` | ✅ P0 |
| Quitar línea | `RemoveOrderItemUseCase` | `order.updated` | ✅ P0 |
| Cancelar línea | `CancelOrderItemUseCase` | `order.updated` | ✅ P0 |
| Asignar chica (1 chica) | `AssignOrderItemGirlUseCase` | `order.updated` | ✅ P0 |
| Editar cabecera (mesa, notas) | `UpdateOrderHeaderUseCase` | `order.updated` | ✅ P0 |
| Enviar a barra | `SendOrderToBarUseCase` | `order.sent_to_bar` | ✅ Emitido |
| Cobrar comanda | `ChargeOrderUseCase` | `order.billed`, `sale.created`, `cash.movement.created` | ✅ Emitido |
| Cancelar comanda | `CancelOrderUseCase` | `order.cancelled` | ✅ Emitido |

### Caja y ventas

| Acción | Use case | Evento | Estado |
|--------|----------|--------|--------|
| Abrir caja | `OpenCashSessionUseCase` | `cash.session.opened` | ✅ |
| Cerrar caja | `CloseCashSessionUseCase` | `cash.session.closed` | ✅ |
| Movimiento manual | `RegisterCashMovementUseCase` | `cash.movement.created` | ✅ |
| Venta directa | `CreateDirectSaleUseCase` | `direct_sale.created`, `sale.created`, `cash.movement.created` | ✅ |

### Liquidaciones

| Acción | Use case | Evento | Estado |
|--------|----------|--------|--------|
| Generar liquidaciones | `GenerateCurrentShiftSettlementsUseCase` | `settlement.generated` | ✅ |
| Pagar liquidación | `MarkSettlementPaidUseCase` | `settlement.paid`, `cash.movement.created` | ✅ |

### Habitaciones / piezas / limpieza

| Acción | Use case | Evento | Estado |
|--------|----------|--------|--------|
| Crear pieza | `CreateRoomServiceUseCase` | `room_service.created` | ✅ |
| Pieza vencida (cron) | `RoomServiceDueNotifier` | `room_service.due` | ✅ |
| Finalizar pieza | `FinishRoomServiceUseCase` | `room_service.finished` | ✅ |
| Marcar habitación limpia | `MarkRoomCleanUseCase` | `room.cleaned`, `cleaning.earnings.updated` | ✅ |
| Marcar disponible | `MarkRoomAvailableUseCase` | — | ❌ **No emitido** |
| Mantenimiento habitación | `MarkRoomMaintenanceUseCase` | — | ❌ **No emitido** |

### Turno oficial

| Acción | Evento | Estado |
|--------|--------|--------|
| Abrir / cerrar turno oficial | — | ❌ **No hay emisor SSE** |

---

## Clasificación global (backend)

| Categoría | Cantidad | Ejemplos |
|-----------|----------|----------|
| **Eventos correctos** | 17 tipos | `order.created`, `order.sent_to_bar`, `order.billed`, `cash.*`, `settlement.*`, `room_service.*`, `room.cleaned` |
| **Acciones sin evento** | 8+ | `UpdateOrderItem`, `RemoveOrderItem`, `AssignOrderItemGirl`, `CancelOrderItem`, `UpdateOrderHeader`, `MarkRoomAvailable`, turno oficial |
| **Eventos con payload genérico** | Varios | `refresh: ['orders']` — no incluye diff ni `order_id` obligatorio en todos |
| **Latencia inherente** | Stream | Poll 2 s — no es push instantáneo |

---

## Caso reportado — análisis causal

**Síntoma:** Garzón crea comanda → cajera en «Cobrar comandas» no la ve hasta F5 / cambiar pestaña.

**Hechos en código:**

1. `CreateOrderUseCase` emite `order.created` (broadcast, `target_role = null`).
2. `cashier/orders/index.vue` suscribe `order.created` → `debouncedLoad()` → `GET /orders?scope=cashier_chargeable&cashier_scope=1`.
3. Scope `cashier_chargeable` incluye estados `OPEN` y `SENT_TO_BAR`.

**Hipótesis ordenadas por probabilidad:**

| # | Causa | Evidencia |
|---|-------|-----------|
| 1 | **SSE desconectado sin feedback** | Cajera no muestra `NightPosSseBanner`; fallo de token/contexto es silencioso |
| 2 | **Conexión SSE detenida al navegar** | `stopSse()` en `onUnmounted` — detalle comanda no tiene SSE |
| 3 | **Latencia + percepción** | Backend `sleep(2)` + debounce 500 ms → hasta ~2,5 s; en XAMPP puede ser mayor por buffering |
| 4 | **Ediciones sin evento** | Si el garzón solo *modifica* líneas (`UpdateOrderItem`), cajera no recibe nada |
| 5 | **Filtro turno** | Sin turno oficial abierto, `cashier_scope=1` → lista vacía aunque existan comandas en BD |
| 6 | **Pestaña equivocada** | Usuario en «Cobradas recientes» — comanda nueva no aplica (pero SSE recarga igual el tab activo) |

**Nota:** No es un «evento que no se emite» para *crear* comanda; es fallo de **entrega/percepción/UI** y **cobertura incompleta** del ciclo de vida.

---

## Filtros que afectan listados (cajera)

`ListOrdersUseCase` con `cashier_scope=1`:

- Filtra por `official_shift_id` del turno **abierto** en sucursal.
- Si no hay turno abierto → **[]** (sin error visible).
- `cashier_chargeable`: solo `OPEN`, `SENT_TO_BAR` (no `IN_PREPARATION`/`READY` aunque estén en otros scopes).

El SSE puede disparar reload correctamente y aun así la API devolver vacío por contexto de turno.

---

## Gaps de arquitectura (backend)

1. **Modelo poll, no push:** `sleep(2)` limita tiempo real estricto.
2. **Eventos de mutación parciales:** muchas operaciones de comanda no emiten.
3. **Sin eventos de turno/habitación disponible:** «liberar mesa/habitación» no tiene señal dedicada.
4. **Sin contrato de payload:** consumidores no pueden hacer patch fino; solo full reload.
5. **Sin test SSE para `order.created`:** `Sse2OperativeEventsTest` cubre `order.sent_to_bar` y `order.billed`, no creación.

---

## Plan de corrección priorizado (backend)

### P0 — Crítico operación

1. **Emitir `order.updated`** desde: `UpdateOrderItemUseCase`, `RemoveOrderItemUseCase`, `AssignOrderItemGirlUseCase`, `CancelOrderItemUseCase`, `UpdateOrderHeaderUseCase`.
2. **Estandarizar payload** mínimo: `{ entity: { type, id }, order_id, summary, refresh: [] }`.
3. **Test** `order.created` en `Sse2OperativeEventsTest` (regresión caso cajera).

### P1 — Alta

4. **Reducir latencia stream:** bajar `sleep(2)` a 0,5–1 s o usar long-poll más agresivo en producción.
5. **Eventos habitación:** `room.available`, `room.maintenance` en use cases correspondientes.
6. **Eventos turno:** `shift.opened`, `shift.closed` cuando exista apertura/cierre oficial.

### P2 — Mejora

7. **Payload enriquecido** opcional: `items_count`, `total`, `status` para evitar GET completo.
8. **Documentar** mapa evento ↔ use case en `SSE_2_REPORT.md` (mantener actualizado).

### P3 — Futuro

9. Redis/pub-sub o Laravel broadcasting para push verdadero (<100 ms).
10. Retención/purge de `operational_events` (crecimiento de tabla).

---

## Matriz acción operativa → evento (referencia rápida)

| Flujo operativo | ¿Emite SSE? | Evento(s) |
|-----------------|-------------|-----------|
| Crear comanda | ✅ | `order.created` |
| Editar comanda (líneas) | ⚠️ Parcial | Solo add/allocation |
| Agregar producto | ✅ | `order.updated` |
| Eliminar producto | ❌ | — |
| Cambiar cantidades | ❌ | — |
| Enviar a barra | ✅ | `order.sent_to_bar` |
| Cobrar | ✅ | `order.billed`, `sale.created` |
| Liberar mesa | ⚠️ | Indirecto vía `order.billed`; no evento mesa |
| Abrir caja | ✅ | `cash.session.opened` |
| Cerrar caja | ✅ | `cash.session.closed` |
| Venta directa | ✅ | `direct_sale.created` |
| Liquidaciones generar | ✅ | `settlement.generated` |
| Pago liquidaciones | ✅ | `settlement.paid` |
| Habitaciones / piezas | ✅ Parcial | `room_service.*`, `room.cleaned` |
| Limpieza | ✅ | `room.cleaned`, `cleaning.earnings.updated` |

---

## Plan unificado backend + frontend

La auditoría frontend (`frontend/FAST_OPERATION_REALTIME_AUDIT.md`) complementa este documento. Resumen conjunto:

| Prioridad | Backend | Frontend | Resultado |
|-----------|---------|----------|-----------|
| **P0** | Emitir `order.updated` en edición de líneas/cabecera | Banner + polling en cajera; SSE persistente (layout); composable comandas en detalle y garzón | Comandas visibles sin F5 |
| **P0** | Test `order.created` en SSE | Reload inmediato + toast en cajera | Regresión caso reportado |
| **P1** | Reducir poll stream (2 s → ≤1 s) | Debounce corto en `order.created` | Latencia <2 s percibida |
| **P1** | Eventos turno y `room.available` | Listeners en consola y habitaciones | Contexto operativo sincronizado |
| **P2** | Payload con `order_id` estándar | Patch de fila vs full reload | Menos GET redundantes |

**Orden sugerido:** (1) eventos backend faltantes → (2) banner/polling cajera → (3) SSE global + detalle/garzón → (4) latencia.

---

## Referencias

- `frontend/FAST_OPERATION_REALTIME_AUDIT.md`
- `SSE_1_REPORT.md`, `SSE_2_REPORT.md`
- `CASHIER_SHIFT_SCOPE_FIX_REPORT.md`
- `OperationalEventEmitter.php`, `EventsStreamController.php`
- Tests: `SseInfrastructureTest.php`, `Sse2OperativeEventsTest.php`
