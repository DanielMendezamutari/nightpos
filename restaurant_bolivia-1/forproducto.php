<?php
require_once("class/class.php"); 
if(isset($_SESSION['acceso'])) { 
if ($_SESSION['acceso'] == "administradorG" || $_SESSION['acceso'] == "administradorS" || $_SESSION["acceso"]=="secretaria") {

$tra = new Login();
$ses = $tra->ExpiraSession();

$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "Impuesto" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']); 

$con = new Login();
$con = $con->ConfiguracionPorId();
$simbolo = ($_SESSION["acceso"] == "administradorG" ? "" : "<strong>".$_SESSION["simbolo"]."</strong>");

if(isset($_POST["proceso"]) and $_POST["proceso"]=="save")
{
$reg = $tra->RegistrarProductos();
exit;
} 
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="update")
{
$reg = $tra->ActualizarProductos();
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
                    <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Gestión de Productos</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Mantenimiento</li>
                                <li class="breadcrumb-item active" aria-current="page">Productos</li>
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
 
    <?php if (isset($_GET['codproducto']) && isset($_GET['codsucursal'])) {
      
    $j = new Login();
    $reg = $j->ProductosPorId(); ?>
      
    <form class="form form-material" method="post" action="#" name="updateproductos" id="updateproductos" data-id="<?php echo $reg[0]["codproducto"]; ?>" enctype="multipart/form-data">
        
    <?php } else { ?>
        
    <form class="form form-material" method="post" action="#" name="saveproductos" id="saveproductos" enctype="multipart/form-data">
      
    <?php } ?>


<!-- Row -->
<div class="row">
    
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-file-photo-o"></i> Imagen de Producto</h4>
            </div>
            

            <div class="form-body">

                <div class="card-body">

                <div class="row">
                    <div class="col-md-12">
                      <div class="text-center"><div class="fileinput fileinput-new" data-provides="fileinput">
                          <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 220px; height: 230px;">
                            <?php 
                            if (isset($reg[0]['codproducto'])) {
                                if (file_exists("fotos/productos/".$reg[0]["codproducto"].".jpg")){
                                echo "<img src='fotos/productos/".$reg[0]['codproducto'].".jpg?".date('h:i:s')."' class='rounded-circle' width='220' height='230'>";
                                } else if (file_exists("fotos/productos/".$reg[0]["codproducto"].".jpeg")){
                                echo "<img src='fotos/productos/".$reg[0]["codproducto"].".jpeg?".date('h:i:s')."' class='rounded-circle' width='220' height='230'>";
                                } else if (file_exists("fotos/productos/".$reg[0]["codproducto"].".png")){
                                echo "<img src='fotos/productos/".$reg[0]["codproducto"].".png?".date('h:i:s')."' class='rounded-circle' width='220' height='230'>";
                                } } else {
                                echo "<img src='fotos/img.png' class='rounded-circle' width='220' height='230'>"; 
                            }
                            ?>
                      </div>
                      <div>
                          <span class="btn btn-success btn-file">
                              <span class="fileinput-new" data-trigger="fileinput"><i class="fa fa-file-image-o"></i> Imagen</span>
                              <span class="fileinput-exists" data-trigger="fileinput"><i class="fa fa-paint-brush"></i> Imagen</span>
                              <input type="file" size="10" data-original-title="Subir Fotografia" data-rel="tooltip" placeholder="Suba su Fotografia" name="imagen" id="imagen"/>
                          </span>
                          <a href="#" class="btn btn-dark fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times-circle"></i> Remover</a>                             
                      </div></div>
                      </div>
                  </div>
                </div><!--<small>Para subir la Imagen debe tener en cuenta:<br> * La Imagen debe ser extension.jpg<br> * La Imagen no debe ser mayor de 1 MB</small>-->
        
                </div>
            </div>

    </div>
</div>
<!--</div>
End Row -->

<!-- Row 
<div class="row">-->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-save"></i> Gestión de Productos</h4>
            </div>

            <div class="form-body">

            <div id="save">
               <!-- error will be shown here ! -->
            </div>

            <div class="card-body">

            <?php if ($_SESSION['acceso'] == "administradorG") { ?> 
            <div class="row">
                <div class="col-md-12"> 
                    <div class="form-group has-feedback"> 
                        <label class="control-label">Seleccione Sucursal: <span class="symbol required"></span></label>
                        <i class="fa fa-bars form-control-feedback"></i>
                        <?php if (isset($reg[0]['codsucursal'])) { ?>
                        <select style="color:#000;font-weight:bold;" name="codsucursal" id="codsucursal" onChange="CargaCategoriasxSucursal(this.form.codsucursal.value); CargaProveedoresxSucursal(this.form.codsucursal.value);" class="form-control" required= "" aria-required="true">
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
                        <select style="color:#000;font-weight:bold;" name="codsucursal" id="codsucursal" onChange="CargaCategoriasxSucursal(this.form.codsucursal.value); CargaProveedoresxSucursal(this.form.codsucursal.value);" class="form-control" required="" aria-required="true">
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
                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Código de Producto: <span class="symbol required"></span></label>
                        <input type="hidden" name="idproducto" id="idproducto" <?php if (isset($reg[0]['idproducto'])) { ?> value="<?php echo encrypt($reg[0]['idproducto']); ?>"<?php } ?>>
                        <input type="hidden" name="proceso" id="proceso" <?php if (isset($reg[0]['idproducto'])) { ?> value="update" <?php } else { ?> value="save" <?php } ?>/>
                        <input type="text" class="form-control" name="codproducto" id="codproducto" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Código de Producto" autocomplete="off" <?php if (isset($reg[0]['codproducto'])) { ?> value="<?php echo $reg[0]['codproducto']; ?>" readonly="readonly" <?php } else { ?><?php } ?> required="" aria-required="true"/> 
                        <i class="fa fa-bolt form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Nombre de Producto: <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="producto" id="producto" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nombre de Producto" autocomplete="off" <?php if (isset($reg[0]['producto'])) { ?> value="<?php echo $reg[0]['producto']; ?>" <?php } ?> required="" aria-required="true"/>  
                        <i class="fa fa-pencil form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Categoria de Producto: <span class="symbol required"></span></label>
                        <i class="fa fa-bars form-control-feedback"></i>
                        <?php if ($_SESSION['acceso'] == "administradorG" && !isset($reg[0]['codcategoria'])) { ?>
                        <select style="color:#000;font-weight:bold;" name="codcategoria" id="codcategoria" class='form-control' required="" aria-required="true">
                        <option value=""> -- SIN RESULTADOS -- </option>
                        </select>
                        <?php } elseif (isset($reg[0]['codcategoria'])) { ?>
                        <select style="color:#000;font-weight:bold;" name="codcategoria" id="codcategoria" class='form-control' required="" aria-required="true">
                        <option value=""> -- SELECCIONE -- </option>
                        <?php
                        $categoria = new Login();
                        $categoria = $categoria->ListarCategorias();
                        if($categoria==""){
                            echo "";    
                        } else {
                        for($i=0;$i<sizeof($categoria);$i++){ ?>
                        <option value="<?php echo encrypt($categoria[$i]['codcategoria']); ?>"<?php if (!(strcmp($reg[0]['codcategoria'], htmlentities($categoria[$i]['codcategoria'])))) { echo "selected=\"selected\"";} ?>><?php echo $categoria[$i]['nomcategoria'] ?></option>        
                        <?php } } ?>
                        </select>  
                        <?php } else { ?>
                        <select style="color:#000;font-weight:bold;" name="codcategoria" id="codcategoria" class='form-control' required="" aria-required="true">
                        <option value=""> -- SELECCIONE -- </option>
                        <?php
                        $categoria = new Login();
                        $categoria = $categoria->ListarCategorias();
                        if($categoria==""){
                            echo "";    
                        } else {
                        for($i=0;$i<sizeof($categoria);$i++){ ?>
                        <option value="<?php echo encrypt($categoria[$i]['codcategoria']); ?>"><?php echo $categoria[$i]['nomcategoria'] ?></option>        
                        <?php } } ?>
                        </select>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Precio de Compra: <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="preciocompra" id="preciocompra" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Precio de Compra" autocomplete="off" <?php if (isset($reg[0]['preciocompra'])) { ?> value="<?php echo number_format($reg[0]['preciocompra'], 2, '.', ''); ?>" <?php } ?> required="" aria-required="true"/>  
                        <i class="fa fa-dollar form-control-feedback"></i>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Precio de Venta (P.V.P): <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="precioventa" id="precioventa" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Precio de Venta" autocomplete="off" <?php if (isset($reg[0]['precioventa'])) { ?> value="<?php echo number_format($reg[0]['precioventa'], 2, '.', ''); ?>" <?php } ?>  required="" aria-required="true"/>  
                        <i class="fa fa-dollar form-control-feedback"></i>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Existencia de Producto: <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="existencia" id="existencia" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Existencia de Producto" autocomplete="off" <?php if (isset($reg[0]['existencia'])) { ?> value="<?php echo $reg[0]['existencia']; ?>" <?php } ?> required="" aria-required="true"/>  
                        <i class="fa fa-bolt form-control-feedback"></i>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Stock Minimo: <span class="symbol required"></span></label>
                         <input type="text" class="form-control" name="stockminimo" id="stockminimo" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Stock Minimo" autocomplete="off" <?php if (isset($reg[0]['stockminimo'])) { ?> value="<?php echo $reg[0]['stockminimo']; ?>" <?php } else { ?> value="0.00" <?php } ?> required="" aria-required="true"/>  
                        <i class="fa fa-bolt form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Stock Máximo: <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="stockmaximo" id="stockmaximo" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Stock Máximo" autocomplete="off" <?php if (isset($reg[0]['stockmaximo'])) { ?> value="<?php echo $reg[0]['stockmaximo']; ?>" <?php } else { ?> value="0.00" <?php } ?> required="" aria-required="true"/>  
                        <i class="fa fa-bolt form-control-feedback"></i>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label"><?php echo $impuesto; ?> de Producto: <span class="symbol required"></span></label>
                        <i class="fa fa-bars form-control-feedback"></i>
                        <?php if (isset($reg[0]['ivaproducto'])) { ?>
                        <select style="color:#000;font-weight:bold;" name="ivaproducto" id="ivaproducto" class="form-control" required="" aria-required="true">
                        <option value=""> -- SELECCIONE -- </option>
                        <option value="SI"<?php if (!(strcmp('SI', $reg[0]['ivaproducto']))) {echo "selected=\"selected\"";} ?>>SI</option>
                        <option value="NO"<?php if (!(strcmp('NO', $reg[0]['ivaproducto']))) {echo "selected=\"selected\"";} ?>>NO</option>
                        </select>
                        <?php } else { ?>
                        <select style="color:#000;font-weight:bold;" name="ivaproducto" id="ivaproducto" class="form-control" required="" aria-required="true">
                        <option value=""> -- SELECCIONE -- </option>
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                        </select>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Descuento de Producto: <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="descproducto" id="descproducto" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Descuento de Producto" autocomplete="off" <?php if (isset($reg[0]['descproducto'])) { ?> value="<?php echo $reg[0]['descproducto']; ?>" <?php } else { ?> value="0.00" <?php } ?> required="" aria-required="true"/>  
                        <i class="fa fa-dollar form-control-feedback"></i>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Código de Barra: </label>
                         <input type="text" class="form-control" name="codigobarra" id="codigobarra" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Código de Barra" autocomplete="off" <?php if (isset($reg[0]['codigobarra'])) { ?> value="<?php echo $reg[0]['codigobarra']; ?>" <?php } ?> required="" aria-required="true"/>  
                        <i class="fa fa-barcode form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Nº de Lote: </label>
                        <input type="text" class="form-control" name="lote" id="lote" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Lote" autocomplete="off" <?php if (isset($reg[0]['lote'])) { ?> value="<?php echo $reg[0]['lote']; ?>" <?php } ?> required="" aria-required="true"/>  
                        <i class="fa fa-pencil form-control-feedback"></i> 
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group has-feedback">
                    <label class="control-label">Fecha de Elaboración: </label>
                    <input type="text" class="form-control calendario" name="fechaelaboracion" id="fechaelaboracion" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Fecha de Elaboración" autocomplete="off" <?php if (isset($reg[0]['fechaelaboracion'])) { ?> value="<?php echo $reg[0]['fechaelaboracion'] == '0000-00-00' ? "" : date("d-m-Y",strtotime($reg[0]['fechaelaboracion'])); ?>"<?php } ?> required="" aria-required="true"/>
                        <i class="fa fa-calendar form-control-feedback"></i>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group has-feedback">
                        <label class="control-label">Fecha de Expiración: </label>
                    <input type="text" class="form-control expira" name="fechaexpiracion" id="fechaexpiracion" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Fecha de Expiración" autocomplete="off" <?php if (isset($reg[0]['fechaexpiracion'])) { ?> value="<?php echo $reg[0]['fechaexpiracion'] == '0000-00-00' ? "" : date("d-m-Y",strtotime($reg[0]['fechaexpiracion'])); ?>"<?php } ?> required="" aria-required="true"/>  
                        <i class="fa fa-calendar form-control-feedback"></i>
                    </div>
                </div>

                <div class="col-md-4">
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
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="n2" name="preparado" value="2" <?php if (isset($reg[0]['preparado']) && $reg[0]['preparado'] == "2") { ?> checked="checked" <?php } ?> class="custom-control-input">
                        <label class="custom-control-label" for="n2">BAR</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
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

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Control de Stock: <span class="symbol required"></span></label> 
                        <br>
                        <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="control1" name="controlstockp" value="1" <?php if (isset($reg[0]['controlstockp']) && $reg[0]['controlstockp'] == "1") { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?> class="custom-control-input">
                        <label class="custom-control-label" for="control1">SI</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio" id="control2" name="controlstockp" value="0" <?php if (isset($reg[0]['controlstockp'])) { ?> <?php if($reg[0]['controlstockp'] == "0") { ?> checked="checked" <?php } } else { ?> checked="checked" <?php } ?> class="custom-control-input">
                        <label class="custom-control-label" for="control2">NO</label>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
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

                </div>

            </div>

        </div>
    </div>

</div>
<!-- End Row -->

<!-- Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="font-22 mdi mdi-cart-plus"></i> Detalles de Ingredientes</h4>
            </div>

        <div class="form-body">

        <div class="card-body">

        <?php  if (!isset($_GET['codproducto'])) { ?>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group has-feedback">
                    <label class="control-label">Búsqueda de Ingrediente: </label>
                    <input type="hidden" name="idingrediente" id="idingrediente">
                    <input type="hidden" name="codingrediente" id="codingrediente">
                    <input type="hidden" name="preciocompraing" id="preciocompraing">
                    <input type="hidden" name="precioventaing" id="precioventaing">
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control agregaingrediente" name="search_ingrediente_sucursal" id="search_ingrediente_sucursal" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Realice la Búsqueda de Ingrediente" autocomplete="off"/>
                  <i class="fa fa-search form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-3">  
                <div class="form-group has-feedback"> 
                    <label class="control-label">Cantidad de Porción: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control agregaingrediente" name="cantidad" id="cantidad" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Cantidad de Porción" title="Ingrese Cantidad de Porción" autocomplete="off" value="0.5"/>  
                    <i class="fa fa-pencil form-control-feedback"></i>                                           
                </div>
            </div>

            <div class="col-md-3">  
                <div class="form-group has-feedback"> 
                    <label class="control-label">Unidad de Medida: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="medida" id="medida" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Unidad de Medida" autocomplete="off" readonly=""/>  
                    <i class="fa fa-pencil form-control-feedback"></i>                                           
                </div>
            </div>  
        </div>
        
        <div class="text-left">
    <button type="button" id="AgregaIngrediente" class="btn btn-info"><span class="fa fa-cart-plus"></span> Agregar</button>
    <button class="btn btn-danger" type="button" id="vaciar"><i class="fa fa-trash-o"></i> Vaciar</button>
        </div>

        <div class="table-responsive m-t-20">
            <table id="carrito" class="table table-hover">
                <thead>
                    <tr class="text-center">
                        <th>Cantidad</th>
                        <th>Descripción</th>
                        <th>Unidad Medida</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
                        <td class="text-center" colspan=6><h4>NO HAY DETALLES AGREGADOS</h4></td>
                    </tr>
                </tbody>
            </table> 
        </div>

        <?php } else { ?>

        <div id="cargaingredientes"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
            <thead>
            <tr role="row">
            </tr>
                <tr>
                <th>Nº</th>
                <th>Cant. Ración</th>
                <th>Ingrediente</th>
                <th>Existencia</th>
                <th>Precio Compra</th>
                <th>Precio Venta</th>
                </tr>
            </thead>
            <tbody>
<?php 
$tru = new Login();
$a=1;
$busq = $tru->VerDetallesIngredientes();

if($busq==""){

echo "";      

} else {

$count = 0;
for($i=0;$i<sizeof($busq);$i++){
$count++; 
?>
    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
    <td><?php echo $a++; ?></td>
    <td>
    <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
    <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetalleIngrediente('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
    <input type="text" class="bold" name="cantidad[]" id="cantidad_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoIngrediente(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="<?php echo number_format($busq[$i]["cantracion"], 2, '.', ''); ?>" title="Ingrese Cantidad">
    <input type="hidden" name="cantidadbd[]" id="cantidadbd_<?php echo $count; ?>" value="<?php echo number_format($busq[$i]["cantracion"], 2, '.', ''); ?>">
    <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetalleIngrediente('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
    </div>
    </td>
    <td>
    <input type="hidden" name="codagrega[]" id="codagrega_<?php echo $count; ?>" value="<?php echo $busq[$i]["codagrega"]; ?>">
    <input type="hidden" name="idingrediente[]" id="idingrediente_<?php echo $count; ?>" value="<?php echo $busq[$i]["idingrediente"]; ?>">
    <input type="hidden" name="codingrediente[]" id="codingrediente_<?php echo $count; ?>" value="<?php echo $busq[$i]["codingrediente"]; ?>">
    <input type="hidden" name="cantingrediente[]" id="cantingrediente_<?php echo $count; ?>" value="<?php echo $busq[$i]["cantingrediente"]; ?>">
    <input type="hidden" class="preciocompraing" name="preciocompraing[]" id="preciocompraing_<?php echo $count; ?>" value="<?php echo $busq[$i]["cantracion"]*$busq[$i]["preciocompra"]; ?>">
    <input type="hidden" class="precioventaing"  name="precioventaing[]" id="precioventaing_<?php echo $count; ?>" value="<?php echo $busq[$i]["cantracion"]*$busq[$i]["precioventa"]; ?>">
    <?php echo $busq[$i]["nomingrediente"]; ?>
    </td>
    <td><?php echo $busq[$i]["cantingrediente"]." ".$busq[$i]["nommedida"]; ?></td>
    <td><label id="txtmontocompra_<?php echo $count; ?>"><?php echo number_format($busq[$i]["cantracion"]*$busq[$i]["preciocompra"], 2, '.', ','); ?></label></td>
    <td><label id="txtmontoventa_<?php echo $count; ?>"><?php echo number_format($busq[$i]["cantracion"]*$busq[$i]["precioventa"], 2, '.', ','); ?></label></td>
                </tr><?php } } ?>
              </tbody>
        </table></div><br>

        <?php } ?>

            <div class="text-right">
    <?php if (isset($_GET['codproducto'])) { ?>
<button type="submit" name="btn-update" id="btn-update" class="btn btn-warning"><span class="fa fa-edit"></span> Actualizar</button>
<a href="productos"><button class="btn btn-dark" type="button"><span class="fa fa-trash-o"></span> Cancelar</button></a>
    <?php } else { ?>
<button type="submit" name="btn-submit" id="btn-submit" class="btn btn-warning"><span class="fa fa-save"></span> Guardar</button>
<button class="btn btn-dark" type="button" onclick="
    document.getElementById('proceso').value = 'save',
    document.getElementById('codproducto').value = '',
    document.getElementById('producto').value = '',
    document.getElementById('codcategoria').value = '',
    document.getElementById('existencia').value = '',
    document.getElementById('stockminimo').value = '',
    document.getElementById('stockmaximo').value = '',
    document.getElementById('ivaproducto').value = '',
    document.getElementById('descproducto').value = '',
    document.getElementById('codigobarra').value = '',
    document.getElementById('lote').value = '',
    document.getElementById('fechaelaboracion').value = '',
    document.getElementById('fechaexpiracion').value = '',
    document.getElementById('codproveedor').value = ''
    "><span class="fa fa-trash-o"></span> Limpiar</button>
    <?php } ?>  
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
    <script type="text/javascript" src="assets/script/jsingredientes.js"></script>
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