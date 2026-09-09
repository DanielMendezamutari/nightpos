using System;
using System.Data;
using System.Linq;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCuentas
{
	private int CuentaID;

	private string Nombre;

	private string Banco;

	private string TipoCuenta;

	private string Nro;

	private string Observacion;

	private bool Activa;

	private bool Moneda;

	private bool EsDeposito;

	private bool EsGiftCard;

	private int metodoPagoSIN;

	private bool EsAdmin;

	private bool FacturaObligatoria;

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

	public string _Nombre
	{
		get
		{
			return Nombre;
		}
		set
		{
			Nombre = value;
		}
	}

	public string _Banco
	{
		get
		{
			return Banco;
		}
		set
		{
			Banco = value;
		}
	}

	public string _TipoCuenta
	{
		get
		{
			return TipoCuenta;
		}
		set
		{
			TipoCuenta = value;
		}
	}

	public string _Nro
	{
		get
		{
			return Nro;
		}
		set
		{
			Nro = value;
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

	public bool _Activa
	{
		get
		{
			return Activa;
		}
		set
		{
			Activa = value;
		}
	}

	public bool _Moneda
	{
		get
		{
			return Moneda;
		}
		set
		{
			Moneda = value;
		}
	}

	public bool _FacturaObligatoria
	{
		get
		{
			return FacturaObligatoria;
		}
		set
		{
			FacturaObligatoria = value;
		}
	}

	public bool _EsDeposito
	{
		get
		{
			return EsDeposito;
		}
		set
		{
			EsDeposito = value;
		}
	}

	public bool _EsGiftCard
	{
		get
		{
			return EsGiftCard;
		}
		set
		{
			EsGiftCard = value;
		}
	}

	public int _metodoPagoSIN
	{
		get
		{
			return metodoPagoSIN;
		}
		set
		{
			metodoPagoSIN = value;
		}
	}

	public bool _EsAdmin
	{
		get
		{
			return EsAdmin;
		}
		set
		{
			EsAdmin = value;
		}
	}

	public DataTable Devolver()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Cuentas.CuentaID,Cuentas.Nombre,Cuentas.Banco,Cuentas.TipoCuenta,Cuentas.Nro,Cuentas.Observacion,Cuentas.Activa,EsDeposito,(SELECT round(sum(Monto),2) FROM Movimientos where CuentaID=Cuentas.CuentaID group by CuentaID) as MontoTotal,iif(Cuentas.Moneda=0,'Bs','$') as Moneda,EsGiftCard ,metodoPagoSIN, Cuentas.EsAdmin, FacturaObligatoria", "Cuentas");
		}
		return BD.ConsultaVer("Cuentas.CuentaID,Cuentas.Nombre,Cuentas.Banco,Cuentas.TipoCuenta,Cuentas.Nro,Cuentas.Observacion,Cuentas.Activa,EsDeposito,(SELECT round(sum(Monto),2) FROM Movimientos where CuentaID=Cuentas.CuentaID group by CuentaID) as MontoTotal,CASE WHEN Cuentas.Moneda=0 then 'Bs' else '$' end as Moneda,EsGiftCard,metodoPagoSIN,Cuentas.EsAdmin, FacturaObligatoria", "Cuentas");
	}

	public DataTable DevolverActivaMenosCajas()
	{
		return BD.ConsultaVer("Cuentas.CuentaID,Cuentas.Nombre", "Cuentas", "Cuentas.EsAdmin = " + VariableGeneral.armarBolean(0) + " and Cuentas.Activa= " + VariableGeneral.armarBolean(1) + " and CuentaID <> " + Conversions.ToString(1) + " and CuentaID <> " + Conversions.ToString(2) + " and CuentaID <> " + Conversions.ToString(3) + " and CuentaID <> " + Conversions.ToString(VariableGeneral.gCobrosQR), "Cuentas.Nombre");
	}

	public DataTable DevolverActivaMenosCajasAdmin()
	{
		return BD.ConsultaVer("Cuentas.CuentaID,Cuentas.Nombre", "Cuentas", "Cuentas.EsAdmin = " + VariableGeneral.armarBolean(1) + " andCuentas.Activa= " + VariableGeneral.armarBolean(1) + " and CuentaID <> " + Conversions.ToString(1) + " and CuentaID <> " + Conversions.ToString(2) + " and CuentaID <> " + Conversions.ToString(3) + " and CuentaID <> " + Conversions.ToString(VariableGeneral.gCobrosQR), "Cuentas.Nombre");
	}

	public DataTable DevolverActiva()
	{
		string text = "Cuentas.Nombre";
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio)
		{
			text = "REPLACE(Cuentas.Nombre, 'Cuenta QR','Bco. BNB RestoM')";
		}
		return BD.ConsultaVer("Cuentas.CuentaID, " + text, "Cuentas", "Cuentas.Activa= " + VariableGeneral.armarBolean(1));
	}

	public DataTable DevolverActivaSinDolares()
	{
		return BD.ConsultaVer("Cuentas.CuentaID,Cuentas.Nombre", "Cuentas", "CuentaID <> " + Conversions.ToString(2) + " and Cuentas.Activa= " + VariableGeneral.armarBolean(1));
	}

	public DataTable DevolverTodasCtas()
	{
		return BD.ConsultaVer("Cuentas.CuentaID,Cuentas.Nombre", "Cuentas");
	}

	public DataTable Devolver(string search, string field)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("* from (select Cuentas.CuentaID,Cuentas.Nombre,Cuentas.Banco,Cuentas.TipoCuenta,Cuentas.Nro,Cuentas.Observacion,Cuentas.Activa,(SELECT round(sum(Monto),2) FROM Movimientos where CuentaID=Cuentas.CuentaID group by CuentaID) as MontoTotal,iif(Cuentas.Moneda=0,'Bs','$') as Moneda,EsGiftCard, FacturaObligatoria", "Cuentas) as tab1", (field + " " + (field.Contains("as date") ? VariableGeneral.ArmarFecha(Conversions.ToDate(search)) : search)) ?? "");
		}
		return BD.ConsultaVer("* from (select Cuentas.CuentaID,Cuentas.Nombre,Cuentas.Banco,Cuentas.TipoCuenta,Cuentas.Nro,Cuentas.Observacion,Cuentas.Activa,(SELECT round(sum(Monto),2) FROM Movimientos where CuentaID=Cuentas.CuentaID group by CuentaID) as MontoTotal,CASE WHEN Cuentas.Moneda=0 then 'Bs' else '$' end as Moneda,EsGiftCard, FacturaObligatoria", "Cuentas) as tab1", (field + " " + (field.Contains("as date") ? VariableGeneral.ArmarFecha(Conversions.ToDate(search)) : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			result = BD.ConsultaModificar("Cuentas", "Nombre='" + Nombre + "',Banco='" + Banco + "',TipoCuenta='" + TipoCuenta + "',metodoPagoSIN=" + Conversions.ToString(metodoPagoSIN) + ",Nro='" + Nro + "',Observacion='" + Observacion + "',Activa=" + VariableGeneral.armarBolean(Activa) + ",Moneda=" + VariableGeneral.armarBolean(Moneda) + ",FacturaObligatoria=" + VariableGeneral.armarBolean(FacturaObligatoria) + ",EsDeposito=" + VariableGeneral.armarBolean(EsDeposito) + ",EsAdmin=" + VariableGeneral.armarBolean(EsAdmin) + ",flagSync=NULL", "CuentaID=" + CuentaID);
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

	public int ModificarGIFCARD()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Cuentas", "EsGiftCard=" + VariableGeneral.armarBolean(0));
			BD.ConsultaModificar("Cuentas", ("EsGiftCard=" + VariableGeneral.armarBolean(EsGiftCard)) ?? "", "CuentaID=" + CuentaID);
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
				CuentaID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(CuentaID)", "Cuentas").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(CuentaID) + ",'" + Nombre + "','" + Banco + "','" + TipoCuenta + "','" + Nro + "','" + Observacion + "',", VariableGeneral.armarBolean(Activa), ","), VariableGeneral.armarBolean(Moneda), ","), VariableGeneral.armarBolean(EsDeposito), ","), VariableGeneral.armarBolean(EsGiftCard), ",", Conversions.ToString(metodoPagoSIN), ",", VariableGeneral.armarBolean(EsAdmin), ",", VariableGeneral.armarBolean(FacturaObligatoria)), "Cuentas(CuentaID,Nombre,Banco,TipoCuenta,Nro,Observacion,Activa,Moneda,EsDeposito,EsGiftCard,metodoPagoSIN,EsAdmin,FacturaObligatoria)");
				result = CuentaID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat("'" + Nombre + "','" + Banco + "','" + TipoCuenta + "','" + Nro.ToString() + "','" + Observacion + "',", VariableGeneral.armarBolean(Activa), ","), VariableGeneral.armarBolean(Moneda), ","), VariableGeneral.armarBolean(EsDeposito), ","), VariableGeneral.armarBolean(EsGiftCard), ",", Conversions.ToString(metodoPagoSIN), ",", VariableGeneral.armarBolean(EsAdmin), ",", VariableGeneral.armarBolean(FacturaObligatoria)), "Cuentas(Nombre,Banco,TipoCuenta,Nro,Observacion,Activa,Moneda,EsDeposito,EsGiftCard,metodoPagoSIN,EsAdmin,FacturaObligatoria)", ref CuentaID);
				result = CuentaID;
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
			if (BD.ConsultaEliminar("Cuentas", "CuentaID = " + CuentaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Cuenta, se encuentra en uso");
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

	public string devolverObservacion()
	{
		DataTable dataTable = BD.ConsultaVer("Observacion", "Cuentas", "CuentaID=" + Conversions.ToString(CuentaID));
		if (dataTable.Rows.Count == 0)
		{
			return "";
		}
		return Conversions.ToString(dataTable.Rows[0][0]);
	}

	public DataTable devolverDescripcion()
	{
		return BD.ConsultaVer(" CuentaID,Banco +'  '+ TipoCuenta", "Cuentas", "activa=" + VariableGeneral.armarBolean(1));
	}

	public DataTable devolverTotalXCuentaID()
	{
		return BD.ConsultaVer("  Cuentas.CuentaID,Cuentas.Nombre,Cuentas.Banco,Cuentas.TipoCuenta,Cuentas.Nro,Cuentas.Observacion,Cuentas.Activa,Cuentas.Moneda, sum(Movimientos.Monto) as 'MontoTotal' ", "Cuentas inner join Movimientos on Movimientos.CuentaID=Cuentas.CuentaID group by  Cuentas.CuentaID,Cuentas.Nombre,Cuentas.Banco,Cuentas.TipoCuenta,Cuentas.Nro,Cuentas.Observacion,Cuentas.Activa,Cuentas.Moneda", "Cuentas.activa=" + VariableGeneral.armarBolean(1));
	}

	public int devolverMonedaCuentaID()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("moneda", "Cuentas", "CuentaID=" + Conversions.ToString(CuentaID));
		if (dataTable.Rows.Count == 1)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		return 0;
	}

	public DataTable devolverMonedaCuentaIDPorTipoMoneda(int Moneda1)
	{
		return BD.ConsultaVer("CuentaID, Nombre", "Cuentas", "Activa=" + VariableGeneral.armarBolean(1) + " and Moneda =" + Conversions.ToString(Moneda1));
	}

	public int devolverCuentaIdPorNombre(string nombre1)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("CuentaID", "Cuentas", "Nombre like '" + nombre1 + "'");
		if (dataTable.Rows.Count >= 1)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		return 0;
	}

	public int devolverCuentaIdPorNombreActivas(string nombre1)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("*", "Cuentas", "Nombre='" + nombre1 + "' and Activa=" + VariableGeneral.armarBolean(1));
		if (dataTable.Rows.Count >= 1)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		return 0;
	}

	public DataTable DevolverCuentasPagos()
	{
		return BD.ConsultaVer("Cuentas.CuentaID,Cuentas.Nombre", "Cuentas", "Cuentas.Activa= " + VariableGeneral.armarBolean(1) + " and Cuentas.cuentaID> 4");
	}

	public void DevolverNombreMoneda()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("Cuentas.Nombre, Cuentas.Moneda", "Cuentas", "Cuentas.cuentaID=" + Conversions.ToString(CuentaID));
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			Moneda = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Moneda"])) ? ((object)false) : dataTable.Rows[0]["Moneda"]);
		}
	}

	public string devolverCuenta()
	{
		DataTable dataTable = BD.ConsultaVer("Nombre", "Cuentas", "CuentaID=" + Conversions.ToString(CuentaID));
		if (dataTable.Rows.Count == 0)
		{
			return "";
		}
		return Conversions.ToString(dataTable.Rows[0][0]);
	}

	public int devolverCuentaXVenta(int ventaID)
	{
		DataTable dataTable = BD.ConsultaVer("select Cuentas.cuentaID \r\n                                    from (DetalleCuenta left join pagos on DetalleCuenta.id=Pagos.DetalleCuentaID) \r\n                                    left join cuentas on Pagos.CuentaID =Cuentas.CuentaID \r\n                                    where Cuentas.Nombre not like '%Cupones%' and  DetalleCuenta.VisitaID = " + Conversions.ToString(ventaID) + "\r\n                                    order by Cuentas.CuentaID desc");
		if (dataTable.Rows.Count == 0)
		{
			return 0;
		}
		return Conversions.ToInteger(dataTable.AsEnumerable().ElementAtOrDefault(0)[0]);
	}

	public bool esTarjetaMetodoPagoSIN()
	{
		DataTable dataTable = BD.ConsultaVer("MetodoPagoSIN", "Cuentas", "CuentaID=" + Conversions.ToString(CuentaID));
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		if (Operators.ConditionalCompareObjectEqual(dataTable.Rows[0][0], 2, TextCompare: false))
		{
			return true;
		}
		return false;
	}

	public bool esFacturaObligatoria()
	{
		DataTable dataTable = BD.ConsultaVer("FacturaObligatoria", "Cuentas", "CuentaID=" + Conversions.ToString(CuentaID));
		if (dataTable.Rows.Count == 0)
		{
			FacturaObligatoria = false;
			return false;
		}
		if (Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), false)))
		{
			FacturaObligatoria = true;
			return true;
		}
		FacturaObligatoria = false;
		return false;
	}
}
