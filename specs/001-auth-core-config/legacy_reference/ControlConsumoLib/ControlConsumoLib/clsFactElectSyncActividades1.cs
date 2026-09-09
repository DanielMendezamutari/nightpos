using System;
using System.Data;
using System.Net;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElectSyncActividades1
{
	private int ID;

	private string Codigo1;

	private string Descripcion;

	private string tipo;

	public int _ID
	{
		get
		{
			return ID;
		}
		set
		{
			ID = value;
		}
	}

	public string _Codigo1
	{
		get
		{
			return Codigo1;
		}
		set
		{
			Codigo1 = value;
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

	public string _tipo
	{
		get
		{
			return tipo;
		}
		set
		{
			tipo = value;
		}
	}

	public clsFactElectSyncActividades1()
	{
		ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Codigo,  Descripcion, tipo", "FactElectActividades", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public DataTable DevolverConCodigo()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Codigo, str(Codigo ) + ' - ' + Descripcion, tipo", "FactElectActividades ", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		}
		return BD.ConsultaVer("Codigo, CAST(Codigo as varchar(16)) + '-' + Descripcion, tipo", "FactElectActividades ", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public int Insertar()
	{
		BD.ConsultaInsertar3("'" + Codigo1 + "','" + Descripcion + "','" + tipo + "'," + Conversions.ToString(VariableGeneral.gConfiguracionID), "FactElectActividades(Codigo,Descripcion, tipo,ConfiguracionID)", ref ID);
		return ID;
	}

	public int EliminarTodo()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("FactElectActividades", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID)) == 0)
			{
				Interaction.MsgBox("no se puede eliminar el Borrado, se encuentra en uso");
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
}
