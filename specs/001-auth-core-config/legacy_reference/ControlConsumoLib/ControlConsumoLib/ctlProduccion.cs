using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlProduccion
{
	private readonly clsProduccion clsPro;

	public ctlProduccion()
	{
		clsPro = new clsProduccion();
	}

	public int GetProduccionID()
	{
		return clsPro._ProduccionID;
	}

	public void SetProduccionID(int ID)
	{
		clsPro._ProduccionID = ID;
	}

	public clsProduccion LlenarClase()
	{
		clsPro.llenarclase();
		return clsPro;
	}

	public DataTable devolverProduccion(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsPro.Devolver();
		}
		return clsPro.Devolver(search, field);
	}

	public DataTable devolverProduccion()
	{
		return clsPro.Devolver();
	}

	public void GuardarProduccion(DateTime Fecha, string Observacion, int almacenId)
	{
		clsPro._Fecha = Fecha;
		clsPro._Observacion = Observacion;
		clsPro._AlmacenId = almacenId;
		if (clsPro._ProduccionID == 0)
		{
			clsPro.Insertar();
		}
		else
		{
			clsPro.Modificar();
		}
	}

	public void EliminarProduccion()
	{
		clsPro.Eliminar();
	}
}
