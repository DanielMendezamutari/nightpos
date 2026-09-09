using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsAnticiposCuentas
{
	private int AnticipoCuentaID;

	private int AnticipoID;

	private int DetalleCuentaID;

	private double Monto;

	public int _AnticipoCuentaID
	{
		get
		{
			return AnticipoCuentaID;
		}
		set
		{
			AnticipoCuentaID = value;
		}
	}

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

	public clsAnticiposCuentas()
	{
		AnticipoCuentaID = 0;
		AnticipoID = 0;
		DetalleCuentaID = 0;
		Monto = 0.0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "AnticiposCuentas", " AnticipoCuentaID=" + AnticipoCuentaID);
		if (dataTable.Rows.Count > 0)
		{
			AnticipoCuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AnticipoCuentaID"])) ? ((object)0) : dataTable.Rows[0]["AnticipoCuentaID"]);
			AnticipoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AnticipoID"])) ? "" : dataTable.Rows[0]["AnticipoID"]);
			DetalleCuentaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DetalleCuentaID"])) ? "" : dataTable.Rows[0]["DetalleCuentaID"]);
			Monto = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Monto"])) ? "" : dataTable.Rows[0]["Monto"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("AnticiposCuentas.AnticipoCuentaID,AnticiposCuentas.AnticipoID,AnticiposCuentas.DetalleCuentaID, Monto", "AnticiposCuentas");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (AnticiposCuentas.AnticipoCuentaID,AnticiposCuentas.AnticipoID,AnticiposCuentas.DetalleCuentaID,Monto", "AnticiposCuentas) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("AnticiposCuentas", "AnticipoID='" + Conversions.ToString(AnticipoID) + "',DetalleCuentaID='" + Conversions.ToString(DetalleCuentaID) + "'Monto=" + Conversion.Str(Monto) + ",flagSync=NULL", "AnticipoCuentaID=" + AnticipoCuentaID);
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
				AnticipoCuentaID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(AnticipoCuentaID)", "AnticiposCuentas").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar3(Conversions.ToString(AnticipoCuentaID) + "," + Conversions.ToString(AnticipoID) + "," + Conversions.ToString(DetalleCuentaID) + "," + Conversion.Str(Monto), "AnticiposCuentas(AnticipoCuentaID,AnticipoID,DetalleCuentaID, Monto)", ref AnticipoCuentaID);
				result = AnticipoID;
			}
			else
			{
				BD.ConsultaInsertar3(Conversions.ToString(AnticipoID) + "," + Conversions.ToString(DetalleCuentaID) + "," + Conversion.Str(Monto), "AnticiposCuentas(AnticipoID,DetalleCuentaID, Monto)", ref AnticipoCuentaID);
				result = AnticipoCuentaID;
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
			if (BD.ConsultaEliminar("AnticiposCuentas", "AnticipoCuentaID = " + AnticipoCuentaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar AnticiposCuentas, se encuentra en uso");
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
		return BD.ConsultaVer("Productos.Nombre as Nombre, AnticiposCuentas.AnticipoCuentaID,AnticiposCuentas.AnticipoID as Producto,AnticiposCuentas.DetalleCuentaID, Monto ,Clientes .Nombre as Cliente", "AnticiposCuentas inner join Productos on AnticiposCuentas.AnticipoID=Productos.ID left join Clientes on AnticiposCuentas.DetalleCuentaID =Clientes.ID ", "AnticiposCuentas.DetalleCuentaID = " + Conversions.ToString(DetalleCuentaID));
	}

	public int EliminarAnticipoCuenta()
	{
		int result;
		try
		{
			BD.ConsultaModificar("AnticiposCuentas", "Monto='" + Conversions.ToString(0) + "',flagSync=NULL", "DetalleCuentaID=" + DetalleCuentaID);
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

	public DataTable DevolverDetalleCuentaID()
	{
		return BD.ConsultaVer("AnticiposCuentas.AnticipoID,AnticiposCuentas.DetalleCuentaID, Monto", "AnticiposCuentas", "DetalleCuentaID =" + DetalleCuentaID);
	}
}
