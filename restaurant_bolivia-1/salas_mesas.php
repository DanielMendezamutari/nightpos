<?php
require_once("class/class.php");

$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "Impuesto" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']);

$con = new Login();
$con = $con->ConfiguracionPorId();
$simbolo = ($_SESSION["acceso"] == "administradorG" ? "" : "<strong>".$_SESSION["simbolo"]."</strong>");
?>

<!--######################### LISTAR MESAS ########################-->
<?php if (isset($_GET['CargaMesas'])): ?>

<h3 class="card-subtitle m-0 text-dark"><i class='font-20 fa fa-tasks'></i> Monitor de Mesas</h3><hr>

<?php
$sala = new Login();
$sala = $sala->ListarSalas();
?>
    <div class="row-horizon">
        <?php 
        if($sala==""){ echo ""; } else {
        $a=1;
        for ($i = 0; $i < sizeof($sala); $i++) { ?>
        <span class="categories <?php echo $activo = ( $a++ == 1 ? "selectedGat" : ""); ?>" id="<?php echo $sala[$i]['nomsala'];?>"><i class="fa fa-tasks"></i> <?php echo $sala[$i]['nomsala'];?></span>
        <?php } } ?>
    </div><br>

    <div id="productList2">

        <div class="row-vertical-mesas">
        <?php
        $mesa = new Login();
        $mesa = $mesa->ListarMesas(); 

        if($mesa==""){

        echo "<div class='alert alert-danger'>";
        echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        echo "<center><span class='fa fa-info-circle'></span> NO EXISTEN MESAS REGISTRADAS ACTUALMENTE</center>";
        echo "</div>";    

        } else {

        $x=1;
        for ($ii = 0; $ii < sizeof($mesa); $ii++) { ?>
   
        <a style="float: left; margin-right: 4px; <?php echo $activo = ( $mesa[$ii]['codsala'] == 1 ? "display: block;" : "display: none;"); ?>" id="<?php echo $mesa[$ii]['nomsala'];?>">
            
        <?php if ($_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="cajero" || $_SESSION["acceso"]=="mesero") { ?>

        <?php // if ($_SESSION["acceso"]=="administradorS" || $mesa[$ii]['mesero'] == "0" || $_SESSION["codigo"] == $mesa[$ii]['mesero']) { ?>
            
            <div class="users-list-name codMesa" title="<?php echo $mesa[$ii]['nommesa']; ?> <?php echo $var = ($mesa[$ii]['statusmesa'] == '0' || $mesa[$ii]['statusmesa'] == '2' ? '(DISPONIBLE)' : '(OCUPADA)'); ?>" style="cursor:pointer;" onclick="VerificaMesa('<?php echo encrypt($mesa[$ii]['codmesa']); ?>')">
                <div id="<?php echo $mesa[$ii]['codmesa']; ?>">
                <input type="hidden" id="category" name="category" value="<?php echo $mesa[$ii]['nomsala']; ?>">
                <button type="button" id="<?php echo $mesa[$ii]['nommesa']; ?>" style="cursor: pointer;width: 100px;height: 100px;-moz-border-radius: 10%;-webkit-border-radius: 10%;border-radius: 10%;border:5px solid #e5e2e2 !important;border:1px solid rgba(227,223,223,.13);background:<?php echo $var = ($mesa[$ii]['statusmesa'] == '0' || $mesa[$ii]['statusmesa'] == '2' ? '#5cb85c;' : 'red;'); ?>" class="miMesa"><span style="font-size:13px;color: #f9f7f7 !important;font-weight:bold;"> <?php echo getSubString($mesa[$ii]['nommesa'], 22); ?></span> </button>
                </div>
                <center class="text-dark alert-link font-12"><?php echo $mesa[$ii]['puestos']; ?> PERSONAS</center>
                <center class="text-dark alert-link font-12"><?php echo $mesa[$ii]['total_deudas']=="" ? $mesa[$ii]['simbolo']."0.00" : $mesa[$ii]['simbolo'].number_format($mesa[$ii]['total_deudas'], 2, '.', ','); ?></center>
            </div>

        <?php //} ?>

        <?php } elseif ($_SESSION["acceso"]=="cajero_usuario") { ?>     

            <div class="users-list-name codMesa" title="<?php echo $mesa[$ii]['nommesa']; ?> <?php echo $var = ($mesa[$ii]['statusmesa'] == '0' || $mesa[$ii]['statusmesa'] == '2' ? '(DISPONIBLE)' : '(PENDIENTE DE COBRO)'); ?>" style="cursor:pointer;" <?php if ($mesa[$ii]['statusmesa'] == '0') { ?> onclick="MesaDisponible();" <?php } else { ?> onclick="ProcesarMesa('<?php echo encrypt($mesa[$ii]['codmesa']); ?>','<?php echo encrypt($mesa[$ii]['codsucursal']); ?>')" <?php } ?>>
                <div id="<?php echo $mesa[$ii]['codmesa']; ?>">
                <input type="hidden" id="category" name="category" value="<?php echo $mesa[$ii]['nomsala']; ?>"> 
                  <button type="button" id="<?php echo $mesa[$ii]['nommesa']; ?>" style="cursor: pointer;width: 100px;height: 100px;-moz-border-radius: 10%;-webkit-border-radius: 10%;border-radius: 10%;border:5px solid #e5e2e2 !important;border:1px solid rgba(227,223,223,.13);background:<?php echo $var = ($mesa[$ii]['statusmesa'] == '0' ? '#5cb85c;' : '#0D89F1;'); ?>" class="miMesa"><span style="font-size:13px;color: #f9f7f7 !important;font-weight:bold;"> <?php echo getSubString($mesa[$ii]['nommesa'], 22); ?></span> </button>
                </div>
                <center class="text-dark alert-link font-12"><?php echo $mesa[$ii]['puestos']; ?> PERSONAS</center>
                <center class="text-dark alert-link font-12"><?php echo $mesa[$ii]['total_deudas']=="" ? $mesa[$ii]['simbolo']."0.00" : $mesa[$ii]['simbolo'].number_format($mesa[$ii]['total_deudas'], 2, '.', ','); ?></center>
            </div>

        <?php } ?>

        </a>
    
        <?php } } ?>

        </div> 
    </div>

<?php endif; ?>
<!--######################### LISTAR MESAS ########################-->


<!--######################### LISTAR PRODUCTOS ########################-->
<?php if (isset($_GET['CargaProductos'])): ?>

<h3 class="card-subtitle m-0 text-dark"><i class='font-20 fa fa-cubes'></i> Monitor de Productos</h3><hr>

<?php
$categoria = new Login();
$categoria = $categoria->ListarCategorias();
?>
    <div class="row-horizon">
        <span class="categories selectedGat" id=""><i class="fa fa-home"></i></span>
        <?php 
        if($categoria==""){ echo ""; } else {
        $a=1;
        for ($i = 0; $i < sizeof($categoria); $i++) { ?>
        <span class="categories" id="<?php echo $categoria[$i]['nomcategoria'];?>"><i class="fa fa-tasks"></i> <?php echo $categoria[$i]['nomcategoria'];?></span>
        <?php } } ?>
    </div>

    <div class="col-md-12">
        <div id="searchContaner"> 
            <div class="form-group has-feedback2"> 
                <label class="control-label"></label>
                <input type="text" class="form-control" name="busquedaproducto" id="busquedaproducto" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Criterio para tu Búsqueda">
                  <i class="fa fa-search form-control-feedback2"></i> 
            </div> 
        </div>
    </div>
    

    <div id="productList2">
        <?php
        $producto = new Login();
        $producto = $producto->ListarProductosModal();

        if($producto==""){

        echo "<div class='alert alert-danger'>";
        echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        echo "<center><span class='fa fa-info-circle'></span> NO EXISTEN PRODUCTOS REGISTRADOS ACTUALMENTE</center>";
        echo "</div>";  

        } else { ?>

        <div class="row-vertical">
            <div class="row">
        <?php for ($ii = 0; $ii < sizeof($producto); $ii++) { ?>
        
        <!-- column -->
        <div ng-click="afterClick()" ng-repeat="product in ::getFavouriteProducts()" OnClick="DoAction(
        '<?php echo $producto[$ii]['idproducto']; ?>',
        '<?php echo $producto[$ii]['codproducto']; ?>',
        '<?php echo $producto[$ii]['producto']; ?>',
        '<?php echo $producto[$ii]['codcategoria']; ?>',
        '<?php echo $producto[$ii]['nomcategoria']; ?>',
        '<?php echo number_format($producto[$ii]['preciocompra'], 2, '.', ''); ?>',
        '<?php echo number_format($producto[$ii]['precioventa'], 2, '.', ''); ?>',
        '<?php echo number_format($producto[$ii]['descproducto'], 2, '.', ''); ?>',
        '<?php echo $ivaproducto = ( $producto[$ii]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', '') : "(E)"); ?>',
        '<?php echo number_format($producto[$ii]['existencia'], 2, '.', ''); ?>',
        '<?php echo $precioconiva = ( $producto[$ii]['ivaproducto'] == 'SI' ? number_format($producto[$ii]['precioventa'], 2, '.', '') : "0.00"); ?>',
        '<?php echo "1"; ?>',
        '<?php echo ", "; ?>',        
        '<?php echo ", "; ?>',
        '<?php echo $producto[$ii]['preparado']; ?>');">
        <div id="<?php echo $producto[$ii]['codproducto']; ?>">
            <div class="darkblue-panel pn" title="<?php echo $producto[$ii]['producto'].' | ('.$producto[$ii]['nomcategoria'].')';?>">
                <div class="darkblue-header">
                   <div class="text-white font-12"><?php echo getSubString($producto[$ii]['producto'],18);?></div>
                </div>
                <?php
                if (file_exists("./fotos/productos/".$producto[$ii]["codproducto"].".jpg")){
                echo "<img src='fotos/productos/".$producto[$ii]['codproducto'].".jpg?".time()."' class='rounded-circle' style='width:130px;height:100px;'>";
                } else if (file_exists("./fotos/productos/".$producto[$ii]["codproducto"].".jpeg")){
                echo "<img src='fotos/productos/".$producto[$ii]['codproducto'].".jpeg?".time()."' class='rounded-circle' style='width:130px;height:100px;'>";
                } else if (file_exists("./fotos/productos/".$producto[$ii]["codproducto"].".png")){
                echo "<img src='fotos/productos/".$producto[$ii]['codproducto'].".png?".time()."' class='rounded-circle' style='width:130px;height:100px;'>";
                } else {
                echo "<img src='fotos/producto.png' class='rounded-circle' style='width:130px;height:100px;'>";  
                } ?>
                <input type="hidden" id="proname" name="proname" value="<?php echo $producto[$ii]['codproducto'].", ".$producto[$ii]['producto'].", ".$producto[$ii]['codigobarra']; ?>">
                <input type="hidden" id="category" name="category" value="<?php echo $producto[$ii]['nomcategoria']; ?>">

                <div class="mask">
                <h5 style="font-size: 11.5px;" class="text-white pull-left"><i class="fa fa-bars"></i> <?php echo number_format($producto[$ii]['existencia'], 2, '.', ','); ?></h5>
                <abbr title="<?php echo $producto[$ii]['montocambio'] == '' ? "" : $producto[$ii]['simbolo2'].number_format($producto[$ii]['precioventa']/$producto[$ii]['montocambio'], 2, '.', ','); ?>"><h5 style="font-size: 11.5px;" class="text-warning pull-right font-12"><?php echo $simbolo.number_format($producto[$ii]['precioventa'], 2, '.', ',');?></h5></abbr>
                </div>
            </div>
        </div>

        </div>
        <!-- column -->
                
        <?php } // fin for ?>
        </div><!-- fin row -->
       </div><!-- fin row-vertical -->

        <?php } // fin if ?>

        </div> 
    </div>

<?php endif; ?>
<!--######################### LISTAR PRODUCTOS ########################-->


<!--######################### LISTAR COMBOS ########################-->
<?php if (isset($_GET['CargaCombos'])): ?>

<h3 class="card-subtitle m-0 text-dark"><i class='font-20 fa fa-archive'></i> Monitor de Combos</h3><hr>

    <div class="col-md-12">
        <div id="searchContaner"> 
            <div class="form-group has-feedback2"> 
                <label class="control-label"></label>
                <input type="text" class="form-control" name="busquedaproducto" id="busquedaproducto" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Criterio para tu Búsqueda">
                  <i class="fa fa-search form-control-feedback2"></i> 
            </div> 
        </div>
    </div>
    

    <div id="productList2">
        <?php
        $combo = new Login();
        $combo = $combo->ListarCombosModal();

        if($combo==""){

        echo "<div class='alert alert-danger'>";
        echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        echo "<center><span class='fa fa-info-circle'></span> NO EXISTEN COMBOS REGISTRADOS ACTUALMENTE</center>";
        echo "</div>";  

        } else { ?>

        <div class="row-vertical">
            <div class="row">
        <?php for ($ii = 0; $ii < sizeof($combo); $ii++) { ?>
        
        <!-- column -->
        <div ng-click="afterClick()" ng-repeat="product in ::getFavouriteProducts()" OnClick="DoAction(
        '<?php echo $combo[$ii]['idcombo']; ?>',
        '<?php echo $combo[$ii]['codcombo']; ?>',
        '<?php echo $combo[$ii]['nomcombo']; ?>',
        '<?php echo "0"; ?>',
        '<?php echo "0"; ?>',
        '<?php echo number_format($combo[$ii]['preciocompra'], 2, '.', ''); ?>',
        '<?php echo number_format($combo[$ii]['precioventa'], 2, '.', ''); ?>',
        '<?php echo number_format($combo[$ii]['desccombo'], 2, '.', ''); ?>',
        '<?php echo $ivacombo = ( $combo[$ii]['ivacombo'] == 'SI' ? number_format($valor, 2, '.', '') : "(E)"); ?>',
        '<?php echo number_format($combo[$ii]['existencia'], 2, '.', ''); ?>',
        '<?php echo $precioconiva = ( $combo[$ii]['ivacombo'] == 'SI' ? number_format($combo[$ii]['precioventa'], 2, '.', '') : "0.00"); ?>',
        '<?php echo "2"; ?>',
        '<?php echo ", "; ?>',       
        '<?php echo ", "; ?>',
        '<?php echo $combo[$ii]['preparado']; ?>');">
        <div id="<?php echo $combo[$ii]['codcombo']; ?>">
            <div class="darkblue-panel pn" title="<?php echo $combo[$ii]['nomcombo'].'';?>">
                <div class="darkblue-header">
                   <div class="text-white font-12"><?php echo getSubString($combo[$ii]['nomcombo'],18);?></div>
                </div>
                <?php
                if (file_exists("./fotos/combos/".$combo[$ii]["codcombo"].".jpg")){
                echo "<img src='fotos/combos/".$combo[$ii]["codcombo"].".jpg?".time()."' class='rounded-circle' style='width:130px;height:100px;'>";
                } else if (file_exists("./fotos/combos/".$combo[$ii]["codcombo"].".jpeg")){
                echo "<img src='fotos/combos/".$combo[$ii]["codcombo"].".jpeg?".time()."' class='rounded-circle' style='width:130px;height:100px;'>";
                } else if (file_exists("./fotos/combos/".$combo[$ii]["codcombo"].".png")){
                echo "<img src='fotos/combos/".$combo[$ii]["codcombo"].".png?".time()."' class='rounded-circle' style='width:130px;height:100px;'>";
                } else {
                echo "<img src='fotos/producto.png' class='rounded-circle' style='width:130px;height:100px;'>";  
                } ?>
                <input type="hidden" id="proname" name="proname" value="<?php echo $combo[$ii]['codcombo'].", ".$combo[$ii]['nomcombo']; ?>">
                <input type="hidden" id="category" name="category" value="*******">
                <div class="mask">
                <h5 style="font-size: 11.5px;" class="text-white pull-left"><i class="fa fa-bars"></i> <?php echo number_format($combo[$ii]['existencia'], 2, '.', ','); ?></h5>
                <abbr title="<?php echo $combo[$ii]['montocambio'] == '' ? "" : $combo[$ii]['simbolo2'].number_format($combo[$ii]['precioventa']/$combo[$ii]['montocambio'], 2, '.', ','); ?>"><h5 style="font-size: 11.5px;" class="text-warning pull-right font-12"><?php echo $simbolo.number_format($combo[$ii]['precioventa'], 2, '.', ',');?></h5></abbr>
                </div>
            </div>
        </div>

        </div>
        <!-- column -->
                
        <?php } // fin for ?>
        </div><!-- fin row -->
       </div><!-- fin row-vertical -->

        <?php } // fin if ?>

        </div> 
    </div>

<?php endif; ?>
<!--######################### LISTAR COMBOS ########################-->


<!--######################### LISTAR EXTRAS ########################-->
<?php if (isset($_GET['CargaExtras'])): ?>

<h3 class="card-subtitle m-0 text-dark"><i class='font-20 fa fa-folder-open'></i> Monitor de Extras</h3><hr>

<?php
$medida = new Login();
$medida = $medida->ListarMedidas();
?>
    <div class="row-horizon">
        <span class="categories selectedGat" id=""><i class="fa fa-home"></i></span>
        <?php 
        if($medida==""){ echo ""; } else {
        $a=1;
        for ($i = 0; $i < sizeof($medida); $i++) { ?>
        <span class="categories" id="<?php echo $medida[$i]['nommedida'];?>"><i class="fa fa-tasks"></i> <?php echo $medida[$i]['nommedida'];?></span>
        <?php } } ?>
    </div>

    <div class="col-md-12">
        <div id="searchContaner"> 
            <div class="form-group has-feedback2"> 
                <label class="control-label"></label>
                <input type="text" class="form-control" name="busquedaproducto" id="busquedaproducto" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Criterio para tu Búsqueda">
                  <i class="fa fa-search form-control-feedback2"></i> 
            </div> 
        </div>
    </div>
    

    <div id="productList2">
        <?php
        $ingrediente = new Login();
        $ingrediente = $ingrediente->ListarIngredientesModal();

        if($ingrediente==""){

        echo "<div class='alert alert-danger'>";
        echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        echo "<center><span class='fa fa-info-circle'></span> NO EXISTEN EXTRAS REGISTRADOS ACTUALMENTE</center>";
        echo "</div>";  

        } else { ?>

        <div class="row-vertical">
            <div class="row">
        <?php for ($ii = 0; $ii < sizeof($ingrediente); $ii++) { ?>
        
        <!-- column -->
        <div ng-click="afterClick()" ng-repeat="product in ::getFavouriteProducts()" OnClick="DoAction(
        '<?php echo $ingrediente[$ii]['idingrediente']; ?>',
        '<?php echo $ingrediente[$ii]['codingrediente']; ?>',
        '<?php echo $ingrediente[$ii]['nomingrediente']; ?>',
        '<?php echo $ingrediente[$ii]['codmedida']; ?>',
        '<?php echo $ingrediente[$ii]['nommedida']; ?>',
        '<?php echo number_format($ingrediente[$ii]['preciocompra'], 2, '.', ''); ?>',
        '<?php echo number_format($ingrediente[$ii]['precioventa'], 2, '.', ''); ?>',
        '<?php echo number_format($ingrediente[$ii]['descingrediente'], 2, '.', ''); ?>',
        '<?php echo $ivaingrediente = ( $ingrediente[$ii]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', '') : "(E)"); ?>',
        '<?php echo number_format($ingrediente[$ii]['cantingrediente'], 2, '.', ''); ?>',
        '<?php echo $precioconiva = ( $ingrediente[$ii]['ivaingrediente'] == 'SI' ? number_format($ingrediente[$ii]['precioventa'], 2, '.', '') : "0.00"); ?>',
        '<?php echo "3"; ?>',
        '<?php echo ", "; ?>',       
        '<?php echo ", "; ?>',
        '<?php echo $ingrediente[$ii]['preparado']; ?>');">
        <div id="<?php echo $ingrediente[$ii]['codingrediente']; ?>">
            <div class="darkblue-panel pn" title="<?php echo $ingrediente[$ii]['nomingrediente'].' | ('.$ingrediente[$ii]['nommedida'].')';?>">
                <div class="darkblue-header">
                   <div class="text-white font-12"><?php echo getSubString($ingrediente[$ii]['nomingrediente'],18);?></div>
                </div>
                <img src='fotos/producto.png' class='rounded-circle' style='width:130px;height:100px;'>
                <input type="hidden" id="proname" name="proname" value="<?php echo $ingrediente[$ii]['codingrediente'].", ".$ingrediente[$ii]['nomingrediente']; ?>">
                <input type="hidden" id="category" name="category" value="<?php echo $ingrediente[$ii]['nommedida']; ?>">

                <div class="mask">
                <h5 style="font-size: 11.5px;" class="text-white pull-left"><i class="fa fa-bars"></i> <?php echo number_format($ingrediente[$ii]['cantingrediente'], 2, '.', ','); ?></h5>
                <abbr title="<?php echo $ingrediente[$ii]['montocambio'] == '' ? "" : $ingrediente[$ii]['simbolo2'].number_format($ingrediente[$ii]['precioventa']/$ingrediente[$ii]['montocambio'], 2, '.', ','); ?>"><h5 style="font-size: 11.5px;" class="text-warning pull-right font-12"><?php echo $simbolo.number_format($ingrediente[$ii]['precioventa'], 2, '.', ',');?></h5></abbr>
                </div>
            </div>
        </div>

        </div>
        <!-- column -->
                
        <?php } // fin for ?>
        </div><!-- fin row -->
       </div><!-- fin row-vertical -->

        <?php } // fin if ?>

        </div> 
    </div>

<?php endif; ?>
<!--######################### LISTAR EXTRAS ########################-->


<?php 
############################ MUESTRA PRODUCTOS FAVORITOS ###########################
if (isset($_GET['Muestra_Productos_Favoritos'])) { 
?> 
        <table class="table">
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
        </table>
<?php  }
############################ MUESTRA PRODUCTOS FAVORITOS ###########################
?>


<?php 
############################ MUESTRA COMBOS FAVORITOS ###########################
if (isset($_GET['Muestra_Combos_Favoritos'])) { 
?> 
        <table class="table">
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
        </table>
<?php  }
############################ MUESTRA COMBOS FAVORITOS ###########################
?>


<?php 
############################ MUESTRA EXTRAS FAVORITOS ###########################
if (isset($_GET['Muestra_Extras_Favoritos'])) { 
?> 
        <table class="table">
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
        </table>
<?php  }
############################ MUESTRA EXTRAS FAVORITOS ###########################
?>

<script type="text/javascript">
$(document).ready(function() {

    //  search product
    $("#busquedaproducto").keyup(function(){
        // Retrieve the input field text
        var filter = $(this).val();
        // Loop through the list
        $("#productList2 #proname").each(function(){
            // If the list item does not contain the text phrase fade it out
            if ($(this).val().search(new RegExp(filter, "i")) < 0) {
              $(this).parent().parent().parent().hide();
            // Show the list item if the phrase matches
            } else {
              $(this).parent().parent().parent().show();
            }
        });
    });


    $(".categorias").on("click", function () {
       // Retrieve the input field text
       var filter = $(this).attr('id');
       $(this).parent().children().removeClass('selectedGat');
       $(this).addClass('selectedGat');
    });


    $(".categories").on("click", function () {
       // Retrieve the input field text
       var filter = $(this).attr('id');
       $(this).parent().children().removeClass('selectedGat');

       $(this).addClass('selectedGat');
       // Loop through the list
       $("#productList2 #category").each(function(){
          // If the list item does not contain the text phrase fade it out
          if ($(this).val().search(new RegExp(filter, "i")) < 0) {
             $(this).parent().parent().parent().hide();
             // Show the list item if the phrase matches
          } else {
             $(this).parent().parent().parent().show();
          }
       });
    });

});
</script>