using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlCategorias_Almacenes
{
	private readonly clsCategorias_Almacenes clsCatAlmacen;

	private readonly clsAlmacenes clsAlm;

	public ctlCategorias_Almacenes()
	{
		clsCatAlmacen = new clsCategorias_Almacenes();
		clsAlm = new clsAlmacenes();
	}

	public int GetcategoriaAlmacenID()
	{
		return clsCatAlmacen._CategoriaID;
	}

	public void SetcategoriaAlmacenID(int ID)
	{
		clsCatAlmacen._categoriaAlmacenID = ID;
	}

	public int devolverAlmacenIDOverride(int AlmacenDefault, int tipoProductoId, int mesaId)
	{
		clsCatAlmacen._CategoriaID = tipoProductoId;
		DataTable dataTable = clsCatAlmacen.devolverAlmacenOverride(mesaId);
		if (dataTable.Rows.Count == 0)
		{
			return AlmacenDefault;
		}
		if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0), 0, TextCompare: false))
		{
			return AlmacenDefault;
		}
		return Conversions.ToInteger(dataTable.Rows[0][0]);
	}

	public void GuardarCategorias_Almacenes(int salon, int Categoria, int Almacen)
	{
		clsCatAlmacen._SalonID = salon;
		clsCatAlmacen._CategoriaID = Categoria;
		clsCatAlmacen._AlmacenID = Almacen;
		if (clsCatAlmacen._categoriaAlmacenID == 0)
		{
			clsCatAlmacen.Insertar();
		}
		else
		{
			clsCatAlmacen.Modificar();
		}
	}

	public void EliminarCategorias_Almacenes()
	{
		clsCatAlmacen.Eliminar();
	}

	public DataTable DevolverTodosAlmacenesInternos(int meseroID)
	{
		clsAlm._ResponsableID = meseroID;
		return clsAlm.DevolverTodosAlmacenesInternos();
	}

	public DataTable devolverCategorias_Almacenes(int id)
	{
		clsCatAlmacen._SalonID = id;
		return clsCatAlmacen.Devolver();
	}
}
