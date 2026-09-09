using System;
using System.Data;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsPacientes
{
	private int PacienteID;

	private string Nombre;

	private int Edad;

	private bool Sexo;

	private string Direccion;

	private DateTime FechaNacimiento;

	private int Telefono;

	private string APP;

	private string ActividadLaboral;

	private string EstadoCivil;

	private int Cant_Hijos;

	private string AlergiaMedicamentos;

	private string Cirugias;

	private string ActividadFisica;

	private string Observaciones;

	private string HEA;

	private string MC;

	private string Tratamientos;

	private double Precio;

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

	public string _Nombre
	{
		get
		{
			return Nombre;
		}
		set
		{
			Nombre = value;
		}
	}

	public int _Edad
	{
		get
		{
			return Edad;
		}
		set
		{
			Edad = value;
		}
	}

	public bool _Sexo
	{
		get
		{
			return Sexo;
		}
		set
		{
			Sexo = value;
		}
	}

	public string _Direccion
	{
		get
		{
			return Direccion;
		}
		set
		{
			Direccion = value;
		}
	}

	public DateTime _FechaNacimiento
	{
		get
		{
			return FechaNacimiento;
		}
		set
		{
			FechaNacimiento = value;
		}
	}

	public int _Telefono
	{
		get
		{
			return Telefono;
		}
		set
		{
			Telefono = value;
		}
	}

	public string _APP
	{
		get
		{
			return APP;
		}
		set
		{
			APP = value;
		}
	}

	public string _ActividadLaboral
	{
		get
		{
			return ActividadLaboral;
		}
		set
		{
			ActividadLaboral = value;
		}
	}

	public string _EstadoCivil
	{
		get
		{
			return EstadoCivil;
		}
		set
		{
			EstadoCivil = value;
		}
	}

	public int _Cant_Hijos
	{
		get
		{
			return Cant_Hijos;
		}
		set
		{
			Cant_Hijos = value;
		}
	}

	public string _AlergiaMedicamentos
	{
		get
		{
			return AlergiaMedicamentos;
		}
		set
		{
			AlergiaMedicamentos = value;
		}
	}

	public string _Cirugias
	{
		get
		{
			return Cirugias;
		}
		set
		{
			Cirugias = value;
		}
	}

	public string _ActividadFisica
	{
		get
		{
			return ActividadFisica;
		}
		set
		{
			ActividadFisica = value;
		}
	}

	public string _Observaciones
	{
		get
		{
			return Observaciones;
		}
		set
		{
			Observaciones = value;
		}
	}

	public string _HEA
	{
		get
		{
			return HEA;
		}
		set
		{
			HEA = value;
		}
	}

	public string _MC
	{
		get
		{
			return MC;
		}
		set
		{
			MC = value;
		}
	}

	public string _Tratamientos
	{
		get
		{
			return Tratamientos;
		}
		set
		{
			Tratamientos = value;
		}
	}

	public double _Precio
	{
		get
		{
			return Precio;
		}
		set
		{
			Precio = value;
		}
	}

	public DataTable Devolver()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Pacientes.PacienteID,Pacientes.Nombre,Pacientes.Edad,iif(Pacientes.Sexo=0,'Masculino','Femenino') as sexo ,Pacientes.Direccion,Pacientes.FechaNacimiento,Pacientes.Telefono,Pacientes.APP,Pacientes.ActividadLaboral,Pacientes.EstadoCivil,Pacientes.Cant_Hijos,Pacientes.AlergiaMedicamentos,Pacientes.Cirugias,Pacientes.ActividadFisica,Pacientes.Observaciones,Pacientes.HEA,Pacientes.MC,Pacientes.Tratamientos,Pacientes.Precio", "Pacientes");
		}
		return BD.ConsultaVer("Pacientes.PacienteID,Pacientes.Nombre,Pacientes.Edad,iif(Pacientes.Sexo=0,'Masculino','Femenino') as sexo ,Pacientes.Direccion,Pacientes.FechaNacimiento,Pacientes.Telefono,Pacientes.APP,Pacientes.ActividadLaboral,Pacientes.EstadoCivil,Pacientes.Cant_Hijos,Pacientes.AlergiaMedicamentos,Pacientes.Cirugias,Pacientes.ActividadFisica,Pacientes.Observaciones,Pacientes.HEA,Pacientes.MC,Pacientes.Tratamientos,Pacientes.Precio", "Pacientes");
	}

	public DataTable DevolverSexo()
	{
		return BD.ConsultaVer("Pacientes.PacienteID,Pacientes.Nombre", "Pacientes", "Pacientes.Sexo= " + VariableGeneral.armarBolean(1));
	}

	public DataTable DevolverTodosPacientes()
	{
		return BD.ConsultaVer("Pacientes.PacienteID,Pacientes.Nombre", "Pacientes");
	}

	public DataTable Devolver(string search, string field)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("* from (select Pacientes.PacienteID,Pacientes.Nombre,Pacientes.Edad,iif(Pacientes.Sexo=0,'Masculino','Femenino') as sexo ,Pacientes.Direccion,Pacientes.FechaNacimiento,Pacientes.Telefono,Pacientes.APP,Pacientes.ActividadLaboral,Pacientes.EstadoCivil,Pacientes.Cant_Hijos,Pacientes.AlergiaMedicamentos,Pacientes.Cirugias,Pacientes.ActividadFisica,Pacientes.Observaciones,Pacientes.HEA,Pacientes.MC,Pacientes.Tratamientos,Pacientes.Precio", "Pacientes) as tab1", (field + " " + (field.Contains("as date") ? VariableGeneral.ArmarFecha(Conversions.ToDate(search)) : search)) ?? "");
		}
		return BD.ConsultaVer("* from (select Pacientes.PacienteID,Pacientes.Nombre,Pacientes.Edad,iif(Pacientes.Sexo=0,'Masculino','Femenino') as sexo ,Pacientes.Direccion,Pacientes.FechaNacimiento,Pacientes.Telefono,Pacientes.APP,Pacientes.ActividadLaboral,Pacientes.EstadoCivil,Pacientes.Cant_Hijos,Pacientes.AlergiaMedicamentos,Pacientes.Cirugias,Pacientes.ActividadFisica,Pacientes.Observaciones,Pacientes.HEA,Pacientes.MC,Pacientes.Tratamientos,Pacientes.Precio", "Pacientes) as tab1", (field + " " + (field.Contains("as date") ? VariableGeneral.ArmarFecha(Conversions.ToDate(search)) : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Pacientes", ("Nombre='" + Nombre + "',Edad=" + Edad + ",Sexo=" + VariableGeneral.armarBolean(Sexo) + ",Direccion='" + Direccion + "',FechaNacimiento=" + VariableGeneral.ArmarFecha(FechaNacimiento) + ",Telefono=" + Telefono + ",APP='" + APP + "',ActividadLaboral='" + ActividadLaboral + "',EstadoCivil='" + EstadoCivil + "',Cant_Hijos=" + Cant_Hijos + ",AlergiaMedicamentos='" + AlergiaMedicamentos + "',Cirugias='" + Cirugias + "',ActividadFisica='" + ActividadFisica + "',Observaciones='" + Observaciones + "',HEA='" + HEA + "',MC='" + MC + "',Tratamientos='" + Tratamientos + "',Precio=" + Conversion.Str(Precio.ToString())) ?? "", "PacienteID =" + PacienteID);
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
			BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat("'" + Nombre + "',", Edad.ToString(), ","), VariableGeneral.armarBolean(Sexo), ",'", Direccion, "',"), VariableGeneral.ArmarFecha(FechaNacimiento), ","), Telefono.ToString(), ",'", APP, "','", ActividadLaboral, "','", EstadoCivil, "',"), Cant_Hijos.ToString(), ",'", AlergiaMedicamentos, "','", Cirugias, "','", ActividadFisica, "','", Observaciones, "','", HEA, "','", MC, "','", Tratamientos, "',"), Conversion.Str(Precio.ToString())) ?? "", "Pacientes(Nombre,Edad,Sexo,Direccion,FechaNacimiento,Telefono,APP,ActividadLaboral,EstadoCivil,Cant_Hijos,AlergiaMedicamentos,Cirugias,ActividadFisica,Observaciones, HEA, MC,Tratamientos,Precio)", ref PacienteID);
			result = PacienteID;
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
			if (BD.ConsultaEliminar("Pacientes", "PacienteID = " + PacienteID) == 0)
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

	public DataTable ReturnEstadoCivil()
	{
		return BD.ConsultaVer("distinct 0, Pacientes.EstadoCivil", "Pacientes", "", "Pacientes.EstadoCivil");
	}
}
