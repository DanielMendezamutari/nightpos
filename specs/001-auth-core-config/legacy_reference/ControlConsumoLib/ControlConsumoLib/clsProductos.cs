using System;
using System.Data;
using System.Drawing;
using System.IO;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsProductos
{
	private int ID;

	private string Nombre;

	private string Codigo;

	private string Descripcion;

	private double Precio;

	private double Costo;

	private DateTime FechaModificacionPrecio;

	private double Stock;

	private double CantidadML;

	private bool tienePreparacion;

	private bool Habilitado;

	private bool ConRecipienteLlevar;

	private int? TipoProductoID;

	private bool esCombo;

	private bool esPorPeso;

	private int? CategoriaProduccionID;

	private bool EscogePersonal;

	private int CantidadPaquete;

	private int DiasUtiles;

	private int CantidadMinima;

	private double Comision;

	private string Presentacion;

	private string UnidadContenido;

	private string Grupo;

	private double GrupoCantidad;

	private bool Borrado;

	private double Orden;

	private double CostoBruto;

	private double CantidadMaxima;

	private string CodigoPY;

	private int Tiempo;

	private double ICE_Fijo;

	private double ICE_Porcentual;

	private string LinkFoto;

	private bool HabilitadoPY;

	private int ExtraParaLlevarID;

	private int ExtraEnMesaID;

	private string actividadSIN1;

	private string CodigoSIN1;

	private int UnidadSIN;

	private bool manejaSerie;

	private bool manejaImei;

	public bool _ConRecipienteLlevar
	{
		get
		{
			return ConRecipienteLlevar;
		}
		set
		{
			ConRecipienteLlevar = value;
		}
	}

	public bool _EscogePersonal
	{
		get
		{
			return EscogePersonal;
		}
		set
		{
			EscogePersonal = value;
		}
	}

	public int _ID
	{
		get
		{
			return ID;
		}
		set
		{
			ID = value;
		}
	}

	public int _ExtraEnMesaID
	{
		get
		{
			return ExtraEnMesaID;
		}
		set
		{
			ExtraEnMesaID = value;
		}
	}

	public int _ExtraParaLlevarID
	{
		get
		{
			return ExtraParaLlevarID;
		}
		set
		{
			ExtraParaLlevarID = value;
		}
	}

	public string _codigoPY
	{
		get
		{
			return CodigoPY;
		}
		set
		{
			CodigoPY = value;
		}
	}

	public bool _manejaSerie
	{
		get
		{
			return manejaSerie;
		}
		set
		{
			manejaSerie = value;
		}
	}

	public bool _manejaImei
	{
		get
		{
			return manejaImei;
		}
		set
		{
			manejaImei = value;
		}
	}

	public bool _HabilitadoPY
	{
		get
		{
			return HabilitadoPY;
		}
		set
		{
			HabilitadoPY = value;
		}
	}

	public string _LinkFoto
	{
		get
		{
			return LinkFoto;
		}
		set
		{
			LinkFoto = value;
		}
	}

	public bool _Habilitado
	{
		get
		{
			return Habilitado;
		}
		set
		{
			Habilitado = value;
		}
	}

	public int? _CategoriaProduccionID
	{
		get
		{
			return CategoriaProduccionID;
		}
		set
		{
			CategoriaProduccionID = value;
		}
	}

	public int? _TipoProductoID
	{
		get
		{
			return TipoProductoID;
		}
		set
		{
			TipoProductoID = value;
		}
	}

	public string _codigo
	{
		get
		{
			return Codigo;
		}
		set
		{
			Codigo = value;
		}
	}

	public string _Nombre
	{
		get
		{
			return Nombre;
		}
		set
		{
			Nombre = value;
		}
	}

	public string _Descripcion
	{
		get
		{
			return Descripcion;
		}
		set
		{
			Descripcion = value;
		}
	}

	public double _Stock
	{
		get
		{
			return Stock;
		}
		set
		{
			Stock = value;
		}
	}

	public double _CantidadML
	{
		get
		{
			return CantidadML;
		}
		set
		{
			CantidadML = value;
		}
	}

	public bool _esPorPeso
	{
		get
		{
			return esPorPeso;
		}
		set
		{
			esPorPeso = value;
		}
	}

	public bool _esCombo
	{
		get
		{
			return esCombo;
		}
		set
		{
			esCombo = value;
		}
	}

	public bool _tienePreparacion
	{
		get
		{
			return tienePreparacion;
		}
		set
		{
			tienePreparacion = value;
		}
	}

	public double _Precio
	{
		get
		{
			return Precio;
		}
		set
		{
			Precio = value;
		}
	}

	public double _Costo
	{
		get
		{
			return Costo;
		}
		set
		{
			Costo = value;
		}
	}

	public DateTime _FechaModificacionPrecio
	{
		get
		{
			return FechaModificacionPrecio;
		}
		set
		{
			FechaModificacionPrecio = value;
		}
	}

	public int _CantidadPaquete
	{
		get
		{
			return CantidadPaquete;
		}
		set
		{
			CantidadPaquete = value;
		}
	}

	public int _DiasUtiles
	{
		get
		{
			return DiasUtiles;
		}
		set
		{
			DiasUtiles = value;
		}
	}

	public int _CantidadMinima
	{
		get
		{
			return CantidadMinima;
		}
		set
		{
			CantidadMinima = value;
		}
	}

	public double _Comision
	{
		get
		{
			return Comision;
		}
		set
		{
			Comision = value;
		}
	}

	public string _Presentacion
	{
		get
		{
			return Presentacion;
		}
		set
		{
			Presentacion = value;
		}
	}

	public string _UnidadContenido
	{
		get
		{
			return UnidadContenido;
		}
		set
		{
			UnidadContenido = value;
		}
	}

	public string _Grupo
	{
		get
		{
			return Grupo;
		}
		set
		{
			Grupo = value;
		}
	}

	public double _GrupoCantidad
	{
		get
		{
			return GrupoCantidad;
		}
		set
		{
			GrupoCantidad = value;
		}
	}

	public bool _Borrado
	{
		get
		{
			return Borrado;
		}
		set
		{
			Borrado = value;
		}
	}

	public double _Orden
	{
		get
		{
			return Orden;
		}
		set
		{
			Orden = value;
		}
	}

	public double _CostoBruto
	{
		get
		{
			return CostoBruto;
		}
		set
		{
			CostoBruto = value;
		}
	}

	public double _CantidadMaxima
	{
		get
		{
			return CantidadMaxima;
		}
		set
		{
			CantidadMaxima = value;
		}
	}

	public int _Tiempo
	{
		get
		{
			return Tiempo;
		}
		set
		{
			Tiempo = value;
		}
	}

	public double _ICE_Fijo
	{
		get
		{
			return ICE_Fijo;
		}
		set
		{
			ICE_Fijo = value;
		}
	}

	public double _ICE_Porcentual
	{
		get
		{
			return ICE_Porcentual;
		}
		set
		{
			ICE_Porcentual = value;
		}
	}

	public string _CodigoSIN1
	{
		get
		{
			return CodigoSIN1;
		}
		set
		{
			CodigoSIN1 = value;
		}
	}

	public string _ActividadSIN1
	{
		get
		{
			return actividadSIN1;
		}
		set
		{
			actividadSIN1 = value;
		}
	}

	public int _UnidadSIN
	{
		get
		{
			return UnidadSIN;
		}
		set
		{
			UnidadSIN = value;
		}
	}

	public clsProductos()
	{
		Nombre = "";
		Descripcion = "";
		Precio = 0.0;
		FechaModificacionPrecio = DateAndTime.Now;
		Costo = 0.0;
		Stock = 0.0;
		CantidadML = 0.0;
		tienePreparacion = false;
		if (configuration.gSupermercado)
		{
			Habilitado = true;
		}
		else
		{
			Habilitado = false;
		}
		TipoProductoID = null;
		esCombo = false;
		esPorPeso = false;
		CategoriaProduccionID = null;
		Codigo = "";
		Comision = 0.0;
		Presentacion = "";
		UnidadContenido = "";
		Grupo = "";
		GrupoCantidad = 0.0;
		Borrado = false;
		Orden = 0.0;
		CostoBruto = 0.0;
		CantidadMaxima = 0.0;
		CodigoPY = "0";
		LinkFoto = "";
		Tiempo = 0;
		ICE_Fijo = 0.0;
		ICE_Porcentual = 0.0;
		HabilitadoPY = false;
		ExtraEnMesaID = 0;
		ExtraParaLlevarID = 0;
		CodigoSIN1 = "0";
		UnidadSIN = 0;
		actividadSIN1 = "0";
		manejaSerie = false;
		manejaImei = false;
	}

	public bool llenarclaseXcodigo()
	{
		DataTable dataTable = ((!configuration.gSupermercado) ? BD.ConsultaVer("*", "Productos", " codigo like '" + Codigo.Replace("'", "\"") + "' and Habilitado=" + VariableGeneral.armarBolean(1)) : BD.ConsultaVer("*", "Productos", " codigo Like '" + Codigo.Replace("'", "\"") + "'"));
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"])) ? ((object)0) : dataTable.Rows[0]["ID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			return true;
		}
		return false;
	}

	public Image getImageProducto()
	{
		Image result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("foto", "Productos_Fotos", "ID=" + ID);
			if (dataTable.Rows.Count > 0)
			{
				if (Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0])))
				{
					result = null;
				}
				else
				{
					byte[] buffer = (byte[])dataTable.Rows[0][0];
					result = Image.FromStream(new MemoryStream(buffer));
				}
			}
			else
			{
				result = null;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = null;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public string proximoCodigo()
	{
		DataTable dataTable = new DataTable();
		if (configuration.gMODO_ACCESS == 2)
		{
			dataTable = BD.ConsultaVer("max(codigo)", "Productos", "codigo REGEXP '^[0-9]+$'");
		}
		else if (configuration.gMODO_ACCESS == 1)
		{
			dataTable = BD.ConsultaVer("max(col)", "(select cdbl(Codigo) as col from Productos where ISNUMERIC(Codigo)) as tab1");
		}
		else if (configuration.gMODO_ACCESS == 0)
		{
			dataTable = BD.ConsultaVer("max(col)", "(select convert(float,Codigo) as col from Productos where ISNUMERIC(Codigo)=1) as tab1");
		}
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToLong(Operators.AddObject(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0])) ? ((object)0) : dataTable.Rows[0][0], 1)).ToString();
		}
		return Conversions.ToString(1);
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*, (" + devolverStocksXXX() + ") as Stock", "Productos", " ID=" + ID);
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"])) ? ((object)0) : dataTable.Rows[0]["ID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			Descripcion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"])) ? "" : dataTable.Rows[0]["Descripcion"]);
			Precio = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Precio"])) ? ((object)0) : dataTable.Rows[0]["Precio"]);
			FechaModificacionPrecio = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaModificacionPrecio"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaModificacionPrecio"]);
			Costo = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Costo"])) ? ((object)0) : dataTable.Rows[0]["Costo"]);
			Stock = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Stock"])) ? ((object)0) : dataTable.Rows[0]["Stock"]);
			CantidadML = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadML"])) ? ((object)0) : dataTable.Rows[0]["CantidadML"]);
			tienePreparacion = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TienePreparacion"])) ? ((object)false) : dataTable.Rows[0]["TienePreparacion"]);
			Habilitado = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Habilitado"])) ? ((object)false) : dataTable.Rows[0]["Habilitado"]);
			ConRecipienteLlevar = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ConRecipienteLlevar"])) ? ((object)false) : dataTable.Rows[0]["ConRecipienteLlevar"]);
			TipoProductoID = (int?)(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"])) ? ((object)0) : dataTable.Rows[0]["TipoProductoID"]);
			esCombo = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esCombo"])) ? ((object)0) : dataTable.Rows[0]["esCombo"]);
			esPorPeso = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esPorPeso"])) ? ((object)0) : dataTable.Rows[0]["esPorPeso"]);
			CategoriaProduccionID = (int?)(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CategoriaProduccionID"])) ? ((object)0) : dataTable.Rows[0]["CategoriaProduccionID"]);
			EscogePersonal = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EscogePersonal"])) ? ((object)false) : dataTable.Rows[0]["EscogePersonal"]);
			Codigo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Codigo"])) ? ((object)0) : dataTable.Rows[0]["Codigo"].ToString().Replace("\"", "'"));
			CantidadPaquete = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadPaquete"])) ? ((object)0) : dataTable.Rows[0]["CantidadPaquete"]);
			DiasUtiles = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DiasUtiles"])) ? ((object)0) : dataTable.Rows[0]["DiasUtiles"]);
			CantidadMinima = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadMinima"])) ? ((object)0) : dataTable.Rows[0]["CantidadMinima"]);
			Comision = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Comision"])) ? ((object)0) : dataTable.Rows[0]["Comision"]);
			Presentacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Presentacion"])) ? "" : dataTable.Rows[0]["Presentacion"]);
			UnidadContenido = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["UnidadContenido"])) ? "" : dataTable.Rows[0]["UnidadContenido"]);
			Grupo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Grupo"])) ? "" : dataTable.Rows[0]["Grupo"]);
			GrupoCantidad = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["GrupoCantidad"])) ? ((object)0) : dataTable.Rows[0]["GrupoCantidad"]);
			Borrado = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Borrado"])) ? ((object)false) : dataTable.Rows[0]["Borrado"]);
			Orden = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Orden"])) ? ((object)0) : dataTable.Rows[0]["Orden"]);
			CostoBruto = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CostoBruto"])) ? ((object)0) : dataTable.Rows[0]["CostoBruto"]);
			CantidadMaxima = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadMaxima"])) ? ((object)0) : dataTable.Rows[0]["CantidadMaxima"]);
			Tiempo = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Tiempo"])) ? ((object)0) : dataTable.Rows[0]["Tiempo"]);
			ICE_Fijo = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ICE_Fijo"])) ? ((object)0) : dataTable.Rows[0]["ICE_Fijo"]);
			ICE_Porcentual = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ICE_Porcentual"])) ? ((object)0) : dataTable.Rows[0]["ICE_Porcentual"]);
			ExtraEnMesaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ExtraEnMesaID"])) ? ((object)0) : dataTable.Rows[0]["ExtraEnMesaID"]);
			ExtraParaLlevarID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ExtraParaLlevarID"])) ? ((object)0) : dataTable.Rows[0]["ExtraParaLlevarID"]);
			CodigoSIN1 = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoSIN"])) ? "0" : dataTable.Rows[0]["CodigoSIN"]);
			UnidadSIN = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["UnidadSIN"])) ? ((object)0) : dataTable.Rows[0]["UnidadSIN"]);
			actividadSIN1 = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["actividadSIN"])) ? "0" : dataTable.Rows[0]["actividadSIN"]);
			manejaSerie = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["manejaSerie"])) ? ((object)false) : dataTable.Rows[0]["manejaSerie"]);
			manejaImei = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["manejaImei"])) ? ((object)false) : dataTable.Rows[0]["manejaImei"]);
			try
			{
				CodigoPY = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoPY"])) ? "0" : dataTable.Rows[0]["CodigoPY"]);
				LinkFoto = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["LinkFoto"])) ? "" : dataTable.Rows[0]["LinkFoto"]);
				HabilitadoPY = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["HabilitadoPY"])) ? ((object)false) : dataTable.Rows[0]["HabilitadoPY"]);
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				CodigoPY = "0";
				LinkFoto = "";
				HabilitadoPY = false;
				ProjectData.ClearProjectError();
			}
		}
	}

	public DataTable Devolver(bool alaVenta)
	{
		return BD.ConsultaVer("Productos.ID,Productos.Codigo,Productos.Nombre, Productos.Descripcion,Productos.Precio,(" + devolverStocksXXX() + ") as Stock,Productos.TienePreparacion,Productos.Habilitado,esPorPeso,TiposProductos.Descripcion As TiposProductos,Productos.Orden", "Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID", "Borrado=" + VariableGeneral.armarBolean(0) + " and Habilitado=" + VariableGeneral.armarBolean(alaVenta), "Productos.Nombre");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select Productos.ID,Productos.Codigo,Productos.Nombre,Productos.Descripcion,Productos.Precio,Productos.FechaModificacionPrecio,Productos.Costo,(" + devolverStocksXXX() + ") as Stock,Productos.CantidadML,Productos.TienePreparacion,Productos.Habilitado,Productos.TipoProductoID As TipoProductoID,TiposProductos.Descripcion As TiposProductos,Productos.ConRecipienteLlevar,esCombo,esPorPeso,CantidadPaquete, DiasUtiles,CantidadMinima,Productos.Codigo,Comision,Grupo, GrupoCantidad,Borrado", "Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID and Borrado=" + VariableGeneral.armarBolean(0) + ") as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int habilitarTodaCategoria(bool habilitar, int id)
	{
		int result;
		try
		{
			BD.ConsultaModificar("Productos", "Habilitado=" + VariableGeneral.armarBolean(habilitar), "Borrado=" + VariableGeneral.armarBolean(0) + " and TipoProductoID=" + id);
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool devolverDetalleProducto()
	{
		DataTable dataTable = BD.ConsultaVer("Productos.Nombre,Productos.Descripcion,CantidadML,tienePreparacion,Productos.FechaModificacionPrecio,(" + devolverStocksXXX() + ") as Stock,Productos.Precio,Costo,TipoProductoID,Productos.ConRecipienteLlevar,esCombo,esPorPeso,EscogePersonal,Presentacion,Grupo,GrupoCantidad,Borrado,Orden,CostoBruto,CantidadMaxima", "Productos", "Productos.ID=" + ID);
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(dataTable.Rows[0]["Nombre"]);
			Descripcion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["descripcion"]), ""));
			Precio = Conversions.ToDouble(dataTable.Rows[0]["Precio"]);
			Stock = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["stock"]), 0));
			Costo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Costo"]), 0));
			CantidadML = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadML"]), 0));
			FechaModificacionPrecio = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaModificacionPrecio"]), DateAndTime.Now));
			tienePreparacion = Conversions.ToBoolean(dataTable.Rows[0]["tienePreparacion"]);
			ConRecipienteLlevar = Conversions.ToBoolean(dataTable.Rows[0]["ConRecipienteLlevar"]);
			TipoProductoID = (int?)dataTable.Rows[0]["TipoProductoID"];
			esCombo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esCombo"]), 0));
			esPorPeso = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esPorPeso"]), 0));
			Presentacion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Presentacion"]), ""));
			EscogePersonal = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EscogePersonal"]), 0));
			Grupo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Grupo"]), ""));
			GrupoCantidad = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["GrupoCantidad"]), 0));
			Borrado = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Borrado"]), 0));
			Orden = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Orden"]), 0));
			CostoBruto = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CostoBruto"]), 0));
			CantidadMaxima = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadMaxima"]), 0));
			return true;
		}
		return false;
	}

	public DataTable devolverProductosPorTipoProducto()
	{
		return BD.ConsultaVer("ID as ProductoID, Nombre", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and TipoProductoID=" + TipoProductoID, "Nombre");
	}

	public DataTable DevolverProductosCompra(DateTime fechai, DateTime fechaf, int idProducto)
	{
		if (idProducto > 0)
		{
			return BD.ConsultaVer("Compras.NroComprobante,Compras.FechaRecepcion as 'Fecha Pedido Compra',Productos.Codigo As Productos, DetalleProductosCompra.PosibleFechaEntrega as 'Posible Fecha Entrada',DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,DetalleProductosCompra.CostoUnitario as 'Costo Unitario',DetalleProductosCompra.Cantidad*DetalleProductosCompra.CostoUnitario as Total ", "DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID", "( Compras.FechaRecepcion between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf) + ") and  Productos.ID=" + Conversions.ToString(idProducto));
		}
		return BD.ConsultaVer("Compras.NroComprobante,Compras.FechaRecepcion as 'Fecha Pedido Compra', DetalleProductosCompra.PosibleFechaEntrega as 'Posible Fecha Entrega',DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,DetalleProductosCompra.CostoUnitario as 'Costo Unitario',DetalleProductosCompra.Cantidad*DetalleProductosCompra.CostoUnitario as Total ", "DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID", "Compras.FechaRecepcion between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf) + " ");
	}

	public DataTable ToReturnProductosSinPreparacion()
	{
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos", ("Borrado=" + VariableGeneral.armarBolean(0) + " and TienePreparacion=" + VariableGeneral.armarBolean(0)) ?? "", "Nombre");
	}

	public DataTable ToReturnProductosHabilitadosByTipo()
	{
		string[] obj = new string[6]
		{
			"Borrado=",
			VariableGeneral.armarBolean(0),
			" and Habilitado=",
			VariableGeneral.armarBolean(1),
			" and TipoProductoID=",
			null
		};
		int? tipoProductoID;
		int? num = (tipoProductoID = TipoProductoID);
		obj[5] = (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null);
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos", string.Concat(obj), "Nombre");
	}

	public DataTable ToReturnProductosSinPreparacionByTipo()
	{
		string[] obj = new string[6]
		{
			"Borrado=",
			VariableGeneral.armarBolean(0),
			" and TienePreparacion=",
			VariableGeneral.armarBolean(0),
			" and TipoProductoID=",
			null
		};
		int? tipoProductoID;
		int? num = (tipoProductoID = TipoProductoID);
		obj[5] = (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null);
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos", string.Concat(obj), "Nombre");
	}

	public DataTable ToReturnProductosByTipo()
	{
		string text = VariableGeneral.armarBolean(0);
		int? tipoProductoID;
		int? num = (tipoProductoID = TipoProductoID);
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos", "Borrado=" + text + " and TipoProductoID=" + (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null), "Nombre");
	}

	public DataTable ToReturnProductosByTipoMenosYo(int yoID)
	{
		string[] obj = new string[6]
		{
			"Borrado=",
			VariableGeneral.armarBolean(0),
			" and TipoProductoID=",
			null,
			null,
			null
		};
		int? tipoProductoID;
		int? num = (tipoProductoID = TipoProductoID);
		obj[3] = (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null);
		obj[4] = " and ID<>";
		obj[5] = Conversions.ToString(yoID);
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos", string.Concat(obj), "Nombre");
	}

	public DataTable ToReturn()
	{
		return BD.ConsultaVer("Productos.ID,Productos.Nombre,Productos.Descripcion,Productos.Precio,Productos.FechaModificacionPrecio,Productos.Costo, Productos.CantidadML, Productos.TienePreparacion, TiposProductos.Descripcion,Productos.ConRecipienteLlevar", "Productos left join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID", "Borrado=" + VariableGeneral.armarBolean(0) + " ", "Nombre");
	}

	public DataTable ToReturnProductosActivosByTipo()
	{
		string[] obj = new string[6]
		{
			"Borrado=",
			VariableGeneral.armarBolean(0),
			" and Habilitado=",
			VariableGeneral.armarBolean(1),
			" and TiposProductos.TipoProductoID=",
			null
		};
		int? tipoProductoID;
		int? num = (tipoProductoID = TipoProductoID);
		obj[5] = (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null);
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos  left join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID", string.Concat(obj), "Productos.Orden,Nombre");
	}

	public DataTable ToReturnProductosActivosByTipo1(string data)
	{
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos  left join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID", "Borrado=" + VariableGeneral.armarBolean(0) + " and Habilitado=" + VariableGeneral.armarBolean(1) + " and TiposProductos.Descripcion='" + data + "'", "Productos.Orden,Nombre");
	}

	public DataTable ToReturnProductosActivosByTipoKiosko(string data)
	{
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos  left join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID", "Borrado=" + VariableGeneral.armarBolean(0) + " and Habilitado=" + VariableGeneral.armarBolean(1) + " and esPorPeso=" + VariableGeneral.armarBolean(1) + " and TiposProductos.Descripcion='" + data + "'", "Productos.Orden,Nombre");
	}

	public DataTable ToReturnProductosByTipo1(string data)
	{
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos  left join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID", "Borrado=" + VariableGeneral.armarBolean(0) + " and TiposProductos.Descripcion='" + data + "'", "Productos.Orden,Nombre");
	}

	public DataTable obtenerProductosYPreparaciones()
	{
		return BD.ConsultaVer("Preparaciones.ParaProductoID,sum(Productos.Costo/Productos.CantidadML*Preparaciones.cantidadML) AS CostoPrep", "Productos INNER JOIN Preparaciones ON Productos.ID = Preparaciones.DeProductoID", "", "", "Preparaciones.ParaProductoID");
	}

	public DataTable obtenerCombosConceptosByProducto()
	{
		DataTable dataTable = BD.ConsultaVer("Preparaciones.Concepto", "((Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID) inner join Productos as P1 on (P1.id=Preparaciones.ParaProductoID and P1.EsCombo=" + VariableGeneral.armarBolean(1) + ")", "Productos.Borrado=" + VariableGeneral.armarBolean(0) + " and Preparaciones.ParaProductoID=" + ID, "min(Preparaciones.PreparacionID)", "Preparaciones.Concepto");
		string text = VariableGeneral.armarBolean(0);
		int? tipoProductoID;
		int? num = (tipoProductoID = _TipoProductoID);
		DataTable table = BD.ConsultaVer("Preparaciones.Concepto", "(Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID", "Productos.Borrado=" + text + " and  Preparaciones.ParaCategoriaID=" + (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null), "min(Preparaciones.PreparacionID)", "Preparaciones.Concepto");
		dataTable.Merge(table, preserveChanges: true);
		return dataTable;
	}

	public DataTable obtenerCombosConceptosByProductoActivo()
	{
		DataTable dataTable = BD.ConsultaVer("distinct Preparaciones.Concepto", "((Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID)inner join Productos as P1 on (P1.id=Preparaciones.ParaProductoID and P1.EsCombo=" + VariableGeneral.armarBolean(1) + ")", "Productos.Borrado=" + VariableGeneral.armarBolean(0) + " and (Preparaciones.ParaProductoID=" + ID + " and Productos.EsCombo=" + VariableGeneral.armarBolean(1) + " ) and productos.Habilitado= " + VariableGeneral.armarBolean(1), "Preparaciones.Concepto");
		string[] obj = new string[6]
		{
			"Borrado=",
			VariableGeneral.armarBolean(0),
			" and (Preparaciones.ParaCategoriaID=",
			null,
			null,
			null
		};
		int? tipoProductoID;
		int? num = (tipoProductoID = _TipoProductoID);
		obj[3] = (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null);
		obj[4] = " ) and productos.Habilitado= ";
		obj[5] = VariableGeneral.armarBolean(1);
		DataTable table = BD.ConsultaVer("distinct Preparaciones.Concepto", "(Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID", string.Concat(obj), "Preparaciones.Concepto");
		dataTable.Merge(table, preserveChanges: true);
		return dataTable;
	}

	public DataTable obtenerCombosProductosByProducto(int TipoEnvioID)
	{
		if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.BiancaFlor) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PokePoke))
		{
			TipoEnvioID = 0;
		}
		if (!_TipoProductoID.HasValue)
		{
			_TipoProductoID = 0;
		}
		string text = "";
		text = ((configuration.gMODO_ACCESS != 1) ? (" + CASE WHEN Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " IS NULL THEN 0 ELSE Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " END ") : (" +  iif( isnull(Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + "), 0, Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " ) "));
		DataTable dataTable = BD.ConsultaVer("Preparaciones.Concepto, Preparaciones.Cantidad, Productos.ID, Productos.Nombre, (PreparacionesComodines.Precio" + ((TipoEnvioID > 0) ? text : "") + ") as Precio, PreparacionesComodines.ModificaPrecio, Preparaciones.PuedeDisminuir, ModificaProductoID", "((Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID) inner join Productos as P1 on (P1.id=Preparaciones.ParaProductoID and P1.EsCombo=" + VariableGeneral.armarBolean(1) + ")", "Productos.Borrado=" + VariableGeneral.armarBolean(0) + " and Preparaciones.ParaProductoID=" + ID, "Preparaciones.PreparacionID");
		string data = "Preparaciones.Concepto, Preparaciones.Cantidad, Productos.ID, Productos.Nombre, (PreparacionesComodines.Precio" + ((TipoEnvioID > 0) ? text : "") + ") as Precio, PreparacionesComodines.ModificaPrecio, Preparaciones.PuedeDisminuir, ModificaProductoID";
		string text2 = VariableGeneral.armarBolean(0);
		int? tipoProductoID;
		int? num = (tipoProductoID = _TipoProductoID);
		DataTable table = BD.ConsultaVer(data, "(Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID", "Borrado=" + text2 + " and Preparaciones.ParaCategoriaID=" + (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null), "Preparaciones.PreparacionID");
		dataTable.Merge(table, preserveChanges: true);
		return dataTable;
	}

	public DataTable obtenerCombosProductosByProductoOrder(int TipoEnvioID)
	{
		if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.BiancaFlor) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PokePoke))
		{
			TipoEnvioID = 0;
		}
		if (!_TipoProductoID.HasValue)
		{
			_TipoProductoID = 0;
		}
		string text = "";
		text = ((configuration.gMODO_ACCESS != 1) ? (" + CASE WHEN Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " IS NULL THEN 0 ELSE Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " END ") : (" +  iif( isnull(Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + "), 0, Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " ) "));
		DataTable dataTable = BD.ConsultaVer("Preparaciones.Concepto, Preparaciones.Cantidad, Productos.ID, Productos.Nombre, (PreparacionesComodines.Precio" + ((TipoEnvioID > 0) ? text : "") + ") as Precio, PreparacionesComodines.ModificaPrecio, Preparaciones.PuedeDisminuir, ModificaProductoID", "((Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID) inner join Productos as P1 on (P1.id=Preparaciones.ParaProductoID and P1.EsCombo=" + VariableGeneral.armarBolean(1) + ")", "Productos.Borrado=" + VariableGeneral.armarBolean(0) + " and Preparaciones.ParaProductoID=" + ID, "Productos.Nombre");
		string data = "Preparaciones.Concepto, Preparaciones.Cantidad, Productos.ID, Productos.Nombre, (PreparacionesComodines.Precio" + ((TipoEnvioID > 0) ? text : "") + ") as Precio, PreparacionesComodines.ModificaPrecio, Preparaciones.PuedeDisminuir, ModificaProductoID";
		string text2 = VariableGeneral.armarBolean(0);
		int? tipoProductoID;
		int? num = (tipoProductoID = _TipoProductoID);
		DataTable table = BD.ConsultaVer(data, "(Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID", "Borrado=" + text2 + " and Preparaciones.ParaCategoriaID=" + (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null), "Productos.Nombre");
		dataTable.Merge(table, preserveChanges: true);
		return dataTable;
	}

	public DataTable obtenerCombosProductosByProductoActivo(int TipoEnvioID)
	{
		if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.BiancaFlor) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PokePoke))
		{
			TipoEnvioID = 0;
		}
		if (!_TipoProductoID.HasValue)
		{
			_TipoProductoID = 0;
		}
		string text = "";
		text = ((configuration.gMODO_ACCESS != 1) ? (" + CASE WHEN Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " IS NULL THEN 0 ELSE Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " END ") : (" +  iif( isnull(Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + "), 0, Productos.PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " ) "));
		DataTable dataTable = BD.ConsultaVer("Preparaciones.Concepto, Preparaciones.Cantidad, Productos.ID, Productos.Nombre,(PreparacionesComodines.Precio" + ((TipoEnvioID > 0) ? text : "") + ") as Precio, PreparacionesComodines.ModificaPrecio, Preparaciones.PuedeDisminuir, ModificaProductoID", "((Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID) inner join Productos as P1 on (P1.id=Preparaciones.ParaProductoID and P1.EsCombo=" + VariableGeneral.armarBolean(1) + ")", "Productos.Borrado=" + VariableGeneral.armarBolean(0) + " and (Preparaciones.ParaProductoID=" + ID + ") and productos.Habilitado= " + VariableGeneral.armarBolean(1), "Preparaciones.Concepto");
		string data = "Preparaciones.Concepto, Preparaciones.Cantidad, Productos.ID, Productos.Nombre,(PreparacionesComodines.Precio" + ((TipoEnvioID > 0) ? text : "") + ") as Precio, PreparacionesComodines.ModificaPrecio, Preparaciones.PuedeDisminuir, ModificaProductoID";
		string[] obj = new string[6]
		{
			"Borrado=",
			VariableGeneral.armarBolean(0),
			" and (Preparaciones.ParaCategoriaID=",
			null,
			null,
			null
		};
		int? tipoProductoID;
		int? num = (tipoProductoID = _TipoProductoID);
		obj[3] = (num.HasValue ? Conversions.ToString(tipoProductoID.GetValueOrDefault()) : null);
		obj[4] = ") and productos.Habilitado= ";
		obj[5] = VariableGeneral.armarBolean(1);
		DataTable table = BD.ConsultaVer(data, "(Preparaciones INNER JOIN PreparacionesComodines ON Preparaciones.PreparacionID = PreparacionesComodines.PreparacionID) INNER JOIN Productos ON PreparacionesComodines.DeProductoID = Productos.ID ", string.Concat(obj), "Preparaciones.Concepto");
		dataTable.Merge(table, preserveChanges: true);
		return dataTable;
	}

	public bool modificarCosto()
	{
		bool result;
		try
		{
			BD.ConsultaModificar("Productos", ("Costo=" + Conversion.Str(Costo)) ?? "", "ID=" + ID);
			result = true;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool modificarCostoBruto()
	{
		bool result;
		try
		{
			BD.ConsultaModificar("Productos", ("CostoBruto=" + Conversion.Str(CostoBruto)) ?? "", "ID=" + ID);
			result = true;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool AnhadirStockProducto(double cantProducto, int almacenID)
	{
		bool result;
		try
		{
			BD.ConsultaModificar("Productos", ("Stock" + Conversions.ToString(almacenID) + "=Stock" + Conversions.ToString(almacenID) + "+" + Conversion.Str(cantProducto)) ?? "", "ID=" + ID);
			result = true;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public DataTable ToReturnProductosActive()
	{
		return BD.ConsultaVer("Productos.ID,Productos.Nombre,Productos.Codigo", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Habilitado=" + VariableGeneral.armarBolean(1), "Nombre");
	}

	public DataTable ToReturnProductosPriceByName()
	{
		return BD.ConsultaVer("Productos.ID,Productos.Precio", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Nombre like '" + Nombre + "'");
	}

	public DataTable ToReturnProductosPriceImpresoraByCodigo()
	{
		return BD.ConsultaVer("Productos.ID,Productos.Nombre,Productos.Precio, Impresoras.Nombre as Impresora, ManejarStock, ConRecipienteLlevar,esCombo,esPorPeso,EscogePersonal,Productos.TipoProductoID, TiposProductos.AlmacenID, TiposProductos.DocumentoSector", "(Productos left join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID) left join Impresoras on TiposProductos.ImpresoraID=Impresoras.ImpresoraID", "Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.Codigo like '" + Codigo + "'");
	}

	public void cargarIdDesdeCodigo(ref int sector)
	{
		DataTable dataTable = BD.ConsultaVer("Id,TiposProductos.DocumentoSector", "Productos left join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID", "Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.Codigo like '" + Codigo.Replace("'", "\"") + "'");
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(dataTable.Rows[0]["ID"]);
			sector = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"]), 0));
		}
		else
		{
			ID = 0;
			sector = 0;
		}
	}

	public int ToReturnIdbySKUpedidosYa(ref string nombre1)
	{
		DataTable dataTable = BD.ConsultaVer("Id,nombre", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.CodigoPY like '" + CodigoPY + "'");
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(dataTable.Rows[0]["ID"]);
			nombre1 = Conversions.ToString(dataTable.Rows[0]["nombre"]);
		}
		else
		{
			ID = 0;
			Nombre = "";
		}
		return ID;
	}

	public int ToReturnIdbyNameDelivery()
	{
		DataTable dataTable = ((configuration.gMODO_ACCESS != 1) ? BD.ConsultaVer("id", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and iif(CHARINDEX('(',nombre) > 0, RTRIM(SUBSTRING(nombre,0,CHARINDEX('(',nombre))), nombre) like '" + Nombre + "'") : BD.ConsultaVer("id", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and  IIf(InStr(Nombre,'(') > 0,  Mid(nombre,1,InStr(nombre,'(') -1 ), nombre) like '" + Nombre + "'"));
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), ""));
			return ID;
		}
		ID = 0;
		return 0;
	}

	public int ToReturnIdbyName(BD_SQL bd1 = null)
	{
		if (bd1 != null)
		{
			DataTable dataTable = bd1.ConsultaVer("id", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and nombre like '" + Nombre + "'");
			if (dataTable.Rows.Count > 0)
			{
				ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), ""));
				return ID;
			}
			ID = 0;
			return 0;
		}
		DataTable dataTable2 = BD.ConsultaVer("id", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and nombre like '" + Nombre + "'");
		if (dataTable2.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0][0]), ""));
			return ID;
		}
		ID = 0;
		return 0;
	}

	public string DevolverNombrexID(int IDPro)
	{
		if (IDPro > 0)
		{
			DataTable dataTable = BD.ConsultaVer("Nombre", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and ID = " + IDPro);
			if (dataTable.Rows.Count > 0)
			{
				return Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), ""));
			}
			return "";
		}
		return "";
	}

	public string ToReturnObservaciones()
	{
		if (ID > 0)
		{
			return Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("Descripcion", "Productos", ("Id= " + Conversions.ToString(ID)) ?? "").Rows[0][0]), ""));
		}
		return "";
	}

	public DataTable ToReturnProductosPriceImpresoraByID(int TipoEnvioID)
	{
		string text = "";
		if (TipoEnvioID > 0)
		{
			text = ((configuration.gMODO_ACCESS != 1) ? (" + CASE WHEN PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " IS NULL THEN 0 ELSE PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " END ") : (" +  iif( isnull(PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + "), 0, PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " ) "));
		}
		return BD.ConsultaVer("Productos.Nombre,Productos.Precio " + text + " as Precio,Productos.Codigo, Impresoras.Nombre as Impresora, ManejarStock, ConRecipienteLlevar,esCombo,esPorPeso,EscogePersonal,Productos.TipoProductoID, TiposProductos.AlmacenID, TiposProductos.DocumentoSector", "(Productos left join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID) left join Impresoras on TiposProductos.ImpresoraID=Impresoras.ImpresoraID", ("Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.ID = " + Conversions.ToString(ID)) ?? "");
	}

	public DataTable ToReturnProductosPriceImpresoraByName(int TipoEnvioID)
	{
		string text = "";
		if (TipoEnvioID > 0)
		{
			text = ((configuration.gMODO_ACCESS != 1) ? (" + CASE WHEN PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " IS NULL THEN 0 ELSE PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " END ") : (" +  iif( isnull(PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + "), 0, PrecioExtraTipoEnvio" + Conversions.ToString(TipoEnvioID) + " ) "));
		}
		return BD.ConsultaVer("Productos.ID,Productos.Precio " + text + " as Precio ,Productos.Codigo, Impresoras.Nombre as Impresora, ManejarStock, ConRecipienteLlevar,esCombo,esPorPeso,EscogePersonal,Productos.TipoProductoID, TiposProductos.AlmacenID, TiposProductos.DocumentoSector", "(Productos left join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID) left join Impresoras on TiposProductos.ImpresoraID=Impresoras.ImpresoraID", "Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.Nombre like '" + Nombre + "'");
	}

	public string toReturnToolTipStock(bool ParaLLevar)
	{
		checked
		{
			string result;
			try
			{
				int num = Conversions.ToInteger(BD.ConsultaVer("AlmacenID", "Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID", "Borrado=" + VariableGeneral.armarBolean(0) + " and Nombre = '" + Nombre + "'").Rows[0][0]);
				DataTable dataTable = BD.ConsultaVer("Productos.ID,TienePreparacion,TiposProductos.ManejarStock, Stock" + Conversions.ToString(num) + " as stock,EsCombo, ExtraEnMesaID, ExtraParaLlevarID ", "Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID", "Borrado=" + VariableGeneral.armarBolean(0) + " and Nombre = '" + Nombre + "'");
				if (dataTable.Rows.Count == 0)
				{
					result = "";
				}
				else
				{
					_ID = Conversions.ToInteger(dataTable.Rows[0][0]);
					_tienePreparacion = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TienePreparacion"]), false));
					bool flag = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ManejarStock"]), false));
					bool flag2 = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EsCombo"]), false));
					if (!flag)
					{
						result = "";
					}
					else
					{
						int num2 = 0;
						num2 = ((!ParaLLevar) ? Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ExtraEnMesaID"]), 0)) : Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ExtraParaLlevarID"]), 0)));
						Stock = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Stock"]), 0));
						if (!_tienePreparacion)
						{
							result = (flag2 ? "Es combo" : ((!flag) ? "" : ((!(Stock >= 0.0)) ? "Quedan: 0" : Conversions.ToString(Operators.ConcatenateObject("Quedan: ", VariableGeneral.NZ(Stock, 0))))));
						}
						else
						{
							ctlPreparacionesComodines ctlPreparacionesComodines2 = new ctlPreparacionesComodines();
							DataTable tbFinal = ctlPreparacionesComodines2.DevolverPreparacionesParaProducto(_ID, num);
							tbFinal.Rows.Clear();
							ctlPreparacionesComodines2.getProductsRecursive(_ID, ref tbFinal, 1.0, telefono: false, num, 0);
							if (num2 > 0)
							{
								ctlPreparacionesComodines2.getProductsRecursive(num2, ref tbFinal, 1.0, telefono: false, num, 0);
							}
							int num3 = 0;
							int num4 = tbFinal.Rows.Count - 1;
							for (int i = 0; i <= num4; i++)
							{
								if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(tbFinal.Rows[i]["Cantidad"]), 0), 0, TextCompare: false))
								{
									num3 = 0;
									break;
								}
								int num5 = Conversions.ToInteger(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
								{
									Operators.DivideObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(tbFinal.Rows[i]["enStock"]), 0), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(tbFinal.Rows[i]["Cantidad"]), 0)),
									0
								}, null, null, null));
								if (num5 < 0)
								{
									num3 = 0;
									break;
								}
								if (i == 0)
								{
									num3 = num5;
								}
								if (num5 < num3)
								{
									num3 = num5;
								}
							}
							string text = ((num3 <= 0) ? "Quedan: 0" : ("Quedan: " + Conversions.ToString(num3)));
							result = text;
						}
					}
				}
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				result = "";
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public string toReturnToolTipCodigo()
	{
		string result;
		try
		{
			result = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("Codigo", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Nombre = '" + Nombre + "'").Rows[0]["Codigo"]), ""));
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = "";
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public void CargarDatosPaquete()
	{
		DataTable dataTable = BD.ConsultaVer("ID, Nombre,CantidadPaquete,DiasUtiles", "Productos", ("Borrado=" + VariableGeneral.armarBolean(0) + " and ID =" + Conversions.ToString(ID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""));
			CantidadPaquete = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadPaquete"]), 0));
			DiasUtiles = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DiasUtiles"]), 0));
		}
		else
		{
			Nombre = "";
			CantidadPaquete = 0;
			DiasUtiles = 0;
		}
	}

	public void cargarDatosCombo(ref string ImpresoraFisica)
	{
		DataTable dataTable = BD.ConsultaVer("Productos.Nombre,Impresoras.NombreFisico", "(Productos LEFT JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) LEFT JOIN Impresoras ON TiposProductos.ImpresoraID = Impresoras.ImpresoraID", ("Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.ID =" + Conversions.ToString(ID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(NewLateBinding.LateGet(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""), null, "Replace", new object[2] { "\"", "'" }, null, null, null));
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? "" : dataTable.Rows[0]["NombreFisico"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			ImpresoraFisica = ((text == null) ? "" : text);
		}
		else
		{
			Nombre = "";
		}
	}

	public void cargarDatosComboKitchenDisplay(ref string ImpresoraFisica)
	{
		DataTable dataTable = BD.ConsultaVer("Productos.Nombre,Impresoras.NombreFisico", "(Productos LEFT JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) LEFT JOIN Impresoras ON TiposProductos.kitchenDisplayID = Impresoras.ImpresoraID", ("Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.ID =" + Conversions.ToString(ID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(NewLateBinding.LateGet(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""), null, "Replace", new object[2] { "\"", "'" }, null, null, null));
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? "" : dataTable.Rows[0]["NombreFisico"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			ImpresoraFisica = ((text == null) ? "" : text);
		}
		else
		{
			Nombre = "";
		}
	}

	public void CargarDatos(int almacenId)
	{
		DataTable dataTable = BD.ConsultaVer("TienePreparacion,Nombre,CantidadML, TipoProductoID, Costo, esCombo,stock" + Conversions.ToString(almacenId) + " as stock, esPorPeso,EscogePersonal,Precio,Comision", "Productos", ("Borrado=" + VariableGeneral.armarBolean(0) + " and ID =" + Conversions.ToString(ID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(NewLateBinding.LateGet(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""), null, "Replace", new object[2] { "\"", "'" }, null, null, null));
			tienePreparacion = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TienePreparacion"]), false));
			Stock = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Stock"]), 0));
			CantidadML = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadML"]), 0));
			TipoProductoID = (int?)VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0);
			Costo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Costo"]), 0));
			esCombo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esCombo"]), false));
			esPorPeso = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esPorPeso"]), false));
			EscogePersonal = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EscogePersonal"]), false));
			Precio = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Precio"]), 0));
			Comision = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Comision"]), 0));
		}
		else
		{
			Nombre = "";
			tienePreparacion = false;
			CantidadML = 0.0;
			Stock = 0.0;
			Costo = 0.0;
			esCombo = false;
			esPorPeso = false;
			EscogePersonal = false;
			Precio = 0.0;
			Comision = 0.0;
		}
	}

	public void cargarDatosSIN(ref string UnidadSIN1)
	{
		DataTable dataTable = BD.ConsultaVer("Productos.Codigo, Nombre,CodigoSIN,ActividadSIN,Productos.UnidadSIN, FactElectUnidadesMedidas.Descripcion as Medida", "Productos left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN ", ("ID =" + Conversions.ToString(ID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(NewLateBinding.LateGet(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""), null, "Replace", new object[2] { "\"", "'" }, null, null, null));
			Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Codigo"]), ""));
			CodigoSIN1 = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoSIN"]), ""));
			UnidadSIN1 = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Medida"]), ""));
			UnidadSIN = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["UnidadSIN"]), 0));
			actividadSIN1 = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ActividadSIN"]), ""));
		}
		else
		{
			Nombre = "";
			Codigo = "";
			CodigoSIN1 = "";
			UnidadSIN1 = "";
			UnidadSIN = 0;
			actividadSIN1 = "";
		}
	}

	public void CargarDatos()
	{
		DataTable dataTable = BD.ConsultaVer("TienePreparacion,Nombre,CantidadML, TipoProductoID, Costo, esCombo, esPorPeso,EscogePersonal,Precio", "Productos", ("ID =" + Conversions.ToString(ID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(NewLateBinding.LateGet(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""), null, "Replace", new object[2] { "\"", "'" }, null, null, null));
			tienePreparacion = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TienePreparacion"]), false));
			CantidadML = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadML"]), 0));
			TipoProductoID = (int?)VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"]), 0);
			Costo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Costo"]), 0));
			esCombo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esCombo"]), false));
			esPorPeso = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esPorPeso"]), false));
			EscogePersonal = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EscogePersonal"]), false));
			Precio = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Precio"]), 0));
		}
		else
		{
			Nombre = "";
			tienePreparacion = false;
			CantidadML = 0.0;
			Stock = 0.0;
			Costo = 0.0;
			esCombo = false;
			esPorPeso = false;
			EscogePersonal = false;
			Precio = 0.0;
		}
	}

	public void CargarPrecio()
	{
		DataTable dataTable = BD.ConsultaVer("Precio", "Productos", ("ID =" + Conversions.ToString(ID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			Precio = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Precio"]), 0));
		}
		else
		{
			Precio = 0.0;
		}
	}

	public int modificarStock(double cant, int almacenID, BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (double.IsInfinity(cant))
			{
				result = 0;
			}
			else if (bd1 != null)
			{
				bd1.ConsultaModificar("Productos", "Stock" + Conversions.ToString(almacenID) + "= CASE WHEN  Productos.Stock" + Conversions.ToString(almacenID) + " is null  THEN  0 ELSE  Stock" + Conversions.ToString(almacenID) + " END + (" + Conversion.Str(cant) + ")", "ID=" + ID);
				result = 1;
			}
			else
			{
				if (configuration.gMODO_ACCESS == 1)
				{
					BD.ConsultaModificar("Productos", "Stock" + Conversions.ToString(almacenID) + "= iif(Productos.Stock" + Conversions.ToString(almacenID) + " is null,0,Stock" + Conversions.ToString(almacenID) + ") + (" + Conversion.Str(cant) + ")", "ID=" + ID);
				}
				else
				{
					BD.ConsultaModificar("Productos", "Stock" + Conversions.ToString(almacenID) + "= CASE WHEN  Productos.Stock" + Conversions.ToString(almacenID) + " is null  THEN  0 ELSE  Stock" + Conversions.ToString(almacenID) + " END + (" + Conversion.Str(cant) + ")", "ID=" + ID);
				}
				result = 1;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int ModificarCostoTraspaso(double cant, double costo1, int almacenID, BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (bd1 != null)
			{
				bd1.ConsultaModificar("Productos", "Costo= CASE WHEN  Productos.Costo is null  or Productos.Costo = 0 or Stock" + Conversions.ToString(almacenID) + "<=0   THEN  " + Conversion.Str(costo1) + " ELSE   (((Costo * Stock" + Conversions.ToString(almacenID) + ") +  (" + Conversion.Str(cant * costo1) + "))/ (Stock" + Conversions.ToString(almacenID) + " + " + Conversion.Str(cant) + " ) ) END", "ID=" + ID);
				result = 1;
			}
			else
			{
				result = 1;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int ModificarCostoProduccion(double cant, double costo1, int almacenID)
	{
		int result;
		try
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				BD.ConsultaModificar("Productos", "Costo= iif(Productos.Stock" + Conversions.ToString(almacenID) + " is null  or Productos.Costo = 0  or Stock" + Conversions.ToString(almacenID) + "<=0 ," + Conversion.Str(costo1) + ",((Costo * Stock" + Conversions.ToString(almacenID) + ") +  (" + Conversion.Str(cant * costo1) + "))/ (Stock" + Conversions.ToString(almacenID) + " + " + Conversion.Str(cant) + " ) )", "ID=" + ID);
			}
			else if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Belen)
			{
				BD.ConsultaModificar("Productos", "Costo= CASE WHEN  Costo is null or Costo = 0 or Stock" + Conversions.ToString(almacenID) + "<=0 THEN  " + Conversion.Str(costo1) + " ELSE   (((Costo * Stock" + Conversions.ToString(almacenID) + ") +  (" + Conversion.Str(cant * costo1) + "))/ (Stock" + Conversions.ToString(almacenID) + " + " + Conversion.Str(cant) + " ) ) END", "ID=" + ID);
			}
			else
			{
				BD.ConsultaModificar("Productos", "Costo= " + Conversion.Str(costo1), "ID=" + ID);
			}
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int Modify(BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (bd1 != null)
			{
				string text = new clsTiposProductos().devolverTipoProductoPorID(TipoProductoID.Value);
				bd1.ConsultaModificar("Productos", ("Nombre='" + Nombre + "',Descripcion='" + Descripcion + "',codigo='" + Codigo.Replace("'", "\"") + "',Precio=" + Conversion.Str(Precio) + ",TipoProductoID = (select max(TipoProductoID) from TiposProductos where Descripcion like '" + text + "'),CantidadML=" + Conversion.Str(CantidadML) + ",CategoriaProduccionID  =" + VariableGeneral.DevolverValorLlaveForanea(CategoriaProduccionID) + ",Habilitado=" + VariableGeneral.armarBolean(Habilitado) + ",ConRecipienteLlevar=" + VariableGeneral.armarBolean(ConRecipienteLlevar) + ",tienePreparacion=" + VariableGeneral.armarBolean(tienePreparacion) + ",esCombo=" + VariableGeneral.armarBolean(esCombo) + ",esPorPeso=" + VariableGeneral.armarBolean(esPorPeso) + ",EscogePersonal=" + VariableGeneral.armarBolean(EscogePersonal) + ",Costo=" + Conversion.Str(Costo) + ",FechaModificacionPrecio=" + VariableGeneral.ArmarFecha(FechaModificacionPrecio) + ",CantidadPaquete=" + Conversions.ToString(CantidadPaquete) + ",DiasUtiles=" + Conversions.ToString(DiasUtiles) + ",CantidadMinima=" + Conversions.ToString(CantidadMinima) + ",Comision=" + Conversion.Str(Comision) + ",Presentacion='" + Presentacion + "',Grupo='" + Grupo + "',GrupoCantidad=" + Conversion.Str(GrupoCantidad) + ",Borrado=" + VariableGeneral.armarBolean(Borrado) + ",UnidadContenido='" + UnidadContenido + "',Orden=" + Conversion.Str(Orden) + ",CostoBruto=" + Conversion.Str(CostoBruto) + ",CodigoPY='" + CodigoPY + "',LinkFoto='" + LinkFoto + "',HabilitadoPY=" + VariableGeneral.armarBolean(HabilitadoPY) + ",Tiempo=" + Conversions.ToString(Tiempo) + ",ICE_Fijo=" + Conversion.Str(ICE_Fijo) + ",ICE_Porcentual=" + Conversion.Str(ICE_Porcentual) + ",ExtraParaLlevarID=" + Conversions.ToString(ExtraParaLlevarID) + ",ExtraEnMesaID=" + Conversions.ToString(ExtraEnMesaID) + ",CantidadMaxima=" + Conversion.Str(CantidadMaxima) + ",manejaSerie=" + VariableGeneral.armarBolean(manejaSerie) + ",manejaImei=" + VariableGeneral.armarBolean(manejaImei) + ",actividadSIN='" + actividadSIN1 + "',CodigoSIN='" + CodigoSIN1 + "',UnidadSIN=" + Conversions.ToString(UnidadSIN)) ?? "", "ID=" + ID);
			}
			else
			{
				BD.ConsultaModificar("Productos", ("Nombre='" + Nombre + "',Descripcion='" + Descripcion + "',codigo='" + Codigo.Replace("'", "\"") + "',Precio=" + Conversion.Str(Precio) + ",TipoProductoID  =" + VariableGeneral.DevolverValorLlaveForanea(TipoProductoID) + ",CantidadML=" + Conversion.Str(CantidadML) + ",CategoriaProduccionID  =" + VariableGeneral.DevolverValorLlaveForanea(CategoriaProduccionID) + ",Habilitado=" + VariableGeneral.armarBolean(Habilitado) + ",ConRecipienteLlevar=" + VariableGeneral.armarBolean(ConRecipienteLlevar) + ",tienePreparacion=" + VariableGeneral.armarBolean(tienePreparacion) + ",esCombo=" + VariableGeneral.armarBolean(esCombo) + ",esPorPeso=" + VariableGeneral.armarBolean(esPorPeso) + ",EscogePersonal=" + VariableGeneral.armarBolean(EscogePersonal) + ",Costo=" + Conversion.Str(Costo) + ",FechaModificacionPrecio=" + VariableGeneral.ArmarFecha(FechaModificacionPrecio) + ",CantidadPaquete=" + Conversions.ToString(CantidadPaquete) + ",DiasUtiles=" + Conversions.ToString(DiasUtiles) + ",CantidadMinima=" + Conversions.ToString(CantidadMinima) + ",Comision=" + Conversion.Str(Comision) + ",Presentacion='" + Presentacion + "',Grupo='" + Grupo + "',GrupoCantidad=" + Conversion.Str(GrupoCantidad) + ",Borrado=" + VariableGeneral.armarBolean(Borrado) + ",UnidadContenido='" + UnidadContenido + "',Orden=" + Conversion.Str(Orden) + ",CostoBruto=" + Conversion.Str(CostoBruto) + ",CodigoPY='" + CodigoPY + "',LinkFoto='" + LinkFoto + "',HabilitadoPY=" + VariableGeneral.armarBolean(HabilitadoPY) + ",Tiempo=" + Conversions.ToString(Tiempo) + ",ICE_Fijo=" + Conversion.Str(ICE_Fijo) + ",ICE_Porcentual=" + Conversion.Str(ICE_Porcentual) + ",ExtraParaLlevarID=" + Conversions.ToString(ExtraParaLlevarID) + ",ExtraEnMesaID=" + Conversions.ToString(ExtraEnMesaID) + ",CantidadMaxima=" + Conversion.Str(CantidadMaxima) + ",manejaSerie=" + VariableGeneral.armarBolean(manejaSerie) + ",manejaImei=" + VariableGeneral.armarBolean(manejaImei) + ",actividadSIN='" + actividadSIN1 + "',CodigoSIN='" + CodigoSIN1 + "',UnidadSIN=" + Conversions.ToString(UnidadSIN)) ?? "", "ID=" + ID);
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
			{
				BD.ConsultaModificar("Productos", ("Stock1=" + Conversion.Str(Stock)) ?? "", "ID=" + ID);
			}
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int Insert(BD_SQL bd1 = null)
	{
		checked
		{
			int result;
			try
			{
				if (bd1 != null)
				{
					string text = new clsTiposProductos().devolverTipoProductoPorID(TipoProductoID.Value);
					if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
					{
						string datos = string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(ID) + ",'" + Nombre + "','" + Descripcion + "',", Conversion.Str(Precio), ","), VariableGeneral.ArmarFecha(FechaModificacionPrecio), ",", Conversion.Str(Costo), ","), Conversion.Str(CantidadML), ","), VariableGeneral.armarBolean(tienePreparacion), ",", VariableGeneral.armarBolean(Habilitado), ",", VariableGeneral.armarBolean(ConRecipienteLlevar), ",(select max(TipoProductoID) from TiposProductos where Descripcion like '", text, "'),", VariableGeneral.armarBolean(esCombo), ",", VariableGeneral.armarBolean(esPorPeso), ",", VariableGeneral.DevolverValorLlaveForanea(CategoriaProduccionID), ",'", Codigo.Replace("'", "\""), "',", VariableGeneral.armarBolean(EscogePersonal), ", "), Conversions.ToString(CantidadPaquete), ","), Conversions.ToString(DiasUtiles), ","), Conversions.ToString(CantidadMinima), ","), Conversion.Str(Comision), ",'", Presentacion, "','", Grupo, "',"), Conversion.Str(GrupoCantidad), ","), VariableGeneral.armarBolean(Borrado), ",'", UnidadContenido, "',"), Conversion.Str(Orden), ","), Conversion.Str(CostoBruto), ","), Conversion.Str(CantidadMaxima), ",'", CodigoPY, "',", VariableGeneral.armarBolean(HabilitadoPY), ",'", LinkFoto, "',", Conversions.ToString(Tiempo), ",", Conversion.Str(ICE_Fijo), " ,", Conversion.Str(ICE_Porcentual), ",", Conversions.ToString(ExtraParaLlevarID), ",", Conversions.ToString(ExtraEnMesaID), ",'", CodigoSIN1, "',", Conversions.ToString(UnidadSIN), ",'", actividadSIN1, "',", VariableGeneral.armarBolean(manejaSerie), ",", VariableGeneral.armarBolean(manejaImei)) ?? "";
						int Id = 0;
						bd1.ConsultaInsertar(datos, "Productos(id,Nombre,Descripcion,Precio,FechaModificacionPrecio,Costo ,CantidadML ,TienePreparacion,Habilitado,ConRecipienteLlevar,TipoProductoID,esCombo,esPorPeso,CategoriaProduccionID,Codigo,EscogePersonal,CantidadPaquete,DiasUtiles,CantidadMinima,Comision,Presentacion, Grupo, GrupoCantidad,Borrado,UnidadContenido,Orden,CostoBruto,CantidadMaxima,CodigoPY,HabilitadoPY,LinkFoto,Tiempo,ICE_Fijo,ICE_Porcentual,ExtraParaLlevarID,ExtraEnMesaID,CodigoSIN,UnidadSIN,actividadSIN,manejaSerie,manejaImei)", ref Id);
					}
					else
					{
						bd1.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat("'" + Nombre + "','" + Descripcion + "',", Conversion.Str(Precio), ","), VariableGeneral.ArmarFechaSQL(FechaModificacionPrecio), ",", Conversion.Str(Costo), ","), Conversion.Str(CantidadML), ","), VariableGeneral.armarBolean(tienePreparacion), ",", VariableGeneral.armarBolean(Habilitado), ",", VariableGeneral.armarBolean(ConRecipienteLlevar), ",(select max(TipoProductoID) from TiposProductos where Descripcion like '", text, "'),", VariableGeneral.armarBolean(esCombo), ",", VariableGeneral.armarBolean(esPorPeso), ",", VariableGeneral.DevolverValorLlaveForanea(CategoriaProduccionID), ",'", Codigo.Replace("'", "\""), "',", VariableGeneral.armarBolean(EscogePersonal), ", "), Conversions.ToString(CantidadPaquete), ","), Conversions.ToString(DiasUtiles), ","), Conversions.ToString(CantidadMinima), ","), Conversion.Str(Comision), ",'", Presentacion, "','", Grupo, "',"), Conversion.Str(GrupoCantidad), ","), VariableGeneral.armarBolean(Borrado), ",'", UnidadContenido, "',"), Conversion.Str(Orden), ","), Conversion.Str(CostoBruto), ","), Conversion.Str(CantidadMaxima), ",'", CodigoPY, "',", VariableGeneral.armarBolean(HabilitadoPY), ",'", LinkFoto, "',", Conversions.ToString(Tiempo), ",", Conversion.Str(ICE_Fijo), " ,", Conversion.Str(ICE_Porcentual), ",", Conversions.ToString(ExtraParaLlevarID), ",", Conversions.ToString(ExtraEnMesaID), ",'", CodigoSIN1, "',", Conversions.ToString(UnidadSIN), ",'", actividadSIN1, "',", VariableGeneral.armarBolean(manejaSerie), ",", VariableGeneral.armarBolean(manejaImei)) ?? "", "Productos(Nombre,Descripcion,Precio,FechaModificacionPrecio,Costo ,CantidadML ,TienePreparacion,Habilitado,ConRecipienteLlevar,TipoProductoID,esCombo,esPorPeso,CategoriaProduccionID,Codigo,EscogePersonal,CantidadPaquete,DiasUtiles,CantidadMinima,Comision,Presentacion,Grupo,GrupoCantidad,Borrado,UnidadContenido,Orden,CostoBruto,CantidadMaxima,CodigoPY,HabilitadoPY,LinkFoto,Tiempo,ICE_Fijo,ICE_Porcentual,ExtraParaLlevarID,ExtraEnMesaID,CodigoSIN,UnidadSIN,actividadSIN,manejaSerie,manejaImei)", ref ID);
					}
					if ((Operators.CompareString(CodigoPY, "0", TextCompare: false) == 0) | (Operators.CompareString(CodigoPY, "", TextCompare: false) == 0))
					{
						CodigoPY = Conversions.ToString(ID);
						bd1.ConsultaModificar("Productos", "CodigoPY='" + CodigoPY + "'", "id=" + Conversions.ToString(ID));
					}
					LimpiarStocksXXXNulos(bd1);
					result = ID;
				}
				else
				{
					if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
					{
						ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ID)", "Productos").Rows[0][0]), 0));
						ID++;
						BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(ID) + ",'" + Nombre + "','" + Descripcion + "',", Conversion.Str(Precio), ","), VariableGeneral.ArmarFecha(FechaModificacionPrecio), ",", Conversion.Str(Costo), ","), Conversion.Str(CantidadML), ","), VariableGeneral.armarBolean(tienePreparacion), ",", VariableGeneral.armarBolean(Habilitado), ",", VariableGeneral.armarBolean(ConRecipienteLlevar), ",", VariableGeneral.DevolverValorLlaveForanea(TipoProductoID), ",", VariableGeneral.armarBolean(esCombo), ",", VariableGeneral.armarBolean(esPorPeso), ",", VariableGeneral.DevolverValorLlaveForanea(CategoriaProduccionID), ",'", Codigo.Replace("'", "\""), "',", VariableGeneral.armarBolean(EscogePersonal), ", "), Conversions.ToString(CantidadPaquete), ","), Conversions.ToString(DiasUtiles), ","), Conversions.ToString(CantidadMinima), ","), Conversion.Str(Comision), ",'", Presentacion, "','", Grupo, "',"), Conversion.Str(GrupoCantidad), ","), VariableGeneral.armarBolean(Borrado), ",'", UnidadContenido, "',"), Conversion.Str(Orden), ","), Conversion.Str(CostoBruto), ","), Conversion.Str(CantidadMaxima), ",'", CodigoPY, "',", VariableGeneral.armarBolean(HabilitadoPY), ",'", LinkFoto, "',", Conversions.ToString(Tiempo), ",", Conversion.Str(ICE_Fijo), " ,", Conversion.Str(ICE_Porcentual), ",", Conversions.ToString(ExtraParaLlevarID), ",", Conversions.ToString(ExtraEnMesaID), ",'", CodigoSIN1, "',", Conversions.ToString(UnidadSIN), ",'", actividadSIN1, "',", VariableGeneral.armarBolean(manejaSerie), ",", VariableGeneral.armarBolean(manejaImei)) ?? "", "Productos(id,Nombre,Descripcion,Precio,FechaModificacionPrecio,Costo ,CantidadML ,TienePreparacion,Habilitado,ConRecipienteLlevar,TipoProductoID,esCombo,esPorPeso,CategoriaProduccionID,Codigo,EscogePersonal,CantidadPaquete,DiasUtiles,CantidadMinima,Comision,Presentacion, Grupo, GrupoCantidad,Borrado,UnidadContenido,Orden,CostoBruto,CantidadMaxima,CodigoPY,HabilitadoPY,LinkFoto,Tiempo,ICE_Fijo,ICE_Porcentual,ExtraParaLlevarID,ExtraEnMesaID,CodigoSIN,UnidadSIN,actividadSIN,manejaSerie,manejaImei)");
					}
					else
					{
						BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat("'" + Nombre + "','" + Descripcion + "',", Conversion.Str(Precio), ","), VariableGeneral.ArmarFecha(FechaModificacionPrecio), ",", Conversion.Str(Costo), ","), Conversion.Str(CantidadML), ","), VariableGeneral.armarBolean(tienePreparacion), ",", VariableGeneral.armarBolean(Habilitado), ",", VariableGeneral.armarBolean(ConRecipienteLlevar), ",", VariableGeneral.DevolverValorLlaveForanea(TipoProductoID), ",", VariableGeneral.armarBolean(esCombo), ",", VariableGeneral.armarBolean(esPorPeso), ",", VariableGeneral.DevolverValorLlaveForanea(CategoriaProduccionID), ",'", Codigo.Replace("'", "\""), "',", VariableGeneral.armarBolean(EscogePersonal), ", "), Conversions.ToString(CantidadPaquete), ","), Conversions.ToString(DiasUtiles), ","), Conversions.ToString(CantidadMinima), ","), Conversion.Str(Comision), ",'", Presentacion, "','", Grupo, "',"), Conversion.Str(GrupoCantidad), ","), VariableGeneral.armarBolean(Borrado), ",'", UnidadContenido, "',"), Conversion.Str(Orden), ","), Conversion.Str(CostoBruto), ","), Conversion.Str(CantidadMaxima), ",'", CodigoPY, "',", VariableGeneral.armarBolean(HabilitadoPY), ",'", LinkFoto, "',", Conversions.ToString(Tiempo), ",", Conversion.Str(ICE_Fijo), " ,", Conversion.Str(ICE_Porcentual), ",", Conversions.ToString(ExtraParaLlevarID), ",", Conversions.ToString(ExtraEnMesaID), ",'", CodigoSIN1, "',", Conversions.ToString(UnidadSIN), ",'", actividadSIN1, "',", VariableGeneral.armarBolean(manejaSerie), ",", VariableGeneral.armarBolean(manejaImei)) ?? "", "Productos(Nombre,Descripcion,Precio,FechaModificacionPrecio,Costo ,CantidadML ,TienePreparacion,Habilitado,ConRecipienteLlevar,TipoProductoID,esCombo,esPorPeso,CategoriaProduccionID,Codigo,EscogePersonal,CantidadPaquete,DiasUtiles,CantidadMinima,Comision,Presentacion, Grupo, GrupoCantidad,Borrado,UnidadContenido,Orden,CostoBruto,CantidadMaxima,CodigoPY,HabilitadoPY,LinkFoto,Tiempo,ICE_Fijo,ICE_Porcentual,ExtraParaLlevarID,ExtraEnMesaID,CodigoSIN,UnidadSIN,actividadSIN,manejaSerie,manejaImei)", ref ID);
					}
					if ((Operators.CompareString(CodigoPY, "0", TextCompare: false) == 0) | (Operators.CompareString(CodigoPY, "", TextCompare: false) == 0))
					{
						CodigoPY = Conversions.ToString(ID);
						BD.ConsultaModificar("Productos", "CodigoPY='" + CodigoPY + "'", "id=" + Conversions.ToString(ID));
					}
					if ((Operators.CompareString(Codigo, "0", TextCompare: false) == 0) | (Operators.CompareString(Codigo, "", TextCompare: false) == 0))
					{
						Codigo = Conversions.ToString(ID);
						BD.ConsultaModificar("Productos", "Codigo='" + Codigo + "'", "id=" + Conversions.ToString(ID));
					}
					LimpiarStocksXXXNulos();
					result = ID;
				}
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				result = 0;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public int Delete()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Productos", "ID = " + ID) == 0)
			{
				Interaction.MsgBox("Can't delete , is in use");
				result = 0;
			}
			else
			{
				result = 1;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public void DeleteLogico()
	{
		DataTable dataTable = new ctlAlmacenes().DevolverTodosAlmacenesInternos(-1);
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				BD.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("stock", dataTable.Rows[i][0]), " =0 ")), "ID=" + Conversions.ToString(ID));
			}
			BD.ConsultaModificar("Productos", "codigo = Codigo + 'Xx', codigoPY= codigoPY + 'Xx',Habilitado= " + VariableGeneral.armarBolean(0) + ", borrado=" + VariableGeneral.armarBolean(1) + ", escombo=" + VariableGeneral.armarBolean(1) + ", tienePreparacion=" + VariableGeneral.armarBolean(1), "ID=" + Conversions.ToString(ID));
		}
	}

	public int DevolverTipoProductoXProducto(int idProducto)
	{
		return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("Productos.TipoProductoID", "Productos", "ID=" + Conversions.ToString(idProducto)).Rows[0][0]), 0));
	}

	public string devolverStocksXXX()
	{
		string text = "";
		DataTable dataTable = new ctlAlmacenes().DevolverTodosAlmacenesInternos(-1);
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				text = Conversions.ToString(Operators.ConcatenateObject(text, Operators.ConcatenateObject(Operators.ConcatenateObject("stock", dataTable.Rows[i][0]), " + ")));
			}
			if (text.Length > 0)
			{
				text = text.Substring(0, text.Length - 3);
			}
			return text;
		}
	}

	public void LimpiarStocksXXXNulos(BD_SQL bd1 = null)
	{
		checked
		{
			if (bd1 != null)
			{
				DataTable dataTable = new ctlAlmacenes().DevolverTodosAlmacenesInternos(-1, bd1);
				int num = dataTable.Rows.Count - 1;
				for (int i = 0; i <= num; i++)
				{
					bd1.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("stock", dataTable.Rows[i][0]), " =0 ")), Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("stock", dataTable.Rows[i][0]), " is null")));
				}
			}
			else
			{
				DataTable dataTable2 = new ctlAlmacenes().DevolverTodosAlmacenesInternos(-1);
				int num2 = dataTable2.Rows.Count - 1;
				for (int j = 0; j <= num2; j++)
				{
					BD.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("stock", dataTable2.Rows[j][0]), " =0 ")), Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("stock", dataTable2.Rows[j][0]), " is null")));
				}
			}
		}
	}

	public DataTable DevolverStockMinimo(bool todo)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			if (todo)
			{
				return BD.ConsultaVer("TiposProductos.Descripcion As TiposProductos,Productos.Codigo, Productos.Nombre,Presentacion,Productos.Costo, round((" + devolverStocksXXX() + "),3) as Stock, CantidadMinima, CantidadMaxima,iif(CantidadMaxima>0,  (CantidadMaxima-iif( (" + devolverStocksXXX() + ")<=0,0,(" + devolverStocksXXX() + ") ) ),0) as DiffStockVsMax", " (Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID)", " Borrado=" + VariableGeneral.armarBolean(0), "TiposProductos.Descripcion,Productos.Nombre");
			}
			return BD.ConsultaVer("TiposProductos.Descripcion As TiposProductos,Productos.Codigo, Productos.Nombre,Presentacion,Productos.Costo,round((" + devolverStocksXXX() + "),3) as Stock, CantidadMinima, CantidadMaxima, iif(CantidadMaxima>0, (CantidadMaxima-iif( (" + devolverStocksXXX() + ")<=0,0,(" + devolverStocksXXX() + ") ) ),0) as DiffStockVsMax", " (Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID)", " Borrado=" + VariableGeneral.armarBolean(0) + " and (Productos.CantidadMinima >= (" + devolverStocksXXX() + ")) and (Productos.CantidadMinima<>0) ", "TiposProductos.Descripcion,Productos.Nombre");
		}
		if (todo)
		{
			return BD.ConsultaVer("TiposProductos.Descripcion As TiposProductos,Productos.Codigo, Productos.Nombre,Presentacion,Productos.Costo, round((" + devolverStocksXXX() + "),3) as Stock, CantidadMinima, CantidadMaxima, CASE WHEN CantidadMaxima>0 then  (CantidadMaxima- CASE WHEN (" + devolverStocksXXX() + ")<=0 THEN 0 ELSE (" + devolverStocksXXX() + ") END ) ELSE 0 END as DiffStockVsMax", "(Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID)", " Borrado=" + VariableGeneral.armarBolean(0), "TiposProductos.Descripcion,Productos.Nombre");
		}
		return BD.ConsultaVer("TiposProductos.Descripcion As TiposProductos,Productos.Codigo, Productos.Nombre,Presentacion,Productos.Costo, round((" + devolverStocksXXX() + "),3) as Stock, CantidadMinima, CantidadMaxima, CASE WHEN CantidadMaxima>0 then (CantidadMaxima-CASE WHEN (" + devolverStocksXXX() + ")<=0 THEN 0 ELSE (" + devolverStocksXXX() + ") END) ELSE 0 END as DiffStockVsMax", " (Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID)", " Borrado=" + VariableGeneral.armarBolean(0) + " and (Productos.CantidadMinima >= (" + devolverStocksXXX() + ")) and (Productos.CantidadMinima<>0) ", "TiposProductos.Descripcion,Productos.Nombre");
	}

	public DataTable DevolverInventarioValorizado(int almacenID, bool prodDiferenteCero)
	{
		string text = "";
		if (almacenID == 0)
		{
			if (prodDiferenteCero)
			{
				text = " and " + devolverStocksXXX() + "<>0 ";
			}
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Productos.ID, Productos.Codigo,familias.Descripcion as Familia,TiposProductos.Descripcion As Categoria,Productos.Nombre,(" + devolverStocksXXX() + ") as Stock,Productos.Presentacion,Productos.Costo,iif( (" + devolverStocksXXX() + ")<=0,0,(" + devolverStocksXXX() + ")*Productos.Costo ) as CostoTotal, Productos.Precio as PrecioVenta, iif( (" + devolverStocksXXX() + ")<=0,0,(" + devolverStocksXXX() + ")*Productos.Precio ) as VentaTotal ", " ((Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join Familias on TiposProductos.FamiliaId =Familias.FamiliaID )", "Borrado=" + VariableGeneral.armarBolean(0) + " and  Productos.ID>3 and Escombo=" + VariableGeneral.armarBolean(0) + " and TienePreparacion=" + VariableGeneral.armarBolean(0) + " " + text, "Productos.Nombre");
			}
			return BD.ConsultaVer("Productos.ID, Productos.Codigo,familias.Descripcion as Familia,TiposProductos.Descripcion As Categoria,Productos.Nombre,(" + devolverStocksXXX() + ") as Stock,Productos.Presentacion,Productos.Costo,CASE WHEN (" + devolverStocksXXX() + ")<=0 THEN 0 ELSE (" + devolverStocksXXX() + ")*Productos.Costo END as CostoTotal, Productos.Precio as PrecioVenta, CASE WHEN (" + devolverStocksXXX() + ")<=0 THEN 0 ELSE (" + devolverStocksXXX() + ")*Productos.Precio END as VentaTotal ", " ((Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join Familias on TiposProductos.FamiliaId =Familias.FamiliaID )", "Borrado=" + VariableGeneral.armarBolean(0) + " and  Productos.ID>3 and Escombo=" + VariableGeneral.armarBolean(0) + " and TienePreparacion=" + VariableGeneral.armarBolean(0) + " " + text, "Productos.Nombre");
		}
		if (prodDiferenteCero)
		{
			text = " and Stock" + Conversions.ToString(almacenID) + "<>0 ";
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Productos.ID, Productos.Codigo,familias.Descripcion as Familia,TiposProductos.Descripcion As Categoria,Productos.Nombre,(Stock" + Conversions.ToString(almacenID) + ") as Stock,Productos.Presentacion,Productos.Costo,round(iif( (Stock" + Conversions.ToString(almacenID) + ")<=0,0,(Stock" + Conversions.ToString(almacenID) + ")*Productos.Costo ),2) as CostoTotal, Productos.Precio as PrecioVenta,round(iif( (Stock" + Conversions.ToString(almacenID) + ")<=0,0,(Stock" + Conversions.ToString(almacenID) + ")*Productos.Precio ),2) as VentaTotal", " ((Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join Familias on TiposProductos.FamiliaId =Familias.FamiliaID )", "Borrado=" + VariableGeneral.armarBolean(0) + " and  Productos.ID>3 and Escombo=" + VariableGeneral.armarBolean(0) + " and TienePreparacion=" + VariableGeneral.armarBolean(0) + " " + text, "Productos.Nombre");
		}
		return BD.ConsultaVer("Productos.ID, Productos.Codigo,familias.Descripcion as Familia,TiposProductos.Descripcion As Categoria,Productos.Nombre,(Stock" + Conversions.ToString(almacenID) + ") as Stock,Productos.Presentacion,Productos.Costo,round(CASE WHEN (Stock" + Conversions.ToString(almacenID) + ")<=0 THEN 0 ELSE (Stock" + Conversions.ToString(almacenID) + ")*Productos.Costo END,2) as CostoTotal, Productos.Precio as PrecioVenta ,round(CASE WHEN (Stock" + Conversions.ToString(almacenID) + ")<=0 THEN 0 ELSE (Stock" + Conversions.ToString(almacenID) + ")*Productos.Precio END,2) as VentaTotal ", " ((Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join Familias on TiposProductos.FamiliaId =Familias.FamiliaID )", "Borrado=" + VariableGeneral.armarBolean(0) + " and  Productos.ID>3  and Escombo=" + VariableGeneral.armarBolean(0) + " and TienePreparacion=" + VariableGeneral.armarBolean(0) + " " + text, "Productos.Nombre");
	}

	public DataTable DevolverInventarioValorizadoAlmacenesSeparados(bool prodDiferenteCero)
	{
		string restriction = "";
		if (prodDiferenteCero)
		{
			restriction = " subquery.Stock <> 0 ";
		}
		string text = "(";
		DataTable dataTable = new ctlAlmacenes().DevolverTodosAlmacenesInternos(-1);
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				if (i > 0)
				{
					text += "\rUNION ALL \r";
				}
				text = Conversions.ToString(Operators.ConcatenateObject(text, Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("SELECT\rProductos.ID, Productos.Codigo, Productos.Nombre, Productos.TipoProductoID, Productos.Presentacion, Productos.Costo, Productos.Precio,\r\n\t\tCASE WHEN stock", dataTable.Rows[i][0]), "<=0 THEN 0 ELSE stock"), dataTable.Rows[i][0]), "*Productos.Costo END as CostoTotal,\r\n\t\tCASE WHEN stock"), dataTable.Rows[i][0]), "<=0 THEN 0 ELSE stock"), dataTable.Rows[i][0]), "*Productos.Precio END as VentaTotal,\r\n\t\tAlmacenes.nombre as Almacen,\r\n        Stock"), dataTable.Rows[i][0]), " AS Stock FROM productos\r\n\t\tleft join Almacenes on AlmacenID = "), dataTable.Rows[i][0]), " where Productos.Borrado ="), VariableGeneral.armarBolean(0)), " and Productos.ID >3 and Escombo="), VariableGeneral.armarBolean(0)), " and TienePreparacion="), VariableGeneral.armarBolean(0))));
			}
			text += ") AS subquery\rleft join TiposProductos On subquery.TipoProductoID = TiposProductos.TipoProductoID\r\n        left join Familias on TiposProductos.FamiliaId =Familias.FamiliaID";
			return BD.ConsultaVer("subquery.ID, subquery.Codigo, familias.Descripcion as Familia,TiposProductos.Descripcion As Categoria, subquery.Nombre, subquery.Stock, subquery.Presentacion, subquery.Costo, subquery.CostoTotal, subquery.Precio as PrecioVenta, subquery.VentaTotal, subquery.Almacen", text, restriction, "subquery.Nombre, subquery.Almacen");
		}
	}

	public DataTable DevolverInventarioValorizadoXAjuste(int almacenID, int ajusteID)
	{
		if (almacenID == 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Productos.ID, Productos.Codigo,familias.Descripcion as Familia,TiposProductos.Descripcion As TiposProductos,Productos.Nombre,(" + devolverStocksXXX() + ") as Stock,Productos.Presentacion,Productos.Costo,iif( (" + devolverStocksXXX() + ")<=0,0,(" + devolverStocksXXX() + ")*Productos.Costo ) as Total, Productos.Precio as PrecioVenta", " ((Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join Familias on TiposProductos.FamiliaId =Familias.FamiliaID )", "Borrado=" + VariableGeneral.armarBolean(0) + " and  Productos.ID>3 and Escombo=" + VariableGeneral.armarBolean(0) + " and TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.ID in (select productoID from  DetallesAjustes where ajusteID= " + Conversions.ToString(ajusteID) + " )", "Productos.Nombre");
			}
			return BD.ConsultaVer("Productos.ID, Productos.Codigo,familias.Descripcion as Familia,TiposProductos.Descripcion As TiposProductos,Productos.Nombre,(" + devolverStocksXXX() + ") as Stock,Productos.Presentacion,Productos.Costo,CASE WHEN (" + devolverStocksXXX() + ")<=0 THEN 0 ELSE (" + devolverStocksXXX() + ")*Productos.Costo END as Total, Productos.Precio as PrecioVenta ", " ((Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join Familias on TiposProductos.FamiliaId =Familias.FamiliaID )", "Borrado=" + VariableGeneral.armarBolean(0) + " and  Productos.ID>3 and Escombo=" + VariableGeneral.armarBolean(0) + " and TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.ID in (select productoID from  DetallesAjustes where ajusteID=" + Conversions.ToString(ajusteID) + " )", "Productos.Nombre");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Productos.ID, Productos.Codigo,familias.Descripcion as Familia,TiposProductos.Descripcion As TiposProductos,Productos.Nombre,(Stock" + Conversions.ToString(almacenID) + ") as Stock,Productos.Presentacion,Productos.Costo,round(iif( (Stock" + Conversions.ToString(almacenID) + ")<=0,0,(Stock" + Conversions.ToString(almacenID) + ")*Productos.Costo ),2) as Total, Productos.Precio as PrecioVenta", " ((Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join Familias on TiposProductos.FamiliaId =Familias.FamiliaID )", "Borrado=" + VariableGeneral.armarBolean(0) + " and  Productos.ID>3 and Escombo=" + VariableGeneral.armarBolean(0) + " and TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.ID in (select productoID from  DetallesAjustes where ajusteID=" + Conversions.ToString(ajusteID) + " )", "Productos.Nombre");
		}
		return BD.ConsultaVer("Productos.ID, Productos.Codigo,familias.Descripcion as Familia,TiposProductos.Descripcion As TiposProductos,Productos.Nombre,(Stock" + Conversions.ToString(almacenID) + ") as Stock,Productos.Presentacion,Productos.Costo,round(CASE WHEN (Stock" + Conversions.ToString(almacenID) + ")<=0 THEN 0 ELSE (Stock" + Conversions.ToString(almacenID) + ")*Productos.Costo END,2) as Total, Productos.Precio as PrecioVenta ", " ((Productos LEFT JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join Familias on TiposProductos.FamiliaId =Familias.FamiliaID )", "Borrado=" + VariableGeneral.armarBolean(0) + " and  Productos.ID>3  and Escombo=" + VariableGeneral.armarBolean(0) + " and TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.ID in (select productoID from  DetallesAjustes where ajusteID=" + Conversions.ToString(ajusteID) + " )", "Productos.Nombre");
	}

	public DataTable BuscarProductos(string CodigoProd, string nombreProd, bool desdeVentas, bool SinPreparacion, int AlmacenId)
	{
		string text = "";
		if (CodigoProd.Length > 0)
		{
			text = text + "Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.Codigo LIKE '%" + CodigoProd.ToString().Replace("'", "\"") + "%'";
		}
		if (nombreProd.Length > 0)
		{
			if (text.Length > 0)
			{
				text += " and ";
			}
			text = text + "Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.Nombre LIKE '%" + nombreProd.Replace("'", "\"") + "%'";
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.CasaCuina)
			{
				text = text + " or Productos.Descripcion LIKE '%" + nombreProd.Replace("'", "\"") + "%'";
			}
		}
		if (text.Length == 0)
		{
			text += " 1=1 ";
		}
		string text2 = "";
		text2 = ((AlmacenId != 0) ? ("Stock" + Conversions.ToString(AlmacenId)) : devolverStocksXXX());
		if (desdeVentas)
		{
			return BD.ConsultaVer(" Productos.ID ,Productos.TipoProductoID as Tipo,Productos.Codigo,Productos.Nombre,round(" + text2 + ",2) as Stock, Productos.Presentacion, Productos.Precio, Productos.Costo, TiposProductos.Descripcion as Categoria", "Productos left join TiposProductos on Productos.TipoProductoID =TiposProductos.TipoProductoID  ", (text + " and Habilitado=" + VariableGeneral.armarBolean(aux: true)) ?? "", "Nombre");
		}
		if (SinPreparacion)
		{
			return BD.ConsultaVer(" Productos.ID ,Productos.TipoProductoID as Tipo,Productos.Codigo,Productos.Nombre,round(" + text2 + ",2) as Stock, Productos.Presentacion, Productos.Precio , Productos.Costo, TiposProductos.Descripcion as Categoria", "Productos left join TiposProductos on Productos.TipoProductoID =TiposProductos.TipoProductoID  ", text + " and tienePreparacion=" + VariableGeneral.armarBolean(aux: false) + " and esCombo=" + VariableGeneral.armarBolean(aux: false), "Nombre");
		}
		return BD.ConsultaVer(" Productos.ID ,Productos.TipoProductoID as Tipo,Productos.Codigo,Productos.Nombre,round(" + text2 + ",2) as Stock, Productos.Presentacion, Productos.Precio , Productos.Costo, TiposProductos.Descripcion as Categoria", "Productos left join TiposProductos on Productos.TipoProductoID =TiposProductos.TipoProductoID  ", text, "Nombre");
	}

	public bool ExisteProductoNombre()
	{
		DataTable dataTable = BD.ConsultaVer("ID", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and  Nombre like '" + Nombre.Replace("'", "\"") + "' ");
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"])) ? ((object)0) : dataTable.Rows[0]["ID"]);
			return true;
		}
		ID = 0;
		return false;
	}

	public bool ExisteCodigo()
	{
		if (BD.ConsultaVer("ID", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and  codigo = '" + Codigo.Replace("'", "\"") + "' and ID <> " + Conversions.ToString(ID)).Rows.Count > 0)
		{
			return true;
		}
		return false;
	}

	public bool ModificarCodigo()
	{
		bool result;
		try
		{
			BD.ConsultaModificar("Productos", "Codigo='" + Codigo.Replace("'", "\"") + "'", "ID=" + ID);
			result = true;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool modificarPrecioUnidad()
	{
		bool result;
		try
		{
			BD.ConsultaModificar("Productos", ("Precio=" + Conversion.Str(Precio) + " ,actividadSIN='" + actividadSIN1 + "',CodigoSIN='" + CodigoSIN1 + "',UnidadSIN=" + Conversions.ToString(UnidadSIN)) ?? "", "ID=" + ID);
			BD.ConsultaModificar("Productos", "Codigo=ID", "(Codigo is null or Codigo='') and ID=" + ID);
			result = true;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool modificarPrecio()
	{
		bool result;
		try
		{
			BD.ConsultaModificar("Productos", "Precio=" + Conversion.Str(Precio) + " , FechaModificacionPrecio=" + VariableGeneral.ArmarFecha(DateAndTime.Now), "ID=" + ID);
			result = true;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public DataTable ToReturnProductosTodos(int yoID)
	{
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and ID<>" + Conversions.ToString(yoID), "Nombre");
	}

	public int BuscarProductosXCodigo()
	{
		DataTable dataTable = BD.ConsultaVer("Productos.ID", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Codigo='" + Codigo + "'");
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(dataTable.Rows[0][0]);
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		ID = 0;
		return 0;
	}

	public DataTable ToReturnProductosPaquetes()
	{
		return BD.ConsultaVer("Productos.ID,Productos.Nombre", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and CantidadPaquete>1", "Nombre");
	}

	public DataTable ToReturnCodigos()
	{
		return BD.ConsultaVer("ROW_NUMBER() over (order by Codigo asc), codigo", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " and Habilitado= 1 and ID>3 and ISNUMERIC(Codigo)<>1", "", "Codigo");
	}

	public void DevolverPrecioUnit(ref double precio1)
	{
		DataTable dataTable = BD.ConsultaVer("Precio", "Productos", " ID=" + ID);
		if (dataTable.Rows.Count > 0)
		{
			precio1 = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Precio"])) ? "" : dataTable.Rows[0]["Precio"]);
		}
	}

	public DataTable DevolverPresentaciones()
	{
		return BD.ConsultaVer("distinct 0,  Presentacion", "Productos", "Borrado=" + VariableGeneral.armarBolean(0) + " ");
	}

	public DataTable DevolverUnidadContenido()
	{
		return BD.ConsultaVer("distinct 0,  UnidadContenido", "Productos", ("Borrado=" + VariableGeneral.armarBolean(0)) ?? "");
	}

	public DataTable DevolverGrupo()
	{
		return BD.ConsultaVer("distinct 0,  Grupo", "Productos", ("Borrado=" + VariableGeneral.armarBolean(0)) ?? "");
	}

	public string DevolverNombreXcodigo()
	{
		DataTable dataTable = BD.ConsultaVer("Nombre", "Productos", "codigo='" + Codigo + "'");
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToString(dataTable.Rows[0][0]);
		}
		return "";
	}

	public string DevolverCodigoXID()
	{
		DataTable dataTable = BD.ConsultaVer("Productos.Codigo", "Productos", "ID='" + Conversions.ToString(ID) + "'");
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToString(dataTable.Rows[0][0]);
		}
		return Conversions.ToString(0);
	}

	public DataTable EsProductoPorPeso()
	{
		return BD.ConsultaVer("esPorPeso", "Productos ", ("Productos.ID = " + Conversions.ToString(ID)) ?? "");
	}
}
