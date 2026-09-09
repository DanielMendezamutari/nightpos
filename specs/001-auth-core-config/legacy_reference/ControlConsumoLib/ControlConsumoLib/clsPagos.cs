using System;
using System.Data;
using System.Runtime.CompilerServices;
using System.Threading;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsPagos
{
	private int PagoID;

	private DateTime Fecha;

	private double MontoBs;

	private string MaquinaPago;

	private int DetalleCuentaID;

	private int cuentaID;

	private int transaccionId;

	private int AgruparPagoID;

	private double Descuento;

	private string NroTarjeta;

	private int MeseroID;

	public int _PagoID
	{
		get
		{
			return PagoID;
		}
		set
		{
			PagoID = value;
		}
	}

	public int _cuentaID
	{
		get
		{
			return cuentaID;
		}
		set
		{
			cuentaID = value;
		}
	}

	public string _nroTarjeta
	{
		get
		{
			return NroTarjeta;
		}
		set
		{
			NroTarjeta = value;
		}
	}

	public int _transaccionId
	{
		get
		{
			return transaccionId;
		}
		set
		{
			transaccionId = value;
		}
	}

	public int _AgruparPagoID
	{
		get
		{
			return AgruparPagoID;
		}
		set
		{
			AgruparPagoID = value;
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

	public double _MontoBs
	{
		get
		{
			return MontoBs;
		}
		set
		{
			MontoBs = value;
		}
	}

	public string _MaquinaPago
	{
		get
		{
			return MaquinaPago;
		}
		set
		{
			MaquinaPago = value;
		}
	}

	public int _DetalleCuentaID
	{
		get
		{
			return DetalleCuentaID;
		}
		set
		{
			DetalleCuentaID = value;
		}
	}

	public double _Descuento
	{
		get
		{
			return Descuento;
		}
		set
		{
			Descuento = value;
		}
	}

	public int _MeseroID
	{
		get
		{
			return MeseroID;
		}
		set
		{
			MeseroID = value;
		}
	}

	public clsPagos()
	{
		Fecha = DateAndTime.Today;
		MontoBs = 0.0;
		MaquinaPago = "";
		DetalleCuentaID = 0;
		cuentaID = 0;
		AgruparPagoID = 0;
		Descuento = 0.0;
		MeseroID = 0;
		NroTarjeta = Conversions.ToString(0);
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Pagos", " PagoID=" + PagoID);
		if (dataTable.Rows.Count > 0)
		{
			PagoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PagoID"])) ? ((object)0) : dataTable.Rows[0]["PagoID"]);
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
			MontoBs = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoBs"])) ? ((object)0) : dataTable.Rows[0]["MontoBs"]);
			MaquinaPago = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MaquinaPago"])) ? "" : dataTable.Rows[0]["MaquinaPago"]);
			DetalleCuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DetalleCuentaID"])) ? ((object)0) : dataTable.Rows[0]["DetalleCuentaID"]);
			cuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["cuentaID"])) ? ((object)0) : dataTable.Rows[0]["cuentaID"]);
			transaccionId = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["transaccionId"])) ? ((object)0) : dataTable.Rows[0]["transaccionId"]);
			AgruparPagoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AgruparPagoID"])) ? ((object)0) : dataTable.Rows[0]["AgruparPagoID"]);
			Descuento = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descuento"])) ? ((object)0) : dataTable.Rows[0]["Descuento"]);
			NroTarjeta = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["nroTarjeta"])) ? "" : dataTable.Rows[0]["nroTarjeta"]);
			MeseroID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MeseroID"])) ? ((object)0) : dataTable.Rows[0]["MeseroID"]);
		}
	}

	public int devolucionDineroBS(double cantidad, ref int CuentaId)
	{
		checked
		{
			int result;
			try
			{
				DataTable dataTable = BD.ConsultaVer("PagoID, MontoBs, CuentaId", "Pagos", "DetalleCuentaID=" + DetalleCuentaID, "pagoId desc");
				int num = dataTable.Rows.Count - 1;
				for (int i = 0; i <= num; i++)
				{
					PagoID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["PagoID"]), 0));
					double num2 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["MontoBs"]), 0));
					if (PagoID > 0)
					{
						CuentaId = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["CuentaId"]), 0));
						if (num2 >= cantidad)
						{
							BD.ConsultaModificar("Pagos", "MontoBs=MontoBs-" + Conversion.Str(cantidad) + ",flagSync=NULL", "PagoID=" + PagoID);
							break;
						}
						BD.ConsultaModificar("Pagos", "MontoBs=0,flagSync=NULL", "PagoID=" + PagoID);
						cantidad -= num2;
					}
					else
					{
						Interaction.MsgBox("Ese cliente no tiene ningun pago!");
					}
				}
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
	}

	public DataTable DevolverMaquinas()
	{
		return BD.ConsultaVer("distinct Pagos.MaquinaPago,Pagos.MaquinaPago", "Pagos");
	}

	public double DevolverEntreFechasPorMaquinaPorcuentaID1(DateTime fechaIni, DateTime fechafin, int cuenta)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select   sum(tab1.Pagado) from ( SELECT    round(sum(Pagos.MontoBs),1)  as Pagado FROM DetalleCuenta INNER JOIN Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID where  CuentaId= " + Conversions.ToString(cuenta) + " and MaquinaPago like '" + MaquinaPago + "' and Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin) + " group by DetalleCuenta.VisitaID) as tab1").Rows[0][0]), 0));
	}

	public double DevolverEntreFechasPorMaquinaPorcuentaID2(DateTime fechaIni, DateTime fechafin, int cuenta)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select   sum(tab1.Pagado) from ( SELECT    round(sum(Pagos.MontoBs),1)  as Pagado FROM DetalleCuenta INNER JOIN Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID where  CuentaId> 4 and MaquinaPago like '" + MaquinaPago + "' and Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin) + " group by DetalleCuenta.VisitaID) as tab1").Rows[0][0]), 0));
	}

	public double DevolverEntreFechasPorMaquinaPorOtrasCuenta(DateTime fechaIni, DateTime fechafin)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select   sum(tab1.Pagado) from ( SELECT    round(sum(Pagos.MontoBs),1)  as Pagado FROM DetalleCuenta INNER JOIN Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID where CuentaId<>" + Conversions.ToString(1) + " and CuentaId<>" + Conversions.ToString(4) + " and CuentaId<>" + Conversions.ToString(2) + " and CuentaId<>" + Conversions.ToString(3) + " and MaquinaPago like '" + MaquinaPago + "' and Pagos.Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin) + " group by DetalleCuenta.VisitaID) as tab1").Rows[0][0]), 0));
	}

	public DataTable DevolverEntreFechasPorMaquinaPorOtrasCuentasAnticipo(DateTime fechaIni, DateTime fechafin, bool desglose)
	{
		if (desglose)
		{
			return BD.ConsultaVer("DetalleCuenta.VisitaID , Cuentas.Nombre , sum(MontoBs) as Total ", "Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID left join DetalleCuenta on DetalleCuenta .id=Pagos.PagoID  ", "Pagos.cuentaId<>" + Conversions.ToString(1) + " and Pagos.CuentaId<>" + Conversions.ToString(4) + " and Pagos.CuentaId<>" + Conversions.ToString(2) + " and Pagos.CuentaId<>" + Conversions.ToString(3) + " and MaquinaPago like '" + MaquinaPago + "' and Pagos.Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin), "DetalleCuenta.VisitaID , Cuentas.Nombre", "DetalleCuenta.VisitaID , Cuentas.Nombre");
		}
		return BD.ConsultaVer("select Nombre,sum(Total ) as Monto from \r\n                                    (\r\n                                    select Cuentas.Nombre , sum(MontoBs) as Total   \r\n                                    from Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID  \r\n                                    where Pagos.cuentaId<> " + Conversions.ToString(1) + "  and Pagos.CuentaId<>" + Conversions.ToString(4) + "  and Pagos.CuentaId<>" + Conversions.ToString(2) + "  and \r\n                                    Pagos.CuentaId<>" + Conversions.ToString(3) + "  and MaquinaPago  like '" + MaquinaPago + "'and Pagos.Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin) + "\r\n                                    group by Cuentas.Nombre \r\n                                    union\r\n                                    select Cuentas.Nombre, sum(Monto) as Total  \r\n                                    from Anticipos left join cuentas on Anticipos.CuentaID =Cuentas.CuentaID \r\n                                    where Anticipos.CuentaID>4 and Anticipos.PC  like '" + MaquinaPago + "' and Anticipos.Fecha between " + VariableGeneral.ArmarFecha(fechaIni) + " And " + VariableGeneral.ArmarFecha(fechafin) + "\r\n                                    group by Cuentas.Nombre ) as tab1\r\n                                    group by tab1.Nombre\r\n                                    order by Nombre ");
	}

	public int getMaxAgruparPagoID()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(AgruparPagoID)", "Pagos").Rows[0][0]), 0), 1));
		}
		Guid guid = Guid.NewGuid();
		int id = 0;
		if (BD.ConsultaInsertar3("'" + guid.ToString() + "'", "PagosAgrupador(AgrupadorGUID)", ref id) != 0)
		{
			if (Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select max(AgruparPagoID) from Pagos").Rows[0][0]), 0)) > id)
			{
				id = getMaxAgruparPagoID();
			}
			return id;
		}
		Thread.Sleep(1000);
		return getMaxAgruparPagoID();
	}

	public void deletePagosAgrupador()
	{
	}

	public int Insertar(int AgruparPago, int ResponsableID)
	{
		int result;
		try
		{
			AgruparPagoID = AgruparPago;
			if (MaquinaPago.Length > 30)
			{
				MaquinaPago = MaquinaPago.Substring(0, 30);
			}
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				PagoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(PagoID)", "Pagos").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(PagoID) + "," + VariableGeneral.ArmarFecha(Fecha) + ",", Conversion.Str(MontoBs), ",'", MaquinaPago, "',"), DetalleCuentaID.ToString(), ",", Conversions.ToString(cuentaID), ",", Conversions.ToString(transaccionId), ",", Conversions.ToString(AgruparPagoID), ",", Conversion.Str(Descuento), ",", Conversions.ToString(ResponsableID), ",'", NroTarjeta, "'"), "Pagos(pagoID,Fecha, MontoBs,  MaquinaPago, DetalleCuentaID,CuentaID,transaccionId,AgruparPagoID, Descuento,MeseroID,nroTarjeta)");
				PagoID = Conversions.ToInteger(BD.ConsultaVer("max(PagoID)", "Pagos").Rows[0][0]);
				result = PagoID;
			}
			else if (BD.ConsultaInsertar3(string.Concat(string.Concat(VariableGeneral.ArmarFecha(Fecha) + ",", Conversion.Str(MontoBs), ",'", MaquinaPago, "',"), DetalleCuentaID.ToString(), ",", Conversions.ToString(cuentaID), ",", Conversions.ToString(transaccionId), ",", Conversions.ToString(AgruparPagoID), ",", Conversion.Str(Descuento), ",", Conversions.ToString(ResponsableID), ",'", NroTarjeta, "'"), "Pagos(Fecha, MontoBs,  MaquinaPago, DetalleCuentaID,CuentaID,transaccionId,AgruparPagoID,Descuento,MeseroID,nroTarjeta)", ref PagoID) != 0)
			{
				result = PagoID;
			}
			else
			{
				new clsLogg().Insertar("Pago de cuenta cls", "NO se inserto exitosamente el pago " + Conversions.ToString(PagoID) + " del detalleCuentaID " + Conversions.ToString(DetalleCuentaID), ResponsableID);
				result = 0;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			new clsLogg().Insertar("Pago de cuenta cls", "NO se inserto exitosamente el pago " + Conversions.ToString(PagoID) + "/ del detalleCuentaID " + Conversions.ToString(DetalleCuentaID) + "-" + ex2.Message, ResponsableID);
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int EliminarXAgruparPagoID(int AgruparPagoID, int mesero, string porque)
	{
		int result;
		try
		{
			new clsLogg().Insertar("Eliminar X AgruparPagoID", "AgruparPagoID " + Conversions.ToString(AgruparPagoID) + " por:" + porque, mesero);
			if (BD.ConsultaEliminar("Pagos", "AgruparPagoID = " + AgruparPagoID + " and not MaquinaPago like 'Solo Factura'") == 0)
			{
				Interaction.MsgBox("no se puede eliminar Pago, se encuentra en uso");
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

	public int EliminarXDetalleCuentaID1(string porque, int mesero)
	{
		int result;
		try
		{
			new clsLogg().Insertar("Eliminar X Detalle Cuenta", "DetalleCuentaID " + Conversions.ToString(DetalleCuentaID) + " por:" + porque, mesero);
			if (BD.ConsultaEliminar("Pagos", "DetalleCuentaID = " + DetalleCuentaID + " and not MaquinaPago like 'Solo Factura'") == 0)
			{
				Interaction.MsgBox("no se puede eliminar Pago, se encuentra en uso");
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

	public DataTable devolverPagosXCliente(int idCliente)
	{
		return BD.ConsultaVer("Pagos.PagoID,Pagos.Fecha,Pagos.MontoBs,Pagos.DetalleCuentaID, Pagos.CuentaID as Cuentas", "Pagos   LEFT JOIN DetalleCuenta On Pagos.DetalleCuentaID  = DetalleCuenta.ID   left join Visitas On DetalleCuenta.VisitaID =Visitas .ID", "Visitas.ClienteID=" + idCliente, "Pagos.Fecha desc");
	}

	public string devolverCuentaPorVisitaId(int visitaId)
	{
		DataTable dataTable = BD.ConsultaVer(" distinct    Cuentas.Nombre", "(Cuentas INNER Join Pagos On Cuentas.CuentaID = Pagos.CuentaID) INNER Join DetalleCuenta On Pagos.DetalleCuentaID = DetalleCuenta.ID", "DetalleCuenta.VisitaID =" + Conversions.ToString(visitaId));
		string text = "";
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				text = Conversions.ToString(Operators.ConcatenateObject(text, Operators.ConcatenateObject(dataTable.Rows[i][0], ", ")));
			}
			if (text.Length > 2)
			{
				text = text.Substring(0, text.Length - 2);
			}
			return text;
		}
	}

	public string devolverCuentaPorVisitaIdSinCupon(int visitaId)
	{
		DataTable dataTable = BD.ConsultaVer(" distinct    Cuentas.Nombre", "(Cuentas INNER Join Pagos On Cuentas.CuentaID = Pagos.CuentaID) INNER Join DetalleCuenta On Pagos.DetalleCuentaID = DetalleCuenta.ID", "DetalleCuenta.VisitaID =" + Conversions.ToString(visitaId));
		string text = "";
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				text = Conversions.ToString(Operators.ConcatenateObject(text, Operators.ConcatenateObject(dataTable.Rows[i][0], ", ")));
			}
			if (text.Length > 2)
			{
				text = text.Substring(0, text.Length - 2);
			}
			return text;
		}
	}

	public bool ModificarCuentaPago(int visitaID)
	{
		bool result;
		try
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				BD.ConsultaModificar("pagos inner join DetalleCuenta on Pagos.DetalleCuentaID =DetalleCuenta.id", "CuentaID=" + Conversions.ToString(cuentaID) + ", nroTarjeta='" + NroTarjeta + "',Pagos.flagSync=NULL", " DetalleCuenta.VisitaID=" + visitaID);
			}
			else
			{
				BD.ConsultaModificar("Pagos", "CuentaID=" + Conversions.ToString(cuentaID) + ", nroTarjeta='" + NroTarjeta + "',Pagos.flagSync=NULL from pagos inner join DetalleCuenta on Pagos.DetalleCuentaID =DetalleCuenta.id ", " DetalleCuenta.VisitaID=" + visitaID);
			}
			result = true;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public double MontoPagado(int visitaID)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Pagos.MontoBs) as Pagando", "DetalleCuenta inner join Pagos on Pagos.DetalleCuentaID = DetalleCuenta.id", "DetalleCuenta.Visitaid = " + Conversions.ToString(visitaID) + " and not Pagos.MaquinaPago like 'Solo Factura'").Rows[0][0]), 0));
	}

	public string DevolverCodigoAlumno(int visitaID)
	{
		string result;
		try
		{
			if (visitaID > 0)
			{
				DataTable dataTable = BD.ConsultaVer("Clientes.Codigo", "Visitas left join Clientes on Clientes.ID = Visitas.ClienteID", "Visitas.ID =" + Conversions.ToString(visitaID));
				string text = "";
				if (dataTable.Rows.Count > 0 && Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), "")).Length > 0)
				{
					text = Conversions.ToString(dataTable.Rows[0][0]);
				}
				result = text;
			}
			else
			{
				result = "";
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = "";
			ProjectData.ClearProjectError();
		}
		return result;
	}
}
