using System.Data;

namespace ControlConsumoLib;

public class ctlFactElectUnidadesMedidas
{
	private readonly clsFactElectUnidadesMedidas clsFact;

	public ctlFactElectUnidadesMedidas()
	{
		clsFact = new clsFactElectUnidadesMedidas();
	}

	public int GetCodigo()
	{
		return clsFact._Codigo;
	}

	public void SetCodigo(int ID)
	{
		clsFact._Codigo = ID;
	}

	public DataTable Devolver()
	{
		return clsFact.Devolver();
	}

	public DataTable DevolverConCodigo()
	{
		return clsFact.DevolverConCodigo();
	}

	public void GuardarFactElectUnidadesMedidas1(int Codigo, string Descripcion)
	{
		clsFact._Codigo = Codigo;
		clsFact._Descripcion = Descripcion;
		clsFact.Insertar();
	}

	public void ordernarMasUsadas()
	{
		clsFact.ordernarMasUsadas();
	}

	public void EliminarFactElectUnidadesMedidas()
	{
		clsFact.EliminarTodo();
	}
}
