# SAAS_ARCHITECTURE.md

# OBJETIVO

Definir la arquitectura SaaS para convertir el sistema heredado de restaurante en plataforma multi empresa para boliches, bares y negocios nocturnos.

---

# ESTRATEGIA MULTI TENANT

Se usará:

**Single Database Multi Tenant**

Una sola base de datos con `tenant_id` en todas las tablas de negocio.

---

# JUSTIFICACIÓN

Esta estrategia es adecuada porque:

* Es compatible con hosting compartido.
* Reduce costos.
* Permite administrar varias empresas desde una instalación.
* Permite planes y suscripciones.
* Es más simple para iniciar como SaaS.

---

# ESTRUCTURA SaaS

| Entidad | Descripción |
| ------- | ----------- |
| Tenant | Empresa cliente del SaaS |
| Branch | Sucursal/casa/boliche dentro del tenant |
| Plan | Plan contratado |
| Subscription | Membresía activa del tenant |
| User | Usuario del sistema |
| Role | Rol operativo |
| Permission | Permiso granular |

---

# TENANTS

Ejemplos:

* Casa 22
* Madames
* Luxos
* Boliche Cliente A
* Bar Cliente B

Campos mínimos:

* id
* name
* slug
* legal_name
* nit
* status
* plan_id
* subscription_status
* subscription_ends_at
* created_at
* updated_at

---

# SUCURSALES / CASAS

El heredado usa `sucursales` y `configuracion`.

En el sistema nuevo se recomienda usar `branches`.

Campos mínimos:

* id
* tenant_id
* name
* code
* address
* phone
* status

Ejemplos:

* Casa 22 Centro
* 22 VIP
* Corona
* Madames
* Luxos

---

# ROLES OPERATIVOS

| Rol | Función |
| --- | ------- |
| Super Admin SaaS | Administra todo el SaaS |
| Tenant Owner | Dueño de empresa |
| Administrador | Administra una o varias sucursales |
| Encargado | Controla turno/casa |
| Cajero | Caja, cobros, arqueo |
| Garzón | Pedidos/comandas |
| Barra | Preparación/despacho |
| Inventario | Stock, compras, traspasos |
| Reportes | Solo lectura gerencial |

---

# AISLAMIENTO DE DATOS

Toda consulta de negocio debe filtrar por:

* `tenant_id`
* `branch_id` cuando aplique

Regla obligatoria:

Un usuario nunca debe ver datos de otro tenant.

---

# SUSCRIPCIONES

El SaaS debe soportar:

* Fecha de inicio.
* Fecha de vencimiento.
* Estado activo/vencido/suspendido.
* Alertas antes de vencer.
* Bloqueo o modo solo lectura al vencer.
* Planes por módulos.

---

# MÓDULOS POR PLAN

Ejemplo:

| Módulo | Básico | Pro | Empresarial |
| ------ | ------ | --- | ------------ |
| Ventas | Sí | Sí | Sí |
| Caja | Sí | Sí | Sí |
| Inventario | Limitado | Sí | Sí |
| Multi sucursal | No | Sí | Sí |
| Reportes avanzados | No | Sí | Sí |
| Comisiones | No | Sí | Sí |
| Auditoría | No | No | Sí |

---

# ARQUITECTURA HEXAGONAL

Estructura recomendada:

```text
app/
  Domain/
    Tenant/
    Identity/
    Branch/
    Product/
    Order/
    Sale/
    Cashbox/
    Inventory/
    Purchase/
    Report/
    NightClub/
  Application/
    UseCases/
    DTOs/
    Services/
  Infrastructure/
    Persistence/Eloquent/
    Auth/Jwt/
    Reports/
  Presentation/
    Http/Controllers/Api/V1/
    Requests/
    Resources/
```

---

# REGLA

El dominio no debe depender de Laravel, Eloquent, Request, Controller ni Vue.

Laravel debe ser infraestructura, no el centro del negocio.

---

# CONTEXTO DE IMPRESIÓN / PRINTING CONTEXT

Agregar un bounded context nuevo llamado `Printing`.

Responsabilidades:

* Gestionar trabajos de impresión.
* Crear cola de impresión automática.
* Permitir reimpresiones.
* Configurar impresoras por tenant y sucursal.
* Asignar impresoras por destino operativo: barra, cocina, caja, recepción.
* Mantener historial de impresión.

Estructura sugerida:

```text
app/Domain/Printing
├── Entities
│   ├── PrintJob.php
│   └── Printer.php
├── ValueObjects
│   ├── PrintJobStatus.php
│   ├── PrinterDestination.php
│   └── PrintDocumentType.php
├── Repositories
│   ├── PrintJobRepositoryInterface.php
│   └── PrinterRepositoryInterface.php
└── Services
    └── PrintJobPayloadBuilderInterface.php
```

```text
app/Application/Printing
├── CreatePrintJobUseCase.php
├── GetPendingPrintJobsUseCase.php
├── MarkPrintJobAsPrintedUseCase.php
├── MarkPrintJobAsFailedUseCase.php
└── ReprintOrderUseCase.php
```

```text
app/Infrastructure/Printing
├── Http
│   └── Controllers
│       └── PrintJobController.php
├── Persistence
│   └── Eloquent
│       ├── PrintJobModel.php
│       └── PrinterModel.php
└── LocalAgent
    └── README.md
```

Regla importante:

El dominio no debe conocer impresoras físicas, drivers, IPs, USB, Windows ni ESC/POS. El dominio solo crea trabajos de impresión. La impresión real pertenece a infraestructura externa mediante el Print Agent local.


---

# CONTEXTO NUEVO: SHIFT CLOSURE / STAFF SETTLEMENT

Agregar dos bounded contexts al sistema:

## Cash & Shift Closure Context

Responsable de:

* Apertura de caja.
* Cierre de turno.
* Ventas por método de pago.
* Ingresos y egresos.
* Diferencia de caja.
* Aprobación de cierre por encargado.

Casos de uso:

```text
OpenShiftCashRegisterUseCase
GetCurrentShiftSummaryUseCase
CloseShiftUseCase
ApproveShiftClosureUseCase
RegisterCashMovementUseCase
```

## Staff Settlement Context

Responsable de:

* Manillas de chicas.
* Piezas.
* Shows.
* Comisión variable de garzones.
* Liquidación diaria.
* Estado de pago pendiente/pagado.

Casos de uso:

```text
CalculateGirlSettlementUseCase
PayGirlSettlementUseCase
CalculateWaiterSettlementUseCase
PayWaiterSettlementUseCase
RegisterRoomServiceUseCase
RegisterShowServiceUseCase
```

## Estructura hexagonal sugerida

```text
app/Domain/ShiftClosure
app/Application/ShiftClosure
app/Infrastructure/Persistence/Eloquent/ShiftClosure

app/Domain/StaffSettlement
app/Application/StaffSettlement
app/Infrastructure/Persistence/Eloquent/StaffSettlement
```

## Eventos de dominio

Cuando se registra una venta o comanda, emitir:

```text
SaleCreated
SaleItemRegistered
PaymentReceived
```

Estos eventos deben permitir generar:

```text
WaiterCommission
GirlBraceletCommission
CashMovement
PrintJob
```

La impresión automática ya definida no reemplaza el cierre, solo acompaña el flujo operativo.
