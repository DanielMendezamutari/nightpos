using System.Data;

namespace ControlConsumoLib;

public class ctlTiposProductosPY
{
	private readonly clsTiposProductosPY clsTip;

	public ctlTiposProductosPY()
	{
		clsTip = new clsTiposProductosPY();
	}

	public int GetTipoProductoPYid()
	{
		return clsTip._TipoProductoPYid;
	}

	public void SetTipoProductoPYid(int ID)
	{
		clsTip._TipoProductoPYid = ID;
	}

	public DataTable devolverTiposProductosPY()
	{
		return clsTip.Devolver();
	}

	public string devolverTiposProductosPYporID()
	{
		return clsTip.devolverTiposProductosPYporID();
	}

	public void GuardarTiposProductosPY(string SKU, string Nombre, int Orden)
	{
		clsTip._SKU = SKU;
		clsTip._Nombre = Nombre;
		clsTip._Orden = Orden;
		if (clsTip._TipoProductoPYid == 0)
		{
			clsTip.Insertar();
		}
		else
		{
			clsTip.Modificar();
		}
	}

	public void EliminarTiposProductosPY()
	{
		clsTip.Eliminar();
	}
}
