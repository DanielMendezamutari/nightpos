using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsAnticipos
{
	private int AnticipoID;

	private DateTime Fecha;

	private double Monto;

	private double MontoRestante;

	private int ClienteID;

	private int CuentaID;

	private string PC;

	public int _AnticipoID
	{
		get
		{
			return AnticipoID;
		}
		set
		{
			AnticipoID = value;
		}
	}

	public double _Monto
	{
		get
		{
			return Monto;
		}
		set
		{
			Monto = value;
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

	public double _MontoRestante
	{
		get
		{
			return MontoRestante;
		}
		set
		{
			MontoRestante = value;
		}
	}

	public int _ClienteID
	{
		get
		{
			return ClienteID;
		}
		set
		{
			ClienteID = value;
		}
	}

	public int _CuentaID
	{
		get
		{
			return CuentaID;
		}
		set
		{
			CuentaID = value;
		}
	}

	public clsAnticipos()
	{
		Monto = 0.0;
		Fecha = DateAndTime.Today;
		AnticipoID = 0;
		MontoRestante = 0.0;
		ClienteID = 0;
		CuentaID = 0;
		PC = "";
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Anticipos", " AnticipoID=" + AnticipoID);
		if (dataTable.Rows.Count > 0)
		{
			AnticipoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AnticipoID"])) ? ((object)0) : dataTable.Rows[0]["AnticipoID"]);
			Fecha = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Fecha"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["Fecha"]);
			Monto = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Monto"])) ? ((object)0) : dataTable.Rows[0]["Monto"]);
			MontoRestante = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoRestante"])) ? ((object)0) : dataTable.Rows[0]["MontoRestante"]);
			ClienteID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ClienteID"])) ? ((object)0) : dataTable.Rows[0]["ClienteID"]);
			CuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CuentaID"])) ? ((object)0) : dataTable.Rows[0]["CuentaID"]);
		}
	}

	public double devolverxFecha(DateTime fechaIni, DateTime fechafin)
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Monto)", "Anticipos", "Fecha between  " + VariableGeneral.ArmarFecha(fechaIni) + " and " + VariableGeneral.ArmarFecha(fechafin)).Rows[0][0]), 0));
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Anticipos.AnticipoID,Anticipos.Fecha,Anticipos.Monto,Anticipos.MontoRestante,Anticipos.ClienteID", "Anticipos");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (Anticipos.AnticipoID,Anticipos.Fecha,Anticipos.Monto,Anticipos.MontoRestante,Anticipos.ClienteID", "Anticipos) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Anticipos", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",Monto=" + Conversion.Str(Monto) + ",MontoRestante=" + Conversion.Str(MontoRestante) + ",ClienteID=" + Conversions.ToString(ClienteID) + ",flagSync=NULL", "AnticipoID=" + AnticipoID);
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
			BD.ConsultaInsertar3(VariableGeneral.ArmarFecha(Fecha) + "," + Conversion.Str(Monto) + "," + Conversion.Str(MontoRestante) + "," + Conversions.ToString(ClienteID) + ",'" + MyProject.Computer.Name + "'", "Anticipos(Fecha,Monto,MontoRestante,ClienteID,PC)", ref AnticipoID);
			AnticipoID = Conversions.ToInteger(BD.ConsultaVer("max(AnticipoID)", "Anticipos").Rows[0][0]);
			result = AnticipoID;
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

	public int Insertar1()
	{
		int result;
		try
		{
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				AnticipoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(AnticipoID)", "Anticipos").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar3(Conversions.ToString(AnticipoID) + "," + VariableGeneral.ArmarFecha(Fecha) + "," + Conversion.Str(Monto) + "," + Conversion.Str(MontoRestante) + "," + Conversions.ToString(ClienteID) + "," + Conversions.ToString(CuentaID) + ",'" + MyProject.Computer.Name + "'", "Anticipos(AnticipoID,Fecha,Monto,MontoRestante,ClienteID,CuentaID,PC)", ref AnticipoID);
				result = AnticipoID;
			}
			else
			{
				BD.ConsultaInsertar3(VariableGeneral.ArmarFecha(Fecha) + "," + Conversion.Str(Monto) + "," + Conversion.Str(MontoRestante) + "," + Conversions.ToString(ClienteID) + "," + Conversions.ToString(CuentaID) + ",'" + MyProject.Computer.Name + "'", "Anticipos(Fecha,Monto,MontoRestante,ClienteID,CuentaID,PC)", ref AnticipoID);
				AnticipoID = Conversions.ToInteger(BD.ConsultaVer("max(AnticipoID)", "Anticipos").Rows[0][0]);
				result = AnticipoID;
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
			if (BD.ConsultaEliminar("Anticipos", "AnticipoID = " + AnticipoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar ClientePedido, se encuentra en uso");
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

	public DataTable DevolverPorID()
	{
		return BD.ConsultaVer("Productos.Nombre as Nombre, Anticipos.AnticipoID,Anticipos.Fecha,Anticipos.Monto,Anticipos.MontoRestante as Producto,Anticipos.ClienteID,CASE when Anticipos.Fecha =1 then 'Lunes' else CASE when Anticipos.Fecha =2 then 'Martes' else CASE when Anticipos.Fecha =3 then 'Miercoles' else CASE when Anticipos.Fecha =4 then 'Jueves' else CASE when Anticipos.Fecha =5 then 'Viernes' else CASE when Anticipos.Fecha =6 then 'Sabado' else CASE when Anticipos.Fecha =7 then 'Domingo' end end end end end end end as Dia, Clientes .Nombre", "Anticipos inner join Productos on Anticipos.MontoRestante=Productos.ID left join Clientes on Anticipos.ClienteID =Clientes.ID ", "Anticipos.ClienteID = " + Conversions.ToString(ClienteID));
	}

	public double DevolverSaldo()
	{
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("Sum(MontoRestante)", "Anticipos", "Anticipos.ClienteID = " + Conversions.ToString(ClienteID)).Rows[0][0]), 0));
	}

	public int DevolverMontoRestante(ref double monto)
	{
		DataTable dataTable = BD.ConsultaVer("top 1 MontoRestante, anticipoID", "Anticipos", "MontoRestante>0 and ClienteID = " + Conversions.ToString(ClienteID), "Fecha");
		if (dataTable.Rows.Count > 0)
		{
			monto = Conversions.ToDouble(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
			{
				VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0),
				2
			}, null, null, null));
			return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), 0));
		}
		monto = 0.0;
		return 0;
	}

	public void RedicirAnticipo(double monto)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			DataTable dataTable = BD.ConsultaVer("AnticipoID", "Anticipos", "MontoRestante>0 and ClienteID =  " + Conversions.ToString(ClienteID) + "  ", "fecha limit 1");
			BD.ConsultaModificar("Anticipos", ("MontoRestante = MontoRestante - " + Conversion.Str(monto)) ?? "", Conversions.ToString(Operators.ConcatenateObject("AnticipoID =", dataTable.Rows[0][0])));
		}
		else
		{
			BD.ConsultaModificar("Anticipos", "MontoRestante =round( MontoRestante - " + Conversion.Str(monto) + ",2)", "AnticipoID in (select top 1 AnticipoID from Anticipos where MontoRestante>0 and ClienteID = " + Conversions.ToString(ClienteID) + " order by Fecha )");
		}
	}

	public int DevolverAnticipos(double mont)
	{
		int result;
		try
		{
			BD.ConsultaModificar("Anticipos", "MontoRestante=MontoRestante + " + Conversion.Str(mont), "AnticipoID=" + AnticipoID);
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

	public void DevolverAnticipo(double monto)
	{
		BD.ConsultaModificar("Anticipos", ("MontoRestante = MontoRestante - " + Conversion.Str(monto)) ?? "", "AnticipoID in (select top 1 AnticipoID from Anticipos where MontoRestante>0 and ClienteID = " + Conversions.ToString(ClienteID) + " order by Fecha )");
	}
}
