using System;
using System.Data;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlAnticipos
{
	private readonly clsAnticipos clsAntic;

	private readonly clsAnticiposCuentas clsAntCuenta;

	public ctlAnticipos()
	{
		clsAntic = new clsAnticipos();
		clsAntCuenta = new clsAnticiposCuentas();
	}

	public int GetAnticipoID()
	{
		return clsAntic._AnticipoID;
	}

	public void SetAnticipoID(int ID)
	{
		clsAntic._AnticipoID = ID;
	}

	public double devolverxFecha(DateTime fechaIni, DateTime fechafin)
	{
		return clsAntic.devolverxFecha(fechaIni, fechafin);
	}

	public clsAnticipos LlenarClaseAnticipo()
	{
		clsAntic.llenarclase();
		return clsAntic;
	}

	public DataTable devolverAnticipo(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsAntic.Devolver();
		}
		return clsAntic.Devolver(search, field);
	}

	public DataTable devolverAnticipo()
	{
		return clsAntic.Devolver();
	}

	public void GuardarAnticipos1(DateTime Fecha, double monto, double montoRestante, int clienteId, int cuentaId)
	{
		if (montoRestante < 0.001)
		{
			montoRestante = 0.0;
		}
		clsAntic._Fecha = Fecha;
		clsAntic._Monto = monto;
		clsAntic._MontoRestante = montoRestante;
		clsAntic._ClienteID = clienteId;
		clsAntic._CuentaID = cuentaId;
		if (clsAntic._AnticipoID == 0)
		{
			clsAntic.Insertar1();
		}
		else
		{
			clsAntic.Modificar();
		}
	}

	public void EliminarClientePedido()
	{
		clsAntic.Eliminar();
	}

	public int eliminarAnticipoPorID(int id)
	{
		clsAntic._AnticipoID = id;
		return clsAntic.Eliminar();
	}

	public double devolverSaldo(int id)
	{
		clsAntic._ClienteID = id;
		return clsAntic.DevolverSaldo();
	}

	public int DevolverMontoRestante(int id, ref double monto)
	{
		clsAntic._ClienteID = id;
		return clsAntic.DevolverMontoRestante(ref monto);
	}

	public void ReducirAnticipo(int idCliente, double monto)
	{
		clsAntic._ClienteID = idCliente;
		clsAntic.RedicirAnticipo(monto);
	}

	public void DevolverAnticipoPagado(int detalleCuentaId)
	{
		clsAntCuenta._DetalleCuentaID = detalleCuentaId;
		DataTable dataTable = clsAntCuenta.DevolverDetalleCuentaID();
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				clsAntic._AnticipoID = Conversions.ToInteger(dataTable.Rows[i][0]);
				clsAntic.DevolverAnticipos(Conversions.ToDouble(dataTable.Rows[i][2]));
			}
			clsAntCuenta.EliminarAnticipoCuenta();
		}
	}

	public void devolverAnticipo(int idCliente, double monto)
	{
		clsAntic._ClienteID = idCliente;
		clsAntic.RedicirAnticipo(monto);
	}

	public void AnticipoNegativo(int idCliente, double monto)
	{
		clsAntic._ClienteID = idCliente;
		clsAntic.RedicirAnticipo(monto);
	}
}
