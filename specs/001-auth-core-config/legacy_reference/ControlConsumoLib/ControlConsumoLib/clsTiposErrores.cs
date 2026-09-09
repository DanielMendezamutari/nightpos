using System;
using System.Data;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsTiposErrores
{
	private int TipoErrorID;

	private string Descripcion;

	private bool Activo;

	public int _TipoErrorID
	{
		get
		{
			return TipoErrorID;
		}
		set
		{
			TipoErrorID = value;
		}
	}

	public string _Descripcion
	{
		get
		{
			return Descripcion;
		}
		set
		{
			Descripcion = value;
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

	public DataTable Devolver()
	{
		return BD.ConsultaVer("TiposErrores.TipoErrorID,TiposErrores.Descripcion,TiposErrores.Activo", "TiposErrores");
	}

	public int Insertar()
	{
		BD.ConsultaInsertar3(string.Concat("'" + Descripcion + "',", VariableGeneral.armarBolean(Activo)) ?? "", "TiposErrores(Descripcion,Activo)", ref TipoErrorID);
		return TipoErrorID;
	}

	public void Modificar()
	{
		BD.ConsultaModificar("TiposErrores", ("Descripcion='" + Descripcion + "',Activo=" + VariableGeneral.armarBolean(Activo)) ?? "", "TipoErrorID= " + TipoErrorID);
	}

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("TiposErrores", "TipoErrorID = " + TipoErrorID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar el Descuento, se encuentra en uso");
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

	public DataTable DevolverTiposErrores()
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer("TipoErrorID,Descripcion", "TiposErrores", "Activo = " + VariableGeneral.armarBolean(1));
		}
		if (configuration.gMODO_ACCESS == 0)
		{
			return BD.ConsultaVer("TipoErrorID,Descripcion", "TiposErrores", "Activo = " + VariableGeneral.armarBolean(1));
		}
		return BD.ConsultaVer("TipoErrorID,Descripcion", "TiposErrores", "Activo = " + VariableGeneral.armarBolean(1));
	}
}
