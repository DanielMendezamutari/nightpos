using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsProduccion
{
	private int ProduccionID;

	private int AlmacenId;

	private DateTime Fecha;

	private string Observacion;

	public int _ProduccionID
	{
		get
		{
			return ProduccionID;
		}
		set
		{
			ProduccionID = value;
		}
	}

	public int _AlmacenId
	{
		get
		{
			return AlmacenId;
		}
		set
		{
			AlmacenId = value;
		}
	}

	public DateTime _Fecha
	{
		get
		{
			return Fecha;
		}
		set
		{
			Fecha = value;
		}
	}

	public string _Observacion
	{
		get
		{
			return Observacion;
		}
		set
		{
			Observacion = value;
		}
	}

	public clsProduccion()
	{
		Fecha = DateAndTime.Now;
		Observacion = "";
		AlmacenId = 0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Produccion", " ProduccionID=" + ProduccionID);
		if (dataTable.Rows.Count > 0)
		{
			ProduccionID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ProduccionID"])) ? ((object)0) : dataTable.Rows[0]["ProduccionID"]);
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			AlmacenId = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AlmacenId"])) ? ((object)0) : dataTable.Rows[0]["AlmacenId"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Produccion.ProduccionID,Produccion.Fecha,Produccion.Observacion,AlmacenId", "Produccion", "", "Produccion.Fecha desc");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select Produccion.ProduccionID,Produccion.Fecha,Produccion.Observacion,AlmacenId", "Produccion) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Produccion", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",AlmacenId=" + Conversions.ToString(AlmacenId) + ",Observacion='" + Observacion + "',flagSync=NULL", "ProduccionID=" + ProduccionID);
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
				ProduccionID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ProduccionID)", "Produccion").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(Conversions.ToString(ProduccionID) + "," + VariableGeneral.ArmarFecha(Fecha) + ",'" + Observacion + "'," + Conversions.ToString(AlmacenId), "Produccion(ProduccionID,Fecha,Observacion,AlmacenId)");
				result = ProduccionID;
			}
			else
			{
				BD.ConsultaInsertar3(VariableGeneral.ArmarFecha(Fecha) + ",'" + Observacion + "'," + Conversions.ToString(AlmacenId), "Produccion(Fecha,Observacion,AlmacenId)", ref ProduccionID);
				result = ProduccionID;
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
			if (BD.ConsultaEliminar("Produccion", "ProduccionID = " + ProduccionID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Produccion, se encuentra en uso");
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
