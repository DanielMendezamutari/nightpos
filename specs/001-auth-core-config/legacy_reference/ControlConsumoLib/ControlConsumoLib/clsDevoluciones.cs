using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDevoluciones
{
	private int DevolucionId;

	private DateTime Fecha;

	private double Cantidad;

	private string Observacion;

	private int DetalleCuentaID;

	public int _DevolucionId
	{
		get
		{
			return DevolucionId;
		}
		set
		{
			DevolucionId = value;
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

	public clsDevoluciones()
	{
		DevolucionId = 0;
		Fecha = DateAndTime.Today;
		Cantidad = 0.0;
		Observacion = "";
		DetalleCuentaID = 0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Devoluciones", " DevolucionId=" + DevolucionId);
		if (dataTable.Rows.Count > 0)
		{
			DevolucionId = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DevolucionId"])) ? ((object)0) : dataTable.Rows[0]["DevolucionId"]);
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Today) : dataTable.Rows[0]["Fecha"]);
			Cantidad = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Cantidad"])) ? ((object)0) : dataTable.Rows[0]["Cantidad"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			DetalleCuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DetalleCuentaID"])) ? ((object)0) : dataTable.Rows[0]["DetalleCuentaID"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("*", "Devoluciones");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Devoluciones", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",Cantidad=" + Conversion.Str(Cantidad) + ",Observacion='" + Observacion + "',flagSync=NULL", "DevolucionId=" + DevolucionId);
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
			BD.ConsultaInsertar3(VariableGeneral.ArmarFecha(Fecha) + "," + Conversion.Str(Cantidad) + ",'" + Observacion + "'," + Conversions.ToString(DetalleCuentaID), "Devoluciones(Fecha, Cantidad, Observacion, DetalleCuentaID)", ref DevolucionId);
			result = DevolucionId;
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
			if (BD.ConsultaEliminar("Devoluciones", "DevolucionId = " + DevolucionId) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Devolucion, se encuentra en uso");
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

	public DataTable DevolverUltimasCuentas(int idCliente)
	{
		return BD.ConsultaVer("  top 50 DetalleCuenta.ID,Productos.Nombre ,DetalleCuenta.Hora, DetalleCuenta.Cantidad,DetalleCuenta.Pago ,DetalleCuenta.Debe,Devoluciones.cantidad as Devolucion, Productos.ID as productoID, Devoluciones.Observacion", "((DetalleCuenta left join visitas on DetalleCuenta.VisitaID = Visitas.ID) left join Productos on DetalleCuenta.ProductoID=Productos.ID) left join Devoluciones on DetalleCuenta.ID =devoluciones.DetalleCuentaID", " Visitas.ClienteID=" + idCliente, "DetalleCuenta.ID desc");
	}

	public DataTable DevolverXID()
	{
		return BD.ConsultaVer("*", "Devoluciones", "DetalleCuentaID = " + Conversions.ToString(DetalleCuentaID));
	}
}
