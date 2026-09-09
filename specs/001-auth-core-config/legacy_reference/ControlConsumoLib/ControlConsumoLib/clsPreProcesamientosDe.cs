using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsPreProcesamientosDe
{
	private int PreProcesamientoDeID;

	private double Cantidad;

	private int ProductoID;

	private int PreProcesamientoID;

	private double Costos;

	public int _PreProcesamientoDeID
	{
		get
		{
			return PreProcesamientoDeID;
		}
		set
		{
			PreProcesamientoDeID = value;
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

	public int _PreProcesamientoID
	{
		get
		{
			return PreProcesamientoID;
		}
		set
		{
			PreProcesamientoID = value;
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

	public clsPreProcesamientosDe()
	{
		Cantidad = 0.0;
		PreProcesamientoDeID = 0;
		ProductoID = 0;
		PreProcesamientoID = 0;
		Costos = 0.0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "PreProcesamientosDe", " PreProcesamientoDeID=" + PreProcesamientoDeID);
		if (dataTable.Rows.Count > 0)
		{
			PreProcesamientoDeID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PreProcesamientoDeID"])) ? ((object)0) : dataTable.Rows[0]["PreProcesamientoDeID"]);
			Cantidad = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Cantidad"])) ? "" : dataTable.Rows[0]["Cantidad"]);
			ProductoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ProductoID"])) ? "" : dataTable.Rows[0]["ProductoID"]);
			PreProcesamientoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PreProcesamientoID"])) ? "" : dataTable.Rows[0]["PreProcesamientoID"]);
			Costos = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Costos"])) ? "" : dataTable.Rows[0]["Costos"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("PreProcesamientosDe.PreProcesamientoDeID,PreProcesamientosDe.Cantidad,PreProcesamientosDe.ProductoID,PreProcesamientosDe.PreProcesamientoID,PreProcesamientosDe.Costos", "PreProcesamientosDe");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("PreProcesamientosDe", "Cantidad=" + Conversion.Str(Cantidad) + ",ProductoID=" + Conversions.ToString(ProductoID) + ",PreProcesamientoID='" + Conversions.ToString(PreProcesamientoID) + "',Costos=" + Conversion.Str(Costos) + ",flagSync=NULL", "PreProcesamientoDeID=" + PreProcesamientoDeID);
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
			BD.ConsultaInsertar3(Conversion.Str(Cantidad) + "," + Conversions.ToString(ProductoID) + "," + Conversions.ToString(PreProcesamientoID) + "," + Conversion.Str(Cantidad), "PreProcesamientosDe(Cantidad,ProductoID,PreProcesamientoID,Costos)", ref PreProcesamientoDeID);
			PreProcesamientoDeID = Conversions.ToInteger(BD.ConsultaVer("max(PreProcesamientoDeID)", "PreProcesamientosDe").Rows[0][0]);
			result = PreProcesamientoDeID;
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
			if (BD.ConsultaEliminar("PreProcesamientosDe", "PreProcesamientoDeID = " + PreProcesamientoDeID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar ClientePedido, se encuentra en uso");
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

	public DataTable DevolverPorID()
	{
		return BD.ConsultaVer("Productos.Nombre as Nombre, PreProcesamientosDe.PreProcesamientoDeID,PreProcesamientosDe.Cantidad,PreProcesamientosDe.ProductoID as Producto,PreProcesamientosDe.PreProcesamientoID,CASE when PreProcesamientosDe.Fecha =1 then 'Lunes' else CASE when PreProcesamientosDe.Fecha =2 then 'Martes' else CASE when PreProcesamientosDe.Fecha =3 then 'Miercoles' else CASE when PreProcesamientosDe.Fecha =4 then 'Jueves' else CASE when PreProcesamientosDe.Fecha =5 then 'Viernes' else CASE when PreProcesamientosDe.Fecha =6 then 'Sabado' else CASE when PreProcesamientosDe.Fecha =7 then 'Domingo' end end end end end end end as Dia, Clientes .Nombre", "PreProcesamientosDe inner join Productos on PreProcesamientosDe.ProductoID=Productos.ID left join Clientes on PreProcesamientosDe.PreProcesamientoID =Clientes.ID ", "PreProcesamientosDe.PreProcesamientoID = " + Conversions.ToString(PreProcesamientoID));
	}

	public double DevolverSaldo()
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("Sum(ProductoID)", "PreProcesamientosDe", "PreProcesamientosDe.PreProcesamientoID = " + Conversions.ToString(PreProcesamientoID)).Rows[0][0]), 0));
	}

	public double DevolverProductoID(ref double Cantidad)
	{
		DataTable dataTable = BD.ConsultaVer("top 1 ProductoID, PreProcesamientoDeID", "PreProcesamientosDe", "ProductoID>0 and PreProcesamientoID = " + Conversions.ToString(PreProcesamientoID));
		if (dataTable.Rows.Count > 0)
		{
			Cantidad = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
			return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), 0));
		}
		Cantidad = 0.0;
		return 0.0;
	}

	public DataTable DevolverXID()
	{
		return BD.ConsultaVer("PreProcesamientosDe.PreProcesamientoDeID ,PreProcesamientosDe.ProductoID,PreProcesamientosDe.PreProcesamientoID, Productos.Nombre, Productos.Presentacion, Productos.Costo,PreProcesamientosDe.Cantidad", "PreProcesamientosDe left join Productos on Productos.ID=PreProcesamientosDe.ProductoID", "PreProcesamientoID= " + Conversions.ToString(PreProcesamientoID));
	}

	public DataTable DevolverReporte(DateTime fechaIni, DateTime fechaFin)
	{
		return BD.ConsultaVer("select Preprocesamientos.PreprocesamientoID,Preprocesamientos.Fecha, Almacenes.nombre ,\r\n                            Productos.Nombre, Productos.Presentacion ,PreprocesamientosDe.Cantidad *(-1) as Cantidad  ,Preprocesamientos.Observacion \r\n                            from Preprocesamientos left join PreprocesamientosDe on Preprocesamientos.PreprocesamientoID =PreprocesamientosDe.PreprocesamientoID\r\n                            left join Almacenes on Preprocesamientos.AlmacenID =Almacenes.AlmacenID \r\n                            left join productos on Productos.ID =PreprocesamientosDe.ProductoID \r\n                            where Preprocesamientos.Fecha  between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin) + " union\r\n                            select Preprocesamientos.PreprocesamientoID,Preprocesamientos.Fecha,Almacenes.nombre  ,\r\n                            Productos.Nombre , Productos.Presentacion  , PreprocesamientosPara.Cantidad  ,Preprocesamientos.Observacion \r\n                            from Preprocesamientos left join PreprocesamientosPara on Preprocesamientos.PreprocesamientoID =PreprocesamientosPara.PreprocesamientoID\r\n                            left join Almacenes on Preprocesamientos.AlmacenID =Almacenes.AlmacenID \r\n                            left join productos on Productos.ID =PreprocesamientosPara.ProductoID \r\n                            where Preprocesamientos.Fecha  between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin));
	}
}
