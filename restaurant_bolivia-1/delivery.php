<?php
require_once("class/class.php"); 
if(isset($_SESSION['acceso'])) { 
if ($_SESSION['acceso'] == "administradorS" || $_SESSION["acceso"]=="secretaria" || $_SESSION["acceso"]=="cajero") {

$tra = new Login();
$ses = $tra->ExpiraSession();  

$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "Impuesto" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']);
$simbolo = ($_SESSION["acceso"] == "administradorG" ? "" : "<strong>".$_SESSION["simbolo"]."</strong>");

$arqueo = new Login();
$arqueo = $arqueo->ArqueoCajaPorUsuario();

if(isset($_POST["proceso"]) and $_POST["proceso"]=="nuevopedido")
{
$reg = $tra->RegistrarDelivery();
exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="nuevocliente")
{
$reg = $tra->RegistrarClientes();
exit;
}        
?>
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Ing. Ruben Chirinos">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title></title>

    <!-- Menu CSS -->
    <link href="assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.css" rel="stylesheet">
    <!-- toast CSS -->
    <link href="assets/plugins/bower_components/toast-master/css/jquery.toast.css" rel="stylesheet">
    <!-- Datatables CSS -->
    <link href="assets/plugins/datatables/dataTables.bootstrap4.css" rel="stylesheet">
    <!-- Sweet-Alert -->
    <link rel="stylesheet" href="assets/css/sweetalert.css">
    <!-- animation CSS -->
    <link href="assets/css/animate.css" rel="stylesheet">
    <!-- needed css -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- color CSS -->
    <link href="assets/css/default.css" id="theme" rel="stylesheet">
    <!--Bootstrap Horizontal CSS -->
    <link href="assets/css/bootstrap-horizon.css" rel="stylesheet">
    <link href="assets/css/elements/tooltip.css" rel="stylesheet" type="text/css" />
    <!-- color alert -->
    <link rel="stylesheet" type="text/css" href="assets/css/alert.css">
    <!--<link href="assets/css/style-light.css" rel="stylesheet">
    Scrolling-tabs CSS
    <link rel="stylesheet" href="assets/css/jquery.scrolling-tabs.css">
    <link rel="stylesheet" href="assets/css/st-demo.css"> -->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

</head>

<body onLoad="muestraReloj()" class="fix-header">
    
   <!-- ============================================================== -->
    <!-- Preloader - style you can find in spinners.css -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
        </svg>
    </div>

    <!-- ============================================================== -->
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-boxed-layout="full" data-header-position="fixed" data-sidebar-position="fixed" class="mini-sidebar"> 

<!--############################## MODAL PARA AGREGAR DESCUENTO EN DETALLE ##############################-->
<!-- sample modal content -->
<div id="myModalDescuento" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-align-justify"></i> Descuento en Detalle</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
                </div>

        <form class="form form-material" method="post" action="#" name="agregadescuento" id="agregadescuento">
                
            <div class="modal-body">

            <div id="agrega_detalle_descuento"></div><!-- detalle observacion -->

            </div>

        </form>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA AGREGAR DESCUENTO EN DETALLE ##############################-->

<!--############################## MODAL PARA AGREGAR OBSERVACIONES EN DETALLE ##############################-->
<!-- sample modal content -->
<div id="myModalObservacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-align-justify"></i> Observación en Detalle</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
                </div>

        <form class="form form-material" method="post" action="#" name="agregaobservaciones" id="agregaobservaciones">
                
            <div class="modal-body">

            <div id="agrega_detalle_observacion"></div><!-- detalle observacion -->

            </div>

        </form>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA AGREGAR OBSERVACIONES EN DETALLE ##############################-->

<!--############################## MODAL PARA AGREGAR SALSAS EN DETALLE ##############################-->
<!-- sample modal content -->
<div id="myModalSalsa" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-align-justify"></i> Salsas en Detalle</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
                </div>

        <form class="form form-material" method="post" action="#" name="agregasalsas" id="agregasalsas">
                
        <div class="modal-body">

            <div id="agrega_detalle_salsa"></div><!-- detalle salsas -->

        </div>

        </form>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA AGREGAR SALSAS EN DETALLE ##############################-->

<!--############################## MODAL PARA MOSTRAR MENU ##############################-->
<!-- sample modal content -->
<div id="myModalMenu" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Menú</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
                            
            <div class="modal-body">

            <div id="muestra_menu"></div>
            
            </div>

            <div class="modal-footer">
<a href="reportepdf?&tipo=<?php echo encrypt("MENU"); ?>" target="_blank" rel="noopener noreferrer"><button id="print" class="btn btn-warning waves-light" type="button"><span><i class="fa fa-print"></i> Imprimir</span></button></a>
<button class="btn btn-dark" type="reset" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cerrar</button>
            </div>

    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal --> 
<!--############################## MODAL PARA MOSTRAR MENU ##############################-->


<!--############################## MODAL PARA MOSTRAR PEDIDOS EN COCINA ##############################-->
<!-- sample modal content -->
<div id="myModalPedidos" class="modal bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Detalles de Pedidos</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <form class="form form-material" name="detalles" id="detalles" action="#">
                
               <div class="modal-body">

                    <div id="detallescocina"></div>

               </div>

            <div class="modal-footer">
<button class="btn btn-dark" type="reset" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cerrar</button>
            </div>
        </form>

    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal --> 
<!--############################## MODAL PARA MOSTRAR PEDIDOS EN COCINA ##############################-->

<!--############################## MODAL PARA REGISTRO DE NUEVO CLIENTE ##############################-->
<!-- sample modal content -->
<div id="myModalCliente" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-save"></i> Nuevo Cliente</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <form class="form form-material" method="post" action="#" name="savecliente" id="savecliente"> 

            <div id="save">
                <!-- error will be shown here ! -->
            </div>
                
        <div class="modal-body">

        <div class="row">
            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Tipo de Cliente: <span class="symbol required"></span></label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <select style="color:#000;font-weight:bold;" name="tipocliente" id="tipocliente" class="form-control" onChange="CargaTipoCliente(this.form.tipocliente.value);" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="NATURAL">NATURAL</option>
                    <option value="JURIDICO">JURIDICO</option>
                    </select> 
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Tipo de Documento: </label>
                    <i class="fa fa-bars form-control-feedback"></i> 
                    <select style="color:#000;font-weight:bold;" name="documcliente" id="documcliente" class='form-control' required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <?php
                    $doc = new Login();
                    $doc = $doc->ListarDocumentos();
                    if($doc==""){ 
                        echo "";
                    } else {
                    for($i=0;$i<sizeof($doc);$i++){ ?>
                    <option value="<?php echo $doc[$i]['coddocumento'] ?>"><?php echo $doc[$i]['documento']; ?></option>
                    <?php } } ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Documento: <span class="symbol required"></span></label>
                    <input type="hidden" name="proceso" id="proceso" value="nuevocliente"/>
                    <input type="hidden" name="codsucursal" id="codsucursal" value="<?php echo encrypt($_SESSION['codsucursal']); ?>">
                    <input type="hidden" name="formulario" id="formulario" value="delivery"/>
                    <input type="text" class="form-control" name="dnicliente" id="dnicliente" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Documento" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Nombre de Cliente: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="nomcliente" id="nomcliente" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nombre de Cliente" disabled="" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Razón Social: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="razoncliente" id="razoncliente" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Razón Social" disabled="" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Giro de Cliente: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="girocliente" id="girocliente" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Giro de Cliente" disabled="" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Teléfono: </label>
                    <input type="text" class="form-control phone-inputmask" name="tlfcliente" id="tlfcliente" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Teléfono" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-phone form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Correo de Cliente: </label>
                    <input type="text" class="form-control" name="emailcliente" id="emailcliente" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Correo Electronico" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-envelope-o form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Provincia: </label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <select style="color:#000;font-weight:bold;" name="id_provincia" id="id_provincia" onChange="CargaDepartamentos(this.form.id_provincia.value);" class='form-control' required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <?php
                    $pro = new Login();
                    $pro = $pro->ListarProvincias();
                    if($pro==""){ 
                        echo "";
                    } else {
                    for($i=0;$i<sizeof($pro);$i++){ ?>
                    <option value="<?php echo $pro[$i]['id_provincia'] ?>"><?php echo $pro[$i]['provincia'] ?></option>        
                    <?php } } ?>
                    </select> 
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Departamentos: </label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <select style="color:#000;font-weight:bold;" class="form-control" id="id_departamento" name="id_departamento" required="" aria-required="true">
                    <option value=""> -- SIN RESULTADOS -- </option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Dirección Domiciliaria: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="direccliente" id="direccliente" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Dirección Domiciliaria" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-map-marker form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Limite de Crédito: <span class="symbol required"></span></label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="limitecredito" id="limitecredito" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Limite de Crédito" autocomplete="off" value="0.00" required="" aria-required="true"/>  
                    <i class="fa fa-usd form-control-feedback"></i>
                </div>
            </div>
        </div>
    </div>

        <div class="modal-footer">
            <div class="col-md-6">
                <button type="submit" name="btn-cliente" id="btn-cliente" class="btn btn-warning btn-lg btn-block waves-effect waves-light"><span class="fa fa-save"></span> Guardar</button>
            </div>
            <div class="col-md-6">
                <button type="button" onclick="ResetCliente2()" class="btn btn-dark btn-lg btn-block waves-effect waves-light" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cancelar</button>
            </div>
        </div>

        </form>

    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal --> 
<!--############################## MODAL PARA REGISTRO DE NUEVO CLIENTE ##############################-->

        <!-- INICIO DE MENU -->
        <?php include('menu.php'); ?>
        <!-- FIN DE MENU -->
   

        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <div class="page-breadcrumb border-bottom">
                <div class="row">
                    <div class="col-lg-3 col-md-4 col-xs-12 align-self-center">
                <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Gestión de Delivery</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Mostrador</li>
                                <li class="breadcrumb-item active" aria-current="page">Delivery</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="page-content container-fluid">
                <!-- ============================================================== -->
                <!-- Start Page Content -->
                <!-- ============================================================== -->

    <!-- Row -->
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div id="notificaciones"></div>
        </div>
    </div>

<form class="form form-material" method="post" action="#" name="savedelivery" id="savedelivery">


<!--############################## MODAL PARA CIERRE DE DELIVERY ##############################-->
<!-- sample modal content -->
<div id="myModalPago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Cierre de Venta</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <div class="modal-body">

        <div class="row">
            <div class="col-md-4">
            	<h4 class="mb-0 font-light">Total a Pagar</h4>
            	<h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextImporte" name="TextImporte"></label></h4>
            </div>

            <div class="col-md-4">
            	<h4 class="mb-0 font-light">Total Recibido</h4>
            	<h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextPagado" name="TextPagado"></label></h4>
            </div>

            <div class="col-md-4">
            	<h4 class="mb-0 font-light">Total Cambio</h4>
            	<h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextCambio" name="TextCambio">0.00</label></h4>
            </div>
        </div>
             
        <div class="row">
        	<div class="col-md-8">
        		<h4 class="mb-0 font-light">Nombre del Cliente</h4>
        		<h4 class="mb-0 font-medium"> <label id="TextCliente" name="TextCliente">CONSUMIDOR FINAL</label></h4>
        	</div>

        	<div class="col-md-4">
        		<h4 class="mb-0 font-light">Limite de Crédito</h4>
        		<h4 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextCredito" name="TextCredito">0.00</label></h4>
        	</div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">Tipo de Documento: <span class="symbol required"></span></label><br>
                    <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="ticket" name="tipodocumento" value="TICKET" checked="checked">
                    <label class="custom-control-label" for="ticket">TICKET</label>
                    </div>
                </div>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="boleta" name="tipodocumento" value="BOLETA">
                    <label class="custom-control-label" for="boleta">BOLETA</label>
                    </div>
                </div><br>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="factura" name="tipodocumento" value="FACTURA">
                    <label class="custom-control-label" for="factura">FACTURA</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
               <div class="form-group">
               <label class="control-label">Condición de Pago: <span class="symbol required"></span></label>
               <div class="custom-control custom-radio">
               <input type="radio" class="custom-control-input" id="contado" name="tipopago" value="CONTADO" onClick="CargaCondicionesPagos()" checked="checked">
               <label class="custom-control-label" for="contado">CONTADO</label>
               </div>

               <div class="custom-control custom-radio">
               <input type="radio" class="custom-control-input" id="credito" name="tipopago" value="CREDITO" onClick="CargaCondicionesPagos()">
               <label class="custom-control-label" for="credito">CRÉDITO</label>
               </div>
            </div>
            </div>

            <div class="col-md-4"> 
                <div class="form-group has-feedback"> 
                <label class="control-label">Costo Delivery: <span class="symbol required"></span></label>
                <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montodelivery" id="montodelivery" onKeyUp="DevolucionDelivery();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Costo Delivery" value="0.00" disabled="" required="" aria-required="true"> 
                <i class="fa fa-dollar form-control-feedback"></i>
                </div> 
            </div>
        </div>


        <div id="muestra_condiciones"><!-- IF CONDICION PAGO -->

      <div class="row">

        <!-- .col -->
        <div class="col-md-6">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 1</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 1: <span class="symbol required"></span></label>
              <i class="fa fa-bars form-control-feedback"></i>
              <input type="hidden" name="montopropina" id="montopropina" value="0.00">
              <select style="color:#000;font-weight:bold;" name="formapago" id="formapago" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO" selected="">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 1: <span class="symbol required"></span></label>
              <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionDelivery();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div> 
          </div>
        </div>

        </div>
        <!-- /.col -->

        <!-- .col -->
        <div class="col-md-6">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 2</h4><hr>
            
        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Forma de Pago Nº 2: </label>
              <i class="fa fa-bars form-control-feedback"></i>
              <select style="color:#000;font-weight:bold;" name="formapago2" id="formapago2" onchange="FormasPagos2();" class="form-control" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="EFECTIVO">EFECTIVO</option>
                <option value="CHEQUE">CHEQUE</option>
                <option value="TARJETA DE CREDITO">TARJETA DE CRÉDITO</option>
                <option value="TARJETA DE DEBITO">TARJETA DE DÉBITO</option>
                <option value="TARJETA PREPAGO">TARJETA PREPAGO</option>
                <option value="TRANSFERENCIA">TRANSFERENCIA</option>
                <option value="DINERO ELECTRONICO">DINERO ELECTRÓNICO</option>
                <option value="CUPON">CUPÓN</option>
                <option value="OTROS">OTROS</option>
              </select>
            </div> 
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
              <label class="control-label">Monto de Pago Nº 2: </label>
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado2" id="montopagado2" onKeyUp="DevolucionDelivery();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="0.00" disabled="" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div>  
          </div>
        </div>

        </div>
        <!-- /.col -->

      </div>

    </div><!-- END CONDICION PAGO -->

    <div class="row">
        <div class="col-md-12"> 
          <div class="form-group has-feedback2"> 
            <label class="control-label">Observaciones: </label> 
            <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"></textarea>
            <i class="fa fa-comment-o form-control-feedback2"></i> 
          </div> 
        </div>
     </div> 

        <div class="row">
            <div class="col-md-6">
                <span id="submit_guardar"><button type="submit" name="btn-cerrar" id="btn-cerrar" class="btn btn-warning btn-lg btn-block waves-effect waves-light"><span class="fa fa-print"></span> Facturar e Imprimir</button></span>
            </div>
            <div class="col-md-6">
                <button type="reset" class="btn btn-dark btn-lg btn-block waves-effect waves-light" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cancelar</button>
            </div>
        </div>
    
        </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA CIERRE DE DELIVERY ##############################-->   

<!--############################## MODAL PARA MOSTRAR REPARTIDORES ##############################-->
<!-- sample modal content -->
<div id="myModalRepartidores" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Repartidores</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
                            
            <div class="modal-body">

            <div id="muestra_repartidores"></div>
            
            </div>

            <div class="modal-footer">
                <button class="btn btn-dark" type="reset" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cerrar</button>
            </div>


        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA MOSTRAR REPARTIDORES ##############################-->

<!-- Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Gestión de Delivery</h4>
            </div>

            <div id="save">
            <!-- error will be shown here ! -->
            </div>
            
            <div class="form-body">

    <div class="card-body">

    <div class="row">

        <!-- .col -->
        <div class="col-md-6">
        
        <h3 class="card-subtitle m-0 text-dark"><i class="font-20 mdi mdi-cart-plus"></i> Detalle de Ventas</h3><hr>

        <?php if($arqueo==""){ ?>

        <div class='alert alert-danger'>
            <center><span class='fa fa-info-circle'></span> POR FAVOR DEBE DE REALIZAR EL ARQUEO DE CAJA ASIGNADA PARA PROCESAR VENTAS <a href="arqueos" class="text-dark alert-link">REALIZAR ARQUEO</a></div></center>

        <?php } else { ?>

        <input type="hidden" name="idproducto" id="idproducto">
        <input type="hidden" name="codproducto" id="codproducto">
        <input type="hidden" name="producto" id="producto">
        <input type="hidden" name="codcategoria" id="codcategoria">
        <input type="hidden" name="categorias" id="categorias">
        <input type="hidden" name="precioventa" id="precioventa">
        <input type="hidden" name="preciocompra" id="preciocompra"> 
        <input type="hidden" name="precioconiva" id="precioconiva">
        <input type="hidden" name="observacion" id="observacion">
        <input type="hidden" name="salsa" id="salsa">
        <input type="hidden" name="ivaproducto" id="ivaproducto">
        <input type="hidden" name="descproducto" id="descproducto">
        <input type="hidden" name="preparado" id="preparado">
        <input type="hidden" name="tipo" id="tipo">
        <input type="hidden" name="cantidad" id="cantidad" value="1">
        <input type="hidden" name="existencia" id="existencia">
        <input type="hidden" name="proceso" id="proceso" value="nuevopedido"/>
        <input type="hidden" name="codpedido" id="codpedido" value="<?php echo encrypt("0"); ?>">
        <input type="hidden" name="codventa" id="codventa" value="<?php echo encrypt("0"); ?>">
        <input type="hidden" name="codsucursal" id="codsucursal" value="<?php echo encrypt($_SESSION['codsucursal']); ?>">

        <div class="row">
            <div class="col-md-8">
                <label class="control-label">Búsqueda de Cliente: </label>
                <div class="input-group">
                <div class="input-group-append">
                    <div class="btn-group" data-bs-toggle="buttons">
                <button type="button" class="btn btn-success waves-effect waves-light" data-placement="left" title="Nuevo Cliente" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCliente" data-backdrop="static" data-keyboard="false"><i class="fa fa-user-plus"></i></button>
                </div></div>
                <input type="hidden" name="codcliente" id="codcliente" value="0">
                <input type="hidden" name="nrodocumento" id="nrodocumento" value="0">
                <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="busqueda" id="busqueda" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Criterio para la Búsqueda del Cliente" value="CONSUMIDOR FINAL" autocomplete="off"/>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                   <label class="control-label">Tipo de Pedido: <span class="symbol required"></span></label><br>
                   <div class="custom-control custom-radio">
                   <input type="radio" class="custom-control-input" id="evento1" name="tipopedido" value="INTERNO" checked="checked" onclick="TipoPedido('this.form.tipopedido.value')">
                   <label class="custom-control-label" for="evento1">EN ESTABLECIMIENTO</label>
                   </div>
                   <div class="custom-control custom-radio">
                   <input type="radio" class="custom-control-input" id="evento2" name="tipopedido" value="EXTERNO" onclick="TipoPedido('this.form.tipopedido.value'); CargarRepartidores('this.form.tipopedido.value');" data-placement="left" title="Ver Menu" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalRepartidores">
                   <label class="custom-control-label" for="evento2">A DOMICILIO</label>
                   </div>
                </div>
            </div>
        </div>

    <div id="favoritos" style="display:none !important;"></div>

        <div class="table-responsive m-t-5 scroll-delivery">
        <table id="carrito" class="table table-hover" style="margin-bottom: -20px !important; margin-top: 0px !important;" id="nvo-ped-det">
                <thead>
                </thead>
                <tbody>
                    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
                        <td class="text-center" colspan=5><h4>NO HAY DETALLES AGREGADOS</h4></td>
                    </tr>
                </tbody>
            </table> 
        </div>

        <hr>

        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group has-feedback2"> 
                    <label class="control-label">Observaciones: </label> 
                    <textarea class="form-control" type="text" name="descripciones" id="descripciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="2"></textarea>
                    <i class="fa fa-comment-o form-control-feedback2"></i> 
                </div> 
            </div>
        </div>

        <div class="table-responsive">
           <table id="carritototal" width="100%">
                <tr>
                   <td class="text-left"><label>TOTAL DE ITEMS:</label></td>
                   <td class="text-right"><label id="lblitems" name="lblitems">0.00</label></td>
                </tr>
                <tr>
                   <td class="text-left"><label>DESCONTADO:</label></td>
                   <td class="text-right"><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado">0.00</label></td>
                </tr>
                <tr>
                   <td class="text-left"><label>SUBTOTAL:</label></td>
                   <td class="text-right"><?php echo $simbolo; ?><label id="labelsubtotal" name="labelsubtotal">0.00</label></td>
                </tr>
                <tr>
                   <td class="text-left"><label><?php echo $impuesto; ?> (<?php echo number_format($valor, 2, '.', ''); ?>%):</label></td>
                   <td class="text-right"><?php echo $simbolo; ?><label id="lbliva" name="lbliva">0.00</label></td>
                </tr>
                <tr>
                    <td class="text-left"><label>DESC <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" style="border-radius:4px;height:25px;width:50px;" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($_SESSION['descsucursal'], 2, '.', ''); ?>"> %:</label></td>
                    <td class="text-right"><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento">0.00</label></td>
                </tr>
                <tr>
                    <td class="text-left"><label>TOTAL A CONFIRMAR:</label></td>
                    <td class="text-right"><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal">0.00</label></td>
                    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="0.00"/>
                    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="0.00"/>
                    <input type="hidden" name="iva" id="iva" value="<?php echo number_format($valor, 2, '.', ''); ?>">
                    <input type="hidden" name="txtIva" id="txtIva" value="0.00"/>
                    <input type="hidden" name="txtdescontado" id="txtdescontado" value="0.00"/>
                    <input type="hidden" name="txtDescuento" id="txtDescuento" value="0.00"/>
                    <input type="hidden" name="txtTotal" id="txtTotal" value="0.00"/>
                    <input type="hidden" name="txtImporte" id="txtImporte" value="0.00"/>
                    <input type="hidden" name="txtAgregado" id="txtAgregado" value="0.00"/>
                    <input type="hidden" name="txtTotalCompra" id="txtTotalCompra" value="0.00"/>
                </tr>
            </table>
        </div>

        <div class="row">
            <div class="col-md-4">
                <button type="button" class="btn btn-info btn-lg btn-block waves-effect waves-light" onClick="RecargaPedidos('<?php echo encrypt("DELIVERY"); ?>');" data-placement="left" title="Ver Pedidos" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalPedidos" data-backdrop="static" data-keyboard="false"><i class="fa fa-cutlery"></i> Ver Cocina</button>
            </div>

            <div class="col-md-4">
                <button type="button" class="btn btn-dark btn-lg btn-block" id="limpiar"><span class="fa fa-trash-o"></span> Limpiar</button>
            </div>

            <div class="col-md-4">
                <button id="buttonpago" type="button" class="btn btn-warning btn-lg btn-block waves-effect waves-light" data-placement="left" title="Cobrar Venta" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalPago" disabled="" data-backdrop="static" data-keyboard="false"><span class="fa fa-save"></span> Pagar</button>
            </div>
        </div>

        <?php } ?>

        </div>
        <!-- /.col -->
        
        <!-- .col -->  
        <div class="col-md-6">

        <span class="pull-right">

        <button type="button" class="btn btn-primary waves-effect waves-light" style="cursor: pointer;" data-placement="left" title="Ver Menu" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMenu" onclick="CargarMenu();"><span class="fa fa-clipboard"></span> </button>

        <button type="button" class="btn btn-success waves-effect waves-light" style="cursor: pointer;" title="Productos" onClick="MostrarProductos();"><span class="fa fa-cubes"></span> </button>
        
        <button type="button" class="btn btn-info waves-effect waves-light" style="cursor: pointer;" title="Combos" onClick="MostrarCombos();"><span class="fa fa-archive"></span> </button>
        
        <button type="button" class="btn btn-danger waves-effect waves-light" style="cursor: pointer;" title="Extras" onClick="MostrarExtras();"><span class="fa fa-folder-open"></span> </button>
        
        </span>

            <div id="loading_productos"></div>

        </div>
       <!-- /.col -->
                                   
    </div>

                </div>
            </div>

        </div>
    </div>
</div>
<!-- End Row -->

        </form> 
                <!-- ============================================================== -->
                <!-- End PAge Content -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Right sidebar -->
                <!-- ============================================================== -->
                <!-- .right-sidebar -->
                <!-- ============================================================== -->
                <!-- End Right sidebar -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            <footer class="footer text-center">
                <i class="fa fa-copyright"></i> <span class="current-year"></span>.
            </footer>
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- -------------------------------------------------------------- -->
    <!-- End Wrapper -->
    <!-- -------------------------------------------------------------- -->

    <!-- -------------------------------------------------------------- -->
    <!-- customizer Panel -->
    <!-- -------------------------------------------------------------- -->
    <aside class="customizer">
      <a href="javascript:void(0)" class="service-panel-toggle" title="Favoritos"
        ><i class="fa fa-spin fa-star"></i
      ></a>
      <div class="customizer-body">
        <!-- Nav tabs -->
        <ul class="nav customizer-tab" role="tablist">
            <li class="nav-item"> <a class="nav-link text-success active" data-toggle="tab" href="#pills-productos" role="tab" onclick="MuestraProductosFavoritos();"><span class="hidden-sm-up"><i class="font-24 mdi mdi-cube fs-6"></i></span></a> </li>
            <li class="nav-item"> <a class="nav-link text-info" data-toggle="tab" href="#pills-combos" role="tab" onclick="MuestraCombosFavoritos();"><span class="hidden-sm-up"><i class="font-24 mdi mdi-archive fs-6"></i></span></a> </li>
            <li class="nav-item"> <a class="nav-link text-danger" data-toggle="tab" href="#pills-extras" role="tab" onclick="MuestraExtrasFavoritos();"><span class="hidden-sm-up"><i class="font-24 mdi mdi-book-multiple fs-6"></i></span></a> </li>
        </ul>
        <!-- Tab panes -->
        <div class="tab-content" id="pills-tabContent">
           <!-- Tab 1 -->
           <div class="tab-pane fade p-3 show active" id="pills-productos" role="tabpanel" aria-labelledby="pills-home-tab">
            
            <hr>

            <div class="table-responsive">
                <div id="div2"><div id="productos_favoritos"><table class="table">
                    <thead class="bg-danger text-white">
                    <tr>
                    <th colspan="2"><i class="fa fa-tasks"></i> Productos Favoritos</th>
                    </tr>

                <?php
                $pfavoritos = new Login();
                $pfavoritos = $pfavoritos->ListarProductosFavoritos();
                $a=1;

                if($pfavoritos==""){
                echo "";   
                } else {
                for($i=0;$i<sizeof($pfavoritos);$i++){?>      
                    </thead>
                    <tbody>
                      <tr class="table2">
                        <td class="alert-link"><span style="cursor:pointer;" OnClick="DoAction('<?php echo $pfavoritos[$i]['idproducto']; ?>','<?php echo $pfavoritos[$i]['codproducto']; ?>','<?php echo $pfavoritos[$i]['producto']; ?>','<?php echo $pfavoritos[$i]['codcategoria']; ?>','<?php echo $pfavoritos[$i]['nomcategoria']; ?>','<?php echo number_format($pfavoritos[$i]['preciocompra'], 2, '.', ''); ?>','<?php echo number_format($pfavoritos[$i]['precioventa'], 2, '.', ''); ?>','<?php echo number_format($pfavoritos[$i]['descproducto'], 2, '.', ''); ?>','<?php echo $ivaproducto = ( $pfavoritos[$i]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', '') : "(E)"); ?>','<?php echo number_format($pfavoritos[$i]['existencia'], 2, '.', ''); ?>','<?php echo $precioconiva = ( $pfavoritos[$i]['ivaproducto'] == 'SI' ? number_format($pfavoritos[$i]['precioventa'], 2, '.', '') : "0.00"); ?>','<?php echo "1"; ?>','<?php echo ", "; ?>','<?php echo ", "; ?>','<?php echo $pfavoritos[$i]['preparado']; ?>');"><?php echo $pfavoritos[$i]['producto']; ?></span></td>
                    </tr>
                <?php } } ?>
                    </tbody>
                </table></div></div>
            </div>

          </div>
          <!-- End Tab 1 -->

          <!-- Tab 2 -->
          <div class="tab-pane fade p-3 show" id="pills-combos" role="tabpanel" aria-labelledby="pills-profile-tab">
            
          <hr>

          <div class="table-responsive">
                <div id="div2"><div id="combos_favoritos"><table class="table">
                    <thead class="bg-danger text-white">
                    <tr>
                    <th colspan="2"><i class="fa fa-tasks"></i> Combos Favoritos</th>
                    </tr>

                <?php
                $cfavoritos = new Login();
                $cfavoritos = $cfavoritos->ListarCombosFavoritos();
                $a=1;

                if($cfavoritos==""){
                echo "";   
                } else {
                for($i=0;$i<sizeof($cfavoritos);$i++){?>      
                    </thead>
                    <tbody>
                      <tr class="table2">
                        <td class="alert-link"><span style="cursor:pointer;" OnClick="DoAction('<?php echo $cfavoritos[$i]['idcombo']; ?>','<?php echo $cfavoritos[$i]['codcombo']; ?>','<?php echo $cfavoritos[$i]['nomcombo']; ?>','<?php echo "********"; ?>','<?php echo "********"; ?>','<?php echo number_format($cfavoritos[$i]['preciocompra'], 2, '.', ''); ?>','<?php echo number_format($cfavoritos[$i]['precioventa'], 2, '.', ''); ?>','<?php echo number_format($cfavoritos[$i]['desccombo'], 2, '.', ''); ?>','<?php echo $ivaproducto = ( $cfavoritos[$i]['ivacombo'] == 'SI' ? number_format($valor, 2, '.', '') : "(E)"); ?>','<?php echo number_format($cfavoritos[$i]['existencia'], 2, '.', ''); ?>','<?php echo $precioconiva = ( $cfavoritos[$i]['ivacombo'] == 'SI' ? number_format($cfavoritos[$i]['precioventa'], 2, '.', '') : "0.00"); ?>','<?php echo "2"; ?>','<?php echo ", "; ?>','<?php echo ", "; ?>','<?php echo $cfavoritos[$i]['preparado']; ?>');"><?php echo $cfavoritos[$i]['nomcombo']; ?></span></td>
                    </tr>
                <?php } } ?>
                    </tbody>
                </table></div></div>
            </div>
            

          </div>
          <!-- End Tab 2 -->
          <!-- Tab 3 -->
          <div class="tab-pane fade p-3 show" id="pills-extras" role="tabpanel" aria-labelledby="pills-contact-tab">
            
          <hr>

          <div class="table-responsive">
                <div id="div2"><div id="extras_favoritos"><table class="table">
                    <thead class="bg-danger text-white">
                    <tr>
                    <th colspan="2"><i class="fa fa-tasks"></i> Extras Favoritos</th>
                    </tr>

                <?php
                $efavoritos = new Login();
                $efavoritos = $efavoritos->ListarIngredientesFavoritos();
                $a=1;

                if($efavoritos==""){
                echo "";   
                } else {
                for($i=0;$i<sizeof($efavoritos);$i++){?>      
                    </thead>
                    <tbody>
                      <tr class="table2">
                        <td class="alert-link"><span style="cursor:pointer;" OnClick="DoAction('<?php echo $efavoritos[$i]['idingrediente']; ?>','<?php echo $efavoritos[$i]['codingrediente']; ?>','<?php echo $efavoritos[$i]['nomingrediente']; ?>','<?php echo $efavoritos[$i]['codmedida']; ?>','<?php echo $efavoritos[$i]['nommedida']; ?>','<?php echo number_format($efavoritos[$i]['preciocompra'], 2, '.', ''); ?>','<?php echo number_format($efavoritos[$i]['precioventa'], 2, '.', ''); ?>','<?php echo number_format($efavoritos[$i]['descingrediente'], 2, '.', ''); ?>','<?php echo $ivaproducto = ( $efavoritos[$i]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', '') : "(E)"); ?>','<?php echo number_format($efavoritos[$i]['cantingrediente'], 2, '.', ''); ?>','<?php echo $precioconiva = ( $efavoritos[$i]['ivaingrediente'] == 'SI' ? number_format($efavoritos[$i]['precioventa'], 2, '.', '') : "0.00"); ?>','<?php echo "3"; ?>','<?php echo ", "; ?>','<?php echo ", "; ?>','<?php echo $efavoritos[$i]['preparado']; ?>');"><?php echo $efavoritos[$i]['nomingrediente']; ?></span></td>
                    </tr>
                <?php } } ?>
                    </tbody>
                </table></div></div>
            </div>
            
          </div>
          <!-- End Tab 3 -->
        </div>
      </div>
    </aside>

    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="assets/script/jquery.min.js"></script>
    <!--<script src="assets/js/bootstrap.bundle.min.js"></script> -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- apps -->
    <script src="assets/js/app.min.js"></script>
    <script src="assets/js/app.init.horizontal-fullwidth.js"></script>
    <script src="assets/js/app-style-switcher.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="assets/js/perfect-scrollbar.js"></script>
    <script src="assets/js/sparkline.js"></script>
    <!--Wave Effects -->
    <script src="assets/js/waves.js"></script>
    <!-- Sweet-Alert -->
    <script src="assets/js/sweetalert-dev.js"></script>
    <!--Menu sidebar -->
    <script src="assets/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="assets/js/custom.js"></script>
    
    <!--  BEGIN CUSTOM STYLE FILE  -->
    <script src="assets/elements/tooltip.js"></script>
    <!--  END CUSTOM STYLE FILE  -->

    <!-- script jquery -->
    <script type="text/javascript" src="assets/script/titulos.js"></script>
    <script type="text/javascript" src="assets/script/jquery.mask.js"></script>
    <script type="text/javascript" src="assets/script/mask.js"></script>
    <script type="text/javascript" src="assets/script/script2.js"></script>
    <!-- <script type="text/javascript" src="assets/script/ajax.js"></script> -->
    <script type="text/javascript" src="assets/script/jsdelivery.js"></script>
    <script type="text/javascript" src="assets/script/validation.min.js"></script>
    <script type="text/javascript" src="assets/script/script.js"></script>
    <!-- script jquery -->

    <!-- Calendario -->
    <link rel="stylesheet" href="assets/calendario/jquery-ui.css" />
    <script src="assets/calendario/jquery-ui.js"></script>
    <script src="assets/script/jscalendario.js"></script>
    <script src="assets/script/autocompleto.js"></script>
    <!-- Calendario -->

    <!-- jQuery -->
    <script src="assets/plugins/noty/packaged/jquery.noty.packaged.min.js"></script>
    <script type="text/jscript">
    $('#loading_productos').append('<center><i class="fa fa-spin fa-spinner"></i> Por favor espere, cargando registros ......</center>').fadeIn("slow");
    setTimeout(function() {
    $('#loading_productos').load("salas_mesas?CargaProductos=si");
     }, 100);
    </script>
    
    <script type="text/javascript">
    setTimeout(function() {
        $('#notificaciones').load("notificaciones?CargaNotificacion=si&tipo="+'<?php echo encrypt('2'); ?>');
    }, 5000);
    setInterval("checkUpdate()", 5000);

    function checkUpdate()
    {
        setTimeout(function() {
            $('#notificaciones').load("notificaciones?CargaNotificacion=si&tipo="+'<?php echo encrypt('2'); ?>');
        }, 3000);
    }
    </script>
    <!-- jQuery -->

</body>
</html>

<?php } else { ?>   
        <script type='text/javascript' language='javascript'>
        alert('NO TIENES PERMISO PARA ACCEDER A ESTA PAGINA.\nCONSULTA CON EL ADMINISTRADOR PARA QUE TE DE ACCESO')  
        document.location.href='panel'   
        </script> 
<?php } } else { ?>
        <script type='text/javascript' language='javascript'>
        alert('NO TIENES PERMISO PARA ACCEDER AL SISTEMA.\nDEBERA DE INICIAR SESION')  
        document.location.href='logout'  
        </script> 
<?php } ?>