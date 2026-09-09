using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsObservacionesCocina
{
	private int ObservacionCocinaID;

	private double Orden;

	private string Descripcion;

	public int _ObservacionCocinaID
	{
		get
		{
			return ObservacionCocinaID;
		}
		set
		{
			ObservacionCocinaID = value;
		}
	}

	public float _Orden
	{
		get
		{
			return (float)Orden;
		}
		set
		{
			Orden = value;
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

	public DataTable Devolver()
	{
		return BD.ConsultaVer("ObservacionesCocina.ObservacionCocinaID,ObservacionesCocina.Orden,ObservacionesCocina.Descripcion", "ObservacionesCocina", "", "ObservacionesCocina.Orden");
	}

	public int Insertar()
	{
		BD.ConsultaInsertar3(Conversion.Str(Orden) + ",'" + Descripcion + "'", "ObservacionesCocina(Orden,Descripcion)", ref ObservacionCocinaID);
		return ObservacionCocinaID;
	}

	public void Modificar()
	{
		BD.ConsultaModificar("ObservacionesCocina", "Orden=" + Conversion.Str(Orden) + ",Descripcion='" + Descripcion + "',flagSync=NULL", "ObservacionCocinaID= " + ObservacionCocinaID);
	}

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("ObservacionesCocina", "ObservacionCocinaID = " + ObservacionCocinaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar la Observacion, se encuentra en uso");
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
