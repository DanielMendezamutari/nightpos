<?php
session_start();
$ObjetoCarrito   = json_decode($_POST['MiCarrito']);
if ($ObjetoCarrito->Codigo=="vaciar") {
    unset($_SESSION["CarritoCotizacion"]);
} else {
    if (isset($_SESSION['CarritoCotizacion'])) {
        $carrito=$_SESSION['CarritoCotizacion'];
        if (isset($ObjetoCarrito->Codigo)) {
            $id = $ObjetoCarrito->Id;
            $txtCodigo = $ObjetoCarrito->Codigo;
            $producto= $ObjetoCarrito->Producto;
            $codcategoria = $ObjetoCarrito->Codcategoria;
            $categorias = $ObjetoCarrito->Categorias;
            $precio = $ObjetoCarrito->Precio;
            $precio2 = $ObjetoCarrito->Precio2;
            $descproducto = $ObjetoCarrito->Descproducto;
            $ivaproducto = $ObjetoCarrito->Ivaproducto;
            $existencia = $ObjetoCarrito->Existencia;
            $precioconiva = $ObjetoCarrito->Precioconiva;
            $tipo = $ObjetoCarrito->Tipo;
            $observacion = $ObjetoCarrito->Observacion;
            $salsa = $ObjetoCarrito->Salsa;
            $preparado = $ObjetoCarrito->Preparado;
            $cantidad = $ObjetoCarrito->Cantidad;
            $opCantidad = $ObjetoCarrito->opCantidad;

            $donde = -1;
            for($i=0;$i<=count($carrito)-1;$i ++){
                
                if($tipo == $carrito[$i]['tipo'] && $txtCodigo == $carrito[$i]['txtCodigo']){

                    $donde=$i;
                }
            }

            if($donde != -1){
                if ($opCantidad === '=') {
                    $cuanto = $cantidad;
                } else {
                    $cuanto = $carrito[$donde]['cantidad'] + $cantidad;
                }

                $carrito[$donde] = array(
                    "id"=>$id,
                    "txtCodigo"=>$txtCodigo,
                    "producto"=>$producto,
                    "codcategoria"=>$codcategoria,
                    "categorias"=>$categorias,
                    "precio"=>$precio,
                    "precio2"=>$precio2,
                    "descproducto"=>$descproducto,
                    "ivaproducto"=>$ivaproducto,
                    "existencia"=>$existencia,
                    "precioconiva"=>$precioconiva,
                    "tipo"=>$tipo,
                    "observacion"=>$observacion,
                    "salsa"=>$salsa,
                    "preparado"=>$preparado,
                    "cantidad"=>$cuanto
                );
            } else {
                $carrito[]=array(
                    "id"=>$id,
                    "txtCodigo"=>$txtCodigo,
                    "producto"=>$producto,
                    "codcategoria"=>$codcategoria,
                    "categorias"=>$categorias,
                    "precio"=>$precio,
                    "precio2"=>$precio2,
                    "descproducto"=>$descproducto,
                    "ivaproducto"=>$ivaproducto,
                    "existencia"=>$existencia,
                    "precioconiva"=>$precioconiva,
                    "tipo"=>$tipo,
                    "observacion"=>$observacion,
                    "salsa"=>$salsa,
                    "preparado"=>$preparado,
                    "cantidad"=>$cantidad
                );
            }
        }
    } else {
        $id = $ObjetoCarrito->Id;
        $txtCodigo = $ObjetoCarrito->Codigo;
        $producto = $ObjetoCarrito->Producto;
        $codcategoria = $ObjetoCarrito->Codcategoria;
        $categorias = $ObjetoCarrito->Categorias;
        $precio = $ObjetoCarrito->Precio;
        $precio2 = $ObjetoCarrito->Precio2;
        $descproducto = $ObjetoCarrito->Descproducto;
        $ivaproducto = $ObjetoCarrito->Ivaproducto;
        $existencia = $ObjetoCarrito->Existencia;
        $precioconiva = $ObjetoCarrito->Precioconiva;
        $tipo = $ObjetoCarrito->Tipo;
        $observacion = $ObjetoCarrito->Observacion;
        $salsa = $ObjetoCarrito->Salsa;
        $preparado = $ObjetoCarrito->Preparado;
        $cantidad = $ObjetoCarrito->Cantidad;
        $carrito[] = array(
            "id"=>$id,
            "txtCodigo"=>$txtCodigo,
            "producto"=>$producto,
            "codcategoria"=>$codcategoria,
            "categorias"=>$categorias,
            "precio"=>$precio,
            "precio2"=>$precio2,
            "descproducto"=>$descproducto,
            "ivaproducto"=>$ivaproducto,
            "existencia"=>$existencia,
            "precioconiva"=>$precioconiva,
            "tipo"=>$tipo,
            "observacion"=>$observacion,
            "salsa"=>$salsa,
            "preparado"=>$preparado,
            "cantidad"=>$cantidad
        );
    }
    $carrito = array_values(
        array_filter($carrito, function($v) {
            return $v['cantidad'] > 0;
        })
    );
    $_SESSION['CarritoCotizacion'] = $carrito;
    echo json_encode($_SESSION['CarritoCotizacion']);
}
