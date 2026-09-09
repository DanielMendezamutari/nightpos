using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsPropinas
{
	private int PropinaID;

	private DateTime Fecha;

	private double MontoBs;

	private string MaquinaPropina;

	private int VisitaID;

	private int cuentaID;

	private int MeseroID;

	public int _PropinaID
	{
		get
		{
			return PropinaID;
		}
		set
		{
			PropinaID = value;
		}
	}

	public int _cuentaID
	{
		get
		{
			return cuentaID;
		}
		set
		{
			cuentaID = value;
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

	public double _MontoBs
	{
		get
		{
			return MontoBs;
		}
		set
		{
			MontoBs = value;
		}
	}

	public string _MaquinaPropina
	{
		get
		{
			return MaquinaPropina;
		}
		set
		{
			MaquinaPropina = value;
		}
	}

	public int _VisitaID
	{
		get
		{
			return VisitaID;
		}
		set
		{
			VisitaID = value;
		}
	}

	public int _MeseroID
	{
		get
		{
			return MeseroID;
		}
		set
		{
			MeseroID = value;
		}
	}

	public clsPropinas()
	{
		Fecha = DateAndTime.Today;
		MontoBs = 0.0;
		MaquinaPropina = "";
		VisitaID = 0;
		cuentaID = 0;
		MeseroID = 0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Propinas", " PropinaID=" + PropinaID);
		if (dataTable.Rows.Count > 0)
		{
			PropinaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PropinaID"])) ? ((object)0) : dataTable.Rows[0]["PropinaID"]);
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
			MontoBs = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoBs"])) ? ((object)0) : dataTable.Rows[0]["MontoBs"]);
			MaquinaPropina = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MaquinaPropina"])) ? "" : dataTable.Rows[0]["MaquinaPropina"]);
			VisitaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["VisitaID"])) ? ((object)0) : dataTable.Rows[0]["VisitaID"]);
			cuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["cuentaID"])) ? ((object)0) : dataTable.Rows[0]["cuentaID"]);
			MeseroID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MeseroID"])) ? ((object)0) : dataTable.Rows[0]["MeseroID"]);
		}
	}

	public DataTable DevolverMaquinas()
	{
		return BD.ConsultaVer("distinct Propinas.MaquinaPropina,Propinas.MaquinaPropina", "Propinas");
	}

	public int Insertar(int ResponsableID)
	{
		int result;
		try
		{
			if (MaquinaPropina.Length > 30)
			{
				MaquinaPropina = MaquinaPropina.Substring(0, 30);
			}
			BD.ConsultaEliminar("Propinas", "VisitaId=" + Conversions.ToString(VisitaID));
			result = ((BD.ConsultaInsertar3(string.Concat(string.Concat(VariableGeneral.ArmarFecha(Fecha) + ",", Conversion.Str(MontoBs), ",'", MaquinaPropina, "',"), VisitaID.ToString(), ",", Conversions.ToString(cuentaID), ",", Conversions.ToString(ResponsableID)), "Propinas(Fecha, MontoBs,  MaquinaPropina, VisitaID,CuentaID,MeseroID)", ref PropinaID) != 0) ? PropinaID : 0);
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

	public double devolverPropinasXvisitaID(int visitaID)
	{
		if (new ctlConfiguraciones().devolverFacturarPropina())
		{
			DataTable dataTable = ((configuration.gStyleBoliches1 != configuration.styleBolichesId.LaSuisse) ? BD.ConsultaVer("MontoBs", "Propinas INNER JOIN Cuentas ON Propinas.cuentaID = Cuentas.CuentaID", "VisitaID=" + visitaID + " and Cuentas.FacturaObligatoria=" + VariableGeneral.armarBolean(1)) : BD.ConsultaVer("MontoBs", "Propinas INNER JOIN Cuentas ON Propinas.cuentaID = Cuentas.CuentaID", "VisitaID=" + visitaID + " and (Cuentas.FacturaObligatoria=" + VariableGeneral.armarBolean(1) + " or Cuentas.CuentaId=" + Conversions.ToString(3) + ")"));
			if (dataTable.Rows.Count > 0)
			{
				return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
			}
			return 0.0;
		}
		return 0.0;
	}
}
