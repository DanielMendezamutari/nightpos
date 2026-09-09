using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsGastos_Facturas
{
	private int Gastos_FacturaID;

	private string Nit;

	private string Nombre;

	private int NroFactura;

	private string NroAutorizacion;

	private string Codigo;

	private DateTime Fecha;

	private double MontoFacturado;

	private double Descuento;

	private int GastoID;

	private double ICE;

	private double Excento;

	private double MontoTotal;

	public int _Gastos_FacturaID
	{
		get
		{
			return Gastos_FacturaID;
		}
		set
		{
			Gastos_FacturaID = value;
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

	public string _Codigo
	{
		get
		{
			return Codigo;
		}
		set
		{
			Codigo = value;
		}
	}

	public string _Nit
	{
		get
		{
			return Nit;
		}
		set
		{
			Nit = value;
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

	public long _NroFactura
	{
		get
		{
			return NroFactura;
		}
		set
		{
			NroFactura = checked((int)value);
		}
	}

	public string _NroAutorizacion
	{
		get
		{
			return NroAutorizacion;
		}
		set
		{
			NroAutorizacion = value;
		}
	}

	public double _MontoFacturado
	{
		get
		{
			return MontoFacturado;
		}
		set
		{
			MontoFacturado = value;
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

	public double _ICE
	{
		get
		{
			return ICE;
		}
		set
		{
			ICE = value;
		}
	}

	public double _Excento
	{
		get
		{
			return Excento;
		}
		set
		{
			Excento = value;
		}
	}

	public double _MontoTotal
	{
		get
		{
			return MontoTotal;
		}
		set
		{
			MontoTotal = value;
		}
	}

	public DataTable ToReturnTodasGastos_Facturas()
	{
		return BD.ConsultaVer("Gastos_Facturas.Gastos_FacturaID,Gastos_Facturas.Nombre", "Gastos_Facturas", "", "Nombre");
	}

	public DataTable ToReturnSoloGastos_Facturas1()
	{
		return BD.ConsultaVer("select Gastos_Facturas.Gastos_FacturaID,Gastos_Facturas.Nit,Gastos_Facturas.Nombre,Gastos_Facturas.NroFactura,Meseros.Nombre as Responsable,Factura, Gastos_Facturas.MontoFacturado from Gastos_Facturas left join Meseros on Meseros.MeseroGastos_FacturaID=Gastos_Facturas.NroAutorizacion order by Gastos_Facturas.Nombre");
	}

	public int Modify()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Gastos_Facturas", string.Concat("Nit='" + Nit + "',Nombre='" + Nombre + "',NroFactura=" + Conversions.ToString(NroFactura), ",NroAutorizacion='", NroAutorizacion, "', Codigo = '", Codigo, "',Fecha =", VariableGeneral.ArmarFecha(Fecha), ",MontoFacturado= ", Conversion.Str(MontoFacturado), ",Descuento= ", Conversion.Str(Descuento), ",GastoID = ", Conversions.ToString(GastoID), ",ICE = ", Conversion.Str(ICE), ",Excento = ", Conversion.Str(Excento), ",MontoTotal = ", Conversion.Str(MontoTotal), ",flagSync=NULL"), "Gastos_FacturaID=" + Gastos_FacturaID);
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
		int result;
		try
		{
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				Gastos_FacturaID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(Gastos_FacturaID)", "Gastos_Facturas").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(Gastos_FacturaID) + ",'" + Nit + "','" + Nombre + "',", Conversions.ToString(NroFactura)), ",'", NroAutorizacion, "','", Codigo, "',", VariableGeneral.ArmarFecha(Fecha), ",", Conversion.Str(MontoFacturado), ",", Conversion.Str(Descuento), ",", Conversions.ToString(GastoID), ",", Conversion.Str(ICE), ",", Conversion.Str(Excento), ",", Conversion.Str(MontoTotal)), "Gastos_Facturas(Gastos_FacturaID, Nit, Nombre, NroFactura, NroAutorizacion,Codigo,Fecha ,MontoFacturado,Descuento,GastoID,ICE,Excento,MontoTotal)");
				result = Gastos_FacturaID;
			}
			else
			{
				BD.ConsultaInsertar3("'" + Nit + "','" + Nombre + "','" + Conversions.ToString(NroFactura) + "','" + NroAutorizacion + "','" + Codigo + "'," + VariableGeneral.ArmarFecha(Fecha) + "," + Conversion.Str(MontoFacturado) + "," + Conversion.Str(Descuento) + "," + Conversions.ToString(GastoID) + "," + Conversion.Str(ICE) + "," + Conversion.Str(Excento) + "," + Conversion.Str(MontoTotal), "Gastos_Facturas( Nit, Nombre, NroFactura, NroAutorizacion,Codigo,Fecha,MontoFacturado,Descuento,GastoID,ICE,Excento,MontoTotal)", ref Gastos_FacturaID);
				result = Gastos_FacturaID;
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

	public int Delete(int GastoID)
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Gastos_Facturas", "GastoID = " + GastoID) == 0)
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

	public void DevolverNitMesa(ref double cod)
	{
		DataTable dataTable = BD.ConsultaVer("Gastos_Facturas.Nit", "Gastos_Facturas", ("Gastos_FacturaID = " + Conversions.ToString(Gastos_FacturaID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			cod = Conversions.ToDouble(dataTable.Rows[0][0]);
		}
	}

	public DataTable DevolverGastos_Facturas()
	{
		return BD.ConsultaVer("*", "Gastos_Facturas", "GastoId = " + Conversions.ToString(GastoID), "Nombre");
	}

	public DataTable devolverReporteFactura(DateTime desde, DateTime hasta)
	{
		return BD.ConsultaVer(" Nit,Nombre,Fecha as FechaEmision,NroFactura, Codigo, MontoFacturado  as Monto_Facturado, NroAutorizacion,Descuento,ICE,Excento ", "Gastos_Facturas", "  Fecha between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "fecha");
	}

	public DataTable devolverReporteFacturaFormatoImpuestos(DateTime desde, DateTime hasta)
	{
		return BD.ConsultaVer("0 as Num, " + ((configuration.gMODO_ACCESS == 1) ? "DateValue(Fecha)" : "CAST(Fecha AS DATE)") + " AS Fecha_Factura,NIT,Nombre as Razon_Social,NroFactura,0 as No_DUI,NroAutorizacion as No_Autorizacion, MontoTotal  as Importe_Total_Compra, 0 as Importe_Credito,MontoFacturado as Subtotal, Descuento, MontoFacturado as Importe_Base, MontoFacturado *0.13 as Credito_Fiscal, Codigo as Codigo_Control, ' ' TipoCompra", " Gastos_Facturas", " Fecha between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Fecha");
	}

	public DataTable ExisteFactura()
	{
		return BD.ConsultaVer("*", "Gastos_Facturas", "Nit = '" + Nit + "' and NroFactura = " + Conversions.ToString(NroFactura) + " and NroAutorizacion='" + NroAutorizacion + "'");
	}

	public DataTable devolverReporteFacturaFormatoImpuestos1(DateTime desde, DateTime hasta)
	{
		return BD.ConsultaVerParaReporte("select 1 as Especificacion,0 as Num, CAST(Gastos_Facturas.Fecha AS DATE) AS Fecha_Factura,Gastos_Facturas.Nit,Gastos_Facturas.Nombre as Nombre_RazonSocial,\r\n                               Gastos_Facturas.NroFactura, 0 as Nro_DUI,Gastos_Facturas.NroAutorizacion as No_Autorizacion, \r\n                                round(Gastos_Facturas.MontoTotal ,2) as Importe_Compra,\r\n                                round(ICE + Excento,2)  as Importe_No_Sujeto,\r\n                                round(Gastos_Facturas.MontoTotal - ICE - Excento,2) as SubTotal,\r\n                                round(Gastos_Facturas.Descuento,2) as Descuento,\r\n                                round(Gastos_Facturas.MontoTotal - ICE - Excento - Descuento ,2)  as Importe_Base,\r\n                                round((Gastos_Facturas.MontoTotal - ICE - Excento - Descuento ) * 0.13,2) as CreditoFiscal,Gastos_Facturas.Codigo as \r\ntrol, 1 as TipoCompra\r\n                                from Gastos_Facturas   where fecha between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + "order by fecha", consolidado: false);
	}
}
