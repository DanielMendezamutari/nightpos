using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlGastos
{
	private readonly clsGastos clsGas;

	private readonly clsCuentas clsCuen;

	public ctlGastos()
	{
		clsGas = new clsGastos();
		clsCuen = new clsCuentas();
	}

	public int GetGastoID()
	{
		return clsGas._GastoID;
	}

	public void SetGastoID(int ID)
	{
		clsGas._GastoID = ID;
	}

	public double devolverMontoEntreFechasPorMaquinaBs(DateTime fechaIni, DateTime fechafin)
	{
		return clsGas.devolverMontoEntreFechasPorMaquinaBs(fechaIni, fechafin);
	}

	public double devolverMontoEntreFechasPorMaquinaDolares(DateTime fechaIni, DateTime fechafin)
	{
		return clsGas.devolverMontoEntreFechasPorMaquinaDolares(fechaIni, fechafin);
	}

	public DataTable devolverPagosXcompraID(int compraID)
	{
		clsGas._CompraID = compraID;
		return clsGas.devolverPagosXcompraID();
	}

	public void GuardarGasto(DateTime FechaSalida, DateTime FechaProgramada, bool FechaSalidaCh, bool FechaProgramadaCh, double Monto, bool RecibiFactura, string Observacion, int TipoGastoID, int compraID, int personalId, string TipoGastoStr, int cuentaId, int proveedorId)
	{
		clsGas._FechaSalida = FechaSalida;
		clsGas._FechaProgramada = FechaProgramada;
		clsGas._FechaSalidaCh = FechaSalidaCh;
		clsGas._FechaProgramadaCh = FechaProgramadaCh;
		clsGas._PersonalID = personalId;
		clsGas._Monto = Monto;
		clsGas._RecibiFactura = RecibiFactura;
		clsGas._Observacion = Observacion;
		clsGas._TipoGastoID = TipoGastoID;
		clsGas._CompraID = compraID;
		clsGas._CuentaID = cuentaId;
		clsGas._ProveedorID = proveedorId;
		clsCuen._CuentaID = cuentaId;
		clsTipoCambio clsTipoCambio2 = new clsTipoCambio();
		clsGas._TipoCambio = ((clsCuen.devolverMonedaCuentaID() == 0) ? 1.0 : clsTipoCambio2.returnTipoCambio());
		new clsTurnos();
		ctlMovimientos ctlMovimientos2 = new ctlMovimientos();
		if (clsGas._GastoID > 0)
		{
			ctlMovimientos2.SetMovimientoID(ctlMovimientos2.ExisteMovimiento(clsGas._GastoID));
			ctlMovimientos2.EliminarMovimiento();
			clsGas._GastoID = clsGas._GastoID;
			clsGas.Eliminar();
			clsGas._GastoID = 0;
		}
		if (clsGas._GastoID == 0)
		{
			clsGas.Insertar();
			if (FechaSalidaCh && ((cuentaId != 1) & (cuentaId != 2)))
			{
				ctlMovimientos2.InsertarMovimiento(FechaSalida, Monto * -1.0, TipoGastoStr, Observacion, (clsCuen.devolverMonedaCuentaID() == 0) ? 1.0 : clsTipoCambio2.returnTipoCambio(), cuentaId, 0, clsGas._GastoID);
			}
		}
	}

	public bool EliminarGasto()
	{
		clsMovimientos clsMovimientos2 = new clsMovimientos();
		clsMovimientos2._GastoID = clsGas._GastoID;
		if (clsMovimientos2.ExisteMovimiento() > 0)
		{
			clsMovimientos2.EliminarXGastoID();
		}
		else
		{
			clsGas.llenarclase();
			if (clsGas._FechaSalidaCh)
			{
				clsMovimientos2.EliminarMovimientoCerrado(clsGas._FechaSalida, clsGas._CuentaID, clsGas._Monto);
			}
		}
		new CtlGastos_Facturas().EliminarGastoID(clsGas._GastoID);
		return clsGas.Eliminar() != 0;
	}

	public bool EliminarCobroXCompraID(int compraID)
	{
		clsGas._CompraID = compraID;
		return clsGas.EliminarCobroXCompraID() != 0;
	}

	public DataTable DevolverGastosPendientes()
	{
		return clsGas.DevolverGastosPendientes1();
	}

	public DataTable devolverGastosSalidaProgramada(DateTime FechaSI, DateTime FechaSF, DateTime FechaPI, DateTime FechaPF, bool completo, int TipoGastoID, bool Gastos, bool Compras)
	{
		if (TipoGastoID == 0)
		{
			return clsGas.devolverGastosSalidaProgramada(FechaSI, FechaSF, FechaPI, FechaPF, completo, Gastos, Compras);
		}
		clsGas._TipoGastoID = TipoGastoID;
		return clsGas.devolverGastosSalidaProgramadaGasto(FechaSI, FechaSF, FechaPI, FechaPF, completo, Gastos, Compras);
	}

	public DataTable devolverGasto(bool todo)
	{
		return clsGas.Devolver(todo);
	}

	public bool HayGastosdProgramadosHoy()
	{
		return clsGas.HayGastosdProgramadosHoy();
	}

	public clsGastos LlenarClase()
	{
		clsGas.llenarclase();
		return clsGas;
	}

	public bool GastoPerteneceAlTurno(DateTime fecha)
	{
		clsTurnos clsTurnos2 = new clsTurnos();
		string desc = "";
		if (clsTurnos2.getInfoTurnoActual(ref desc))
		{
			return clsTurnos2.GastoPerteneceAlTurno(fecha);
		}
		return false;
	}

	public DataTable DevolverGastosFecha(DateTime fechaIni, DateTime fechaFin)
	{
		return clsGas.DevolverGastosFecha(fechaIni, fechaFin);
	}
}
