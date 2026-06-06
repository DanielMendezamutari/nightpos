<?php
require_once("class/class.php"); 
if(isset($_SESSION['acceso'])) { 
if ($_SESSION["acceso"]=="administradorG" || $_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="secretaria" || $_SESSION["acceso"]=="cajero") {

$tra = new Login();
$ses = $tra->ExpiraSession();

if(isset($_POST["proceso"]) and $_POST["proceso"]=="procesar")
{
$reg = $tra->ProcesarCotizaciones();
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

<!--############################## MODAL PARA VER DETALLE DE COTIZACION ######################################-->
<!-- sample modal content -->
<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title" id="myLargeModalLabel"><font color="white"><i class="fa fa-tasks"></i> Detalle de Cotización</font></h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            <div class="modal-body">
                <p><div id="muestracotizacionmodal"></div></p>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA VER DETALLE DE COTIZACION ######################################-->


<!--############################## MODAL PARA PROCESAR COTIZACION ######################################-->
<!-- sample modal content -->
<div id="myModalPago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
            <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Detalle de Cotización</h4>
            </div>
            
        <form class="form form-material" name="procesacotizacion" id="procesacotizacion" action="#">
                
               <div class="modal-body">

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group has-feedback">
                        <label class="control-label">Busqueda de Cliente: <span class="symbol required"></span></label>
                        <input type="hidden" name="proceso" id="proceso" value="procesar"/>
                        <input type="hidden" name="codcotizacion" id="codcotizacion"/>
                        <input type="hidden" name="codsucursal" id="codsucursal"/>
                        <input type="hidden" name="codcliente" id="codcliente"/>
                        <input type="hidden" name="nrodocumento" id="nrodocumento">
                        <input type="hidden" name="txtImporte" id="txtImporte"/>
                        <input type="hidden" name="txtTotal" id="txtTotal"/>
                        <input type="hidden" name="limitecredito" id="limitecredito" value="0.00"/>
                        <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00"/>
                        <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="busqueda" id="busqueda" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Realice la Búsqueda del Cliente por Nº de Documento, Nombres o Apellidos" autocomplete="off" required="" aria-required="true"/> 
                        <i class="fa fa-search form-control-feedback"></i> 
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                   <h4 class="mb-0 font-light">Total a Pagar</h4>
                   <h4 class="mb-0 font-medium"><label id="TextImporte" name="TextImporte">0.00</label></h4>
                </div>

                <div class="col-md-4">
                   <h4 class="mb-0 font-light">Total Recibido</h4>
                   <h4 class="mb-0 font-medium"><label id="TextPagado" name="TextPagado">0.00</label></h4>
                </div>

                <div class="col-md-4">
                   <h4 class="mb-0 font-light">Total Cambio</h4>
                   <h4 class="mb-0 font-medium"><label id="TextCambio" name="TextCambio">0.00</label></h4>
                </div>
            </div>
           
            <div class="row">
                <div class="col-md-8">
                   <h4 class="mb-0 font-light">Nombre del Cliente</h4>
                   <h4 class="mb-0 font-medium"> <label id="TextCliente" name="TextCliente">Consumidor Final</label></h4>
                </div>

                <div class="col-md-4">
                   <h4 class="mb-0 font-light">Limite de Crédito</h4>
                   <h4 class="mb-0 font-medium"><label id="TextCredito" name="TextCredito">0.00</label></h4>
                </div>
            </div>
            
            <hr>

            <div class="row">
                <div class="col-md-6">
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

                <div class="col-md-6">
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
            </div>

            <div class="row">
                <div class="col-md-6"> 
                    <div class="form-group has-feedback"> 
                        <label class="control-label">Nombre de Repartidor: <span class="symbol required"></span></label>
                        <i class="fa fa-bars form-control-feedback"></i>
                        <select style="color:#000;font-weight:bold;" name="repartidores" id="repartidores" class="form-control" required="" aria-required="true">
                        <option value=""> -- SELECCIONE -- </option>
                        <?php
                        $usuario = new Login();
                        $usuario = $usuario->ListarRepartidores();
                        if($usuario==""){ 
                          echo "";
                        } else {
                        for($i=0;$i<sizeof($usuario);$i++){ ?>
                        <option value="<?php echo encrypt($usuario[$i]['codigo']); ?>"><?php echo $usuario[$i]['nombres'] ?></option>   
                        <?php } } ?>
                        </select>
                    </div> 
                </div>

                <div class="col-md-6"> 
                    <div class="form-group has-feedback"> 
                        <label class="control-label">Costo Delivery: <span class="symbol required"></span></label>
                        <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montodelivery" id="montodelivery" onKeyUp="DevolucionCotizacion();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Costo Delivery" value="0.00" disabled="" required="" aria-required="true"> 
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
                  <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionCotizacion();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="" required="" aria-required="true"> 
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
                  <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado2" id="montopagado2" onKeyUp="DevolucionCotizacion();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="0.00" disabled="" required="" aria-required="true"> 
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
                <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"></textarea>
                <i class="fa fa-comment-o form-control-feedback2"></i> 
              </div> 
            </div>
          </div> 

            </div><!-- END CONDICION PAGO -->

           

            </div>

            <div class="modal-footer">
                <span id="submit_guardar"><button type="submit" name="btn-submit" id="btn-submit" class="btn btn-warning"><span class="fa fa-print"></span> Facturar e Imprimir</button></span>
                <button class="btn btn-dark" type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="document.getElementById('procesacotizacion').reset();"><span class="fa fa-trash-o"></span> Cancelar</button>
            </div>
        </form>

    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal -->  
<!--############################## MODAL PARA PROCESAR COTIZACION ######################################-->

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
                <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Consulta General</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Cotizaciones</li>
                                <li class="breadcrumb-item active" aria-current="page">Cotizaciones</li>
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
               
<?php if ($_SESSION['acceso'] == "administradorG"){ ?>

<!-- Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Cotizaciones</h4>
            </div>

            <div class="form-body">

            <div class="card-body">

        <form class="form form-material" method="post" action="#" name="cotizacionesxsucursal" id="cotizacionesxsucursal">

            <div class="row">

                <div class="col-md-12"> 
                    <div class="form-group has-feedback"> 
                        <label class="control-label">Seleccione Sucursal: <span class="symbol required"></span></label>
                        <i class="fa fa-bars form-control-feedback"></i>
                        <select style="color:#000;font-weight:bold;" name="codsucursal" id="codsucursal" class="form-control" required="" aria-required="true">
                        <option value=""> -- SELECCIONE -- </option>
                        <?php
                        $sucursal = new Login();
                        $sucursal = $sucursal->ListarSucursales();
                        if($sucursal==""){ 
                            echo "";
                        } else {
                        for($i=0;$i<sizeof($sucursal);$i++){
                        ?>
                        <option value="<?php echo encrypt($sucursal[$i]['codsucursal']); ?>"><?php echo $sucursal[$i]['cuitsucursal'].": ".$sucursal[$i]['nomsucursal']; ?></option>       
                        <?php } } ?>
                        </select>
                    </div> 
                </div>
            </div>

            <div class="text-right">
                <button type="button" id="BotonBusqueda" onClick="BuscaCotizacionesxSucursal()" class="btn btn-warning"><span class="fa fa-search"></span> Realizar Búsqueda</button>
            </div>

            </form>

            </div>
        </div>
     </div>
  </div>
</div>
<!-- End Row -->

<div id="muestra_detalles"></div>

<?php } else { ?>

<!-- Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Cotizaciones</h4>
            </div>

            <div class="form-body">

                <div class="card-body">

                    <div id="save">
                    <!-- error will be shown here ! -->
                    </div>

                    <div class="row">

                    <div class="col-md-6">
                        <div class="btn-group m-b-20">
                        <a class="btn waves-effect waves-light btn-light" href="reportepdf?tipo=<?php echo encrypt("COTIZACIONES") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                        <a class="btn waves-effect waves-light btn-light" href="reporteexcel?documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("COTIZACIONES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                        <a class="btn waves-effect waves-light btn-light" href="reporteexcel?documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("COTIZACIONES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                        </div>
                    </div>
                </div>

                <div id="cotizaciones"></div>

            </div>
        </div>
     </div>
  </div>
</div>
<!-- End Row -->

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
    <?php if ($_SESSION['acceso'] == "administradorG"){ ?>
    <script type="text/javascript">
    $(document).ready(function(){
        $(document).keypress(function(e) {        
            var keycode = (e.keyCode ? e.keyCode : e.which);
            if (keycode == '13') {
            $("#BotonBusqueda").trigger("click");
            return false;
            }
        });                    
    }); 
    </script>
    <?php } else { ?>
    <script type="text/jscript">
    $('#cotizaciones').append('<center><p><i class="fa fa-spin fa-spinner"></i> Por favor espere, cargando registros ......</p></center>').fadeIn("slow");
    setTimeout(function() {
    $('#cotizaciones').load("consultas?CargaCotizaciones=si");
     }, 200);
    </script>
    <?php } ?>
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