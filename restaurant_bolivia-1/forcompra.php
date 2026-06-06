<?php
require_once("class/class.php"); 
if(isset($_SESSION['acceso'])) { 
if ($_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="secretaria") {

$tra = new Login();
$ses = $tra->ExpiraSession(); 

$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "Impuesto" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']);
$simbolo = ($_SESSION["acceso"] == "administradorG" ? "" : "<strong>".$_SESSION["simbolo"]."</strong>");

if(isset($_POST["proceso"]) and $_POST["proceso"]=="save")
{
   $reg = $tra->RegistrarCompras();
   exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="update")
{
   $reg = $tra->ActualizarCompras();
   exit;
} 
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="newproveedor")
{
   $reg = $tra->RegistrarProveedores();
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
    <!-- color alert -->
    <link rel="stylesheet" type="text/css" href="assets/css/alert.css">
    <!-- cache 
    <script type="text/javascript" src="assets/js/cssrefresh.js"></script>-->

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

<!--############################## MODAL PARA REGISTRO DE NUEVO PROVEEDOR ######################################-->
<!-- sample modal content -->
<div id="myModalProveedor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-save"></i> Gestión de Proveedor</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <form class="form form-material" method="post" action="#" name="saveproveedor" id="saveproveedor"> 

                
        <div class="modal-body">

        <div class="row">
            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Tipo de Documento: </label>
                    <i class="fa fa-bars form-control-feedback"></i> 
                    <select style="color:#000;font-weight:bold;" name="documproveedor" id="documproveedor" class='form-control' required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <?php
                    $doc = new Login();
                    $doc = $doc->ListarDocumentos();
                    if($doc==""){
                        echo "";    
                    } else {
                    for($i=0;$i<sizeof($doc);$i++){ ?>
                    <option value="<?php echo $doc[$i]['coddocumento'] ?>"><?php echo $doc[$i]['documento'] ?></option>
                    <?php } } ?>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Documento: <span class="symbol required"></span></label>
                    <input type="hidden" name="proceso" id="proceso" value="newproveedor"/>
                    <input type="hidden" name="formulario" id="formulario" value="forcompra"/>
                    <input type="hidden" name="codsucursal" id="codsucursal" value="<?php echo encrypt($_SESSION['codsucursal']); ?>">
                    <input type="text" class="form-control" name="cuitproveedor" id="cuitproveedor" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Documento" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Nombre de Proveedor: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="nomproveedor" id="nomproveedor" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nombre de Proveedor" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Teléfono: <span class="symbol required"></span></label>
                    <input type="text" class="form-control phone-inputmask" name="tlfproveedor" id="tlfproveedor" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Teléfono" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-phone form-control-feedback"></i> 
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

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Departamento: </label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <select style="color:#000;font-weight:bold;" class="form-control" id="id_departamento" name="id_departamento" required="" aria-required="true">
                    <option value=""> -- SIN RESULTADOS -- </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Dirección de Proveedor: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="direcproveedor" id="direcproveedor" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Dirección de Proveedor" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-map-marker form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Correo de Proveedor: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="emailproveedor" id="emailproveedor" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Correo Electronico" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-envelope-o form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Nombre de Vendedor: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="vendedor" id="vendedor" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nombre de Vendedor" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Teléfono: <span class="symbol required"></span></label>
                    <input type="text" class="form-control phone-inputmask" name="tlfvendedor" id="tlfvendedor" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Teléfono" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-phone form-control-feedback"></i> 
                </div>
            </div>
        </div>
    </div>

            <div class="modal-footer">
                <button type="submit" name="btn-proveedor" id="btn-proveedor" class="btn btn-warning"><span class="fa fa-save"></span> Guardar</button>
                <button class="btn btn-dark" type="button" onclick="
                document.getElementById('documproveedor').value = '',
                document.getElementById('cuitproveedor').value = '',
                document.getElementById('nomproveedor').value = '',
                document.getElementById('tlfproveedor').value = '',
                document.getElementById('id_provincia').value = '',
                document.getElementById('id_departamento').value = '',
                document.getElementById('direcproveedor').value = '',
                document.getElementById('emailproveedor').value = '',
                document.getElementById('vendedor').value = '',
                document.getElementById('tlfvendedor').value = ''
                " data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cerrar</button>
            </div>
        </form>

    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal --> 
<!--############################## MODAL PARA REGISTRO DE NUEVO PROVEEDOR ######################################-->      
                    
    
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
                    <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Gestión de Compras</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Compras</li>
                                <li class="breadcrumb-item active" aria-current="page">Gestión de Compras</li>
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
            <h4 class="card-title text-white"><i class="fa fa-save"></i> Gestión de Compras</h4>
            </div>

<?php if (isset($_GET['codcompra']) && isset($_GET['codsucursal']) && decrypt($_GET["proceso"])=="U") {
      
$j = new Login();
$reg = $j->ComprasPorId(); ?>
      
<form class="form form-material" method="post" action="#" name="updatecompras" id="updatecompras" data-id="<?php echo $reg[0]["codcompra"]; ?>">
        
<?php } else { ?>
        
<form class="form form-material" method="post" action="#" name="savecompras" id="savecompras">

<?php } ?>
           
    <div id="save">
    <!-- error will be shown here ! -->
    </div>

    <div class="form-body">

    <div class="card-body">

    <input type="hidden" name="proceso" id="proceso" <?php if (isset($reg[0]['idcompra'])) { ?> value="update" <?php } else { ?> value="save" <?php } ?>/>
    <input type="hidden" name="idcompra" id="idcompra" <?php if (isset($reg[0]['idcompra'])) { ?> value="<?php echo $reg[0]['idcompra']; ?>"<?php } ?>>
     <input type="hidden" name="codcompra" id="codcompra" <?php if (isset($reg[0]['codcompra'])) { ?> value="<?php echo encrypt($reg[0]['codcompra']); ?>"<?php } ?>>
    <input type="hidden" name="compra" id="compra" <?php if (isset($reg[0]['codcompra'])) { ?> value="<?php echo encrypt($reg[0]['codcompra']); ?>"<?php } ?>> 

    <input type="hidden" name="codsucursal" id="codsucursal" <?php if (isset($reg[0]['codsucursal'])) { ?> value="<?php echo encrypt($reg[0]['codsucursal']); ?>" <?php } else { ?> value="<?php echo encrypt($_SESSION["codsucursal"]); ?>"<?php } ?>>
    <input type="hidden" name="sucursal" id="sucursal" <?php if (isset($reg[0]['codsucursal'])) { ?> value="<?php echo encrypt($reg[0]['codsucursal']); ?>" <?php } ?>>
    
    <input type="hidden" name="status" id="status" <?php if (isset($reg[0]['idcompra'])) { ?> value="<?php echo decrypt($_GET["status"]); ?>" <?php } ?>>
       
    <h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-file-send"></i> Datos de Factura</h2><hr>

    <div class="row"> 
        <div class="col-md-3"> 
            <div class="form-group has-feedback"> 
                <label class="control-label">N° de Factura: <span class="symbol required"></span></label>
                <input type="hidden" name="formulario" id="formulario" value="forcompra">
                <input class="form-control" type="text" name="codfactura" id="codfactura" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="N° Compra" <?php if (isset($reg[0]['codfactura'])) { ?> value="<?php echo $reg[0]['codfactura']; ?>" <?php } else { ?> required="" aria-required="true" <?php } ?>>
                <i class="fa fa-flash form-control-feedback"></i> 
            </div> 
        </div>

        <div class="col-md-3"> 
            <div class="form-group has-feedback"> 
                <label class="control-label">Fecha de Emisión: <span class="symbol required"></span></label> 
                <input type="text" class="form-control calendario" name="fechaemision" id="fechaemision" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Fecha Emisión" <?php if (isset($reg[0]['fechaemision'])) { ?> value="<?php echo $reg[0]['fechaemision'] == '0000-00-00' ? "" : date("d-m-Y",strtotime($reg[0]['fechaemision'])); ?>"<?php } ?> required="" aria-required="true">
                <i class="fa fa-calendar form-control-feedback"></i>  
            </div> 
        </div>

        <div class="col-md-3"> 
            <div class="form-group has-feedback"> 
                <label class="control-label">Fecha de Recepción: <span class="symbol required"></span></label> 
                <input type="text" class="form-control calendario" name="fecharecepcion" id="fecharecepcion" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Fecha Recepción" <?php if (isset($reg[0]['fecharecepcion'])) { ?> value="<?php echo $reg[0]['fecharecepcion'] == '0000-00-00' ? "" : date("d-m-Y",strtotime($reg[0]['fecharecepcion'])); ?>"<?php } ?> required="" aria-required="true">
                 <i class="fa fa-calendar form-control-feedback"></i>  
            </div> 
        </div>

       <div class="col-md-3">
            <label class="control-label">Seleccione Proveedor: <span class="symbol required"></span></label>
            <div class="input-group mb-3">
            <div class="input-group-append">
            <button type="button" class="btn btn-success waves-effect waves-light" data-placement="left" title="Nuevo Proveedor" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalProveedor" data-backdrop="static" data-keyboard="false"><i class="fa fa-user-plus"></i> (F7)</button>
            </div>
            <?php if (isset($reg[0]['codproveedor'])) { ?>
            <select style="color:#000;font-weight:bold;" name="codproveedor" id="codproveedor" class='form-control' required="" aria-required="true">
            <option value=""> -- SELECCIONE -- </option>
            <?php
            $proveedor = new Login();
            $proveedor = $proveedor->ListarProveedores();
            if($proveedor==""){ 
              echo "";
            } else {
            for($i=0;$i<sizeof($proveedor);$i++){ ?>
            <option value="<?php echo $proveedor[$i]['codproveedor'] ?>"<?php if (!(strcmp($reg[0]['codproveedor'], htmlentities($proveedor[$i]['codproveedor'])))) {echo "selected=\"selected\""; } ?>><?php echo $proveedor[$i]['nomproveedor'] ?></option>        
            <?php } } ?>
            </select>
            <?php } else { ?>
            <select style="color:#000;font-weight:bold;" name="codproveedor" id="codproveedor" class='form-control' required="" aria-required="true">
            <option value=""> -- SELECCIONE -- </option>
            <?php
            $proveedor = new Login();
            $proveedor = $proveedor->ListarProveedores();
            if($proveedor==""){ 
                echo "";
            } else {
            for($i=0;$i<sizeof($proveedor);$i++){ ?>
            <option value="<?php echo $proveedor[$i]['codproveedor'] ?>"><?php echo $proveedor[$i]['nomproveedor'] ?></option>
            <?php } } ?>
            </select>
            <?php } ?> 
            </div>
        </div>
    </div>


    <div class="row"> 
        <div class="col-md-3"> 
            <div class="form-group has-feedback"> 
                <label class="control-label">Tipo de Compra: <span class="symbol required"></span></label>
                <i class="fa fa-bars form-control-feedback"></i>
                <?php if (isset($reg[0]['tipocompra'])) { ?>
                <select style="color:#000;font-weight:bold;" name="tipocompra" id="tipocompra" class="form-control" onChange="CargaFormaPagosCompras()" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="CONTADO"<?php if (!(strcmp('CONTADO', $reg[0]['tipocompra']))) {echo "selected=\"selected\"";} ?>>CONTADO</option>
                <option value="CREDITO"<?php if (!(strcmp('CREDITO', $reg[0]['tipocompra']))) {echo "selected=\"selected\"";} ?>>CRÉDITO</option>
                </select>
                <?php } else { ?>
                <select style="color:#000;font-weight:bold;" name="tipocompra" id="tipocompra" class="form-control" onChange="CargaFormaPagosCompras()" required="" aria-required="true">
                <option value=""> -- SELECCIONE -- </option>
                <option value="CONTADO">CONTADO</option>
                <option value="CREDITO">CRÉDITO</option>
                </select>
                <?php } ?>
           </div> 
        </div>

        <div class="col-md-3"> 
            <div class="form-group has-feedback"> 
                <label class="control-label">Forma de Pago: <span class="symbol required"></span></label>
                <i class="fa fa-bars form-control-feedback"></i>

            <?php if (isset($reg[0]['formacompra']) && $reg[0]['tipocompra']=="CONTADO") { ?>

                <select style="color:#000;font-weight:bold;" name="formacompra" id="formacompra" class="form-control" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="EFECTIVO"<?php if (!(strcmp('EFECTIVO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>EFECTIVO</option>
                    <option value="CHEQUE"<?php if (!(strcmp('CHEQUE', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>CHEQUE</option>
                    <option value="TARJETA DE CREDITO"<?php if (!(strcmp('TARJETA DE CREDITO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>TARJETA DE CRÉDITO</option>
                    <option value="TARJETA DE DEBITO"<?php if (!(strcmp('TARJETA DE DEBITO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>TARJETA DE DÉBITO</option>
                    <option value="TARJETA PREPAGO"<?php if (!(strcmp('TARJETA PREPAGO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>TARJETA PREPAGO</option>
                    <option value="TRANSFERENCIA"<?php if (!(strcmp('TRANSFERENCIA', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>TRANSFERENCIA</option>
                    <option value="DINERO ELECTRONICO"<?php if (!(strcmp('DINERO ELECTRONICO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>DINERO ELECTRÓNICO</option>
                    <option value="CUPON"<?php if (!(strcmp('CUPON', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>CUPÓN</option>
                    <option value="OTROS"<?php if (!(strcmp('OTROS', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>OTROS</option>
                </select>

            <?php } elseif (isset($reg[0]['formacompra']) && $reg[0]['tipocompra']=="CREDITO") { ?>

                <select style="color:#000;font-weight:bold;" name="formacompra" id="formacompra" class="form-control" disabled="" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="EFECTIVO"<?php if (!(strcmp('EFECTIVO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>EFECTIVO</option>
                    <option value="CHEQUE"<?php if (!(strcmp('CHEQUE', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>CHEQUE</option>
                    <option value="TARJETA DE CREDITO"<?php if (!(strcmp('TARJETA DE CREDITO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>TARJETA DE CRÉDITO</option>
                    <option value="TARJETA DE DEBITO"<?php if (!(strcmp('TARJETA DE DEBITO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>TARJETA DE DÉBITO</option>
                    <option value="TARJETA PREPAGO"<?php if (!(strcmp('TARJETA PREPAGO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>TARJETA PREPAGO</option>
                    <option value="TRANSFERENCIA"<?php if (!(strcmp('TRANSFERENCIA', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>TRANSFERENCIA</option>
                    <option value="DINERO ELECTRONICO"<?php if (!(strcmp('DINERO ELECTRONICO', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>DINERO ELECTRÓNICO</option>
                    <option value="CUPON"<?php if (!(strcmp('CUPON', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>CUPÓN</option>
                    <option value="OTROS"<?php if (!(strcmp('OTROS', $reg[0]['formacompra']))) {echo "selected=\"selected\"";} ?>>OTROS</option>
                </select>

            <?php } else { ?>

                <select style="color:#000;font-weight:bold;" name="formacompra" id="formacompra" class="form-control" disabled="" required="" aria-required="true">
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
            <?php } ?>

            </div> 
        </div>


        <?php if (isset($reg[0]['fechavencecredito']) && $reg[0]['tipocompra']=="CONTADO") { ?>

        <div class="col-md-3"> 
            <div class="form-group has-feedback"> 
               <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
               <input type="text" class="form-control" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Fecha Vence Crédito" <?php if (isset($reg[0]['fechavencecredito'])) { ?> value="<?php echo $reg[0]['fechavencecredito'] == '0000-00-00' ? "" : date("d-m-Y",strtotime($reg[0]['fechavencecredito'])); ?>"<?php } ?> disabled="" required="" aria-required="true">
               <i class="fa fa-calendar form-control-feedback"></i>  
            </div> 
        </div> 

        <?php } elseif (isset($reg[0]['fechavencecredito']) && $reg[0]['tipocompra']=="CREDITO") { ?>

        <div class="col-md-3"> 
            <div class="form-group has-feedback"> 
               <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
               <input type="text" class="form-control" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Fecha Vence Crédito" <?php if (isset($reg[0]['fechavencecredito'])) { ?> value="<?php echo $reg[0]['fechavencecredito'] == '0000-00-00' ? "" : date("d-m-Y",strtotime($reg[0]['fechavencecredito'])); ?>"<?php } ?> required="" aria-required="true">
               <i class="fa fa-calendar form-control-feedback"></i>  
            </div> 
        </div> 

        <?php } else { ?>

        <div class="col-md-3"> 
            <div class="form-group has-feedback"> 
               <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
               <input type="text" class="form-control" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Fecha Vence Crédito" disabled="" required="" aria-required="true">
               <i class="fa fa-calendar form-control-feedback"></i>  
            </div> 
        </div>  

            <?php } ?>


        <div class="col-md-3"> 
            <div class="form-group has-feedback"> 
                <label class="control-label">Observaciones: </label>
                <input class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" <?php if (isset($reg[0]['observaciones'])) { ?> value="<?php echo $reg[0]['observaciones']; ?>" <?php } ?> required="" aria-required="true">
                <i class="fa fa-comments form-control-feedback"></i> 
            </div> 
        </div>   

    </div>


<?php if (isset($_GET['codcompra']) && isset($_GET['codsucursal']) && decrypt($_GET["proceso"])=="U") { ?>

<h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Detalles de Factura</h2><hr>

<div id="detallescomprasupdate">

    <div class="table-responsive m-t-20">
            <table class="table2 table-hover">
                <thead>
                    <tr class="text-center">
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Precio Unitario</th>
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
$detalle = $tra->VerDetallesCompras();
$a=1;
$count = 0;
for($i=0;$i<sizeof($detalle);$i++){ 
$count++; 
?>
    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
    <td>
    <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
    <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetalleCompra('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
    <input type="text" class="bold" name="cantcompra[]" id="cantcompra_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoCompra(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="<?php echo number_format($detalle[$i]["cantcompra"], 2, '.', ''); ?>" title="Ingrese Cantidad">
    <input type="hidden" name="cantidadcomprabd[]" id="cantidadcomprabd_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["cantcompra"], 2, '.', ''); ?>">
    <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetalleCompra('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
    </div>
    </td>

    <td class="text-danger alert-link">
    <input type="hidden" name="coddetallecompra[]" id="coddetallecompra" value="<?php echo $detalle[$i]["coddetallecompra"]; ?>">
    <input type="hidden" name="tipo[]" id="tipo" value="<?php echo $detalle[$i]["tipo"]; ?>">
    <input type="hidden" name="codproducto[]" id="codproducto" value="<?php echo $detalle[$i]["codproducto"]; ?>">
    <input type="hidden" name="precioventa[]" id="precioventa" value="<?php echo number_format($detalle[$i]["precioventac"], 2, '.', ''); ?>">
    <?php if($detalle[$i]['tipo'] == 1){
        echo "PRODUCTO";
    } elseif($detalle[$i]['tipo'] == 2){
        echo "INGREDIENTE";
    } ?></td>

    <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
    <small class="text-danger alert-link"><?php echo $detalle[$i]['tipo'] == 1 ? $detalle[$i]['nomcategoria'] : $detalle[$i]['nommedida']; ?></small></label></td>
            
    <td><strong><input type="hidden" name="preciocompra[]" id="preciocompra_<?php echo $count; ?>" value="<?php echo $detalle[$i]["preciocomprac"]; ?>">
    <input type="hidden" name="precioconiva[]" id="precioconiva_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproductoc'] == '0.00' ? "0.00" : number_format($detalle[$i]["preciocomprac"], 2, '.', ''); ?>"><?php echo number_format($detalle[$i]['preciocomprac'], 2, '.', ','); ?></strong></td>

    <td><input type="hidden" name="valortotal[]" id="valortotal_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["valortotal"], 2, '.', ''); ?>"><label id="txtvalortotal_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></label></td>

    <td>
    <input type="hidden" name="descfactura[]" id="descfactura_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["descfactura"], 2, '.', ''); ?>">
    <input type="hidden" class="totaldescuentoc" name="totaldescuentoc[]" id="totaldescuentoc_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["totaldescuentoc"], 2, '.', ''); ?>">
    <label id="txtdescproducto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['totaldescuentoc'], 2, '.', ','); ?></label><sup><label><?php echo number_format($detalle[$i]['descfactura'], 2, '.', ','); ?>%</label></sup></td>

    <td><input type="hidden" name="ivaproducto[]" id="ivaproducto_<?php echo $count; ?>" value="<?php echo $detalle[$i]["ivaproductoc"]; ?>"><label><?php echo $detalle[$i]['ivaproductoc'] != '0.00' ? number_format($detalle[$i]['ivaproductoc'], 2, '.', '')."%" : "(E)"; ?></label></td>

    <td><input type="hidden" class="subtotalivasi" name="subtotalivasi[]" id="subtotalivasi_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproductoc'] != '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalivano" name="subtotalivano[]" id="subtotalivano_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproductoc'] == '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalimpuestos" name="subtotalimpuestos[]" id="subtotalimpuestos_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['subtotalimpuestos'], 2, '.', ''); ?>">

    <input type="hidden" class="subtotaldiscriminado" name="subtotaldiscriminado[]" id="subtotaldiscriminado_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproductoc'] != '0.00' ? number_format($detalle[$i]['valorneto']-$detalle[$i]['subtotalimpuestos'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="valorneto" name="valorneto[]" id="valorneto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto'], 2, '.', ''); ?>" ><label id="txtvalorneto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></label></td>

 <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
<button type="button" class="btn btn-rounded btn-dark" onClick="EliminarDetallesComprasUpdate('<?php echo encrypt($detalle[$i]["coddetallecompra"]); ?>','<?php echo encrypt($detalle[$i]["codcompra"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[0]["codproveedor"]); ?>','<?php echo encrypt("DETALLESCOMPRAS") ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                 </tr>
                     <?php } ?>
                </tbody>
            </table><hr>

             <table id="carritototal" class="table-responsive">
            <tr>
    <td width="250"><h5><label>Gravado (<?php echo $reg[0]['ivac'] ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal"><?php echo number_format($reg[0]['subtotalivasic'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdiscriminado" id="txtdiscriminado" value="<?php echo number_format($reg[0]['subtotalivasic'], 0, '.', ''); ?>"/>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="<?php echo number_format($reg[0]['subtotalivasic'], 2, '.', ''); ?>"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    
    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2"><?php echo number_format($reg[0]['subtotalivanoc'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="<?php echo number_format($reg[0]['subtotalivanoc'], 2, '.', ''); ?>"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['ivac'], 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo number_format($reg[0]['ivac'], 2, '.', ''); ?>"></label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva"><?php echo number_format($reg[0]['totalivac'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtIva" id="txtIva" value="<?php echo number_format($reg[0]['totalivac'], 2, '.', ''); ?>"/>
    </td>
    </tr>
    <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado"><?php echo number_format($reg[0]['descontadoc'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdescontado" id="txtdescontado" value="<?php echo number_format($reg[0]['descontadoc'], 2, '.', ''); ?>"/>
        </td>
    
    <td>
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" style="border-radius:4px;height:30px;width:60px;" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['descuentoc'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento"><?php echo number_format($reg[0]['totaldescuentoc'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="<?php echo number_format($reg[0]['totaldescuentoc'], 2, '.', ''); ?>"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal"><?php echo number_format($reg[0]['totalpagoc'], 2, '.', ','); ?></label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="<?php echo number_format($reg[0]['totalpagoc'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtTotal2" id="txtTotal2" value="<?php echo number_format($reg[0]['totalpagoc'], 2, '.', ''); ?>"/>
    </td>
                    </tr>
    </table>

        </div>

</div>

<?php } else { ?>

        <input type="hidden" name="codproducto" id="codproducto"/>
        <input type="hidden" name="producto" id="producto">
        <input type="hidden" name="codcategoria" id="codcategoria">
        <input type="hidden" name="categorias" id="categorias">

     <h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Detalles de Factura</h2><hr>

        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                    <label class="control-label">Tipo de Búsqueda: <span class="symbol required"></span></label> 
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="name1" name="tipo" value="1" checked="checked" class="custom-control-input">
                        <label class="custom-control-label" for="name1">PRODUCTO</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="name2" name="tipo" value="2" class="custom-control-input">
                        <label class="custom-control-label" for="name2">INGREDIENTE</label>
                    </div>
                </div>
            </div>

            <div class="col-md-4"> 
                <div class="form-group has-feedback"> 
                   <label class="control-label">Ingrese Descripción para Búsqueda: <span class="symbol required"></span></label>
                   <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="search_compra" id="search_compra" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Realice la Búsqueda por Código o Descripción">
                   <i class="fa fa-search form-control-feedback"></i> 
                </div> 
            </div>

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Cantidad Compra: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="cantidad" id="cantidad" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Cantidad" autocomplete="off">
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div> 
            </div>

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Dcto en Compra: <span class="symbol required"></span></label>
                    <input class="form-control" type="text" name="descfactura" id="descfactura" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Descuento en Compra" value="0.00">
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div> 
            </div>

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Dcto en Venta: <span class="symbol required"></span></label>
                    <input class="form-control" type="text" name="descproducto" id="descproducto" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Descuento en Venta" value="0.00">
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div> 
            </div>
        </div>
 
        <div class="row">
            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label"><?php echo $impuesto; ?> de Producto: <span class="symbol required"></span></label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <select style="color:#000;font-weight:bold;" name="ivaproducto" id="ivaproducto" class="form-control">
                        <option value="">SELECCIONE</option>
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                    </select> 
                </div> 
            </div>

            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Precio de Compra: <span class="symbol required"></span></label>
                    <input type="hidden" name="porcentaje" id="porcentaje" value="0.00">
                    <input class="form-control calculoprecio" type="text" name="preciocompra" id="preciocompra" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Precio de Compra">
                    <input type="hidden" name="precioconiva" id="precioconiva" value="0.00">                        
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div> 
            </div>                                                                                                                             
            <div class="col-md-2"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Precio de Venta: <span class="symbol required"></span></label>
                    <input class="form-control" type="text" name="precioventa" id="precioventa" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Precio de Venta" value="0.00">
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div> 
            </div> 

            <div class="col-md-2">
                <div class="form-group has-feedback"> 
                    <label class="control-label">N° de Lote: <span class="symbol required"></span></label>
                    <input class="form-control" type="text" name="lote" id="lote" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="N° de Lote" value="0">
                    <i class="fa fa-flash form-control-feedback"></i> 
                </div> 
            </div>
                    
            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Fecha de Elaboración: </label>
                    <input type="text" class="form-control calendario" name="fechaelaboracion" id="fechaelaboracion" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Fecha de Elab." autocomplete="off"/>
                    <i class="fa fa-calendar form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Fecha de Expiración: </label>
                    <input type="text" class="form-control expira" name="fechaexpiracion" id="fechaexpiracion" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Fecha de Exp." autocomplete="off"/>  
                    <i class="fa fa-calendar form-control-feedback"></i>
                </div>
            </div>
        </div>  

        <div class="pull-right">
    <button type="button" id="AgregaProducto" class="btn btn-info"><span class="fa fa-cart-plus"></span> Agregar (Enter)</button>
        </div></br>

        <div class="table-responsive m-t-40">
            <table id="carrito" class="table table-hover">
                <thead>
                    <tr class="text-center">
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th>Impuesto</th>
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
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" style="border-radius:4px;height:30px;width:60px;" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="0.00">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento">0.00</label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="0.00"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal">0.00</label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="0.00"/>
    </td>
        </tr>
        </table>
        </div>


<?php } ?> 

<div class="clearfix"></div>
<hr>
              <div class="text-right">
<?php  if (isset($_GET['codcompra']) && decrypt($_GET["proceso"])=="U") { ?>
<span id="submit_update"><button type="submit" name="btn-update" id="btn-update" class="btn btn-warning"><span class="fa fa-edit"></span> Actualizar</button></span>
<button class="btn btn-dark" type="reset"><span class="fa fa-trash-o"></span> Cancelar</button> 
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
</div>
<!-- End Row -->
            <!-- ============================================================== -->
            <!-- End PAge Content -->
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
    <script type="text/javascript" src="assets/script/script2.js"></script>
    <script type="text/javascript" src="assets/script/jscompras.js"></script>
    <script type="text/javascript" src="assets/script/validation.min.js"></script>
    <script type="text/javascript" src="assets/script/script.js"></script>
    <!-- script jquery -->

    <!-- Calendario -->
    <link rel="stylesheet" href="assets/calendario/jquery-ui.css"/>
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