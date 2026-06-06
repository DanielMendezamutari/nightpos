<?php
require_once 'fpdf/pdf.php';
require_once 'class/class.php';

$casos = [
  'PROVINCIAS'               => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarProvincias',
    'output'  => ['Listado de Provincias.pdf', 'I'],
  ],
  'DEPARTAMENTOS'                => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarDepartamentos',
    'output'  => ['Listado de Departamentos.pdf', 'I'],
  ],
  'DOCUMENTOS'             => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarDocumentos',
    'output'  => ['Listado de Tipos de Documentos.pdf', 'I'],
  ],
  'TIPOMONEDA'             => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarTiposMonedas',
    'output'  => ['Listado de Tipos de Moneda.pdf', 'I'],
  ],
  'TIPOCAMBIO'             => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarTiposCambio',
    'output'  => ['Listado de Tipos de Cambio.pdf', 'I'],
  ],
  'MEDIOSPAGOS'            => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarMediosPagos',
    'output'  => ['Listado de Medios de Pago.pdf', 'I'],
  ],
  'IMPUESTOS'              => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarImpuestos',
    'output'  => ['Listado de Impuestos.pdf', 'I'],
  ],
  'SUCURSALES'             => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarSucursales',
    'output'  => ['Listado de Sucursales.pdf', 'I'],
  ],
  'HORARIOS'              => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarHorarios',
    'output'  => ['Listado de Horarios.pdf', 'I'],
  ],
  'CATEGORIAS'              => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarCategorias',
    'output'  => ['Listado de Categorias.pdf', 'I'],
  ],
  'MEDIDAS'              => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarMedidas',
    'output'  => ['Listado de Medidas.pdf', 'I'],
  ],
  'SALSAS'              => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarSalsas',
    'output'  => ['Listado de Salsas.pdf', 'I'],
  ],
  'SALAS'              => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarSalas',
    'output'  => ['Listado de Salas.pdf', 'I'],
  ],
  'MESAS'              => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarMesas',
    'output'  => ['Listado de Mesas.pdf', 'I'],
  ],
  'USUARIOS'               => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarUsuarios',
    'output'  => ['Listado de Usuarios.pdf', 'I'],
  ],
  'LOGS'                   => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarLogs',
    'output'  => ['Listado Logs de Acceso.pdf', 'I'],
  ],
  'CLIENTES'               => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarClientes',
    'output'  => ['Listado de Clientes.pdf', 'I'],
  ],
  'PROVEEDORES'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarProveedores',
    'output'  => ['Listado de Proveedores.pdf', 'I'],
  ],
  'INGREDIENTES'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarIngredientes',
    'output'  => ['Listado de Ingredientes.pdf', 'I'],
  ],
  'INGREDIENTESMINIMO'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarIngredientesMinimo',
    'output'  => ['Listado de Ingredientes en Stock Minimo.pdf', 'I'],
  ],
  'INGREDIENTESMAXIMO'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarIngredientesMaximo',
    'output'  => ['Listado de Ingredientes en Stock Maximo.pdf', 'I'],
  ],
  'INGREDIENTESXMONEDA'      => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarIngredientesxMoneda',
    'output'  => ['Listado de Ingredientes por Moneda.pdf', 'I'],
  ],
  'KARDEXINGREDIENTES'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarKardexIngredientes',
    'output'  => ['Listado de Kardex de Ingrediente.pdf', 'I'],
  ],
  'KARDEXVALORIZADOINGREDIENTES'             => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarKardexValorizadoIngredientes',
    'output'  => ['Listado de Kardex Valorizado.pdf', 'I'],
  ],
  'KARDEXINGREDIENTESVALORIZADOXFECHAS'             => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarKardexValorizadoIngredientesxFechas',
    'output'  => ['Listado de Kardex Extras Valorizado por Fechas.pdf', 'I'],
  ],
  'INGREDIENTESVENDIDOS'             => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarIngredientesVendidos',
    'output'  => ['Listado de Ingredientes Vendidos.pdf', 'I'],
  ],
  'PRODUCTOS'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarProductos',
    'output'  => ['Listado de Productos.pdf', 'I'],
  ],
  'PRODUCTOSMINIMO'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarProductosMinimo',
    'output'  => ['Listado de Productos en Stock Minimo.pdf', 'I'],
  ],
  'PRODUCTOSMAXIMO'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarProductosMaximo',
    'output'  => ['Listado de Productos en Stock Maximo.pdf', 'I'],
  ],
  'PRODUCTOSXMONEDA'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarProductosxMoneda',
    'output'  => ['Listado de Productos por Moneda.pdf', 'I'],
  ],
  'KARDEXPRODUCTOS'             => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarKardexProductos',
    'output'  => ['Listado de Kardex de Producto.pdf', 'I'],
  ],
  'KARDEXVALORIZADOPRODUCTOS'             => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarKardexValorizadoProductos',
    'output'  => ['Listado de Kardex Valorizado.pdf', 'I'],
  ],
  'KARDEXPRODUCTOSVALORIZADOXFECHAS'             => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarKardexValorizadoProductosxFechas',
    'output'  => ['Listado de Kardex Productos Valorizado por Fechas.pdf', 'I'],
  ],
  'PRODUCTOSVENDIDOS'             => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarProductosVendidos',
    'output'  => ['Listado de Productos Vendidos.pdf', 'I'],
  ],
  'MENU'          => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarMenu',
    'output'  => ['Listado de Menu.pdf', 'I'],
  ],
  'COMBOS'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCombos',
    'output'  => ['Listado de Combos.pdf', 'I'],
  ],
  'COMBOSMINIMO'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCombosMinimo',
    'output'  => ['Listado de Combos en Stock Minimo.pdf', 'I'],
  ],
  'COMBOSMAXIMO'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCombosMaximo',
    'output'  => ['Listado de Combos en Stock Maximo.pdf', 'I'],
  ],
  'COMBOSXMONEDA'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCombosxMoneda',
    'output'  => ['Listado de Combos por Moneda.pdf', 'I'],
  ],
  'KARDEXCOMBOS'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarKardexCombos',
    'output'  => ['Listado de Kardex de Combo.pdf', 'I'],
  ],
  'KARDEXVALORIZADOCOMBOS'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarKardexValorizadoCombos',
    'output'  => ['Listado de Kardex Combos Valorizado.pdf', 'I'],
  ],
  'KARDEXCOMBOSVALORIZADOXFECHAS'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarKardexValorizadoCombosxFechas',
    'output'  => ['Listado de Kardex Combos Valorizado por Fechas.pdf', 'I'],
  ],
  'COMBOSVENDIDOS'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCombosVendidos',
    'output'  => ['Listado de Combos Vendidos.pdf', 'I'],
  ],
  'FACTURACOMPRA'          => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'FacturaCompra',
    'output'  => ['Factura de Compra.pdf', 'I'],
  ],
  'COMPRAS'                => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCompras',
    'output'  => ['Listado de Compras.pdf', 'I'],
  ],
  'CUENTASXPAGAR'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCuentasxPagar',
    'output'  => ['Listado de Cuentas por Pagar.pdf', 'I'],
  ],
  'COMPRASXFECHAS'         => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarComprasxFechas',
    'output'  => ['Listado de Compras por Fechas.pdf', 'I'],
  ],
  'COMPRASXPROVEEDOR'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarComprasxProveedor',
    'output'  => ['Listado de Compras por Proveedor.pdf', 'I'],
  ],
  'FACTURATRASPASO'        => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'FacturaTraspaso',
    'output'  => ['Factura de Traspasos.pdf', 'I'],
  ],
  'TRASPASOS'              => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarTraspasos',
    'output'  => ['Listado de Traspasos.pdf', 'I'],
  ],
  'TRASPASOSXSUCURSAL'     => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarTraspasosxSucursal',
    'output'  => ['Listado de Traspasos por Sucursal.pdf', 'I'],
  ],
  'TRASPASOSXFECHAS'       => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarTraspasosxFechas',
    'output'  => ['Listado de Traspasos por Fechas.pdf', 'I'],
  ],
  'DETALLESTRASPASOSXFECHAS'       => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarDetallesTraspasosxFechas',
    'output'  => ['Listado de Detalles Traspasos por Fechas.pdf', 'I'],
  ],
  'DETALLESTRASPASOSXSUCURSAL'       => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarDetallesTraspasosxSucursal',
    'output'  => ['Listado de Detalles Traspasos por Fechas.pdf', 'I'],
  ],
  'PRODUCTOSTRASPASOS'       => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarProductosTraspasos',
    'output'  => ['Listado de Detalles Productos Traspasos.pdf', 'I'],
  ],
  'INGREDIENTESTRASPASOS'       => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarIngredientesTraspasos',
    'output'  => ['Listado de Detalles Ingredientes Traspasos.pdf', 'I'],
  ],
  'TICKETCOTIZACION'      => [
    'medidas'        => ['P', 'mm', 'ticket3'],
    'func'           => 'TicketCotizacion',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Cotizacion.pdf', 'I'],
  ],
  'FACTURACOTIZACION'      => [
    'medidas'        => ['P', 'mm', 'A4'],
    'func'           => 'FacturaCotizacion',
    'setPrintFooter' => 'true',
    'output'         => ['Factura de Cotizacion.pdf', 'I'],
  ],
  'COTIZACIONES'           => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCotizaciones',
    'output'  => ['Listado de Cotizaciones.pdf', 'I'],
  ],
  'COTIZACIONESXFECHAS'    => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCotizacionesxFechas',
    'output'  => ['Listado de Cotizaciones.pdf', 'I'],
  ],
  'DETALLESCOTIZACIONESXFECHAS'     => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarDetallesCotizacionesxFechas',
    'output'  => ['Listado de Detalles Cotizados por Fechas.pdf', 'I'],
  ],
  'DETALLESCOTIZACIONESXVENDEDOR'  => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarDetallesCotizacionesxVendedor',
    'output'  => ['Listado de Detalles Cotizados por Vendedor.pdf', 'I'],
  ],
  'CAJAS'                  => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarCajas',
    'output'  => ['Listado de Cajas.pdf', 'I'],
  ],
  'ARQUEOS'                => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarArqueos',
    'output'  => ['Listado de Arqueos de Cajas.pdf', 'I'],
  ],
  'TICKETCIERRE'           => [
    'medidas'        => ['P', 'mm', 'cierre'],
    'func'           => 'TicketCierre',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Cierre.pdf', 'I'],
  ],
  'MOVIMIENTOS'            => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarMovimientos',
    'output'  => ['Listado de Movimientos en Caja.pdf', 'I'],
  ],
  'TICKETMOVIMIENTO'           => [
    'medidas'        => ['P', 'mm', 'movimiento'],
    'func'           => 'TicketMovimiento',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Movimiento.pdf', 'I'],
  ],
  'ARQUEOSXFECHAS'         => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarArqueosxFechas',
    'output'  => ['Listado de Arqueos por Fechas.pdf', 'I'],
  ],
  'MOVIMIENTOSXFECHAS'     => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarMovimientosxFechas',
    'output'  => ['Listado de Movimientos por Fechas.pdf', 'I'],
  ],
  'INFORMECAJASXFECHAS'     => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'InformeCajasxFechas',
    'output'  => ['Informe de Caja x Fechas.pdf', 'I'],
  ],
  'GANANCIASXFECHAS'         => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarGananciasxFechas',
    'output'  => ['Listado de Ganancias por Fechas.pdf', 'I'],
  ],
  'PEDIDOS'                 => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarPedidos',
    'output'  => ['Listado de Pedidos.pdf', 'I'],
  ],
  'PEDIDOSDIARIAS'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarPedidosDiarias',
    'output'  => ['Listado de Pedidos del Dia.pdf', 'I'],
  ],
  'PEDIDOSXCAJAS'           => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarPedidosxCajas',
    'output'  => ['Listado de Pedidos por Cajas.pdf', 'I'],
  ],
  'PEDIDOSXFECHAS'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarPedidosxFechas',
    'output'  => ['Listado de Pedidos por Fechas.pdf', 'I'],
  ],
  'PEDIDOSXFECHASENTREGA'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarPedidosxFechasEntrega',
    'output'  => ['Listado de Pedidos por Fechas de Entrega.pdf', 'I'],
  ],
  'GENERAL'                 => [
    'medidas'        => ['P', 'mm', 'ticket2'],
    'func'           => 'TicketGeneral',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Comanda/Bar/Reposteria.pdf.pdf', 'I'],
  ],
  'COMANDA'                 => [
    'medidas'        => ['P', 'mm', 'ticket2'],
    'func'           => 'TicketComanda',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Comanda.pdf', 'I'],
  ],
  'BAR'                 => [
    'medidas'        => ['P', 'mm', 'ticket2'],
    'func'           => 'TicketBar',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Bar.pdf', 'I'],
  ],
  'REPOSTERIA'                 => [
    'medidas'        => ['P', 'mm', 'ticket2'],
    'func'           => 'TicketReposteria',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Reposteria.pdf', 'I'],
  ],
  'PRECUENTA'                 => [
    'medidas'        => ['P', 'mm', 'precuenta'],
    'func'           => 'TicketPrecuenta',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Precuenta.pdf', 'I'],
  ],
  'TICKETDELIVERY'                 => [
    'medidas'        => ['P', 'mm', 'ticket3'],
    'func'           => 'TicketDelivery',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Delivery.pdf', 'I'],
  ],
  'TICKET'                 => [
    'medidas'        => ['P', 'mm', 'ticket3'],
    'func'           => 'TicketVenta',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Venta.pdf', 'I'],
  ],
  'BOLETA'                 => [
    'medidas'        => ['P', 'mm', 'ticket3'],
    'func'           => 'BoletaVenta',
    'setPrintFooter' => 'true',
    'output'         => ['Boleta de Venta.pdf', 'I'],
  ],
  'FACTURA'                => [
    'medidas'        => ['P', 'mm', 'mitad'],
    'func'           => 'FacturaVenta',
    'setPrintFooter' => 'true',
    'output'         => ['Factura de Venta.pdf', 'I'],
  ],
  'VENTAS'                 => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentas',
    'output'  => ['Listado de Ventas.pdf', 'I'],
  ],
  'VENTASDIARIAS'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentasDiarias',
    'output'  => ['Listado de Ventas del Dia.pdf', 'I'],
  ],
  'DELIVERYPENDIENTES'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentasDeliveryPendientes',
    'output'  => ['Listado de Ventas en Delivery Pendientes.pdf', 'I'],
  ],
  'DELIVERYPAGADOS'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentasDeliveryPagados',
    'output'  => ['Listado de Ventas en Delivery Pagados.pdf', 'I'],
  ],
  'DELIVERYDIARIAS'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentasDeliveryDiarias',
    'output'  => ['Listado de Ventas en Delivery del Dia.pdf', 'I'],
  ],
  'DELIVERYXREPARTIDOR'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentasDeliveryxRepartidor',
    'output'  => ['Listado de Ventas en Delivery por Repartidor.pdf', 'I'],
  ],
  'VENTASXCAJAS'           => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentasxCajas',
    'output'  => ['Listado de Ventas por Cajas.pdf', 'I'],
  ],
  'VENTASXFECHAS'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentasxFechas',
    'output'  => ['Listado de Ventas por Fechas.pdf', 'I'],
  ],
  'VENTASXCONDICIONES'          => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentasxCondiciones',
    'output'  => ['Listado de Ventas por Formas de Pago.pdf', 'I'],
  ],
  'VENTASXTIPOS'          => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarVentasxTipos',
    'output'  => ['Listado de Ventas por Tipos de Clientes.pdf', 'I'],
  ],
  'VENTASXCLIENTES'        => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarVentasxClientes',
    'output'  => ['Listado de Ventas por Clientes.pdf', 'I'],
  ],
  'DELIVERYXFECHAS'        => [
    'medidas' => ['P', 'mm', 'A4'],
    'func'    => 'TablaListarDeliveryxFechas',
    'output'  => ['Listado de Delivery por Fechas.pdf', 'I'],
  ],
  'DETALLESVENTASXCAJAS'          => [
    'medidas'        => ['L', 'mm', 'LEGAL'],
    'func'           => 'TablaListarDetallesVentasxCajas',
    'output'         => ['Listado de Detalles Ventas por Cajas.pdf', 'I'],
  ],
  'DETALLESVENTASXFECHAS'          => [
    'medidas'        => ['L', 'mm', 'LEGAL'],
    'func'           => 'TablaListarDetallesVentasxFechas',
    'output'         => ['Listado de Detalles Ventas por Fechas.pdf', 'I'],
  ],
  'DETALLESVENTASXVENDEDOR'          => [
    'medidas'        => ['L', 'mm', 'LEGAL'],
    'func'           => 'TablaListarDetallesVentasxVendedor',
    'output'         => ['Listado de Detalles Ventas por Vendedor.pdf', 'I'],
  ],
  'COMISIONXVENTAS'        => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarComisionxVentas',
    'output'  => ['Listado de Comisión por Ventas.pdf', 'I'],
  ],
  'INFORMEVENTASXFECHAS'          => [
    'medidas'        => ['P', 'mm', 'A4'],
    'func'           => 'InformeVentasxFechas',
    'output'         => ['Informe de Ventas x Fechas.pdf', 'I'],
  ],
  'GANANCIASVENTASXFECHAS'          => [
    'medidas'        => ['L', 'mm', 'LEGAL'],
    'func'           => 'TablaListarGananciasVentasxFechas',
    'setPrintFooter' => 'true',
    'output'         => ['Listado de Ganancias de Ventas por Fechas.pdf', 'I'],
  ],
  'TICKETCREDITO'          => [
    'medidas'        => ['P', 'mm', 'ticket3'],
    'func'           => 'TicketCredito',
    'setPrintFooter' => 'true',
    'output'         => ['Ticket de Abonos.pdf', 'I'],
  ],
  'CREDITOS'               => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCreditos',
    'output'  => ['Listado de Creditos.pdf', 'I'],
  ],
  'ABONOSCREDITOSXCAJAS'        => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarAbonosCreditosxCajas',
    'output'  => ['Listado de Abonos Ventas a Creditos por Cajas.pdf', 'I'],
  ],
  'CREDITOSXFECHAS'        => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarCreditosxFechas',
    'output'  => ['Listado Ventas a Creditos por Fechas.pdf', 'I'],
  ],
  'CREDITOSXCLIENTES'      => [
    'medidas' => ['L', 'mm', 'LETTER'],
    'func'    => 'TablaListarCreditosxClientes',
    'output'  => ['Listado Ventas a Creditos por Clientes.pdf', 'I'],
  ],
  'DETALLESCREDITOSXCLIENTES'      => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarDetallesCreditosxClientes',
    'output'  => ['Listado de Detalles Creditos por Clientes.pdf', 'I'],
  ],
  'NOTACREDITO'            => [
    'medidas'        => ['P', 'mm', 'mitad'],
    'func'           => 'NotaCredito',
    'setPrintFooter' => 'true',
    'output'         => ['Nota de Credito.pdf', 'I'],
  ],
  'NOTAS'           => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarNotas',
    'output'  => ['Listado de Notas de Creditos.pdf', 'I'],
  ],
  'NOTASXCAJAS'   => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarNotasxCajas',
    'output'  => ['Listado de Notas de Creditos x Cajas.pdf', 'I'],
  ],
  'NOTASXFECHAS'    => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarNotasxFechas',
    'output'  => ['Listado de Notas de Creditos x Fechas.pdf', 'I'],
  ],
  'NOTASXCLIENTE'   => [
    'medidas' => ['L', 'mm', 'LEGAL'],
    'func'    => 'TablaListarNotasxClientes',
    'output'  => ['Listado de Notas de Creditos x Clientes.pdf', 'I'],
  ],
];

$tipo = decrypt($_GET['tipo']);
if (!in_array($tipo, ['GENERAL', 'COMANDA', 'BAR', 'REPOSTERIA', 'PRECUENTA', 'TICKET', 'BOLETA', 'TICKETCREDITO'])) {
  //validarAccesos([]) or die;
}

ob_start();
$caso_data = $casos[$tipo];
$pdf       = new PDF(
  $caso_data['medidas'][0],
  $caso_data['medidas'][1],
  $caso_data['medidas'][2]
);
if (in_array($tipo, ['GENERAL', 'COMANDA', 'BAR', 'REPOSTERIA', 'PRECUENTA', 'TICKET', 'BOLETA', 'TICKETCREDITO'])) {
  $pdf->AutoPrint();
} 
$pdf->AddPage();
$pdf->SetAuthor("Ing. Ruben Chirinos");
$pdf->SetCreator("FPDF Y PHP");
$pdf->{$caso_data['func']}();
$pdf->Output($caso_data['output'][0], $caso_data['output'][1]);
ob_end_flush();
?>