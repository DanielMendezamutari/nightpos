using System.Data;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlTipoEnvios
{
	private readonly clsTipoEnvios clsTiEnv;

	public ctlTipoEnvios()
	{
		clsTiEnv = new clsTipoEnvios();
	}

	public int GetTipoEnvioID()
	{
		return clsTiEnv._TipoEnvioID;
	}

	public void SetTipoEnvioID(int ID)
	{
		clsTiEnv._TipoEnvioID = ID;
	}

	public DataTable devolverTipoEnvios(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsTiEnv.Devolver();
		}
		return clsTiEnv.Devolver(search, field);
	}

	public DataTable DevolverMiData()
	{
		return clsTiEnv.DevolverMiData();
	}

	public DataTable devolverTipoEnvios()
	{
		return clsTiEnv.Devolver();
	}

	public DataTable devolverTodosTipoEnvios()
	{
		return clsTiEnv.DevolverTodosTipoEnvios();
	}

	public int devolverCuentaCupones(string nombre)
	{
		return clsTiEnv.devolverCuentaCupones(nombre);
	}

	public int devolverCuentaTarjeta(string nombre)
	{
		return clsTiEnv.devolverCuentaTarjeta(nombre);
	}

	public int DevolverTipoEnvio(string nopmbre)
	{
		return clsTiEnv.DevolverTipoEnvio(nopmbre);
	}

	public int DevolverDeliveryExterno(string nopmbre)
	{
		return 0 - (clsTiEnv.DevolverDeliveryExterno(nopmbre) ? 1 : 0);
	}

	public DataTable devolverTipoEnviosActivos()
	{
		return clsTiEnv.devolverTipoEnviosActivos();
	}

	public void Guardar(string Nombre, string orden, bool Activo, int TarjetaCuentaID, int CuponesCuentaID, bool DeliveryExterno, BD_SQL bd1 = null)
	{
		clsTiEnv._Nombre = Nombre;
		clsTiEnv._Orden = Conversions.ToInteger(orden);
		clsTiEnv._Activo = Activo;
		clsTiEnv._DeliveryExterno = DeliveryExterno;
		clsTiEnv._TarjetaCuentaID = TarjetaCuentaID;
		clsTiEnv._CuponesCuentaID = CuponesCuentaID;
		if (clsTiEnv._TipoEnvioID == 0)
		{
			clsTiEnv.Insertar(bd1);
			if (bd1 != null)
			{
				if (bd1.ConsultWithOutAlerts("select PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + " from Productos") != 0)
				{
					bd1.ConsultaModificar("Productos", "PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + "= 0", "PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + " is null");
					return;
				}
				bd1.ConsultWithOutAlerts("ALTER TABLE Productos ADD PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + " money");
				bd1.ConsultaModificar("Productos", "PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + "= 0", "PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + " is null");
			}
			else if (BD.ConsultWithOutAlerts("select PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + " from Productos") != 0)
			{
				BD.ConsultaModificar("Productos", "PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + "= 0", "PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + " is null");
			}
			else
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + " money");
				BD.ConsultaModificar("Productos", "PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + "= 0", "PrecioExtraTipoEnvio" + Conversions.ToString(clsTiEnv._TipoEnvioID) + " is null");
			}
		}
		else
		{
			clsTiEnv.Modificar();
		}
	}

	public void Eliminar()
	{
		clsTiEnv.Eliminar();
	}
}
