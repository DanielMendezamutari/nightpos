using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDetalleCuenta
{
	private int ID;

	private double Cantidad;

	private DateTime Hora;

	private string VisitaID;

	private string ProductoID;

	private double Pago;

	private double Debe;

	private int Cerrada;

	private string Comentarios;

	private string meseroID;

	private double Costo;

	private double precioUnit;

	private string TomoPedidoMeseroID;

	private int Orden;

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

	public double _Cantidad
	{
		get
		{
			return Cantidad;
		}
		set
		{
			Cantidad = value;
		}
	}

	public DateTime _Hora
	{
		get
		{
			return Hora;
		}
		set
		{
			Hora = value;
		}
	}

	public int _VisitaID
	{
		get
		{
			return Conversions.ToInteger(VisitaID);
		}
		set
		{
			VisitaID = Conversions.ToString(value);
		}
	}

	public int _ProductoID
	{
		get
		{
			return Conversions.ToInteger(ProductoID);
		}
		set
		{
			ProductoID = Conversions.ToString(value);
		}
	}

	public int _meseroID
	{
		get
		{
			if (Operators.CompareString(meseroID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(meseroID);
		}
		set
		{
			if (value == 0)
			{
				meseroID = "null";
			}
			else
			{
				meseroID = Conversions.ToString(value);
			}
		}
	}

	public int _TomoPedidoMeseroID
	{
		get
		{
			if (Operators.CompareString(TomoPedidoMeseroID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(TomoPedidoMeseroID);
		}
		set
		{
			if (value == 0)
			{
				TomoPedidoMeseroID = "null";
			}
			else
			{
				TomoPedidoMeseroID = Conversions.ToString(value);
			}
		}
	}

	public double _Pago
	{
		get
		{
			return Pago;
		}
		set
		{
			Pago = value;
		}
	}

	public double _costo
	{
		get
		{
			return Costo;
		}
		set
		{
			Costo = value;
		}
	}

	public double _Debe
	{
		get
		{
			return Debe;
		}
		set
		{
			Debe = value;
		}
	}

	public double _precioUnit
	{
		get
		{
			return precioUnit;
		}
		set
		{
			precioUnit = value;
		}
	}

	public bool _Cerrada
	{
		get
		{
			return Cerrada != 0;
		}
		set
		{
			Cerrada = 0 - (value ? 1 : 0);
		}
	}

	public string _Comentarios
	{
		get
		{
			return Comentarios;
		}
		set
		{
			if (value.Length > 150)
			{
				Comentarios = value.Substring(0, 149);
			}
			else
			{
				Comentarios = value;
			}
		}
	}

	public int _Orden
	{
		get
		{
			return Orden;
		}
		set
		{
			Orden = value;
		}
	}

	public DataTable ToReturnCovers()
	{
		return BD.ConsultaVer("DetalleCuenta.id ", " DetalleCuenta inner join Productos On Productos.ID = DetalleCuenta.ProductoID And Productos.Nombre Like 'COVER%'  ", " VisitaID= " + VisitaID);
	}

	public DataTable ToReturnCombosVisita()
	{
		return BD.ConsultaVer(" DetalleCuenta.ID,   DetalleCuenta.Cantidad, Productos.Nombre, Productos_1.Nombre AS CompuestoPor", "(((DetalleCuenta INNER JOIN ProductosCombos ON DetalleCuenta.ID = ProductosCombos.DetalleCuentaID) INNER JOIN  Productos ON DetalleCuenta.ProductoID = Productos.ID) INNER JOIN Productos AS Productos_1 ON ProductosCombos.ProductoID = Productos_1.ID)", "DetalleCuenta.VisitaID=" + VisitaID, "DetalleCuenta.ID");
	}

	public DataTable ToReturnProductosVendidosAnteriormente(int visitaID)
	{
		return BD.ConsultaVer(("select DetalleCuenta.ID, nombre, DetalleCuenta.Cantidad from Productos inner join  DetalleCuenta on Productos.ID=DetalleCuenta.ProductoID where VisitaId=" + Conversions.ToString(visitaID) + " and DetalleCuenta.borrada=" + VariableGeneral.armarBolean(aux: false)) ?? "");
	}

	public DataTable ToReturn()
	{
		return BD.ConsultaVer("DetalleCuenta.ID,DetalleCuenta.Cantidad,DetalleCuenta.Hora,Visitas.Codigo As Visitas,Productos.Nombre As Productos,DetalleCuenta.Pago,DetalleCuenta.Debe,DetalleCuenta.Cerrada,DetalleCuenta.Comentarios", "(DetalleCuenta LEFT JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) LEFT JOIN Productos On DetalleCuenta.ProductoID = Productos.ID");
	}

	public bool ClienteTieneDeuda(int clienteID)
	{
		return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "(Visitas INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID) ", ("Visitas.ClienteID =" + Conversions.ToString(clienteID) + " and DetalleCuenta.Debe > 0 and DetalleCuenta.Cerrada=" + VariableGeneral.armarBolean(0) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)) ?? "").Rows[0][0], 0, TextCompare: false);
	}

	public int getNroOrden(int VisitaID)
	{
		DataTable dataTable = BD.ConsultaVer("max(Orden)", "DetalleCuenta ", "VisitaID =" + Conversions.ToString(VisitaID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
		}
		return 0;
	}

	public bool HayAlgunaVentaPorProductoID(int prodID)
	{
		return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "DetalleCuenta ", "ProductoID =" + Conversions.ToString(prodID)).Rows[0][0], 0, TextCompare: false);
	}

	public DataTable ToReturnDetalleCuentaFromVisitaID()
	{
		return BD.ConsultaVer("0 as Change1, DetalleCuenta.ID,DetalleCuenta.Hora, Productos.Nombre As Producto, DetalleCuenta.Cantidad,DetalleCuenta.Pago+DetalleCuenta.Debe  as Total, DetalleCuenta.Pago as HabiaPagado,DetalleCuenta.Debe-DetalleCuenta.Debe as Pago,DetalleCuenta.Debe,DetalleCuenta.Cerrada, DetalleCuenta.Borrada,DetalleCuenta.Comentarios,DetalleCuenta.ProductoID, DetalleCuenta.PrecioUnit as Precio, DetalleCuenta.VisitaID", "((Visitas INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID) LEFT JOIN Mesas ON Visitas.mesaID = Mesas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", ("Visitas.ID =" + VisitaID) ?? "");
	}

	public DataTable TieneDiferentesDosificacionesYsectores(int visitaID)
	{
		return BD.ConsultaVer("distinct TiposProductos.ConfiguracionID, TiposProductos.DocumentoSector", "( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID ", " TiposProductos.ConfiguracionID is not null and DetalleCuenta.VisitaID = " + Conversions.ToString(visitaID));
	}

	public DataTable TieneDiferentesDosificacionesAgrupadorIDYsectores(int agrupadorID)
	{
		return BD.ConsultaVer("distinct TiposProductos.ConfiguracionID, TiposProductos.DocumentoSector", "(( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) inner join Pagos on DetalleCuenta.id=Pagos.DetalleCuentaID  ", " TiposProductos.ConfiguracionID is not null and Pagos.AgruparPagoID = " + Conversions.ToString(agrupadorID));
	}

	public DataTable ToReturnDetalleCuentaFromVisitaIDparaBorrar(string pagomiPc)
	{
		return BD.ConsultaVer("DetalleCuenta.ID, DetalleCuenta.Cantidad,ProductoID,PrecioUnit", "DetalleCuenta", "VisitaID =" + VisitaID + " and Borrada=" + VariableGeneral.armarBolean(0));
	}

	public DataTable ToReturnDetalleCuentaFromClienteWithChangeDeuda(int clienteID)
	{
		return BD.ConsultaVer("0 as Change1, DetalleCuenta.ID,DetalleCuenta.Hora, Productos.Nombre As Producto, DetalleCuenta.Cantidad,DetalleCuenta.Pago+DetalleCuenta.Debe as Total,DetalleCuenta.Pago as HabiaPagado,DetalleCuenta.Debe-DetalleCuenta.Debe as Pago,DetalleCuenta.Debe,DetalleCuenta.Cerrada,DetalleCuenta.Comentarios,DetalleCuenta.VisitaID,DetalleCuenta.ProductoID, DetalleCuenta.PrecioUnit  as Precio", "((Visitas INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID) LEFT JOIN Mesas ON Visitas.mesaID = Mesas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", ("Visitas.ClienteID =" + Conversions.ToString(clienteID) + " and DetalleCuenta.Debe > 0 and DetalleCuenta.Cerrada=" + VariableGeneral.armarBolean(0) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)) ?? "", "DetalleCuenta.Hora");
	}

	public double ToReturnTotalDeudaCliente(int clienteID)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(DetalleCuenta.Debe)", "((Visitas INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID) LEFT JOIN Mesas ON Visitas.mesaID = Mesas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", ("Visitas.ClienteID =" + Conversions.ToString(clienteID) + " and DetalleCuenta.Debe > 0 and DetalleCuenta.Cerrada=" + VariableGeneral.armarBolean(0) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)) ?? "").Rows[0][0]), 0));
	}

	public DataTable ToReturnDetalleCuentaFromVisitaWithChange()
	{
		return BD.ConsultaVer("0 as Change1, DetalleCuenta.ID,DetalleCuenta.Hora, Productos.Nombre As Producto, DetalleCuenta.Cantidad,DetalleCuenta.Pago+DetalleCuenta.Debe as Total,DetalleCuenta.Pago as HabiaPagado,DetalleCuenta.Debe-DetalleCuenta.Debe as Pago,DetalleCuenta.Debe,DetalleCuenta.Cerrada,DetalleCuenta.Comentarios", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", "Visitas.ID=" + VisitaID);
	}

	public DataTable ToReturnDetalleCuentaFromVisitaParaSepara()
	{
		return BD.ConsultaVer("DetalleCuenta.ID, Productos.ID as ProductoID, DetalleCuenta.Cantidad, Productos.Nombre As Producto", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", "Visitas.ID=" + VisitaID + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and DetalleCuenta.Cerrada=" + VariableGeneral.armarBolean(0));
	}

	public DataTable ToReturnDetalleFromVisita()
	{
		return BD.ConsultaVer("DetalleCuenta.ID, Productos.ID as ProductoID, DetalleCuenta.Hora, Productos.Nombre As Producto, DetalleCuenta.Cantidad,DetalleCuenta.Pago,DetalleCuenta.Debe,DetalleCuenta.Cerrada, Meseros.Nombre as Mesero,DetalleCuenta.Borrada,DetalleCuenta.Comentarios,Meseros.MeseroId", "((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) LEFT JOIN Meseros on Meseros.MeseroId=DetalleCuenta.TomoPedidoMeseroID", "Visitas.ID=" + VisitaID);
	}

	public DataTable ToReturnTotalFromVisita1()
	{
		return BD.ConsultaVer("Productos.Nombre As Producto, sum(DetalleCuenta.Cantidad) as Cantidad, sum(DetalleCuenta.Pago) as Pago, sum(DetalleCuenta.Debe) as Debe", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", "Visitas.ID=" + VisitaID + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0), "", "DetalleCuenta.ProductoID, Productos.Nombre");
	}

	public double ReturnMontoTotalVisitaYconfiguracion(int configID, int DocumentoSector)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Pago+Debe) ", "(DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID", "visitaID=" + VisitaID + " and TiposProductos.ConfiguracionID=" + Conversions.ToString(configID) + " and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)).Rows[0][0]), 0));
	}

	public double ReturnICEVisitaYconfiguracion1(int configID, int AgruparPagoID, int DocumentoSector)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			if (Conversions.ToDouble(VisitaID) > 0.0)
			{
				return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(DetalleCuenta.Cantidad * CantidadML * (ICE_Fijo/1000) + iif(ICE_porcentual>0, (DetalleCuenta.PrecioUnit-  (CantidadML*(ICE_Fijo/1000)) ) / ((1-0.13)*  (ICE_porcentual/100)+1)   * DetalleCuenta.Cantidad  * 0.87 *  (ICE_porcentual/100) , 0) ) ", "(DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID", "visitaID=" + VisitaID + " and TiposProductos.ConfiguracionID=" + Conversions.ToString(configID) + " and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)).Rows[0][0]), 0));
			}
			if (AgruparPagoID > 0)
			{
				return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(DetalleCuenta.Cantidad * CantidadML * (ICE_Fijo/1000) +  iif(ICE_porcentual>0, (DetalleCuenta.PrecioUnit-  (CantidadML*(ICE_Fijo/1000)) ) / ((1-0.13)*  (ICE_porcentual/100)+1)   * DetalleCuenta.Cantidad  * 0.87 *  (ICE_porcentual/100) , 0) ) ", "((DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID)  inner join Pagos on Pagos.DetalleCuentaID=DetalleCuenta.ID", "AgruparPagoID=" + Conversions.ToString(AgruparPagoID) + " and TiposProductos.ConfiguracionID=" + Conversions.ToString(configID) + " and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)).Rows[0][0]), 0));
			}
			return 0.0;
		}
		if (Conversions.ToDouble(VisitaID) > 0.0)
		{
			return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(DetalleCuenta.Cantidad * CantidadML * (ICE_Fijo/1000) + CASE WHEN ICE_porcentual>0 then (DetalleCuenta.PrecioUnit-  (CantidadML*(ICE_Fijo/1000)) ) / ((1-0.13)*  (ICE_porcentual/100)+1)   * DetalleCuenta.Cantidad  * 0.87 *  (ICE_porcentual/100) else  0 end ) ", "(DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID", "visitaID=" + VisitaID + " and TiposProductos.ConfiguracionID=" + Conversions.ToString(configID) + " and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)).Rows[0][0]), 0));
		}
		if (AgruparPagoID > 0)
		{
			return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(DetalleCuenta.Cantidad * CantidadML * (ICE_Fijo/1000) +  CASE WHEN ICE_porcentual>0 then (DetalleCuenta.PrecioUnit-  (CantidadML*(ICE_Fijo/1000)) ) / ((1-0.13)*  (ICE_porcentual/100)+1)   * DetalleCuenta.Cantidad  * 0.87 *  (ICE_porcentual/100) else 0 end ) ", "((DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID)  inner join Pagos on Pagos.DetalleCuentaID=DetalleCuenta.ID", "AgruparPagoID=" + Conversions.ToString(AgruparPagoID) + " and TiposProductos.ConfiguracionID=" + Conversions.ToString(configID) + " and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)).Rows[0][0]), 0));
		}
		return 0.0;
	}

	public double ReturnMontoTotalVisita()
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Debe+Pago) ", "DetalleCuenta", "(pago>0 or debe>0) and visitaID=" + VisitaID + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)).Rows[0][0]), 0));
	}

	public double TieneDescuento()
	{
		DataTable dataTable = BD.ConsultaVer("sum(Debe+Pago) as Total, sum(Cantidad*PrecioUnit) as Original ", "DetalleCuenta", "visitaID=" + VisitaID + " and ProductoID <> " + Conversions.ToString(1) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0));
		if (dataTable.Rows.Count > 0)
		{
			if (Operators.ConditionalCompareObjectLess(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Total"]), 0), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Original"]), 0), TextCompare: false))
			{
				return -1.0;
			}
			return 0.0;
		}
		return 0.0;
	}

	public void ReturnCuentaTotalFromVisitaFacturacionGrandeElectronica(ref DataSet data, string DatNombre, int documentoSector, int agrupadorID)
	{
		string query = "";
		string text = "Productos.Codigo";
		string text2 = "";
		int num = 2;
		num = documentoSector switch
		{
			35 => 5, 
			14 => 5, 
			_ => 2, 
		};
		string text3 = "SUM(ROUND((PrecioUnit * DetalleCuenta.Cantidad) - (DetalleCuenta.Pago + DetalleCuenta.Debe) + 0.0000001, " + Conversions.ToString(num) + "))";
		if (configuration.gMODO_ACCESS == 1)
		{
			text2 = "trim";
			text3 = "SUM(ROUND((PrecioUnit * DetalleCuenta.Cantidad) - (DetalleCuenta.Pago + DetalleCuenta.Debe), " + Conversions.ToString(num) + "))";
		}
		if (Conversions.ToDouble(VisitaID) > 0.0)
		{
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mundocruz)
			{
				text = "DetalleCuenta.ID";
				query = "select Productos.Nombre As Producto, PrecioUnit, DetalleCuenta.Cantidad as Cantidad, DetalleCuenta.Pago+DetalleCuenta.Debe as subTotal, ROUND((PrecioUnit * DetalleCuenta.Cantidad) - (DetalleCuenta.Pago + DetalleCuenta.Debe) + 0.0000001, 5) as Descuento, Productos.Codigo,FactElectUnidadesMedidas.Descripcion as UnidadMedida, UnidadSIN, ActividadSIN, Observaciones.Observacion,manejaSerie,manejaImei, DetalleCuenta.ID as ID\r\n                FROM ((((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID)  left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN) left join Observaciones on Observaciones.DetalleCuentaID= DetalleCuenta.ID \r\n                WHERE Visitas.ID=" + VisitaID + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(aux: false) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((documentoSector > 0) ? (" and TiposProductos.DocumentoSector=" + Conversions.ToString(documentoSector)) : "") + " order by " + text;
			}
			else
			{
				query = "select " + text2 + "(Productos.Nombre) As Producto, PrecioUnit, sum(DetalleCuenta.Cantidad) as Cantidad, sum(DetalleCuenta.Pago+DetalleCuenta.Debe) as subTotal, " + text3 + " as Descuento, Productos.Codigo,FactElectUnidadesMedidas.Descripcion as UnidadMedida, UnidadSIN, ActividadSIN, Observaciones.Observacion,manejaSerie,manejaImei, min(DetalleCuenta.ID) as ID\r\n                FROM ((((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID)  left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN) left join Observaciones on Observaciones.DetalleCuentaID= DetalleCuenta.ID \r\n                WHERE Visitas.ID=" + VisitaID + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(aux: false) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((documentoSector > 0) ? (" and TiposProductos.DocumentoSector=" + Conversions.ToString(documentoSector)) : "") + " group by " + text2 + "(Productos.Nombre), PrecioUnit,  Productos.Codigo,FactElectUnidadesMedidas.Descripcion, UnidadSIN, ActividadSIN, Observaciones.Observacion,manejaSerie,manejaImei\r\n                order by " + text;
			}
		}
		else if (agrupadorID > 0)
		{
			query = "select " + text2 + "(Productos.Nombre) As Producto, PrecioUnit, sum(DetalleCuenta.Cantidad) as Cantidad, sum(DetalleCuenta.Pago+DetalleCuenta.Debe) as subTotal, " + text3 + " as Descuento, Productos.Codigo,FactElectUnidadesMedidas.Descripcion as UnidadMedida, UnidadSIN, ActividadSIN, Observaciones.Observacion,manejaSerie,manejaImei, min(DetalleCuenta.ID) as ID\r\n                FROM (((((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID)  left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN) left join Observaciones on Observaciones.DetalleCuentaID= DetalleCuenta.ID ) left join pagos on pagos.DetalleCuentaID =DetalleCuenta.id\r\n                WHERE pagos.AgruparPagoID  = " + Conversions.ToString(agrupadorID) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(aux: false) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((documentoSector > 0) ? (" and TiposProductos.DocumentoSector=" + Conversions.ToString(documentoSector)) : "") + " group by " + text2 + "(Productos.Nombre), PrecioUnit,  Productos.Codigo,FactElectUnidadesMedidas.Descripcion, UnidadSIN, ActividadSIN, Observaciones.Observacion,manejaSerie,manejaImei\r\n                order by " + text;
		}
		else
		{
			Interaction.MsgBox("Sin visitaId ni AgrupadorID");
		}
		BD.ConsultaVerDataset(ref data, query, DatNombre);
	}

	public void ReturnCuentaTotalFromVisitaFacturacionGrande(ref DataSet data, string DatNombre, int documentoSector)
	{
		string query = "select Productos.Nombre As Producto, ROUND(PrecioUnit,2) as Precio, DetalleCuenta.Cantidad as Cantidad, Comentarios as Unidad, ROUND((DetalleCuenta.Pago+DetalleCuenta.Debe),2) as Total,Productos.Codigo,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN FROM (((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID)  INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN  WHERE Visitas.ID=" + VisitaID + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(aux: false) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((documentoSector > 0) ? (" and TiposProductos.DocumentoSector=" + Conversions.ToString(documentoSector)) : "") + " order by Productos.Codigo";
		BD.ConsultaVerDataset(ref data, query, DatNombre);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionStigma(DateTime fecha)
	{
		return BD.ConsultaVer("Productos.Nombre As Producto,DetalleCuenta.Hora , (PrecioUnit) as Precio, (DetalleCuenta.Cantidad) as Cantidad, (DetalleCuenta.Pago) as Pago, (DetalleCuenta.Debe) as Debe,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN ", "Visitas.ID=" + VisitaID + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and DetalleCuenta.Hora <= " + VariableGeneral.ArmarFecha(fecha), "Hora desc");
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionStigma2(DateTime fecha, DateTime FechaIni)
	{
		return BD.ConsultaVer("Productos.Nombre As Producto, (PrecioUnit) as Precio, sum(DetalleCuenta.Cantidad) as Cantidad, sum(DetalleCuenta.Pago) as Pago, sum(DetalleCuenta.Debe) as Debe,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID  left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN ", "Visitas.ID=" + VisitaID + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and Hora between " + VariableGeneral.ArmarFecha(FechaIni) + " and " + VariableGeneral.ArmarFecha(fecha), "", "DetalleCuenta.ProductoID, Productos.Nombre, precioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion, UnidadSIN,ActividadSIN");
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionElectronica(int DocumentoSector)
	{
		if (configuration.gManejaComidaKilo)
		{
			return BD.ConsultaVer("Productos.Nombre As Producto, (precioUnit) As Precio, (DetalleCuenta.Cantidad) As Cantidad, (DetalleCuenta.Pago) As Pago, (DetalleCuenta.Debe) As Debe, CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN ", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector));
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jardin)
		{
			return BD.ConsultaVer("Productos.Nombre As Producto, (PrecioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe , CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector), "", "DetalleCuenta.ProductoID, Productos.Nombre,PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN, ActividadSIN,CantidadML, ICE_Fijo, ICE_porcentual");
		}
		string text = "";
		text = ((!configuration.gManejaElServicio) ? "Productos.Nombre" : "DetalleCuenta.ProductoID desc");
		if (configuration.gFormatoFacturaGrande | configuration.gSoloFacturacionGrande)
		{
			text = "Productos.Codigo";
		}
		return BD.ConsultaVer("Productos.Nombre As Producto, (PrecioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe , CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector), text, "DetalleCuenta.ProductoID, Productos.Nombre,PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN, ActividadSIN,CantidadML, ICE_Fijo, ICE_porcentual");
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacion(int DocumentoSector)
	{
		if (configuration.gManejaComidaKilo)
		{
			return BD.ConsultaVer("Productos.Nombre As Producto, (precioUnit) As Precio, (DetalleCuenta.Cantidad) As Cantidad, (DetalleCuenta.Pago) As Pago, (DetalleCuenta.Debe) As Debe, CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN ", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((DocumentoSector > 0) ? (" and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector)) : ""));
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jardin)
		{
			return BD.ConsultaVer("Productos.Nombre As Producto, (PrecioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe ,  CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((DocumentoSector > 0) ? (" and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector)) : ""), "", "DetalleCuenta.ProductoID, Productos.Nombre,PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN, ActividadSIN, productos.CantidadML, ICE_Fijo, ICE_porcentual");
		}
		string text = "";
		text = ((!configuration.gManejaElServicio) ? "Productos.Nombre" : "DetalleCuenta.ProductoID desc");
		if (configuration.gFormatoFacturaGrande | configuration.gSoloFacturacionGrande)
		{
			text = "Productos.Codigo";
		}
		return BD.ConsultaVer("Productos.Nombre As Producto, (PrecioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe , CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((DocumentoSector > 0) ? (" and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector)) : ""), text, "DetalleCuenta.ProductoID, Productos.Nombre,PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN, ActividadSIN,CantidadML, ICE_Fijo, ICE_porcentual");
	}

	public void ToReturnCuentaTotalFromVisitaNotaCreditoXML(ref DataSet data, string DatNombre, int debitoID)
	{
		string text = " group by DetalleCuenta.ProductoID, Productos.Nombre,PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN,ActividadSIN";
		string query = "select Productos.Nombre As Producto,PrecioUnit, sum(DebitoCreditoDetalle.Cantidad) As Cantidad, sum(DebitoCreditoDetalle.Subtotal ) As Subtotal,  sum(Pago+Debe -DebitoCreditoDetalle.Subtotal )  as Descuento, Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as UnidadMedida, UnidadSIN, ActividadSIN, DetalleCuenta.ProductoID from ((((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) INNER JOIN DebitoCreditoDetalle on (DebitoCreditoDetalle.DetalleCuentaID=DetalleCuenta.Id and DebitoCreditoDetalle.DebitoCreditoID=" + Conversions.ToString(debitoID) + ")) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN where Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " " + text;
		BD.ConsultaVerDataset(ref data, query, DatNombre);
	}

	public DataTable ToReturnCuentaTotalFromVisitaNotaCreditoXML(int debitoID)
	{
		string text = " group by DetalleCuenta.ProductoID, Productos.Nombre,PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN,ActividadSIN";
		return BD.ConsultaVer("select Productos.Nombre As Producto, (PrecioUnit) As Precio, sum(DebitoCreditoDetalle.Cantidad) As Cantidad,  sum(DebitoCreditoDetalle.Subtotal) As Subtotal,  sum(Pago+Debe - DebitoCreditoDetalle.SubTotal )  as Descuento, Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN, DetalleCuenta.ProductoID from ((((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) INNER JOIN DebitoCreditoDetalle on (DebitoCreditoDetalle.DetalleCuentaID=DetalleCuenta.Id and DebitoCreditoDetalle.DebitoCreditoID=" + Conversions.ToString(debitoID) + ")) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN where Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " " + text);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionXML(bool desdeContingencia, bool paraXml, int DocumentoSector)
	{
		string text = "";
		if (configuration.gMODO_ACCESS == 1)
		{
			text = "trim";
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jardin)
		{
			return BD.ConsultaVer(text + "(Productos.Nombre) As Producto, (PrecioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe ,CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN,manejaSerie,manejaImei", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0), "", "DetalleCuenta.ProductoID, " + text + "(Productos.Nombre),PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN, ActividadSIN, productos.CantidadML,manejaSerie,manejaImei,CantidadML, ICE_Fijo, ICE_porcentual");
		}
		string text2 = "";
		string text3 = "Max(DetalleCuenta.Hora) as Hora";
		string text4 = " group by DetalleCuenta.ProductoID,  " + text + "(Productos.Nombre),PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN,ActividadSIN,manejaSerie,manejaImei,CantidadML, ICE_Fijo, ICE_porcentual ";
		text2 = ((!configuration.gManejaElServicio) ? "Producto" : "DetalleCuenta.ProductoID desc");
		if (configuration.gFormatoFacturaGrande | configuration.gSoloFacturacionGrande)
		{
			text2 = "Productos.Codigo";
		}
		if (desdeContingencia & paraXml)
		{
			text2 = "Hora,Producto";
			text3 = " DetalleCuenta.Hora ";
			text4 = " group by DetalleCuenta.ProductoID,  " + text + "(Productos.Nombre),PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN,ActividadSIN,manejaSerie,manejaImei,DetalleCuenta.Hora,CantidadML, ICE_Fijo, ICE_porcentual ";
		}
		if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Aerocruz) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mundocruz))
		{
			text3 = "Max(DetalleCuenta.Hora) as Hora";
			text4 = " group by DetalleCuenta.ID,DetalleCuenta.ProductoID,  " + text + "(Productos.Nombre),PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN,ActividadSIN,manejaSerie,manejaImei,CantidadML, ICE_Fijo, ICE_porcentual ";
			text2 = "DetCuenId";
		}
		return BD.ConsultaVer("select  " + text + "(Productos.Nombre) As Producto, (PrecioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe , CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion as Medida, UnidadSIN, ActividadSIN, manejaSerie, manejaImei, '' as Observacion,DetalleCuenta.ProductoID, " + text3 + ", min(DetalleCuenta.ID) as DetCuenId from (((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas on FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN where Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and ((manejaImei = " + VariableGeneral.armarBolean(0) + " and manejaSerie = " + VariableGeneral.armarBolean(0) + ") or (manejaImei is null and manejaSerie is null)) and TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((DocumentoSector > 0) ? (" and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector)) : "") + " " + text4 + " UNION  Select  " + text + "(Productos.Nombre) As Producto, (precioUnit) As Precio, (DetalleCuenta.Cantidad) As Cantidad, (DetalleCuenta.Pago) As Pago, (DetalleCuenta.Debe) As Debe , CantidadML, ICE_Fijo, ICE_porcentual, Productos.Codigo, CodigoSIN, FactElectUnidadesMedidas.Descripcion As Medida, UnidadSIN, ActividadSIN, manejaSerie, manejaImei, Observaciones.Observacion,DetalleCuenta.ProductoID, DetalleCuenta.Hora, DetalleCuenta.ID as DetCuenId from ((((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas On FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN) left join Observaciones on Observaciones.DetalleCuentaID= DetalleCuenta.ID where Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " And (manejaImei = " + VariableGeneral.armarBolean(1) + " or manejaSerie = " + VariableGeneral.armarBolean(1) + ") And TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((DocumentoSector > 0) ? (" and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector)) : "") + " order by " + text2);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionConBorrados(int DocumentoSector)
	{
		string text = "";
		if (configuration.gMODO_ACCESS == 1)
		{
			text = "trim";
		}
		if (configuration.gManejaComidaKilo)
		{
			return BD.ConsultaVer(text + "(Productos.Nombre) As Producto, (precioUnit) As Precio, (DetalleCuenta.Cantidad) As Cantidad, (DetalleCuenta.Pago) As Pago, (DetalleCuenta.Debe) As Debe, CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion As Medida, UnidadSIN, ActividadSIN", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas On FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN ", "Visitas.ID=" + VisitaID + "  And TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " and TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector) + " ");
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jardin)
		{
			return BD.ConsultaVer(text + "(Productos.Nombre) As Producto, (precioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe , CantidadML, ICE_Fijo, ICE_porcentual, Productos.Codigo, CodigoSIN, FactElectUnidadesMedidas.Descripcion As Medida, UnidadSIN, ActividadSIN", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas On FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Visitas.ID=" + VisitaID + " And TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector) + " ", "", "DetalleCuenta.ProductoID,  " + text + "(Productos.Nombre),PrecioUnit,Productos.Codigo,Productos.CantidadML,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN, ActividadSIN,CantidadML, ICE_Fijo, ICE_porcentual");
		}
		string text2 = "";
		text2 = ((!configuration.gManejaElServicio) ? (text + "(Productos.Nombre)") : "DetalleCuenta.ProductoID desc");
		if (configuration.gFormatoFacturaGrande | configuration.gSoloFacturacionGrande)
		{
			text2 = "Productos.Codigo";
		}
		return BD.ConsultaVer(text + "(Productos.Nombre) As Producto, (PrecioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe , CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion As Medida, UnidadSIN, ActividadSIN", "(((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas On FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Visitas.ID=" + VisitaID + "  And TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " And TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector) + " ", text2, "DetalleCuenta.ProductoID,  " + text + "(Productos.Nombre),PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN, ActividadSIN,CantidadML, ICE_Fijo, ICE_porcentual");
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionYconfiguracion(int configID, int codigoDocumentoSector)
	{
		string text = "";
		string text2 = "";
		if (configuration.gMODO_ACCESS == 1)
		{
			text2 = "trim";
		}
		text = ((!configuration.gManejaElServicio) ? (text2 + "(Productos.Nombre)") : "DetalleCuenta.ProductoID desc");
		if (configuration.gFormatoFacturaGrande | configuration.gSoloFacturacionGrande)
		{
			text = "DetalleCuenta.ProductoID desc";
		}
		return BD.ConsultaVer(text2 + "(Productos.Nombre) As Producto, PrecioUnit As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe, CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion As Medida, UnidadSIN, ActividadSIN", "((DetalleCuenta INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas On FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "DetalleCuenta.visitaID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " And TiposProductos.ConfiguracionId=" + Conversions.ToString(configID) + " And TiposProductos.DocumentoSector=" + Conversions.ToString(codigoDocumentoSector), text, "DetalleCuenta.ProductoID, " + text2 + "(Productos.Nombre),PrecioUnit,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion,UnidadSIN, ActividadSIN,CantidadML, ICE_Fijo, ICE_porcentual");
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(int AgruparPagoID, int DocumentoSector)
	{
		string text = "";
		if (configuration.gMODO_ACCESS == 1)
		{
			text = "trim";
		}
		if (configuration.gManejaComidaKilo)
		{
			return BD.ConsultaVer("max(DetalleCuenta.Hora) As Hora, " + text + "(Productos.Nombre) As Producto,productos.Codigo, (PrecioUnit) As Precio, (DetalleCuenta.Cantidad) As Cantidad, sum(pago) As Pago, sum(debe) As Debe, sum(Pagos.MontoBs)As Pagando , CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion As Medida, UnidadSIN, ActividadSIN", "((((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) Inner join Pagos On Pagos.DetalleCuentaId=DetalleCuenta.ID)  INNER JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas On FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Pagos.AgruparPagoID=" + Conversions.ToString(AgruparPagoID) + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " And TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((DocumentoSector > 0) ? (" And TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector)) : ""), "", text + "(Productos.Nombre),productos.Codigo,PrecioUnit, DetalleCuenta.Cantidad,DetalleCuenta.Pago,DetalleCuenta.Debe , Pagos.MontoBs,CodigoSIN,FactElectUnidadesMedidas.Descripcion , UnidadSIN, ActividadSIN,CantidadML, ICE_Fijo, ICE_porcentual");
		}
		return BD.ConsultaVer("DetalleCuenta.Hora, " + text + "(Productos.Nombre) As Producto,productos.Codigo, (PrecioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(pago) As Pago, sum(debe) As Debe, sum(Pagos.MontoBs)As Pagando , CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion As Medida, UnidadSIN, ActividadSIN,manejaSerie,manejaImei", "((((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) Inner join Pagos On Pagos.DetalleCuentaId=DetalleCuenta.ID) INNER JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas On FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Pagos.AgruparPagoID=" + Conversions.ToString(AgruparPagoID) + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " And TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ((DocumentoSector > 0) ? (" And TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector)) : ""), "DetalleCuenta.ProductoID", "DetalleCuenta.Hora,DetalleCuenta.ProductoID, " + text + "(Productos.Nombre),PrecioUnit,productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion , UnidadSIN, ActividadSIN,manejaSerie,manejaImei,CantidadML, ICE_Fijo, ICE_porcentual");
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoIDConBorrados(int AgruparPagoID, int DocumentoSector)
	{
		return BD.ConsultaVer("DetalleCuenta.Hora,Productos.Nombre As Producto,productos.Codigo, (PrecioUnit) As Precio, sum(DetalleCuenta.Cantidad) As Cantidad, sum(pago) As Pago, sum(debe) As Debe, sum(Pagos.MontoBs)As Pagando , CantidadML, ICE_Fijo, ICE_porcentual,Productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion As Medida, UnidadSIN, ActividadSIN,manejaSerie,manejaImei", "((((DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID) Inner join Pagos On Pagos.DetalleCuentaId=DetalleCuenta.ID) INNER JOIN TiposProductos On Productos.TipoProductoID = TiposProductos.TipoProductoID) left join FactElectUnidadesMedidas On FactElectUnidadesMedidas.Codigo=Productos.UnidadSIN", "Pagos.AgruparPagoID=" + Conversions.ToString(AgruparPagoID) + "  And TiposProductos.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " And TiposProductos.DocumentoSector=" + Conversions.ToString(DocumentoSector), "DetalleCuenta.ProductoID", "DetalleCuenta.Hora,DetalleCuenta.ProductoID, Productos.Nombre,PrecioUnit,productos.Codigo,CodigoSIN,FactElectUnidadesMedidas.Descripcion , UnidadSIN, ActividadSIN,manejaSerie,manejaImei,CantidadML, ICE_Fijo, ICE_porcentual");
	}

	public DataTable ToReturnCuentaDeudaFaltanteFromVisitaDelivery(bool tieneCombo)
	{
		if (tieneCombo)
		{
			return BD.ConsultaVer("DetalleCuenta.ProductoID, Productos.Nombre As Producto,sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe, PrecioUnit As Precio, DetalleCuenta.ID", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  ", "", "DetalleCuenta.ProductoID, Productos.Nombre,PrecioUnit, DetalleCuenta.ID");
		}
		return BD.ConsultaVer("DetalleCuenta.ProductoID, Productos.Nombre As Producto,sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe, PrecioUnit As Precio", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  ", "", "DetalleCuenta.ProductoID, Productos.Nombre,PrecioUnit");
	}

	public DataTable ToReturnDeudaFaltanteFromVisita()
	{
		return BD.ConsultaVer("DetalleCuenta.ProductoID, Productos.Nombre As Producto,sum(DetalleCuenta.Cantidad) As Cantidad, \r\n                            sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe, detalleCuenta.PrecioUnit As Precio", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) \r\n                              INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  ", "Productos.Orden, Productos.Nombre", "DetalleCuenta.ProductoID,Productos.Orden, Productos.Nombre,PrecioUnit");
	}

	public DataTable ToReturnDeudaFaltanteFromVisitaCuenta()
	{
		return BD.ConsultaVer("DetalleCuenta.ProductoID, Productos.Nombre As Producto,sum(DetalleCuenta.Cantidad) As Cantidad, \r\n                                sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe, PrecioUnit As Precio", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) \r\n                              INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", "Visitas.ID=" + VisitaID + " and debe>0  And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  ", "Productos.Orden, Productos.Nombre", "DetalleCuenta.ProductoID,Productos.Orden, Productos.Nombre,PrecioUnit");
	}

	public DataTable ToReturnDeudaFaltanteFromVisitaParaAndroid(bool tieneCombo)
	{
		if (tieneCombo)
		{
			return BD.ConsultaVer("DetalleCuenta.ID, DetalleCuenta.ProductoID, Productos.Nombre As Producto, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe,Case When  Imp1.nombre Is null Then Impresoras.Nombre Else Imp1.nombre End  As Impresora, avg(DetalleCuenta.PrecioUnit) As Precio", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID left join TiposProductos  On TiposProductos.TipoProductoID = Productos.TipoProductoID  left join Mesas On Mesas.ID = Visitas.MesaID left join  Salones On Salones.SalonID=Mesas.SalonID left join Impresoras On Impresoras.ImpresoraID=TiposProductos.ImpresoraID left join Categorias_Impresoras  On TiposProductos.TipoProductoID = Categorias_Impresoras.CategoriaId And Categorias_Impresoras.SalonId=Salones.salonID left join Impresoras As Imp1 On Imp1.ImpresoraID= Categorias_Impresoras.ImpresoraID", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  And (DetalleCuenta.Cerrada=" + VariableGeneral.armarBolean(0) + " or (Pago=0 and debe=0)) ", "", "DetalleCuenta.ID, DetalleCuenta.ProductoID, Productos.Nombre, Case When  Imp1.nombre Is null Then Impresoras.Nombre Else Imp1.nombre End");
		}
		return BD.ConsultaVer("DetalleCuenta.ProductoID, Productos.Nombre As Producto, sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe,Case When  Imp1.nombre Is null Then Impresoras.Nombre Else Imp1.nombre End  As Impresora, avg(DetalleCuenta.PrecioUnit) As Precio", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID left join TiposProductos  On TiposProductos.TipoProductoID = Productos.TipoProductoID  left join Mesas On Mesas.ID = Visitas.MesaID left join  Salones On Salones.SalonID=Mesas.SalonID left join Impresoras On Impresoras.ImpresoraID=TiposProductos.ImpresoraID left join Categorias_Impresoras  On TiposProductos.TipoProductoID = Categorias_Impresoras.CategoriaId And Categorias_Impresoras.SalonId=Salones.salonID left join Impresoras As Imp1 On Imp1.ImpresoraID= Categorias_Impresoras.ImpresoraID", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  And (DetalleCuenta.Cerrada=" + VariableGeneral.armarBolean(0) + " or (Pago=0 and debe=0)) ", "", "DetalleCuenta.ProductoID, Productos.Nombre, Case When  Imp1.nombre Is null Then Impresoras.Nombre Else Imp1.nombre End");
	}

	public DataTable ToReturnPedidoCompleto()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return null;
		}
		return BD.ConsultaVer("DetalleCuenta.ProductoID, Productos.Nombre As Producto,   sum(DetalleCuenta.Cantidad) As Cantidad, sum(DetalleCuenta.Pago) As Pago, sum(DetalleCuenta.Debe) As Debe,Case When  Imp1.nombre Is null Then Impresoras.Nombre Else Imp1.nombre End  As Impresora, avg(DetalleCuenta.PrecioUnit) As Precio, min(MeseroID) as meseroId, (select " + VariableGeneral.setconcatStr("ProductoID") + " from ProductosCombos where ProductosCombos.DetalleCuentaID= DetalleCuenta.ID ) as ProductosCombo," + VariableGeneral.setconcatStr("Observaciones.Observacion") + " as  Observaciones, TiposProductos.AlmacenID, TiposProductos.ManejarStock ", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID left join TiposProductos  On TiposProductos.TipoProductoID = Productos.TipoProductoID  left join Mesas On Mesas.ID = Visitas.MesaID left join  Salones On Salones.SalonID=Mesas.SalonID left join Impresoras On Impresoras.ImpresoraID=TiposProductos.ImpresoraID left join Categorias_Impresoras  On TiposProductos.TipoProductoID = Categorias_Impresoras.CategoriaId And Categorias_Impresoras.SalonId=Salones.salonID left join Impresoras As Imp1 On Imp1.ImpresoraID= Categorias_Impresoras.ImpresoraID left join Observaciones on Observaciones.DetalleCuentaID =DetalleCuenta.ID", "Visitas.ID=" + VisitaID + " And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  ", "", "DetalleCuenta.ID,DetalleCuenta.ProductoID, Productos.Nombre,TiposProductos.AlmacenID,TiposProductos.ManejarStock, Case When  Imp1.nombre Is null Then Impresoras.Nombre Else Imp1.nombre End");
	}

	public bool restar1ytraspasar1()
	{
		bool result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("Hora, ProductoID,Cantidad,Pago,Debe, MeseroID, Costo, TomoPedidoMeseroID,precioUnit", "DetalleCuenta", "ID=" + ID);
			double num = Conversions.ToDouble(dataTable.Rows[0]["Debe"]);
			double num2 = Conversions.ToDouble(dataTable.Rows[0]["Pago"]);
			double num3 = Conversions.ToDouble(dataTable.Rows[0]["Cantidad"]);
			Conversions.ToDouble(dataTable.Rows[0]["precioUnit"]);
			double num4 = (num2 + num) / num3;
			double num5 = 0.0;
			int CuentaId = 0;
			if (num >= num4)
			{
				BD.ConsultaModificar("DetalleCuenta", "Cantidad=Cantidad-1, Debe=Debe-" + Conversion.Str(num4) + ", Costo=Costo-(Costo/Cantidad), flagSync=NULL", "ID=" + ID);
			}
			else
			{
				double num6 = (num3 - 1.0) * num4;
				num5 = num2 - num6;
				clsPagos obj = new clsPagos();
				obj._DetalleCuentaID = ID;
				obj.devolucionDineroBS(num5, ref CuentaId);
				BD.ConsultaModificar("DetalleCuenta", "Cantidad=Cantidad-1, Pago=Pago-" + Conversion.Str(num5) + ", Debe=0, Costo=Costo-(Costo/Cantidad), flagSync=NULL", "ID=" + ID);
			}
			if (dataTable.Rows.Count > 0)
			{
				ID = 0;
				Cantidad = 1.0;
				Hora = Conversions.ToDate(dataTable.Rows[0]["Hora"]);
				ProductoID = Conversions.ToString(dataTable.Rows[0]["ProductoID"]);
				Pago = num5;
				Debe = num4 - num5;
				Cerrada = 0;
				Comentarios = "";
				meseroID = Conversions.ToString(dataTable.Rows[0]["meseroID"]);
				Costo = Conversions.ToDouble(Operators.DivideObject(dataTable.Rows[0]["Costo"], dataTable.Rows[0]["Cantidad"]));
				TomoPedidoMeseroID = Conversions.ToString(dataTable.Rows[0]["TomoPedidoMeseroID"]);
				precioUnit = Conversions.ToDouble(dataTable.Rows[0]["precioUnit"]);
				Insertar();
				if (num5 > 0.0)
				{
					clsPagos obj2 = new clsPagos
					{
						_Fecha = DateAndTime.Now,
						_MaquinaPago = MyProject.Computer.Name,
						_MontoBs = num5,
						_DetalleCuentaID = ID,
						_cuentaID = CuentaId
					};
					int maxAgruparPagoID = obj2.getMaxAgruparPagoID();
					obj2.Insertar(maxAgruparPagoID, _meseroID);
				}
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

	public bool traspasarAvisitaId()
	{
		bool result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", ("VisitaID=" + VisitaID.ToString()) ?? "", "ID=" + ID);
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

	public bool traspasarAvisitaId1(double descuento)
	{
		bool result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", "Debe=((Preciounit *" + Conversion.Str(descuento) + ")*Cantidad) - pago,VisitaID=" + VisitaID.ToString() + ",flagSync=NULL", "ID=" + ID);
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

	public int Modify()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", "Cantidad=" + Conversion.Str(Cantidad) + ",Hora=" + VariableGeneral.ArmarFecha(Hora) + ",VisitaID=" + VisitaID.ToString() + ",ProductoID=" + ProductoID.ToString() + ",Pago=" + Conversion.Str(Pago) + ",precioUnit=" + Conversion.Str(precioUnit) + ",Debe=" + Conversion.Str(Debe) + ",Cerrada=" + VariableGeneral.armarBolean(Cerrada) + ",Comentarios='" + Comentarios + "',flagSync=NULL", "ID=" + ID);
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

	public int PagoTotalCuentaPorVisita()
	{
		int result;
		try
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				BD.ConsultaModificar("DetalleCuenta as R inner join Visitas as V on V.ID =R.VisitaID", "Pago=Pago+Debe,Cerrada=" + VariableGeneral.armarBolean(1) + ",R.flagSync=NULL", "V.ID=" + VisitaID.ToString() + " and Cerrada= " + VariableGeneral.armarBolean(0) + " and Borrada= " + VariableGeneral.armarBolean(0));
				BD.ConsultaModificar("DetalleCuenta as R inner join Visitas as V on V.ID =R.VisitaID ", "Debe=0", "V.ID=" + VisitaID.ToString());
			}
			else
			{
				BD.ConsultaModificar("R", "Pago=Pago+Debe,Cerrada=" + VariableGeneral.armarBolean(1) + ",R.flagSync=NULL from DetalleCuenta as R inner join Visitas as V on V.ID =R.VisitaID ", "V.ID=" + VisitaID.ToString() + " and Cerrada= " + VariableGeneral.armarBolean(0) + " and Borrada= " + VariableGeneral.armarBolean(0));
				BD.ConsultaModificar("R", "Debe=0 from DetalleCuenta as R inner join Visitas as V on V.ID =R.VisitaID ", "V.ID=" + VisitaID.ToString() + " ");
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

	public int cambiarCantidad()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", "Cantidad=" + Conversion.Str(Cantidad) + ",Debe=" + Conversion.Str(Cantidad) + "*precioUnit ,Costo= (Costo/Cantidad) * " + Conversion.Str(Cantidad) + ",Comentarios=Comentarios + '" + Comentarios + "',flagSync=NULL", "ID=" + ID);
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

	public int PagoParcialCuenta1()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", "Pago=" + Conversion.Str(Pago) + ",Cerrada=" + VariableGeneral.armarBolean(Cerrada) + ",Debe=" + Conversion.Str(Debe) + ",Comentarios='" + Comentarios + "',flagSync=NULL", "ID=" + ID);
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

	public void Delay(double dblSecs)
	{
		DateAndTime.Now.AddSeconds(1.1574074074074073E-05);
		DateTime t = DateAndTime.Now.AddSeconds(1.1574074074074073E-05).AddSeconds(dblSecs);
		while (DateTime.Compare(DateAndTime.Now, t) <= 0)
		{
		}
	}

	public void guardarCosto(double costo1)
	{
		BD.ConsultaModificar("DetalleCuenta", "Costo=" + Conversion.Str(costo1), "ID=" + Conversions.ToString(ID));
	}

	public int Insertar()
	{
		checked
		{
			int result;
			try
			{
				if (ID > 0)
				{
					result = ID;
				}
				else if ((configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Soboce))
				{
					DataTable dataTable = BD.ConsultaVer("max(ID)", "DetalleCuenta");
					ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
					ID++;
					if (Pago > 0.0)
					{
						BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(ID) + "," + Conversion.Str(Cantidad) + ",", VariableGeneral.ArmarFecha(Hora), ","), VisitaID.ToString(), ","), ProductoID.ToString(), ","), Conversion.Str(Pago), ","), Conversion.Str(Debe), ","), VariableGeneral.armarBolean(Cerrada), ",'", Comentarios, "',0,", meseroID, ",", Conversion.Str(Costo), ",", TomoPedidoMeseroID, ",", Conversion.Str(precioUnit), ",'", MyProject.Computer.Name, "', ", Conversions.ToString(Orden)), "DetalleCuenta(id,Cantidad ,Hora,VisitaID,ProductoID,Pago,Debe,Cerrada,Comentarios,Borrada,MeseroID,Costo,TomoPedidoMeseroID,precioUnit, PC,Orden)");
					}
					else
					{
						BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(ID) + "," + Conversion.Str(Cantidad) + ",", VariableGeneral.ArmarFecha(Hora), ","), VisitaID.ToString(), ","), ProductoID.ToString(), ","), Conversion.Str(Pago), ","), Conversion.Str(Debe), ","), VariableGeneral.armarBolean(Cerrada), ",'", Comentarios, "',0,", meseroID, ",", Conversion.Str(Costo), ",", TomoPedidoMeseroID, ",", Conversion.Str(precioUnit), ",'", MyProject.Computer.Name, "',", Conversions.ToString(Orden)), "DetalleCuenta(id,Cantidad ,Hora,VisitaID,ProductoID,Pago,Debe,Cerrada,Comentarios,Borrada,MeseroID,Costo,TomoPedidoMeseroID,precioUnit, PC,Orden)");
					}
					result = ID;
				}
				else
				{
					if (Pago > 0.0)
					{
						BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversion.Str(Cantidad) + ",", VariableGeneral.ArmarFecha(Hora), ","), VisitaID.ToString(), ","), ProductoID.ToString(), ","), Conversion.Str(Pago), ","), Conversion.Str(Debe), ","), VariableGeneral.armarBolean(Cerrada), ",'", Comentarios, "',0,", meseroID, ",", Conversion.Str(Costo), ",", TomoPedidoMeseroID, ",", Conversion.Str(precioUnit), ",'", MyProject.Computer.Name, "',", Conversions.ToString(Orden)), "DetalleCuenta(Cantidad ,Hora,VisitaID,ProductoID,Pago,Debe,Cerrada,Comentarios,Borrada,MeseroID,Costo,TomoPedidoMeseroID,precioUnit, PC,Orden)", ref ID);
					}
					else
					{
						BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversion.Str(Cantidad) + ",", VariableGeneral.ArmarFecha(Hora), ","), VisitaID.ToString(), ","), ProductoID.ToString(), ","), Conversion.Str(Pago), ","), Conversion.Str(Debe), ","), VariableGeneral.armarBolean(Cerrada), ",'", Comentarios, "',0,", meseroID, ",", Conversion.Str(Costo), ",", TomoPedidoMeseroID, ",", Conversion.Str(precioUnit), ",'", MyProject.Computer.Name, "',", Conversions.ToString(Orden)), "DetalleCuenta(Cantidad ,Hora,VisitaID,ProductoID,Pago,Debe,Cerrada,Comentarios,Borrada,MeseroID,Costo,TomoPedidoMeseroID,precioUnit, PC, orden)", ref ID);
					}
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

	public int modificarKaraoke()
	{
		int result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("ID, Pago", "DetalleCuenta", "VisitaID=" + VisitaID + " and ProductoID=" + Conversions.ToString(2));
			if (dataTable.Rows.Count > 0)
			{
				ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
				Pago = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), 0));
			}
			else
			{
				ID = 0;
			}
			double num = 0.0;
			DataTable dataTable2 = BD.ConsultaVer("Fecha,Descripcion", "Visitas left join Mesas on Mesas.Id=Visitas.MesaID", "Visitas.ID=" + VisitaID);
			float num2 = 0f;
			if (dataTable2.Rows.Count > 0)
			{
				num2 = Conversions.ToSingle(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["Descripcion"]), 0));
			}
			if (ID == 0)
			{
				if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					ID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("MAX(ID)", "DetalleCuenta").Rows[0][0]), 0), 1));
					BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(ID) + ",1,", VariableGeneral.ArmarFecha(DateAndTime.Now), ","), VisitaID.ToString(), ","), 2.ToString(), ","), Conversions.ToString(0), ","), Conversions.ToString(0), ","), VariableGeneral.armarBolean(0), ",'',0,NULL,0,NULL,", Conversion.Str(num2), ",'", MyProject.Computer.Name, "'"), "DetalleCuenta(Id,Cantidad ,Hora,VisitaID,ProductoID,Pago,Debe,Cerrada,Comentarios,Borrada,MeseroID,Costo,TomoPedidoMeseroID,precioUnit,PC)");
				}
				else
				{
					BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat("1," + VariableGeneral.ArmarFecha(DateAndTime.Now) + ",", VisitaID.ToString(), ","), 2.ToString(), ","), Conversions.ToString(0), ","), Conversions.ToString(0), ","), VariableGeneral.armarBolean(0), ",'',0,NULL,0,NULL,", Conversion.Str(num2), ",'", MyProject.Computer.Name, "'"), "DetalleCuenta(Cantidad ,Hora,VisitaID,ProductoID,Pago,Debe,Cerrada,Comentarios,Borrada,MeseroID,Costo,TomoPedidoMeseroID,precioUnit,PC)", ref ID);
				}
			}
			DateTime now = DateAndTime.Now;
			double num3;
			if (dataTable2.Rows.Count > 0)
			{
				now = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["Fecha"]), DateAndTime.Now));
				num3 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["Descripcion"]), 0));
				if (num3 == 0.0)
				{
					BD.ConsultaEliminar("DetalleCuenta", "ID=" + Conversions.ToString(ID));
				}
				else
				{
					num = (DateAndTime.Now - now).TotalHours;
					num = ((num < 0.2) ? 0.0 : ((!(num < 1.0)) ? FormatHour(num) : 1.0));
					num3 *= num;
					num3 = ((!configuration.gRedonderCentavos) ? Convert.ToDouble(VariableGeneral.toDecimalSIN(num3, 2)) : Conversions.ToDouble(Strings.FormatNumber(Math.Round(num3 * 2.0) / 2.0, 1)));
				}
			}
			else
			{
				num3 = 0.0;
			}
			if (Pago > num3)
			{
				int CuentaId = 0;
				clsPagos obj = new clsPagos();
				obj._DetalleCuentaID = ID;
				obj.devolucionDineroBS(Pago - num3, ref CuentaId);
				BD.ConsultaModificar("DetalleCuenta", "Cantidad= " + Conversion.Str(num) + ",Pago= " + Conversion.Str(num3) + ",Debe=0, Cerrada=" + VariableGeneral.armarBolean(1) + ",flagSync=NULL", "ID=" + Conversions.ToString(ID));
			}
			else
			{
				BD.ConsultaModificar("DetalleCuenta", "Cantidad= " + Conversion.Str(num) + ",Debe=" + Conversion.Str(num3 - Pago) + ",  Cerrada=" + VariableGeneral.armarBolean(0) + ",flagSync=NULL", "ID=" + Conversions.ToString(ID));
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

	private double FormatHour(double intThisTemperature)
	{
		intThisTemperature = Conversions.ToDouble(Strings.FormatNumber(intThisTemperature, 1));
		double num = intThisTemperature - Conversion.Int(intThisTemperature);
		if (num > 0.0)
		{
			double num2 = num;
			if ((num2 > 0.0) & (num2 <= 0.25))
			{
				return Conversion.Int(intThisTemperature);
			}
			if ((num2 > 0.25) & (num2 <= 0.5))
			{
				return Conversion.Int(intThisTemperature) + 0.5;
			}
			return Conversion.Int(intThisTemperature) + 1.0;
		}
		return intThisTemperature;
	}

	public int modificarServicio(bool con10porcent, ref double MontoDevuelto, ref int CuentaId)
	{
		int result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("ID, Pago, debe", "DetalleCuenta", "VisitaID=" + VisitaID + " and ProductoID=" + Conversions.ToString(1));
			if (dataTable.Rows.Count > 0)
			{
				ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
				Pago = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), 0));
				Debe = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][2]), 0));
				goto IL_0101;
			}
			ID = 0;
			Pago = 0.0;
			Debe = 0.0;
			if (!((ID == 0) & !con10porcent))
			{
				goto IL_0101;
			}
			result = 0;
			goto end_IL_0000;
			IL_0101:
			if (ID == 0)
			{
				if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					ID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("MAX(ID)", "DetalleCuenta").Rows[0][0]), 0), 1));
					BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(ID) + ",1,", VariableGeneral.ArmarFecha(DateAndTime.Now), ","), VisitaID.ToString(), ","), 1.ToString(), ","), Conversions.ToString(0), ","), Conversions.ToString(0), ","), VariableGeneral.armarBolean(0), ",'',0,NULL,0,NULL,0,'", MyProject.Computer.Name, "',", Conversions.ToString(Orden)), "DetalleCuenta(ID,Cantidad ,Hora,VisitaID,ProductoID,Pago,Debe,Cerrada,Comentarios,Borrada,MeseroID,Costo,TomoPedidoMeseroID,precioUnit,PC,Orden)");
				}
				else
				{
					BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat("1," + VariableGeneral.ArmarFecha(DateAndTime.Now) + ",", VisitaID.ToString(), ","), 1.ToString(), ","), Conversions.ToString(0), ","), Conversions.ToString(0), ","), VariableGeneral.armarBolean(0), ",'',0,NULL,0,NULL,0,'", MyProject.Computer.Name, "',", Conversions.ToString(Orden)), "DetalleCuenta(Cantidad ,Hora,VisitaID,ProductoID,Pago,Debe,Cerrada,Comentarios,Borrada,MeseroID,Costo,TomoPedidoMeseroID,precioUnit,PC,Orden)", ref ID);
				}
			}
			DataTable dataTable2 = ((configuration.gStyleBoliches1 != configuration.styleBolichesId.Tapekua) ? BD.ConsultaVer("sum(PrecioUnit*Cantidad)", "DetalleCuenta", "DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and VisitaID=" + VisitaID + " and ProductoID<>" + Conversions.ToString(1), "", "VisitaID") : BD.ConsultaVer("sum(PrecioUnit*Cantidad)", "DetalleCuenta inner join Productos on Productos.ID=DetalleCuenta.ProductoID", "(not Productos.Nombre like 'COVER%') and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and VisitaID=" + VisitaID + " and ProductoID<>" + Conversions.ToString(1), "", "VisitaID"));
			double num = 0.0;
			if (dataTable2.Rows.Count > 0)
			{
				num = Conversions.ToDouble(Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0][0]), 0), VariableGeneral.gProductoServicioPorcentaje));
				num = (((configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspana) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jarana) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Paradise) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Palestino) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Sansha) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Tang) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Goss) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.MangaRosa) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bernadette) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Tapekua) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Serafina)) ? Conversions.ToDouble(Strings.FormatNumber(Math.Round(num * 2.0) / 2.0, 0)) : ((!configuration.gRedonderCentavos) ? Conversions.ToDouble(Strings.FormatNumber(Math.Round(num * 2.0) / 2.0, 1)) : Conversions.ToDouble(Strings.FormatNumber(Math.Round(num * 2.0) / 2.0, 0))));
			}
			else
			{
				num = 0.0;
			}
			if (con10porcent)
			{
				if (num == 0.0)
				{
					if (Pago > 0.0)
					{
						clsPagos obj = new clsPagos();
						obj._DetalleCuentaID = ID;
						obj.EliminarXDetalleCuentaID1("modificar servicio", Conversions.ToInteger(meseroID));
						MontoDevuelto = Pago;
					}
					BD.ConsultaModificar("DetalleCuenta", "Debe=0,Pago=0, precioUnit=0,flagSync=NULL, Cerrada=" + VariableGeneral.armarBolean(1), "ID=" + Conversions.ToString(ID));
				}
				else if (Pago + Debe != num)
				{
					if (Pago > num)
					{
						clsPagos obj2 = new clsPagos();
						obj2._DetalleCuentaID = ID;
						obj2.devolucionDineroBS(Pago - num, ref CuentaId);
						MontoDevuelto = Pago - num;
						BD.ConsultaModificar("DetalleCuenta", "Pago= " + Conversion.Str(num) + ",Debe=0,flagSync=NULL, precioUnit=" + Conversion.Str(num) + ", Cerrada=" + VariableGeneral.armarBolean(1), "ID=" + Conversions.ToString(ID));
					}
					else
					{
						Debe = num - Pago;
						BD.ConsultaModificar("DetalleCuenta", "Debe=" + Conversion.Str(num - Pago) + ",flagSync=NULL, precioUnit=" + Conversion.Str(num) + ", Cerrada=" + VariableGeneral.armarBolean(0), "ID=" + Conversions.ToString(ID));
					}
				}
			}
			else
			{
				if (Pago > 0.0)
				{
					clsPagos obj3 = new clsPagos();
					obj3._DetalleCuentaID = ID;
					obj3.EliminarXDetalleCuentaID1("modificar servicio 2", Conversions.ToInteger(meseroID));
				}
				BD.ConsultaModificar("DetalleCuenta", "Debe=0,precioUnit=0,Pago=0,flagSync=NULL, Cerrada=" + VariableGeneral.armarBolean(1), "ID=" + Conversions.ToString(ID));
			}
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

	public int eliminarServicio()
	{
		int result;
		try
		{
			BD.ConsultaEliminar("DetalleCuenta", "ProductoId=" + Conversions.ToString(1) + " and VisitaID=" + VisitaID.ToString());
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

	public int Eliminar(string obs)
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", "Costo=0, Pago=0,flagSync=NULL,Borrada=" + VariableGeneral.armarBolean(1) + ",Debe=0,Cerrada=" + VariableGeneral.armarBolean(1) + ",Comentarios='" + obs + "'", "ID=" + ID);
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

	public int BorrandoTemporal(bool borrado)
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", ("Borrada=" + VariableGeneral.armarBolean(borrado)) ?? "", "ID=" + ID);
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

	public int EliminarFisicamente()
	{
		int result;
		try
		{
			BD.ConsultaEliminar("DetalleCuenta", "ID=" + ID);
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

	public void DevolverDataSetNotaPedidos(ref DataSet data, string DatNombre)
	{
		string text = "select Productos.Nombre as Item, productos.Precio, productos.Precio*DetalleCuenta.Cantidad as Importe, DetalleCuenta.Cantidad ";
		text = text + "from DetalleCuenta left join Productos on DetalleCuenta.ProductoID=Productos.ID where DetalleCuenta.VisitaID = " + VisitaID;
		BD.ConsultaVerDataset(ref data, text, DatNombre);
	}

	public DataTable ToReturnPagoCuentas()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer(" iif( Cuentas.Nombre='Caja chica Bs' , 'Efectivo' , Cuentas.Nombre ) as Nombre, sum(Pagos.MontoBs) as monto, Pagos.AgruparPagoID ", " (DetalleCuenta INNER JOIN Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN Cuentas ON Pagos.CuentaID = Cuentas.CuentaID", "DetalleCuenta.VisitaID =" + VisitaID, "", "Cuentas.Nombre, Pagos.AgruparPagoID ");
		}
		return BD.ConsultaVer(" case when Cuentas.Nombre='Caja chica Bs' then 'Efectivo' else Cuentas.Nombre end as Nombre, sum(Pagos.MontoBs) as monto, Pagos.AgruparPagoID ", " (DetalleCuenta INNER JOIN Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN Cuentas ON Pagos.CuentaID = Cuentas.CuentaID", "DetalleCuenta.VisitaID =" + VisitaID, "", "Cuentas.Nombre, Pagos.AgruparPagoID");
	}

	public DataTable ToReturnServiciosDetalleCuenta()
	{
		return BD.ConsultaVer("DetalleCuenta.ID, DetalleCuenta.ProductoID, DetalleCuenta.Pago ,DetalleCuenta.Debe,DetalleCuenta.Cerrada", " DetalleCuenta", ("ProductoID= 1  and VisitaID =" + VisitaID) ?? "");
	}

	public int CambioPaquete()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", "ProductoID='" + ProductoID + "'", "ID=" + ID);
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

	public DataTable ToReturnDeudaTotalFromVisita()
	{
		return BD.ConsultaVer("DetalleCuenta.ProductoID, Productos.Nombre As Producto,   sum(DetalleCuenta.Cantidad) as Cantidad, sum(DetalleCuenta.Pago)  + sum(DetalleCuenta.Debe) as Debe, PrecioUnit as Precio", "(DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", "Visitas.ID=" + VisitaID + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  ", "", "DetalleCuenta.ProductoID, Productos.Nombre, PrecioUnit");
	}

	public int AplicarDescuento(double desc)
	{
		int result;
		try
		{
			string text = " and ProductoID <>1 ";
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Hapo)
			{
				text = " ";
			}
			if (configuration.gRedonderCentavos)
			{
				BD.ConsultaModificar("DetalleCuenta", "debe= round(round(((PrecioUnit *  " + Conversion.Str(desc) + ")*Cantidad) *2,0)/2  ,1) - Pago", "Borrada <> " + VariableGeneral.armarBolean(1) + text + " and  VisitaID=" + VisitaID + " and ProductoID not in (select ID from Productos where nombre like 'COVER%')");
			}
			else
			{
				BD.ConsultaModificar("DetalleCuenta", "debe= round(((PrecioUnit * " + Conversion.Str(desc) + ")*Cantidad) - Pago,2)", "Borrada <> " + VariableGeneral.armarBolean(1) + text + " and  VisitaID=" + VisitaID + " and ProductoID not in (select ID from Productos where nombre like 'COVER%')");
			}
			BD.ConsultaModificar("DetalleCuenta", "cerrada=" + VariableGeneral.armarBolean(1), "debe<=0 and cerrada=" + VariableGeneral.armarBolean(0) + " and  VisitaID=" + VisitaID);
			BD.ConsultaModificar("DetalleCuenta", "cerrada=" + VariableGeneral.armarBolean(0), "debe>0 and cerrada=" + VariableGeneral.armarBolean(1) + " and  VisitaID=" + VisitaID);
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

	public DataTable DevolverCuentaClientes(int clienteID)
	{
		return BD.ConsultaVer("distinct  Visitas.fecha, Visitas.ID as Cuenta, sum( DetalleCuenta.debe ) as Deuda", "Visitas left join DetalleCuenta on Visitas.ID=DetalleCuenta.VisitaID", ("Visitas.ClienteID =" + Conversions.ToString(clienteID) + " and DetalleCuenta.Debe > 0 and DetalleCuenta.Cerrada=" + VariableGeneral.armarBolean(0) + " and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0)) ?? "", "Visitas.fecha", "Visitas.fecha, Visitas.ID");
	}

	public DataTable DevolverReporteDeudasCLientes()
	{
		return BD.ConsultaVer("Clientes_NT.ID,Clientes_NT.Nombre, Clientes_NT.Apellidos, Clientes_NT.Cel,Clientes_NT.Correo, Visitas.Fecha,sum( DetalleCuenta.Debe) as Deuda , min(FechaUso ) as Desde, max(FechaUso ) as Hasta", "((((DetalleCuenta  left join Visitas on DetalleCuenta.VisitaID=Visitas.ID) left join Clientes_NT on Visitas.ClienteID = Clientes_NT.ID) left join DetalleCuentas_Paquetes on  DetalleCuentas_Paquetes.DetalleCuentaID=DetalleCuenta.ID) left join  UsoPaquetes on DetalleCuentas_Paquetes.DetalleCuentaID=UsoPaquetes .DetalleCuenta_PaqueteID )", "DetalleCuenta.Debe > 0", "Visitas.Fecha", "Clientes_NT.ID,Clientes_NT.Nombre, Clientes_NT.Apellidos, Clientes_NT.Cel,Clientes_NT.Correo, Visitas.Fecha");
	}

	public void DevolverDataSetNotaEntrega(ref DataSet data, string DatNombre)
	{
		string text = "Select productos.Codigo as ID, productos.Nombre as Item, DetalleCuenta.Cantidad ,DetalleCuenta.PrecioUnit as Precio, (sum(DetalleCuenta.Cantidad)*DetalleCuenta.PrecioUnit) as Importe";
		text = text + " from DetalleCuenta  left join Productos on DetalleCuenta.ProductoID=Productos.ID where DetalleCuenta.Borrada=0 and DetalleCuenta.VisitaID = " + Conversions.ToString(ID) + " group by productos.Codigo, productos.Nombre, DetalleCuenta.Cantidad ,DetalleCuenta.PrecioUnit";
		BD.ConsultaVerDataset(ref data, text, DatNombre);
		_ = data.Tables.Count;
	}

	public void DevolverDataSetNotapagos(ref DataSet data, string DatNombre, int agrupadorID)
	{
		string text = "Select DetalleCuenta.VisitaID as ID, productos.Nombre as Item,round( pagos.MontoBs/DetalleCuenta.PrecioUnit,1) as Cantidad ,\r\n               DetalleCuenta.PrecioUnit as Precio, pagos.MontoBs as Importe";
		text = text + " from DetalleCuenta  left join Productos on DetalleCuenta.ProductoID=Productos.ID left join pagos on pagos.DetalleCuentaID =DetalleCuenta.id where DetalleCuenta.Borrada=0 and pagos.AgruparPagoID  = " + Conversions.ToString(agrupadorID) + " group by DetalleCuenta.VisitaID, productos.Nombre, DetalleCuenta.Cantidad ,DetalleCuenta.PrecioUnit,pagos.MontoBs";
		BD.ConsultaVerDataset(ref data, text, DatNombre);
	}

	public int ActualizarDevolucion()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleCuenta", "Cantidad=Cantidad - " + Conversion.Str(Cantidad) + ",flagSync=NULL,Debe= Debe - " + Conversion.Str(Debe), "ID=" + ID);
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

	public DataTable DevolverReporteGrupal(DateTime dtpDesdeDate, DateTime dtpHastaDate)
	{
		string text = "";
		string text2 = " Productos.esCombo =" + VariableGeneral.armarBolean(0) + " ";
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vulcanica)
		{
			text2 = " 1=1 ";
		}
		text = "select Grupo as Agrupador, sum(Cant) as Cantidad  from  (  Select DetalleCuenta.ID, ProductosCombos.ProductoComboID,  Productos_1.Grupo, DetalleCuenta.Cantidad*Productos_1.GrupoCantidad As Cant  FROM            ((DetalleCuenta INNER JOIN ProductosCombos On DetalleCuenta.ID = ProductosCombos.DetalleCuentaID) INNER JOIN                           Productos As Productos_1 On ProductosCombos.ProductoID = Productos_1.ID)  where Productos_1.Grupo <> '' and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " UNION  Select  DetalleCuenta.ID,0, Productos.Grupo, DetalleCuenta.Cantidad*Productos.GrupoCantidad As Cant  FROM           ( DetalleCuenta INNER JOIN Productos On (DetalleCuenta.ProductoID = Productos.ID And " + text2 + "  And Productos.Grupo <>''))   where DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  and DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " ) as tab1  group by Grupo";
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
		{
			text = text + "   UNION select 'Z-' + Nombre as Grupo, SUM(cantidad) as Cant from ProductosUsos left join Productos on ProductoID = ID where ProductoID = (select top 1 id from Productos where nombre like '%plato de%' and Borrado = 0) and Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + "group by Nombre";
		}
		return BD.ConsultaVer(text);
	}

	public DataTable DevolverReporteKikyTipoEntrega(DateTime dtpDesdeDate, DateTime dtpHastaDate)
	{
		return BD.ConsultaVer("Select TipoEnvios.Nombre as 'Entregado', Count(*) as cantidad  from visitas inner join DetalleCuenta On Visitas.ID =DetalleCuenta.VisitaID  inner join TipoEnvios On TipoEnvios.TipoEnvioID=  visitas.TipoEnvioID    where DetalleCuenta.PC ='" + MyProject.Computer.Name + "' and (visitas.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")  group by TipoEnvios.Nombre ");
	}

	public DataTable DevolverFacturasConTarjeta(DateTime dtpDesdeDate, DateTime dtpHastaDate, string PC)
	{
		string text = "";
		text = "Select Visitas.ID , DetalleCuenta.Orden, Facturas.Monto, Facturas.NroFactura,  Facturas.Nombre, TipoEnvios.Nombre as TipoEnvio ";
		text += " FROM (((Pagos left join DetalleCuenta On Pagos.DetalleCuentaID = DetalleCuenta.ID) left join Visitas On DetalleCuenta.VisitaID =Visitas.ID) left join Facturas On Visitas.ID =Facturas.VisitaID) left join TipoEnvios on TipoEnvios.TipoEnvioID = Visitas.TipoEnvioID ";
		text = text + " where CuentaID=" + Conversions.ToString(3) + " And DetalleCuenta.PC = '" + PC + "' and (visitas.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")";
		text += " group by  Visitas.ID , DetalleCuenta.Orden, Facturas.Monto, Facturas.NroFactura,  Facturas.Nombre, TipoEnvios.Nombre  ";
		return BD.ConsultaVer(text);
	}

	public DataTable DevolverPedidoTotal()
	{
		string text = "";
		text = "select DetalleCuenta.ID,DetalleCuenta.ProductoID,DetalleCuenta.PrecioUnit as Precio,DetalleCuenta.Cantidad,tab1.Producto, tab1.Nombre  as impresora,-1 as manejarstock, 0 as Total, ";
		text = text + " DetalleCuenta.Comentarios as Observaciones, " + VariableGeneral.armarBolean(0) + " as ConRecipienteLLevar, " + VariableGeneral.armarBolean(0) + " as porKilo,'' as  ProductosCombo, DetalleCuenta.MeseroID,";
		text += "  1 as AlmacenID,0 as PrecioOri from DetalleCuenta left join  ";
		text += " (select Productos.ID,Productos.Nombre as Producto, Impresoras.Nombre  from (productos left join TiposProductos on Productos.TipoProductoID =TiposProductos.TipoProductoID)  ";
		text += " left join Impresoras on TiposProductos.ImpresoraID =Impresoras.ImpresoraID) as tab1 on detallecuenta.ProductoID=tab1.ID  ";
		text = text + " where VisitaID =  " + VisitaID;
		return BD.ConsultaVer(text);
	}

	public DataTable DevolverDetalleSacNet(int visitaID, int facturaID)
	{
		new DataTable();
		return BD.ConsultaVer("Select productoID,codigo, Cantidad,(PrecioUnit*Cantidad)-(Pago+Debe) as Descuento, productos.Nombre from DetalleCuenta left join productos on DetalleCuenta .ProductoID =Productos.ID where DetalleCuenta.Borrada= " + VariableGeneral.armarBolean(0) + " and visitaID=" + Conversions.ToString(visitaID));
	}

	public DataTable DevolverTipoPago()
	{
		return BD.ConsultaVer("");
	}

	public DataTable DevolverVentasTotales(int idFamilia, int mes, int anho)
	{
		string text = "";
		text = ((idFamilia <= 0) ? ("SELECT  N, \r\n                        CASE DIA \r\n                             when 'Monday' then 'LUNES'\r\n                             when 'Tuesday' then 'MARTES'\r\n                             when 'Wednesday' then 'MIERCOLES'\r\n                             when 'Thursday' then 'JUEVES'\r\n                             when 'Friday' then 'VIERNES'\r\n                             when 'Saturday' then 'SABADO'\r\n                             when 'Sunday' then 'DOMINGO'\r\n                        END as DIA, FECHA, VENTAS,ACUMULADO,ROUND( ACUMULADO/N ,2) AS PROMEDIO\r\n                        from\r\n                        (\r\n                        select ROW_NUMBER() OVER(ORDER BY t1.fecha ASC) as N, datename(dw, t1.fecha) as Dia, t1.FECHA, t1.VENTAS, sum(t2.ventas) as ACUMULADO\r\n                        from\r\n                        (\r\n                        select Fecha, sum (Pago) as VENTAS from \r\n                        (\r\n                        --\r\n\r\n                        select convert (date, Hora) as FECHA, DetalleCuenta.Pago \r\n                        from DetalleCuenta left join productos on DetalleCuenta.productoID=productos.id\r\n                        left join tiposproductos on productos.tipoproductoID=tiposproductos.TipoProductoID\r\n                        left join familias on tiposproductos.FamiliaId =Familias.FamiliaID \r\n                        where  year(DetalleCuenta.Hora) =" + Conversions.ToString(anho) + " and month(DetalleCuenta.Hora)=" + Conversions.ToString(mes) + "\r\n\r\n                        --\r\n                        ) as tab1\r\n                        group by Fecha\r\n                        ) as t1\r\n                        inner join \r\n                        (\r\n                        select Fecha, sum (Pago) as ventas from \r\n                        (\r\n                        select convert (date, Hora) as FECHA, DetalleCuenta.Pago \r\n                        from DetalleCuenta left join productos on DetalleCuenta.productoID=productos.id\r\n                        left join tiposproductos on productos.tipoproductoID=tiposproductos.TipoProductoID\r\n                        left join familias on tiposproductos.FamiliaId =Familias.FamiliaID \r\n                         where  year(DetalleCuenta.Hora) =" + Conversions.ToString(anho) + " and month(DetalleCuenta.Hora)=" + Conversions.ToString(mes) + "\r\n                        ) as tab1\r\n                        group by Fecha\r\n                        ) as t2\r\n                        on t1.fecha>=t2.Fecha \r\n                        group by t1.Fecha, t1.ventas  ) as tabla") : ("SELECT  N, \r\n                        CASE DIA \r\n                             when 'Monday' then 'LUNES'\r\n                             when 'Tuesday' then 'MARTES'\r\n                             when 'Wednesday' then 'MIERCOLES'\r\n                             when 'Thursday' then 'JUEVES'\r\n                             when 'Friday' then 'VIERNES'\r\n                             when 'Saturday' then 'SABADO'\r\n                             when 'Sunday' then 'DOMINGO'\r\n                        END as DIA, FECHA, VENTAS,ACUMULADO,ROUND( ACUMULADO/N ,2) AS PROMEDIO\r\n                        from\r\n                        (\r\n                        select ROW_NUMBER() OVER(ORDER BY t1.fecha ASC) as N, datename(dw, t1.fecha) as Dia, t1.FECHA, t1.VENTAS, sum(t2.ventas) as ACUMULADO\r\n                        from\r\n                        (\r\n                        select Fecha, sum (Pago) as VENTAS from \r\n                        (\r\n                        --\r\n\r\n                        select convert (date, Hora) as FECHA, DetalleCuenta.Pago \r\n                        from DetalleCuenta left join productos on DetalleCuenta.productoID=productos.id\r\n                        left join tiposproductos on productos.tipoproductoID=tiposproductos.TipoProductoID\r\n                        left join familias on tiposproductos.FamiliaId =Familias.FamiliaID \r\n                        where  year(DetalleCuenta.Hora) =" + Conversions.ToString(anho) + " and month(DetalleCuenta.Hora)=" + Conversions.ToString(mes) + "\r\n                        and Familias.FamiliaID =" + Conversions.ToString(idFamilia) + "\r\n\r\n                        --\r\n                        ) as tab1\r\n                        group by Fecha\r\n                        ) as t1\r\n                        inner join \r\n                        (\r\n                        select Fecha, sum (Pago) as ventas from \r\n                        (\r\n                        select convert (date, Hora) as FECHA, DetalleCuenta.Pago \r\n                        from DetalleCuenta left join productos on DetalleCuenta.productoID=productos.id\r\n                        left join tiposproductos on productos.tipoproductoID=tiposproductos.TipoProductoID\r\n                        left join familias on tiposproductos.FamiliaId =Familias.FamiliaID \r\n                         where  year(DetalleCuenta.Hora) =" + Conversions.ToString(anho) + " and month(DetalleCuenta.Hora)=" + Conversions.ToString(mes) + "\r\n                        and Familias.FamiliaID =" + Conversions.ToString(idFamilia) + "\r\n                        ) as tab1\r\n                        group by Fecha\r\n                        ) as t2\r\n                        on t1.fecha>=t2.Fecha \r\n                        group by t1.Fecha, t1.ventas  ) as tabla"));
		if (configuration.gMODO_ACCESS == 0)
		{
			return BD.ConsultaVer(text);
		}
		return null;
	}
}
