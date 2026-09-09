using System;
using System.Data;
using ConfigToptech;

namespace ControlConsumoLib;

public class ctlDetalleProduccion
{
	private readonly clsDetallesProduccion clsDet;

	public ctlDetalleProduccion()
	{
		clsDet = new clsDetallesProduccion();
	}

	public int GetDetalleProduccionID()
	{
		return clsDet._DetalleProduccionID;
	}

	public void SetDetalleProduccionID(int ID)
	{
		clsDet._DetalleProduccionID = ID;
	}

	public clsDetallesProduccion LlenarClase()
	{
		clsDet.llenarclase();
		return clsDet;
	}

	public DataTable devolverDetallesProduccion(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsDet.Devolver();
		}
		return clsDet.Devolver(search, field);
	}

	public DataTable devolverDetallesProduccion()
	{
		return clsDet.Devolver();
	}

	public DataTable devolverDetallesProduccionXProduccionID(int ProduccionID)
	{
		clsDet._ProduccionID = ProduccionID;
		return clsDet.DevolverXProduccionID();
	}

	public void GuardarDetalleProduccion(double Producida, string Observacion, int ProduccionID, int ProductoID, double Eliminada, double Reciclada, bool insertando, bool permiteEliminar, int almacenID)
	{
		clsDet._Observacion = Observacion;
		clsDet._ProduccionID = ProduccionID;
		clsDet._ProductoID = ProductoID;
		clsDet._Producida = Producida;
		clsDet._Eliminada = Eliminada;
		clsDet._Reciclada = Reciclada;
		if (clsDet._DetalleProduccionID == 0)
		{
			clsDet.Insertar();
		}
		else
		{
			clsDet.Modificar();
		}
		float num = 0f;
		if (insertando)
		{
			if (!(Producida > 0.0))
			{
				return;
			}
			ctlProductos ctlProductos2 = new ctlProductos();
			ctlProductos2.SetProductoID(ProductoID);
			ctlProductos2.cargarDatos(almacenID);
			ctlProductos ctlProductos3 = new ctlProductos();
			ctlProductos3.SetTipoProductoID(ctlProductos2.getProductoTipoProductoID());
			ctlProductos3.LlenarClaseTiposProductos();
			if (ctlProductos3.GetTipoProductoManejaStock() == 0)
			{
				return;
			}
			if (new ctlPreparaciones().devolverPreparacionesParaProducto(ctlProductos2.GetProductoID(), 0).Rows.Count > 0)
			{
				ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.CasaCuina)
				{
					ctlProductos ctlProd = ctlProductos2;
					double pago = 0.0;
					double debe = 0.0;
					double Costo = num;
					ctlDetalleCuenta2.reducirStock(ctlProd, ParaLlevar: false, ref Producida, ref pago, ref debe, deCombo: false, telefono: false, ref Costo, ProduccionID, almacenID, desdeReporte: false, soyCombo: false);
					num = (float)Costo;
				}
				else
				{
					ctlProductos ctlProd2 = ctlProductos2;
					double Costo = 0.0;
					double debe = 0.0;
					double pago = num;
					ctlDetalleCuenta2.reducirStock(ctlProd2, ParaLlevar: false, ref Producida, ref Costo, ref debe, deCombo: false, telefono: false, ref pago, ProduccionID, ctlProductos3.GetTipoProductoAlmacenID(), desdeReporte: false, soyCombo: false);
					num = (float)pago;
				}
			}
			else
			{
				num = (float)(ctlProductos2.getCosto() * Producida);
			}
			ctlProductos2 = new ctlProductos();
			ctlProductos2.SetProductoID(ProductoID);
			ctlProductos2.ModificarCostoProduccion(Producida, (double)num / Producida, almacenID);
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PastaMadre)
			{
				ctlProductos2.modificarStock(Producida - Eliminada + Reciclada, 0, clsDet._DetalleProduccionID, 0, almacenID);
			}
			else
			{
				ctlProductos2.modificarStock(Producida, 0, clsDet._DetalleProduccionID, 0, almacenID);
			}
		}
		else if (permiteEliminar & (Eliminada > 0.0))
		{
			ctlProductos obj = new ctlProductos();
			obj.SetProductoID(ProductoID);
			obj.modificarStock(Eliminada * -1.0, 0, clsDet._DetalleProduccionID, 0, almacenID);
		}
	}

	public void EliminarDetalleProduccion()
	{
		clsDet.Eliminar();
	}

	public DataTable DevolverReporteProduccion(DateTime fechaIni, DateTime fechaFin)
	{
		return clsDet.DevolverReporteProduccion(fechaIni, fechaFin);
	}
}
