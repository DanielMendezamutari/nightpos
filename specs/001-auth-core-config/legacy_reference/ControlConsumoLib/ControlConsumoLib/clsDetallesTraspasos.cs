using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDetallesTraspasos
{
	private int DetalleTraspasoID;

	private double Cantidad;

	private double CantidadFinal;

	private string Observacion;

	private int TraspasoID;

	private int ProductoID;

	private double Costos;

	public int _DetalleTraspasoID
	{
		get
		{
			return DetalleTraspasoID;
		}
		set
		{
			DetalleTraspasoID = value;
		}
	}

	public double _Cantidad
	{
		get
		{
			return Cantidad;
		}
		set
		{
			Cantidad = value;
		}
	}

	public double _CantidadFinal
	{
		get
		{
			return CantidadFinal;
		}
		set
		{
			CantidadFinal = value;
		}
	}

	public string _Observacion
	{
		get
		{
			return Observacion;
		}
		set
		{
			Observacion = value;
		}
	}

	public int _TraspasoID
	{
		get
		{
			return TraspasoID;
		}
		set
		{
			TraspasoID = value;
		}
	}

	public int _ProductoID
	{
		get
		{
			return ProductoID;
		}
		set
		{
			ProductoID = value;
		}
	}

	public double _Costos
	{
		get
		{
			return Costos;
		}
		set
		{
			Costos = value;
		}
	}

	public clsDetallesTraspasos()
	{
		Cantidad = 0.0;
		Observacion = "";
		TraspasoID = 0;
		ProductoID = 0;
		CantidadFinal = 0.0;
		Costos = 0.0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "DetallesTraspasos", " DetalleTraspasoID=" + DetalleTraspasoID);
		if (dataTable.Rows.Count > 0)
		{
			DetalleTraspasoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DetalleTraspasoID"])) ? ((object)0) : dataTable.Rows[0]["DetalleTraspasoID"]);
			Cantidad = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Cantidad"])) ? ((object)0) : dataTable.Rows[0]["Cantidad"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			TraspasoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TraspasoID"])) ? ((object)0) : dataTable.Rows[0]["TraspasoID"]);
			ProductoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ProductoID"])) ? ((object)0) : dataTable.Rows[0]["ProductoID"]);
			Costos = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Costos"])) ? ((object)0) : dataTable.Rows[0]["Costos"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("DetallesTraspasos.DetalleTraspasoID,DetallesTraspasos.Cantidad,DetallesTraspasos.Observacion,DetallesTraspasos.TraspasoID,DetallesTraspasos.ProductoID,DetallesTraspasos.Costos", "DetallesTraspasos");
	}

	public DataTable DevolverXTraspasoID(int almacenId)
	{
		if (TraspasoID == 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Productos.ID, Productos.Nombre,  iif(Stock" + Conversions.ToString(almacenId) + " is null,0,Stock" + Conversions.ToString(almacenId) + ") as enStock,  productos.Presentacion, iif(tab1.Cantidad is null,0,tab1.Cantidad) as CantidadAjustada, tab1.Observacion, tab1.CantidadFinal, TiposProductos.Descripcion as Tipos,Productos.Costo, Productos.Habilitado,  Productos.CantidadML , productos.UnidadContenido, Productos.Codigo", "\t(Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID ) left join (  SELECT DetallesTraspasos.ProductoID, DetallesTraspasos.Cantidad, DetallesTraspasos.CantidadFinal, DetallesTraspasos.Observacion\t \tFROM DetallesTraspasos \tWHERE  DetallesTraspasos.TraspasoID= " + Conversions.ToString(TraspasoID) + "\t ) as tab1 on tab1.ProductoID=Productos.ID ", ("Productos.Borrado= " + VariableGeneral.armarBolean(0) + " and Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.esCombo=" + VariableGeneral.armarBolean(0)) ?? "", "TiposProductos.Descripcion , Productos.Nombre");
			}
			return BD.ConsultaVer("Productos.ID, Productos.Nombre,  CASE WHEN  Productos.Stock" + Conversions.ToString(almacenId) + " is null  THEN 0 ELSE  Stock" + Conversions.ToString(almacenId) + "  END  as enStock,  productos.Presentacion, CASE WHEN tab1.Cantidad is null  THEN  0 ELSE  Cantidad  END  as CantidadAjustada, tab1.Observacion, tab1.CantidadFinal, TiposProductos.Descripcion as Tipos,Productos.Costo,  0 as CostoTotal, Productos.Habilitado,  Productos.CantidadML , productos.UnidadContenido, Productos.Codigo", "  (Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID ) left join (  SELECT DetallesTraspasos.ProductoID, DetallesTraspasos.Cantidad, DetallesTraspasos.CantidadFinal, DetallesTraspasos.Observacion\t   FROM DetallesTraspasos \tWHERE  DetallesTraspasos.TraspasoID= " + Conversions.ToString(TraspasoID) + "\t ) as tab1 on tab1.ProductoID=Productos.ID ", ("Productos.Borrado= " + VariableGeneral.armarBolean(0) + " and Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.esCombo=" + VariableGeneral.armarBolean(0)) ?? "", "TiposProductos.Descripcion , Productos.Nombre");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Productos.ID, Productos.Nombre,  iif(Productos.Stock" + Conversions.ToString(almacenId) + " is null,0,Stock" + Conversions.ToString(almacenId) + ") as enStock,  productos.Presentacion, iif(tab1.Cantidad is null,0,tab1.Cantidad) as CantidadAjustada, tab1.Observacion, tab1.CantidadFinal, TiposProductos.Descripcion as Tipos,tab1.Costos as Costo,Productos.Habilitado,  Productos.CantidadML , productos.UnidadContenido, Productos.Codigo", "\t(Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID ) INNER join (  SELECT DetallesTraspasos.ProductoID, DetallesTraspasos.Cantidad, DetallesTraspasos.CantidadFinal, DetallesTraspasos.Observacion,DetallesTraspasos.Costos\t \tFROM DetallesTraspasos \tWHERE  DetallesTraspasos.TraspasoID= " + Conversions.ToString(TraspasoID) + "\t ) as tab1 on tab1.ProductoID=Productos.ID ", ("Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.esCombo=" + VariableGeneral.armarBolean(0)) ?? "", "TiposProductos.Descripcion , Productos.Nombre");
		}
		return BD.ConsultaVer("Productos.ID, Productos.Nombre,  CASE WHEN  Productos.Stock" + Conversions.ToString(almacenId) + " is null  THEN 0 ELSE  Stock" + Conversions.ToString(almacenId) + "  END  as enStock,  productos.Presentacion,CASE WHEN tab1.Cantidad is null  THEN  0 ELSE  Cantidad  END  as CantidadAjustada, tab1.Observacion, tab1.CantidadFinal, TiposProductos.Descripcion as Tipos,  CASE tab1.Cantidad WHEN 0 THEN 0 ELSE (CASE WHEN tab1.Costos is null  THEN  0 ELSE tab1.Costos END /   Cantidad) END as Costo , tab1.Costos as CostoTotal, Productos.Habilitado,  Productos.CantidadML , productos.UnidadContenido, Productos.Codigo", "\t(Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID ) INNER join (  SELECT DetallesTraspasos.ProductoID, CASE WHEN DetallesTraspasos.Cantidad is null  THEN 0 ELSE DetallesTraspasos.Cantidad END as Cantidad, DetallesTraspasos.CantidadFinal, DetallesTraspasos.Observacion,DetallesTraspasos.Costos\t \tFROM DetallesTraspasos \tWHERE  DetallesTraspasos.TraspasoID= " + Conversions.ToString(TraspasoID) + "\t ) as tab1 on tab1.ProductoID=Productos.ID ", ("Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.esCombo=" + VariableGeneral.armarBolean(0)) ?? "", "TiposProductos.Descripcion , Productos.Nombre");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select DetallesTraspasos.DetalleTraspasoID,DetallesTraspasos.Cantidad,DetallesTraspasos.Observacion,DetallesTraspasos.TraspasoID,DetallesTraspasos.ProductoID", "DetallesTraspasos) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetallesTraspasos", "Cantidad=" + Conversion.Str(Cantidad) + ",Observacion='" + Observacion + "',TraspasoID=" + TraspasoID + ",ProductoID=" + ProductoID + ",flagSync=NULL", "DetalleTraspasoID=" + DetalleTraspasoID);
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

	public int Insertar(BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (bd1 != null)
			{
				bd1.ConsultaInsertar(string.Concat(string.Concat(string.Concat(Conversion.Str(Cantidad) + "," + Conversion.Str(CantidadFinal) + ",'" + Observacion + "',", TraspasoID.ToString(), ","), ProductoID.ToString(), ","), Conversions.ToString(Math.Round(Conversions.ToDouble(Conversion.Str(Costos)) * Conversions.ToDouble(Conversion.Str(Cantidad)), 2))) ?? "", "DetallesTraspasos(Cantidad,CantidadFinal,Observacion,TraspasoID,ProductoID,Costos)", ref DetalleTraspasoID);
				result = DetalleTraspasoID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(Conversion.Str(Cantidad) + "," + Conversion.Str(CantidadFinal) + ",'" + Observacion + "',", TraspasoID.ToString(), ","), ProductoID.ToString(), ","), Conversion.Str(Costos * Cantidad)) ?? "", "DetallesTraspasos(Cantidad,CantidadFinal,Observacion,TraspasoID,ProductoID,Costos)", ref DetalleTraspasoID);
				result = DetalleTraspasoID;
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

	public int EliminarxpProdID()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("DetallesTraspasos", "ProductoID = " + ProductoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar DetalleTraspaso, se encuentra en uso");
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

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("DetallesTraspasos", "DetalleTraspasoID = " + DetalleTraspasoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar DetalleTraspaso, se encuentra en uso");
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

	public void DevolverDetalleTraspaso(ref DataSet data, string datNombre, int almacenId)
	{
		string text = "Select Productos.ID, Productos.Nombre,  CASE WHEN  Productos.Stock" + Conversions.ToString(almacenId) + " is null  THEN 0 ELSE  Stock" + Conversions.ToString(almacenId) + "  END  as enStock,  Productos.CantidadML , productos.UnidadContenido,productos.Presentacion,CASE WHEN tab1.Cantidad is null  THEN  0 ELSE  Cantidad  END  as CantidadAjustada, tab1.Observacion, tab1.CantidadFinal, TiposProductos.Descripcion as Tipos, Round(tab1.Costos,2) as Costo,Productos.Habilitado";
		text += " From\t(Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID ) INNER join (  SELECT DetallesTraspasos.ProductoID, DetallesTraspasos.Cantidad, DetallesTraspasos.CantidadFinal, DetallesTraspasos.Observacion,DetallesTraspasos.Costos\t ";
		text += " FROM DetallesTraspasos ";
		text = text + " WHERE  DetallesTraspasos.TraspasoID= " + Conversions.ToString(TraspasoID);
		text = text + " ) as tab1 on tab1.ProductoID=Productos.ID  where Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.esCombo=" + VariableGeneral.armarBolean(0) + "  order by  TiposProductos.Descripcion,Productos.Nombre";
		BD.ConsultaVerDataset(ref data, text, datNombre);
	}
}
