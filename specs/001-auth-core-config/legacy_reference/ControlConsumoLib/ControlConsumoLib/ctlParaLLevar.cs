using System;
using System.Data;

namespace ControlConsumoLib;

public class ctlParaLLevar
{
	private readonly clsParaLLevar clsPar;

	public ctlParaLLevar()
	{
		clsPar = new clsParaLLevar();
	}

	public int GetParaLlevarID()
	{
		return clsPar._ParaLlevarID;
	}

	public void SetParaLlevarID(int ID)
	{
		clsPar._ParaLlevarID = ID;
	}

	public clsParaLLevar LlenarClase()
	{
		clsPar.llenarclase();
		return clsPar;
	}

	public clsParaLLevar cargarPorNombre(string nombre)
	{
		clsPar._Nombre = nombre;
		clsPar.cargarPorNombre();
		return clsPar;
	}

	public DataTable devolverDireccionesPorNombre(string nombre)
	{
		clsPar._Nombre = nombre;
		return clsPar.devolverDireccionesPorNombre();
	}

	public clsParaLLevar cargarPorNit(string nit)
	{
		clsPar._NIT = nit;
		clsPar.cargarPorNit();
		return clsPar;
	}

	public DataTable cargarDetallePorTelefono(string telefono)
	{
		clsPar._Telefono = telefono;
		return clsPar.cargarDetallePorTelefono();
	}

	public DataTable cargarDetallePorNit(string Nit)
	{
		clsPar._NIT = Nit;
		return clsPar.cargarDetallePorNIT();
	}

	public DataTable cargarDetallePorNombre(string Nit)
	{
		clsPar._NIT = Nit;
		return clsPar.cargarDetallePorNombre();
	}

	public clsParaLLevar cargarPorTelefono(string telefono)
	{
		clsPar._Telefono = telefono;
		clsPar.cargarPorTelefono();
		return clsPar;
	}

	public DataTable devolverParaLLevar(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsPar.Devolver();
		}
		return clsPar.Devolver(search, field);
	}

	public DataTable devolverParaLLevar()
	{
		return clsPar.Devolver();
	}

	public DataTable devolverNombresParaLLevar()
	{
		return clsPar.devolverNombresParaLLevar();
	}

	public void GuardarParaLlevar(string Nombre, string NombreFactura, string NIT, DateTime HoraRecoger, string Telefono, string direccion, int motociclista, string notas, double laty_Num, double lonx_num, string MetodoPago, string email)
	{
		clsPar._Nombre = Nombre;
		clsPar._NombreFactura = NombreFactura;
		clsPar._NIT = NIT;
		clsPar._HoraRecoger = HoraRecoger;
		clsPar._Telefono = Telefono;
		clsPar._Motociclista = motociclista;
		clsPar._notas = notas;
		clsPar._Direccion = direccion;
		clsPar._MetodoPago = MetodoPago;
		clsPar._laty_num = laty_Num;
		clsPar._lonx_num = lonx_num;
		clsPar._email = email;
		if (clsPar._ParaLlevarID == 0)
		{
			clsPar.Insertar(email);
		}
		else
		{
			clsPar.Modificar();
		}
	}

	public void UpdateRepartidor(int repartidorID)
	{
		clsPar._Motociclista = repartidorID;
		clsPar.UpdateRepartidor();
	}

	public void UpdateMetodoPago(string metodoPago)
	{
		clsPar._MetodoPago = metodoPago;
		clsPar.UpdateMetodoPago();
	}

	public void UpdateMontoDelivery(double monto)
	{
		clsPar.UpdateMontoDelivery(monto);
	}

	public double getMontoDelivery()
	{
		return clsPar.getMontoDelivery();
	}

	public void EliminarParaLlevar()
	{
		clsPar.Eliminar();
	}
}
