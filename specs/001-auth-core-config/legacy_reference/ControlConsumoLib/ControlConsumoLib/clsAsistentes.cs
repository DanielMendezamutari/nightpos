using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsAsistentes
{
	private int ID;

	private string NombreCorto;

	private string NombreCompleto;

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

	public string _NombreCorto
	{
		get
		{
			return NombreCorto;
		}
		set
		{
			NombreCorto = value;
		}
	}

	public string _NombreCompleto
	{
		get
		{
			return NombreCompleto;
		}
		set
		{
			NombreCompleto = value;
		}
	}

	public clsAsistentes()
	{
		NombreCorto = "";
		ID = 0;
		NombreCompleto = "";
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Asistentes", " ID=" + ID);
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"])) ? ((object)0) : dataTable.Rows[0]["ID"]);
			NombreCorto = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreCorto"])) ? "" : dataTable.Rows[0]["NombreCorto"]);
			NombreCompleto = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreCompleto"])) ? "" : dataTable.Rows[0]["NombreCompleto"]);
		}
	}

	public bool hayAsistentes()
	{
		return BD.ConsultaVer("Asistentes.ID,Asistentes.NombreCorto", "Asistentes", "1=1").Rows.Count > 0;
	}

	public DataTable DevolverXid()
	{
		return BD.ConsultaVer("Asistentes.ID,Asistentes.NombreCorto ", "Asistentes", "Asistentes.ID =" + ID);
	}

	public int Insertar()
	{
		int result;
		try
		{
			BD.ConsultaInsertar(Conversions.ToString(ID) + ",'" + NombreCorto + "','" + NombreCompleto + "'", "Asistentes(id,NombreCorto,NombreCompleto)");
			result = ID;
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
			if (BD.ConsultaEliminar("Asistentes", "ID = " + ID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Asistentes, se encuentra en uso");
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
