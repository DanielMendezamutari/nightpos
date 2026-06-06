# OPERATION_CASH_FINANCE_AUDIT.md

**Fecha:** 2026-06-05  
**Alcance:** Operación, Caja y Finanzas — navegación, backend, frontend  
**Método:** Revisión de código, rutas API, permisos seeder, páginas Vue, documentación obligatoria. **Sin implementación.**

**Documentos revisados:**

| Documento | Hallazgo relevante |
|-----------|-------------------|
| `FRONTEND_GUIDELINES.md` | Frontend consume API; pantallas POS deben validar backend primero |
| `frontend/FRONTEND_AUDIT_REPORT.md` | Shell Materialize OK; pantallas operativas dispersas; navbar con widgets demo |
| `NIGHTPOS_MASTER_AUDIT.md` | Núcleo operativo ~65–70% producción; reportes/impresión pendientes |
| `NIGHTPOS_OPERATION_FLOW_AUDIT.md` | Flujo cajera coherente; módulos Finanzas placeholder; garzón/chica con brechas UX |
| `CASHIER_ORDER_AND_DIRECT_SALE_AUDIT.md` | Fase A/B completadas; venta directa ya implementada |
| `ORDERS_COMPLETE_AUDIT.md` | Scopes/KPI desalineados (mitigado parcialmente); estados IN_PREPARATION/READY huérfanos |
| `frontend/NAVIGATION_OPERATIONAL_UX_REPORT.md` | Reorden reciente; venta directa quedó solo en sección Caja |
| `backend/ADMIN_CASH_SESSIONS_REPORT.md` | Fiscalización multicaja funcional; separada de caja operativa |
| `backend/DIRECT_SALES_REPORT.md` | `POST /direct-sales` funcional; `order_id` null |
| `backend/SETTLEMENTS_CASH_UI_FIX_REPORT.md` | Liquidaciones funcionales; pago exige caja abierta |
| `backend/SERVICES_CASH_ACCOUNTING_FIX_REPORT.md` | Servicios registran ingreso en caja vía resolver compartido |

---

## 1. Estado actual del menú

Archivo activo: `frontend/src/navigation/vertical/nightpos-r4.js` (exportado desde `navigation/vertical/index.js`).

```
OPERACIÓN
  Dashboard operativo
  Consola de turno
  Cobrar comandas
  Comandas activas
  Servicios → Manillas / Piezas / Shows / Control piezas
  Habitaciones → Dashboard / Listado / Disponibles / Limpieza / Mantenimiento

CAJA
  Mi caja
  Venta directa          ← única entrada de menú para venta directa
  Ventas del turno
  Fiscalización de cajas

FINANZAS
  Liquidaciones → Resumen / Garzones / Chicas / Limpieza / Historial
  Cierre de turno
```

**No aparece en menú (pero rutas existen):**

| Ruta | Estado |
|------|--------|
| `nightpos-orders-new` | Funcional; acceso desde listado comandas, no menú |
| `nightpos-shifts-current/open/history` | Funcional; tabs internos, sin entrada menú |
| `nightpos-finance-movements` | Placeholder |
| `nightpos-finance-cash-close` | Placeholder |
| `nightpos-finance-reports` | Placeholder |
| `nightpos-finance-shift-close` | Redirect a `nightpos-shifts-close` |

**Accesos rápidos fuera del menú:**

- **Consola de turno** (`shift-console/index.vue`): Cobrar comandas, Venta directa, Mi caja, Servicios, Habitaciones, Liquidaciones.
- **Dashboard** (`dashboard.vue`): Comandas, Caja, Ventas, Productos — **sin** Cobrar comandas ni Venta directa.
- **Mi caja** (`cash/index.vue`): botón «Venta directa» solo si sesión abierta.

---

## 2. Estado actual backend

### 2.1 Operación

| Endpoint | Método | Permiso | Estado |
|----------|--------|---------|--------|
| `/shift-console/current` | GET | `shift_console.access` | **Funcional** — agregado KPIs turno/caja/comandas/habitaciones/liquidaciones |
| `/orders` | GET | `orders.access` | **Funcional** — scopes: active, open, sent_to_bar, pending_charge, cashier_chargeable, etc. |
| `/orders` | POST | `orders.create` | **Funcional** |
| `/orders/{id}` | GET | `orders.access` | **Funcional** |
| `/orders/{id}/items` | POST | `orders.add_items` | **Funcional** |
| `/orders/{id}/items/{itemId}` | PUT/DELETE | `orders.update_items` | **Funcional** |
| `/orders/{id}/items/{itemId}/cancel` | POST | `orders.cancel_item` | **Funcional** |
| `/orders/{id}` | PATCH | `orders.update_header` | **Funcional** |
| `/orders/{id}/send-to-bar` | POST | `orders.send_to_bar` | **Funcional** |
| `/orders/{id}/cancel` | POST | `orders.cancel` | **Funcional** |
| `/orders/{id}/charge` | POST | `sales.charge` | **Funcional** — crea sale + cash movement |
| `/direct-sales` | POST | `sales.direct_create` | **Funcional** — venta sin comanda |
| `/bracelets` | GET/POST | `bracelets.access/create` | **Funcional** |
| `/room-services` | GET/POST | `room_services.access/create` | **Funcional** |
| `/room-services/{id}/finish` | POST | `room_services.finish` | **Funcional** |
| `/room-services/{id}/check` | POST | `room_services.check` | **Funcional** |
| `/room-services/active`, `/due`, `/control` | GET | varios | **Funcional** |
| `/shows` | GET/POST | `shows.access/create` | **Funcional** |
| `/rooms` | GET/POST/PUT | `rooms.access/create/update` | **Funcional** |
| `/rooms/available`, `/cleaning` | GET | `rooms.access` | **Funcional** |
| `/rooms/{id}/mark-clean`, `/mark-maintenance`, `/mark-available` | POST | `rooms.clean/maintenance` | **Funcional** |
| `/waiter/*`, `/cleaning/*`, `/girl/*` | GET/POST | roles móviles | **Funcional** — fuera del sidebar admin |

**Placeholders backend Operación:** ninguno. Estados `IN_PREPARATION` / `READY` existen en dominio pero **ningún use case** los asigna en runtime normal.

### 2.2 Caja

| Endpoint | Método | Permiso | Estado |
|----------|--------|---------|--------|
| `/cash/session/current` | GET | `cash.access` | **Funcional** — sesión del usuario (`OpenCashSessionResolver`) |
| `/cash/session/open` | POST | `cash.access` | **Funcional** |
| `/cash/session/close` | POST | `cash.access` | **Funcional** |
| `/cash/movements` | POST | `cash.access` | **Funcional** — INCOME/EXPENSE manual |
| `/sales` | GET | `sales.list` | **Funcional** — listado ventas turno/sucursal |
| `/sales/{id}` | GET | `sales.list` | **Funcional** |
| `/direct-sales` | POST | `sales.direct_create` | **Funcional** — conceptualmente operación diaria, ruta bajo dominio venta/caja |
| `/admin/cash-sessions` | GET | `admin.cash_sessions.list` | **Funcional** — fiscalización multicaja |
| `/admin/cash-sessions/summary` | GET | `admin.cash_sessions.summary` | **Funcional** |
| `/admin/cash-sessions/{id}` | GET | `admin.cash_sessions.view` | **Funcional** |

**Faltan en backend Caja:**

- Endpoint consolidado de movimientos históricos cross-sesión (hoy solo en sesión actual + detalle admin).
- Export arqueo PDF/Excel (solo impresión frontend vía ruta print).
- API de reportes financieros (`reports.access` existe en permisos pero **no hay ruta API**).

### 2.3 Finanzas

| Endpoint | Método | Permiso | Estado |
|----------|--------|---------|--------|
| `/settlements/current-shift` | GET | `settlements.access` | **Funcional** |
| `/settlements/current-shift/pending-sources` | GET | `settlements.pending_sources` | **Funcional** |
| `/settlements/generate-current-shift` | POST | `settlements.generate` | **Funcional** |
| `/settlements/{id}` | GET | `settlements.access` | **Funcional** |
| `/settlements/{id}/mark-paid` | POST | `settlements.pay` | **Funcional** — egreso en caja (CLEANING y otros) |
| `/settlements/history` | GET | `settlements.history` | **Funcional** |
| `/shifts/current` | GET | `shifts.access` | **Funcional** |
| `/shifts` | GET | `shifts.list` | **Funcional** — historial |
| `/shifts/{id}` | GET | `shifts.list` | **Funcional** |
| `/shifts/{id}/summary` | GET | `shifts.list` | **Funcional** |
| `/shifts/{id}/export.csv` | GET | `shifts.list` | **Funcional** |
| `/shifts/open` | POST | `shifts.open` | **Funcional** |
| `/shifts/{id}/close` | POST | `shifts.close` | **Funcional** |

**Placeholders / faltantes Finanzas:**

| Concepto | Backend | Frontend |
|----------|---------|----------|
| Reportes financieros | **No existe API** | `finance/reports` = placeholder |
| Movimientos consolidados | Parcial (sesión actual + admin detalle) | `finance/movements` = placeholder |
| Cierre de caja dedicado | Cubierto por `cash/session/close` | `finance/cash-close` = placeholder redundante |

### 2.4 Duplicados y lógica repetida (backend)

| Duplicación | Detalle | Riesgo |
|-------------|---------|--------|
| `ChargeOrderUseCase` vs `CreateDirectSaleUseCase` | Ambos: validan caja, turno, precios, crean `Sale` + `SalePayment` + `CashMovement` | Mantenimiento doble; cambios de reglas deben aplicarse en dos sitios |
| `OpenCashSessionResolver` | Usado en caja, cobro, venta directa, servicios, liquidaciones | **Correcto** — resolver compartido, no duplicación problemática |
| `OrderItemPricing` en venta directa | Reutiliza pricing de comandas para productos sin comanda | **Correcto** pero acopla venta directa al módulo Order |
| Fiscalización bajo prefijo `/admin/` | API admin; UI bajo `/nightpos/finance/cash-sessions` y menú bajo **Caja** | Ubicación semántica inconsistente entre capas |
| Permiso `reports.access` | Existe en seeder (`tenant_owner`) | Sin endpoint ni UI funcional — permiso huérfano |

### 2.5 Rutas mal ubicadas (backend)

| Ruta / recurso | Ubicación actual | Ubicación semántica esperada |
|----------------|------------------|------------------------------|
| `POST /direct-sales` | Grupo ventas (junto a charge) | Correcto técnicamente; operativamente es acción diaria de cajera |
| `GET /admin/cash-sessions*` | Admin | Finanzas / auditoría (no caja operativa del usuario) |
| `POST /settlements/{id}/mark-paid` | Finanzas | Correcto; genera movimiento en caja operativa |
| Turnos (`/shifts/*`) | Transversal operación + finanzas | Cierre en Finanzas; apertura/historial más administrativo |

---

## 3. Estado actual frontend

### 3.1 Páginas por módulo

**Operación — funcionales:**

| Página | Ruta | Permiso |
|--------|------|---------|
| Dashboard operativo | `/nightpos/dashboard` | null (abierto) |
| Consola de turno | `/nightpos/shift-console` | `shift_console.access` |
| Cobrar comandas | `/nightpos/cashier/orders` | `sales.charge` |
| Comandas activas | `/nightpos/orders` | `orders.access` |
| Detalle comanda | `/nightpos/orders/:id` | `orders.access` |
| Nueva comanda | `/nightpos/orders/new` | `orders.create` |
| Servicios (manillas/piezas/shows/control) | `/nightpos/services/*` | varios |
| Habitaciones | `/nightpos/rooms/*` | `rooms.access` |
| Venta directa | `/nightpos/cash/direct-sale` | `sales.direct_create` |

**Caja — funcionales:**

| Página | Ruta | Permiso |
|--------|------|---------|
| Mi caja | `/nightpos/cash` | `cash.access` |
| Venta directa | `/nightpos/cash/direct-sale` | `sales.direct_create` |
| Ventas del turno | `/nightpos/sales` | `sales.list` |
| Fiscalización multicaja | `/nightpos/finance/cash-sessions/*` | `admin.cash_sessions.*` |

**Finanzas — funcionales:**

| Página | Ruta | Permiso |
|--------|------|---------|
| Liquidaciones resumen | `/nightpos/settlements` | `settlements.access` |
| Liquidaciones por rol | `/nightpos/settlements/waiters|girls|cleaning` | `settlements.access` |
| Historial liquidaciones | `/nightpos/settlements/history` | `settlements.history` |
| Detalle liquidación | `/nightpos/settlements/:id` | `settlements.access` |
| Cierre de turno | `/nightpos/shifts/close` | `shifts.close` |

**Finanzas / Caja — placeholders (rutas huérfanas):**

| Página | Ruta | Nota |
|--------|------|------|
| Movimientos de caja | `/nightpos/finance/movements` | Redirige mentalmente a Mi caja; no en menú |
| Cierre de caja | `/nightpos/finance/cash-close` | Duplica Mi caja; no en menú |
| Reportes financieros | `/nightpos/finance/reports` | Placeholder; `tenant_owner` tiene permiso pero no menú |
| Cierre turno (alias) | `/nightpos/finance/shift-close` | Redirect a `shifts/close` |

**Turnos — funcionales sin menú:**

| Página | Ruta | Permiso |
|--------|------|---------|
| Turno actual | `/nightpos/shifts/current` | `shifts.access` |
| Abrir turno | `/nightpos/shifts/open` | `shifts.open` |
| Historial turnos | `/nightpos/shifts/history` | `shifts.list` |

Accesibles vía tabs internos (`useShiftSectionTabs`) solo si el usuario navega a una ruta de turnos directamente.

### 3.2 Guards y permisos frontend

- Router guard (`plugins/1.router/guards.js`): redirige garzón → `/waiter`, limpieza → `/cleaning`, chica → `/girl`; valida `meta.permission`.
- Menú: filtrado CASL + `useNightPosNavItems` (superadmin sin contexto operativo oculta módulos sucursal).
- Home cajera: `resolveHomeRoute` → **Consola de turno** si tiene `shift_console.access`.

### 3.3 Qué ve cada rol

#### Cajera (`cashier`)

**Menú visible:**

- Operación: Dashboard, Consola, Cobrar comandas, Comandas activas, Servicios, Habitaciones
- Caja: Mi caja, **Venta directa**, Ventas del turno — **sin** Fiscalización
- Finanzas: Liquidaciones, Cierre de turno

**No ve:** Catálogo, Personal, Configuración, Plataforma, Fiscalización.

**Permisos demo relevantes:** `shift_console.access`, `sales.charge`, `sales.direct_create`, `cash.access`, `settlements.*`, servicios, habitaciones. **Sin** `admin.cash_sessions.*`, `shifts.open`, `shifts.list`, `reports.access`.

#### Admin (`tenant_owner`)

**Menú visible:** Operación + Caja + Finanzas + Catálogo + Personal + Configuración.

**Incluye:** Fiscalización de cajas, todas las liquidaciones, cierre de turno.

**No ve en menú:** Reportes financieros (ruta placeholder existe; permiso `reports.access` asignado).

#### Cajera senior (`cashier_senior`)

Similar a cajera + **Fiscalización de cajas** (`admin.cash_sessions.*`).

#### Superadmin con contexto operativo

Todo lo del admin + Plataforma SaaS al final del menú.

#### Superadmin sin contexto

Solo Plataforma SaaS (sin Operación/Caja/Finanzas).

---

## 4. Qué está bien

| Área | Evidencia |
|------|-----------|
| Backend operativo núcleo | Comandas, cobro, venta directa, caja, servicios, habitaciones, liquidaciones, turnos — APIs con tests |
| Separación caja operativa vs fiscalización | `OpenCashSessionResolver` vs `admin/cash-sessions` documentado y respetado |
| Vista cajera dedicada | `/cashier/orders` con scope `cashier_chargeable` |
| Venta directa funcional end-to-end | UI + `POST /direct-sales` + movimiento caja + liquidación chica CON_ACOMPANANTE |
| Consola de turno | Agregación operativa + accesos rápidos recientes |
| Liquidaciones | Generar, pagar, historial; banner caja; sin congelamiento (fix aplicado) |
| Guards por rol móvil | Garzón/limpieza/chica aislados del sidebar admin |
| Menú sin duplicados Caja/Ventas | Mejora respecto a versión anterior (FASE UX OPERATIVA) |
| Sidebar estable desktop | Breakpoint overlay corregido a 960px |
| Venta directa con caja cerrada | Página muestra alerta + `QuickOpenCashDialog` — UX correcta |

---

## 5. Qué está mal

| # | Problema | Impacto |
|---|----------|---------|
| M1 | **Venta directa solo en menú Caja**, no en Operación | Uso diario menos visible; contradice flujo mental de la cajera |
| M2 | Ruta física `/nightpos/cash/direct-sale` refuerza que «es de Caja» | URL y breadcrumbs no reflejan prioridad operativa |
| M3 | **Dashboard** sin accesos a Cobrar comandas ni Venta directa | Punto de entrada débil vs consola de turno |
| M4 | Botón «Venta directa» en Mi caja **oculto si caja cerrada** | Inconsistente: menú sí enlaza; usuario puede entrar pero no desde caja |
| M5 | **Fiscalización en sección Caja** del menú | Mezcla operación diaria (mi caja) con auditoría admin |
| M6 | **Turnos** (actual/abrir/historial) sin entrada menú | Admin no descubre gestión de turno salvo cierre en Finanzas |
| M7 | **Reportes financieros**: permiso sin API ni UI real | Falsa expectativa para admin |
| M8 | Rutas placeholder financieras existen (`movements`, `cash-close`, `reports`) | Riesgo de enlaces rotos o confusión si se descubren por URL |
| M9 | Detalle comanda cajera: «Volver» va a `nightpos-orders`, no a `cashier-orders` | Rompe flujo cobro/corrección |
| M10 | Estados `IN_PREPARATION` / `READY` en UI/KPI sin transición real | KPIs y filtros engañosos |

---

## 6. Qué está duplicado

| Elemento | Dónde aparece | Recomendación |
|----------|---------------|---------------|
| Venta directa (acceso) | Menú Caja + botón Mi caja + consola turno | Mantener múltiples accesos; **agregar Operación** como primario |
| Cierre de turno | `shifts/close` + `finance/shift-close` (redirect) | Eliminar alias o no exponer |
| Cierre de caja | `cash/index` (completo) + `finance/cash-close` (placeholder) | Ocultar/eliminar placeholder |
| Movimientos caja | `cash/index` (tabla en sesión) + `finance/movements` (placeholder) | Consolidar en Mi caja |
| Lógica creación venta | `ChargeOrderUseCase` + `CreateDirectSaleUseCase` | Extraer servicio compartido (fase técnica posterior) |
| Archivo nav legacy | `nightpos.js` vs `nightpos-r4.js` | `nightpos.js` no se importa; candidato a limpieza documental |
| Ventas listado | Menú «Ventas del turno» en Caja | Correcto una sola vez (ya no duplicado en Finanzas) |

---

## 7. Qué está mal ubicado

| Elemento | Ubicación actual | Ubicación correcta según negocio |
|----------|------------------|----------------------------------|
| **Venta directa** (menú) | Caja | **Operación** (primario) + acceso secundario en Caja/consola |
| **Venta directa** (ruta URL) | `/nightpos/cash/direct-sale` | Considerar alias `/nightpos/operation/direct-sale` o `/nightpos/direct-sale` |
| **Fiscalización multicaja** | Menú Caja; páginas bajo `finance/cash-sessions` | **Caja** (admin/senior) o **Finanzas** — hoy carpeta frontend ≠ menú |
| **Cierre de turno** | Finanzas (menú) | Correcto en Finanzas |
| **Ventas del turno** | Caja | Correcto en Caja (consulta post-cobro) |
| **Nueva comanda** | Sin menú; solo botón en listado | Operación (admin) o solo garzón — hoy cajera puede crear vía API pero sin menú claro |
| **Dashboard operativo** | Operación | Candidato a fusionar con Consola o relegar |

---

## 8. Qué debería moverse

### Menú (prioridad alta)

1. **Venta directa** → de Caja a **Operación** (después de «Cobrar comandas»).
2. Mantener en Caja: Mi caja, Ventas del turno, Fiscalización (solo roles con permiso).
3. **Fiscalización** — decidir: quedarse en Caja (como pide el negocio) o mover a Finanzas; alinear carpeta `finance/cash-sessions` con decisión.
4. **Historial turnos / Abrir turno** — agregar submenú Finanzas o Configuración para admin (`shifts.open`, `shifts.list`).

### Accesos rápidos

5. Actualizar **dashboard** para igualar consola: Cobrar, Venta directa, Mi caja, Servicios, Liquidaciones.
6. Botón Venta directa en Mi caja: mostrar **siempre** (con permiso), no solo con sesión abierta — la página ya maneja caja cerrada.

### Rutas

7. Alias de ruta venta directa bajo namespace operativo (opcional, no romper `/cash/direct-sale`).
8. Ocultar o eliminar rutas placeholder: `finance/movements`, `finance/cash-close`, `finance/reports`, `finance/shift-close`.

---

## 9. Qué debería ocultarse

| Elemento | Motivo |
|----------|--------|
| `finance/reports` | Sin API; placeholder |
| `finance/movements` | Redundante con Mi caja |
| `finance/cash-close` | Redundante con Mi caja |
| `finance/shift-close` | Redirect innecesario si menú apunta a `shifts/close` |
| Reportes en menú | Hasta existir API + UI real |
| Dashboard operativo (opcional) | Si consola de turno es el home real de cajera |

**No ocultar:** Fiscalización (admin/senior), Historial liquidaciones, Ventas del turno, Cierre de turno.

---

## 10. Qué falta implementar

| Prioridad | Item | Capa |
|-----------|------|------|
| Alta | Venta directa visible en menú **Operación** | Frontend nav |
| Alta | Alinear accesos dashboard con consola | Frontend |
| Media | API reportes financieros | Backend + Frontend |
| Media | Export arqueo / cierre turno PDF | Backend + Frontend |
| Media | Servicio compartido creación venta (charge + direct) | Backend refactor |
| Media | Entrada menú gestión turnos (abrir/historial) para admin | Frontend nav |
| Baja | Alias ruta venta directa operativa | Frontend router |
| Baja | Eliminar páginas placeholder financieras | Frontend |
| Baja | Transiciones reales IN_PREPARATION/READY o eliminar de KPIs | Backend + Frontend |
| Baja | Permiso `reports.access` — implementar o quitar del seeder | Backend |

---

## 11. Propuesta final de menú

Alineada con observación de negocio y estado real del sistema:

```
OPERACIÓN
  Consola de turno
  Cobrar comandas
  Venta directa              ← PRIMARIO (uso diario)
  Comandas activas
  Servicios
    Manillas / Piezas / Shows / Control piezas
  Habitaciones
    Dashboard / Listado / Disponibles / Limpieza / Mantenimiento
  [Opcional admin] Nueva comanda

CAJA
  Mi caja                    ← apertura, movimientos, cierre sesión
  Ventas del turno           ← consulta ventas cobradas
  Fiscalización de cajas   ← solo admin / cajera senior

FINANZAS
  Liquidaciones
    Resumen / Garzones / Chicas / Limpieza / Historial
  Cierre de turno
  [Futuro] Reportes financieros   ← solo cuando exista API
  [Opcional admin] Turnos
    Turno actual / Abrir / Historial

--- fuera de estos tres bloques ---
Catálogo / Personal / Configuración / Plataforma SaaS (sin cambios)
```

**Accesos rápidos unificados** (consola + dashboard + opcional sticky bar):

Cobrar comandas · Venta directa · Mi caja · Servicios · Habitaciones · Liquidaciones

---

## 12. Propuesta final de rutas

| Función | Ruta recomendada | Ruta actual | Acción |
|---------|------------------|-------------|--------|
| Venta directa | `/nightpos/direct-sale` o mantener `/nightpos/cash/direct-sale` | `/nightpos/cash/direct-sale` | Agregar alias; menú apunta a ruta canónica |
| Cobrar comandas | `/nightpos/cashier/orders` | Igual | OK |
| Mi caja | `/nightpos/cash` | Igual | OK |
| Fiscalización | `/nightpos/finance/cash-sessions` | Igual | OK; alinear con menú Caja |
| Liquidaciones | `/nightpos/settlements` | Igual | OK |
| Cierre turno | `/nightpos/shifts/close` | Igual | OK |
| Turno admin | `/nightpos/shifts/current` | Igual | Agregar a menú Finanzas (admin) |
| Reportes | — | `/nightpos/finance/reports` | Ocultar hasta implementar |
| Placeholders | — | `finance/movements`, `finance/cash-close` | Deprecar / 404 suave |

**API — sin cambios urgentes.** Opcional futuro: `GET /reports/financial-summary` con `reports.access`.

---

## 13. Prioridad de corrección

| Orden | Tarea | Esfuerzo | Impacto |
|-------|-------|----------|---------|
| **P0** | Mover «Venta directa» al menú **Operación** (mantener secundario en Caja/consola) | Bajo | Alto — visibilidad uso diario |
| **P0** | Unificar accesos rápidos dashboard = consola | Bajo | Alto |
| **P1** | Botón Venta directa en Mi caja visible con caja cerrada | Muy bajo | Medio |
| **P1** | Ocultar rutas placeholder financieras del router o marcar `meta.hidden` | Bajo | Medio — evita confusión |
| **P1** | Corregir «Volver» en detalle comanda modo cajera → `cashier-orders` | Bajo | Medio |
| **P2** | Menú turnos (abrir/historial) para admin en Finanzas | Bajo | Medio |
| **P2** | Decisión y documentación: fiscalización en Caja vs Finanzas | Bajo | Bajo |
| **P3** | Alias ruta venta directa bajo `/operation/` | Medio | Bajo |
| **P3** | Refactor servicio venta compartido backend | Alto | Medio (deuda técnica) |
| **P4** | API + UI reportes financieros | Alto | Alto (post-MVP) |

---

## 14. Riesgos si no se corrige

| Riesgo | Consecuencia |
|--------|--------------|
| Venta directa escondida en Caja | Cajeras buscan en Operación; fricción diaria; subutilización del feature |
| Dashboard desalineado | Usuarios ignoran dashboard; dependencia total del menú lateral |
| Placeholders descubribles | Admin entra a «Reportes» vacíos; pérdida de confianza |
| Fiscalización mal agrupada | Cajeras confunden «mi caja» con «todas las cajas» |
| Turnos sin menú | Cierre de turno sin contexto de turno actual/historial |
| Lógica venta duplicada | Reglas de cobro divergen entre comanda y venta directa en futuras fases |
| KPIs IN_PREPARATION/READY | Decisiones operativas basadas en estados que no ocurren |

---

## Preguntas clave — respuestas

### Venta directa

| Pregunta | Respuesta |
|----------|-----------|
| ¿Está en Caja solamente? | **En menú: sí.** También en consola turno y botón Mi caja. **No está en Operación.** |
| ¿Debe duplicarse en Operación? | **Sí** — regla de negocio correcta: primario en Operación, secundario en Caja. |
| ¿El botón aparece si la caja está cerrada? | **En Mi caja: no** (`v-if="session"`). **En menú y consola: sí** siempre. |
| ¿Debe abrir caja desde ahí? | **Sí** — la página `direct-sale.vue` ya ofrece `QuickOpenCashDialog`. |

### Finanzas

| Pregunta | Respuesta |
|----------|-----------|
| ¿Es basurero? | **Parcialmente** — tiene placeholders huérfanos; liquidaciones y cierre turno están bien. |
| ¿Qué debe contener? | Liquidaciones, pagos, cierre turno, historial, reportes reales (futuro). |
| ¿Qué no debe contener? | Venta directa diaria, comandas, acciones caja diaria (excepto egresos por liquidación). |

### Caja

| Pregunta | Respuesta |
|----------|-----------|
| ¿Contenido correcto? | Mi caja (abrir/cerrar/movimientos) + ventas consulta + fiscalización admin. |
| ¿Venta directa aquí? | **Acceso secundario OK** (botón rápido); no como única entrada de menú. |

### Operación

| Pregunta | Respuesta |
|----------|-----------|
| ¿Contenido correcto? | Casi completo; **falta Venta directa** en menú. |
| ¿Consola vs dashboard? | Consola es el centro real; dashboard quedó rezagado. |

---

## Plan siguiente fase (NO implementar aún)

### FASE UX FINAL — NAVEGACIÓN OPERATIVA

**Objetivo:** Menú, rutas y accesos rápidos alineados al uso real del boliche; Operación como centro de acciones diarias; Caja y Finanzas sin mezclar operación con auditoría.

**Alcance propuesto:**

1. Reordenar `nightpos-r4.js` según propuesta §11 (Venta directa en Operación).
2. Mantener acceso secundario Venta directa en Mi caja y consola (ya existe).
3. Unificar shortcuts en `dashboard.vue`.
4. Ajustar botón Mi caja (visible con caja cerrada).
5. Ocultar/deprecar placeholders financieros.
6. Corregir navegación «Volver» flujo cajera.
7. Opcional: submenú Turnos para admin en Finanzas.
8. Documentar en `frontend/NAVIGATION_UX_FINAL_REPORT.md`.
9. Validación manual: cajera, admin, cajera senior.

**Fuera de alcance de esa fase:**

- Nuevos endpoints reportes.
- Refactor backend venta compartida.
- Cambios garzón/limpieza/chica.

**Criterio de cierre:**

- Cajera encuentra Venta directa en Operación en ≤2 clics.
- Sin placeholders visibles en menú.
- Dashboard y consola con mismos accesos operativos.
- Sidebar estable en desktop (ya corregido).

---

*Auditoría completada. Sin cambios de código.*
