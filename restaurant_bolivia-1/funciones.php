<?php
require_once("class/class.php");
?>
<script src="assets/script/jscalendario.js"></script>
<script src="assets/script/autocompleto.js"></script> 

<?php
$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "Impuesto" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']);

$sucursal = new Login();
$sucursal = $sucursal->SucursalesPorIdSession();
$porcentajepropina = (empty($sucursal) ? "0.00" : $sucursal[0]['porcentajepropina']);
$total_porcentaje = (empty($sucursal) ? "0.00" : number_format($sucursal[0]['porcentajepropina']/100, 2, '.', ''));

$con = new Login();
$con = $con->ConfiguracionPorId();

$new = new Login();
?>


<?php 
######################## BUSCA HORARIOS X SUCURSAL ########################
if (isset($_GET['BuscaHorariosxSucursal']) && isset($_GET['codsucursal'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $horario = $new->ListarHorarios();

  if($codsucursal == "") { ?>

  <option value=""> -- SIN RESULTADOS -- </option>
  <?php } else { ?>
  <option value=""> -- SELECCIONE -- </option>
  <?php
  for($i=0;$i<sizeof($horario);$i++){
  ?>
  <option value="<?php echo encrypt($horario[$i]['codhorario']); ?>"><?php echo $horario[$i]['hora_desde']." : ".$horario[$i]['hora_hasta']; ?></option>
    <?php 
    } 
  }
}
######################## BUSCA HORARIOS X SUCURSAL #############################
?>


<?php 
######################## BUSCA CATEGORIAS X SUCURSAL ########################
if (isset($_GET['BuscaCategoriasxSucursal']) && isset($_GET['codsucursal'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $categoria = $new->ListarCategorias();

  if($codsucursal == "") { ?>

  <option value=""> -- SIN RESULTADOS -- </option>
  <?php } else { ?>
  <option value=""> -- SELECCIONE -- </option>
  <?php
  for($i=0;$i<sizeof($categoria);$i++){
  ?>
  <option value="<?php echo encrypt($categoria[$i]['codcategoria']); ?>"><?php echo $categoria[$i]['nomcategoria']; ?></option>
    <?php 
    } 
  }
}
######################## BUSCA CATEGORIAS X SUCURSAL #############################
?>


<?php 
######################## BUSCA MEDIDAS X SUCURSAL ########################
if (isset($_GET['BuscaMedidasxSucursal']) && isset($_GET['codsucursal'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $medida = $new->ListarMedidas();

  if($codsucursal == "") { ?>

  <option value=""> -- SIN RESULTADOS -- </option>
  <?php } else { ?>
  <option value=""> -- SELECCIONE -- </option>
  <?php
  for($i=0;$i<sizeof($medida);$i++){
  ?>
  <option value="<?php echo encrypt($medida[$i]['codmedida']); ?>"><?php echo $medida[$i]['nommedida']; ?></option>
    <?php 
    } 
  }
}
######################## BUSCA MEDIDAS X SUCURSAL #############################
?>

<?php 
######################## BUSCA PROVEEDORES X SUCURSAL ########################
if (isset($_GET['BuscaProveedoresxSucursal']) && isset($_GET['codsucursal'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $proveedor = $new->ListarProveedores();

  if($codsucursal == "") { ?>

  <option value=""> -- SIN RESULTADOS -- </option>
  <?php } else { ?>
  <option value=""> -- SELECCIONE -- </option>
  <?php
  for($i=0;$i<sizeof($proveedor);$i++){
  ?>
  <option value="<?php echo encrypt($proveedor[$i]['codproveedor']); ?>"><?php echo $proveedor[$i]['nomproveedor']; ?></option>
    <?php 
    } 
  }
}
######################## BUSCA PROVEEDORES X SUCURSAL #############################
?>


<?php 
######################## BUSCA PROVEEDORES ########################
if (isset($_GET['BuscaProveedores'])) {
  
$proveedor = $new->ListarProveedores();
?>
  <option value=""> -- SELECCIONE -- </option>
  <?php
  for($i=0;$i<sizeof($proveedor);$i++){
  ?>
  <option value="<?php echo $proveedor[$i]['codproveedor']; ?>" ><?php echo $proveedor[$i]['nomproveedor']; ?></option>
<?php 
  } 
}
######################## BUSCA PROVEEDORES ########################
?>


<?php 
######################## BUSCA DEPARTAMENTOS POR PROVINCIAS ########################
if (isset($_GET['BuscaDepartamentos']) && isset($_GET['id_provincia'])) {
  
  $dep = $new->ListarDepartamentoXProvincias();

  $id_provincia = limpiar($_GET['id_provincia']);

  if($id_provincia == "" || $id_provincia == 0) { ?>

  <option value="">-- SIN RESULTADOS --</option>
  <?php } else { ?>

  <option value=""> -- SELECCIONE -- </option>
  <?php
  for($i=0;$i<sizeof($dep);$i++){
  ?>
  <option value="<?php echo $dep[$i]['id_departamento']; ?>" ><?php echo $dep[$i]['departamento']; ?></option>
  <?php 
    }
  }
}
######################## BUSCA DEPARTAMENTOS POR PROVINCIAS ########################
?>

<?php 
######################## SELECCIONE DEPARTAMENTOS POR PROVINCIAS ########################
if (isset($_GET['SeleccionaDepartamento']) && isset($_GET['id_provincia']) && isset($_GET['id_departamento'])) {
  
  $dep = $new->SeleccionaDepartamento();
?>
  </div>
  </div>
  <option value="">SELECCIONE</option>
  <?php for($i=0;$i<sizeof($dep);$i++){ ?>
  <option value="<?php echo $dep[$i]['id_departamento']; ?>"<?php if (!(strcmp($_GET['id_departamento'], htmlentities($dep[$i]['id_departamento'])))) {echo "selected=\"selected\"";} ?>><?php echo $dep[$i]['departamento']; ?></option>
<?php
  } 
}
######################## SELECCIONE DEPARTAMENTOS POR PRIVINCIAS ########################
?>


<?php 
######################## BUSCA SALAS POR SUCURSALES ########################
if (isset($_GET['BuscaSalasxSucursal']) && isset($_GET['codsucursal'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);

  $sala = new Login();
  $sala = $sala->BuscarSalasxSucursales();
  ?>
  <option value=""> -- SELECCIONE -- </option>
  <?php
  for($i=0;$i<sizeof($sala);$i++){
  ?>
  <option value="<?php echo encrypt($sala[$i]['codsala']); ?>" ><?php echo $sala[$i]['nomsala']; ?></option>
<?php  
  }
}
######################## BUSCA SALAS POR SUCURSALES ########################
?>

<?php 
######################## SELECCIONE SALAS POR SUCURSALES ########################
if (isset($_GET['SeleccionaSalas']) && isset($_GET['codsucursal']) && isset($_GET['codsala'])) {
  
  $sala = $new->BuscarSalasxSucursales();
  ?>
  <option value="">SELECCIONE</option>
  <?php for($i=0;$i<sizeof($sala);$i++){ ?>
  <option value="<?php echo encrypt($sala[$i]['codsala']); ?>"<?php if (!(strcmp($_GET['codsala'], htmlentities(encrypt($sala[$i]['codsala']))))) { echo "selected=\"selected\"";} ?>><?php echo $sala[$i]['nomsala']; ?></option>
<?php
  } 
}
######################## SELECCIONE SALAS POR SUCURSALES ########################
?>

<?php 
######################## BUSCA MESAS POR SALAS ########################
if (isset($_GET['BuscaMesasxSalas']) && isset($_GET['codsala'])) {
  
  $mesa = new Login();
  $mesa = $mesa->BuscarMesas();

  $codsala = limpiar($_GET['codsala']);

  if($codsala=="") { ?>

  <option value="">-- SIN RESULTADOS --</option>
  <?php } else { ?>
  <option value=""> -- SELECCIONE -- </option>
  <?php
  for($i=0;$i<sizeof($mesa);$i++){
  ?>
  <option value="<?php echo encrypt($mesa[$i]['codmesa']); ?>" ><?php echo $mesa[$i]['nommesa']; ?></option>
  <?php 
    } 
  }
}
######################## BUSCA MESAS POR SALAS ########################
?>

<?php
######################## MOSTRAR USUARIO EN VENTANA MODAL ############################
if (isset($_GET['BuscaUsuarioModal']) && isset($_GET['codigo'])) { 
$reg = $new->UsuariosPorId();
?>
  <table class="table-responsive" border="0" class="text-center">
  <tr>
    <td><strong>Nº de Documento:</strong> <?php echo $reg[0]['dni']; ?></td>
  </tr>
  <tr>
    <td><strong>Nombres y Apellidos:</strong> <?php echo $reg[0]['nombres']; ?></td>
  </tr>
  <tr>
    <td><strong>Sexo:</strong> <?php echo $reg[0]['sexo']; ?></td>
  </tr>
  <tr>
    <td><strong>Dirección Domiciliaria: </strong> <?php echo $reg[0]['direccion']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº de Teléfono: </strong> <?php echo $reg[0]['telefono']; ?></td>
  </tr>
  <tr>
    <td><strong>Correo Electrónico: </strong> <?php echo $reg[0]['email']; ?></td>
  </tr>
  <tr>
    <td><strong>Usuario de Acceso: </strong> <?php echo $reg[0]['usuario']; ?></td>
  </tr>
  <tr>
    <td><strong>Nivel de Acceso: </strong> <?php echo $reg[0]['nivel']; ?></td>
  </tr>
  <tr>
    <td><strong>Comisión por Ventas: </strong> <?php echo number_format($reg[0]['comision'], 2, '.', ''); ?>%</td>
  </tr>
  <tr>
  <td><strong>Estado de Acceso: </strong> <?php echo $status = ( $reg[0]['status'] == 1 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ACTIVO</span>" : "<span class='badge badge-danger'><i class='fa fa-times'></i> INACTIVO</span>"); ?></td>
  </tr>
  <?php if($_SESSION['acceso'] == "administradorG") { ?>
  <tr>
    <td><strong>Sucursal Asignada: </strong> <?php echo $reg[0]['codsucursal'] == "0" ? "**********" : $reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal']; ?></td>
  </tr>
  <?php } ?>
</table>  

  <?php
   } 
######################## MOSTRAR USUARIO EN VENTANA MODAL ############################
?>


<?php 
########################## BUSCA USUARIOS POR SUCURSALES #############################
if (isset($_GET['BuscaUsuariosxSucursal']) && isset($_GET['codsucursal'])) {
  
$usuario = $new->BuscarUsuariosxSucursal();
  ?>
<option value=""> -- SELECCIONE -- </option>
  <?php
   for($i=0;$i<sizeof($usuario);$i++){
    ?>
<option value="<?php echo $usuario[$i]['codigo'] ?>"><?php echo $usuario[$i]['dni'].": ".$usuario[$i]['nombres']; ?></option>
    <?php 
   } 
}
############################# BUSCA USUARIOS POR SUCURSALES ##########################
?>


<?php 
######################## SELECCIONE USUARIOS POR SUCURSAL ########################
if (isset($_GET['MuestraUsuario']) && isset($_GET['codigo']) && isset($_GET['codsucursal'])) {
  
$usuario = $new->BuscarUsuariosxSucursal();
?>
<option value=""> -- SELECCIONE -- </option>
  <?php
   for($i=0;$i<sizeof($usuario);$i++){
    ?>
<option value="<?php echo $usuario[$i]['codigo'] ?>"<?php if (!(strcmp($_GET['codigo'], htmlentities($usuario[$i]['codigo'])))) { echo "selected=\"selected\"";} ?>><?php echo $usuario[$i]['dni'].": ".$usuario[$i]['nombres']; ?></option>
<?php
   } 
}
######################## SELECCIONE USUARIOS POR SUCURSAL #######################
?>




<?php
######################### MOSTRAR SUCURSAL EN VENTANA MODAL ##########################
if (isset($_GET['BuscaSucursalModal']) && isset($_GET['codsucursal'])) { 

$reg = $new->SucursalesPorId();
?>
  <table class="table-responsive" border="0">
  <tr>
    <td><strong>Nº de Sucursal: </strong> <?php echo $reg[0]['nrosucursal']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº de <?php echo $reg[0]['documsucursal'] == '0' ? "Documento" : $reg[0]['documento'] ?>: </strong> <?php echo $reg[0]['cuitsucursal']; ?></td>
  </tr>
  <tr>
    <td><strong>Razòn Social: </strong> <?php echo $reg[0]['nomsucursal']; ?></td>
  </tr>
  <tr>
    <td><strong>Código de Giro: </strong> <?php echo $reg[0]['codgiro']; ?></td>
  </tr>
  <tr>
    <td><strong>Giro de Sucursal: </strong> <?php echo $reg[0]['girosucursal']; ?></td>
  </tr>
  <tr>
    <td><strong>Provincia: </strong> <?php echo $reg[0]['id_provincia'] == '0' ? "******" : $reg[0]['provincia']; ?></td>
  </tr>
  <tr>
    <td><strong>Departamento: </strong> <?php echo $reg[0]['id_departamento'] == '0' ? "******" : $reg[0]['departamento']; ?></td>
  </tr>
  <tr>
    <td><strong>Dirección de Sucursal: </strong> <?php echo $reg[0]['direcsucursal']; ?></td>
  </tr>
  <tr>
    <td><strong>Correo Electrónico: </strong> <?php echo $reg[0]['correosucursal']; ?></td>
  </tr> 
  <tr>
    <td><strong>Nº de Teléfono: </strong> <?php echo $reg[0]['tlfsucursal']; ?></td>
  </tr> 
  <tr>
    <td><strong>Nº de Actividad: </strong> <?php echo $reg[0]['nroactividadsucursal']; ?></td>
  </tr> 
  <tr>
    <td><strong>Nº de Inicio de Ticket: </strong> <?php echo $reg[0]['inicioticket']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº de Inicio de Boleta: </strong> <?php echo $reg[0]['inicioboleta']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº de Inicio de Factura: </strong> <?php echo $reg[0]['iniciofactura']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº de Inicio Nota de Crédito: </strong> <?php echo $reg[0]['inicionotacredito']; ?></td>
  </tr>
  <tr>
    <td><strong>Fecha de Autorización: </strong> <?php echo $reg[0]['fechaautorsucursal'] == '0000-00-00' ? "******" : date("d-m-Y",strtotime($reg[0]['fechaautorsucursal'])); ?></td>
  </tr> 
  <tr>
    <td><strong>Lleva Contabilidad: </strong> <?php echo $reg[0]['llevacontabilidad']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº <?php echo $reg[0]['documencargado'] == '0' ? "Documento" : $reg[0]['documento2'] ?> de Encargado:</strong> <?php echo $reg[0]['dniencargado']; ?></td>
  </tr>
  <tr>
    <td><strong>Nombre de Encargado:</strong> <?php echo $reg[0]['nomencargado']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº de Telèfono:</strong> <?php echo $reg[0]['tlfencargado'] == '' ? "******" : $reg[0]['tlfencargado']; ?></td>
  </tr>
  <tr>
    <td><strong>Descuento Global en Ventas: </strong> <?php echo $reg[0]['descsucursal']; ?>%</td>
  </tr>   
  <tr>
    <td><strong>% Propina Sugerida: </strong> <?php echo $reg[0]['porcentajepropina']; ?>%</td>
  </tr>
  <tr>
    <td><strong>Moneda Nacional: </strong> <?php echo $reg[0]['codmoneda'] == '0' ? "******" : $reg[0]['moneda']; ?></td>
  </tr> 
  <tr>
    <td><strong>Moneda Tipo de Cambio:</strong> <?php echo $reg[0]['codmoneda2'] == '0' ? "******" : $reg[0]['moneda2']; ?></td>
  </tr>
  <td><strong>Estado: </strong> <?php echo $status = ($reg[0]['estado'] == 1 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ACTIVA</span>" : "<span class='badge badge-danger'><i class='fa fa-times'></i> INACTIVA</span>"); ?></td>
  </tr>  
</table>
<?php 
} 
######################### MOSTRAR SUCURSAL EN VENTANA MODAL #########################
?>

<?php 
######################## SELECCIONE SUCURSALES DIFERENTES ########################
if (isset($_GET['MuestraSucursalesDiferentes']) && isset($_GET['codsucursal'])) {
  
$sucursal = $new->BuscarSucursalesDiferentes();
?>
<option value=""> -- SELECCIONE -- </option>
  <?php
   for($i=0;$i<sizeof($sucursal);$i++){
    ?>
<option style="color:#000;font-weight:bold;" value="<?php echo encrypt($sucursal[$i]['codsucursal']); ?>"><?php echo $sucursal[$i]['cuitsucursal'].": ".$sucursal[$i]['nomsucursal']; ?></option>
<?php
   } 
}
######################## SELECCIONE SUCURSALES DIFERENTES #######################
?>





<?php 
######################## MUESTRA DIV CLIENTE ########################
if (isset($_GET['BuscaDivCliente'])) {
  
  ?>
<div class="row">
      <div class="col-md-12">
<font color="red"><label> Para poder realizar la Carga Masiva de Clientes, el archivo Excel, debe estar estructurado de 12 columnas, la cuales tendrán las siguientes especificaciones:</label></font><br>

  1. Tipo de Cliente (Opciones: NATURAL/JURIDICO).<br>
  2. Tipo de Documento. (Debera de Ingresar el Codigo de Documento a la que corresponde)<br>
  3. Nº de Documento.<br>
  4. Nombre de Cliente.<br>
  5. Razón Social (Ingresar en caso de ser Cliente Juridico de lo contrario dejarlo vacio).<br>
  6. Giro de Cliente (Ingresar en caso de ser Cliente Juridico de lo contrario dejarlo vacio).<br>
  7. Nº de Teléfono. (Formato: (9999) 9999999).<br>
  8. Provincia. (Debera de Ingresar el Codigo de Provincia a la que corresponde)<br>
  9. Departamento. (Debera de Ingresar el Codigo de Departamento a la que corresponde)<br>
  10. Dirección Domiciliaria.<br>
  11. Correo Electronico.<br>
  12. Monto de Crédito en Ventas.<br><br>

  <font color="red"><label> NOTA:</label></font><br>
  a) Se debe de guardar como archivo .CSV  (delimitado por comas)(*.csv).<br>
  b) Todos los datos deberán escribirse en mayúscula para mejor orden y visibilidad en los reportes.<br>
  c) Deben de tener en cuenta que la carga masiva de Clientes, deben de ser cargados como se explica, para evitar problemas de datos del cliente dentro del Sistema.<br><br>
   </div>
</div>                               
<?php 
  }
######################## MUESTRA DIV CLIENTE ########################
?>

<?php
######################## MOSTRAR CLIENTE EN VENTANA MODAL ########################
if (isset($_GET['BuscaClienteModal']) && isset($_GET['codcliente'])) { 

$reg = $new->ClientesPorId();
?>
 <table class="table-responsive" border="0" class="text-center">
  <tr>
    <td><strong>Código:</strong> <?php echo $reg[0]['codcliente']; ?></td>
  </tr>
  <tr>
    <td><strong>Tipo de Cliente: </strong> <?php echo $reg[0]['tipocliente']; ?></td>
  </tr> 
  <tr>
    <td><strong>Nº de <?php echo $reg[0]['documcliente'] == '0' ? "Documento" : $reg[0]['documento'] ?>:</strong> <?php echo $reg[0]['dnicliente']; ?></td>
  </tr>
  <tr>
    <td><strong>Nombre/Razón Social:</strong> <?php echo $reg[0]['nomcliente']; ?></td>
  </tr>
  <tr>
    <td><strong>Giro de Cliente:</strong> <?php echo $reg[0]['tipocliente'] == 'NATURAL' ? "******" : $reg[0]['girocliente']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº de Teléfono: </strong> <?php echo $reg[0]['tlfcliente'] == '' ? "******" : $reg[0]['tlfcliente'] ?></td>
  </tr>
  <tr>
    <td><strong>Provincia: </strong> <?php echo $reg[0]['provincia'] == '' ? "******" : $reg[0]['provincia'] ?></td>
  </tr>
  <tr>
    <td><strong>Departamento: </strong> <?php echo $reg[0]['departamento'] == '' ? "******" : $reg[0]['departamento'] ?></td>
  </tr>
  <tr>
    <td><strong>Dirección Domiciliaria: </strong> <?php echo $reg[0]['direccliente']; ?></td>
  </tr>
  <tr>
    <td><strong>Correo Electrónico: </strong> <?php echo $reg[0]['emailcliente'] == '' ? "******" : $reg[0]['emailcliente'] ?></td>
  </tr> 
  <tr>
    <td><strong>Limite de Crédito: </strong> <?php echo number_format($reg[0]['limitecredito'], 2, '.', ','); ?></td>
  </tr> 
  <tr>
    <td><strong>Cantidad de Compras: </strong> <?php echo number_format($reg[0]['cantidad'], 2, '.', ','); ?></td>
  </tr>  
  <tr>
    <td><strong>Total en Compras: </strong> <?php echo number_format($reg[0]['totalcompras'], 2, '.', ','); ?></td>
  </tr>  
  <tr>
    <td><strong>Fecha de Ingreso: </strong> <?php echo date("d-m-Y",strtotime($reg[0]['fechaingreso'])); ?></td>
  </tr>
</table>
<?php 
} 
######################## MOSTRAR CLIENTE EN VENTANA MODAL ########################
?>












<?php 
######################## MUESTRA DIV PROVEEDOR ########################
if (isset($_GET['BuscaDivProveedor'])) {
  
  ?>
<div class="row">
      <div class="col-md-12">
<font color="red"><label> Para poder realizar la Carga Masiva de Proveedores, el archivo Excel, debe estar estructurado de 10 columnas, la cuales tendrán las siguientes especificaciones:</label></font><br>

  1. Tipo de Documento. (Debera de Ingresar el Codigo de Documento a la que corresponde)<br>
  2. Nº de Documento.<br>
  3. Nombre de Proveedor (Ingresar Nombre de Proveedor).<br>
  4. Nº de Teléfono. (Formato: (9999) 9999999).<br>
  5. Provincia. (Debera de Ingresar el Codigo de Provincia a la que corresponde)<br>
  6. Departamento. (Debera de Ingresar el Codigo de Departamento a la que corresponde)<br>
  7. Dirección de Proveedor.<br>
  8. Correo Electronico.<br>
  9. Nombre de Vendedor.<br>
  10. Nº de Teléfono de Vendedor. (Formato: (9999) 9999999).<br><br>

  <font color="red"><label> NOTA:</label></font><br>
  a) Se debe de guardar como archivo .CSV  (delimitado por comas)(*.csv).<br>
  b) Todos los datos deberán escribirse en mayúscula para mejor orden y visibilidad en los reportes.<br>
  c) Deben de tener en cuenta que la carga masiva de Proveedores, deben de ser cargados como se explica, para evitar problemas de datos del proveedor dentro del Sistema.<br><br>
   </div>
</div>
<?php 
  }
######################## MUESTRA DIV PROVEEDOR ########################
?>

<?php
######################## MOSTRAR PROVEEDOR EN VENTANA MODAL ########################
if (isset($_GET['BuscaProveedorModal']) && isset($_GET['codproveedor'])) { 

$reg = $new->ProveedoresPorId();
?>
  
  <table class="table-responsive" border="0" class="text-center">
  <tr>
    <td><strong>Código:</strong> <?php echo $reg[0]['codproveedor']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº de <?php echo $reg[0]['documproveedor'] == '0' ? "Documento" : $reg[0]['documento']; ?>:</strong> <?php echo $reg[0]['cuitproveedor']; ?>:</td>
  </tr>
  <tr>
    <td><strong>Nombres de Proveedor:</strong> <?php echo $reg[0]['nomproveedor']; ?></td>
  </tr>
  <tr>
    <td><strong>Nº de Teléfono: </strong> <?php echo $reg[0]['tlfproveedor']; ?></td>
  </tr>
  <tr>
    <td><strong>Provincia: </strong> <?php echo $reg[0]['provincia'] == '' ? "******" : $reg[0]['provincia']; ?></td>
  </tr>
  <tr>
    <td><strong>Departamento: </strong> <?php echo $reg[0]['departamento'] == '' ? "******" : $reg[0]['departamento']; ?></td>
  </tr>
  <tr>
    <td><strong>Dirección de Proveedor: </strong> <?php echo $reg[0]['direcproveedor']; ?></td>
  </tr>
  <tr>
    <td><strong>Correo Electrónico: </strong> <?php echo $reg[0]['emailproveedor']; ?></td>
  </tr> 
  <tr>
    <td><strong>Vendedor: </strong> <?php echo $reg[0]['vendedor']; ?></td>
  </tr> 
  <tr>
    <td><strong>Nº de Teléfono: </strong> <?php echo $reg[0]['tlfvendedor']; ?></td>
  </tr>
  <tr>
    <td><strong>Fecha de Ingreso: </strong> <?php echo date("d-m-Y",strtotime($reg[0]['fechaingreso'])); ?></td>
  </tr>
</table>
<?php 
} 
######################## MOSTRAR PROVEEDOR EN VENTANA MODAL ########################
?>





























<?php 
######################## MUESTRA DIV INGREDIENTE ########################
if (isset($_GET['BuscaDivIngrediente'])) {
  
  ?>
<div class="row">
      <div class="col-md-12">
<font color="red"><label> Para poder realizar la Carga Masiva de Ingredientes, el archivo Excel, debe estar estructurado de 16 columnas, la cuales tendrán las siguientes especificaciones:</label></font><br><br>

  1. Código de Ingrediente (Ejem. 0001).<br>
  2. Nombre de Ingrediente.<br>
  3. Código de Medida. (Deberá ingresar el Nº de Unidad de Medida a la que corresponde).<br>
  4. Precio Compra. (Numeros con 2 decimales).<br>
  5. Precio Venta. (Numeros con 2 decimales).<br>
  6. Cantidad. (Debe de ser con 2 decimales).<br>
  7. Stock Minimo. (Debe de ser con 2 decimales).<br>
  8. Stock Máximo. (Debe de ser con 2 decimales).<br>
  9. <?php echo $impuesto; ?> de Ingrediente. (Ejem. SI o NO).<br>
  10. Descuento de Producto. (Numeros con 2 decimales).<br>
  11. Lote de Producto (En caso de no tener colocar Cero).<br>
  12. Fecha de Expiración. (Formato: 0000-00-00).<br>
  13. Proveedor. (Debe de verificar a que codigo pertenece el Proveedor existente).<br>
  14. Preparado. (1 = Cocina, 2 = Bar, 3 = Reposteria).<br>
  15. Favorito. (Se debe de colocar 0 o 1. Ejem. SI = 1, NO = 0)<br>
  16. Control de Stock. (Se debe de colocar 0 o 1. Ejem. SI = 1, NO = 0).<br><br>

  <font color="red"><label> NOTA:</label></font><br>
  a) Se debe de guardar como archivo .CSV  (delimitado por comas)(*.csv).<br>
  b) Descargar Plantilla <a class="text-info" href="fotos/ingredientes.csv">AQUI</a>.<br>
  c) Todos los datos deberán escribirse en mayúscula para mejor orden y visibilidad en los reportes.<br>
  d) Deben de tener en cuenta que la carga masiva de Ingredientes, deben de ser cargados como se explica, para evitar problemas de datos del ingrediente dentro del Sistema.<br><br>
    </div>
</div>                                 
<?php 
}
######################## MUESTRA DIV INGREDIENTE ########################
?>

<?php
######################## MOSTRAR INGREDIENTES EN VENTANA MODAL ########################
if (isset($_GET['BuscaIngredienteModal']) && isset($_GET['codingrediente']) && isset($_GET['codsucursal'])) { 

$reg = $new->IngredientesPorId(); 
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>"); 
?>
  
  <table class="table-responsive" border="0" class="text-center">
  <tr>
    <td><strong>Código:</strong> <?php echo $reg[0]['codingrediente']; ?></td>
  </tr>
  <tr>
    <td><strong>Ingrediente:</strong> <?php echo $reg[0]['nomingrediente']; ?></td>
  </tr> 
  <tr>
  <td><strong>Proveedor: </strong><?php echo $reg[0]['codproveedor'] == '0' ? "******" : $reg[0]['cuitproveedor'].": ".$reg[0]['nomproveedor']; ?></td>
  </tr> 
  <tr>
    <td><strong>Unidad Medida:</strong> <?php echo $reg[0]['nommedida']; ?></td>
  </tr>
  <tr>
    <td><strong>Precio de Compra: </strong> <?php echo $preciocompra = ($_SESSION['acceso'] == "cajero" || $_SESSION["acceso"]=="cocinero" ? "**********" : $simbolo.number_format($reg[0]['preciocompra'], 2, '.', ',')); ?></td>
  </tr> 
  <tr>
    <td><strong>Precio de Venta: </strong> <?php echo $simbolo.number_format($reg[0]['precioventa'], 2, '.', ','); ?></td>
  </tr>
<?php if($reg[0]['montocambio']!=""){ ?>
  <tr>
    <td><strong><?php echo "Precio ".$reg[0]['siglas']; ?>: </strong> 
      <?php echo "<label>".$reg[0]['simbolo2']."</label> ".number_format($reg[0]['precioventa']/$reg[0]['montocambio'], 2, '.', ','); ?></td>
  </tr> 
<?php } ?>
  <tr>
    <td><strong>Existencia: </strong> <?php echo $reg[0]['cantingrediente']; ?></td>
  </tr> 
  <tr>
    <td><strong>Stock Minimo: </strong> <?php echo $reg[0]['stockminimo'] == '0' ? "******" : $reg[0]['stockminimo']; ?></td>
  </tr> 
  <tr>
    <td><strong>Stock Máximo: </strong> <?php echo $reg[0]['stockmaximo'] == '0' ? "******" : $reg[0]['stockmaximo']; ?></td>
  </tr> 
  <tr>
    <td><strong><?php echo $impuesto; ?>: </strong> <?php echo $reg[0]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
  </tr> 
  <tr>
    <td><strong>Descuento: </strong> <?php echo number_format($reg[0]['descingrediente'], 2, '.', ',')."%"; ?></td>
  </tr>  
  <tr>
    <td><strong>Nº de Lote: </strong> <?php echo $reg[0]['lote'] == '0' ? "******" : $reg[0]['lote']; ?></td>
  </tr>
  <tr>
    <td><strong>Fecha de Expiración: </strong> <?php echo $reg[0]['fechaexpiracion'] == '0000-00-00' ? "******" : date("d-m-Y",strtotime($reg[0]['fechaexpiracion'])); ?></td>
  </tr>
  <tr>
    <td><strong>Estado: </strong> <?php echo $status = ( $reg[0]['cantingrediente'] != 0.00 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ACTIVO</span>" : "<span class='badge badge-danger'><i class='fa fa-times'></i> INACTIVO</span>"); ?></td>
  </tr>
    <tr>
    <td><strong>Control de Stock: </strong> <?php echo $control = ( $reg[0]['controlstocki'] == 1 ? "<span class='badge badge-success'><i class='fa fa-check'></i> SI</span>" : "<span class='badge badge-warning'><i class='fa fa-times'></i> NO</span>"); ?></td>  
    </tr>
  <?php if($_SESSION['acceso'] == "administradorG") { ?>
  <tr>
    <td><strong>Sucursal Asignada: </strong> <?php echo $reg[0]['codsucursal'] == "0" ? "**********" : $reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal']; ?></td>
  </tr>
  <?php } ?>
</table>
<?php 
} 
######################## MOSTRAR INGREDIENTES EN VENTANA MODAL ########################
?>

<?php 
######################## BUSQUEDA DE PRODUCTOS POR MONEDA ########################
if (isset($_GET['BuscaIngredientesxMoneda']) && isset($_GET['codsucursal']) && isset($_GET['codmoneda'])) { 

  $codsucursal = limpiar($_GET['codsucursal']);
  $codmoneda = limpiar($_GET['codmoneda']);

  if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codmoneda=="") { 

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE TIPO DE MONEDA PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else {

$cambio = new Login();
$cambio = $cambio->BuscarTiposCambios();
  
$reg = $new->ListarIngredientes();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Ingredientes según Moneda</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codmoneda=<?php echo $codmoneda; ?>&tipo=<?php echo encrypt("INGREDIENTESXMONEDA"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codmoneda=<?php echo $codmoneda; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("INGREDIENTESXMONEDA"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codmoneda=<?php echo $codmoneda; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("INGREDIENTESXMONEDA"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

              </div>
            </div>
          </div>

          <div id="div4"><table id="datatable-responsive" class="table2 table-hover table-nomargin table-bordered dataTable table-striped" cellspacing="0" width="100%">
                               <thead>
                               <tr role="row">
                                  <th>N°</th>
                                  <th>Código</th>
                                  <th>Nombre de Ingrediente</th>
                                  <th>Unidad Medida</th>                                 
                                  <th><?php echo $impuesto; ?></th>
                                  <th>Desc %</th>
                                  <th>Existencia</th>
                                  <th>Precio Venta</th>
                                  <th><?php echo $cambio[0]['siglas']; ?></th> 
                               </tr>
                               </thead>
                               <tbody class="BusquedaRapida">

<?php 
if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON INGREDIENTES ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
$TotalPrecioVenta=0;
$TotalPrecioVentaDescuento=0;
$TotalMoneda=0;
$TotalArticulos=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo  = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
$simbolo2 = "<strong>".$cambio[0]['simbolo']."</strong>";

$Descuento       = $reg[$i]['descingrediente']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal     = $reg[$i]['precioventa']-$PrecioDescuento;

$TotalArticulos += $reg[$i]['cantingrediente'];
$TotalPrecioVenta     += $reg[$i]['precioventa'];
$TotalPrecioVentaDescuento  += $PrecioFinal;
$TotalMoneda    += ($cambio == 0 ? "0" : $reg[$i]['precioventa']/$cambio[0]['montocambio']);
?>
    <tr role="row" class="odd">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['codingrediente']; ?></td>
      <td><?php echo $reg[$i]['nomingrediente']; ?></td>
      <td><?php echo $reg[$i]['nommedida']; ?></td>
      <td><?php echo $reg[$i]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
      <td><?php echo number_format($reg[$i]['descingrediente'], 2, '.', ','); ?></td>
      <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo2.number_format($reg[$i]['precioventa']/$cambio[0]['montocambio'], 2, '.', ','); ?></td>
    </tr>
    <?php } ?>
    <tr>
      <td colspan="6"></td>
      <td><label><?php echo number_format($TotalArticulos, 2, '.', ','); ?></label></td>
      <td><label><?php echo $simbolo.number_format($TotalPrecioVenta, 2, '.', ','); ?></label></td>
      <td><label><?php echo $simbolo2.number_format($TotalMoneda, 2, '.', ','); ?></label></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>


          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
######################## BUSQUEDA DE INGREDIENTES POR MONEDA ##########################
?>

<?php 
######################## BUSQUEDA DE KARDEX POR INGREDIENTES ########################
if (isset($_GET['BuscaKardexIngrediente']) && isset($_GET['codingrediente']) && isset($_GET['codsucursal'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$codingrediente = limpiar($_GET['codingrediente']); 

  if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codingrediente=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR REALICE LA BÚSQUEDA DEL INGREDIENTE CORRECTAMENTE</center>";
  echo "</div>";
  exit;
   
   } else {
  
$kardex = new Login();
$kardex = $kardex->BuscarKardexIngrediente();  

$detalle = new Login();
$detalle = $detalle->DetalleKardexIngrediente();
?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Movimientos de Ingrediente </h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codingrediente=<?php echo $codingrediente; ?>&tipo=<?php echo encrypt("KARDEXINGREDIENTES"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codingrediente=<?php echo $codingrediente; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("KARDEXINGREDIENTES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codingrediente=<?php echo $codingrediente; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("KARDEXINGREDIENTES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

              </div>
            </div>
          </div>

          <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                              <thead>
                              <tr>
                                  <th>Nº</th>
                                  <th>Movimiento</th>
                                  <th>Entradas</th>
                                  <th>Salidas</th>
                                  <th>Devolución</th>
                                  <th>Precio Costo</th>
                                  <th>Costo Movimiento</th>
                                  <th>Stock Actual</th>
                                  <th>Documento</th>
                                  <th>Fecha de Kardex</th>
                              </tr>
                              </thead>
                              <tbody>
<?php
$TotalEntradas=0;
$TotalSalidas=0;
$TotalDevolucion=0;
$a=1;
for($i=0;$i<sizeof($kardex);$i++){ 
$simbolo = ($detalle[0]['simbolo'] == "" ? "" : "<strong>".$detalle[0]['simbolo']."</strong>");

$TotalEntradas+=$kardex[$i]['entradas'];
$TotalSalidas+=$kardex[$i]['salidas'];
$TotalDevolucion+=$kardex[$i]['devolucion'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $kardex[$i]['movimiento']; ?></td>
  <td><?php echo number_format($kardex[$i]['entradas'], 2, '.', ','); ?></td>
  <td><?php echo number_format($kardex[$i]['salidas'], 2, '.', ','); ?></td>
  <td><?php echo number_format($kardex[$i]['devolucion'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($kardex[$i]['precio'], 2, '.', ','); ?></td>
  <?php if($kardex[$i]["movimiento"]=="ENTRADAS"){ ?>
  <td><?php echo $simbolo.number_format($kardex[$i]['precio']*$kardex[$i]['entradas'], 2, '.', ','); ?></td>
  <?php } elseif($kardex[$i]["movimiento"]=="SALIDAS"){ ?>
  <td><?php echo $simbolo.number_format($kardex[$i]['precio']*$kardex[$i]['salidas'], 2, '.', ','); ?></td>
  <?php } else { ?>
  <td><?php echo $simbolo.number_format($kardex[$i]['precio']*$kardex[$i]['devolucion'], 2, '.', ','); ?></td>
  <?php } ?>
  <td><?php echo number_format($kardex[$i]['stockactual'], 2, '.', ','); ?></td>
  <td><?php echo $kardex[$i]['documento']." ".$num = ($kardex[$i]['documento'] == 'VENTA' || $kardex[$i]['documento'] == 'DEVOLUCION' ? $kardex[$i]['codproceso'] : ""); ?></td>
  <td><?php echo date("d-m-Y",strtotime($kardex[$i]['fechakardex'])); ?></td>
  </tr>
  <?php  }  ?>
  </tbody>
  </table>
                        
        <label>Detalles de Ingrediente</label><br>
        <label>Código:</label> <?php echo $detalle[0]['codingrediente']; ?><br>
        <label>Descripción:</label> <?php echo $detalle[0]['nomingrediente']; ?><br>
        <label>Categoria:</label> <?php echo $detalle[0]['nommedida']; ?><br>
        <label>Total Entradas:</label> <?php echo number_format($TotalEntradas, 2, '.', ','); ?><br>
        <label>Total Salidas:</label> <?php echo number_format($TotalSalidas, 2, '.', ','); ?><br>
        <label>Total Devolución:</label> <?php echo number_format($TotalDevolucion, 2, '.', ','); ?><br>
        <label>Existencia:</label> <?php echo number_format($detalle[0]['cantingrediente'], 2, '.', ','); ?><br>
        <label>Precio Compra:</label> <?php echo $simbolo.number_format($detalle[0]['preciocompra'], 2, '.', ','); ?><br>
        <label>Precio Venta:</label> <?php echo $simbolo.number_format($detalle[0]['precioventa'], 2, '.', ','); ?>
            </div>

        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
######################## BUSQUEDA DE KARDEX POR INGREDIENTES ########################
?>

<?php 
########################### BUSQUEDA KARDEX INGREDIENTES VALORIZADO POR FECHAS ##########################
if (isset($_GET['BuscaKardexIngredientesxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
  if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DESDE PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA HASTA PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DESDE NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarKardexIngredientesValorizadoxFechas();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Kardex Valorizado por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("KARDEXINGREDIENTESVALORIZADOXFECHAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("KARDEXINGREDIENTESVALORIZADOXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("KARDEXINGREDIENTESVALORIZADOXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr role="row">
                              <th>Nº</th>
                              <th>Código</th>
                              <th>Descripción de Producto</th>
                              <th>Medida</th>
                              <th>Desc %</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Precio de Venta</th>
                              <th>Vendido</th>
                              <th>Total Venta</th>
                              <th>Total Compra</th>
                              <th>Ganancias</th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$PrecioCompraTotal=0;
$PrecioVentaTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$ImpuestosCompraTotal=0;
$ImpuestosVentaTotal=0;
$CompraTotal=0;
$VentaTotal=0;
$TotalGanancia=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioCompraTotal+=$reg[$i]['preciocompra'];
$PrecioVentaTotal+=$reg[$i]['precioventa'];
$ExisteTotal+=$reg[$i]['cantingrediente'];
$VendidosTotal+=$reg[$i]['cantidad'];

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

//VALOR DE IMPUESTO
$ValorImpuesto = 1 + ($valor/100);

//CALCULO SUBTOTAL IMPUESTOS PRECIO COMPRA
$DiscriminadoC         = $reg[$i]['preciocompra']/$ValorImpuesto;
$SubtotalDiscriminadoC = $reg[$i]['preciocompra'] - $DiscriminadoC;
$BaseDiscriminadoC     = $SubtotalDiscriminadoC * $reg[$i]['cantidad'];
$SubtotalimpuestosC    = ($reg[$i]['ivaproducto'] != '0.00' ? number_format($BaseDiscriminadoC, 2, '.', '') : "0.00");

//CALCULO SUBTOTAL IMPUESTOS PRECIO VENTA
$DiscriminadoV         = $PrecioFinal/$ValorImpuesto;
$SubtotalDiscriminadoV = $PrecioFinal - $DiscriminadoV;
$BaseDiscriminadoV     = $SubtotalDiscriminadoV * $reg[$i]['cantidad'];
$SubtotalimpuestosV    = ($reg[$i]['ivaproducto'] != '0.00' ? number_format($BaseDiscriminadoV, 2, '.', '') : "0.00");

$SumCompra = ($reg[$i]['preciocompra']*$reg[$i]['cantidad'])-$SubtotalimpuestosC;
$SumVenta  = ($PrecioFinal*$reg[$i]['cantidad'])-$SubtotalimpuestosV; 

$CompraTotal          += $SumCompra;
$ImpuestosCompraTotal += $SubtotalimpuestosC;
$VentaTotal           += $SumVenta;
$ImpuestosVentaTotal  += $SubtotalimpuestosV;
$TotalGanancia        += $SumVenta-$SumCompra;
?>
            <tr role="row" class="odd">
            <td><?php echo $a++; ?></div></td>
            <td><?php echo $reg[$i]['codproducto']; ?></td>
            <td><?php echo $reg[$i]['producto']; ?></td>
            <td><?php echo $reg[$i]['codcategoria'] == '0' ? "*****" : $reg[$i]['nommedida']; ?></td>
            <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
            <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
            <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
                      </tr>
              <?php  }  ?>
            <tr>
              <td colspan="7"></td>
              <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></label></td>
            </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA KARDEX INGREDIENTES VALORIZADO POR FECHAS ##########################
?>

<?php 
######################## BUSQUEDA DE INGREDIENTES VENDIDOS ########################
if (isset($_GET['BuscaIngredientesVendidos']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} elseif($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarIngredientesVendidos();  
?>
 
<!-- Row -->
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Ingredientes Vendidos por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("INGREDIENTESVENDIDOS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("INGREDIENTESVENDIDOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("INGREDIENTESVENDIDOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>Código</th>
                              <th>Descripción de Ingrediente</th>
                              <th>Unidad Medida</th>
                              <th>Precio de Venta</th>
                              <th>Existencia</th>
                              <th>Vendido</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Desc %</th>
                              <th>Monto Total</th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$TotalGeneral=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal   += $reg[$i]['precioventa'];
$ExisteTotal   += $reg[$i]['cantingrediente'];
$VendidosTotal += $reg[$i]['cantidad'];

$Descuento        = $reg[$i]['descproducto']/100;
$PrecioDescuento  = $reg[$i]['precioventa']*$Descuento;
//$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$CalculoDescuento = number_format($reg[$i]['totaldescuentov'], 2, '.', '');
$PrecioFinal      = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg              = $reg[$i]['ivaproducto']/100;
$CalculoImpuesto  = number_format($reg[$i]['subtotalimpuestos'], 2, '.', '');

$TotalDescuento += $CalculoDescuento; 
$TotalImpuesto  += $CalculoImpuesto; 
$TotalGeneral   += $PrecioFinal*$reg[$i]['cantidad'];
?>
          <tr>
            <td><?php echo $a++; ?></div></td>
            <td><?php echo $reg[$i]['codproducto']; ?></td>
            <td><?php echo $reg[$i]['producto']; ?></td>
            <td><?php echo $reg[$i]['nommedida']; ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
            <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
            <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
            <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
            <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
            </tr>
            <?php  }  ?>
          <tr>
            <td colspan="4"></td>
            <td><label><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></label></td>
            <td><label><?php echo number_format($ExisteTotal, 2, '.', ','); ?></label></td>
            <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
            <td><label><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></label></td>
            <td><label><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></label></td>
            <td><label><?php echo $simbolo.number_format($TotalGeneral, 2, '.', ','); ?></label></td>
          </tr>
          </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
######################## BUSQUEDA DE INGREDIENTES VENDIDOS ########################
?>





















<?php 
######################## MUESTRA DIV PRODUCTO ########################
if (isset($_GET['BuscaDivProducto'])) {
  
  ?>
<div class="row">
      <div class="col-md-12">
<font color="red"><label> Para poder realizar la Carga Masiva de Productos, el archivo Excel, debe estar estructurado de 18 columnas, la cuales tendrán las siguientes especificaciones:</label></font><br><br>

  1. Código de Producto (Ejem. 0001).<br>
  2. Nombre de Producto.<br>
  3. Código de Categoria. (Deberá ingresar el Nº de Categoria a la que corresponde o colocar Cero.<br>
  4. Precio Compra. (Numeros con 2 decimales).<br>
  5. Precio Venta. (Numeros con 2 decimales).<br>
  6. Existencia. (Debe de ser solo enteros).<br>
  7. Stock Minimo. (Debe de ser solo enteros).<br>
  8. Stock Máximo. (Debe de ser solo enteros).<br>
  9. <?php echo $impuesto; ?> de Producto. (Ejem. SI o NO).<br>
  10. Descuento de Producto. (Numeros con 2 decimales).<br>
  11. Código de Barra. (En caso de no tener colocar Cero.<br>
  12. Lote de Producto (En caso de no tener colocar Cero.<br>
  13. Fecha de Elaboración. (Formato: 0000-00-00).<br>
  14. Fecha de Expiración. (Formato: 0000-00-00).<br>
  15. Proveedor. (Debe de verificar a que codigo pertenece el Proveedor existente).<br>
  16. Preparado. (1 = Cocina, 2 =  Bar, 3 = Reposteria)<br>
  17. Favorito. (Se debe de colocar 0 o 1. Ejem. SI = 1, NO = 0)<br>
  18. Control de Stock. (Se debe de colocar 0 o 1. Ejem. SI = 1, NO = 0).<br><br>

  <font color="red"><label> NOTA:</label></font><br>
  a) Se debe de guardar como archivo .CSV  (delimitado por comas)(*.csv).<br>
  b) Descargar Plantilla <a class="text-info" href="fotos/productos.csv">AQUI</a>.<br>
  c) Todos los datos deberán escribirse en mayúscula para mejor orden y visibilidad en los reportes.<br>
  d) Deben de tener en cuenta que la carga masiva de Productos, deben de ser cargados como se explica, para evitar problemas de datos del productos dentro del Sistema.<br><br>
    </div>
</div>                                 
<?php 
}
######################## MUESTRA DIV PRODUCTO ########################
?>

<?php
######################## MOSTRAR PRODUCTOS EN VENTANA MODAL ########################
if (isset($_GET['BuscaProductoModal']) && isset($_GET['codproducto']) && isset($_GET['codsucursal'])) { 

$reg = $new->ProductosPorId(); 
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>"); 
?>
  
  <table class="table-responsive" border="0" class="text-center">
  <tr>
    <td><strong>Código:</strong> <?php echo $reg[0]['codproducto']; ?></td>
  </tr>
  <tr>
    <td><strong>Producto:</strong> <?php echo $reg[0]['producto']; ?></td>
  </tr> 
  <tr>
  <td><strong>Proveedor: </strong><?php echo $reg[0]['codproveedor'] == '0' ? "******" : $reg[0]['cuitproveedor'].": ".$reg[0]['nomproveedor']; ?></td>
  </tr> 
  <tr>
    <td><strong>Categoria:</strong> <?php echo $reg[0]['nomcategoria']; ?></td>
  </tr>
  <tr>
    <td><strong>Precio de Compra: </strong> <?php echo $preciocompra = ($_SESSION['acceso'] == "cajero" || $_SESSION["acceso"]=="cocinero" ? "**********" : $simbolo.number_format($reg[0]['preciocompra'], 2, '.', ',')); ?></td>
  </tr> 
  <tr>
    <td><strong>Precio de Venta: </strong> <?php echo $simbolo.number_format($reg[0]['precioventa'], 2, '.', ','); ?></td>
  </tr>
<?php if($reg[0]['montocambio']!=""){ ?>
  <tr>
    <td><strong><?php echo "Precio ".$reg[0]['siglas']; ?>: </strong> 
      <?php echo "<label>".$reg[0]['simbolo2']."</label> ".number_format($reg[0]['precioventa']/$reg[0]['montocambio'], 2, '.', ','); ?></td>
  </tr> 
<?php } ?>
  <tr>
    <td><strong>Existencia: </strong> <?php echo number_format($reg[0]['existencia'], 2, '.', ','); ?></td>
  </tr> 
  <tr>
    <td><strong>Stock Minimo: </strong> <?php echo $reg[0]['stockminimo'] == '0.00' ? "******" : number_format($reg[0]['stockminimo'], 2, '.', ','); ?></td>
  </tr> 
  <tr>
    <td><strong>Stock Máximo: </strong> <?php echo $reg[0]['stockmaximo'] == '0.00' ? "******" : number_format($reg[0]['stockmaximo'], 2, '.', ','); ?></td>
  </tr> 
  <tr>
    <td><strong><?php echo $impuesto; ?>: </strong> <?php echo $reg[0]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
  </tr> 
  <tr>
    <td><strong>Descuento: </strong> <?php echo number_format($reg[0]['descproducto'], 2, '.', ',')."%"; ?></td>
  </tr> 
  <tr>
  <td><strong>Código de Barra: </strong> <?php echo $reg[0]['codigobarra'] == '0' ? "******" : $reg[0]['codigobarra']; ?></td>
  </tr> 
  <tr>
    <td><strong>Nº de Lote: </strong> <?php echo $reg[0]['lote'] == '0' ? "******" : $reg[0]['lote']; ?></td>
  </tr> 
  <tr>
    <td><strong>Fecha de Elaboración: </strong> <?php echo $reg[0]['fechaelaboracion'] == '0000-00-00' ? "******" : date("d-m-Y",strtotime($reg[0]['fechaelaboracion'])); ?></td>
  </tr> 
  <tr>
    <td><strong>Fecha de Expiración: </strong> <?php echo $reg[0]['fechaexpiracion'] == '0000-00-00' ? "******" : date("d-m-Y",strtotime($reg[0]['fechaexpiracion'])); ?></td>
  </tr>
  <tr>
    <td><strong>Estado: </strong> <?php echo $status = ( $reg[0]['existencia'] != 0 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ACTIVO</span>" : "<span class='badge badge-danger'><i class='fa fa-times'></i> INACTIVO</span>"); ?></td>
  </tr>
    <tr>
    <td><strong>Preparado: </strong> 
    <?php if($reg[0]['preparado'] == 1){ 
    echo "<span class='badge badge-success'><i class='fa fa-bank'></i> EN COCINA</span>"; 
    } elseif($reg[0]['preparado'] == 2){ 
    echo "<span class='badge badge-info'><i class='fa fa-bank'></i> EN BAR</span>"; 
    } else { echo "<span class='badge badge-primary'><i class='fa fa-bank'></i> EN PASTELERIA</span>"; 
    } ?></td>  
    </tr>
    <tr>
    <td><strong>Favorito: </strong> <?php echo $favorito = ( $reg[0]['favorito'] == 1 ? "<span class='badge badge-success'><i class='fa fa-check'></i> SI</span>" : "<span class='badge badge-warning'><i class='fa fa-times'></i> NO</span>"); ?></td>  
    </tr>
    <tr>
    <td><strong>Control de Stock: </strong> <?php echo $control = ( $reg[0]['controlstockp'] == 1 ? "<span class='badge badge-success'><i class='fa fa-check'></i> SI</span>" : "<span class='badge badge-warning'><i class='fa fa-times'></i> NO</span>"); ?></td>  
    </tr>
  <?php if($_SESSION['acceso'] == "administradorG") { ?>
  <tr>
    <td><strong>Sucursal Asignada: </strong> <?php echo $reg[0]['codsucursal'] == "0" ? "**********" : $reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal']; ?></td>
  </tr>
  <?php } ?>
</table>

<?php 
$tru = new Login();
$a=1;
$busq = $tru->VerDetallesIngredientes(); 

if($busq==""){

    echo "";      
    
} else {

?>
<div id="div1">
  <table id="default_order" class="table2 table-striped table-bordered border display m-t-10">
    <thead>
    <tr>
    <th colspan="6" data-priority="1"><center>Ingredientes Agregados</center></th>
    </tr>
    <tr>
      <th>Nº</th>
      <th>Ingrediente</th>
      <th>Medida</th>
      <th>Existencia</th>
      <th>Cant. Ración</th>
      <th>P.V.P</th>
    </tr>
    </thead>
    <tbody>
<?php 
$TotalCosto=0;
for($i=0;$i<sizeof($busq);$i++){
$TotalCosto+=($busq[$i]['precioventa']-$busq[$i]['descingrediente']/100)*$busq[$i]["cantracion"];
?>
          <tr>
            <th><?php echo $a++; ?></th>
<td><?php echo $busq[$i]["nomingrediente"]; ?></td>
<td><?php echo $busq[$i]["nommedida"]; ?></td>
<td><?php echo number_format($busq[$i]["cantingrediente"], 2, '.', ','); ?></td>
<td><?php echo number_format($busq[$i]["cantracion"], 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($busq[$i]["precioventa"], 2, '.', ','); ?></td>
    </tr> 
    <?php } ?> 
    <tr>
      <td colspan="4"></td>
      <td><label>Total Gasto</label></td>
      <td><label><?php echo $simbolo.number_format($TotalCosto, 2, '.', ','); ?></label></td>
      </tr>
    </tbody>
    </table>
    </div>
<?php  
  }
} 
######################## MOSTRAR PRODUCTOS EN VENTANA MODAL ########################
?>

<?php 
######################## MUESTRA INGREDIENTES AGREGADOS A PRODUCTOS ########################
if (isset($_GET['BuscaDetallesProducto']) && isset($_GET['codproducto']) && isset($_GET['codsucursal'])) { 
$new = new Login();
$reg = $new->ProductosPorId();  
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>"); 
?>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group has-feedback">
              <label class="control-label">Código de Producto: <span class="symbol required"></span></label>
              <input type="hidden" name="proceso" id="proceso" value="save"/>
              <input type="hidden" name="codsucursal" id="codsucursal" value="<?php echo encrypt($reg[0]['codsucursal']); ?>"> 
              <input type="hidden" name="codproducto" id="codproducto" value="<?php echo encrypt($reg[0]['codproducto']); ?>"/>
              <input type="hidden" name="preciocompra" id="preciocompra" value="0.00"/>
              <input type="hidden" name="precioventa" id="precioventa" value="0.00"/>
              <br /><abbr title="Código de Producto"><?php echo $reg[0]['codproducto']; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Nombre de Producto: <span class="symbol required"></span></label>
                <br /><abbr title="Nombre de Producto"><?php echo $reg[0]['producto']; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Existencia: <span class="symbol required"></span></label>
                <br /><abbr title="Existencia de Producto"><?php echo $reg[0]['existencia']; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Categoria de Producto: <span class="symbol required"></span></label>
                <br /><abbr title="Categoria de Producto"><?php echo $reg[0]['nomcategoria']; ?></abbr>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Precio de Compra: <span class="symbol required"></span></label>
                <input type="hidden" name="preciocomprabd" id="preciocomprabd" value="<?php echo $reg[0]['preciocompra']; ?>"/>
                <br /><abbr title="Precio de Compra"><?php echo number_format($reg[0]['preciocompra'], 2, '.', ','); ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Precio de Venta: <span class="symbol required"></span></label>
                <input type="hidden" name="precioventabd" id="precioventabd" value="<?php echo $reg[0]['precioventa']; ?>"/>
                <br /><abbr title="Precio de Venta"><?php echo number_format($reg[0]['precioventa'], 2, '.', ','); ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label"><?php echo $impuesto; ?> de Producto: <span class="symbol required"></span></label>
                <br /><abbr title="<?php echo $impuesto; ?> de Producto"><?php echo $reg[0]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Descuento de Producto: <span class="symbol required"></span></label>
                <br /><abbr title="Descuento de Producto"><?php echo number_format($reg[0]['descproducto'], 2, '.', ','); ?></abbr>
            </div>
        </div>
    </div>

<h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Ingredientes Agregados</h2><br>

<table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
    <thead>
    <tr role="row">
    </tr>
    <tr>
        <th>Nº</th>
        <th>Cant. Porción</th>
        <th>Ingrediente</th>
        <th>Existencia</th>
        <th>Precio Compra</th>
        <th>Precio Venta</th>
        <th><span class="mdi mdi-drag-horizontal"></span></th>
    </tr>
    </thead>
    <tbody>
<?php 
$tru = new Login();
$a=1;
$busq = $tru->VerDetallesIngredientes();

if($busq==""){

echo "";      

} else {

for($i=0;$i<sizeof($busq);$i++){
?>
  <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
    <td><?php echo $a++; ?></td>
    <td><?php echo $busq[$i]["cantracion"]; ?></td>
    <td><?php echo $busq[$i]["nomingrediente"]; ?></td>
    <td><?php echo $busq[$i]["cantingrediente"]." ".$busq[$i]["nommedida"]; ?></td>
    <td><?php echo number_format($busq[$i]["cantracion"]*$busq[$i]["preciocompra"], 2, '.', ','); ?></td>
    <td><?php echo number_format($busq[$i]["cantracion"]*$busq[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><button type="button" class="btn btn-dark btn-rounded" onClick="EliminaDetalleProducto('<?php echo encrypt($busq[$i]['codproducto']); ?>','<?php echo encrypt($busq[$i]['idingrediente']); ?>','<?php echo encrypt($busq[$i]['codingrediente']); ?>','<?php echo encrypt($busq[$i]['cantracion']); ?>','<?php echo encrypt($busq[$i]['codsucursal']); ?>','<?php echo encrypt("ELIMINADETALLEPRODUCTO"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td>
  </tr><?php } } ?>
  </tbody>
  </table>
<?php 
}
######################## MUESTRA INGREDIENTES AGREGADOS A PRODUCTOS ########################
?>

<?php 
######################## BUSQUEDA DE PRODUCTOS POR MONEDA ########################
if (isset($_GET['BuscaProductosxMoneda']) && isset($_GET['codsucursal']) && isset($_GET['codmoneda'])) { 

  $codsucursal = limpiar($_GET['codsucursal']);
  $codmoneda = limpiar($_GET['codmoneda']);

  if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codmoneda=="") { 

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE TIPO DE MONEDA PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else {

$cambio = new Login();
$cambio = $cambio->BuscarTiposCambios();
  
$reg = $new->ListarProductos();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Productos según Moneda</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codmoneda=<?php echo $codmoneda; ?>&tipo=<?php echo encrypt("PRODUCTOSXMONEDA"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codmoneda=<?php echo $codmoneda; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("PRODUCTOSXMONEDA"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codmoneda=<?php echo $codmoneda; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("PRODUCTOSXMONEDA"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

              </div>
            </div>
          </div>

          <div id="div4"><table id="datatable-responsive" class="table2 table-hover table-nomargin table-bordered dataTable table-striped" cellspacing="0" width="100%">
                               <thead>
                               <tr role="row">
                                  <th>N°</th>
                                  <th>Código</th>
                                  <th>Nombre de Producto</th>
                                  <th>Categoria</th>                                 
                                  <th><?php echo $impuesto; ?></th>
                                  <th>Desc %</th>
                                  <th>Existencia</th>
                                  <th>Precio Venta</th>
                                  <th><?php echo $cambio[0]['siglas']; ?></th> 
                               </tr>
                               </thead>
                               <tbody class="BusquedaRapida">

<?php 
if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PRODUCTOS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
$TotalPrecioVenta=0;
$TotalPrecioVentaDescuento=0;
$TotalMoneda=0;
$TotalArticulos=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo  = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
$simbolo2 = "<strong>".$cambio[0]['simbolo']."</strong>";

$Descuento       = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal     = $reg[$i]['precioventa']-$PrecioDescuento;

$TotalArticulos += $reg[$i]['existencia'];
$TotalPrecioVenta     += $reg[$i]['precioventa'];
$TotalPrecioVentaDescuento  += $PrecioFinal;
$TotalMoneda    += ($cambio == 0 ? "0" : $reg[$i]['precioventa']/$cambio[0]['montocambio']);
?>
    <tr role="row" class="odd">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['codproducto']; ?></td>
      <td><?php echo $reg[$i]['producto']; ?></td>
      <td><?php echo $reg[$i]['nomcategoria']; ?></td>
      <td><?php echo $reg[$i]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
      <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?></td>
      <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo2.number_format($reg[$i]['precioventa']/$cambio[0]['montocambio'], 2, '.', ','); ?></td>
    </tr>
    <?php } ?>
    <tr>
      <td colspan="6"></td>
      <td><label><?php echo number_format($TotalArticulos, 2, '.', ','); ?></label></td>
      <td><label><?php echo $simbolo.number_format($TotalPrecioVenta, 2, '.', ','); ?></label></td>
      <td><label><?php echo $simbolo2.number_format($TotalMoneda, 2, '.', ','); ?></label></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
######################## BUSQUEDA DE PRODUCTOS POR MONEDA ##########################
?>


<?php 
######################## BUSQUEDA DE KARDEX POR PRODUCTOS ########################
if (isset($_GET['BuscaKardexProducto']) && isset($_GET['codsucursal']) && isset($_GET['codproducto'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$codproducto = limpiar($_GET['codproducto']); 

  if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codproducto=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR REALICE LA BÚSQUEDA DEL PRODUCTO CORRECTAMENTE</center>";
  echo "</div>";
  exit;
   
   } else {
  
$kardex = new Login();
$kardex = $kardex->BuscarKardexProducto();

$detalle = new Login();
$detalle = $detalle->DetalleKardexProducto(); 
?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Movimientos por Producto </h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codproducto=<?php echo $codproducto; ?>&tipo=<?php echo encrypt("KARDEXPRODUCTOS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codproducto=<?php echo $codproducto; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("KARDEXPRODUCTOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codproducto=<?php echo $codproducto; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("KARDEXPRODUCTOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

              </div>
            </div>
          </div>

          <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                              <thead>
                              <tr>
                                  <th>Nº</th>
                                  <th>Movimiento</th>
                                  <th>Entradas</th>
                                  <th>Salidas</th>
                                  <th>Devolución</th>
                                  <th>Precio Costo</th>
                                  <th>Costo Movimiento</th>
                                  <th>Stock Actual</th>
                                  <th>Documento</th>
                                  <th>Fecha de Kardex</th>
                              </tr>
                              </thead>
                              <tbody>
<?php
$TotalEntradas=0;
$TotalSalidas=0;
$TotalDevolucion=0;
$a=1;
for($i=0;$i<sizeof($kardex);$i++){ 
$simbolo = ($detalle[0]['simbolo'] == "" ? "" : "<strong>".$detalle[0]['simbolo']."</strong>");

$TotalEntradas+=$kardex[$i]['entradas'];
$TotalSalidas+=$kardex[$i]['salidas'];
$TotalDevolucion+=$kardex[$i]['devolucion'];
?>
    <tr>
      <td><?php echo $a++; ?></td>
      <td><?php echo $kardex[$i]['movimiento']; ?></td>
      <td><?php echo number_format($kardex[$i]['entradas'], 2, '.', ','); ?></td>
      <td><?php echo number_format($kardex[$i]['salidas'], 2, '.', ','); ?></td>
      <td><?php echo number_format($kardex[$i]['devolucion'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($kardex[$i]['precio'], 2, '.', ','); ?></td>
      <?php if($kardex[$i]["movimiento"]=="ENTRADAS"){ ?>
      <td><?php echo $simbolo.number_format($kardex[$i]['precio']*$kardex[$i]['entradas'], 2, '.', ','); ?></td>
      <?php } elseif($kardex[$i]["movimiento"]=="SALIDAS"){ ?>
      <td><?php echo $simbolo.number_format($kardex[$i]['precio']*$kardex[$i]['salidas'], 2, '.', ','); ?></td>
      <?php } else { ?>
      <td><?php echo $simbolo.number_format($kardex[$i]['precio']*$kardex[$i]['devolucion'], 2, '.', ','); ?></td>
      <?php } ?>
      <td><?php echo number_format($kardex[$i]['stockactual'], 2, '.', ','); ?></td>
      <td><?php echo $kardex[$i]['documento']." ".$num = ($kardex[$i]['documento'] == 'VENTA' || $kardex[$i]['documento'] == 'DEVOLUCION' ? $kardex[$i]['codproceso'] : ""); ?></td>
      <td><?php echo date("d-m-Y",strtotime($kardex[$i]['fechakardex'])); ?></td>
    </tr>
    <?php  }  ?>
    </tbody>
    </table>
                        
          <label>Detalles de Producto</label><br>
          <label>Código:</label> <?php echo $detalle[0]['codproducto']; ?><br>
          <label>Descripción:</label> <?php echo $detalle[0]['producto']; ?><br>
          <label>Categoria:</label> <?php echo $detalle[0]['nomcategoria']; ?><br>
          <label>Total Entradas:</label> <?php echo number_format($TotalEntradas, 2, '.', ','); ?><br>
          <label>Total Salidas:</label> <?php echo number_format($TotalSalidas, 2, '.', ','); ?><br>
          <label>Total Devolución:</label> <?php echo number_format($TotalDevolucion, 2, '.', ','); ?><br>
          <label>Existencia:</label> <?php echo number_format($detalle[0]['existencia'], 2, '.', ','); ?><br>
          <label>Precio Compra:</label> <?php echo $simbolo.number_format($detalle[0]['preciocompra'], 2, '.', ','); ?><br>
          <label>Precio Venta:</label> <?php echo $simbolo.number_format($detalle[0]['precioventa'], 2, '.', ','); ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
######################## BUSQUEDA DE KARDEX POR PRODUCTOS ########################
?>

<?php 
########################### BUSQUEDA KARDEX PRODUCTOS VALORIZADO POR FECHAS ##########################
if (isset($_GET['BuscaKardexProductosxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
  if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DESDE PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA HASTA PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DESDE NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarKardexProductosValorizadoxFechas();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Kardex Valorizado por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("KARDEXPRODUCTOSVALORIZADOXFECHAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("KARDEXPRODUCTOSVALORIZADOXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("KARDEXPRODUCTOSVALORIZADOXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr role="row">
                              <th>Nº</th>
                              <th>Código</th>
                              <th>Descripción de Producto</th>
                              <th>Categoria</th>
                              <th>Desc %</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Precio de Venta</th>
                              <th>Vendido</th>
                              <th>Total Venta</th>
                              <th>Total Compra</th>
                              <th>Ganancias</th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$PrecioCompraTotal=0;
$PrecioVentaTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$ImpuestosCompraTotal=0;
$ImpuestosVentaTotal=0;
$CompraTotal=0;
$VentaTotal=0;
$TotalGanancia=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioCompraTotal+=$reg[$i]['preciocompra'];
$PrecioVentaTotal+=$reg[$i]['precioventa'];
$ExisteTotal+=$reg[$i]['existencia'];
$VendidosTotal+=$reg[$i]['cantidad'];

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

//VALOR DE IMPUESTO
$ValorImpuesto = 1 + ($valor/100);

//CALCULO SUBTOTAL IMPUESTOS PRECIO COMPRA
$DiscriminadoC         = $reg[$i]['preciocompra']/$ValorImpuesto;
$SubtotalDiscriminadoC = $reg[$i]['preciocompra'] - $DiscriminadoC;
$BaseDiscriminadoC     = $SubtotalDiscriminadoC * $reg[$i]['cantidad'];
$SubtotalimpuestosC    = ($reg[$i]['ivaproducto'] != '0.00' ? number_format($BaseDiscriminadoC, 2, '.', '') : "0.00");

//CALCULO SUBTOTAL IMPUESTOS PRECIO VENTA
$DiscriminadoV         = $PrecioFinal/$ValorImpuesto;
$SubtotalDiscriminadoV = $PrecioFinal - $DiscriminadoV;
$BaseDiscriminadoV     = $SubtotalDiscriminadoV * $reg[$i]['cantidad'];
$SubtotalimpuestosV    = ($reg[$i]['ivaproducto'] != '0.00' ? number_format($BaseDiscriminadoV, 2, '.', '') : "0.00");

$SumCompra = ($reg[$i]['preciocompra']*$reg[$i]['cantidad'])-$SubtotalimpuestosC;
$SumVenta  = ($PrecioFinal*$reg[$i]['cantidad'])-$SubtotalimpuestosV; 

$CompraTotal          += $SumCompra;
$ImpuestosCompraTotal += $SubtotalimpuestosC;
$VentaTotal           += $SumVenta;
$ImpuestosVentaTotal  += $SubtotalimpuestosV;
$TotalGanancia        += $SumVenta-$SumCompra;
?>
            <tr role="row" class="odd">
            <td><?php echo $a++; ?></div></td>
            <td><?php echo $reg[$i]['codproducto']; ?></td>
            <td><?php echo $reg[$i]['producto']; ?></td>
            <td><?php echo $reg[$i]['codcategoria'] == '0' ? "*****" : $reg[$i]['nomcategoria']; ?></td>
            <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
            <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
            <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
                      </tr>
              <?php  }  ?>
            <tr>
              <td colspan="7"></td>
              <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></label></td>
            </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA KARDEX PRODUCTOS VALORIZADO POR FECHAS ##########################
?>

<?php 
######################## BUSQUEDA DE PRODUCTOS VENDIDOS ########################
if (isset($_GET['BuscaProductosVendidos']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

 } else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

 } elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarProductosVendidos();  
?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Productos Vendidos por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("PRODUCTOSVENDIDOS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("PRODUCTOSVENDIDOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("PRODUCTOSVENDIDOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <th>Nº</th>
                          <th>Código</th>
                          <th>Descripción de Producto</th>
                          <th>Categoria</th>
                          <th>Precio de Venta</th>
                          <th>Existencia</th>
                          <th>Vendido</th>
                          <th><?php echo $impuesto; ?></th>
                          <th>Desc %</th>
                          <th>Monto Total</th>
                        </tr>
                      </thead>
                      <tbody>
<?php
$a=1;
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$TotalGeneral=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal   += $reg[$i]['precioventa'];
$ExisteTotal   += $reg[$i]['existencia'];
$VendidosTotal += $reg[$i]['cantidad'];

$Descuento        = $reg[$i]['descproducto']/100;
$PrecioDescuento  = $reg[$i]['precioventa']*$Descuento;
//$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$CalculoDescuento = number_format($reg[$i]['totaldescuentov'], 2, '.', '');
$PrecioFinal      = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg              = $reg[$i]['ivaproducto']/100;
$CalculoImpuesto  = number_format($reg[$i]['subtotalimpuestos'], 2, '.', '');

$TotalDescuento += $CalculoDescuento; 
$TotalImpuesto  += $CalculoImpuesto; 
$TotalGeneral   += $PrecioFinal*$reg[$i]['cantidad']; 
?>
          <tr>
            <td><?php echo $a++; ?></div></td>
            <td><?php echo $reg[$i]['codproducto']; ?></td>
            <td><?php echo $reg[$i]['producto']; ?></td>
            <td><?php echo $reg[$i]['nomcategoria']; ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
            <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
            <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
            <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
            <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
          </tr>
          <?php  }  ?>
          <tr>
            <td colspan="4"></td>
            <td><label><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></label></td>
            <td><label><?php echo number_format($ExisteTotal, 2, '.', ','); ?></label></td>
            <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
            <td><label><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></label></td>
            <td><label><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></label></td>
            <td><label><?php echo $simbolo.number_format($TotalGeneral, 2, '.', ','); ?></label></td>
          </tr>
          </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
######################## BUSQUEDA DE PRODUCTOS VENDIDOS ########################
?>


<?php 
######################## BUSQUEDA DE PRODUCTOS PARA MENU ########################
if (isset($_GET['Buscar_Menu'])) { 

$reg = $new->ListarProductosMenu(); 
?>
<div id="div2">

<?php
if($reg==""){

} else {

$a=1;
for($cont = 0, $s = sizeof($reg); $cont < $s; $cont++):
$simbolo = ($reg[$cont]['simbolo'] == "" ? "" : "<strong>".$reg[$cont]['simbolo']."</strong>");
?>

<div class="table-responsive">
  <table class="table">
    <thead class="bg-danger text-white">
      <tr>
        <th colspan="2"><i class="fa fa-tasks"></i> <?php echo $reg[$cont]["nomcategoria"]; ?></th>
      </tr>

<?php
$a=1;
$explode = explode("<br>",$reg[$cont]['menu_productos']);

for($aum = 0, $r = sizeof($explode); $aum < $r; $aum++):
list($producto,$precioventa,$existencia) = explode("|",$explode[$aum]);
?>      
    </thead>
    <tbody>
      <tr class="table2">
        <td width="80%"><?php echo $producto; ?></td>
        <td width="20%"><?php echo $simbolo.$precioventa; ?></td>
      </tr>

<?php 
   endfor; ##fin de for 
?>
    </tbody>
  </table>
</div>

<?php
  endfor; ##fin de for
}
?>

<?php
$tra2 = new Login();
$combo = $tra2->ListarCombosMenu();

if($combo==""){

} else {

?> 

<div class="table-responsive">
  <table class="table">
    <thead class="bg-danger text-white">
      <tr>
        <th colspan="3"><i class="fa fa-tasks"></i> COMBOS</th>
      </tr>
<?php
$a=1;
for($contt = 0, $ss = sizeof($combo); $contt < $ss; $contt++):
$simbolo = ($combo[$contt]['simbolo'] == "" ? "" : "<strong>".$combo[$contt]['simbolo']."</strong>");
?>      
    </thead>
    <tbody>
      <tr class="table2">
        <td width="30%"><?php echo $combo[$contt]["nomcombo"]; ?></td>
        <td class="font-12" width="50%"><?php echo $combo[$contt]["detalles_productos"]; ?></td>
        <td width="20%"><?php echo $simbolo.$combo[$contt]["precioventa"]; ?></td>
      </tr>

<?php 
  endfor; ##fin de for 
}
?>


<?php
$extra = new Login();
$extra = $extra->ListarIngredientesMenu(); 
?>

<?php
if($extra==""){

} else {

$a=1;
for($cont = 0, $s = sizeof($extra); $cont < $s; $cont++):
$simbolo = ($extra[$cont]['simbolo'] == "" ? "" : "<strong>".$extra[$cont]['simbolo']."</strong>");
?>

<div class="table-responsive">
  <table class="table">
    <thead class="bg-danger text-white">
      <tr>
        <th colspan="2"><i class="fa fa-tasks"></i> <?php echo $extra[$cont]["nommedida"]; ?></th>
      </tr>

<?php
$a=1;
$explode = explode("<br>",$extra[$cont]['menu_extras']);

for($aum = 0, $r = sizeof($explode); $aum < $r; $aum++):
list($nomingrediente,$precioingrediente,$existencia) = explode("|",$explode[$aum]);
?>      
    </thead>
    <tbody>
      <tr class="table2">
        <td width="80%"><?php echo $nomingrediente; ?></td>
        <td width="20%"><?php echo $simbolo.$precioingrediente; ?></td>
      </tr>

<?php 
   endfor; ##fin de for 
?>
    </tbody>
  </table>
</div>

<?php
  endfor; ##fin de for
}
?>

    </tbody>
  </table>
</div>

</div>

<?php
}
######################## BUSQUEDA DE PRODUCTOS PARA MENU ##########################
?>




























<?php
######################## MOSTRAR COMBOS EN VENTANA MODAL ########################
if (isset($_GET['BuscaComboModal']) && isset($_GET['codcombo']) && isset($_GET['codsucursal'])) { 

$reg = $new->CombosPorId(); 
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");
?>
  
  <table class="table-responsive" border="0" align="center">
  <tr>
    <td><strong>Código:</strong> <?php echo $reg[0]['codcombo']; ?></td>
  </tr>
  <tr>
    <td><strong>Nombre de Combo:</strong> <?php echo $reg[0]['nomcombo']; ?></td>
  </tr> 
  <tr>
    <td><strong>Precio de Compra: </strong> <?php echo $preciocompra = ($_SESSION['acceso'] == "cajero" || $_SESSION["acceso"]=="cocinero" ? "**********" : $simbolo.number_format($reg[0]['preciocompra'], 2, '.', ',')); ?></td>
  </tr> 
  <tr>
    <td><strong>Precio de Venta: </strong> <?php echo $simbolo.number_format($reg[0]['precioventa'], 2, '.', ','); ?></td>
  </tr>
<?php if($reg[0]['montocambio']!=""){ ?>
  <tr>
    <td><strong><?php echo "Precio ".$reg[0]['siglas']; ?>: </strong> 
      <?php echo "<label>".$reg[0]['simbolo2']."</label> ".number_format($reg[0]['precioventa']/$reg[0]['montocambio'], 2, '.', ','); ?></td>
  </tr> 
<?php } ?>
  <tr>
    <td><strong>Existencia: </strong> <?php echo number_format($reg[0]['existencia'], 2, '.', ','); ?></td>
  </tr> 
  <tr>
    <td><strong>Stock Minimo: </strong> <?php echo $reg[0]['stockminimo'] == '0.00' ? "******" : number_format($reg[0]['stockminimo'], 2, '.', ','); ?></td>
  </tr> 
  <tr>
    <td><strong>Stock Máximo: </strong> <?php echo $reg[0]['stockmaximo'] == '0.00' ? "******" : number_format($reg[0]['stockmaximo'], 2, '.', ','); ?></td>
  </tr> 
  <tr>
    <td><strong><?php echo $impuesto; ?>: </strong> <?php echo $reg[0]['ivacombo'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
  </tr> 
  <tr>
    <td><strong>Descuento: </strong> <?php echo number_format($reg[0]['desccombo'], 2, '.', ',')."%"; ?></td>
  </tr> 
  <tr>
    <td><strong>Estado: </strong> <?php echo $status = ( $reg[0]['existencia'] != 0 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ACTIVO</span>" : "<span class='badge badge-danger'><i class='fa fa-times'></i> INACTIVO</span>"); ?></td>
  </tr>
    <tr>
    <td><strong>Preparado: </strong> 
    <?php if($reg[0]['preparado'] == 1){ 
    echo "<span class='badge badge-success'><i class='fa fa-bank'></i> EN COCINA</span>"; 
    } elseif($reg[0]['preparado'] == 2){ 
    echo "<span class='badge badge-info'><i class='fa fa-bank'></i> EN BAR</span>"; 
    } else { echo "<span class='badge badge-primary'><i class='fa fa-bank'></i> EN PASTELERIA</span>"; 
    } ?></td>  
    </tr>
    
  <?php if($_SESSION['acceso'] == "administradorG") { ?>
  <tr>
    <td><strong>Sucursal Asignada: </strong> <?php echo $reg[0]['codsucursal'] == "0" ? "**********" : $reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal']; ?></td>
  </tr>
  <?php } ?>
</table>


<?php 
$tru = new Login();
$a=1;
$busq = $tru->VerDetallesProductos(); 

if($busq==""){

    echo "";      
    
} else {

?>
<div id="div1">
  <table id="default_order" class="table2 table-striped table-bordered border display m-t-10">
    <thead>
    <tr>
    <th colspan="6" data-priority="1"><center>Productos Agregados</center></th>
    </tr>
    <tr>
      <th>Nº</th>
      <th>Producto</th>
      <th>Categoria</th>
      <th>Existencia</th>
      <th>Cantidad</th>
      <th>P.V.P</th>
    </tr>
    </thead>
    <tbody>
<?php 
$TotalCosto=0;
for($i=0;$i<sizeof($busq);$i++){
$TotalCosto+=($busq[$i]['precioventa']-$busq[$i]['descproducto']/100)*$busq[$i]["cantidad"];
?>
  <tr>
    <th><?php echo $a++; ?></th>
    <td><?php echo $busq[$i]["producto"]; ?></td>
    <td><?php echo $busq[$i]["nomcategoria"]; ?></td>
    <td><?php echo number_format($busq[$i]["existencia"], 2, '.', ','); ?></td>
    <td><?php echo number_format($busq[$i]["cantidad"], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($busq[$i]["precioventa"], 2, '.', ','); ?></td>
  </tr> 
  <?php } ?> 
  <tr>
    <td colspan="4"></td>
    <td><label>Total Gasto</label></td>
    <td><label><?php echo $simbolo.number_format($TotalCosto, 2, '.', ','); ?></label></td>
    </tr>
    </tbody>
  </table>
  </div>
<?php  
  }
} 
######################## MOSTRAR COMBOS EN VENTANA MODAL ########################
?>

<?php 
######################## MUESTRA PRODUCTOS AGREGADOS A COMBOS ########################
if (isset($_GET['BuscaDetallesCombo']) && isset($_GET['codcombo']) && isset($_GET['codsucursal'])) { 
$new = new Login();
$reg = $new->CombosPorId();   
?>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group has-feedback">
              <label class="control-label">Código de Combo: <span class="symbol required"></span></label>
              <input type="hidden" name="proceso" id="proceso" value="save"/>
              <input type="hidden" name="codsucursal" id="codsucursal" value="<?php echo encrypt($reg[0]['codsucursal']); ?>"> 
              <input type="hidden" name="codcombo" id="codcombo" value="<?php echo encrypt($reg[0]['codcombo']); ?>"/>
              <input type="hidden" name="preciocompra" id="preciocompra" value="0.00"/>
              <input type="hidden" name="precioventa" id="precioventa" value="0.00"/>
              <br /><abbr title="Código de Combo"><?php echo $reg[0]['codcombo']; ?></abbr>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group has-feedback">
                <label class="control-label">Nombre de Combo: <span class="symbol required"></span></label>
                <br /><abbr title="Nombre de Combo"><?php echo $reg[0]['nomcombo']; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Existencia: <span class="symbol required"></span></label>
                <br /><abbr title="Existencia de Combo"><?php echo number_format($reg[0]['existencia'], 2, '.', ','); ?></abbr>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Precio de Compra: <span class="symbol required"></span></label>
                <input type="hidden" name="preciocomprabd" id="preciocomprabd" value="<?php echo $reg[0]['preciocompra']; ?>"/>
                <br /><abbr title="Precio de Compra"><?php echo number_format($reg[0]['preciocompra'], 2, '.', ','); ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Precio de Venta: <span class="symbol required"></span></label>
                <input type="hidden" name="precioventabd" id="precioventabd" value="<?php echo $reg[0]['precioventa']; ?>"/>
                <br /><abbr title="Precio de Venta"><?php echo number_format($reg[0]['precioventa'], 2, '.', ','); ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label"><?php echo $impuesto; ?> de Combo: <span class="symbol required"></span></label>
                <br /><abbr title="<?php echo $impuesto; ?> de Combo"><?php echo $reg[0]['ivacombo'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></abbr>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group has-feedback">
                <label class="control-label">Descuento de Combo: <span class="symbol required"></span></label>
                <br /><abbr title="Descuento de Combo"><?php echo number_format($reg[0]['desccombo'], 2, '.', ','); ?></abbr>
            </div>
        </div>
    </div>

<h2 class="card-subtitle m-0 text-dark"><i class="font-22 mdi mdi-cart-plus"></i> Productos Agregados</h2><br>

<table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
          <thead>
          <tr role="row">
          </tr>
          <tr>
            <th>Nº</th>
            <th>Cantidad</th>
            <th>Producto</th>
            <th>Existencia</th>
            <th>Precio Compra</th>
            <th>Precio Venta</th>
            <th><span class="mdi mdi-drag-horizontal"></span></th>
          </tr>
          </thead>
          <tbody>
<?php 
$tru = new Login();
$a=1;
$busq = $tru->VerDetallesProductos();

if($busq==""){

echo "";      

} else {

for($i=0;$i<sizeof($busq);$i++){
?>
  <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
    <td><?php echo $a++; ?></td>
    <td><?php echo $busq[$i]["cantidad"]; ?></td>
    <td><?php echo $busq[$i]["producto"]; ?></td>
    <td><?php echo number_format($busq[$i]["existencia"], 2, '.', ','); ?></td>
    <td><?php echo number_format($busq[$i]["preciocompra"], 2, '.', ','); ?></td>
    <td><?php echo number_format($busq[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><button type="button" class="btn btn-dark btn-rounded" onClick="EliminaDetalleCombo('<?php echo encrypt($busq[$i]['codcombo']); ?>','<?php echo encrypt($busq[$i]['idproducto']); ?>','<?php echo encrypt($busq[$i]['codproducto']); ?>','<?php echo encrypt($busq[$i]['cantidad']); ?>','<?php echo encrypt($busq[$i]['codsucursal']); ?>','<?php echo encrypt("ELIMINADETALLECOMBO"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td>
  </tr><?php } } ?>
  </tbody>
  </table>
<?php 
}
######################## MUESTRA PRODUCTOS AGREGADOS A COMBOS ########################
?>

<?php 
######################## BUSQUEDA DE COMBOS POR MONEDA ########################
if (isset($_GET['BuscaCombosxMoneda']) && isset($_GET['codsucursal']) && isset($_GET['codmoneda'])) { 

  $codsucursal = limpiar($_GET['codsucursal']);
  $codmoneda = limpiar($_GET['codmoneda']);

  if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codmoneda=="") { 

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE TIPO DE MONEDA PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
   } else {

$cambio = new Login();
$cambio = $cambio->BuscarTiposCambios();
  
$reg = $new->ListarCombos();  
?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Combos al Cambio de <?php echo $cambio[0]['moneda']." (".$cambio[0]['siglas'].")"; ?></h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codmoneda=<?php echo $codmoneda; ?>&tipo=<?php echo encrypt("COMBOSXMONEDA"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codmoneda=<?php echo $codmoneda; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("COMBOSXMONEDA"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codmoneda=<?php echo $codmoneda; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("COMBOSXMONEDA"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

              </div>
            </div>
          </div>

          <div id="div3"><table id="datatable-responsive" class="table2 table-hover table-nomargin table-bordered dataTable table-striped" cellspacing="0" width="100%">
                               <thead>
                               <tr role="row">
                                  <th>N°</th>
                                  <th>Código</th>
                                  <th>Nombre de Combo</th>
                                  <th><?php echo $impuesto; ?></th>
                                  <th>Desc %</th>
                                  <th>Detalles de Productos</th> 
                                  <th>Existencia</th>
                                  <th>Precio Venta</th>
                                  <th><?php echo $cambio[0]['siglas']; ?></th>
                               </tr>
                               </thead>
                               <tbody class="BusquedaRapida">

<?php 
if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COMBOS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
$TotalPrecioVenta=0;
$TotalPrecioVentaDescuento=0;
$TotalMoneda=0;
$TotalArticulos=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo  = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
$simbolo2 = "<strong>".$cambio[0]['simbolo']."</strong>";

$Descuento       = $reg[$i]['desccombo']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal     = $reg[$i]['precioventa']-$PrecioDescuento;

$TotalArticulos += $reg[$i]['existencia'];
$TotalPrecioVenta     += $reg[$i]['precioventa'];
$TotalPrecioVentaDescuento  += $PrecioFinal;
$TotalMoneda    += ($cambio == 0 ? "0" : $reg[$i]['precioventa']/$cambio[0]['montocambio']);
?>
    <tr role="row" class="odd">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['codcombo']; ?></td>
      <td><?php echo $reg[$i]['nomcombo']; ?></td>
      <td><?php echo $reg[$i]['ivacombo'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
      <td><?php echo number_format($reg[$i]['desccombo'], 2, '.', ','); ?></td>
      <td class="font-10 bold"><?php echo $reg[$i]['detalles_productos']; ?></td>
      <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo2.number_format($reg[$i]['precioventa']/$cambio[0]['montocambio'], 2, '.', ','); ?></td>
    </tr>
    <?php } ?>
    <tr>
      <td colspan="6"></td>
      <td><label><?php echo number_format($TotalArticulos, 2, '.', ','); ?></label></td>
      <td><label><?php echo $simbolo.number_format($TotalPrecioVenta, 2, '.', ','); ?></label></td>
      <td><label><?php echo $simbolo2.number_format($TotalMoneda, 2, '.', ','); ?></label></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
      </div>

        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
######################## BUSQUEDA DE COMBOS POR MONEDA ##########################
?>


<?php 
######################## BUSQUEDA DE KARDEX POR COMBOS ########################
if (isset($_GET['BuscaKardexCombo']) && isset($_GET['codsucursal']) && isset($_GET['codcombo'])) { 

  $codsucursal = limpiar($_GET['codsucursal']);
  $codcombo = limpiar($_GET['codcombo']); 

  if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcombo=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR REALICE LA BÚSQUEDA DEL COMBO CORRECTAMENTE</center>";
  echo "</div>";
  exit;
   
   } else {
  
$kardex = new Login();
$kardex = $kardex->BuscarKardexCombo(); 

$detalle = new Login();
$detalle = $detalle->DetalleKardexCombo(); 
?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Movimientos del Combo <?php echo $detalle[0]['codcombo'].": ".$detalle[0]['nomcombo']; ?></h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcombo=<?php echo $codcombo; ?>&tipo=<?php echo encrypt("KARDEXCOMBOS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcombo=<?php echo $codcombo; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("KARDEXCOMBOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcombo=<?php echo $codcombo; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("KARDEXCOMBOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

              </div>
            </div>
          </div>

          <div id="div3"><table id="datatable-scroller" class="table table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                              <thead>
                              <tr>
                                  <th>Nº</th>
                                  <th>Movimiento</th>
                                  <th>Entradas</th>
                                  <th>Salidas</th>
                                  <th>Devolución</th>
                                  <th>Precio Costo</th>
                                  <th>Costo Movimiento</th>
                                  <th>Stock Actual</th>
                                  <th>Documento</th>
                                  <th>Fecha de Kardex</th>
                              </tr>
                              </thead>
                              <tbody>
<?php
$TotalEntradas=0;
$TotalSalidas=0;
$TotalDevolucion=0;
$a=1;
for($i=0;$i<sizeof($kardex);$i++){ 
$simbolo = ($detalle[0]['simbolo'] == "" ? "" : "<strong>".$detalle[0]['simbolo']."</strong>");

$TotalEntradas+=$kardex[$i]['entradas'];
$TotalSalidas+=$kardex[$i]['salidas'];
$TotalDevolucion+=$kardex[$i]['devolucion'];
?>
          <tr>
            <td><?php echo $a++; ?></td>
            <td><?php echo $kardex[$i]['movimiento']; ?></td>
            <td><?php echo number_format($kardex[$i]['entradas'], 2, '.', ','); ?></td>
            <td><?php echo number_format($kardex[$i]['salidas'], 2, '.', ','); ?></td>
            <td><?php echo number_format($kardex[$i]['devolucion'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($kardex[$i]['precio'], 2, '.', ','); ?></td>
            <?php if($kardex[$i]["movimiento"]=="ENTRADAS"){ ?>
              <td><?php echo number_format($kardex[$i]['precio']*$kardex[$i]['entradas'], 2, '.', ','); ?></td>
            <?php } elseif($kardex[$i]["movimiento"]=="SALIDAS"){ ?>
              <td><?php echo number_format($kardex[$i]['precio']*$kardex[$i]['salidas'], 2, '.', ','); ?></td>
            <?php } else { ?>
              <td><?php echo number_format($kardex[$i]['precio']*$kardex[$i]['devolucion'], 2, '.', ','); ?></td>
            <?php } ?>
            <td><?php echo number_format($kardex[$i]['stockactual'], 2, '.', ','); ?></td>
            <td><?php echo $kardex[$i]['documento']." ".$num = ($kardex[$i]['documento'] == 'VENTA' || $kardex[$i]['documento'] == 'DEVOLUCION' ? $kardex[$i]['codproceso'] : ""); ?></td>
            <td><?php echo date("d-m-Y",strtotime($kardex[$i]['fechakardex'])); ?></td>
          </tr>
          <?php } ?>
          </tbody>
          </table>
                        
          <label>Detalles de Combo</label><br>
          <label>Código:</label> <?php echo $detalle[0]['codcombo']; ?><br>
          <label>Descripción:</label> <?php echo $detalle[0]['nomcombo']; ?><br>
          <label>Total Entradas:</label> <?php echo number_format($TotalEntradas, 2, '.', ','); ?><br>
          <label>Total Salidas:</label> <?php echo number_format($TotalSalidas, 2, '.', ','); ?><br>
          <label>Total Devolución:</label> <?php echo number_format($TotalDevolucion, 2, '.', ','); ?><br>
          <label>Existencia:</label> <?php echo number_format($detalle[0]['existencia'], 2, '.', ','); ?><br>
          <label>Precio Compra:</label> <?php echo $simbolo.number_format($detalle[0]['preciocompra'], 2, '.', ','); ?><br>
          <label>Precio Venta:</label> <?php echo $simbolo.number_format($detalle[0]['precioventa'], 2, '.', ','); ?>
            </div>

        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
######################## BUSQUEDA DE KARDEX POR COMBOS ########################
?>


<?php 
########################### BUSQUEDA KARDEX COMBOS VALORIZADO POR FECHAS ##########################
if (isset($_GET['BuscaKardexCombosxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
  if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DESDE PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA HASTA PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DESDE NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarKardexCombosValorizadoxFechas();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Kardex de Combos Valorizado por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("KARDEXCOMBOSVALORIZADOXFECHAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("KARDEXCOMBOSVALORIZADOXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("KARDEXCOMBOSVALORIZADOXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr role="row">
                              <th>Nº</th>
                              <th>Código</th>
                              <th>Descripción de Combo</th>
                              <th>Desc %</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Precio de Venta</th>
                              <th>Vendido</th>
                              <th>Total Venta</th>
                              <th>Total Compra</th>
                              <th>Ganancias</th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$PrecioCompraTotal=0;
$PrecioVentaTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$ImpuestosCompraTotal=0;
$ImpuestosVentaTotal=0;
$CompraTotal=0;
$VentaTotal=0;
$TotalGanancia=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioCompraTotal+=$reg[$i]['preciocompra'];
$PrecioVentaTotal+=$reg[$i]['precioventa'];
$ExisteTotal+=$reg[$i]['existencia'];
$VendidosTotal+=$reg[$i]['cantidad'];

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

//VALOR DE IMPUESTO
$ValorImpuesto = 1 + ($valor/100);

//CALCULO SUBTOTAL IMPUESTOS PRECIO COMPRA
$DiscriminadoC         = $reg[$i]['preciocompra']/$ValorImpuesto;
$SubtotalDiscriminadoC = $reg[$i]['preciocompra'] - $DiscriminadoC;
$BaseDiscriminadoC     = $SubtotalDiscriminadoC * $reg[$i]['cantidad'];
$SubtotalimpuestosC    = ($reg[$i]['ivaproducto'] != '0.00' ? number_format($BaseDiscriminadoC, 2, '.', '') : "0.00");

//CALCULO SUBTOTAL IMPUESTOS PRECIO VENTA
$DiscriminadoV         = $PrecioFinal/$ValorImpuesto;
$SubtotalDiscriminadoV = $PrecioFinal - $DiscriminadoV;
$BaseDiscriminadoV     = $SubtotalDiscriminadoV * $reg[$i]['cantidad'];
$SubtotalimpuestosV    = ($reg[$i]['ivaproducto'] != '0.00' ? number_format($BaseDiscriminadoV, 2, '.', '') : "0.00");

$SumCompra = ($reg[$i]['preciocompra']*$reg[$i]['cantidad'])-$SubtotalimpuestosC;
$SumVenta  = ($PrecioFinal*$reg[$i]['cantidad'])-$SubtotalimpuestosV; 

$CompraTotal          += $SumCompra;
$ImpuestosCompraTotal += $SubtotalimpuestosC;
$VentaTotal           += $SumVenta;
$ImpuestosVentaTotal  += $SubtotalimpuestosV;
$TotalGanancia        += $SumVenta-$SumCompra;
?>
            <tr role="row" class="odd">
            <td><?php echo $a++; ?></div></td>
            <td><?php echo $reg[$i]['codproducto']; ?></td>
            <td><?php echo $reg[$i]['producto']; ?></td>
            <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
            <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
            <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
            </tr>
            <?php  }  ?>
            <tr>
              <td colspan="6"></td>
              <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></label></td>
            </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA KARDEX PRODUCTOS VALORIZADO POR FECHAS ##########################
?>

<?php 
######################## BUSQUEDA DE COMBOS VENDIDOS ########################
if (isset($_GET['BuscaCombosVendidos']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarCombosVendidos();  
?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Combos Vendidos por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("COMBOSVENDIDOS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("COMBOSVENDIDOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("COMBOSVENDIDOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

          <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                              <thead>
                                <tr>
                                  <th>Nº</th>
                                  <th>Código</th>
                                  <th>Descripción de Combo</th>
                                  <th>Precio de Venta</th>
                                  <th>Existencia</th>
                                  <th>Vendido</th>
                                  <th><?php echo $impuesto; ?></th>
                                  <th>Desc %</th>
                                  <th>Monto Total</th>
                                </tr>
                              </thead>
                              <tbody>
<?php
$a=1;
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$TotalGeneral=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal   += $reg[$i]['precioventa'];
$ExisteTotal   += $reg[$i]['existencia'];
$VendidosTotal += $reg[$i]['cantidad'];

$Descuento        = $reg[$i]['descproducto']/100;
$PrecioDescuento  = $reg[$i]['precioventa']*$Descuento;
//$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$CalculoDescuento = number_format($reg[$i]['totaldescuentov'], 2, '.', '');
$PrecioFinal      = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg              = $reg[$i]['ivaproducto']/100;
$CalculoImpuesto  = number_format($reg[$i]['subtotalimpuestos'], 2, '.', '');

$TotalDescuento += $CalculoDescuento; 
$TotalImpuesto  += $CalculoImpuesto; 
$TotalGeneral   += $PrecioFinal*$reg[$i]['cantidad']; 
?>
        <tr>
          <td><?php echo $a++; ?></div></td>
          <td><?php echo $reg[$i]['codproducto']; ?></td>
          <td><?php echo $reg[$i]['producto']; ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
          <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
          <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
          <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
          <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
        </tr>
        <?php } ?>
        <tr>
          <td colspan="3"></td>
          <td><label><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></label></td>
          <td><label><?php echo number_format($ExisteTotal, 2, '.', ','); ?></label></td>
          <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
          <td><label><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></label></td>
          <td><label><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></label></td>
          <td><label><?php echo $simbolo.number_format($TotalGeneral, 2, '.', ','); ?></label></td>
        </tr>
        </tbody>
        </table>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
######################## BUSQUEDA DE COMBOS VENDIDOS ########################
?>


















<?php
######################### MOSTRAR COMPRA PAGADA EN VENTANA MODAL ########################
if (isset($_GET['BuscaCompraPagadaModal']) && isset($_GET['codcompra']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->ComprasPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

if($reg==""){
    
  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COMPRAS Y DETALLES ACTUALMENTE </center>";
  echo "</div>";    

} else {
?>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left">
                                    <address>
  <h4><b class="text-danger">RAZÓN SOCIAL</b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomsucursal']; ?>,
  <br/> Nº <?php echo $reg[0]['documsucursal'] == '0' ? "DOCUMENTO" : $reg[0]['documento']; ?>: <?php echo $reg[0]['cuitsucursal']; ?> - TLF: <?php echo $reg[0]['tlfsucursal']; ?></p>

  <h4><b class="text-danger">Nº COMPRA <?php echo $reg[0]['codfactura']; ?></b></h4>
  <p class="text-muted m-l-5">ESTADO: 
  <?php if($reg[0]["statuscompra"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[0]["statuscompra"]."</span>"; } 
  elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado'] == "0000-00-00" && $reg[0]['statuscompra'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[0]["statuscompra"]."</span>"; } ?>

  <?php if($reg[0]['fechavencecredito']!= "0000-00-00") { ?>
  <br>DIAS VENCIDOS: 
  <?php if($reg[0]['fechavencecredito']== '0000-00-00') { echo "0"; } 
  elseif($reg[0]['fechavencecredito'] >= date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo "0"; } 
  elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[0]['fechavencecredito']); }
  elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[0]['fechapagado'],$reg[0]['fechavencecredito']); } ?>
  <?php } ?>
  
  <?php if($reg[0]['fechapagado']!= "0000-00-00") { ?>
  <br>FECHA PAGADA: <?php echo date("d-m-Y",strtotime($reg[0]['fechapagado'])); ?>
  <?php } ?>

  <br>FECHA DE EMISIÓN: <?php echo date("d-m-Y",strtotime($reg[0]['fechaemision'])); ?>
  <br/> FECHA DE RECEPCIÓN: <?php echo date("d-m-Y",strtotime($reg[0]['fecharecepcion'])); ?></p>
                                    </address>
                                </div>

                                <div class="pull-right text-right">
                                    <address>
  <h4><b class="text-danger">PROVEEDOR</b></h4>
  <p class="text-muted m-l-30"><?php echo $reg[0]['nomproveedor'] == '' ? "******" : $reg[0]['nomproveedor']; ?>,
  <br/>DIREC: <?php echo $reg[0]['direcproveedor'] == '' ? "******" : $reg[0]['direcproveedor']; ?> <?php echo $reg[0]['provincia2'] == '' ? "******" : $reg[0]['provincia2']; ?> <?php echo $reg[0]['departamento2'] == '' ? "******" : $reg[0]['departamento2']; ?>
  <br/> EMAIL: <?php echo $reg[0]['emailproveedor'] == '' ? "******" : $reg[0]['emailproveedor']; ?>
  <br/> Nº <?php echo $reg[0]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: <?php echo $reg[0]['cuitproveedor'] == '' ? "******" : $reg[0]['cuitproveedor']; ?> - TLF: <?php echo $reg[0]['tlfproveedor'] == '' ? "******" : $reg[0]['tlfproveedor']; ?>
  <br/> VENDEDOR: <?php echo $reg[0]['vendedor']; ?> - TLF: <?php echo $reg[0]['tlfvendedor'] == '' ? "******" : $reg[0]['tlfvendedor']; ?></p>
                                            
                                    </address>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="table-responsive m-t-10" style="clear: both;">
                                    <div id="div1"><table class="table2 table-hover">
                                        <thead>
                                            <tr>
                        <th>#</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
                        <?php if ($_SESSION['acceso'] == "administradorS") { ?>
                        <th><span class="mdi mdi-drag-horizontal"></span></th>
                        <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesCompras();

$SubTotal = 0;
$a=1;
for($i=0;$i<sizeof($detalle);$i++){  
$SubTotal += $detalle[$i]['valorneto']; 
?>
  <tr>
  <td><label><?php echo $a++; ?></label></td>
  <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
  <small class="text-danger alert-link"><?php echo $detalle[$i]['tipo'] == 1 ? $detalle[$i]['nomcategoria'] : $detalle[$i]['nommedida']; ?></small></td>
  <td><?php echo $detalle[$i]['cantcompra']; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['preciocomprac'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.$detalle[$i]['totaldescuentoc']; ?><sup><strong><?php echo $detalle[$i]['descfactura']; ?>%</strong></sup></td>
  <td><?php echo $detalle[$i]['ivaproductoc'] != '0.00' ? number_format($detalle[$i]['ivaproductoc'], 2, '.', '')."%" : "(E)"; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></td>
  <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
  <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDetallesComprasPagadasModal('<?php echo encrypt($detalle[$i]["coddetallecompra"]); ?>','<?php echo encrypt($detalle[$i]["codcompra"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[0]["codproveedor"]); ?>','<?php echo encrypt("DETALLESCOMPRAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                          </tr>
                                      <?php } ?>
                                      </tbody>
                                  </table></div>
                              </div>
                          </div>


                          <div class="col-md-12">
                              <div class="pull-right text-right">
<p><b>Subtotal:</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasic']+$reg[0]['subtotalivanoc'], 2, '.', ','); ?></p>
<p><b>Total Grabado (<?php echo number_format($reg[0]['ivac'], 2, '.', ''); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasic'], 2, '.', ','); ?></p>
<p><b>Total Exento (0%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivanoc'], 2, '.', ','); ?></p>
<p><b>Total <?php echo $impuesto; ?> (<?php echo number_format($reg[0]['ivac'], 2, '.', ''); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totalivac'], 2, '.', ','); ?> </p>
<p><b>Desc. Global (<?php echo $reg[0]['descuentoc']; ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaldescuentoc'], 2, '.', ','); ?> </p>
                                        <hr>
<h4><b>Importe Total :</b> <?php echo $simbolo.number_format($reg[0]['totalpagoc'], 2, '.', ','); ?></h4></div>
                                    <div class="clearfix"></div>
                                    <hr>
                                <div class="col-md-12">
                                    <div class="text-right">
 <a href="reportepdf?codcompra=<?php echo encrypt($reg[0]['codcompra']); ?>&codsucursal=<?php echo encrypt($reg[0]['codsucursal']); ?>&tipo=<?php echo encrypt("FACTURACOMPRA"); ?>" target="_blank" rel="noopener noreferrer"><button id="print" class="btn waves-light btn-light" type="button"><span><i class="fa fa-print"></i> Imprimir</span> </button></a>
 <button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
                                    </div>
                                </div>
                            </div>
                <!-- .row -->

  <?php
       }
   } 
######################### MOSTRAR COMPRA PAGADA EN VENTANA MODAL ########################
?>

<?php
####################### MOSTRAR COMPRA PENDIENTE EN VENTANA MODAL #######################
if (isset($_GET['BuscaCompraPendienteModal']) && isset($_GET['codcompra']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->ComprasPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

  if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COMPRAS Y DETALLES ACTUALMENTE </center>";
    echo "</div>";    

} else {
?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pull-left">
                                        <address>
  <h4><b class="text-danger">RAZÓN SOCIAL</b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomsucursal']; ?>,
  <br/> Nº <?php echo $reg[0]['documsucursal'] == '0' ? "DOCUMENTO" : $reg[0]['documento']; ?>: <?php echo $reg[0]['cuitsucursal']; ?> - TLF: <?php echo $reg[0]['tlfsucursal']; ?></p>

  <h4><b class="text-danger">Nº COMPRA <?php echo $reg[0]['codfactura']; ?></b></h4>
  <p class="text-muted m-l-5">ESTADO: 
  <?php if($reg[0]["statuscompra"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[0]["statuscompra"]."</span>"; } 
      elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado'] == "0000-00-00" && $reg[0]['statuscompra'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
      else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[0]["statuscompra"]."</span>"; } ?>

  <?php if($reg[0]['fechavencecredito']!= "0000-00-00") { ?>
  <br>DIAS VENCIDOS: 
  <?php if($reg[0]['fechavencecredito']== '0000-00-00') { echo "0"; } 
        elseif($reg[0]['fechavencecredito'] >= date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo "0"; } 
        elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[0]['fechavencecredito']); }
        elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[0]['fechapagado'],$reg[0]['fechavencecredito']); } ?>
  <?php } ?>
  
  <?php if($reg[0]['fechapagado']!= "0000-00-00") { ?>
  <br>FECHA PAGADA: <?php echo date("d-m-Y",strtotime($reg[0]['fechapagado'])); ?>
  <?php } ?>

  <br>FECHA DE EMISIÓN: <?php echo date("d-m-Y",strtotime($reg[0]['fechaemision'])); ?>
  <br/> FECHA DE RECEPCIÓN: <?php echo date("d-m-Y",strtotime($reg[0]['fecharecepcion'])); ?></p>
                                        </address>
                                    </div>
                                    <div class="pull-right text-right">
                                        <address>
  <h4><b class="text-danger">PROVEEDOR</b></h4>
  <p class="text-muted m-l-30"><?php echo $reg[0]['nomproveedor'] == '' ? "******" : $reg[0]['nomproveedor']; ?>,
  <br/>DIREC: <?php echo $reg[0]['direcproveedor'] == '' ? "******" : $reg[0]['direcproveedor']; ?> <?php echo $reg[0]['provincia2'] == '' ? "******" : $reg[0]['provincia2']; ?> <?php echo $reg[0]['departamento2'] == '' ? "******" : $reg[0]['departamento2']; ?>
  <br/> EMAIL: <?php echo $reg[0]['emailproveedor'] == '' ? "******" : $reg[0]['emailproveedor']; ?>
  <br/> Nº <?php echo $reg[0]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: <?php echo $reg[0]['cuitproveedor'] == '' ? "******" : $reg[0]['cuitproveedor']; ?> - TLF: <?php echo $reg[0]['tlfproveedor'] == '' ? "******" : $reg[0]['tlfproveedor']; ?>
  <br/> VENDEDOR: <?php echo $reg[0]['vendedor']; ?> - TLF: <?php echo $reg[0]['tlfvendedor'] == '' ? "******" : $reg[0]['tlfvendedor']; ?></p>
                                            
                                        </address>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="table-responsive m-t-10" style="clear: both;">
                                        <div id="div1"><table class="table2 table-hover">
                                            <thead>
                                              <tr>
                        <th>#</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
<?php if ($_SESSION['acceso'] == "administradorS") { ?><th><span class="mdi mdi-drag-horizontal"></span></th><?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesCompras();

$SubTotal = 0;
$a=1;
for($i=0;$i<sizeof($detalle);$i++){  
$SubTotal += $detalle[$i]['valorneto'];
?>
                                                <tr>
      <td><label><?php echo $a++; ?></label></td>
      <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
      <small class="text-danger alert-link"><?php echo $detalle[$i]['tipo'] == 1 ? $detalle[$i]['nomcategoria'] : $detalle[$i]['nommedida']; ?></small></td>
      <td><?php echo $detalle[$i]['cantcompra']; ?></td>
      <td><?php echo $simbolo.number_format($detalle[$i]['preciocomprac'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.$detalle[$i]['totaldescuentoc']; ?><sup><strong><?php echo $detalle[$i]['descfactura']; ?>%</strong></sup></td>
      <td><?php echo $detalle[$i]['ivaproductoc'] != '0.00' ? number_format($detalle[$i]['ivaproductoc'], 2, '.', '')."%" : "(E)"; ?></td>
      <td><?php echo $simbolo.number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></td>
 <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
<button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDetallesComprasPendientesModal('<?php echo encrypt($detalle[$i]["coddetallecompra"]); ?>','<?php echo encrypt($detalle[$i]["codcompra"]); ?>','<?php echo encrypt($reg[0]["codproveedor"]); ?>','<?php echo encrypt("DETALLESCOMPRAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                                </tr>
                                      <?php } ?>
                                            </tbody>
                                        </table></div>
                                    </div>
                                </div>


                                <div class="col-md-12">

                                    <div class="pull-right text-right">
<p><b>Subtotal:</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasic']+$reg[0]['subtotalivanoc'], 2, '.', ','); ?></p>
<p><b>Total Grabado (<?php echo number_format($reg[0]['ivac'], 2, '.', ''); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasic'], 2, '.', ','); ?></p>
<p><b>Total Exento (0%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivanoc'], 2, '.', ','); ?></p>
<p><b>Total <?php echo $impuesto; ?> (<?php echo number_format($reg[0]['ivac'], 2, '.', ''); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totalivac'], 2, '.', ','); ?> </p>
<p><b>Descontado %:</b> <?php echo $simbolo.number_format($reg[0]['descontadoc'], 2, '.', ','); ?> </p>
<p><b>Desc. Global (<?php echo $reg[0]['descuentoc']; ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaldescuentoc'], 2, '.', ','); ?> </p>
                                        <hr>
<h4><b>Importe Total :</b> <?php echo $simbolo.number_format($reg[0]['totalpagoc'], 2, '.', ','); ?></h4></div>
                                    <div class="clearfix"></div>
                                    <hr>

                                <div class="col-md-12">
                                    <div class="text-right">
 <a href="reportepdf?codcompra=<?php echo encrypt($reg[0]['codcompra']); ?>&codsucursal=<?php echo encrypt($reg[0]['codsucursal']); ?>&tipo=<?php echo encrypt("FACTURACOMPRA"); ?>" target="_blank" rel="noopener noreferrer"><button id="print" class="btn waves-light btn-light" type="button"><span><i class="fa fa-print"></i> Imprimir</span></button></a>
 <button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
                                    </div>
                                </div>
                            </div>
                <!-- .row -->

  <?php
       }
   } 
###################### MOSTRAR COMPRA PENDIENTE EN VENTANA MODAL #######################
?>


<?php
######################## MOSTRAR DETALLES DE COMPRAS UPDATE ############################
if (isset($_GET['MuestraDetallesComprasUpdate']) && isset($_GET['codcompra']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->ComprasPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

?>

<div class="table-responsive m-t-20">
            <table class="table2 table-hover">
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
<?php if ($_SESSION['acceso'] == "administradorS") { ?><th>Acción</th><?php } ?>
                    </tr>
                </thead>
                <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesCompras();
$a=1;
$count = 0;
for($i=0;$i<sizeof($detalle);$i++){ 
$count++; 
?>
    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
    <td>
    <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
    <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetalleCompra('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
    <input type="text" class="bold" name="cantcompra[]" id="cantcompra_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoCompra(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="<?php echo number_format($detalle[$i]["cantcompra"], 2, '.', ''); ?>" title="Ingrese Cantidad">
    <input type="hidden" name="cantidadcomprabd[]" id="cantidadcomprabd_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["cantcompra"], 2, '.', ''); ?>">
    <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetalleCompra('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
    </div>
    </td>

    <td class="text-danger alert-link">
    <input type="hidden" name="coddetallecompra[]" id="coddetallecompra" value="<?php echo $detalle[$i]["coddetallecompra"]; ?>">
    <input type="hidden" name="tipo[]" id="tipo" value="<?php echo $detalle[$i]["tipo"]; ?>">
    <input type="hidden" name="codproducto[]" id="codproducto" value="<?php echo $detalle[$i]["codproducto"]; ?>">
    <input type="hidden" name="precioventa[]" id="precioventa" value="<?php echo number_format($detalle[$i]["precioventac"], 2, '.', ''); ?>">
    <?php if($detalle[$i]['tipo'] == 1){
        echo "PRODUCTO";
    } elseif($detalle[$i]['tipo'] == 2){
        echo "INGREDIENTE";
    } ?></td>

    <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
    <small class="text-danger alert-link"><?php echo $detalle[$i]['tipo'] == 1 ? $detalle[$i]['nomcategoria'] : $detalle[$i]['nommedida']; ?></small></label></td>
            
    <td><strong><input type="hidden" name="preciocompra[]" id="preciocompra_<?php echo $count; ?>" value="<?php echo $detalle[$i]["preciocomprac"]; ?>">
    <input type="hidden" name="precioconiva[]" id="precioconiva_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproductoc'] == '0.00' ? "0.00" : number_format($detalle[$i]["preciocomprac"], 2, '.', ''); ?>"><?php echo number_format($detalle[$i]['preciocomprac'], 2, '.', ','); ?></strong></td>

    <td><input type="hidden" name="valortotal[]" id="valortotal_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["valortotal"], 2, '.', ''); ?>"><label id="txtvalortotal_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></label></td>

    <td>
    <input type="hidden" name="descfactura[]" id="descfactura_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["descfactura"], 2, '.', ''); ?>">
    <input type="hidden" class="totaldescuentoc" name="totaldescuentoc[]" id="totaldescuentoc_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["totaldescuentoc"], 2, '.', ''); ?>">
    <label id="txtdescproducto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['totaldescuentoc'], 2, '.', ','); ?></label><sup><label><?php echo number_format($detalle[$i]['descfactura'], 2, '.', ','); ?>%</label></sup></td>

    <td><input type="hidden" name="ivaproducto[]" id="ivaproducto_<?php echo $count; ?>" value="<?php echo $detalle[$i]["ivaproductoc"]; ?>"><label><?php echo $detalle[$i]['ivaproductoc'] != '0.00' ? number_format($detalle[$i]['ivaproductoc'], 2, '.', '')."%" : "(E)"; ?></label></td>

    <td><input type="hidden" class="subtotalivasi" name="subtotalivasi[]" id="subtotalivasi_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproductoc'] != '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalivano" name="subtotalivano[]" id="subtotalivano_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproductoc'] == '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalimpuestos" name="subtotalimpuestos[]" id="subtotalimpuestos_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['subtotalimpuestos'], 2, '.', ''); ?>">

    <input type="hidden" class="subtotaldiscriminado" name="subtotaldiscriminado[]" id="subtotaldiscriminado_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto']-$detalle[$i]['subtotalimpuestos'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="valorneto" name="valorneto[]" id="valorneto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto'], 2, '.', ''); ?>" ><label id="txtvalorneto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></label></td>

 <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
<button type="button" class="btn btn-rounded btn-dark" onClick="EliminarDetallesComprasUpdate('<?php echo encrypt($detalle[$i]["coddetallecompra"]); ?>','<?php echo encrypt($detalle[$i]["codcompra"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[0]["codproveedor"]); ?>','<?php echo encrypt("DETALLESCOMPRAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                 </tr>
                     <?php } ?>
                </tbody>
            </table><hr>

             <table id="carritototal" class="table-responsive">
            <tr>
    <td width="250"><h5><label>Gravado (<?php echo number_format($reg[0]['ivac'], 2, '.', '') ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal"><?php echo number_format($reg[0]['subtotalivasic'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdiscriminado" id="txtdiscriminado" value="<?php echo number_format($reg[0]['subtotalivasic'], 0, '.', ''); ?>"/>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="<?php echo number_format($reg[0]['subtotalivasic'], 2, '.', ''); ?>"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    
    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2"><?php echo number_format($reg[0]['subtotalivanoc'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="<?php echo number_format($reg[0]['subtotalivanoc'], 2, '.', ''); ?>"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['ivac'], 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo number_format($reg[0]['ivac'], 2, '.', ''); ?>"></label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva"><?php echo number_format($reg[0]['totalivac'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtIva" id="txtIva" value="<?php echo number_format($reg[0]['totalivac'], 2, '.', ''); ?>"/>
    </td>
    </tr>
    <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado"><?php echo number_format($reg[0]['descontadoc'], 2, '.', ''); ?></label></h5>
    <input type="hidden" name="txtdescontado" id="txtdescontado" value="<?php echo number_format($reg[0]['descontadoc'], 2, '.', ''); ?>"/>
        </td>
    
    <td>
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" style="border-radius:4px;height:30px;width:60px;" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['descuentoc'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento"><?php echo number_format($reg[0]['totaldescuentoc'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="<?php echo number_format($reg[0]['totaldescuentoc'], 2, '.', ''); ?>"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal"><?php echo number_format($reg[0]['totalpagoc'], 2, '.', ','); ?></label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="<?php echo number_format($reg[0]['totalpagoc'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtTotal2" id="txtTotal2" value="<?php echo number_format($reg[0]['totalpagoc'], 2, '.', ''); ?>"/>
    </td>
                    </tr>
    </table>

  </div>
<?php
  } 
######################## MOSTRAR DETALLES DE COMPRAS UPDATE ########################
?>

<?php
########################## BUSQUEDA COMPRAS POR FECHAS ##########################
if (isset($_GET['BuscaComprasxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarComprasxFechas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Compras de Productos por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("COMPRASXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("COMPRASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("COMPRASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

  <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                        <th>Nº</th>
                        <th>N° de Compra</th>
                        <th>Descripción de Proveedor</th>
                        <th>Estado</th>
                        <th>Dias Venc.</th>
                        <th>Fecha de Emisión</th>
                        <th>Fecha de Recepción</th>
                        <th>Nº de Articulos</th>
                        <th>Subtotal</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <th>Imp. Total</th>
                        <th><i class="mdi mdi-drag-horizontal"></i></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'];
$TotalImpuesto+=$reg[$i]['totalivac'];
$TotalDescuento+=$reg[$i]['totaldescuentoc'];
$TotalImporte+=$reg[$i]['totalpagoc'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><abbr title="<?php echo "Nº ".$documento = ($reg[$i]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['cuitproveedor']; ?>"><?php echo $reg[$i]['nomproveedor']; ?></abbr></td>
  <td><?php if($reg[$i]["statuscompra"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statuscompra"]."</span>"; } 
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statuscompra'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statuscompra"]."</span>"; } ?>
        
  </td>
  <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
  <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaemision'])); ?></td>
  <td><?php echo date("d-m-Y",strtotime($reg[$i]['fecharecepcion'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
  <td>
  <a href="reportepdf?codcompra=<?php echo encrypt($reg[$i]['codcompra']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt("FACTURACOMPRA"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
        </tr>
        <?php } ?>
        <tr>
        <td colspan="7"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
         </tr>
            </tbody>
          </table>
         </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  }
} 
########################## BUSQUEDA COMPRAS POR FECHAS ########################
?>

<?php
######################## BUSQUEDA COMPRAS POR PROVEEDORES ########################
if (isset($_GET['BuscaComprasxProvedores']) && isset($_GET['codsucursal']) && isset($_GET['codproveedor'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codproveedor = limpiar($_GET['codproveedor']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codproveedor=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE PROVEEDOR PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else {

$pre = new Login();
$reg = $pre->BuscarComprasxProveedor();
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Compras por Proveedor</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codproveedor=<?php echo $codproveedor; ?>&tipo=<?php echo encrypt("COMPRASXPROVEEDOR"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codproveedor=<?php echo $codproveedor; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("COMPRASXPROVEEDOR"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codproveedor=<?php echo $codproveedor; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("COMPRASXPROVEEDOR"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de <?php echo $reg[0]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: </label> <?php echo $reg[0]['cuitproveedor']; ?><br>

            <label class="control-label">Nombre de Proveedor: </label> <?php echo $reg[0]['nomproveedor']; ?><br>

            <label class="control-label">Nombre de Vendedor: </label> <?php echo $reg[0]['vendedor']; ?>
        </div>
      </div>

  <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                        <th>Nº</th>
                        <th>N° de Compra</th>
                        <th>Descripción de Proveedor</th>
                        <th>Estado</th>
                        <th>Dias Venc.</th>
                        <th>Fecha de Emisión</th>
                        <th>Fecha de Recepción</th>
                        <th>Nº de Articulos</th>
                        <th>Subtotal</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <th>Imp. Total</th>
                        <th><i class="mdi mdi-drag-horizontal"></i></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'];
$TotalImpuesto+=$reg[$i]['totalivac'];
$TotalDescuento+=$reg[$i]['totaldescuentoc'];
$TotalImporte+=$reg[$i]['totalpagoc'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><abbr title="<?php echo "Nº ".$documento = ($reg[$i]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['cuitproveedor']; ?>"><?php echo $reg[$i]['nomproveedor']; ?></abbr></td>
  <td><?php if($reg[$i]["statuscompra"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statuscompra"]."</span>"; } 
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statuscompra'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statuscompra"]."</span>"; } ?>
  </td>
  <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
  <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaemision'])); ?></td>
  <td><?php echo date("d-m-Y",strtotime($reg[$i]['fecharecepcion'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
  <td>
  <a href="reportepdf?codcompra=<?php echo encrypt($reg[$i]['codcompra']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt("FACTURACOMPRA"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
        </tr>
        <?php } ?>
        <tr>
        <td colspan="7"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
         </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  }
} 
########################## BUSQUEDA COMPRAS POR PROVEEDORES ##########################
?>




























<?php
######################## MOSTRAR TRASPASO EN VENTANA MODAL ########################
if (isset($_GET['BuscaTraspasoModal']) && isset($_GET['codtraspaso']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->TraspasosPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

if($reg==""){
    
  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON DETALLES ACTUALMENTE </center>";
  echo "</div>";    

} else {
?>
          <div class="row">
              <div class="col-md-12">
                <div class="pull-left">
                    <address>
  <h4><b class="text-danger">SUCURSAL ENVIA</b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomsucursal']; ?>
  <br/>DIREC: <?php echo $reg[0]['id_provincia'] == '0' ? "" : $reg[0]['provincia']; ?> <?php echo $reg[0]['id_departamento'] == '' ? "" : $reg[0]['departamento']; ?> <?php echo $reg[0]['direcsucursal'] == '' ? "******" : $reg[0]['direcsucursal']; ?>
  <br/> EMAIL: <?php echo $reg[0]['correosucursal'] == '' ? "**********" : $reg[0]['correosucursal']; ?>
  <br/> Nº <?php echo $reg[0]['documsucursal'] == '0' ? "DOCUMENTO" : $reg[0]['documento'] ?>: <?php echo $reg[0]['cuitsucursal']; ?> - TLF: <?php echo $reg[0]['tlfsucursal']; ?>
  <br/> <?php echo $reg[0]['nomencargado']; ?></p>

  <h4><b class="text-danger">TRASPASO Nº <?php echo $reg[0]['codfactura']; ?></b></h4>
  <p class="text-muted m-l-5">FECHA: <?php echo date("d-m-Y H:i:s",strtotime($reg[0]['fechatraspaso'])); ?>
  <br/> OBSERVACIONES: <?php echo $reg[0]['observaciones'] == "" ? "**********" : $reg[0]['observaciones']; ?></p>
                    </address>
                </div>

                <div class="pull-right text-right">
                  <address>
  <h4><b class="text-danger">SUCURSAL RECIBE</b></h4>
  <p class="text-muted m-l-30"><?php echo $reg[0]['nomsucursal2']; ?>
  <br/>DIREC: <?php echo $reg[0]['id_provincia2'] == '0' ? "" : $reg[0]['provincia2']; ?> <?php echo $reg[0]['id_departamento2'] == '' ? "" : $reg[0]['departamento2']; ?> <?php echo $reg[0]['direcsucursal2'] == '' ? "******" : $reg[0]['direcsucursal2']; ?>
  <br/> EMAIL: <?php echo $reg[0]['correosucursal2'] == '' ? "**********" : $reg[0]['correosucursal2']; ?>
  <br/> Nº <?php echo $reg[0]['documsucursal2'] == '0' ? "DOCUMENTO" : $reg[0]['documento2'] ?>: <?php echo $reg[0]['cuitsucursal2']; ?> - TLF: <?php echo $reg[0]['tlfsucursal2']; ?>
  <br/> <?php echo $reg[0]['nomencargado2']; ?></p>

                    </address>
                  </div>
              </div>
              
              <div class="col-md-12">
                <div class="table-responsive m-t-10" style="clear: both;">
                  <div id="div1"><table class="table2 table-hover">
                      <thead>
                      <tr>
                        <th>#</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
                        <?php if ($_SESSION['acceso'] == "administradorS") { ?>
                        <th>Acción</th>
                        <?php } ?>
                      </tr>
                      </thead>
                      <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesTraspasos();

$SubTotal = 0;
$a=1;
for($i=0;$i<sizeof($detalle);$i++){  
$SubTotal += $detalle[$i]['valorneto']; 
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td class="text-left"><label><h5><?php echo $detalle[$i]['producto']; ?></h5>
  <small class="text-danger alert-link"><?php echo $detalle[$i]['tipo'] == 1 ? $detalle[$i]['nomcategoria'] : $detalle[$i]['nommedida']; ?></small></label></td>
  <td><?php echo $detalle[$i]['cantidad']; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?><sup><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></td>
  <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
  <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDetalleTraspasoModal('<?php echo encrypt($detalle[$i]["coddetalletraspaso"]); ?>','<?php echo encrypt($detalle[$i]["codtraspaso"]); ?>','<?php echo encrypt($reg[0]["recibe"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt("DETALLESTRASPASOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                                </tr>
                                      <?php } ?>
                                            </tbody>
                                        </table></div>
                                    </div>
                                </div>

                                <div class="col-md-12">

                                    <div class="pull-right text-right">
<p><b>Subtotal:</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi']+$reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Total Grabado (<?php echo number_format($reg[0]['iva'], 2, '.', '') ?>%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?></p>
<p><b>Total Exento (0%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Total <?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaliva'], 2, '.', ','); ?> </p>
<p><b>Descontado %:</b> <?php echo $simbolo.number_format($reg[0]['descontado'], 2, '.', ','); ?></p>
<p><b>Desc. Global (<?php echo number_format($reg[0]['descuento'], 2, '.', '') ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaldescuento'], 2, '.', ','); ?> </p>
                                        <hr>
<h4><b>Importe Total :</b> <?php echo $simbolo.number_format($reg[0]['totalpago'], 2, '.', ','); ?></h4></div>
                                    <div class="clearfix"></div>
                                    <hr>
                                <div class="col-md-12">
                                    <div class="text-right">
 <a href="reportepdf?codtraspaso=<?php echo encrypt($reg[0]['codtraspaso']); ?>&codsucursal=<?php echo encrypt($reg[0]['codsucursal']); ?>&tipo=<?php echo encrypt("FACTURATRASPASO"); ?>" target="_blank" rel="noopener noreferrer"><button id="print" class="btn waves-light btn-light" type="button"><span><i class="fa fa-print"></i> Imprimir</span> </button></a>
 <button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
                                    </div>
                                </div>
                            </div>
                <!-- .row -->

  <?php
  }
} 
######################## MOSTRAR TRASPASO EN VENTANA MODAL ########################
?>

<?php
######################### MOSTRAR DETALLES DE TRASPASOS UPDATE ##########################
if (isset($_GET['MuestraDetallesTraspasoUpdate']) && isset($_GET['codtraspaso']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->TraspasosPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

?>

<div class="table-responsive m-t-20">
        <table class="table2 table-hover">
            <thead>
                <tr>
                    <th>Cantidad</th>
                    <th>Tipo</th>
                    <th class="text-left">Descripción de Producto</th>
                    <th>Precio Unitario</th>
                    <th>Valor Total</th>
                    <th>Desc %</th>
                    <th><?php echo $impuesto; ?></th>
                    <th>Valor Neto</th>
                    <?php if ($_SESSION['acceso'] == "administradorS") { ?>
                    <th>Acción</th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesTraspasos();
$a=1;
$count = 0;
for($i=0;$i<sizeof($detalle);$i++){ 
$count++; 
?>
  <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
  <td>
  <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
  <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetalleTraspaso('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
  <input type="text" class="bold" name="cantidad[]" id="cantidad_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoTraspaso(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="<?php echo number_format($detalle[$i]["cantidad"], 2, '.', ''); ?>" title="Ingrese Cantidad">
  <input type="hidden" name="cantidadbd[]" id="cantidadbd_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["cantidad"], 2, '.', ''); ?>">
  <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetalleTraspaso('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
  </div>
  </td>

  <td class="text-danger alert-link">
  <input type="hidden" name="coddetalletraspaso[]" id="coddetalletraspaso" value="<?php echo $detalle[$i]["coddetalletraspaso"]; ?>">
  <input type="hidden" name="tipo[]" id="tipo" value="<?php echo $detalle[$i]["tipo"]; ?>">
  <input type="hidden" name="codproducto[]" id="codproducto" value="<?php echo $detalle[$i]["codproducto"]; ?>">
  <input type="hidden" name="preciocompra[]" id="preciocompra_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["preciocompra"], 2, '.', ''); ?>">
  <?php if($detalle[$i]['tipo'] == 1){
      echo "PRODUCTO";
  } elseif($detalle[$i]['tipo'] == 3){
      echo "INGREDIENTE";
  } ?></td>

  <td class="text-left"><label><h5><?php echo $detalle[$i]['producto']; ?></h5>
  <small class="text-danger alert-link"><?php echo $detalle[$i]['tipo'] == 1 ? $detalle[$i]['nomcategoria'] : $detalle[$i]['nommedida']; ?></small></label></td>
          
  <td><input type="hidden" name="precioventa[]" id="precioventa_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>">
  <input type="hidden" name="precioconiva[]" id="precioconiva_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? "0.00" : number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>"><?php echo number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></td>

  <td><input type="hidden" name="valortotal[]" id="valortotal_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["valortotal"], 2, '.', ''); ?>"><label id="txtvalortotal_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></label></td>

  <td>
  <input type="hidden" name="descproducto[]" id="descproducto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["descproducto"], 2, '.', ''); ?>">
  <input type="hidden" class="totaldescuentov" name="totaldescuentov[]" id="totaldescuentov_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["totaldescuentov"], 2, '.', ','); ?>">
  <strong><label id="txtdescproducto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?></label><sup><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ''); ?>%</sup></strong></td>

  <td><input type="hidden" name="ivaproducto[]" id="ivaproducto_<?php echo $count; ?>" value="<?php echo $detalle[$i]["ivaproducto"]; ?>"><strong><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? $detalle[$i]['ivaproducto']."%" : "(E)"; ?></strong></td>

  <td><input type="hidden" class="subtotalivasi" name="subtotalivasi[]" id="subtotalivasi_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

  <input type="hidden" class="subtotalivano" name="subtotalivano[]" id="subtotalivano_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

  <input type="hidden" class="subtotalimpuestos" name="subtotalimpuestos[]" id="subtotalimpuestos_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['subtotalimpuestos'], 2, '.', ''); ?>">

  <input type="hidden" class="subtotaldiscriminado" name="subtotaldiscriminado[]" id="subtotaldiscriminado_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto']-$detalle[$i]['subtotalimpuestos'], 2, '.', '') : "0.00"; ?>">

  <input type="hidden" class="valorneto" name="valorneto[]" id="valorneto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto'], 2, '.', ''); ?>" >

  <input type="hidden" class="valorneto2" name="valorneto2[]" id="valorneto2_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto2'], 2, '.', ''); ?>" >

  <strong> <label id="txtvalorneto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></label></strong></td>

  <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
  <button type="button" class="btn btn-outline-dark btn-rounded" onClick="EliminarDetallesTraspasoUpdate('<?php echo encrypt($detalle[$i]["coddetalletraspaso"]); ?>','<?php echo encrypt($detalle[$i]["codtraspaso"]); ?>','<?php echo encrypt($reg[0]["recibe"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt("DETALLESTRASPASOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
      </tr>
      <?php } ?>
    </tbody>
  </table><hr>

  <table id="carritototal" class="table-responsive">
    <tr>
    <td width="250"><h5><label>Gravado (<?php echo $reg[0]['iva'] ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal"><?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdiscriminado" id="txtdiscriminado" value="<?php echo number_format($reg[0]['subtotalivasi'], 0, '.', ''); ?>"/>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="<?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ''); ?>"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    
    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2"><?php echo number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="<?php echo number_format($reg[0]['subtotalivano'], 2, '.', ''); ?>"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>"></label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva"><?php echo number_format($reg[0]['totaliva'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtIva" id="txtIva" value="<?php echo number_format($reg[0]['totaliva'], 2, '.', ''); ?>"/>
    </td>
    </tr>
    <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado"><?php echo number_format($reg[0]['descontado'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdescontado" id="txtdescontado" value="<?php echo number_format($reg[0]['descontado'], 2, '.', ''); ?>"/>
        </td>
    <td>
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" style="border-radius:4px;height:30px;width:60px;" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['descuento'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento"><?php echo number_format($reg[0]['totaldescuento'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="<?php echo number_format($reg[0]['totaldescuento'], 2, '.', ''); ?>"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal"><?php echo number_format($reg[0]['totalpago'], 2, '.', ','); ?></label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtTotal2" id="txtTotal2" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    </td>
    </tr>
    </table>
  </div>
<?php
} 
######################### MOSTRAR DETALLES DE TRASPASOS UPDATE ##########################
?>

<?php
########################## BUSQUEDA TRASPASOS POR SUCURSAL ##########################
if (isset($_GET['BuscaTraspasosxSucursal']) && isset($_GET['codsucursal'])) {
  
$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";   
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarTraspasosxSucursal();
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Traspasos por Sucursal </h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("TRASPASOSXSUCURSAL"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("TRASPASOSXSUCURSAL"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("TRASPASOSXSUCURSAL"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nombre de Sucursal: </label> <?php echo $reg[0]['nomsucursal']; ?>
      
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                        <thead>
                          <tr>
                            <th>Nº</th>
                            <th>N° de Traspaso</th>
                            <th>Sucursal que Recibe</th>
                            <th>Fecha Trapasos</th>
                            <th>Nº de Articulos</th>
                            <th>SubTotal</th>
                            <th><?php echo $impuesto; ?></th>
                            <th>Desc.</th>
                            <th>Imp. Total</th>
                            <th><span class="mdi mdi-drag-horizontal"></span></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['sumarticulos']; 
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><?php echo $reg[$i]['cuitsucursal2'].": <strong>".$reg[$i]['nomsucursal2']."</strong>"; ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechatraspaso'])); ?></td>
  <td><?php echo number_format($reg[$i]['sumarticulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo $reg[$i]['iva']; ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>

  <td> <a href="reportepdf?codtraspaso=<?php echo encrypt($reg[$i]['codtraspaso']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("FACTURATRASPASO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-outline-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
                                  </tr>
                        <?php } ?>
         <tr>
           <td colspan="4"></td>
<td><?php echo $simbolo.number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td></td>
         </tr>
                              </tbody>
                          </table>
                      </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
########################## BUSQUEDA TRASPASOS POR SUCURSAL ##########################
?>

<?php
########################## BUSQUEDA TRASPASOS POR FECHAS ##########################
if (isset($_GET['BuscaTraspasosxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DESDE PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA HASTA PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DESDE NO PUEDE SER MAYOR QUE LA FECHA HASTA</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarTraspasosxFechas();
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Traspasos por Fechas </h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("TRASPASOSXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("TRASPASOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("TRASPASOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nombre de Sucursal: </label> <?php echo $reg[0]['nomsucursal']; ?><br>
      
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <th>Nº</th>
                          <th>N° de Traspaso</th>
                          <th>Sucursal que Recibe</th>
                          <th>Fecha Trapasos</th>
                          <th>Nº de Articulos</th>
                          <th>SubTotal</th>
                          <th><?php echo $impuesto; ?></th>
                          <th>Desc.</th>
                          <th>Imp. Total</th>
                          <th><span class="mdi mdi-drag-horizontal"></span></th>
                        </tr>
                      </thead>
                      <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['sumarticulos']; 
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><?php echo $reg[$i]['cuitsucursal2'].": <strong>".$reg[$i]['nomsucursal2']."</strong>"; ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechatraspaso'])); ?></td>
  <td><?php echo number_format($reg[$i]['sumarticulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo $reg[$i]['iva']; ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>

  <td> <a href="reportepdf?codtraspaso=<?php echo encrypt($reg[$i]['codtraspaso']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("FACTURATRASPASO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-outline-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
        </tr>
        <?php } ?>
        <tr>
        <td colspan="4"></td>
<td><?php echo $simbolo.number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td></td>
         </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
########################## BUSQUEDA TRASPASOS POR FECHAS ##########################
?>


<?php 
########################### BUSQUEDA DE DETALLES TRASPASOS POR FECHAS ##########################
if (isset($_GET['BuscaDetallesTraspasosxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$traspaso = new Login();
$reg = $traspaso->BuscarDetallesTraspasosxFechas();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Detalles de Traspasos por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("DETALLESTRASPASOSXFECHAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("DETALLESTRASPASOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("DETALLESTRASPASOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>Tipo</th>
                              <th>Descripción</th>
                              <th>Categoria</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Desc %</th>
                              <th>Precio de Venta</th>
                              <th>Existencia</th>
                              <th>Traspaso</th>
                              <th>Monto Total</th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$PagoTotal=0;

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal+=$reg[$i]['precioventa'];

if($reg[$i]['tipo'] == 1){
$Tipo="PRODUCTO";
$Categoria=$reg[$i]['nomcategoria'];
$Existencia=$reg[$i]['existencia'];
$ExisteTotal+=$reg[$i]['existencia'];
} else {
$Tipo="INGREDIENTE"; 
$Categoria=$reg[$i]['nommedida'];
$Existencia=$reg[$i]['cantingrediente'];
$ExisteTotal+=$reg[$i]['cantingrediente'];
}

$VendidosTotal+=$reg[$i]['cantidad'];

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg = $reg[$i]['ivaproducto']/100;
$SubtotalImpuesto = $PrecioFinal*$ivg;
$CalculoImpuesto = $SubtotalImpuesto*$reg[$i]['cantidad'];

$TotalDescuento+=$CalculoDescuento; 
$TotalImpuesto+=$CalculoImpuesto; 
$PagoTotal+=$PrecioFinal*$reg[$i]['cantidad']; 
?>
            <tr>
              <td><?php echo $a++; ?></td>
              <td class="text-danger alert-link"><?php echo $Tipo; ?></td>
              <td><?php echo $reg[$i]['producto']; ?></td>
              <td><?php echo $Categoria ?></td>
              <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($reg[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
              <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
              <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
              <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
              <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
              <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
            </tr>
            <?php  }  ?>
            <tr>
              <td colspan="6"></td>
              <td><label><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo number_format($ExisteTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></label></td>
            </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA DE DETALLES TRASPASOS POR FECHAS ##########################
?>


<?php 
########################### BUSQUEDA DE DETALLES TRASPASOS POR SUCURSAL ##########################
if (isset($_GET['BuscaDetallesTraspasosxSucursal']) && isset($_GET['codsucursal']) && isset($_GET['recibe']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$recibe = limpiar($_GET['recibe']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL QUE ENVIA PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($recibe=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL QUE RECIBE PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$traspaso = new Login();
$reg = $traspaso->BuscarDetallesTraspasosxSucursal();  
?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Detalles de Traspasos por Sucursal</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&recibe=<?php echo $recibe; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("DETALLESTRASPASOSXSUCURSAL"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&recibe=<?php echo $recibe; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("DETALLESTRASPASOSXSUCURSAL"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&recibe=<?php echo $recibe; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("DETALLESTRASPASOSXSUCURSAL"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nombre de Vendedor: </label> <?php echo $reg[0]['nombres']; ?><br>

            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <th>Nº</th>
                          <th>Tipo</th>
                          <th>Descripción</th>
                          <th>Categoria</th>
                          <th><?php echo $impuesto; ?></th>
                          <th>Desc %</th>
                          <th>Precio de Venta</th>
                          <th>Existencia</th>
                          <th>Traspaso</th>
                          <th>Monto Total</th>
                        </tr>
                      </thead>
                      <tbody>
<?php
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$PagoTotal=0;

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal+=$reg[$i]['precioventa'];

if($reg[$i]['tipo'] == 1){
$Tipo="PRODUCTO";
$Categoria=$reg[$i]['nomcategoria'];
$Existencia=$reg[$i]['existencia'];
$ExisteTotal+=$reg[$i]['existencia'];
} else {
$Tipo="INGREDIENTE"; 
$Categoria=$reg[$i]['nommedida'];
$Existencia=$reg[$i]['cantingrediente'];
$ExisteTotal+=$reg[$i]['cantingrediente'];
}

$VendidosTotal+=$reg[$i]['cantidad'];

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg = $reg[$i]['ivaproducto']/100;
$SubtotalImpuesto = $PrecioFinal*$ivg;
$CalculoImpuesto = $SubtotalImpuesto*$reg[$i]['cantidad'];

$TotalDescuento+=$CalculoDescuento; 
$TotalImpuesto+=$CalculoImpuesto; 
$PagoTotal+=$PrecioFinal*$reg[$i]['cantidad']; 
?>
              <tr>
                <td><?php echo $a++; ?></td>
                <td class="text-danger alert-link"><?php echo $Tipo; ?></td>
                <td><?php echo $reg[$i]['producto']; ?></td>
                <td><?php echo $Categoria ?></td>
                <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($reg[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
                <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
                <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
                <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
                <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
                <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
              </tr>
              <?php } ?>
              <tr>
                <td colspan="6"></td>
                <td><label><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></label></td>
                <td><label><?php echo number_format($ExisteTotal, 2, '.', ','); ?></label></td>
                <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
                <td><label><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></label></td>
              </tr>
              </tbody>
              </table>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA DE DETALLES TRASPASOS POR SUCURSAL ##########################
?>

























<?php
######################## MOSTRAR COTIZACIONES EN VENTANA MODAL #########################
if (isset($_GET['BuscaCotizacionModal']) && isset($_GET['codcotizacion']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->CotizacionesPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

  if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COTIZACIONES Y DETALLES ACTUALMENTE </center>";
    echo "</div>";    

} else {
?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pull-left">
                                        <address>
  <h4><b class="text-danger">SUCURSAL</b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomsucursal']; ?>,
  <br/> Nº <?php echo $reg[0]['documsucursal'] == '0' ? "DOCUMENTO" : $reg[0]['documento']; ?>: <?php echo $reg[0]['cuitsucursal']; ?> - TLF: <?php echo $reg[0]['tlfsucursal']; ?></p>

  <h4><b class="text-dark">Nº COTIZACIÓN <?php echo $reg[0]['codfactura']; ?></b></h4>
  <p class="text-muted m-l-5">FECHA DE EMISIÓN: <?php echo date("d-m-Y H:i:s",strtotime($reg[0]['fechacotizacion'])); ?></p>
                                        </address>
                                    </div>
                                    <div class="pull-right text-right">
                                        <address>
  <h4><b class="text-danger">CLIENTE</b></h4>
  <p class="text-muted m-l-30"><?php echo $reg[0]['nomcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['nomcliente']; ?>,
  <?php echo $reg[0]['direccliente'] == '' ? "" : "<br/>".$reg[0]['direccliente']; ?>
  <?php echo $reg[0]['provincia2'] == '' ? "" : "<br/>".$reg[0]['provincia2']; ?> <?php echo $reg[0]['departamento2'] == '' ? "" : $reg[0]['departamento2']; ?>
  <br/> EMAIL: <?php echo $reg[0]['emailcliente'] == '' ? "*******" : $reg[0]['emailcliente']; ?>
  <br/> Nº <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3'] ?>: <?php echo $reg[0]['dnicliente'] == '' ? "*******" : $reg[0]['dnicliente']; ?> - TLF: <?php echo $reg[0]['tlfcliente'] == '' ? "*******" : $reg[0]['tlfcliente']; ?></p>
                                            
                        </address>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="table-responsive m-t-10" style="clear: both;">
                        <div id="div1"><table class="table table-hover">
                        <thead>
                        <tr>
                        <th>#</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
                        <?php if ($_SESSION['acceso'] == "administradorS" && $reg[0]["procesada"] == 1) { ?>
                        <th>Acción</th>
                        <?php } ?>
                      </tr>
                    </thead>
                  <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesCotizaciones();

$SubTotal = 0;
$a=1;
for($i=0;$i<sizeof($detalle);$i++){  
$SubTotal += $detalle[$i]['valorneto'];
?>
  <tr>
  <td><label><?php echo $a++; ?></label></td>
  <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
  <small class="text-danger alert-link"><?php echo $observaciones = ($detalle[$i]['detallesobservaciones'] == '' ? "**********" : $detalle[$i]['detallesobservaciones']); ?></small></td>
  <td><?php echo $detalle[$i]['cantcotizacion']; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?><sup><strong><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ','); ?>%</strong></sup></td>
  <td><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></td>
 <?php if ($_SESSION['acceso'] == "administradorS" && $reg[0]["procesada"] == 1) { ?><td>
<button type="button" class="btn btn-rounded btn-dark" onClick="EliminarDetallesCotizacionModal('<?php echo encrypt($detalle[$i]["coddetallecotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codcotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt("DETALLESCOTIZACIONES"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                                </tr>
                                      <?php } ?>
                                            </tbody>
                                        </table></div>
                                    </div>
                                </div>

                                <div class="col-md-12">

                                    <div class="pull-right text-right">
<p><b>Subtotal:</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi']+$reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Gravado  (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?><p>
<p><b>Exento (0%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Total <?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaliva'], 2, '.', ','); ?> </p>
<p><b>Descontado %:</b> <?php echo $simbolo.number_format($reg[0]['descontado'], 2, '.', ','); ?> </p>
<p><b>Desc. Global (<?php echo number_format($reg[0]['descuento'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaldescuento'], 2, '.', ','); ?> </p>
                                        <hr>
<h4><b>Importe Total:</b> <?php echo $simbolo.number_format($reg[0]['totalpago'], 2, '.', ','); ?></h4></div>
                                    <div class="clearfix"></div>
                                    <hr>

                                <div class="col-md-12">
                                    <div class="text-right">
 <a href="reportepdf?codcotizacion=<?php echo encrypt($reg[0]['codcotizacion']); ?>&codsucursal=<?php echo encrypt($reg[0]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[0]['tipodocumento']) ?>" target="_blank" rel="noopener noreferrer"><button id="print" class="btn waves-light btn-light" type="button"> <span><i class="fa fa-print"></i> Imprimir</span></button></a>
 <button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
                                    </div>
                                </div>
                            </div>
                <!-- .row -->
  <?php
  }
} 
######################### MOSTRAR COTIZACIONES EN VENTANA MODAL #########################
?>


<?php
####################### MOSTRAR DETALLES DE COTIZACIONES UPDATE #########################
if (isset($_GET['MuestraDetallesCotizacionesUpdate']) && isset($_GET['codcotizacion']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->CotizacionesPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");
?>
<div class="table-responsive m-t-20">
            <table class="table2 table-hover">
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
                        <?php if ($_SESSION['acceso'] == "administradorS") { ?>
                        <th>Acción</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesCotizaciones();
$a=1;
$count = 0;
for($i=0;$i<sizeof($detalle);$i++){ 
$count++;  
?>
  <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
  <td>
  <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
  <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetalleCotizacion('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
  <input type="text" class="bold" name="cantcotizacion[]" id="cantcotizacion_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoCotizacion(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="<?php echo number_format($detalle[$i]["cantcotizacion"], 2, '.', ''); ?>" title="Ingrese Cantidad">
  <input type="hidden" name="cantcotizacionbd[]" id="cantcotizacionbd_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["cantcotizacion"], 2, '.', ''); ?>">
  <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetalleCotizacion('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
  </div>
  </td>
      
  <td class="text-danger alert-link">
  <input type="hidden" name="coddetallecotizacion[]" id="coddetallecotizacion" value="<?php echo $detalle[$i]["coddetallecotizacion"]; ?>">
  <input type="hidden" name="codproducto[]" id="codproducto" value="<?php echo $detalle[$i]["codproducto"]; ?>">
  <input type="hidden" name="tipo[]" id="tipo" value="<?php echo $detalle[$i]["tipo"]; ?>">
  <input type="hidden" name="preciocompra[]" id="preciocompra_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["preciocompra"], 2, '.', ''); ?>">
  <?php if($detalle[$i]['tipo'] == 1){
    echo "PRODUCTO";
  } elseif($detalle[$i]['tipo'] == 2){
    echo "COMBO";
  } else {
    echo "EXTRA";
  } ?></td>  
  <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
  <small class="text-danger alert-link"><?php echo $observaciones = ($detalle[$i]['detallesobservaciones'] == '' ? "**********" : $detalle[$i]['detallesobservaciones']); ?> <?php echo $salsas = ($detalle[$i]['detallesalsas'] == '' ? "" : $detalle[$i]['detallesalsas']); ?></small></td>
      
  <td><strong><input type="hidden" name="precioventa[]" id="precioventa_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>">
  <input type="hidden" name="precioconiva[]" id="precioconiva_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? "0.00" : number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>"><?php echo number_format($detalle[$i]['precioventa'], 2, '.', ''); ?></strong></td>

  <td><input type="hidden" name="valortotal[]" id="valortotal_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["valortotal"], 2, '.', ''); ?>"><label><label id="txtvalortotal_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valortotal'], 2, '.', ''); ?></label></label></td>

  <td><input type="hidden" name="descproducto[]" id="descproducto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["descproducto"], 2, '.', ''); ?>">
  <input type="hidden" class="totaldescuentov" name="totaldescuentov[]" id="totaldescuentov_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["totaldescuentov"], 2, '.', ''); ?>">
  <label><label id="txtdescproducto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['totaldescuentov'], 2, '.', ''); ?></label><sup><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ''); ?>%</sup></label></td>

  <td><input type="hidden" name="ivaproducto[]" id="ivaproducto_<?php echo $count; ?>" value="<?php echo $detalle[$i]["ivaproducto"]; ?>"><label><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', '')."%" : "(E)"; ?></label></td>

  <td><input type="hidden" class="subtotalivasi" name="subtotalivasi[]" id="subtotalivasi_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

  <input type="hidden" class="subtotalivano" name="subtotalivano[]" id="subtotalivano_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

  <input type="hidden" class="subtotalimpuestos" name="subtotalimpuestos[]" id="subtotalimpuestos_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['subtotalimpuestos'], 2, '.', ''); ?>">

  <input type="hidden" class="subtotaldiscriminado" name="subtotaldiscriminado[]" id="subtotaldiscriminado_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto']-$detalle[$i]['subtotalimpuestos'], 2, '.', '') : "0.00"; ?>">

  <input type="hidden" class="valorneto" name="valorneto[]" id="valorneto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto'], 2, '.', ''); ?>" >

  <input type="hidden" class="valorneto2" name="valorneto2[]" id="valorneto2_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto2'], 2, '.', ''); ?>" >

  <label> <label id="txtvalorneto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valorneto'], 2, '.', ''); ?></label></label></td>

  <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
  <button type="button" class="btn btn-rounded btn-dark" onClick="EliminarDetallesCotizacionesUpdate('<?php echo encrypt($detalle[$i]["coddetallecotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codcotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt("DETALLESCOTIZACIONES"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                 </tr>
                     <?php } ?>
                </tbody>
            </table><hr>

            <table id="carritototal" class="table-responsive">
                <tr>
    <td width="250"><h5><label>Gravado (<?php echo number_format($reg[0]['iva'], 2, '.', '') ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal"><?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdiscriminado" id="txtdiscriminado" value="<?php echo number_format($reg[0]['subtotalivasi'], 0, '.', ''); ?>"/>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="<?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ''); ?>"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2"><?php echo number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="<?php echo number_format($reg[0]['subtotalivano'], 2, '.', ''); ?>"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>"></label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva"><?php echo number_format($reg[0]['totaliva'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtIva" id="txtIva" value="<?php echo number_format($reg[0]['totaliva'], 2, '.', ''); ?>"/>
    </td>
                </tr>
                <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado"><?php echo number_format($reg[0]['descontado'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdescontado" id="txtdescontado" value="<?php echo number_format($reg[0]['descontado'], 2, '.', ''); ?>"/>
        </td>
    
    <td>
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" style="border-radius:4px;height:30px;width:60px;"  onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['descuento'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento"><?php echo number_format($reg[0]['totaldescuento'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="<?php echo number_format($reg[0]['totaldescuento'], 2, '.', ''); ?>"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal"><?php echo number_format($reg[0]['totalpago'], 2, '.', ','); ?></label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtTotalCompra" id="txtTotalCompra" value="<?php echo number_format($reg[0]['totalpago2'], 2, '.', ''); ?>"/>    </td>
        </tr>
      </table>
  </div>
<?php
} 
####################### MOSTRAR DETALLES DE COTIZACIONES UPDATE #########################
?>

<?php
####################### MOSTRAR DETALLES DE COTIZACIONES AGREGAR #######################
if (isset($_GET['MuestraDetallesCotizacionesAgregar']) && isset($_GET['codcotizacion']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->CotizacionesPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

?>

<div class="table-responsive m-t-20">
            <table class="table2 table-hover">
                <thead>
                    <tr>
                        <th>Nº</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
                        <?php if ($_SESSION['acceso'] == "administradorS") { ?>
                        <th>Acción</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesCotizaciones();
$a=1;
for($i=0;$i<sizeof($detalle);$i++){  
?>
  <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
  <td><label><?php echo $a++; ?></label></td>
  <td class="text-danger alert-link">
  <?php if($detalle[$i]['tipo'] == 1){
    echo "PRODUCTO";
  } elseif($detalle[$i]['tipo'] == 2){
    echo "COMBO";
  } else {
    echo "EXTRA";
  } ?></td>
  <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
  <small class="text-danger alert-link"><?php echo $observaciones = ($detalle[$i]['detallesobservaciones'] == '' ? "**********" : $detalle[$i]['detallesobservaciones']); ?> <?php echo $salsas = ($detalle[$i]['detallesalsas'] == '' ? "" : $detalle[$i]['detallesalsas']); ?></small></td>
  <td><?php echo $detalle[$i]['cantcotizacion']; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?><sup><label><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ''); ?>%</label></sup></td>
  <td><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', '')."%" : "(E)"; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valorneto'], 2, '.', ''); ?></td>
  <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
  <button type="button" class="btn btn-rounded btn-dark" onClick="EliminarDetallesCotizacionesAgregar('<?php echo encrypt($detalle[$i]["coddetallecotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codcotizacion"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt("DETALLESCOTIZACIONES"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                 </tr>
                     <?php } ?>
                </tbody>
            </table>

            <table id="carritototal" class="table-responsive">
                <tr>
    <td width="250"><h5><label>Gravado (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?></label></h5>
    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></label></h5>
    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):</label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['totaliva'], 2, '.', ','); ?></label></h5>
    </td>
                </tr>
                <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['descontado'], 2, '.', ','); ?></label></h5>
        </td>
    
    <td>
    <h5><label>Desc. Global <?php echo number_format($reg[0]['descuento'], 2, '.', ''); ?>%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['totaldescuento'], 2, '.', ','); ?></label></h5>
    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label><?php echo number_format($reg[0]['totalpago'], 2, '.', ','); ?></label></b></h4>
    </td>
      </tr>
    </table>
  </div>
<?php
} 
######################## MOSTRAR DETALLES DE COTIZACIONES AGREGRA #######################
?>


<?php
########################## BUSQUEDA COTIZACIONES POR FECHAS ##########################
if (isset($_GET['BuscaCotizacionesxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarCotizacionesxFechas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Cotizaciones por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("COTIZACIONESXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("COTIZACIONESXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("COTIZACIONESXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

          <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                              <thead>
                                <tr>
                                  <th>Nº</th>
                                  <th>N° de Cotización</th>
                                  <th>Descripción de Cliente</th>
                                  <th>Fecha Emisión</th>
                                  <th>Nº de Articulos</th>
                                  <th>Subtotal</th>
                                  <th><?php echo $impuesto; ?></th>
                                  <th>Desc %</th>
                                  <th>Imp. Total</th>
                                  <th><span class="mdi mdi-drag-horizontal"></span></th>
                                </tr>
                              </thead>
                              <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago'];
?>
                                <tr>
                                  <td><?php echo $a++; ?></td>
                                  <td><?php echo $reg[$i]['codfactura']; ?></td>
<td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento3']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td> 
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechacotizacion'])); ?></td> 
                                  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  <td> <a href="reportepdf?codcotizacion=<?php echo encrypt($reg[$i]['codcotizacion']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("FACTURACOTIZACION"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-rounded btn-secondary" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
                                  </tr>
                        <?php  }  ?>
         <tr>
           <td colspan="4"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
         </tr>
                              </tbody>
                          </table>
                      </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  
   }
 } 
########################## BUSQUEDA COTIZACIONES POR FECHAS ##########################
?>

<?php 
########################### BUSQUEDA DE DETALLES COTIZACIONES POR FECHAS ##########################
if (isset($_GET['BuscaDetallesCotizacionesxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$cotizado = new Login();
$reg = $cotizado->BuscarDetallesCotizacionesxFechas();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Detalles de Cotizaciones por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("DETALLESCOTIZACIONESXFECHAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("DETALLESCOTIZACIONESXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("DETALLESCOTIZACIONESXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <th>Nº</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Categoria</th>
                        <th>Precio de Venta</th>
                        <th>Existencia</th>
                        <th>Cotizado</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <th>Monto Total</th>
                      </tr>
                    </thead>
                    <tbody>
<?php
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$PagoTotal=0;

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal+=$reg[$i]['precioventa'];

if($reg[$i]['tipo'] == 1){

$Tipo="PRODUCTO";
$Categoria=$reg[$i]['nomcategoria'];
$Existencia=$reg[$i]['existencia'];
$ExisteTotal+=$reg[$i]['existencia'];

} elseif($reg[$i]['tipo'] == 2){

$Tipo="COMBO";
$Categoria="********";
$Existencia=$reg[$i]['cantcombo'];
$ExisteTotal+=$reg[$i]['cantcombo'];

} else {

$Tipo="EXTRA";
$Categoria=$reg[$i]['nommedida'];
$Existencia=$reg[$i]['cantingrediente'];
$ExisteTotal+=$reg[$i]['cantingrediente'];
}

$VendidosTotal+=$reg[$i]['cantidad'];

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg = $reg[$i]['ivaproducto']/100;
$SubtotalImpuesto = $PrecioFinal*$ivg;
$CalculoImpuesto = $SubtotalImpuesto*$reg[$i]['cantidad'];

$TotalDescuento+=$CalculoDescuento; 
$TotalImpuesto+=$CalculoImpuesto; 
$PagoTotal+=$PrecioFinal*$reg[$i]['cantidad']; 
?>

          <tr>
          <td><?php echo $a++; ?></td>
          <td class="text-danger alert-link"><?php echo $Tipo; ?></td>
          <td><?php echo $reg[$i]['producto']; ?></td>
          <td><?php echo $Categoria ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
          <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
          <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
          <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
          <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
                    </tr>
            <?php } ?>
          <tr>
            <td colspan="4"></td>
            <td><label><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></label></td>
            <td><label><?php echo number_format($ExisteTotal, 2, '.', ','); ?></label></td>
            <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
            <td><label><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></label></td>
            <td><label><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></label></td>
            <td><label><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></label></td>
          </tr>
                  </tbody>
              </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA DE DETALLES COTIZACIONES POR FECHAS ##########################
?>


<?php 
########################### BUSQUEDA DE DETALLES COTIZACIONES POR VENDEDOR ##########################
if (isset($_GET['BuscaDetallesCotizacionesxVendedor']) && isset($_GET['codsucursal']) && isset($_GET['codigo']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$codigo = limpiar($_GET['codigo']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codigo=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE VENDEDOR PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarDetallesCotizacionesxVendedor();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Detalles de Cotizaciones por Vendedor</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("DETALLESCOTIZACIONESXVENDEDOR"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("DETALLESCOTIZACIONESXVENDEDOR"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("DETALLESCOTIZACIONESXVENDEDOR"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nombre de Vendedor: </label> <?php echo $reg[0]['nombres']; ?><br>

            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                    <thead>
                      <tr>
                        <th>Nº</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Categoria</th>
                        <th>Precio de Venta</th>
                        <th>Existencia</th>
                        <th>Cotizado</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <th>Monto Total</th>
                      </tr>
                    </thead>
                    <tbody>
<?php
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$PagoTotal=0;

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal+=$reg[$i]['precioventa'];

if($reg[$i]['tipo'] == 1){

$Tipo="PRODUCTO";
$Categoria=$reg[$i]['nomcategoria'];
$Existencia=$reg[$i]['existencia'];
$ExisteTotal+=$reg[$i]['existencia'];

} elseif($reg[$i]['tipo'] == 2){

$Tipo="COMBO";
$Categoria="********";
$Existencia=$reg[$i]['cantcombo'];
$ExisteTotal+=$reg[$i]['cantcombo'];

} else {

$Tipo="EXTRA";
$Categoria=$reg[$i]['nommedida'];
$Existencia=$reg[$i]['cantingrediente'];
$ExisteTotal+=$reg[$i]['cantingrediente'];
}

$VendidosTotal+=$reg[$i]['cantidad'];

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg = $reg[$i]['ivaproducto']/100;
$SubtotalImpuesto = $PrecioFinal*$ivg;
$CalculoImpuesto = $SubtotalImpuesto*$reg[$i]['cantidad'];

$TotalDescuento+=$CalculoDescuento; 
$TotalImpuesto+=$CalculoImpuesto; 
$PagoTotal+=$PrecioFinal*$reg[$i]['cantidad']; 
?>

            <tr>
              <td><?php echo $a++; ?></td>
              <td class="text-danger alert-link"><?php echo $Tipo; ?></td>
              <td><?php echo $reg[$i]['producto']; ?></td>
              <td><?php echo $Categoria ?></td>
              <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
              <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
              <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
              <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
              <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
              <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
            </tr>
            <?php } ?>
            <tr>
              <td colspan="4"></td>
              <td><label><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo number_format($ExisteTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo number_format($VendidosTotal, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></label></td>
              <td><label><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></label></td>
            </tr>
          </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA DE DETALLES COTIZACIONES POR VENDEDOR ##########################
?>





















<?php
######################## MOSTRAR CAJA DE VENTA EN VENTANA MODAL ########################
if (isset($_GET['BuscaCajaModal']) && isset($_GET['codcaja'])) { 

$reg = $new->CajasPorId();
?>
  
  <table class="table-responsive" border="0" class="text-center"> 
  <tr>
    <td><strong>Nº de Caja:</strong> <?php echo $reg[0]['nrocaja']; ?></td>
  </tr>
  <tr>
    <td><strong>Nombre de Caja:</strong> <?php echo $reg[0]['nomcaja']; ?></td>
  </tr>
  <tr>
    <td><strong>Responsable de Caja: </strong> <?php echo $reg[0]['nombres']; ?></td>
  </tr>
  <?php if($_SESSION['acceso'] == "administradorG") { ?>
  <tr>
    <td><strong>Sucursal Asignada: </strong> <?php echo $reg[0]['codsucursal'] == "0" ? "**********" : $reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal']; ?></td>
  </tr>
  <?php } ?>
</table>
<?php 
} 
######################## MOSTRAR CAJA DE VENTA EN VENTANA MODAL ########################
?>


<?php 
############################# BUSCAR CAJAS POR SUCURSALES #############################
if (isset($_GET['BuscaCajasxSucursal']) && isset($_GET['codsucursal'])) {
  
$caja = $new->BuscarCajasxSucursal();
  ?>
<option value=""> -- SELECCIONE -- </option>
  <?php
   for($i=0;$i<sizeof($caja);$i++){
    ?>
<option value="<?php echo encrypt($caja[$i]['codcaja']) ?>"><?php echo $caja[$i]['hora_desde']." - ".$caja[$i]['hora_hasta']." | ".$caja[$i]['nrocaja'].": ".$caja[$i]['nomcaja']." | ".$caja[$i]['nombres']; ?></option>
  <?php 
  } 
}
############################# BUSCAR CAJAS POR SUCURSALES ##########################
?>


<?php 
############################# BUSCAR CAJAS POR SUCURSALES #############################
if (isset($_GET['BuscaCajasAbiertasxSucursal']) && isset($_GET['codsucursal'])) {
  
$caja = $new->ListarCajasAbiertas();
  ?>
<option value=""> -- SELECCIONE -- </option>
  <?php
   for($i=0;$i<sizeof($caja);$i++){
    ?>
<option value="<?php echo $caja[$i]['codcaja']; ?>"><?php echo $caja[$i]['hora_desde']." - ".$caja[$i]['hora_hasta']." | ".$caja[$i]['nrocaja'].": ".$caja[$i]['nomcaja']." | ".$caja[$i]['nombres']; ?></option>
    <?php 
   } 
}
############################# BUSCAR CAJAS POR SUCURSALES ##########################
?>

<?php
######################## MOSTRAR ARQUEO EN CAJA EN VENTANA MODAL ########################
if (isset($_GET['BuscaArqueoModal']) && isset($_GET['codarqueo'])) { 

$reg = $new->ArqueoCajaPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

$TotalVentas = $reg[0]['efectivo']+$reg[0]['cheque']+$reg[0]['tcredito']+$reg[0]['tdebito']+$reg[0]['tprepago']+$reg[0]['transferencia']+$reg[0]['electronico']+$reg[0]['cupon']+$reg[0]['otros']+$reg[0]['creditos']+$reg[0]['propinasefectivo']+$reg[0]['propinascheque']+$reg[0]['propinastcredito']+$reg[0]['propinastdebito']+$reg[0]['propinastprepago']+$reg[0]['propinastransferencia']+$reg[0]['propinaselectronico']+$reg[0]['propinascupon']+$reg[0]['propinasotros'];

$VentaOtros = $reg[0]['cheque']+$reg[0]['tcredito']+$reg[0]['tdebito']+$reg[0]['tprepago']+$reg[0]['transferencia']+$reg[0]['electronico']+$reg[0]['cupon']+$reg[0]['otros'];

$TotalEfectivo = $reg[0]['montoinicial']+$reg[0]['efectivo']+$reg[0]['ingresosefectivo']+$reg[0]['abonosefectivo']+$reg[0]['propinasefectivo']-$reg[0]['egresos'];

$TotalOtros = $reg[0]['cheque']+$reg[0]['tcredito']+$reg[0]['tdebito']+$reg[0]['tprepago']+$reg[0]['transferencia']+$reg[0]['electronico']+$reg[0]['cupon']+$reg[0]['otros']+$reg[0]['creditos']+$reg[0]['abonosotros']+$reg[0]['propinascheque']+$reg[0]['propinastcredito']+$reg[0]['propinastdebito']+$reg[0]['propinastprepago']+$reg[0]['propinastransferencia']+$reg[0]['propinaselectronico']+$reg[0]['propinascupon']+$reg[0]['propinasotros']+$reg[0]['ingresosotros'];
?>
  
  <table class="table-responsive" border="0" class="text-center">
  <tr>
    <td><h4 class="card-subtitle m-0 text-dark"><i class="mdi mdi-account-outline"></i> Cajero</h4><hr></td>
  </tr>

  <tr>
    <td><strong>Horario de Caja:</strong> <?php echo $reg[0]['hora_desde']." - ".$reg[0]['hora_hasta']; ?></td>
  </tr>
  <tr>
    <td><strong>Nombre de Caja:</strong> <?php echo $reg[0]['nrocaja'].": ".$reg[0]['nomcaja']; ?></td>
  </tr>
  <tr>
    <td><strong>Responsable:</strong> <?php echo $reg[0]['dni'].": ".$reg[0]['nombres']; ?></td>
  </tr>
  <tr>
    <td><strong>Hora Apertura:</strong> <?php echo date("d-m-Y H:i:s",strtotime($reg[0]['fechaapertura'])); ?></td>
  </tr>
  <tr>
    <td><strong>Hora Cierre:</strong> <?php echo $cierre = ( $reg[0]['statusarqueo'] == '1' ? $reg[0]['fechacierre'] : date("d-m-Y H:i:s",strtotime($reg[0]['fechacierre']))); ?></td>
  </tr>
  <tr>
    <td><strong>Monto Inicial:</strong> <?php echo $simbolo.number_format($reg[0]['montoinicial'], 2, '.', ','); ?></td>
  </tr>

  <?php //if($_SESSION['acceso'] == "administradorG" || $_SESSION['acceso'] == "administradorS" || $_SESSION['acceso'] == "secretaria" || $_SESSION['acceso'] == "cajero" && $reg[0]['statusarqueo'] == 0) { ?>

  <tr>
    <td><hr><h4 class="card-subtitle m-0 text-dark"><i class="mdi mdi-cart-plus"></i> Desglose en Ventas</h4><hr></td>
  </tr>

  <tr>
    <td><strong>Efectivo:</strong> <?php echo $simbolo.number_format($reg[0]['efectivo'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Cheque:</strong> <?php echo $simbolo.number_format($reg[0]['cheque'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Tarjeta Crédito:</strong> <?php echo $simbolo.number_format($reg[0]['tcredito'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Tarjeta Débito:</strong> <?php echo $simbolo.number_format($reg[0]['tdebito'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Tarjeta Prepago:</strong> <?php echo $simbolo.number_format($reg[0]['tprepago'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Transferencia:</strong> <?php echo $simbolo.number_format($reg[0]['transferencia'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Dinero Electrónico:</strong> <?php echo $simbolo.number_format($reg[0]['electronico'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Cupón:</strong> <?php echo $simbolo.number_format($reg[0]['cupon'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Otros:</strong> <?php echo $simbolo.number_format($reg[0]['otros'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Crédito:</strong> <?php echo $simbolo.number_format($reg[0]['creditos'], 2, '.', ','); ?></td>
  </tr>

  <tr>
    <td><hr><h4 class="card-subtitle m-0 text-dark"><i class="mdi mdi-cart-plus"></i> Propinas</h4><hr></td>
  </tr>

  <tr>
    <td><strong>Propinas Efectivo:</strong> <?php echo $simbolo.number_format($reg[0]['propinasefectivo'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Propinas Cheque:</strong> <?php echo $simbolo.number_format($reg[0]['propinascheque'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Propinas Tarjeta Crédito:</strong> <?php echo $simbolo.number_format($reg[0]['propinastcredito'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Propinas Tarjeta Débito:</strong> <?php echo $simbolo.number_format($reg[0]['propinastdebito'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Propinas Tarjeta Prepago:</strong> <?php echo $simbolo.number_format($reg[0]['propinastprepago'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Propinas Transferencia:</strong> <?php echo $simbolo.number_format($reg[0]['propinastransferencia'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Propinas Dinero Electrónico:</strong> <?php echo $simbolo.number_format($reg[0]['propinaselectronico'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Propinas Cupón:</strong> <?php echo $simbolo.number_format($reg[0]['propinascupon'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Propinas Otros:</strong> <?php echo $simbolo.number_format($reg[0]['propinasotros'], 2, '.', ','); ?></td>
  </tr>

  <tr>
    <td><hr><h4 class="card-subtitle m-0 text-dark"><i class="mdi mdi-cart-plus"></i> Abonos de Créditos</h4><hr></td>
  </tr>

  <tr>
    <td><strong>Abonos Efectivo:</strong> <?php echo $simbolo.number_format($reg[0]['abonosefectivo'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Abonos Otros:</strong> <?php echo $simbolo.number_format($reg[0]['abonosotros'], 2, '.', ','); ?></td>
  </tr>

  <tr>
    <td><hr><h4 class="card-subtitle m-0 text-dark"><i class="mdi mdi-cash-usd"></i> Movimientos</h4><hr></td>
  </tr>

  <tr>
    <td><strong>Ingresos Efectivo:</strong> <?php echo $simbolo.number_format($reg[0]['ingresosefectivo'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Ingresos Otros:</strong> <?php echo $simbolo.number_format($reg[0]['ingresosotros'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Egresos:</strong> <?php echo $simbolo.number_format($reg[0]['egresos'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Notas de Crédito:</strong> <?php echo $simbolo.number_format($reg[0]['egresonotas'], 2, '.', ','); ?></td>
  </tr>

  <tr>
    <td><hr><h4 class="card-subtitle m-0 text-dark"><i class="mdi mdi-scale-balance"></i> Balance en Caja</h4><hr></td>
  </tr>

  <tr>
    <td><strong>Total en Ventas:</strong> <?php echo $simbolo.number_format($TotalVentas, 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Ventas en Efectivo:</strong> <?php echo $simbolo.number_format($reg[0]['efectivo']+$reg[0]['propinasefectivo'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Ventas en Otros:</strong> <?php echo $simbolo.number_format($VentaOtros, 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Total en Efectivo:</strong> <?php echo $simbolo.number_format($TotalEfectivo, 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Total en Otros:</strong> <?php echo $simbolo.number_format($TotalOtros, 2, '.', ','); ?></td>
  </tr>

  <tr>
    <td><strong>Efectivo en Caja:</strong> <?php echo $simbolo.number_format($reg[0]['dineroefectivo'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Diferencia:</strong> <?php echo $simbolo.number_format($reg[0]['diferencia'], 2, '.', ','); ?></td>
  </tr>
  <tr>
    <td><strong>Observaciones:</strong> <?php echo $reg[0]['comentarios'] == '' ? "**********" : $reg[0]['comentarios']; ?></td>
  </tr>
  <?php //} ?>

  <?php if($_SESSION['acceso'] == "administradorG") { ?>
  <tr>
    <td><strong>Sucursal Asignada: </strong> <?php echo $reg[0]['codsucursal'] == "0" ? "**********" : $reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal']; ?></td>
  </tr>
  <?php } ?>
</table>
  
<?php
} 
######################## MOSTRAR ARQUEO EN CAJA EN VENTANA MODAL ########################
?>


<?php
######################## BUSQUEDA ARQUEOS DE CAJA POR FECHAS ########################
if (isset($_GET['BuscaArqueosxFechas']) && isset($_GET['codsucursal']) && isset($_GET['codcaja']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcaja = limpiar($_GET['codcaja']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcaja=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarArqueosxFechas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Arqueos de Cajas por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("ARQUEOSXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("ARQUEOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("ARQUEOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

  <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                              <thead>
                                <tr>
                                  <th>Nº</th>
                                  <th>Horario</th>
                                  <th>Caja</th>
                                  <th>Hora de Apertura</th>
                                  <th>Hora de Cierre</th>
                                  <th>Inicial</th>
                                  <th>Total Ventas</th>
                                  <th>Ventas Efectivo</th>
                                  <th>Ventas Otros</th>
                                  <th>Otros Efectivo</th>
                                  <th>Total Efectivo</th>
                                  <th>Dinero en Caja</th>
                                  <th>Diferencia</th>
                                </tr>
                              </thead>
                              <tbody>
<?php
$TotalVentas = 0;
$VentasEfectivo = 0;
$VentasOtros = 0;
$OtrosEfectivo = 0;
$TotalCreditos = 0;
$AbonosEfectivo = 0;
$AbonosOtros = 0;
$IngresosEfectivo = 0;
$IngresosOtros = 0;
$TotalEgresos = 0;
$PropinasEfectivo = 0;
$PropinasOtros = 0;
$TotalEfectivo = 0;
$TotalCaja = 0;
$TotalDiferencia = 0;

$a=1; 
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalVentas += $reg[$i]['efectivo']+$reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['creditos']+$reg[$i]['propinasefectivo']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'];

$VentasEfectivo += $reg[$i]['efectivo']+$reg[$i]['propinasefectivo'];

$VentasOtros += $reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['creditos']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'];

$OtrosEfectivo += $reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']+$reg[$i]['propinasefectivo'];

$TotalEfectivo += $reg[$i]['montoinicial']+$reg[$i]['efectivo']+$reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']+$reg[$i]['propinasefectivo']-$reg[$i]['egresos'];

$TotalOtros = $reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['creditos']+$reg[$i]['abonosotros']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros']+$reg[$i]['ingresosotros'];


$TotalCreditos += $reg[$i]['creditos'];
$AbonosEfectivo += $reg[$i]['abonosefectivo'];
$AbonosOtros += $reg[$i]['abonosotros'];
$IngresosEfectivo += $reg[$i]['ingresosefectivo'];
$IngresosOtros += $reg[$i]['ingresosotros'];
$TotalEgresos += $reg[$i]['egresos'];
$PropinasEfectivo += $reg[$i]['propinasefectivo'];
$PropinasOtros += $reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'];
$TotalCaja += $reg[$i]['dineroefectivo'];
$TotalDiferencia += $reg[$i]['diferencia'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
  <td><abbr title="<?php echo "Responsable: ".$reg[$i]['nombres']; ?>"><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></abbr></td>
              <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaapertura'])); ?></td>
<td><?php echo $reg[$i]['fechacierre'] == '0000-00-00 00:00:00' ? "******" : date("d-m-Y",strtotime($reg[$i]['fechacierre'])); ?></td>
<td><?php echo $simbolo.number_format($reg[$i]['montoinicial'], 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($reg[$i]['efectivo']+$reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['creditos']+$reg[$i]['propinasefectivo']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'], 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($reg[$i]['efectivo']+$reg[$i]['propinasefectivo'], 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['creditos']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'], 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']+$reg[$i]['propinasefectivo'], 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($reg[$i]['montoinicial']+$reg[$i]['efectivo']+$reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']+$reg[$i]['propinasefectivo']-$reg[$i]['egresos'], 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($reg[$i]['dineroefectivo'], 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($reg[$i]['diferencia'], 2, '.', ','); ?></td>
                                </tr>
        <?php } ?>
         <tr>
           <td colspan="6"></td>
<td><?php echo $simbolo.number_format($TotalVentas, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($VentasEfectivo, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($VentasOtros, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($OtrosEfectivo, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalEfectivo, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalCaja, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDiferencia, 2, '.', ','); ?></td>
         </tr>
                              </tbody>
                          </table>
                      </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  
   }
 } 
######################## BUSQUEDA ARQUEOS DE CAJAS POR FECHAS ########################
?>


<?php 
########################### BUSQUEDA GANANCIAS POR FECHAS ##########################
if (isset($_GET['BuscaGananciasxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DESDE PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA HASTA PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DESDE NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$ingresos = new Login();
$detalle_ingreso = $ingresos->BuscarIngresosxFechas(); 
  
$gastos = new Login();
$detalle_gasto = $gastos->BuscarGastosxFechas(); 

$ganancias = new Login();
$reg = $ganancias->BuscarGananciasxFechas();  
?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Ganancias por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("GANANCIASXFECHAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("GANANCIASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("GANANCIASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nombre de Sucursal: </label> <?php echo $reg[0]['nomsucursal']; ?><br>
      
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div2"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                  <thead>
                    <tr>
                      <th>Nº</th>
                      <th>Código</th>
                      <th>Descripción de Producto</th>
                      <th>Categoria</th>
                      <th>Desc</th>
                      <th>Precio Compra</th>
                      <th>Precio Venta</th>
                      <th>Vendido</th>
                      <th>Total Venta</th>
                      <th>Total Compra</th>
                      <th>Ganancias</th>
                    </tr>
                  </thead>
                  <tbody>
<?php
$PrecioCompraTotal=0;
$PrecioVentaTotal=0;
$VendidosTotal=0;
$CompraTotal=0;
$VentaTotal=0;
$TotalGanancia=0;
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioCompraTotal+=$reg[$i]['preciocompra'];
$PrecioVentaTotal+=$reg[$i]['precioventa'];
$VendidosTotal+=$reg[$i]['cantidad']; 

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

//CALCULO SUBTOTAL IMPUESTOS
$ValorImpuesto = 1 + ($reg[$i]['ivaproducto']/100);
$Discriminado = $reg[$i]['precioventa']/$ValorImpuesto;
$SubtotalDiscriminado = $reg[$i]['precioventa'] - $Discriminado;
$BaseDiscriminado = $SubtotalDiscriminado * $reg[$i]['cantidad'];
$Subtotalimpuestos = number_format($BaseDiscriminado, 2, '.', '');

$SumVenta = $PrecioFinal*$reg[$i]['cantidad']; 
$SumCompra = $reg[$i]['preciocompra']*$reg[$i]['cantidad'];

$CompraTotal+=$reg[$i]['preciocompra']*$reg[$i]['cantidad'];
$VentaTotal+=$PrecioFinal*$reg[$i]['cantidad'];
$TotalGanancia+=$SumVenta-$SumCompra;
?>
            <tr>
            <td><?php echo $a++; ?></div></td>
            <td><?php echo $reg[$i]['codproducto']; ?></td>
            <td><?php echo $reg[$i]['producto']; ?></td>
            <td><?php if($reg[$i]['tipo'] == 1){
              echo $reg[$i]['nomcategoria'];
            } elseif($reg[$i]['tipo'] == 2){
              echo "**********";
            } else {
              echo $reg[$i]['nommedida'];
            } ?></td>
            <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
            <td><?php echo $simbolo.number_format($reg[$i]["preciocompra"], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
            <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
            </tr>
            <?php  }  ?>
            <tr class="text-dark alert-link">
              <td colspan="7"></td>
              <td><?php echo number_format($VendidosTotal, 2, '.', ','); ?></td>
              <td><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></td>
              <td><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></td>
              <td><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></td>
            </tr>
            <tr class="text-dark alert-link">
              <td colspan="8"></td>
              <td colspan="2">INGRESOS ADICIONALES</td>
              <td><?php echo $simbolo.number_format($detalle_ingreso[0]['ingresos'], 2, '.', ','); ?></td>
            </tr>
            <tr class="text-dark alert-link">
              <td colspan="8"></td>
              <td colspan="2">GASTOS</td>
              <td><?php echo $simbolo.number_format($detalle_gasto[0]['gastos'], 2, '.', ','); ?></td>
            </tr>
            <tr class="text-dark alert-link">
              <td colspan="8"></td>
              <td colspan="2">TOTAL</td>
              <td><?php echo $simbolo.number_format($TotalGanancia+$detalle_ingreso[0]['ingresos']-$detalle_gasto[0]['gastos'], 2, '.', ','); ?></td>
            </tr>
            </tbody>
            </table>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA GANANCIAS POR FECHAS ##########################
?>















<?php
###################### MOSTRAR MOVIMIENTO EN CAJA EN VENTANA MODAL #######################
if (isset($_GET['BuscaMovimientoModal']) && isset($_GET['numero']) && isset($_GET['codsucursal'])) {

$reg = $new->MovimientosPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

  ?>
  
  <table class="table-responsive" border="0" class="text-center">
  <tr>
    <td><strong>Horario de Caja:</strong> <?php echo $reg[0]['hora_desde']." - ".$reg[0]['hora_hasta']; ?></td>
  </tr>
  <tr>
    <td><strong>Nombre de Caja:</strong> <?php echo $reg[0]['nrocaja'].": ".$reg[0]['nomcaja']; ?></td>
  </tr>
  <tr>
    <td><strong>Tipo de Movimiento:</strong> <?php echo $tipo = ( $reg[0]['tipomovimiento'] == "INGRESO" ? "<span class='badge badge-success'><i class='fa fa-check'></i> INGRESO</span>" : "<span class='badge badge-dark'><i class='fa fa-times'></i> EGRESO</span>"); ?></td>
  </tr>
  <tr>
    <td><strong>Descripción de Movimiento:</strong> <?php echo $reg[0]['descripcionmovimiento']; ?></td>
  </tr>
  <tr>
    <td><strong>Monto de Movimiento:</strong> <?php echo $simbolo.number_format($reg[0]['montomovimiento'], 2, '.', ','); ?></td>
    </tr>
  <tr>
    <td><strong>Medio de Movimiento:</strong> <?php echo $reg[0]['mediomovimiento']; ?></td>
  </tr>
  <tr>
    <td><strong>Fecha Movimiento:</strong> <?php echo date("d-m-Y H:i:s",strtotime($reg[0]['fechamovimiento'])); ?></td>
  </tr>
  <tr>
    <td><strong>Responsable:</strong> <?php echo $reg[0]['dni'].": ".$reg[0]['nombres']; ?></td>
  </tr>
  <?php if($_SESSION['acceso'] == "administradorG") { ?>
  <tr>
    <td><strong>Sucursal Asignada: </strong> <?php echo $reg[0]['codsucursal'] == "0" ? "**********" : $reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal']; ?></td>
  </tr>
  <?php } ?>
</table>
  
  <?php
   } 
###################### MOSTRAR MOVIMIENTO EN CAJA EN VENTANA MODAL ######################
?>

<?php
######################## BUSQUEDA MOVIMIENTOS DE CAJA POR FECHAS ########################
if (isset($_GET['BuscaMovimientosxFechas']) && isset($_GET['codsucursal']) && isset($_GET['codcaja']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcaja = limpiar($_GET['codcaja']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else if($codcaja=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarMovimientosxFechas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Movimientos en Cajas por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("MOVIMIENTOSXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("MOVIMIENTOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("MOVIMIENTOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

  <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                              <thead>
                                <tr>
                                  <th>Nº</th>
                                  <th>Horario</th>
                                  <th>Nº de Caja</th>
                                  <th>Responsable</th>
                                  <th>Tipo Movimiento</th>
                                  <th>Descripción</th>
                                  <th>Monto</th>
                                  <th>Medio de Movimiento</th>
                                  <th>Fecha Movimiento</th>
                                </tr>
                              </thead>
                              <tbody>
<?php
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
?>
  <tr>
    <td><?php echo $a++; ?></td>
    <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
    <td><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></td>
    <td><?php echo $reg[$i]['nombres']; ?></td>
    <td><?php echo $tipo = ( $reg[$i]['tipomovimiento'] == 'INGRESO' ? "<span class='badge badge-info'><i class='fa fa-check'></i> ".$reg[$i]['tipomovimiento']."</span>" : "<span class='badge badge-danger'><i class='fa fa-times'></i> ".$reg[$i]['tipomovimiento']."</span>"); ?></td>
    <td><?php echo $reg[$i]['descripcionmovimiento']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['montomovimiento'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['mediomovimiento']; ?></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechamovimiento'])); ?></td>
                                </tr>
                        <?php  }  ?>
                              </tbody>
                          </table>
                      </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  
   }
 } 
######################## BUSQUEDA MOVIMIENTOS DE CAJAS POR FECHAS ########################
?>


<?php
######################## BUSQUEDA INFORMES CAJAS POR FECHAS ########################
if (isset($_GET['BuscaInformesCajasxFechas']) && isset($_GET['codsucursal']) && isset($_GET['codcaja']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcaja = limpiar($_GET['codcaja']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else if($codcaja=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {


$caja = new Login();
$caja = $caja->CajasPorId();
$simbolo = ($caja[0]['simbolo'] == "" ? "" : "<strong>".$caja[0]['simbolo']."</strong>");

$venta = new Login();
$venta = $venta->SumarVentasCajasxFechas();

$arqueo = new Login();
$arqueo = $arqueo->SumarArqueosCajasxFechas();

$balance = $venta[0]['totalventa']-$venta[0]['totaliva']+$arqueo[0]['totalingresos']+$arqueo[0]['totalabonos'];
$ganancias = $balance-$arqueo[0]['totalegresos'];
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Informe de Cajas por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("INFORMECAJASXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("INFORMECAJASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("INFORMECAJASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
                <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

                <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
            </div>
          </div><hr>

          <div class="row">
            <table border="0" class="table2 table-striped table-bordered border display">
              <tr>
                <td class="text-left text-dark alert-link">TOTAL DE VENTAS</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($venta[0]['totalventa'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">TOTAL DE INGRESOS</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($arqueo[0]['totalingresos'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">ABONOS A CRÉDITOS</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($arqueo[0]['totalabonos'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">TOTAL DE GASTOS (EGRESOS)</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($arqueo[0]['totalegresos'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">TOTAL DE IMPUESTOS DE VENTAS <?php echo $impuesto; ?> (<?php echo $valor; ?>%)</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($venta[0]['totaliva'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">EFECTIVO EN CAJA SIN IMPUESTO</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($ganancias, 2, '.', ','); ?></td>
              </tr>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  
   }
 } 
######################## BUSQUEDA INFORMES CAJAS POR FECHAS ########################
?>




























<?php
######################## MOSTRAR PEDIDOS EN VENTANA MODAL ########################
if (isset($_GET['BuscaPedidoModal']) && isset($_GET['codventa']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->VentasPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

  if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PEDIDOS Y DETALLES ACTUALMENTE </center>";
    echo "</div>";    

} else {
?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pull-left">
                                        <address>
  <h4><b class="text-danger">RAZÓN SOCIAL</b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomsucursal']; ?>,
  <br/> Nº <?php echo $reg[0]['documsucursal'] == '0' ? "DOCUMENTO" : $reg[0]['documento']; ?>: <?php echo $reg[0]['cuitsucursal']; ?> - TLF: <?php echo $reg[0]['tlfsucursal']; ?></p>

  <h4><b class="text-danger">Nº <?php echo $reg[0]['tipodocumento'].": ".$reg[0]['codfactura']; ?></b></h4>
  <p class="text-muted m-l-5">Nº SERIE: <?php echo $reg[0]['codserie']; ?>

  <br>Nº DE CAJA: <?php echo $reg[0]['nrocaja'].": ".$reg[0]['nomcaja']; ?>

  <br>ESTADO DE PEDIDO: 
  <?php echo $reg[0]["statuspedido"] == 0 ? "<span class='badge badge-success'><i class='fa fa-check'></i> PAGADA</span>" : "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> PENDIENTE</span>"; ?>
  
  <br>FECHA DE ENTREGA: <?php echo date("d-m-Y",strtotime($reg[0]['fechaentrega'])); ?>
  <br>HORA DE ENTREGA: <?php echo date("H:i:s",strtotime($reg[0]['fechaentrega'])); ?>

  <br>FECHA DE EMISIÓN: <?php echo date("d-m-Y H:i:s",strtotime($reg[0]['fechaventa'])); ?></p>
                                        </address>
                                    </div>
                                    <div class="pull-right text-right">
                                        <address>
  <h4><b class="text-danger">CLIENTE</b></h4>
  <p class="text-muted m-l-30"><?php echo $reg[0]['nomcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['nomcliente']; ?>,
  <br/>DIREC: <?php echo $reg[0]['direccliente'] == '' ? "******" : $reg[0]['direccliente']; ?> <?php echo $reg[0]['provincia2'] == '' ? "" : $reg[0]['provincia2']; ?> <?php echo $reg[0]['departamento2'] == '' ? "" : $reg[0]['departamento2']; ?>
  <br/> EMAIL: <?php echo $reg[0]['emailcliente'] == '' ? "******" : $reg[0]['emailcliente']; ?>
  <br/> Nº <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: <?php echo $reg[0]['dnicliente'] == '' ? "******" : $reg[0]['dnicliente']; ?> - TLF: <?php echo $reg[0]['tlfcliente'] == '' ? "******" : $reg[0]['tlfcliente']; ?></p>
                                            
                                        </address>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="table-responsive m-t-10" style="clear: both;">
                                        <table class="table2 table-hover">
                               <thead>
                        <tr>
                        <th>#</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
<?php if ($_SESSION['acceso'] == "administradorS") { ?><th><span class="mdi mdi-drag-horizontal"></span></th><?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesVentas();

$SubTotal = 0;
$a=1;
for($i=0;$i<sizeof($detalle);$i++){  
$SubTotal += $detalle[$i]['valorneto'];
?>
                                                <tr>
      <td><label><?php echo $a++; ?></label></td>
      <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
      <small class="text-danger alert-link"><?php echo $detalle[$i]['codcategoria'] == '0' ? "**********" : $detalle[$i]['nomcategoria'] ?></small></td>
      <td><?php echo $detalle[$i]['cantventa']; ?></td>
      <td><?php echo $simbolo.number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?><sup><strong><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ','); ?>%</strong></sup></td>
      <td><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
      <td><?php echo $simbolo.number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></td>
 <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
<button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDetallesVentaModal('<?php echo encrypt($detalle[$i]["coddetalleventa"]); ?>','<?php echo encrypt($detalle[$i]["codventa"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[0]["codcliente"]); ?>','<?php echo encrypt("DETALLESPEDIDOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                                </tr>
                                      <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>


                                <div class="col-md-12">

                                    <div class="pull-right text-right">
<p><b>Subtotal:</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi']+$reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Total Grabado (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?><p>
<p><b>Total Exento (0%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Total <?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaliva'], 2, '.', ','); ?> </p>
<p><b>Descontado %:</b> <?php echo $simbolo.number_format($reg[0]['descontado'], 2, '.', ','); ?> </p>
<p><b>Desc. Global (<?php echo $reg[0]['descuento']; ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaldescuento'], 2, '.', ','); ?> </p>
<p><b>Costo Delivery:</b> <?php echo $simbolo.number_format($reg[0]['montodelivery'], 2, '.', ','); ?> </p>
<p><b>Total Propina:</b> <?php echo $simbolo.number_format($reg[0]['montopropina'], 2, '.', ','); ?> </p>
                                        <hr>
<h4><b>Importe Total:</b> <?php echo $simbolo.number_format($reg[0]['totalpago'], 2, '.', ','); ?></h4>

<?php if($reg[0]["tipopago"] == "CREDITO"){ ?>
<h4><b>Total Pagado:</b> <?php echo $simbolo.number_format($reg[0]['creditopagado'], 2, '.', ','); ?></h4>

<h4><b>Total Pendiente:</b> <?php echo $simbolo.number_format(($reg[0]['totalpago']+$reg[0]['totalpropina'])-$reg[0]['creditopagado'], 2, '.', ','); ?></h4>
<?php } ?>

</div>
                                    <div class="clearfix"></div>
                                    <hr>

                                <div class="col-md-12">
                                    <div class="text-right">
 <?php if($reg[0]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[0]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[0]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[0]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button id="print" class="btn waves-light btn-light" type="button"><span><i class="fa fa-print"></i> Imprimir</span></button></a><?php } ?>
 <button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
                                    </div>
                                </div>
                            </div>
                <!-- .row -->
<?php
  }
} 
######################## MOSTRAR PEDIDOS EN VENTANA MODAL ########################
?>

<?php
######################## MOSTRAR DETALLES DE PEDIDOS UPDATE ########################
if (isset($_GET['MuestraDetallesPedidosUpdate']) && isset($_GET['codventa']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->VentasPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");
?>

<!--############################## MODAL PARA ACTUALIZAR VENTA ######################################-->
<!-- sample modal content -->
<div id="myModalPago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Actualizar Venta</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <!--<form class="form form-material" name="cerrardelivery" id="cerrardelivery" action="#">-->

        <div class="modal-body">

        <div class="row">
            <div class="col-md-4">
                <h4 class="mb-0 font-light">Total a Pagar</h4>
                <h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextImporte" name="TextImporte"><?php echo number_format($reg[0]['totalpago']+$reg[0]['totalpropina'], 2, '.', ','); ?></label></h4>
            </div>

            <div class="col-md-4">
                <h4 class="mb-0 font-light">Total Recibido</h4>
                <h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextPagado" name="TextPagado"><?php echo number_format($reg[0]['montopagado']+$reg[0]['montopagado2'], 2, '.', ','); ?></label></h4>
            </div>

            <div class="col-md-4">
                <h4 class="mb-0 font-light">Total Cambio</h4>
                <h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextCambio" name="TextCambio"><?php echo $cambio = ($reg[0]['tipopago'] == 'CREDITO' ? "0.00" : number_format($reg[0]['totalpago']+$reg[0]['totalpropina']-($reg[0]['montopagado']+$reg[0]['montopagado2']), 2, '.', ',')); ?></label></h4>
            </div>
        </div>
             
        <div class="row">
            <div class="col-md-8">
                <h4 class="mb-0 font-light">Nombre del Cliente</h4>
                <h4 class="mb-0 font-medium"> <label id="TextCliente" name="TextCliente"><?php echo $reg[0]['codcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['documento'].": ".$reg[0]['dnicliente'].": ".$reg[0]['nomcliente']; ?></label></h4>
            </div>

            <div class="col-md-4">
                <h4 class="mb-0 font-light">Limite de Crédito</h4>
                <h4 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextCredito" name="TextCredito"><?php echo $reg[0]['codcliente'] == '' ? "0.00" : number_format($reg[0]['creditodisponible'], 2, '.', ','); ?></label></h4>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">Tipo de Documento: <span class="symbol required"></span></label><br>
                    <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="ticket" name="tipodocumento" value="TICKET" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "TICKET") { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?> disabled="">
                    <label class="custom-control-label" for="ticket">TICKET</label>
                    </div>
                </div>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="boleta" name="tipodocumento" value="BOLETA" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "BOLETA") { ?> checked="checked" <?php } ?> disabled="">
                    <label class="custom-control-label" for="boleta">BOLETA</label>
                    </div>
                </div><br>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="factura" name="tipodocumento" value="FACTURA" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "FACTURA") { ?> checked="checked" <?php } ?> disabled="">
                    <label class="custom-control-label" for="factura">FACTURA</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
               <div class="form-group">
               <label class="control-label">Condición de Pago: <span class="symbol required"></span></label>
               <div class="custom-control custom-radio">
               <input type="radio" class="custom-control-input" id="contado" name="tipopago" value="CONTADO" onClick="CargaCondicionesPagos()" <?php if (isset($reg[0]['tipopago']) && $reg[0]['tipopago'] == "CONTADO") { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?> disabled="">
               <label class="custom-control-label" for="contado">CONTADO</label>
               </div>

               <div class="custom-control custom-radio">
               <input type="radio" class="custom-control-input" id="credito" name="tipopago" value="CREDITO" onClick="CargaCondicionesPagos()" <?php if (isset($reg[0]['tipopago']) && $reg[0]['tipopago'] == "CREDITO") { ?> checked="checked" <?php } ?> disabled="">
               <label class="custom-control-label" for="credito">CRÉDITO</label>
               </div>
            </div>
            </div>

            <div class="col-md-4"> 
                <div class="form-group has-feedback"> 
                <label class="control-label">Costo Delivery: <span class="symbol required"></span></label>
                <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montodelivery" id="montodelivery" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Costo Delivery" value="<?php echo number_format($reg[0]['montodelivery'], 2, '.', ''); ?>" <?php if ($reg[0]['repartidor'] == 0) { ?> disabled="" <?php } ?> required="" aria-required="true"> 
                <i class="fa fa-dollar form-control-feedback"></i>
                </div> 
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">Estado de Pedido: <span class="symbol required"></span></label>
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="0" name="statuspedido" value="0" <?php if (isset($reg[0]['statuspedido']) && $reg[0]['statuspedido'] == 0) { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?>>
                    <label class="custom-control-label" for="0">ENTREGADO</label>
                    </div>

                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="1" name="statuspedido" value="1" <?php if (isset($reg[0]['statuspedido']) && $reg[0]['statuspedido'] == 1) { ?> checked="checked" <?php } ?>>
                    <label class="custom-control-label" for="1">PENDIENTE</label>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Fecha de Entrega: <span class="symbol required"></span></label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control expira" name="fechaentrega" id="fechaentrega" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Fecha de Entrega" autocomplete="off" value="<?php echo date("d-m-Y",strtotime($reg[0]['fechaentrega'])); ?>" required="" aria-required="true"/>
                    <i class="fa fa-calendar form-control-feedback"></i>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group has-feedback">
                    <label class="control-label">Hora de Entrega: <span class="symbol required"></span></label>
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control" name="horaentrega" id="horaentrega" onKeyUp="this.value=this.value.toUpperCase();" placeholder="Ingrese Hora de Entrega" autocomplete="off" value="<?php echo date("H:i",strtotime($reg[0]['fechaentrega'])); ?>" required="" aria-required="true"/>
                    <i class="fa fa-calendar form-control-feedback"></i>
                </div>
            </div>
        </div>

        <div id="muestra_condiciones"><!-- IF CONDICION PAGO -->

        <?php if (isset($reg[0]['tipopago']) && $reg[0]['tipopago'] == "CONTADO") { ?>

        <div class="row">

        <!-- .col -->
        <div class="col-md-6">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 1</h4><hr>
            
        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Forma de Pago Nº 1: <span class="symbol required"></span></label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <input type="hidden" name="montopropina" id="montopropina" value="0.00">
                    <select style="color:#000;font-weight:bold;" name="formapago" id="formapago" class="form-control" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="EFECTIVO"<?php if (!(strcmp('EFECTIVO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>EFECTIVO</option>
                    <option value="CHEQUE"<?php if (!(strcmp('CHEQUE', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>CHEQUE</option>
                    <option value="TARJETA DE CREDITO"<?php if (!(strcmp('TARJETA DE CREDITO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TARJETA DE CRÉDITO</option>
                    <option value="TARJETA DE DEBITO"<?php if (!(strcmp('TARJETA DE DEBITO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TARJETA DE DÉBITO</option>
                    <option value="TARJETA PREPAGO"<?php if (!(strcmp('TARJETA PREPAGO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TARJETA PREPAGO</option>
                    <option value="TRANSFERENCIA"<?php if (!(strcmp('TRANSFERENCIA', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TRANSFERENCIA</option>
                    <option value="DINERO ELECTRONICO"<?php if (!(strcmp('DINERO ELECTRONICO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>DINERO ELECTRÓNICO</option>
                    <option value="CUPON"<?php if (!(strcmp('CUPON', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>CUPÓN</option>
                    <option value="OTROS"<?php if (!(strcmp('OTROS', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>OTROS</option>
                  </select>
                </div> 
            </div>
        </div>

        <div class="row">
            <div class="col-md-12"> 
                    <div class="form-group has-feedback"> 
                    <label class="control-label">Monto de Pago Nº 1: <span class="symbol required"></span></label>
                    <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
                    <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="<?php echo number_format($reg[0]['montopagado'], 2, '.', ''); ?>" required="" aria-required="true"> 
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
                    <select style="color:#000;font-weight:bold;" name="formapago2" id="formapago2" class="form-control" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="EFECTIVO"<?php if (!(strcmp('EFECTIVO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>EFECTIVO</option>
                    <option value="CHEQUE"<?php if (!(strcmp('CHEQUE', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>CHEQUE</option>
                    <option value="TARJETA DE CREDITO"<?php if (!(strcmp('TARJETA DE CREDITO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TARJETA DE CRÉDITO</option>
                    <option value="TARJETA DE DEBITO"<?php if (!(strcmp('TARJETA DE DEBITO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TARJETA DE DÉBITO</option>
                    <option value="TARJETA PREPAGO"<?php if (!(strcmp('TARJETA PREPAGO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TARJETA PREPAGO</option>
                    <option value="TRANSFERENCIA"<?php if (!(strcmp('TRANSFERENCIA', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TRANSFERENCIA</option>
                    <option value="DINERO ELECTRONICO"<?php if (!(strcmp('DINERO ELECTRONICO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>DINERO ELECTRÓNICO</option>
                    <option value="CUPON"<?php if (!(strcmp('CUPON', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>CUPÓN</option>
                    <option value="OTROS"<?php if (!(strcmp('OTROS', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>OTROS</option>
                  </select>
               </div> 
            </div>
        </div>

        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Monto de Pago Nº 2: </label>
                    <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado2" id="montopagado2" onKeyUp="DevolucionPedido();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="<?php echo number_format($reg[0]['montopagado2'], 2, '.', ''); ?>" <?php if ($reg[0]['formapago2'] == "" || $reg[0]['formapago2'] == 0) { ?> disabled="" <?php } ?> required="" aria-required="true"> 
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
                <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"><?php echo $reg[0]['observaciones']; ?></textarea>
                <i class="fa fa-comment-o form-control-feedback2"></i> 
              </div> 
            </div>
         </div> 

        <?php } else { ?>

        <div class="row">
            <div class="col-md-4"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
                    <input type="hidden" name="formapago" id="formapago" value="">
                    <input type="hidden" name="montopagado" id="montopagado" value="0.00">
                    <input type="hidden" name="formapago2" id="formapago2" value="">
                    <input type="hidden" name="montopagado2" id="montopagado2" value="0.00">
                    <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
                    <input type="hidden" name="montopropina" id="montopropina" value="0.00">
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control vencecredito" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off"  value="<?php echo date("d-m-Y",strtotime($reg[0]['fechavencecredito'])); ?>" placeholder="Ingrese Fecha Vence Crédito" aria-required="true">
                    <i class="fa fa-calendar form-control-feedback"></i>  
                </div> 
            </div>

            <div class="col-md-8"> 
                <div class="form-group has-feedback2"> 
                    <label class="control-label">Observaciones: </label> 
                    <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"><?php echo $reg[0]['observaciones']; ?></textarea>
                    <i class="fa fa-comment-o form-control-feedback2"></i> 
                </div> 
            </div>
         </div> 

        <?php } ?>

        </div><!-- END CONDICION PAGO -->

        <div class="row">
            <div class="col-md-6">
                <span id="submit_guardar"><button type="submit" name="btn-update" id="btn-update" class="btn btn-warning btn-lg btn-block waves-effect waves-light"><span class="fa fa-print"></span> Facturar e Imprimir</button></span>
            </div>
            <div class="col-md-6">
                <button type="reset" class="btn btn-dark btn-lg btn-block waves-effect waves-light" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cancelar</button>
            </div>
        </div>

        </div>
        <!--</form>-->

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA ACTUALIZAR VENTA ######################################-->

<div class="table-responsive m-t-20">
            <table class="table2 table-hover">
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Precio Unit.</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
<?php if ($_SESSION['acceso'] == "administradorS") { ?><th><span class="mdi mdi-drag-horizontal"></span></th><?php } ?>
                    </tr>
                </thead>
                <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesVentas();
$a=1;
$count = 0;
for($i=0;$i<sizeof($detalle);$i++){ 
$count++;
?>
    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
      
    <td>
    <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
    <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetallePedido('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
    <input type="text" class="bold" name="cantventa[]" id="cantventa_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoPedido(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="<?php echo number_format($detalle[$i]["cantventa"], 2, '.', ''); ?>" title="Ingrese Cantidad">
    <input type="hidden" name="cantidadventabd[]" id="cantidadventabd_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["cantventa"], 2, '.', ''); ?>">
    <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetallePedido('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
    </div>
    </td>
      
    <td class="text-danger alert-link">
    <input type="hidden" name="coddetalleventa[]" id="coddetalleventa" value="<?php echo $detalle[$i]["coddetalleventa"]; ?>">
    <input type="hidden" name="codproducto[]" id="codproducto" value="<?php echo $detalle[$i]["codproducto"]; ?>">
    <input type="hidden" name="tipo[]" id="tipo" value="<?php echo $detalle[$i]["tipo"]; ?>">
    <input type="hidden" class="preciocompra" name="preciocompra[]" id="preciocompra_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["preciocompra"], 2, '.', ''); ?>">
    <?php if($detalle[$i]['tipo'] == 1){
        echo "PRODUCTO";
    } elseif($detalle[$i]['tipo'] == 2){
        echo "COMBO";
    } else {
        echo "EXTRA";
    } ?></td>
      
    <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
    <small class="text-danger alert-link"><?php echo $detalle[$i]['detallesobservaciones'] == '' ? "**********" : $detalle[$i]['detallesobservaciones'] ?></small></td>
      
    <td><strong><input type="hidden" name="precioventa[]" id="precioventa_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>">
    <input type="hidden" name="precioconiva[]" id="precioconiva_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? "0.00" : number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>"><?php echo number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></strong></td>

    <td><input type="hidden" name="valortotal[]" id="valortotal_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["valortotal"], 2, '.', ''); ?>"><label id="txtvalortotal_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></label></td>
      
    <td><input type="hidden" name="descproducto[]" id="descproducto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["descproducto"], 2, '.', ''); ?>">
    <input type="hidden" class="totaldescuentov" name="totaldescuentov[]" id="totaldescuentov_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["totaldescuentov"], 2, '.', ''); ?>">
    <label id="txtdescproducto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?></label><sup><label><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ','); ?>%</label></sup></td>

    <td><input type="hidden" name="ivaproducto[]" id="ivaproducto_<?php echo $count; ?>" value="<?php echo $detalle[$i]["ivaproducto"]; ?>"><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', '')."%" : "(E)"; ?></td>

    <td><input type="hidden" class="subtotalivasi" name="subtotalivasi[]" id="subtotalivasi_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalivano" name="subtotalivano[]" id="subtotalivano_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalimpuestos" name="subtotalimpuestos[]" id="subtotalimpuestos_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['subtotalimpuestos'], 2, '.', ''); ?>">

    <input type="hidden" class="subtotaldiscriminado" name="subtotaldiscriminado[]" id="subtotaldiscriminado_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto']-$detalle[$i]['subtotalimpuestos'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="valorneto" name="valorneto[]" id="valorneto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto'], 2, '.', ''); ?>" >

    <input type="hidden" class="valorneto2" name="valorneto2[]" id="valorneto2_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto2'], 2, '.', ''); ?>" >

    <label id="txtvalorneto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></label></td>
 <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
<button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDetallesPedidoUpdate('<?php echo encrypt($detalle[$i]["coddetalleventa"]); ?>','<?php echo encrypt($detalle[$i]["codventa"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[0]["codcliente"]); ?>','<?php echo encrypt("DETALLESPEDIDOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>

                                 </tr>
                     <?php } ?>
                </tbody>
            </table><hr>

             <table id="carritototal" class="table-responsive">
            <tr>
    <td width="250"><h5><label>Gravado (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal"><?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdiscriminado" id="txtdiscriminado" value="<?php echo number_format($reg[0]['subtotalivasi'], 0, '.', ''); ?>"/>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="<?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ''); ?>"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    
    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2"><?php echo number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="<?php echo number_format($reg[0]['subtotalivano'], 2, '.', ''); ?>"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo $reg[0]['iva'] ?>"></label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva"><?php echo number_format($reg[0]['totaliva'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtIva" id="txtIva" value="<?php echo number_format($reg[0]['totaliva'], 2, '.', ''); ?>"/>
    </td>
    </tr>
    <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado"><?php echo number_format($reg[0]['descontado'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdescontado" id="txtdescontado" value="<?php echo number_format($reg[0]['descontado'], 2, '.', ''); ?>"/>
        </td>
    
    <td>
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" style="border-radius:4px;height:30px;width:60px;" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['descuento'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento"><?php echo number_format($reg[0]['totaldescuento'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="<?php echo number_format($reg[0]['totaldescuento'], 2, '.', ''); ?>"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal"><?php echo number_format($reg[0]['totalpago'], 2, '.', ','); ?></label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtFactura" id="txtFactura" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtImporte" id="txtImporte" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtTotalCompra" id="txtTotalCompra" value="<?php echo number_format($reg[0]['totalpago2'], 2, '.', ''); ?>"/>    </td>
                    </tr>
    </table>
        </div>
<?php
  } 
######################## MOSTRAR DETALLES DE PEDIDOS UPDATE ########################
?>

<?php
######################## BUSQUEDA PEDIDOS POR CAJAS ########################
if (isset($_GET['BuscaPedidosxCajas']) && isset($_GET['codsucursal']) && isset($_GET['codcaja']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcaja = limpiar($_GET['codcaja']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcaja=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarPedidosxCajas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Pedidos por Cajas </h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("PEDIDOSXCAJAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("PEDIDOSXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("PEDIDOSXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de Caja: </label> <?php echo $reg[0]['nrocaja']; ?><br>

            <label class="control-label">Nombre de Caja: </label> <?php echo $reg[0]['nomcaja']; ?><br>

            <label class="control-label">Horario de Caja: </label> <?php echo $reg[0]['hora_desde']." - ".$reg[0]['hora_hasta']; ?><br>

            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Descripción de Cliente</th>
                              <th>Tipo Pago</th>
                              <th>Estado</th>
                              <th>Fecha Emisión</th>
                              <th>Nº de Articulos</th>
                              <th>Imp. Total</th>
                              <th>Total Abono</th>
                              <th>Total Debe</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalImporte+= $reg[$i]['totalpago'];
$TotalAbono+= $reg[$i]['creditopagado'];
$TotalDebe+= ($reg[$i]['tipopago'] == 'CONTADO' ? "0.00" : $reg[$i]['totalpago']-$reg[$i]['creditopagado']);
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>

  <td><?php echo $reg[$i]["tipopago"]; ?></td>
  <td><?php echo $reg[$i]["statuspedido"] == 0 ? "ENTREGADA" : "PENDIENTE"; ?></td>

  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>

  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php echo $reg[$i]['tipopago'] == 'CONTADO' ? $simbolo."0.00" : $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php if($reg[$i]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a><?php } else { ?><span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ANULADA</span><?php } ?></td>
        </tr>
        <?php } ?>
        <tr>
        <td colspan="6"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></td>
         </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA PEDIDOS POR CAJAS ########################
?>


<?php
######################## BUSQUEDA PEDIDOS POR FECHAS ########################
if (isset($_GET['BuscaPedidosxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarPedidosxFechas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Pedidos por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("PEDIDOSXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("PEDIDOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("PEDIDOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Descripción de Cliente</th>
                              <th>Tipo Pago</th>
                              <th>Estado</th>
                              <th>Fecha Emisión</th>
                              <th>Nº de Articulos</th>
                              <th>Imp. Total</th>
                              <th>Total Abono</th>
                              <th>Total Debe</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalImporte+= $reg[$i]['totalpago'];
$TotalAbono+= $reg[$i]['creditopagado'];
$TotalDebe+= ($reg[$i]['tipopago'] == 'CONTADO' ? "0.00" : $reg[$i]['totalpago']-$reg[$i]['creditopagado']);
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php echo $reg[$i]["tipopago"]; ?></td>
  <td><?php echo $reg[$i]["statuspedido"] == 0 ? "ENTREGADA" : "PENDIENTE"; ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php echo $reg[$i]['tipopago'] == 'CONTADO' ? $simbolo."0.00" : $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php if($reg[$i]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a><?php } else { ?><span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ANULADA</span><?php } ?></td>
    </tr>
    <?php } ?>
    <tr>
    <td colspan="6"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></td>
    </tr>
            </tbody>
          </table>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA PEDIDOS POR FECHAS ########################
?>


<?php
######################## BUSQUEDA PEDIDOS POR FECHAS DE ENTREGA ########################
if (isset($_GET['BuscaPedidosxFechasEntrega']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarPedidosxFechasEntrega();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Pedidos por Fechas de Entrega</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("PEDIDOSXFECHASENTREGA"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("PEDIDOSXFECHASENTREGA"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("PEDIDOSXFECHASENTREGA"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Descripción de Cliente</th>
                              <th>Tipo Pago</th>
                              <th>Estado</th>
                              <th>Fecha Emisión</th>
                              <th>Fecha Entrega</th>
                              <th>Hora Entrega</th>
                              <th>Detalles de Articulos</th>
                              <th>Imp. Total</th>
                              <th>Total Abono</th>
                              <th>Total Debe</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalImporte+= $reg[$i]['totalpago'];
$TotalAbono+= $reg[$i]['creditopagado'];
$TotalDebe+= ($reg[$i]['tipopago'] == 'CONTADO' ? "0.00" : $reg[$i]['totalpago']-$reg[$i]['creditopagado']);
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php echo $reg[$i]["tipopago"]; ?></td>
  <td><?php echo $reg[$i]["statuspedido"] == 0 ? "ENTREGADA" : "PENDIENTE"; ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaentrega'])); ?></td>
  <td><?php echo date("H:i:s",strtotime($reg[$i]['fechaentrega'])); ?></td>
  <td class="font-10 bold"><?php echo $reg[$i]['detalles_productos']; ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php echo $reg[$i]['tipopago'] == 'CONTADO' ? $simbolo."0.00" : $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php if($reg[$i]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a><?php } else { ?><span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ANULADA</span><?php } ?></td>
        </tr>
        <?php } ?>
        <tr>
          <td colspan="9"></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></td>
        </tr>
                </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA PEDIDOS POR FECHAS DE ENTREGA ########################
?>


















<?php
######################## MOSTRAR VENTAS EN VENTANA MODAL ########################
if (isset($_GET['BuscaVentaModal']) && isset($_GET['codventa']) && isset($_GET['codsucursal']) && isset($_GET['modulo'])) { 
$reg = $new->VentasPorId();
$modulo = $_GET['modulo'];
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

  if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON VENTAS Y DETALLES ACTUALMENTE </center>";
    echo "</div>";    

} else {
?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pull-left">
                                        <address>
  <h4><b class="text-danger">RAZÓN SOCIAL</b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomsucursal']; ?>,
  <br/> Nº <?php echo $reg[0]['documsucursal'] == '0' ? "DOCUMENTO" : $reg[0]['documento']; ?>: <?php echo $reg[0]['cuitsucursal']; ?> - TLF: <?php echo $reg[0]['tlfsucursal']; ?></p>

  <h4><b class="text-danger">Nº <?php echo $reg[0]['tipodocumento'].": ".$reg[0]['codfactura']; ?></b></h4>
  <p class="text-muted m-l-5">Nº SERIE: <?php echo $reg[0]['codserie']; ?>

  <?php if($reg[0]['codmesa']!= '0') { ?>
  <br><?php echo $reg[0]['nomsala'].": ".$reg[0]['nommesa']; ?>
  <?php } ?>

  <br>Nº DE CAJA: <?php echo $caja = ($reg[0]['codcaja'] == "0" ? "********" : $reg[0]['nrocaja'].": ".$reg[0]['nomcaja']); ?>
  <br>NOMBRE DE CAJERO: <?php echo $cajero = ($reg[0]['codcaja'] == "0" ? "********" : $reg[0]['nombres']); ?>
  
  <?php if($reg[0]['fechavencecredito']!= "0000-00-00") { ?>
  <br>DIAS VENCIDOS: 
  <?php if($reg[0]['fechavencecredito']== '0000-00-00') { echo "0"; } 
        elseif($reg[0]['fechavencecredito'] >= date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo "0"; } 
        elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[0]['fechavencecredito']); }
        elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[0]['fechapagado'],$reg[0]['fechavencecredito']); } ?>
  <?php } ?>

  <br>STATUS: 
  <?php if($reg[0]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[0]["statusventa"]."</span>"; } 
      elseif($reg[0]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[0]["statusventa"]."</span>"; }
      elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado'] == "0000-00-00" && $reg[0]['statusventa'] == "PENDIENTE" && $reg[0]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
      else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[0]["statusventa"]."</span>"; } ?>
  
  <?php if($reg[0]['fechapagado']!= "0000-00-00") { ?>
  <br>FECHA PAGADA: <?php echo date("d-m-Y",strtotime($reg[0]['fechapagado'])); ?>
  <?php } ?>

  <br>FECHA DE EMISIÓN: <?php echo date("d-m-Y H:i:s",strtotime($reg[0]['fechaventa'])); ?></p>
                                        </address>
                                    </div>
                                    <div class="pull-right text-right">
                                        <address>
  <h4><b class="text-danger">CLIENTE</b></h4>
  <p class="text-muted m-l-30"><?php echo $reg[0]['nomcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['nomcliente']; ?>,
  <br/>DIREC: <?php echo $reg[0]['direccliente'] == '' ? "******" : $reg[0]['direccliente']; ?> <?php echo $reg[0]['provincia2'] == '' ? "" : $reg[0]['provincia2']; ?> <?php echo $reg[0]['departamento2'] == '' ? "" : $reg[0]['departamento2']; ?>
  <br/> EMAIL: <?php echo $reg[0]['emailcliente'] == '' ? "******" : $reg[0]['emailcliente']; ?>
  <br/> Nº <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: <?php echo $reg[0]['dnicliente'] == '' ? "******" : $reg[0]['dnicliente']; ?> - TLF: <?php echo $reg[0]['tlfcliente'] == '' ? "******" : $reg[0]['tlfcliente']; ?></p>
                                            
                                        </address>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="table-responsive m-t-10" style="clear: both;">
                                        <div id="div1"><table class="table2 table-hover">
                               <thead>
                        <tr>
                        <th>#</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
                        <?php if ($_SESSION['acceso'] == "administradorS") { ?>
                        <th><span class="mdi mdi-drag-horizontal"></span></th>
                        <?php } ?>
                        </tr>
                        </thead>
                        <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesVentas();

$SubTotal = 0;
$a=1;
for($i=0;$i<sizeof($detalle);$i++){  
$SubTotal += $detalle[$i]['valorneto'];
?>
                                                <tr>
  <td><label><?php echo $a++; ?></label></td>
  <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
  <small class="text-danger alert-link">
  <?php if($detalle[$i]['tipo'] == 1){
    echo $detalle[$i]['nomcategoria'];
  } elseif($detalle[$i]['tipo'] == 2){
    echo "**********";
  } else {
    echo $detalle[$i]['nommedida'];
  } ?>
  </small></td>
  <td><?php echo $detalle[$i]['cantventa']; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?><sup><strong><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ','); ?>%</strong></sup></td>
  <td><?php echo $detalle[$i]['ivaproducto'] != 'SI' ? number_format($detalle[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></td>
  <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
  <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDetallesVentaModal('<?php echo encrypt($detalle[$i]["coddetalleventa"]); ?>','<?php echo encrypt($detalle[$i]["codventa"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[0]["codcliente"]); ?>','<?php echo $modulo; ?>','<?php echo encrypt("DETALLESVENTAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>
                                                </tr>
                                      <?php } ?>
                                            </tbody>
                                        </table></div>
                                    </div>
                                </div>


                                <div class="col-md-12">

                                    <div class="pull-right text-right">
<p><b>Subtotal:</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi']+$reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Total Grabado (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?><p>
<p><b>Total Exento (8)0%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Total <?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaliva'], 2, '.', ','); ?> </p>
<p><b>Descontado %:</b> <?php echo $simbolo.number_format($reg[0]['descontado'], 2, '.', ','); ?> </p>
<p><b>Desc. Global (<?php echo $reg[0]['descuento']; ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaldescuento'], 2, '.', ','); ?> </p>

<?php if($reg[0]["codmesa"] != 0){ ?>
<p><b>Propina (<?php echo $reg[0]['propinasugerida']; ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totalpropina'], 2, '.', ','); ?> </p>
<?php } ?>

<?php if($reg[0]["repartidor"] != 0){ ?>
<p><b>Costo Delivery:</b> <?php echo $simbolo.number_format($reg[0]['montodelivery'], 2, '.', ','); ?> </p>
<?php } ?>
                                        <hr>
<h4><b>Importe Total:</b> <?php echo $simbolo.number_format($reg[0]['totalpago']+$reg[0]['totalpropina']+$reg[0]['montodelivery'], 2, '.', ','); ?></h4>

<?php if($reg[0]["tipopago"] == "CREDITO"){ ?>
<h4><b>Total Pagado:</b> <?php echo $simbolo.number_format($reg[0]['creditopagado'], 2, '.', ','); ?></h4>

<h4><b>Total Pendiente:</b> <?php echo $simbolo.number_format(($reg[0]['totalpago']+$reg[0]['totalpropina']+$reg[0]['montodelivery'])-$reg[0]['creditopagado'], 2, '.', ','); ?></h4>
<?php } ?>

</div>
                                    <div class="clearfix"></div>
                                    <hr>

                                <div class="col-md-12">
                                    <div class="text-right">
 <?php if($reg[0]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[0]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[0]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[0]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button id="print" class="btn waves-light btn-light" type="button"><span><i class="fa fa-print"></i> Imprimir</span></button></a><?php } ?>
 <button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
                                    </div>
                                </div>
                            </div>
                <!-- .row -->
<?php
  }
} 
######################## MOSTRAR VENTAS EN VENTANA MODAL ########################
?>


<?php
######################## MOSTRAR DETALLES DE VENTAS UPDATE ########################
if (isset($_GET['MuestraDetallesVentasUpdate']) && isset($_GET['codventa']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->VentasPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");
?>

<!--############################## MODAL PARA ACTUALIZAR VENTA ######################################-->
<!-- sample modal content -->
<div id="myModalPago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white" id="myModalLabel"><i class="fa fa-tasks"></i> Actualizar Venta</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><img src="assets/images/close.png"/></button>
            </div>
            
        <!--<form class="form form-material" name="cerrardelivery" id="cerrardelivery" action="#">-->

        <div class="modal-body">

        <div class="row">
            <div class="col-md-4">
                <h4 class="mb-0 font-light">Total a Pagar</h4>
                <h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextImporte" name="TextImporte"><?php echo number_format($reg[0]['totalpago']+$reg[0]['totalpropina'], 2, '.', ','); ?></label></h4>
            </div>

            <div class="col-md-4">
                <h4 class="mb-0 font-light">Total Recibido</h4>
                <h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextPagado" name="TextPagado"><?php echo number_format($reg[0]['montopagado']+$reg[0]['montopagado2'], 2, '.', ','); ?></label></h4>
            </div>

            <div class="col-md-4">
                <h4 class="mb-0 font-light">Total Cambio</h4>
                <h3 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextCambio" name="TextCambio"><?php echo $cambio = ($reg[0]['tipopago'] == 'CREDITO' ? "0.00" : number_format($reg[0]['totalpago']+$reg[0]['totalpropina']-($reg[0]['montopagado']+$reg[0]['montopagado2']), 2, '.', ',')); ?></label></h4>
            </div>
        </div>
             
        <div class="row">
            <div class="col-md-8">
                <h4 class="mb-0 font-light">Nombre del Cliente</h4>
                <h4 class="mb-0 font-medium"> <label id="TextCliente" name="TextCliente"><?php echo $reg[0]['codcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['documento'].": ".$reg[0]['dnicliente'].": ".$reg[0]['nomcliente']; ?></label></h4>
            </div>

            <div class="col-md-4">
                <h4 class="mb-0 font-light">Limite de Crédito</h4>
                <h4 class="mb-0 font-medium"><?php echo $simbolo; ?><label id="TextCredito" name="TextCredito"><?php echo $reg[0]['codcliente'] == '' ? "0.00" : number_format($reg[0]['creditodisponible'], 2, '.', ','); ?></label></h4>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="control-label">Tipo de Documento: <span class="symbol required"></span></label><br>
                    <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="ticket" name="tipodocumento" value="TICKET" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "TICKET") { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?> disabled="">
                    <label class="custom-control-label" for="ticket">TICKET</label>
                    </div>
                </div>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="boleta" name="tipodocumento" value="BOLETA" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "BOLETA") { ?> checked="checked" <?php } ?> disabled="">
                    <label class="custom-control-label" for="boleta">BOLETA</label>
                    </div>
                </div><br>

                <div class="form-check form-check-inline">
                    <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="factura" name="tipodocumento" value="FACTURA" <?php if (isset($reg[0]['tipodocumento']) && $reg[0]['tipodocumento'] == "FACTURA") { ?> checked="checked" <?php } ?> disabled="">
                    <label class="custom-control-label" for="factura">FACTURA</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
               <div class="form-group">
               <label class="control-label">Condición de Pago: <span class="symbol required"></span></label>
               <div class="custom-control custom-radio">
               <input type="radio" class="custom-control-input" id="contado" name="tipopago" value="CONTADO" onClick="CargaCondicionesPagos()" <?php if (isset($reg[0]['tipopago']) && $reg[0]['tipopago'] == "CONTADO") { ?> checked="checked" <?php } else { ?> checked="checked" <?php } ?> disabled="">
               <label class="custom-control-label" for="contado">CONTADO</label>
               </div>

               <div class="custom-control custom-radio">
               <input type="radio" class="custom-control-input" id="credito" name="tipopago" value="CREDITO" onClick="CargaCondicionesPagos()" <?php if (isset($reg[0]['tipopago']) && $reg[0]['tipopago'] == "CREDITO") { ?> checked="checked" <?php } ?> disabled="">
               <label class="custom-control-label" for="credito">CRÉDITO</label>
               </div>
            </div>
            </div>

            <div class="col-md-4"> 
                <div class="form-group has-feedback"> 
                <label class="control-label">Costo Delivery: <span class="symbol required"></span></label>
                <input type="hidden" name="porcentajepropina" id="porcentajepropina" value="<?php echo number_format($porcentajepropina, 2, '.', ''); ?>"/>
                <input type="hidden" name="totalporcentajepropina" id="totalporcentajepropina" value="<?php echo number_format($reg[0]['totalpropina'], 2, '.', ''); ?>"/>
                <input type="hidden" name="propinasugerida" id="propinasugerida" value="<?php echo number_format($reg[0]['propinasugerida'], 2, '.', ''); ?>"/>
                <input type="hidden" name="totalpropina" id="totalpropina" value="<?php echo number_format($reg[0]['totalpropina'], 2, '.', ''); ?>"/>
                <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montodelivery" id="montodelivery" onKeyUp="DevolucionVenta();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Ingrese Costo Delivery" value="<?php echo number_format($reg[0]['montodelivery'], 2, '.', ''); ?>" <?php if ($reg[0]['repartidor'] == 0) { ?> disabled="" <?php } ?> required="" aria-required="true"> 
                <i class="fa fa-dollar form-control-feedback"></i>
                </div> 
            </div>
        </div>

        <div id="muestra_condiciones"><!-- IF CONDICION PAGO -->

        <?php if (isset($reg[0]['tipopago']) && $reg[0]['tipopago'] == "CONTADO") { ?>

        <div class="row">

        <!-- .col -->
        <div class="col-md-4">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Propina Sugerida</h4><hr>
            
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="control-label">Desea Pagar?: <span class="symbol required"></span></label><br>
                <div class="form-check form-check-inline">
                  <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="1" name="opcionpropina" value="1" onClick="CargaOpcionPropina()" <?php if (isset($reg[0]['opcionpropina']) && $reg[0]['codmesa'] != 0 && $reg[0]['opcionpropina'] == 1) { ?> checked="checked" <?php } ?> <?php if ($reg[0]['codmesa'] == 0) { ?> disabled="" <?php } ?>>
                    <label class="custom-control-label" for="1">SI</label>
                  </div>
                </div>

                <div class="form-check form-check-inline">
                  <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="2" name="opcionpropina" value="2" onClick="CargaOpcionPropina()" <?php if (isset($reg[0]['opcionpropina']) && $reg[0]['codmesa'] != 0 && $reg[0]['opcionpropina'] == 2) { ?> checked="checked" <?php } ?> <?php if ($reg[0]['codmesa'] == 0) { ?> disabled="" <?php } ?>>
                    <label class="custom-control-label" for="2">NO</label>
                  </div>
                </div>

                <div class="form-check form-check-inline">
                  <div class="custom-control custom-radio">
                    <input type="radio" class="custom-control-input" id="3" name="opcionpropina" value="3" onClick="CargaOpcionPropina()" <?php if (isset($reg[0]['opcionpropina']) && $reg[0]['codmesa'] != 0 && $reg[0]['opcionpropina'] == 3) { ?> checked="checked" <?php } ?> <?php if ($reg[0]['codmesa'] == 0) { ?> disabled="" <?php } ?>>
                    <label class="custom-control-label" for="3">OTRO MONTO</label>
                  </div>
                </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12"> 
            <div class="form-group has-feedback"> 
                <label class="control-label">Propina Recibida: <span class="symbol required"></span></label>
                <input style="color:#000;font-weight:bold;" class="form-control number" type="text" name="montopropinasugerida" id="montopropinasugerida" onKeyUp="CargaOpcionPropina();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Propina Recibida" value="<?php echo $propina = ($reg[0]['opcionpropina'] == 3 ? number_format($reg[0]['totalpropina'], 2, '.', '') : "0.00"); ?>" <?php if ($reg[0]['codmesa'] == 0 || $reg[0]['opcionpropina'] != 3) { ?> disabled="" <?php } ?> required="" aria-required="true">
               <i class="fa fa-dollar form-control-feedback"></i>
            </div> 
          </div>
        </div>

        </div>
        <!-- /.col -->

        <!-- .col -->
        <div class="col-md-4">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 1</h4><hr>
            
        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Forma de Pago Nº 1: <span class="symbol required"></span></label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <input type="hidden" name="montopropina" id="montopropina" value="0.00">
                    <select style="color:#000;font-weight:bold;" name="formapago" id="formapago" class="form-control" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="EFECTIVO"<?php if (!(strcmp('EFECTIVO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>EFECTIVO</option>
                    <option value="CHEQUE"<?php if (!(strcmp('CHEQUE', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>CHEQUE</option>
                    <option value="TARJETA DE CREDITO"<?php if (!(strcmp('TARJETA DE CREDITO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TARJETA DE CRÉDITO</option>
                    <option value="TARJETA DE DEBITO"<?php if (!(strcmp('TARJETA DE DEBITO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TARJETA DE DÉBITO</option>
                    <option value="TARJETA PREPAGO"<?php if (!(strcmp('TARJETA PREPAGO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TARJETA PREPAGO</option>
                    <option value="TRANSFERENCIA"<?php if (!(strcmp('TRANSFERENCIA', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>TRANSFERENCIA</option>
                    <option value="DINERO ELECTRONICO"<?php if (!(strcmp('DINERO ELECTRONICO', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>DINERO ELECTRÓNICO</option>
                    <option value="CUPON"<?php if (!(strcmp('CUPON', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>CUPÓN</option>
                    <option value="OTROS"<?php if (!(strcmp('OTROS', $reg[0]['formapago']))) {echo "selected=\"selected\"";} ?>>OTROS</option>
                  </select>
                </div> 
            </div>
        </div>

        <div class="row">
            <div class="col-md-12"> 
                    <div class="form-group has-feedback"> 
                    <label class="control-label">Monto de Pago Nº 1: <span class="symbol required"></span></label>
                    <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
                    <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado" id="montopagado" onKeyUp="DevolucionVenta();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 1" value="<?php echo number_format($reg[0]['montopagado'], 2, '.', ''); ?>" required="" aria-required="true"> 
                    <i class="fa fa-dollar form-control-feedback"></i>
                </div> 
            </div>
        </div>

        </div>
        <!-- /.col -->

        <!-- .col -->
        <div class="col-md-4">

        <h4 class="card-subtitle m-0 text-dark"><i class="font-18 mdi mdi-cash-multiple"></i> Métodos de Pago Nº 2</h4><hr>
            
        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Forma de Pago Nº 2: </label>
                    <i class="fa fa-bars form-control-feedback"></i>
                    <select style="color:#000;font-weight:bold;" name="formapago2" id="formapago2" class="form-control" required="" aria-required="true">
                    <option value=""> -- SELECCIONE -- </option>
                    <option value="EFECTIVO"<?php if (!(strcmp('EFECTIVO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>EFECTIVO</option>
                    <option value="CHEQUE"<?php if (!(strcmp('CHEQUE', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>CHEQUE</option>
                    <option value="TARJETA DE CREDITO"<?php if (!(strcmp('TARJETA DE CREDITO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TARJETA DE CRÉDITO</option>
                    <option value="TARJETA DE DEBITO"<?php if (!(strcmp('TARJETA DE DEBITO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TARJETA DE DÉBITO</option>
                    <option value="TARJETA PREPAGO"<?php if (!(strcmp('TARJETA PREPAGO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TARJETA PREPAGO</option>
                    <option value="TRANSFERENCIA"<?php if (!(strcmp('TRANSFERENCIA', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>TRANSFERENCIA</option>
                    <option value="DINERO ELECTRONICO"<?php if (!(strcmp('DINERO ELECTRONICO', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>DINERO ELECTRÓNICO</option>
                    <option value="CUPON"<?php if (!(strcmp('CUPON', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>CUPÓN</option>
                    <option value="OTROS"<?php if (!(strcmp('OTROS', $reg[0]['formapago2']))) {echo "selected=\"selected\"";} ?>>OTROS</option>
                  </select>
               </div> 
            </div>
        </div>

        <div class="row">
            <div class="col-md-12"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Monto de Pago Nº 2: </label>
                    <input style="color:#000;font-weight:bold;" class="form-control" type="text" name="montopagado2" id="montopagado2" onKeyUp="DevolucionVenta();" onKeyPress="EvaluateText('%f', this);" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" autocomplete="off" placeholder="Monto de Pago Nº 2" value="<?php echo number_format($reg[0]['montopagado2'], 2, '.', ''); ?>" <?php if ($reg[0]['formapago2'] == "" || $reg[0]['formapago2'] == 0) { ?> disabled="" <?php } ?> required="" aria-required="true"> 
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
                <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"><?php echo $reg[0]['observaciones']; ?></textarea>
                <i class="fa fa-comment-o form-control-feedback2"></i> 
              </div> 
            </div>
         </div> 

        <?php } else { ?>

        <div class="row">
            <div class="col-md-4"> 
                <div class="form-group has-feedback"> 
                    <label class="control-label">Fecha Vence Crédito: <span class="symbol required"></span></label> 
                    <input type="hidden" name="formapago" id="formapago" value="">
                    <input type="hidden" name="montopagado" id="montopagado" value="0.00">
                    <input type="hidden" name="formapago2" id="formapago2" value="">
                    <input type="hidden" name="montopagado2" id="montopagado2" value="0.00">
                    <input type="hidden" name="montodevuelto" id="montodevuelto" value="0.00">
                    <input type="hidden" name="montopropina" id="montopropina" value="0.00">
                    <input style="color:#000;font-weight:bold;" type="text" class="form-control vencecredito" name="fechavencecredito" id="fechavencecredito" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off"  value="<?php echo date("d-m-Y",strtotime($reg[0]['fechavencecredito'])); ?>" placeholder="Ingrese Fecha Vence Crédito" aria-required="true">
                    <i class="fa fa-calendar form-control-feedback"></i>  
                </div> 
            </div>

            <div class="col-md-8"> 
                <div class="form-group has-feedback2"> 
                    <label class="control-label">Observaciones: </label> 
                    <textarea class="form-control" type="text" name="observaciones" id="observaciones" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" placeholder="Ingrese Observaciones" rows="1"><?php echo $reg[0]['observaciones']; ?></textarea>
                    <i class="fa fa-comment-o form-control-feedback2"></i> 
                </div> 
            </div>
         </div> 

        <?php } ?>

        </div><!-- END CONDICION PAGO -->
    

        <div class="row">
            <div class="col-md-6">
                <span id="submit_guardar"><button type="submit" name="btn-update" id="btn-update" class="btn btn-warning btn-lg btn-block waves-effect waves-light"><span class="fa fa-print"></span> Facturar e Imprimir</button></span>
            </div>
            <div class="col-md-6">
                <button type="reset" class="btn btn-dark btn-lg btn-block waves-effect waves-light" class="close" data-dismiss="modal" aria-hidden="true"><span class="fa fa-trash-o"></span> Cancelar</button>
            </div>
        </div>

        </div>
        <!--</form>-->

        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<!--############################## MODAL PARA ACTUALIZAR VENTA ######################################-->

<div class="table-responsive m-t-20">
            <table class="table2 table-hover">
                <thead>
                    <tr>
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Precio Unit.</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
<?php if ($_SESSION['acceso'] == "administradorS") { ?><th><span class="mdi mdi-drag-horizontal"></span></th><?php } ?>
                    </tr>
                </thead>
                <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesVentas();
$a=1;
$count = 0;
for($i=0;$i<sizeof($detalle);$i++){ 
$count++;
?>
    <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
    <td>
    <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
    <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetalleVenta('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
    <input type="text" class="bold" name="cantventa[]" id="cantventa_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoVenta(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="<?php echo number_format($detalle[$i]["cantventa"], 2, '.', ''); ?>" title="Ingrese Cantidad">
    <input type="hidden" name="cantidadventabd[]" id="cantidadventabd_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["cantventa"], 2, '.', ''); ?>">
    <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetalleVenta('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
    </div>
    </td>
      
    <td class="text-danger alert-link">
    <input type="hidden" name="coddetalleventa[]" id="coddetalleventa" value="<?php echo $detalle[$i]["coddetalleventa"]; ?>">
    <input type="hidden" name="codproducto[]" id="codproducto" value="<?php echo $detalle[$i]["codproducto"]; ?>">
    <input type="hidden" name="tipo[]" id="tipo" value="<?php echo $detalle[$i]["tipo"]; ?>">
    <input type="hidden" class="preciocompra" name="preciocompra[]" id="preciocompra_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["preciocompra"], 2, '.', ''); ?>">
    <?php if($detalle[$i]['tipo'] == 1){
        echo "PRODUCTO";
    } elseif($detalle[$i]['tipo'] == 2){
        echo "COMBO";
    } else {
        echo "EXTRA";
    } ?></td>
      
    <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
    <small class="text-danger alert-link"><?php echo $detalle[$i]['detallesobservaciones'] == '' ? "**********" : $detalle[$i]['detallesobservaciones'] ?></small></td>
      
    <td><strong>
    <input type="hidden" name="precioventa[]" id="precioventa_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>">
    <input type="hidden" name="precioconiva[]" id="precioconiva_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? "0.00" : number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>">
    <?php echo number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></strong></td>

    <td><input type="hidden" name="valortotal[]" id="valortotal_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["valortotal"], 2, '.', ''); ?>"><label id="txtvalortotal_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></label></td>
      
    <td><input type="hidden" name="descproducto[]" id="descproducto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["descproducto"], 2, '.', ''); ?>">
    <input type="hidden" class="totaldescuentov" name="totaldescuentov[]" id="totaldescuentov_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["totaldescuentov"], 2, '.', ''); ?>">
    <label id="txtdescproducto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?></label><sup><label><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ','); ?>%</label></sup></td>

    <td><input type="hidden" name="ivaproducto[]" id="ivaproducto_<?php echo $count; ?>" value="<?php echo $detalle[$i]["ivaproducto"]; ?>"><label><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', '')."%" : "(E)"; ?></label></td>

    <td><input type="hidden" class="subtotalivasi" name="subtotalivasi[]" id="subtotalivasi_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalivano" name="subtotalivano[]" id="subtotalivano_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? number_format($detalle[$i]['valorneto'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="subtotalimpuestos" name="subtotalimpuestos[]" id="subtotalimpuestos_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['subtotalimpuestos'], 2, '.', ''); ?>">

    <input type="hidden" class="subtotaldiscriminado" name="subtotaldiscriminado[]" id="subtotaldiscriminado_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['valorneto']-$detalle[$i]['subtotalimpuestos'], 2, '.', '') : "0.00"; ?>">

    <input type="hidden" class="valorneto" name="valorneto[]" id="valorneto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto'], 2, '.', ''); ?>" >

    <input type="hidden" class="valorneto2" name="valorneto2[]" id="valorneto2_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]['valorneto2'], 2, '.', ''); ?>" >

    <label id="txtvalorneto_<?php echo $count; ?>"><?php echo number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></label></td>

 <?php if ($_SESSION['acceso'] == "administradorS") { ?><td>
<button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDetallesVentaUpdate('<?php echo encrypt($detalle[$i]["coddetalleventa"]); ?>','<?php echo encrypt($detalle[$i]["codventa"]); ?>','<?php echo encrypt($detalle[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[0]["codcliente"]); ?>','<?php echo encrypt("DETALLESVENTAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td><?php } ?>

                                 </tr>
                     <?php } ?>
                </tbody>
            </table><hr>

             <table id="carritototal" class="table-responsive">
            <tr>
    <td width="250"><h5><label>Gravado (<?php echo $reg[0]['iva'] ?>%):</label></h5></td>
    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal"><?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdiscriminado" id="txtdiscriminado" value="<?php echo number_format($reg[0]['subtotalivasi'], 0, '.', ''); ?>"/>
    <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="<?php echo number_format($reg[0]['subtotalivasi'], 2, '.', ''); ?>"/>    </td>
                  
    <td width="250">
    <h5><label>Exento (0%):</label></h5>    
    </td>

    <td width="250">
    <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2"><?php echo number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="<?php echo number_format($reg[0]['subtotalivano'], 2, '.', ''); ?>"/>    </td>
    
    <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ''); ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo $reg[0]['iva'] ?>"></label></h5>
    </td>

    <td class="text-center" width="250">
    <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva"><?php echo number_format($reg[0]['totaliva'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtIva" id="txtIva" value="<?php echo number_format($reg[0]['totaliva'], 2, '.', ''); ?>"/>
    </td>
    </tr>
    <tr>
    <td>
    <h5><label>Descontado %:</label></h5> </td>
    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado"><?php echo number_format($reg[0]['descontado'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtdescontado" id="txtdescontado" value="<?php echo number_format($reg[0]['descontado'], 2, '.', ''); ?>"/>
        </td>
    
    <td>
    <h5><label>Desc. Global <input class="number" type="text" name="descuento" id="descuento" onKeyPress="EvaluateText('%f', this);" style="border-radius:4px;height:30px;width:60px;" onBlur="this.value = NumberFormat(this.value, '2', '.', '')" onKeyUp="this.value=this.value.toUpperCase();" autocomplete="off" value="<?php echo number_format($reg[0]['descuento'], 2, '.', ''); ?>">%:</label></h5>    </td>

    <td>
    <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento"><?php echo number_format($reg[0]['totaldescuento'], 2, '.', ','); ?></label></h5>
    <input type="hidden" name="txtDescuento" id="txtDescuento" value="<?php echo number_format($reg[0]['totaldescuento'], 2, '.', ''); ?>"/>    </td>

    <td><h4><b>Importe Total</b></h4>
    </td>

    <td class="text-center">
    <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal"><?php echo number_format($reg[0]['totalpago'], 2, '.', ','); ?></label></b></h4>
    <input type="hidden" name="txtTotal" id="txtTotal" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtFactura" id="txtFactura" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtImporte" id="txtImporte" value="<?php echo number_format($reg[0]['totalpago'], 2, '.', ''); ?>"/>
    <input type="hidden" name="txtTotalCompra" id="txtTotalCompra" value="<?php echo number_format($reg[0]['totalpago2'], 2, '.', ''); ?>"/></td>
    </tr>
    </table>
  </div>
<?php
} 
######################## MOSTRAR DETALLES DE VENTAS UPDATE ########################
?>


<?php
######################## BUSQUEDA VENTAS POR CAJAS ########################
if (isset($_GET['BuscaVentasxCajas']) && isset($_GET['codsucursal']) && isset($_GET['codcaja']) && isset($_GET['tipopago']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcaja = limpiar($_GET['codcaja']);
  $tipopago = limpiar($_GET['tipopago']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcaja=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;
   
  } else if($tipopago=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE TIPO DE PAGO PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarVentasxCajas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Ventas por Cajas </h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("VENTASXCAJAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("VENTASXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("VENTASXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de Caja: </label> <?php echo $reg[0]['nrocaja']; ?><br>

            <label class="control-label">Nombre de Caja: </label> <?php echo $reg[0]['nomcaja']; ?><br>

            <label class="control-label">Horario de Caja: </label> <?php echo $reg[0]['hora_desde']." - ".$reg[0]['hora_hasta']; ?><br>

            <label class="control-label">Tipo de Pago: </label> <?php if(decrypt($_GET['tipopago']) == 1){ echo "GENERAL"; }elseif(decrypt($_GET['tipopago']) == 2){ echo "CONTADO"; } elseif(decrypt($_GET['tipopago']) == 3){ echo "CREDITO"; }  ?><br>

            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Descripción de Cliente</th>
                              <th>Estado</th>
                              <th>Fecha Emisión</th>
                              <th>Nº de Articulos</th>
                              <th>Subtotal</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Desc %</th>
                              <th>Imp. Total</th>
                              <th>Abonado</th>
                              <th>Total Debe</th>
                              <th>Total Disponible</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;
$TotalDisponible=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=($reg[$i]["tipopago"] == "CREDITO" ? number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', '') : "0.00");
$TotalDisponible+=($reg[$i]["tipopago"] == "CREDITO" ? number_format($reg[$i]['creditopagado'], 2, '.', '') : number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ''));
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
  elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php echo $var = ($reg[$i]["tipopago"] == "CREDITO" ? $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', ',') : $simbolo."0.00"); ?></td>
  <td><?php echo $var = ($reg[$i]["tipopago"] == "CREDITO" ? $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ',') : $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ',')); ?></td>
  <td><?php if($reg[$i]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a><?php } else { ?><span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ANULADA</span><?php } ?></td>
          </tr>
          <?php } ?>
          <tr>
          <td colspan="5"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDisponible, 2, '.', ','); ?></td>
         </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA VENTAS POR CAJAS ########################
?>


<?php
######################## BUSQUEDA VENTAS POR FECHAS ########################
if (isset($_GET['BuscaVentasxFechas']) && isset($_GET['codsucursal']) && isset($_GET['tipopago']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $tipopago = limpiar($_GET['tipopago']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($tipopago=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE TIPO DE PAGO PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarVentasxFechas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Ventas por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("VENTASXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("VENTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("VENTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">

          <label class="control-label">Tipo de Pago: </label> <?php if(decrypt($_GET['tipopago']) == 1){ echo "GENERAL"; }elseif(decrypt($_GET['tipopago']) == 2){ echo "CONTADO"; } elseif(decrypt($_GET['tipopago']) == 3){ echo "CREDITO"; }  ?><br>
          <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

          <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                        <thead>
                          <tr>
                            <th>Nº</th>
                            <th>N° de Venta</th>
                            <th>Descripción de Cliente</th>
                            <th>Estado</th>
                            <th>Fecha Emisión</th>
                            <th>Nº de Articulos</th>
                            <th>Subtotal</th>
                            <th><?php echo $impuesto; ?></th>
                            <th>Desc %</th>
                            <th>Imp. Total</th>
                            <th>Abonado</th>
                            <th>Total Debe</th>
                            <th>Total Disponible</th>
                            <th><i class="mdi mdi-drag-horizontal"></i></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;
$TotalDisponible=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=($reg[$i]["tipopago"] == "CREDITO" ? number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', '') : "0.00");
$TotalDisponible+=($reg[$i]["tipopago"] == "CREDITO" ? number_format($reg[$i]['creditopagado'], 2, '.', '') : number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ''));
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
  elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php echo $var = ($reg[$i]["tipopago"] == "CREDITO" ? $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', ',') : $simbolo."0.00"); ?></td>
  <td><?php echo $var = ($reg[$i]["tipopago"] == "CREDITO" ? $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ',') : $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ',')); ?></td>
  <td><?php if($reg[$i]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a><?php } else { ?><span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ANULADA</span><?php } ?></td>
          </tr>
          <?php } ?>
          <tr>
           <td colspan="5"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDisponible, 2, '.', ','); ?></td>
         </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA VENTAS POR FECHAS ########################
?>

<?php
######################## BUSQUEDA VENTAS CONDICION DE PAGO Y FECHAS ########################
if (isset($_GET['BuscaVentasxCondiciones']) && isset($_GET['codsucursal']) && isset($_GET['codcaja']) && isset($_GET['formapago']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcaja = limpiar($_GET['codcaja']);
  $formapago = limpiar($_GET['formapago']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcaja=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;
   
  } else if($formapago=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE FORMA DE PAGO PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarVentasxCondiciones();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Ventas por Formas de Pagos</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&formapago=<?php echo $formapago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("VENTASXCONDICIONES"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&formapago=<?php echo $formapago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("VENTASXCONDICIONES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&formapago=<?php echo $formapago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("VENTASXCONDICIONES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de Caja: </label> <?php echo $reg[0]['nrocaja']; ?><br>

            <label class="control-label">Nombre de Caja: </label> <?php echo $reg[0]['nomcaja']; ?><br>

            <label class="control-label">Forma de Pago: </label> <?php echo $formapago; ?><br>
            
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Descripción de Cliente</th>
                              <th>Estado</th>
                              <th>Fecha Emisión</th>
                              <th>Forma de Pago #1</th>
                              <th>Forma de Pago #2</th>
                              <th>Nº de Articulos</th>
                              <th>Subtotal</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Desc %</th>
                              <th>Imp. Total</th>
                              <th>Total Pago </th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;
$TotalPagado=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
if($reg[$i]['formapago'] == $formapago){
$ImportePagado = $reg[$i]['montopagado']-$reg[$i]['montodevuelto'];
$TotalPagado += $reg[$i]['montopagado']-$reg[$i]['montodevuelto'];
} else {
$ImportePagado = $reg[$i]['montopagado2'];
$TotalPagado += $reg[$i]['montopagado2']; 
}
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
  elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
  <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($ImportePagado, 2, '.', ','); ?></td>
  <td><?php if($reg[$i]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a><?php } else { ?><span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ANULADA</span><?php } ?></td>
          </tr>
          <?php } ?>
          <tr>
           <td colspan="7"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalPagado, 2, '.', ','); ?></td>
         </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA VENTAS POR CONDICION DE PAGO Y FECHAS ########################
?>


<?php
######################## BUSQUEDA VENTAS POR TIPOS DE CLIENTES ########################
if (isset($_GET['BuscaVentasxTipos']) && isset($_GET['codsucursal']) && isset($_GET['tipocliente']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $tipocliente = limpiar($_GET['tipocliente']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);
  $tipo = ($tipocliente == 'NATURAL' ? "NATURALES" : "JURIDICOS");

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($tipocliente=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE TIPO DE CLIENTE PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarVentasxTipos();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Ventas por Tipo de Clientes</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipocliente=<?php echo $tipocliente; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("VENTASXTIPOS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&tipocliente=<?php echo $tipocliente; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("VENTASXTIPOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&tipocliente=<?php echo $tipocliente; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("VENTASXTIPOS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de Caja: </label> <?php echo $reg[0]['nrocaja']; ?><br>

            <label class="control-label">Tipo de Cliente: </label> <?php echo $tipo; ?><br>
            
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Documento</th>
                              <th>Descripción de Cliente</th>
                              <th>Nº de Teléfono</th>
                              <th>Cantidad Compras</th>
                              <th>Total Compras</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]["dnicliente"]; ?></td>
  <td><?php echo $reg[$i]['nomcliente']; ?></td>
  <td><?php echo $reg[$i]['tlfcliente'] == '' ? "******" : $reg[$i]['tlfcliente']; ?></td>
  <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalcompras'], 2, '.', ','); ?></td>
  <td>
  <a href="reportepdf?codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&codcliente=<?php echo $reg[$i]['codcliente']; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("VENTASXCLIENTES"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-rounded btn-secondary" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
              </tr>
              <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA VENTAS POR TIPOS DE CLIENTES ########################
?>


<?php
######################## BUSQUEDA VENTAS POR CLIENTES ########################
if (isset($_GET['BuscaVentasxClientes']) && isset($_GET['codsucursal']) && isset($_GET['codcliente']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcliente = limpiar($_GET['codcliente']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcliente=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR REALICE LA BÚSQUEDA DEL CLIENTE CORRECTAMENTE</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarVentasxClientes();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Ventas por Clientes </h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcliente=<?php echo $codcliente; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("VENTASXCLIENTES"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcliente=<?php echo $codcliente; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("VENTASXCLIENTES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcliente=<?php echo $codcliente; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("VENTASXCLIENTES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de Caja: </label> <?php echo $reg[0]['nrocaja']; ?><br>

            <label class="control-label">Nº de <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: </label> <?php echo $reg[0]['dnicliente']; ?><br>

            <label class="control-label">Nombre de Cliente: </label> <?php echo $reg[0]['nomcliente']; ?><br>
            
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Estado</th>
                              <th>Fecha Emisión</th>
                              <th>Forma de Pago #1</th>
                              <th>Forma de Pago #2</th>
                              <th>Nº de Articulos</th>
                              <th>Subtotal</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Desc %</th>
                              <th>Imp. Total</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
  elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
  <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"], 2, '.', ','); ?></td>
  <td><?php if($reg[$i]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a><?php } else { ?><span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ANULADA</span><?php } ?></td>
        </tr>
        <?php } ?>
        <tr>
          <td colspan="6"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
         </tr>
              </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA VENTAS POR CLIENTES ########################
?>

<?php 
########################### BUSQUEDA DELIVERY POR FECHAS ##########################
if (isset($_GET['BuscaDeliveryxFechas']) && isset($_GET['codsucursal']) && isset($_GET['codigo']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$codigo = limpiar($_GET['codigo']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codigo=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE VENDEDOR PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$delivery = new Login();
$reg = $delivery->BuscarDeliveryxFechas();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Delivery del Repartidor <?php echo $reg[0]['nombres2']; ?>  y Fecha Desde <?php echo date("d-m-Y", strtotime($desde)); ?> Hasta <?php echo date("d-m-Y", strtotime($hasta)); ?></h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("DELIVERYXFECHAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("DELIVERYXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("DELIVERYXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de Caja: </label> <?php echo $reg[0]['nrocaja']; ?><br>

            <label class="control-label">Nombre de Repartidor: </label> <?php echo $reg[0]['nombres2']; ?><br>
            
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Descripción de Cliente</th>
                              <th>Fecha Emisión</th>
                              <th>Nº de Articulos</th>
                              <th>Imp. Total</th>
                              <th>Total Delivery</th>
                              <th>Total Comisión</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalDelivery=0;
$TotalComision=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
   
$CalculoComision = ($reg[$i]['comision2'] == "0.00" ? "0.00" : ($reg[$i]['montodelivery'])*($reg[$i]['comision2']/100));

$TotalArticulos+=$reg[$i]['articulos'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"];
$TotalDelivery+=$reg[$i]['montodelivery'];
$TotalComision+=$CalculoComision;
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento3']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['montodelivery'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($CalculoComision, 2, '.', ','); ?></td>

  <td><?php if($reg[$i]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a><?php } else { ?><span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ANULADA</span><?php } ?></td>
          </tr>
          <?php } ?>
          <tr>
          <td colspan="4"></td>
  <td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($TotalDelivery, 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($TotalComision, 2, '.', ','); ?></td>
         </tr>
                            </tbody>
                        </table>
                    </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA DE DELIVERY POR FECHAS ##########################
?>

<?php 
########################### BUSQUEDA DE DETALLES VENTAS POR CAJAS ##########################
if (isset($_GET['BuscaDetallesVentasxCajas']) && isset($_GET['codsucursal']) && isset($_GET['codcaja']) && isset($_GET['tipopago']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$codcaja = limpiar($_GET['codcaja']);
$tipopago = limpiar($_GET['tipopago']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcaja=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;
   
  } else if($tipopago=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE TIPO DE PAGO PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DESDE PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA HASTA PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DESDE NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarDetallesVentasxCajas();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Detalles de Ventas por Fecha</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("DETALLESVENTASXCAJAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("DETALLESVENTASXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("DETALLESVENTASXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de Caja: </label> <?php echo $reg[0]['nrocaja']; ?><br>

            <label class="control-label">Nombre de Caja: </label> <?php echo $reg[0]['nomcaja']; ?><br>

            <label class="control-label">Horario de Caja: </label> <?php echo $reg[0]['hora_desde']." - ".$reg[0]['hora_hasta']; ?><br>

            <label class="control-label">Tipo de Pago: </label> <?php if(decrypt($_GET['tipopago']) == 1){ echo "GENERAL"; }elseif(decrypt($_GET['tipopago']) == 2){ echo "CONTADO"; } elseif(decrypt($_GET['tipopago']) == 3){ echo "CREDITO"; }  ?><br>
      
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div2"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>Tipo</th>
                              <th>Descripción</th>
                              <th>Categoria</th>
                              <th>Desc</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Precio de Venta</th>
                              <th>Existencia</th>
                              <th>Vendido</th>
                              <th>Monto Total</th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$PagoTotal=0;

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal+=$reg[$i]['precioventa'];

if($reg[$i]['tipo'] == 1){

$Tipo="PRODUCTO";
$Categoria=$reg[$i]['nomcategoria'];
$Existencia=$reg[$i]['existencia'];
$ExisteTotal+=$reg[$i]['existencia'];

} elseif($reg[$i]['tipo'] == 2){

$Tipo="COMBO";
$Categoria="********";
$Existencia=$reg[$i]['cantcombo'];
$ExisteTotal+=$reg[$i]['cantcombo'];

} else {

$Tipo="EXTRA";
$Categoria=$reg[$i]['nommedida'];
$Existencia=$reg[$i]['cantingrediente'];
$ExisteTotal+=$reg[$i]['cantingrediente'];
}

$VendidosTotal+=$reg[$i]['cantidad']; 

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg = $reg[$i]['ivaproducto']/100;
$SubtotalImpuesto = $PrecioFinal*$ivg;
$CalculoImpuesto = $SubtotalImpuesto*$reg[$i]['cantidad'];

$TotalDescuento+=$CalculoDescuento; 
$TotalImpuesto+=$CalculoImpuesto; 
$PagoTotal+=$PrecioFinal*$reg[$i]['cantidad']; 
?>
          <tr>
          <td><?php echo $a++; ?></td>
          <td class="text-danger alert-link"><?php echo $Tipo; ?></td>
          <td><?php echo $reg[$i]['producto']; ?></td>
          <td><?php echo $Categoria ?></td>
          <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
          <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($reg[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
          <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
          <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
                    </tr>
            <?php  }  ?>
          <tr class="text-dark alert-link">
            <td colspan="6"></td>
            <td><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></td>
            <td><?php echo number_format($ExisteTotal, 2, '.', ','); ?></td>
            <td><?php echo number_format($VendidosTotal, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></td>
          </tr>
          </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA DE DETALLES VENTAS POR CAJAS ##########################
?>

<?php 
########################### BUSQUEDA DE DETALLES VENTAS POR FECHAS ##########################
if (isset($_GET['BuscaDetallesVentasxFechas']) && isset($_GET['codsucursal']) && isset($_GET['tipopago']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$tipopago = limpiar($_GET['tipopago']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($tipopago=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE TIPO DE PAGO PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DESDE PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA HASTA PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DESDE NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarDetallesVentasxFechas();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Detalles de Ventas por Fecha</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("DETALLESVENTASXFECHAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("DETALLESVENTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&tipopago=<?php echo $tipopago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("DETALLESVENTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Tipo de Pago: </label> <?php if(decrypt($_GET['tipopago']) == 1){ echo "GENERAL"; }elseif(decrypt($_GET['tipopago']) == 2){ echo "CONTADO"; } elseif(decrypt($_GET['tipopago']) == 3){ echo "CREDITO"; }  ?><br>
      
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div2"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>Tipo</th>
                              <th>Descripción</th>
                              <th>Categoria</th>
                              <th>Desc</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Precio de Venta</th>
                              <th>Existencia</th>
                              <th>Vendido</th>
                              <th>Monto Total</th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$PagoTotal=0;

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal+=$reg[$i]['precioventa'];

if($reg[$i]['tipo'] == 1){

$Tipo="PRODUCTO";
$Categoria=$reg[$i]['nomcategoria'];
$Existencia=$reg[$i]['existencia'];
$ExisteTotal+=$reg[$i]['existencia'];

} elseif($reg[$i]['tipo'] == 2){

$Tipo="COMBO";
$Categoria="********";
$Existencia=$reg[$i]['cantcombo'];
$ExisteTotal+=$reg[$i]['cantcombo'];

} else {

$Tipo="EXTRA";
$Categoria=$reg[$i]['nommedida'];
$Existencia=$reg[$i]['cantingrediente'];
$ExisteTotal+=$reg[$i]['cantingrediente'];
}

$VendidosTotal+=$reg[$i]['cantidad']; 

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg = $reg[$i]['ivaproducto']/100;
$SubtotalImpuesto = $PrecioFinal*$ivg;
$CalculoImpuesto = $SubtotalImpuesto*$reg[$i]['cantidad'];

$TotalDescuento+=$CalculoDescuento; 
$TotalImpuesto+=$CalculoImpuesto; 
$PagoTotal+=$PrecioFinal*$reg[$i]['cantidad']; 
?>
          <tr>
          <td><?php echo $a++; ?></td>
          <td class="text-danger alert-link"><?php echo $Tipo; ?></td>
          <td><?php echo $reg[$i]['producto']; ?></td>
          <td><?php echo $Categoria ?></td>
          <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
          <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($reg[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
          <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
          <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
                    </tr>
            <?php  }  ?>
          <tr class="text-dark alert-link">
            <td colspan="6"></td>
            <td><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></td>
            <td><?php echo number_format($ExisteTotal, 2, '.', ','); ?></td>
            <td><?php echo number_format($VendidosTotal, 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></td>
          </tr>
          </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA DE DETALLES VENTAS POR FECHAS ##########################
?>


<?php 
########################### BUSQUEDA DE DETALLES VENTAS POR VENDEDOR ##########################
if (isset($_GET['BuscaDetallesVentasxVendedor']) && isset($_GET['codsucursal']) && isset($_GET['tipopago']) && isset($_GET['codigo']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$tipopago = limpiar($_GET['tipopago']);
$codigo = limpiar($_GET['codigo']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($tipopago=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE TIPO DE PAGO PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;
   
  } else if($codigo=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE VENDEDOR PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DESDE PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA HASTA PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DESDE NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarDetallesVentasxVendedor();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Detalles de Ventas por Vendedor</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipopago=<?php echo $tipopago; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("DETALLESVENTASXVENDEDOR"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&tipopago=<?php echo $tipopago; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("DETALLESVENTASXVENDEDOR"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&tipopago=<?php echo $tipopago; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("DETALLESVENTASXVENDEDOR"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Tipo de Pago: </label> <?php if(decrypt($_GET['tipopago']) == 1){ echo "GENERAL"; }elseif(decrypt($_GET['tipopago']) == 2){ echo "CONTADO"; } elseif(decrypt($_GET['tipopago']) == 3){ echo "CREDITO"; }  ?><br>

            <label class="control-label">Nombre de Vendedor: </label> <?php echo $reg[0]['nombres']; ?><br>
      
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div2"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                      <thead>
                        <tr>
                          <th>Nº</th>
                          <th>Tipo</th>
                          <th>Descripción</th>
                          <th>Categoria</th>
                          <th>Desc</th>
                          <th><?php echo $impuesto; ?></th>
                          <th>Precio de Venta</th>
                          <th>Existencia</th>
                          <th>Vendido</th>
                          <th>Monto Total</th>
                        </tr>
                      </thead>
                      <tbody>
<?php
$PrecioTotal=0;
$ExisteTotal=0;
$VendidosTotal=0;
$TotalDescuento=0;
$TotalImpuesto=0;
$PagoTotal=0;

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$PrecioTotal+=$reg[$i]['precioventa'];

if($reg[$i]['tipo'] == 1){

$Tipo="PRODUCTO";
$Categoria=$reg[$i]['nomcategoria'];
$Existencia=$reg[$i]['existencia'];
$ExisteTotal+=$reg[$i]['existencia'];

} elseif($reg[$i]['tipo'] == 2){

$Tipo="COMBO";
$Categoria="********";
$Existencia=$reg[$i]['cantcombo'];
$ExisteTotal+=$reg[$i]['cantcombo'];

} else {

$Tipo="EXTRA";
$Categoria=$reg[$i]['nommedida'];
$Existencia=$reg[$i]['cantingrediente'];
$ExisteTotal+=$reg[$i]['cantingrediente'];
}

$VendidosTotal+=$reg[$i]['cantidad']; 

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$CalculoDescuento = $PrecioDescuento*$reg[$i]['cantidad'];
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg = $reg[$i]['ivaproducto']/100;
$SubtotalImpuesto = $PrecioFinal*$ivg;
$CalculoImpuesto = $SubtotalImpuesto*$reg[$i]['cantidad'];

$TotalDescuento+=$CalculoDescuento; 
$TotalImpuesto+=$CalculoImpuesto; 
$PagoTotal+=$PrecioFinal*$reg[$i]['cantidad']; 
?>
        <tr>
        <td><?php echo $a++; ?></td>
        <td class="text-danger alert-link"><?php echo $Tipo; ?></td>
        <td><?php echo $reg[$i]['producto']; ?></td>
        <td><?php echo $Categoria ?></td>
        <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
        <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($reg[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
        <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
        <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
        <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
                  </tr>
          <?php  }  ?>
        <tr class="text-dark alert-link">
          <td colspan="6"></td>
          <td><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></td>
          <td><?php echo number_format($ExisteTotal, 2, '.', ','); ?></td>
          <td><?php echo number_format($VendidosTotal, 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></td>
        </tr>
        </tbody>
        </table>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA DE DETALLES VENTAS POR VENDEDOR ##########################
?>


<?php 
########################### BUSQUEDA COMISION POR VENDEDOR ##########################
if (isset($_GET['BuscaComisionxVentas']) && isset($_GET['codsucursal']) && isset($_GET['codigo']) && isset($_GET['desde']) && isset($_GET['hasta'])) { 

$codsucursal = limpiar($_GET['codsucursal']);
$codigo = limpiar($_GET['codigo']);
$desde = limpiar($_GET['desde']); 
$hasta = limpiar($_GET['hasta']);
   
 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codigo=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE VENDEDOR PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {
  
$vendidos = new Login();
$reg = $vendidos->BuscarComisionxVentas();  
 ?>
 
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Comisión por Vendedor </h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("COMISIONXVENTAS"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("COMISIONXVENTAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codigo=<?php echo $codigo; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("COMISIONXVENTAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nombre de Vendedor: </label> <?php echo $reg[0]['nombres']; ?><br>

            <label class="control-label">Comisión: </label> <?php echo number_format($reg[0]['comision'], 2, '.', ','); ?><br>
            
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Descripción de Cliente</th>
                              <th>Estado</th>
                              <th>Fecha Emisión</th>
                              <th>Nº de Articulos</th>
                              <th>Subtotal</th>
                              <th><?php echo $impuesto; ?></th>
                              <th>Desc %</th>
                              <th>Imp. Total</th>
                              <th>Total Comisión</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;
$TotalComision=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
   
$CalculoComision = ($reg[$i]['comision'] == "0.00" ? "0.00" : ($reg[$i]['totalpago'])*($reg[$i]['comision']/100));

$TotalArticulos += $reg[$i]['articulos'];
$TotalSubtotal  += $reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto  += $reg[$i]['totaliva'];
$TotalDescuento += $reg[$i]['totaldescuento'];
$TotalImporte   += $reg[$i]['totalpago'];
$TotalComision  += $CalculoComision;
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento3']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>

  <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
  elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>

  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($CalculoComision, 2, '.', ','); ?></td>
  <td><?php if($reg[$i]['statusventa'] != "ANULADA"){ ?><a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a><?php } else { ?><span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ANULADA</span><?php } ?></td>
      </tr>
      <?php  }  ?>
      <tr>
      <td colspan="5"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalComision, 2, '.', ','); ?></td>
      </tr>
      </tbody>
      </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
  } 
}
########################### BUSQUEDA DE COMISION POR VENDEDOR ##########################
?>

<?php
######################## BUSQUEDA INFORMES VENTAS POR FECHAS ########################
if (isset($_GET['BuscaInformesVentasxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$sucursal = new Login();
$sucursal = $sucursal->SucursalesPorId();
$simbolo = ($sucursal[0]['simbolo'] == "" ? "" : "<strong>".$sucursal[0]['simbolo']."</strong>");

$venta = new Login();
$venta = $venta->SumarVentasxFechas();

$compra = new Login();
$compra = $compra->SumarComprasxFechas();

$arqueo = new Login();
$arqueo = $arqueo->SumarArqueosxFechas();

$cartera = new Login();
$cartera = $cartera->SumarCarteraxFechas();

$utilidadneta = ($venta[0]['totalventa']-$venta[0]['totaliva'])-($venta[0]['totalcompra']);
$balance = $venta[0]['totalventa']-$venta[0]['totaliva']+$arqueo[0]['totalingresos']+$arqueo[0]['totalabonos'];
$balance2 = $compra[0]['totalcomprageneral']+$arqueo[0]['totalegresos'];
$balancegeneral = $balance - $balance2;
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Informe de Ventas por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("INFORMEVENTASXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("INFORMEVENTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("INFORMEVENTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
                <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

                <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
            </div>
          </div><hr>

          <div class="row">
            <table border="0" class="table2 table-striped table-bordered border display">
              <tr>
                <td class="text-left text-dark alert-link">TOTAL DE VENTAS</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($venta[0]['totalventa'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">TOTAL DE INGRESOS</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($arqueo[0]['totalingresos'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">ABONOS A CRÉDITOS</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($arqueo[0]['totalabonos'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">TOTAL DE COMPRAS</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($compra[0]['totalcomprageneral'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">TOTAL DE GASTOS (EGRESOS)</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($arqueo[0]['totalegresos'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">CARTERA DE CLIENTES</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($cartera[0]['totaldebe']-$cartera[0]['totalpagado'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">TOTAL DE IMPUESTOS DE VENTAS <?php echo $impuesto; ?> (<?php echo $valor; ?>%)</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($venta[0]['totaliva'], 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">BALANCE GENERAL</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($balancegeneral, 2, '.', ','); ?></td>
              </tr>
              <tr>
                <td class="text-left text-dark alert-link">UTILIDAD NETA</td>
                <td class="text-left text-dark alert-link"><?php echo $simbolo.number_format($utilidadneta, 2, '.', ','); ?></td>
              </tr>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA INFORMES VENTAS POR FECHAS ########################
?>

<?php
########################## BUSQUEDA GANANCIAS DE VENTAS POR VENDEDOR ##########################
if (isset($_GET['BuscaGananciasVentasxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  //$codigo = limpiar($_GET['codigo']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);
  //$tipousuario = limpiar($_GET['tipousuario']);

 if($codsucursal=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarGananciasVentasxFechas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Ganancias de Ventas por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("GANANCIASVENTASXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("GANANCIASVENTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("GANANCIASVENTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

          <div id="div2"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                              <thead>
                                <tr>
                                  <th>Nº</th>
                                  <th>N° de Venta</th>
                                  <th>Descripción de Cliente</th>
                                  <th>Estado</th>
                                  <th>Fecha Emisión</th>
                                  <th>Nº de Articulos</th>
                                  <th>Subtotal</th>
                                  <th><?php echo $impuesto; ?></th>
                                  <th>Desc</th>
                                  <th>Imp. Total</th>
                                  <th>Ganancia</th>
                                  <th><span class="mdi mdi-drag-horizontal"></span></th>
                                </tr>
                              </thead>
                              <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;
$TotalGanancia=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
   
$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago'];
$TotalGanancia+=$reg[$i]['totalpago']-$reg[$i]['totalcompra'];
?>
                                <tr>
                                  <td><?php echo $a++; ?></td>
                                  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento3']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
      elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
      elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
      else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo $reg[$i]['articulos']; ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['totalcompra'], 2, '.', ','); ?></td>

  <td> <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']) ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-rounded btn-secondary" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
                                  </tr>
                        <?php  }  ?>
         <tr>
          <td colspan="5"></td>
<td><strong><?php echo $TotalArticulos; ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></strong></td>
         </tr>
                              </tbody>
                          </table>
                      </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
########################## BUSQUEDA GANANCIAS DE VENTAS POR VENDEDOR ##########################
?>








































<?php
######################## MOSTRAR VENTA DE CREDITO EN VENTANA MODAL #######################
if (isset($_GET['BuscaCreditoModal']) && isset($_GET['codventa']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->CreditosPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

?>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pull-left">
                                        <address>
  <h4><b class="text-danger">RAZÓN SOCIAL</b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomsucursal']; ?>,
  <br/> Nº <?php echo $reg[0]['documsucursal'] == '0' ? "DOCUMENTO" : $reg[0]['documento']; ?>: <?php echo $reg[0]['cuitsucursal']; ?> - TLF: <?php echo $reg[0]['tlfsucursal']; ?></p>

  <h4><b class="text-danger">Nº <?php echo $reg[0]['tipodocumento'].": ".$reg[0]['codfactura']; ?></b></h4>
  <p class="text-muted m-l-5">Nº DE CAJA: <?php echo $reg[0]['nrocaja'].": ".$reg[0]['nomcaja']; ?>
  <br>TOTAL FACTURA: <?php echo $simbolo.number_format($reg[0]['totalpago']+$reg[0]["totalpropina"], 2, '.', ','); ?>
  <br>TOTAL ABONO: <?php echo $simbolo.number_format($reg[0]['creditopagado'], 2, '.', ','); ?>
  <br>TOTAL DEBE: <?php echo $simbolo.number_format($reg[0]['totalpago']+$reg[0]["totalpropina"]-$reg[0]['creditopagado'], 2, '.', ','); ?>
  
  <?php if($reg[0]['tipoventa']=='1'){ ?>

  <br>DIAS VENCIDOS: 
  <?php if($reg[0]['fechavencecredito']== '0000-00-00') { echo "0"; } 
        elseif($reg[0]['fechavencecredito'] >= date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo "0"; } 
        elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[0]['fechavencecredito']); }
        elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[0]['fechapagado'],$reg[0]['fechavencecredito']); } ?>
  <br>STATUS: 
  <?php if($reg[0]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[0]["statusventa"]."</span>"; } 
      elseif($reg[0]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[0]["statusventa"]."</span>"; }
      elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado'] == "0000-00-00" && $reg[0]['statusventa'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
      else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[0]["statusventa"]."</span>"; } ?>
  <?php if($reg[0]['fechapagado']!= "0000-00-00") { ?>
  <br>FECHA PAGADA: <?php echo date("d-m-Y",strtotime($reg[0]['fechapagado'])); ?>
  <?php } ?>

  <?php } ?>

  <br>FECHA DE EMISIÓN: <?php echo date("d-m-Y H:i:s",strtotime($reg[0]['fechaventa'])); ?></p>

  <h4><b class="text-danger">CLIENTE </b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['nomcliente']; ?>,
  <br/>DIREC: <?php echo $reg[0]['direccliente'] == '' ? "******" : $reg[0]['direccliente']; ?> <?php echo $reg[0]['provincia2'] == '' ? "" : $reg[0]['provincia2']; ?> <?php echo $reg[0]['departamento2'] == '' ? "" : $reg[0]['departamento2']; ?>
  <br/> EMAIL: <?php echo $reg[0]['emailcliente'] == '' ? "******" : $reg[0]['emailcliente']; ?>
  <br/> Nº <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: <?php echo $reg[0]['dnicliente'] == '' ? "******" : $reg[0]['dnicliente']; ?> - TLF: <?php echo $reg[0]['tlfcliente'] == '' ? "******" : $reg[0]['tlfcliente']; ?></p>

                </address>
              </div>
            </div>
                                
            <div class="col-md-12">
                  <div class="table-responsive m-t-10" style="clear: both;">
                      <div id="div1"><table class="table2 table-hover">
                               <thead>
                        <tr><th colspan="5">DETALLES DE ABONOS</th></tr>
                        <tr>
                        <th>#</th>
                        <th>Nº de Caja</th>
                        <th>Monto de Abono</th>
                        <th>Forma de Pago</th>
                        <th>Fecha de Abono</th>
                        </tr>
                        </thead>
                        <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesAbonos();

if($detalle==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON ABONOS ACTUALMENTE </center>";
    echo "</div>";    

} else {

$a=1;
for($i=0;$i<sizeof($detalle);$i++){  

?>
      <tr>
        <td><?php echo $a++; ?></td>
        <td><?php echo $detalle[$i]['nrocaja'].": ".$detalle[$i]['nomcaja']; ?></td>
        <td><?php echo $simbolo.number_format($detalle[$i]['montoabono'], 2, '.', ','); ?></td>
        <td><?php echo $detalle[$i]['formaabono']; ?></td>
        <td><?php echo date("d-m-Y H:i:s",strtotime($detalle[$i]['fechaabono'])); ?></td>
      </tr>
      <?php } } ?>
      </tbody>
      </table></div>
      </div>
      <hr>

      <div class="col-md-12">
        <div class="text-right">
         <a href="reportepdf?codventa=<?php echo encrypt($reg[0]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[0]['codsucursal']); ?>&tipo=<?php echo encrypt("TICKETCREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button id="print" class="btn waves-light btn-light" type="button"><span><i class="fa fa-print"></i> Imprimir</span></button></a>
         <button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
       </div>
      </div>
      </div>
    <!-- .row -->
<?php
} 
######################## MOSTRAR VENTA DE CREDITO EN VENTANA MODAL #######################
?>


<?php
######################## BUSQUEDA ABONOS CREDITOS POR CAJAS ########################
if (isset($_GET['BuscaAbonosCreditosxCajas']) && isset($_GET['codsucursal']) && isset($_GET['codcaja']) && isset($_GET['formapago']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcaja = limpiar($_GET['codcaja']);
  $formapago = limpiar($_GET['formapago']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcaja=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;
   
  } else if($formapago=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE FORMA DE PAGO PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

} else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarAbonosCreditosxCajas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Abonos Créditos por Cajas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&formapago=<?php echo $formapago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("ABONOSCREDITOSXCAJAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&formapago=<?php echo $formapago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("ABONOSCREDITOSXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&formapago=<?php echo $formapago; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("ABONOSCREDITOSXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de Caja: </label> <?php echo $reg[0]['nrocaja']; ?><br>

            <label class="control-label">Nombre de Caja: </label> <?php echo $reg[0]['nomcaja']; ?><br>

            <label class="control-label">Horario de Caja: </label> <?php echo $reg[0]['hora_desde']." - ".$reg[0]['hora_hasta']; ?><br>

            <label class="control-label">Forma de Pago: </label> <?php echo $formapago; ?><br>
            
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>N° de Documento</th>
                              <th>Descripción de Cliente</th>
                              <th>Fecha de Abono</th>
                              <th>Monto de Abono</th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

//$TotalArticulos+=$reg[$i]['articulos'];
$TotalImporte+=$reg[$i]['montoabono'];
?>
    <tr>
      <td><?php echo $a++; ?></td>
      <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
      <td><?php echo $reg[$i]['documento'].": ".$reg[$i]['dnicliente']; ?></td>
      <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></td>
      <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaabono'])); ?></td>
      <td><?php echo $simbolo.number_format($reg[$i]['montoabono'], 2, '.', ','); ?></td>
    </tr>
    <?php } ?>
      <tr>
      <td colspan="5"></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
      </tr>
          </tbody>
        </table>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA ABONOS CREDITOS POR CAJAS ########################
?>


<?php
######################## BUSQUEDA CREDITOS POR FECHAS ########################
if (isset($_GET['BuscaCreditosxFechas']) && isset($_GET['codsucursal']) && isset($_GET['status']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $status = limpiar($_GET['status']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($status=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE ESTADO DE CRÉDITO PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarCreditosxFechas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Créditos por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&status=<?php echo $status; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("CREDITOSXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&status=<?php echo $status; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("CREDITOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&status=<?php echo $status; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("CREDITOSXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Estado de Crédito: </label> <?php if(decrypt($status) == 1){ echo "GENERAL"; }elseif(decrypt($status) == 2){ echo "PAGADA"; } elseif(decrypt($status) == 3){ echo "PENDIENTE"; }  ?><br>

            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Descripción de Cliente</th>
                              <th>Estado</th>
                              <th>Dias Venc</th>
                              <th>Fecha Emisión</th>
                              <th>Imp. Total</th>
                              <th>Total Abono</th>
                              <th>Total Debe</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
   
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
  elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
  <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td> <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("TICKETCREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-rounded btn-secondary" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
        </tr>
        <?php } ?>
        <tr>
          <td colspan="6"></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></td>
        </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA CREDITOS POR FECHAS ########################
?>


<?php
######################## BUSQUEDA CREDITOS POR CLIENTES ########################
if (isset($_GET['BuscaCreditosxClientes']) && isset($_GET['codsucursal']) && isset($_GET['status']) && isset($_GET['codcliente'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $status = limpiar($_GET['status']);
  $codcliente = limpiar($_GET['codcliente']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($status=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE ESTADO DE CRÉDITO PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcliente=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR REALICE LA BÚSQUEDA DEL CLIENTE CORRECTAMENTE</center>";
   echo "</div>";   
   exit;

} else {

$pre = new Login();
$reg = $pre->BuscarCreditosxClientes();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Créditos por Cientes</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&status=<?php echo $status; ?>&codcliente=<?php echo $codcliente; ?>&tipo=<?php echo encrypt("CREDITOSXCLIENTES"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&status=<?php echo $status; ?>&codcliente=<?php echo $codcliente; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("CREDITOSXCLIENTES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&status=<?php echo $status; ?>&codcliente=<?php echo $codcliente; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("CREDITOSXCLIENTES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Estado de Crédito: </label> <?php if(decrypt($status) == 1){ echo "GENERAL"; }elseif(decrypt($status) == 2){ echo "PAGADA"; } elseif(decrypt($status) == 3){ echo "PENDIENTE"; }  ?><br>

            <label class="control-label">Nº de <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: </label> <?php echo $reg[0]['dnicliente']; ?><br>

            <label class="control-label">Nombre de Cliente: </label> <?php echo $reg[0]['nomcliente']; ?><br>
            
            <label class="control-label">Nº de Telefono: </label> <?php echo $reg[0]['tlfcliente'] == "" ? "********" : $reg[0]['tlfcliente']; ?><br>

            <label class="control-label">Dirección Domiciliaria: </label> <?php echo $reg[0]['direccliente'] == "" ? "********" : $reg[0]['direccliente']; ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                          <thead>
                            <tr>
                              <th>Nº</th>
                              <th>N° de Venta</th>
                              <th>Descripción de Cliente</th>
                              <th>Estado</th>
                              <th>Dias Venc</th>
                              <th>Fecha Emisión</th>
                              <th>Imp. Total</th>
                              <th>Total Abono</th>
                              <th>Total Debe</th>
                              <th><i class="mdi mdi-drag-horizontal"></i></th>
                            </tr>
                          </thead>
                          <tbody>
<?php
$a=1;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
   
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
  elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
  <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td> <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("TICKETCREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-rounded btn-secondary" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
          </tr>
          <?php } ?>
          <tr>
          <td colspan="6"></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></td>
         </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA CREDITOS POR CLIENTES ########################
?>

<?php
######################## BUSQUEDA DETALLES CREDITOS POR CLIENTES ########################
if (isset($_GET['BuscaDetallesCreditosxClientes']) && isset($_GET['codsucursal']) && isset($_GET['status']) && isset($_GET['codcliente'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $status = limpiar($_GET['status']);
  $codcliente = limpiar($_GET['codcliente']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($status=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE ESTADO DE CRÉDITO PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcliente=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR REALICE LA BÚSQUEDA DEL CLIENTE CORRECTAMENTE</center>";
   echo "</div>";   
   exit;

} else {

$pre = new Login();
$reg = $pre->BuscarDetallesCreditosxClientes();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Créditos por Cientes</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&status=<?php echo $status; ?>&codcliente=<?php echo $codcliente; ?>&tipo=<?php echo encrypt("DETALLESCREDITOSXCLIENTES"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&status=<?php echo $status; ?>&codcliente=<?php echo $codcliente; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("DETALLESCREDITOSXCLIENTES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&status=<?php echo $status; ?>&codcliente=<?php echo $codcliente; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("DETALLESCREDITOSXCLIENTES"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Estado de Crédito: </label> <?php if(decrypt($status) == 1){ echo "GENERAL"; }elseif(decrypt($status) == 2){ echo "PAGADA"; } elseif(decrypt($status) == 3){ echo "PENDIENTE"; }  ?><br>

            <label class="control-label">Nº de <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: </label> <?php echo $reg[0]['dnicliente']; ?><br>

            <label class="control-label">Nombre de Cliente: </label> <?php echo $reg[0]['nomcliente']; ?><br>
            
            <label class="control-label">Nº de Telefono: </label> <?php echo $reg[0]['tlfcliente'] == "" ? "********" : $reg[0]['tlfcliente']; ?><br>

            <label class="control-label">Dirección Domiciliaria: </label> <?php echo $reg[0]['direccliente'] == "" ? "********" : $reg[0]['direccliente']; ?>
        </div>
      </div>

      <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                        <thead>
                          <tr>
                            <th>Nº</th>
                            <th>N° de Venta</th>
                            <th>Descripción de Cliente</th>
                            <th>Estado</th>
                            <th>Dias Venc</th>
                            <th>Fecha Emisión</th>
                            <th>Detalles Productos</th>
                            <th>Imp. Total</th>
                            <th>Total Abono</th>
                            <th>Total Debe</th>
                            <th><i class="mdi mdi-drag-horizontal"></i></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
$a=1;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
   
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
  <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td>
  <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
  elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
  else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
  <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
  elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
  <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
  <td class="font-10 bold"><?php echo $reg[$i]['detalles_productos']; ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  <td> <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("TICKETCREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-rounded btn-secondary" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a></td>
          </tr>
          <?php } ?>
          <tr>
           <td colspan="7"></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></td>
         </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
######################## BUSQUEDA DETALLES CREDITOS POR CLIENTES ########################
?>









































<?php
######################## MOSTRAR FACTURA PARA NOTA DE CREDITO ########################
if (isset($_GET['ProcesaNotaCredito']) && isset($_GET['numeroventa']) && isset($_GET['codsucursal']) && isset($_GET['descontar'])) { 
 
  $numeroventa = limpiar($_GET['numeroventa']);
  $codsucursal = limpiar($_GET['codsucursal']);
  $descontar = limpiar($_GET['descontar']);
  $codarqueo = limpiar(isset($_GET['codarqueo']) ? $_GET["codarqueo"] : "");

  $reg = $new->BuscarVentasPorId();
  $simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

 if($numeroventa=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR REALICE LA BÚSQUEDA DEL Nº DE DOCUMENTO CORRECTAMENTE</center>";
   echo "</div>";   
   exit;

 	} else if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if(isset($_GET['codarqueo']) && $codarqueo=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

 } elseif($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> EL Nº DE DOCUMENTO INGRESADO NO SE ENCUENTRA REGISTRADO </center>";
    echo "</div>";    

} else {

?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Detalle de <?php echo $reg[0]['tipodocumento']." Nº: ".$reg[0]['codfactura']; ?></h4>
      </div>

      <div class="form-body">
        <div class="card-body">


          <div class="row">
            <div class="col-md-12">
              <div class="pull-left">
                <address>
  <h4><b class="text-danger">RAZÓN SOCIAL</b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomsucursal']; ?>,
  <br/> Nº <?php echo $reg[0]['documsucursal'] == '0' ? "DOCUMENTO" : $reg[0]['documento'] ?>: <?php echo $reg[0]['cuitsucursal']; ?> - TLF: <?php echo $reg[0]['tlfsucursal']; ?>

  <?php 
  if(isset($_GET['codarqueo'])){ 
  $arqueo = new Login();
  $arqueo = $arqueo->ArqueoCajaPorId();
  ?>
  <br><strong>CAJA PARA NOTA CRÉDITO:</strong> <span class="text-danger alert-link"><?php echo $arqueo[0]['nrocaja'].": ".$arqueo[0]['nomcaja']; ?></span>
  <br><strong>SALDO EN CAJA:</strong> <span class="text-danger alert-link"><?php echo number_format($arqueo[0]['efectivo']-$arqueo[0]['egresos'], 2, '.', ','); ?></span>
  <?php } ?></p>

  <h4><b class="text-danger">Nº <?php echo $reg[0]['tipodocumento']." ". $reg[0]['codfactura']; ?></b></h4>
  <p class="text-muted m-l-5">Nº SERIE: <?php echo $reg[0]['codserie']; ?>

  <?php if($reg[0]['codmesa']!= '0') { ?>
  <br><?php echo $reg[0]['nomsala'].": ".$reg[0]['nommesa']; ?>
  <?php } ?>

  <br>Nº DE CAJA: <?php echo $reg[0]['nrocaja'].": ".$reg[0]['nomcaja']; ?>
  
  <?php if($reg[0]['fechavencecredito']!= "0000-00-00") { ?>
  <br>DIAS VENCIDOS: 
  <?php if($reg[0]['fechavencecredito']== '0000-00-00') { echo "0"; } 
        elseif($reg[0]['fechavencecredito'] >= date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo "0"; } 
        elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[0]['fechavencecredito']); }
        elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[0]['fechapagado'],$reg[0]['fechavencecredito']); } ?>
  <?php } ?>

  <br>STATUS: 
  <?php if($reg[0]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[0]["statusventa"]."</span>"; } 
      elseif($reg[0]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[0]["statusventa"]."</span>"; }
      elseif($reg[0]['fechavencecredito'] < date("Y-m-d") && $reg[0]['fechapagado'] == "0000-00-00" && $reg[0]['statusventa'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
      else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[0]["statusventa"]."</span>"; } ?>
  
  <?php if($reg[0]['fechapagado']!= "0000-00-00") { ?>
  <br>FECHA PAGADA: <?php echo date("d-m-Y",strtotime($reg[0]['fechapagado'])); ?>
  <?php } ?>

  <br>FECHA DE EMISIÓN: <?php echo date("d-m-Y H:i:s",strtotime($reg[0]['fechaventa'])); ?></p>
              </address>
            </div>
                                    
   <div class="pull-right text-right">
              <address>
  <h4><b class="text-danger">CLIENTE</b></h4>
  <p class="text-muted m-l-30"><?php echo $reg[0]['nomcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['nomcliente']; ?>,
  <br/>DIREC: <?php echo $reg[0]['direccliente'] == '' ? "******" : $reg[0]['direccliente']; ?> <?php echo $reg[0]['provincia2'] == '' ? "" : $reg[0]['provincia2']; ?> <?php echo $reg[0]['departamento2'] == '' ? "" : $reg[0]['departamento2']; ?>
  <br/> EMAIL: <?php echo $reg[0]['emailcliente'] == '' ? "******" : $reg[0]['emailcliente']; ?>
  <br/> Nº <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3'] ?>: <?php echo $reg[0]['dnicliente'] == '' ? "******" : $reg[0]['dnicliente']; ?> - TLF: <?php echo $reg[0]['tlfcliente'] == '' ? "******" : $reg[0]['tlfcliente']; ?></p>
              </address>

      <div class="row">
        <div class="col-md-12">
          <div class="form-group">
            <h4><b class="text-danger">PROCEDIMIENTO</b></h4>
            
            <div class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" id="evento1" name="observaciones" value="DOCUMENTO ANULADO" checked="checked">
              <label class="custom-control-label" for="evento1">DOCUMENTO ANULADO</label>
            </div>

            <div class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" id="evento2" name="observaciones" value="TEXTO CORREJIDO">
              <label class="custom-control-label" for="evento2">TEXTO CORREJIDO</label>
            </div>

            <div class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" id="evento3" name="observaciones" value="MONTO CORREJIDO">
              <label class="custom-control-label" for="evento3">MONTO CORREJIDO</label>
            </div>

          </div>
        </div>
      </div>
        
    </div>

        </div>
    </div>

      <div class="table-responsive m-t-20">
        <table class="table2 table-hover">
            <thead>
                <tr>
                    <th>Devolución</th>
                    <th>Vendido</th>
                    <th class="text-left">Descripción de Producto</th>
                    <th>Precio Unitario</th>
                    <th>Valor Total</th>
                    <th>Desc %</th>
                    <th><?php echo $impuesto; ?></th>
                    <th>Valor Neto</th>
                </tr>
            </thead>
            <tbody>
<?php 
$tra = new Login();
$detalle = $tra->BuscarDetallesVentas();

$SubTotal = 0;
$a=1;
$b=0;
$count = 0;
for($i=0;$i<sizeof($detalle);$i++){  
$SubTotal += $detalle[$i]['valorneto'];
$c = $b++; 
$count++; 
?>
  <tr class="warning-element" style="border-left: 2px solid #ffb22b !important; background: #fefde3;">
  <td>
  <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected input-group-sm">
  <span class="input-group-btn input-group-prepend"><button class="btn btn-classic bootstrap-touchspin-down input-button" style="cursor:pointer;border-radius:5px 0px 0px 5px;background-color:#cd874a;" type="button" onClick="PresionarDetalleDevolucion('a',<?php echo $count; ?>)"><span class='fa fa-minus'></span></button></span>
  <input type="text" class="bold" name="devuelto[]" id="devuelto_<?php echo $count; ?>" style="width:60px;height:40px;font-size:14px;background:#e7f8fc;font-weight:bold;" onfocus="this.style.background=('#e7f8fc')" onKeyPress="EvaluateText('%f', this);" onBlur="this.style.background=('#e7f8fc'); this.value = NumberFormat(this.value, '2', '.', '');" onKeyUp="this.value=this.value.toUpperCase(); ProcesarCalculoDevolucion(<?php echo $count; ?>);" autocomplete="off" placeholder="Cantidad" value="0.00" title="Ingrese Cantidad">
  <span class="input-group-btn input-group-append"><button class="btn btn-classic bootstrap-touchspin-up" type="button" style="cursor:pointer;border-radius:0px 5px 5px 0px;background-color:#cd874a;" onClick="PresionarDetalleDevolucion('b',<?php echo $count; ?>)"><span class='fa fa-plus'></span></button></span>
  </div>
  </td>

  <td class="text-dark alert-link"><?php echo number_format($detalle[$i]["cantventa"], 2, '.', ''); ?></td>

  <td class="text-left">
  <input type="hidden" name="coddetalleventa[]" id="coddetalleventa" value="<?php echo encrypt($detalle[$i]["coddetalleventa"]); ?>">
  <input type="hidden" name="tipo[]" id="tipo" value="<?php echo $detalle[$i]["tipo"]; ?>">
  <input type="hidden" name="idproducto[]" id="idproducto" value="<?php echo $detalle[$i]["idproducto"]; ?>">
  <input type="hidden" name="codproducto[]" id="codproducto" value="<?php echo $detalle[$i]["codproducto"]; ?>">
  <input type="hidden" name="producto[]" id="producto" value="<?php echo $detalle[$i]["producto"]; ?>">
  <input type="hidden" name="codcategoria[]" id="codcategoria" value="<?php echo $detalle[$i]["codcategoria"]; ?>">
  <input type="hidden" name="cantidad[]" id="cantidad_<?php echo $count; ?>" value="<?php echo $detalle[$i]["cantventa"]; ?>">
  <h5 class="text-dark alert-link"><?php echo $detalle[$i]['producto']; ?></h5>
  <small class="text-danger alert-link"><?php echo $detalle[$i]['codcategoria'] == '0' ? "**********" : $detalle[$i]['nomcategoria'] ?></small></strong></td>
  <td>
  <input type="hidden" name="precioconiva[]" id="precioconiva_<?php echo $count; ?>" value="<?php echo $detalle[$i]['ivaproducto'] == '0.00' ? "0.00" : number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>">
  <input type="hidden" name="preciocompra[]" id="preciocompra_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["preciocompra"], 2, '.', ''); ?>">
  <input type="hidden" name="precioventa[]" id="precioventa_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["precioventa"], 2, '.', ''); ?>"><strong><?php echo $simbolo.number_format($detalle[$i]['precioventa'], 2, '.', ''); ?></strong></td>
  <td>
  <input type="hidden" name="valortotal[]" id="valortotal_<?php echo $count; ?>" value="0.00">
  <strong><label id="txtvalortotal_<?php echo $count; ?>">0.00</label></strong></td>
    
  <td>
  <input type="hidden" name="descproducto[]" id="descproducto_<?php echo $count; ?>" value="<?php echo number_format($detalle[$i]["descproducto"], 2, '.', ''); ?>">
  <input type="hidden" class="totaldescuentov" name="totaldescuentov[]" id="totaldescuentov_<?php echo $count; ?>" value="0.00"><strong><?php echo $simbolo.number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?><sup>0.00%</sup></strong></td>

  <td>
  <input type="hidden" name="ivaproducto[]" id="ivaproducto_<?php echo $count; ?>" value="<?php echo $detalle[$i]["ivaproducto"]; ?>"><strong><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', '')."%" : "(E)"; ?></strong>
  </td>

  <td>
  <input type="hidden" class="subtotalivasi" name="subtotalivasi[]" id="subtotalivasi_<?php echo $count; ?>" value="0.00">
  <input type="hidden" class="subtotalivano" name="subtotalivano[]" id="subtotalivano_<?php echo $count; ?>" value="0.00">
  <input type="hidden" class="subtotalimpuestos" name="subtotalimpuestos[]" id="subtotalimpuestos_<?php echo $count; ?>" value="0.00">
  <input type="hidden" class="subtotaldiscriminado" name="subtotaldiscriminado[]" id="subtotaldiscriminado_<?php echo $count; ?>" value="0.00">
  <input type="hidden" class="valorneto" name="valorneto[]" id="valorneto_<?php echo $count; ?>" value="0.00" >
  <strong> <label id="txtvalorneto_<?php echo $count; ?>">0.00</label></strong></td>
            </tr>
          <?php } ?>
        </tbody>
      </table><hr>

  <input type="hidden" name="idventa" id="idventa" value="<?php echo encrypt($reg[0]['idventa']); ?>">
  <input type="hidden" name="codventa" id="codventa" value="<?php echo encrypt($reg[0]['codventa']); ?>">
  <input type="hidden" name="codfactura" id="codfactura" value="<?php echo encrypt($reg[0]['codfactura']); ?>">
  <input type="hidden" name="tipodocumento" id="tipodocumento" value="<?php echo $reg[0]['tipodocumento']; ?>"/>
  <input type="hidden" name="tipopago" id="tipopago" value="<?php echo $reg[0]['tipopago']; ?>"/>
  <input type="hidden" name="abonototal" id="abonototal" value="<?php echo number_format($reg[0]["creditopagado"], 2, '.', ''); ?>"/>
  <input type="hidden" name="codcliente" id="codcliente" value="<?php echo $codigo = ($reg[0]['codcliente'] == "" ? "0" : $reg[0]['codcliente']); ?>"/>
  <input type="hidden" name="tipocliente" id="tipocliente" value="<?php echo $tipo = ($reg[0]['tipocliente'] == "" ? "0" : $reg[0]['tipocliente']); ?>"/>
  <input type="hidden" name="dnicliente" id="dnicliente" value="<?php echo $dni = ($reg[0]['dnicliente'] == "" ? "0" : $reg[0]['dnicliente']); ?>"/>
  <input type="hidden" name="nomcliente" id="nomcliente" value="<?php echo $nombre = ($reg[0]['nomcliente'] == "" ? "0" : $reg[0]['nomcliente']); ?>"/>
  <input type="hidden" name="girocliente" id="girocliente" value="<?php echo $giro = ($reg[0]['girocliente'] == "" ? "0" : $reg[0]['girocliente']); ?>"/>
  <input type="hidden" name="provincia" id="provincia" value="<?php echo $provincia = ($reg[0]['id_provincia'] == "" || $reg[0]['id_provincia'] == "0" ? "0" : $reg[0]['provincia']); ?>"/>
  <input type="hidden" name="departamento" id="departamento" value="<?php echo $departamento = ($reg[0]['id_departamento'] == "" || $reg[0]['id_departamento'] == "0"  ? "0" : $reg[0]['departamento']); ?>"/>
  <input type="hidden" name="direccliente" id="girocliente" value="<?php echo $direccion = ($reg[0]['direccliente'] == "" ? "0" : $reg[0]['direccliente']); ?>"/>

           <table id="carritototal" class="table-responsive">
              <tr>
  <td width="250"><h5><label>Gravado (<?php echo number_format($reg[0]['iva'], 2, '.', '') ?>%):</label></h5></td>
  <td width="250">
  <h5><?php echo $simbolo; ?><label id="lblsubtotal" name="lblsubtotal">0.00</label></h5>
  <input type="hidden" name="txtdiscriminado" id="txtdiscriminado" value="0"/>
  <input type="hidden" name="txtsubtotal" id="txtsubtotal" value="0.00"/>    
  </td>
                
  <td width="250">
  <h5><label>Exento (0%):</label></h5>    
  </td>

  <td width="250">
  <h5><?php echo $simbolo; ?><label id="lblsubtotal2" name="lblsubtotal2">0.00</label></h5>
  <input type="hidden" name="txtsubtotal2" id="txtsubtotal2" value="0.00"/>    
  </td>
  
  <td width="250"><h5><label><?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', '') ?>%):<input type="hidden" name="iva" id="iva" autocomplete="off" value="<?php echo number_format($reg[0]['iva'], 2, '.', '') ?>"></label></h5>
  </td>

  <td class="text-center" width="250">
  <h5><?php echo $simbolo; ?><label id="lbliva" name="lbliva">0.00</label></h5>
  <input type="hidden" name="txtIva" id="txtIva" value="0.00"/>
  </td>
  </tr>
  <tr>
  <td>
  <h5><label>Descontado %:</label></h5> 
  </td>
  <td>
  <h5><?php echo $simbolo; ?><label id="lbldescontado" name="lbldescontado">0.00</label></h5>
  <input type="hidden" name="txtdescontado" id="txtdescontado" value="0.00"/>
  </td>
  <td>
  <h5><label>Desc. Global <?php echo number_format($reg[0]['descuento'], 2, '.', '') ?>%:</label></h5>    
  </td>
  <td>
  <h5><?php echo $simbolo; ?><label id="lbldescuento" name="lbldescuento">0.00</label></h5>
  <input type="hidden" name="descuento" id="descuento" value="<?php echo number_format($reg[0]['descuento'], 2, '.', '') ?>">
  <input type="hidden" name="txtDescuento" id="txtDescuento" value="0.00"/></td>

  <td><h4><b>Importe Total</b></h4></td>

  <td class="text-center">
  <h4><b><?php echo $simbolo; ?><label id="lbltotal" name="lbltotal">0.00</label></b></h4>
  <input type="hidden" name="txtTotal" id="txtTotal" value="0.00"/></td>
  </tr>
  </table>
  </div><hr>

      <div class="text-right">
<button type="submit" name="btn-submit" id="btn-submit" class="btn btn-primary"><span class="fa fa-save"></span> Guardar Nota</button>
      </div>
          
        </div>
      </div>

    </div>
  </div>
</div>
<!-- End Row -->

<?php  
  }
}
######################## MOSTRAR FACTURA PARA NOTA DE CREDITO ########################
?>


<?php
######################## MOSTRAR NOTA DE CREDITO EN VENTANA MODAL ########################
if (isset($_GET['BuscaNotaCreditoModal']) && isset($_GET['codnota']) && isset($_GET['codsucursal'])) { 
 
$reg = $new->NotasCreditoPorId();
$simbolo = ($reg[0]['simbolo'] == "" ? "" : "<strong>".$reg[0]['simbolo']."</strong>");

  if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON VENTAS Y DETALLES ACTUALMENTE </center>";
    echo "</div>";    

} else {
?>
                <div class="row">
                  <div class="col-md-12">
                      <div class="pull-left">
                          <address>
  <h4><b class="text-danger">RAZÓN SOCIAL</b></h4>
  <p class="text-muted m-l-5"><?php echo $reg[0]['nomsucursal']; ?>,
  <br/> Nº <?php echo $reg[0]['documsucursal'] == '0' ? "DOCUMENTO" : $reg[0]['documento'] ?>: <?php echo $reg[0]['cuitsucursal']; ?> - TLF: <?php echo $reg[0]['tlfsucursal']; ?></p>

  <h4><b class="text-danger">Nº NOTA CRÉDITO <?php echo $reg[0]['codfactura']; ?></b></h4>
  <p class="text-muted m-l-5">Nº <?php echo $reg[0]['tipodocumento']; ?>: <?php echo $reg[0]['facturaventa']; ?>

  <br>OBSERVACIÓN: <?php echo $reg[0]["observaciones"]; ?>
  <br>Nº DE CAJA: <?php echo $caja = ($reg[0]['codcaja'] == 0 ? "**********": $reg[0]['nrocaja'].": ".$reg[0]['nomcaja']); ?>
  <br>FECHA DE EMISIÓN: <?php echo date("d-m-Y H:i:s",strtotime($reg[0]['fechanota'])); ?></p>
                            </address>
                        </div>
                        
                        <div class="pull-right text-right">
                            <address>
  <h4><b class="text-danger">CLIENTE</b></h4>
  <p class="text-muted m-l-30"><?php echo $reg[0]['nomcliente'] == '' ? "CONSUMIDOR FINAL" : $reg[0]['nomcliente']; ?>,
  <br/>DIREC: <?php echo $reg[0]['direccliente'] == '' ? "******" : $reg[0]['direccliente']; ?> <?php echo $reg[0]['provincia2'] == '' ? "" : $reg[0]['provincia2']; ?> <?php echo $reg[0]['departamento2'] == '' ? "" : $reg[0]['departamento2']; ?>
  <br/> EMAIL: <?php echo $reg[0]['emailcliente'] == '' ? "******" : $reg[0]['emailcliente']; ?>
  <br/> Nº <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3'] ?>: <?php echo $reg[0]['dnicliente'] == '' ? "******" : $reg[0]['dnicliente']; ?> - TLF: <?php echo $reg[0]['tlfcliente'] == '' ? "******" : $reg[0]['tlfcliente']; ?></p>
                             </address>
                        </div>
                    </div>
                        
                    <div class="col-md-12">
                      <div class="table-responsive m-t-10" style="clear: both;">
                        <div id="div1"><table class="table2 table-hover">
                        <thead>
                        <tr>
                        <th>#</th>
                        <th class="text-left">Descripción de Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Valor Total</th>
                        <th>Desc %</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Valor Neto</th>
                        </tr>
                        </thead>
                        <tbody>
<?php 
$tra = new Login();
$detalle = $tra->VerDetallesNotasCredito();

$SubTotal = 0;
$a=1;
for($i=0;$i<sizeof($detalle);$i++){  
$SubTotal += $detalle[$i]['valorneto'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td class="text-left"><h5><strong><?php echo $detalle[$i]['producto']; ?></strong></h5>
  <small class="text-danger alert-link">
  <?php if($detalle[$i]['tipo'] == 1){
    echo $detalle[$i]['nomcategoria'];
  } elseif($detalle[$i]['tipo'] == 2){
    echo "**********";
  } else {
    echo $detalle[$i]['nommedida'];
  } ?>
  </small></small></td>
  <td><?php echo $detalle[$i]['cantventa']; ?></strong></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['precioventa'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valortotal'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['totaldescuentov'], 2, '.', ','); ?><sup><strong><?php echo number_format($detalle[$i]['descproducto'], 2, '.', ','); ?>%</strong></sup></label></td>
  <td><?php echo $detalle[$i]['ivaproducto'] != '0.00' ? number_format($detalle[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></label></td>
  <td><?php echo $simbolo.number_format($detalle[$i]['valorneto'], 2, '.', ','); ?></td>
              </tr>
              <?php } ?>
                        </tbody>
                        </table></div>
                    </div>
                  </div>


                                <div class="col-md-12">

                                    <div class="pull-right text-right">
<p><b>Subtotal:</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi']+$reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Total Grabado (<?php echo number_format($reg[0]['iva'], 2, '.', ',') ?>%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivasi'], 2, '.', ','); ?><p>
<p><b>Total Exento (0%):</b> <?php echo $simbolo.number_format($reg[0]['subtotalivano'], 2, '.', ','); ?></p>
<p><b>Total <?php echo $impuesto; ?> (<?php echo number_format($reg[0]['iva'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaliva'], 2, '.', ','); ?> </p>
<p><b>Desc. Global (<?php echo number_format($reg[0]['descuento'], 2, '.', ','); ?>%):</b> <?php echo $simbolo.number_format($reg[0]['totaldescuento'], 2, '.', ','); ?> </p>
                                        <hr>
<h4><b>Importe Total:</b> <?php echo $simbolo.number_format($reg[0]['totalpago'], 2, '.', ','); ?></h4></div>
                                    <div class="clearfix"></div>
                                    <hr>

                            <div class="col-md-12">
                                <div class="text-right">
<a href="reportepdf?codnota=<?php echo encrypt($reg[0]['codnota']); ?>&codsucursal=<?php echo encrypt($reg[0]['codsucursal']); ?>&tipo=<?php echo encrypt("NOTACREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button id="print" class="btn waves-light btn-light" type="button"><span><i class="fa fa-print"></i> Imprimir</span></button></a>
<button type="button" class="btn btn-dark" data-dismiss="modal"><span class="fa fa-times-circle"></span> Cerrar</button>
                                </div>
                            </div>
                        </div>
                <!-- .row -->
<?php
  }
} 
######################## MOSTRAR NOTA DE CREDITO EN VENTANA MODAL ########################
?>


<?php
########################## BUSQUEDA NOTAS DE CREDITOS POR CAJAS ##########################
if (isset($_GET['BuscaNotasxCajas']) && isset($_GET['codsucursal']) && isset($_GET['codcaja']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcaja = limpiar($_GET['codcaja']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcaja=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE CAJA PARA TU BÚSQUEDA</center>";
   echo "</div>";   
   exit;

  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


  } else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

  } elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarNotasxCajas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Notas de Créditos por Caja </h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("NOTASXCAJAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("NOTASXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcaja=<?php echo $codcaja; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("NOTASXCAJAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de Caja: </label> <?php echo $reg[0]['nrocaja']; ?><br>

            <label class="control-label">Nombre de Caja: </label> <?php echo $reg[0]['nomcaja']; ?><br>

            <label class="control-label">Horario de Caja: </label> <?php echo $reg[0]['hora_desde']." - ".$reg[0]['hora_hasta']; ?><br>

            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

  <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                              <thead>
                              <tr>
                                <th>N°</th>
                                <th>N° de Nota</th>
                                <th>Nº de Documento</th>
                                <th>Descripción de Cliente</th>
                                <th>Motivo de Nota</th>
                                <th>Fecha Emisión</th>
                                <th>Nº Artic</th>
                                <th>SubTotal</th>
                                <th><?php echo $impuesto; ?></th>
                                <th>Dcto %</th>
                                <th>Imp. Total</th>
                                <th><i class="mdi mdi-drag-horizontal"></i></th>
                                </tr>
                              </thead>
                              <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><?php echo $reg[$i]['tipodocumento']." Nº: ".$reg[$i]['facturaventa']; ?></td>
  <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></td>
  <td><?php echo $reg[$i]['observaciones']; ?></td>

  <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechanota'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  <td>
  <a href="reportepdf?codnota=<?php echo encrypt($reg[$i]['codnota']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("NOTACREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } ?>
        <tr>
        <td colspan="6"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
         </tr>
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
########################## BUSQUEDA NOTAS DE CREDITOS POR CAJAS ########################
?>

<?php
########################## BUSQUEDA NOTAS DE CREDITOS POR FECHAS ##########################
if (isset($_GET['BuscaNotasxFechas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $desde = limpiar($_GET['desde']);
  $hasta = limpiar($_GET['hasta']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($desde=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA DE INICIO PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;


} else if($hasta=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE FECHA FINAL PARA TU BÚSQUEDA</center>";
  echo "</div>"; 
  exit;

} elseif (strtotime($desde) > strtotime($hasta)) {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> LA FECHA DE INICIO NO PUEDE SER MAYOR QUE LA FECHA DE FIN</center>";
  echo "</div>"; 
  exit;

} else {

$pre = new Login();
$reg = $pre->BuscarNotasxFechas();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Notas de Créditos por Fechas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("NOTASXFECHAS"); ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("NOTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("NOTASXFECHAS"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Fecha Desde: </label> <?php echo date("d-m-Y", strtotime($desde)); ?><br>

            <label class="control-label">Fecha Hasta: </label> <?php echo date("d-m-Y", strtotime($hasta)); ?>
        </div>
      </div>

  <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th>N°</th>
            <th>N° de Caja</th>
            <th>N° de Nota</th>
            <th>Nº de Documento</th>
            <th>Descripción de Cliente</th>
            <th>Motivo de Nota</th>
            <th>Fecha Emisión</th>
            <th>Nº Artic</th>
            <th>SubTotal</th>
            <th><?php echo $impuesto; ?></th>
            <th>Dcto %</th>
            <th>Imp. Total</th>
            <th><i class="mdi mdi-drag-horizontal"></i></th>
          </tr>
        </thead>
        <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $caja = ($reg[$i]['codcaja'] == '0' ? "**********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?></td>
  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><?php echo $reg[$i]['tipodocumento']." Nº: ".$reg[$i]['facturaventa']; ?></td>
  <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></td>
  <td><?php echo $reg[$i]['observaciones']; ?></td>

  <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechanota'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  <td>
  <a href="reportepdf?codnota=<?php echo encrypt($reg[$i]['codnota']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("NOTACREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
          </td>
          </tr>
          <?php } ?>
         <tr>
          <td colspan="7"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
         </tr>
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
########################## BUSQUEDA NOTAS DE CREDITOS POR FECHAS ########################
?>


<?php
######################## BUSQUEDA NOTAS DE CREDITOS POR CLIENTES ########################
if (isset($_GET['BuscaNotasxClientes']) && isset($_GET['codsucursal']) && isset($_GET['codcliente'])) {
  
  $codsucursal = limpiar($_GET['codsucursal']);
  $codcliente = limpiar($_GET['codcliente']);

 if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;
   
  } else if($codcliente=="") {

   echo "<div class='alert alert-danger'>";
   echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
   echo "<center><span class='fa fa-info-circle'></span> POR FAVOR REALICE LA BÚSQUEDA DEL CLIENTE CORRECTAMENTE</center>";
   echo "</div>";   
   exit;

} else {

$pre = new Login();
$reg = $pre->BuscarNotasxClientes();
  ?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Notas de Créditos por Clientes</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

          <div class="row">
            <div class="col-md-7">
              <div class="btn-group m-b-20">
              <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&codcliente=<?php echo $codcliente; ?>&tipo=<?php echo encrypt("NOTASXCLIENTE"); ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcliente=<?php echo $codcliente; ?>&documento=<?php echo encrypt("EXCEL"); ?>&tipo=<?php echo encrypt("NOTASXCLIENTE"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&codcliente=<?php echo $codcliente; ?>&documento=<?php echo encrypt("WORD"); ?>&tipo=<?php echo encrypt("NOTASXCLIENTE"); ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
          </div>

      <div class="row">
        <div class="col-md-12">
            <label class="control-label">Nº de <?php echo $reg[0]['documcliente'] == '0' ? "DOCUMENTO" : $reg[0]['documento3']; ?>: </label> <?php echo $reg[0]['dnicliente']; ?><br>

            <label class="control-label">Nombre de Cliente: </label> <?php echo $reg[0]['nomcliente']; ?><br>
        </div>
      </div>

  <div id="div3"><table id="datatable-scroller" class="table2 table-hover table-striped table-bordered nowrap" cellspacing="0" width="100%">
                        <thead>
                        <tr>
                          <th>N°</th>
                          <th>N° de Caja</th>
                          <th>N° de Nota</th>
                          <th>Nº de Documento</th>
                          <th>Motivo de Nota</th>
                          <th>Fecha Emisión</th>
                          <th>Nº Artic</th>
                          <th>SubTotal</th>
                          <th><?php echo $impuesto; ?></th>
                          <th>Dcto %</th>
                          <th>Imp. Total</th>
                          <th><i class="mdi mdi-drag-horizontal"></i></th>
                          </tr>
                        </thead>
                        <tbody>
<?php
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalImporte+=$reg[$i]['totalpago'];
?>
  <tr>
  <td><?php echo $a++; ?></td>
  <td><?php echo $caja = ($reg[$i]['codcaja'] == '0' ? "**********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?></td>
  <td><?php echo $reg[$i]['codfactura']; ?></td>
  <td><?php echo $reg[$i]['tipodocumento']." Nº: ".$reg[$i]['facturaventa']; ?></td>
  <td><?php echo $reg[$i]['observaciones']; ?></td>

  <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechanota'])); ?></td>
  <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
  <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  <td>
  <a href="reportepdf?codnota=<?php echo encrypt($reg[$i]['codnota']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("NOTACREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } ?>
        <tr>
          <td colspan="6"></td>
<td><?php echo number_format($TotalArticulos, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></td>
<td><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></td>
         </tr>
            </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

<?php
  }
} 
########################## BUSQUEDA NOTAS DE CREDITOS POR CLIENTES ##########################
?>