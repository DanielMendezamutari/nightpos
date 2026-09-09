using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsVisitas
{
	private int ID;

	private DateTime Fecha;

	private string MesaID;

	private bool enMesa;

	private string MesaAdicionalID;

	public int ClienteID;

	private string PersonaSinMesaID;

	private string ParaLlevarID;

	private int ImprimioCuenta;

	private string Observacion;

	private string TipoEnvioID;

	public int _ID
	{
		get
		{
			return ID;
		}
		set
		{
			ID = value;
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

	public bool _enMesa
	{
		get
		{
			return enMesa;
		}
		set
		{
			enMesa = value;
		}
	}

	public int _MesaID
	{
		get
		{
			if (Operators.CompareString(MesaID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(MesaID);
		}
		set
		{
			if (value == 0)
			{
				MesaID = "null";
			}
			else
			{
				MesaID = Conversions.ToString(value);
			}
		}
	}

	public int _MesaAdicionalID
	{
		get
		{
			if (Operators.CompareString(MesaAdicionalID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(MesaAdicionalID);
		}
		set
		{
			if (value == 0)
			{
				MesaAdicionalID = "null";
			}
			else
			{
				MesaAdicionalID = Conversions.ToString(value);
			}
		}
	}

	public int _PersonaSinMesaID
	{
		get
		{
			if (Operators.CompareString(PersonaSinMesaID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(PersonaSinMesaID);
		}
		set
		{
			if (value == 0)
			{
				PersonaSinMesaID = "null";
			}
			else
			{
				PersonaSinMesaID = Conversions.ToString(value);
			}
		}
	}

	public int _ParaLlevarID
	{
		get
		{
			if (Operators.CompareString(ParaLlevarID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(ParaLlevarID);
		}
		set
		{
			if (value == 0)
			{
				ParaLlevarID = "null";
			}
			else
			{
				ParaLlevarID = Conversions.ToString(value);
			}
		}
	}

	public int _ImprimioCuenta
	{
		get
		{
			return ImprimioCuenta;
		}
		set
		{
			ImprimioCuenta = value;
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

	public int _TipoEnvioID
	{
		get
		{
			if (Operators.CompareString(TipoEnvioID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(TipoEnvioID);
		}
		set
		{
			if (value == 0)
			{
				TipoEnvioID = "null";
			}
			else
			{
				TipoEnvioID = Conversions.ToString(value);
			}
		}
	}

	public clsVisitas()
	{
		_TipoEnvioID = 0;
	}

	public void llenarclase(ref string ClienteNombre, ref string ClienteApellido, ref string ClienteCodigo)
	{
		DataTable dataTable = ((configuration.gStyleBoliches1 != configuration.styleBolichesId.NatuLife) ? BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Visitas.MesaID As MesaID,Visitas.ClienteID,Clientes.Nombre As ClienteNombre,Clientes.Apellidos As ClienteApellido,Visitas.EnMesa,Visitas.PersonaSinMesaID As PersonaSinMesaID,Visitas.ParaLlevarID As ParaLlevarID, Visitas.MesaAdicionalID as MesaAdicionalID,ImprimioCuenta, Visitas.Observacion, Visitas.TipoEnvioID, Clientes.Codigo as ClienteCodigo", "(Visitas LEFT JOIN Clientes On Visitas.ClienteID = Clientes.ID) ", "Visitas.ID=" + ID) : BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Visitas.MesaID As MesaID,Visitas.ClienteID,Clientes_NT.Nombre As ClienteNombre,Clientes_NT.Apellidos As ClienteApellido,Visitas.EnMesa,Visitas.PersonaSinMesaID As PersonaSinMesaID,Visitas.ParaLlevarID As ParaLlevarID, Visitas.MesaAdicionalID as MesaAdicionalID,ImprimioCuenta, Visitas.Observacion , Visitas.TipoEnvioID, Clientes.Codigo as ClienteCodigo", "(Visitas LEFT JOIN Clientes_NT On Visitas.ClienteID = Clientes_NT.ID) ", "Visitas.ID=" + ID));
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"])) ? ((object)0) : dataTable.Rows[0]["ID"]);
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
			MesaID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MesaID"])) ? ((object)0) : dataTable.Rows[0]["MesaID"]);
			ClienteID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ClienteID"])) ? ((object)0) : dataTable.Rows[0]["ClienteID"]);
			ClienteNombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ClienteNombre"])) ? "" : dataTable.Rows[0]["ClienteNombre"]);
			ClienteApellido = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ClienteApellido"])) ? "" : dataTable.Rows[0]["ClienteApellido"]);
			ClienteCodigo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ClienteCodigo"])) ? "" : dataTable.Rows[0]["ClienteCodigo"]);
			enMesa = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EnMesa"])) ? ((object)false) : dataTable.Rows[0]["EnMesa"]);
			PersonaSinMesaID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PersonaSinMesaID"])) ? ((object)0) : dataTable.Rows[0]["PersonaSinMesaID"]);
			ParaLlevarID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ParaLlevarID"])) ? ((object)0) : dataTable.Rows[0]["ParaLlevarID"]);
			MesaAdicionalID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MesaAdicionalID"])) ? ((object)0) : dataTable.Rows[0]["MesaAdicionalID"]);
			ImprimioCuenta = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImprimioCuenta"])) ? ((object)0) : dataTable.Rows[0]["ImprimioCuenta"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			TipoEnvioID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoEnvioID"])) ? ((object)0) : dataTable.Rows[0]["TipoEnvioID"]);
		}
	}

	public DataTable ToReturn(DateTime inicio, DateTime fin, bool pedidos)
	{
		string text = "CONVERT(VARCHAR,";
		string text2 = "UPPER";
		if (configuration.gMODO_ACCESS == 1)
		{
			text = "CStr(";
			text2 = "UCase";
		}
		if (!pedidos)
		{
			if (configuration.gComidaRapida)
			{
				if (configuration.gMODO_ACCESS == 1)
				{
					return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa,TipoEnvios.Nombre as TipoEnvio, Clientes.Nombre, Clientes.Apellidos, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Pago)) as Pago, sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Debe)) as Debe , Meseros.Nombre as ImprimioCuenta, Visitas.Observacion, max(DetalleCuenta.Orden) as Orden , (select iif(max(Cuentas.Nombre) = min(Cuentas.Nombre) , max(Cuentas.Nombre)   , max(Cuentas.Nombre) + ' | ' + min(Cuentas.Nombre)) as ok from ((Cuentas inner join Pagos on Pagos.CuentaID=Cuentas.CuentaID) inner join DetalleCuenta as d1 on d1.ID = Pagos.detalleCuentaID ) where d1.VisitaID = Visitas.ID ) as TipoPago, Visitas.EnMesa", "((((((Visitas inner join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID )  left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID ) ", "Visitas.Fecha between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, Clientes.Nombre, Clientes.Apellidos , Meseros.Nombre,Visitas.Observacion,TipoEnvios.Nombre, Visitas.EnMesa");
				}
				return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa,TipoEnvios.Nombre as TipoEnvio, Clientes.Nombre, Clientes.Apellidos, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Pago end) as Pago, sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Debe end) as Debe, Meseros.Nombre as ImprimioCuenta, Visitas.Observacion, max(DetalleCuenta.Orden) as Orden , (select CASE WHEN max(Cuentas.Nombre) = min(Cuentas.Nombre) THEN max(Cuentas.Nombre)   ELSE max(Cuentas.Nombre) + ' | ' + min(Cuentas.Nombre) END AS ok from ((Cuentas inner join Pagos on Pagos.CuentaID=Cuentas.CuentaID) inner join DetalleCuenta as d1 on d1.ID = Pagos.detalleCuentaID ) where d1.VisitaID = Visitas.ID )  as TipoPago, Visitas.EnMesa", "((((((Visitas inner join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID )  left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID ) ", "Visitas.Fecha between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, Clientes.Nombre, Clientes.Apellidos , Meseros.Nombre,  Visitas.Observacion,TipoEnvios.Nombre, Visitas.EnMesa");
			}
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa, Clientes.Nombre, Clientes.Apellidos, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Pago)) as Pago, sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Debe)) as Debe, Meseros.Nombre as ImprimioCuenta, TipoEnvios.Nombre as TipoEnvio, Visitas.EnMesa ", "((((((((Visitas inner join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID ) left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID  ) ) )", "Visitas.Fecha between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, Clientes.Nombre, Clientes.Apellidos , Meseros.Nombre, TipoEnvios.Nombre, Visitas.EnMesa");
			}
			return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa, Clientes.Nombre, Clientes.Apellidos, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Pago end) as Pago, sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Debe end) as Debe, Meseros.Nombre as ImprimioCuenta, TipoEnvios.Nombre as TipoEnvio, Visitas.EnMesa, tab1.MaquinaPago ", "((((((((Visitas inner join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID ) left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID))) left join (select VisitaID, min(maquinapago) as MaquinaPago from DetalleCuenta left join Pagos on DetalleCuenta.id = Pagos.DetalleCuentaID group by VisitaID) as tab1 on tab1.VisitaID = Visitas.ID", "Visitas.Fecha between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, Clientes.Nombre, Clientes.Apellidos , Meseros.Nombre,TipoEnvios.Nombre, Visitas.EnMesa, tab1.MaquinaPago");
		}
		if (configuration.gComidaRapida)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa,TipoEnvios.Nombre as TipoEnvio, ParaLLevar.Nombre , ParaLLevar.NombreFactura, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura, sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Debe)) as Debe, Meseros.Nombre as ImprimioCuenta, Visitas.Observacion, max(DetalleCuenta.Orden) as Orden, (select   iif(max(Cuentas.Nombre) = min(Cuentas.Nombre) , max(Cuentas.Nombre)   , max(Cuentas.Nombre) + ' | ' + min(Cuentas.Nombre)) AS ok from ((Cuentas inner join Pagos on Pagos.CuentaID=Cuentas.CuentaID) inner join DetalleCuenta as d1 on d1.ID= Pagos.detalleCuentaID) where d1.VisitaID = Visitas.ID ) as TipoPago, Visitas.EnMesa", "(((((((Visitas inner join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID )  left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID ) left join ParaLLevar on Visitas.ParaLlevarID =ParaLLevar.ParaLlevarID) ", "ParaLLevar.HoraRecoger between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, ParaLLevar.Nombre , ParaLLevar.NombreFactura , Meseros.Nombre,  Visitas.Observacion,TipoEnvios.Nombre, Visitas.EnMesa");
			}
			return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa,TipoEnvios.Nombre as TipoEnvio, ParaLLevar.Nombre , ParaLLevar.NombreFactura, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Pago end) as Pago, sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Debe end) as Debe, Meseros.Nombre as ImprimioCuenta, Visitas.Observacion, max(DetalleCuenta.Orden) as Orden,(select CASE WHEN max(Cuentas.Nombre) = min(Cuentas.Nombre) THEN max(Cuentas.Nombre)   ELSE max(Cuentas.Nombre) + ' | ' + min(Cuentas.Nombre) END AS ok from ((Cuentas inner join Pagos on Pagos.CuentaID=Cuentas.CuentaID) inner join DetalleCuenta as d1 on d1.ID = Pagos.detalleCuentaID ) where d1.VisitaID = Visitas.ID )  as TipoPago, Visitas.EnMesa", "(((((((Visitas inner join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID )  left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID ) left join ParaLLevar on Visitas.ParaLlevarID =ParaLLevar.ParaLlevarID) ", "ParaLLevar.HoraRecoger between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, ParaLLevar.Nombre , ParaLLevar.NombreFactura , Meseros.Nombre,  Visitas.Observacion,TipoEnvios.Nombre, Visitas.EnMesa");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa, ParaLLevar.Nombre , ParaLLevar.NombreFactura, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Pago)) as Pago, sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Debe)) as Debe, Meseros.Nombre as ImprimioCuenta, TipoEnvios.Nombre as TipoEnvio, Visitas.EnMesa", "(((((((((Visitas inner join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID ) left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID  ) ) ) left join ParaLLevar on Visitas.ParaLlevarID =ParaLLevar.ParaLlevarID)", "ParaLLevar.HoraRecoger between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, ParaLLevar.Nombre , ParaLLevar.NombreFactura , Meseros.Nombre, TipoEnvios.Nombre, Visitas.EnMesa");
		}
		return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa, ParaLLevar.Nombre , ParaLLevar.NombreFactura, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Pago end) as Pago, sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Debe end) as Debe, Meseros.Nombre as ImprimioCuenta, TipoEnvios.Nombre as TipoEnvio, Visitas.EnMesa ", "(((((((((Visitas inner join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID ) left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID  ) ) ) left join ParaLLevar on Visitas.ParaLlevarID =ParaLLevar.ParaLlevarID)", "ParaLLevar.HoraRecoger between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, ParaLLevar.Nombre , ParaLLevar.NombreFactura , Meseros.Nombre, TipoEnvios.Nombre, Visitas.EnMesa");
	}

	public DataTable ToReturnCompleto(DateTime inicio, DateTime fin, bool pedidos)
	{
		string text = "CONVERT(VARCHAR,";
		string text2 = "UPPER";
		if (configuration.gMODO_ACCESS == 1)
		{
			text = "CStr(";
			text2 = "UCase";
		}
		if (!pedidos)
		{
			if (configuration.gComidaRapida)
			{
				if (configuration.gMODO_ACCESS == 1)
				{
					return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa,TipoEnvios.Nombre as TipoEnvio, Clientes.Nombre, Clientes.Apellidos, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Pago)) as Pago, sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Debe)) as Debe , Meseros.Nombre as ImprimioCuenta, Visitas.Observacion, max(DetalleCuenta.Orden) as Orden , (select iif(max(Cuentas.Nombre) = min(Cuentas.Nombre) , max(Cuentas.Nombre)   , max(Cuentas.Nombre) + ' | ' + min(Cuentas.Nombre)) as ok from ((Cuentas inner join Pagos on Pagos.CuentaID=Cuentas.CuentaID) inner join DetalleCuenta as d1 on d1.ID = Pagos.detalleCuentaID ) where d1.VisitaID = Visitas.ID ) as TipoPago, Visitas.EnMesa", "((((((Visitas left join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID )  left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID ) ", "Visitas.Fecha between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, Clientes.Nombre, Clientes.Apellidos , Meseros.Nombre,Visitas.Observacion,TipoEnvios.Nombre, Visitas.EnMesa");
				}
				return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa,TipoEnvios.Nombre as TipoEnvio, Clientes.Nombre, Clientes.Apellidos, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Pago end) as Pago, sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Debe end) as Debe, Meseros.Nombre as ImprimioCuenta, Visitas.Observacion, max(DetalleCuenta.Orden) as Orden , (select CASE WHEN max(Cuentas.Nombre) = min(Cuentas.Nombre) THEN max(Cuentas.Nombre)   ELSE max(Cuentas.Nombre) + ' | ' + min(Cuentas.Nombre) END AS ok from ((Cuentas inner join Pagos on Pagos.CuentaID=Cuentas.CuentaID) inner join DetalleCuenta as d1 on d1.ID = Pagos.detalleCuentaID ) where d1.VisitaID = Visitas.ID )  as TipoPago, Visitas.EnMesa", "((((((Visitas left join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID )  left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID ) ", "Visitas.Fecha between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, Clientes.Nombre, Clientes.Apellidos , Meseros.Nombre,  Visitas.Observacion,TipoEnvios.Nombre, Visitas.EnMesa");
			}
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa, Clientes.Nombre, Clientes.Apellidos, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Pago)) as Pago, sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Debe)) as Debe, Meseros.Nombre as ImprimioCuenta, TipoEnvios.Nombre as TipoEnvio, Visitas.EnMesa ", "((((((((Visitas left join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID ) left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID  ) ) )", "Visitas.Fecha between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, Clientes.Nombre, Clientes.Apellidos , Meseros.Nombre, TipoEnvios.Nombre, Visitas.EnMesa");
			}
			return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa, Clientes.Nombre, Clientes.Apellidos, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Pago end) as Pago, sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Debe end) as Debe, Meseros.Nombre as ImprimioCuenta, TipoEnvios.Nombre as TipoEnvio, Visitas.EnMesa ", "((((((((Visitas left join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID ) left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID  ) ) )", "Visitas.Fecha between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, Clientes.Nombre, Clientes.Apellidos , Meseros.Nombre,TipoEnvios.Nombre, Visitas.EnMesa");
		}
		if (configuration.gComidaRapida)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa,TipoEnvios.Nombre as TipoEnvio, ParaLLevar.Nombre , ParaLLevar.NombreFactura, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura, sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Debe)) as Debe, Meseros.Nombre as ImprimioCuenta, Visitas.Observacion, max(DetalleCuenta.Orden) as Orden, (select   iif(max(Cuentas.Nombre) = min(Cuentas.Nombre) , max(Cuentas.Nombre)   , max(Cuentas.Nombre) + ' | ' + min(Cuentas.Nombre)) AS ok from ((Cuentas inner join Pagos on Pagos.CuentaID=Cuentas.CuentaID) inner join DetalleCuenta as d1 on d1.ID= Pagos.detalleCuentaID) where d1.VisitaID = Visitas.ID ) as TipoPago, Visitas.EnMesa", "(((((((Visitas left join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID )  left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID ) left join ParaLLevar on Visitas.ParaLlevarID =ParaLLevar.ParaLlevarID) ", "ParaLLevar.HoraRecoger between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, ParaLLevar.Nombre , ParaLLevar.NombreFactura , Meseros.Nombre,  Visitas.Observacion,TipoEnvios.Nombre, Visitas.EnMesa");
			}
			return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa,TipoEnvios.Nombre as TipoEnvio, ParaLLevar.Nombre , ParaLLevar.NombreFactura, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Pago end) as Pago, sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Debe end) as Debe, Meseros.Nombre as ImprimioCuenta, Visitas.Observacion, max(DetalleCuenta.Orden) as Orden,(select CASE WHEN max(Cuentas.Nombre) = min(Cuentas.Nombre) THEN max(Cuentas.Nombre)   ELSE max(Cuentas.Nombre) + ' | ' + min(Cuentas.Nombre) END AS ok from ((Cuentas inner join Pagos on Pagos.CuentaID=Cuentas.CuentaID) inner join DetalleCuenta as d1 on d1.ID = Pagos.detalleCuentaID ) where d1.VisitaID = Visitas.ID )  as TipoPago, Visitas.EnMesa", "(((((((Visitas left join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID )  left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID ) left join ParaLLevar on Visitas.ParaLlevarID =ParaLLevar.ParaLlevarID) ", "ParaLLevar.HoraRecoger between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, ParaLLevar.Nombre , ParaLLevar.NombreFactura , Meseros.Nombre,  Visitas.Observacion,TipoEnvios.Nombre, Visitas.EnMesa");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa, ParaLLevar.Nombre , ParaLLevar.NombreFactura, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Pago)) as Pago, sum(iif(DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + ",0, DetalleCuenta.Debe)) as Debe, Meseros.Nombre as ImprimioCuenta, TipoEnvios.Nombre as TipoEnvio, Visitas.EnMesa", "(((((((((Visitas left join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID ) left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID  ) ) ) left join ParaLLevar on Visitas.ParaLlevarID =ParaLLevar.ParaLlevarID)", "ParaLLevar.HoraRecoger between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, ParaLLevar.Nombre , ParaLLevar.NombreFactura , Meseros.Nombre, TipoEnvios.Nombre, Visitas.EnMesa");
		}
		return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa, ParaLLevar.Nombre , ParaLLevar.NombreFactura, (select max( " + text + " NroFactura)  + ' - ' + " + text2 + "(Facturas.Nombre)) from Facturas where Facturas.VisitaID=Visitas.id and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + " ) as Factura,sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Pago end) as Pago, sum(case when DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(1) + " then 0 else DetalleCuenta.Debe end) as Debe, Meseros.Nombre as ImprimioCuenta, TipoEnvios.Nombre as TipoEnvio, Visitas.EnMesa ", "(((((((((Visitas left join DetalleCuenta on DetalleCuenta.VisitaID= Visitas.ID) left join Clientes on Clientes.ID = Visitas.ClienteID) ) LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID ) left join Meseros on Visitas.ImprimioCuenta =Meseros.MeseroID ) left join TipoEnvios  on Visitas.TipoEnvioID = TipoEnvios.TipoEnvioID  ) ) ) left join ParaLLevar on Visitas.ParaLlevarID =ParaLLevar.ParaLlevarID)", "ParaLLevar.HoraRecoger between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin), "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, ParaLLevar.Nombre , ParaLLevar.NombreFactura , Meseros.Nombre, TipoEnvios.Nombre, Visitas.EnMesa");
	}

	public DataTable ToReturnNatulife(DateTime inicio, DateTime fin)
	{
		return BD.ConsultaVer("Visitas.ID,Visitas.Fecha,Mesas.ID as mesaID, Mesas.Nombre as Mesa, Clientes_NT.Nombre, Clientes_NT.Apellidos, max(Facturas.NroFactura) as Factura,sum(DetalleCuenta.Pago) as Total, sum(DetalleCuenta.Debe) as Debe", "(((Visitas LEFT JOIN Mesas on Mesas.ID=Visitas.MesaID) left join Clientes_NT on Clientes_NT.ID = Visitas.ClienteID) left join Facturas on (Facturas.VisitaId=Visitas.ID and Facturas.Anulada=" + VariableGeneral.armarBolean(0) + ")) left join DetalleCuenta on (DetalleCuenta.VisitaID= Visitas.ID )", ("Visitas.Fecha between " + VariableGeneral.ArmarFecha(inicio) + " and " + VariableGeneral.ArmarFecha(fin)) ?? "", "Visitas.Fecha", "Visitas.ID,Visitas.Fecha,Mesas.ID, Mesas.Nombre, Clientes_NT.Nombre, Clientes_NT.Apellidos");
	}

	public DataTable ToReturnVisitasXcliente(int clienteID)
	{
		return BD.ConsultaVer("Visitas.Id, Fecha, sum(Pago+Debe) as Total, max(Facturas.NroFactura) as NroFactura, sum(Facturas.Monto) as Fact_Monto", "(Visitas inner join DetalleCuenta on (DetalleCuenta.VisitaID = Visitas.Id and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + ")) left join Facturas on (Facturas.VisitaID = Visitas.id and Facturas.Anulada =" + VariableGeneral.armarBolean(0) + ")", "Visitas.ClienteID=" + Conversions.ToString(clienteID), "Visitas.Id desc", "Visitas.Id,Fecha");
	}

	public DataTable ToReturn()
	{
		return BD.ConsultaVer("Visitas.ID,Visitas.Fecha, MesaID", "Visitas");
	}

	public bool esNuevoDia()
	{
		string from = "";
		string to = "";
		VariableGeneral.workingWithDate(ref from, ref to, VariableGeneral._horaCierreTurno, DateAndTime.Now);
		DataTable dataTable = ((DateAndTime.Now.Hour < 6) ? BD.ConsultaVer("count(*)  as contador", "Visitas", "Visitas.Fecha Between " + from + " And " + to + " ") : BD.ConsultaVer("count(*)  as contador", "Visitas", "Visitas.Fecha Between " + VariableGeneral.ArmarFecha(DateAndTime.Today.AddHours(6.0)) + " And " + VariableGeneral.ArmarFecha(DateAndTime.Now) + " "));
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		bool num = Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["contador"]), 0), 0, TextCompare: false);
		if (num & !configuration.gManejaTurnos)
		{
			new clsPagos().deletePagosAgrupador();
		}
		return num;
	}

	public bool hayCuentasAbiertas(ref bool ParaLlevar)
	{
		if (Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("count(*)  as contador", "Visitas", "MesaID>1 and EnMesa = " + VariableGeneral.armarBolean(1)).Rows[0][0], 0, TextCompare: false))
		{
			string from = "";
			string to = "";
			VariableGeneral.workingWithDate(ref from, ref to, VariableGeneral._horaCierreTurno, DateAndTime.Now);
			if (Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("count(*)  as contador", "Visitas inner join ParaLLevar on ParaLLevar.ParaLlevarID = Visitas.ParaLlevarID ", "EnMesa = " + VariableGeneral.armarBolean(1) + " and ParaLLevar.HoraRecoger <= " + to + " ").Rows[0][0], 0, TextCompare: false))
			{
				return false;
			}
			ParaLlevar = true;
			return true;
		}
		return true;
	}

	public string NroCuentasAbiertas()
	{
		string text = "";
		DataTable dataTable = BD.ConsultaVer("count(*)  as contador", "Visitas", "MesaID>1 and EnMesa = " + VariableGeneral.armarBolean(1));
		DataTable dataTable2 = BD.ConsultaVer("count(*)  as contador", "Visitas inner join ParaLLevar on ParaLLevar.ParaLlevarID = Visitas.ParaLlevarID ", ("EnMesa = " + VariableGeneral.armarBolean(1)) ?? "");
		if (Conversions.ToDouble(dataTable.Rows[0][0].ToString()) != 0.0)
		{
			text = " - " + dataTable.Rows[0][0].ToString() + "  Mesas Abiertas ";
		}
		if (Conversions.ToDouble(dataTable2.Rows[0][0].ToString()) != 0.0)
		{
			text = text + " - " + dataTable2.Rows[0][0].ToString() + "  Pedidos para llevar ";
		}
		return text;
	}

	public DataTable ToReturnLastVisitaActivaByMesaID(int mesaID)
	{
		return BD.ConsultaVer("top 1 Visitas.ID", "(Visitas INNER JOIN Mesas On Mesas.ID=Visitas.MesaID)", "Mesas.ID =" + Conversions.ToString(mesaID) + " And  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + " ", "Visitas.Fecha desc");
	}

	public DataTable ClienteTieneVisitaHoy(int prodId)
	{
		return BD.ConsultaVer("top 1 Visitas.ID, Fecha", "Visitas inner join detalleCuenta On Visitas.Id=DetalleCuenta.visitaId", "Visitas.ClienteID=" + Conversions.ToString(ClienteID) + " And DetalleCuenta.ProductoId=" + Conversions.ToString(prodId), "Visitas.Fecha desc");
	}

	public DataTable ClienteTieneVisitaHoyPensionados()
	{
		return BD.ConsultaVer("top 1 Visitas.ID, isnull( UsoPaquetes.FechaUso, DATEADD (DAY ,-10,  GETDATE ()) ) as FechaUso ", "  \r\n                             Visitas inner join detalleCuenta On Visitas.Id=DetalleCuenta.visitaId \r\n                            left join DetalleCuentas_Paquetes on DetalleCuenta.ID=DetalleCuentas_Paquetes.DetalleCuentaID \r\n                            left join UsoPaquetes on DetalleCuentas_Paquetes.DetalleCuenta_PaqueteID =UsoPaquetes.DetalleCuenta_PaqueteID ", "Visitas.ClienteID=" + Conversions.ToString(ClienteID), "UsoPaquetes.FechaUso desc");
	}

	public DataTable ToReturnLastVisitaActivaByMesaCodigo(string codigoMesa)
	{
		if (configuration.gCodigosMesasNumericos)
		{
			double num = Conversions.ToDouble(codigoMesa.Replace(".", ","));
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("top 1 Visitas.ID, Visitas.Fecha", "(Visitas INNER JOIN Mesas On Mesas.ID=Visitas.MesaID)", " CDbl(Mesas.codigo)= " + Conversion.Str(num) + " And  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + " ", "Visitas.Fecha desc");
			}
			return BD.ConsultaVer("top 1 Visitas.ID, Visitas.Fecha", "(Visitas INNER JOIN Mesas On Mesas.ID=Visitas.MesaID)", " cast(Mesas.codigo As float) = " + Conversion.Str(num) + " And  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + " ", "Visitas.Fecha desc");
		}
		return BD.ConsultaVer("top 1 Visitas.ID, Visitas.Fecha", "(Visitas INNER JOIN Mesas on Mesas.ID=Visitas.MesaID)", "Mesas.codigo like '" + codigoMesa + "' and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + " ", "Visitas.Fecha desc");
	}

	public DataTable ToReturnLastVisitaActivaByMesaCodigoOLD(string codigoMesa)
	{
		return BD.ConsultaVer("top 1 Visitas.ID,Visitas.Fecha, Visitas.MesaID, Clientes.Nombre, Clientes.Apellidos", "(Visitas INNER JOIN Mesas on Mesas.ID=Visitas.MesaID) left join Clientes on Clientes.ID = Visitas.ClienteID", "Mesas.codigo like '" + codigoMesa + "' and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + " ", "Visitas.Fecha desc");
	}

	public int responsabilizar()
	{
		int result;
		try
		{
			if (ClienteID == 0)
			{
				BD.ConsultaModificar("Visitas", "ClienteID=NULL,flagSync=NULL", "ID=" + ID);
			}
			else
			{
				BD.ConsultaModificar("Visitas", "ClienteID=" + Conversions.ToString(ClienteID) + ",flagSync=NULL", "ID=" + ID);
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

	public int juntarCuentas(int VisitaID)
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", "VisitaID=" + Conversions.ToString(VisitaID) + ",flagSync=NULL", "VisitaID=" + ID);
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

	public int cambiarMesa()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Visitas", "MesaID=" + MesaID + ",MesaAdicionalID=null,flagSync=NULL", "ID=" + ID);
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

	public int UpdateFechaVisita()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Visitas", "Fecha=" + VariableGeneral.ArmarFecha(DateAndTime.Now) + ",flagSync=NULL", "ID=" + ID);
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

	public int Modify()
	{
		int result;
		try
		{
			string from = "";
			string to = "";
			VariableGeneral.workingWithDate(ref from, ref to, VariableGeneral._horaCierreTurno, Fecha);
			if (Observacion.Length > 100)
			{
				Observacion = Observacion.Substring(0, 99);
			}
			BD.ConsultaModificar("Visitas", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",DiaKey=" + from + ",MesaID=" + MesaID + ",PersonaSinMesaID=" + PersonaSinMesaID + ",MesaAdicionalID=" + MesaAdicionalID + ",ParaLlevarID=" + ParaLlevarID + ", observacion= '" + Observacion + "',flagSync=NULL", "ID=" + ID);
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

	public int ChangePAraLlevarId()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Visitas", "ParaLlevarID=" + ParaLlevarID + ",flagSync=NULL", "ID=" + ID);
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

	public int Insert()
	{
		checked
		{
			int result;
			try
			{
				string from = "";
				string to = "";
				VariableGeneral.workingWithDate(ref from, ref to, VariableGeneral._horaCierreTurno, Fecha);
				if ((configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Soboce))
				{
					ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ID)", "Visitas").Rows[0][0]), 0));
					ID++;
					BD.ConsultaInsertar(string.Concat(Conversions.ToString(ID) + ",", VariableGeneral.ArmarFecha(Fecha), ",", from, ",", MesaID, ",NULL,", VariableGeneral.armarBolean(1), ",", PersonaSinMesaID, ",", MesaAdicionalID, ",", ParaLlevarID, ",", Conversions.ToString(ImprimioCuenta), ", '", Observacion, "',", TipoEnvioID), "Visitas(ID, Fecha, DiaKey, MesaID, ClienteID, EnMesa, PersonaSinMesaID, MesaAdicionalID, ParaLlevarID,ImprimioCuenta,Observacion,TipoEnvioID)");
					result = ID;
				}
				else
				{
					BD.ConsultaInsertar3(VariableGeneral.ArmarFecha(Fecha) + "," + from + "," + MesaID + ",NULL," + VariableGeneral.armarBolean(1) + "," + PersonaSinMesaID + "," + MesaAdicionalID + "," + ParaLlevarID + "," + Conversions.ToString(ImprimioCuenta) + ", '" + Observacion + "'," + TipoEnvioID, "Visitas( Fecha, DiaKey, MesaID, ClienteID, EnMesa, PersonaSinMesaID, MesaAdicionalID, ParaLlevarID,ImprimioCuenta,Observacion,TipoEnvioID)", ref ID);
					result = ID;
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
	}

	public bool estaenMesa()
	{
		bool result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("EnMesa", "Visitas", "ID=" + ID);
			result = dataTable.Rows.Count != 0 && Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), false));
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

	public int Ocupar()
	{
		checked
		{
			int result;
			try
			{
				int num = 0;
				DataTable dataTable = BD.ConsultaVer("clienteID,sum(debe) as deuda, sum(pago) as pago, MesaID ", "(Visitas left join DetalleCuenta on DetalleCuenta.VisitaID =Visitas.id)", "Visitas.id = " + Conversions.ToString(ID), "", "ClienteID,MesaID");
				double value = 0.0;
				if (dataTable.Rows.Count > 0)
				{
					value = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["pago"]), 0));
					Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["deuda"]), 0));
				}
				double value2 = new clsPagos().MontoPagado(ID);
				if (dataTable.Rows.Count <= 0)
				{
					goto IL_0286;
				}
				num = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MesaID"]), 0));
				if (Operators.ConditionalCompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["deuda"]), 0), 0, TextCompare: false))
				{
					if (!Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["clienteID"])) || enMesa)
					{
						goto IL_0286;
					}
					Interaction.MsgBox("No deberia poder cerrar esta mesa, contactarse con el administrador del sistema");
					result = 0;
				}
				else
				{
					if (Math.Round(value, 0) == Math.Round(value2, 0) || enMesa)
					{
						goto IL_0286;
					}
					DataTable dataTable2 = new ctlDetalleCuenta().ToReturnDetalleCuentaFromVisitaIDparaBorrar1(ID, MyProject.Computer.Name);
					int num2 = dataTable2.Rows.Count - 1;
					for (int i = 0; i <= num2; i++)
					{
						clsPagos obj = new clsPagos();
						obj._DetalleCuentaID = Conversions.ToInteger(dataTable2.Rows[i]["ID"]);
						obj.EliminarXDetalleCuentaID1("Ocupar Mesa", 0);
					}
					BD.ConsultaModificar("DetalleCuenta", "debe=debe+Pago", "VisitaID=" + Conversions.ToString(ID));
					BD.ConsultaModificar("DetalleCuenta", "Pago=0,Cerrada=" + VariableGeneral.armarBolean(0), "VisitaID=" + Conversions.ToString(ID));
					BD.ConsultaModificar("visitas", "Descuento=0", "id=" + Conversions.ToString(ID));
					Interaction.MsgBox("Diff en pagos, No deberia poder cerrar esta mesa, vuelva a gestionar el pago, contactarse con el administrador del sistema");
					result = 0;
				}
				goto end_IL_0000;
				IL_0286:
				if ((((configuration.gMesasVisibilidad == configuration.MesasVisibilidad.VeoMesasLibresMasMisMesas) & !configuration.gComidaRapida) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Hapo)) && !enMesa && num > 0)
				{
					clsMesas obj2 = new clsMesas();
					obj2._ID = num;
					obj2._responsableID = 0;
					obj2.cambiarMesero();
				}
				if (VariableGeneral.gSgteMesaVisible)
				{
					if (!enMesa && num > 0)
					{
						ctlMesas ctlMesas2 = new ctlMesas();
						ctlMesas2.SetID(num);
						ctlMesas2.loadMesaPorID();
						string nombre = ctlMesas2.GetNombre();
						if (nombre.Contains("-") & !nombre.EndsWith("-1"))
						{
							ctlMesas2.setActivo("", activo: false);
						}
						else
						{
							ctlMesas2.setDescripcion("");
						}
					}
				}
				else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vulcanica)
				{
					ctlMesas obj3 = new ctlMesas();
					obj3.SetID(num);
					obj3.setDescripcion("");
				}
				BD.ConsultaModificar("Visitas", "EnMesa=" + VariableGeneral.armarBolean(enMesa) + ", flagSync = NULL", "ID=" + ID);
				result = 1;
				end_IL_0000:;
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

	public int Delete()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Visitas", "ID = " + ID) == 0)
			{
				Interaction.MsgBox("Can't delete , is in use");
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

	public bool VerificarFecha()
	{
		bool result;
		try
		{
			result = DateTime.Compare(Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(Fecha) ", "Visitas").Rows[0][0]), DateAndTime.Now)), DateAndTime.Now) <= 0;
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

	public bool DescuentoEnFactura(double Descuento)
	{
		bool result;
		try
		{
			BD.ConsultaModificar("Visitas", "Descuento=" + Conversion.Str(Descuento) + ",flagSync=NULL", "ID=" + ID);
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

	public void UpdateImprimioCuenta(int meseroID)
	{
		try
		{
			BD.ConsultaModificar("Visitas", "ImprimioCuenta=" + Conversions.ToString(meseroID) + ",flagSync=NULL", "ID=" + ID);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
	}

	public void UpdateImprimioFactura(int meseroID)
	{
		try
		{
			BD.ConsultaModificar("Visitas", "ImprimioCuenta=" + Conversions.ToString(meseroID) + ",flagSync=NULL", "ID=" + ID + " and (ImprimioCuenta is null or ImprimioCuenta=0) ");
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
	}

	public bool DevolverImprimioCuenta()
	{
		bool result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("ImprimioCuenta", "Visitas", "ID=" + ID);
			result = dataTable.Rows.Count > 0 && Operators.ConditionalCompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0), 0, TextCompare: false);
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

	public bool DevolverUsoServicio()
	{
		bool result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("pago + debe", "DetalleCuenta", "VisitaID=" + ID + " and ProductoID = 1");
			result = dataTable.Rows.Count > 0 && Operators.ConditionalCompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0), 0, TextCompare: false);
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

	public bool TipoEnvioesMesa(int IDTipoEnvio)
	{
		bool result;
		try
		{
			result = BD.ConsultaVer("Nombre", "TipoEnvios", "nombre like '%mesa%' and TipoEnvioID = " + IDTipoEnvio).Rows.Count > 0;
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

	public bool TipoEnvioesPeYa()
	{
		bool result;
		try
		{
			result = BD.ConsultaVer("Nombre", "TipoEnvios", "nombre like '%pedidos ya%' and TipoEnvioID = " + TipoEnvioID.ToString()).Rows.Count > 0;
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

	public int DevolverclienteID()
	{
		int result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("ClienteID", "Visitas", "Visitas.ID=" + ID);
			result = ((dataTable.Rows.Count > 0) ? Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0)) : 0);
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

	public string DevolverQuienImprimioCuenta()
	{
		string result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("Meseros.Nombre", "Visitas inner join Meseros on (Meseros.meseroID=Visitas.ImprimioCuenta)", "Visitas.ID=" + ID);
			result = ((dataTable.Rows.Count <= 0) ? "" : Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0)));
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

	public bool yaAbrioHoy(DateTime fecha)
	{
		if (BD.ConsultaVer("*", "Visitas", "(fecha between " + VariableGeneral.ArmarFecha(fecha) + " and " + VariableGeneral.ArmarFecha(DateAndTime.Now) + ") And  MesaID = " + MesaID + " and EnMesa=" + VariableGeneral.armarBolean(0)).Rows.Count == 0)
		{
			return false;
		}
		return true;
	}

	public string getIdentificadorXnroTurno(string nroTurno)
	{
		clsTurnos obj = new clsTurnos();
		double montoInibs = 0.0;
		double montoInidol = 0.0;
		int RespArqueo = 0;
		DateTime FechaIni = default(DateTime);
		obj.getInfoTurnoActual(ref FechaIni, ref montoInibs, ref montoInidol, ref RespArqueo);
		DataTable dataTable = BD.ConsultaVer("max(Identificador)", "Visitas inner join DetalleCuenta on DetalleCuenta.VisitaId=Visitas.Id", "fecha >= " + VariableGeneral.ArmarFecha(FechaIni) + " And  Orden = " + nroTurno);
		if (dataTable.Rows.Count == 0)
		{
			return "";
		}
		return Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), ""));
	}

	public int BuscarParaLLevarID()
	{
		int result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("ParaLLevarID", "Visitas", "Visitas.ID=" + ID);
			result = ((dataTable.Rows.Count > 0) ? Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0)) : 0);
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

	public int ModificarIdentificador(string Identificador)
	{
		int result;
		try
		{
			if (Identificador.Length > 50)
			{
				Identificador = Identificador.Substring(0, 49);
			}
			BD.ConsultaModificar("Visitas", " Identificador = '" + Identificador + "',flagSync=NULL", "ID=" + ID);
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

	public int ModificarObservacion()
	{
		int result;
		try
		{
			if (Observacion.Length > 100)
			{
				Observacion = Observacion.Substring(0, 99);
			}
			BD.ConsultaModificar("Visitas", " observacion= '" + Observacion + "',flagSync=NULL", "ID=" + ID);
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

	public int ModificarTipoEnvio()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Visitas", "TipoEnvioID = " + TipoEnvioID + ",flagSync=NULL", "ID=" + ID);
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

	public string DevolverTipoEnvio()
	{
		string result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("TipoEnvios.Nombre", "visitas inner join TipoEnvios on Visitas.TipoEnvioID =TipoEnvios.TipoEnvioID", "Visitas.ID=" + ID);
			result = ((dataTable.Rows.Count <= 0) ? "-" : VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), "MESA").ToString().ToUpper());
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = "-";
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public string DevolverTipoEnvioxTipoID()
	{
		string result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("Nombre", "TipoEnvios", "TipoEnvioID=" + TipoEnvioID.ToString());
			result = ((dataTable.Rows.Count <= 0) ? "-" : VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), "MESA").ToString().ToUpper());
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = "-";
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int DevolverObservacionNroCuenta(DateTime fecha)
	{
		int result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("select  Observacion from visitas\r\n                                                    where ID =" + ID);
			if (dataTable.Rows.Count > 0)
			{
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), ""), "", TextCompare: false))
				{
					result = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
				}
				else
				{
					DataTable dataTable2 = BD.ConsultaVer("select  max(Observacion) as cuenta from visitas\r\n                                                    where DiaKey >=" + VariableGeneral.ArmarFecha(fecha));
					result = ((dataTable2.Rows.Count <= 0) ? 1 : ((!Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0][0]), ""), "", TextCompare: false)) ? 1 : Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0][0]), 0), 1))));
				}
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
			result = 1;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public void CabecerSacNet(int visitaID, int AgruparPagoID, int facturaID, ref string NIT, ref string Nombre, ref int clientID, ref DateTime fecha, ref string email, ref int TipoDocumento, ref int codigoMetodoPago, ref string numeroTarjeta, ref string codigoFact, ref string Monto)
	{
		bool flag = false;
		double num = 0.0;
		if (facturaID > 0)
		{
			DataTable dataTable = BD.ConsultaVer("select facturas.FechaEmision as fecha,facturas.NIT, facturas.Nombre, facturas.Correo, Facturas.TipoDocumentoID, Facturas.Monto, Facturas.montoGiftCard,Codigo\r\n                                from facturas where FacturaID=" + Conversions.ToString(facturaID));
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? "" : dataTable.Rows[0]["NIT"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
			email = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Correo"])) ? "" : dataTable.Rows[0]["Correo"]);
			TipoDocumento = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoDocumentoID"])) ? ((object)0) : dataTable.Rows[0]["TipoDocumentoID"]);
			Monto = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["monto"])) ? ((object)0) : dataTable.Rows[0]["monto"]);
			num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoGiftCard"])) ? ((object)0) : dataTable.Rows[0]["MontoGiftCard"]);
			codigoFact = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["codigo"])) ? "" : dataTable.Rows[0]["codigo"]);
			flag = true;
		}
		else if (AgruparPagoID > 0)
		{
			DataTable dataTable = BD.ConsultaVer("select facturas.FechaEmision as fecha,facturas.NIT, facturas.Nombre, facturas.Correo, Facturas.TipoDocumentoID, Facturas.Monto, Facturas.montoGiftCard,Codigo\r\n                                from facturas where AgruparPagoID=" + Conversions.ToString(AgruparPagoID));
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? "" : dataTable.Rows[0]["NIT"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
			email = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Correo"])) ? "" : dataTable.Rows[0]["Correo"]);
			TipoDocumento = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoDocumentoID"])) ? ((object)0) : dataTable.Rows[0]["TipoDocumentoID"]);
			Monto = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["monto"])) ? ((object)0) : dataTable.Rows[0]["monto"]);
			num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoGiftCard"])) ? ((object)0) : dataTable.Rows[0]["MontoGiftCard"]);
			codigoFact = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["codigo"])) ? "" : dataTable.Rows[0]["codigo"]);
			flag = true;
		}
		else
		{
			DataTable dataTable = BD.ConsultaVer("select sum(pago) as pago from DetalleCuenta where VisitaID=" + Conversions.ToString(visitaID));
			if (dataTable.Rows.Count > 0)
			{
				Monto = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
			}
			num = 0.0;
			codigoFact = "";
			NIT = "";
			Nombre = "";
			fecha = DateAndTime.Now;
			email = "";
			TipoDocumento = 0;
			codigoMetodoPago = 1;
			numeroTarjeta = "";
		}
		DataTable dataTable2 = BD.ConsultaVer("select ClienteID, fecha from Visitas where ID=" + Conversions.ToString(visitaID));
		clientID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["clienteID"]), 0))) ? ((object)0) : VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["clienteID"]), 0));
		if (!flag)
		{
			fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable2.Rows[0]["Fecha"]);
		}
		DataTable dataTable3 = ((visitaID > 0) ? BD.ConsultaVer("Cuentas.CuentaID, sum(pagos.MontoBs) as Monto, max(nroTarjeta) as nroTarjeta, MetodoPagoSIN ", "(Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID)\r\n                        inner join DetalleCuenta on DetalleCuenta.id = Pagos.DetalleCuentaID", "Cuentas.EsGiftCard = " + VariableGeneral.armarBolean(0) + " and DetalleCuenta.VisitaID  = " + Conversions.ToString(visitaID), "Cuentas.CuentaID", "Cuentas.CuentaID,MetodoPagoSIN") : ((AgruparPagoID <= 0) ? new DataTable() : BD.ConsultaVer("Cuentas.CuentaID, sum(pagos.MontoBs) as Monto, max(nroTarjeta) as nroTarjeta, MetodoPagoSIN ", "(Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID)\r\n                        inner join DetalleCuenta on DetalleCuenta.id = Pagos.DetalleCuentaID", "Cuentas.EsGiftCard = " + VariableGeneral.armarBolean(0) + " and Pagos.AgruparPagoID = " + Conversions.ToString(AgruparPagoID), "Cuentas.CuentaID", "Cuentas.CuentaID,MetodoPagoSIN")));
		if (dataTable3.Rows.Count == 0)
		{
			if (num == Conversions.ToDouble(Monto))
			{
				codigoMetodoPago = 27;
			}
			else if (num > 0.0)
			{
				codigoMetodoPago = 35;
			}
			else
			{
				codigoMetodoPago = 1;
			}
		}
		else if (dataTable3.Rows.Count == 1)
		{
			if (Conversions.ToBoolean(Operators.AndObject(num == 0.0, Operators.CompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0]["MetodoPagoSIN"]), 0), 0, TextCompare: false))))
			{
				codigoMetodoPago = Conversions.ToInteger(dataTable3.Rows[0]["MetodoPagoSIN"]);
				if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0]["MetodoPagoSIN"]), 0), 2, TextCompare: false))
				{
					string text = VariableGeneral.armarNumeroTarjetaSin(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0]["nroTarjeta"]), "")));
					if ((text.Length == 0) | (Operators.CompareString(text, "0000000000000000", TextCompare: false) == 0))
					{
						codigoMetodoPago = 1;
					}
					else
					{
						numeroTarjeta = text;
					}
				}
			}
			else if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectEqual(dataTable3.Rows[0]["CuentaID"], 1, TextCompare: false), Operators.CompareObjectEqual(dataTable3.Rows[0]["CuentaID"], 2, TextCompare: false))))
			{
				if (num > 0.0)
				{
					codigoMetodoPago = 35;
				}
				else
				{
					codigoMetodoPago = 1;
				}
			}
			else if (Operators.ConditionalCompareObjectEqual(dataTable3.Rows[0]["CuentaID"], 3, TextCompare: false))
			{
				numeroTarjeta = VariableGeneral.armarNumeroTarjetaSin(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0]["nroTarjeta"]), "")));
				if (num > 0.0)
				{
					codigoMetodoPago = 40;
					if ((numeroTarjeta.Length <= 1) | (Operators.CompareString(numeroTarjeta, "0000000000000000", TextCompare: false) == 0))
					{
						codigoMetodoPago = 35;
						numeroTarjeta = "0";
					}
				}
				else
				{
					codigoMetodoPago = 2;
					if ((numeroTarjeta.Length <= 1) | (Operators.CompareString(numeroTarjeta, "0000000000000000", TextCompare: false) == 0))
					{
						codigoMetodoPago = 1;
						numeroTarjeta = "0";
					}
				}
			}
			else if (num == Conversions.ToDouble(Monto))
			{
				codigoMetodoPago = 27;
			}
			else if (num > 0.0)
			{
				if (Operators.ConditionalCompareObjectEqual(dataTable3.Rows[0]["MetodoPagoSIN"], 7, TextCompare: false))
				{
					codigoMetodoPago = 64;
				}
				else
				{
					codigoMetodoPago = 35;
				}
			}
			else
			{
				codigoMetodoPago = Conversions.ToInteger(dataTable3.Rows[0]["MetodoPagoSIN"]);
			}
		}
		else if (dataTable3.Rows.Count == 2)
		{
			if (Conversions.ToBoolean(Operators.AndObject(Operators.OrObject(Operators.CompareObjectEqual(dataTable3.Rows[0]["CuentaID"], 1, TextCompare: false), Operators.CompareObjectEqual(dataTable3.Rows[1]["CuentaID"], 2, TextCompare: false)), Operators.CompareObjectEqual(dataTable3.Rows[1]["CuentaID"], 3, TextCompare: false))))
			{
				numeroTarjeta = VariableGeneral.armarNumeroTarjetaSin(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[1]["nroTarjeta"]), "")));
				if (num > 0.0)
				{
					codigoMetodoPago = 86;
				}
				else
				{
					codigoMetodoPago = 10;
					if ((numeroTarjeta.Length <= 1) | (Operators.CompareString(numeroTarjeta, "0000000000000000", TextCompare: false) == 0))
					{
						codigoMetodoPago = 1;
						numeroTarjeta = "0";
					}
				}
			}
			else if (Conversions.ToBoolean(Operators.AndObject(Operators.CompareObjectEqual(dataTable3.Rows[0]["CuentaID"], 3, TextCompare: false), Operators.OrObject(Operators.CompareObjectEqual(dataTable3.Rows[1]["CuentaID"], 1, TextCompare: false), Operators.CompareObjectEqual(dataTable3.Rows[1]["CuentaID"], 2, TextCompare: false)))))
			{
				numeroTarjeta = VariableGeneral.armarNumeroTarjetaSin(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0]["nroTarjeta"]), "")));
				if (num > 0.0)
				{
					codigoMetodoPago = 86;
				}
				else
				{
					codigoMetodoPago = 10;
					if ((numeroTarjeta.Length <= 1) | (Operators.CompareString(numeroTarjeta, "0000000000000000", TextCompare: false) == 0))
					{
						codigoMetodoPago = 1;
						numeroTarjeta = "0";
					}
				}
			}
			else
			{
				codigoMetodoPago = 1;
			}
		}
		else if (num > 0.0)
		{
			if (Operators.ConditionalCompareObjectEqual(dataTable3.Rows[0]["MetodoPagoSIN"], 7, TextCompare: false))
			{
				codigoMetodoPago = 64;
			}
			else
			{
				codigoMetodoPago = 35;
			}
		}
		else
		{
			codigoMetodoPago = 1;
		}
		if ((Operators.CompareString(numeroTarjeta, "0", TextCompare: false) == 0) & (codigoMetodoPago == 2))
		{
			codigoMetodoPago = 1;
		}
	}

	public int DevolverMeseroID()
	{
		int result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("top 1 MeseroID", "Detallecuenta", "VisitaID=" + ID + " and ProductoID <> 1");
			result = ((dataTable.Rows.Count > 0) ? Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0)) : 0);
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
