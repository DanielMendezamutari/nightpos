using System.Data;

namespace ControlConsumoLib;

public class ctlPreparaciones
{
	private readonly clsPreparaciones clsPre;

	public ctlPreparaciones()
	{
		clsPre = new clsPreparaciones();
	}

	public int GetPreparacionID()
	{
		return clsPre._PreparacionID;
	}

	public void SetPreparacionID(int ID)
	{
		clsPre._PreparacionID = ID;
	}

	public clsPreparaciones LlenarClase()
	{
		clsPre.llenarclase();
		return clsPre;
	}

	public bool tieneComboParaTipoProducto(int CategoriaId)
	{
		clsPre._paraCategoriaID = CategoriaId;
		return clsPre.tieneComboParaTipoProducto();
	}

	public DataTable devolverPreparacionesParaProducto(int paraID, int paraCategoriaId)
	{
		clsPre._paraProductoID1 = paraID;
		clsPre._paraCategoriaID = paraCategoriaId;
		return clsPre.DevolverParaProducto();
	}

	public DataTable DevolverSiSoyUsadoComoPreparacion(int deID)
	{
		return clsPre.DevolverSiSoyUsadoComoPreparacion(deID);
	}

	public DataTable devolverPreparacionesParTodosProductos(bool combos, bool chbSoloHabilitadoVenta)
	{
		return clsPre.devolverPreparacionesParTodosProductos(combos, chbSoloHabilitadoVenta);
	}

	public DataTable devolverPreparacionesParaTodosProductosSubReceta()
	{
		return clsPre.devolverPreparacionesParaTodosProductosSubReceta();
	}

	public void GuardarPreparacion(double cantidad, string concepto, int ParaProductoID, int paraCategoriaID, bool PuedeDisminuir, BD_SQL bd1 = null)
	{
		clsPre._cantidad = cantidad;
		clsPre._Concepto = concepto;
		clsPre._paraProductoID1 = ParaProductoID;
		clsPre._paraCategoriaID = paraCategoriaID;
		clsPre._PuedeDisminuir = PuedeDisminuir;
		if (clsPre._PreparacionID == 0)
		{
			clsPre.Insertar(bd1);
		}
		else
		{
			clsPre.Modificar();
		}
	}

	public void EliminarPreparacion()
	{
		new ctlPreparacionesComodines().EliminarPreparacionesID(clsPre._PreparacionID);
		clsPre.Eliminar();
	}
}
