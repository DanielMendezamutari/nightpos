using System.Data;

namespace ControlConsumoLib;

public class ctlPersonasSinMesa
{
	private readonly clsPersonasSinMesa clsPer;

	public ctlPersonasSinMesa()
	{
		clsPer = new clsPersonasSinMesa();
	}

	public int GetPersonaSinMesaID()
	{
		return clsPer._PersonaSinMesaID;
	}

	public void SetPersonaSinMesaID(int ID)
	{
		clsPer._PersonaSinMesaID = ID;
	}

	public clsPersonasSinMesa LlenarClase()
	{
		clsPer.llenarclase();
		return clsPer;
	}

	public DataTable devolverPersonasSinMesa(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsPer.Devolver();
		}
		return clsPer.Devolver(search, field);
	}

	public DataTable devolverPersonasSinMesa()
	{
		return clsPer.Devolver();
	}

	public void GuardarPersonaSinMesa(string NombreFamilia, int CantidadPersonas)
	{
		clsPer._NombreFamilia = NombreFamilia;
		clsPer._CantidadPersonas = CantidadPersonas;
		if (clsPer._PersonaSinMesaID == 0)
		{
			clsPer.Insertar();
		}
		else
		{
			clsPer.Modificar();
		}
	}

	public void EliminarPersonaSinMesa()
	{
		clsPer.Eliminar();
	}
}
