# DOMAIN_DESIGN.md

**Proyecto:** NIGHTPOS SaaS  
**Fase:** 2 — Diseño del dominio  
**Fecha:** 2026-06-02  
**Referencias:** `CURRENT_SYSTEM_AUDIT.md`, `SAAS_ARCHITECTURE.md`, `DATABASE_GUIDELINES.md`, `BOLICHE_RULES.md`, `DEVELOPMENT_RULES.md`  
**Restricción:** Documento de diseño únicamente. Sin código, migraciones, controladores ni frontend.

---

## 1. Propósito y principios

Este documento define el **modelo de dominio** del nuevo NightPOS SaaS: bounded contexts, entidades, agregados, value objects, eventos y reglas invariantes. La implementación futura seguirá:

| Principio | Aplicación |
| --------- | ---------- |
| **DDD** | Lenguaje ubicuo, bounded contexts, agregados con invariantes |
| **Arquitectura hexagonal** | Dominio en el centro; puertos hacia afuera |
| **Clean Architecture** | Dependencias hacia el dominio; capas concéntricas |
| **SOLID** | Casos de uso pequeños; interfaces de repositorio por contexto |
| **Multi-tenant SaaS** | `TenantId` y `BranchId` como value objects transversales en todo agregado operativo |

**Estrategia de datos:** Single Database Multi-Tenant (`tenant_id` en tablas de negocio).

---

## 2. Mapa de contextos (Context Map)

```text
                    ┌─────────────────┐
                    │  Platform SaaS  │
                    │ Tenant / Plan   │
                    └────────┬────────┘
                             │ OHS (Open Host Service)
         ┌───────────────────┼───────────────────┐
         ▼                   ▼                   ▼
   ┌──────────┐       ┌──────────┐       ┌──────────┐
   │   Auth   │       │  Branch  │       │  Shift   │
   └────┬─────┘       └────┬─────┘       └────┬─────┘
        │                  │                  │
        └────────┬─────────┴────────┬─────────┘
                 ▼                  ▼
          ┌────────────┐     ┌─────────────┐
          │ User/Staff │     │  Products   │
          └─────┬──────┘     └──────┬──────┘
                │                   │
    ┌───────────┼───────────────────┼───────────────┐
    ▼           ▼                   ▼               ▼
┌────────┐ ┌──────────┐      ┌──────────┐    ┌────────────┐
│ Orders │→│  Sales   │←────│   Cash   │    │ Inventory  │
└───┬────┘ └────┬─────┘      └────┬─────┘    └────────────┘
    │           │                 │
    ▼           ├─────────────────┤
┌──────────┐    ▼                 ▼
│ Printing │ ┌──────────────────────────────┐
└──────────┘ │     Staff Settlement         │
             │  ┌────────────┬─────────────┐ │
             │  │Girl Comm.  │Waiter Comm. │ │
             │  └────────────┴─────────────┘ │
             └──────────────┬───────────────┘
                            ▼
                     ┌──────────┐
                     │ Reports  │  (ACL / Read models)
                     └──────────┘
```

| Relación | Contextos | Patrón |
| -------- | --------- | ------ |
| Orders → Sales | Comanda cobrada genera venta | **Customer-Supplier** (Sales define contrato de cobro) |
| Sales → Girl/Waiter Commissions | Al pagar venta | **Published Language** (eventos) |
| Sales → Cash | Pagos y movimientos | **Conformist** hacia políticas de caja |
| Orders → Printing | Tras persistir comanda | **Event-driven** |
| Reports → todos | Solo lectura | **Anti-Corruption Layer** + proyecciones |
| Platform → operativos | Suscripción activa | **Shared Kernel** (`TenantId`, `BranchId`) |

---

## 3. Bounded contexts

### 3.1 Tenant (Plataforma SaaS)

**Responsabilidad:** Empresa cliente del SaaS, plan, suscripción, estado de membresía.

| Tipo | Nombre | Descripción |
| ---- | ------ | ----------- |
| Aggregate Root | `Tenant` | Empresa con slug, datos legales, estado |
| Entity | `Subscription` | Vigencia, estado (active/expired/suspended) |
| Entity | `Plan` | Módulos habilitados (catálogo de plataforma) |
| Value Object | `TenantId`, `TenantSlug`, `SubscriptionStatus` | |

**Invariantes:** No operar ventas si suscripción no está activa (validación en Application + Domain Policy).

---

### 3.2 Branch / Sucursal

**Responsabilidad:** Casa/boliche dentro del tenant; configuración operativa local.

| Tipo | Nombre |
| ---- | ------ |
| Aggregate Root | `Branch` |
| Entity | `BranchSettings` (comandas bar/cocina, moneda, timezone, propina) |
| Entity | `Room` (sala/ambiente) |
| Entity | `RoomTable` (mesa/privado) |
| Value Object | `BranchId`, `BranchCode`, `TableStatus` |

**Nota:** `Room`/`RoomTable` pueden modelarse aquí o en sub-módulo **Venue**; se ubican en Branch por cohesión con operación diaria.

---

### 3.3 Auth

**Responsabilidad:** Identidad de acceso, credenciales, tokens (concepto de dominio: sesión autenticada).

| Tipo | Nombre |
| ---- | ------ |
| Entity | `Credential` (email/password hash — persistencia en infra) |
| Entity | `PinCredential` (PIN operativo cajero/garzón) |
| Value Object | `AuthenticatedUserId`, `AccessTokenClaims` |

**Límite:** JWT y hashing son **infraestructura**; el dominio solo expone “usuario autenticado con tenant y branches permitidos”.

---

### 3.4 User / Staff

**Responsabilidad:** Usuario del sistema y perfil operativo (garzón, chica, cajero, encargado).

| Tipo | Nombre |
| ---- | ------ |
| Aggregate Root | `StaffMember` |
| Entity | `User` (identidad compartida con Auth) |
| Entity | `StaffProfile` | `staff_type`, `commission_percent` actual (config) |
| Entity | `UserBranchAssignment` |
| Value Object | `StaffRole`, `StaffMemberId`, `UserId` |

**Regla:** El porcentaje de comisión **vigente** vive en `StaffProfile`; el **aplicado a una venta** vive en Sales (snapshot).

---

### 3.5 Shift / Turnos

**Responsabilidad:** Turnos **oficiales** de negocio (calendario), no la sesión de caja del cajero.

| Tipo | Nombre |
| ---- | ------ |
| Entity | `OfficialShift` | Instancia de turno en sucursal/fecha |
| Value Object | `OfficialShiftType` | `DAY` (09:00–21:00), `NIGHT` (21:00–09:00 día siguiente) |
| Value Object | `ShiftWindow` | Rango horario con timezone de branch |
| Domain Service | `OfficialShiftResolver` | Dado `occurredAt`, devuelve turno oficial activo |

**Turnos oficiales (regla de negocio fija):**

| Tipo | Inicio | Fin |
| ---- | ------ | --- |
| `DAY` | 09:00 | 21:00 (mismo día calendario) |
| `NIGHT` | 21:00 | 09:00 (día siguiente) |

Toda venta, comisión y liquidación referencia `official_shift_id`.

---

### 3.6 Cash Register / Caja

**Responsabilidad:** Caja física/lógica, sesión de cajero, movimientos, cierre y cuadre.

| Tipo | Nombre |
| ---- | ------ |
| Aggregate Root | `CashRegister` | Configuración de caja por branch |
| Aggregate Root | `CashSession` | Apertura/cierre operativo del cajero (ex-arqueo) |
| Entity | `CashMovement` | Ingreso, egreso, pago personal |
| Entity | `ShiftClosure` | Resumen de cierre por método de pago |
| Value Object | `CashSessionId`, `OpeningFloat`, `CashDifference` |

**Distinción crítica:**

- **OfficialShift** = ventana de reporte/liquidación (09–21 / 21–09).
- **CashSession** = turno del cajero con monto inicial, ventas y cierre (puede abrirse dentro de un OfficialShift).

---

### 3.7 Orders / Comandas

**Responsabilidad:** Comanda en mesa o venta pendiente antes del cobro.

| Tipo | Nombre |
| ---- | ------ |
| Aggregate Root | `Order` |
| Entity | `OrderItem` |
| Entity | `OrderItemNote` |
| Entity | `OrderStatusHistory` |
| Value Object | `OrderId`, `OrderNumber`, `OrderStatus`, `DispatchDestination` |

**Estados típicos (`OrderStatus`):** `OPEN`, `SENT_TO_BAR`, `IN_PREPARATION`, `READY`, `BILLED`, `CANCELLED`.

---

### 3.8 Sales / Cobros

**Responsabilidad:** Venta cobrada, pagos, snapshots inmutables, vínculo con comanda y caja.

| Tipo | Nombre |
| ---- | ------ |
| Aggregate Root | `Sale` |
| Entity | `SaleItem` |
| Entity | `Payment` |
| Entity | `SalePaymentBreakdown` |
| Entity | `SaleItemStaffSnapshot` | Precio, modalidad, chica, garzón, comisiones **al momento del cobro** |
| Value Object | `SaleId`, `SaleStatus`, `PaymentMethod` |

**Invariante central:** Al `SalePaid`, comisiones de garzón y montos de chica (manilla) quedan **persistidos en el agregado**, no solo calculados al cierre.

---

### 3.9 Products / Precios

**Responsabilidad:** Catálogo, modalidades de precio boliche, reglas chica/casa.

| Tipo | Nombre |
| ---- | ------ |
| Aggregate Root | `Product` |
| Entity | `Category`, `Unit` |
| Entity | `ProductPrice` | Por branch y `ProductSaleMode` |
| Entity | `ProductPriceRule` | `customer_price`, `girl_amount`, `house_amount` |
| Entity | `Combo`, `ComboItem`, `RecipeLine` |
| Domain Service | `ProductPriceResolver` |
| Value Object | `ProductId`, `ProductSaleMode`, `Sku` |

---

### 3.10 Inventory / Kardex

**Responsabilidad:** Stock, movimientos, compras, traspasos.

| Tipo | Nombre |
| ---- | ------ |
| Aggregate Root | `StockItem` (por producto/ingrediente/combo y branch) |
| Entity | `StockMovement` |
| Aggregate Root | `PurchaseOrder` |
| Aggregate Root | `StockTransfer` |
| Entity | `Supplier` |
| Value Object | `StockMovementType`, `Quantity` |

---

### 3.11 Printing / Impresión automática

**Responsabilidad:** Cola de trabajos; sin conocer impresora física.

| Tipo | Nombre |
| ---- | ------ |
| Aggregate Root | `PrintJob` |
| Entity | `Printer` (config por branch) |
| Value Object | `PrintJobStatus`, `PrinterDestination`, `PrintDocumentType`, `PrintPayload` |
| Domain Service | `PrintJobGrouper` | Agrupa ítems por destino (BAR, KITCHEN, CASHIER) |

**Regla:** El hosting solo crea `PrintJob`; un **Print Agent** (infra externa) consume la cola.

---

### 3.12 Staff Settlement / Liquidaciones

**Responsabilidad:** Consolidar y pagar al personal por turno oficial; orquesta subcontextos.

| Tipo | Nombre |
| ---- | ------ |
| Aggregate Root | `StaffSettlement` (por staff + official_shift + branch) |
| Entity | `SettlementLine` |
| Value Object | `SettlementStatus` (`PENDING`, `PAID`, `CANCELLED`) |

Subcontextos especializados (misma capa de dominio, módulos separados):

---

### 3.13 Girl Commissions / Manillas, piezas y shows

**Responsabilidad:** Ingresos de chicas desglosados por fuente.

| Tipo | Nombre |
| ---- | ------ |
| Entity | `GirlBraceletEntry` | Manilla por venta CON_ACOMPANANTE |
| Aggregate Root | `RoomService` | Pieza |
| Aggregate Root | `ShowService` | Show |
| Entity | `GirlSettlement` | Liquidación diaria por chica/turno |
| Entity | `GirlSettlementItem` | Detalle por `BRACELET`, `ROOM`, `SHOW`, `BONUS`, `DISCOUNT` |
| Value Object | `GirlCommissionSource`, `GirlAmount` |

**Regla:** Manillas se originan en `SalePaid` con modalidad CON_ACOMPANANTE; piezas/shows son agregados independientes registrados en caja.

---

### 3.14 Waiter Commissions / Porcentajes variables

**Responsabilidad:** Comisión de garzón por venta y liquidación por turno.

| Tipo | Nombre |
| ---- | ------ |
| Entity | `WaiterCommissionEntry` | Generada en cada `SaleItem` con garzón |
| Entity | `WaiterSettlement` | Total a pagar por turno |
| Value Object | `CommissionPercent`, `CommissionAmount` |

**Regla:** `WaiterCommissionGenerated` ocurre en **SalePaid**, no exclusivamente en `ShiftClosed`.

---

### 3.15 Reports

**Responsabilidad:** Consultas gerenciales, exportación PDF/Excel.

| Tipo | Nombre |
| ---- | ------ |
| Read Model | `SalesByDateProjection`, `TopProductsProjection`, etc. |
| Domain Service | `ReportScope` (tenant, branch, official_shift, rango) |

**Límite:** Reports **no contiene reglas de negocio** que alteren ventas o caja; solo lee proyecciones.

---

## 4. Value Objects (catálogo)

### 4.1 Obligatorios (solicitados)

| Value Object | Tipo / Valores | Uso |
| ------------ | -------------- | --- |
| `Money` | Decimal + `Currency` (BOB default) | Precios, pagos, comisiones, cierre |
| `PaymentMethod` | `CASH`, `QR`, `CARD`, `OTHER` | Ventas, movimientos, liquidaciones |
| `OrderStatus` | Enum ciclo de comanda | Agregado `Order` |
| `ShiftStatus` | `SCHEDULED`, `ACTIVE`, `CLOSED` | `OfficialShift`, `CashSession` |
| `CommissionPercent` | 0–100, validación | Garzón (config y snapshot) |
| `ProductSaleMode` | `REGULAR`, `SOLO`, `CON_ACOMPANANTE`, `PROMO`, `VIP` | Precios boliche |
| `StaffRole` | `WAITER`, `GIRL`, `CASHIER`, `MANAGER`, … | Permisos operativos |
| `PrintJobStatus` | `PENDING`, `PRINTING`, `PRINTED`, `FAILED`, `CANCELLED` | Cola impresión |

### 4.2 Transversales (Shared Kernel)

| Value Object | Descripción |
| ------------ | ----------- |
| `TenantId` | Identificador empresa SaaS |
| `BranchId` | Sucursal/casa |
| `UserId` / `StaffMemberId` | Identidad |
| `OfficialShiftId` | Turno oficial 09–21 / 21–09 |
| `CashSessionId` | Sesión de caja del cajero |
| `OccurredAt` | DateTime con timezone de branch |

### 4.3 Adicionales recomendados

| Value Object | Uso |
| ------------ | --- |
| `Quantity` | Ítems enteros/decimales |
| `TableStatus` | `AVAILABLE`, `OCCUPIED`, `RESERVED`, `CLOSED` |
| `DispatchDestination` | `BAR`, `KITCHEN`, `CASHIER`, `RECEPTION`, `GENERAL` |
| `CashMovementType` | `INCOME`, `EXPENSE`, `STAFF_PAYMENT`, `ADJUSTMENT` |
| `GirlCommissionSource` | `BRACELET`, `ROOM`, `SHOW`, `BONUS`, `DISCOUNT` |
| `LegacyCode` | Trazabilidad migración desde `codproducto`, etc. |

**Reglas de implementación VO:** Inmutables, comparación por valor, sin identidad propia.

---

## 5. Aggregates (raíces e invariantes)

### 5.1 `Tenant`

| Elemento | Contenido |
| -------- | --------- |
| Raíz | `Tenant` |
| Límite | Datos legales + referencia a `Subscription` activa |
| Invariantes | Slug único; no eliminar con branches activas sin política |

### 5.2 `Branch`

| Elemento | Contenido |
| -------- | --------- |
| Raíz | `Branch` |
| Hijos | `Room`, `RoomTable`, `BranchSettings` |
| Invariantes | Pertenece a un `TenantId`; mesa única por código en branch |

### 5.3 `Shift` (OfficialShift)

| Elemento | Contenido |
| -------- | --------- |
| Raíz | `OfficialShift` |
| Invariantes | No solapar DAY/NIGHT en misma branch/fecha; ventas solo asignan a turno resuelto por `OfficialShiftResolver` |

### 5.4 `CashRegister` + `CashSession`

| Elemento | Contenido |
| -------- | --------- |
| Raíz operativa | `CashSession` (ciclo apertura–cierre) |
| Config | `CashRegister` (catálogo de cajas) |
| Invariantes | Una sesión `OPEN` por cajero/caja/branch (política configurable); no venta sin sesión abierta; movimientos solo con sesión abierta |

### 5.5 `Order`

| Elemento | Contenido |
| -------- | --------- |
| Raíz | `Order` |
| Hijos | `OrderItem[]`, historial estado |
| Invariantes | `tenant_id` + `branch_id`; ítems con precio resuelto por dominio; CON_ACOMPANANTE requiere `girl_id` antes de enviar/cobrar; no modificar ítems en `BILLED`/`CANCELLED` |

### 5.6 `Sale`

| Elemento | Contenido |
| -------- | --------- |
| Raíz | `Sale` |
| Hijos | `SaleItem`, `Payment`, `SaleItemStaffSnapshot` |
| Invariantes | Toda venta: `tenant_id`, `branch_id`, `official_shift_id`, `cash_session_id`; snapshots obligatorios; comisión garzón y monto chica en línea al pagar; suma pagos = total |

### 5.7 `Product`

| Elemento | Contenido |
| -------- | --------- |
| Raíz | `Product` |
| Hijos | `ProductPrice`, `ProductPriceRule` |
| Invariantes | Al menos un precio activo por modalidad habilitada; no precios negativos; reglas CON_ACOMPANANTE con `girl_amount` definido si aplica |

### 5.8 `StaffMember`

| Elemento | Contenido |
| -------- | --------- |
| Raíz | `StaffMember` |
| Hijos | `StaffProfile`, asignación branches |
| Invariantes | Chica/garzón con `staff_type` coherente; comisión garzón en rango válido |

### 5.9 `StaffSettlement` (+ Girl / Waiter)

| Elemento | Contenido |
| -------- | --------- |
| Raíz | `GirlSettlement` / `WaiterSettlement` (agregados separados por tipo) |
| Invariantes | Solo pagar líneas del `official_shift_id` y `branch_id` actuales; no doble pago (`PAID` idempotente); pago registra `CashMovement` |

### 5.10 `PrintJob`

| Elemento | Contenido |
| -------- | --------- |
| Raíz | `PrintJob` |
| Invariantes | Pertenece a tenant/branch; fallo de impresión no revierte comanda; reintento incrementa `attempts` |

---

## 6. Domain Events

| Evento | Emisor | Payload mínimo | Consumidores típicos |
| ------ | ------ | ---------------- | -------------------- |
| `ShiftOpened` | Cash | `cashSessionId`, `branchId`, `officialShiftId`, `cashierId` | Reports, auditoría |
| `ShiftClosed` | Cash | `shiftClosureId`, totales por método, diferencia | Reports, Staff Settlement |
| `OrderCreated` | Orders | `orderId`, `tableId`, `waiterId` | Printing (opcional), auditoría |
| `OrderItemAdded` | Orders | `orderId`, `itemId`, `productSaleMode`, `girlId?` | Printing (agrupar jobs) |
| `SalePaid` | Sales | `saleId`, snapshots, pagos | Inventory, Girl/Waiter Commissions, Cash |
| `InventoryStockMoved` | Inventory | `stockItemId`, `quantity`, `reason` | Alerts, Reports |
| `PrintJobCreated` | Printing | `printJobId`, `destination` | Print Agent (infra) |
| `WaiterCommissionGenerated` | Sales / Waiter | `saleItemId`, `amount`, `percent` snapshot | Waiter Settlement (acumular) |
| `GirlCommissionGenerated` | Sales / Girl | `saleItemId`, `braceletAmount`, `girlId` | Girl Settlement (acumular) |
| `StaffSettlementClosed` | Staff Settlement | `settlementId`, `paidAmount`, `paymentMethod` | Cash (movimiento), Reports |

**Eventos adicionales recomendados:** `OrderSentToBar`, `SaleVoided`, `CashMovementRegistered`, `RoomServiceRegistered`, `ShowServiceRegistered`.

**Política:** Eventos son hechos pasados; handlers en **Application** coordinan entre contextos; el dominio solo los emite desde agregados.

---

## 7. Reglas críticas (políticas de dominio)

### 7.1 Catálogo obligatorio (Fase 2)

| # | Regla | Enforcement |
| --- | ----- | ----------- |
| R1 | Toda venta pertenece a **tenant**, **branch** y **turno oficial** | `Sale` aggregate + `OfficialShiftResolver` |
| R2 | No vender/cobrar sin **caja abierta** (`CashSession` OPEN) | Domain policy antes de `SalePaid` |
| R3 | Cada producto puede tener precio **SOLO** y **CON_ACOMPANANTE** | `Product` + `ProductPriceResolver` |
| R4 | Venta **CON_ACOMPANANTE** exige **chica asignada** | `OrderItem` / `SaleItemStaffSnapshot` |
| R5 | Toda venta guarda **snapshot** de precio, comisión y personal | `SaleItemStaffSnapshot` inmutable |
| R6 | Comisión garzón se **persiste en la venta** al cobrar | `WaiterCommissionGenerated` + snapshot en `Sale` |
| R7 | Comisiones chica separadas: **manillas**, **piezas**, **shows** | Subcontextos Girl; fuentes distintas en liquidación |
| R8 | Cierre separa **efectivo**, **QR**, **tarjeta**, gastos, comisiones, **diferencia** | `ShiftClosure` entity |
| R9 | Impresión vía **PrintJobs**; hosting no imprime | Printing context |
| R10 | Turnos oficiales: **09:00–21:00** y **21:00–09:00** siguiente | `OfficialShiftType` + `ShiftWindow` |

### 7.2 Reglas derivadas (documentación + auditoría)

| Regla | Descripción |
| ----- | ----------- |
| R11 | Frontend no envía precio final; solo `product_id`, `quantity`, `product_sale_mode` |
| R12 | No recalcular comisiones históricas con % actual del garzón |
| R13 | Pago a chica/garzón genera `CashMovement` tipo `STAFF_PAYMENT` |
| R14 | Efectivo esperado = ventas efectivo + ingresos − egresos − pagos personal efectivo |
| R15 | Fallo de `PrintJob` no cancela comanda |
| R16 | Suscripción vencida bloquea operaciones de venta (no login de plataforma según política) |
| R17 | Descuento de stock en la misma transacción que `SalePaid` |
| R18 | No cerrar caja con comandas abiertas o ventas pendientes (salvo permiso especial) |

### 7.3 Fórmula de cierre (referencia)

```text
expected_cash = sales_cash + income_cash - expense_cash - staff_payments_cash
cash_difference = counted_cash - expected_cash
```

QR y tarjeta se reportan aparte (no mezclar en efectivo físico).

---

## 8. Límites de capas (Hexagonal / Clean)

### 8.1 Dominio (`app/Domain/{Context}/`)

**Pertenece:**

- Entidades y aggregate roots
- Value objects y enums de negocio
- Domain events
- Domain services (`ProductPriceResolver`, `OfficialShiftResolver`)
- Repository **interfaces** (puertos)
- Excepciones de dominio (`CashSessionNotOpen`, `GirlRequiredForSaleMode`)
- Políticas e invariantes

**No pertenece:**

- Laravel, Eloquent, HTTP, JWT, PDF, ESC/POS, SQL, Vue, Axios

### 8.2 Aplicación (`app/Application/{Context}/`)

**Pertenece:**

- Casos de uso (commands/queries)
- DTOs de entrada/salida de casos de uso
- Orquestación transaccional entre agregados
- Handlers de domain events (integración entre contextos)
- Servicios de aplicación (autorización de permisos como puerto)

**No pertenece:**

- Reglas de precio (delegar a dominio)
- HTML/PDF/JSON de API (delegar a presentación)
- Detalle de impresora USB/IP

### 8.3 Infraestructura (`app/Infrastructure/`)

**Pertenece:**

- Implementación Eloquent de repositorios
- JWT, middleware `tenant`, `subscription`
- Cola, mail, filesystem
- Generadores PDF reportes
- Adaptador Print Agent API
- Migraciones y seeders (**fase posterior**)

### 8.4 Presentación (`app/Presentation/`)

**Pertenece:**

- Controllers `/api/v1`
- Form Requests (validación formato)
- API Resources (serialización)
- Respuesta JSON uniforme `{ success, message, data }`

**No pertenece:**

- Cálculo de comisiones o precios

### 8.5 Frontend (`frontend/`)

**Pertenece:**

- UI Materialize, Pinia, rutas
- Intención del usuario (seleccionar modalidad, chica, mesa)
- Mostrar totales **devueltos por API**
- Permisos de UI (ocultar botones)

**No pertenece:**

- Precio final como fuente de verdad
- Lógica de cierre de caja o liquidación
- Creación de `PrintJob` (solo estado leído y reimpresión autorizada)

---

## 9. Mapa: sistema heredado → dominio SaaS

| Heredado | Bounded Context | Agregado / Entidad SaaS |
| -------- | --------------- | ------------------------ |
| `configuracion` | Tenant | `Tenant` |
| — (nuevo) | Tenant | `Plan`, `Subscription` |
| `sucursales` | Branch | `Branch`, `BranchSettings` |
| `salas`, `mesas` | Branch | `Room`, `RoomTable` |
| `usuarios` | Auth + User/Staff | `User`, `StaffMember`, `StaffProfile` |
| `log` | Auth / Platform | Auditoría (infra) |
| `horarios` | Shift | Plantilla → `OfficialShiftType` |
| `cajas` | Cash | `CashRegister` |
| `arqueocaja` | Cash | `CashSession`, `ShiftClosure` |
| `movimientoscajas` | Cash | `CashMovement` |
| `categorias`, `medidas`, `salsas` | Products | `Category`, `Unit`, modificadores |
| `productos` | Products | `Product`, `ProductPrice` |
| — | Products | `ProductPriceRule`, `ProductSaleMode` |
| `ingredientes`, `productosxingredientes` | Products / Inventory | `RecipeLine`, `StockItem` |
| `combos`, `combosxproductos` | Products | `Combo`, `ComboItem` |
| `pedidos`, `detallepedidos` | Orders | `Order`, `OrderItem` |
| `notificaciones` | Orders + Printing | `OrderStatusHistory`, `PrintJob` |
| `comanda_bar/cocina/reposteria` | Orders + Printing | UI despacho + `DispatchDestination` |
| `ventas`, `detalleventas` | Sales | `Sale`, `SaleItem` |
| — | Sales | `SaleItemStaffSnapshot`, `Payment` |
| `usuarios.comision` | Waiter Commissions | Config en `StaffProfile`; snapshot en `Sale` |
| — | Girl Commissions | `GirlBraceletEntry`, `RoomService`, `ShowService` |
| — | Staff Settlement | `GirlSettlement`, `WaiterSettlement` |
| `kardex_*` | Inventory | `StockMovement` |
| `compras`, `detallecompras` | Inventory | `PurchaseOrder` |
| `traspasos`, `detalletraspasos` | Inventory | `StockTransfer` |
| `proveedores` | Inventory | `Supplier` |
| `clientes`, `creditosxclientes` | CRM (fase opcional) | Fuera MVP; ACL si se migra |
| `cotizaciones` | CRM (opcional) | Baja prioridad boliche |
| `delivery` | Orders (opcional) | Flag bajo prioridad |
| `notascredito` | Sales | `SaleVoid` / nota crédito |
| `reportepdf`, `reporteexcel` | Reports | Proyecciones + infra PDF |
| `impuestos`, `tiposmoneda` | Branch / Products | Config fiscal |
| `provincias`, `departamentos` | Branch / CRM | Datos maestros |

---

## 10. Estructura de carpetas objetivo (referencia, sin implementar)

```text
app/
  Domain/
    Tenant/
    Branch/
    Auth/
    Staff/
    Shift/
    Cash/
    Orders/
    Sales/
    Products/
    Inventory/
    Printing/
    StaffSettlement/
    GirlCommissions/
    WaiterCommissions/
    Reports/
    Shared/          # TenantId, BranchId, Money, ...
  Application/
    {Context}/UseCases/
    {Context}/Handlers/
  Infrastructure/
    Persistence/Eloquent/
    Auth/Jwt/
    Printing/
    Reports/
  Presentation/
    Http/Controllers/Api/V1/
    Requests/
    Resources/
```

---

## 11. Riesgos de diseño (pre-implementación)

| ID | Riesgo | Impacto | Mitigación |
| ---- | ------ | ------- | ---------- |
| D1 | Confundir **OfficialShift** con **CashSession** | Reportes y liquidaciones incorrectos | Nombres ubicuos; `OfficialShiftResolver` único |
| D2 | Comisión garzón solo al cierre | Histórico inconsistente | R6: snapshot + evento en `SalePaid` |
| D3 | Agregado `Sale` demasiado grande | Transacciones largas | Snapshots como entidades hijas; eventos asíncronos solo post-commit |
| D4 | Girl/Waiter como un solo “settlement” | Mezcla manillas/piezas/shows | Agregados separados; `GirlCommissionSource` |
| D5 | Print en dominio Sales/Orders | Acoplamiento | Solo evento → Application → `CreatePrintJobs` |
| D6 | Reports recalculan comisiones | Cierres falsos | Proyecciones desde snapshots; prohibido en DEVELOPMENT_RULES |
| D7 | Multi-tenant leak | Seguridad crítica | `TenantScope` en infra + tests de aislamiento |
| D8 | Turno NIGHT cruza medianoche | Ventas mal asignadas | Tests con 20:59, 21:00, 08:59, 09:00; timezone branch |
| D9 | Dos cajeros, una caja | Race conditions | Lock optimista en `CashSession` o regla 1 sesión/caja |
| D10 | Migración legacy sin `price_type` | Datos históricos incompletos | `legacy_code` + migrar solo resumen o default REGULAR |
| D11 | Contextos Inventory ↔ Sales | Stock negativo | Transacción única; evento `InventoryStockMoved` después de validar |
| D12 | StaffSettlement paga sin caja abierta | Descuadre | Policy: `STAFF_PAYMENT` solo con sesión OPEN o en cierre |
| D13 | Over-engineering de eventos | Complejidad | MVP: handlers síncronos en Application; cola después |
| D14 | CRM/Delivery en mismo MVP | Retraso | Bounded contexts opcionales detrás de `plan_modules` |

---

## 12. Casos de uso clave (referencia Application — no implementar aún)

| Contexto | Caso de uso |
| -------- | ----------- |
| Auth | `LoginUseCase`, `LoginWithPinUseCase` |
| Tenant | `CreateTenantUseCase`, `SuspendTenantUseCase` |
| Branch | `CreateBranchUseCase` |
| Shift | `ResolveOfficialShiftUseCase` |
| Cash | `OpenCashSessionUseCase`, `CloseCashSessionUseCase`, `RegisterCashMovementUseCase` |
| Products | `CreateProductUseCase`, `ResolveProductPriceUseCase` |
| Orders | `CreateOrderUseCase`, `AddOrderItemUseCase`, `SendOrderToBarUseCase` |
| Sales | `ChargeOrderUseCase`, `CreateDirectSaleUseCase` |
| Printing | `CreatePrintJobsForOrderUseCase` |
| Girl | `RegisterRoomServiceUseCase`, `RegisterShowServiceUseCase` |
| Waiter | (implícito en `ChargeOrderUseCase`) |
| Settlement | `PayGirlSettlementUseCase`, `PayWaiterSettlementUseCase` |

---

## 13. Criterios de aceptación Fase 2

- [x] Bounded contexts definidos (15 áreas solicitadas)
- [x] Entidades principales por contexto
- [x] Value Objects solicitados + transversales
- [x] Aggregates con invariantes
- [x] Domain Events catalogados
- [x] Reglas críticas incluyendo turnos 09:00–21:00 y 21:00–09:00
- [x] Límites dominio / aplicación / infra / frontend
- [x] Mapa heredado → SaaS
- [x] Riesgos de diseño documentados
- [x] Sin código ni migraciones

---

## 14. Siguiente paso (esperar instrucciones)

Fase 3 recomendada según `MIGRATION_PLAN.md`: **fundación backend** (estructura de carpetas, contratos base, excepciones, respuesta API, middleware tenant/subscription) **o** detalle de **esquema físico** (`DATABASE_SCHEMA.md`) derivado de este diseño.

---

*Documento generado en Fase 2. Sistema heredado `restaurant_bolivia-1` sin cambios.*
