using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlPreProcesamientos
{
	private readonly clsPreProcesamientos clsPro;

	private readonly clsPreProcesamientosDe clsPreDe;

	private readonly clsPreProcesamientosPara clsPrePara;

	public ctlPreProcesamientos()
	{
		clsPro = new clsPreProcesamientos();
		clsPreDe = new clsPreProcesamientosDe();
		clsPrePara = new clsPreProcesamientosPara();
	}

	public int GetPreprocesamientoID()
	{
		return clsPro._PreprocesamientoID;
	}

	public void SetPreprocesamientoID(int ID)
	{
		clsPro._PreprocesamientoID = ID;
	}

	public DataTable devolverPreProcesamientos(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsPro.devolver();
		}
		return clsPro.devolver(search, field);
	}

	public DataTable devolverPreProcesamientos()
	{
		return clsPro.devolver();
	}

	public DataTable DevolverTodosPreProcesamientos()
	{
		return BD.ConsultaVer("PreProcesamientos.PreprocesamientoID,PreProcesamientos.Fecha,PreProcesamientos.Observacion,PreProcesamientos.UsuarioID,PreProcesamientos.AlmacenID", "PreProcesamientos");
	}

	public void GuardarPreProcesamientos(DateTime Fecha, string Observacion, int UsuarioID, int almacenID)
	{
		clsPro._Fecha = Fecha;
		clsPro._Observacion = Observacion;
		clsPro._UsuarioID = UsuarioID;
		clsPro._AlmacenID = almacenID;
		if (clsPro._PreprocesamientoID == 0)
		{
			clsPro.Insertar();
		}
		else
		{
			clsPro.Modificar();
		}
	}

	public void EliminarPreprocesamiento()
	{
		clsPro.Eliminar();
	}

	public int GetPreprocesamientoDeID()
	{
		return clsPreDe._PreProcesamientoDeID;
	}

	public void SetPreprocesamientoDeID(int ID)
	{
		clsPreDe._PreProcesamientoDeID = ID;
	}

	public DataTable devolverPreProcesamientosDe(int idPreProc)
	{
		clsPreDe._PreProcesamientoID = idPreProc;
		return clsPreDe.DevolverXID();
	}

	public DataTable DevolverReporte(DateTime fechaIni, DateTime fechaFin)
	{
		return clsPreDe.DevolverReporte(fechaIni, fechaFin);
	}

	public void GuardarPreProcesamientosDe(double Cantidad, int ProductoID, int PreprocesamientoID, double costos)
	{
		clsPreDe._Cantidad = Cantidad;
		clsPreDe._ProductoID = ProductoID;
		clsPreDe._PreProcesamientoID = PreprocesamientoID;
		clsPreDe._Costos = costos;
		clsPreDe.Insertar();
	}

	public void EliminarPreProcesamientosDe(int id)
	{
		clsPreDe._PreProcesamientoDeID = id;
		clsPreDe.Eliminar();
	}

	public int GetPreprocesamientoParaID()
	{
		return clsPrePara._PreProcesamientoParaID;
	}

	public void SetPreprocesamientoParaID(int ID)
	{
		clsPrePara._PreProcesamientoParaID = ID;
	}

	public DataTable devolverPreProcesamientosPara(int idPreProc)
	{
		clsPrePara._PreProcesamientoID = idPreProc;
		return clsPrePara.DevolverXID();
	}

	public void GuardarPreProcesamientosPara(double Cantidad, int ProductoID, int PreprocesamientoID)
	{
		clsPrePara._Cantidad = Cantidad;
		clsPrePara._ProductoID = ProductoID;
		clsPrePara._PreProcesamientoID = PreprocesamientoID;
		clsPrePara.Insertar();
	}

	public void EliminarPreProcesamientosPara(int id)
	{
		clsPrePara._PreProcesamientoParaID = id;
		clsPrePara.Eliminar();
	}
}
