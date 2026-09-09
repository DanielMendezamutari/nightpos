using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDetallesProduccion
{
	private int DetalleProduccionID;

	private double Producida;

	private double Eliminada;

	private double Reciclada;

	private string Observacion;

	private int ProduccionID;

	private int ProductoID;

	public int _DetalleProduccionID
	{
		get
		{
			return DetalleProduccionID;
		}
		set
		{
			DetalleProduccionID = value;
		}
	}

	public double _Producida
	{
		get
		{
			return Producida;
		}
		set
		{
			Producida = value;
		}
	}

	public double _Reciclada
	{
		get
		{
			return Reciclada;
		}
		set
		{
			Reciclada = value;
		}
	}

	public double _Eliminada
	{
		get
		{
			return Eliminada;
		}
		set
		{
			Eliminada = value;
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

	public int _ProduccionID
	{
		get
		{
			return ProduccionID;
		}
		set
		{
			ProduccionID = value;
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

	public clsDetallesProduccion()
	{
		Producida = 0.0;
		Observacion = "";
		ProduccionID = 0;
		ProductoID = 0;
		Eliminada = 0.0;
		Reciclada = 0.0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "DetallesProduccion", " DetalleProduccionID=" + DetalleProduccionID);
		if (dataTable.Rows.Count > 0)
		{
			DetalleProduccionID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DetalleProduccionID"])) ? ((object)0) : dataTable.Rows[0]["DetalleProduccionID"]);
			Producida = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Producida"])) ? ((object)0) : dataTable.Rows[0]["Producida"]);
			Reciclada = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Reciclada"])) ? ((object)0) : dataTable.Rows[0]["Reciclada"]);
			Eliminada = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Eliminada"])) ? ((object)0) : dataTable.Rows[0]["Eliminada"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			ProduccionID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ProduccionID"])) ? ((object)0) : dataTable.Rows[0]["ProduccionID"]);
			ProductoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ProductoID"])) ? ((object)0) : dataTable.Rows[0]["ProductoID"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("DetallesProduccion.DetalleProduccionID,DetallesProduccion.Producida,DetallesProduccion.Eliminada,DetallesProduccion.Reciclada,DetallesProduccion.Observacion,DetallesProduccion.ProduccionID,DetallesProduccion.ProductoID", "DetallesProduccion");
	}

	public DataTable DevolverXProduccionID()
	{
		if (ProduccionID == 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("tab1.DetalleProduccionID,Productos.ID as ProductoID,CategoriasProduccion.Nombre as Categoria, Productos.Nombre as Producto,Productos.Presentacion ,   iif(tab1.Producida is null,0,tab1.Producida) as Cantidad, iif(tab1.Eliminada is null,0,tab1.Eliminada) as Eliminada, iif(tab1.Reciclada is null,0,tab1.Reciclada) as Reciclada,  iif(tab1.Producida is null,0,tab1.Producida-tab1.Eliminada-tab1.Reciclada) as Uso,  tab1.Observacion as Observaciones , Impresoras.Nombre as Impresora,'' as ProductosCombo, 0 as MeseroID, TiposProductos.AlmacenID", "\t(((Productos left join (  SELECT DetallesProduccion.DetalleProduccionID,DetallesProduccion.ProductoID, DetallesProduccion.Producida, DetallesProduccion.Eliminada,DetallesProduccion.Reciclada, DetallesProduccion.Observacion FROM DetallesProduccion \tWHERE  DetallesProduccion.ProduccionID=" + Conversions.ToString(ProduccionID) + " ) as tab1 on tab1.ProductoID=Productos.ID) inner join CategoriasProduccion on CategoriasProduccion.CategoriaProduccionID = Productos.CategoriaProduccionID)LEFT JOIN TiposProductos on TiposProductos.TipoProductoID= Productos.TipoProductoID ) LEFT JOIN Impresoras on Impresoras.ImpresoraId=TiposProductos.ImpresoraID   ", "Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Productos.Borrado = " + VariableGeneral.armarBolean(0), "CategoriasProduccion.Nombre,Productos.Nombre");
			}
			return BD.ConsultaVer("tab1.DetalleProduccionID,Productos.ID as ProductoID,CategoriasProduccion.Nombre as Categoria, Productos.Nombre  as Producto,Productos.Presentacion ,   CASE WHEN tab1.Producida is null  THEN  0 ELSE  Producida  END  as Cantidad,   CASE WHEN tab1.Eliminada is null  THEN  0 ELSE  Eliminada  END  as Eliminada,   CASE WHEN tab1.Reciclada is null  THEN  0 ELSE  Reciclada  END  as Reciclada, case when tab1.Producida is null THEN 0 else tab1.Producida-tab1.Eliminada-tab1.Reciclada end as Uso,  tab1.Observacion  as Observaciones, Impresoras.Nombre as Impresora,'' as ProductosCombo, 0 as MeseroID, TiposProductos.AlmacenID", "\tProductos left join (  SELECT DetallesProduccion.DetalleProduccionID,DetallesProduccion.ProductoID, DetallesProduccion.Producida, DetallesProduccion.Eliminada,DetallesProduccion.Reciclada, DetallesProduccion.Observacion FROM DetallesProduccion \tWHERE  DetallesProduccion.ProduccionID=" + Conversions.ToString(ProduccionID) + " ) as tab1 on tab1.ProductoID=Productos.ID inner join CategoriasProduccion on CategoriasProduccion.CategoriaProduccionID = Productos.CategoriaProduccionID  LEFT JOIN TiposProductos on TiposProductos.TipoProductoID= Productos.TipoProductoID LEFT JOIN Impresoras on Impresoras.ImpresoraId=TiposProductos.ImpresoraID  ", "Productos.TienePreparacion= " + VariableGeneral.armarBolean(0) + " and Productos.Borrado = " + VariableGeneral.armarBolean(0), "CategoriasProduccion.Nombre,Productos.Nombre");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("tab1.DetalleProduccionID,Productos.ID as ProductoID,CategoriasProduccion.Nombre as Categoria, Productos.Nombre as Producto,Productos.Presentacion ,   iif(tab1.Producida is null,0,tab1.Producida) as Cantidad, iif(tab1.Eliminada is null,0,tab1.Eliminada) as Eliminada, iif(tab1.Reciclada is null,0,tab1.Reciclada) as Reciclada,  iif(tab1.Producida is null,0,tab1.Producida-tab1.Eliminada-tab1.Reciclada) as Uso,  tab1.Observacion as Observaciones , Impresoras.Nombre as Impresora,'' as ProductosCombo, 0 as MeseroID, TiposProductos.AlmacenID", "\t((((  SELECT DetallesProduccion.DetalleProduccionID,DetallesProduccion.ProductoID, DetallesProduccion.Producida, DetallesProduccion.Eliminada,DetallesProduccion.Reciclada, DetallesProduccion.Observacion FROM DetallesProduccion \tWHERE  DetallesProduccion.ProduccionID=" + Conversions.ToString(ProduccionID) + " ) as tab1  left join Productos  on tab1.ProductoID=Productos.ID) left join CategoriasProduccion on CategoriasProduccion.CategoriaProduccionID = Productos.CategoriaProduccionID)LEFT JOIN TiposProductos on TiposProductos.TipoProductoID= Productos.TipoProductoID ) LEFT JOIN Impresoras on Impresoras.ImpresoraId=TiposProductos.ImpresoraID   ", "1=1", "CategoriasProduccion.Nombre,Productos.Nombre");
		}
		return BD.ConsultaVer("tab1.DetalleProduccionID,Productos.ID as ProductoID,CategoriasProduccion.Nombre as Categoria, Productos.Nombre  as Producto,Productos.Presentacion ,   CASE WHEN tab1.Producida is null  THEN  0 ELSE  Producida  END  as Cantidad,   CASE WHEN tab1.Eliminada is null  THEN  0 ELSE  Eliminada  END  as Eliminada,   CASE WHEN tab1.Reciclada is null  THEN  0 ELSE  Reciclada  END  as Reciclada, case when tab1.Producida is null THEN 0 else tab1.Producida-tab1.Eliminada-tab1.Reciclada end as Uso,  tab1.Observacion  as Observaciones, Impresoras.Nombre as Impresora,'' as ProductosCombo, 0 as MeseroID, TiposProductos.AlmacenID", "(  SELECT DetallesProduccion.DetalleProduccionID,DetallesProduccion.ProductoID, DetallesProduccion.Producida, DetallesProduccion.Eliminada,DetallesProduccion.Reciclada, DetallesProduccion.Observacion FROM DetallesProduccion \tWHERE  DetallesProduccion.ProduccionID=" + Conversions.ToString(ProduccionID) + " ) as tab1  left join Productos  on tab1.ProductoID=Productos.ID left join CategoriasProduccion on CategoriasProduccion.CategoriaProduccionID = Productos.CategoriaProduccionID  LEFT JOIN TiposProductos on TiposProductos.TipoProductoID= Productos.TipoProductoID LEFT JOIN Impresoras on Impresoras.ImpresoraId=TiposProductos.ImpresoraID  ", "1=1", "CategoriasProduccion.Nombre,Productos.Nombre");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select DetallesProduccion.DetalleProduccionID,DetallesProduccion.Producida,DetallesProduccion.Eliminada,DetallesProduccion.Reciclada,DetallesProduccion.Observacion,DetallesProduccion.ProduccionID,DetallesProduccion.ProductoID", "DetallesProduccion) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetallesProduccion", "Producida=" + Conversion.Str(Producida) + ",Eliminada=" + Conversion.Str(Eliminada) + ",Reciclada=" + Conversion.Str(Reciclada) + ",Observacion='" + Observacion + "',ProduccionID=" + ProduccionID + ",ProductoID=" + ProductoID + ",flagSync=NULL", "DetalleProduccionID=" + DetalleProduccionID);
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
				DetalleProduccionID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(DetalleProduccionID)", "DetallesProduccion").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(DetalleProduccionID) + "," + Conversion.Str(Producida) + "," + Conversion.Str(Eliminada) + "," + Conversion.Str(Reciclada) + ",'" + Observacion + "',", ProduccionID.ToString(), ","), ProductoID.ToString()) ?? "", "DetallesProduccion(DetalleProduccionID,Producida,Eliminada,Reciclada,Observacion,ProduccionID ,ProductoID)");
				result = ProductoID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat(Conversion.Str(Producida) + "," + Conversion.Str(Eliminada) + "," + Conversion.Str(Reciclada) + ",'" + Observacion + "',", ProduccionID.ToString(), ","), ProductoID.ToString()) ?? "", "DetallesProduccion(Producida,Eliminada,Reciclada,Observacion,ProduccionID ,ProductoID)", ref DetalleProduccionID);
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

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("DetallesProduccion", "DetalleProduccionID = " + DetalleProduccionID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar DetalleProduccion, se encuentra en uso");
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

	public DataTable DevolverReporteProduccion(DateTime fechaIni, DateTime fechaFin)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("tab1.ProduccionID,Produccion.Fecha as FechaProduccion,   CategoriasProduccion.Nombre as Categoria, Productos.Nombre as Producto,  iif(tab1.Producida is null,0,tab1.Producida) as Cantidad, iif(tab1.Eliminada is null,0,tab1.Eliminada) as Eliminada, iif(tab1.Reciclada is null,0,tab1.Reciclada) as Reciclada,  iif(tab1.Producida is null,0,tab1.Producida-tab1.Eliminada-tab1.Reciclada) as Uso,  tab1.Observacion as Observaciones ", "\t((( (  SELECT DetallesProduccion.DetalleProduccionID,DetallesProduccion.ProductoID, DetallesProduccion.Producida, DetallesProduccion.Eliminada,DetallesProduccion.Reciclada, DetallesProduccion.Observacion, DetallesProduccion.ProduccionID FROM DetallesProduccion  ) as tab1  left join Productos on tab1.ProductoID=Productos.ID) left join CategoriasProduccion on CategoriasProduccion.CategoriaProduccionID = Productos.CategoriaProduccionID) LEFT JOIN TiposProductos on TiposProductos.TipoProductoID= Productos.TipoProductoID ) LEFT JOIN Produccion on Produccion.ProduccionId=tab1.ProduccionID   ", ("  Tab1.Producida>0 and Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Produccion.Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin)) ?? "", "tab1.ProduccionID");
		}
		return BD.ConsultaVer("tab1.ProduccionID,Produccion.Fecha as FechaProduccion,  CategoriasProduccion.Nombre as Categoria, Productos.Nombre  as Producto,  CASE WHEN tab1.Producida is null  THEN  0 ELSE  Producida  END  as Cantidad,   CASE WHEN tab1.Eliminada is null  THEN  0 ELSE  Eliminada  END  as Eliminada,   CASE WHEN tab1.Reciclada is null  THEN  0 ELSE  Reciclada  END  as Reciclada, case when tab1.Producida is null THEN 0 else tab1.Producida-tab1.Eliminada-tab1.Reciclada end as Uso,  tab1.Observacion  as Observaciones", "\t (  SELECT DetallesProduccion.DetalleProduccionID,DetallesProduccion.ProductoID, DetallesProduccion.Producida, DetallesProduccion.Eliminada,DetallesProduccion.Reciclada, DetallesProduccion.Observacion, DetallesProduccion.ProduccionID FROM DetallesProduccion ) as tab1   left join Productos on tab1.ProductoID=Productos.ID left join CategoriasProduccion on CategoriasProduccion.CategoriaProduccionID = Productos.CategoriaProduccionID  LEFT JOIN TiposProductos on TiposProductos.TipoProductoID= Productos.TipoProductoID LEFT JOIN Produccion on Produccion.ProduccionId=tab1.ProduccionID  ", ("  Tab1.Producida>0 and Productos.TienePreparacion=" + VariableGeneral.armarBolean(0) + " and Produccion.Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin)) ?? "", "tab1.ProduccionID");
	}
}
