using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsGastos
{
	private int GastoID;

	private DateTime FechaSalida;

	private DateTime FechaProgramada;

	private bool FechaSalidaCh;

	private bool FechaProgramadaCh;

	private double Monto;

	private int RecibiFactura;

	private string Observacion;

	private string TipoGastoID;

	private string PersonalID;

	private string CompraID;

	private int CuentaID;

	private double TipoCambio;

	private int ProveedorID;

	public int _GastoID
	{
		get
		{
			return GastoID;
		}
		set
		{
			GastoID = value;
		}
	}

	public DateTime _FechaSalida
	{
		get
		{
			return FechaSalida;
		}
		set
		{
			FechaSalida = value;
		}
	}

	public DateTime _FechaProgramada
	{
		get
		{
			return FechaProgramada;
		}
		set
		{
			FechaProgramada = value;
		}
	}

	public bool _FechaProgramadaCh
	{
		get
		{
			return FechaProgramadaCh;
		}
		set
		{
			FechaProgramadaCh = value;
		}
	}

	public bool _FechaSalidaCh
	{
		get
		{
			return FechaSalidaCh;
		}
		set
		{
			FechaSalidaCh = value;
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

	public bool _RecibiFactura
	{
		get
		{
			return RecibiFactura != 0;
		}
		set
		{
			RecibiFactura = 0 - (value ? 1 : 0);
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

	public int _TipoGastoID
	{
		get
		{
			if (Operators.CompareString(TipoGastoID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(TipoGastoID);
		}
		set
		{
			if (value == 0)
			{
				TipoGastoID = "null";
			}
			else
			{
				TipoGastoID = Conversions.ToString(value);
			}
		}
	}

	public int _PersonalID
	{
		get
		{
			if (Operators.CompareString(CompraID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(PersonalID);
		}
		set
		{
			if (value == 0)
			{
				PersonalID = "null";
			}
			else
			{
				PersonalID = Conversions.ToString(value);
			}
		}
	}

	public int _CompraID
	{
		get
		{
			if (Operators.CompareString(CompraID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(CompraID);
		}
		set
		{
			if (value == 0)
			{
				CompraID = "null";
			}
			else
			{
				CompraID = Conversions.ToString(value);
			}
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

	public int _ProveedorID
	{
		get
		{
			return ProveedorID;
		}
		set
		{
			ProveedorID = value;
		}
	}

	public string devolevrCampoEsCompra()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return " Not IsNull(compraID)";
		}
		if (configuration.gMODO_ACCESS == 2)
		{
			return " Not IsNull(compraID) ";
		}
		return "cast(CASE WHEN compraID is null  THEN 0 ELSE  1  END as bit )";
	}

	public double devolverMontoEntreFechasPorMaquinaBs(DateTime fechaIni, DateTime fechafin)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("round(sum(Gastos.Monto),1)", "Gastos", "CuentaID=" + Conversions.ToString(1) + " and Maquina like '" + MyProject.Computer.Name + "' and Gastos.FechaSalida is not null and FechaSalida between  " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin)).Rows[0][0]), 0));
	}

	public double devolverMontoEntreFechasPorMaquinaDolares(DateTime fechaIni, DateTime fechafin)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("round(sum(Gastos.Monto),1)", "Gastos", "CuentaID=" + Conversions.ToString(2) + " and Maquina like '" + MyProject.Computer.Name + "' and Gastos.FechaSalida is not null and FechaSalida between  " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin)).Rows[0][0]), 0));
	}

	public DataTable Devolver(bool todo)
	{
		if (todo)
		{
			return BD.ConsultaVer("Gastos.GastoID,Gastos.FechaSalida,Gastos.FechaProgramada,Gastos.Monto,Gastos.RecibiFactura,Gastos.Observacion,TiposGastos.Descripcion As TiposGastos, CompraID, " + devolevrCampoEsCompra() + " as Compra1, Meseros.Nombre as Personal, Gastos.Maquina, Cuentas.Nombre as Cuentas , Cuentas.CuentaID , Gastos.TipoCambio, Gastos.ProveedorID", "((Gastos LEFT JOIN TiposGastos On Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas ON Cuentas.CUENTAID=Gastos.cuentaID", "", "Gastos.FechaSalida,Gastos.FechaProgramada");
		}
		DateTime fecha = ((DateAndTime.Now.Hour >= VariableGeneral._horaCierreTurno) ? DateAndTime.Today.AddHours(VariableGeneral._horaCierreTurno) : DateAndTime.Today.AddDays(-1.0).AddHours(VariableGeneral._horaCierreTurno));
		return BD.ConsultaVer("Gastos.GastoID,Gastos.FechaSalida,Gastos.FechaProgramada,Gastos.Monto,Gastos.RecibiFactura,Gastos.Observacion,TiposGastos.Descripcion As TiposGastos, CompraID, " + devolevrCampoEsCompra() + " as Compra1, Meseros.Nombre as Personal, Gastos.Maquina, Cuentas.Nombre as Cuentas, Cuentas.CuentaID, Gastos.TipoCambio,Gastos.ProveedorID", "((Gastos LEFT JOIN TiposGastos On Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID ) LEFT JOIN Cuentas ON Cuentas.CUENTAID=Gastos.cuentaID ", "Gastos.FechaSalida >= " + VariableGeneral.ArmarFecha(fecha) + " or Gastos.FechaProgramada >= " + VariableGeneral.ArmarFecha(fecha), "Gastos.FechaSalida,Gastos.FechaProgramada");
	}

	public bool HayGastosdProgramadosHoy()
	{
		return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("Count(*)", "Gastos", "FechaProgramada between " + VariableGeneral.ArmarFecha(DateAndTime.Today.AddDays(-7.0)) + " and " + VariableGeneral.ArmarFecha(DateAndTime.Today.AddDays(1.0))).Rows[0][0], 0, TextCompare: false);
	}

	public DataTable devolverPagosXcompraID()
	{
		return BD.ConsultaVer("Gastos.GastoID,Gastos.FechaSalida,Gastos.FechaProgramada,Gastos.Monto,Gastos.RecibiFactura,Gastos.Observacion,TiposGastos.Descripcion As TiposGastos, CompraID, CuentaID as Cuentas, TipoCambio,Gastos.ProveedorID", "Gastos LEFT JOIN TiposGastos On Gastos.TipoGastoID = TiposGastos.TipoGastoID ", "CompraID=" + CompraID.ToString(), "Gastos.FechaSalida,Gastos.FechaProgramada");
	}

	public DataTable DevolverGastosPendientes1()
	{
		return BD.ConsultaVer("Gastos.GastoID,Gastos.FechaProgramada,Gastos.Monto,Gastos.Observacion,TiposGastos.Descripcion As TiposGastos, Compras.NroComprobante,Proveedores.NombreEmpresa, Proveedores.Direccion,Gastos.ProveedorID", "((Gastos LEFT JOIN TiposGastos On Gastos.TipoGastoID = TiposGastos.TipoGastoID) left join Compras on Compras.CompraID=Gastos.CompraID) left join Proveedores on Proveedores.ProveedorID=Compras.ProveedorID", "FechaSalida is null and FechaProgramada is not null", "Gastos.FechaProgramada");
	}

	public int Modificar()
	{
		int result;
		try
		{
			string text = Conversions.ToString((CuentaID == 0) ? "NULL" : ((object)CuentaID));
			BD.ConsultaModificar("Gastos", "FechaSalida=" + VariableGeneral.ArmarFecha(FechaSalida, FechaSalidaCh) + ",FechaProgramada=" + VariableGeneral.ArmarFecha(FechaProgramada, FechaProgramadaCh) + ",Monto=" + Conversion.Str(Monto) + ",RecibiFactura=" + Conversions.ToString(RecibiFactura) + ",CuentaID=" + text + ",Observacion='" + Observacion + "',PersonalID=" + PersonalID + ",Maquina='" + ((CuentaID == 1) ? MyProject.Computer.Name : "") + "',TipoGastoID=" + TipoGastoID.ToString() + ",CompraID=" + CompraID.ToString() + ",ProveedorID=" + ProveedorID + ",TipoCambio=" + Conversion.Str(TipoCambio) + ",flagSync=NULL", "GastoID=" + GastoID);
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
			string text = Conversions.ToString((CuentaID == 0) ? "NULL" : ((object)CuentaID));
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				GastoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(GastoID)", "Gastos").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(GastoID) + "," + VariableGeneral.ArmarFecha(FechaSalida, FechaSalidaCh) + ",", VariableGeneral.ArmarFecha(FechaProgramada, FechaProgramadaCh), ","), Conversion.Str(Monto), ","), Conversions.ToString(RecibiFactura), ",'", Observacion, "',"), TipoGastoID.ToString(), ","), ProveedorID.ToString(), ","), CompraID.ToString(), ",", PersonalID, ",'", MyProject.Computer.Name, "',", text, ",", Conversion.Str(TipoCambio)), "Gastos(GastoID,FechaSalida,FechaProgramada,Monto,RecibiFactura ,Observacion,TipoGastoID,ProveedorID,CompraID,PersonalID,Maquina,CuentaID, TipoCambio)");
				GastoID = Conversions.ToInteger(BD.ConsultaVer("max(GastoID)", "Gastos").Rows[0][0]);
				result = GastoID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(VariableGeneral.ArmarFecha(FechaSalida, FechaSalidaCh) + ",", VariableGeneral.ArmarFecha(FechaProgramada, FechaProgramadaCh), ","), Conversion.Str(Monto), ","), Conversions.ToString(RecibiFactura), ",'", Observacion, "',"), TipoGastoID.ToString(), ","), ProveedorID.ToString(), ","), CompraID.ToString(), ",", PersonalID, ",'", MyProject.Computer.Name, "',", text, ",", Conversion.Str(TipoCambio)), "Gastos (FechaSalida,FechaProgramada,Monto,RecibiFactura ,Observacion,TipoGastoID,ProveedorID,CompraID,PersonalID,Maquina,CuentaID, TipoCambio)", ref GastoID);
				result = GastoID;
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
			if (BD.ConsultaEliminar("Gastos", "GastoID = " + GastoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Gasto, se encuentra en uso");
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

	public int EliminarCobroXCompraID()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Gastos", "CompraID = " + CompraID.ToString()) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Gasto, se encuentra en uso");
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

	public DataTable devolverGastosSalidaProgramadaGasto(DateTime FechaSI, DateTime FechaSF, DateTime FechaPI, DateTime FechaPF, bool completo, bool gastos, bool compras)
	{
		if (completo)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto, iif( Proveedores.NombreEmpresa is null, TiposGastos.Descripcion,Proveedores.NombreEmpresa ) as Proveedor_Gasto, Gastos.FechaSalida as 'Fecha Salida',Meseros.Nombre as Personal,  Gastos.FechaProgramada as 'Fecha Programada',iif( Gastos.FechaSalida is null, 0,Gastos.Monto) as Pagado, iif( Gastos.FechaSalida is not null, 0,Gastos.Monto) as 'Por Pagar',iif( Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + ", '$Us','Bs') as Moneda,Gastos.TipoCambio,Cuentas.Nombre as Cuenta, Gastos.RecibiFactura as 'Con Factura',  iif(Gastos.CompraID is null, 0, 1) as 'De Compra',  Compras.NroComprobante as 'Nro Compra',Proveedores.NombreEmpresa as Proveedor, Compras.Comentarios as 'Obs Compra' , Gastos.Maquina, Gastos.Observacion", "((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID ) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID)  LEFT JOIN Proveedores on Proveedores.ProveedorID = Compras.ProveedorID ", "Gastos.TipoGastoID=" + TipoGastoID + "  and ((FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ")) ", "Gastos.FechaSalida, Gastos.FechaProgramada");
			}
			if (gastos & !compras)
			{
				return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto,CASE WHEN  Proveedores.NombreEmpresa is null THEN TiposGastos.Descripcion ELSE Proveedores.NombreEmpresa END  as Proveedor_Gasto  , Gastos.FechaSalida as 'Fecha Salida',Meseros.Nombre as Personal, Gastos.FechaProgramada as 'Fecha Programada',CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda,Gastos.TipoCambio,  Cuentas.Nombre as Cuenta, Gastos.RecibiFactura as 'Con Factura', CASE WHEN  Gastos.CompraID is null  THEN 0 ELSE  1  END  as 'De Compra', Compras.NroComprobante as 'Nro Compra',Proveedores.NombreEmpresa as Proveedor, Compras.Comentarios as 'Obs Compra' , Gastos.Maquina, Gastos.Observacion, case when (Gastos_Facturas .Gastos_FacturaID is null or Gastos_Facturas .Gastos_FacturaID=0 ) then 'False' else 'True' end as Facturado", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID)  LEFT JOIN Proveedores on Proveedores.ProveedorID = Compras.ProveedorID )  left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas .GastoID  ", " gastos.compraID is null and Gastos.TipoGastoID=" + TipoGastoID + "  and ( (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ")) ", "Gastos.FechaSalida, Gastos.FechaProgramada");
			}
			if (!gastos & compras)
			{
				return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto,CASE WHEN  Proveedores.NombreEmpresa is null THEN TiposGastos.Descripcion ELSE Proveedores.NombreEmpresa END  as Proveedor_Gasto  , Gastos.FechaSalida as 'Fecha Salida',Meseros.Nombre as Personal, Gastos.FechaProgramada as 'Fecha Programada',CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda,Gastos.TipoCambio,  Cuentas.Nombre as Cuenta, Gastos.RecibiFactura as 'Con Factura', CASE WHEN  Gastos.CompraID is null  THEN 0 ELSE  1  END  as 'De Compra', Compras.NroComprobante as 'Nro Compra',Proveedores.NombreEmpresa as Proveedor, Compras.Comentarios as 'Obs Compra' , Gastos.Maquina, Gastos.Observacion, case when (Gastos_Facturas .Gastos_FacturaID is null or Gastos_Facturas .Gastos_FacturaID=0 ) then 'False' else 'True' end as Facturado", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID)  LEFT JOIN Proveedores on Proveedores.ProveedorID = Compras.ProveedorID )  left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas .GastoID  ", " gastos.compraID is not null and Gastos.TipoGastoID=" + TipoGastoID + "  and ( (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ")) ", "Gastos.FechaSalida, Gastos.FechaProgramada");
			}
			return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto,CASE WHEN  Proveedores.NombreEmpresa is null THEN TiposGastos.Descripcion ELSE Proveedores.NombreEmpresa END  as Proveedor_Gasto  , Gastos.FechaSalida as 'Fecha Salida',Meseros.Nombre as Personal, Gastos.FechaProgramada as 'Fecha Programada',CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda,Gastos.TipoCambio,  Cuentas.Nombre as Cuenta, Gastos.RecibiFactura as 'Con Factura', CASE WHEN  Gastos.CompraID is null  THEN 0 ELSE  1  END  as 'De Compra', Compras.NroComprobante as 'Nro Compra',Proveedores.NombreEmpresa as Proveedor, Compras.Comentarios as 'Obs Compra' , Gastos.Maquina, Gastos.Observacion, case when (Gastos_Facturas .Gastos_FacturaID is null or Gastos_Facturas .Gastos_FacturaID=0 ) then 'False' else 'True' end as Facturado", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID)  LEFT JOIN Proveedores on Proveedores.ProveedorID = Compras.ProveedorID )  left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas .GastoID  ", "Gastos.TipoGastoID=" + TipoGastoID + "  and ( (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ")) ", "Gastos.FechaSalida, Gastos.FechaProgramada");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FalmiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto, iif( Proveedores.NombreEmpresa is null, TiposGastos.Descripcion,Proveedores.NombreEmpresa ) as Proveedor_Gasto , iif( Gastos.FechaSalida is null, Gastos.FechaProgramada,Gastos.FechaSalida)  as Fecha,iif( Gastos.FechaSalida is null, 0,Gastos.Monto) as Pagado, iif( Gastos.FechaSalida is not null, 0,Gastos.Monto) as 'Por Pagar',iif( Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + ", '$Us','Bs') as Moneda,Gastos.TipoCambio,Cuentas.Nombre as Cuenta, Gastos.Observacion", "((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID ) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID) left join Proveedores on Proveedores.ProveedorID =Compras .CompraID ", "Gastos.TipoGastoID=" + TipoGastoID + "  and  ((FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ")) ", "Gastos.FechaSalida, Gastos.FechaProgramada");
		}
		if (gastos & !compras)
		{
			return BD.ConsultaVer("TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto,CASE WHEN  Proveedores.NombreEmpresa is null THEN TiposGastos.Descripcion ELSE Proveedores.NombreEmpresa END  as Proveedor_Gasto , CASE WHEN Gastos.FechaSalida is null  THEN   Gastos.FechaProgramada ELSE  Gastos.FechaSalida  END  as 'Fecha', CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda,Gastos.TipoCambio,  Cuentas.Nombre as Cuenta,  Gastos.Observacion,  case when (Gastos_Facturas .Gastos_FacturaID is null or Gastos_Facturas .Gastos_FacturaID=0 ) then 'False' else 'True' end as Facturado", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID) left join Proveedores on Proveedores.ProveedorID =Compras .CompraID )  left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas .GastoID  ", " gastos.compraID is null and Gastos.TipoGastoID=" + TipoGastoID + "  and ( (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") )", "Gastos.FechaSalida, Gastos.FechaProgramada");
		}
		if (!gastos & compras)
		{
			return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto,CASE WHEN  Proveedores.NombreEmpresa is null THEN TiposGastos.Descripcion ELSE Proveedores.NombreEmpresa END  as Proveedor_Gasto , CASE WHEN Gastos.FechaSalida is null  THEN   Gastos.FechaProgramada ELSE  Gastos.FechaSalida  END  as 'Fecha', CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda,Gastos.TipoCambio,  Cuentas.Nombre as Cuenta,  Gastos.Observacion,  case when (Gastos_Facturas .Gastos_FacturaID is null or Gastos_Facturas .Gastos_FacturaID=0 ) then 'False' else 'True' end as Facturado", "(((((Gastos INNER JOIN TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID) left join Proveedores on Proveedores.ProveedorID =Compras .CompraID )  left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas .GastoID  ", " gastos.compraID is not null and Gastos.TipoGastoID=" + TipoGastoID + "  and ( (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") )", "Gastos.FechaSalida, Gastos.FechaProgramada");
		}
		return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto,CASE WHEN  Proveedores.NombreEmpresa is null THEN TiposGastos.Descripcion ELSE Proveedores.NombreEmpresa END  as Proveedor_Gasto , CASE WHEN Gastos.FechaSalida is null  THEN   Gastos.FechaProgramada ELSE  Gastos.FechaSalida  END  as 'Fecha', CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda,Gastos.TipoCambio,  Cuentas.Nombre as Cuenta,  Gastos.Observacion,  case when (Gastos_Facturas .Gastos_FacturaID is null or Gastos_Facturas .Gastos_FacturaID=0 ) then 'False' else 'True' end as Facturado", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID) left join Proveedores on Proveedores.ProveedorID =Compras .CompraID )  left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas .GastoID  ", "Gastos.TipoGastoID=" + TipoGastoID + "  and ( (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") )", "Gastos.FechaSalida, Gastos.FechaProgramada");
	}

	public DataTable devolverGastosSalidaProgramada(DateTime FechaSI, DateTime FechaSF, DateTime FechaPI, DateTime FechaPF, bool completo, bool Gastos, bool Compras)
	{
		if (completo)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto, TiposGastos.Descripcion  as Gasto,Proveedores.NombreEmpresa as Proveedor, Gastos.FechaSalida as 'Fecha Salida',Meseros.Nombre as Personal,  Gastos.FechaProgramada as 'Fecha Programada',iif( Gastos.FechaSalida is null, 0,Gastos.Monto) as Pagado, iif( Gastos.FechaSalida is not null, 0,Gastos.Monto) as 'Por Pagar',iif( Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + ", '$Us','Bs') as Moneda, Gastos.TipoCambio,Cuentas.Nombre as Cuenta, Gastos.RecibiFactura as 'Con Factura',  iif(Gastos.CompraID is null, 0, 1) as 'De Compra',  Compras.NroComprobante as 'Nro Compra',  Compras.Comentarios as 'Obs Compra' , Gastos.Maquina, Gastos.Observacion,Gastos_Facturas.NroFactura", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID ) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID) LEFT JOIN Proveedores on Proveedores.ProveedorID = Gastos.ProveedorID ) left join Gastos_Facturas on Gastos_Facturas.GastoID =Gastos.GastoID   ", " (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
			}
			if (Gastos & !Compras)
			{
				return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto, TiposGastos.Descripcion  as Gasto, Proveedores.NombreEmpresa as Proveedor, Gastos.FechaSalida as 'Fecha Salida',Meseros.Nombre as Personal, Gastos.FechaProgramada as 'Fecha Programada',CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda , Gastos.TipoCambio,  Cuentas.Nombre as Cuenta, Gastos.RecibiFactura as 'Con Factura', CASE WHEN  Gastos.CompraID is null  THEN 0 ELSE  1  END  as 'De Compra',  Compras.NroComprobante as 'Nro Compra', Compras.Comentarios as 'Obs Compra' ,Gastos.Maquina, Gastos.Observacion,Gastos_Facturas.NroFactura", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID) LEFT JOIN Proveedores on Proveedores.ProveedorID = Gastos.ProveedorID) left join Gastos_Facturas on Gastos_Facturas.GastoID =Gastos.GastoID    ", " gastos.compraID is null and (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
			}
			if (!Gastos & Compras)
			{
				return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto, TiposGastos.Descripcion  as Gasto, Proveedores.NombreEmpresa as Proveedor, Gastos.FechaSalida as 'Fecha Salida',Meseros.Nombre as Personal, Gastos.FechaProgramada as 'Fecha Programada',CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda , Gastos.TipoCambio,  Cuentas.Nombre as Cuenta, Gastos.RecibiFactura as 'Con Factura', CASE WHEN  Gastos.CompraID is null  THEN 0 ELSE  1  END  as 'De Compra',  Compras.NroComprobante as 'Nro Compra', Compras.Comentarios as 'Obs Compra' ,Gastos.Maquina, Gastos.Observacion,Gastos_Facturas.NroFactura", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID) LEFT JOIN Proveedores on Proveedores.ProveedorID = Gastos.ProveedorID) left join Gastos_Facturas on Gastos_Facturas.GastoID =Gastos.GastoID    ", " gastos.compraID is not null and (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
			}
			return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto, TiposGastos.Descripcion  as Gasto, Proveedores.NombreEmpresa as Proveedor, Gastos.FechaSalida as 'Fecha Salida',Meseros.Nombre as Personal, Gastos.FechaProgramada as 'Fecha Programada',CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda , Gastos.TipoCambio,  Cuentas.Nombre as Cuenta, Gastos.RecibiFactura as 'Con Factura', CASE WHEN  Gastos.CompraID is null  THEN 0 ELSE  1  END  as 'De Compra',  Compras.NroComprobante as 'Nro Compra', Compras.Comentarios as 'Obs Compra' ,Gastos.Maquina, Gastos.Observacion,Gastos_Facturas.NroFactura", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID) LEFT JOIN Proveedores on Proveedores.ProveedorID = Gastos.ProveedorID) left join Gastos_Facturas on Gastos_Facturas.GastoID =Gastos.GastoID    ", " (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto, iif( Proveedores.NombreEmpresa is null, TiposGastos.Descripcion,Proveedores.NombreEmpresa ) as Proveedor_Gasto  , iif( Gastos.FechaSalida is null, Gastos.FechaProgramada,Gastos.FechaSalida)  as Fecha,iif( Gastos.FechaSalida is null, 0,Gastos.Monto) as Pagado, iif( Gastos.FechaSalida is not null, 0,Gastos.Monto) as 'Por Pagar',iif( Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + ", '$Us','Bs') as Moneda, Gastos.TipoCambio,Cuentas.Nombre as Cuenta, Gastos.Observacion", "((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID ) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID) left join Proveedores on Proveedores.ProveedorID =Gastos.ProveedorID", " (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
		}
		if (Gastos & !Compras)
		{
			return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto ,CASE WHEN  Proveedores.NombreEmpresa is null THEN TiposGastos.Descripcion ELSE Proveedores.NombreEmpresa END  as Proveedor_Gasto , CASE WHEN Gastos.FechaSalida is null  THEN   Gastos.FechaProgramada ELSE  Gastos.FechaSalida  END  as 'Fecha', CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda, Gastos.TipoCambio,  Cuentas.Nombre as Cuenta,  Gastos.Observacion, case when (Gastos_Facturas .Gastos_FacturaID is null or Gastos_Facturas .Gastos_FacturaID=0 ) then 'False' else 'True' end as Facturado", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID ) left join Proveedores on Proveedores.ProveedorID =Gastos.ProveedorID)  left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas .GastoID  ", " gastos.compraID is null and (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
		}
		if (!Gastos & Compras)
		{
			return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto ,CASE WHEN  Proveedores.NombreEmpresa is null THEN TiposGastos.Descripcion ELSE Proveedores.NombreEmpresa END  as Proveedor_Gasto , CASE WHEN Gastos.FechaSalida is null  THEN   Gastos.FechaProgramada ELSE  Gastos.FechaSalida  END  as 'Fecha', CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda, Gastos.TipoCambio,  Cuentas.Nombre as Cuenta,  Gastos.Observacion, case when (Gastos_Facturas .Gastos_FacturaID is null or Gastos_Facturas .Gastos_FacturaID=0 ) then 'False' else 'True' end as Facturado", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID ) left join Proveedores on Proveedores.ProveedorID =Gastos.ProveedorID)  left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas .GastoID  ", " gastos.compraID is not null and (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
		}
		return BD.ConsultaVer("Gastos.GastoID, TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto ,CASE WHEN  Proveedores.NombreEmpresa is null THEN TiposGastos.Descripcion ELSE Proveedores.NombreEmpresa END  as Proveedor_Gasto , CASE WHEN Gastos.FechaSalida is null  THEN   Gastos.FechaProgramada ELSE  Gastos.FechaSalida  END  as 'Fecha', CASE WHEN Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE  0  END  as 'Por Pagar',CASE WHEN  Cuentas.Moneda = " + VariableGeneral.armarBolean(1) + "  THEN '$Us' ELSE  'Bs'  END  as Moneda, Gastos.TipoCambio,  Cuentas.Nombre as Cuenta,  Gastos.Observacion, case when (Gastos_Facturas .Gastos_FacturaID is null or Gastos_Facturas .Gastos_FacturaID=0 ) then 'False' else 'True' end as Facturado", "(((((Gastos INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Compras ON Gastos.CompraID = Compras.CompraID ) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID) LEFT JOIN Cuentas on Cuentas.CuentaId=Gastos.CuentaID ) left join Proveedores on Proveedores.ProveedorID =Gastos.ProveedorID)  left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas .GastoID  ", " (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Gastos", " GastoID=" + GastoID);
		if (dataTable.Rows.Count > 0)
		{
			FechaSalidaCh = true;
			FechaProgramadaCh = true;
			GastoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["GastoID"])) ? ((object)0) : dataTable.Rows[0]["GastoID"]);
			FechaSalida = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaSalida"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaSalida"]);
			FechaProgramada = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaProgramada"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaProgramada"]);
			FechaSalidaCh = !Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaSalida"]));
			FechaProgramadaCh = !Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaProgramada"]));
			Monto = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Monto"])) ? ((object)0) : dataTable.Rows[0]["Monto"]);
			RecibiFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["RecibiFactura"])) ? ((object)0) : dataTable.Rows[0]["RecibiFactura"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? ((object)0) : dataTable.Rows[0]["Observacion"]);
			TipoGastoID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoGastoID"])) ? ((object)0) : dataTable.Rows[0]["TipoGastoID"]);
			CompraID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CompraID"])) ? ((object)0) : dataTable.Rows[0]["CompraID"]);
			PersonalID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PersonalID"])) ? "" : dataTable.Rows[0]["PersonalID"]);
			CuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CuentaID"])) ? "" : dataTable.Rows[0]["CuentaID"]);
		}
	}

	public DataTable DevolverGastosEntreFechas(DateTime fechaIni, DateTime fechaFin, int idCuenta)
	{
		return BD.ConsultaVer("Gastos.GastoID, Gastos.FechaSalida, Gastos.Monto,Gastos.TipoGastoID,Gastos.CuentaID, Gastos.Observacion, TiposGastos.Descripcion   ", "Gastos left join TiposGastos on Gastos.TipoGastoID  = TiposGastos.TipoGastoID", "FechaSalida between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin) + " and FechaSalida  is not null and Gastos.CuentaID = " + Conversions.ToString(idCuenta) + " and Maquina = '" + MyProject.Computer.Name + "'");
	}

	public DataTable DevolverGastosFecha(DateTime fechaIni, DateTime fechaFin)
	{
		return BD.ConsultaVer("Gastos.GastoID,Gastos.FechaSalida,Gastos.FechaProgramada,Gastos.Monto,Gastos.RecibiFactura,Gastos.Observacion,TiposGastos.FamiliaGasto as Familia,   TiposGastos.SubTipo as Tipo_Gasto ,TiposGastos.Descripcion As Gasto, CompraID, " + devolevrCampoEsCompra() + " as Compra1, Meseros.Nombre as Personal, Gastos.Maquina, Cuentas.Nombre as Cuentas, Cuentas.CuentaID, Gastos.TipoCambio, Gastos.ProveedorId, Proveedores.NombreEmpresa as Acreedor", "(((Gastos LEFT JOIN TiposGastos On Gastos.TipoGastoID = TiposGastos.TipoGastoID) LEFT JOIN Meseros ON Meseros.MeseroID = Gastos.PersonalID ) LEFT JOIN Cuentas ON Cuentas.CUENTAID=Gastos.cuentaID) LEFT JOIN Proveedores on Gastos.ProveedorID = Proveedores.ProveedorID", "(FechaSalida between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin) + ") or (FechaProgramada between " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin) + ")", "Gastos.FechaSalida,Gastos.FechaProgramada");
	}
}
