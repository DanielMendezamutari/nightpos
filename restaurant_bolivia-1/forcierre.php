<?php
require_once("class/class.php"); 
if(isset($_SESSION['acceso'])) { 
if ($_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="secretaria" || $_SESSION["acceso"]=="cajero") {

$tra = new Login();
$ses = $tra->ExpiraSession(); 

if(isset($_POST["proceso"]) and $_POST["proceso"]=="cerrar")
{
$reg = $tra->CerrarArqueoCaja();
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

<body onLoad="muestraReloj(); getTime();" class="fix-header">
    
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
                    <h5 class="font-medium text-uppercase mb-0"><i class="fa fa-tasks"></i> Gestión de Cierre</h5>
                    </div>
                    <div class="col-lg-9 col-md-8 col-xs-12 align-self-center">
                        <nav aria-label="breadcrumb" class="mt-2 float-md-right float-left">
                            <ol class="breadcrumb mb-0 justify-content-end p-0">
                                <li class="breadcrumb-item">Mantenimiento</li>
                                <li class="breadcrumb-item active" aria-current="page">Cierre de Caja</li>
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
 
     
    <?php  if (isset($_GET['codarqueo'])) {
      
    $j = new Login();
    $reg = $j->ArqueoCajaPorId();
    $simbolo = ($reg == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>"); ?>
      
    <form class="form form-material" method="post" action="#" name="savecerrararqueo" id="savecerrararqueo" data-id="<?php echo $reg[0]["codarqueo"]; ?>">
      
    <?php } ?>


<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Detalle de Caja</h4>
            </div>

            <div id="save">
            <!-- error will be shown here ! -->
            </div>


            <div class="form-body">

    <?php 
    $VentaGeneral = $reg[0]['efectivo']+$reg[0]['cheque']+$reg[0]['tcredito']+$reg[0]['tdebito']+$reg[0]['tprepago']+$reg[0]['transferencia']+$reg[0]['electronico']+$reg[0]['cupon']+$reg[0]['otros']+$reg[0]['creditos']+$reg[0]['propinasefectivo']+$reg[0]['propinascheque']+$reg[0]['propinastcredito']+$reg[0]['propinastdebito']+$reg[0]['propinastprepago']+$reg[0]['propinastransferencia']+$reg[0]['propinaselectronico']+$reg[0]['propinascupon']+$reg[0]['propinasotros'];

    $VentaOtros = $reg[0]['cheque']+$reg[0]['tcredito']+$reg[0]['tdebito']+$reg[0]['tprepago']+$reg[0]['transferencia']+$reg[0]['electronico']+$reg[0]['cupon']+$reg[0]['otros']+$reg[0]['creditos']+$reg[0]['propinascheque']+$reg[0]['propinastcredito']+$reg[0]['propinastdebito']+$reg[0]['propinastprepago']+$reg[0]['propinastransferencia']+$reg[0]['propinaselectronico']+$reg[0]['propinascupon']+$reg[0]['propinasotros'];

    $SiEfectivo = $reg[0]['efectivo']+$reg[0]['propinasefectivo'];

    $NoEfectivo = $reg[0]['cheque']+$reg[0]['tcredito']+$reg[0]['tdebito']+$reg[0]['tprepago']+$reg[0]['transferencia']+$reg[0]['electronico']+$reg[0]['cupon']+$reg[0]['otros']+$reg[0]['creditos']+$reg[0]['propinascheque']+$reg[0]['propinastcredito']+$reg[0]['propinastdebito']+$reg[0]['propinastprepago']+$reg[0]['propinastransferencia']+$reg[0]['propinaselectronico']+$reg[0]['propinascupon']+$reg[0]['propinasotros'];

    $AbonosGeneral = $reg[0]['abonosefectivo']+$reg[0]['abonosotros'];

    $AbonosSi = $reg[0]['abonosefectivo'];

    $AbonosNo = $reg[0]['abonosotros'];

    $TotalEfectivo = $reg[0]['montoinicial']+$reg[0]['efectivo']+$reg[0]['propinasefectivo']+$reg[0]['ingresosefectivo']+$reg[0]['abonosefectivo']-$reg[0]['egresos'];
    ?>        

                <div class="card-body">

    <h3 class="card-subtitle m-0 text-dark"><i class="font-20 mdi mdi-monitor"></i> Detalle de Caja</h3><hr>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Caja: </label>
                    <input type="hidden" name="proceso" id="proceso" value="cerrar"/>
                    <input type="hidden" name="formulario" id="formulario" value="forcierre"/>
                    <input type="hidden" name="codarqueo" id="codarqueo" value="<?php echo encrypt($reg[0]["codarqueo"]); ?>" /> <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="caja" id="caja" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo $reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]; ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-desktop form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nombre de Cajero: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="nombres" id="nombres" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo $reg[0]["nombres"]; ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-user form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Monto Apertura: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="inicial" id="inicial" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]["montoinicial"], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Fecha Apertura: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="apertura" id="apertura" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo date("d-m-Y H:i:s",strtotime($reg[0]["fechaapertura"])); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-calendar form-control-feedback"></i> 
                </div>
            </div>
        </div>


    <h3 class="card-subtitle m-0 text-dark"><i class="font-20 mdi mdi-presentation"></i> Desglose por Forma de Pago</h3><hr>
                
        <div class="row">
            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Efectivo: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="efectivo" id="efectivo" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['efectivo'], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Cheque: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="cheque" id="cheque" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['cheque'], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Tarjeta de Crédito: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="tcredito" id="tcredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]["tcredito"], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Tarjeta de Débito: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="tdebito" id="tdebito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]["tdebito"], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Tarjeta Prepago: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="tprepago" id="tprepago" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]["tprepago"], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>
            
            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Transferencia: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="transferencia" id="transferencia" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['transferencia'], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Electronico: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="electronico" id="electronico" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]["electronico"], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Cupón: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="cupon" id="cupon" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]["cupon"], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Otros: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="otros" id="otros" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]["otros"], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Propinas en Efectivo: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="propinasefectivo" id="propinasefectivo" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]["propinasefectivo"], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group has-feedback">
                    <label class="control-label">Propinas Otros: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="propinasotros" id="propinasotros" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['propinascheque']+$reg[0]['propinastcredito']+$reg[0]['propinastdebito']+$reg[0]['propinastprepago']+$reg[0]['propinastransferencia']+$reg[0]['propinaselectronico']+$reg[0]['propinascupon']+$reg[0]['propinasotros'], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

        </div>

        <h3 class="card-subtitle m-0 text-dark"><i class="font-20 mdi mdi-presentation"></i> Desglose por Nº Documentos</h3><hr>
                
        <div class="row">
        
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Ticket: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="nroticket" id="nroticket" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo $reg[0]["nroticket"]; ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Boletas: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="nroboleta" id="nroboleta" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo $reg[0]["nroboleta"]; ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Facturas: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="nrofactura" id="nrofactura" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo $reg[0]["nrofactura"]; ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nº de Notas: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="nronota" id="nronota" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo $reg[0]["nronota"]; ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-bolt form-control-feedback"></i> 
                </div>
            </div>
        </div>

    <h3 class="card-subtitle m-0 text-dark"><i class="font-20 mdi mdi-scale-balance"></i> Balance en Caja</h3><hr>


        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Ventas General: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="ventas" id="ventas" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($VentaGeneral, 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Ventas (Efectivo): </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="vefectivo" id="vefectivo" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($SiEfectivo, 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Ventas (No Efectivo): </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="nefectivo" id="nefectivo" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($NoEfectivo, 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Ventas a Créditos: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="creditos" id="creditos" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]["creditos"], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Abon. Créditos (Efectivo): </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="abonose" id="abonose" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($AbonosSi, 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Abon. Créditos (No Efectivo): </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="abonosn" id="abonosn" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($AbonosNo, 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Ingresos (Efectivo): </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="ingresoe" id="ingresoe" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['ingresosefectivo'], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Ingresos (No Efectivo): </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="ingreson" id="ingreson" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['ingresosotros'], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Egresos: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="egresos" id="egresos" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['egresos'], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Nota de Crédito: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="egresonotas" id="egresonotas" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['egresonotas'], 2, '.', ','); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Estimado en Caja: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="estimado" id="estimado" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" value="<?php echo number_format($TotalEfectivo, 2, '.', ''); ?>" disabled="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Diferencia: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="diferencia" id="diferencia" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="0.00" required="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Efectivo Disponible: <span class="symbol required"></span></label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control cierrecaja" name="dineroefectivo" id="dineroefectivo" onKeyUp="this.value=this.value.toUpperCase();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" value="<?php echo number_format($TotalEfectivo, 2, '.', ''); ?>" required="" aria-required="true"/>  
                    <i class="fa fa-dollar form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group has-feedback">
                    <label class="control-label">Hora de Cierre: <span class="symbol required"></span></label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="fecharegistro" id="fecharegistro" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Hora de Cierre" autocomplete="off" readonly="" aria-required="true"/> 
                    <i class="fa fa-clock-o form-control-feedback"></i> 
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group has-feedback">
                    <label class="control-label">Observaciones: </label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="comentarios" id="comentarios" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Observaciones de Cierre" autocomplete="off" required="" aria-required="true"/> 
                    <i class="fa fa-comment-o form-control-feedback"></i> 
                </div>
            </div>
        </div>


            <div class="text-right">
<?php if($reg[0]["statusarqueo"] == 1){ ?>
<button type="submit" name="btn-submit" id="btn-submit" class="btn btn-warning"><span class="fa fa-archive"></span> Cerrar Caja</button>
<button class="btn btn-dark" type="reset" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cancelar</button>
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