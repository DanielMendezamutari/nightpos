using System;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCuadroDeTexto
{
	private int ID;

	private string Campo;

	private string CampoValor;

	private string CampoID;

	private string Tabla;

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

	public string _Campo
	{
		get
		{
			return Campo;
		}
		set
		{
			Campo = value;
		}
	}

	public string _CampoValor
	{
		get
		{
			return CampoValor;
		}
		set
		{
			CampoValor = value;
		}
	}

	public string _CampoID
	{
		get
		{
			return CampoID;
		}
		set
		{
			CampoID = value;
		}
	}

	public string _Tabla
	{
		get
		{
			return Tabla;
		}
		set
		{
			Tabla = value;
		}
	}

	public object EsMio()
	{
		object result;
		try
		{
			result = ((configuration.gMODO_ACCESS == 1) ? Operators.CompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("count(*)", Tabla, "CStr(" + Campo + ") = '" + CampoValor + "' and CStr(" + CampoID + ") = " + ID).Rows[0][0]), 0), 0, TextCompare: false) : ((configuration.gMODO_ACCESS != 2) ? Operators.CompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("count(*)", Tabla, "cast(" + Campo + " as varchar) = '" + CampoValor + "' and cast(" + CampoID + "  as varchar) = " + ID).Rows[0][0]), 0), 0, TextCompare: false) : Operators.CompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("count(*)", Tabla, "cast(" + Campo + " as char) = '" + CampoValor + "' and cast(" + CampoID + "  as char) = " + ID).Rows[0][0]), 0), 0, TextCompare: false)));
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public object Validar()
	{
		object result;
		try
		{
			result = ((configuration.gMODO_ACCESS == 1) ? Operators.CompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("count(*)", Tabla, "CStr(" + Campo + ") = '" + CampoValor + "'").Rows[0][0]), 0), 0, TextCompare: false) : ((configuration.gMODO_ACCESS != 2) ? Operators.CompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("count(*)", Tabla, "convert( varchar(255), " + Campo + ") like '" + CampoValor + "'").Rows[0][0]), 0), 0, TextCompare: false) : Operators.CompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("count(*)", Tabla, "cast(" + Campo + " as char) = cast('" + CampoValor + "' as char)").Rows[0][0]), 0), 0, TextCompare: false)));
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}
}
