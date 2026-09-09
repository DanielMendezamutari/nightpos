using System.Data;
using Microsoft.VisualBasic;

namespace ControlConsumoLib;

public class ctlDetallesAjustes
{
	private readonly clsDetallesAjustes clsDet;

	public ctlDetallesAjustes()
	{
		clsDet = new clsDetallesAjustes();
	}

	public int GetDetalleAjusteID()
	{
		return clsDet._DetalleAjusteID;
	}

	public void SetDetalleAjusteID(int ID)
	{
		clsDet._DetalleAjusteID = ID;
	}

	public clsDetallesAjustes LlenarClase()
	{
		clsDet.llenarclase();
		return clsDet;
	}

	public DataTable devolverDetallesAjustes(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsDet.Devolver();
		}
		return clsDet.Devolver(search, field);
	}

	public DataTable devolverDetallesAjustes()
	{
		return clsDet.Devolver();
	}

	public DataTable devolverDetallesAjustesXAjusteID(int AjusteID, int AlmacenID)
	{
		clsDet._AjusteID = AjusteID;
		return clsDet.DevolverXAjusteID(AlmacenID);
	}

	public void GuardarDetalleAjuste(double Cantidad, string Observacion, int AjusteID, int ProductoID, double CantidadFinal, double CantidadInicial, double Costo, int almacenID, double costoBruto)
	{
		clsDet._Cantidad = Cantidad;
		Observacion = Observacion.Replace("'", "`");
		if (Observacion.Length > 500)
		{
			Observacion = Observacion.Substring(0, 500);
		}
		clsDet._Observacion = Observacion;
		clsDet._AjusteID = AjusteID;
		clsDet._ProductoID = ProductoID;
		clsDet._Costo = Costo;
		clsDet._CostoBruto = costoBruto;
		clsDet._CantidadFinal = CantidadFinal;
		if (clsDet._DetalleAjusteID == 0)
		{
			clsDet.Insertar();
			if ((CantidadFinal != CantidadInicial) | (Observacion.Length > 0))
			{
				ctlProductos obj = new ctlProductos();
				obj.SetProductoID(ProductoID);
				obj.modificarStock(Cantidad, 0, 0, 0, almacenID);
			}
		}
		else
		{
			Interaction.MsgBox("modificar???");
		}
	}

	public void EliminarDetalleAjuste()
	{
		clsDet.Eliminar();
	}

	public void EliminarDetalleAjustexProductoID(int prodId)
	{
		clsDet._ProductoID = prodId;
		clsDet.EliminarxpProdID();
	}

	public void DevolverDetalleAjuste(ref DataSet data, string DatNombre, int idAjuste)
	{
		clsDet._AjusteID = idAjuste;
		clsDet.DevolverDetalleAjuste(ref data, DatNombre);
	}
}
