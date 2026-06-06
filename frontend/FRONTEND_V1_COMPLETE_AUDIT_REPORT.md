# FRONTEND V1 — AUDITORÍA COMPLETA PRE QA / PREPRODUCCIÓN

**Fecha:** 2026-06-06  
**Alcance:** Frontend Vue 3 + Vuetify — NightPOS V1 operativo  
**Modo:** Auditoría original (jun 2026) + **actualización post P0/P1**  
**Contexto:** V1-90 a V1-96 completadas. Backend 376 tests verdes. Problema reportado: en Limpieza/Piezas los botones ejecutan la acción pero la pantalla parece no cambiar.

---

## ✅ ACTUALIZACIÓN POST P0 + P1 (2026-06-06)

| Hallazgo | Estado | Evidencia |
|----------|--------|-----------|
| **C-01** Snackbar invisible | ✅ Resuelto | `stores/notify.js` + `NightPosGlobalSnackbar` en `App.vue`; 41 VSnackbars locales eliminados |
| **C-03** Sin loading en botones | ✅ Resuelto | `useActionLoading` en limpieza, piezas, habitaciones, liquidaciones, reportes |
| **C-04** Terminar solo ACTIVE | ✅ Resuelto | `room-services/index` — ACTIVE + DUE |
| **A-01** SSE faltante piezas/habitaciones | ✅ Resuelto | `useRoomOperationalEvents` en 4 pantallas |
| **SSE** `sseConnected` roto | ✅ Resuelto | `connected` / `reconnecting` reales en `useOperationalEvents` + `NightPosSseBanner` |

**Reportes de implementación:** `P0_FEEDBACK_VISUAL_REPORT.md`, `P1_SSE_ROOMS_REPORT.md`  
**Tests:** `pnpm run test` → 4 passing (frontend); `php artisan test` → 376 passing  
**V1-97 impresión:** ❌ No iniciada (según plan)

**Documentos revisados:**
- `NIGHTPOS_V1_DEVELOPMENT_MAP.md`
- `frontend/SSE_1_REPORT.md`, `SSE_2_REPORT.md`, `REPORTS_V1_REPORT.md`
- `frontend/POS_CAT_REPORT.md`, `DIRECT_SALES_REPORT.md`, `DIRECT_SALE_MIXED_PAYMENTS_REPORT.md`
- `frontend/CLEANING_MOBILE_MODE_REPORT.md`, `ROOM_SERVICE_NOTIFICATIONS_REPORT.md`
- `frontend/NAVIGATION_UX_FINAL_REPORT.md`, `SETTLEMENTS_CASH_UI_FIX_REPORT.md`
- `frontend/CASHIER_ORDER_CORRECTION_REPORT.md`, `ORDER_MAIN_ACTIONS_RESTORE_REPORT.md`
- `frontend/V1_91_STABILIZATION_REPORT.md`, `FRONTEND_AUDIT_REPORT.md` (jun 2026)
- Código fuente: 97 páginas `src/pages/nightpos/**/*.vue`, composables SSE/notify, navegación `nightpos-r4.js`

---

## 1. Resumen ejecutivo

El frontend V1 **funciona a nivel de integración API** y cubre el flujo operativo completo (caja, comandas, servicios, liquidaciones, reportes, SSE parcial). Sin embargo, **no está listo para piloto real** sin corregir primero un problema transversal de **feedback visual invisible** que explica directamente el reporte de Limpieza/Piezas.

### Diagnóstico del problema reportado

En `/nightpos/cleaning` y pantallas relacionadas de piezas/habitaciones:

| Lo que el código hace | Lo que el usuario percibe |
|----------------------|---------------------------|
| `await apiCall()` → éxito | Nada visible durante la espera |
| `notify('Habitación marcada…')` | **Sin snackbar en template → toast invisible** |
| `await load()` → actualiza arrays reactivos | Lista puede cambiar de sección sin animación ni confirmación |
| KPIs se recalculan vía `computed` | Cambio numérico sutil, fácil de no notar |
| Sin `:loading` en botones | Botón no muestra progreso |

**Conclusión:** La acción **sí se ejecuta** y en la mayoría de casos **sí refresca datos** vía `load()`. El usuario cree que no pasó nada porque **no hay feedback visual confirmatorio** (snackbar) ni **estado de carga en el botón**. Esto es un fallo de UX crítico, no necesariamente de lógica de negocio.

### Veredicto global

| Dimensión | Estado | Nota |
|-----------|--------|------|
| Integración API | ✅ Sólida | 4/5 |
| Feedback post-acción (snackbar/loading) | ✅ Global + loading crítico (P0) | 4/5 |
| Tiempo real (SSE) | ✅ Piezas/habitaciones cubiertos (P1) — 10 páginas SSE | 4/5 |
| Caja / cobro / venta directa | ✅ Casi listo para QA | 4/5 |
| Limpieza / piezas / habitaciones | ✅ Listo para QA piloto (post P0+P1) | 4/5 |
| Garzón móvil | ⚠️ Funcional sin tiempo real | 3/5 |
| Liquidaciones | ⚠️ Lógica OK, feedback roto en subpáginas | 3/5 |
| Reportes V1-96 | ⚠️ Datos reales, errores invisibles | 3/5 |
| Navegación | ✅ Buena (nightpos-r4) | 4/5 |
| Placeholders | ⚠️ Algunos ocultos por redirect | 3/5 |

**¿Pasar a V1-97 (impresión)?** **Sí, tras QA manual** del flujo pieza→limpieza→liquidación en local. P0 y P1 implementados.

**¿Listo para piloto?** **Casi sí** — validar manualmente flujo limpieza/piezas con dos terminales; cajera y admin ya operativos.

---

## 2. Estado global frontend

### Arquitectura

- **97 páginas** bajo `src/pages/nightpos/`
- **7 páginas con SSE** (`useOperationalEvents`)
- **3 páginas con polling** (30s): `cleaning`, `room-control`, `shift-console`
- **Composables clave:** `useNightPosNotify`, `useOperationalEvents`, `useOnContextChange`, `useRoomDueAlerts`
- **Navegación:** `nightpos-r4.js` — orden operativo correcto post UX final

### Patrón roto transversal: `notify()` sin `<VSnackbar>`

`useNightPosNotify()` crea estado **local por componente**. Si la página llama `notify()` pero no renderiza `<VSnackbar v-model="snackbar.show">`, **ningún mensaje aparece**.

**Páginas operativas afectadas (sin snackbar, con `notify()`):**

| Área | Páginas |
|------|---------|
| **Limpieza / piezas** | `cleaning/index`, `services/room-control`, `services/room-services/*`, `services/bracelets/*`, `services/shows/*` |
| **Habitaciones** | `rooms/dashboard`, `rooms/list`, `rooms/cleaning`, `rooms/available`, `rooms/maintenance`, `rooms/create`, `rooms/[id]/edit` |
| **Liquidaciones** | `settlements/index`, `settlements/waiters`, `girls`, `cleaning`, `history`, `[id]` |
| **Finanzas** | `finance/reports`, `finance/cash-sessions/[id]`, `shift-console`, `shifts/current`, `shifts/history` |
| **Catálogo** | `catalog/prices`, `products/unpriced` |

**Páginas con snackbar correcto (referencia):** `cash/index`, `cash/direct-sale`, `cashier/orders`, `orders/[id]`, `waiter/*`, `girl/index`, `shifts/close`, `shifts/open`.

### Patrón roto: `sseConnected` nunca cambia

Varias páginas registran `on('error')` / `on('open')` para SSE, pero `useOperationalEvents.js` **filtra explícitamente** esos tipos nativos de EventSource (línea 105). El banner "Tiempo real desconectado" en `cleaning/index.vue` **nunca se activa** — es código muerto.

---

## 3. Hallazgos críticos

### C-01 — Snackbar ausente en Limpieza móvil y Control de piezas
**Severidad:** CRÍTICO  
**Área:** Limpieza, piezas, habitaciones  
**Archivos:** `cleaning/index.vue`, `services/room-control/index.vue`, `rooms/cleaning.vue`, `services/room-services/index.vue`

Todas las acciones (`marcar limpia`, `finalizar`, `tocar puerta`) llaman `notify()` tras éxito, pero **no hay `<VSnackbar>`** en el template. El usuario no recibe confirmación visual. Esto reproduce exactamente el problema reportado.

### C-02 — Snackbar ausente en Liquidaciones (pagar / generar)
**Severidad:** CRÍTICO  
**Área:** Finanzas / caja  
**Archivos:** `settlements/index.vue`, `settlements/waiters.vue`, `girls.vue`, `cleaning.vue`

`markSettlementPaid()` y `generateCurrentShiftSettlements()` muestran `notify('Liquidación pagada…')` pero el mensaje es invisible. La tabla sí hace `reload()` — el dato cambia pero el cajero no lo confirma visualmente.

### C-03 — Sin estado `:loading` en botones de acción (Limpieza/Piezas)
**Severidad:** CRÍTICO  
**Área:** UX operativa móvil  

Botones en `cleaning/index.vue` y `room-control/index.vue` no tienen `:loading` ni deshabilitación durante `await apiCall()`. Durante 200–800ms la card permanece idéntica. Combinado con C-01, la experiencia es "presioné y no pasó nada".

### C-04 — Piezas admin: botón "Terminar" solo para status ACTIVE
**Severidad:** CRÍTICO (operación)  
**Archivo:** `services/room-services/index.vue`

```vue
v-if="item.status === 'ACTIVE' && can('room_services.finish')"
```

Piezas en estado `DUE` (tiempo cumplido) **no muestran botón Terminar** en la lista admin, aunque el backend y limpieza móvil sí permiten finalizarlas. Admin debe usar Control de piezas o limpieza móvil.

---

## 4. Hallazgos importantes (ALTO)

### A-01 — SSE no cubre room-control, room-services ni habitaciones
**Severidad:** ALTO  

SSE V1-94 cubrió `cleaning`, `cashier/orders`, `cash`, `shift-console`, `settlements/index`. **No cubrió:**
- `services/room-control` (admin piezas — solo polling 30s)
- `services/room-services` (lista piezas — sin polling ni SSE)
- `rooms/*` (dashboard, cleaning, list — sin tiempo real)

Eventos backend emitidos pero **no consumidos** en frontend operativo de piezas:

| Evento backend | Consumido en |
|--------------|--------------|
| `room_service.created` | shift-console |
| `room_service.due` | cleaning móvil |
| `room_service.finished` | cleaning + shift-console |
| `room.cleaned` | cleaning + shift-console |
| `cleaning.earnings.updated` | cleaning móvil |

**Gap:** `room-control` y `rooms/cleaning` dependen de polling 30s o remount.

### A-02 — Comandas activas (`orders/index`) sin SSE
**Severidad:** ALTO  
**Archivo:** `orders/index.vue`

Tiene snackbar inline propio, pero **sin SSE ni polling**. En operación multi-terminal (garzón crea → admin ve comandas activas), requiere cambiar tab o salir/entrar para ver cambios.

### A-03 — Detalle comanda (`orders/[id]`) sin SSE
**Severidad:** ALTO  

Mutaciones locales tras cobrar/editar funcionan, pero ediciones concurrentes (otro terminal modifica ítems) no se reflejan. Tras `chargeOrder`, solo parchea `status` sin `loadOrder()` completo.

### A-04 — Garzón móvil sin tiempo real
**Severidad:** ALTO  
**Archivos:** `waiter/index.vue`, `waiter/orders/index.vue`, `waiter/orders/[id].vue`

Tienen snackbar correcto. Sin SSE ni polling. Garzón en piso no ve cuando cajera cobra o barra actualiza sin navegar.

### A-05 — Settlement subpages sin SSE
**Severidad:** ALTO  

`settlements/index` tiene SSE, pero `waiters/girls/cleaning` usan `useCurrentShiftSettlements` que solo carga en mount + context change. Pago en subpágina A no actualiza subpágina B hasta F5.

### A-06 — Errores invisibles en Reportes y Consola
**Severidad:** ALTO  
**Archivos:** `finance/reports/index.vue`, `shift-console/index.vue`

`notify(error)` sin snackbar. Si falla carga de reporte, datos anteriores permanecen en pantalla sin aviso.

### A-07 — Cross-page staleness tras acciones locales
**Severidad:** ALTO  

Acción en una pantalla refresca solo esa pantalla:

| Acción en | Se actualiza | Queda stale hasta… |
|-----------|--------------|-------------------|
| `cleaning` marcar limpia | cleaning | room-control (30s poll), rooms/cleaning (F5) |
| `room-control` finalizar | room-control | cleaning (SSE ~400ms), room-services (F5) |
| `room-services` crear pieza | index tras redirect | room-control, cleaning, rooms/dashboard |
| `settlements/cleaning` pagar | esa tabla | cash (SSE), settlements/index (SSE) |

---

## 5. Mejoras (MEDIO / BAJO)

### M-01 — `sseConnected` / banner desconectado es código muerto (MEDIO)
Páginas: `cleaning`, `cash`, `cashier/orders`, `shift-console`. El composable no despacha `open`/`error` a handlers custom.

### M-02 — Silent catch en cargas auxiliares (MEDIO)
- `cleaning/index`: earnings `.catch(() => null)` — card oculta sin error
- `room-services/create`: rooms `.catch(() => [])` — muestra "sin habitaciones" en error de red
- `cash/index`, `direct-sale`, `orders/[id]`: cash session / girls fallan silenciosamente

### M-03 — Post-acción `load()` sin `loading=true` (MEDIO)
En cleaning/room-control, tras marcar limpia el `load()` no activa `VProgressLinear` (solo `loading=false` en finally del load inicial). Refresh es silencioso.

### M-04 — Placeholders y redirects ocultos (MEDIO)
Redirects funcionales (no confunden): `finance/cash-close`, `finance/shift-close`, `finance/movements`, `operation/shifts`, `rooms/index`.

Placeholders visibles (correctos): `settings/printers`, `staff/roles`, `platform/plans`, etc.

### M-05 — Duplicación snackbar: inline vs composable (BAJO)
`orders/index` y `cashier/orders` usan snackbar inline propio; resto usa composable. Inconsistente pero funcional donde hay `<VSnackbar>`.

### M-06 — Navbar demo Materialize residual (BAJO)
Auditoría previa (`FRONTEND_AUDIT_REPORT.md`): búsqueda global, atajos demo, i18n irrelevante en navbar operativo.

### M-07 — `direct-sale` sin `useOnContextChange` (BAJO)
Cambio tenant/sucursal puede dejar estado de caja abierta desactualizado hasta SSE o remount.

---

## 6. Tabla por pantalla

Leyenda: **Refresh** = ¿recarga datos tras acción propia? · **Feedback** = ¿snackbar/loading visible? · **RT** = tiempo real (SSE/poll)

| Ruta | Rol principal | Refresh | Feedback | RT | Estado QA |
|------|---------------|---------|----------|-----|-----------|
| `/nightpos/cleaning` | Limpieza | ✅ load() | ❌ | SSE+30s | 🔴 Bloqueado |
| `/nightpos/services/room-control` | Admin/cajera | ✅ refresh() | ❌ | 30s poll | 🔴 Bloqueado |
| `/nightpos/services/room-services` | Admin | ✅ load() | ❌ | — | 🔴 Bloqueado |
| `/nightpos/services/room-services/create` | Admin | redirect | ❌ | — | 🟡 Parcial |
| `/nightpos/rooms/dashboard` | Admin | mount only | ❌ | — | 🟡 Solo lectura |
| `/nightpos/rooms/cleaning` | Admin | ✅ load() | ❌ | — | 🔴 Bloqueado |
| `/nightpos/rooms/list` | Admin | mount only | ❌ | — | 🟡 Solo lectura |
| `/nightpos/rooms/available` | Admin | mount only | ❌ | — | 🟡 Solo lectura |
| `/nightpos/rooms/maintenance` | Admin | ✅ load() | ❌ | — | 🟡 Parcial |
| `/nightpos/cash` | Cajera | ✅ API resp | ✅ | SSE | 🟢 Listo QA |
| `/nightpos/cash/direct-sale` | Cajera | ✅ local | ✅ | SSE parcial | 🟢 Listo QA |
| `/nightpos/cashier/orders` | Cajera | ✅ loadOrders | ✅ | SSE | 🟢 Listo QA |
| `/nightpos/orders` | Admin/garzón | tab change | ✅ | — | 🟡 Sin RT |
| `/nightpos/orders/:id` | Admin/cajera | ✅ parcial | ✅ | — | 🟡 Sin RT |
| `/nightpos/waiter` | Garzón | mount only | ✅ | — | 🟡 Sin RT |
| `/nightpos/waiter/orders` | Garzón | scope change | ✅ | — | 🟡 Sin RT |
| `/nightpos/waiter/orders/:id` | Garzón | ✅ API resp | ✅ | — | 🟡 Sin RT |
| `/nightpos/settlements` | Admin/cajera | ✅ refreshAll | ❌ | SSE | 🟡 Feedback roto |
| `/nightpos/settlements/waiters` | Admin | ✅ reload | ❌ | — | 🔴 Bloqueado |
| `/nightpos/settlements/girls` | Admin | ✅ reload | ❌ | — | 🔴 Bloqueado |
| `/nightpos/settlements/cleaning` | Admin | ✅ reload | ❌ | — | 🔴 Bloqueado |
| `/nightpos/finance/reports` | Admin | ✅ per tab | ❌ | — | 🟡 Parcial |
| `/nightpos/shift-console` | Admin | ✅ load | ❌ | SSE+30s | 🟡 Feedback roto |
| `/nightpos/shifts/close` | Admin | ✅ load | ✅ | — | 🟢 Listo QA |
| `/nightpos/finance/cash-sessions` | Admin | manual reload | ❌ | — | 🟡 Admin OK |
| `/nightpos/girl` | Chica | mount | ✅ | — | 🟢 Listo QA |
| `/nightpos/services/bracelets` | Cajera | mount | ❌ | — | 🟡 Parcial |
| `/nightpos/services/shows` | Cajera | mount | ❌ | — | 🟡 Parcial |

---

## 7. Tabla por acción (patrón botón → UI)

Foco en el problema reportado y acciones críticas operativas:

| Pantalla | Acción | API | ¿Actualiza UI? | ¿Feedback visible? | Problema |
|----------|--------|-----|----------------|-------------------|----------|
| **cleaning** | Tocar puerta | `POST /cleaning/room-services/{id}/check` | ✅ `await load()` | ❌ | Toast invisible, sin loading en botón |
| **cleaning** | Finalizar servicio | `POST /cleaning/room-services/{id}/finish` | ✅ `await load()` | ❌ | Card sale de "due" sin confirmación |
| **cleaning** | Marcar limpia | `POST /cleaning/rooms/{id}/mark-clean` | ✅ `await load()` | ❌ | **Caso reportado principal** |
| **room-control** | Revisado | `POST /room-services/{id}/check` | ✅ `refresh()` | ❌ | Mismo patrón |
| **room-control** | Finalizar | `POST /room-services/{id}/finish` | ✅ `refresh()` | ❌ | Mismo patrón |
| **room-control** | Marcar limpia | `POST /rooms/{id}/mark-clean` | ✅ `refresh()` | ❌ | Mismo patrón |
| **room-services** | Terminar | `POST /room-services/{id}/finish` | ✅ `load()` | ❌ | Solo ACTIVE, no DUE |
| **rooms/cleaning** | Marcar limpia | `POST /rooms/{id}/mark-clean` | ✅ `load()` | ❌ | Mismo patrón |
| **settlements/cleaning** | Pagar | `POST /settlements/{id}/mark-paid` | ✅ `reload()` | ❌ | Dinero — sin confirmación |
| **settlements/waiters** | Pagar | idem | ✅ `reload()` | ❌ | idem |
| **cash** | Abrir caja | `POST /cash/session/open` | ✅ session ref | ✅ | OK |
| **cash** | Movimiento | `POST /cash/movements` | ✅ session ref | ✅ | OK |
| **cash** | Cerrar caja | `POST /cash/session/close` | ✅ null session | ✅ | OK |
| **cashier/orders** | (solo lectura) | — | ✅ SSE | ✅ | OK |
| **orders/:id** | Cobrar | `POST /orders/{id}/charge` | ⚠️ parcial patch | ✅ | Falta reload completo |
| **direct-sale** | Confirmar venta | `POST /direct-sales` | ✅ clear cart | ✅ | OK |
| **reports** | Aplicar filtros | `GET /reports/*` | ✅ | ❌ | Error invisible |

---

## 8. SSE readiness frontend

### Páginas con SSE activo (7)

| Página | Eventos escuchados | Debounce | Fallback poll |
|--------|-------------------|----------|---------------|
| `cleaning/index` | due, finished, cleaned, earnings.updated | 400ms | 30s ✅ |
| `cash/index` | cash.*, sale.*, settlement.paid | 600ms | — |
| `cash/direct-sale` | session opened/closed | — | — |
| `cashier/orders` | order.* (5 tipos) | 500ms | — |
| `settlements/index` | settlement.*, cash.movement | 600ms | — |
| `shift-console` | orders, sales, cash, settlements, room_service (parcial) | 600ms | 30s ✅ |

### Eventos backend NO consumidos en ninguna página operativa de piezas

- `room_service.created` → solo shift-console (no room-services, no room-control)
- `room_service.due` → solo cleaning móvil (no room-control admin)
- `room.cleaned` → cleaning + shift-console (no rooms/cleaning, no room-control)
- `cleaning.earnings.updated` → solo cleaning móvil

### Eventos order no consumidos donde deberían

- `order.updated`, `order.sent_to_bar` → cashier ✅, pero **no** en `orders/index`, `orders/[id]`, waiter/*
- `order.billed` → cashier ✅, waiter detail ❌

### Recomendación SSE mínima pre-piloto

1. Agregar SSE a `room-control`, `room-services/index`, `rooms/cleaning`, `rooms/dashboard`
2. Eventos mínimos: `room_service.*`, `room.cleaned`, `cleaning.earnings.updated`
3. Arreglar `useOperationalEvents` para exponer estado de conexión real
4. Evitar doble refresh: si SSE conectado, reducir polling a 60s o desactivar en cleaning/room-control

---

## 9. Rutas con problemas

### 🔴 Bloqueadas para QA operativo

| Ruta | Problema principal |
|------|-------------------|
| `/nightpos/cleaning` | Sin snackbar + sin loading en botones |
| `/nightpos/services/room-control` | Sin snackbar + sin SSE |
| `/nightpos/services/room-services` | Sin snackbar + Terminar solo ACTIVE |
| `/nightpos/rooms/cleaning` | Sin snackbar + sin SSE |
| `/nightpos/settlements/waiters` | Sin snackbar |
| `/nightpos/settlements/girls` | Sin snackbar |
| `/nightpos/settlements/cleaning` | Sin snackbar (pago dinero) |

### 🟡 Parciales — usable con F5

| Ruta | Problema |
|------|----------|
| `/nightpos/orders` | Sin SSE |
| `/nightpos/orders/:id` | Sin SSE, charge parcial |
| `/nightpos/waiter/*` | Sin SSE |
| `/nightpos/settlements` | SSE OK pero snackbar roto |
| `/nightpos/finance/reports` | Errores invisibles |
| `/nightpos/shift-console` | SSE OK pero snackbar roto |
| `/nightpos/services/bracelets`, `shows` | Sin snackbar, sin RT |

### 🟢 Listas para QA

| Ruta | Notas |
|------|-------|
| `/nightpos/cash` | SSE + snackbar + refresh completo |
| `/nightpos/cash/direct-sale` | POS-CAT + pago mixto + snackbar |
| `/nightpos/cashier/orders` | SSE + snackbar |
| `/nightpos/orders/:id` | Snackbar OK (falta SSE) |
| `/nightpos/shifts/close` | Checklist V1-96 + snackbar |
| `/nightpos/waiter/orders/:id` | Snackbar OK (falta SSE) |
| `/nightpos/girl` | Snackbar OK |

---

## 10. Recomendación final

### ¿Se puede pasar a V1-97 (Impresión)?

**No inmediatamente.** V1-97 es condicional y no bloquea piloto si no hay ticket obligatorio, pero **V1-98 QA fallaría** en limpieza y liquidaciones sin corregir feedback visual.

### Correcciones obligatorias ANTES de QA (orden de prioridad)

#### Fase P0 — Feedback visual (1–2 días, alto impacto)

1. **Snackbar global o layout-level** — una sola `<VSnackbar>` en layout operativo alimentada por Pinia/provide, O agregar `<VSnackbar>` a las ~25 páginas que usan `notify()` sin renderizarlo
2. **`:loading` + `:disabled` en botones de acción** en `cleaning`, `room-control`, `rooms/cleaning`, `settlements/*`
3. **Feedback optimista opcional:** remover card de lista inmediatamente tras éxito, antes de `load()` (mejora percepción)

#### Fase P1 — Tiempo real piezas (1–2 días)

4. SSE en `room-control`, `room-services/index`, `rooms/cleaning`
5. Escuchar `room_service.due|finished|created`, `room.cleaned`, `cleaning.earnings.updated`
6. Arreglar tracking conexión SSE en composable

#### Fase P2 — Operación multi-terminal (2–3 días)

7. SSE en `orders/index`, `waiter/orders`, `settlements` subpages
8. `loadOrder()` completo tras cobrar en `orders/[id]`
9. Botón "Terminar" para piezas DUE en `room-services/index`

#### Fase P3 — QA formal (V1-98)

10. Checklist manual por rol con las pantallas 🟢
11. Test multi-terminal: limpieza + cajera + admin simultáneos

### ¿Frontend listo para piloto?

| Rol | ¿Listo? | Bloqueante |
|-----|---------|------------|
| Cajera (caja + cobrar) | **Casi sí** | Menor: errores silenciosos auxiliares |
| Admin (reportes + cierre) | **Casi sí** | Snackbar en reportes/shift-console |
| Limpieza móvil | **No** | C-01, C-03 — problema reportado |
| Admin piezas/habitaciones | **No** | C-01, A-01 |
| Garzón móvil | **Casi sí** | Sin RT, usable con navegación |
| Chica | **Sí** | — |

### Estimación esfuerzo corrección pre-piloto

| Fase | Esfuerzo | Impacto |
|------|----------|---------|
| P0 Snackbar + loading | ~1–2 días | Resuelve problema reportado |
| P1 SSE piezas | ~1–2 días | Elimina F5 en operación piezas |
| P2 SSE comandas/garzón | ~2–3 días | Multi-terminal completo |
| **Total mínimo piloto** | **~3–4 días** | Limpieza + cajera + admin |

---

## Anexo A — Respuestas a preguntas de auditoría (Limpieza)

| Pregunta | Respuesta actual |
|----------|------------------|
| ¿Después de marcar limpia se quita de la lista? | **Sí en datos** (`load()` actualiza `cleaning[]`), **no perceptible** sin snackbar |
| ¿Después de finalizar pieza pasa a limpieza? | **Sí en datos** — item sale de `due`/`active`, room aparece en `cleaning` |
| ¿Después de limpiar aparece disponible? | **Sí en backend** — room AVAILABLE; en UI desaparece de cleaning list tras `load()` |
| ¿Se muestra snackbar? | **No** — bug C-01 |
| ¿Se refrescan KPIs? | **Sí** — `kpiCards` es computed sobre refs actualizados |
| ¿SSE funciona? | **Parcial** — eventos sí disparan `debouncedLoad`, banner desconectado no funciona |
| ¿Polling fallback? | **Sí** — 30s en cleaning y room-control |

---

## Anexo B — Materialize / Vuetify

| Componente | Estado |
|-----------|--------|
| `VDialog` en settlements pay | ✅ Funcional, con `:loading` en confirmar |
| `VDataTable` room-services, settlements | ✅ OK |
| `VTabs` reports, cashier orders | ✅ OK |
| `VAlert` due piezas | ✅ OK |
| Iconos `$primary` inválidos | ✅ No encontrados en páginas nightpos |
| Overlays bloqueantes | ✅ No detectados post-fixes vnode |
| Botones touch móvil cleaning | ✅ Tamaño OK en `cleaning-mobile.scss` |

---

*Fin del informe. P0 y P1 implementados 2026-06-06. Próximo paso: QA manual flujo operativo, luego V1-97 (impresión) si el local lo requiere.*
