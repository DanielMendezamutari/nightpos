using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsPreProcesamientosPara
{
	private int PreProcesamientoParaID;

	private double Cantidad;

	private int ProductoID;

	private int PreProcesamientoID;

	public int _PreProcesamientoParaID
	{
		get
		{
			return PreProcesamientoParaID;
		}
		set
		{
			PreProcesamientoParaID = value;
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

	public clsPreProcesamientosPara()
	{
		Cantidad = 0.0;
		PreProcesamientoParaID = 0;
		ProductoID = 0;
		PreProcesamientoID = 0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "PreProcesamientosPara", " PreProcesamientoParaID=" + PreProcesamientoParaID);
		if (dataTable.Rows.Count > 0)
		{
			PreProcesamientoParaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PreProcesamientoParaID"])) ? ((object)0) : dataTable.Rows[0]["PreProcesamientoParaID"]);
			Cantidad = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Cantidad"])) ? "" : dataTable.Rows[0]["Cantidad"]);
			ProductoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ProductoID"])) ? "" : dataTable.Rows[0]["ProductoID"]);
			PreProcesamientoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PreProcesamientoID"])) ? "" : dataTable.Rows[0]["PreProcesamientoID"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("PreProcesamientosPara.PreProcesamientoParaID,PreProcesamientosPara.Cantidad,PreProcesamientosPara.ProductoID,PreProcesamientosPara.PreProcesamientoID", "PreProcesamientosPara");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("PreProcesamientosPara", "Cantidad=" + Conversion.Str(Cantidad) + ",ProductoID=" + Conversions.ToString(ProductoID) + ",PreProcesamientoID='" + Conversions.ToString(PreProcesamientoID) + "',flagSync=NULL", "PreProcesamientoParaID=" + PreProcesamientoParaID);
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
			BD.ConsultaInsertar3(Conversion.Str(Cantidad) + "," + Conversions.ToString(ProductoID) + "," + Conversions.ToString(PreProcesamientoID), "PreProcesamientosPara(Cantidad,ProductoID,PreProcesamientoID)", ref PreProcesamientoParaID);
			PreProcesamientoParaID = Conversions.ToInteger(BD.ConsultaVer("max(PreProcesamientoParaID)", "PreProcesamientosPara").Rows[0][0]);
			result = PreProcesamientoParaID;
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
			if (BD.ConsultaEliminar("PreProcesamientosPara", "PreProcesamientoParaID = " + PreProcesamientoParaID) == 0)
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
		return BD.ConsultaVer("Productos.Nombre as Nombre, PreProcesamientosPara.PreProcesamientoParaID,PreProcesamientosPara.Cantidad,PreProcesamientosPara.ProductoID as Producto,PreProcesamientosPara.PreProcesamientoID,CASE when PreProcesamientosPara.Fecha =1 then 'Lunes' else CASE when PreProcesamientosPara.Fecha =2 then 'Martes' else CASE when PreProcesamientosPara.Fecha =3 then 'Miercoles' else CASE when PreProcesamientosPara.Fecha =4 then 'Jueves' else CASE when PreProcesamientosPara.Fecha =5 then 'Viernes' else CASE when PreProcesamientosPara.Fecha =6 then 'Sabado' else CASE when PreProcesamientosPara.Fecha =7 then 'Domingo' end end end end end end end as Dia, Clientes .Nombre", "PreProcesamientosPara inner join Productos on PreProcesamientosPara.ProductoID=Productos.ID left join Clientes on PreProcesamientosPara.PreProcesamientoID =Clientes.ID ", "PreProcesamientosPara.PreProcesamientoID = " + Conversions.ToString(PreProcesamientoID));
	}

	public double DevolverSaldo()
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("Sum(ProductoID)", "PreProcesamientosPara", "PreProcesamientosPara.PreProcesamientoID = " + Conversions.ToString(PreProcesamientoID)).Rows[0][0]), 0));
	}

	public double DevolverProductoID(ref double Cantidad)
	{
		DataTable dataTable = BD.ConsultaVer("top 1 ProductoID, PreProcesamientoParaID", "PreProcesamientosPara", "ProductoID>0 and PreProcesamientoID = " + Conversions.ToString(PreProcesamientoID));
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
		return BD.ConsultaVer("PreProcesamientosPara.PreProcesamientoParaID,PreProcesamientosPara.ProductoID,PreProcesamientosPara.PreProcesamientoID, Productos.Nombre,  Productos.Presentacion,Productos.Costo,PreProcesamientosPara.Cantidad", "PreProcesamientosPara left join Productos on Productos.ID=PreProcesamientosPara.ProductoID", "PreProcesamientoID= " + Conversions.ToString(PreProcesamientoID));
	}
}
