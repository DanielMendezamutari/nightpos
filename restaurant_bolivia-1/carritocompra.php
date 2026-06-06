<?php
//CARRITO DE ENTRADAS DE PRODUCTOS
session_start();
$ObjetoCarrito   = json_decode($_POST['MiCarrito']);
if ($ObjetoCarrito->Codigo=="vaciar") {
    unset($_SESSION["CarritoCompra"]);
} else {
    if (isset($_SESSION['CarritoCompra'])) {
        $carrito=$_SESSION['CarritoCompra'];
        if (isset($ObjetoCarrito->Codigo)) {
            $txtCodigo = $ObjetoCarrito->Codigo;
            $producto= $ObjetoCarrito->Producto;
            $codcategoria = $ObjetoCarrito->Codcategoria;
            $categorias = $ObjetoCarrito->Categorias;
            $precio = $ObjetoCarrito->Precio;
            $precio2 = $ObjetoCarrito->Precio2;
            $descproductofact = $ObjetoCarrito->DescproductoFact;
            $descproducto = $ObjetoCarrito->Descproducto;
            $ivaproducto = $ObjetoCarrito->Ivaproducto;
            $precioconiva = $ObjetoCarrito->Precioconiva;
            $lote = $ObjetoCarrito->Lote;
            $fechaelaboracion = $ObjetoCarrito->Fechaelaboracion;
            $fechaexpiracion = $ObjetoCarrito->Fechaexpiracion;
            $tipo = $ObjetoCarrito->Tipo;
            $cantidad = $ObjetoCarrito->Cantidad;
            $opCantidad = $ObjetoCarrito->opCantidad;

            //$donde  = array_search($txtCodigo, array_column($carrito, 'txtCodigo'));

            /*if ($donde !== FALSE) {
                if ($opCantidad === '=') {
                    $cuanto = $cantidad;
                } else {
                    $cuanto = $carrito[$donde]['cantidad'] + $cantidad;
                }*/

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
                    "txtCodigo"=>$txtCodigo,
                    "producto"=>$producto,
                    "codcategoria"=>$codcategoria,
                    "categorias"=>$categorias,
                    "precio"=>$precio,
                    "precio2"=>$precio2,
                    "descproductofact"=>$descproductofact,
                    "descproducto"=>$descproducto,
                    "ivaproducto"=>$ivaproducto,
                    "precioconiva"=>$precioconiva,
                    "lote"=>$lote,
                    "fechaelaboracion"=>$fechaelaboracion,
                    "fechaexpiracion"=>$fechaexpiracion,
                    "tipo"=>$tipo,
                    "cantidad"=>$cuanto
                );
            } else {
                $carrito[]=array(
                    "txtCodigo"=>$txtCodigo,
                    "producto"=>$producto,
                    "codcategoria"=>$codcategoria,
                    "categorias"=>$categorias,
                    "precio"=>$precio,
                    "precio2"=>$precio2,
                    "descproductofact"=>$descproductofact,
                    "descproducto"=>$descproducto,
                    "ivaproducto"=>$ivaproducto,
                    "precioconiva"=>$precioconiva,
                    "lote"=>$lote,
                    "fechaelaboracion"=>$fechaelaboracion,
                    "fechaexpiracion"=>$fechaexpiracion,
                    "tipo"=>$tipo,
                    "cantidad"=>$cantidad
                );
            }
        }
    } else {
        $txtCodigo = $ObjetoCarrito->Codigo;
        $producto = $ObjetoCarrito->Producto;
        $codcategoria = $ObjetoCarrito->Codcategoria;
        $categorias = $ObjetoCarrito->Categorias;
        $precio = $ObjetoCarrito->Precio;
        $precio2 = $ObjetoCarrito->Precio2;
        $descproductofact = $ObjetoCarrito->DescproductoFact;
        $descproducto = $ObjetoCarrito->Descproducto;
        $ivaproducto = $ObjetoCarrito->Ivaproducto;
        $precioconiva = $ObjetoCarrito->Precioconiva;
        $lote = $ObjetoCarrito->Lote;
        $fechaelaboracion = $ObjetoCarrito->Fechaelaboracion;
        $fechaexpiracion = $ObjetoCarrito->Fechaexpiracion;
        $tipo = $ObjetoCarrito->Tipo;
        $cantidad = $ObjetoCarrito->Cantidad;
        $carrito[] = array(
            "txtCodigo"=>$txtCodigo,
            "producto"=>$producto,
            "codcategoria"=>$codcategoria,
            "categorias"=>$categorias,
            "precio"=>$precio,
            "precio2"=>$precio2,
            "descproductofact"=>$descproductofact,
            "descproducto"=>$descproducto,
            "ivaproducto"=>$ivaproducto,
            "precioconiva"=>$precioconiva,
            "lote"=>$lote,
            "fechaelaboracion"=>$fechaelaboracion,
            "fechaexpiracion"=>$fechaexpiracion,
            "tipo"=>$tipo,
            "cantidad"=>$cantidad
        );
    }
    $carrito = array_values(
        array_filter($carrito, function($v) {
            return $v['cantidad'] > 0;
        })
    );
    $_SESSION['CarritoCompra'] = $carrito;
    echo json_encode($_SESSION['CarritoCompra']);
}
