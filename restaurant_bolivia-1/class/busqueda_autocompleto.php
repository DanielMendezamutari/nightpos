<?php
include('class.consultas.php');
include_once('funciones_basicas.php');

if (isset($_GET['Busqueda_Categorias'])):

$filtro = $_GET["term"];
$Json = new Json;
$categoria = $Json->BuscaCategoria($filtro);
echo json_encode($categoria);

endif;



if (isset($_GET['Busqueda_Clientes'])):

$filtro = $_GET["term"];
$Json = new Json;
$clientes = $Json->BuscaClientes($filtro);
echo json_encode($clientes);

endif;

if (isset($_GET['Busqueda_Clientes_Sucursal'])):

$filtro  = $_GET["term"];
$filtro2 = decrypt($_GET["term2"]);
$Json = new Json;
$clientes = $Json->BuscaClientesxSucursal($filtro,$filtro2);
echo json_encode($clientes);

endif;



if (isset($_GET['Busqueda_Ingredientes'])):

$filtro = $_GET["term"];
$Json = new Json;
$ingredientes = $Json->BuscaIngredientes($filtro);
echo json_encode($ingredientes);

endif;

if (isset($_GET['Busqueda_Ingredientes_Sucursal'])):

$filtro  = $_GET["term"];
$filtro2 = decrypt($_GET["term2"]);
$Json = new Json;
$ingredientes = $Json->BuscaIngredientesxSucursal($filtro,$filtro2);
echo json_encode($ingredientes);

endif;



if (isset($_GET['Busqueda_Productos'])):

$filtro = $_GET["term"];
$Json = new Json;
$productos  = $Json->BuscaProductos($filtro);
echo json_encode($productos);

endif;

if (isset($_GET['Busqueda_Productos_Sucursal'])):

$filtro  = $_GET["term"];
$filtro2 = decrypt($_GET["term2"]);
$Json = new Json;
$productos = $Json->BuscaProductosxSucursal($filtro,$filtro2);
echo json_encode($productos);

endif;



if (isset($_GET['Busqueda_Combos'])):

$filtro = $_GET["term"];
$Json = new Json;
$combos  = $Json->BuscaCombo($filtro);
echo json_encode($combos);

endif;

if (isset($_GET['Busqueda_Combos_Sucursal'])):

$filtro  = $_GET["term"];
$filtro2 = decrypt($_GET["term2"]);
$Json = new Json;
$combos = $Json->BuscaCombosxSucursal($filtro,$filtro2);
echo json_encode($combos);

endif;



if (isset($_GET['Busqueda_Facturas'])):

$filtro = $_GET["term"];
$filtro2 = decrypt($_GET["term2"]);
$Json = new Json;
$facturas = $Json->BuscaFacturas($filtro,$filtro2);
echo json_encode($facturas);

endif;
?>  