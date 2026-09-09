using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlCategorias_Impresoras
{
	private readonly clsCategorias_Impresoras clsCatImpresora;

	private readonly clsImpresoras clsImp;

	public ctlCategorias_Impresoras()
	{
		clsCatImpresora = new clsCategorias_Impresoras();
		clsImp = new clsImpresoras();
	}

	public int GetcategoriaImpresoraID()
	{
		return clsCatImpresora._CategoriaID;
	}

	public void SetcategoriaImpresoraID(int ID)
	{
		clsCatImpresora._categoriaImpresoraID = ID;
	}

	public string devolverImpresoraOverride(string impresoraDefault, int tipoProductoId, int mesaId)
	{
		clsCatImpresora._CategoriaID = tipoProductoId;
		DataTable dataTable = clsCatImpresora.devolverImpresoraOverride(mesaId);
		if (dataTable.Rows.Count == 0)
		{
			return impresoraDefault;
		}
		if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), ""), "", TextCompare: false))
		{
			return "";
		}
		return Conversions.ToString(dataTable.Rows[0][0]);
	}

	public void GuardarCategorias_Impresoras(int salon, int Categoria, int Impresora)
	{
		clsCatImpresora._SalonID = salon;
		clsCatImpresora._CategoriaID = Categoria;
		clsCatImpresora._ImpresoraID = Impresora;
		if (clsCatImpresora._categoriaImpresoraID == 0)
		{
			clsCatImpresora.Insertar();
		}
		else
		{
			clsCatImpresora.Modificar();
		}
	}

	public void EliminarCategorias_Impresoras()
	{
		clsCatImpresora.Eliminar();
	}

	public DataTable devolverImpresorasPorDescripcion()
	{
		return clsImp.devolverImpresorasPorDescripcion();
	}

	public DataTable devolverCategorias_Impresoras(int id)
	{
		clsCatImpresora._SalonID = id;
		return clsCatImpresora.Devolver();
	}
}
