using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlDetalleProductosCompra
{
	private readonly clsDetalleProductosCompra clsDet;

	public ctlDetalleProductosCompra()
	{
		clsDet = new clsDetalleProductosCompra();
	}

	public int getDetalleProductoCompraID()
	{
		return clsDet._DetalleProductoCompraID;
	}

	public void SetDetalleProductoCompraID(int ID)
	{
		clsDet._DetalleProductoCompraID = ID;
	}

	public int getProductoIDdeCompra()
	{
		clsDet.cargarProductoIDdeCompra();
		return clsDet._ProductoID;
	}

	public DataTable devolverDetalleProductosCompra()
	{
		return clsDet.Devolver();
	}

	public DataTable devolverDetalleProductosCompraXcompraID(object compraID)
	{
		clsDet._CompraID = Conversions.ToInteger(compraID);
		return clsDet.devolverDetalleProductosCompraXcompraID();
	}

	public DateTime devolverMaxFechaRecepcionXcompraID(object compraID)
	{
		DateTime result;
		try
		{
			clsDet._CompraID = Conversions.ToInteger(compraID);
			result = Conversions.ToDate(clsDet.devolverMaxFechaRecepcionXcompraID().Rows[0][0]);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = DateAndTime.Now;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public void GuardarDetalleProductoCompra(DateTime PosibleFechaEntrega, DateTime FechaEntrega, bool PosibleFechaEntregaCh, bool FechaEntregaCh, double Cantidad, double CostoUnitario, int CompraID, int ProductoID, double CostoBruto)
	{
		clsDet._PosibleFechaEntrega = PosibleFechaEntrega;
		clsDet._FechaEntrega = FechaEntrega;
		clsDet._PosibleFechaEntregaCh = PosibleFechaEntregaCh;
		clsDet._FechaEntregaCh = FechaEntregaCh;
		clsDet._Cantidad = Cantidad;
		clsDet._CostoUnitario = CostoUnitario;
		clsDet._CompraID = CompraID;
		clsDet._ProductoID = ProductoID;
		clsDet._CostoBruto = CostoBruto;
		if (clsDet._DetalleProductoCompraID == 0)
		{
			clsDet.Insertar();
		}
		else
		{
			clsDet.Modificar();
		}
	}

	public void EliminarDetalleProductoCompra()
	{
		clsDet.Eliminar();
	}

	public DataTable devolverReporteProductosCompraxfecha(DateTime fechaI, DateTime fechaF, int almacenID)
	{
		return clsDet.devolverReporteProductosCompraxfecha(fechaI, fechaF, almacenID);
	}

	public DataTable devolverReporteProductosCompraxfechaXcompraID(int compraID)
	{
		clsDet._CompraID = compraID;
		return clsDet.devolverReporteProductosCompraxfechaXcompraID();
	}

	public DataTable devolverDetalleProvePgDeuda(int idProveedor, int idAlmacen)
	{
		return clsDet.devolverDetalleProveedorPgDeuda(idProveedor, idAlmacen);
	}

	public DataTable devolverDetalleDeudasVencidas(int idProveedor, int idAlmacen)
	{
		return clsDet.devolverDetalleDeudasVencidas(idProveedor, idAlmacen);
	}

	public DataTable devolverPagosProgramados(int idProveedor, int idAlmacen, DateTime Desde, DateTime Hasta)
	{
		return clsDet.devolverPagosProgramados(idProveedor, idAlmacen, Desde, Hasta);
	}

	public DataTable devolverDetalleProveedorPgDeudaDetalle()
	{
		return clsDet.devolverDetalleProveedorPgDeudaCh();
	}

	public DataTable devolverDetalleCompraProducto(DateTime fechai, DateTime fechaF, int idCompra, int idProducto, int AlmacenID)
	{
		clsDet._CompraID = idCompra;
		clsDet._ProductoID = idProducto;
		return clsDet.devolverDetalleCompraProducto(fechai, fechaF, AlmacenID);
	}

	public DataTable devolverDetalleCompraProveedor(DateTime fechai, DateTime fechaF, int Proveedor, int idProducto, int almacenID)
	{
		clsDet._ProductoID = idProducto;
		return clsDet.devolverDetalleCompraProveedor(fechai, fechaF, Proveedor, almacenID);
	}

	public DataTable devolverGastosSalidaProgramada(DateTime FechaSI, DateTime FechaSF, DateTime FechaPI, DateTime FechaPF)
	{
		return clsDet.devolverGastosSalidaProgramada(FechaSI, FechaSF, FechaPI, FechaPF);
	}

	public void DevolverDataSetComproGastos(ref DataSet data, string DatNombre, int compraID)
	{
		clsDet._CompraID = compraID;
		clsDet.DevolverDataSetComproGastos(ref data, DatNombre);
	}

	public int DevolverCompraExistente(int idCompra, int idProducto)
	{
		clsDet._CompraID = idCompra;
		clsDet._ProductoID = idProducto;
		return clsDet.DevolverCompraExistente();
	}

	public void ActualizarCostos(double CostoUnitario, double CostoBruto)
	{
		clsDet._CostoUnitario = CostoUnitario;
		clsDet._CostoBruto = CostoBruto;
		clsDet.ActualizarCostos();
	}
}
