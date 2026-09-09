using System;
using System.Collections.Generic;
using System.Data;
using System.IO;
using System.Linq;
using System.Runtime.CompilerServices;
using System.Threading;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElecContingencias
{
	public void verificarSiFolderExisteYborrar(string folder)
	{
		if (Directory.Exists(folder))
		{
			try
			{
				File.SetAttributes(folder, FileAttributes.Normal);
				Directory.Delete(folder, recursive: true);
				File.Delete(folder + ".tar");
				File.Delete(folder + ".tar.zip");
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				ProjectData.ClearProjectError();
			}
		}
	}

	public bool EventoFueraLinea(ref string error1, int UsuarioID, int _ContingenciaID)
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		clsFactElecConfig2.devolverDatosSiTokenActivo1();
		clsFactElecObtencionCodigos clsFactElecObtencionCodigos2 = new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente);
		string codigoCUIS = "";
		string codigoCUFD = "";
		string CodigoControl = "";
		if (codigoCUIS.Length == 0)
		{
			clsFactElecObtencionCodigos2.ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
		}
		DateTime fecha;
		int cufdID;
		if (codigoCUFD.Length == 0)
		{
			fecha = DateAndTime.Now;
			cufdID = 0;
			if (!clsFactElecObtencionCodigos2.ObtenerCUFD(ref codigoCUFD, ref CodigoControl, ref fecha, ref cufdID))
			{
				Interaction.MsgBox("No se pudo obtener codigo cufD");
				return false;
			}
		}
		string cafc = "";
		int codigoMotivoEvento = 0;
		string codigoEventoRespuesta = "";
		codigoCUFD = "";
		DateTime inicio = default(DateTime);
		int num = new clsFactElecContingencias().checkearContingenciaFueraDeLinea(ref codigoCUFD, ref inicio, ref cafc, ref codigoMotivoEvento, ref codigoEventoRespuesta, _ContingenciaID);
		if (num == 0)
		{
			Interaction.MsgBox("No hay una contingencia " + Conversions.ToString(_ContingenciaID));
			error1 = "";
			return false;
		}
		_ = clsFactElecConfig2.CodigoPuntoVenta;
		int num2 = 2;
		string text = Conversions.ToString(clsFactElecConfig2.NIT);
		DataTable dataTable = BD.ConsultaVer("select TOP 500 FacturaID, VisitaID, NroFactura, tipoDocumentoID, Facturas.FechaEmision  AS Fecha_Factura, Facturas.NIT, Complemento, Facturas.Nombre as Razon_Social, Facturas.Monto  as Importe_Total_Venta,Facturas.ICE as Importe_Ice, Facturas.Monto+Facturas.Descuento  as Subtotal,Facturas.Descuento as Descuentos, montoGiftCard as GIFTCARD, Facturas.Monto  as Importe_Base, round((((Facturas.Monto)-MontoGiftCard)*0.13),2) as Debito_Fiscal,  Facturas.Codigo  as Codigo_Control, FactElectLeyes.Descripcion as Ley, Anulada, Facturas.AgruparPagoID, Facturas.DocumentoSector  from facturas left join FactElectLeyes on (facturas.LeyID=FactElectLeyes.ID)  where  (EstadoSiat=0 or EstadoSiat>=3 or EstadoSiat is null) and FueraLineaID = " + Conversions.ToString(num));
		if (dataTable.Rows.Count == 0)
		{
			Interaction.MsgBox("No hay facturas sin enviar en la Contingencia " + Conversions.ToString(num) + "; se terminara la Contingencia");
			new clsFactElecContingencias().TerminarContingenciaFueraDeLinea(DateAndTime.Now, -1, num, "");
			verificarSiFolderExisteYborrar("Xml\\" + Conversions.ToString(num));
			error1 = "";
			return false;
		}
		string codigoCUFD2 = "";
		fecha = DateAndTime.Now;
		cufdID = 0;
		clsFactElecObtencionCodigos2.ObtenerCUFD(ref codigoCUFD2, ref CodigoControl, ref fecha, ref cufdID);
		if (Operators.CompareString(codigoCUFD, codigoCUFD2, TextCompare: false) == 0)
		{
			new clsCUFD().modificarFechaHastaCUFD(codigoCUFD2, DateAndTime.Now);
			Thread.Sleep(1000);
			fecha = DateAndTime.Now;
			cufdID = 0;
			clsFactElecObtencionCodigos2.ObtenerCUFD(ref codigoCUFD2, ref CodigoControl, ref fecha, ref cufdID);
		}
		DataTable dataTable2 = BD.ConsultaVer("select min(fechaEmision) as minima, max(FechaEmision) as maxima from facturas where (EstadoSiat=0 or EstadoSiat>=3 or EstadoSiat is null) and FueraLineaID = " + Conversions.ToString(num));
		if (dataTable2.Rows.Count == 0)
		{
			Interaction.MsgBox("No hay facturas sin enviar en la Contingencia " + Conversions.ToString(num) + ", se terminara la Contingencia");
			new clsFactElecContingencias().TerminarContingenciaFueraDeLinea(DateAndTime.Now, -1, num, "");
			verificarSiFolderExisteYborrar("Xml\\" + Conversions.ToString(num));
			error1 = "";
			return false;
		}
		DateTime dateTime = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["minima"]), DateAndTime.Now));
		dateTime = dateTime;
		DataTable dataTable3 = BD.ConsultaVer("fechaDesde, fechaHasta", "FactElectCUFD", "codigoCUFD like '" + codigoCUFD + "'");
		DateTime t;
		DateTime t2;
		if (dataTable3.Rows.Count > 0)
		{
			t = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0][0]), DateAndTime.Today));
			t2 = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0][1]), DateAndTime.Today));
		}
		else
		{
			t = dateTime.AddSeconds(-1.0);
			t2 = dateTime.AddSeconds(-1.0);
		}
		if (codigoEventoRespuesta.Length <= 3)
		{
			if ((DateTime.Compare(dateTime, t) > 0) & (DateTime.Compare(dateTime, t2) < 0))
			{
				inicio = dateTime.AddSeconds(-1.0);
			}
			if (DateTime.Compare(t.AddSeconds(20.0), inicio) > 0)
			{
				inicio = t.AddSeconds(20.0);
			}
			if (Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "FactElectFueraLinea", VariableGeneral.ArmarFecha(inicio) + " between Inicio and Final").Rows[0][0], 0, TextCompare: false))
			{
				inicio = dateTime.AddSeconds(-1.0);
			}
		}
		if (DateTime.Compare(inicio, dateTime) > 0 && cafc.Length == 0)
		{
			inicio = dateTime;
		}
		if (DateTime.Compare(inicio, t) < 0 && cafc.Length == 0)
		{
			inicio = t.AddSeconds(1.0);
		}
		if (DateTime.Compare(inicio, dateTime) > 0 && cafc.Length == 0)
		{
			if (codigoEventoRespuesta.Length <= 3 && !Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "FactElectFueraLinea", VariableGeneral.ArmarFecha(inicio) + " between Inicio and Final").Rows[0][0], 0, TextCompare: false))
			{
				inicio = dateTime.AddSeconds(-1.0);
			}
			if (DateTime.Compare(inicio, dateTime) > 0)
			{
				Interaction.MsgBox("El inicio de Contingencia no puede ser mayor que la emision de la 1er factura");
				return false;
			}
		}
		DateTime dateTime2 = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["maxima"]), DateAndTime.Now)).AddSeconds(20.0);
		if (DateTime.Compare(dateTime2.AddMinutes(-1.0), inicio) <= 0)
		{
			dateTime2 = dateTime2.AddMinutes(1.0);
		}
		if (DateTime.Compare(dateTime2, inicio) < 0)
		{
			dateTime2 = DateAndTime.Now;
		}
		if (DateTime.Compare(dateTime2, DateAndTime.Now.AddMinutes(-1.0)) > 0)
		{
			Interaction.MsgBox("Tocara esperar 1 minuto para poder subirlo, tenga paciencia");
			Thread.Sleep(60000);
		}
		clsFacElecOperaciones clsFacElecOperaciones2 = new clsFacElecOperaciones((int)clsFactElecConfig2.CodigoAmbiente);
		bool nitValidado = false;
		if (Directory.Exists("Xml\\" + Conversions.ToString(num)))
		{
			int num3 = Directory.GetFiles("Xml\\" + Conversions.ToString(num), "*.xml").Count();
			if ((num3 != dataTable.Rows.Count) & (num3 > 0))
			{
				try
				{
					string text2 = "Xml\\" + Conversions.ToString(num);
					string[] files = Directory.GetFiles(text2, "*.*", SearchOption.TopDirectoryOnly);
					foreach (string text3 in files)
					{
						string right = text3.Replace(text2 + "\\Factura", "").Replace(".xml", "");
						bool flag = true;
						foreach (object row in dataTable.Rows)
						{
							object objectValue = RuntimeHelpers.GetObjectValue(row);
							if (Conversions.ToBoolean(Operators.AndObject(Operators.CompareObjectEqual(NewLateBinding.LateIndexGet(objectValue, new object[1] { "NroFactura" }, null), right, TextCompare: false), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue, new object[1] { "anulada" }, null)), false))))
							{
								flag = false;
								break;
							}
						}
						if (flag)
						{
							File.Delete(text3);
						}
					}
				}
				catch (Exception ex)
				{
					ProjectData.SetProjectError(ex);
					Exception ex2 = ex;
					Interaction.MsgBox(ex2.Message);
					ProjectData.ClearProjectError();
				}
			}
		}
		if (!Directory.Exists("Xml\\" + Conversions.ToString(num)))
		{
			Directory.CreateDirectory("Xml\\" + Conversions.ToString(num));
			if (!Directory.Exists("Xml\\" + Conversions.ToString(num)))
			{
				Interaction.MsgBox("No puede crear el folder en XML\\");
				return false;
			}
		}
		if (codigoEventoRespuesta.Length <= 3)
		{
			if (!clsFacElecOperaciones2.RegistroEventoSignificativo(ref codigoEventoRespuesta, codigoMotivoEvento, codigoCUIS, codigoCUFD2, codigoCUFD, inicio, dateTime2, "ContingenciaID " + Conversions.ToString(num), num))
			{
				if (!clsFacElecOperaciones2.consultaEventoSignificativo1(ref codigoEventoRespuesta, codigoMotivoEvento, codigoCUIS, codigoCUFD2, inicio, num))
				{
					Interaction.MsgBox("No pudo registrar ni consultar el evento");
					error1 = "";
					return false;
				}
			}
			else
			{
				new clsFactElecContingencias().updateContingenciaFechas(num, inicio, dateTime2);
			}
		}
		var enumerable = dataTable.AsEnumerable().GroupBy([SpecialName] (DataRow row) => row.Field<int>("DocumentoSector"), [SpecialName] (DataRow row) => row, [SpecialName] (int DocumentoSector, IEnumerable<DataRow> _0024VB_0024ItAnonymous) => new
		{
			DocumentoSector = DocumentoSector,
			Group = _0024VB_0024ItAnonymous
		});
		bool flag2 = true;
		foreach (var item in enumerable)
		{
			int documentoSector = item.DocumentoSector;
			verificarSiFolderExisteYborrar("Xml\\" + Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector));
			Directory.CreateDirectory("Xml\\" + Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector));
			if (!Directory.Exists("Xml\\" + Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector)))
			{
				Interaction.MsgBox("No puede crear el folder en XML\\");
				return false;
			}
			foreach (DataRow item2 in item.Group)
			{
				bool flag3 = true;
				if (File.Exists(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("Xml\\" + Conversions.ToString(num) + "\\Factura", item2["NroFactura"]), ".xml"))))
				{
					if (Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(item2["Anulada"]), false)))
					{
						flag3 = false;
						File.Copy(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("Xml\\" + Conversions.ToString(num) + "\\Factura", item2["NroFactura"]), ".xml")), Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("Xml\\" + Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector) + "\\Factura", item2["NroFactura"]), ".xml")), overwrite: true);
					}
					else
					{
						File.Delete(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("Xml\\" + Conversions.ToString(num) + "\\Factura", item2["NroFactura"]), ".xml")));
					}
				}
				if (!flag3)
				{
					continue;
				}
				MemoryStream ResultadoStream = new MemoryStream();
				clsCreateXMLFactura clsCreateXMLFactura2 = new clsCreateXMLFactura();
				string strXmlUtf = "";
				string error2 = "";
				double num4 = 0.0;
				num4 = new ctlDetalleCuenta().ReturnICEVisitaYconfiguracion1(Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(item2["visitaID"]), 0)), VariableGeneral.gConfiguracionID, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(item2["AgruparPagoID"]), 0)), documentoSector);
				num4 = Conversions.ToDouble(VariableGeneral.NZ(num4, 0));
				if (clsCreateXMLFactura2.crearXMLcompraVentaComputarizada(documentoSector, (int)clsFactElecConfig2.CodigoModalidad, ref ResultadoStream, ref strXmlUtf, Conversions.ToString(item2["Codigo_Control"]), codigoCUFD, Conversions.ToString(clsFactElecConfig2.codigoSucursal), Conversions.ToString(clsFactElecConfig2.NIT), Conversions.ToDate(item2["Fecha_Factura"]), Conversions.ToString(item2["Razon_Social"]), Conversions.ToString(item2["NIT"]), Conversions.ToString(item2["Complemento"]), Conversions.ToDouble(item2["Importe_Total_Venta"]), num4, Conversions.ToInteger(item2["NroFactura"]), UsuarioID, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(item2["visitaID"]), 0)), null, Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(item2["AgruparPagoID"]), 0)), facturandoAnticipadamente: false, clsFactElecConfig2.CodigoPuntoVenta, Conversions.ToString((num2 == 2) ? Operators.ConcatenateObject(Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector) + "\\Factura", item2["NroFactura"]) : ""), Conversions.ToDouble(item2["GIFTCARD"]), cafc, Conversions.ToDouble(item2["Descuentos"]), Conversions.ToInteger(item2["tipoDocumentoID"]), num > 0, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(item2["Ley"]), "")), nitValidado, ref error2, desdeCelular: false))
				{
					if (error2.Length > 0)
					{
						Interaction.MsgBox(error2);
					}
					ResultadoStream.Dispose();
					ResultadoStream.Close();
					ResultadoStream = null;
					string errores = "";
					if (!Conversions.ToBoolean(clsCreateXMLFactura2.validarXML(strXmlUtf, ref errores, documentoSector, (int)clsFactElecConfig2.CodigoModalidad, desdeCelular: false)))
					{
						Interaction.MsgBox(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("No pudo validar el xml de la factura ", item2["NroFactura"]), "\r\n"), errores));
						File.Delete(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("Xml\\" + Conversions.ToString(num) + "\\Factura", item2["NroFactura"]), ".xml")));
					}
				}
				else
				{
					Interaction.MsgBox(Operators.ConcatenateObject("No pudo generar el xml de la factura ", item2["NroFactura"]));
				}
			}
			string text4 = "Xml\\" + Conversions.ToString(num) + ".tar";
			string text5 = "Xml\\" + Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector) + "\\";
			ctlFacturaElectronicaAlgoritmo ctlFacturaElectronicaAlgoritmo2 = new ctlFacturaElectronicaAlgoritmo();
			ctlFacturaElectronicaAlgoritmo2.TARcompress(text5, text4);
			FileInfo fileToCompress = new FileInfo(text4);
			if (!ctlFacturaElectronicaAlgoritmo2.CompressGZIP(fileToCompress))
			{
				Interaction.MsgBox("No comprimio");
				error1 = "";
				return false;
			}
			byte[] array = File.ReadAllBytes(text4 + ".zip");
			string algoritmoHashSHA = ctlFacturaElectronicaAlgoritmo2.getAlgoritmoHashSHA256(array);
			int cantFact;
			if (Directory.Exists(text5))
			{
				cantFact = Directory.GetFiles(text5, "*.xml").Count();
				if (Operators.CompareString(codigoEventoRespuesta, "0", TextCompare: false) == 0)
				{
					codigoEventoRespuesta = "";
				}
				clsFactElecSectorCompraVenta clsFactElecSectorCompraVenta2 = new clsFactElecSectorCompraVenta((int)clsFactElecConfig2.CodigoAmbiente, documentoSector, (int)clsFactElecConfig2.CodigoModalidad);
				if (codigoEventoRespuesta.Length > 3)
				{
					int num5 = 1;
					switch (documentoSector)
					{
					case 8:
						num5 = 2;
						break;
					case 24:
						num5 = 3;
						break;
					}
					string CodigoRecepcionEventoRespuesta = "";
					if (Conversions.ToBoolean(clsFactElecSectorCompraVenta2.RecepcionFacturaPaqueteFueraLinea(num2, codigoCUFD2, codigoCUIS, num5, documentoSector, array, algoritmoHashSHA, cafc, cantFact, codigoEventoRespuesta, num, ref CodigoRecepcionEventoRespuesta, dateTime2, ref error1)))
					{
						if (Conversions.ToBoolean(clsFactElecSectorCompraVenta2.ValidacionFacturaPaqueteFueraLinea(num2, codigoCUFD2, codigoCUIS, num5, documentoSector, num, codigoEventoRespuesta, CodigoRecepcionEventoRespuesta, ref error1)))
						{
							ctlFacturas ctlFacturas2 = new ctlFacturas();
							DataTable dataTable4 = ctlFacturas2.devolverFueradeLineaNoEnviada(num);
							int num8;
							checked
							{
								int num6 = dataTable4.Rows.Count - 1;
								for (int num7 = 0; num7 <= num6; num7++)
								{
									if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable4.Rows[num7]["correo"]), "").ToString().Length > 0)
									{
										string text6 = "";
										ImprimiendoComandas.sendEmailFactura(link: (clsFactElecConfig2.CodigoAmbiente != clsFactElecConfig.FactAmbiente.Produccion) ? Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("https://pilotosiat.impuestos.gob.bo/consulta/QR?nit=" + text + "&cuf=", dataTable4.Rows[num7]["Codigo"]), "&numero="), dataTable4.Rows[num7]["NroFactura"]), "&t="), (!configuration.gFormatoFacturaGrande) ? 1 : 2)) : Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("https://siat.impuestos.gob.bo/consulta/QR?nit=" + text + "&cuf=", dataTable4.Rows[num7]["Codigo"]), "&numero="), dataTable4.Rows[num7]["NroFactura"]), "&t="), (!configuration.gFormatoFacturaGrande) ? 1 : 2)), emailTo: Conversions.ToString(dataTable4.Rows[num7]["correo"]), archivoXML: Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(MyProject.Application.Info.DirectoryPath + "\\Xml\\" + Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector) + "\\Factura", dataTable4.Rows[num7]["NroFactura"]), ".xml")), archivoFactura: Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(MyProject.Application.Info.DirectoryPath + "\\Xml\\" + Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector) + "\\Factura", dataTable4.Rows[num7]["NroFactura"]), ".pdf")), Modalidad: unchecked((int)clsFactElecConfig2.CodigoModalidad), NombreFactura: Conversions.ToString(dataTable4.Rows[num7]["Nombre"]), NroFactura: Conversions.ToString(dataTable4.Rows[num7]["NroFactura"]), FechaEmision: Conversions.ToDate(dataTable4.Rows[num7]["FechaEmision"]), esFactura: true);
									}
								}
								num8 = dataTable.Rows.Count - 1;
							}
							for (int num9 = 0; num9 <= num8; num9 = checked(num9 + 1))
							{
								if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num9]["DocumentoSector"]), 0), documentoSector, TextCompare: false))
								{
									if (Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num9]["Anulada"]), false)))
									{
										clsFactElecSectorCompraVenta obj = new clsFactElecSectorCompraVenta((int)clsFactElecConfig2.CodigoAmbiente, documentoSector, (int)clsFactElecConfig2.CodigoModalidad);
										int codigoEmision = 1;
										int codigoMotivo = 1;
										string cufd = codigoCUFD2;
										string cuis = codigoCUIS;
										int tipoFacturaDocumento = num5;
										string cuf = Conversions.ToString(dataTable.Rows[num9]["Codigo_Control"]);
										bool ExisteEnSiat = false;
										string mensaje = "";
										obj.AnularFactura1(codigoMotivo, codigoEmision, cufd, cuis, tipoFacturaDocumento, documentoSector, cuf, ref ExisteEnSiat, ref mensaje);
										Thread.Sleep(2000);
									}
									clsFactElecSectorCompraVenta obj2 = new clsFactElecSectorCompraVenta((int)clsFactElecConfig2.CodigoAmbiente, documentoSector, (int)clsFactElecConfig2.CodigoModalidad);
									string error3 = "";
									int estado = 0;
									int codigoEmision2 = 1;
									if (Conversions.ToBoolean(obj2.ValidacionFactura1(codigoEmision2, codigoCUFD2, codigoCUIS, num5, documentoSector, Conversions.ToString(dataTable.Rows[num9]["Codigo_Control"]), ref estado, ref error3)))
									{
										ctlFacturas2.SetFacturaID(Conversions.ToInteger(dataTable.Rows[num9]["FacturaID"]));
										ctlFacturas2.setEstado(estado);
									}
								}
							}
						}
						else
						{
							if (error1.Contains("LA FECHA DE ENVIO DEL PAQUETE ESTA FUERA DE PLAZO") && Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("select count(*) from facturas where (EstadoSiat=0 or EstadoSiat>=3 or EstadoSiat is null) and FueraLineaID = " + Conversions.ToString(num) + " and DocumentoSector=" + Conversions.ToString(documentoSector)).Rows[0][0], 0, TextCompare: false) && Directory.Exists("Xml\\" + Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector)))
							{
								verificarSiFolderExisteYborrar("Xml\\" + Conversions.ToString(num) + "\\" + Conversions.ToString(documentoSector));
								File.Delete("Xml\\" + Conversions.ToString(num) + ".tar");
								File.Delete("Xml\\" + Conversions.ToString(num) + ".tar.zip");
							}
							flag2 = false;
						}
					}
					else
					{
						error1 += "\r\nNo se pudo recepcionar";
						flag2 = false;
					}
				}
				else
				{
					flag2 = false;
				}
				continue;
			}
			cantFact = 0;
			Interaction.MsgBox("No hay facturas que declarar");
			error1 = "";
			return false;
		}
		if (flag2)
		{
			try
			{
				if (Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("select count(*) from facturas where (EstadoSiat=0 or EstadoSiat>=3 or EstadoSiat is null) and FueraLineaID = " + Conversions.ToString(num)).Rows[0][0], 0, TextCompare: false))
				{
					verificarSiFolderExisteYborrar("Xml\\" + Conversions.ToString(num));
					File.Delete("Xml\\" + Conversions.ToString(num) + ".tar");
					File.Delete("Xml\\" + Conversions.ToString(num) + ".tar.zip");
				}
			}
			catch (Exception ex3)
			{
				ProjectData.SetProjectError(ex3);
				Exception ex4 = ex3;
				ProjectData.ClearProjectError();
			}
		}
		return flag2;
	}

	public void VerificarFacturas(DateTime desde, DateTime hasta)
	{
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (!clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				return;
			}
			clsFactElecObtencionCodigos clsFactElecObtencionCodigos2 = new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente);
			string codigoCUIS = "";
			string codigoCUFD = "";
			string CodigoControl = "";
			if (codigoCUIS.Length == 0)
			{
				clsFactElecObtencionCodigos2.ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			}
			int cufdID;
			if (codigoCUFD.Length == 0)
			{
				DateTime fecha = DateAndTime.Now;
				cufdID = 0;
				clsFactElecObtencionCodigos2.ObtenerCUFD(ref codigoCUFD, ref CodigoControl, ref fecha, ref cufdID);
			}
			ctlFacturas ctlFacturas2 = new ctlFacturas();
			if (codigoCUFD.Length == 0)
			{
				Interaction.MsgBox("No se pudo obtener CUFD vigente");
				return;
			}
			DataTable dataTable = BD.ConsultaVer("select FacturaID, Facturas.FechaEmision  AS Fecha_Factura, Facturas.Codigo  as Codigo_Control, DocumentoSector  from Facturas  where FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta));
			cufdID = checked(dataTable.Rows.Count - 1);
			for (int i = 0; i <= cufdID; i = checked(i + 1))
			{
				clsFactElecSectorCompraVenta obj = new clsFactElecSectorCompraVenta((int)clsFactElecConfig2.CodigoAmbiente, Conversions.ToInteger(dataTable.Rows[i]["DocumentoSector"]), (int)clsFactElecConfig2.CodigoModalidad);
				int codigoEmision = 1;
				string error = "";
				int estado = 0;
				int tipoFacturaDocumento = 1;
				if (Operators.ConditionalCompareObjectEqual(dataTable.Rows[i]["DocumentoSector"], clsFactElecConfig.FactSectores.TasaCero, TextCompare: false))
				{
					tipoFacturaDocumento = 2;
				}
				else if (Operators.ConditionalCompareObjectEqual(dataTable.Rows[i]["DocumentoSector"], clsFactElecConfig.FactSectores.CreditoDebito, TextCompare: false))
				{
					tipoFacturaDocumento = 3;
				}
				if (Conversions.ToBoolean(obj.ValidacionFactura1(codigoEmision, codigoCUFD, codigoCUIS, tipoFacturaDocumento, Conversions.ToInteger(dataTable.Rows[i]["DocumentoSector"]), Conversions.ToString(dataTable.Rows[i]["Codigo_Control"]), ref estado, ref error)))
				{
					ctlFacturas2.SetFacturaID(Conversions.ToInteger(dataTable.Rows[i]["FacturaID"]));
					ctlFacturas2.setEstado(estado);
				}
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
	}

	public int checkearContingenciaFueraDeLinea(ref string CUFDactual, ref DateTime inicio, ref string cafc, ref int codigoMotivoEvento, ref string codigoEventoRespuesta, int ContingenciaID)
	{
		DataTable dataTable = ((ContingenciaID > 0) ? BD.ConsultaVer("FactElectFueraLineaID, CUFD, inicio, cafc, motivo,codigoEventoRespuesta", "FactElectFueraLinea", "FactElectFueraLineaID=" + Conversions.ToString(ContingenciaID)) : ((CUFDactual.Length <= 0) ? BD.ConsultaVer("FactElectFueraLineaID, CUFD, inicio, cafc, motivo,codigoEventoRespuesta", "FactElectFueraLinea", "Final is null and (CodigoEventoRespuesta is null or CodigoEventoRespuesta ='0') and ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID), "FactElectFueraLineaID desc") : BD.ConsultaVer("FactElectFueraLineaID, CUFD, inicio, cafc, motivo,codigoEventoRespuesta", "FactElectFueraLinea", "Final Is null and (CodigoEventoRespuesta is null or CodigoEventoRespuesta ='0') And cufd='" + CUFDactual + "' ", "FactElectFueraLineaID desc")));
		if (dataTable.Rows.Count > 0)
		{
			inicio = Conversions.ToDate(dataTable.Rows[0]["inicio"]);
			cafc = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["cafc"]), ""));
			codigoMotivoEvento = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["motivo"]), 0));
			CUFDactual = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CUFD"]), 0));
			codigoEventoRespuesta = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["codigoEventoRespuesta"]), ""));
			if (codigoEventoRespuesta.Length < 3)
			{
				codigoEventoRespuesta = "";
			}
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		cafc = "";
		codigoMotivoEvento = 0;
		codigoEventoRespuesta = "";
		return 0;
	}

	public int CrearContingenciaFueraDeLinea(DateTime inicio, int codigoEvento, string CUFDactual, string cafc, int cufdID, bool desdeCelular)
	{
		DataTable dataTable = BD.ConsultaVer("FactElectFueraLineaID", "FactElectFueraLinea", "Final is null and (CodigoEventoRespuesta is null or CodigoEventoRespuesta ='0') and CUFD='" + CUFDactual + "'", "FactElectFueraLineaID desc");
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		int id = 0;
		BD.ConsultaInsertar3(VariableGeneral.ArmarFecha(inicio) + "," + Conversions.ToString(codigoEvento) + ",'" + CUFDactual + "','" + cafc + "'," + Conversions.ToString(cufdID) + "," + Conversions.ToString(VariableGeneral.gConfiguracionID), "FactElectFueraLinea(Inicio, motivo, CUFD,cafc, cufdID,ConfiguracionID )", ref id);
		string text = "";
		if (desdeCelular)
		{
			text = "c:\\Restotech\\";
		}
		if (!Directory.Exists(text + "Xml\\" + Conversions.ToString(id)))
		{
			Directory.CreateDirectory(text + "Xml\\" + Conversions.ToString(id));
		}
		return id;
	}

	public void TerminarContingenciaFueraDeLinea(DateTime fin, int cantidad, int ContingenciaId, string CodigoRecepcionEventoRespuesta)
	{
		string text = (",Cantidad=" + Conversions.ToString(cantidad)) ?? "";
		if (cantidad == -1)
		{
			text = " ";
		}
		string text2 = ",CodigoRecepcionEvento='" + CodigoRecepcionEventoRespuesta + "'";
		if (CodigoRecepcionEventoRespuesta.Length == 0)
		{
			text2 = " ";
		}
		BD.ConsultaModificar("FactElectFueraLinea", "Final= " + VariableGeneral.ArmarFecha(fin) + " " + text + text2 + ",flagSync=NULL", "FactElectFueraLineaID=" + Conversions.ToString(ContingenciaId));
		if (cantidad == -1)
		{
			BD.ConsultaModificar("FactElectFueraLinea", "CodigoRecepcionEvento='-1',flagSync=NULL", "(CodigoEventoRespuesta is null or CodigoEventoRespuesta ='0') and FactElectFueraLineaID=" + Conversions.ToString(ContingenciaId));
			BD.ConsultaModificar("FactElectFueraLinea", "CodigoEventoRespuesta= '-1',flagSync=NULL", "FactElectFueraLineaID=" + Conversions.ToString(ContingenciaId) + " and (CodigoEventoRespuesta is null or CodigoEventoRespuesta ='0')");
		}
	}

	public int ValidarContingenciaFueraDeLinea(int ContingenciaId)
	{
		return BD.ConsultaModificar("FactElectFueraLinea", "obs='Validada',flagSync=NULL,estado= " + VariableGeneral.armarBolean(1), "FactElectFueraLineaID=" + Conversions.ToString(ContingenciaId));
	}

	public int updateContingenciaObs(int ContingenciaId, string Obs)
	{
		return BD.ConsultaModificar("FactElectFueraLinea", "Obs='" + Obs + "',flagSync=NULL", "FactElectFueraLineaID=" + Conversions.ToString(ContingenciaId));
	}

	public void updateContingenciaFechas(int ContingenciaId, DateTime fechaIni, DateTime fechaFin)
	{
		BD.ConsultaModificar("FactElectFueraLinea", "Inicio=" + VariableGeneral.ArmarFecha(fechaIni) + ",Final=" + VariableGeneral.ArmarFecha(fechaFin) + ",flagSync=NULL", "FactElectFueraLineaID=" + Conversions.ToString(ContingenciaId));
	}

	public void updateContingenciaCodigoCreacionEvento(int ContingenciaId, string CodigoEventoRespuesta)
	{
		BD.ConsultaModificar("FactElectFueraLinea", "CodigoEventoRespuesta='" + CodigoEventoRespuesta + "',flagSync=NULL", "FactElectFueraLineaID=" + Conversions.ToString(ContingenciaId));
	}

	public DataTable checkContingenciasSInCerrar(DateTime desde, DateTime hasta)
	{
		return BD.ConsultaVer("FactElectFueraLineaID", "FactElectFueraLinea", "(Final is null or CodigoEventoRespuesta is null or CodigoEventoRespuesta = '0' ) and inicio between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " and ConfiguracionID =" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public DataTable checkContingenciasFacturasSinEnviar(DateTime desde, DateTime hasta)
	{
		return BD.ConsultaVer("distinct FueraLineaID", "Facturas inner join FactElectFueraLinea on FactElectFueraLinea.FactElectFueraLineaID=Facturas.FueraLineaID ", "FueraLineaID>0 and (EstadoSiat=0 or EstadoSiat>=3 or EstadoSiat is null) and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " and FactElectFueraLinea.ConfiguracionID =" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}
}
