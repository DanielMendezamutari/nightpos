<?php
require_once("class/class.php");
if (isset($_SESSION['acceso'])) {
if ($_SESSION['acceso'] == "administradorG" || $_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="secretaria" || $_SESSION["acceso"]=="cajero" || $_SESSION["acceso"]=="mesero" || $_SESSION["acceso"]=="cocinero" || $_SESSION["acceso"]=="bar" || $_SESSION["acceso"]=="reposteria" || $_SESSION["acceso"]=="repartidor") {

$imp = new Login();
$imp = $imp->ImpuestosPorId();
$impuesto = ($imp == "" ? "Impuesto" : $imp[0]['nomimpuesto']);
$valor = ($imp == "" ? "0.00" : $imp[0]['valorimpuesto']);

$con = new Login();
$con = $con->ConfiguracionPorId();
    
$tra = new Login();
?>


<?php
############################# CARGAR USUARIOS ############################
if (isset($_GET['CargaUsuarios'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">

        <thead>
        <tr role="row">
            <th>N°</th>
            <th>N° de Documento</th>
            <th>Nombres y Apellidos</th>
            <th>Nº de Teléfono</th>
            <th>Usuario</th>
            <th>Nivel</th>
            <th>Status</th>
            <?php if ($_SESSION['acceso'] == "administradorG") { ?>
            <th>Sucursal</th>
            <?php } ?>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarUsuarios();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON USUARIOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['dni']; ?></td>
    <td><?php echo $reg[$i]['nombres']; ?></td>
    <td><?php echo $reg[$i]['telefono']; ?></td>
    <td><?php echo $reg[$i]['usuario']; ?></td>
    <td><?php echo $reg[$i]['nivel']; ?></td>
    <td><?php echo $status = ( $reg[$i]['status'] == 1 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ACTIVO</span>" : "<span class='badge badge-danger'><i class='fa fa-times'></i> INACTIVO</span>"); ?></td>
    <?php if ($_SESSION['acceso'] == "administradorG") { ?><td class="text-dark alert-link"><?php echo $reg[$i]['codsucursal'] == '' ? "*********" : $reg[$i]['cuitsucursal'].": ".$reg[$i]['nomsucursal']; ?></td><?php } ?>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerUsuario('<?php echo encrypt($reg[$i]["codigo"]); ?>')"><i class="fa fa-eye"></i></button>

    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalUser" data-backdrop="static" data-keyboard="false" onClick="UpdateUsuario('<?php echo $reg[$i]["codigo"]; ?>','<?php echo $reg[$i]["dni"]; ?>','<?php echo $reg[$i]["nombres"]; ?>','<?php echo $reg[$i]["sexo"]; ?>','<?php echo $reg[$i]["direccion"]; ?>','<?php echo $reg[$i]["telefono"]; ?>','<?php echo $reg[$i]["email"]; ?>','<?php echo $reg[$i]["usuario"]; ?>','<?php echo $reg[$i]["nivel"]; ?>','<?php echo $reg[$i]["status"]; ?>','<?php echo number_format($reg[$i]['comision'], 2, '.', ''); ?>','<?php echo $reg[$i]["codsucursal"] == '' ? "0" : encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>

    <?php if($reg[$i]["status"] == 1){ ?>
    <span class="btn btn-danger btn-rounded" style="cursor: pointer;" title="Inactivar Usuario" onClick="StatusUsuario('<?php echo encrypt($reg[$i]["codigo"]); ?>','<?php echo encrypt($reg[$i]["status"]); ?>','<?php echo encrypt("STATUSUSUARIOS") ?>')"><i class="fa fa-user-times"></i></span>
    <?php } else { ?>
    <span class="btn btn-warning btn-rounded text-white" style="cursor: pointer;" title="Activar Usuario" onClick="StatusUsuario('<?php echo encrypt($reg[$i]["codigo"]); ?>','<?php echo encrypt($reg[$i]["status"]); ?>','<?php echo encrypt("STATUSUSUARIOS") ?>')"><i class="fa fa-user-plus"></i></span>
    <?php } ?>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarUsuario('<?php echo encrypt($reg[$i]["codigo"]); ?>','<?php echo encrypt($reg[$i]["dni"]); ?>','<?php echo encrypt("USUARIOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR USUARIOS ############################
?>




<?php
############################# CARGAR PROVINCIAS ############################
if (isset($_GET['CargaProvincias'])) { 
?>
<div class="table-responsive"><table id="datatable" class="table table-striped table-bordered border display">

                        <thead>
                        <tr role="row">
                            <th>N°</th>
                            <th>Provincias</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarProvincias();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PROVINCIAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['provincia']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" onClick="UpdateProvincia('<?php echo $reg[$i]['id_provincia']; ?>','<?php echo $reg[$i]["provincia"]; ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarProvincia('<?php echo encrypt($reg[$i]["id_provincia"]); ?>','<?php echo encrypt("PROVINCIAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR PROVINCIAS ############################
?>


<?php
############################# CARGAR DEPARTAMENTOS ############################
if (isset($_GET['CargaDepartamentos'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">

                 <thead>
                 <tr role="row">
                    <th>N°</th>
                    <th>Provincia</th>
                    <th>Departamento</th>
                    <th>Acciones</th>
                 </tr>
                 </thead>
                 <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarDepartamentos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON DEPARTAMENTOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['provincia']; ?></td>
    <td><?php echo $reg[$i]['departamento']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" onClick="UpdateDepartamento('<?php echo $reg[$i]["id_departamento"]; ?>','<?php echo $reg[$i]["departamento"]; ?>','<?php echo $reg[$i]['id_provincia']; ?>','update')"><i class="fa fa-edit"></i></button>
                                 
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDepartamento('<?php echo encrypt($reg[$i]["id_departamento"]); ?>','<?php echo encrypt("DEPARTAMENTOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR DEPARTAMENTOS ############################
?>



<?php
############################# CARGAR TIPOS DE DOCUMENTOS ############################
if (isset($_GET['CargaDocumentos'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">

                    <thead>
                    <tr role="row">
                        <th>N°</th>
                        <th>Nombre</th>
                        <th>Descripción de Documento</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarDocumentos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON TIPOS DE DOCUMENTOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['documento']; ?></td>
    <td><?php echo $reg[$i]['descripcion']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDocumento" data-backdrop="static" data-keyboard="false" onClick="UpdateDocumento('<?php echo encrypt($reg[$i]["coddocumento"]); ?>','<?php echo $reg[$i]["documento"]; ?>','<?php echo $reg[$i]["descripcion"]; ?>','update')"><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarDocumento('<?php echo encrypt($reg[$i]["coddocumento"]); ?>','<?php echo encrypt("DOCUMENTOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR TIPOS DE DOCUMENTOS ############################
?>



<?php
############################# CARGAR TIPOS DE MONEDA ############################
if (isset($_GET['CargaMonedas'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">

             <thead>
             <tr role="row">
                <th>N°</th>
                <th>Nombre de Moneda</th>
                <th>Siglas</th>
                <th>Simbolo</th>
                <th>Acciones</th>
             </tr>
             </thead>
             <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarTipoMoneda();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON TIPOS DE MONEDAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['moneda']; ?></td>
    <td><?php echo $reg[$i]['siglas']; ?></td>
    <td><?php echo $reg[$i]['simbolo']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalTipoMoneda" data-backdrop="static" data-keyboard="false" onClick="UpdateTipoMoneda('<?php echo encrypt($reg[$i]["codmoneda"]); ?>','<?php echo $reg[$i]["moneda"]; ?>','<?php echo $reg[$i]["siglas"]; ?>','<?php echo $reg[$i]["simbolo"]; ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarTipoMoneda('<?php echo encrypt($reg[$i]["codmoneda"]); ?>','<?php echo encrypt("TIPOMONEDA"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR TIPOS DE MONEDA ############################
?>












<?php
############################# CARGAR TIPOS DE CAMBIO X SUCURSAL ############################
if (isset($_GET['BuscaTiposCambiosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Tipos de Cambio</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nuevo Cambio" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalTipoCambio" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxTipoCambio('<?php echo $codsucursal; ?>')"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("TIPOCAMBIO") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("TIPOCAMBIO") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("TIPOCAMBIO") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Descripción de Cambio</th>
                        <th>Monto de Cambio</th>
                        <th>Tipo Moneda</th>
                        <th>Fecha Ingreso</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarTipoCambio();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON TIPOS DE CAMBIOS DE MONEDA ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codcambio']; ?></td>
    <td><?php echo $reg[$i]['descripcioncambio']; ?></td>
    <td><?php echo $reg[$i]['montocambio']; ?></td>
    <td><abbr title="<?php echo "Siglas: ".$reg[$i]['siglas']; ?>"><?php echo $reg[$i]['moneda']; ?></abbr></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechacambio'])); ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalTipoCambio" data-backdrop="static" data-keyboard="false" onClick="UpdateTipoCambio('<?php echo encrypt($reg[$i]["codcambio"]); ?>','<?php echo $reg[$i]["descripcioncambio"]; ?>','<?php echo $reg[$i]["montocambio"]; ?>','<?php echo encrypt($reg[$i]["codmoneda"]); ?>','<?php echo date("Y-m-d",strtotime($reg[$i]['fechacambio'])); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarTipoCambio('<?php echo encrypt($reg[$i]["codcambio"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("TIPOCAMBIO"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR TIPOS DE CAMBIO X SUCURSAL ############################
?>

<?php
############################# CARGAR TIPOS DE CAMBIO ############################
if (isset($_GET['CargaCambios'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">

                 <thead>
                 <tr role="row">
                    <th>N°</th>
                    <th>Código</th>
                    <th>Descripción de Cambio</th>
                    <th>Monto de Cambio</th>
                    <th>Tipo Moneda</th>
                    <th>Fecha Ingreso</th>
                    <th>Acciones</th>
                 </tr>
                 </thead>
                 <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarTipoCambio();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON TIPOS DE CAMBIO DE MONEDA ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codcambio']; ?></td>
    <td><?php echo $reg[$i]['descripcioncambio']; ?></td>
    <td><?php echo $reg[$i]['montocambio']; ?></td>
    <td><abbr title="<?php echo "Siglas: ".$reg[$i]['siglas']; ?>"><?php echo $reg[$i]['moneda']; ?></abbr></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechacambio'])); ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalTipoCambio" data-backdrop="static" data-keyboard="false" onClick="UpdateTipoCambio('<?php echo encrypt($reg[$i]["codcambio"]); ?>','<?php echo $reg[$i]["descripcioncambio"]; ?>','<?php echo $reg[$i]["montocambio"]; ?>','<?php echo encrypt($reg[$i]["codmoneda"]); ?>','<?php echo date("Y-m-d",strtotime($reg[$i]['fechacambio'])); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarTipoCambio('<?php echo encrypt($reg[$i]["codcambio"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("TIPOCAMBIO"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button>  </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR TIPOS DE CAMBIO ############################
?>











<?php
############################# CARGAR IMPUESTOS X SUCURSAL ############################
if (isset($_GET['BuscaImpuestosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Impuestos</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nuevo Impuesto" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalImpuesto" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxImpuesto('<?php echo $codsucursal; ?>')"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("IMPUESTOS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("IMPUESTOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("IMPUESTOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Impuesto</th>
                        <th>Valor (%)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarImpuestos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON IMPUESTOS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codimpuesto']; ?></td>
    <td><?php echo $reg[$i]['nomimpuesto']; ?></td>
    <td><?php echo $reg[$i]['valorimpuesto']; ?></td>
    <td><?php echo $status = ($reg[$i]['statusimpuesto'] == 1 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ACTIVO</span>" : "<span class='badge badge-danger'><i class='fa fa-times'></i> INACTIVO</span>"); ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalImpuesto" data-backdrop="static" data-keyboard="false" onClick="UpdateImpuesto('<?php echo encrypt($reg[$i]["codimpuesto"]); ?>','<?php echo $reg[$i]["nomimpuesto"]; ?>','<?php echo $reg[$i]["valorimpuesto"]; ?>','<?php echo $reg[$i]["statusimpuesto"]; ?>','<?php echo date("d-m-Y",strtotime($reg[$i]['fechaimpuesto'])); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarImpuesto('<?php echo encrypt($reg[$i]["codimpuesto"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("IMPUESTOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR IMPUESTOS X SUCURSAL ############################
?>

<?php
############################# CARGAR IMPUESTOS ############################
if (isset($_GET['CargaImpuestos'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">

            <thead>
                <tr role="row">
                <th>N°</th>
                <th>Código</th>
                <th>Nombre de Impuesto</th>
                <th>Valor (%)</th>
                <th>Status</th>
                <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarImpuestos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON IMPUESTOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codimpuesto']; ?></td>
    <td><?php echo $reg[$i]['nomimpuesto']; ?></td>
    <td><?php echo $reg[$i]['valorimpuesto']; ?></td>
    <td><?php echo $status = ($reg[$i]['statusimpuesto'] == 1 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ACTIVO</span>" : "<span class='badge badge-dark'><i class='fa fa-times'></i> INACTIVO</span>"); ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalImpuesto" data-backdrop="static" data-keyboard="false" onClick="UpdateImpuesto('<?php echo encrypt($reg[$i]["codimpuesto"]); ?>','<?php echo $reg[$i]["nomimpuesto"]; ?>','<?php echo $reg[$i]["valorimpuesto"]; ?>','<?php echo $reg[$i]["statusimpuesto"]; ?>','<?php echo date("d-m-Y",strtotime($reg[$i]['fechaimpuesto'])); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button> 
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarImpuesto('<?php echo encrypt($reg[$i]["codimpuesto"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("IMPUESTOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>      
<?php 
} 
############################# CARGAR IMPUESTOS ############################
?>










<?php
############################# CARGAR SUCURSALES ############################
if (isset($_GET['CargaSucursales'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">

            <thead>
            <tr role="row">
                <th>N°</th>
                <th>Logo</th>
                <th>N° de Documento</th>
                <th>Razón Social</th>
                <th>Nº de Teléfono</th>
                <th>Email</th>
                <th>Encargado</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarSucursales();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON SUCURSALES ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php if (file_exists("fotos/sucursales/".$reg[$i]["cuitsucursal"].".png")){
    echo "<img src='fotos/sucursales/".$reg[$i]["cuitsucursal"].".png?' class='img-rounded' style='margin:0px;' width='50' height='40'>";
       }else{
    echo "<img src='fotos/img.png' class='img-rounded' style='margin:0px;' width='50' height='40'>";  
    } ?></td>
    <td><?php echo $reg[$i]['cuitsucursal']; ?></td>
    <td class="text-dark alert-link"><?php echo $reg[$i]['nomsucursal']; ?></td>
    <td><?php echo $reg[$i]['tlfsucursal']; ?></td>
    <td><?php echo $reg[$i]['correosucursal']; ?></td>
    <td><?php echo $reg[$i]['nomencargado']; ?></td>
    <td><?php echo $status = ( $reg[$i]['estado'] == 1 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ACTIVO</span>" : "<span class='badge badge-danger'><i class='fa fa-times'></i> INACTIVO</span>"); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false" onClick="VerSucursal('<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalSucursal" data-backdrop="static" data-keyboard="false" onClick="UpdateSucursal('<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo $reg[$i]["nrosucursal"]; ?>','<?php echo $documento = ($reg[$i]["documsucursal"] == 0 ? "" : $reg[$i]["documsucursal"]); ?>','<?php echo $reg[$i]["cuitsucursal"]; ?>','<?php echo $reg[$i]["nomsucursal"]; ?>','<?php echo $reg[$i]["codgiro"]; ?>','<?php echo $reg[$i]["girosucursal"]; ?>','<?php echo ($reg[$i]['id_provincia'] == '0' ? "" : $reg[$i]['id_provincia']); ?>','<?php echo $reg[$i]["direcsucursal"]; ?>','<?php echo $reg[$i]["correosucursal"]; ?>','<?php echo $reg[$i]["tlfsucursal"]; ?>','<?php echo $reg[$i]["nroactividadsucursal"]; ?>','<?php echo $reg[$i]["inicioticket"]; ?>','<?php echo $reg[$i]["inicioboleta"]; ?>','<?php echo $reg[$i]["iniciofactura"]; ?>','<?php echo $reg[$i]["inicionotacredito"]; ?>','<?php echo $reg[$i]["fechaautorsucursal"]; ?>','<?php echo $reg[$i]["llevacontabilidad"]; ?>','<?php echo $documento2 = ($reg[$i]["documencargado"] == 0 ? "" : $reg[$i]["documencargado"]); ?>','<?php echo $reg[$i]["dniencargado"]; ?>','<?php echo $reg[$i]["nomencargado"]; ?>','<?php echo $reg[$i]["tlfencargado"]; ?>','<?php echo number_format($reg[$i]["descsucursal"], 2, '.', ''); ?>','<?php echo number_format($reg[$i]["porcentajepropina"], 2, '.', ''); ?>','<?php echo $reg[$i]["codmoneda"]; ?>','<?php echo $reg[$i]["codmoneda2"]; ?>','<?php echo $reg[$i]["comanda_cocina"]; ?>','<?php echo $reg[$i]["comanda_bar"]; ?>','<?php echo $reg[$i]["comanda_reposteria"]; ?>','<?php echo preg_replace("/\r\n|\r|\n/",'\n',$reg[$i]['membrete']); ?>','update'); SelectDepartamento('<?php echo ($reg[$i]['id_provincia'] == '0' ? "" : $reg[$i]['id_provincia']); ?>','<?php echo $reg[$i]["id_departamento"]; ?>')"><i class="fa fa-edit"></i></button>

    <?php if($_SESSION['acceso'] == "administradorG" && $reg[$i]["estado"] == 1){ ?>
    <span class="btn btn-danger btn-rounded" style="cursor: pointer;" title="Inactivar Sucursal" onClick="StatusSucursal('<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["estado"]); ?>','<?php echo encrypt("STATUSSUCURSALES") ?>')"><i class="fa fa-times"></i></span>
    <?php } else if($_SESSION['acceso'] == "administradorG" && $reg[$i]["estado"] == 0){ ?>
    <span class="btn btn-warning btn-rounded" style="cursor: pointer;" title="Activar Sucursal" onClick="StatusSucursal('<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["estado"]); ?>','<?php echo encrypt("STATUSSUCURSALES") ?>')"><i class="fa fa-check"></i></span>
    <?php } ?>
    
    <?php if ($_SESSION['acceso'] == "administradorG") { ?><button type="button" class="btn btn-dark btn-rounded" onClick="EliminarSucursal('<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("SUCURSALES"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button><?php } ?> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR SUCURSALES ############################
?>
















<?php
############################# CARGAR HORARIOS X SUCURSAL ############################
if (isset($_GET['BuscaHorariosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Horarios</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nuevo Horario" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCategoria" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxHorario('<?php echo $codsucursal; ?>')"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("HORARIOS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("HORARIOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("HORARIOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Hora Desde</th>
                        <th>Hora Hasta</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarHorarios();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON HORARIOS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codhorario']; ?></td>
    <td><?php echo $reg[$i]['hora_desde']; ?></td>
    <td><?php echo $reg[$i]['hora_hasta']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalHorario" data-backdrop="static" data-keyboard="false" onClick="UpdateHorario('<?php echo encrypt($reg[$i]["codhorario"]); ?>','<?php echo $reg[$i]["hora_desde"]; ?>','<?php echo $reg[$i]["hora_hasta"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarHorario('<?php echo encrypt($reg[$i]["codhorario"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("HORARIOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR HORARIOS X SUCURSAL ############################
?>

<?php
############################# CARGAR HORARIOS ############################
if (isset($_GET['CargaHorarios'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">  
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Hora Desde</th>
                        <th>Hora Hasta</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarHorarios();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON HORARIOS DE CAJAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codhorario']; ?></td>
    <td><?php echo $reg[$i]['hora_desde']; ?></td>
    <td><?php echo $reg[$i]['hora_hasta']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalHorario" data-backdrop="static" data-keyboard="false" onClick="UpdateHorario('<?php echo encrypt($reg[$i]["codhorario"]); ?>','<?php echo $reg[$i]["hora_desde"]; ?>','<?php echo $reg[$i]["hora_hasta"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarHorario('<?php echo encrypt($reg[$i]["codhorario"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("HORARIOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR HORARIOS ############################
?>














<?php
############################# CARGAR CATEGORIAS X SUCURSAL ############################
if (isset($_GET['BuscaCategoriasxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Categorias</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nueva Categoria" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCategoria" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxCategoria('<?php echo $codsucursal; ?>')"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("CATEGORIAS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("CATEGORIAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("CATEGORIAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Categoria</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarCategorias();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON CATEGORIAS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codcategoria']; ?></td>
    <td><?php echo $reg[$i]['nomcategoria']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCategoria" data-backdrop="static" data-keyboard="false" onClick="UpdateCategoria('<?php echo encrypt($reg[$i]["codcategoria"]); ?>','<?php echo $reg[$i]["nomcategoria"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCategoria('<?php echo encrypt($reg[$i]["codcategoria"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("CATEGORIAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR CATEGORIAS X SUCURSAL ############################
?>

<?php
############################# CARGAR CATEGORIAS ############################
if (isset($_GET['CargaCategorias'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">  
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Categoria</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarCategorias();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON CATEGORIAS DE PRODUCTOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codcategoria']; ?></td>
    <td><?php echo $reg[$i]['nomcategoria']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCategoria" data-backdrop="static" data-keyboard="false" onClick="UpdateCategoria('<?php echo encrypt($reg[$i]["codcategoria"]); ?>','<?php echo $reg[$i]["nomcategoria"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCategoria('<?php echo encrypt($reg[$i]["codcategoria"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("CATEGORIAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR CATEGORIAS ############################
?>













<?php
############################# CARGAR MEDIDAS X SUCURSAL ############################
if (isset($_GET['BuscaMedidasxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Unidad Medidas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nueva Medida" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMedida" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxMedida('<?php echo $codsucursal; ?>')"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("MEDIDAS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("MEDIDAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("MEDIDAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Medida</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarMedidas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON MEDIDAS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codmedida']; ?></td>
    <td><?php echo $reg[$i]['nommedida']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMedida" data-backdrop="static" data-keyboard="false" onClick="UpdateMedida('<?php echo encrypt($reg[$i]["codmedida"]); ?>','<?php echo $reg[$i]["nommedida"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarMedida('<?php echo encrypt($reg[$i]["codmedida"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("MEDIDAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR MEDIDAS X SUCURSAL ############################
?>

<?php
############################# CARGAR MEDIDAS ############################
if (isset($_GET['CargaMedidas'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                         <thead>
                         <tr role="row">
                            <th>N°</th>
                            <th>Código</th>
                            <th>Nombre de Medida</th>
                            <th>Acciones</th>
                         </tr>
                         </thead>
                         <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarMedidas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON MEDIDAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codmedida']; ?></td>
    <td><?php echo $reg[$i]['nommedida']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMedida" data-backdrop="static" data-keyboard="false" onClick="UpdateMedida('<?php echo encrypt($reg[$i]["codmedida"]); ?>','<?php echo $reg[$i]["nommedida"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarMedida('<?php echo encrypt($reg[$i]["codmedida"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("MEDIDAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR UNIDADES ############################
?>














<?php
############################# CARGAR SALSAS X SUCURSAL ############################
if (isset($_GET['BuscaSalsasxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Salsas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nueva Salsa" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalSalsa" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxSalsa('<?php echo $codsucursal; ?>')"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("SALSAS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("SALSAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("SALSAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Foto</th>
                        <th>Código</th>
                        <th>Nombre de Salsa</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarSalsas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON SALSAS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php if (file_exists("fotos/salsas/".$reg[$i]["codsalsa"].".jpg")){
    echo "<img src='fotos/salsas/".$reg[$i]["codsalsa"].".jpg?' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
       }else{
    echo "<img src='fotos/img.png' class='rounded-circle' style='margin:0px;' width='50' height='40'>";  
    } ?></td>
    <td><?php echo $reg[$i]['codsalsa']; ?></td>
    <td><?php echo $reg[$i]['nomsalsa']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalSalsa" data-backdrop="static" data-keyboard="false" onClick="UpdateSalsa('<?php echo encrypt($reg[$i]["codsalsa"]); ?>','<?php echo $reg[$i]["nomsalsa"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarSalsa('<?php echo encrypt($reg[$i]["codsalsa"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("SALSAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR SALSAS X SUCURSAL ############################
?>

<?php
############################# CARGAR SALSAS ############################
if (isset($_GET['CargaSalsas'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Foto</th>
                        <th>Código</th>
                        <th>Nombre de Salsa</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarSalsas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON SALSAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php if (file_exists("fotos/salsas/".$reg[$i]["codsalsa"].".jpg")){
    echo "<img src='fotos/salsas/".$reg[$i]["codsalsa"].".jpg?' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
       }else{
    echo "<img src='fotos/img.png' class='rounded-circle' style='margin:0px;' width='50' height='40'>";  
    } ?></td>
    <td><?php echo $reg[$i]['codsalsa']; ?></td>
    <td><?php echo $reg[$i]['nomsalsa']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalSalsa" data-backdrop="static" data-keyboard="false" onClick="UpdateSalsa('<?php echo encrypt($reg[$i]["codsalsa"]); ?>','<?php echo $reg[$i]["nomsalsa"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarSalsa('<?php echo encrypt($reg[$i]["codsalsa"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("SALSAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR SALSAS ############################
?>















<?php
############################# CARGAR SALAS X SUCURSAL ############################
if (isset($_GET['BuscaSalasxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Salas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nueva Salsa" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalSala" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxSala('<?php echo $codsucursal; ?>')"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("SALAS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("SALAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("SALAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Sala</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarSalas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON SALAS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codsala']; ?></td>
    <td><?php echo $reg[$i]['nomsala']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalSala" data-backdrop="static" data-keyboard="false" onClick="UpdateSala('<?php echo encrypt($reg[$i]["codsala"]); ?>','<?php echo $reg[$i]["nomsala"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarSala('<?php echo encrypt($reg[$i]["codsala"]); ?>','<?php echo "1"; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("SALAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR SALAS X SUCURSAL ############################
?>

<?php
############################# CARGAR SALAS ############################
if (isset($_GET['CargaSalas'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
            
            <thead>
                <tr role="row">
                <th>N°</th>
                <th>Código</th>
                <th>Nombre de Sala</th>
                <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarSalas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON SALAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codsala']; ?></td>
    <td><?php echo $reg[$i]['nomsala']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalSala" data-backdrop="static" data-keyboard="false" onClick="UpdateSala('<?php echo encrypt($reg[$i]["codsala"]); ?>','<?php echo $reg[$i]["nomsala"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarSala('<?php echo encrypt($reg[$i]["codsala"]); ?>','<?php echo "2"; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("SALAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR SALAS ############################
?>















<?php
############################# CARGAR MESAS X SUCURSAL ############################
if (isset($_GET['BuscaMesasxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Mesas</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nueva Salsa" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMesa" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxMesa('<?php echo $codsucursal; ?>'); CargaSalas('<?php echo $codsucursal; ?>');"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("MESAS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("MESAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("MESAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Sala</th>
                        <th>Nombre de Mesa</th>
                        <th>Nº de Puestos</th>
                        <th>Status</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarMesas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON MESAS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codmesa']; ?></td>
    <td><?php echo $reg[$i]['nomsala']; ?></td>
    <td><?php echo $reg[$i]['nommesa']; ?></td>
    <td><?php echo $reg[$i]['puestos']; ?></td>
    <td><?php echo $status = ( $reg[$i]['statusmesa'] == 0 ? "<span class='badge badge-success'><i class='fa fa-check'></i> DISPONIBLE</span>" : "<span class='badge badge-dark'><i class='fa fa-times'></i> RESERVADA</span>"); ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMesa" data-backdrop="static" data-keyboard="false" onClick="UpdateMesa('<?php echo encrypt($reg[$i]["codmesa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codsala"]); ?>','<?php echo $reg[$i]["nommesa"]; ?>','<?php echo $reg[$i]["puestos"]; ?>','update'); SelectSala('<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codsala"]); ?>')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarMesa('<?php echo encrypt($reg[$i]["codmesa"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("MESAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR MESAS X SUCURSAL ############################
?>

<?php
############################# CARGAR MESAS ############################
if (isset($_GET['CargaMesas'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                 <thead>
                 <tr role="row">
                    <th>N°</th>
                    <th>Código</th>
                    <th>Nombre de Sala</th>
                    <th>Nombre de Mesa</th>
                    <th>Nº de Puestos</th>
                    <th>Status</th>
                    <th>Acciones</th>
                 </tr>
                 </thead>
                 <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarMesas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON MESAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codmesa']; ?></td>
    <td><?php echo $reg[$i]['nomsala']; ?></td>
    <td><?php echo $reg[$i]['nommesa']; ?></td>
    <td><?php echo $reg[$i]['puestos']; ?></td>
    <td><?php echo $status = ( $reg[$i]['statusmesa'] == 0 ? "<span class='badge badge-success'><i class='fa fa-check'></i> DISPONIBLE</span>" : "<span class='badge badge-dark'><i class='fa fa-times'></i> RESERVADA</span>"); ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMesa" data-backdrop="static" data-keyboard="false" onClick="UpdateMesa('<?php echo encrypt($reg[$i]["codmesa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codsala"]); ?>','<?php echo $reg[$i]["nommesa"]; ?>','<?php echo $reg[$i]["puestos"]; ?>','update'); SelectSala('<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codsala"]); ?>')"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarMesa('<?php echo encrypt($reg[$i]["codmesa"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("MESAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button>

        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR MESAS ############################
?>


















<?php
############################# CARGAR CLIENTES X SUCURSAL ############################
if (isset($_GET['BuscaClientesxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Clientes</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn waves-effect waves-light btn-light" data-placement="left" title="Carga Masiva" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCargaMasiva" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxMasivaCliente('<?php echo $codsucursal; ?>')"><span class="fa fa-cloud-upload text-dark"></span> Cargar</button>
                    
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nuevo Cliente" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCliente" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxCliente('<?php echo $codsucursal; ?>')"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("CLIENTES") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("CLIENTES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("CLIENTES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Tipo de Cliente</th>
                        <th>Nº de Documento</th>
                        <th>Nombres</th>
                        <th>Nº de Teléfono</th>
                        <th>Correo Electrónico</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarClientes();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON CLIENTES ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td class="text-dark alert-link"><?php echo $reg[$i]['tipocliente']; ?></td>
    <td><?php echo $documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento'])." ".$reg[$i]['dnicliente']; ?></td>
    <td><?php echo $cliente = ($reg[$i]['tipocliente'] == 'NATURAL' ? $reg[$i]['nomcliente'] : $reg[$i]['razoncliente']); ?></td>
    <td><?php echo $reg[$i]['tlfcliente'] == '' ? "***********" : $reg[$i]['tlfcliente']; ?></td>
    <td><?php echo $reg[$i]['emailcliente'] == '' ? "***********" : $reg[$i]['emailcliente']; ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerCliente('<?php echo encrypt($reg[$i]["codcliente"]); ?>')"><i class="fa fa-eye"></i></button>
    
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCliente" data-backdrop="static" data-keyboard="false" onClick="UpdateCliente('<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo $reg[$i]["tipocliente"]; ?>','<?php echo $documento = ($reg[$i]["documcliente"] == 0 ? "" : $reg[$i]["documcliente"]); ?>','<?php echo $reg[$i]["dnicliente"]; ?>','<?php echo $reg[$i]["nomcliente"]; ?>','<?php echo $reg[$i]["razoncliente"]; ?>','<?php echo $reg[$i]["girocliente"]; ?>','<?php echo $reg[$i]["tlfcliente"]; ?>','<?php echo $reg[$i]["id_provincia"]; ?>','<?php echo $reg[$i]["direccliente"]; ?>','<?php echo $reg[$i]["emailcliente"]; ?>','<?php echo number_format($reg[$i]["limitecredito"], 2, '.', ''); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update'); SelectDepartamento('<?php echo $reg[$i]['id_provincia']; ?>','<?php echo $reg[$i]["id_departamento"]; ?>'); CargaTipoCliente('<?php echo $reg[$i]["tipocliente"]; ?>');"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCliente('<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt("CLIENTES") ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR CLIENTES X SUCURSAL ############################
?>

<?php
############################# CARGAR CLIENTES ############################
if (isset($_GET['CargaClientes']) && isset($_GET['bclientes'])) {

$criterio = limpiar($_GET['bclientes']);   
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Tipo de Cliente</th>
                        <th>Nº de Documento</th>
                        <th>Nombres</th>
                        <th>Nº de Teléfono</th>
                        <th>Correo Electrónico</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
if($criterio==""){
    
  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE VALOR PARA TU CRITERIO DE BÚSQUEDA </center>";
  echo "</div>";
  exit;    

} else {

$reg = $tra->BusquedaClientes();
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td class="text-dark alert-link"><?php echo $reg[$i]['tipocliente']; ?></td>
    <td><?php echo $documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento'])." ".$reg[$i]['dnicliente']; ?></td>
    <td><?php echo $cliente = ($reg[$i]['tipocliente'] == 'JURIDICO' ? $reg[$i]['razoncliente'] : $reg[$i]['nomcliente']); ?></td>
    <td><?php echo $reg[$i]['tlfcliente'] == '' ? "***********" : $reg[$i]['tlfcliente']; ?></td>
    <td><?php echo $reg[$i]['emailcliente'] == '' ? "***********" : $reg[$i]['emailcliente']; ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerCliente('<?php echo encrypt($reg[$i]["codcliente"]); ?>')"><i class="fa fa-eye"></i></button>
    
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCliente" data-backdrop="static" data-keyboard="false" onClick="UpdateCliente('<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo $reg[$i]["tipocliente"]; ?>','<?php echo $documento = ($reg[$i]["documcliente"] == 0 ? "" : $reg[$i]["documcliente"]); ?>','<?php echo $reg[$i]["dnicliente"]; ?>','<?php echo $reg[$i]["nomcliente"]; ?>','<?php echo $reg[$i]["razoncliente"]; ?>','<?php echo $reg[$i]["girocliente"]; ?>','<?php echo $reg[$i]["tlfcliente"]; ?>','<?php echo $reg[$i]["id_provincia"]; ?>','<?php echo $reg[$i]["direccliente"]; ?>','<?php echo $reg[$i]["emailcliente"]; ?>','<?php echo number_format($reg[$i]["limitecredito"], 2, '.', ''); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update'); SelectDepartamento('<?php echo $reg[$i]['id_provincia']; ?>','<?php echo $reg[$i]["id_departamento"]; ?>'); CargaTipoCliente('<?php echo $reg[$i]["tipocliente"]; ?>');"><i class="fa fa-edit"></i></button>
    
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCliente('<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt("CLIENTES") ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button>    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR CLIENTES ############################
?>















<?php
############################# CARGAR PROVEEDORES X SUCURSAL ############################
if (isset($_GET['BuscaProveedoresxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Control de Proveedores</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
                <div class="btn-group m-b-20">
                <button type="button" class="btn waves-effect waves-light btn-light" data-placement="left" title="Carga Masiva" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCargaMasiva" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxMasivaProveedor('<?php echo $codsucursal; ?>')"><span class="fa fa-cloud-upload text-dark"></span> Cargar</button>
                    
                <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nuevo Proveedor" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalProveedor" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxProveedor('<?php echo $codsucursal; ?>')"><i class="fa fa-plus"></i> Nuevo</button>

                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("PROVEEDORES") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("PROVEEDORES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("PROVEEDORES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
                </div>
            </div>
        </div>

        <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Nº de Documento</th>
                        <th>Nombres de Proveedor</th>
                        <th>Correo Electrónico</th>
                        <th>Nº de Teléfono</th>
                        <th>Vendedor</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarProveedores();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PROVEEDORES ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $documento = ($reg[$i]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[$i]['documento'])." ".$reg[$i]['cuitproveedor']; ?></td>
    <td><?php echo $reg[$i]['nomproveedor']; ?></td>
    <td><?php echo $reg[$i]['emailproveedor'] == '' ? "*********" : $reg[$i]['emailproveedor']; ?></td>
    <td><?php echo $reg[$i]['tlfproveedor'] == '' ? "*********" : $reg[$i]['tlfproveedor']; ?></td>
    <td><?php echo $reg[$i]['vendedor'] == '' ? "*********" : $reg[$i]['vendedor']; ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerProveedor('<?php echo encrypt($reg[$i]["codproveedor"]); ?>')"><i class="fa fa-eye"></i></button>

    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalProveedor" data-backdrop="static" data-keyboard="false" onClick="UpdateProveedor('<?php echo encrypt($reg[$i]["codproveedor"]); ?>','<?php echo $documento = ($reg[$i]["documproveedor"] == 0 ? "" : $reg[$i]["documproveedor"]); ?>','<?php echo $reg[$i]["cuitproveedor"]; ?>','<?php echo $reg[$i]["nomproveedor"]; ?>','<?php echo $reg[$i]["tlfproveedor"]; ?>','<?php echo ($reg[$i]['id_provincia'] == '0' ? "" : $reg[$i]['id_provincia']); ?>','<?php echo $reg[$i]["direcproveedor"]; ?>','<?php echo $reg[$i]["emailproveedor"]; ?>','<?php echo $reg[$i]["vendedor"]; ?>','<?php echo $reg[$i]["tlfvendedor"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update'); SelectDepartamento('<?php echo $reg[$i]['id_provincia']; ?>','<?php echo $reg[$i]["id_departamento"]; ?>')"><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarProveedor('<?php echo encrypt($reg[$i]["codproveedor"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("PROVEEDORES"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR PROVEEDORES X SUCURSAL ############################
?>

<?php
############################# CARGAR PROVEEDORES ############################
if (isset($_GET['CargaProveedores'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Nº de Documento</th>
                        <th>Nombres de Proveedor</th>
                        <th>Correo Electrónico</th>
                        <th>Nº de Teléfono</th>
                        <th>Vendedor</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarProveedores();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PROVEEDORES ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $documento = ($reg[$i]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[$i]['documento'])." ".$reg[$i]['cuitproveedor']; ?></td>
    <td><?php echo $reg[$i]['nomproveedor']; ?></td>
    <td><?php echo $reg[$i]['emailproveedor'] == '' ? "*********" : $reg[$i]['emailproveedor']; ?></td>
    <td><?php echo $reg[$i]['tlfproveedor'] == '' ? "*********" : $reg[$i]['tlfproveedor']; ?></td>
    <td><?php echo $reg[$i]['vendedor'] == '' ? "*********" : $reg[$i]['vendedor']; ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerProveedor('<?php echo encrypt($reg[$i]["codproveedor"]); ?>')"><i class="fa fa-eye"></i></button>

    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalProveedor" data-backdrop="static" data-keyboard="false" onClick="UpdateProveedor('<?php echo encrypt($reg[$i]["codproveedor"]); ?>','<?php echo $documento = ($reg[$i]["documproveedor"] == 0 ? "" : $reg[$i]["documproveedor"]); ?>','<?php echo $reg[$i]["cuitproveedor"]; ?>','<?php echo $reg[$i]["nomproveedor"]; ?>','<?php echo $reg[$i]["tlfproveedor"]; ?>','<?php echo ($reg[$i]['id_provincia'] == '0' ? "" : $reg[$i]['id_provincia']); ?>','<?php echo $reg[$i]["direcproveedor"]; ?>','<?php echo $reg[$i]["emailproveedor"]; ?>','<?php echo $reg[$i]["vendedor"]; ?>','<?php echo $reg[$i]["tlfvendedor"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update'); SelectDepartamento('<?php echo $reg[$i]['id_provincia']; ?>','<?php echo $reg[$i]["id_departamento"]; ?>')"><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarProveedor('<?php echo encrypt($reg[$i]["codproveedor"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("PROVEEDORES"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button></td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR PROVEEDORES ############################
?>














<?php
############################# CARGAR INGREDIENTES X SUCURSAL ############################
if (isset($_GET['BuscaIngredientesxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Ingredientes</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

      <div class="row">

            <div class="col-md-12">
              <div class="btn-group m-b-20">
              <button type="button" class="btn waves-effect waves-light btn-light" data-placement="left" title="Carga Masiva" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCargaMasiva" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxMasivaIngrediente('<?php echo $codsucursal; ?>')"><span class="fa fa-cloud-upload"></span> Cargar</font></button>

              <div class="btn-group">
                <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i> Pdf</button>
                  <div class="dropdown-menu dropdown-menu-left" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(164px, 35px, 0px);">
                                
                    <a class="dropdown-item" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("INGREDIENTES") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Listado General</a>

                    <a class="dropdown-item" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("INGREDIENTESMINIMO") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Stock Minimo</a>

                    <a class="dropdown-item" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("INGREDIENTESMAXIMO") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Stock Máximo</a>

                  </div>
              </div> 

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("INGREDIENTES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("INGREDIENTES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("INGREDIENTESCSV") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> CSV</a>

              </div>
            </div>
          </div>

      <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Ingrediente</th>
                        <th>Unidad Medida</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>P.V EXT</th>
                        <th>Stock</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarIngredientes();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON INGREDIENTES ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
$moneda = (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ',')); 
?>
    <?php echo $tr = ($reg[$i]['cantingrediente'] <= $reg[$i]['stockminimo'] ? '<tr role="row" class="odd" style="border-left: 2px solid #ff5050 !important; background: #fce3e3;">' : '<tr role="row" class="odd">'); ?>
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codingrediente']; ?></td>
    <td><?php echo $reg[$i]['nomingrediente']; ?><small class="text-danger alert-link"><?php echo $tr = ($reg[$i]['cantingrediente'] <= $reg[$i]['stockminimo'] ? '<br>STOCK MINIMO' : ''); ?></small></td>
    <td><?php echo $reg[$i]['nommedida']; ?></td>
    <td><?php echo $preciocompra = ($_SESSION['acceso'] == "cajero" || $_SESSION["acceso"]=="cocinero" ? "**********" : $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ',')); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['moneda2'] == '' ? "*****" : "<strong>".$reg[$i]['simbolo2']."</strong> ".$moneda; ?></td>
    <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['descingrediente'], 2, '.', ','); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerIngrediente('<?php echo encrypt($reg[$i]["codingrediente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateIngrediente('<?php echo encrypt($reg[$i]["codingrediente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarIngrediente('<?php echo encrypt($reg[$i]["codingrediente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("INGREDIENTES"); ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button>
    </td>
    </tr>
    <?php } } ?>
    </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR INGREDIENTES X SUCURSAL ############################
?>


<?php
############################# CARGAR INGREDIENTES ############################
if (isset($_GET['CargaIngredientes'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Ingrediente</th>
                        <th>Unidad Medida</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>P.V EXT</th>
                        <th>Stock</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarIngredientes();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON INGREDIENTES ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
$moneda = (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ',')); 
?>
    <?php echo $tr = ($reg[$i]['cantingrediente'] <= $reg[$i]['stockminimo'] ? '<tr role="row" class="odd" style="border-left: 2px solid #ff5050 !important; background: #fce3e3;">' : '<tr role="row" class="odd">'); ?>
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codingrediente']; ?></td>
    <td><?php echo $reg[$i]['nomingrediente']; ?><small class="text-danger alert-link"><?php echo $tr = ($reg[$i]['cantingrediente'] <= $reg[$i]['stockminimo'] ? '<br>STOCK MINIMO' : ''); ?></small></td>
    <td><?php echo $reg[$i]['nommedida']; ?></td>
    <td><?php echo $preciocompra = ($_SESSION['acceso'] == "cajero" || $_SESSION["acceso"]=="cocinero" ? "**********" : $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ',')); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['moneda2'] == '' ? "*****" : "<strong>".$reg[$i]['simbolo2']."</strong> ".$moneda; ?></td>
    <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['descingrediente'], 2, '.', ','); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerIngrediente('<?php echo encrypt($reg[$i]["codingrediente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <?php if ($_SESSION['acceso'] == "cocinero") {?>
    <button type="button" class="btn btn-danger btn-rounded" data-placement="left" title="Sumar stock" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalStock" data-backdrop="static" data-keyboard="false" onClick="SumarIngrediente('<?php echo $reg[$i]["idingrediente"]; ?>','<?php echo $reg[$i]["codingrediente"]; ?>','<?php echo $reg[$i]["nomingrediente"]; ?>')"><i class="fa fa-refresh"></i></button>
    <?php } ?>

    <?php if ($_SESSION['acceso'] == "administradorS" || $_SESSION["acceso"]=="secretaria") { ?>
    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateIngrediente('<?php echo encrypt($reg[$i]["codingrediente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarIngrediente('<?php echo encrypt($reg[$i]["codingrediente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("INGREDIENTES"); ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button>
    <?php } ?></td>
    </tr>
    <?php } } ?>
    </tbody>
    </table></div>
<?php
} 
############################# CARGAR INGREDIENTES ############################
?>










<?php
############################# CARGAR KARDEX VALORIZADO INGREDIENTES X SUCURSAL ############################
if (isset($_GET['BuscaValorizadoIngredientesxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);
$monedap = new Login();
$cambio = $monedap->MonedaProductoId(); 

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Kardex Valorizado</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">

            <div class="col-md-12">
              <div class="btn-group m-b-20">
                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("KARDEXVALORIZADOINGREDIENTES") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("KARDEXVALORIZADOINGREDIENTES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("KARDEXVALORIZADOINGREDIENTES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

              </div>
            </div>
          </div>

      <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                         <thead>
                         <tr role="row">
                            <th>N°</th>
                            <th>Código</th>
                            <th>Nombre de Ingrediente</th>
                            <th>Unidad Medida</th>
                            <th>Precio Compra</th>
                            <th>Precio Venta</th>
                            <th>Stock</th>
                            <th><?php echo $impuesto; ?></th>
                            <th>Desc %</th>
                            <th>Total Venta</th>
                            <th>Total Compra</th>
                            <th>Ganancias</th>
                         </tr>
                         </thead>
                         <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarIngredientes();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON INGREDIENTES ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

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
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codingrediente']; ?></td>
    <td><?php echo $reg[$i]['nomingrediente']; ?></td>
    <td><?php echo $reg[$i]['nommedida']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['descingrediente'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR KARDEX VALORIZADO INGREDIENTES X SUCURSAL ############################
?>

<?php
############################# CARGAR KARDEX VALORIZADO DE INGREDIENTES ############################
if (isset($_GET['CargaKardexValorizadoIngredientes'])) { 

$monedap = new Login();
$cambio = $monedap->MonedaProductoId(); 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                 <thead>
                 <tr role="row">
                    <th>N°</th>
                    <th>Código</th>
                    <th>Nombre de Ingrediente</th>
                    <th>Unidad Medida</th>
                    <th>Precio Compra</th>
                    <th>Precio Venta</th>
                    <th>Stock</th>
                    <th><?php echo $impuesto; ?></th>
                    <th>Desc %</th>
                    <th>Total Venta</th>
                    <th>Total Compra</th>
                    <th>Ganancias</th>
                 </tr>
                 </thead>
                 <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarIngredientes();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON INGREDIENTES ACTUALMENTE </center>";
    echo "</div>";    

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
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codingrediente']; ?></td>
    <td><?php echo $reg[$i]['nomingrediente']; ?></td>
    <td><?php echo $reg[$i]['nommedida']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['cantingrediente'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivaingrediente'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['descingrediente'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR KARDEX VALORIZADO DE INGREDIENTES ############################
?>












<?php
############################# CARGAR PRODUCTOS X SUCURSAL ############################
if (isset($_GET['BuscaProductosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Productos</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">

            <div class="col-md-12">
              <div class="btn-group m-b-20">
              <button type="button" class="btn waves-effect waves-light btn-light" data-placement="left" title="Carga Masiva" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCargaMasiva" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxMasivaProducto('<?php echo $codsucursal; ?>')"><span class="fa fa-cloud-upload"></span> Cargar</font></button>

                <div class="btn-group">
                <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i> Pdf</button>
                    <div class="dropdown-menu dropdown-menu-left" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(164px, 35px, 0px);">
                                
                    <a class="dropdown-item" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("PRODUCTOS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Listado General</a>

                    <a class="dropdown-item" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("PRODUCTOSMINIMO") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Stock Minimo</a>

                    <a class="dropdown-item" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("PRODUCTOSMAXIMO") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Stock Máximo</a>
                   </div>
                </div> 

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("PRODUCTOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("PRODUCTOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("PRODUCTOSCSV") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> CSV</a>

              </div>
            </div>
          </div>

      <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                          <thead>
                          <tr role="row">
                          <th>N°</th>
                          <th>Img</th>
                          <th>Código</th>
                          <th>Nombre de Producto</th>
                          <th>Categoria</th>
                          <th>Precio Compra</th>
                          <th>Precio Venta</th>
                          <th>P.V EXT</th>
                          <th>Stock</th>
                          <th><?php echo $impuesto; ?> </th>
                          <th>Desc %</th>
                          <th>Acciones</th>
                          </tr>
                          </thead>
                          <tbody class="BusquedaRapida">
<?php
$reg = $tra->ListarProductos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON INGREDIENTES ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
$simbolo2 = ($reg[$i]['simbolo2'] == "" ? "" : "<strong>".$reg[$i]['simbolo2']."</strong>");
$moneda = (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ','));
?>
    <?php echo $tr = ($reg[$i]['existencia'] <= $reg[$i]['stockminimo'] ? '<tr role="row" class="odd" style="border-left: 2px solid #ff5050 !important; background: #fce3e3;">' : '<tr role="row" class="odd">'); ?>
    <td><?php echo $a++; ?></td>
    <td>
    <?php
    if (file_exists("fotos/productos/".$reg[$i]["codproducto"].".jpg")){
        echo "<img src='fotos/productos/".$reg[$i]["codproducto"].".jpg?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else if (file_exists("fotos/productos/".$reg[$i]["codproducto"].".jpeg")){
        echo "<img src='fotos/productos/".$reg[$i]["codproducto"].".jpeg?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else if (file_exists("fotos/productos/".$reg[$i]["codproducto"].".png")){   
        echo "<img src='fotos/productos/".$reg[$i]["codproducto"].".png?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else {
        echo "<img src='fotos/img.png' class='rounded-circle' style='margin:0px;' width='50' height='40'>";  
    } 
    ?>  
    </td>
    <td><?php echo $reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?><small class="text-danger alert-link"><?php echo $tr = ($reg[$i]['existencia'] <= $reg[$i]['stockminimo'] ? '<br>STOCK MINIMO' : ''); ?></small></td>
    <td><?php echo $reg[$i]['nomcategoria']; ?></td>
    <td><?php echo $preciocompra = ($_SESSION['acceso'] == "cajero" || $_SESSION["acceso"]=="cocinero" ? "**********" : $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ',')); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo2.$moneda; ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerProducto('<?php echo encrypt($reg[$i]["codproducto"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateProducto('<?php echo encrypt($reg[$i]["codproducto"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-danger btn-rounded" onClick="AgregaIngrediente('<?php echo encrypt($reg[$i]["codproducto"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Agregar" ><i class="fa fa-cart-arrow-down"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarProducto('<?php echo encrypt($reg[$i]["codproducto"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("PRODUCTOS"); ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button>
    </td>
                    </tr>
                    <?php } } ?>
                  </tbody>
                </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR PRODUCTOS X SUCURSAL ############################
?>


<?php
############################# CARGAR PRODUCTOS ############################
if (isset($_GET['CargaProductos'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                    <thead>
                    <tr role="row">
                        <th>N°</th>
                        <th>Img</th>
                        <th>Código</th>
                        <th>Nombre de Producto</th>
                        <th>Categoria</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>P.V EXT</th>
                        <th>Stock</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <?php echo $perfil = ($_SESSION['acceso'] == "administradorS" || $_SESSION["acceso"]=="secretaria" ? "<th>Acciones</th>" : "<th><i class='mdi mdi-drag-horizontal'></i></th>"); ?>
                    </tr>
                    </thead>
                    <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarProductos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PRODUCTOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");
$moneda = (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ',')); 
?>
    
    <?php echo $tr = ($reg[$i]['existencia'] <= $reg[$i]['stockminimo'] ? '<tr role="row" class="odd" style="border-left: 2px solid #ff5050 !important; background: #fce3e3;">' : '<tr role="row" class="odd">'); ?>
    <td><?php echo $a++; ?></td>
    <td>
    <?php
    if (file_exists("fotos/productos/".$reg[$i]["codproducto"].".jpg")){
    echo "<img src='fotos/productos/".$reg[$i]["codproducto"].".jpg?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else if (file_exists("fotos/productos/".$reg[$i]["codproducto"].".jpeg")){
    echo "<img src='fotos/productos/".$reg[$i]["codproducto"].".jpeg?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else if (file_exists("fotos/productos/".$reg[$i]["codproducto"].".png")){   
    echo "<img src='fotos/productos/".$reg[$i]["codproducto"].".png?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else {
    echo "<img src='fotos/img.png' class='rounded-circle' style='margin:0px;' width='50' height='40'>";  
   } 
   ?>  
   </td>
   <td><?php echo $reg[$i]['codproducto']; ?></td>
   <td><?php echo $reg[$i]['producto']; ?><small class="text-danger alert-link"><?php echo $tr = ($reg[$i]['existencia'] <= $reg[$i]['stockminimo'] ? '<br>STOCK MINIMO' : ''); ?></small></td>
   <td><?php echo $reg[$i]['nomcategoria']; ?></td>
   <td><?php echo $preciocompra = ($_SESSION['acceso'] == "cajero" || $_SESSION["acceso"]=="cocinero" ? "**********" : $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ',')); ?></td>
   <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
   <td><?php echo $reg[$i]['moneda2'] == '' ? "*****" : "<strong>".$reg[$i]['simbolo2']."</strong> ".$moneda; ?></td>
   <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
   <td><?php echo $reg[$i]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
   <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?></td>
   <td>
   <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerProducto('<?php echo encrypt($reg[$i]["codproducto"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

   <?php if ($_SESSION['acceso'] == "cocinero") {?>
   <button type="button" class="btn btn-danger btn-rounded" data-placement="left" title="Sumar stock" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalStock" data-backdrop="static" data-keyboard="false" onClick="SumarProducto('<?php echo $reg[$i]["idproducto"]; ?>','<?php echo $reg[$i]["codproducto"]; ?>','<?php echo $reg[$i]["producto"]; ?>')"><i class="fa fa-refresh"></i></button>
   <?php } ?>

   <?php if ($_SESSION['acceso'] == "administradorS" || $_SESSION["acceso"]=="secretaria") { ?>
   <button type="button" class="btn btn-info btn-rounded" onClick="UpdateProducto('<?php echo encrypt($reg[$i]["codproducto"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

   <button type="button" class="btn btn-danger btn-rounded" onClick="AgregaIngrediente('<?php echo encrypt($reg[$i]["codproducto"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Agregar" ><i class="fa fa-cart-arrow-down"></i></button>

   <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarProducto('<?php echo encrypt($reg[$i]["codproducto"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("PRODUCTOS"); ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button>
   <?php } ?>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR PRODUCTOS ############################
?>









<?php
############################# CARGAR KARDEX VALORIZADO PRODUCTOS X SUCURSAL ############################
if (isset($_GET['BuscaValorizadoProductosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);
$monedap = new Login();
$cambio = $monedap->MonedaProductoId(); 

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Kardex Valorizado</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">

            <div class="col-md-12">
              <div class="btn-group m-b-20">
                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("KARDEXVALORIZADOPRODUCTOS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("KARDEXVALORIZADOPRODUCTOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("KARDEXVALORIZADOPRODUCTOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

              </div>
            </div>
          </div>

      <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                             <thead>
                             <tr role="row">
                                <th>N°</th>
                                <th>Código</th>
                                <th>Nombre de Producto</th>
                                <th>Categoria</th>
                                <th>Precio Compra</th>
                                <th>Precio Venta</th>
                                <th>Stock</th>
                                <th><?php echo $impuesto; ?></th>
                                <th>Desc %</th>
                                <th>Total Venta</th>
                                <th>Total Compra</th>
                                <th>Ganancias</th>
                             </tr>
                             </thead>
                             <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarProductos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PRODUCTOS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

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
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $reg[$i]['nomcategoria']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
        </tr>
        <?php } } ?>
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
############################# CARGAR KARDEX VALORIZADO PRODUCTOS X SUCURSAL ############################
?>

<?php
############################# CARGAR KARDEX VALORIZADO DE PRODUCTOS ############################
if (isset($_GET['CargaKardexValorizadoProductos'])) { 

$monedap = new Login();
$cambio = $monedap->MonedaProductoId(); 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Producto</th>
                        <th>Categoria</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Stock</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <th>Total Venta</th>
                        <th>Total Compra</th>
                        <th>Ganancias</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarProductos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PRODUCTOS ACTUALMENTE </center>";
    echo "</div>";    

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
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codproducto']; ?></td>
    <td><?php echo $reg[$i]['producto']; ?></td>
    <td><?php echo $reg[$i]['nomcategoria']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivaproducto'] == 'SI' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['descproducto'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR KARDEX VALORIZADO DE PRODUCTOS ############################
?>












<?php
############################# CARGAR COMBOS X SUCURSAL ############################
if (isset($_GET['BuscaCombosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Combos</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">

            <div class="col-md-12">
              <div class="btn-group m-b-20">
              <div class="btn-group">
                <button type="button" class="btn btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i> Pdf</button>
                  <div class="dropdown-menu dropdown-menu-left" x-placement="bottom-end" style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(164px, 35px, 0px);">
                                
                    <a class="dropdown-item" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("COMBOS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Listado General</a>

                    <a class="dropdown-item" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("COMBOSMINIMO") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Stock Minimo</a>

                    <a class="dropdown-item" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("COMBOSMAXIMO") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Stock Máximo</a>

                  </div>
              </div> 

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("COMBOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("COMBOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>

              </div>
            </div>
            </div>

            <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                         <thead>
                         <tr role="row">
                            <th>N°</th>
                            <th>Img</th>
                            <th>Código</th>
                            <th>Nombre de Combo</th>
                            <th>Precio Compra</th>
                            <th>Precio Venta</th>
                            <th>P.V EXT</th>
                            <th>Stock</th>
                            <th><?php echo $impuesto; ?></th>
                            <th>Descto</th>
                            <th>Detalles de Productos</th>
                            <th>Acciones</th> 
                         </tr>
                         </thead>
                         <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarCombos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COMBOS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
$moneda = (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ',')); 
?>
    <?php echo $tr = ($reg[$i]['existencia'] <= $reg[$i]['stockminimo'] ? '<tr role="row" class="odd" style="border-left: 2px solid #ff5050 !important; background: #fce3e3;">' : '<tr role="row" class="odd">'); ?>
    <td><?php echo $a++; ?></td>
    <td>
    <?php
    if (file_exists("fotos/combos/".$reg[$i]["codcombo"].".jpg")){
    echo "<img src='fotos/combos/".$reg[$i]["codcombo"].".jpg?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else if (file_exists("fotos/combos/".$reg[$i]["codcombo"].".jpeg")){
    echo "<img src='fotos/combos/".$reg[$i]["codcombo"].".jpeg?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else if (file_exists("fotos/combos/".$reg[$i]["codcombo"].".png")){   
    echo "<img src='fotos/combos/".$reg[$i]["codcombo"].".png?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else {
    echo "<img src='fotos/img.png' class='rounded-circle' style='margin:0px;' width='50' height='40'>";  
    } 
    ?>  
    </td>
    <td><?php echo $reg[$i]['codcombo']; ?></td>
    <td><?php echo $reg[$i]['nomcombo']; ?></abbr></td>
    <td><?php echo $preciocompra = ($_SESSION['acceso'] == "cajero" || $_SESSION["acceso"]=="cocinero" ? "**********" : $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ',')); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['moneda2'] == '' ? "*****" : "<strong>".$reg[$i]['simbolo2']."</strong> ".$moneda; ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivacombo'] != '0' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['desccombo'], 2, '.', ','); ?></td>
    <td class="font-10 bold"><?php echo $reg[$i]['detalles_productos']; ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false" onClick="VerCombo('<?php echo encrypt($reg[$i]["codcombo"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateCombo('<?php echo encrypt($reg[$i]["codcombo"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-danger btn-rounded" onClick="AgregaProducto('<?php echo encrypt($reg[$i]["codcombo"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Agregar" ><i class="fa fa-cart-arrow-down"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCombo('<?php echo encrypt($reg[$i]["codcombo"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("COMBOS"); ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR COMBOS X SUCURSAL ############################
?>

<?php
############################# CARGAR COMBOS ############################
if (isset($_GET['CargaCombos'])) { 

$monedap = new Login();
$cambio = $monedap->MonedaProductoId(); 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                 <thead>
                 <tr role="row">
                    <th>N°</th>
                    <th>Img</th>
                    <th>Código</th>
                    <th>Nombre de Combo</th>
                    <th>Precio Compra</th>
                    <th>Precio Venta</th>
                    <th>P.V EXT</th>
                    <th>Stock</th>
                    <th><?php echo $impuesto; ?></th>
                    <th>Descto</th>
                    <th>Detalles de Productos</th>
                    <?php echo $perfil = ($_SESSION['acceso'] == "administradorS" || $_SESSION["acceso"]=="secretaria" ? "<th>Acciones</th>" : "<th><i class='mdi mdi-drag-horizontal'></i></th>"); ?> 
                 </tr>
                 </thead>
                 <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarCombos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COMBOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
$moneda = (empty($reg[$i]['montocambio']) ? "0.00" : number_format($reg[$i]['precioventa'] / $reg[$i]['montocambio'], 2, '.', ',')); 
?>
    <?php echo $tr = ($reg[$i]['existencia'] <= $reg[$i]['stockminimo'] ? '<tr role="row" class="odd" style="border-left: 2px solid #ff5050 !important; background: #fce3e3;">' : '<tr role="row" class="odd">'); ?>
    <td><?php echo $a++; ?></td>
    <td>
    <?php
    if (file_exists("fotos/combos/".$reg[$i]["codcombo"].".jpg")){
    echo "<img src='fotos/combos/".$reg[$i]["codcombo"].".jpg?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else if (file_exists("fotos/combos/".$reg[$i]["codcombo"].".jpeg")){
    echo "<img src='fotos/combos/".$reg[$i]["codcombo"].".jpeg?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else if (file_exists("fotos/combos/".$reg[$i]["codcombo"].".png")){   
    echo "<img src='fotos/combos/".$reg[$i]["codcombo"].".png?".time()."' class='rounded-circle' style='margin:0px;' width='50' height='40'>";
    } else {
    echo "<img src='fotos/img.png' class='rounded-circle' style='margin:0px;' width='50' height='40'>";  
    } 
    ?>  
    </td>
    <td><?php echo $reg[$i]['codcombo']; ?></td>
    <td><?php echo $reg[$i]['nomcombo']; ?></abbr></td>
    <td><?php echo $preciocompra = ($_SESSION['acceso'] == "cajero" || $_SESSION["acceso"]=="cocinero" ? "**********" : $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ',')); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['moneda2'] == '' ? "*****" : "<strong>".$reg[$i]['simbolo2']."</strong> ".$moneda; ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivacombo'] != '0' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['desccombo'], 2, '.', ','); ?></td>
    <td class="font-10 bold"><?php echo $reg[$i]['detalles_productos']; ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false" onClick="VerCombo('<?php echo encrypt($reg[$i]["codcombo"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>
    <?php if ($_SESSION['acceso'] == "cocinero") {?>
    <button type="button" class="btn btn-danger btn-rounded" data-placement="left" title="Sumar Stock" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalStock" data-backdrop="static" data-keyboard="false" onClick="SumarCombo('<?php echo $reg[$i]["idcombo"]; ?>','<?php echo $reg[$i]["codcombo"]; ?>','<?php echo $reg[$i]["nomcombo"]; ?>')"><i class="fa fa-refresh"></i></button>
    <?php } ?>

    <?php if ($_SESSION['acceso'] == "administradorS" || $_SESSION["acceso"]=="secretaria") {?>
    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateCombo('<?php echo encrypt($reg[$i]["codcombo"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-danger btn-rounded" onClick="AgregaProducto('<?php echo encrypt($reg[$i]["codcombo"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')" title="Agregar" ><i class="fa fa-cart-arrow-down"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCombo('<?php echo encrypt($reg[$i]["codcombo"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("COMBOS"); ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button>
    <?php } ?>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR COMBOS ############################
?>










<?php
############################# CARGAR KARDEX VALORIZADO COMBOS X SUCURSAL ############################
if (isset($_GET['BuscaValorizadoCombosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);
$monedap = new Login();
$cambio = $monedap->MonedaProductoId(); 

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Kardex Valorizado</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

        <div class="row">
            <div class="col-md-12">
              <div class="btn-group m-b-20">
                <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("KARDEXVALORIZADOCOMBOS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("KARDEXVALORIZADOCOMBOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

                <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("KARDEXVALORIZADOCOMBOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
              </div>
            </div>
        </div>

      <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Combo</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Stock</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <th>Total Venta</th>
                        <th>Total Compra</th>
                        <th>Ganancias</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarCombos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COMBOS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";    

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
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codcombo']; ?></td>
    <td><?php echo $reg[$i]['nomcombo']; ?></abbr></td>
    <td><?php echo $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivacombo'] != '0' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['desccombo'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
        </tr>
        <?php } } ?>
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
############################# CARGAR KARDEX VALORIZADO COMBOS X SUCURSAL ############################
?>

<?php
############################# CARGAR KARDEX VALORIZADO DE COMBOS ############################
if (isset($_GET['CargaKardexValorizadoCombos'])) { 

$monedap = new Login();
$cambio = $monedap->MonedaProductoId(); 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>Código</th>
                        <th>Nombre de Combo</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Stock</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Desc %</th>
                        <th>Total Venta</th>
                        <th>Total Compra</th>
                        <th>Ganancias</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarCombos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COMBOS ACTUALMENTE </center>";
    echo "</div>";    

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
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codcombo']; ?></td>
    <td><?php echo $reg[$i]['nomcombo']; ?></abbr></td>
    <td><?php echo $simbolo.number_format($reg[$i]['preciocompra'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['precioventa'], 2, '.', ','); ?></td>
    <td><?php echo number_format($reg[$i]['existencia'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]['ivacombo'] != '0' ? number_format($valor, 2, '.', ',')."%" : "(E)"; ?></td>
    <td><?php echo number_format($reg[$i]['desccombo'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumCompra, 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($SumVenta-$SumCompra, 2, '.', ','); ?></td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR KARDEX VALORIZADO DE COMBOS ############################
?>













<?php
############################# CARGAR COMPRAS X SUCURSAL ############################
if (isset($_GET['BuscaComprasxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Compras</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("COMPRAS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("COMPRAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("COMPRAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                             <thead>
                             <tr role="row">
                                <th>N°</th>
                                <th>N° de Compra</th>
                                <th>Descripción de Proveedor</th>
                                <th>Nº de Artic</th>
                                <th>Subtotal</th>
                                <th><?php echo $impuesto; ?></th>
                                <th>Dcto %</th>
                                <th>Imp. Total</th>
                                <th>Fecha Emisión</th>
                                <th>Acciones</th>
                             </tr>
                             </thead>
                             <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarCompras();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COMPRAS ACTUALMENTE </center>";
    echo "</div>";
    exit();    

} else {

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");   
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo "Nº ".$documento = ($reg[$i]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['cuitproveedor']."<br> ".$reg[$i]['nomproveedor']; ?></td>
    <td class="text-center"><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaemision'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerCompraPagada('<?php echo encrypt($reg[$i]["codcompra"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <a href="reportepdf?codcompra=<?php echo encrypt($reg[$i]['codcompra']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt("FACTURACOMPRA") ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR COMPRAS X SUCURSAL ############################
?>

<?php
############################# CARGAR COMPRAS ############################
if (isset($_GET['CargaCompras']) && isset($_GET['bcompras'])) {

$criterio = limpiar($_GET['bcompras']);
?>
<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>N° de Compra</th>
                        <th>Descripción de Proveedor</th>
                        <th>Nº de Artic</th>
                        <th>Subtotal</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Dcto %</th>
                        <th>Imp. Total</th>
                        <th>Fecha Emisión</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">

<?php 
if($criterio==""){
    
  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE VALOR PARA TU CRITERIO DE BÚSQUEDA </center>";
  echo "</div>"; 
  exit;   

} else {

$reg = $tra->BusquedaCompras();
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");   
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo "Nº ".$documento = ($reg[$i]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['cuitproveedor']."<br> ".$reg[$i]['nomproveedor']; ?></td>
    <td class="text-center"><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechaemision'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerCompraPagada('<?php echo encrypt($reg[$i]["codcompra"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <?php if($_SESSION['acceso']=="administradorS" || $_SESSION["acceso"]=="secretaria"){ ?>
    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateCompra('<?php echo encrypt($reg[$i]["codcompra"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("U"); ?>','<?php echo encrypt("P"); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCompra('<?php echo encrypt($reg[$i]["codcompra"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codproveedor"]); ?>','<?php echo "P"; ?>','<?php echo encrypt("COMPRAS") ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button> 
    <?php } ?>
    <a href="reportepdf?codcompra=<?php echo encrypt($reg[$i]['codcompra']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt("FACTURACOMPRA") ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR COMPRAS ############################
?>









<?php
############################# CARGAR CUENTAS X PAGAR X SUCURSAL ############################
if (isset($_GET['BuscaCuentasxPagarxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Cuentas x Pagar</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("CUENTASXPAGAR") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("CUENTASXPAGAR") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("CUENTASXPAGAR") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                             <thead>
                             <tr role="row">
                                <th>N°</th>
                                <th>N° de Compra</th>
                                <th>Descripción de Proveedor</th>
                                <th>Nº de Artic</th>
                                <th>Subtotal</th>
                                <th><?php echo $impuesto; ?></th>
                                <th>Dcto %</th>
                                <th>Imp. Total</th>
                                <th>Vencidos</th>
                                <th>Status</th>
                                <th>Acciones</th>
                             </tr>
                             </thead>
                             <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarCuentasxPagar();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON CUENTAS X PAGAR ACTUALMENTE </center>";
    echo "</div>";
    exit();    

} else {

$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo "Nº ".$documento = ($reg[$i]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['cuitproveedor']."<br> ".$reg[$i]['nomproveedor']; ?></td>      
    <td class="text-center"><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
        elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
        elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
        elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php if($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statuscompra'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
      else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statuscompra"]."</span>"; } ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerCompraPendiente('<?php echo encrypt($reg[$i]["codcompra"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <a href="reportepdf?codcompra=<?php echo encrypt($reg[$i]['codcompra']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt("FACTURACOMPRA") ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn  btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
            </td>
            </tr>
            <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR CUENTAS X PAGAR X SUCURSAL ############################
?>

<?php
############################# CARGAR CUENTAS POR PAGAR ############################
if (isset($_GET['CargaCuentasxPagar']) && isset($_GET['bcompras'])) {

$criterio = limpiar($_GET['bcompras']); 
?>
<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                     <thead>
                     <tr role="row">
                        <th>N°</th>
                        <th>N° de Compra</th>
                        <th>Descripción de Proveedor</th>
                        <th>Nº de Artic</th>
                        <th>Subtotal</th>
                        <th><?php echo $impuesto; ?></th>
                        <th>Dcto %</th>
                        <th>Imp. Total</th>
                        <th>Vencidos</th>
                        <th>Status</th>
                        <th>Acciones</th>
                     </tr>
                     </thead>
                     <tbody class="BusquedaRapida">

<?php 
if($criterio==""){
    
  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE VALOR PARA TU CRITERIO DE BÚSQUEDA </center>";
  echo "</div>"; 
  exit;   

} else {

$reg = $tra->BusquedaCuentasxPagar();
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo "Nº ".$documento = ($reg[$i]['documproveedor'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['cuitproveedor']."<br> ".$reg[$i]['nomproveedor']; ?></td>      
    <td class="text-center"><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasic']+$reg[$i]['subtotalivanoc'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalivac'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['ivac'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuentoc'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuentoc'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpagoc'], 2, '.', ','); ?></td>
    <td><?php if($reg[$i]['fechavencecredito']== '0000-00-00') { echo "0"; } 
        elseif($reg[$i]['fechavencecredito'] >= date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo "0"; } 
        elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']== "0000-00-00") { echo Dias_Transcurridos(date("Y-m-d"),$reg[$i]['fechavencecredito']); }
        elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado']!= "0000-00-00") { echo Dias_Transcurridos($reg[$i]['fechapagado'],$reg[$i]['fechavencecredito']); } ?></td>
    <td><?php if($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statuscompra'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
      else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statuscompra"]."</span>"; } ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerCompraPendiente('<?php echo encrypt($reg[$i]["codcompra"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <?php if ($_SESSION["acceso"]=="administradorS" || $_SESSION["acceso"]=="secretaria") { ?>

    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateCompra('<?php echo encrypt($reg[$i]["codcompra"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("U"); ?>','<?php echo "D"; ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-danger btn-rounded" onClick="PagarCompra('<?php echo encrypt($reg[$i]["codcompra"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("PAGARFACTURA") ?>')" title="Pagar Factura" ><i class="fa fa-refresh"></i></button> 

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCompra('<?php echo encrypt($reg[$i]["codcompra"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codproveedor"]); ?>','<?php echo encrypt("D") ?>','<?php echo encrypt("COMPRAS") ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button>

    <?php } ?>
    <a href="reportepdf?codcompra=<?php echo encrypt($reg[$i]['codcompra']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt("FACTURACOMPRA") ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn  btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
            </td>
            </tr>
            <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR CUENTAS POR PAGAR ############################
?>













<?php
############################# CARGAR TRASPASOS X SUCURSAL ############################
if (isset($_GET['BuscaTrapasosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Listado de Traspasos</h4>
      </div>

      <div class="form-body">
        <div class="card-body">

      <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("TRASPASOS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("TRASPASOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("TRASPASOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
      </div>

      <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                <thead>
                <tr role="row">
                    <th>N°</th>
                    <th>Código</th>
                    <th>Sucursal Remitente</th>
                    <th>Sucursal Destinatario</th>
                    <th>Nº Artículos</th>
                    <th>Observaciones</th>
                    <th>Fecha de Emisión</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarTraspasos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON TRASPASOS DE PRODUCTOS ACTUALMENTE EN LA SUCURSAL SELECCIONADA </center>";
    echo "</div>";
    exit();    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
?>
     <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['cuitsucursal'].": <strong>".$reg[$i]['nomsucursal']."</strong>: ".$reg[$i]['nomencargado']; ?></td>
    <td><?php echo $reg[$i]['cuitsucursal2'].": <strong>".$reg[$i]['nomsucursal2']."</strong>: ".$reg[$i]['nomencargado2']; ?></td>
    <td><?php echo $reg[$i]['sumarticulos']; ?></td>
    <td><?php echo $reg[$i]['observaciones'] == "" ? "**********" : $reg[$i]['observaciones']; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechatraspaso'])); ?></td>
    <td>                    
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerTraspaso('<?php echo encrypt($reg[$i]["codtraspaso"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <a href="reportepdf?codtraspaso=<?php echo encrypt($reg[$i]['codtraspaso']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("FACTURATRASPASO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
                </tr>
                <?php } } ?>
                </tbody>
            </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR TRASPASOS X SUCURSAL ############################
?>

<?php
############################# CARGAR TRASPASOS ############################
if (isset($_GET['CargaTraspasos'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                <thead>
                <tr role="row">
                    <th>N°</th>
                    <th>Código</th>
                    <th>Sucursal Remitente</th>
                    <th>Sucursal Destinatario</th>
                    <th>Nº Artículos</th>
                    <th>Observaciones</th>
                    <th>Fecha de Emisión</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarTraspasos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON TRASPASOS DE PRODUCTOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = "<strong>".$reg[$i]['simbolo']."</strong> ";
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['cuitsucursal'].": <strong>".$reg[$i]['nomsucursal']."</strong>: ".$reg[$i]['nomencargado']; ?></td>
    <td><?php echo $reg[$i]['cuitsucursal2'].": <strong>".$reg[$i]['nomsucursal2']."</strong>: ".$reg[$i]['nomencargado2']; ?></td>
    <td><?php echo $reg[$i]['sumarticulos']; ?></td>
    <td><?php echo $reg[$i]['observaciones'] == "" ? "**********" : $reg[$i]['observaciones']; ?></td>
    <td><?php echo date("d-m-Y",strtotime($reg[$i]['fechatraspaso'])); ?></td>
    <td>                    
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerTraspaso('<?php echo encrypt($reg[$i]["codtraspaso"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <?php if($_SESSION['acceso'] == "administradorS"){ ?>

    <!--<button type="button" class="btn btn-outline-info btn-rounded" onClick="UpdateTraspaso('<?php echo encrypt($reg[$i]["codtraspaso"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("U"); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>-->

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarTraspaso('<?php echo encrypt($reg[$i]["codtraspaso"]); ?>','<?php echo encrypt($reg[$i]["recibe"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("TRASPASOS"); ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button> 
    <?php } ?>

    <a href="reportepdf?codtraspaso=<?php echo encrypt($reg[$i]['codtraspaso']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("FACTURATRASPASO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR TRASPASOS ############################
?>









<?php
############################# CARGAR COTIZACIONES X SUCURSAL ############################
if (isset($_GET['BuscaCotizacionesxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Cotizaciones</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("COTIZACIONES") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("COTIZACIONES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("COTIZACIONES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
             <thead>
              <tr role="row">
                <th>N°</th>
                <th>N° de Cotización</th>
                <th>Descripción de Cliente</th>
                <th>Nº Artic</th>
                <th>Subtotal</th>
                <th><?php echo $impuesto; ?></th>
                <th>Dcto %</th>
                <th>Imp. Total</th>
                <th>Fecha Emisión</th>
                <th>Acciones</th>
              </tr>
             </thead>
             <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarCotizaciones();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COTIZACIONES ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = "<strong>".$reg[$i]['simbolo']."</strong> ";
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td> 
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', '.'); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechacotizacion'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerCotizacion('<?php echo encrypt($reg[$i]["codcotizacion"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <a href="reportepdf?codcotizacion=<?php echo encrypt($reg[$i]['codcotizacion']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("FACTURACOTIZACION"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR COTIZACIONES X SUCURSAL ############################
?>

<?php
############################# CARGAR COTIZACIONES ############################
if (isset($_GET['CargaCotizaciones'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                 <thead>
                  <tr role="row">
                    <th>N°</th>
                    <th>N° de Cotización</th>
                    <th>Descripción de Cliente</th>
                    <th>Nº Artic</th>
                    <th>Subtotal</th>
                    <th><?php echo $impuesto; ?></th>
                    <th>Dcto %</th>
                    <th>Imp. Total</th>
                    <th>Fecha Emisión</th>
                    <th>Acciones</th>
                  </tr>
                 </thead>
                 <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarCotizaciones();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON COTIZACIONES ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = "<strong>".$reg[$i]['simbolo']."</strong> ";
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td> 
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', '.'); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechacotizacion'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerCotizacion('<?php echo encrypt($reg[$i]["codcotizacion"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>
    
    <?php if($_SESSION['acceso']=="administradorS" && $reg[$i]["procesada"] == 1 || $_SESSION['acceso']=="secretaria" && $reg[$i]["procesada"] == 1 || $_SESSION["acceso"]=="cajero" && $reg[$i]["procesada"] == 1){ ?>
    <button type="button" class="btn btn-danger btn-rounded" data-placement="left" title="Procesar a Venta" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalPago" data-backdrop="static" data-keyboard="false" onClick="ProcesaCotizacion('<?php echo encrypt($reg[$i]["codcotizacion"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo $reg[$i]["codcliente"]; ?>','<?php echo $reg[$i]['codcliente'] == '0' ? "0" : $reg[$i]['dnicliente']; ?>','<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente'].": ".$reg[$i]['nomcliente']; ?>','<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?>','<?php echo number_format($reg[$i]["limitecredito"], 2, '.', ''); ?>','<?php echo number_format($reg[$i]["totalpago"], 2, '.', ''); ?>')"><i class="fa fa-folder-open-o"></i></button>
    <?php } ?>

    <?php if($_SESSION['acceso']=="administradorS" && $reg[$i]["procesada"] == 1){ ?>
    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateCotizacion('<?php echo encrypt($reg[$i]["codcotizacion"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("U"); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-warning btn-rounded" onClick="AgregaDetalleCotizacion('<?php echo encrypt($reg[$i]["codcotizacion"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("A"); ?>')" title="Agregar Detalle" ><i class="text-white fa fa-tasks"></i></button>

   <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCotizacion('<?php echo encrypt($reg[$i]["codcotizacion"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("COTIZACIONES"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> 
    <?php } ?>

    <a href="reportepdf?codcotizacion=<?php echo encrypt($reg[$i]['codcotizacion']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
 <?php
} 
############################# CARGAR COTIZACIONES ############################
?>











<?php
############################# CARGAR DE CAJAS X SUCURSAL ############################
if (isset($_GET['BuscaCajasxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Cajas</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <button type="button" class="btn btn-success btn-light" data-placement="left" title="Nueva Caja" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCaja" data-backdrop="static" data-keyboard="false" onClick="AgregaSucursalxCaja('<?php echo $codsucursal; ?>'); CargaHorariosxSucursal('<?php echo $codsucursal; ?>'); CargaUsuarios('<?php echo $codsucursal; ?>');"><i class="fa fa-plus"></i> Nuevo</button>

            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("CAJAS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("CAJAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("CAJAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                    <thead>
                    <tr role="row">
                        <th>N°</th>
                        <th>Horario</th>
                        <th>Nombre de Caja</th>
                        <th>Nº Documento</th>
                        <th>Responsable</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarCajas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON CAJAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['hora_desde']." : ".$reg[$i]['hora_hasta']; ?></td>
    <td><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></td>
    <td><?php echo $reg[$i]['dni']; ?></td>
    <td><?php echo $reg[$i]['nombres']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCaja" data-backdrop="static" data-keyboard="false" onClick="UpdateCaja('<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcaja"]); ?>','<?php echo $reg[$i]["nrocaja"]; ?>','<?php echo $reg[$i]["nomcaja"]; ?>','<?php echo $reg[$i]["codigo"]; ?>','update'); CargaUsuarios('<?php echo encrypt($reg[$i]["codsucursal"]); ?>'); SelectUsuario('<?php echo $reg[$i]["codigo"]; ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>');"><i class="fa fa-edit"></i></button>
                                 
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCaja('<?php echo encrypt($reg[$i]["codcaja"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("CAJAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR DE CAJAS X SUCURSAL ############################
?>

<?php
############################# CARGAR CAJAS PARA VENTAS ############################
if (isset($_GET['CargaCajas'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                    <thead>
                    <tr role="row">
                        <th>N°</th>
                        <th>Horario</th>
                        <th>Nombre de Caja</th>
                        <th>Nº Documento</th>
                        <th>Responsable</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarCajas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON CAJAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $reg[$i]['hora_desde']." : ".$reg[$i]['hora_hasta']; ?></td>
    <td><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></td>
    <td><?php echo $reg[$i]['dni']; ?></td>
    <td><?php echo $reg[$i]['nombres']; ?></td>
    <td>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCaja" data-backdrop="static" data-keyboard="false" onClick="UpdateCaja('<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcaja"]); ?>','<?php echo encrypt($reg[$i]["codhorario"]); ?>','<?php echo $reg[$i]["nrocaja"]; ?>','<?php echo $reg[$i]["nomcaja"]; ?>','<?php echo $reg[$i]["codigo"]; ?>','update')"><i class="fa fa-edit"></i></button>
                                 
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarCaja('<?php echo encrypt($reg[$i]["codcaja"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("CAJAS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button> </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>   
<?php 
} 
############################# CARGAR CAJAS PARA VENTAS ############################
?>







<?php
############################# CARGAR ARQUEOS DE CAJAS X SUCURSAL ############################
if (isset($_GET['BuscaArqueosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Arqueos de Cajas</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("ARQUEOS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("ARQUEOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("ARQUEOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                            <thead>
                            <tr role="row">
                            <th>N°</th>
                            <th>Horario</th>
                            <th>Caja</th>
                            <th>Responsable</th>
                            <th>Hora de Apertura</th>
                            <th>Hora de Cierre</th>
                            <th>Monto Inicial</th>
                            <th>Efectivo en Caja</th>
                            <th>Diferencia en Caja</th>
                            <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarArqueoCaja();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON ARQUEOS DE CAJAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
$TotalEfectivo = $reg[$i]['montoinicial']+$reg[$i]['efectivo']+$reg[$i]['propinasefectivo']+$reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']-$reg[$i]['egresos']; 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
    <td><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></td>
    <td><?php echo $reg[$i]['dni'].": ".$reg[$i]['nombres']; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaapertura'])); ?></td>
    <td><?php echo $reg[$i]['statusarqueo'] == 1 ? "**********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechacierre'])); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['montoinicial'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['dineroefectivo'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['diferencia'], 2, '.', ','); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerArqueo('<?php echo encrypt($reg[$i]["codarqueo"]); ?>')"><i class="fa fa-eye"></i></button>
    <?php if($_SESSION['acceso'] == "administradorS" && $reg[$i]["statusarqueo"] == '1' || $_SESSION['acceso'] == "secretaria" && $reg[$i]["statusarqueo"] == '1' || $_SESSION['acceso'] == "cajero" && $reg[$i]["statusarqueo"] == '1'){ ?>
    <button type="button" class="btn btn-dark btn-rounded" onClick="CerrarCaja('<?php echo encrypt($reg[$i]["codarqueo"]); ?>')" title="Cerrar Caja" ><i class="fa fa-archive"></i></button>
    <?php } else if($_SESSION['acceso'] == "secretaria2" && $reg[$i]["statusarqueo"] == '1' || $_SESSION['acceso'] == "cajero2" && $reg[$i]["statusarqueo"] == '1'){ ?>
    <button type="button" class="btn btn-dark btn-rounded" data-placement="left" title="Cerrar Arqueo" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCerrarCaja" data-backdrop="static" data-keyboard="false" onClick="CerrarArqueo(
    '<?php echo encrypt($reg[$i]["codarqueo"]); ?>',
    '<?php echo $reg[$i]["nrocaja"].": ".$reg[$i]["nomcaja"]; ?>',
    '<?php echo $reg[$i]["dni"].": ".$reg[$i]["nombres"]; ?>',
    '<?php echo number_format($reg[$i]["montoinicial"], 2, '.', ''); ?>',
    '<?php echo number_format($TotalEfectivo, 2, '.', ''); ?>',
    '<?php echo $reg[$i]["fechaapertura"]; ?>'
    )"><i class="fa fa-archive"></i></i></button>
    <?php } else { ?>
    <a href="reportepdf?codarqueo=<?php echo encrypt($reg[$i]['codarqueo']); ?>&tipo=<?php echo encrypt("TICKETCIERRE"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    <?php } ?>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR ARQUEOS DE CAJAS X SUCURSAL ############################
?>

<?php
########################### CARGAR ARQUEOS DE CAJAS PARA VENTAS ##########################
if (isset($_GET['CargaArqueos'])) { 

?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                            <thead>
                            <tr role="row">
                            <th>N°</th>
                            <th>Horario</th>
                            <th>Caja</th>
                            <th>Responsable</th>
                            <th>Hora de Apertura</th>
                            <th>Hora de Cierre</th>
                            <th>Monto Inicial</th>
                            <th>Efectivo en Caja</th>
                            <th>Diferencia en Caja</th>
                            <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarArqueoCaja();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON ARQUEOS DE CAJAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
$TotalEfectivo = $reg[$i]['montoinicial']+$reg[$i]['efectivo']+$reg[$i]['propinasefectivo']+$reg[$i]['ingresosefectivo']+$reg[$i]['abonosefectivo']-$reg[$i]['egresos']; 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
    <td><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></td>
    <td><?php echo $reg[$i]['dni'].": ".$reg[$i]['nombres']; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaapertura'])); ?></td>
    <td><?php echo $reg[$i]['statusarqueo'] == 1 ? "**********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechacierre'])); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['montoinicial'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['dineroefectivo'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['diferencia'], 2, '.', ','); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerArqueo('<?php echo encrypt($reg[$i]["codarqueo"]); ?>')"><i class="fa fa-eye"></i></button>
    <?php if($_SESSION['acceso'] == "administradorS" && $reg[$i]["statusarqueo"] == '1' || $_SESSION['acceso'] == "secretaria" && $reg[$i]["statusarqueo"] == '1' || $_SESSION['acceso'] == "cajero" && $reg[$i]["statusarqueo"] == '1'){ ?>
    <button type="button" class="btn btn-dark btn-rounded" onClick="CerrarCaja('<?php echo encrypt($reg[$i]["codarqueo"]); ?>')" title="Cerrar Caja" ><i class="fa fa-archive"></i></button>
    <?php } else if($_SESSION['acceso'] == "secretaria2" && $reg[$i]["statusarqueo"] == '1' || $_SESSION['acceso'] == "cajero2" && $reg[$i]["statusarqueo"] == '1'){ ?>
    <button type="button" class="btn btn-dark btn-rounded" data-placement="left" title="Cerrar Arqueo" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCerrarCaja" data-backdrop="static" data-keyboard="false" onClick="CerrarArqueo(
    '<?php echo encrypt($reg[$i]["codarqueo"]); ?>',
    '<?php echo $reg[$i]["nrocaja"].": ".$reg[$i]["nomcaja"]; ?>',
    '<?php echo $reg[$i]["dni"].": ".$reg[$i]["nombres"]; ?>',
    '<?php echo number_format($reg[$i]["montoinicial"], 2, '.', ''); ?>',
    '<?php echo number_format($TotalEfectivo, 2, '.', ''); ?>',
    '<?php echo $reg[$i]["fechaapertura"]; ?>'
    )"><i class="fa fa-archive"></i></i></button>
    <?php } else { ?>
    <a href="reportepdf?codarqueo=<?php echo encrypt($reg[$i]['codarqueo']); ?>&tipo=<?php echo encrypt("TICKETCIERRE"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    <?php } ?>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
######################### CARGAR ARQUEOS DE CAJAS PARA VENTAS ############################
?>








<?php
############################# CARGAR MOVIMIENTOS DE CAJAS X SUCURSAL ############################
if (isset($_GET['BuscaMovimientosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Movimientos de Cajas</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("MOVIMIENTOS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("MOVIMIENTOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("MOVIMIENTOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                            <thead>
                            <tr role="row">
                              <th>N°</th>
                              <th>Horario</th>
                              <th>Caja</th>
                              <th>Responsable</th>
                              <th>Tipo</th>
                              <th>Descripción</th>
                              <th>Monto</th>
                              <th>Fecha</th>
                              <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarMovimientos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON MOVIMIENTOS EN CAJAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
    <td><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></td>
    <td><?php echo $reg[$i]['nombres']; ?></td>
    <td><?php echo $reg[$i]['tipomovimiento']; ?></td>
    <td><?php echo $reg[$i]['descripcionmovimiento']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['montomovimiento'], 2, '.', ','); ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechamovimiento'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerMovimiento('<?php echo encrypt($reg[$i]["numero"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <a href="reportepdf?numero=<?php echo encrypt($reg[$i]['numero']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("TICKETMOVIMIENTO") ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded text-white" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR MOVIMIENTOS DE CAJAS X SUCURSAL ############################
?>

<?php
######################## CARGAR MOVIMIENTOS DE CAJAS ########################
if (isset($_GET['CargaMovimientos'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                            <thead>
                            <tr role="row">
                              <th>N°</th>
                              <th>Horario</th>
                              <th>Caja</th>
                              <th>Responsable</th>
                              <th>Tipo</th>
                              <th>Descripción</th>
                              <th>Monto</th>
                              <th>Fecha</th>
                              <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarMovimientos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON MOVIMIENTOS EN CAJAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td class="text-danger alert-link"><?php echo $reg[$i]['hora_desde']." - ".$reg[$i]['hora_hasta']; ?></td>
    <td><?php echo $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']; ?></td>
    <td><?php echo $reg[$i]['nombres']; ?></td>
    <td><?php echo $reg[$i]['tipomovimiento']; ?></td>
    <td><?php echo $reg[$i]['descripcionmovimiento']; ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['montomovimiento'], 2, '.', ','); ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechamovimiento'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerMovimiento('<?php echo encrypt($reg[$i]["numero"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <?php if ($reg[$i]['statusarqueo']=="1") { ?>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Editar" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalMovimiento" data-backdrop="static" data-keyboard="false" onClick="UpdateMovimiento('<?php echo $reg[$i]["codmovimiento"]; ?>','<?php echo encrypt($reg[$i]["numero"]); ?>','<?php echo encrypt($reg[$i]["codarqueo"]); ?>','<?php echo encrypt($reg[$i]["codcaja"]); ?>','<?php echo $reg[$i]["tipomovimiento"]; ?>','<?php echo $reg[$i]["descripcionmovimiento"]; ?>','<?php echo number_format($reg[$i]["montomovimiento"], 2, '.', ','); ?>','<?php echo $reg[$i]["mediomovimiento"]; ?>','<?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechamovimiento'])); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','update')"><i class="fa fa-edit"></i></button>
    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarMovimiento('<?php echo encrypt($reg[$i]["codmovimiento"]); ?>','<?php echo encrypt("MOVIMIENTOS"); ?>')" title="Eliminar" ><i class="fa fa-trash-o"></i></button>
    <?php } ?>

    <a href="reportepdf?numero=<?php echo encrypt($reg[$i]['numero']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("TICKETMOVIMIENTO") ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded text-white" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
######################## CARGAR MOVIMIENTOS DE CAJAS #######################
?>









<?php
############################# CARGAR PEDIDOS X SUCURSAL ############################
if (isset($_GET['BuscaPedidosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Pedidos</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("PEDIDOS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("PEDIDOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("PEDIDOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                            <thead>
                            <tr role="row">
                                <th>N°</th>
                                <th>Descripción de Cliente</th>
                                <th>Nº Artic</th>
                                <th>Subtotal</th>
                                <th><?php echo $impuesto; ?></th>
                                <th>Dcto %</th>
                                <th>Imp. Total</th>
                                <th>Status</th>
                                <th>Fecha Emisión</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarPedidos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PEDIDOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = "<strong>".$reg[$i]['simbolo']."</strong> ";
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td> 
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]["statuspedido"] == 0 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ENTREGADA</span>" : "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> PENDIENTE</span>"; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerPedido('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>
    <?php if($_SESSION['acceso'] == "administradorS" || $_SESSION['acceso'] == "cajero" && $reg[$i]['statuspedido'] == 1) { ?>

    <?php if($reg[$i]['statuspedido'] == 1) { ?><button type="button" class="btn btn-danger btn-rounded" onClick="EntregarPedido('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("ENTREGARPEDIDO"); ?>')" title="Entregar Pedido"><i class="fa fa-refresh"></i></button><?php } ?>
    <?php } ?>

    <?php if($_SESSION['acceso']=="administradorS"){ ?>

    <button type="button" class="btn btn-info btn-rounded" onClick="UpdatePedido('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("U"); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <?php if($reg[$i]['statuspedido'] == 0) { ?><button type="button" class="btn btn-dark btn-rounded" onClick="EliminarPedido('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt("PEDIDOS"); ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button><?php } ?> 

    <?php } ?>
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR PEDIDOS X SUCURSAL ############################
?>

<?php
############################# CARGAR PEDIDOS ############################
if (isset($_GET['CargaPedidos'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                        <thead>
                        <tr role="row">
                            <th>N°</th>
                            <th>Descripción de Cliente</th>
                            <th>Nº Artic</th>
                            <th>Subtotal</th>
                            <th><?php echo $impuesto; ?></th>
                            <th>Dcto %</th>
                            <th>Imp. Total</th>
                            <th>Status</th>
                            <th>Fecha Emisión</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarPedidos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PEDIDOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = "<strong>".$reg[$i]['simbolo']."</strong> ";
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></abbr></td> 
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpago'], 2, '.', ','); ?></td>
    <td><?php echo $reg[$i]["statuspedido"] == 0 ? "<span class='badge badge-success'><i class='fa fa-check'></i> ENTREGADA</span>" : "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> PENDIENTE</span>"; ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerPedido('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>
    <?php if($_SESSION['acceso'] == "administradorS" || $_SESSION['acceso'] == "cajero" && $reg[$i]['statuspedido'] == 1) { ?>

    <?php if($reg[$i]['statuspedido'] == 1) { ?><button type="button" class="btn btn-danger btn-rounded" onClick="EntregarPedido('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("ENTREGARPEDIDO"); ?>')" title="Entregar Pedido"><i class="fa fa-refresh"></i></button><?php } ?>
    <?php } ?>

    <?php if($_SESSION['acceso']=="administradorS"){ ?>

    <button type="button" class="btn btn-info btn-rounded" onClick="UpdatePedido('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("U"); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <?php if($reg[$i]['statuspedido'] == 0) { ?><button type="button" class="btn btn-dark btn-rounded" onClick="EliminarPedido('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt("PEDIDOS"); ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button><?php } ?> 

    <?php } ?>
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR PEDIDOS ############################
?>











<?php
############################# CARGAR VENTAS X SUCURSAL ############################
if (isset($_GET['BuscaVentasxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Ventas</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("VENTAS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("VENTAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("VENTAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                         <thead>
                         <tr role="row">
                            <th>N°</th>
                            <th>N° de Venta</th>
                            <th>Descripción de Cliente</th>
                            <th>Nº Artic</th>
                            <th>Subtotal</th>
                            <th><?php echo $impuesto; ?></th>
                            <th>Dcto %</th>
                            <th>Imp. Total</th>
                            <th>Status</th>
                            <th>Fecha Emisión</th>
                            <th>Acciones</th>
                         </tr>
                         </thead>
                         <tbody class="BusquedaRapida">

<?php
$reg = $tra->ListarVentas();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PEDIDOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><abbr title="CAJA: <?php echo $caja = ($reg[0]['codcaja'] == "0" ? "********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?>"><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></abbr></td>
    <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $cliente = ( $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?><br>
    <?php if($reg[$i]['codmesa'] != 0){ echo "<small class='text-dark alert-link'><i class='fa fa-tasks'></i> ".$reg[$i]['nomsala']."<br><i class='fa fa-tasks'></i> ".$reg[$i]['nommesa']."</small>"; 
    } elseif($reg[$i]['repartidor'] == 0){ echo "<small class='text-dark alert-link'><i class='fa fa-home'></i> EN ESTABLECIMIENTO</small>"; 
    } elseif($reg[$i]['repartidor'] != 0 ){ echo "<small class='text-dark alert-link'><i class='fa fa-motorcycle'></i> DELIVERY</small>"; } ?></abbr></td>

    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]['totalpropina']+$reg[$i]['totalpropina']+$reg[$i]['montodelivery'], 2, '.', ','); ?></td>
  
    <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
    elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
    else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>

    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo "1"; ?>')"><i class="fa fa-eye"></i></button>

    <?php if($reg[$i]['statusventa'] != "ANULADA"){ ?>  
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']) ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    <?php } ?>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR VENTAS X SUCURSAL ############################
?>

<?php
############################# CARGAR VENTAS ############################
if (isset($_GET['CargaVentas'])&& isset($_GET['bventas'])) {

$criterio = limpiar($_GET['bventas']);
?>
<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                 <thead>
                 <tr role="row">
                    <th>N°</th>
                    <th>N° de Venta</th>
                    <th>Descripción de Cliente</th>
                    <th>Nº Artic</th>
                    <th>Subtotal</th>
                    <th><?php echo $impuesto; ?></th>
                    <th>Dcto %</th>
                    <th>Imp. Total</th>
                    <th>Status</th>
                    <th>Fecha Emisión</th>
                    <th>Acciones</th>
                 </tr>
                 </thead>
                 <tbody class="BusquedaRapida">

<?php
if($criterio==""){
    
  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR INGRESE VALOR PARA TU CRITERIO DE BÚSQUEDA </center>";
  echo "</div>";
  exit;    

} else {

$reg = $tra->BusquedaVentas();
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><abbr title="CAJA: <?php echo $caja = ($reg[0]['codcaja'] == "0" ? "********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?>"><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></abbr></td>
    <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $cliente = ( $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?><br>
    <?php if($reg[$i]['codmesa'] != 0){ echo "<small class='text-dark alert-link'><i class='fa fa-tasks'></i> ".$reg[$i]['nomsala']."<br><i class='fa fa-tasks'></i> ".$reg[$i]['nommesa']."</small>"; 
    } elseif($reg[$i]['repartidor'] == 0){ echo "<small class='text-dark alert-link'><i class='fa fa-home'></i> EN ESTABLECIMIENTO</small>"; 
    } elseif($reg[$i]['repartidor'] != 0 ){ echo "<small class='text-dark alert-link'><i class='fa fa-motorcycle'></i> DELIVERY</small>"; } ?></abbr></td>

    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]['totalpropina']+$reg[$i]['totalpropina']+$reg[$i]['montodelivery'], 2, '.', ','); ?></td>
  
    <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
    elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
    else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>

    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo "1"; ?>')"><i class="fa fa-eye"></i></button>

    <?php if($_SESSION['acceso']=="administradorS" || $_SESSION["acceso"]=="cajero" && $reg[$i]['docelectronico'] == 0 && $reg[$i]['statusventa'] != "ANULADA"){ ?>
    
    <?php if($reg[$i]['statusarqueo'] == 1){ ?><button type="button" class="btn btn-info btn-rounded" onClick="UpdateVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("U"); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo "1"; ?>','<?php echo encrypt("VENTAS") ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button><?php } ?>
     
    <?php } ?>
    <?php if($reg[$i]['statusventa'] != "ANULADA"){ ?>  
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']) ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    <?php } ?>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR VENTAS ############################
?>






<?php
############################# CARGAR VENTAS EN DELIVERY PENDIENTES X SUCURSAL ############################
if (isset($_GET['BuscaVentasDeliveryPendientesxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Ventas en Delivery</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("DELIVERYPENDIENTES") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("DELIVERYPENDIENTES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("DELIVERYPENDIENTES") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                             <thead>
                             <tr role="row">
                                <th>N°</th>
                                <th>N° de Venta</th>
                                <th>Descripción de Cliente</th>
                                <th>Nº Artic</th>
                                <th>Subtotal</th>
                                <th><?php echo $impuesto; ?></th>
                                <th>Dcto %</th>
                                <th>Imp. Total</th>
                                <th>Status</th>
                                <th>Fecha Emisión</th>
                                <th>Acciones</th>
                             </tr>
                             </thead>
                             <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarVentasDeliveryPendientes();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON VENTAS EN DELIVERY ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><abbr title="CAJA: <?php echo $caja = ($reg[0]['codcaja'] == "0" ? "********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?>"><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></abbr></td>
    
    <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $cliente = ( $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?><br>
    <?php if($reg[$i]['repartidor'] == 0){ echo "<small class='text-dark alert-link'><i class='fa fa-home'></i> EN ESTABLECIMIENTO</small>"; 
    } elseif($reg[$i]['repartidor'] != 0 ){ echo "<small class='text-dark alert-link'><i class='fa fa-motorcycle'></i> DELIVERY</small>"; } ?></abbr></td>

    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]['montodelivery'], 2, '.', ','); ?></td>
    <td><?php 
    if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
    elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
    else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>

    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo "2"; ?>')"><i class="fa fa-eye"></i></button>

    <?php if($reg[$i]['statusventa'] != "ANULADA"){ ?>  
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']) ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>

    <?php if($reg[$i]['codcaja'] == "0"){ ?>  
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt("TICKETDELIVERY") ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    <?php } ?>

    <?php } ?>
            </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR VENTAS EN DELIVERY PENDIENTES X SUCURSAL ############################
?>

<?php
############################# CARGAR VENTAS EN DELIVERY PENDIENTES ############################
if (isset($_GET['CargaVentasDeliveryPendientes'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                             <thead>
                             <tr role="row">
                                <th>N°</th>
                                <th>N° de Venta</th>
                                <th>Descripción de Cliente</th>
                                <th>Nº Artic</th>
                                <th>Subtotal</th>
                                <th><?php echo $impuesto; ?></th>
                                <th>Dcto %</th>
                                <th>Imp. Total</th>
                                <th>Status</th>
                                <th>Fecha Emisión</th>
                                <th>Acciones</th>
                             </tr>
                             </thead>
                             <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarVentasDeliveryPendientes();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON VENTAS EN DELIVERY ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><abbr title="CAJA: <?php echo $caja = ($reg[0]['codcaja'] == "0" ? "********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?>"><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></abbr></td>
    
    <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $cliente = ( $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?><br>
    <?php if($reg[$i]['repartidor'] == 0){ echo "<small class='text-dark alert-link'><i class='fa fa-home'></i> EN ESTABLECIMIENTO</small>"; 
    } elseif($reg[$i]['repartidor'] != 0 ){ echo "<small class='text-dark alert-link'><i class='fa fa-motorcycle'></i> DELIVERY</small>"; } ?></abbr></td>

    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]['montodelivery'], 2, '.', ','); ?></td>
    <td><?php 
    if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
    elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
    else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>

    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo "2"; ?>')"><i class="fa fa-eye"></i></button>

    <?php if($_SESSION['acceso']=="administradorS" || $_SESSION["acceso"]=="cajero" && $reg[$i]['docelectronico'] == 0 && $reg[$i]['statusventa'] != "ANULADA"){ ?>
    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("U"); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo "2"; ?>','<?php echo encrypt("VENTAS") ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button> 
    <?php } ?>

    <?php if($_SESSION['acceso'] == "administradorS" && $reg[$i]['codcaja'] == 0 || $_SESSION['acceso'] == "secretaria" && $reg[$i]['codcaja'] == 0|| $_SESSION['acceso'] == "cajero" && $reg[$i]['codcaja'] == 0) { ?>

    <button type="button" class="btn btn-danger btn-rounded" onClick="CobrarDelivery('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("COBRARDELIVERY"); ?>')" title="Entregar Pedido"><i class="fa fa-refresh"></i></button>

    <?php } ?>

    <?php if($reg[$i]['statusventa'] != "ANULADA"){ ?>  
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']) ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>

    <?php if($reg[$i]['codcaja'] == "0"){ ?>  
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt("TICKETDELIVERY") ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    <?php } ?>

    <?php } ?>
            </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR VENTAS EN DELIVERY PENDIENTES ############################
?>






<?php
############################# CARGAR VENTAS EN DELIVERY PAGADAS ############################
if (isset($_GET['CargaVentasDeliveryPagadas']) && isset($_GET['codsucursal']) && isset($_GET['desde']) && isset($_GET['hasta'])) {

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

$reg = $tra->ListarVentasDeliveryPagadas();
?>
 <!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Delivery Pagados por Fecha Desde <?php echo date("d-m-Y", strtotime($desde)); ?> Hasta <?php echo date("d-m-Y", strtotime($hasta)); ?></h4>
      </div>

      <div class="form-body">
        <div class="card-body">

      <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&tipo=<?php echo encrypt("DELIVERYPAGADOS") ?>" target="_blank" rel="noopener noreferrer"  data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("DELIVERYPAGADOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

              <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&desde=<?php echo $desde; ?>&hasta=<?php echo $hasta; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("DELIVERYPAGADOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
      </div>

      <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                                 <thead>
                                 <tr role="row">
                                    <th>N°</th>
                                    <th>N° de Venta</th>
                                    <th>Descripción de Cliente</th>
                                    <th>Nº Artic</th>
                                    <th>Subtotal</th>
                                    <th><?php echo $impuesto; ?></th>
                                    <th>Dcto %</th>
                                    <th>Imp. Total</th>
                                    <th>Status</th>
                                    <th>Fecha Emisión</th>
                                    <th>Acciones</th>
                                 </tr>
                                 </thead>
                                 <tbody class="BusquedaRapida">
<?php 
if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON VENTAS EN DELIVERY ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><abbr title="CAJA: <?php echo $caja = ($reg[0]['codcaja'] == "0" ? "********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?>"><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></abbr></td>
    
    <td><abbr title="<?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?>"><?php echo $cliente = ( $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?><br>
    <?php if($reg[$i]['repartidor'] == 0){ echo "<small class='text-dark alert-link'><i class='fa fa-home'></i> EN ESTABLECIMIENTO</small>"; 
    } elseif($reg[$i]['repartidor'] != 0 ){ echo "<small class='text-dark alert-link'><i class='fa fa-motorcycle'></i> DELIVERY</small>"; } ?></abbr></td>

    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['subtotalivasi']+$reg[$i]['subtotalivano'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaliva'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['iva'], 2, '.', ','); ?>%</sup></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totaldescuento'], 2, '.', ','); ?><sup><?php echo number_format($reg[$i]['descuento'], 2, '.', ','); ?>%</sup></td>
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]['montodelivery'], 2, '.', ','); ?></td>
    <td><?php 
    if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
    elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE" && $reg[$i]['codcaja'] != "0") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
    else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>

    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo "3"; ?>')"><i class="fa fa-eye"></i></button>

    <?php if($_SESSION['acceso']=="administradorS" || $_SESSION["acceso"]=="cajero" && $reg[$i]['docelectronico'] == 0 && $reg[$i]['statusventa'] != "ANULADA"){ ?>
    <button type="button" class="btn btn-info btn-rounded" onClick="UpdateVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo "3"; ?>','<?php echo encrypt("U"); ?>')" title="Editar" ><i class="fa fa-edit"></i></button>

    <button type="button" class="btn btn-dark btn-rounded" onClick="EliminarVenta('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo "3"; ?>','<?php echo encrypt("VENTAS") ?>')" title="Eliminar"><i class="fa fa-trash-o"></i></button> 
    <?php } ?>

    <?php if($_SESSION['acceso'] == "administradorS" && $reg[$i]['codcaja'] == 0 || $_SESSION['acceso'] == "secretaria" && $reg[$i]['codcaja'] == 0|| $_SESSION['acceso'] == "cajero" && $reg[$i]['codcaja'] == 0) { ?>

    <button type="button" class="btn btn-danger btn-rounded" onClick="CobrarDelivery('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt("COBRARDELIVERY"); ?>')" title="Entregar Pedido"><i class="fa fa-refresh"></i></button>

    <?php } ?>

    <?php if($reg[$i]['statusventa'] != "ANULADA"){ ?>  
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']) ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>

    <?php if($reg[$i]['codcaja'] == "0"){ ?>  
    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']) ?>&tipo=<?php echo encrypt("TICKETDELIVERY") ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    <?php } ?>

    <?php } ?>
                </td>
            </tr>
            <?php } } ?>
            </tbody>
        </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR VENTAS EN DELIVERY PAGADAS ############################
?>








<?php
############################# CARGAR CREDITOS X SUCURSAL ############################
if (isset($_GET['BuscaCreditosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Ventas a Créditos</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("CREDITOS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("CREDITOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("CREDITOS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                             <thead>
                             <tr role="row">
                                <th>N°</th>
                                <th>N° de Venta</th>
                                <th>Nº de Documento</th>
                                <th>Nombre de Cliente</th>
                                <th>Imp. Total</th>
                                <th>Abono</th>
                                <th>Debe</th>
                                <th>Status</th>
                                <th>Acciones</th>
                             </tr>
                             </thead>
                             <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarCreditos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON CREDITOS DE VENTAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?></td> 
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></td>

    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]['totalpropina'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]['totalpropina']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
    elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
    else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerCredito('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>

    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("TICKETCREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-warning btn-rounded text-white" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>

    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR CREDITOS X SUCURSAL ############################
?>

<?php
############################# CARGAR CREDITOS ############################
if (isset($_GET['CargaCreditos'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                             <thead>
                             <tr role="row">
                                <th>N°</th>
                                <th>N° de Venta</th>
                                <th>Nº de Documento</th>
                                <th>Nombre de Cliente</th>
                                <th>Imp. Total</th>
                                <th>Abono</th>
                                <th>Debe</th>
                                <th>Status</th>
                                <th>Acciones</th>
                             </tr>
                             </thead>
                             <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarCreditos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON CREDITOS DE VENTAS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>");  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : "Nº ".$documento = ($reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento']).": ".$reg[$i]['dnicliente']; ?></td> 
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></td>

    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]['totalpropina'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php echo $simbolo.number_format($reg[$i]['totalpago']+$reg[$i]['totalpropina']-$reg[$i]['creditopagado'], 2, '.', ','); ?></td>
    <td><?php if($reg[$i]["statusventa"] == 'PAGADA') { echo "<span class='badge badge-success'><i class='fa fa-check'></i> ".$reg[$i]["statusventa"]."</span>"; } 
    elseif($reg[$i]["statusventa"] == 'ANULADA') { echo "<span class='badge badge-warning text-white'><i class='fa fa-exclamation-circle'></i> ".$reg[$i]["statusventa"]."</span>"; }
    elseif($reg[$i]['fechavencecredito'] < date("Y-m-d") && $reg[$i]['fechapagado'] == "0000-00-00" && $reg[$i]['statusventa'] == "PENDIENTE") { echo "<span class='badge badge-danger'><i class='fa fa-times'></i> VENCIDA </span>"; }
    else { echo "<span class='badge badge-info'><i class='fa fa-exclamation-triangle'></i> ".$reg[$i]["statusventa"]."</span>"; } ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalDetalle" data-backdrop="static" data-keyboard="false" onClick="VerCredito('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>
    <?php if($reg[$i]['totalpago'] != $reg[$i]['creditopagado']) { ?>
    <button type="button" class="btn btn-info btn-rounded" data-placement="left" title="Nuevo Abono" data-original-title="" data-href="#" data-toggle="modal" data-target="#myModalCredito" data-backdrop="static" data-keyboard="false" onClick="AbonoCreditoVenta('<?php echo encrypt($reg[$i]["codcliente"]); ?>',
    '<?php echo encrypt($reg[$i]["codventa"]); ?>',
    '<?php echo encrypt($reg[$i]["codsucursal"]); ?>',
    '<?php echo number_format($reg[$i]['totalpago']-$reg[$i]['creditopagado'], 2, '.', ''); ?>',
    '<?php echo $reg[$i]['documcliente'] == '0' ? "DOCUMENTO" : $reg[$i]['documento'].": ".$reg[$i]["dnicliente"]; ?>',
    '<?php echo $reg[$i]["nomcliente"]; ?>',
    '<?php echo "<strong>".$reg[$i]["tipodocumento"].":</strong> ".$reg[$i]["codfactura"]; ?>',
    '<?php echo number_format($reg[$i]["totalpago"]+$reg[$i]["totalpropina"], 2, '.', ''); ?>',
    '<?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechaventa'])); ?>',
    '<?php echo number_format($total = ( $reg[$i]['creditopagado'] == '' ? "0.00" : $reg[$i]['creditopagado']), 2, '.', ''); ?>',
    '<?php echo number_format($reg[$i]['totalpago']+$reg[$i]["totalpropina"]-$reg[$i]['creditopagado'], 2, '.', ''); ?>')"><i class="fa fa-credit-card"></i></button>
    <?php } ?>

    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("TICKETCREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-warning btn-rounded text-white" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>

    <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
    </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR CREDITOS ############################
?>






<?php
############################# CARGAR NOTAS DE CREDITOS X SUCURSAL ############################
if (isset($_GET['BuscaNotasCreditosxSucursal'])&& isset($_GET['codsucursal'])) {

$codsucursal = limpiar($_GET['codsucursal']);

if($codsucursal=="") {

  echo "<div class='alert alert-danger'>";
  echo "<button type='button' class='close' data-dismiss='alert' aria-text='true'>&times;</button>";
  echo "<center><span class='fa fa-info-circle'></span> POR FAVOR SELECCIONE SUCURSAL PARA TU BÚSQUEDA</center>";
  echo "</div>";
  exit;

} else { 
?>

<!-- Row -->
 <div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header bg-warning">
        <h4 class="card-title text-white"><i class="fa fa-tasks"></i> Búsqueda de Ventas a Créditos</h4>
      </div>

    <div class="form-body">
        <div class="card-body">

    <div class="row">
        <div class="col-md-7">
            <div class="btn-group m-b-20">
            <a class="btn waves-effect waves-light btn-light" href="reportepdf?codsucursal=<?php echo $codsucursal; ?>&tipo=<?php echo encrypt("NOTAS") ?>" target="_blank" rel="noopener noreferrer" data-toggle="tooltip" data-placement="bottom" title="Exportar Pdf"><span class="fa fa-file-pdf-o text-dark"></span> Pdf</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("EXCEL") ?>&tipo=<?php echo encrypt("NOTAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Excel"><span class="fa fa-file-excel-o text-dark"></span> Excel</a>

            <a class="btn waves-effect waves-light btn-light" href="reporteexcel?codsucursal=<?php echo $codsucursal; ?>&documento=<?php echo encrypt("WORD") ?>&tipo=<?php echo encrypt("NOTAS") ?>" data-toggle="tooltip" data-placement="bottom" title="Exportar Word"><span class="fa fa-file-word-o text-dark"></span> Word</a>
          </div>
        </div>
    </div>

    <div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                             <thead>
                             <tr role="row">
                                <th>N°</th>
                                <th>N° de Caja</th>
                                <th>N° de Nota</th>
                                <th>Nº de Documento</th>
                                <th>Descripción de Cliente</th>
                                <th>Motivo de Nota</th>
                                <th>Nº Artic</th>
                                <th>Imp. Total</th>
                                <th>Fecha Emisión</th>
                                <th>Acciones</th>
                             </tr>
                             </thead>
                             <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarNotasCreditos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON NOTAS DE CREDITOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $caja = ($reg[$i]['codcaja'] == '0' ? "**********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['tipodocumento']." Nº: ".$reg[$i]['facturaventa']; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></td>
    <td class="text-center"><?php echo $reg[$i]["observaciones"]; ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td> 
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpago'], 0, '.', '.'); ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechanota'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerNota('<?php echo encrypt($reg[$i]["codnota"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>
    <a href="reportepdf?codnota=<?php echo encrypt($reg[$i]['codnota']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("NOTACREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->
<?php
   }
} 
############################# CARGAR NOTAS DE CREDITOS X SUCURSAL ############################
?>

<?php
############################# CARGAR NOTAS DE CREDITO ############################
if (isset($_GET['CargaNotas'])) { 
?>

<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
                             <thead>
                             <tr role="row">
                                <th>N°</th>
                                <th>N° de Caja</th>
                                <th>N° de Nota</th>
                                <th>Nº de Documento</th>
                                <th>Descripción de Cliente</th>
                                <th>Motivo de Nota</th>
                                <th>Nº Artic</th>
                                <th>Imp. Total</th>
                                <th>Fecha Emisión</th>
                                <th>Acciones</th>
                             </tr>
                             </thead>
                             <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarNotasCreditos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON NOTAS DE CREDITOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$simbolo = ($reg[$i]['simbolo'] == "" ? "" : "<strong>".$reg[$i]['simbolo']."</strong>"); 
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $caja = ($reg[$i]['codcaja'] == '0' ? "**********" : $reg[$i]['nrocaja'].": ".$reg[$i]['nomcaja']); ?></td>
    <td><?php echo $reg[$i]['codfactura']; ?></td>
    <td><?php echo $reg[$i]['tipodocumento']." Nº: ".$reg[$i]['facturaventa']; ?></td>
    <td><?php echo $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']; ?></td>
    <td class="text-center"><?php echo $reg[$i]["observaciones"]; ?></td>
    <td><?php echo number_format($reg[$i]['articulos'], 2, '.', ','); ?></td> 
    <td class="text-center"><?php echo $simbolo.number_format($reg[$i]['totalpago'], 0, '.', '.'); ?></td>
    <td><?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechanota'])); ?></td>
    <td>
    <button type="button" class="btn btn-success btn-rounded" data-placement="left" title="Ver" data-original-title="" data-href="#" data-toggle="modal" data-target=".bs-example-modal-lg" data-backdrop="static" data-keyboard="false" onClick="VerNota('<?php echo encrypt($reg[$i]["codnota"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>')"><i class="fa fa-eye"></i></button>
    <a href="reportepdf?codnota=<?php echo encrypt($reg[$i]['codnota']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("NOTACREDITO"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
} 
############################# CARGAR NOTAS DE CREDITO ############################
?>





<?php
############################# CARGAR PEDIDOS ############################
if (isset($_GET['CargaDetallesPedidos']) && isset($_GET['proceso'])) { 

if(limpiar(decrypt($_GET["proceso"]))=="MESAS"){

?>
<div class="table-responsive"><table id="default_order" class="table table-striped table-bordered border display">
        <thead>
            <tr role="row">
                <th>N°</th>
                <th>Descripción de Cliente</th>
                <th>Nº Pedido</th>
                <th>Platillos</th>
                <th>Tiempo</th>
                <th>Observaciones</th>
                <th><span class="mdi mdi-drag-horizontal"></span></th>
            </tr>
        </thead>
        <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarDetallesPedidos();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PEDIDOS DE PRODUCTOS ACTUALMENTE </center>";
    echo "</div>";    

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$explode = explode("<br>",$reg[$i]['detalles']);

$fecha1 = new DateTime($reg[$i]['fechadetallepedido']);
$fecha2 = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? new DateTime() : new DateTime($reg[$i]['fechadetalleentrega']));
$fecha = $fecha1->diff($fecha2);  
?>
    <tr role="row" class="odd">
    <td><?php echo $a++; ?></td>
    <td><?php echo $cliente = ( $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente'])."<br><small class='text-danger alert-link'><i class='fa fa-clock-o'></i> ".$reg[$i]['nomsala']."<br>".$reg[$i]['nommesa']."</small>"; ?></td>
    <td><?php echo "<strong>".$reg[$i]['codpedido']."-".$reg[$i]['numpedido']."</strong><br><small><i class='fa fa-clock-o'></i> ".date("H:i:s",strtotime($reg[$i]['fechadetallepedido']))."</small>"; ?></td>
    <td>
    <?php
    for($cont=0; $cont<COUNT($explode); $cont++):
    list($cantidad,$producto,$observacion,$salsa,$cocinero) = explode("|",$explode[$cont]);
    if($cocinero == 1){ 
        $status = "<span class='badge badge-danger'><i class='fa fa-exclamation-triangle'></i> PENDIENTE</span>";
    } else if($cocinero == 2){ 
        $status = "<span class='badge badge-info'><i class='fa fa-file'></i> EN PREPARACIÓN</span>";
    } else { 
        $status = "<span class='badge badge-success'><i class='fa fa-check'></i> LISTO</span>";
    }
    ?>
    <p class="mb-0 font-12 alert-link"><?php echo $cantidad."<a class='text-danger font-12'> | </a>".$producto."".$var = (empty($observacion) ? "" : "<a class='text-danger font-12'> | </a>".$observacion)."".$var2 = (empty($salsa) ? "" : "<a class='text-danger font-12'> | </a>".$salsa)."<a class='text-danger font-12'> | </a>".$status; ?></p>
    <?php
    endfor;
    ?>
    </td>
    <td><?php printf('Hace %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s); ?></td>
    <td><?php echo $descripciones = ( $reg[$i]['descripciones'] == '' ? "**********" : $reg[$i]['descripciones']); ?></td>
    <td>
    <a href="reportepdf?codpedido=<?php echo encrypt($reg[$i]['codpedido']); ?>&numpedido=<?php echo encrypt($reg[$i]['numpedido']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("GENERAL"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-warning btn-rounded" title="Imprimir Comanda/Bar"><i class="fa fa-print"></i></button></a>

        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>

<?php } elseif(limpiar(decrypt($_GET["proceso"]))=="DELIVERY"){ ?>

<div class="table-responsive"><table id="default_order" class="table2 table-striped table-bordered border display">
        <thead>
            <tr role="row">
                <th>N°</th>
                <th>Descripción de Cliente</th>
                <th>Nº Pedido</th>
                <th>Platillos</th>
                <th>Tiempo Pedido</th>
                <th>Observaciones</th>
                <th><span class="mdi mdi-drag-horizontal"></span></th>
            </tr>
        </thead>
        <tbody class="BusquedaRapida">

<?php 
$reg = $tra->ListarDetallesPedidos();

if($reg==""){
    
    echo "";   

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$explode = explode("<br>",$reg[$i]['detalles']);

$fecha1 = new DateTime($reg[$i]['fechadetallepedido']);
$fecha2 = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? new DateTime() : new DateTime($reg[$i]['fechadetalleentrega']));
$fecha = $fecha1->diff($fecha2);
?>
    <tr>
    <td><?php echo $a++; ?></td>
    <td><?php echo $cliente = ( $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?><br>
    <?php if($reg[$i]['tipopedido'] == 1){ echo "<small class='text-danger alert-link'><i class='fa fa-clock-o'></i> ".$reg[$i]['nomsala']."<br>".$reg[$i]['nommesa']."</small>"; 
    } elseif($reg[$i]['tipopedido'] == 2 && $reg[$i]['repartidor'] == 0 || $reg[$i]['tipopedido'] == 4 && $reg[$i]['repartidor'] == 0){ echo "<small><i class='fa fa-home'></i> EN ESTABLECIMIENTO</small>"; 
    } elseif($reg[$i]['tipopedido'] == 2 && $reg[$i]['repartidor'] != 0 || $reg[$i]['tipopedido'] == 4 && $reg[$i]['repartidor'] != 0){ echo "<small><i class='fa fa-motorcycle'></i> DELIVERY</small>"; } ?></td>
    <td><?php echo "<strong>".$reg[$i]['numpedido']."</strong><br><small><i class='fa fa-clock-o'></i> ".date("H:i:s",strtotime($reg[$i]['fechadetallepedido']))."</small>"; ?></td>

    <td>
    <?php
    for($cont=0; $cont<COUNT($explode); $cont++):
    list($cantidad,$producto,$observacion,$salsa,$cocinero) = explode("|",$explode[$cont]);
    if($cocinero == 1){ 
        $status = "<span class='badge badge-danger'><i class='fa fa-exclamation-triangle'></i> PENDIENTE</span>";
    } else if($cocinero == 2){ 
        $status = "<span class='badge badge-info'><i class='fa fa-file'></i> EN PREPARACIÓN</span>";
    } else { 
        $status = "<span class='badge badge-success'><i class='fa fa-check'></i> LISTO</span>";
    }
    ?>
    <p class="mb-0 font-12 alert-link"><?php echo $cantidad."<a class='text-danger font-12'> | </a>".$producto."".$var = (empty($observacion) ? "" : "<a class='text-danger font-12'> | </a>".$observacion)."".$var2 = (empty($salsa) ? "" : "<a class='text-danger font-12'> | </a>".$salsa)."<a class='text-danger font-12'> | </a>".$status; ?></p>
    <?php
    endfor;
    ?>
    </td>

    <td><?php printf('Hace %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s); ?></td>
    <td><?php echo $descripciones = ( $reg[$i]['descripciones'] == '' ? "**********" : $reg[$i]['descripciones']); ?></td>
    <td>
    <a href="reportepdf?codpedido=<?php echo encrypt($reg[$i]['codpedido']); ?>&numpedido=<?php echo encrypt($reg[$i]['numpedido']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&&tipo=<?php echo encrypt("GENERAL"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-warning btn-rounded" title="Imprimir Comanda/Bar"><i class="fa fa-print"></i></button></a>

        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>

<?php } elseif(limpiar(decrypt($_GET["proceso"]))=="PEDIDOS"){ ?>

<div class="table-responsive"><table id="default_order" class="table2 table-striped table-bordered border display">
        <thead>
            <tr role="row">
                <th>N°</th>
                <th>Descripción de Cliente</th>
                <th>Platillos</th>
                <th>Fecha para Entrega</th>
                <th>Observaciones</th>
                <th><span class="mdi mdi-drag-horizontal"></span></th>
            </tr>
        </thead>
        <tbody class="BusquedaRapida">
<?php 
$reg = $tra->ListarDetallesPedidos();

if($reg==""){
    
    echo "";   

} else {
 
$a=1;
for($i=0;$i<sizeof($reg);$i++){
$explode = explode("<br>",$reg[$i]['detalles']); 
?>
    <tr>
    <td><?php echo $a++; ?></td>
    <td><?php echo $cliente = ( $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente'])."<br><small class='text-dark alert-link'>".$tlf = ($reg[$i]['tlfcliente'] == "" ? "**********" : "<i class='fa fa-phone'></i> ".$reg[$i]['tlfcliente'])."</small>"; ?></td>
    <td>
    <?php
    for($cont=0; $cont<COUNT($explode); $cont++):
    list($cantidad,$producto,$observacion,$salsa,$cocinero) = explode("|",$explode[$cont]);
    if($cocinero == 1){ 
        $status = "<span class='badge badge-danger'><i class='fa fa-exclamation-triangle'></i> PENDIENTE</span>";
    } else if($cocinero == 2){ 
        $status = "<span class='badge badge-info'><i class='fa fa-file'></i> EN PREPARACIÓN</span>";
    } else { 
        $status = "<span class='badge badge-success'><i class='fa fa-check'></i> LISTO</span>";
    }
    ?>
    <p class="mb-0 font-12 alert-link"><?php echo $cantidad."<a class='text-danger font-12'> | </a>".$producto."".$var = (empty($observacion) ? "" : "<a class='text-danger font-12'> | </a>".$observacion)."".$var2 = (empty($salsa) ? "" : "<a class='text-danger font-12'> | </a>".$salsa)."<a class='text-danger font-12'> | </a>".$status; ?></p>
    <?php
    endfor;
    ?>
    </td>
    <td><?php echo "<span class='text-dark alert-link'>Fecha:</span> ".date("d-m-Y",strtotime($reg[$i]['fechaentrega']))."<br> <span class='text-dark alert-link'>Hora:</span>: ".date("H:i:s",strtotime($reg[$i]['fechaentrega'])).""; ?></td>
    <td><?php echo $descripciones = ( $reg[$i]['descripciones'] == '' ? "**********" : $reg[$i]['descripciones']); ?></td>
    <td>
    <a href="reportepdf?codpedido=<?php echo encrypt($reg[$i]['codpedido']); ?>&numpedido=<?php echo encrypt($reg[$i]['numpedido']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&&tipo=<?php echo encrypt("GENERAL"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-warning btn-rounded" title="Imprimir Comanda/Bar"><i class="fa fa-print"></i></button></a>
        </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table></div>
<?php
    }
} 
############################# CARGAR PEDIDOS ############################
?>


<?php
############################# CARGAR MOSTRADOR COCINA NUEVOS ############################
if (isset($_GET['CargaMostradorNuevos']) && isset($_GET['proceso'])) { 

$proceso = limpiar($_GET['proceso']);

$reg = $tra->ListarMostradorNuevos(); ?>

<!-- Row -->
<?php if(decrypt($_GET['proceso']) == "TODOS" || decrypt($_GET['proceso']) == "MESAS" || decrypt($_GET['proceso']) == "DELIVERY"){ ?><div class="page-content container-fluid note-has-grid">
    <ul class="nav nav-pills p-3 bg-white mb-3 align-items-center">
        <h4 class="font-medium text-uppercase mb-0"><i class="fa fa-file-text"></i> NUEVOS PEDIDOS</h4>
    </ul>
</div><?php } ?>
<!-- End Row -->

<?php if(empty($reg)){
    
    if(decrypt($_GET['proceso']) == "ENTREGADOS" || decrypt($_GET['proceso']) == "GENERAL"){
        echo "<div class='alert alert-danger'>";
        echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PEDIDOS ".decrypt($proceso)." DE PRODUCTOS A COCINA ACTUALMENTE </center>";
        echo "</div>";
    }    

} else if(decrypt($_GET['proceso']) == "GENERAL"){
?>
<!-- Row--> 
<div class="row">
    <!-- Column -->
    <div class="col-lg-12">
        <div class="card">
            <div class="bg-warning p-2">
                <div class="text-center text-white alert-link">
                  <i class="mdi mdi-reorder-horizontal"></i> TOTAL GENERAL DE PEDIDOS EN COMANDA
                </div>
            </div>

        <div class="card-body">

        <table width="100%">
            <tr class="text-dark alert-link">
              <td>DESCRIPCIÓN DE PRODUCTO</td>
              <td>CANTIDAD</td>
            </tr>
            <?php 
            $a=1;
            for($i=0;$i<sizeof($reg);$i++){ 
            ?>
            <tr>
              <td class="text-dark alert-link"><?php echo $reg[$i]['producto']; ?></td>
              <td class="text-danger alert-link"><?php echo number_format($reg[$i]['cantidad_pedidos'], 2, '.', '.'); ?></td>
            </tr>
        <?php } ?>
        </table>

        </div>

        </div>
    </div>
    <!-- Column -->                         
</div>
<!-- Row -->

<?php 

} else { ?>

<!-- Row--> 
<div class="row">

<?php 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 

$fecha1 = new DateTime($reg[$i]['fechadetallepedido']);
$fecha2 = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? new DateTime() : new DateTime($reg[$i]['fechadetalleentrega']));
$fecha = $fecha1->diff($fecha2);
?>
<!-- Column -->
<div class="col-lg-3">
    <div class="card">
        <div class="bg-warning p-2">
          <div class="text-center text-white alert-link">
            <i class="mdi mdi-reorder-horizontal"></i> DETALLE DE PEDIDO 
          </div>
        </div>

        <div class="card-body">
            <h5 class="text-danger alert-link">
                <?php if($reg[$i]['tipopedido'] == 1) { 
                    echo $reg[$i]['nomsala']."<br>".$reg[$i]['nommesa']; 
                } elseif($reg[$i]['tipopedido'] == 2) { 
                    echo "DELIVERY";
                } elseif($reg[$i]['tipopedido'] == 3) { 
                    echo "PEDIDO"; ?><br>
                Fecha para Entrega: <?php echo date("d-m-Y",strtotime($reg[$i]['fechaentrega'])); ?><br>
                Hora para Entrega: <?php echo date("H:i:s",strtotime($reg[$i]['fechaentrega'])); ?><br> 
                <?php } else { 
                    echo "VENTA COTIZADA"; 
                } ?>
            </h5>

            <h5 class="text-dark alert-link"><?php echo $cliente = ( $reg[$i]['codcliente'] == '' || $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?></h5>

            <p class="mb-0 text-danger font-12 alert-link">Recibido: <?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetallepedido'])); ?><br>

            <?php if($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00'){

                printf('Hace: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s); 

            } else { 

            echo "Entregado: ".$entregado = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? "**********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetalleentrega'])));

                printf('<br>Esperado: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s);
            } ?>
            </p>

            <p class="mb-0 mt-2 font-14 alert-link"><?php echo "Pedido: ".$reg[$i]['numpedido']; ?></p><hr>

            <?php
            $explode = explode("<br>",$reg[$i]['detalles']); 
            for($cont=0; $cont<COUNT($explode); $cont++):
            list($cantidad,$producto,$observacion,$salsa) = explode("|",$explode[$cont]);
            ?>

            <p class="mb-0 font-12 alert-link"><?php echo $cantidad."<a class='text-danger font-12'> | </a>".$producto."".$var = (empty($observacion) ? "" : "<a class='text-danger font-12'> | </a>".$observacion)."".$var2 = (empty($salsa) ? "" : "<a class='text-danger font-12'> | </a>".$salsa); ?></p>

            <?php
            endfor;
            ?>

            <?php
            if($reg[$i]['descripciones'] != ""){ 
            ?>
            <p class="mb-0 text-danger font-12 alert-link">Observaciones: <?php echo $reg[$i]['descripciones'] == '' ? "" : "(".$reg[$i]['descripciones'].")"; ?><br>
            <?php } ?>

        <?php if($reg[$i]['cocinero'] == '1'){?>
            <div class="d-flex no-block align-items-center mb-3">
                <div class="ml-auto">
                    <?php if ($_SESSION["acceso"] == "administradorS" || $_SESSION["acceso"] == "cocinero") { ?><button type="button" class="btn btn-info btn-rounded" onClick="PrepararPedidosCocina('<?php echo encrypt($reg[$i]["codpedido"]); ?>','<?php echo encrypt($reg[$i]["numpedido"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["tipopedido"]); ?>','<?php echo $proceso; ?>','<?php echo encrypt("PREPARARPEDIDOCOCINERO"); ?>')" title="Preparar Pedido" ><i class="fa fa-file-text"></i></button><?php } ?>

                    <a href="reportepdf?codpedido=<?php echo encrypt($reg[$i]['codpedido']); ?>&numpedido=<?php echo encrypt($reg[$i]['numpedido']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("COMANDA"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Comanda"><i class="fa fa-print"></i></button></a>
                </div>
            </div>
        <?php } ?>

        </div>
    </div>
</div>
<!-- Column -->
                            
<?php } } ?>
                                 
</div>
<!-- Row -->
<?php
} 
############################# CARGAR MOSTRADOR COCINA NUEVOS ############################
?>

<?php
############################# CARGAR MOSTRADOR COCINA PREPARACION ############################
if (isset($_GET['CargaMostradorPreparacion']) && isset($_GET['proceso'])) { 

$proceso = limpiar($_GET['proceso']);

if(decrypt($_GET['proceso']) == "ENTREGADOS" || decrypt($_GET['proceso']) == "GENERAL"){

    echo "";
    exit;
}

$reg = $tra->ListarMostradorPreparacion(); ?>

<!-- Row -->
<div class="page-content container-fluid note-has-grid">
    <ul class="nav nav-pills p-3 bg-white mb-3 align-items-center">
        <h4 class="font-medium text-uppercase mb-0"><i class="fa fa-file-text"></i> EN PREPARACIÓN</h4>
    </ul>
</div>
<!-- End Row -->

<?php

if($reg==""){
    
    echo "";  

} else {
?>

<!-- Row--> 
<div class="row">

<?php 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 

$fecha1 = new DateTime($reg[$i]['fechadetallepedido']);
$fecha2 = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? new DateTime() : new DateTime($reg[$i]['fechadetalleentrega']));
$fecha = $fecha1->diff($fecha2);
?>
<!-- Column -->
<div class="col-lg-3">
    <div class="card">
        <div class="bg-warning p-2">
          <div class="text-center text-white alert-link">
            <i class="mdi mdi-reorder-horizontal"></i> DETALLE DE PEDIDO 
          </div>
        </div>

        <div class="card-body">
            <h5 class="text-danger alert-link">
                <?php if($reg[$i]['tipopedido'] == 1) { 
                    echo $reg[$i]['nomsala']."<br>".$reg[$i]['nommesa']; 
                } elseif($reg[$i]['tipopedido'] == 2) { 
                    echo "DELIVERY";
                } elseif($reg[$i]['tipopedido'] == 3) { 
                    echo "PEDIDO"; ?><br>
                Fecha para Entrega: <?php echo date("d-m-Y",strtotime($reg[$i]['fechaentrega'])); ?><br>
                Hora para Entrega: <?php echo date("H:i:s",strtotime($reg[$i]['fechaentrega'])); ?><br> 
                <?php } else { 
                    echo "VENTA COTIZADA"; 
                } ?>
            </h5>

            <h5 class="text-dark alert-link"><?php echo $cliente = ( $reg[$i]['codcliente'] == '' || $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?></h5>

            <p class="mb-0 text-danger font-12 alert-link">Recibido: <?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetallepedido'])); ?><br>

            <?php if($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00'){

                printf('Hace: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s); 

            } else { 

            echo "Entregado: ".$entregado = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? "**********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetalleentrega'])));

                printf('<br>Esperado: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s);
            } ?>
            </p>

            <p class="mb-0 mt-2 font-14 alert-link"><?php echo "Pedido: ".$reg[$i]['numpedido']; ?></p><hr>

            <?php
            $explode = explode("<br>",$reg[$i]['detalles']); 
            for($cont=0; $cont<COUNT($explode); $cont++):
            list($cantidad,$producto,$observacion,$salsa) = explode("|",$explode[$cont]);
            ?>

            <p class="mb-0 font-12 alert-link"><?php echo $cantidad."<a class='text-danger font-12'> | </a>".$producto."".$var = (empty($observacion) ? "" : "<a class='text-danger font-12'> | </a>".$observacion)."".$var2 = (empty($salsa) ? "" : "<a class='text-danger font-12'> | </a>".$salsa); ?></p>

            <?php
            endfor;
            ?>

            <?php
            if($reg[$i]['descripciones'] != ""){ 
            ?>
            <p class="mb-0 text-danger font-12 alert-link">Observaciones: <?php echo $reg[$i]['descripciones'] == '' ? "" : "(".$reg[$i]['descripciones'].")"; ?><br>
            <?php } ?>

        <?php if($reg[$i]['cocinero'] == '2'){?>
            <div class="d-flex no-block align-items-center mb-3">
                <div class="ml-auto">
                    <?php if ($_SESSION["acceso"] == "administradorS" || $_SESSION["acceso"] == "cocinero") { ?><button type="button" class="btn btn-info btn-rounded" onClick="EntregarPedidosCocina('<?php echo encrypt($reg[$i]["codpedido"]); ?>','<?php echo encrypt($reg[$i]["numpedido"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt($reg[$i]["codmesa"]); ?>','<?php echo encrypt($reg[$i]["tipopedido"]); ?>','<?php echo $proceso; ?>','<?php echo encrypt("ENTREGARPEDIDOCOCINERO"); ?>')" title="Entregar Pedido" ><i class="fa fa-refresh"></i></button><?php } ?>

                    <a href="reportepdf?codpedido=<?php echo encrypt($reg[$i]['codpedido']); ?>&numpedido=<?php echo encrypt($reg[$i]['numpedido']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("COMANDA"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Comanda"><i class="fa fa-print"></i></button></a>
                </div>
            </div>
        <?php } ?>

        </div>
    </div>
</div>
<!-- Column -->
                            
<?php } } ?>
                                 
</div>
<!-- Row -->
<?php
} 
############################# CARGAR MOSTRADOR COCINA PREPARACION ############################
?>







<?php
############################# CARGAR BAR NUEVOS ############################
if (isset($_GET['CargaBarNuevos']) && isset($_GET['proceso'])) { 

$proceso = limpiar($_GET['proceso']);

$reg = $tra->ListarBarNuevos(); ?>

<!-- Row -->
<?php if(decrypt($_GET['proceso']) == "TODOS" || decrypt($_GET['proceso']) == "MESAS" || decrypt($_GET['proceso']) == "DELIVERY"){ ?><div class="page-content container-fluid note-has-grid">
    <ul class="nav nav-pills p-3 bg-white mb-3 align-items-center">
        <h4 class="font-medium text-uppercase mb-0"><i class="fa fa-file-text"></i> NUEVOS PEDIDOS</h4>
    </ul>
</div><?php } ?>
<!-- End Row -->

<?php if(empty($reg)){
    
    if(decrypt($_GET['proceso']) == "ENTREGADOS" || decrypt($_GET['proceso']) == "GENERAL"){
        echo "<div class='alert alert-danger'>";
        echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PEDIDOS ".decrypt($proceso)." DE PRODUCTOS A BARRA ACTUALMENTE </center>";
        echo "</div>";
    }    

} else if(decrypt($_GET['proceso']) == "GENERAL"){
?>
<!-- Row--> 
<div class="row">
    <!-- Column -->
    <div class="col-lg-12">
        <div class="card">
            <div class="bg-warning p-2">
                <div class="text-center text-white alert-link">
                  <i class="mdi mdi-reorder-horizontal"></i> TOTAL GENERAL DE PEDIDOS EN BARRA
                </div>
            </div>

        <div class="card-body">

        <table width="100%">
            <tr class="text-dark alert-link">
              <td>DESCRIPCIÓN DE PRODUCTO</td>
              <td>CANTIDAD</td>
            </tr>
            <?php 
            $a=1;
            for($i=0;$i<sizeof($reg);$i++){ 
            ?>
            <tr>
              <td class="text-dark alert-link"><?php echo $reg[$i]['producto']; ?></td>
              <td class="text-danger alert-link"><?php echo number_format($reg[$i]['cantidad_pedidos'], 2, '.', '.'); ?></td>
            </tr>
        <?php } ?>
        </table>

        </div>

        </div>
    </div>
    <!-- Column -->                         
</div>
<!-- Row -->

<?php } else { ?>

<!-- Row--> 
<div class="row">

<?php 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 

$fecha1 = new DateTime($reg[$i]['fechadetallepedido']);
$fecha2 = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? new DateTime() : new DateTime($reg[$i]['fechadetalleentrega']));
$fecha = $fecha1->diff($fecha2);
?>
<!-- Column -->
<div class="col-lg-3">
    <div class="card">
        <div class="bg-warning p-2">
          <div class="text-center text-white alert-link">
            <i class="mdi mdi-reorder-horizontal"></i> DETALLE DE PEDIDO 
          </div>
        </div>

        <div class="card-body">
            <h5 class="text-danger alert-link">
                <?php if($reg[$i]['tipopedido'] == 1) { 
                    echo $reg[$i]['nomsala']."<br>".$reg[$i]['nommesa']; 
                } elseif($reg[$i]['tipopedido'] == 2) { 
                    echo "DELIVERY";
                } elseif($reg[$i]['tipopedido'] == 3) { 
                    echo "PEDIDO"; ?><br>
                Fecha para Entrega: <?php echo date("d-m-Y",strtotime($reg[$i]['fechaentrega'])); ?><br>
                Hora para Entrega: <?php echo date("H:i:s",strtotime($reg[$i]['fechaentrega'])); ?><br> 
                <?php } else { 
                    echo "VENTA COTIZADA"; 
                } ?>
            </h5>

            <h5 class="text-dark alert-link"><?php echo $cliente = ( $reg[$i]['codcliente'] == '' || $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?></h5>

            <p class="mb-0 text-danger font-12 alert-link">Recibido: <?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetallepedido'])); ?><br>

            <?php if($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00'){

                printf('Hace: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s); 

            } else { 

            echo "Entregado: ".$entregado = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? "**********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetalleentrega'])));

                printf('<br>Esperado: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s);
            } ?>
            </p>

            <p class="mb-0 mt-2 font-14 alert-link"><?php echo "Pedido: ".$reg[$i]['numpedido']; ?></p><hr>

            <?php
            $explode = explode("<br>",$reg[$i]['detalles']); 
            for($cont=0; $cont<COUNT($explode); $cont++):
            list($cantidad,$producto,$observacion,$salsa) = explode("|",$explode[$cont]);
            ?>

            <p class="mb-0 font-12 alert-link"><?php echo $cantidad."<a class='text-danger font-12'> | </a>".$producto."".$var = (empty($observacion) ? "" : "<a class='text-danger font-12'> | </a>".$observacion)."".$var2 = (empty($salsa) ? "" : "<a class='text-danger font-12'> | </a>".$salsa); ?></p>

            <?php
            endfor;
            ?>

            <?php
            if($reg[$i]['descripciones'] != ""){ 
            ?>
            <p class="mb-0 text-danger font-12 alert-link">Observaciones: <?php echo $reg[$i]['descripciones'] == '' ? "" : "(".$reg[$i]['descripciones'].")"; ?><br>
            <?php } ?>

        <?php if($reg[$i]['cocinero'] == '1'){?>
            <div class="d-flex no-block align-items-center mb-3">
                <div class="ml-auto">
                    <?php if ($_SESSION["acceso"] == "administradorS" || $_SESSION["acceso"] == "bar") { ?><button type="button" class="btn btn-info btn-rounded" onClick="PrepararPedidosBar('<?php echo encrypt($reg[$i]["codpedido"]); ?>','<?php echo encrypt($reg[$i]["numpedido"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["tipopedido"]); ?>','<?php echo $proceso; ?>','<?php echo encrypt("PREPARARPEDIDOBAR"); ?>')" title="Preparar Pedido" ><i class="fa fa-file-text"></i></button><?php } ?>

                    <a href="reportepdf?codpedido=<?php echo encrypt($reg[$i]['codpedido']); ?>&numpedido=<?php echo encrypt($reg[$i]['numpedido']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("BAR"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Comanda"><i class="fa fa-print"></i></button></a>
                </div>
            </div>
        <?php } ?>

        </div>
    </div>
</div>
<!-- Column -->
                            
<?php } } ?>
                                 
</div>
<!-- Row -->
<?php
} 
############################# CARGAR BAR NUEVOS ############################
?>

<?php
############################# CARGAR MOSTRADOR BAR PREPARACION ############################
if (isset($_GET['CargaBarPreparacion']) && isset($_GET['proceso'])) { 

$proceso = limpiar($_GET['proceso']);

if(decrypt($_GET['proceso']) == "ENTREGADOS" || decrypt($_GET['proceso']) == "GENERAL"){

    echo "";
    exit;
}

$reg = $tra->ListarBarPreparacion(); ?>

<!-- Row -->
<div class="page-content container-fluid note-has-grid">
    <ul class="nav nav-pills p-3 bg-white mb-3 align-items-center">
        <h4 class="font-medium text-uppercase mb-0"><i class="fa fa-file-text"></i> EN PREPARACIÓN</h4>
    </ul>
</div>
<!-- End Row -->

<?php

if($reg==""){
    
    echo "";  

} else {
?>

<!-- Row--> 
<div class="row">

<?php 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 

$fecha1 = new DateTime($reg[$i]['fechadetallepedido']);
$fecha2 = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? new DateTime() : new DateTime($reg[$i]['fechadetalleentrega']));
$fecha = $fecha1->diff($fecha2);
?>
<!-- Column -->
<div class="col-lg-3">
    <div class="card">
        <div class="bg-warning p-2">
          <div class="text-center text-white alert-link">
            <i class="mdi mdi-reorder-horizontal"></i> DETALLE DE PEDIDO 
          </div>
        </div>

        <div class="card-body">
            <h5 class="text-danger alert-link">
                <?php if($reg[$i]['tipopedido'] == 1) { 
                    echo $reg[$i]['nomsala']."<br>".$reg[$i]['nommesa']; 
                } elseif($reg[$i]['tipopedido'] == 2) { 
                    echo "DELIVERY";
                } elseif($reg[$i]['tipopedido'] == 3) { 
                    echo "PEDIDO"; ?><br>
                Fecha para Entrega: <?php echo date("d-m-Y",strtotime($reg[$i]['fechaentrega'])); ?><br>
                Hora para Entrega: <?php echo date("H:i:s",strtotime($reg[$i]['fechaentrega'])); ?><br> 
                <?php } else { 
                    echo "VENTA COTIZADA"; 
                } ?>
            </h5>

            <h5 class="text-dark alert-link"><?php echo $cliente = ( $reg[$i]['codcliente'] == '' || $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?></h5>

            <p class="mb-0 text-danger font-12 alert-link">Recibido: <?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetallepedido'])); ?><br>

            <?php if($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00'){

                printf('Hace: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s); 

            } else { 

            echo "Entregado: ".$entregado = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? "**********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetalleentrega'])));

                printf('<br>Esperado: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s);
            } ?>
            </p>

            <p class="mb-0 mt-2 font-14 alert-link"><?php echo "Pedido: ".$reg[$i]['numpedido']; ?></p><hr>

            <?php
            $explode = explode("<br>",$reg[$i]['detalles']); 
            for($cont=0; $cont<COUNT($explode); $cont++):
            list($cantidad,$producto,$observacion,$salsa) = explode("|",$explode[$cont]);
            ?>

            <p class="mb-0 font-12 alert-link"><?php echo $cantidad."<a class='text-danger font-12'> | </a>".$producto."".$var = (empty($observacion) ? "" : "<a class='text-danger font-12'> | </a>".$observacion)."".$var2 = (empty($salsa) ? "" : "<a class='text-danger font-12'> | </a>".$salsa); ?></p>

            <?php
            endfor;
            ?>

            <?php
            if($reg[$i]['descripciones'] != ""){ 
            ?>
            <p class="mb-0 text-danger font-12 alert-link">Observaciones: <?php echo $reg[$i]['descripciones'] == '' ? "" : "(".$reg[$i]['descripciones'].")"; ?><br>
            <?php } ?>

        <?php if($reg[$i]['cocinero'] == '2'){?>
            <div class="d-flex no-block align-items-center mb-3">
                <div class="ml-auto">
                    <?php if ($_SESSION["acceso"] == "administradorS" || $_SESSION["acceso"] == "bar") { ?><button type="button" class="btn btn-info btn-rounded" onClick="EntregarPedidosBar('<?php echo encrypt($reg[$i]["codpedido"]); ?>','<?php echo encrypt($reg[$i]["numpedido"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt($reg[$i]["codmesa"]); ?>','<?php echo encrypt($reg[$i]["tipopedido"]); ?>','<?php echo $proceso; ?>','<?php echo encrypt("ENTREGARPEDIDOBAR"); ?>')" title="Entregar Pedido" ><i class="fa fa-refresh"></i></button><?php } ?>

                    <a href="reportepdf?codpedido=<?php echo encrypt($reg[$i]['codpedido']); ?>&numpedido=<?php echo encrypt($reg[$i]['numpedido']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("BAR"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Comanda"><i class="fa fa-print"></i></button></a>
                </div>
            </div>
        <?php } ?>

        </div>
    </div>
</div>
<!-- Column -->
                            
<?php } } ?>
                                 
</div>
<!-- Row -->
<?php
} 
############################# CARGAR MOSTRADOR BAR PREPARACION ############################
?>







<?php
############################# CARGAR REPOSTERIA NUEVOS ############################
if (isset($_GET['CargaReposteriaNuevos']) && isset($_GET['proceso'])) { 

$proceso = limpiar($_GET['proceso']);

$reg = $tra->ListarReposteriaNuevos(); ?>

<!-- Row -->
<?php if(decrypt($_GET['proceso']) == "TODOS" || decrypt($_GET['proceso']) == "MESAS" || decrypt($_GET['proceso']) == "DELIVERY"){ ?><div class="page-content container-fluid note-has-grid">
    <ul class="nav nav-pills p-3 bg-white mb-3 align-items-center">
        <h4 class="font-medium text-uppercase mb-0"><i class="fa fa-file-text"></i> NUEVOS PEDIDOS</h4>
    </ul>
</div><?php } ?>
<!-- End Row -->

<?php if(empty($reg)){
    
    if(decrypt($_GET['proceso']) == "ENTREGADOS" || decrypt($_GET['proceso']) == "GENERAL"){
        echo "<div class='alert alert-danger'>";
        echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
        echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PEDIDOS ".decrypt($proceso)." DE PRODUCTOS A REPOSTERIA ACTUALMENTE </center>";
        echo "</div>";
    }    

} else if(decrypt($_GET['proceso']) == "GENERAL"){
?>
<!-- Row--> 
<div class="row">
    <!-- Column -->
    <div class="col-lg-12">
        <div class="card">
            <div class="bg-warning p-2">
                <div class="text-center text-white alert-link">
                  <i class="mdi mdi-reorder-horizontal"></i> TOTAL GENERAL DE PEDIDOS EN REPOSTERIA
                </div>
            </div>

        <div class="card-body">

        <table width="100%">
            <tr class="text-dark alert-link">
              <td>DESCRIPCIÓN DE PRODUCTO</td>
              <td>CANTIDAD</td>
            </tr>
            <?php 
            $a=1;
            for($i=0;$i<sizeof($reg);$i++){ 
            ?>
            <tr>
              <td class="text-dark alert-link"><?php echo $reg[$i]['producto']; ?></td>
              <td class="text-danger alert-link"><?php echo number_format($reg[$i]['cantidad_pedidos'], 2, '.', '.'); ?></td>
            </tr>
        <?php } ?>
        </table>

        </div>

        </div>
    </div>
    <!-- Column -->                         
</div>
<!-- Row -->

<?php } else { ?>

<!-- Row--> 
<div class="row">

<?php 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 

$fecha1 = new DateTime($reg[$i]['fechadetallepedido']);
$fecha2 = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? new DateTime() : new DateTime($reg[$i]['fechadetalleentrega']));
$fecha = $fecha1->diff($fecha2);
?>
<!-- Column -->
<div class="col-lg-3">
    <div class="card">
        <div class="bg-warning p-2">
          <div class="text-center text-white alert-link">
            <i class="mdi mdi-reorder-horizontal"></i> DETALLE DE PEDIDO 
          </div>
        </div>

        <div class="card-body">
            <h5 class="text-danger alert-link">
                <?php if($reg[$i]['tipopedido'] == 1) { 
                    echo $reg[$i]['nomsala']."<br>".$reg[$i]['nommesa']; 
                } elseif($reg[$i]['tipopedido'] == 2) { 
                    echo "DELIVERY";
                } elseif($reg[$i]['tipopedido'] == 3) { 
                    echo "PEDIDO"; ?><br>
                Fecha para Entrega: <?php echo date("d-m-Y",strtotime($reg[$i]['fechaentrega'])); ?><br>
                Hora para Entrega: <?php echo date("H:i:s",strtotime($reg[$i]['fechaentrega'])); ?><br> 
                <?php } else { 
                    echo "VENTA COTIZADA"; 
                } ?>
            </h5>

            <h5 class="text-dark alert-link"><?php echo $cliente = ( $reg[$i]['codcliente'] == '' || $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?></h5>

            <p class="mb-0 text-danger font-12 alert-link">Recibido: <?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetallepedido'])); ?><br>

            <?php if($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00'){

                printf('Hace: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s); 

            } else { 

            echo "Entregado: ".$entregado = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? "**********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetalleentrega'])));

                printf('<br>Esperado: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s);
            } ?>
            </p>

            <p class="mb-0 mt-2 font-14 alert-link"><?php echo "Pedido: ".$reg[$i]['numpedido']; ?></p><hr>

            <?php
            $explode = explode("<br>",$reg[$i]['detalles']); 
            for($cont=0; $cont<COUNT($explode); $cont++):
            list($cantidad,$producto,$observacion,$salsa) = explode("|",$explode[$cont]);
            ?>

            <p class="mb-0 font-12 alert-link"><?php echo $cantidad."<a class='text-danger font-12'> | </a>".$producto."".$var = (empty($observacion) ? "" : "<a class='text-danger font-12'> | </a>".$observacion)."".$var2 = (empty($salsa) ? "" : "<a class='text-danger font-12'> | </a>".$salsa); ?></p>

            <?php
            endfor;
            ?>

            <?php
            if($reg[$i]['descripciones'] != ""){ 
            ?>
            <p class="mb-0 text-danger font-12 alert-link">Observaciones: <?php echo $reg[$i]['descripciones'] == '' ? "" : "(".$reg[$i]['descripciones'].")"; ?><br>
            <?php } ?>

        <?php if($reg[$i]['cocinero'] == '1'){?>
            <div class="d-flex no-block align-items-center mb-3">
                <div class="ml-auto">
                    <?php if ($_SESSION["acceso"] == "administradorS" || $_SESSION["acceso"] == "reposteria") { ?><button type="button" class="btn btn-info btn-rounded" onClick="PrepararPedidosReposteria('<?php echo encrypt($reg[$i]["codpedido"]); ?>','<?php echo encrypt($reg[$i]["numpedido"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["tipopedido"]); ?>','<?php echo $proceso; ?>','<?php echo encrypt("PREPARARPEDIDOREPOSTERIA"); ?>')" title="Preparar Pedido" ><i class="fa fa-file-text"></i></button><?php } ?>

                    <a href="reportepdf?codpedido=<?php echo encrypt($reg[$i]['codpedido']); ?>&numpedido=<?php echo encrypt($reg[$i]['numpedido']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("REPOSTERIA"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Comanda"><i class="fa fa-print"></i></button></a>
                </div>
            </div>
        <?php } ?>

        </div>
    </div>
</div>
<!-- Column -->
                            
<?php } } ?>
                                 
</div>
<!-- Row -->
<?php
} 
############################# CARGAR REPOSTERIA NUEVOS ############################
?>

<?php
############################# CARGAR MOSTRADOR REPOSTERIA PREPARACION ############################
if (isset($_GET['CargaReposteriaPreparacion']) && isset($_GET['proceso'])) { 

$proceso = limpiar($_GET['proceso']);

if(decrypt($_GET['proceso']) == "ENTREGADOS" || decrypt($_GET['proceso']) == "GENERAL"){

    echo "";
    exit;
}

$reg = $tra->ListarReposteriaPreparacion(); ?>

<!-- Row -->
<div class="page-content container-fluid note-has-grid">
    <ul class="nav nav-pills p-3 bg-white mb-3 align-items-center">
        <h4 class="font-medium text-uppercase mb-0"><i class="fa fa-file-text"></i> EN PREPARACIÓN</h4>
    </ul>
</div>
<!-- End Row -->

<?php

if($reg==""){
    
    echo "";  

} else {
?>

<!-- Row--> 
<div class="row">

<?php 
$a=1;
for($i=0;$i<sizeof($reg);$i++){ 

$fecha1 = new DateTime($reg[$i]['fechadetallepedido']);
$fecha2 = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? new DateTime() : new DateTime($reg[$i]['fechadetalleentrega']));
$fecha = $fecha1->diff($fecha2);
?>
<!-- Column -->
<div class="col-lg-3">
    <div class="card">
        <div class="bg-warning p-2">
          <div class="text-center text-white alert-link">
            <i class="mdi mdi-reorder-horizontal"></i> DETALLE DE PEDIDO 
          </div>
        </div>

        <div class="card-body">
            <h5 class="text-danger alert-link">
                <?php if($reg[$i]['tipopedido'] == 1) { 
                    echo $reg[$i]['nomsala']."<br>".$reg[$i]['nommesa']; 
                } elseif($reg[$i]['tipopedido'] == 2) { 
                    echo "DELIVERY";
                } elseif($reg[$i]['tipopedido'] == 3) { 
                    echo "PEDIDO"; ?><br>
                Fecha para Entrega: <?php echo date("d-m-Y",strtotime($reg[$i]['fechaentrega'])); ?><br>
                Hora para Entrega: <?php echo date("H:i:s",strtotime($reg[$i]['fechaentrega'])); ?><br> 
                <?php } else { 
                    echo "VENTA COTIZADA"; 
                } ?>
            </h5>

            <h5 class="text-dark alert-link"><?php echo $cliente = ( $reg[$i]['codcliente'] == '' || $reg[$i]['codcliente'] == '0' ? "CONSUMIDOR FINAL" : $reg[$i]['nomcliente']); ?></h5>

            <p class="mb-0 text-danger font-12 alert-link">Recibido: <?php echo date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetallepedido'])); ?><br>

            <?php if($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00'){

                printf('Hace: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s); 

            } else { 

            echo "Entregado: ".$entregado = ($reg[$i]['fechadetalleentrega'] == '0000-00-00 00:00:00' ? "**********" : date("d-m-Y H:i:s",strtotime($reg[$i]['fechadetalleentrega'])));

                printf('<br>Esperado: %d horas, %d minutos, %d segundos ', $fecha->h, $fecha->i, $fecha->s);
            } ?>
            </p>

            <p class="mb-0 mt-2 font-14 alert-link"><?php echo "Pedido: ".$reg[$i]['numpedido']; ?></p><hr>

            <?php
            $explode = explode("<br>",$reg[$i]['detalles']); 
            for($cont=0; $cont<COUNT($explode); $cont++):
            list($cantidad,$producto,$observacion,$salsa) = explode("|",$explode[$cont]);
            ?>

            <p class="mb-0 font-12 alert-link"><?php echo $cantidad."<a class='text-danger font-12'> | </a>".$producto."".$var = (empty($observacion) ? "" : "<a class='text-danger font-12'> | </a>".$observacion)."".$var2 = (empty($salsa) ? "" : "<a class='text-danger font-12'> | </a>".$salsa); ?></p>

            <?php
            endfor;
            ?>

            <?php
            if($reg[$i]['descripciones'] != ""){ 
            ?>
            <p class="mb-0 text-danger font-12 alert-link">Observaciones: <?php echo $reg[$i]['descripciones'] == '' ? "" : "(".$reg[$i]['descripciones'].")"; ?><br>
            <?php } ?>

        <?php if($reg[$i]['cocinero'] == '2'){?>
            <div class="d-flex no-block align-items-center mb-3">
                <div class="ml-auto">
                    <?php if ($_SESSION["acceso"] == "administradorS" || $_SESSION["acceso"] == "reposteria") { ?><button type="button" class="btn btn-info btn-rounded" onClick="EntregarPedidosReposteria('<?php echo encrypt($reg[$i]["codpedido"]); ?>','<?php echo encrypt($reg[$i]["numpedido"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["codcliente"]); ?>','<?php echo encrypt($reg[$i]["codmesa"]); ?>','<?php echo encrypt($reg[$i]["tipopedido"]); ?>','<?php echo $proceso; ?>','<?php echo encrypt("ENTREGARPEDIDOREPOSTERIA"); ?>')" title="Entregar Pedido" ><i class="fa fa-refresh"></i></button><?php } ?>

                    <a href="reportepdf?codpedido=<?php echo encrypt($reg[$i]['codpedido']); ?>&numpedido=<?php echo encrypt($reg[$i]['numpedido']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt("REPOSTERIA"); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Comanda"><i class="fa fa-print"></i></button></a>
                </div>
            </div>
        <?php } ?>

        </div>
    </div>
</div>
<!-- Column -->
                            
<?php } } ?>
                                 
</div>
<!-- Row -->
<?php
} 
############################# CARGAR MOSTRADOR REPOSTERIA PREPARACION ############################
?>






<?php
############################# CARGAR MOSTRADOR DELIVERY ############################
if (isset($_GET['CargaDelivery']) && isset($_GET['proceso'])) { 

$proceso = limpiar($_GET['proceso']);

$reg = $tra->ListarDelivery();

if($reg==""){
    
    echo "<div class='alert alert-danger'>";
    echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    echo "<center><span class='fa fa-info-circle'></span> NO SE ENCONTRARON PEDIDOS ".decrypt($proceso)." DE PRODUCTOS PARA ENTREGAS ACTUALMENTE </center>";
    echo "</div>";    

} else { ?>

<!-- Row--> 
<div class="row">


<?php 
$a=1;
for($i=0;$i<sizeof($reg);$i++){  
?>

         <!-- Column -->
            <div class="col-lg-4">
              <div class="card">
                <div class="bg-warning p-2">
                  <div class="text-center text-white">
                    <i class="mdi mdi-reorder-horizontal"></i> DETALLE DE DELIVERY
                  </div>
                </div>

                <div class="card-body">
                  <h5 class="font-normal text-danger"><?php echo $nombre = ( $reg[$i]['codcliente'] == '0' ? "<strong>CLIENTE: ******</strong>" : $reg[$i]['nomcliente'])."<br>".$direccion = ( $reg[$i]['codcliente'] == '0' ? "DIRECC: ******" : $reg[$i]['direccliente'])."<br> ".$tlf = ( $reg[$i]['tlfcliente'] == '' ? "Nº TLF: ******" : $reg[$i]['tlfcliente']); ?></h5>

                  <p class="mb-0 mt-2 font-12"><?php echo "<span style='font-size:12px;'><strong>Pedido #".$a++."<br>".$reg[$i]['detalles']."</strong></span>"; ?></p>

                <?php if($reg[$i]['tipodocumento'] != '0' && $reg[$i]['entregado'] == '1'){ ?>
                  
                  <div class="d-flex no-block align-items-center mb-3">
                    <div class="ml-auto">
                      <button type="button" class="btn btn-info btn-rounded" onClick="EntregarDelivery('<?php echo encrypt($reg[$i]["codventa"]); ?>','<?php echo encrypt($reg[$i]["codsucursal"]); ?>','<?php echo encrypt($reg[$i]["delivery"]); ?>','<?php echo $proceso; ?>','<?php echo encrypt("PEDIDODELIVERY"); ?>')" title="Entregar Pedido" ><i class="fa fa-refresh"></i></button>

                      <a href="reportepdf?codventa=<?php echo encrypt($reg[$i]['codventa']); ?>&codsucursal=<?php echo encrypt($reg[$i]['codsucursal']); ?>&tipo=<?php echo encrypt($reg[$i]['tipodocumento']); ?>" target="_blank" rel="noopener noreferrer"><button type="button" class="btn btn-secondary btn-rounded" title="Imprimir Pdf"><i class="fa fa-print"></i></button></a>
                    </div>
                  </div>

                <?php } ?>

                </div>
              </div>
            </div>
            <!-- Column -->

            <?php } } ?>
                                 
      </div>
    <!-- Row -->
 <?php
   } 
############################# CARGAR MOSTRADOR DELIVERY ############################
?>

<!-- Datatables-->
  <script src="assets/plugins/datatables/dataTables.min.js"></script>
  <script src="assets/plugins/datatables/datatable-basic.init.js"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      $('#datatable').dataTable();
      $('#default_order').dataTable();
    } );
  </script>


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