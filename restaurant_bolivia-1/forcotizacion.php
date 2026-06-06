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

if(isset($_POST["proceso"]) and $_POST["proceso"]=="save")
{
$reg = $tra->RegistrarCotizaciones();
exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="update")
{
$reg = $tra->ActualizarCotizaciones();
exit;
}  
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="agregar") 
{
$reg = $tra->AgregarDetallesCotizaciones();
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
    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-boxed-layout="full" data-boxed-layout="boxed" data-header-position="fixed" data-sidebar-position="fixed" class="mini-sidebar"> 

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

<!--############################## MODAL PARA AGREGAR OBSERVACIONES EN DETALLE ####################################-->
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
<!--############################## MODAL PARA AGREGAR OBSERVACIONES EN DETALLE ###################################-->

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

<!--#################### MODAL PARA BUSQUEDA DE PRODUCTOS #########################-->
<div id="myModalBusqueda" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-align-justify"></i> Búsqueda</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
                </div>
                <div class="modal-body">

            <div class="row">
                <div class="col-md-4">
                   <button type="button" class="btn btn-success btn-lg btn-block waves-effect waves-light" style="cursor: pointer;" onClick="MostrarProductos();"><span class="fa fa-cubes"></span> Productos</button>
                </div>
                <div class="col-md-4">
                   <button type="button" class="btn btn-info btn-lg btn-block waves-effect waves-light" style="cursor: pointer;" onClick="MostrarCombos();"><span class="fa fa-archive"></span> Combos</button>
                </div>
                <div class="col-md-4">
                   <button type="button" class="btn btn-danger btn-lg btn-block waves-effect waves-light" style="cursor: pointer;" onClick="MostrarExtras();"><span class="mdi mdi-book-multiple"></span> Extras</button>
                </div>
            </div><hr>

                <!-- .div load -->
                <div id="loading_productos"></div>
                <!-- /.div load -->

              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!--#################### MODAL PARA BUSQUEDA DE PRODUCTOS #########################-->

<!--############################## MODAL PARA REGISTRO DE NUEVO CLIENTE ######################################-->
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
                    <option value="<?php echo $doc[$i]['coddocumento']; ?>"><?php echo $doc[$i]['documento']; ?></option>
                    <?php } } ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Documento: <span class="symbol required"></span></label>
                    <input type="hidden" name="proceso" id="proceso" value="nuevocliente"/>
                    <input type="hidden" name="codsucursal" id="codsucursal" value="<?php echo encrypt($_SESSION['codsucursal']); ?>">
                    <input type="hidden" name="formulario" id="formulario" value="forcotizacion"/>
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
                <button type="submit" name="btn-cliente" id="btn-cliente" class="btn btn-warning"><span class="fa fa-save"></span> Guardar</button>
                <button class="btn btn-dark" type="button" onclick="ResetCliente2()" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cancelar</button>
            </div>

        </form>

    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal --> 
<!--############################## MODAL PARA REGISTRO DE NUEVO CLIENTE ######################################-->

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
                    <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Gestión de Cotizaciones</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Cotizaciones</li>
                                <li class="breadcrumb-item active" aria-current="page">Cotización</li>
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
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
            <h4 class="card-title text-white"><i class="fa fa-save"></i> Gestión de Cotizaciones</h4>
            </div>

<?php if (isset($_GET['codcotizacion']) && isset($_GET['codsucursal']) && decrypt($_GET["proceso"])=="U") {
      
$j = new Login();
$reg = $j->CotizacionesPorId(); ?>
      
<form class="form form-material" method="post" action="#" name="updatecotizaciones" id="updatecotizaciones" data-id="<?php echo $reg[0]["codcotizacion"]; ?>">

<?php } else if (isset($_GET['codcotizacion']) && isset($_GET['codsucursal']) && decrypt($_GET["proceso"])=="A") {
      
$j = new Login();
$reg = $j->CotizacionesPorId(); ?>
      
<form class="form form-material" method="post" action="#" name="agregacotizaciones" id="agregacotizaciones" data-id="<?php echo $reg[0]["codcotizacion"]; ?>">
        
<?php } else { ?>
        
 <form class="form form-material" method="post" action="#" name="savecotizaciones" id="savecotizaciones">

<?php } ?>
           

    <div id="save">
    <!-- error will be shown here ! -->
    </div>

    <div class="form-body">

    <div class="card-body">

    <input type="hidden" name="codcotizacion" id="codcotizacion" <?php if (isset($reg[0]['codcotizacion'])) { ?> value="<?php echo $reg[0]['codcotizacion']; ?>"<?php } ?>>
    <input type="hidden" name="cotizacion" id="cotizacion" <?php if (isset($reg[0]['codcotizacion'])) { ?> value="<?php echo encrypt($reg[0]['codcotizacion']); ?>"<?php } ?>>

    <input type="hidden" name="codsucursal" id="codsucursal" <?php if (isset($reg[0]['codsucursal'])) { ?> value="<?php echo encrypt($reg[0]['codsucursal']); ?>" <?php } else { ?> value="<?php echo encrypt($_SESSION["codsucursal"]); ?>"<?php } ?>>
    <input type="hidden" name="sucursal" id="sucursal" <?php if (isset($reg[0]['codsucursal'])) { ?> value="<?php echo encrypt($reg[0]['codsucursal']); ?>" <?php } ?>>
    
    <input type="hidden" name="proceso" id="proceso" <?php if (isset($_GET['codcotizacion']) && decrypt($_GET["proceso"])=="U") { ?> value="update" <?php } elseif (isset($_GET['codcotizacion']) && decrypt($_GET["proceso"])=="A") { ?> value="agregar" <?php } else { ?> value="save" <?php } ?>>
        
    <h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-file-send"></i> Datos de Factura</h2><hr>

    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                <label class="control-label">Tipo de Documento: <span class="symbol required"></span></label><br>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="ticket" name="tipodocumento" value="TICKETCOTIZACION" <?php if (isset($reg[0]['tipodocumento'])) { ?> <?php if($reg[0]['tipodocumento'] == "TICKETCOTIZACION") { ?> value="TICKETCOTIZACION" checked="checked" <?php } } else { ?> checked="checked" <?php } ?>>
                        <label class="custom-control-label" for="ticket">TICKET</label>
                    </div>
                </div><br>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                        <input type="radio" class="custom-control-input" id="factura" name="tipodocumento" value="FACTURACOTIZACION" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "FACTURACOTIZACION") { ?> checked="checked" <?php } ?>>
                        <label class="custom-control-label" for="factura">FACTURA</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-10">
            <label class="control-label">Búsqueda de Cliente: </label>
            <div class="input-group mb-3">
            <div class="input-group-append">
            <button type="button" class="btn btn-success waves-effect waves-light" data-placement="left" title="Nuevo Cliente" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCliente" data-backdrop="static" data-keyboard="false"><i class="fa fa-user-plus"></i> (F7)</button>
            </div>
            <input type="hidden" name="codcliente" id="codcliente" <?php if (isset($reg[0]['codcliente'])) { ?> value="<?php echo $reg[0]['codcliente'] == '' ? "0" : $reg[0]['codcliente']; ?>" <?php } else { ?> value="0" <?php } ?>>
            <input type="hidden" name="nrodocumento" id="nrodocumento" <?php if (isset($reg[0]['codcliente'])) { ?> value="<?php echo $reg[0]['codcliente'] == '' ? "0" : $reg[0]['dnicliente']; ?>" <?php } else { ?> value="0" <?php } ?>>
            <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="busqueda" id="busqueda" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Criterio para la Búsqueda del Cliente" autocomplete="off" <?php if (isset($reg[0]['codcliente'])) { ?> value="<?php echo $reg[0]['codcliente'] == "" || $reg[0]['codcliente'] == "0" ? "CONSUMIDOR FINAL" : $reg[0]['documento'].": ".$reg[0]['dnicliente'].": ".$reg[0]['nomcliente']; ?>" <?php } else { ?> value="CONSUMIDOR FINAL" <?php } ?>/>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group has-feedback">
                <label class="control-label">Observaciones: </label>
                <input type="text" class="form-control" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Observación de Cotización" autocomplete="off" <?php if (isset($reg[0]['observaciones'])) { ?> value="<?php echo $reg[0]['observaciones']; ?>" <?php } ?> required="" aria-required="true"/> 
                <i class="fa fa-comments form-control-feedback"></i> 
            </div>
        </div>
    </div>

<?php if (isset($_GET['codcotizacion']) && isset($_GET['codsucursal']) && decrypt($_GET["proceso"])=="U") { ?>

<h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Detalles de Factura</h2><hr>

<div id="detallescotizacionesupdate">

        <div class="table-responsive m-t-20">
            <table class="table2 table-hover">
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
<?php if ($_SESSION['acceso'] == "administradorS") { ?><th>Acción</th><?php } ?>
                    </tr>
                </thead>
                <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesCotizaciones();
$a=1;
$count = 0;
for($i=0;$i<sizeof($detalle);$i++){ 
$count++;  
?>
    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
    <td>
    <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
    <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetalleCotizacion('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
    <input type="text" class="bold" name="cantcotizacion[]" id="cantcotizacion_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoCotizacion(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="<?php echo number_format($detalle[$i]["cantcotizacion"], 2, '.', ''); ?>" title="Ingrese Cantidad">
    <input type="hidden" name="cantcotizacionbd[]" id="cantcotizacionbd_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["cantcotizacion"], 2, '.', ''); ?>">
    <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetalleCotizacion('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
    </div>
    </td>
      
    <td class="text-danger alert-link">
    <input type="hidden" name="coddetallecotizacion[]" id="coddetallecotizacion" value="<?php echo $detalle[$i]["coddetallecotizacion"]; ?>">
    <input type="hidden" name="codproducto[]" id="codproducto" value="<?php echo $detalle[$i]["codproducto"]; ?>">
    <input type="hidden" name="tipo[]" id="tipo" value="<?php echo $detalle[$i]["tipo"]; ?>">
    <input type="hidden" name="preciocompra[]" id="preciocompra_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["preciocompra"], 2, '.', ''); ?>">
    <?php if($detalle[$i]['tipo'] == 1){
        echo "PRODUCTO";
    } elseif($detalle[$i]['tipo'] == 2){
        echo "COMBO";
    } else {
        echo "EXTRA";
    } ?></td>
      
    <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
    <small class="text-danger alert-link"><?php echo $observaciones = ($detalle[$i]['detallesobservaciones'] == '' ? "**********" : $detalle[$i]['detallesobservaciones']); ?> <?php echo $salsas = ($detalle[$i]['detallesalsas'] == '' ? "" : $detalle[$i]['detallesalsas']); ?></small></td>
      
    <td><strong><input type="hidden" name="precioventa[]" id="precioventa_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>">
    <input type="hidden" name="precioconiva[]" id="precioconiva_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? "0.00" : number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>"><?php echo number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></strong></td>

    <td><input type="hidden" name="valortotal[]" id="valortotal_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["valortotal"], 2, '.', ''); ?>"><label><label id="txtvalortotal_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valortotal'], 2, '.', ''); ?></label></label></td>

    <td><input type="hidden" name="descproducto[]" id="descproducto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["descproducto"], 2, '.', ''); ?>">
    <input type="hidden" class="totaldescuentov" name="totaldescuentov[]" id="totaldescuentov_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["totaldescuentov"], 2, '.', ''); ?>">
    <label><label id="txtdescproducto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['totaldescuentov'], 2, '.', ''); ?></label><sup><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ''); ?>%</sup></label></td>

    <td><input type="hidden" name="ivaproducto[]" id="ivaproducto_<?php echo $count; ?>" value="<?php echo $detalle[$i]["ivaproducto"]; ?>"><label><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', '')."%" : "(E)"; ?></label></td>

    <td><input type="hidden" class="subtotalivasi" name="subtotalivasi[]" id="subtotalivasi_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalivano" name="subtotalivano[]" id="subtotalivano_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalimpuestos" name="subtotalimpuestos[]" id="subtotalimpuestos_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['subtotalimpuestos'], 2, '.', ''); ?>">

    <input type="hidden" class="subtotaldiscriminado" name="subtotaldiscriminado[]" id="subtotaldiscriminado_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto']-$detalle[$i]['subtotalimpuestos'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="valorneto" name="valorneto[]" id="valorneto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto'], 2, '.', ''); ?>" >

    <input type="hidden" class="valorneto2" name="valorneto2[]" id="valorneto2_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto2'], 2, '.', ''); ?>" >

    <label> <label id="txtvalorneto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></label></label></td>

 <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
<button type="button" class="btn btn-rounded btn-dark" onClick="EliminarDetallesCotizacionesUpdate('<?php echo encrypt($detalle[$i]["coddetallecotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codcotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt("DETALLESCOTIZACIONES") ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                 </tr>
                     <?php } ?>
                </tbody>
            </table><hr>

            <table id="carritototal" class="table-responsive">
                <tr>
    <td width="250"><h5><label>Gravado (<?php echo number_format($reg[0]['iva'], 2, '.', '') ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal"><?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdiscriminado" id="txtdiscriminado" value="<?php echo number_format($reg[0]['subtotalivasi'], 0, '.', ''); ?>"/>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="<?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ''); ?>"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2"><?php echo number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="<?php echo number_format($reg[0]['subtotalivano'], 2, '.', ''); ?>"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>"></label></h5>
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
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" style="border-radius:4px;height:30px;width:60px;"  onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['descuento'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento"><?php echo number_format($reg[0]['totaldescuento'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="<?php echo number_format($reg[0]['totaldescuento'], 2, '.', ''); ?>"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal"><?php echo number_format($reg[0]['totalpago'], 2, '.', ','); ?></label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtTotalCompra" id="txtTotalCompra" value="<?php echo number_format($reg[0]['totalpago2'], 2, '.', ''); ?>"/>    </td>
                    </tr>
                  </table>

        </div>
</div>


<?php } else if (isset($_GET['codcotizacion']) && isset($_GET['codsucursal']) && decrypt($_GET["proceso"])=="A") { ?>

<h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Detalles Agregados</h2><hr>

<div id="detallescotizacionesagregar">

        <div class="table-responsive m-t-20">
            <table class="table2 table-hover">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
<?php if ($_SESSION['acceso'] == "administradorS") { ?><th>Acción</th><?php } ?>
                    </tr>
                </thead>
                <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesCotizaciones();
$a=1;
for($i=0;$i<sizeof($detalle);$i++){  
    ?>
    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
      <td><label><?php echo $a++; ?></label></td>
      
      <td class="text-danger alert-link">
      <?php if($detalle[$i]['tipo'] == 1){
        echo "PRODUCTO";
      } elseif($detalle[$i]['tipo'] == 2){
        echo "COMBO";
      } else {
        echo "EXTRA";
      } ?></td>
      
      <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
      <small class="text-danger alert-link"><?php echo $observaciones = ($detalle[$i]['detallesobservaciones'] == '' ? "**********" : $detalle[$i]['detallesobservaciones']); ?> <?php echo $salsas = ($detalle[$i]['detallesalsas'] == '' ? "" : $detalle[$i]['detallesalsas']); ?></small></td>

      <td><?php echo $detalle[$i]['cantcotizacion']; ?></td>
      
      <td><?php echo $simbolo.number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></td>

       <td><?php echo $simbolo.number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></td>
      
      <td><?php echo $simbolo.number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?><sup><label><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ''); ?>%</label></sup></td>

      <td><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', '')."%" : "(E)"; ?></td>

      <td><?php echo $simbolo.number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></td>

 <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
<button type="button" class="btn btn-rounded btn-dark" onClick="EliminarDetallesCotizacionesAgregar('<?php echo encrypt($detalle[$i]["coddetallecotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codcotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt("DETALLESCOTIZACIONES") ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                 </tr>
                     <?php } ?>
                </tbody>
            </table><hr>

            <table id="carritototal" class="table-responsive">
                <tr>
    <td width="250"><h5><label>Gravado (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?></label></h5>
    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></label></h5>
    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):</label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['totaliva'], 2, '.', ','); ?></label></h5>
    </td>
                </tr>
                <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['descontado'], 2, '.', ','); ?></label></h5>
        </td>
    
    <td>
    <h5><label>Desc. Global <?php echo number_format($reg[0]['descuento'], 2, '.', ''); ?>%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['totaldescuento'], 2, '.', ','); ?></label></h5>
    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['totalpago'], 2, '.', ','); ?></label></b></h4>
    </td>
                    </tr>
                  </table>

            </div>
    </div>

            <hr>

        <input type="hidden" name="idproducto" id="idproducto">
        <input type="hidden" name="codproducto" id="codproducto">
        <input type="hidden" name="producto" id="producto">
        <input type="hidden" name="codcategoria" id="codcategoria">
        <input type="hidden" name="categorias" id="categorias">
        <input type="hidden" name="preciocompra" id="preciocompra"> 
        <input type="hidden" name="precioconiva" id="precioconiva">
        <input type="hidden" name="ivaproducto" id="ivaproducto">
        <input type="hidden" name="observacion" id="observacion">
        <input type="hidden" name="salsa" id="salsa">
        <input type="hidden" name="preparado" id="preparado">

        <h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Detalles de Factura</h2><hr>

        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label">Tipo de Búsqueda: <span class="symbol required"></span></label> 
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="name1" name="tipo" value="1" checked="checked" class="custom-control-input"onclick="VerificaDetalle()">
                        <label class="custom-control-label" for="name1">PRODUCTO</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="name2" name="tipo" value="2" class="custom-control-input"onclick="VerificaDetalle()">
                        <label class="custom-control-label" for="name2">COMBO</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="name3" name="tipo" value="3" class="custom-control-input"onclick="VerificaDetalle()">
                        <label class="custom-control-label" for="name3">EXTRA</label>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3"> 
                <div class="form-group has-feedback"> 
                  <label class="control-label">Ingrese Descripción para Búsqueda: <span class="symbol required"></span></label>
                  <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="search_cotizacion" id="search_cotizacion" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Realice la Búsqueda por Código o Descripción">
                  <i class="fa fa-search form-control-feedback"></i> 
                </div> 
            </div>

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                <label class="control-label">Precio Unitario: <span class="symbol required"></span></label>
                <input class="form-control" type="text" name="precioventa" id="precioventa" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Precio de Venta">
                <i class="fa fa-dollar form-control-feedback"></i> 
               </div> 
            </div> 

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                 <label class="control-label">Stock Actual: <span class="symbol required"></span></label>
                 <input type="text" class="form-control" name="existencia" id="existencia" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Existencia" disabled="disabled" readonly="readonly">
                 <i class="fa fa-bolt form-control-feedback"></i> 
              </div> 
            </div>  

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Descuento: <span class="symbol required"></span></label>
                    <input class="form-control" type="text" name="descproducto" id="descproducto" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Descuento" value="0.00">
                    <i class="fa fa-tint form-control-feedback"></i> 
                </div> 
            </div>

            <div class="col-md-1"> 
                <div class="form-group has-feedback"> 
                 <label class="control-label">Cantidad: <span class="symbol required"></span></label>
                 <input type="text" class="form-control" name="cantidad" id="cantidad" onKeyUp="this.value=this.value.toUpperCase();"onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '0', '.', '')" autocomplete="off" placeholder="Cantidad">
                 <i class="fa fa-bolt form-control-feedback"></i> 
                </div> 
            </div>
        </div>

        <div class="pull-right">
    <button type="button" class="btn btn-success" data-placement="left" title="Buscar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalBusqueda" data-backdrop="static" data-keyboard="false" onClick="MostrarProductos()"><i class="fa fa-search"></i> Búsqueda</button>
    <button type="button" id="AgregaProducto" class="btn btn-info"><span class="fa fa-cart-plus"></span> Agregar (Enter)</button>
        </div></br>

        <div class="table-responsive m-t-40">
            <table id="carrito" class="table2">
                <thead>
                    <tr class="text-center">
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
                        <td class="text-center" colspan=9><h4>NO HAY DETALLES AGREGADOS</h4></td>
                    </tr>
                </tbody>
              </table><hr>

              <table id="carritototal" class="table-responsive">
                <tr>
    <td width="250"><h5><label>Gravado (<?php echo number_format($valor, 2, '.', ''); ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal">0.00</label></h5>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="0.00"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2">0.00</label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="0.00"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($valor, 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo number_format($valor, 2, '.', ''); ?>"></label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva">0.00</label></h5>
    <input type="hidden" name="txtIva" id="txtIva" value="0.00"/>
    </td>
                </tr>
                <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado">0.00</label></h5>
    <input type="hidden" name="txtdescontado" id="txtdescontado" value="0.00"/>
        </td>
    
    <td>
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" style="border-radius:4px;height:30px;width:60px;"  onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($_SESSION['descsucursal'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento">0.00</label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="0.00"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal">0.00</label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="0.00"/>
    <input type="hidden" name="txtTotalCompra" id="txtTotalCompra" value="0.00"/>    </td>
                    </tr>
                  </table>
              
        </div>


<?php } else { ?>

        <input type="hidden" name="idproducto" id="idproducto">
        <input type="hidden" name="codproducto" id="codproducto">
        <input type="hidden" name="producto" id="producto">
        <input type="hidden" name="codcategoria" id="codcategoria">
        <input type="hidden" name="categorias" id="categorias">
        <input type="hidden" name="preciocompra" id="preciocompra"> 
        <input type="hidden" name="precioconiva" id="precioconiva">
        <input type="hidden" name="ivaproducto" id="ivaproducto">
        <input type="hidden" name="observacion" id="observacion">
        <input type="hidden" name="salsa" id="salsa">
        <input type="hidden" name="preparado" id="preparado">

        <h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Detalles de Factura</h2><hr>

        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label">Tipo de Búsqueda: <span class="symbol required"></span></label> 
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="name1" name="tipo" value="1" checked="checked" class="custom-control-input" onclick="VerificaDetalle()">
                        <label class="custom-control-label" for="name1">PRODUCTO</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="name2" name="tipo" value="2" class="custom-control-input" onclick="VerificaDetalle()">
                        <label class="custom-control-label" for="name2">COMBO</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="name3" name="tipo" value="3" class="custom-control-input" onclick="VerificaDetalle()">
                        <label class="custom-control-label" for="name3">EXTRA</label>
                    </div>
                </div>
            </div>

            <div class="col-md-3"> 
                <div class="form-group has-feedback"> 
                  <label class="control-label">Ingrese Descripción para Búsqueda: <span class="symbol required"></span></label>
                  <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="search_cotizacion" id="search_cotizacion" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Realice la Búsqueda por Código o Descripción">
                  <i class="fa fa-search form-control-feedback"></i> 
                </div> 
            </div>

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                <label class="control-label">Precio Unitario: <span class="symbol required"></span></label>
                <input class="form-control" type="text" name="precioventa" id="precioventa" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Precio de Venta">
                <i class="fa fa-dollar form-control-feedback"></i> 
               </div> 
            </div> 

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                 <label class="control-label">Stock Actual: <span class="symbol required"></span></label>
                 <input type="text" class="form-control" name="existencia" id="existencia" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Existencia" disabled="disabled" readonly="readonly">
                 <i class="fa fa-bolt form-control-feedback"></i> 
              </div> 
            </div>  

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Descuento: <span class="symbol required"></span></label>
                    <input class="form-control" type="text" name="descproducto" id="descproducto" onKeyPress="EvaluateText('%f', this);"  onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Descuento" value="0.00">
                    <i class="fa fa-tint form-control-feedback"></i> 
                </div> 
            </div>

            <div class="col-md-1"> 
                <div class="form-group has-feedback"> 
                 <label class="control-label">Cantidad: <span class="symbol required"></span></label>
                 <input type="text" class="form-control" name="cantidad" id="cantidad" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '0', '.', '')" autocomplete="off" placeholder="Cantidad">
                 <i class="fa fa-bolt form-control-feedback"></i> 
                </div> 
            </div>
        </div>

        <div class="pull-right">
    <button type="button" class="btn btn-success" data-placement="left" title="Buscar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalBusqueda" data-backdrop="static" data-keyboard="false" onClick="MostrarProductos()"><i class="fa fa-search"></i> Búsqueda</button>
    <button type="button" id="AgregaProducto" class="btn btn-info"><span class="fa fa-cart-plus"></span> Agregar (Enter)</button>
        </div></br>

        <div class="table-responsive m-t-40">
            <table id="carrito" class="table2">
                <thead>
                    <tr class="text-center">
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
                        <td class="text-center" colspan=9><h4>NO HAY DETALLES AGREGADOS</h4></td>
                    </tr>
                </tbody>
              </table>

            <hr>

             <table id="carritototal" class="table-responsive">
                <tr>
    <td width="250"><h5><label>Gravado (<?php echo number_format($valor, 2, '.', ''); ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal">0.00</label></h5>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="0.00"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2">0.00</label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="0.00"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($valor, 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo number_format($valor, 2, '.', ''); ?>"></label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva">0.00</label></h5>
    <input type="hidden" name="txtIva" id="txtIva" value="0.00"/>
    </td>
                </tr>
                <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado">0.00</label></h5>
    <input type="hidden" name="txtdescontado" id="txtdescontado" value="0.00"/>
        </td>
    
    <td>
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" style="border-radius:4px;height:30px;width:60px;" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($_SESSION['descsucursal'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento">0.00</label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="0.00"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal">0.00</label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="0.00"/>
    <input type="hidden" name="txtTotalCompra" id="txtTotalCompra" value="0.00"/>    </td>
                    </tr>
                  </table>

        </div>


<?php } ?> 

<div class="clearfix"></div>
<hr>
              <div class="text-right">
<?php  if (isset($_GET['codcotizacion']) && decrypt($_GET["proceso"])=="U") { ?>
<span id="submit_update"><button type="submit" name="btn-update" id="btn-update" class="btn btn-warning"><span class="fa fa-edit"></span> Actualizar</button></span>
<a href="cotizaciones"><button class="btn btn-dark" type="button"><span class="fa fa-trash-o"></span> Cancelar</button></a> 
<?php } else if (isset($_GET['codcotizacion']) && decrypt($_GET["proceso"])=="A") { ?>  
<span id="submit_agregar"><button type="submit" name="btn-agregar" id="btn-agregar" class="btn btn-warning"><span class="fa fa-plus-circle"></span> Agregar</button></span>
<button class="btn btn-dark" type="button" id="vaciar2"><span class="fa fa-trash-o"></span> Cancelar</button>
<?php } else { ?>  
<span id="submit_guardar"><button type="submit" name="btn-submit" id="btn-submit" class="btn btn-warning"><span class="fa fa-save"></span> Guardar (F2)</button></span>
<button class="btn btn-dark" type="button" id="vaciar"><i class="fa fa-trash-o"></i> Limpiar (F4)</button>
<?php } ?>
</div>

          </div>
       </div>
     </form>
   </div>
  </div>
<!--</div>
 End Row -->


</div>
<!-- End Row -->
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
    <script type="text/javascript" src="assets/script/jscotizaciones.js"></script>
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