using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsAjustes
{
	private int AjusteID;

	private DateTime Fecha;

	private string Observacion;

	private int AlmacenID;

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

	public int _AlmacenID
	{
		get
		{
			return AlmacenID;
		}
		set
		{
			AlmacenID = value;
		}
	}

	public clsAjustes()
	{
		Fecha = DateAndTime.Now;
		Observacion = "";
		AjusteID = 0;
		AlmacenID = 0;
	}

	public void getLastDate()
	{
		DataTable dataTable = BD.ConsultaVer("max(fecha) as maxFecha", "Ajustes", " 1=1");
		if (dataTable.Rows.Count > 0)
		{
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["maxFecha"])) ? null : dataTable.Rows[0]["maxFecha"]);
		}
		else
		{
			Fecha = DateTime.MinValue;
		}
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Ajustes", " AjusteID=" + AjusteID);
		if (dataTable.Rows.Count > 0)
		{
			AjusteID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AjusteID"])) ? ((object)0) : dataTable.Rows[0]["AjusteID"]);
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			AlmacenID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AlmacenID"])) ? ((object)0) : dataTable.Rows[0]["AlmacenID"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Ajustes.AjusteID,Ajustes.Fecha,Ajustes.Observacion,Ajustes.AlmacenID", "Ajustes", "", "Fecha desc");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select Ajustes.AjusteID,Ajustes.Fecha,Ajustes.Observacion,Ajustes.AlmacenID", "Ajustes) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public DataTable Devolver1mesAtras()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Ajustes.AjusteID,Ajustes.Fecha,Ajustes.Observacion,Ajustes.AlmacenID", "Ajustes", "Fecha>DATEADD('m',-1,Now)", "Fecha desc");
		}
		return BD.ConsultaVer("Ajustes.AjusteID,Ajustes.Fecha,Ajustes.Observacion,Ajustes.AlmacenID", "Ajustes", "Fecha>DATEADD(MM,-1,GETDATE())", "Fecha desc");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Ajustes", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",Observacion='" + Observacion + "',AlmacenID='" + Conversions.ToString(AlmacenID) + "'", "AjusteID=" + AjusteID);
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
				AjusteID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(AjusteID)", "Ajustes").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(Conversions.ToString(AjusteID) + "," + VariableGeneral.ArmarFecha(Fecha) + ",'" + Observacion + "'," + Conversions.ToString(AlmacenID), "Ajustes(AjusteID,Fecha,Observacion,AlmacenID)");
				result = AjusteID;
			}
			else
			{
				BD.ConsultaInsertar3(VariableGeneral.ArmarFecha(Fecha) + ",'" + Observacion + "'," + Conversions.ToString(AlmacenID), "Ajustes(Fecha,Observacion,AlmacenID)", ref AjusteID);
				result = AjusteID;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			AjusteID = 0;
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
			if (BD.ConsultaEliminar("Ajustes", "AjusteID = " + AjusteID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Ajuste, se encuentra en uso");
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

	public DataTable DevolverReporteAjustes(DateTime fechai, DateTime fechaf)
	{
		if (AlmacenID > 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Ajustes.AjusteID,Ajustes.Fecha , Almacenes.nombre as Almacen , Productos.Nombre, Productos.UnidadContenido,DetallesAjustes.cantidad,(DetallesAjustes.Costo ) as costoUnitario,DetallesAjustes.observacion as Obs,(DetallesAjustes.Costo) as Total, Ajustes.Observacion ", "((((Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID)  left join DetallesAjustes on Ajustes.AjusteID = DetallesAjustes.AjusteID) left join productos on DetallesAjustes.ProductoID = productos.ID) ) left join (Select Ajustes.AjusteID, Almacenes.Nombre as Destino from Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID) tab1 on tab1.AjusteID =Ajustes.AjusteID ", " Ajustes.AlmacenID = " + Conversions.ToString(AlmacenID) + " and Ajustes.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
			}
			return BD.ConsultaVer("Ajustes.AjusteID,Ajustes.Fecha , Almacenes.nombre as Almacen , Productos.Nombre, Productos.UnidadContenido,DetallesAjustes.cantidad,DetallesAjustes.Costo  as costoUnitario,DetallesAjustes.observacion as Obs,(DetallesAjustes.Costo) as Total, Ajustes.Observacion ", "(((Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID)  left join DetallesAjustes on Ajustes.AjusteID = DetallesAjustes.AjusteID) left join productos on DetallesAjustes.ProductoID = productos.ID) left join (Select Ajustes.AjusteID, Almacenes.Nombre as Destino from Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID) tab1 on tab1.AjusteID =Ajustes.AjusteID ", " Ajustes.AlmacenID = " + Conversions.ToString(AlmacenID) + " and  Ajustes.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Ajustes.AjusteID,Ajustes.Fecha , Almacenes.nombre as Almacen , Productos.Nombre, Productos.UnidadContenido, ROUND(DetallesAjustes.cantidad, 2), ROUND(DetallesAjustes.Costo, 2) as costoUnitario,DetallesAjustes.observacion as Obs,(DetallesAjustes.Costo) as Total, Ajustes.Observacion ", "((((Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID)  left join DetallesAjustes on Ajustes.AjusteID = DetallesAjustes.AjusteID) left join productos on DetallesAjustes.ProductoID = productos.ID)) left join (Select Ajustes.AjusteID, Almacenes.Nombre as Destino from Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID) tab1 on tab1.AjusteID =Ajustes.AjusteID", " Ajustes.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
		}
		return BD.ConsultaVer("Ajustes.AjusteID,Ajustes.Fecha , Almacenes.nombre as Almacen  , Productos.Nombre, Productos.UnidadContenido, ROUND(DetallesAjustes.cantidad, 2), ROUND(DetallesAjustes.Costo, 2) as costoUnitario,DetallesAjustes.observacion as Obs,(DetallesAjustes.Costo) as Total, Ajustes.Observacion ", "(((Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID)  left join DetallesAjustes on Ajustes.AjusteID = DetallesAjustes.AjusteID) left join productos on DetallesAjustes.ProductoID = productos.ID ) left join (Select Ajustes.AjusteID, Almacenes.Nombre as Destino from Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID) tab1 on tab1.AjusteID =Ajustes.AjusteID", " Ajustes.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
	}

	public DataTable DevolverReporteAjustes2(DateTime fechai, DateTime fechaf)
	{
		if (AlmacenID > 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Ajustes.AjusteID ,TiposProductos.Descripcion as Categoria, Productos.Codigo as 'Codigo del Producto',Productos.Nombre as Producto, DetallesAjustes.CantidadFinal - DetallesAjustes.cantidad  as CantidadTeorica ,   Productos.Presentacion, DetallesAjustes.cantidad  as 'Cantidad de Ajuste' ,DetallesAjustes.observacion,DetallesAjustes.CantidadFinal as 'Cantidad Final Ajustada', Productos.Habilitado,DetallesAjustes.Costo  as costoUnitario,DetallesAjustes.CostoBruto  as costoBruto,(DetallesAjustes.Costo * DetallesAjustes.CantidadFinal) as 'Total Final Ajustado'", "((((Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID)  left join DetallesAjustes on Ajustes.AjusteID = DetallesAjustes.AjusteID) left join productos on DetallesAjustes.ProductoID = productos.ID ) left join TiposProductos on Productos.TipoProductoID =TiposProductos.TipoProductoID)left join (Select Ajustes.AjusteID, Almacenes.Nombre as Destino from Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID) tab1 on tab1.AjusteID =Ajustes.AjusteID ", " Ajustes.AlmacenID = " + Conversions.ToString(AlmacenID) + " and Ajustes.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
			}
			return BD.ConsultaVer("Ajustes.AjusteID ,TiposProductos.Descripcion as Categoria, Productos.Codigo as 'Codigo del Producto',Productos.Nombre as Producto, DetallesAjustes.CantidadFinal - DetallesAjustes.cantidad  as CantidadTeorica ,  Productos.Presentacion, DetallesAjustes.cantidad  as 'Cantidad de Ajuste' ,DetallesAjustes.observacion,DetallesAjustes.CantidadFinal as 'Cantidad Final Ajustada', Productos.Habilitado,DetallesAjustes.Costo  as costoUnitario,DetallesAjustes.CostoBruto  as costoBruto,(DetallesAjustes.Costo * DetallesAjustes.CantidadFinal) as 'Total Final Ajustado'", "((((Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID)  left join DetallesAjustes on Ajustes.AjusteID = DetallesAjustes.AjusteID) left join productos on DetallesAjustes.ProductoID = productos.ID ) left join TiposProductos on Productos.TipoProductoID =TiposProductos.TipoProductoID)left join (Select Ajustes.AjusteID, Almacenes.Nombre as Destino from Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID) tab1 on tab1.AjusteID =Ajustes.AjusteID ", " Ajustes.AlmacenID = " + Conversions.ToString(AlmacenID) + " and  Ajustes.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Ajustes.AjusteID ,TiposProductos.Descripcion as Categoria, Productos.Codigo as 'Codigo del Producto',Productos.Nombre as Producto, ROUND(DetallesAjustes.CantidadFinal - DetallesAjustes.cantidad, 3)  as CantidadTeorica ,   Productos.Presentacion, ROUND(DetallesAjustes.cantidad, 3)  as 'Cantidad de Ajuste' ,DetallesAjustes.observacion,DetallesAjustes.CantidadFinal as 'Cantidad Final Ajustada', Productos.Habilitado,(DetallesAjustes.Costo /  DetallesAjustes.cantidad) as costoUnitario ,(DetallesAjustes.CostoBruto /  DetallesAjustes.cantidad) as costoBruto,(DetallesAjustes.Costo * DetallesAjustes.CantidadFinal) as 'Total Final Ajustado'", "((((Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID)  left join DetallesAjustes on Ajustes.AjusteID = DetallesAjustes.AjusteID) left join productos on DetallesAjustes.ProductoID = productos.ID ) left join TiposProductos on Productos.TipoProductoID =TiposProductos.TipoProductoID)left join (Select Ajustes.AjusteID, Almacenes.Nombre as Destino from Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID) tab1 on tab1.AjusteID =Ajustes.AjusteID ", " Ajustes.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
		}
		return BD.ConsultaVer("Ajustes.AjusteID ,TiposProductos.Descripcion as Categoria, Productos.Codigo as 'Codigo del Producto',Productos.Nombre as Producto, ROUND(DetallesAjustes.CantidadFinal - DetallesAjustes.cantidad, 3)  as CantidadTeorica ,  Productos.Presentacion, ROUND(DetallesAjustes.cantidad, 3)  as 'Cantidad de Ajuste' ,DetallesAjustes.observacion,DetallesAjustes.CantidadFinal as 'Cantidad Final Ajustada', Productos.Habilitado, DetallesAjustes.Costo as costoUnitario,DetallesAjustes.CostoBruto  as costoBruto,(DetallesAjustes.Costo* DetallesAjustes.CantidadFinal) as 'Total Final Ajustado'", "((((Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID)  left join DetallesAjustes on Ajustes.AjusteID = DetallesAjustes.AjusteID) left join productos on DetallesAjustes.ProductoID = productos.ID ) left join TiposProductos on Productos.TipoProductoID =TiposProductos.TipoProductoID)left join (Select Ajustes.AjusteID, Almacenes.Nombre as Destino from Ajustes left join almacenes on Ajustes.almacenID=almacenes.AlmacenID) tab1 on tab1.AjusteID =Ajustes.AjusteID ", " Ajustes.Fecha between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaf));
	}

	public DataTable DevolverReporteAjustes3(int Ajustei, int Ajustef, DateTime fechai, DateTime fechaf)
	{
		DataTable result;
		try
		{
			result = BD.ConsultaVerParaReporte("declare @ini as date = " + VariableGeneral.ArmarFecha(fechai) + "\r\n                    declare @fin as date = " + VariableGeneral.ArmarFecha(fechaf) + "\r\n                    select Codigo, Producto, Presentacion, Inicial, Compras, IngxTraspaso as [IngresoXtraspaso], IngTotales as IngresosTotales, InventarioFisico,\r\n                    IngTotales - InventarioFisico as Dispuesto, Costo as CostoUniBs, ROUND(((IngTotales - InventarioFisico) * Costo),3) as UsadoBs,\r\n                    IngTotales - Ventas as Sistema, Ventas, SalidaXtraspaso, ROUND(Costo * (InventarioFisico - (IngTotales - Ventas)),3) as VariaBs,\r\n                    ROUND(InventarioFisico - (IngTotales - Ventas),3) as Varia from\r\n                    (select Productos.Codigo, Productos.Nombre as Producto, Productos.Presentacion, AjusteInicial as Inicial,\r\n                    COALESCE(ROUND(CantidadCompra, 3),0) as Compras, COALESCE(ROUND(CostoCompra, 3),0) as Costo,\r\n                    COALESCE(IngresoxTraspaso,0) as IngxTraspaso,\r\n                    AjusteInicial + COALESCE(ROUND(CantidadCompra, 3),0) + COALESCE(IngresoxTraspaso,0) as IngTotales,\r\n                    COALESCE(SalidaxTraspaso,0) as SalidaXtraspaso, COALESCE(Ventas,0) as Ventas, CantidadTeorica, \r\n                    COALESCE(AjusteFinal,0) as InventarioFisico, ROUND(AjusteFinal-CantidadTeorica, 3) as Diferencia from Productos\r\n\r\n                    left join (select productoID, sum(CostoBruto) as CostoCompra,sum(cantidad) as CantidadCompra from DetalleProductosCompra where FechaEntrega between @ini and @fin group by ProductoID) as TabCompra on Productos.ID = TabCompra.ProductoID\r\n                    left join (select productoID, CantidadFinal as AjusteInicial from DetallesAjustes where AjusteID = (select top 1 AjusteID from Ajustes where AjusteID = " + Conversions.ToString(Ajustei) + ")) as TabAjusteIni on Productos.ID = TabAjusteIni.ProductoID\r\n                    left join (select productoID, ROUND(Cantidad, 3) as CantidadAjustada, CantidadFinal as AjusteFinal, ROUND(DetallesAjustes.CantidadFinal - DetallesAjustes.cantidad, 3) as CantidadTeorica from DetallesAjustes where AjusteID = (select top 1 AjusteID from Ajustes where AjusteID = " + Conversions.ToString(Ajustef) + ")) as TabAjusteFin on Productos.ID = TabAjusteFin.ProductoID\r\n                    left join (select ProductoID, sum(cantidad) as IngresoxTraspaso from DetallesTraspasos where traspasoID in (select TraspasoID from Traspasos where AlmacenID2 = 1 and Fecha between @ini and @fin) group by DetallesTraspasos.ProductoID) as TabIngresoTraspaso on Productos.ID  = TabIngresoTraspaso.ProductoID\r\n                    left join (select ProductoID, sum(cantidad) as SalidaxTraspaso from DetallesTraspasos where traspasoID in (select TraspasoID from Traspasos where AlmacenID = 1 and Fecha between @ini and @fin) group by DetallesTraspasos.ProductoID) as TabSalidaTraspaso on Productos.ID  = TabSalidaTraspaso.ProductoID\r\n                    left join (select ID, ROUND(Sum(Cantidad),3) as Ventas from (select productos.ID, sum(ProductosUsos.Cantidad) as Cantidad \r\n                    from ((ProductosUsos inner join DetalleCuenta on ProductosUsos.DetalleCuentaID = DetalleCuenta.ID) \r\n                    inner join Productos on ProductosUsos.ProductoID = Productos.ID)  where (ProductosUsos.Fecha between @ini and @fin) group by productos.ID union select productos.ID, sum(ProductosUsos.Cantidad) as Cantidad\r\n                    from ((ProductosUsos inner join Produccion on ProductosUsos.ProduccionID = Produccion.ProduccionID) inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha between @ini and @fin) group by productos.ID union select productos.ID, sum(ProductosUsos.Cantidad) as Cantidad \r\n                    from ((ProductosUsos inner join Preprocesamientos on ProductosUsos.PreProcesamientoID = Preprocesamientos.PreprocesamientoID )inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha between @ini and @fin) group by productos.ID) as tab1 group by id) as TabVentas on TabVentas.ID = Productos.ID\r\n\r\n                    where Productos.id > 3 and Productos.ID in (TabAjusteIni.ProductoID)\r\n                    ) as Tab1 order by Producto", consolidado: false);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox("Error en la consulta");
			result = new DataTable();
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public DataTable DevolverListaAjustes()
	{
		if (AlmacenID > 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("AjusteID, cast(cast(Fecha as date) as varchar)  + ' - ' + Almacenes.nombre  + ' - ' + Observacion ", "Ajustes left join Almacenes on Almacenes.AlmacenID = Ajustes.AlmacenID", " Ajustes.AlmacenID = " + Conversions.ToString(AlmacenID), "AjusteID desc");
			}
			return BD.ConsultaVer("AjusteID, cast(cast(Fecha as date) as varchar)  + ' - ' + Almacenes.nombre  + ' - ' + Observacion ", "Ajustes left join Almacenes on Almacenes.AlmacenID = Ajustes.AlmacenID", " Ajustes.AlmacenID = " + Conversions.ToString(AlmacenID), "AjusteID desc");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("AjusteID, cast(cast(Fecha as date) as varchar)  + ' - ' + Almacenes.nombre  + ' - ' + Observacion ", "Ajustes left join Almacenes on Almacenes.AlmacenID = Ajustes.AlmacenID", "", "AjusteID desc");
		}
		return BD.ConsultaVer("AjusteID, cast(cast(Fecha as date) as varchar)  + ' - ' + Almacenes.nombre  + ' - ' + Observacion ", "Ajustes left join Almacenes on Almacenes.AlmacenID = Ajustes.AlmacenID", "", "AjusteID desc");
	}
}
