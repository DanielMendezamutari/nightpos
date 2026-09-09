using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsVentasNoSincronizadas
{
	private int VentaNoSincronizadaID;

	private int VisitaID;

	private string Observacion;

	private int FacturaID;

	public int _VentaNoSincronizadaID
	{
		get
		{
			return VentaNoSincronizadaID;
		}
		set
		{
			VentaNoSincronizadaID = value;
		}
	}

	public int _VisitaID
	{
		get
		{
			return VisitaID;
		}
		set
		{
			VisitaID = value;
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

	public int _FacturID
	{
		get
		{
			return FacturaID;
		}
		set
		{
			FacturaID = value;
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("*", "VentasNoSincronizadas", "Observacion is null or Observacion like ''");
	}

	public int ModificarObs()
	{
		int result;
		try
		{
			BD.ConsultaModificar("VentasNoSincronizadas", "Observacion='" + Observacion.Replace("'", "*") + "'", "VentaNoSincronizadaID=" + VentaNoSincronizadaID);
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

	public int ModificarFacturaID()
	{
		int result;
		try
		{
			BD.ConsultaModificar("VentasNoSincronizadas", "FacturaID=" + Conversions.ToString(FacturaID), "VentaNoSincronizadaID=" + VentaNoSincronizadaID);
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
			BD.ConsultaInsertar3(string.Concat(Conversions.ToString(VisitaID) + ",", Conversions.ToString(FacturaID), ",'", Observacion, "'"), "VentasNoSincronizadas(VisitaID,FacturaID,Observacion)", ref VentaNoSincronizadaID);
			result = VentaNoSincronizadaID;
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
			if (BD.ConsultaEliminar("VentasNoSincronizadas", "VentaNoSincronizadaID = " + VentaNoSincronizadaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Almacen, se encuentra en uso");
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

	public DataTable DevolverTodosVentasNoSincronizadas()
	{
		return BD.ConsultaVer("VentasNoSincronizadas.VentaNoSincronizadaID,VentasNoSincronizadas.VisitaID,VentasNoSincronizadas.Observacion,VentasNoSincronizadas.IP,VentasNoSincronizadas.Instancia,VentasNoSincronizadas.Usuario,VentasNoSincronizadas.Pass,VentasNoSincronizadas.Interno", "VentasNoSincronizadas");
	}

	public int ExisteVisitaID()
	{
		DataTable dataTable = BD.ConsultaVer("VentaNoSincronizadaID", "VentasNoSincronizadas", "VisitaID=" + Conversions.ToString(VisitaID));
		if (dataTable.Rows.Count > 0)
		{
			VentaNoSincronizadaID = Conversions.ToInteger(dataTable.Rows[0][0]);
			return VentaNoSincronizadaID;
		}
		return 0;
	}
}
