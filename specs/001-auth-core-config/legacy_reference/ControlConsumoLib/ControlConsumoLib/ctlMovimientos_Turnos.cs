using System.Data;

namespace ControlConsumoLib;

public class ctlMovimientos_Turnos
{
	private readonly clsMovimientos_Turnos clsMov;

	public ctlMovimientos_Turnos()
	{
		clsMov = new clsMovimientos_Turnos();
	}

	public int GetMovimientos_TurnoID()
	{
		return clsMov._Movimientos_TurnoID;
	}

	public void SetMovimientos_TurnoID(int ID)
	{
		clsMov._Movimientos_TurnoID = ID;
	}

	public DataTable DevolverMovimientosTurnosPorMovimientoID(int movimientoID)
	{
		clsMov._MovimientoID = movimientoID;
		return clsMov.DevolverMovimientosTurnosPorMovimientoID();
	}

	public DataTable DevolverTurnosDisponibles()
	{
		return clsMov.DevolverTurnosDisponibles();
	}

	public void InserstartMovimientos_Turno(int MovimientoID, int TurnoID)
	{
		clsMov._MovimientoID = MovimientoID;
		clsMov._TurnoID = TurnoID;
		clsMov.Insertar();
	}

	public void EliminarMovimientos_Turno()
	{
		clsMov.Eliminar();
	}

	public void EliminarMovimientos_Turnobymovimiento(int movId)
	{
		clsMov._MovimientoID = movId;
		clsMov.EliminarMovimientos_Turnobymovimiento();
	}

	public DataTable DevolverMovimientosTurnosPorMovimientoID()
	{
		return clsMov.DevolverMovimientosTurnosPorMovimientoID();
	}
}
