using System.Data;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlAlmacenes
{
	private readonly clsAlmacenes clsAlm;

	public ctlAlmacenes()
	{
		clsAlm = new clsAlmacenes();
	}

	public int GetAlmacenID()
	{
		return clsAlm._AlmacenID;
	}

	public void SetAlmacenID(int ID)
	{
		clsAlm._AlmacenID = ID;
	}

	public DataTable devolverAlmacenes(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsAlm.Devolver();
		}
		return clsAlm.Devolver(search, field);
	}

	public DataTable DevolverMiData2()
	{
		return clsAlm.DevolverMiData();
	}

	public clsAlmacenes LlenarClase1()
	{
		clsAlm.llenarclase();
		return clsAlm;
	}

	public string devolverSQLs()
	{
		clsAlm.llenarclase();
		string text = "";
		if (clsAlm._IP.Length > 0)
		{
			if (clsAlm._Instancia.Length > 0)
			{
				return "packet size=4096;data source=" + clsAlm._IP + "\\" + clsAlm._Instancia + ";initial catalog=" + clsAlm._Descripcion + ";User Id=" + clsAlm._Usuario + ";Password=" + clsAlm._Pass + ";";
			}
			return "packet size=4096;data source=" + clsAlm._IP + ";initial catalog=" + clsAlm._Descripcion + ";User Id=" + clsAlm._Usuario + ";Password=" + clsAlm._Pass + ";";
		}
		return "packet size=4096;data source=" + clsAlm._Instancia + ";initial catalog=" + clsAlm._Descripcion + ";User Id=" + clsAlm._Usuario + ";Password=" + clsAlm._Pass + ";";
	}

	public string getnombre()
	{
		return clsAlm._Nombre;
	}

	public DataTable devolverAlmacenes()
	{
		return clsAlm.Devolver();
	}

	public DataTable devolverTodosAlmacenes(int meseroID)
	{
		clsAlm._ResponsableID = meseroID;
		return clsAlm.DevolverTodosAlmacenes();
	}

	public DataTable DevolverTodosAlmacenesExternosConConexion()
	{
		return clsAlm.DevolverTodosAlmacenesExternosConConexion();
	}

	public DataTable DevolverTodosAlmacenesInternos(int meseroID, BD_SQL bd1 = null)
	{
		clsAlm._ResponsableID = meseroID;
		return clsAlm.DevolverTodosAlmacenesInternos(bd1);
	}

	public DataTable DevolverSoloMisAlmacenesInternos(int meseroID)
	{
		clsAlm._ResponsableID = meseroID;
		return clsAlm.DevolverSoloMisAlmacenesInternos();
	}

	public int getIDporNombreAlmacen(string nombre, BD_SQL bd)
	{
		return clsAlm.getIDporNombreAlmacen(nombre, bd);
	}

	public bool TieneIP(int IDalm)
	{
		clsAlm._AlmacenID = IDalm;
		return clsAlm.TieneIP();
	}

	public bool TieneInstancia(int IDalm)
	{
		clsAlm._AlmacenID = IDalm;
		return clsAlm.TieneInstancia();
	}

	public void GuardarAlmacen(string Nombre, string Descripcion, string IP, string Instancia, string Usuario, string Pass, bool Interno, bool inicioInterno, int ResponsableID)
	{
		clsAlm._Nombre = Nombre;
		clsAlm._Descripcion = Descripcion;
		clsAlm._IP = IP;
		clsAlm._Instancia = Instancia;
		clsAlm._Usuario = Usuario;
		clsAlm._Pass = Pass;
		clsAlm._Interno = Interno;
		clsAlm._ResponsableID = ResponsableID;
		if (clsAlm._AlmacenID == 0)
		{
			clsAlm.Insertar();
			if (Interno)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Stock" + Conversions.ToString(clsAlm._AlmacenID) + " float");
				BD.ConsultaModificar("Productos", "Stock" + Conversions.ToString(clsAlm._AlmacenID) + "= 0", "1=1");
			}
			return;
		}
		if (inicioInterno & !Interno)
		{
			BD.ConsultWithOutAlerts("ALTER TABLE Productos drop COLUMN Stock" + Conversions.ToString(clsAlm._AlmacenID));
		}
		if (!inicioInterno & Interno)
		{
			BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Stock" + Conversions.ToString(clsAlm._AlmacenID) + " float");
			BD.ConsultaModificar("Productos", "Stock" + Conversions.ToString(clsAlm._AlmacenID) + "= 0", "1=1");
		}
		clsAlm.Modificar();
	}

	public void EliminarAlmacen()
	{
		clsAlm.Eliminar();
		BD.ConsultWithOutAlerts("ALTER TABLE Productos drop COLUMN Stock" + Conversions.ToString(clsAlm._AlmacenID));
	}
}
