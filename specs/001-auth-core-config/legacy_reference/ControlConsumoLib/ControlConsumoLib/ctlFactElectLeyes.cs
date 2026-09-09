namespace ControlConsumoLib;

public class ctlFactElectLeyes
{
	private readonly clsFactElectLeyes clsFact;

	public ctlFactElectLeyes()
	{
		clsFact = new clsFactElectLeyes();
	}

	public string GetCodigo()
	{
		return clsFact._Codigo1;
	}

	public void SetCodigo(string ID)
	{
		clsFact._Codigo1 = ID;
	}

	public void DevolverRandomLey(string codigo, ref int leyId, ref string Ley, int CodigoAmbiente)
	{
		clsFact._Codigo1 = codigo;
		clsFact.DevolverRandomLey(CodigoAmbiente);
		leyId = clsFact._ID;
		Ley = clsFact._Descripcion;
	}

	public int getMAxId()
	{
		return clsFact.getMAxId();
	}

	public void DevolverLey(int leyId, ref string Ley)
	{
		if (leyId > 0)
		{
			clsFact._ID = leyId;
			clsFact.DevolverLey();
			Ley = clsFact._Descripcion;
		}
		else
		{
			Ley = "";
		}
	}

	public void GuardarFactElectLeyes1(int id, string Codigo, string Descripcion)
	{
		clsFact._ID = id;
		clsFact._Codigo1 = Codigo;
		clsFact._Descripcion = Descripcion;
		clsFact.Insertar();
	}

	public void EliminarFactElectLeyes()
	{
		clsFact.EliminarTodo();
	}
}
