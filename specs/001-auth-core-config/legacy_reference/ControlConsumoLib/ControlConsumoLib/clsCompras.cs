using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCompras
{
	private int CompraID;

	private int NroComprobante;

	private DateTime FechaRecepcion;

	private double ImporteCompra;

	private string Comentarios;

	private string ProveedorID;

	private int MeseroID;

	private int AlmacenID;

	public int _CompraID
	{
		get
		{
			return CompraID;
		}
		set
		{
			CompraID = value;
		}
	}

	public int _NroComprobante
	{
		get
		{
			return NroComprobante;
		}
		set
		{
			NroComprobante = value;
		}
	}

	public DateTime _FechaRecepcion
	{
		get
		{
			return FechaRecepcion;
		}
		set
		{
			FechaRecepcion = value;
		}
	}

	public double _ImporteCompra
	{
		get
		{
			return ImporteCompra;
		}
		set
		{
			ImporteCompra = value;
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
			Comentarios = value;
		}
	}

	public int _ProveedorID
	{
		get
		{
			if (Operators.CompareString(ProveedorID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(ProveedorID);
		}
		set
		{
			if (value == 0)
			{
				ProveedorID = "null";
			}
			else
			{
				ProveedorID = Conversions.ToString(value);
			}
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

	public int _AlmacenID
	{
		get
		{
			return AlmacenID;
		}
		set
		{
			AlmacenID = value;
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa As Proveedores", "Compras LEFT JOIN Proveedores On Compras.ProveedorID = Proveedores.ProveedorID");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa As Proveedores", "Compras LEFT JOIN Proveedores On Compras.ProveedorID = Proveedores.ProveedorID ) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int DevolverSgteNro()
	{
		DataTable dataTable = BD.ConsultaVer("max(NroComprobante)", "Compras");
		if (dataTable.Rows.Count > 0)
		{
			if (Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0])))
			{
				return 1;
			}
			return Conversions.ToInteger(Operators.AddObject(dataTable.Rows[0][0], 1));
		}
		return 1;
	}

	public DataTable devolverComprasCodigos()
	{
		return BD.ConsultaVer("Compras.CompraID,Compras.NroComprobante", "Compras", "", "NroComprobante");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Compras", "NroComprobante=" + NroComprobante + ",FechaRecepcion=" + VariableGeneral.ArmarFecha(FechaRecepcion) + ",ImporteCompra=" + Conversion.Str(ImporteCompra) + ",Comentarios='" + Comentarios + "',ProveedorID=" + ProveedorID.ToString() + ",AlmacenID=" + AlmacenID + ",flagSync=NULL", "CompraID=" + CompraID);
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
				CompraID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(CompraID)", "Compras").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(CompraID) + "," + NroComprobante + ",", VariableGeneral.ArmarFecha(FechaRecepcion), ","), Conversion.Str(ImporteCompra), ",'", Comentarios, "',"), ProveedorID.ToString(), ","), Conversions.ToString(MeseroID), ","), Conversions.ToString(AlmacenID)) ?? "", "Compras(CompraID,NroComprobante,FechaRecepcion,ImporteCompra,Comentarios,ProveedorID,MeseroID,AlmacenID)");
				CompraID = Conversions.ToInteger(BD.ConsultaVer("max(CompraID)", "Compras").Rows[0][0]);
				result = CompraID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(NroComprobante + ",", VariableGeneral.ArmarFecha(FechaRecepcion), ","), Conversion.Str(ImporteCompra), ",'", Comentarios, "',"), ProveedorID.ToString(), ","), Conversions.ToString(MeseroID), ","), Conversions.ToString(AlmacenID)) ?? "", "Compras(NroComprobante,FechaRecepcion,ImporteCompra,Comentarios,ProveedorID,MeseroID,AlmacenID)", ref CompraID);
				result = CompraID;
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
			if (BD.ConsultaEliminar("Compras", "CompraID = " + CompraID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Compra, se encuentra en uso");
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

	public DataTable DevolverReporteTodos(DateTime desde, DateTime hasta)
	{
		if (AlmacenID == 0)
		{
			return BD.ConsultaVer("Compras.NroComprobante,Compras.FechaRecepcion as 'Fecha Pedido', Compras.ImporteCompra ,(Proveedores.NombreEmpresa) As Proveedores,Comentarios, Almacenes.Nombre as Almacen,case when( Gastos_Facturas.Gastos_FacturaID>0) then 'True' else 'False'end as Facturado", "((((Compras LEFT JOIN Proveedores On Compras.ProveedorID  = Proveedores.ProveedorID) \r\n                                left join Almacenes on Compras.AlmacenID=Almacenes.AlmacenID)\r\n                                left join gastos on compras.compraid=Gastos.CompraID  )\r\n                                left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas.GastoID )", "FechaRecepcion between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "FechaRecepcion");
		}
		return BD.ConsultaVer("Compras.NroComprobante,Compras.FechaRecepcion as 'Fecha Pedido', Compras.ImporteCompra ,(Proveedores.NombreEmpresa) As Proveedores,Comentarios, Almacenes.Nombre as Almacen,case when( Gastos_Facturas.Gastos_FacturaID>0) then 'True' else 'False'end as Facturado", " ((((Compras LEFT JOIN Proveedores On Compras.ProveedorID  = Proveedores.ProveedorID)\r\n                                Left Join Almacenes on Compras.AlmacenID=Almacenes.AlmacenID)\r\n                                Left Join gastos on compras.compraid=Gastos.CompraID  )\r\n                                Left Join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas.GastoID )", "FechaRecepcion between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " and Almacenes.AlmacenID= " + AlmacenID, "FechaRecepcion");
	}

	public DataTable DevolverReporte(DateTime desde, DateTime hasta)
	{
		if (AlmacenID == 0)
		{
			return BD.ConsultaVer("Compras.NroComprobante,Compras.FechaRecepcion as 'Fecha Pedido', Compras.ImporteCompra ,(Proveedores.NombreEmpresa) As Proveedores,Comentarios, Almacenes.Nombre as Almacen,case when( Gastos_Facturas.Gastos_FacturaID>0) then 'True' else 'False'end as Facturado", "((((Compras LEFT JOIN Proveedores On Compras.ProveedorID  = Proveedores.ProveedorID) \r\n                                left join Almacenes on Compras.AlmacenID=Almacenes.AlmacenID)\r\n                                left join gastos on compras.compraid=Gastos.CompraID  )\r\n                                left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas.GastoID )", "FechaRecepcion between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " and Compras.ProveedorID=" + ProveedorID.ToString(), "FechaRecepcion");
		}
		return BD.ConsultaVer("Compras.NroComprobante,Compras.FechaRecepcion as 'Fecha Pedido', Compras.ImporteCompra ,(Proveedores.NombreEmpresa) As Proveedores,Comentarios, Almacenes.Nombre as Almacen,case when( Gastos_Facturas.Gastos_FacturaID>0) then 'True' else 'False'end as Facturado", "((((Compras LEFT JOIN Proveedores On Compras.ProveedorID  = Proveedores.ProveedorID) \r\n                                left join Almacenes on Compras.AlmacenID=Almacenes.AlmacenID)\r\n                                left join gastos on compras.compraid=Gastos.CompraID  )\r\n                                left join Gastos_Facturas on Gastos .GastoID =Gastos_Facturas.GastoID )", "FechaRecepcion between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " and Compras.ProveedorID=" + ProveedorID.ToString() + " and Almacenes.AlmacenID= " + AlmacenID, "FechaRecepcion");
	}

	public void DevolverDatosComprasPorComprasID(ref int Nro, ref DateTime fecha, ref string nombre, ref float Importe, ref string Comentarios)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("Compras.NroComprobante,Compras.FechaRecepcion, Proveedores.NombreEmpresa,Compras.ImporteCompra,Compras.Comentarios", "Compras left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID ", "CompraID =" + Conversions.ToString(CompraID));
		if (dataTable.Rows.Count > 0)
		{
			Nro = Conversions.ToInteger(dataTable.Rows[0][0]);
			fecha = Conversions.ToDate(dataTable.Rows[0][1]);
			nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][2]), ""));
			Importe = Conversions.ToSingle(dataTable.Rows[0][3]);
			Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][4]), ""));
		}
		else
		{
			Nro = 0;
			fecha = DateAndTime.Now;
			nombre = "";
			Importe = 0f;
			Comentarios = "";
		}
	}

	public DataTable Devolver1()
	{
		return BD.ConsultaVer("Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion as FechaPedido,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa As Proveedores, tab1.Monto as Pagado, tab2.Monto as Programado, Compras.AlmacenID ", "(((Compras LEFT JOIN Proveedores On Compras.ProveedorID = Proveedores.ProveedorID)  left join (select CompraID,sum(Monto) as Monto from Gastos where FechaSalida is not null group by CompraID) as tab1 on Compras.CompraID=tab1.CompraID) left join (select CompraID,sum(Monto) as Monto from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID)", "1=1", "Compras.CompraID desc", "Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion,Compras.ImporteCompra, Compras.Comentarios, Proveedores.NombreEmpresa, tab1.Monto, tab2.Monto, Compras.AlmacenID  ");
	}

	public DataTable DevolverParciales()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion as FechaPedido,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa As Proveedores, tab1.Monto1 as Pagado, tab2.Monto2 as Programado, Compras.AlmacenID ", "(((Compras LEFT JOIN Proveedores On Compras.ProveedorID = Proveedores.ProveedorID) left join (select CompraID,sum(Monto) as Monto1 from Gastos where FechaSalida is not null group by CompraID) as tab1 on Compras.CompraID=tab1.CompraID) left join (select CompraID,sum(Monto) as Monto2 from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID)", " Compras.FechaRecepcion > DATEADD('m',-1,Now)", "Compras.CompraID desc", "Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa, tab1.Monto1,tab2.Monto2,Compras.AlmacenID  ");
		}
		return BD.ConsultaVer("Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion as FechaPedido,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa As Proveedores, tab1.Monto as Pagado, tab2.Monto as Programado,Compras.AlmacenID ", "(((Compras LEFT JOIN Proveedores On Compras.ProveedorID = Proveedores.ProveedorID) left join (select CompraID,sum(Monto) as Monto from Gastos where FechaSalida is not null group by CompraID) as tab1 on Compras.CompraID=tab1.CompraID) left join (select CompraID,sum(Monto) as Monto from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID)", " Compras.FechaRecepcion > DATEADD(MM,-1,GETDATE())", "Compras.CompraID desc", "Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa, tab1.Monto,tab2.Monto,Compras.AlmacenID  ");
	}

	public DataTable DevolverDeudas()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion as FechaPedido,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa As Proveedores,iif(ISNULL(tab1.Monto)=0,tab1.Monto,0) as Pagado,iif(ISNULL(tab2.Monto)=0,tab2.Monto,0) as Programado,Compras.AlmacenID ", "(((Compras LEFT JOIN Proveedores On Compras.ProveedorID = Proveedores.ProveedorID) left join (select CompraID,sum(Gastos.Monto) as Monto from Gastos where FechaSalida is not null group by CompraID) as tab1 on Compras.CompraID=tab1.CompraID) left join (select CompraID,sum(Gastos.Monto) as Monto from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID)", "1=1 and Compras.ImporteCompra> iif( ISNULL(tab1.Monto)=0, tab1.Monto,0 ) ", "Compras.CompraID desc", "Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa, tab1.Monto,tab2.Monto,Compras.AlmacenID  ");
		}
		return BD.ConsultaVer("Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion as FechaPedido,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa As Proveedores,ISNULL(tab1.Monto,0) as Pagado, ISNULL(tab2.Monto,0) as Programado,Compras.AlmacenID ", "(((Compras LEFT JOIN Proveedores On Compras.ProveedorID = Proveedores.ProveedorID) left join (select CompraID,sum(Gastos.Monto) as Monto from Gastos where FechaSalida is not null group by CompraID) as tab1 on Compras.CompraID=tab1.CompraID) left join (select CompraID,sum(Gastos.Monto) as Monto from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID)", "1=1 and Compras.ImporteCompra>ISNULL(tab1.Monto,0) ", "Compras.CompraID desc", "Compras.CompraID,Compras.NroComprobante,Compras.FechaRecepcion,Compras.ImporteCompra,Compras.Comentarios,Proveedores.NombreEmpresa, tab1.Monto,tab2.Monto, Compras.AlmacenID  ");
	}

	public int DevolverMesero()
	{
		DataTable dataTable = BD.ConsultaVer("MeseroID", "Compras", "CompraID = " + Conversions.ToString(CompraID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		return 0;
	}
}
