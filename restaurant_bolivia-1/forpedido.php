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
$reg = $tra->RegistrarPedido();
exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="nuevocliente")
{
$reg = $tra->RegistrarClientes();
exit;
} 
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="update")
{
$reg = $tra->ActualizarPedidos();
exit;
}  
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="agrega")
{
$reg = $tra->AgregarDetallesPedidos();
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
    <!-- timepicker CSS -->
    <link href="assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
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
                    <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-align-justify"></i> Detalle de Producto/Combo</h4>
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
                    <option value="0"> -- SELECCIONE -- </option>
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
                    <input type="hidden" name="formulario" id="formulario" value="forpedido"/>
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
                    <option value="0"> -- SELECCIONE -- </option>
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
                <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Gestión de Pedidos</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Pedidos</li>
                                <li class="breadcrumb-item active" aria-current="page">Pedidos</li>
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

<?php if (isset($_GET['codventa']) && isset($_GET['codsucursal']) && decrypt($_GET["proceso"])=="U") {
      
$j = new Login();
$reg = $j->VentasPorId(); ?>
      
<form class="form form-material" method="post" action="#" name="updatepedido" id="updatepedido" data-id="<?php echo $reg[0]["codventa"]; ?>">

<!-- Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
            <h4 class="card-title text-white"><i class="fa fa-save"></i> Gestión de Pedidos</h4>
            </div>

               <div class="form-body">

                <div id="save">
                   <!-- error will be shown here ! -->
                </div>

            <div class="card-body">

    
<input type="hidden" name="proceso" id="proceso" <?php if (isset($_GET['codventa']) && decrypt($_GET["proceso"])=="U") { ?> value="update" <?php } else { ?> value="agrega" <?php } ?>/>

<input type="hidden" name="codventa" id="codventa" value="<?php echo $reg[0]['codventa']; ?>">
<input type="hidden" name="venta" id="venta" value="<?php echo encrypt($reg[0]['codventa']); ?>">

<input type="hidden" name="codsucursal" id="codsucursal" value="<?php echo encrypt($reg[0]['codsucursal']); ?>">
<input type="hidden" name="sucursal" id="sucursal" value="<?php echo encrypt($reg[0]['codsucursal']); ?>">

<input type="hidden" name="codpedido" id="codpedido" value="<?php echo $reg[0]['codpedido']; ?>">
<input type="hidden" name="codcaja" id="codcaja" value="<?php echo $reg[0]['codcaja']; ?>">
        
    <h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-file-send"></i> Datos de Factura</h2><hr>

    <div class="row">
        <div class="col-md-6">
          <div class="form-group has-feedback">
            <label class="control-label">Búsqueda de Cliente: <span class="symbol required"></span></label>
            <input type="hidden" name="codcliente" id="codcliente" value="<?php echo $reg[0]['codcliente'] == '' ? "0" : $reg[0]['codcliente']; ?>">
            <input type="hidden" name="nrodocumento" id="nrodocumento" value="<?php echo $reg[0]['codcliente'] == '' ? "0" : $reg[0]['dnicliente']; ?>">
            <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="busqueda" id="busqueda" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Realice la Búsqueda del Cliente por Nº de Documento, Nombres o Apellidos" value="<?php echo $reg[0]['codcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['documento'].": ".$reg[0]['dnicliente'].": ".$reg[0]['nomcliente']; ?>" autocomplete="off" required="" aria-required="true"/>
            <i class="fa fa-search form-control-feedback"></i> 
          </div>
        </div>

        <div class="col-md-6"> 
            <div class="form-group has-feedback"> 
                <label class="control-label">Nombre de Repartidor: </label>
                <i class="fa fa-bars form-control-feedback"></i>
                <select style="color:#000;font-weight:bold;" name="repartidor" id="repartidor" class="form-control" required="" aria-required="true">
                <option value="0"> -- SELECCIONE -- </option>
                <?php
                $usuario = new Login();
                $usuario = $usuario->ListarRepartidores();
                if($usuario==""){ 
                            echo "";
                } else {
                for($i=0;$i<sizeof($usuario);$i++){ ?>
                <option value="<?php echo encrypt($usuario[$i]['codigo']); ?>"<?php if (!(strcmp($reg[0]['repartidor'], htmlentities($usuario[$i]['codigo'])))) {echo "selected=\"selected\""; } ?>><?php echo $usuario[$i]['nombres'] ?></option> <?php } } ?>
                </select>
            </div> 
        </div>
    </div>

<h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Detalles de Factura</h2><hr>

<div id="detallespedidosupdate">

<!--############################## MODAL PARA ACTUALIZAR VENTA ######################################-->
<!-- sample modal content -->
<div id="myModalPago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Actualizar Pedido</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <!--<form class="form form-material" name="cerrardelivery" id="cerrardelivery" action="#">-->

        <div class="modal-body">

        <div class="row">
            <div class="col-md-4">
                <h4 class="mb-0 font-light">Total a Pagar</h4>
                <h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextImporte" name="TextImporte"><?php echo number_format($reg[0]['totalpago']+$reg[0]['totalpropina'], 2, '.', ','); ?></label></h4>
            </div>

            <div class="col-md-4">
                <h4 class="mb-0 font-light">Total Recibido</h4>
                <h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextPagado" name="TextPagado"><?php echo number_format($reg[0]['montopagado']+$reg[0]['montopagado2'], 2, '.', ','); ?></label></h4>
            </div>

            <div class="col-md-4">
                <h4 class="mb-0 font-light">Total Cambio</h4>
                <h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextCambio" name="TextCambio"><?php echo $cambio = ($reg[0]['tipopago'] == 'CREDITO' ? "0.00" : number_format($reg[0]['totalpago']+$reg[0]['totalpropina']-($reg[0]['montopagado']+$reg[0]['montopagado2']), 2, '.', ',')); ?></label></h4>
            </div>
        </div>
             
        <div class="row">
            <div class="col-md-8">
                <h4 class="mb-0 font-light">Nombre del Cliente</h4>
                <h4 class="mb-0 font-medium"> <label id="TextCliente" name="TextCliente"><?php echo $reg[0]['codcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['documento'].": ".$reg[0]['dnicliente'].": ".$reg[0]['nomcliente']; ?></label></h4>
            </div>

            <div class="col-md-4">
                <h4 class="mb-0 font-light">Limite de Crédito</h4>
                <h4 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextCredito" name="TextCredito"><?php echo $reg[0]['codcliente'] == '' ? "0.00" : number_format($reg[0]['creditodisponible'], 2, '.', ','); ?></label></h4>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">Tipo de Documento: <span class="symbol required"></span></label><br>
                    <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="ticket" name="tipodocumento" value="TICKET" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "TICKET") { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?> disabled="">
                    <label class="custom-control-label" for="ticket">TICKET</label>
                    </div>
                </div>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="boleta" name="tipodocumento" value="BOLETA" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "BOLETA") { ?> checked="checked" <?php } ?> disabled="">
                    <label class="custom-control-label" for="boleta">BOLETA</label>
                    </div>
                </div><br>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="factura" name="tipodocumento" value="FACTURA" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "FACTURA") { ?> checked="checked" <?php } ?> disabled="">
                    <label class="custom-control-label" for="factura">FACTURA</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
               <div class="form-group">
               <label class="control-label">Condición de Pago: <span class="symbol required"></span></label>
               <div class="custom-control custom-radio">
               <input type="radio" class="custom-control-input" id="contado" name="tipopago" value="CONTADO" onClick="CargaCondicionesPagos()" <?php if (isset($reg[0]['tipopago']) && $reg[0]['tipopago'] == "CONTADO") { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?> disabled="">
               <label class="custom-control-label" for="contado">CONTADO</label>
               </div>

               <div class="custom-control custom-radio">
               <input type="radio" class="custom-control-input" id="credito" name="tipopago" value="CREDITO" onClick="CargaCondicionesPagos()" <?php if (isset($reg[0]['tipopago']) && $reg[0]['tipopago'] == "CREDITO") { ?> checked="checked" <?php } ?> disabled="">
               <label class="custom-control-label" for="credito">CRÉDITO</label>
               </div>
            </div>
            </div>

            <div class="col-md-4"> 
                <div class="form-group has-feedback"> 
                <label class="control-label">Costo Delivery: <span class="symbol required"></span></label>
                <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montodelivery" id="montodelivery" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Costo Delivery" value="<?php echo number_format($reg[0]['montodelivery'], 2, '.', ''); ?>" <?php if ($reg[0]['repartidor'] == 0) { ?> disabled="" <?php } ?> required="" aria-required="true"> 
                <i class="fa fa-dollar form-control-feedback"></i>
                </div> 
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">Estado de Pedido: <span class="symbol required"></span></label>
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="0" name="statuspedido" value="0" <?php if (isset($reg[0]['statuspedido']) && $reg[0]['statuspedido'] == 0) { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?>>
                    <label class="custom-control-label" for="0">ENTREGADO</label>
                    </div>

                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="1" name="statuspedido" value="1" <?php if (isset($reg[0]['statuspedido']) && $reg[0]['statuspedido'] == 1) { ?> checked="checked" <?php } ?>>
                    <label class="custom-control-label" for="1">PENDIENTE</label>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Fecha de Entrega: <span class="symbol required"></span></label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control expira" name="fechaentrega" id="fechaentrega" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Fecha de Entrega" autocomplete="off" value="<?php echo date("d-m-Y",strtotime($reg[0]['fechaentrega'])); ?>" required="" aria-required="true"/>
                    <i class="fa fa-calendar form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Hora de Entrega: <span class="symbol required"></span></label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="horaentrega" id="horaentrega" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Hora de Entrega" autocomplete="off" value="<?php echo date("H:i",strtotime($reg[0]['fechaentrega'])); ?>" required="" aria-required="true"/>
                    <i class="fa fa-calendar form-control-feedback"></i>
                </div>
            </div>
        </div>

        <div id="muestra_condiciones"><!-- IF CONDICION PAGO -->

        <?php if (isset($reg[0]['tipopago']) && $reg[0]['tipopago'] == "CONTADO") { ?>

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
                    <option value="EFECTIVO"<?php if (!(strcmp('EFECTIVO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>EFECTIVO</option>
                    <option value="CHEQUE"<?php if (!(strcmp('CHEQUE', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>CHEQUE</option>
                    <option value="TARJETA DE CREDITO"<?php if (!(strcmp('TARJETA DE CREDITO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TARJETA DE CRÉDITO</option>
                    <option value="TARJETA DE DEBITO"<?php if (!(strcmp('TARJETA DE DEBITO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TARJETA DE DÉBITO</option>
                    <option value="TARJETA PREPAGO"<?php if (!(strcmp('TARJETA PREPAGO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TARJETA PREPAGO</option>
                    <option value="TRANSFERENCIA"<?php if (!(strcmp('TRANSFERENCIA', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TRANSFERENCIA</option>
                    <option value="DINERO ELECTRONICO"<?php if (!(strcmp('DINERO ELECTRONICO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>DINERO ELECTRÓNICO</option>
                    <option value="CUPON"<?php if (!(strcmp('CUPON', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>CUPÓN</option>
                    <option value="OTROS"<?php if (!(strcmp('OTROS', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>OTROS</option>
                  </select>
                </div> 
            </div>
        </div>

        <div class="row">
            <div class="col-md-12"> 
                    <div class="form-group has-feedback"> 
                    <label class="control-label">Monto de Pago Nº 1: <span class="symbol required"></span></label>
                    <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
                    <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="<?php echo number_format($reg[0]['montopagado'], 2, '.', ''); ?>" required="" aria-required="true"> 
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
                    <option value="EFECTIVO"<?php if (!(strcmp('EFECTIVO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>EFECTIVO</option>
                    <option value="CHEQUE"<?php if (!(strcmp('CHEQUE', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>CHEQUE</option>
                    <option value="TARJETA DE CREDITO"<?php if (!(strcmp('TARJETA DE CREDITO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TARJETA DE CRÉDITO</option>
                    <option value="TARJETA DE DEBITO"<?php if (!(strcmp('TARJETA DE DEBITO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TARJETA DE DÉBITO</option>
                    <option value="TARJETA PREPAGO"<?php if (!(strcmp('TARJETA PREPAGO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TARJETA PREPAGO</option>
                    <option value="TRANSFERENCIA"<?php if (!(strcmp('TRANSFERENCIA', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TRANSFERENCIA</option>
                    <option value="DINERO ELECTRONICO"<?php if (!(strcmp('DINERO ELECTRONICO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>DINERO ELECTRÓNICO</option>
                    <option value="CUPON"<?php if (!(strcmp('CUPON', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>CUPÓN</option>
                    <option value="OTROS"<?php if (!(strcmp('OTROS', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>OTROS</option>
                  </select>
               </div> 
            </div>
        </div>

        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Monto de Pago Nº 2: </label>
                    <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado2" id="montopagado2" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="<?php echo number_format($reg[0]['montopagado2'], 2, '.', ''); ?>" <?php if ($reg[0]['formapago2'] == "" || $reg[0]['formapago2'] == 0) { ?> disabled="" <?php } ?> required="" aria-required="true"> 
                    <i class="fa fa-dollar form-control-feedback"></i>
                </div>  
            </div>
        </div>

        </div>
        <!-- /.col -->

        </div>

        <div class="row">
            <div class="col-md-12"> 
              <div class="form-group has-feedback2"> 
                <label class="control-label">Observaciones: </label> 
                <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"><?php echo $reg[0]['observaciones']; ?></textarea>
                <i class="fa fa-comment-o form-control-feedback2"></i> 
              </div> 
            </div>
         </div> 

        <?php } else { ?>

        <div class="row">
            <div class="col-md-4"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
                    <input type="hidden" name="formapago" id="formapago" value="">
                    <input type="hidden" name="montopagado" id="montopagado" value="0.00">
                    <input type="hidden" name="formapago2" id="formapago2" value="">
                    <input type="hidden" name="montopagado2" id="montopagado2" value="0.00">
                    <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
                    <input type="hidden" name="montopropina" id="montopropina" value="0.00">
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control vencecredito" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off"  value="<?php echo date("d-m-Y",strtotime($reg[0]['fechavencecredito'])); ?>" placeholder="Ingrese Fecha Vence Crédito" aria-required="true">
                    <i class="fa fa-calendar form-control-feedback"></i>  
                </div> 
            </div>

            <div class="col-md-8"> 
                <div class="form-group has-feedback2"> 
                    <label class="control-label">Observaciones: </label> 
                    <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"><?php echo $reg[0]['observaciones']; ?></textarea>
                    <i class="fa fa-comment-o form-control-feedback2"></i> 
                </div> 
            </div>
         </div> 

        <?php } ?>

        </div><!-- END CONDICION PAGO -->

        <div class="row">
            <div class="col-md-6">
                <span id="submit_guardar"><button type="submit" name="btn-update" id="btn-update" class="btn btn-warning btn-lg btn-block waves-effect waves-light"><span class="fa fa-print"></span> Facturar e Imprimir</button></span>
            </div>
            <div class="col-md-6">
                <button type="reset" class="btn btn-dark btn-lg btn-block waves-effect waves-light" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cancelar</button>
            </div>
        </div>

        </div>
        <!--</form>-->

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA ACTUALIZAR VENTA ######################################-->

    <div class="table-responsive m-t-20">
        <table class="table2 table-hover">
            <thead>
            <tr class="text-center">
                <th>Cantidad</th>
                <th>Tipo</th>
                <th class="text-left">Descripción de Producto</th>
                <th>Precio Unit.</th>
                <th>Valor Total</th>
                <th>Desc %</th>
                <th>Impuesto</th>
                <th>Valor Neto</th>
                <?php if ($_SESSION['acceso'] == "administradorS") { ?>
                <th>Acción</th>
                <?php } ?>
                </tr>
            </thead>
            <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesVentas();
$a=1;
$count = 0;
for($i=0;$i<sizeof($detalle);$i++){ 
$count++;
?>
    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
    <td>
    <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
    <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetallePedido('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
    <input type="text" class="bold" name="cantventa[]" id="cantventa_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoPedido(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="<?php echo number_format($detalle[$i]["cantventa"], 2, '.', ''); ?>" title="Ingrese Cantidad">
    <input type="hidden" name="cantidadventabd[]" id="cantidadventabd_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["cantventa"], 2, '.', ''); ?>">
    <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetallePedido('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
    </div>
    </td>
    <td class="text-danger alert-link">
    <input type="hidden" name="coddetalleventa[]" id="coddetalleventa" value="<?php echo $detalle[$i]["coddetalleventa"]; ?>">
    <input type="hidden" name="codproducto[]" id="codproducto" value="<?php echo $detalle[$i]["codproducto"]; ?>">
    <input type="hidden" name="tipo[]" id="tipo" value="<?php echo $detalle[$i]["tipo"]; ?>">
    <input type="hidden" class="preciocompra" name="preciocompra[]" id="preciocompra_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["preciocompra"], 2, '.', ''); ?>">
    <?php if($detalle[$i]['tipo'] == 1){
        echo "PRODUCTO";
    } elseif($detalle[$i]['tipo'] == 2){
        echo "COMBO";
    } else {
        echo "EXTRA";
    } ?></td>
      
    <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
    <small class="text-danger alert-link"><?php echo $detalle[$i]['detallesobservaciones'] == '' ? "**********" : $detalle[$i]['detallesobservaciones'] ?></small></td>
      
    <td><strong><input type="hidden" name="precioventa[]" id="precioventa_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>">
    <input type="hidden" name="precioconiva[]" id="precioconiva_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? "0.00" : number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>"><?php echo number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></strong></td>

    <td><input type="hidden" name="valortotal[]" id="valortotal_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["valortotal"], 2, '.', ''); ?>"><label id="txtvalortotal_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></label></td>
      
    <td><input type="hidden" name="descproducto[]" id="descproducto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["descproducto"], 2, '.', ''); ?>">
    <input type="hidden" class="totaldescuentov" name="totaldescuentov[]" id="totaldescuentov_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["totaldescuentov"], 2, '.', ''); ?>">
    <label id="txtdescproducto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?></label><sup><label><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ','); ?>%</label></sup></td>

    <td><input type="hidden" name="ivaproducto[]" id="ivaproducto_<?php echo $count; ?>" value="<?php echo $detalle[$i]["ivaproducto"]; ?>"><label><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', '')."%" : "(E)"; ?></label></td>

    <td><input type="hidden" class="subtotalivasi" name="subtotalivasi[]" id="subtotalivasi_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalivano" name="subtotalivano[]" id="subtotalivano_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalimpuestos" name="subtotalimpuestos[]" id="subtotalimpuestos_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['subtotalimpuestos'], 2, '.', ''); ?>">

    <input type="hidden" class="subtotaldiscriminado" name="subtotaldiscriminado[]" id="subtotaldiscriminado_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto']-$detalle[$i]['subtotalimpuestos'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="valorneto" name="valorneto[]" id="valorneto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto'], 2, '.', ''); ?>" >

    <input type="hidden" class="valorneto2" name="valorneto2[]" id="valorneto2_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto2'], 2, '.', ''); ?>" >

    <label id="txtvalorneto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></label></td>
    <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDetallesPedidoUpdate('<?php echo encrypt($detalle[$i]["coddetalleventa"]); ?>','<?php echo encrypt($detalle[$i]["codventa"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[0]["codcliente"]); ?>','<?php echo encrypt("DETALLESPEDIDOS") ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>

                                 </tr>
                     <?php } ?>
                </tbody>
            </table><hr>

            <table id="carritototal" class="table-responsive">
            <tr>
    <td width="250"><h5><label>Gravado (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal"><?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdiscriminado" id="txtdiscriminado" value="<?php echo number_format($reg[0]['subtotalivasi'], 0, '.', ','); ?>"/>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="<?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ''); ?>"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    
    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2"><?php echo number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="<?php echo number_format($reg[0]['subtotalivano'], 2, '.', ''); ?>"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo $reg[0]['iva'] ?>"></label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva"><?php echo number_format($reg[0]['totaliva'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtIva" id="txtIva" value="<?php echo number_format($reg[0]['totaliva'], 2, '.', ''); ?>"/>
    </td>
    </tr>
    <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado"><?php echo number_format($reg[0]['descontado'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdescontado" id="txtdescontado" value="<?php echo number_format($reg[0]['descontado'], 2, '.', ''); ?>"/>
        </td>
    
    <td>
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" style="border-radius:4px;height:30px;width:60px;" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['descuento'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento"><?php echo number_format($reg[0]['totaldescuento'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="<?php echo number_format($reg[0]['totaldescuento'], 2, '.', ''); ?>"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal"><?php echo number_format($reg[0]['totalpago'], 2, '.', ','); ?></label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtFactura" id="txtFactura" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtImporte" id="txtImporte" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtTotalCompra" id="txtTotalCompra" value="<?php echo number_format($reg[0]['totalpago2'], 2, '.', ''); ?>"/>    </td>
                    </tr>
    </table>
        </div>
</div>

<div class="clearfix"></div>
<hr>
              <div class="text-right">
<button type="button" id="buttonpago" class="btn btn-warning waves-effect waves-light" data-placement="left" title="Cobrar Venta" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalPago" data-backdrop="static" data-keyboard="false"><span class="fa fa-edit"></span> Actualizar</button>

<a href="pedidos"><button class="btn btn-dark" type="button"><span class="fa fa-trash-o"></span> Cancelar</button></a>
              </div>
          </div>
       </div>
     </form>
   </div>
  </div>
</div>

<!-- End Row -->


<?php } else if (isset($_GET['codventa']) && isset($_GET['codsucursal']) && decrypt($_GET["proceso"])=="A") {
      
$reg = $tra->VentasPorId(); ?>
      
<form class="form form-material" method="post" action="#" name="agregaventas" id="agregaventas" data-id="<?php echo $reg[0]["codventa"] ?>">

<?php } else { ?>

<form class="form form-material" method="post" action="#" name="savepedido" id="savepedido">

<!--############################## MODAL PARA CIERRE DE DELIVERY ##############################-->
<!-- sample modal content -->
<div id="myModalPago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Cierre de Pedido</h4>
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
                <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montodelivery" id="montodelivery" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Costo Delivery" value="0.00" required="" aria-required="true"> 
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
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="" required="" aria-required="true">
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
              <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado2" id="montopagado2" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="0.00" disabled="" required="" aria-required="true"> 
              <i class="fa fa-dollar form-control-feedback"></i>
            </div>  
          </div>
        </div>

        </div>
        <!-- /.col -->

      </div>

    </div><!-- END CONDICION PAGO -->

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">Estado de Pedido: <span class="symbol required"></span></label>
                <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="0" name="statuspedido" value="0" checked="checked">
                <label class="custom-control-label" for="0">ENTREGADO</label>
                </div>

                <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="1" name="statuspedido" value="1">
                <label class="custom-control-label" for="1">PENDIENTE</label>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group has-feedback">
                <label class="control-label">Fecha de Entrega: <span class="symbol required"></span></label>
                <input style="color:#000;font-weight:bold;" type="text" class="form-control expira" name="fechaentrega" id="fechaentrega" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Fecha de Entrega" autocomplete="off" value="<?php echo date("d-m-Y"); ?>" required="" aria-required="true"/>
                <i class="fa fa-calendar form-control-feedback"></i>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group has-feedback">
                <label class="control-label">Hora de Entrega: <span class="symbol required"></span></label>
                <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="horaentrega" id="horaentrega" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Hora de Entrega" autocomplete="off" value="<?php echo date("H:i"); ?>" required="" aria-required="true"/>
                <i class="fa fa-calendar form-control-feedback"></i>
            </div>
        </div>
    </div>

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

<!-- Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Gestión de Pedidos</h4>
            </div>

            <div id="save">
            <!-- error will be shown here ! -->
            </div>
            
            <div class="form-body">

              <div class="card-body">

    <div class="row">

        <!-- .col -->
        <div class="col-md-6">
        
        <h3 class="card-subtitle m-0 text-dark"><i class="font-20 mdi mdi-cart-plus"></i> Detalle de Pedido</h3><hr>

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
            <div class="col-md-12">
                <label class="control-label">Búsqueda de Cliente: </label>
                <div class="input-group mb-3">
                <div class="input-group-append">
                <button type="button" class="btn btn-success waves-effect waves-light" data-placement="left" title="Nuevo Cliente" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCliente" data-backdrop="static" data-keyboard="false"><i class="fa fa-user-plus"></i></button>
                </div>
                <input type="hidden" name="codcliente" id="codcliente" value="0">
                <input type="hidden" name="nrodocumento" id="nrodocumento" value="0">
                <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="busqueda" id="busqueda" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Criterio para la Búsqueda del Cliente" autocomplete="off"/>
                </div>
            </div>
        </div>
    
    <div id="favoritos" style="display:none !important;"></div>

        <div class="table-responsive m-t-5 scroll-pedido">
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
                <button type="button" class="btn btn-info btn-lg btn-block waves-effect waves-light" onClick="RecargaPedidos('<?php echo encrypt("PEDIDOS"); ?>');" data-placement="left" title="Ver Pedidos" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalPedidos" data-backdrop="static" data-keyboard="false"><i class="fa fa-cutlery"></i> Ver Cocina</button>
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

<?php } ?>
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
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->

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

    <!-- script jquery -->
    <script type="text/javascript" src="assets/script/titulos.js"></script>
    <script type="text/javascript" src="assets/script/jquery.mask.js"></script>
    <script type="text/javascript" src="assets/script/mask.js"></script>
    <script type="text/javascript" src="assets/script/script2.js"></script>
    <script type="text/javascript" src="assets/script/jspedidos.js"></script>
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
    <script src="assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script type="text/javascript">
    $('body').on('focus',"#horaentrega", function(){
        $(this).timepicker({showMeridian: false});
    });
    </script>
    <script type="text/jscript">
    $('#loading_productos').append('<center><i class="fa fa-spin fa-spinner"></i> Por favor espere, cargando registros ......</center>').fadeIn("slow");
    setTimeout(function() {
    $('#loading_productos').load("salas_mesas?CargaProductos=si");
     }, 100);
    </script>
    <script type="text/javascript">
    setTimeout(function() {
        $('#notificaciones').load("notificaciones?CargaNotificacion=si&tipo="+'<?php echo encrypt('3'); ?>');
    }, 5000);
    setInterval("checkUpdate()", 5000);

    function checkUpdate()
    {
        setTimeout(function() {
            $('#notificaciones').load("notificaciones?CargaNotificacion=si&tipo="+'<?php echo encrypt('3'); ?>');
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