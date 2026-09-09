using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlProveedores
{
	private readonly clsProveedores clsPro;

	public ctlProveedores()
	{
		clsPro = new clsProveedores();
	}

	public int GetProveedorID()
	{
		return clsPro._ProveedorID;
	}

	public void SetProveedorID(int ID)
	{
		clsPro._ProveedorID = ID;
	}

	public DataTable devolverProveedores()
	{
		return clsPro.Devolver();
	}

	public DataTable devolverProveedoresPorNombreEmpresa()
	{
		return clsPro.devolverProveedoresPorNombreEmpresa();
	}

	public void GuardarProveedor(string NombreEmpresa, string NombreContacto, string Nit, int TelefonoDomicilio, int TelefonoOficina, int Celular, string Email, string Direccion, string Observaciones, int DiasCredito, string Banco, string TitularCuenta, string NroCuenta)
	{
		clsPro._NombreEmpresa = NombreEmpresa;
		clsPro._NombreContacto = NombreContacto;
		clsPro._Nit = Nit;
		clsPro._TelefonoDomicilio = TelefonoDomicilio;
		clsPro._TelefonoOficina = TelefonoOficina;
		clsPro._Celular = Celular;
		clsPro._Email = Email;
		clsPro._Direccion = Direccion;
		clsPro._Observaciones = Observaciones;
		clsPro._DiasCredito = DiasCredito;
		clsPro._Banco = Banco;
		clsPro._TitularCuenta = TitularCuenta;
		clsPro._NroCuenta = NroCuenta;
		if (clsPro._ProveedorID == 0)
		{
			clsPro.Insertar();
		}
		else
		{
			clsPro.Modificar();
		}
	}

	public void EliminarProveedor()
	{
		clsPro.Eliminar();
	}

	public DataTable devolverProveedores(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsPro.Devolver();
		}
		return clsPro.Devolver(search, field);
	}

	public void devolverProveedoresPorID(int id, ref string nit, ref string nombre)
	{
		clsPro._ProveedorID = id;
		clsPro.devolverProveedoresPorID();
		nit = clsPro._Nit;
		nombre = clsPro._NombreEmpresa;
	}

	public void devolverProveedoresReporte(int id, ref string direccion, ref string telefono)
	{
		clsPro._ProveedorID = id;
		clsPro.devolverProveedoresReporte();
		direccion = clsPro._Direccion;
		telefono = Conversions.ToString(clsPro._TelefonoDomicilio) + "-" + Conversions.ToString(clsPro._TelefonoOficina) + "-" + Conversions.ToString(clsPro._Celular);
	}

	public DataTable BuscarProveedores(string Codigo, string Nombre)
	{
		return clsPro.BuscarProveedores(Codigo, Nombre);
	}

	public int DevolverDiasCredito()
	{
		int result;
		try
		{
			DataTable dataTable = clsPro.devolverDiasCredito();
			result = ((dataTable.Rows.Count > 0) ? Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DiasCredito"]), 0)) : 0);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public DataTable DevolverBancos()
	{
		return clsPro.DevolverBancos();
	}
}
