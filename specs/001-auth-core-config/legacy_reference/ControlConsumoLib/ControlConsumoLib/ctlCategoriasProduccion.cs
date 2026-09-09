using System.Data;

namespace ControlConsumoLib;

public class ctlCategoriasProduccion
{
	private readonly clsCategoriasProduccion clsCat;

	public ctlCategoriasProduccion()
	{
		clsCat = new clsCategoriasProduccion();
	}

	public int GetCategoriaProduccionID()
	{
		return clsCat._CategoriaProduccionID;
	}

	public void SetCategoriaProduccionID(int ID)
	{
		clsCat._CategoriaProduccionID = ID;
	}

	public clsCategoriasProduccion LlenarClase()
	{
		clsCat.llenarclase();
		return clsCat;
	}

	public DataTable devolverCategoriasProduccion(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsCat.Devolver();
		}
		return clsCat.Devolver(search, field);
	}

	public DataTable devolverCategoriasProduccion()
	{
		return clsCat.Devolver();
	}

	public DataTable devolverCategoriasProduccionPorNombre()
	{
		return clsCat.devolverCategoriasProduccionPorNombre();
	}

	public void GuardarCategoriaProduccion(string Nombre)
	{
		clsCat._Nombre = Nombre;
		if (clsCat._CategoriaProduccionID == 0)
		{
			clsCat.Insertar();
		}
		else
		{
			clsCat.Modificar();
		}
	}

	public void EliminarCategoriaProduccion()
	{
		clsCat.Eliminar();
	}
}
