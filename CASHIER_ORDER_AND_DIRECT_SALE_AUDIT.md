# Auditoría — Corrección de comandas por cajera + Venta directa de caja

**Proyecto:** NightPOS SaaS  
**Fecha:** 2026-06-02  
**Alcance:** Análisis de código y documentación existente. **Sin implementación.**  
**Referencias obligatorias revisadas:**

| Documento | Ubicación | Relevancia |
|-----------|-----------|------------|
| `DOMAIN_DESIGN.md` | raíz | Agregados Order/Sale, invariantes de snapshot y comisiones |
| `FRONTEND_GUIDELINES.md` | raíz | Regla backend-first, pantallas POS/caja |
| `NIGHTPOS_OPERATION_FLOW_AUDIT.md` | raíz | Flujo cajera, huecos operativos, riesgo comisión garzón |
| `NIGHTPOS_MASTER_AUDIT.md` | raíz | Panorama general del sistema |
| `PHASE_7_REPORT.md` | `backend/` | Comandas: endpoints base, estados, sin edición de líneas |
| `PHASE_9_REPORT.md` | *(no presente en repo; cobro documentado en fases posteriores)* | Ventas/cobro inferido de `ChargeOrderUseCase` y migración `sales` |
| `PHASE_C1_REPORT.md` | `backend/` | Garzón obligatorio al crear comanda (cajera debe elegir garzón) |
| `OPERATIONAL_ROLE_FLOW_FIX_REPORT.md` | `backend/` + `frontend/` | Vista `cashier/orders`, scope `cashier_chargeable`, fix garzón/chica |
| `SERVICE_CASH_SESSION_RESOLUTION_FIX_REPORT.md` | `backend/` | Resolver único de caja (`OpenCashSessionResolver`) |
| `ADMIN_CASH_SESSIONS_REPORT.md` | `backend/` | Modelo caja por usuario, fiscalización vs caja operativa |
| `CASH_OPERATIONS.md` | raíz | Flujo caja, cobros asociados a sesión del usuario |

---

## Resumen ejecutivo

Tras el fix operativo reciente, la cajera tiene una vista clara para **listar y cobrar** comandas (`/nightpos/cashier/orders`), pero la UI expone casi exclusivamente el CTA **Cobrar**. En operación real de boliche, la cajera debe poder **corregir la comanda antes del cobro** cuando el garzón se equivoca.

En backend existen capacidades parciales (agregar ítem, asignar chica, cancelar comanda completa, cobrar), pero **no hay edición ni baja de líneas**, ni cambio de cantidad/modalidad/mesa, ni cancelación de línea con motivo. El frontend de detalle compartido (`/nightpos/orders/:id`) oculta varias acciones que el API sí permitiría a la cajera.

Para **venta directa sin comanda**, el esquema de base de datos **ya contempla** `sales.order_id` nullable y `sale_items.order_item_id` nullable, pero la capa de aplicación (`SaleRepositoryInterface::create`, `ChargeOrderUseCase`) **obliga** comanda y no existe `POST /direct-sales`.

**Recomendación de orden:** implementar **primero Fase A** (corrección de comandas), luego **Fase B** (venta directa). Fase A cierra un hueco operativo inmediato con menor superficie de cambio; Fase B requiere un use case nuevo y ajustes de dominio/repositorio aunque el DDL ya esté preparado.

---

## 1. Cómo está hoy el flujo de cajera para comandas

### 1.1 Navegación y pantallas

```
Menú Operación → Cobrar comandas
  → GET /api/v1/orders?scope=cashier_chargeable
  → /nightpos/cashier/orders

Por cada comanda:
  - Caja cerrada → «Abrir caja ahora» → /nightpos/cash?open=1
  - Caja abierta → «Cobrar» → /nightpos/orders/:id?charge=1
```

**Archivos clave:**

| Capa | Archivo |
|------|---------|
| Lista cajera | `frontend/src/pages/nightpos/cashier/orders/index.vue` |
| Detalle compartido | `frontend/src/pages/nightpos/orders/[id].vue` |
| API listado | `backend/app/Application/Order/UseCases/ListOrdersUseCase.php` (`scope=cashier_chargeable`) |

### 1.2 Scope `cashier_chargeable`

Incluye comandas en estados: `OPEN`, `SENT_TO_BAR`, `IN_PREPARATION`, `READY` — todas las pendientes de cobro en la sucursal (sin filtro por garzón).

La cajera ve: mesa/etiqueta, número, garzón, hora, estado, cantidad de ítems, total.

### 1.3 Detalle al cobrar

Al entrar con `?charge=1`, si hay caja abierta y la comanda es cobrable, se abre automáticamente `ChargeOrderModal`.

El detalle reutiliza la pantalla de comandas general (`orders/[id].vue`), no una vista específica de cajera. El botón «Volver» apunta a `nightpos-orders` (lista admin/garzón), no a `nightpos-cashier-orders`.

### 1.4 Acceso backend

`WaiterOrderAccessPolicy` restringe comandas **solo** cuando `staff_role === 'WAITER'`. La cajera accede a **cualquier** comanda de la sucursal (correcto para corrección y cobro).

### 1.5 Caja y cobro

- Caja: `GET /cash/session/current` vía `OpenCashSessionResolver` (sesión del usuario autenticado).
- Cobro: `POST /orders/{id}/charge` → `ChargeOrderUseCase` crea `sales`, `sale_items`, `sale_payments`, movimientos de caja, marca comanda `BILLED`.

Documentado en `OPERATIONAL_ROLE_FLOW_FIX_REPORT.md` y `SERVICE_CASH_SESSION_RESOLUTION_FIX_REPORT.md`.

---

## 2. Qué acciones puede hacer hoy la cajera

### 2.1 En la lista `/cashier/orders`

| Acción | Disponible |
|--------|------------|
| Ver listado de comandas cobrables | Sí |
| Ver estado de caja propia | Sí |
| Abrir caja (redirección) | Sí |
| Cobrar (único CTA por card) | Sí |
| Ver / corregir sin cobrar | **No** (no hay botón) |
| Agregar / editar desde lista | **No** |

### 2.2 En detalle `/orders/:id` (si la cajera llega por Cobrar o URL directa)

Condición UI: `modifiable = status ∈ {OPEN, SENT_TO_BAR}` (`canModifyOrder`).

| Acción | Backend | UI actual (cajera) | Notas |
|--------|---------|-------------------|-------|
| Ver ítems y totales | Sí | Sí | `OrderItemsTable` solo lectura |
| Agregar producto | Sí (`POST .../items`) si `allowsItemChanges()` | Solo si `status === OPEN` (`showAdd=isOpen`) | **Desalineado:** API permite también en `SENT_TO_BAR` |
| Asignar/cambiar chica en línea | Sí (`PATCH .../items/{id}`) | Solo vía flujo «Enviar a barra» (`AssignGirlModal`) | No hay acción por línea en tabla |
| Enviar a barra | Sí | Sí si `OPEN` | Cajera tiene permiso; operación típica del garzón |
| Cancelar comanda completa | Sí (`POST .../cancel`) | Sí si no `BILLED`/`CANCELLED` | Sin motivo obligatorio; permitido incluso en `SENT_TO_BAR` |
| Cobrar | Sí (`POST .../charge`) | Sí (modal) | Requiere caja abierta + chica en ítems `CON_ACOMPANANTE` |
| Cambiar cantidad | **No** | **No** | — |
| Cambiar modalidad (SOLO / CON_ACOMPANANTE) | **No** | **No** | — |
| Quitar línea / cancelar línea | **No** | **No** | — |
| Corregir mesa / ambiente | **No** | **No** | Solo al crear comanda (`POST /orders`) |
| Editar en `IN_PREPARATION` / `READY` | Backend bloquea ítems | Barra de acciones oculta (`!modifiable`) | Solo cobro vía modal si entra con `?charge=1` |

### 2.3 Permisos RBAC cajera (`TenantDefaultRolePermissions::cashier()`)

Incluye: `orders.access`, `orders.create`, `orders.add_items`, `orders.send_to_bar`, `sales.charge`, `sales.list`, `products.list`, servicios, liquidaciones, etc.

**No existen** permisos granulares tipo `orders.update_items`, `orders.cancel_item`, `sales.direct_create`.

---

## 3. Qué acciones faltan (vs regla de negocio solicitada)

### 3.1 Estado OPEN — cajera debería poder

| Acción requerida | Estado actual |
|------------------|---------------|
| Agregar ítems | Backend sí; UI cajera parcial (solo en detalle, no desde lista; botón oculto si no es cajera quien navega solo a cobrar) |
| Editar cantidad | **Falta** |
| Quitar ítems | **Falta** |
| Cambiar chica | Backend sí (PATCH); UI sin control por línea |
| Cambiar modalidad | **Falta** |
| Cancelar comanda | Backend sí; sin motivo ni auditoría de línea |
| Corregir mesa/ambiente | **Falta** |
| Cobrar | Implementado |

### 3.2 Estado SENT_TO_BAR — política recomendada

| Acción | Backend hoy | Debería |
|--------|-------------|---------|
| Agregar extra | Sí (`allowsItemChanges`) | Sí |
| Corregir chica | Sí (PATCH) | Sí |
| Cancelar línea con motivo | **No** | Sí (solo con motivo si `item_status = SENT`) |
| Eliminar ítems enviados sin motivo | **No** (no hay delete) | No |
| Cobrar | Sí | Sí |

**Brecha de dominio:** `order_items.item_status` pasa a `SENT` al enviar a barra, pero no hay use case que consulte este flag para políticas de baja.

### 3.3 Estados IN_PREPARATION / READY

- Backend: `allowsItemChanges()` = false → no agregar/editar ítems.
- UI: sin barra de modificación; cajera solo puede cobrar.
- Alineado con la regla «antes de cobrar» si se interpreta que correcciones de ítems terminan en `SENT_TO_BAR`; en pico operativo puede ser restrictivo.

### 3.4 BILLED / PAID

- Backend y UI: solo lectura; cobro bloqueado.
- Anulación con permisos especiales: **fuera de alcance** (no implementada).

---

## 4. Qué permite backend y qué bloquea frontend

### 4.1 Matriz backend

| Operación | Endpoint | Permiso | Estados permitidos | Observación |
|-----------|----------|---------|-------------------|-------------|
| Listar cobrables | `GET /orders?scope=cashier_chargeable` | `orders.access` | OPEN…READY | OK |
| Agregar ítem | `POST /orders/{id}/items` | `orders.add_items` | OPEN, SENT_TO_BAR | Precio resuelto en servidor |
| Asignar chica | `PATCH /orders/{id}/items/{itemId}` | `orders.add_items` | OPEN, SENT_TO_BAR | Solo `CON_ACOMPANANTE` |
| Enviar barra | `POST /orders/{id}/send-to-bar` | `orders.send_to_bar` | OPEN | Exige chica en ítems acompañante |
| Cancelar comanda | `POST /orders/{id}/cancel` | `orders.access` | ≠ BILLED, CANCELLED | Sin motivo; sin `WaiterOrderAccessPolicy` |
| Cobrar | `POST /orders/{id}/charge` | `sales.charge` | ≠ BILLED/CANCELLED | Caja abierta + turno operativo |
| Actualizar ítem | — | — | — | **No existe** |
| Eliminar ítem | — | — | — | **No existe** |
| Cancelar línea | — | — | — | **No existe** |
| Actualizar mesa/ambiente | — | — | — | **No existe** |
| Venta directa | — | — | — | **No existe** |

**Dominio:** `OrderStatus::allowsItemChanges()` → `[OPEN, SENT_TO_BAR]`.  
**Dominio:** `OrderStatus::canCancel()` → todo excepto `BILLED`, `CANCELLED`.

### 4.2 Matriz frontend

| Bloqueo / limitación | Causa |
|----------------------|-------|
| Lista cajera solo «Cobrar» | `cashier/orders/index.vue` — un solo CTA |
| Agregar producto oculto en `SENT_TO_BAR` | `[id].vue`: `:show-add="isOpen"` (solo OPEN) |
| Ítems sin acciones por fila | `OrderItemsTable.vue` — lista de solo lectura |
| Cambiar chica no accesible por línea | `AssignGirlModal` ligado a envío a barra |
| Volver a lista incorrecta | Link a `nightpos-orders`, no `nightpos-cashier-orders` |
| Sin pantalla venta directa | Ruta y menú inexistentes |

### 4.3 Conclusión desalineación

| Capacidad | Backend | Frontend cajera |
|-----------|---------|-----------------|
| Agregar en SENT_TO_BAR | Permite | Bloquea botón |
| Asignar chica | Permite | Flujo indirecto |
| Cancelar comanda en barra | Permite sin motivo | Permite (riesgo operativo) |
| Editar cantidad / quitar línea | No permite | No muestra (coherente) |

La cajera **no está limitada por RBAC** en la mayoría de correcciones posibles hoy; está limitada por **API incompleta** y **UI orientada solo a cobro**.

---

## 5. Qué tablas soportan venta sin `order_id`

Migración `2026_06_03_100009_create_sales_tables.php`:

| Tabla | Campo | Nullable | Significado |
|-------|-------|----------|-------------|
| `sales` | `order_id` | **Sí** | Venta sin comanda permitida a nivel DDL |
| `sales` | `waiter_user_id` | **Sí** | Sin garzón / sin comisión garzón |
| `sale_items` | `order_item_id` | **Sí** | Ítems no originados en comanda |
| `sales` | `unique(order_id)` | — | Una venta por comanda cuando hay `order_id`; múltiples filas con `order_id` NULL permitidas (MySQL) |

También: `official_shift_id` en `sales` (migración `2026_06_03_100013`).

**Tablas relacionadas que venta directa debe poblar:**

- `sales` — cabecera
- `sale_items` — líneas con snapshots de precio, modalidad, chica/casa, comisión garzón (null si sin garzón)
- `sale_payments` — desglose de pago
- `cash_movements` — ingreso por método (patrón de `ChargeOrderUseCase`)
- Opcional futuro: `audit_logs` (`sale.charged` / `sale.direct_created`)

**No usa `orders` / `order_items`** en venta directa.

**Referencia paralela:** servicios (manilla, pieza, show) registran ingreso vía `ServiceIncomeCashRecorder` → `cash_movements`, pero **no** crean filas en `sales`. La venta directa de productos de catálogo debe ir por el agregado `Sale`, no por el circuito de servicios.

---

## 6. ¿`sales.order_id` es obligatorio hoy?

| Capa | ¿Obligatorio? |
|------|---------------|
| Base de datos | **No** — columna nullable |
| `SaleRepositoryInterface::create()` | **Sí** — parámetro `int $orderId` (no nullable) |
| `EloquentSaleRepository::create()` | **Sí** — persiste siempre `order_id` |
| `ChargeOrderUseCase` | **Sí** — único flujo de creación de ventas; siempre desde comanda |
| Tests / API pública | **Sí** de facto — solo `POST /orders/{id}/charge` |

**Conclusión:** el modelo de datos **anticipa** ventas sin comanda; la **aplicación actual obliga** comanda en todo el flujo de venta.

---

## 7. Cambios backend necesarios

### 7.1 Fase A — Corrección de comanda por cajera

#### Nuevos endpoints (propuesta)

| Método | Ruta | Descripción |
|--------|------|-------------|
| `PUT` | `/api/v1/orders/{id}/items/{item_id}` | Actualizar cantidad, `sale_mode`, `girl_user_id`; recalcular precios vía `ProductPriceResolver` |
| `DELETE` | `/api/v1/orders/{id}/items/{item_id}` | Quitar línea en `OPEN` (sin motivo) |
| `POST` | `/api/v1/orders/{id}/items/{item_id}/cancel` | Cancelar línea en `SENT_TO_BAR` (o si `item_status = SENT`) con `reason` obligatorio |
| `PATCH` | `/api/v1/orders/{id}` | Actualizar `table_label`, `service_area_id` (opcional Fase A.1) |

`POST /orders/{id}/items` ya existe; mantener.

#### Use cases sugeridos

- `UpdateOrderItemUseCase` — validar estado, modalidad, chica si `CON_ACOMPANANTE`, recalcular totales de orden.
- `RemoveOrderItemUseCase` — solo `OPEN` y ítems `PENDING`.
- `CancelOrderItemUseCase` — `SENT_TO_BAR` + motivo; marcar ítem cancelado (nuevo estado o flag), no borrar físico; auditoría.
- `UpdateOrderHeaderUseCase` — mesa/ambiente antes de cobro.

#### Reglas de dominio a formalizar

```text
OPEN:
  - add / update qty / remove line / change mode / change girl / cancel order

SENT_TO_BAR:
  - add extra (nuevos ítems PENDING)
  - update girl on line
  - cancel line with reason if item_status = SENT
  - no hard-delete sent lines

IN_PREPARATION / READY:
  - sin cambios de ítems (mantener o relajar en fase posterior)

BILLED / CANCELLED:
  - rechazar todo cambio
```

#### Repositorio

Extender `OrderRepositoryInterface`:

- `updateItem(...)`, `removeItem(...)`, `cancelItem(..., reason)`, `recalculateTotals(...)`, `updateHeader(...)`.

#### Permisos

| Permiso propuesto | Uso |
|-------------------|-----|
| `orders.add_items` | Mantener para POST ítems y PATCH chica |
| `orders.update_items` | PUT ítems, PATCH cabecera |
| `orders.cancel_item` | POST cancel línea |
| `orders.cancel` | Cancelar comanda (separar de `orders.access`) |

Cajera: asignar `orders.update_items`, `orders.cancel_item` en `TenantDefaultRolePermissions::cashier()`.

#### Auditoría

Registrar en bitácora: `order.item_updated`, `order.item_removed`, `order.item_cancelled`, `order.header_updated` con `reason` cuando aplique.

#### Tests

Feature tests: cajera corrige comanda ajena; garzón no corrige comanda ajena; línea SENT exige motivo; BILLED rechaza cambios.

---

### 7.2 Fase B — Venta directa de caja

#### Nuevo endpoint

```
POST /api/v1/direct-sales
Permiso: sales.direct_create (o reutilizar sales.charge con flag)
```

#### Payload (según especificación)

```json
{
  "items": [
    {
      "product_id": 1,
      "sale_mode": "SOLO_CLIENTE",
      "quantity": 1,
      "girl_user_id": null
    }
  ],
  "payments": [
    { "method": "CASH", "amount": 20 }
  ],
  "notes": "Venta directa caja"
}
```

#### Use case `CreateDirectSaleUseCase`

Transacción única:

1. `EnsureOperationalShiftUseCase`
2. `OpenCashSessionResolver` — caja abierta obligatoria
3. Resolver precios por ítem (`ProductPriceResolver`)
4. Validar `girl_user_id` si `CON_ACOMPANANTE`
5. `SaleRepository::createDirect(...)` con `orderId: null`, `waiterUserId: null`
6. Crear `sale_payments`
7. `cashSessions->addMovement` por cada pago (`description`: venta directa + `sale_number`)
8. `audit->record('sale.direct_created', ...)`

#### Cambios en repositorio / dominio

```php
// SaleRepositoryInterface — nuevo método o orderId nullable
public function create(
    ...
    ?int $orderId,
    ...
): Sale;
```

- `existsForOrder` sin cambios.
- Snapshots de `sale_items` iguales a cobro de comanda (`girl_amount`, `house_amount`, comisión garzón = 0 o null).

#### Liquidaciones

Ítems `CON_ACOMPANANTE` con chica deben alimentar fuentes de liquidación de chicas (mismo camino que `sale_items` de comanda). Verificar `GenerateSettlementUseCase` / pending sources.

#### Lo que no requiere migración

DDL ya soporta `order_id` y `order_item_id` null. Posible columna `notes` en `sales` si se desea persistir — **evaluar** si hace falta migración menor.

---

## 8. Cambios frontend necesarios

### 8.1 Fase A — Corrección de comanda por cajera

#### Lista `/nightpos/cashier/orders`

| Elemento | Cambio |
|----------|--------|
| CTA secundario | **Ver / corregir** → `/nightpos/orders/:id` (sin `charge=1`) |
| CTA primario | Mantener **Cobrar** |
| Navegación retorno | En detalle desde cajera, volver a `nightpos-cashier-orders` |

#### Detalle (vista cajera o modo `?mode=cashier`)

| Botón / acción | Condición UI |
|----------------|--------------|
| Agregar producto | `canModifyOrder` (OPEN + SENT_TO_BAR) — **no** solo OPEN |
| Cambiar cantidad | Por línea, OPEN; restricción en SENT |
| Cambiar chica | Por línea en `CON_ACOMPANANTE` |
| Cambiar modalidad | Por línea en OPEN |
| Quitar línea | OPEN, ítem pendiente |
| Cancelar línea | SENT_TO_BAR + diálogo motivo |
| Cancelar comanda | Con confirmación; motivo si ya enviada a barra |
| Cobrar | Sin cambios |

#### Componentes

- Evolucionar `OrderItemsTable.vue` → acciones por fila (o `OrderItemRowActions.vue`).
- Reutilizar `OrderAddProductDialog`, `AssignGirlModal` fuera del flujo send-to-bar.
- Ajustar `OrderActionsBar.vue` o crear `CashierOrderActionsBar.vue`.
- API client: `updateOrderItem`, `removeOrderItem`, `cancelOrderItem`, `updateOrder`.

#### Permisos UI

Usar `useNightPosPermissions` con nuevos flags (`orders.update_items`, etc.).

#### UX (FRONTEND_GUIDELINES)

- Botones táctiles grandes en caja.
- Confirmaciones en bajas y cancelaciones.
- Mensajes de error del backend visibles (motivo obligatorio).

---

### 8.2 Fase B — Venta directa de caja

| Elemento | Propuesta |
|----------|-----------|
| Ruta | `/nightpos/cash/direct-sale` |
| Menú | Caja → Venta directa |
| Permiso página | `sales.direct_create` o `sales.charge` |
| Layout | POS rápido: buscador, favoritos (`useOrderProductShortcuts`), cantidad, carrito, total, método pago, cobrar |
| Caja cerrada | Mismo patrón que `cashier/orders` — bloquear cobro, CTA abrir caja |
| API | `POST /direct-sales` |
| Post-cobro | Snackbar + opción imprimir ticket / nueva venta |

**No** crear comanda intermedia ni pedir garzón.

---

## 9. Riesgos contables y operativos

| ID | Riesgo | Severidad | Mitigación propuesta |
|----|--------|-----------|----------------------|
| R-01 | Cancelar comanda en `SENT_TO_BAR` sin motivo (hoy posible) | Alta | Motivo obligatorio + auditoría; coordinar con barra |
| R-02 | Eliminar ítem ya impreso en barra sin trazabilidad | Alta | Soft-cancel con motivo; no DELETE físico en SENT |
| R-03 | Cambio de cantidad/modalidad altera total sin reimpresión | Media | Evento auditoría; opcional cola print «comanda modificada» |
| R-04 | Comisión garzón incorrecta si cajera crea comanda con garzón mal elegido | Alta (ya documentado en `NIGHTPOS_OPERATION_FLOW_AUDIT` B-11) | UI clara al crear; no aplica a venta directa |
| R-05 | Venta directa sin `order_id` invisible en reportes por mesa/garzón | Media | Reportes por `sale_items` y flag `order_id IS NULL` |
| R-06 | Venta directa `CON_ACOMPANANTE` sin liquidar chica | Alta | Mismos snapshots que cobro comanda; tests liquidación |
| R-07 | Doble registro ingreso (sale + movimiento manual) | Media | Descripción y `source_type` en movimientos de caja |
| R-08 | Movimientos de cobro sin `source_type`/`source_id` (hoy en `ChargeOrderUseCase`) | Media | Unificar con `ServiceIncomeCashRecorder` pattern |
| R-09 | Arqueo de caja: ventas directas deben sumar en sesión | Alta | Mismo `cash_session_id` que cobros |
| R-10 | Anulación post-factura | — | Fuera de alcance; no implementar ahora |

**Inventario / kardex:** el cobro actual no descuenta stock automáticamente; venta directa hereda el mismo comportamiento (sin riesgo adicional inmediato).

---

## 10. Propuesta de implementación por fases

### FASE A — Corrección de comanda por cajera

**Objetivo:** La cajera corrige errores del garzón antes de cobrar, con políticas por estado.

#### Backend

| Entrega | Detalle |
|---------|---------|
| A.1 | `PUT /orders/{id}/items/{item_id}` — cantidad, modalidad, chica |
| A.2 | `DELETE /orders/{id}/items/{item_id}` — solo OPEN + PENDING |
| A.3 | `POST /orders/{id}/items/{item_id}/cancel` — motivo obligatorio si enviado |
| A.4 | `PATCH /orders/{id}` — mesa/ambiente (opcional) |
| A.5 | Permisos + auditoría + tests Feature |

#### Frontend

| Entrega | Detalle |
|---------|---------|
| A.F1 | `cashier/orders`: botones Ver/corregir + Cobrar |
| A.F2 | Detalle modo cajera: barra de acciones alineada a `canModifyOrder` |
| A.F3 | `OrderItemsTable` con acciones por línea |
| A.F4 | Diálogos cantidad, modalidad, motivo cancelación |
| A.F5 | Navegación retorno a lista cajera |

**Esfuerzo estimado:** medio. Reutiliza gran parte del stack de comandas (Fase 7 + C4 + fix operativo).

---

### FASE B — Venta directa de caja

**Objetivo:** POS rápido en caja para productos sin comanda ni garzón.

#### Backend

| Entrega | Detalle |
|---------|---------|
| B.1 | `SaleRepository` con `orderId` nullable |
| B.2 | `CreateDirectSaleUseCase` transaccional |
| B.3 | `POST /api/v1/direct-sales` + permiso `sales.direct_create` |
| B.4 | Movimientos caja + auditoría |
| B.5 | Tests: caja cerrada falla; snapshots; liquidación chica |

#### Frontend

| Entrega | Detalle |
|---------|---------|
| B.F1 | Ruta `/nightpos/cash/direct-sale` |
| B.F2 | Menú Caja → Venta directa |
| B.F3 | Pantalla POS (reutilizar componentes de `OrderAddProductDialog` / shortcuts) |
| B.F4 | Integración caja abierta + cobro |

**Esfuerzo estimado:** medio-alto. Nuevo flujo de venta aunque el DDL esté listo.

---

## Validación — ¿Qué implementar primero?

| Criterio | Fase A (corrección) | Fase B (venta directa) |
|----------|---------------------|-------------------------|
| Urgencia operativa | **Alta** — errores de garzón bloquean cobro o generan reclamos | Media — workaround actual: crear comanda mínima + cobrar |
| API existente | ~40% (add, patch chica, cancel order, charge) | ~10% (solo esquema DB) |
| Riesgo contable | Medio — controlable con motivos y soft-cancel | Medio — nuevo canal de ingreso |
| Dependencias | Independiente | Beneficia de resolver movimientos caja con `source_*` (puede hacerse en A o B) |
| UX cajera | Cierra brecha reportada post-fix (solo Cobrar) | Amplía capacidad POS |

### Recomendación final

1. **Implementar primero Fase A** — corrige el problema más visible (cajera solo cobra), aprovecha permisos y endpoints parciales, y reduce fricción en el flujo principal comanda → cobro.
2. **Implementar después Fase B** — venta directa es valiosa pero tiene workaround operativo; requiere nuevo use case y pantalla POS dedicada.
3. **No paralelizar** ambas en el mismo sprint si el equipo es pequeño: comparten touch en `orders/[id].vue`, permisos cajera y movimientos de caja; secuencia A → B reduce conflictos.

**Ambas son necesarias** para paridad con operación real de boliche; la priorización es por impacto inmediato y costo de integración.

---

## Anexo — Mapa de archivos para implementación futura

| Área | Archivos actuales |
|------|-------------------|
| Lista cajera | `frontend/src/pages/nightpos/cashier/orders/index.vue` |
| Detalle comanda | `frontend/src/pages/nightpos/orders/[id].vue` |
| Tabla ítems | `frontend/src/components/nightpos/orders/OrderItemsTable.vue` |
| Acciones | `frontend/src/components/nightpos/orders/OrderActionsBar.vue` |
| Rutas API | `backend/routes/api.php` |
| Controlador | `backend/app/Http/Controllers/Api/V1/OrderController.php` |
| Estados | `backend/app/Domain/Order/ValueObjects/OrderStatus.php` |
| Cobro | `backend/app/Application/Sale/UseCases/ChargeOrderUseCase.php` |
| Ventas repo | `backend/app/Infrastructure/Persistence/Eloquent/Repositories/EloquentSaleRepository.php` |
| Permisos cajera | `backend/app/Application/Tenant/Support/TenantDefaultRolePermissions.php` |
| DDL ventas | `backend/database/migrations/2026_06_03_100009_create_sales_tables.php` |

---

*Documento de auditoría únicamente. No se modificó código de aplicación.*
