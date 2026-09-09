using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCodigosFacturas
{
	private int CodigoID;

	private DateTime FechaDesde;

	private DateTime FechaHasta;

	private string Llave;

	private long autorizacion;

	private int NroFactura;

	private string NIT;

	public int NroFactura2;

	public bool Activo;

	public int _CodigoID
	{
		get
		{
			return CodigoID;
		}
		set
		{
			CodigoID = value;
		}
	}

	public long _autorizacion
	{
		get
		{
			return autorizacion;
		}
		set
		{
			autorizacion = value;
		}
	}

	public int _NroFactura
	{
		get
		{
			return NroFactura;
		}
		set
		{
			NroFactura = value;
		}
	}

	public DateTime _FechaDesde
	{
		get
		{
			return FechaDesde;
		}
		set
		{
			FechaDesde = value;
		}
	}

	public DateTime _FechaHasta
	{
		get
		{
			return FechaHasta;
		}
		set
		{
			FechaHasta = value;
		}
	}

	public string _NIT
	{
		get
		{
			return NIT;
		}
		set
		{
			NIT = value;
		}
	}

	public string _Llave
	{
		get
		{
			return Llave;
		}
		set
		{
			Llave = value;
		}
	}

	public bool _Activo
	{
		get
		{
			return Activo;
		}
		set
		{
			Activo = value;
		}
	}

	public clsCodigosFacturas()
	{
		FechaDesde = DateAndTime.Today;
		FechaHasta = DateAndTime.Today.AddDays(180.0);
		Llave = "";
		NroFactura = 0;
		autorizacion = 0L;
		NIT = "";
		Activo = true;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "CodigosFacturas", " CodigoID=" + CodigoID);
		if (dataTable.Rows.Count > 0)
		{
			CodigoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoID"])) ? ((object)0) : dataTable.Rows[0]["CodigoID"]);
			FechaDesde = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaDesde"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaDesde"]);
			FechaHasta = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaHasta"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaHasta"]);
			Llave = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Llave"])) ? "" : dataTable.Rows[0]["Llave"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? ((object)0) : dataTable.Rows[0]["NIT"]);
			NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroFactura"])) ? ((object)0) : dataTable.Rows[0]["NroFactura"]);
			autorizacion = Conversions.ToLong(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["autorizacion"])) ? ((object)0) : dataTable.Rows[0]["autorizacion"]);
			Activo = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Activo"])) ? ((object)true) : dataTable.Rows[0]["Activo"]);
		}
	}

	public bool DevolverCodigoPorID()
	{
		DataTable dataTable = BD.ConsultaVer("*", "CodigosFacturas", " CodigoID= " + Conversions.ToString(CodigoID));
		if (dataTable.Rows.Count > 0)
		{
			CodigoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoID"])) ? ((object)0) : dataTable.Rows[0]["CodigoID"]);
			FechaDesde = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaDesde"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaDesde"]);
			FechaHasta = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaHasta"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaHasta"]);
			Llave = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Llave"])) ? "" : dataTable.Rows[0]["Llave"]);
			NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroFactura"])) ? ((object)0) : dataTable.Rows[0]["NroFactura"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? ((object)0) : dataTable.Rows[0]["NIT"]);
			autorizacion = Conversions.ToLong(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["autorizacion"])) ? ((object)0) : dataTable.Rows[0]["autorizacion"]);
			Activo = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Activo"])) ? ((object)true) : dataTable.Rows[0]["Activo"]);
			return true;
		}
		CodigoID = 0;
		return false;
	}

	public bool VerificarFacturasLlaves(DateTime hoy)
	{
		DataTable dataTable = BD.ConsultaVer("*", "CodigosFacturas", VariableGeneral.ArmarFecha(hoy) + " between FechaDesde and FechaHasta and ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " and Activo= " + VariableGeneral.armarBolean(1), "FechaHasta desc");
		if (dataTable.Rows.Count > 0)
		{
			CodigoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoID"])) ? ((object)0) : dataTable.Rows[0]["CodigoID"]);
			FechaDesde = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaDesde"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaDesde"]);
			FechaHasta = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaHasta"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaHasta"]);
			Llave = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Llave"])) ? "" : dataTable.Rows[0]["Llave"]);
			NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroFactura"])) ? ((object)0) : dataTable.Rows[0]["NroFactura"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? ((object)0) : dataTable.Rows[0]["NIT"]);
			autorizacion = Conversions.ToLong(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["autorizacion"])) ? ((object)0) : dataTable.Rows[0]["autorizacion"]);
			Activo = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Activo"])) ? ((object)true) : dataTable.Rows[0]["Activo"]);
			return true;
		}
		CodigoID = 0;
		return false;
	}

	public bool DevolverCodigoActivo(DateTime hoy)
	{
		DataTable dataTable = BD.ConsultaVer("*", "CodigosFacturas", configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(hoy) + configuration.CaracterFecha + " between FechaDesde and FechaHasta and ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " and Activo= " + VariableGeneral.armarBolean(1));
		if (dataTable.Rows.Count > 0)
		{
			CodigoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoID"])) ? ((object)0) : dataTable.Rows[0]["CodigoID"]);
			FechaDesde = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaDesde"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaDesde"]);
			FechaHasta = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaHasta"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaHasta"]);
			Llave = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Llave"])) ? "" : dataTable.Rows[0]["Llave"]);
			NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroFactura"])) ? ((object)0) : dataTable.Rows[0]["NroFactura"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? ((object)0) : dataTable.Rows[0]["NIT"]);
			autorizacion = Conversions.ToLong(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["autorizacion"])) ? ((object)0) : dataTable.Rows[0]["autorizacion"]);
			NroFactura2 = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroFactura2"])) ? ((object)0) : dataTable.Rows[0]["NroFactura2"]);
			Activo = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Activo"])) ? ((object)true) : dataTable.Rows[0]["Activo"]);
			return true;
		}
		CodigoID = 0;
		return false;
	}

	public void DevolverUltimoNIT()
	{
		DataTable dataTable = BD.ConsultaVer("NIT", "CodigosFacturas", "ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID), "CodigoID");
		if (dataTable.Rows.Count > 0)
		{
			NIT = Conversions.ToString(checked(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[dataTable.Rows.Count - 1]["NIT"])) ? ((object)0) : dataTable.Rows[dataTable.Rows.Count - 1]["NIT"]));
		}
		else
		{
			NIT = "0";
		}
	}

	public void DevolverUltimoNITFactElec()
	{
		DataTable dataTable = BD.ConsultaVer("Nit", "FactElectConfiguracion", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID), "FactElectConfiguracionID");
		if (dataTable.Rows.Count > 0)
		{
			NIT = Conversions.ToString(checked(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[dataTable.Rows.Count - 1]["Nit"])) ? ((object)0) : dataTable.Rows[dataTable.Rows.Count - 1]["Nit"]));
		}
		else
		{
			NIT = "0";
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("CodigosFacturas.CodigoID,CodigosFacturas.FechaDesde,CodigosFacturas.FechaHasta,CodigosFacturas.Llave,NroFactura,Autorizacion,Activo", "CodigosFacturas", "ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select CodigosFacturas.CodigoID,CodigosFacturas.FechaDesde,CodigosFacturas.FechaHasta,CodigosFacturas.Llave,NroFactura,Autorizacion,Activo", "CodigosFacturas where ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " ) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int ModificarNroFactura()
	{
		int result;
		try
		{
			BD.ConsultaModificar("CodigosFacturas", "NroFactura=" + Conversions.ToString(NroFactura) + ",NroFactura2=" + Conversions.ToString(NroFactura), "CodigoID=" + CodigoID);
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

	public int obtenerSgteNroFactura()
	{
		int result;
		try
		{
			int num = 0;
			if (configuration.gMODO_ACCESS == 1)
			{
				DataTable dataTable = BD.ConsultaVer("select NroFactura from CodigosFacturas where CodigoID=" + CodigoID);
				if (dataTable.Rows.Count > 0)
				{
					num = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0), 1));
					BD.ConsultaModificar("CodigosFacturas", "NroFactura=" + Conversions.ToString(num) + ",NroFactura2=" + Conversions.ToString(num), "CodigoID=" + CodigoID);
					goto IL_0124;
				}
				result = 0;
			}
			else
			{
				DataTable dataTable2 = BD.ConsultaVer("update CodigosFacturas set NroFactura= NroFactura + 1   OUTPUT INSERTED.NroFactura  where CodigoID=" + CodigoID);
				if (dataTable2.Rows.Count > 0)
				{
					num = Conversions.ToInteger(dataTable2.Rows[0][0]);
					BD.ConsultaModificar("CodigosFacturas", "NroFactura2=" + Conversions.ToString(num), "CodigoID=" + CodigoID);
					goto IL_0124;
				}
				result = 0;
			}
			goto end_IL_0000;
			IL_0124:
			result = num;
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

	public int ModificarNroFactura2()
	{
		int result;
		try
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				BD.ConsultaModificar("CodigosFacturas", "CantFactura2= iif( ISNULL(CantFactura2) , 0 , CantFactura2) + 1, NroFactura2 =" + Conversions.ToString(NroFactura), "CodigoID=" + CodigoID);
			}
			else
			{
				BD.ConsultaModificar("CodigosFacturas", "CantFactura2= case when CantFactura2 is null then 0 else CantFactura2  end + 1, NroFactura2 =" + Conversions.ToString(NroFactura), "CodigoID=" + CodigoID);
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

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("CodigosFacturas", "FechaDesde=" + VariableGeneral.ArmarFecha(FechaDesde) + ", FechaHasta=" + VariableGeneral.ArmarFecha(FechaHasta) + ", NroFactura=" + Conversions.ToString(NroFactura) + ", NroFactura2=" + Conversions.ToString(NroFactura) + ", autorizacion=" + Conversions.ToString(autorizacion) + ", Activo=" + VariableGeneral.armarBolean(Activo) + ", Llave='" + Llave.Trim() + "', NIT='" + NIT + "'", "CodigoID=" + CodigoID);
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
				CodigoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(CodigoID)", "CodigosFacturas").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(Conversions.ToString(CodigoID) + "," + VariableGeneral.ArmarFecha(FechaDesde) + ",", VariableGeneral.ArmarFecha(FechaHasta), ",'", Llave.Trim(), "',", Conversions.ToString(NroFactura), ",", Conversions.ToString(NroFactura), ",'", Conversions.ToString(autorizacion), "','", NIT, "',", Conversions.ToString(VariableGeneral.gConfiguracionID), ",", VariableGeneral.armarBolean(Activo)), "CodigosFacturas(CodigoID, FechaDesde, FechaHasta, Llave, NroFactura,NroFactura2, Autorizacion, NIT,ConfiguracionID,Activo)");
				CodigoID = Conversions.ToInteger(BD.ConsultaVer("max(CodigoID)", "CodigosFacturas").Rows[0][0]);
				result = CodigoID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(VariableGeneral.ArmarFecha(FechaDesde) + ",", VariableGeneral.ArmarFecha(FechaHasta), ",'", Llave.Trim(), "',", Conversions.ToString(NroFactura), ",", Conversions.ToString(NroFactura), ",'", Conversions.ToString(autorizacion), "','", NIT, "',", Conversions.ToString(VariableGeneral.gConfiguracionID), ",", VariableGeneral.armarBolean(Activo)), "CodigosFacturas( FechaDesde, FechaHasta, Llave, NroFactura,NroFactura2, Autorizacion, NIT,ConfiguracionID,Activo)", ref CodigoID);
				result = CodigoID;
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
			if (BD.ConsultaEliminar("CodigosFacturas", "CodigoID = " + CodigoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Codigo, se encuentra en uso");
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
