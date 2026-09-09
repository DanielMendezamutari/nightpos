using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsMovimientos
{
	private int MovimientoID;

	private DateTime Fecha;

	private double Monto;

	private string Descripcion;

	private string Observacion;

	private double TipoCambio;

	private string TurnoID;

	private string GastoID;

	private int CuentaID;

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

	public DateTime _Fecha
	{
		get
		{
			return Fecha;
		}
		set
		{
			Fecha = value;
		}
	}

	public double _Monto
	{
		get
		{
			return Monto;
		}
		set
		{
			Monto = value;
		}
	}

	public string _Descripcion
	{
		get
		{
			return Descripcion;
		}
		set
		{
			Descripcion = value;
		}
	}

	public string _Observacion
	{
		get
		{
			return Observacion;
		}
		set
		{
			Observacion = value;
		}
	}

	public double _TipoCambio
	{
		get
		{
			return TipoCambio;
		}
		set
		{
			TipoCambio = value;
		}
	}

	public int _TurnoID
	{
		get
		{
			if (Operators.CompareString(TurnoID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(TurnoID);
		}
		set
		{
			if (value == 0)
			{
				TurnoID = "null";
			}
			else
			{
				TurnoID = Conversions.ToString(value);
			}
		}
	}

	public int _CuentaID
	{
		get
		{
			return CuentaID;
		}
		set
		{
			CuentaID = value;
		}
	}

	public int _GastoID
	{
		get
		{
			if (Operators.CompareString(GastoID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(GastoID);
		}
		set
		{
			if (value == 0)
			{
				GastoID = "null";
			}
			else
			{
				GastoID = Conversions.ToString(value);
			}
		}
	}

	public void DevolverOtrosEntreFechasPorPC(DateTime fechaIni, DateTime fechafin, ref double montoBs, ref double montoDolares)
	{
		montoBs = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Monto)", "Movimientos", "TurnoID is null and Descripcion <> 'Cambio de dolares' and CuentaID= " + Conversions.ToString(1) + " and Descripcion not like 'Anticipo del cliente%' and Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin) + " and PC like '" + MyProject.Computer.Name + "'").Rows[0][0]), 0));
		montoDolares = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Monto)", "Movimientos", "TurnoID is null and Descripcion <> 'Cambio de dolares' and CuentaID= " + Conversions.ToString(2) + " and Descripcion not like 'Anticipo del cliente%' and Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin) + " and PC like '" + MyProject.Computer.Name + "'").Rows[0][0]), 0));
	}

	public void DevolverCambiosEntreFechasPorPC(DateTime fechaIni, DateTime fechafin, ref double montoBs, ref double montoDolares)
	{
		montoBs = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Monto)", "Movimientos", "Descripcion like 'Cambio de dolares' and CuentaID= " + Conversions.ToString(1) + " and Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin) + " and PC like '" + MyProject.Computer.Name + "'").Rows[0][0]), 0));
		montoDolares = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Monto)", "Movimientos", "Descripcion like 'Cambio de dolares' and CuentaID= " + Conversions.ToString(2) + " and Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin) + " and PC like '" + MyProject.Computer.Name + "'").Rows[0][0]), 0));
	}

	public DataTable Devolver(bool todo)
	{
		DataTable result;
		try
		{
			if (!todo)
			{
				DateTime fecha = ((DateAndTime.Now.Hour >= VariableGeneral._horaCierreTurno) ? DateAndTime.Today.AddHours(VariableGeneral._horaCierreTurno) : DateAndTime.Today.AddDays(-1.0).AddHours(VariableGeneral._horaCierreTurno));
				result = ((configuration.gMODO_ACCESS == 1) ? BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, iif(Movimientos.TipoCambio =1,'Bs','$') as Moneda ,Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ," + ((configuration.gMODO_ACCESS == 1) ? "iif(Turnos.TurnoID is null,0,1)=1" : "cast(iif(Turnos.TurnoID is null,0,1) as bit)") + " As Turnos," + ((configuration.gMODO_ACCESS == 1) ? "iif(Gastos.GastoID is null,0,1)" : "cast(iif(Gastos.GastoID is null,0,1) as bit)") + " As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "Fecha >= " + VariableGeneral.ArmarFecha(fecha), "Movimientos.Fecha") : ((configuration.gMODO_ACCESS != 2) ? BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, case when Movimientos.TipoCambio =1 then 'Bs' else '$' end as Moneda ,Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ," + ((configuration.gMODO_ACCESS == 1) ? "case when Turnos.TurnoID is null then 0 else 1 end = 1" : "cast(case when Turnos.TurnoID is null then 0 else 1 end as bit)") + " As Turnos," + ((configuration.gMODO_ACCESS == 1) ? "case when Gastos.GastoID is null then 0 else 1 end =1" : "cast(case when Gastos.GastoID is null then 0 else 1 end as bit)") + " As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "Fecha >= " + VariableGeneral.ArmarFecha(fecha), "Movimientos.Fecha") : BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, case when Movimientos.TipoCambio =1 then 'Bs' else '$' end as Moneda ,Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ,  case when Turnos.TurnoID is null then 0 else 1 end = 1  As Turnos, case when Gastos.GastoID is null then 0 else 1 end =1  As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "Fecha >= " + VariableGeneral.ArmarFecha(fecha), "Movimientos.Fecha")));
			}
			else
			{
				result = ((configuration.gMODO_ACCESS == 1) ? BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, iif(Movimientos.TipoCambio =1,'Bs','$') as Moneda ,Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ," + ((configuration.gMODO_ACCESS == 1) ? "iif(Turnos.TurnoID is null,0,1)=1" : "cast(iif(Turnos.TurnoID is null,0,1) as bit)") + " As Turnos," + ((configuration.gMODO_ACCESS == 1) ? "iif(Gastos.GastoID is null,0,1)" : "cast(iif(Gastos.GastoID is null,0,1) as bit)") + " As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "", "Movimientos.Fecha") : ((configuration.gMODO_ACCESS != 2) ? BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, CASE WHEN Movimientos.TipoCambio =1 then 'Bs' else '$' end as Moneda ,Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ,cast(case when Turnos.TurnoID is null then 0 else 1 end as bit) As Turnos," + ((configuration.gMODO_ACCESS == 1) ? "case when Gastos.GastoID is null then 0 else 1 end=1" : "cast(case when Gastos.GastoID is null then 0 else 1 end as bit)") + " As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "", "Movimientos.Fecha") : BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, CASE WHEN Movimientos.TipoCambio =1 then 'Bs' else '$' end as Moneda ,Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ,case when Turnos.TurnoID is null then 0 else 1 end = 1  As Turnos, case when Gastos.GastoID is null then 0 else 1 end=1 As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "", "Movimientos.Fecha")));
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = new DataTable();
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int ModificarMovimientoXgastoID()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Movimientos", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",Monto=" + Conversion.Str(Monto) + ",Descripcion='" + Descripcion + "',CuentaID=" + Conversions.ToString(CuentaID) + ",GastoID=" + GastoID + ",Observacion='" + Observacion + "',TipoCambio=" + Conversion.Str(TipoCambio) + ",flagSync=NULL", "gastoID=" + GastoID.ToString());
			result = 1;
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

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Movimientos", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",Monto=" + Conversion.Str(Monto) + ",Descripcion='" + Descripcion + "',CuentaID=" + Conversions.ToString(CuentaID) + ",Observacion='" + Observacion + "',TipoCambio=" + Conversion.Str(TipoCambio) + ",flagSync=NULL", "MovimientoID=" + MovimientoID);
			result = 1;
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

	public int Insertar()
	{
		int result;
		try
		{
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				MovimientoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(MovimientoID)", "Movimientos").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(Conversions.ToString(MovimientoID) + "," + VariableGeneral.ArmarFecha(Fecha) + ",", Conversion.Str(Monto), ",'", Descripcion, "','", Observacion, "',"), Conversion.Str(TipoCambio), ","), Conversions.ToString(CuentaID), ",", GastoID, ",", TurnoID, ",'", MyProject.Computer.Name, "'"), "Movimientos(MovimientoID,Fecha,Monto ,Descripcion,Observacion,TipoCambio ,CuentaID,GastoID,TurnoID,PC)");
				MovimientoID = Conversions.ToInteger(BD.ConsultaVer("max(MovimientoID)", "Movimientos").Rows[0][0]);
				result = MovimientoID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(VariableGeneral.ArmarFecha(Fecha) + ",", Conversion.Str(Monto), ",'", Descripcion, "','", Observacion, "',"), Conversion.Str(TipoCambio), ","), Conversions.ToString(CuentaID), ",", GastoID, ",", TurnoID, ",'", MyProject.Computer.Name, "'"), "Movimientos(Fecha,Monto ,Descripcion,Observacion,TipoCambio ,CuentaID,GastoID,TurnoID,PC)", ref MovimientoID);
				result = MovimientoID;
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
			if (BD.ConsultaEliminar("Movimientos", "MovimientoID = " + MovimientoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Movimiento, se encuentra en uso");
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

	public int EliminarXCompraID(int CompraID)
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Movimientos", "GastoID in (select GastoID from Gastos where CompraID = " + CompraID + ")") == 0)
			{
				Interaction.MsgBox("no se puede eliminar Movimiento, se encuentra en uso");
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

	public int EliminarMovimientoXturnoID(int turnoID)
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Movimientos", (" turnoID = " + turnoID) ?? "") == 0)
			{
				Interaction.MsgBox("no se puede eliminar Movimiento, se encuentra en uso");
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

	public int DeshacerMovimientos(DateTime fecha, int visitaID)
	{
		int result;
		try
		{
			BD.ConsultaEliminar("Movimientos", "fecha>=" + VariableGeneral.ArmarFecha(fecha) + " and fecha<=" + VariableGeneral.ArmarFecha(DateAndTime.Now) + " and Descripcion='Cambio de dolares' and PC like '" + MyProject.Computer.Name + "'");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("update R set R.pago=R.pago-Pagos.MontoBs, R.Debe=R.Debe +Pagos.MontoBs, Cerrada=" + VariableGeneral.armarBolean(0) + ",R.flagSync=NULL from DetalleCuenta as R inner join Pagos on Pagos.DetalleCuentaId=R.ID where  R.VisitaId= " + Conversions.ToString(visitaID) + " and Pagos.fecha>=" + VariableGeneral.ArmarFecha(fecha) + " and  Pagos.fecha<=" + VariableGeneral.ArmarFecha(DateAndTime.Now) + " and Pagos.MaquinaPago like '" + MyProject.Computer.Name + "' ");
			}
			else
			{
				BD.ConsultWithOutAlerts("update DetalleCuenta as R inner join Pagos on Pagos.DetalleCuentaId=R.ID  set R.pago=R.pago-Pagos.MontoBs, R.Debe=R.Debe +Pagos.MontoBs , Cerrada=" + VariableGeneral.armarBolean(0) + ",R.flagSync=NULL where R.VisitaId= " + Conversions.ToString(visitaID) + " and Pagos.fecha>=" + VariableGeneral.ArmarFecha(fecha) + " and  Pagos.fecha<=" + VariableGeneral.ArmarFecha(DateAndTime.Now) + " and Pagos.MaquinaPago like '" + MyProject.Computer.Name + "' ");
			}
			BD.ConsultaEliminar("Pagos", "fecha>=" + VariableGeneral.ArmarFecha(fecha) + " and Pagos.fecha<=" + VariableGeneral.ArmarFecha(DateAndTime.Now) + " and MaquinaPago like '" + MyProject.Computer.Name + "' and DetalleCuentaID in (select id from DetalleCuenta where VisitaID=" + Conversions.ToString(visitaID) + " )");
			result = 1;
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

	public int EliminarXGastoID()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Movimientos", "GastoID = " + GastoID.ToString()) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Movimiento, se encuentra en uso");
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

	public double DevolverMontoXCuentaID()
	{
		DataTable dataTable = BD.ConsultaVer("sum(monto)", "movimientos", "CuentaID =" + Conversions.ToString(CuentaID));
		if (Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0])))
		{
			return 0.0;
		}
		return Conversions.ToDouble(dataTable.Rows[0][0]);
	}

	public DataTable MovimientoReportXRangoFecha(DateTime fechai, DateTime fechaF)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer(" Movimientos.Fecha,Cuentas.Nombre as Cuentas ,iif(Cuentas.Moneda=0,Movimientos.monto,0  ) as Bolivianos,iif(Cuentas.Moneda=1,Movimientos.monto,0  ) as Dolares, iif(Movimientos.TipoCambio =1,'Bs','$') as Moneda , Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ,cast(iif(Cobros.CobroID is null,0,1) as bit) As Cobros,cast(iif(Gastos.GastoID is null,0,1) as bit) As Gastos ", ("Movimientos LEFT JOIN Cobros On Movimientos.CobroID = Cobros.CobroID LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID where Movimientos.Fecha  BETWEEN " + VariableGeneral.ArmarFecha(fechai) + " AND " + VariableGeneral.ArmarFecha(fechaF)) ?? "");
		}
		return BD.ConsultaVer(" Movimientos.Fecha,Cuentas.Nombre as Cuentas ,case when Cuentas.Moneda=0 then Movimientos.monto else 0  end as Bolivianos,case when Cuentas.Moneda=1 then Movimientos.monto else 0  end as Dolares, case when Movimientos.TipoCambio =1 then 'Bs' else '$' end as Moneda , Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ,cast(case when Cobros.CobroID is null then 0 else 1 end as bit) As Cobros,cast(case when Gastos.GastoID is null then 0 else 1 end as bit) As Gastos ", ("Movimientos LEFT JOIN Cobros On Movimientos.CobroID = Cobros.CobroID LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID where Movimientos.Fecha  BETWEEN " + VariableGeneral.ArmarFecha(fechai) + " AND " + VariableGeneral.ArmarFecha(fechaF)) ?? "");
	}

	public DataTable MovimientoReporteCta()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Cuentas.Nombre,Cuentas.Banco,Cuentas.TipoCuenta,(SELECT sum(Monto) FROM Movimientos   where CuentaID=Cuentas.CuentaID and Cuentas.Moneda =0  group by CuentaID) as 'Bolivianos',  (SELECT sum(Monto) FROM Movimientos   where Movimientos.CuentaID=Cuentas.CuentaID and Cuentas.Moneda =1  group by CuentaID) as 'Dolares', iif(Cuentas.Moneda=0,'Bs','$') as Moneda ", "Cuentas");
		}
		return BD.ConsultaVer("Cuentas.Nombre,Cuentas.Banco,Cuentas.TipoCuenta,(SELECT sum(Monto) FROM Movimientos   where CuentaID=Cuentas.CuentaID and Cuentas.Moneda =0  group by CuentaID) as 'Bolivianos',  (SELECT sum(Monto) FROM Movimientos   where Movimientos.CuentaID=Cuentas.CuentaID and Cuentas.Moneda =1  group by CuentaID) as 'Dolares', CASE WHEN Cuentas.Moneda=0 then 'Bs' else '$' end as Moneda ", "Cuentas");
	}

	public DataTable DevolverXFecha(DateTime FechaIni, DateTime FechaFin)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, iif(Movimientos.TipoCambio =1,'Bs','$') as Moneda ,Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ," + ((configuration.gMODO_ACCESS == 1) ? "iif(Turnos.TurnoID is null,0,1)=1" : "cast(iif(Turnos.TurnoID is null,0,1) as bit)") + " As Turnos," + ((configuration.gMODO_ACCESS == 1) ? "iif(Gastos.GastoID is null,0,1)" : "cast(iif(Gastos.GastoID is null,0,1) as bit)") + " As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "", "Movimientos.Fecha");
		}
		return BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, CASE WHEN Movimientos.TipoCambio =1 then 'Bs' else '$' end as Moneda ,Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ,cast(case when Turnos.TurnoID is null then 0 else 1 end as bit) As Turnos," + ((configuration.gMODO_ACCESS == 1) ? "case when Gastos.GastoID is null then 0 else 1 end=1" : "cast(case when Gastos.GastoID is null then 0 else 1 end as bit)") + " As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "", "Movimientos.Fecha");
	}

	public int ExisteMovimiento()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Movimientos", "GastoID = " + GastoID);
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		return 0;
	}

	public double devolverMovimientosAnticiposXCajaFecha(DateTime fechai, DateTime fechaF, int idCuenta)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Monto) as monto1", " Movimientos", "CuentaID=" + Conversions.ToString(idCuenta) + " and Movimientos.Fecha  BETWEEN " + VariableGeneral.ArmarFecha(fechai) + " AND (" + VariableGeneral.ArmarFecha(fechaF) + ")").Rows[0][0]), 0));
	}

	public double devolverMovimientosXCajaFecha(DateTime fechai, DateTime fechaF, int idCuenta)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Monto) as monto", " Movimientos", "  CuentaID=" + Conversions.ToString(idCuenta) + " and Movimientos.Fecha  BETWEEN " + VariableGeneral.ArmarFecha(fechai) + " AND (" + VariableGeneral.ArmarFecha(fechaF) + ")").Rows[0][0]), 0));
	}

	public int DevolverTurnoXGastoID()
	{
		DataTable dataTable = BD.ConsultaVer("select TurnoID  from Movimientos where GastoID=" + GastoID);
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), -1));
		}
		return -1;
	}

	public void EliminarMovimientoCerrado(DateTime fecha, int cuenta, double mont)
	{
		DataTable dataTable = BD.ConsultaVer("select MovimientoID from Movimientos where Fecha > " + VariableGeneral.ArmarFecha(fecha) + " and CuentaID = " + Conversions.ToString(cuenta) + " and PC= '" + MyProject.Computer.Name + "' and Descripcion like 'Gastos%' order by Fecha  asc");
		if (dataTable.Rows.Count > 0 && Operators.ConditionalCompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), -1), 0, TextCompare: false))
		{
			BD.ConsultaModificar("Movimientos", "Monto= Monto + " + Conversion.Str(mont) + ",flagSync=NULL", Conversions.ToString(Operators.ConcatenateObject("MovimientoID=", dataTable.Rows[0][0])));
		}
	}

	public DataTable DevolverXFechasReporte(DateTime FechaIni, DateTime FechaFin)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, iif(Movimientos.TipoCambio =1,'Bs','$') as Moneda,Movimientos.Descripcion,Movimientos.Observacion,Movimientos.TipoCambio ," + ((configuration.gMODO_ACCESS == 1) ? "iif(Turnos.TurnoID is null,0,1)=1" : "cast(iif(Turnos.TurnoID is null,0,1) as bit)") + " As Turnos," + ((configuration.gMODO_ACCESS == 1) ? "iif(Gastos.GastoID is null,0,1)" : "cast(iif(Gastos.GastoID is null,0,1) as bit)") + " As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "Movimientos.Fecha  BETWEEN  " + VariableGeneral.ArmarFecha(FechaIni) + " and  " + VariableGeneral.ArmarFecha(FechaFin), "Movimientos.Fecha");
		}
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, case when Movimientos.TipoCambio =1 then 'Bs' else '$' end as Moneda ,Movimientos.Descripcion, Movimientos.Observacion,Movimientos.TipoCambio ,  case when Turnos.TurnoID is null then 0 else 1 end = 1  As Turnos, case when Gastos.GastoID is null then 0 else 1 end =1  As Gastos, Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID) left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "Movimientos.Fecha  BETWEEN  " + VariableGeneral.ArmarFecha(FechaIni) + " and  " + VariableGeneral.ArmarFecha(FechaFin), "Movimientos.Fecha");
		}
		return BD.ConsultaVer("Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto, case when Movimientos.TipoCambio =1 then 'Bs' else '$' end as Moneda ,Movimientos.Descripcion, Movimientos.Observacion,Movimientos.TipoCambio ," + ((configuration.gMODO_ACCESS == 1) ? "case when Turnos.TurnoID is null then 0 else 1 end = 1" : "cast(case when Turnos.TurnoID is null then 0 else 1 end as bit)") + " As Turnos," + ((configuration.gMODO_ACCESS == 1) ? "case when Gastos.GastoID is null then 0 else 1 end =1" : "cast(case when Gastos.GastoID is null then 0 else 1 end as bit)") + " As Gastos,  Cuentas.CuentaID ,Cuentas.Nombre as Cuentas", "((Movimientos LEFT JOIN Turnos On Movimientos.TurnoID = Turnos.TurnoID) LEFT JOIN Gastos On Movimientos.GastoID = Gastos.GastoID)  left join Cuentas on Cuentas.CuentaId=Movimientos.CuentaID", "Movimientos.Fecha  BETWEEN  " + VariableGeneral.ArmarFecha(FechaIni) + " and  " + VariableGeneral.ArmarFecha(FechaFin), "Movimientos.Fecha");
	}
}
