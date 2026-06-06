<?php
require_once("class/class.php"); 
if(isset($_SESSION['acceso'])) { 
    if ($_SESSION['acceso'] == "administradorG" || $_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="secretaria" || $_SESSION["acceso"]=="cajero" || $_SESSION["acceso"]=="mesero" || $_SESSION["acceso"]=="cocinero" || $_SESSION["acceso"]=="bar" || $_SESSION["acceso"]=="reposteria" || $_SESSION["acceso"]=="repartidor") {

$tra = new Login();
$ses = $tra->ExpiraSession();

$grafico = new Login();
$grafico = $grafico->ContarRegistros();  

$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "Impuesto" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']);
$simbolo = ($_SESSION["acceso"] == "administradorG" ? "" : "<strong>".$_SESSION["simbolo"]."</strong>");

if(isset($_POST["proceso"]) and $_POST["proceso"]=="nuevopedido")
{
$reg = $tra->NuevoPedido();
exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="agregarpedido")
{
$reg = $tra->AgregaPedido();
exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="cobrarmesa")
{
$reg = $tra->CobrarMesa();
exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="dividirpago")
{
$reg = $tra->CobrarCuentaSeparada();
exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="nuevocliente")
{
$reg = $tra->RegistrarClientes();
exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="traspaso")
{
$reg = $tra->CambiarMesas();
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
    <!-- Custom CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/quill.snow.css">
    <!-- needed css -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- color CSS -->
    <link href="assets/css/default.css" id="theme" rel="stylesheet">
   <!--Bootstrap Horizontal CSS -->
    <link href="assets/css/bootstrap-horizon.css" rel="stylesheet">
    <!-- color alert -->
    <link rel="stylesheet" type="text/css" href="assets/css/alert.css">

    <!-- script jquery -->
    <script src="assets/script/jquery.min.js"></script> 
    <script type="text/javascript" src="assets/plugins/chart.js/chart.min.js"></script>
    <script type="text/javascript" src="assets/script/graficos.js"></script>
    <!--  script jquery -->

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

<!--############################## MODAL PARA AGREGAR OBSERVACIONES EN DETALLE ###################################-->
<!-- sample modal content -->
<div id="myModalObservacion" class="modal fade" tabindex="-1" role="dialog" style="overflow-y: scroll;" aria-labelledby="myModalLabel" aria-hidden="true">
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

<!--############################## MODAL PARA MOSTRAR MENU ######################################-->
<!-- sample modal content -->
<div id="myModalMenu" class="modal fade" tabindex="-1" role="dialog" style="overflow-y: scroll;" aria-labelledby="myModalLabel" aria-hidden="true">
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
<!--############################## MODAL PARA MOSTRAR MENU ######################################-->

<!--############################## MODAL PARA MOSTRAR PEDIDOS EN COCINA ######################################-->
<!-- sample modal content -->
<div id="myModalPedidos" class="modal bs-example-modal-lg" tabindex="-1" role="dialog" style="overflow-y: scroll;" aria-labelledby="myModalLabel" aria-hidden="true">
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
<!--############################## MODAL PARA MOSTRAR PEDIDOS EN COCINA ######################################-->


<!--############################## MODAL CAMBIO DE MESA ######################################-->
<!-- sample modal content -->
<div id="myModalCambioMesa" class="modal fade" tabindex="-1" role="dialog" style="overflow-y: scroll;" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Cambio de Mesa</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <form class="form form-material" name="cambiarmesa" id="cambiarmesa" action="#">

            <div class="modal-body">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group has-feedback">
                        <label class="control-label">Nombre de Sala: <span class="symbol required"></span></label>
                        <input type="hidden" name="proceso" id="proceso" value="traspaso"/>
                        <input type="hidden" name="codpedido" id="codpedido">
                        <input type="hidden" name="mesa_actual" id="mesa_actual">
                        <br/><abbr title="Nombre de Sala"><label class="text-danger" id="salaentra"></label></abbr>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group has-feedback">
                        <label class="control-label">Nombre de Mesa: <span class="symbol required"></span></label>
                        <br/><abbr title="Nombre de Mesa"><label class="text-danger" id="mesaentra"></label></abbr>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group has-feedback">
                        <label class="control-label">Seleccione Sala: <span class="symbol required"></span></label>
                        <i class="fa fa-bars form-control-feedback"></i>
                        <select style="color:#000;font-weight:bold;" name="nuevasala" id="nuevasala" class='form-control' onChange="CargaMesas(this.form.nuevasala.value);" required="" aria-required="true">
                        <option value=""> -- SELECCIONE -- </option>
                        <?php
                        $sala = new Login();
                        $sala = $sala->ListarSalas();
                        if($sala==""){
                            echo "";    
                         } else {
                        for($i=0;$i<sizeof($sala);$i++){
                        ?>
                        <option value="<?php echo encrypt($sala[$i]['codsala']); ?>"><?php echo $sala[$i]['nomsala'] ?></option>
                        <?php } } ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group has-feedback">
                        <label class="control-label">Seleccione Mesa: <span class="symbol required"></span></label>
                        <i class="fa fa-bars form-control-feedback"></i>
                        <select style="color:#000;font-weight:bold;" name="nuevamesa" id="nuevamesa" class='form-control' required="" aria-required="true">
                        <option value="">-- SIN RESULTADOS --</option>
                        </select>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6">
                   <button type="submit" name="btn-cambiar" id="btn-cambiar" class="btn btn-primary btn-lg btn-block waves-effect waves-light"><span class="fa fa-save"></span> Cambiar Mesa</button>
                </div>
                <div class="col-md-6">
                   <button type="reset" class="btn btn-dark btn-lg btn-block waves-effect waves-light" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cancelar</button>
                </div>
            </div>
    
            </div>

        </form>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal --> 
<!--############################## MODAL CAMBIO DE MESA ######################################-->


<!--############################## MODAL PARA MOSTRAR DETALLE DE PEDIDOS EN MESA ###################################-->
<!-- sample modal content -->
<div id="MyModalDetallePedido" class="modal fade" tabindex="-1" role="dialog" style="overflow-y: scroll;" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-align-justify"></i> Detalles Agregados</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
                </div>
                
            <div class="modal-body">

            <div id="muestradetallepedidosmesa"></div><!-- detalle observacion -->

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
            </div>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA MOSTRAR DETALLE DE PEDIDOS EN MESA ###################################-->


<!--############################## MODAL PARA COBRAR MESA ######################################-->
<!-- sample modal content -->
<div id="myModalPago" class="modal fade" tabindex="-1" role="dialog" style="overflow-y: scroll;" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Cobrar Mesa</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <form class="form form-material" name="cobrarmesa" id="cobrarmesa" action="#">

            <div class="modal-body">

            <div id="cierremesa"></div>

            <div class="row">
                <div class="col-md-6">
                   <span id="submit_cerrar"><button type="submit" name="btn-cerrar" id="btn-cerrar" class="btn btn-warning btn-lg btn-block waves-effect waves-light"><span class="fa fa-print"></span> Facturar e Imprimir</button></span>
                </div>
                <div class="col-md-6">
                   <button type="reset" class="btn btn-dark btn-lg btn-block waves-effect waves-light" class="close" data-dismiss="modal" aria-hidden="true" onclick="CerraModalCobro1();"><span class="fa fa-trash-o"></span> Cancelar</button>
                </div>
            </div>
    
            </div>

        </form>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal --> 
<!--############################## MODAL PARA COBRAR MESA ######################################-->

<!--############################## MODAL PARA DIVIDIR CUENTA ######################################-->
<!-- sample modal content -->
<div id="myModalPagoSeparado" class="modal fade" tabindex="-1" role="dialog" style="overflow-y: scroll;" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Dividir Cuenta</h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <form class="form form-material" name="cobrarmesaseparada" id="cobrarmesaseparada" action="#">

            <div class="modal-body">

            <div id="separarcuenta"></div>

            <div class="row">
                <div class="col-md-6">
                   <span id="submit_separar"><button type="submit" name="btn-cerrar" id="btn-cerrar" class="btn btn-warning btn-lg btn-block waves-effect waves-light"><span class="fa fa-print"></span> Facturar e Imprimir</button></span>
                </div>
                <div class="col-md-6">
                   <button type="reset" class="btn btn-dark btn-lg btn-block waves-effect waves-light" class="close" data-dismiss="modal" aria-hidden="true" onclick="CerraModalCobro2();"><span class="fa fa-trash-o"></span> Cancelar</button>
                </div>
            </div>
    
            </div>

        </form>

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal --> 
<!--############################## MODAL PARA DIVIDIR CUENTA ######################################--> 
 
<!--############################## MODAL PARA REGISTRO DE NUEVO CLIENTE ######################################-->
<!-- sample modal content -->
<div id="myModalCliente" class="modal fade" tabindex="-1" role="dialog" style="overflow-y: scroll;" aria-labelledby="myModalLabel" aria-hidden="true">
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
                    <input type="hidden" name="formulario" id="formulario" value="panel"/>
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
                <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> 

                <?php if($_SESSION['acceso'] == "mesero" || $_SESSION['acceso'] == "cocinero" || $_SESSION['acceso'] == "repartidor") { echo "Mostrador de Pedidos"; } elseif($_SESSION['acceso'] == "administradorS" || $_SESSION['acceso'] == "secretaria" || $_SESSION['acceso'] == "cajero"){ echo "Gestión de Ventas"; } else { echo "Dashboard"; } ?>

                </h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item"><a href="panel" class="text-info"> Mostrador</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php if($_SESSION['acceso'] == "mesero" || $_SESSION['acceso'] == "cocinero" || $_SESSION["acceso"]=="bar" || $_SESSION["acceso"]=="reposteria" || $_SESSION['acceso'] == "repartidor") { echo "<a href='logout' class='text-info'> Cerrar Sesión</a>"; } elseif($_SESSION['acceso'] == "administradorS" || $_SESSION['acceso'] == "secretaria" || $_SESSION['acceso'] == "cajero"){ echo "Ventas"; } else { echo "Dashboard"; } ?></li>
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

<?php if ($_SESSION['acceso'] == "administradorG") { ?>

    <!-- ============================================================== -->
    <!-- Grafico por Sucursales -->
    <!-- ============================================================== -->
    <!-- Row -->
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-uppercase mb-0">
                        Gráficos de Sucursales del Año <?php echo date("Y"); ?>
                    </h5>
                    <div id="chart-container">
                        <canvas id="barChart" width="400" height="100"></canvas>
                    </div>
                        <script>
                        $(document).ready(function () {
                            showGraphBarS();
                        });
                        </script>
                </div>
            </div>
        </div>
    </div>
    <!-- End Row -->
    <!-- ============================================================== -->
    <!-- Grafico por Sucursales -->
    <!-- ============================================================== -->

<?php } else if ($_SESSION["acceso"]=="cajero_usuario") { ?>
            
<form class="form form-material" method="post" action="#" name="saveventas" id="saveventas">   

<!-- Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Gestión de Ventas</h4>
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

    <div id="pedidos"></div>

    <div id="muestradetallemesa">
        <center class="alert-link" >SELECCIONE MESA PARA CONTINUAR <i class="font-20 fa fa-arrow-right"></i></center>
        <h6 class="text-dark text-center">Para ingresar o visualizar pedidos</h6>
    </div>

        </div>
        <!-- /.col -->
        
        <!-- .col -->  
        <div class="col-md-6">

        <span class="pull-right">

        <button type="button" class="btn btn-primary waves-effect waves-light" style="cursor: pointer;" data-placement="left" title="Ver Menu" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMenu" onclick="CargarMenu();"><span class="fa fa-clipboard"></span> </button>

        <button type="button" class="btn btn-dark waves-effect waves-light" style="cursor: pointer;" title="Refrescar Mesas" onClick="MostrarMesas();"><span class="fa fa-medium"></span> </button>
        
        </span>
            
            <div id="loading_mesas"></div>

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

<?php } else if ($_SESSION['acceso'] == "administradorS" || $_SESSION["acceso"]=="cajero" || $_SESSION["acceso"]=="mesero") { ?>

    <!-- Row -->
    <div class="row">
        <div class="col-md-12 col-lg-12">
            <div id="notificaciones"></div>
        </div>
    </div>
    <script type="text/javascript">
    setTimeout(function() {
        $('#notificaciones').load("notificaciones?CargaNotificacion=si&tipo="+'<?php echo encrypt('1'); ?>');
    }, 5000);
    setInterval("checkUpdate()", 5000);

    function checkUpdate()
    {
        setTimeout(function() {
            $('#notificaciones').load("notificaciones?CargaNotificacion=si&tipo="+'<?php echo encrypt('1'); ?>');
        }, 3000);
    }
    </script>

   <form class="form form-material" method="post" action="#" name="saveventas" id="saveventas">   

<!-- Row -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Gestión de Ventas</h4>
            </div>

            <div id="save">
            <!-- error will be shown here ! -->
            </div>
            
            <div class="form-body">

              <div class="card-body">


    <!-- AQUI MUESTRO DETALLES DE MESAS -->
    <div id="muestra_detalles_mesas" class="show"><!-- div id -->

        <div class="row">
            
            <!-- .col -->  
            <div class="col-md-12">

            <span class="pull-right">

            <button type="button" class="btn btn-primary waves-effect waves-light" style="cursor: pointer;" data-placement="left" title="Ver Menu" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMenu" onclick="CargarMenu();"><span class="fa fa-clipboard"></span> </button>

            <button type="button" class="btn btn-dark waves-effect waves-light" style="cursor: pointer;" title="<?php echo $var = ($_SESSION['acceso'] == "administradorS" || $_SESSION['acceso'] == "mesero" ? "Monitor de Mesas" : "Refrescar Mesas"); ?>" onClick="MostrarMesas();"><span class="fa fa-medium"></span> </button>

            </span>
                
                <div id="loading_mesas"></div>

            </div>
           <!-- /.col -->
                                       
        </div>

    </div><!-- div id -->
    <!-- AQUI MUESTRO DETALLES DE MESAS -->


    <!-- AQUI MUESTRO DETALLES DE PEDIDOS -->
    <div id="muestra_detalles_pedidos" class="hide"><!-- div id -->

        <div class="row">

            <!-- .col -->
            <div class="col-md-6">
            
            <h3 class="card-subtitle m-0 text-dark"><i class="font-20 mdi mdi-cart-plus"></i> Detalle de Ventas</h3><hr>

               <div id="pedidos"></div>

            <div id="muestradetallemesa">
            <center class="alert-link" >SELECCIONE MESA PARA CONTINUAR <i class="font-20 fa fa-arrow-right"></i></center>
            <h6 class="text-dark text-center">Para ingresar o visualizar pedidos</h6>
            </div>

            </div>
            <!-- /.col -->
            
            <!-- .col -->  
            <div class="col-md-6">

            <span class="pull-right">

            <button type="button" class="btn btn-primary waves-effect waves-light" style="cursor: pointer;" data-placement="left" title="Ver Menu" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMenu" onclick="CargarMenu();"><span class="fa fa-clipboard"></span> </button>

            <button type="button" class="btn btn-dark waves-effect waves-light" style="cursor: pointer;" title="<?php echo $var = ($_SESSION['acceso'] == "administradorS" || $_SESSION['acceso'] == "mesero" ? "Monitor de Mesas" : "Refrescar Mesas"); ?>" onClick="MostrarMesas();"><span class="fa fa-medium"></span> </button>
            
            <?php if ($_SESSION['acceso'] == "administradorS" || $_SESSION['acceso'] == "mesero") { ?>
            <button type="button" class="btn btn-success waves-effect waves-light" style="cursor: pointer;" title="Productos" onClick="MostrarProductos();"><span class="fa fa-cubes"></span> </button>
            
            <button type="button" class="btn btn-info waves-effect waves-light" style="cursor: pointer;" title="Combos" onClick="MostrarCombos();"><span class="fa fa-archive"></span> </button>
            
            <button type="button" class="btn btn-danger waves-effect waves-light" style="cursor: pointer;" title="Extras" onClick="MostrarExtras();"><span class="fa fa-folder-open"></span> </button>
            <?php } ?>

            </span>
                
                <div id="loading_productos"></div>

            </div>
           <!-- /.col -->
                                       
        </div>

    </div><!-- div id -->
    <!-- AQUI MUESTRO DETALLES DE PEDIDOS -->


                </div>
            </div>

        </div>
    </div>
</div>
<!-- End Row -->

        </form> 

<?php } else if ($_SESSION['acceso'] == "cocinero") { ?>

    <!-- Row -->
    <div class="page-content container-fluid note-has-grid">
        <ul class="nav nav-pills p-3 bg-white mb-3 rounded-pill align-items-center">
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraComanda('<?php echo encrypt("TODOS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center active px-2 px-md-3 mr-0 mr-md-2" id="all-todo">
               <i class="mdi mdi-account-search"></i><span class="d-none d-md-block"> Todos</span></a> 
            </li>

            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraComanda('<?php echo encrypt("MESAS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="all-category">
               <i class="mdi mdi-black-mesa"></i><span class="d-none d-md-block"> Mesas</span></a> 
            </li>
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraComanda('<?php echo encrypt("DELIVERY"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-business">
                <i class="mdi mdi-motorbike"></i><span class="d-none d-md-block"> Delivery</span></a> 
            </li>
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraComanda('<?php echo encrypt("ENTREGADOS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-social">
                <i class="mdi mdi-view-parallel"></i><span class="d-none d-md-block"> Entregados</span></a> 
            </li>
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraComanda('<?php echo encrypt("GENERAL"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-general">
                <i class="mdi mdi-view-parallel"></i><span class="d-none d-md-block"> Total Pedidos</span></a> 
            </li>

            <li class="nav-item ml-auto"> <a href="javascript:void(0)" onClick="RecargaComanda('<?php echo encrypt("TODOS"); ?>');" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes">
                <i class="mdi mdi-refresh"></i><span class="d-none d-md-block font-14"> Recargar Mostrador</span></a> 
            </li>
        </ul>
    </div>
    <!-- End Row -->

    <div id="mostrador_nuevos"></div>

    <div id="mostrador_preparacion"></div>

    <script type="text/javascript">
    $('#mostrador_nuevos').append('<center><i class="fa fa-spin fa-spinner"></i> Por favor espere, cargando registros ......</center>').fadeIn("slow");
    setTimeout(function() {
    $('#mostrador_nuevos').load("consultas?CargaMostradorNuevos=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    $('#mostrador_preparacion').load("consultas?CargaMostradorPreparacion=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    }, 200);
    </script>

<?php } else if ($_SESSION['acceso'] == "bar") { ?>

    <!-- Row -->
    <div class="page-content container-fluid note-has-grid">
        <ul class="nav nav-pills p-3 bg-white mb-3 rounded-pill align-items-center">
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraBar('<?php echo encrypt("TODOS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center active px-2 px-md-3 mr-0 mr-md-2" id="all-todo">
               <i class="mdi mdi-account-search"></i><span class="d-none d-md-block"> Todos</span></a> 
            </li>

            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraBar('<?php echo encrypt("MESAS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="all-category">
               <i class="mdi mdi-black-mesa"></i><span class="d-none d-md-block"> Mesas</span></a> 
            </li>
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraBar('<?php echo encrypt("DELIVERY"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-business">
                <i class="mdi mdi-motorbike"></i><span class="d-none d-md-block"> Delivery</span></a> 
            </li>
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraBar('<?php echo encrypt("ENTREGADOS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-social">
                <i class="mdi mdi-view-parallel"></i><span class="d-none d-md-block"> Entregados</span></a> 
            </li>
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraBar('<?php echo encrypt("GENERAL"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-general">
                <i class="mdi mdi-view-parallel"></i><span class="d-none d-md-block"> Total Pedidos</span></a> 
            </li>

            <li class="nav-item ml-auto"> <a href="javascript:void(0)" onClick="RecargaBar('<?php echo encrypt("TODOS"); ?>');" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes">
                <i class="mdi mdi-refresh"></i><span class="d-none d-md-block font-14"> Recargar Mostrador</span></a> 
            </li>
        </ul>
    </div>
    <!-- End Row -->

    <div id="barra_nuevos"></div>

    <div id="barra_preparacion"></div>

    <script type="text/javascript">
    $('#barra_nuevos').append('<center><i class="fa fa-spin fa-spinner"></i> Por favor espere, cargando registros ......</center>').fadeIn("slow");
    setTimeout(function() {
    $('#barra_nuevos').load("consultas?CargaBarNuevos=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    $('#barra_preparacion').load("consultas?CargaBarPreparacion=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    }, 200);
    </script>

<?php } else if ($_SESSION['acceso'] == "reposteria") { ?>

    <!-- Row -->
    <div class="page-content container-fluid note-has-grid">
        <ul class="nav nav-pills p-3 bg-white mb-3 rounded-pill align-items-center">
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraReposteria('<?php echo encrypt("TODOS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center active px-2 px-md-3 mr-0 mr-md-2" id="all-todo">
               <i class="mdi mdi-account-search"></i><span class="d-none d-md-block"> Todos</span></a> 
            </li>

            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraReposteria('<?php echo encrypt("MESAS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="all-category">
               <i class="mdi mdi-black-mesa"></i><span class="d-none d-md-block"> Mesas</span></a> 
            </li>
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraReposteria('<?php echo encrypt("DELIVERY"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-business">
                <i class="mdi mdi-motorbike"></i><span class="d-none d-md-block"> Delivery</span></a> 
            </li>
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraReposteria('<?php echo encrypt("ENTREGADOS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-social">
                <i class="mdi mdi-view-parallel"></i><span class="d-none d-md-block"> Entregados</span></a> 
            </li>
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraReposteria('<?php echo encrypt("GENERAL"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-general">
                <i class="mdi mdi-view-parallel"></i><span class="d-none d-md-block"> Total Pedidos</span></a> 
            </li>

            <li class="nav-item ml-auto"> <a href="javascript:void(0)" onClick="RecargaReposteria('<?php echo encrypt("TODOS"); ?>');" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes">
                <i class="mdi mdi-refresh"></i><span class="d-none d-md-block font-14"> Recargar Mostrador</span></a> 
            </li>
        </ul>
    </div>
    <!-- End Row -->

    <div id="reposteria_nuevos"></div>

    <div id="reposteria_preparacion"></div>

    <script type="text/javascript">
    $('#reposteria_nuevos').append('<center><i class="fa fa-spin fa-spinner"></i> Por favor espere, cargando registros ......</center>').fadeIn("slow");
    setTimeout(function() {
    
    $('#reposteria_nuevos').load("consultas?CargaReposteriaNuevos=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    $('#reposteria_preparacion').load("consultas?CargaReposteriaPreparacion=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    }, 200);
    </script>


<?php } else if ($_SESSION['acceso'] == "repartidor") { ?>

    <!-- Row -->
    <div class="page-content container-fluid note-has-grid">
        <ul class="nav nav-pills p-3 bg-white mb-3 rounded-pill align-items-center">
            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraDelivery('<?php echo encrypt("TODOS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center active px-2 px-md-3 mr-0 mr-md-2" id="all-todo">
               <i class="mdi mdi-account-search"></i><span class="d-none d-md-block"> Pendientes</span></a> 
            </li>

            
            <li class="nav-item"> <a href="javascript:void(0)" onClick="MuestraDelivery('<?php echo encrypt("ENTREGADOS"); ?>');" class="nav-link rounded-pill note-link d-flex align-items-center px-2 px-md-3 mr-0 mr-md-2" id="note-social">
                <i class="mdi mdi-view-parallel"></i><span class="d-none d-md-block"> Entregados</span></a> 
            </li>

            <li class="nav-item ml-auto"> <a href="javascript:void(0)" onClick="RecargaDelivery('<?php echo encrypt("TODOS"); ?>');" class="nav-link btn-primary rounded-pill d-flex align-items-center px-3" id="add-notes">
                <i class="mdi mdi-refresh"></i><span class="d-none d-md-block font-14"> Recargar Mostrador</span></a> 
            </li>
        </ul>
    </div>
    <!-- End Row -->
            
    <div id="delivery"></div>


    <script type="text/javascript">
    $('#delivery').append('<center><i class="fa fa-spin fa-spinner"></i> Por favor espere, cargando registros ......</center>').fadeIn("slow");
    setTimeout(function() {
    $('#delivery').load("consultas?CargaDelivery=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
     }, 200);
    </script>

<?php } else { ?>

    <div class="row">
    <div class="col-md-12 col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title text-uppercase mb-0">
                Gráfico de Registros
            </h5>
            <div id="chart-container">
                <canvas id="bar-chart" width="800" height="400"></canvas>
            </div>
            <script>
                    // Bar chart
                    new Chart(document.getElementById("bar-chart"), {
                        type: 'bar',
                        data: {
                            labels: ["Clientes", "Proveedores", "Ingredientes", "Productos", "Compras", "Ventas"],
                            datasets: [
                            {
                                label: "Cantidad Nº",
                                backgroundColor: ["#ff7676", "#3e95cd","#3cba9f","#003399","#f0ad4e","#969788"],
                                data: [<?php echo $grafico[0]['clientes'] ?>,<?php echo $grafico[0]['proveedores'] ?>,<?php echo $grafico[0]['ingredientes'] ?>,<?php echo $grafico[0]['productos'] ?>,<?php echo $grafico[0]['compras'] ?>,<?php echo $grafico[0]['ventas'] ?>]
                            }
                            ]
                        },
                        options: {
                            legend: { display: false },
                            title: {
                                display: true,
                                text: 'Cantidad de Registros'
                            }
                        }
                    });
                </script>
            </div>
        </div>
    </div>

<?php  
$compra = new Login();
$commes = $compra->SumaCompras();

$venta = new Login();
$venmes = $venta->SumaVentas();

?>

<div class="col-md-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-uppercase mb-0">
                    Compras del Año <?php echo date("Y"); ?>
                </h5>
                <div id="chart-container">
                <canvas id="bar-chart3" width="800" height="400"></canvas>
                </div>
                <script>
                // Bar chart
                new Chart(document.getElementById("bar-chart3"), {
                type: 'bar',
                data: {
                labels: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"],
                datasets: [
                {
                        label: "Monto Mensual",
                        backgroundColor: ["#ff7676","#3e95cd","#808080","#F38630","#25AECD","#008080","#00FFFF","#3cba9f","#2E64FE","#e8c3b9","#F7BE81","#FA5858"],
                        data: [<?php 

                    if($commes == "") { echo 0; } else {

                      $meses = array(1 => 0, 2=> 0, 3=> 0, 4=> 0, 5=> 0, 6=> 0, 7=> 0, 8=> 0, 9=> 0, 10=> 0, 11=> 0, 12 => 0);
                      foreach($commes as $row) {
                        $mes = $row['mes'];
                        $meses[$mes] = $row['totalmes'];
                    }
                        foreach($meses as $mes) {
                        echo "{$mes},"; } } ?>]
                        }]
                    },
                    options: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Suma de Monto Mensual'
                        }
                    }
                });
                </script>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-uppercase mb-0">
                    Ventas del Año <?php echo date("Y"); ?>
                </h5>
                <div id="chart-container">
                <canvas id="bar-chart4" width="800" height="400"></canvas>
                </div>
                <script>
                // Bar chart
                new Chart(document.getElementById("bar-chart4"), {
                type: 'bar',
                data: {
                labels: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"],
                datasets: [
                      {
                        label: "Monto Mensual",
                        backgroundColor: ["#ff7676","#3e95cd","#808080","#F38630","#7B82EC","#8EE1BC","#D3E37D","#E8AC9E","#2E64FE","#E399DA","#F7BE81","#FA5858"],
                        data: [<?php 

                        if($venmes == "") { echo 0; } else {

                        $meses = array(1 => 0, 2=> 0, 3=> 0, 4=> 0, 5=> 0, 6=> 0, 7=> 0, 8=> 0, 9=> 0, 10=> 0, 11=> 0, 12 => 0);
                          foreach($venmes as $row) {
                            $mes = $row['mes'];
                            $meses[$mes] = $row['totalmes'];
                        }
                        foreach($meses as $mes) {
                            echo "{$mes},"; } } ?>]
                        }]
                    },
                    options: {
                        legend: { display: false },
                        title: {
                        display: true,
                        text: 'Suma de Monto Mensual'
                        }
                    }
                });
                </script>
            </div>
        </div>
    </div>

    <div class="col-md-12 col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-uppercase mb-0">
                    8 Productos Mas Vendidos del Año <?php echo date("Y"); ?>
                </h5>
                <div id="chart-container">
                <canvas id="DoughnutChart" width="800" height="500"></canvas>
                </div>
                <script>
                $(document).ready(function () {
                    showGraphDoughnutPV();
                });
                </script>
            </div>
        </div>
    </div>
</div>

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

    <?php if ($_SESSION["acceso"]=="mesero") { ?>
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
    <?php } ?>
   

    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="assets/script/jquery.min.js"></script> 
    <script src="assets/js/popper.min.js"></script>
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
    <script src="assets/js/notes.js"></script>
    <!--Custom JavaScript -->
    <script src="assets/js/custom.js"></script>

    <!-- script jquery -->
    <script type="text/javascript" src="assets/script/titulos.js"></script>
    <script type="text/javascript" src="assets/script/jquery.mask.js"></script>
    <script type="text/javascript" src="assets/script/mask.js"></script>
    <script type="text/javascript" src="assets/script/script2.js"></script>
    <script type="text/javascript" src="assets/script/jsventas.js"></script>
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
    <?php if ($_SESSION['acceso'] == "cocinero") { ?>

    <script type="text/javascript">
    setInterval(function() {
    $("#all-todo").addClass("active");
    $("#all-category").removeClass("active");
    $("#note-business").removeClass("active");
    $("#note-social").removeClass("active");
    $("#note-general").removeClass("active");
    $('#mostrador_nuevos').load("consultas?CargaMostradorNuevos=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    $('#mostrador_preparacion').load("consultas?CargaMostradorPreparacion=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
     }, 10000);
    </script>

    <?php } elseif ($_SESSION['acceso'] == "bar") { ?>

    <script type="text/javascript">
    setInterval(function() {
    $("#all-todo").addClass("active");
    $("#all-category").removeClass("active");
    $("#note-business").removeClass("active");
    $("#note-social").removeClass("active");
    $("#note-general").removeClass("active");
    $('#barra_nuevos').load("consultas?CargaBarNuevos=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    $('#barra_preparacion').load("consultas?CargaBarPreparacion=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
     }, 10000);
    </script>

    <?php } elseif ($_SESSION['acceso'] == "reposteria") { ?>

    <script type="text/javascript">
    setInterval(function() {
    $("#all-todo").addClass("active");
    $("#all-category").removeClass("active");
    $("#note-business").removeClass("active");
    $("#note-social").removeClass("active");
    $("#note-general").removeClass("active");
    $('#reposteria_nuevos').load("consultas?CargaReposteriaNuevos=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    $('#reposteria_preparacion').load("consultas?CargaReposteriaPreparacion=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    }, 10000);
    </script>

    <?php } elseif ($_SESSION['acceso'] == "repartidor") { ?>

    <script type="text/javascript">
    setInterval(function() {
    $("#all-todo").addClass("active");
    $("#note-social").removeClass("active");
    $('#delivery').load("consultas?CargaDelivery=si&proceso="+'<?php echo encrypt("TODOS"); ?>');
    }, 10000);
    </script> 

    <?php } else { ?>

    <script type="text/javascript">
    $('#loading_mesas').append('<center><i class="fa fa-spin fa-spinner"></i> Por favor espere, cargando registros ......</center>').fadeIn("slow");
    setTimeout(function() {
    $('#loading_mesas').load("salas_mesas?CargaMesas=si");
     }, 1000);
    </script>

    <?php } ?>

</body>
</html>

<?php } else { ?>   
        <script type='text/javascript' language='javascript'>
        alert('NO TIENES PERMISO PARA ACCEDER A ESTA PAGINA.\nCONSULTA CON EL ADMINISTRADOR PARA QUE TE DE ACCESO')  
        document.location.href='logout'   
        </script> 
<?php } } else { ?>
        <script type='text/javascript' language='javascript'>
        alert('NO TIENES PERMISO PARA ACCEDER AL SISTEMA.\nDEBERA DE INICIAR SESION')  
        document.location.href='logout'  
        </script> 
<?php } ?>