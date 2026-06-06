<?php
require_once("class/class.php"); 
if(isset($_SESSION['acceso'])) { 
if ($_SESSION['acceso'] == "administradorG" || $_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="secretaria") {

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
$reg = $tra->AgregarIngredientes();
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
               
<!-- Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-save"></i> Detalle de Producto</h4>
            </div>
<?php  
$j = new Login();
$reg = $j->ProductosPorId(); 
?>

<form class="form form-material" method="post" action="#" name="agregaingredientes" id="agregaingredientes" data-id="<?php echo $reg[0]["codproducto"] ?>" enctype="multipart/form-data">

    <div id="save">
        <!-- error will be shown here ! -->
    </div>

    <div class="form-body">

    <div class="card-body">

    <div id="cargaingredientes">

    <div class="row">
        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Código de Producto: <span class="symbol required"></span></label>
                <input type="hidden" name="proceso" id="proceso" value="save"/>
                <input type="hidden" name="codsucursal" id="codsucursal" value="<?php echo encrypt($reg[0]['codsucursal']); ?>"> 
                <input type="hidden" name="codproducto" id="codproducto" value="<?php echo encrypt($reg[0]['codproducto']); ?>"/>
                <input type="hidden" name="preciocompra" id="preciocompra" value="0.00"/>
                <input type="hidden" name="precioventa" id="precioventa" value="0.00"/>
                <input type="hidden" name="producto" id="producto" value="<?php echo encrypt($reg[0]['codproducto']); ?>">
                <br /><abbr title="Código de Producto"><?php echo $reg[0]['codproducto']; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Nombre de Producto: <span class="symbol required"></span></label>
                <br /><abbr title="Nombre de Producto"><?php echo $reg[0]['producto']; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Existencia: <span class="symbol required"></span></label>
                <br /><abbr title="Existencia de Producto"><?php echo $reg[0]['existencia']; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Categoria de Producto: <span class="symbol required"></span></label>
                <br /><abbr title="Categoria de Producto"><?php echo $reg[0]['nomcategoria']; ?></abbr>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Precio de Compra: <span class="symbol required"></span></label>
                <input type="hidden" name="preciocomprabd" id="preciocomprabd" value="<?php echo $reg[0]['preciocompra']; ?>"/>
                <br /><abbr title="Precio de Compra"><?php echo number_format($reg[0]['preciocompra'], 2, '.', ','); ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Precio de Venta: <span class="symbol required"></span></label>
                <input type="hidden" name="precioventabd" id="precioventabd" value="<?php echo $reg[0]['precioventa']; ?>"/>
                <br /><abbr title="Precio de Venta"><?php echo number_format($reg[0]['precioventa'], 2, '.', ','); ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Impuesto de Producto: <span class="symbol required"></span></label>
                <br /><abbr title="Impuesto de Producto"><?php echo $reg[0]['ivaproducto'] != '0.00' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Descuento de Producto: <span class="symbol required"></span></label>
                <br /><abbr title="Descuento de Producto"><?php echo number_format($reg[0]['descproducto'], 2, '.', ','); ?></abbr>
            </div>
        </div>
    </div>

    <h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Ingredientes Agregados</h2><br>

    <table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
    <thead>
    <tr role="row">
    </tr>
    <tr>
        <th>Nº</th>
        <th>Cant. Porción</th>
        <th>Ingrediente</th>
        <th>Existencia</th>
        <th>Precio Compra</th>
        <th>Precio Venta</th>
        <th><span class="mdi mdi-drag-horizontal"></span></th>
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

for($i=0;$i<sizeof($busq);$i++){
?>
    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
        <td><?php echo $a++; ?></td>
        <td><?php echo $busq[$i]["cantracion"]; ?></td>
        <td><?php echo $busq[$i]["nomingrediente"]; ?></td>
        <td><?php echo $busq[$i]["cantingrediente"]." ".$busq[$i]["nommedida"]; ?></td>
        <td><?php echo number_format($busq[$i]["cantracion"]*$busq[$i]["preciocompra"], 2, '.', ','); ?></td>
        <td><?php echo number_format($busq[$i]["cantracion"]*$busq[$i]["precioventa"], 2, '.', ','); ?></td>
        <td><button type="button" class="btn btn-dark btn-rounded" onClick="EliminaDetalleProducto('<?php echo encrypt($busq[$i]['codproducto']); ?>','<?php echo encrypt($busq[$i]['idingrediente']); ?>','<?php echo encrypt($busq[$i]['codingrediente']); ?>','<?php echo encrypt($busq[$i]['cantracion']); ?>','<?php echo encrypt($busq[$i]['codsucursal']); ?>','<?php echo encrypt("ELIMINADETALLEPRODUCTO"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td>
    </tr><?php } } ?>
    </tbody>
    </table></div>

    <hr><h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Detalles de Ingredientes</h2><hr>

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
                <input style="color:#000;font-weight:bold;" type="text" class="form-control agregaingrediente" name="cantidad" id="cantidad" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" placeholder="Ingrese Cantidad de Porción" title="Ingrese Cantidad de Porción" autocomplete="off" value="0.5"/>  
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

        <div class="text-right">
<button type="submit" name="btn-submit" id="btn-submit" class="btn btn-warning"><span class="fa fa-save"></span> Agregar</button>
<a href="productos"><button class="btn btn-dark" type="button"><span class="fa fa-trash-o"></span> Cancelar</button></a>
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