using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlSeguimiento
{
	private readonly clsSeguimiento clsSeg;

	public ctlSeguimiento()
	{
		clsSeg = new clsSeguimiento();
	}

	public int GetSeguimientoID()
	{
		return clsSeg._SeguimientoID;
	}

	public void SetSeguimientoID(int ID)
	{
		clsSeg._SeguimientoID = ID;
	}

	public DataTable devolverSeguimiento(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsSeg.Devolver();
		}
		return clsSeg.Devolver(search, field);
	}

	public DataTable devolverSeguimiento()
	{
		return clsSeg.Devolver();
	}

	public void GuardarSeguimiento(DateTime Fecha, double Peso, double Espalda, double Pecho, double Hombro, double BrazoD, double BrazoI, double CinturaAlta, double CinturaMedia, double CinturaBaja, double Caderas, double Gluteos, double PiernaD, double PiernaI, double GemeloD, double GemeloI, string Observacion, int pacienteID)
	{
		clsSeg._Fecha = Fecha;
		clsSeg._Peso = Peso;
		clsSeg._Espalda = Espalda;
		clsSeg._Pecho = Pecho;
		clsSeg._Hombros = Hombro;
		clsSeg._BrazoD = BrazoD;
		clsSeg._BrazoI = BrazoI;
		clsSeg._CinturaAlta = CinturaAlta;
		clsSeg._CinturaMedia = CinturaMedia;
		clsSeg._CinturaBaja = CinturaBaja;
		clsSeg._Caderas = Caderas;
		clsSeg._Gluteos = Gluteos;
		clsSeg._PiernaD = PiernaD;
		clsSeg._PiernaI = PiernaI;
		clsSeg._GemeloD = GemeloD;
		clsSeg._GemeloI = GemeloI;
		clsSeg._Observacion = Observacion;
		clsSeg._PacienteID = pacienteID;
		if (clsSeg._SeguimientoID == 0)
		{
			clsSeg.Insertar();
		}
		else
		{
			clsSeg.Modificar();
		}
	}

	public void EliminarSeguimiento()
	{
		clsSeg.Eliminar();
	}

	public DataTable devolverSeguimientoPorPacienteID(int id)
	{
		clsSeg._PacienteID = id;
		return clsSeg.DevolverXPacienteID();
	}
}
