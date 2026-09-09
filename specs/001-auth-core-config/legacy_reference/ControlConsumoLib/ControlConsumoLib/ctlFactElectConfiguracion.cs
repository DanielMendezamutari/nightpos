using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlFactElectConfiguracion
{
	private readonly clsFactElectConfiguracion clsFacConf;

	public ctlFactElectConfiguracion()
	{
		clsFacConf = new clsFactElectConfiguracion();
	}

	public int GetFactElectConfiguracionID()
	{
		return clsFacConf._FactElectConfiguracionID;
	}

	public void SetFactElectConfiguracionID(int ID)
	{
		clsFacConf._FactElectConfiguracionID = ID;
	}

	public DataTable devolverFactElectConfiguracion()
	{
		return clsFacConf.Devolver();
	}

	public void Devolver(ref int FactElectConfiguracionID, ref string TokenDelegado, ref DateTime Tokendesde, ref DateTime TokenVigencia, ref int CodigoAmbiente, ref int CodigoModalidad, ref string CodigoSistema, ref int codigoSucursal, ref int CodigoPuntoVenta, ref long Nit, ref int ConfiguracionID, ref int nrofactura, ref string FirmaDir, ref string firmaClave, ref bool chbSectorCompraVenta, ref bool chbSectorBon, ref bool chbSectorTasaCEro, ref bool chbSectorICE, ref bool chbSectorNoteDebito)
	{
		clsFacConf._FactElectConfiguracionID = FactElectConfiguracionID;
		clsFacConf.DevolverDatos();
		FactElectConfiguracionID = clsFacConf._FactElectConfiguracionID;
		TokenDelegado = clsFacConf._TokenDelegado;
		TokenVigencia = clsFacConf._TokenVigencia;
		CodigoAmbiente = clsFacConf._CodigoAmbiente;
		CodigoModalidad = clsFacConf._CodigoModalidad;
		CodigoSistema = clsFacConf._CodigoSistema;
		codigoSucursal = clsFacConf._codigoSucursal;
		CodigoPuntoVenta = clsFacConf._CodigoPuntoVenta;
		Nit = clsFacConf._Nit;
		ConfiguracionID = clsFacConf._ConfiguracionID;
		Tokendesde = clsFacConf._TokenDesde;
		nrofactura = clsFacConf._nroFactura;
		FirmaDir = clsFacConf._FirmaDir;
		firmaClave = clsFacConf._FirmaClave;
		chbSectorCompraVenta = clsFacConf._SectorCompraVenta;
		chbSectorBon = clsFacConf._SectorCompraVentaBon;
		chbSectorTasaCEro = clsFacConf._SectorTasaCEro;
		chbSectorICE = clsFacConf._SectorICE;
		chbSectorNoteDebito = clsFacConf._SectorNotaDebito;
	}

	public void DevolverNIT(ref long Nit)
	{
		clsFacConf._FactElectConfiguracionID = VariableGeneral.gConfiguracionID;
		clsFacConf.DevolverDatos();
		Nit = clsFacConf._Nit;
	}

	public void Guardar(string TokenDelegado, DateTime TokenDesde, DateTime TokenVigencia, int CodigoAmbiente, int CodigoModalidad, string CodigoSistema, int codigoSucursal, int CodigoPuntoVenta, long Nit, int ConfiguracionID, int NroFactura, string firmaDir, string firmaClave, bool chbSectorCompraVenta, bool chbSectorBon, bool chbSectorTasaCEro, bool chbSectorICE, bool chbSectorNoteDebito)
	{
		clsFacConf._TokenDelegado = TokenDelegado;
		clsFacConf._TokenVigencia = TokenVigencia;
		clsFacConf._TokenDesde = TokenDesde;
		clsFacConf._nroFactura = NroFactura;
		clsFacConf._CodigoAmbiente = CodigoAmbiente;
		clsFacConf._CodigoModalidad = CodigoModalidad;
		clsFacConf._CodigoSistema = CodigoSistema;
		clsFacConf._codigoSucursal = codigoSucursal;
		clsFacConf._CodigoPuntoVenta = CodigoPuntoVenta;
		clsFacConf._Nit = Nit;
		clsFacConf._ConfiguracionID = ConfiguracionID;
		clsFacConf._nroFactura = NroFactura;
		clsFacConf._FirmaClave = firmaClave;
		clsFacConf._FirmaDir = firmaDir;
		clsFacConf._SectorCompraVenta = chbSectorCompraVenta;
		clsFacConf._SectorCompraVentaBon = chbSectorBon;
		clsFacConf._SectorTasaCEro = chbSectorTasaCEro;
		clsFacConf._SectorICE = chbSectorICE;
		clsFacConf._SectorNotaDebito = chbSectorNoteDebito;
		clsFacConf.Modificar();
	}
}
