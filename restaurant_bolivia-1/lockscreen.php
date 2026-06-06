<?php
require_once("class/class.php");
if (isset($_SESSION['acceso'])) {

$tra = new Login();

if(isset($_POST["proceso"]) and $_POST["proceso"]=="login")
{
    $log = $tra->Logueo();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="Ing. Ruben Chirinos">
<link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
    <title></title>
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- script jquery -->
    <script src="assets/script/jquery.min.js"></script> 
    <script type="text/javascript" src="assets/script/titulos.js"></script>
    <script type="text/javascript" src="assets/script/validation.min.js"></script>
    <script type="text/javascript" src="assets/script/script.js"></script>
    <!-- script jquery -->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>

<body>
    <div class="main-wrapper">
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <!-- Preloader -->
        <div class="preloader" style="display: none;">
          <div class="cssload-speeding-wheel"></div>
        </div>
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center" style="background:url(assets/images/bg-home.png) no-repeat center top;">
            <div class="auth-box">

              <div id="loginform">

                <div class="logo">
                  <span class="db"><?php if (file_exists("fotos/logo_principal.png")){
                    echo "<img src='fotos/logo_principal.png' width='86%' height='64px' alt='Logo Principal'>"; 
                            } else {
                    echo "<img src='' width='86%' height='64px' alt='Logo Principal'>"; 
                            } 
                    ?></span>
                  <h5 class="font-medium"></h5>
                </div><hr>

                <!-- Form -->
                <div class="row">
                  <div class="col-12">

                   <form class="form form-material new-lg-form" name="lockscreen" id="lockscreen" action="">

                    <div id="login">
                      <!-- error will be shown here ! -->
                    </div>

                    <div class="form-group">
                      <div class="col-xs-12 text-center">
                        <div class="user-thumb text-center"> 

                          <?php if($_SESSION['acceso']=="cocinero"){

                                if (isset($_SESSION['dni'])) {
                                    if (file_exists("fotos/".$_SESSION['dni'].".jpg")){
                                    echo "<img src='fotos/".$_SESSION['dni'].".jpg?' width='100' height='100' class='rounded-circle'>"; 
                                    } else {
                                    echo "<img src='fotos/cocinero.png' width='100' class='rounded-circle'>"; 
                                    } } else {
                                    echo "<img src='fotos/cocinero.png' width='100' class='rounded-circle'>"; 
                                    }

                            } elseif($_SESSION['acceso']=="bar"){

                                if (isset($_SESSION['dni'])) {
                                    if (file_exists("fotos/".$_SESSION['dni'].".jpg")){
                                    echo "<img src='fotos/".$_SESSION['dni'].".jpg?' width='100' height='100' class='rounded-circle'>"; 
                                    } else {
                                    echo "<img src='fotos/bar.png' width='100' class='rounded-circle'>"; 
                                    } } else {
                                    echo "<img src='fotos/bar.png' width='100' class='rounded-circle'>"; 
                                    }

                            } elseif($_SESSION['acceso']=="reposteria"){

                                if (isset($_SESSION['dni'])) {
                                    if (file_exists("fotos/".$_SESSION['dni'].".jpg")){
                                    echo "<img src='fotos/".$_SESSION['dni'].".jpg?' width='100' height='100' class='rounded-circle'>"; 
                                    } else {
                                    echo "<img src='fotos/reposteria.png' width='100' class='rounded-circle'>"; 
                                    } } else {
                                    echo "<img src='fotos/reposteria.png' width='100' class='rounded-circle'>"; 
                                    }

                            } elseif($_SESSION['acceso']=="mesero"){

                                if (isset($_SESSION['dni'])) {
                                    if (file_exists("fotos/".$_SESSION['dni'].".jpg")){
                                    echo "<img src='fotos/".$_SESSION['dni'].".jpg?' width='100' height='100' class='rounded-circle'>"; 
                                    } else {
                                    echo "<img src='fotos/mesero.png' width='100' class='rounded-circle'>"; 
                                    } } else {
                                    echo "<img src='fotos/mesero.png' width='100' class='rounded-circle'>"; 
                                    }


                            } elseif($_SESSION['acceso']=="repartidor"){

                                if (isset($_SESSION['dni'])) {
                                    if (file_exists("fotos/".$_SESSION['dni'].".jpg")){
                                    echo "<img src='fotos/".$_SESSION['dni'].".jpg?' width='100' height='100' class='rounded-circle'>"; 
                                    } else {
                                    echo "<img src='fotos/repartidor.png' width='100' class='rounded-circle'>"; 
                                    } } else {
                                    echo "<img src='fotos/repartidor.png' width='100' class='rounded-circle'>"; 
                                    }

                            } else {

                                if (isset($_SESSION['dni'])) {
                                    if (file_exists("fotos/".$_SESSION['dni'].".jpg")){
                                    echo "<img src='fotos/".$_SESSION['dni'].".jpg?' width='100' height='100' class='rounded-circle'>"; 
                                    } else {
                                    echo "<img src='fotos/avatar.png' width='100' class='rounded-circle'>"; 
                                    } } else {
                                    echo "<img src='fotos/avatar.png' width='100' class='rounded-circle'>"; 
                                    }
                            }
                          ?>
                          <h4 class="text-white"><?php echo $_SESSION['nombres'] ?></h4>
                          <p align="center" class="text-white">Introduzca su Contraseña para acceder al sistema</p>
                        </div>
                      </div>
                    </div>


                    <div class="row">
                      <div class="col-lg-12">
                        <div class="form-group has-feedback">
                        <div class="campo">
                          <label class="control-label text-white">Ingrese su Password: <a class="symbol required"></a></label>
                          <input type="hidden" name="proceso" id="proceso" value="login"/>
                          <input type="hidden" name="usuario" id="usuario" value="<?php echo $_SESSION['usuario'] ?>">
                          <input class="form-control text-white" type="password" placeholder="Ingrese su Password" name="password" id="txtPassword" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" required="" aria-required="true"><span id="show_password" class="mdi mdi-eye icon text-white" onclick="MostrarPassword()"></span>
                        </div>
                        <i class="fa fa-key form-control-feedback text-white"></i> 
                        </div>
                      </div>
                    </div>

                    <div class="form-group">
                      <div class="col-md-12 text-center">
                       <a href="logout" class="text-white"><span class="fa fa-exclamation-triangle"></span> No Acceder con <?php echo $_SESSION['nombres']?>?</a>         
                      </div>
                    </div>

                    <div class="form-group text-center m-t-20">
                      <div class="col-xs-12">
                        <span id="submit_login"><button type="submit" name="btn-login" id="btn-login" class="btn btn-outline-warning btn-lg btn-block waves-effect waves-light" data-toggle="tooltip" data-placement="top" title="" data-original-title="Haga clic aquí para iniciar sesión"><span class="fa fa-sign-in"></span> Acceder</button></span>
                      </div>
                    </div>

                  </form>
                </div>
              </div>
            </div>

          </div>

        </div>
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper scss in scafholding.scss -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper scss in scafholding.scss -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Right Sidebar -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Right Sidebar -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- All Required js -->
    <!-- ============================================================== -->
    <!-- jQuery -->
    <script src="assets/js/jquery.js"></script>
    <script type="text/javascript" src="assets/script/password.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- Menu Plugin JavaScript -->
    <script src="assets/js/sidebar-nav.js"></script>

    <!--slimscroll JavaScript -->
    <script src="assets/js/jquery_002.js"></script>
    <!--Wave Effects -->
    <script src="assets/js/waves.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="assets/js/custom.js"></script>
    <!-- jQuery -->
    <script src="assets/plugins/noty/packaged/jquery.noty.packaged.min.js"></script>
</body>

</html>
<?php } else { ?>
        <script type='text/javascript' language='javascript'>
        alert('NO TIENES PERMISO PARA ACCEDER AL SISTEMA.\nDEBERA DE INICIAR SESION')  
        document.location.href='logout'  
        </script> 
<?php } ?>