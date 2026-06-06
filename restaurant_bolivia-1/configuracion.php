<?php
require_once("class/class.php");
if (isset($_SESSION['acceso'])) {
    if ($_SESSION['acceso'] == "administradorG") {

$tra = new Login();
$ses = $tra->ExpiraSession();

$reg = $tra->ConfiguracionPorId();

if(isset($_POST["proceso"]) and $_POST["proceso"]=="update")
{
$reg = $tra->ActualizarConfiguracion();
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
            <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Configuración</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Administración</li>
                                <li class="breadcrumb-item active" aria-current="page">Configuración</li>
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
                <h4 class="card-title text-white"><i class="fa fa-save"></i> Configuración</h4>
            </div>
            <form class="form-material" method="post" action="#" name="configuracion" id="configuracion" enctype="multipart/form-data">

            <div id="save">
               <!-- error will be shown here ! -->
            </div>

            <div class="form-body">

            <div class="card-body">
        
        
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Tipo de Documento: </label>
                        <i class="fa fa-bars form-control-feedback"></i> 
                        <select style="color:#000;font-weight:bold;" name="documsucursal" id="documsucursal" class='form-control' required="" aria-required="true">
                        <option value=""> -- SELECCIONE -- </option>
                        <?php
                        $doc = new Login();
                        $doc = $doc->ListarDocumentos();
                        if($doc==""){
                            echo "";    
                        } else {
                        for($i=0;$i<sizeof($doc);$i++){ ?>
                        <option value="<?php echo $doc[$i]['coddocumento']; ?>"<?php if (!(strcmp($reg[0]['documsucursal'], htmlentities($doc[$i]['coddocumento'])))) { echo "selected=\"selected\""; } ?>><?php echo $doc[$i]['documento']; ?></option>
                        <?php } } ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Nº de Registro: <span class="symbol required"></span></label>
                        <input type="hidden" name="proceso" id="proceso" value="update"/>
                        <input type="hidden" class="form-control" name="id" id="id" value="<?php echo $reg[0]['id']; ?>"/>
                        <input type="text" class="form-control" name="cuitsucursal" id="cuitsucursal" value="<?php echo $reg[0]['cuitsucursal']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Registro" autocomplete="off" required="" aria-required="true"/> 
                        <i class="fa fa-bolt form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Razón Social: <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="nomsucursal" id="nomsucursal" value="<?php echo $reg[0]['nomsucursal']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nombre de Empresa" autocomplete="off" required="" aria-required="true"/>  
                        <i class="fa fa-pencil form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Nº Actividad o Giro: </label>
                        <input type="text" class="form-control" name="codgiro" id="codgiro" value="<?php echo $reg[0]['codgiro']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Código de Giro" autocomplete="off" required="" aria-required="true"/>  
                        <i class="fa fa-pencil form-control-feedback"></i> 
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Giro de Empresa: </label>
                        <input type="text" class="form-control" name="girosucursal" id="girosucursal" value="<?php echo $reg[0]['girosucursal']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Giro de Empresa" autocomplete="off" required="" aria-required="true"/>  
                        <i class="fa fa-pencil form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Nº de Teléfono: <span class="symbol required"></span></label>
                        <input type="text" class="form-control phone-inputmask" name="tlfsucursal" id="tlfsucursal" value="<?php echo $reg[0]['tlfsucursal']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Teléfono de Empresa" autocomplete="off" required="" aria-required="true"/>  
                        <i class="fa fa-phone form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Correo Electrónico: <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="correosucursal" id="correosucursal" value="<?php echo $reg[0]['correosucursal']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Email de Empresa" autocomplete="off" required="" aria-required="true"/> 
                        <i class="fa fa-envelope-o form-control-feedback"></i> 
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
                        <option value="<?php echo $pro[$i]['id_provincia']; ?>"<?php if (!(strcmp($reg[0]['id_provincia'], htmlentities($pro[$i]['id_provincia'])))) { echo "selected=\"selected\""; } ?>><?php echo $pro[$i]['provincia']; ?></option>
                        <?php } } ?>
                        </select>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Departamento: </label>
                        <i class="fa fa-bars form-control-feedback"></i>
                        <select style="color:#000;font-weight:bold;" class="form-control" id="id_departamento" name="id_departamento" required="" aria-required="true">
                        <option value=""> -- SELECCIONE -- </option>
                        <?php
                        $dep = new Login();
                        $dep = $dep->ListarDepartamentos();
                        if($dep==""){
                            echo "";    
                        } else {
                        for($i=0;$i<sizeof($dep);$i++){ ?>
                        <option value="<?php echo $dep[$i]['id_departamento']; ?>"<?php if (!(strcmp($reg[0]['id_departamento'], htmlentities($dep[$i]['id_departamento'])))) { echo "selected=\"selected\""; } ?>><?php echo $dep[$i]['departamento']; ?></option>
                        <?php } } ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Dirección: <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="direcsucursal" id="direcsucursal" value="<?php echo $reg[0]['direcsucursal']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Dirección de Empresa" autocomplete="off" required="" aria-required="true"/>  
                        <i class="fa fa-map-marker form-control-feedback"></i> 
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
                        <option value="<?php echo $doc[$i]['coddocumento']; ?>"<?php if (!(strcmp($reg[0]['documencargado'], htmlentities($doc[$i]['coddocumento'])))) { echo "selected=\"selected\""; } ?>><?php echo $doc[$i]['documento']; ?></option>
                        <?php } } ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group has-feedback">
                       <label class="control-label">Nº de  Doc. de Encargado: <span class="symbol required"></span></label>
                       <input type="text" class="form-control" name="dniencargado" id="dniencargado" value="<?php echo $reg[0]['dniencargado']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº Documento de Gerente" autocomplete="off" required="" aria-required="true"/>  
                       <i class="fa fa-bolt form-control-feedback"></i> 
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Nombre de Encargado: <span class="symbol required"></span></label>
                        <input type="text" class="form-control" name="nomencargado" id="nomencargado" value="<?php echo $reg[0]['nomencargado']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nombre de Gerente" autocomplete="off" required="" aria-required="true"/>  
                        <i class="fa fa-pencil form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group has-feedback">
                        <label class="control-label">Nº de Teléfono: <span class="symbol required"></span></label>
                        <input type="text" class="form-control phone-inputmask" name="tlfencargado" id="tlfencargado" value="<?php echo $reg[0]['tlfencargado']; ?>" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Nº de Teléfono Encargado" autocomplete="off" required="" aria-required="true"/>  
                        <i class="fa fa-phone form-control-feedback"></i> 
                    </div>
                </div>

                <div class="col-md-3">
                  <div class="fileinput fileinput-new" data-provides="fileinput">
                      <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 120px; height: 110px;">
                        <?php if (file_exists("fotos/logo_principal.png")){
                            echo "<img src='fotos/logo_principal.png' class='img-rounded' border='0' width='120' height='110' title='Logo' data-rel='tooltip'>"; 
                        } else {
                            echo "<img src='fotos/ninguna.png' class='img-rounded' border='0' width='120' height='110' title='Sin Logo' data-rel='tooltip'>"; 
                        } ?>
                    </div>
                    <div>
                      <span class="btn btn-success btn-file">
                          <span class="fileinput-new"><i class="fa fa-file-image-o"></i> Logo Principal</span>
                          <span class="fileinput-exists"><i class="fa fa-paint-brush"></i> Logo Principal</span>
                          <input type="file" size="10" data-original-title="Subir Logo Principal" data-rel="tooltip" placeholder="Suba su Logo Principal" name="imagen" id="imagen"/>
                      </span>
                      <a href="#" class="btn btn-dark fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times-circle"></i> Remover</a><small><p>Para Subir el Logo Principal debe tener en cuenta:<br> * La Imagen debe ser extension.png<br> * La imagen no debe ser mayor de 2 MB</p></small>
                    </div>
                  </div>
              </div>

              <div class="col-md-3">
                  <div class="fileinput fileinput-new" data-provides="fileinput">
                      <div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width: 120px; height: 110px;">
                        <?php if (file_exists("fotos/logo_pdf.png")){
                            echo "<img src='fotos/logo_pdf.png' class='img-rounded' border='0' width='120' height='110' title='Logo' data-rel='tooltip'>"; 
                        } else {
                            echo "<img src='fotos/ninguna.png' class='img-rounded' border='0' width='120' height='110' title='Sin Logo' data-rel='tooltip'>"; 
                        } ?>
                    </div>
                    <div>
                      <span class="btn btn-success btn-file">
                          <span class="fileinput-new"><i class="fa fa-file-image-o"></i> Logo Principal</span>
                          <span class="fileinput-exists"><i class="fa fa-paint-brush"></i> Logo Principal</span>
                          <input type="file" size="10" data-original-title="Subir Logo Pdf" data-rel="tooltip" placeholder="Suba su Logo Principal" name="imagen2" id="imagen2"/>
                      </span>
                      <a href="#" class="btn btn-dark fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times-circle"></i> Remover</a><small><p>Para Subir el Logo Pdf debe tener en cuenta:<br> * La Imagen debe ser extension.png<br> * La imagen no debe ser mayor de 2 MB</p></small>
                    </div>
                  </div>
              </div>
          </div>

          <div class="text-right">
            <button type="submit" name="btn-update" id="btn-update" class="btn btn-warning"><span class="fa fa-save"></span> Actualizar</button>
            <button class="btn btn-dark" type="reset"><span class="fa fa-trash-o"></span> Cancelar</button>
        </div>


            </div>
        </div>
    </form>
</div>
</div>
</div>
<!--End Row -->
                
                
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