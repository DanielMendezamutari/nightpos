using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsSeguimiento
{
	private int SeguimientoID;

	private DateTime Fecha;

	private double Peso;

	private double Espalda;

	private double Pecho;

	private double Hombros;

	private double BrazoD;

	private double BrazoI;

	private double CinturaAlta;

	private double CinturaMedia;

	private double CinturaBaja;

	private double Caderas;

	private double Gluteos;

	private double PiernaD;

	private double PiernaI;

	private double GemeloD;

	private double GemeloI;

	private string Observacion;

	private int PacienteID;

	public int _SeguimientoID
	{
		get
		{
			return SeguimientoID;
		}
		set
		{
			SeguimientoID = value;
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

	public double _Peso
	{
		get
		{
			return Peso;
		}
		set
		{
			Peso = value;
		}
	}

	public double _Espalda
	{
		get
		{
			return Espalda;
		}
		set
		{
			Espalda = value;
		}
	}

	public double _Pecho
	{
		get
		{
			return Pecho;
		}
		set
		{
			Pecho = value;
		}
	}

	public double _Hombros
	{
		get
		{
			return Hombros;
		}
		set
		{
			Hombros = value;
		}
	}

	public double _BrazoD
	{
		get
		{
			return BrazoD;
		}
		set
		{
			BrazoD = value;
		}
	}

	public double _BrazoI
	{
		get
		{
			return BrazoI;
		}
		set
		{
			BrazoI = value;
		}
	}

	public double _CinturaAlta
	{
		get
		{
			return CinturaAlta;
		}
		set
		{
			CinturaAlta = value;
		}
	}

	public double _CinturaMedia
	{
		get
		{
			return CinturaMedia;
		}
		set
		{
			CinturaMedia = value;
		}
	}

	public double _CinturaBaja
	{
		get
		{
			return CinturaBaja;
		}
		set
		{
			CinturaBaja = value;
		}
	}

	public double _Caderas
	{
		get
		{
			return Caderas;
		}
		set
		{
			Caderas = value;
		}
	}

	public double _Gluteos
	{
		get
		{
			return Gluteos;
		}
		set
		{
			Gluteos = value;
		}
	}

	public double _PiernaD
	{
		get
		{
			return PiernaD;
		}
		set
		{
			PiernaD = value;
		}
	}

	public double _PiernaI
	{
		get
		{
			return PiernaI;
		}
		set
		{
			PiernaI = value;
		}
	}

	public double _GemeloD
	{
		get
		{
			return GemeloD;
		}
		set
		{
			GemeloD = value;
		}
	}

	public double _GemeloI
	{
		get
		{
			return GemeloI;
		}
		set
		{
			GemeloI = value;
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

	public int _PacienteID
	{
		get
		{
			return PacienteID;
		}
		set
		{
			PacienteID = value;
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Seguimiento.SeguimientoID,Seguimiento.Fecha,Seguimiento.Peso,Seguimiento.Espalda,Seguimiento.Pecho,Seguimiento.Hombros,Seguimiento.BrazoD,Seguimiento.BrazoI,Seguimiento.CinturaAlta,Seguimiento.CinturaMedia,Seguimiento.CinturaBaja,Seguimiento.Caderas,Seguimiento.Gluteos,Seguimiento.PiernaD,Seguimiento.PiernaI,Seguimiento.GemeloD,Seguimiento.GemeloI,Seguimiento.Observacion,PacienteID ", "Seguimiento");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select Seguimiento.SeguimientoID,Seguimiento.Fecha,Seguimiento.Peso,Seguimiento.Espalda,Seguimiento.Pecho,Seguimiento.Hombros,Seguimiento.BrazoD,Seguimiento.BrazoI,Seguimiento.CinturaAlta,Seguimiento.CinturaMedia,Seguimiento.CinturaBaja,Seguimiento.Caderas,Seguimiento.Gluteos,Seguimiento.PiernaD,Seguimiento.PiernaI,Seguimiento.GemeloD,,Seguimiento.GemeloI,Seguimiento.Observacion,PacienteID", "Seguimiento) as tab1", (field + " " + (field.Contains("as date") ? VariableGeneral.ArmarFecha(Conversions.ToDate(search)) : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Seguimiento", ("Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",Peso=" + Conversion.Str(Peso.ToString()) + ",Espalda=" + Conversion.Str(Espalda.ToString()) + ",Pecho=" + Conversion.Str(Pecho.ToString()) + ",Hombros=" + Conversion.Str(Hombros.ToString()) + ",BrazoD=" + Conversion.Str(BrazoD.ToString()) + ",BrazoI=" + Conversion.Str(BrazoI.ToString()) + ",CinturaAlta=" + Conversion.Str(CinturaAlta.ToString()) + ",CinturaMedia=" + Conversion.Str(CinturaMedia.ToString()) + ",CinturaBaja=" + Conversion.Str(CinturaBaja.ToString()) + ",Caderas=" + Conversion.Str(Caderas.ToString()) + ",Gluteos=" + Conversion.Str(Gluteos.ToString()) + ",PiernaD=" + Conversion.Str(PiernaD.ToString()) + ",PiernaI=" + Conversion.Str(PiernaI.ToString()) + ",GemeloD=" + Conversion.Str(GemeloD.ToString()) + ",GemeloI=" + Conversion.Str(GemeloI.ToString()) + ",Observacion='" + Observacion + "',PacienteID=" + Conversions.ToString(PacienteID)) ?? "", "SeguimientoID =" + SeguimientoID);
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
			BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(VariableGeneral.ArmarFecha(Fecha) + ",", Conversion.Str(Peso.ToString()), ","), Conversion.Str(Espalda.ToString()), ","), Conversion.Str(Pecho.ToString()), ","), Conversion.Str(Hombros.ToString()), ","), Conversion.Str(BrazoD.ToString()), ","), Conversion.Str(BrazoI.ToString()), ","), Conversion.Str(CinturaAlta.ToString()), ","), Conversion.Str(CinturaMedia.ToString()), ","), Conversion.Str(CinturaBaja.ToString()), ","), Conversion.Str(Caderas.ToString()), ","), Conversion.Str(Gluteos.ToString()), ","), Conversion.Str(PiernaD.ToString()), ","), Conversion.Str(PiernaI.ToString()), ","), Conversion.Str(GemeloD.ToString()), ","), Conversion.Str(GemeloI.ToString()), ",'", Observacion, "',"), Conversions.ToString(PacienteID)) ?? "", "Seguimiento(Fecha,Peso,Espalda,Pecho,Hombros,BrazoD,BrazoI,CinturaAlta,CinturaMedia,CinturaBaja, Caderas, Gluteos,PiernaD,PiernaI, GemeloD, GemeloI,Observacion,PacienteID)", ref SeguimientoID);
			result = SeguimientoID;
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
			if (BD.ConsultaEliminar("Seguimiento", "SeguimientoID = " + SeguimientoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Cuenta, se encuentra en uso");
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

	public DataTable ReturnHombros()
	{
		return BD.ConsultaVer("distinct 0, Seguimiento.Hombros", "Seguimiento", "", "Seguimiento.Hombros");
	}

	public DataTable DevolverXPacienteID()
	{
		return BD.ConsultaVer("Seguimiento.SeguimientoID,Seguimiento.Fecha,Seguimiento.Peso,Seguimiento.Espalda,Seguimiento.Pecho,Seguimiento.Hombros,Seguimiento.BrazoD,Seguimiento.BrazoI,Seguimiento.CinturaAlta,Seguimiento.CinturaMedia,Seguimiento.CinturaBaja,Seguimiento.Caderas,Seguimiento.Gluteos,Seguimiento.PiernaD,Seguimiento.PiernaI,Seguimiento.GemeloD,Seguimiento.GemeloI,Seguimiento.Observacion, PacienteID ", "Seguimiento", "PacienteID= " + Conversions.ToString(PacienteID));
	}
}
