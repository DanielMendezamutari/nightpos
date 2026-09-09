using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElectConfiguracion
{
	private int FactElectConfiguracionID;

	private string TokenDelegado;

	private DateTime TokenVigencia;

	private int CodigoAmbiente;

	private int CodigoModalidad;

	private string CodigoSistema;

	private int codigoSucursal;

	private int CodigoPuntoVenta;

	private long Nit;

	private int ConfiguracionID;

	private DateTime TokenDesde;

	private int nroFactura;

	private string FirmaDir;

	private string FirmaClave;

	private bool SectorCompraVenta;

	private bool SectorCompraVentaBon;

	private bool SectorTasaCEro;

	private bool SectorICE;

	private bool SectorNotaDebito;

	public int _FactElectConfiguracionID
	{
		get
		{
			return FactElectConfiguracionID;
		}
		set
		{
			FactElectConfiguracionID = value;
		}
	}

	public string _TokenDelegado
	{
		get
		{
			return TokenDelegado;
		}
		set
		{
			TokenDelegado = value;
		}
	}

	public string _FirmaDir
	{
		get
		{
			return FirmaDir;
		}
		set
		{
			FirmaDir = value;
		}
	}

	public string _FirmaClave
	{
		get
		{
			return FirmaClave;
		}
		set
		{
			FirmaClave = value;
		}
	}

	public DateTime _TokenVigencia
	{
		get
		{
			return TokenVigencia;
		}
		set
		{
			TokenVigencia = value;
		}
	}

	public DateTime _TokenDesde
	{
		get
		{
			return TokenDesde;
		}
		set
		{
			TokenDesde = value;
		}
	}

	public int _nroFactura
	{
		get
		{
			return nroFactura;
		}
		set
		{
			nroFactura = value;
		}
	}

	public int _CodigoAmbiente
	{
		get
		{
			return CodigoAmbiente;
		}
		set
		{
			CodigoAmbiente = value;
		}
	}

	public int _CodigoModalidad
	{
		get
		{
			return CodigoModalidad;
		}
		set
		{
			CodigoModalidad = value;
		}
	}

	public string _CodigoSistema
	{
		get
		{
			return CodigoSistema;
		}
		set
		{
			CodigoSistema = value;
		}
	}

	public int _codigoSucursal
	{
		get
		{
			return codigoSucursal;
		}
		set
		{
			codigoSucursal = value;
		}
	}

	public int _CodigoPuntoVenta
	{
		get
		{
			return CodigoPuntoVenta;
		}
		set
		{
			CodigoPuntoVenta = value;
		}
	}

	public long _Nit
	{
		get
		{
			return Nit;
		}
		set
		{
			Nit = value;
		}
	}

	public int _ConfiguracionID
	{
		get
		{
			return ConfiguracionID;
		}
		set
		{
			ConfiguracionID = value;
		}
	}

	public bool _SectorCompraVenta
	{
		get
		{
			return SectorCompraVenta;
		}
		set
		{
			SectorCompraVenta = value;
		}
	}

	public bool _SectorCompraVentaBon
	{
		get
		{
			return SectorCompraVentaBon;
		}
		set
		{
			SectorCompraVentaBon = value;
		}
	}

	public bool _SectorTasaCEro
	{
		get
		{
			return SectorTasaCEro;
		}
		set
		{
			SectorTasaCEro = value;
		}
	}

	public bool _SectorICE
	{
		get
		{
			return SectorICE;
		}
		set
		{
			SectorICE = value;
		}
	}

	public bool _SectorNotaDebito
	{
		get
		{
			return SectorNotaDebito;
		}
		set
		{
			SectorNotaDebito = value;
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("FactElectConfiguracion.FactElectConfiguracionID,FactElectConfiguracion.TokenDelegado,TokenVigencia,\r\n                    FactElectConfiguracion.CodigoAmbiente,FactElectConfiguracion.CodigoModalidad,FactElectConfiguracion.CodigoSistema,\r\n                    FactElectConfiguracion.codigoSucursal,FactElectConfiguracion.CodigoPuntoVenta,Nit", "FactElectConfiguracion");
	}

	public bool devolverDatosFirma(ref string file, ref string clave)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("*", "FactElectConfiguracion", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			FactElectConfiguracionID = Conversions.ToInteger(dataTable.Rows[0]["FactElectConfiguracionID"]);
			file = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FirmaDir"]), ""));
			clave = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FirmaClave"]), ""));
			return true;
		}
		FactElectConfiguracionID = 0;
		FirmaDir = "";
		FirmaClave = "";
		Interaction.MsgBox("No habia configuracion");
		return false;
	}

	public void DevolverDatos()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("*", "FactElectConfiguracion", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			FactElectConfiguracionID = Conversions.ToInteger(dataTable.Rows[0]["FactElectConfiguracionID"]);
			TokenDelegado = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TokenDelegado"]), ""));
			TokenVigencia = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TokenVigencia"]), DateAndTime.Now));
			CodigoAmbiente = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoAmbiente"]), 0));
			TokenDesde = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TokenDesde"]), DateAndTime.Now));
			nroFactura = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["nroFactura"]), 0));
			CodigoModalidad = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoModalidad"]), 0));
			CodigoSistema = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoSistema"]), ""));
			codigoSucursal = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["codigoSucursal"]), 0));
			CodigoPuntoVenta = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoPuntoVenta"]), 0));
			Nit = Conversions.ToLong(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nit"]), 0));
			ConfiguracionID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ConfiguracionID"]), 0));
			FirmaDir = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FirmaDir"]), ""));
			FirmaClave = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FirmaClave"]), ""));
			SectorCompraVenta = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["SectorCompraVenta"]), 0));
			SectorCompraVentaBon = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["SectorCompraVentaBon"]), 0));
			SectorTasaCEro = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["SectorTasaCEro"]), 0));
			SectorICE = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["SectorICE"]), 0));
			SectorNotaDebito = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["SectorNotaDebito"]), 0));
		}
		else
		{
			FactElectConfiguracionID = 0;
			TokenDelegado = "";
			TokenVigencia = DateAndTime.Now;
			TokenDesde = DateAndTime.Now;
			CodigoAmbiente = 0;
			CodigoModalidad = 0;
			CodigoSistema = "";
			codigoSucursal = 0;
			CodigoPuntoVenta = 0;
			Nit = 0L;
			ConfiguracionID = VariableGeneral.gConfiguracionID;
			nroFactura = 0;
			FirmaDir = "";
			FirmaClave = "";
			Interaction.MsgBox("No habia configuracion SIAT");
		}
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("FactElectConfiguracion", "TokenDelegado='" + TokenDelegado + "',TokenVigencia=" + VariableGeneral.ArmarFecha(TokenVigencia) + ",TokenDesde=" + VariableGeneral.ArmarFecha(TokenDesde) + ",nroFactura=" + Conversions.ToString(nroFactura) + ",CodigoAmbiente=" + Conversions.ToString(CodigoAmbiente) + ",CodigoModalidad=" + Conversions.ToString(CodigoModalidad) + ",CodigoSistema='" + CodigoSistema + "',codigoSucursal=" + Conversions.ToString(codigoSucursal) + ",CodigoPuntoVenta=" + Conversions.ToString(CodigoPuntoVenta) + ",Nit=" + Conversions.ToString(Nit) + ",ConfiguracionID=" + Conversions.ToString(ConfiguracionID) + ",SectorCompraVenta=" + VariableGeneral.armarBolean(SectorCompraVenta) + ",SectorCompraVentaBon=" + VariableGeneral.armarBolean(SectorCompraVentaBon) + ",SectorTasaCEro=" + VariableGeneral.armarBolean(SectorTasaCEro) + ",SectorICE=" + VariableGeneral.armarBolean(SectorICE) + ",SectorNotaDebito=" + VariableGeneral.armarBolean(SectorNotaDebito) + ",FirmaDir='" + FirmaDir + "',FirmaClave='" + FirmaClave + "'", "ConfiguracionID=" + VariableGeneral.gConfiguracionID);
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
			BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat("'" + TokenDelegado + "',", VariableGeneral.ArmarFecha(TokenVigencia), ","), VariableGeneral.ArmarFecha(TokenDesde), ","), Conversions.ToString(nroFactura), ","), Conversions.ToString(CodigoAmbiente), ","), Conversions.ToString(CodigoModalidad), ",'", CodigoSistema, "',"), Conversions.ToString(codigoSucursal), ","), Conversions.ToString(CodigoPuntoVenta), ","), Conversions.ToString(Nit), ","), Conversions.ToString(ConfiguracionID), ",'", FirmaDir, "','", FirmaClave, "',", VariableGeneral.armarBolean(1)), "FactElectConfiguracion(TokenDelegado,TokenVigencia,tokendesde, nroFactura,CodigoAmbiente,CodigoModalidad,CodigoSistema,codigoSucursal,CodigoPuntoVenta,Nit,ConfiguracionID, firmaDir, firmaClave,SectorCompraVenta)", ref FactElectConfiguracionID);
			result = FactElectConfiguracionID;
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
			if (BD.ConsultaEliminar("FactElectConfiguracion", "FactElectConfiguracionID = " + FactElectConfiguracionID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Almacen, se encuentra en uso");
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
