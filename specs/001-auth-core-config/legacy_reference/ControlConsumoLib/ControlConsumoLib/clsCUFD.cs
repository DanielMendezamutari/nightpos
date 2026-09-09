using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCUFD
{
	public int FactElectCUFDID;

	public string codigoCUFD;

	public string codigoControl;

	public string codigoPuntoVenta;

	public DateTime FechaHasta;

	public DateTime FechaDesde;

	public clsCUFD()
	{
		FactElectCUFDID = 0;
		codigoCUFD = "";
		codigoControl = "";
		codigoPuntoVenta = Conversions.ToString(0);
		FechaHasta = DateAndTime.Now;
		FechaDesde = DateAndTime.Now;
	}

	public void guardarCUFD(string CUFD, string codigoControl, DateTime fechaVigente, int codigoPuntoVenta, int configuracion)
	{
		BD.ConsultaInsertar3("'" + CUFD + "','" + codigoControl + "'," + VariableGeneral.ArmarFecha(DateAndTime.Now) + "," + VariableGeneral.ArmarFecha(fechaVigente) + "," + Conversions.ToString(configuracion) + "," + Conversions.ToString(codigoPuntoVenta), "FactElectCUFD(codigoCUFD,codigoControl,FechaDesde,FechaHasta,ConfiguracionID,codigoPuntoVenta)", ref FactElectCUFDID);
	}

	public bool obtenerCUFDvigente(int codigoPuntoVenta, DateTime fecha)
	{
		DataTable dataTable = BD.ConsultaVer("codigoCUFD,codigoControl, FactElectCUFDID", "FactElectCUFD", "(" + VariableGeneral.ArmarFecha(fecha) + " between FechaDesde and FechaHasta ) and ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " and codigoPuntoVenta=" + Conversions.ToString(codigoPuntoVenta), "FactElectCUFD.FactElectCUFDID desc");
		if (dataTable.Rows.Count == 1)
		{
			codigoCUFD = Conversions.ToString(dataTable.Rows[0][0]);
			codigoControl = Conversions.ToString(dataTable.Rows[0][1]);
			FactElectCUFDID = Conversions.ToInteger(dataTable.Rows[0][2]);
			return true;
		}
		if (dataTable.Rows.Count > 1)
		{
			codigoCUFD = Conversions.ToString(dataTable.Rows[0][0]);
			codigoControl = Conversions.ToString(dataTable.Rows[0][1]);
			FactElectCUFDID = Conversions.ToInteger(dataTable.Rows[0][2]);
			return true;
		}
		return false;
	}

	public bool obtenerUltimoCUFD(int codigoPuntoVenta)
	{
		DataTable dataTable = BD.ConsultaVer("codigoCUFD,codigoControl,FechaHasta,FactElectCUFDID,FechaDesde", "FactElectCUFD", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " and codigoPuntoVenta=" + Conversions.ToString(codigoPuntoVenta), "FechaHasta desc");
		if (dataTable.Rows.Count > 0)
		{
			codigoCUFD = Conversions.ToString(dataTable.Rows[0]["codigoCUFD"]);
			codigoControl = Conversions.ToString(dataTable.Rows[0]["codigoControl"]);
			FechaHasta = Conversions.ToDate(dataTable.Rows[0]["FechaHasta"]);
			FactElectCUFDID = Conversions.ToInteger(dataTable.Rows[0]["FactElectCUFDID"]);
			FechaDesde = Conversions.ToDate(dataTable.Rows[0]["FechaDesde"]);
			return true;
		}
		return false;
	}

	public bool obtenerFechaFinCUFD(ref DateTime FechaHasta)
	{
		DataTable dataTable = BD.ConsultaVer("FechaHasta", "FactElectCUFD", "FactElectCUFDID=" + Conversions.ToString(FactElectCUFDID));
		if (dataTable.Rows.Count > 0)
		{
			FechaHasta = Conversions.ToDate(dataTable.Rows[0][0]);
			return true;
		}
		return false;
	}

	public void modificarFechaHastaCUFD(string cufd, DateTime fecha)
	{
		BD.ConsultaModificar("FactElectCUFD", "FechaHasta=" + VariableGeneral.ArmarFecha(fecha) + ",flagSync=NULL", "codigoCUFD ='" + cufd + "' and FechaHasta > " + VariableGeneral.ArmarFecha(fecha) + " ");
	}

	public void deleteCUFD1(string cufd)
	{
		BD.ConsultaEliminar("FactElectCUFD", "codigoCUFD ='" + cufd + "'");
	}

	public void deleteCUFDall()
	{
		BD.ConsultaEliminar("FactElectCUFD", "1 =1");
	}

	public void dshabilitarCUFDvigente()
	{
		BD.ConsultaModificar("FactElectCUFD", "FechaHasta=FechaDesde ,flagSync=NULL", VariableGeneral.ArmarFecha(DateAndTime.Now) + " between FechaDesde and FechaHasta ");
	}
}
