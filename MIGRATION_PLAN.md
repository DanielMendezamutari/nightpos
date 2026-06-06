# MIGRATION_PLAN.md

# OBJETIVO

Plan formal para migrar `restaurant_bolivia-1` a NIGHTPOS SaaS.

---

# ENFOQUE

Migración incremental por módulos.

No se migra todo a la vez.

Cada fase debe cerrar backend + frontend + tests.

---

# FASE 0 — ANÁLISIS

Estado: realizado parcialmente.

Tareas:

* Revisar estructura de archivos.
* Revisar SQL dump.
* Identificar tablas.
* Identificar pantallas.
* Identificar funciones críticas.
* Documentar reglas de boliche.

---

# FASE 1 — FUNDACIÓN BACKEND

Crear estructura Laravel:

```text
app/Domain
app/Application
app/Infrastructure
app/Presentation
```

Crear:

* excepciones de dominio
* contratos base
* respuesta API uniforme
* middleware tenant
* middleware subscription
* auditoría base

---

# FASE 2 — AUTH / USUARIOS / PERMISOS

Migrar desde `usuarios`.

Debe incluir:

* login JWT
* login PIN
* roles
* permisos
* usuarios por sucursal
* usuario activo/inactivo

---

# FASE 3 — TENANTS / SUCURSALES

Migrar desde:

* `configuracion`
* `sucursales`

Crear:

* tenants
* branches
* planes
* suscripciones

---

# FASE 4 — PRODUCTOS / PRECIOS BOLICHE

Migrar desde:

* `productos`
* `categorias`
* `medidas`
* `combos`

Crear:

* productos
* categorías
* combos
* precios por modalidad

Esta fase es crítica porque aquí entra:

* SOLO
* CON_ACOMPANANTE

---

# FASE 5 — SALAS / MESAS

Migrar desde:

* `salas`
* `mesas`

Adaptar a:

* ambientes
* mesas
* privados
* estados de ocupación

---

# FASE 6 — CAJA

Migrar desde:

* `cajas`
* `arqueocaja`
* `movimientoscajas`

Implementar:

* apertura de caja
* ingresos
* egresos
* cierre
* resumen por método de pago

---

# FASE 7 — PEDIDOS / COMANDAS

Migrar desde:

* `pedidos`
* `detallepedidos`
* `notificaciones`

Implementar:

* abrir comanda
* agregar productos
* enviar a barra
* preparar/entregar
* cancelar
* cobrar

---

# FASE 8 — VENTAS

Migrar desde:

* `ventas`
* `detalleventas`

Implementar:

* venta directa
* venta desde comanda
* pagos múltiples
* anulación
* recibo
* cierre contra caja

---

# FASE 9 — INVENTARIO

Migrar desde:

* `kardex_productos`
* `kardex_ingredientes`
* `kardex_combos`
* `compras`
* `detallecompras`
* `traspasos`
* `detalletraspasos`

Implementar:

* stock actual
* kardex
* compras
* traspasos
* ajustes
* alertas mínimo/máximo

---

# FASE 10 — REPORTES

Migrar reportes heredados:

* ventas por fecha
* ventas por caja
* ventas por vendedor
* productos vendidos
* comisiones
* ganancias
* movimientos
* arqueos

---

# CRITERIO DE CIERRE POR FASE

Una fase se cierra solo si tiene:

* casos de uso
* endpoints
* validaciones
* tests
* documentación
* pantalla Vue funcional
* changelog actualizado

---

# NO HACER

No iniciar reportes antes de estabilizar ventas y caja.
No iniciar frontend de ventas si caja no está lista.
No migrar inventario sin transacciones.

---

# FASE 7.5 — IMPRESIÓN AUTOMÁTICA DE COMANDAS

Esta fase se implementa después de pedidos/comandas y antes de cerrar ventas/pagos.

Objetivo:

Agregar impresión automática sin modificar el flujo del garzón.

Tareas backend:

* Crear bounded context `Printing`.
* Crear tablas `printers` y `print_jobs`.
* Crear entidad `PrintJob`.
* Crear entidad `Printer`.
* Crear `CreatePrintJobsForOrderUseCase`.
* Crear endpoints del Print Agent.
* Crear endpoint de reimpresión.
* Crear permisos de reimpresión.

Tareas frontend:

* Mostrar estado de impresión en detalle de comanda.
* Permitir reimprimir solo a usuarios autorizados.
* Crear pantalla de configuración de impresoras por sucursal.

Tareas Print Agent local:

* Crear app local liviana para Windows/Linux.
* Consultar `/api/v1/print-jobs/pending` cada pocos segundos.
* Imprimir automáticamente.
* Confirmar impresión con `/api/v1/print-jobs/{id}/printed`.
* Marcar fallos con `/api/v1/print-jobs/{id}/failed`.

Criterio de aceptación:

Cuando un garzón guarde una comanda desde celular, la comanda debe aparecer en el sistema y también imprimirse automáticamente en la impresora correspondiente sin pasos adicionales.


---

# FASE NUEVA — CIERRE DE TURNO Y LIQUIDACIÓN DE PERSONAL

Esta fase debe agregarse después de ventas/pagos y antes de reportes finales.

## Objetivo

Permitir que la cajera cierre el turno con información completa:

* Ventas efectivo.
* Ventas QR.
* Ventas tarjeta.
* Comisiones de garzones.
* Manillas de chicas.
* Piezas.
* Shows.
* Pagos realizados.
* Diferencia de caja.

## Backend

Implementar:

```text
ShiftClosure Context
StaffSettlement Context
ProductPriceRules
SaleItemStaffSnapshots
GirlSettlements
WaiterSettlements
RoomServices
ShowServices
```

## Frontend

Implementar:

```text
Pantalla cierre de turno
Pantalla liquidación chicas
Pantalla liquidación garzones
Selector solo/con chica en comanda móvil
Selector de chica obligatorio cuando corresponde
```

## Validaciones

```text
No vender sin caja abierta.
No vender CON_CHICA sin seleccionar chica.
No cerrar caja si hay ventas pendientes.
No pagar dos veces la misma liquidación.
No cambiar cierre cerrado sin permiso administrativo.
```
