using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlCompras
{
	private readonly clsCompras clsCom;

	public ctlCompras()
	{
		clsCom = new clsCompras();
	}

	public int GetCompraID()
	{
		return clsCom._CompraID;
	}

	public void SetCompraID(int ID)
	{
		clsCom._CompraID = ID;
	}

	public int DevolverSgteNro()
	{
		return clsCom.DevolverSgteNro();
	}

	public DataTable devolverCompras(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsCom.Devolver();
		}
		return clsCom.Devolver(search, field);
	}

	public DataTable devolverComprasCodigos()
	{
		return clsCom.devolverComprasCodigos();
	}

	public DataTable devolverCompraReporte(DateTime desde, DateTime hasta, int clienteID, int almacenID)
	{
		clsCom._ProveedorID = clienteID;
		clsCom._AlmacenID = almacenID;
		if (clienteID == 0)
		{
			return clsCom.DevolverReporteTodos(desde, hasta);
		}
		return clsCom.DevolverReporte(desde, hasta);
	}

	public void GuardarCompra(int NroComprobante, DateTime FechaRecepcion, double ImporteCompra, string Comentarios, int ProveedorID, int MeseroID, int AlmacenID)
	{
		clsCom._NroComprobante = NroComprobante;
		clsCom._FechaRecepcion = FechaRecepcion;
		clsCom._ImporteCompra = ImporteCompra;
		clsCom._Comentarios = Comentarios;
		clsCom._ProveedorID = ProveedorID;
		clsCom._MeseroID = MeseroID;
		clsCom._AlmacenID = AlmacenID;
		if (clsCom._CompraID == 0)
		{
			clsCom.Insertar();
		}
		else
		{
			clsCom.Modificar();
		}
	}

	public void EliminarCompra()
	{
		clsCom.Eliminar();
	}

	public void DevolverDatosComprasPorComprasID(ref int Nro, ref DateTime fecha, ref string nombre, ref float Importe, int compraID, ref string observacion)
	{
		clsCom._CompraID = compraID;
		clsCom.DevolverDatosComprasPorComprasID(ref Nro, ref fecha, ref nombre, ref Importe, ref observacion);
	}

	public DataTable DevolverCompras1()
	{
		return clsCom.Devolver1();
	}

	public DataTable DevolverComprasParciales()
	{
		return clsCom.DevolverParciales();
	}

	public DataTable DevolverComprasDeudas()
	{
		return clsCom.DevolverDeudas();
	}

	public int DevolverMesero(int compraID)
	{
		clsCom._CompraID = compraID;
		return clsCom.DevolverMesero();
	}
}
