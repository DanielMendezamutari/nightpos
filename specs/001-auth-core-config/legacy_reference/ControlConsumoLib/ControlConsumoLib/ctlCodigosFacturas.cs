using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlCodigosFacturas
{
	private readonly clsCodigosFacturas clsCod;

	public ctlCodigosFacturas()
	{
		clsCod = new clsCodigosFacturas();
	}

	public int GetCodigoID()
	{
		return clsCod._CodigoID;
	}

	public void SetCodigoID(int ID)
	{
		clsCod._CodigoID = ID;
	}

	public clsCodigosFacturas LlenarClase()
	{
		clsCod.llenarclase();
		return clsCod;
	}

	public DateTime getFechaHasta()
	{
		return clsCod._FechaHasta;
	}

	public bool DevolverCodigoPorID(int codigoID, ref string llave, ref long autorizacion, ref int NroFact, ref string nit, ref string fechaLimiteEmision, ref bool Activo)
	{
		clsCod._CodigoID = codigoID;
		if (clsCod.DevolverCodigoPorID())
		{
			llave = clsCod._Llave;
			autorizacion = clsCod._autorizacion;
			NroFact = clsCod._NroFactura;
			nit = clsCod._NIT;
			Activo = clsCod._Activo;
			fechaLimiteEmision = clsCod._FechaHasta.ToString("dd/MM/yyyy");
			return true;
		}
		return false;
	}

	public bool DevolverCodigoActivo(DateTime hoy, ref string llave, ref long autorizacion, ref string nit, ref string fechaLimiteEmision, ref bool Activo)
	{
		if (clsCod.DevolverCodigoActivo(hoy))
		{
			llave = clsCod._Llave;
			autorizacion = clsCod._autorizacion;
			Activo = clsCod._Activo;
			nit = clsCod._NIT;
			Activo = clsCod._Activo;
			fechaLimiteEmision = clsCod._FechaHasta.ToString("dd/MM/yyyy");
			return true;
		}
		return false;
	}

	public bool VerificarFacturasLlaves(DateTime hoy)
	{
		if (clsCod.VerificarFacturasLlaves(hoy))
		{
			return true;
		}
		return false;
	}

	public void ModificarNroFactura1(int NroFact)
	{
		clsCod._NroFactura = NroFact;
		clsCod.ModificarNroFactura();
	}

	public int obtenerSgteNroFactura()
	{
		return clsCod.obtenerSgteNroFactura();
	}

	public int DevolverNroFactura2()
	{
		return clsCod.NroFactura2;
	}

	public void ModificarNroFactura2(int NroFact)
	{
		clsCod._NroFactura = NroFact;
		clsCod.ModificarNroFactura2();
	}

	public DataTable devolverCodigosFacturas(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsCod.Devolver();
		}
		return clsCod.Devolver(search, field);
	}

	public DataTable devolverCodigosFacturas()
	{
		return clsCod.Devolver();
	}

	public void GuardarCodigo(DateTime FechaDesde, DateTime FechaHasta, string Llave, long autorizacion, int nroInicial, string nit, bool activo)
	{
		clsCod._FechaDesde = FechaDesde;
		clsCod._FechaHasta = FechaHasta;
		clsCod._Llave = Llave;
		clsCod._autorizacion = autorizacion;
		clsCod._NroFactura = nroInicial;
		clsCod._NIT = nit;
		clsCod._Activo = activo;
		if (clsCod._CodigoID == 0)
		{
			clsCod.Insertar();
		}
		else
		{
			clsCod.Modificar();
		}
	}

	public void EliminarCodigo()
	{
		clsCod.Eliminar();
	}

	public string DevolverUltimoNIT()
	{
		clsCod.DevolverUltimoNIT();
		return clsCod._NIT;
	}

	public string DevolverUltimoNITFactElect()
	{
		clsCod.DevolverUltimoNITFactElec();
		return clsCod._NIT;
	}
}
