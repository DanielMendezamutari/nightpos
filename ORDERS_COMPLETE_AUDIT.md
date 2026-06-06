# Auditoría completa del módulo de comandas — NightPOS

**Fecha:** 2026-06-02  
**Alcance:** Estados, scopes, rutas, roles, KPIs, UX y consistencia frontend/backend.  
**Método:** Revisión de código, seeders, reportes de fase y documentación obligatoria. **Sin implementación.**

**Referencias revisadas:** `DOMAIN_DESIGN.md`, `FRONTEND_GUIDELINES.md`, `NIGHTPOS_OPERATION_FLOW_AUDIT.md`, `NIGHTPOS_MASTER_AUDIT.md`, `PHASE_7_REPORT.md`, `PHASE_9_REPORT.md`, `PHASE_C1_REPORT.md`, `PHASE_C2_REPORT.md`, `PHASE_C4_WAITER_REPORT.md`, `CASHIER_ORDER_CORRECTION_REPORT.md`, `OPERATIONAL_ROLE_FLOW_FIX_REPORT.md`.

---

## Resumen ejecutivo

El módulo de comandas **funciona en el circuito feliz** (crear → agregar → enviar barra → cobrar), pero hay **desalineación sistemática** entre:

1. **Qué estados existen en dominio** vs **qué estados usa la operación real** (solo `OPEN` → `SENT_TO_BAR` → `BILLED`).
2. **Qué filtro usa cada pantalla** (casi todas las vistas admin/cajera usan solo `status=OPEN`).
3. **Qué ámbito cuenta cada KPI** (garzón = sus comandas + turno; cajera/consola = sucursal; admin dashboard = solo OPEN).

El síntoma reportado — *«existe una comanda pero aparece “No hay comandas abiertas”»* — tiene **causa raíz identificada** y es reproducible: la comanda está en `SENT_TO_BAR` (u otro estado distinto de `OPEN`) y el listado consultado filtra únicamente `OPEN`.

---

## 1. Mapa completo de estados

### 1.1 Estados definidos en dominio

Fuente: `OrderStatus` (`backend/app/Domain/Order/ValueObjects/OrderStatus.php`), labels en `useOrderHelpers.js`.

| Estado | Label UI | Significado de negocio | ¿Se alcanza hoy en runtime? |
|--------|----------|------------------------|----------------------------|
| `OPEN` | Abierta | Comanda creada; ítems editables (garzón/cajera según permisos) | **Sí** — creación y hasta envío a barra |
| `SENT_TO_BAR` | En barra | Enviada a preparación; extras y corrección limitada | **Sí** — `SendOrderToBarUseCase` |
| `IN_PREPARATION` | En preparación | Barra preparando (sin edición de líneas) | **Solo demo seed** (`W-DEMO-05`); **ningún use case** lo asigna en operación normal |
| `READY` | Lista | Lista para entregar/cobrar (solo cobro) | **Solo demo seed** (`W-DEMO-04`); **ningún use case** lo asigna en operación normal |
| `BILLED` | Cobrada | Venta creada; solo lectura | **Sí** — `ChargeOrderUseCase` |
| `CANCELLED` | Cancelada | Anulada pre-cobro | **Sí** — `CancelOrderUseCase` |

**Transición real implementada:**

```text
OPEN ──send-to-bar──► SENT_TO_BAR ──charge──► BILLED
  │                        │
  └──── cancel ────────────┴──── cancel ───► CANCELLED
```

`IN_PREPARATION` y `READY` están en scopes, KPIs y UI, pero **no hay integración con barra/cocina** que cambie el estado. En producción operativa casi todo post-envío queda en `SENT_TO_BAR` hasta el cobro.

### 1.2 Estados de línea (`order_items.item_status`)

| Valor | Significado |
|-------|-------------|
| `PENDING` | No enviado a barra |
| `SENT` | Enviado en `send-to-bar` |
| `CANCELLED` | Línea anulada (soft cancel, Fase A cajera) |

Los totales y el cobro excluyen líneas `CANCELLED` (post Fase A).

---

## 2. Mapa de roles

| Rol | Rutas principales | Listados que ve | Detalle que usa | Cobrar |
|-----|-------------------|-----------------|-----------------|--------|
| **Garzón** | `/nightpos/waiter`, `/waitpos/waiter/orders`, `/waitpos/waiter/orders/:id` | Solo **sus** comandas (`waiter_user_id`) | Vista móvil propia | No |
| **Cajera** | `/nightpos/cashier/orders`, `/nightpos/orders/:id?from=cashier` | **Todas** la sucursal (`cashier_chargeable`) | Vista compartida + modo corrección | Sí |
| **Admin / tenant_owner** | `/nightpos/orders`, `/nightpos/orders/:id`, consola turno | `GET /orders?status=OPEN` (solo OPEN) | Vista compartida completa | Sí (si permisos) |
| **Chica** | `/nightpos/girl` | No comandas | — | No |

**Aislamiento garzón:** `WaiterOrderAccessPolicy` — solo `staff_role === 'WAITER'`. Cajera/admin ven todas las comandas de la sucursal.

---

## 3. Flujo real actual (por rol)

### 3.1 Garzón — `/nightpos/waiter`

```
Dashboard (GET /waiter/dashboard)
  ├── KPI Abiertas      → count status=OPEN (solo propias, turno actual)
  ├── KPI En barra      → count status=SENT_TO_BAR
  ├── KPI Pend. cobro   → count SENT_TO_BAR + IN_PREPARATION + READY  ⚠️ solapa con “En barra”
  └── Recientes         → activas (OPEN…READY), max 6

Lista /waiter/orders?scope=
  ├── active (default)  → OPEN, SENT_TO_BAR, IN_PREPARATION, READY
  ├── open              → solo OPEN
  ├── sent_to_bar       → solo SENT_TO_BAR
  └── pending_charge    → SENT_TO_BAR, IN_PREPARATION, READY  ⚠️ incluye SENT_TO_BAR otra vez

Detalle /waiter/orders/:id
  ├── OPEN: gestionar, + producto, enviar barra
  ├── SENT_TO_BAR: ver, agregar extra
  ├── IN_PREPARATION/READY: ver + aviso “pendiente cobro”
  └── BILLED: ver historial
```

**Filtro de turno:** garzón filtra por `official_shift_id` del turno abierto cuando existe. Sin turno abierto → ve comandas de **todos los turnos** (histórico mezclado).

### 3.2 Cajera — `/nightpos/cashier/orders`

```
Lista única (GET /orders?scope=cashier_chargeable)
  → OPEN, SENT_TO_BAR, IN_PREPARATION, READY
  → Toda la sucursal, SIN filtro de turno por defecto
  → CTAs: Ver/corregir | Cobrar

Detalle /orders/:id?from=cashier
  → Modo corrección (Fase A) + cobro
  → Volver a /cashier/orders
```

**No hay pestañas ni priorización** (pendientes primero, abiertas, historial).

### 3.3 Admin / operación desktop — `/nightpos/orders`

```
Lista (GET /orders?status=OPEN)  ⚠️ SOLO OPEN
  → Mensaje: "No hay comandas abiertas"
  → No muestra SENT_TO_BAR ni pendientes de cobro

Detalle /orders/:id
  → Vista compartida; modo corrección si tiene orders.update_items
```

**Atajo consola turno** enlaza a `nightpos-orders` (mismo listado solo OPEN) — incoherente con sus propios contadores.

### 3.4 Consola de turno — `/nightpos/shift-console`

```
KPIs (turno actual, toda la sucursal):
  - open_orders        → count OPEN
  - pending_charge_orders → count SENT_TO_BAR + IN_PREPARATION + READY

Pestañas comandas:
  - Abiertas / En barra / Pend. cobro / Cobradas recientes
```

**Solapamiento:** pestaña «Pend. cobro» **incluye** comandas ya listadas en «En barra» (`SENT_TO_BAR` está en ambos conjuntos).

### 3.5 Dashboard admin — `/nightpos/dashboard`

```
KPI "Comandas abiertas" → fetchOrders('OPEN') → solo OPEN
Subtitle explícito: "Estado OPEN"
```

Coherente consigo mismo, **incoherente** con consola/cajera si hay comandas en barra.

---

## 4. Mapa de rutas y APIs

### 4.1 Frontend — rutas involucradas

| Ruta | Rol | Componente | API listado |
|------|-----|------------|-------------|
| `/nightpos/waiter` | Garzón | `waiter/index.vue` | `GET /waiter/dashboard` |
| `/nightpos/waiter/orders` | Garzón | `waiter/orders/index.vue` | `GET /waiter/orders?scope=` |
| `/nightpos/waiter/orders/new` | Garzón | `waiter/orders/new.vue` | `POST /orders` |
| `/nightpos/waiter/orders/:id` | Garzón | `waiter/orders/[id].vue` | `GET /orders/:id` |
| `/nightpos/cashier/orders` | Cajera | `cashier/orders/index.vue` | `GET /orders?scope=cashier_chargeable` |
| `/nightpos/orders` | Admin/cajera | `orders/index.vue` | `GET /orders?status=OPEN` |
| `/nightpos/orders/new` | Admin/cajera | `orders/new.vue` | `POST /orders` |
| `/nightpos/orders/:id` | Todos* | `orders/[id].vue` | `GET /orders/:id` |
| `/nightpos/shift-console` | Admin/cajera | `shift-console/index.vue` | `GET /shift-console/current` |
| `/nightpos/dashboard` | Admin | `dashboard.vue` | `GET /orders?status=OPEN` (KPI) |

\*Garzón usa preferentemente `/waiter/orders/:id`; puede acceder a `/orders/:id` si navega manualmente.

### 4.2 Backend — endpoints de comandas

| Método | Ruta | Consumidor típico |
|--------|------|-------------------|
| `GET` | `/api/v1/orders` | Admin listado, cajera (`scope`), dashboard KPI |
| `GET` | `/api/v1/orders/{id}` | Todos los detalles |
| `POST` | `/api/v1/orders` | Nueva comanda |
| `PATCH` | `/api/v1/orders/{id}` | Corrección cabecera (cajera) |
| `POST/PUT/DELETE` | `/api/v1/orders/{id}/items…` | Ítems |
| `POST` | `/api/v1/orders/{id}/send-to-bar` | Garzón |
| `POST` | `/api/v1/orders/{id}/cancel` | Cajera/admin |
| `POST` | `/api/v1/orders/{id}/charge` | Cajera |
| `GET` | `/api/v1/waiter/dashboard` | Dashboard garzón |
| `GET` | `/api/v1/waiter/orders?scope=` | Lista garzón |
| `GET` | `/api/v1/waiter/orders/active` | Alias `scope=active` |

**Parámetros `GET /orders` (genérico):**

| Parámetro | Efecto |
|-----------|--------|
| `status=OPEN` | Un solo estado |
| `scope=cashier_chargeable` | `OPEN`, `SENT_TO_BAR`, `IN_PREPARATION`, `READY` |
| `current_shift=1` | Filtra por turno oficial abierto (**existe en backend; frontend no lo usa**) |

**Parámetros `GET /waiter/orders` (solo garzón):**

| scope | Estados |
|-------|---------|
| `active` (default) | OPEN, SENT_TO_BAR, IN_PREPARATION, READY |
| `open` | OPEN |
| `sent_to_bar` | SENT_TO_BAR |
| `pending_charge` | SENT_TO_BAR, IN_PREPARATION, READY |

**No existen** `active`, `open`, `sent_to_bar`, `pending_charge` en `GET /orders` general — solo en API garzón.

---

## 5. Problemas encontrados

### 5.1 Críticos (operación / confusión inmediata)

| ID | Problema | Evidencia |
|----|----------|-----------|
| **O-01** | **Listado admin «Comandas» oculta comandas existentes** | `orders/index.vue` → `fetchOrders('OPEN')`. Comanda en `SENT_TO_BAR` → mensaje *«No hay comandas abiertas»* |
| **O-02** | **Consola turno enlaza a listado incorrecto** | Cards y tablas muestran pendientes/en barra; `to: 'nightpos-orders'` abre solo OPEN |
| **O-03** | **Dos pantallas de detalle** (waiter vs shared) con comportamiento distinto | Duplicación de lógica; riesgo de regresión al corregir una sola |

### 5.2 Altos (KPIs y scopes)

| ID | Problema | Evidencia |
|----|----------|-----------|
| **O-04** | **Doble conteo «En barra» vs «Pendiente cobro»** | `pending_charge` incluye `SENT_TO_BAR` en waiter dashboard, shift console y scopes |
| **O-05** | **Ámbito distinto por rol sin etiquetar** | Garzón: filtro turno + solo propias. Cajera: sucursal sin turno. Admin KPI: solo OPEN sucursal |
| **O-06** | **Estados fantasma `IN_PREPARATION` / `READY`** | En scopes/KPIs/UI pero sin transición automática en backend (salvo seed demo) |
| **O-07** | **`current_shift=1` no usado en frontend** | Backend lo soporta (`ListOrdersUseCase`); ningún listado UI lo envía → mezcla turnos en cajera |

### 5.3 Medios (UX / expectativa vs realidad)

| ID | Problema | Evidencia |
|----|----------|-----------|
| **O-08** | Cajera: listado plano sin secciones | Una sola lista `cashier_chargeable`; sin prioridad «pendientes primero» |
| **O-09** | Garzón: scope `open` no incluye `SENT_TO_BAR` editable | Propuesta operativa: «abiertas» = OPEN + barra editable; hoy están separadas |
| **O-10** | Navegación menú «Listado» vs «Cobrar comandas» duplicada | `nightpos-r4.js`: dos entradas que no muestran el mismo universo |
| **O-11** | Mensajes vacíos genéricos | «No hay comandas abiertas» cuando el usuario busca «cualquier comanda activa» |

### 5.4 Bajos

| ID | Problema |
|----|----------|
| **O-12** | Label «Comandas abiertas» en dashboard admin es técnicamente correcto (OPEN) pero engaña al usuario operativo |
| **O-13** | `WaiterOrderActions` trata `SENT_TO_BAR` como pendiente cobro en KPI pero como «en barra» en otra tarjeta |

---

## 6. Inconsistencias detectadas (ejemplos concretos)

### 6.1 Caso reproducible del bug reportado

| Paso | Resultado |
|------|-----------|
| Garzón crea comanda y envía a barra | `status = SENT_TO_BAR` |
| Admin va a **Operación → Listado** (`/nightpos/orders`) | `GET /orders?status=OPEN` → **lista vacía** |
| Mensaje UI | *«No hay comandas abiertas. Cree una nueva para empezar.»* |
| Cajera va a **Cobrar comandas** | **Sí ve** la comanda (`cashier_chargeable`) |
| Consola turno | «En barra» = 1, «Comandas abiertas» KPI card puede = 0 |

**Conclusión:** la comanda **existe**; el listado consultado **filtra mal** para la expectativa del usuario.

### 6.2 Matriz de conteo (misma noche, 1 comanda SENT_TO_BAR garzón A)

| Fuente | ¿La ve? | Contador relevante |
|--------|---------|-------------------|
| Garzón dashboard «Abiertas» | No | 0 |
| Garzón dashboard «En barra» | Sí | 1 |
| Garzón dashboard «Pend. cobro» | Sí | 1 (misma comanda) |
| Admin `/nightpos/orders` | No | 0 (mensaje vacío) |
| Admin dashboard KPI | No | 0 |
| Cajera `/cashier/orders` | Sí | 1 en lista |
| Shift console «Abiertas» | No | 0 |
| Shift console «En barra» | Sí | 1 |
| Shift console «Pend. cobro» | Sí | 1 (duplicada conceptualmente) |

### 6.3 Misma comanda, acciones distintas según entrada

| Entrada | Agregar ítem en SENT_TO_BAR | Corregir cantidad | Cobrar |
|---------|----------------------------|-------------------|--------|
| `/waiter/orders/:id` | Sí (UI) | No (sin permiso API) | No |
| `/orders/:id` (cajera) | Sí | Sí (Fase A) | Sí |
| `/orders/:id` (admin sin update_items) | Según permisos | No | Sí |

---

## 7. Detalle de comanda — matriz rol × estado (actual vs esperado)

### Garzón

| Estado | Esperado (negocio) | Actual |
|--------|---------------------|--------|
| OPEN | Agregar, modificar propio | Sí — detalle waiter |
| SENT_TO_BAR | Agregar extra | Sí |
| READY / IN_PREPARATION | Solo lectura | Sí + aviso cobro |
| BILLED | Lectura | Sí |

### Cajera

| Estado | Esperado | Actual (post Fase A) |
|--------|----------|----------------------|
| OPEN | Corregir, cobrar | Sí — `/orders/:id?from=cashier` |
| SENT_TO_BAR | Corregir según reglas, cobrar | Sí — chica, cancel línea, add extra |
| READY / IN_PREPARATION | Cobrar | Sí cobrar; sin edición líneas |
| BILLED | Lectura | Sí |

### Admin

| Estado | Esperado | Actual |
|--------|----------|--------|
| Todos operativos | Todo según permisos | Sí en API; **listado** solo muestra OPEN |
| BILLED | Lectura / ventas | Vía detalle o ventas, no listado comandas |

---

## 8. Análisis UX

### 8.1 ¿El usuario entiende dónde están sus comandas?

| Rol | Claridad | Motivo |
|-----|----------|--------|
| Garzón móvil | **Media-alta** | Dashboard con KPIs + scopes; pero solapamiento barra/cobro confunde |
| Cajera | **Media** | Una lista mezcla todo; no distingue «urgente cobro» vs «aún editable» |
| Admin desktop | **Baja** | Listado principal miente por omisión (solo OPEN) |

### 8.2 Fricción / clics

- Garzón OPEN: dashboard → lista → gestionar (3 clics) — aceptable móvil.
- Cajera cobro: cashier/orders → cobrar → modal (2 clics) — bien.
- Cajera corrección: cashier/orders → ver/corregir → menú línea (3+ clics) — aceptable.
- Admin busca comanda en barra: menú → listado vacío → **dead end** — **grave**.

### 8.3 Pantallas con datos distintos para la misma comanda

- **Sí:** misma comanda visible en cajera y oculta en admin listado.
- **Sí:** contadores garzón (propias) ≠ contadores consola (sucursal).
- **No** hay divergencia de `total`/`status` en API para el mismo `id` (detalle consistente).

---

## 9. Materialize — componentes reutilizables (sin reinventar)

Patrones ya en el proyecto o plantilla que aplican a comandas:

| Necesidad | Referencia existente | Uso sugerido |
|-----------|---------------------|--------------|
| KPI cards | `WaiterKpiCard.vue`, `CardStatisticsVertical` (shift-console) | Dashboard garzón/cajera unificado |
| Tabs por estado | `shift-console/index.vue` (`VTabs` + `VDataTable`) | Listado admin/cajera |
| Chips de estado | `orderStatusColor/Label`, chips en cards | Ya usado; estandarizar en todos los listados |
| Lista pedidos ecommerce | `pages/apps/ecommerce/order/list` | Widgets por estado + tabla filtrable + búsqueda |
| CRM analytics cards | `views/dashboards/crm`, `analytics` | Resumen turno garzón (total vendido pendiente — cuando exista API) |
| Menú acciones fila | `OrderItemsTable` (Fase A) | Mantener patrón `VMenu` compacto |
| Cards clicables | `orders/index.vue`, `WaiterOrderCard` | Unificar estilo `VCard` + chip estado |

**No hace falta** nuevo design system; hace falta **reutilizar VTabs + KPI + VDataTable** como ya hace consola de turno.

---

## 10. Propuesta de corrección (sin implementar)

### 10.1 Principios de diseño acordados

1. **Un vocabulario operativo** para el usuario: *Abierta*, *En barra*, *Pendiente de cobro*, *Cobrada* — no exponer `IN_PREPARATION`/`READY` hasta que barra los alimente.
2. **Un listado admin** que muestre **todas las comandas activas** de la sucursal (o del turno), no solo OPEN.
3. **KPIs mutuamente excluyentes** (sin doble conteo).
4. **Mismo filtro de turno** opcional y visible en cajera/admin (hoy solo garzón lo aplica).
5. **Una entrada de detalle** o comportamiento parity entre waiter/shared.

### 10.2 Modelo de listado propuesto — Garzón

| Pestaña / sección | Estados API | Contenido |
|-------------------|-------------|-----------|
| **Activas / Gestionar** | `OPEN` + `SENT_TO_BAR` | Edición y extras |
| **Pendiente cobro** | `SENT_TO_BAR`, `READY`* | Solo si barra implementada; hoy ≈ `SENT_TO_BAR` excluyendo OPEN |
| **Finalizadas** | `BILLED` (turno actual) | Historial reciente |

\*Cuando exista integración barra.

**Dashboard garzón:** Abiertas (OPEN) | En barra (SENT_TO_BAR) | Por cobrar (READY+IN_PREPARATION o subconjunto sin SENT_TO_BAR) | Total turno (futuro).

### 10.3 Modelo de listado propuesto — Cajera

| Sección (orden) | Scope backend |
|-----------------|---------------|
| 1. Pendientes de cobro | Nuevo scope o `pending_charge` sin OPEN |
| 2. Abiertas / en barra editable | `OPEN` + `SENT_TO_BAR` |
| 3. Historial reciente cobradas | `BILLED` + límite temporal |

Implementación UI: `VTabs` como shift-console o secciones en una página.

### 10.4 Modelo de listado propuesto — Admin

Reemplazar `fetchOrders('OPEN')` por:

- Pestañas alineadas a consola de turno, **o**
- Redirigir «Listado» a vista con `scope=cashier_chargeable` + pestaña BILLED, **o**
- Fusionar «Listado» y «Cobrar comandas» en un solo módulo «Comandas» con tabs.

### 10.5 Backend (scopes sugeridos a futuro)

| Scope nuevo / ajustado | Estados | Uso |
|------------------------|---------|-----|
| `operational_active` | OPEN, SENT_TO_BAR | Admin listado principal |
| `pending_charge_strict` | READY, IN_PREPARATION (futuro) o READY+IN_PREPARATION sin SENT_TO_BAR | KPI sin solape |
| `cashier_chargeable` | Mantener o renombrar | Cajera |
| `billed_recent` | BILLED + turno/fecha | Historial |

Ajustar `pending_charge` actual para **no incluir** `SENT_TO_BAR` si «En barra» es categoría separada.

---

## 11. Prioridad de corrección

| Prioridad | ID | Acción | Esfuerzo |
|-----------|-----|--------|----------|
| **P0 — Urgente** | O-01, O-02, O-11 | Corregir listado `/nightpos/orders` y enlaces consola; mensajes alineados al filtro real | Bajo |
| **P1 — Alto** | O-04, O-06 | Redefinir `pending_charge` sin solape; ocultar o documentar IN_PREPARATION/READY | Medio |
| **P1 — Alto** | O-05, O-07 | Alinear filtro de turno (UI toggle o default `current_shift=1`) | Medio |
| **P2 — Medio** | O-08, O-09 | Tabs cajera y garzón según modelo operativo | Medio |
| **P2 — Medio** | O-03 | Unificar o documentar dual detalle waiter/shared | Alto |
| **P3 — Bajo** | O-10, O-12 | Menú navegación y copy KPIs | Bajo |

### Qué corregir primero

1. **Listado admin + mensaje vacío + links consola** (P0) — elimina el bug reportado sin tocar flujo garzón/cajera.
2. **Definición única de KPIs** (P1) — evita que operación desconfíe de los números.
3. **Tabs cajera/admin** (P2) — mejora operación en pico.
4. **Estados barra IN_PREPARATION/READY** (P2 backend) — solo cuando exista flujo barra; hasta entonces simplificar UI.

---

## 12. Fases futuras que dependen de esta auditoría

| Fase | Dependencia |
|------|-------------|
| **Fase B — Venta directa caja** | Requiere listados/KPIs de venta claros; no bloqueada, pero conviene P0 resuelto para no sumar confusión |
| **Integración barra / cocina** | Necesita estados `IN_PREPARATION`/`READY` con transiciones reales y scopes sin solape |
| **Reportes operativos** | Requieren definición única de «comanda activa» vs «pendiente cobro» vs «cobrada» |
| **Notificaciones garzón «lista para cobro»** | Depende de estado READY real y contadores consistentes |
| **Refactor detalle único** | Depende de decisión O-03 (waiter vs shared) |

---

## 13. Validación manual recomendada (post-auditoría, pre-fix)

1. Crear comanda → enviar barra → **no cobrar**.
2. Abrir `/nightpos/orders` como admin → confirmar mensaje vacío.
3. Abrir `/nightpos/cashier/orders` como cajera → confirmar que aparece.
4. Comparar KPIs garzón vs consola vs dashboard admin para la misma comanda.
5. Cobrar → verificar que desaparece de `cashier_chargeable` y aparece en ventas / pestaña cobradas consola.

---

## 14. Conclusión

El módulo de comandas **no está roto en backend** para el flujo principal, pero **sí está fragmentado en frontend y semántica de scopes**. El problema *«hay comanda pero no hay comandas abiertas»* no es un fallo de persistencia: es un **filtro `status=OPEN` en la vista equivocada** mientras la comanda vive en `SENT_TO_BAR`.

**No implementar nuevas fases** (venta directa, reportes, barra) hasta cerrar **P0 + P1**, porque seguirían mostrando datos inconsistentes y erosionarían la confianza operativa.

---

*Documento de auditoría únicamente. Sin cambios de código.*
