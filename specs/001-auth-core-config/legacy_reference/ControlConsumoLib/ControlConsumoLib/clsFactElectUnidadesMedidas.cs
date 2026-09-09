using System;
using System.Data;
using System.Net;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElectUnidadesMedidas
{
	private int Codigo;

	private string Descripcion;

	public int _Codigo
	{
		get
		{
			return Codigo;
		}
		set
		{
			Codigo = value;
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

	public clsFactElectUnidadesMedidas()
	{
		ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Codigo,  Descripcion", "FactElectUnidadesMedidas", "1=1", "Orden, Descripcion");
	}

	public DataTable DevolverConCodigo()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Codigo,Descripcion + ' - ' + str(Codigo)", "FactElectUnidadesMedidas", "1=1", "Orden, Descripcion");
		}
		return BD.ConsultaVer("Codigo, Descripcion + ' - ' +  CAST(Codigo as varchar(16))", "FactElectUnidadesMedidas", "1=1", "Orden, Descripcion");
	}

	public int Insertar()
	{
		BD.ConsultaInsertar3(Conversions.ToString(Codigo) + ",'" + Descripcion + "',1", "FactElectUnidadesMedidas(Codigo,Descripcion, Orden)", ref Codigo);
		return Codigo;
	}

	public void ordernarMasUsadas()
	{
		BD.ConsultaModificar("FactElectUnidadesMedidas", "Orden=0", "codigo in (5,26, 58, 97, 47, 62)");
	}

	public int EliminarTodo()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("FactElectUnidadesMedidas", "1=1") == 0)
			{
				Interaction.MsgBox("no se puede eliminar, se encuentra en uso");
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
