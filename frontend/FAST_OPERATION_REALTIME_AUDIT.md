# Auditoría — Fast Operation Mode (tiempo real) — Frontend

**Fecha:** 2026-06-16  
**Estado:** Auditoría completada — **P0 implementado 2026-06-16**  
**Implementación:** `FAST_OPERATION_REALTIME_P0_IMPLEMENTATION_REPORT.md`  
**Regla de negocio:** Toda acción operativa debe reflejarse en otros usuarios vía SSE, sin F5.  
**Par backend:** `backend/FAST_OPERATION_REALTIME_AUDIT.md`

---

## Resumen ejecutivo

El frontend tiene **infraestructura SSE reutilizable** (`useOperationalEvents`, singleton con `refCount`) y **10 pantallas** la activan. Sin embargo, la arquitectura es **por página**, no global: al salir de una ruta se llama `stopSse()` y la conexión se cierra si no hay otra pantalla suscrita.

El caso reportado (**cajera no ve comanda nueva en «Cobrar comandas»**) tiene código SSE **correcto en la lista** (`order.created` → reload), pero falla en operación real por:

1. **Sin feedback visual** — `sseConnected` se obtiene pero **no se usa**; no hay `NightPosSseBanner` en cajera.
2. **Sin polling de respaldo** — a diferencia de consola de turno / limpieza (30 s), cajera depende 100 % del SSE.
3. **Detalle sin SSE** — al cobrar/corregir en `orders/[id]`, la lista de cajera deja de estar montada; otro usuario editando la misma comanda no se refleja.
4. **Garzón sin SSE** — todas las pantallas `waiter/orders/*` recargan solo al montar o cambiar filtro.
5. **Gaps backend** — ediciones de línea no emiten `order.updated` (ver doc backend); el listener existe pero nunca se dispara.
6. **Latencia acumulada** — poll backend 2 s + debounce frontend 500 ms.

**Conclusión:** el frontend **no cumple** la regla «100 % tiempo real sin intervención» en flujos críticos de comanda, aunque la base técnica ya está implementada en parte.

---

## Infraestructura SSE (frontend)

| Componente | Ubicación | Comportamiento |
|------------|-----------|----------------|
| Composable principal | `composables/useOperationalEvents.js` | Singleton: una `EventSource`, handlers en `Map`, `refCount` |
| Inicio / parada | `start()` / `stop()` por página | `refCount === 0` → cierra conexión |
| Token | `fetchSseToken()` → `GET /events/stream?token=…&last_event_id=…` | Reconexión con backoff 2 s → 30 s |
| Contexto | `watch(context.version)` | Reinicia stream al cambiar tenant/sucursal |
| Logout | `watch(auth.isAuthenticated)` | Detiene SSE y limpia handlers |
| Wrapper habitaciones | `composables/useRoomOperationalEvents.js` | 5 eventos room + debounce 400 ms |
| Banner desconexión | `components/nightpos/layout/NightPosSseBanner.vue` | Alerta si `!connected` |
| Debug | `NightPosStabilityDebug.vue` | `getOperationalEventsDebugState()` |

**No hay SSE en layouts** — ningún `layouts/*.vue` llama `useOperationalEvents`. Cada pantalla gestiona su propio ciclo de vida.

---

## Inventario de pantallas

### Con SSE activo

| Pantalla | Archivo | Eventos escuchados | Acción refresh | Banner | Polling fallback |
|----------|---------|-------------------|----------------|--------|------------------|
| Cobrar comandas | `cashier/orders/index.vue` | `order.created`, `order.sent_to_bar`, `order.updated`, `order.billed`, `order.cancelled` | `loadOrders()` debounce **500 ms** | ❌ | ❌ |
| Consola de turno | `shift-console/index.vue` | `order.created/billed/cancelled`, `sale.*`, `direct_sale.*`, `cash.*`, `settlement.*`, `room_service.created/finished` | `load()` debounce 600 ms | ✅ | ✅ 30 s |
| Caja | `cash/index.vue` | `cash.movement.created`, `cash.session.*`, `sale.created`, `direct_sale.created`, `settlement.paid` | `loadSession()` debounce 600 ms | ❌ | ❌ |
| Liquidaciones | `settlements/index.vue` | `settlement.generated`, `settlement.paid`, `cash.movement.created` | `refreshAll()` debounce 600 ms | ❌ | ❌ |
| Venta directa | `cash/direct-sale.vue` | `cash.session.opened`, `cash.session.closed` | `loadCash()` inmediato | ❌ | ❌ |
| Limpieza (móvil) | `cleaning/index.vue` | vía `useRoomOperationalEvents` | `load()` debounce 400 ms | ✅ | ✅ 30 s |
| Control piezas | `services/room-control/index.vue` | vía `useRoomOperationalEvents` | `refresh()` debounce 400 ms | ✅ | ✅ 30 s |
| Piezas (lista) | `services/room-services/index.vue` | vía `useRoomOperationalEvents` | `load()` debounce 400 ms | ✅ | ❌ |
| Dashboard habitaciones | `rooms/dashboard.vue` | vía `useRoomOperationalEvents` | `load()` debounce 400 ms | ✅ | ❌ |
| Limpieza habitaciones | `rooms/cleaning.vue` | vía `useRoomOperationalEvents` | `load()` debounce 400 ms | ✅ | ❌ |

### Sin SSE (crítico para operación)

| Pantalla | Archivo | Refresh actual | Impacto |
|----------|---------|----------------|---------|
| Lista comandas (admin/cajera) | `orders/index.vue` | Solo `onMounted` + `useOnContextChange` | No ve comandas nuevas de otros |
| Detalle / corrección / cobro | `orders/[id].vue` | Solo `onMounted` + tras acción local | Dos cajeras en la misma comanda desincronizadas |
| Nueva comanda | `orders/new.vue` | — | Bajo (sale de la pantalla al crear) |
| Garzón — listas | `waiter/orders/index.vue` | `onMounted` + `watch(scope)` | No ve comandas de otros garzones ni cambios de cajera |
| Garzón — detalle | `waiter/orders/[id].vue` | Carga inicial + acciones locales | Edición concurrente invisible |
| Garzón — nueva | `waiter/orders/new.vue` | — | Bajo |

**No existe pantalla de barra/cocina** con SSE en el frontend auditado.

---

## Clasificación por flujo operativo

### 1. Crear comanda

| Actor | Pantalla | SSE | ¿Refresca? | Veredicto |
|-------|----------|-----|------------|-----------|
| Garzón crea | `waiter/orders/new` | ❌ | N/A (navega al detalle) | OK local |
| Cajera lista | `cashier/orders` | ✅ escucha `order.created` | ✅ `debouncedLoad` | ⚠️ **Frágil** — sin banner ni fallback |
| Admin lista | `orders/index` | ❌ | ❌ | ❌ **Gap** |
| Consola turno | `shift-console` | ✅ | ✅ | ✅ Correcto (+ polling) |

### 2. Editar comanda (líneas, cantidades, chica, cabecera)

| Actor | Pantalla | Listener | Backend emite | Veredicto |
|-------|----------|----------|---------------|-----------|
| Cajera lista | `cashier/orders` | `order.updated` | ❌ parcial (solo add/allocation) | ❌ **No refresca** en la mayoría de ediciones |
| Detalle cajera | `orders/[id]` | — | — | ❌ **Sin SSE** |
| Garzón detalle | `waiter/orders/[id]` | — | — | ❌ **Sin SSE** |

### 3. Agregar / repartir productos (combo)

| Evento backend | Cajera lista | Detalle | Garzón |
|----------------|--------------|---------|--------|
| `order.updated` (add) | ✅ reload | ❌ sin listener | ❌ |
| `order.updated` (allocations) | ✅ reload | ❌ | ❌ |

### 4. Enviar a barra

| Pantalla | Evento | Comportamiento |
|----------|--------|----------------|
| `cashier/orders` | `order.sent_to_bar` | Reload + toast «Nueva comanda enviada a barra» |
| `shift-console` | — | ❌ **No escucha** `order.sent_to_bar` |
| `orders/index` | — | ❌ |
| Garzón | — | ❌ |

### 5. Cobrar comanda

| Pantalla | Evento | Comportamiento |
|----------|--------|----------------|
| `cashier/orders` | `order.billed` | Reload (comanda sale del tab chargeable) |
| `cash/index` | `sale.created` | Reload sesión caja |
| `settlements` | indirecto vía movimientos | Parcial |
| `orders/[id]` | — | Solo actualiza tras acción propia |

### 6. Cancelar comanda

| Pantalla | Evento | Veredicto |
|----------|--------|-----------|
| `cashier/orders` | `order.cancelled` | ✅ |
| `shift-console` | `order.cancelled` | ✅ |
| `orders/index`, detalle, garzón | — | ❌ |

### 7. Caja (apertura, movimientos, cierre)

| Pantalla | Eventos | Veredicto |
|----------|---------|-----------|
| `cash/index` | `cash.session.*`, movimientos, ventas | ✅ Correcto |
| `cash/direct-sale` | solo apertura/cierre sesión | ✅ Suficiente |
| `cashier/orders` | `loadCashSession()` en cada reload de lista | ✅ Indirecto |

### 8. Venta directa

Emisión backend: `direct_sale.created`, `sale.created`, `cash.movement.created`.  
Frontend: solo `shift-console` y `cash/index` reaccionan. Pantalla de venta directa no necesita reload post-venta.

### 9. Liquidaciones

| Pantalla | Eventos | Veredicto |
|----------|---------|-----------|
| `settlements/index` | `settlement.generated`, `settlement.paid`, `cash.movement.created` | ✅ Correcto |
| Sin banner ni polling | — | ⚠️ Frágil ante desconexión |

### 10. Habitaciones / piezas / limpieza

| Pantalla | Mecanismo | Veredicto |
|----------|-----------|-----------|
| `cleaning`, `room-control` | SSE + banner + polling 30 s | ✅ **Mejor patrón** del proyecto |
| `room-services`, `rooms/*` | SSE + banner, sin polling | ⚠️ Aceptable |
| Eventos no emitidos (`room.available`) | — | Gap backend; UI no puede reaccionar |

### 11. Turno oficial

Ninguna pantalla escucha eventos de turno (backend tampoco emite). Cambios de turno solo vía `useOnContextChange` o F5.

---

## Matriz pantalla × evento × acción

Leyenda: ✅ escucha y refresca · ⚠️ escucha pero incompleto · — no aplica · ❌ no escucha

| Evento | cashier/orders | shift-console | cash | settlements | orders/* | waiter/* |
|--------|----------------|---------------|------|-------------|----------|----------|
| `order.created` | ✅ reload | ✅ reload | — | — | ❌ | ❌ |
| `order.updated` | ✅ reload | ❌ | — | — | ❌ | ❌ |
| `order.sent_to_bar` | ✅ reload+toast | ❌ | — | — | ❌ | ❌ |
| `order.billed` | ✅ reload | ✅ reload | ✅ session | — | ❌ | ❌ |
| `order.cancelled` | ✅ reload | ✅ reload | — | — | ❌ | ❌ |
| `cash.session.*` | indirecto | ✅ | ✅ | — | — | — |
| `cash.movement.created` | — | ✅ | ✅ | ✅ | — | — |
| `sale.created` | — | ✅ | ✅ | — | — | — |
| `direct_sale.created` | — | ✅ | ✅ | — | — | — |
| `settlement.*` | — | ✅ | parcial | ✅ | — | — |
| `room_service.*` | — | parcial | — | — | — | — |
| `room.cleaned` | — | — | — | — | — | — (solo módulo rooms) |

---

## Caso reportado — análisis frontend

**Síntoma:** Garzón crea comanda → cajera en «Cobrar comandas» no la ve hasta F5 / cambiar pestaña.

**Código en `cashier/orders/index.vue`:**

```javascript
on('order.created', debouncedLoad)
// ...
onMounted(() => { loadOrders(); startSse() })
onUnmounted(() => { stopSse() })
```

**Variables `sseConnected` y `sseReconnecting` se declaran pero no se usan en el template** — el operador no sabe si el tiempo real está caído.

**Escenarios que explican el síntoma sin bug en el listener:**

| Escenario | Mecanismo |
|-----------|-----------|
| SSE caído (token, red, XAMPP buffering) | Sin banner ni polling → lista congelada |
| Cajera en detalle de otra comanda | Lista desmontada → `stopSse()` → no recibe eventos |
| Tab «Cobradas recientes» | Comanda nueva no aparece (esperado), pero usuario interpreta como fallo |
| API `cashier_scope=1` sin turno abierto | Reload OK pero respuesta `[]` — parece que SSE no funcionó |
| Solo edición posterior (no creación) | Backend no emite → listener nunca corre |

**Cambiar pestaña funciona** porque dispara `onTabChange` → `loadOrders()` (GET manual), no porque SSE se «arregle».

---

## Patrones detectados

### ✅ Correcto (referencia)

- **`shift-console` + `cleaning` / `room-control`:** SSE + `NightPosSseBanner` + polling 30 s como red de seguridad.
- **`useRoomOperationalEvents`:** composable reutilizable con debounce y toast en `room_service.due`.
- **Singleton `refCount`:** evita múltiples conexiones si dos pantallas SSE están montadas (poco frecuente hoy).

### ⚠️ Evento existe pero UI incompleta

- Cajera escucha `order.updated` pero backend no emite en ediciones comunes.
- `shift-console` no escucha `order.updated` ni `order.sent_to_bar`.
- `cash/index` obtiene `sseConnected` sin mostrarlo.

### ❌ Sin SSE / sin refresh

- Todo el módulo `orders/*` (lista y detalle compartido cajera/admin).
- Todo el módulo `waiter/orders/*`.
- Ningún layout operativo mantiene conexión global.

### 🔄 Polling innecesario vs faltante

| Pantalla | Polling | Evaluación |
|----------|---------|------------|
| `shift-console`, `cleaning`, `room-control` | 30 s | ✅ Apropiado como fallback |
| `cashier/orders` | ninguno | ❌ **Debería tener** fallback mínimo (15–30 s) |
| `orders/index`, `waiter/*` | ninguno | ❌ Crítico sin SSE |

---

## Gaps de arquitectura (frontend)

1. **SSE opt-in por página** — olvidar `startSse()` en una pantalla nueva deja un módulo ciego.
2. **`stopSse()` en unmount** — navegación normal mata la conexión; no hay capa de persistencia en layout.
3. **Sin composable de dominio «comandas»** — duplicación de listeners y debounces entre cajera y consola.
4. **Detalle sin invalidación** — `orders/[id]` es la pantalla más crítica para concurrencia y no tiene SSE.
5. **Banner inconsistente** — 4 pantallas con banner, 6 con SSE sin feedback visual.
6. **Debounce fijo 500–600 ms** — suma latencia al poll backend de 2 s.
7. **Sin estrategia de patch** — siempre full reload; aceptable hoy pero limita percepción de instantaneidad.

---

## Plan de corrección priorizado (frontend)

### P0 — Crítico operación (caso cajera + comandas)

1. **`NightPosSseBanner`** en `cashier/orders/index.vue`, `cash/index.vue`, `settlements/index.vue` — usar `sseConnected` / `sseReconnecting` ya declarados.
2. **Polling fallback 30 s** en `cashier/orders` (mismo patrón que `shift-console`).
3. **SSE global en layout operativo** — iniciar `useOperationalEvents().start()` en layout NightPOS (vertical/horizontal) y **no** detener en unmount de hijos; páginas solo registran handlers. Alternativa mínima: no llamar `stopSse()` en páginas críticas y usar solo `cleanupLocalHandlers` al desmontar.
4. **Composable `useOrderOperationalEvents(reloadFn)`** — centraliza listeners: `order.created`, `order.updated`, `order.sent_to_bar`, `order.billed`, `order.cancelled`; debounce configurable; opcional toast en `order.created` para cajera.
5. **SSE en `orders/[id].vue`** — al recibir evento con mismo `order_id`, `loadOrder()`; banner o indicador «Comanda actualizada por otro usuario».
6. **SSE en `waiter/orders/index.vue` y `[id].vue`** — mismo composable; pull-to-refresh manual como respaldo móvil.

### P1 — Alta

7. **SSE en `orders/index.vue`** — lista admin/supervisión.
8. **Ampliar `shift-console`** — añadir `order.updated`, `order.sent_to_bar`.
9. **Reload inmediato en `order.created`** (sin debounce o debounce 100 ms) en cajera; mantener debounce para ráfagas de `order.updated`.
10. **Toast discreto** en cajera: «Nueva comanda» al crear (complementa el de `sent_to_bar`).

### P2 — Mejora UX

11. **Indicador «En vivo»** en header operativo cuando `connected` (chip verde), no solo alerta cuando falla.
12. **Invalidación selectiva** si payload trae `order_id` — insertar/actualizar fila sin recargar lista completa.
13. **Polling ligero en `settlements` y `cash`** cuando `!connected`.
14. **Tests E2E o composable unit test** — mock EventSource, verificar que handler llama `reloadFn`.

### P3 — Futuro

15. **Service Worker / shared worker** para SSE entre pestañas del mismo usuario.
16. **Optimistic UI** en garzón móvil con reconciliación al evento.

---

## Plan unificado backend + frontend

| Prioridad | Backend | Frontend | Resultado esperado |
|-----------|---------|----------|-------------------|
| **P0** | Emitir `order.updated` en 5 use cases de edición | Banner + fallback polling cajera; SSE layout global; composable comandas en detalle y garzón | Crear/editar comanda visible en cajera ≤3 s sin F5 |
| **P0** | Test regresión `order.created` | Toast + reload inmediato en cajera | Caso reportado cubierto en CI |
| **P1** | Reducir `sleep(2)` en stream | Reducir debounce `order.created` | Latencia percibida <2 s |
| **P1** | Eventos turno/habitación | Listeners en consola y room-control | Turno y mesas sincronizados |
| **P2** | Payload con `order_id` obligatorio | Patch de fila en listas | Menos carga API |

**Orden de implementación recomendado:**

1. Backend P0 (eventos faltantes) — desbloquea listeners ya escritos en cajera.  
2. Frontend P0.1–P0.2 (banner + polling cajera) — mitiga desconexiones sin refactor grande.  
3. Frontend P0.3–P0.6 (layout SSE + composable + detalle/garzón) — cierra concurrencia.  
4. Backend P1 latencia + Frontend P1 debounce — afinación.

---

## Checklist de verificación manual (post-implementación)

- [ ] Garzón crea comanda → cajera en «Por cobrar» la ve en <5 s sin F5.
- [ ] Garzón edita cantidad → cajera ve total actualizado.
- [ ] Cajera A en detalle → Cajera B cobra → A recibe aviso o redirect.
- [ ] Desconectar WiFi 10 s → banner amarillo → reconectar → lista se actualiza (o polling).
- [ ] Cambiar sucursal en contexto → SSE reinicia sin errores en consola.
- [ ] Consola turno refleja `sent_to_bar` y ediciones.
- [ ] Limpieza sigue funcionando con SSE + polling (regresión).

---

## Referencias

- `backend/FAST_OPERATION_REALTIME_AUDIT.md`
- `frontend/src/composables/useOperationalEvents.js`
- `frontend/src/composables/useRoomOperationalEvents.js`
- `frontend/src/pages/nightpos/cashier/orders/index.vue`
- `SSE_1_REPORT.md`, `SSE_2_REPORT.md`, `P1_SSE_ROOMS_REPORT.md`
