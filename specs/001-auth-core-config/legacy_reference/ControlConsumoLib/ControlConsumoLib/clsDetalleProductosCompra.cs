using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDetalleProductosCompra
{
	private int DetalleProductoCompraID;

	private DateTime PosibleFechaEntrega;

	private DateTime FechaEntrega;

	private bool PosibleFechaEntregaCh;

	private bool FechaEntregaCh;

	private double Cantidad;

	private double CostoUnitario;

	private int CompraID;

	private string ProductoID;

	private double CostoBruto;

	public int _DetalleProductoCompraID
	{
		get
		{
			return DetalleProductoCompraID;
		}
		set
		{
			DetalleProductoCompraID = value;
		}
	}

	public DateTime _PosibleFechaEntrega
	{
		get
		{
			return PosibleFechaEntrega;
		}
		set
		{
			PosibleFechaEntrega = value;
		}
	}

	public DateTime _FechaEntrega
	{
		get
		{
			return FechaEntrega;
		}
		set
		{
			FechaEntrega = value;
		}
	}

	public bool _PosibleFechaEntregaCh
	{
		get
		{
			return PosibleFechaEntregaCh;
		}
		set
		{
			PosibleFechaEntregaCh = value;
		}
	}

	public bool _FechaEntregaCh
	{
		get
		{
			return FechaEntregaCh;
		}
		set
		{
			FechaEntregaCh = value;
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

	public double _CostoUnitario
	{
		get
		{
			return CostoUnitario;
		}
		set
		{
			CostoUnitario = value;
		}
	}

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

	public double _CostoBruto
	{
		get
		{
			return CostoBruto;
		}
		set
		{
			CostoBruto = value;
		}
	}

	public void cargarProductoIDdeCompra()
	{
		DataTable dataTable = BD.ConsultaVer("ProductoID", "DetalleProductosCompra", "DetalleProductosCompra.DetalleProductoCompraID=" + DetalleProductoCompraID);
		if (dataTable.Rows.Count > 0)
		{
			ProductoID = Conversions.ToString(dataTable.Rows[0][0]);
		}
		else
		{
			ProductoID = Conversions.ToString(0);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("DetalleProductosCompra.DetalleProductoCompraID,DetalleProductosCompra.PosibleFechaEntrega,DetalleProductosCompra.FechaEntrega,DetalleProductosCompra.Cantidad,DetalleProductosCompra.CostoUnitario,DetalleProductosCompra.CostoBruto,DetalleProductosCompra.CompraID, Nombre As Productos,Productos.ID", "DetalleProductosCompra LEFT JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID");
	}

	public DataTable devolverDetalleProductosCompraXcompraID()
	{
		return BD.ConsultaVer("DetalleProductosCompra.DetalleProductoCompraID,DetalleProductosCompra.PosibleFechaEntrega,DetalleProductosCompra.FechaEntrega,DetalleProductosCompra.Cantidad,DetalleProductosCompra.CostoUnitario,DetalleProductosCompra.CostoBruto,DetalleProductosCompra.CompraID, Nombre As Productos, Productos.ID,Round(DetalleProductosCompra.Cantidad*DetalleProductosCompra.CostoBruto,2) as Total", "DetalleProductosCompra LEFT JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID", "DetalleProductosCompra.CompraID=" + CompraID);
	}

	public DataTable devolverMaxFechaRecepcionXcompraID()
	{
		return BD.ConsultaVer("max(fechaentrega)", "DetalleProductosCompra", "CompraID=" + CompraID);
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleProductosCompra", "PosibleFechaEntrega=" + VariableGeneral.ArmarFecha(PosibleFechaEntrega, PosibleFechaEntregaCh) + ",FechaEntrega=" + VariableGeneral.ArmarFecha(FechaEntrega, FechaEntregaCh) + ",Cantidad=" + Conversion.Str(Cantidad) + ",CostoUnitario=" + Conversion.Str(CostoUnitario) + ",CompraID=" + CompraID + ",ProductoID=" + ProductoID.ToString() + ",CostoBruto=" + Conversion.Str(CostoBruto) + ",flagSync=NULL", "DetalleProductoCompraID=" + DetalleProductoCompraID);
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
				DetalleProductoCompraID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(DetalleProductoCompraID)", "DetalleProductosCompra").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(DetalleProductoCompraID) + "," + VariableGeneral.ArmarFecha(PosibleFechaEntrega, PosibleFechaEntregaCh) + ",", VariableGeneral.ArmarFecha(FechaEntrega, FechaEntregaCh), ","), Conversion.Str(Cantidad), ","), Conversion.Str(CostoUnitario), ","), CompraID.ToString(), ","), ProductoID.ToString(), ","), Conversion.Str(CostoBruto)) ?? "", "DetalleProductosCompra(DetalleProductoCompraID,PosibleFechaEntrega,FechaEntrega,Cantidad,CostoUnitario,CompraID,ProductoID,CostoBruto)");
				DetalleProductoCompraID = Conversions.ToInteger(BD.ConsultaVer("max(DetalleProductoCompraID)", "DetalleProductosCompra").Rows[0][0]);
				result = DetalleProductoCompraID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(VariableGeneral.ArmarFecha(PosibleFechaEntrega, PosibleFechaEntregaCh) + ",", VariableGeneral.ArmarFecha(FechaEntrega, FechaEntregaCh), ","), Conversion.Str(Cantidad), ","), Conversion.Str(CostoUnitario), ","), CompraID.ToString(), ","), ProductoID.ToString(), ","), Conversion.Str(CostoBruto)) ?? "", "DetalleProductosCompra(PosibleFechaEntrega,FechaEntrega,Cantidad,CostoUnitario,CompraID,ProductoID,CostoBruto)", ref DetalleProductoCompraID);
				result = DetalleProductoCompraID;
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
			if (BD.ConsultaEliminar("DetalleProductosCompra", "DetalleProductoCompraID = " + DetalleProductoCompraID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar DetalleProductoCompra, se encuentra en uso");
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

	public DataTable devolverReporteProductosCompraxfecha(DateTime fechaI, DateTime fechaF, int almacenID)
	{
		if (almacenID == 0)
		{
			return BD.ConsultaVer("familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre as Producto, SUM(DetalleProductosCompra.Cantidad) AS 'Cantidad Total',  DetalleProductosCompra.CostoUnitario as 'Costo Unitario',SUM(DetalleProductosCompra.Cantidad) *DetalleProductosCompra.CostoUnitario as Total, Almacenes.Nombre as Almacen", "((Productos INNER JOIN  DetalleProductosCompra ON Productos.ID = DetalleProductosCompra.ProductoID) INNER JOIN Compras ON DetalleProductosCompra.CompraID = Compras.CompraID) left join TiposProductos on Productos.TipoProductoID = TiposProductos.TipoProductoID left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId left join Almacenes on Compras.AlmacenID=Almacenes.AlmacenID", "DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechaI) + " and " + VariableGeneral.ArmarFecha(fechaF) + " ", "'Cantidad Total'", "familias.Descripcion, TiposProductos.Descripcion, Productos.Nombre, DetalleProductosCompra.CostoUnitario,Almacenes.Nombre");
		}
		return BD.ConsultaVer("familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre as Producto, SUM(DetalleProductosCompra.Cantidad) AS 'Cantidad Total',  DetalleProductosCompra.CostoUnitario as 'Costo Unitario',SUM(DetalleProductosCompra.Cantidad) *DetalleProductosCompra.CostoUnitario as Total,Almacenes.Nombre as Almacen", "((Productos INNER JOIN  DetalleProductosCompra ON Productos.ID = DetalleProductosCompra.ProductoID) INNER JOIN Compras ON DetalleProductosCompra.CompraID = Compras.CompraID) left join TiposProductos on Productos.TipoProductoID = TiposProductos.TipoProductoID left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId left join Almacenes on Compras.AlmacenID=Almacenes.AlmacenID", "DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechaI) + " and " + VariableGeneral.ArmarFecha(fechaF) + " and Compras.AlmacenID=  " + Conversions.ToString(almacenID) + " ", "'Cantidad Total'", "familias.Descripcion, TiposProductos.Descripcion, Productos.Nombre, DetalleProductosCompra.CostoUnitario, Almacenes.Nombre");
	}

	public DataTable devolverReporteProductosCompraxfechaXcompraID()
	{
		return BD.ConsultaVer("familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre as Producto, SUM(DetalleProductosCompra.Cantidad) AS 'Cantidad Total',  DetalleProductosCompra.CostoUnitario as 'Costo Unitario',SUM(DetalleProductosCompra.Cantidad) *DetalleProductosCompra.CostoUnitario as Total,Almacenes.Nombre as Almacen", "((Productos INNER JOIN  DetalleProductosCompra ON Productos.ID = DetalleProductosCompra.ProductoID) INNER JOIN Compras ON DetalleProductosCompra.CompraID = Compras.CompraID) left join TiposProductos on Productos.TipoProductoID = TiposProductos.TipoProductoID left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId  left join Almacenes on Compras.AlmacenID=Almacenes.AlmacenID", " Compras.CompraID= " + Conversions.ToString(CompraID), "'Cantidad Total'", "familias.Descripcion, TiposProductos.Descripcion, Productos.Nombre, DetalleProductosCompra.CostoUnitario, Compras.FechaRecepcion, Almacenes.Nombre");
	}

	public DataTable devolverDetalleProveedorPgDeuda(int Idproveedor, int idAlmacen)
	{
		if (idAlmacen == 0)
		{
			if (Idproveedor == 0)
			{
				if (configuration.gMODO_ACCESS == 1)
				{
					return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras,iif(ISNULL(tab1.deuda)=0,tab1.deuda,0) as Total_Pago, sum(importeCompra)- iif(ISNULL(tab1.deuda)=0,tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen      ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID ))left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", "", "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda, Almacenes.Nombre");
				}
				return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , tab2.FechaProgramada, Almacenes.Nombre As Almacen ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID )) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", "", "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre");
			}
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen       ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID )) left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", "Proveedores.ProveedorID =" + Conversions.ToString(Idproveedor), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda,Almacenes.Nombre");
			}
			return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , tab2.FechaProgramada,  Almacenes.Nombre As Almacen     ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID )) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", "Proveedores.ProveedorID = " + Conversions.ToString(Idproveedor), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre");
		}
		if (Idproveedor == 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras,iif(ISNULL(tab1.deuda)=0,tab1.deuda,0) as Total_Pago, sum(importeCompra)- iif(ISNULL(tab1.deuda)=0,tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen       ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID ) )left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda,Almacenes.Nombre");
			}
			return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda ,  tab2.FechaProgramada, Almacenes.Nombre As Almacen     ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID ) ) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen       ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID ) )left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen) + " and Proveedores.ProveedorID =" + Conversions.ToString(Idproveedor), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda,Almacenes.Nombre");
		}
		return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda  , tab2.FechaProgramada,  Almacenes.Nombre As Almacen    ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID )) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen) + " and Proveedores.ProveedorID = " + Conversions.ToString(Idproveedor), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre");
	}

	public DataTable devolverDetalleDeudasVencidas(int Idproveedor, int idAlmacen)
	{
		if (idAlmacen == 0)
		{
			if (Idproveedor == 0)
			{
				if (configuration.gMODO_ACCESS == 1)
				{
					return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras,iif(ISNULL(tab1.deuda)=0,tab1.deuda,0) as Total_Pago, sum(importeCompra)- iif(ISNULL(tab1.deuda)=0,tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta   ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID ))left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", "", "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
				}
				return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , tab2.FechaProgramada, Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID )) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", "FechaProgramada < GETDATE()", "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
			}
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta   ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID )) left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", "Proveedores.ProveedorID =" + Conversions.ToString(Idproveedor), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda,Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
			}
			return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , tab2.FechaProgramada,  Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID )) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", "Proveedores.ProveedorID = " + Conversions.ToString(Idproveedor) + "and FechaProgramada < GETDATE()", "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
		}
		if (Idproveedor == 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras,iif(ISNULL(tab1.deuda)=0,tab1.deuda,0) as Total_Pago, sum(importeCompra)- iif(ISNULL(tab1.deuda)=0,tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta    ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID ) )left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda,Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
			}
			return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda ,  tab2.FechaProgramada, Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta  ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID ) ) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen) + "and FechaProgramada < GETDATE()", "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta  ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID ) )left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen) + " and Proveedores.ProveedorID =" + Conversions.ToString(Idproveedor), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda,Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
		}
		return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda  , tab2.FechaProgramada,  Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta  ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID )) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen) + " and Proveedores.ProveedorID = " + Conversions.ToString(Idproveedor) + "and FechaProgramada < GETDATE()", "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
	}

	public DataTable devolverPagosProgramados(int idProveedor, int idAlmacen, DateTime Desde, DateTime Hasta)
	{
		if (idAlmacen == 0)
		{
			if (idProveedor == 0)
			{
				if (configuration.gMODO_ACCESS == 1)
				{
					return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras,iif(ISNULL(tab1.deuda)=0,tab1.deuda,0) as Total_Pago, sum(importeCompra)- iif(ISNULL(tab1.deuda)=0,tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID ))left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", "", "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
				}
				return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , tab2.FechaProgramada, Almacenes.Nombre As Almacen , Banco, TitularCuenta, NroCuenta", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID )) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", "FechaProgramada between " + VariableGeneral.ArmarFecha(Desde) + " and " + VariableGeneral.ArmarFecha(Hasta), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
			}
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID )) left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", "Proveedores.ProveedorID =" + Conversions.ToString(idProveedor), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda,Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
			}
			return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , tab2.FechaProgramada,  Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID )) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", "Proveedores.ProveedorID = " + Conversions.ToString(idProveedor) + "and FechaProgramada between " + VariableGeneral.ArmarFecha(Desde) + " and " + VariableGeneral.ArmarFecha(Hasta), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
		}
		if (idProveedor == 0)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras,iif(ISNULL(tab1.deuda)=0,tab1.deuda,0) as Total_Pago, sum(importeCompra)- iif(ISNULL(tab1.deuda)=0,tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen , Banco, TitularCuenta, NroCuenta", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID ) )left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda,Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
			}
			return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda ,  tab2.FechaProgramada, Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID ) ) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen) + "and FechaProgramada between " + VariableGeneral.ArmarFecha(Desde) + " and " + VariableGeneral.ArmarFecha(Hasta), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda , Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1.CompraID=Compras.CompraID ) )left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen) + " and Proveedores.ProveedorID =" + Conversions.ToString(idProveedor), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras.FechaRecepcion ,Compras.Comentarios,tab1.deuda,Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
		}
		return BD.ConsultaVer("Compras.NroComprobante, Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios  , sum(importeCompra) as Total_Compras, ISNULL(tab1.deuda,0) as Total_Pago, sum(importeCompra)- ISNULL(tab1.deuda,0)  as Total_Deuda  , tab2.FechaProgramada,  Almacenes.Nombre As Almacen, Banco, TitularCuenta, NroCuenta    ", "(((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select compras.CompraID , sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID  where Gastos.FechaSalida Is Not null  group by  compras.CompraID) as tab1 on tab1 .CompraID   =Compras.CompraID )) left join (select CompraID, max(FechaProgramada) as FechaProgramada from Gastos where FechaProgramada is not null group by CompraID) as tab2 on Compras.CompraID=tab2.CompraID left join almacenes on Compras.AlmacenID=Almacenes.AlmacenID  ", " Almacenes.AlmacenID= " + Conversions.ToString(idAlmacen) + " and Proveedores.ProveedorID = " + Conversions.ToString(idProveedor) + "and FechaProgramada between " + VariableGeneral.ArmarFecha(Desde) + " and " + VariableGeneral.ArmarFecha(Hasta), "", "Compras.NroComprobante,Proveedores.NombreEmpresa, Compras .FechaRecepcion ,Compras .Comentarios,tab1 .deuda, tab2.FechaProgramada, Almacenes.Nombre, Banco, TitularCuenta, NroCuenta");
	}

	public DataTable devolverDetalleProveedorPgDeudaCh()
	{
		return BD.ConsultaVer("Compras.CompraID ,Proveedores.NombreEmpresa , Compras.FechaRecepcion as Fecha  , sum(importeCompra) as Total_Compras,CASE WHEN tab1.deuda is null then 0 else tab1.deuda end as Total_Pago,sum(importeCompra) - CASE WHEN tab1.deuda is null then 0 else tab1.deuda end   as Total_Deuda    ", "((Compras left join Proveedores on compras.ProveedorID=Proveedores.ProveedorID ) Left Join (select Compras.CompraID , compras.ProveedorID, sum(Monto ) as deuda from gastos  left join Compras on  gastos.CompraID =Compras.CompraID where Gastos.FechaSalida Is Not null group by  Compras.CompraID , compras.ProveedorID) as tab1 on tab1 .CompraID  =Compras.CompraID  )  ", "ImporteCompra >0", "", "Compras.CompraID ,Proveedores.NombreEmpresa,tab1 .deuda, Compras.FechaRecepcion having(sum(importeCompra) - CASE WHEN tab1.deuda is null then 0 else tab1.deuda end >0)");
	}

	public DataTable devolverDetalleCompraProducto(DateTime fechai, DateTime fechaF, int almacenID)
	{
		if (almacenID == 0)
		{
			if (Conversions.ToDouble(ProductoID) > 0.0)
			{
				if (CompraID > 0)
				{
					return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal ,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal, Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID)  left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID  left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId ", " Productos .ID = " + ProductoID + " and Compras.CompraID=" + CompraID);
				}
				return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID)  left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID  left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", "  Productos .ID = " + ProductoID + " and  (DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + ") ");
			}
			if (CompraID > 0)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID)   left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId ", "Compras.CompraID=" + CompraID);
			}
			return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  ,Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID)  left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID  left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", " DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " ");
		}
		if (Conversions.ToDouble(ProductoID) > 0.0)
		{
			if (CompraID > 0)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal ,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal, Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID  left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", " Almacenes. AlmacenID = " + almacenID + " and Productos .ID = " + ProductoID + " and Compras.CompraID=" + CompraID);
			}
			return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID  left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", " Almacenes. AlmacenID = " + almacenID + " and Productos .ID = " + ProductoID + " and  (DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + ") ");
		}
		if (CompraID > 0)
		{
			return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId ", " Almacenes. AlmacenID = " + almacenID + " and Compras.CompraID=" + CompraID);
		}
		return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  ,Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID  left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", " Almacenes. AlmacenID = " + almacenID + " and DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " ");
	}

	public DataTable devolverDetalleCompraProveedor(DateTime fechai, DateTime fechaF, int ProveedorID, int AlmacenID)
	{
		if (AlmacenID == 0)
		{
			if (Conversions.ToDouble(ProductoID) > 0.0)
			{
				if (ProveedorID > 0)
				{
					return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID ) left join Almacenes on compras.AlmacenID =Almacenes.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", "  Productos .ID = " + ProductoID + " and Proveedores.ProveedorID =  " + Conversions.ToString(ProveedorID) + "  and  (DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + ") ");
				}
				return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on compras.AlmacenID =Almacenes.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", "  Productos .ID = " + ProductoID + " and  (DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + ") ");
			}
			if (ProveedorID > 0)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on compras.AlmacenID =Almacenes.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", " Proveedores.ProveedorID =  " + Conversions.ToString(ProveedorID) + " and DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " ");
			}
			return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on compras.AlmacenID =Almacenes.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", " DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " ");
		}
		if (Conversions.ToDouble(ProductoID) > 0.0)
		{
			if (ProveedorID > 0)
			{
				return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal, Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", "Almacenes. AlmacenID = " + AlmacenID + " and Productos .ID = " + ProductoID + " and Proveedores.ProveedorID =  " + Conversions.ToString(ProveedorID) + "  and  (DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + ") ");
			}
			return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto,  DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", "Almacenes. AlmacenID = " + AlmacenID + " and Productos .ID = " + ProductoID + " and  (DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + ") ");
		}
		if (ProveedorID > 0)
		{
			return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", "Almacenes. AlmacenID = " + AlmacenID + " and Proveedores.ProveedorID =  " + Conversions.ToString(ProveedorID) + " and DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " ");
		}
		return BD.ConsultaVer("Compras.NroComprobante, Productos.Codigo, Familias.Descripcion as Familia, TiposProductos.Descripcion as Categoria, Productos.Nombre As Producto, DetalleProductosCompra.FechaEntrega as 'Fecha Entrega',DetalleProductosCompra.Cantidad,Productos.Presentacion,DetalleProductosCompra.CostoBruto as PrecioUnitario, Round(DetalleProductosCompra.CostoBruto*DetalleProductosCompra.Cantidad,2) as PrecioTotal,DetalleProductosCompra.CostoUnitario, Round(DetalleProductosCompra.CostoUnitario*DetalleProductosCompra.Cantidad,2) as CostoTotal  , Proveedores.NombreEmpresa,Compras.Comentarios, Almacenes.Nombre as Almacen", "(((DetalleProductosCompra INNER JOIN Productos On DetalleProductosCompra.ProductoID = Productos.ID) inner join Compras on Compras.CompraID=DetalleProductosCompra.CompraID) left join Proveedores on Compras.ProveedorID=Proveedores.ProveedorID) left join Almacenes on Almacenes.AlmacenID=Compras.AlmacenID left join TiposProductos on TiposProductos.TipoProductoID = Productos.TipoProductoID   left join Familias on Familias.FamiliaID = TiposProductos.FamiliaId", "Almacenes. AlmacenID = " + AlmacenID + " and DetalleProductosCompra.FechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " or DetalleProductosCompra.PosibleFechaEntrega between " + VariableGeneral.ArmarFecha(fechai) + " and " + VariableGeneral.ArmarFecha(fechaF) + " ");
	}

	public DataTable devolverGastosSalidaProgramada(DateTime FechaSI, DateTime FechaSF, DateTime FechaPI, DateTime FechaPF)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("TiposGastos.Descripcion AS TiposGastos, Gastos.FechaSalida as 'Fecha Salida', Gastos.FechaProgramada as 'Fecha Programada',iif( Gastos.FechaSalida is null, 0,Gastos.Monto) as Pagado, iif( Gastos.FechaSalida is not null, 0,Gastos.Monto) as PorPagar, Gastos.RecibiFactura as 'Con Factura', Gastos.Efectivo_Cheque, iif(Gastos.CompraID is null, 0, 1) as DeCompra, Compras.NroComprobante", "Gastos INNER JOIN Personal ON Gastos.PersonalID = Personal.PersonalID INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID LEFT OUTER JOIN Compras ON Gastos.CompraID = Compras.CompraID", " (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
		}
		return BD.ConsultaVer("TiposGastos.Descripcion AS TiposGastos, Gastos.FechaSalida as 'Fecha Salida', Gastos.FechaProgramada as 'Fecha Programada',CASE WHEN  Gastos.FechaSalida is null  THEN  0 ELSE Gastos.Monto  END as Pagado, CASE WHEN  Gastos.FechaSalida is null  THEN  Gastos.Monto ELSE 0  END as PorPagar, Gastos.RecibiFactura as 'Con Factura', Gastos.Efectivo_Cheque,CASE WHEN  Gastos.CompraID is null  THEN  0 ELSE  1 END as DeCompra, Compras.NroComprobante", "Gastos INNER JOIN Personal ON Gastos.PersonalID = Personal.PersonalID INNER JOIN  TiposGastos ON Gastos.TipoGastoID = TiposGastos.TipoGastoID LEFT OUTER JOIN Compras ON Gastos.CompraID = Compras.CompraID", " (FechaSalida between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") or  (FechaProgramada between " + VariableGeneral.ArmarFecha(FechaPI) + " and " + VariableGeneral.ArmarFecha(FechaPF) + ") ", "Gastos.FechaSalida, Gastos.FechaProgramada");
	}

	public void DevolverDataSetComproGastos(ref DataSet data, string DatNombre)
	{
		string text = "\tselect Productos.Nombre,Productos.Descripcion, DetalleProductosCompra.Cantidad, DetalleProductosCompra.CostoBruto as CostoUnitario ,(DetalleProductosCompra.Cantidad*DetalleProductosCompra.CostoBruto ) as MontoTotal ,0 as estado,CostoUnitario as CostoContable,(DetalleProductosCompra.Cantidad*DetalleProductosCompra.CostoUnitario ) as CostoTotal \t  ";
		text = text + "\tfrom DetalleProductosCompra inner join Productos  on DetalleProductosCompra.ProductoID =Productos.ID where DetalleProductosCompra.CompraID   = " + Conversions.ToString(CompraID);
		BD.ConsultaVerDataset(ref data, text, DatNombre);
	}

	public int DevolverCompraExistente()
	{
		if (BD.ConsultaVer("*", "DetalleProductosCompra", "CompraID=" + Conversions.ToString(CompraID) + " and productoID = " + ProductoID).Rows.Count > 0)
		{
			return 1;
		}
		return 0;
	}

	public int ActualizarCostos()
	{
		int result;
		try
		{
			BD.ConsultaModificar("DetalleProductosCompra", "CostoUnitario=" + Conversion.Str(CostoUnitario) + ",CostoBruto=" + Conversion.Str(CostoBruto) + ",flagSync=NULL", "DetalleProductoCompraID=" + DetalleProductoCompraID);
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
