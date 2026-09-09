using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlFacturas
{
	private readonly clsFacturas clsFac;

	public ctlFacturas()
	{
		clsFac = new clsFacturas();
	}

	public int GetFacturaID()
	{
		return clsFac._FacturaID;
	}

	public void SetFacturaID(int ID)
	{
		clsFac._FacturaID = ID;
	}

	public int GetCodigoID()
	{
		return clsFac._CodigoID;
	}

	public int GetVisitaID()
	{
		return clsFac._VisitaID;
	}

	public int getAgruparPagoID()
	{
		return clsFac._AgruparPagoID;
	}

	public int getDocumentoSector()
	{
		return clsFac._DocumentoSector;
	}

	public clsFacturas LlenarClase(bool fact2)
	{
		clsFac.llenarclase(fact2);
		return clsFac;
	}

	public int visitatieneFactura(int visitaId, ref bool factura2, ref int DocumentoSector)
	{
		if (visitaId > 0)
		{
			clsFac._VisitaID = visitaId;
			if (clsFac.visitatieneFactura(ref factura2, ref DocumentoSector))
			{
				return clsFac._NroFactura;
			}
			factura2 = false;
			return 0;
		}
		factura2 = false;
		return 0;
	}

	public int CuantasFacturaEnContingenciaHay(bool todas)
	{
		return clsFac.CuantasFacturaEnContingenciaHay(todas);
	}

	public bool HayFacturasDespuesDeFecha(DateTime fecha)
	{
		if (clsFac.HayFacturasDespuesDeFecha(fecha))
		{
			return true;
		}
		return false;
	}

	public bool buscarPorNro(int nro)
	{
		clsFac._NroFactura = nro;
		clsFac.buscarPorNro();
		if (clsFac._FacturaID > 0)
		{
			return true;
		}
		return false;
	}

	public bool buscarPorVisitaID(int visitaID, ref bool fact2)
	{
		clsFac._VisitaID = visitaID;
		clsFac.buscarPorVisitaID(ref fact2);
		if (clsFac._FacturaID > 0)
		{
			return true;
		}
		return false;
	}

	public bool buscarPorVisitaIDConAnulada(int visitaID, ref bool fact2)
	{
		clsFac._VisitaID = visitaID;
		clsFac.buscarPorVisitaIDConAnulada(ref fact2);
		if (clsFac._FacturaID > 0)
		{
			return true;
		}
		return false;
	}

	public void EmailEnviado()
	{
		clsFac.EmailEnviado();
	}

	public void setcodigoRecepcion(string codigoRecepcion)
	{
		clsFac.setCodigoRecepcion(codigoRecepcion);
	}

	public void setEstado(int estado)
	{
		clsFac.setEstado(estado);
	}

	public int getEstado()
	{
		clsFac.getEstado();
		return clsFac._EstadoSiat;
	}

	public void setContingenciaID(int cotinID)
	{
		clsFac.setContingenciaID(cotinID);
	}

	public DataTable CargarNombreNitxNombreClientes(string Nombre)
	{
		return clsFac.CargarNombreNitxNombreClientes(Nombre);
	}

	public string getNombrePorNIT(string nit)
	{
		if ((Operators.CompareString(nit, "", TextCompare: false) == 0) | (Operators.CompareString(nit, VariableGeneral._sinNit2, TextCompare: false) == 0))
		{
			return VariableGeneral._SinNombre2;
		}
		clsFac._NIT = nit;
		clsFac.getNombrePorNIT();
		if (clsFac._Nombre.Length == 0)
		{
			ctlClientes ctlClientes2 = new ctlClientes();
			clsFacturas obj = clsFac;
			string cI = nit.ToString();
			string celular = "";
			string correo = "";
			obj._Nombre = ctlClientes2.NombreFacturaXci(cI, ref celular, ref correo);
		}
		return clsFac._Nombre;
	}

	public void getUltimoNombreEmailTelefonoPorNIT(string nit, ref string Nombre, ref string email, ref string telefono, ref int tipoDocumentoID)
	{
		if ((Operators.CompareString(nit, "", TextCompare: false) == 0) | (Operators.CompareString(nit, VariableGeneral._sinNit2, TextCompare: false) == 0))
		{
			email = "";
			telefono = "";
			Nombre = VariableGeneral._SinNombre2;
			tipoDocumentoID = 1;
		}
		clsFac._NIT = nit;
		clsFac.getUltimoNombreEmailTelefonoPorNIT();
		if (clsFac._Nombre.Length == 0)
		{
			ctlClientes ctlClientes2 = new ctlClientes();
			clsFac._Nombre = ctlClientes2.NombreFacturaXci(nit.ToString(), ref telefono, ref email);
			return;
		}
		Nombre = clsFac._Nombre;
		email = clsFac._Correo;
		telefono = clsFac._Telefono;
		tipoDocumentoID = clsFac._TipoDocumentoID;
	}

	public void getUltimoNombreEmailNITPorCelular(string telefono, ref string Nombre, ref string email, ref string nit, ref int tipoDocumentoID)
	{
		if (Operators.CompareString(telefono, "", TextCompare: false) == 0)
		{
			email = "";
			nit = VariableGeneral._sinNit2;
			Nombre = VariableGeneral._SinNombre2;
			tipoDocumentoID = 1;
			return;
		}
		clsFac._Telefono = telefono;
		clsFac.getUltimoNombreEmailNITPorCelular();
		if (clsFac._Nombre.Length == 0)
		{
			ctlClientes ctlClientes2 = new ctlClientes();
			clsFac._Nombre = ctlClientes2.NombreFacturaXci(nit.ToString(), ref telefono, ref email);
			return;
		}
		Nombre = clsFac._Nombre;
		email = clsFac._Correo;
		nit = clsFac._NIT;
		tipoDocumentoID = clsFac._TipoDocumentoID;
	}

	public string getCumpleañosPorNIT(string nit)
	{
		if ((Operators.CompareString(nit, "", TextCompare: false) == 0) | (Operators.CompareString(nit, VariableGeneral._sinNit2, TextCompare: false) == 0))
		{
			return VariableGeneral._SinNombre2;
		}
		clsFac._NIT = nit;
		clsFac.getCumpleañosPorNIT();
		return clsFac._Aux;
	}

	public string getNitPorNombre(string nombre)
	{
		clsFac._Nombre = nombre;
		clsFac.getNitPorNombre();
		return clsFac._NIT;
	}

	public string UpdateDescuento(double descto)
	{
		return Conversions.ToString(clsFac.UpdateDescuento(descto));
	}

	public bool InsertarFactura(string NIT, string Nombre, DateTime FechaEmision, int NroFactura, string Codigo, double Monto, double MontoICE, int VisitaID, int CodigoID, string Aux, int AgruparPagoID, double descto, bool factura2, double montoGiftCard, string Correo, string Complemento, string telefono, int leyID, int tipoDocumentoID, int FueraLineaID, int CUFDid, int documentoSector)
	{
		if (descto < 0.0)
		{
			descto = 0.0;
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
		{
			Nombre = Nombre.ToUpper();
		}
		clsFac._NIT = NIT;
		clsFac._Nombre = Nombre.Replace("'", "`");
		clsFac._FechaEmision = FechaEmision;
		clsFac._NroFactura = NroFactura;
		clsFac._Codigo = Codigo;
		clsFac._Monto = Monto;
		clsFac._MontoICE = MontoICE;
		clsFac._MontoGiftCard = montoGiftCard;
		clsFac._VisitaID = VisitaID;
		clsFac._CodigoID = CodigoID;
		clsFac._Aux = Aux;
		clsFac._Correo = Correo;
		clsFac._AgruparPagoID = AgruparPagoID;
		clsFac._DocumentoSector = documentoSector;
		if (Complemento.Length > 5)
		{
			clsFac._Complemento = Complemento.Substring(0, 5);
		}
		else
		{
			clsFac._Complemento = Complemento;
		}
		clsFac._TipoDocumentoID = tipoDocumentoID;
		clsFac._leyID = leyID;
		clsFac._Telefono = telefono;
		clsFac._FueraLineaID = FueraLineaID;
		clsFac._CUFDid = CUFDid;
		if (clsFac._FacturaID == 0)
		{
			return clsFac.Insertar(descto, factura2);
		}
		return clsFac.Modificar();
	}

	public bool ModificarFactura(long NIT, string Nombre, DateTime FechaEmision, int NroFactura, string Codigo, double Monto, double MontoICE, int VisitaID, int CodigoID, string Aux, int AgruparPagoID, double descto, bool factura2, double montoGiftCard, string Correo, string Complemento)
	{
		if (descto < 0.0)
		{
			descto = 0.0;
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
		{
			Nombre = Nombre.ToUpper();
		}
		clsFac._NIT = Conversions.ToString(NIT);
		clsFac._Nombre = Nombre;
		clsFac._FechaEmision = FechaEmision;
		clsFac._NroFactura = NroFactura;
		clsFac._Codigo = Codigo;
		clsFac._Monto = Monto;
		clsFac._MontoICE = MontoICE;
		clsFac._MontoGiftCard = montoGiftCard;
		clsFac._VisitaID = VisitaID;
		clsFac._CodigoID = CodigoID;
		clsFac._Aux = Aux;
		clsFac._Correo = Correo;
		clsFac._AgruparPagoID = AgruparPagoID;
		clsFac._Complemento = Complemento;
		return clsFac.Modificar();
	}

	public void eliminarFactura()
	{
		clsFac.eliminarFactura();
	}

	public DataTable devolverReporteFactura(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool pagoCon, bool consolidado, bool noEnSIAT)
	{
		return clsFac.devolverReporteFactura(desde, hasta, chbNombre, chbSnimbre, chbAnulado, pagoCon, consolidado, noEnSIAT);
	}

	public DataTable devolverReporteFacturaOdonto(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool pagoCon, bool consolidado)
	{
		return clsFac.devolverReporteFactura2(desde, hasta, chbNombre, chbSnimbre, chbAnulado, pagoCon, consolidado);
	}

	public DataTable devolverReporteFacturaLite(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool pagoCon, bool consolidado)
	{
		return clsFac.devolverReporteFacturaLite(desde, hasta, chbNombre, chbSnimbre, chbAnulado, pagoCon, consolidado);
	}

	public DataTable devolverReporteFacturaPorHoras(DateTime desde, DateTime hasta)
	{
		return clsFac.devolverReporteFacturaPorHoras(desde, hasta);
	}

	public DataTable devolverReporteFacturaFormatoImpuestos2022fsv(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool fact2, bool consolidado)
	{
		return clsFac.devolverReporteFacturaFormatoImpuestos2022fsv(desde, hasta, chbNombre, chbSnimbre, chbAnulado, fact2, consolidado);
	}

	public DataTable devolverReporteFacturaFormatoImpuestos2022(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool fact2, bool consolidado)
	{
		return clsFac.devolverReporteFacturaFormatoImpuestos2022(desde, hasta, chbNombre, chbSnimbre, chbAnulado, fact2, consolidado);
	}

	public void SemaforoVentasActuales(ref int cantFac, ref double ventashoy)
	{
		DataTable dataTable = BD.ConsultaVer("count(*), sum(Monto)", "Facturas", "(FechaEmision between " + VariableGeneral.ArmarFecha(DateAndTime.Today) + " and " + VariableGeneral.ArmarFecha(DateAndTime.Now) + ") and Anulada=" + VariableGeneral.armarBolean(0));
		cantFac = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
		ventashoy = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), 0));
	}

	public DataTable DevolverReporte1(DateTime FechaIni, DateTime fechaFin)
	{
		return clsFac.DevoverReporte1(FechaIni, fechaFin);
	}

	public DataTable DevolverReportePorMovimientos(DateTime FechaIni, DateTime fechaFin)
	{
		return clsFac.DevolverReportePorMovimientos(FechaIni, fechaFin);
	}

	public DataTable DevolverReportePorTurnos(DateTime FechaIni, DateTime fechaFin)
	{
		return clsFac.DevolverReportePorTurnos(FechaIni, fechaFin);
	}

	public DataTable devolverFueradeLineaNoEnviada(int ContingenciaID)
	{
		clsFac._FueraLineaID = ContingenciaID;
		return clsFac.devolverFueradeLineaNoEnviada();
	}

	public int VerificarCodigo(int id)
	{
		clsFac._CodigoID = id;
		return clsFac.VerificarCodigo();
	}

	public void getNitNombrePorAux(string aux, ref string nit1, ref string nombre1)
	{
		clsFac._Aux = aux;
		clsFac.getNitNombrePorAux(ref nit1, ref nombre1);
	}

	public void DevolverFacturaXVisita2(ref string nit, ref string Nombre, ref string nrofactura, int visitaID)
	{
		clsFac._VisitaID = visitaID;
		clsFac.DevolverFacturaXVisita();
		nit = clsFac._NIT;
		Nombre = clsFac._Nombre;
		nrofactura = Conversions.ToString(clsFac._NroFactura);
	}

	public DataTable devolverCumpleañerosFactura(string mes)
	{
		if (mes.Length == 1)
		{
			mes = "0" + mes;
		}
		return clsFac.devolverCumpleañerosFactura(mes);
	}
}
