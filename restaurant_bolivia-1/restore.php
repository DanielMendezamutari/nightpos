<?php
header("Content-Type: text/html;charset=utf-8");
require_once("class/class.php");
if (isset($_SESSION['acceso'])) {
    if ($_SESSION['acceso'] == "administradorG" || $_SESSION['acceso'] == "administradorS") {

$tra = new Login();
$ses = $tra->ExpiraSession();          
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
        <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Restauración</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Administración</li>
                                <li class="breadcrumb-item active" aria-current="page">Base de Datos</li>
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
                                <h4 class="card-title text-white"><i class="fa fa-navicon"></i> Restauración</h4>
                            </div>
                            
<form class="form validacion form-material" method="post" action="restore" enctype="multipart/form-data">

    <div class="form-body">

        <div class="card-body">

<?php
// Motrar todos los errores de PHP
//error_reporting(E_ALL - E_NOTICE);
error_reporting(0);
ini_set('upload_max_filesize', '80M');
ini_set('post_max_size', '80M');
ini_set('memory_limit', '-1'); //evita el error Fatal error: Allowed memory size of X bytes exhausted (tried to allocate Y bytes)...
ini_set('max_execution_time', 300); // es lo mismo que set_time_limit(300) ;
ini_set('mysql.connect_timeout', 300);
ini_set('default_socket_timeout', 300);
//En MYSQL archivo "my.ini" ==> max_allowed_packet = 22M
//"SET GLOBAL max_allowed_packet = 22M;"
//"SET GLOBAL connect_timeout = 20;"
//"SET GLOBAL net_read_timeout=50;"
//esto no se si solo es modificable en php.ini
ini_set('file_uploads','On'); 
ini_set('upload_tmp_dir','upload');

$host= "localhost";  //valor predeterminado 
$usuario= "root"; //valor predeterminado
$passwd= ""; //valor predeterminado
$bd= "softrestaurant_bolivia"; //parametro obligatorio   
$puerto="3306"; //parametro obligatorio

function convertir_utf8($cadena) { 
    if ( strlen(utf8_decode($cadena)) == strlen($cadena) ) {           
                // $cadena is not UTF-8            
        return iconv("ISO-8859-1", "UTF-8//TRANSLIT", $cadena);
    } else {    // already UTF-8
        return $cadena;
    }
    // Alternativa0 $strSQLs = mb_convert_encoding($strSQLs, 'HTML-ENTITIES', "UTF-8");
    // Alternativa1 $strSQLs = iconv('ISO-8859-1','UTF-8//TRANSLIT',$strSQLs);
    // Alternativa2 $strSQLs = mb_convert_encoding($strSQLs, 'UTF-8',  mb_detect_encoding($strSQLs, 'UTF-8, ISO-8859-1', true));
}
                
function Debug($Texto,$variable,$crear=false){
   if ($gestor = fopen("log.txt", ($crear)?'w':'a')){ 
           $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);
          fwrite($gestor, date("Y-m-d H:i:s.").$micro ."==***==== $Texto ===***===\r\n".print_r($variable,true)."\r\n") ;
         fclose($gestor); 
   } else die("NO SE PUEDE CREAR log.txt");
}

function CadenaAcotada($Cadena,$CadenaInicio,$CadenaFinal){
    $inicio=stripos(" ".$Cadena,$CadenaInicio);//ponemos un espacio para distinguir entre no encontrado o estar al prinicipio
    if ($inicio) {
        $inicio += strlen($CadenaInicio)-1;
        $fin=stripos($Cadena,$CadenaFinal,$inicio);
        if (!$fin) return false;
        return substr($Cadena,$inicio,$fin-$inicio);
    }else  return false;
}

function TableToHTML($conexion,$strSQL){
    //$conexion  viene de hacer $conexion = new mysqli(...);
    //$strSQL es el SQL que retorna tegistos p.ej. SELECT
    $rs = $conexion->query($strSQL)or die("No se puede ejecutar la consulta: $strSQL MySQL: \n".$conexion->error);
    $num_rows= $rs->num_rows;
    if ( $num_rows <> 0) {
            $regSELECT= $rs->fetch_assoc();
            $strHTML = '<table border="1" cellpadding="10" cellspacing="1" > <thead> <tr>';
            $x=0;
            foreach ($regSELECT as $key=>$value) {
                $strHTML .= "<th><b>".$key."</b></th>";
            }     
            $strHTML .= "</tr> </thead> <tbody>";
            do {
                $strHTML .= "<tr>";
                $x=0;
                foreach ($regSELECT as $value) {
                    $strHTML .= "<td > ".$value." </td>";                                
                }
                $strHTML .= "</tr>";
            } While  ($regSELECT= $rs->fetch_assoc());
            $strHTML .= " </tbody> </table>";
            return nl2br($strHTML);
    }
    return "Sin registros en resultado";
}

function run_split_sql($uploadfile, $host, $usuario, $passwd, $bd){
    $strSQLs = file_get_contents($uploadfile);
    unlink($uploadfile);
    //  Elimina lineas vacias o que empiezan por -- #   //   o entre /* y */
    // Elimna los espacios en blanco entre ; y \r\n
    // handle DOS and Mac encoded linebreaks
    $strSQLs=preg_replace("/\r\n$/","\n",$strSQLs);
    $strSQLs=preg_replace("/\r$/","\n",$strSQLs);
    $strSQLs = trim(preg_replace('/ {2,}/', ' ', $strSQLs));    // ----- remove multiple spaces ----- 
    $strSQLs = str_replace("\r","",$strSQLs);                     //los \r\n los dejamos solo en \n
    $lines=explode("\n",$strSQLs);
    $strSQLs = array();
    $in_comment = false;
    foreach ($lines as $key => $line){
        $line=trim($line); //preg_replace("#.*/#","",$line)
        $ignoralinea =(("#" == $line) || ("--" == substr($line,0,2)) || (!$line) || ($line==""));
        if (!$ignoralinea){
            //Eliminar comentarios que empiezan por /* y terminan por */    
            if( preg_match("/^\/\*/", ($line)) ) $in_comment = true;
            if( !$in_comment ) $strSQLs[] = $line ;
            if( preg_match("/\*\//", ($line)) ) $in_comment = false;
        }
    }
    unset($lines);
    
// Particionar en sentencias
$IncludeDelimiter=false;
$delimiter=";";
$delimiterLen= 1;
$sql="";
// CONEXION 
$conexion = new mysqli($host, $usuario, $passwd, $bd) or die ("No se puede conectar con el servidor MySQL: %s\n". $conexion->connect_error);
//$conexion = new mysqli('localhost','root','','softsioux',3306) or die ("No se puede conectar con el servidor MySQL: %s\n". $conexion->connect_error);

$NumLinea=0;
foreach ($strSQLs as $key => $line){

    if ("DELIMITER" == substr($line,0,9)){  //empieza por DELIMITER
        $D=explode(" ",$line);
        $delimiter= $D[1];
        $delimiterLen= strlen($delimiter);
        $sql=($IncludeDelimiter)? $line ."\n" : "";
    }elseif (substr($line,-1*$delimiterLen) == $delimiter) { //hemos alcanzado el  Delimiter
            if (($NumLinea++ % 100)==0) {// ver con que base de datos estamos para poder reconectar caso de error
                $respuesta = $conexion->query("select database() as db");
                $row = $respuesta->fetch_array(MYSQLI_NUM);
                $db=$row[0];
            }
            $sql .= ($IncludeDelimiter)? $line : substr($line,0,-1*$delimiterLen);
            $respuesta = $conexion->query($sql);
            if ($respuesta) echo "";
            
            else {
               echo "";
               if (!$conexion->ping() ){ 

                $conexion = new mysqli($host, $usuario, $passwd, $bd) or die ("No se puede conectar con el servidor MySQL: %s\n". $conexion->connect_error);
                //$conexion = new mysqli('localhost','root','','softsioux',3306) or die ("No se puede conectar con el servidor MySQL: %s\n". $conexion->connect_error);
                $respuesta = $conexion->query($sql);
                if ($respuesta) echo "<br>$NumLinea REEJECUTADO:  ". str_replace("\n"," ",substr($sql,0,130))."...";
                else echo "<br><b><u>$NumLinea REPITE-E R R O R: ".$conexion->errno." :</u></b>". $conexion->error ." ====> ". substr($sql,0,1022)."...";
            }
        }    

        $sql="";
    } else { 
        $sql .= $line ."\n";
    }
}
$conexion->close();    
}

if (isset($_POST['upload'])) {
    $uploadfile = "../" . basename($_FILES['userfile']['name']);
    print '';
    switch ($_FILES['userfile']['error']){
    case 0:
    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {

    echo"<div align='center' class='alert alert-success'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<span class='fa fa-check-square-o'></span> LA COPIA DE SEGURIDAD <b> $uploadfile </b> SE HA RESTAURADO CORRECTAMENTE </div>";

   // echo" LA COPIA DE SEGURIDAD <b> $uploadfile </b> SE HA RESTAURADO CORRECTAMENTE</div>";
    run_split_sql($uploadfile, $host, $usuario, $passwd, $bd);
    } else     
    echo "<br>¡Posible error en carga de archivos!";
    break;
    case 1: // UPLOAD_ERR_INI_SIZE
    echo "<br>El archivo sobrepasa el limite autorizado por el servidor(archivo php.ini) !";
    break;
    case 2: // UPLOAD_ERR_FORM_SIZE
    echo "<br>El archivo sobrepasa el limite autorizado en el formulario HTML !";
    break;
    case 3: // UPLOAD_ERR_PARTIAL
    echo "<br>El envio del archivo ha sido suspendido durante la transferencia!";
    break;
    case 4: // UPLOAD_ERR_NO_FILE
    echo "<br><font color='red'> Por Favor seleccione el backup de la base de datos para restaurar !</font>";
    break;
    default: 
    echo "<br>ERROR DESCONOCIDO !"; 
    break;
}
print "";
unset($_POST['upload']);
$_POST[]=array();
}
?>  

<div class="row">
    <div class="col-md-12"> 
        <div class="form-group has-feedback">
            <div class="fileinput fileinput-new" data-provides="fileinput">
                <div class="form-group has-feedback"> 
                <label class="control-label">Realice la búsqueda del Archivo: <span class="symbol required"></span></label>
                <div class="input-group">
                    <div class="form-control" data-trigger="fileinput"><i class="fa fa-file-photo-o fileinput-exists"></i>
                       <span class="fileinput-filename"></span>
                    </div>
                   <span class="input-group-addon btn btn-success btn-file">
                    <span class="fileinput-new"><i class="fa fa-cloud-upload"></i> Selecciona Archivo</span>
                    <span class="fileinput-exists"><i class="fa fa-file-photo-o"></i> Cambiar</span>
                    <input type="file" class="btn btn-default" data-original-title="Subir Imagen" data-rel="tooltip" placeholder="Suba su Imagen" name="userfile" id="userfile" autocomplete="off" title="Buscar Archivo" required="" aria-required="true">
                   </span>
                   <a href="#" class="input-group-addon btn btn-dark fileinput-exists" data-dismiss="fileinput"><i class="fa fa-trash-o"></i> Quitar</a>
                </div><small><p>Realice la búsqueda del Archivo para restaurar la Base de Datos</p></small>
                </div>
            </div>
        </div> 
    </div>
</div>

            <div class="text-right">
                <button type="submit" name="upload" id="upload" class="btn btn-warning"><span class="fa fa-cloud-upload"></span> Restaurar</button>
                <button class="btn btn-dark" type="reset"><span class="fa fa-trash-o"></span> Cancelar</button>
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
    <script type="text/javascript">
    $(document).ready(function(){
        $(".validacion").validate();
    });
    </script>
    <!-- script jquery -->

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