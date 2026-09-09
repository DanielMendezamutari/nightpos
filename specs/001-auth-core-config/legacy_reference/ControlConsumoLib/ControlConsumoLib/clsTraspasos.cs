using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsTraspasos
{
	private int TraspasoID;

	private DateTime Fecha;

	private string Observacion;

	private int almacenID;

	private int almacenID2;

	private int MeseroID;

	private int EncargadoID;

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

	public DateTime _Fecha
	{
		get
		{
			return Fecha;
		}
		set
		{
			Fecha = value;
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

	public int _almacenID
	{
		get
		{
			return almacenID;
		}
		set
		{
			almacenID = value;
		}
	}

	public int _almacenID2
	{
		get
		{
			return almacenID2;
		}
		set
		{
			almacenID2 = value;
		}
	}

	public int _MeseroID
	{
		get
		{
			return MeseroID;
		}
		set
		{
			MeseroID = value;
		}
	}

	public int _EncargadoID
	{
		get
		{
			return EncargadoID;
		}
		set
		{
			EncargadoID = value;
		}
	}

	public clsTraspasos()
	{
		Fecha = DateAndTime.Now;
		Observacion = "";
		almacenID = 0;
		almacenID2 = 0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Traspasos", " TraspasoID=" + TraspasoID);
		if (dataTable.Rows.Count > 0)
		{
			TraspasoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TraspasoID"])) ? ((object)0) : dataTable.Rows[0]["TraspasoID"]);
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			almacenID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["almacenID"])) ? ((object)0) : dataTable.Rows[0]["almacenID"]);
			almacenID2 = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["almacenID2"])) ? ((object)false) : dataTable.Rows[0]["almacenID2"]);
			MeseroID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MeseroID"])) ? ((object)false) : dataTable.Rows[0]["MeseroID"]);
			EncargadoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EncargadoID"])) ? ((object)false) : dataTable.Rows[0]["EncargadoID"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("*", "Traspasos");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select *", "Traspasos) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar(BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (bd1 != null)
			{
				bd1.ConsultaModificar("Traspasos", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",Observacion='" + Observacion + "',MeseroID=" + Conversions.ToString(MeseroID) + ",flagSync=NULL", "TraspasoID=" + TraspasoID);
				result = 1;
			}
			else
			{
				BD.ConsultaModificar("Traspasos", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",Observacion='" + Observacion + "',MeseroID=" + Conversions.ToString(MeseroID) + ",flagSync=NULL", "TraspasoID=" + TraspasoID);
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

	public double verificarCantItems(BD_SQL bd1 = null)
	{
		if (bd1 != null)
		{
			return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(bd1.ConsultaVer("sum(Cantidad)", "DetallesTraspasos", "TraspasoID=" + Conversions.ToString(TraspasoID)).Rows[0][0]), 0));
		}
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Cantidad)", "DetallesTraspasos", "TraspasoID=" + Conversions.ToString(TraspasoID)).Rows[0][0]), 0));
	}

	public int Insertar(BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (bd1 != null)
			{
				bd1.ConsultaInsertar(VariableGeneral.ArmarFechaSQL(Fecha) + ",'" + Observacion + "'," + Conversions.ToString(almacenID) + "," + Conversions.ToString(almacenID2) + "," + Conversions.ToString(MeseroID) + "," + Conversions.ToString(EncargadoID), "Traspasos(Fecha,Observacion,AlmacenID,almacenID2,MeseroID,EncargadoID)", ref TraspasoID);
				result = TraspasoID;
			}
			else
			{
				BD.ConsultaInsertar3(VariableGeneral.ArmarFecha(Fecha) + ",'" + Observacion + "'," + Conversions.ToString(almacenID) + "," + Conversions.ToString(almacenID2) + "," + Conversions.ToString(MeseroID) + "," + Conversions.ToString(EncargadoID), "Traspasos(Fecha,Observacion,AlmacenID,almacenID2,MeseroID,EncargadoID)", ref TraspasoID);
				result = TraspasoID;
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
			if (BD.ConsultaEliminar("Traspasos", "TraspasoID = " + TraspasoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Traspaso, se encuentra en uso");
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

	public bool ExisteAlmacen()
	{
		bool result;
		try
		{
			result = BD.ConsultaVer("*", "Traspasos", "AlmacenID = " + Conversions.ToString(almacenID)).Rows.Count > 0;
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

	public DataTable DevolverTraspasos()
	{
		return BD.ConsultaVer("Traspasos.TraspasoID, Traspasos.Fecha, Traspasos.Observacion, Traspasos.AlmacenID, Traspasos.AlmacenID2, Almacenes.nombre as Desde, Al2.Nombre as Para , round(sum(DetallesTraspasos.Cantidad),1) as CantItems, round(sum(DetallesTraspasos.Costos),2) as CostoTotal", "((Traspasos left join Almacenes on Almacenes.AlmacenID=Traspasos.AlmacenID) left join Almacenes  as Al2 on Al2.AlmacenID=Traspasos.AlmacenID2) left join DetallesTraspasos on DetallesTraspasos.TraspasoID=Traspasos.TraspasoID", "", "Traspasos.Fecha desc", "Traspasos.TraspasoID, Traspasos.Fecha, Traspasos.Observacion, Traspasos.AlmacenID, Traspasos.AlmacenID2, Almacenes.nombre, Al2.Nombre");
	}

	public DataTable DevolverTraspasoParcial()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Traspasos.TraspasoID, Traspasos.Fecha, Traspasos.Observacion, Traspasos.AlmacenID, Traspasos.AlmacenID2, Almacenes.nombre as Desde, Al2.Nombre as Para, round(sum(DetallesTraspasos.Cantidad),1) as CantItems, round(sum(DetallesTraspasos.Costos),2) as CostoTotal", "((Traspasos left join Almacenes on Almacenes.AlmacenID=Traspasos.AlmacenID) left join Almacenes  as Al2 on Al2.AlmacenID=Traspasos.AlmacenID2) left join DetallesTraspasos on DetallesTraspasos.TraspasoID=Traspasos.TraspasoID", "Traspasos.Fecha> DATEADD('m',-1,Now)", "Traspasos.Fecha desc", "Traspasos.TraspasoID, Traspasos.Fecha, Traspasos.Observacion, Traspasos.AlmacenID, Traspasos.AlmacenID2, Almacenes.nombre, Al2.Nombre");
		}
		return BD.ConsultaVer("Traspasos.TraspasoID, Traspasos.Fecha, Traspasos.Observacion, Traspasos.AlmacenID, Traspasos.AlmacenID2, Almacenes.nombre as Desde, Al2.Nombre as Para, round(sum(DetallesTraspasos.Cantidad),1) as CantItems, round(sum(DetallesTraspasos.Costos),2) as CostoTotal", "(Traspasos left join Almacenes on Almacenes.AlmacenID=Traspasos.AlmacenID) left join Almacenes  as Al2 on Al2.AlmacenID=Traspasos.AlmacenID2  left join DetallesTraspasos on DetallesTraspasos.TraspasoID=Traspasos.TraspasoID", "Traspasos.Fecha> DATEADD(MM,-1,GETDATE())", "Traspasos.Fecha desc", "Traspasos.TraspasoID, Traspasos.Fecha, Traspasos.Observacion, Traspasos.AlmacenID, Traspasos.AlmacenID2, Almacenes.nombre, Al2.Nombre");
	}

	public DataTable DevolverReporteTraspasos(DateTime fechai, DateTime fechaf)
	{
		if (MeseroID > 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("traspasos.traspasoID,traspasos.Fecha , Almacenes.nombre as 'Almacen Origen', tab1.Destino as 'Almacen Destino' ,TiposProductos.Descripcion  as Categoria, Productos.Codigo, Productos.Nombre, Productos.Presentacion ,DetallesTraspasos.cantidad,(DetallesTraspasos.Costos / DetallesTraspasos.cantidad) as CostoUnitario,(DetallesTraspasos.Costos) as CostoTotal, Productos.Precio as PrecioVenta, DetallesTraspasos.cantidad*Productos.Precio as PrecioTotal,  DetallesTraspasos.observacion as Obs, Traspasos.Observacion, Meseros.Nombre as 'Entregado a' ", "(((((traspasos left join almacenes on traspasos.almacenID=almacenes.AlmacenID)  left join DetallesTraspasos on traspasos.traspasoID = DetallesTraspasos.traspasoID) left join productos on DetallesTraspasos.ProductoID = productos.ID) left join Meseros on Traspasos.MeseroID =Meseros.MeseroID) left join TiposProductos on Productos.TipoProductoID  =TiposProductos.TipoProductoID )  left join (Select traspasos.traspasoID, Almacenes.Nombre as Destino from traspasos left join almacenes on traspasos.almacenID2=almacenes.AlmacenID) tab1 on tab1.TraspasoID =traspasos.traspasoID ", " traspasos.MeseroID = " + Conversions.ToString(MeseroID) + " and traspasos.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
			}
			return BD.ConsultaVer("traspasos.traspasoID,traspasos.Fecha , Almacenes.nombre as 'Almacen Origen', tab1.Destino as 'Almacen Destino' ,TiposProductos.Descripcion  as Categoria, Productos.Codigo, Productos.Nombre, Productos.Presentacion, DetallesTraspasos.cantidad,(DetallesTraspasos.Costos / DetallesTraspasos.cantidad) as CostoUnitario,(DetallesTraspasos.Costos) as CostoTotal, Productos.Precio as PrecioVenta, DetallesTraspasos.cantidad*Productos.Precio as PrecioTotal,DetallesTraspasos.observacion as Obs, Traspasos.Observacion, Meseros.Nombre as 'Entregado a' ", "((((traspasos left join almacenes on traspasos.almacenID=almacenes.AlmacenID)  left join DetallesTraspasos on traspasos.traspasoID = DetallesTraspasos.traspasoID) left join productos on DetallesTraspasos.ProductoID = productos.ID left join Meseros on Traspasos.MeseroID =Meseros.MeseroID)  left join TiposProductos on Productos.TipoProductoID  =TiposProductos.TipoProductoID )  left join (Select traspasos.traspasoID, Almacenes.Nombre as Destino from traspasos left join almacenes on traspasos.almacenID2=almacenes.AlmacenID) tab1 on tab1.TraspasoID =traspasos.traspasoID ", " traspasos.MeseroID = " + Conversions.ToString(MeseroID) + " and  traspasos.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("traspasos.traspasoID,traspasos.Fecha , Almacenes.nombre as 'Almacen Origen', tab1.Destino as 'Almacen Destino' ,TiposProductos.Descripcion  as Categoria, Productos.Codigo, Productos.Nombre, Productos.Presentacion, DetallesTraspasos.cantidad,(DetallesTraspasos.Costos / DetallesTraspasos.cantidad) as CostoUnitario,(DetallesTraspasos.Costos) as CostoTotal, Productos.Precio as PrecioVenta, DetallesTraspasos.cantidad*Productos.Precio as PrecioTotal,DetallesTraspasos.observacion as Obs, Traspasos.Observacion, Meseros.Nombre as 'Entregado a' ", "(((((traspasos left join almacenes on traspasos.almacenID=almacenes.AlmacenID)  left join DetallesTraspasos on traspasos.traspasoID = DetallesTraspasos.traspasoID) left join productos on DetallesTraspasos.ProductoID = productos.ID) left join Meseros on Traspasos.MeseroID =Meseros.MeseroID)  left join TiposProductos on Productos.TipoProductoID  =TiposProductos.TipoProductoID )  left join (Select traspasos.traspasoID, Almacenes.Nombre as Destino from traspasos left join almacenes on traspasos.almacenID2=almacenes.AlmacenID) tab1 on tab1.TraspasoID =traspasos.traspasoID", " traspasos.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
		}
		return BD.ConsultaVer("traspasos.traspasoID,traspasos.Fecha , Almacenes.nombre as 'Almacen Origen', tab1.Destino as 'Almacen Destino' ,TiposProductos.Descripcion  as Categoria, Productos.Codigo, Productos.Nombre, Productos.Presentacion, DetallesTraspasos.cantidad,(DetallesTraspasos.Costos / DetallesTraspasos.cantidad) as CostoUnitario,(DetallesTraspasos.Costos) as CostoTotal, Productos.Precio as PrecioVenta, DetallesTraspasos.cantidad*Productos.Precio as PrecioTotal,DetallesTraspasos.observacion as Obs, Traspasos.Observacion, Meseros.Nombre as 'Entregado a' ", "((((traspasos left join almacenes on traspasos.almacenID=almacenes.AlmacenID)  left join DetallesTraspasos on traspasos.traspasoID = DetallesTraspasos.traspasoID) left join productos on DetallesTraspasos.ProductoID = productos.ID left join Meseros on Traspasos.MeseroID =Meseros.MeseroID)  left join TiposProductos on Productos.TipoProductoID  =TiposProductos.TipoProductoID )  left join (Select traspasos.traspasoID, Almacenes.Nombre as Destino from traspasos left join almacenes on traspasos.almacenID2=almacenes.AlmacenID) tab1 on tab1.TraspasoID =traspasos.traspasoID", " traspasos.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
	}

	public int DevolverEncargadoID()
	{
		DataTable dataTable = BD.ConsultaVer("EncargadoID", "Traspasos", "TraspasoID = " + Conversions.ToString(TraspasoID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		return 0;
	}

	public int ModificarObservacion()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Traspasos", "Observacion='" + Observacion + "',flagSync=NULL", "TraspasoID=" + TraspasoID);
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
}
