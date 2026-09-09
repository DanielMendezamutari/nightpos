using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsMovimientos_Turnos
{
	private int Movimientos_TurnoID;

	private int MovimientoID;

	private int TurnoID;

	public int _Movimientos_TurnoID
	{
		get
		{
			return Movimientos_TurnoID;
		}
		set
		{
			Movimientos_TurnoID = value;
		}
	}

	public int _MovimientoID
	{
		get
		{
			return MovimientoID;
		}
		set
		{
			MovimientoID = value;
		}
	}

	public int _TurnoID
	{
		get
		{
			return TurnoID;
		}
		set
		{
			TurnoID = value;
		}
	}

	public clsMovimientos_Turnos()
	{
		Movimientos_TurnoID = 0;
		MovimientoID = 0;
		TurnoID = 0;
	}

	public int Insertar()
	{
		int result;
		try
		{
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				Movimientos_TurnoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(Movimientos_TurnoID)", "Movimientos_Turnos").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(Conversions.ToString(Movimientos_TurnoID) + "," + Conversions.ToString(MovimientoID) + ",", Conversions.ToString(TurnoID)) ?? "", "Movimientos_Turnos(Movimientos_TurnoID,MovimientoID,TurnoID)");
				Movimientos_TurnoID = Conversions.ToInteger(BD.ConsultaVer("max(Movimientos_TurnoID)", "Movimientos_Turnos").Rows[0][0]);
				result = Movimientos_TurnoID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(Conversions.ToString(MovimientoID) + ",", Conversions.ToString(TurnoID)) ?? "", "Movimientos_Turnos(MovimientoID,TurnoID)", ref Movimientos_TurnoID);
				result = Movimientos_TurnoID;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int EliminarMovimientos_Turnobymovimiento()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Movimientos_Turnos", "MovimientoID = " + MovimientoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Movimientos_Turnos, se encuentra en uso");
				result = 0;
			}
			else
			{
				result = 1;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Movimientos_Turnos", "Movimientos_TurnoID = " + Movimientos_TurnoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Movimientos_Turnos, se encuentra en uso");
				result = 0;
			}
			else
			{
				result = 1;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public DataTable DevolverTurnosDisponibles()
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer(" TurnoId, " + ((configuration.gMODO_ACCESS == 1) ? "(fechaIni &  \" - Turno \" &  Nro)" : "CAST(fechaIni AS char(20)) +  '  Turno ' + CAST(Nro AS char(20))  ") + " as turno ", "Turnos", " TurnoID  not in (select TurnoID  from Movimientos_Turnos) and fechaIni >= " + VariableGeneral.ArmarFecha(DateAndTime.Today.AddMonths(-1)));
		}
		return BD.ConsultaVer(" TurnoId, " + ((configuration.gMODO_ACCESS == 1) ? "(fechaIni &  \" - Turno \" &  Nro)" : "CAST(fechaIni AS varchar(20)) +  '  Turno ' + CAST(Nro AS varchar(20))  ") + " as turno ", "Turnos", " TurnoID  not in (select TurnoID  from Movimientos_Turnos) and fechaIni >= " + VariableGeneral.ArmarFecha(DateAndTime.Today.AddMonths(-1)));
	}

	public DataTable DevolverMovimientosTurnosPorMovimientoID()
	{
		return BD.ConsultaVer(" Movimientos_Turnos.Movimientos_TurnoID ," + ((configuration.gMODO_ACCESS == 1) ? "(fechaIni &  \" - Turno \" &  Nro)" : "CAST(fechaIni AS varchar) +  '  Turno ' + CAST(Nro AS varchar)  ") + "  as Turno ", "Movimientos_Turnos inner join Turnos on Turnos.TurnoId=Movimientos_Turnos.turnoID", " Movimientos_Turnos.movimientoId=" + Conversions.ToString(MovimientoID));
	}
}
