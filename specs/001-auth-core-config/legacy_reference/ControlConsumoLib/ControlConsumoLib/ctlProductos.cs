using System;
using System.Data;
using System.Drawing;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlProductos
{
	private readonly clsProductos clsPro;

	public const int ProductoRecipienteLlevarID = 2;

	public const int ProductoServicioID = 1;

	private readonly clsTiposProductos clsTip;

	private readonly clsProductosUsos clsProdUs;

	private readonly clsFamilia clsFam;

	private clsProductosDeshabilitados clsProDesh;

	public ctlProductos()
	{
		clsPro = new clsProductos();
		clsTip = new clsTiposProductos();
		clsProdUs = new clsProductosUsos();
		clsFam = new clsFamilia();
	}

	public int GetProductoID()
	{
		return clsPro._ID;
	}

	public void SetProductoID(int ID)
	{
		clsPro._ID = ID;
	}

	public clsProductos LlenarClase()
	{
		clsPro.llenarclase();
		return clsPro;
	}

	public string proximoCodigo()
	{
		return clsPro.proximoCodigo();
	}

	public int cargarIdDesdeCodigo(string CodigoBarra, ref int sector)
	{
		clsPro._codigo = CodigoBarra;
		clsPro.cargarIdDesdeCodigo(ref sector);
		return clsPro._ID;
	}

	public int ToReturnIdbyName(string nombre, BD_SQL bd1 = null)
	{
		clsPro._Nombre = nombre;
		return clsPro.ToReturnIdbyName(bd1);
	}

	public int ToReturnIdbyNameDelivery(string nombre)
	{
		clsPro._Nombre = nombre.Trim();
		return clsPro.ToReturnIdbyNameDelivery();
	}

	public int ToReturnIdbySKUpedidosYa(string sku, ref string nombre)
	{
		clsPro._codigoPY = sku.Trim();
		return clsPro.ToReturnIdbySKUpedidosYa(ref nombre);
	}

	public bool verificarCodigo(ref string codigo, ref int productoID)
	{
		clsPro._codigo = codigo;
		if (clsPro.llenarclaseXcodigo())
		{
			codigo = clsPro._Nombre;
			productoID = clsPro._ID;
			return true;
		}
		return false;
	}

	public DataTable ToReturnProductosSinPreparacion()
	{
		return clsPro.ToReturnProductosSinPreparacion();
	}

	public DataTable ToReturnProductosSinPreparacionByTipo(int tipoPrep)
	{
		clsPro._TipoProductoID = tipoPrep;
		return clsPro.ToReturnProductosSinPreparacionByTipo();
	}

	public DataTable ToReturnProductosByTipo(int tipoPrep)
	{
		clsPro._TipoProductoID = tipoPrep;
		return clsPro.ToReturnProductosByTipo();
	}

	public DataTable ToReturnProductosByTipoMenosYo(int tipoPrep, int yoID)
	{
		clsPro._TipoProductoID = tipoPrep;
		return clsPro.ToReturnProductosByTipoMenosYo(yoID);
	}

	public DataTable ToReturnProductosHabilitadosByTipo(int tipoPrep)
	{
		clsPro._TipoProductoID = tipoPrep;
		return clsPro.ToReturnProductosHabilitadosByTipo();
	}

	public DataTable ToReturnProductos()
	{
		return clsPro.ToReturn();
	}

	public DataTable devolverProductos(bool chbAlaVenta)
	{
		return clsPro.Devolver(chbAlaVenta);
	}

	public bool llenarByID()
	{
		return clsPro.devolverDetalleProducto();
	}

	public DataTable obtenerCombosConceptosByProducto()
	{
		return clsPro.obtenerCombosConceptosByProducto();
	}

	public DataTable obtenerCombosConceptosByProductoActivo()
	{
		return clsPro.obtenerCombosConceptosByProductoActivo();
	}

	public DataTable obtenerCombosProductosByProducto(int TipoEnvioID)
	{
		return clsPro.obtenerCombosProductosByProducto(TipoEnvioID);
	}

	public DataTable obtenerCombosProductosByProductoOrder(int TipoEnvioID)
	{
		return clsPro.obtenerCombosProductosByProductoOrder(TipoEnvioID);
	}

	public DataTable obtenerCombosProductosByProductoActivo(int TipoEnvioID)
	{
		return clsPro.obtenerCombosProductosByProductoActivo(TipoEnvioID);
	}

	public bool devolverDetalleProductos(ref string descripcion, ref string Stock, ref string PRECIO, ref string costo, ref string Presentacion, ref string costoBruto)
	{
		if (clsPro.devolverDetalleProducto())
		{
			descripcion = clsPro._Descripcion;
			Stock = Conversions.ToString(clsPro._Stock);
			PRECIO = Conversions.ToString(clsPro._Precio);
			costo = Conversions.ToString(clsPro._Costo);
			Presentacion = clsPro._Presentacion;
			costoBruto = Conversions.ToString(clsPro._CostoBruto);
			return true;
		}
		return false;
	}

	public void anhadirStockProducto(double CantProducto, int almacenId)
	{
		clsPro.AnhadirStockProducto(CantProducto, almacenId);
	}

	public void modificarCosto(double costo)
	{
		clsPro._Costo = costo;
		clsPro.modificarCosto();
	}

	public void modificarCostoBruto(double costobruto)
	{
		clsPro._CostoBruto = costobruto;
		clsPro.modificarCostoBruto();
	}

	public void modificarCostoProductosYPreparacionesByProducto()
	{
	}

	public DataTable ToReturnProductosActivosByTipoID(int tipoProdID)
	{
		clsPro._TipoProductoID = tipoProdID;
		return clsPro.ToReturnProductosActivosByTipo();
	}

	public DataTable ToReturnProductosActivosByTipo1(string tipoProd)
	{
		return clsPro.ToReturnProductosActivosByTipo1(tipoProd);
	}

	public DataTable ToReturnProductosActivosByTipoKiosko(string tipoProd)
	{
		return clsPro.ToReturnProductosActivosByTipoKiosko(tipoProd);
	}

	public DataTable ToReturnProductosByTipo1(string tipoProd)
	{
		return clsPro.ToReturnProductosByTipo1(tipoProd);
	}

	public DataTable ToReturnProductosActivos()
	{
		return clsPro.ToReturnProductosActive();
	}

	public double ToReturnProductosPriceImpresoraByCodigo(int mesaID, string Codigo, ref string nombre, ref string Impresora, ref int ManejarStock, ref bool conRecipiente, ref bool esCombo, ref bool esPorPeso, ref bool EscogePersonal, ref int AlmacenID)
	{
		clsPro._codigo = Codigo;
		DataTable dataTable = clsPro.ToReturnProductosPriceImpresoraByCodigo();
		if (dataTable.Rows.Count > 0)
		{
			clsPro._ID = Conversions.ToInteger(dataTable.Rows[0]["ID"]);
			clsPro._Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""));
			clsPro._Precio = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Precio"]), 0));
			Impresora = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Impresora"]), "").ToString().Replace("\\\\" + MyProject.Computer.Name + "\\", "");
			ctlCategorias_Impresoras ctlCategorias_Impresoras2 = new ctlCategorias_Impresoras();
			Impresora = ctlCategorias_Impresoras2.devolverImpresoraOverride(Impresora, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0)), mesaID).ToString().Replace("\\\\" + MyProject.Computer.Name + "\\", "");
			ctlCategorias_Almacenes ctlCategorias_Almacenes2 = new ctlCategorias_Almacenes();
			AlmacenID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AlmacenID"]), 0));
			AlmacenID = ctlCategorias_Almacenes2.devolverAlmacenIDOverride(AlmacenID, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0)), mesaID);
			nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""));
			ManejarStock = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ManejarStock"]), 0));
			conRecipiente = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ConRecipienteLlevar"]), false));
			esCombo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esCombo"]), false));
			esPorPeso = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esPorPeso"]), false));
			EscogePersonal = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EscogePersonal"]), false));
		}
		else
		{
			clsPro._ID = 0;
			clsPro._Nombre = "";
			nombre = "";
			Impresora = "";
			ManejarStock = 0;
			conRecipiente = false;
			esPorPeso = false;
			EscogePersonal = false;
			clsPro._Precio = 0.0;
			Interaction.MsgBox("algo paso con el producto, no se encontro el precio");
		}
		return clsPro._Precio;
	}

	public string ToReturnObservaciones()
	{
		return clsPro.ToReturnObservaciones();
	}

	public string ToReturnProductosPriceImpresoraByName1(int mesaID, string name, ref double Precio, ref string Impresora, ref bool ManejarStock, ref bool conRecipiente, ref bool esCombo, ref bool esPorPeso, ref bool EscogePersonal, ref int almacenID, ref int Sector, int TipoEnvioID)
	{
		clsPro._Nombre = name;
		DataTable dataTable = clsPro.ToReturnProductosPriceImpresoraByName(TipoEnvioID);
		if (dataTable.Rows.Count > 0)
		{
			clsPro._ID = Conversions.ToInteger(dataTable.Rows[0]["ID"]);
			clsPro._TipoProductoID = (int?)VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0);
			clsTip._TipoProductoID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0));
			clsPro._Precio = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Precio"]), 0));
			Sector = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DocumentoSector"]), 0));
			int cantDecimales = 2;
			if (Sector == 35)
			{
				cantDecimales = 5;
			}
			Precio = Convert.ToDouble(VariableGeneral.toDecimalSIN(clsPro._Precio, cantDecimales));
			Impresora = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Impresora"]), "").ToString().Replace("\\\\" + MyProject.Computer.Name + "\\", "");
			clsPro._codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["codigo"]), 0));
			if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Beer) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin))
			{
				if (Operators.CompareString(MyProject.Computer.Name.ToUpper(), "SERVIDOR", TextCompare: false) == 0)
				{
					ctlCategorias_Impresoras ctlCategorias_Impresoras2 = new ctlCategorias_Impresoras();
					Impresora = ctlCategorias_Impresoras2.devolverImpresoraOverride(Impresora, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0)), mesaID).ToString().Replace("\\\\" + MyProject.Computer.Name + "\\", "");
				}
			}
			else
			{
				ctlCategorias_Impresoras ctlCategorias_Impresoras3 = new ctlCategorias_Impresoras();
				Impresora = ctlCategorias_Impresoras3.devolverImpresoraOverride(Impresora, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0)), mesaID).ToString().Replace("\\\\" + MyProject.Computer.Name + "\\", "");
			}
			ctlCategorias_Almacenes ctlCategorias_Almacenes2 = new ctlCategorias_Almacenes();
			almacenID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AlmacenID"]), 0));
			almacenID = ctlCategorias_Almacenes2.devolverAlmacenIDOverride(almacenID, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0)), mesaID);
			if (new ctlPreparaciones().tieneComboParaTipoProducto(Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0))))
			{
				esCombo = true;
			}
			else
			{
				esCombo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esCombo"]), false));
			}
			ManejarStock = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ManejarStock"]), 0));
			conRecipiente = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ConRecipienteLlevar"]), false));
			esPorPeso = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esPorPeso"]), false));
			EscogePersonal = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EscogePersonal"]), false));
		}
		else
		{
			clsPro._ID = 0;
			clsPro._codigo = "";
			clsPro._Precio = 0.0;
			if (Operators.CompareString(name, "Delivery", TextCompare: false) != 0)
			{
				Interaction.MsgBox("Algo paso con el producto " + name + ", no se lo encontro");
			}
		}
		return clsPro._codigo;
	}

	public string ToReturnProductosPriceImpresoraByID1(int mesaID, int prodID, ref double Precio, ref string Impresora, ref bool ManejarStock, ref bool conRecipiente, ref bool esCombo, ref bool esPorPeso, ref bool EscogePersonal, ref int AlmacenID, ref int Sector, int tipoEvioID)
	{
		clsPro._ID = prodID;
		DataTable dataTable = clsPro.ToReturnProductosPriceImpresoraByID(tipoEvioID);
		if (dataTable.Rows.Count > 0)
		{
			clsPro._Nombre = Conversions.ToString(dataTable.Rows[0]["Nombre"]);
			clsPro._Precio = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Precio"]), 0));
			Sector = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DocumentoSector"]), 0));
			int cantDecimales = 2;
			if (Sector == 35)
			{
				cantDecimales = 5;
			}
			clsPro._Precio = Convert.ToDouble(VariableGeneral.toDecimalSIN(clsPro._Precio, cantDecimales));
			Precio = clsPro._Precio;
			Impresora = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Impresora"]), "").ToString().Replace("\\\\" + MyProject.Computer.Name + "\\", "");
			clsPro._codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["codigo"]), 0));
			ctlCategorias_Impresoras ctlCategorias_Impresoras2 = new ctlCategorias_Impresoras();
			Impresora = ctlCategorias_Impresoras2.devolverImpresoraOverride(Impresora, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0)), mesaID).ToString().Replace("\\\\" + MyProject.Computer.Name + "\\", "");
			ctlCategorias_Almacenes ctlCategorias_Almacenes2 = new ctlCategorias_Almacenes();
			AlmacenID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AlmacenID"]), 0));
			AlmacenID = ctlCategorias_Almacenes2.devolverAlmacenIDOverride(AlmacenID, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0)), mesaID);
			ManejarStock = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ManejarStock"]), 0));
			conRecipiente = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ConRecipienteLlevar"]), false));
			esCombo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esCombo"]), false));
			esPorPeso = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esPorPeso"]), false));
			EscogePersonal = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EscogePersonal"]), false));
		}
		else
		{
			Interaction.MsgBox("algo paso con el producto, no se encontro el precio");
		}
		return clsPro._Nombre;
	}

	public string toReturnToolTipStock(string name, bool ParaLLevar)
	{
		clsPro._Nombre = name;
		return clsPro.toReturnToolTipStock(ParaLLevar);
	}

	public string toReturnToolTipCodigo(string name)
	{
		clsPro._Nombre = name;
		return clsPro.toReturnToolTipCodigo();
	}

	public void cargarDatos(int almacenId)
	{
		clsPro.CargarDatos(almacenId);
	}

	public void cargarDatosSinStock()
	{
		clsPro.CargarDatos();
		clsTip._TipoProductoID = clsPro._TipoProductoID.Value;
	}

	public void cargarDatosSIN(ref string Codigo, ref string Nombre, ref string CodigoSIN, ref string UnidadSINstr, ref int UnidadSIN, ref string ActividadSIN)
	{
		clsPro.cargarDatosSIN(ref UnidadSINstr);
		Codigo = clsPro._codigo;
		Nombre = clsPro._Nombre;
		CodigoSIN = clsPro._CodigoSIN1;
		ActividadSIN = clsPro._ActividadSIN1;
		UnidadSIN = clsPro._UnidadSIN;
	}

	public void cargarDatosCombo(ref string impresoraFisica)
	{
		clsPro.cargarDatosCombo(ref impresoraFisica);
	}

	public void cargarDatosComboKitchenDisplay(ref string kitchenDisplayFisica)
	{
		clsPro.cargarDatosComboKitchenDisplay(ref kitchenDisplayFisica);
	}

	public void CargarPrecio()
	{
		clsPro.CargarPrecio();
	}

	public bool tienePreparacion()
	{
		return clsPro._tienePreparacion;
	}

	public bool EsCombo()
	{
		return clsPro._esCombo;
	}

	public int getProductoTipoProductoID()
	{
		return clsPro._TipoProductoID.Value;
	}

	public double getStock()
	{
		return clsPro._Stock;
	}

	public double getPrecio()
	{
		return clsPro._Precio;
	}

	public string getNombre()
	{
		return clsPro._Nombre;
	}

	public double getCosto()
	{
		return clsPro._Costo;
	}

	public double getComision()
	{
		return clsPro._Comision;
	}

	public double getCantidadML()
	{
		return clsPro._CantidadML;
	}

	public void modificarStock(double cant, int detalleCuentaID, int ProduccionId, int prepProcesamientoID, int almacenID, BD_SQL bd1 = null)
	{
		clsPro.modificarStock(cant, almacenID, bd1);
		if ((detalleCuentaID > 0) | (ProduccionId > 0) | (prepProcesamientoID > 0))
		{
			clsProductosUsos clsProductosUsos2 = new clsProductosUsos();
			if (cant < 0.0)
			{
				clsProductosUsos2._cantidad = cant * -1.0;
				clsProductosUsos2._ProductoID = clsPro._ID;
				clsProductosUsos2._DetalleCuentaID = detalleCuentaID;
				clsProductosUsos2._produccionID = ProduccionId;
				clsProductosUsos2._preProcesamientoID = prepProcesamientoID;
				clsProductosUsos2._Fecha = DateAndTime.Now;
				clsProductosUsos2.Insertar(bd1);
			}
			else if (detalleCuentaID > 0)
			{
				clsProductosUsos2._DetalleCuentaID = detalleCuentaID;
				clsProductosUsos2._ProductoID = clsPro._ID;
				clsProductosUsos2._cantidad = cant;
				clsProductosUsos2.ModificarCantidad(bd1);
			}
		}
	}

	public void modificarCostoTraspaso(double cant, int detalleCuentaID, double costo, int almacenId, BD_SQL bd1 = null)
	{
		if (costo > 0.0)
		{
			clsPro.ModificarCostoTraspaso(cant, costo, almacenId, bd1);
		}
	}

	public void ModificarCostoProduccion(double cant, double costo, int almacenID)
	{
		if (costo > 0.0)
		{
			clsPro.ModificarCostoProduccion(cant, costo, almacenID);
		}
	}

	public bool Save(string Nombre, string Descripcion, double Precio, DateTime FechaModificacionPrecio, double costo, double Stock, double CantidadML, bool TienePreparacion, bool Habilitado, int tipoProductoId, bool ConRecipienteLlevar, bool escombo, bool esPorPeso, int CategoriaProduccionID, string codigo, bool EscogePersonal, int cantidadPaquete, int diasUtiles, int cantidadMinima, double comision, string presentacion, string unidadContenido, string grupo, double grupoCant, bool Borrado, double orden, double costoBruto, double cantidadMaxima, string codigoPY, int tiempo, double ICE_Fijo, double ice_porcentual, Image Image1, string linkFoto, bool habilitadoPY, int ExtraEnMesa, int extraParaLlevar, string CodigoSIN, int UnidadSIN, string actividadSIN, bool consolidarPro, string nombreInicial, bool CambiosPreparacion, bool manejaSerie, bool manejaImei)
	{
		clsPro._Nombre = Nombre.Replace("'", "`").Replace("\"", "`").Replace("[", "(")
			.Replace("]", ")");
		clsPro._Descripcion = Descripcion;
		clsPro._Precio = Precio;
		clsPro._FechaModificacionPrecio = FechaModificacionPrecio;
		clsPro._Costo = costo;
		clsPro._TipoProductoID = tipoProductoId;
		clsPro._Stock = Stock;
		clsPro._Habilitado = Habilitado;
		clsPro._CantidadML = CantidadML;
		clsPro._ConRecipienteLlevar = ConRecipienteLlevar;
		clsPro._tienePreparacion = TienePreparacion;
		clsPro._esCombo = escombo;
		clsPro._esPorPeso = esPorPeso;
		clsPro._CategoriaProduccionID = (int?)Interaction.IIf(CategoriaProduccionID == 0, null, CategoriaProduccionID);
		clsPro._EscogePersonal = EscogePersonal;
		clsPro._codigo = codigo;
		clsPro._HabilitadoPY = habilitadoPY;
		clsPro._DiasUtiles = diasUtiles;
		clsPro._CantidadPaquete = cantidadPaquete;
		clsPro._CantidadMinima = cantidadMinima;
		clsPro._Comision = comision;
		clsPro._Presentacion = presentacion;
		clsPro._UnidadContenido = unidadContenido;
		clsPro._Grupo = grupo;
		clsPro._GrupoCantidad = grupoCant;
		clsPro._Borrado = Borrado;
		clsPro._Orden = orden;
		clsPro._CostoBruto = costoBruto;
		clsPro._CantidadMaxima = cantidadMaxima;
		clsPro._CodigoSIN1 = CodigoSIN;
		clsPro._UnidadSIN = UnidadSIN;
		clsPro._ActividadSIN1 = actividadSIN;
		clsPro._LinkFoto = linkFoto;
		if (Operators.CompareString(codigoPY, "0", TextCompare: false) == 0)
		{
			clsPro._codigoPY = Conversions.ToString(0);
		}
		else
		{
			clsPro._codigoPY = codigoPY;
		}
		clsPro._Tiempo = tiempo;
		clsPro._ICE_Fijo = ICE_Fijo;
		clsPro._ICE_Porcentual = ice_porcentual;
		clsPro._ExtraParaLlevarID = extraParaLlevar;
		clsPro._ExtraEnMesaID = ExtraEnMesa;
		string text = clsPro.DevolverNombrexID(extraParaLlevar);
		string text2 = clsPro.DevolverNombrexID(ExtraEnMesa);
		clsPro._manejaImei = manejaImei;
		clsPro._manejaSerie = manejaSerie;
		bool flag = false;
		checked
		{
			if (clsPro._ID == 0)
			{
				flag = true;
				int num = clsPro.Insert();
				if (consolidarPro)
				{
					ctlAlmacenes ctlAlmacenes2 = new ctlAlmacenes();
					DataTable dataTable = ctlAlmacenes2.DevolverTodosAlmacenesExternosConConexion();
					string text3 = "";
					int num2 = dataTable.Rows.Count - 1;
					for (int i = 0; i <= num2; i++)
					{
						BD_SQL bD_SQL = new BD_SQL();
						ctlAlmacenes2.SetAlmacenID(Conversions.ToInteger(dataTable.Rows[i][0]));
						bD_SQL.setSQLs(ctlAlmacenes2.devolverSQLs());
						string error = "";
						if (bD_SQL.testConectar(ref error))
						{
							DataTable dataTable2 = bD_SQL.ConsultaVer("ID", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Nombre = '" + text + "'");
							if (dataTable2.Rows.Count > 0)
							{
								clsPro._ExtraParaLlevarID = Conversions.ToInteger(dataTable2.Rows[0][0]);
							}
							else
							{
								clsPro._ExtraParaLlevarID = 0;
							}
							dataTable2 = bD_SQL.ConsultaVer("ID", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Nombre = '" + text2 + "'");
							if (dataTable2.Rows.Count > 0)
							{
								clsPro._ExtraEnMesaID = Conversions.ToInteger(dataTable2.Rows[0][0]);
							}
							else
							{
								clsPro._ExtraEnMesaID = 0;
							}
							clsPro.Insert(bD_SQL);
							if (configuration.gTipoFacturacion == 2 && Operators.ConditionalCompareObjectEqual(bD_SQL.ConsultaVer("count(*)", "FactElectActividades", "Codigo like '" + actividadSIN + "'").Rows[0][0], 0, TextCompare: false))
							{
								DataTable dataTable3 = bD_SQL.ConsultaVer("select top 1 count(*) as cont, ActividadSIN, CodigoSIN from Productos where Borrado=" + VariableGeneral.armarBolean(0) + " group by ActividadSIN, CodigoSIN order by cont desc ");
								if (dataTable3.Rows.Count > 0)
								{
									bD_SQL.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("ActividadSIN='", dataTable3.Rows[0]["ActividadSIN"]), "', CodigoSIN='"), dataTable3.Rows[0]["CodigoSIN"]), "'")), "ID=" + Conversions.ToString(clsPro._ID));
								}
							}
						}
						else
						{
							text3 = ((text3.Length != 0) ? (text3 + ctlAlmacenes2.getnombre() + ", ") : ("No se pudo conectar con: " + ctlAlmacenes2.getnombre() + ", "));
						}
					}
					if (Operators.CompareString(text3, "", TextCompare: false) != 0)
					{
						Interaction.MsgBox(text3);
					}
				}
				if (configuration.styleBolichesId.KIKY == configuration.gStyleBoliches1)
				{
					if (Image1 == null)
					{
						BD.ConsultaInsertar(Conversions.ToString(clsPro._ID) + ",NULL", "Productos_Fotos");
					}
					else
					{
						BD.realizarConsultaModificarFoto("Modificar_Foto_Producto", clsPro._ID, Image1);
					}
				}
				return num > 0;
			}
			int num3 = clsPro.Modify();
			if (consolidarPro)
			{
				string text4 = "";
				int iD = clsPro._ID;
				ctlAlmacenes ctlAlmacenes3 = new ctlAlmacenes();
				DataTable dataTable4 = ctlAlmacenes3.DevolverTodosAlmacenesExternosConConexion();
				int num4 = dataTable4.Rows.Count - 1;
				for (int j = 0; j <= num4; j++)
				{
					BD_SQL bD_SQL2 = new BD_SQL();
					ctlAlmacenes3.SetAlmacenID(Conversions.ToInteger(dataTable4.Rows[j][0]));
					bD_SQL2.setSQLs(ctlAlmacenes3.devolverSQLs());
					string error = "";
					if (bD_SQL2.testConectar(ref error))
					{
						DataTable dataTable5 = bD_SQL2.ConsultaVer("Id", "Productos", "UPPER(nombre) like '" + nombreInicial.ToUpper() + "' and borrado=" + VariableGeneral.armarBolean(0));
						if (dataTable5.Rows.Count > 0)
						{
							clsPro._ID = Conversions.ToInteger(dataTable5.Rows[0][0]);
						}
						else
						{
							clsPro._ID = 0;
						}
						dataTable5 = bD_SQL2.ConsultaVer("ID", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Nombre = '" + text + "'");
						if (dataTable5.Rows.Count > 0)
						{
							clsPro._ExtraParaLlevarID = Conversions.ToInteger(dataTable5.Rows[0][0]);
						}
						else
						{
							clsPro._ExtraParaLlevarID = 0;
						}
						dataTable5 = bD_SQL2.ConsultaVer("ID", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Nombre = '" + text2 + "'");
						if (dataTable5.Rows.Count > 0)
						{
							clsPro._ExtraEnMesaID = Conversions.ToInteger(dataTable5.Rows[0][0]);
						}
						else
						{
							clsPro._ExtraEnMesaID = 0;
						}
						if (clsPro._ID == 0)
						{
							if (flag)
							{
								clsPro.Insert(bD_SQL2);
							}
							else if (Interaction.MsgBox("En la suc " + dataTable4.Rows[j]["Nombre"].ToString() + " no se encontro el item " + nombreInicial + ", desea agregarlo?", MsgBoxStyle.YesNo, " No existe ") == MsgBoxResult.Yes)
							{
								clsPro.Insert(bD_SQL2);
							}
						}
						else
						{
							clsPro.Modify(bD_SQL2);
						}
						if (CambiosPreparacion)
						{
							ctlPreparaciones ctlPreparaciones2 = new ctlPreparaciones();
							ctlPreparacionesComodines ctlPreparacionesComodines2 = new ctlPreparacionesComodines();
							DataTable dataTable6 = ctlPreparacionesComodines2.DevolverPreparacionesParaProductoDeTablas(iD);
							if (dataTable6.Rows.Count > 0)
							{
								string text5 = "";
								foreach (object row in dataTable6.Rows)
								{
									object objectValue = RuntimeHelpers.GetObjectValue(row);
									text5 = Conversions.ToString(Operators.ConcatenateObject(text5, Operators.ConcatenateObject(Operators.ConcatenateObject("'", NewLateBinding.LateIndexGet(objectValue, new object[1] { "deProducto" }, null)), "',")));
									if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue, new object[1] { "ModificaProducto" }, null)), "").ToString().Length > 0)
									{
										text5 = Conversions.ToString(Operators.ConcatenateObject(text5, Operators.ConcatenateObject(Operators.ConcatenateObject("'", NewLateBinding.LateIndexGet(objectValue, new object[1] { "ModificaProducto" }, null)), "',")));
									}
								}
								text5 = text5.Substring(0, text5.Length - 1);
								if (text5.Length > 0)
								{
									DataTable dataTable7 = bD_SQL2.ConsultaVer("Id,nombre", "Productos", "UPPER(nombre) in (" + text5.ToUpper() + ") and borrado=" + VariableGeneral.armarBolean(0));
									bD_SQL2.ConsultaEliminar("PreparacionesComodines", "PreparacionID  in (select Preparaciones.PreparacionID from Preparaciones where ParaProductoID = " + Conversions.ToString(clsPro._ID) + ")");
									bD_SQL2.ConsultaEliminar("Preparaciones", "ParaProductoID  = " + Conversions.ToString(clsPro._ID));
									int num5 = 0;
									int num6 = dataTable6.Rows.Count - 1;
									for (int k = 0; k <= num6; k++)
									{
										if (Operators.ConditionalCompareObjectNotEqual(num5, dataTable6.Rows[k]["PreparacionID"], TextCompare: false))
										{
											ctlPreparaciones2.SetPreparacionID(0);
											ctlPreparaciones2.GuardarPreparacion(Conversions.ToDouble(dataTable6.Rows[k]["Cantidad"]), Conversions.ToString(dataTable6.Rows[k]["Concepto"]), clsPro._ID, 0, Conversions.ToBoolean(dataTable6.Rows[k]["PuedeDisminuir"]), bD_SQL2);
											num5 = Conversions.ToInteger(dataTable6.Rows[k]["PreparacionID"]);
										}
										int num7 = 0;
										int num8 = 0;
										foreach (object row2 in dataTable7.Rows)
										{
											object objectValue2 = RuntimeHelpers.GetObjectValue(row2);
											if (Operators.CompareString(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "nombre" }, null).ToString().ToUpper(), dataTable6.Rows[k]["deProducto"].ToString().ToUpper(), TextCompare: false) == 0)
											{
												num7 = Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Id" }, null));
												break;
											}
										}
										if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[k]["ModificaProducto"]), "").ToString().Length > 0)
										{
											foreach (object row3 in dataTable7.Rows)
											{
												object objectValue3 = RuntimeHelpers.GetObjectValue(row3);
												if (Operators.CompareString(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "nombre" }, null).ToString().ToUpper(), dataTable6.Rows[k]["ModificaProducto"].ToString().ToUpper(), TextCompare: false) == 0)
												{
													num8 = Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Id" }, null));
													break;
												}
											}
										}
										if (num7 == 0 && Interaction.MsgBox(Operators.ConcatenateObject(Operators.ConcatenateObject("En la suc " + dataTable4.Rows[j]["Nombre"].ToString() + " no se encontro el item ", dataTable6.Rows[k]["deProducto"]), ", desea agregarlo?"), MsgBoxStyle.YesNo, " No existe ") == MsgBoxResult.Yes)
										{
											SaveDesdeTraspaso(Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[k]["deProductoID"]), 0)), bD_SQL2);
											DataTable dataTable8 = bD_SQL2.ConsultaVer("id", "Productos", "upper(nombre) like '" + dataTable6.Rows[k]["deProducto"].ToString().ToUpper() + "'");
											if (dataTable8.Rows.Count == 0)
											{
												Interaction.MsgBox(Operators.ConcatenateObject("No consiguio crear ese item ", dataTable6.Rows[k]["deProducto"]));
											}
											else
											{
												num7 = Conversions.ToInteger(dataTable8.Rows[0]["id"]);
											}
										}
										if (Operators.ConditionalCompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[k]["ModificaProductoId"]), 0), 0, TextCompare: false) && num8 == 0 && Interaction.MsgBox(Operators.ConcatenateObject(Operators.ConcatenateObject("En la suc " + dataTable4.Rows[j]["Nombre"].ToString() + " no se encontro el item ", dataTable6.Rows[k]["ModificaProducto"]), ", desea agregarlo?"), MsgBoxStyle.YesNo, " No existe ") == MsgBoxResult.Yes)
										{
											SaveDesdeTraspaso(Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[k]["ModificaProductoId"]), 0)), bD_SQL2);
											DataTable dataTable9 = bD_SQL2.ConsultaVer("id", "Productos", "upper(nombre) like '" + dataTable6.Rows[k]["ModificaProducto"].ToString().ToUpper() + "'");
											if (dataTable9.Rows.Count == 0)
											{
												Interaction.MsgBox(Operators.ConcatenateObject("No consiguio crear ese item ", dataTable6.Rows[k]["deProducto"]));
											}
											else
											{
												num7 = Conversions.ToInteger(dataTable9.Rows[0]["id"]);
											}
										}
										if (num7 > 0)
										{
											ctlPreparacionesComodines2.SetPreparacionComodinID(0);
											ctlPreparacionesComodines2.GuardarPreparacionComodin(num7, ctlPreparaciones2.GetPreparacionID(), Conversions.ToDouble(dataTable6.Rows[k]["Precio"]), Conversions.ToBoolean(dataTable6.Rows[k]["ModificaPrecio"]), num8, bD_SQL2);
										}
									}
								}
							}
						}
						if (configuration.gTipoFacturacion == 2 && Operators.ConditionalCompareObjectEqual(bD_SQL2.ConsultaVer("count(*)", "FactElectActividades", "Codigo like '" + actividadSIN + "'").Rows[0][0], 0, TextCompare: false))
						{
							DataTable dataTable10 = bD_SQL2.ConsultaVer("select top 1 count(*) as cont, ActividadSIN, CodigoSIN from Productos where Borrado=" + VariableGeneral.armarBolean(0) + " group by ActividadSIN, CodigoSIN order by cont desc ");
							if (dataTable10.Rows.Count > 0)
							{
								bD_SQL2.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("ActividadSIN='", dataTable10.Rows[0]["ActividadSIN"]), "', CodigoSIN='"), dataTable10.Rows[0]["CodigoSIN"]), "'")), "ID=" + Conversions.ToString(clsPro._ID));
							}
						}
					}
					else
					{
						text4 = ((text4.Length != 0) ? (text4 + ctlAlmacenes3.getnombre() + ", ") : ("No se pudo conectar con: " + ctlAlmacenes3.getnombre() + ", "));
					}
				}
				if (Operators.CompareString(text4, "", TextCompare: false) != 0)
				{
					Interaction.MsgBox(text4);
				}
				clsPro._ID = iD;
			}
			if (configuration.styleBolichesId.KIKY == configuration.gStyleBoliches1)
			{
				BD.ConsultaEliminar("Productos_Fotos", "id=" + Conversions.ToString(clsPro._ID));
				BD.ConsultWithOutAlerts("insert into Productos_Fotos values (" + Conversions.ToString(clsPro._ID) + ",NULL)");
				BD.realizarConsultaModificarFoto("Modificar_Foto_Producto", clsPro._ID, Image1);
			}
			return num3 > 0;
		}
	}

	public void SaveDesdeTraspaso(int productoID, BD_SQL BdAux)
	{
		clsProductos obj = new clsProductos();
		obj._ID = productoID;
		obj.llenarclase();
		obj._Stock = 0.0;
		obj._ID = 0;
		obj.Insert(BdAux);
		ctlPreparaciones ctlPreparaciones2 = new ctlPreparaciones();
		ctlPreparacionesComodines ctlPreparacionesComodines2 = new ctlPreparacionesComodines();
		DataTable dataTable = ctlPreparacionesComodines2.DevolverPreparacionesParaProductoDeTablas(productoID);
		if (dataTable.Rows.Count <= 0)
		{
			return;
		}
		string text = "";
		foreach (object row in dataTable.Rows)
		{
			object objectValue = RuntimeHelpers.GetObjectValue(row);
			text = Conversions.ToString(Operators.ConcatenateObject(text, Operators.ConcatenateObject(Operators.ConcatenateObject("'", NewLateBinding.LateIndexGet(objectValue, new object[1] { "deProducto" }, null)), "',")));
		}
		checked
		{
			text = text.Substring(0, text.Length - 1);
			if (text.Length <= 0)
			{
				return;
			}
			DataTable dataTable2 = BdAux.ConsultaVer("Id,nombre", "Productos", "nombre in (" + text + ") and borrado=" + VariableGeneral.armarBolean(0));
			int num = 0;
			int num2 = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num2; i++)
			{
				if (Operators.ConditionalCompareObjectNotEqual(num, dataTable.Rows[i]["PreparacionID"], TextCompare: false))
				{
					ctlPreparaciones2.SetPreparacionID(0);
					ctlPreparaciones2.GuardarPreparacion(Conversions.ToDouble(dataTable.Rows[i]["Cantidad"]), Conversions.ToString(dataTable.Rows[i]["Concepto"]), clsPro._ID, 0, Conversions.ToBoolean(dataTable.Rows[i]["PuedeDisminuir"]), BdAux);
					num = Conversions.ToInteger(dataTable.Rows[i]["PreparacionID"]);
				}
				int num3 = 0;
				foreach (object row2 in dataTable2.Rows)
				{
					object objectValue2 = RuntimeHelpers.GetObjectValue(row2);
					if (Operators.ConditionalCompareObjectEqual(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "nombre" }, null), dataTable.Rows[i]["deProducto"], TextCompare: false))
					{
						num3 = Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Id" }, null));
						break;
					}
				}
				if (num3 == 0 && Interaction.MsgBox(Operators.ConcatenateObject(Operators.ConcatenateObject("No se encontro el item ", dataTable.Rows[i]["deProducto"]), ", desea agregarlo?"), MsgBoxStyle.YesNo, " No existe ") == MsgBoxResult.Yes)
				{
					SaveDesdeTraspaso(Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["deProductoID"]), 0)), BdAux);
					DataTable dataTable3 = BdAux.ConsultaVer("id", "Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("nombre like '", dataTable.Rows[i]["deProducto"]), "'")));
					if (dataTable3.Rows.Count == 0)
					{
						Interaction.MsgBox(Operators.ConcatenateObject("No consiguio crear ese item ", dataTable.Rows[i]["deProducto"]));
					}
					else
					{
						num3 = Conversions.ToInteger(dataTable3.Rows[0]["id"]);
					}
				}
				if (num3 > 0)
				{
					ctlPreparacionesComodines2.SetPreparacionComodinID(0);
					ctlPreparacionesComodines2.GuardarPreparacionComodin(num3, ctlPreparaciones2.GetPreparacionID(), Conversions.ToDouble(dataTable.Rows[i]["Precio"]), Conversions.ToBoolean(dataTable.Rows[i]["ModificaPrecio"]), Conversions.ToInteger(dataTable.Rows[i]["ModificaProductoID"]), BdAux);
				}
			}
		}
	}

	public bool Delete()
	{
		return clsPro.Delete() != 0;
	}

	public void DeleteLogico()
	{
		clsPro.DeleteLogico();
	}

	public DataTable devolverProductosPorTipoProducto()
	{
		clsPro._TipoProductoID = clsTip._TipoProductoID;
		return clsPro.devolverProductosPorTipoProducto();
	}

	public void DevolverCostoCantidad(ref double stock, ref double costo)
	{
		clsPro.devolverDetalleProducto();
		costo = clsPro._Costo;
		stock = clsPro._Stock;
	}

	public string EsProductoPorPeso(int prodID, ref bool esPorPeso)
	{
		clsPro._ID = prodID;
		DataTable dataTable = clsPro.EsProductoPorPeso();
		if (dataTable.Rows.Count > 0)
		{
			esPorPeso = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esPorPeso"]), false));
		}
		else
		{
			Interaction.MsgBox("algo paso con el producto, no se encontro el dato");
		}
		return clsPro._Nombre;
	}

	public int GetTipoProductoID()
	{
		return clsTip._TipoProductoID;
	}

	public int GetTipoProductoAlmacenID()
	{
		return clsTip._AlmacenID;
	}

	public int GetTipoProductoManejaStock()
	{
		return 0 - (clsTip._ManejarStock ? 1 : 0);
	}

	public void SetTipoProductoID(int ID)
	{
		clsPro._TipoProductoID = ID;
		clsTip._TipoProductoID = ID;
	}

	public DataTable devolverTiposProductosFacturacion(string desc)
	{
		return clsTip.devolverTiposProductosFacturacion(desc);
	}

	public DataTable devolverFamiliasQtenganProductos(int mesaId)
	{
		if (mesaId == 0)
		{
			return clsTip.devolverFamiliasQtenganProductos();
		}
		return clsTip.devolverFamiliasQtenganProductosOverrideAsociadosAlSalon(mesaId);
	}

	public DataTable devolverTiposProductosQtenganProductos(int mesaId, string Familia, int TipoUsuarioID)
	{
		if (mesaId == 0)
		{
			return clsTip.devolverTiposProductosQtenganProductos1(Familia);
		}
		return clsTip.devolverTiposProductosQtenganProductosOverrideAsociadosAlSalon(mesaId, Familia, TipoUsuarioID);
	}

	public DataTable devolverImagenesTiposProductosQtenganProductos1()
	{
		return clsTip.devolverImagenesTiposProductosQtenganProductos1();
	}

	public bool devolverCategoriaManejaICE()
	{
		return clsTip.devolverManejaICE();
	}

	public DataTable devolverTiposProductosPorDescripcionXConfig()
	{
		return clsTip.devolverTiposProductosPorDescripcionXConfig();
	}

	public DataTable devolverTiposProductosPorDescripcion1()
	{
		return clsTip.devolverTiposProductosPorDescripcion1();
	}

	public string devolverTipoProductoPorCodigo(string codigo)
	{
		return clsTip.devolverTipoProductoPorCodigo(codigo);
	}

	public DataTable devolverCategoriaProduccionPorDescripcion1()
	{
		return clsTip.devolverCategoriaProduccionPorDescripcion1();
	}

	public DataTable DevolverProductosCompra(DateTime fechai, DateTime fechaf, int idProducto)
	{
		return clsPro.DevolverProductosCompra(fechai, fechaf, idProducto);
	}

	public int GetTipoProductos()
	{
		return clsTip._TipoProductoID;
	}

	public void setTipoProductos(int ID)
	{
		clsTip._TipoProductoID = ID;
	}

	public clsTiposProductos LlenarClaseTiposProductos()
	{
		clsTip.llenarclase();
		return clsTip;
	}

	public DataTable devolverTipoProducto(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsTip.Devolver();
		}
		return clsTip.Devolver(search, field);
	}

	public DataTable devolverTipoProducto()
	{
		return clsTip.Devolver();
	}

	public DataTable devolverTipoProductosPorDescripcion()
	{
		return clsTip.devolverTipoProductosPorDescripcion();
	}

	public void GuardarTipoProducto(string Descripcion, int ImpresoraID, bool ManejadorStock, string codigo, int familiaID, int orden, int almacenID, int tipoUsuarioID, int configuracionID, Image Image1, int kitchenDisplayID, bool consolidarSucursales, int sector)
	{
		clsTip._Descripcion = Descripcion;
		clsTip._ImpresoraID = ImpresoraID;
		clsTip._ManejarStock = ManejadorStock;
		clsTip._codigo = codigo;
		clsTip._FamiliaID = familiaID;
		clsTip._orden = orden;
		clsTip._AlmacenID = almacenID;
		clsTip._TipoUsuarioId = tipoUsuarioID;
		clsTip._ConfiguracionID = configuracionID;
		clsTip._kitchenDisplayID = kitchenDisplayID;
		clsTip._DocumentoSector = sector;
		checked
		{
			if (clsTip._TipoProductoID == 0)
			{
				clsTip.Insertar();
				if (consolidarSucursales)
				{
					ctlAlmacenes ctlAlmacenes2 = new ctlAlmacenes();
					DataTable dataTable = ctlAlmacenes2.DevolverTodosAlmacenesExternosConConexion();
					int num = dataTable.Rows.Count - 1;
					for (int i = 0; i <= num; i++)
					{
						BD_SQL bD_SQL = new BD_SQL();
						ctlAlmacenes2.SetAlmacenID(Conversions.ToInteger(dataTable.Rows[i][0]));
						bD_SQL.setSQLs(ctlAlmacenes2.devolverSQLs());
						string error = "";
						if (bD_SQL.testConectar(ref error))
						{
							clsTip.Insertar(bD_SQL);
						}
						else
						{
							Interaction.MsgBox("No se pudo conectar con " + ctlAlmacenes2.getnombre());
						}
					}
				}
				if (configuration.styleBolichesId.KIKY == configuration.gStyleBoliches1)
				{
					if (Image1 == null)
					{
						BD.ConsultaInsertar(Conversions.ToString(clsTip._TipoProductoID) + ",NULL", "TiposProductos_Fotos");
					}
					else
					{
						BD.realizarConsultaModificarFoto("Modificar_Foto_TipoProducto", clsTip._TipoProductoID, Image1);
					}
				}
				return;
			}
			clsTip.Modificar();
			if (consolidarSucursales)
			{
				ctlAlmacenes ctlAlmacenes3 = new ctlAlmacenes();
				DataTable dataTable2 = ctlAlmacenes3.DevolverTodosAlmacenesExternosConConexion();
				int num2 = dataTable2.Rows.Count - 1;
				for (int j = 0; j <= num2; j++)
				{
					BD_SQL bD_SQL2 = new BD_SQL();
					ctlAlmacenes3.SetAlmacenID(Conversions.ToInteger(dataTable2.Rows[j][0]));
					bD_SQL2.setSQLs(ctlAlmacenes3.devolverSQLs());
					string error = "";
					if (bD_SQL2.testConectar(ref error))
					{
						clsTip.Modificar(bD_SQL2);
					}
					else
					{
						Interaction.MsgBox("No se pudo conectar con " + ctlAlmacenes3.getnombre());
					}
				}
			}
			if (configuration.styleBolichesId.KIKY == configuration.gStyleBoliches1)
			{
				BD.ConsultaEliminar("TiposProductos_Fotos", "id=" + Conversions.ToString(clsTip._TipoProductoID));
				BD.ConsultWithOutAlerts("insert into TiposProductos_Fotos(id, foto) values (" + Conversions.ToString(clsTip._TipoProductoID) + ",NULL)");
				BD.realizarConsultaModificarFoto("Modificar_Foto_TipoProducto", clsTip._TipoProductoID, Image1);
			}
		}
	}

	public void habilitarTodaCategoria(bool habilitar)
	{
		clsPro.habilitarTodaCategoria(habilitar, clsTip._TipoProductoID);
	}

	public void EliminarTipoProducto()
	{
		clsTip.Eliminar();
	}

	public int DevolverTipoProductoXProducto(int idProducto)
	{
		return clsPro.DevolverTipoProductoXProducto(idProducto);
	}

	public void EsPaquete(ref int dias, ref int Cantidad)
	{
		clsPro.CargarDatosPaquete();
		dias = clsPro._DiasUtiles;
		Cantidad = clsPro._CantidadPaquete;
	}

	public string devolverStocksXXX()
	{
		return clsPro.devolverStocksXXX();
	}

	public DataTable DevolverProductosStockMinimo(bool todo)
	{
		return clsPro.DevolverStockMinimo(todo);
	}

	public DataTable DevolverProductosInventarioValorizado(int almacenID, bool prodDiferenteCero)
	{
		return clsPro.DevolverInventarioValorizado(almacenID, prodDiferenteCero);
	}

	public DataTable DevolverInventarioValorizadoAlmacenesSeparados(bool prodDiferenteCero)
	{
		return clsPro.DevolverInventarioValorizadoAlmacenesSeparados(prodDiferenteCero);
	}

	public DataTable DevolverProductosInventarioValorizadoXAjuste(int almacenID, int AjusteID)
	{
		return clsPro.DevolverInventarioValorizadoXAjuste(almacenID, AjusteID);
	}

	public DataTable BuscarProductos(string Codigo, string Nombre, bool desdeVentas, bool SinPreparacion, int AlmacenId)
	{
		return clsPro.BuscarProductos(Codigo, Nombre, desdeVentas, SinPreparacion, AlmacenId);
	}

	public bool ExisteCodigo(string Codigo)
	{
		clsPro._codigo = Codigo;
		return clsPro.ExisteCodigo();
	}

	public bool ExisteProductoNombre(string nombre)
	{
		clsPro._Nombre = nombre;
		return clsPro.ExisteProductoNombre();
	}

	public void modificarCodigo(string Codigo)
	{
		clsPro._codigo = Codigo;
		clsPro.ModificarCodigo();
	}

	public DataTable devolverProductosUsos(DateTime FechaSI, DateTime FechaSF, int TipoProd, bool agrupados)
	{
		return new clsProductosUsos().devolverDevolverProductosUsos(FechaSI, FechaSF, TipoProd, agrupados);
	}

	public void modificarPrecio(double Precio)
	{
		clsPro._Precio = Precio;
		clsPro.modificarPrecio();
	}

	public void modificarPrecioUnidad(double Precio, string CodigoSIN, int UnidadSIN, string actividadSIN)
	{
		clsPro._Precio = Precio;
		clsPro._CodigoSIN1 = CodigoSIN;
		clsPro._UnidadSIN = UnidadSIN;
		clsPro._ActividadSIN1 = actividadSIN;
		clsPro.modificarPrecioUnidad();
	}

	public DataTable ToReturnProductosTodosMenosYo(int yoId)
	{
		return clsPro.ToReturnProductosTodos(yoId);
	}

	public int BuscarProductosXCodigo(string codigo)
	{
		clsPro._codigo = codigo;
		return clsPro.BuscarProductosXCodigo();
	}

	public object DevolvoverProdUsosXDetalle(int id)
	{
		clsProdUs._DetalleCuentaID = id;
		return clsProdUs.DevolvoverProdUsosXDetalle();
	}

	public int GetFamiliaID()
	{
		return clsFam._FamiliaID;
	}

	public void SetFamiliaID(int ID)
	{
		clsFam._FamiliaID = ID;
	}

	public clsFamilia LlenarClaseFamilias()
	{
		clsFam.llenarclase();
		return clsFam;
	}

	public DataTable devolverFamilia(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsFam.Devolver();
		}
		return clsFam.Devolver(search, field);
	}

	public DataTable devolverFamilia()
	{
		return clsFam.Devolver();
	}

	public DataTable devolverFamiliaPorDescripcion()
	{
		return clsFam.devolverFamiliaPorDescripcion1();
	}

	public void GuardarFamilia(string Descripcion, string Codigo)
	{
		clsFam._Descripcion = Descripcion;
		clsFam._Codigo = Codigo;
		if (clsFam._FamiliaID == 0)
		{
			clsFam.Insertar();
		}
		else
		{
			clsFam.Modificar();
		}
	}

	public void EliminarFamilia()
	{
		clsFam.Eliminar();
	}

	public DataTable ToReturnProductosPaquetes()
	{
		return clsPro.ToReturnProductosPaquetes();
	}

	public void DevolverPrecioUnit(int id, ref double precio1)
	{
		clsPro._ID = id;
		clsPro.DevolverPrecioUnit(ref precio1);
	}

	public DataTable devolverProductosUsosSrPollo(DateTime FechaSI, DateTime FechaSF, string Nombre)
	{
		return new clsProductosUsos().devolverDevolverProductosUsosSrPollo(FechaSI, FechaSF, Nombre);
	}

	public DataTable DevolverPresentaciones()
	{
		return clsPro.DevolverPresentaciones();
	}

	public DataTable DevolverUnidadesContenido()
	{
		return clsPro.DevolverUnidadContenido();
	}

	public DataTable DevolverGrupo()
	{
		return clsPro.DevolverGrupo();
	}

	public string DevolverCodigoXID()
	{
		return clsPro.DevolverCodigoXID();
	}

	public string DevolverNombreXcodigo(string codigo)
	{
		clsPro._codigo = codigo;
		return clsPro.DevolverNombreXcodigo();
	}

	public void limpiarStockProductosConPreparacion(int ProductoID)
	{
		DataTable dataTable = new ctlAlmacenes().DevolverTodosAlmacenesInternos(-1);
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				if (ProductoID == 0)
				{
					BD.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("stock", dataTable.Rows[i][0]), " =0 ")), "(TienePreparacion =" + VariableGeneral.armarBolean(1) + " or esCombo=" + VariableGeneral.armarBolean(1) + " ) and (CategoriaProduccionID is null or CategoriaProduccionID=0)");
				}
				else
				{
					BD.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("stock", dataTable.Rows[i][0]), " =0 ")), "(TienePreparacion =" + VariableGeneral.armarBolean(1) + " or esCombo=" + VariableGeneral.armarBolean(1) + " ) and (CategoriaProduccionID is null or CategoriaProduccionID=0) And id=" + Conversions.ToString(ProductoID));
				}
			}
		}
	}

	public void DeshabilitarTemp(int productoID, int meseroID)
	{
		if (clsProDesh == null)
		{
			clsProDesh = new clsProductosDeshabilitados();
		}
		clsProDesh._Fecha = DateAndTime.Now;
		clsProDesh._productoId = productoID;
		clsProDesh._usuarioID = meseroID;
		clsProDesh.Insertar();
	}

	public void reHabilitar(int desHabiID, int prodID)
	{
		if (clsProDesh == null)
		{
			clsProDesh = new clsProductosDeshabilitados();
		}
		clsProDesh._productoId = prodID;
		clsProDesh.EliminarXprodID();
		clsPro._ID = prodID;
		clsPro.llenarclase();
		clsPro._Habilitado = true;
		if (Conversions.ToDouble(clsPro._codigoPY) > 0.0)
		{
			clsPro._HabilitadoPY = true;
		}
		clsPro.Modify();
	}

	public DataTable devolverPRoductosDeshabilitados()
	{
		if (clsProDesh == null)
		{
			clsProDesh = new clsProductosDeshabilitados();
		}
		return clsProDesh.devolver();
	}

	public void crearProducto(string nombre)
	{
		int tipoProductoId = Conversions.ToInteger(devolverTiposProductosPorDescripcion1().Rows[0][0]);
		int num = 0;
		int num2 = 0;
		int unidadSIN = 0;
		if (configuration.gTipoFacturacion == 2)
		{
			try
			{
				ctlFactElectSyncActividades ctlFactElectSyncActividades2 = new ctlFactElectSyncActividades();
				ctlFactElectProductosServicios obj = new ctlFactElectProductosServicios();
				num = Conversions.ToInteger(ctlFactElectSyncActividades2.Devolver().Rows[0][0]);
				num2 = Conversions.ToInteger(obj.DevolverXActividad(Conversions.ToString(num)).Rows[0][0]);
				unidadSIN = 58;
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				ProjectData.ClearProjectError();
			}
		}
		SetProductoID(0);
		Save(nombre, "", 0.0, DateAndTime.Now, 0.0, 0.0, 0.0, TienePreparacion: false, Habilitado: false, tipoProductoId, ConRecipienteLlevar: false, escombo: false, esPorPeso: false, 0, "", EscogePersonal: false, 0, 0, 0, 0.0, "", "", "", 0.0, Borrado: false, 0.0, 0.0, 0.0, Conversions.ToString(0), 0, 0.0, 0.0, null, "", habilitadoPY: false, 0, 0, Conversions.ToString(num2), unidadSIN, Conversions.ToString(num), consolidarPro: false, "", CambiosPreparacion: false, manejaSerie: false, manejaImei: false);
	}
}
