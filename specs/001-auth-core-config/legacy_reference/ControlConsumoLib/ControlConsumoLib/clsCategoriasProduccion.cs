using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCategoriasProduccion
{
	private int CategoriaProduccionID;

	private string Nombre;

	public int _CategoriaProduccionID
	{
		get
		{
			return CategoriaProduccionID;
		}
		set
		{
			CategoriaProduccionID = value;
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

	public clsCategoriasProduccion()
	{
		Nombre = "";
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "CategoriasProduccion", " CategoriaProduccionID=" + CategoriaProduccionID);
		if (dataTable.Rows.Count > 0)
		{
			CategoriaProduccionID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CategoriaProduccionID"])) ? ((object)0) : dataTable.Rows[0]["CategoriaProduccionID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
		}
	}

	public DataTable devolverCategoriasProduccionPorNombre()
	{
		return BD.ConsultaVer("CategoriaProduccionID,Nombre", "CategoriasProduccion", "", "Nombre");
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("CategoriasProduccion.CategoriaProduccionID,CategoriasProduccion.Nombre", "CategoriasProduccion");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select CategoriasProduccion.CategoriaProduccionID,CategoriasProduccion.Nombre", "CategoriasProduccion) as tab1", (field + " " + (field.Contains("as date") ? VariableGeneral.ArmarFecha(Conversions.ToDate(search)) : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("CategoriasProduccion", "Nombre='" + Nombre + "'", "CategoriaProduccionID=" + CategoriaProduccionID);
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
				BD.ConsultaInsertar("'" + Nombre + "'", "CategoriasProduccion(Nombre)");
				CategoriaProduccionID = Conversions.ToInteger(BD.ConsultaVer("max(CategoriaProduccionID)", "CategoriasProduccion").Rows[0][0]);
				result = CategoriaProduccionID;
			}
			else
			{
				BD.ConsultaInsertar3("'" + Nombre + "'", "CategoriasProduccion(Nombre)", ref CategoriaProduccionID);
				result = CategoriaProduccionID;
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
			if (BD.ConsultaEliminar("CategoriasProduccion", "CategoriaProduccionID = " + CategoriaProduccionID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar CategoriaProduccion, se encuentra en uso");
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
