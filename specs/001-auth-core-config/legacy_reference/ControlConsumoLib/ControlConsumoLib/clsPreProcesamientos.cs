using System;
using System.Data;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsPreProcesamientos
{
	private int PreprocesamientoID;

	private DateTime Fecha;

	private string Observacion;

	private int UsuarioID;

	private int AlmacenID;

	public int _PreprocesamientoID
	{
		get
		{
			return PreprocesamientoID;
		}
		set
		{
			PreprocesamientoID = value;
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

	public int _UsuarioID
	{
		get
		{
			return UsuarioID;
		}
		set
		{
			UsuarioID = value;
		}
	}

	public int _AlmacenID
	{
		get
		{
			return AlmacenID;
		}
		set
		{
			AlmacenID = value;
		}
	}

	public DataTable devolver()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("PreProcesamientos.PreprocesamientoID , PreProcesamientos.Fecha , PreProcesamientos.Observacion,PreProcesamientos.UsuarioID,PreProcesamientos.AlmacenID", "PreProcesamientos");
		}
		return BD.ConsultaVer("PreProcesamientos.PreprocesamientoID , PreProcesamientos.Fecha , PreProcesamientos.Observacion,PreProcesamientos.UsuarioID,PreProcesamientos.AlmacenID", "PreProcesamientos");
	}

	public DataTable devolver(string search, string field)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("* from (select PreProcesamientos.PreprocesamientoID , PreProcesamientos.Fecha , PreProcesamientos.Observacion,Preprocesamiento.UsuarioID,PreProcesamientos.AlmacenID", "PreProcesamientos) as tab1", (field + " " + (field.Contains("as date") ? VariableGeneral.ArmarFecha(Conversions.ToDate(search)) : search)) ?? "");
		}
		return BD.ConsultaVer("* from (select PreProcesamientos.PreprocesamientoID , PreProcesamientos.Fecha , PreProcesamientos.Observacion,Preprocesamiento.UsuarioID,PreProcesamientos.AlmacenID", "PreProcesamientos) as tab1", (field + " " + (field.Contains("as date") ? VariableGeneral.ArmarFecha(Conversions.ToDate(search)) : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("PreProcesamientos", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",Observacion='" + Observacion + "',UsuarioID=" + UsuarioID + ",AlmacenID=" + AlmacenID + ",flagSync=NULL", "PreprocesamientoID = " + PreprocesamientoID);
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
			BD.ConsultaInsertar3(string.Concat(string.Concat(VariableGeneral.ArmarFecha(Fecha) + ",'" + Observacion + "',", Conversions.ToString(UsuarioID), ","), Conversions.ToString(AlmacenID)) ?? "", "PreProcesamientos(Fecha,Observacion,UsuarioID,AlmacenID)", ref PreprocesamientoID);
			result = PreprocesamientoID;
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
			if (BD.ConsultaEliminar("PreProcesamientos", "PreprocesamientoID = " + PreprocesamientoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Cuenta, se encuentra en uso");
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
