using System;
using System.Data;
using System.Net;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElectProductosServicios
{
	private string Codigo1;

	private string Descripcion;

	private string CodigoActividad1;

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

	public string _codigoActividad1
	{
		get
		{
			return CodigoActividad1;
		}
		set
		{
			CodigoActividad1 = value;
		}
	}

	public clsFactElectProductosServicios()
	{
		ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
	}

	public DataTable DevolverXActividad(string Actividad)
	{
		return BD.ConsultaVer("FactElectProductosServicios.Codigo,  Descripcion", "FactElectProductosServicios ", "CodigoActividad='" + Actividad + "'");
	}

	public DataTable DevolverXActividadConCodigo(string Actividad)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Codigo, str(Codigo) + ' - ' + Descripcion", "FactElectProductosServicios ", "CodigoActividad='" + Actividad + "'");
		}
		return BD.ConsultaVer("Codigo, CAST(Codigo as varchar(16)) + '-' + Descripcion", "FactElectProductosServicios ", "CodigoActividad= '" + Actividad + "'");
	}

	public int Insertar()
	{
		if (Descripcion.Length > 255)
		{
			Descripcion = Descripcion.Substring(0, 249);
		}
		string data = "'" + Codigo1 + "','" + CodigoActividad1 + "','" + Descripcion + "'," + Conversions.ToString(VariableGeneral.gConfiguracionID);
		ref string codigo = ref Codigo1;
		int id = Conversions.ToInteger(codigo);
		BD.ConsultaInsertar3(data, "FactElectProductosServicios(Codigo,codigoActividad,Descripcion,ConfiguracionID)", ref id);
		codigo = Conversions.ToString(id);
		return Conversions.ToInteger(Codigo1);
	}

	public int EliminarTodo()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("FactElectProductosServicios", "ConfiguracionID = " + VariableGeneral.gConfiguracionID) == 0)
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
