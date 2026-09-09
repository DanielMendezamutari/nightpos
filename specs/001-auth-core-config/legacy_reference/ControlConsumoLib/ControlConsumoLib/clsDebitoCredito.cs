using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDebitoCredito
{
	private int DebitoCreditoID;

	private DateTime FechaEmision;

	private int NroNotaCreditoDebito;

	private string Codigo;

	private decimal MontoDevuelto;

	private bool Anulada;

	private DateTime? FechaAnulacion;

	private int FacturaID;

	private int EstadoSiat;

	private int cufdID;

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

	public DateTime _FechaEmision
	{
		get
		{
			return FechaEmision;
		}
		set
		{
			FechaEmision = value;
		}
	}

	public int? _NroNotaCreditoDebito
	{
		get
		{
			return NroNotaCreditoDebito;
		}
		set
		{
			NroNotaCreditoDebito = value.Value;
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

	public decimal _MontoDevuelto
	{
		get
		{
			return MontoDevuelto;
		}
		set
		{
			MontoDevuelto = value;
		}
	}

	public bool _Anulada
	{
		get
		{
			return Anulada;
		}
		set
		{
			Anulada = value;
		}
	}

	public DateTime? _FechaAnulacion
	{
		get
		{
			return FechaAnulacion;
		}
		set
		{
			FechaAnulacion = value;
		}
	}

	public int _FacturaID
	{
		get
		{
			return FacturaID;
		}
		set
		{
			FacturaID = value;
		}
	}

	public int _EstadoSiat
	{
		get
		{
			return EstadoSiat;
		}
		set
		{
			EstadoSiat = value;
		}
	}

	public int _cufdID
	{
		get
		{
			return cufdID;
		}
		set
		{
			cufdID = value;
		}
	}

	public clsDebitoCredito()
	{
		DebitoCreditoID = 0;
		FechaEmision = DateTime.MinValue;
		NroNotaCreditoDebito = 0;
		Codigo = string.Empty;
		MontoDevuelto = 0m;
		Anulada = false;
		FechaAnulacion = null;
		FacturaID = 0;
		EstadoSiat = 0;
		cufdID = 0;
	}

	public void LlenarClase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "DebitoCredito", "DebitoCreditoID=" + DebitoCreditoID);
		if (dataTable.Rows.Count > 0)
		{
			DebitoCreditoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DebitoCreditoID"])) ? ((object)0) : dataTable.Rows[0]["DebitoCreditoID"]);
			FechaEmision = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaEmision"])) ? null : dataTable.Rows[0]["FechaEmision"]);
			NroNotaCreditoDebito = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroNotaCreditoDebito"])) ? null : dataTable.Rows[0]["NroNotaCreditoDebito"]);
			Codigo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Codigo"])) ? string.Empty : dataTable.Rows[0]["Codigo"]);
			MontoDevuelto = Conversions.ToDecimal(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoDevuelto"])) ? null : dataTable.Rows[0]["MontoDevuelto"]);
			Anulada = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Anulada"])) ? null : dataTable.Rows[0]["Anulada"]);
			FechaAnulacion = (DateTime?)(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaAnulacion"])) ? null : dataTable.Rows[0]["FechaAnulacion"]);
			FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? null : dataTable.Rows[0]["FacturaID"]);
			EstadoSiat = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EstadoSiat"])) ? null : dataTable.Rows[0]["EstadoSiat"]);
			cufdID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["cufdID"])) ? null : dataTable.Rows[0]["cufdID"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("*", "DebitoCredito");
	}

	public int Insertar()
	{
		int result;
		try
		{
			BD.ConsultaInsertar3(VariableGeneral.ArmarFecha(FechaEmision) + "," + Conversions.ToString(NroNotaCreditoDebito) + ",'" + Codigo + "'," + Conversion.Str(MontoDevuelto) + "," + VariableGeneral.armarBolean(Anulada) + "," + (FechaAnulacion.HasValue ? VariableGeneral.ArmarFecha(FechaAnulacion.Value) : "NULL") + "," + Conversions.ToString(FacturaID) + "," + Conversions.ToString(EstadoSiat) + ",'" + Conversions.ToString(cufdID) + "'", "DebitoCredito(FechaEmision, NroNotaCreditoDebito, Codigo, MontoDevuelto, Anulada, FechaAnulacion, FacturaID, EstadoSiat, cufdID)", ref DebitoCreditoID);
			result = DebitoCreditoID;
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

	public object setEstado()
	{
		object result;
		try
		{
			result = ((BD.ConsultaModificar("DebitoCredito", "EstadoSIAT=" + Conversions.ToString(EstadoSiat) + ",flagSync=NULL", "DebitoCreditoID= " + Conversions.ToString(DebitoCreditoID)) == 0) ? ((object)false) : ((object)true));
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

	public bool setCodigoRecepcion(string codigoRecepcion)
	{
		bool result;
		try
		{
			result = ((BD.ConsultaModificar("DebitoCredito", "codigoRecepcion='" + codigoRecepcion + "',flagSync=NULL", "DebitoCreditoID= " + Conversions.ToString(DebitoCreditoID)) != 0) ? true : false);
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

	public object anular()
	{
		object result;
		try
		{
			result = ((BD.ConsultaModificar("DebitoCredito", "Anulada=" + VariableGeneral.armarBolean(1) + ",FechaAnulacion=" + VariableGeneral.ArmarFecha(DateAndTime.Now) + ",flagSync=NULL", "DebitoCreditoID= " + Conversions.ToString(DebitoCreditoID)) == 0) ? ((object)false) : ((object)true));
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

	public object RevertirAnular()
	{
		object result;
		try
		{
			result = ((BD.ConsultaModificar("DebitoCredito", "Anulada=" + VariableGeneral.armarBolean(0) + ",FechaAnulacion=NULL,flagSync=NULL", "DebitoCreditoID= " + Conversions.ToString(DebitoCreditoID)) == 0) ? ((object)false) : ((object)true));
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

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("DebitoCredito", "DebitoCreditoID = " + DebitoCreditoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar BlackList, se encuentra en uso");
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
