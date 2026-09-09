using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDebitoCreditoDetalle
{
	private int DebitoCreditoDetalleID;

	private double Cantidad;

	private double SubTotal;

	private int DetalleCuentaID;

	private int DebitoCreditoID;

	public int _DebitoCreditoDetalleID
	{
		get
		{
			return DebitoCreditoDetalleID;
		}
		set
		{
			DebitoCreditoDetalleID = value;
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

	public double _SubTotal
	{
		get
		{
			return SubTotal;
		}
		set
		{
			SubTotal = value;
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

	public int _DebitoCreditoID
	{
		get
		{
			return DebitoCreditoID;
		}
		set
		{
			DebitoCreditoID = value;
		}
	}

	public clsDebitoCreditoDetalle()
	{
		DebitoCreditoDetalleID = 0;
		Cantidad = 0.0;
		DetalleCuentaID = 0;
		DebitoCreditoID = 0;
		SubTotal = 0.0;
	}

	public void LlenarClase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "DebitoCreditoDetalle", "DebitoCreditoDetalleID=" + DebitoCreditoDetalleID);
		if (dataTable.Rows.Count > 0)
		{
			DebitoCreditoDetalleID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DebitoCreditoDetalleID"])) ? ((object)0) : dataTable.Rows[0]["DebitoCreditoDetalleID"]);
			Cantidad = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Cantidad"])) ? ((object)0) : dataTable.Rows[0]["Cantidad"]);
			SubTotal = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["SubTotal"])) ? ((object)0) : dataTable.Rows[0]["SubTotal"]);
			DetalleCuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DetalleCuentaID"])) ? ((object)0) : dataTable.Rows[0]["DetalleCuentaID"]);
			DebitoCreditoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DebitoCreditoID"])) ? ((object)0) : dataTable.Rows[0]["DebitoCreditoID"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("*", "DebitoCreditoDetalle");
	}

	public int Insertar()
	{
		int result;
		try
		{
			BD.ConsultaInsertar3("'" + Conversion.Str(Cantidad) + "','" + Conversion.Str(SubTotal) + "'," + Conversions.ToString(DetalleCuentaID) + "," + Conversions.ToString(DebitoCreditoID), "DebitoCreditoDetalle(Cantidad,SubTotal, DetalleCuentaID, DebitoCreditoID)", ref DebitoCreditoDetalleID);
			result = DebitoCreditoDetalleID;
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
			if (BD.ConsultaEliminar("DebitoCreditoDetalle", "DebitoCreditoDetalleID = " + DebitoCreditoDetalleID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar DebitoCreditoDetalle, se encuentra en uso");
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
}
