# DEVELOPMENT_RULES.md

# OBJETIVO

Reglas obligatorias para Cursor al migrar el sistema.

---

# REGLA PRINCIPAL

Este proyecto es una migración a:

* Laravel API REST.
* Vue 3.
* Arquitectura hexagonal.
* SOLID.
* Multi Tenant.

No es una continuación del PHP heredado.

---

# PROHIBIDO

Cursor no debe:

* Crear nuevas pantallas PHP heredadas.
* Meter SQL directo en controladores.
* Poner reglas de negocio en Vue.
* Quemar precios en frontend.
* Crear CRUD genéricos sin casos de uso.
* Mezclar caja con ventas sin transacciones.
* Descontar stock sin registrar movimiento.
* Vender sin caja abierta.
* Ignorar tenant_id.
* Saltarse tests.

---

# OBLIGATORIO

Cada módulo debe tener:

* Entidades de dominio.
* Value Objects cuando aplique.
* Casos de uso.
* Puertos/repositorios.
* Implementación Eloquent en Infrastructure.
* Controller API.
* Request validation.
* Resource/DTO de salida.
* Tests unitarios y feature.
* Documentación API.

---

# ESTRUCTURA DE CAPAS

```text
Domain: reglas puras del negocio
Application: casos de uso
Infrastructure: Laravel, Eloquent, JWT, reportes
Presentation: Controllers, Requests, Resources
Frontend: Vue consume API, no decide reglas
```

---

# PRINCIPIOS SOLID

## Single Responsibility

Cada clase debe tener una sola responsabilidad.

Ejemplo:

* `CreateOrderUseCase` crea comanda.
* `ChargeOrderUseCase` cobra comanda.
* `CloseCashSessionUseCase` cierra caja.
* `ResolveProductPriceUseCase` decide precio según modalidad.

## Open/Closed

La regla de precios debe poder extenderse sin modificar ventas.

Ejemplo:

* `SOLO`
* `CON_ACOMPANANTE`
* `PROMO`
* `VIP`

## Liskov

Los repositorios deben cumplir contratos.

## Interface Segregation

No crear interfaces gigantes tipo `SistemaRepository`.

## Dependency Inversion

Los casos de uso dependen de interfaces, no de Eloquent.

---

# TRANSACCIONES

Usar transacciones en:

* Crear venta.
* Cobrar mesa.
* Cerrar caja.
* Registrar compra.
* Registrar traspaso.
* Ajustar inventario.
* Anular venta.

---

# VALIDACIONES CRÍTICAS

Antes de vender:

* tenant activo
* suscripción vigente
* usuario autorizado
* sucursal activa
* caja abierta
* producto activo
* precio válido
* stock suficiente si controla inventario

---

# REGLA DE FRONTEND

El frontend solo envía intención:

```json
{
  "product_id": 10,
  "quantity": 1,
  "price_type": "CON_ACOMPANANTE"
}
```

El backend decide el precio real.

---

# NOMENCLATURA

Usar inglés para código:

* Product
* Sale
* Order
* CashSession
* Branch
* Tenant
* Inventory

Usar español solo en textos visibles al usuario.

---

# REGLAS PARA IMPRESIÓN AUTOMÁTICA

Cursor debe respetar estas reglas:

* El backend SaaS no imprime directamente.
* No usar drivers de impresora dentro del dominio.
* No conectar Laravel directamente con USB, Windows, red local o ESC/POS desde casos de uso de negocio.
* La impresión debe funcionar con cola `print_jobs`.
* Al crear una comanda, se debe crear automáticamente uno o varios `PrintJob`.
* El garzón no debe hacer ningún paso extra.
* Si la impresión falla, la comanda sigue registrada.
* Debe existir opción de reimpresión por permisos.
* Cada `PrintJob` debe pertenecer a `tenant_id` y `branch_id`.
* El Print Agent debe usar un token seguro por sucursal.
* No exponer trabajos de impresión de otras sucursales.

Caso de uso obligatorio al crear comanda:

```text
CreateOrderUseCase
    ↓
Save order
    ↓
Update inventory/kardex
    ↓
CreatePrintJobsForOrderUseCase
    ↓
Return order response
```


---

# REGLAS OBLIGATORIAS PARA CIERRE Y COMISIONES

Cursor debe cumplir:

* No calcular comisiones solo en frontend.
* No recalcular ventas antiguas con precios actuales.
* No recalcular comisiones antiguas con porcentajes actuales.
* Guardar snapshot de producto, precio, chica, garzón, porcentaje y monto al momento de vender.
* Toda venta debe pertenecer a un turno abierto.
* No permitir cierre si hay ventas pendientes, pagos incompletos o comandas sin finalizar, salvo permiso especial.
* No permitir pagar liquidaciones si el turno no corresponde a la sucursal actual.
* Registrar pagos a chicas y garzones como movimientos de caja.
* Separar siempre efectivo, QR y tarjeta.
* El cierre debe ser auditable.

# TESTS MÍNIMOS

Crear tests para:

```text
Venta solo no genera comisión de chica.
Venta con chica genera manilla.
Paceña con chica genera monto chica configurado.
Huari con chica genera monto chica configurado.
Garzón 5% calcula comisión correcta.
Garzón 6% calcula comisión correcta.
Cambio de porcentaje no modifica ventas antiguas.
Cierre separa efectivo, QR y tarjeta.
Pago a chica descuenta del efectivo esperado si fue en efectivo.
Pago a garzón descuenta del efectivo esperado si fue en efectivo.
```
