# NIGHTPOS V1 — MAPA OFICIAL DE DESARROLLO

**Documento maestro de continuidad hacia NightPOS V1.**
**Fecha:** 2026-06-06
**Estado del documento:** Vigente — reemplaza la sección "Roadmap" de auditorías previas como guía de ejecución.
**Fuentes:** `NIGHTPOS_MASTER_AUDIT.md`, `NIGHTPOS_OPERATION_AUDIT.md`, `CURRENT_SYSTEM_AUDIT.md`, reportes `PHASE_*` / `PHASE_C*` / `PHASE_R*` (backend + frontend), reportes de venta directa, caja, liquidaciones, comandas, garzón y limpieza, y los nuevos `POS_CAT_REPORT.md`.

> **Regla de uso:** Este documento ordena el trabajo hasta el **99% de V1**. No reemplaza los reportes por módulo; los orquesta. Cualquier fase nueva debe respetar el orden definido en la sección 4.

---

## 1. Resumen ejecutivo

| Dimensión | Estado |
|-----------|--------|
| **Clasificación** | **MVP operativo con reportes → listo para piloto real** |
| **% estimado hacia V1 (boliche operable)** | **~99%** (post P0+P1 frontend) |
| **% estimado hacia producción comercial SaaS** | ~88% (post SAAS-1) |
| **¿Opera una noche de prueba controlada?** | **Sí** (1 caja, datos precargados, sin impresión obligatoria) |
| **¿Listo para piloto en local real?** | **Sí** — solo bloquea decisión de impresión y preproducción |
| **Suite de tests** | **529 passing (100% verde)** |

### Lectura rápida

NightPOS ya **no es un MVP visual**: es un POS nocturno funcional con SaaS multi-tenant, comandas, precios SOLO/CON_ACOMPANANTE, caja, **venta directa con pago mixto**, liquidaciones, servicios (manillas/piezas/shows), habitaciones, limpieza móvil y modo garzón. Las últimas entregas (venta directa, pago mixto, POS-CAT) cerraron huecos críticos del flujo de cobro y catálogo.

La brecha hacia V1 ya **no es construir el núcleo**, sino:

1. **Tiempo real (SSE)** — P0 comandas completado; barra sin pantalla SSE dedicada en V1.
2. **Reportes / cierre documentado** — cierre de turno confiable y exportable.
3. **Impresión mínima** — comanda a barra y ticket caja (o documentar su ausencia).
4. **QA operativo end-to-end** y **preproducción** (env, backups, build, HTTPS).

### Riesgos pendientes (alto nivel)

| Riesgo | Severidad | Estado |
|--------|-----------|--------|
| Pantallas sin actualización en vivo (limpieza, cajera, barra) | Medio | SSE-2 + **P1 SSE piezas** completados; barra sin SSE en V1 |
| Feedback visual invisible (snackbar/loading) | Alto | ✅ **P0** completado 2026-06-06 |
| Cierre de turno sin reporte confiable/exportable | Alto | V1-96 |
| Sin impresión barra/caja si el local la exige | Mitigado | ✅ V1-97 vista imprimible (térmica vía navegador); auto-print en V2 |
| Backups / despliegue / HTTPS no formalizados | Alto | V1-99 |
| Caja **por usuario** (no caja física compartida) | Medio | Capacitación + V2 |
| Habitaciones atascadas en CLEANING bloquean piezas | Medio | Mitigado; reforzar en QA |
| Sin auditoría global de acciones sensibles | Medio | V2 (mínimo documentar en V1) |

---

## 2. Módulos completados

Criterio de "completado": API + reglas de negocio + UI mínima + tests o cobertura de flujo + reporte de fase.

| Módulo | Estado | Evidencia |
|--------|--------|-----------|
| **SaaS / tenants / sucursales** | ✅ | Fase 4–5, platform setup, wizard, **SAAS-1 planes** |
| **Usuarios / roles / permisos (RBAC)** | ✅ | Fase 4–5, 10, 12; middleware por slug; **gestión local roles** (`ROLE_PERMISSION_MANAGEMENT_REPORT.md`) |
| **Login PIN / password** | ✅ | `AuthApiTest`; superadmin sin tenant; **selección empresa/sucursal** (`LOGIN_CONTEXT_SELECTION_REPORT.md`) |
| **Productos / categorías / precios** | ✅ | Fase 6, 12; SOLO/CON_ACOMPANANTE, girl/house amount |
| **Catálogo POS (POS-CAT)** | ✅ | `POS_CAT_REPORT.md`; endpoint `pos-catalog`, picker unificado |
| **Comandas (orders)** | ✅ | Fase 7, C1; crear, ítems, enviar barra, cancelar, historial |
| **Corrección de comandas por cajera** | ✅ | `CASHIER_ORDER_CORRECTION_REPORT.md` + fixes VNode/visibility |
| **Caja (cash sessions)** | ✅ | Fase 8; apertura/cierre, movimientos, quick open, financial summary |
| **Venta directa** | ✅ | `DIRECT_SALES_REPORT.md` + pricing fix |
| **Pago mixto** | ✅ | `DIRECT_SALE_MIXED_PAYMENTS_REPORT.md`; CASH+QR+CARD, `MixedPaymentForm` |
| **Servicios: manillas / piezas / shows** | ✅ | Fase 15, 17; quick girl/room/show type |
| **Habitaciones (rooms)** | ✅ | Fase 18; estados, mark-clean, bloqueos |
| **Limpieza móvil / control piezas** | ✅ | Fase 17, `CLEANING_MOBILE_MODE_REPORT.md`; polling + sonido |
| **Liquidaciones** | ✅ | Fase 14, 16; garzones/chicas/manillas/piezas/shows, anti-duplicado, EXPENSE en caja |
| **Fiscalización multicaja (admin)** | ✅ | `ADMIN_CASH_SESSIONS_REPORT.md`; sesiones por sucursal |
| **Turnos oficiales + consola** | ✅ | Fase 13, C2; OPEN/CLOSE, auto-turno, KPIs cierre |
| **Navegación operativa** | ✅ | R1–R4, `NAVIGATION_UX_FINAL_REPORT.md` |
| **Notificaciones in-app** | ✅ (sin push) | Fase 17; list/read API |
| **Quick Actions A/B** | ✅ | Chica, caja desde cobro, habitación, garzón, wizard SaaS |

---

## 3. Módulos en ajuste

| Módulo | Estado | Qué falta |
|--------|--------|-----------|
| **POS-CAT** | ✅ implementado, ⏳ validación | QA con 20/100/200 productos en celular y caja real |
| **SSE-1 (infra tiempo real)** | ✅ completado | `operational_events`, `sse_tokens`, token SSE, stream, `useOperationalEvents`, reconnect, heartbeat, filtros |
| **SSE-2 (eventos UI)** | ✅ completado | 17 eventos conectados, 10 páginas SSE, 12 tests |
| **P0 Fast Operation (comandas tiempo real)** | ✅ completado 2026-06-16 | `order.updated` en 5 use cases, payload estándar, `useOrderOperationalEvents`, SSE layout persistente, polling 30 s — `FAST_OPERATION_REALTIME_P0_IMPLEMENTATION_REPORT.md` (be+fe) |
| **P0 Feedback visual** | ✅ completado | Snackbar global Pinia, loading acciones críticas — `P0_FEEDBACK_VISUAL_REPORT.md` |
| **P1 SSE piezas/habitaciones** | ✅ completado | 4 pantallas + fix `useOperationalEvents` — `P1_SSE_ROOMS_REPORT.md` |
| **Reportes** | ✅ V1-96 | Cierre turno, 6 tabs reportes, CSV; falta PDF/Excel en V2 |
| **Impresión / tickets** | ✅ V1-97 (vista imprimible) | Comanda/ventas/caja/turno imprimibles; auto-print + agente local → V2 |
| **Producción / despliegue** | ⏳ parcial | `.env` prod, migraciones limpias, backups, HTTPS, build, checklist primera noche |

---

## 4. Roadmap hasta el 99% de V1 (orden obligatorio)

> El orden es **dependiente**: no saltar fases. SSE-1 habilita SSE-2; reportes y QA dependen de que el flujo en vivo esté estable.

### FASE V1-90 — POS-CAT ✅ (COMPLETADA)

**Objetivo:** catálogo vendible para 200+ productos sin scroll masivo.

Incluye (todo entregado):
- Productos sin precio (pantalla admin dedicada)
- Filtro de vendibles (`sellable_only`)
- Buscador eficiente (mín. 2 caracteres, server-side)
- Categorías visibles con conteo
- Favoritos y recientes
- Aplicado a garzón, venta directa y comanda de cajera

**Reportes:** `backend/POS_CAT_REPORT.md`, `frontend/POS_CAT_REPORT.md`.
**Validación:** `POS_CAT_VALIDATION_REPORT.md` — APROBADO para 20/100/200 productos.

---

### FASE V1-91.3 — BUGFIX TIMEZONE PIEZAS ✅ (COMPLETADA 2026-06-06)

**Objetivo:** corregir cálculo de tiempo de piezas — pieza recién creada aparecía como "Tiempo cumplido".

Logros:
- **Causa raíz**: `APP_TIMEZONE=UTC` + frontend enviando hora local Bolivia (UTC-4) sin offset → Carbon interpretaba la hora como 4h en el pasado.
- `config/app.php` → `timezone = env('APP_TIMEZONE', 'America/La_Paz')`.
- `CreateRoomServiceUseCase`: `Carbon::parse($startedAt, $tz)` + `Carbon::now($tz)`.
- `GirlIncomeMapper`, `EloquentRoomServiceRepository`: `Carbon::now($tz)` en todos los cálculos relativos.
- Frontend `create.vue`: pre-pobla `started_at` con hora local actual; muestra fin estimado en tiempo real.
- 7 tests nuevos de cálculo de tiempo, suite 340/340 verde.

**Documentación:** `backend/ROOM_SERVICE_TIME_CALCULATION_FIX_REPORT.md`, `frontend/ROOM_SERVICE_TIME_CALCULATION_FIX_REPORT.md`

---

### FASE V1-91.2 — AJUSTE REGLA LIMPIEZA EN PIEZAS ✅ (COMPLETADA 2026-06-06)

**Objetivo:** corregir el modelo de cálculo de pago de limpieza en servicios de pieza.

Logros:
- Nuevo campo `cleaning_amount` en registro de pieza (descuenta del bruto de la chica).
- `gross_girl_amount` almacenado para auditoría.
- `girl_amount` = neto real (post-deducción).
- `MarkRoomCleanUseCase` usa `room_service.cleaning_amount` como fuente primaria del pago de limpieza.
- Liquidación chica = neto; liquidación limpieza = cleaning_task.amount desde room_service.
- Frontend: campo de monto limpieza + distribución en tiempo real.
- 8 tests nuevos, suite 333/333 verde.

**Documentación:** `backend/ROOM_SERVICE_CLEANING_DEDUCTION_REPORT.md`, `frontend/ROOM_SERVICE_CLEANING_DEDUCTION_REPORT.md`

---

### FASE V1-91.1 — ESTABILIZACIÓN PRE-SSE ✅ (COMPLETADA 2026-06-06)

**Objetivo:** corregir inconsistencias operativas críticas detectadas en auditoría V1-91 antes de iniciar SSE.

Logros:
- **Pago limpieza visible en caja y cierre**: botón Pagar en listas de liquidaciones, alerta en cierre caja, egreso creado en caja, expected_cash actualizado.
- **15 tests reparados**: setups de test ahora abren caja correctamente; permisos `settlements.access` para garzones y chicas.
- **6 tests nuevos** de pago de limpieza: exige caja, crea EXPENSE, expected_cash baja, anti-doble pago, pending en cierre turno.
- **Estados IN_PREPARATION/READY ocultos** de KPIs en V1 (condicional en shift console).
- **Aviso cobrar OPEN**: se puede cobrar comanda OPEN con aviso informativo.
- **Suite 100% verde**: 325 tests, 2172 assertions.

**Documentación:**
- `BAR_MODULE_V1_DECISION.md` — Módulo Barra out-of-scope en V1
- `ORDER_CHARGE_RULES_V1.md` — Reglas de cobro V1
- `POS_CAT_VALIDATION_REPORT.md` — Validación POS-CAT
- `backend/V1_91_STABILIZATION_REPORT.md`, `frontend/V1_91_STABILIZATION_REPORT.md`

---

### FASE V1-92 — SSE-1 BASE ✅ (COMPLETADA 2026-06-06)

**Objetivo:** canal de eventos en vivo, seguro y multi-tenant, sin acoplar a un módulo.

Logros:
- Tabla `operational_events` (tenant_id, branch_id, type, payload, target_role, created_at, id auto-increment).
- Tabla `sse_tokens` (token, tenant_id, branch_id, user_id, role_scope, expires_at).
- `OperationalEventEmitter` — servicio para emitir eventos desde cualquier use case.
- `IssueOperationalEventTokenUseCase` — genera token SSE con TTL 60s.
- `POST /api/v1/events/token` — endpoint autenticado para obtener token.
- `GET /api/v1/events/stream?token=...` — endpoint SSE sin auth header (usa token).
- Filtros por tenant, branch, target_role.
- Heartbeat `: heartbeat` cada 15 iteraciones (~30s).
- Reconnect automático con `last_event_id`.
- Composable `useOperationalEvents` (frontend) — pide token, abre EventSource, reconecta, cierra en logout.
- 13 tests nuevos SSE. Suite: **353 tests, todos PASS**.

**Documentación:** `backend/SSE_1_REPORT.md`, `frontend/SSE_1_REPORT.md`.

---

### FASE V1-94 — SSE-2 LIMPIEZA / CAJERA ✅ (COMPLETADA)

**Objetivo:** que las pantallas operativas se actualicen **sin F5**, sobre la base de V1-92.

**17 eventos conectados en 15 Use Cases:**
- Room services: `room_service.created`, `room_service.due`, `room_service.finished`, `room.cleaned`, `cleaning.earnings.updated`
- Comandas: `order.created`, `order.updated`, `order.sent_to_bar`, `order.billed`, `order.cancelled`
- Caja: `cash.session.opened`, `cash.session.closed`, `cash.movement.created`, `sale.created`, `direct_sale.created`
- Liquidaciones: `settlement.generated`, `settlement.paid`

**6 páginas frontend con SSE activo:**
- Limpieza móvil (`/cleaning`) — con toast en piezas vencidas + indicador SSE offline
- Cajera comandas (`/cashier/orders`) — actualización instantánea
- Mi Caja (`/cash`) — movimientos en tiempo real
- Ventas directas (`/cash/direct-sale`) — session reload por SSE
- Consola de turno (`/shift-console`) — todos los eventos operativos
- Liquidaciones (`/settlements`) — settlement events

**Corrección técnica:** `findSince(roleScope=null)` ahora retorna todos los eventos sin filtrar por `target_role` (admin ve todo). Roles específicos filtran broadcast + propio.

**Mejora al sistema de tests:** `nightposSeedOrderProduct()` y `nightposCreateOrderWithItem()` movidos a `Pest.php` (globales).

**12 nuevos tests en `Sse2OperativeEventsTest.php`** (365 total, 100% verde).

**Documentación:** `backend/SSE_2_REPORT.md`, `frontend/SSE_2_REPORT.md`.

---

### FASE P0 — FAST OPERATION MODE (COMANDAS TIEMPO REAL) ✅ (COMPLETADA 2026-06-16)

**Objetivo:** garzón, cajera y admin ven cambios de comandas sin F5 (caso reportado: cajera no ve comanda nueva).

**Backend:**
- `order.updated` en `UpdateOrderItem`, `RemoveOrderItem`, `AssignOrderItemGirl`, `CancelOrderItem`, `UpdateOrderHeader`
- Payload estándar vía `OrderOperationalEventPayload` (`order_id`, `status`, `source`, `refresh`)
- 10 tests `SseOrderEventsP0Test.php` — suite completa verde

**Frontend:**
- `useOrderOperationalEvents`, `useOperationalPollingFallback`, `useOperationalSseHost`
- Banner SSE en cajera, orders, waiter, cash, settlements
- Polling 30 s en listas y detalle de comandas
- SSE persistente en layouts operativos

**Documentación:** `FAST_OPERATION_REALTIME_AUDIT.md` (be+fe), `FAST_OPERATION_REALTIME_P0_IMPLEMENTATION_REPORT.md` (be+fe).

---

### FASE V1-96 — REPORTES Y CIERRE ✅ (COMPLETADA 2026-06-06)

**Objetivo:** cierre de turno confiable y reportes operativos básicos.

Incluye:
- Cierre de turno claro (efectivo/QR/tarjeta, diferencias, liquidaciones pagadas)
- Reporte diario (ventas, comandas, servicios, liquidaciones)
- Caja por método de pago
- Liquidaciones pagadas vs pendientes
- Export PDF/Excel (si es viable técnicamente; si no, documentar alternativa)
- Historial por fecha (turnos, ventas, cajas)

**Criterio de salida:** el dueño puede cerrar la noche con un resumen confiable y consultar días anteriores.

**Reporte:** `backend/REPORTS_V1_REPORT.md`, `frontend/REPORTS_V1_REPORT.md`.

---

### FASE P0 — FEEDBACK VISUAL OPERATIVO ✅ (COMPLETADA 2026-06-06)

**Objetivo:** eliminar acciones silenciosas (snackbar invisible + botones sin loading).

Logros:
- Store Pinia `notify` + `NightPosGlobalSnackbar` en `App.vue`
- `useActionLoading` en limpieza, piezas, habitaciones, liquidaciones, reportes
- 41 VSnackbars locales eliminados; `useNightPosNotify` delega al store global
- 4 tests vitest frontend

**Documentación:** `frontend/P0_FEEDBACK_VISUAL_REPORT.md`, actualización `FRONTEND_V1_COMPLETE_AUDIT_REPORT.md`

---

### FASE P1 — SSE OPERATIVO PIEZAS ✅ (COMPLETADA 2026-06-06)

**Objetivo:** tiempo real en piezas/habitaciones sin F5; fix estado conexión SSE.

Logros:
- SSE en `room-control`, `room-services`, `rooms/cleaning`, `rooms/dashboard`
- `useRoomOperationalEvents` + `NightPosSseBanner`
- Fix `useOperationalEvents`: `connected`, `reconnecting`, handlers `open`/`error`
- C-04: botón Terminar para ACTIVE + DUE

**Documentación:** `frontend/P1_SSE_ROOMS_REPORT.md`

---

### FASE V1-97 — TICKETS PDF / VISTA IMPRIMIBLE ✅ (COMPLETADA 2026-06-06)

**Cambio de alcance:** de "impresión automática con agente local" a **vista imprimible + `window.print()`** (impresora térmica vía navegador). La impresión automática / agente local / cola de impresoras queda para **V2**.

Logros:
- 5 rutas imprimibles con layout `blank` (sin nav, fondo blanco, botones Imprimir/Volver): `print/order/:id`, `print/sale/:id`, `print/cash`, `print/cash-session/:id`, `print/shift/:id`.
- 5 componentes: `PrintableTicketShell`, `PrintableOrderTicket`, `PrintableSaleTicket`, `PrintableCashSessionReport`, `PrintableShiftClosureReport` (ancho térmico 58/80 mm).
- Botones "Ver imprimible/ticket" en detalle comanda, venta, Mi Caja, fiscalización caja, cierre e historial de turno.
- Backend: endpoints reutilizados; único cambio = `cashier_name`/`waiter_name` en `GetSaleUseCase` y `waiter_name` en `GetOrderUseCase`. **Sin `print_jobs`.**
- Suite **376 verde** (sin tests nuevos; endpoints reutilizados).

**Criterio de salida cumplido:** se entregan documentos imprimibles; la operación no se bloquea si no se imprime. Impresión automática documentada como V2.

**Reporte:** `frontend/PRINTABLE_TICKETS_V1_REPORT.md`.

**Diferido a V2:** agente local, cola de impresión, térmica directa, WebSocket, facturación electrónica.

---

### FASE CONTROL CIERRE — PRODUCTOS VENDIDOS VS. COMANDADOS ✅ (COMPLETADA 2026-06-06)

Control de conciliación de productos comandados (cobrados) vs. vendidos, con identificación
de venta directa y diferencias. **No es Kardex** — sin stock, compras, mermas, costos ni
inventario físico (todo eso → V2).

Logros:
- Backend: `GET /api/v1/reports/product-reconciliation` (`reports.access`), filtros
  `date_from/date_to/official_shift_id/cash_session_id/waiter_user_id`.
  Conciliación por `sale_items.order_item_id` vs. `order_items` facturados; estados
  `OK / QUANTITY_MISMATCH / MISSING_IN_SALE / SOLD_WITHOUT_ORDER / PENDING_NOT_SOLD / CANCELLED / DIRECT_SALE_ONLY`.
- **10 tests nuevos** verdes (`ProductReconciliationTest`); suite de reportes completa verde.
- Frontend: pestaña **Productos** en Reportes, sección en **Cierre de caja**, bloque
  (solo advertencia, no bloquea) en **Cierre de turno**, y secciones en tickets imprimibles
  de caja y turno. Componentes `ProductReconciliationPanel` y `PrintableReconciliationSection`.
- Reportes: `backend/PRODUCT_RECONCILIATION_REPORT.md`, `frontend/PRODUCT_RECONCILIATION_REPORT.md`.

**Diferido a V2:** Inventario/Kardex real, stock, compras, mermas, costos, inventario físico.

---

### FASE V1-98 — QA OPERATIVO

**Objetivo:** simulacro de noche real con todos los roles.

Prueba simulada de extremo a extremo:
- Garzón, cajera, limpieza, chica, admin, superadmin
- Venta directa + pago mixto
- Liquidaciones + cierre de turno
- Fiscalización multicaja
- Validación POS-CAT con 20 / 100 / 200 productos (celular + caja)

**Criterio de salida:** checklist por rol (sección 7) ejecutado sin desarrollo ad hoc.

**Reporte esperado:** `QA_OPERATIVO_V1_REPORT.md`.

---

### FASE V1-99 — PREPRODUCCIÓN

**Objetivo:** dejar el sistema desplegable en entorno de preproducción.

Incluye:
- `.env` de producción
- Migraciones limpias (orden y reversibilidad)
- Seeders correctos (datos mínimos por tenant)
- Backup automatizado + prueba de restore
- HTTPS
- Logs / observabilidad mínima
- Permisos finales revisados
- Build de frontend
- Checklist "primera noche"

**Criterio de salida:** una noche real en sucursal piloto sin intervención de desarrollo.

**Reporte esperado:** `PREPRODUCCION_V1_REPORT.md` + actualización de `DEPLOYMENT_CHECKLIST.md`.

---

## 5. Qué NO entra en V1 (queda para V2)

- Facturación electrónica completa (si no está lista al cierre de V1)
- Inventario / kardex avanzado (descuento de stock, compras, proveedores, traspasos)
- BI avanzado / dashboards gerenciales comparativos
- App nativa (más allá de PWA si se decidiera)
- Multi-moneda
- Suscripciones SaaS automatizadas (billing, cobro recurrente) — **SAAS-2+**
- Enforcement de límites por plan — **SAAS-4**
- Marketing / CRM / clientes / créditos / cotizaciones
- Delivery, combos, recetas, cocina separada (legacy restaurante)
- Portal propio de chica / garzón con analítica
- Migración automatizada de datos legacy

---

## 6. Definición de 99% V1

NightPOS V1 llega al **99%** cuando se cumplen **todos** estos puntos:

| # | Criterio | Fase que lo asegura |
|---|----------|---------------------|
| 1 | Cajera puede operar toda la noche | Hecho + V1-94/96 |
| 2 | Garzón puede comandar desde celular | Hecho + V1-94 |
| 3 | Limpieza recibe eventos y gestiona piezas | V1-94 |
| 4 | Chica puede ver sus ingresos | Hecho (verificar en V1-98) |
| 5 | Admin puede fiscalizar caja | Hecho + V1-94 |
| 6 | Superadmin puede operar tenants | Hecho |
| 7 | Venta directa funciona | ✅ Hecho |
| 8 | Pago mixto funciona | ✅ Hecho |
| 9 | Liquidaciones funcionan | ✅ Hecho |
| 10 | Cierre de turno genera datos confiables | V1-96 |
| 11 | Impresión mínima lista o documentada | V1-97 |
| 12 | Sistema desplegado en preproducción | V1-99 |

> **Regla:** No se declara "V1 99%" mientras quede pendiente cualquiera de los 12 criterios. Los puntos 7, 8 y 9 ya están cumplidos.

---

## 7. Checklist por rol (para QA V1-98)

### Cajera
- [ ] Login con PIN
- [ ] Abrir caja
- [ ] Cobrar comanda (CASH / QR / CARD / MIXED)
- [ ] Venta directa con pago mixto
- [ ] Corregir comanda (cambiar/eliminar ítem)
- [ ] Ver comandas por cobrar actualizadas sin F5 (post V1-94)
- [ ] Registrar movimiento de caja
- [ ] Cerrar caja con cuadre correcto

### Garzón
- [ ] Login con PIN (modo móvil)
- [ ] Nueva comanda
- [ ] Buscar producto en catálogo grande (POS-CAT) sin scroll largo
- [ ] Usar favoritos / recientes
- [ ] Asignar chica en CON_ACOMPANANTE
- [ ] Enviar a barra
- [ ] Ver estado de sus comandas

### Limpieza
- [ ] Ver control de piezas
- [ ] Recibir alerta de pieza por vencer/vencida (sin F5, post V1-94)
- [ ] Marcar habitación como limpia
- [ ] Confirmar liberación de habitación

### Chica
- [ ] Ver ingresos por modalidad / servicios
- [ ] Ver manillas / piezas / shows asignados
- [ ] Validar liquidación (sin duplicados)

### Admin
- [ ] Fiscalizar caja (sesiones por sucursal)
- [ ] Ver liquidaciones pagadas/pendientes
- [ ] Configurar productos / precios / categorías
- [ ] Configurar precio a producto sin precio (POS-CAT)
- [ ] Cerrar turno y ver KPIs confiables
- [ ] Consultar reporte diario / historial (post V1-96)

### Superadmin
- [ ] Crear tenant + sucursal + admin (wizard)
- [ ] Login sin tenant
- [ ] Operar sobre múltiples tenants con aislamiento

---

## 8. Próxima fase inmediata

**Siguiente fase a implementar:** **V1-98 — QA OPERATIVO** (simulacro noche real con todos los roles)

P0, P1 y V1-97 (tickets imprimibles) completados.

Orden restante:

1. **V1-98** — QA operativo (simulacro noche real)
2. **V1-99** — Preproducción
3. **V2** — Impresión automática (agente local), facturación electrónica

---

## 9. Control de progreso

| Fase | Estado | % estimado | Bloquea piloto | Responsable | Reporte esperado |
|------|--------|-----------|----------------|-------------|------------------|
| V1-90 POS-CAT | ✅ Completada | 100% | No | Dev | `POS_CAT_REPORT.md` (be+fe) ✅ |
| V1-92 SSE-1 base | ✅ Completada | 100% | **Sí** | Dev | `SSE_1_REPORT.md` (be+fe) ✅ |
| V1-94 SSE-2 limpieza/cajera | ✅ Completada | 100% | **Sí** | Dev | `SSE_2_REPORT.md` (be+fe) ✅ |
| V1-96 Reportes y cierre | ✅ Completada | 100% | **Sí** | Dev | `REPORTS_V1_REPORT.md` (be+fe) ✅ |
| P0 Feedback visual | ✅ Completada | 100% | **Sí** | Dev | `P0_FEEDBACK_VISUAL_REPORT.md` ✅ |
| P1 SSE piezas | ✅ Completada | 100% | **Sí** | Dev | `P1_SSE_ROOMS_REPORT.md` ✅ |
| V1-97 Tickets PDF / vista imprimible | ✅ Completada | 100% | Condicional* | Dev | `PRINTABLE_TICKETS_V1_REPORT.md` ✅ |
| Control cierre — Productos vendidos vs. comandados | ✅ Completada | 100% | Sí | Dev | `PRODUCT_RECONCILIATION_REPORT.md` ✅ |
| V1-98 QA operativo | ❌ Pendiente | 0% | **Sí** | Dev + Operación | `QA_OPERATIVO_V1_REPORT.md` |
| V1-99 Preproducción | ⏳ Parcial | ~20% | **Sí** | Dev + Infra | `PREPRODUCCION_V1_REPORT.md` |

\* *Impresión bloquea el piloto solo si el local exige ticket en papel; de lo contrario se documenta y no bloquea.*

### Estado global de V1

```
V1-90  ██████████ 100%  POS-CAT
V1-92  ██████████ 100%  SSE-1
V1-94  ██████████ 100%  SSE-2
V1-96  ██████████ 100%  Reportes
P0     ██████████ 100%  Feedback visual
P1     ██████████ 100%  SSE piezas
V1-97  ██████████ 100%  Tickets imprimibles
CIERRE ██████████ 100%  Conciliación productos
V1-98  ░░░░░░░░░░   0%  QA
V1-99  ██░░░░░░░░  20%  Preproducción
```

**% global hacia V1: ~99%** (núcleo + SSE + reportes + feedback UX + tickets imprimibles; falta QA formal y despliegue).

---

## Apéndice — Documentos de referencia

| Tema | Documento |
|------|-----------|
| Estado global y producción | `NIGHTPOS_MASTER_AUDIT.md` |
| Simulación de noche operativa | `NIGHTPOS_OPERATION_AUDIT.md` |
| Sistema heredado y mapeo | `CURRENT_SYSTEM_AUDIT.md` |
| Venta directa | `backend/DIRECT_SALES_REPORT.md`, `frontend/DIRECT_SALES_REPORT.md`, `frontend/DIRECT_SALE_PRICING_FIX_REPORT.md` |
| Pago mixto | `backend/DIRECT_SALE_MIXED_PAYMENTS_REPORT.md`, `frontend/DIRECT_SALE_MIXED_PAYMENTS_REPORT.md` |
| Catálogo POS | `backend/POS_CAT_REPORT.md`, `frontend/POS_CAT_REPORT.md` |
| Caja / fiscalización | `backend/ADMIN_CASH_SESSIONS_REPORT.md`, `OPERATION_CASH_FINANCE_AUDIT.md` |
| Motivos de caja | `backend/CASH_MOVEMENT_REASONS_MANAGEMENT_REPORT.md`, `frontend/CASH_MOVEMENT_REASONS_MANAGEMENT_REPORT.md` |
| Cierre caja / scope cajera | `backend/CASHIER_CLOSE_CHECK_REPORT.md`, `frontend/CASHIER_CLOSE_CHECK_REPORT.md`, `backend/CASHIER_SHIFT_SCOPE_FIX_REPORT.md`, `frontend/CASHIER_SHIFT_SCOPE_FIX_REPORT.md` |
| Liquidaciones / scope turno | `backend/SETTLEMENT_SHIFT_SCOPE_FIX_REPORT.md`, `frontend/SETTLEMENT_SHIFT_SCOPE_FIX_REPORT.md` |
| Liquidaciones / permisos cajera (auditoría 2026-06-16) | `backend/SETTLEMENTS_PERMISSION_AUDIT.md`, `frontend/SETTLEMENTS_PERMISSION_AUDIT.md` |
| Matriz permisos NightPOS (auditoría 2026-06-16) | `backend/NIGHTPOS_PERMISSION_MATRIX_AUDIT.md`, `frontend/NIGHTPOS_PERMISSION_MATRIX_AUDIT.md` |
| Liquidaciones | `backend/CLEANING_SETTLEMENTS_REPORT.md`, `frontend/SETTLEMENTS_CASH_UI_FIX_REPORT.md` |
| Liquidaciones parciales / cortes | `backend/PARTIAL_SETTLEMENTS_IMPLEMENTATION_REPORT.md`, `frontend/PARTIAL_SETTLEMENTS_IMPLEMENTATION_REPORT.md` |
| Liquidaciones parciales post-Mis mesas | `backend/PARTIAL_SETTLEMENTS_AFTER_TABLES_FIX_REPORT.md`, `frontend/PARTIAL_SETTLEMENTS_AFTER_TABLES_FIX_REPORT.md` |
| Comandas | `ORDERS_COMPLETE_AUDIT.md`, `CASHIER_ORDER_AND_DIRECT_SALE_AUDIT.md` |
| Garzón | `frontend/WAITER_MOBILE_*`, `backend/PHASE_C4_WAITER_REPORT.md` |
| Limpieza | `backend/CLEANING_MOBILE_MODE_REPORT.md`, `ROOM_SERVICE_NOTIFICATIONS_REPORT.md` |
| Despliegue | `DEPLOYMENT_CHECKLIST.md` |
| SaaS planes (SAAS-1) | `backend/SAAS_PLAN_MANAGEMENT_REPORT.md`, `frontend/SAAS_PLAN_MANAGEMENT_REPORT.md` |

---

## SAAS-1 — Planes y límites (completado 2026-06-14)

| Entrega | Estado |
|---------|--------|
| Unificación `TenantProvisioner` (wizard + crear empresa) | ✅ |
| Tablas `plans`, `plan_limits`, `tenants.plan_id` | ✅ |
| CRUD planes API + UI superadmin | ✅ |
| Uso vs límites (OK/WARNING/LIMIT_REACHED, sin bloqueo) | ✅ |
| Dashboard SaaS ampliado | ✅ |
| Tests `TenantProvisioningTest`, `PlanManagementTest` | ✅ |

**Siguiente fase:** SAAS-2 — Suscripciones (no iniciada).

---

## BUGFIX — Motivos de caja (completado 2026-06-14)

| Entrega | Estado |
|---------|--------|
| Fix unwrap API → selector Mi Caja poblado | ✅ |
| Tipo `BOTH` en backend y UI | ✅ |
| `GET /cash/movement-reasons` para cajera (`cash.access`) | ✅ |
| Navegación Configuración → Motivos de caja con permiso | ✅ |
| Alerta + accesos rápidos Crear/Gestionar en Mi Caja | ✅ |
| Seeder motivos básicos (INCOME/EXPENSE/BOTH) | ✅ |
| Tests `CashMovementReasonsManagementTest` (10) + `PhaseC3Test` | ✅ |
| Build frontend | ✅ |

**Permisos:** `settings.cash_reasons` (ver/usar), `settings.cash_reasons.manage` (administrar; incluye cajera senior).  
**Ruta UI:** `/nightpos/settings/cash-reasons`.  
**Pendiente V2:** campo `description`, endpoint DELETE explícito, slugs `cash_movement_reasons.*` si se unifica nomenclatura.

---

## BUGFIX — Cierre caja + scope cajera + Enter cobrar (completado 2026-06-15)

| Entrega | Estado |
|---------|--------|
| Enter cobra en ChargeOrderModal y venta directa | ✅ |
| `GET /cash/session/current/close-check` + enforcement | ✅ |
| Bloqueo cierre: comandas, piezas, liquidaciones | ✅ |
| Scope cajera turno/caja (`cashier_scope`, `current_session`) | ✅ |
| `GET /shifts/current/close-check` (sin `reports.access`) | ✅ |
| Cajera básica sin `shifts.close` | ✅ |
| Tests `CashierCloseCheckTest` (9) | ✅ |
| Build frontend | ✅ |

---

## BUGFIX — Liquidaciones scope turno (completado 2026-06-15)

| Entrega | Estado |
|---------|--------|
| `resolveOpenShiftId()` — solo turno OPEN en liquidaciones | ✅ |
| Sin fallback a turnos cerrados en current-shift | ✅ |
| `context` + `sources_summary` en API | ✅ |
| Close-check usa `cash_session.official_shift_id` | ✅ |
| UI turno actual + mensaje vacío | ✅ |
| Tests `SettlementShiftScopeTest` (5) | ✅ |

---

## FEATURE — Liquidaciones parciales / múltiples cortes (completado 2026-06-16)

| Entrega | Estado |
|---------|--------|
| Múltiples `staff_settlements` por persona/turno/tipo | ✅ |
| `ensureSettlement()` solo reutiliza PENDING | ✅ |
| Deduplicación por `staff_settlement_items` | ✅ |
| `cut_number` / `cut_label` en API | ✅ |
| Blocker `unsettled_settlement_sources` (caja + turno) | ✅ |
| UI cortes en girls/waiters/cleaning/history | ✅ |
| Tests `PartialSettlementsTest` (10) | ✅ |
| Docs `PARTIAL_SETTLEMENTS_*` | ✅ |

---

## FEATURE — Combos con manillas multichica CBA-1…CBA-5 (completado 2026-06-16)

| Entrega | Estado |
|---------|--------|
| CBA-1 Catálogo declarativo (`settlement_behavior`, `bracelet_units_per_line`, etc.) | ✅ |
| CBA-2 `order_item_allocations` + sync API + validación suma exacta | ✅ |
| CBA-3 `sale_item_allocations` snapshot al cobrar | ✅ |
| CBA-4 Liquidación `GIRL_BRACELET_ALLOCATION` por chica/allocation | ✅ |
| CBA-5 UI `BraceletAllocationPanel` garzón + cajera | ✅ |
| **CBA-UX Flujo híbrido garzón (una chica + reparto táctil)** | ✅ 2026-06-16 |
| Bloqueo venta directa combos V1 | ✅ |
| Tests `ComboBraceletAllocationTest` (11) | ✅ |
| Docs `COMBO_BRACELET_ALLOCATION_*` | ✅ |

**Regla:** `SUM(manillas) = quantity × bracelet_units_per_line`.  
**Sin romper:** CON_ACOMPAÑANTE simple, manillas legacy, liquidaciones parciales, piezas/shows.  
**Pendiente V2:** venta directa con allocator.

Ver **CBA-6:** `COMBO_BRACELET_REPORTING_CLOSURE_REPORT.md`.  
Ver **CBA-UX:** `frontend/WAITER_COMBO_UX_IMPLEMENTATION_REPORT.md`.

---

## FEATURE — CBA-UX Flujo híbrido garzón combos (completado 2026-06-16)

| Ítem | Estado |
|------|--------|
| Picker combo sin Solo/Con acompañante | ✅ |
| Atajo «todas para una chica» (1 tap) | ✅ |
| Reparto multichica táctil (todas las chicas visibles) | ✅ |
| Indicador visual manillas ●●●○○○ | ✅ |
| Cantidad × manillas anticipado (ej. 2 = 12) | ✅ |
| Editar reparto mismo diálogo | ✅ |
| Re-reparto al cambiar cantidad | ✅ |
| Productos normales sin cambios | ✅ |

**Doc:** `frontend/WAITER_COMBO_UX_IMPLEMENTATION_REPORT.md`

---

## FEATURE — Garzón UX Fase 1: tipo de venta + precuenta local (completado 2026-06-16)

| Entrega | Estado |
|---------|--------|
| Tabs Solo / Con compañía / Combos / Otros (`WaiterSaleTypeTabs`) | ✅ |
| Tap único Solo → agregar sin modalidad | ✅ |
| `GirlQuickPicker` con búsqueda realtime | ✅ |
| Combos → flujo híbrido existente (`ComboAllocationDialog`) | ✅ |
| Bucket Otros (Cover/Cortesía/Extras client-side) | ✅ |
| `POST orders/{id}/precheck/print` → job `PRECHECK` | ✅ |
| Botón garzón «Imprimir precuenta» + fallback vista manual | ✅ |
| Tests `WaiterPrecheckPrintTest` (8) | ✅ |

**Docs:** `frontend/WAITER_SALE_TYPE_FLOW_IMPLEMENTATION_REPORT.md`, `frontend/WAITER_PRECHECK_PRINT_REPORT.md`, `backend/WAITER_PRECHECK_PRINT_REPORT.md`  
**Fix productos antiguos:** `frontend/WAITER_SALE_TYPE_FLOW_PRODUCT_FILTER_FIX_REPORT.md` (2026-06-16)  
**Auditoría previa:** `frontend/WAITER_SALE_TYPE_FLOW_AUDIT.md`, `backend/WAITER_SALE_TYPE_FLOW_AUDIT.md`

---

## FEATURE — CBA-6 Reportes, cierre y precuenta combos (completado 2026-06-16)

| Entrega | Estado |
|---------|--------|
| `ComboBraceletReportingService` + reportes enriquecidos | ✅ |
| Cierre caja/turno con `combo_bracelets` | ✅ |
| `GET orders/{id}/precheck` + ticket venta con allocations | ✅ |
| UI precuenta, reportes, `ComboBraceletSummaryPanel` | ✅ |
| Tests `ComboBraceletReportingTest` (10) | ✅ |
| Docs `COMBO_BRACELET_REPORTING_CLOSURE_*` | ✅ |

---

## FEATURE — Fase A: Acompañante visible en comanda/ticket/precuenta (completado 2026-06-16)

| Entrega | Estado |
|---------|--------|
| `girl_name` en ítems simples `CON_ACOMPANANTE` (API) | ✅ |
| Batch load nombres (`UserRepository::findDisplayNamesByIds`) | ✅ |
| `girl_name` en ventas/tickets (`SaleAllocationPresenter`) | ✅ |
| Descripción liquidación `GIRL_CONSUMPTION` con nombre | ✅ |
| UI `Manilla: María` / `Manilla: Sin asignar` | ✅ |
| Tests `CompanionNameDisplayTest` (5) | ✅ |
| Docs `COMPANION_NAME_DISPLAY_*` | ✅ |

**Regla:** SOLO_CLIENTE sin `girl_name`; combos sin cambio (allocations).  
**Pendiente:** Fase D copy manillas — ver `WAITER_TABLES_COMPANION_BRACELET_AUDIT.md`.

---

## FEATURE — Fase B: Modelo de mesas MVP (backend completado 2026-06-16)

| Entrega | Estado |
|---------|--------|
| Tablas `service_tables`, `waiter_table_assignments` | ✅ |
| `orders.service_table_id` | ✅ |
| CRUD admin `/service-tables` | ✅ |
| Sync asignaciones `/waiter-table-assignments/sync` | ✅ |
| `GET /waiter/my-tables` (FREE/OCCUPIED) | ✅ |
| `POST /waiter/my-tables/{id}/open` (idempotente) | ✅ |
| Guard anti-duplicado comanda activa por mesa | ✅ |
| Permisos Phase B + seeder | ✅ |
| Tests `WaiterTablesPhaseBTest` (10) | ✅ |
| Doc `WAITER_TABLES_PHASE_B_REPORT.md` | ✅ |

---

## FEATURE — Fase C: Mis mesas garzón + config admin (frontend completado 2026-06-16)

| Entrega | Estado |
|---------|--------|
| Home garzón grid LIBRE/OCUPADA (tap-to-open) | ✅ |
| `WaiterTableTile` + `WaiterTablesGrid` + `useWaiterTables` | ✅ |
| SSE/polling refresh al cobrar | ✅ |
| Bottom nav Mesas / Comandas / Otra mesa | ✅ |
| Config Mesas (`settings/service-tables`) | ✅ |
| Asignar mesas garzones (`staff/waiter-assignments`) | ✅ |
| Doc `WAITER_TABLES_PHASE_C_REPORT.md` | ✅ |
| Bugfix freeze asignación mesas (`WAITER_ASSIGNMENTS_FREEZE_FIX_REPORT.md`) | ✅ |
| UX garzón solo mesas asignadas (sin «Otra mesa») | ✅ |
| Tests + fix liquidaciones parciales post-Mis mesas | ✅ |

**Liquidaciones parciales:** migración `2026_06_16_100010` + tests `PartialSettlementsAfterTablesTest`; Mis mesas no rompe pipeline. Docs: `PARTIAL_SETTLEMENTS_AFTER_TABLES_FIX_REPORT.md`.

**Bugfix 2026-06-16:** overlay VSelect + `nextTick` sin import en `overlaySafety.js` congelaba navegación tras guardar asignaciones — corregido.

**Pendiente Fase D:** copy manillas + mover manillas manuales.

---

## FEATURE — Cajera alta presión Fase 0 (completado 2026-06-16)

**Objetivo:** quick wins operativos sin rediseñar layout cajera.

| Área | Entregable |
|------|------------|
| API cola cobro | `waiting_minutes`, flags combo/acompañante/bloqueo, orden urgencia |
| UI cola | chips + Cobrar disabled si `charge_blocked` |
| Atajos | Enter/Esc en modales pago, caja, movimientos, liquidaciones |
| Mi caja | `?open=1` abre diálogo apertura |
| Ventas turno | SSE + reimprimir última venta |

**Docs:** `backend/CASHIER_HIGH_PRESSURE_PHASE0_REPORT.md`, `frontend/CASHIER_HIGH_PRESSURE_PHASE0_REPORT.md`, auditorías actualizadas.

**Tests:** `CashierChargeQueuePhase0Test` (11). Suite completa OK.

**Pendiente Fase 1:** ~~shell cajera + cobro desde card sin detalle.~~ **Fase 1 completada 2026-06-16** — `CASHIER_HIGH_PRESSURE_PHASE1_REPORT.md`.

**Pendiente Fase 2:** ~~shell cajera dedicado~~ **Fase 2A completada 2026-06-16** — `CASHIER_HIGH_PRESSURE_PHASE2A_SHELL_REPORT.md`. **Ajuste 2A (cierre por método + Más permisos) 2026-06-17.** Pendiente: pago express desde card (Fase 2B).

---

## AJUSTE Fase 2A — Cierre por método + Menú «Más» (2026-06-17)

| Área | Entregable |
|------|------------|
| Cierre caja | Resumen Efectivo/QR/Tarjeta en Mi Caja y diálogo cierre; declaración por método |
| Backend summary | `sales_by_method`, `opening_cash`, alias `income/expense/sales/expected_*` |
| Tab Más | `useCashierMoreMenu` — secciones por permiso (Operación, Catálogo, Config, Finanzas) |
| Routing | Allowlist ampliado: products, services, rooms, settings, shift-console, staff |
| Permisos | Sin cambio seeders; auditoría cajera básica vs senior |

**Docs:** `backend/CASHIER_CLOSE_BY_METHOD_REPORT.md`, `frontend/CASHIER_CLOSE_BY_METHOD_REPORT.md`, `backend/CASHIER_MORE_PERMISSIONS_AUDIT_REPORT.md`, `frontend/CASHIER_MORE_PERMISSIONS_AUDIT_REPORT.md`, `frontend/CASHIER_MORE_MENU_IMPLEMENTATION_REPORT.md`

**Backend:** `CashSessionFinancialSummaryBuilder` (campos adicionales retrocompatibles).

**Close API:** solo `declared_closing_amount`; QR/tarjeta en `closing_notes`.

**Tests:** `SettlementPaymentMethodTest` OK.

**Pendiente:** Fase 2B cajera (pago express desde card).

---

## AJUSTE UX/Auth — Logout cajera + sesión operativa (2026-06-17)

| Área | Entregable |
|------|------------|
| Cuenta en shell | Sección Cuenta en «Más» + menú usuario desktop en status bar |
| Logout | Cerrar sesión / Cambiar cuenta → limpia tokens, contexto, SSE → login |
| JWT TTL | Default 12 h (`JWT_TTL=720`), refresh 14 días, `refresh_iat=true` |
| Refresh API | `POST /auth/refresh` + interceptor axios renueva token en 401 |
| Login expirado | «Tu sesión expiró. Vuelve a ingresar.» |

**Docs:** `backend/AUTH_SESSION_TTL_OPERATIONAL_REPORT.md`, `frontend/AUTH_SESSION_TTL_OPERATIONAL_REPORT.md`, `frontend/CASHIER_LOGOUT_SWITCH_ACCOUNT_REPORT.md`

**Tests:** `AuthApiTest` (+2 refresh/TTL).

**Pendiente:** Fase 2B cajera (en pausa hasta validar logout + sesión).

---

## AUDITORÍA V1 RELEASE CANDIDATE (2026-06-17)

Radiografía de producto completa — **sin cambios de código**.

| Documento | Contenido |
|-----------|-----------|
| `backend/NIGHTPOS_V1_RELEASE_AUDIT.md` | API, reglas, permisos, kardex, seguridad, infra |
| `frontend/NIGHTPOS_V1_RELEASE_AUDIT.md` | UX, shells, flujos, clics, impresión, SSE |

**Veredicto:** **NO listo** para viernes/sábado autónomo de máxima carga. **SÍ** para piloto controlado con V1-98 + V1-99 + alcance firmado (sin barra/kardex/impresión auto si no aplica).

**Bloqueadores P0:** V1-98 (0%), V1-99 (~20%), kardex/barra/impresión si el local los exige.

**Siguiente paso obligatorio:** ejecutar V1-98 QA operativo → V1-99 preprod → declarar RC solo sin P0/P1 abiertos.

---

## AUDITORÍA PLANIFICADA — Kardex / Inventario V1 (2026-06-17)

Diseño de módulo kardex para cerrar V1 operativo con control de stock. **Sin implementación aún.**

| Documento | Contenido |
|-----------|-----------|
| `backend/INVENTORY_KARDEX_V1_AUDIT.md` | Estado actual, modelo, hooks venta, combos, API, permisos, fases INV-1…5 |
| `frontend/INVENTORY_KARDEX_V1_AUDIT.md` | UI producto, sección Inventario, cierre, reportes, fases INV-FE-1…5 |

**Estado actual:** `track_inventory` inerte; sin stock/movimientos/componentes; combos = manillas liquidación, no inventario.

**Recomendación stock V1:** permitir negativo + alertas (no bloquear venta).

**Estimado implementación:** ~12–17 días backend + ~8–10 días frontend + QA.

---

## AUDITORÍA PLANIFICADA — Impresión local automática (2026-06-17)

Diseño agente local + cola `print_jobs`. **Sin implementación aún.**

| Documento | Contenido |
|-----------|-----------|
| `backend/LOCAL_PRINTING_AGENT_AUDIT.md` | Modelo datos, API, hooks send-to-bar, seguridad device_key, fases PRINT-1…6 |
| `frontend/LOCAL_PRINTING_AGENT_AUDIT.md` | UI admin impresoras, badges estado, reimpresión |
| `agent/LOCAL_PRINTING_AGENT_AUDIT.md` | Agente Node.js Windows, polling, ESC/POS, dry-run |

**Estado actual:** V1-97 solo `window.print()`; sin `print_jobs` en DB; `settings/printers` placeholder.

**Recomendación V1:** Agente Node + polling 1,5s + `content_text` generado en backend + auto print al enviar a barra.

**Estimado MVP comanda auto:** ~11–15 días dev + QA impresora real.

---

## AJUSTE UX — Tab Piezas en shell cajera (2026-06-17)

| Área | Entregable |
|------|------------|
| Navegación | `Cobrar \| Piezas \| Venta \| Caja \| Más` (desktop + bottom nav) |
| Ruta shell | `/nightpos/cashier/piezas` → reutiliza `room-services/index.vue` |
| Permisos tab | Visible si `room_services.access` o `rooms.access` |
| Más | Piezas eliminado del menú secundario (sin duplicado) |
| Redirect | `nightpos-services-room-services` → shell piezas (cajera básica) |

**Docs:** `CASHIER_HIGH_PRESSURE_PHASE2A_SHELL_REPORT.md`, `CASHIER_MORE_MENU_IMPLEMENTATION_REPORT.md` actualizados.

**Backend:** sin cambios.

---

## FEATURE — Cajera alta presión Fase 2A Shell (completado 2026-06-16)

**Objetivo:** shell cajera simplificado solo para cajera básica — Cobrar | Piezas | Venta | Caja | Más.

| Área | Entregable |
|------|------------|
| Shell | `CashierShell` + status bar + bottom/desktop nav |
| Home | `/nightpos/cashier/orders` vía guards + `resolveHomeRoute` |
| Tabs | Cobrar, Venta directa, Mi caja, Más (permisos) |
| Caja cerrada | Banner persistente + Abrir caja en todos los tabs |
| Indicadores | Caja, pendientes BOB, SSE |
| Guards | Allowlist shell + redirect admin → shell |
| Senior/admin | Menú completo sin cambios |

**Docs:** `frontend/CASHIER_HIGH_PRESSURE_PHASE2A_SHELL_REPORT.md`

**Backend:** sin cambios.

**Build:** `npm run build` — OK.

---

## BUGFIX — Consistencia liquidaciones / close-check (2026-06-17)

**Problema:** close-check bloqueaba por pagos pendientes pero Liquidaciones mostraba vacío.

| Área | Fix |
|------|-----|
| Scope cajera | `my_cash_session` muestra PENDING de su caja |
| Close-check | Filtra por `cash_session_id`; blockers `SETTLEMENTS_*` |
| Generate | Cajera usa turno de su sesión de caja |
| API | `settlement_summary` en endpoints de liquidaciones |
| Frontend | Alerts + mensaje generate + botón Ir en blockers |

**Docs:** `backend/SETTLEMENT_CLOSE_CHECK_CONSISTENCY_FIX_REPORT.md`, `frontend/SETTLEMENT_CLOSE_CHECK_CONSISTENCY_FIX_REPORT.md`

**Tests:** `SettlementCloseCheckConsistencyTest` (7).

**Pendiente:** Fase 2B cajera (en pausa).

---

## AUDITORÍA — Liquidaciones y permisos cajera (2026-06-16, sin código)

**Estado:** Diagnóstico completado — **sin fixes implementados** (decisión pendiente).

**Problemas reportados:** (1) cajera genera pero UI dice vacío, (2) cajera no ve/paga liquidaciones que admin sí, (3) permisos nuevos al rol no aparecen en menú.

### Causa raíz (evidencia)

| # | Veredicto | Detalle |
|---|-----------|---------|
| 1–2 | **Scope read ≠ scope write** | `generateForShift` escanea **todo el turno**; `GET current-shift` filtra por `cash_session_id` (`my_cash_session`). Settlements GIRL/WAITER con `cash_session_id = NULL` (manillas, piezas, shows) **invisibles** para cajera básica. Admin usa `scope = shift`. |
| 3 | **Permisos congelados en sesión** | Permisos en cookie `userData` al login. `POST /auth/refresh` no devuelve permisos. Cambios de rol requieren **logout/login** o `GET /auth/me` (no se invoca al arrancar). |

**No es falta de slug `settlements.*` en demo:** cajera tiene los 5 permisos de liquidaciones. Diferenciador de scope: `admin.cash_sessions.view` (cajera senior ✓, cajera básica ✗).

### Entregables

| Documento | Contenido |
|-----------|-----------|
| `backend/SETTLEMENTS_PERMISSION_AUDIT.md` | API, scope resolver, generate vs read, mark-paid, JWT |
| `frontend/SETTLEMENTS_PERMISSION_AUDIT.md` | UI, menú Más, toasts, guards, cache permisos |
| `backend/NIGHTPOS_PERMISSION_MATRIX_AUDIT.md` | Matriz 98 permisos × 7 roles demo + drift wizard |
| `frontend/NIGHTPOS_PERMISSION_MATRIX_AUDIT.md` | Menú vs permiso, visibilidad cajera shell |

### Opciones de fix (pendiente aprobación)

1. Alinear `generateForShift` con filtro `cash_session_id` para cajera básica, **o** asignar siempre `cash_session_id` al crear settlements (manillas/piezas/shows).
2. Refrescar permisos en bootstrap (`/auth/me`) tras editar rol.
3. Documentar que `admin.cash_sessions.view` cambia alcance liquidaciones (equivalente operativo a “ver turno completo”).

**Tests relevantes:** `SettlementShiftScopeTest.php`, `SettlementCloseCheckConsistencyTest.php`.

**Relacionado:** fixes previos scope (`SETTLEMENT_SHIFT_SCOPE_FIX_REPORT.md`) no cubren desalineación generate/read ni `cash_session_id` NULL en GIRL.

---

## FEATURE — Cajera alta presión Fase 1 (completado 2026-06-16)

**Objetivo:** cobrar desde la cola **sin navegar al detalle**.

| Área | Entregable |
|------|------------|
| Cobro inline | `Cobrar` en card → `ChargeOrderModal` en la misma pantalla |
| Carga previa | `fetchOrder(id)` + overlay loading en card |
| Jerarquía UX | **Cobrar** primario · **Corregir** secundario (outlined) |
| Bloqueos | `charge_blocked` → Cobrar disabled + chips |
| Post-cobro | Snackbar «Comanda cobrada.» + refresh lista + SSE |
| Caja cerrada | `QuickOpenCashDialog` inline (sin navegar) |
| Modal | Título con mesa + Nº comanda |

**Docs:** `frontend/CASHIER_HIGH_PRESSURE_PHASE1_REPORT.md`

**Backend:** sin cambios (usa API Fase 0).

**Clics cobro simple:** 5–6 → **2** (Cobrar + Todo efectivo + Enter).

**Build:** `npm run build` — OK.

---

*Documento de planificación. Próxima acción SaaS: **SAAS-2 Suscripciones**.*
