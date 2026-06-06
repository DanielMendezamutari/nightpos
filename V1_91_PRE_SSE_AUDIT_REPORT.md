# V1-91 — Auditoría operativa final pre-SSE

**Proyecto:** NightPOS SaaS V1  
**Fecha:** 2026-06-06  
**Alcance:** Auditoría técnica y operativa completa antes de V1-92 (SSE-1) y V1-94 (SSE-2).  
**Método:** Revisión de 24+ reportes obligatorios, auditorías maestras, código backend/frontend puntual y ejecución de tests.  
**Regla:** Sin implementación. Sin migraciones. Sin cambios de código.

**Documentos revisados (obligatorios + complementarios):**

| Documento | Estado revisado |
|-----------|-----------------|
| `NIGHTPOS_V1_DEVELOPMENT_MAP.md` | Vigente — mapa V1 al ~82% |
| `NIGHTPOS_MASTER_AUDIT.md` | Vigente — núcleo operativo; impresión/reportes pendientes |
| `NIGHTPOS_OPERATION_AUDIT.md` | Vigente — simulación noche; riesgos A/C/K/H/S |
| `CURRENT_SYSTEM_AUDIT.md` | Legacy; NightPOS nuevo ya no es scaffold |
| `FINANCE_AND_WAITER_PRODUCT_SELECTOR_AUDIT.md` | Vigente — liquidaciones/cierre/egresos/venta directa |
| `OPERATION_CASH_FINANCE_AUDIT.md` | Vigente — menú, placeholders Finanzas, estados fantasma |
| `backend/POS_CAT_REPORT.md` + `frontend/POS_CAT_REPORT.md` | V1-90 completada |
| `backend/DIRECT_SALES_REPORT.md` + `frontend/DIRECT_SALES_REPORT.md` | Venta directa funcional |
| `backend/DIRECT_SALE_MIXED_PAYMENTS_REPORT.md` | Pago mixto 15/15 tests |
| `backend/CASHIER_ORDER_CORRECTION_REPORT.md` | Corrección cajera Fase A |
| `backend/ORDER_ITEM_PRODUCT_CHANGE_REPORT.md` | Cambio producto en línea |
| `backend/ADMIN_CASH_SESSIONS_REPORT.md` | Fiscalización multicaja |
| `backend/CLEANING_MOBILE_MODE_REPORT.md` + `frontend/` | Modo limpieza móvil |
| `backend/ROOM_SERVICE_NOTIFICATIONS_REPORT.md` | Polling 30s + sonido |
| `backend/SERVICES_CASH_ACCOUNTING_FIX_REPORT.md` | Servicios exigen caja abierta |
| `backend/SETTLEMENTS_CASH_UI_FIX_REPORT.md` | Fix UI liquidaciones |
| `PHASE_13`–`18`, `PHASE_C1`–`C4` (backend + frontend) | Turnos, liquidaciones, servicios, habitaciones, garzón |
| `ORDERS_COMPLETE_AUDIT.md` | Estados y scopes — parcialmente mitigado |
| `CASHIER_ORDER_AND_DIRECT_SALE_AUDIT.md` | Fases A/B cerradas |

**Verificación en código (muestra representativa):**

- `OrderStatus.php`, `SendOrderToBarUseCase.php`, `ChargeOrderUseCase.php`
- `OrderListScopeResolver.php`, `OrderAccessGuard.php`
- `CashSessionFinancialSummaryBuilder.php`, `MarkSettlementPaidUseCase.php`
- `PosProductPicker.vue`, `usePosCatalog.js`, `direct-sale.vue`
- Navegación `nightpos-r4.js` — **sin módulo Barra**
- Tests: `php artisan test` → **306 passed, 15 failed** (2026-06-06)

---

## 1. Resumen ejecutivo

NightPOS está en **pre-piloto operativo**: el núcleo de una noche (comandas → servicios → cobro/venta directa → liquidaciones → cierre) **funciona en el happy path** con datos precargados y personal capacitado. Las fases recientes (venta directa, pago mixto, POS-CAT, egresos unificados en liquidaciones, KPIs de cierre) cerraron huecos críticos reportados en auditorías de junio.

**Sin embargo, SSE no debe arrancar de inmediato sin una mini-estabilización.** Hay tres bloqueos concretos:

1. **15 tests backend fallando** — principalmente servicios/liquidaciones que ahora exigen caja abierta (`422` donde los tests esperan `201`). Indica deriva entre regla de negocio nueva y suite de regresión.
2. **Estados de comanda fantasma** (`IN_PREPARATION`, `READY`) — presentes en dominio, scopes, KPIs y UI, pero **ningún use case los asigna** en operación normal. Contaminan el modelo de eventos que SSE-2 necesita definir.
3. **No existe módulo Barra** — `send-to-bar` es semántico; no hay pantalla, rol ni flujo que avance la comanda más allá de `SENT_TO_BAR`.

**Veredicto pre-SSE:**

| Pregunta | Respuesta |
|----------|-----------|
| ¿Puede operar una noche de prueba? | **Sí**, con condiciones |
| ¿Está listo para piloto con tiempo real? | **No aún** |
| ¿Puede comenzar SSE-1 inmediatamente? | **No** — requiere sprint de estabilización (~2–4 días) |
| **% real del sistema hacia V1** | **~80%** (ajuste a la baja vs mapa ~82% por tests + estados fantasma + validación POS-CAT pendiente) |

---

## 2. Estado real del sistema

### 2.1 Lo que sí funciona (confirmado en reportes + código)

| Área | Capacidad operativa |
|------|---------------------|
| Auth multi-tenant | PIN + password, contexto tenant/sucursal |
| Comandas | Crear, ítems, enviar barra, cancelar, cobrar, corrección cajera (Fase A) |
| Garzón móvil | Dashboard, comandas propias, POS-CAT en selector |
| Caja | Apertura/cierre por usuario, movimientos, `financial_summary`, pago mixto |
| Venta directa | Sin comanda, precios activos, pago mixto, movimientos por método |
| Servicios | Manillas, piezas, shows — **con caja abierta** e ingreso en caja |
| Habitaciones | Estados, mark-clean, bloqueos OCCUPIED |
| Limpieza | Modo móvil, control piezas, polling, liquidación base+pieza |
| Liquidaciones | Generar, pagar (EXPENSE unificado WAITER/GIRL/CLEANING), banner caja |
| Turnos | Auto-turno, cierre con KPIs ventas + liquidaciones |
| Fiscalización | Admin multicaja read-only |
| Catálogo POS | `pos-catalog`, favoritos/recientes, sin precio |

### 2.2 Lo que funciona con fricción

| Área | Fricción |
|------|----------|
| Liquidaciones | Montos en **0.00** hasta pulsar «Generar»; cajera no siempre conoce el paso |
| Cierre de turno | KPIs de liquidación visibles, pero flujo sigue **desconectado** de pago masivo |
| Limpieza | Depende de pestaña abierta (polling 30s); sin push/SSE |
| Cajera / consola | Polling 30s en consola turno; F5 para ver cambios |
| Garzón | Modal fullscreen + varios taps por línea; POS-CAT mitiga catálogo grande |
| Admin comandas | Tabs por scope (mitigado); aún disperso vs vista cajera |

### 2.3 Lo que no existe o está roto

| Área | Estado |
|------|--------|
| Módulo Barra operativo | **No existe** |
| Estados IN_PREPARATION / READY en runtime | **Fantasma** (solo seed demo) |
| SSE / tiempo real | **No iniciado** |
| Impresión comanda/ticket | **No iniciado** (solo contratos dominio) |
| Reportes API (`reports.access`) | **Sin rutas** |
| Placeholders Finanzas (movimientos, reportes, cierre caja) | **Pantallas vacías** |
| 15 tests Feature | **Fallando** (servicios/liquidaciones sin caja en test) |

### 2.4 Métricas de calidad (snapshot 2026-06-06)

| Métrica | Valor |
|---------|-------|
| Tests backend | 306 pass / **15 fail** / 2038 assertions |
| Tests DirectSale | 15/15 (incluye mixto y sin precio) |
| Tests PosCatalog | 9/9 |
| Tests SettlementsCashUiFix | 7/7 |
| Polling activo | Consola turno, room-control, cleaning (30s) |

---

## 3. Hallazgos por módulo

### 3.1 Comandas

**Flujo real implementado:**

```text
OPEN ──send-to-bar──► SENT_TO_BAR ──charge──► BILLED
  │                        │
  └──── cancel ────────────┴──── cancel ───► CANCELLED
```

**Estados — veredicto:**

| Estado | ¿Se usa? | ¿Fantasma? | Recomendación |
|--------|----------|------------|---------------|
| `OPEN` | **Sí** — creación, edición, envío | No | **Mantener** |
| `SENT_TO_BAR` | **Sí** — único post-envío en producción | No | **Mantener** — evento SSE clave |
| `IN_PREPARATION` | **No** en runtime | **Sí** — solo seed `W-DEMO-05` | **Ocultar de UI/KPI** o implementar barra |
| `READY` | **No** en runtime | **Sí** — solo seed `W-DEMO-04` | **Ocultar de UI/KPI** o implementar barra |
| `BILLED` | **Sí** — post-cobro | No | **Mantener** |
| `CANCELLED` | **Sí** | No | **Mantener** |

**Capacidades verificadas:**

| Operación | Backend | Frontend | Notas |
|-----------|---------|----------|-------|
| Creación | ✅ | ✅ | C1: garzón obligatorio si no es WAITER |
| Agregar ítem | ✅ | ✅ | POS-CAT en dialog |
| Corrección cajera (qty, modo, cancel línea) | ✅ | ✅ | Fase A |
| Cambio de producto en línea | ✅ | ✅ | Con motivo si SENT_TO_BAR |
| Envío a barra | ✅ | ✅ | Solo desde OPEN |
| Cobro | ✅ | ✅ | **No exige** SENT_TO_BAR — cobra OPEN también |
| Cancelación comanda | ✅ | ✅ | Pre-BILLED |

**Inconsistencias detectadas:**

| ID | Hallazgo | Impacto |
|----|----------|---------|
| COM-01 | `ChargeOrderUseCase` no valida que la comanda esté en `SENT_TO_BAR` — permite cobrar `OPEN` | Operativo flexible; puede saltarse barra |
| COM-02 | KPI garzón `pending_charge` incluye `SENT_TO_BAR` + `IN_PREPARATION` + `READY` — solapa con «En barra» | Confusión en dashboard garzón |
| COM-03 | `canModifyOrder()` solo `OPEN` y `SENT_TO_BAR`; `IN_PREPARATION`/`READY` bloquean edición en guard pero nadie llega ahí | Código muerto |
| COM-04 | Listado admin usa tabs por scope (mitigado ORDERS_SCOPE_KPI_FIX); cajera usa `cashier_chargeable` correctamente | Parcialmente resuelto |
| COM-05 | Detalle cajera `?from=cashier` — «Volver» puede apuntar a lista admin | UX menor |

---

### 3.2 Garzón móvil

**Estado:** usable en piloto; POS-CAT (V1-90) resolvió el problema de 200+ productos.

| Aspecto | Estado | Detalle |
|---------|--------|---------|
| UX móvil | ✅ Aceptable | Layout blank, bottom nav, cards táctiles |
| Selector productos | ✅ Mejorado | `PosProductPicker`: fav/recientes/categorías/búsqueda ≥2 chars |
| Velocidad comandar | ⏳ Media | Sigue siendo modal fullscreen + modalidad + cantidad |
| Categorías | ✅ | Desde API `pos-catalog` |
| Favoritos/recientes | ✅ | localStorage por dispositivo |
| Flujo noche | ✅ | Crear → agregar → asignar chica → enviar barra |

**Qué sigue lento/confuso antes del piloto:**

- Varios taps por línea (abrir dialog → buscar → elegir modo → cantidad → confirmar).
- Favoritos no sincronizados entre dispositivos del mismo garzón.
- Sin notificación cuando cajera cobra (garzón descubre al refrescar).
- Sin PWA instalable (URL manual en celular).

**Bug reciente corregido (fuera de alcance SSE pero relevante):** `direct-sale.vue` tenía `onMounted(loadCash)` antes de `const loadCash` (TDZ) — corregido en sesión posterior; venta directa de cajera, no garzón.

---

### 3.3 Caja

**Estado:** núcleo contable sólido; UI Mi caja mejorada con `financial_summary`.

| Operación | Estado | Validación |
|-----------|--------|------------|
| Apertura | ✅ | Por usuario (`findOpenForUser`) |
| Cierre | ✅ | `expected_cash` = apertura + ventas efectivo + ingresos manuales − egresos manuales |
| Movimientos manuales | ✅ | Motivos C3; UI usa catálogo |
| Cobros comanda | ✅ | INCOME por método; asocia `cash_session_id` |
| Venta directa | ✅ | Igual que cobro; MIXED soportado |
| Pagos liquidaciones | ✅ | EXPENSE unificado (WAITER/GIRL/CLEANING) |
| Fiscalización admin | ✅ | Read-only multicaja |

**Fórmula `expected_cash` (CashSessionFinancialSummaryBuilder):**

```text
expected = opening + total_cash_sales + manual_income − manual_expense
```

Los egresos por liquidación entran vía `cash_movements` EXPENSE — **correcto** si se pagan desde liquidaciones.

**Inconsistencias:**

| ID | Hallazgo | Severidad |
|----|----------|-----------|
| CAJ-01 | Caja **por usuario** — dos cajeras = dos sesiones | Importante (capacitación) |
| CAJ-02 | Cierre turno exige **todas** las cajas cerradas | Importante |
| CAJ-03 | KPI «Ingresos manuales» en Mi caja usa `financial_summary.total_manual_income` con fallback a `income_total` — fallback mezcla cobros | Mejora (mitigado si API siempre trae summary) |
| CAJ-04 | Dashboard operativo: ventas del día aún placeholder en algunos widgets | Mejora |
| CAJ-05 | Sin export PDF arqueo desde API | Mejora (V1-96) |

---

### 3.4 Venta directa

**Estado:** ✅ operativa tras fixes DSP (precios activos + pago mixto + POS-CAT).

| Validación | Resultado |
|------------|-----------|
| Productos con precio | ✅ `active_prices` vía pos-catalog |
| Productos sin precio | ✅ Botones disabled + pantalla admin Sin precio |
| Pago mixto | ✅ 15 tests DirectSaleApiTest |
| Movimientos caja | ✅ INCOME por cada método |
| Flujo sin comanda | ✅ `POST /direct-sales`, `order_id` null |
| Permiso `sales.direct_create` | ✅ En menú Caja (VD-A) |

**Riesgos residuales:**

- Validación manual POS-CAT con 200 productos en celular **pendiente** (V1-98).
- Chica obligatoria en ítems CON_ACOMPANANTE antes de cobrar — coherente con comandas.

---

### 3.5 Liquidaciones

**Estado:** backend robusto; UX de generación manual sigue siendo punto de fricción.

| Tipo | Generación | Pago | Egreso caja |
|------|------------|------|-------------|
| Garzones | ✅ Comisiones ventas cobradas | ✅ mark-paid | ✅ EXPENSE |
| Chicas | ✅ Consumos + servicios | ✅ | ✅ EXPENSE |
| Limpieza | ✅ Base + pieza (`cleaning_tasks`) | ✅ | ✅ EXPENSE |

**Inconsistencias:**

| ID | Hallazgo | Severidad |
|----|----------|-----------|
| LIQ-01 | Resumen muestra **0.00** hasta `POST /settlements/generate-current-shift` | Importante — capacitación |
| LIQ-02 | Cierre turno muestra KPIs liquidación pero no fuerza generación previa | Importante |
| LIQ-03 | `shift_closures.total_*_payouts` — persistencia al cerrar turno implementada (L1) pero requiere validación en QA | Importante |
| LIQ-04 | Piezas ACTIVE no liquidan hasta FINISHED — documentado; riesgo si no se finalizan | Importante |
| LIQ-05 | 15 tests fallan al crear servicios sin abrir caja en test setup | **Crítico** (regresión CI) |

---

### 3.6 Limpieza

**Regla de negocio (correcta según `CLEANING_SETTLEMENTS_REPORT.md`):**

- `cleaning_base_amount` — una vez por turno si hubo al menos una limpieza.
- `cleaning_room_amount` — por cada `cleaning_task` al marcar habitación limpia.
- Anti-duplicados por `room_service_id`.

| Aspecto | Estado |
|---------|--------|
| Piezas / vencimientos | ✅ `room-services/due`, control |
| Habitaciones CLEANING → AVAILABLE | ✅ `mark-clean` |
| Ingresos limpieza (vista staff) | ✅ `GET /cleaning/shift-earnings` |
| Notificaciones | ⏳ Polling 30s + sonido (mp3 manual) |
| WhatsApp | ❌ Stub |

**Riesgo operativo H-01 (persistente):** habitación atascada en `CLEANING` bloquea nuevas piezas hasta `mark-clean`.

---

### 3.7 Habitaciones

**Transiciones implementadas:**

```text
AVAILABLE ──(iniciar pieza)──► OCCUPIED ──(fin pieza)──► CLEANING ──(mark-clean)──► AVAILABLE
     │                                                              │
     └────────────────── MAINTENANCE ◄─────────────────────────────┘
```

| Estado | Uso | Bloqueo venta |
|--------|-----|---------------|
| `AVAILABLE` | ✅ | — |
| `OCCUPIED` | ✅ Pieza activa | Sí para misma habitación |
| `CLEANING` | ✅ Post-servicio | **Sí** — crítico operativo |
| `MAINTENANCE` | ✅ Admin | Sí |

**Validación:** backend evita doble OCCUPIED; no hay auto-timeout de CLEANING.

---

### 3.8 Servicios (manillas, piezas, shows)

**Estado:** funcional con contabilidad de caja unificada (`ServiceIncomeCashRecorder`).

| Servicio | Caja requerida | INCOME | Liquidación | Turno |
|----------|----------------|--------|-------------|-------|
| Manilla | ✅ 422 sin caja | ✅ | ✅ GIRL | ✅ shift_id |
| Pieza | ✅ | ✅ | ✅ GIRL + limpieza | ✅ |
| Show | ✅ | ✅ | ✅ GIRL | ✅ |

**Problema crítico:** tests de Fase 16 / QuickActions / GirlIncome fallan con `422` porque no abren caja en `beforeEach` — la regla de negocio es correcta, la suite no.

---

### 3.9 Finanzas (menú y estructura)

**Menú actual (`nightpos-r4.js`):**

```
OPERACIÓN → dashboard, consola, cobrar comandas, comandas, servicios, habitaciones
CAJA → mi caja, venta directa, ventas turno, fiscalización
FINANZAS → liquidaciones (5 sub), cierre turno
```

| Elemento | Estado |
|----------|--------|
| Liquidaciones | ✅ Funcional |
| Cierre turno | ✅ Con KPIs ventas + liquidaciones |
| Fiscalización multicaja | ✅ Admin |
| `nightpos-finance-movements` | ❌ Placeholder |
| `nightpos-finance-reports` | ❌ Placeholder |
| `nightpos-finance-cash-close` | ❌ Placeholder |

**Redundancia:** Consola turno y Cierre turno se solapan parcialmente — aceptable si roles distintos.

**Falta:** API reportes, export histórico, P&L (V1-96).

---

### 3.10 Barra — auditoría especial

**¿Existe módulo Barra operativamente?**

| Criterio | Resultado |
|----------|-----------|
| Ruta frontend `/nightpos/bar` o similar | **No** |
| Entrada en menú / rol Barra | **No** |
| API para marcar IN_PREPARATION / READY | **No** |
| Pantalla cocina/barra legacy migrada | **No** |
| Impresión comanda a barra | **No** |

**¿Se usa `SENT_TO_BAR`?** **Sí** — garzón envía; cajera ve en `pending_charge`; es el único estado intermedio real.

**¿Se usan `IN_PREPARATION` y `READY`?** **No** en producción. Solo:

- Definidos en `OrderStatus.php`
- Incluidos en scopes `pending_charge`, `cashier_chargeable`
- Labels en UI garzón/cajera
- Datos demo en seeder

**Respuestas directas:**

| Pregunta | Respuesta |
|----------|-----------|
| ¿La barra existe operativamente? | **No** — solo el estado `SENT_TO_BAR` como marcador |
| ¿Se va a usar en piloto típico boliche? | **Depende** — si barra solo prepara sin sistema, `SENT_TO_BAR` basta |
| ¿Debe implementarse módulo barra en V1? | **Opcional** — acoplado a V1-97 impresión o V2 |
| ¿Eliminar IN_PREPARATION y READY? | **Recomendado ocultar** de KPI/scopes/UI hasta tener barra; mantener en dominio para no romper seed |
| ¿Cómo afecta SSE? | SSE-2 debe emitir `order.sent_to_bar` al enviar; **no** emitir `order.ready` hasta existir transición real |

**Modelo SSE propuesto (pre-corrección):**

```text
order.created
order.sent_to_bar     ← implementar en SSE-2
order.charged         ← sale.created equivalente
order.cancelled
room_service.due      ← ya existe lógica notificación
room.cleaned
cash.movement.created
cleaning.earnings.updated
```

Eventos **diferidos** hasta módulo barra: `order.in_preparation`, `order.ready`.

---

## 4. Hallazgos críticos

Errores que afectan dinero, caja, liquidaciones o ventas.

| ID | Hallazgo | Módulo | Evidencia | Corrección propuesta |
|----|----------|--------|-----------|----------------------|
| **CRIT-01** | **15 tests Feature fallando** — servicios/liquidaciones devuelven 422 sin caja en tests | Servicios / CI | `php artisan test` 2026-06-06 | Actualizar tests para abrir caja en setup; no revertir regla de negocio |
| **CRIT-02** | Habitación en `CLEANING` sin limpieza bloquea venta de piezas | Habitaciones | `NIGHTPOS_OPERATION_AUDIT` H-01 | Proceso operativo + alerta UI al registrar pieza; SSE `room.cleaned` mitigará visibilidad |
| **CRIT-03** | Liquidaciones en **0.00** hasta generar manualmente — cajera puede cerrar turno sin pagar personal | Liquidaciones | `FINANCE_AND_WAITER_PRODUCT_SELECTOR_AUDIT` | Banner obligatorio en cierre + bloqueo suave si `total_pending > 0` |
| **CRIT-04** | Cobro de comanda `OPEN` sin pasar por barra — posible bypass operativo | Comandas | `ChargeOrderUseCase` sin check de estado | Decisión de negocio: permitir (flex) o exigir `SENT_TO_BAR` (estricto) |
| **CRIT-05** | Sin backups/despliegue formalizado | Preproducción | `NIGHTPOS_MASTER_AUDIT` P2/P4 | V1-99 — no bloquea SSE pero bloquea piloto real |

---

## 5. Hallazgos importantes

Errores operativos que no rompen el happy path pero afectan piloto.

| ID | Hallazgo | Módulo |
|----|----------|--------|
| **IMP-01** | Estados fantasma IN_PREPARATION/READY en scopes y KPIs | Comandas |
| **IMP-02** | KPI garzón «Pend. cobro» duplica SENT_TO_BAR | Garzón |
| **IMP-03** | Caja por usuario — dos cajeras no comparten sesión | Caja |
| **IMP-04** | Polling 30s en limpieza/cajera/consola — F5 efectivo hoy | SSE motivación |
| **IMP-05** | Placeholders Finanzas (movimientos, reportes) confunden menú | Finanzas |
| **IMP-06** | Sin impresión barra — locales con ticket obligatorio no pueden operar | Barra |
| **IMP-07** | POS-CAT sin QA formal 20/100/200 productos | Catálogo |
| **IMP-08** | Favoritos/recientes solo localStorage — no multi-dispositivo | Garzón |
| **IMP-09** | Piezas ACTIVE no liquidan — riesgo comisiones limpieza incompletas | Limpieza |
| **IMP-10** | Tenant nuevo vacío sin onboarding «primera noche» | SaaS |

---

## 6. Mejoras (UX y optimización)

| ID | Mejora | Módulo |
|----|--------|--------|
| **MEJ-01** | Reducir taps en agregar producto garzón (inline qty, último modo recordado) | Garzón |
| **MEJ-02** | PWA garzón + icono pantalla completa | Garzón |
| **MEJ-03** | Dashboard ventas reales vs placeholder | Finanzas |
| **MEJ-04** | Export PDF cierre caja / turno | Reportes |
| **MEJ-05** | Sugerir habitaciones alternativas en error `roomNotAvailable` | Habitaciones |
| **MEJ-06** | Volver de detalle cajera a `cashier/orders` no a `orders` | Comandas |
| **MEJ-07** | Archivo `room-due.mp3` documentado pero no incluido en repo | Limpieza |

---

## 7. SSE Readiness

| Módulo | Estado | Bloquea SSE-1 | Bloquea SSE-2 | Por qué |
|--------|--------|---------------|---------------|---------|
| **Comandas** | REQUIERE CORRECCIÓN | No | **Sí** | Estados fantasma; definir eventos reales antes de `order.ready` |
| **Garzón móvil** | LISTO PARA SSE | No | No | POS-CAT estable; consumidor de eventos cobro |
| **Caja** | LISTO PARA SSE | No | No | Movimientos y summary consistentes |
| **Venta directa** | LISTO PARA SSE | No | No | `sale.created` directo a implementar |
| **Liquidaciones** | REQUIERE CORRECCIÓN | No | Parcial | Tests rotos; evento pago personal opcional |
| **Limpieza** | LISTO PARA SSE | No | No | Mayor beneficiario de SSE-2 (`room_service.due`) |
| **Habitaciones** | LISTO PARA SSE | No | No | `room.cleaned` claro |
| **Servicios** | REQUIERE CORRECCIÓN | **Sí** (tests) | No | 422 tests — estabilizar antes de CI con SSE |
| **Finanzas / fiscalización** | LISTO PARA SSE | No | No | `cash.movement.created` para admin |
| **Barra** | BLOQUEA SSE-2 parcial | No | **Sí** (modelo) | Sin módulo; no emitir eventos inexistentes |
| **Catálogo POS** | LISTO PARA SSE | No | No | No depende de tiempo real |
| **Turnos** | LISTO PARA SSE | No | Parcial | Cierre podría usar evento `shift.closing` en V2 |

**Leyenda:**

- **LISTO PARA SSE** — puede consumir/emitir eventos sin cambio de modelo previo.
- **REQUIERE CORRECCIÓN** — funciona en UI pero hay deuda que contaminará eventos o CI.
- **BLOQUEA SSE** — decisión de arquitectura pendiente antes de implementar consumidores.

---

## 8. Recomendación final

### 8.1 ¿Puede comenzar SSE inmediatamente?

**No.** Recomendación: **sprint de estabilización V1-91 (2–4 días)** antes de escribir código SSE.

### 8.2 Qué corregir antes de SSE (orden propuesto)

| Prioridad | ID | Tarea | Esfuerzo | Bloquea |
|-----------|-----|-------|----------|---------|
| **P0** | CRIT-01 | Reparar 15 tests (abrir caja en setup de servicios/settlements) | 0.5–1 día | SSE-1 CI |
| **P0** | IMP-01 | Decisión + documento: ocultar IN_PREPARATION/READY de scopes/KPI/UI | 0.5 día | SSE-2 modelo |
| **P1** | CRIT-03 | UX cierre: aviso liquidaciones no generadas / pendientes | 1 día | Piloto |
| **P1** | CRIT-04 | Decisión negocio: ¿cobrar solo SENT_TO_BAR o también OPEN? | 0.5 día | Operación |
| **P1** | IMP-07 | QA manual POS-CAT 20/100/200 | 0.5 día | Piloto garzón |
| **P2** | IMP-02 | Ajustar KPI garzón pending_charge (excluir SENT_TO_BAR duplicado) | 0.25 día | UX |
| **P2** | Barra | Documentar formalmente: «sin módulo barra en V1; SENT_TO_BAR es terminal hasta cobro» | 0.25 día | SSE-2 |

### 8.3 Orden de trabajo para V1-92 (SSE-1) tras estabilización

```text
V1-91.1  Fix tests + decisión estados comanda + doc barra     (2–3 días)
    ↓
V1-92.1  Migración operational_events + contrato evento base
V1-92.2  Emisor en use cases (send-to-bar, charge, mark-clean, mark-paid, direct-sale)
V1-92.3  Token SSE + GET /events/stream + filtros tenant/branch/rol
V1-92.4  useOperationalEvents + reconnect + heartbeat
V1-92.5  Tests SSE + doc backend/frontend SSE_1_REPORT.md
    ↓
V1-94    SSE-2 consumidores: cleaning, cashier/orders, shift-console, admin cash
```

### 8.4 Porcentaje real del sistema

| Métrica | Valor | Notas |
|---------|-------|-------|
| **Hacia V1 operable (boliche una noche)** | **~80%** | Núcleo hecho; SSE, reportes, QA, tests restantes |
| **Hacia piloto en local real** | **~72%** | + tiempo real + estabilización CI + capacitación liquidaciones |
| **Hacia producción comercial SaaS** | **~68%** | + impresión + backups + reportes exportables |

Ajuste vs `NIGHTPOS_V1_DEVELOPMENT_MAP.md` (~82%): **−2 puntos** por tests fallando, estados fantasma no resueltos y validación POS-CAT pendiente.

### 8.5 Go / No-Go SSE

| Escenario | Veredicto |
|-----------|-----------|
| Iniciar SSE-1 mañana sin fix tests | **No-Go** |
| Iniciar SSE-1 tras V1-91.1 (tests + decisión estados) | **Go** |
| Iniciar SSE-2 sin doc barra y sin P0/P1 | **No-Go** |
| Piloto boliche sin SSE (polling 30s) | **Go** con capacitación |
| Piloto boliche con impresión obligatoria | **No-Go** hasta V1-97 |

---

## Apéndice A — Matriz de tests por área crítica

| Suite | Estado | Notas |
|-------|--------|-------|
| `DirectSaleApiTest` | 15/15 ✅ | Mixto + sin precio |
| `PosCatalogApiTest` | 9/9 ✅ | |
| `SettlementsCashUiFixTest` | 7/7 ✅ | |
| `CashierOrderCorrectionTest` | ✅ | Fase A |
| `OrderItemProductChangeTest` | ✅ | |
| `OrdersScopeKpiFixTest` | ✅ | |
| `GirlIncomeNoWaiterTest` | ❌ 5+ fail | Caja requerida |
| `QuickActionsPhaseBTest` | ❌ fail | Caja requerida |
| `QuickGirlCreateTest` | ❌ fail | Caja requerida |
| `SettlementsPhase16Test` | ❌ fail | Caja requerida |

## Apéndice B — Eventos SSE recomendados (V1-92 / V1-94)

| Evento | Emisor (use case) | Consumidores |
|--------|-------------------|--------------|
| `order.sent_to_bar` | `SendOrderToBarUseCase` | Barra futura, consola, garzón |
| `order.charged` / `sale.created` | `ChargeOrderUseCase`, `CreateDirectSaleUseCase` | Cajera, admin, fiscalización |
| `cash.movement.created` | Cobros, movimientos, liquidaciones | Mi caja, fiscalización |
| `room_service.due` | `RoomServiceDueNotifier` / scheduler | Limpieza, cajera |
| `room.cleaned` | `MarkRoomCleanUseCase` | Cajera, servicios |
| `settlement.paid` | `MarkSettlementPaidUseCase` | Admin, cierre turno |
| `cleaning.earnings.updated` | `MarkRoomCleanUseCase` | Limpieza móvil |

---

*Documento generado en fase V1-91. Sin cambios de código. Próxima acción recomendada: ejecutar V1-91.1 (estabilización) y luego V1-92 SSE-1 BASE.*
