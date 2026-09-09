using System.Data;

namespace ControlConsumoLib;

public class ctlObservacionesCocina
{
	private readonly clsObservacionesCocina clsObs;

	public ctlObservacionesCocina()
	{
		clsObs = new clsObservacionesCocina();
	}

	public int GetObservacionCocinaID()
	{
		return clsObs._ObservacionCocinaID;
	}

	public void SetObservacionCocinaID(int ID)
	{
		clsObs._ObservacionCocinaID = ID;
	}

	public DataTable devolverObservaciones()
	{
		return clsObs.Devolver();
	}

	public void GuardarObservacion(double Orden, string Descripcion)
	{
		clsObs._Orden = (float)Orden;
		clsObs._Descripcion = Descripcion;
		if (clsObs._ObservacionCocinaID == 0)
		{
			clsObs.Insertar();
		}
		else
		{
			clsObs.Modificar();
		}
	}

	public void EliminarObservacion()
	{
		clsObs.Eliminar();
	}
}
