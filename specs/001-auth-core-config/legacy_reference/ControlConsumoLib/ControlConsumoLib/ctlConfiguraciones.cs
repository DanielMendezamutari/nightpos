using System;
using System.Data;
using ConfigToptech;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlConfiguraciones
{
	private readonly clsConfiguraciones clsConf;

	public ctlConfiguraciones()
	{
		clsConf = new clsConfiguraciones();
	}

	public void DevolverNombreEmpresa1(ref string empresa)
	{
		clsConf.Devolver();
		empresa = clsConf._NombreEmpresa;
	}

	public void DevolverRazonSocial(ref string RazonSocial)
	{
		clsConf.Devolver();
		if (clsConf._Dueño.Trim().Length > 0)
		{
			RazonSocial = clsConf._Dueño;
		}
		else
		{
			RazonSocial = clsConf._NombreEmpresa;
		}
	}

	public void DevolverPorcentaje(ref bool conporcentaje, ref double porcentaje, ref string sucursal, ref string Descripcion)
	{
		clsConf.Devolver();
		conporcentaje = clsConf._ConPorcentaje;
		porcentaje = clsConf._Porcentaje;
		sucursal = clsConf._Sucursal;
		Descripcion = clsConf._Descripcion;
	}

	public void devolver(ref int idconfiguracion, ref bool conporcentaje, ref double porcentaje, ref string empresa, ref string sucursal, ref string direccion, ref string telefono, ref string dueño, ref string email, ref string actividadEconomica, ref string comentario, ref string ley, ref bool soloAdminBorra, ref bool cant, ref bool DatosFacturas, ref bool DobleFactura, ref bool FacturaBackupArchivo, ref bool FacturaExpress, ref string municipio)
	{
		clsConf.Devolver();
		idconfiguracion = checked((int)Math.Round(clsConf._IdConfiguracion));
		conporcentaje = clsConf._ConPorcentaje;
		porcentaje = clsConf._Porcentaje;
		empresa = clsConf._NombreEmpresa;
		sucursal = clsConf._Sucursal;
		direccion = clsConf._Direccion;
		telefono = clsConf._Telefono;
		dueño = clsConf._Dueño;
		email = clsConf._Email;
		actividadEconomica = clsConf._ActividadEconomica;
		comentario = clsConf._Comentario;
		ley = clsConf._Ley;
		soloAdminBorra = clsConf._soloAdminBorra;
		cant = clsConf._CantidadPersonas;
		DatosFacturas = clsConf.DatosFacturas;
		DobleFactura = clsConf.DobleFactura;
		FacturaBackupArchivo = clsConf.FacturaBackupArchivo;
		FacturaExpress = clsConf._FacturacionExpress;
		municipio = clsConf._Municipio;
	}

	public bool manejaServicio()
	{
		clsConf.Devolver();
		return clsConf._ConPorcentaje;
	}

	public bool ImprimeOtrasCuentas()
	{
		return clsConf.DevolverImprimeOtrasCuentas();
	}

	public bool conCantiPersonas()
	{
		clsConf.Devolver();
		return clsConf._CantidadPersonas;
	}

	public bool CerrarTurnoMesasAbiertas()
	{
		return clsConf.CerrarTurnoMesasAbiertas();
	}

	public bool DevolverSoloAdminBorra()
	{
		return clsConf.DevolverSoloAdminBorra();
	}

	public string devolverEmails()
	{
		clsConf.Devolver();
		return clsConf._Email;
	}

	public string devolverActividadEconomica()
	{
		clsConf.Devolver();
		return clsConf._ActividadEconomica;
	}

	public string devolverDireccion()
	{
		clsConf.Devolver();
		return clsConf._Direccion.Replace("\r\n", "");
	}

	public DataTable devolverConfiguraciones()
	{
		return clsConf.devolverConfiguraciones();
	}

	public int devolverCantConfiguraciones()
	{
		return clsConf.devolverCantConfirguraciones();
	}

	public string devolverLey()
	{
		clsConf.Devolver();
		return clsConf._Ley;
	}

	public string devolverSucursal()
	{
		clsConf.Devolver();
		return clsConf._Sucursal;
	}

	public string devolverComentario()
	{
		clsConf.Devolver();
		return clsConf._Comentario;
	}

	public string devolverImprimirdatosFactura()
	{
		clsConf.DevolverDatosFacturas();
		return Conversions.ToString(clsConf.DatosFacturas);
	}

	public void Guardar(bool Conporcentaje, double porcentaje, string NombreEmpresa, string sucursal, string direccion, string telefono, string dueño, string email, string actividadEconomica, string comentario, string ley, bool soloAdminBorras, bool CantidadPersonas, bool DatosFacturas, bool DobleFactura, bool FacturaBackupArchivo, bool facturaExpress, string Municipio, bool ImprimeOtrasCuentas)
	{
		clsConf._ConPorcentaje = Conporcentaje;
		clsConf._Porcentaje = porcentaje;
		clsConf._NombreEmpresa = NombreEmpresa;
		clsConf._Sucursal = sucursal;
		clsConf._Direccion = direccion;
		clsConf._Municipio = Municipio;
		clsConf._Telefono = telefono;
		clsConf._Dueño = dueño;
		clsConf._Ley = ley;
		clsConf._Email = email;
		clsConf._ActividadEconomica = actividadEconomica;
		clsConf._Comentario = comentario;
		clsConf.DatosFacturas = DatosFacturas;
		clsConf.DobleFactura = DobleFactura;
		clsConf.FacturacionExpress = facturaExpress;
		clsConf._CantidadPersonas = CantidadPersonas;
		clsConf._soloAdminBorra = soloAdminBorras;
		clsConf.ImprimeOtrasCuentas = ImprimeOtrasCuentas;
		configuration.gManejaElServicio = Conporcentaje;
		clsConf._FacturaBackupArchivo = FacturaBackupArchivo;
		VariableGeneral.gProductoServicioPorcentaje = porcentaje / 100.0;
		clsConf.Modificar();
	}

	public int devolverVersion()
	{
		return clsConf.devolverVersion();
	}

	public bool devolverFacturarPropina()
	{
		return clsConf.devolverFacturarPropina();
	}

	public string devolverDescripcion()
	{
		return clsConf.devolverDescripcion();
	}

	public int devolverNroOrden()
	{
		clsConf.DevolverNroOrden();
		return clsConf._NroOrden;
	}

	public int devolverFacturaBucle()
	{
		return clsConf.devolverFacturaBucle();
	}

	public bool devolverGuardarCliente()
	{
		return clsConf.devolverGuardarCliente();
	}

	public string devolverPCPedidos()
	{
		return clsConf.devolverPCpedidos();
	}

	public int devolverCuentaFormatoFactura1()
	{
		return clsConf.devolverCuentaFormatoFactura();
	}

	public bool devolverDobleCuenta()
	{
		return clsConf.devolverDobleCuenta();
	}

	public bool devolverFacturaXWhatsapp()
	{
		return clsConf.devolverFacturaXWhatsapp();
	}

	public void GuardarFacturaBucle(int cant)
	{
		clsConf.GuardarFacturaBucle(cant);
	}

	public void GuardarDobleCuenta(bool doble)
	{
		clsConf.GuardarDobleCuenta(doble);
	}

	public void GuardarFacturaXwhatsapp(bool fact)
	{
		clsConf.GuardarFacturaXwhatsapp(fact);
	}

	public void GuardarFacturarPropina(bool si)
	{
		clsConf.GuardarFacturarPropina(si);
	}

	public void GuardarClienteParaLlevar(bool si)
	{
		clsConf.GuardarClienteParaLlevar(si);
	}

	public void GuardarCuentaFormatoFactura(int opcion, int config)
	{
		clsConf.GuardarCuentaFormatoFactura(opcion, config);
	}

	public void GuardarPCpedidos(string PCpedidos)
	{
		clsConf.GuardarPCpedidos(PCpedidos);
	}

	public void GuardarNroOrden(int nroOrden)
	{
		if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Brasargent) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.LocosAsar) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.srPollo))
		{
			if (nroOrden >= 99)
			{
				clsConf._NroOrden = 1;
			}
			else
			{
				clsConf._NroOrden = nroOrden;
			}
		}
		else
		{
			clsConf._NroOrden = nroOrden;
		}
		clsConf.ModificarNroOrden();
	}

	public void IniciarBaseDatos()
	{
		clsConf.IniciarBaseDatos();
	}

	public DataTable DevolverCaberceraReporte1()
	{
		return clsConf.DevolverCaberceraReporte1();
	}

	public DataTable DevolverCaberceraReporte2()
	{
		return clsConf.DevolverCaberceraReporte2();
	}

	public void devolverRpteNota(ref string direccion, ref string telefono, ref string NombreEmpresa)
	{
		clsConf.Devolver();
		direccion = clsConf._Direccion;
		telefono = clsConf._Telefono;
		NombreEmpresa = clsConf._NombreEmpresa;
	}

	public bool DevolverFacturacionExpress()
	{
		return clsConf.DevolverFacturacionExpress();
	}

	public bool DevolverDobleFactura()
	{
		return clsConf.DevolverDobleFactura();
	}

	public bool DevolverFacturaBackupArchivo()
	{
		return clsConf.DevolverFacturaBackupArchivo();
	}
}
