# SYSTEM_ANALYSIS.md

# OBJETIVO

Analizar el sistema heredado `restaurant_bolivia-1` antes de migrarlo a backend API + frontend Vue.

Ningún módulo debe programarse sin revisar primero este archivo.

---

# INFORMACIÓN GENERAL

| Elemento | Resultado |
| -------- | --------- |
| Sistema original | Restaurant Bolivia |
| Tipo | Sistema PHP monolítico |
| Framework | No se detecta framework moderno; PHP procedural/OOP propio |
| Base de datos | MySQL dump incluido |
| Fecha del dump | `softrestaurant_bolivia_backup_10-05-2024.sql` |
| Tablas detectadas | 44 |
| Archivo principal de lógica | `class/class.php` |
| Conexión | `class/classconexion.php` con PDO |
| Pantallas | Archivos `.php` por módulo |
| Reportes | FPDF y Excel/PHP |
| Arquitectura objetivo | Laravel API REST + Vue 3 + Hexagonal |

---

# DIAGNÓSTICO DEL HEREDADO

El sistema actual mezcla:

* Presentación HTML/PHP.
* Consultas SQL.
* Reglas de negocio.
* Control de sesión.
* Validaciones.
* Reportes.
* Operación de caja.

La lógica está altamente concentrada en `class/class.php`, con funciones como:

* `RegistrarPedido`
* `CobrarMesa`
* `CobrarCuentaSeparada`
* `RegistrarDelivery`
* `RegistrarPagoPedidos`
* `RegistrarArqueoCaja`
* `CerrarArqueoCaja`
* `RegistrarProductos`
* `RegistrarCombos`
* `RegistrarCompras`
* `RegistrarTraspasos`
* `BuscarVentasxFechas`
* `BuscarComisionxVentas`
* `ProductosMasVendidos`

Esto confirma que el heredado tiene lógica útil, pero no está separado por capas.

---

# TABLAS PRINCIPALES DETECTADAS

| Grupo | Tablas |
| ----- | ------ |
| Seguridad | `usuarios`, `log` |
| Empresa / sucursal | `configuracion`, `sucursales`, `horarios`, `cajas` |
| Ambientes | `salas`, `mesas` |
| Catálogos | `categorias`, `medidas`, `salsas`, `documentos`, `tiposmoneda`, `tiposcambio`, `impuestos` |
| Clientes / proveedores | `clientes`, `proveedores`, `creditosxclientes` |
| Productos | `productos`, `ingredientes`, `combos`, `productosxingredientes`, `combosxproductos` |
| Inventario | `kardex_productos`, `kardex_ingredientes`, `kardex_combos` |
| Compras | `compras`, `detallecompras`, `cuentasxpagar` se maneja por consultas |
| Traspasos | `traspasos`, `detalletraspasos` |
| Pedidos / comandas | `pedidos`, `detallepedidos`, `notificaciones` |
| Ventas | `ventas`, `detalleventas`, `notascredito`, `detallenotas` |
| Caja | `arqueocaja`, `movimientoscajas`, `abonoscreditos` |
| Delivery | se maneja dentro de pedidos/ventas y pantallas `delivery*.php` |

---

# PANTALLAS IMPORTANTES

| Pantalla heredada | Interpretación para nuevo sistema |
| ----------------- | --------------------------------- |
| `index.php` | Login |
| `panel.php` | Dashboard |
| `usuarios.php` | Usuarios |
| `sucursales.php` | Casas / sucursales |
| `salas.php` | Ambientes del boliche |
| `mesas.php` | Mesas / privados |
| `productos.php` | Productos / bebidas |
| `combos.php` | Combos / promociones |
| `ingredientes.php` | Insumos |
| `compras.php` | Compras |
| `traspasos.php` | Movimiento entre casas |
| `pedidos.php` | Comandas |
| `ventas.php` | Ventas cobradas |
| `cajas.php` | Cajas |
| `arqueos.php` | Apertura/cierre de caja |
| `movimientos.php` | Ingresos/egresos de caja |
| `comanda_bar.php` | Vista bar |
| `comanda_cocina.php` | Vista cocina |
| `delivery.php` | Delivery, no prioritario para boliche |
| `reportepdf.php` / `reporteexcel.php` | Reportes exportables |

---

# PROBLEMAS TÉCNICOS

| Problema | Riesgo |
| -------- | ------ |
| Lógica de negocio en un archivo gigante | Difícil mantener |
| SQL directo | Riesgo de seguridad y errores |
| No hay API REST clara | No sirve para frontend moderno |
| Sesión PHP heredada | No sirve bien para SaaS/API móvil |
| Nombres de columnas antiguos | Dificulta escalabilidad |
| Sin tenant_id | No está listo para SaaS |
| Caja e inventario acoplados | Riesgo al modificar ventas |
| Reportes mezclados con lógica | Difícil migrar |

---

# VALOR RESCATABLE

No se debe perder:

* Flujo de mesas y pedidos.
* Flujo de caja/arquéo.
* Kardex de productos/ingredientes/combos.
* Reportes por fechas, cajas, vendedores, clientes y productos.
* Separación de comanda para bar/cocina/repostería.
* Traspasos entre sucursales.
* Gestión de compras y proveedores.

---

# ADAPTACIÓN A BOLICHE

El modelo restaurante se puede adaptar a boliche si se cambia el foco:

| Restaurante | Boliche |
| ----------- | ------- |
| Mesa | Mesa, sala, privado o ambiente |
| Mesero | Garzón / anfitrión |
| Producto | Bebida, servicio, promoción |
| Pedido | Comanda |
| Cocina/bar | Barra / caja / despacho |
| Delivery | No prioritario |
| Cliente | Cliente ocasional o registrado |
| Combo | Promo 2x1, botella + compañía, cover |

La regla nueva más importante es el precio por modalidad:

* `SOLO`: cliente consume sin acompañante.
* `CON_ACOMPANANTE`: cliente consume con acompañante y tiene otro precio.

Esta regla no debe quemarse en el frontend. Debe vivir en el dominio/backend.

---

# NECESIDAD DETECTADA: IMPRESIÓN AUTOMÁTICA

El sistema heredado ya maneja comandas y pantallas relacionadas, por ejemplo `comanda_bar.php`. En la migración a SaaS se debe mantener la visualización de comandas dentro del sistema, pero agregar impresión automática.

Problema técnico:

El nuevo sistema estará en hosting online, mientras que la impresora térmica estará físicamente en el boliche. El hosting no puede imprimir directamente en esa impresora local.

Solución técnica:

Crear una cola de impresión en el backend y un Print Agent local instalado en una PC del boliche.

La impresión automática debe considerarse una extensión del flujo de comanda, no un reemplazo.

Flujo objetivo:

```text
Pedido/comanda creada
    ↓
Sistema guarda comanda
    ↓
Sistema crea PrintJob
    ↓
Print Agent local imprime
    ↓
Comanda sigue disponible en pantalla y reportes
```


---

# NUEVO ANÁLISIS OPERATIVO PARA BOLICHE

El sistema heredado de restaurante maneja productos, ventas, pedidos, caja y arqueos, pero para boliche se requiere mayor detalle en cierre de turno.

Diferencias clave:

* Un producto puede tener precio solo cliente y precio con chica.
* La diferencia o monto configurado puede pertenecer a una chica como manilla.
* Una chica puede ganar además por piezas y shows.
* Un garzón puede tener comisión variable según configuración.
* La cajera debe cerrar separando efectivo, QR y tarjeta.
* El pago al personal puede hacerse el mismo día, por lo que el cierre debe calcular montos a pagar.

Conclusión:

El sistema nuevo no solo debe migrar ventas y caja. Debe agregar un motor de liquidaciones por turno para garzones y chicas.
