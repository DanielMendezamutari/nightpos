<?php
isset($_SESSION) or session_start();
require_once("classconexion.php");

class conectorDB extends Db
{
	public function __construct()
    {
        parent::__construct();
    } 	
	
	public function EjecutarSentencia($consulta, $valores = array()){  //funcion principal, ejecuta todas las consultas
		$resultado = false;
		
		if($statement = $this->dbh->prepare($consulta)){  //prepara la consulta
			if(preg_match_all("/(:\w+)/", $consulta, $campo, PREG_PATTERN_ORDER)){ //tomo los nombres de los campos iniciados con :xxxxx
				$campo = array_pop($campo); //inserto en un arreglo
				foreach($campo as $parametro){
					$statement->bindValue($parametro, $valores[substr($parametro,1)]);
				}
			}
			try {
				if (!$statement->execute()) { //si no se ejecuta la consulta...
					print_r($statement->errorInfo()); //imprimir errores
					return false;
				}
				$resultado = $statement->fetchAll(PDO::FETCH_ASSOC); //si es una consulta que devuelve valores los guarda en un arreglo.
				$statement->closeCursor();
			}
			catch(PDOException $e){
				echo "Error de ejecución: \n";
				print_r($e->getMessage());
			}	
		}
		return $resultado;
		$this->dbh = null; //cerramos la conexión
	} /// Termina funcion consultarBD
}/// Termina clase conectorDB

class Json
{
	private $json;

	################################ BUSQUEDA DE CATEGORIAS ################################
	public function BuscaCategoria($filtro){
    $consulta = "SELECT CONCAT(nomcategoria) as label, codcategoria FROM categorias WHERE CONCAT(nomcategoria) LIKE '%".$filtro."%' ORDER BY codcategoria ASC LIMIT 0,10";
			$conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE CATEGORIAS ################################
	
	################################ BUSQUEDA DE MEDIDAS ################################
	public function BuscaMedidas($filtro){
        $consulta = "SELECT CONCAT(nommedida) as label, codmedida FROM medidas WHERE CONCAT(nommedida) LIKE '%".$filtro."%' ORDER BY codmedida ASC LIMIT 0,10";
			$conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE MEDIDAS ################################


	################################ BUSQUEDA DE INGREDIENTES X SUCURSAL ################################
	public function BuscaIngredientesxSucursal($filtro,$filtro2){

		$consulta = "SELECT
		CONCAT(ingredientes.nomingrediente) as label, 
        ingredientes.idingrediente, 
        ingredientes.codingrediente, 
        ingredientes.nomingrediente, 
        ingredientes.codmedida, 
        ROUND(ingredientes.preciocompra, 2) preciocompra, 
        ROUND(ingredientes.precioventa, 2) precioventa, 
        ROUND(ingredientes.cantingrediente, 2) cantingrediente, 
        ingredientes.ivaingrediente, 
        ROUND(ingredientes.descingrediente, 2) descingrediente, 
        ingredientes.lote, 
        ingredientes.fechaexpiracion, 
        medidas.nommedida 
        FROM ingredientes 
        LEFT JOIN medidas ON ingredientes.codmedida = medidas.codmedida
        WHERE CONCAT(ingredientes.codingrediente, '',ingredientes.nomingrediente, '',medidas.nommedida) LIKE '%".$filtro."%'
        AND ingredientes.codsucursal = '".strip_tags($filtro2)."'
        ORDER BY ingredientes.codingrediente 
        ASC LIMIT 0,15";
		$conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE INGREDIENTES X SUCURSAL ################################

	################################ BUSQUEDA DE INGREDIENTES ################################
	public function BuscaIngredientes($filtro){

        $consulta = "SELECT 
        CONCAT(ingredientes.nomingrediente) as label, 
        ingredientes.idingrediente, 
        ingredientes.codingrediente, 
        ingredientes.nomingrediente, 
        ingredientes.codmedida, 
        ROUND(ingredientes.preciocompra, 2) preciocompra, 
        ROUND(ingredientes.precioventa, 2) precioventa, 
        ROUND(ingredientes.cantingrediente, 2) cantingrediente, 
        ingredientes.ivaingrediente, 
        ROUND(ingredientes.descingrediente, 2) descingrediente, 
        ingredientes.lote, 
        ingredientes.fechaexpiracion, 
        medidas.nommedida 
        FROM ingredientes 
        LEFT JOIN medidas ON ingredientes.codmedida = medidas.codmedida 
        WHERE CONCAT(ingredientes.codingrediente, '',ingredientes.nomingrediente, '',ingredientes.nommedida) LIKE '%".$filtro."%' 
        AND ingredientes.codsucursal= '".strip_tags($_SESSION["codsucursal"])."' 
        ORDER BY ingredientes.codingrediente 
        ASC LIMIT 0,15";
        $conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE INGREDIENTES ################################



	################################ BUSQUEDA DE PRODUCTOS X SUCURSAL ################################
	public function BuscaProductosxSucursal($filtro,$filtro2){

		$consulta = "SELECT
		CONCAT(productos.producto) as label, 
        productos.idproducto, 
        productos.codproducto, 
        productos.producto, productos.codcategoria, 
        ROUND(productos.preciocompra, 2) preciocompra, 
        ROUND(productos.precioventa, 2) precioventa, 
        ROUND(productos.existencia, 2) existencia, 
        productos.ivaproducto, 
        ROUND(productos.descproducto, 2) descproducto, 
        productos.preparado, 
        productos.lote, 
        productos.fechaelaboracion, 
        productos.fechaexpiracion, 
        categorias.nomcategoria 
        FROM productos 
        LEFT JOIN categorias ON productos.codcategoria=categorias.codcategoria
        WHERE CONCAT(productos.codproducto, '',productos.producto, '',productos.codigobarra, '',categorias.nomcategoria) LIKE '%".$filtro."%'
        AND productos.codsucursal = '".strip_tags($filtro2)."'
        ORDER BY productos.codproducto 
        ASC LIMIT 0,15";
		$conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE PRODUCTOS X SUCURSAL ################################

	################################ BUSQUEDA DE PRODUCTOS ################################
	public function BuscaProductos($filtro){

        $consulta = "SELECT 
        CONCAT(productos.producto) as label, 
        productos.idproducto, 
        productos.codproducto, 
        productos.producto, 
        productos.codcategoria, 
        ROUND(productos.preciocompra, 2) preciocompra, 
        ROUND(productos.precioventa, 2) precioventa, 
        ROUND(productos.existencia, 2) existencia, 
        productos.ivaproducto, 
        ROUND(productos.descproducto, 2) descproducto, 
        productos.preparado, 
        productos.lote, 
        productos.fechaelaboracion, 
        productos.fechaexpiracion, 
        categorias.nomcategoria 
        FROM productos 
        LEFT JOIN categorias ON productos.codcategoria=categorias.codcategoria
        WHERE CONCAT(productos.codproducto, '',productos.producto, '',productos.codigobarra, '',categorias.nomcategoria) LIKE '%".$filtro."%' 
        AND productos.codsucursal= '".strip_tags($_SESSION["codsucursal"])."' 
        ORDER BY productos.codproducto 
        ASC LIMIT 0,15";
        $conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE PRODUCTOS ################################



	################################ BUSQUEDA DE COMBOS X SUCURSAL ################################
	public function BuscaCombosxSucursal($filtro,$filtro2){

		$consulta = "SELECT
		CONCAT(combos.nomcombo) as label, 
        combos.idcombo, 
        combos.codcombo, 
        combos.nomcombo, 
        ROUND(combos.preciocompra, 2) preciocompra, 
        ROUND(combos.precioventa, 2) precioventa, 
        ROUND(combos.existencia, 2) existencia, 
        combos.ivacombo, 
        ROUND(combos.desccombo, 2) desccombo, 
        combos.preparado 
        FROM combos 
        WHERE CONCAT(combos.codcombo, '',combos.nomcombo) LIKE '%".$filtro."%'
        AND combos.codsucursal = '".strip_tags($filtro2)."'
        ORDER BY combos.codcombo 
        ASC LIMIT 0,15";
		$conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE COMBOS X SUCURSAL ################################

	################################ BUSQUEDA DE COMBOS ################################
	public function BuscaCombos($filtro){

        $consulta = "SELECT 
        CONCAT(combos.nomcombo) as label, 
        combos.idcombo, 
        combos.codcombo, 
        combos.nomcombo, 
        ROUND(combos.preciocompra, 2) preciocompra, 
        ROUND(combos.precioventa, 2) precioventa, 
        ROUND(combos.existencia, 2) existencia, 
        combos.ivacombo, 
        ROUND(combos.desccombo, 2) desccombo, 
        combos.preparado 
        FROM combos 
        WHERE CONCAT(combos.codcombo, '',combos.nomcombo) LIKE '%".$filtro."%' 
        AND codsucursal= '".strip_tags($_SESSION["codsucursal"])."' 
        ORDER BY codcombo 
        ASC LIMIT 0,15";
        $conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE COMBOS ################################



	################################ BUSQUEDA DE CLIENTES X SUCURSAL ################################
	public function BuscaClientesxSucursal($filtro,$filtro2){

		$consulta = "SELECT
		CONCAT(if(clientes.documcliente='0','DOC.',documentos.documento), ': ',clientes.dnicliente, ': ',if(clientes.nomcliente='',clientes.razoncliente,clientes.nomcliente), ' | ',if(clientes.direccliente='','***',clientes.direccliente)) as label,  
		clientes.codcliente, 
		clientes.dnicliente,
		clientes.tipocliente,
		clientes.nomcliente,
		clientes.razoncliente,
		clientes.direccliente, 
		clientes.limitecredito
	    FROM
        clientes 
        LEFT JOIN documentos ON clientes.documcliente = documentos.coddocumento
        WHERE CONCAT(clientes.dnicliente, '',if(clientes.nomcliente='',clientes.razoncliente,clientes.nomcliente), '',clientes.girocliente) LIKE '%".$filtro."%'
        AND clientes.codsucursal = '".strip_tags($filtro2)."'
        ORDER BY clientes.codcliente 
        ASC LIMIT 0,10";
		$conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE CLIENTES X SUCURSAL ################################

	################################ BUSQUEDA DE CLIENTES ################################
	public function BuscaClientes($filtro){

		$consulta = "SELECT
		CONCAT(if(clientes.documcliente='0','DOC.',documentos.documento), ': ',clientes.dnicliente, ': ',if(clientes.nomcliente='',clientes.razoncliente,clientes.nomcliente), ' | ',if(clientes.direccliente='','***',clientes.direccliente)) as label,  
		clientes.codcliente, 
		clientes.dnicliente,
		clientes.tipocliente,
		clientes.nomcliente,
		clientes.razoncliente,
		clientes.direccliente, 
		clientes.limitecredito,
		ROUND(SUM(if(clientes.limitecredito!='0',clientes.limitecredito-pag.montocredito,clientes.limitecredito)), 2) creditodisponible
	    FROM
        clientes 
        LEFT JOIN documentos ON clientes.documcliente = documentos.coddocumento
        LEFT JOIN
	        (SELECT
	        codcliente, montocredito       
	        FROM creditosxclientes WHERE codsucursal = '".strip_tags($_SESSION['codsucursal'])."') pag ON pag.codcliente = clientes.codcliente 
        WHERE CONCAT(clientes.dnicliente, '',if(clientes.nomcliente='',clientes.razoncliente,clientes.nomcliente), '',clientes.girocliente) LIKE '%".$filtro."%'
        AND clientes.codsucursal = '".strip_tags($_SESSION['codsucursal'])."'
        GROUP BY clientes.codcliente 
        ORDER BY clientes.codcliente ASC LIMIT 0,10";
		$conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE CLIENTES ################################

	

	################################ BUSQUEDA DE FACTURAS X SUCURSAL ################################
	public function BuscaFacturas($filtro,$filtro2){

		$consulta = "SELECT
		CONCAT(ventas.tipodocumento, ' Nº ',ventas.codfactura, ': ',if(ventas.codcliente='0','CONSUMIDOR FINAL',if(clientes.nomcliente='',clientes.razoncliente,clientes.nomcliente))) as label, 
		ventas.idventa, 
		ventas.codventa, 
		ventas.codfactura 
		FROM ventas LEFT JOIN clientes ON ventas.codcliente = clientes.codcliente 
		WHERE CONCAT(ventas.tipodocumento, ventas.codventa, ventas.codfactura, if(ventas.codcliente='0','CONSUMIDOR FINAL',if(clientes.nomcliente='',clientes.razoncliente,clientes.nomcliente))) LIKE '%".$filtro."%'
        AND ventas.codsucursal = '".strip_tags($filtro2)."' 
		AND ventas.statusventa != 'ANULADA'
        ORDER BY ventas.codventa 
        ASC LIMIT 0,15";
		$conexion = new conectorDB;
		$this->json = $conexion->EjecutarSentencia($consulta);
		return $this->json;
	}
	################################ BUSQUEDA DE FACTURAS X SUCURSAL ################################

}/// TERMINA CLASE  ///
?>