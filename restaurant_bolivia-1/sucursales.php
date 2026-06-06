<?php
require_once("class/class.php"); 
if(isset($_SESSION['acceso'])) { 
     if ($_SESSION['acceso'] == "administradorG" || $_SESSION["acceso"]=="administradorS") {

$tra = new Login();
$ses = $tra->ExpiraSession(); 

if(isset($_POST["proceso"]) and $_POST["proceso"]=="save")
{
$reg = $tra->RegistrarSucursales();
exit;
}
elseif(isset($_POST["proceso"]) and $_POST["proceso"]=="update")
{
$reg = $tra->ActualizarSucursales();
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

<!--############################## MODAL PARA VER DETALLE DE SUCURSAL ######################################-->
<!-- sample modal content -->
<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-align-justify"></i> Detalle de Sucursal</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
                </div>
                <div class="modal-body">

                <div id="muestrasucursalmodal"></div> 

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
<!--############################## MODAL PARA VER DETALLE DE SUCURSAL ######################################-->                


<!--############################## MODAL PARA GESTION DE SUCURSALES ######################################-->
<!-- sample modal content -->
<div id="myModalSucursal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-save"></i> Gestión de Sucursales</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <form class="form form-material" method="post" action="#" name="savesucursal" id="savesucursal" enctype="multipart/form-data">
                
        <div class="modal-body">

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Código de Sucursal: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="nrosucursal" id="nrosucursal" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Código de Sucursal" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Tipo de Documento: <span class="symbol required"></span></label>
                    <i class="fa fa-bars form-control-feedback"></i> 
                    <input type="hidden" name="proceso" id="proceso" value="save"/>
                    <input type="hidden" class="form-control" name="codsucursal" id="codsucursal"/>
                    <select style="color:#000;font-weight:bold;" name="documsucursal" id="documsucursal" class='form-control' required="" aria-required="true">
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

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Registro: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="cuitsucursal" id="cuitsucursal" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Registro" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Razón Social: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="nomsucursal" id="nomsucursal" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Razón Social" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Giro: </label>
                    <input type="text" class="form-control" name="codgiro" id="codgiro" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Código de Giro" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Giro de Empresa: </label>
                    <input type="text" class="form-control" name="girosucursal" id="girosucursal" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Giro de Empresa" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
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

           <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Departamento: </label>
                    <i class="fa fa-bars form-control-feedback"></i> 
                    <select style="color:#000;font-weight:bold;" name="id_departamento" id="id_departamento" class="form-control" required="" aria-required="true">
                        <option value=""> -- SIN RESULTADOS -- </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Dirección de Sucursal: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="direcsucursal" id="direcsucursal" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Dirección de Sucursal" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-map-marker form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Correo Electronico: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="correosucursal" id="correosucursal" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Correo Electronico" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-envelope-o form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Teléfono: <span class="symbol required"></span></label>
                    <input type="text" class="form-control phone-inputmask" name="tlfsucursal" id="tlfsucursal" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Teléfono" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-phone form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Actividad: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="nroactividadsucursal" id="nroactividadsucursal" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Nº de Actividad" required="" aria-required="true">
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div> 
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº Inicio de Ticket: <span class="symbol required"></span></label>
                    <input type="number" min="1" step="1" class="form-control number" name="inicioticket" id="inicioticket" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Nº de Inicio de Ticket" required="" aria-required="true">
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº Inicio de Boleta: <span class="symbol required"></span></label>
                    <input type="number" min="1" step="1" class="form-control number" name="inicioboleta" id="inicioboleta" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Nº de Inicio de Boleta" required="" aria-required="true">
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº Inicio de Factura: <span class="symbol required"></span></label>
                    <input type="number" min="1" step="1" class="form-control number" name="iniciofactura" id="iniciofactura" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº Inicio de Factura" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-bolt form-control-feedback"></i>  
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº Inicio Nota Crédito: <span class="symbol required"></span></label>
                    <input type="number" min="1" step="1" class="form-control number" name="inicionotacredito" id="inicionotacredito" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº Inicio Nota de Crédito" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-bolt form-control-feedback"></i>  
                </div>
            </div> 
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Fecha de Autorización: </label>
                    <input type="text" class="form-control calendario" name="fechaautorsucursal" id="fechaautorsucursal" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Fecha de Autorización" required="" aria-required="true">
                    <i class="fa fa-calendar form-control-feedback"></i> 
                </div>
            </div> 

            <div class="col-md-3"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Lleva Contabilidad: </label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <select style="color:#000;font-weight:bold;" name="llevacontabilidad" id="llevacontabilidad" class="form-control" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="SI">SI</option>
                    <option value="NO">NO</option>
                    </select>
                </div> 
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Tipo de Documento: </label>
                    <i class="fa fa-bars form-control-feedback"></i> 
                    <select style="color:#000;font-weight:bold;" name="documencargado" id="documencargado" class='form-control' required="" aria-required="true">
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

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº Doc. de Encargado: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="dniencargado" id="dniencargado" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº Documento" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nombre de Encargado: <span class="symbol required"></span></label>
                    <input type="text" class="form-control" name="nomencargado" id="nomencargado" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nombre de Director" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-pencil form-control-feedback"></i> 
                </div>
            </div> 

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Teléfono de Encargado: </label>
                    <input type="text" class="form-control phone-inputmask" name="tlfencargado" id="tlfencargado" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Teléfono" autocomplete="off" required="" aria-required="true"/>  
                    <i class="fa fa-phone form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Descuento en Sucursal: </label> 
                    <input type="text" class="form-control" name="descsucursal" id="descsucursal" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Descuento en Ventas" value="0.00" required="" aria-required="true">
                    <i class="fa fa-usd form-control-feedback"></i>  
                </div> 
            </div>

            <div class="col-md-3"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">% de Propina Sugerida: </label> 
                    <input type="text" class="form-control" name="porcentajepropina" id="porcentajepropina" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese % de Propina en Ventas" value="0.00" required="" aria-required="true">
                    <i class="fa fa-usd form-control-feedback"></i>  
                </div> 
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Moneda Nacional: <span class="symbol required"></span></label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <select style="color:#000;font-weight:bold;" name="codmoneda" id="codmoneda" class='form-control' required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <?php
                    $moneda = new Login();
                    $moneda = $moneda->ListarTipoMoneda();
                    if($moneda==""){ 
                        echo "";
                    } else {
                    for($i=0;$i<sizeof($moneda);$i++){ ?>
                    <option value="<?php echo $moneda[$i]['codmoneda'] ?>"><?php echo $moneda[$i]['moneda'] ?></option>
                    <?php } } ?>
                    </select> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Moneda Cambio: </label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <select style="color:#000;font-weight:bold;" name="codmoneda2" id="codmoneda2" class='form-control' required="" aria-required="true">
                    <option value="0"> -- SELECCIONE -- </option>
                    <?php
                    $moneda = new Login();
                    $moneda = $moneda->ListarTipoMoneda();
                    if($moneda==""){ 
                        echo "";
                    } else {
                    for($i=0;$i<sizeof($moneda);$i++){ ?>
                    <option value="<?php echo $moneda[$i]['codmoneda'] ?>"><?php echo $moneda[$i]['moneda'] ?></option>
                    <?php } } ?>
                    </select> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Mostrar Ticket Cocina: <span class="symbol required"></span></label> 
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" name="comanda_cocina" id="cocina1" value="1" checked="checked" class="custom-control-input">
                    <label class="custom-control-label" for="cocina1">SI</label>
                    </div><br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" name="comanda_cocina" id="cocina2" value="0" class="custom-control-input">
                    <label class="custom-control-label" for="cocina2">NO</label>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Mostrar Ticket Bar: <span class="symbol required"></span></label> 
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" name="comanda_bar" id="bar1" value="1" checked="checked" class="custom-control-input">
                    <label class="custom-control-label" for="bar1">SI</label>
                    </div><br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" name="comanda_bar" id="bar2" value="0" class="custom-control-input">
                    <label class="custom-control-label" for="bar2">NO</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Mostrar Ticket Reposteria: <span class="symbol required"></span></label>
                    <br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" name="comanda_reposteria" id="reposteria1" value="1" checked="checked" class="custom-control-input">
                    <label class="custom-control-label" for="reposteria1">SI</label>
                    </div><br>
                    <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" name="comanda_reposteria" id="reposteria2" value="0" class="custom-control-input">
                    <label class="custom-control-label" for="reposteria2">NO</label>
                    </div>
                </div>
            </div>

             <div class="col-md-3"> 
                <div class="form-group has-feedback2"> 
                    <label class="control-label">Membrete en Ticket: </label> 
                    <textarea class="form-control" type="text" name="membrete" id="membrete" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Membrete en Ticket" rows="3"></textarea>
                    <i class="fa fa-comment-o form-control-feedback2"></i> 
                </div> 
            </div>

            <div class="col-md-6"> 
                <div class="form-group has-feedback">
                    <div class="fileinput fileinput-new" data-provides="fileinput">
                        <div class="form-group has-feedback"> 
                            <label class="control-label">Realice la Búsqueda de Imagen: </label>
                            <div class="input-group">
                            <div class="form-control" data-trigger="fileinput"><i class="fa fa-file-photo-o fileinput-exists"></i>
                            <span class="fileinput-filename"></span>
                            </div>
                            <span class="input-group-addon btn btn-success btn-file">
                            <span class="fileinput-new"><i class="fa fa-cloud-upload"></i> Selecciona Imagen</span>
                            <span class="fileinput-exists"><i class="fa fa-file-photo-o"></i> Cambiar</span>
                            <input type="file" class="btn btn-default" data-original-title="Subir Imagen" data-rel="tooltip" placeholder="Suba su Imagen" name="imagen" id="imagen" autocomplete="off" title="Buscar Archivo">
                            </span>
                            <a href="#" class="input-group-addon btn btn-dark fileinput-exists" data-dismiss="fileinput"><i class="fa fa-trash-o"></i> Quitar</a>
                            </div><small><p>Para Subir Logo de Sucursal debe tener en cuenta:<br> * La Imagen debe ser extension.png<br> * La imagen no debe ser mayor de 2 MB</p></small>
                        </div>
                    </div>
                </div> 
            </div>

            </div>
        </div>

            <div class="modal-footer">
                <button type="submit" name="btn-submit" id="btn-submit" class="btn btn-warning"><span class="fa fa-save"></span> Guardar</button>
                <button class="btn btn-dark" type="button" onclick="
                document.getElementById('proceso').value = 'save',
                document.getElementById('codsucursal').value = '',
                document.getElementById('nrosucursal').value = '',
                document.getElementById('documsucursal').value = '',
                document.getElementById('cuitsucursal').value = '',
                document.getElementById('nomsucursal').value = '',
                document.getElementById('id_provincia').value = '',
                document.getElementById('id_departamento').value = '',
                document.getElementById('direcsucursal').value = '',
                document.getElementById('correosucursal').value = '',
                document.getElementById('tlfsucursal').value = '',
                document.getElementById('nroactividadsucursal').value = '',
                document.getElementById('inicioticket').value = '',
                document.getElementById('inicioboleta').value = '',
                document.getElementById('iniciofactura').value = '',
                document.getElementById('inicionotacredito').value = '',
                document.getElementById('fechaautorsucursal').value = '',
                document.getElementById('llevacontabilidad').value = '',
                document.getElementById('documencargado').value = '',
                document.getElementById('dniencargado').value = '',
                document.getElementById('nomencargado').value = '',
                document.getElementById('tlfencargado').value = '',
                document.getElementById('descsucursal').value = '0.00',
                document.getElementById('porcentajepropina').value = '0.00',
                document.getElementById('codmoneda').value = '',
                document.getElementById('codmoneda2').value = '',
                document.getElementById('membrete').value = '',
                document.getElementById('imagen').value = ''
                " data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cerrar</button>
            </div>
        </form>

    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal --> 
<!--############################## MODAL PARA GESTION DE SUCURSALES ######################################-->

    
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
                                <li class="breadcrumb-item">Administración</li>
                                <li class="breadcrumb-item active" aria-current="page">Sucursales</li>
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
                <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Sucursales</h4>
            </div>

            <div id="save">
            <!-- error will be shown here ! -->
            </div>

            <div class="form-body">

                <div class="card-body">

                <div class="row">
                    <div class="col-md-7">
                    <?php if ($_SESSION['acceso'] == "administradorG") { ?>
                        <div class="btn-group m-b-20">
                            <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nueva Sucursal" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalSucursal" data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i> Nuevo</button>

                            <a class="btn waves-effect waves-light btn-light" href="reportepdf?tipo=<?php echo encrypt("SUCURSALES") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("SUCURSALES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("SUCURSALES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                        </div>
                    <?php } ?>
                    </div>
                </div>

                <div id="sucursales"></div>

            </div>
        </div>
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
    <script type="text/javascript" src="assets/script/jquery.mask.js"></script>
    <script type="text/javascript" src="assets/script/mask.js"></script>
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
    <script type="text/jscript">
    $('#sucursales').append('<center><i class="fa fa-spin fa-spinner"></i> Por favor espere, cargando registros ......</center>').fadeIn("slow");
    setTimeout(function() {
    $('#sucursales').load("consultas?CargaSucursales=si");
     }, 200);
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