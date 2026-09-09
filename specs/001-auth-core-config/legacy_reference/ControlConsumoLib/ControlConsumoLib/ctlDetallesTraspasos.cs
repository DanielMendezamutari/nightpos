using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlDetallesTraspasos
{
	private readonly clsDetallesTraspasos clsDet;

	public ctlDetallesTraspasos()
	{
		clsDet = new clsDetallesTraspasos();
	}

	public int GetDetalleTraspasoID()
	{
		return clsDet._DetalleTraspasoID;
	}

	public void SetDetalleTraspasoID(int ID)
	{
		clsDet._DetalleTraspasoID = ID;
	}

	public clsDetallesTraspasos LlenarClase()
	{
		clsDet.llenarclase();
		return clsDet;
	}

	public DataTable devolverDetallesTraspasos(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsDet.Devolver();
		}
		return clsDet.Devolver(search, field);
	}

	public DataTable devolverDetallesTraspasos()
	{
		return clsDet.Devolver();
	}

	public DataTable devolverDetallesTraspasosXTraspasoID(int TraspasoID, int almacenID)
	{
		clsDet._TraspasoID = TraspasoID;
		return clsDet.DevolverXTraspasoID(almacenID);
	}

	public void GuardarDetalleTraspaso(double Cantidad, string Observacion, int TraspasoID, int ProductoID, double CantidadFinal, double CantidadInicial, bool traspasando, double costo, int almacenID, int almacenID2, BD_SQL bd1 = null)
	{
		clsDet._Cantidad = Cantidad;
		clsDet._Observacion = Observacion;
		clsDet._TraspasoID = TraspasoID;
		clsDet._ProductoID = ProductoID;
		clsDet._Costos = costo;
		if (bd1 == null)
		{
			clsDet._CantidadFinal = CantidadFinal;
			if (clsDet._DetalleTraspasoID == 0)
			{
				clsDet.Insertar();
				if ((CantidadFinal != CantidadInicial) | (Observacion.Length > 0))
				{
					ctlProductos ctlProductos2 = new ctlProductos();
					ctlProductos2.SetProductoID(ProductoID);
					ctlAlmacenes obj = new ctlAlmacenes();
					obj.SetAlmacenID(almacenID2);
					DataTable dataTable = obj.DevolverMiData2();
					if (dataTable.Rows.Count > 0 && Conversions.ToBoolean(dataTable.Rows[0]["interno"]))
					{
						ctlProductos2.modificarStock(Cantidad, 0, 0, 0, almacenID2);
					}
					obj.SetAlmacenID(almacenID);
					dataTable = obj.DevolverMiData2();
					if (dataTable.Rows.Count > 0 && Conversions.ToBoolean(dataTable.Rows[0]["interno"]))
					{
						ctlProductos2.modificarStock(Cantidad * -1.0, 0, 0, 0, almacenID);
					}
				}
			}
			else
			{
				Interaction.MsgBox("modificar???");
			}
			return;
		}
		clsDet._CantidadFinal = 0.0;
		if (clsDet._DetalleTraspasoID == 0)
		{
			if (Cantidad > 0.0)
			{
				ctlProductos ctlProductos3 = new ctlProductos();
				ctlProductos3.ToReturnIdbyName(Observacion, bd1);
				if (ctlProductos3.GetProductoID() == 0)
				{
					Interaction.MsgBox("No se encontro el producto " + Observacion);
					return;
				}
				clsDet._Observacion = "";
				clsDet._ProductoID = ProductoID;
				clsDet._ProductoID = ctlProductos3.GetProductoID();
				clsDet.Insertar(bd1);
				int almacenId = 1;
				ctlProductos3.modificarCostoTraspaso(Cantidad, 0, costo, almacenId, bd1);
				ctlProductos3.modificarStock(Cantidad, 0, 0, 0, almacenID2, bd1);
			}
		}
		else
		{
			Interaction.MsgBox("modificar???");
		}
	}

	public void EliminarDetalleTraspaso()
	{
		clsDet.Eliminar();
	}

	public void EliminarDetalleTraspasoxProductoID(int prodId)
	{
		clsDet._ProductoID = prodId;
		clsDet.EliminarxpProdID();
	}

	public void DevolverDetalleTraspaso(ref DataSet data, string DatNombre, int id, int almacenID)
	{
		clsDet._TraspasoID = id;
		clsDet.DevolverDetalleTraspaso(ref data, DatNombre, almacenID);
	}
}
