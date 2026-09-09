using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlTraspasos
{
	private readonly clsTraspasos clsTras;

	public ctlTraspasos()
	{
		clsTras = new clsTraspasos();
	}

	public int GetTraspasoID()
	{
		return clsTras._TraspasoID;
	}

	public void SetTraspasoID(int ID)
	{
		clsTras._TraspasoID = ID;
	}

	public clsTraspasos LlenarClase()
	{
		clsTras.llenarclase();
		return clsTras;
	}

	public DataTable devolverTraspasos(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsTras.Devolver();
		}
		return clsTras.Devolver(search, field);
	}

	public DataTable devolverTraspasos()
	{
		return clsTras.Devolver();
	}

	public double verificarCantItems(BD_SQL bd = null)
	{
		return clsTras.verificarCantItems(bd);
	}

	public void GuardarTraspaso(DateTime Fecha, string Observacion, int almacenID, int almacenID2, int meseroID, int encargadoID, BD_SQL bd = null)
	{
		clsTras._Fecha = Fecha;
		clsTras._Observacion = Observacion;
		clsTras._almacenID = almacenID;
		clsTras._almacenID2 = almacenID2;
		clsTras._MeseroID = meseroID;
		clsTras._EncargadoID = encargadoID;
		if (clsTras._TraspasoID == 0)
		{
			clsTras.Insertar(bd);
		}
		else
		{
			clsTras.Modificar(bd);
		}
	}

	public void EliminarTraspaso()
	{
		clsTras.Eliminar();
	}

	public bool ExisteAlmacen(int idAlmacen)
	{
		clsTras._almacenID = idAlmacen;
		return clsTras.ExisteAlmacen();
	}

	public DataTable devolverTraspasos1()
	{
		return clsTras.DevolverTraspasos();
	}

	public DataTable devolverTraspasoParcial()
	{
		return clsTras.DevolverTraspasoParcial();
	}

	public DataTable devolverReporteTraspasos(DateTime fechaIni, DateTime fechaFin, int meseroId)
	{
		clsTras._MeseroID = meseroId;
		return clsTras.DevolverReporteTraspasos(fechaIni, fechaFin);
	}

	public int DevolverEncargadoID(int traspasoID)
	{
		clsTras._TraspasoID = traspasoID;
		return clsTras.DevolverEncargadoID();
	}

	public bool ModificarObservacion(string observacion)
	{
		clsTras._Observacion = observacion;
		return clsTras.ModificarObservacion() != 0;
	}
}
