using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlDevoluciones
{
	private readonly clsDevoluciones clsDev;

	public ctlDevoluciones()
	{
		clsDev = new clsDevoluciones();
	}

	public int GetDevolucionId()
	{
		return clsDev._DevolucionId;
	}

	public void SetDevolucionId(int ID)
	{
		clsDev._DevolucionId = ID;
	}

	public clsDevoluciones LlenarClase()
	{
		clsDev.llenarclase();
		return clsDev;
	}

	public DataTable devolverDevoluciones()
	{
		return clsDev.Devolver();
	}

	public double devolverCantidadAnterior(int detalleCuentaId)
	{
		clsDev._DetalleCuentaID = detalleCuentaId;
		DataTable dataTable = clsDev.DevolverXID();
		if (dataTable.Rows.Count == 0)
		{
			return 0.0;
		}
		return Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Cantidad"])) ? ((object)0) : dataTable.Rows[0]["Cantidad"]);
	}

	public void GuardarDevolucion(DateTime fecha, double cantidad, string observacion, int detalleCuentaId)
	{
		clsDev._Fecha = fecha;
		clsDev._Cantidad = cantidad;
		clsDev._Observacion = observacion;
		clsDev._DetalleCuentaID = detalleCuentaId;
		DataTable dataTable = clsDev.DevolverXID();
		if (dataTable.Rows.Count == 0)
		{
			clsDev.Insertar();
			return;
		}
		clsDev._DevolucionId = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DevolucionID"])) ? ((object)0) : dataTable.Rows[0]["DevolucionID"]);
		clsDev.Modificar();
	}

	public void EliminarDevolucion()
	{
		clsDev.Eliminar();
	}

	public DataTable DevolverUltimasCuentas(int idCliente)
	{
		return clsDev.DevolverUltimasCuentas(idCliente);
	}
}
