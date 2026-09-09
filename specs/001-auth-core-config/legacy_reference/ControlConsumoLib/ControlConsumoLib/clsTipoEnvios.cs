using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsTipoEnvios
{
	private int TipoEnvioID;

	private string Nombre;

	private int Orden;

	private bool Activo;

	private int TarjetaCuentaID;

	private int CuponesCuentaID;

	private bool DeliveryExterno;

	public int _TipoEnvioID
	{
		get
		{
			return TipoEnvioID;
		}
		set
		{
			TipoEnvioID = value;
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

	public bool _Activo
	{
		get
		{
			return Activo;
		}
		set
		{
			Activo = value;
		}
	}

	public bool _DeliveryExterno
	{
		get
		{
			return DeliveryExterno;
		}
		set
		{
			DeliveryExterno = value;
		}
	}

	public int _TarjetaCuentaID
	{
		get
		{
			return TarjetaCuentaID;
		}
		set
		{
			TarjetaCuentaID = value;
		}
	}

	public int _CuponesCuentaID
	{
		get
		{
			return CuponesCuentaID;
		}
		set
		{
			CuponesCuentaID = value;
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("TipoEnvios.TipoEnvioID,TipoEnvios.Nombre,TipoEnvios.Orden,TipoEnvios.Activo,TipoEnvios.TarjetaCuentaID,TipoEnvios.CuponesCuentaID,DeliveryExterno", "TipoEnvios");
	}

	public DataTable DevolverMiData()
	{
		return BD.ConsultaVer("TipoEnvios.TipoEnvioID,TipoEnvios.Nombre,TipoEnvios.Orden,TipoEnvios.Activo,TipoEnvios.TarjetaCuentaID,TipoEnvios.CuponesCuentaID,DeliveryExterno", "TipoEnvios", "TipoEnvioID=" + Conversions.ToString(TipoEnvioID));
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select TipoEnvios.TipoEnvioID,TipoEnvios.Nombre,TipoEnvios.Orden,TipoEnvios.Activo,TipoEnvios.TarjetaCuentaID,TipoEnvios.CuponesCuentaID,DeliveryExterno", "TipoEnvios) as tab1");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("TipoEnvios", ("Nombre='" + Nombre + "',Orden=" + Conversions.ToString(Orden) + ",Activo=" + VariableGeneral.armarBolean(Activo) + ",DeliveryExterno=" + VariableGeneral.armarBolean(DeliveryExterno) + ",TarjetaCuentaID=" + Conversions.ToString(TarjetaCuentaID) + ",CuponesCuentaID=" + Conversions.ToString(CuponesCuentaID)) ?? "", "TipoEnvioID=" + TipoEnvioID);
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
				bd1.ConsultaInsertar(string.Concat(string.Concat("'" + Nombre + "',", Conversions.ToString(Orden), ","), VariableGeneral.armarBolean(Activo), ",", Conversions.ToString(TarjetaCuentaID), ",", Conversions.ToString(CuponesCuentaID), ",", VariableGeneral.armarBolean(DeliveryExterno)), "TipoEnvios(Nombre,Orden,Activo,TarjetaCuentaID, CuponesCuentaID,DeliveryExterno)", ref TipoEnvioID);
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat("'" + Nombre + "',", Conversions.ToString(Orden), ","), VariableGeneral.armarBolean(Activo), ",", Conversions.ToString(TarjetaCuentaID), ",", Conversions.ToString(CuponesCuentaID), ",", VariableGeneral.armarBolean(DeliveryExterno)), "TipoEnvios(Nombre,Orden,Activo,TarjetaCuentaID, CuponesCuentaID,DeliveryExterno)", ref TipoEnvioID);
			}
			result = TipoEnvioID;
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
			if (BD.ConsultaEliminar("TipoEnvios", "TipoEnvioID = " + TipoEnvioID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Almacen, se encuentra en uso");
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

	public DataTable DevolverTodosTipoEnvios()
	{
		return BD.ConsultaVer("TipoEnvios.TipoEnvioID,TipoEnvios.Nombre,TipoEnvios.Orden,TipoEnvios.Activo,TipoEnvios.TarjetaCuentaID,TipoEnvios.CuponesCuentaID,DeliveryExterno", "TipoEnvios");
	}

	public int devolverCuentaTarjeta(string nombre)
	{
		DataTable dataTable = BD.ConsultaVer("TarjetaCuentaID", "TipoEnvios", "nombre like '" + nombre + "'");
		if (dataTable.Rows.Count == 0)
		{
			return 0;
		}
		return Conversions.ToInteger(dataTable.Rows[0][0]);
	}

	public int devolverCuentaCupones(string nombre)
	{
		DataTable dataTable = BD.ConsultaVer("CuponesCuentaID", "TipoEnvios", "nombre like '" + nombre + "'");
		if (dataTable.Rows.Count == 0)
		{
			return 0;
		}
		return Conversions.ToInteger(dataTable.Rows[0][0]);
	}

	public bool DevolverDeliveryExterno(string nombre)
	{
		DataTable dataTable = BD.ConsultaVer("DeliveryExterno", "TipoEnvios", "nombre like '" + nombre + "'");
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		return Conversions.ToBoolean(dataTable.Rows[0][0]);
	}

	public int DevolverTipoEnvio(string nombre)
	{
		DataTable dataTable = BD.ConsultaVer("TipoEnvioID", "TipoEnvios", "nombre like '" + nombre + "'");
		if (dataTable.Rows.Count == 0)
		{
			return 0;
		}
		return Conversions.ToInteger(dataTable.Rows[0][0]);
	}

	public DataTable devolverTipoEnviosActivos()
	{
		return BD.ConsultaVer("TipoEnvios.TipoEnvioID,TipoEnvios.Nombre as TipoEnvio", "TipoEnvios", ("Activo=" + VariableGeneral.armarBolean(1)) ?? "", "Orden");
	}
}
