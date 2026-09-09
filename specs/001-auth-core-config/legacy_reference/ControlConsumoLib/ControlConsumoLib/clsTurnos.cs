using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsTurnos
{
	public int TurnoID;

	private int Nro;

	public int get_nro()
	{
		return Nro;
	}

	public bool getInfoTurnoActual(ref DateTime FechaIni, ref double montoInibs, ref double montoInidol, ref int RespArqueo)
	{
		int horaCierreTurno = VariableGeneral._horaCierreTurno;
		DateTime fecha = ((DateAndTime.Now.Hour >= horaCierreTurno) ? DateAndTime.Today : DateAndTime.Today.AddDays(-1.0));
		DataTable dataTable = BD.ConsultaVer("*", "Turnos", "FechaIni > " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(fecha) + configuration.CaracterFecha + " and Fechafin is null and PC like '" + MyProject.Computer.Name + "'");
		if (dataTable.Rows.Count > 0)
		{
			FechaIni = Conversions.ToDate(dataTable.Rows[0]["FechaIni"]);
			montoInibs = Conversions.ToDouble(dataTable.Rows[0]["MontoIni"]);
			montoInidol = Conversions.ToDouble(dataTable.Rows[0]["MontoIniDolar"]);
			RespArqueo = Conversions.ToInteger(dataTable.Rows[0]["PersonalID"]);
			TurnoID = Conversions.ToInteger(dataTable.Rows[0]["TurnoID"]);
			return true;
		}
		TurnoID = 0;
		FechaIni = DateTime.MinValue;
		return false;
	}

	public bool getultimaInfo(ref DateTime FechaIni, ref double montoInibs, ref double montoInidol, ref int RespArqueo)
	{
		DataTable dataTable = BD.ConsultaVer("top 1 *", "Turnos", " PC like '" + MyProject.Computer.Name + "'", "FechaIni desc");
		if (dataTable.Rows.Count > 0)
		{
			FechaIni = Conversions.ToDate(dataTable.Rows[0]["FechaIni"]);
			montoInibs = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoIni"]), 0));
			montoInidol = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoIniDolar"]), 0));
			RespArqueo = Conversions.ToInteger(dataTable.Rows[0]["PersonalID"]);
			TurnoID = Conversions.ToInteger(dataTable.Rows[0]["TurnoID"]);
			return true;
		}
		TurnoID = 0;
		FechaIni = DateAndTime.Now;
		return false;
	}

	public bool HayTurnosAbiertos()
	{
		DataTable dataTable = BD.ConsultaVer("count(*)", "Turnos", string.Concat(str2: VariableGeneral.armarSoloLaFecha((DateAndTime.Now.Hour >= VariableGeneral._horaCierreTurno) ? DateAndTime.Today.AddHours(VariableGeneral._horaCierreTurno) : DateAndTime.Today.AddDays(-1.0).AddHours(VariableGeneral._horaCierreTurno)), str0: "FechaIni > ", str1: configuration.CaracterFecha, str3: configuration.CaracterFecha) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			return Operators.ConditionalCompareObjectGreater(dataTable.Rows[0][0], 0, TextCompare: false);
		}
		return false;
	}

	public bool reabrir()
	{
		bool result;
		try
		{
			BD.ConsultaModificar("Turnos", "FechaFin=NULL,MontoFin=NULL,montoFinDolar=NULL,Observaciones=''", "TurnoID=" + TurnoID);
			new ctlMovimientos().EliminarMovimientoXturnoID(TurnoID);
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

	public bool getInfoTurnoActual(ref string desc)
	{
		DateTime fecha = ((DateAndTime.Now.Hour >= VariableGeneral._horaCierreTurno) ? DateAndTime.Today.AddHours(VariableGeneral._horaCierreTurno) : DateAndTime.Today.AddDays(-1.0).AddHours(VariableGeneral._horaCierreTurno));
		DataTable dataTable = BD.ConsultaVer("*", "Turnos", "FechaIni > " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(fecha) + configuration.CaracterFecha + " and Fechafin is null and PC like '" + MyProject.Computer.Name + "'");
		if (dataTable.Rows.Count > 0)
		{
			Nro = Conversions.ToInteger(dataTable.Rows[0]["Nro"]);
			desc = Conversions.ToString(Operators.ConcatenateObject(" Turno Nro ", dataTable.Rows[0]["Nro"]));
			TurnoID = Conversions.ToInteger(dataTable.Rows[0]["TurnoID"]);
			return true;
		}
		Nro = 0;
		TurnoID = 0;
		desc = "";
		return false;
	}

	public bool getInfoTurnoActualEnCualquierPC()
	{
		DateTime fecha = ((DateAndTime.Now.Hour >= VariableGeneral._horaCierreTurno) ? DateAndTime.Today.AddHours(VariableGeneral._horaCierreTurno) : DateAndTime.Today.AddDays(-1.0).AddHours(VariableGeneral._horaCierreTurno));
		DataTable dataTable = BD.ConsultaVer("*", "Turnos", "FechaIni > " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(fecha) + configuration.CaracterFecha + " and Fechafin is null");
		if (dataTable.Rows.Count > 0)
		{
			TurnoID = Conversions.ToInteger(dataTable.Rows[0]["TurnoID"]);
			return true;
		}
		TurnoID = 0;
		return false;
	}

	public int returnTurno()
	{
		return Nro;
	}

	public void returnFechasXturno(ref DateTime fechaini, ref DateTime dateini, ref DateTime fechafin, ref DateTime datefin)
	{
		DataTable dataTable = BD.ConsultaVer("FechaIni,FechaFin,Meseros.Nombre as Mesero, Nro, observaciones, MontoIni, montoFin", "(Turnos inner join Meseros on Turnos.PersonalId=Meseros.MeseroID)", "turnoId =" + Conversions.ToString(TurnoID));
		if (dataTable.Rows.Count > 0)
		{
			fechaini = Conversions.ToDate(dataTable.Rows[0]["FechaIni"]);
			dateini = Conversions.ToDate(dataTable.Rows[0]["FechaIni"]);
			fechafin = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaFin"]), DateAndTime.Now));
			datefin = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaFin"]), DateAndTime.Now));
			Nro = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["nro"]), 0));
		}
		else
		{
			fechaini = DateAndTime.Now;
			dateini = DateAndTime.Now;
			fechafin = DateAndTime.Now;
			datefin = DateAndTime.Now;
			Nro = 0;
		}
	}

	public void returnFechasXturno(ref DateTime fechaini, ref DateTime fechafin, ref string Mesero, ref string observaciones, ref double montoIni, ref double montoFin, ref double montoIniDolar, ref double montoFinDolar)
	{
		DataTable dataTable = BD.ConsultaVer("FechaIni,FechaFin,Meseros.Nombre as Mesero, Nro, observaciones, MontoIni, montoFin", "(Turnos inner join Meseros on Turnos.PersonalId=Meseros.MeseroID)", "turnoId =" + Conversions.ToString(TurnoID));
		if (dataTable.Rows.Count > 0)
		{
			fechaini = Conversions.ToDate(dataTable.Rows[0]["FechaIni"]);
			fechafin = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaFin"]), DateAndTime.Now));
			Mesero = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Mesero"]), ""));
			Nro = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["nro"]), 0));
			montoIni = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["montoIni"]), 0));
			montoFin = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["montoFin"]), 0));
			observaciones = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["observaciones"]), 0));
		}
		else
		{
			fechaini = DateAndTime.Now;
			fechafin = DateAndTime.Now;
			Mesero = "";
			montoIni = 0.0;
			montoFin = 0.0;
			Nro = 0;
			observaciones = "";
		}
	}

	public DataTable DevolverTurnos()
	{
		if (configuration.gManejaTurnos)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer(" TurnoID, Int([FechaIni]) & ' - ' & Nro  & ' - ' & Meseros.Nombre ", "Turnos inner join Meseros on Turnos.PersonalId=Meseros.MeseroID ", "FechaIni >" + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(DateAndTime.Today.AddDays(-30.0)) + configuration.CaracterFecha, " TurnoId desc");
			}
			if (configuration.gMODO_ACCESS == 2)
			{
				return BD.ConsultaVer(" TurnoID, concat(cast(cast(FechaIni as date) as char(50)) , ' - ' , CAST(Nro AS CHAR(50))  , ' - ' , Meseros.Nombre   ) ", "Turnos inner join Meseros on Turnos.PersonalId=Meseros.MeseroID ", "FechaIni >" + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(DateAndTime.Today.AddDays(-30.0)) + configuration.CaracterFecha, " TurnoId desc");
			}
			return BD.ConsultaVer(" TurnoID, cast(cast(FechaIni as date) as varchar)  + ' - ' + CAST(Nro AS VARCHAR)  + ' - ' + Meseros.Nombre ", "Turnos inner join Meseros on Turnos.PersonalId=Meseros.MeseroID ", "FechaIni >" + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(DateAndTime.Today.AddDays(-30.0)) + configuration.CaracterFecha, " TurnoId desc");
		}
		return new DataTable();
	}

	public bool Finalizar1(DateTime FechaFin, string observaciones, double montoFinBS, double montoFinDolares)
	{
		bool result;
		try
		{
			BD.ConsultaModificar("Turnos", "FechaFin=" + VariableGeneral.ArmarFecha(FechaFin) + ",MontoFin=" + Conversion.Str(montoFinBS) + ",montoFinDolar=" + Conversion.Str(montoFinDolares) + ",Observaciones='" + observaciones + "',flagSync=NULL", "TurnoID=" + TurnoID);
			new clsPagos().deletePagosAgrupador();
			if (VariableGeneral.gSgteMesaVisible)
			{
				BD.ConsultaModificar("Mesas", "Activo=" + VariableGeneral.armarBolean(0), "Nombre like '%-%' and not nombre like '%-1' and id not in (select mesaId from Visitas where enMesa=" + VariableGeneral.armarBolean(1) + ")");
				BD.ConsultaModificar("Mesas", "Descripcion=''", "id not in (select mesaId from Visitas where enMesa=" + VariableGeneral.armarBolean(1) + ")");
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

	public int Iniciar(int personal, DateTime FechaIni, double MontoInibs, double MontoInidolares, bool turnoAm, ref bool HayPedidosProgramados)
	{
		checked
		{
			int result;
			try
			{
				string from = "";
				string to = "";
				VariableGeneral.workingWithDate(ref from, ref to, VariableGeneral._horaCierreTurno, FechaIni);
				int num = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(nro)", "Turnos", "FechaIni >= " + from).Rows[0][0]), 0), 1));
				if (num > 1)
				{
					DataTable dataTable = BD.ConsultaVer("top 1 FechaIni, FechaFin", "Turnos", "FechaIni >= " + from + " and FechaIni < " + to + " and PC like '" + MyProject.Computer.Name + "'", "FechaIni desc");
					if (dataTable.Rows.Count > 0 && !Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaFin"])))
					{
						DateTime dateTime = Conversions.ToDate(dataTable.Rows[0]["FechaFin"]);
						new ctlDetalleCuenta();
						DataTable dataTable2 = BD.ConsultaVer("count(*) as cant, min(hora) as HoraIni", "DetalleCuenta", "hora between " + VariableGeneral.ArmarFecha(dateTime) + " and " + VariableGeneral.ArmarFecha(FechaIni));
						if (Operators.ConditionalCompareObjectGreater(dataTable2.Rows[0]["cant"], 0, TextCompare: false))
						{
							FechaIni = Conversions.ToDate(dataTable2.Rows[0]["HoraIni"]);
							FechaIni = FechaIni.AddSeconds(-1.0);
							if (DateTime.Compare(FechaIni, dateTime) < 0)
							{
								FechaIni = dateTime;
							}
						}
					}
				}
				if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					TurnoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(TurnoID)", "Turnos").Rows[0][0]), 0), 1));
					BD.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(TurnoID) + "," + VariableGeneral.ArmarFecha(FechaIni) + ",", Conversions.ToString(num), ","), Conversions.ToString(personal), ",", Conversion.Str(MontoInibs), ",", Conversion.Str(MontoInidolares), ",'", MyProject.Computer.Name, "',", VariableGeneral.armarBolean(turnoAm)), "Turnos(TurnoID,FechaIni,Nro,PersonalID,MontoIni,MontoIniDolar,PC, TurnoAm)");
					TurnoID = Conversions.ToInteger(BD.ConsultaVer("max(TurnoID)", "Turnos").Rows[0][0]);
				}
				else
				{
					BD.ConsultaInsertar3(string.Concat(string.Concat(VariableGeneral.ArmarFecha(FechaIni) + ",", Conversions.ToString(num), ","), Conversions.ToString(personal), ",", Conversion.Str(MontoInibs), ",", Conversion.Str(MontoInidolares), ",'", MyProject.Computer.Name, "',", VariableGeneral.armarBolean(turnoAm)), "Turnos(FechaIni,Nro,PersonalID,MontoIni,MontoIniDolar,PC,TurnoAm)", ref TurnoID);
				}
				try
				{
					if (num == 1)
					{
						DataTable dataTable3 = new ctlMesas().ToReturnMesasYvisitasActivas(soloMesas: false, pedidoEnEspera: false, 0);
						int num2 = dataTable3.Rows.Count - 1;
						bool ManejarStock = default(bool);
						for (int i = 0; i <= num2; i++)
						{
							if (Information.IsDate(RuntimeHelpers.GetObjectValue(dataTable3.Rows[i]["Codigo"])) && DateTime.Compare(Conversions.ToDate(dataTable3.Rows[i]["Codigo"]).Date, DateAndTime.Today) == 0)
							{
								if (!HayPedidosProgramados)
								{
									Interaction.MsgBox("Hay entregas programadas para el día de hoy. se procedera a disminuir el inventario de esos productos");
								}
								HayPedidosProgramados = true;
								DataTable dataTable4 = new ctlDetalleCuenta().ToReturnProductosVendidosAnteriormente(Conversions.ToInteger(dataTable3.Rows[i]["VisitaID"]));
								int num3 = dataTable4.Rows.Count - 1;
								string Impresora;
								for (int j = 0; j <= num3; j++)
								{
									ctlProductos ctlProductos2 = new ctlProductos();
									int almacenID = 0;
									string name = Conversions.ToString(dataTable4.Rows[j]["nombre"]);
									int Sector = 0;
									double Precio = 0.0;
									Impresora = "";
									bool conRecipiente = false;
									bool esCombo = false;
									bool esPorPeso = false;
									bool EscogePersonal = false;
									ctlProductos2.ToReturnProductosPriceImpresoraByName1(0, name, ref Precio, ref Impresora, ref ManejarStock, ref conRecipiente, ref esCombo, ref esPorPeso, ref EscogePersonal, ref almacenID, ref Sector, 0);
									ctlProductos2.cargarDatos(almacenID);
									double Costo = 0.0;
									ctlDetalleCuenta obj = new ctlDetalleCuenta();
									obj.SetID(Conversions.ToInteger(dataTable4.Rows[j]["ID"]));
									DataRow dataRow;
									Precio = Conversions.ToDouble((dataRow = dataTable4.Rows[j])["Cantidad"]);
									double pago = 0.0;
									double debe = 0.0;
									obj.reducirStock(ctlProductos2, ParaLlevar: false, ref Precio, ref pago, ref debe, deCombo: false, telefono: false, ref Costo, 0, almacenID, desdeReporte: false, soyCombo: false);
									dataRow["Cantidad"] = Precio;
									obj.guardarCosto(Costo);
								}
								DataTable dgvPedido = new ctlDetalleCuenta().DevolverPedidoTotal(Conversions.ToInteger(dataTable3.Rows[i]["VisitaID"]));
								string txtMesa = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[i]["Descripcion"]), ""));
								int visitaId = Conversions.ToInteger(dataTable3.Rows[i]["VisitaID"]);
								Impresora = "";
								ImprimiendoComandas.printComandas(dgvPedido, aumentoEnCuenta: false, 0, "", paraLlevar: true, "", "Anticipado", txtMesa, imprimir: true, visitaId, "", ref Impresora);
							}
						}
					}
				}
				catch (Exception ex)
				{
					ProjectData.SetProjectError(ex);
					Exception ex2 = ex;
					Interaction.MsgBox("Problema con entregas programadas");
					ProjectData.ClearProjectError();
				}
				result = TurnoID;
			}
			catch (Exception ex3)
			{
				ProjectData.SetProjectError(ex3);
				Exception ex4 = ex3;
				result = 0;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public DataTable DevolverTurnoReporte1(int id)
	{
		return BD.ConsultaVer("select Turnos.TurnoID, FechaIni, Nro from Turnos where TurnoID=" + Conversions.ToString(id));
	}

	public bool GastoPerteneceAlTurno(DateTime fecha)
	{
		DataTable dataTable = BD.ConsultaVer("top 1 *", "Turnos", " PC like '" + MyProject.Computer.Name + "'", "FechaIni desc");
		if (dataTable.Rows.Count > 0 && Operators.ConditionalCompareObjectGreater(fecha, dataTable.Rows[0]["FechaIni"], TextCompare: false))
		{
			return true;
		}
		return false;
	}

	public bool getInfoReimprimir(ref DateTime FechaIni, ref double montoInibs, ref double montoInidol, ref int RespArqueo, ref int nro, ref int MeseroID)
	{
		DataTable dataTable = BD.ConsultaVer("*", "Turnos", "FechaIni = " + VariableGeneral.ArmarFecha(FechaIni) + "  and PC like '" + MyProject.Computer.Name + "'");
		if (dataTable.Rows.Count > 0)
		{
			FechaIni = Conversions.ToDate(dataTable.Rows[0]["FechaIni"]);
			montoInibs = Conversions.ToDouble(dataTable.Rows[0]["MontoIni"]);
			montoInidol = Conversions.ToDouble(dataTable.Rows[0]["MontoIniDolar"]);
			RespArqueo = Conversions.ToInteger(dataTable.Rows[0]["PersonalID"]);
			TurnoID = Conversions.ToInteger(dataTable.Rows[0]["TurnoID"]);
			nro = Conversions.ToInteger(dataTable.Rows[0]["Nro"]);
			MeseroID = Conversions.ToInteger(dataTable.Rows[0]["PersonalID"]);
			return true;
		}
		TurnoID = 0;
		return false;
	}

	public void ActualizarTurnos()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			BD.ConsultaModificar("Turnos inner join  Turnos as a1 on  a1.TurnoId= Turnos.TurnoId+1", " Turnos.fechaFin= DATEADD( 's',-1, a1.FechaIni) ", "Turnos.fechaFin is null and Turnos.FechaIni<" + VariableGeneral.ArmarFecha(DateAndTime.Today));
		}
		else if (configuration.gMODO_ACCESS == 2)
		{
			BD.ConsultaModificar("Turnos inner join  Turnos as a1 on  a1.TurnoId= Turnos.TurnoId+1", " Turnos.fechaFin= DATE_ADD(a1.FechaIni, INTERVAL  -1 SECOND) ", "Turnos.fechaFin is null and Turnos.FechaIni<" + VariableGeneral.ArmarFecha(DateAndTime.Today));
		}
		else
		{
			BD.ConsultaModificar("Turnos", " Turnos.fechaFin= DATEADD( ss,-1, a1.FechaIni)  from Turnos inner join  Turnos as a1 on  a1.TurnoId= Turnos.TurnoId+1", "Turnos.fechaFin is null and Turnos.FechaIni<" + VariableGeneral.ArmarFecha(DateAndTime.Today));
		}
	}

	public bool getultimaInfoCualquierPC(ref DateTime FechaIni)
	{
		DataTable dataTable = BD.ConsultaVer("top 1 *", "Turnos", "", "FechaIni desc");
		if (dataTable.Rows.Count > 0)
		{
			FechaIni = Conversions.ToDate(dataTable.Rows[0]["FechaIni"]);
			return true;
		}
		TurnoID = 0;
		FechaIni = DateAndTime.Now;
		return false;
	}

	~clsTurnos()
	{
		base.Finalize();
	}
}
