using System;
using System.Collections;
using System.Collections.Generic;
using System.Data;
using System.IO;
using System.Runtime.CompilerServices;
using System.Text;
using System.Text.RegularExpressions;
using System.Xml;
using System.Xml.Linq;
using System.Xml.Schema;
using System.Xml.Serialization;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCreateXMLFactura
{
	public bool crearXMLcompraVentaComputarizada(int Sector, int Modalidad, ref MemoryStream ResultadoStream, ref string strXmlUtf8, string cuf, string cufd, string codigoSuc, string miNit, DateTime fecha, string nombreRazonSocial, string nit, string complemento, double monto, double ICEmonto, int nroFactura, int cajeroID, int visitaId, DataTable dtProdsAFacturar, int AgruparPagoID, bool facturandoAnticipadamente, int codigoPuntoVenta, string enFisicoFolderArchivo, double MontoGiftCard, string cafc, double descuento, int codigoTipoDocumentoIdentidad, bool esContingencia, string ley, bool NitValidado, ref string error1, bool desdeCelular)
	{
		checked
		{
			bool result;
			try
			{
				string empresa = "";
				string sucursal = "";
				string direccion = "";
				string municipio = "";
				string telefono = "";
				string dueño = "";
				string email = "";
				string actividadEconomica = "";
				string comentario = "";
				ctlConfiguraciones obj = new ctlConfiguraciones();
				string ley2 = "";
				bool soloAdminBorra = false;
				bool cant = false;
				int idconfiguracion = default(int);
				bool conporcentaje = default(bool);
				double porcentaje = default(double);
				bool DatosFacturas = default(bool);
				bool DobleFactura = default(bool);
				bool FacturaBackupArchivo = default(bool);
				bool FacturaExpress = default(bool);
				obj.devolver(ref idconfiguracion, ref conporcentaje, ref porcentaje, ref empresa, ref sucursal, ref direccion, ref telefono, ref dueño, ref email, ref actividadEconomica, ref comentario, ref ley2, ref soloAdminBorra, ref cant, ref DatosFacturas, ref DobleFactura, ref FacturaBackupArchivo, ref FacturaExpress, ref municipio);
				int cantDecimales = 2;
				object obj2;
				object obj3;
				object obj5;
				if (Modalidad == 1)
				{
					switch (Sector)
					{
					case 35:
					{
						obj2 = new facturaElectronicaCompraVentaBon();
						NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
						{
							new facturaElectronicaCompraVentaBonCabecera()
						}, null, null);
						obj3 = new List<facturaElectronicaCompraVentaBonDetalle>();
						object obj4 = new facturaElectronicaCompraVentaBonDetalle();
						obj5 = new facturaElectronicaCompraVentaBonDetalle();
						cantDecimales = 5;
						break;
					}
					case 8:
					{
						obj2 = new facturaElectronicaTasaCero();
						NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
						{
							new facturaElectronicaTasaCeroCabecera()
						}, null, null);
						obj3 = new List<facturaElectronicaTasaCeroDetalle>();
						object obj4 = new facturaElectronicaTasaCeroDetalle();
						obj5 = new facturaElectronicaTasaCeroDetalle();
						break;
					}
					case 14:
					{
						obj2 = new facturaElectronicaAlcanzadaIce();
						NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
						{
							new facturaElectronicaAlcanzadaIceCabecera()
						}, null, null);
						obj3 = new List<facturaElectronicaAlcanzadaIceDetalle>();
						object obj4 = new facturaElectronicaAlcanzadaIceDetalle();
						obj5 = new facturaElectronicaAlcanzadaIceDetalle();
						cantDecimales = 5;
						break;
					}
					default:
					{
						obj2 = new facturaElectronicaCompraVenta();
						NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
						{
							new facturaElectronicaCompraVentaCabecera()
						}, null, null);
						obj3 = new List<facturaElectronicaCompraVentaDetalle>();
						object obj4 = new facturaElectronicaCompraVentaDetalle();
						obj5 = new facturaElectronicaCompraVentaDetalle();
						break;
					}
					}
				}
				else
				{
					switch (Sector)
					{
					case 35:
					{
						obj2 = new facturaComputarizadaCompraVentaBon();
						NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
						{
							new facturaComputarizadaCompraVentaBonCabecera()
						}, null, null);
						obj3 = new List<facturaComputarizadaCompraVentaBonDetalle>();
						object obj4 = new facturaComputarizadaCompraVentaBonDetalle();
						obj5 = new facturaComputarizadaCompraVentaBonDetalle();
						cantDecimales = 5;
						break;
					}
					case 14:
					{
						obj2 = new facturaComputarizadaAlcanzadaIce();
						NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
						{
							new facturaComputarizadaAlcanzadaIceCabecera()
						}, null, null);
						obj3 = new List<facturaComputarizadaAlcanzadaIceDetalle>();
						object obj4 = new facturaComputarizadaAlcanzadaIceDetalle();
						obj5 = new facturaComputarizadaAlcanzadaIceDetalle();
						cantDecimales = 5;
						break;
					}
					case 8:
					{
						obj2 = new facturaComputarizadaTasaCero();
						NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
						{
							new facturaComputarizadaTasaCeroCabecera()
						}, null, null);
						obj3 = new List<facturaComputarizadaTasaCeroDetalle>();
						object obj4 = new facturaComputarizadaTasaCeroDetalle();
						obj5 = new facturaComputarizadaTasaCeroDetalle();
						break;
					}
					default:
					{
						obj2 = new facturaComputarizadaCompraVenta();
						NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
						{
							new facturaComputarizadaCompraVentaCabecera()
						}, null, null);
						obj3 = new List<facturaComputarizadaCompraVentaDetalle>();
						object obj4 = new facturaComputarizadaCompraVentaDetalle();
						obj5 = new facturaComputarizadaCompraVentaDetalle();
						break;
					}
					}
				}
				if ((NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null) == null) & !desdeCelular)
				{
					Interaction.MsgBox("No se configuro bien el sector de facturacion en el XML");
					result = false;
				}
				else
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "nitEmisor", new object[1] { miNit }, null, null, OptimisticSet: false, RValueBase: true);
					if (dueño.Length > 0)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "razonSocialEmisor", new object[1] { dueño.Replace("\r\n", " ").Replace("\r", " ").Trim() }, null, null, OptimisticSet: false, RValueBase: true);
					}
					else
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "razonSocialEmisor", new object[1] { empresa.Replace("\r\n", " ").Replace("\r", " ").Trim() }, null, null, OptimisticSet: false, RValueBase: true);
					}
					if (municipio.Length > 0)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "municipio", new object[1] { municipio }, null, null, OptimisticSet: false, RValueBase: true);
					}
					else
					{
						if (!desdeCelular)
						{
							Interaction.MsgBox("Verificar el municipio en las configuraciones");
						}
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "municipio", new object[1] { "Santa Cruz" }, null, null, OptimisticSet: false, RValueBase: true);
					}
					if (telefono.Length == 0)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "telefono", new object[1] { "0" }, null, null, OptimisticSet: false, RValueBase: true);
					}
					else
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "telefono", new object[1] { telefono }, null, null, OptimisticSet: false, RValueBase: true);
					}
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroFactura", new object[1] { nroFactura }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "cuf", new object[1] { cuf }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "cufd", new object[1] { cufd }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoSucursal", new object[1] { codigoSuc }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "direccion", new object[1] { direccion.Replace("\r\n", " ").Replace("\r", " ").Replace("  ", " ")
						.Trim() }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "fechaEmision", new object[1] { VariableGeneral.ArmarFechaSIN(fecha) }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "nombreRazonSocial", new object[1] { nombreRazonSocial }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoTipoDocumentoIdentidad", new object[1] { codigoTipoDocumentoIdentidad }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroDocumento", new object[1] { nit }, null, null, OptimisticSet: false, RValueBase: true);
					if (complemento.Length > 0)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "complemento", new object[1] { complemento }, null, null, OptimisticSet: false, RValueBase: true);
					}
					if (codigoTipoDocumentoIdentidad == 5)
					{
						if (esContingencia)
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoExcepcion", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
						}
						if (!NitValidado)
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoExcepcion", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
						}
						if ((Operators.CompareString(nit, "99001", TextCompare: false) == 0) | (Operators.CompareString(nit, "99002", TextCompare: false) == 0) | (Operators.CompareString(nit, "99003", TextCompare: false) == 0))
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoExcepcion", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
						}
					}
					ctlVisitas obj6 = new ctlVisitas();
					obj6.SetID(visitaId);
					string codigoCliente = obj6.getCodigoCliente();
					if (codigoCliente.ToString().Length > 0)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoCliente", new object[1] { codigoCliente }, null, null, OptimisticSet: false, RValueBase: true);
					}
					else
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoCliente", new object[1] { nit }, null, null, OptimisticSet: false, RValueBase: true);
					}
					ICEmonto = Convert.ToDouble(VariableGeneral.toDecimalSIN(ICEmonto, 2));
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoTotal", new object[1] { VariableGeneral.toDecimalSIN(monto, 2) }, null, null, OptimisticSet: false, RValueBase: true);
					if (Sector == 8)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoTotalSujetoIva", new object[1] { 0 }, null, null, OptimisticSet: false, RValueBase: true);
					}
					else
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoTotalSujetoIva", new object[1] { VariableGeneral.toDecimalSIN(monto - MontoGiftCard, 2) }, null, null, OptimisticSet: false, RValueBase: true);
					}
					if (Sector != 14)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoGiftCard", new object[1] { VariableGeneral.toDecimalSIN(MontoGiftCard, 2) }, null, null, OptimisticSet: false, RValueBase: true);
					}
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMoneda", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoTotalMoneda", new object[1] { VariableGeneral.toDecimalSIN(monto, 2) }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "tipoCambio", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "descuentoAdicional", new object[1] { VariableGeneral.toDecimalSIN(descuento, 2) }, null, null, OptimisticSet: false, RValueBase: true);
					DataTable dataTable = ((visitaId > 0) ? BD.ConsultaVer("Cuentas.CuentaID, sum(pagos.MontoBs) as Monto, max(nroTarjeta) as nroTarjeta, MetodoPagoSIN ", "(Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID)\r\n                        inner join DetalleCuenta on DetalleCuenta.id = Pagos.DetalleCuentaID", "Cuentas.EsGiftCard = " + VariableGeneral.armarBolean(0) + " and DetalleCuenta.VisitaID  = " + Conversions.ToString(visitaId), "Cuentas.CuentaID", "Cuentas.CuentaID,MetodoPagoSIN") : ((AgruparPagoID <= 0) ? new DataTable() : BD.ConsultaVer("Cuentas.CuentaID, sum(pagos.MontoBs) as Monto, max(nroTarjeta) as nroTarjeta, MetodoPagoSIN ", "(Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID)\r\n                        inner join DetalleCuenta on DetalleCuenta.id = Pagos.DetalleCuentaID", "Cuentas.EsGiftCard = " + VariableGeneral.armarBolean(0) + " and Pagos.AgruparPagoID = " + Conversions.ToString(AgruparPagoID), "Cuentas.CuentaID", "Cuentas.CuentaID,MetodoPagoSIN")));
					if (dataTable.Rows.Count == 0)
					{
						if (MontoGiftCard == monto)
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 27 }, null, null, OptimisticSet: false, RValueBase: true);
						}
						else if (MontoGiftCard > 0.0)
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 35 }, null, null, OptimisticSet: false, RValueBase: true);
						}
						else
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
						}
					}
					else if (dataTable.Rows.Count == 1)
					{
						if (Conversions.ToBoolean(Operators.AndObject(MontoGiftCard == 0.0, Operators.CompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MetodoPagoSIN"]), 0), 0, TextCompare: false))))
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { dataTable.Rows[0]["MetodoPagoSIN"] }, null, null, OptimisticSet: false, RValueBase: true);
							if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MetodoPagoSIN"]), 0), 2, TextCompare: false))
							{
								string text = VariableGeneral.armarNumeroTarjetaSin(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["nroTarjeta"]), "")));
								if ((text.Length == 0) | (Operators.CompareString(text, "0000000000000000", TextCompare: false) == 0))
								{
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
								}
								else
								{
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[1] { text }, null, null, OptimisticSet: false, RValueBase: true);
								}
							}
						}
						else if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectEqual(dataTable.Rows[0]["CuentaID"], 1, TextCompare: false), Operators.CompareObjectEqual(dataTable.Rows[0]["CuentaID"], 2, TextCompare: false))))
						{
							if (MontoGiftCard > 0.0)
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 35 }, null, null, OptimisticSet: false, RValueBase: true);
							}
							else
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
							}
						}
						else if (Operators.ConditionalCompareObjectEqual(dataTable.Rows[0]["CuentaID"], 3, TextCompare: false))
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[1] { VariableGeneral.armarNumeroTarjetaSin(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["nroTarjeta"]), ""))) }, null, null, OptimisticSet: false, RValueBase: true);
							if (MontoGiftCard > 0.0)
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 40 }, null, null, OptimisticSet: false, RValueBase: true);
								if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectLessEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[0], null, null, null), null, "Length", new object[0], null, null, null), 1, TextCompare: false), Operators.CompareObjectEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[0], null, null, null), "0000000000000000", TextCompare: false))))
								{
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 35 }, null, null, OptimisticSet: false, RValueBase: true);
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[1] { "0" }, null, null, OptimisticSet: false, RValueBase: true);
								}
							}
							else
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 2 }, null, null, OptimisticSet: false, RValueBase: true);
								if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectLessEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[0], null, null, null), null, "Length", new object[0], null, null, null), 1, TextCompare: false), Operators.CompareObjectEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[0], null, null, null), "0000000000000000", TextCompare: false))))
								{
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[1] { "0" }, null, null, OptimisticSet: false, RValueBase: true);
								}
							}
						}
						else if (MontoGiftCard == monto)
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 27 }, null, null, OptimisticSet: false, RValueBase: true);
						}
						else if (MontoGiftCard > 0.0)
						{
							if (Operators.ConditionalCompareObjectEqual(dataTable.Rows[0]["MetodoPagoSIN"], 7, TextCompare: false))
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 64 }, null, null, OptimisticSet: false, RValueBase: true);
							}
							else
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 35 }, null, null, OptimisticSet: false, RValueBase: true);
							}
						}
						else
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { dataTable.Rows[0]["MetodoPagoSIN"] }, null, null, OptimisticSet: false, RValueBase: true);
						}
					}
					else if (dataTable.Rows.Count == 2)
					{
						if (Conversions.ToBoolean(Operators.AndObject(Operators.OrObject(Operators.CompareObjectEqual(dataTable.Rows[0]["CuentaID"], 1, TextCompare: false), Operators.CompareObjectEqual(dataTable.Rows[1]["CuentaID"], 2, TextCompare: false)), Operators.CompareObjectEqual(dataTable.Rows[1]["CuentaID"], 3, TextCompare: false))))
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[1] { VariableGeneral.armarNumeroTarjetaSin(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[1]["nroTarjeta"]), ""))) }, null, null, OptimisticSet: false, RValueBase: true);
							if (MontoGiftCard > 0.0)
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 86 }, null, null, OptimisticSet: false, RValueBase: true);
							}
							else
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 10 }, null, null, OptimisticSet: false, RValueBase: true);
								if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectLessEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[0], null, null, null), null, "Length", new object[0], null, null, null), 1, TextCompare: false), Operators.CompareObjectEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[0], null, null, null), "0000000000000000", TextCompare: false))))
								{
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[1] { "0" }, null, null, OptimisticSet: false, RValueBase: true);
								}
							}
						}
						else if (Conversions.ToBoolean(Operators.AndObject(Operators.CompareObjectEqual(dataTable.Rows[0]["CuentaID"], 3, TextCompare: false), Operators.OrObject(Operators.CompareObjectEqual(dataTable.Rows[1]["CuentaID"], 1, TextCompare: false), Operators.CompareObjectEqual(dataTable.Rows[1]["CuentaID"], 2, TextCompare: false)))))
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[1] { VariableGeneral.armarNumeroTarjetaSin(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["nroTarjeta"]), ""))) }, null, null, OptimisticSet: false, RValueBase: true);
							if (MontoGiftCard > 0.0)
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 86 }, null, null, OptimisticSet: false, RValueBase: true);
							}
							else
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 10 }, null, null, OptimisticSet: false, RValueBase: true);
								if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectLessEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[0], null, null, null), null, "Length", new object[0], null, null, null), 1, TextCompare: false), Operators.CompareObjectEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[0], null, null, null), "0000000000000000", TextCompare: false))))
								{
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
									NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[1] { "0" }, null, null, OptimisticSet: false, RValueBase: true);
								}
							}
						}
						else
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
						}
					}
					else if (MontoGiftCard > 0.0)
					{
						if (Operators.ConditionalCompareObjectEqual(dataTable.Rows[0]["MetodoPagoSIN"], 7, TextCompare: false))
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 64 }, null, null, OptimisticSet: false, RValueBase: true);
						}
						else
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 35 }, null, null, OptimisticSet: false, RValueBase: true);
						}
					}
					else
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
					}
					if (Conversions.ToBoolean(Operators.AndObject(Operators.CompareObjectEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroTarjeta", new object[0], null, null, null), "0", TextCompare: false), Operators.CompareObjectEqual(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[0], null, null, null), 2, TextCompare: false))))
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoMetodoPago", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
					}
					if (cafc.Length > 0)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "cafc", new object[1] { cafc }, null, null, OptimisticSet: false, RValueBase: true);
					}
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoPuntoVenta", new object[1] { codigoPuntoVenta }, null, null, OptimisticSet: false, RValueBase: true);
					if (ley.Length == 0)
					{
						ctlFactElectLeyes obj7 = new ctlFactElectLeyes();
						int leyId = 0;
						obj7.DevolverRandomLey("0", ref leyId, ref ley, Modalidad);
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "leyenda", new object[1] { ley.Replace("\r\n", " ").Replace("\r", " ") }, null, null, OptimisticSet: false, RValueBase: true);
					}
					else
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "leyenda", new object[1] { ley.Replace("\r\n", " ").Replace("\r", " ") }, null, null, OptimisticSet: false, RValueBase: true);
					}
					if (cajeroID > 0)
					{
						ctlMeseros ctlMeseros2 = new ctlMeseros();
						ctlMeseros2.SetMeseroID(cajeroID);
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "usuario", new object[1] { ctlMeseros2.devolverNombre() }, null, null, OptimisticSet: false, RValueBase: true);
						if (NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "usuario", new object[0], null, null, null).ToString().Length == 0)
						{
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "usuario", new object[1] { "Restotech" }, null, null, OptimisticSet: false, RValueBase: true);
						}
					}
					else
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "usuario", new object[1] { "Restotech" }, null, null, OptimisticSet: false, RValueBase: true);
					}
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoDocumentoSector", new object[1] { Sector }, null, null, OptimisticSet: false, RValueBase: true);
					DataTable dataTable2 = new DataTable();
					if (dtProdsAFacturar != null)
					{
						dataTable2 = ((AgruparPagoID <= 0) ? dtProdsAFacturar : new ctlDetalleCuenta().ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(AgruparPagoID, Sector));
					}
					else
					{
						ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
						if (visitaId > 0)
						{
							dataTable2 = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionXML(visitaId, esContingencia, paraXml: true, Sector);
							if (dataTable2.Rows.Count == 0 && esContingencia)
							{
								dataTable2 = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionConBorrados(visitaId, Sector);
								double num = 0.0;
								int leyId = dataTable2.Rows.Count - 1;
								for (int i = 0; i <= leyId; i++)
								{
									dataTable2.Rows[i]["pago"] = Operators.MultiplyObject(dataTable2.Rows[i]["Precio"], dataTable2.Rows[i]["Cantidad"]);
									dataTable2.Rows[i]["Debe"] = 0;
									num = Conversions.ToDouble(Operators.AddObject(num, dataTable2.Rows[i]["pago"]));
								}
								if (monto != num)
								{
									error1 = error1 + "Hay un error entre los items y el monto total de la factura nro " + Conversions.ToString(nroFactura);
								}
							}
						}
						else if (AgruparPagoID > 0)
						{
							dataTable2 = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(AgruparPagoID, Sector);
						}
					}
					double num2 = new clsPropinas().devolverPropinasXvisitaID(visitaId);
					double num3 = 0.0;
					double num4 = 0.0;
					double num5 = 0.0;
					double num6 = 0.0;
					int num7 = dataTable2.Rows.Count - 1;
					int num8 = 0;
					while (true)
					{
						if (num8 <= num7)
						{
							double num9 = 0.0;
							bool flag = false;
							if (AgruparPagoID > 0)
							{
								if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectGreater(Operators.AddObject(dataTable2.Rows[num8]["Debe"], dataTable2.Rows[num8]["Pago"]), 0, TextCompare: false), Operators.CompareObjectGreater(dataTable2.Rows[num8]["Pagando"], 0, TextCompare: false))))
								{
									flag = true;
								}
							}
							else if (Operators.ConditionalCompareObjectGreater(Operators.AddObject(dataTable2.Rows[num8]["Debe"], dataTable2.Rows[num8]["Pago"]), 0, TextCompare: false))
							{
								flag = true;
							}
							if (Sector == 35)
							{
								flag = true;
							}
							else
							{
								_ = !flag & (configuration.gStyleBoliches1 == configuration.styleBolichesId.Aerocruz);
							}
							if (flag)
							{
								if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["precio"]), -1), -1, TextCompare: false))
								{
									ctlProductos ctlProductos2 = new ctlProductos();
									ctlProductos2.ToReturnIdbyName(Conversions.ToString(dataTable2.Rows[num8]["Producto"]));
									ctlProductos2.CargarPrecio();
									dataTable2.Rows[num8]["precio"] = ctlProductos2.getPrecio();
								}
								string text2 = Conversions.ToString(dataTable2.Rows[num8]["Producto"]);
								double num10;
								if (AgruparPagoID > 0)
								{
									if (Operators.ConditionalCompareObjectGreater(dataTable2.Rows[num8]["Pagando"], 0, TextCompare: false))
									{
										num9 = Conversions.ToDouble(dataTable2.Rows[num8]["Pagando"]);
										num10 = Conversions.ToDouble(Operators.DivideObject(Operators.MultiplyObject(dataTable2.Rows[num8]["Pagando"], dataTable2.Rows[num8]["Cantidad"]), dataTable2.Rows[num8]["Pagando"]));
									}
									else
									{
										num9 = 0.0;
										num10 = 0.0;
									}
								}
								else
								{
									num9 = Conversions.ToDouble(Operators.AddObject(dataTable2.Rows[num8]["Debe"], dataTable2.Rows[num8]["Pago"]));
									num10 = Conversions.ToDouble(dataTable2.Rows[num8]["Cantidad"]);
								}
								double num11 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["Precio"]), 0));
								if (Operators.CompareString(text2.ToUpper(), "SERVICIO", TextCompare: false) == 0)
								{
									num11 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["Precio"]), 0));
								}
								if (Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["ActividadSIN"])))
								{
									if (!desdeCelular)
									{
										Interaction.MsgBox("El Producto: " + text2 + ", No tiene bien configurada su actividad SIN, pida a administración que lo arregle");
										result = false;
									}
									else
									{
										result = false;
									}
									break;
								}
								object obj4 = ((Modalidad == 1) ? (Sector switch
								{
									35 => new facturaElectronicaCompraVentaBonDetalle(), 
									8 => new facturaElectronicaTasaCeroDetalle(), 
									14 => new facturaElectronicaAlcanzadaIceDetalle(), 
									_ => new facturaElectronicaCompraVentaDetalle(), 
								}) : (Sector switch
								{
									35 => new facturaComputarizadaCompraVentaBonDetalle(), 
									8 => new facturaComputarizadaTasaCeroDetalle(), 
									14 => new facturaComputarizadaAlcanzadaIceDetalle(), 
									_ => new facturaComputarizadaCompraVentaDetalle(), 
								}));
								NewLateBinding.LateSet(obj4, null, "actividadEconomica", new object[1] { dataTable2.Rows[num8]["ActividadSIN"] }, null, null);
								if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.lavasecoUniversal1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.JESPfacturacion))
								{
									NewLateBinding.LateSet(obj4, null, "codigoProducto", new object[1] { Operators.ConcatenateObject(dataTable2.Rows[num8]["codigo"], VariableGeneral.toDecimalSIN(num11, cantDecimales).ToString().Replace(",", "")
										.Replace(".", "")) }, null, null);
									NewLateBinding.LateSet(obj4, null, "descripcion", new object[1] { CleanInput(text2 + " " + VariableGeneral.toDecimalSIN(num11, cantDecimales)) }, null, null);
								}
								else
								{
									NewLateBinding.LateSet(obj4, null, "codigoProducto", new object[1] { dataTable2.Rows[num8]["codigo"] }, null, null);
									NewLateBinding.LateSet(obj4, null, "descripcion", new object[1] { CleanInput(text2) }, null, null);
								}
								if (NewLateBinding.LateGet(obj4, null, "codigoProducto", new object[0], null, null, null).ToString().Length == 0 && !desdeCelular)
								{
									Interaction.MsgBox(Operators.ConcatenateObject(Operators.ConcatenateObject("El producto ", NewLateBinding.LateGet(obj4, null, "descripcion", new object[0], null, null, null)), " no tiene bien configurado su codigo, avisar al administrador"));
									if (NewLateBinding.LateGet(obj4, null, "descripcion", new object[0], null, null, null).ToString().Length > 3)
									{
										NewLateBinding.LateSet(obj4, null, "codigoProducto", new object[1] { NewLateBinding.LateGet(obj4, null, "descripcion", new object[0], null, null, null).ToString().Substring(0, 3) }, null, null);
									}
									else
									{
										NewLateBinding.LateSet(obj4, null, "codigoProducto", new object[1] { NewLateBinding.LateGet(obj4, null, "descripcion", new object[0], null, null, null) }, null, null);
									}
								}
								NewLateBinding.LateSet(obj4, null, "codigoProductoSin", new object[1] { dataTable2.Rows[num8]["codigoSIN"] }, null, null);
								NewLateBinding.LateSet(obj4, null, "cantidad", new object[1] { VariableGeneral.toDecimalSIN(num10, cantDecimales) }, null, null);
								NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(num11, cantDecimales) }, null, null);
								NewLateBinding.LateSet(obj4, null, "unidadMedida", new object[1] { dataTable2.Rows[num8]["unidadSIN"] }, null, null);
								NewLateBinding.LateSet(obj4, null, "subTotal", new object[1] { VariableGeneral.toDecimalSIN(num9, cantDecimales) }, null, null);
								double num12 = Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.SubtractObject(Convert.ToDouble(VariableGeneral.toDecimalSIN(num10 * num11, cantDecimales)), NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null)), cantDecimales));
								if (num12 >= 0.0)
								{
									NewLateBinding.LateSet(obj4, null, "montoDescuento", new object[1] { num12 }, null, null);
								}
								else
								{
									double num13 = Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.DivideObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), cantDecimales));
									if (num13 <= 0.0)
									{
										num13 = 0.01;
									}
									object instance = obj4;
									NewLateBinding.LateSet(instance, null, "precioUnitario", new object[1] { Operators.AddObject(NewLateBinding.LateGet(instance, null, "precioUnitario", new object[0], null, null, null), num13) }, null, null);
									NewLateBinding.LateSet(obj4, null, "montoDescuento", new object[1] { VariableGeneral.toDecimalSIN(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null)), cantDecimales) }, null, null);
								}
								if (decimal.Compare(VariableGeneral.toDecimalSIN(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null)), cantDecimales), VariableGeneral.toDecimalSIN(Operators.AddObject(NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), cantDecimales)) != 0)
								{
									NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(Operators.DivideObject(Operators.AddObject(NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), cantDecimales) }, null, null);
									if (decimal.Compare(VariableGeneral.toDecimalSIN(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null)), cantDecimales), VariableGeneral.toDecimalSIN(Operators.AddObject(NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), cantDecimales)) != 0)
									{
										double num14 = Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.DivideObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), cantDecimales));
										if (num14 <= 0.0)
										{
											num14 = 0.01;
										}
										object instance = obj4;
										NewLateBinding.LateSet(instance, null, "precioUnitario", new object[1] { Operators.AddObject(NewLateBinding.LateGet(instance, null, "precioUnitario", new object[0], null, null, null), num14) }, null, null);
										NewLateBinding.LateSet(obj4, null, "montoDescuento", new object[1] { VariableGeneral.toDecimalSIN(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null)), cantDecimales) }, null, null);
									}
								}
								try
								{
									double num15;
									if (Sector == 14)
									{
										if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["ICE_Fijo"]), 0), 0, TextCompare: false), Operators.CompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["ICE_porcentual"]), 0), 0, TextCompare: false))))
										{
											NewLateBinding.LateSet(obj4, null, "marcaIce", new object[1] { 1 }, null, null);
											NewLateBinding.LateSet(obj4, null, "cantidadIce", new object[1] { VariableGeneral.toDecimalSIN(Operators.DivideObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["CantidadML"]), 0)), 1000), cantDecimales) }, null, null);
											NewLateBinding.LateSet(obj4, null, "montoIceEspecifico", new object[1] { VariableGeneral.toDecimalSIN(Convert.ToDouble(Operators.MultiplyObject(dataTable2.Rows[num8]["ICE_Fijo"], NewLateBinding.LateGet(obj4, null, "cantidadIce", new object[0], null, null, null))), cantDecimales) }, null, null);
											num15 = Convert.ToDouble(RuntimeHelpers.GetObjectValue(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["ICE_porcentual"]), 0)));
											if (num15 >= 1.0)
											{
												num15 /= 100.0;
												if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null), 0, TextCompare: false))
												{
													NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { num11 }, null, null);
													decimal num16 = 0.13m;
													decimal d = 0.01m;
													decimal d2 = Conversions.ToDecimal(NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null));
													decimal num17 = Conversions.ToDecimal(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null));
													int num18 = 0;
													while (true)
													{
														if (num18 >= 100)
														{
															error1 = "No consiguio precio";
															result = false;
															break;
														}
														NewLateBinding.LateSet(obj4, null, "alicuotaIva", new object[1] { VariableGeneral.toDecimalSIN(Operators.MultiplyObject(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), num17), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), num16), cantDecimales) }, null, null);
														NewLateBinding.LateSet(obj4, null, "precioNetoVentaIce", new object[1] { VariableGeneral.toDecimalSIN(Operators.SubtractObject(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), num17), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "alicuotaIva", new object[0], null, null, null)), cantDecimales) }, null, null);
														NewLateBinding.LateSet(obj4, null, "montoIcePorcentual", new object[1] { VariableGeneral.toDecimalSIN(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "precioNetoVentaIce", new object[0], null, null, null), num15), cantDecimales) }, null, null);
														decimal d3 = VariableGeneral.toDecimalSIN(Operators.AddObject(Operators.AddObject(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), num17), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "montoIceEspecifico", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "montoIcePorcentual", new object[0], null, null, null)), cantDecimales);
														decimal num19 = decimal.Subtract(d2, d3);
														num17 = Conversions.ToDecimal(Operators.AddObject(num17, Operators.DivideObject(num19, NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null))));
														if (decimal.Compare(Math.Abs(num19), d) > 0)
														{
															continue;
														}
														NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(num17, cantDecimales) }, null, null);
														goto IL_2d53;
													}
													break;
												}
												NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(Operators.DivideObject(Operators.SubtractObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), Operators.DivideObject(NewLateBinding.LateGet(obj4, null, "montoIceEspecifico", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null))), 0.87 * num15 + 1.0), cantDecimales) }, null, null);
											}
											else
											{
												NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(Operators.DivideObject(Operators.SubtractObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), Operators.DivideObject(NewLateBinding.LateGet(obj4, null, "montoIceEspecifico", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null))), 0.87 * num15 + 1.0), cantDecimales) }, null, null);
											}
											goto IL_2d53;
										}
										NewLateBinding.LateSet(obj4, null, "marcaIce", new object[1] { 2 }, null, null);
									}
									goto end_IL_2788;
									IL_2d53:
									NewLateBinding.LateSet(obj4, null, "alicuotaIva", new object[1] { VariableGeneral.toDecimalSIN(Operators.MultiplyObject(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), 0.13), cantDecimales) }, null, null);
									NewLateBinding.LateSet(obj4, null, "precioNetoVentaIce", new object[1] { VariableGeneral.toDecimalSIN(Operators.SubtractObject(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "alicuotaIva", new object[0], null, null, null)), cantDecimales) }, null, null);
									NewLateBinding.LateSet(obj4, null, "alicuotaEspecifica", new object[1] { VariableGeneral.toDecimalSIN(Convert.ToDouble(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["ICE_Fijo"])), cantDecimales) }, null, null);
									NewLateBinding.LateSet(obj4, null, "alicuotaPorcentual", new object[1] { VariableGeneral.toDecimalSIN(num15, cantDecimales) }, null, null);
									NewLateBinding.LateSet(obj4, null, "montoIcePorcentual", new object[1] { VariableGeneral.toDecimalSIN(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "precioNetoVentaIce", new object[0], null, null, null), num15), cantDecimales) }, null, null);
									num3 = Conversions.ToDouble(Operators.AddObject(num3, NewLateBinding.LateGet(obj4, null, "montoIcePorcentual", new object[0], null, null, null)));
									num4 = Conversions.ToDouble(Operators.AddObject(num4, NewLateBinding.LateGet(obj4, null, "montoIceEspecifico", new object[0], null, null, null)));
									NewLateBinding.LateSet(obj4, null, "subTotal", new object[1] { VariableGeneral.toDecimalSIN(Operators.AddObject(Operators.AddObject(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "montoIceEspecifico", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "montoIcePorcentual", new object[0], null, null, null)), cantDecimales) }, null, null);
									if (decimal.Compare(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null)), 2), VariableGeneral.toDecimalSIN(num9, 2)) != 0)
									{
										Interaction.MsgBox("no deberia haber dif ");
									}
									end_IL_2788:;
								}
								catch (Exception ex)
								{
									ProjectData.SetProjectError(ex);
									Exception ex2 = ex;
									ProjectData.ClearProjectError();
								}
								try
								{
									if ((Sector == 1) | (Sector == 35))
									{
										if (Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["ManejaSerie"]), false)) && dataTable2.Rows[num8]["Observacion"].ToString().Length > 0)
										{
											NewLateBinding.LateSet(obj4, null, "numeroSerie", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["Observacion"]), "") }, null, null);
										}
										if (Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["ManejaImei"]), false)) && dataTable2.Rows[num8]["Observacion"].ToString().Length > 0)
										{
											NewLateBinding.LateSet(obj4, null, "numeroImei", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num8]["Observacion"]), "") }, null, null);
										}
									}
								}
								catch (Exception ex3)
								{
									ProjectData.SetProjectError(ex3);
									Exception ex4 = ex3;
									ProjectData.ClearProjectError();
								}
								num5 = Conversions.ToDouble(Operators.AddObject(num5, NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null)));
								if (Operators.ConditionalCompareObjectEqual(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), 0, TextCompare: false))
								{
									NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { 1 }, null, null);
								}
								if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), 0, TextCompare: false))
								{
									object instance2 = obj3;
									object[] obj8 = new object[1] { obj4 };
									object[] array = obj8;
									bool[] obj9 = new bool[1] { true };
									bool[] array2 = obj9;
									NewLateBinding.LateCall(instance2, null, "Add", obj8, null, null, obj9, IgnoreReturn: true);
									if (array2[0])
									{
										obj4 = RuntimeHelpers.GetObjectValue(array[0]);
									}
								}
							}
							num8++;
							continue;
						}
						if (Sector == 14)
						{
							if (num3 > 0.0)
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoIcePorcentual", new object[1] { VariableGeneral.toDecimalSIN(num3, 2) }, null, null, OptimisticSet: false, RValueBase: true);
							}
							if (num4 > 0.0)
							{
								NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoIceEspecifico", new object[1] { VariableGeneral.toDecimalSIN(num4, 2) }, null, null, OptimisticSet: false, RValueBase: true);
							}
							NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoTotalSujetoIva", new object[1] { VariableGeneral.toDecimalSIN(Operators.SubtractObject(Operators.SubtractObject(NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoTotalSujetoIva", new object[0], null, null, null), NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoIcePorcentual", new object[0], null, null, null)), NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoIceEspecifico", new object[0], null, null, null)), 2) }, null, null, OptimisticSet: false, RValueBase: true);
						}
						if (num6 > 0.0)
						{
							foreach (object item in (IEnumerable)obj3)
							{
								object objectValue = RuntimeHelpers.GetObjectValue(item);
								if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateGet(objectValue, null, "subTotal", new object[0], null, null, null), num6, TextCompare: false))
								{
									object instance = objectValue;
									NewLateBinding.LateSet(instance, null, "subTotal", new object[1] { Operators.SubtractObject(NewLateBinding.LateGet(instance, null, "subTotal", new object[0], null, null, null), num6) }, null, null);
									instance = objectValue;
									NewLateBinding.LateSet(instance, null, "montoDescuento", new object[1] { Operators.AddObject(NewLateBinding.LateGet(instance, null, "montoDescuento", new object[0], null, null, null), num6) }, null, null);
									num6 = 0.0;
									break;
								}
							}
						}
						if (num2 > 0.0)
						{
							ctlProductos obj10 = new ctlProductos();
							string Codigo = "";
							string Nombre = "";
							string CodigoSIN = "";
							string UnidadSINstr = "";
							int UnidadSIN = 0;
							string ActividadSIN = "";
							obj10.SetProductoID(1);
							obj10.cargarDatosSIN(ref Codigo, ref Nombre, ref CodigoSIN, ref UnidadSINstr, ref UnidadSIN, ref ActividadSIN);
							NewLateBinding.LateSet(obj5, null, "actividadEconomica", new object[1] { (Operators.CompareString(ActividadSIN, "", TextCompare: false) == 0) ? NewLateBinding.LateGet(NewLateBinding.LateIndexGet(obj3, new object[1] { 0 }, null), null, "actividadEconomica", new object[0], null, null, null) : ActividadSIN }, null, null);
							NewLateBinding.LateSet(obj5, null, "codigoProductoSin", new object[1] { (Operators.CompareString(CodigoSIN, "", TextCompare: false) == 0) ? NewLateBinding.LateGet(NewLateBinding.LateIndexGet(obj3, new object[1] { 0 }, null), null, "codigoProductoSin", new object[0], null, null, null) : CodigoSIN }, null, null);
							NewLateBinding.LateSet(obj5, null, "unidadMedida", new object[1] { (UnidadSIN == 0) ? NewLateBinding.LateGet(NewLateBinding.LateIndexGet(obj3, new object[1] { 0 }, null), null, "unidadMedida", new object[0], null, null, null) : ((object)UnidadSIN) }, null, null);
							NewLateBinding.LateSet(obj5, null, "codigoProducto", new object[1] { Codigo }, null, null);
							NewLateBinding.LateSet(obj5, null, "cantidad", new object[1] { 1 }, null, null);
							NewLateBinding.LateSet(obj5, null, "descripcion", new object[1] { Nombre }, null, null);
							NewLateBinding.LateSet(obj5, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(num2, cantDecimales) }, null, null);
							NewLateBinding.LateSet(obj5, null, "montoDescuento", new object[1] { 0 }, null, null);
							NewLateBinding.LateSet(obj5, null, "subTotal", new object[1] { VariableGeneral.toDecimalSIN(num2, cantDecimales) }, null, null);
							object[] array;
							bool[] array2;
							NewLateBinding.LateCall(obj3, null, "Add", array = new object[1] { obj5 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
							if (array2[0])
							{
								obj5 = RuntimeHelpers.GetObjectValue(array[0]);
							}
						}
						NewLateBinding.LateSet(obj2, null, "detalle", new object[1] { NewLateBinding.LateGet(obj3, null, "ToArray", new object[0], null, null, null) }, null, null);
						XmlDocument doc = new XmlDocument();
						doc.PreserveWhitespace = true;
						ctlFacturaElectronicaAlgoritmo ctlFacturaElectronicaAlgoritmo2 = new ctlFacturaElectronicaAlgoritmo();
						doc.LoadXml(SerializeObjectToXmlString(RuntimeHelpers.GetObjectValue(obj2)));
						if (Modalidad == 1)
						{
							DateTime Expiracion = DateAndTime.Now;
							if (!ctlFacturaElectronicaAlgoritmo2.FirmarXML(ref doc, ref error1, ref Expiracion, desdeCelular))
							{
								error1 = error1 + "error firmando.\r\n" + error1;
								result = false;
								break;
							}
						}
						strXmlUtf8 = doc.OuterXml;
						Encoding encoding = new UTF8Encoding(encoderShouldEmitUTF8Identifier: false);
						string text3 = "";
						if (desdeCelular)
						{
							text3 = "c:\\Restotech\\";
						}
						if (File.Exists(text3 + "Xml\\Factura.xml"))
						{
							File.Delete(text3 + "Xml\\Factura.xml");
						}
						StreamWriter streamWriter = new StreamWriter(text3 + "Xml\\Factura.xml", append: false, encoding, 1024);
						doc.Save(streamWriter);
						streamWriter.Close();
						streamWriter.Dispose();
						if (enFisicoFolderArchivo.Length > 0)
						{
							StreamWriter streamWriter2 = new StreamWriter(text3 + "Xml\\" + enFisicoFolderArchivo + ".xml", append: false, encoding, 1024);
							doc.Save(streamWriter2);
							streamWriter2.Close();
							streamWriter2.Dispose();
						}
						ResultadoStream = new MemoryStream();
						StreamWriter streamWriter3 = new StreamWriter(ResultadoStream, encoding, 1024, leaveOpen: true);
						doc.Save(streamWriter3);
						streamWriter3.Close();
						streamWriter3.Dispose();
						result = true;
						break;
					}
				}
			}
			catch (Exception ex5)
			{
				ProjectData.SetProjectError(ex5);
				Exception ex6 = ex5;
				error1 += ex6.Message;
				result = false;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public bool crearXMLnotaDebitoCredito(int DocumentoSectorFactura, int Modalidad, ref MemoryStream ResultadoStream, ref string strXmlUtf8, string cuf, string cufFactura, string cufd, string codigoSuc, string miNit, DateTime fechaFactura, DateTime fechaEmision, string nombreRazonSocial, string nit, string complemento, double montoTotalOriginal, double montoTotalDevuelto, int nroFacturaOriginal, int NroNotaDebito, int cajeroID, int visitaId, DataTable dtProdsAFacturar, int AgruparPagoID, int codigoPuntoVenta, string enFisicoFolderArchivo, double descuento, int codigoTipoDocumentoIdentidad, bool esContingencia, string ley, bool NitValidado, int debitoID, ref string error1)
	{
		bool result;
		try
		{
			string empresa = "";
			string sucursal = "";
			string direccion = "";
			string municipio = "";
			string telefono = "";
			string dueño = "";
			string email = "";
			string actividadEconomica = "";
			string comentario = "";
			ctlConfiguraciones obj = new ctlConfiguraciones();
			string ley2 = "";
			bool soloAdminBorra = false;
			bool cant = false;
			int idconfiguracion = default(int);
			bool conporcentaje = default(bool);
			double porcentaje = default(double);
			bool DatosFacturas = default(bool);
			bool DobleFactura = default(bool);
			bool FacturaBackupArchivo = default(bool);
			bool FacturaExpress = default(bool);
			obj.devolver(ref idconfiguracion, ref conporcentaje, ref porcentaje, ref empresa, ref sucursal, ref direccion, ref telefono, ref dueño, ref email, ref actividadEconomica, ref comentario, ref ley2, ref soloAdminBorra, ref cant, ref DatosFacturas, ref DobleFactura, ref FacturaBackupArchivo, ref FacturaExpress, ref municipio);
			int cantDecimales = 2;
			object obj2;
			object obj3;
			object obj5;
			if (Modalidad == 1)
			{
				obj2 = new notaFiscalElectronicaCreditoDebito();
				NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
				{
					new notaFiscalElectronicaCreditoDebitoCabecera()
				}, null, null);
				obj3 = new List<notaFiscalElectronicaCreditoDebitoDetalle>();
				object obj4 = new notaFiscalElectronicaCreditoDebitoDetalle();
				obj5 = new notaFiscalElectronicaCreditoDebitoDetalle();
			}
			else
			{
				obj2 = new notaFiscalComputarizadaCreditoDebito();
				NewLateBinding.LateSet(obj2, null, "cabecera", new object[1]
				{
					new notaFiscalComputarizadaCreditoDebitoCabecera()
				}, null, null);
				obj3 = new List<notaFiscalComputarizadaCreditoDebitoDetalle>();
				object obj4 = new notaFiscalComputarizadaCreditoDebitoDetalle();
				obj5 = new notaFiscalComputarizadaCreditoDebitoDetalle();
			}
			if (NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null) == null)
			{
				Interaction.MsgBox("No se configuro bien el sector de facturacion en el XML");
				result = false;
			}
			else
			{
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "nitEmisor", new object[1] { miNit }, null, null, OptimisticSet: false, RValueBase: true);
				if (dueño.Length > 0)
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "razonSocialEmisor", new object[1] { dueño.Replace("\r\n", " ").Replace("\r", " ").Trim() }, null, null, OptimisticSet: false, RValueBase: true);
				}
				else
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "razonSocialEmisor", new object[1] { empresa.Replace("\r\n", " ").Replace("\r", " ").Trim() }, null, null, OptimisticSet: false, RValueBase: true);
				}
				if (municipio.Length > 0)
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "municipio", new object[1] { municipio }, null, null, OptimisticSet: false, RValueBase: true);
				}
				else
				{
					Interaction.MsgBox("Verificar el municipio en las configuraciones");
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "municipio", new object[1] { "Santa Cruz" }, null, null, OptimisticSet: false, RValueBase: true);
				}
				if (telefono.Length == 0)
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "telefono", new object[1] { "0" }, null, null, OptimisticSet: false, RValueBase: true);
				}
				else
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "telefono", new object[1] { telefono }, null, null, OptimisticSet: false, RValueBase: true);
				}
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroFactura", new object[1] { nroFacturaOriginal }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroNotaCreditoDebito", new object[1] { NroNotaDebito }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "cuf", new object[1] { cuf }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroAutorizacionCuf", new object[1] { cufFactura }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "cufd", new object[1] { cufd }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoSucursal", new object[1] { codigoSuc }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "direccion", new object[1] { direccion.Replace("\r\n", " ").Replace("\r", " ").Replace("  ", " ")
					.Trim() }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "fechaEmision", new object[1] { VariableGeneral.ArmarFechaSIN(fechaEmision) }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "fechaEmisionFactura", new object[1] { VariableGeneral.ArmarFechaSIN(fechaFactura) }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "nombreRazonSocial", new object[1] { nombreRazonSocial }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoTipoDocumentoIdentidad", new object[1] { codigoTipoDocumentoIdentidad }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "numeroDocumento", new object[1] { nit }, null, null, OptimisticSet: false, RValueBase: true);
				if (complemento.Length > 0)
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "complemento", new object[1] { complemento }, null, null, OptimisticSet: false, RValueBase: true);
				}
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoExcepcion", new object[1] { 0 }, null, null, OptimisticSet: false, RValueBase: true);
				if (codigoTipoDocumentoIdentidad == 5)
				{
					if (esContingencia)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoExcepcion", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
					}
					if (!NitValidado)
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoExcepcion", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
					}
					if ((Operators.CompareString(nit, "99001", TextCompare: false) == 0) | (Operators.CompareString(nit, "99002", TextCompare: false) == 0) | (Operators.CompareString(nit, "99003", TextCompare: false) == 0))
					{
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoExcepcion", new object[1] { 1 }, null, null, OptimisticSet: false, RValueBase: true);
					}
				}
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoTotalOriginal", new object[1] { montoTotalOriginal + descuento }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoTotalDevuelto", new object[1] { montoTotalDevuelto }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoEfectivoCreditoDebito", new object[1] { montoTotalDevuelto * 0.13 }, null, null, OptimisticSet: false, RValueBase: true);
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "montoDescuentoCreditoDebito", new object[1] { 0 }, null, null, OptimisticSet: false, RValueBase: true);
				ctlVisitas obj6 = new ctlVisitas();
				obj6.SetID(visitaId);
				string codigoCliente = obj6.getCodigoCliente();
				if (codigoCliente.ToString().Length > 0)
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoCliente", new object[1] { codigoCliente }, null, null, OptimisticSet: false, RValueBase: true);
				}
				else
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoCliente", new object[1] { nit }, null, null, OptimisticSet: false, RValueBase: true);
				}
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoPuntoVenta", new object[1] { codigoPuntoVenta }, null, null, OptimisticSet: false, RValueBase: true);
				if (ley.Length == 0)
				{
					ctlFactElectLeyes ctlFactElectLeyes2 = new ctlFactElectLeyes();
					clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
					if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
					{
						int leyId = 0;
						ctlFactElectLeyes2.DevolverRandomLey("0", ref leyId, ref ley, (int)clsFactElecConfig2.CodigoAmbiente);
						NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "leyenda", new object[1] { ley.Replace("\r\n", " ").Replace("\r", " ") }, null, null, OptimisticSet: false, RValueBase: true);
					}
				}
				else
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "leyenda", new object[1] { ley.Replace("\r\n", " ").Replace("\r", " ") }, null, null, OptimisticSet: false, RValueBase: true);
				}
				if (cajeroID > 0)
				{
					ctlMeseros ctlMeseros2 = new ctlMeseros();
					ctlMeseros2.SetMeseroID(cajeroID);
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "usuario", new object[1] { ctlMeseros2.devolverNombre() }, null, null, OptimisticSet: false, RValueBase: true);
				}
				if (NewLateBinding.LateGet(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "usuario", new object[0], null, null, null).ToString().Length == 0)
				{
					NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "usuario", new object[1] { "Restotech" }, null, null, OptimisticSet: false, RValueBase: true);
				}
				NewLateBinding.LateSetComplex(NewLateBinding.LateGet(obj2, null, "cabecera", new object[0], null, null, null), null, "codigoDocumentoSector", new object[1] { clsFactElecConfig.FactSectores.CreditoDebito }, null, null, OptimisticSet: false, RValueBase: true);
				DataTable dataTable = new DataTable();
				DataTable dataTable2 = new DataTable();
				checked
				{
					if (dtProdsAFacturar != null)
					{
						dataTable2 = ((AgruparPagoID <= 0) ? dtProdsAFacturar : new ctlDetalleCuenta().ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(AgruparPagoID, DocumentoSectorFactura));
					}
					else
					{
						ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
						if (visitaId > 0)
						{
							dataTable2 = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionXML(visitaId, esContingencia, paraXml: true, DocumentoSectorFactura);
							dataTable = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaNotaCreditoXML(visitaId, debitoID);
							if (dataTable2.Rows.Count == 0 && esContingencia)
							{
								dataTable2 = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionConBorrados(visitaId, DocumentoSectorFactura);
								double num = 0.0;
								int leyId = dataTable2.Rows.Count - 1;
								for (int i = 0; i <= leyId; i++)
								{
									dataTable2.Rows[i]["pago"] = Operators.MultiplyObject(dataTable2.Rows[i]["Precio"], dataTable2.Rows[i]["Cantidad"]);
									dataTable2.Rows[i]["Debe"] = 0;
									num = Conversions.ToDouble(Operators.AddObject(num, dataTable2.Rows[i]["pago"]));
								}
								if (montoTotalOriginal != num)
								{
									error1 = error1 + "Hay un error entre los items y el monto total de la factura nro " + Conversions.ToString(nroFacturaOriginal);
								}
							}
						}
						else if (AgruparPagoID > 0)
						{
							dataTable2 = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(AgruparPagoID, DocumentoSectorFactura);
							Interaction.MsgBox("no esta implementado para agrupador");
						}
					}
					double num2 = new clsPropinas().devolverPropinasXvisitaID(visitaId);
					double num3 = 0.0;
					double num4 = 0.0;
					int num5 = dataTable2.Rows.Count - 1;
					int num6 = 0;
					while (true)
					{
						if (num6 <= num5)
						{
							double num7 = 0.0;
							bool flag = false;
							if (AgruparPagoID > 0)
							{
								if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectGreater(Operators.AddObject(dataTable2.Rows[num6]["Debe"], dataTable2.Rows[num6]["Pago"]), 0, TextCompare: false), Operators.CompareObjectGreater(dataTable2.Rows[num6]["Pagando"], 0, TextCompare: false))))
								{
									flag = true;
								}
							}
							else if (Operators.ConditionalCompareObjectGreater(Operators.AddObject(dataTable2.Rows[num6]["Debe"], dataTable2.Rows[num6]["Pago"]), 0, TextCompare: false))
							{
								flag = true;
							}
							if (DocumentoSectorFactura == 35)
							{
								flag = true;
							}
							if (flag)
							{
								if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num6]["precio"]), -1), -1, TextCompare: false))
								{
									ctlProductos ctlProductos2 = new ctlProductos();
									ctlProductos2.ToReturnIdbyName(Conversions.ToString(dataTable2.Rows[num6]["Producto"]));
									ctlProductos2.CargarPrecio();
									dataTable2.Rows[num6]["precio"] = ctlProductos2.getPrecio();
								}
								string text = Conversions.ToString(dataTable2.Rows[num6]["Producto"]);
								double num8;
								if (AgruparPagoID > 0)
								{
									if (Operators.ConditionalCompareObjectGreater(dataTable2.Rows[num6]["Pagando"], 0, TextCompare: false))
									{
										num7 = Conversions.ToDouble(dataTable2.Rows[num6]["Pagando"]);
										num8 = Conversions.ToDouble(Operators.DivideObject(Operators.MultiplyObject(dataTable2.Rows[num6]["Pagando"], dataTable2.Rows[num6]["Cantidad"]), dataTable2.Rows[num6]["Pagando"]));
									}
									else
									{
										num7 = 0.0;
										num8 = 0.0;
									}
								}
								else
								{
									num7 = Conversions.ToDouble(Operators.AddObject(dataTable2.Rows[num6]["Debe"], dataTable2.Rows[num6]["Pago"]));
									num8 = Conversions.ToDouble(dataTable2.Rows[num6]["Cantidad"]);
								}
								double num9 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num6]["Precio"]), 0));
								if (Operators.CompareString(text.ToUpper(), "SERVICIO", TextCompare: false) == 0)
								{
									num9 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num6]["Precio"]), 0));
								}
								if (Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num6]["ActividadSIN"])))
								{
									Interaction.MsgBox("El Producto: " + text + ", No tiene bien configurada su actividad SIN, pida a administración que lo arregle");
									result = false;
									break;
								}
								object obj4 = ((Modalidad != 1) ? ((object)new notaFiscalComputarizadaCreditoDebitoDetalle()) : ((object)new notaFiscalElectronicaCreditoDebitoDetalle()));
								NewLateBinding.LateSet(obj4, null, "actividadEconomica", new object[1] { dataTable2.Rows[num6]["ActividadSIN"] }, null, null);
								if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.lavasecoUniversal1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.JESPfacturacion))
								{
									NewLateBinding.LateSet(obj4, null, "codigoProducto", new object[1] { Operators.ConcatenateObject(dataTable2.Rows[num6]["codigo"], VariableGeneral.toDecimalSIN(num9, cantDecimales).ToString().Replace(",", "")
										.Replace(".", "")) }, null, null);
									NewLateBinding.LateSet(obj4, null, "descripcion", new object[1] { CleanInput(text + VariableGeneral.toDecimalSIN(num9, cantDecimales)) }, null, null);
								}
								else
								{
									NewLateBinding.LateSet(obj4, null, "codigoProducto", new object[1] { dataTable2.Rows[num6]["codigo"] }, null, null);
									NewLateBinding.LateSet(obj4, null, "descripcion", new object[1] { CleanInput(text) }, null, null);
								}
								NewLateBinding.LateSet(obj4, null, "codigoProductoSin", new object[1] { dataTable2.Rows[num6]["codigoSIN"] }, null, null);
								NewLateBinding.LateSet(obj4, null, "cantidad", new object[1] { VariableGeneral.toDecimalSIN(num8, cantDecimales) }, null, null);
								NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(num9, cantDecimales) }, null, null);
								NewLateBinding.LateSet(obj4, null, "unidadMedida", new object[1] { dataTable2.Rows[num6]["unidadSIN"] }, null, null);
								NewLateBinding.LateSet(obj4, null, "subTotal", new object[1] { VariableGeneral.toDecimalSIN(num7, cantDecimales) }, null, null);
								double num10 = Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.SubtractObject(num8 * num9, NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null)), cantDecimales));
								if (num10 >= 0.0)
								{
									NewLateBinding.LateSet(obj4, null, "montoDescuento", new object[1] { num10 }, null, null);
								}
								else
								{
									double num11 = Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.DivideObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), cantDecimales));
									if (num11 <= 0.0)
									{
										num11 = 0.01;
									}
									object instance = obj4;
									NewLateBinding.LateSet(instance, null, "precioUnitario", new object[1] { Operators.AddObject(NewLateBinding.LateGet(instance, null, "precioUnitario", new object[0], null, null, null), num11) }, null, null);
									NewLateBinding.LateSet(obj4, null, "montoDescuento", new object[1] { VariableGeneral.toDecimalSIN(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null)), cantDecimales) }, null, null);
								}
								if (decimal.Compare(VariableGeneral.toDecimalSIN(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null)), cantDecimales), VariableGeneral.toDecimalSIN(Operators.AddObject(NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), cantDecimales)) != 0)
								{
									NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(Operators.DivideObject(Operators.AddObject(NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), cantDecimales) }, null, null);
									if (decimal.Compare(VariableGeneral.toDecimalSIN(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null)), cantDecimales), VariableGeneral.toDecimalSIN(Operators.AddObject(NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null)), cantDecimales)) != 0)
									{
										double num12 = Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.DivideObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), cantDecimales));
										if (num12 <= 0.0)
										{
											num12 = 0.01;
										}
										object instance = obj4;
										NewLateBinding.LateSet(instance, null, "precioUnitario", new object[1] { Operators.AddObject(NewLateBinding.LateGet(instance, null, "precioUnitario", new object[0], null, null, null), num12) }, null, null);
										NewLateBinding.LateSet(obj4, null, "montoDescuento", new object[1] { VariableGeneral.toDecimalSIN(Operators.SubtractObject(Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null)), NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null)), cantDecimales) }, null, null);
									}
								}
								num3 = Conversions.ToDouble(Operators.AddObject(num3, NewLateBinding.LateGet(obj4, null, "subTotal", new object[0], null, null, null)));
								if (Operators.ConditionalCompareObjectEqual(NewLateBinding.LateGet(obj4, null, "precioUnitario", new object[0], null, null, null), 0, TextCompare: false))
								{
									NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { 1 }, null, null);
								}
								NewLateBinding.LateSet(obj4, null, "codigoDetalleTransaccion", new object[1] { 1 }, null, null);
								if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateGet(obj4, null, "cantidad", new object[0], null, null, null), 0, TextCompare: false))
								{
									object instance2 = obj3;
									object[] obj7 = new object[1] { obj4 };
									object[] array = obj7;
									bool[] obj8 = new bool[1] { true };
									bool[] array2 = obj8;
									NewLateBinding.LateCall(instance2, null, "Add", obj7, null, null, obj8, IgnoreReturn: true);
									if (array2[0])
									{
										obj4 = RuntimeHelpers.GetObjectValue(array[0]);
									}
								}
							}
							num6++;
							continue;
						}
						if (num4 > 0.0)
						{
							foreach (object item in (IEnumerable)obj3)
							{
								object objectValue = RuntimeHelpers.GetObjectValue(item);
								if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateGet(objectValue, null, "subTotal", new object[0], null, null, null), num4, TextCompare: false))
								{
									object instance = objectValue;
									NewLateBinding.LateSet(instance, null, "subTotal", new object[1] { Operators.SubtractObject(NewLateBinding.LateGet(instance, null, "subTotal", new object[0], null, null, null), num4) }, null, null);
									instance = objectValue;
									NewLateBinding.LateSet(instance, null, "montoDescuento", new object[1] { Operators.AddObject(NewLateBinding.LateGet(instance, null, "montoDescuento", new object[0], null, null, null), num4) }, null, null);
									num4 = 0.0;
									break;
								}
							}
						}
						if (num2 > 0.0)
						{
							ctlProductos obj9 = new ctlProductos();
							string Codigo = "";
							string Nombre = "";
							string CodigoSIN = "";
							string UnidadSINstr = "";
							int UnidadSIN = 0;
							string ActividadSIN = "";
							obj9.SetProductoID(1);
							obj9.cargarDatosSIN(ref Codigo, ref Nombre, ref CodigoSIN, ref UnidadSINstr, ref UnidadSIN, ref ActividadSIN);
							NewLateBinding.LateSet(obj5, null, "codigoDetalleTransaccion", new object[1] { 1 }, null, null);
							NewLateBinding.LateSet(obj5, null, "actividadEconomica", new object[1] { (Operators.CompareString(ActividadSIN, "", TextCompare: false) == 0) ? NewLateBinding.LateGet(NewLateBinding.LateIndexGet(obj3, new object[1] { 0 }, null), null, "actividadEconomica", new object[0], null, null, null) : ActividadSIN }, null, null);
							NewLateBinding.LateSet(obj5, null, "codigoProductoSin", new object[1] { (Operators.CompareString(CodigoSIN, "", TextCompare: false) == 0) ? NewLateBinding.LateGet(NewLateBinding.LateIndexGet(obj3, new object[1] { 0 }, null), null, "codigoProductoSin", new object[0], null, null, null) : CodigoSIN }, null, null);
							NewLateBinding.LateSet(obj5, null, "unidadMedida", new object[1] { (UnidadSIN == 0) ? NewLateBinding.LateGet(NewLateBinding.LateIndexGet(obj3, new object[1] { 0 }, null), null, "unidadMedida", new object[0], null, null, null) : ((object)UnidadSIN) }, null, null);
							NewLateBinding.LateSet(obj5, null, "codigoProducto", new object[1] { Codigo }, null, null);
							NewLateBinding.LateSet(obj5, null, "cantidad", new object[1] { 1 }, null, null);
							NewLateBinding.LateSet(obj5, null, "descripcion", new object[1] { Nombre }, null, null);
							NewLateBinding.LateSet(obj5, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(num2, cantDecimales) }, null, null);
							NewLateBinding.LateSet(obj5, null, "montoDescuento", new object[1] { 0 }, null, null);
							NewLateBinding.LateSet(obj5, null, "subTotal", new object[1] { VariableGeneral.toDecimalSIN(num2, cantDecimales) }, null, null);
							object[] array;
							bool[] array2;
							NewLateBinding.LateCall(obj3, null, "Add", array = new object[1] { obj5 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
							if (array2[0])
							{
								obj5 = RuntimeHelpers.GetObjectValue(array[0]);
							}
						}
						int num13 = dataTable.Rows.Count - 1;
						int num14 = 0;
						while (true)
						{
							if (num14 <= num13)
							{
								double num15 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num14]["Precio"]), 0));
								string text2 = Conversions.ToString(dataTable.Rows[num14]["Producto"]);
								if (Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[num14]["ActividadSIN"])))
								{
									Interaction.MsgBox("El Producto: " + text2 + ", No tiene bien configurada su actividad SIN, pida a administración que lo arregle");
									result = false;
									break;
								}
								object obj4 = ((Modalidad != 1) ? ((object)new notaFiscalComputarizadaCreditoDebitoDetalle()) : ((object)new notaFiscalElectronicaCreditoDebitoDetalle()));
								NewLateBinding.LateSet(obj4, null, "actividadEconomica", new object[1] { dataTable.Rows[num14]["ActividadSIN"] }, null, null);
								if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.lavasecoUniversal1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.JESPfacturacion))
								{
									NewLateBinding.LateSet(obj4, null, "codigoProducto", new object[1] { Operators.ConcatenateObject(dataTable.Rows[num14]["codigo"], VariableGeneral.toDecimalSIN(num15, cantDecimales).ToString().Replace(",", "")
										.Replace(".", "")) }, null, null);
									NewLateBinding.LateSet(obj4, null, "descripcion", new object[1] { CleanInput(text2 + VariableGeneral.toDecimalSIN(num15, cantDecimales)) }, null, null);
								}
								else
								{
									NewLateBinding.LateSet(obj4, null, "codigoProducto", new object[1] { dataTable.Rows[num14]["codigo"] }, null, null);
									NewLateBinding.LateSet(obj4, null, "descripcion", new object[1] { CleanInput(text2) }, null, null);
								}
								NewLateBinding.LateSet(obj4, null, "codigoProductoSin", new object[1] { dataTable.Rows[num14]["codigoSIN"] }, null, null);
								NewLateBinding.LateSet(obj4, null, "cantidad", new object[1] { VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable.Rows[num14]["cantidad"]), cantDecimales) }, null, null);
								NewLateBinding.LateSet(obj4, null, "precioUnitario", new object[1] { VariableGeneral.toDecimalSIN(num15, cantDecimales) }, null, null);
								NewLateBinding.LateSet(obj4, null, "unidadMedida", new object[1] { dataTable.Rows[num14]["unidadSIN"] }, null, null);
								NewLateBinding.LateSet(obj4, null, "montoDescuento", new object[1] { VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable.Rows[num14]["Descuento"]), cantDecimales) }, null, null);
								if (Operators.ConditionalCompareObjectLess(NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null), 0, TextCompare: false))
								{
									NewLateBinding.LateSet(obj4, null, "montoDescuento", new object[1] { Operators.MultiplyObject(NewLateBinding.LateGet(obj4, null, "montoDescuento", new object[0], null, null, null), -1) }, null, null);
								}
								NewLateBinding.LateSet(obj4, null, "subTotal", new object[1] { VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable.Rows[num14]["Subtotal"]), cantDecimales) }, null, null);
								NewLateBinding.LateSet(obj4, null, "codigoDetalleTransaccion", new object[1] { 2 }, null, null);
								object[] array;
								bool[] array2;
								NewLateBinding.LateCall(obj3, null, "Add", array = new object[1] { obj4 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
								if (array2[0])
								{
									obj4 = RuntimeHelpers.GetObjectValue(array[0]);
								}
								num14++;
								continue;
							}
							NewLateBinding.LateSet(obj2, null, "detalle", new object[1] { NewLateBinding.LateGet(obj3, null, "ToArray", new object[0], null, null, null) }, null, null);
							XmlDocument doc = new XmlDocument();
							doc.PreserveWhitespace = true;
							ctlFacturaElectronicaAlgoritmo ctlFacturaElectronicaAlgoritmo2 = new ctlFacturaElectronicaAlgoritmo();
							doc.LoadXml(SerializeObjectToXmlString(RuntimeHelpers.GetObjectValue(obj2)));
							if (Modalidad == 1)
							{
								DateTime Expiracion = DateAndTime.Now;
								if (!ctlFacturaElectronicaAlgoritmo2.FirmarXML(ref doc, ref error1, ref Expiracion, desdeCelular: false))
								{
									error1 = error1 + "error firmando.\r\n" + error1;
									result = false;
									break;
								}
							}
							strXmlUtf8 = doc.OuterXml;
							Encoding encoding = new UTF8Encoding(encoderShouldEmitUTF8Identifier: false);
							string text3 = "";
							if (File.Exists(text3 + "Xml\\Factura.xml"))
							{
								File.Delete(text3 + "Xml\\Factura.xml");
							}
							StreamWriter streamWriter = new StreamWriter(text3 + "Xml\\Factura.xml", append: false, encoding, 1024);
							doc.Save(streamWriter);
							streamWriter.Close();
							streamWriter.Dispose();
							if (enFisicoFolderArchivo.Length > 0)
							{
								StreamWriter streamWriter2 = new StreamWriter(text3 + "Xml\\" + enFisicoFolderArchivo + ".xml", append: false, encoding, 1024);
								doc.Save(streamWriter2);
								streamWriter2.Close();
								streamWriter2.Dispose();
							}
							ResultadoStream = new MemoryStream();
							StreamWriter streamWriter3 = new StreamWriter(ResultadoStream, encoding, 1024, leaveOpen: true);
							doc.Save(streamWriter3);
							streamWriter3.Close();
							streamWriter3.Dispose();
							result = true;
							break;
						}
						break;
					}
				}
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			error1 += ex2.Message;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public static T XmlDeserialize<T>(string toDeserialize)
	{
		XmlSerializer xmlSerializer = new XmlSerializer(typeof(T));
		using StringReader textReader = new StringReader(toDeserialize);
		return Conversions.ToGenericParameter<T>(xmlSerializer.Deserialize(textReader));
	}

	public static string XmlSerialize<T>(T toSerialize)
	{
		string result;
		try
		{
			XmlSerializer xmlSerializer = new XmlSerializer(typeof(T));
			using StringWriter stringWriter = new StringWriter();
			xmlSerializer.Serialize(stringWriter, toSerialize);
			result = stringWriter.ToString();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = null;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public static string SerializeObjectToXmlString(object toSerialize)
	{
		Type type = toSerialize.GetType();
		XmlSerializer xmlSerializer = new XmlSerializer(type);
		using StringWriter stringWriter = new StringWriter();
		xmlSerializer.Serialize(stringWriter, RuntimeHelpers.GetObjectValue(toSerialize));
		return stringWriter.ToString();
	}

	public object validarXML(string strXML, ref string errores, int Sector, int Modalidad, bool desdeCelular)
	{
		object result;
		try
		{
			clsXSDcompra clsXSDcompra2 = new clsXSDcompra();
			string text = "";
			switch (Modalidad)
			{
			case 2:
				switch (Sector)
				{
				case 1:
					text = clsXSDcompra2.xsdComputarizadaCompraVenta;
					break;
				case 8:
					text = clsXSDcompra2.xsdComputarizadaTasaCero;
					break;
				case 35:
					text = clsXSDcompra2.xsdComputarizadaCompraVentaBon;
					break;
				case 14:
					text = clsXSDcompra2.xsdComputarizadaICE;
					break;
				case 24:
					text = clsXSDcompra2.xsdComputarizadaNotaDebito;
					break;
				}
				break;
			case 1:
				switch (Sector)
				{
				case 1:
					text = clsXSDcompra2.xsdElectronicaCompraVenta;
					break;
				case 8:
					text = clsXSDcompra2.xsdElectronicaTasaCero;
					break;
				case 35:
					text = clsXSDcompra2.xsdElectronicaCompraVentaBon;
					break;
				case 14:
					text = clsXSDcompra2.xsdElectronicaICE;
					break;
				case 24:
					text = clsXSDcompra2.xsdElectronicaNotaDebito;
					break;
				}
				break;
			}
			if (text.Length == 0)
			{
				errores = "No se cargo al xsd para el validador";
				result = false;
			}
			else
			{
				if (desdeCelular)
				{
					text = text.Replace("schemaLocation=\"SignatureSchema.xsd\"", "schemaLocation=\"file://C://Restotech//SignatureSchema.xsd\"");
				}
				XDocument source = XDocument.Parse(strXML);
				XmlSchemaSet xmlSchemaSet = new XmlSchemaSet();
				StringBuilder stringBuilder = new StringBuilder();
				MemoryStream memoryStream = new MemoryStream(Encoding.ASCII.GetBytes(text));
				using (memoryStream)
				{
					xmlSchemaSet.Add(XmlSchema.Read(memoryStream, [SpecialName] (object s, ValidationEventArgs e1) =>
					{
						stringBuilder.AppendLine($"{e1.Message}");
					}));
				}
				try
				{
					source.Validate(xmlSchemaSet, [SpecialName] (object o, ValidationEventArgs e2) =>
					{
						stringBuilder.AppendLine($"{e2.Exception.Message}");
					});
				}
				catch (Exception ex)
				{
					ProjectData.SetProjectError(ex);
					Exception ex2 = ex;
					stringBuilder.AppendLine($"{ex2.Message}");
					ProjectData.ClearProjectError();
				}
				if (stringBuilder.Length > 0)
				{
					errores = stringBuilder.ToString();
					result = false;
				}
				else
				{
					result = true;
				}
			}
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			errores += ex4.Message;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public string CleanInput(string strIn)
	{
		string result;
		try
		{
			result = Regex.Replace(strIn, "[^\\w\\s\\.@-]", "", RegexOptions.None, TimeSpan.FromSeconds(1.5));
		}
		catch (RegexMatchTimeoutException ex)
		{
			ProjectData.SetProjectError(ex);
			RegexMatchTimeoutException ex2 = ex;
			result = string.Empty;
			ProjectData.ClearProjectError();
		}
		return result;
	}
}
