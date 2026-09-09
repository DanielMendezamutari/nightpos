using System;
using System.Collections.Generic;
using System.Data;
using System.Drawing;
using System.Globalization;
using System.IO;
using System.Linq;
using System.Net;
using System.Net.Mail;
using System.Net.Security;
using System.Reflection;
using System.Runtime.CompilerServices;
using System.Runtime.InteropServices;
using System.Security.Cryptography.X509Certificates;
using System.ServiceModel;
using System.Text;
using System.Windows.Forms;
using ConfigToptech;
using ControlConsumoLib.My;
using ControlConsumoLib.Sac;
using Microsoft.Office.Interop.Excel;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;
using ThoughtWorks.QRCode.Codec;
using iText.Kernel.Geom;
using iText.Kernel.Pdf;
using iText.Layout;
using iText.Layout.Element;
using iText.Layout.Properties;

namespace ControlConsumoLib;

[StandardModule]
public sealed class ImprimiendoComandas
{
	private static DateTime _FechaExpiracion;

	private static int _nroOrdenCliente;

	public static string _ImprimiendoObs = "";

	public static bool _Ciego = false;

	public static int _TurnoID = 0;

	private static readonly clsTipoCambio clsTi = new clsTipoCambio();

	private static int _nroCuenta = 0;

	public static void SetFechaExpiracion(DateTime fecha)
	{
		_FechaExpiracion = fecha;
	}

	public static bool printCuentaTotalAndroid(System.Data.DataTable dgvTotal, string txtMesa, string mesero, bool desdeCuentaTotal, int NroOrden, bool desdeFactura, int VisitaID, int ParaLlevarID, int mesaID, string TipoEnvio, DateTime fecha)
	{
		dgvTotal.AcceptChanges();
		System.Data.DataTable dataTable = dgvTotal.Copy();
		bool encontro = false;
		ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
		if (dataTable.Rows.Count == 0)
		{
			return true;
		}
		string text = "";
		double num = 0.0;
		new clsLogg();
		float num2 = 0f;
		int num3 = 0;
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Stigma)
		{
			text = ctlImpresoras2.devolverNombreFisicoPorNombre(dataTable.Rows[0]["Impresora"].ToString());
		}
		else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspana)
		{
			text = ctlImpresoras2.devolverImpresoraCuentaFisico();
		}
		else if (desdeFactura)
		{
			text = ctlImpresoras2.DevolverImprimirFacturaFisico();
			if (Operators.CompareString(text, "", TextCompare: false) == 0)
			{
				text = ctlImpresoras2.devolverImpresoraCuentaFisico();
			}
		}
		else
		{
			text = ctlImpresoras2.devolverImpresoraCuentaFisico();
		}
		if (mesaID > 0)
		{
			string text2 = new ctlSalones().devolverImpresoraCuentaOverride(mesaID);
			if (text2.Length > 0)
			{
				text = text2;
			}
		}
		PrinterClass P = ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial) ? new PrinterClass("C:\\Restotech Bestial", text, ref encontro, "Restotech Cuenta") : (((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial)) ? new PrinterClass("C:\\Restotech", text, ref encontro, "Restotech Cuenta") : ((!File.Exists(MyProject.Application.Info.DirectoryPath + "\\Logo.bmp")) ? new PrinterClass("C:\\Restotech", text, ref encontro, "Restotech Cuenta") : new PrinterClass(MyProject.Application.Info.DirectoryPath, text, ref encontro, "Restotech Cuenta"))));
		if (!encontro)
		{
			return false;
		}
		PrinterClass printerClass = P;
		printerClass.BigFont();
		printerClass.Bold = true;
		if (configuration.gStyleBoliches1 != configuration.styleBolichesId.IrishPub)
		{
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Paradise)
			{
				printerClass.GotoSixth(2.0);
				printerClass.WriteLine("PARADISE");
				printerClass.FeedPaper(1);
			}
			else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.ComidaSuarez)
			{
				printerClass.WriteLine("COMIDA TIPICA SUAREZ");
				printerClass.FeedPaper(1);
			}
			else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Stigma)
			{
				printerClass.GotoSixth(2.0);
				printerClass.WriteLine("STIGMA");
				printerClass.FeedPaper(1);
			}
			else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.expoFood)
			{
				printerClass.GotoSixth(2.0);
				printerClass.WriteLine("TOPTECH");
				printerClass.FeedPaper(1);
			}
			else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Brasargent)
			{
				printerClass.GotoSixth(2.0);
				printerClass.WriteLine("1987");
				printerClass.FeedPaper(1);
			}
			else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Meraki)
			{
				printerClass.GotoSixth(2.0);
				printerClass.WriteLine("MERAKI");
				printerClass.FeedPaper(1);
			}
			else if (!configuration.gComidaRapida)
			{
				printerClass.PrintLogo();
			}
		}
		ctlVisitas ctlVisitas2 = new ctlVisitas();
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Rodeo)
		{
			_nroCuenta = ctlVisitas2.DevolverObservacionNroCuenta(DateAndTime.Today);
		}
		ctlVisitas2.ModificarObservacion(_nroCuenta.ToString());
		printerClass.GotoSixth(1.0);
		printerClass.UnderlineOn();
		printerClass.AlignCenter();
		if (ParaLlevarID > 0)
		{
			printerClass.WriteLine(TipoEnvio.Replace(" PeYa", "").Replace(" PeYA", ""));
		}
		else
		{
			printerClass.WriteLine("CUENTA");
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Rodeo)
		{
			printerClass.WriteLine(_nroCuenta.ToString());
		}
		printerClass.AlignLeft();
		printerClass.UnderlineOff();
		printerClass.GotoSixth(1.0);
		printerClass.NormalFont();
		printerClass.WriteLine("");
		printerClass.WriteLine("Fecha: " + fecha.ToString());
		printerClass.WriteLine("");
		printerClass.NormalBiggerFont();
		if (mesaID > 0)
		{
			printerClass.WriteLine("");
			printerClass.WriteLine("Mesa: " + txtMesa);
		}
		else
		{
			printerClass.WriteLine("");
			printerClass.WriteLine(txtMesa.ToUpper());
		}
		if (NroOrden > 0)
		{
			printerClass.WriteLine("");
			printerClass.WriteLine("Orden: " + Conversions.ToString(NroOrden));
			printerClass.WriteLine("");
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.AguaViva1)
		{
			System.Data.DataTable dataTable2 = BD.ConsultaVer("Select Nombre, Apellidos, celular, fijo from Clientes left join Visitas on visitas.ClienteID  =Clientes.ID where Visitas.ID = " + Conversions.ToString(VisitaID));
			if (dataTable2.Rows.Count > 0)
			{
				printerClass.WriteLine("");
				printerClass.NormalFont();
				printerClass.GotoSixth(1.0);
				printerClass.WriteLine("Nombre : " + dataTable2.Rows[0][0].ToString() + " " + dataTable2.Rows[0][1].ToString());
				printerClass.GotoSixth(1.0);
				printerClass.WriteLine("Telf. : " + dataTable2.Rows[0][2].ToString() + " / " + dataTable2.Rows[0][3].ToString());
				printerClass.WriteLine("");
			}
		}
		printerClass.NormalFont();
		printerClass.Bold = true;
		printerClass.GotoSixth(1.0);
		printerClass.WriteChars("Descripcion");
		printerClass.GotoSixth(4.1);
		printerClass.WriteChars("Cant.");
		printerClass.GotoSixth(5.0);
		printerClass.WriteChars("Precio");
		printerClass.GotoSixth(6.0);
		printerClass.WriteChars("Total");
		printerClass.Bold = false;
		printerClass.WriteLine("");
		printerClass.DrawLine();
		printerClass.NormalFont();
		dataTable.AcceptChanges();
		DataView dataView = new DataView(dataTable);
		dataView.Sort = "ProductoID DESC";
		ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
		System.Data.DataTable table = ctlDetalleCuenta2.ToReturnCombosVisita(VisitaID);
		checked
		{
			foreach (object item in dataView)
			{
				object objectValue = RuntimeHelpers.GetObjectValue(item);
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Tapekua) & NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null).ToString().ToUpper()
					.Contains("COVER"))
				{
					num2 = Conversions.ToSingle(Operators.AddObject(num2, NewLateBinding.LateIndexGet(objectValue, new object[1] { "Debe" }, null)));
					num3 = Conversions.ToInteger(Operators.AddObject(num3, NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null)));
					continue;
				}
				printerClass.GotoSixth(1.0);
				string text3 = "";
				System.Drawing.Font font = new System.Drawing.Font("FontA1x1", 9f);
				for (int num4 = 0; num4 < NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null).ToString().Length; num4++)
				{
					text3 += NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null).ToString().Substring(num4, 1);
					if (TextRenderer.MeasureText(text3, font).Width > 155)
					{
						text3 = text3.Substring(0, text3.Length - 2);
						break;
					}
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
				{
					printerClass.WriteLine(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null).ToString());
				}
				else
				{
					printerClass.WriteChars(text3);
				}
				printerClass.GotoSixth(4.5);
				printerClass.WriteChars(Conversions.ToDouble(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null)).ToString("##.##"));
				printerClass.GotoSixth(5.0);
				printerClass.WriteChars(Conversions.ToString(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
				{
					VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Precio" }, null)), 0),
					1
				}, null, null, null)));
				printerClass.GotoSixth(6.0);
				double value = Conversions.ToDouble(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Debe" }, null)), 0), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue, new object[1] { "pago" }, null)), 0)));
				printerClass.WriteChars(Math.Round(value, 1).ToString());
				printerClass.WriteLine("");
				num = Conversions.ToDouble(Operators.AddObject(num, Operators.MultiplyObject(NewLateBinding.LateIndexGet(objectValue, new object[1] { "cantidad" }, null), NewLateBinding.LateIndexGet(objectValue, new object[1] { "precio" }, null))));
				if (dataTable.Columns["ID"] == null)
				{
					continue;
				}
				DataView dataView2 = new DataView(table);
				dataView2.RowFilter = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("ID= '", NewLateBinding.LateIndexGet(objectValue, new object[1] { "ID" }, null)), "'"));
				foreach (object item2 in dataView2)
				{
					object objectValue2 = RuntimeHelpers.GetObjectValue(item2);
					printerClass.GotoSixth(1.2);
					string text4 = "";
					for (int num5 = 0; num5 < NewLateBinding.LateIndexGet(objectValue2, new object[1] { "CompuestoPor" }, null).ToString().Length; num5++)
					{
						text4 += NewLateBinding.LateIndexGet(objectValue2, new object[1] { "CompuestoPor" }, null).ToString().Substring(num5, 1);
						if (TextRenderer.MeasureText(text4, font).Width > 152)
						{
							text4 = text4.Substring(0, text4.Length - 2);
							break;
						}
					}
					if (!text4.Trim().ToUpper().StartsWith("RECETA"))
					{
						printerClass.WriteChars("-" + text4);
						printerClass.GotoSixth(4.5);
						printerClass.WriteChars(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null).ToString());
						printerClass.WriteLine("");
					}
				}
			}
			dataTable.AcceptChanges();
			printerClass.NormalBiggerFont();
			printerClass.DrawLine();
			printerClass.GotoSixth(1.0);
			string filter = "1=1";
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Tapekua)
			{
				filter = "Producto not like 'COVER*'";
			}
			double num6 = Convert.ToDouble(RuntimeHelpers.GetObjectValue(dataTable.Compute("Sum(Debe)", filter)));
			double num7 = Convert.ToDouble(RuntimeHelpers.GetObjectValue(dataTable.Compute("sum(pago)", filter)));
			double num8 = 0.0;
			if (configuration.gRedonderCentavos)
			{
				num8 = Conversions.ToDouble(Strings.FormatNumber(Math.Round(num6 * 2.0) / 2.0, 1));
				num8 = ((num6 > 0.0) ? num6 : 0.0);
			}
			else
			{
				num8 = Convert.ToDouble(VariableGeneral.toDecimalSIN((num6 > 0.0) ? num6 : 0.0, 2));
			}
			double num9 = 0.0;
			double num10 = 0.0;
			if (ParaLlevarID > 0)
			{
				ctlParaLLevar obj = new ctlParaLLevar();
				obj.SetParaLlevarID(ParaLlevarID);
				num9 = obj.getMontoDelivery();
				System.Data.DataTable dataTable3 = BD.ConsultaVer("select delivery_Dscto from DeliveryApp where Visita_ID=" + Conversions.ToString(VisitaID));
				if (dataTable3.Rows.Count > 0)
				{
					num10 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0][0]), 0));
				}
			}
			if (num9 > 0.0)
			{
				printerClass.WriteChars("Subtotal ");
			}
			else
			{
				printerClass.WriteChars("Total ");
			}
			printerClass.GotoSixth(5.0);
			printerClass.WriteChars(Math.Round(num8 + num7, 2) + " Bs ");
			printerClass.WriteLine("");
			if (num9 > 0.0)
			{
				printerClass.GotoSixth(1.0);
				printerClass.WriteChars("Delivery ");
				printerClass.GotoSixth(5.0);
				printerClass.WriteChars(Math.Round(num9, 2) + " Bs ");
				printerClass.WriteLine("");
				printerClass.DrawLine();
				printerClass.GotoSixth(1.0);
				printerClass.WriteChars("Total ");
				printerClass.GotoSixth(5.0);
				printerClass.WriteChars(Math.Round(num8 + num7 + num9, 2) + " Bs ");
				printerClass.WriteLine("");
			}
			if (num10 > 0.0)
			{
				printerClass.Bold = true;
				printerClass.WriteLine("");
				printerClass.GotoSixth(1.0);
				printerClass.WriteChars("Delivery Dscto");
				printerClass.GotoSixth(5.0);
				printerClass.WriteChars(Math.Round(num10, 2) + " Bs ");
				printerClass.WriteLine("");
				printerClass.Bold = false;
			}
			if (num8 + num7 + num9 != num8 + num9)
			{
				printerClass.GotoSixth(1.0);
				printerClass.Bold = true;
				printerClass.WriteChars("Por Cobrar");
				printerClass.GotoSixth(5.0);
				printerClass.WriteChars(Math.Round(num8 + num9, 2) + " Bs ");
				printerClass.WriteLine("");
				printerClass.Bold = false;
			}
			printerClass.WriteLine("");
			if (Math.Round(num, 0) > Math.Round(num6, 0))
			{
				printerClass.NormalBiggerFont();
				printerClass.Bold = true;
				printerClass.GotoSixth(1.0);
				double Descuento = 0.0;
				int num11 = ctlVisitas2.DevolverclienteID(VisitaID);
				if (num11 > 0)
				{
					ctlClientes obj2 = new ctlClientes();
					double MinimoMonto = 0.0;
					double maximoMonto = 0.0;
					string mensajeCajero = "";
					obj2.DevolverDescuento(num11, ref Descuento, ref MinimoMonto, ref maximoMonto, ref mensajeCajero);
				}
				if (Descuento > 0.0)
				{
					printerClass.MaxFont();
					printerClass.WriteLine("Descuento del " + Conversions.ToString(Descuento) + "%");
					printerClass.NormalBiggerFont();
					printerClass.WriteChars("Equivalente a " + Conversions.ToString(Math.Round(num - num6 - num7, 1)) + " Bs. ");
				}
				else if (num - num6 - num7 != 0.0)
				{
					printerClass.NormalBiggerFont();
					printerClass.WriteChars("Descuento de " + Conversions.ToString(Math.Round(num - num6 - num7, 1)) + " Bs. ");
				}
				printerClass.Bold = false;
				printerClass.WriteLine("");
				printerClass.WriteLine("");
			}
			if (!configuration.gManejaElServicio & !configuration.gComidaRapida)
			{
				if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Bocarte)
				{
					printerClass.NormalFont();
					printerClass.WriteLine("Propina no incluida.");
					printerClass.WriteLine("");
				}
				else
				{
					printerClass.NormalFont();
					printerClass.WriteLine("El servicio no viene incluido.");
					printerClass.WriteLine("");
				}
			}
			printerClass.NormalBiggerFont();
			if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo))
			{
				printerClass.NormalFont();
			}
			printerClass.Bold = true;
			printerClass.GotoSixth(1.0);
			System.Data.DataTable dataTable4 = ctlDetalleCuenta2.ToReturnPagoCuentas(VisitaID);
			int num12 = dataTable4.Rows.Count - 1;
			for (int i = 0; i <= num12; i++)
			{
				PrinterClass printerClass2 = printerClass;
				object left = Operators.ConcatenateObject(Operators.ConcatenateObject("Pagó con ", dataTable4.Rows[i]["Nombre"]), " :  ");
				object[] array;
				DataRow dataRow;
				bool[] array2;
				object right = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
				{
					(dataRow = dataTable4.Rows[i])["Monto"],
					1
				}, null, null, array2 = new bool[2] { true, false });
				if (array2[0])
				{
					dataRow["Monto"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
				}
				printerClass2.WriteChars(Conversions.ToString(Operators.ConcatenateObject(left, right)));
				printerClass.WriteLine("");
			}
			printerClass.NormalFont();
			printerClass.GotoSixth(1.0);
			string text5 = new ctlConfiguraciones().devolverComentario();
			if ((text5.Length > 0) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.LolaHuari))
			{
				printerClass.WriteLine("");
				printerClass.WriteLine(text5 ?? "");
				printerClass.WriteLine("");
			}
			printerClass.WriteChars("Gracias por su preferencia!");
			printerClass.WriteLine("");
			if (ParaLlevarID > 0)
			{
				ctlParaLLevar obj3 = new ctlParaLLevar();
				obj3.SetParaLlevarID(ParaLlevarID);
				if (VariableGeneral.gPedidosYa & TipoEnvio.ToUpper().StartsWith("PEDIDOS YA"))
				{
					clsDeliveryApp obj4 = new clsDeliveryApp();
					int PeYaid = 0;
					string orderNroByVisitaId = obj4.getOrderNroByVisitaId(VisitaID, 1, ref PeYaid);
					if (orderNroByVisitaId.Length > 1)
					{
						printerClass.WriteLine("");
						printerClass.WriteChars("PedidosYa Nro: " + orderNroByVisitaId);
						printerClass.WriteLine("");
					}
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.MariaDeMolina)
				{
					clsDeliveryApp obj5 = new clsDeliveryApp();
					int PeYaid = 0;
					string orderNroByVisitaId2 = obj5.getOrderNroByVisitaId(VisitaID, 3, ref PeYaid);
					if (orderNroByVisitaId2.Length > 1)
					{
						printerClass.WriteLine("");
						printerClass.WriteChars("Maria de Molina Nro: " + orderNroByVisitaId2);
						printerClass.WriteLine("");
					}
				}
				clsParaLLevar clsParaLLevar2 = obj3.LlenarClase();
				if (clsParaLLevar2._Motociclista > 0)
				{
					ctlMeseros obj6 = new ctlMeseros();
					obj6.SetMeseroID(clsParaLLevar2._Motociclista);
					string text6 = obj6.devolverNombre();
					printerClass.WriteLine("");
					printerClass.WriteChars("Repartidor: " + text6);
					printerClass.WriteLine("");
				}
				if (clsParaLLevar2._MetodoPago.Length > 0)
				{
					if (dataTable4.Rows.Count <= 0)
					{
						printerClass.WriteLine("");
						printerClass.WriteChars("Pagara con : " + clsParaLLevar2._MetodoPago);
					}
					printerClass.WriteLine("");
				}
				if ((clsParaLLevar2._NIT.Length > 1) | (clsParaLLevar2._NombreFactura.Length > 0))
				{
					printerClass.WriteLine("");
					printerClass.WriteChars("Fact.: " + clsParaLLevar2._NombreFactura);
					printerClass.WriteLine("");
					printerClass.WriteChars("NIT: " + clsParaLLevar2._NIT);
					printerClass.WriteLine("");
				}
				else if (desdeCuentaTotal && Conversions.ToBoolean(new ctlConfiguraciones().devolverImprimirdatosFactura()))
				{
					if (configuration.gTipoFacturacion == 2)
					{
						printerClass.WriteLine("");
						printerClass.WriteChars("Nombre ...............................................");
						printerClass.FeedPaper(2);
						printerClass.WriteLine("");
						printerClass.WriteChars("NIT .....................................................");
						printerClass.WriteLine("");
						printerClass.WriteChars("NIT(  )  CI(  )  Extranjero(  ) Pasaporte(  )");
						printerClass.WriteLine("");
						printerClass.FeedPaper(1);
						printerClass.WriteLine("");
						printerClass.WriteChars("Email .....................................................");
						printerClass.WriteLine("");
					}
					else
					{
						printerClass.WriteLine("");
						printerClass.WriteChars("Nombre ...............................................");
						printerClass.FeedPaper(2);
						printerClass.WriteLine("");
						printerClass.WriteChars("NIT .....................................................");
						printerClass.WriteLine("");
					}
				}
				if (clsParaLLevar2._Direccion.Length > 0)
				{
					string text7 = "Dir.: ";
					string text8 = clsParaLLevar2._Direccion;
					while (text8.Length > 0)
					{
						if (text8.Length < 40)
						{
							printerClass.WriteChars(text7 + text8);
							printerClass.WriteLine("");
							text8 = text8.Remove(0, text8.Length);
						}
						else
						{
							printerClass.WriteChars(text7 + text8.Substring(0, 40));
							printerClass.WriteLine("");
							text8 = text8.Remove(0, 40);
						}
						text7 = "";
					}
				}
				if (clsParaLLevar2._Telefono.Length > 0)
				{
					printerClass.WriteChars("Telf.: " + clsParaLLevar2._Telefono);
					printerClass.WriteLine("");
				}
				string text9 = "";
				text9 = ((!((clsParaLLevar2._HoraRecoger.Day == DateAndTime.Today.Day) & ((configuration.gStyleBoliches1 != configuration.styleBolichesId.Vikingo) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.UgosPizza)))) ? clsParaLLevar2._HoraRecoger.ToString("dd/MM/yy HH:mm") : clsParaLLevar2._HoraRecoger.ToString("HH:mm"));
				if (clsParaLLevar2._Direccion.Length > 0)
				{
					if (Operators.CompareString(clsParaLLevar2._Direccion, "Recogera en local", TextCompare: false) == 0)
					{
						printerClass.WriteChars("Recoge a " + text9);
						printerClass.WriteLine("");
					}
					else
					{
						printerClass.WriteChars("Entrega a " + text9);
						printerClass.WriteLine("");
					}
				}
				else
				{
					if (DateTime.Compare(clsParaLLevar2._HoraRecoger, DateAndTime.Now.AddMinutes(30.0)) > 0)
					{
						printerClass.Bold = true;
						printerClass.BigSmallerFont();
					}
					printerClass.WriteChars("Recoge a " + text9);
					printerClass.WriteLine("");
					printerClass.Bold = false;
					printerClass.NormalFont();
				}
				if ((clsParaLLevar2._laty_num != 0.0) & !TipoEnvio.ToUpper().StartsWith("PEDIDOS YA"))
				{
					printerClass.WriteChars("GPS: " + Math.Round(clsParaLLevar2._laty_num, 6).ToString().Replace(",", ".") + " , " + Math.Round(clsParaLLevar2._lonx_num, 6).ToString().Replace(",", "."));
					printerClass.WriteLine("");
					QRubicacion(pin_skipped: false, clsParaLLevar2._laty_num, clsParaLLevar2._lonx_num, ref P);
				}
				if (clsParaLLevar2._notas.Length > 0)
				{
					printerClass.WriteChars("Notas:" + clsParaLLevar2._notas);
					printerClass.WriteLine("");
				}
			}
			else
			{
				bool flag = Conversions.ToBoolean(new ctlConfiguraciones().devolverImprimirdatosFactura());
				if (flag && desdeCuentaTotal && flag && flag)
				{
					if (configuration.gTipoFacturacion == 2)
					{
						printerClass.WriteLine("");
						printerClass.WriteChars("Nombre ...............................................");
						printerClass.FeedPaper(2);
						printerClass.WriteLine("");
						printerClass.WriteChars("NIT .....................................................");
						printerClass.WriteLine("");
						printerClass.WriteChars("NIT(  )  CI(  )  Extranjero(  ) Pasaporte(  )");
						printerClass.WriteLine("");
						printerClass.FeedPaper(1);
						printerClass.WriteLine("");
						printerClass.WriteChars("Email .....................................................");
						printerClass.WriteLine("");
					}
					else
					{
						printerClass.WriteLine("");
						printerClass.WriteChars("Nombre ...............................................");
						printerClass.FeedPaper(2);
						printerClass.WriteLine("");
						printerClass.WriteChars("NIT .....................................................");
						printerClass.WriteLine("");
					}
				}
				if (mesero.Length > 0)
				{
					printerClass.WriteLine("");
					printerClass.NormalBiggerFont();
					printerClass.WriteChars("Mesero " + mesero);
					printerClass.WriteLine("");
				}
			}
			printerClass.smallFont();
			if (configuration.gPeluqueria)
			{
				printerClass.WriteChars("Sistema Beautytech by Toptech");
			}
			else if (configuration.gGimnasio)
			{
				printerClass.WriteChars("Sistema Gymtech by Toptech");
			}
			else
			{
				printerClass.WriteChars("Sistema Restotech by Toptech");
			}
			if (ParaLlevarID == 0)
			{
				int num13 = 0;
				if (dataTable.Rows.Count <= 15)
				{
					num13 = 25 - dataTable.Rows.Count;
					num13 = 3;
					printerClass.FeedPaper(num13);
				}
			}
			printerClass.WriteLine("");
			printerClass.WriteChars("Cuenta " + Conversions.ToString(VisitaID));
			printerClass.WriteLine("");
			if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo))
			{
				clsConfiguraciones clsConfiguraciones2 = new clsConfiguraciones();
				printerClass.AlignCenter();
				printerClass.NormalBiggerFont();
				printerClass.WriteLine(clsConfiguraciones2.devolverDescripcion());
			}
			printerClass.tinnyFont();
			printerClass.WriteChars(".");
			printerClass.CutPaper();
			printerClass.EndDoc();
			printerClass = null;
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LolaHuari)
			{
				string text10 = new ctlConfiguraciones().devolverComentario();
				if (text10.Length > 0)
				{
					P = new PrinterClass(System.Windows.Forms.Application.StartupPath, text, ref encontro, "RestotechComments");
					PrinterClass printerClass3 = P;
					printerClass3.NormalFont10();
					printerClass3.Bold = false;
					printerClass3.WriteLine("");
					printerClass3.WriteLine(text10 ?? "");
					printerClass3.WriteLine("");
					printerClass3.CutPaper();
					printerClass3.EndDoc();
					_ = null;
				}
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Tapekua && num2 > 0f)
			{
				P = new PrinterClass("C:\\Restotech\\", text, ref encontro, "Restotech Cover");
				PrinterClass printerClass4 = P;
				printerClass4.BigFont();
				printerClass4.Bold = true;
				printerClass4.GotoSixth(2.0);
				printerClass4.UnderlineOn();
				printerClass4.WriteLine("        CUENTA");
				printerClass4.UnderlineOff();
				printerClass4.GotoSixth(1.0);
				printerClass4.NormalFont();
				printerClass4.WriteChars("");
				printerClass4.WriteLine("Fecha:" + DateTime.Now.ToString());
				printerClass4.NormalBiggerFont();
				printerClass4.WriteChars("");
				printerClass4.WriteLine(txtMesa);
				printerClass4.NormalFont();
				printerClass4.FeedPaper(2);
				printerClass4.Bold = true;
				printerClass4.DrawLine();
				printerClass4.GotoSixth(1.0);
				printerClass4.WriteChars("Descripcion");
				printerClass4.GotoSixth(4.1);
				printerClass4.WriteChars("Cant.");
				printerClass4.GotoSixth(5.0);
				printerClass4.WriteChars("Precio");
				printerClass4.GotoSixth(6.0);
				printerClass4.WriteChars("Total");
				printerClass4.Bold = false;
				printerClass4.WriteLine("");
				printerClass4.DrawLine();
				printerClass4.DrawLine();
				printerClass4.NormalFont();
				printerClass4.GotoSixth(1.0);
				printerClass4.WriteChars("Cover");
				printerClass4.GotoSixth(4.3);
				printerClass4.WriteChars(Conversions.ToString(num3));
				printerClass4.GotoSixth(5.0);
				printerClass4.WriteChars(Math.Round(num2 / (float)num3, 1).ToString());
				printerClass4.GotoSixth(6.0);
				printerClass4.WriteChars(Math.Round(num2, 1).ToString());
				printerClass4.WriteLine("");
				printerClass4.NormalBiggerFont();
				printerClass4.DrawLine();
				printerClass4.DrawLine();
				printerClass4.GotoSixth(1.0);
				printerClass4.WriteChars("Total: ");
				printerClass4.GotoSixth(5.0);
				printerClass4.WriteChars(Math.Round(num2, 1) + " Bs.");
				printerClass4.WriteLine("");
				printerClass4.CutPaper();
				printerClass4.EndDoc();
				_ = null;
			}
			return true;
		}
	}

	public static void QRubicacion(bool pin_skipped, double LATY_NUM, double LONX_NUM, ref PrinterClass P)
	{
		if (!pin_skipped)
		{
			string text = "";
			text = "https://maps.google.com/?q=" + Conversion.Str(LATY_NUM).Trim() + "," + Conversion.Str(LONX_NUM).Trim();
			QRCodeEncoder qRCodeEncoder = new QRCodeEncoder();
			qRCodeEncoder.QRCodeEncodeMode = QRCodeEncoder.ENCODE_MODE.BYTE;
			qRCodeEncoder.QRCodeScale = int.Parse(Conversions.ToString(2));
			qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.H;
			qRCodeEncoder.QRCodeVersion = 0;
			qRCodeEncoder.QRCodeBackgroundColor = Color.FromArgb(Color.White.ToArgb());
			qRCodeEncoder.QRCodeForegroundColor = Color.FromArgb(Color.Black.ToArgb());
			try
			{
				System.Drawing.Image image = qRCodeEncoder.Encode(text, Encoding.UTF8);
				P.PrintQRfactura1(image);
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				ProjectData.ClearProjectError();
			}
		}
	}

	public static bool printCuentaTotalFactura(System.Data.DataTable dgvTotal, string txtMesa, string mesero, bool desdeCuentaTotal, int NroOrden, int visitaID, bool desdeKiosko, string personaRecoge, string TelefonoRecoge)
	{
		checked
		{
			bool result;
			try
			{
				bool encontro = false;
				ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
				if (dgvTotal.Rows.Count == 0)
				{
					result = true;
				}
				else
				{
					string text = "";
					text = ctlImpresoras2.DevolverImprimirFacturaFisico();
					if (Operators.CompareString(text, "", TextCompare: false) == 0)
					{
						text = ctlImpresoras2.devolverImpresoraCuentaFisico();
					}
					PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, text, ref encontro, "Restotech Cuenta");
					if (configuration.gPeluqueria & !encontro)
					{
						printerClass = new PrinterClass(System.Windows.Forms.Application.StartupPath, ctlImpresoras2.devolverImpresoraCuentaFisico(), ref encontro, "Restotech Cuenta");
					}
					if (!encontro)
					{
						printerClass = new PrinterClass(System.Windows.Forms.Application.StartupPath, ctlImpresoras2.devolverImpresoraCuentaFisico(), ref encontro, "Restotech Cuenta");
					}
					if (!encontro)
					{
						result = false;
					}
					else
					{
						PrinterClass printerClass2 = printerClass;
						printerClass2.BigFont();
						printerClass2.Bold = true;
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Paradise)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("     PARADISE");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.ComidaSuarez)
						{
							printerClass2.WriteLine("COMIDA TIPICA SUAREZ");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Tang)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("    TANG");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Stigma)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("     STIGMA");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.expoFood)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("    TOPTECH");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.shiwu)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("    SHIWU");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Brasargent)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("   1987");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BAHREM)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine(" BAHREM  RESTO  BAR ");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElSolar)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("  EL SOLAR");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.shiwu)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("          SHIWU");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Acai)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("       ACAI BAR ");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BAHREM)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("  BAHREM  RESTO  BAR  ");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jardin)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("   ");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.MagnoGym)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("  'MAGNO GYM' ");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Solstice)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("  'SOLSTICE' ");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.MicromercadoCercaTuyo)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("  'CERCA TUYO' ");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.MicromercadoPasse)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("      'PASSE' ");
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Sabores)
						{
							printerClass2.WriteLine("   ");
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteLine("SABORES Y COLORES");
							printerClass2.FeedPaper(1);
							printerClass2.FeedPaper(1);
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.NatuLife)
						{
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteLine("    NATULIFE");
							printerClass2.FeedPaper(1);
						}
						printerClass2.GotoSixth(2.0);
						printerClass2.UnderlineOn();
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LaCastañuela)
						{
							printerClass2.AlignCenter();
							printerClass2.Bold = true;
							if (Operators.CompareString(txtMesa, "", TextCompare: false) == 0)
							{
								printerClass2.WriteLine("RECIBO");
							}
							else
							{
								printerClass2.WriteLine("PEDIDO AL CREDITO");
							}
							printerClass2.WriteLine("");
							printerClass2.Bold = false;
						}
						else
						{
							printerClass2.AlignCenter();
							printerClass2.WriteLine("CUENTA");
						}
						ctlVisitas ctlVisitas2 = new ctlVisitas();
						if (ctlVisitas2.BuscarParaLlevarID(visitaID) > 0)
						{
							printerClass2.NormalFont();
							printerClass2.AlignCenter();
							ctlVisitas2.SetID(visitaID);
							int MesaID = 0;
							int ClienteID = 0;
							string Obs = "";
							ctlVisitas2.llenarclase(ref MesaID, ref ClienteID, ref Obs);
							string text2 = ctlVisitas2.DevolverTipoEnvio(visitaID);
							if (Operators.CompareString(text2, "-", TextCompare: false) != 0)
							{
								string text3 = text2;
								printerClass2.WriteLine(text3);
								printerClass2.WriteLine("");
							}
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BaileysLiquors)
						{
							printerClass2.NormalFont();
							printerClass2.AlignCenter();
							printerClass2.WriteLine("BAILEYS LIQUORS & MARKET");
							printerClass2.WriteLine("");
						}
						else if (configuration.gStyleBoliches1 != configuration.styleBolichesId.KIKY)
						{
							printerClass2.NormalFont();
							printerClass2.AlignCenter();
							printerClass2.WriteLine("En Mesa");
							printerClass2.WriteLine("");
						}
						printerClass2.UnderlineOff();
						printerClass2.GotoSixth(1.0);
						printerClass2.NormalFont();
						printerClass2.WriteChars("");
						printerClass2.WriteLine("Fecha:" + DateTime.Now.ToString());
						printerClass2.NormalBiggerFont();
						printerClass2.Bold = true;
						if ((!configuration.gSupermercado & (configuration.gStyleBoliches1 != configuration.styleBolichesId.LaCastañuela) & !configuration.gPeluqueria) && NroOrden > 0)
						{
							printerClass2.WriteChars("");
							printerClass2.WriteChars("Orden: " + Conversions.ToString(NroOrden));
							printerClass2.WriteLine("");
						}
						if (personaRecoge.Length > 0)
						{
							printerClass2.WriteChars("Recoge:" + personaRecoge);
							printerClass2.WriteLine("");
						}
						if (TelefonoRecoge.Length > 0)
						{
							printerClass2.WriteChars("Telefono:" + TelefonoRecoge);
							printerClass2.WriteLine("");
						}
						if (configuration.gPeluqueria && NroOrden > 0)
						{
							printerClass2.WriteChars("");
							printerClass2.WriteChars("Turno: " + Conversions.ToString(NroOrden));
							printerClass2.WriteLine("");
						}
						System.Data.DataTable dataTable = BD.ConsultaVer("Select Nombre, Apellidos from Clientes inner join Visitas on visitas.ClienteID  =Clientes.ID where Visitas.ID = " + Conversions.ToString(visitaID));
						if (dataTable.Rows.Count > 0)
						{
							printerClass2.WriteLine("");
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LaCastañuela)
							{
								printerClass2.NormalFont();
							}
							else
							{
								printerClass2.NormalBiggerFont();
							}
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteLine(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("Cliente : ", dataTable.Rows[0][0]), " "), dataTable.Rows[0][1])));
						}
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LaCastañuela)
						{
							printerClass2.WriteLine("");
						}
						if (txtMesa.Length > 0)
						{
							printerClass2.NormalFont10();
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars(txtMesa);
							printerClass2.WriteLine("");
							printerClass2.WriteLine("");
						}
						printerClass2.NormalBiggerFont();
						printerClass2.Bold = true;
						printerClass2.DrawLine();
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars("Descripcion");
						printerClass2.GotoSixth(4.7);
						printerClass2.WriteChars("Cant.");
						printerClass2.GotoSixth(5.8);
						printerClass2.WriteChars("Total");
						printerClass2.Bold = false;
						printerClass2.WriteLine("");
						printerClass2.DrawLine();
						printerClass2.NormalFont();
						dgvTotal.AcceptChanges();
						DataView dataView = new DataView(dgvTotal);
						dataView.Sort = "Producto DESC";
						foreach (object item in dataView)
						{
							object objectValue = RuntimeHelpers.GetObjectValue(item);
							printerClass2.GotoSixth(1.0);
							string text4 = "";
							for (int num = 0; num < NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null).ToString().Length; num++)
							{
								text4 += NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null).ToString().Substring(num, 1);
								if (num > 20)
								{
									break;
								}
							}
							printerClass2.WriteChars(text4);
							printerClass2.GotoSixth(5.0);
							printerClass2.WriteChars(Conversions.ToDouble(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null)).ToString("##.##"));
							printerClass2.GotoSixth(6.0);
							printerClass2.WriteChars(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								Operators.AddObject(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Debe" }, null), NewLateBinding.LateIndexGet(objectValue, new object[1] { "Pago" }, null)),
								1
							}, null, null, null).ToString());
							printerClass2.WriteLine("");
						}
						printerClass2.NormalBiggerFont();
						printerClass2.DrawLine();
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars("Total: ");
						printerClass2.GotoSixth(5.3);
						printerClass2.WriteChars(Math.Round(Convert.ToDouble(RuntimeHelpers.GetObjectValue(dgvTotal.Compute("Sum(Debe)+Sum(Pago)", "1=1"))), 1) + " Bs ");
						printerClass2.WriteLine("");
						printerClass2.WriteLine("");
						if (desdeKiosko)
						{
							printerClass2.WriteLine("");
							printerClass2.NormalBiggerFont();
							printerClass2.GotoSixth(1.0);
							printerClass2.Bold = true;
							printerClass2.WriteChars("Adulau pasa por caja a cancelar!");
							printerClass2.WriteLine("");
							printerClass2.WriteLine("");
						}
						printerClass2.NormalFont();
						printerClass2.Bold = false;
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars("Gracias por su preferencia!");
						printerClass2.WriteLine("");
						printerClass2.WriteLine("");
						printerClass2.smallFont();
						if (configuration.gPeluqueria)
						{
							printerClass2.WriteChars("Sistema Beautytech by Toptech");
						}
						else if (configuration.gGimnasio)
						{
							printerClass2.WriteChars("Sistema Gymtech by Toptech");
						}
						else
						{
							printerClass2.WriteChars("Sistema Restotech by Toptech");
						}
						if (!((configuration.gStyleBoliches1 == configuration.styleBolichesId.Tuticapa) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.bigBaby)))
						{
							printerClass2.WriteLine("");
							string text5 = Conversions.ToString(visitaID);
							if ((text5.Length > 3) & ((configuration.gStyleBoliches1 != configuration.styleBolichesId.KAO) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.KIKY)))
							{
								text5 = text5.Substring(text5.Length - 3, 3);
							}
							printerClass2.WriteChars("Cuenta: " + text5);
							printerClass2.WriteLine("");
						}
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LaCastañuela)
						{
							ctlDetalleCuenta obj = new ctlDetalleCuenta();
							System.Data.DataTable dataTable2 = new System.Data.DataTable();
							dataTable2 = obj.DevolverTipoPago(visitaID);
							if (dataTable2.Rows.Count > 0)
							{
								printerClass2.WriteChars(Conversions.ToString(Operators.ConcatenateObject("Pago con : ", dataTable2.Rows[0][0])));
							}
							else
							{
								printerClass2.WriteChars("Pago con : Al Credito");
							}
						}
						int num2 = 0;
						if (dgvTotal.Rows.Count <= 15)
						{
							num2 = 25 - dgvTotal.Rows.Count;
							num2 = 4;
							if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.ComidaSuarez) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Sabores) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.KIKY) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Vikingo) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.UgosPizza))
							{
								printerClass2.FeedPaper(num2);
							}
							else
							{
								printerClass2.WriteLine("");
							}
						}
						printerClass2.tinnyFont();
						printerClass2.WriteChars(".");
						printerClass2.CutPaper();
						printerClass2.EndDoc();
						printerClass2 = null;
						result = true;
					}
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

	public static void printTicketComidaRapida(System.Data.DataTable dgvPedido, bool aumentoEnCuenta, int NroOrden, string PersonaQueRecoge, bool paraLlevar, string NombreMesero, string lblMesa, string txtMesa, bool imprimir, double Total, bool TodoSeparado, bool SopaEnTaper, bool porCredito, int visitaId)
	{
		checked
		{
			try
			{
				dgvPedido.AcceptChanges();
				System.Data.DataTable dataTable = dgvPedido.Copy();
				foreach (object row in dataTable.Rows)
				{
					object objectValue = RuntimeHelpers.GetObjectValue(row);
					if (Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null)), "")).Contains(" |- "))
					{
						object[] obj = new object[2] { "Producto", null };
						object instance = NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null);
						object[] obj2 = new object[2] { 0, null };
						object instance3;
						object instance2 = (instance3 = NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null));
						object[] array = new object[1];
						object obj3 = (array[0] = " |- ");
						obj2[1] = NewLateBinding.LateGet(instance2, null, "IndexOf", array, null, null, null);
						object[] array2 = obj2;
						bool[] obj4 = new bool[2] { false, true };
						bool[] array3 = obj4;
						object obj5 = NewLateBinding.LateGet(instance, null, "Substring", obj2, null, null, obj4);
						if (array3[1])
						{
							NewLateBinding.LateSetComplex(instance3, null, "IndexOf", new object[2]
							{
								obj3,
								array2[1]
							}, null, null, OptimisticSet: true, RValueBase: true);
						}
						obj[1] = obj5;
						NewLateBinding.LateIndexSet(objectValue, obj, null);
					}
				}
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElSolar) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Sabores))
				{
					printTicketElSolar(dataTable, aumentoEnCuenta, NroOrden, PersonaQueRecoge, paraLlevar, NombreMesero, lblMesa, txtMesa, imprimir, Total, TodoSeparado, SopaEnTaper, porCredito);
				}
				else
				{
					if (!imprimir)
					{
						return;
					}
					int num = 0;
					dataTable.AcceptChanges();
					DataView dataView = new DataView(dataTable);
					string[] array4 = new string[2];
					num = 0;
					ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
					string text = ctlImpresoras2.devolverImpresoraCuentaFisico();
					if (text.Length > 0)
					{
						array4[0] = text;
						_ = array4[0];
						num = 1;
					}
					if (porCredito)
					{
						if (num == 0)
						{
							array4[0] = ctlImpresoras2.DevolverImprimirFacturaFisico();
							_ = array4[0];
							num = 1;
						}
						if (num == 0)
						{
							return;
						}
					}
					else if (num == 0)
					{
						return;
					}
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) & !porCredito)
					{
						array4[0] = "DESPACHO";
						_ = array4[0];
						num = 1;
					}
					bool encontro = false;
					if (array4[0].Length <= 0)
					{
						return;
					}
					PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, array4[0], ref encontro, "Restotech Ticket");
					if (!encontro)
					{
						Interaction.MsgBox("No puedo encontrar la impresora " + array4[0]);
						return;
					}
					PrinterClass printerClass2 = printerClass;
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Tuticapa) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.bigBaby))
					{
						printerClass2.FeedPaper(8);
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jungla)
					{
						printerClass2.PrintLogo();
					}
					printerClass2.AlignCenter();
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos))
					{
						printerClass2.BigFont();
					}
					else
					{
						printerClass2.MaxFont();
					}
					printerClass2.Bold = true;
					string text2 = "Pedido";
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Solstice)
					{
						printerClass2.WriteLine("SOLSTICE");
						text2 = "Recibo";
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Panessa)
					{
						text2 = "MESA";
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.SaintGeorge)
					{
						text2 = "ENTRE MASAS";
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Doce)
					{
						text2 = ((!paraLlevar) ? "EN MESA" : "PARA LLEVAR");
					}
					ctlVisitas ctlVisitas2 = new ctlVisitas();
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | paraLlevar)
					{
						ctlVisitas2.SetID(visitaId);
						int MesaID = 0;
						int ClienteID = 0;
						string Obs = "";
						ctlVisitas2.llenarclase(ref MesaID, ref ClienteID, ref Obs);
						string text3 = ctlVisitas2.DevolverTipoEnvio(visitaId);
						if (Operators.CompareString(text3, "-", TextCompare: false) != 0)
						{
							text2 = text3;
						}
					}
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.SirFrancis) & porCredito)
					{
						text2 = "AL CREDITO";
					}
					if (aumentoEnCuenta)
					{
						printerClass2.WriteLine(text2 + " - (Aumento) Then");
					}
					else
					{
						printerClass2.WriteLine(text2);
					}
					printerClass2.AlignLeft();
					if (NroOrden > 0)
					{
						printerClass2.Bold = true;
						printerClass2.GotoSixth(1.0);
						if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CheGaucho))
						{
							printerClass2.NormalFont();
						}
						else
						{
							printerClass2.NormalBiggerFont();
						}
						if (paraLlevar)
						{
							printerClass2.WriteChars("ORDEN " + Conversions.ToString(NroOrden));
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Solstice)
						{
							printerClass2.WriteChars("Turno " + Conversions.ToString(NroOrden));
						}
						else
						{
							printerClass2.WriteChars("Orden " + Conversions.ToString(NroOrden));
						}
						printerClass2.WriteLine("");
						printerClass2.Bold = false;
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia)
					{
						printerClass2.AlignLeft();
						printerClass2.WriteLine("Mesa : " + txtMesa);
					}
					if (PersonaQueRecoge.Length > 0)
					{
						printerClass2.GotoSixth(1.0);
						if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos))
						{
							printerClass2.NormalBiggerFont();
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA)
						{
							printerClass2.smallFont();
						}
						else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia))
						{
							printerClass2.NormalFont();
						}
						else
						{
							printerClass2.BigFont();
						}
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Panessa)
						{
							printerClass2.WriteLine("Cliente: " + PersonaQueRecoge);
						}
						else
						{
							printerClass2.WriteLine("Recoge " + PersonaQueRecoge);
						}
					}
					if (TodoSeparado)
					{
						printerClass2.AlignCenter();
						printerClass2.WriteChars("--- TODO SEPARADO ---");
						printerClass2.WriteLine("");
						printerClass2.GotoSixth(1.0);
						printerClass2.AlignLeft();
					}
					if (SopaEnTaper)
					{
						printerClass2.AlignCenter();
						printerClass2.WriteChars("--- SOPA EN TAPER ---");
						printerClass2.WriteLine("");
						printerClass2.GotoSixth(1.0);
						printerClass2.AlignLeft();
					}
					printerClass2.GotoSixth(1.0);
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CheGaucho))
					{
						printerClass2.NormalFont();
						printerClass2.WriteLine("Fecha: " + DateTime.Now.ToString());
					}
					else
					{
						printerClass2.NormalBiggerFont();
						printerClass2.WriteLine(DateTime.Now.ToString());
					}
					printerClass2.DrawLine();
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CheGaucho) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza))
					{
						printerClass2.NormalBiggerFont();
					}
					else
					{
						printerClass2.BigFont();
					}
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars("");
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars("Cant");
					printerClass2.GotoSixth(2.0);
					printerClass2.WriteChars("   Descripcion");
					printerClass2.WriteLine("");
					printerClass2.DrawLine();
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos)
					{
						printerClass2.BigFont();
					}
					else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Solstice) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA))
					{
						printerClass2.smallFont();
					}
					else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
					{
						printerClass2.NormalBiggerFont();
					}
					else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SaintGeorge))
					{
						printerClass2.NormalFont();
					}
					else
					{
						printerClass2.BigFont();
					}
					DataView dataView2 = dataView;
					int num2 = 0;
					string text4 = "";
					foreach (object item in dataView2)
					{
						object objectValue2 = RuntimeHelpers.GetObjectValue(item);
						if (Operators.ConditionalCompareObjectEqual(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "ProductoID" }, null), 2, TextCompare: false))
						{
							num2 = Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null));
							text4 = Conversions.ToString(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null));
						}
						else
						{
							if (!Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null), 0, TextCompare: false))
							{
								continue;
							}
							printerClass2.GotoSixth(1.0);
							double num3 = Conversions.ToDouble(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null));
							string source = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString().Trim();
							if (!((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) & source.All([SpecialName] (char c3) => c3 == '-')))
							{
								if (source.All([SpecialName] (char c3) => c3 == '-'))
								{
									printerClass2.Draw2Line();
									continue;
								}
								if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia)
								{
									if (dataView2.Table.Columns.Contains("ProductosCombo"))
									{
										if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "ProductosCombo" }, null)), ""), "9999", TextCompare: false))
										{
											printerClass2.WriteChars("*");
											NewLateBinding.LateIndexSet(objectValue2, new object[2] { "ProductosCombo", "" }, null);
										}
										else
										{
											printerClass2.WriteChars(num3.ToString("##.##") ?? "");
										}
									}
									else
									{
										printerClass2.WriteChars(num3.ToString("##.##") ?? "");
									}
								}
								else
								{
									printerClass2.WriteChars(num3.ToString("##.##") ?? "");
								}
							}
							printerClass2.GotoSixth(1.5);
							int num4 = 0;
							string text5 = "";
							string text6 = "";
							string text7 = "";
							int num5 = 230;
							System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
							NewLateBinding.LateIndexSet(objectValue2, new object[2]
							{
								"Producto",
								NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString()
							}, null);
							while (num4 < NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString().Length)
							{
								text5 += NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString().Substring(num4, 1);
								if (TextRenderer.MeasureText(text5, font).Width > num5)
								{
									text7 += NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString().Substring(num4, 1);
								}
								else
								{
									text6 += NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString().Substring(num4, 1);
								}
								num4++;
							}
							printerClass2.WriteLine(text6);
							if (text7.Trim().Length > 0)
							{
								printerClass2.GotoSixth(1.5);
								printerClass2.WriteLine(text7.Trim());
							}
							if (configuration.gPeluqueria && Operators.ConditionalCompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "MeseroID" }, null)), 0), 0, TextCompare: false))
							{
								ctlMeseros ctlMeseros2 = new ctlMeseros();
								ctlMeseros2.SetMeseroID(Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "MeseroID" }, null)));
								string text8 = ctlMeseros2.devolverNombre();
								if (Operators.CompareString(NombreMesero, text8, TextCompare: false) != 0)
								{
									printerClass2.WriteLine("  --" + text8);
								}
							}
							if (!porCredito)
							{
								if (dataView2.Table.Columns.Contains("ProductosCombo") && VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "ProductosCombo" }, null)), "").ToString().Length > 0)
								{
									string[] array5 = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "ProductosCombo" }, null).ToString().Split(',');
									foreach (string obj6 in array5)
									{
										ctlProductos ctlProductos2 = new ctlProductos();
										string[] array6 = obj6.ToString().Split('-');
										string Obs;
										if (array6.Length > 1)
										{
											if (Operators.CompareString(array6[1], "1", TextCompare: false) != 0)
											{
												continue;
											}
											ctlProductos2.SetProductoID(Conversions.ToInteger(array6[0]));
											Obs = "";
											ctlProductos2.cargarDatosCombo(ref Obs);
											printerClass2.GotoSixth(1.0);
											if (array6.Length > 2)
											{
												if (Operators.CompareString(array6[2], "1", TextCompare: false) == 0)
												{
													if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo))
													{
														printerClass2.WriteLine("   -" + ctlProductos2.getNombre());
													}
													else
													{
														printerClass2.WriteLine("  +" + ctlProductos2.getNombre());
													}
												}
												else
												{
													printerClass2.WriteLine("  -" + ctlProductos2.getNombre());
												}
											}
											else
											{
												printerClass2.WriteLine("  +" + ctlProductos2.getNombre());
											}
											continue;
										}
										ctlProductos2.SetProductoID(Conversions.ToInteger(array6[0]));
										Obs = "";
										ctlProductos2.cargarDatosCombo(ref Obs);
										printerClass2.GotoSixth(1.0);
										if (array6.Length > 2)
										{
											if (Operators.CompareString(array6[2], "1", TextCompare: false) == 0)
											{
												printerClass2.WriteLine("  +" + ctlProductos2.getNombre());
											}
											else
											{
												printerClass2.WriteLine("  -" + ctlProductos2.getNombre());
											}
										}
										else
										{
											printerClass2.WriteLine("  +" + ctlProductos2.getNombre());
										}
									}
								}
								if (dataView2.Table.Columns.Contains("Observaciones"))
								{
									if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Observaciones" }, null)), "").ToString().Length > 0)
									{
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
										{
											printerClass2._FontSize += 2.0;
											printerClass2.Bold = true;
										}
										printerClass2.GotoSixth(1.5);
										string text9 = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Observaciones" }, null).ToString();
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO)
										{
											printerClass2.GotoSixth(2.0);
											text9 = "(" + text9 + ")";
										}
										string[] array7 = ("**" + text9).Split(new string[3]
										{
											Environment.NewLine,
											"\n",
											"\r"
										}, StringSplitOptions.None);
										foreach (string obj7 in array7)
										{
											string text10 = "-";
											string Obs = obj7;
											for (int num6 = 0; num6 < Obs.Length; num6++)
											{
												char c = Obs[num6];
												if (TextRenderer.MeasureText(text10 + Conversions.ToString(c), font).Width > num5 - 10)
												{
													printerClass2.WriteLine(text10);
													text10 = "  " + c;
												}
												else
												{
													text10 += Conversions.ToString(c);
												}
											}
											if (Operators.CompareString(text10, "", TextCompare: false) != 0)
											{
												printerClass2.WriteLine(text10);
											}
										}
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
										{
											printerClass2._FontSize -= 2.0;
											printerClass2.Bold = false;
										}
									}
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
									{
										printerClass2.DrawLine();
									}
								}
							}
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo))
							{
								printerClass2.AlignLeft();
								printerClass2.WriteLine("-------------------------");
							}
							if (configuration.gStyleBoliches1 != configuration.styleBolichesId.BuenDia || !dataView2.Table.Columns.Contains("Observaciones") || VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Observaciones" }, null)), "").ToString().Length <= 0)
							{
								continue;
							}
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
							{
								printerClass2._FontSize += 2.0;
								printerClass2.Bold = true;
							}
							printerClass2.GotoSixth(1.5);
							string text11 = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Observaciones" }, null).ToString();
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO)
							{
								printerClass2.GotoSixth(2.0);
								text11 = "(" + text11 + ")";
							}
							string[] array8 = ("**" + text11).Split(new string[3]
							{
								Environment.NewLine,
								"\n",
								"\r"
							}, StringSplitOptions.None);
							foreach (string obj8 in array8)
							{
								string text12 = "-";
								string text13 = obj8;
								for (int num8 = 0; num8 < text13.Length; num8++)
								{
									char c2 = text13[num8];
									if (TextRenderer.MeasureText(text12 + Conversions.ToString(c2), font).Width > num5 - 10)
									{
										printerClass2.WriteLine(text12);
										text12 = "  " + c2;
									}
									else
									{
										text12 += Conversions.ToString(c2);
									}
								}
								if (Operators.CompareString(text12, "", TextCompare: false) != 0)
								{
									printerClass2.WriteLine(text12);
								}
							}
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
							{
								printerClass2._FontSize -= 2.0;
								printerClass2.Bold = false;
							}
						}
					}
					if (num2 > 0)
					{
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(num2.ToString());
						printerClass2.GotoSixth(2.0);
						printerClass2.WriteChars(text4.ToString());
						printerClass2.WriteLine("");
					}
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Solstice) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CheGaucho) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SaintGeorge))
					{
						printerClass2.WriteLine("");
						printerClass2.NormalBiggerFont();
					}
					if (configuration.gStyleBoliches1 != configuration.styleBolichesId.BuenDia)
					{
						printerClass2.WriteChars("TOTAL ");
						printerClass2.GotoSixth(4.5);
						printerClass2.WriteChars(Math.Round(Convert.ToDouble(Total), 1).ToString("#0.#0") + " " + VariableGeneral.MonedaString1);
					}
					printerClass2.WriteLine("");
					clsPagos clsPagos2 = new clsPagos();
					string text14 = clsPagos2.devolverCuentaPorVisitaId(visitaId);
					if (text14.Length > 0)
					{
						printerClass2.NormalBigFont();
						printerClass2.WriteLine("");
						printerClass2.GotoSixth(1.0);
						if (Operators.CompareString(text14.ToUpper(), "CAJA CHICA BS", TextCompare: false) == 0)
						{
							printerClass2.WriteChars("PAGÓ:  EFECTIVO");
						}
						else
						{
							printerClass2.WriteChars("PAGÓ:  " + text14.ToUpper() + " ");
						}
						printerClass2.WriteLine("");
					}
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA))
					{
						printerClass2.NormalBiggerFont();
					}
					else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CheGaucho))
					{
						printerClass2.NormalFont();
					}
					else
					{
						printerClass2.BigFont();
					}
					printerClass2.DrawLine();
					printerClass2.GotoSixth(1.0);
					if (!configuration.gComidaRapida)
					{
						printerClass2.WriteChars("Por " + NombreMesero);
						printerClass2.WriteLine("");
					}
					if (porCredito)
					{
						printerClass2.WriteLine("");
						printerClass2.WriteLine("");
						printerClass2.WriteChars("             Firma");
						printerClass2.WriteLine("");
					}
					if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Tuticapa)
					{
						printerClass2.NormalFont();
						printerClass2.WriteChars("Cuenta " + Conversions.ToString(visitaId));
						printerClass2.WriteLine("");
					}
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo))
					{
						clsConfiguraciones clsConfiguraciones2 = new clsConfiguraciones();
						printerClass2.AlignCenter();
						printerClass2.NormalBiggerFont();
						printerClass2.WriteLine(clsConfiguraciones2.devolverDescripcion());
						printerClass2.AlignLeft();
					}
					int num9 = ctlVisitas2.DevolverclienteID(visitaId);
					if (num9 > 0)
					{
						printerClass2.AlignLeft();
						ctlClientes obj9 = new ctlClientes();
						string name = "";
						string lastname = "";
						obj9.SetID(num9);
						obj9.getClientInfoByID(ref name, ref lastname);
						printerClass2.WriteLine("Cliente: " + name + " " + lastname);
						printerClass2.NormalFont();
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.SaintGeorge && clsPagos2.DevolverCodigoAlumno(visitaId).Length > 0)
					{
						printerClass2.BigFont();
						printerClass2.AlignCenter();
						printerClass2.WriteLine("Codigo: " + clsPagos2.DevolverCodigoAlumno(visitaId));
						printerClass2.NormalFont();
						printerClass2.AlignLeft();
					}
					if (!((configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo)))
					{
						printerClass2.WriteLine("");
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Solstice)
					{
						printerClass2.WriteChars("Expiracion " + Conversions.ToString(_FechaExpiracion));
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.srPollo)
					{
						string text15 = "";
						text15 = new ctlConfiguraciones().devolverComentario();
						if (text15.Length > 0)
						{
							printerClass2.WriteLine("");
							printerClass2.WriteLine(text15 ?? "");
							printerClass2.WriteLine("");
						}
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos)
					{
						printerClass2.WriteLine("");
						printerClass2.WriteLine("");
						printerClass2.WriteLine("");
						printerClass2.WriteLine(".");
					}
					else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.DonShawarmaLite)
					{
						printerClass2.WriteLine(" ");
						printerClass2.WriteLine(" ");
						printerClass2.WriteLine(" ");
						printerClass2.WriteLine(" ");
						printerClass2.WriteLine(" ");
						printerClass2.WriteLine(" ");
						printerClass2.WriteLine(" ");
						printerClass2.WriteLine(".");
					}
					else
					{
						printerClass2.WriteLine(".");
					}
					printerClass2.CutPaper();
					printerClass2.EndDoc();
					printerClass2 = null;
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

	public static void printTicketfinalProduccion(DateTime dtpDesdeDate, DateTime dtpDesdeTime, DateTime dtpHastaDate, DateTime dtpHastatime, string Mesero, string obs)
	{
		System.Data.DataTable dataTable = BD.ConsultaVer((" SELECT   sum(DetallesProduccion.Producida) as Producida, sum(DetallesProduccion.Eliminada) as Eliminada, sum(DetallesProduccion.Reciclada) as Reciclada   FROM ((DetallesProduccion inner join Productos on DetallesProduccion.ProductoID=Productos.ID) inner join Produccion on Produccion.ProduccionID= DetallesProduccion.ProduccionID   ) inner join CategoriasProduccion on CategoriasProduccion.CategoriaProduccionID = Productos.CategoriaProduccionID    WHERE  Produccion.Fecha between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeTime) + configuration.CaracterFecha + " And " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastatime) + configuration.CaracterFecha) ?? "");
		System.Data.DataTable dataTable2 = BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,   sum(DetalleCuenta.Cantidad) as Cantidad,    sum( DetalleCuenta.Pago) as Pagado   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Pago>0 And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " And    (DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeTime) + configuration.CaracterFecha + " And " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastatime) + configuration.CaracterFecha + ") group by TiposProductos.Descripcion   ");
		System.Data.DataTable dataTable3 = BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd ,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Pago=0 And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " And    (DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeTime) + configuration.CaracterFecha + " And " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastatime) + configuration.CaracterFecha + ") group by TiposProductos.Descripcion   ");
		System.Data.DataTable dataTable4 = BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,   sum(DetalleCuenta.Cantidad) as Cantidad,    sum( DetalleCuenta.Pago) as Pagado   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where  Productos.esporPeso= " + VariableGeneral.armarBolean(1) + " And DetalleCuenta.Pago>0 And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " And    (DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeTime) + configuration.CaracterFecha + " And " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastatime) + configuration.CaracterFecha + ") group by TiposProductos.Descripcion   ");
		System.Data.DataTable dataTable5 = BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd ,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.esporPeso= " + VariableGeneral.armarBolean(1) + " And DetalleCuenta.Pago=0 And DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " And    (DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeTime) + configuration.CaracterFecha + " And " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastatime) + configuration.CaracterFecha + ") group by TiposProductos.Descripcion   ");
		System.Data.DataTable dataTable6 = BD.ConsultaVer((" select max(nroFactura), min(nroFactura)  from Facturas   where Facturas.pc= '" + MyProject.Computer.Name + "' and FechaEmision between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeTime) + configuration.CaracterFecha + " And " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastatime) + configuration.CaracterFecha) ?? "");
		string text = "";
		ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
		bool encontro = false;
		PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, ctlImpresoras2.DevolverImprimirFacturaFisico(), ref encontro, "Restotech Ticket");
		if (!encontro)
		{
			Interaction.MsgBox("No puedo encontrar la impresora " + ctlImpresoras2.DevolverImprimirFacturaFisico());
			return;
		}
		PrinterClass printerClass2 = printerClass;
		printerClass2.AlignCenter();
		printerClass2.NormalBiggerFont();
		printerClass2.Bold = true;
		printerClass2.WriteLine("Reporte de Cierre ");
		printerClass2.smallFont();
		printerClass2.WriteLine(VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeTime) + " - " + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastatime));
		ctlConfiguraciones ctlConfiguraciones2 = new ctlConfiguraciones();
		printerClass2.WriteLine(ctlConfiguraciones2.devolverSucursal());
		if (Mesero.Length > 0)
		{
			printerClass2.WriteLine(Mesero);
		}
		printerClass2.WriteLine("");
		double num = 0.0;
		double num2 = 0.0;
		double num3 = 0.0;
		double num4 = 0.0;
		checked
		{
			int num5 = dataTable2.Rows.Count - 1;
			for (int i = 0; i <= num5; i++)
			{
				num = Conversions.ToDouble(Operators.AddObject(num, dataTable2.Rows[i]["Pagado"]));
			}
			int num6 = dataTable3.Rows.Count - 1;
			for (int j = 0; j <= num6; j++)
			{
				num2 = Conversions.ToDouble(Operators.AddObject(num2, dataTable3.Rows[j]["Debe"]));
			}
			int num7 = dataTable4.Rows.Count - 1;
			for (int k = 0; k <= num7; k++)
			{
				num3 = Conversions.ToDouble(Operators.AddObject(num3, dataTable4.Rows[k]["Cantidad"]));
			}
			int num8 = dataTable5.Rows.Count - 1;
			for (int l = 0; l <= num8; l++)
			{
				num4 = Conversions.ToDouble(Operators.AddObject(num4, dataTable5.Rows[l]["Cantidad"]));
			}
			printerClass2.Bold = false;
			printerClass2.NormalFont();
			printerClass2.GotoSixth(1.0);
			printerClass2.WriteChars("Venta Total (Bs) ");
			printerClass2.GotoSixth(5.0);
			printerClass2.WriteChars(Conversions.ToString(Math.Round(num, 1)));
			printerClass2.GotoSixth(6.0);
			printerClass2.WriteChars(Conversions.ToString(Math.Round(num2, 1)));
			printerClass2.WriteLine("");
			printerClass2.smallFont();
			int num9 = dataTable2.Rows.Count - 1;
			for (int m = 0; m <= num9; m++)
			{
				printerClass2.GotoSixth(2.0);
				printerClass2.WriteChars(Conversions.ToString(dataTable2.Rows[m]["tipoProd"]));
				printerClass2.GotoSixth(5.0);
				PrinterClass printerClass3 = printerClass2;
				object[] array;
				DataRow dataRow;
				bool[] array2;
				object obj = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
				{
					(dataRow = dataTable2.Rows[m])["Pagado"],
					1
				}, null, null, array2 = new bool[2] { true, false });
				if (array2[0])
				{
					dataRow["Pagado"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
				}
				printerClass3.WriteChars(Conversions.ToString(obj));
				printerClass2.WriteLine("");
			}
			printerClass2.WriteLine("");
			printerClass2.WriteLine("");
			if (!Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Producida"])))
			{
				printerClass2.NormalFont();
				printerClass2.GotoSixth(1.0);
				printerClass2.WriteChars("Produccion(Kg) ");
				printerClass2.GotoSixth(5.0);
				printerClass2.WriteChars(Conversions.ToString(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
				{
					VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Producida"]), 0),
					1
				}, null, null, null)));
				printerClass2.WriteLine("");
			}
			printerClass2.GotoSixth(1.0);
			printerClass2.WriteChars("Venta(Kg) ");
			printerClass2.GotoSixth(5.0);
			printerClass2.WriteChars(Conversions.ToString(Math.Round(num3, 1)));
			printerClass2.WriteLine("");
			printerClass2.GotoSixth(1.0);
			printerClass2.WriteChars("Controlables(Kg) ");
			printerClass2.GotoSixth(5.0);
			printerClass2.WriteChars(Conversions.ToString(Math.Round(num4, 1)));
			printerClass2.WriteLine("");
			if (!Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Eliminada"])))
			{
				printerClass2.GotoSixth(1.0);
				printerClass2.WriteChars("Eliminada(Kg) ");
				printerClass2.GotoSixth(5.0);
				PrinterClass printerClass4 = printerClass2;
				Type typeFromHandle = typeof(Math);
				DataRow dataRow;
				object[] obj2 = new object[2]
				{
					(dataRow = dataTable.Rows[0])["Eliminada"],
					1
				};
				object[] array = obj2;
				bool[] obj3 = new bool[2] { true, false };
				bool[] array2 = obj3;
				object obj4 = NewLateBinding.LateGet(null, typeFromHandle, "Round", obj2, null, null, obj3);
				if (array2[0])
				{
					dataRow["Eliminada"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
				}
				printerClass4.WriteChars(Conversions.ToString(obj4));
				printerClass2.WriteLine("");
			}
			if (!Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Reciclada"])))
			{
				printerClass2.GotoSixth(1.0);
				printerClass2.WriteChars("Reciclada(Kg) ");
				printerClass2.GotoSixth(5.0);
				PrinterClass printerClass5 = printerClass2;
				Type typeFromHandle2 = typeof(Math);
				DataRow dataRow;
				object[] obj5 = new object[2]
				{
					(dataRow = dataTable.Rows[0])["Reciclada"],
					1
				};
				object[] array = obj5;
				bool[] obj6 = new bool[2] { true, false };
				bool[] array2 = obj6;
				object obj7 = NewLateBinding.LateGet(null, typeFromHandle2, "Round", obj5, null, null, obj6);
				if (array2[0])
				{
					dataRow["Reciclada"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
				}
				printerClass5.WriteChars(Conversions.ToString(obj7));
				printerClass2.WriteLine("");
			}
			if (!Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Producida"])))
			{
				printerClass2.GotoSixth(1.0);
				printerClass2.WriteChars("Diferencia(Kg) ");
				printerClass2.GotoSixth(5.0);
				printerClass2.WriteChars(Conversions.ToString(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
				{
					Operators.SubtractObject(Operators.SubtractObject(Operators.SubtractObject(Operators.SubtractObject(dataTable.Rows[0]["Producida"], num4), num3), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Eliminada"]), 0)), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Reciclada"]), 0)),
					1
				}, null, null, null)));
				printerClass2.WriteLine("");
			}
			printerClass2.WriteLine("");
			printerClass2.NormalFont();
			printerClass2.GotoSixth(1.0);
			printerClass2.WriteChars(Conversions.ToString(Operators.ConcatenateObject("Factura Inicial ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0][1]), 0))));
			printerClass2.WriteLine("");
			printerClass2.WriteChars(Conversions.ToString(Operators.ConcatenateObject("Factura Final ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0][0]), 0))));
			printerClass2.smallFont();
			printerClass2.WriteLine("");
			printerClass2.WriteChars(obs);
			printerClass2.WriteLine("");
			printerClass2.WriteLine(Conversions.ToString(DateAndTime.Now));
			printerClass2.WriteLine("");
			printerClass2.CutPaper();
			printerClass2.EndDoc();
			text = printerClass2.Texto;
			try
			{
				if (ctlConfiguraciones2.devolverEmails().Length > 0)
				{
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
					{
						SendEmail(text, ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()) + " - Ventas Diarias " + VariableGeneral.armarSoloLaFecha(DateAndTime.Now), copiaAmi: true, enPDF: false);
					}
					else
					{
						SendEmail(text, "Restotech - Ventas Diarias " + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + " - " + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()), copiaAmi: true, enPDF: false);
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
			printerClass2 = null;
		}
	}

	public static void printTicketfinalTurno(DateTime dtpDesdeDate, DateTime dtpHastaDate, string Mesero, string obs, double montoIniBs, double montoIniDolares, double cobradoBs, double ventaBS, double compraDolares, double cobradoTarjeta, double gastadoBs, double gastadoDolares, double otrosBs, double otrosDolares, bool ciego, bool conCantidadPersonas, bool desdeVentas, double PropinaTarjeta)
	{
		string ToEmail = "";
		ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
		_Ciego = ciego;
		string text = "";
		text = ((!((configuration.gStyleBoliches1 == configuration.styleBolichesId.LaGaleria) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mandarin1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.NuevaChina) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SanHo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspana) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito))) ? ctlImpresoras2.devolverImpresoraCuentaFisico() : ctlImpresoras2.DevolverImprimirFacturaFisico());
		if (text.Length == 0)
		{
			text = ctlImpresoras2.DevolverImprimirFacturaFisico();
		}
		if (text.Length == 0)
		{
			text = ctlImpresoras2.DevolverImpresora();
		}
		bool encontro = false;
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
		{
			text = ctlImpresoras2.DevolverImprimirCierreKiky();
		}
		object obj = ((!((configuration.gStyleBoliches1 == configuration.styleBolichesId.Kaldi) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Kivon))) ? ((object)new PrinterClass(MyProject.Application.Info.DirectoryPath, text, ref encontro, "Restotech Cierre")) : ((object)new PrinterClassOLD(MyProject.Application.Info.DirectoryPath, ref text, ref encontro, "Restotech Cierre")));
		if (!encontro)
		{
			Interaction.MsgBox("No puedo encontrar la impresora ");
			return;
		}
		fillDataCierre(RuntimeHelpers.GetObjectValue(obj), dtpDesdeDate, dtpHastaDate, Mesero, obs, montoIniBs, montoIniDolares, cobradoBs, ventaBS, compraDolares, cobradoTarjeta, gastadoBs, gastadoDolares, otrosBs, otrosDolares, ref ToEmail, ciego, conCantidadPersonas, PropinaTarjeta, desdeVentas);
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pranna)
		{
			fillDataCierre(RuntimeHelpers.GetObjectValue(obj), dtpDesdeDate, dtpHastaDate, Mesero, obs, montoIniBs, montoIniDolares, cobradoBs, ventaBS, compraDolares, cobradoTarjeta, gastadoBs, gastadoDolares, otrosBs, otrosDolares, ref ToEmail, ciego, conCantidadPersonas, PropinaTarjeta, desdeVentas);
		}
		checked
		{
			try
			{
				ctlConfiguraciones ctlConfiguraciones2 = new ctlConfiguraciones();
				if (ctlConfiguraciones2.devolverEmails().Length <= 0)
				{
					return;
				}
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
				{
					SendEmail(ToEmail, ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()) + " - Ventas Diarias " + VariableGeneral.armarSoloLaFecha(DateAndTime.Now), copiaAmi: true, enPDF: false);
				}
				else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Batos))
				{
					System.Data.DataTable dataTable = new ctlTipoEnvios().devolverTipoEnviosActivos();
					string text2 = " CASE WHEN TipoEnvioID=0 THEN 0 ";
					int num = dataTable.Rows.Count - 1;
					for (int i = 0; i <= num; i++)
					{
						text2 = Conversions.ToString(Operators.ConcatenateObject(text2, Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(" WHEN TipoEnvioID=", dataTable.Rows[i][0]), " THEN PrecioExtraTipoEnvio"), dataTable.Rows[i][0])));
					}
					text2 += " ELSE 0 END";
					System.Data.DataTable dtable = BD.ConsultaVer("select FECHA, ALMACENCOD ,SUCURSALCOD,VENDEDORCOD ,MONEDAID, PEDVENTAORDEN ,TIPOPAGOID ,CREDITODIAS , ITEMCOD , SUM (CANTIDADITEM) AS CANTIDADITEM ,PRECIOUNI ,case when (MAX(RECARGO)<0) then 0 else MAX(RECARGO) end as RECARGO\r\n                                from ( SELECT  CONVERT(varchar,  Visitas.DiaKey, 103) AS FECHA, (select max(ALMACENCOD) from ConfiguracionesDelfinNet) as ALMACENCOD, (select max(SUCURSALCOD) from ConfiguracionesDelfinNet) AS SUCURSALCOD, (select max(VENDEDORCOD) from ConfiguracionesDelfinNet) AS VENDEDORCOD, 1 AS MONEDAID, (select min(visitaID) from DetalleCuenta where DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + configuration.CaracterFecha + " and " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) + configuration.CaracterFecha + " )   AS PEDVENTAORDEN, IIF(SUM(DetalleCuenta.DEBE)>0, 2,1) AS TIPOPAGOID,  IIF(SUM(DetalleCuenta.DEBE)>0, 30,0) AS CREDITODIAS,  Productos.Codigo AS ITEMCOD, sum(DetalleCuenta.Cantidad) as CANTIDADITEM, AVG(Productos.Precio) AS PRECIOUNI,    sum(Pago+debe - (DetalleCuenta.Cantidad*Productos.Precio)) AS RECARGO     FROM Visitas INNER JOIN    DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID INNER JOIN    Productos ON DetalleCuenta.ProductoID = Productos.ID WHERE DetalleCuenta.Borrada =0 and  (DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + configuration.CaracterFecha + " and " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) + configuration.CaracterFecha + " ) and Productos.Codigo<>'56'   group by  Visitas.DiaKey,   Productos.Codigo \r\n\r\n                                union\r\n\r\n                                    SELECT  CONVERT(varchar,  Visitas.DiaKey, 103) AS FECHA, (select max(ALMACENCOD) from ConfiguracionesDelfinNet) as ALMACENCOD, \r\n                                (select max(SUCURSALCOD) from ConfiguracionesDelfinNet) AS SUCURSALCOD, (select max(VENDEDORCOD) \r\n                                from ConfiguracionesDelfinNet) AS VENDEDORCOD, 1 AS MONEDAID, \r\n                                (select min(visitaID) \r\n                                from DetalleCuenta \r\n                                where DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + configuration.CaracterFecha + " and " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) + configuration.CaracterFecha + " )     AS PEDVENTAORDEN, \r\n                                IIF(SUM(DetalleCuenta.DEBE)>0, 2,1) AS TIPOPAGOID,  IIF(SUM(DetalleCuenta.DEBE)>0, 30,0) AS CREDITODIAS,  \r\n                                Productos.Codigo AS ITEMCOD, \r\n                                sum(DetalleCuenta.Cantidad) as CANTIDADITEM ,\r\n                                round( avg(Productos.Precio + " + text2 + ") ,2) AS PRECIOUNI,    \r\n                                -999 AS RECARGO     \r\n                                FROM Visitas INNER JOIN    DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID \r\n                                left join  ProductosCombos on DetalleCuenta.id=ProductosCombos.DetalleCuentaID \r\n                                INNER JOIN    Productos on ProductosCombos.ProductoID =Productos.id   \r\n                                WHERE DetalleCuenta.Borrada =0 and  (DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + configuration.CaracterFecha + " and " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) + configuration.CaracterFecha + " )   \r\n                                 and (Productos .Nombre like '%Mitad%' or Productos .Nombre like '%Extra%' or Productos .Nombre like '%Catupity%') and Productos.Codigo <> '175'\r\n                                group by  Visitas.DiaKey,   Productos.Codigo) as tab1\r\n                                group by FECHA, ALMACENCOD ,SUCURSALCOD,VENDEDORCOD ,MONEDAID, PEDVENTAORDEN ,TIPOPAGOID ,CREDITODIAS , ITEMCOD ,PRECIOUNI ");
					string text3 = System.Windows.Forms.Application.StartupPath + "\\DelfinNet\\" + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()) + "-" + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + "-" + Mesero + ".txt";
					File.Delete(text3 + ".txt");
					ExportToCSV(dtable, text3);
					sendEmailData(ToEmail, "Restotech - Ventas Diarias Nuevo" + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + " - " + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()), copiaAmi: true, text3);
				}
				else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pekelicious) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pranna) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaGrande))
				{
					try
					{
						System.Data.DataTable dat = new ctlProductos().DevolverProductosInventarioValorizado(0, prodDiferenteCero: false);
						string text4 = System.Windows.Forms.Application.StartupPath + "\\Reportes\\Valorizado" + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()) + "-" + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + "-" + Mesero + ".xls";
						File.Delete(text4 + ".xls");
						FormatoExel(dat, text4);
						sendEmailData(ToEmail, "Restotech - Ventas Diarias " + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + " - " + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()), copiaAmi: true, text4);
					}
					catch (Exception ex)
					{
						ProjectData.SetProjectError(ex);
						Exception ex2 = ex;
						SendEmail(ToEmail, "Restotech - Ventas Diarias " + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + " - " + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()), copiaAmi: true, enPDF: false);
						ProjectData.ClearProjectError();
					}
				}
				else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.SantaMaria)
				{
					try
					{
						System.Data.DataTable dat2 = BD.ConsultaVer("select Tab1.Nombre as CanalVenta, sum(Cantidad) as Cantidad, Productos.Nombre as Producto, CONVERT(int,Productos.Precio) as PrecioUni, CONVERT(int, Sum(Pago)) as Total \r\n                                                                    from DetalleCuenta\r\n                                                                    left join Productos on DetalleCuenta.ProductoID = Productos.ID\r\n                                                                    left join Visitas on Visitas.id = DetalleCuenta.VisitaID\r\n                                                                    left join (select TipoEnvios.TipoEnvioID, case Nombre when 'PEDIDOS YA' then Nombre else 'MESA - LLEVAR' end as Nombre from TipoEnvios) as Tab1 on Visitas.TipoEnvioID = Tab1.TipoEnvioID\r\n                                                                    where DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + configuration.CaracterFecha + " and " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) + configuration.CaracterFecha + " and DetalleCuenta.Borrada = 0 \r\n                                                                    group by Tab1.Nombre, Productos.Nombre, Productos.Precio\r\n                                                                    order by Productos.Nombre");
						string text5 = System.Windows.Forms.Application.StartupPath + "\\Reportes\\VentasTotales " + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()) + "-" + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + "-" + Mesero + ".xls";
						File.Delete(text5 + ".xls");
						FormatoExel2(dat2, text5);
						sendEmailData(ToEmail, "Restotech - Ventas Diarias " + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + " - " + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()), copiaAmi: true, text5);
					}
					catch (Exception ex3)
					{
						ProjectData.SetProjectError(ex3);
						Exception ex4 = ex3;
						Interaction.MsgBox("No se pudo convertir un archivo en excel", MsgBoxStyle.Information);
						SendEmail(ToEmail, "Restotech - Ventas Diarias " + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + " - " + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()), copiaAmi: true, enPDF: false);
						ProjectData.ClearProjectError();
					}
					SoloEfectivoCierre(new PrinterClass(MyProject.Application.Info.DirectoryPath, text, ref encontro, "Restotech Cierre"), dtpDesdeDate, dtpHastaDate, Mesero, obs, montoIniBs, montoIniDolares, cobradoBs, ventaBS, compraDolares, cobradoTarjeta, gastadoBs, gastadoDolares, otrosBs, otrosDolares, ref ToEmail, ciego, conCantidadPersonas, PropinaTarjeta, desdeVentas);
				}
				else
				{
					SendEmail(ToEmail, "Restotech - Ventas Diarias " + VariableGeneral.armarSoloLaFecha(DateAndTime.Now) + " - " + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()), copiaAmi: true, enPDF: false);
				}
				System.Data.DataTable dataTable2 = new clsLogg().devolverRemoverItems(dtpDesdeDate, dtpHastaDate);
				string text6 = "";
				if (dataTable2.Rows.Count > 0)
				{
					text6 = "Eventos de " + ((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion()) + ":\r\n";
					int num2 = dataTable2.Rows.Count - 1;
					for (int j = 0; j <= num2; j++)
					{
						text6 += Conversions.ToDate(dataTable2.Rows[j]["Fecha"]).ToString("HH:mm", CultureInfo.InvariantCulture);
						text6 = Conversions.ToString(Operators.ConcatenateObject(text6, Operators.ConcatenateObject(Operators.ConcatenateObject("  -", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[j]["Nombre"]), "")), " ")));
						text6 += Operators.ConcatenateObject(" -Removió ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[j]["Accion"]), "")).ToString().Trim();
						text6 += "\r\n";
					}
					text6 += "\r\n";
					SendEmail(text6, "Notificación de Ventas Canceladas", copiaAmi: false, enPDF: false, conMsgbox: false);
				}
			}
			catch (Exception ex5)
			{
				ProjectData.SetProjectError(ex5);
				Exception ex6 = ex5;
				Interaction.MsgBox(ex6.Message);
				ProjectData.ClearProjectError();
			}
		}
	}

	public static bool ExportToCSV(System.Data.DataTable dtable, string fileName)
	{
		bool result = true;
		try
		{
			StringBuilder stringBuilder = new StringBuilder();
			string text = ",";
			string text2 = "\"";
			string newLine = Environment.NewLine;
			foreach (DataColumn column in dtable.Columns)
			{
				stringBuilder.Append(wrapValue(column.ColumnName, text2, text) + text);
			}
			stringBuilder.Append(newLine);
			foreach (DataRow row in dtable.Rows)
			{
				foreach (DataColumn column2 in dtable.Columns)
				{
					if (Operators.CompareString(column2.ColumnName, "Fecha", TextCompare: false) == 0)
					{
						string value = Conversions.ToString(Operators.ConcatenateObject(Convert.ToInt32(RuntimeHelpers.GetObjectValue(NewLateBinding.LateGet(row[column2], null, "day", new object[0], null, null, null))).ToString("00") + "/" + Convert.ToInt32(RuntimeHelpers.GetObjectValue(NewLateBinding.LateGet(row[column2], null, "month", new object[0], null, null, null))).ToString("00") + "/", NewLateBinding.LateGet(row[column2], null, "year", new object[0], null, null, null)));
						stringBuilder.Append(wrapValue(value, text2, text) + text);
					}
					else if ((Operators.CompareString(column2.ColumnName, "PRECIOUNI", TextCompare: false) == 0) | (Operators.CompareString(column2.ColumnName, "RECARGO", TextCompare: false) == 0))
					{
						string value2 = Conversions.ToDouble(row[column2]).ToString("######0.#0");
						stringBuilder.Append(wrapValue(value2, text2, text) + text);
					}
					else
					{
						stringBuilder.Append(wrapValue(row[column2].ToString(), text2, text) + text);
					}
				}
				stringBuilder.Append(newLine);
			}
			using StreamWriter streamWriter = new StreamWriter(fileName);
			streamWriter.Write(stringBuilder.ToString());
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message + "\r\n" + ex2.StackTrace);
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public static string wrapValue(string value, string group, string separator)
	{
		if (value.Contains(separator))
		{
			if (value.Contains(group))
			{
				value = value.Replace(group, group + group);
			}
			value = group + value + group;
		}
		return value;
	}

	public static void FormatoExel(System.Data.DataTable dat, string dir)
	{
		checked
		{
			try
			{
				new ctlProductos();
				DataSet dataSet = new DataSet();
				dataSet.Tables.Add();
				dataSet.Tables[0].Columns.Add("ID");
				dataSet.Tables[0].Columns.Add("Codigo");
				dataSet.Tables[0].Columns.Add("Familia");
				dataSet.Tables[0].Columns.Add("TiposProductos");
				dataSet.Tables[0].Columns.Add("Nombre");
				dataSet.Tables[0].Columns.Add("Stock");
				dataSet.Tables[0].Columns.Add("Presentacion");
				dataSet.Tables[0].Columns.Add("Costo");
				dataSet.Tables[0].Columns.Add("Total");
				dataSet.Tables[0].Columns.Add("PrecioVenta");
				dataSet.Tables[0].Columns.Add("VentaTotal");
				int num = dat.Rows.Count - 1;
				for (int i = 0; i <= num; i++)
				{
					DataRow dataRow = dataSet.Tables[0].NewRow();
					int num2 = dat.Columns.Count - 1;
					for (int j = 0; j <= num2; j++)
					{
						dataRow[j] = RuntimeHelpers.GetObjectValue(dat.Rows[i][j]);
					}
					dataSet.Tables[0].Rows.Add(dataRow);
				}
				ApplicationClass applicationClass = new ApplicationClass();
				Workbook workbook = applicationClass.Workbooks.Add(RuntimeHelpers.GetObjectValue(Missing.Value));
				Worksheet worksheet = (Worksheet)workbook.ActiveSheet;
				System.Data.DataTable dataTable = dataSet.Tables[0];
				int num3 = 0;
				int num4 = 0;
				foreach (DataColumn column in dataTable.Columns)
				{
					num3++;
					applicationClass.Cells[1, num3] = column.ColumnName;
				}
				foreach (DataRow row in dataTable.Rows)
				{
					num4++;
					num3 = 0;
					foreach (DataColumn column2 in dataTable.Columns)
					{
						num3++;
						applicationClass.Cells[num4 + 1, num3] = RuntimeHelpers.GetObjectValue(row[column2.ColumnName]);
					}
				}
				worksheet.Columns.AutoFit();
				if (File.Exists(dir))
				{
					File.Delete(dir);
				}
				workbook.SaveAs(dir, RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), XlSaveAsAccessMode.xlNoChange, RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value));
				workbook.Close(RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value));
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				Interaction.MsgBox("Exportando a excel. " + ex2.Message);
				ProjectData.ClearProjectError();
			}
		}
	}

	public static void FormatoExel2(System.Data.DataTable dat, string dir)
	{
		checked
		{
			try
			{
				DataSet dataSet = new DataSet();
				dataSet.Tables.Add();
				dataSet.Tables[0].Columns.Add("CanalVenta");
				dataSet.Tables[0].Columns.Add("Cantidad");
				dataSet.Tables[0].Columns.Add("Producto");
				dataSet.Tables[0].Columns.Add("PrecioUnit");
				dataSet.Tables[0].Columns.Add("Total");
				int num = dat.Rows.Count - 1;
				for (int i = 0; i <= num; i++)
				{
					DataRow dataRow = dataSet.Tables[0].NewRow();
					int num2 = dat.Columns.Count - 1;
					for (int j = 0; j <= num2; j++)
					{
						dataRow[j] = RuntimeHelpers.GetObjectValue(dat.Rows[i][j]);
					}
					dataSet.Tables[0].Rows.Add(dataRow);
				}
				ApplicationClass applicationClass = new ApplicationClass();
				Workbook workbook = applicationClass.Workbooks.Add(RuntimeHelpers.GetObjectValue(Missing.Value));
				Worksheet worksheet = (Worksheet)workbook.ActiveSheet;
				System.Data.DataTable dataTable = dataSet.Tables[0];
				int num3 = 0;
				int num4 = 0;
				foreach (DataColumn column in dataTable.Columns)
				{
					num3++;
					applicationClass.Cells[1, num3] = column.ColumnName;
				}
				foreach (DataRow row in dataTable.Rows)
				{
					num4++;
					num3 = 0;
					foreach (DataColumn column2 in dataTable.Columns)
					{
						num3++;
						applicationClass.Cells[num4 + 1, num3] = RuntimeHelpers.GetObjectValue(row[column2.ColumnName]);
					}
				}
				worksheet.Columns.AutoFit();
				if (File.Exists(dir))
				{
					File.Delete(dir);
				}
				workbook.SaveAs(dir, RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), XlSaveAsAccessMode.xlNoChange, RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value));
				workbook.Close(RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value), RuntimeHelpers.GetObjectValue(Missing.Value));
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				Interaction.MsgBox("Exportando a excel. " + ex2.Message);
				ProjectData.ClearProjectError();
			}
		}
	}

	public static void fillDataCierre(object p, DateTime dtpDesdeDate, DateTime dtpHastaDate, string Mesero, string ObservacionTurno, double montoIniBs, double montoIniDolares, double cobradoBs, double ventaBS, double compraDolares, double cobradoTarjeta, double gastadoBs, double gastadoDolares, double otrosBs, double otrosDolares, ref string ToEmail, bool ciego, bool conCantidadPersonas, double PropinaTarjeta, bool desdeReporteVentas)
	{
		ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
		ctlProductos ctlProductos2 = new ctlProductos();
		ctlDetalleCuenta obj = new ctlDetalleCuenta();
		System.Data.DataTable dataTable = ctlProductos2.devolverCategoriaProduccionPorDescripcion1();
		string text = MyProject.Computer.Name;
		clsPagos clsPagos2 = new clsPagos
		{
			_MaquinaPago = text
		};
		string text2 = " DetalleCuenta.PC  like '" + text + "' ";
		string text3 = text;
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BurgerMunch)
		{
			text2 = " 1=1 ";
			text3 = "%%";
		}
		if (!configuration.gComidaRapida)
		{
			text3 = "%%";
		}
		if (desdeReporteVentas | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Batos))
		{
			text2 = " 1=1 ";
			clsPagos2._MaquinaPago = "%%";
			text = "%%";
		}
		double num = Math.Round(clsPagos2.DevolverEntreFechasPorMaquinaPorOtrasCuenta(dtpDesdeDate, dtpHastaDate), 1);
		System.Data.DataTable dataTable2 = obj.DevolverReporteGrupal(dtpDesdeDate, dtpHastaDate);
		System.Data.DataTable dataTable3 = obj.DevolverFacturasConTarjeta(dtpDesdeDate, dtpHastaDate, clsPagos2._MaquinaPago);
		double num2 = 0.0;
		double value = 0.0;
		if (conCantidadPersonas)
		{
			try
			{
				System.Data.DataTable dataTable4 = ((configuration.gMODO_ACCESS != 1) ? BD.ConsultaVer("select sum(cast(observacion as float)) from Visitas where ISNUMERIC(observacion)=1 and mesaID>1 And Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate)) : BD.ConsultaVer("select sum(CLng(observacion)) from Visitas where ISNUMERIC(observacion)=1 and mesaID>1 And Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate)));
				if (dataTable4.Rows.Count > 0)
				{
					num2 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable4.Rows[0][0]), 0));
				}
				dataTable4 = ((configuration.gMODO_ACCESS != 1) ? BD.ConsultaVer("select sum(monto) /sum(cast(observacion as float)) from Visitas inner join \r\n                                                    (select visitaId, sum(pago+debe) as monto from DetalleCuenta where (DetalleCuenta.Borrada =" + VariableGeneral.armarBolean(0) + ") group by visitaId) as tab1 on tab1.VisitaID = Visitas.ID            \r\n                                          where  ISNUMERIC(observacion)=1 And mesaID>1 And Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate)) : BD.ConsultaVer("select sum(monto) /sum(CLng(observacion)) from Visitas inner join \r\n                                                    (select visitaId, sum(pago+debe) as monto from DetalleCuenta where (DetalleCuenta.Borrada =" + VariableGeneral.armarBolean(0) + ") group by visitaId) as tab1 on tab1.VisitaID = Visitas.ID            \r\n                                          where  ISNUMERIC(observacion)=1 And mesaID>1 And Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate)));
				value = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable4.Rows[0][0]), 0));
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				ProjectData.ClearProjectError();
			}
		}
		if (dataTable.Rows.Count > 0)
		{
			BD.ConsultaVer(" SELECT CategoriasProduccion.Nombre , sum(DetallesProduccion.Producida) as Producida, sum(DetallesProduccion.Eliminada) as Eliminada, sum(DetallesProduccion.Reciclada) as Reciclada   FROM ((DetallesProduccion inner join Productos on DetallesProduccion.ProductoID=Productos.ID) inner join Produccion on Produccion.ProduccionID= DetallesProduccion.ProduccionID   ) inner join CategoriasProduccion on CategoriasProduccion.CategoriaProduccionID = Productos.CategoriaProduccionID    WHERE  Produccion.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate) + "  group by CategoriasProduccion.Nombre ");
		}
		System.Data.DataTable dataTable5 = new System.Data.DataTable();
		System.Data.DataTable dataTable6;
		System.Data.DataTable dataTable7;
		System.Data.DataTable dataTable8;
		System.Data.DataTable dataTable9;
		System.Data.DataTable dataTable10;
		System.Data.DataTable dataTable11;
		System.Data.DataTable dataTable12;
		System.Data.DataTable dataTable13;
		if (configuration.gComidaRapida | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Ottimo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin))
		{
			dataTable6 = (configuration.gComidaRapida ? BD.ConsultaVer("select tab1.tipoProd,tab1.Producto,sum(tab1.Cantidad) as Cantidad, sum(tab1.Pagado) as Pagado, sum(tab1.Costo) as Costo from\r\n                        ( select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  min( DetalleCuenta.Cantidad) as Cantidad,    sum( Pagos.MontoBs) as Pagado,  sum(DetalleCuenta.Costo) as Costo   from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)  inner join Pagos on Pagos.DetalleCuentaID =DetalleCuenta.id  where  (" + text2 + " and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by DetalleCuenta.ID , TiposProductos.Codigo ,Productos.Nombre ) as tab1\r\n                     group by tab1.tipoProd,tab1.Producto  order by tipoProd ,Producto") : ((configuration.gStyleBoliches1 != configuration.styleBolichesId.KulturBerlin) ? BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,    sum( DetalleCuenta.Pago) as Pagado,  sum( DetalleCuenta.Costo) as Costo   from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)   where " + text2 + " and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Codigo ,Productos.Nombre  order by TiposProductos.Codigo ,Productos.Nombre ") : BD.ConsultaVer("select tab1.tipoProd,tab1.Producto,sum(tab1.Cantidad) as Cantidad, sum(tab1.Pagado) as Pagado, sum(tab1.Costo) as Costo from\r\n                        ( select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  min( DetalleCuenta.Cantidad) as Cantidad,    sum( Pagos.MontoBs) as Pagado,  sum( DetalleCuenta.Costo) as Costo   from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)  inner join Pagos on Pagos.DetalleCuentaID =DetalleCuenta.id  where  (Pagos.MaquinaPago   like '" + text + "' and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by DetalleCuenta.ID , TiposProductos.Codigo ,Productos.Nombre ) as tab1\r\n                     group by tab1.tipoProd,tab1.Producto   order by tipoProd ,Producto")));
			dataTable7 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum(DetalleCuenta.Costo) as Costo , clientes.Nombre as Nombre  from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)  left join clientes on visitas.ClienteID =Clientes.ID   where  (" + text2 + " and  DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by clientes.Nombre, TiposProductos.Codigo,Productos.Nombre  order by clientes.Nombre, TiposProductos.Codigo ,Productos.Nombre   ");
			dataTable8 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo , clientes.Nombre as Nombre  from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)  left join clientes on visitas.ClienteID =Clientes.ID   where " + text2 + " and visitas.ParaLlevarID is not null and visitas.EnMesa=" + VariableGeneral.armarBolean(1) + " and DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by clientes.Nombre, TiposProductos.Codigo,Productos.Nombre  order by clientes.Nombre, TiposProductos.Codigo ,Productos.Nombre   ");
			dataTable9 = BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,  sum( DetalleCuenta.Pago) as Pagado  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where " + text2 + " and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Descripcion order by TiposProductos.Descripcion     ");
			dataTable10 = BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,    sum(DetalleCuenta.Debe) as Debe   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where " + text2 + " and DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Descripcion order by TiposProductos.Descripcion   ");
			dataTable11 = BD.ConsultaVer(" select Familias.Descripcion as tipoProd,  sum( DetalleCuenta.Pago) as Pagado  from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID) left join Familias on Familias.FamiliaId=TiposProductos.FamiliaID  where " + text2 + " and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by Familias.Descripcion order by Familias.Descripcion     ");
			dataTable12 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  sum(Borrados.Cantidad) as Cantidad, DetalleCuenta.Hora  as FechaPedido  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID)       inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID ) inner join Borrados on Borrados.DetalleCuentaID = DetalleCuenta.ID   where (borrados.PC like '" + text3 + "' and  (Borrados.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TiposProductos.Codigo ,Productos.Nombre, DetalleCuenta.hora  order by TiposProductos.Codigo ,Productos.Nombre ");
			dataTable13 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo, (DetalleCuenta.PrecioUnit ) as precio   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where  " + text2 + " and  DetalleCuenta.Pago=0 and DetalleCuenta.Debe=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Codigo,Productos.Nombre,DetalleCuenta.PrecioUnit   order by TiposProductos.Codigo ,Productos.Nombre  ");
		}
		else
		{
			dataTable6 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,    sum( DetalleCuenta.Pago) as Pagado,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Codigo ,Productos.Nombre  order by TiposProductos.Codigo ,Productos.Nombre ");
			dataTable7 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where visitas.ParaLlevarID is null and ( DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and  (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TiposProductos.Codigo,Productos.Nombre  UNION  select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where visitas.EnMesa=" + VariableGeneral.armarBolean(0) + " and visitas.ParaLlevarID is not null and ( DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and  (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TiposProductos.Codigo,Productos.Nombre  order by TiposProductos.Codigo ,Productos.Nombre   ");
			dataTable8 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where  visitas.EnMesa=" + VariableGeneral.armarBolean(1) + " and (visitas.ParaLlevarID  is not null and DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TiposProductos.Codigo,Productos.Nombre  order by TiposProductos.Codigo ,Productos.Nombre   ");
			dataTable9 = BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,  sum( DetalleCuenta.Pago) as Pagado  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Descripcion order by TiposProductos.Descripcion     ");
			dataTable10 = BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,    sum(DetalleCuenta.Debe) as Debe   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Descripcion order by TiposProductos.Descripcion   ");
			dataTable5 = BD.ConsultaVer("  select Clientes.Nombre ,     sum(DetalleCuenta.Debe) as Debe   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)      left join clientes on clientes.ID =Visitas.ClienteID    where DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by Clientes.Nombre  order by Clientes.Nombre    ");
			dataTable11 = BD.ConsultaVer(" select Familias.Descripcion as tipoProd,  sum( DetalleCuenta.Pago) as Pagado  from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID) left join Familias on Familias.FamiliaId=TiposProductos.FamiliaID  where DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by Familias.Descripcion order by Familias.Descripcion     ");
			dataTable12 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  sum(Borrados.Cantidad) as Cantidad,    DetalleCuenta.Hora as FechaPedido  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID)       inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID ) inner join Borrados on Borrados.DetalleCuentaID = DetalleCuenta.ID   where (borrados.PC like '" + text3 + "' and  (Borrados.fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TiposProductos.Codigo ,Productos.Nombre,DetalleCuenta.Hora  order by TiposProductos.Codigo ,Productos.Nombre ");
			dataTable13 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo  , (DetalleCuenta.PrecioUnit ) as precio  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Pago=0 and DetalleCuenta.Debe=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Codigo,Productos.Nombre,DetalleCuenta.PrecioUnit   order by TiposProductos.Codigo ,Productos.Nombre  ");
		}
		double value2 = 0.0;
		double value3 = 0.0;
		double value4 = 0.0;
		double value5 = 0.0;
		int num3 = 0;
		System.Data.DataTable dataTable14 = new System.Data.DataTable();
		System.Data.DataTable dataTable15 = BD.ConsultaVer(" select TurnoID from turnos where  (FechaIni= " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and FechaFin =  " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")");
		if (dataTable15.Rows.Count > 0)
		{
			num3 = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable15.Rows[0][0]), 0));
			dataTable14 = (System.Data.DataTable)VariableGeneral.NZ(BD.ConsultaVer("select * from Arqueo where TurnoID = " + Conversions.ToString(num3) + " order by ArqueoID desc"), 0);
			if (dataTable14.Rows.Count > 0)
			{
				value2 = Conversions.ToDouble(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["B200"]), 0), 200), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["B100"]), 0), 100)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["B50"]), 0), 50)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["B20"]), 0), 20)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["B10"]), 0), 10)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["B5"]), 0), 5)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["B2"]), 0), 2)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["B1"]), 0), 1)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["C50"]), 0), 0.5)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["C20"]), 0), 0.2)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["C10"]), 0), 0.1)));
				value3 = Conversions.ToDouble(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["D100"]), 0), 100), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["D50"]), 0), 50)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["D20"]), 0), 20)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["D10"]), 0), 10)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["D5"]), 0), 5)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["D1"]), 0), 1)));
				value4 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["Tarjetas"]), 0));
				value5 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable14.Rows[0]["_OtrasCajas"]), 0));
			}
		}
		System.Data.DataTable dataTable16 = BD.ConsultaVer("select max(nroFactura), min(nroFactura), sum(monto) as Monto, CodigoID, min(estadoSIAT) as estadoSIAT, max(FueraLineaID) as FueraLineaID  from Facturas   where Facturas.pc like '" + text + "' and  FechaEmision between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " and anulada=" + VariableGeneral.armarBolean(0) + " group by CodigoID");
		System.Data.DataTable dataTable17 = new System.Data.DataTable();
		dataTable17 = ((configuration.gMODO_ACCESS != 1) ? BD.ConsultaVer(string.Concat(string.Concat("select  isnull(TipoEnvios.Nombre,'Atencion') as Tipo,count(visitas.ID) as Cantidad, sum(tab1.monto)  as Monto ,tab1.Nombre as Cuenta  from visitas left join TipoEnvios on Visitas.TipoEnvioID =TipoEnvios.TipoEnvioID  left join ( select  visitas.id, sum (pagos.MontoBs)as  monto ,Cuentas.Nombre  from ((Visitas left join DetalleCuenta  on Visitas.ID =DetalleCuenta.VisitaID) left join pagos on DetalleCuenta.id=Pagos.DetalleCuentaID) left join Cuentas on pagos.CuentaID =cuentas.CuentaID   where DetalleCuenta.Borrada = " + VariableGeneral.armarBolean(aux: false) + "  and " + text2 + "  and Pagos.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), "group by visitas.id ,Cuentas.Nombre   ) as tab1 on Visitas.ID =tab1.ID    where tab1.monto >0 and Visitas.Fecha between ", VariableGeneral.ArmarFecha(dtpDesdeDate), " and ", VariableGeneral.ArmarFecha(dtpHastaDate)), " group by TipoEnvios.Nombre ,tab1.Nombre order by  TipoEnvios.Nombre ")) : BD.ConsultaVer("select  iif (isnull(TipoEnvios.Nombre),'Atencion',TipoEnvios.Nombre)  as Tipo,count(visitas.ID) as Cantidad, sum(tab1.monto)  as Monto,tab1.Nombre as Cuenta   from ((visitas left join TipoEnvios on Visitas.TipoEnvioID =TipoEnvios.TipoEnvioID)  left join ( select  visitas.id, sum (pagos.MontoBs)as  monto  ,Cuentas.Nombre from ((Visitas left join DetalleCuenta  on Visitas.ID =DetalleCuenta.VisitaID) left join pagos on DetalleCuenta.id=Pagos.DetalleCuentaID) left join Cuentas on pagos.CuentaID =cuentas.CuentaID   where (DetalleCuenta.Borrada = " + VariableGeneral.armarBolean(aux: false) + "  and " + text2 + " and Pagos.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by visitas.id,Cuentas.Nombre  ) as tab1 on Visitas.ID =tab1.ID  )  where (tab1.monto >0 and (Visitas.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TipoEnvios.Nombre,Cuentas.Nombre order by  TipoEnvios.Nombre "));
		System.Data.DataTable dataTable18 = new System.Data.DataTable();
		dataTable18 = ((configuration.gMODO_ACCESS != 1) ? BD.ConsultaVer(string.Concat("select  isnull(TipoEnvios.Nombre,'Mesa') as Tipo,count(visitas.ID) as Cantidad, sum(tab1.monto)  as Monto  from visitas left join TipoEnvios on Visitas.TipoEnvioID =TipoEnvios.TipoEnvioID  left join ( select  visitas.id, sum (DetalleCuenta.Pago +  DetalleCuenta.Debe) as  monto from Visitas left join DetalleCuenta  on Visitas.ID =DetalleCuenta.VisitaID  where DetalleCuenta.Borrada = " + VariableGeneral.armarBolean(aux: false) + " group by visitas.id ) as tab1 on Visitas.ID =tab1.ID    where tab1.monto >0 and Visitas.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), " group by TipoEnvios.Nombre ")) : BD.ConsultaVer(string.Concat("select  iif (isnull (TipoEnvios.Nombre),'Mesa',TipoEnvios.Nombre)  as Tipo,count(visitas.ID) as Cantidad, sum(tab1.monto)  as Monto  from ((visitas left join TipoEnvios on Visitas.TipoEnvioID =TipoEnvios.TipoEnvioID)  left join ( select  visitas.id, sum (DetalleCuenta.Pago +  DetalleCuenta.Debe) as  monto from Visitas left join DetalleCuenta  on Visitas.ID =DetalleCuenta.VisitaID  where DetalleCuenta.Borrada = " + VariableGeneral.armarBolean(aux: false) + " group by visitas.id ) as tab1 on Visitas.ID =tab1.ID  )  where tab1.monto >0 and Visitas.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), " group by TipoEnvios.Nombre ")));
		System.Data.DataTable dataTable19 = BD.ConsultaVer((" select nroFactura, Monto  from Facturas   where Facturas.pc like '" + text + "' and  anulada = " + VariableGeneral.armarBolean(1) + " and  FechaAnulacion between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable20 = BD.ConsultaVer("Sum(Monto)", "Anticipos", ("Anticipos.PC like '" + text + "' and CuentaID=" + Conversions.ToString(1) + " and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable21 = BD.ConsultaVer("Sum(Monto)", "Anticipos", ("Anticipos.PC like '" + text + "' and CuentaID=" + Conversions.ToString(2) + " and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable22 = BD.ConsultaVer("Sum(Monto)", "Anticipos", ("Anticipos.PC like '" + text + "' and CuentaID=" + Conversions.ToString(3) + " and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable23 = BD.ConsultaVer("Sum(Monto)", "Anticipos inner join Cuentas on Anticipos.CuentaID =Cuentas.CuentaID ", ("Anticipos.PC like '" + text + "' and Cuentas.Moneda  =0 and Anticipos.CuentaID> 4  and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable24 = BD.ConsultaVer("Sum(Monto)", "Anticipos inner join Cuentas on Anticipos.CuentaID =Cuentas.CuentaID ", ("Anticipos.PC like '" + text + "' and Cuentas.Moneda  =1 and Anticipos.CuentaID> 4  and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		cobradoTarjeta = Conversions.ToDouble(Operators.SubtractObject(cobradoTarjeta, NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
		{
			VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable22.Rows[0][0]), 0),
			1
		}, null, null, null)));
		double num4 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("round(sum(Gastos.Monto),1)", "Gastos", "CuentaID >" + Conversions.ToString(4) + " and CuentaID in (select cuentaID from Cuentas where Moneda =0 )  and Gastos.FechaSalida is not null and FechaSalida between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)).Rows[0][0]), 0));
		double num5 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select   sum(tab1.Pagado) from ( SELECT    round(sum(Pagos.MontoBs),1)  as Pagado FROM DetalleCuenta INNER JOIN Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID where  CuentaId= " + Conversions.ToString(4) + " and MaquinaPago like '" + text + "' and Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " group by DetalleCuenta.VisitaID) as tab1").Rows[0][0]), 0));
		double num6 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select   sum((PrecioUnit * DetalleCuenta.Cantidad) - (DetalleCuenta.Pago + DetalleCuenta.Debe)) as Descuento FROM DetalleCuenta where DetalleCuenta.Pago + DetalleCuenta.Debe>0 and Borrada =0 and  Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)).Rows[0][0]), 0));
		System.Data.DataTable dataTable25 = BD.ConsultaVer("select tab1.Nombre as Cuenta, count(*) as Cantidad from (SELECT      Cuentas.Nombre , AgruparPagoID FROM Pagos left join Cuentas on Cuentas.CuentaID = Pagos.CuentaID where MaquinaPago like '" + text + "' and Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + "  group by AgruparPagoID,  Cuentas.Nombre) as tab1  group by tab1.Nombre ");
		double num7 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select count(*) from (select visitas.ID, Visitas.ClienteID, sum(DetalleCuenta.debe) as deuda from Visitas left join DetalleCuenta on Visitas.ID =DetalleCuenta.VisitaID  where  Visitas.fecha  between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " group by visitas.ID, Visitas.ClienteID) as tab1 where tab1.deuda =0").Rows[0][0]), 0));
		double num8 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select count(*) from (select visitas.ID, Visitas.ClienteID, sum(DetalleCuenta.debe) as deuda from Visitas left join DetalleCuenta on Visitas.ID =DetalleCuenta.VisitaID  where  Visitas.fecha  between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " group by visitas.ID, Visitas.ClienteID) as tab1 where tab1.deuda >0").Rows[0][0]), 0));
		object obj2 = p;
		NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
		NewLateBinding.LateCall(obj2, null, "NormalBiggerFont", new object[0], null, null, null, IgnoreReturn: true);
		NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
		if (desdeReporteVentas)
		{
			NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "REPORTE DE VENTAS TOTALES" }, null, null, null, IgnoreReturn: true);
		}
		else
		{
			NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Reporte de Ventas " }, null, null, null, IgnoreReturn: true);
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.srPollo)
		{
			NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Turno No. " + Conversions.ToString(num3) }, null, null, null, IgnoreReturn: true);
		}
		if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza))
		{
			if (Mesero.Length > 7)
			{
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { Mesero.Substring(0, 7) }, null, null, null, IgnoreReturn: true);
			}
			else
			{
				object instance = obj2;
				object[] obj3 = new object[1] { Mesero };
				object[] array = obj3;
				bool[] obj4 = new bool[1] { true };
				bool[] array2 = obj4;
				NewLateBinding.LateCall(instance, null, "WriteLine", obj3, null, null, obj4, IgnoreReturn: true);
				if (array2[0])
				{
					Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
				}
			}
		}
		NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
		NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { DateAndTime.Now }, null, null, null, IgnoreReturn: true);
		if (!((configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza)))
		{
			if (desdeReporteVentas)
			{
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "DE TODAS LAS PC" }, null, null, null, IgnoreReturn: true);
			}
			else
			{
				object instance2 = obj2;
				object[] obj5 = new object[1] { text };
				object[] array = obj5;
				bool[] obj6 = new bool[1] { true };
				bool[] array2 = obj6;
				NewLateBinding.LateCall(instance2, null, "WriteLine", obj5, null, null, obj6, IgnoreReturn: true);
				if (array2[0])
				{
					text = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
				}
			}
		}
		ctlConfiguraciones ctlConfiguraciones2 = new ctlConfiguraciones();
		NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { (ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
		NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
		NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFechaDMA(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + "  -  " + VariableGeneral.armarSoloLaFechaDMA(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
		if (Mesero.Length > 0 && !((configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza)))
		{
			object instance3 = obj2;
			object[] obj7 = new object[1] { Mesero };
			object[] array = obj7;
			bool[] obj8 = new bool[1] { true };
			bool[] array2 = obj8;
			NewLateBinding.LateCall(instance3, null, "WriteLine", obj7, null, null, obj8, IgnoreReturn: true);
			if (array2[0])
			{
				Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
			}
		}
		NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
		double num9 = 0.0;
		double num10 = 0.0;
		checked
		{
			int num11 = dataTable7.Rows.Count - 1;
			for (int i = 0; i <= num11; i++)
			{
				num9 = Conversions.ToDouble(Operators.AddObject(num9, dataTable7.Rows[i]["Debe"]));
			}
			int num12 = dataTable8.Rows.Count - 1;
			for (int j = 0; j <= num12; j++)
			{
				num10 = Conversions.ToDouble(Operators.AddObject(num10, dataTable8.Rows[j]["Debe"]));
			}
			if (configuration.gStyleBoliches1 != configuration.styleBolichesId.SirFrancis)
			{
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Inicial (Bs) : " }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(montoIniBs, 1) }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Inicial ($Us) : " }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(montoIniDolares, 1) }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza))
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto en Caja (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(montoIniBs + cobradoBs + ventaBS + otrosBs - gastadoBs, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable20.Rows[0][0]), 0)),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
				}
			}
			double num13 = 0.0;
			System.Data.DataTable dataTable26 = new System.Data.DataTable();
			System.Data.DataTable dataTable27 = new System.Data.DataTable();
			System.Data.DataTable dataTable28 = new System.Data.DataTable();
			if (configuration.gStyleBoliches1 != configuration.styleBolichesId.BuenDia)
			{
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable20.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo (Bs): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable20.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable21.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo ($us): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable21.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable22.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo (Tarjeta): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable22.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable23.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo (Otros Bs): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable23.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable24.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo (Otros $us): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable24.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				if (cobradoBs != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas en Efectivo (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(cobradoBs, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (cobradoTarjeta != 0.0)
				{
					if (PropinaTarjeta != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas Tarjeta : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(cobradoTarjeta, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					if (PropinaTarjeta != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Propinas Tarjeta : " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(PropinaTarjeta, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "___________________" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Total Tarjeta : " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(cobradoTarjeta + PropinaTarjeta, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LomoGrill)
				{
					double num14 = Conversions.ToDouble(Operators.CompareObjectGreater(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(Operators.AddObject(num, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable23.Rows[0][0]), 0)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable24.Rows[0][0]), 0), clsTi.returnTipoCambio())),
						1
					}, null, null, null), 0, TextCompare: false));
					if (num14 != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas otras Cuentas: " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						object instance4 = obj2;
						object[] obj9 = new object[1] { num14 };
						object[] array = obj9;
						bool[] obj10 = new bool[1] { true };
						bool[] array2 = obj10;
						NewLateBinding.LateCall(instance4, null, "WriteChars", obj9, null, null, obj10, IgnoreReturn: true);
						if (array2[0])
						{
							num14 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				else if (num > 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas Otras Cuentas: " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(num5, 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas Anticipos : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(num5, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				if (!((configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspana) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspanaPanaderia)))
				{
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Total Cobros : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					num13 = Conversions.ToDouble(Operators.AddObject(Operators.AddObject(cobradoBs + cobradoTarjeta, VariableGeneral.NZ(num5, 0)), VariableGeneral.NZ(num, 0)));
					if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Subway)
					{
						num13 = Math.Round(num13, 1);
					}
					object[] array;
					bool[] array2;
					NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { num13 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
					if (array2[0])
					{
						num13 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
					}
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
				}
				if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Rinconada)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					if (num9 != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Deuda Total (Bs): " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num9, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				if (desdeReporteVentas)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					if (num9 != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas Total (Bs): " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num13 + num9, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				if (gastadoBs != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Gastos (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(gastadoBs, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (gastadoDolares != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Gastos ($Us) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(gastadoDolares, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (otrosBs != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Otros (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(otrosBs, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (otrosDolares != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Otros ($Us) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(otrosDolares, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if ((num4 != 0.0) & (configuration.gStyleBoliches1 == configuration.styleBolichesId.shiwu))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Gastos Otras Cajas (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(num4, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.UgosPizza) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Vikingo))
				{
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SirFrancis))
					{
						montoIniBs = 0.0;
						montoIniDolares = 0.0;
					}
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Final (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(montoIniBs + cobradoBs + ventaBS + otrosBs - gastadoBs, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable20.Rows[0][0]), 0)),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Final Tarjeta : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(cobradoTarjeta, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable22.Rows[0][0]), 0)),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(Operators.AddObject(num, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable23.Rows[0][0]), 0)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable24.Rows[0][0]), 0), clsTi.returnTipoCambio())),
						1
					}, null, null, null), 0, TextCompare: false))
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Final Otras Cuentas : " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							Operators.AddObject(Operators.AddObject(num, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable23.Rows[0][0]), 0)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable24.Rows[0][0]), 0), clsTi.returnTipoCambio())),
							1
						}, null, null, null) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Final ($Us) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(montoIniDolares + compraDolares - gastadoDolares + otrosDolares, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable21.Rows[0][0]), 0)),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					if (compraDolares != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Compras ($Us) : " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(compraDolares, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					if (num6 != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Descuentos (Bs): " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							VariableGeneral.NZ(num6, 0),
							1
						}, null, null, null) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					double num15 = 0.0;
					if (dataTable13.Rows.Count > 0)
					{
						int num16 = dataTable13.Rows.Count - 1;
						for (int k = 0; k <= num16; k++)
						{
							num15 = Conversions.ToDouble(Operators.AddObject(num15, NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								Operators.MultiplyObject(dataTable13.Rows[k]["Precio"], dataTable13.Rows[k]["Cantidad"]),
								2
							}, null, null, null)));
						}
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cortesia (Bs): " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							VariableGeneral.NZ(num15, 0),
							1
						}, null, null, null) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
			}
			dataTable26 = clsPagos2.DevolverEntreFechasPorMaquinaPorOtrasCuentasAnticipo(dtpDesdeDate, dtpHastaDate, configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin);
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin)
			{
				if (dataTable26.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Desglose Cobros de otras cuentas" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cuenta" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.3 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Pagado con" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					int num17 = dataTable26.Rows.Count - 1;
					for (int l = 0; l <= num17; l++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						object[] array;
						DataRow dataRow;
						bool[] array2;
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable26.Rows[l])[0] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow[0] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable26.Rows[l])[1] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.3 }, null, null, null, IgnoreReturn: true);
						object instance5 = obj2;
						object[] array3 = new object[1];
						object left = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable26.Rows[l])[2],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow[2] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array3[0] = Operators.ConcatenateObject(left, " (Bs) ");
						NewLateBinding.LateCall(instance5, null, "WriteChars", array3, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
			}
			else if (dataTable26.Rows.Count > 0)
			{
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Desglose Cobros de otras cuentas" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				int num18 = dataTable26.Rows.Count - 1;
				for (int m = 0; m <= num18; m++)
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(dataTable26.Rows[m][0], " :  ") }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					object instance6 = obj2;
					object[] array4 = new object[1];
					object[] array;
					DataRow dataRow;
					bool[] array2;
					object obj11 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
					{
						(dataRow = dataTable26.Rows[m])[1],
						1
					}, null, null, array2 = new bool[2] { true, false });
					if (array2[0])
					{
						dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
					}
					array4[0] = obj11;
					NewLateBinding.LateCall(instance6, null, "WriteChars", array4, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
			}
			double num19 = 0.0;
			dataTable27 = BD.ConsultaVer("Cuentas.Nombre , sum(MontoBs) as Total ", "Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID ", "MaquinaPago like 'Pedidos Ya%' and Fecha between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), "Cuentas.Nombre", "Cuentas.Nombre");
			dataTable28 = BD.ConsultaVer("Cuentas.Nombre , sum(MontoBs) as Total ", "Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID ", "MaquinaPago like 'Pedidos Ya%' and Fecha between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), "Cuentas.Nombre", "Cuentas.Nombre");
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.ComidaSuarez)
			{
				if (dataTable28.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Para cobrarle a Pedidos Ya" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					int num20 = dataTable28.Rows.Count - 1;
					for (int n = 0; n <= num20; n++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(dataTable28.Rows[n][0], " (Bs) :  ") }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						object instance7 = obj2;
						object[] array5 = new object[1];
						object[] array;
						DataRow dataRow;
						bool[] array2;
						object obj12 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable28.Rows[n])[1],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array5[0] = obj12;
						NewLateBinding.LateCall(instance7, null, "WriteChars", array5, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						num19 = Conversions.ToDouble(Operators.AddObject(num19, dataTable28.Rows[n][1]));
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
			}
			else if (dataTable27.Rows.Count > 0)
			{
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Para cobrarle a Pedidos Ya" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				int num21 = dataTable27.Rows.Count - 1;
				for (int num22 = 0; num22 <= num21; num22++)
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(dataTable27.Rows[num22][0], " (Bs) :  ") }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					object instance8 = obj2;
					object[] array6 = new object[1];
					object[] array;
					DataRow dataRow;
					bool[] array2;
					object obj13 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
					{
						(dataRow = dataTable27.Rows[num22])[1],
						1
					}, null, null, array2 = new bool[2] { true, false });
					if (array2[0])
					{
						dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
					}
					array6[0] = obj13;
					NewLateBinding.LateCall(instance8, null, "WriteChars", array6, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					num19 = Conversions.ToDouble(Operators.AddObject(num19, dataTable27.Rows[num22][1]));
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
			}
			double num23 = 0.0;
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen)
			{
				System.Data.DataTable dataTable29 = BD.ConsultaVer("Cuentas.Nombre , sum(MontoBs) as Total ", "Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID ", "MaquinaPago like 'QR' and Fecha between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), "Cuentas.Nombre", "Cuentas.Nombre");
				if (dataTable29.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cobros con QR" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					int num24 = dataTable29.Rows.Count - 1;
					for (int num25 = 0; num25 <= num24; num25++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(dataTable29.Rows[num25][0], " (Bs) :  ") }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						object instance9 = obj2;
						object[] array7 = new object[1];
						object[] array;
						DataRow dataRow;
						bool[] array2;
						object obj14 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable29.Rows[num25])[1],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array7[0] = obj14;
						NewLateBinding.LateCall(instance9, null, "WriteChars", array7, null, null, null, IgnoreReturn: true);
						num23 = Conversions.ToDouble(Operators.AddObject(num23, dataTable29.Rows[num25][1]));
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
			}
			if (((configuration.gStyleBoliches1 != configuration.styleBolichesId.InesEspana) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.InesEspanaPanaderia)) && ((num19 > 0.0) | (num23 > 0.0)))
			{
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "TOTAL VENTAS" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num13 + num19 + num23, 1) }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen)
			{
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "TOTAL INGRESO" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
				{
					Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(cobradoBs + cobradoTarjeta, VariableGeneral.NZ(num, 0)), num19), num23), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable20.Rows[0][0]), 0)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable21.Rows[0][0]), 0), clsTi.returnTipoCambio())), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable22.Rows[0][0]), 0)), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable23.Rows[0][0]), 0)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable24.Rows[0][0]), 0), clsTi.returnTipoCambio())),
					1
				}, null, null, null) }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
			}
			System.Data.DataTable dataTable30 = BD.ConsultaVer("select Productos.Nombre as Producto,  \r\n            sum(DetalleCuenta.Debe) as Debe \r\n            from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) \r\n            inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     \r\n            where  visitas.EnMesa =1 and (DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    \r\n            (DetalleCuenta.Hora between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + "))  group by Productos.Nombre   ");
			double num26 = 0.0;
			if (dataTable30.Rows.Count > 0)
			{
				int num27 = dataTable30.Rows.Count - 1;
				for (int num28 = 0; num28 <= num27; num28++)
				{
					num26 = Conversions.ToDouble(Operators.AddObject(num26, dataTable30.Rows[num28]["Debe"]));
				}
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas Abiertas" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num26, 2) }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.NotMac)
			{
				System.Data.DataTable dataTable31 = BD.ConsultaVer("select meseros.Nombre \r\n                                from pagos left join meseros on pagos.MeseroID =Meseros.MeseroID \r\n                                where MaquinaPago like '" + text + "'  and pagos.Fecha \r\n                                between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " group by meseros.Nombre ");
				if (dataTable31.Rows.Count > 0)
				{
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "PERSONAL DE VENTAS" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					int num29 = dataTable31.Rows.Count - 1;
					for (int num30 = 0; num30 <= num29; num30++)
					{
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable31.Rows[num30]["Nombre"]), "") }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
			{
				NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "NormalBiggerFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Reporte de Ventas " }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { DateAndTime.Now }, null, null, null, IgnoreReturn: true);
				object instance10 = obj2;
				object[] obj15 = new object[1] { text };
				object[] array = obj15;
				bool[] obj16 = new bool[1] { true };
				bool[] array2 = obj16;
				NewLateBinding.LateCall(instance10, null, "WriteLine", obj15, null, null, obj16, IgnoreReturn: true);
				if (array2[0])
				{
					text = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { (ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + " - " + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
				if (Mesero.Length > 0)
				{
					object instance11 = obj2;
					object[] obj17 = new object[1] { Mesero };
					array = obj17;
					bool[] obj18 = new bool[1] { true };
					array2 = obj18;
					NewLateBinding.LateCall(instance11, null, "WriteLine", obj17, null, null, obj18, IgnoreReturn: true);
					if (array2[0])
					{
						Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
					}
				}
				NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Sansha && dataTable25.Rows.Count > 0)
			{
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Cant. Transacciones" }, null, null, null, IgnoreReturn: true);
				int num31 = dataTable25.Rows.Count - 1;
				for (int num32 = 0; num32 <= num31; num32++)
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					object instance12 = obj2;
					object[] array8 = new object[1];
					DataRow dataRow;
					object[] array;
					bool[] array2;
					object left2 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
					{
						(dataRow = dataTable25.Rows[num32])["Cantidad"],
						1
					}, null, null, array2 = new bool[2] { true, false });
					if (array2[0])
					{
						dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
					}
					array8[0] = Operators.ConcatenateObject(left2, "    ");
					NewLateBinding.LateCall(instance12, null, "WriteChars", array8, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
					string text4 = Conversions.ToString(dataTable25.Rows[num32]["Cuenta"]);
					NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text4 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
					if (array2[0])
					{
						text4 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
			}
			NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
			int num33 = 0;
			if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.Rinconada) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.SanHo))
			{
				if (((configuration.gStyleBoliches1 != configuration.styleBolichesId.SirFrancis) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Naoki) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.EspigaDeOro)) && !(ciego & (configuration.gStyleBoliches1 == configuration.styleBolichesId.Meraki)))
				{
					if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.MicromercadoPasse) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.DonMiguel) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.PizzaGrande))
					{
						NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
						{
							NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						}
						if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.NotMac) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Serendipity) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.LaSuisse) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Vulcanica))
						{
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Container))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Productos Cobrados" }, null, null, null, IgnoreReturn: true);
							}
							else
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Ventas Totales" }, null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							int num34 = dataTable6.Rows.Count - 1;
							for (int num35 = 0; num35 <= num34; num35++)
							{
								if (Conversions.ToBoolean(Operators.AndObject(configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza, Operators.CompareObjectEqual(dataTable6.Rows[num35]["Producto"], "BORDE CATUPIRY", TextCompare: false))))
								{
									continue;
								}
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								object instance13 = obj2;
								object[] array9 = new object[1];
								Type typeFromHandle = typeof(Math);
								DataRow dataRow;
								object[] obj19 = new object[2]
								{
									(dataRow = dataTable6.Rows[num35])["Cantidad"],
									1
								};
								object[] array = obj19;
								bool[] obj20 = new bool[2] { true, false };
								bool[] array2 = obj20;
								object left3 = NewLateBinding.LateGet(null, typeFromHandle, "Round", obj19, null, null, obj20);
								if (array2[0])
								{
									dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
								}
								array9[0] = Operators.ConcatenateObject(left3, "   ");
								NewLateBinding.LateCall(instance13, null, "WriteChars", array9, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
								object left4 = num33;
								object right = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
								{
									(dataRow = dataTable6.Rows[num35])["Cantidad"],
									1
								}, null, null, array2 = new bool[2] { true, false });
								if (array2[0])
								{
									dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
								}
								num33 = Conversions.ToInteger(Operators.AddObject(left4, right));
								string text5 = "";
								text5 = ((!((configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SantaMaria))) ? Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable6.Rows[num35]["tipoProd"], " - "), dataTable6.Rows[num35]["Producto"])) : Conversions.ToString(dataTable6.Rows[num35]["Producto"]));
								NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text5 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
								if (array2[0])
								{
									text5 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
								}
								if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Rinconada)
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 6 }, null, null, null, IgnoreReturn: true);
									object instance14 = obj2;
									object[] array10 = new object[1];
									Type typeFromHandle2 = typeof(Math);
									object[] obj21 = new object[2]
									{
										(dataRow = dataTable6.Rows[num35])["Pagado"],
										2
									};
									array = obj21;
									bool[] obj22 = new bool[2] { true, false };
									array2 = obj22;
									object left5 = NewLateBinding.LateGet(null, typeFromHandle2, "Round", obj21, null, null, obj22);
									if (array2[0])
									{
										dataRow["Pagado"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									array10[0] = Operators.ConcatenateObject(left5, "   ");
									NewLateBinding.LateCall(instance14, null, "WriteChars", array10, null, null, null, IgnoreReturn: true);
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "______________________" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Conversions.ToString(num33) + "    ITEMS VENDIDOS " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.SantaMaria)
							{
								System.Data.DataTable dataTable32 = BD.ConsultaVer("select tab1.tipoProd,tab1.Producto,sum(tab1.Cantidad) as Cantidad, sum(tab1.Pagado) as Pagado from\r\n                                    ( select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  min( DetalleCuenta.Cantidad) as Cantidad,    sum( Pagos.MontoBs) as Pagado from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)  inner join Pagos on Pagos.DetalleCuentaID =DetalleCuenta.id  where  (Visitas.TipoEnvioID = (select top 1 TipoEnvioID from TipoEnvios where nombre like '%PEDIDOS%YA%') and " + text2 + " and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by DetalleCuenta.ID , TiposProductos.Codigo ,Productos.Nombre ) as tab1\r\n                                     group by tab1.tipoProd,tab1.Producto  order by tipoProd ,Producto");
								if (dataTable32.Rows.Count > 0)
								{
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Ventas Totales Pedidos Ya" }, null, null, null, IgnoreReturn: true);
									int num36 = 0;
									int num37 = dataTable32.Rows.Count - 1;
									for (int num38 = 0; num38 <= num37; num38++)
									{
										NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
										object instance15 = obj2;
										object[] array11 = new object[1];
										DataRow dataRow;
										object[] array;
										bool[] array2;
										object left6 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
										{
											(dataRow = dataTable32.Rows[num38])["Cantidad"],
											1
										}, null, null, array2 = new bool[2] { true, false });
										if (array2[0])
										{
											dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
										}
										array11[0] = Operators.ConcatenateObject(left6, "   ");
										NewLateBinding.LateCall(instance15, null, "WriteChars", array11, null, null, null, IgnoreReturn: true);
										NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
										object left7 = num36;
										object right2 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
										{
											(dataRow = dataTable32.Rows[num38])["Cantidad"],
											1
										}, null, null, array2 = new bool[2] { true, false });
										if (array2[0])
										{
											dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
										}
										num36 = Conversions.ToInteger(Operators.AddObject(left7, right2));
										string text6 = Conversions.ToString(dataTable32.Rows[num38]["Producto"]);
										NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text6 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
										if (array2[0])
										{
											text6 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
										}
										NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
									}
									NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "______________________" }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Conversions.ToString(num36) + "    ITEMS VENDIDOS " }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								}
							}
						}
						if (dataTable7.Rows.Count > 0)
						{
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Productos por Cobrar" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							int num39 = dataTable7.Rows.Count - 1;
							for (int num40 = 0; num40 <= num39; num40++)
							{
								if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin)
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									object instance16 = obj2;
									object[] array12 = new object[1];
									Type typeFromHandle3 = typeof(Math);
									DataRow dataRow;
									object[] obj23 = new object[2]
									{
										(dataRow = dataTable7.Rows[num40])["Cantidad"],
										1
									};
									object[] array = obj23;
									bool[] obj24 = new bool[2] { true, false };
									bool[] array2 = obj24;
									object left8 = NewLateBinding.LateGet(null, typeFromHandle3, "Round", obj23, null, null, obj24);
									if (array2[0])
									{
										dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									array12[0] = Operators.ConcatenateObject(left8, "    ");
									NewLateBinding.LateCall(instance16, null, "WriteChars", array12, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
									int length = 13;
									if (dataTable7.Rows[num40]["Producto"].ToString().Length < 13)
									{
										length = dataTable7.Rows[num40]["Producto"].ToString().Length;
									}
									NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { dataTable7.Rows[num40]["Producto"].ToString().Substring(0, length) }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
									object value6 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
									{
										(dataRow = dataTable7.Rows[num40])["Debe"],
										1
									}, null, null, array2 = new bool[2] { true, false });
									if (array2[0])
									{
										dataRow["Debe"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									double num41 = Conversions.ToDouble(value6);
									NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Conversions.ToString(num41) + " Bs." }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4.5 }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable7.Rows[num40]["Nombre"]), "") }, null, null, null, IgnoreReturn: true);
								}
								else
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									object instance17 = obj2;
									object[] array13 = new object[1];
									Type typeFromHandle4 = typeof(Math);
									DataRow dataRow;
									object[] obj25 = new object[2]
									{
										(dataRow = dataTable7.Rows[num40])["Cantidad"],
										1
									};
									object[] array = obj25;
									bool[] obj26 = new bool[2] { true, false };
									bool[] array2 = obj26;
									object left9 = NewLateBinding.LateGet(null, typeFromHandle4, "Round", obj25, null, null, obj26);
									if (array2[0])
									{
										dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									array13[0] = Operators.ConcatenateObject(left9, "    ");
									NewLateBinding.LateCall(instance17, null, "WriteChars", array13, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
									string text7 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable7.Rows[num40]["tipoProd"], " - "), dataTable7.Rows[num40]["Producto"]));
									NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text7 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
									if (array2[0])
									{
										text7 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
									}
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						if (dataTable8.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Productos para entregar" }, null, null, null, IgnoreReturn: true);
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							int num42 = dataTable8.Rows.Count - 1;
							for (int num43 = 0; num43 <= num42; num43++)
							{
								if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin)
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									object instance18 = obj2;
									object[] array14 = new object[1];
									Type typeFromHandle5 = typeof(Math);
									DataRow dataRow;
									object[] obj27 = new object[2]
									{
										(dataRow = dataTable8.Rows[num43])["Cantidad"],
										1
									};
									object[] array = obj27;
									bool[] obj28 = new bool[2] { true, false };
									bool[] array2 = obj28;
									object left10 = NewLateBinding.LateGet(null, typeFromHandle5, "Round", obj27, null, null, obj28);
									if (array2[0])
									{
										dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									array14[0] = Operators.ConcatenateObject(left10, "    ");
									NewLateBinding.LateCall(instance18, null, "WriteChars", array14, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
									int length2 = 13;
									if (dataTable8.Rows[num43]["Producto"].ToString().Length < 13)
									{
										length2 = dataTable8.Rows[num43]["Producto"].ToString().Length;
									}
									NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { dataTable8.Rows[num43]["Producto"].ToString().Substring(0, length2) }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
									object value7 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
									{
										(dataRow = dataTable8.Rows[num43])["Debe"],
										1
									}, null, null, array2 = new bool[2] { true, false });
									if (array2[0])
									{
										dataRow["Debe"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									double num44 = Conversions.ToDouble(value7);
									NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Conversions.ToString(num44) + " Bs." }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4.5 }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable8.Rows[num43]["Nombre"]), "") }, null, null, null, IgnoreReturn: true);
								}
								else
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									object instance19 = obj2;
									object[] array15 = new object[1];
									Type typeFromHandle6 = typeof(Math);
									DataRow dataRow;
									object[] obj29 = new object[2]
									{
										(dataRow = dataTable8.Rows[num43])["Cantidad"],
										1
									};
									object[] array = obj29;
									bool[] obj30 = new bool[2] { true, false };
									bool[] array2 = obj30;
									object left11 = NewLateBinding.LateGet(null, typeFromHandle6, "Round", obj29, null, null, obj30);
									if (array2[0])
									{
										dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									array15[0] = Operators.ConcatenateObject(left11, "    ");
									NewLateBinding.LateCall(instance19, null, "WriteChars", array15, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
									string text8 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable8.Rows[num43]["tipoProd"], " - "), dataTable8.Rows[num43]["Producto"]));
									NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text8 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
									if (array2[0])
									{
										text8 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
									}
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.IrishPub)
						{
							System.Data.DataTable dataTable33 = new System.Data.DataTable();
							System.Data.DataTable dataTable34 = new System.Data.DataTable();
							System.Data.DataTable dataTable35 = new System.Data.DataTable();
							dataTable33 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID  inner join Clientes on Clientes.ID=Visitas.ClienteID where DetalleCuenta.Pago=0 and DetalleCuenta.Debe=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") and Clientes.Nombre like 'ERROR TURNO NOCHE'  group by TiposProductos.Codigo,Productos.Nombre   order by TiposProductos.Codigo ,Productos.Nombre  ");
							dataTable34 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID inner join Clientes on Clientes.ID=Visitas.ClienteID  where DetalleCuenta.Pago=0 and DetalleCuenta.Debe=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") and Clientes.Nombre like 'ERROR TURNO MAÑANA' group by TiposProductos.Codigo,Productos.Nombre   order by TiposProductos.Codigo ,Productos.Nombre  ");
							dataTable35 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID  inner join Clientes on Clientes.ID=Visitas.ClienteID where DetalleCuenta.Pago=0 and DetalleCuenta.Debe=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") and Clientes.Nombre like 'PREPARACION TRAGOS'  group by TiposProductos.Codigo,Productos.Nombre   order by TiposProductos.Codigo ,Productos.Nombre  ");
							dataTable13 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo, (DetalleCuenta.PrecioUnit ) as precio    from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID  inner join Clientes on Clientes.ID=Visitas.ClienteID where DetalleCuenta.Pago=0 and DetalleCuenta.Debe=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") and Clientes.Nombre <>  'ERROR TURNO NOCHE' and Clientes.Nombre <> 'ERROR TURNO MAÑANA' and Clientes.Nombre <> 'PREPARACION TRAGOS'  group by TiposProductos.Codigo,Productos.Nombre,DetalleCuenta.PrecioUnit   order by TiposProductos.Codigo ,Productos.Nombre  ");
							if (dataTable13.Rows.Count > 0)
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Productos de Cortesia" }, null, null, null, IgnoreReturn: true);
								int num45 = dataTable13.Rows.Count - 1;
								for (int num46 = 0; num46 <= num45; num46++)
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									object instance20 = obj2;
									object[] array16 = new object[1];
									DataRow dataRow;
									object[] array;
									bool[] array2;
									object left12 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
									{
										(dataRow = dataTable13.Rows[num46])["Cantidad"],
										1
									}, null, null, array2 = new bool[2] { true, false });
									if (array2[0])
									{
										dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									array16[0] = Operators.ConcatenateObject(left12, "    ");
									NewLateBinding.LateCall(instance20, null, "WriteChars", array16, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
									string text9 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable13.Rows[num46]["tipoProd"], " - "), dataTable13.Rows[num46]["Producto"]));
									NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text9 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
									if (array2[0])
									{
										text9 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
									}
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							if (dataTable33.Rows.Count > 0)
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Errores Turno Noche" }, null, null, null, IgnoreReturn: true);
								int num47 = dataTable33.Rows.Count - 1;
								for (int num48 = 0; num48 <= num47; num48++)
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									object instance21 = obj2;
									object[] array17 = new object[1];
									DataRow dataRow;
									object[] array;
									bool[] array2;
									object left13 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
									{
										(dataRow = dataTable33.Rows[num48])["Cantidad"],
										1
									}, null, null, array2 = new bool[2] { true, false });
									if (array2[0])
									{
										dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									array17[0] = Operators.ConcatenateObject(left13, "    ");
									NewLateBinding.LateCall(instance21, null, "WriteChars", array17, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
									string text10 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable33.Rows[num48]["tipoProd"], " - "), dataTable33.Rows[num48]["Producto"]));
									NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text10 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
									if (array2[0])
									{
										text10 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
									}
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							if (dataTable34.Rows.Count > 0)
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Errores turno mañana" }, null, null, null, IgnoreReturn: true);
								int num49 = dataTable34.Rows.Count - 1;
								for (int num50 = 0; num50 <= num49; num50++)
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									object instance22 = obj2;
									object[] array18 = new object[1];
									DataRow dataRow;
									object[] array;
									bool[] array2;
									object left14 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
									{
										(dataRow = dataTable34.Rows[num50])["Cantidad"],
										1
									}, null, null, array2 = new bool[2] { true, false });
									if (array2[0])
									{
										dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									array18[0] = Operators.ConcatenateObject(left14, "    ");
									NewLateBinding.LateCall(instance22, null, "WriteChars", array18, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
									string text11 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable34.Rows[num50]["tipoProd"], " - "), dataTable34.Rows[num50]["Producto"]));
									NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text11 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
									if (array2[0])
									{
										text11 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
									}
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							if (dataTable35.Rows.Count > 0)
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Preparacion de Tragos" }, null, null, null, IgnoreReturn: true);
								int num51 = dataTable35.Rows.Count - 1;
								for (int num52 = 0; num52 <= num51; num52++)
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									object instance23 = obj2;
									object[] array19 = new object[1];
									DataRow dataRow;
									object[] array;
									bool[] array2;
									object left15 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
									{
										(dataRow = dataTable35.Rows[num52])["Cantidad"],
										1
									}, null, null, array2 = new bool[2] { true, false });
									if (array2[0])
									{
										dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
									}
									array19[0] = Operators.ConcatenateObject(left15, "    ");
									NewLateBinding.LateCall(instance23, null, "WriteChars", array19, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
									string text12 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable35.Rows[num52]["tipoProd"], " - "), dataTable35.Rows[num52]["Producto"]));
									NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text12 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
									if (array2[0])
									{
										text12 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
									}
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
						}
						else if (dataTable13.Rows.Count > 0)
						{
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
							{
								NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "NormalBiggerFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Reporte de Ventas " }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { DateAndTime.Now }, null, null, null, IgnoreReturn: true);
								object instance24 = obj2;
								object[] obj31 = new object[1] { text };
								object[] array = obj31;
								bool[] obj32 = new bool[1] { true };
								bool[] array2 = obj32;
								NewLateBinding.LateCall(instance24, null, "WriteLine", obj31, null, null, obj32, IgnoreReturn: true);
								if (array2[0])
								{
									text = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { (ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + " - " + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
								if (Mesero.Length > 0)
								{
									object instance25 = obj2;
									object[] obj33 = new object[1] { Mesero };
									array = obj33;
									bool[] obj34 = new bool[1] { true };
									array2 = obj34;
									NewLateBinding.LateCall(instance25, null, "WriteLine", obj33, null, null, obj34, IgnoreReturn: true);
									if (array2[0])
									{
										Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
									}
								}
								NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Productos de Cortesia" }, null, null, null, IgnoreReturn: true);
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							int num53 = dataTable13.Rows.Count - 1;
							for (int num54 = 0; num54 <= num53; num54++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								object instance26 = obj2;
								object[] array20 = new object[1];
								DataRow dataRow;
								object[] array;
								bool[] array2;
								object left16 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
								{
									(dataRow = dataTable13.Rows[num54])["Cantidad"],
									1
								}, null, null, array2 = new bool[2] { true, false });
								if (array2[0])
								{
									dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
								}
								array20[0] = Operators.ConcatenateObject(left16, "    ");
								NewLateBinding.LateCall(instance26, null, "WriteChars", array20, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
								string text13 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable13.Rows[num54]["tipoProd"], " - "), dataTable13.Rows[num54]["Producto"]));
								NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text13 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
								if (array2[0])
								{
									text13 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						if (dataTable12.Rows.Count > 0)
						{
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
							{
								NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "NormalBiggerFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Reporte de Ventas " }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { DateAndTime.Now }, null, null, null, IgnoreReturn: true);
								object instance27 = obj2;
								object[] obj35 = new object[1] { text };
								object[] array = obj35;
								bool[] obj36 = new bool[1] { true };
								bool[] array2 = obj36;
								NewLateBinding.LateCall(instance27, null, "WriteLine", obj35, null, null, obj36, IgnoreReturn: true);
								if (array2[0])
								{
									text = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { (ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + " - " + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
								if (Mesero.Length > 0)
								{
									object instance28 = obj2;
									object[] obj37 = new object[1] { Mesero };
									array = obj37;
									bool[] obj38 = new bool[1] { true };
									array2 = obj38;
									NewLateBinding.LateCall(instance28, null, "WriteLine", obj37, null, null, obj38, IgnoreReturn: true);
									if (array2[0])
									{
										Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
									}
								}
								NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Productos Borrados" }, null, null, null, IgnoreReturn: true);
							if (Operators.CompareString(text3, "%%", TextCompare: false) == 0)
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "de cualquier PC" }, null, null, null, IgnoreReturn: true);
							}
							else
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "desde: " + text3 }, null, null, null, IgnoreReturn: true);
							}
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							int num55 = dataTable12.Rows.Count - 1;
							for (int num56 = 0; num56 <= num55; num56++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								object instance29 = obj2;
								object[] array21 = new object[1];
								DataRow dataRow;
								object[] array;
								bool[] array2;
								object left17 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
								{
									(dataRow = dataTable12.Rows[num56])["Cantidad"],
									1
								}, null, null, array2 = new bool[2] { true, false });
								if (array2[0])
								{
									dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
								}
								array21[0] = Operators.ConcatenateObject(left17, "    ");
								NewLateBinding.LateCall(instance29, null, "WriteChars", array21, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.armarSoloLaFechaDMA(Conversions.ToDate(dataTable12.Rows[num56]["FechaPedido"])) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.8 }, null, null, null, IgnoreReturn: true);
								string text14 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable12.Rows[num56]["tipoProd"], " - "), dataTable12.Rows[num56]["Producto"]));
								NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text14 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
								if (array2[0])
								{
									text14 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
					{
						NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "NormalBiggerFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Reporte de Ventas " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { DateAndTime.Now }, null, null, null, IgnoreReturn: true);
						object instance30 = obj2;
						object[] obj39 = new object[1] { text };
						object[] array = obj39;
						bool[] obj40 = new bool[1] { true };
						bool[] array2 = obj40;
						NewLateBinding.LateCall(instance30, null, "WriteLine", obj39, null, null, obj40, IgnoreReturn: true);
						if (array2[0])
						{
							text = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { (ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + " - " + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
						if (Mesero.Length > 0)
						{
							object instance31 = obj2;
							object[] obj41 = new object[1] { Mesero };
							array = obj41;
							bool[] obj42 = new bool[1] { true };
							array2 = obj42;
							NewLateBinding.LateCall(instance31, null, "WriteLine", obj41, null, null, obj42, IgnoreReturn: true);
							if (array2[0])
							{
								Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
							}
						}
						NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					double num57 = 0.0;
					if (configuration.gStyleBoliches1 != configuration.styleBolichesId.BuenDia)
					{
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Ventas por Categorias (Bs)" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
						if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						int num58 = dataTable9.Rows.Count - 1;
						for (int num59 = 0; num59 <= num58; num59++)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							object instance32 = obj2;
							object[] array22 = new object[1];
							object[] array;
							DataRow dataRow;
							bool[] array2;
							object left18 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
							{
								(dataRow = dataTable9.Rows[num59])["Pagado"],
								1
							}, null, null, array2 = new bool[2] { true, false });
							if (array2[0])
							{
								dataRow["Pagado"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							array22[0] = Operators.ConcatenateObject(left18, "    ");
							NewLateBinding.LateCall(instance32, null, "WriteChars", array22, null, null, null, IgnoreReturn: true);
							object left19 = num57;
							object right3 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
							{
								(dataRow = dataTable9.Rows[num59])["Pagado"],
								1
							}, null, null, array2 = new bool[2] { true, false });
							if (array2[0])
							{
								dataRow["Pagado"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							num57 = Conversions.ToDouble(Operators.AddObject(left19, right3));
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { dataTable9.Rows[num59]["tipoProd"].ToString().Trim() }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						if (dataTable10.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Deudas por Categorias (Bs)" }, null, null, null, IgnoreReturn: true);
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							int num60 = dataTable10.Rows.Count - 1;
							for (int num61 = 0; num61 <= num60; num61++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								object instance33 = obj2;
								object[] array23 = new object[1];
								object[] array;
								DataRow dataRow;
								bool[] array2;
								object left20 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
								{
									(dataRow = dataTable10.Rows[num61])["Debe"],
									1
								}, null, null, array2 = new bool[2] { true, false });
								if (array2[0])
								{
									dataRow["Debe"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
								}
								array23[0] = Operators.ConcatenateObject(left20, "    ");
								NewLateBinding.LateCall(instance33, null, "WriteChars", array23, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { dataTable10.Rows[num61]["tipoProd"].ToString().Trim() }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
						}
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						if (dataTable5.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Deudas por Clientes (Bs)" }, null, null, null, IgnoreReturn: true);
							int num62 = dataTable5.Rows.Count - 1;
							for (int num63 = 0; num63 <= num62; num63++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								object instance34 = obj2;
								object[] array24 = new object[1];
								object[] array;
								DataRow dataRow;
								bool[] array2;
								object left21 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
								{
									(dataRow = dataTable5.Rows[num63])["Debe"],
									1
								}, null, null, array2 = new bool[2] { true, false });
								if (array2[0])
								{
									dataRow["Debe"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
								}
								array24[0] = Operators.ConcatenateObject(left21, "    ");
								NewLateBinding.LateCall(instance34, null, "WriteChars", array24, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { dataTable5.Rows[num63]["Nombre"].ToString().Trim() }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
						}
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
					{
						NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "NormalBiggerFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Reporte de Ventas " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { DateAndTime.Now }, null, null, null, IgnoreReturn: true);
						object instance35 = obj2;
						object[] obj43 = new object[1] { text };
						object[] array = obj43;
						bool[] obj44 = new bool[1] { true };
						bool[] array2 = obj44;
						NewLateBinding.LateCall(instance35, null, "WriteLine", obj43, null, null, obj44, IgnoreReturn: true);
						if (array2[0])
						{
							text = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { (ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + " - " + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
						if (Mesero.Length > 0)
						{
							object instance36 = obj2;
							object[] obj45 = new object[1] { Mesero };
							array = obj45;
							bool[] obj46 = new bool[1] { true };
							array2 = obj46;
							NewLateBinding.LateCall(instance36, null, "WriteLine", obj45, null, null, obj46, IgnoreReturn: true);
							if (array2[0])
							{
								Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
							}
						}
						NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
					}
					if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.BuenDia) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Serendipity) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.LaGaleria) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Vikingo) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.UgosPizza) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Vulcanica))
					{
						if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Ventas por Familia (Bs)" }, null, null, null, IgnoreReturn: true);
						if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						int num64 = dataTable11.Rows.Count - 1;
						for (int num65 = 0; num65 <= num64; num65++)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							object instance37 = obj2;
							object[] array25 = new object[1];
							object[] array;
							DataRow dataRow;
							bool[] array2;
							object left22 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
							{
								(dataRow = dataTable11.Rows[num65])["Pagado"],
								1
							}, null, null, array2 = new bool[2] { true, false });
							if (array2[0])
							{
								dataRow["Pagado"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							array25[0] = Operators.ConcatenateObject(left22, "    ");
							NewLateBinding.LateCall(instance37, null, "WriteChars", array25, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { dataTable11.Rows[num65]["tipoProd"].ToString().Trim() }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Dalias15)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Productos Secundarios" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
						System.Data.DataTable dataTable36 = BD.ConsultaVer("select Nombre, sum(ProdSec) as ProdSec\t\t\tfrom(SELECT Productos.Nombre,  iif((select min(d1.Hora) from DetalleCuenta as d1 where d1.VisitaID = Visitas.ID)<DetalleCuenta.Hora ,DetalleCuenta.Cantidad,0) as ProdSec\t\t\tFROM                     Visitas INNER JOIN\t\t\t DetalleCuenta  ON DetalleCuenta.VisitaID = Visitas.ID INNER JOIN\t\t\t                         Productos ON DetalleCuenta.ProductoID = Productos.ID LEFT OUTER JOIN\t\t\t                         Mesas ON Visitas.MesaID = Mesas.ID\t\t\twhere DetalleCuenta.Borrada =0 and Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " ) as tab1 where ProdSec>0 group by Nombre\torder by Nombre\t");
						int num66 = dataTable36.Rows.Count - 1;
						for (int num67 = 0; num67 <= num66; num67++)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							string text15 = Conversions.ToString(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable36.Rows[num67]["ProdSec"]), 0) }, null, null, null));
							object[] array;
							bool[] array2;
							NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text15 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
							if (array2[0])
							{
								text15 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
							}
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.7 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable36.Rows[num67]["Nombre"]), "") }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					if (Operators.ConditionalCompareObjectGreater(Operators.SubtractObject(Operators.AddObject(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(cobradoBs + cobradoTarjeta, VariableGeneral.NZ(num5, 0)),
						1
					}, null, null, null), num), num57), 0, TextCompare: false))
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						string text16 = "cast (DetalleCuenta.Hora as date)";
						if (configuration.gMODO_ACCESS != 0)
						{
							text16 = "CDate(DetalleCuenta.Hora) ";
						}
						System.Data.DataTable dataTable37 = BD.ConsultaVer(text16 + " as fecha, Pagos.MontoBs  ,Cuentas.Nombre as caja, clientes.Nombre", "(((Pagos left join DetalleCuenta on Pagos.DetalleCuentaID =DetalleCuenta.ID )\r\n                                left join visitas on DetalleCuenta.VisitaID =Visitas.ID) left join clientes on Clientes.ID =Visitas.ClienteID)\r\n                                left join cuentas on Pagos.CuentaID =Cuentas.CuentaID ", "Pagos.MaquinaPago like '" + clsPagos2._MaquinaPago + "' and Pagos.fecha between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " and  Hora not between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate));
						NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " Cobrado de Cuentas Anteriores" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Fecha Venta" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.8 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Pagado Con" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cliente" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
						double num68 = 0.0;
						int num69 = dataTable37.Rows.Count - 1;
						for (int num70 = 0; num70 <= num69; num70++)
						{
							object[] array;
							DataRow dataRow;
							bool[] array2;
							object left23 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
							{
								(dataRow = dataTable37.Rows[num70])["MontoBs"],
								1
							}, null, null, array2 = new bool[2] { true, false });
							if (array2[0])
							{
								dataRow["MontoBs"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							if (Operators.ConditionalCompareObjectNotEqual(left23, 0, TextCompare: false))
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								object instance38 = obj2;
								object instance39;
								object[] obj47 = new object[1] { NewLateBinding.LateGet(instance39 = dataTable37.Rows[num70]["Fecha"], null, "ToShortDateString", new object[0], null, null, null) };
								array = obj47;
								bool[] obj48 = new bool[1] { true };
								array2 = obj48;
								NewLateBinding.LateCall(instance38, null, "WriteChars", obj47, null, null, obj48, IgnoreReturn: true);
								if (array2[0])
								{
									NewLateBinding.LateSetComplex(instance39, null, "ToShortDateString", new object[1] { array[0] }, null, null, OptimisticSet: true, RValueBase: true);
								}
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.8 }, null, null, null, IgnoreReturn: true);
								object instance40 = obj2;
								object[] array26 = new object[1];
								object left24 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
								{
									(dataRow = dataTable37.Rows[num70])["MontoBs"],
									1
								}, null, null, array2 = new bool[2] { true, false });
								if (array2[0])
								{
									dataRow["MontoBs"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
								}
								array26[0] = Operators.ConcatenateObject(left24, " (Bs) ");
								NewLateBinding.LateCall(instance40, null, "WriteChars", array26, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
								num68 = Conversions.ToDouble(Operators.AddObject(num68, dataTable37.Rows[num70]["MontoBs"]));
								int length3 = 13;
								if (dataTable37.Rows[num70]["Caja"].ToString().Length < 13)
								{
									length3 = dataTable37.Rows[num70]["Caja"].ToString().Length;
								}
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { dataTable37.Rows[num70]["Caja"].ToString().Substring(0, length3) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable37.Rows[num70]["Nombre"]), "") }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
						}
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.3 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num68, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " Monto Cobrado de cuentas anteriores" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.3 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num57, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " Monto Total de ventas del dia" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					}
					if (configuration.gManejaElServicio)
					{
						System.Data.DataTable dataTable38 = new clsLogg().devolverBorrandoServicio(dtpDesdeDate, dtpHastaDate);
						if (dataTable38.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Servicios Borrados" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
							int num71 = dataTable38.Rows.Count - 1;
							for (int num72 = 0; num72 <= num71; num72++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								DateTime dateTime = Conversions.ToDate(dataTable38.Rows[num72]["Fecha"]);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { dateTime.ToString("HH:mm", CultureInfo.InvariantCulture) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.8 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject("  ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable38.Rows[num72]["Accion"]), "")) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
						}
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.DonMiguel)
					{
						double num73 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Compute("Sum(Costo)", "1=1")), "0"));
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Precio Produccion Pagados: " + Conversions.ToString(Math.Round(num73, 1)) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " 8 %: " + Conversions.ToString(Math.Round(num73 * 0.08, 1)) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " 2 %: " + Conversions.ToString(Math.Round(num73 * 0.02, 1)) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						num73 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable7.Compute("Sum(Costo)", "1=1")), 0));
						if (num73 > 0.0)
						{
							NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Precio Produccion en Deudas:" + Conversions.ToString(Math.Round(num73, 1)) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " 8 %: " + Conversions.ToString(Math.Round(num73 * 0.08, 1)) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " 2 %: " + Conversions.ToString(Math.Round(num73 * 0.02, 1)) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElSolar)
					{
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { " POLLOS ENTEROS " }, null, null, null, IgnoreReturn: true);
						int num74 = 0;
						System.Data.DataTable dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad)/cast(4 as float) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '%1/4%' AND TiposProductos.Descripcion like '%PLATO%'  and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")    ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "1/4: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								3
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							num74 = Conversions.ToInteger(Operators.AddObject(num74, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0)));
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad)/cast(8 as float) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '%1/8%' AND TiposProductos.Descripcion like '%PLATO%'  and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")   ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "1/8:" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								3
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							num74 = Conversions.ToInteger(Operators.AddObject(num74, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0)));
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad)/cast(4 as float) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '%1/4%' AND TiposProductos.Descripcion like '%PLATO%'  and DetalleCuenta.Pago=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")    ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "1/4 Credito: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								3
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							num74 = Conversions.ToInteger(Operators.AddObject(num74, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0)));
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad)/cast(8 as float) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '%1/8%' AND TiposProductos.Descripcion like '%PLATO%' and DetalleCuenta.Pago=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")   ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "1/8 Credito: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								3
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							num74 = Conversions.ToInteger(Operators.AddObject(num74, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0)));
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Cena:" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "D Cocido:" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "D Crudo:" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "TOTAL:" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '%1/2 LI%' and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")    ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "1/2 Lt: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '%Agua%' and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")    ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Agua: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '%POPULAR%' and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")  ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Popular: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '2 LIT%' and Productos.Nombre <> '%PEP%' and Productos.Nombre <> '%SEV%'  and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")  ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "2 Lts: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '2 LITROS PEPS%' and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")  ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "2 Lts Pepsi: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '2 LITROS SEVE%' and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")  ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "2 Lts Seven Up: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable39 = BD.ConsultaVer(" select sum(DetalleCuenta.Cantidad) as Cantidad   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where Productos.Nombre like '%TROPI%' and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")  ");
						if (dataTable39.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Tropifrut: " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable39.Rows[0][0]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					if (configuration.gStyleBoliches1 != configuration.styleBolichesId.PizzaGrande && dataTable2.Rows.Count > 0)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza))
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Adicionales" }, null, null, null, IgnoreReturn: true);
						}
						else
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Grupos" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
						int num75 = dataTable2.Rows.Count - 1;
						for (int num76 = 0; num76 <= num75; num76++)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num76]["Cantidad"]), 0) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(" ", dataTable2.Rows[num76]["Agrupador"]) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
					}
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo))
					{
						System.Data.DataTable dataTable40 = BD.ConsultaVer("select round(Stock1,2) as Stock, Nombre as Producto, Presentacion from Productos where TipoProductoID = (select TipoProductoID from TiposProductos where Descripcion = 'INVENTARIO DIARIO') order by Orden");
						if (dataTable40.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "CutPaper", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "Draw2Line", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { Mesero.Substring(0, 7) + " - " + ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Inventario diario al: " + DateAndTime.Now.ToString() }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "STOCK     INSUMO" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
							int num77 = dataTable40.Rows.Count - 1;
							for (int num78 = 0; num78 <= num77; num78++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable40.Rows[num78]["Stock"]), 0) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable40.Rows[num78]["Producto"], " ("), dataTable40.Rows[num78]["Presentacion"]), ")") }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
						}
						DayOfWeek dayOfWeek = DayOfWeek.Sunday;
						if ((DateAndTime.Now.DayOfWeek == dayOfWeek) & (DateAndTime.Now.Hour >= 20))
						{
							dataTable40 = BD.ConsultaVer("select round(Stock1,2) as Stock, Nombre as Producto, Presentacion from Productos where TipoProductoID = (select TipoProductoID from TiposProductos where Descripcion = 'INVENTARIO SEMANAL') order by Orden");
							if (dataTable40.Rows.Count > 0)
							{
								NewLateBinding.LateCall(obj2, null, "CutPaper", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "Draw2Line", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { Mesero.Substring(0, 7) + " - " + ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Inventario semanal al: " + DateAndTime.Now.ToString() }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "STOCK     INSUMO" }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
								int num79 = dataTable40.Rows.Count - 1;
								for (int num80 = 0; num80 <= num79; num80++)
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable40.Rows[num80]["Stock"]), 0) }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2 }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable40.Rows[num80]["Producto"], " ("), dataTable40.Rows[num80]["Presentacion"]), ")") }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								}
							}
						}
						dataTable40 = BD.ConsultaVer("select REPLACE(Visitas.Identificador, '|', ' - ') as Cliente, Tab1.Consumo from Visitas \r\n                            left join (select VisitaID, sum(pago) as Consumo from DetalleCuenta where Borrada = 0 group by VisitaID) as Tab1 on Tab1.VisitaID = Visitas.ID\r\n                            where Identificador is not null and Tab1.Consumo is not null and Visitas.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " order by Tab1.Consumo desc");
						if (dataTable40.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "CutPaper", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "Draw2Line", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { Mesero.Substring(0, 7) + " - " + ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Clientes del Turno" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFechaDMA(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + "  -  " + VariableGeneral.armarSoloLaFechaDMA(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "CLIENTE                                    CONSUMO BS" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
							int num81 = dataTable40.Rows.Count - 1;
							for (int num82 = 0; num82 <= num81; num82++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								object[] array;
								DataRow dataRow;
								bool[] array2;
								NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable40.Rows[num82])["Cliente"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
								if (array2[0])
								{
									dataRow["Cliente"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
								}
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(Conversions.ToDouble(dataTable40.Rows[num82]["Consumo"]), 2) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
						}
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "  REPORTE TIPO ENTREGA GENERAL " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Tipo " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " Cant. " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "  Monto" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						int num83 = dataTable18.Rows.Count - 1;
						for (int num84 = 0; num84 <= num83; num84++)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							object[] array;
							DataRow dataRow;
							bool[] array2;
							NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable18.Rows[num84])["Tipo"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
							if (array2[0])
							{
								dataRow["Tipo"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
							object instance41 = obj2;
							object[] array27 = new object[1];
							object obj49 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
							{
								(dataRow = dataTable18.Rows[num84])["Cantidad"],
								0
							}, null, null, array2 = new bool[2] { true, false });
							if (array2[0])
							{
								dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							array27[0] = obj49;
							NewLateBinding.LateCall(instance41, null, "WriteChars", array27, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable18.Rows[num84]["Monto"]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "  REPORTE TIPO ENTREGA DE PC: " + text }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Tipo " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cant. " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						int num85 = dataTable17.Rows.Count - 1;
						for (int num86 = 0; num86 <= num85; num86++)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							object[] array;
							DataRow dataRow;
							bool[] array2;
							NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable17.Rows[num86])["Tipo"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
							if (array2[0])
							{
								dataRow["Tipo"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
							object instance42 = obj2;
							object[] array28 = new object[1];
							object right4 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
							{
								(dataRow = dataTable17.Rows[num86])["Cantidad"],
								0
							}, null, null, array2 = new bool[2] { true, false });
							if (array2[0])
							{
								dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							array28[0] = Operators.ConcatenateObject(Operators.ConcatenateObject(" - ", right4), " - ");
							NewLateBinding.LateCall(instance42, null, "WriteChars", array28, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable17.Rows[num86]["Monto"]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						double num87 = 0.0;
						double num88 = 0.0;
						double num89 = 0.0;
						if (dataTable3.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
							{
								NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "NormalBiggerFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Reporte de Ventas " }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { DateAndTime.Now }, null, null, null, IgnoreReturn: true);
								object instance43 = obj2;
								object[] obj50 = new object[1] { text };
								object[] array = obj50;
								bool[] obj51 = new bool[1] { true };
								bool[] array2 = obj51;
								NewLateBinding.LateCall(instance43, null, "WriteLine", obj50, null, null, obj51, IgnoreReturn: true);
								if (array2[0])
								{
									text = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
								}
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { (ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + " - " + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
								if (Mesero.Length > 0)
								{
									object instance44 = obj2;
									object[] obj52 = new object[1] { Mesero };
									array = obj52;
									bool[] obj53 = new bool[1] { true };
									array2 = obj53;
									NewLateBinding.LateCall(instance44, null, "WriteLine", obj52, null, null, obj53, IgnoreReturn: true);
									if (array2[0])
									{
										Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
									}
								}
								NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "  REPORTE DE VENTAS CON TARJETA " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "ID" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Orden" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Nro.Factura" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							int num90 = dataTable3.Rows.Count - 1;
							for (int num91 = 0; num91 <= num90; num91++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								object[] array;
								DataRow dataRow;
								bool[] array2;
								NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable3.Rows[num91])["ID"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
								if (array2[0])
								{
									dataRow["ID"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
								}
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["Orden"]), 0) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
								{
									VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["Monto"]), 0),
									0
								}, null, null, null) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["NroFactura"]), 0) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["TipoEnvio"]), "") }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject("Nombre : ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["Nombre"]), "")) }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
								if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["TipoEnvio"]), "").ToString().Contains("UBER"))
								{
									num88 = Conversions.ToDouble(Operators.AddObject(num88, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["Monto"]), 0)));
								}
								else if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["TipoEnvio"]), "").ToString().Contains("PEDIDOS YA"))
								{
									num89 = Conversions.ToDouble(Operators.AddObject(num89, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["Monto"]), 0)));
								}
								else
								{
									num87 = Conversions.ToDouble(Operators.AddObject(num87, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[num91]["Monto"]), 0)));
								}
							}
							NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Total      " + Conversions.ToString(num87) + " Bs" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Total UBER " + Conversions.ToString(num88) + " Bs" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Total PEDIDOS YA " + Conversions.ToString(num89) + " Bs" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.KIKY) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.InesEspana) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.InesEspanaPanaderia) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.FogonGringo))
					{
						double num92 = 0.0;
						if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "REPORTE TIPO ENTREGA POR PC " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
						if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Tipo " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.7 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cant. " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.2 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Caja " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						int num93 = dataTable17.Rows.Count - 1;
						object[] array;
						bool[] array2;
						for (int num94 = 0; num94 <= num93; num94++)
						{
							num92 = Conversions.ToDouble(Operators.AddObject(num92, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable17.Rows[num94]["Monto"]), 0)));
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							DataRow dataRow;
							NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable17.Rows[num94])["Tipo"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
							if (array2[0])
							{
								dataRow["Tipo"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							object instance45 = obj2;
							object[] array29 = new object[1];
							object right5 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
							{
								(dataRow = dataTable17.Rows[num94])["Cantidad"],
								0
							}, null, null, array2 = new bool[2] { true, false });
							if (array2[0])
							{
								dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							array29[0] = Operators.ConcatenateObject(Operators.ConcatenateObject(" - ", right5), " - ");
							NewLateBinding.LateCall(instance45, null, "WriteChars", array29, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable17.Rows[num94]["Monto"]), 0),
								1
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(" ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable17.Rows[num94]["Cuenta"]), "")) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "TOTAL Bs " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { num92 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							num92 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
						}
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.srPollo)
					{
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { " CANT. POLLOS  " }, null, null, null, IgnoreReturn: true);
						System.Data.DataTable dataTable41 = ctlProductos2.devolverProductosUsosSrPollo(dtpDesdeDate, dtpHastaDate, "pollo Leña COCIDO");
						if (dataTable41.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Leña  : " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable41.Rows[0][0]), 0),
								3
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						dataTable41 = ctlProductos2.devolverProductosUsosSrPollo(dtpDesdeDate, dtpHastaDate, "Pollo Broaster COCIDO");
						if (dataTable41.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Broaster : " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable41.Rows[0][0]), 0),
								3
							}, null, null, null) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
					}
					if (configuration.gStyleBoliches1 != configuration.styleBolichesId.TangExpress2)
					{
						System.Data.DataTable dataTable42 = BD.ConsultaVer("TiposGastos.Descripcion as TipoGasto, FechaSalida, Monto  , Cuentas.Nombre as Cuenta , Gastos.Observacion ", "(Gastos inner join TiposGastos on TiposGastos.TipoGastoID = Gastos.TipoGastoID ) inner join Cuentas on Cuentas.CuentaID = Gastos.CuentaID ", "FechaSalida between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " and Maquina like '" + text + "'");
						if (dataTable42.Rows.Count > 0)
						{
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Gastos" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
							{
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
							int num95 = dataTable42.Rows.Count - 1;
							for (int num96 = 0; num96 <= num95; num96++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								string text17 = Conversions.ToString(Operators.ConcatenateObject(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable42.Rows[num96]["Monto"]), 1) }, null, null, null), " "));
								object[] array;
								bool[] array2;
								NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text17 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
								if (array2[0])
								{
									text17 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
								}
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.8 }, null, null, null, IgnoreReturn: true);
								string[] array30 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable42.Rows[num96]["Cuenta"]), ""), " / "), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable42.Rows[num96]["TipoGasto"]), "")), " - "), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable42.Rows[num96]["Observacion"]), ""))).Split(new string[3]
								{
									Environment.NewLine,
									"\n",
									"\r"
								}, StringSplitOptions.None);
								foreach (string obj54 in array30)
								{
									System.Drawing.Font fuente = new System.Drawing.Font("FontA1x1", Conversions.ToSingle(NewLateBinding.LateGet(obj2, null, "_FontSize", new object[0], null, null, null)));
									int anchoEnPixeles = 250;
									List<string> list = DividirFrasePorAnchoFont(obj54.Trim(), anchoEnPixeles, fuente);
									foreach (string item in list)
									{
										NewLateBinding.LateCall(obj2, null, "WriteLine", array = new object[1] { item }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
										if (array2[0])
										{
											string current = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
										}
									}
								}
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaGrande) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mhinos))
						{
							dataTable42 = BD.ConsultaVer("Cuentas.Nombre, (SELECT round(sum(Monto),2) FROM Movimientos where CuentaID=Cuentas.CuentaID group by CuentaID) as MontoTotal", "Cuentas", "Cuentas.Nombre = (select Cuentas.Nombre from Gastos inner join Cuentas on Cuentas.CuentaID = Gastos.CuentaID where Cuentas.Nombre like 'CAJA CHICA SUC%' and Maquina like '" + text + "' Group by Cuentas.nombre)");
							if (dataTable42.Rows.Count > 0)
							{
								int num98 = dataTable42.Rows.Count - 1;
								for (int num99 = 0; num99 <= num98; num99++)
								{
									NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
									NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("Saldo en ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable42.Rows[num99]["Nombre"]), "")), "   Bs "), NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
									{
										VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable42.Rows[num99]["MontoTotal"]), 0),
										2
									}, null, null, null)) }, null, null, null, IgnoreReturn: true);
								}
							}
						}
					}
					if (configuration.gStyleBoliches1 != configuration.styleBolichesId.FogonGringo && ((ctlImpresoras2.DevolverImprimirFacturaFisico().Length > 0) & (dataTable16.Rows.Count > 0)))
					{
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						int num100 = dataTable16.Rows.Count - 1;
						for (int num101 = 0; num101 <= num100; num101++)
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							int num102 = 0;
							num102 = ((!Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[num101][2]), 0), 0, TextCompare: false)) ? Conversions.ToInteger(Operators.AddObject(Operators.SubtractObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[num101][0]), 0), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[num101][1]), 0)), 1)) : 0);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cantidad Facturas: " + Conversions.ToString(num102) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject("Factura Inicial: ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[num101][1]), 0)) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject("Factura Final: ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[num101][0]), 0)) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject("Facturas anuladas: ", VariableGeneral.NZ(dataTable19.Rows.Count, 0)) }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(Operators.ConcatenateObject("Monto Facturado: ", NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[num101]["Monto"]), 0),
								2
							}, null, null, null)), " Bs.") }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							if (configuration.gTipoFacturacion == 2 && Conversions.ToBoolean(Operators.AndObject(Operators.CompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[num101]["FueraLineaID"]), 0), 0, TextCompare: false), Operators.CompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[num101]["EstadoSiat"]), 0), 0, TextCompare: false))))
							{
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "HAY FACTURAS EMITIDAS EN Contingencia, revisar" }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
						}
						System.Data.DataTable dataTable43 = BD.ConsultaVer("select DetalleCuenta.visitaID,  max(Monto_Factura) as Monto_Factura1,sum(DetalleCuenta.Debe) + sum(DetalleCuenta.Pago) as Monto_Cuenta  \t    from\tDetalleCuenta inner join (select sum(Facturas.Monto+Facturas.Descuento) as Monto_Factura, VisitaID, max(FechaEmision) as fechaFactura  from  Facturas where Facturas.Anulada =" + VariableGeneral.armarBolean(0) + " group by VisitaID) as tab1 on (tab1.VisitaID = DetalleCuenta.visitaID and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + ") \t  \twhere (fechaFactura  between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " ) \t  \tgroup by DetalleCuenta.visitaID  \thaving  max(Monto_Factura) <> (sum(DetalleCuenta.Debe) + sum(DetalleCuenta.Pago)) \t  \torder by DetalleCuenta.visitaID ");
						if (dataTable43.Rows.Count > 0)
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "MONTOS DE FACT. NO COINCIDEN VENTAS" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "CuentaID      Monto Fact.      Monto Cuenta" }, null, null, null, IgnoreReturn: true);
							int num103 = dataTable43.Rows.Count - 1;
							for (int num104 = 0; num104 <= num103; num104++)
							{
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(Operators.ConcatenateObject("", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable43.Rows[num104][0]), 0)), " - ") }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
								{
									VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable43.Rows[num104][1]), 0),
									2
								}, null, null, null), "Bs ") }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4.5 }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
								{
									VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable43.Rows[num104][2]), 0),
									2
								}, null, null, null), "Bs ") }, null, null, null, IgnoreReturn: true);
								NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							}
						}
					}
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bistecca)
				{
					double num105 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select sum(pago) from DetalleCuenta where ProductoID = 1 and Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)).Rows[0][0]), 0));
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					object instance46 = obj2;
					object[] obj55 = new object[1] { num105 };
					object[] array = obj55;
					bool[] obj56 = new bool[1] { true };
					bool[] array2 = obj56;
					NewLateBinding.LateCall(instance46, null, "WriteChars", obj55, null, null, obj56, IgnoreReturn: true);
					if (array2[0])
					{
						num105 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
					}
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "BS.  EN SERVICIO" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				double num106 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select  sum(DetalleCuenta.Debe) FROM DetalleCuenta left join visitas on DetalleCuenta.VisitaID =Visitas.id  where visitas.ParaLlevarID >0 ").Rows[0][0]), 0));
				if (dataTable10.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					object instance47 = obj2;
					object[] obj57 = new object[1] { num106 };
					object[] array = obj57;
					bool[] obj58 = new bool[1] { true };
					bool[] array2 = obj58;
					NewLateBinding.LateCall(instance47, null, "WriteChars", obj57, null, null, obj58, IgnoreReturn: true);
					if (array2[0])
					{
						num106 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
					}
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "BS.  EN PEDIDOS PARA LLEVAR SIN PAGAR" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (dataTable19.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Facturas Anuladas" }, null, null, null, IgnoreReturn: true);
					int num107 = dataTable19.Rows.Count - 1;
					for (int num108 = 0; num108 <= num107; num108++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(Operators.ConcatenateObject("Nro.  ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable19.Rows[num108][0]), 0)), " - ") }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable19.Rows[num108][1]), 0),
							2
						}, null, null, null), " Bs") }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Dubai)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					System.Data.DataTable dataTable44 = ((configuration.gMODO_ACCESS != 1) ? BD.ConsultaVer("max(Meseros.Nombre) as Personal, max(Mesas.Nombre) as Mesas, sum(DetalleCuenta.Pago) as Pagaron, sum(DetalleCuenta.Debe) as Deben, sum(DetalleCuenta.Costo) as Costo,   case when  (Meseros.Porcentaje>0) then (round(sum(Meseros.Porcentaje/100* Productos.Precio* DetalleCuenta.Cantidad ),1)) else (round(sum( Productos.Comision * DetalleCuenta.Cantidad ),1 )) end as Comision", "(((DetalleCuenta  INNER JOIN Visitas ON DetalleCuenta.VisitaID = Visitas.ID) left join Mesas on Mesas.ID=Visitas.MesaID ) left join  Meseros on Meseros.MeseroID =DetalleCuenta.MeseroID) left join Productos on Productos.Id=DetalleCuenta.ProductoID", "DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + configuration.CaracterFecha + " and " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) + configuration.CaracterFecha + ")", "Personal, Mesas", "Meseros.MeseroID, DetalleCuenta.VisitaID,Meseros.Porcentaje ") : BD.ConsultaVer("max(Meseros.Nombre) as Personal, max(Mesas.Nombre) as Mesas, sum(DetalleCuenta.Pago) as Pagaron, sum(DetalleCuenta.Debe) as Deben, sum(DetalleCuenta.Costo) as Costo,iif ( (Meseros.Porcentaje>0),(round(sum(Meseros.Porcentaje/100* Productos.Precio* DetalleCuenta.Cantidad ),1)),(round(sum( Productos.Comision * DetalleCuenta.Cantidad ),1 ))) as Comision", "(((DetalleCuenta  INNER JOIN Visitas ON DetalleCuenta.VisitaID = Visitas.ID) left join Mesas on Mesas.ID=Visitas.MesaID ) left join  Meseros on Meseros.MeseroID =DetalleCuenta.MeseroID) left join Productos on Productos.Id=DetalleCuenta.ProductoID", "DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + configuration.CaracterFecha + " and " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) + configuration.CaracterFecha + ")", "Personal, Mesas", "Meseros.MeseroID, DetalleCuenta.VisitaID,Meseros.Porcentaje "));
					string text18 = "11";
					int num109 = dataTable44.Rows.Count - 1;
					for (int num110 = 0; num110 <= num109; num110++)
					{
						if (Operators.ConditionalCompareObjectNotEqual(text18, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable44.Rows[num110]["Personal"]), ""), TextCompare: false))
						{
							text18 = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable44.Rows[num110]["Personal"]), ""));
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							object instance48 = obj2;
							object[] obj59 = new object[1] { text18 };
							object[] array = obj59;
							bool[] obj60 = new bool[1] { true };
							bool[] array2 = obj60;
							NewLateBinding.LateCall(instance48, null, "WriteChars", obj59, null, null, obj60, IgnoreReturn: true);
							if (array2[0])
							{
								text18 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
							}
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable44.Rows[num110]["Pagaron"]), 0),
							2
						}, null, null, null), " Bs") }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable44.Rows[num110]["Mesas"]), 0) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				if (conCantidadPersonas && num2 != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cant. Personas Atendidas : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num2, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ticket Promedio por persona : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(value, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Boulangerie)
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cantidad al Contado : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
					object instance49 = obj2;
					object[] obj61 = new object[1] { num7 };
					object[] array = obj61;
					bool[] obj62 = new bool[1] { true };
					bool[] array2 = obj62;
					NewLateBinding.LateCall(instance49, null, "WriteChars", obj61, null, null, obj62, IgnoreReturn: true);
					if (array2[0])
					{
						num7 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cantidad Credito : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { num8 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
					if (array2[0])
					{
						num8 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin)
				{
					System.Data.DataTable dataTable45 = BD.ConsultaVer("substring(CONVERT(VARCHAR, fecha, 108),0,6) as Hora, sum(MontoBs) as Monto", "pagos", "MaquinaPago Like '" + text + "' and CuentaID =" + Conversions.ToString(3) + " and (Fecha between " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + configuration.CaracterFecha + " and " + configuration.CaracterFecha + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) + configuration.CaracterFecha + ")", "Fecha", "Fecha");
					if (dataTable45.Rows.Count > 0)
					{
						NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "DETALLE COBROS CON TARJETAS (Bs)" }, null, null, null, IgnoreReturn: true);
						int num111 = dataTable45.Rows.Count - 1;
						for (int num112 = 0; num112 <= num111; num112++)
						{
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { dataTable45.Rows[num112]["Hora"].ToString() + "   - " }, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
							object instance50 = obj2;
							object[] array31 = new object[1];
							object[] array;
							DataRow dataRow;
							bool[] array2;
							object obj63 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
							{
								(dataRow = dataTable45.Rows[num112])["Monto"],
								1
							}, null, null, array2 = new bool[2] { true, false });
							if (array2[0])
							{
								dataRow["Monto"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
							}
							array31[0] = obj63;
							NewLateBinding.LateCall(instance50, null, "WriteChars", array31, null, null, null, IgnoreReturn: true);
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				if (_Ciego)
				{
					if (dataTable14.Rows.Count > 0)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "ARQUEO DE CAJA BS" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
						NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Billetes" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cant.  " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "*Monedas " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cant." }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "200    = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						object instance51 = obj2;
						DataRow dataRow;
						object[] obj64 = new object[1] { (dataRow = dataTable14.Rows[0])["B200"] };
						object[] array = obj64;
						bool[] obj65 = new bool[1] { true };
						bool[] array2 = obj65;
						NewLateBinding.LateCall(instance51, null, "WriteChars", obj64, null, null, obj65, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["B200"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "  *5 bs      = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["B5"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["B5"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "100    = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["B100"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["B100"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "  *2 bs     = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["B2"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["B2"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " 50     = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["B50"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["B50"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "  *1 bs      = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["B1"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["B1"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " 20     = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["B20"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["B20"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "*50 ctvs     = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["C50"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["C50"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " 10     = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["B10"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["B10"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "*20ctvs     = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["C20"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["C20"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "              *10 ctvs     = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["C10"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["C10"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "ARQUEO DE CAJA $US" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
						NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Billetes" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cant.  " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "*Billetes" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cant.  " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "100    = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["D100"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["D100"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " *10      = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["D10"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["D10"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " 50    = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["D50"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["D50"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "  *5      = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["D5"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["D5"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " 20     = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["D20"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["D20"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "  *1      = " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable14.Rows[0])["D1"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["D1"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "CIERRE CAJERO" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Cajero (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(value2, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Cajero Tarjeta : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(value4, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Cajero ($Us) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(value3, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Otras Cajas (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(value5, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (((configuration.gStyleBoliches1 == configuration.styleBolichesId.SirFrancis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Naoki)) && dataTable12.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Productos Borrados" }, null, null, null, IgnoreReturn: true);
					int num113 = dataTable12.Rows.Count - 1;
					for (int num114 = 0; num114 <= num113; num114++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						object instance52 = obj2;
						object[] array32 = new object[1];
						DataRow dataRow;
						object[] array;
						bool[] array2;
						object left25 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable12.Rows[num114])["Cantidad"],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array32[0] = Operators.ConcatenateObject(left25, "    ");
						NewLateBinding.LateCall(instance52, null, "WriteChars", array32, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.armarSoloLaFechaDMA(Conversions.ToDate(dataTable12.Rows[num114]["FechaPedido"])) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.8 }, null, null, null, IgnoreReturn: true);
						string text19 = Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable12.Rows[num114]["tipoProd"], " - "), dataTable12.Rows[num114]["Producto"]));
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text19 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							text19 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				System.Data.DataTable dataTable46 = BD.ConsultaVer("Cuentas.Nombre, Sum(Montobs) as Monto", "Propinas inner join Cuentas on Cuentas.cuentaID=Propinas.CuentaId", ("MaquinaPropina like '" + text + "' and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "", "Cuentas.Nombre", "Cuentas.Nombre");
				if (dataTable46.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Propinas" }, null, null, null, IgnoreReturn: true);
					int num115 = dataTable46.Rows.Count - 1;
					for (int num116 = 0; num116 <= num115; num116++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						object instance53 = obj2;
						object[] array33 = new object[1];
						object[] array;
						DataRow dataRow;
						bool[] array2;
						object left26 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable46.Rows[num116])["Monto"],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow["Monto"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array33[0] = Operators.ConcatenateObject(left26, "    ");
						NewLateBinding.LateCall(instance53, null, "WriteChars", array33, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable46.Rows[num116])["Nombre"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["Nombre"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SirFrancis)) && gastadoBs > 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Gastos" }, null, null, null, IgnoreReturn: true);
					System.Data.DataTable dataTable47 = BD.ConsultaVer("TiposGastos.Descripcion as TipoGasto, FechaSalida, Monto  , Cuentas.Nombre as Cuenta , Gastos.Observacion ", " (Gastos inner join TiposGastos on TiposGastos.TipoGastoID = Gastos.TipoGastoID ) inner join Cuentas on Cuentas.CuentaID = Gastos.CuentaID ", " FechaSalida between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate));
					int num117 = dataTable47.Rows.Count - 1;
					for (int num118 = 0; num118 <= num117; num118++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						string text20 = Conversions.ToString(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable47.Rows[num118]["Monto"]), 0) }, null, null, null));
						object[] array;
						bool[] array2;
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { text20 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							text20 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.7 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable47.Rows[num118]["Cuenta"]), ""), " - "), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable47.Rows[num118]["TipoGasto"]), "")), " - "), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable47.Rows[num118]["Observacion"]), "")) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				_ = (configuration.gStyleBoliches1 == configuration.styleBolichesId.Alquimia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CafeAme);
				if (_Ciego && dataTable14.Rows.Count > 0 && configuration.gStyleBoliches1 != configuration.styleBolichesId.ElCortijo)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
					string[] array34 = ObservacionTurno.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string obj66 in array34)
					{
						System.Drawing.Font fuente2 = new System.Drawing.Font("FontA1x1", Conversions.ToSingle(NewLateBinding.LateGet(obj2, null, "_FontSize", new object[0], null, null, null)));
						int anchoEnPixeles2 = 250;
						List<string> list2 = DividirFrasePorAnchoFont(obj66.Trim(), anchoEnPixeles2, fuente2);
						foreach (string item2 in list2)
						{
							object[] array;
							bool[] array2;
							NewLateBinding.LateCall(obj2, null, "WriteLine", array = new object[1] { item2 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
							if (array2[0])
							{
								string current2 = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
							}
						}
					}
					double num120 = Conversions.ToDouble(Operators.SubtractObject(Operators.AddObject(Operators.AddObject(Math.Round(num, 1), NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable23.Rows[0][0]), 0),
						1
					}, null, null, null)), NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable24.Rows[0][0]), 0), clsTi.returnTipoCambio()),
						1
					}, null, null, null)), Math.Round(value5, 1)));
					if (num120 != 0.0)
					{
						if (num120 > 0.0)
						{
							NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { " ** FALTAN OTRAS CAJAS " + Conversions.ToString(Math.Abs(num120)) + " BS\r\n" }, null, null, null, IgnoreReturn: true);
						}
						else
						{
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { " ** SOBRAN OTRAS CAJAS " + Conversions.ToString(Math.Abs(num120)) + " BS\r\n" }, null, null, null, IgnoreReturn: true);
						}
					}
				}
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin)
			{
				System.Data.DataTable dataTable48 = BD.ConsultaVer((" select * from movimientos where cuentaid=10    and  Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
				if (dataTable48.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { " MOVIMIENTOS " }, null, null, null, IgnoreReturn: true);
					int num121 = dataTable48.Rows.Count - 1;
					for (int num122 = 0; num122 <= num121; num122++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Caja Fuerte    " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.5 }, null, null, null, IgnoreReturn: true);
						object instance54 = obj2;
						object[] array35 = new object[1];
						object[] array;
						DataRow dataRow;
						bool[] array2;
						object left27 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable48.Rows[num122])["Monto"],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow["Monto"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array35[0] = Operators.ConcatenateObject(left27, "    ");
						NewLateBinding.LateCall(instance54, null, "WriteChars", array35, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 3.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable48.Rows[num122])["Descripcion"] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow["Descripcion"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Iturri)
			{
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "." }, null, null, null, IgnoreReturn: true);
			}
			if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Batos))
			{
				NewLateBinding.LateCall(obj2, null, "CutPaper", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "Draw2Line", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "NormalBiggerFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Reporte de Ventas " }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { DateAndTime.Now }, null, null, null, IgnoreReturn: true);
				object instance55 = obj2;
				object[] obj67 = new object[1] { text };
				object[] array = obj67;
				bool[] obj68 = new bool[1] { true };
				bool[] array2 = obj68;
				NewLateBinding.LateCall(instance55, null, "WriteLine", obj67, null, null, obj68, IgnoreReturn: true);
				if (array2[0])
				{
					text = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { (ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + " - " + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
				if (Mesero.Length > 0)
				{
					object instance56 = obj2;
					object[] obj69 = new object[1] { Mesero };
					array = obj69;
					bool[] obj70 = new bool[1] { true };
					array2 = obj70;
					NewLateBinding.LateCall(instance56, null, "WriteLine", obj69, null, null, obj70, IgnoreReturn: true);
					if (array2[0])
					{
						Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
					}
				}
				NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "AlignLeft", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Sucursal " + ctlConfiguraciones2.devolverSucursal() }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				int num123 = 0;
				if (dataTable16.Rows.Count > 0)
				{
					num123 = ((!Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[0][2]), 0), 0, TextCompare: false)) ? Conversions.ToInteger(Operators.AddObject(Operators.SubtractObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[0][0]), 0), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[0][1]), 0)), 1)) : 0);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cantidad Facturas:    " + Conversions.ToString(num123) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject("Factura Inicial:    ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[0][1]), 0)) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject("Factura Final:    ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[0][0]), 0)) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					System.Data.DataTable dataTable49 = BD.ConsultaVer("select Cuentas.Nombre,sum( Pagos.MontoBs) as Monto \r\n                            from Pagos left join Cuentas on Pagos.CuentaID =Cuentas.CuentaID \r\n                            left join DetalleCuenta on DetalleCuenta.ID =Pagos.DetalleCuentaID \r\n                            left join Facturas on Facturas.VisitaID =DetalleCuenta.VisitaID \r\n                             where  Facturas.pc like '" + text + "' and  FechaEmision \r\n                             between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " and anulada=" + VariableGeneral.armarBolean(0) + "  \r\n                             group by Cuentas.Nombre");
					System.Data.DataTable dataTable50 = BD.ConsultaVer(" Select Productos.Nombre ,sum(DetalleCuenta.Cantidad) as Cantidad, sum(tab1.Bs ) as Bs\r\n                            from DetalleCuenta \r\n                            left join Facturas on Facturas.VisitaID =DetalleCuenta.VisitaID \r\n                            left join productos on Productos.ID =DetalleCuenta.ProductoID   \r\n                            left join \r\n                            (Select DetalleCuenta.ID, sum(pagos.MontoBs) as Bs\r\n                            from Pagos \r\n                            left join DetalleCuenta on DetalleCuenta.ID =Pagos.DetalleCuentaID \r\n                            left join Facturas on Facturas.VisitaID =DetalleCuenta.VisitaID \r\n                            where Facturas.pc like '" + text + "' and  FechaEmision \r\n                            between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " and anulada=" + VariableGeneral.armarBolean(0) + " \r\n                            group by DetalleCuenta.ID) as tab1 on DetalleCuenta.id=tab1.ID \r\n                             where Facturas.pc like '" + text + "' and  FechaEmision \r\n                             between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " and anulada=" + VariableGeneral.armarBolean(0) + " \r\n                             group by Productos.Nombre");
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { Operators.ConcatenateObject(Operators.ConcatenateObject("Monto Facturado :    ", NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable16.Rows[0]["Monto"]), 0),
						2
					}, null, null, null)), " Bs.") }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "COBROS" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					double num124 = 0.0;
					int num125 = dataTable49.Rows.Count - 1;
					for (int num126 = 0; num126 <= num125; num126++)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable49.Rows[num126]["Nombre"]), "") }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
						object instance57 = obj2;
						object[] array36 = new object[1];
						DataRow dataRow;
						object obj71 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable49.Rows[num126])["Monto"],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow["Monto"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array36[0] = obj71;
						NewLateBinding.LateCall(instance57, null, "WriteChars", array36, null, null, null, IgnoreReturn: true);
						object left28 = num124;
						object right6 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable49.Rows[num126])["Monto"],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow["Monto"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						num124 = Conversions.ToDouble(Operators.AddObject(left28, right6));
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Total Cobros" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { num124 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
					if (array2[0])
					{
						num124 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
					}
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Items" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Medida" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.8 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Bs" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					double num127 = 0.0;
					double num128 = 0.0;
					int num129 = dataTable50.Rows.Count - 1;
					for (int num130 = 0; num130 <= num129; num130++)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						if (Conversions.ToBoolean(VariableGeneral.NZ(dataTable50.Rows[num130]["Nombre"].ToString().Length > 30, "")))
						{
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(dataTable50.Rows[num130]["Nombre"].ToString().Substring(1, 30), "") }, null, null, null, IgnoreReturn: true);
						}
						else
						{
							NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable50.Rows[num130]["Nombre"]), "") }, null, null, null, IgnoreReturn: true);
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4.7 }, null, null, null, IgnoreReturn: true);
						object instance58 = obj2;
						object[] array37 = new object[1];
						DataRow dataRow;
						object obj72 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable50.Rows[num130])["Cantidad"],
							0
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array37[0] = obj72;
						NewLateBinding.LateCall(instance58, null, "WriteChars", array37, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.8 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable50.Rows[num130]["Bs"]), 0),
							1
						}, null, null, null) }, null, null, null, IgnoreReturn: true);
						object left29 = num127;
						object right7 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable50.Rows[num130])["Cantidad"],
							0
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						num127 = Conversions.ToDouble(Operators.AddObject(left29, right7));
						num128 = Conversions.ToDouble(Operators.AddObject(num128, NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable50.Rows[num130]["Bs"]), 0),
							1
						}, null, null, null)));
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Total Items Vendidos" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 4.7 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { num127 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
					if (array2[0])
					{
						num127 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
					}
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.8 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { num128 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
					if (array2[0])
					{
						num128 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
					}
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
				}
			}
			ToEmail = Conversions.ToString(NewLateBinding.LateGet(p, null, "Texto", new object[0], null, null, null));
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
			{
				p = null;
				string directoryPath = MyProject.Application.Info.DirectoryPath;
				string printerName = ctlImpresoras2.DevolverImprimirCierreKiky();
				bool encontro = true;
				PrinterClass printerClass = new PrinterClass(directoryPath, printerName, ref encontro, "Restotech Cierre");
				PrinterClass printerClass2 = printerClass;
				printerClass2.AlignCenter();
				printerClass2.NormalBiggerFont();
				printerClass2.Bold = true;
				if (desdeReporteVentas)
				{
					printerClass2.BigSmallerFont();
					printerClass2.WriteLine("REPORTE VENTAS TOTALES");
				}
				else
				{
					printerClass2.WriteLine("Reporte de Ventas");
				}
				printerClass2.NormalFont();
				printerClass2.WriteLine(Conversions.ToString(DateAndTime.Now));
				printerClass2.WriteLine(text);
				printerClass2.WriteLine((ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion());
				printerClass2.smallFont();
				printerClass2.WriteLine(VariableGeneral.armarSoloLaFecha(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + " - " + VariableGeneral.armarSoloLaFecha(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate));
				if (Mesero.Length > 0)
				{
					printerClass2.WriteLine(Mesero);
				}
				printerClass2.DrawLine();
				if (dataTable2.Rows.Count > 0)
				{
					printerClass.WriteLine("");
					printerClass.WriteLine("Grupos");
					int num131 = dataTable2.Rows.Count - 1;
					for (int num132 = 0; num132 <= num131; num132++)
					{
						printerClass.GotoSixth(1.0);
						printerClass.WriteChars(Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[num132]["Cantidad"]), 0)));
						printerClass.GotoSixth(3.0);
						printerClass.WriteChars(Conversions.ToString(Operators.ConcatenateObject(" ", dataTable2.Rows[num132]["Agrupador"])));
						printerClass.WriteLine("");
					}
				}
				printerClass.WriteLine("");
				printerClass2.WriteLine("Ventas Totales");
				int num133 = dataTable6.Rows.Count - 1;
				for (int num134 = 0; num134 <= num133; num134++)
				{
					if (!Conversions.ToBoolean(Operators.AndObject(configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza, Operators.CompareObjectEqual(dataTable6.Rows[num134]["Producto"], "BORDE CATUPIRY", TextCompare: false))))
					{
						printerClass2.GotoSixth(1.0);
						PrinterClass printerClass3 = printerClass2;
						Type typeFromHandle7 = typeof(Math);
						DataRow dataRow;
						object[] obj73 = new object[2]
						{
							(dataRow = dataTable6.Rows[num134])["Cantidad"],
							1
						};
						object[] array = obj73;
						bool[] obj74 = new bool[2] { true, false };
						bool[] array2 = obj74;
						object left30 = NewLateBinding.LateGet(null, typeFromHandle7, "Round", obj73, null, null, obj74);
						if (array2[0])
						{
							dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						printerClass3.WriteChars(Conversions.ToString(Operators.ConcatenateObject(left30, "   ")));
						printerClass2.GotoSixth(1.5);
						object left31 = num33;
						object right8 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable6.Rows[num134])["Cantidad"],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow["Cantidad"] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						num33 = Conversions.ToInteger(Operators.AddObject(left31, right8));
						string text21 = "";
						text21 = ((!((configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza))) ? Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable6.Rows[num134]["tipoProd"], " - "), dataTable6.Rows[num134]["Producto"])) : Conversions.ToString(dataTable6.Rows[num134]["Producto"]));
						printerClass2.WriteChars(text21);
						printerClass2.WriteLine("");
					}
				}
				printerClass2.AlignLeft();
				printerClass2.WriteLine("__________");
				printerClass2.WriteChars(Conversions.ToString(num33) + "    ITEMS VENDIDOS ");
				printerClass2.WriteLine("");
				printerClass2.WriteLine("");
				if (_Ciego)
				{
					printerClass2.AlignCenter();
					printerClass2.WriteLine("");
					printerClass2.Bold = true;
					printerClass2.GotoSixth(3.0);
					printerClass2.WriteChars("CIERRE CAJERO");
					printerClass2.WriteLine("");
					printerClass2.WriteLine("");
					printerClass2.Bold = false;
					printerClass2.NormalFont();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars("Monto Cajero (Bs) : ");
					printerClass2.GotoSixth(5.0);
					printerClass2.WriteChars(Conversions.ToString(Math.Round(value2, 1)));
					printerClass2.WriteLine("");
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars("Monto Cajero Tarjeta : ");
					printerClass2.GotoSixth(5.0);
					printerClass2.WriteChars(Conversions.ToString(Math.Round(value4, 1)));
					printerClass2.WriteLine("");
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars("Monto Cajero ($Us) : ");
					printerClass2.GotoSixth(5.0);
					printerClass2.WriteChars(Conversions.ToString(Math.Round(value3, 1)));
					printerClass2.WriteLine("");
				}
				printerClass2.WriteLine("");
				if (dataTable26.Rows.Count > 0)
				{
					printerClass2.NormalFont();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars("Desglose Cobros de otras cuentas");
					printerClass2.WriteLine("");
					printerClass2.NormalFont();
					int num135 = dataTable26.Rows.Count - 1;
					for (int num136 = 0; num136 <= num135; num136++)
					{
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(Conversions.ToString(Operators.ConcatenateObject(dataTable26.Rows[num136][0], " :  ")));
						printerClass2.GotoSixth(5.0);
						PrinterClass printerClass4 = printerClass2;
						object[] array;
						DataRow dataRow;
						bool[] array2;
						object obj75 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable26.Rows[num136])[1],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						printerClass4.WriteChars(Conversions.ToString(obj75));
						printerClass2.WriteLine("");
					}
					printerClass2.WriteLine("");
				}
				if (dataTable27.Rows.Count > 0)
				{
					printerClass2.NormalFont();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars("Para cobrarle a Pedidos Ya");
					printerClass2.WriteLine("");
					printerClass2.NormalFont();
					int num137 = dataTable27.Rows.Count - 1;
					for (int num138 = 0; num138 <= num137; num138++)
					{
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(Conversions.ToString(Operators.ConcatenateObject(dataTable27.Rows[num138][0], " (Bs) :  ")));
						printerClass2.GotoSixth(5.0);
						PrinterClass printerClass5 = printerClass2;
						object[] array;
						DataRow dataRow;
						bool[] array2;
						object obj76 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable27.Rows[num138])[1],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						printerClass5.WriteChars(Conversions.ToString(obj76));
						printerClass2.WriteLine("");
					}
					printerClass2.WriteLine("");
				}
				printerClass2.WriteLine("");
				printerClass2.smallFont();
				printerClass2.AlignLeft();
				printerClass2.GotoSixth(1.0);
				string[] array38 = ObservacionTurno.Split(new string[3]
				{
					Environment.NewLine,
					"\n",
					"\r"
				}, StringSplitOptions.None);
				foreach (string obj77 in array38)
				{
					System.Drawing.Font fuente3 = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
					int anchoEnPixeles3 = 250;
					List<string> list3 = DividirFrasePorAnchoFont(obj77.Trim(), anchoEnPixeles3, fuente3);
					foreach (string item3 in list3)
					{
						printerClass2.WriteLine(item3);
					}
				}
				printerClass2 = null;
				printerClass.CutPaper();
				printerClass.EndDoc();
			}
			else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspana) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspanaPanaderia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Donal) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.IrishPub) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.VacafriaLaPaz) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaGrande) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.MiAlegria) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SantaMaria))
			{
				p = null;
			}
			else
			{
				NewLateBinding.LateCall(obj2, null, "CutPaper", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "EndDoc", new object[0], null, null, null, IgnoreReturn: true);
			}
			obj2 = null;
		}
	}

	public static void SoloEfectivoCierre(object p, DateTime dtpDesdeDate, DateTime dtpHastaDate, string Mesero, string ObservacionTurno, double montoIniBs, double montoIniDolares, double cobradoBs, double ventaBS, double compraDolares, double cobradoTarjeta, double gastadoBs, double gastadoDolares, double otrosBs, double otrosDolares, ref string ToEmail, bool ciego, bool conCantidadPersonas, double PropinaTarjeta, bool desdeReporteVentas)
	{
		new ctlImpresoras();
		ctlProductos ctlProductos2 = new ctlProductos();
		ctlDetalleCuenta obj = new ctlDetalleCuenta();
		System.Data.DataTable dataTable = ctlProductos2.devolverCategoriaProduccionPorDescripcion1();
		string text = MyProject.Computer.Name;
		clsPagos clsPagos2 = new clsPagos
		{
			_MaquinaPago = text
		};
		string text2 = " DetalleCuenta.PC  like '" + text + "' ";
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BurgerMunch)
		{
			text2 = " 1=1 ";
		}
		if (desdeReporteVentas | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Batos))
		{
			text2 = " 1=1 ";
			clsPagos2._MaquinaPago = "%%";
			text = "%%";
		}
		double num = Math.Round(clsPagos2.DevolverEntreFechasPorMaquinaPorOtrasCuenta(dtpDesdeDate, dtpHastaDate), 1);
		obj.DevolverReporteGrupal(dtpDesdeDate, dtpHastaDate);
		obj.DevolverFacturasConTarjeta(dtpDesdeDate, dtpHastaDate, clsPagos2._MaquinaPago);
		if (conCantidadPersonas)
		{
			try
			{
				System.Data.DataTable dataTable2 = ((configuration.gMODO_ACCESS != 1) ? BD.ConsultaVer("select sum(cast(observacion as float)) from Visitas where ISNUMERIC(observacion)=1 and mesaID>1 And Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate)) : BD.ConsultaVer("select sum(CLng(observacion)) from Visitas where ISNUMERIC(observacion)=1 and mesaID>1 And Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate)));
				if (dataTable2.Rows.Count > 0)
				{
					Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0][0]), 0));
				}
				dataTable2 = ((configuration.gMODO_ACCESS != 1) ? BD.ConsultaVer("select sum(monto) /sum(cast(observacion as float)) from Visitas inner join \r\n                                                    (select visitaId, sum(pago+debe) as monto from DetalleCuenta where (DetalleCuenta.Borrada =" + VariableGeneral.armarBolean(0) + ") group by visitaId) as tab1 on tab1.VisitaID = Visitas.ID            \r\n                                          where  ISNUMERIC(observacion)=1 And mesaID>1 And Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate)) : BD.ConsultaVer("select sum(monto) /sum(CLng(observacion)) from Visitas inner join \r\n                                                    (select visitaId, sum(pago+debe) as monto from DetalleCuenta where (DetalleCuenta.Borrada =" + VariableGeneral.armarBolean(0) + ") group by visitaId) as tab1 on tab1.VisitaID = Visitas.ID            \r\n                                          where  ISNUMERIC(observacion)=1 And mesaID>1 And Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate)));
				Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0][0]), 0));
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				ProjectData.ClearProjectError();
			}
		}
		if (dataTable.Rows.Count > 0)
		{
			BD.ConsultaVer(" SELECT CategoriasProduccion.Nombre , sum(DetallesProduccion.Producida) as Producida, sum(DetallesProduccion.Eliminada) as Eliminada, sum(DetallesProduccion.Reciclada) as Reciclada   FROM ((DetallesProduccion inner join Productos on DetallesProduccion.ProductoID=Productos.ID) inner join Produccion on Produccion.ProduccionID= DetallesProduccion.ProduccionID   ) inner join CategoriasProduccion on CategoriasProduccion.CategoriaProduccionID = Productos.CategoriaProduccionID    WHERE  Produccion.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " And " + VariableGeneral.ArmarFecha(dtpHastaDate) + "  group by CategoriasProduccion.Nombre ");
		}
		new System.Data.DataTable();
		System.Data.DataTable dataTable3;
		System.Data.DataTable dataTable4;
		System.Data.DataTable dataTable5;
		if (configuration.gComidaRapida | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Ottimo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin))
		{
			if (configuration.gComidaRapida)
			{
				BD.ConsultaVer("select tab1.tipoProd,tab1.Producto,sum(tab1.Cantidad) as Cantidad, sum(tab1.Pagado) as Pagado, sum(tab1.Costo) as Costo from\r\n                        ( select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  min( DetalleCuenta.Cantidad) as Cantidad,    sum( Pagos.MontoBs) as Pagado,  sum(DetalleCuenta.Costo) as Costo   from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)  inner join Pagos on Pagos.DetalleCuentaID =DetalleCuenta.id  where  (" + text2 + " and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by DetalleCuenta.ID , TiposProductos.Codigo ,Productos.Nombre ) as tab1\r\n                     group by tab1.tipoProd,tab1.Producto  order by tipoProd ,Producto");
			}
			else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin)
			{
				BD.ConsultaVer("select tab1.tipoProd,tab1.Producto,sum(tab1.Cantidad) as Cantidad, sum(tab1.Pagado) as Pagado, sum(tab1.Costo) as Costo from\r\n                        ( select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  min( DetalleCuenta.Cantidad) as Cantidad,    sum( Pagos.MontoBs) as Pagado,  sum( DetalleCuenta.Costo) as Costo   from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)  inner join Pagos on Pagos.DetalleCuentaID =DetalleCuenta.id  where  (Pagos.MaquinaPago   like '" + text + "' and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by DetalleCuenta.ID , TiposProductos.Codigo ,Productos.Nombre ) as tab1\r\n                     group by tab1.tipoProd,tab1.Producto   order by tipoProd ,Producto");
			}
			else
			{
				BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,    sum( DetalleCuenta.Pago) as Pagado,  sum( DetalleCuenta.Costo) as Costo   from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)   where " + text2 + " and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Codigo ,Productos.Nombre  order by TiposProductos.Codigo ,Productos.Nombre ");
			}
			dataTable3 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum(DetalleCuenta.Costo) as Costo , clientes.Nombre as Nombre  from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)  left join clientes on visitas.ClienteID =Clientes.ID   where  (" + text2 + " and  DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by clientes.Nombre, TiposProductos.Codigo,Productos.Nombre  order by clientes.Nombre, TiposProductos.Codigo ,Productos.Nombre   ");
			dataTable4 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo , clientes.Nombre as Nombre  from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID)  left join clientes on visitas.ClienteID =Clientes.ID   where " + text2 + " and visitas.ParaLlevarID  is not null and DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by clientes.Nombre, TiposProductos.Codigo,Productos.Nombre  order by clientes.Nombre, TiposProductos.Codigo ,Productos.Nombre   ");
			BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,  sum( DetalleCuenta.Pago) as Pagado  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where " + text2 + " and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Descripcion order by TiposProductos.Descripcion     ");
			BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,    sum(DetalleCuenta.Debe) as Debe   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where " + text2 + " and DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Descripcion order by TiposProductos.Descripcion   ");
			BD.ConsultaVer(" select Familias.Descripcion as tipoProd,  sum( DetalleCuenta.Pago) as Pagado  from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID) left join Familias on Familias.FamiliaId=TiposProductos.FamiliaID  where " + text2 + " and DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by Familias.Descripcion order by Familias.Descripcion     ");
			BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  sum(Borrados.Cantidad) as Cantidad, DetalleCuenta.Hora  as FechaPedido  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID)       inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID ) inner join Borrados on Borrados.DetalleCuentaID = DetalleCuenta.ID   where (borrados.PC like '" + text + "' and  (Borrados.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TiposProductos.Codigo ,Productos.Nombre, DetalleCuenta.hora  order by TiposProductos.Codigo ,Productos.Nombre ");
			dataTable5 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo, (DetalleCuenta.PrecioUnit ) as precio   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where  " + text2 + " and  DetalleCuenta.Pago=0 and DetalleCuenta.Debe=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Codigo,Productos.Nombre,DetalleCuenta.PrecioUnit   order by TiposProductos.Codigo ,Productos.Nombre  ");
		}
		else
		{
			BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,    sum( DetalleCuenta.Pago) as Pagado,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + "  and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Codigo ,Productos.Nombre  order by TiposProductos.Codigo ,Productos.Nombre ");
			dataTable3 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where visitas.ParaLlevarID is null and ( DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and  (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TiposProductos.Codigo,Productos.Nombre  order by TiposProductos.Codigo ,Productos.Nombre   ");
			dataTable4 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where  visitas.EnMesa=" + VariableGeneral.armarBolean(1) + " and (visitas.ParaLlevarID  is not null and DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TiposProductos.Codigo,Productos.Nombre  order by TiposProductos.Codigo ,Productos.Nombre   ");
			BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,  sum( DetalleCuenta.Pago) as Pagado  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Descripcion order by TiposProductos.Descripcion     ");
			BD.ConsultaVer(" select TiposProductos.Descripcion as tipoProd,    sum(DetalleCuenta.Debe) as Debe   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Descripcion order by TiposProductos.Descripcion   ");
			BD.ConsultaVer("  select Clientes.Nombre ,     sum(DetalleCuenta.Debe) as Debe   from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)      left join clientes on clientes.ID =Visitas.ClienteID    where DetalleCuenta.Debe>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by Clientes.Nombre  order by Clientes.Nombre    ");
			BD.ConsultaVer(" select Familias.Descripcion as tipoProd,  sum( DetalleCuenta.Pago) as Pagado  from ((( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID) left join Familias on Familias.FamiliaId=TiposProductos.FamiliaID  where DetalleCuenta.Pago>0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by Familias.Descripcion order by Familias.Descripcion     ");
			BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd,Productos.Nombre as Producto,  sum(Borrados.Cantidad) as Cantidad,    DetalleCuenta.Hora as FechaPedido  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID)       inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID ) inner join Borrados on Borrados.DetalleCuentaID = DetalleCuenta.ID   where (borrados.PC like '" + text + "' and  (Borrados.fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TiposProductos.Codigo ,Productos.Nombre,DetalleCuenta.Hora  order by TiposProductos.Codigo ,Productos.Nombre ");
			dataTable5 = BD.ConsultaVer(" select TiposProductos.Codigo as tipoProd ,Productos.Nombre as Producto,  sum(DetalleCuenta.Cantidad) as Cantidad,     sum(DetalleCuenta.Debe) as Debe ,  sum( DetalleCuenta.Costo) as Costo  , (DetalleCuenta.PrecioUnit ) as precio  from (( DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) inner join Visitas on Visitas.ID=DetalleCuenta.VisitaID)     inner join TiposProductos on TiposProductos.TipoProductoID =Productos.TipoProductoID   where DetalleCuenta.Pago=0 and DetalleCuenta.Debe=0 and DetalleCuenta.Borrada=" + VariableGeneral.armarBolean(0) + " and    (DetalleCuenta.Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by TiposProductos.Codigo,Productos.Nombre,DetalleCuenta.PrecioUnit   order by TiposProductos.Codigo ,Productos.Nombre  ");
		}
		int num2 = 0;
		System.Data.DataTable dataTable6 = new System.Data.DataTable();
		System.Data.DataTable dataTable7 = BD.ConsultaVer(" select TurnoID from turnos where  (FechaIni= " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and FechaFin =  " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")");
		if (dataTable7.Rows.Count > 0)
		{
			num2 = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable7.Rows[0][0]), 0));
			dataTable6 = (System.Data.DataTable)VariableGeneral.NZ(BD.ConsultaVer("select * from Arqueo where TurnoID = " + Conversions.ToString(num2) + " order by ArqueoID desc"), 0);
			if (dataTable6.Rows.Count > 0)
			{
				Conversions.ToDouble(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["B200"]), 0), 200), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["B100"]), 0), 100)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["B50"]), 0), 50)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["B20"]), 0), 20)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["B10"]), 0), 10)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["B5"]), 0), 5)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["B2"]), 0), 2)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["B1"]), 0), 1)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["C50"]), 0), 0.5)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["C20"]), 0), 0.2)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["C10"]), 0), 0.1)));
				Conversions.ToDouble(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.AddObject(Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["D100"]), 0), 100), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["D50"]), 0), 50)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["D20"]), 0), 20)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["D10"]), 0), 10)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["D5"]), 0), 5)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["D1"]), 0), 1)));
				Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["Tarjetas"]), 0));
				Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable6.Rows[0]["_OtrasCajas"]), 0));
			}
		}
		BD.ConsultaVer("select max(nroFactura), min(nroFactura), sum(monto) as Monto, CodigoID, min(estadoSIAT) as estadoSIAT, max(FueraLineaID) as FueraLineaID  from Facturas   where Facturas.pc like '" + text + "' and  FechaEmision between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " and anulada=" + VariableGeneral.armarBolean(0) + " group by CodigoID");
		new System.Data.DataTable();
		if (configuration.gMODO_ACCESS == 1)
		{
			BD.ConsultaVer("select  iif (isnull(TipoEnvios.Nombre),'Atencion',TipoEnvios.Nombre)  as Tipo,count(visitas.ID) as Cantidad, sum(tab1.monto)  as Monto,tab1.Nombre as Cuenta   from ((visitas left join TipoEnvios on Visitas.TipoEnvioID =TipoEnvios.TipoEnvioID)  left join ( select  visitas.id, sum (pagos.MontoBs)as  monto  ,Cuentas.Nombre from ((Visitas left join DetalleCuenta  on Visitas.ID =DetalleCuenta.VisitaID) left join pagos on DetalleCuenta.id=Pagos.DetalleCuentaID) left join Cuentas on pagos.CuentaID =cuentas.CuentaID   where (DetalleCuenta.Borrada = " + VariableGeneral.armarBolean(aux: false) + "  and " + text2 + " and Pagos.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ") group by visitas.id,Cuentas.Nombre  ) as tab1 on Visitas.ID =tab1.ID  )  where (tab1.monto >0 and (Visitas.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + ")) group by TipoEnvios.Nombre,Cuentas.Nombre order by  TipoEnvios.Nombre ");
		}
		else
		{
			BD.ConsultaVer(string.Concat(string.Concat("select  isnull(TipoEnvios.Nombre,'Atencion') as Tipo,count(visitas.ID) as Cantidad, sum(tab1.monto)  as Monto ,tab1.Nombre as Cuenta  from visitas left join TipoEnvios on Visitas.TipoEnvioID =TipoEnvios.TipoEnvioID  left join ( select  visitas.id, sum (pagos.MontoBs)as  monto ,Cuentas.Nombre  from ((Visitas left join DetalleCuenta  on Visitas.ID =DetalleCuenta.VisitaID) left join pagos on DetalleCuenta.id=Pagos.DetalleCuentaID) left join Cuentas on pagos.CuentaID =cuentas.CuentaID   where DetalleCuenta.Borrada = " + VariableGeneral.armarBolean(aux: false) + "  and " + text2 + "  and Pagos.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), "group by visitas.id ,Cuentas.Nombre   ) as tab1 on Visitas.ID =tab1.ID    where tab1.monto >0 and Visitas.Fecha between ", VariableGeneral.ArmarFecha(dtpDesdeDate), " and ", VariableGeneral.ArmarFecha(dtpHastaDate)), " group by TipoEnvios.Nombre ,tab1.Nombre order by  TipoEnvios.Nombre "));
		}
		new System.Data.DataTable();
		if (configuration.gMODO_ACCESS == 1)
		{
			BD.ConsultaVer(string.Concat("select  iif (isnull (TipoEnvios.Nombre),'Mesa',TipoEnvios.Nombre)  as Tipo,count(visitas.ID) as Cantidad, sum(tab1.monto)  as Monto  from ((visitas left join TipoEnvios on Visitas.TipoEnvioID =TipoEnvios.TipoEnvioID)  left join ( select  visitas.id, sum (DetalleCuenta.Pago +  DetalleCuenta.Debe) as  monto from Visitas left join DetalleCuenta  on Visitas.ID =DetalleCuenta.VisitaID  where DetalleCuenta.Borrada = " + VariableGeneral.armarBolean(aux: false) + " group by visitas.id ) as tab1 on Visitas.ID =tab1.ID  )  where tab1.monto >0 and Visitas.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), " group by TipoEnvios.Nombre "));
		}
		else
		{
			BD.ConsultaVer(string.Concat("select  isnull(TipoEnvios.Nombre,'Mesa') as Tipo,count(visitas.ID) as Cantidad, sum(tab1.monto)  as Monto  from visitas left join TipoEnvios on Visitas.TipoEnvioID =TipoEnvios.TipoEnvioID  left join ( select  visitas.id, sum (DetalleCuenta.Pago +  DetalleCuenta.Debe) as  monto from Visitas left join DetalleCuenta  on Visitas.ID =DetalleCuenta.VisitaID  where DetalleCuenta.Borrada = " + VariableGeneral.armarBolean(aux: false) + " group by visitas.id ) as tab1 on Visitas.ID =tab1.ID    where tab1.monto >0 and Visitas.Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), " group by TipoEnvios.Nombre "));
		}
		BD.ConsultaVer((" select nroFactura, Monto  from Facturas   where Facturas.pc like '" + text + "' and  anulada = " + VariableGeneral.armarBolean(1) + " and  FechaAnulacion between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable8 = BD.ConsultaVer("Sum(Monto)", "Anticipos", ("Anticipos.PC like '" + text + "' and CuentaID=" + Conversions.ToString(1) + " and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable9 = BD.ConsultaVer("Sum(Monto)", "Anticipos", ("Anticipos.PC like '" + text + "' and CuentaID=" + Conversions.ToString(2) + " and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable10 = BD.ConsultaVer("Sum(Monto)", "Anticipos", ("Anticipos.PC like '" + text + "' and CuentaID=" + Conversions.ToString(3) + " and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable11 = BD.ConsultaVer("Sum(Monto)", "Anticipos inner join Cuentas on Anticipos.CuentaID =Cuentas.CuentaID ", ("Anticipos.PC like '" + text + "' and Cuentas.Moneda  =0 and Anticipos.CuentaID> 4  and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		System.Data.DataTable dataTable12 = BD.ConsultaVer("Sum(Monto)", "Anticipos inner join Cuentas on Anticipos.CuentaID =Cuentas.CuentaID ", ("Anticipos.PC like '" + text + "' and Cuentas.Moneda  =1 and Anticipos.CuentaID> 4  and Fecha between" + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)) ?? "");
		cobradoTarjeta = Conversions.ToDouble(Operators.SubtractObject(cobradoTarjeta, NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
		{
			VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable10.Rows[0][0]), 0),
			1
		}, null, null, null)));
		double num3 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("round(sum(Gastos.Monto),1)", "Gastos", "CuentaID >" + Conversions.ToString(4) + " and CuentaID in (select cuentaID from Cuentas where Moneda =0 )  and Gastos.FechaSalida is not null and FechaSalida between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)).Rows[0][0]), 0));
		double num4 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select   sum(tab1.Pagado) from ( SELECT    round(sum(Pagos.MontoBs),1)  as Pagado FROM DetalleCuenta INNER JOIN Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID where  CuentaId= " + Conversions.ToString(4) + " and MaquinaPago like '" + text + "' and Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " group by DetalleCuenta.VisitaID) as tab1").Rows[0][0]), 0));
		double num5 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select   sum((PrecioUnit * DetalleCuenta.Cantidad) - (DetalleCuenta.Pago + DetalleCuenta.Debe)) as Descuento FROM DetalleCuenta where DetalleCuenta.Pago + DetalleCuenta.Debe>0 and Borrada =0 and  Hora between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate)).Rows[0][0]), 0));
		BD.ConsultaVer("select tab1.Nombre as Cuenta, count(*) as Cantidad from (SELECT      Cuentas.Nombre , AgruparPagoID FROM Pagos left join Cuentas on Cuentas.CuentaID = Pagos.CuentaID where MaquinaPago like '" + text + "' and Fecha between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + "  group by AgruparPagoID,  Cuentas.Nombre) as tab1  group by tab1.Nombre ");
		Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select count(*) from (select visitas.ID, Visitas.ClienteID, sum(DetalleCuenta.debe) as deuda from Visitas left join DetalleCuenta on Visitas.ID =DetalleCuenta.VisitaID  where  Visitas.fecha  between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " group by visitas.ID, Visitas.ClienteID) as tab1 where tab1.deuda =0").Rows[0][0]), 0));
		Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select count(*) from (select visitas.ID, Visitas.ClienteID, sum(DetalleCuenta.debe) as deuda from Visitas left join DetalleCuenta on Visitas.ID =DetalleCuenta.VisitaID  where  Visitas.fecha  between " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate) + " group by visitas.ID, Visitas.ClienteID) as tab1 where tab1.deuda >0").Rows[0][0]), 0));
		object obj2 = p;
		NewLateBinding.LateCall(obj2, null, "AlignCenter", new object[0], null, null, null, IgnoreReturn: true);
		NewLateBinding.LateCall(obj2, null, "NormalBiggerFont", new object[0], null, null, null, IgnoreReturn: true);
		NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
		if (desdeReporteVentas)
		{
			NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "REPORTE DE VENTAS TOTALES" }, null, null, null, IgnoreReturn: true);
		}
		else
		{
			NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Reporte de Ventas " }, null, null, null, IgnoreReturn: true);
		}
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.srPollo)
		{
			NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "Turno No. " + Conversions.ToString(num2) }, null, null, null, IgnoreReturn: true);
		}
		if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza))
		{
			if (Mesero.Length > 7)
			{
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { Mesero.Substring(0, 7) }, null, null, null, IgnoreReturn: true);
			}
			else
			{
				object instance = obj2;
				object[] obj3 = new object[1] { Mesero };
				object[] array = obj3;
				bool[] obj4 = new bool[1] { true };
				bool[] array2 = obj4;
				NewLateBinding.LateCall(instance, null, "WriteLine", obj3, null, null, obj4, IgnoreReturn: true);
				if (array2[0])
				{
					Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
				}
			}
		}
		NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
		NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { DateAndTime.Now }, null, null, null, IgnoreReturn: true);
		if (!((configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza)))
		{
			if (desdeReporteVentas)
			{
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "DE TODAS LAS PC" }, null, null, null, IgnoreReturn: true);
			}
			else
			{
				object instance2 = obj2;
				object[] obj5 = new object[1] { text };
				object[] array = obj5;
				bool[] obj6 = new bool[1] { true };
				bool[] array2 = obj6;
				NewLateBinding.LateCall(instance2, null, "WriteLine", obj5, null, null, obj6, IgnoreReturn: true);
				if (array2[0])
				{
					text = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
				}
			}
		}
		ctlConfiguraciones ctlConfiguraciones2 = new ctlConfiguraciones();
		NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { (ctlConfiguraciones2.devolverDescripcion().ToString().Length == 0) ? ctlConfiguraciones2.devolverSucursal() : ctlConfiguraciones2.devolverDescripcion() }, null, null, null, IgnoreReturn: true);
		NewLateBinding.LateCall(obj2, null, "smallFont", new object[0], null, null, null, IgnoreReturn: true);
		NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { VariableGeneral.armarSoloLaFechaDMA(dtpDesdeDate) + " " + VariableGeneral.armarSoloLaHora(dtpDesdeDate) + "  -  " + VariableGeneral.armarSoloLaFechaDMA(dtpHastaDate) + " " + VariableGeneral.armarSoloLaHora(dtpHastaDate) }, null, null, null, IgnoreReturn: true);
		if (Mesero.Length > 0 && !((configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza)))
		{
			object instance3 = obj2;
			object[] obj7 = new object[1] { Mesero };
			object[] array = obj7;
			bool[] obj8 = new bool[1] { true };
			bool[] array2 = obj8;
			NewLateBinding.LateCall(instance3, null, "WriteLine", obj7, null, null, obj8, IgnoreReturn: true);
			if (array2[0])
			{
				Mesero = (string)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(string));
			}
		}
		NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
		double num6 = 0.0;
		double num7 = 0.0;
		checked
		{
			int num8 = dataTable3.Rows.Count - 1;
			for (int i = 0; i <= num8; i++)
			{
				num6 = Conversions.ToDouble(Operators.AddObject(num6, dataTable3.Rows[i]["Debe"]));
			}
			int num9 = dataTable4.Rows.Count - 1;
			for (int j = 0; j <= num9; j++)
			{
				num7 = Conversions.ToDouble(Operators.AddObject(num7, dataTable4.Rows[j]["Debe"]));
			}
			if (configuration.gStyleBoliches1 != configuration.styleBolichesId.SirFrancis)
			{
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Inicial (Bs) : " }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(montoIniBs, 1) }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Inicial ($Us) : " }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(montoIniDolares, 1) }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza))
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto en Caja (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(montoIniBs + cobradoBs + ventaBS + otrosBs - gastadoBs, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable8.Rows[0][0]), 0)),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "DrawLine", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
				}
			}
			double num10 = 0.0;
			System.Data.DataTable dataTable13 = new System.Data.DataTable();
			System.Data.DataTable dataTable14 = new System.Data.DataTable();
			System.Data.DataTable dataTable15 = new System.Data.DataTable();
			if (configuration.gStyleBoliches1 != configuration.styleBolichesId.BuenDia)
			{
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable8.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo (Bs): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable8.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable9.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo ($us): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable9.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable10.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo (Tarjeta): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable10.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable11.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo (Otros Bs): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable11.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable12.Rows[0][0]), 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Anticipo (Otros $us): " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable12.Rows[0][0]), 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				if (cobradoBs != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas en Efectivo (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(cobradoBs, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (cobradoTarjeta != 0.0)
				{
					if (PropinaTarjeta != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas Tarjeta : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(cobradoTarjeta, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					if (PropinaTarjeta != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Propinas Tarjeta : " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(PropinaTarjeta, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "___________________" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1.5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Total Tarjeta : " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(cobradoTarjeta + PropinaTarjeta, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LomoGrill)
				{
					double num11 = Conversions.ToDouble(Operators.CompareObjectGreater(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(Operators.AddObject(num, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable11.Rows[0][0]), 0)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable12.Rows[0][0]), 0), clsTi.returnTipoCambio())),
						1
					}, null, null, null), 0, TextCompare: false));
					if (num11 != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas otras Cuentas: " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						object instance4 = obj2;
						object[] obj9 = new object[1] { num11 };
						object[] array = obj9;
						bool[] obj10 = new bool[1] { true };
						bool[] array2 = obj10;
						NewLateBinding.LateCall(instance4, null, "WriteChars", obj9, null, null, obj10, IgnoreReturn: true);
						if (array2[0])
						{
							num11 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
						}
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				else if (num > 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas Otras Cuentas: " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num, 1) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (Operators.ConditionalCompareObjectNotEqual(VariableGeneral.NZ(num4, 0), 0, TextCompare: false))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas Anticipos : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(num4, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				if (!((configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspana) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspanaPanaderia)))
				{
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Total Cobros : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					num10 = Conversions.ToDouble(Operators.AddObject(Operators.AddObject(cobradoBs + cobradoTarjeta, VariableGeneral.NZ(num4, 0)), VariableGeneral.NZ(num, 0)));
					if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Subway)
					{
						num10 = Math.Round(num10, 1);
					}
					object[] array;
					bool[] array2;
					NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { num10 }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
					if (array2[0])
					{
						num10 = (double)Conversions.ChangeType(RuntimeHelpers.GetObjectValue(array[0]), typeof(double));
					}
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
				}
				if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Rinconada)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					if (num6 != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Deuda Total (Bs): " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num6, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				if (desdeReporteVentas)
				{
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					if (num6 != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Ventas Total (Bs): " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num10 + num6, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
				if (gastadoBs != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Gastos (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(gastadoBs, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (gastadoDolares != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Gastos ($Us) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(gastadoDolares, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (otrosBs != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Otros (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(otrosBs, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if (otrosDolares != 0.0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Otros ($Us) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(otrosDolares, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				if ((num3 != 0.0) & (configuration.gStyleBoliches1 == configuration.styleBolichesId.shiwu))
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Gastos Otras Cajas (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						VariableGeneral.NZ(num3, 0),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.UgosPizza) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Vikingo))
				{
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SirFrancis))
					{
						montoIniBs = 0.0;
						montoIniDolares = 0.0;
					}
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Final (Bs) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(montoIniBs + cobradoBs + ventaBS + otrosBs - gastadoBs, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable8.Rows[0][0]), 0)),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Final Tarjeta : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(cobradoTarjeta, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable10.Rows[0][0]), 0)),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(Operators.AddObject(num, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable11.Rows[0][0]), 0)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable12.Rows[0][0]), 0), clsTi.returnTipoCambio())),
						1
					}, null, null, null), 0, TextCompare: false))
					{
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Final Otras Cuentas : " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							Operators.AddObject(Operators.AddObject(num, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable11.Rows[0][0]), 0)), Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable12.Rows[0][0]), 0), clsTi.returnTipoCambio())),
							1
						}, null, null, null) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto Final ($Us) : " }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
					{
						Operators.AddObject(montoIniDolares + compraDolares - gastadoDolares + otrosDolares, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable9.Rows[0][0]), 0)),
						1
					}, null, null, null) }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					if (compraDolares != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Compras ($Us) : " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(compraDolares, 1) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					if (num5 != 0.0)
					{
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Descuentos (Bs): " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							VariableGeneral.NZ(num5, 0),
							1
						}, null, null, null) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					double num12 = 0.0;
					if (dataTable5.Rows.Count > 0)
					{
						int num13 = dataTable5.Rows.Count - 1;
						for (int k = 0; k <= num13; k++)
						{
							num12 = Conversions.ToDouble(Operators.AddObject(num12, NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
							{
								Operators.MultiplyObject(dataTable5.Rows[k]["Precio"], dataTable5.Rows[k]["Cantidad"]),
								2
							}, null, null, null)));
						}
						NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cortesia (Bs): " }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
						{
							VariableGeneral.NZ(num12, 0),
							1
						}, null, null, null) }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
				}
			}
			dataTable13 = clsPagos2.DevolverEntreFechasPorMaquinaPorOtrasCuentasAnticipo(dtpDesdeDate, dtpHastaDate, configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin);
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KulturBerlin)
			{
				if (dataTable13.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Desglose Cobros de otras cuentas" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cuenta" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2.3 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Pagado con" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.5 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Monto" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					int num14 = dataTable13.Rows.Count - 1;
					for (int l = 0; l <= num14; l++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						object[] array;
						DataRow dataRow;
						bool[] array2;
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable13.Rows[l])[0] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow[0] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 2 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", array = new object[1] { (dataRow = dataTable13.Rows[l])[1] }, null, null, array2 = new bool[1] { true }, IgnoreReturn: true);
						if (array2[0])
						{
							dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5.3 }, null, null, null, IgnoreReturn: true);
						object instance5 = obj2;
						object[] array3 = new object[1];
						object left = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable13.Rows[l])[2],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow[2] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array3[0] = Operators.ConcatenateObject(left, " (Bs) ");
						NewLateBinding.LateCall(instance5, null, "WriteChars", array3, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
			}
			else if (dataTable13.Rows.Count > 0)
			{
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Desglose Cobros de otras cuentas" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				int num15 = dataTable13.Rows.Count - 1;
				for (int m = 0; m <= num15; m++)
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(dataTable13.Rows[m][0], " :  ") }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					object instance6 = obj2;
					object[] array4 = new object[1];
					object[] array;
					DataRow dataRow;
					bool[] array2;
					object obj11 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
					{
						(dataRow = dataTable13.Rows[m])[1],
						1
					}, null, null, array2 = new bool[2] { true, false });
					if (array2[0])
					{
						dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
					}
					array4[0] = obj11;
					NewLateBinding.LateCall(instance6, null, "WriteChars", array4, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
			}
			double num16 = 0.0;
			dataTable14 = BD.ConsultaVer("Cuentas.Nombre , sum(MontoBs) as Total ", "Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID ", "MaquinaPago like 'Pedidos Ya%' and Fecha between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), "Cuentas.Nombre", "Cuentas.Nombre");
			dataTable15 = BD.ConsultaVer("Cuentas.Nombre , sum(MontoBs) as Total ", "Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID ", "MaquinaPago like 'Pedidos Ya%' and Fecha between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), "Cuentas.Nombre", "Cuentas.Nombre");
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.ComidaSuarez)
			{
				if (dataTable15.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Para cobrarle a Pedidos Ya" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					int num17 = dataTable15.Rows.Count - 1;
					for (int n = 0; n <= num17; n++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(dataTable15.Rows[n][0], " (Bs) :  ") }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						object instance7 = obj2;
						object[] array5 = new object[1];
						object[] array;
						DataRow dataRow;
						bool[] array2;
						object obj12 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable15.Rows[n])[1],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array5[0] = obj12;
						NewLateBinding.LateCall(instance7, null, "WriteChars", array5, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
						num16 = Conversions.ToDouble(Operators.AddObject(num16, dataTable15.Rows[n][1]));
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
			}
			else if (dataTable14.Rows.Count > 0)
			{
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Para cobrarle a Pedidos Ya" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
				int num18 = dataTable14.Rows.Count - 1;
				for (int num19 = 0; num19 <= num18; num19++)
				{
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(dataTable14.Rows[num19][0], " (Bs) :  ") }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
					object instance8 = obj2;
					object[] array6 = new object[1];
					object[] array;
					DataRow dataRow;
					bool[] array2;
					object obj13 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
					{
						(dataRow = dataTable14.Rows[num19])[1],
						1
					}, null, null, array2 = new bool[2] { true, false });
					if (array2[0])
					{
						dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
					}
					array6[0] = obj13;
					NewLateBinding.LateCall(instance8, null, "WriteChars", array6, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					num16 = Conversions.ToDouble(Operators.AddObject(num16, dataTable14.Rows[num19][1]));
				}
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
			}
			double num20 = 0.0;
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen)
			{
				System.Data.DataTable dataTable16 = BD.ConsultaVer("Cuentas.Nombre , sum(MontoBs) as Total ", "Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID ", "MaquinaPago like 'QR' and Fecha between  " + VariableGeneral.ArmarFecha(dtpDesdeDate) + " and " + VariableGeneral.ArmarFecha(dtpHastaDate), "Cuentas.Nombre", "Cuentas.Nombre");
				if (dataTable16.Rows.Count > 0)
				{
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "Cobros con QR" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					NewLateBinding.LateCall(obj2, null, "NormalFont", new object[0], null, null, null, IgnoreReturn: true);
					int num21 = dataTable16.Rows.Count - 1;
					for (int num22 = 0; num22 <= num21; num22++)
					{
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Operators.ConcatenateObject(dataTable16.Rows[num22][0], " (Bs) :  ") }, null, null, null, IgnoreReturn: true);
						NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
						object instance9 = obj2;
						object[] array7 = new object[1];
						object[] array;
						DataRow dataRow;
						bool[] array2;
						object obj14 = NewLateBinding.LateGet(null, typeof(Math), "Round", array = new object[2]
						{
							(dataRow = dataTable16.Rows[num22])[1],
							1
						}, null, null, array2 = new bool[2] { true, false });
						if (array2[0])
						{
							dataRow[1] = RuntimeHelpers.GetObjectValue(RuntimeHelpers.GetObjectValue(array[0]));
						}
						array7[0] = obj14;
						NewLateBinding.LateCall(instance9, null, "WriteChars", array7, null, null, null, IgnoreReturn: true);
						num20 = Conversions.ToDouble(Operators.AddObject(num20, dataTable16.Rows[num22][1]));
						NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
					}
					NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				}
			}
			if (((configuration.gStyleBoliches1 != configuration.styleBolichesId.InesEspana) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.InesEspanaPanaderia)) && ((num16 > 0.0) | (num20 > 0.0)))
			{
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { true }, null, null);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 1 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { "TOTAL VENTAS" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "GotoSixth", new object[1] { 5 }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteChars", new object[1] { Math.Round(num10 + num16 + num20, 1) }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateCall(obj2, null, "WriteLine", new object[1] { "" }, null, null, null, IgnoreReturn: true);
				NewLateBinding.LateSet(obj2, null, "Bold", new object[1] { false }, null, null);
			}
			ToEmail = Conversions.ToString(NewLateBinding.LateGet(p, null, "Texto", new object[0], null, null, null));
			NewLateBinding.LateCall(obj2, null, "CutPaper", new object[0], null, null, null, IgnoreReturn: true);
			NewLateBinding.LateCall(obj2, null, "EndDoc", new object[0], null, null, null, IgnoreReturn: true);
			obj2 = null;
		}
	}

	public static void SubirSac()
	{
		System.Data.DataTable dataTable = new System.Data.DataTable();
		int num = default(int);
		ctlVentasNoSincronizadas ctlVentasNoSincronizadas2 = new ctlVentasNoSincronizadas();
		ctlVisitas ctlVisitas2 = new ctlVisitas();
		ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
		System.Data.DataTable dataTable2 = new System.Data.DataTable();
		dataTable = ctlVentasNoSincronizadas2.devolverVentasNoSincronizadas();
		num = ((configuration.gStyleBoliches1 != configuration.styleBolichesId.BiancaFlor) ? Conversions.ToInteger(BD.ConsultaVer("select SUBSTRING(LTRIM(Sucursal),0,2) as sucursal  from Configuraciones").Rows[0][0]) : 0);
		checked
		{
			if (dataTable.Rows.Count > 0)
			{
				WebServiceVentasSoapClient webServiceVentasSoapClient = new WebServiceVentasSoapClient();
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BiancaFlor)
				{
					if (configuration.gModo_Remoto)
					{
						webServiceVentasSoapClient.Endpoint.Address = new EndpointAddress("http://177.222.127.43");
					}
					else
					{
						webServiceVentasSoapClient.Endpoint.Address = new EndpointAddress("http://192.168.10.10");
					}
				}
				string text = "";
				string NIT = "";
				string Nombre = "";
				int clientID = 0;
				DateTime fecha = DateAndTime.Now;
				string email = "";
				int TipoDocumento = 0;
				int formaPago = 0;
				string nroTarjeta = "";
				string codigo = "";
				double num2 = 0.0;
				string text2 = "";
				int num3 = dataTable.Rows.Count - 1;
				for (int i = 0; i <= num3; i++)
				{
					try
					{
						text = "";
						text2 = "";
						int visitaID = Conversions.ToInteger(dataTable.Rows[i]["VisitaID"]);
						int facturaID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["FacturaID"]), 0));
						string Monto = Conversions.ToString(num2);
						ctlVisitas2.CabecerSacNet(visitaID, 0, facturaID, ref NIT, ref Nombre, ref clientID, ref fecha, ref email, ref TipoDocumento, ref formaPago, ref nroTarjeta, ref codigo, ref Monto);
						num2 = Conversions.ToDouble(Monto);
						codigo = "0";
						text = webServiceVentasSoapClient.VentaCabecera(VariableGeneral.ArmarSoloFechaSTR(fecha), Conversions.ToString(dataTable.Rows[i]["VisitaID"]), Conversions.ToString(clientID), NIT, Nombre, Conversions.ToInteger(num.ToString()), codigo, new decimal(num2), formaPago);
						bool flag = false;
						dataTable2 = ctlDetalleCuenta2.DevolverDetalleSacNet(Conversions.ToInteger(dataTable.Rows[i]["VisitaID"]), Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i]["FacturaID"]), 0)));
						int num4 = dataTable2.Rows.Count - 1;
						for (int j = 0; j <= num4; j++)
						{
							flag = false;
							if (dataTable2.Rows[j]["Codigo"].ToString().EndsWith("Xx"))
							{
								dataTable2.Rows[j]["Codigo"] = dataTable2.Rows[j]["Codigo"].ToString().Substring(0, dataTable2.Rows[j]["Codigo"].ToString().Length - 2);
							}
							text2 = Conversions.ToString(dataTable2.Rows[j]["Codigo"]);
							try
							{
								webServiceVentasSoapClient.VentaDetalle(text, Conversions.ToString(dataTable2.Rows[j]["Codigo"]), Conversions.ToDecimal(dataTable2.Rows[j]["Cantidad"]), Conversions.ToDecimal(dataTable2.Rows[j]["Descuento"]));
							}
							catch (Exception ex)
							{
								ProjectData.SetProjectError(ex);
								Exception ex2 = ex;
								flag = true;
								if (ex2.Message.Contains("No data exists for the row or column."))
								{
									Interaction.MsgBox(Operators.ConcatenateObject(Operators.ConcatenateObject("No existe el producto: " + text2 + " / ", dataTable2.Rows[j]["Nombre"]), ". Verifique en el sistema contable y dele aceptar para reintentar"));
									webServiceVentasSoapClient.VentaDetalle(text, Conversions.ToString(dataTable2.Rows[j]["Codigo"]), Conversions.ToDecimal(dataTable2.Rows[j]["Cantidad"]), Conversions.ToDecimal(dataTable2.Rows[j]["Descuento"]));
									flag = false;
								}
								ProjectData.ClearProjectError();
							}
						}
						if (!flag)
						{
							ctlVentasNoSincronizadas2.SetVentaNoSincronizadaID(Conversions.ToInteger(dataTable.Rows[i]["VentaNoSincronizadaID"]));
							ctlVentasNoSincronizadas2.EliminarVentasNoSincronizadas();
						}
						else
						{
							ctlVentasNoSincronizadas2.SetVentaNoSincronizadaID(Conversions.ToInteger(dataTable.Rows[i]["VentaNoSincronizadaID"]));
							ctlVentasNoSincronizadas2.GuardarVentas(Conversions.ToString(ctlVisitas2.GetID()), "No existe el producto: " + text2, 0);
						}
					}
					catch (Exception ex3)
					{
						ProjectData.SetProjectError(ex3);
						Exception ex4 = ex3;
						string text3 = ex4.Message.ToString();
						text3 = text3.Replace("System.ServiceModel.FaultException: Server was unable to process request. ---> ", "");
						text3 = text3.Replace("Server was unable to process request. ---> ORA-20501: ", "");
						text3 = text3.Replace("Server was unable to process request. ---> ORA-00604: ", "");
						text3 = text3.Replace("Server was unable to process request. ---> ", "");
						if (text3.Contains("No data exists for the row or column."))
						{
							text3 = ((text2.Length > 0) ? ("Prod Code " + text2 + ". ") : "") + text3;
						}
						if (text3.Contains("NroDoc ya Existe, Otro usuario en la red ya registro este Valor."))
						{
							text3 = "NroDoc ya Existe, Otro usuario en la red ya registro este Valor.";
						}
						int length = 449;
						if (text3.ToString().Length < 450)
						{
							length = text3.ToString().Length;
						}
						string observacion = text3.ToString().Substring(0, length);
						ctlVentasNoSincronizadas2.SetVentaNoSincronizadaID(Conversions.ToInteger(dataTable.Rows[i]["VentaNoSincronizadaID"]));
						ctlVentasNoSincronizadas2.GuardarVentas(Conversions.ToString(ctlVisitas2.GetID()), observacion, 0);
						ProjectData.ClearProjectError();
					}
				}
			}
			Interaction.MsgBox("Termino!");
		}
	}

	public static bool SendEmailA(string correo, string data, string titulo, bool copiaAmi, bool enPDF, ref string error1)
	{
		checked
		{
			bool result;
			try
			{
				if (VariableGeneral.CheckForInternetConnection())
				{
					string[] array = correo.Split(';');
					if (array.Length == 0)
					{
						error1 = "no correo";
						result = false;
					}
					else
					{
						if (enPDF)
						{
							System.Drawing.Font font = new System.Drawing.Font("FontA1x1", 12f);
							Size size = TextRenderer.MeasureText(data, font);
							bool flag = false;
							if (size.Width > 400)
							{
								flag = true;
							}
							if (File.Exists("Info.pdf"))
							{
								File.Delete("Info.pdf");
							}
							PdfWriter writer = new PdfWriter("Info.pdf");
							PdfDocument pdfDocument = new PdfDocument(writer);
							Document document = new Document(pdfDocument);
							if (flag)
							{
								pdfDocument.SetDefaultPageSize(PageSize.A4.Rotate());
							}
							Paragraph element = new Paragraph(titulo).SetTextAlignment(TextAlignment.CENTER).SetFontSize(15f);
							document.Add(element);
							Paragraph paragraph = new Paragraph("").SetTextAlignment(TextAlignment.LEFT).SetFontSize(9f);
							paragraph.SetWordSpacing(-1f);
							string text = "";
							int num = data.Length - 1;
							for (int i = 0; i <= num; i++)
							{
								if (Operators.CompareString(Conversions.ToString(data[i]), "\t", TextCompare: false) == 0)
								{
									paragraph.Add(text);
									paragraph.Add(new iText.Layout.Element.Tab());
									text = "";
								}
								else
								{
									text += Conversions.ToString(data[i]);
								}
							}
							paragraph.Add(text);
							document.Add(paragraph);
							data = "\r\rGracias, el equipo de Toptech\r\rEste es un mensaje automatizado. Por favor no responda a este correo.\rPor cualquier consulta puede escribirnos a mailto:soporte@toptech.com.bo\rToptech Ⓒ 2014-" + Conversions.ToString(DateAndTime.Today.Year);
						}
						else
						{
							data = data + "\r\rGracias, el equipo de Toptech\r\rEste es un mensaje automatizado. Por favor no responda a este correo.\rPor cualquier consulta puede escribirnos a mailto:soporte@toptech.com.bo\rToptech Ⓒ 2014-" + Conversions.ToString(DateAndTime.Today.Year);
						}
						ctlEmail obj = new ctlEmail();
						int idEmail = 0;
						string Username = "";
						string Passw = "";
						string Port = "";
						string Frm = "";
						string Host = "";
						bool SSL = false;
						obj.devolver(ref idEmail, ref Username, ref Passw, ref Port, ref Frm, ref Host, ref SSL);
						SmtpClient smtpClient = new SmtpClient();
						MailMessage mailMessage = new MailMessage();
						smtpClient.Credentials = new NetworkCredential(Username, Passw);
						smtpClient.Port = Conversions.ToInteger(Port);
						if (SSL)
						{
							smtpClient.EnableSsl = true;
						}
						smtpClient.Host = Host;
						mailMessage = new MailMessage();
						mailMessage.From = new MailAddress(Frm);
						string[] array2 = array;
						foreach (string text2 in array2)
						{
							mailMessage.To.Add(text2.ToString().Trim());
						}
						if (copiaAmi)
						{
							mailMessage.Bcc.Add("toptechsrl@gmail.com");
						}
						mailMessage.Subject = titulo;
						mailMessage.Body = data;
						if (enPDF && File.Exists("Info.pdf"))
						{
							mailMessage.Attachments.Add(new Attachment("Info.pdf"));
						}
						ServicePointManager.ServerCertificateValidationCallback = [SpecialName] (object s, X509Certificate certificate, X509Chain chain, SslPolicyErrors sslPolicyErrors) => true;
						smtpClient.Send(mailMessage);
						result = true;
					}
				}
				else
				{
					error1 = "no internet";
					result = false;
				}
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				error1 = ex2.Message;
				result = false;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public static void SendEmail(string data, string titulo, bool copiaAmi, bool enPDF, bool conMsgbox = true)
	{
		checked
		{
			try
			{
				if (VariableGeneral.CheckForInternetConnection())
				{
					string[] array = new ctlConfiguraciones().devolverEmails().Split(';');
					if (array.Length == 0)
					{
						return;
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.pizzaRing)
					{
						enPDF = true;
					}
					if (enPDF)
					{
						try
						{
							System.Drawing.Font font = new System.Drawing.Font("FontA1x1", 12f);
							Size size = TextRenderer.MeasureText(data, font);
							bool flag = false;
							if (size.Width > 400)
							{
								flag = true;
							}
							if (File.Exists("Info.pdf"))
							{
								File.Delete("Info.pdf");
							}
							PdfWriter writer = new PdfWriter("Info.pdf");
							PdfDocument pdfDocument = new PdfDocument(writer);
							Document document = new Document(pdfDocument);
							if (flag)
							{
								pdfDocument.SetDefaultPageSize(PageSize.A4.Rotate());
							}
							Paragraph element = new Paragraph(titulo).SetTextAlignment(TextAlignment.CENTER).SetFontSize(15f);
							document.Add(element);
							Paragraph paragraph = new Paragraph("").SetTextAlignment(TextAlignment.LEFT).SetFontSize(9f);
							paragraph.SetWordSpacing(-1f);
							string text = "";
							int num = data.Length - 1;
							for (int i = 0; i <= num; i++)
							{
								if (Operators.CompareString(Conversions.ToString(data[i]), "\t", TextCompare: false) == 0)
								{
									paragraph.Add(text);
									paragraph.Add(new iText.Layout.Element.Tab());
									text = "";
								}
								else
								{
									text += Conversions.ToString(data[i]);
								}
							}
							paragraph.Add(text);
							document.Add(paragraph);
							document.Close();
							data = "\r\rGracias, el equipo de Toptech\r\rEste es un mensaje automatizado. Por favor no responda a este correo.\rPor cualquier consulta puede escribirnos a mailto:soporte@toptech.com.bo\rToptech  Ⓒ 2014-" + Conversions.ToString(DateAndTime.Today.Year);
						}
						catch (Exception ex)
						{
							ProjectData.SetProjectError(ex);
							Exception ex2 = ex;
							if (conMsgbox)
							{
								Interaction.MsgBox(ex2.Message);
							}
							data = data + "\r\rGracias, el equipo de Toptech\r\rEste es un mensaje automatizado. Por favor no responda a este correo.\rPor cualquier consulta puede escribirnos a mailto:soporte@toptech.com.bo\rToptech  Ⓒ 2014-" + Conversions.ToString(DateAndTime.Today.Year);
							ProjectData.ClearProjectError();
						}
					}
					else
					{
						data = data + "\r\rGracias, el equipo de Toptech\r\rEste es un mensaje automatizado. Por favor no responda a este correo.\rPor cualquier consulta puede escribirnos a mailto:soporte@toptech.com.bo\rToptech  Ⓒ 2014-" + Conversions.ToString(DateAndTime.Today.Year);
					}
					ctlEmail obj = new ctlEmail();
					int idEmail = 0;
					string Username = "";
					string Passw = "";
					string Port = "";
					string Frm = "";
					string Host = "";
					bool SSL = false;
					obj.devolver(ref idEmail, ref Username, ref Passw, ref Port, ref Frm, ref Host, ref SSL);
					SmtpClient smtpClient = new SmtpClient();
					MailMessage mailMessage = new MailMessage();
					if (SSL)
					{
						smtpClient.EnableSsl = true;
					}
					smtpClient.Credentials = new NetworkCredential(Username, Passw);
					smtpClient.Port = Conversions.ToInteger(Port);
					smtpClient.Host = Host;
					mailMessage = new MailMessage();
					mailMessage.From = new MailAddress(Frm);
					string[] array2 = array;
					foreach (string text2 in array2)
					{
						if (text2.ToString().Trim().Length > 0)
						{
							mailMessage.To.Add(text2.ToString().Trim());
						}
					}
					smtpClient.Timeout = 20000;
					if (titulo.Length > 73)
					{
						mailMessage.Subject = titulo.Substring(0, 73);
					}
					else
					{
						mailMessage.Subject = titulo;
					}
					mailMessage.Body = data;
					if (enPDF && File.Exists("Info.pdf"))
					{
						mailMessage.Attachments.Add(new Attachment("Info.pdf"));
					}
					if (copiaAmi)
					{
						mailMessage.Bcc.Add("toptechsrl@gmail.com");
					}
					ServicePointManager.ServerCertificateValidationCallback = [SpecialName] (object s, X509Certificate certificate, X509Chain chain, SslPolicyErrors sslPolicyErrors) => true;
					smtpClient.Send(mailMessage);
					if (conMsgbox)
					{
						Interaction.MsgBox("Email enviado");
					}
				}
				else
				{
					new clsLogg().Insertar("Mail", "No Tiene Intenet", 0);
					if (conMsgbox)
					{
						Interaction.MsgBox("No tiene internet");
					}
				}
			}
			catch (Exception ex3)
			{
				ProjectData.SetProjectError(ex3);
				Exception ex4 = ex3;
				new clsLogg().Insertar("Mail", "Excepcion", 0);
				if (conMsgbox)
				{
					Interaction.MsgBox(ex4.ToString());
				}
				ProjectData.ClearProjectError();
			}
		}
	}

	public static void sendEmailData(string data, string titulo, bool copiaAmi, string archivo, string archivo2 = "")
	{
		try
		{
			if (VariableGeneral.CheckForInternetConnection())
			{
				string[] array = new ctlConfiguraciones().devolverEmails().Split(';');
				if (array.Length == 0)
				{
					return;
				}
				data = data + "\r\rGracias, el equipo de Toptech\r\rEste es un mensaje automatizado. Por favor no responda a este correo.\rPor cualquier consulta puede escribirnos a mailto:soporte@toptech.com.bo\rToptech  Ⓒ 2014-" + Conversions.ToString(DateAndTime.Today.Year);
				ctlEmail obj = new ctlEmail();
				int idEmail = 0;
				string Username = "";
				string Passw = "";
				string Port = "";
				string Frm = "";
				string Host = "";
				bool SSL = false;
				obj.devolver(ref idEmail, ref Username, ref Passw, ref Port, ref Frm, ref Host, ref SSL);
				SmtpClient smtpClient = new SmtpClient();
				MailMessage mailMessage = new MailMessage();
				if (SSL)
				{
					smtpClient.EnableSsl = true;
				}
				smtpClient.Credentials = new NetworkCredential(Username, Passw);
				smtpClient.Port = Conversions.ToInteger(Port);
				smtpClient.Host = Host;
				mailMessage = new MailMessage();
				mailMessage.From = new MailAddress(Frm);
				string[] array2 = array;
				foreach (string text in array2)
				{
					if (text.ToString().Trim().Length > 0)
					{
						mailMessage.To.Add(text.ToString().Trim());
					}
				}
				smtpClient.Timeout = 20000;
				mailMessage.Attachments.Add(new Attachment(archivo));
				if (Operators.CompareString(archivo2, "", TextCompare: false) != 0)
				{
					mailMessage.Attachments.Add(new Attachment(archivo2));
				}
				if (titulo.Length > 73)
				{
					mailMessage.Subject = titulo.Substring(0, 73);
				}
				else
				{
					mailMessage.Subject = titulo;
				}
				mailMessage.Body = data;
				if (copiaAmi)
				{
					mailMessage.Bcc.Add("toptechsrl@gmail.com");
				}
				ServicePointManager.ServerCertificateValidationCallback = [SpecialName] (object s, X509Certificate certificate, X509Chain chain, SslPolicyErrors sslPolicyErrors) => true;
				smtpClient.Send(mailMessage);
				mailMessage.Dispose();
				Interaction.MsgBox("Email enviado");
			}
			else
			{
				new clsLogg().Insertar("Mail", "No Tiene Intenet", 0);
				Interaction.MsgBox("No tiene internet");
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			new clsLogg().Insertar("Mail", "Excepcion", 0);
			Interaction.MsgBox(ex2.ToString());
			ProjectData.ClearProjectError();
		}
	}

	public static bool sendEmailFactura(string emailTo, string archivoXML, string archivoFactura, int Modalidad, string NombreFactura, string NroFactura, DateTime FechaEmision, string link, bool esFactura)
	{
		SmtpClient smtpClient = new SmtpClient();
		MailMessage mailMessage = new MailMessage();
		bool result;
		try
		{
			if (VariableGeneral.CheckForInternetConnection())
			{
				if (emailTo.Length == 0)
				{
					result = false;
				}
				else
				{
					string body = "Estimado(a) " + NombreFactura.ToString().ToUpper() + ", \r\n\r\nAdjunto a este correo, encontrará su " + ((!esFactura) ? "Nota Debito Credito" : ("Factura " + ((Modalidad == 1) ? "Electrónica" : "Computarizada") + " en Línea (Representación Gráfica)")) + " con N°  " + NroFactura + " emitida en " + VariableGeneral.ArmarSoloFechaSTR(FechaEmision) + " y la información de la transacción en formato XML. \r\n\r\nEste correo se genera de forma automática. Si tiene algún problema al abrir la factura, también puede descargarla desde la página web " + link + "\r\n\r\nCualquier consulta respecto a la factura, no dude realizarla dentro del mes de su emisión. \r\n\r\nGracias por su preferencia!";
					ctlEmail obj = new ctlEmail();
					int idEmail = 0;
					string Username = "";
					string Passw = "";
					string Port = "";
					string Frm = "";
					string Host = "";
					bool SSL = false;
					obj.devolver(ref idEmail, ref Username, ref Passw, ref Port, ref Frm, ref Host, ref SSL);
					if (SSL)
					{
						smtpClient.EnableSsl = true;
					}
					else
					{
						smtpClient.EnableSsl = false;
					}
					smtpClient.Credentials = new NetworkCredential(Username, Passw);
					smtpClient.Port = Conversions.ToInteger(Port);
					smtpClient.Host = Host;
					mailMessage = new MailMessage();
					mailMessage.From = new MailAddress(Frm);
					smtpClient.Timeout = 20000;
					if (File.Exists(archivoFactura))
					{
						mailMessage.Attachments.Add(new Attachment(archivoFactura));
					}
					if (File.Exists(archivoXML))
					{
						mailMessage.Attachments.Add(new Attachment(archivoXML));
					}
					if (esFactura)
					{
						mailMessage.Subject = "Notificación Emisión de Factura";
					}
					else
					{
						mailMessage.Subject = "Notificación Emisión de Nota Debito Credito";
					}
					mailMessage.Body = body;
					string[] array = emailTo.Split(';');
					if (array.Length == 0)
					{
						result = false;
					}
					else
					{
						string[] array2 = array;
						foreach (string text in array2)
						{
							if (text.ToString().Trim().Length > 0)
							{
								mailMessage.To.Add(text.ToString().Trim().Replace("ñ", "n"));
							}
						}
						smtpClient.Timeout = 20000;
						ServicePointManager.ServerCertificateValidationCallback = [SpecialName] (object s, X509Certificate certificate, X509Chain chain, SslPolicyErrors sslPolicyErrors) => true;
						smtpClient.Send(mailMessage);
						mailMessage.Dispose();
						mailMessage = null;
						result = true;
					}
				}
			}
			else
			{
				new clsLogg().Insertar("Mail", "No Tiene Internet", 0);
				result = false;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			new clsLogg().Insertar("Mail", ex2.Message.ToString().Substring(0, (ex2.Message.Length > 200) ? 200 : ex2.Message.Length), 0);
			Interaction.MsgBox(ex2.ToString());
			mailMessage.Dispose();
			mailMessage = null;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public static bool sendEmailAnularFactura(string emailTo, string Titulo, string body)
	{
		bool result;
		try
		{
			if (VariableGeneral.CheckForInternetConnection())
			{
				if (emailTo.Length == 0)
				{
					result = false;
				}
				else
				{
					string body2 = body + "\r\nEste es un mensaje automatizado. Por favor no responda a este correo.";
					ctlEmail obj = new ctlEmail();
					int idEmail = 0;
					string Username = "";
					string Passw = "";
					string Port = "";
					string Frm = "";
					string Host = "";
					bool SSL = false;
					obj.devolver(ref idEmail, ref Username, ref Passw, ref Port, ref Frm, ref Host, ref SSL);
					if (Username.Length == 0)
					{
						result = false;
					}
					else
					{
						SmtpClient smtpClient = new SmtpClient();
						MailMessage mailMessage = new MailMessage();
						if (SSL)
						{
							smtpClient.EnableSsl = true;
						}
						smtpClient.Credentials = new NetworkCredential(Username, Passw);
						smtpClient.Port = Conversions.ToInteger(Port);
						smtpClient.Host = Host;
						mailMessage = new MailMessage();
						mailMessage.From = new MailAddress(Frm);
						smtpClient.Timeout = 20000;
						mailMessage.Subject = Titulo;
						mailMessage.Body = body2;
						string[] array = emailTo.Split(';');
						if (array.Length == 0)
						{
							result = false;
						}
						else
						{
							string[] array2 = array;
							foreach (string text in array2)
							{
								if (text.ToString().Trim().Length > 0)
								{
									mailMessage.To.Add(text.ToString().Trim());
								}
							}
							ServicePointManager.ServerCertificateValidationCallback = [SpecialName] (object s, X509Certificate certificate, X509Chain chain, SslPolicyErrors sslPolicyErrors) => true;
							smtpClient.Send(mailMessage);
							mailMessage.Dispose();
							mailMessage = null;
							result = true;
						}
					}
				}
			}
			else
			{
				new clsLogg().Insertar("Mail", "No Tiene Internet", 0);
				result = false;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			new clsLogg().Insertar("Mail", ex2.Message.ToString().Substring(0, (ex2.Message.Length > 200) ? 200 : ex2.Message.Length), 0);
			Interaction.MsgBox(ex2.ToString());
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public static bool imprimirPagandoConOtrasCuentas(int cuentaID, double Monto, int VisitaID, string Mesa)
	{
		bool result;
		try
		{
			if (configuration.gModo_Remoto | (configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspana) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspanaPanaderia))
			{
				result = false;
			}
			else
			{
				ctlConfiguraciones ctlConfiguraciones2 = new ctlConfiguraciones();
				if (ctlConfiguraciones2.ImprimeOtrasCuentas())
				{
					if (!((cuentaID > 4) & (Monto > 0.0)))
					{
						result = false;
					}
					else
					{
						new ctlConfiguraciones();
						ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
						bool encontro = false;
						string text = "";
						text = ctlImpresoras2.DevolverImprimirFacturaFisico();
						if (Operators.CompareString(text, "", TextCompare: false) == 0)
						{
							text = ctlImpresoras2.devolverImpresoraCuentaFisico();
						}
						PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, text, ref encontro, "RestotechComprobante");
						if (!encontro)
						{
							Interaction.MsgBox("No puedo encontrar la impresora " + text);
							result = false;
						}
						else
						{
							PrinterClass printerClass2 = printerClass;
							string text2 = new ctlCuentas().DevolverCuenta(ref cuentaID);
							if (text2.Length == 0)
							{
								result = false;
							}
							else
							{
								printerClass2.AlignCenter();
								printerClass2.BigFont();
								printerClass2.WriteLine("COBRADO CON");
								printerClass2.Bold = true;
								printerClass2.WriteLine(text2.ToUpper());
								printerClass2.Bold = false;
								printerClass2.AlignLeft();
								printerClass2.NormalBiggerFont();
								printerClass2.FeedPaper(1);
								if (Mesa.Length > 0)
								{
									printerClass2.Bold = true;
									printerClass2.WriteLine(Mesa);
									printerClass2.Bold = false;
								}
								printerClass2.WriteLine("Fecha: " + VariableGeneral.ArmarFechaSTR(DateAndTime.Now));
								printerClass2.WriteLine("Monto: " + Monto.ToString("#,###.##"));
								if (VisitaID > 0)
								{
									printerClass2.WriteLine("CuentaId: " + Conversions.ToString(VisitaID));
								}
								if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.UgosPizza) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vikingo))
								{
									printerClass2.AlignCenter();
									printerClass2.NormalBiggerFont();
									printerClass2.WriteLine(ctlConfiguraciones2.devolverDescripcion());
								}
								printerClass2.FeedPaper(1);
								printerClass2.CutPaper();
								printerClass2.EndDoc();
								result = true;
							}
						}
					}
				}
				else
				{
					result = false;
				}
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

	public static bool printComandas(System.Data.DataTable dgvPedido, bool aumentoEnCuenta, int NroOrden, string PersonaQueRecoge, bool paraLlevar, string NombreMesero, string lblMesa, string txtMesa, bool imprimir, int VisitaId, string Direccion, [Optional][DefaultParameterValue("")] ref string error1)
	{
		checked
		{
			bool result;
			try
			{
				dgvPedido.AcceptChanges();
				System.Data.DataTable dataTable = dgvPedido.Copy();
				foreach (object row in dataTable.Rows)
				{
					object objectValue = RuntimeHelpers.GetObjectValue(row);
					if (Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null)), "")).Contains(" |- "))
					{
						object[] obj = new object[2] { "Producto", null };
						object instance = NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null);
						object[] obj2 = new object[2] { 0, null };
						object instance3;
						object instance2 = (instance3 = NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null));
						object[] array = new object[1];
						object obj3 = (array[0] = " |- ");
						obj2[1] = NewLateBinding.LateGet(instance2, null, "IndexOf", array, null, null, null);
						object[] array2 = obj2;
						bool[] obj4 = new bool[2] { false, true };
						bool[] array3 = obj4;
						object obj5 = NewLateBinding.LateGet(instance, null, "Substring", obj2, null, null, obj4);
						if (array3[1])
						{
							NewLateBinding.LateSetComplex(instance3, null, "IndexOf", new object[2]
							{
								obj3,
								array2[1]
							}, null, null, OptimisticSet: true, RValueBase: true);
						}
						obj[1] = obj5;
						NewLateBinding.LateIndexSet(objectValue, obj, null);
					}
				}
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElSolar) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini))
				{
					result = printComandasSolarZucchini(dataTable, aumentoEnCuenta, NroOrden, PersonaQueRecoge, paraLlevar, NombreMesero, lblMesa, txtMesa, imprimir, ref error1);
				}
				else if (dataTable.Rows.Count == 0)
				{
					result = true;
				}
				else
				{
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo)
					{
						try
						{
							MyProject.Computer.Audio.Play(Directory.GetCurrentDirectory() + "/beep.wav");
							MyProject.Computer.Audio.Play(Directory.GetCurrentDirectory() + "/beep.wav");
							MyProject.Computer.Audio.Play(Directory.GetCurrentDirectory() + "/beep.wav");
						}
						catch (Exception ex)
						{
							ProjectData.SetProjectError(ex);
							Exception ex2 = ex;
							ProjectData.ClearProjectError();
						}
					}
					ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
					ctlProductos ctlProductos2 = new ctlProductos();
					if (configuration.gCombosSeimprimenComoItem)
					{
						int num = dataTable.Rows.Count - 1;
						for (int i = 0; i <= num; i++)
						{
							DataRow dataRow = dataTable.Rows[i];
							if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataRow["ProductosCombo"]), "").ToString().Length <= 0)
							{
								continue;
							}
							string[] array4 = dataRow["ProductosCombo"].ToString().Split(',');
							foreach (string obj6 in array4)
							{
								ctlProductos ctlProductos3 = new ctlProductos();
								string[] array5 = obj6.ToString().Split('-');
								bool flag = true;
								bool flag2 = false;
								if (array5.Length > 1 && Conversions.ToDouble(array5[1]) == 0.0)
								{
									flag = false;
								}
								if (array5.Length > 2 && Conversions.ToDouble(array5[2]) == 0.0)
								{
									flag2 = true;
								}
								if (flag)
								{
									ctlProductos3.SetProductoID(Conversions.ToInteger(array5[0]));
									string impresoraFisica = "";
									ctlProductos3.cargarDatosCombo(ref impresoraFisica);
									DataRow dataRow2 = dataTable.NewRow();
									dataRow2["Producto"] = ctlProductos3.getNombre();
									if (flag2)
									{
										dataRow2["Cantidad"] = VariableGeneral.NZ(Operators.MultiplyObject(dataRow["Cantidad"], -1), 0).ToString();
									}
									else
									{
										dataRow2["Cantidad"] = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataRow["Cantidad"]), 0).ToString();
									}
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia)
									{
										dataRow2["ProductosCombo"] = "9999";
									}
									dataRow2["Impresora"] = "";
									dataRow2["AlmacenID"] = 0;
									dataRow2["ManejarStock"] = 0;
									dataRow2["Precio"] = 0;
									string name = Conversions.ToString(dataRow2["Producto"]);
									DataRow dataRow3;
									double Precio = Conversions.ToDouble((dataRow3 = dataRow2)["Precio"]);
									DataRow dataRow4;
									impresoraFisica = Conversions.ToString((dataRow4 = dataRow2)["Impresora"]);
									DataRow dataRow5;
									bool ManejarStock = Conversions.ToBoolean((dataRow5 = dataRow2)["ManejarStock"]);
									bool conRecipiente = false;
									bool esCombo = false;
									bool esPorPeso = false;
									bool EscogePersonal = false;
									DataRow dataRow6;
									int almacenID = Conversions.ToInteger((dataRow6 = dataRow2)["AlmacenID"]);
									int Sector = 0;
									ctlProductos3.ToReturnProductosPriceImpresoraByName1(0, name, ref Precio, ref impresoraFisica, ref ManejarStock, ref conRecipiente, ref esCombo, ref esPorPeso, ref EscogePersonal, ref almacenID, ref Sector, 0);
									dataRow6["AlmacenID"] = almacenID;
									dataRow5["ManejarStock"] = ManejarStock;
									dataRow4["Impresora"] = impresoraFisica;
									dataRow3["Precio"] = Precio;
									dataRow2["ProductoID"] = ctlProductos3.GetProductoID();
									dataTable.Rows.Add(dataRow2);
								}
							}
							dataRow["ProductosCombo"] = "";
						}
					}
					ctlVisitas ctlVisitas2 = new ctlVisitas();
					if (!imprimir)
					{
						result = false;
					}
					else
					{
						string text = "";
						int num2 = 0;
						bool flag3 = false;
						bool flag4 = false;
						if (flag4)
						{
							int Sector = dataTable.Rows.Count - 1;
							for (int k = 0; k <= Sector; k++)
							{
								dataTable.Rows[k]["Impresora"] = Operators.ConcatenateObject(dataTable.Rows[k]["Impresora"], (k + 1).ToString("0#"));
							}
						}
						dataTable.TableName = "Prods";
						DataView defaultView = dataTable.DefaultView;
						defaultView.Sort = "Impresora ASC";
						bool flag5 = false;
						foreach (object item in defaultView)
						{
							object objectValue2 = RuntimeHelpers.GetObjectValue(item);
							if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null), 0, TextCompare: false) && ((Operators.CompareString(text, NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString(), TextCompare: false) != 0) & (NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString().Length > 0)))
							{
								num2++;
								text = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString();
								if (text.Length > 0)
								{
									flag5 = true;
								}
							}
						}
						if (!flag5)
						{
							result = true;
						}
						else
						{
							string[] array6 = new string[num2 * 4 + 1];
							string[] array7 = new string[num2 * 4 + 1];
							num2 = 0;
							text = "";
							foreach (object item2 in defaultView)
							{
								object objectValue3 = RuntimeHelpers.GetObjectValue(item2);
								if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Cantidad" }, null), 0, TextCompare: false) && ((Operators.CompareString(text, NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Impresora" }, null).ToString(), TextCompare: false) != 0) & (NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Impresora" }, null).ToString().Length > 0)))
								{
									string text2 = "";
									text2 = ((!flag4) ? ctlImpresoras2.devolverNombreFisicoPorNombre(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Impresora" }, null).ToString()) : ctlImpresoras2.devolverNombreFisicoPorNombre(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Impresora" }, null).ToString().Substring(0, NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Impresora" }, null).ToString().Length - 2)));
									string[] array8 = text2.ToString().Split(';');
									foreach (string text3 in array8)
									{
										array6[num2] = text3;
										text = NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Impresora" }, null).ToString();
										array7[num2] = NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Impresora" }, null).ToString();
										num2++;
									}
								}
							}
							if (num2 == 0)
							{
								result = false;
							}
							else
							{
								int num3 = 1;
								string text4 = "";
								int num4 = num2 - 1;
								for (int l = 0; l <= num4; l++)
								{
									bool encontro = false;
									if (array6[l].Length <= 0)
									{
										continue;
									}
									PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, array6[l], ref encontro, "Restotech Pedido");
									if (!encontro)
									{
										error1 = "No puedo encontrar la impresora " + array6[l] + " directory path " + MyProject.Application.Info.DirectoryPath;
										continue;
									}
									if (Operators.CompareString(lblMesa, "Para LLevar:", TextCompare: false) == 0)
									{
										lblMesa = "Llevar:";
									}
									PrinterClass printerClass2 = printerClass;
									flag3 = true;
									double num5 = 0.0;
									if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Naoki) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vulcanica))
									{
										printerClass2.FeedPaper(4);
									}
									else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Tuticapa) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.bigBaby))
									{
										printerClass2.FeedPaper(8);
									}
									printerClass2.AlignCenter();
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
									{
										printerClass2.BigFont();
									}
									else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mune))
									{
										printerClass2.NormalBiggerFont();
									}
									else
									{
										printerClass2.MaxFont();
									}
									printerClass2.Bold = true;
									if (configuration.gStyleBoliches1 != configuration.styleBolichesId.DonMiguel)
									{
										if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pagode) & (Operators.CompareString(PersonaQueRecoge, "ENTREGAR", TextCompare: false) == 0))
										{
											text4 = "ENTREGAR";
										}
										else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Naoki)
										{
											text4 = ((!paraLlevar) ? "SALON" : "DELIVERY");
										}
										else if (paraLlevar)
										{
											text4 = ((!configuration.gComidaRapida) ? "PARA LLEVAR" : "PARA LLEVAR");
											if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Dalias15)
											{
												text4 = lblMesa;
												lblMesa = "";
											}
										}
										else if (configuration.gPeluqueria)
										{
											text4 = ((configuration.gStyleBoliches1 != configuration.styleBolichesId.marisaViera) ? "SERVICIO" : "CLIENTE");
											System.Data.DataTable dataTable2 = BD.ConsultaVer("Select Nombre, Apellidos   from Clientes inner join Visitas on visitas.ClienteID  =Clientes.ID where Visitas.ID = " + Conversions.ToString(VisitaId));
											if (dataTable2.Rows.Count > 0)
											{
												text4 = Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable2.Rows[0][0], " "), dataTable2.Rows[0][1]).ToString().ToUpper();
											}
										}
										else if (configuration.gSupermercado)
										{
											text4 = " PEDIDO";
										}
										else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.FormulaFitness)
										{
											printerClass2.GotoSixth(2.0);
											printerClass2.WriteLine("  FORMULA FITNESS");
											printerClass2.FeedPaper(1);
										}
										else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.MHTraining)
										{
											printerClass2.GotoSixth(2.0);
											printerClass2.WriteLine("    MH TRAINING");
											printerClass2.FeedPaper(1);
										}
										else
										{
											text4 = "EN MESA";
										}
										if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY) | paraLlevar)
										{
											ctlVisitas2.SetID(VisitaId);
											int MesaID = 0;
											int ClienteID = 0;
											string impresoraFisica = "";
											ctlVisitas2.llenarclase(ref MesaID, ref ClienteID, ref impresoraFisica);
											if (Operators.CompareString(ctlVisitas2.DevolverTipoEnvio(VisitaId), "-", TextCompare: false) != 0)
											{
												text4 = ctlVisitas2.DevolverTipoEnvio(VisitaId);
											}
										}
										if (aumentoEnCuenta)
										{
											printerClass2.WriteLine(text4 + " - (Aumento)");
										}
										else
										{
											printerClass2.WriteLine(text4);
										}
									}
									if (VariableGeneral.gConCantidadPersonas)
									{
										if (Operators.CompareString(_ImprimiendoObs, "", TextCompare: false) != 0)
										{
											printerClass2.AlignLeft();
											printerClass2.NormalBiggerFont();
											printerClass2.WriteLine(" Cant. Pers: " + _ImprimiendoObs);
											printerClass2.AlignCenter();
											printerClass2.NormalFont();
											printerClass2.WriteLine("");
										}
									}
									else if (configuration.gStyleBoliches1 != configuration.styleBolichesId.KAO && Operators.CompareString(_ImprimiendoObs, "", TextCompare: false) != 0)
									{
										printerClass2.AlignLeft();
										printerClass2.NormalBiggerFont();
										printerClass2.WriteLine(" Obs: " + _ImprimiendoObs);
										printerClass2.AlignCenter();
										printerClass2.NormalFont();
										printerClass2.WriteLine("");
									}
									if (((configuration.gStyleBoliches1 == configuration.styleBolichesId.FastTaste) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CateringSacherCorp)) & (Operators.CompareString(PersonaQueRecoge, "", TextCompare: false) != 0))
									{
										printerClass2.WriteLine(" Curso : " + PersonaQueRecoge);
										printerClass2.WriteLine("");
									}
									printerClass2.AlignLeft();
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.DonMiguel)
									{
										printerClass2.GotoSixth(1.0);
										printerClass2.WriteChars("");
										printerClass2.GotoSixth(1.0);
										printerClass2.WriteChars(" " + NombreMesero);
										printerClass2.GotoSixth(6.0);
										printerClass2.WriteChars(txtMesa);
										printerClass2.WriteLine("");
									}
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.IrishPub)
									{
										NroOrden = 0;
									}
									if (NroOrden > 0)
									{
										printerClass2.Bold = true;
										printerClass2.GotoSixth(1.0);
										if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mune))
										{
											printerClass2.NormalFont();
										}
										else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Tang)
										{
											printerClass2.MaxFont();
										}
										else
										{
											printerClass2.NormalBiggerFont();
										}
										if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Rinconada)
										{
											printerClass2.WriteLine(" Orden: " + Conversions.ToString(NroOrden));
										}
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.DonMiguel)
										{
											printerClass2.WriteLine("");
										}
										printerClass2.Bold = false;
									}
									if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Soboce) & (Operators.CompareString(Direccion, "", TextCompare: false) != 0))
									{
										printerClass2.BigFont();
										printerClass2.GotoSixth(1.0);
										printerClass2.WriteLine("ID Empleado: " + Direccion);
									}
									if ((PersonaQueRecoge.Length > 0) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.FastTaste) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Naoki))
									{
										printerClass2.GotoSixth(1.0);
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
										{
											printerClass2.NormalBiggerFont();
										}
										else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.CheGaucho) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mune))
										{
											printerClass2.smallFont();
										}
										else
										{
											printerClass2.BigFont();
										}
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pagode)
										{
											printerClass2.AlignRight();
											printerClass2.WriteChars(lblMesa + ": " + txtMesa);
											printerClass2.WriteLine("");
											printerClass2.AlignLeft();
										}
										else
										{
											printerClass2.WriteChars("Recoge: " + PersonaQueRecoge);
											printerClass2.WriteLine("");
										}
										if ((txtMesa.Length > 0) & (Operators.CompareString(txtMesa.ToUpper(), PersonaQueRecoge.ToUpper(), TextCompare: false) != 0))
										{
											printerClass2.AlignRight();
											if (Operators.CompareString(lblMesa, text4, TextCompare: false) == 0)
											{
												printerClass2.WriteChars(txtMesa.ToUpper());
												printerClass2.WriteLine("");
											}
											else
											{
												printerClass2.WriteChars(lblMesa + ": " + txtMesa);
												printerClass2.WriteLine("");
											}
											printerClass2.AlignLeft();
										}
									}
									else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Naoki)
									{
										printerClass2.NormalBiggerFont();
										printerClass2.WriteChars(PersonaQueRecoge);
										printerClass2.WriteLine("");
										if (!paraLlevar)
										{
											printerClass2.AlignRight();
											printerClass2.WriteChars(lblMesa + ": " + txtMesa);
											printerClass2.WriteLine("");
											printerClass2.AlignLeft();
										}
									}
									else
									{
										printerClass2.GotoSixth(1.0);
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
										{
											printerClass2.NormalBiggerFont();
										}
										else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mune))
										{
											printerClass2.NormalFont();
										}
										else
										{
											printerClass2.BigFont();
										}
										if (((configuration.gStyleBoliches1 != configuration.styleBolichesId.DonMiguel) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.marisaViera)) && txtMesa.Length > 0)
										{
											printerClass2.AlignRight();
											if (lblMesa.Contains(text4))
											{
												string text5 = lblMesa.Replace(text4, "");
												if (text5.Length == 0)
												{
													printerClass2.WriteChars(txtMesa.ToUpper().Trim());
													printerClass2.WriteLine("");
												}
												else
												{
													printerClass2.WriteChars(text5.Trim() + ": " + txtMesa);
													printerClass2.WriteLine("");
												}
											}
											else
											{
												printerClass2.WriteChars(lblMesa + ": " + txtMesa);
												printerClass2.WriteLine("");
											}
											if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO && Operators.CompareString(_ImprimiendoObs, "", TextCompare: false) != 0)
											{
												printerClass2.AlignLeft();
												printerClass2.NormalBiggerFont();
												printerClass2.WriteLine("Cant. Pers: " + _ImprimiendoObs);
												printerClass2.AlignCenter();
												printerClass2.NormalFont();
											}
											if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.FastTaste) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CateringSacherCorp))
											{
												int ClienteID = txtMesa.Length - 1;
												for (int m = 0; m <= ClienteID; m++)
												{
													if (Operators.CompareString(Conversions.ToString(txtMesa[m]), "\r", TextCompare: false) == 0)
													{
														num3++;
													}
												}
											}
											if (configuration.gStyleBoliches1 == configuration.styleBolichesId.DonMiguel)
											{
												printerClass2.WriteLine("");
											}
											printerClass2.AlignLeft();
										}
									}
									printerClass2.AlignLeft();
									if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Naoki) & (Operators.CompareString(Direccion, "", TextCompare: false) != 0))
									{
										string text6 = "Dir.: ";
										printerClass2.NormalFont();
										printerClass2.DrawLine();
										printerClass2.WriteLine("");
										string text7 = Direccion;
										while (text7.Length > 0)
										{
											if (text7.Length < 30)
											{
												printerClass2.WriteChars(text6 + text7);
												printerClass2.WriteLine("");
												text7 = text7.Remove(0, text7.Length);
											}
											else
											{
												printerClass2.WriteChars(text6 + text7.Substring(0, 30));
												printerClass2.WriteLine("");
												text7 = text7.Remove(0, 30);
											}
											text6 = "";
										}
										printerClass2.WriteLine("");
										printerClass2.DrawLine();
									}
									if (ctlVisitas2.GetParaLlevarID() > 0)
									{
										ctlParaLLevar obj7 = new ctlParaLLevar();
										obj7.SetParaLlevarID(ctlVisitas2.GetParaLlevarID());
										clsParaLLevar clsParaLLevar2 = obj7.LlenarClase();
										string text8 = "";
										text8 = ((clsParaLLevar2._HoraRecoger.Day != DateAndTime.Today.Day) ? clsParaLLevar2._HoraRecoger.ToString("dd/MM/yy HH:mm") : clsParaLLevar2._HoraRecoger.ToString("HH:mm"));
										if (DateTime.Compare(clsParaLLevar2._HoraRecoger, DateAndTime.Now.AddMinutes(30.0)) > 0)
										{
											printerClass2.Bold = true;
											printerClass2.BigSmallerFont();
										}
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bracan)
										{
											printerClass2.Bold = true;
											printerClass2.BigSmallerFont();
											if ((Operators.CompareString(clsParaLLevar2._Direccion, "Recogera en local", TextCompare: false) == 0) | (Operators.CompareString(clsParaLLevar2._Direccion, "", TextCompare: false) == 0))
											{
												printerClass2.WriteLine(clsParaLLevar2._Direccion);
												printerClass2.WriteLine("Hora estimada: " + text8);
											}
											else
											{
												printerClass2.WriteLine("Enviar por Delivery");
												printerClass2.WriteLine("Hora estimada: " + text8);
											}
											printerClass2.WriteLine("");
										}
										else
										{
											printerClass2.WriteChars("Recoge a " + text8);
											printerClass2.WriteLine("");
										}
										printerClass2.Bold = false;
										printerClass2.NormalFont();
									}
									printerClass2.GotoSixth(1.0);
									if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mune))
									{
										printerClass2.NormalFont();
									}
									else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Sabores)
									{
										printerClass2.NormalFont();
									}
									else
									{
										printerClass2.NormalBiggerFont();
									}
									printerClass2.GotoSixth(2.0);
									printerClass2.WriteLine(" " + DateTime.Now.ToString());
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.DonMiguel)
									{
										printerClass2.WriteLine("");
									}
									printerClass2.DrawLine();
									if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Sabores) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mune))
									{
										printerClass2.NormalBiggerFont();
									}
									else
									{
										printerClass2.BigFont();
									}
									int num6 = 230;
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen)
									{
										num6 = 190;
									}
									if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Mandarin1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.NuevaChina) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SanHo))
									{
										printerClass2.GotoSixth(1.0);
										printerClass2.WriteChars("Descripcion");
										printerClass2.GotoSixth(3.5);
										printerClass2.WriteChars("Cant");
										printerClass2.WriteLine("");
										printerClass2.DrawLine();
									}
									else if (configuration.gStyleBoliches1 != configuration.styleBolichesId.DonMiguel)
									{
										printerClass2.GotoSixth(1.0);
										printerClass2.WriteChars("Cant");
										printerClass2.GotoSixth(2.0);
										printerClass2.WriteChars("   Descripcion");
										printerClass2.WriteLine("");
										printerClass2.DrawLine();
									}
									if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Mune) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Acai) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Nectar) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CheGaucho))
									{
										printerClass2.NormalBiggerFont();
									}
									else
									{
										printerClass2.BigFont();
									}
									DataView dataView = defaultView;
									dataView.RowFilter = "Impresora = '" + array7[l] + "'";
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Burshi)
									{
										dataView.Sort = "ProductoID Desc";
									}
									if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Sabores) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jalapenos))
									{
										printerClass2.NormalFont();
									}
									else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.EspigaDeOro)
									{
										printerClass2.MaxFont();
										printerClass2.Bold = true;
									}
									else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Serendipity) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mune))
									{
										printerClass2.smallFont();
									}
									else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Naoki) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bracan))
									{
										printerClass2.NormalBiggerFont();
									}
									System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
									printerClass2.AlignLeft();
									int num7 = 0;
									string text9 = "";
									foreach (object item3 in dataView)
									{
										object objectValue4 = RuntimeHelpers.GetObjectValue(item3);
										if (Operators.ConditionalCompareObjectEqual(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "ProductoID" }, null), 2, TextCompare: false))
										{
											num7 = Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Cantidad" }, null));
											text9 = Conversions.ToString(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null));
										}
										else
										{
											if (!Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Cantidad" }, null), 0, TextCompare: false))
											{
												continue;
											}
											if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Mandarin1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.NuevaChina) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SanHo))
											{
												printerClass2.GotoSixth(3.5);
											}
											else
											{
												printerClass2.GotoSixth(1.0);
											}
											num5 = Conversions.ToDouble(Operators.AddObject(num5, Operators.MultiplyObject(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Cantidad" }, null), NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Precio" }, null))));
											if (((configuration.gStyleBoliches1 == configuration.styleBolichesId.FastTaste) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CateringSacherCorp)) & (Operators.CompareString(PersonaQueRecoge, "", TextCompare: false) != 0))
											{
												printerClass2.WriteChars("  " + (num3 - 1).ToString("##.##"));
											}
											else
											{
												double num8 = Conversions.ToDouble(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Cantidad" }, null));
												string text10 = NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString().Trim();
												if (!(((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) & text10.All([SpecialName] (char c2) => c2 == '-')) | ((configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO) & (Operators.CompareString(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString(), "ENTRADA", TextCompare: false) == 0))))
												{
													if (text10.All([SpecialName] (char c2) => c2 == '-'))
													{
														printerClass2.Draw2Line();
														continue;
													}
													if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO) & (Operators.CompareString(text10, "FONDO", TextCompare: false) == 0))
													{
														printerClass2.WriteLine("");
													}
													else if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "ProductosCombo" }, null)), "").ToString().Length > 0)
													{
														printerClass2.WriteChars(num8.ToString("##.##") + "x");
													}
													else
													{
														printerClass2.WriteChars(num8.ToString("##.##") ?? "");
													}
												}
											}
											int num9 = 0;
											string text11 = "";
											string text12 = "";
											string text13 = "";
											string text14 = "";
											string text15 = "";
											string text16 = "";
											if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Mandarin1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.NuevaChina) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SanHo))
											{
												ctlProductos2.SetProductoID(Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "ProductoID" }, null)));
												text12 = ctlProductos2.DevolverCodigoXID();
												printerClass2.GotoSixth(1.5);
											}
											else
											{
												NewLateBinding.LateIndexSet(objectValue4, new object[2]
												{
													"Producto",
													NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString()
												}, null);
												while (num9 < NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString().Length)
												{
													text11 += NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString().Substring(num9, 1);
													Size size = TextRenderer.MeasureText(text11, font);
													if (size.Width > num6 * 4)
													{
														text16 += NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString().Substring(num9, 1);
													}
													else if (size.Width > num6 * 3)
													{
														text15 += NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString().Substring(num9, 1);
													}
													else if (size.Width > num6 * 2)
													{
														text14 += NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString().Substring(num9, 1);
													}
													else if (size.Width > num6)
													{
														text13 += NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString().Substring(num9, 1);
													}
													else
													{
														text12 += NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Producto" }, null).ToString().Substring(num9, 1);
													}
													num9++;
												}
												printerClass2.GotoSixth(1.7);
											}
											printerClass2.WriteChars(text12);
											printerClass2.WriteLine("");
											if (text13.Trim().Length > 0)
											{
												printerClass2.GotoSixth(1.7);
												printerClass2.WriteChars(text13.Trim());
												printerClass2.WriteLine("");
											}
											if (text14.Trim().Length > 0)
											{
												printerClass2.GotoSixth(1.7);
												printerClass2.WriteChars(text14.Trim());
												printerClass2.WriteLine("");
											}
											if (text15.Trim().Length > 0)
											{
												printerClass2.GotoSixth(1.7);
												printerClass2.WriteChars(text15.Trim());
												printerClass2.WriteLine("");
											}
											if (text16.Trim().Length > 0)
											{
												printerClass2.GotoSixth(1.7);
												printerClass2.WriteChars(text16.Trim());
												printerClass2.WriteLine("");
											}
											if (dataTable.Columns.Contains("AsistenteID"))
											{
												if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "AsistenteID" }, null)), "").ToString().Length > 0 && Operators.CompareString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "AsistenteID" }, null)), "").ToString(), "0", TextCompare: false) != 0)
												{
													ctlMeseros ctlMeseros2 = new ctlMeseros();
													string[] array9 = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "AsistenteID" }, null)), "").ToString().Split(',');
													if (array9.Length > 0)
													{
														string[] array10 = array9;
														foreach (string text17 in array10)
														{
															if (Operators.CompareString(text17, "", TextCompare: false) != 0)
															{
																ctlMeseros2.SetMeseroID(Conversions.ToInteger(text17));
																string text18 = ctlMeseros2.devolverNombre();
																if (Operators.CompareString(NombreMesero, text18, TextCompare: false) != 0)
																{
																	printerClass2.WriteChars("  --" + text18);
																	printerClass2.WriteLine("");
																}
															}
														}
													}
												}
											}
											else if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "MeseroID" }, null)), "").ToString().Length > 0 && Operators.CompareString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "MeseroID" }, null)), "").ToString(), "0", TextCompare: false) != 0)
											{
												ctlMeseros ctlMeseros3 = new ctlMeseros();
												string[] array11 = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "MeseroID" }, null)), "").ToString().Split(',');
												if (array11.Length > 0)
												{
													string[] array12 = array11;
													foreach (string text19 in array12)
													{
														if (Operators.CompareString(text19, "", TextCompare: false) != 0)
														{
															ctlMeseros3.SetMeseroID(Conversions.ToInteger(text19));
															string text20 = ctlMeseros3.devolverNombre();
															if (Operators.CompareString(NombreMesero, text20, TextCompare: false) != 0)
															{
																printerClass2.WriteChars("  --" + text20);
																printerClass2.WriteLine("");
															}
														}
													}
												}
											}
											if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "ProductosCombo" }, null)), "").ToString().Length > 0)
											{
												string[] array13 = NewLateBinding.LateIndexGet(objectValue4, new object[1] { "ProductosCombo" }, null).ToString().Split(',');
												string text21 = "";
												int num11 = 0;
												int num12 = 0;
												string[] array14 = array13;
												foreach (string text22 in array14)
												{
													if (Operators.CompareString(text21, text22, TextCompare: false) != 0)
													{
														if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) & (Operators.CompareString(text22, "9999", TextCompare: false) == 0))
														{
															text21 = Conversions.ToString(0);
															continue;
														}
														if (num12 > 0)
														{
															ctlProductos ctlProductos4 = new ctlProductos();
															string[] array15 = text21.ToString().Split('-');
															string impresoraFisica2 = "";
															if (array15[0].Length > 0)
															{
																ctlProductos4.SetProductoID(Conversions.ToInteger(array15[0]));
																ctlProductos4.cargarDatosCombo(ref impresoraFisica2);
																if (array15.Length > 1)
																{
																	if (!(configuration.gCombosSeimprimenComoItem & configuration.gEnCombosSeimprimeTodo) && !configuration.gEnCombosSeimprimeTodo && Operators.CompareString(impresoraFisica2, array6[l], TextCompare: false) != 0 && !(impresoraFisica2.ToString().Contains(array6[l].ToString()) & impresoraFisica2.Contains(";")))
																	{
																		array15[1] = "0";
																	}
																	if (Operators.CompareString(array15[1], "1", TextCompare: false) == 0)
																	{
																		printerClass2.GotoSixth(1.2);
																		string text23 = "";
																		text23 = ((Conversions.ToDouble(array15[2]) != 0.0) ? "+" : "-");
																		printerClass2.WriteChars(text23 + Conversions.ToString(num11));
																		printerClass2.GotoSixth(2.0);
																		string nombre = ctlProductos4.getNombre();
																		num9 = 0;
																		text12 = "";
																		text13 = "";
																		text11 = "";
																		for (; num9 < nombre.Length; num9++)
																		{
																			text11 += nombre.Substring(num9, 1);
																			if (TextRenderer.MeasureText(text11, font).Width > num6 - 10)
																			{
																				text13 += nombre.Substring(num9, 1);
																			}
																			else
																			{
																				text12 += nombre.Substring(num9, 1);
																			}
																		}
																		printerClass2.WriteChars(text12);
																		printerClass2.WriteLine("");
																		if (text13.Trim().Length > 0)
																		{
																			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen)
																			{
																				printerClass2.GotoSixth(1.5);
																			}
																			else
																			{
																				printerClass2.GotoSixth(2.0);
																			}
																			printerClass2.WriteChars(text13.Trim());
																			printerClass2.WriteLine("");
																		}
																	}
																}
																else if (array15[0].Length > 0)
																{
																	printerClass2.GotoSixth(1.2);
																	printerClass2.WriteChars("+" + Conversions.ToString(num11));
																	printerClass2.GotoSixth(2.0);
																	string nombre2 = ctlProductos4.getNombre();
																	num9 = 0;
																	text12 = "";
																	text13 = "";
																	text11 = "";
																	for (; num9 < nombre2.Length; num9++)
																	{
																		text11 += nombre2.Substring(num9, 1);
																		if (TextRenderer.MeasureText(text11, font).Width > num6 - 10)
																		{
																			text13 += nombre2.Substring(num9, 1);
																		}
																		else
																		{
																			text12 += nombre2.Substring(num9, 1);
																		}
																	}
																	printerClass2.WriteChars(text12);
																	printerClass2.WriteLine("");
																	if (text13.Trim().Length > 0)
																	{
																		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen)
																		{
																			printerClass2.GotoSixth(1.5);
																		}
																		else
																		{
																			printerClass2.GotoSixth(2.0);
																		}
																		printerClass2.WriteChars(text13.Trim());
																		printerClass2.WriteLine("");
																	}
																}
															}
														}
														text21 = text22;
														num11 = 1;
													}
													else
													{
														num11++;
													}
													num12++;
												}
												ctlProductos ctlProductos5 = new ctlProductos();
												string impresoraFisica3 = "";
												string[] array16 = text21.ToString().Split('-');
												ctlProductos5.SetProductoID(Conversions.ToInteger(array16[0]));
												ctlProductos5.cargarDatosCombo(ref impresoraFisica3);
												if (array16.Length > 1)
												{
													if (!(configuration.gCombosSeimprimenComoItem & configuration.gEnCombosSeimprimeTodo) && !configuration.gEnCombosSeimprimeTodo && Operators.CompareString(impresoraFisica3, array6[l], TextCompare: false) != 0 && !(impresoraFisica3.ToString().Contains(array6[l].ToString()) & impresoraFisica3.Contains(";")))
													{
														array16[1] = "0";
													}
													if (Operators.CompareString(array16[1], "1", TextCompare: false) == 0)
													{
														printerClass2.GotoSixth(1.2);
														string text24 = "";
														text24 = ((Conversions.ToDouble(array16[2]) != 0.0) ? "+" : "-");
														printerClass2.WriteChars(text24 + Conversions.ToString(num11));
														printerClass2.GotoSixth(2.0);
														string nombre3 = ctlProductos5.getNombre();
														num9 = 0;
														text12 = "";
														text13 = "";
														text11 = "";
														for (; num9 < nombre3.Length; num9++)
														{
															text11 += nombre3.Substring(num9, 1);
															if (TextRenderer.MeasureText(text11, font).Width > num6 - 10)
															{
																text13 += nombre3.Substring(num9, 1);
															}
															else
															{
																text12 += nombre3.Substring(num9, 1);
															}
														}
														printerClass2.WriteChars(text12);
														printerClass2.WriteLine("");
														if (text13.Trim().Length > 0)
														{
															if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen)
															{
																printerClass2.GotoSixth(1.5);
															}
															else
															{
																printerClass2.GotoSixth(2.0);
															}
															printerClass2.WriteChars(text13.Trim());
															printerClass2.WriteLine("");
														}
													}
												}
												else if (Conversions.ToDouble(text21) != 0.0)
												{
													printerClass2.GotoSixth(1.2);
													printerClass2.WriteChars("+" + Conversions.ToString(num11));
													printerClass2.GotoSixth(2.0);
													string nombre4 = ctlProductos5.getNombre();
													num9 = 0;
													text12 = "";
													text13 = "";
													text11 = "";
													for (; num9 < nombre4.Length; num9++)
													{
														text11 += nombre4.Substring(num9, 1);
														if (TextRenderer.MeasureText(text11, font).Width > num6 - 10)
														{
															text13 += nombre4.Substring(num9, 1);
														}
														else
														{
															text12 += nombre4.Substring(num9, 1);
														}
													}
													printerClass2.WriteChars(text12);
													printerClass2.WriteLine("");
													if (text13.Trim().Length > 0)
													{
														if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen)
														{
															printerClass2.GotoSixth(1.5);
														}
														else
														{
															printerClass2.GotoSixth(2.0);
														}
														printerClass2.GotoSixth(2.0);
														printerClass2.WriteChars(text13.Trim());
														printerClass2.WriteLine("");
													}
												}
											}
											if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Observaciones" }, null)), "").ToString().Length > 0)
											{
												if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
												{
													printerClass2._FontSize += 2.0;
													printerClass2.Bold = true;
												}
												printerClass2.GotoSixth(1.5);
												string text25 = NewLateBinding.LateIndexGet(objectValue4, new object[1] { "Observaciones" }, null).ToString();
												if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO)
												{
													printerClass2.GotoSixth(2.0);
													text25 = "(" + text25 + ")";
												}
												string[] array17 = ("**" + text25).Split(new string[3]
												{
													Environment.NewLine,
													"\n",
													"\r"
												}, StringSplitOptions.None);
												foreach (string obj8 in array17)
												{
													string text26 = "-";
													string impresoraFisica = obj8;
													for (int num15 = 0; num15 < impresoraFisica.Length; num15++)
													{
														char c = impresoraFisica[num15];
														if (TextRenderer.MeasureText(text26 + Conversions.ToString(c), font).Width > num6 - 10)
														{
															printerClass2.WriteLine(text26);
															text26 = "  " + c;
														}
														else
														{
															text26 += Conversions.ToString(c);
														}
													}
													if (Operators.CompareString(text26, "", TextCompare: false) != 0)
													{
														printerClass2.WriteLine(text26);
													}
												}
												if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
												{
													printerClass2._FontSize -= 2.0;
													printerClass2.Bold = false;
												}
											}
											if (!((configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Vulcanica) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bravissimo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jalapenos)))
											{
												printerClass2.DrawLine();
											}
										}
									}
									printerClass2.GotoSixth(1.0);
									if (num7 > 0)
									{
										printerClass2.GotoSixth(1.0);
										printerClass2.WriteChars("  " + num7);
										printerClass2.GotoSixth(2.0);
										printerClass2.WriteChars(text9.ToString());
										printerClass2.WriteLine("");
									}
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
									{
										printerClass2.NormalBiggerFont();
									}
									else if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCortijo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.BuenDia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Mune))
									{
										printerClass2.NormalFont();
									}
									else
									{
										printerClass2.BigFont();
									}
									if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Vulcanica)
									{
										printerClass2.DrawLine();
									}
									printerClass2.GotoSixth(1.0);
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Chaplin)
									{
										System.Data.DataTable dataTable3 = BD.ConsultaVer("SELECT Meseros.Nombre FROM Visitas INNER JOIN  Mesas ON Visitas.MesaID = Mesas.ID INNER JOIN  Meseros ON Mesas.ResponsableID = Meseros.MeseroID WHERE  Visitas.ID = " + Conversions.ToString(VisitaId));
										if (dataTable3.Rows.Count > 0)
										{
											printerClass2.WriteLine("");
											printerClass2.WriteLine("");
											printerClass2.WriteChars(Conversions.ToString(Operators.ConcatenateObject("Resp.: ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0][0]), ""))));
											printerClass2.WriteLine("");
										}
									}
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Texas)
									{
										printerClass2.WriteLine("TOTAL " + num5 + " Bs.");
									}
									if (!configuration.gComidaRapida)
									{
										printerClass2.WriteLine("");
										if (configuration.gStyleBoliches1 != configuration.styleBolichesId.DonMiguel)
										{
											printerClass2.WriteChars("Por: " + NombreMesero);
										}
									}
									else
									{
										printerClass2.WriteLine("");
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bracan)
										{
											printerClass2.WriteLine("Por: " + NombreMesero);
										}
										printerClass2.WriteLine("");
									}
									if (paraLlevar & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Mhinos))
									{
										printerClass2.WriteLine("");
										printerClass2.WriteChars("................................");
									}
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.IrishPub)
									{
										printerClass2.WriteLine("");
										printerClass2.smallFont();
										printerClass2.WriteLine("Id " + Conversions.ToString(VisitaId));
									}
									if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Jardin) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Soboce) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Belen))
									{
										printerClass2.WriteLine("");
										printerClass2.WriteLine("");
										printerClass2.WriteLine("");
										printerClass2.WriteLine(".");
									}
									if (((configuration.gStyleBoliches1 == configuration.styleBolichesId.Tuticapa) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.bigBaby)) && NroOrden > 0)
									{
										printerClass2.Bold = true;
										printerClass2.GotoSixth(1.0);
										printerClass2.NormalBiggerFont();
										printerClass2.WriteLine("");
										printerClass2.WriteChars(" Orden: " + Conversions.ToString(NroOrden));
										printerClass2.WriteLine("");
										printerClass2.Bold = false;
									}
									printerClass2.CutPaper();
									printerClass2.EndDoc();
									printerClass2 = null;
								}
								result = flag3;
							}
						}
					}
				}
			}
			catch (Exception ex3)
			{
				ProjectData.SetProjectError(ex3);
				Exception ex4 = ex3;
				error1 = ex4.Message;
				result = false;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public static void printComandasBatman(System.Data.DataTable dgvPedido, bool aumentoEnCuenta, int NroOrden, string PersonaQueRecoge, bool paraLlevar, string NombreMesero, string lblMesa, string txtMesa, bool imprimir)
	{
		dgvPedido.AcceptChanges();
		System.Data.DataTable dataTable = dgvPedido.Copy();
		if (!imprimir)
		{
			return;
		}
		string left = "";
		int num = 0;
		DataView dataView = new DataView(dataTable);
		dataView.Sort = "Impresora ASC";
		checked
		{
			foreach (object item in dataView)
			{
				object objectValue = RuntimeHelpers.GetObjectValue(item);
				if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null), 0, TextCompare: false) && ((Operators.CompareString(left, NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString(), TextCompare: false) != 0) & (NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString().Length > 0)))
				{
					num++;
					left = NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString();
				}
			}
			string[] array = new string[num * 4 + 1];
			string[] array2 = new string[num * 4 + 1];
			num = 0;
			left = "";
			ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
			foreach (object item2 in dataView)
			{
				object objectValue2 = RuntimeHelpers.GetObjectValue(item2);
				if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null), 0, TextCompare: false) && ((Operators.CompareString(left, NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString(), TextCompare: false) != 0) & (NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString().Length > 0)))
				{
					array[num] = ctlImpresoras2.devolverNombreFisicoPorNombre(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString());
					left = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString();
					array2[num] = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString();
					num++;
				}
			}
			if (num == 0)
			{
				return;
			}
			int num2 = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num2; i++)
			{
				bool encontro = false;
				if (ctlImpresoras2.devolverNombreFisicoPorNombre(dataTable.Rows[i]["Impresora"].ToString()).Length <= 0)
				{
					continue;
				}
				PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, ctlImpresoras2.devolverNombreFisicoPorNombre(Conversions.ToString(dataTable.Rows[i]["Impresora"])), ref encontro, "Restotech Pedido");
				if (!encontro)
				{
					Interaction.MsgBox(Operators.ConcatenateObject("No puedo encontrar la impresora ", dataTable.Rows[i]["Impresora"]));
					continue;
				}
				PrinterClass printerClass2 = printerClass;
				printerClass2.AlignCenter();
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
				{
					printerClass2.BigFont();
				}
				else
				{
					printerClass2.MaxFont();
				}
				printerClass2.Bold = true;
				string text = "";
				text = ((!paraLlevar) ? "Pedido" : ((!configuration.gComidaRapida) ? "Pedido Copia" : "Pedido para llevar"));
				if (aumentoEnCuenta)
				{
					printerClass2.WriteLine(text + " - (Aumento)");
				}
				else
				{
					printerClass2.WriteLine(text);
				}
				if (NroOrden > 0)
				{
					printerClass2.GotoSixth(1.0);
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
					{
						printerClass2.NormalFont();
					}
					else
					{
						printerClass2.NormalBiggerFont();
					}
					printerClass2.WriteChars("Orden: " + Conversions.ToString(NroOrden));
					printerClass2.WriteLine("");
				}
				if (PersonaQueRecoge.Length > 0)
				{
					printerClass2.GotoSixth(1.0);
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
					{
						printerClass2.NormalBiggerFont();
					}
					else
					{
						printerClass2.BigFont();
					}
					printerClass2.WriteChars("Recoge: " + PersonaQueRecoge);
					printerClass2.WriteLine("");
				}
				else
				{
					printerClass2.GotoSixth(1.0);
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
					{
						printerClass2.NormalBiggerFont();
					}
					else
					{
						printerClass2.BigFont();
					}
					if (txtMesa.Length > 0)
					{
						printerClass2.WriteChars(lblMesa + ": " + txtMesa);
						printerClass2.WriteLine("");
					}
				}
				printerClass2.GotoSixth(1.0);
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
				{
					printerClass2.NormalFont();
					printerClass2.WriteLine("Fecha:" + DateTime.Now.ToString());
				}
				else
				{
					printerClass2.NormalBiggerFont();
					printerClass2.WriteLine(DateTime.Now.ToString());
				}
				printerClass2.DrawLine();
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
				{
					printerClass2.NormalBiggerFont();
				}
				else
				{
					printerClass2.BigFont();
				}
				printerClass2.GotoSixth(1.0);
				printerClass2.WriteChars("");
				printerClass2.GotoSixth(1.0);
				printerClass2.WriteChars("Cant");
				printerClass2.GotoSixth(2.0);
				printerClass2.WriteChars("   Descripcion");
				printerClass2.WriteLine("");
				printerClass2.DrawLine();
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
				{
					printerClass2.NormalBiggerFont();
				}
				else
				{
					printerClass2.MaxFont();
				}
				int num3 = 0;
				string text2 = "";
				DataRow dataRow = dataTable.Rows[i];
				if (Operators.ConditionalCompareObjectEqual(dataRow["ProductoID"], 2, TextCompare: false))
				{
					num3 = Conversions.ToInteger(dataRow["Cantidad"]);
					text2 = Conversions.ToString(dataRow["Producto"]);
				}
				else if (Operators.ConditionalCompareObjectGreater(dataRow["Cantidad"], 0, TextCompare: false))
				{
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars(Conversions.ToDouble(dataRow["Cantidad"]).ToString("##.##"));
					printerClass2.GotoSixth(2.0);
					printerClass2.WriteChars("  " + dataRow["Producto"].ToString());
					printerClass2.WriteLine("");
					if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataRow["ProductosCombo"]), "").ToString().Length > 0)
					{
						string[] array3 = dataRow["ProductosCombo"].ToString().Split(',');
						foreach (string obj in array3)
						{
							ctlProductos ctlProductos2 = new ctlProductos();
							string[] array4 = obj.ToString().Split('-');
							if (Operators.CompareString(array4[1], "1", TextCompare: false) == 0)
							{
								ctlProductos2.SetProductoID(Conversions.ToInteger(array4[0]));
								string impresoraFisica = "";
								ctlProductos2.cargarDatosCombo(ref impresoraFisica);
								printerClass2.GotoSixth(1.0);
								printerClass2.WriteLine("  +" + ctlProductos2.getNombre());
							}
						}
					}
					System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
					int num4 = 230;
					if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataRow["Observaciones"]), "").ToString().Length > 0)
					{
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
						{
							printerClass2._FontSize += 2.0;
							printerClass2.Bold = true;
						}
						printerClass2.GotoSixth(1.5);
						string text3 = dataRow["Observaciones"].ToString();
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO)
						{
							printerClass2.GotoSixth(2.0);
							text3 = "(" + text3 + ")";
						}
						string[] array5 = ("**" + text3).Split(new string[3]
						{
							Environment.NewLine,
							"\n",
							"\r"
						}, StringSplitOptions.None);
						foreach (string obj2 in array5)
						{
							string text4 = "-";
							string impresoraFisica = obj2;
							for (int l = 0; l < impresoraFisica.Length; l++)
							{
								char c = impresoraFisica[l];
								if (TextRenderer.MeasureText(text4 + Conversions.ToString(c), font).Width > num4 - 10)
								{
									printerClass2.WriteLine(text4);
									text4 = "  " + c;
								}
								else
								{
									text4 += Conversions.ToString(c);
								}
							}
							if (Operators.CompareString(text4, "", TextCompare: false) != 0)
							{
								printerClass2.WriteLine(text4);
							}
						}
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
						{
							printerClass2._FontSize -= 2.0;
							printerClass2.Bold = false;
						}
					}
				}
				if (num3 > 0)
				{
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars(num3.ToString());
					printerClass2.GotoSixth(2.0);
					printerClass2.WriteChars(text2.ToString());
					printerClass2.WriteLine("");
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
				{
					printerClass2.NormalBiggerFont();
				}
				else
				{
					printerClass2.BigFont();
				}
				printerClass2.DrawLine();
				printerClass2.GotoSixth(1.0);
				if (!configuration.gComidaRapida)
				{
					printerClass2.WriteLine("");
					printerClass2.WriteChars("Por: " + NombreMesero);
				}
				if (paraLlevar)
				{
					printerClass2.WriteLine("");
					printerClass2.WriteChars("................................");
				}
				printerClass2.CutPaper();
				printerClass2.EndDoc();
				printerClass2 = null;
			}
		}
	}

	public static void printComandasSaintGeorge(System.Data.DataTable dgvPedido, bool aumentoEnCuenta, int NroOrden, string PersonaQueRecoge, bool paraLlevar, string NombreMesero, string lblMesa, string txtMesa, bool imprimir, int visitaID)
	{
		dgvPedido.AcceptChanges();
		System.Data.DataTable dataTable = dgvPedido.Copy();
		if (!imprimir)
		{
			return;
		}
		string left = "";
		int num = 0;
		DataView dataView = new DataView(dataTable);
		dataView.Sort = "Impresora ASC";
		checked
		{
			foreach (object item in dataView)
			{
				object objectValue = RuntimeHelpers.GetObjectValue(item);
				if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null), 0, TextCompare: false) && ((Operators.CompareString(left, NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString(), TextCompare: false) != 0) & (NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString().Length > 0)))
				{
					num++;
					left = NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString();
				}
			}
			string[] array = new string[num * 4 + 1];
			string[] array2 = new string[num * 4 + 1];
			num = 0;
			left = "";
			ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
			foreach (object item2 in dataView)
			{
				object objectValue2 = RuntimeHelpers.GetObjectValue(item2);
				if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null), 0, TextCompare: false) && ((Operators.CompareString(left, NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString(), TextCompare: false) != 0) & (NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString().Length > 0)))
				{
					array[num] = ctlImpresoras2.devolverNombreFisicoPorNombre(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString());
					left = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString();
					array2[num] = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString();
					num++;
				}
			}
			if (num == 0)
			{
				return;
			}
			byte b = 0;
			int num2 = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num2; i++)
			{
				byte b2 = Conversions.ToByte(Operators.SubtractObject(dataTable.Rows[i]["Cantidad"], 1));
				b = 0;
				while (unchecked((uint)b <= (uint)b2))
				{
					bool encontro = false;
					if (ctlImpresoras2.devolverNombreFisicoPorNombre(dataTable.Rows[i]["Impresora"].ToString()).Length > 0)
					{
						PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, ctlImpresoras2.devolverNombreFisicoPorNombre(Conversions.ToString(dataTable.Rows[i]["Impresora"])), ref encontro, "Restotech Pedido");
						if (!encontro)
						{
							Interaction.MsgBox(Operators.ConcatenateObject("No puedo encontrar la impresora ", dataTable.Rows[i]["Impresora"]));
						}
						else
						{
							PrinterClass printerClass2 = printerClass;
							printerClass2.AlignCenter();
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Pupis)
							{
								printerClass2.BigFont();
							}
							else
							{
								printerClass2.MaxFont();
							}
							printerClass2.Bold = true;
							string text = "ENTRE MASAS";
							if (aumentoEnCuenta)
							{
								printerClass2.WriteLine(text + " - (Aumento)");
							}
							else
							{
								printerClass2.WriteLine(text);
							}
							printerClass2.WriteLine("");
							printerClass2.AlignLeft();
							printerClass2.NormalFont();
							printerClass2.WriteChars("Cuenta: " + visitaID);
							System.Data.DataTable dataTable2 = BD.ConsultaVer("Select Nombre, Apellidos, nacionalidad from Clientes left join Visitas on visitas.ClienteID  =Clientes.ID where Visitas.ID = " + Conversions.ToString(visitaID));
							if (dataTable2.Rows.Count > 0)
							{
								printerClass2.GotoSixth(4.0);
								printerClass2.WriteChars("Curso: " + dataTable2.Rows[0][2].ToString());
								printerClass2.WriteLine("");
								printerClass2.WriteChars("Cliente: " + dataTable2.Rows[0][0].ToString() + " " + dataTable2.Rows[0][1].ToString());
							}
							printerClass2.WriteLine("");
							printerClass2.GotoSixth(0.0);
							printerClass2.DrawLine();
							printerClass2.NormalBiggerFont();
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars("");
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars("Cant");
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteChars("   Descripcion");
							printerClass2.WriteLine("");
							printerClass2.DrawLine();
							printerClass2.NormalFont10();
							int num3 = 0;
							string text2 = "";
							DataRow dataRow = dataTable.Rows[i];
							if (Operators.ConditionalCompareObjectEqual(dataRow["ProductoID"], 2, TextCompare: false))
							{
								num3 = Conversions.ToInteger(dataRow["Cantidad"]);
								text2 = Conversions.ToString(dataRow["Producto"]);
							}
							else if (Operators.ConditionalCompareObjectGreater(dataRow["Cantidad"], 0, TextCompare: false))
							{
								printerClass2.GotoSixth(1.0);
								printerClass2.WriteChars(1.0.ToString("##.##"));
								printerClass2.GotoSixth(1.5);
								int j = 0;
								string text3 = "";
								string text4 = "";
								string text5 = "";
								int num4 = 230;
								System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
								dataRow["Producto"] = dataRow["Producto"].ToString();
								for (; j < dataRow["Producto"].ToString().Length; j++)
								{
									text3 += dataRow["Producto"].ToString().Substring(j, 1);
									if (TextRenderer.MeasureText(text3, font).Width > num4)
									{
										text5 += dataRow["Producto"].ToString().Substring(j, 1);
									}
									else
									{
										text4 += dataRow["Producto"].ToString().Substring(j, 1);
									}
								}
								printerClass2.WriteChars(text4);
								printerClass2.WriteLine("");
								if (text5.Trim().Length > 0)
								{
									printerClass2.GotoSixth(1.5);
									printerClass2.WriteChars(text5.Trim());
									printerClass2.WriteLine("");
								}
								if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataRow["ProductosCombo"]), "").ToString().Length > 0)
								{
									string[] array3 = dataRow["ProductosCombo"].ToString().Split(',');
									foreach (string obj in array3)
									{
										ctlProductos ctlProductos2 = new ctlProductos();
										string[] array4 = obj.ToString().Split('-');
										if (Operators.CompareString(array4[1], "1", TextCompare: false) == 0)
										{
											ctlProductos2.SetProductoID(Conversions.ToInteger(array4[0]));
											string impresoraFisica = "";
											ctlProductos2.cargarDatosCombo(ref impresoraFisica);
											printerClass2.GotoSixth(1.0);
											printerClass2.WriteLine("  +" + ctlProductos2.getNombre());
										}
									}
								}
								if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataRow["Observaciones"]), "").ToString().Length > 0)
								{
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
									{
										printerClass2._FontSize += 2.0;
										printerClass2.Bold = true;
									}
									printerClass2.GotoSixth(1.5);
									string text6 = dataRow["Observaciones"].ToString();
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO)
									{
										printerClass2.GotoSixth(2.0);
										text6 = "(" + text6 + ")";
									}
									string[] array5 = ("**" + text6).Split(new string[3]
									{
										Environment.NewLine,
										"\n",
										"\r"
									}, StringSplitOptions.None);
									foreach (string obj2 in array5)
									{
										string text7 = "-";
										string impresoraFisica = obj2;
										for (int m = 0; m < impresoraFisica.Length; m++)
										{
											char c = impresoraFisica[m];
											if (TextRenderer.MeasureText(text7 + Conversions.ToString(c), font).Width > num4 - 10)
											{
												printerClass2.WriteLine(text7);
												text7 = "  " + c;
											}
											else
											{
												text7 += Conversions.ToString(c);
											}
										}
										if (Operators.CompareString(text7, "", TextCompare: false) != 0)
										{
											printerClass2.WriteLine(text7);
										}
									}
									if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
									{
										printerClass2._FontSize -= 2.0;
										printerClass2.Bold = false;
									}
								}
							}
							if (num3 > 0)
							{
								printerClass2.GotoSixth(1.0);
								printerClass2.WriteChars(num3.ToString());
								printerClass2.GotoSixth(2.0);
								printerClass2.WriteChars(text2.ToString());
								printerClass2.WriteLine("");
							}
							printerClass2.DrawLine();
							printerClass2.AlignCenter();
							printerClass2.NormalFont();
							printerClass2.WriteLine("");
							printerClass2.WriteLine("GRACIAS POR SU COMPRA");
							printerClass2.CutPaper();
							printerClass2.EndDoc();
							printerClass2 = null;
						}
					}
					b = (byte)unchecked((uint)(b + 1));
				}
			}
		}
	}

	public static List<string> DividirFrasePorAnchoFont(string frase, int anchoEnPixeles, System.Drawing.Font fuente)
	{
		string[] array = frase.Split(' ');
		List<string> list = new List<string>();
		string text = "";
		using (Graphics graphics = Graphics.FromHwnd(IntPtr.Zero))
		{
			string[] array2 = array;
			foreach (string text2 in array2)
			{
				if (graphics.MeasureString(text + " " + text2, fuente).Width <= (float)anchoEnPixeles)
				{
					text = text + " " + text2;
					continue;
				}
				list.Add(text.Trim());
				text = text2;
			}
		}
		if (!string.IsNullOrWhiteSpace(text))
		{
			list.Add(text.Trim());
		}
		return list;
	}

	public static string printFacturaNewFormat(int nroFactura, string miNIT, string nombre, string nit, string autorizacion, int _NroOrden, int visitaID, int AgruparPagoID, string MonedaString, double monto, double IceMonto, string codigo, string _fechaLimiteEmision, DateTime fecha, bool esCopia, string Impresora, System.Data.DataTable dtProdsAFacturar, bool facturandoAnticipadamente, double cambio, bool ImprimirEnArchivo, string Observacion, double AcumuladoPago, int meseroID, string leyGen, bool esContingencia, int CodigoPuntoVenta, string visitas, int DocumentoSector)
	{
		checked
		{
			string result;
			try
			{
				int num = configuration.gTipoFacturacion;
				if (((DateTime.Compare(fecha, DateAndTime.Today) < 0) & (configuration.gTipoFacturacion == 2)) && ((autorizacion.Length > 0) & (codigo.Length < 18)))
				{
					num = 1;
				}
				double num2 = 7.5;
				double font = 6.5;
				bool flag = false;
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.AguaViva1)
				{
					num2 = 8.5;
					font = 7.5;
				}
				else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Fuego)
				{
					num2 = 6.0;
					font = 6.0;
					flag = true;
				}
				else
				{
					num2 = 7.5;
				}
				DateTime now = DateAndTime.Now;
				ctlConfiguraciones ctlConfiguraciones2 = new ctlConfiguraciones();
				new ctlImpresoras();
				bool encontro = false;
				PrinterClass printerClass;
				if (ImprimirEnArchivo)
				{
					string text = "";
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.bigBaby) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspanaPanaderia) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaGo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Catalinda))
					{
						text = "2";
					}
					Impresora = "FacturaPDF" + text;
					printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, Impresora, ref encontro, Conversions.ToString(nroFactura) + "-" + codigo, flag);
				}
				else
				{
					printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, Impresora, ref encontro, Conversions.ToString(nroFactura) + "-" + codigo, flag);
				}
				if (!encontro)
				{
					result = "No puedo encontrar la impresora " + Impresora;
				}
				else
				{
					PrinterClass printerClass2 = printerClass;
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Distinto)
					{
						printerClass2.PrintLogo();
					}
					printerClass2.AlignCenter();
					printerClass2.setFont(num2);
					printerClass2.Bold = false;
					string empresa = "";
					string sucursal = "";
					string direccion = "";
					string municipio = "";
					string telefono = "";
					string dueño = "";
					string email = "";
					string actividadEconomica = "";
					string comentario = "";
					string ley = "";
					bool soloAdminBorra = false;
					bool cant = false;
					int idconfiguracion = default(int);
					bool conporcentaje = default(bool);
					double porcentaje = default(double);
					bool DatosFacturas = default(bool);
					bool DobleFactura = default(bool);
					bool FacturaBackupArchivo = default(bool);
					bool FacturaExpress = default(bool);
					ctlConfiguraciones2.devolver(ref idconfiguracion, ref conporcentaje, ref porcentaje, ref empresa, ref sucursal, ref direccion, ref telefono, ref dueño, ref email, ref actividadEconomica, ref comentario, ref ley, ref soloAdminBorra, ref cant, ref DatosFacturas, ref DobleFactura, ref FacturaBackupArchivo, ref FacturaExpress, ref municipio);
					if (leyGen.Length > 0)
					{
						ley = leyGen;
					}
					string[] array = empresa.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string text2 in array)
					{
						printerClass2.WriteLine(text2.Trim());
					}
					if (dueño.Length > 0)
					{
						string[] array2 = ("De:" + dueño).Split(new string[3]
						{
							Environment.NewLine,
							"\n",
							"\r"
						}, StringSplitOptions.None);
						foreach (string text3 in array2)
						{
							printerClass2.WriteLine(text3.Trim());
						}
					}
					printerClass2.WriteLine(sucursal);
					if (num == 2)
					{
						printerClass2.WriteLine("No. Punto de Venta " + Conversions.ToString(CodigoPuntoVenta));
					}
					string[] array3 = direccion.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string text4 in array3)
					{
						printerClass2.WriteLine(text4.Trim());
					}
					if (telefono.Length > 1)
					{
						printerClass2.WriteLine("Telefono:" + telefono);
					}
					if (municipio.Length > 1)
					{
						printerClass2.WriteLine(municipio);
					}
					if (num == 1)
					{
						printerClass2.WriteLine("SCF 1");
					}
					ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
					System.Data.DataTable dataTable = new System.Data.DataTable();
					double num3 = 0.0;
					num3 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(pagos.MontoBs) as MontoGiftCard ", " (Pagos inner join Cuentas on Cuentas.CuentaID = Pagos.CuentaID) inner join DetalleCuenta on DetalleCuenta.id = Pagos.DetalleCuentaID", "Cuentas.EsGiftCard = " + VariableGeneral.armarBolean(1) + " and DetalleCuenta.VisitaID  = " + Conversions.ToString(visitaID)).Rows[0][0]), 0));
					if (num3 > monto)
					{
						num3 = monto;
					}
					if (dtProdsAFacturar == null)
					{
						if (visitaID > 0)
						{
							dataTable = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionXML(visitaID, esContingencia, paraXml: false, DocumentoSector);
						}
						else if (AgruparPagoID > 0)
						{
							dataTable = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(AgruparPagoID, DocumentoSector);
						}
					}
					else
					{
						dataTable = dtProdsAFacturar;
					}
					printerClass2.Bold = true;
					printerClass2.AlignCenter();
					string text5 = "FACTURA";
					string text6 = "";
					string text7 = "(Con Derecho a Crédito Fiscal)";
					if (DocumentoSector == 8)
					{
						text5 = "FACTURA TASA CERO";
						if (dataTable.Rows.Count > 0)
						{
							text6 = ((!dataTable.Rows[0]["Producto"].ToString().ToUpper().Contains("TRANS")) ? "VENTA DE LIBROS" : "TRANSPORTE DE CARGA INTERNACIONAL");
						}
						text7 = "(Sin Derecho a Crédito Fiscal)";
					}
					printerClass2.WriteLine(text5);
					if (text6.Length > 0)
					{
						printerClass2.WriteLine(text6);
					}
					printerClass2.setFont(font);
					printerClass2.WriteLine(text7);
					printerClass2.setFont(num2);
					printerClass2.WriteLine("");
					printerClass2.Bold = false;
					string text8 = "";
					text8 = ((!((configuration.gStyleBoliches1 == configuration.styleBolichesId.PollosBatman) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Aerocruz))) ? " " : "  ");
					printerClass2.AlignLeft();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteLine(text8 + "NIT: " + miNIT);
					printerClass2.WriteLine(text8 + "Nº DE FACTURA: " + Conversions.ToString(nroFactura));
					if (num == 1)
					{
						printerClass2.WriteLine(text8 + "COD. AUTORIZACION: " + autorizacion.ToString());
					}
					else
					{
						try
						{
							string text9 = "COD. AUTORIZACION: " + codigo.Substring(0, 10);
							string text10 = codigo.Substring(10, codigo.Length - 30);
							int startIndex = 10 + text10.Length;
							int length = codigo.Length - 10 - text10.Length;
							string text11 = codigo.Substring(startIndex, length);
							printerClass2.WriteLine(text8 + text9);
							printerClass2.WriteLine(text8 + text10);
							printerClass2.WriteLine(text8 + text11);
						}
						catch (Exception ex)
						{
							ProjectData.SetProjectError(ex);
							Exception ex2 = ex;
							_ = "COD. AUTORIZACION: " + codigo;
							ProjectData.ClearProjectError();
						}
					}
					if (flag)
					{
						printerClass2.WriteLine("_____________________________________________________");
					}
					else
					{
						printerClass2.DrawLine();
					}
					printerClass2.WriteLine("");
					if (num == 1)
					{
						printerClass2.WriteLine(text8 + "Actividad Económica: " + actividadEconomica);
						printerClass2.WriteLine("");
					}
					printerClass2.WriteLine(text8 + "Fecha: " + Conversions.ToString(fecha));
					int num4 = 0;
					string text12 = "";
					text12 = ((num != 1) ? (text8 + "Nombre/Razón Social: " + nombre) : (text8 + "Señor(es): " + nombre));
					System.Drawing.Font font2 = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
					int anchoEnPixeles = 250;
					List<string> list = DividirFrasePorAnchoFont(text12, anchoEnPixeles, font2);
					foreach (string item in list)
					{
						printerClass2.WriteLine(text8 + item);
					}
					ctlVisitas ctlVisitas2 = new ctlVisitas();
					ctlVisitas2.SetID(visitaID);
					if (num == 1)
					{
						printerClass2.WriteLine(text8 + "NIT/CI: " + nit);
					}
					else
					{
						printerClass2.WriteLine(text8 + "NIT/CI/CEX: " + nit);
						string codigoCliente = ctlVisitas2.getCodigoCliente();
						if (codigoCliente.ToString().Length > 0)
						{
							printerClass2.WriteLine(text8 + "COD. CLIENTE: " + codigoCliente);
						}
						else
						{
							printerClass2.WriteLine(text8 + "COD. CLIENTE: " + nit);
						}
					}
					if (!configuration.gSupermercado)
					{
						printerClass2.DrawLine();
						if (_NroOrden > 0)
						{
							printerClass2.NormalBiggerFont();
							printerClass2.Bold = true;
							printerClass2.WriteLine(text8 + "ORDEN: " + Conversions.ToString(_NroOrden));
						}
					}
					else
					{
						printerClass2.WriteLine("");
						if (flag)
						{
							printerClass2.WriteLine("_____________________________________________________");
						}
						else
						{
							printerClass2.DrawLine();
						}
					}
					printerClass2.setFont(num2);
					printerClass2.Bold = false;
					if (num == 1)
					{
						if (flag)
						{
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars(text8 + "CONCEP.");
							printerClass2.GotoSixth(2.5);
							printerClass2.WriteChars(text8 + "CANT.");
							printerClass2.GotoSixth(3.5);
							printerClass2.WriteChars(text8 + "P.UNIT");
							printerClass2.GotoSixth(4.9);
							printerClass2.WriteChars(text8 + "TOTAL");
						}
						else
						{
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars(text8 + "CONCEPTO");
							printerClass2.GotoSixth(2.8);
							printerClass2.WriteChars(text8 + "CANT.");
							printerClass2.GotoSixth(3.8);
							printerClass2.WriteChars(text8 + "P.UNIT");
							printerClass2.GotoSixth(5.3);
							printerClass2.WriteChars(text8 + "TOTAL");
						}
					}
					else
					{
						printerClass2.GotoSixth(3.3);
						printerClass2.Bold = true;
						printerClass2.WriteChars(text8 + "DETALLE");
						printerClass2.Bold = false;
					}
					printerClass2.WriteLine("");
					if (flag)
					{
						printerClass2.WriteLine("_____________________________________________________");
					}
					else
					{
						printerClass2.DrawLine();
					}
					double num5 = 0.0;
					double num6 = 0.0;
					double num7 = 0.0;
					if (AgruparPagoID > 0 && dataTable.Columns.Contains("Pagando"))
					{
						if (dataTable.Rows.Count > 0)
						{
							int num8 = dataTable.Rows.Count - 1;
							for (int l = 0; l <= num8; l++)
							{
								num5 += Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[l]["Pagando"]), 0));
							}
						}
						if (num5 == 0.0)
						{
							facturandoAnticipadamente = true;
						}
						num5 = 0.0;
					}
					int cantDecimales = 2;
					bool flag2 = false;
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Allegronet1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.TartinaFactura) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Aerocruz))
					{
						flag2 = true;
					}
					switch (DocumentoSector)
					{
					case 35:
						flag2 = true;
						cantDecimales = 5;
						break;
					case 14:
						cantDecimales = 5;
						break;
					}
					double num9 = 0.0;
					double num10 = 0.0;
					if (dataTable.Rows.Count > 0)
					{
						int num11 = dataTable.Rows.Count - 1;
						int num12 = 0;
						while (true)
						{
							if (num12 <= num11)
							{
								if ((Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Debe"]), 0)) + Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Pago"]), 0)) > 0.0) | flag2)
								{
									if (Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["precio"]), -1)) == -1.0)
									{
										ctlProductos ctlProductos2 = new ctlProductos();
										ctlProductos2.ToReturnIdbyName(dataTable.Rows[num12]["Producto"].ToString());
										ctlProductos2.CargarPrecio();
										dataTable.Rows[num12]["precio"] = ctlProductos2.getPrecio();
									}
									dataTable.Rows[num12]["precio"] = VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["precio"]), cantDecimales);
									dataTable.Rows[num12]["cantidad"] = VariableGeneral.toDecimalSIN(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["cantidad"]), cantDecimales);
									double num13;
									if ((AgruparPagoID > 0) & !facturandoAnticipadamente)
									{
										num5 += Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Pagando"]), 0));
										num6 += Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["precio"]), 0)) * Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Cantidad"]), 0));
										num13 = ((Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Pago"]), 0)) == 0.0) ? 0.0 : (Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Pagando"]), 0)) * Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Cantidad"]), 0)) / (Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Pago"]), 0)) + Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Debe"]), 0)))));
									}
									else
									{
										num5 += Convert.ToDouble(VariableGeneral.toDecimalSIN(Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Debe"]), 0)) + Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Pago"]), 0)), cantDecimales));
										num6 += Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["precio"]), 0), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Cantidad"]), 0)), cantDecimales));
										num7 += Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Debe"]), 0), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Pago"]), 0)), cantDecimales));
										num13 = Conversions.ToDouble(dataTable.Rows[num12]["Cantidad"]);
									}
									printerClass2.GotoSixth(1.0);
									string text13 = "";
									text13 = dataTable.Rows[num12]["Codigo"].ToString() + " - " + dataTable.Rows[num12]["Producto"].ToString();
									double num14 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Precio"]), 0));
									double num15 = Conversions.ToDouble(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Pago"]), 0), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["debe"]), 0)));
									double num16 = num13 * num14 - num15;
									if (!((Operators.CompareString(text13.ToUpper(), "SERVICIO", TextCompare: false) == 0) & (num5 == 0.0)))
									{
										if (num == 1)
										{
											printerClass2.WriteLine(text8 + text13.Trim());
											if (flag)
											{
												printerClass2.GotoSixth(2.0);
											}
											else
											{
												printerClass2.GotoSixth(2.5);
											}
											printerClass2.WriteChars(text8 + num13.ToString("#,##0.#0", CultureInfo.InvariantCulture));
											if (flag)
											{
												printerClass2.GotoSixth(3.5);
											}
											else
											{
												printerClass2.GotoSixth(3.9);
											}
											if (Operators.CompareString(text13.ToUpper(), "SERVICIO", TextCompare: false) == 0)
											{
												num14 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Precio"]), 0));
											}
											double num17 = 0.0;
											if (IceMonto > 0.0)
											{
												num17 = Conversions.ToDouble(Operators.DivideObject(Operators.MultiplyObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["CantidadML"]), 0), VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["ICE_Fijo"]), 0)), 1000));
												num14 -= num17;
											}
											printerClass2.WriteChars(text8 + num14.ToString("#,##0.#0", CultureInfo.InvariantCulture));
											if (flag)
											{
												printerClass2.GotoSixth(4.9);
											}
											else
											{
												printerClass2.GotoSixth(5.3);
											}
											printerClass2.WriteChars(text8 + VariableGeneral.toDecimalSIN(num13 * num14, cantDecimales).ToString("#,##0.#0", CultureInfo.InvariantCulture));
											printerClass2.WriteLine("");
										}
										else
										{
											printerClass2.Bold = true;
											List<string> list2 = DividirFrasePorAnchoFont(text13.Trim(), anchoEnPixeles, font2);
											foreach (string item2 in list2)
											{
												printerClass2.WriteLine(text8 + item2);
											}
											printerClass2.Bold = false;
											printerClass2.WriteLine(text8 + "   Unidad de Medida: " + VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Medida"]), "").ToString());
											try
											{
												if (Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["ManejaSerie"]), false)) && Operators.ConditionalCompareObjectGreater(NewLateBinding.LateGet(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Observacion"]), ""), null, "Length", new object[0], null, null, null), 0, TextCompare: false))
												{
													printerClass2.WriteLine(Conversions.ToString(Operators.ConcatenateObject(text8 + "   Número de serie: ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Observacion"]), ""))));
												}
												if (Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["ManejaImei"]), false)) && Operators.ConditionalCompareObjectGreater(NewLateBinding.LateGet(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Observacion"]), ""), null, "Length", new object[0], null, null, null), 0, TextCompare: false))
												{
													printerClass2.WriteLine(Conversions.ToString(Operators.ConcatenateObject(text8 + "   Número de Imei: ", VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Observacion"]), ""))));
												}
											}
											catch (Exception ex3)
											{
												ProjectData.SetProjectError(ex3);
												Exception ex4 = ex3;
												ProjectData.ClearProjectError();
											}
											if (Operators.CompareString(text13.ToUpper(), "SERVICIO", TextCompare: false) == 0)
											{
												num14 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["Precio"]), 0));
											}
											printerClass2.GotoSixth(1.0);
											double num23;
											double num21;
											double num19;
											double num18;
											double num20;
											double num22;
											double num24;
											switch (DocumentoSector)
											{
											case 35:
												printerClass2.WriteChars(text8 + num13.ToString("#,##0.####0", CultureInfo.InvariantCulture) + " X " + num14.ToString("#,##0.####0", CultureInfo.InvariantCulture) + " - " + num16.ToString("#,##0.####0", CultureInfo.InvariantCulture));
												if (flag)
												{
													printerClass2.GotoSixth(4.9);
												}
												else
												{
													printerClass2.GotoSixth(5.3);
												}
												printerClass2.WriteChars(text8 + VariableGeneral.toDecimalSIN(num13 * num14 - num16, cantDecimales).ToString("#,##0.####0", CultureInfo.InvariantCulture));
												break;
											case 14:
											{
												double num25 = Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.DivideObject(Operators.MultiplyObject(num13, VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["CantidadML"]), 0)), 1000), cantDecimales));
												num23 = Convert.ToDouble(VariableGeneral.toDecimalSIN(Convert.ToDouble(Operators.MultiplyObject(dataTable.Rows[num12]["ICE_Fijo"], num25)), cantDecimales));
												num19 = 0.0;
												num18 = 0.0;
												num22 = 0.0;
												num21 = Convert.ToDouble(RuntimeHelpers.GetObjectValue(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["ICE_porcentual"]), 0)));
												if (!(num21 >= 1.0))
												{
													num19 = Convert.ToDouble(VariableGeneral.toDecimalSIN((num14 - num23 / num13) / (0.87 * num21 + 1.0), cantDecimales));
													goto IL_186b;
												}
												num21 /= 100.0;
												if (!(num16 > 0.0))
												{
													num19 = Convert.ToDouble(VariableGeneral.toDecimalSIN((num14 - num23 / num13) / (0.87 * num21 + 1.0), cantDecimales));
													goto IL_186b;
												}
												num19 = num14;
												decimal value = 0.13m;
												decimal d = 0.01m;
												decimal d2 = new decimal(num15);
												decimal num26 = new decimal(num19);
												int num27 = 0;
												while (true)
												{
													if (num27 >= 100)
													{
														result = Conversions.ToString(Value: false);
														break;
													}
													num18 = Convert.ToDouble(VariableGeneral.toDecimalSIN((num13 * Convert.ToDouble(num26) - num16) * Convert.ToDouble(value), cantDecimales));
													num22 = Convert.ToDouble(VariableGeneral.toDecimalSIN(Convert.ToDouble(VariableGeneral.toDecimalSIN(num13 * Convert.ToDouble(num26) - num16 - num18, cantDecimales)) * num21, cantDecimales));
													decimal d3 = VariableGeneral.toDecimalSIN(num13 * Convert.ToDouble(num26) - num16 + num23 + num22, cantDecimales);
													decimal value2 = decimal.Subtract(d2, d3);
													num26 = new decimal(Convert.ToDouble(num26) + Convert.ToDouble(value2) / num13);
													if (decimal.Compare(Math.Abs(value2), d) > 0)
													{
														continue;
													}
													num19 = Convert.ToDouble(VariableGeneral.toDecimalSIN(num26, cantDecimales));
													goto IL_186b;
												}
												goto end_IL_0000;
											}
											default:
												{
													printerClass2.WriteChars(text8 + num13.ToString("#,##0.#0", CultureInfo.InvariantCulture) + " X " + num14.ToString("#,##0.#0", CultureInfo.InvariantCulture) + " - " + num16.ToString("#,##0.#0", CultureInfo.InvariantCulture));
													if (flag)
													{
														printerClass2.GotoSixth(4.9);
													}
													else
													{
														printerClass2.GotoSixth(5.3);
													}
													printerClass2.WriteChars(text8 + VariableGeneral.toDecimalSIN(num13 * num14 - num16, cantDecimales).ToString("#,##0.#0", CultureInfo.InvariantCulture));
													break;
												}
												IL_186b:
												num18 = Convert.ToDouble(VariableGeneral.toDecimalSIN((num13 * num19 - num16) * 0.13, cantDecimales));
												num20 = Convert.ToDouble(VariableGeneral.toDecimalSIN(num13 * num19 - num16 - num18, cantDecimales));
												Convert.ToDouble(VariableGeneral.toDecimalSIN(Convert.ToDouble(RuntimeHelpers.GetObjectValue(dataTable.Rows[num12]["ICE_Fijo"])), cantDecimales));
												Convert.ToDouble(VariableGeneral.toDecimalSIN(num21, cantDecimales));
												num22 = Convert.ToDouble(VariableGeneral.toDecimalSIN(num20 * num21, cantDecimales));
												num9 += num22;
												num10 += num23;
												num24 = Convert.ToDouble(VariableGeneral.toDecimalSIN(num13 * num19 - num16 + num23 + num22, cantDecimales));
												printerClass2.WriteChars(text8 + num13.ToString("#,##0.####0", CultureInfo.InvariantCulture) + " X " + num19.ToString("#,##0.####0", CultureInfo.InvariantCulture) + " - " + num16.ToString("#,##0.####0", CultureInfo.InvariantCulture) + " +");
												printerClass2.WriteLine("");
												printerClass2.WriteChars(text8 + num22.ToString("#,##0.####0", CultureInfo.InvariantCulture) + " + " + num23.ToString("#,##0.####0", CultureInfo.InvariantCulture));
												if (flag)
												{
													printerClass2.GotoSixth(4.9);
												}
												else
												{
													printerClass2.GotoSixth(5.3);
												}
												printerClass2.WriteChars(text8 + VariableGeneral.toDecimalSIN(num24, cantDecimales).ToString("#,##0.####0", CultureInfo.InvariantCulture));
												break;
											}
											printerClass2.WriteLine("");
										}
									}
								}
								num12++;
								continue;
							}
							double num28 = new clsPropinas().devolverPropinasXvisitaID(visitaID);
							if (!(num28 > 0.0))
							{
								break;
							}
							num6 += num28;
							num7 += num28;
							ctlProductos obj = new ctlProductos();
							string Codigo = "";
							string Nombre = "";
							string CodigoSIN = "";
							string UnidadSINstr = "";
							int UnidadSIN = 0;
							string ActividadSIN = "";
							obj.SetProductoID(1);
							obj.cargarDatosSIN(ref Codigo, ref Nombre, ref CodigoSIN, ref UnidadSINstr, ref UnidadSIN, ref ActividadSIN);
							printerClass2.GotoSixth(1.0);
							if (num == 1)
							{
								printerClass2.WriteLine(text8 + Codigo + " - " + Nombre);
								if (flag)
								{
									printerClass2.GotoSixth(2.0);
								}
								else
								{
									printerClass2.GotoSixth(2.5);
								}
								printerClass2.WriteChars(text8 + "1.00");
								if (flag)
								{
									printerClass2.GotoSixth(3.5);
								}
								else
								{
									printerClass2.GotoSixth(3.9);
								}
								printerClass2.WriteChars(text8 + num28.ToString("#,##0.#0", CultureInfo.InvariantCulture));
								if (flag)
								{
									printerClass2.GotoSixth(4.9);
								}
								else
								{
									printerClass2.GotoSixth(5.3);
								}
								printerClass2.WriteChars(text8 + num28.ToString("#,##0.#0", CultureInfo.InvariantCulture));
								printerClass2.WriteLine("");
							}
							else
							{
								printerClass2.Bold = true;
								printerClass2.WriteLine(text8 + Codigo + " - " + Nombre);
								printerClass2.Bold = false;
								printerClass2.WriteLine(text8 + "   Unidad de Medida: " + UnidadSINstr);
								printerClass2.GotoSixth(1.0);
								printerClass2.WriteChars(text8 + "1.00 X " + num28.ToString("#,##0.#0", CultureInfo.InvariantCulture) + " - 0.00");
								if (flag)
								{
									printerClass2.GotoSixth(4.9);
								}
								else
								{
									printerClass2.GotoSixth(5.3);
								}
								printerClass2.WriteChars(text8 + num28.ToString("#,##0.#0", CultureInfo.InvariantCulture));
								printerClass2.WriteLine("");
							}
							break;
						}
					}
					double num29 = 0.0;
					if (num == 1)
					{
						num29 = num6 - monto;
					}
					else if (visitaID > 0)
					{
						System.Data.DataTable dataTable2 = BD.ConsultaVer("Descuento,FacturaID", "Facturas", "anulada= " + VariableGeneral.armarBolean(0) + " and VisitaID = " + Conversions.ToString(visitaID), "FacturaID desc");
						if (dataTable2.Rows.Count == 0)
						{
							dataTable2 = BD.ConsultaVer("Descuento,FacturaID", "Facturas2", "anulada= " + VariableGeneral.armarBolean(0) + " and VisitaID = " + Conversions.ToString(visitaID), "FacturaID desc");
						}
						if (dataTable2.Rows.Count != 0)
						{
							num29 += Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0][0]), 0));
						}
					}
					if (flag)
					{
						printerClass2.WriteLine("____________________________________________________________");
					}
					else
					{
						printerClass2.DrawLine();
					}
					printerClass2.GotoSixth(1.0);
					if (num == 1)
					{
						if (IceMonto > 0.0)
						{
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + "SUB TOTAL : ");
							printerClass2.GotoSixth(5.3);
							printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(num6 - IceMonto), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + "ICE ESP. : ");
							printerClass2.GotoSixth(5.3);
							string text14 = Math.Round(IceMonto, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture);
							printerClass2.WriteChars(text8 + text14);
							printerClass2.WriteLine("");
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + "TOTAL FACTURADO : ");
							printerClass2.GotoSixth(5.3);
							printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(num6), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							if (num29 > 0.0)
							{
								printerClass2.GotoSixth(1.5);
								printerClass2.WriteChars(text8 + "DESCUENTO : ");
								printerClass2.GotoSixth(5.3);
								printerClass2.WriteChars(text8 + Math.Round(num29, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
								printerClass2.WriteLine("");
							}
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + "TOTAL A COBRAR : ");
							printerClass2.GotoSixth(5.3);
							printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(monto), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
						}
						else if ((num6 > monto) & (AcumuladoPago > 0.0))
						{
							num29 = num6 - num5;
							string text15 = "IMPORTE ";
							string text16 = "DESCUENTO  ";
							string text17 = "TOTAL ";
							if ((AcumuladoPago < num6 - num29) & (AcumuladoPago > 0.0))
							{
								text15 = "IMPORTE DEL SERVICIO ";
								text16 = "AL CREDITO ";
								text17 = "TOTAL PAGADO ";
								num29 = 0.0;
							}
							else if ((AcumuladoPago == num6 - num29) & (AcumuladoPago > 0.0))
							{
								text15 = "IMPORTE DEL SERVICIO ";
								text16 = "(-) ANTICIPO ";
								text17 = "TOTAL PAGADO ";
							}
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + text15 + MonedaString + ": ");
							if (flag)
							{
								printerClass2.GotoSixth(4.9);
							}
							else
							{
								printerClass2.GotoSixth(5.3);
							}
							printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(num6), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + text16 + MonedaString + ": ");
							if (flag)
							{
								printerClass2.GotoSixth(4.9);
							}
							else
							{
								printerClass2.GotoSixth(5.3);
							}
							printerClass2.WriteChars(text8 + Math.Round(num6 - monto - num29, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							if (num29 > 0.0)
							{
								printerClass2.GotoSixth(1.5);
								printerClass2.WriteChars(text8 + "DESCUENTO " + MonedaString + ": ");
								if (flag)
								{
									printerClass2.GotoSixth(4.9);
								}
								else
								{
									printerClass2.GotoSixth(5.3);
								}
								printerClass2.WriteChars(text8 + Math.Round(num29, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
								printerClass2.WriteLine("");
							}
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + text17 + MonedaString + ": ");
							if (flag)
							{
								printerClass2.GotoSixth(4.9);
							}
							else
							{
								printerClass2.GotoSixth(5.3);
							}
							printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(monto), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Allegronet1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.TartinaFactura))
							{
								ctlFacturas ctlFacturas2 = new ctlFacturas();
								if (!esCopia && ctlFacturas2.buscarPorNro(nroFactura))
								{
									ctlFacturas2.UpdateDescuento(Math.Round(num29, 2));
								}
							}
						}
						else if (num6 > monto)
						{
							string text18 = "IMPORTE ";
							string text19 = "DESCUENTO  ";
							string text20 = "TOTAL ";
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + text18 + MonedaString + ": ");
							if (flag)
							{
								printerClass2.GotoSixth(4.9);
							}
							else
							{
								printerClass2.GotoSixth(5.3);
							}
							printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(num6), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + text19 + MonedaString + ": ");
							if (flag)
							{
								printerClass2.GotoSixth(4.9);
							}
							else
							{
								printerClass2.GotoSixth(5.3);
							}
							printerClass2.WriteChars(text8 + Math.Round(num6 - monto, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + text20 + MonedaString + ": ");
							if (flag)
							{
								printerClass2.GotoSixth(4.9);
							}
							else
							{
								printerClass2.GotoSixth(5.3);
							}
							printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(monto), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Allegronet1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.TartinaFactura))
							{
								ctlFacturas ctlFacturas3 = new ctlFacturas();
								if (!esCopia && ctlFacturas3.buscarPorNro(nroFactura))
								{
									ctlFacturas3.UpdateDescuento(Math.Round(num29, 2));
								}
							}
						}
						else
						{
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + "TOTAL " + MonedaString + ": ");
							if (flag)
							{
								printerClass2.GotoSixth(4.9);
							}
							else
							{
								printerClass2.GotoSixth(5.3);
							}
							string text21 = Math.Round(Convert.ToDouble(monto), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture);
							printerClass2.WriteChars(text8 + text21);
							printerClass2.WriteLine("");
						}
						string text22 = "";
						text22 = ((DateTime.Compare(fecha, DateAndTime.Today.AddDays(-7.0)) >= 0) ? new clsPagos().devolverCuentaPorVisitaId(visitaID) : "");
						if ((cambio >= 0.0) & (text22.Length > 0))
						{
							printerClass2.GotoSixth(1.5);
							if (Operators.CompareString(text22.ToUpper(), "CAJA CHICA BS", TextCompare: false) == 0)
							{
								printerClass2.WriteChars(text8 + "EFECTIVO " + MonedaString + ": ");
							}
							else
							{
								if (text22.Length > 20)
								{
									text22 = text22.ToString().Substring(0, 20);
								}
								printerClass2.WriteChars(text8 + text22.ToUpper() + ": ");
							}
							if (flag)
							{
								printerClass2.GotoSixth(4.9);
							}
							else
							{
								printerClass2.GotoSixth(5.3);
							}
							printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(monto + cambio), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							printerClass2.GotoSixth(1.5);
							printerClass2.WriteChars(text8 + "CAMBIO " + MonedaString + ": ");
							if (flag)
							{
								printerClass2.GotoSixth(4.9);
							}
							else
							{
								printerClass2.GotoSixth(5.3);
							}
							printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(cambio), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
						}
						else
						{
							if (text22.Length > 0)
							{
								printerClass2.WriteLine("");
								printerClass2.GotoSixth(1.0);
								if (Operators.CompareString(text22.ToUpper(), "CAJA CHICA BS", TextCompare: false) == 0)
								{
									printerClass2.WriteChars(text8 + "METODO DE PAGO:  EFECTIVO");
								}
								else
								{
									printerClass2.WriteChars(text8 + "METODO DE PAGO:  " + text22.ToUpper() + " ");
								}
							}
							printerClass2.WriteLine("");
						}
					}
					else
					{
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(text8 + "SUB TOTAL Bs: ");
						printerClass2.GotoSixth(5.3);
						printerClass2.WriteChars(text8 + VariableGeneral.toDecimalSIN(num7, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
						printerClass2.WriteLine("");
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(text8 + "DESCUENTO Bs: ");
						printerClass2.GotoSixth(5.3);
						printerClass2.WriteChars(text8 + VariableGeneral.toDecimalSIN(num29, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
						printerClass2.WriteLine("");
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(text8 + "TOTAL Bs: ");
						printerClass2.GotoSixth(5.3);
						printerClass2.WriteChars(text8 + VariableGeneral.toDecimalSIN(monto, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
						printerClass2.WriteLine("");
						if (DocumentoSector == 14)
						{
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars(text8 + "(-)TOTAL ICE ESPECÍFICO Bs: ");
							printerClass2.GotoSixth(5.3);
							num10 = Convert.ToDouble(VariableGeneral.toDecimalSIN(num10, 2));
							printerClass2.WriteChars(text8 + num10.ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars(text8 + "(-)TOTAL ICE PORCENTUAL Bs: ");
							printerClass2.GotoSixth(5.3);
							num9 = Convert.ToDouble(VariableGeneral.toDecimalSIN(num9, 2));
							printerClass2.WriteChars(text8 + num9.ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
						}
						else
						{
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars(text8 + "MONTO GIFT CARD Bs: ");
							printerClass2.GotoSixth(5.3);
							num3 = Convert.ToDouble(VariableGeneral.toDecimalSIN(num3, 2));
							printerClass2.WriteChars(text8 + num3.ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
							printerClass2.Bold = true;
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars(text8 + "MONTO A PAGAR Bs: ");
							printerClass2.GotoSixth(5.3);
							printerClass2.WriteChars(text8 + VariableGeneral.toDecimalSIN(monto - num3, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
							printerClass2.WriteLine("");
						}
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(text8 + "IMP.BASE CREDITO FISCAL: ");
						printerClass2.GotoSixth(5.3);
						if (DocumentoSector == 8)
						{
							printerClass2.WriteChars(text8 + Math.Round(0m, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
						}
						else
						{
							printerClass2.WriteChars(text8 + VariableGeneral.toDecimalSIN(monto - num3 - num10 - num9, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
						}
						printerClass2.WriteLine("");
						printerClass2.Bold = false;
					}
					printerClass2.WriteLine("");
					printerClass2.AlignLeft();
					string text23 = new clsNumLetras2().Convertir(Math.Round(Convert.ToDouble(monto - num3), 2)) + " " + MonedaString;
					string text24 = "Son: ";
					for (num4 = 0; num4 < text23.Length; num4++)
					{
						text24 += text23.ToString().Substring(num4, 1);
						if (TextRenderer.MeasureText(text24, font2).Width > 230)
						{
							printerClass2.WriteLine(text8 + text24);
							text24 = "";
						}
					}
					printerClass2.WriteLine(text8 + text24);
					printerClass2.WriteLine("");
					if ((IceMonto > 0.0) & (num == 1))
					{
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(text8 + "Importe base para Credito Fiscal:");
						if (flag)
						{
							printerClass2.GotoSixth(4.9);
						}
						else
						{
							printerClass2.GotoSixth(5.3);
						}
						printerClass2.WriteChars(text8 + Math.Round(Convert.ToDouble(monto - IceMonto), 2).ToString("#,##0.#0", CultureInfo.InvariantCulture));
						printerClass2.WriteLine("");
					}
					if (flag)
					{
						printerClass2.WriteLine("____________________________________________________________");
						printerClass2.WriteLine("");
					}
					else
					{
						printerClass2.DrawLine();
						printerClass2.WriteLine("");
					}
					if (num == 1)
					{
						printerClass2.WriteLine("");
						printerClass2.WriteLine(text8 + "Codigo de control: " + codigo);
						printerClass2.WriteLine(text8 + "Fecha limite de Emision: " + _fechaLimiteEmision);
						printerClass2.WriteLine("");
					}
					if (comentario.Length > 0)
					{
						printerClass2.WriteLine("");
						printerClass2.WriteLine(comentario ?? "");
						printerClass2.WriteLine("");
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Aerocruz)
					{
						double num30 = new clsTipoCambio().returnTipoCambio();
						double value3 = monto / num30;
						printerClass2.WriteLine("");
						printerClass2.WriteLine(text8 + "EQUIVALENTE A " + Math.Round(value3, 2).ToString("#,##0.#0", CultureInfo.InvariantCulture) + " $US");
						printerClass2.WriteLine("");
					}
					printerClass2.AlignCenter();
					string text25 = "";
					text25 = ((num == 1) ? (miNIT + "|" + Conversions.ToString(nroFactura) + "|" + autorizacion + "|" + now.ToString("dd/MM/yyyy") + "|" + Math.Round(monto, 2).ToString("##0.#0", CultureInfo.InvariantCulture) + "|" + Math.Round(monto - IceMonto, 2).ToString("##0.#0", CultureInfo.InvariantCulture) + "|" + codigo + "|" + nit + "|" + Math.Round(IceMonto, 2).ToString("##0.#0", CultureInfo.InvariantCulture) + "|0|0|" + Math.Round(num29, 2).ToString("##0.#0", CultureInfo.InvariantCulture)) : ((Conversions.ToInteger(BD.ConsultaVer("CodigoAmbiente", "FactElectConfiguracion", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID)).Rows[0][0]) == 1) ? ((!esContingencia) ? ("https://siat.impuestos.gob.bo/consulta/QR?nit=" + miNIT + "&cuf=" + codigo + "&numero=" + Conversions.ToString(nroFactura) + "&t=1") : ("https://siat.impuestos.gob.bo/consulta/QR?nit=" + miNIT + "&cuf=" + codigo + "&numero=" + Conversions.ToString(nroFactura) + "&t=1")) : ((!esContingencia) ? ("https://pilotosiat.impuestos.gob.bo/consulta/QR?nit=" + miNIT + "&cuf=" + codigo + "&numero=" + Conversions.ToString(nroFactura) + "&t=1") : ("https://pilotosiat.impuestos.gob.bo/consulta/QR?nit=" + miNIT + "&cuf=" + codigo + "&numero=" + Conversions.ToString(nroFactura) + "&t=1"))));
					QRCodeEncoder qRCodeEncoder = new QRCodeEncoder();
					qRCodeEncoder.QRCodeEncodeMode = QRCodeEncoder.ENCODE_MODE.BYTE;
					qRCodeEncoder.QRCodeScale = 2;
					switch ("Medio (15%)")
					{
					case "Bajo (7%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.L;
						break;
					case "Medio (15%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.M;
						break;
					case "Alto (25%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.Q;
						break;
					case "Muy alto (30%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.H;
						break;
					}
					qRCodeEncoder.QRCodeVersion = 0;
					qRCodeEncoder.QRCodeBackgroundColor = Color.FromArgb(Color.White.ToArgb());
					qRCodeEncoder.QRCodeForegroundColor = Color.FromArgb(Color.Black.ToArgb());
					System.Drawing.Image image;
					try
					{
						image = qRCodeEncoder.Encode(text25, Encoding.UTF8);
					}
					catch (Exception ex5)
					{
						ProjectData.SetProjectError(ex5);
						Exception ex6 = ex5;
						result = ex6.Message;
						ProjectData.ClearProjectError();
						goto end_IL_0000;
					}
					printerClass2.PrintQRfactura1(image);
					printerClass2.WriteLine("");
					printerClass2.AlignCenter();
					printerClass2.GotoSixth(1.0);
					printerClass2.setFont(num2);
					printerClass2.WriteLine(text8 + "ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL");
					printerClass2.WriteLine(text8 + "PAÍS, EL USO ILÍCITO DE ÉSTA SERÁ SANCIONADO");
					printerClass2.WriteLine(text8 + "PENALMENTE DE ACUERDO A LEY");
					printerClass2.WriteLine("");
					printerClass2.setFont(font);
					if (ley.Length > 0)
					{
						string text26 = "";
						for (num4 = 0; num4 < ley.Length; num4++)
						{
							text26 += ley.Substring(num4, 1);
							if (TextRenderer.MeasureText(text26, font2).Width > 300)
							{
								printerClass2.WriteLine(text8 + text26);
								text26 = "";
							}
						}
						printerClass2.WriteLine(text8 + text26);
						printerClass2.WriteLine("");
					}
					printerClass2.setFont(num2);
					if (num != 1)
					{
						if (esContingencia)
						{
							printerClass2.WriteLine(text8 + "\"Este documento es la Representación");
							printerClass2.WriteLine(text8 + "Gráfica de un Documento Fiscal Digital");
							printerClass2.WriteLine(text8 + "emitido fuera de linea, verifique su envío");
							printerClass2.WriteLine(text8 + "con su proveedor o en la página web ");
							printerClass2.WriteLine(text8 + "www.impuestos.gob.bo\"");
							printerClass2.WriteLine("");
						}
						else if (ImprimirEnArchivo)
						{
							printerClass2.WriteLine(text8 + "\"Este documento es la Representación");
							printerClass2.WriteLine(text8 + "Gráfica de un Documento Fiscal Digital");
							printerClass2.WriteLine(text8 + "emitido en una modalidad de facturación\"");
							printerClass2.WriteLine(text8 + "en línea\"");
							printerClass2.WriteLine("");
						}
						else
						{
							printerClass2.WriteLine(text8 + "\"Este documento es una impresión de un");
							printerClass2.WriteLine(text8 + "Documento Fiscal Digital emitido en una ");
							printerClass2.WriteLine(text8 + "modalidad de facturación en línea\"");
							printerClass2.WriteLine("");
						}
					}
					if (!configuration.gComidaRapida)
					{
						ctlVisitas2.SetID(visitaID);
						int ClienteID = 0;
						string Obs = "";
						int MesaID = default(int);
						ctlVisitas2.llenarclase(ref MesaID, ref ClienteID, ref Obs);
						if (MesaID > 0)
						{
							ctlMesas ctlMesas2 = new ctlMesas();
							ctlMesas2.SetID(MesaID);
							ctlMesas2.loadMesaPorID();
							if (configuration.gStyleBoliches1 == configuration.styleBolichesId.DonMiguel)
							{
								printerClass2.WriteLine("Mesero:  " + ctlMesas2.GetResponsableNombre());
							}
							string nombre2 = ctlMesas2.GetNombre();
							if (nombre2.Length > 0)
							{
								printerClass2.WriteLine("Mesa: " + nombre2);
							}
						}
					}
					if (Observacion.Length > 0)
					{
						printerClass2.WriteLine("");
						printerClass2.WriteLine(Observacion);
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Rokani)
					{
						ctlMeseros ctlMeseros2 = new ctlMeseros();
						ctlMeseros2.SetMeseroID(meseroID);
						printerClass2.WriteLine("Encargado: " + ctlMeseros2.devolverNombre());
					}
					if (configuration.styleBolichesId.KIKY == configuration.gStyleBoliches1)
					{
						printerClass2.WriteLine("Cuenta: " + Conversions.ToString(visitaID));
					}
					if (configuration.styleBolichesId.Landivar == configuration.gStyleBoliches1)
					{
						printerClass2.WriteLine("Cuenta: " + visitas);
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Iturri)
					{
						printerClass2.WriteLine("");
						printerClass2.WriteLine("");
					}
					if (!ImprimirEnArchivo)
					{
						printerClass2.CutPaper();
					}
					printerClass2.EndDoc();
					image.Dispose();
					image = null;
					printerClass2 = null;
					result = "";
				}
				end_IL_0000:;
			}
			catch (Exception ex7)
			{
				ProjectData.SetProjectError(ex7);
				Exception ex8 = ex7;
				result = ex8.Message;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public static void printTicketElSolar(System.Data.DataTable dgvPedido, bool aumentoEnCuenta, int NroOrden, string PersonaQueRecoge, bool paraLlevar, string NombreMesero, string lblMesa, string txtMesa, bool imprimir, double Total, bool TodoSeparado, bool SopaEnTaper, bool porCredito)
	{
		dgvPedido.AcceptChanges();
		System.Data.DataTable table = dgvPedido.Copy();
		if (!imprimir)
		{
			return;
		}
		int num = 0;
		DataView dataView = new DataView(table);
		string[] array = new string[2];
		num = 0;
		ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
		if (ctlImpresoras2.devolverImpresoraCuentaFisico().Length > 0)
		{
			array[0] = ctlImpresoras2.devolverImpresoraCuentaFisico();
			_ = array[0];
			num = 1;
		}
		if (porCredito)
		{
			if (num == 0)
			{
				array[0] = ctlImpresoras2.DevolverImprimirFacturaFisico();
				_ = array[0];
				num = 1;
			}
			if (num == 0)
			{
				return;
			}
		}
		else if (num == 0)
		{
			return;
		}
		bool encontro = false;
		if (array[0].Length <= 0)
		{
			return;
		}
		PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, array[0], ref encontro, "Restotech Ticket");
		if (!encontro)
		{
			Interaction.MsgBox("No puedo encontrar la impresora " + array[0]);
			return;
		}
		PrinterClass printerClass2 = printerClass;
		printerClass2.AlignCenter();
		printerClass2.MaxFont();
		printerClass2.Bold = true;
		string text = "Pedido";
		if (aumentoEnCuenta)
		{
			printerClass2.WriteLine(text + " - (Aumento)");
		}
		else
		{
			printerClass2.WriteLine(text);
		}
		if (NroOrden > 0)
		{
			printerClass2.GotoSixth(1.0);
			printerClass2.MaxFont();
			if (paraLlevar)
			{
				printerClass2.WriteChars("Orden: " + Conversions.ToString(NroOrden) + " - Para llevar");
			}
			else
			{
				printerClass2.WriteChars("Orden: " + Conversions.ToString(NroOrden));
			}
			printerClass2.WriteLine("");
		}
		if (PersonaQueRecoge.Length > 0)
		{
			printerClass2.GotoSixth(1.0);
			printerClass2.BigFont();
			printerClass2.WriteChars("Recoge: " + PersonaQueRecoge);
			printerClass2.WriteLine("");
		}
		printerClass2.GotoSixth(1.0);
		printerClass2.NormalFont();
		printerClass2.WriteLine("Fecha:" + DateTime.Now.ToString());
		printerClass2.DrawLine();
		printerClass2.NormalFont();
		printerClass2.GotoSixth(1.0);
		printerClass2.WriteChars("");
		printerClass2.GotoSixth(1.0);
		printerClass2.WriteChars("Cant");
		printerClass2.GotoSixth(2.0);
		printerClass2.WriteChars("   Descripcion");
		printerClass2.WriteLine("");
		printerClass2.DrawLine();
		printerClass2.NormalFont();
		DataView dataView2 = dataView;
		int num2 = 0;
		string text2 = "";
		checked
		{
			foreach (object item in dataView2)
			{
				object objectValue = RuntimeHelpers.GetObjectValue(item);
				if (Operators.ConditionalCompareObjectEqual(NewLateBinding.LateIndexGet(objectValue, new object[1] { "ProductoID" }, null), 2, TextCompare: false))
				{
					num2 = Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null));
					text2 = Conversions.ToString(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null));
				}
				else
				{
					if (!Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null), 0, TextCompare: false))
					{
						continue;
					}
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars(Conversions.ToDouble(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null)).ToString("##.##") ?? "");
					printerClass2.GotoSixth(2.0);
					printerClass2.WriteChars("  " + NewLateBinding.LateIndexGet(objectValue, new object[1] { "Producto" }, null).ToString());
					printerClass2.WriteLine("");
					if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue, new object[1] { "ProductosCombo" }, null)), "").ToString().Length > 0)
					{
						string[] array2 = NewLateBinding.LateIndexGet(objectValue, new object[1] { "ProductosCombo" }, null).ToString().Split(',');
						foreach (string obj in array2)
						{
							ctlProductos ctlProductos2 = new ctlProductos();
							string[] array3 = obj.ToString().Split('-');
							if (Operators.CompareString(array3[1], "1", TextCompare: false) == 0)
							{
								ctlProductos2.SetProductoID(Conversions.ToInteger(array3[0]));
								string impresoraFisica = "";
								ctlProductos2.cargarDatosCombo(ref impresoraFisica);
								printerClass2.GotoSixth(1.0);
								printerClass2.WriteLine("  +" + ctlProductos2.getNombre());
							}
						}
					}
					System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
					int num3 = 230;
					if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Observaciones" }, null)), "").ToString().Length <= 0)
					{
						continue;
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
					{
						printerClass2._FontSize += 2.0;
						printerClass2.Bold = true;
					}
					printerClass2.GotoSixth(1.5);
					string text3 = NewLateBinding.LateIndexGet(objectValue, new object[1] { "Observaciones" }, null).ToString();
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO)
					{
						printerClass2.GotoSixth(2.0);
						text3 = "(" + text3 + ")";
					}
					string[] array4 = ("**" + text3).Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string obj2 in array4)
					{
						string text4 = "-";
						string impresoraFisica = obj2;
						for (int k = 0; k < impresoraFisica.Length; k++)
						{
							char c = impresoraFisica[k];
							if (TextRenderer.MeasureText(text4 + Conversions.ToString(c), font).Width > num3 - 10)
							{
								printerClass2.WriteLine(text4);
								text4 = "  " + c;
							}
							else
							{
								text4 += Conversions.ToString(c);
							}
						}
						if (Operators.CompareString(text4, "", TextCompare: false) != 0)
						{
							printerClass2.WriteLine(text4);
						}
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
					{
						printerClass2._FontSize -= 2.0;
						printerClass2.Bold = false;
					}
				}
			}
			if (num2 > 0)
			{
				printerClass2.GotoSixth(1.0);
				printerClass2.WriteChars(num2.ToString());
				printerClass2.GotoSixth(2.0);
				printerClass2.WriteChars(text2.ToString());
				printerClass2.WriteLine("");
			}
			printerClass2.WriteChars("Total: ");
			printerClass2.GotoSixth(5.0);
			printerClass2.WriteChars(Math.Round(Convert.ToDouble(Total), 1).ToString("##.#0") + " " + VariableGeneral.MonedaString1);
			printerClass2.WriteLine("");
			printerClass2.NormalFont();
			printerClass2.DrawLine();
			printerClass2.GotoSixth(1.0);
			if (!configuration.gComidaRapida)
			{
				printerClass2.WriteLine("");
				printerClass2.WriteChars("Por: " + NombreMesero);
			}
			if (porCredito)
			{
				printerClass2.WriteLine("");
				printerClass2.WriteLine("");
				printerClass2.WriteChars("             Firma");
				printerClass2.WriteLine("");
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos)
			{
				printerClass2.WriteLine("");
				printerClass2.WriteLine("");
				printerClass2.WriteLine("");
				printerClass2.WriteLine(".");
			}
			else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.DonShawarmaLite)
			{
				printerClass2.WriteLine(" ");
				printerClass2.WriteLine(" ");
				printerClass2.WriteLine(" ");
				printerClass2.WriteLine(" ");
				printerClass2.WriteLine(" ");
				printerClass2.WriteLine(" ");
				printerClass2.WriteLine(" ");
				printerClass2.WriteLine(".");
			}
			else
			{
				printerClass2.WriteLine(".");
			}
			printerClass2.CutPaper();
			printerClass2.EndDoc();
			printerClass2 = null;
		}
	}

	public static bool printComandasSolarZucchini(System.Data.DataTable dgvPedido, bool aumentoEnCuenta, int NroOrden, string PersonaQueRecoge, bool paraLlevar, string NombreMesero, string lblMesa, string txtMesa, bool imprimir, ref string error1)
	{
		checked
		{
			bool result;
			try
			{
				dgvPedido.AcceptChanges();
				System.Data.DataTable table = dgvPedido.Copy();
				if (!imprimir)
				{
					result = false;
				}
				else
				{
					string left = "";
					int num = 0;
					bool flag = false;
					DataView dataView = new DataView(table);
					dataView.Sort = "Impresora ASC";
					foreach (object item in dataView)
					{
						object objectValue = RuntimeHelpers.GetObjectValue(item);
						if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null), 0, TextCompare: false) && ((Operators.CompareString(left, NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString(), TextCompare: false) != 0) & (NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString().Length > 0)))
						{
							num++;
							left = NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString();
						}
					}
					string[] array = new string[num * 4 + 1];
					string[] array2 = new string[num * 4 + 1];
					num = 0;
					left = "";
					ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
					foreach (object item2 in dataView)
					{
						object objectValue2 = RuntimeHelpers.GetObjectValue(item2);
						if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null), 0, TextCompare: false) && ((Operators.CompareString(left, NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString(), TextCompare: false) != 0) & (NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString().Length > 0)))
						{
							string[] array3 = ctlImpresoras2.devolverNombreFisicoPorNombre(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString()).ToString().Split(';');
							foreach (string text in array3)
							{
								array[num] = text;
								left = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString();
								array2[num] = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Impresora" }, null).ToString();
								num++;
							}
						}
					}
					if (num == 0)
					{
						error1 = "no encontro impresoras";
						result = false;
					}
					else
					{
						int num2 = num - 1;
						int num3 = 0;
						while (true)
						{
							if (num3 <= num2)
							{
								bool encontro = false;
								if (array[num3].Length > 0)
								{
									PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, array[num3], ref encontro, "Restotech Pedido");
									if (!encontro)
									{
										Interaction.MsgBox("No puedo encontrar la impresora " + array[num3]);
										error1 = "No puedo encontrar la impresora " + array[num3];
										result = false;
										break;
									}
									PrinterClass printerClass2 = printerClass;
									flag = true;
									printerClass2.AlignCenter();
									printerClass2.MaxFont();
									printerClass2.Bold = true;
									string text2 = "";
									text2 = ((!paraLlevar) ? "EN MESA" : ((Operators.CompareString(PersonaQueRecoge, "", TextCompare: false) != 0) ? "RECOGER" : ((!configuration.gComidaRapida) ? "Pedido Copia" : "LLEVAR")));
									if (aumentoEnCuenta)
									{
										printerClass2.WriteLine(text2 + " - (Aumento)");
									}
									else
									{
										printerClass2.WriteLine(text2);
									}
									if (NroOrden > 0)
									{
										printerClass2.GotoSixth(1.0);
										printerClass2.MaxFont();
										printerClass2.WriteChars(" Orden: " + Conversions.ToString(NroOrden));
										printerClass2.WriteLine("");
									}
									if (PersonaQueRecoge.Length > 0)
									{
										printerClass2.GotoSixth(1.0);
										printerClass2.BigFont();
										printerClass2.WriteChars("Recoge: " + PersonaQueRecoge);
										printerClass2.WriteLine("");
									}
									else
									{
										printerClass2.GotoSixth(1.0);
										printerClass2.NormalBiggerFont();
										if (txtMesa.Length > 0)
										{
											printerClass2.AlignRight();
											printerClass2.WriteChars(lblMesa + ": " + txtMesa);
											printerClass2.WriteLine("");
										}
									}
									printerClass2.AlignLeft();
									printerClass2.GotoSixth(1.0);
									printerClass2.NormalFont();
									printerClass2.WriteLine(" " + DateTime.Now.ToString());
									printerClass2.DrawLine();
									printerClass2.NormalFont();
									printerClass2.GotoSixth(1.0);
									printerClass2.WriteChars("");
									printerClass2.GotoSixth(1.0);
									printerClass2.WriteChars("Cant");
									printerClass2.GotoSixth(2.0);
									printerClass2.WriteChars("   Descripcion");
									printerClass2.WriteLine("");
									printerClass2.DrawLine();
									printerClass2.NormalFont();
									DataView dataView2 = dataView;
									dataView2.RowFilter = "Impresora= '" + array2[num3] + "'";
									printerClass2.AlignLeft();
									int num4 = 0;
									string text3 = "";
									foreach (object item3 in dataView2)
									{
										object objectValue3 = RuntimeHelpers.GetObjectValue(item3);
										if (Operators.ConditionalCompareObjectEqual(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "ProductoID" }, null), 2, TextCompare: false))
										{
											num4 = Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Cantidad" }, null));
											text3 = Conversions.ToString(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Producto" }, null));
										}
										else
										{
											if (!Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Cantidad" }, null), 0, TextCompare: false))
											{
												continue;
											}
											printerClass2.GotoSixth(1.0);
											double num5 = Conversions.ToDouble(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Cantidad" }, null));
											if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "ProductosCombo" }, null)), "").ToString().Length > 0)
											{
												printerClass2.WriteChars("  " + num5.ToString("##.##") + "x");
											}
											else
											{
												printerClass2.WriteChars("  " + num5.ToString("##.##"));
											}
											int num6 = 0;
											string text4 = "";
											string text5 = "";
											string text6 = "";
											System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
											int num7 = 230;
											while (num6 < NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Producto" }, null).ToString().Length)
											{
												text4 += NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Producto" }, null).ToString().Substring(num6, 1);
												if (TextRenderer.MeasureText(text4, font).Width > num7)
												{
													text6 += NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Producto" }, null).ToString().Substring(num6, 1);
												}
												else
												{
													text5 += NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Producto" }, null).ToString().Substring(num6, 1);
												}
												num6++;
											}
											printerClass2.GotoSixth(1.5);
											printerClass2.WriteChars(text5);
											printerClass2.WriteLine("");
											if (text6.Trim().Length > 0)
											{
												printerClass2.GotoSixth(1.5);
												printerClass2.WriteChars(text6.Trim());
												printerClass2.WriteLine("");
											}
											if (Operators.ConditionalCompareObjectGreater(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "MeseroID" }, null)), 0), 0, TextCompare: false))
											{
												ctlMeseros ctlMeseros2 = new ctlMeseros();
												ctlMeseros2.SetMeseroID(Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "MeseroID" }, null)));
												string text7 = ctlMeseros2.devolverNombre();
												if (Operators.CompareString(NombreMesero, text7, TextCompare: false) != 0)
												{
													printerClass2.WriteChars("  --" + text7);
													printerClass2.WriteLine("");
												}
											}
											if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "ProductosCombo" }, null)), "").ToString().Length > 0)
											{
												string[] array4 = NewLateBinding.LateIndexGet(objectValue3, new object[1] { "ProductosCombo" }, null).ToString().Split(',');
												string[] array5 = array4;
												for (int j = 0; j < array5.Length; j++)
												{
													_ = array5[j];
												}
												string text8 = "";
												int num8 = 0;
												int num9 = 0;
												string[] array6 = array4;
												foreach (string text9 in array6)
												{
													if (Operators.CompareString(text8, text9, TextCompare: false) != 0)
													{
														if (num9 > 0)
														{
															ctlProductos ctlProductos2 = new ctlProductos();
															string[] array7 = text8.ToString().Split('-');
															string impresoraFisica = "";
															ctlProductos2.SetProductoID(Conversions.ToInteger(array7[0]));
															ctlProductos2.cargarDatosCombo(ref impresoraFisica);
															if (array7.Length > 1)
															{
																if (Operators.CompareString(impresoraFisica, array[num3], TextCompare: false) != 0 && !(impresoraFisica.ToString().Contains(array[num3].ToString()) & impresoraFisica.Contains(";")))
																{
																	array7[1] = "0";
																}
																if (Operators.CompareString(array7[1], "1", TextCompare: false) == 0)
																{
																	printerClass2.GotoSixth(1.2);
																	string text10 = "";
																	text10 = ((Conversions.ToDouble(array7[2]) != 0.0) ? "  +" : "  -");
																	printerClass2.WriteChars(text10 + Conversions.ToString(num8));
																	printerClass2.GotoSixth(2.0);
																	printerClass2.WriteChars(ctlProductos2.getNombre());
																	printerClass2.WriteLine("");
																}
															}
															else if (array7[0].Length > 0)
															{
																printerClass2.GotoSixth(1.2);
																printerClass2.WriteChars("  +" + Conversions.ToString(num8));
																printerClass2.GotoSixth(2.0);
																printerClass2.WriteChars(ctlProductos2.getNombre());
																printerClass2.WriteLine("");
															}
														}
														text8 = text9;
														num8 = 1;
													}
													else
													{
														num8++;
													}
													num9++;
												}
												ctlProductos ctlProductos3 = new ctlProductos();
												string impresoraFisica2 = "";
												string[] array8 = text8.ToString().Split('-');
												ctlProductos3.SetProductoID(Conversions.ToInteger(array8[0]));
												ctlProductos3.cargarDatosCombo(ref impresoraFisica2);
												if (array8.Length > 1)
												{
													if (Operators.CompareString(impresoraFisica2, array[num3], TextCompare: false) != 0 && !(impresoraFisica2.ToString().Contains(array[num3].ToString()) & impresoraFisica2.Contains(";")))
													{
														array8[1] = "0";
													}
													if (Operators.CompareString(array8[1], "1", TextCompare: false) == 0)
													{
														printerClass2.GotoSixth(1.2);
														string text11 = "";
														text11 = ((Conversions.ToDouble(array8[2]) != 0.0) ? "  +" : "  -");
														printerClass2.WriteChars(text11 + Conversions.ToString(num8));
														printerClass2.GotoSixth(2.0);
														printerClass2.WriteChars(ctlProductos3.getNombre());
														printerClass2.WriteLine("");
													}
												}
												else
												{
													printerClass2.GotoSixth(1.2);
													printerClass2.WriteChars("  +" + Conversions.ToString(num8));
													printerClass2.GotoSixth(2.0);
													printerClass2.WriteChars(ctlProductos3.getNombre());
													printerClass2.WriteLine("");
												}
											}
											font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
											if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Observaciones" }, null)), "").ToString().Length <= 0)
											{
												continue;
											}
											if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
											{
												printerClass2._FontSize += 2.0;
												printerClass2.Bold = true;
											}
											printerClass2.GotoSixth(1.5);
											string text12 = NewLateBinding.LateIndexGet(objectValue3, new object[1] { "Observaciones" }, null).ToString();
											if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO)
											{
												printerClass2.GotoSixth(2.0);
												text12 = "(" + text12 + ")";
											}
											string[] array9 = ("**" + text12).Split(new string[3]
											{
												Environment.NewLine,
												"\n",
												"\r"
											}, StringSplitOptions.None);
											foreach (string obj in array9)
											{
												string text13 = "-";
												string text14 = obj;
												for (int m = 0; m < text14.Length; m++)
												{
													char c = text14[m];
													if (TextRenderer.MeasureText(text13 + Conversions.ToString(c), font).Width > num7 - 10)
													{
														printerClass2.WriteLine(text13);
														text13 = "  " + c;
													}
													else
													{
														text13 += Conversions.ToString(c);
													}
												}
												if (Operators.CompareString(text13, "", TextCompare: false) != 0)
												{
													printerClass2.WriteLine(text13);
												}
											}
											if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
											{
												printerClass2._FontSize -= 2.0;
												printerClass2.Bold = false;
											}
										}
									}
									if (num4 > 0)
									{
										printerClass2.GotoSixth(1.0);
										printerClass2.WriteChars(num4.ToString());
										printerClass2.GotoSixth(2.0);
										printerClass2.WriteChars(text3.ToString());
										printerClass2.WriteLine("");
									}
									printerClass2.NormalFont();
									printerClass2.DrawLine();
									printerClass2.GotoSixth(1.0);
									if (!configuration.gComidaRapida)
									{
										printerClass2.WriteLine("");
										if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.DonMiguel) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Zucchini))
										{
											printerClass2.WriteChars("Por: " + NombreMesero);
										}
									}
									else
									{
										printerClass2.WriteLine("");
										printerClass2.WriteLine("");
									}
									if (paraLlevar)
									{
										printerClass2.WriteLine("");
										printerClass2.WriteChars("................................");
									}
									printerClass2.CutPaper();
									printerClass2.EndDoc();
									printerClass2 = null;
								}
								num3++;
								continue;
							}
							result = flag;
							break;
						}
					}
				}
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				error1 = ex2.Message;
				Interaction.MsgBox(ex2.Message);
				result = false;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public static bool printRecibo(string personal, double Monto, string TipoGasto, string Observaciones, DateTime fecha, string caja, int nro)
	{
		try
		{
			ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
			string text = ctlImpresoras2.devolverImpresoraCuentaFisico();
			if (text.Length == 0)
			{
				text = ctlImpresoras2.DevolverImprimirFacturaFisico();
			}
			if (text.Length == 0)
			{
				text = ctlImpresoras2.DevolverImpresora();
			}
			bool encontro = false;
			PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, text, ref encontro, "Restotech Recibo");
			if (!encontro)
			{
				Interaction.MsgBox("No puedo encontrar la impresora ");
			}
			else
			{
				PrinterClass printerClass2 = printerClass;
				printerClass2.WriteLine("");
				printerClass2.WriteLine("Nro. " + Conversions.ToString(nro) + " / " + Conversions.ToString(DateAndTime.Today.Year));
				printerClass2.WriteLine("");
				printerClass2.AlignCenter();
				printerClass2.MaxFont();
				printerClass2.Bold = true;
				printerClass2.WriteLine("RECIBO DE PAGO");
				printerClass2.Bold = false;
				printerClass2.AlignLeft();
				printerClass2.NormalFont();
				printerClass2.WriteLine("");
				printerClass2.GotoSixth(1.0);
				printerClass2.WriteLine(" FECHA :  " + fecha.ToString("dd/MM/yyyy HH:mm"));
				printerClass2.DrawLine();
				printerClass2.WriteLine("");
				printerClass2.WriteLine(" TIPO DE GASTO :     " + TipoGasto);
				printerClass2.GotoSixth(2.0);
				printerClass2.WriteLine("");
				printerClass2.WriteLine(" MONTO PAGO    :     " + Conversions.ToString(Monto));
				printerClass2.WriteLine("");
				printerClass2.WriteLine(" CAJA   :   " + caja);
				printerClass2.WriteLine("");
				printerClass2.WriteLine(" POR CONCEPTO DE : ");
				if (Observaciones.ToString().Length > 0)
				{
					Observaciones = Observaciones.ToString().ToLower();
					printerClass2.GotoSixth(1.0);
					string[] array = Observaciones.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string obj in array)
					{
						System.Drawing.Font fuente = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
						int anchoEnPixeles = 250;
						List<string> list = DividirFrasePorAnchoFont(obj.Trim(), anchoEnPixeles, fuente);
						foreach (string item in list)
						{
							printerClass2.WriteLine("  " + item);
						}
					}
				}
				printerClass2.FeedPaper();
				printerClass2.AlignCenter();
				printerClass2.WriteLine("------------------------------ ");
				printerClass2.WriteLine(" Firma ");
				printerClass2.WriteLine("");
				printerClass2.AlignLeft();
				printerClass2.WriteLine("Nombre _ _ _ _ _ _ _ _ _ _ _ _ _ _ ");
				printerClass2.WriteLine("");
				printerClass2.WriteLine("CI _ _ _ _ _ _ _ _ _ _ _ _ _ _ ");
				printerClass2.WriteLine("");
				printerClass2.DrawLine();
				printerClass2.WriteLine("");
				printerClass2.WriteLine("PERSONAL : " + personal);
				printerClass2.CutPaper();
				printerClass2.EndDoc();
				printerClass2 = null;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
		return true;
	}

	public static bool printCuentaNewFormat(string miNIT, string nombre, string nit, string autorizacion, int _NroOrden, int visitaID, int AgruparPagoID, string MonedaString, double monto, string codigo, string _fechaLimiteEmision, DateTime fecha, bool esCopia, string Impresora, System.Data.DataTable dtProdsAFacturar, bool facturandoAnticipadamente)
	{
		checked
		{
			bool result;
			try
			{
				_ = DateAndTime.Now;
				ctlConfiguraciones ctlConfiguraciones2 = new ctlConfiguraciones();
				new ctlImpresoras();
				bool encontro = false;
				PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, Impresora, ref encontro, "Restotech Cuenta");
				if (!encontro)
				{
					Interaction.MsgBox("No puedo encontrar la impresora " + Impresora);
					result = false;
				}
				else
				{
					PrinterClass printerClass2 = printerClass;
					printerClass2.AlignCenter();
					printerClass2.NormalFont();
					printerClass2.Bold = false;
					string empresa = "";
					string sucursal = "";
					string direccion = "";
					string telefono = "";
					string dueño = "";
					string email = "";
					string actividadEconomica = "";
					string comentario = "";
					string ley = "";
					bool DatosFacturas = false;
					bool DobleFactura = false;
					bool FacturaBackupArchivo = false;
					bool FacturaExpress = false;
					bool soloAdminBorra = false;
					bool cant = false;
					string municipio = "";
					int idconfiguracion = default(int);
					bool conporcentaje = default(bool);
					double porcentaje = default(double);
					ctlConfiguraciones2.devolver(ref idconfiguracion, ref conporcentaje, ref porcentaje, ref empresa, ref sucursal, ref direccion, ref telefono, ref dueño, ref email, ref actividadEconomica, ref comentario, ref ley, ref soloAdminBorra, ref cant, ref DatosFacturas, ref DobleFactura, ref FacturaBackupArchivo, ref FacturaExpress, ref municipio);
					string[] array = empresa.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string text in array)
					{
						printerClass2.WriteLine(text.Trim());
					}
					if (dueño.Length > 0)
					{
						string[] array2 = ("De:" + dueño).Split(new string[3]
						{
							Environment.NewLine,
							"\n",
							"\r"
						}, StringSplitOptions.None);
						foreach (string text2 in array2)
						{
							printerClass2.WriteLine(text2.Trim());
						}
					}
					printerClass2.WriteLine(sucursal);
					if (telefono.Length > 0)
					{
						printerClass2.WriteLine("Telefono:" + telefono);
					}
					string[] array3 = direccion.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string text3 in array3)
					{
						printerClass2.WriteLine(text3.Trim());
					}
					printerClass2.Bold = true;
					printerClass2.WriteLine("CUENTA");
					printerClass2.WriteLine("");
					printerClass2.Bold = false;
					string text4 = "";
					text4 = ((configuration.gStyleBoliches1 != configuration.styleBolichesId.PollosBatman) ? " " : "  ");
					printerClass2.AlignLeft();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteLine(text4 + "CUENTA No.:" + Conversions.ToString(visitaID));
					printerClass2.DrawLine();
					printerClass2.WriteLine("");
					if (configuration.gTipoFacturacion == 1)
					{
						printerClass2.WriteLine(text4 + "Actividad Economica: " + actividadEconomica);
						printerClass2.WriteLine("");
					}
					printerClass2.WriteLine(text4 + "Fecha:" + Conversions.ToString(fecha));
					System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
					int num = 0;
					string text5 = text4 + "Señor(ES):";
					_ = text4 + "Señor(ES):";
					printerClass2.WriteLine(nombre);
					if (configuration.gTipoFacturacion == 1)
					{
						printerClass2.WriteLine(text4 + "NIT/CI:" + nit);
					}
					else
					{
						printerClass2.WriteLine(text4 + "NIT/CI/CEX:" + nit);
					}
					if (!configuration.gSupermercado)
					{
						printerClass2.DrawLine();
						if (_NroOrden > 0)
						{
							printerClass2.NormalBiggerFont();
							printerClass2.Bold = true;
							printerClass2.WriteLine(text4 + "ORDEN:" + Conversions.ToString(_NroOrden));
						}
					}
					else
					{
						printerClass2.WriteLine("");
						printerClass2.DrawLine();
					}
					printerClass2.Bold = false;
					printerClass2.NormalFont();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars(text4 + "CANT");
					printerClass2.GotoSixth(2.2);
					printerClass2.WriteChars(text4 + "CONCEPTO");
					printerClass2.GotoSixth(4.0);
					printerClass2.WriteChars(text4 + "P.UNIT");
					printerClass2.GotoSixth(5.1);
					printerClass2.WriteChars(text4 + "TOTAL");
					printerClass2.WriteLine("");
					printerClass2.DrawLine();
					printerClass2.NormalFont();
					ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
					System.Data.DataTable dataTable = new System.Data.DataTable();
					if (dtProdsAFacturar == null)
					{
						if (visitaID > 0)
						{
							dataTable = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacion(visitaID, 0);
						}
						else if (AgruparPagoID > 0)
						{
							dataTable = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(AgruparPagoID, 0);
						}
					}
					else
					{
						dataTable = dtProdsAFacturar;
					}
					double num2 = 0.0;
					double num3 = 0.0;
					int num4 = dataTable.Rows.Count - 1;
					for (int l = 0; l <= num4; l++)
					{
						if (!Operators.ConditionalCompareObjectGreater(Operators.AddObject(dataTable.Rows[l]["Debe"], dataTable.Rows[l]["Pago"]), 0, TextCompare: false))
						{
							continue;
						}
						if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[l]["precio"]), -1), -1, TextCompare: false))
						{
							ctlProductos ctlProductos2 = new ctlProductos();
							ctlProductos2.ToReturnIdbyName(Conversions.ToString(dataTable.Rows[l]["Producto"]));
							ctlProductos2.CargarPrecio();
							dataTable.Rows[l]["precio"] = ctlProductos2.getPrecio();
						}
						num2 = Conversions.ToDouble(Operators.AddObject(num2, Operators.AddObject(dataTable.Rows[l]["Debe"], dataTable.Rows[l]["Pago"])));
						num3 = ((!configuration.gRedonderCentavos) ? (num3 + Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.MultiplyObject(dataTable.Rows[l]["precio"], dataTable.Rows[l]["Cantidad"]), 2))) : (num3 + Conversions.ToDouble(Strings.FormatNumber(Math.Round(Convert.ToDouble(Operators.MultiplyObject(dataTable.Rows[l]["precio"], dataTable.Rows[l]["Cantidad"])) * 2.0) / 2.0, 1))));
						double num5 = Conversions.ToDouble(dataTable.Rows[l]["Cantidad"]);
						printerClass2.GotoSixth(1.0);
						text5 = "";
						printerClass2.WriteChars(text4 + num5.ToString("#0.##").ToString());
						if (configuration.gManejaComidaKilo)
						{
							printerClass2.GotoSixth(1.9);
						}
						else
						{
							printerClass2.GotoSixth(1.5);
						}
						for (num = 0; num < dataTable.Rows[l]["Producto"].ToString().Length; num++)
						{
							text5 += dataTable.Rows[l]["Producto"].ToString().Substring(num, 1);
							Size size = TextRenderer.MeasureText(text5, font);
							if (configuration.gManejaComidaKilo)
							{
								if (size.Width > 125)
								{
									text5 = text5.Substring(0, text5.Length - 2);
									break;
								}
							}
							else if (size.Width > 130)
							{
								text5 = text5.Substring(0, text5.Length - 2);
								break;
							}
						}
						if (!((Operators.CompareString(text5.ToUpper(), "SERVICIO", TextCompare: false) == 0) & (num2 == 0.0)))
						{
							printerClass2.WriteChars(text4 + text5.Trim());
							printerClass2.GotoSixth(4.5);
							double num6 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[l]["Precio"]), 0));
							if (Operators.CompareString(text5.ToUpper(), "SERVICIO", TextCompare: false) == 0)
							{
								num6 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[l]["Precio"]), 0));
							}
							printerClass2.WriteChars(text4 + num6.ToString("#0.##"));
							printerClass2.GotoSixth(5.2);
							if (configuration.gRedonderCentavos)
							{
								printerClass2.WriteChars(text4 + Strings.FormatNumber(Math.Round(Convert.ToDouble(num5 * num6) * 2.0) / 2.0, 1));
							}
							else
							{
								printerClass2.WriteChars(text4 + (num5 * num6).ToString("#0.##"));
							}
							printerClass2.WriteLine("");
						}
					}
					printerClass2.NormalFont();
					printerClass2.DrawLine();
					printerClass2.GotoSixth(1.0);
					if (num3 > monto)
					{
						printerClass2.WriteChars(text4 + "IMPORTE " + MonedaString + ": ");
						printerClass2.GotoSixth(5.1);
						printerClass2.WriteChars(text4 + Math.Round(Convert.ToDouble(num3), 2).ToString("##.#0"));
						printerClass2.WriteLine("");
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(text4 + "DESCUENTO " + MonedaString + ": ");
						printerClass2.GotoSixth(5.1);
						printerClass2.WriteChars(text4 + Math.Round(num3 - monto, 2).ToString("##.#0"));
						printerClass2.WriteLine("");
						printerClass2.WriteChars(text4 + "TOTAL " + MonedaString + ": ");
						printerClass2.GotoSixth(5.1);
						printerClass2.WriteChars(text4 + Math.Round(Convert.ToDouble(monto), 2).ToString("##.#0"));
						printerClass2.WriteLine("");
					}
					else
					{
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(text4 + "TOTAL " + MonedaString + ": ");
						printerClass2.GotoSixth(5.1);
						printerClass2.WriteChars(text4 + Math.Round(Convert.ToDouble(monto), 2).ToString("##.#0"));
						printerClass2.WriteLine("");
					}
					printerClass2.WriteLine("");
					printerClass2.AlignLeft();
					string text6 = new clsNumLetras2().Convertir(Math.Round(Convert.ToDouble(monto), 2));
					string text7 = "Son " + MonedaString + " : ";
					for (num = 0; num < text6.Length; num++)
					{
						text7 += text6.ToString().Substring(num, 1);
						if (TextRenderer.MeasureText(text7, font).Width > 230)
						{
							printerClass2.WriteLine(text4 + text7);
							text7 = "";
						}
					}
					printerClass2.WriteLine(text4 + text7);
					printerClass2.WriteLine("");
					printerClass2.WriteLine("No valido para credito fiscal");
					printerClass2.WriteLine("");
					if (comentario.Length > 0)
					{
						printerClass2.WriteLine("");
						printerClass2.WriteLine(comentario ?? "");
						printerClass2.WriteLine("");
					}
					printerClass2.AlignCenter();
					string text8 = "";
					text8 += "Para control interno";
					QRCodeEncoder qRCodeEncoder = new QRCodeEncoder();
					qRCodeEncoder.QRCodeEncodeMode = QRCodeEncoder.ENCODE_MODE.BYTE;
					qRCodeEncoder.QRCodeScale = int.Parse(Conversions.ToString(2));
					switch ("Medio (15%)")
					{
					case "Bajo (7%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.L;
						break;
					case "Medio (15%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.M;
						break;
					case "Alto (25%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.Q;
						break;
					case "Muy alto (30%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.H;
						break;
					}
					qRCodeEncoder.QRCodeVersion = 0;
					qRCodeEncoder.QRCodeBackgroundColor = Color.FromArgb(Color.White.ToArgb());
					qRCodeEncoder.QRCodeForegroundColor = Color.FromArgb(Color.Black.ToArgb());
					System.Drawing.Image image = null;
					try
					{
						image = qRCodeEncoder.Encode(text8, Encoding.UTF8);
					}
					catch (Exception ex)
					{
						ProjectData.SetProjectError(ex);
						Exception ex2 = ex;
						Interaction.MsgBox(ex2.Message, MsgBoxStyle.Critical);
						ProjectData.ClearProjectError();
					}
					printerClass2.PrintQRfactura1(image);
					printerClass2.AlignLeft();
					printerClass2.WriteLine(text4 + "\"ESTO ES SOLO UN  RECIBO");
					printerClass2.WriteLine(text4 + "DESTINADO UNICAMENTE PARA CONTROL");
					printerClass2.WriteLine(text4 + "NO TIENE VALIDEZ FISCAL, NI REEMPLAZA");
					printerClass2.WriteLine(text4 + "A UNA FACTURA.\"");
					printerClass2.CutPaper();
					printerClass2.EndDoc();
					printerClass2 = null;
					result = true;
				}
			}
			catch (Exception ex3)
			{
				ProjectData.SetProjectError(ex3);
				Exception ex4 = ex3;
				result = false;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public static bool printCuentaNewFormat2(string miNIT, string nombre, string nit, string autorizacion, int _NroOrden, int visitaID, int AgruparPagoID, string MonedaString, double monto, string codigo, string _fechaLimiteEmision, DateTime fecha, bool esCopia, string Impresora, System.Data.DataTable dtProdsAFacturar, bool facturandoAnticipadamente)
	{
		checked
		{
			bool result;
			try
			{
				_ = DateAndTime.Now;
				ctlConfiguraciones ctlConfiguraciones2 = new ctlConfiguraciones();
				new ctlImpresoras();
				bool encontro = false;
				PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, Impresora, ref encontro, "Restotech Cuenta");
				if (!encontro)
				{
					Interaction.MsgBox("No puedo encontrar la impresora " + Impresora);
					result = false;
				}
				else
				{
					PrinterClass printerClass2 = printerClass;
					printerClass2.AlignCenter();
					printerClass2.smallFont1();
					printerClass2.Bold = false;
					string empresa = "";
					string sucursal = "";
					string direccion = "";
					string telefono = "";
					string dueño = "";
					string email = "";
					string actividadEconomica = "";
					string comentario = "";
					string ley = "";
					bool DatosFacturas = false;
					bool DobleFactura = false;
					bool FacturaBackupArchivo = false;
					bool FacturaExpress = false;
					bool soloAdminBorra = false;
					bool cant = false;
					string municipio = "";
					int idconfiguracion = default(int);
					bool conporcentaje = default(bool);
					double porcentaje = default(double);
					ctlConfiguraciones2.devolver(ref idconfiguracion, ref conporcentaje, ref porcentaje, ref empresa, ref sucursal, ref direccion, ref telefono, ref dueño, ref email, ref actividadEconomica, ref comentario, ref ley, ref soloAdminBorra, ref cant, ref DatosFacturas, ref DobleFactura, ref FacturaBackupArchivo, ref FacturaExpress, ref municipio);
					string[] array = empresa.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string text in array)
					{
						printerClass2.WriteLine(text.Trim());
					}
					if (dueño.Length > 0)
					{
						string[] array2 = ("De:" + dueño).Split(new string[3]
						{
							Environment.NewLine,
							"\n",
							"\r"
						}, StringSplitOptions.None);
						foreach (string text2 in array2)
						{
							printerClass2.WriteLine(text2.Trim());
						}
					}
					printerClass2.WriteLine(sucursal);
					if (telefono.Length > 0)
					{
						printerClass2.WriteLine("Telefono:" + telefono);
					}
					string[] array3 = direccion.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string text3 in array3)
					{
						printerClass2.WriteLine(text3.Trim());
					}
					printerClass2.Bold = true;
					printerClass2.WriteLine("CUENTA");
					printerClass2.smallFont();
					if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.SantaMaria) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Sacura))
					{
						printerClass2.WriteLine("(No valido para credito fiscal)");
					}
					printerClass2.WriteLine("");
					printerClass2.Bold = false;
					string text4 = "";
					text4 = ((configuration.gStyleBoliches1 != configuration.styleBolichesId.PollosBatman) ? " " : "  ");
					printerClass2.NormalFont();
					printerClass2.AlignLeft();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteLine(text4 + "CUENTA No.: " + Conversions.ToString(visitaID));
					printerClass2.DrawLine();
					if (configuration.gTipoFacturacion == 1)
					{
						printerClass2.WriteLine(text4 + "Actividad Economica: " + actividadEconomica);
						printerClass2.WriteLine("");
					}
					printerClass2.WriteLine(text4 + "Fecha:" + Conversions.ToString(fecha));
					System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
					int num = 0;
					string text5 = text4 + "Señor(ES): ";
					_ = text4 + "Señor(ES): ";
					printerClass2.WriteLine(text5 + nombre);
					if (configuration.gTipoFacturacion == 1)
					{
						printerClass2.WriteLine(text4 + "NIT/CI:" + nit);
					}
					else
					{
						printerClass2.WriteLine(text4 + "NIT/CI/CEX:" + nit);
					}
					printerClass2.WriteLine(text4 + "COD.CLIENTE: " + nit);
					printerClass2.DrawLine();
					printerClass2.WriteLine("");
					if (_NroOrden > 0)
					{
						printerClass2.NormalBiggerFont();
						printerClass2.Bold = true;
						printerClass2.WriteLine(text4 + "ORDEN:" + Conversions.ToString(_NroOrden));
					}
					printerClass2.Bold = false;
					printerClass2.NormalFont();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars(text4 + "CANT");
					printerClass2.GotoSixth(2.2);
					printerClass2.WriteChars(text4 + "CONCEPTO");
					printerClass2.GotoSixth(4.0);
					printerClass2.WriteChars(text4 + "P.UNIT");
					printerClass2.GotoSixth(5.1);
					printerClass2.WriteChars(text4 + "TOTAL");
					printerClass2.WriteLine("");
					printerClass2.DrawLine();
					printerClass2.NormalFont();
					ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
					System.Data.DataTable dataTable = new System.Data.DataTable();
					if (dtProdsAFacturar == null)
					{
						if (visitaID > 0)
						{
							dataTable = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacion(visitaID, 0);
						}
						else if (AgruparPagoID > 0)
						{
							dataTable = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(AgruparPagoID, 0);
						}
					}
					else
					{
						dataTable = dtProdsAFacturar;
					}
					double num2 = 0.0;
					double num3 = 0.0;
					int num4 = dataTable.Rows.Count - 1;
					for (int l = 0; l <= num4; l++)
					{
						if (!Operators.ConditionalCompareObjectGreater(Operators.AddObject(dataTable.Rows[l]["Debe"], dataTable.Rows[l]["Pago"]), 0, TextCompare: false))
						{
							continue;
						}
						if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[l]["precio"]), -1), -1, TextCompare: false))
						{
							ctlProductos ctlProductos2 = new ctlProductos();
							ctlProductos2.ToReturnIdbyName(Conversions.ToString(dataTable.Rows[l]["Producto"]));
							ctlProductos2.CargarPrecio();
							dataTable.Rows[l]["precio"] = ctlProductos2.getPrecio();
						}
						num2 = Conversions.ToDouble(Operators.AddObject(num2, Operators.AddObject(dataTable.Rows[l]["Debe"], dataTable.Rows[l]["Pago"])));
						num3 = ((!configuration.gRedonderCentavos) ? (num3 + Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.MultiplyObject(dataTable.Rows[l]["precio"], dataTable.Rows[l]["Cantidad"]), 2))) : (num3 + Conversions.ToDouble(Strings.FormatNumber(Math.Round(Convert.ToDouble(Operators.MultiplyObject(dataTable.Rows[l]["precio"], dataTable.Rows[l]["Cantidad"])) * 2.0) / 2.0, 1))));
						double num5 = Conversions.ToDouble(dataTable.Rows[l]["Cantidad"]);
						printerClass2.GotoSixth(1.0);
						text5 = "";
						printerClass2.WriteChars(text4 + num5.ToString("#0.##").ToString());
						if (configuration.gManejaComidaKilo)
						{
							printerClass2.GotoSixth(1.9);
						}
						else
						{
							printerClass2.GotoSixth(1.5);
						}
						for (num = 0; num < dataTable.Rows[l]["Producto"].ToString().Length; num++)
						{
							text5 += dataTable.Rows[l]["Producto"].ToString().Substring(num, 1);
							Size size = TextRenderer.MeasureText(text5, font);
							if (configuration.gManejaComidaKilo)
							{
								if (size.Width > 125)
								{
									text5 = text5.Substring(0, text5.Length - 2);
									break;
								}
							}
							else if (size.Width > 130)
							{
								text5 = text5.Substring(0, text5.Length - 2);
								break;
							}
						}
						if (!((Operators.CompareString(text5.ToUpper(), "SERVICIO", TextCompare: false) == 0) & (num2 == 0.0)))
						{
							printerClass2.WriteChars(text4 + text5.Trim());
							printerClass2.GotoSixth(4.5);
							double num6 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[l]["Precio"]), 0));
							if (Operators.CompareString(text5.ToUpper(), "SERVICIO", TextCompare: false) == 0)
							{
								num6 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[l]["Precio"]), 0));
							}
							printerClass2.WriteChars(text4 + num6.ToString("#0.##"));
							printerClass2.GotoSixth(5.2);
							if (configuration.gRedonderCentavos)
							{
								printerClass2.WriteChars(text4 + Strings.FormatNumber(Math.Round(Convert.ToDouble(num5 * num6) * 2.0) / 2.0, 1));
							}
							else
							{
								printerClass2.WriteChars(text4 + (num5 * num6).ToString("#0.##"));
							}
							printerClass2.WriteLine("");
						}
					}
					printerClass2.smallFont1();
					printerClass2.DrawLine();
					printerClass2.GotoSixth(1.0);
					if (num3 > monto)
					{
						printerClass2.WriteChars(text4 + "IMPORTE " + MonedaString + ": ");
						printerClass2.GotoSixth(5.1);
						printerClass2.WriteChars(text4 + Math.Round(Convert.ToDouble(num3), 2).ToString("##.#0"));
						printerClass2.WriteLine("");
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(text4 + "DESCUENTO " + MonedaString + ": ");
						printerClass2.GotoSixth(5.1);
						printerClass2.WriteChars(text4 + Math.Round(num3 - monto, 2).ToString("##.#0"));
						printerClass2.WriteLine("");
						printerClass2.WriteChars(text4 + "TOTAL " + MonedaString + ": ");
						printerClass2.GotoSixth(5.1);
						printerClass2.WriteChars(text4 + Math.Round(Convert.ToDouble(monto), 2).ToString("##.#0"));
						printerClass2.WriteLine("");
					}
					else
					{
						printerClass2.GotoSixth(1.0);
						printerClass2.WriteChars(text4 + "TOTAL " + MonedaString + ": ");
						printerClass2.GotoSixth(5.1);
						printerClass2.WriteChars(text4 + Math.Round(Convert.ToDouble(monto), 2).ToString("##.#0"));
						printerClass2.WriteLine("");
					}
					printerClass2.WriteLine("");
					printerClass2.AlignLeft();
					string text6 = new clsNumLetras2().Convertir(Math.Round(Convert.ToDouble(monto), 2));
					string text7 = "Son " + MonedaString + " : ";
					for (num = 0; num < text6.Length; num++)
					{
						text7 += text6.ToString().Substring(num, 1);
						if (TextRenderer.MeasureText(text7, font).Width > 230)
						{
							printerClass2.WriteLine(text4 + text7);
							text7 = "";
						}
					}
					printerClass2.WriteLine(text4 + text7);
					string text8 = new clsPagos().devolverCuentaPorVisitaId(visitaID);
					if (text8.Length > 0)
					{
						printerClass2.GotoSixth(1.0);
						if (Operators.CompareString(text8.ToUpper(), "CAJA CHICA BS", TextCompare: false) == 0)
						{
							printerClass2.WriteLine(text4 + "PAGÓ:  EFECTIVO");
						}
						else
						{
							printerClass2.WriteLine(text4 + "PAGÓ:  " + text8.ToUpper() + " ");
						}
					}
					printerClass2.DrawLine();
					printerClass2.WriteLine("");
					if (comentario.Length > 0)
					{
						printerClass2.WriteLine("");
						printerClass2.WriteLine(comentario ?? "");
						printerClass2.WriteLine("");
					}
					printerClass2.AlignCenter();
					string text9 = "";
					text9 += "Para control interno";
					QRCodeEncoder qRCodeEncoder = new QRCodeEncoder();
					qRCodeEncoder.QRCodeEncodeMode = QRCodeEncoder.ENCODE_MODE.BYTE;
					qRCodeEncoder.QRCodeScale = int.Parse(Conversions.ToString(2));
					switch ("Medio (15%)")
					{
					case "Bajo (7%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.L;
						break;
					case "Medio (15%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.M;
						break;
					case "Alto (25%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.Q;
						break;
					case "Muy alto (30%)":
						qRCodeEncoder.QRCodeErrorCorrect = QRCodeEncoder.ERROR_CORRECTION.H;
						break;
					}
					qRCodeEncoder.QRCodeVersion = 0;
					qRCodeEncoder.QRCodeBackgroundColor = Color.FromArgb(Color.White.ToArgb());
					qRCodeEncoder.QRCodeForegroundColor = Color.FromArgb(Color.Black.ToArgb());
					System.Drawing.Image image = null;
					try
					{
						image = qRCodeEncoder.Encode(text9, Encoding.UTF8);
					}
					catch (Exception ex)
					{
						ProjectData.SetProjectError(ex);
						Exception ex2 = ex;
						Interaction.MsgBox(ex2.Message, MsgBoxStyle.Critical);
						ProjectData.ClearProjectError();
					}
					printerClass2.PrintQRfactura1(image);
					printerClass2.AlignCenter();
					printerClass2.smallFont1();
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Sacura) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SantaMaria))
					{
						printerClass2.WriteLine(text4 ?? "");
						printerClass2.WriteLine(text4 + "\"SR. CLIENTE,  A LA ENTREGA");
						printerClass2.WriteLine(text4 + "DEL PRODUCTO EXIJA SU FACTURA.\"");
					}
					else
					{
						printerClass2.WriteLine(text4 + "\"ESTO ES SOLO UN  RECIBO");
						printerClass2.WriteLine(text4 + "DESTINADO UNICAMENTE PARA CONTROL");
						printerClass2.WriteLine(text4 + "NO TIENE VALIDEZ FISCAL, NI REEMPLAZA");
						printerClass2.WriteLine(text4 + "A UNA FACTURA.\"");
					}
					printerClass2.CutPaper();
					printerClass2.EndDoc();
					printerClass2 = null;
					result = true;
				}
			}
			catch (Exception ex3)
			{
				ProjectData.SetProjectError(ex3);
				Exception ex4 = ex3;
				result = false;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public static string printAnularFactura(int nroFactura, string miNIT, string nombre, string nit, string autorizacion, int _NroOrden, int visitaID, int AgruparPagoID, string MonedaString, double monto, string codigo, DateTime _fechaLimiteEmision, DateTime fecha, bool esCopia, string Impresora, System.Data.DataTable dtProdsAFacturar, bool facturandoAnticipadamente, double cambio, int documentoSector)
	{
		checked
		{
			string result;
			try
			{
				new clsLogg().Insertar("factura", "1 De Celular, impresora: " + Impresora, 18);
				_ = DateAndTime.Now;
				ctlConfiguraciones ctlConfiguraciones2 = new ctlConfiguraciones();
				new ctlImpresoras();
				bool encontro = false;
				PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, Impresora, ref encontro, "Restotech Fact");
				if (!encontro)
				{
					result = "No puedo encontrar la impresora " + Impresora;
				}
				else
				{
					PrinterClass printerClass2 = printerClass;
					printerClass2.AlignCenter();
					printerClass2.NormalFont();
					printerClass2.Bold = false;
					string empresa = "";
					string sucursal = "";
					string direccion = "";
					string telefono = "";
					string dueño = "";
					string email = "";
					string actividadEconomica = "";
					string comentario = "";
					string ley = "";
					bool FacturaExpress = false;
					bool soloAdminBorra = false;
					bool cant = false;
					string municipio = "";
					int idconfiguracion = default(int);
					bool conporcentaje = default(bool);
					double porcentaje = default(double);
					bool DatosFacturas = default(bool);
					bool DobleFactura = default(bool);
					bool FacturaBackupArchivo = default(bool);
					ctlConfiguraciones2.devolver(ref idconfiguracion, ref conporcentaje, ref porcentaje, ref empresa, ref sucursal, ref direccion, ref telefono, ref dueño, ref email, ref actividadEconomica, ref comentario, ref ley, ref soloAdminBorra, ref cant, ref DatosFacturas, ref DobleFactura, ref FacturaBackupArchivo, ref FacturaExpress, ref municipio);
					string[] array = empresa.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string text in array)
					{
						printerClass2.WriteLine(text.Trim());
					}
					if (dueño.Length > 0)
					{
						string[] array2 = ("De:" + dueño).Split(new string[3]
						{
							Environment.NewLine,
							"\n",
							"\r"
						}, StringSplitOptions.None);
						foreach (string text2 in array2)
						{
							printerClass2.WriteLine(text2.Trim());
						}
					}
					printerClass2.WriteLine(sucursal);
					if (telefono.Length > 0)
					{
						printerClass2.WriteLine("Telefono:" + telefono);
					}
					string[] array3 = direccion.Split(new string[3]
					{
						Environment.NewLine,
						"\n",
						"\r"
					}, StringSplitOptions.None);
					foreach (string text3 in array3)
					{
						printerClass2.WriteLine(text3.Trim());
					}
					printerClass2.WriteLine("SCF 1");
					printerClass2.Bold = true;
					printerClass2.WriteLine("FACTURA ANULADA");
					printerClass2.WriteLine("");
					printerClass2.Bold = false;
					string text4 = "";
					text4 = ((configuration.gStyleBoliches1 != configuration.styleBolichesId.PollosBatman) ? " " : "  ");
					printerClass2.AlignLeft();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteLine(text4 + "NIT:" + miNIT);
					printerClass2.WriteLine(text4 + "FACTURA No.:" + Conversions.ToString(nroFactura));
					printerClass2.WriteLine(text4 + "AUTORIZACION No.:" + autorizacion.ToString());
					printerClass2.DrawLine();
					printerClass2.WriteLine("");
					printerClass2.WriteLine(text4 + "Actividad Economica: " + actividadEconomica);
					printerClass2.WriteLine("");
					printerClass2.WriteLine(text4 + "Fecha:" + Conversions.ToString(fecha));
					System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
					int l = 0;
					string text5 = text4 + "Señor(ES):";
					string text6 = text4 + "Señor(ES):";
					string text7 = "";
					for (; l < nombre.Length; l++)
					{
						text5 += nombre.ToString().Substring(l, 1);
						if (TextRenderer.MeasureText(text5, font).Width > 235)
						{
							text7 += nombre.Substring(l, 1);
						}
						else
						{
							text6 += nombre.Substring(l, 1);
						}
					}
					printerClass2.WriteLine(text6);
					if (text7.Trim().Length > 0)
					{
						printerClass2.WriteLine(text7);
					}
					printerClass2.WriteLine(text4 + "NIT/CI:" + nit);
					printerClass2.WriteLine("");
					printerClass2.DrawLine();
					printerClass2.Bold = false;
					printerClass2.NormalFont();
					printerClass2.GotoSixth(1.0);
					printerClass2.WriteChars(text4 + "CANT");
					printerClass2.GotoSixth(2.2);
					printerClass2.WriteChars(text4 + "CONCEPTO");
					printerClass2.GotoSixth(4.0);
					printerClass2.WriteChars(text4 + "P.UNIT");
					printerClass2.GotoSixth(5.1);
					printerClass2.WriteChars(text4 + "TOTAL");
					printerClass2.WriteLine("");
					printerClass2.DrawLine();
					printerClass2.NormalFont();
					ctlDetalleCuenta ctlDetalleCuenta2 = new ctlDetalleCuenta();
					System.Data.DataTable dataTable = new System.Data.DataTable();
					if (dtProdsAFacturar == null)
					{
						if (visitaID > 0)
						{
							dataTable = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacion(visitaID, documentoSector);
						}
						else if (AgruparPagoID > 0)
						{
							dataTable = ctlDetalleCuenta2.ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(AgruparPagoID, documentoSector);
						}
					}
					else
					{
						dataTable = dtProdsAFacturar;
					}
					double num = 0.0;
					double num2 = 0.0;
					int num3 = dataTable.Rows.Count - 1;
					for (int m = 0; m <= num3; m++)
					{
						if (!Operators.ConditionalCompareObjectGreater(Operators.AddObject(dataTable.Rows[m]["Debe"], dataTable.Rows[m]["Pago"]), 0, TextCompare: false))
						{
							continue;
						}
						if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[m]["precio"]), -1), -1, TextCompare: false))
						{
							ctlProductos ctlProductos2 = new ctlProductos();
							ctlProductos2.ToReturnIdbyName(Conversions.ToString(dataTable.Rows[m]["Producto"]));
							ctlProductos2.CargarPrecio();
							dataTable.Rows[m]["precio"] = ctlProductos2.getPrecio();
						}
						double num4;
						if ((AgruparPagoID > 0) & !facturandoAnticipadamente)
						{
							num = Conversions.ToDouble(Operators.AddObject(num, dataTable.Rows[m]["Pagando"]));
							num2 = Conversions.ToDouble(Operators.AddObject(num2, dataTable.Rows[m]["Pagando"]));
							num4 = Conversions.ToDouble(Operators.DivideObject(Operators.MultiplyObject(dataTable.Rows[m]["Pagando"], dataTable.Rows[m]["Cantidad"]), Operators.AddObject(dataTable.Rows[m]["Pago"], dataTable.Rows[m]["Debe"])));
						}
						else
						{
							num = Conversions.ToDouble(Operators.AddObject(num, Operators.AddObject(dataTable.Rows[m]["Debe"], dataTable.Rows[m]["Pago"])));
							num2 = ((!configuration.gRedonderCentavos) ? (num2 + Convert.ToDouble(VariableGeneral.toDecimalSIN(Operators.MultiplyObject(dataTable.Rows[m]["precio"], dataTable.Rows[m]["Cantidad"]), 2))) : (num2 + Conversions.ToDouble(Strings.FormatNumber(Math.Round(Convert.ToDouble(Operators.MultiplyObject(dataTable.Rows[m]["precio"], dataTable.Rows[m]["Cantidad"])) * 2.0) / 2.0, 1))));
							num4 = Conversions.ToDouble(dataTable.Rows[m]["Cantidad"]);
						}
						printerClass2.GotoSixth(1.0);
						text5 = "";
						printerClass2.WriteChars(text4 + num4.ToString("#0.##").ToString());
						if (configuration.gManejaComidaKilo)
						{
							printerClass2.GotoSixth(1.9);
						}
						else
						{
							printerClass2.GotoSixth(1.5);
						}
						for (l = 0; l < dataTable.Rows[m]["Producto"].ToString().Length; l++)
						{
							text5 += dataTable.Rows[m]["Producto"].ToString().Substring(l, 1);
							Size size = TextRenderer.MeasureText(text5, font);
							if (configuration.gManejaComidaKilo)
							{
								if (size.Width > 125)
								{
									text5 = text5.Substring(0, text5.Length - 2);
									break;
								}
							}
							else if (size.Width > 130)
							{
								text5 = text5.Substring(0, text5.Length - 2);
								break;
							}
						}
						if (!((Operators.CompareString(text5.ToUpper(), "SERVICIO", TextCompare: false) == 0) & (num == 0.0)))
						{
							printerClass2.WriteChars(text4 + text5.Trim());
							printerClass2.GotoSixth(4.5);
							double num5 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[m]["Precio"]), 0));
							if (Operators.CompareString(text5.ToUpper(), "SERVICIO", TextCompare: false) == 0)
							{
								num5 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[m]["Precio"]), 0));
							}
							printerClass2.WriteChars(text4 + num5.ToString("#0.##"));
							printerClass2.GotoSixth(5.2);
							if (configuration.gRedonderCentavos)
							{
								printerClass2.WriteChars(text4 + Strings.FormatNumber(Math.Round(Convert.ToDouble(num4 * num5) * 2.0) / 2.0, 1));
							}
							else
							{
								printerClass2.WriteChars(text4 + (num4 * num5).ToString("#0.##"));
							}
							printerClass2.WriteLine("");
						}
					}
					printerClass2.NormalFont();
					printerClass2.DrawLine();
					printerClass2.GotoSixth(1.0);
					printerClass2.GotoSixth(2.0);
					printerClass2.WriteChars(text4 + "TOTAL " + MonedaString + ": ");
					printerClass2.GotoSixth(5.1);
					printerClass2.WriteChars(text4 + Math.Round(Convert.ToDouble(monto), 2).ToString("##.#0"));
					printerClass2.WriteLine("");
					printerClass2.WriteLine("");
					printerClass2.AlignLeft();
					string text8 = new clsNumLetras2().Convertir(Math.Round(Convert.ToDouble(monto), 2));
					string text9 = "Son " + MonedaString + ":";
					for (l = 0; l < text8.Length; l++)
					{
						text9 += text8.ToString().Substring(l, 1);
						if (TextRenderer.MeasureText(text9, font).Width > 230)
						{
							printerClass2.WriteLine(text4 + text9);
							text9 = "";
						}
					}
					printerClass2.WriteLine(text4 + text9);
					printerClass2.WriteLine("");
					printerClass2.WriteLine("Cuenta: " + Conversions.ToString(visitaID));
					printerClass2.CutPaper();
					printerClass2.EndDoc();
					printerClass2 = null;
					result = "";
				}
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				result = ex2.Message;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public static bool printComandasDespacho(System.Data.DataTable dgvPedido, bool aumentoEnCuenta, int NroOrden, string PersonaQueRecoge, bool paraLlevar, string NombreMesero, string lblMesa, string txtMesa, bool imprimir, int VisitaId, string Direccion, [Optional][DefaultParameterValue("")] ref string error1)
	{
		checked
		{
			bool result;
			try
			{
				dgvPedido.AcceptChanges();
				System.Data.DataTable dataTable = dgvPedido.Copy();
				ctlImpresoras ctlImpresoras2 = new ctlImpresoras();
				new ctlProductos();
				ctlVisitas ctlVisitas2 = new ctlVisitas();
				if (!imprimir)
				{
					result = false;
				}
				else
				{
					string text = "";
					int num = 0;
					bool flag = false;
					dataTable.AcceptChanges();
					DataView dataView = new DataView(dataTable);
					dataView.Sort = "Impresora ASC";
					foreach (object item in dataView)
					{
						RuntimeHelpers.GetObjectValue(item);
						num++;
						text = "DESPACHO";
					}
					string[] array = new string[num * 4 + 1];
					string[] array2 = new string[num * 4 + 1];
					num = 0;
					text = "";
					foreach (object item2 in dataView)
					{
						object objectValue = RuntimeHelpers.GetObjectValue(item2);
						if (Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Cantidad" }, null), 0, TextCompare: false) && ((Operators.CompareString(text, NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString(), TextCompare: false) != 0) & (NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString().Length > 0)))
						{
							ctlImpresoras2.devolverNombreFisicoPorNombre(NewLateBinding.LateIndexGet(objectValue, new object[1] { "Impresora" }, null).ToString());
							string[] array3 = "DESPACHO".ToString().Split(';');
							foreach (string text2 in array3)
							{
								array[num] = text2;
								text = "DESPACHO";
								array2[num] = "DESPACHO";
								num++;
							}
						}
					}
					if (num == 0)
					{
						result = false;
					}
					else
					{
						string text3 = "";
						int num2 = num - 1;
						for (int j = 0; j <= num2; j++)
						{
							bool encontro = false;
							if (array[j].Length <= 0)
							{
								continue;
							}
							PrinterClass printerClass = new PrinterClass(MyProject.Application.Info.DirectoryPath, array[j], ref encontro, "Restotech Pedido");
							array[j] = "DESPACHO";
							if (!encontro)
							{
								error1 = "No puedo encontrar la impresora " + array[j] + " directory path " + MyProject.Application.Info.DirectoryPath;
								continue;
							}
							PrinterClass printerClass2 = printerClass;
							flag = true;
							double num3 = 0.0;
							printerClass2.AlignCenter();
							printerClass2.MaxFont();
							printerClass2.Bold = true;
							text3 = ((!paraLlevar) ? "PEDIDO" : ((!configuration.gComidaRapida) ? "PARA LLEVAR" : "PARA LLEVAR"));
							if (paraLlevar)
							{
								ctlVisitas2.SetID(VisitaId);
								int MesaID = 0;
								int ClienteID = 0;
								string Obs = "";
								ctlVisitas2.llenarclase(ref MesaID, ref ClienteID, ref Obs);
								if (Operators.CompareString(ctlVisitas2.DevolverTipoEnvio(VisitaId), "-", TextCompare: false) != 0)
								{
									text3 = ctlVisitas2.DevolverTipoEnvio(VisitaId);
								}
							}
							if (aumentoEnCuenta)
							{
								printerClass2.WriteLine(text3 + " - (Aumento)");
							}
							else
							{
								printerClass2.WriteLine(text3);
							}
							if (NroOrden > 0)
							{
								printerClass2.Bold = true;
								printerClass2.GotoSixth(1.0);
								printerClass2.NormalBiggerFont();
								printerClass2.WriteChars(" Orden: " + Conversions.ToString(NroOrden));
								printerClass2.WriteLine("");
								printerClass2.Bold = false;
							}
							if (PersonaQueRecoge.Length > 0)
							{
								printerClass2.GotoSixth(1.0);
								printerClass2.BigFont();
								printerClass2.WriteChars("Recoge: " + PersonaQueRecoge);
								printerClass2.WriteLine("");
								if (txtMesa.Length > 0)
								{
									printerClass2.AlignRight();
									if (Operators.CompareString(lblMesa, text3, TextCompare: false) == 0)
									{
										printerClass2.WriteChars(txtMesa.ToUpper());
										printerClass2.WriteLine("");
									}
									else
									{
										printerClass2.WriteChars(lblMesa + ": " + txtMesa);
										printerClass2.WriteLine("");
									}
								}
							}
							else
							{
								printerClass2.GotoSixth(1.0);
								printerClass2.BigFont();
								if (txtMesa.Length > 0)
								{
									printerClass2.AlignRight();
									if (Operators.CompareString(lblMesa, text3, TextCompare: false) == 0)
									{
										printerClass2.WriteChars(txtMesa.ToUpper());
										printerClass2.WriteLine("");
									}
									else
									{
										printerClass2.WriteChars(lblMesa + ": " + txtMesa);
										printerClass2.WriteLine("");
									}
								}
							}
							printerClass2.AlignLeft();
							if (ctlVisitas2.GetParaLlevarID() > 0)
							{
								ctlParaLLevar obj = new ctlParaLLevar();
								obj.SetParaLlevarID(ctlVisitas2.GetParaLlevarID());
								clsParaLLevar clsParaLLevar2 = obj.LlenarClase();
								string text4 = "";
								text4 = ((clsParaLLevar2._HoraRecoger.Day != DateAndTime.Today.Day) ? clsParaLLevar2._HoraRecoger.ToString("dd/MM/yy HH:mm") : clsParaLLevar2._HoraRecoger.ToString("HH:mm"));
								if (DateTime.Compare(clsParaLLevar2._HoraRecoger, DateAndTime.Now.AddMinutes(30.0)) > 0)
								{
									printerClass2.Bold = true;
									printerClass2.BigSmallerFont();
								}
								printerClass2.WriteChars("Recoge a " + text4);
								printerClass2.WriteLine("");
								printerClass2.Bold = false;
								printerClass2.NormalFont();
							}
							printerClass2.GotoSixth(1.0);
							printerClass2.NormalBiggerFont();
							printerClass2.GotoSixth(3.0);
							printerClass2.WriteLine(" " + DateTime.Now.ToString());
							printerClass2.DrawLine();
							printerClass2.BigFont();
							printerClass2.GotoSixth(1.0);
							printerClass2.WriteChars("Cant");
							printerClass2.GotoSixth(2.0);
							printerClass2.WriteChars("   Descripcion");
							printerClass2.WriteLine("");
							printerClass2.DrawLine();
							printerClass2.BigFont();
							DataView dataView2 = dataView;
							dataView2.RowFilter = "Impresora= '" + array2[j] + "'";
							System.Drawing.Font font = new System.Drawing.Font("FontA1x1", (float)printerClass2._FontSize);
							printerClass2.AlignLeft();
							int num4 = 0;
							string text5 = "";
							foreach (object item3 in dataView2)
							{
								object objectValue2 = RuntimeHelpers.GetObjectValue(item3);
								if (Operators.ConditionalCompareObjectEqual(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "ProductoID" }, null), 2, TextCompare: false))
								{
									num4 = Conversions.ToInteger(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null));
									text5 = Conversions.ToString(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null));
								}
								else
								{
									if (!Operators.ConditionalCompareObjectGreater(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null), 0, TextCompare: false))
									{
										continue;
									}
									printerClass2.GotoSixth(1.0);
									num3 = Conversions.ToDouble(Operators.AddObject(num3, Operators.MultiplyObject(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null), NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Precio" }, null))));
									double num5 = Conversions.ToDouble(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Cantidad" }, null));
									if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "ProductosCombo" }, null)), "").ToString().Length > 0)
									{
										printerClass2.WriteChars(num5.ToString("##.##") + "x");
									}
									else
									{
										printerClass2.WriteChars(num5.ToString("##.##") ?? "");
									}
									int num6 = 0;
									string text6 = "";
									string text7 = "";
									string text8 = "";
									int num7 = 230;
									NewLateBinding.LateIndexSet(objectValue2, new object[2]
									{
										"Producto",
										NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString()
									}, null);
									while (num6 < NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString().Length)
									{
										text6 += NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString().Substring(num6, 1);
										if (TextRenderer.MeasureText(text6, font).Width > num7)
										{
											text8 += NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString().Substring(num6, 1);
										}
										else
										{
											text7 += NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Producto" }, null).ToString().Substring(num6, 1);
										}
										num6++;
									}
									printerClass2.GotoSixth(2.0);
									printerClass2.WriteChars(text7);
									printerClass2.WriteLine("");
									if (text8.Trim().Length > 0)
									{
										printerClass2.GotoSixth(2.0);
										printerClass2.GotoSixth(2.0);
										printerClass2.WriteChars(text8.Trim());
										printerClass2.WriteLine("");
									}
									if (dataTable.Columns.Contains("AsistenteID"))
									{
										if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "AsistenteID" }, null)), "").ToString().Length > 0 && Operators.CompareString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "AsistenteID" }, null)), "").ToString(), "0", TextCompare: false) != 0)
										{
											ctlMeseros ctlMeseros2 = new ctlMeseros();
											string[] array4 = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "AsistenteID" }, null)), "").ToString().Split(',');
											if (array4.Length > 0)
											{
												string[] array5 = array4;
												foreach (string text9 in array5)
												{
													if (Operators.CompareString(text9, "", TextCompare: false) != 0)
													{
														ctlMeseros2.SetMeseroID(Conversions.ToInteger(text9));
														string text10 = ctlMeseros2.devolverNombre();
														if (Operators.CompareString(NombreMesero, text10, TextCompare: false) != 0)
														{
															printerClass2.WriteChars("  --" + text10);
															printerClass2.WriteLine("");
														}
													}
												}
											}
										}
									}
									else if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "MeseroID" }, null)), "").ToString().Length > 0 && Operators.CompareString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "MeseroID" }, null)), "").ToString(), "0", TextCompare: false) != 0)
									{
										ctlMeseros ctlMeseros3 = new ctlMeseros();
										string[] array6 = VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "MeseroID" }, null)), "").ToString().Split(',');
										if (array6.Length > 0)
										{
											string[] array7 = array6;
											foreach (string text11 in array7)
											{
												if (Operators.CompareString(text11, "", TextCompare: false) != 0)
												{
													ctlMeseros3.SetMeseroID(Conversions.ToInteger(text11));
													string text12 = ctlMeseros3.devolverNombre();
													if (Operators.CompareString(NombreMesero, text12, TextCompare: false) != 0)
													{
														printerClass2.WriteChars("  --" + text12);
														printerClass2.WriteLine("");
													}
												}
											}
										}
									}
									if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "ProductosCombo" }, null)), "").ToString().Length > 0)
									{
										string[] array8 = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "ProductosCombo" }, null).ToString().Split(',');
										string text13 = "";
										int num8 = 0;
										int num9 = 0;
										string[] array9 = array8;
										foreach (string text14 in array9)
										{
											if (Operators.CompareString(text13, text14, TextCompare: false) != 0)
											{
												if (num9 > 0)
												{
													ctlProductos ctlProductos2 = new ctlProductos();
													string[] array10 = text13.ToString().Split('-');
													string impresoraFisica = "";
													ctlProductos2.SetProductoID(Conversions.ToInteger(array10[0]));
													ctlProductos2.cargarDatosCombo(ref impresoraFisica);
													if (array10.Length > 1)
													{
														if (!(configuration.gCombosSeimprimenComoItem & configuration.gEnCombosSeimprimeTodo) && !configuration.gEnCombosSeimprimeTodo && Operators.CompareString(impresoraFisica, array[j], TextCompare: false) != 0 && !(impresoraFisica.ToString().Contains(array[j].ToString()) & impresoraFisica.Contains(";")))
														{
															array10[1] = "0";
														}
														if (Operators.CompareString(array10[1], "1", TextCompare: false) == 0)
														{
															printerClass2.GotoSixth(1.2);
															string text15 = "";
															text15 = ((Conversions.ToDouble(array10[2]) != 0.0) ? "  +" : "  -");
															printerClass2.WriteChars(text15 + Conversions.ToString(num8));
															printerClass2.GotoSixth(2.0);
															printerClass2.WriteChars(ctlProductos2.getNombre());
															printerClass2.WriteLine("");
														}
													}
													else if (array10[0].Length > 0)
													{
														printerClass2.GotoSixth(1.2);
														printerClass2.WriteChars("  +" + Conversions.ToString(num8));
														printerClass2.GotoSixth(2.0);
														printerClass2.WriteChars(ctlProductos2.getNombre());
														printerClass2.WriteLine("");
													}
												}
												text13 = text14;
												num8 = 1;
											}
											else
											{
												num8++;
											}
											num9++;
										}
										ctlProductos ctlProductos3 = new ctlProductos();
										string text16 = "";
										text16 = "DESPACHO";
										string[] array11 = text13.ToString().Split('-');
										ctlProductos3.SetProductoID(Conversions.ToInteger(array11[0]));
										if (array11.Length > 1)
										{
											if (!(configuration.gCombosSeimprimenComoItem & configuration.gEnCombosSeimprimeTodo) && !configuration.gEnCombosSeimprimeTodo && Operators.CompareString(text16, array[j], TextCompare: false) != 0 && !(text16.ToString().Contains(array[j].ToString()) & text16.Contains(";")))
											{
												array11[1] = "0";
											}
											if (Operators.CompareString(array11[1], "1", TextCompare: false) == 0)
											{
												printerClass2.GotoSixth(1.2);
												string text17 = "";
												text17 = ((Conversions.ToDouble(array11[2]) != 0.0) ? "  +" : "  -");
												printerClass2.WriteChars(text17 + Conversions.ToString(num8));
												printerClass2.GotoSixth(2.0);
												printerClass2.WriteChars(ctlProductos3.getNombre());
												printerClass2.WriteLine("");
											}
										}
										else if (Conversions.ToDouble(text13) != 0.0)
										{
											printerClass2.GotoSixth(1.2);
											printerClass2.WriteChars("  +" + Conversions.ToString(num8));
											printerClass2.GotoSixth(2.0);
											printerClass2.WriteChars(ctlProductos3.getNombre());
											printerClass2.WriteLine("");
										}
									}
									if (VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Observaciones" }, null)), "").ToString().Length > 0)
									{
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
										{
											printerClass2._FontSize += 2.0;
											printerClass2.Bold = true;
										}
										printerClass2.GotoSixth(1.5);
										string text18 = NewLateBinding.LateIndexGet(objectValue2, new object[1] { "Observaciones" }, null).ToString();
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO)
										{
											printerClass2.GotoSixth(2.0);
											text18 = "(" + text18 + ")";
										}
										string[] array12 = ("**" + text18).Split(new string[3]
										{
											Environment.NewLine,
											"\n",
											"\r"
										}, StringSplitOptions.None);
										foreach (string obj2 in array12)
										{
											string text19 = "-";
											string Obs = obj2;
											for (int m = 0; m < Obs.Length; m++)
											{
												char c = Obs[m];
												if (TextRenderer.MeasureText(text19 + Conversions.ToString(c), font).Width > num7 - 10)
												{
													printerClass2.WriteLine(text19);
													text19 = "  " + c;
												}
												else
												{
													text19 += Conversions.ToString(c);
												}
											}
											if (Operators.CompareString(text19, "", TextCompare: false) != 0)
											{
												printerClass2.WriteLine(text19);
											}
										}
										if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
										{
											printerClass2._FontSize -= 2.0;
											printerClass2.Bold = false;
										}
									}
									printerClass2.DrawLine();
								}
							}
							if (num4 > 0)
							{
								printerClass2.GotoSixth(1.0);
								printerClass2.WriteChars("  " + num4);
								printerClass2.GotoSixth(2.0);
								printerClass2.WriteChars(text5.ToString());
								printerClass2.WriteLine("");
							}
							printerClass2.BigFont();
							printerClass2.DrawLine();
							printerClass2.GotoSixth(1.0);
							if (!configuration.gComidaRapida)
							{
								printerClass2.WriteLine("");
								printerClass2.WriteChars("Por: " + NombreMesero);
							}
							else
							{
								printerClass2.WriteLine("");
								printerClass2.WriteLine("");
							}
							if (paraLlevar)
							{
								printerClass2.WriteLine("");
								printerClass2.WriteChars("................................");
							}
							printerClass2.CutPaper();
							printerClass2.EndDoc();
							printerClass2 = null;
						}
						result = flag;
					}
				}
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				error1 = ex2.Message;
				result = false;
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}
}
