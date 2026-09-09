using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlPacientes
{
	private readonly clsPacientes clsPac;

	public ctlPacientes()
	{
		clsPac = new clsPacientes();
	}

	public int GetPacienteID()
	{
		return clsPac._PacienteID;
	}

	public void SetPacienteID(int ID)
	{
		clsPac._PacienteID = ID;
	}

	public DataTable devolverPacientes(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsPac.Devolver();
		}
		return clsPac.Devolver(search, field);
	}

	public DataTable devolverPacientes()
	{
		return clsPac.Devolver();
	}

	public DataTable DevolverTodosPacientes()
	{
		return BD.ConsultaVer("Pacientes.PacienteID,Paciente.Nombre", "Pacientes");
	}

	public void GuardarPaciente(string Nombre, int Edad, bool Sexo, string Direccion, DateTime FechaNacimiento, int Telefono, string APP, string ActividadLaboral, string EstadoCivil, int Cant_Hijos, string AlergiaMedicamentos, string Cirugias, string ActividadFisica, string Observaciones, string HEA, string MC, string Tratamientos, double Precio)
	{
		clsPac._Nombre = Nombre;
		clsPac._Edad = Edad;
		clsPac._Sexo = Sexo;
		clsPac._Direccion = Direccion;
		clsPac._FechaNacimiento = FechaNacimiento;
		clsPac._Telefono = Telefono;
		clsPac._APP = APP;
		clsPac._ActividadLaboral = ActividadLaboral;
		clsPac._EstadoCivil = EstadoCivil;
		clsPac._Cant_Hijos = Cant_Hijos;
		clsPac._AlergiaMedicamentos = AlergiaMedicamentos;
		clsPac._Cirugias = Cirugias;
		clsPac._ActividadFisica = ActividadFisica;
		clsPac._Observaciones = Observaciones;
		clsPac._HEA = HEA;
		clsPac._MC = MC;
		clsPac._Tratamientos = Tratamientos;
		clsPac._Precio = Precio;
		if (clsPac._PacienteID == 0)
		{
			clsPac.Insertar();
		}
		else
		{
			clsPac.Modificar();
		}
	}

	public void EliminarPaciente()
	{
		clsPac.Eliminar();
	}

	public DataTable DevolverTodasPacientes()
	{
		return BD.ConsultaVer("Pacientes.PacienteID,Pacientes.Nombre", "Pacientes");
	}

	public DataTable ReturnEstadoCivil()
	{
		return clsPac.ReturnEstadoCivil();
	}
}
