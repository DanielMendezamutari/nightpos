using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsPersonasSinMesa
{
	private int PersonaSinMesaID;

	private string NombreFamilia;

	private int CantidadPersonas;

	public int _PersonaSinMesaID
	{
		get
		{
			return PersonaSinMesaID;
		}
		set
		{
			PersonaSinMesaID = value;
		}
	}

	public string _NombreFamilia
	{
		get
		{
			return NombreFamilia;
		}
		set
		{
			NombreFamilia = value;
		}
	}

	public int _CantidadPersonas
	{
		get
		{
			return CantidadPersonas;
		}
		set
		{
			CantidadPersonas = value;
		}
	}

	public clsPersonasSinMesa()
	{
		NombreFamilia = "";
		CantidadPersonas = 0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "PersonasSinMesa", " PersonaSinMesaID=" + PersonaSinMesaID);
		if (dataTable.Rows.Count > 0)
		{
			PersonaSinMesaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PersonaSinMesaID"])) ? ((object)0) : dataTable.Rows[0]["PersonaSinMesaID"]);
			NombreFamilia = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFamilia"])) ? "" : dataTable.Rows[0]["NombreFamilia"]);
			CantidadPersonas = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadPersonas"])) ? ((object)0) : dataTable.Rows[0]["CantidadPersonas"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("PersonasSinMesa.PersonaSinMesaID,PersonasSinMesa.NombreFamilia,PersonasSinMesa.CantidadPersonas", "PersonasSinMesa");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select PersonasSinMesa.PersonaSinMesaID,PersonasSinMesa.NombreFamilia,PersonasSinMesa.CantidadPersonas", "PersonasSinMesa) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("PersonasSinMesa", "NombreFamilia='" + NombreFamilia + "',CantidadPersonas=" + CantidadPersonas + ",flagSync=NULL", "PersonaSinMesaID=" + PersonaSinMesaID);
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
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				PersonaSinMesaID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(PersonaSinMesaID)", "PersonasSinMesa").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(Conversions.ToString(PersonaSinMesaID) + ",'" + NombreFamilia + "',", CantidadPersonas.ToString()) ?? "", "PersonasSinMesa(PersonaSinMesaID,NombreFamilia,CantidadPersonas)");
				PersonaSinMesaID = Conversions.ToInteger(BD.ConsultaVer("max(PersonaSinMesaID)", "PersonasSinMesa").Rows[0][0]);
				result = PersonaSinMesaID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat("'" + NombreFamilia + "',", CantidadPersonas.ToString()) ?? "", "PersonasSinMesa(NombreFamilia,CantidadPersonas)", ref PersonaSinMesaID);
				result = PersonaSinMesaID;
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

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("PersonasSinMesa", "PersonaSinMesaID = " + PersonaSinMesaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar PersonaSinMesa, se encuentra en uso");
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
