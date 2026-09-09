using System;
using System.Data;

namespace ControlConsumoLib;

public class CtlGastos_Facturas
{
	private readonly clsGastos_Facturas clsGas;

	public CtlGastos_Facturas()
	{
		clsGas = new clsGastos_Facturas();
	}

	public int GetGastos_FacturaID()
	{
		return clsGas._Gastos_FacturaID;
	}

	public void SetGastos_FacturaID(int ID)
	{
		clsGas._Gastos_FacturaID = ID;
	}

	public void Guardar(string nit, string Nombre, int NroFactura, string NroAutorizacion, string Codigo, DateTime Fecha, double MontoFacturado, double Descuento, int GastoID, double ICE, double Excento, double montoTotal)
	{
		clsGas._Nit = nit;
		clsGas._Nombre = Nombre;
		clsGas._NroFactura = NroFactura;
		clsGas._NroAutorizacion = NroAutorizacion;
		clsGas._Codigo = Codigo;
		clsGas._ICE = ICE;
		clsGas._Excento = Excento;
		clsGas._Fecha = Fecha;
		clsGas._MontoFacturado = MontoFacturado;
		clsGas._Descuento = Descuento;
		clsGas._GastoID = GastoID;
		clsGas._MontoTotal = montoTotal;
		if (clsGas._Gastos_FacturaID == 0)
		{
			clsGas.Insert();
		}
		else
		{
			clsGas.Modify();
		}
	}

	public bool EliminarGastoID(int GastoID)
	{
		return clsGas.Delete(GastoID) != 0;
	}

	public DataTable DevolverFastos_Facturas(int id)
	{
		clsGas._GastoID = id;
		return clsGas.DevolverGastos_Facturas();
	}

	public DataTable devolverReporteFactura(DateTime desde, DateTime hasta)
	{
		return clsGas.devolverReporteFactura(desde, hasta);
	}

	public DataTable devolverReporteFacturaFormatoImpuestos(DateTime desde, DateTime hasta)
	{
		return clsGas.devolverReporteFacturaFormatoImpuestos(desde, hasta);
	}

	public bool ExisteFactura(string nit, int nro, string autorizacion)
	{
		new DataTable();
		clsGas._Nit = nit;
		clsGas._NroAutorizacion = autorizacion;
		clsGas._NroFactura = nro;
		if (clsGas.ExisteFactura().Rows.Count > 0)
		{
			return true;
		}
		return false;
	}

	public DataTable devolverReporteFacturaFormatoImpuestos1(DateTime desde, DateTime hasta)
	{
		return clsGas.devolverReporteFacturaFormatoImpuestos1(desde, hasta);
	}
}
