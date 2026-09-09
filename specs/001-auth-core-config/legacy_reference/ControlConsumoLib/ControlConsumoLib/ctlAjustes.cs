using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlAjustes
{
	private readonly clsAjustes clsAju;

	public ctlAjustes()
	{
		clsAju = new clsAjustes();
	}

	public int GetAjusteID()
	{
		return clsAju._AjusteID;
	}

	public DateTime getLastDate()
	{
		clsAju.getLastDate();
		return clsAju._Fecha;
	}

	public void SetAjusteID(int ID)
	{
		clsAju._AjusteID = ID;
	}

	public clsAjustes LlenarClase()
	{
		clsAju.llenarclase();
		return clsAju;
	}

	public DataTable devolverAjustes(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsAju.Devolver();
		}
		return clsAju.Devolver(search, field);
	}

	public DataTable Devolver1mesAtras()
	{
		return clsAju.Devolver1mesAtras();
	}

	public DataTable devolverAjustes()
	{
		return clsAju.Devolver();
	}

	public void GuardarAjuste(DateTime Fecha, string Observacion, int AlmacenID)
	{
		clsAju._Fecha = Fecha;
		clsAju._Observacion = Observacion;
		clsAju._AlmacenID = AlmacenID;
		if (clsAju._AjusteID == 0)
		{
			clsAju.Insertar();
		}
		else
		{
			clsAju.Modificar();
		}
	}

	public void EliminarAjuste()
	{
		clsAju.Eliminar();
	}

	public DataTable devolverReporteAjustes(DateTime fechaIni, DateTime fechaFin, int amacenID)
	{
		clsAju._AlmacenID = amacenID;
		return clsAju.DevolverReporteAjustes(fechaIni, fechaFin);
	}

	public DataTable devolverReporteAjustes2(DateTime fechaIni, DateTime fechaFin, int amacenID)
	{
		clsAju._AlmacenID = amacenID;
		return clsAju.DevolverReporteAjustes2(fechaIni, fechaFin);
	}

	public DataTable devolverReporteAjustes3(int AjusteIni, int AjusteFin, DateTime fechaIni, DateTime fechaFin)
	{
		return clsAju.DevolverReporteAjustes3(AjusteIni, AjusteFin, fechaIni, fechaFin);
	}

	public DataTable devolverListaAjustes(int almacenID)
	{
		clsAju._AlmacenID = almacenID;
		return clsAju.DevolverListaAjustes();
	}
}
