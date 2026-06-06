<?php
require_once("class/class.php");

$tra = new Login();

if(isset($_POST['btn-login']))
{
  $log = $tra->Logueo();
  exit;
}
elseif(isset($_POST["btn-recuperar"]))
{
  $reg = $tra->RecuperarPassword();
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
  <!-- Favicon icon -->
  <link rel="icon" type="image/png" sizes="16x16" href="assets/images/favicon.png">
  <title></title>

    <!-- animation CSS -->
    <link href="assets/css/animate.css" rel="stylesheet">
    <!-- needed css -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- color CSS -->
    <link href="assets/css/default.css" id="theme" rel="stylesheet">

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
      <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
          <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
        </svg>
      </div>
      <!-- ============================================================== -->
      <!-- Preloader - style you can find in spinners.css -->
      <!-- ============================================================== -->

      <!-- ============================================================== -->
      <!-- Login box.scss -->
      <!-- ============================================================== -->
      <!--<div class="auth-wrapper d-flex no-block justify-content-center align-items-center" style="background:url(assets/images/bg.png) no-repeat center center / cover !important; position: relative; background-size: cover; background-position: 100% 100%; background-attachment: fixed !important; overflow: hidden; ">-->

      <div class="auth-wrapper d-flex no-block justify-content-center align-items-center" style="background:url(assets/images/auth-bg.jpg) no-repeat center center;">
            <div class="auth-box">

              <div id="loginform">
                <div class="logo">
        <span class="db"><img src="assets/images/logo-icon.png" alt="logo" width="100" height="100"></span>
                  <h3 class="font-medium mb-0">Login de Acceso</h3>
                    <p align="center" class="text-muted">Introduzca sus datos de acceso para ingresar al Sistema</p>
                </div>
                <!-- Form -->
                <div class="row">
                  <div class="col-12">

                    <!--<form class="form-horizontal mt-3" id="loginform" action="index.html">-->
                      <form class="form form-material new-lg-form" name="login" id="login" action="">

                        <div id="error">
                          <!-- error will be shown here ! -->
                        </div>

                        <div class="row">
                          <div class="col-md-12 m-t-10">
                            <div class="form-group has-feedback">
                              <label class="control-label">Ingrese su Usuario: <span class="symbol required"></span></label>
                              <input type="text" class="form-control" placeholder="Ingrese su Usuario" name="usuario" id="usuario" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" required="" aria-required="true"> 
                              <i class="fa fa-user form-control-feedback"></i>                
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group has-feedback">
                              <label class="control-label">Ingrese su Password: <span class="symbol required"></span></label>
                              <input class="form-control" type="password" placeholder="Ingrese su Password" name="password" id="password" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" required="" aria-required="true">
                              <i class="fa fa-key form-control-feedback"></i>               
                            </div>
                          </div>
                        </div>
                        <!--#ff7676 
                        255,118,118--> 

                        <div class="form-group">
                          <div class="col-md-12">
                            <a href="javascript:void(0)" id="to-recover" class="text-dark pull-right"><i class="fa fa-lock"></i> Olvidaste tu Contraseña?</a>          
                          </div>
                        </div>

                        <div class="form-group text-center m-t-20">
                          <div class="col-xs-12">
                            <button class="btn btn-danger btn-lg btn-block text-uppercase" data-toggle="tooltip" data-placement="top" title="" data-original-title="Haga clic aquí para iniciar sesión" name="btn-login" id="btn-login" type="submit"><span class="fa fa-sign-in"></span> Acceder</button>
                          </div>
                        </div>

                      </form>
                    </div>
                  </div>
                </div>
                

                <div id="recoverform">
                  <div class="logo">
         <span class="db"><img src="assets/images/logo-icon.png" alt="logo" width="100" height="100"></span>
                    <h3 class="font-medium mb-0">Recuperar Password</h3>
                    <p align="center" class="text-muted">Ingrese su correo electrónico para que su Nueva Clave de Acceso le sea enviada al mismo!</p>
                  </div>

                  <!-- Form -->
                  <div class="row">
                    <div class="col-12">

                      <!--<form class="form-horizontal mt-3" id="loginform" action="index.html">-->
                        <form class="form form-material new-lg-form" name="recuperarpassword" id="recuperarpassword" action="">

                          <div id="error">
                            <!-- error will be shown here ! -->
                          </div>

                          <div class="row">
                            <div class="col-md-12 m-t-20">
                              <div class="form-group has-feedback">
                                <label class="control-label">Correo Electrónico: <span class="symbol required"></span></label>
                                <input type="text" class="form-control" name="email" id="email" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese su Correo Electronico" autocomplete="off" required="" aria-required="true"/> 
                                <i class="fa fa-envelope-o form-control-feedback"></i>                
                              </div>
                            </div>
                          </div>

                          <div class="form-group">
                            <div class="col-md-12">
                              <a href="javascript:void(0)" id="to-login" class="text-dark pull-right"><i class="fa fa-arrow-circle-left"></i> Acceder al Sistema</a>          
                            </div>
                          </div>

                          <div class="form-group text-center m-t-20">
                            <div class="col-xs-12">
                              <button class="btn btn-danger btn-lg btn-block text-uppercase waves-effect waves-light" name="btn-recuperar" id="btn-recuperar" type="submit"><span class="fa fa-check-square-o"></span> Recuperar</button>
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
    <!-- jQuery 
      <script src="assets/js/jquery.js"></script>-->
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

    </body></html>