using System.Data;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlCuentas
{
	public enum MonedaCuenta
	{
		Bs,
		Dolares
	}

	public enum CuentaID
	{
		CajaChicaBs = 1,
		CajaChicaDolares,
		Tarjeta,
		Anticipo
	}

	private readonly clsCuentas clsCue;

	public ctlCuentas()
	{
		clsCue = new clsCuentas();
	}

	public int GetCuentaID()
	{
		return clsCue._CuentaID;
	}

	public void SetCuentaID(int ID)
	{
		clsCue._CuentaID = ID;
	}

	public DataTable devolverCuentas(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsCue.Devolver();
		}
		return clsCue.Devolver(search, field);
	}

	public DataTable devolverCuentas()
	{
		return clsCue.Devolver();
	}

	public void GuardarCuenta(string Nombre, string Banco, string TipoCuenta, long Nro, string Observacion, bool Activa, bool Moneda, bool esDeposito, bool EsGiftCard, int metodoPagoSIN, bool EsAdmin, bool FacturaObligatoria)
	{
		clsCue._Nombre = Nombre;
		clsCue._Banco = Banco;
		clsCue._TipoCuenta = TipoCuenta;
		clsCue._Nro = Conversions.ToString(Nro);
		clsCue._Observacion = Observacion;
		clsCue._Activa = Activa;
		clsCue._Moneda = Moneda;
		clsCue._EsDeposito = esDeposito;
		clsCue._EsGiftCard = EsGiftCard;
		clsCue._metodoPagoSIN = metodoPagoSIN;
		clsCue._EsAdmin = EsAdmin;
		clsCue._FacturaObligatoria = FacturaObligatoria;
		if (clsCue._CuentaID == 0)
		{
			clsCue.Insertar();
		}
		else
		{
			clsCue.Modificar();
		}
	}

	public void ModificarGIFCARD(bool EsGiftCard)
	{
		clsCue._EsGiftCard = EsGiftCard;
		clsCue.ModificarGIFCARD();
	}

	public void EliminarCuenta()
	{
		clsCue.Eliminar();
	}

	public DataTable DevolverActivaMenosCajas()
	{
		return clsCue.DevolverActivaMenosCajas();
	}

	public DataTable DevolverActiva()
	{
		return clsCue.DevolverActiva();
	}

	public DataTable DevolverActivaSinDolares()
	{
		return clsCue.DevolverActivaSinDolares();
	}

	public DataTable devolverDescripcion()
	{
		return clsCue.devolverDescripcion();
	}

	public string devolverObservacion()
	{
		return clsCue.devolverObservacion();
	}

	public DataTable devolverTotalXCuentaID()
	{
		return clsCue.devolverTotalXCuentaID();
	}

	public int devolverMonedaCuentaID(int cuentaID)
	{
		clsCue._CuentaID = cuentaID;
		return clsCue.devolverMonedaCuentaID();
	}

	public DataTable devolverMonedaCuentaIDPorTipoMoneda(int moneda)
	{
		return clsCue.devolverMonedaCuentaIDPorTipoMoneda(moneda);
	}

	public DataTable DevolverTodasCtas()
	{
		return BD.ConsultaVer("Cuentas.CuentaID,Cuentas.Nombre", "Cuentas");
	}

	public int ExisteCuentaTigoMoney()
	{
		DataTable dataTable = BD.ConsultaVer("Cuentas.CuentaID", "Cuentas", "Nombre like 'Tigo Money'");
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		return 0;
	}

	public DataTable DevolverCuentaspagos()
	{
		return clsCue.DevolverCuentasPagos();
	}

	public int devolverCuentaIdPorNombre(string nombre)
	{
		return clsCue.devolverCuentaIdPorNombre(nombre);
	}

	public int devolverCuentaIdPorNombreActivas(string nombre)
	{
		return clsCue.devolverCuentaIdPorNombreActivas(nombre);
	}

	public void DevolverNombreMoneda(ref string nombre, ref bool moneda)
	{
		clsCue.DevolverNombreMoneda();
		moneda = clsCue._Moneda;
		nombre = clsCue._Nombre;
	}

	public string DevolverCuenta(ref int cuentaID)
	{
		clsCue._CuentaID = cuentaID;
		return clsCue.devolverCuenta();
	}

	public int devolverCuentaXVenta(ref int ventaID)
	{
		return clsCue.devolverCuentaXVenta(ventaID);
	}

	public bool esTarjetaMetodoPagoSIN(int cuentaID)
	{
		clsCue._CuentaID = cuentaID;
		return clsCue.esTarjetaMetodoPagoSIN();
	}

	public bool esFacturaObligatoria(int cuentaID)
	{
		clsCue._CuentaID = cuentaID;
		return clsCue.esFacturaObligatoria();
	}
}
