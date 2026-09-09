using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsLogg
{
	private int LoggID;

	private string Accion;

	private string Formulario;

	private int UserID;

	private DateTime Fecha;

	public int _LoggID
	{
		get
		{
			return LoggID;
		}
		set
		{
			LoggID = value;
		}
	}

	public string _Accion
	{
		get
		{
			return Accion;
		}
		set
		{
			Accion = value;
		}
	}

	public string _Formulario
	{
		get
		{
			return Formulario;
		}
		set
		{
			Formulario = value;
		}
	}

	public string _UserID
	{
		get
		{
			return Conversions.ToString(UserID);
		}
		set
		{
			UserID = Conversions.ToInteger(value);
		}
	}

	public int Insertar(string Formulario1, string Accion1, int userId1)
	{
		int result;
		try
		{
			if (Accion1.Length > 200)
			{
				Accion1 = Accion1.Substring(0, 199);
			}
			BD.ConsultaInsertar3(string.Concat(VariableGeneral.ArmarFecha(DateAndTime.Now) + ",'" + Accion1.Replace("'", "`") + "','" + Formulario1 + "',", Conversions.ToString(userId1)) ?? "", "Logg(Fecha,Accion,Formulario,UserID)", ref LoggID);
			result = LoggID;
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

	public DataTable devolverBorrandoServicio(DateTime desde, DateTime hasta)
	{
		DataTable result;
		try
		{
			result = BD.ConsultaVer("Fecha,Accion", "Logg", "Formulario like 'Borrando Servicio' and Fecha between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta));
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = new DataTable();
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public DataTable devolverRemoverItems(DateTime desde, DateTime hasta)
	{
		DataTable result;
		try
		{
			result = BD.ConsultaVer("Fecha,Accion,Meseros.Nombre", "Logg left join Meseros on Meseros.MeseroID=Logg.UserID", "Formulario like 'Remover Items' and Fecha between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "UserID,Fecha");
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = new DataTable();
			ProjectData.ClearProjectError();
		}
		return result;
	}
}
