# ROADMAP.md

# OBJETIVO

Orden oficial para que Cursor migre el sistema sin romper la lógica del negocio.

---

# ROADMAP GENERAL

| Fase | Módulo | Estado inicial | Prioridad |
| ---- | ------ | -------------- | --------- |
| 0 | Análisis heredado | Documentado | Alta |
| 1 | Arquitectura base | Pendiente | Alta |
| 2 | Auth / usuarios | Pendiente | Alta |
| 3 | Tenants / sucursales | Pendiente | Alta |
| 4 | Productos / precios boliche | Pendiente | Alta |
| 5 | Salas / mesas | Pendiente | Alta |
| 6 | Caja / arqueo | Pendiente | Crítica |
| 7 | Pedidos / comandas | Pendiente | Crítica |
| 8 | Ventas / pagos | Pendiente | Crítica |
| 9 | Inventario / kardex | Pendiente | Alta |
| 10 | Compras / proveedores | Pendiente | Media |
| 11 | Traspasos | Pendiente | Media |
| 12 | Reportes | Pendiente | Alta |
| 13 | Comisiones / reglas boliche | Pendiente | Alta |
| 14 | Suscripciones SaaS | Pendiente | Alta |
| 15 | Auditoría / seguridad | Pendiente | Media |

---

# ORDEN RECOMENDADO PARA CURSOR

## Sprint 1

* Crear Laravel limpio.
* Crear arquitectura hexagonal.
* Crear respuesta API.
* Crear migraciones SaaS base.
* Crear Auth JWT.

## Sprint 2

* Usuarios.
* Roles.
* Permisos.
* Tenants.
* Sucursales.

## Sprint 3

* Productos.
* Categorías.
* Precios.
* Regla SOLO / CON_ACOMPANANTE.

## Sprint 4

* Salas.
* Mesas.
* Estados.
* Vista Vue de mesas.

## Sprint 5

* Caja.
* Apertura.
* Movimientos.
* Cierre.

## Sprint 6

* Comandas.
* Agregar productos.
* Enviar a barra.
* Cobrar mesa.

## Sprint 7

* Ventas directas.
* Pagos múltiples.
* Reporte de caja.

## Sprint 8

* Inventario.
* Kardex.
* Compras.
* Traspasos.

## Sprint 9

* Reportes gerenciales.
* Comisiones.
* Exportaciones.

---

# DEPENDENCIAS ENTRE MÓDULOS

```text
Auth -> Tenant -> Branch -> Product -> Price -> Cash -> Order -> Sale -> Inventory -> Reports
```

---

# MÓDULO CRÍTICO

El módulo más delicado es:

```text
Order + Sale + Cash + Inventory
```

Porque al cobrar una mesa debe ocurrir en una transacción:

1. Validar caja abierta.
2. Validar productos y precios.
3. Crear venta.
4. Registrar pagos.
5. Descontar stock.
6. Actualizar mesa.
7. Cerrar comanda.
8. Registrar auditoría.

---

# PRIMER MVP VISUAL

El primer MVP visible debe permitir:

* iniciar sesión
* abrir caja
* ver mesas
* abrir comanda
* agregar cerveza con precio SOLO o CON_ACOMPANANTE
* cobrar
* ver cierre de caja

Si eso funciona, el sistema ya valida la adaptación al boliche.

---

# FASE NUEVA RECOMENDADA: 7.5 IMPRESIÓN AUTOMÁTICA

Insertar esta fase después de:

```text
Fase 7: Pedidos / comandas
```

y antes de:

```text
Fase 8: Ventas / pagos
```

Motivo:

La impresión de comandas es crítica para operación real de boliche, barra y cocina.

Entregables:

* Cola de impresión `print_jobs`.
* Configuración de impresoras por sucursal.
* Print Agent local.
* Impresión por destino: BAR, KITCHEN, CASHIER.
* Reimpresión con permisos.
* Estados: pendiente, imprimiendo, impreso, fallido.

Actualizar roadmap general:

| Fase | Módulo | Prioridad |
| ---- | ------ | --------- |
| 7.5 | Impresión automática de comandas | Crítica |


---

# AJUSTE DE ROADMAP: CIERRE Y LIQUIDACIONES

Agregar como prioridad crítica:

| Fase | Módulo | Prioridad |
| ---- | ------ | --------- |
| 8.5 | Cierre por método de pago | Crítica |
| 8.6 | Manillas / liquidación chicas | Crítica |
| 8.7 | Piezas y shows | Alta |
| 8.8 | Comisión variable de garzones | Crítica |
| 8.9 | Pagos diarios a personal | Crítica |

Este bloque debe implementarse antes de reportes avanzados porque los reportes dependen de liquidaciones correctas.
