using System;

namespace ControlConsumoLib;

public class ctlArqueo
{
	private readonly clsArqueo clsArq;

	public ctlArqueo()
	{
		clsArq = new clsArqueo();
	}

	public void setArqueoID(int ID)
	{
		clsArq.ArqueoID = ID;
	}

	public int getArqueoID()
	{
		return clsArq.ArqueoID;
	}

	public void Insertar(int _b200, int _b100, int _b50, int _b20, int _b10, int _b5, int _b2, int _b1, int _c50, int _c20, int _c10, int _d100, int _d50, int _d20, int _d10, float _monto, int _personalID, DateTime _fecha, int _turnoID, int _d5, int _d1, double _OtrasCajas)
	{
		clsArq.B200 = _b200;
		clsArq.B100 = _b100;
		clsArq.B50 = _b50;
		clsArq.B20 = _b20;
		clsArq.B10 = _b10;
		clsArq.B5 = _b5;
		clsArq.B2 = _b2;
		clsArq.B1 = _b1;
		clsArq.C50 = _c50;
		clsArq.C20 = _c20;
		clsArq.C10 = _c10;
		clsArq.D100 = _d100;
		clsArq.D50 = _d50;
		clsArq.D20 = _d20;
		clsArq.D10 = _d10;
		clsArq.D5 = _d5;
		clsArq.D1 = _d1;
		clsArq.OtrasCajas = _OtrasCajas;
		clsArq.Tarjetas = _monto;
		clsArq.PersonalID = _personalID;
		clsArq.Fecha = _fecha;
		clsArq.TurnoID = _turnoID;
		clsArq.Insertar();
	}

	public void Modificar(int _turnoID)
	{
		clsArq.TurnoID = _turnoID;
		clsArq.Modificar();
	}

	public void Eliminar()
	{
		clsArq.Eliminar();
	}

	public void devolverMontosUltimoTurno(ref double bs, ref double dolar)
	{
		clsArq.devolverMontosUltimoTurno(ref bs, ref dolar);
	}
}
