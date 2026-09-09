using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsObservaciones
{
	private int ObservacionID;

	private int? DetalleCuentaID;

	private string Observacion;

	private string NumeroSerieImei;

	public int _ObservacionID
	{
		get
		{
			return ObservacionID;
		}
		set
		{
			ObservacionID = value;
		}
	}

	public int _DetalleCuentaID
	{
		get
		{
			return DetalleCuentaID.Value;
		}
		set
		{
			DetalleCuentaID = value;
		}
	}

	public string _Observacion
	{
		get
		{
			return Observacion;
		}
		set
		{
			Observacion = value;
		}
	}

	public string _NumeroSerieImei
	{
		get
		{
			return NumeroSerieImei;
		}
		set
		{
			NumeroSerieImei = value;
		}
	}

	public string DevolverObservaciones()
	{
		int? detalleCuentaID;
		int? num = (detalleCuentaID = DetalleCuentaID);
		DataTable dataTable = BD.ConsultaVer("Observaciones.Observacion", "Observaciones", "DetalleCuentaID=" + (num.HasValue ? Conversions.ToString(detalleCuentaID.GetValueOrDefault()) : null), "ObservacionId desc");
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToString(dataTable.Rows[0][0]);
		}
		return "";
	}

	public int Insertar()
	{
		int result;
		try
		{
			if (Observacion.Length <= 0)
			{
				goto IL_0165;
			}
			int? num;
			int? detalleCuentaID;
			if (configuration.gStyleBoliches1 > configuration.styleBolichesId.Bless)
			{
				num = (detalleCuentaID = DetalleCuentaID);
				BD.ConsultaInsertar3((num.HasValue ? Conversions.ToString(detalleCuentaID.GetValueOrDefault()) : null) + ",'" + Observacion.Replace("'", "`") + "'", "Observaciones(DetalleCuentaID,Observacion)", ref ObservacionID);
				goto IL_0165;
			}
			ObservacionID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ObservacionID)", "Observaciones").Rows[0][0]), 0), 1));
			string[] obj = new string[6]
			{
				Conversions.ToString(ObservacionID),
				",",
				null,
				null,
				null,
				null
			};
			num = (detalleCuentaID = DetalleCuentaID);
			obj[2] = (num.HasValue ? Conversions.ToString(detalleCuentaID.GetValueOrDefault()) : null);
			obj[3] = ",'";
			obj[4] = Observacion.Replace("'", "`");
			obj[5] = "'";
			BD.ConsultaInsertar(string.Concat(obj), "Observaciones(ObservacionID,DetalleCuentaID,Observacion)");
			ObservacionID = Conversions.ToInteger(BD.ConsultaVer("max(ObservacionID)", "Observaciones").Rows[0][0]);
			result = ObservacionID;
			goto end_IL_0000;
			IL_0165:
			result = ObservacionID;
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

	public int Modificar()
	{
		int result;
		try
		{
			string[] obj = new string[5] { "DetalleCuentaID=", null, null, null, null };
			int? detalleCuentaID;
			int? num = (detalleCuentaID = DetalleCuentaID);
			obj[1] = (num.HasValue ? Conversions.ToString(detalleCuentaID.GetValueOrDefault()) : null);
			obj[2] = ",Observacion='";
			obj[3] = Observacion;
			obj[4] = "',flagSync=NULL";
			BD.ConsultaModificar("Observaciones", string.Concat(obj), "ObservacionID=" + ObservacionID);
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

	public int Existe()
	{
		int? detalleCuentaID;
		int? num = (detalleCuentaID = DetalleCuentaID);
		DataTable dataTable = BD.ConsultaVer("Observaciones.ObservacionID", "Observaciones", "DetalleCuentaID=" + (num.HasValue ? Conversions.ToString(detalleCuentaID.GetValueOrDefault()) : null));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		return 0;
	}

	public int EliminarXdetalleID()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Observaciones", "DetalleCuentaID = " + DetalleCuentaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Observaciones, se encuentra en uso");
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
