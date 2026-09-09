using System;
using System.Data;
using System.Globalization;
using System.IO;
using System.Linq;
using System.Runtime.CompilerServices;
using System.Threading;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlDetalleCuentaIntermediaria
{
	public bool ImprimirCuenta(int VisitaID, int MesaId, int meseroID)
	{
		bool result;
		try
		{
			ctlDetalleCuenta obj = new ctlDetalleCuenta();
			ctlMesas ctlMesas2 = new ctlMesas();
			ctlMesas2.SetID(MesaId);
			ctlMesas2.loadMesaPorID();
			ctlMeseros ctlMeseros2 = new ctlMeseros();
			ctlMeseros2.SetMeseroID(meseroID);
			bool tieneCombo = false;
			ImprimiendoComandas.printCuentaTotalAndroid(obj.ToReturnDeudaFaltanteFromVisitaParaAndroid(VisitaID, tieneCombo), ctlMesas2.GetNombre(), ctlMeseros2.devolverNombre(), desdeCuentaTotal: true, 0, desdeFactura: false, VisitaID, 0, MesaId, "", DateAndTime.Now);
			if (meseroID > 0)
			{
				new clsLogg().Insertar("Imprimir Cuenta", "De Celular, Mesa " + ctlMesas2.GetNombre(), meseroID);
				new ctlVisitas().UpdateImprimioCuenta(VisitaID, meseroID);
			}
			result = true;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			new clsLogg().Insertar("Imprimir Cuenta", "Error " + ex2.Message, meseroID);
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public string verCuenta(int VisitaID)
	{
		checked
		{
			string result;
			try
			{
				DataTable dataTable = new ctlDetalleCuenta().ToReturnDeudaFaltanteFromVisitaParaAndroid(VisitaID, tieneCombo: false);
				string text = "";
				int num = dataTable.Rows.Count - 1;
				for (int i = 0; i <= num; i++)
				{
					text = text + dataTable.Rows[i]["Cantidad"].ToString() + " - " + dataTable.Rows[i]["Producto"].ToString() + "\r\n";
				}
				text = text + "TOTAL " + Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Compute("Sum(Debe)", "1=1")), 0)) + " Bs.";
				result = text;
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				result = "";
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	private string ReplaceFirst(string text, string search, string replace)
	{
		int num = text.IndexOf(search);
		if (num < 0)
		{
			return text;
		}
		return text.Substring(0, num) + replace + text.Substring(checked(num + search.Length));
	}

	public object MeterFacturaOnline(int TipodoctoID, string NroDocumento, string complemento, string FactNombre, double monto, double descuento, string correo, string telefono, string nroTarjeta, string productos, ref string error2, ref string dirArchivoResultado)
	{
		string error3 = "";
		ctlMeseros ctlMeseros2 = new ctlMeseros();
		object result;
		try
		{
			ctlConfiguraciones obj = new ctlConfiguraciones();
			int num = obj.devolverNroOrden();
			obj.GuardarNroOrden(checked(num + 1));
			MyProject.Application.ChangeCulture("es-BO");
			MyProject.Application.ChangeUICulture("es-BO");
			Thread.CurrentThread.CurrentCulture = new CultureInfo("es-BO");
			Thread.CurrentThread.CurrentCulture.DateTimeFormat.ShortDatePattern = "dd/MM/yyyy";
			Thread.CurrentThread.CurrentCulture.NumberFormat.CurrencyDecimalSeparator = ",";
			Thread.CurrentThread.CurrentCulture.NumberFormat.CurrencyGroupSeparator = ".";
			Thread.CurrentThread.CurrentCulture.NumberFormat.NumberDecimalSeparator = ",";
			Thread.CurrentThread.CurrentCulture.NumberFormat.NumberGroupSeparator = ".";
			int num2 = ctlMeseros2.devolver1erMesero();
			ctlVisitas ctlVisitas2 = new ctlVisitas();
			ctlVisitas2.Save(DateTime.Now, 1, 0, 0, 0, "", num2);
			int iD = ctlVisitas2.GetID();
			ctlProductos ctlProductos2 = new ctlProductos();
			string[] array = productos.Split('|');
			double num3 = 0.0;
			try
			{
				string[] array2 = array;
				foreach (string text in array2)
				{
					if (text.ToString().Trim().Length <= 0)
					{
						continue;
					}
					string[] array3 = text.ToString().Split(',');
					if (array3.Length > 0)
					{
						string codigoBarra = array3[0];
						int sector = 0;
						int productoID = ctlProductos2.cargarIdDesdeCodigo(codigoBarra, ref sector);
						int cantDecimales = 2;
						if (sector == 35)
						{
							cantDecimales = 5;
						}
						double PrecioUnit = Convert.ToDouble(VariableGeneral.toDecimalSIN(double.Parse(array3[1], CultureInfo.InvariantCulture), cantDecimales));
						double num4 = Convert.ToDouble(VariableGeneral.toDecimalSIN(double.Parse(array3[2], CultureInfo.InvariantCulture), cantDecimales));
						double Cantidad = Convert.ToDouble(VariableGeneral.toDecimalSIN(double.Parse(array3[3], CultureInfo.InvariantCulture), cantDecimales));
						ctlDetalleCuenta obj2 = new ctlDetalleCuenta();
						DateTime now = DateTime.Now;
						double Pago = Convert.ToDouble(VariableGeneral.toDecimalSIN(PrecioUnit * Cantidad - num4, cantDecimales));
						double Debe = 0.0;
						obj2.Save1(ref Cantidad, now, iD, productoID, ref PrecioUnit, ref Pago, ref Debe, Cerrada: true, num2, manejarStock: false, "", pedidoEspera: false, (nroTarjeta.Length == 0) ? 1 : 3, num2, "", 0, esAnticipo: false, 0, 0, 0, 1, ParaLLevar: false, 0, nroTarjeta);
						num3 += Convert.ToDouble(VariableGeneral.toDecimalSIN(PrecioUnit * Cantidad - num4, cantDecimales));
					}
				}
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				error2 = "Problema con los productos enviados";
				result = false;
				ProjectData.ClearProjectError();
				goto end_IL_000c;
			}
			string factImpresoraFisico = new ctlImpresoras().DevolverImprimirFacturaFisico();
			string codigoCUF = "";
			if (configuration.gTipoFacturacion != 2)
			{
				goto IL_03c8;
			}
			if (decimal.Compare(VariableGeneral.toDecimalSIN(num3, 2), VariableGeneral.toDecimalSIN(monto + descuento, 2)) == 0)
			{
				if (new clsFactElecConfig().devolverDatosSiTokenActivo1())
				{
					double num5 = 0.0;
					num5 = new ctlDetalleCuenta().ReturnICEVisitaYconfiguracion1(iD, VariableGeneral.gConfiguracionID, 0, 14);
					num5 = Conversions.ToDouble(VariableGeneral.NZ(num5, 0));
					if (!SaveFacturaFisicaElectronica(NroDocumento, complemento, FactNombre, monto, num5, ctlVisitas2.GetID(), factImpresoraFisico, null, ref error3, 0.0, descuento, ctlMeseros2.GetMeseroID(), TipodoctoID, 0, 0, correo, ref codigoCUF))
					{
						error3 = error3 + "FactElec " + error3;
						error2 += error3;
					}
				}
				else
				{
					error3 = "No hay token activo ";
				}
				goto IL_03c8;
			}
			error2 = "Montos no coinciden: total " + Conversions.ToString(monto) + " vs sumatoria detalle " + Conversions.ToString(VariableGeneral.toDecimalSIN(num3, 2));
			result = false;
			goto end_IL_000c;
			IL_03c8:
			if (error2.Length > 0)
			{
				new clsLogg().Insertar("Meter Factura Online", "De dll, Error: " + error2, ctlMeseros2.GetMeseroID());
				dirArchivoResultado = "";
				result = false;
			}
			else
			{
				ctlFacturas obj3 = new ctlFacturas();
				int DocumentoSector = 0;
				bool factura = false;
				int num6 = obj3.visitatieneFactura(iD, ref factura, ref DocumentoSector);
				if (num6 > 0)
				{
					dirArchivoResultado = "C:\\Restotech\\Facturas\\" + Conversions.ToString(num6) + "-" + codigoCUF + ".pdf";
				}
				result = true;
			}
			end_IL_000c:;
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			error2 = error2 + error3 + "--" + ex4.Message;
			new clsLogg().Insertar("Meter Factura Online", "De dll, Error: " + error2, ctlMeseros2.GetMeseroID());
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool MeterEnLaCuenta(Guid PedidoID, ref string error2)
	{
		if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.SirPieper) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.LolaHuari))
		{
			VariableGeneral.gSgteMesaVisible = true;
		}
		string error3 = "";
		ctlMeseros ctlMeseros2 = new ctlMeseros();
		checked
		{
			bool result;
			try
			{
				clsDetalleCuentaIntermediaria clsDetalleCuentaIntermediaria2 = new clsDetalleCuentaIntermediaria();
				ctlConfiguraciones obj = new ctlConfiguraciones();
				string sucursal = "";
				string Descripcion = "";
				bool conporcentaje = default(bool);
				double porcentaje = default(double);
				obj.DevolverPorcentaje(ref conporcentaje, ref porcentaje, ref sucursal, ref Descripcion);
				configuration.gManejaElServicio = conporcentaje;
				VariableGeneral.gProductoServicioPorcentaje = porcentaje / 100.0;
				int num = obj.devolverNroOrden();
				obj.GuardarNroOrden(num + 1);
				clsDetalleCuentaIntermediaria2._pedidoID = PedidoID.ToString("D");
				DataTable dataTable = clsDetalleCuentaIntermediaria2.ToReturnXpedido(ref error3);
				ctlVisitas ctlVisitas2 = new ctlVisitas();
				ctlMesas ctlMesas2;
				ctlDetalleCuenta ctlDetalleCuenta2;
				string txtNit;
				string cboNombre;
				string factImpresoraFisico;
				double num2;
				int num3;
				bool flag2;
				int tipoDoctumentoID;
				if (dataTable.Rows.Count == 0)
				{
					new clsLogg().Insertar("Meter en cuenta", "no habia nada en el pedido", 1);
					result = false;
				}
				else
				{
					bool flag;
					if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["VisitaID"]), 0), 0, TextCompare: false))
					{
						if (ctlVisitas2.ToLoadLastVisitaActivaByMesaID(Conversions.ToInteger(dataTable.Rows[0]["MesaID"])))
						{
							flag = true;
							dataTable.Rows[0]["VisitaID"] = ctlVisitas2.GetID();
						}
						else
						{
							flag = false;
							ctlVisitas2.SetID(0);
							ctlVisitas2.Save(DateAndTime.Now, Conversions.ToInteger(dataTable.Rows[0]["MesaID"]), 0, 0, 0, "", Conversions.ToInteger(dataTable.Rows[0]["tomoPedidoID"]));
						}
					}
					else
					{
						flag = true;
						ctlVisitas2.SetID(Conversions.ToInteger(dataTable.Rows[0]["VisitaID"]));
					}
					ctlMeseros2.SetMeseroID(Conversions.ToInteger(dataTable.Rows[0]["tomoPedidoID"]));
					ctlMesas2 = new ctlMesas();
					ctlMesas2.SetID(Conversions.ToInteger(dataTable.Rows[0]["MesaID"]));
					ctlDetalleCuenta2 = new ctlDetalleCuenta();
					txtNit = "";
					cboNombre = "";
					factImpresoraFisico = "";
					num2 = 0.0;
					num3 = -1;
					flag2 = false;
					tipoDoctumentoID = 1;
					int num4 = dataTable.Rows.Count - 1;
					for (int i = 0; i <= num4; i++)
					{
						dataTable.Rows[i]["Observaciones"] = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["Observaciones"]), "").ToString().Replace("'", "`");
						string text = dataTable.Rows[i]["Producto"].ToString().ToUpper();
						if ((Operators.CompareString(text, "FACTURA PARCIAL", TextCompare: false) == 0) | (Operators.CompareString(text, "FACTURA TOTAL", TextCompare: false) == 0) | (Operators.CompareString(text, "FACTURA PARCIAL CI", TextCompare: false) == 0) | (Operators.CompareString(text, "FACTURA TOTAL CI", TextCompare: false) == 0) | (Operators.CompareString(text, "FACTURA PARCIAL NIT", TextCompare: false) == 0) | (Operators.CompareString(text, "FACTURA TOTAL NIT", TextCompare: false) == 0))
						{
							num3 = i;
							if ((Operators.CompareString(text, "FACTURA TOTAL", TextCompare: false) == 0) | (Operators.CompareString(text, "FACTURA TOTAL CI", TextCompare: false) == 0) | (Operators.CompareString(text, "FACTURA TOTAL NIT", TextCompare: false) == 0))
							{
								flag2 = true;
							}
							if (text.Contains(" CI"))
							{
								tipoDoctumentoID = 1;
							}
							else if (text.Contains(" NIT"))
							{
								tipoDoctumentoID = 5;
							}
							factImpresoraFisico = new ctlImpresoras().devolverNombreFisicoPorNombre(dataTable.Rows[i]["Impresora"].ToString());
							if (ctlMesas2.GetID() > 0)
							{
								string text2 = new ctlSalones().devolverImpresoraFActuraOverride(ctlMesas2.GetID());
								if (text2.Length > 0)
								{
									factImpresoraFisico = text2;
								}
							}
							string[] array = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["Observaciones"]), "").ToString().Split('-');
							if (array.Length <= 1)
							{
								txtNit = VariableGeneral._sinNit2;
								cboNombre = VariableGeneral._SinNombre2;
							}
							else
							{
								txtNit = array[0].Trim();
								cboNombre = array[1].Trim();
							}
						}
						else if (Operators.CompareString(dataTable.Rows[i]["Producto"].ToString().ToUpper(), "DESCRIPCION MESA", TextCompare: false) == 0)
						{
							ctlMesas2.setDescripcion(Conversions.ToString(dataTable.Rows[i]["Observaciones"]));
						}
						else if (Operators.CompareString(dataTable.Rows[i]["Producto"].ToString().ToUpper(), "CANT PERSONAS", TextCompare: false) == 0)
						{
							ctlVisitas2.ModificarObservacion(Conversions.ToString(dataTable.Rows[i]["Cantidad"]));
							ImprimiendoComandas._ImprimiendoObs = Conversions.ToString(dataTable.Rows[i]["Cantidad"]);
						}
						else
						{
							if (!Operators.ConditionalCompareObjectGreater(dataTable.Rows[i]["Cantidad"], 0, TextCompare: false))
							{
								continue;
							}
							num2 = Conversions.ToDouble(Operators.AddObject(num2, Operators.MultiplyObject(dataTable.Rows[i]["Cantidad"], dataTable.Rows[i]["Precio"])));
							if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["ProductosCombo"]), "").ToString().Length > 0)
							{
								string text3 = Conversions.ToString(NewLateBinding.LateGet(NewLateBinding.LateGet(dataTable.Rows[i]["ProductosCombo"], null, "Replace", new object[2] { " ", "" }, null, null, null), null, "Replace", new object[2] { " ", "" }, null, null, null));
								DataTable dataTable2 = BD.ConsultaVer("Preparaciones.PreparacionID, Cantidad, dbo.PreparacionesComodines.DeProductoID as opciones, 0 as escogido", "(Productos inner join Preparaciones on Preparaciones.ParaProductoID=Productos.id) inner join PreparacionesComodines on Preparaciones.PreparacionID=PreparacionesComodines.PreparacionID ", Conversions.ToString(Operators.ConcatenateObject("id=", dataTable.Rows[i]["ProductoID"])), "Preparaciones.PreparacionID, cantidad");
								string[] array2 = text3.Split(',');
								for (int j = 0; j < array2.Length; j++)
								{
									string[] array3 = array2[j].ToString().Split('-');
									if (array3[0].Length == 0)
									{
										continue;
									}
									int num5 = dataTable2.Rows.Count - 1;
									for (int k = 0; k <= num5; k++)
									{
										if (Operators.ConditionalCompareObjectLess(Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Compute("sum(escogido)", Conversions.ToString(Operators.ConcatenateObject("PreparacionID = ", dataTable2.Rows[k]["PreparacionID"])))), 0)), dataTable2.Rows[k]["cantidad"], TextCompare: false) && Operators.ConditionalCompareObjectEqual(dataTable2.Rows[k]["opciones"], array3[0], TextCompare: false))
										{
											DataRow dataRow;
											(dataRow = dataTable2.Rows[k])["escogido"] = Operators.AddObject(dataRow["escogido"], 1);
											break;
										}
									}
								}
								text3 = "," + text3 + ",";
								DataTable dataTable3 = new DataView(dataTable2).ToTable(true, "PreparacionID", "Cantidad");
								int num6 = dataTable3.Rows.Count - 1;
								for (int l = 0; l <= num6; l++)
								{
									int num7 = Conversions.ToInteger(dataTable3.Rows[l]["Cantidad"]);
									int num8 = Convert.ToInt32(RuntimeHelpers.GetObjectValue(dataTable2.Compute("SUM(escogido)", Conversions.ToString(Operators.ConcatenateObject("PreparacionID = ", dataTable3.Rows[l]["PreparacionID"])))));
									if (num8 == num7)
									{
										continue;
									}
									int num9 = num7 - num8;
									if (num9 <= 0)
									{
										continue;
									}
									int num10 = num9 - 1;
									for (int m = 0; m <= num10; m++)
									{
										int num11 = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Compute("Min(opciones)", Conversions.ToString(Operators.ConcatenateObject("escogido>0 and PreparacionID = ", dataTable3.Rows[l]["PreparacionID"])))), 0));
										if (num11 == 0)
										{
											num11 = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Compute("Min(opciones)", Conversions.ToString(Operators.ConcatenateObject("PreparacionID = ", dataTable3.Rows[l]["PreparacionID"])))), 0));
											text3 = text3 + Conversions.ToString(num11) + ",";
											continue;
										}
										text3 = ReplaceFirst(text3, "," + Conversions.ToString(num11) + ",", "," + Conversions.ToString(num11) + "," + Conversions.ToString(num11) + ",");
									}
								}
								text3 = text3.Substring(1, text3.Length - 2);
								dataTable.Rows[i]["ProductosCombo"] = text3;
							}
							ctlDetalleCuenta2.SetID(0);
							int num12 = Conversions.ToInteger(dataTable.Rows[i]["DocumentoSector"]);
							int cantDecimales = 2;
							if (num12 == 35)
							{
								cantDecimales = 5;
							}
							if (!dataTable.Rows[i]["Producto"].ToString().Trim().All([SpecialName] (char c) => c == '-'))
							{
								double Cantidad = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["Cantidad"]), cantDecimales));
								DateTime now = DateAndTime.Now;
								int iD = ctlVisitas2.GetID();
								int productoID = Conversions.ToInteger(dataTable.Rows[i]["ProductoID"]);
								double Precio = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["Precio"]), cantDecimales));
								double Pago = 0.0;
								double Debe = Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.MultiplyObject(dataTable.Rows[i]["Cantidad"], dataTable.Rows[i]["Precio"]), cantDecimales));
								ctlDetalleCuenta2.SaveCelular(ref Cantidad, now, iD, productoID, ref Precio, ref Pago, ref Debe, Cerrada: false, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["Observaciones"]), "")), Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["AsistenteID"]), "")), Conversions.ToBoolean(dataTable.Rows[i]["ManejarStock"]), Conversions.ToInteger(dataTable.Rows[i]["tomoPedidoID"]), Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["ProductosCombo"]), "")), Conversions.ToInteger(dataTable.Rows[i]["AlmacenID"]), adicionaPrecioExtraCombos: true, num, ParaLLevar: false);
							}
							dataTable.Rows[i]["DetalleID"] = ctlDetalleCuenta2.GetID();
						}
					}
					if (configuration.gManejaElServicio)
					{
						int iD2 = ctlVisitas2.GetID();
						double Debe = 0.0;
						double Pago = 0.0;
						ctlDetalleCuenta2.recalcularElServicio(iD2, con10porcent: true, ref Debe, ref Pago);
					}
					if (!recalcularDescuento(ctlVisitas2.GetID(), ctlVisitas2.DevolverclienteID(ctlVisitas2.GetID())))
					{
						error3 += "problema en descuento";
					}
					if ((num3 > -1) & (dataTable.Rows.Count == 1))
					{
						dataTable.Rows.RemoveAt(num3);
						goto IL_0e12;
					}
					if (num3 > -1)
					{
						dataTable.Rows.RemoveAt(num3);
					}
					ctlMesas2.loadMesaPorID();
					string text4 = ctlMeseros2.devolverNombre();
					string text5 = "";
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vulcanica)
					{
						text5 = ctlMesas2.GetDescripcion();
					}
					bool aumentoEnCuenta = flag;
					string personaQueRecoge = text5;
					string nombre = ctlMesas2.GetNombre();
					int iD3 = ctlVisitas2.GetID();
					string direccion = error3;
					string error4 = "";
					if (ImprimiendoComandas.printComandas(dataTable, aumentoEnCuenta, num, personaQueRecoge, paraLlevar: false, text4, "Mesa", nombre, imprimir: true, iD3, direccion, ref error4))
					{
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Stigma)
						{
							ctlMesas2.loadMesaPorID();
							ImprimiendoComandas.printCuentaTotalAndroid(dataTable, ctlMesas2.GetNombre(), text4, desdeCuentaTotal: false, num, desdeFactura: false, ctlVisitas2.GetID(), 0, ctlMesas2.GetID(), "", DateAndTime.Now);
						}
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia)
						{
							ImprimiendoComandas.printTicketComidaRapida(dataTable, aumentoEnCuenta: false, num, text5, paraLlevar: false, ctlMeseros2.devolverNombre(), "Mesa", ctlMesas2.GetNombre(), imprimir: true, 0.0, TodoSeparado: false, SopaEnTaper: false, porCredito: false, ctlVisitas2.GetID());
						}
						goto IL_0e12;
					}
					error3 += "printComandas-";
					error3 += error3;
					error2 += error3;
					new clsLogg().Insertar("Meter en Cuenta", "De Celular, Error: " + error2, ctlMeseros2.GetMeseroID());
					result = false;
				}
				goto end_IL_002d;
				IL_0e12:
				clsDetalleCuentaIntermediaria2.EliminarXpedidoID();
				if (num3 > -1)
				{
					if (configuration.gTipoFacturacion == 1)
					{
						if (flag2)
						{
							if (Conversions.ToBoolean(Operators.NotObject(SaveFacturaFisicaFSV(txtNit, cboNombre, ctlDetalleCuenta2.ReturnMontoTotalVisita(ctlVisitas2.GetID()), 0.0, ctlVisitas2.GetID(), factImpresoraFisico, null, ref error3, 0.0, 0.0, ctlMeseros2.GetMeseroID()))))
							{
								error3 = "SFV FACT Total " + error3;
								error2 += error3;
							}
						}
						else if (dataTable.Rows.Count > 0 && Conversions.ToBoolean(Operators.NotObject(SaveFacturaFisicaFSV(txtNit, cboNombre, num2, 0.0, ctlVisitas2.GetID(), factImpresoraFisico, dataTable, ref error3, 0.0, 0.0, ctlMeseros2.GetMeseroID()))))
						{
							error3 = "SFV FACT Total " + error3;
							error2 += error3;
						}
					}
					else if (configuration.gTipoFacturacion == 2)
					{
						string codigoCUF = "";
						if (new clsFactElecConfig().devolverDatosSiTokenActivo1())
						{
							if (flag2)
							{
								if (!SaveFacturaFisicaElectronica(txtNit, "", cboNombre, ctlDetalleCuenta2.ReturnMontoTotalVisita(ctlVisitas2.GetID()), 0.0, ctlVisitas2.GetID(), factImpresoraFisico, null, ref error3, 0.0, 0.0, ctlMeseros2.GetMeseroID(), tipoDoctumentoID, 0, ctlMesas2.GetID(), "", ref codigoCUF))
								{
									error3 = "SIAT 1 " + error3;
									error2 += error3;
								}
							}
							else
							{
								clsPagos clsPagos2 = new clsPagos();
								int maxAgruparPagoID = clsPagos2.getMaxAgruparPagoID();
								int num13 = dataTable.Rows.Count - 1;
								for (int num14 = 0; num14 <= num13; num14++)
								{
									if (Operators.ConditionalCompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num14]["DetalleID"]), 0), 0, TextCompare: false))
									{
										clsPagos2._DetalleCuentaID = Conversions.ToInteger(dataTable.Rows[num14]["DetalleID"]);
										clsPagos2._Fecha = DateAndTime.Now;
										clsPagos2._MaquinaPago = "Solo Factura";
										clsPagos2._cuentaID = 1;
										clsPagos2._MontoBs = Conversions.ToDouble(Operators.MultiplyObject(dataTable.Rows[num14]["Cantidad"], dataTable.Rows[num14]["Precio"]));
										clsPagos2.Insertar(maxAgruparPagoID, ctlMeseros2.GetMeseroID());
									}
								}
								if (dataTable.Rows.Count > 0 && !SaveFacturaFisicaElectronica(txtNit, "", cboNombre, num2, 0.0, 0, factImpresoraFisico, dataTable, ref error3, 0.0, 0.0, ctlMeseros2.GetMeseroID(), tipoDoctumentoID, maxAgruparPagoID, ctlMesas2.GetID(), "", ref codigoCUF))
								{
									error3 = "SIAT 2 " + error3;
									error2 += error3;
								}
							}
						}
					}
				}
				if (error2.Length > 0)
				{
					new clsLogg().Insertar("Meter en Cuenta", "De Celular, Error: " + error2, ctlMeseros2.GetMeseroID());
					result = false;
				}
				else
				{
					result = true;
				}
				end_IL_002d:;
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				error2 = error2 + error3 + "--" + ex2.Message;
				new clsLogg().Insertar("Meter en Cuenta", "De Celular, Error: " + error2, ctlMeseros2.GetMeseroID());
				result = false;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public bool MeterEnLaCuentaDeliveryPlataforma(string PedidoID, int meseroId, ref string error2, ref int VisitaID, int Plataforma, string nombreIntegracion)
	{
		string text = Plataforma switch
		{
			5 => "YAIGO", 
			4 => "PerformancePHP", 
			3 => "GastroVentures", 
			2 => "RESTOMENU", 
			1 => "PEDIDOS YA", 
			_ => "", 
		};
		string error3 = "";
		checked
		{
			bool result;
			if (meseroId == 0)
			{
				error2 = "No puso el codigo acceso del personal encargado";
				new clsLogg().Insertar("Meter delivery", error2, meseroId);
				result = false;
			}
			else
			{
				try
				{
					clsDetalleCuentaIntermediaria clsDetalleCuentaIntermediaria2 = new clsDetalleCuentaIntermediaria();
					ctlConfiguraciones obj = new ctlConfiguraciones();
					int num = obj.devolverNroOrden();
					obj.GuardarNroOrden(num + 1);
					clsDetalleCuentaIntermediaria2._pedidoID = PedidoID;
					DataTable dataTable = ((Plataforma != 3) ? BD.ConsultaVer("*", "DeliveryApp", "Order_no = " + PedidoID + " and plataforma=" + Conversions.ToString(Plataforma)) : BD.ConsultaVer("*", "DeliveryApp", "Order_no = '" + PedidoID + "' and plataforma=" + Conversions.ToString(Plataforma)));
					while (dataTable.Rows.Count > 1)
					{
						BD.ConsultaEliminar("DeliveryApp", Conversions.ToString(Operators.ConcatenateObject("DeliveryID=", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[1]["DeliveryID"]), 0))));
						dataTable = ((Plataforma != 3) ? BD.ConsultaVer("*", "DeliveryApp", "Order_no = " + PedidoID + " and plataforma=" + Conversions.ToString(Plataforma)) : BD.ConsultaVer("*", "DeliveryApp", "Order_no = '" + PedidoID + "' and plataforma=" + Conversions.ToString(Plataforma)));
					}
					if (dataTable.Rows.Count <= 0)
					{
						goto IL_01fb;
					}
					int num2 = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["VISITA_ID"]), 0));
					if (num2 <= 0)
					{
						goto IL_01fb;
					}
					error2 = "El pedido " + PedidoID + " ya fue ingresado en la visita " + Conversions.ToString(num2);
					result = false;
					goto end_IL_006d;
					IL_01fb:
					DataTable dataTable2 = clsDetalleCuentaIntermediaria2.ToReturnXpedidoPlataforma(Plataforma, ref error3, meseroId);
					if (dataTable2 == null)
					{
						new clsLogg().Insertar("Meter delivery", "Problema cargando datos", meseroId);
						Interaction.MsgBox("Problema cargando datos");
						result = false;
					}
					else
					{
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PerformancePHP)
						{
							double num3 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ORDER_TOTAL"]), 0));
							double num4 = 0.0;
							int num5 = dataTable2.Rows.Count - 1;
							for (int i = 0; i <= num5; i++)
							{
								num4 = Conversions.ToDouble(Operators.AddObject(num4, Operators.MultiplyObject(dataTable2.Rows[i]["Cantidad"], dataTable2.Rows[i]["Precio"])));
							}
							if (num4 != num3)
							{
								Thread.Sleep(1000);
								dataTable2 = clsDetalleCuentaIntermediaria2.ToReturnXpedidoPlataforma(Plataforma, ref error3, meseroId);
								num4 = 0.0;
								int num6 = dataTable2.Rows.Count - 1;
								for (int j = 0; j <= num6; j++)
								{
									num4 = Conversions.ToDouble(Operators.AddObject(num4, Operators.MultiplyObject(dataTable2.Rows[j]["Cantidad"], dataTable2.Rows[j]["Precio"])));
								}
								if (num4 != num3)
								{
									Interaction.MsgBox("Error trayendo todos los items del pedido " + PedidoID + " / " + VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Delivery_name"]), "").ToString().ToUpper() + ". verificar manualmente que este todo bien");
								}
							}
						}
						ctlVisitas ctlVisitas2 = new ctlVisitas();
						if (dataTable2.Rows.Count == 0)
						{
							error2 += "No hay items en el pedido";
							new clsLogg().Insertar("Meter delivery", error2, meseroId);
							result = false;
						}
						else
						{
							bool aumentoEnCuenta = false;
							ctlVisitas2.SetID(0);
							ctlParaLLevar ctlParaLLevar2 = new ctlParaLLevar();
							ctlParaLLevar2.SetParaLlevarID(0);
							DateTime dateTime = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PICKUP_TIME"]), DateAndTime.Now));
							Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Order_type"]), 0));
							if (dataTable.Rows[0]["Delivery_phone"].ToString().Length > 20)
							{
								dataTable.Rows[0]["Delivery_phone"] = dataTable.Rows[0]["Delivery_phone"].ToString().Substring(0, 20);
							}
							ctlParaLLevar2.GuardarParaLlevar(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Delivery_name"]), "")), Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Bill_name"]), "")), Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"]), "")), Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PICKUP_TIME"]), DateAndTime.Now)), Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Delivery_phone"]), "")), Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DELIVERY_ADDRESS_1"]), ""), " "), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DELIVERY_ADDRESS_2"]), ""))), 0, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DELIVERY_NOTES"]), "")), Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["LATY_NUM"]), 0)), Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["LONX_NUM"]), 0)), Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CHECKOUT_METHOD"]), "")), Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["order_email"]), "")));
							if (Operators.ConditionalCompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DELIVERY_TOTAL"]), 0), 0, TextCompare: false))
							{
								ctlParaLLevar2.UpdateMontoDelivery(Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DELIVERY_TOTAL"]), 0)));
							}
							ctlVisitas2.SetID(0);
							ctlVisitas2.Save(DateAndTime.Now, 0, 0, ctlParaLLevar2.GetParaLlevarID(), 0, "", meseroId);
							VisitaID = ctlVisitas2.GetID();
							if (Plataforma == 3)
							{
								BD.ConsultaModificar("DeliveryApp", "VISITA_ID=" + Conversions.ToString(VisitaID), "Order_no = '" + PedidoID + "' and plataforma=" + Conversions.ToString(Plataforma));
							}
							else
							{
								BD.ConsultaModificar("DeliveryApp", "VISITA_ID=" + Conversions.ToString(VisitaID), "Order_no = " + PedidoID + " and plataforma=" + Conversions.ToString(Plataforma));
							}
							ctlTipoEnvios ctlTipoEnvios2 = new ctlTipoEnvios();
							ctlVisitas2.setTipoEnvio(ctlTipoEnvios2.DevolverTipoEnvio(text));
							if (ctlTipoEnvios2.DevolverDeliveryExterno(text) != 0)
							{
								int num7 = dataTable2.Rows.Count - 1;
								for (int k = 0; k <= num7; k++)
								{
									if (Operators.CompareString(dataTable2.Rows[k]["Producto"].ToString().ToUpper().Trim(), "DELIVERY", TextCompare: false) == 0)
									{
										ctlParaLLevar2.UpdateMontoDelivery(Conversions.ToDouble(dataTable2.Rows[k]["Precio"]));
										dataTable2.Rows.RemoveAt(k);
										break;
									}
								}
							}
							ctlMeseros ctlMeseros2 = new ctlMeseros();
							ctlMeseros2.SetMeseroID(meseroId);
							double num8 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Discounts"]), 0));
							ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
							Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"]), ""));
							Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["BILL_NAME"]), ""));
							int maxAgruparPagoID = new clsPagos().getMaxAgruparPagoID();
							int num9 = dataTable2.Rows.Count - 1;
							for (int l = 0; l <= num9; l++)
							{
								if (!Operators.ConditionalCompareObjectGreater(dataTable2.Rows[l]["Cantidad"], 0, TextCompare: false))
								{
									continue;
								}
								ctlDetalleCuenta2.SetID(0);
								if (Conversions.ToBoolean(Operators.OrObject(Operators.CompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CHECKOUT_METHOD"]), "Efectivo"), "Pago online", TextCompare: false), Plataforma == 3)))
								{
									if (num8 > 0.0)
									{
										double num10 = Conversions.ToDouble(Operators.MultiplyObject(dataTable2.Rows[l]["Cantidad"], dataTable2.Rows[l]["Precio"]));
										double num11 = 0.0;
										double num12 = 0.0;
										if (num10 <= num8)
										{
											num11 = num10;
											num8 -= num10;
										}
										else
										{
											num11 = num8;
											num12 = num10 - num11;
											num8 = 0.0;
										}
										int num13 = Conversions.ToInteger(dataTable2.Rows[l]["DocumentoSector"]);
										int cantDecimales = 2;
										if (num13 == 35)
										{
											cantDecimales = 5;
										}
										double Cantidad = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Cantidad"]), cantDecimales));
										DateTime now = DateAndTime.Now;
										int iD = ctlVisitas2.GetID();
										int productoID = Conversions.ToInteger(dataTable2.Rows[l]["ProductoID"]);
										double Precio = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Precio"]), cantDecimales));
										double Pago = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Debe"]), cantDecimales));
										double Debe = 0.0;
										ctlDetalleCuenta2.SaveCelular(ref Cantidad, now, iD, productoID, ref Precio, ref Pago, ref Debe, Cerrada: true, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Observaciones"]), "")), "", Conversions.ToBoolean(dataTable2.Rows[l]["ManejarStock"]), meseroId, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ProductosCombo"]), "")), Conversions.ToInteger(dataTable2.Rows[l]["AlmacenID"]), adicionaPrecioExtraCombos: false, num, ParaLLevar: true);
										if (num11 > 0.0)
										{
											clsPagos obj2 = new clsPagos();
											obj2._Fecha = DateAndTime.Now;
											obj2._MaquinaPago = (text + " " + nombreIntegracion).Trim();
											obj2._DetalleCuentaID = ctlDetalleCuenta2.GetID();
											obj2._Descuento = 0.0;
											ctlTipoEnvios ctlTipoEnvios3 = new ctlTipoEnvios();
											obj2._cuentaID = ctlTipoEnvios3.devolverCuentaCupones(text);
											obj2._MontoBs = num11;
											obj2._transaccionId = 0;
											obj2.Insertar(maxAgruparPagoID, meseroId);
										}
										if (num12 > 0.0)
										{
											clsPagos obj3 = new clsPagos();
											obj3._Fecha = DateAndTime.Now;
											obj3._MaquinaPago = (text + " " + nombreIntegracion).Trim();
											obj3._DetalleCuentaID = ctlDetalleCuenta2.GetID();
											obj3._Descuento = 0.0;
											ctlTipoEnvios ctlTipoEnvios4 = new ctlTipoEnvios();
											obj3._cuentaID = ctlTipoEnvios4.devolverCuentaTarjeta(text);
											obj3._MontoBs = num12;
											obj3._transaccionId = 0;
											obj3.Insertar(maxAgruparPagoID, meseroId);
										}
									}
									else if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ProductoID"]), 0), 0, TextCompare: false))
									{
										Interaction.MsgBox(Operators.ConcatenateObject("esta mal seteado un producto de la cuenta de ", dataTable.Rows[0]["Delivery_name"]));
									}
									else
									{
										int num14 = Conversions.ToInteger(dataTable2.Rows[l]["DocumentoSector"]);
										int cantDecimales2 = 2;
										if (num14 == 35)
										{
											cantDecimales2 = 5;
										}
										double Debe = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Cantidad"]), cantDecimales2));
										DateTime now2 = DateAndTime.Now;
										int iD2 = ctlVisitas2.GetID();
										int productoID2 = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ProductoID"]), 0));
										double Pago = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Precio"]), cantDecimales2));
										double Precio = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Debe"]), cantDecimales2));
										double Cantidad = 0.0;
										ctlDetalleCuenta2.SaveCelular(ref Debe, now2, iD2, productoID2, ref Pago, ref Precio, ref Cantidad, Cerrada: true, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Observaciones"]), "")), "", Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ManejarStock"]), false)), meseroId, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ProductosCombo"]), "")), Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["AlmacenID"]), 1)), adicionaPrecioExtraCombos: false, num, ParaLLevar: true);
										clsPagos clsPagos2 = new clsPagos();
										clsPagos2._Fecha = DateAndTime.Now;
										clsPagos2._MaquinaPago = (text + " " + nombreIntegracion).Trim();
										clsPagos2._DetalleCuentaID = ctlDetalleCuenta2.GetID();
										clsPagos2._Descuento = 0.0;
										if (Plataforma == 3)
										{
											ctlCuentas ctlCuentas2 = new ctlCuentas();
											clsPagos2._cuentaID = ctlCuentas2.devolverCuentaIdPorNombreActivas(Conversions.ToString(dataTable.Rows[0]["CHECKOUT_METHOD"]));
										}
										else
										{
											ctlTipoEnvios ctlTipoEnvios5 = new ctlTipoEnvios();
											clsPagos2._cuentaID = ctlTipoEnvios5.devolverCuentaTarjeta(text);
										}
										clsPagos2._MontoBs = Conversions.ToDouble(dataTable2.Rows[l]["Debe"]);
										clsPagos2._transaccionId = 0;
										clsPagos2.Insertar(maxAgruparPagoID, meseroId);
									}
								}
								else if (num8 > 0.0)
								{
									if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ProductoID"]), 0), 0, TextCompare: false))
									{
										error2 = Conversions.ToString(Operators.ConcatenateObject(error2, Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("No hay SKU para el producto ", dataTable2.Rows[l]["Producto"]), ","), "\r")));
										continue;
									}
									double num15 = Conversions.ToDouble(Operators.MultiplyObject(dataTable2.Rows[l]["Cantidad"], dataTable2.Rows[l]["Precio"]));
									double num16 = 0.0;
									double num17 = 0.0;
									if (num15 <= num8)
									{
										num16 = num15;
										num8 -= num15;
									}
									else
									{
										num16 = num8;
										num17 = num15 - num16;
										num8 = 0.0;
									}
									int num18 = Conversions.ToInteger(dataTable2.Rows[l]["DocumentoSector"]);
									int cantDecimales3 = 2;
									if (num18 == 35)
									{
										cantDecimales3 = 5;
									}
									double Cantidad = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Cantidad"]), cantDecimales3));
									DateTime now3 = DateAndTime.Now;
									int iD3 = ctlVisitas2.GetID();
									int productoID3 = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ProductoID"]), 0));
									double Precio = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Precio"]), 0)), cantDecimales3));
									double Pago = Convert.ToDouble(VariableGeneral.toDecimalSIN(num16, cantDecimales3));
									double Debe = Convert.ToDouble(VariableGeneral.toDecimalSIN(num17, cantDecimales3));
									ctlDetalleCuenta2.SaveCelular(ref Cantidad, now3, iD3, productoID3, ref Precio, ref Pago, ref Debe, num17 == 0.0, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Observaciones"]), "")), "", Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ManejarStock"]), 0)), meseroId, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ProductosCombo"]), "")), Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["AlmacenID"]), 1)), adicionaPrecioExtraCombos: false, num, ParaLLevar: true);
									if (num16 > 0.0)
									{
										clsPagos obj4 = new clsPagos();
										obj4._Fecha = DateAndTime.Now;
										obj4._MaquinaPago = (text + " " + nombreIntegracion).Trim();
										obj4._DetalleCuentaID = ctlDetalleCuenta2.GetID();
										obj4._Descuento = 0.0;
										ctlTipoEnvios ctlTipoEnvios6 = new ctlTipoEnvios();
										obj4._cuentaID = ctlTipoEnvios6.devolverCuentaCupones(text);
										obj4._MontoBs = num16;
										obj4._transaccionId = 0;
										obj4.Insertar(maxAgruparPagoID, meseroId);
									}
								}
								else if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ProductoID"]), 0), 0, TextCompare: false))
								{
									error2 = Conversions.ToString(Operators.ConcatenateObject(error2, Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("No hay SKU para el producto ", dataTable2.Rows[l]["Producto"]), ","), "\r")));
								}
								else
								{
									int num19 = Conversions.ToInteger(dataTable2.Rows[l]["DocumentoSector"]);
									int cantDecimales4 = 2;
									if (num19 == 35)
									{
										cantDecimales4 = 5;
									}
									double Debe = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Cantidad"]), cantDecimales4));
									DateTime now4 = DateAndTime.Now;
									int iD4 = ctlVisitas2.GetID();
									int productoID4 = Conversions.ToInteger(dataTable2.Rows[l]["ProductoID"]);
									double Pago = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Precio"]), 0)), cantDecimales4));
									double Precio = 0.0;
									double Cantidad = Convert.ToDouble(VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Debe"]), 0)), cantDecimales4));
									ctlDetalleCuenta2.SaveCelular(ref Debe, now4, iD4, productoID4, ref Pago, ref Precio, ref Cantidad, Cerrada: false, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["Observaciones"]), "")), "", Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ManejarStock"]), 0)), meseroId, Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["ProductosCombo"]), "")), Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[l]["AlmacenID"]), 1)), adicionaPrecioExtraCombos: false, num, ParaLLevar: true);
								}
							}
							switch (Plataforma)
							{
							case 3:
							{
								string mesero = ctlMeseros2.devolverNombre();
								bool tieneCombo3 = Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "ProductosCombos", "ProductosCombos.DetalleCuentaID in (select id from DetalleCuenta where visitaId=" + Conversions.ToString(VisitaID) + ")").Rows[0][0], 0, TextCompare: false);
								dataTable2 = ctlDetalleCuenta2.ToReturnCuentaDeudaFaltanteFromVisitaDelivery(ctlVisitas2.GetID(), tieneCombo3);
								ImprimiendoComandas.printCuentaTotalAndroid(dataTable2, Conversions.ToString(dataTable.Rows[0]["Delivery_name"]), mesero, desdeCuentaTotal: false, num, desdeFactura: true, ctlVisitas2.GetID(), ctlParaLLevar2.GetParaLlevarID(), 0, (text + " " + nombreIntegracion).Trim(), DateAndTime.Now);
								break;
							}
							default:
							{
								if (DateTime.Compare(dateTime, DateAndTime.Today.AddDays(1.0).AddHours(7.0)) >= 0)
								{
									ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
									bool encontro = false;
									string text2 = ctlImpresoras2.devolverImpresoraCuentaFisico();
									if (Operators.CompareString(text2, "", TextCompare: false) == 0)
									{
										text2 = ctlImpresoras2.DevolverImprimirFacturaFisico();
									}
									if (Operators.CompareString(text2, "", TextCompare: false) == 0)
									{
										break;
									}
									PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, text2, ref encontro, "RestotechParaOtrodia");
									if (encontro)
									{
										printerClass.BigFont();
										printerClass.AlignCenter();
										printerClass.WriteLine((text + " " + nombreIntegracion).Trim());
										printerClass.AlignLeft();
										printerClass.WriteLine("");
										printerClass.NormalBiggerFont();
										printerClass.WriteLine("\r\nPara otro día: " + VariableGeneral.ArmarFecha(dateTime));
										printerClass.WriteLine("");
										printerClass.WriteLine(Conversions.ToString(Operators.ConcatenateObject("Para:", dataTable.Rows[0]["Delivery_name"])));
										printerClass.WriteLine("");
										printerClass.WriteLine(Conversions.ToString(Operators.ConcatenateObject("Dir.:", dataTable.Rows[0]["DELIVERY_ADDRESS_1"])));
										printerClass.WriteLine("");
										printerClass.DrawLine();
										printerClass.CutPaper();
										printerClass.EndDoc();
										bool tieneCombo = Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "ProductosCombos", "ProductosCombos.DetalleCuentaID in (select id from DetalleCuenta where visitaId=" + Conversions.ToString(VisitaID) + ")").Rows[0][0], 0, TextCompare: false);
										dataTable2 = ctlDetalleCuenta2.ToReturnCuentaDeudaFaltanteFromVisitaDelivery(ctlVisitas2.GetID(), tieneCombo);
										ImprimiendoComandas.printCuentaTotalAndroid(dataTable2, Conversions.ToString(dataTable.Rows[0]["Delivery_name"]), ctlMeseros2.devolverNombre(), desdeCuentaTotal: false, num, desdeFactura: true, ctlVisitas2.GetID(), ctlParaLLevar2.GetParaLlevarID(), 0, (text + " " + nombreIntegracion).Trim(), DateAndTime.Now);
										if (new ctlConfiguraciones().devolverDobleCuenta())
										{
											ImprimiendoComandas.printCuentaTotalAndroid(dataTable2, Conversions.ToString(dataTable.Rows[0]["Delivery_name"]), ctlMeseros2.devolverNombre(), desdeCuentaTotal: false, num, desdeFactura: true, ctlVisitas2.GetID(), ctlParaLLevar2.GetParaLlevarID(), 0, (text + " " + nombreIntegracion).Trim(), DateAndTime.Now);
										}
									}
									break;
								}
								string text3 = ctlMeseros2.devolverNombre();
								TimeSpan timeSpan = dateTime - DateAndTime.Now;
								int num20 = 60;
								string text4;
								if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen)
								{
									text4 = dateTime.ToString("HH:mm");
									num20 = 30;
								}
								else
								{
									text4 = "Entrega a las " + dateTime.ToString("HH:mm");
								}
								DataTable dataTable3 = ctlDetalleCuenta2.ToReturnPedidoCompleto(ctlVisitas2.GetID());
								if (dataTable3 == null)
								{
									dataTable3 = dataTable2;
								}
								else if (dataTable3.Rows.Count != dataTable2.Rows.Count)
								{
									Interaction.MsgBox("Avise al administador, revisar el pedido Id " + Conversions.ToString(ctlVisitas2.GetID()) + " la cantidad de productos es diferente al pedido inicial");
								}
								DataTable dgvPedido = dataTable3;
								string personaQueRecoge = ((timeSpan.TotalMinutes > (double)num20) ? text4 : "");
								string lblMesa = (text + " " + nombreIntegracion).Trim();
								string txtMesa = Conversions.ToString(dataTable.Rows[0]["Delivery_name"]);
								int iD5 = ctlVisitas2.GetID();
								string direccion = error3;
								string error4 = "";
								if (ImprimiendoComandas.printComandas(dgvPedido, aumentoEnCuenta, num, personaQueRecoge, paraLlevar: true, text3, lblMesa, txtMesa, imprimir: true, iD5, direccion, ref error4))
								{
									bool tieneCombo2 = Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "ProductosCombos", "ProductosCombos.DetalleCuentaID in (select id from DetalleCuenta where visitaId=" + Conversions.ToString(VisitaID) + ")").Rows[0][0], 0, TextCompare: false);
									dataTable2 = ctlDetalleCuenta2.ToReturnCuentaDeudaFaltanteFromVisitaDelivery(ctlVisitas2.GetID(), tieneCombo2);
									if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Bracan)
									{
										ImprimiendoComandas.printCuentaTotalAndroid(dataTable2, Conversions.ToString(dataTable.Rows[0]["Delivery_name"]), text3, desdeCuentaTotal: false, num, desdeFactura: true, ctlVisitas2.GetID(), ctlParaLLevar2.GetParaLlevarID(), 0, (text + " " + nombreIntegracion).Trim(), DateAndTime.Now);
									}
									if (new ctlConfiguraciones().devolverDobleCuenta())
									{
										ImprimiendoComandas.printCuentaTotalAndroid(dataTable2, Conversions.ToString(dataTable.Rows[0]["Delivery_name"]), text3, desdeCuentaTotal: false, num, desdeFactura: true, ctlVisitas2.GetID(), ctlParaLLevar2.GetParaLlevarID(), 0, (text + " " + nombreIntegracion).Trim(), DateAndTime.Now);
									}
									break;
								}
								error2 = error2 + "No imprimio - " + error3;
								new clsLogg().Insertar("Meter delivery", error2, meseroId);
								result = false;
								goto end_IL_006d;
							}
							case 4:
								break;
							}
							error2 += error3;
							result = true;
						}
					}
					end_IL_006d:;
				}
				catch (Exception ex)
				{
					ProjectData.SetProjectError(ex);
					Exception ex2 = ex;
					error2 = error2 + error3 + "--" + ex2.Message;
					new clsLogg().Insertar("Meter delivery", error2, meseroId);
					result = false;
					ProjectData.ClearProjectError();
				}
			}
			return result;
		}
	}

	public bool SaveFacturaFisicaElectronica(string txtNit, string txtComplemento, string cboNombre, double Monto, double IceMonto, int visitaID, string FactImpresoraFisico, DataTable dtFacturar, ref string error1, double AcumuladoPago, double descuento, int meseroID, int tipoDoctumentoID, int AgruparPagoID, int mesaID, string txtCorreo, ref string codigoCUF1)
	{
		bool result;
		try
		{
			DateTime now = DateTime.Now;
			now = now.AddMilliseconds(checked(now.Millisecond * -1));
			ctlFacturas ctlFacturas2 = new ctlFacturas();
			if ((Conversions.ToDouble(txtNit) == 0.0) | (Operators.CompareString(txtNit, "0", TextCompare: false) == 0))
			{
				txtNit = VariableGeneral._sinNit2;
			}
			if ((Operators.CompareString(txtNit, VariableGeneral._sinNit2, TextCompare: false) == 0) & (cboNombre.Length == 0))
			{
				cboNombre = VariableGeneral._SinNombre2;
			}
			if (cboNombre.Trim().Length == 0)
			{
				ctlFacturas obj = new ctlFacturas();
				string email = "";
				string nit = txtNit;
				string telefono = "";
				int tipoDocumentoID = default(int);
				obj.getUltimoNombreEmailTelefonoPorNIT(nit, ref cboNombre, ref email, ref telefono, ref tipoDocumentoID);
				tipoDoctumentoID = tipoDocumentoID;
				if (txtCorreo.Length == 0)
				{
					txtCorreo = email;
				}
				if (cboNombre.Trim().Length == 0)
				{
					cboNombre = VariableGeneral._SinNombre2;
				}
			}
			cboNombre = cboNombre.Replace("'", "`");
			string text = ((txtNit.Length == 0) ? VariableGeneral._sinNit2 : txtNit);
			string text2 = ((cboNombre.Length == 0) ? VariableGeneral._SinNombre2 : cboNombre);
			if (Operators.CompareString(text, "99002", TextCompare: false) == 0)
			{
				text2 = "CONTROL TRIBUTARIO";
			}
			if (Operators.CompareString(text, "99003", TextCompare: false) == 0)
			{
				text2 = "VENTAS MENORES DEL DIA";
			}
			if ((Operators.CompareString(text, "99001", TextCompare: false) == 0) | (Operators.CompareString(text, "99002", TextCompare: false) == 0) | (Operators.CompareString(text, "99003", TextCompare: false) == 0))
			{
				tipoDoctumentoID = 5;
			}
			if (Operators.CompareString(text, VariableGeneral._sinNit2, TextCompare: false) == 0)
			{
				tipoDoctumentoID = 4;
				if (visitaID > 0)
				{
					text = Conversions.ToString(visitaID);
					if (visitaID.ToString().Length < 11)
					{
						text = visitaID.ToString().PadLeft(11, '9');
					}
				}
				else if (AgruparPagoID > 0)
				{
					text = Conversions.ToString(AgruparPagoID);
					if (AgruparPagoID.ToString().Length < 11)
					{
						text = AgruparPagoID.ToString().PadLeft(11, '9');
					}
				}
				if (Operators.CompareString(text, "0", TextCompare: false) == 0)
				{
					text = "7";
				}
			}
			else if ((text.Length < 4) & (tipoDoctumentoID == 1))
			{
				tipoDoctumentoID = 4;
			}
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			long nIT;
			string codigoCUIS;
			string codigoCUFD;
			int cufdID;
			int num;
			clsFactElecContingencias clsFactElecContingencias2;
			float num3;
			int leyId;
			string Ley;
			string cafc;
			int num4;
			int codigoMotivoEvento;
			bool nitValidado;
			bool flag;
			ctlConfiguraciones ctlConfiguraciones2;
			DateTime dateTime = default(DateTime);
			string text3;
			int num2;
			int num5 = default(int);
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				nIT = clsFactElecConfig2.NIT;
				if (DateTime.Compare(clsFactElecConfig2.TokenVigencia, now) <= 0)
				{
					error1 = "No hay codigo Token activo, Por favor revise la vigencia, expiro el " + VariableGeneral.ArmarFechaSTR(clsFactElecConfig2.TokenVigencia);
					result = false;
				}
				else
				{
					clsFactElecObtencionCodigos clsFactElecObtencionCodigos2 = new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente);
					codigoCUIS = "";
					codigoCUFD = "";
					cufdID = 0;
					string CodigoControl = "";
					ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
					DataTable dataTable = ((visitaID > 0) ? ctlDetalleCuenta2.TieneDiferentesDosificacionesYsectores(visitaID) : ((AgruparPagoID <= 0) ? new DataTable() : ctlDetalleCuenta2.TieneDiferentesDosificacionesAgrupadorIDYsectores(AgruparPagoID)));
					if (dataTable.Rows.Count > 1)
					{
						error1 = "Esa venta tiene mas de un sector, no deberia!";
						result = false;
					}
					else
					{
						num = Conversions.ToInteger(dataTable.Rows[0][1]);
						clsFactElecSectorCompraVenta clsFactElecSectorCompraVenta2 = new clsFactElecSectorCompraVenta((int)clsFactElecConfig2.CodigoAmbiente, num, (int)clsFactElecConfig2.CodigoModalidad);
						clsFactElecContingencias2 = new clsFactElecContingencias();
						new ctlFacturas();
						num2 = 0;
						num3 = 0f;
						num3 = Conversions.ToSingle(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(pagos.MontoBs) as MontoGiftCard ", " (Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID) inner join DetalleCuenta on DetalleCuenta.id = Pagos.DetalleCuentaID", "Cuentas.EsGiftCard = " + VariableGeneral.armarBolean(1) + " and DetalleCuenta.VisitaID  = " + Conversions.ToString(visitaID)).Rows[0][0]), 0));
						if ((double)num3 > Monto)
						{
							num3 = (float)Monto;
						}
						leyId = 0;
						Ley = "";
						cafc = "";
						num4 = 0;
						codigoMotivoEvento = 0;
						nitValidado = false;
						flag = false;
						ctlConfiguraciones2 = new ctlConfiguraciones();
						try
						{
							clsFactElecObtencionCodigos2.ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
							DateTime t = now;
							if (!clsFactElecObtencionCodigos2.ObtenerCUFD(ref codigoCUFD, ref CodigoControl, ref now, ref cufdID, desdeCelular: true))
							{
								clsCUFD clsCUFD2 = new clsCUFD();
								if (clsCUFD2.obtenerUltimoCUFD(clsFactElecConfig2.CodigoPuntoVenta))
								{
									codigoCUFD = clsCUFD2.codigoCUFD;
									CodigoControl = clsCUFD2.codigoControl;
									dateTime = clsCUFD2.FechaHasta.AddMinutes(-5.0);
									cufdID = clsCUFD2.FactElectCUFDID;
									flag = true;
								}
							}
							if (codigoCUFD.Length == 0 && flag)
							{
								error1 = "No se puede emitir porque no hay CUFDiario, deben haber problemas tecnicos, revise su internet, intente mas tarde";
								result = false;
							}
							else
							{
								string error2 = "";
								string telefono;
								if (clsFactElecSectorCompraVenta2.verificarComunicacion(ref error2))
								{
									num2 = 1;
									if (DateTime.Compare(t, now) != 0)
									{
										now = DateTime.Now.AddMinutes(1.0);
										now = now.AddMilliseconds(checked(now.Millisecond * -1));
									}
									num5 = clsFactElecConfig2.obtenerSgteNroFactura();
									if (tipoDoctumentoID == 5)
									{
										clsFactElecObtencionCodigos clsFactElecObtencionCodigos3 = new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente);
										if (!((Operators.CompareString(text, "99001", TextCompare: false) == 0) | (Operators.CompareString(text, "99002", TextCompare: false) == 0) | (Operators.CompareString(text, "99003", TextCompare: false) == 0)))
										{
											string nIT2 = txtNit;
											telefono = "";
											switch (clsFactElecObtencionCodigos3.verificarNIT1(nIT2, ref telefono))
											{
											case 1:
												nitValidado = true;
												break;
											case 0:
												tipoDoctumentoID = 1;
												break;
											default:
												tipoDoctumentoID = 1;
												nitValidado = false;
												break;
											}
										}
									}
									goto IL_05fb;
								}
								new clsLogg().Insertar("verificar Comunicacion Electronica", "no hay comunicacion. " + error2, 0);
								num2 = 2;
								DateTime inicio = now;
								if (DateTime.Compare(t, now) != 0)
								{
									inicio = DateTime.Now.AddMinutes(2.0);
									now = inicio.AddSeconds(20.0);
									now = now.AddMilliseconds(checked(now.Millisecond * -1));
								}
								telefono = "";
								num4 = clsFactElecContingencias2.checkearContingenciaFueraDeLinea(ref codigoCUFD, ref inicio, ref cafc, ref codigoMotivoEvento, ref telefono, 0);
								nitValidado = false;
								if (num4 == 0)
								{
									inicio = now;
									if (flag)
									{
										inicio = dateTime;
									}
									num4 = clsFactElecContingencias2.CrearContingenciaFueraDeLinea(inicio, 1, codigoCUFD, "", cufdID, desdeCelular: true);
								}
								if (!((num4 > 0) & (cafc.Length > 0)))
								{
									num5 = clsFactElecConfig2.obtenerSgteNroFactura();
									if (!Directory.Exists("C:\\Restotech\\Xml\\" + Conversions.ToString(num4)))
									{
										Directory.CreateDirectory("C:\\Restotech\\Xml\\" + Conversions.ToString(num4));
									}
									goto IL_05fb;
								}
								error1 = "No puede mandar Contingencias con codigo CAFC por aca";
								result = false;
							}
							goto end_IL_0390;
							IL_05fb:
							new ctlFactElectLeyes().DevolverRandomLey("0", ref leyId, ref Ley, (int)clsFactElecConfig2.CodigoAmbiente);
							int codigoModalidad = (int)clsFactElecConfig2.CodigoModalidad;
							int tipoFactura = 1;
							if (num == 8)
							{
								tipoFactura = 2;
							}
							text3 = (codigoCUF1 = new ctlFacturaElectronicaAlgoritmo().GenerarCUF(nIT, now, clsFactElecConfig2.codigoSucursal, codigoModalidad, num2, tipoFactura, num, num5, clsFactElecConfig2.CodigoPuntoVenta, CodigoControl));
							goto IL_06ee;
							end_IL_0390:;
						}
						catch (Exception ex)
						{
							ProjectData.SetProjectError(ex);
							Exception ex2 = ex;
							checked
							{
								num5--;
								_ = (num4 > 0) & (cafc.Length > 0);
								error1 = "Error generando el codigo, hagala de nuevo. " + ex2.Message + "\rNro Fact: " + Conversions.ToString(num5 + 1) + ", Nit: " + text + ", Monto: " + Conversions.ToString(Monto) + ", Config: " + Conversions.ToString(VariableGeneral.gConfiguracionID);
								result = false;
								ProjectData.ClearProjectError();
							}
						}
					}
				}
			}
			else
			{
				error1 = "No hay codigo Token activo";
				result = false;
			}
			goto end_IL_0000;
			IL_0d55:
			try
			{
				string autorizacion = "0";
				string text4 = "";
				if (configuration.gFormatoFacturaGrande)
				{
					goto end_IL_0d55;
				}
				ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
				string text5 = new ctlSalones().devolverImpresoraFActuraOverride(mesaID);
				text4 = ((text5.Length <= 0) ? ctlImpresoras2.DevolverImprimirFacturaFisico() : text5);
				bool facturandoAnticipadamente = true;
				if (text4.Length > 0)
				{
					error1 = ImprimiendoComandas.printFacturaNewFormat(num5, Conversions.ToString(nIT), text2, text + " " + txtComplemento, "", 0, visitaID, AgruparPagoID, "Bs", Monto, IceMonto, text3, "", now, esCopia: false, text4, null, facturandoAnticipadamente, 0.0, ImprimirEnArchivo: false, "", 0.0, meseroID, Ley, num4 > 0, clsFactElecConfig2.CodigoPuntoVenta, "", num);
					if (error1.Length > 0)
					{
						new clsLogg().Insertar("Imprimir Factura", "De Celular. " + error1, meseroID);
					}
				}
				if (!((txtCorreo.Length > 0) | ctlConfiguraciones2.DevolverFacturaBackupArchivo()))
				{
					goto end_IL_0d55;
				}
				string text6 = "";
				text6 = MyProject.Application.Info.DirectoryPath + "\\Facturas\\";
				if (!configuration.gFormatoFacturaGrande && File.Exists(text6 + Conversions.ToString(num5) + "-" + text3 + ".pdf"))
				{
					File.Delete(text6 + Conversions.ToString(num5) + "-" + text3 + ".pdf");
				}
				string text7 = "";
				if (!configuration.gFormatoFacturaGrande)
				{
					text7 = ImprimiendoComandas.printFacturaNewFormat(num5, Conversions.ToString(nIT), text2, text + " " + txtComplemento, autorizacion, 0, visitaID, 0, "Bs", Monto, IceMonto, text3, "", now, esCopia: false, text4, null, facturandoAnticipadamente, 0.0, ImprimirEnArchivo: true, "", 0.0, meseroID, Ley, num4 > 0, clsFactElecConfig2.CodigoPuntoVenta, "", num);
				}
				if (text7.Length > 0)
				{
					error1 = text7;
				}
				else
				{
					VariableGeneral.FileReadyToRead(text6 + Conversions.ToString(num5) + "-" + text3 + ".pdf", 45);
				}
				if (File.Exists(text6 + Conversions.ToString(num5) + "-" + text3 + ".pdf"))
				{
					bool flag2 = false;
					if (txtCorreo.Length > 0)
					{
						string text8 = "";
						flag2 = ImprimiendoComandas.sendEmailFactura(link: (clsFactElecConfig2.CodigoAmbiente != clsFactElecConfig.FactAmbiente.Produccion) ? ("https://pilotosiat.impuestos.gob.bo/consulta/QR?nit=" + Conversions.ToString(nIT) + "&cuf=" + text3 + "&numero=" + Conversions.ToString(num5) + "&t=" + Conversions.ToString((!configuration.gFormatoFacturaGrande) ? 1 : 2)) : ("https://siat.impuestos.gob.bo/consulta/QR?nit=" + Conversions.ToString(nIT) + "&cuf=" + text3 + "&numero=" + Conversions.ToString(num5) + "&t=" + Conversions.ToString((!configuration.gFormatoFacturaGrande) ? 1 : 2)), emailTo: txtCorreo, archivoXML: MyProject.Application.Info.DirectoryPath + "\\Xml\\Factura.xml", archivoFactura: text6 + Conversions.ToString(num5) + "-" + text3 + ".pdf", Modalidad: (int)clsFactElecConfig2.CodigoModalidad, NombreFactura: text2, NroFactura: Conversions.ToString(num5), FechaEmision: now, esFactura: true);
						if (flag2)
						{
							ctlFacturas2.EmailEnviado();
						}
					}
					if (ctlConfiguraciones2.DevolverFacturaBackupArchivo())
					{
						if (flag2)
						{
						}
					}
					else if (flag2)
					{
						File.Delete(text6 + Conversions.ToString(num5) + "-" + text3 + ".pdf");
					}
					else if (num4 > 0)
					{
						File.Move(text6 + Conversions.ToString(num5) + "-" + text3 + ".pdf", "Xml\\" + Conversions.ToString(num4) + "\\Factura" + Conversions.ToString(num5) + ".pdf");
					}
					goto end_IL_0d55;
				}
				error1 = "No encontro el pdf";
				result = false;
				goto end_IL_0000;
				end_IL_0d55:;
			}
			catch (Exception ex3)
			{
				ProjectData.SetProjectError(ex3);
				Exception ex4 = ex3;
				error1 = "Error imprimiendo factura. " + ex4.Message;
				result = false;
				ProjectData.ClearProjectError();
				goto end_IL_0000;
			}
			goto IL_1236;
			IL_06ee:
			checked
			{
				try
				{
					new clsFactElectLeyes();
					if (ctlFacturas2.InsertarFactura(text, text2, now, num5, text3, Monto, IceMonto, visitaID, 0, "", AgruparPagoID, descuento, factura2: false, num3, txtCorreo, txtComplemento, "", leyId, tipoDoctumentoID, num4, cufdID, num))
					{
						goto end_IL_06ee;
					}
					num5--;
					_ = (num4 > 0) & (cafc.Length > 0);
					error1 = "Por alguna razon no se guardo esta factura";
					result = false;
					goto end_IL_0000;
					end_IL_06ee:;
				}
				catch (Exception ex5)
				{
					ProjectData.SetProjectError(ex5);
					Exception ex6 = ex5;
					num5--;
					_ = (num4 > 0) & (cafc.Length > 0);
					error1 = "Error guardando la factura";
					result = false;
					ProjectData.ClearProjectError();
					goto end_IL_0000;
				}
			}
			try
			{
				MemoryStream ResultadoStream = new MemoryStream();
				clsCreateXMLFactura clsCreateXMLFactura2 = new clsCreateXMLFactura();
				string strXmlUtf = "";
				bool num6 = clsCreateXMLFactura2.crearXMLcompraVentaComputarizada(num, (int)clsFactElecConfig2.CodigoModalidad, ref ResultadoStream, ref strXmlUtf, text3, codigoCUFD, Conversions.ToString(clsFactElecConfig2.codigoSucursal), Conversions.ToString(nIT), now, text2, text, txtComplemento, Monto, IceMonto, num5, meseroID, visitaID, dtFacturar, AgruparPagoID, facturandoAnticipadamente: false, clsFactElecConfig2.CodigoPuntoVenta, (num2 == 2) ? (Conversions.ToString(num4) + "\\Factura" + num5) : "", num3, cafc, descuento, tipoDoctumentoID, num4 > 0, Ley, nitValidado, ref error1, desdeCelular: true);
				string errores = error1;
				if (num6)
				{
					if (!Conversions.ToBoolean(clsCreateXMLFactura2.validarXML(strXmlUtf, ref errores, num, (int)clsFactElecConfig2.CodigoModalidad, desdeCelular: true)))
					{
						ctlFacturas2.eliminarFactura();
						num5 = checked(num5 - 1);
						_ = (num4 > 0) & (cafc.Length > 0);
						ResultadoStream.Dispose();
						ResultadoStream.Close();
						ResultadoStream = null;
						error1 = "Problema con el xml, " + errores;
						result = false;
					}
					else
					{
						if (num2 != 1)
						{
							goto IL_0d55;
						}
						ctlFacturaElectronicaAlgoritmo ctlFacturaElectronicaAlgoritmo2 = new ctlFacturaElectronicaAlgoritmo();
						byte[] streamResult = null;
						if (ctlFacturaElectronicaAlgoritmo2.CompressGZIPmemory(ResultadoStream.ToArray(), ref streamResult))
						{
							string algoritmoHashSHA = ctlFacturaElectronicaAlgoritmo2.getAlgoritmoHashSHA256(streamResult);
							clsFactElecSectorCompraVenta clsFactElecSectorCompraVenta3 = new clsFactElecSectorCompraVenta((int)clsFactElecConfig2.CodigoAmbiente, num, (int)clsFactElecConfig2.CodigoModalidad);
							int tipoFacturaDocumento = 1;
							switch (num)
							{
							case 8:
								tipoFacturaDocumento = 2;
								break;
							case 24:
								tipoFacturaDocumento = 3;
								break;
							}
							int errorID = 0;
							string codigoRecepcion = "";
							if (Conversions.ToBoolean(clsFactElecSectorCompraVenta3.RecepcionFactura1(num2, codigoCUFD, codigoCUIS, tipoFacturaDocumento, num, streamResult, algoritmoHashSHA, now, ref errorID, ref error1, ref codigoRecepcion)))
							{
								ctlFacturas2.setcodigoRecepcion(codigoRecepcion);
								int estado = 0;
								if (Conversions.ToBoolean(clsFactElecSectorCompraVenta3.ValidacionFactura1(num2, codigoCUFD, codigoCUIS, tipoFacturaDocumento, num, text3, ref estado, ref error1)))
								{
									ctlFacturas2.setEstado(estado);
									if (estado != 1)
									{
										new clsLogg().Insertar("Factura Electronica", "No se valido la factura  " + Conversions.ToString(num5) + " con monto " + Conversions.ToString(Monto) + " de la maquina " + MyProject.Computer.Name, meseroID);
										string telefono = "";
										DateTime inicio2 = default(DateTime);
										num4 = clsFactElecContingencias2.checkearContingenciaFueraDeLinea(ref codigoCUFD, ref inicio2, ref cafc, ref codigoMotivoEvento, ref telefono, 0);
										if (num4 == 0)
										{
											inicio2 = now;
											if (flag)
											{
												inicio2 = dateTime;
											}
											if (DateTime.Compare(inicio2, now) >= 0)
											{
												inicio2 = now.AddSeconds(-2.0);
											}
											num4 = clsFactElecContingencias2.CrearContingenciaFueraDeLinea(inicio2, 1, codigoCUFD, "", cufdID, desdeCelular: true);
										}
										ctlFacturas2.setContingenciaID(num4);
									}
									else
									{
										new clsLogg().Insertar("Factura Electronica", "Se emitio la factura  " + Conversions.ToString(num5) + " con monto " + Conversions.ToString(Monto) + " por celular", meseroID);
									}
								}
								else
								{
									new clsLogg().Insertar("Factura Electronica", "No se valido la factura  " + Conversions.ToString(num5) + " con monto " + Conversions.ToString(Monto) + " por celular", meseroID);
									num2 = 2;
									string telefono = "";
									DateTime inicio3 = default(DateTime);
									num4 = clsFactElecContingencias2.checkearContingenciaFueraDeLinea(ref codigoCUFD, ref inicio3, ref cafc, ref codigoMotivoEvento, ref telefono, 0);
									if (num4 == 0)
									{
										inicio3 = now;
										if (flag)
										{
											inicio3 = dateTime;
										}
										if (DateTime.Compare(inicio3, now) >= 0)
										{
											inicio3 = now.AddSeconds(-2.0);
										}
										num4 = clsFactElecContingencias2.CrearContingenciaFueraDeLinea(inicio3, 1, codigoCUFD, "", cufdID, desdeCelular: true);
									}
									ctlFacturas2.setContingenciaID(num4);
								}
							}
							else
							{
								if ((errorID == 1) | (errorID == 2) | (errorID == 3))
								{
									Interaction.MsgBox(error1 + "\r\nNo se consiguio enviar la factura " + Conversions.ToString(num5) + ". Entraremos en Contingencia. error ID " + Conversions.ToString(errorID));
									new clsLogg().Insertar("Factura Electronica", "No se recepciono la factura  " + Conversions.ToString(num5) + " con monto " + Conversions.ToString(Monto) + " de la maquina " + MyProject.Computer.Name + ". Error " + error1, meseroID);
								}
								else
								{
									error1 = error1 + "No se consiguio enviar la factura " + Conversions.ToString(num5) + ". Entraremos en Contingencia.";
									new clsLogg().Insertar("Factura Electronica", "No se recepciono la factura  " + Conversions.ToString(num5) + " con monto " + Conversions.ToString(Monto) + " de la maquina " + MyProject.Computer.Name + ". " + error1, meseroID);
								}
								num2 = 2;
								DateTime inicio4 = now.AddSeconds(-2.0);
								string telefono = "";
								num4 = clsFactElecContingencias2.checkearContingenciaFueraDeLinea(ref codigoCUFD, ref inicio4, ref cafc, ref codigoMotivoEvento, ref telefono, 0);
								if (num4 == 0)
								{
									inicio4 = now;
									if (flag)
									{
										inicio4 = dateTime;
									}
									if (DateTime.Compare(inicio4, now) >= 0)
									{
										inicio4 = now.AddSeconds(-2.0);
									}
									int codigoEvento = 1;
									if (errorID == 1)
									{
										codigoEvento = 2;
									}
									num4 = clsFactElecContingencias2.CrearContingenciaFueraDeLinea(inicio4, codigoEvento, codigoCUFD, "", cufdID, desdeCelular: false);
								}
								ctlFacturas2.setContingenciaID(num4);
							}
							goto IL_0d55;
						}
						ResultadoStream.Dispose();
						ResultadoStream.Close();
						ResultadoStream = null;
						error1 = "No comprimio";
						result = false;
					}
				}
				else
				{
					ctlFacturas2.eliminarFactura();
					num5 = checked(num5 - 1);
					_ = (num4 > 0) & (cafc.Length > 0);
					ResultadoStream.Dispose();
					ResultadoStream.Close();
					ResultadoStream = null;
					error1 = "Problema creando el xml, fact " + Conversions.ToString(num5) + errores;
					result = false;
				}
			}
			catch (Exception ex7)
			{
				ProjectData.SetProjectError(ex7);
				Exception ex8 = ex7;
				error1 = "Algun error no controlado? avisar a Toptech " + ex8.Message;
				result = false;
				ProjectData.ClearProjectError();
			}
			end_IL_0000:;
		}
		catch (Exception ex9)
		{
			ProjectData.SetProjectError(ex9);
			Exception ex10 = ex9;
			Interaction.MsgBox(ex10.Message);
			result = false;
			ProjectData.ClearProjectError();
		}
		goto IL_1238;
		IL_1236:
		result = true;
		goto IL_1238;
		IL_1238:
		return result;
	}

	public object SaveFacturaFisicaFSV(string txtNit, string cboNombre, double Monto, double IceMonto, int visitaID, string FactImpresoraFisico, DataTable dtFacturar, ref string error1, double AcumuladoPago, double descuento, int meseroID)
	{
		object result;
		try
		{
			int documentoSector = 1;
			DateTime now = DateAndTime.Now;
			ctlFacturas ctlFacturas2 = new ctlFacturas();
			if (ctlFacturas2.HayFacturasDespuesDeFecha(now))
			{
				error1 = "Hay facturas despues de fecha";
				result = false;
			}
			else
			{
				string llave = "";
				if ((txtNit.Length == 0) | (Operators.CompareString(txtNit, "0", TextCompare: false) == 0))
				{
					txtNit = VariableGeneral._sinNit2;
				}
				if ((Operators.CompareString(txtNit, VariableGeneral._sinNit2, TextCompare: false) == 0) & (cboNombre.Length == 0))
				{
					cboNombre = VariableGeneral._SinNombre2;
				}
				if (cboNombre.Length == 0)
				{
					cboNombre = new ctlFacturas().getNombrePorNIT(txtNit);
					if (cboNombre.Length == 0)
					{
						cboNombre = VariableGeneral._SinNombre2;
					}
				}
				string nit = "";
				string text = "";
				string fechaLimiteEmision = "";
				string value = Conversions.ToString(Value: false);
				ctlCodigosFacturas ctlCodigosFacturas2 = new ctlCodigosFacturas();
				DateTime today = DateAndTime.Today;
				bool Activo = Conversions.ToBoolean(value);
				long autorizacion = default(long);
				bool num = ctlCodigosFacturas2.DevolverCodigoActivo(today, ref llave, ref autorizacion, ref nit, ref fechaLimiteEmision, ref Activo);
				value = Conversions.ToString(Activo);
				if (num)
				{
					int num2 = ctlCodigosFacturas2.obtenerSgteNroFactura();
					try
					{
						text = new CodigoControl().generar1(monto: Conversions.ToString(Math.Round(Convert.ToDouble(Monto), MidpointRounding.AwayFromZero)), autorizacion: Conversions.ToString(autorizacion), numero: Conversions.ToString(num2), nitci: txtNit, fecha: now.ToString("yyyyMMdd"), llave: llave);
					}
					catch (Exception ex)
					{
						ProjectData.SetProjectError(ex);
						Exception ex2 = ex;
						error1 = "no hay codigo activo";
						result = false;
						ProjectData.ClearProjectError();
						goto end_IL_0000;
					}
					try
					{
						ctlFacturas2.SetFacturaID(0);
						if (ctlFacturas2.InsertarFactura(txtNit, cboNombre, now, num2, text, Monto, IceMonto, visitaID, ctlCodigosFacturas2.GetCodigoID(), "", 0, descuento, factura2: false, 0.0, "", "", "", 0, 1, 0, 0, documentoSector))
						{
							goto end_IL_016a;
						}
						result = false;
						goto end_IL_0000;
						end_IL_016a:;
					}
					catch (Exception ex3)
					{
						ProjectData.SetProjectError(ex3);
						Exception ex4 = ex3;
						error1 = ex4.Message;
						result = false;
						ProjectData.ClearProjectError();
						goto end_IL_0000;
					}
					try
					{
						try
						{
							if (configuration.gTipoFacturacion == 2)
							{
								Conversions.ToInteger(BD.ConsultaVer("CodigoAmbiente", "FactElectConfiguracion", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID)).Rows[0][0]);
							}
						}
						catch (Exception ex5)
						{
							ProjectData.SetProjectError(ex5);
							Exception ex6 = ex5;
							ProjectData.ClearProjectError();
						}
						new ctlImpresoras();
						error1 = ImprimiendoComandas.printFacturaNewFormat(num2, nit, cboNombre, txtNit, Conversions.ToString(autorizacion), 0, visitaID, 0, "Bs", Monto, IceMonto, text, fechaLimiteEmision, now, esCopia: false, FactImpresoraFisico, dtFacturar, facturandoAnticipadamente: false, -1.0, ImprimirEnArchivo: false, "", AcumuladoPago, meseroID, "", esContingencia: false, 0, "", 1);
						result = true;
					}
					catch (Exception ex7)
					{
						ProjectData.SetProjectError(ex7);
						Exception ex8 = ex7;
						error1 = ex8.Message;
						result = false;
						ProjectData.ClearProjectError();
					}
				}
				else
				{
					error1 = "no hay codigo activo";
					result = false;
				}
			}
			end_IL_0000:;
		}
		catch (Exception ex9)
		{
			ProjectData.SetProjectError(ex9);
			Exception ex10 = ex9;
			error1 = ex10.Message;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool recalcularDescuento(int visitaID, int clienteID)
	{
		bool result;
		try
		{
			if (clienteID == 0)
			{
				result = true;
			}
			else
			{
				ctlDetalleCuenta obj = new ctlDetalleCuenta();
				double Descuento = 0.0;
				double MinimoMonto = 0.0;
				double maximoMonto = 0.0;
				string mensajeCajero = "";
				new ctlClientes().DevolverDescuento(clienteID, ref Descuento, ref MinimoMonto, ref maximoMonto, ref mensajeCajero);
				double num = obj.ReturnMontoTotalVisita(visitaID);
				if (((MinimoMonto > 0.0) | (maximoMonto > 0.0)) && maximoMonto <= num)
				{
					Descuento /= 100.0;
					Descuento = maximoMonto * Descuento * 100.0 / num;
				}
				double desc = 1.0 - Descuento / 100.0;
				obj.AplicarDescuento(visitaID, desc);
				result = true;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}
}
