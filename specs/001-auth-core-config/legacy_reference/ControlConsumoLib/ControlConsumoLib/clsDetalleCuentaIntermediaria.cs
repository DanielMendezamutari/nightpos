using System;
using System.Data;
using ConfigToptech;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDetalleCuentaIntermediaria
{
	private int ID;

	private int Cantidad;

	private int VisitaID;

	private int ProductoID;

	private string Comentarios;

	private string meseroID;

	private string pedidoID;

	private int MesaID;

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

	public int _Cantidad
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

	public int _VisitaID
	{
		get
		{
			return VisitaID;
		}
		set
		{
			VisitaID = value;
		}
	}

	public string _pedidoID
	{
		get
		{
			return pedidoID;
		}
		set
		{
			pedidoID = value;
		}
	}

	public int _ProductoID
	{
		get
		{
			return ProductoID;
		}
		set
		{
			ProductoID = value;
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

	public int _MesaID
	{
		get
		{
			return MesaID;
		}
		set
		{
			MesaID = value;
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

	public DataTable ToReturnXpedido(ref string error1)
	{
		if (configuration.gAgruparPedidos)
		{
			return BD.ConsultaVerSinAlertas("sum(Cantidad) as Cantidad,VisitaID as VisitaID,ProductoID," + VariableGeneral.setconcatStr("Comentarios") + " as Observaciones,MeseroID as tomoPedidoID,PedidoID,MesaID,Productos.Precio,Productos.Nombre as Producto, ManejarStock,sum(Cantidad)* Productos.Precio as Debe,0 as Pago,CASE WHEN  Imp1.nombre is null THEN ( CASE WHEN Impresoras.Nombre is null then ''  else   Impresoras.Nombre end) else Imp1.nombre end as Impresora, DetalleCuentaIntermediaria.Extras as ProductosCombo, DetalleCuentaIntermediaria.AsistenteId as AsistenteID, CASE WHEN  Categorias_Almacenes.AlmacenID is null THEN TiposProductos.AlmacenID  else  Categorias_Almacenes.AlmacenID end as AlmacenId, 0 as DetalleID, TiposProductos.DocumentoSector", "DetalleCuentaIntermediaria inner join Productos on Productos.ID=DetalleCuentaIntermediaria.ProductoID inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID  inner join Mesas on Mesas.ID = MesaID left join  Salones on Salones.SalonID=Mesas.SalonID left join Impresoras on Impresoras.ImpresoraID=TiposProductos.ImpresoraID left join Categorias_Impresoras  on TiposProductos.TipoProductoID = Categorias_Impresoras.CategoriaId and Categorias_Impresoras.SalonId=Salones.salonID left join Impresoras as Imp1 on Imp1.ImpresoraID= Categorias_Impresoras.ImpresoraID  left join Categorias_Almacenes on TiposProductos.TipoProductoID = Categorias_Almacenes.CategoriaId and Categorias_Almacenes.SalonId=Salones.salonID", "PedidoID='" + pedidoID.ToString() + "'", "VisitaID,ProductoID,MeseroID,PedidoID,MesaID,Productos.Precio,Productos.Nombre,ManejarStock,Imp1.nombre,Impresoras.Nombre , detalleCuentaIntermediaria.AsistenteId, TiposProductos.AlmacenID, Categorias_Almacenes.AlmacenID,DetalleCuentaIntermediaria.Extras,TiposProductos.Orden, TiposProductos.DocumentoSector", "TiposProductos.Orden, min(DetalleCuentaIntermediaria.ID)", ref error1);
		}
		return BD.ConsultaVerSinAlertas("DetalleCuentaIntermediaria.ID,Cantidad,VisitaID,ProductoID,Comentarios as Observaciones,MeseroID as tomoPedidoID,PedidoID,MesaID,Productos.Precio,Productos.Nombre as Producto, ManejarStock,Cantidad*Productos.Precio as Debe,0 as Pago, CASE WHEN  Imp1.nombre is null THEN ( CASE WHEN Impresoras.Nombre is null then ''  else   Impresoras.Nombre end) else Imp1.nombre end as Impresora, DetalleCuentaIntermediaria.Extras as ProductosCombo, DetalleCuentaIntermediaria.AsistenteId as AsistenteID, CASE WHEN  Categorias_Almacenes.AlmacenID is null THEN TiposProductos.AlmacenID  else  Categorias_Almacenes.AlmacenID end as AlmacenId, 0 as DetalleID, TiposProductos.DocumentoSector", "DetalleCuentaIntermediaria inner join Productos on Productos.ID=DetalleCuentaIntermediaria.ProductoID inner join TiposProductos on TiposProductos.TipoProductoID=Productos.TipoProductoID  inner join Mesas on Mesas.ID = MesaID left join  Salones on Salones.SalonID=Mesas.SalonID left join Impresoras on Impresoras.ImpresoraID=TiposProductos.ImpresoraID left join Categorias_Impresoras  on TiposProductos.TipoProductoID = Categorias_Impresoras.CategoriaId and Categorias_Impresoras.SalonId=Salones.salonID left join Impresoras as Imp1 on Imp1.ImpresoraID= Categorias_Impresoras.ImpresoraID  left join Categorias_Almacenes on TiposProductos.TipoProductoID = Categorias_Almacenes.CategoriaId and Categorias_Almacenes.SalonId=Salones.salonID", "PedidoID='" + pedidoID.ToString() + "'", "", "DetalleCuentaIntermediaria.id", ref error1);
	}

	public DataTable ToReturnXpedidoPlataforma(int Plataforma, ref string error1, int meseroID)
	{
		checked
		{
			if ((Plataforma == 1) | (Plataforma == 4) | (Plataforma == 5))
			{
				if (configuration.gMODO_ACCESS == 1)
				{
					DataTable dataTable = BD.ConsultaVerSinAlertas("DeliveryApp_detalle.QUANTITY as Cantidad, 0 AS VisitaId, Productos.ID AS ProductoID, DeliveryApp_detalle.ORDER_NOTE AS Observaciones, " + Conversions.ToString(meseroID) + "  AS MeseroID1, 0 AS pedidoID, 0 AS MEsaID, ORDER_DISCOUNT+ORDER_PRICE as Precio, Productos.Nombre AS Producto, TiposProductos.ManejarStock, DeliveryApp_detalle.QUANTITY * (ORDER_PRICE) AS Debe, 0 AS Pago,  Impresoras.Nombre as Impresora, format(Productos1.ID,'') AS ProductosCombo, DeliveryApp_Detalle_Combo.ORDER_NAME as ProductosComboNombre, " + Conversions.ToString(meseroID) + " AS MeseroID,TiposProductos.AlmacenID,DeliveryApp_detalle.DeliveryDetalleID, TiposProductos.DocumentoSector", "(((((DeliveryApp INNER JOIN DeliveryApp_detalle ON DeliveryApp.DeliveryID = DeliveryApp_detalle.DeliveryAppID) LEFT JOIN Productos ON ( DeliveryApp_detalle.ORDER_SKU = Productos.CodigoPY  and  Productos.Borrado =" + VariableGeneral.armarBolean(0) + ")) LEFT JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) LEFT JOIN Impresoras ON TiposProductos.ImpresoraID = Impresoras.ImpresoraID) left join DeliveryApp_Detalle_Combo on  DeliveryApp_Detalle_Combo.DeliveryApp_DetalleID =DeliveryApp_detalle.DeliveryDetalleID) left join   Productos as Productos1 ON ( DeliveryApp_Detalle_Combo.ORDER_SKU = Productos1.CodigoPY and Productos1.borrado=" + VariableGeneral.armarBolean(0) + ")", "plataforma=" + Conversions.ToString(Plataforma) + " and Order_No=" + pedidoID + " order by DeliveryApp_detalle.DeliveryDetalleID", ref error1);
					if (dataTable.Rows.Count > 1)
					{
						int num = 0;
						int num2 = dataTable.Rows.Count - 1;
						for (int i = 0; i <= num2 && i < dataTable.Rows.Count; i++)
						{
							if (Operators.ConditionalCompareObjectEqual(num, dataTable.Rows[i]["DeliveryDetalleID"], TextCompare: false))
							{
								if ((Operators.CompareString(dataTable.Rows[i]["ProductosCombo"].ToString(), "", TextCompare: false) == 0) & (dataTable.Rows[i]["ProductosComboNombre"].ToString().Length > 0))
								{
									error1 = error1 + "Opcional no hizo " + dataTable.Rows[i]["ProductosComboNombre"].ToString() + "\r";
								}
								dataTable.Rows[i - 1]["ProductosCombo"] = Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable.Rows[i - 1]["ProductosCombo"], ","), dataTable.Rows[i]["ProductosCombo"]);
								dataTable.Rows.RemoveAt(i);
								i--;
							}
							else
							{
								num = Conversions.ToInteger(dataTable.Rows[i]["DeliveryDetalleID"]);
							}
						}
					}
					return dataTable;
				}
				return BD.ConsultaVerSinAlertas("DeliveryApp_detalle.QUANTITY as Cantidad, 0 AS VisitaId, Productos.ID AS ProductoID, DeliveryApp_detalle.ORDER_NOTE AS Observaciones, " + Conversions.ToString(meseroID) + "  AS MeseroID1, 0 AS pedidoID, 0 AS MEsaID, ORDER_DISCOUNT+ORDER_PRICE as Precio, Productos.Nombre AS Producto, TiposProductos.ManejarStock, DeliveryApp_detalle.QUANTITY * (ORDER_DISCOUNT+ORDER_PRICE) AS Debe, 0 AS Pago,  Impresoras.Nombre as Impresora, ( select " + VariableGeneral.setconcatStr("concat(ID , '-1-' ,FORMAT(Price, 'N2'))") + " from ( Select  Productos1.ID, DeliveryApp_Detalle_Combo as col2, Price  FROM DeliveryApp_Detalle_Combo INNER JOIN Productos As Productos1 On (DeliveryApp_Detalle_Combo.ORDER_SKU = Productos1.CodigoPY And Productos1.borrado=" + VariableGeneral.armarBolean(0) + ")   where DeliveryApp_Detalle_Combo.DeliveryApp_DetalleID =DeliveryApp_detalle.DeliveryDetalleID   union    Select PreparacionesComodines.DeProductoID ,PreparacionesComodines.PreparacionComodinID, PreparacionesComodines.Precio  from Productos As Pro2 inner join Preparaciones On Pro2.id=Preparaciones.ParaProductoID And Preparaciones.Cantidad=1 And    (select count(*) from PreparacionesComodines where PreparacionesComodines.PreparacionID = Preparaciones.PreparacionID)=1  inner join PreparacionesComodines on PreparacionesComodines.PreparacionID = Preparaciones.PreparacionID    where Pro2.id =  Productos.ID  And esCombo = " + VariableGeneral.armarBolean(1) + " ) as tab3  )  AS ProductosCombo,   " + Conversions.ToString(meseroID) + " AS MeseroID,TiposProductos.AlmacenID, TiposProductos.DocumentoSector", "(((DeliveryApp INNER JOIN DeliveryApp_detalle ON DeliveryApp.DeliveryID = DeliveryApp_detalle.DeliveryAppID) LEFT JOIN Productos ON ((DeliveryApp_detalle.ORDER_SKU = Productos.CodigoPY and Productos.CodigoPY<>'0' and Productos.Borrado =" + VariableGeneral.armarBolean(0) + ")  and ( DeliveryApp_detalle.ORDER_SKU = Productos.CodigoPY or Productos.Nombre ='Delivery')))  LEFT JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) LEFT JOIN Impresoras ON TiposProductos.ImpresoraID = Impresoras.ImpresoraID", "plataforma=" + Conversions.ToString(Plataforma) + " and Order_No=" + pedidoID, ref error1);
			}
			if (Plataforma == 3)
			{
				return BD.ConsultaVerSinAlertas("DeliveryApp_detalle.QUANTITY as Cantidad, 0 AS VisitaId, Productos.ID AS ProductoID, DeliveryApp_detalle.ORDER_NOTE AS Observaciones, " + Conversions.ToString(meseroID) + "  AS MeseroID1, 0 AS pedidoID, 0 AS MEsaID, ORDER_DISCOUNT+ORDER_PRICE as Precio, Productos.Nombre AS Producto, TiposProductos.ManejarStock, DeliveryApp_detalle.QUANTITY * (ORDER_PRICE) AS Debe, 0 AS Pago,  Impresoras.Nombre as Impresora,  ( select " + VariableGeneral.setconcatStr("ID") + " from ( Select  Productos1.ID, DeliveryApp_Detalle_Combo as col2  FROM DeliveryApp_Detalle_Combo INNER JOIN Productos As Productos1 On (DeliveryApp_Detalle_Combo.ORDER_SKU = Productos1.Codigo And Productos1.borrado=" + VariableGeneral.armarBolean(0) + ")   where DeliveryApp_Detalle_Combo.DeliveryApp_DetalleID =DeliveryApp_detalle.DeliveryDetalleID   union    Select PreparacionesComodines.DeProductoID ,PreparacionesComodines.PreparacionComodinID  from Productos As Pro2 inner join Preparaciones On Pro2.id=Preparaciones.ParaProductoID And Preparaciones.Cantidad=1 And    (select count(*) from PreparacionesComodines where PreparacionesComodines.PreparacionID = Preparaciones.PreparacionID)=1  inner join PreparacionesComodines on PreparacionesComodines.PreparacionID = Preparaciones.PreparacionID    where Pro2.id =  Productos.ID  And esCombo = " + VariableGeneral.armarBolean(1) + " ) as tab3  )  AS ProductosCombo,  " + Conversions.ToString(meseroID) + " AS MeseroID,TiposProductos.AlmacenID, TiposProductos.DocumentoSector", "(((DeliveryApp INNER JOIN DeliveryApp_detalle ON DeliveryApp.DeliveryID = DeliveryApp_detalle.DeliveryAppID) LEFT JOIN Productos ON (DeliveryApp_detalle.ORDER_SKU =  Productos.Codigo   and  Productos.Borrado =" + VariableGeneral.armarBolean(0) + ")) LEFT JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) LEFT JOIN Impresoras ON TiposProductos.ImpresoraID = Impresoras.ImpresoraID", "plataforma=" + Conversions.ToString(Plataforma) + " and Order_No='" + pedidoID + "'", ref error1);
			}
			if (configuration.gMODO_ACCESS == 1)
			{
				DataTable dataTable2 = BD.ConsultaVerSinAlertas("DeliveryApp_detalle.QUANTITY as Cantidad, 0 AS VisitaId, Productos.ID AS ProductoID, DeliveryApp_detalle.ORDER_NOTE AS Observaciones, " + Conversions.ToString(meseroID) + "  AS MeseroID1, 0 AS pedidoID, 0 AS MEsaID, ORDER_DISCOUNT+ORDER_PRICE as Precio, Productos.Nombre AS Producto, TiposProductos.ManejarStock, DeliveryApp_detalle.QUANTITY * (ORDER_PRICE) AS Debe, 0 AS Pago,  Impresoras.Nombre as Impresora, format(Productos1.ID,'') AS ProductosCombo, DeliveryApp_Detalle_Combo.ORDER_NAME as ProductosComboNombre, " + Conversions.ToString(meseroID) + " AS MeseroID,TiposProductos.AlmacenID,DeliveryApp_detalle.DeliveryDetalleID, TiposProductos.DocumentoSector", "(((((DeliveryApp INNER JOIN DeliveryApp_detalle ON DeliveryApp.DeliveryID = DeliveryApp_detalle.DeliveryAppID) LEFT JOIN Productos ON (DeliveryApp_detalle.ORDER_NAME =  IIf(InStr( Productos.NOMBRE,'(') > 0,  Mid( Productos.NOMBRE,1,InStr( Productos.NOMBRE,'(') -1 ),  Productos.NOMBRE)   and  Productos.Borrado =" + VariableGeneral.armarBolean(0) + ")) LEFT JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) LEFT JOIN Impresoras ON TiposProductos.ImpresoraID = Impresoras.ImpresoraID) left join DeliveryApp_Detalle_Combo on  DeliveryApp_Detalle_Combo.DeliveryApp_DetalleID =DeliveryApp_detalle.DeliveryDetalleID) left join   Productos as Productos1 ON (DeliveryApp_Detalle_Combo.ORDER_NAME = Productos1.NOMBRE and Productos1.borrado=" + VariableGeneral.armarBolean(0) + ")", "plataforma=" + Conversions.ToString(Plataforma) + " and Order_No=" + pedidoID + " order by DeliveryApp_detalle.DeliveryDetalleID", ref error1);
				if (dataTable2.Rows.Count > 1)
				{
					int num3 = 0;
					int num4 = dataTable2.Rows.Count - 1;
					for (int j = 0; j <= num4 && j < dataTable2.Rows.Count; j++)
					{
						if (Operators.ConditionalCompareObjectEqual(num3, dataTable2.Rows[j]["DeliveryDetalleID"], TextCompare: false))
						{
							if ((Operators.CompareString(dataTable2.Rows[j]["ProductosCombo"].ToString(), "", TextCompare: false) == 0) & (dataTable2.Rows[j]["ProductosComboNombre"].ToString().Length > 0))
							{
								error1 = error1 + "Opcional no hizo match " + dataTable2.Rows[j]["ProductosComboNombre"].ToString() + "\r";
							}
							dataTable2.Rows[j - 1]["ProductosCombo"] = Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable2.Rows[j - 1]["ProductosCombo"], ","), dataTable2.Rows[j]["ProductosCombo"]);
							dataTable2.Rows.RemoveAt(j);
							j--;
						}
						else
						{
							num3 = Conversions.ToInteger(dataTable2.Rows[j]["DeliveryDetalleID"]);
						}
					}
				}
				return dataTable2;
			}
			return BD.ConsultaVerSinAlertas("DeliveryApp_detalle.QUANTITY as Cantidad, 0 AS VisitaId, Productos.ID AS ProductoID, DeliveryApp_detalle.ORDER_NOTE AS Observaciones, " + Conversions.ToString(meseroID) + "  AS MeseroID1, 0 AS pedidoID, 0 AS MEsaID, ORDER_DISCOUNT+ORDER_PRICE as Precio, Productos.Nombre AS Producto, TiposProductos.ManejarStock, DeliveryApp_detalle.QUANTITY * (ORDER_PRICE) AS Debe, 0 AS Pago,  Impresoras.Nombre as Impresora,   ( select " + VariableGeneral.setconcatStr("ID") + " from ( Select  Productos1.ID, DeliveryApp_Detalle_Combo as col2  FROM DeliveryApp_Detalle_Combo INNER JOIN Productos As Productos1 On (DeliveryApp_Detalle_Combo.ORDER_NAME = iif(CHARINDEX('(',Productos1.NOMBRE) > 0, RTRIM(SUBSTRING(Productos1.NOMBRE,0,CHARINDEX('(',Productos1.NOMBRE))), Productos1.NOMBRE) And Productos1.borrado=" + VariableGeneral.armarBolean(0) + ")   where DeliveryApp_Detalle_Combo.DeliveryApp_DetalleID =DeliveryApp_detalle.DeliveryDetalleID   union    Select PreparacionesComodines.DeProductoID ,PreparacionesComodines.PreparacionComodinID  from Productos As Pro2 inner join Preparaciones On Pro2.id=Preparaciones.ParaProductoID And Preparaciones.Cantidad=1 And    (select count(*) from PreparacionesComodines where PreparacionesComodines.PreparacionID = Preparaciones.PreparacionID)=1  inner join PreparacionesComodines on PreparacionesComodines.PreparacionID = Preparaciones.PreparacionID    where Pro2.id =  Productos.ID  And esCombo = " + VariableGeneral.armarBolean(1) + " ) as tab3  )  AS ProductosCombo,   " + Conversions.ToString(meseroID) + " AS MeseroID,TiposProductos.AlmacenID, TiposProductos.DocumentoSector", "(((DeliveryApp INNER JOIN DeliveryApp_detalle ON DeliveryApp.DeliveryID = DeliveryApp_detalle.DeliveryAppID) LEFT JOIN Productos ON (DeliveryApp_detalle.ORDER_NAME = iif(CHARINDEX('(',Productos.NOMBRE) > 0, RTRIM(SUBSTRING(Productos.NOMBRE,0,CHARINDEX('(',Productos.NOMBRE))), Productos.NOMBRE)  and  Productos.Borrado =" + VariableGeneral.armarBolean(0) + ")) LEFT JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) LEFT JOIN Impresoras ON TiposProductos.ImpresoraID = Impresoras.ImpresoraID", "plataforma=" + Conversions.ToString(Plataforma) + " and Order_No=" + pedidoID, ref error1);
		}
	}

	public int EliminarXpedidoID()
	{
		int result;
		try
		{
			BD.ConsultaEliminar("DetalleCuentaIntermediaria", "PedidoID='" + pedidoID.ToString() + "'");
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
