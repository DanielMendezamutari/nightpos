using System.Data;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlVentasNoSincronizadas
{
	private readonly clsVentasNoSincronizadas clsAlm;

	public ctlVentasNoSincronizadas()
	{
		clsAlm = new clsVentasNoSincronizadas();
	}

	public int GetVentaNoSincronizadaID()
	{
		return clsAlm._VentaNoSincronizadaID;
	}

	public void SetVentaNoSincronizadaID(int ID)
	{
		clsAlm._VentaNoSincronizadaID = ID;
	}

	public DataTable devolverVentasNoSincronizadas()
	{
		return clsAlm.Devolver();
	}

	public void GuardarVentas(string visitaID, string observacion, int facturaID)
	{
		clsAlm._VisitaID = Conversions.ToInteger(visitaID);
		clsAlm._Observacion = observacion;
		clsAlm._FacturID = facturaID;
		if (clsAlm._VentaNoSincronizadaID == 0)
		{
			clsAlm._VisitaID = Conversions.ToInteger(visitaID);
			if (clsAlm.ExisteVisitaID() > 0)
			{
				clsAlm.ModificarFacturaID();
			}
			else
			{
				clsAlm.Insertar();
			}
		}
		else
		{
			clsAlm.ModificarObs();
		}
	}

	public void EliminarVentasNoSincronizadas()
	{
		clsAlm.Eliminar();
	}
}
