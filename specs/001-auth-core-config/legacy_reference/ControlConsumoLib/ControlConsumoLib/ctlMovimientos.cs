using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlMovimientos
{
	private readonly clsMovimientos clsMov;

	public ctlMovimientos()
	{
		clsMov = new clsMovimientos();
	}

	public int GetMovimientoID()
	{
		return clsMov._MovimientoID;
	}

	public void SetMovimientoID(int ID)
	{
		clsMov._MovimientoID = ID;
	}

	public DataTable devolverMovimientos(bool todo)
	{
		return clsMov.Devolver(todo);
	}

	public void DevolverOtrosEntreFechasPorPC(DateTime fechaIni, DateTime fechafin, ref double montoBs, ref double montoDolares)
	{
		clsMov.DevolverOtrosEntreFechasPorPC(fechaIni, fechafin, ref montoBs, ref montoDolares);
	}

	public void DevolverCambiosEntreFechasPorPC(DateTime fechaIni, DateTime fechafin, ref double montoBs, ref double montoDolares)
	{
		clsMov.DevolverCambiosEntreFechasPorPC(fechaIni, fechafin, ref montoBs, ref montoDolares);
	}

	public void DeshacerMovimientos(DateTime fecha, int visitaID)
	{
		clsMov.DeshacerMovimientos(fecha, visitaID);
	}

	public void InsertarMovimiento(DateTime Fecha, double Monto, string Descripcion, string Observacion, double TipoCambio, int CuentaID, int turnoID, int gastoId)
	{
		clsMov._Fecha = Fecha;
		clsMov._Monto = Monto;
		clsMov._Descripcion = Descripcion;
		clsMov._Observacion = Observacion;
		clsMov._TipoCambio = TipoCambio;
		clsMov._CuentaID = CuentaID;
		clsMov._TurnoID = turnoID;
		clsMov._GastoID = gastoId;
		clsMov.Insertar();
	}

	public void ModificarMovimiento(DateTime Fecha, double Monto, string Descripcion, string Observacion, int CuentaID, double TipoCambio)
	{
		clsMov._Fecha = Fecha;
		clsMov._Monto = Monto;
		clsMov._CuentaID = CuentaID;
		clsMov._Descripcion = Descripcion;
		clsMov._Observacion = Observacion;
		clsMov._TipoCambio = TipoCambio;
		clsMov.Modificar();
	}

	public void ModificarMovimientoXgastoID(DateTime Fecha, double Monto, string Descripcion, string Observacion, int CuentaID, double TipoCambio, int gastoId)
	{
		clsMov._Fecha = Fecha;
		clsMov._Monto = Monto;
		clsMov._CuentaID = CuentaID;
		clsMov._Descripcion = Descripcion;
		clsMov._Observacion = Observacion;
		clsMov._TipoCambio = TipoCambio;
		clsMov._GastoID = gastoId;
		clsMov.ModificarMovimientoXgastoID();
	}

	public void EliminarMovimiento()
	{
		new ctlMovimientos_Turnos().EliminarMovimientos_Turnobymovimiento(clsMov._MovimientoID);
		clsMov.Eliminar();
	}

	public void EliminarMovimientoXturnoID(int turnoID)
	{
		clsMov.EliminarMovimientoXturnoID(turnoID);
	}

	public double DevolverMontoXCuentaID(int CuentaID)
	{
		clsMov._CuentaID = CuentaID;
		return clsMov.DevolverMontoXCuentaID();
	}

	public double devolverMovimientosXCajaFecha(DateTime fechai, DateTime fechaF, short idCuenta)
	{
		return clsMov.devolverMovimientosXCajaFecha(fechai, fechaF, idCuenta);
	}

	public double devolverMovimientosAnticiposXCajaFecha(DateTime fechai, DateTime fechaF, short idCuenta)
	{
		return clsMov.devolverMovimientosAnticiposXCajaFecha(fechai, fechaF, idCuenta);
	}

	public int ExisteMovimiento(int idGasto)
	{
		clsMov._GastoID = idGasto;
		return clsMov.ExisteMovimiento();
	}

	public int DevolverTurnoXGastoID(int id)
	{
		clsMov._GastoID = id;
		return clsMov.DevolverTurnoXGastoID();
	}

	public DataTable devolverMovimientosXFechas(DateTime FechaIni, DateTime fechaFin)
	{
		return clsMov.DevolverXFechasReporte(FechaIni, fechaFin);
	}
}
