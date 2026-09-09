using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFamilia
{
	private int FamiliaID;

	private string Descripcion;

	private string Codigo;

	public int _FamiliaID
	{
		get
		{
			return FamiliaID;
		}
		set
		{
			FamiliaID = value;
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

	public string _Codigo
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

	public clsFamilia()
	{
		Descripcion = "";
		FamiliaID = 0;
		Codigo = "";
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Familias", " FamiliaID=" + FamiliaID);
		if (dataTable.Rows.Count > 0)
		{
			FamiliaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FamiliaID"])) ? ((object)0) : dataTable.Rows[0]["FamiliaID"]);
			Descripcion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"])) ? "" : dataTable.Rows[0]["Descripcion"]);
			Codigo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Codigo"])) ? "" : dataTable.Rows[0]["Codigo"]);
		}
	}

	public DataTable devolverFamiliaPorDescripcion1()
	{
		return BD.ConsultaVer("FamiliaID,Descripcion", "Familias", "", "Descripcion");
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Familias.FamiliaID,Familias.Descripcion ,Familias.Codigo ", "Familias  ");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (Familias.FamiliaID,Familias.Descripcion  ,Familias.Codigo  ", "Familias ) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public DataTable devolverTipoProductosPorDescripcion()
	{
		return BD.ConsultaVer("FamiliaID,Descripcion", "Familias");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Familias", "Descripcion='" + Descripcion + "',Codigo='" + Codigo + "'", "FamiliaID=" + FamiliaID);
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
			BD.ConsultaInsertar3("'" + Descripcion + "','" + Codigo + "'", "Familias(Descripcion,Codigo)", ref FamiliaID);
			result = FamiliaID;
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
			if (BD.ConsultaEliminar("Familias", "FamiliaID = " + FamiliaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Familias, se encuentra en uso");
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
