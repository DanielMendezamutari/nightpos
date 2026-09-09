using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElecConfig
{
	public enum FactAmbiente
	{
		Produccion = 1,
		Pruebas
	}

	public enum FactModalidades
	{
		Eletronica = 1,
		Computarizada
	}

	public enum FactSectores
	{
		CompraVenta = 1,
		CompraVentaBon = 35,
		TasaCero = 8,
		ICE = 14,
		CreditoDebito = 24
	}

	public string TokenDelegado;

	public DateTime TokenVigencia;

	public FactAmbiente CodigoAmbiente;

	public FactModalidades CodigoModalidad;

	public string CodigoSistema;

	public int codigoSucursal;

	public int CodigoPuntoVenta;

	public long NIT;

	public int nroFactura;

	public int nroFactura2;

	public clsFactElecConfig()
	{
		CodigoPuntoVenta = 0;
		nroFactura2 = 0;
		nroFactura = 0;
	}

	public bool hayTokenActivo()
	{
		bool result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("*", "FactElectConfiguracion", "configuracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
			if (dataTable.Rows.Count > 0)
			{
				TokenVigencia = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TokenVigencia"]), DateAndTime.Now.AddDays(-365.0)));
				result = DateTime.Compare(TokenVigencia, DateAndTime.Today) >= 0;
			}
			else
			{
				result = false;
			}
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

	public bool devolverDatosSiTokenActivo1()
	{
		bool result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("*", "FactElectConfiguracion", "configuracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
			if (dataTable.Rows.Count > 0)
			{
				TokenDelegado = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TokenDelegado"]), ""));
				TokenVigencia = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TokenVigencia"]), DateAndTime.Now.AddDays(-365.0)));
				CodigoAmbiente = (FactAmbiente)Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoAmbiente"]), 0));
				CodigoModalidad = (FactModalidades)Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoModalidad"]), 0));
				CodigoSistema = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoSistema"]), 0));
				codigoSucursal = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["codigoSucursal"]), 0));
				CodigoPuntoVenta = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoPuntoVenta"]), 0));
				NIT = Conversions.ToLong(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"]), 0));
				nroFactura = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["nroFactura"]), 0));
				nroFactura2 = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["nroFactura2"]), 0));
				result = DateTime.Compare(TokenVigencia, DateAndTime.Today) >= 0;
			}
			else
			{
				result = false;
			}
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

	public bool devolverSiTieneNotaDebitoCredito()
	{
		bool result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("SectorNotaDebito", "FactElectConfiguracion", "configuracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
			result = dataTable.Rows.Count > 0 && Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["SectorNotaDebito"]), false));
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

	public int ModificarNroFactura(int NroFactura)
	{
		int result;
		try
		{
			if (NroFactura < 0)
			{
				BD.ConsultaModificar("FactElectConfiguracion", "NroFactura=" + Conversions.ToString(0) + ",NroFactura2=" + Conversions.ToString(0), "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
			}
			else
			{
				BD.ConsultaModificar("FactElectConfiguracion", "NroFactura=" + Conversions.ToString(NroFactura) + ",NroFactura2=" + Conversions.ToString(NroFactura), "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
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

	public int obtenerSgteNroFactura()
	{
		int result;
		try
		{
			int num = 0;
			if (configuration.gMODO_ACCESS == 1)
			{
				num = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select NroFactura from FactElectConfiguracion where  ConfiguracionID=" + VariableGeneral.gConfiguracionID).Rows[0][0]), 0), 1));
				BD.ConsultaModificar("FactElectConfiguracion", "NroFactura=" + Conversions.ToString(num) + ",NroFactura2=" + Conversions.ToString(num), "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
			}
			else
			{
				num = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("update FactElectConfiguracion set NroFactura= NroFactura + 1   OUTPUT INSERTED.NroFactura  where ConfiguracionID=" + VariableGeneral.gConfiguracionID).Rows[0][0]), 0));
				if (num == 0)
				{
					num = 1;
					BD.ConsultaModificar("FactElectConfiguracion", "NroFactura=" + Conversions.ToString(num), "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
				}
				BD.ConsultaModificar("FactElectConfiguracion", "NroFactura2=" + Conversions.ToString(num), "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
			}
			result = num;
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

	public int obtenerSgteNroNotaDebito()
	{
		int result;
		try
		{
			int num = 0;
			if (configuration.gMODO_ACCESS == 1)
			{
				num = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select NroDebitoCredito from FactElectConfiguracion where  ConfiguracionID=" + VariableGeneral.gConfiguracionID).Rows[0][0]), 0), 1));
				BD.ConsultaModificar("FactElectConfiguracion", "NroDebitoCredito=" + Conversions.ToString(num), "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
			}
			else
			{
				num = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("update FactElectConfiguracion set NroDebitoCredito= NroDebitoCredito + 1   OUTPUT INSERTED.NroDebitoCredito  where ConfiguracionID=" + VariableGeneral.gConfiguracionID).Rows[0][0]), 0));
				if (num == 0)
				{
					num = 1;
					BD.ConsultaModificar("FactElectConfiguracion", "NroDebitoCredito=" + Conversions.ToString(num), "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
				}
			}
			result = num;
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

	public int DevolverNroFactura2()
	{
		return nroFactura2;
	}

	public int ModificarNroFactura2(int NroFact)
	{
		int result;
		try
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				BD.ConsultaModificar("FactElectConfiguracion", "CantFactura2= iif( ISNULL(CantFactura2) , 0 , CantFactura2) + 1, NroFactura2 =" + Conversions.ToString(NroFact), "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
			}
			else
			{
				BD.ConsultaModificar("FactElectConfiguracion", "CantFactura2= case when CantFactura2 is null then 0 else CantFactura2  end + 1, NroFactura2 =" + Conversions.ToString(NroFact), "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
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
}
