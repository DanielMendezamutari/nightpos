using System.Data;

namespace ControlConsumoLib;

public class ctlTiposErrores
{
	private readonly clsTiposErrores clsDesc;

	public ctlTiposErrores()
	{
		clsDesc = new clsTiposErrores();
	}

	public int GetTipoErrorID()
	{
		return clsDesc._TipoErrorID;
	}

	public void SetTipoErrorID(int ID)
	{
		clsDesc._TipoErrorID = ID;
	}

	public DataTable DevolverTiposErrores()
	{
		return clsDesc.Devolver();
	}

	public void GuardarTiposErrores(string Descripcion, bool Activo)
	{
		clsDesc._Descripcion = Descripcion;
		clsDesc._Activo = Activo;
		if (clsDesc._TipoErrorID == 0)
		{
			clsDesc.Insertar();
		}
		else
		{
			clsDesc.Modificar();
		}
	}

	public void EliminarTiposErrores()
	{
		clsDesc.Eliminar();
	}

	public DataTable DevolverTiposErroresCombo()
	{
		return clsDesc.DevolverTiposErrores();
	}
}
