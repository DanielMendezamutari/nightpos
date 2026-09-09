using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsTiposProductosPY
{
	private int TipoProductoPYid;

	private string SKU;

	private string Nombre;

	private int Orden;

	public int _TipoProductoPYid
	{
		get
		{
			return TipoProductoPYid;
		}
		set
		{
			TipoProductoPYid = value;
		}
	}

	public string _SKU
	{
		get
		{
			return SKU;
		}
		set
		{
			SKU = value;
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

	public int _Orden
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

	public string devolverTiposProductosPYporID()
	{
		DataTable dataTable = BD.ConsultaVer("SKU,Nombre, ", "TiposProductosPY", "TipoProductoPYid=" + Conversions.ToString(TipoProductoPYid));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToString(dataTable.Rows[0][0]);
		}
		return "";
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("TiposProductosPY.TipoProductoPYid,TiposProductosPY.SKU, TiposProductosPY.Nombre, TiposProductosPY.Orden", "TiposProductosPY");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("TiposProductosPY", "SKU='" + SKU + "',Nombre='" + Nombre + "',Orden=" + Conversions.ToString(Orden), "TipoProductoPYid=" + TipoProductoPYid);
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
			BD.ConsultaInsertar3("'" + SKU + "','" + Nombre + "'," + Conversions.ToString(Orden), "TiposProductosPY(SKU,Nombre,Orden )", ref TipoProductoPYid);
			result = TipoProductoPYid;
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
			if (BD.ConsultaEliminar("TiposProductosPY", "TipoProductoPYid = " + TipoProductoPYid) == 0)
			{
				Interaction.MsgBox("no se puede eliminar TipoGasto, se encuentra en uso");
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
}
