using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCUIS
{
	public int FactElectCUISID;

	public string codigoCUIS;

	public int codigoPuntoVenta;

	public clsCUIS()
	{
		codigoCUIS = "";
	}

	public void guardarCUIS(string CUIS, DateTime fechaVigente, int codigoPuntoVenta)
	{
		string data = "'" + CUIS + "'," + VariableGeneral.ArmarFecha(DateAndTime.Now) + "," + VariableGeneral.ArmarFecha(fechaVigente) + "," + Conversions.ToString(VariableGeneral.gConfiguracionID) + "," + Conversions.ToString(codigoPuntoVenta);
		int id = 0;
		BD.ConsultaInsertar3(data, "FactElectCUIS(CodigoCUIS,FechaDesde,FechaHasta,ConfiguracionID,codigoPuntoVenta)", ref id);
	}

	public bool obtenerCUISvigente(int codigoPuntoVenta)
	{
		DataTable dataTable = BD.ConsultaVer("CodigoCUIS", "FactElectCUIS", "(" + VariableGeneral.ArmarFecha(DateAndTime.Now) + " between FechaDesde and FechaHasta ) and ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " and codigoPuntoVenta=" + Conversions.ToString(codigoPuntoVenta));
		if (dataTable.Rows.Count == 1)
		{
			codigoCUIS = Conversions.ToString(dataTable.Rows[0][0]);
			return true;
		}
		if (dataTable.Rows.Count > 1)
		{
			codigoCUIS = Conversions.ToString(dataTable.Rows[checked(dataTable.Rows.Count - 1)][0]);
			return true;
		}
		return false;
	}

	public void deleteCUISDall()
	{
		BD.ConsultaEliminar("FactElectCUIS", "1 =1");
	}

	public void deshabilitarCUISDvigente()
	{
		BD.ConsultaModificar("FactElectCUIS", "FechaHasta=FechaDesde,flagSync=NULL", VariableGeneral.ArmarFecha(DateAndTime.Now) + " between FechaDesde and FechaHasta and ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}
}
