<?php
require_once("class/class.php");
if (isset($_SESSION['acceso'])) {
if ($_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="secretaria" || $_SESSION["acceso"]=="cajero" || $_SESSION["acceso"]=="mesero" || $_SESSION["acceso"]=="cocinero" || $_SESSION["acceso"]=="bar" || $_SESSION["acceso"]=="reposteria" || $_SESSION["acceso"]=="repartidor") {
    
$tra = new Login();
?>

<?php
############################# CARGAR NOTIFICACIONES ############################
if (isset($_GET['CargaNotificacion']) && isset($_GET['tipo'])) { 

 $tipo = limpiar($_GET['tipo']);

 $reg = $tra->ListarNotificaciones();

if($reg==""){
    
} else { 
?>

  <!-- Row--> 
  <div class="row">

<?php 
$a=1;

for($i=0;$i<sizeof($reg);$i++){ 
?>
    <div class="col-md-2"> 
        <div class="panel-body"> 
          <div class="alert alert-info" style="background: #85d4f5;">
            <button type="button" class="close" aria-hidden="true" onClick="EliminarNotificacion('<?php echo encrypt($reg[$i]["id"]) ?>','<?php echo $tipo; ?>','<?php echo encrypt("ELIMINANOTIFICACION"); ?>')">×</button>
            <h5 class="alert-link"><i class="mdi mdi-file"></i> PEDIDO LISTO</h5>

            <p class="mb-0 font-16 alert-link" style="color: #8c1b29;">
              <?php if($reg[$i]["preparado"] == 1){ echo "COCINA";
              } elseif($reg[$i]["preparado"] == 2){ echo "BAR";
              } elseif($reg[$i]["preparado"] == 3){ echo "REPOSTERIA"; } ?></p>

            <p class="mb-0 font-14 alert-link" style="color: #8c1b29;">Nº DE PEDIDO:</span> <span class="mb-0 font-12 alert-link"><?php echo $reg[$i]['numpedido']; ?></p>
            
            <?php if($reg[$i]["tipo"] == 1){ ?>

              <p class="mb-0 text-dark font-12 alert-link"><?php echo $reg[$i]['nomsala']; ?></p>
              <p class="mb-0 text-dark font-12 alert-link"><?php echo $reg[$i]['nommesa']; ?></p>

            <?php } elseif($reg[$i]["tipo"] == 2 || $reg[$i]["tipo"] == 4){ ?>

              <p class="mb-0 font-14 alert-link" style="color: #8c1b29;">CLIENTE:</p> <p class="mb-0 font-12 alert-link"><?php echo $reg[$i]["nomcliente"] == "" ? "************" : $reg[$i]["nomcliente"]; ?></p>

            <?php } elseif($reg[$i]["tipo"] == 3){ ?>

              <p class="mb-0 text-danger font-12 alert-link">CLIENTE:</p> <p class="mb-0 font-12 alert-link"><?php echo $reg[$i]["nomcliente"] == "" ? "************" : $reg[$i]["nomcliente"]; ?></p>

            <?php } ?>
          </div>
        </div> <!-- Panel-body -->
    </div>

    <script type="text/javascript">
    var audio = document.createElement("AUDIO")
    document.body.appendChild(audio);
    audio.src = "assets/notificacion.mp3"
    audio.loop = false;
    var sound = true;
    audio.play();
      document.body.addEventListener("mousemove", function () {
        if(sound == true){
       audio.play();
       
        sound =false;
      }
    })
    </script> 
                           
<?php } ?>
</div>
<?php } ?>
                                 
<!-- Row -->
<?php
} 
############################# CARGAR NOTIFICACIONES ############################
?>

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