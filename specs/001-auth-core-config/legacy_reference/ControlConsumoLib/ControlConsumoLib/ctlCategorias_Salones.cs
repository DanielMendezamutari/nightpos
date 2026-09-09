using System.Data;

namespace ControlConsumoLib;

public class ctlCategorias_Salones
{
	private readonly clsCategorias_Salones clsCatSalon;

	private clsSalones clsAlm;

	public ctlCategorias_Salones()
	{
		clsCatSalon = new clsCategorias_Salones();
		clsAlm = new clsSalones();
	}

	public int GetCategoriaSalonID()
	{
		return clsCatSalon._CategoriaID;
	}

	public void SetCategoriaSalonID(int ID)
	{
		clsCatSalon._CategoriaSalonID = ID;
	}

	public void GuardarCategorias_Salones(int salon, int Categoria)
	{
		clsCatSalon._SalonID = salon;
		clsCatSalon._CategoriaID = Categoria;
		if (clsCatSalon._CategoriaSalonID == 0)
		{
			clsCatSalon.Insertar();
		}
		else
		{
			clsCatSalon.Modificar();
		}
	}

	public void EliminarCategorias_Salones()
	{
		clsCatSalon.Eliminar();
	}

	public DataTable devolverCategorias_Salones(int id)
	{
		clsCatSalon._SalonID = id;
		return clsCatSalon.Devolver();
	}
}
