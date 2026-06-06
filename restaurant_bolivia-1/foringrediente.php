<?php
require_once("class/class.php"); 
if(isset($_SESSION['acceso'])) { 
if ($_SESSION['acceso'] == "administradorG" || $_SESSION['acceso'] == "administradorS" || $_SESSION["acceso"]=="secretaria"){

$tra = new Login();
$ses = $tra->ExpiraSession(); 

$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "Impuesto" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']);

$con = new Login();
$con = $con->ConfiguracionPorId();

if(isset($_POST["proceso"]) and $_POST["proceso"]=="save")
{
$reg = $tra->RegistrarIngredientes();
exit;
} 
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="update")
{
$reg = $tra->ActualizarIngredientes();
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
        <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Gestión de Ingredientes</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Mantenimiento</li>
                                <li class="breadcrumb-item active" aria-current="page">Ingredientes</li>
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
                <h4 class="card-title text-white"><i class="fa fa-save"></i> Gestión de Ingredientes</h4>
            </div>

    <?php if (isset($_GET['codingrediente']) && isset($_GET['codsucursal'])) {
      
    $j = new Login();
    $reg = $j->IngredientesPorId(); ?>
    <form class="form form-material" method="post" action="#" name="updateingredientes" id="updateingredientes" enctype="multipart/form-data"> 
    <?php } else { ?> 
    <form class="form form-material" method="post" action="#" name="saveingredientes" id="saveingredientes" enctype="multipart/form-data"> 
    <?php } ?>

        <div id="save">
            <!-- error will be shown here ! -->
        </div>

        <div class="form-body">

        <div class="card-body">

        <?php if ($_SESSION['acceso'] == "administradorG") { ?> 
        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Seleccione Sucursal: <span class="symbol required"></span></label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <?php if (isset($reg[0]['codsucursal'])) { ?>
                    <select style="color:#000;font-weight:bold;" name="codsucursal" id="codsucursal" onChange="CargaMedidasxSucursal(this.form.codsucursal.value); CargaProveedoresxSucursal(this.form.codsucursal.value);" class="form-control" required= "" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <?php
                    $sucursal = new Login();
                    $sucursal = $sucursal->ListarSucursales();
                    if($sucursal==""){ 
                        echo "";
                    } else {
                    for($i=0;$i<sizeof($sucursal);$i++){ ?>
                    <option value="<?php echo encrypt($sucursal[$i]['codsucursal']) ?>"<?php if (!(strcmp($reg[0]['codsucursal'], htmlentities($sucursal[$i]['codsucursal'])))) { echo "selected=\"selected\""; } ?>><?php echo $sucursal[$i]['cuitsucursal'].": ".$sucursal[$i]['nomsucursal'] ?></option>        
                    <?php } } ?>
                    </select>
                    <?php } else { ?>
                    <select style="color:#000;font-weight:bold;" name="codsucursal" id="codsucursal" onChange="CargaMedidasxSucursal(this.form.codsucursal.value); CargaProveedoresxSucursal(this.form.codsucursal.value);" class="form-control" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <?php
                    $sucursal = new Login();
                    $sucursal = $sucursal->ListarSucursales();
                    if($sucursal==""){ 
                        echo "";
                    } else {
                    for($i=0;$i<sizeof($sucursal);$i++){ ?>
                    <option value="<?php echo encrypt($sucursal[$i]['codsucursal']); ?>"><?php echo $sucursal[$i]['cuitsucursal'].": ".$sucursal[$i]['nomsucursal'] ?></option>
                    <?php } } ?>
                    </select>
                    <?php } ?> 
                </div> 
            </div> 
        </div>
        <?php } else {  ?> 
        <input type="hidden" name="codsucursal" id="codsucursal" value="<?php echo encrypt($_SESSION["codsucursal"]); ?>">
        <?php } ?>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Código de Ingrediente: <span class="symbol required"></span></label>
                    <input type="hidden" name="idingrediente" id="idingrediente" <?php if (isset($reg[0]['idingrediente'])) { ?> value="<?php echo encrypt($reg[0]['idingrediente']); ?>"<?php } ?>>
                    <input type="hidden" name="proceso" id="proceso" <?php if (isset($reg[0]['idingrediente'])) { ?> value="update" <?php } else { ?> value="save" <?php } ?>/>
                    <input type="text" class="form-control" name="codingrediente" id="codingrediente" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Código de Ingrediente" autocomplete="off" <?php if (isset($reg[0]['codingrediente'])) { ?> value="<?php echo $reg[0]['codingrediente']; ?>" readonly="readonly" <?php } else { ?><?php } ?> required="" aria-required="true"/> 
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nombre de Ingrediente: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="nomingrediente" id="nomingrediente" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nombre de Ingrediente" autocomplete="off" <?php if (isset($reg[0]['nomingrediente'])) { ?> value="<?php echo $reg[0]['nomingrediente']; ?>" <?php } ?> required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Medida de Ingrediente: <span class="symbol required"></span></label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <?php if ($_SESSION['acceso'] == "administradorG" && !isset($reg[0]['codmedida'])) { ?>
                    <select style="color:#000;font-weight:bold;" name="codmedida" id="codmedida" class='form-control' required="" aria-required="true">
                    <option value=""> -- SIN RESULTADOS -- </option>
                    </select>
                    <?php } elseif (isset($reg[0]['codmedida'])) { ?>
                    <select style="color:#000;font-weight:bold;" name="codmedida" id="codmedida" class='form-control' required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <?php
                    $medida = new Login();
                    $medida = $medida->ListarMedidas();
                    if($medida==""){ 
                        echo "";
                    } else {
                    for($i=0;$i<sizeof($medida);$i++){ ?>
                    <option value="<?php echo encrypt($medida[$i]['codmedida']); ?>"<?php if (!(strcmp($reg[0]['codmedida'], htmlentities($medida[$i]['codmedida'])))) { echo "selected=\"selected\"";} ?>><?php echo $medida[$i]['nommedida'] ?></option>        
                    <?php } } ?>
                    </select>  
                     <?php } else { ?>
                    <select style="color:#000;font-weight:bold;" name="codmedida" id="codmedida" class='form-control' required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <?php
                    $medida = new Login();
                    $medida = $medida->ListarMedidas();
                    if($medida==""){
                          echo "";    
                    } else {
                    for($i=0;$i<sizeof($medida);$i++){ ?>
                     <option value="<?php echo encrypt($medida[$i]['codmedida']); ?>"><?php echo $medida[$i]['nommedida'] ?></option>        
                    <?php } } ?>
                  </select>
                     <?php } ?>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Precio de Compra: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="preciocompra" id="preciocompra" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Precio de Compra" autocomplete="off" <?php if (isset($reg[0]['preciocompra'])) { ?> value="<?php echo number_format($reg[0]['preciocompra'], 2, '.', ''); ?>" <?php } ?> required="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Precio de Venta (P.V.P): <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="precioventa" id="precioventa" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Precio de Venta" autocomplete="off" <?php if (isset($reg[0]['precioventa'])) { ?> value="<?php echo number_format($reg[0]['precioventa'], 2, '.', ''); ?>" <?php } ?>  required="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Cantidad de Ingrediente: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="cantingrediente" id="cantingrediente" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Cantidad de Ingrediente" autocomplete="off" <?php if (isset($reg[0]['cantingrediente'])) { ?> value="<?php echo $reg[0]['cantingrediente']; ?>" <?php } ?> required="" aria-required="true"/>  
                    <i class="fa fa-bolt form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Stock Minimo: <span class="symbol required"></span></label>
                     <input type="text" class="form-control" name="stockminimo" id="stockminimo" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Stock Minimo" autocomplete="off" <?php if (isset($reg[0]['stockminimo'])) { ?> value="<?php echo $reg[0]['stockminimo']; ?>" <?php } else { ?> value="0.00" <?php } ?> required="" aria-required="true"/>  
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Stock Máximo: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="stockmaximo" id="stockmaximo" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Stock Máximo" autocomplete="off" <?php if (isset($reg[0]['stockmaximo'])) { ?> value="<?php echo $reg[0]['stockmaximo']; ?>" <?php } else { ?> value="0.00" <?php } ?> required="" aria-required="true"/>  
                    <i class="fa fa-bolt form-control-feedback"></i>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label"><?php echo $impuesto; ?> de Ingrediente: <span class="symbol required"></span></label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <?php if (isset($reg[0]['ivaingrediente'])) { ?>
                    <select style="color:#000;font-weight:bold;" name="ivaingrediente" id="ivaingrediente" class="form-control" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="SI"<?php if (!(strcmp('SI', $reg[0]['ivaingrediente']))) {echo "selected=\"selected\"";} ?>>SI</option>
                    <option value="NO"<?php if (!(strcmp('NO', $reg[0]['ivaingrediente']))) {echo "selected=\"selected\"";} ?>>NO</option>
                    </select>
                    <?php } else { ?>
                    <select style="color:#000;font-weight:bold;" name="ivaingrediente" id="ivaingrediente" class="form-control" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="SI">SI</option>
                    <option value="NO">NO</option>
                    </select>
                    <?php } ?>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Descuento de Ingrediente: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="descingrediente" id="descingrediente" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Descuento de Ingrediente" autocomplete="off" <?php if (isset($reg[0]['descingrediente'])) { ?> value="<?php echo $reg[0]['descingrediente']; ?>" <?php } else { ?> value="0.00" <?php } ?> required="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Lote: </label>
                    <input type="text" class="form-control" name="lote" id="lote" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Lote" autocomplete="off" <?php if (isset($reg[0]['lote'])) { ?> value="<?php echo $reg[0]['lote']; ?>" <?php } ?> required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Fecha de Expiración: </label>
                <input type="text" class="form-control expira" name="fechaexpiracion" id="fechaexpiracion" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Fecha de Expiración" autocomplete="off" <?php if (isset($reg[0]['fechaexpiracion'])) { ?> value="<?php echo $reg[0]['fechaexpiracion'] == '0000-00-00' ? "" : date("d-m-Y",strtotime($reg[0]['fechaexpiracion'])); ?>"<?php } ?> required="" aria-required="true"/>  
                    <i class="fa fa-calendar form-control-feedback"></i>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Preparado: <span class="symbol required"></span></label> 
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="n1" name="preparado" value="1" <?php if (isset($reg[0]['preparado']) && $reg[0]['preparado'] == "1") { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?> class="custom-control-input">
                    <label class="custom-control-label" for="n1">COCINA</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="n3" name="preparado" value="3" <?php if (isset($reg[0]['preparado']) && $reg[0]['preparado'] == "3") { ?> checked="checked" <?php } ?> class="custom-control-input">
                    <label class="custom-control-label" for="n3">PASTELERIA</label>
                    </div><br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="n2" name="preparado" value="2" <?php if (isset($reg[0]['preparado']) && $reg[0]['preparado'] == "2") { ?> checked="checked" <?php } ?> class="custom-control-input">
                    <label class="custom-control-label" for="n2">BAR</label>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Favorito: <span class="symbol required"></span></label> 
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="name1" name="favorito" value="1" <?php if (isset($reg[0]['favorito']) && $reg[0]['favorito'] == "1") { ?> checked="checked" <?php } ?> class="custom-control-input">
                    <label class="custom-control-label" for="name1">SI</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="name2" name="favorito" value="0" <?php if (isset($reg[0]['favorito'])) { ?> <?php if($reg[0]['favorito'] == "0") { ?> checked="checked" <?php } } else { ?> checked="checked" <?php } ?>  class="custom-control-input">
                    <label class="custom-control-label" for="name2">NO</label>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Control de Stock: <span class="symbol required"></span></label> 
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="control1" name="controlstocki" value="1" <?php if (isset($reg[0]['controlstocki']) && $reg[0]['controlstocki'] == "1") { ?> checked="checked" <?php } ?> class="custom-control-input">
                    <label class="custom-control-label" for="control1">SI</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="control2" name="controlstocki" value="0" <?php if (isset($reg[0]['controlstocki'])) { ?> <?php if($reg[0]['controlstocki'] == "0") { ?> checked="checked" <?php } } else { ?> checked="checked" <?php } ?> class="custom-control-input">
                    <label class="custom-control-label" for="control2">NO</label>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Seleccione Proveedor: </label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <?php if ($_SESSION['acceso'] == "administradorG" && !isset($reg[0]['codproveedor'])) { ?>
                    <select style="color:#000;font-weight:bold;" name="codproveedor" id="codproveedor" class='form-control' required="" aria-required="true">
                    <option value=""> -- SIN RESULTADOS -- </option>
                    </select>
                    <?php } elseif (isset($reg[0]['codproveedor'])) { ?>
                    <select style="color:#000;font-weight:bold;" name="codproveedor" id="codproveedor" class='form-control' required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <?php
                    $proveedor = new Login();
                    $proveedor = $proveedor->ListarProveedores();
                    if($proveedor==""){
                        echo "";    
                    } else {
                    for($i=0;$i<sizeof($proveedor);$i++){ ?>
                    <option value="<?php echo encrypt($proveedor[$i]['codproveedor']); ?>"<?php if (!(strcmp($reg[0]['codproveedor'], htmlentities($proveedor[$i]['codproveedor'])))) {echo "selected=\"selected\""; } ?>><?php echo $proveedor[$i]['nomproveedor'] ?></option>        
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
                    <option value="<?php echo encrypt($proveedor[$i]['codproveedor']); ?>"><?php echo $proveedor[$i]['nomproveedor'] ?></option>        
                    <?php } } ?>
                    </select>
                    <?php } ?> 
                </div>
            </div>
        </div>

            <div class="text-right">
    <?php  if (isset($_GET['codingrediente'])) { ?>
<button type="submit" name="btn-update" id="btn-update" class="btn btn-warning"><span class="fa fa-edit"></span> Actualizar</button>
<button class="btn btn-dark" type="reset"><span class="fa fa-trash-o"></span> Cancelar</button> 
    <?php } else { ?>
<button type="submit" name="btn-submit" id="btn-submit" class="btn btn-warning"><span class="fa fa-save"></span> Guardar</button>
<button class="btn btn-dark" type="reset"><span class="fa fa-trash-o"></span> Limpiar</button>
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

    <!-- Custom file upload -->
    <script src="assets/plugins/fileupload/bootstrap-fileupload.min.js"></script>

    <!-- script jquery -->
    <script type="text/javascript" src="assets/script/titulos.js"></script>
    <script type="text/javascript" src="assets/script/script2.js"></script>
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