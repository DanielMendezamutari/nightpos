<?php
require_once("class/class.php");
  if (isset($_SESSION['acceso'])) {
    if ($_SESSION['acceso'] == "administradorG" || $_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="secretaria" || $_SESSION["acceso"]=="cajero" || $_SESSION["acceso"]=="mesero" || $_SESSION["acceso"]=="cocinero" || $_SESSION["acceso"]=="repartidor") {

$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "IMPUESTO" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']);

$con = new Login();
$con = $con->ConfiguracionPorId();

$tipo = decrypt($_GET['tipo']);
$documento = decrypt($_GET['documento']);
$extension = $documento == 'EXCEL' ? '.xls' : '.doc';

switch($tipo)
{

############################### MODULO DE CONFIGURACIONES ###############################

case 'PROVINCIAS': 

$archivo = str_replace(" ", "_","LISTADO DE PROVINCIAS");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>NOMBRE DE PROVINCIA</th>
         </tr>
      <?php 
$tra = new Login();
$reg = $tra->ListarProvincias();

if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
         <tr class="text-center" class="even_row">
           <td><?php echo $reg[$i]['id_provincia']; ?></td>
           <td><?php echo $reg[$i]['provincia']; ?></td>
         </tr>
        <?php } } ?>
</table>
<?php
break;

case 'DEPARTAMENTOS': 

$archivo = str_replace(" ", "_","LISTADO DE DEPARTAMENTOS");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>NOMBRE DE PROVINCIA</th>
           <th>NOMBRE DE DEPARTAMENTO</th>
         </tr>
      <?php 
$tra = new Login();
$reg = $tra->ListarDepartamentos();

if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
         <tr class="text-center" class="even_row">
           <td><?php echo $a++; ?></td>
           <td><?php echo $reg[$i]['provincia']; ?></td>
           <td><?php echo $reg[$i]['departamento']; ?></td>
         </tr>
        <?php } } ?>
</table>
<?php
break;

case 'DOCUMENTOS':

$tra = new Login();
$reg = $tra->ListarDocumentos(); 

$archivo = str_replace(" ", "_","LISTADO DE DOCUMENTOS TRIBUTARIOS");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>NOMBRE DE DOCUMENTO</th>
           <th>DESCRIPCIÓN DE DOCUMENTO</th>
         </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
         <tr class="text-center" class="even_row">
           <td><?php echo $a++; ?></td>
           <td><?php echo $reg[$i]['documento']; ?></td>
           <td><?php echo $reg[$i]['descripcion']; ?></td>
         </tr>
        <?php } } ?>
</table>
<?php
break;

case 'TIPOMONEDA': 

$archivo = str_replace(" ", "_","LISTADO DE TIPOS DE MONEDA");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>NOMBRE DE MONEDA</th>
           <th>SIGLAS</th>
           <th>SIMBOLO</th>
         </tr>
      <?php 
$tra = new Login();
$reg = $tra->ListarTipoMoneda();

if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
         <tr class="text-center" class="even_row">
           <td><?php echo $a++; ?></td>
           <td><?php echo $reg[$i]['moneda']; ?></td>
           <td><?php echo $reg[$i]['siglas']; ?></td>
           <td><?php echo $reg[$i]['simbolo']; ?></td>
         </tr>
        <?php } } ?>
</table>
<?php
break;

case 'TIPOCAMBIO':

$tra = new Login();
$reg = $tra->ListarTipoCambio();

$archivo = str_replace(" ", "_","LISTADO DE TIPO DE CAMBIO EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>CÓDIGO</th>
           <th>DESCRIPCIÓN DE CAMBIO</th>
           <th>MONTO DE CAMBIO</th>
           <th>TIPO DE MONEDA</th>
           <th>FECHA DE INGRESO</th>
         </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
        <tr class="text-center" class="even_row">
          <td><?php echo $a++; ?></td>
          <td><?php echo $reg[$i]['codcambio']; ?></td>
          <td><?php echo $reg[$i]['descripcioncambio']; ?></td>
          <td><?php echo $reg[$i]['montocambio']; ?></td>
          <td><?php echo $reg[$i]['moneda']."/".$reg[$i]['siglas']; ?></td>
          <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechacambio'])); ?></td>
        </tr>
        <?php } } ?>
</table>
<?php
break;

case 'IMPUESTOS':

$tra = new Login();
$reg = $tra->ListarImpuestos(); 

$archivo = str_replace(" ", "_","LISTADO DE IMPUESTOS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>CÓDIGO</th>
        <th>NOMBRE DE IMPUESTO</th>
        <th>VALOR(%)</th>
        <th>ESTADO</th>
        <th>REGISTRO</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['codimpuesto']; ?></td>
      <td><?php echo $reg[$i]['nomimpuesto']; ?></td>
      <td><?php echo $reg[$i]['valorimpuesto']; ?></td>
      <td><?php echo $status = ($reg[$i]['statusimpuesto'] == 1 ? "ACTIVO" : "INACTIVO"); ?></td>
      <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaimpuesto'])); ?></td>
    </tr>
    <?php } } ?>
</table>
<?php
break;

case 'SUCURSALES': 

$archivo = str_replace(" ", "_","LISTADO DE SUCURSALES");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE SUCURSAL</th>
      <th>Nº DE DOCUMENTO</th>
      <th>NOMBRE DE SUCURSAL</th>
      <th>CÓDIGO GIRO</th>
      <th>GIRO DE SUCURSAL</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>PROVINCIA</th>
      <th>DEPARTAMENTO</th>
      <th>DIRECCIÓN</th>
      <?php } ?>
      <th>CORREO ELECTRONICO</th>
      <th>Nº DE TELÉFONO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>Nº DE ACTIVIDAD</th>
      <th>Nº DE TICKET</th>
      <th>Nº DE BOLETA</th>
      <th>Nº DE FACTURA</th>
      <th>Nº NOTA DE CRÉDITO</th>
      <th>FECHA DE AUTORIZACIÓN</th>
      <th>LLEVA CONTABILIDAD</th>
      <th>DESCUENTO GLOBAL</th>
      <th>MONEDA NACIONAL</th>
      <th>MONEDA CAMBIO</th>
      <th>Nº DOC. ENCARGADO</th>
      <?php } ?>
      <th>NOMBRE DE ENCARGADO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>Nº DE TELÉFONO ENCARGADO</th>
      <?php } ?>
      <th>ESTADO</th>
    </tr>
    <?php 
$tra = new Login();
$reg = $tra->ListarSucursales();

if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['nrosucursal']; ?></td>
    <td><?php echo $reg[$i]['documento'].": ".$reg[$i]['cuitsucursal']; ?></td>
    <td><?php echo $reg[$i]['nomsucursal']; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codgiro']; ?></td>
    <td><?php echo $reg[$i]['girosucursal']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['id_provincia'] == '0' ? "*********" : $reg[$i]['provincia']; ?></td>
    <td><?php echo $reg[$i]['id_departamento'] == '0' ? "*********" : $reg[$i]['departamento']; ?></td>
    <td><?php echo $reg[$i]['direcsucursal']; ?></td>
    <?php } ?>
    <td><?php echo $reg[$i]['correosucursal']; ?></td>
    <td><?php echo $reg[$i]['tlfsucursal']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo '&nbsp;'.$reg[$i]['nroactividadsucursal']; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['inicioticket']; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['inicioboleta']; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['iniciofactura']; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['inicionotacredito']; ?></td>
    <td><?php echo $reg[$i]['fechaautorsucursal'] == '0000-00-00' ? "*********" : date("d-m-Y",strtotime($reg[$i]['fechaautorsucursal'])); ?></td>
    <td><?php echo $reg[$i]['descsucursal']; ?></td>
    <td><?php echo $reg[$i]['llevacontabilidad']; ?></td>
    <td><?php echo $reg[$i]['codmoneda'] == '0' ? "*********" : $reg[$i]['moneda']; ?></td>
    <td><?php echo $reg[$i]['codmoneda2'] == '0' ? "*********" : $reg[$i]['moneda2']; ?></td>
    <td><?php echo $reg[$i]['documento2'].": ".$reg[$i]['dniencargado']; ?></td>
    <?php } ?>
    <td><?php echo $reg[$i]['nomencargado']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['tlfencargado'] == '' ? "*********" : $reg[$i]['tlfencargado']; ?></td>
    <?php } ?>
    <td><?php echo $status = ( $reg[$i]['estado'] == 1 ? "ACTIVO" : "INACTIVO"); ?></td>
  </tr>
  <?php } } ?>
</table>
<?php
break;

case 'HORARIOS':

$tra = new Login();
$reg = $tra->ListarHorarios(); 

$archivo = str_replace(" ", "_","LISTADO DE HORARIOS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>CÓDIGO</th>
        <th>HORA DESDE</th>
        <th>HORA HASTA</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['codhorario']; ?></td>
      <td><?php echo $reg[$i]['hora_desde']; ?></td>
      <td><?php echo $reg[$i]['hora_hasta']; ?></td>
    </tr>
    <?php } } ?>
</table>
<?php
break;

case 'CATEGORIAS':

$tra = new Login();
$reg = $tra->ListarCategorias(); 

$archivo = str_replace(" ", "_","LISTADO DE CATEGORIAS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>CÓDIGO</th>
        <th>NOMBRE DE CATEGORIA</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['codcategoria']; ?></td>
      <td><?php echo $reg[$i]['nomcategoria']; ?></td>
    </tr>
    <?php } } ?>
</table>
<?php
break;

case 'MEDIDAS':

$tra = new Login();
$reg = $tra->ListarMedidas(); 

$archivo = str_replace(" ", "_","LISTADO DE UNIDAD DE MEDIDAS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>CÓDIGO</th>
      <th>NOMBRE DE MEDIDA</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['codmedida']; ?></td>
      <td><?php echo $reg[$i]['nommedida']; ?></td>
    </tr>
    <?php } } ?>
</table>
<?php
break;

case 'SALSAS': 

$tra = new Login();
$reg = $tra->ListarSalsas();

$archivo = str_replace(" ", "_","LISTADO DE SALSAS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>CÓDIGO</th>
           <th>NOMBRE DE SALSA</th>
         </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
        <tr class="text-center" class="even_row">
          <td><?php echo $a++; ?></td>
          <td><?php echo $reg[$i]['codsalsa']; ?></td>
          <td><?php echo $reg[$i]['nomsalsa']; ?></td>
        </tr>
        <?php } } ?>
</table>
<?php
break;

case 'SALAS': 

$tra = new Login();
$reg = $tra->ListarSalas();

$archivo = str_replace(" ", "_","LISTADO DE SALAS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>NOMBRE DE SALA</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
        <tr class="text-center" class="even_row">
          <td><?php echo $a++; ?></td>
          <td><?php echo $reg[$i]['codsala']; ?></td>
          <td><?php echo $reg[$i]['nomsala']; ?></td>
        </tr>
        <?php } } ?>
</table>
<?php
break;

case 'MESAS': 

$tra = new Login();
$reg = $tra->ListarMesas();

$archivo = str_replace(" ", "_","LISTADO DE MESAS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>NOMBRE DE SALA</th>
          <th>NOMBRE DE MESA</th>
          <th>Nº DE PERSONAS</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
        <tr class="text-center" class="even_row">
          <td><?php echo $a++; ?></td>
          <td><?php echo $reg[$i]['codmesa']; ?></td>
          <td><?php echo $reg[$i]['nomsala']; ?></td>
          <td><?php echo $reg[$i]['nommesa']; ?></td>
          <td><?php echo $reg[$i]['puestos']; ?></td>
        </tr>
        <?php } } ?>
</table>
<?php
break;
############################### MODULO DE CONFIGURACIONES ##############################



################################## MODULO DE USUARIOS ##################################
case 'USUARIOS': 

$tra = new Login();
$reg = $tra->ListarUsuarios();

if ($_SESSION['acceso'] == "administradorG") {
$archivo = str_replace(" ", "_","LISTADO DE USUARIOS");
} else {
$archivo = str_replace(" ", "_","LISTADO DE USUARIOS EN (SUCURSAL ".$_SESSION['cuitsucursal']." ".$_SESSION['nomsucursal'].")");
}

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE DOCUMENTO</th>
      <th>NOMBRES Y APELLIDOS</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>SEXO</th>
      <th>Nº DE TELEFONO</th>
      <th>CORREO ELECTRONICO</th>
      <?php } ?>
      <th>USUARIO</th>
      <th>NIVEL</th>
      <th>ESTADO</th>
      <?php if ($_SESSION['acceso'] == "administradorG") { ?><th>SUCURSAL</th><?php } ?>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['dni']; ?></td>
      <td><?php echo $reg[$i]['nombres']; ?></td>
      <?php if ($documento == "EXCEL") { ?>
      <td><?php echo $reg[$i]['sexo']; ?></td>
      <td><?php echo $telefono = ($reg[$i]["telefono"] == '' ? "***********" : $reg[$i]['telefono']); ?></td>
      <td><?php echo $reg[$i]['email']; ?></td>
      <?php } ?>
      <td><?php echo $reg[$i]['usuario']; ?></td>
      <td><?php echo $reg[$i]['nivel']; ?></td>
      <td><?php echo $status = ( $reg[$i]['status'] == 1 ? "ACTIVO" : "INACTIVO"); ?></td>
      <?php if ($_SESSION['acceso'] == "administradorG") { ?><td><strong><?php echo $reg[$i]['cuitsucursal'].": ".$reg[$i]['nomsucursal']; ?></strong></td><?php } ?>
    </tr>
    <?php } } ?>
</table>
<?php
break;

case 'LOGS': 

$archivo = str_replace(" ", "_","LISTADO LOGS DE ACCESO");
header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>IP EQUIPO</th>
        <th>TIEMPO DE ENTRADA</th>
        <th>NAVEGADOR DE ACCESO</th>
        <th>PÁGINAS DE ACCESO</th>
        <th>USUARIOS</th>
      </tr>
<?php 
$tra = new Login();
$reg = $tra->ListarLogs();

if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['ip']; ?></td>
      <td><?php echo $reg[$i]['tiempo']; ?></td>
      <td><?php echo $reg[$i]['detalles']; ?></td>
      <td><?php echo $reg[$i]['paginas']; ?></td>
      <td><?php echo $reg[$i]['usuario']; ?></td>
    </tr>
    <?php } } ?>
</table>
<?php
break;
################################ MODULO DE USUARIOS ##############################














############################### MODULO DE CLIENTES ###################################
case 'CLIENTES': 

$tra = new Login();
$reg = $tra->ListarClientes();

$archivo = str_replace(" ", "_","LISTADO DE CLIENTES EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>TIPO CLIENTE</th>
        <th>TIPO DE DOCUMENTO</th>
        <th>Nº DE DOCUMENTO</th>
        <th>NOMBRES/RAZÓN SOCIAL</th>
        <th>GIRO DE CLIENTE</th>
        <th>Nº DE TELÉFONO</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>PROVINCIA</th>
        <th>DEPARTAMENTO</th>
        <th>DIRECCIÓN DOMICILIARIA</th>
        <th>CORREO ELECTRONICO</th>
        <?php } ?>
        <th>LIMITE DE CRÉDITO</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo $reg[$i]['tipocliente']; ?></td>
      <td><?php echo $reg[$i]['documcliente'] == '0' ? "*********" : $reg[$i]['documento']; ?></td>
      <td><?php echo $reg[$i]['dnicliente']; ?></td>
      <td><?php echo $cliente = ($reg[$i]['tipocliente'] == 'NATURAL' ? $reg[$i]['nomcliente'] : $reg[$i]['razoncliente']); ?></td>
      <td><?php echo $reg[$i]['girocliente'] == '' ? "*********" : $reg[$i]['girocliente']; ?></td>
      <td><?php echo $reg[$i]['tlfcliente'] == '' ? "*********" : $reg[$i]['tlfcliente']; ?></td>
      <?php if ($documento == "EXCEL") { ?>
      <td><?php echo $reg[$i]['id_provincia'] == '0' ? "*********" : $reg[$i]['provincia']; ?></td>
      <td><?php echo $reg[$i]['id_departamento'] == '0' ? "*********" : $reg[$i]['departamento']; ?></td>
      <td><?php echo $reg[$i]['direccliente']; ?></td>
      <td><?php echo $reg[$i]['emailcliente'] == '' ? "*********" : $reg[$i]['emailcliente']; ?></td>
      <?php } ?>
      <td><?php echo $reg[$i]['limitecredito']; ?></td>
    </tr>
    <?php } } ?>
</table>
<?php
break;
############################### MODULO DE CLIENTES ###################################










################################ MODULO DE PROVEEDORES #################################
case 'PROVEEDORES': 

$tra = new Login();
$reg = $tra->ListarProveedores();

$archivo = str_replace(" ", "_","LISTADO DE PROVEEDORES EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>TIPO DE DOCUMENTO</th>
        <th>Nº DE DOCUMENTO</th>
        <th>NOMBRE DE PROVEEDOR</th>
        <th>Nº DE TELÉFONO</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>PROVINCIA</th>
        <th>DEPARTAMENTO</th>
        <th>DIRECCIÓN DOMICILIARIA</th>
        <th>CORREO ELECTRONICO</th>
        <?php } ?>
        <th>VENDEDOR</th>
        <th>Nº DE TELÉFONO</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
      <tr class="text-center" class="even_row">
        <td><?php echo $a++; ?></td>
        <td><?php echo $reg[$i]['documproveedor'] == '0' ? "*********" : $reg[$i]['documento']; ?></td>
        <td><?php echo $reg[$i]['cuitproveedor']; ?></td>
        <td><?php echo $reg[$i]['nomproveedor']; ?></td>
        <td><?php echo $reg[$i]['tlfproveedor'] == '' ? "*********" : $reg[$i]['tlfproveedor']; ?></td>
        <?php if ($documento == "EXCEL") { ?>
        <td><?php echo $reg[$i]['id_provincia'] == '0' ? "*********" : $reg[$i]['provincia']; ?></td>
        <td><?php echo $reg[$i]['id_departamento'] == '0' ? "*********" : $reg[$i]['departamento']; ?></td>
        <td><?php echo $reg[$i]['direcproveedor']; ?></td>
        <td><?php echo $reg[$i]['emailproveedor'] == '' ? "*********" : $reg[$i]['emailproveedor']; ?></td>
        <?php } ?>
        <td><?php echo $reg[$i]['vendedor']; ?></td>
        <td><?php echo $reg[$i]['tlfvendedor']; ?></td>
      </tr>
      <?php } } ?>
</table>
<?php
break;
################################# MODULO DE PROVEEDORES ################################



























################################ MODULO DE INGREDIENTES ################################
case 'INGREDIENTES':

$tra = new Login();
$reg = $tra->ListarIngredientes();

$archivo = str_replace(" ", "_","LISTADO DE INGREDIENTES EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>CÓDIGO</th>
           <th>DESCRIPCIÓN DE INGREDIENTE</th>
           <th>MEDIDA</th>
           <?php if ($documento == "EXCEL") { ?>
           <th>STOCK MINIMO</th>
           <th>STOCK MÁXIMO</th>
           <?php } ?>
           <th><?php echo $impuesto; ?></th>
           <th>DESC %</th>
           <?php if ($documento == "EXCEL") { ?>
           <th>LOTE</th>
           <th>FECHA DE EXPIRACIÓN</th>
           <th>PROVEEDOR</th>
           <?php } ?>
           <th>PRECIO COMPRA</th>
           <th>PRECIO VENTA</th>
           <th>P.V EXT</th>
           <th>EXISTENCIA</th>
         </tr>
      <?php 

if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalCompra=0;
$TotalVenta=0;
$TotalMoneda=0;
$TotalArticulos=0;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
$simbolo2 = ($reg[$i]['simbolo2'] == "" ? "" : "<strong>".$reg[$i]['simbolo2']."</strong>");

$moneda = (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ','));

$TotalCompra+=$reg[$i]['preciocompra'];
$TotalVenta+=$reg[$i]['precioventa'];
$TotalMoneda += (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ','));
$TotalArticulos+=$reg[$i]['cantingrediente'];
?>
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo '&nbsp;'.$reg[$i]['codingrediente']; ?></td>
      <td><?php echo $reg[$i]['nomingrediente']; ?></td>
      <td><?php echo $reg[$i]['nommedida']; ?></td>
      <?php if ($documento == "EXCEL") { ?>
      <td><?php echo $reg[$i]['stockminimo'] == '0.00' ? "*********" : number_format($reg[$i]['stockminimo'], 2, '.', ','); ?></td>
      <td><?php echo $reg[$i]['stockmaximo'] == '0.00' ? "*********" : number_format($reg[$i]['stockmaximo'], 2, '.', ','); ?></td>
      <?php } ?>
      <td><?php echo $reg[$i]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
      <td><?php echo number_format($reg[$i]['descingrediente'], 2, '.', ','); ?></td>
      <?php if ($documento == "EXCEL") { ?>
      <td><?php echo $reg[$i]['lote']; ?></td>
      <td><?php echo $reg[$i]['fechaexpiracion'] == '0000-00-00' ? "*********" : date("d-m-Y",strtotime($reg[$i]['fechaexpiracion'])); ?></td>
      <td><?php echo $reg[$i]['codproveedor'] == '0' ? "*********" : $reg[$i]['nomproveedor']; ?></td>
      <?php } ?>
      <td><?php echo $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo2.$moneda; ?></td>
      <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
    </tr>
    <?php } ?>
    <tr class="text-center">
    <?php echo $documento == "EXCEL" ? '<td colspan="11"></td>' : '<td colspan="6"></td>'; ?> 
    <td><strong><?php echo $simbolo.number_format($TotalCompra, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalVenta, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo2.number_format($TotalMoneda, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    </tr>
    <?php } ?>
</table>
<?php
break;

case 'INGREDIENTESCSV':

$tra = new Login();
$reg = $tra->ListarIngredientes();

$archivo = str_replace(" ", "_","LISTADO DE INGREDIENTES EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <?php 

if($reg==""){
echo "";      
} else {
  
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr class="text-center" class="even_row">
      <td><?php echo '&nbsp;'.$reg[$i]['codingrediente']; ?></td>
      <td><?php echo $reg[$i]['nomingrediente']; ?></td>
      <td><?php echo $reg[$i]['codmedida']; ?></td>
      <td><?php echo number_format($reg[$i]['preciocompra'], 2, '.', ''); ?></td>
      <td><?php echo number_format($reg[$i]['precioventa'], 2, '.', ''); ?></td>
      <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ''); ?></td>
      <td><?php echo $reg[$i]['stockminimo'] == '0.00' ? "0" : number_format($reg[$i]['stockminimo'], 2, '.', ''); ?></td>
      <td><?php echo $reg[$i]['stockmaximo'] == '0.00' ? "0" : number_format($reg[$i]['stockmaximo'], 2, '.', ''); ?></td>
      <td><?php echo $reg[$i]['ivaingrediente']; ?></td>
      <td><?php echo number_format($reg[$i]['descingrediente'], 2, '.', ''); ?></td>
      <td><?php echo $reg[$i]['lote']; ?></td>
      <td><?php echo $reg[$i]['fechaexpiracion']; ?></td>
      <td><?php echo $reg[$i]['codproveedor']; ?></td>
    </tr>
    <?php }  } ?>
</table>
<?php
break;

case 'INGREDIENTESXMONEDA':

$cambio = new Login();
$cambio = $cambio->BuscarTiposCambios();

$tra = new Login();
$reg = $tra->ListarIngredientes(); 

$archivo = str_replace(" ", "_","LISTADO DE INGREDIENTES EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal'])." Y MONEDA ".$cambio[0]['moneda'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE INGREDIENTE</th>
          <th>UNIDAD MEDIDA</th>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <th>EXISTENCIA</th>
          <th>PRECIO VENTA</th>
          <th>PRECIO <?php echo $cambio[0]['siglas']; ?></th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalPrecioVenta=0;
$TotalPrecioVentaDescuento=0;
$TotalMoneda=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo  = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
$simbolo2 = "<strong>".$cambio[0]['simbolo']."</strong>";

$Descuento       = $reg[$i]['descingrediente']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal     = $reg[$i]['precioventa']-$PrecioDescuento;

$TotalArticulos += $reg[$i]['cantingrediente'];
$TotalPrecioVenta     += $reg[$i]['precioventa'];
$TotalPrecioVentaDescuento  += $PrecioFinal;
$TotalMoneda    += ($cambio == 0 ? "0" : $PrecioFinal/$cambio[0]['montocambio']);
?>
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo '&nbsp;'.$reg[$i]['codingrediente']; ?></td>
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
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($TotalPrecioVenta, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo2.number_format($TotalMoneda, 2, '.', ','); ?></strong></td>
    </tr>
    <?php } ?>
</table>
<?php
break;

case 'KARDEXINGREDIENTES':

$kardex = new Login();
$kardex = $kardex->BuscarKardexIngrediente(); 

$detalle = new Login();
$detalle = $detalle->DetalleKardexIngrediente();

$archivo = str_replace(" ", "_","KARDEX DEL INGREDIENTE (".portales($detalle[0]['nomingrediente'])." Y SUCURSAL: ".$detalle[0]['cuitsucursal'].": ".$detalle[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>MOVIMIENTO</th>
        <th>ENTRADAS</th>
        <th>SALIDAS</th>
        <th>DEVOLUCIÓN</th>
        <th>EXISTENCIA</th>
        <?php if ($documento == "EXCEL") { ?>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <th>PRECIO</th>
        <?php } ?>
        <th>DOCUMENTO</th>
        <th>FECHA KARDEX</th>
      </tr>
<?php 
if($kardex==""){
echo "";      
} else {

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
      <tr class="even_row">
        <td><?php echo $a++; ?></td>
        <td><?php echo $kardex[$i]['movimiento']; ?></td>
        <td><?php echo number_format($kardex[$i]['entradas'], 2, '.', ','); ?></td>
        <td><?php echo number_format($kardex[$i]['salidas'], 2, '.', ','); ?></td>
        <td><?php echo number_format($kardex[$i]['devolucion'], 2, '.', ','); ?></td>
        <td><?php echo number_format($kardex[$i]['stockactual'], 2, '.', ','); ?></td>
        <?php if ($documento == "EXCEL") { ?>
        <td><?php echo $kardex[$i]['ivaingrediente']; ?></td>
        <td><?php echo number_format($kardex[$i]['descingrediente'], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($kardex[$i]["precio"], 2, '.', ','); ?></td>
        <?php } ?>
        <td><?php echo $kardex[$i]['documento']." ".$num = ($kardex[$i]['documento'] == 'VENTA' || $kardex[$i]['documento'] == 'DEVOLUCION' ? $kardex[$i]['codproceso'] : ""); ?></td>
        <td><?php echo date("d-m-Y",strtotime($kardex[$i]['fechakardex'])); ?></td>
      </tr>
      <?php } } ?>
</table>
<strong>DETALLE DE INGREDIENTE<br>
<strong>CÓDIGO: <?php echo $detalle[0]['codingrediente']; ?><br>
<strong>DESCRIPCIÓN: <?php echo $detalle[0]['nomingrediente']; ?><br>
<strong>MEDIDA: <?php echo $detalle[0]['nommedida']; ?><br>
<strong>TOTAL ENTRADAS: <?php echo number_format($TotalEntradas, 2, '.', ','); ?><br>
<strong>TOTAL SALIDAS: <?php echo number_format($TotalSalidas, 2, '.', ','); ?><br>
<strong>TOTAL DEVOLUCIÓN: <?php echo number_format($TotalDevolucion, 2, '.', ','); ?><br>
<strong>EXISTENCIA: <?php echo number_format($detalle[0]['cantingrediente'], 2, '.', ','); ?><br>
<strong>PRECIO COMPRA: <?php echo $simbolo." ".number_format($detalle[0]['preciocompra'], 2, '.', ','); ?><br>
<strong>PPRECIO VENTA: <?php echo $simbolo." ".number_format($detalle[0]['precioventa'], 2, '.', ','); ?>
<?php
break;

case 'KARDEXVALORIZADOINGREDIENTES':

$tra = new Login();
$reg = $tra->ListarIngredientes(); 

$archivo = str_replace(" ", "_","KARDEX VALORIZADO DE INGREDIENTES DE SUCURSAL: (".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE INGREDIENTE</th>
          <th>MEDIDA</th>
          <th>PRECIO COMPRA</th>
          <th>PRECIO VENTA</th>
          <th>DESC %</th>
          <th><?php echo $impuesto; ?></th>
          <th>EXISTENCIA</th>
          <th>TOTAL VENTA</th>
          <th><?php echo $impuesto; ?> VENTA</th>
          <th>TOTAL COMPRA</th>
          <th><?php echo $impuesto; ?> COMPRA</th>
          <th>GANANCIAS</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {

$a=1;
$PrecioCompraTotal=0;
$PrecioVentaTotal=0;
$ExisteTotal=0;
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

$Descuento = $reg[$i]['descingrediente']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

//VALOR DE IMPUESTO
$ValorImpuesto = 1 + ($valor/100);

//CALCULO SUBTOTAL IMPUESTOS PRECIO COMPRA
$DiscriminadoC         = $reg[$i]['preciocompra']/$ValorImpuesto;
$SubtotalDiscriminadoC = $reg[$i]['preciocompra'] - $DiscriminadoC;
$BaseDiscriminadoC     = $SubtotalDiscriminadoC * $reg[$i]['cantingrediente'];
$SubtotalimpuestosC    = ($reg[$i]['ivaingrediente'] == 'SI' ? number_format($BaseDiscriminadoC, 2, '.', '') : "0.00");

//CALCULO SUBTOTAL IMPUESTOS PRECIO VENTA
$DiscriminadoV         = $PrecioFinal/$ValorImpuesto;
$SubtotalDiscriminadoV = $PrecioFinal - $DiscriminadoV;
$BaseDiscriminadoV     = $SubtotalDiscriminadoV * $reg[$i]['cantingrediente'];
$SubtotalimpuestosV    = ($reg[$i]['ivaingrediente'] == 'SI' ? number_format($BaseDiscriminadoV, 2, '.', '') : "0.00");

$SumCompra = ($reg[$i]['preciocompra']*$reg[$i]['cantingrediente'])-$SubtotalimpuestosC;
$SumVenta  = ($PrecioFinal*$reg[$i]['cantingrediente'])-$SubtotalimpuestosV; 

$CompraTotal          += $SumCompra;
$ImpuestosCompraTotal += $SubtotalimpuestosC;
$VentaTotal           += $SumVenta;
$ImpuestosVentaTotal  += $SubtotalimpuestosV;
$TotalGanancia        += $SumVenta-$SumCompra;
?>
      <tr class="even_row">
        <td><?php echo $a++; ?></td>
        <td><?php echo '&nbsp;'.$reg[$i]['codingrediente']; ?></td>
        <td><?php echo $reg[$i]['nomingrediente']; ?></td>
        <td><?php echo $reg[$i]['nommedida']; ?></td>
        <td><?php echo $simbolo.number_format($reg[$i]["preciocompra"], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
        <td><?php echo number_format($reg[$i]['descingrediente'], 2, '.', ','); ?>%</td>
        <td><?php echo $reg[$i]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
        <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SubtotalimpuestosV, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SubtotalimpuestosC, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
      </tr>
      <?php } ?>
      <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($ImpuestosVentaTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($ImpuestosCompraTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></strong></td>
      </tr>
      <?php } ?>
</table>
<?php
break;

case 'KARDEXINGREDIENTESVALORIZADOXFECHAS':

$tra = new Login();
$reg = $tra->BuscarKardexIngredientesValorizadoxFechas(); 

$archivo = str_replace(" ", "_","KARDEX VALORIZADO DE EXTRAS POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE INGREDIENTE</th>
          <th>MEDIDA</th>
          <th>PRECIO COMPRA</th>
          <th>PRECIO VENTA</th>
          <th>DESC %</th>
          <th><?php echo $impuesto; ?></th>
          <th>EXISTENCIA</th>
          <th>VENDIDO</th>
          <th>TOTAL VENTA</th>
          <th><?php echo $impuesto; ?> VENTA</th>
          <th>TOTAL COMPRA</th>
          <th><?php echo $impuesto; ?> COMPRA</th>
          <th>GANANCIAS</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
      <tr class="even_row">
        <td><?php echo $a++; ?></td>
        <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
        <td><?php echo $reg[$i]['producto']; ?></td>
        <td><?php echo $reg[$i]['codcategoria'] == '' ? "*****" : $reg[$i]['nommedida']; ?></td>
        <td><?php echo $simbolo.number_format($reg[$i]["preciocompra"], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
        <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
        <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
        <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
        <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SubtotalimpuestosV, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SubtotalimpuestosC, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
        </tr>
        <?php } } ?>
      <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($ImpuestosVentaTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($ImpuestosCompraTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></strong></td>
      </tr>
</table>
<?php
break;



case 'INGREDIENTESVENDIDOS':

$tra = new Login();
$reg = $tra->BuscarIngredientesVendidos(); 

$archivo = str_replace(" ", "_","INGREDIENTES VENDIDOS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>CÓDIGO</th>
           <th>DESCRIPCIÓN DE INGREDIENTE</th>
           <th>MEDIDA</th>
           <th>PRECIO VENTA</th>
           <th>EXISTENCIA</th>
           <th>VENDIDO</th>
           <th><?php echo $impuesto; ?></th>
           <th>DESC %</th>
           <th>MONTO TOTAL</th>
         </tr>
      <?php 

if($reg==""){
echo "";      
} else {

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
$CalculoDescuento = $reg[$i]['totaldescuentov'];
$PrecioFinal      = $reg[$i]['precioventa']-$PrecioDescuento;

$ivg              = $reg[$i]['ivaproducto']/100;
$CalculoImpuesto  = number_format($reg[$i]['subtotalimpuestos'], 2, '.', '');

$TotalDescuento += number_format($reg[$i]['totaldescuentov'], 2, '.', ''); 
$TotalImpuesto  += $CalculoImpuesto; 
$TotalGeneral   += $PrecioFinal*$reg[$i]['cantidad'];
?>
      <tr class="text-center" class="even_row">
        <td><?php echo $a++; ?></td>
        <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
        <td><?php echo $reg[$i]['producto']; ?></td>
        <td><?php echo $reg[$i]['nommedida']; ?></td>
        <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
        <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
        <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
        <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
        <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
      </tr>
      <?php } } ?>
      <tr class="text-center">
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($TotalGeneral, 2, '.', ','); ?></strong></td>
      </tr>
</table>
<?php
break;
################################# MODULO DE INGREDIENTES ##################################

























################################ MODULO DE PRODUCTOS ################################
case 'PRODUCTOS':

$tra = new Login();
$reg = $tra->ListarProductos();

$archivo = str_replace(" ", "_","LISTADO DE PRODUCTOS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE PRODUCTO</th>
          <th>CATEGORIA</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>STOCK MINIMO</th>
          <th>STOCK MÁXIMO</th>
          <?php } ?>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>CÓDIGO DE BARRA</th>
          <th>LOTE</th>
          <th>FECHA DE ELABORACIÓN</th>
          <th>FECHA DE EXPIRACIÓN</th>
          <th>PROVEEDOR</th>
          <?php } ?>
          <th>PRECIO COMPRA</th>
          <th>PRECIO VENTA</th>
          <th>P.V EXT</th>
          <th>EXISTENCIA</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalCompra=0;
$TotalVenta=0;
$TotalMoneda=0;
$TotalArticulos=0;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
$simbolo2 = ($reg[$i]['simbolo2'] == "" ? "" : "<strong>".$reg[$i]['simbolo2']."</strong>");
$moneda = (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ',')); 

$TotalCompra+=$reg[$i]['preciocompra'];
$TotalVenta+=$reg[$i]['precioventa'];
$TotalMoneda += (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ','));
$TotalArticulos+=$reg[$i]['existencia'];
?>
        <tr class="text-center" class="even_row">
          <td><?php echo $a++; ?></td>
          <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
          <td><?php echo $reg[$i]['producto']; ?></td>
          <td><?php echo $reg[$i]['nomcategoria']; ?></td>
          <?php if ($documento == "EXCEL") { ?>
          <td><?php echo $reg[$i]['stockminimo'] == '0.00' ? "*********" : $reg[$i]['stockminimo']; ?></td>
          <td><?php echo $reg[$i]['stockmaximo'] == '0.00' ? "*********" : $reg[$i]['stockmaximo']; ?></td>
          <?php } ?>
          <td><?php echo $reg[$i]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
          <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?></td>
          <?php if ($documento == "EXCEL") { ?>
          <td><?php echo $reg[$i]['codigobarra'] == '' ? "*********" : $reg[$i]['codigobarra']; ?></td>
          <td><?php echo $reg[$i]['lote'] == '' || $reg[$i]['lote'] == '0' ? "*********" : $reg[$i]['lote']; ?></td>
          <td><?php echo $reg[$i]['fechaelaboracion'] == '0000-00-00' ? "*********" : date("d-m-Y",strtotime($reg[$i]['fechaelaboracion'])); ?></td>
          <td><?php echo $reg[$i]['fechaexpiracion'] == '0000-00-00' ? "*********" : date("d-m-Y",strtotime($reg[$i]['fechaexpiracion'])); ?></td>
          <td><?php echo $reg[$i]['codproveedor'] == '0' ? "*********" : $reg[$i]['nomproveedor']; ?></td>
          <?php } ?>
          <td><?php echo $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo2.$moneda; ?></td>
          <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
        </tr>
        <?php } ?>
        <tr class="text-center">
        <?php echo $documento == "EXCEL" ? '<td colspan="13"></td>' : '<td colspan="6"></td>'; ?> 
        <td><strong><?php echo $simbolo.number_format($TotalCompra, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($TotalVenta, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo2.number_format($TotalMoneda, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
        </tr>
        <?php } ?>
</table>
<?php
break;

case 'PRODUCTOSCSV':

$tra = new Login();
$reg = $tra->ListarProductosCsv();

$archivo = str_replace(" ", "_","LISTADO DE PRODUCTOS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <?php 

if($reg==""){
echo "";      
} else {
  
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr class="text-center" class="even_row">
      <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
      <td><?php echo $reg[$i]['producto']; ?></td>
      <td><?php echo $reg[$i]['codcategoria']; ?></td>
      <td><?php echo number_format($reg[$i]['preciocompra'], 2, '.', ''); ?></td>
      <td><?php echo number_format($reg[$i]['precioventa'], 2, '.', ''); ?></td>
      <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ''); ?></td>
      <td><?php echo $reg[$i]['stockminimo'] == '0.00' ? "0" : number_format($reg[$i]['stockminimo'], 2, '.', ''); ?></td>
      <td><?php echo $reg[$i]['stockmaximo'] == '0.00' ? "0" : number_format($reg[$i]['stockmaximo'], 2, '.', ''); ?></td>
      <td><?php echo $reg[$i]['ivaproducto']; ?></td>
      <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ''); ?></td>
      <td><?php echo $reg[$i]['codigobarra']; ?></td>
      <td><?php echo $reg[$i]['lote']; ?></td>
      <td><?php echo $reg[$i]['fechaelaboracion'] == '0000-00-00' ? "0000-00-00" : date("d-m-Y",strtotime($reg[$i]['fechaelaboracion'])); ?></td>
      <td><?php echo $reg[$i]['fechaexpiracion'] == '0000-00-00' ? "0000-00-00" : date("d-m-Y",strtotime($reg[$i]['fechaexpiracion'])); ?></td>
      <td><?php echo $reg[$i]['codproveedor']; ?></td>
      <td><?php echo $reg[$i]['preparado']; ?></td>
      <td><?php echo $reg[$i]['favorito']; ?></td>
      <td><?php echo $reg[$i]['controlstockp']; ?></td>
    </tr>
    <?php } } ?>
</table>
<?php
break;

case 'PRODUCTOSXMONEDA':

$cambio = new Login();
$cambio = $cambio->BuscarTiposCambios();

$tra = new Login();
$reg = $tra->ListarProductos(); 

$archivo = str_replace(" ", "_","LISTADO DE PRODUCTOS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal'])." Y MONEDA ".$cambio[0]['moneda'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE PRODUCTO</th>
          <th>CATEGORIA</th>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <th>EXISTENCIA</th>
          <th>PRECIO VENTA</th>
          <th>PRECIO <?php echo $cambio[0]['siglas']; ?></th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalPrecioVenta=0;
$TotalPrecioVentaDescuento=0;
$TotalMoneda=0;

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
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
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
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($TotalPrecioVenta, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo2.number_format($TotalMoneda, 2, '.', ','); ?></strong></td>
    </tr>
    <?php } ?>
</table>
<?php
break;

case 'KARDEXPRODUCTOS':

$kardex = new Login();
$kardex = $kardex->BuscarKardexProducto();

$detalle = new Login();
$detalle = $detalle->DetalleKardexProducto(); 

$archivo = str_replace(" ", "_","KARDEX DEL PRODUCTO (".portales($detalle[0]['producto'])." Y SUCURSAL: ".$detalle[0]['cuitsucursal'].": ".$detalle[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>MOVIMIENTO</th>
          <th>ENTRADAS</th>
          <th>SALIDAS</th>
          <th>DEVOLUCIÓN</th>
          <th>EXISTENCIA</th>
          <?php if ($documento == "EXCEL") { ?>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <th>PRECIO</th>
          <?php } ?>
          <th>DOCUMENTO</th>
          <th>FECHA KARDEX</th>
        </tr>
<?php 
if($kardex==""){
echo "";      
} else {

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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo $kardex[$i]['movimiento']; ?></td>
    <td><?php echo number_format($kardex[$i]['entradas'], 2, '.', ','); ?></td>
    <td><?php echo number_format($kardex[$i]['salidas'], 2, '.', ','); ?></td>
    <td><?php echo number_format($kardex[$i]['devolucion'], 2, '.', ','); ?></td>
    <td><?php echo number_format($kardex[$i]['stockactual'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $kardex[$i]['ivaproducto']; ?></td>
    <td><?php echo number_format($kardex[$i]['descproducto'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($kardex[$i]["precio"], 2, '.', ','); ?></td>
    <?php } ?>
    <td><?php echo $kardex[$i]['documento']." ".$num = ($kardex[$i]['documento'] == 'VENTA' || $kardex[$i]['documento'] == 'DEVOLUCION' ? $kardex[$i]['codproceso'] : ""); ?></td>
    <td><?php echo date("d-m-Y",strtotime($kardex[$i]['fechakardex'])); ?></td>
  </tr>
  <?php } } ?>
</table>
<strong>DETALLE DE PRODUCTO<br>
<strong>CÓDIGO: <?php echo $detalle[0]['codproducto']; ?><br>
<strong>DESCRIPCIÓN: <?php echo $detalle[0]['producto']; ?><br>
<strong>CATEGORIA: <?php echo $detalle[0]['nomcategoria']; ?><br>
<strong>TOTAL ENTRADAS: <?php echo number_format($TotalEntradas, 2, '.', ','); ?><br>
<strong>TOTAL SALIDAS: <?php echo number_format($TotalSalidas, 2, '.', ','); ?><br>
<strong>TOTAL DEVOLUCIÓN: <?php echo number_format($TotalDevolucion, 2, '.', ','); ?><br>
<strong>EXISTENCIA: <?php echo number_format($detalle[0]['existencia'], 2, '.', ','); ?><br>
<strong>PRECIO COMPRA: <?php echo $simbolo." ".number_format($detalle[0]['preciocompra'], 2, '.', ','); ?><br>
<strong>PPRECIO VENTA: <?php echo $simbolo." ".number_format($detalle[0]['precioventa'], 2, '.', ','); ?>
<?php
break;

case 'KARDEXVALORIZADOPRODUCTOS':

$tra = new Login();
$reg = $tra->ListarProductos();  

$archivo = str_replace(" ", "_","KARDEX VALORIZADO DE PRODUCTOS DE SUCURSAL: (".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE PRODUCTO</th>
          <th>CATEGORIA</th>
          <th>PRECIO COMPRA</th>
          <th>PRECIO VENTA</th>
          <th>DESC %</th>
          <th><?php echo $impuesto; ?></th>
          <th>EXISTENCIA</th>
          <th>TOTAL VENTA</th>
          <th><?php echo $impuesto; ?> VENTA</th>
          <th>TOTAL COMPRA</th>
          <th><?php echo $impuesto; ?> COMPRA</th>
          <th>GANANCIAS</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {

$PrecioCompraTotal=0;
$PrecioVentaTotal=0;
$ExisteTotal=0;
$ImpuestosCompraTotal=0;
$ImpuestosVentaTotal=0;
$CompraTotal=0;
$VentaTotal=0;
$TotalGanancia=0;

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$PrecioCompraTotal+=$reg[$i]['preciocompra'];
$PrecioVentaTotal+=$reg[$i]['precioventa'];
$ExisteTotal+=$reg[$i]['existencia'];

$Descuento = $reg[$i]['descproducto']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

//VALOR DE IMPUESTO
$ValorImpuesto = 1 + ($valor/100);

//CALCULO SUBTOTAL IMPUESTOS PRECIO COMPRA
$DiscriminadoC         = $reg[$i]['preciocompra']/$ValorImpuesto;
$SubtotalDiscriminadoC = $reg[$i]['preciocompra'] - $DiscriminadoC;
$BaseDiscriminadoC     = $SubtotalDiscriminadoC * $reg[$i]['existencia'];
$SubtotalimpuestosC    = ($reg[$i]['ivaproducto'] == 'SI' ? number_format($BaseDiscriminadoC, 2, '.', '') : "0.00");

//CALCULO SUBTOTAL IMPUESTOS PRECIO VENTA
$DiscriminadoV         = $PrecioFinal/$ValorImpuesto;
$SubtotalDiscriminadoV = $PrecioFinal - $DiscriminadoV;
$BaseDiscriminadoV     = $SubtotalDiscriminadoV * $reg[$i]['existencia'];
$SubtotalimpuestosV    = ($reg[$i]['ivaproducto'] == 'SI' ? number_format($BaseDiscriminadoV, 2, '.', '') : "0.00");

$SumCompra = ($reg[$i]['preciocompra']*$reg[$i]['existencia'])-$SubtotalimpuestosC;
$SumVenta  = ($PrecioFinal*$reg[$i]['existencia'])-$SubtotalimpuestosV; 

$CompraTotal          += $SumCompra;
$ImpuestosCompraTotal += $SubtotalimpuestosC;
$VentaTotal           += $SumVenta;
$ImpuestosVentaTotal  += $SubtotalimpuestosV;
$TotalGanancia        += $SumVenta-$SumCompra; 
?>
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $reg[$i]['nomcategoria']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]["preciocompra"], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
    <td><?php echo $reg[$i]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SubtotalimpuestosV, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SubtotalimpuestosC, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
    </tr>
    <?php } ?>
  <tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($ImpuestosVentaTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($ImpuestosCompraTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'KARDEXPRODUCTOSVALORIZADOXFECHAS':

$tra = new Login();
$reg = $tra->BuscarKardexProductosValorizadoxFechas(); 

$archivo = str_replace(" ", "_","KARDEX VALORIZADO DE PRODUCTOS POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE PRODUCTO</th>
          <th>CATEGORIA</th>
          <th>PRECIO COMPRA</th>
          <th>PRECIO VENTA</th>
          <th>DESC %</th>
          <th><?php echo $impuesto; ?></th>
          <th>EXISTENCIA</th>
          <th>VENDIDO</th>
          <th>TOTAL VENTA</th>
          <th><?php echo $impuesto; ?> VENTA</th>
          <th>TOTAL COMPRA</th>
          <th><?php echo $impuesto; ?> COMPRA</th>
          <th>GANANCIAS</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $reg[$i]['codcategoria'] == '' ? "*****" : $reg[$i]['nomcategoria']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]["preciocompra"], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
    <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SubtotalimpuestosV, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SubtotalimpuestosC, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
    </tr>
    <?php } } ?>
  <tr>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
  <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($ImpuestosVentaTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($ImpuestosCompraTotal, 2, '.', ','); ?></strong></td>
  <td><strong><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;

case 'PRODUCTOSVENDIDOS':

$tra = new Login();
$reg = $tra->BuscarProductosVendidos(); 

$archivo = str_replace(" ", "_","PRODUCTOS VENDIDOS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
       <tr>
         <th>Nº</th>
         <th>CÓDIGO</th>
         <th>DESCRIPCIÓN DE PRODUCTO</th>
         <th>CATEGORIA</th>
         <th>PRECIO VENTA</th>
         <th>EXISTENCIA</th>
         <th>VENDIDO</th>
         <th><?php echo $impuesto; ?></th>
         <th>DESC %</th>
         <th>MONTO TOTAL</th>
       </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $reg[$i]['nomcategoria']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
  </tr>
  <?php } } ?>
  <tr class="text-center">
    <td colspan="4"></td>
    <td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalGeneral, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;
################################# MODULO DE PRODUCTOS ##################################
























################################ MODULO DE COMBOS ################################
case 'COMBOS':

$tra = new Login();
$reg = $tra->ListarCombos();

$archivo = str_replace(" ", "_","LISTADO DE PRODUCTOS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE COMBO</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>STOCK MINIMO</th>
          <th>STOCK MÁXIMO</th>
          <?php } ?>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>PRECIO COMPRA</th>
          <th>PRECIO VENTA</th>
          <th>P.V EXT</th>
          <th>EXISTENCIA</th>
          <th>DETALLES DE PRODUCTOS</th>
          <?php } ?>
         </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalCompra=0;
$TotalVenta=0;
$TotalMoneda=0;
$TotalArticulos=0;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
$simbolo2 = ($reg[$i]['simbolo2'] == "" ? "" : "<strong>".$reg[$i]['simbolo2']."</strong>");

$moneda = (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ',')); 

$TotalCompra+=$reg[$i]['preciocompra'];
$TotalVenta+=$reg[$i]['precioventa'];
$TotalMoneda += (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ','));
$TotalArticulos+=$reg[$i]['existencia'];
?>
        <tr align="center" class="even_row">
          <td><?php echo $a++; ?></td>
          <td><?php echo '&nbsp;'.$reg[$i]['codcombo']; ?></td>
          <td><?php echo $reg[$i]['nomcombo']; ?></td>
          <?php if ($documento == "EXCEL") { ?>
          <td><?php echo $reg[$i]['stockminimo'] == '0.00' ? "*********" : number_format($reg[$i]['stockminimo'], 2, '.', ','); ?></td>
          <td><?php echo $reg[$i]['stockmaximo'] == '0.00' ? "*********" : number_format($reg[$i]['stockmaximo'], 2, '.', ','); ?></td>
          <?php } ?>
          <td><?php echo $reg[$i]['ivacombo'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
          <td><?php echo number_format($reg[$i]['desccombo'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo2.$moneda; ?></td>
          <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
          <?php if ($documento == "EXCEL") { ?>
          <td style="text-align:left;font-weight:bold;font-size:10px;"><?php echo $reg[$i]['detalles_productos']; ?></td>      
          <?php } ?>
        </tr>
        <?php } ?>
        <tr align="center">
        <?php echo $documento == "EXCEL" ? '<td colspan="7"></td>' : '<td colspan="5"></td>'; ?>
        <td><strong><?php echo $simbolo.number_format($TotalCompra, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($TotalVenta, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo2.number_format($TotalMoneda, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
        <?php echo $documento == "EXCEL" ? '<td></td>' : ''; ?>
        </tr>
        <?php } ?>
</table>
<?php
break;

case 'COMBOSXMONEDA':

$cambio = new Login();
$cambio = $cambio->BuscarTiposCambios();

$tra = new Login();
$reg = $tra->ListarCombos(); 

$archivo = str_replace(" ", "_","LISTADO DE COMBOS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal'])." Y MONEDA ".$cambio[0]['moneda'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE COMBO</th>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <th>DETALLES DE PRODUCTOS</th>
          <th>EXISTENCIA</th>
          <th>PRECIO VENTA</th>
          <th>PRECIO <?php echo $cambio[0]['siglas']; ?></th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalPrecioVenta=0;
$TotalPrecioVentaDescuento=0;
$TotalMoneda=0;

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
    <tr class="text-center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo '&nbsp;'.$reg[$i]['codcombo']; ?></td>
      <td><?php echo $reg[$i]['nomcombo']; ?></td>
      <td><?php echo $reg[$i]['ivacombo'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
      <td><?php echo number_format($reg[$i]['desccombo'], 2, '.', ','); ?></td>
      <td style="text-align:left;font-weight:bold;font-size:10px;"><?php echo $reg[$i]['detalles_productos']; ?></td>
      <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo2.number_format($reg[$i]['precioventa']/$cambio[0]['montocambio'], 2, '.', ','); ?></td>
    </tr>
    <?php } ?>
    <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($TotalPrecioVenta, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo2.number_format($TotalMoneda, 2, '.', ','); ?></strong></td>
    </tr>
    <?php } ?>
</table>
<?php
break;

case 'KARDEXCOMBOS':

$kardex = new Login();
$kardex = $kardex->BuscarKardexCombo(); 

$detalle = new Login();
$detalle = $detalle->DetalleKardexCombo(); 

$archivo = str_replace(" ", "_","KARDEX DEL COMBO (".portales($detalle[0]['nomcombo'])." Y SUCURSAL: ".$detalle[0]['cuitsucursal'].": ".$detalle[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>MOVIMIENTO</th>
          <th>ENTRADAS</th>
          <th>SALIDAS</th>
          <th>DEVOLUCIÓN</th>
          <th>EXISTENCIA</th>
          <?php if ($documento == "EXCEL") { ?>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <th>PRECIO</th>
          <?php } ?>
          <th>DOCUMENTO</th>
          <th>FECHA KARDEX</th>
        </tr>
      <?php 

if($kardex==""){
echo "";      
} else {

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
    <tr class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo $kardex[$i]['movimiento']; ?></td>
      <td><?php echo number_format($kardex[$i]['entradas'], 2, '.', ','); ?></td>
      <td><?php echo number_format($kardex[$i]['salidas'], 2, '.', ','); ?></td>
      <td><?php echo number_format($kardex[$i]['devolucion'], 2, '.', ','); ?></td>
      <td><?php echo number_format($kardex[$i]['stockactual'], 2, '.', ','); ?></td>
      <?php if ($documento == "EXCEL") { ?>
       <td><?php echo $kardex[$i]['ivacombo']; ?></td>
      <td><?php echo number_format($kardex[$i]['desccombo'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($kardex[$i]["precio"], 2, '.', ','); ?></td>
      <?php } ?>
      <td><?php echo $kardex[$i]['documento']." ".$num = ($kardex[$i]['documento'] == 'VENTA' || $kardex[$i]['documento'] == 'DEVOLUCION' ? $kardex[$i]['codproceso'] : ""); ?></td>
      <td><?php echo date("d-m-Y",strtotime($kardex[$i]['fechakardex'])); ?></td>
    </tr>
    <?php } } ?>
</table>
<strong>DETALLE DE COMBO</strong><br>
<strong>CÓDIGO:</strong> <?php echo $detalle[0]['codcombo']; ?><br>
<strong>DESCRIPCIÓN:</strong> <?php echo $detalle[0]['nomcombo']; ?><br>
<strong>TOTAL ENTRADAS:</strong> <?php echo number_format($TotalEntradas, 2, '.', ','); ?><br>
<strong>TOTAL SALIDAS:</strong> <?php echo number_format($TotalSalidas, 2, '.', ','); ?><br>
<strong>TOTAL DEVOLUCIÓN:</strong> <?php echo number_format($TotalDevolucion, 2, '.', ','); ?><br>
<strong>EXISTENCIA:</strong> <?php echo number_format($detalle[0]['existencia'], 2, '.', ','); ?><br>
<strong>PRECIO COMPRA:</strong> <?php echo $simbolo." ".number_format($detalle[0]['preciocompra'], 2, '.', ','); ?><br>
<strong>PPRECIO VENTA:</strong> <?php echo $simbolo." ".number_format($detalle[0]['precioventa'], 2, '.', ','); ?>
<?php
break;

case 'KARDEXVALORIZADOCOMBOS':

$tra = new Login();
$reg = $tra->ListarCombos(); 

$archivo = str_replace(" ", "_","KARDEX VALORIZADO DE COMBOS DE SUCURSAL: (".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE COMBO</th>
          <th>PRECIO COMPRA</th>
          <th>PRECIO VENTA</th>
          <th>DESC %</th>
          <th><?php echo $impuesto; ?></th>
          <th>EXISTENCIA</th>
          <th>TOTAL VENTA</th>
          <th><?php echo $impuesto; ?> VENTA</th>
          <th>TOTAL COMPRA</th>
          <th><?php echo $impuesto; ?> COMPRA</th>
          <th>GANANCIAS</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {

$a=1;
$PrecioCompraTotal=0;
$PrecioVentaTotal=0;
$ExisteTotal=0;
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

$Descuento = $reg[$i]['desccombo']/100;
$PrecioDescuento = $reg[$i]['precioventa']*$Descuento;
$PrecioFinal = $reg[$i]['precioventa']-$PrecioDescuento;

//VALOR DE IMPUESTO
$ValorImpuesto = 1 + ($valor/100);

//CALCULO SUBTOTAL IMPUESTOS PRECIO COMPRA
$DiscriminadoC         = $reg[$i]['preciocompra']/$ValorImpuesto;
$SubtotalDiscriminadoC = $reg[$i]['preciocompra'] - $DiscriminadoC;
$BaseDiscriminadoC     = $SubtotalDiscriminadoC * $reg[$i]['existencia'];
$SubtotalimpuestosC    = ($reg[$i]['ivacombo'] == 'SI' ? number_format($BaseDiscriminadoC, 2, '.', '') : "0.00");

//CALCULO SUBTOTAL IMPUESTOS PRECIO VENTA
$DiscriminadoV         = $PrecioFinal/$ValorImpuesto;
$SubtotalDiscriminadoV = $PrecioFinal - $DiscriminadoV;
$BaseDiscriminadoV     = $SubtotalDiscriminadoV * $reg[$i]['existencia'];
$SubtotalimpuestosV    = ($reg[$i]['ivacombo'] == 'SI' ? number_format($BaseDiscriminadoV, 2, '.', '') : "0.00");

$SumCompra = ($reg[$i]['preciocompra']*$reg[$i]['existencia'])-$SubtotalimpuestosC;
$SumVenta  = ($PrecioFinal*$reg[$i]['existencia'])-$SubtotalimpuestosV; 

$CompraTotal          += $SumCompra;
$ImpuestosCompraTotal += $SubtotalimpuestosC;
$VentaTotal           += $SumVenta;
$ImpuestosVentaTotal  += $SubtotalimpuestosV;
$TotalGanancia        += $SumVenta-$SumCompra;
?>
         <tr class="even_row">
          <td><?php echo $a++; ?></td>
          <td><?php echo '&nbsp;'.$reg[$i]['codcombo']; ?></td>
          <td><?php echo $reg[$i]['nomcombo']; ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]["preciocompra"], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
          <td><?php echo number_format($reg[$i]['desccombo'], 2, '.', ','); ?>%</td>
          <td><?php echo $reg[$i]['ivacombo'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
          <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($SubtotalimpuestosV, 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($SubtotalimpuestosC, 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
         </tr>
        <?php } ?>
        <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($ImpuestosVentaTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($ImpuestosCompraTotal, 2, '.', ','); ?></strong></td>
        <td><strong><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></strong></td>
        </tr>
        <?php } ?>
</table>
<?php
break;

case 'KARDEXCOMBOSVALORIZADOXFECHAS':

$tra = new Login();
$reg = $tra->BuscarKardexCombosValorizadoxFechas(); 

$archivo = str_replace(" ", "_","KARDEX VALORIZADO DE COMBOS POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE COMBO</th>
          <th>PRECIO COMPRA</th>
          <th>PRECIO VENTA</th>
          <th>DESC %</th>
          <th><?php echo $impuesto; ?></th>
          <th>EXISTENCIA</th>
          <th>VENDIDO</th>
          <th>TOTAL VENTA</th>
          <th><?php echo $impuesto; ?> VENTA</th>
          <th>TOTAL COMPRA</th>
          <th><?php echo $impuesto; ?> COMPRA</th>
          <th>GANANCIAS</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
      <tr class="even_row">
        <td><?php echo $a++; ?></td>
        <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
        <td><?php echo $reg[$i]['producto']; ?></td>
        <td><?php echo $simbolo.number_format($reg[$i]["preciocompra"], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
        <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
        <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
        <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
        <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SubtotalimpuestosV, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SubtotalimpuestosC, 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
      </tr>
      <?php } } ?>
      <tr>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($ImpuestosVentaTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($ImpuestosCompraTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></strong></td>
      </tr>
</table>
<?php
break;

case 'COMBOSVENDIDOS':

$tra = new Login();
$reg = $tra->BuscarCombosVendidos(); 

$archivo = str_replace(" ", "_","COMBOS VENDIDOS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");


header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN DE COMBO</th>
          <th>PRECIO VENTA</th>
          <th>EXISTENCIA</th>
          <th>VENDIDO</th>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <th>MONTO TOTAL</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
    <tr align="center" class="even_row">
      <td><?php echo $a++; ?></td>
      <td><?php echo '&nbsp;'.$reg[$i]['codproducto']; ?></td>
      <td><?php echo $reg[$i]['producto']; ?></td>
      <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
      <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
      <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
      <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
      <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
    </tr>
    <?php } } ?>
    <tr align="center">
      <td colspan="3"></td>
      <td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
      <td><strong><?php echo $simbolo.number_format($TotalGeneral, 2, '.', ','); ?></strong></td>
    </tr>
</table>
<?php
break;

################################# MODULO DE COMBOS ##################################

























################################### MODULO DE COMPRAS ###################################
case 'COMPRAS':

$tra = new Login();
$reg = $tra->ListarCompras(); 

$archivo = str_replace(" ", "_","LISTADO DE COMPRAS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>Nº DE COMPRA</th>
          <th>DESCRIPCIÓN DE PROVEEDOR</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>ESTADO</th>
          <th>DIAS VENC.</th>
          <th>FECHA VENCE</th>
          <th>FECHA PAGADO</th>
          <?php } ?>
          <th>FECHA EMISIÓN</th>
          <th>Nº DE ARTICULOS</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>SUBTOTAL</th>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <?php } ?>
          <th>IMPORTE TOTAL</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['cuitproveedor'].": ".$reg[$i]['nomproveedor']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]["statuscompra"]; ?></td>

    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>

    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*********" : date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
      
    <td><?php echo $reg[$i]['statuscompra'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statuscompra']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>

    <?php } ?>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaemision'])); ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="8"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'CUENTASXPAGAR':

$tra = new Login();
$reg = $tra->ListarCuentasxPagar(); 

$archivo = str_replace(" ", "_","LISTADO DE COMPRAS POR PAGAR EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE COMPRA</th>
        <th>DESCRIPCIÓN DE PROVEEDOR</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>ESTADO</th>
        <th>DIAS VENC.</th>
        <th>FECHA VENCE</th>
        <th>FECHA PAGADO</th>
        <?php } ?>
        <th>FECHA EMISIÓN</th>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['cuitproveedor'].": ".$reg[$i]['nomproveedor']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]["statuscompra"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*********" : date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statuscompra'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statuscompra']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaemision'])); ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="8"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'COMPRASXFECHAS':

$tra = new Login();
$reg = $tra->BuscarComprasxFechas(); 

$archivo = str_replace(" ", "_","LISTADO DE COMPRAS POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE COMPRA</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <th>DIAS VENC.</th>
      <th>FECHA VENCE</th>
      <th>FECHA PAGADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <th>Nº DE ARTICULOS</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>SUBTOTAL</th>
      <th><?php echo $impuesto; ?></th>
      <th>DESC %</th>
      <?php } ?>
      <th>IMPORTE TOTAL</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codfactura']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]["statuscompra"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*********" : date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statuscompra'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statuscompra']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaemision'])); ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="7"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'COMPRASXPROVEEDOR':

$tra = new Login();
$reg = $tra->BuscarComprasxProveedor();

$archivo = str_replace(" ", "_","LISTADO DE COMPRAS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal'])." Y PROVEEDOR ".$reg[0]['cuitproveedor'].": ".$reg[0]['nomproveedor'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE COMPRA</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>ESTADO</th>
        <th>DIAS VENC.</th>
        <th>FECHA VENCE</th>
        <th>FECHA PAGADO</th>
        <?php } ?>
        <th>FECHA EMISIÓN</th>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codfactura']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]["statuscompra"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*********" : date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statuscompra'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statuscompra']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaemision'])); ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="7"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;
################################## MODULO DE COMPRAS ###################################















################################# MODULO DE TRASPASOS #################################
case 'TRASPASOS':

$tra = new Login();
$reg = $tra->ListarTraspasos(); 

$archivo = str_replace(" ", "_","LISTADO DE TRASPASOS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE TRASPASO</th>
        <th>SUCURSAL REMITENTE</th>
        <th>SUCURSAL DESTINATARIO</th>
        <th>OBSERVACIONES</th>
        <th>FECHA DE EMISIÓN</th>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr align="center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['cuitsucursal'].": <strong>".$reg[$i]['nomsucursal']."</strong>"; ?></td>
    <td><?php echo $reg[$i]['cuitsucursal2'].": <strong>".$reg[$i]['nomsucursal2']."</strong>"; ?></td>
    <td><?php echo $reg[$i]['observaciones'] == "" ? "**********" : $reg[$i]['observaciones']; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechatraspaso'])); ?></td>
    <td><?php echo number_format($reg[$i]['sumarticulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr align="center">
    <td colspan="6"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'TRASPASOSXFECHAS':

$tra = new Login();
$reg = $tra->BuscarTraspasosxFechas(); 

$archivo = str_replace(" ", "_","LISTADO DE TRASPASOS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL N°: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" align="center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>Nº DE TRASPASO</th>
          <th>SUCURSAL REMITENTE</th>
          <th>SUCURSAL DESTINATARIO</th>
          <th>OBSERVACIONES</th>
          <th>FECHA DE EMISIÓN</th>
          <th>Nº DE ARTICULOS</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>SUBTOTAL</th>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <?php } ?>
          <th>IMPORTE TOTAL</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr align="center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['cuitsucursal'].": <strong>".$reg[$i]['nomsucursal']."</strong>"; ?></td>
    <td><?php echo $reg[$i]['cuitsucursal2'].": <strong>".$reg[$i]['nomsucursal2']."</strong>"; ?></td>
    <td><?php echo $reg[$i]['observaciones'] == "" ? "**********" : $reg[$i]['observaciones']; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechatraspaso'])); ?></td>
    <td><?php echo number_format($reg[$i]['sumarticulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr align="center">
    <td colspan="6"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'DETALLESTRASPASOSXFECHAS':

$tra = new Login();
$reg = $tra->BuscarDetallesTraspasosxFechas();

$archivo = str_replace(" ", "_","DETALLES TRASPASOS POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>TIPO</th>
          <th>CÓDIGO</th>
          <th>DESCRIPCIÓN</th>
          <th>CATEGORIA</th>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <th>PRECIO VENTA</th>
          <th>EXISTENCIA</th>
          <th>TRASPASADO</th>
          <th>MONTO TOTAL</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
   <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td style="font-size:12px;color:#ff5050;font-weight:bold;"><?php echo $Tipo; ?></td>
    <td><?php echo $reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $Categoria ?></td>
    <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($reg[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
    <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
   </tr>
  <?php } } ?>
  <tr>
  <td colspan="7"></td>
    <td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;

case 'DETALLESTRASPASOSXSUCURSAL':

$tra = new Login();
$reg = $tra->BuscarDetallesTraspasosxSucursal(); 

$archivo = str_replace(" ", "_","DETALLES TRASPASOS POR SUCURSAL ENVIA(".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal']." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL RECIBE: ".$reg[0]['cuitsucursal2'].": ".$reg[0]['nomsucursal2'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>TIPO</th>
        <th>CÓDIGO</th>
        <th>DESCRIPCIÓN</th>
        <th>CATEGORIA</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <th>PRECIO VENTA</th>
        <th>EXISTENCIA</th>
        <th>TRASPASADO</th>
        <th>MONTO TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td style="font-size:12px;color:#ff5050;font-weight:bold;"><?php echo $Tipo; ?></td>
    <td><?php echo $reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $Categoria ?></td>
    <td><?php echo $reg[$i]['ivaproducto'] != '0.00' ? number_format($reg[$i]['ivaproducto'], 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</td>
    <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
   </tr>
  <?php } } ?>
  <tr>
    <td colspan="7"></td>
    <td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;
################################## MODULO DE TRASPASOS ###################################

















############################### MODULO DE COTIZACIONES ###############################
case 'COTIZACIONES':

$tra = new Login();
$reg = $tra->ListarCotizaciones(); 

$archivo = str_replace(" ", "_","LISTADO DE COTIZACIONES EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>Nº DE COTIZACIÓN</th>
           <th>DESCRIPCIÓN DE CLIENTE</th>
           <th>OBSERVACIONES</th>
           <th>FECHA DE EMISIÓN</th>
           <th>Nº DE ARTICULOS</th>
           <?php if ($documento == "EXCEL") { ?>
           <th>SUBTOTAL</th>
           <th><?php echo $impuesto; ?></th>
           <th>DESC %</th>
           <?php } ?>
           <th>IMPORTE TOTAL</th>
         </tr>
      <?php 

if($reg==""){
echo "";      
} else {
  
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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['observaciones'] == '' ? "***********" : $reg[$i]['observaciones']; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechacotizacion'])); ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
      </tr>
      <?php } ?>
      <tr>
    <?php echo $documento == "EXCEL" ? '<td colspan="5"></td>' : '<td colspan="5"></td>'; ?>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
      </tr>
    <?php } ?>
</table>
<?php
break;

case 'COTIZACIONESXFECHAS':

$tra = new Login();
$reg = $tra->BuscarCotizacionesxFechas();

$archivo = str_replace(" ", "_","LISTADO DE COTIZACIONES (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
     <tr>
       <th>Nº</th>
       <th>Nº DE COTIZACIÓN</th>
       <th>DESCRIPCIÓN DE CLIENTE</th>
       <th>OBSERVACIONES</th>
       <th>FECHA DE EMISIÓN</th>
       <th>Nº DE ARTICULOS</th>
       <?php if ($documento == "EXCEL") { ?>
       <th>SUBTOTAL</th>
       <th><?php echo $impuesto; ?></th>
       <th>DESC %</th>
       <?php } ?>
       <th>IMPORTE TOTAL</th>
     </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo '&nbsp;'.$reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['observaciones'] == '' ? "***********" : $reg[$i]['observaciones']; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechacotizacion'])); ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    </tr>
    <?php } ?>
    <tr>
    <?php echo $documento == "EXCEL" ? '<td colspan="5"></td>' : '<td colspan="5"></td>'; ?>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    </tr>
  <?php } ?>
</table>
<?php
break;

case 'DETALLESCOTIZACIONESXFECHAS':

$tra = new Login();
$reg = $tra->BuscarDetallesCotizacionesxFechas();

$archivo = str_replace(" ", "_","DETALLES COTIZACIONES POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
     <tr>
       <th>Nº</th>
       <th>TIPO</th>
       <th>CÓDIGO</th>
       <th>DESCRIPCIÓN</th>
       <th>CATEGORIA</th>
       <th>PRECIO VENTA</th>
       <th>EXISTENCIA</th>
       <th>COTIZADO</th>
       <th><?php echo $impuesto; ?></th>
       <th>DESC %</th>
       <th>MONTO TOTAL</th>
     </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
       <tr class="even_row">
        <td><?php echo $a++; ?></td>
        <td style="font-size:12px;color:#ff5050;font-weight:bold;"><?php echo $Tipo; ?></td>
        <td><?php echo $reg[$i]['codproducto']; ?></td>
        <td><?php echo $reg[$i]['producto']; ?></td>
        <td><?php echo $Categoria ?></td>
        <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
        <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
        <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
        <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
        <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
        <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
       </tr>
      <?php } } ?>
       <tr>
      <td colspan="5"></td>
<td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
<td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
<td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></strong></td>
      </tr>
</table>
<?php
break;

case 'DETALLESCOTIZACIONESXVENDEDOR':

$tra = new Login();
$reg = $tra->BuscarDetallesCotizacionesxVendedor(); 

$archivo = str_replace(" ", "_","DETALLES COTIZACIONES POR VENDEDOR (".$reg[0]['dni'].": ".$reg[0]['nombres']." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>TIPO</th>
           <th>CÓDIGO</th>
           <th>DESCRIPCIÓN</th>
           <th>CATEGORIA</th>
           <th>PRECIO VENTA</th>
           <th>EXISTENCIA</th>
           <th>COTIZADO</th>
           <th><?php echo $impuesto; ?></th>
           <th>DESC %</th>
           <th>MONTO TOTAL</th>
         </tr>
      <?php 

if($reg==""){
echo "";      
} else {

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
     <tr class="even_row">
      <td><?php echo $a++; ?></td>
      <td style="font-size:12px;color:#ff5050;font-weight:bold;"><?php echo $Tipo; ?></td>
      <td><?php echo $reg[$i]['codproducto']; ?></td>
      <td><?php echo $reg[$i]['producto']; ?></td>
      <td><?php echo $Categoria ?></td>
      <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
      <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
      <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
      <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
      <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
     </tr>
    <?php } } ?>
      <tr>
      <td colspan="5"></td>
<td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
<td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
<td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></strong></td>
      </tr>
</table>
<?php
break;
############################### MODULO DE COTIZACIONES ###############################
























##################################### MODULO DE CAJAS ###################################
case 'CAJAS':

$tra = new Login();
$reg = $tra->ListarCajas();  

$archivo = str_replace(" ", "_","LISTADO DE CAJAS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>Nº DE CAJA</th>
           <th>NOMBRE DE CAJA</th>
           <th>RESPONSABLE</th>
         </tr>
      <?php 

if($reg==""){
echo "";      
} else {
  
$a=1; 
for($i=0;$i<sizeof($reg);$i++){
?>
       <tr class="text-center" class="even_row">
         <td><?php echo $a++; ?></td>
         <td><?php echo $reg[$i]['nrocaja']; ?></td>
         <td><?php echo $reg[$i]['nomcaja']; ?></td>
         <td><?php echo $reg[$i]['dni'].": ".$reg[$i]['nombres']; ?></td>
       </tr>
      <?php } } ?>
</table>
<?php
break;

case 'ARQUEOS':

$tra = new Login();
$reg = $tra->ListarArqueoCaja();  

$archivo = str_replace(" ", "_","LISTADO DE DE ARQUEOS DE CAJAS DE (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>HORARIO</th>
           <th>NOMBRE DE CAJA</th>
           <?php if ($documento == "EXCEL") { ?>
           <th>RESPONSABLE</th>
           <th>APERTURA</th>
           <th>CIERRE</th>
           <th>OBSERVACIONES</th>
           <?php } ?>
           <th>INICIAL</th>
           <th>TOTAL EN VENTAS</th>
           <?php if ($documento == "EXCEL") { ?>
           <th>VENTAS EN EFECTIVO</th>
           <th>VENTAS EN OTROS</th>
           <th>VENTAS A CREDITOS</th>
           <th>ABONOS EN EFECTIVO</th>
           <th>ABONOS EN OTROS</th>
           <th>INGRESOS EN EFECTIVO</th>
           <th>INGRESOS EN OTROS</th>
           <th>EGRESOS</th>
           <th>PROPINAS EN EFECTIVO</th>
           <th>PROPINAS EN OTROS</th>
           <?php } ?>
           <th>TOTAL EFECTIVO</th>
           <th>EFECTIVO EN CAJA</th>
           <th>DIFERENCIA</th>
         </tr>
      <?php 

if($reg==""){
echo "";      
} else {

$TotalVentas = 0;
$VentasEfectivo = 0;
$VentasOtros = 0;
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

$VentasOtros += $reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'];

$TotalEfectivo += $reg[$i]['montoinicial']+$reg[$i]['efectivo']+$reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']+$reg[$i]['propinasefectivo']-$reg[$i]['egresos'];

$TotalOtros = $reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['abonosotros']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros']+$reg[$i]['ingresosotros'];


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
         <tr class="text-center" class="even_row">
          <td><?php echo $a++; ?></td>
          <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
          <td><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></td>
          <?php if ($documento == "EXCEL") { ?>
          <td><?php echo $reg[$i]['dni'].": ".$reg[$i]['nombres']; ?></td>
          <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaapertura'])); ?></td>
          <td><?php echo $reg[$i]['fechacierre'] == '0000-00-00 00:00:00' ? "*********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechacierre'])); ?></td>
          <td><?php echo $reg[$i]['comentarios'] == '' ? "*********" : $reg[$i]['comentarios']; ?></td>
          <?php } ?>
          <td><?php echo $simbolo.number_format($reg[$i]['montoinicial'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['efectivo']+$reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['creditos']+$reg[$i]['propinasefectivo']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'], 2, '.', ','); ?></td>
          <?php if ($documento == "EXCEL") { ?>
          <td><?php echo $simbolo.number_format($reg[$i]['efectivo']+$reg[$i]['propinasefectivo'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['creditos'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['abonosefectivo'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['abonosotros'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['ingresosefectivo'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['ingresosotros'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['egresos'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['propinasefectivo'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'], 2, '.', ','); ?></td>
          <?php } ?>
          <td><?php echo $simbolo.number_format($reg[$i]['montoinicial']+$reg[$i]['efectivo']+$reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']+$reg[$i]['propinasefectivo']-$reg[$i]['egresos'], 2, '.', ','); ?></td>

          <td><?php echo $simbolo.number_format($reg[$i]['dineroefectivo'], 2, '.', ','); ?></td>
          <td><?php echo $simbolo.number_format($reg[$i]['diferencia'], 2, '.', ','); ?></td>
         </tr>
        <?php } ?>
         <tr class="text-center">
<?php if ($documento == "EXCEL") { ?>    
          <td colspan="8"></td>
<?php } else { ?>   
          <td colspan="4"></td>
<?php } ?>
<td><strong><?php echo $simbolo.number_format($TotalVentas, 2, '.', ','); ?></strong></td>
<?php if ($documento == "EXCEL") { ?>
<td><strong><?php echo $simbolo.number_format($VentasEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($VentasOtros, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalCreditos, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($AbonosEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($AbonosOtros, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($IngresosEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($IngresosOtros, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalEgresos, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($PropinasEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($PropinasOtros, 2, '.', ','); ?></strong></td>
<?php } ?>
<td><strong><?php echo $simbolo.number_format($TotalEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalCaja, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalDiferencia, 2, '.', ','); ?></strong></td>
         </tr>
<?php } ?>
</table>
<?php
break;

case 'ARQUEOSXFECHAS':

$tra = new Login();
$reg = $tra->BuscarArqueosxFechas(); 

$archivo = str_replace(" ", "_","LISTADO DE ARQUEOS EN (CAJA ".$reg[0]['nrocaja'].": ".$reg[0]['nomcaja']." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL Nº: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>HORARIO</th>
           <th>RESPONSABLE</th>
<?php if ($documento == "EXCEL") { ?>
           <th>APERTURA</th>
           <th>CIERRE</th>
           <th>OBSERVACIONES</th>
<?php } ?>
           <th>INICIAL</th>
           <th>TOTAL EN VENTAS</th>
<?php if ($documento == "EXCEL") { ?>
           <th>VENTAS EN EFECTIVO</th>
           <th>VENTAS EN OTROS</th>
           <th>VENTAS A CREDITOS</th>
           <th>ABONOS EN EFECTIVO</th>
           <th>ABONOS EN OTROS</th>
           <th>INGRESOS EN EFECTIVO</th>
           <th>INGRESOS EN OTROS</th>
           <th>EGRESOS</th>
           <th>PROPINAS EN EFECTIVO</th>
           <th>PROPINAS EN OTROS</th>
<?php } ?>
           <th>TOTAL EFECTIVO</th>
           <th>EFECTIVO EN CAJA</th>
           <th>DIFERENCIA</th>
         </tr>
      <?php 

if($reg==""){
echo "";      
} else {

$TotalVentas = 0;
$VentasEfectivo = 0;
$VentasOtros = 0;
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

$VentasOtros += $reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'];

$TotalEfectivo += $reg[$i]['montoinicial']+$reg[$i]['efectivo']+$reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']+$reg[$i]['propinasefectivo']-$reg[$i]['egresos'];

$TotalOtros = $reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['abonosotros']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros']+$reg[$i]['ingresosotros'];

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
         <tr class="text-center" class="even_row">
           <td><?php echo $a++; ?></td>
           <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
           <td><?php echo $reg[$i]['dni'].": ".$reg[$i]['nombres']; ?></td>
<?php if ($documento == "EXCEL") { ?>
           <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaapertura'])); ?></td>
           <td><?php echo $reg[$i]['fechacierre'] == '0000-00-00 00:00:00' ? "*********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechacierre'])); ?></td>
           <td><?php echo $reg[$i]['comentarios'] == '' ? "*********" : $reg[$i]['comentarios']; ?></td>
<?php } ?>
            <td><?php echo $simbolo.number_format($reg[$i]['montoinicial'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['efectivo']+$reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['creditos']+$reg[$i]['propinasefectivo']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'], 2, '.', ','); ?></td>
<?php if ($documento == "EXCEL") { ?>
            <td><?php echo $simbolo.number_format($reg[$i]['efectivo']+$reg[$i]['propinasefectivo'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['cheque']+$reg[$i]['tcredito']+$reg[$i]['tdebito']+$reg[$i]['tprepago']+$reg[$i]['transferencia']+$reg[$i]['electronico']+$reg[$i]['cupon']+$reg[$i]['otros']+$reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['creditos'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['abonosefectivo'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['abonosotros'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['ingresosefectivo'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['ingresosotros'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['egresos'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['propinasefectivo'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['propinascheque']+$reg[$i]['propinastcredito']+$reg[$i]['propinastdebito']+$reg[$i]['propinastprepago']+$reg[$i]['propinastransferencia']+$reg[$i]['propinaselectronico']+$reg[$i]['propinascupon']+$reg[$i]['propinasotros'], 2, '.', ','); ?></td>
<?php } ?>
            <td><?php echo $simbolo.number_format($reg[$i]['montoinicial']+$reg[$i]['efectivo']+$reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']+$reg[$i]['propinasefectivo']-$reg[$i]['egresos'], 2, '.', ','); ?></td>

            <td><?php echo $simbolo.number_format($reg[$i]['dineroefectivo'], 2, '.', ','); ?></td>
            <td><?php echo $simbolo.number_format($reg[$i]['diferencia'], 2, '.', ','); ?></td>
<?php if ($_SESSION['acceso'] == "administradorG") { ?><td><strong><?php echo $reg[$i]['cuitsucursal'].": ".$reg[$i]['nomsucursal']; ?></strong></td><?php } ?>
         </tr>
        <?php } ?>
         <tr class="text-center">
<?php if ($documento == "EXCEL") { ?>    
          <td colspan="7"></td>
<?php } else { ?>   
          <td colspan="4"></td>
<?php } ?>
<td><strong><?php echo $simbolo.number_format($TotalVentas, 2, '.', ','); ?></strong></td>
<?php if ($documento == "EXCEL") { ?>
<td><strong><?php echo $simbolo.number_format($VentasEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($VentasOtros, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalCreditos, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($AbonosEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($AbonosOtros, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($IngresosEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($IngresosOtros, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalEgresos, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($PropinasEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($PropinasOtros, 2, '.', ','); ?></strong></td>
<?php } ?>
<td><strong><?php echo $simbolo.number_format($TotalEfectivo, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalCaja, 2, '.', ','); ?></strong></td>
<td><strong><?php echo $simbolo.number_format($TotalDiferencia, 2, '.', ','); ?></strong></td>
         </tr>
<?php } ?>
</table>
<?php
break;

case 'MOVIMIENTOS':

$tra = new Login();
$reg = $tra->ListarMovimientos(); 

$archivo = str_replace(" ", "_","LISTADO DE DE MOVIMIENTOS DE CAJAS DE (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>HORARIO</th>
           <th>NOMBRE DE CAJA</th>
           <th>RESPONSABLE</th>
           <th>DESCRIPCIÓN</th>
           <th>TIPO</th>
           <th>MONTO</th>
           <th>MEDIO</th>
           <th>FECHA MOVIMIENTO</th>
         </tr>
      <?php 

if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalImporte=0;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
$TotalImporte+=$reg[$i]['montomovimiento'];
?>
         <tr class="text-center" class="even_row">
           <td><?php echo $a++; ?></td>
           <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
           <td><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></td>
           <td><?php echo $reg[$i]['dni'].": ".$reg[$i]['nombres']; ?></td>
           <td><?php echo $reg[$i]['descripcionmovimiento']; ?></td>
           <td><?php echo $reg[$i]['tipomovimiento']; ?></td>
           <td><?php echo $simbolo.number_format($reg[$i]['montomovimiento'], 2, '.', ','); ?></td>
           <td><?php echo $reg[$i]['mediomovimiento']; ?></td>
           <td><?php echo $reg[$i]['fechamovimiento']; ?></td>
         </tr>
        <?php } ?>
         <tr class="text-center">
           <td colspan="6"></td>
<td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
<td></td>
<td></td>
         </tr>
        <?php } ?>
</table>
<?php
break;

case 'MOVIMIENTOSXFECHAS':

$tra = new Login();
$reg = $tra->BuscarMovimientosxFechas(); 

$archivo = str_replace(" ", "_","LISTADO DE MOVIMIENTOS EN (CAJA ".$reg[0]['nrocaja'].": ".$reg[0]['nomcaja']." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL Nº: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>HORARIO</th>
           <th>RESPONSABLE</th>
           <th>DESCRIPCIÓN</th>
           <th>TIPO</th>
           <th>MONTO</th>
           <th>MEDIO</th>
           <th>FECHA MOVIMIENTO</th>
         </tr>
      <?php 

if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalImporte=0;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
$TotalImporte+=$reg[$i]['montomovimiento'];
?>
         <tr class="text-center" class="even_row">
           <td><?php echo $a++; ?></td>
           <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
           <td><?php echo $reg[$i]['dni'].": ".$reg[$i]['nombres']; ?></td>
           <td><?php echo $reg[$i]['descripcionmovimiento']; ?></td>
           <td><?php echo $reg[$i]['tipomovimiento']; ?></td>
           <td><?php echo $simbolo.number_format($reg[$i]['montomovimiento'], 2, '.', ','); ?></td>
           <td><?php echo $reg[$i]['mediomovimiento']; ?></td>
           <td><?php echo $reg[$i]['fechamovimiento']; ?></td>
         </tr>
        <?php } } ?>
         <tr class="text-center">
           <td colspan="5"></td>
<td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
<td></td>
<td></td>
         </tr>
</table>
<?php
break;

case 'INFORMECAJASXFECHAS':

$caja = new Login();
$caja = $caja->CajasPorId();
$simbolo = ($caja[0]['simbolo'] == "" ? "" : "<strong>".$caja[0]['simbolo']."</strong>");

$venta = new Login();
$venta = $venta->SumarVentasCajasxFechas();

$arqueo = new Login();
$arqueo = $arqueo->SumarArqueosCajasxFechas();

$balance = $venta[0]['totalventa']-$venta[0]['totaliva']+$arqueo[0]['totalingresos']+$arqueo[0]['totalabonos'];
$ganancias = $balance-$arqueo[0]['totalegresos']; 

$archivo = str_replace(" ", "_","INFORME DE (CAJA ".$caja[0]['nrocaja'].": ".$caja[0]['nomcaja']." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL Nº: ".$caja[0]['cuitsucursal'].": ".$caja[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr align="center">
           <th><strong>TOTAL DE VENTAS</strong></th>
           <th><strong><?php echo $simbolo.number_format($venta[0]['totalventa'], 2, '.', ','); ?></strong></th>
         </tr>
          <tr align="center">
           <td><strong>TOTAL DE INGRESOS</strong></td>
           <td><strong><?php echo $simbolo.number_format($arqueo[0]['totalingresos'], 2, '.', ','); ?></strong></td>
         </tr>
          <tr align="center">
           <td><strong>ABONOS A CRÉDITOS</strong></td>
           <td><strong><?php echo $simbolo.number_format($arqueo[0]['totalabonos'], 2, '.', ','); ?></strong></td>
         </tr>
          <tr align="center">
           <td><strong>TOTAL DE GASTOS (EGRESOS)</strong></td>
           <td><strong><?php echo $simbolo.number_format($arqueo[0]['totalegresos'], 2, '.', ','); ?></strong></td>
         </tr>
          <tr align="center">
           <td><strong>TOTAL DE IMPUESTOS DE VENTAS <?php echo $impuesto; ?> (<?php echo $valor; ?>%)</strong></td>
           <td><strong><?php echo $simbolo.number_format($venta[0]['totaliva'], 2, '.', ','); ?></strong></td>
         </tr>
          <tr align="center">
           <td><strong>EFECTIVO EN CAJA SIN IMPUESTO</strong></td>
           <td><strong><?php echo $simbolo.number_format($ganancias, 2, '.', ','); ?></strong></td>
         </tr>
</table>
<?php
break;

case 'GANANCIASXFECHAS':

$ingresos = new Login();
$detalle_ingreso = $ingresos->BuscarIngresosxFechas(); 

$gastos = new Login();
$detalle_gasto = $gastos->BuscarGastosxFechas(); 

$ganancias = new Login();
$reg = $ganancias->BuscarGananciasxFechas();  

$archivo = str_replace(" ", "_","GANANCIAS POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>CÓDIGO</th>
      <th>DESCRIPCIÓN DE PRODUCTO</th>
      <th>CATEGORIA</th>
      <th>DESC.</th>
      <?php if($_SESSION['acceso']=="administradorG" || $_SESSION['acceso']=="administradorS"){ ?>
      <th>PRECIO COMPRA</th>
      <?php } ?>
      <th>PRECIO VENTA</th>
      <th>VENDIDO</th>
      <th>TOTAL VENTA</th>
      <th>TOTAL COMPRA</th>
      <th>GANANCIAS</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
    <tr class="even_row">
      <td><?php echo $a++; ?></td>
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
      <?php if($_SESSION['acceso']=="administradorG" || $_SESSION['acceso']=="administradorS"){ ?>
      <td><?php echo $simbolo.number_format($reg[$i]["preciocompra"], 2, '.', ','); ?></td>
      <?php } ?>
      <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
      <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
      <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
    </tr>
    <?php } } ?>
    <tr>
    <?php if($_SESSION['acceso']=="administradorG" || $_SESSION['acceso']=="administradorS"){ ?><td colspan="7"></td><?php } else { ?><td colspan="6"></td><?php } ?>
    <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($VentaTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($CompraTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></strong></td>
    </tr>
    <tr class="text-dark alert-link">
      <?php if($_SESSION['acceso']=="administradorG" || $_SESSION['acceso']=="administradorS"){ ?><td colspan="9"></td><?php } else { ?><td colspan="8"></td><?php } ?>
      <td><strong>INGRESOS ADICIONALES</strong></td>
      <td><strong><?php echo $simbolo.number_format($detalle_ingreso[0]['ingresos'], 2, '.', ','); ?></strong></td>
    </tr>
    <tr class="text-dark alert-link">
      <?php if($_SESSION['acceso']=="administradorG" || $_SESSION['acceso']=="administradorS"){ ?><td colspan="9"></td><?php } else { ?><td colspan="8"></td><?php } ?>
      <td><strong>GASTOS</strong></td>
      <td><strong><?php echo $simbolo.number_format($detalle_gasto[0]['gastos'], 2, '.', ','); ?></strong></td>
    </tr>
    <tr class="text-dark alert-link">
      <?php if($_SESSION['acceso']=="administradorG" || $_SESSION['acceso']=="administradorS"){ ?><td colspan="9"></td><?php } else { ?><td colspan="8"></td><?php } ?>
      <td><strong>TOTAL</strong></td>
      <td><strong><?php echo $simbolo.number_format($TotalGanancia+$detalle_ingreso[0]['ingresos']-$detalle_gasto[0]['gastos'], 2, '.', ','); ?></strong></td>
    </tr>
</table>
<?php
break;
#################################### MODULO DE CAJAS ####################################




















################################## MODULO DE PEDIDOS ###################################
case 'PEDIDOSDIARIAS':

$tra = new Login();
$reg = $tra->ListarPedidosDiarias();  

$archivo = str_replace(" ", "_","LISTADO DE PEDIDOS DEL (DIA".date("d-m-Y")." EN SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
         <tr>
           <th>Nº</th>
           <th>Nº DE VENTA</th>
           <th>DESCRIPCIÓN DE CLIENTE</th>
           <th>TIPO DE PAGO</th>
           <?php if ($documento == "EXCEL") { ?>
           <th>ESTADO</th>
           <?php } ?>
           <th>FECHA EMISIÓN</th>
           <?php if ($documento == "EXCEL") { ?>
           <th>FORMA DE PAGO #1</th>
           <th>FORMA DE PAGO #2</th>
           <?php } ?>
           <th>Nº DE ARTICULOS</th>
           <th>IMPORTE TOTAL</th>
           <th>TOTAL ABONO</th>
           <th>TOTAL DEBE</th>
         </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$TotalArticulos+= $reg[$i]['articulos'];
$TotalImporte+= $reg[$i]['totalpago'];
$TotalAbono+= $reg[$i]['creditopagado'];
$TotalDebe+= ($reg[$i]['tipopago'] == 'CONTADO' ? "0.00" : $reg[$i]['totalpago']-$reg[$i]['creditopagado']);
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]["statuspedido"] == 0 ? "ENTREGADA" : "PENDIENTE"; ?></td>
    <?php } ?> 
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['tipopago'] == 'CONTADO' ? $simbolo."0.00" : $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="8"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'PEDIDOS':

$tra = new Login();
$reg = $tra->ListarPedidos();  

$archivo = str_replace(" ", "_","LISTADO DE PEDIDOS EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
       <tr>
         <th>Nº</th>
         <th>Nº DE VENTA</th>
         <th>DESCRIPCIÓN DE CLIENTE</th>
         <th>TIPO DE PAGO</th>
         <?php if ($documento == "EXCEL") { ?>
         <th>ESTADO</th>
         <?php } ?>
         <th>FECHA EMISIÓN</th>
         <?php if ($documento == "EXCEL") { ?>
         <th>FORMA DE PAGO #1</th>
         <th>FORMA DE PAGO #2</th>
         <?php } ?>
         <th>Nº DE ARTICULOS</th>
         <th>IMPORTE TOTAL</th>
         <th>TOTAL ABONO</th>
         <th>TOTAL DEBE</th>
       </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$TotalArticulos+= $reg[$i]['articulos'];
$TotalImporte+= $reg[$i]['totalpago'];
$TotalAbono+= $reg[$i]['creditopagado'];
$TotalDebe+= ($reg[$i]['tipopago'] == 'CONTADO' ? "0.00" : $reg[$i]['totalpago']-$reg[$i]['creditopagado']);
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]["statuspedido"] == 0 ? "ENTREGADA" : "PENDIENTE"; ?></td>
    <?php } ?>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['tipopago'] == 'CONTADO' ? $simbolo."0.00" : $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="8"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'PEDIDOSXCAJAS':

$tra = new Login();
$reg = $tra->BuscarPedidosxCajas(); 

$archivo = str_replace(" ", "_","LISTADO DE PEDIDOS EN (CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." DE HORARIO: ".$reg[0]["hora_desde"]." - ".$reg[0]["hora_hasta"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>Nº DE VENTA</th>
          <th>DESCRIPCIÓN DE CLIENTE</th>
          <th>TIPO DE PAGO</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>ESTADO</th>
          <?php } ?>
          <th>FECHA EMISIÓN</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>FORMA DE PAGO #1</th>
          <th>FORMA DE PAGO #2</th>
          <?php } ?>
          <th>Nº DE ARTICULOS</th>
          <th>IMPORTE TOTAL</th>
          <th>TOTAL ABONO</th>
          <th>TOTAL DEBE</th>
         </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$TotalArticulos+= $reg[$i]['articulos'];
$TotalImporte+= $reg[$i]['totalpago'];
$TotalAbono+= $reg[$i]['creditopagado'];
$TotalDebe+= ($reg[$i]['tipopago'] == 'CONTADO' ? "0.00" : $reg[$i]['totalpago']-$reg[$i]['creditopagado']);
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]["statuspedido"] == 0 ? "ENTREGADA" : "PENDIENTE"; ?></td>
    <?php } ?>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['tipopago'] == 'CONTADO' ? $simbolo."0.00" : $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="8"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'PEDIDOSXFECHAS':

$tra = new Login();
$reg = $tra->BuscarPedidosxFechas();

$archivo = str_replace(" ", "_","LISTADO DE PEDIDOS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE VENTA</th>
      <th>DESCRIPCIÓN DE CLIENTE</th>
      <th>TIPO DE PAGO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>FORMA DE PAGO #1</th>
      <th>FORMA DE PAGO #2</th>
      <?php } ?>
      <th>Nº DE ARTICULOS</th>
      <th>IMPORTE TOTAL</th>
      <th>TOTAL ABONO</th>
      <th>TOTAL DEBE</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$TotalArticulos+= $reg[$i]['articulos'];
$TotalImporte+= $reg[$i]['totalpago'];
$TotalAbono+= $reg[$i]['creditopagado'];
$TotalDebe+= ($reg[$i]['tipopago'] == 'CONTADO' ? "0.00" : $reg[$i]['totalpago']-$reg[$i]['creditopagado']);
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]["statuspedido"] == 0 ? "ENTREGADA" : "PENDIENTE"; ?></td>
    <?php } ?>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['tipopago'] == 'CONTADO' ? $simbolo."0.00" : $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="8"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;


case 'PEDIDOSXFECHASENTREGA':

$tra = new Login();
$reg = $tra->BuscarPedidosxFechasEntrega();

$archivo = str_replace(" ", "_","LISTADO DE PEDIDOS DE ENTREGA (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE VENTA</th>
      <th>DESCRIPCIÓN DE CLIENTE</th>
      <th>TIPO DE PAGO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <th>FECHA ENTREGA</th>
      <th>HORA ENTREGA</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>FORMA DE PAGO #1</th>
      <th>FORMA DE PAGO #2</th>
      <?php } ?>
      <th>DETALLES DE ARTICULOS</th>
      <th>IMPORTE TOTAL</th>
      <th>TOTAL ABONO</th>
      <th>TOTAL DEBE</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$TotalArticulos+= $reg[$i]['articulos'];
$TotalImporte+= $reg[$i]['totalpago'];
$TotalAbono+= $reg[$i]['creditopagado'];
$TotalDebe+= ($reg[$i]['tipopago'] == 'CONTADO' ? "0.00" : $reg[$i]['totalpago']-$reg[$i]['creditopagado']);
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]["statuspedido"] == 0 ? "ENTREGADA" : "PENDIENTE"; ?></td>
    <?php } ?>
     
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaentrega'])); ?></td>
    <td><?php echo date("H:i:s",strtotime($reg[$i]['fechaentrega'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td style="text-align:left;font-weight:bold;font-size:10px;"><?php echo $reg[$i]['detalles_productos']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['tipopago'] == 'CONTADO' ? $simbolo."0.00" : $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="11"></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;
################################## MODULO DE PEDIDOS ###################################




















################################## MODULO DE VENTAS ###################################
case 'VENTASDIARIAS':

$tra = new Login();
$reg = $tra->ListarVentasDiarias();  

$archivo = str_replace(" ", "_","LISTADO DE VENTAS DEL (DIA".date("d-m-Y")." EN SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE VENTA</th>
      <th>DESCRIPCIÓN DE CLIENTE</th>
      <th>TIPO DE PAGO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <th>DIAS VENC.</th>
      <th>FECHA VENCE</th>
      <th>FECHA PAGADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>FORMA DE PAGO #1</th>
      <th>FORMA DE PAGO #2</th>
      <?php } ?>
      <th>Nº DE ARTICULOS</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>SUBTOTAL</th>
      <th><?php echo $impuesto; ?></th>
      <th>DESC %</th>
      <th>PROPINA %</th>
      <?php } ?>
      <th>IMPORTE TOTAL</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalPropina=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
   
$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalPropina+=$reg[$i]['totalpropina'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpropina'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['propinasugerida'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="11"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalPropina, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'VENTAS':

$tra = new Login();
$reg = $tra->ListarVentas();  

$archivo = str_replace(" ", "_","LISTADO DE VENTAS DE (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE VENTA</th>
      <th>DESCRIPCIÓN DE CLIENTE</th>
      <th>TIPO DE PAGO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <th>DIAS VENC.</th>
      <th>FECHA VENCE</th>
      <th>FECHA PAGADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>FORMA DE PAGO #1</th>
      <th>FORMA DE PAGO #2</th>
      <?php } ?>
      <th>Nº DE ARTICULOS</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>SUBTOTAL</th>
      <th><?php echo $impuesto; ?></th>
      <th>DESC %</th>
      <th>PROPINA %</th>
      <?php } ?>
      <th>IMPORTE TOTAL</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalPropina=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
   
$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalPropina+=$reg[$i]['totalpropina'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpropina'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['propinasugerida'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="11"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalPropina, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'DELIVERYDIARIAS':

$tra = new Login();
$reg = $tra->ListarVentasDeliveryDiarias();  

$archivo = str_replace(" ", "_","LISTADO DE VENTAS  EN DELIVERY DEL (DIA".date("d-m-Y")." EN SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>TIPO DELIVERY</th>
        <th>Nº DE VENTA</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <th>TIPO DE PAGO</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>ESTADO</th>
        <th>DIAS VENC.</th>
        <th>FECHA VENCE</th>
        <th>FECHA PAGADO</th>
        <?php } ?>
        <th>FECHA EMISIÓN</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>FORMA DE PAGO #1</th>
        <th>FORMA DE PAGO #2</th>
        <?php } ?>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php if($reg[$i]['repartidor'] == 0){ echo "EN ESTABLECIMIENTO"; 
    } elseif($reg[$i]['repartidor'] != 0 ){ echo "DELIVERY"; } ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>

    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>  
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="12"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'DELIVERYPENDIENTES':

$tra = new Login();
$reg = $tra->ListarVentasDeliveryPendientes();  

$archivo = str_replace(" ", "_","LISTADO DE VENTAS DE DELIVERY PENDIENTES EN (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>TIPO DELIVERY</th>
      <th>Nº DE VENTA</th>
      <th>DESCRIPCIÓN DE CLIENTE</th>
      <th>TIPO DE PAGO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <th>DIAS VENC.</th>
      <th>FECHA VENCE</th>
      <th>FECHA PAGADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>FORMA DE PAGO #1</th>
      <th>FORMA DE PAGO #2</th>
      <?php } ?>
      <th>Nº DE ARTICULOS</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>SUBTOTAL</th>
      <th><?php echo $impuesto; ?></th>
      <th>DESC %</th>
      <?php } ?>
      <th>IMPORTE TOTAL</th>
      <?php if ($_SESSION['acceso'] == "administradorG") { ?><th>SUCURSAL</th><?php } ?>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php if($reg[$i]['repartidor'] == 0){ echo "EN ESTABLECIMIENTO"; 
    } elseif($reg[$i]['repartidor'] != 0 ){ echo "DELIVERY"; } ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>      
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
    <?php if ($_SESSION['acceso'] == "administradorG") { ?><td><strong><?php echo $reg[$i]['cuitsucursal'].": ".$reg[$i]['nomsucursal']; ?></strong></td><?php } ?>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="12"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'DELIVERYPAGADOS':

$tra = new Login();
$reg = $tra->ListarVentasDeliveryPagadas();  

$archivo = str_replace(" ", "_","LISTADO DE DELIVERY PAGADOS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>TIPO DELIVERY</th>
          <th>Nº DE VENTA</th>
          <th>DESCRIPCIÓN DE CLIENTE</th>
          <th>TIPO DE PAGO</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>ESTADO</th>
          <th>DIAS VENC.</th>
          <th>FECHA VENCE</th>
          <th>FECHA PAGADO</th>
          <?php } ?>
          <th>FECHA EMISIÓN</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>FORMA DE PAGO #1</th>
          <th>FORMA DE PAGO #2</th>
          <?php } ?>
          <th>Nº DE ARTICULOS</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>SUBTOTAL</th>
          <th><?php echo $impuesto; ?></th>
          <th>DESC %</th>
          <?php } ?>
          <th>IMPORTE TOTAL</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php if($reg[$i]['repartidor'] == 0){ echo "EN ESTABLECIMIENTO"; 
    } elseif($reg[$i]['repartidor'] != 0 ){ echo "DELIVERY"; } ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>

    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>

    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
      
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>

    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="12"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
   <?php } ?>
   <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'VENTASXCAJAS':

$tra = new Login();
$reg = $tra->BuscarVentasxCajas(); 

if(decrypt($_GET['tipopago']) == 1){ 

$archivo = str_replace(" ", "_","LISTADO DE VENTAS GENERALES EN (CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." DE HORARIO: ".$reg[0]["hora_desde"]." - ".$reg[0]["hora_hasta"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

} elseif(decrypt($_GET['tipopago']) == 2){ 

$archivo = str_replace(" ", "_","LISTADO DE VENTAS A CONTADO EN (CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." DE HORARIO: ".$reg[0]["hora_desde"]." - ".$reg[0]["hora_hasta"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

} elseif(decrypt($_GET['tipopago']) == 3){ 

$archivo = str_replace(" ", "_","LISTADO DE VENTAS A CRÉDITO EN (CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." DE HORARIO: ".$reg[0]["hora_desde"]." - ".$reg[0]["hora_hasta"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");  

}

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE VENTA</th>
      <th>DESCRIPCIÓN DE CLIENTE</th>
      <th>TIPO DE PAGO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <th>DIAS VENC.</th>
      <th>FECHA VENCE</th>
      <th>FECHA PAGADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>FORMA DE PAGO #1</th>
      <th>FORMA DE PAGO #2</th>
      <?php } ?>
      <th>Nº DE ARTICULOS</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>SUBTOTAL</th>
      <th><?php echo $impuesto; ?></th>
      <th>DESC %</th>
      <th>PROPINA %</th>
      <?php } ?>
      <th>IMPORTE TOTAL</th>
      <th>ABONADO</th>
      <th>TOTAL DEBE</th>
      <th>TOTAL DISPONIBLE</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalPropina=0;
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
$TotalPropina+=$reg[$i]['totalpropina'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=($reg[$i]["tipopago"] == "CREDITO" ? number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', '') : "0.00");
$TotalDisponible+=($reg[$i]["tipopago"] == "CREDITO" ? number_format($reg[$i]['creditopagado'], 2, '.', '') : number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ''));
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpropina'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['propinasugerida'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $var = ($reg[$i]["tipopago"] == "CREDITO" ? $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', ',') : "0.00"); ?></td>
    <td><?php echo $var = ($reg[$i]["tipopago"] == "CREDITO" ? $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ',') : $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ',')); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="11"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalPropina, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDisponible, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'VENTASXFECHAS':

$tra = new Login();
$reg = $tra->BuscarVentasxFechas();

if(decrypt($_GET['tipopago']) == 1){ 

$archivo = str_replace(" ", "_","LISTADO DE VENTAS GENERALES POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($_GET['tipopago']) == 2){ 

$archivo = str_replace(" ", "_","LISTADO DE VENTAS A CONTADO POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($_GET['tipopago']) == 3){ 

$archivo = str_replace(" ", "_","LISTADO DE VENTAS A CREDITO POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

}

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE VENTA</th>
      <th>DESCRIPCIÓN DE CLIENTE</th>
      <th>TIPO DE PAGO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <th>DIAS VENC.</th>
      <th>FECHA VENCE</th>
      <th>FECHA PAGADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>FORMA DE PAGO #1</th>
      <th>FORMA DE PAGO #2</th>
      <?php } ?>
      <th>Nº DE ARTICULOS</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>SUBTOTAL</th>
      <th><?php echo $impuesto; ?></th>
      <th>DESC %</th>
      <th>PROPINA %</th>
      <?php } ?>
      <th>IMPORTE TOTAL</th>
      <th>ABONADO</th>
      <th>TOTAL DEBE</th>
      <th>TOTAL DISPONIBLE</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalPropina=0;
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
$TotalPropina+=$reg[$i]['totalpropina'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=($reg[$i]["tipopago"] == "CREDITO" ? number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', '') : "0.00");
$TotalDisponible+=($reg[$i]["tipopago"] == "CREDITO" ? number_format($reg[$i]['creditopagado'], 2, '.', '') : number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ''));
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>

    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpropina'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['propinasugerida'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $var = ($reg[$i]["tipopago"] == "CREDITO" ? $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"]-$reg[$i]['creditopagado'], 2, '.', ',') : "0.00"); ?></td>
    <td><?php echo $var = ($reg[$i]["tipopago"] == "CREDITO" ? $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ',') : $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ',')); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="11"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalPropina, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDisponible, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'VENTASXCONDICIONES':

$tra = new Login();
$reg = $tra->BuscarVentasxCondiciones();
$formapago = limpiar($_GET["formapago"]); 

$archivo = str_replace(" ", "_","LISTADO DE VENTAS A CONTADO EN (FORMA DE PAGO: ".$formapago." DE CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." Y FECHA DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE VENTA</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <th>TIPO DE PAGO</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>ESTADO</th>
        <th>DIAS VENC.</th>
        <th>FECHA VENCE</th>
        <th>FECHA PAGADO</th>
        <?php } ?>
        <th>FECHA EMISIÓN</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>FORMA DE PAGO #1</th>
        <th>FORMA DE PAGO #2</th>
        <?php } ?>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <th>PROPINA %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
        <th>TOTAL PAGO</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalPropina=0;
$TotalImporte=0;
$TotalPagado=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
   
$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalPropina+=$reg[$i]['totalpropina'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
if($reg[$i]['formapago'] == $formapago){
$ImportePagado = $reg[$i]['montopagado']-$reg[$i]['montodevuelto'];
$TotalPagado += $reg[$i]['montopagado']-$reg[$i]['montodevuelto'];
} else {
$ImportePagado = $reg[$i]['montopagado2'];
$TotalPagado += $reg[$i]['montopagado2']; 
}
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpropina'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['propinasugerida'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['formapago'] == $formapago ? $simbolo.number_format($reg[$i]['montopagado'], 2, '.', ',') : $simbolo.number_format($reg[$i]['montopagado2'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="11"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalPropina, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalPagado, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'VENTASXTIPOS':

$tra = new Login();
$reg = $tra->BuscarVentasxTipos(); 

$archivo = str_replace(" ", "_","LISTADO DE VENTAS A CLIENTES (".$tipo = ($_GET["tipocliente"] == 'NATURAL' ? "NATURALES" : "JURIDICOS")." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE DOCUMENTO</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <th>Nº DE TELÉFONO</th>
        <th>CANTIDAD COMPRAS</th>
        <th>TOTAL COMPRAS</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalGeneral=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
   
$TotalArticulos+=$reg[$i]['cantidad'];
$TotalGeneral+=$reg[$i]['totalcompras'];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo $documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]["dnicliente"]; ?></td>
    <td><?php echo $reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tlfcliente'] == '' ? "*********" : $reg[$i]['tlfcliente']; ?></td>
    <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalcompras'], 2, '.', ','); ?></td>
    </tr>
  <?php } } ?>
  <tr class="text-center">
    <td colspan="4"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalGeneral, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;

case 'VENTASXCLIENTES':

$tra = new Login();
$reg = $tra->BuscarVentasxClientes(); 

$archivo = str_replace(" ", "_","LISTADO DE DETALLE DE VENTAS DEL CLIENTE (".$reg[0]["dnicliente"].": ".$reg[0]["nomcliente"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE VENTA</th>
      <th>TIPO DE PAGO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <th>DIAS VENC.</th>
      <th>FECHA VENCE</th>
      <th>FECHA PAGADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>FORMA DE PAGO #1</th>
      <th>FORMA DE PAGO #2</th>
      <?php } ?>
      <th>Nº DE ARTICULOS</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>SUBTOTAL</th>
      <th><?php echo $impuesto; ?></th>
      <th>DESC %</th>
      <th>PROPINA %</th>
      <?php } ?>
      <th>IMPORTE TOTAL</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalPropina=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
   
$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalPropina+=$reg[$i]['totalpropina'];
$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['formapago'] == "0" ? "**********" : $reg[$i]['formapago']; ?></td>
    <td><?php echo $reg[$i]['formapago2'] == "0" ? "**********" : $reg[$i]['formapago2']; ?></td>
    <?php } ?>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpropina'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['propinasugerida'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]+$reg[$i]["montodelivery"], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="10"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalPropina, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'DELIVERYXFECHAS':

$tra = new Login();
$reg = $tra->BuscarDeliveryxFechas(); 

$archivo = str_replace(" ", "_","LISTADO DE DELIVERY POR REPARTIDOR (".$reg[0]["dni2"].": ".$reg[0]["nombres2"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE VENTA</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <th>FECHA EMISIÓN</th>
        <th>Nº DE ARTICULOS</th>
        <th>TOTAL IMPORTE</th>
        <th>TOTAL DELIVERY</th>
        <th>TOTAL COMISIÓN</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalImporte=0;
$TotalDelivery=0;
$TotalComision=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
   
$CalculoComision = ($reg[$i]['comision2'] == "0.00" ? "0.00" : ($reg[$i]['montodelivery'])*($reg[$i]['comision2']/100));

$TotalArticulos+=$reg[$i]['articulos'];
$TotalImporte+=$reg[$i]['totalpago'];
$TotalDelivery+=$reg[$i]['montodelivery'];
$TotalComision+=$CalculoComision;
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['montodelivery'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($CalculoComision, 2, '.', ','); ?></td>
  </tr>
  <?php } } ?>
  <tr class="text-center">
    <td colspan="4"></td>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDelivery, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalComision, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;

case 'DETALLESVENTASXCAJAS':

$tra = new Login();
$reg = $tra->BuscarDetallesVentasxCajas();

if(decrypt($_GET['tipopago']) == 1){ 

$archivo = str_replace(" ", "_","DETALLES DE VENTAS GENERALES EN (CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." DE HORARIO: ".$reg[0]["hora_desde"]." - ".$reg[0]["hora_hasta"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

} elseif(decrypt($_GET['tipopago']) == 2){ 

$archivo = str_replace(" ", "_","DETALLES DE VENTAS A CONTADO EN (CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." DE HORARIO: ".$reg[0]["hora_desde"]." - ".$reg[0]["hora_hasta"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

} elseif(decrypt($_GET['tipopago']) == 3){ 

$archivo = str_replace(" ", "_","DETALLES DE VENTAS A CRÉDITO EN (CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." DE HORARIO: ".$reg[0]["hora_desde"]." - ".$reg[0]["hora_hasta"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");  

} 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>TIPO</th>
        <th>CÓDIGO</th>
        <th>DESCRIPCIÓN</th>
        <th>CATEGORIA</th>
        <th>PRECIO VENTA</th>
        <th>EXISTENCIA</th>
        <th>VENDIDO</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <th>MONTO TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td style="font-size:12px;color:#ff5050;font-weight:bold;"><?php echo $Tipo; ?></td>
    <td><?php echo $reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $Categoria ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
  </tr>
  <?php } } ?>
  <tr>
    <td colspan="5"></td>
    <td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;

case 'DETALLESVENTASXFECHAS':

$tra = new Login();
$reg = $tra->BuscarDetallesVentasxFechas();

if(decrypt($_GET['tipopago']) == 1){ 

$archivo = str_replace(" ", "_","DETALLES DE VENTAS GENERALES POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($_GET['tipopago']) == 2){ 

$archivo = str_replace(" ", "_","DETALLES DE VENTAS A CONTADO POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($_GET['tipopago']) == 3){ 

$archivo = str_replace(" ", "_","DETALLES DE VENTAS A CREDITO POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 
}

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>TIPO</th>
        <th>CÓDIGO</th>
        <th>DESCRIPCIÓN</th>
        <th>CATEGORIA</th>
        <th>PRECIO VENTA</th>
        <th>EXISTENCIA</th>
        <th>VENDIDO</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <th>MONTO TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td style="font-size:12px;color:#ff5050;font-weight:bold;"><?php echo $Tipo; ?></td>
    <td><?php echo $reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $Categoria ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
  </tr>
  <?php } } ?>
  <tr>
    <td colspan="5"></td>
    <td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;

case 'DETALLESVENTASXVENDEDOR':

$tra = new Login();
$reg = $tra->BuscarDetallesVentasxVendedor(); 

if(decrypt($_GET['tipopago']) == 1){ 

$archivo = str_replace(" ", "_","DETALLES DE VENTAS GENERALES POR VENDEDOR (".$reg[0]['dni'].": ".$reg[0]['nombres']." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($_GET['tipopago']) == 2){ 

$archivo = str_replace(" ", "_","DETALLES DE VENTAS A CONTADO POR VENDEDOR (".$reg[0]['dni'].": ".$reg[0]['nombres']." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($_GET['tipopago']) == 3){ 

$archivo = str_replace(" ", "_","DETALLES DE VENTAS A CREDITO POR VENDEDOR (".$reg[0]['dni'].": ".$reg[0]['nombres']." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 
}


header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>TIPO</th>
      <th>CÓDIGO</th>
      <th>DESCRIPCIÓN</th>
      <th>CATEGORIA</th>
      <th>PRECIO VENTA</th>
      <th>EXISTENCIA</th>
      <th>VENDIDO</th>
      <th><?php echo $impuesto; ?></th>
      <th>DESC %</th>
      <th>MONTO TOTAL</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {

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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td style="font-size:12px;color:#ff5050;font-weight:bold;"><?php echo $Tipo; ?></td>
    <td><?php echo $reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $Categoria ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]["precioventa"], 2, '.', ','); ?></td>
    <td><?php echo number_format($Existencia, 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['cantidad'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($CalculoImpuesto, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivaproducto'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($CalculoDescuento, 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($PrecioFinal*$reg[$i]['cantidad'], 2, '.', ','); ?></td>
  </tr>
  <?php } } ?>
  <tr>
    <td colspan="5"></td>
    <td><strong><?php echo $simbolo.number_format($PrecioTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($ExisteTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo number_format($VendidosTotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($PagoTotal, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;

case 'COMISIONXVENTAS':

$tra = new Login();
$reg = $tra->BuscarComisionxVentas(); 

$archivo = str_replace(" ", "_","LISTADO DE COMISIÓN POR VENDEDOR (".$reg[0]["dni"].": ".$reg[0]["nombres"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
      <th>Nº</th>
      <th>Nº DE VENTA</th>
      <th>DESCRIPCIÓN DE CLIENTE</th>
      <th>TIPO DE PAGO</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>ESTADO</th>
      <th>DIAS VENC.</th>
      <th>FECHA VENCE</th>
      <th>FECHA PAGADO</th>
      <?php } ?>
      <th>FECHA EMISIÓN</th>
      <th>Nº DE ARTICULOS</th>
      <?php if ($documento == "EXCEL") { ?>
      <th>SUBTOTAL</th>
      <th><?php echo $impuesto; ?></th>
      <th>DESC %</th>
      <?php } ?>
      <th>IMPORTE TOTAL</th>
      <th>TOTAL COMISIÓN</th>
    </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalPropina=0;
$TotalImporte=0;
$TotalComision=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$CalculoComision = ($reg[$i]['comision'] == "0.00" ? "0.00" : ($reg[$i]['totalpago'])*($reg[$i]['comision']/100));

$TotalArticulos += $reg[$i]['articulos'];
$TotalSubtotal  += $reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto  += $reg[$i]['totaliva'];
$TotalDescuento += $reg[$i]['totaldescuento'];
$TotalPropina   += $reg[$i]['totalpropina'];
$TotalImporte   += $reg[$i]['totalpago'];
$TotalComision  += $CalculoComision;
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?></td>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>    
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($CalculoComision, 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <?php echo $documento == "EXCEL" ? '<td colspan="9"></td>' : '<td colspan="5"></td>'; ?>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalComision, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'INFORMEVENTASXFECHAS':

$sucursal = new Login();
$sucursal = $sucursal->SucursalesPorId();
$simbolo = ($sucursal[0]['simbolo'] == "" ? "" : $sucursal[0]['simbolo']);

$venta = new Login();
$venta = $venta->SumarVentasxFechas();

$compra = new Login();
$compra = $compra->SumarComprasxFechas();

$arqueo = new Login();
$arqueo = $arqueo->SumarArqueosxFechas();

$cartera = new Login();
$cartera = $cartera->SumarCarteraxFechas();

$utilidadneta = $venta[0]['totalventa']-$venta[0]['totalcompra']-$venta[0]['totaliva'];
$balance = $venta[0]['totalventa']-$venta[0]['totaliva']+$arqueo[0]['totalingresos']+$arqueo[0]['totalabonos'];
$balance2 = $compra[0]['totalcomprageneral']+$arqueo[0]['totalegresos'];
$balancegeneral = $balance - $balance2;

$archivo = str_replace(" ", "_","INFORME DE VENTAS POR FECHAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL Nº: ".$sucursal[0]['cuitsucursal'].": ".$sucursal[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
  <tr align="center">
    <th><strong>TOTAL DE VENTAS</strong></th>
    <th><strong><?php echo $simbolo.number_format($venta[0]['totalventa'], 2, '.', ','); ?></strong></th>
  </tr>
  <tr align="center">
    <td><strong>TOTAL DE INGRESOS</strong></td>
    <td><strong><?php echo $simbolo.number_format($arqueo[0]['totalingresos'], 2, '.', ','); ?></strong></td>
  </tr>
  <tr align="center">
    <td><strong>ABONOS A CRÉDITOS</strong></td>
    <td><strong><?php echo $simbolo.number_format($arqueo[0]['totalabonos'], 2, '.', ','); ?></strong></td>
  </tr>
  <tr align="center">
    <td><strong>TOTAL COMPRAS</strong></td>
    <td><strong><?php echo $simbolo.number_format($compra[0]['totalcomprageneral'], 2, '.', ','); ?></strong></td>
  </tr>
  <tr align="center">
    <td><strong>TOTAL DE GASTOS (EGRESOS)</strong></td>
    <td><strong><?php echo $simbolo.number_format($arqueo[0]['totalegresos'], 2, '.', ','); ?></strong></td>
  </tr>
  <tr align="center">
    <td><strong>CARTERA DE CLIENTES</strong></td>
    <td><strong><?php echo $simbolo.number_format($cartera[0]['totaldebe']-$cartera[0]['totalpagado'], 2, '.', ','); ?></strong></td>
  </tr>
  <tr align="center">
    <td><strong>TOTAL DE IMPUESTOS DE VENTAS <?php echo $impuesto; ?> (<?php echo $valor; ?>%)</strong></td>
    <td><strong><?php echo $simbolo.number_format($venta[0]['totaliva'], 2, '.', ','); ?></strong></td>
  </tr>
  <tr align="center">
    <td><strong>BALANCE GENERAL</strong></td>
    <td><strong><?php echo $simbolo.number_format($balancegeneral, 2, '.', ','); ?></strong></td>
  </tr>
  <tr align="center">
    <td><strong>UTILIDAD NETA</strong></td>
    <td><strong><?php echo $simbolo.number_format($utilidadneta, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;

case 'GANANCIASVENTASXFECHAS':

$tra = new Login();
$reg = $tra->BuscarGananciasVentasxFechas(); 


$archivo = str_replace(" ", "_","LISTADO DE GANANCIAS EN VENTAS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE VENTA</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <th>TIPO DE PAGO</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>ESTADO</th>
        <th>DIAS VENC.</th>
        <th>FECHA VENCE</th>
        <th>FECHA PAGADO</th>
        <th>OBSERVACIONES</th>
        <?php } ?>
        <th>FECHA DE EMISIÓN</th>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DCTO</th>
        <th>PROPINA %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
        <th>GANANCIAS</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalSubtotal=0;
$TotalImpuesto=0;
$TotalDescuento=0;
$TotalPropina=0;
$TotalImporte=0;
$TotalGanancia=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
   
$TotalArticulos+=$reg[$i]['articulos'];
$TotalSubtotal+=$reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'];
$TotalImpuesto+=$reg[$i]['totaliva'];
$TotalDescuento+=$reg[$i]['totaldescuento'];
$TotalPropina+=$reg[$i]['totalpropina'];
$TotalImporte+=$reg[$i]['totalpago'];
$TotalGanancia+=$reg[$i]['totalpago']-$reg[$i]['totalcompra'];
?>
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['tipopago']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>

    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>

    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <td><?php echo $reg[$i]['observaciones'] == '' ? "***********" : $reg[$i]['observaciones']; ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td><?php echo $reg[$i]['articulos']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpropina'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['propinasugerida'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']-$reg[$i]['totalcompra'], 2, '.', ','); ?></td>
  </tr>
  <?php } } ?>
  <tr>
    <?php echo $documento == "EXCEL" ? '<td colspan="10"></td>' : '<td colspan="6"></td>'; ?>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalPropina, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalGanancia, 2, '.', ','); ?></strong></td>
  </tr>
</table>
<?php
break;
################################# MODULO DE VENTAS ################################






















################################## MODULO DE CREDITOS #################################
case 'CREDITOS':

$tra = new Login();
$reg = $tra->ListarCreditos(); 

$archivo = str_replace(" ", "_","LISTADO DE VENTAS A CREDITOS DE (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE VENTA</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>ESTADO</th>
        <th>DIAS VENC.</th>
        <th>FECHA VENCE</th>
        <th>FECHA PAGADO</th>
        <?php } ?>
        <th>FECHA EMISIÓN</th>
        <th>IMPORTE TOTAL</th>
        <th>TOTAL ABONO</th>
        <th>TOTAL DEBE</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  

$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]-$reg[$i]['creditopagado'];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>

    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>

    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?>

    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>

    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="8"></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'ABONOSCREDITOSXCAJAS':

$tra = new Login();
$reg = $tra->BuscarAbonosCreditosxCajas();
$formapago = limpiar($_GET["formapago"]); 

$archivo = str_replace(" ", "_","LISTADO DE ABONOS DE CREDITOS EN (CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." DE HORARIO: ".$reg[0]["hora_desde"]." - ".$reg[0]["hora_hasta"]." CONDICIÓN DE PAGO: ".$formapago." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")"); 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE VENTA</th>
        <th>Nº DE DOCUMENTO</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <th>FORMA DE ABONO</th>
        <th>FECHA DE ABONO</th>
        <th>MONTO ABONO</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalArticulos=0;
$TotalImporte=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
   
//$TotalArticulos+=$reg[$i]['articulos'];
$TotalImporte += $reg[$i]['montoabono'];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['documento'].": ".$reg[$i]['dnicliente']; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></td>
    <td><?php echo $reg[$i]['formaabono']; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaabono'])); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['montoabono'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="6"></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'CREDITOSXFECHAS':

$tra = new Login();
$reg = $tra->BuscarCreditosxFechas(); 

$status = limpiar($_GET["status"]); 

if(decrypt($status) == 1){ 

$archivo = str_replace(" ", "_","LISTADO DE CREDITOS EN GENERAL (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($status) == 2){ 

$archivo = str_replace(" ", "_","LISTADO DE CREDITOS PAGADOS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($status) == 3){ 

$archivo = str_replace(" ", "_","LISTADO DE CREDITOS PENDIENTES (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");  
}

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>Nº DE VENTA</th>
          <th>DESCRIPCIÓN DE CLIENTE</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>ESTADO</th>
          <th>DIAS VENC.</th>
          <th>FECHA VENCE</th>
          <th>FECHA PAGADO</th>
          <?php } ?>
          <th>FECHA EMISIÓN</th>
          <th>IMPORTE TOTAL</th>
          <th>TOTAL ABONO</th>
          <th>TOTAL DEBE</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  

$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]-$reg[$i]['creditopagado'];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="8"></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'CREDITOSXCLIENTES':

$tra = new Login();
$reg = $tra->BuscarCreditosxClientes(); 

$status = limpiar($_GET["status"]); 

if(decrypt($status) == 1){ 

$archivo = str_replace(" ", "_","LISTADO DE CREDITOS EN GENERAL DEL (CLIENTE: ".$reg[0]["dnicliente"].": ".$reg[0]["nomcliente"]." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($status) == 2){ 

$archivo = str_replace(" ", "_","LISTADO DE CREDITOS PAGADOS DEL (CLIENTE: ".$reg[0]["dnicliente"].": ".$reg[0]["nomcliente"]." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($status) == 3){ 

$archivo = str_replace(" ", "_","LISTADO DE CREDITOS PENDIENTES DEL (CLIENTE: ".$reg[0]["dnicliente"].": ".$reg[0]["nomcliente"]." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");
} 

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE VENTA</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>ESTADO</th>
        <th>DIAS VENC.</th>
        <th>FECHA VENCE</th>
        <th>FECHA PAGADO</th>
        <?php } ?>
        <th>FECHA EMISIÓN</th>
        <th>IMPORTE TOTAL</th>
        <th>TOTAL ABONO</th>
        <th>TOTAL DEBE</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]-$reg[$i]['creditopagado'];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>

    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="7"></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'DETALLESCREDITOSXCLIENTES':

$tra = new Login();
$reg = $tra->BuscarDetallesCreditosxClientes();  

$status = limpiar($_GET["status"]); 

if(decrypt($status) == 1){ 

$archivo = str_replace(" ", "_","LISTADO DE DETALLES CREDITOS EN GENERAL DEL (CLIENTE: ".$reg[0]["dnicliente"].": ".$reg[0]["nomcliente"]." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($status) == 2){ 

$archivo = str_replace(" ", "_","LISTADO DE DETALLES CREDITOS PAGADOS DEL (CLIENTE: ".$reg[0]["dnicliente"].": ".$reg[0]["nomcliente"]." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

} elseif(decrypt($status) == 3){ 

$archivo = str_replace(" ", "_","LISTADO DE DETALLES CREDITOS PENDIENTES DEL (CLIENTE: ".$reg[0]["dnicliente"].": ".$reg[0]["nomcliente"]." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");
}

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" class="text-center" cellpadding="0" cellspacing="0" bordercolor="#000000">
        <tr>
          <th>Nº</th>
          <th>Nº DE VENTA</th>
          <?php if ($documento == "EXCEL") { ?>
          <th>ESTADO</th>
          <th>DIAS VENC.</th>
          <th>FECHA VENCE</th>
          <th>FECHA PAGADO</th>
          <?php } ?>
          <th>FECHA EMISIÓN</th>
          <th>DETALLES DE PRODUCTOS</th>
          <th>IMPORTE TOTAL</th>
          <th>TOTAL ABONO</th>
          <th>TOTAL DEBE</th>
        </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
$a=1;
$TotalImporte=0;
$TotalAbono=0;
$TotalDebe=0;

for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 

$TotalImporte+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"];
$TotalAbono+=$reg[$i]['creditopagado'];
$TotalDebe+=$reg[$i]['totalpago']+$reg[$i]["totalpropina"]-$reg[$i]['creditopagado'];
?>
  <tr class="text-center" class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" ? "VENCIDA" : $reg[$i]["statusventa"]; ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php echo $reg[$i]['fechavencecredito'] == '0000-00-00' ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechavencecredito'])); ?>
    <td><?php echo $reg[$i]['statusventa'] == 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" || $reg[$i]['statusventa']!= 'PAGADA' && $reg[$i]['fechapagado']== "0000-00-00" ? "*****" :  date("d-m-Y",strtotime($reg[$i]['fechapagado'])); ?></td>
    <?php } ?>
           
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td style="text-align:left;font-weight:bold;font-size:10px;"><?php echo $reg[$i]['detalles_productos']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr class="text-center">
    <td colspan="8"></td>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalAbono, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDebe, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;
################################# MODULO DE CREDITOS ###################################
















################################# MODULO DE NOTAS DE CREDITOS ###################################
case 'NOTAS':

$tra = new Login();
$reg = $tra->ListarNotasCreditos(); 

$archivo = str_replace(" ", "_","LISTADO DE NOTAS DE CREDITOS DE (SUCURSAL ".$sucursal = ($reg == "" ? "" : $reg[0]['cuitsucursal']." ".$reg[0]['nomsucursal']).")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE CAJA</th>
        <th>Nº DE NOTA</th>
        <th>Nº DE DOCUMENTO</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <th>FECHA DE EMISIÓN</th>
        <th>MOTIVO DE NOTA</th>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo $caja = ($reg[$i]['codcaja'] == '0' ? "**********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['tipodocumento']." Nº: ".$reg[$i]['facturaventa']; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '' || $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechanota'])); ?></td>
    <td><?php echo $reg[$i]['observaciones']; ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr>
    <?php echo $documento == "EXCEL" ? '<td colspan="7"></td>' : '<td colspan="7"></td>'; ?>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'NOTASXCAJAS':

$tra = new Login();
$reg = $tra->BuscarNotasxCajas(); 

$archivo = str_replace(" ", "_","LISTADO DE NOTAS DE CREDITOS EN (CAJA Nº: ".$reg[0]["nrocaja"].": ".$reg[0]["nomcaja"]." DE HORARIO: ".$reg[0]["hora_desde"]." - ".$reg[0]["hora_hasta"]." DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE NOTA</th>
        <th>Nº DE DOCUMENTO</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <th>FECHA DE EMISIÓN</th>
        <th>MOTIVO DE NOTA</th>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['tipodocumento']." Nº: ".$reg[$i]['facturaventa']; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '' || $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechanota'])); ?></td>
    <td><?php echo $reg[$i]['observaciones']; ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr>
    <?php echo $documento == "EXCEL" ? '<td colspan="6"></td>' : '<td colspan="6"></td>'; ?>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'NOTASXFECHAS':

$tra = new Login();
$reg = $tra->BuscarNotasxFechas(); 

$archivo = str_replace(" ", "_","LISTADO DE NOTAS DE CREDITOS (DESDE ".date("d-m-Y", strtotime($_GET["desde"]))." HASTA ".date("d-m-Y", strtotime($_GET["hasta"]))." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE CAJA</th>
        <th>Nº DE NOTA</th>
        <th>Nº DE DOCUMENTO</th>
        <th>DESCRIPCIÓN DE CLIENTE</th>
        <th>FECHA DE EMISIÓN</th>
        <th>MOTIVO DE NOTA</th>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo $caja = ($reg[$i]['codcaja'] == '0' ? "**********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['tipodocumento']." Nº: ".$reg[$i]['facturaventa']; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '' || $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechanota'])); ?></td>
    <td><?php echo $reg[$i]['observaciones']; ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr>
    <?php echo $documento == "EXCEL" ? '<td colspan="7"></td>' : '<td colspan="7"></td>'; ?>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;

case 'NOTASXCLIENTE':

$tra = new Login();
$reg = $tra->BuscarNotasxClientes(); 

$archivo = str_replace(" ", "_","LISTADO DE NOTAS DE CREDITOS DEL (CLIENTE: ".$reg[0]["dnicliente"].": ".$reg[0]["nomcliente"]." Y SUCURSAL: ".$reg[0]['cuitsucursal'].": ".$reg[0]['nomsucursal'].")");

header("Content-Type: application/vnd.ms-$documento"); 
header("Expires: 0"); 
header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
header("content-disposition: attachment;filename=".$archivo.$extension);

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
      <tr>
        <th>Nº</th>
        <th>Nº DE CAJA</th>
        <th>Nº DE NOTA</th>
        <th>Nº DE DOCUMENTO</th>
        <th>FECHA DE EMISIÓN</th>
        <th>MOTIVO DE NOTA</th>
        <th>Nº DE ARTICULOS</th>
        <?php if ($documento == "EXCEL") { ?>
        <th>SUBTOTAL</th>
        <th><?php echo $impuesto; ?></th>
        <th>DESC %</th>
        <?php } ?>
        <th>IMPORTE TOTAL</th>
      </tr>
<?php 
if($reg==""){
echo "";      
} else {
  
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
  <tr class="even_row">
    <td><?php echo $a++; ?></td>
    <td><?php echo $caja = ($reg[$i]['codcaja'] == '0' ? "**********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['tipodocumento']." Nº: ".$reg[$i]['facturaventa']; ?></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechanota'])); ?></td>
    <td><?php echo $reg[$i]['observaciones']; ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <?php } ?>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
  </tr>
  <?php } ?>
  <tr>
    <?php echo $documento == "EXCEL" ? '<td colspan="6"></td>' : '<td colspan="6"></td>'; ?>
    <td><strong><?php echo number_format($TotalArticulos, 2, '.', ','); ?></strong></td>
    <?php if ($documento == "EXCEL") { ?>
    <td><strong><?php echo $simbolo.number_format($TotalSubtotal, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalImpuesto, 2, '.', ','); ?></strong></td>
    <td><strong><?php echo $simbolo.number_format($TotalDescuento, 2, '.', ','); ?></strong></td>
    <?php } ?>
    <td><strong><?php echo $simbolo.number_format($TotalImporte, 2, '.', ','); ?></strong></td>
  </tr>
  <?php } ?>
</table>
<?php
break;
################################# MODULO DE NOTAS DE CREDITOS ###################################
}
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