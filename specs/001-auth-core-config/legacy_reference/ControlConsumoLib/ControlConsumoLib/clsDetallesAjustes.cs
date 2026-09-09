using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDetallesAjustes
{
	private int DetalleAjusteID;

	private double Cantidad;

	private double CantidadFinal;

	private string Observacion;

	private int AjusteID;

	private int ProductoID;

	private double Costo;

	private double CostoBruto;

	public int _DetalleAjusteID
	{
		get
		{
			return DetalleAjusteID;
		}
		set
		{
			DetalleAjusteID = value;
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

	public int _AjusteID
	{
		get
		{
			return AjusteID;
		}
		set
		{
			AjusteID = value;
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

	public clsDetallesAjustes()
	{
		Cantidad = 0.0;
		Observacion = "";
		AjusteID = 0;
		ProductoID = 0;
		CantidadFinal = 0.0;
		Costo = 0.0;
		CostoBruto = 0.0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "DetallesAjustes", " DetalleAjusteID=" + DetalleAjusteID);
		if (dataTable.Rows.Count > 0)
		{
			DetalleAjusteID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DetalleAjusteID"])) ? ((object)0) : dataTable.Rows[0]["DetalleAjusteID"]);
			Cantidad = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Cantidad"])) ? ((object)0) : dataTable.Rows[0]["Cantidad"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			AjusteID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AjusteID"])) ? ((object)0) : dataTable.Rows[0]["AjusteID"]);
			ProductoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ProductoID"])) ? ((object)0) : dataTable.Rows[0]["ProductoID"]);
			Costo = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Costo"])) ? ((object)0) : dataTable.Rows[0]["Costo"]);
			CostoBruto = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CostoBruto"])) ? ((object)0) : dataTable.Rows[0]["CostoBruto"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("DetallesAjustes.DetalleAjusteID,DetallesAjustes.Cantidad,DetallesAjustes.Observacion,DetallesAjustes.AjusteID,DetallesAjustes.ProductoID,DetallesAjustes.Costo,DetallesAjustes.CostoBruto", "DetallesAjustes");
	}

	public DataTable DevolverXAjusteID(int almacenID)
	{
		if (AjusteID == 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Productos.ID,Productos.Orden, Productos.Nombre,  iif(Productos.Stock" + Conversions.ToString(almacenID) + " is null,0,Stock" + Conversions.ToString(almacenID) + ") as enStock, productos.Presentacion,   iif(tab1.Cantidad is null,0,tab1.Cantidad) as CantidadAjustada, tab1.Observacion, tab1.CantidadFinal, TiposProductos.Descripcion as Tipos ,productos.Habilitado, iif(Productos.Costo is null,0,Productos.Costo) as Costo, iif(Productos.CostoBruto is null,0,Productos.CostoBruto) as CostoBruto,Productos.CantidadML , productos.UnidadContenido, Productos.Codigo", "\t(Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID ) left join (  SELECT DetallesAjustes.ProductoID, DetallesAjustes.Cantidad, DetallesAjustes.CantidadFinal, DetallesAjustes.Observacion\t \tFROM DetallesAjustes \tWHERE  DetallesAjustes.AjusteID= " + Conversions.ToString(AjusteID) + "\t ) as tab1 on tab1.ProductoID=Productos.ID ", ("Productos.Borrado= " + VariableGeneral.armarBolean(0) + " and Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.esCombo=" + VariableGeneral.armarBolean(0)) ?? "", "Productos.Orden,TiposProductos.Descripcion , Productos.Nombre");
			}
			return BD.ConsultaVer("Productos.ID,Productos.Orden, Productos.Nombre,  CASE WHEN  Productos.Stock" + Conversions.ToString(almacenID) + " is null  THEN 0 ELSE  Stock" + Conversions.ToString(almacenID) + "  END  as enStock,  productos.Presentacion,   CASE WHEN tab1.Cantidad is null  THEN  0 ELSE  Cantidad  END  as CantidadAjustada, tab1.Observacion, tab1.CantidadFinal, TiposProductos.Descripcion as Tipos  ,productos.Habilitado,  CASE WHEN  Productos.Costo is null  THEN 0 ELSE  Productos.Costo  END  as Costo,  CASE WHEN  Productos.CostoBruto is null  THEN 0 ELSE  Productos.CostoBruto  END  as CostoBruto,Productos.CantidadML , productos.UnidadContenido, Productos.Codigo", "\t(Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID ) left join (  SELECT DetallesAjustes.ProductoID, DetallesAjustes.Cantidad, DetallesAjustes.CantidadFinal, DetallesAjustes.Observacion\t \tFROM DetallesAjustes \tWHERE  DetallesAjustes.AjusteID= " + Conversions.ToString(AjusteID) + "\t ) as tab1 on tab1.ProductoID=Productos.ID ", ("Productos.Borrado= " + VariableGeneral.armarBolean(0) + " and Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.esCombo=" + VariableGeneral.armarBolean(0)) ?? "", "Productos.Orden,TiposProductos.Descripcion , Productos.Nombre");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Productos.ID,Productos.Orden, Productos.Nombre,  iif(Productos.Stock" + Conversions.ToString(almacenID) + " is null,0,Stock" + Conversions.ToString(almacenID) + ") as enStock,  productos.Presentacion,  iif(tab1.Cantidad is null,0,tab1.Cantidad) as CantidadAjustada, tab1.Observacion, tab1.CantidadFinal, TiposProductos.Descripcion as Tipos  ,productos.Habilitado, iif(Productos.Costo is null,0,Productos.Costo) as Costo, iif(Productos.CostoBruto is null,0,Productos.CostoBruto) as CostoBruto,Productos.CantidadML , productos.UnidadContenido, Productos.Codigo", "\t(Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID ) inner join (  SELECT DetallesAjustes.ProductoID, DetallesAjustes.Cantidad, DetallesAjustes.CantidadFinal, DetallesAjustes.Observacion\t \tFROM DetallesAjustes \tWHERE  DetallesAjustes.AjusteID= " + Conversions.ToString(AjusteID) + "\t ) as tab1 on tab1.ProductoID=Productos.ID ", ("Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.esCombo=" + VariableGeneral.armarBolean(0)) ?? "", "Productos.Orden,TiposProductos.Descripcion , Productos.Nombre");
		}
		return BD.ConsultaVer("Productos.ID,Productos.Orden, Productos.Nombre,  CASE WHEN  Productos.Stock" + Conversions.ToString(almacenID) + " is null  THEN 0 ELSE  Stock" + Conversions.ToString(almacenID) + "  END  as enStock,  productos.Presentacion,  CASE WHEN tab1.Cantidad is null  THEN  0 ELSE  Cantidad  END  as CantidadAjustada, tab1.Observacion, tab1.CantidadFinal, TiposProductos.Descripcion as Tipos  ,productos.Habilitado,  CASE WHEN  Productos.Costo is null  THEN 0 ELSE  Productos.Costo  END  as Costo,  CASE WHEN  Productos.CostoBruto is null  THEN 0 ELSE  Productos.CostoBruto  END  as CostoBruto,Productos.CantidadML , productos.UnidadContenido, Productos.Codigo", "\t(Productos inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID ) inner join (  SELECT DetallesAjustes.ProductoID, DetallesAjustes.Cantidad, DetallesAjustes.CantidadFinal, DetallesAjustes.Observacion\t \tFROM DetallesAjustes \tWHERE  DetallesAjustes.AjusteID= " + Conversions.ToString(AjusteID) + "\t ) as tab1 on tab1.ProductoID=Productos.ID ", ("Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.esCombo=" + VariableGeneral.armarBolean(0)) ?? "", "Productos.Orden,TiposProductos.Descripcion , Productos.Nombre");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select DetallesAjustes.DetalleAjusteID,DetallesAjustes.Cantidad,DetallesAjustes.Observacion,DetallesAjustes.AjusteID,DetallesAjustes.ProductoID", "DetallesAjustes) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetallesAjustes", "Cantidad=" + Conversion.Str(Cantidad) + ",Observacion='" + Observacion + "',AjusteID=" + AjusteID + ",ProductoID=" + ProductoID + ",Costo=" + Conversion.Str(Costo) + ",CostoBruto=" + Conversion.Str(CostoBruto) + ",flagSync=NULL", "DetalleAjusteID=" + DetalleAjusteID);
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

	public int Insertar()
	{
		int result;
		try
		{
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				DetalleAjusteID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(DetalleAjusteID)", "DetallesAjustes").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(DetalleAjusteID) + "," + Conversion.Str(Cantidad) + "," + Conversion.Str(CantidadFinal) + ",'" + Observacion + "',", AjusteID.ToString(), ","), ProductoID.ToString(), ","), Conversion.Str(Costo), ","), Conversion.Str(CostoBruto)) ?? "", "DetallesAjustes(DetalleAjusteID,Cantidad,CantidadFinal,Observacion,AjusteID,ProductoID, Costo,CostoBruto)");
				result = ProductoID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(Conversion.Str(Cantidad) + "," + Conversion.Str(CantidadFinal) + ",'" + Observacion + "',", AjusteID.ToString(), ","), ProductoID.ToString(), ","), Conversion.Str(Costo), ","), Conversion.Str(CostoBruto)) ?? "", "DetallesAjustes(Cantidad,CantidadFinal,Observacion,AjusteID,ProductoID, Costo,CostoBruto)", ref DetalleAjusteID);
				result = ProductoID;
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
			if (BD.ConsultaEliminar("DetallesAjustes", "ProductoID = " + ProductoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar DetalleAjuste, se encuentra en uso");
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
			if (BD.ConsultaEliminar("DetallesAjustes", "DetalleAjusteID = " + DetalleAjusteID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar DetalleAjuste, se encuentra en uso");
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

	public void DevolverDetalleAjuste(ref DataSet data, string datNombre)
	{
		string text = "select Almacenes.Nombre as Almacen, Ajustes.AjusteID, TiposProductos.Descripcion as Categoria, ";
		text += "Productos.Nombre as Producto, Round(DetallesAjustes.CantidadFinal - DetallesAjustes.cantidad, 2)  as CantidadTeorica, Productos.Presentacion, Round(DetallesAjustes.cantidad,2) as CantidadAjustada, Round(DetallesAjustes.CantidadFinal,2) as CantidadFinalAjustada,";
		text += "DetallesAjustes.Observacion ";
		text += "from Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID ";
		text += "left join DetallesAjustes on Ajustes.AjusteID = DetallesAjustes.AjusteID ";
		text += "left join productos on DetallesAjustes.ProductoID = productos.ID ";
		text += "left join TiposProductos on Productos.TipoProductoID =TiposProductos.TipoProductoID ";
		text = text + "where DetallesAjustes.AjusteID = " + Conversions.ToString(_AjusteID);
		BD.ConsultaVerDataset(ref data, text, datNombre);
	}
}
