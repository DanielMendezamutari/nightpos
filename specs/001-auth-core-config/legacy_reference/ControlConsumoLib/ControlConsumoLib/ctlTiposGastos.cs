using System.Data;

namespace ControlConsumoLib;

public class ctlTiposGastos
{
	private readonly clsTiposGastos clsTip;

	public ctlTiposGastos()
	{
		clsTip = new clsTiposGastos();
	}

	public int GetTipoGastoID()
	{
		return clsTip._TipoGastoID;
	}

	public void SetTipoGastoID(int ID)
	{
		clsTip._TipoGastoID = ID;
	}

	public DataTable devolverTiposGastos()
	{
		return clsTip.Devolver();
	}

	public DataTable devolverTiposGastosPorDescripcion()
	{
		return clsTip.devolverTiposGastosPorDescripcion();
	}

	public string devolverTiposGastosporID()
	{
		return clsTip.devolverTiposGastosporID();
	}

	public void GuardarTipoGasto(string Descripcion, string subtipo, bool activo, string FliaGastos)
	{
		clsTip._Descripcion = Descripcion;
		clsTip._SubTipo = subtipo;
		clsTip._Activo = activo;
		clsTip._FamiliaGasto = FliaGastos;
		if (clsTip._TipoGastoID == 0)
		{
			clsTip.Insertar();
		}
		else
		{
			clsTip.Modificar();
		}
	}

	public void EliminarTipoGasto()
	{
		clsTip.Eliminar();
	}

	public DataTable devolverSubTipos()
	{
		return clsTip.devolverSubTipo();
	}

	public DataTable devolverFamilia()
	{
		return clsTip.devolverFamilia();
	}

	public DataTable devolverTiposGastosPorDescripcionFiltrado(string subtipo)
	{
		clsTip._SubTipo = subtipo;
		return clsTip.devolverTiposGastosPorDescripcionFiltrado();
	}

	public DataTable devolverCategoriaPorFamilia(string familia)
	{
		clsTip._FamiliaGasto = familia;
		return clsTip.devolverCategoriaPorFamilia();
	}
}
