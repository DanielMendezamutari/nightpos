using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsProductosUsos
{
	private int ProductoUsoID;

	private double cantidad;

	private int ProductoID;

	private int DetalleCuentaID;

	private int produccionID;

	private int preProcesamientoID;

	private DateTime Fecha;

	public int _ProductoUsoID
	{
		get
		{
			return ProductoUsoID;
		}
		set
		{
			ProductoUsoID = value;
		}
	}

	public double _cantidad
	{
		get
		{
			return cantidad;
		}
		set
		{
			cantidad = value;
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

	public int _produccionID
	{
		get
		{
			return produccionID;
		}
		set
		{
			produccionID = value;
		}
	}

	public int _preProcesamientoID
	{
		get
		{
			return preProcesamientoID;
		}
		set
		{
			preProcesamientoID = value;
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

	public clsProductosUsos()
	{
		cantidad = 0.0;
		DetalleCuentaID = 0;
		ProductoID = 0;
		preProcesamientoID = 0;
		produccionID = 0;
		Fecha = DateAndTime.Now;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "ProductosUsos", " ProductoUsoID=" + ProductoUsoID);
		if (dataTable.Rows.Count > 0)
		{
			ProductoUsoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ProductoUsoID"])) ? ((object)0) : dataTable.Rows[0]["ProductoUsoID"]);
			produccionID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["produccionID"])) ? ((object)0) : dataTable.Rows[0]["produccionID"]);
			preProcesamientoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["preProcesamientoID"])) ? ((object)0) : dataTable.Rows[0]["preProcesamientoID"]);
			DetalleCuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DetalleCuentaID"])) ? ((object)0) : dataTable.Rows[0]["DetalleCuentaID"]);
			cantidad = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["cantidad"])) ? ((object)0) : dataTable.Rows[0]["cantidad"]);
			ProductoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ProductoID"])) ? ((object)0) : dataTable.Rows[0]["ProductoID"]);
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("ProductosUsos.ProductoUsoID,ProductosUsos.Cantidad,ProductoID,ProductosUsos.DetalleCuentaID", "ProductosUsos");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("ProductosUsos", "cantidad=" + Conversion.Str(cantidad) + ",ProductoID=" + ProductoID + ",DetalleCuentaID=" + DetalleCuentaID + ",flagSync=NULL", "ProductoUsoID=" + ProductoUsoID);
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

	public int Insertar(BD_SQL bd1 = null)
	{
		checked
		{
			int result;
			try
			{
				if (bd1 != null)
				{
					bd1.ConsultaInsertar(string.Concat(Conversion.Str(cantidad) + "," + Conversions.ToString(ProductoID) + ",", Conversions.ToString(DetalleCuentaID), ",", Conversions.ToString(preProcesamientoID), ",", Conversions.ToString(produccionID), ",", VariableGeneral.ArmarFecha(Fecha)), "ProductosUsos(Cantidad,ProductoID,DetalleCuentaID,preProcesamientoID,ProduccionID,Fecha)", ref ProductoUsoID);
					result = ProductoUsoID;
				}
				else if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					ProductoUsoID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ProductoUsoID)", "ProductosUsos").Rows[0][0]), 0));
					ProductoUsoID++;
					BD.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(ProductoUsoID) + ",", Conversion.Str(cantidad), ",", Conversions.ToString(ProductoID), ","), Conversions.ToString(DetalleCuentaID), ",", Conversions.ToString(preProcesamientoID), ",", Conversions.ToString(produccionID), ",", VariableGeneral.ArmarFecha(Fecha)), "ProductosUsos(ProductoUsoID,Cantidad,ProductoID,DetalleCuentaID,preProcesamientoID,ProduccionID,Fecha)");
					result = ProductoUsoID;
				}
				else
				{
					BD.ConsultaInsertar3(string.Concat(Conversion.Str(cantidad) + "," + Conversions.ToString(ProductoID) + ",", Conversions.ToString(DetalleCuentaID), ",", Conversions.ToString(preProcesamientoID), ",", Conversions.ToString(produccionID), ",", VariableGeneral.ArmarFecha(Fecha)), "ProductosUsos(Cantidad,ProductoID,DetalleCuentaID,preProcesamientoID,ProduccionID,Fecha)", ref ProductoUsoID);
					result = ProductoUsoID;
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

	public int ModificarCantidad(BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (bd1 != null)
			{
				bd1.ConsultaModificar("ProductosUsos", "cantidad= Round( Cantidad -" + Conversion.Str(cantidad) + ",2),flagSync=NULL", "DetalleCuentaID=" + DetalleCuentaID + " and ProductoID= " + ProductoID);
				result = 1;
			}
			else
			{
				BD.ConsultaModificar("ProductosUsos", "cantidad=Round( Cantidad -" + Conversion.Str(cantidad) + ",2),flagSync=NULL", "DetalleCuentaID=" + DetalleCuentaID + " and ProductoID= " + ProductoID);
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

	public int EliminarXpara(BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (bd1 != null)
			{
				if (bd1.ConsultaEliminar("ProductosUsos", "DetalleCuentaID = " + DetalleCuentaID) == 0)
				{
					Interaction.MsgBox("no se puede eliminar Preparacion, se encuentra en uso");
					result = 0;
				}
				else
				{
					result = 1;
				}
			}
			else if (BD.ConsultaEliminar("ProductosUsos", "DetalleCuentaID = " + DetalleCuentaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Preparacion, se encuentra en uso");
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

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("ProductosUsos", "ProductoUsoID = " + ProductoUsoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Preparacion, se encuentra en uso");
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

	public DataTable devolverDevolverProductosUsos(DateTime FechaSI, DateTime FechaSF, int tipoProd, bool agrupados)
	{
		if (!agrupados)
		{
			if (tipoProd == 0)
			{
				return BD.ConsultaVer("select * from (((select ProductosUsos.ProductoUsoID, 'Venta' as Tipo,  Productos.Nombre , DetalleCuenta.Hora as fecha, Productos.CantidadML as Contenido,(ProductosUsos.Cantidad) as 'Cantidad',Productos.Costo as CostoActual from ((ProductosUsos inner join DetalleCuenta on ProductosUsos.DetalleCuentaID = DetalleCuenta.ID) inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") ) union (select ProductosUsos.ProductoUsoID,'Produccion' as Tipo,  Productos.Nombre,produccion.fecha as fecha, Productos.CantidadML as Contenido,(ProductosUsos.Cantidad) as 'Cantidad', Productos.Costo  from ((ProductosUsos inner join Produccion on ProductosUsos.ProduccionID = Produccion.ProduccionID) inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ")) union (select ProductosUsos.ProductoUsoID,'PreProcesamiento' as Tipo, Productos.Nombre,Preprocesamientos.fecha as hora, Productos.CantidadML as Contenido,(ProductosUsos.Cantidad) as 'Cantidad', Productos.Costo  from ((ProductosUsos inner join Preprocesamientos on ProductosUsos.PreProcesamientoID = Preprocesamientos.PreprocesamientoID )inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") )) ) as tab1 order by nombre");
			}
			return BD.ConsultaVer("select * from (((select ProductosUsos.ProductoUsoID, 'Venta' as Tipo,Productos.Nombre , DetalleCuenta.Hora as fecha, Productos.CantidadML as Contenido,(ProductosUsos.Cantidad) as 'Cantidad',Productos.Costo as CostoActual from ((ProductosUsos inner join DetalleCuenta on ProductosUsos.DetalleCuentaID = DetalleCuenta.ID) inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ")and Productos.TipoProductoID=" + Conversions.ToString(tipoProd) + " ) union (select ProductosUsos.ProductoUsoID, 'Produccion' as Tipo, Productos.Nombre,produccion.fecha as fecha, Productos.CantidadML as Contenido,(ProductosUsos.Cantidad) as 'Cantidad', Productos.Costo  from ((ProductosUsos inner join Produccion on ProductosUsos.ProduccionID = Produccion.ProduccionID) inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ")and Productos.TipoProductoID=" + Conversions.ToString(tipoProd) + " ) union (select ProductosUsos.ProductoUsoID,'PreProcesamiento' as Tipo, Productos.Nombre,Preprocesamientos.fecha as hora, Productos.CantidadML as Contenido,(ProductosUsos.Cantidad) as 'Cantidad', Productos.Costo  from ((ProductosUsos inner join Preprocesamientos on ProductosUsos.PreProcesamientoID = Preprocesamientos.PreprocesamientoID )inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") and Productos.TipoProductoID=" + Conversions.ToString(tipoProd) + " )) ) as tab1 order by nombre");
		}
		if (tipoProd == 0)
		{
			return BD.ConsultaVer(" * from (select Nombre, Contenido, ROUND(Sum(Cantidad),3) as 'Cantidad Usada', ROUND(avg(Costo),3) as CostoActual, ROUND(Sum(Cantidad)*avg(Costo),3) as CostoTotalActual", "(select productos.ID, Productos.Nombre, Productos.CantidadML as Contenido,sum(ProductosUsos.Cantidad) as Cantidad, avg(Productos.Costo) as Costo  from ((ProductosUsos inner join DetalleCuenta on ProductosUsos.DetalleCuentaID = DetalleCuenta.ID) inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") group by productos.ID, Productos.Nombre, Productos.CantidadML union select productos.ID, Productos.Nombre, Productos.CantidadML as Contenido,sum(ProductosUsos.Cantidad) as Cantidad, avg(Productos.Costo) as Costo from ((ProductosUsos inner join Produccion on ProductosUsos.ProduccionID = Produccion.ProduccionID) inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") group by productos.ID, Productos.Nombre, Productos.CantidadML  union select productos.ID, Productos.Nombre, Productos.CantidadML as Contenido,sum(ProductosUsos.Cantidad) as Cantidad, avg(Productos.Costo) as Costo  from ((ProductosUsos inner join Preprocesamientos on ProductosUsos.PreProcesamientoID = Preprocesamientos.PreprocesamientoID )inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") group by productos.ID, Productos.Nombre, Productos.CantidadML ) as tab1", "", "", "Nombre, Contenido) as tab1 order by Nombre ");
		}
		return BD.ConsultaVer(" * from (select Nombre, Contenido, ROUND(Sum(Cantidad),3) as 'Cantidad Usada', ROUND(avg(Costo),3) as CostoActual, ROUND(Sum(Cantidad)*avg(Costo),3) as CostoTotalActual", "(select productos.ID, Productos.Nombre, Productos.CantidadML as Contenido,sum(ProductosUsos.Cantidad) as Cantidad,avg(Productos.Costo) as Costo from ((ProductosUsos inner join DetalleCuenta on ProductosUsos.DetalleCuentaID = DetalleCuenta.ID) inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") and Productos.TipoProductoID=" + Conversions.ToString(tipoProd) + " group by productos.ID, Productos.Nombre, Productos.CantidadML union select productos.ID, Productos.Nombre, Productos.CantidadML as Contenido,sum(ProductosUsos.Cantidad) as Cantidad, avg(Productos.Costo) as Costo  from ((ProductosUsos inner join Produccion on ProductosUsos.ProduccionID = Produccion.ProduccionID) inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") and Productos.TipoProductoID=" + Conversions.ToString(tipoProd) + " group by productos.ID, Productos.Nombre, Productos.CantidadML  union select productos.ID, Productos.Nombre, Productos.CantidadML as Contenido,sum(ProductosUsos.Cantidad) as Cantidad, avg(Productos.Costo) as Costo  from ((ProductosUsos inner join Preprocesamientos on ProductosUsos.PreProcesamientoID = Preprocesamientos.PreprocesamientoID )inner join   Productos on ProductosUsos.ProductoID = Productos.ID)  where   (ProductosUsos.Fecha  between " + VariableGeneral.ArmarFecha(FechaSI) + " and " + VariableGeneral.ArmarFecha(FechaSF) + ") and Productos.TipoProductoID=" + Conversions.ToString(tipoProd) + " group by productos.ID, Productos.Nombre, Productos.CantidadML ) as tab1", "", "", "Nombre, Contenido) as tab1 order by Nombre ");
	}

	public DataTable DevolvoverProdUsosXDetalle()
	{
		return BD.ConsultaVer("Cantidad, ProductoID", "ProductosUsos", "DetalleCuentaID= " + Conversions.ToString(DetalleCuentaID));
	}

	public DataTable devolverDevolverProductosUsosSrPollo(DateTime FechaSI, DateTime FechaSF, string prod)
	{
		return BD.ConsultaVer("Sum(ProductosUsos.Cantidad) as 'Cantidad Usada'", "((ProductosUsos left join DetalleCuenta on ProductosUsos.DetalleCuentaID = DetalleCuenta.ID) left join   Productos on ProductosUsos.ProductoID = Productos.ID)", "DetalleCuenta.Hora  between" + VariableGeneral.ArmarFecha(FechaSI) + "and " + VariableGeneral.ArmarFecha(FechaSF) + " and Productos.Nombre like '%" + prod + "%'");
	}
}
