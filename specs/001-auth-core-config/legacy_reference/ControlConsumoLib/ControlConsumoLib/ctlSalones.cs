using System.Data;

namespace ControlConsumoLib;

public class ctlSalones
{
	private readonly clsSalones clsSal;

	public ctlSalones()
	{
		clsSal = new clsSalones();
	}

	public int GetSalonId()
	{
		return clsSal._SalonId;
	}

	public void SetSalonId(int ID)
	{
		clsSal._SalonId = ID;
	}

	public clsSalones LlenarClase()
	{
		clsSal.llenarclase();
		return clsSal;
	}

	public string devolverImpresoraCuentaOverride(int mesaID)
	{
		clsSal.devolerImpresoraCuentaOverride(mesaID);
		return clsSal._ImpresoraCuenta;
	}

	public string devolverImpresoraFActuraOverride(int mesaID)
	{
		clsSal.devolverImpresoraFActuraOverride(mesaID);
		return clsSal._ImpresoraFactura;
	}

	public string devolverImpresoraFacturaOverrideXvisitaID(int visitaID)
	{
		clsSal.devolverImpresoraFacturaOverrideXvisitaID(visitaID);
		return clsSal._ImpresoraFactura;
	}

	public string devolverImpresoraFacturaOverrideXCategoria(int visitaID)
	{
		clsSal.devolverImpresoraFacturaOverrideXCategoria(visitaID);
		return clsSal._ImpresoraFactura;
	}

	public DataTable devolverSalones(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsSal.Devolver();
		}
		return clsSal.Devolver(search, field);
	}

	public DataTable devolverSalones()
	{
		return clsSal.Devolver();
	}

	public DataTable devolverSalonesPorNombre()
	{
		return clsSal.devolverSalonesPorNombre();
	}

	public void GuardarSalon(string Nombre, string impresoraCuenta, string impresoraFactura)
	{
		clsSal._Nombre = Nombre;
		clsSal._ImpresoraCuenta = impresoraCuenta;
		clsSal._ImpresoraFactura = impresoraFactura;
		if (clsSal._SalonId == 0)
		{
			clsSal.Insertar();
		}
		else
		{
			clsSal.Modificar();
		}
	}

	public void EliminarSalon()
	{
		clsSal.Eliminar();
	}
}
