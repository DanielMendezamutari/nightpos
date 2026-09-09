using System;
using System.Collections.Generic;
using System.Data;
using System.Globalization;
using System.IO;
using System.Net;
using System.Runtime.CompilerServices;
using System.Text.RegularExpressions;
using System.Threading;
using System.Windows.Forms;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

[StandardModule]
public sealed class VariableGeneral
{
	public static int gConfiguracionID = 1;

	public static string _sinNit2 = "0";

	public static string _SinNombre2 = "Consumidor Final";

	public static string MonedaString1 = "Bs.";

	public static int _horaCierreTurno = 8;

	public static double gProductoServicioPorcentaje;

	public static int gCobrosQR = 0;

	public static int gCobrosPagaTodo = 0;

	public static bool gPedidosYa = false;

	public static bool gFidelizacionLatam1 = false;

	public static bool gQRupones = false;

	public static string gQuestTag = "";

	public static ushort gEmitirFacturaSiat = 1;

	public static bool gConCantidadPersonas = false;

	public static bool gSgteMesaVisible = false;

	public static ushort gVersionSql = 0;

	public static string ApplicationName = "Control Consumo 2019";

	private static bool testDateTime;

	public static readonly int MyVersionProgram = 411;

	public static string setconcatStr(string var)
	{
		if (gVersionSql >= 2017)
		{
			return "STRING_AGG(" + var + ", ',')";
		}
		return "dbo.GROUP_CONCAT(" + var + ")";
	}

	public static bool FileReadyToRead(string filePath, int maxDuration)
	{
		int num = 0;
		while (num < maxDuration)
		{
			num = checked(num + 1);
			try
			{
				using (new StreamReader(filePath))
				{
					return true;
				}
			}
			catch (Exception projectError)
			{
				ProjectData.SetProjectError(projectError);
				Thread.Sleep(1000);
				ProjectData.ClearProjectError();
			}
		}
		return false;
	}

	public static bool IsValidEmailFormat(string s)
	{
		return Regex.IsMatch(s, "^([0-9a-zA-Z]([-\\.\\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\\w]*[0-9a-zA-Z]\\.)+[a-zA-Z]{2,9})$");
	}

	public static string ArmarFechaSIN(DateTime fecha)
	{
		return fecha.ToString("yyyy-MM-ddTHH:mm:ss.fff", CultureInfo.InvariantCulture);
	}

	public static decimal toDecimalSIN(object value, int cantDecimales)
	{
		if (decimal.TryParse(Convert.ToString(RuntimeHelpers.GetObjectValue(value)), out var result))
		{
			return Math.Round(result, cantDecimales, MidpointRounding.AwayFromZero);
		}
		return 0m;
	}

	public static string armarBolean(bool aux)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			if (aux)
			{
				return "TRUE";
			}
			return "FALSE";
		}
		if (configuration.gMODO_ACCESS == 2)
		{
			if (aux)
			{
				return "TRUE";
			}
			return "FALSE";
		}
		if (aux)
		{
			return "'TRUE'";
		}
		return "'FALSE'";
	}

	public static string armarBolean(bool? aux)
	{
		if (aux.HasValue)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				if (aux.Value)
				{
					return "TRUE";
				}
				return "FALSE";
			}
			if (configuration.gMODO_ACCESS == 2)
			{
				if (aux == true)
				{
					return "TRUE";
				}
				return "FALSE";
			}
			if (aux == true)
			{
				return "'TRUE'";
			}
			return "'FALSE'";
		}
		return Conversions.ToString(Value: false);
	}

	public static string armarBolean(int aux)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			if (aux != 0)
			{
				return "TRUE";
			}
			return "FALSE";
		}
		if (configuration.gMODO_ACCESS == 2)
		{
			if (aux != 0)
			{
				return "TRUE";
			}
			return "FALSE";
		}
		if (aux != 0)
		{
			return "'TRUE'";
		}
		return "'FALSE'";
	}

	public static string armarBoleanSQL(bool aux)
	{
		if (aux)
		{
			return "'TRUE'";
		}
		return "'FALSE'";
	}

	public static string armarWildCards()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return "*";
		}
		return "%";
	}

	public static string DevolverValorLlaveForanea(int? variable)
	{
		if (variable.HasValue)
		{
			return variable.ToString();
		}
		return "null";
	}

	public static int DevolverValorLlaveForaneaCero(int? variable)
	{
		if (variable.HasValue)
		{
			return variable.Value;
		}
		return 0;
	}

	public static void TestDate()
	{
		if (BD.ConsultWithOutAlerts("Update _TestDate set Date1 = " + configuration.CaracterFecha + "2019/20/1 0:0:0" + configuration.CaracterFecha) != 0)
		{
			testDateTime = true;
		}
		else
		{
			testDateTime = false;
		}
		if (configuration.gMODO_ACCESS == 2)
		{
			testDateTime = false;
		}
	}

	public static void workingWithDate(ref string from1, ref string to1, int horaCierre, DateTime fecha)
	{
		if (fecha.Hour < horaCierre)
		{
			to1 = configuration.CaracterFecha + armarSoloLaFecha(fecha) + " " + Conversions.ToString(horaCierre) + ":00:00" + configuration.CaracterFecha;
			from1 = configuration.CaracterFecha + armarSoloLaFecha(fecha.AddDays(-1.0)) + " " + Conversions.ToString(horaCierre) + ":00:00" + configuration.CaracterFecha;
		}
		else
		{
			from1 = configuration.CaracterFecha + armarSoloLaFecha(fecha) + " " + Conversions.ToString(horaCierre) + ":00:00" + configuration.CaracterFecha;
			to1 = configuration.CaracterFecha + armarSoloLaFecha(fecha.AddDays(1.0)) + " " + Conversions.ToString(horaCierre) + ":00:00" + configuration.CaracterFecha;
		}
	}

	public static string ArmarFecha(DateTime fecha, bool Checked)
	{
		if (Checked)
		{
			if (configuration.gMODO_ACCESS == 1)
			{
				if (!testDateTime)
				{
					return "#" + Conversions.ToString(fecha.Year) + "/" + Conversions.ToString(fecha.Month) + "/" + Conversions.ToString(fecha.Day) + " " + Conversions.ToString(fecha.Hour) + ":" + Conversions.ToString(fecha.Minute) + ":" + Conversions.ToString(fecha.Second) + "#";
				}
				return "#" + Conversions.ToString(fecha.Year) + "/" + Conversions.ToString(fecha.Day) + "/" + Conversions.ToString(fecha.Month) + " " + Conversions.ToString(fecha.Hour) + ":" + Conversions.ToString(fecha.Minute) + ":" + Conversions.ToString(fecha.Second) + "#";
			}
			return "'" + fecha.ToString("yyyyMMdd HH:mm:ss", CultureInfo.InvariantCulture) + "'";
		}
		return "NULL";
	}

	public static string ArmarFechaMysql(DateTime fecha)
	{
		return "'" + Conversions.ToString(fecha.Year) + "/" + Conversions.ToString(fecha.Month) + "/" + Conversions.ToString(fecha.Day) + " " + Conversions.ToString(fecha.Hour) + ":" + Conversions.ToString(fecha.Minute) + ":" + Conversions.ToString(fecha.Second) + "'";
	}

	public static string ArmarFecha(DateTime fecha)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			if (!testDateTime)
			{
				return "#" + Conversions.ToString(fecha.Year) + "/" + Conversions.ToString(fecha.Month) + "/" + Conversions.ToString(fecha.Day) + " " + Conversions.ToString(fecha.Hour) + ":" + Conversions.ToString(fecha.Minute) + ":" + Conversions.ToString(fecha.Second) + "#";
			}
			return "#" + Conversions.ToString(fecha.Year) + "/" + Conversions.ToString(fecha.Day) + "/" + Conversions.ToString(fecha.Month) + " " + Conversions.ToString(fecha.Hour) + ":" + Conversions.ToString(fecha.Minute) + ":" + Conversions.ToString(fecha.Second) + "#";
		}
		return "'" + fecha.ToString("yyyyMMdd HH:mm:ss", CultureInfo.InvariantCulture) + "'";
	}

	public static string ArmarFechaSTR(DateTime fecha)
	{
		return Conversions.ToString(fecha.Day) + "/" + Conversions.ToString(fecha.Month) + "/" + Conversions.ToString(fecha.Year) + " " + Conversions.ToString(fecha.Hour) + ":" + Conversions.ToString(fecha.Minute);
	}

	public static string ArmarSoloFechaSTR(DateTime fecha)
	{
		return Conversions.ToString(fecha.Day) + "/" + Conversions.ToString(fecha.Month) + "/" + Conversions.ToString(fecha.Year);
	}

	public static string ArmarFechaSinCaracteres(DateTime fecha)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			if (!testDateTime)
			{
				return Conversions.ToString(fecha.Year) + "/" + Conversions.ToString(fecha.Month) + "/" + Conversions.ToString(fecha.Day) + " " + Conversions.ToString(fecha.Hour) + ":" + Conversions.ToString(fecha.Minute) + ":" + Conversions.ToString(fecha.Second);
			}
			return Conversions.ToString(fecha.Year) + "/" + Conversions.ToString(fecha.Day) + "/" + Conversions.ToString(fecha.Month) + " " + Conversions.ToString(fecha.Hour) + ":" + Conversions.ToString(fecha.Minute) + ":" + Conversions.ToString(fecha.Second);
		}
		return fecha.ToString("yyyyMMdd HH:mm:ss", CultureInfo.InvariantCulture);
	}

	public static string ArmarFechaSQL(DateTime fecha)
	{
		return "'" + fecha.ToString("yyyyMMdd HH:mm:ss", CultureInfo.InvariantCulture) + "'";
	}

	public static string armarSoloLaHora(DateTime fecha)
	{
		return fecha.ToString("HH:mm:ss", CultureInfo.InvariantCulture);
	}

	public static string armarSoloLaFecha(DateTime fecha)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			if (!testDateTime)
			{
				return Conversions.ToString(fecha.Year) + "/" + Conversions.ToString(fecha.Month) + "/" + Conversions.ToString(fecha.Day);
			}
			return Conversions.ToString(fecha.Year) + "/" + Conversions.ToString(fecha.Day) + "/" + Conversions.ToString(fecha.Month);
		}
		return fecha.ToString("yyyyMMdd", CultureInfo.InvariantCulture);
	}

	public static string armarSoloLaFecha2(DateTime fecha)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			if (!testDateTime)
			{
				return Conversions.ToString(fecha.Year) + "-" + Conversions.ToString(fecha.Month) + "-" + Conversions.ToString(fecha.Day);
			}
			return Conversions.ToString(fecha.Year) + "-" + Conversions.ToString(fecha.Day) + "-" + Conversions.ToString(fecha.Month);
		}
		return fecha.ToString("yyyy-MM-dd", CultureInfo.InvariantCulture);
	}

	public static string armarSoloLaFechaMDA(DateTime fecha)
	{
		return Conversions.ToString(fecha.Month) + "-" + Conversions.ToString(fecha.Day) + "-" + Conversions.ToString(fecha.Year);
	}

	public static string armarSoloLaFechaDMA(DateTime fecha)
	{
		return Conversions.ToString(fecha.Day) + "-" + Conversions.ToString(fecha.Month) + "-" + Conversions.ToString(fecha.Year);
	}

	public static string EsNumero(string inptstr)
	{
		if ((Operators.CompareString(inptstr, "", TextCompare: false) == 0) | Information.IsDBNull(inptstr))
		{
			return "0";
		}
		return inptstr;
	}

	public static string armarNumeroTarjetaSin(string nroTarjeta)
	{
		checked
		{
			if (nroTarjeta.Length >= 8)
			{
				string text = nroTarjeta.Substring(0, 4);
				int num = nroTarjeta.Length - 4 - 1;
				for (int i = 4; i <= num; i++)
				{
					text += "0";
				}
				text += nroTarjeta.Substring(nroTarjeta.Length - 4, 4);
				if (Operators.CompareString(text.Replace("0", ""), "", TextCompare: false) == 0)
				{
					return "0";
				}
				return text;
			}
			return "0";
		}
	}

	public static object NZ(object S, object Def)
	{
		if (S == null)
		{
			return Def;
		}
		if (Information.IsDBNull(RuntimeHelpers.GetObjectValue(S)))
		{
			return Def;
		}
		return S;
	}

	public static object ExisteImpresora(string PrinterName1)
	{
		return true;
	}

	public static bool CheckForInternetConnection()
	{
		bool result;
		try
		{
			result = ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Dollhouse) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bonita)) || (MyProject.Computer.Network.Ping("www.google.com") ? true : false);
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

	public static void updateActualizador()
	{
		string text = Application.StartupPath + "\\ActualizadorRestotech.exe";
		try
		{
			if (File.Exists(text))
			{
				MyProject.Computer.FileSystem.RenameFile(text, "ActualizadorRestotech.ToBeDeleted");
			}
			string address = "https://toptech.com.bo/Restomenu_Update/ActualizadorRestotech.exe";
			using WebClient webClient = new WebClient();
			webClient.DownloadFile(address, text);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
		try
		{
			if (File.Exists(text))
			{
				File.Delete(Application.StartupPath + "\\ActualizadorRestotech.ToBeDeleted");
				return;
			}
			string file = text.ToString().Replace(".exe", ".ToBeDeleted");
			MyProject.Computer.FileSystem.RenameFile(file, "ActualizadorRestotech.exe");
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			ProjectData.ClearProjectError();
		}
	}

	public static void updateSync()
	{
		string text = Application.StartupPath + "\\Sync\\RestoSync.dtsx";
		if (!File.Exists(text))
		{
			return;
		}
		try
		{
			if (File.Exists(text))
			{
				MyProject.Computer.FileSystem.RenameFile(text, "RestoSync.ToBeDeleted");
			}
			string address = "https://toptech.com.bo/Restomenu_Update/RestoSync.dtsx";
			using WebClient webClient = new WebClient();
			webClient.DownloadFile(address, text);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
		try
		{
			if (File.Exists(text))
			{
				File.Delete(Application.StartupPath + "\\Sync\\RestoSync.ToBeDeleted");
				return;
			}
			string text2 = text.ToString().Replace(".dtsx", ".ToBeDeleted");
			if (File.Exists(text2))
			{
				MyProject.Computer.FileSystem.RenameFile(text2, "RestoSync.dtsx");
			}
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			ProjectData.ClearProjectError();
		}
	}

	public static void actualizarMiWebServiceDll()
	{
		try
		{
			if (configuration.gMODO_ACCESS == 1 || configuration.gComidaRapida)
			{
				return;
			}
			string directoryPath = MyProject.Application.Info.DirectoryPath;
			List<string> list = new List<string>();
			if (Directory.Exists(directoryPath))
			{
				string[] directories = Directory.GetDirectories(directoryPath);
				foreach (string path in directories)
				{
					if (new DirectoryInfo(path).Name.StartsWith("Servicio"))
					{
						list.Add(new DirectoryInfo(path).FullName);
					}
				}
			}
			if (list.Count <= 0)
			{
				return;
			}
			foreach (string item in list)
			{
				string text = item + "\\bin";
				if (File.Exists(text + "\\ControlConsumoLib.dll"))
				{
					File.Delete(text + "\\ControlConsumoLib.dll");
					File.Copy("ControlConsumoLib.dll", text + "\\ControlConsumoLib.dll");
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

	public static bool TestNewField(int gBdVersion)
	{
		if (gBdVersion <= 0)
		{
			Interaction.MsgBox("No puede tener config <= 0");
			return false;
		}
		string[] array = new string[3] { "\\Xml", "\\Facturas", "\\Backups" };
		if (configuration.gTipoFacturacion == 2)
		{
			string[] array2 = array;
			foreach (string text in array2)
			{
				string path = MyProject.Application.Info.DirectoryPath + text;
				if (!Directory.Exists(path))
				{
					Directory.CreateDirectory(path);
				}
			}
		}
		if (gBdVersion == MyVersionProgram)
		{
			return false;
		}
		string text2 = "";
		text2 = ((configuration.gMODO_ACCESS != 1) ? "varchar(500)" : "LONGTEXT");
		if (configuration.gMODO_ACCESS == 1)
		{
			if (BD.ConsultWithOutAlerts("select top 1 AppVersion from Configuraciones") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD AppVersion integer ");
				BD.ConsultaModificar("Configuraciones", "AppVersion=0", "2=2");
			}
		}
		else if (Information.IsDBNull(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select COL_LENGTH('Configuraciones', 'AppVersion') ").Rows[0][0])))
		{
			BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD AppVersion integer ");
			BD.ConsultaModificar("Configuraciones", "AppVersion=0", "2=2");
		}
		int num = 0;
		if (gBdVersion <= 0)
		{
			Interaction.MsgBox("Algo esta mal, contacte con el proveedor.");
			return false;
		}
		if (gBdVersion <= 1)
		{
			gBdVersion = 1;
			BD.ConsultaModificar("Configuraciones", "AppVersion=1", "AppVersion is null");
			if (BD.ConsultWithOutAlerts("select top 1 nit from BlackList") == 0)
			{
				BD.ConsultWithOutAlerts("CREATE TABLE BlackList(\t\t  \t\tBlackListID integer NOT NULL,\t  \t\tNIT varchar(50),\t  \t\tObservacion varchar(200),\t  \t\tPRIMARY KEY (BlackListID))");
			}
			if (BD.ConsultWithOutAlerts("select top 1 CantidadMinima from Productos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD CantidadMinima integer ");
				BD.ConsultaModificar("Productos", "CantidadMinima=0", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 ID from Asistentes") == 0)
			{
				BD.ConsultWithOutAlerts("CREATE TABLE Asistentes(\t\t  \t\tID integer NOT NULL,\t  \t\tNombreCorto varchar(100),\t  \t\tNombreCompleto varchar(100),\t  \t\tPRIMARY KEY (ID))");
			}
			if (BD.ConsultWithOutAlerts("select top 1 CantidadPaquete from Productos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD CantidadPaquete integer ");
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD DiasUtiles integer ");
				BD.ConsultaModificar("Productos", "CantidadPaquete=0", "2=2");
				BD.ConsultaModificar("Productos", "DiasUtiles=0", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 Cantidad from DetalleCuentas_Paquetes") == 0)
			{
				BD.ConsultWithOutAlerts("CREATE TABLE DetalleCuentas_Paquetes(\t\t  \t\tDetalleCuenta_PaqueteID integer NOT NULL,\t  \t\tCantidad integer,\t  \t\tCantidadUsada integer,\t  \t\tFechaExpiracion dateTime,\t  \t\tDetalleCuentaID integer NOT NULL,PRIMARY KEY (DetalleCuenta_PaqueteID))");
			}
			if (BD.ConsultWithOutAlerts("select top 1 UsoPaqueteID from UsoPaquetes") == 0)
			{
				BD.ConsultWithOutAlerts("CREATE TABLE UsoPaquetes(\t\t  \t\tUsoPaqueteID integer NOT NULL,\t  \t\tFechaUso dateTime,\t  \t\tDetalleCuenta_PaqueteID integer , PRIMARY KEY (UsoPaqueteID), CONSTRAINT fkDetalleCuentas_Paquetes FOREIGN KEY (DetalleCuenta_PaqueteID) REFERENCES DetalleCuentas_Paquetes(DetalleCuenta_PaqueteID) )");
			}
			if (BD.ConsultWithOutAlerts("select top 1 Categoria_ImpresoraID from Categorias_Impresoras") == 0)
			{
				BD.ConsultWithOutAlerts("CREATE TABLE Categorias_Impresoras(      CategoriaImpresoraID integer NOT NULL,     SalonID integer NULL,     CategoriaID integer  NULL,     ImpresoraID integer  NULL, PRIMARY KEY(CategoriaImpresoraID))");
			}
			if (BD.ConsultWithOutAlerts("select top 1 Movimientos_TurnoID from Movimientos_Turnos") == 0)
			{
				BD.ConsultWithOutAlerts("CREATE TABLE Movimientos_Turnos(\t\t  \t\tMovimientos_TurnoID integer NOT NULL,\t  \t\tMovimientoID integer NOT NULL,\t  \t\tTurnoID integer NOT NULL, PRIMARY KEY(Movimientos_TurnoID))");
			}
			if (BD.ConsultWithOutAlerts("select top 1 EscogePersonal from Productos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD EscogePersonal  bit NULL ");
				BD.ConsultaModificar("Productos", "EscogePersonal=0", "EscogePersonal is null");
			}
			if (BD.ConsultWithOutAlerts("select top 1 TomoPedidoMeseroID from DetalleCuenta") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta ADD TomoPedidoMeseroID integer NULL, CONSTRAINT fkDetalleCuenta_TomoPedidoMesero FOREIGN KEY (TomoPedidoMeseroID) REFERENCES Meseros(MeseroID) ");
				BD.ConsultaModificar("DetalleCuenta", "TomoPedidoMeseroID=MeseroID", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 correo from Clientes") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD Correo varchar(100) ");
				BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD Cumpleanos date ");
				BD.ConsultaModificar("Clientes", "Correo=''", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 SimboloID from Mesas") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Mesas ADD SimboloID integer ");
			}
			if (BD.ConsultWithOutAlerts("select top 1 SimboloId from Simbolo") == 0)
			{
				BD.ConsultWithOutAlerts("CREATE TABLE Simbolo(\t\t  \t\tSimboloId integer NOT NULL,\t  \t\tposicionX integer NOT NULL,\t  \t\tposicionY integer NOT NULL,\t \t  \t\ttamanoX integer NOT NULL,\t\t  \t\ttamanoY integer NOT NULL,\t\t  \t\trotacion integer NOT NULL,\t\t  \t\ttipo varchar(50) NULL,\t  \t\ttemp_vb varchar(50) NULL,  \t\tdescripcion varchar(50) NULL, PRIMARY KEY(SimboloId)\t  \t )\t\t ");
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.shiwu && Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("select count(*) from CategoriasProduccion").Rows[0][0], 0, TextCompare: false))
			{
				BD.ConsultaInsertar("1,'Carnes'", "CategoriasProduccion");
			}
			if (BD.ConsultWithOutAlerts("select top 1 Direccion from ParaLlevar") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE ParaLlevar ADD Direccion varchar(255) ");
			}
			if (Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("select count(*) from ParaLlevar").Rows[0][0], 0, TextCompare: false))
			{
				BD.ConsultaInsertar("1,'Para Llevar','',0,NULL,'',''", "ParaLlevar");
			}
			if (BD.ConsultWithOutAlerts("select top 1 Observacion from Facturas") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD FechaAnulacion datetime,Observacion varchar(200),personalID integer ");
			}
			if (BD.ConsultWithOutAlerts("select top 1 SalonID from Salones") == 0)
			{
				BD.ConsultWithOutAlerts(" \tCREATE TABLE Salones(\t\t  \t\tSalonID integer NOT NULL,\t  \t\tNombre varchar(50) NULL\t  \t )\t\t ");
				BD.ConsultWithOutAlerts("ALTER TABLE Mesas ADD SalonID Integer ");
				BD.ConsultWithOutAlerts("ALTER TABLE Simbolo ADD SalonID Integer ");
				BD.ConsultaInsertar("1,'Salon 1'", "Salones");
				BD.ConsultaModificar("Mesas", "SalonId=1", "2=2");
			}
			BD.ConsultWithOutAlerts("ALTER TABLE DetalleProductosCompra ALTER COLUMN CostoUnitario float");
			if (BD.ConsultWithOutAlerts("select top 1 Codigo from Productos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Codigo varchar(20) ");
				BD.ConsultaModificar("Productos", "Codigo =ID", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 Codigo from TiposProductos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD Codigo varchar(20) ");
				BD.ConsultaModificar("TiposProductos", "Codigo =TipoProductoID", "2=2");
			}
			if (configuration.gManejaTurnos)
			{
				if (BD.ConsultWithOutAlerts("select top 1 Observaciones  from Turnos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Turnos ADD Observaciones varchar(250) ");
					BD.ConsultaModificar("Turnos", "Observaciones=''", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select top 1 PC from Turnos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Turnos ADD PC varchar(100) ");
					BD.ConsultaModificar("Turnos", "PC='" + MyProject.Computer.Name + "'", "2=2");
				}
			}
			else
			{
				BD.ConsultWithOutAlerts("DROP tABLE Turnos");
				if (configuration.gMODO_ACCESS == 1)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Turnos(\tTurnoID integer NOT NULL,\tFechaIni datetime NULL,\tFechaFin datetime NULL,\tNro integer NULL,\tPersonalID integer NULL,\tMontoIni money NULL,\tObservaciones varchar(250) NULL,\tPC varchar(100) NULL,\tMontoFin money NULL,\tMontoIniDolar money NULL,\tMontoFinDolar money NULL , PRIMARY KEY ( \tTurnoID ))");
				}
				else
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Turnos(\t[TurnoID] [int] NOT NULL,\t[FechaIni] [datetime] NULL,\t[FechaFin] [datetime] NULL,\t[Nro] [int] NULL,\t[PersonalID] [int] NULL,\t[MontoIni] [money] NULL,\t[Observaciones] [varchar](250) NULL,\t[PC] [varchar](100) NULL,\t[MontoFin] [money] NULL,\t[MontoIniDolar] [money] NULL,\t[MontoFinDolar] [money] NULL, CONSTRAINT [PK_Turnos] PRIMARY KEY CLUSTERED (\t[TurnoID] ASC)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]) ON [PRIMARY]");
				}
			}
			if (BD.ConsultWithOutAlerts("select top 1 CuentaID  from Pagos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Pagos ADD CuentaID integer ");
			}
			if (BD.ConsultWithOutAlerts("select top 1 MontoBs  from Pagos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Pagos ADD MontoBs money; ");
				BD.ConsultaModificar("Pagos", "MontoBs=Monto", "2=2");
				BD.ConsultWithOutAlerts("ALTER TABLE Pagos drop COLUMN Monto;");
			}
			if (BD.ConsultWithOutAlerts("select top 1 MontoDolares  from Pagos") != 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Pagos DROP COLUMN MontoDolares");
			}
			if (configuration.gManejaTurnos)
			{
				if (BD.ConsultWithOutAlerts("select top 1 MontoFin  from Turnos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Turnos ADD MontoFin money ");
					BD.ConsultaModificar("Turnos", "MontoFin=0", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select top 1 MontoIni  from Turnos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Turnos ADD MontoIni money ");
					BD.ConsultaModificar("Turnos", "MontoIni=0", "MontoIni is null");
				}
				if (BD.ConsultWithOutAlerts("select top 1 MontoIniDolar  from Turnos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Turnos ADD MontoIniDolar money ");
					BD.ConsultaModificar("Turnos", "MontoIniDolar=0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Turnos ADD MontoFinDolar money ");
					BD.ConsultaModificar("Turnos", "MontoFinDolar=0", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select top 1 PC from Turnos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Turnos ADD PC varchar(100) ");
					BD.ConsultaModificar("Turnos", "PC =''", "2=2");
				}
			}
			if (BD.ConsultWithOutAlerts("select top 1 CuentaID from Cuentas") == 0)
			{
				BD.ConsultWithOutAlerts("CREATE TABLE Cuentas(\t\t  \t\tCuentaID integer NOT NULL,\t  \t\tNombre varchar(255) NULL,\t  \t\tBanco varchar(255) NULL,\t  \t\tTipoCuenta varchar(100) NULL,\t  \t\tNro integer NULL,\t  \t\tObservacion varchar(255) NULL,\t  \t\tActiva bit NULL,\t  \t\tMoneda bit NULL\t, PRIMARY KEY(CuentaID)\t  \t )\t\t ");
				BD.ConsultaInsertar("1,'Caja chica Bs','','',0,''," + armarBolean(1) + "," + armarBolean(0), "Cuentas");
				BD.ConsultaInsertar("2,'Caja chica $Us','','',0,''," + armarBolean(1) + "," + armarBolean(1), "Cuentas");
				BD.ConsultaInsertar("3,'Tarjeta','','',0,''," + armarBolean(1) + "," + armarBolean(0), "Cuentas");
			}
			if (BD.ConsultWithOutAlerts("select top 1 MovimientoID from Movimientos") == 0)
			{
				BD.ConsultWithOutAlerts(" CREATE TABLE Movimientos(\t\t  \t\tMovimientoID integer NOT NULL,\t  \t\tFecha datetime NULL,\t  \t\tMonto money NULL,\t  \t\tDescripcion varchar(100) NULL,\t  \t\tObservacion varchar(255) NULL,\t  \t\tTipoCambio money NULL,\t  \t\tCuentaID integer NULL,\t  \t\tGastoID integer NULL,\t  \t\tTurnoID integer NULL, PRIMARY KEY(MovimientoID)\t);\t ");
			}
			if (BD.ConsultWithOutAlerts("select top 1 PC from Movimientos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Movimientos ADD PC varchar(100) ");
				BD.ConsultaModificar("Movimientos", "PC =''", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 CuentaID from Gastos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Gastos ADD CuentaID integer ");
				BD.ConsultaModificar("Gastos", "CuentaID =1", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 CategoriaProduccionID from CategoriasProduccion") == 0)
			{
				BD.ConsultWithOutAlerts(" CREATE TABLE CategoriasProduccion(\t\t  \t\tCategoriaProduccionID integer NOT NULL,\t  \t\tNombre varchar(100) NULL, PRIMARY KEY(CategoriaProduccionID)\t);\t ");
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD CategoriaProduccionID integer NULL");
			}
			if (BD.ConsultWithOutAlerts("select top 1 ConTarjeta  from Pagos") != 0)
			{
				BD.ConsultaModificar("Pagos", ("CuentaID=" + Conversions.ToString(3)) ?? "", "ConTarjeta = " + armarBolean(1));
				BD.ConsultWithOutAlerts("ALTER TABLE Pagos DROP COLUMN ConTarjeta");
			}
		}
		if (gBdVersion < 3)
		{
			gBdVersion = 3;
			BD.ConsultaModificar("Configuraciones", "AppVersion=3", "AppVersion is null");
			if (configuration.gMODO_ACCESS == 1)
			{
				BD.ConsultaModificar("TiposProductos", "Codigo=Left(Descripcion,20)", "2=2");
			}
			else
			{
				BD.ConsultaModificar("TiposProductos", "Codigo=SUBSTRING(Descripcion,0,20)", "2=2");
			}
			if (configuration.gStyleBoliches1 < configuration.styleBolichesId.Bless)
			{
				if (BD.ConsultWithOutAlerts("select top 1 ProductoUsoID from ProductosUsos") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE ProductosUsos(\t\t  \t\tProductoUsoID integer NOT NULL,\t  \t\tCantidad float,\t  \t\tProductoID integer,\t  \t\tDetalleCuentaID integer,\t  \t\tPRIMARY KEY (ProductoUsoID))");
				}
			}
			else if (BD.ConsultWithOutAlerts("select top 1 ProductoUsoID from ProductosUsos") == 0)
			{
				BD.ConsultWithOutAlerts("CREATE TABLE ProductosUsos(\t\t     ProductoUsoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tCantidad float not null,\t  \t\tProductoID integer not null,\t  \t\tDetalleCuentaID integer not null,\t  \t\tPRIMARY KEY (ProductoUsoID))");
			}
			if (BD.ConsultWithOutAlerts("select top 1 Email from Configuraciones") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD Email varchar(250) ");
				BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD ActividadEconomica varchar(250) ");
				BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD Comentario  varchar(250) ");
				BD.ConsultaModificar("Configuraciones", "Email =''", "2=2");
				BD.ConsultaModificar("Configuraciones", "ActividadEconomica =''", "2=2");
				BD.ConsultaModificar("Configuraciones", "Comentario =''", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 AsistenteID from DetalleCuentaIntermediaria") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuentaIntermediaria ADD  AsistenteID int");
			}
		}
		if (gBdVersion < 4)
		{
			gBdVersion = 4;
			BD.ConsultaModificar("Configuraciones", "AppVersion=4", "1=1");
			if (BD.ConsultWithOutAlerts("select top 1 SubTipo from TiposGastos") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE TiposGastos ADD SubTipo  varchar(100) ");
				BD.ConsultaModificar("TiposGastos", "SubTipo=''", "2=2");
			}
		}
		if (gBdVersion < 5)
		{
			gBdVersion = 5;
			BD.ConsultaModificar("Configuraciones", "AppVersion=5", "1=1");
			if (configuration.gStyleBoliches1 < configuration.styleBolichesId.Bless)
			{
				if (BD.ConsultWithOutAlerts("select top 1 id from DetalleCuentaIntermediaria") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE DetalleCuentaIntermediaria(\t\t  \t\tId integer NOT NULL,\t  \t\tCantidad integer,\t  \t\tVisitaoID integer,\t  \t\tProductoID integer,\t  \t\tComentarios varchar(200),\t  \t\tMeseroID integer,\t  \t\tMesaID integer,\t  \t\tPedidoID integer,\t  \t\tAsistenteID integer,\t  \t\tPRIMARY KEY (Id))");
				}
				if (BD.ConsultWithOutAlerts("select top 1 id from Programacion") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Programacion(\t\t  \t\tId integer NOT NULL,\t  \t\tFechaIni DateTime,\t  \t\tFechaFin DateTime,\t  \t\testado integer,\t  \t\tPersonalProgramoID integer,\t  \t\tMeserosID integer,\t  \t\tMesasID integer,\t  \t\tClienteID integer,\t  \t\tObservaciones varchar(200),\t  \t\tPRIMARY KEY (Id))");
				}
				if (BD.ConsultWithOutAlerts("select top 1 id from ProgramacionServicio") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE ProgramacionServicio(\t\t  \t\tId integer NOT NULL,\t  \t\tProductosID integer,\t  \t\tCantidad integer,\t  \t\tProgramacionID integer,\t  \t\tPRIMARY KEY (Id))");
				}
			}
			else
			{
				if (BD.ConsultWithOutAlerts("select top 1 Id from DetalleCuentaIntermediaria") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE DetalleCuentaIntermediaria(\t\t     Id " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tCantidad integer,\t  \t\tVisitaID integer,\t  \t\tProductoID integer,\t  \t\tComentarios varchar(200),\t  \t\tMeseroID integer,\t  \t\tMesaID integer,\t  \t\tPedidoID integer,\t  \t\tAsistenteID integer,\t  \t\tPRIMARY KEY (Id))");
				}
				if (BD.ConsultWithOutAlerts("select top 1 Id from Programacion") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Programacion(\t\t     Id " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFechaIni DateTime,\t  \t\tFechaFin DateTime,\t  \t\tEstado integer,\t  \t\tPersonalProgramoID integer,\t  \t\tMeserosID integer,\t  \t\tMesasID integer,\t  \t\tClienteID integer,\t  \t\tObservaciones varchar(200),\t  \t\tPRIMARY KEY (Id))");
				}
				if (BD.ConsultWithOutAlerts("select top 1 Id from ProgramacionServicio") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE ProgramacionServicio(\t\t     Id " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tProductoID integer,\t  \t\tCantidad integer,\t  \t\tProgramacionID integer,\t  \t\tPRIMARY KEY (Id))");
				}
			}
		}
		if (gBdVersion < 6)
		{
			gBdVersion = 6;
			BD.ConsultaModificar("Configuraciones", "AppVersion=6", "1=1");
			if (BD.ConsultWithOutAlerts("select top 1 Activo from Mesas") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Mesas ADD Activo bit NULL ");
				BD.ConsultaModificar("Mesas", "Activo=1", "2=2");
			}
		}
		if (gBdVersion < 7)
		{
			gBdVersion = 7;
			BD.ConsultaModificar("Configuraciones", "AppVersion=7", "1=1");
			if (BD.ConsultWithOutAlerts("select top 1 Ley from Configuraciones") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD Ley varchar(255) ");
				BD.ConsultaModificar("Configuraciones", "Ley ='Ley No. 453: Los productos deben\r suministrarse en condiciones de\r inocuidad, calidad y seguridad'", "Ley is null");
			}
		}
		if (gBdVersion < 12)
		{
			if (gBdVersion < 12)
			{
				gBdVersion = 12;
				BD.ConsultaModificar("Configuraciones", "AppVersion=" + Conversions.ToString(gBdVersion), "1=1");
				if (configuration.gStyleBoliches1 < configuration.styleBolichesId.Bless)
				{
					if (BD.ConsultWithOutAlerts("select top 1 ObservacionId from Observaciones") == 0)
					{
						BD.ConsultWithOutAlerts("CREATE TABLE Observaciones(\t\t  \t\tObservacionId integer NOT NULL,\t  \t\tDetalleCuentaID integer,\t  \t\tObservacion varchar(255),\t  \t\tPRIMARY KEY (ObservacionId))");
					}
				}
				else if (BD.ConsultWithOutAlerts("select top 1 ObservacionId from Observaciones") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Observaciones(\t\t     ObservacionId " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tDetalleCuentaID integer,\t  \t\tObservacion varchar(255),\t  \t\tPRIMARY KEY (ObservacionId))");
				}
			}
			if (BD.ConsultWithOutAlerts("select top 1 Saldo from Clientes") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD Saldo Float ");
				BD.ConsultaModificar("Clientes", "Saldo=0", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 Descuento from Clientes") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD Descuento Float ");
				BD.ConsultaModificar("Clientes", "Descuento=0", "2=2");
			}
			if (BD.ConsultWithOutAlerts("select top 1 Porcentaje from Meseros") == 0)
			{
				BD.ConsultWithOutAlerts("ALTER TABLE Meseros ADD Porcentaje Float ");
				BD.ConsultaModificar("Meseros", "Porcentaje=0", "2=2");
			}
		}
		if (gBdVersion < 13)
		{
			gBdVersion = 13;
			BD.ConsultaModificar("Configuraciones", "AppVersion=" + Conversions.ToString(gBdVersion), "1=1");
			if (configuration.gStyleBoliches1 < configuration.styleBolichesId.Bless)
			{
				if (BD.ConsultWithOutAlerts("select top 1 AnticipoID from Anticipos") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Anticipos(\t\t  \t\tAnticipoId integer NOT NULL,\t  \t\tFecha dateTime,\t  \t\tMonto Float,\t  \t\tMontoRestante float,\t  \t\tClienteID integer,\t  \t\tPRIMARY KEY (AnticipoID))");
				}
				if (BD.ConsultWithOutAlerts("select top 1 AnticipoCuentaID from AnticiposCuentas") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE AnticiposCuentas(\t\t  \t\tAnticipoCuentaID integer NOT NULL,\t  \t\tAnticipoID integer,\t  \t\tDetalleCuentaID integer,\t  \t\tPRIMARY KEY (AnticipoCuentaID))");
				}
			}
			else
			{
				if (BD.ConsultWithOutAlerts("select top 1 AnticipoID from Anticipos") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Anticipos(\t\t     AnticipoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFecha dateTime,\t  \t\tMonto Float,\t  \t\tMontoRestante float,\t  \t\tClienteID integer,\t  \t\tPRIMARY KEY (AnticipoID))");
				}
				if (BD.ConsultWithOutAlerts("select top 1 AnticipoCuentaID from AnticiposCuentas") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE AnticiposCuentas(\t\t     AnticipoCuentaID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tAnticipoID integer,\t  \t\tDetalleCuentaID integer,\t  \t\tPRIMARY KEY (AnticipoCuentaID))");
				}
			}
		}
		checked
		{
			if (gBdVersion < 14)
			{
				gBdVersion = 14;
				BD.ConsultaModificar("Configuraciones", "AppVersion=" + Conversions.ToString(gBdVersion), "1=1");
				DataTable dataTable = BD.ConsultaVer("select CuentaID from Cuentas where CuentaID = 4");
				if (configuration.gStyleBoliches1 < configuration.styleBolichesId.Bless)
				{
					if (dataTable.Rows.Count > 0)
					{
						int num2 = Conversions.ToInteger(Operators.SubtractObject(BD.ConsultaVer("select max(CuentaID) from Cuentas").Rows[0][0], 3));
						BD.ConsultWithOutAlerts("insert  into Cuentas (CuentaID , Nombre, Banco,TipoCuenta,Nro,Observacion,Activa,Moneda)  select CuentaID + " + Conversions.ToString(num2) + ", Nombre, Banco,TipoCuenta,Nro,Observacion,Activa,Moneda from Cuentas where CuentaID =4");
						BD.ConsultaModificar("Cuentas", ("Nombre='Caja Anticipo ', Banco='',TipoCuenta='', Nro= 0, Observacion='', Activa=" + armarBolean(0) + ",Moneda=" + armarBolean(0)) ?? "", "CuentaID= 4");
						num2 += 4;
						BD.ConsultaModificar("Gastos", "CuentaID=" + Conversions.ToString(num2), "CuentaID= 4");
						BD.ConsultaModificar("Pagos", "CuentaID=" + Conversions.ToString(num2), "CuentaID= 4");
						BD.ConsultaModificar("Movimientos", "CuentaID=" + Conversions.ToString(num2), "CuentaID= 4");
					}
					else
					{
						BD.ConsultaInsertar("4,'Caja Anticipo','','',0,''," + armarBolean(0) + "," + armarBolean(0), "Cuentas(CuentaID , Nombre, Banco,TipoCuenta,Nro,Observacion,Activa,Moneda)");
					}
				}
				else if (dataTable.Rows.Count > 0)
				{
					BD.ConsultWithOutAlerts("insert  into Cuentas select Nombre, Banco,TipoCuenta,Nro,Observacion,Activa,Moneda from Cuentas where CuentaID =4");
					BD.ConsultaModificar("Cuentas", ("Nombre='Caja Anticipo', Banco='',TipoCuenta='', Nro= 0, Observacion='', Activa=" + armarBolean(0) + ",Moneda=" + armarBolean(0)) ?? "", "CuentaID= 4");
					int num3 = Conversions.ToInteger(BD.ConsultaVer("select max(CuentaID) from Cuentas").Rows[0][0]);
					BD.ConsultaModificar("Gastos", "CuentaID=" + Conversions.ToString(num3), "CuentaID= 4");
					BD.ConsultaModificar("Pagos", "CuentaID=" + Conversions.ToString(num3), "CuentaID= 4");
					BD.ConsultaModificar("Movimientos", "CuentaID=" + Conversions.ToString(num3), "CuentaID= 4");
				}
				else
				{
					BD.ConsultaInsertar("'Caja Anticipo','','',0,''," + armarBolean(0) + "," + armarBolean(0), "Cuentas");
					Operators.ConditionalCompareObjectNotEqual(BD.ConsultaVer("select max(CuentaID) from Cuentas").Rows[0][0], 4, TextCompare: false);
				}
			}
			if (configuration.gStyleBoliches1 != configuration.styleBolichesId.Restomenu && BD.ConsultWithOutAlerts("select top 1 ConfiguracionID from CodigosFacturas") == 0)
			{
				gBdVersion = 14;
			}
			if (gBdVersion < 15)
			{
				gBdVersion = 15;
				BD.ConsultaModificar("Configuraciones", "AppVersion=15", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 ConfiguracionID from CodigosFacturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CodigosFacturas ADD ConfiguracionID integer ");
					BD.ConsultaModificar("CodigosFacturas", "ConfiguracionID=1", "2=2");
				}
			}
			if (gBdVersion < 16)
			{
				gBdVersion = 16;
				BD.ConsultaModificar("Configuraciones", "AppVersion=16", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 Comision from Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Comision float ");
					BD.ConsultaModificar("Productos", "Comision=0", "2=2");
				}
			}
			if (gBdVersion < 17)
			{
				gBdVersion = 17;
				BD.ConsultaModificar("Configuraciones", "AppVersion=17", "1=1");
				if (BD.ConsultWithOutAlerts("Select top 1 ReferidoPor from Clientes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD ReferidoPor integer ");
					BD.ConsultaModificar("Clientes", "ReferidoPor=0", "2=2");
				}
			}
			if (gBdVersion < 18)
			{
				gBdVersion = 18;
				BD.ConsultaModificar("Configuraciones", "AppVersion=18", "1=1");
				if (BD.ConsultWithOutAlerts("Select top 1 EsDeposito from Cuentas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Cuentas ADD EsDeposito bit ");
					BD.ConsultaModificar("Cuentas", "EsDeposito=" + armarBolean(0), "2=2");
				}
			}
			if (gBdVersion < 19)
			{
				gBdVersion = 19;
				BD.ConsultaModificar("Configuraciones", "AppVersion=19", "1=1");
				if (BD.ConsultWithOutAlerts("Select top 1 Motociclista from ParaLlevar") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ParaLLevar ADD Motociclista integer ");
					BD.ConsultaModificar("ParaLlevar", "Motociclista=0", "2=2");
				}
				if (BD.ConsultWithOutAlerts("Select top 1 FacturaCredito from Clientes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD FacturaCredito bit");
					BD.ConsultaModificar("Clientes", "FacturaCredito=0", "2=2");
				}
			}
			if (gBdVersion < 20)
			{
				gBdVersion = 20;
				BD.ConsultaModificar("Configuraciones", "AppVersion=20", "1=1");
				if (BD.ConsultWithOutAlerts("Select top 1 Monto from AnticiposCuentas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE AnticiposCuentas ADD Monto float ");
					BD.ConsultaModificar("AnticiposCuentas", "Monto=0", "2=2");
				}
			}
			if (gBdVersion < 21)
			{
				gBdVersion = 21;
				BD.ConsultaModificar("Configuraciones", "AppVersion=21", "1=1");
				if (BD.ConsultWithOutAlerts("Select top 1 PacienteID from Pacientes") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Pacientes(\t\t     PacienteID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT, " : " integer IDENTITY(1, 1) Not NULL,") + " \t\tNombre varchar(100),\t  \t\tedad integer,\t   \tSexo bit,\t  \t\tDireccion varchar(250),\t  \t\tFechaNacimiento datetime,\t  \t\ttelefono integer,\t  \t\tAPP varchar(250),\t  \t\tActividadLaboral varchar(100),\t  \t\tEstadoCivil varchar(100),\t  \t\tCant_Hijos integer,\t  \t\tAlergiaMedicamentos varchar(250),\t  \t\tCirugias varchar(250),\t  \t\tActividadFisica varchar(250),\t  \t\tObservaciones varchar(250),\t  \t\tHEA varchar(250),\t  \t\tMC varchar(100),\t  \t\tTratamientos varchar(250),\t  \t\tPrecio float,\t  \t\tPRIMARY KEY (PacienteID))");
				}
				if (BD.ConsultWithOutAlerts("Select top 1 SeguimientoID from Seguimiento") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Seguimiento(\t\t     SeguimientoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFecha  datetime,\t  \t\tPeso  float,\t  \t\tEspalda  float,\t   \tPecho  float,\t  \t\tHombros  float,\t  \t\tBrazoD  float,\t  \t\tBrazoI float,\t  \t\tCinturaAlta  float,\t  \t\tCinturaMedia  float,\t  \t\tCinturaBaja  float,\t  \t\tCaderas  float,\t  \t\tGluteos float,\t  \t\tPiernaD  float,\t  \t\tPiernaI  float,\t  \t\tGemeloD  float,\t  \t\tGemeloI  float,\t  \t\tObservacion  varchar(250),\t  \t\tPacienteID  integer,\t  \t\tPRIMARY KEY (SeguimientoID))");
				}
			}
			if (gBdVersion < 22)
			{
				gBdVersion = 22;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "1=1");
				if (BD.ConsultWithOutAlerts("Select top 1 Descripcion from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD Descripcion varchar(100) ");
					BD.ConsultaModificar("Configuraciones", "Descripcion=ActividadEconomica", "2=2");
				}
			}
			if (gBdVersion < 23)
			{
				gBdVersion = 23;
				BD.ConsultaModificar("Configuraciones", "AppVersion=" + Conversions.ToString(gBdVersion), "1=1");
				if (BD.ConsultWithOutAlerts("Select top 1 Aux from Facturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD Aux varchar(100) ");
					BD.ConsultaModificar("Facturas", "Aux=''", "2=2");
				}
			}
			if (gBdVersion < 24)
			{
				gBdVersion = 24;
				BD.ConsultaModificar("Configuraciones", "AppVersion=" + Conversions.ToString(gBdVersion), "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 AlmacenID from Almacenes") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Almacenes(\t\t     AlmacenID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tnombre  varchar(250),\t  \t\tdescripcion  varchar(250),\t  \t\tPRIMARY KEY (AlmacenID))");
					BD.ConsultaInsertar("'Primario',''", "Almacenes(nombre,descripcion )");
					BD.ConsultWithOutAlerts("CREATE TABLE Traspasos(\t\t     TraspasoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFecha  datetime,\t  \t\tObservacion  varchar(250),\t  \t\tAlmacenID  integer,\t Entrante bit,  \t\tPRIMARY KEY (TraspasoID))");
					BD.ConsultWithOutAlerts("CREATE TABLE DetallesTraspasos(\t\t     DetalleTraspasoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tCantidad float ,CantidadFinal float,\t  \t\tObservacion  varchar(250) ,\t  \t\tTraspasoID  integer ,\t ProductoID integer,  \t\tPRIMARY KEY (DetalleTraspasoID))");
				}
			}
			if (gBdVersion < 25)
			{
				gBdVersion = 25;
				BD.ConsultaModificar("Configuraciones", "AppVersion=" + Conversions.ToString(gBdVersion), "1=1");
				BD.ConsultaModificar("Productos", "cantidadML=1", "cantidadML=0 and TienePreparacion=" + armarBolean(1));
			}
			if (gBdVersion < 26)
			{
				gBdVersion = 26;
				BD.ConsultaModificar("Configuraciones", "AppVersion=26", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 Activo from Clientes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD Activo bit");
					BD.ConsultaModificar("Clientes", "Activo=1", "2=2");
				}
			}
			if (gBdVersion < 27)
			{
				gBdVersion = 27;
				BD.ConsultaModificar("Configuraciones", "AppVersion=27", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 ImprimioCuenta from Visitas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Visitas ADD ImprimioCuenta int");
					BD.ConsultaModificar("Visitas", "ImprimioCuenta=0", "2=2");
				}
			}
			if (gBdVersion < 28)
			{
				gBdVersion = 28;
				BD.ConsultaModificar("Configuraciones", "AppVersion=28", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 ip from Almacenes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Almacenes ADD IP varchar(50)");
					BD.ConsultWithOutAlerts("ALTER TABLE Almacenes ADD Instancia varchar(50)");
					BD.ConsultWithOutAlerts("ALTER TABLE Almacenes ADD Usuario varchar(50)");
					BD.ConsultWithOutAlerts("ALTER TABLE Almacenes ADD Pass varchar(50)");
					BD.ConsultaModificar("Almacenes", "IP=''", "2=2");
					BD.ConsultaModificar("Almacenes", "Instancia=''", "2=2");
					BD.ConsultaModificar("Almacenes", "Usuario=''", "2=2");
					BD.ConsultaModificar("Almacenes", "Pass=''", "2=2");
				}
			}
			if (gBdVersion < 29)
			{
				gBdVersion = 29;
				BD.ConsultaModificar("Configuraciones", "AppVersion=29", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 TipoCambio from Gastos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Gastos ADD TipoCambio Float ");
					BD.ConsultaModificar("Gastos", "TipoCambio=1", "2=2");
				}
			}
			if (gBdVersion < 30)
			{
				gBdVersion = 30;
				BD.ConsultaModificar("Configuraciones", "AppVersion=30", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 Observacion from PreProcesamientos") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE PreProcesamientos(       PreprocesamientoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + "   Fecha datetime,     Observacion varchar(200),     UsuarioID integer,     PRIMARY KEY (PreprocesamientoID))");
				}
			}
			if (gBdVersion < 31)
			{
				gBdVersion = 31;
				BD.ConsultaModificar("Configuraciones", "AppVersion=31", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 Cantidad from PreProcesamientosDe") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE PreProcesamientosDe(       PreprocesamientoDeID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + "   Cantidad float,     ProductoID integer,     PreprocesamientoID integer,     PRIMARY KEY (PreprocesamientoDeID))");
				}
				if (BD.ConsultWithOutAlerts("select top 1 Cantidad from PreProcesamientosPara") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE PreProcesamientosPara(       PreprocesamientoParaID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + "   Cantidad float,     ProductoID integer,     PreprocesamientoID integer,     PRIMARY KEY (PreprocesamientoParaID))");
				}
			}
			if (gBdVersion < 32)
			{
				gBdVersion = 32;
				BD.ConsultaModificar("Configuraciones", "AppVersion=32", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 Direccion from Clientes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD Direccion varchar(250) ");
					BD.ConsultaModificar("Clientes", "Direccion=''", "2=2");
				}
			}
			if (gBdVersion < 33)
			{
				gBdVersion = 33;
				BD.ConsultaModificar("Configuraciones", "AppVersion=33", "1=1");
				if (BD.ConsultWithOutAlerts("select top 1 ArqueoID from Arqueo") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Arqueo(       ArqueoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + "  Fecha datetime,    B200 integer,    B100 integer,    B50 integer,    B20 integer,    B10 integer,    B5 integer,    B2 integer,    B1 integer,    C50 integer,    C20 integer,    C10 integer,    D50 integer,    D100 integer,    D20 integer,    D10 integer,    Tarjetas Money,   PersonalID integer,    TurnoID integer,    PRIMARY KEY (ArqueoID))");
				}
			}
			if (gBdVersion < 34)
			{
				gBdVersion = 34;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				if (BD.ConsultWithOutAlerts("select top 1 Precio from PreparacionesComodines") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE PreparacionesComodines ADD Precio money ");
					BD.ConsultaModificar("PreparacionesComodines", "Precio=0", "2=2");
				}
			}
			if (gBdVersion < 35)
			{
				gBdVersion = 35;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				if (BD.ConsultWithOutAlerts("select top 1 transaccionID from Pagos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Pagos ADD transaccionID integer ");
					BD.ConsultaModificar("Pagos", "transaccionID=0", "2=2");
				}
			}
			if (gBdVersion < 37)
			{
				gBdVersion = 37;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				if (BD.ConsultWithOutAlerts("select top 1 PrecioUnit from DetalleCuenta") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta ADD PrecioUnit money ");
					BD.ConsultaModificar("DetalleCuenta", "PrecioUnit=(Pago+Debe)", "Cantidad>0");
					BD.ConsultaModificar("DetalleCuenta", "PrecioUnit=PrecioUnit/Cantidad", "Cantidad>0");
					BD.ConsultaModificar("DetalleCuenta", "PrecioUnit=0", "Cantidad=0");
					BD.ConsultaEliminar("DetallesAjustes", "Cantidad=0");
				}
			}
			if (gBdVersion < 38)
			{
				gBdVersion = 38;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				BD.ConsultWithOutAlerts("ALTER TABLE Visitas ALTER COLUMN  ImprimioCuenta int");
				BD.ConsultaModificar("Meseros", "Codigo= Contrasenha", "Codigo is null");
			}
			if (gBdVersion < 39)
			{
				gBdVersion = 39;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				if (BD.ConsultWithOutAlerts("select top 1 D5 from Arqueo") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Arqueo ADD D5 integer ");
					BD.ConsultaModificar("Arqueo", "D5=0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Arqueo ADD D1 integer ");
					BD.ConsultaModificar("Arqueo", "D1=0", "2=2");
				}
			}
			if (gBdVersion < 41)
			{
				gBdVersion = 41;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				BD.ConsultaModificar("DetalleCuenta", "PrecioUnit=PrecioUnit/Cantidad", "Cantidad>1 and PrecioUnit=(Pago+Debe)");
				if (!configuration.gComidaRapida)
				{
					BD.ConsultaEliminar("Turnos", "Observaciones is null and MontoFin is null and FechaIni<" + ArmarFecha(DateAndTime.Today));
				}
			}
			if (gBdVersion < 42)
			{
				gBdVersion = 42;
				if (BD.ConsultWithOutAlerts("select top 1 * from Queries") == 0)
				{
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultWithOutAlerts("CREATE TABLE Queries(      ID AUTOINCREMENT,    Name_ Memo ,    select_ Memo ,     from_ Memo ,    where_ Memo ,     order_by Memo ,     group_by Memo ,      PRIMARY KEY (ID))");
					}
					else
					{
						BD.ConsultWithOutAlerts("CREATE TABLE Queries(       ID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + "  Name_ varchar(100),   select_ varchar(1000),   from_ varchar(500),   where_ varchar(500),   order_by varchar(500),   group_by varchar(500),    PRIMARY KEY (ID))");
					}
					BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				}
			}
			if (gBdVersion < 43)
			{
				gBdVersion = 43;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultaModificar("DetalleCuenta", "PrecioUnit = (select Precio from Productos where ID=ProductoID )", "PrecioUnit is null");
				}
			}
			if (gBdVersion < 44)
			{
				gBdVersion = 44;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				BD.ConsultWithOutAlerts("CREATE INDEX PIndexVisitasFecha ON Visitas (Fecha)");
				BD.ConsultWithOutAlerts("CREATE INDEX PIndexDetalleCuentaHora ON DetalleCuenta (Hora)");
			}
			num = 46;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 * from Familias") == 0)
				{
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultWithOutAlerts("CREATE TABLE Familias(      FamiliaID AUTOINCREMENT,    Descripcion varchar(100),     PRIMARY KEY (FamiliaID))");
					}
					else
					{
						BD.ConsultWithOutAlerts("CREATE TABLE Familias(      FamiliaID integer IDENTITY(1,1) NOT NULL,    Descripcion varchar(100)     PRIMARY KEY (FamiliaID))");
					}
					int id = 0;
					BD.ConsultaInsertar3("'General'", "Familias(Descripcion)", ref id);
					if (BD.ConsultWithOutAlerts("select top 1 FamiliaId from TiposProductos") == 0)
					{
						BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD  FamiliaId int");
						BD.ConsultaModificar("TiposProductos", "FamiliaId= " + Conversions.ToString(id), "2=2");
					}
					BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				}
			}
			num = 47;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 ObservacionCocinaId from ObservacionesCocina") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE ObservacionesCocina(\t\t     ObservacionCocinaId " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tOrden float,\t  \t\tDescripcion varchar(255),\t  \t\tPRIMARY KEY (ObservacionCocinaId))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 48;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("DetalleCuenta", "precioUnit=0", "Borrada=" + armarBolean(1));
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 49;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 AgruparPagoID from Pagos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Pagos ADD AgruparPagoID integer ");
					BD.ConsultaModificar("Pagos", "AgruparPagoID=PagoID", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 50;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE DetallesProduccion ALTER COLUMN Producida float ");
				BD.ConsultWithOutAlerts("ALTER TABLE DetallesProduccion ALTER COLUMN Eliminada float ");
				BD.ConsultWithOutAlerts("ALTER TABLE DetallesProduccion ALTER COLUMN Reciclada float ");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 51;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 soloAdminBorra from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD soloAdminBorra bit ");
					BD.ConsultaModificar("Configuraciones", ("soloAdminBorra =" + armarBolean(0)) ?? "", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 52;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 DescuentoID from Descuentos") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Descuentos(\t\t     DescuentoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tNombre varchar (250),\t  \t\tPorcentaje float,\t  \t\tMinimoMonto float,\t  \t\tMaximoMonto float,\t  \t\tObservacion varchar(250),\t  \t\tActivo bit,\t  \t\tAvisoCajero  varchar(200),\t  \t\tPRIMARY KEY (DescuentoID))");
				}
				if (configuration.gMODO_ACCESS == 1)
				{
					BD.ConsultWithOutAlerts("insert into Descuentos(nombre,porcentaje,activo) select distinct CStr(Descuento),Descuento,1 from Clientes where Descuento>0 ");
					BD.ConsultWithOutAlerts(" update   Descuentos inner join Clientes as D   on D.Descuento=Descuentos.Porcentaje set  D.Descuento = Descuentos.DescuentoID");
				}
				else
				{
					BD.ConsultWithOutAlerts("insert into Descuentos(nombre,porcentaje,activo) select distinct cast(Descuento as varchar),Descuento,1 from Clientes where Descuento>0 ");
					BD.ConsultWithOutAlerts("update D set D.Descuento = Descuentos.DescuentoID from (Descuentos inner join Clientes  as D on D.Descuento=Descuentos.Porcentaje)");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 53;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Presentacion from Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Presentacion varchar(250) ");
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD UnidadContenido varchar(250) ");
					BD.ConsultaModificar("Productos", "Presentacion =''", "2=2");
					BD.ConsultaModificar("Productos", "UnidadContenido =''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 54;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 FechaInicio from Meseros") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Meseros ADD FechaInicio datetime ");
					BD.ConsultaModificar("Meseros", "FechaInicio =" + ArmarFecha(DateAndTime.Today), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 55;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("TiposProductos", "FamiliaId= 1", "FamiliaId is null or FamiliaId=0");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 56;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("CREATE PROCEDURE backupBD @Name nvarchar(50) AS BEGIN DECLARE @SQLStatement VARCHAR(2000) SET @SQLStatement = 'C:\\Restotech\\Backups\\' + CONVERT(nvarchar(30), GETDATE(), 110) +'.bak' BACKUP DATABASE @Name TO  DISK = @SQLStatement  WITH INIT END");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 57;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 LoggID from Logg") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Logg(\t\t     LoggID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFecha Datetime,\t  \t\tUserID integer,\t  \t\tAccion varchar(200),\t  \t\tFormulario varchar(200),\t  \t\tPRIMARY KEY (LoggID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 58;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 PC from DetalleCuenta") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta ADD PC varchar(15) ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 59;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Codigo from Clientes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD Codigo Varchar(50) ");
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.FastTaste) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CateringSacherCorp) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CurtiembreTauro) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Soboce))
					{
						BD.ConsultaModificar("Clientes", "Codigo =CI", "Codigo is null");
						BD.ConsultaModificar("Clientes", "Codigo =ID", "Codigo is null");
					}
					else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.MagnoGym)
					{
						BD.ConsultaModificar("Clientes", "Codigo =Comentarios", "2=2");
					}
					else
					{
						BD.ConsultaModificar("Clientes", "Codigo =ID", "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 60;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Clientes ALTER COLUMN  cumpleanos dateTime");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 61;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("update Visitas set EnMesa=" + armarBolean(0) + " where ParaLlevarID =1 ");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 62;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD Orden integer ");
				BD.ConsultWithOutAlerts("update TiposProductos set Orden=TipoProductoID");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 64;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Codigo from Familias") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Familias ADD Codigo varchar(50) ");
					BD.ConsultaModificar("Familias", "Codigo =''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 65;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (Conversions.ToString(NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select Descripcion from Configuraciones").Rows[0][0]), "")).Length == 0)
				{
					BD.ConsultaModificar("Configuraciones", "Descripcion= '" + configuration.db_file.Replace("ControlConsumo", "").Replace("data", "") + "'", "Descripcion is null");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 66;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Gastos_FacturaID from Gastos_Facturas") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Gastos_Facturas(    Gastos_FacturaID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tNit Varchar(50), \t\tNombre varchar(250), \t\tNroFactura integer, \t\tNroAutorizacion varchar(50), \t\tCodigo integer, \t\tFecha DateTime, \t\tMontoFacturado Float, \t\tDescuento Float, \t\tGastoID integer, \t\tPRIMARY KEY (Gastos_FacturaID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 67;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (gBdVersion > num)
				{
					BD.ConsultaModificar("Productos", "cantidadML=1", "cantidadML=0 and TienePreparacion=" + armarBolean(1));
					BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				}
			}
			num = 68;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Cuentas ALTER COLUMN  Nro varchar(50)");
				BD.ConsultWithOutAlerts("ALTER TABLE Gastos_Facturas ALTER COLUMN  Codigo varchar(50)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 69;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Costos from DetallesTraspasos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetallesTraspasos ADD Costos float ");
					BD.ConsultWithOutAlerts("update DetallesTraspasos set Costos=0");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 70;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE TiposGastos ADD Activo bit ");
				BD.ConsultaModificar("TiposGastos", "Activo=1", "2=2");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 71;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 produccionID from ProductosUsos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ProductosUsos ADD produccionID integer ");
					BD.ConsultaModificar("ProductosUsos", "produccionID=0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE ProductosUsos ADD preProcesamientoID integer ");
					BD.ConsultaModificar("ProductosUsos", "preProcesamientoID=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 72;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("DetalleCuenta", "cerrada=" + armarBolean(1), "debe=0 and cerrada=" + armarBolean(0));
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 73;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 AgruparPagoID from Facturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD AgruparPagoID integer ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 74;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 MaxDeuda from Clientes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD MaxDeuda float ");
					BD.ConsultaModificar("Clientes", "MaxDeuda=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 75;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Descuento from Facturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD Descuento float ");
					BD.ConsultaModificar("Facturas", "Descuento=0", "2=2");
				}
				if (configuration.gMODO_ACCESS == 1)
				{
					BD.ConsultWithOutAlerts(" CREATE TABLE facturaDescuento ( id integer Not NULL  ,  descuento money )");
					BD.ConsultWithOutAlerts(" deleTE FROM facturaDescuento WHERE 1=1");
					BD.ConsultWithOutAlerts(" insert into facturaDescuento(id, descuento)  select VisitaID, sum(PrecioUnit *Cantidad)-(sum(pago)+sum(debe)) as descuento   from DetalleCuenta where Borrada= " + armarBolean(0) + "  group by VisitaID ");
					BD.ConsultWithOutAlerts("update Facturas inner join facturaDescuento   on facturaDescuento.id=Facturas.visitaID set Facturas.Descuento= facturaDescuento.descuento ");
					BD.ConsultWithOutAlerts(" drop TABLE facturaDescuento ");
				}
				else
				{
					BD.ConsultWithOutAlerts("update Facturas set  Facturas.Descuento = tab1.descuento from Facturas inner join (select sum(PrecioUnit *Cantidad)-(sum(pago)+sum(debe)) as descuento ,VisitaID  from DetalleCuenta where Borrada= " + armarBolean(0) + "  group by VisitaID ) as tab1 on tab1.VisitaID=Facturas.visitaID");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 76;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 UserName from Email") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Email(       EmailID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tUserName Varchar(50), \t\tPassw varchar(50), \t\tPort varchar(50), \t\tFrm varchar(50), \t\tHost varchar(50),  \t\tPRIMARY KEY (EmailID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 77;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Observacion from Visitas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Visitas ADD Observacion varchar (250) ");
					BD.ConsultaModificar("Visitas", "Observacion=''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 78;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("CREATE INDEX PIndexFacturasFecha ON Facturas (FechaEmision)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 79;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Costos from PreprocesamientosDe") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE PreprocesamientosDe ADD Costos float ");
					BD.ConsultaModificar("PreprocesamientosDe", "Costos=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 81;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gTipoFacturacion == 1)
				{
					if (configuration.gMODO_ACCESS == 1)
					{
						if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Fragolia)
						{
							BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHR(13) + CHR(10) + '        Yacuiba - Bolivia'", "1=1");
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.EspigaDeOro)
						{
							BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHR(13) + CHR(10) + '        El Alto - Bolivia'", "1=1");
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Stigma)
						{
							BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHR(13) + CHR(10) + '   Sucre - Chuquisaca - Bolivia'", "1=1");
						}
						else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LosLomitosExpress)
						{
							BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHR(13) + CHR(10) + '   Montero - Santa Cruz - Bolivia'", "1=1");
						}
						else
						{
							BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHR(13) + CHR(10) + '        Santa Cruz - Bolivia'", "1=1");
						}
					}
					else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Fragolia)
					{
						BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHAR(13) + CHAR(10) + '        Yacuiba - Bolivia'", "1=1");
					}
					else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.EspigaDeOro)
					{
						BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHAR(13) + CHAR(10) + '        El Alto - Bolivia'", "1=1");
					}
					else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Stigma)
					{
						BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHAR(13) + CHAR(10) + '   Sucre - Chuquisaca - Bolivia'", "1=1");
					}
					else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LosLomitosExpress)
					{
						BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHAR(13) + CHAR(10) + '   Montero - Santa Cruz - Bolivia'", "1=1");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "Direccion= Direccion + CHAR(13) + CHAR(10) + '        Santa Cruz - Bolivia'", "1=1");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 82;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 DevolucionID from Devoluciones") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Devoluciones(    DevolucionID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFecha DateTime, \t\tCantidad Float, \t\tObservacion varchar(250), \t\tDetalleCuentaID integer, \t\tPRIMARY KEY (DevolucionID))");
				}
				BD.ConsultWithOutAlerts("CREATE INDEX DiaKey_INDEX ON Visitas (DiaKey)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 83;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CantidadPersonas from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD CantidadPersonas bit");
					BD.ConsultaModificar("Configuraciones", "CantidadPersonas= " + armarBolean(0), "2=2");
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Boulangerie) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Empanaderia))
					{
						BD.ConsultaModificar("Configuraciones", "CantidadPersonas= " + armarBolean(1), "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 84;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 ImpresoraCuenta from Salones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Salones ADD ImpresoraCuenta varchar(200) ");
					BD.ConsultaModificar("Salones", "ImpresoraCuenta=''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 85;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Costo from DetallesAjustes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetallesAjustes ADD Costo float ");
					BD.ConsultWithOutAlerts("update DetallesAjustes set Costo=0");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 86;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 NroFactura2 from CodigosFacturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CodigosFacturas ADD NroFactura2 integer ");
					BD.ConsultaModificar("CodigosFacturas", "NroFactura2=NroFactura", "NroFactura2 is null");
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD FacturaBucle integer ");
					BD.ConsultaModificar("Configuraciones", "FacturaBucle=0", "1 = 1");
				}
				if (configuration.gMODO_ACCESS == 1)
				{
					BD.ConsultWithOutAlerts("Select * INTO Facturas2   FROM Facturas ");
					BD.ConsultWithOutAlerts("delete from Facturas2 ");
				}
				else
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Facturas2( FacturaID integer IDENTITY(1,1) Not NULL, NIT varchar(50) NULL,  Nombre varchar(200) NULL, FechaEmision datetime NULL, NroFactura integer NULL, Codigo varchar(20) NULL, Monto money NULL, VisitaID integer NULL, CodigoID integer NULL, Anulada bit NULL, FechaAnulacion datetime NULL, Observacion varchar(200) NULL, personalID integer NULL, Aux varchar(100) NULL,AgruparPagoID integer NULL, Descuento money NULL, PRIMARY KEY (FacturaID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 87;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 DatosFacturas from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD DatosFacturas bit");
					BD.ConsultaModificar("Configuraciones", "DatosFacturas= " + armarBolean(0), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 88;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CantFactura2 from CodigosFacturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CodigosFacturas ADD CantFactura2 integer ");
					BD.ConsultaModificar("CodigosFacturas", "CantFactura2=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 89;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 ReglaID from Reglas") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Reglas(    ReglaID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tDia integer, \t\tHoraIni DateTime, \t\tHoraFin DateTime, \t\tProductoID integer, \t\tHabilitado bit, \t\tPRIMARY KEY (ReglaID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 90;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 BorradoID from Borrados") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Borrados(    BorradoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFecha DateTime, \t\tDetalleCuentaID integer, \t\tPrecio float, \t\tCantidad float, \t\tPRIMARY KEY (BorradoID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 91;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Descuento from Pagos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Pagos ADD Descuento Float");
					BD.ConsultaModificar("Pagos", "Descuento=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 92;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 MeseroID from Traspasos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Traspasos ADD MeseroID integer ");
					BD.ConsultaModificar("Traspasos", "MeseroID=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 93;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CostoNeto from DetalleProductosCompra") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetalleProductosCompra ADD CostoNeto float");
					BD.ConsultaModificar("DetalleProductosCompra", "CostoNeto=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 94;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
				{
					BD.ConsultaInsertar("'Auto','',0,NULL,'','',0", "ParaLlevar");
					BD.ConsultaInsertar("'Moto','',0,NULL,'','',0", "ParaLlevar");
					if (Operators.ConditionalCompareObjectNotEqual(BD.ConsultaVer("ParaLlevarID", "ParaLlevar", "Nombre ='Auto'").Rows[0][0], 4, TextCompare: false))
					{
						Interaction.MsgBox("Creo mal el 4.Auto en tabla ParaLlevar");
					}
					if (Operators.ConditionalCompareObjectNotEqual(BD.ConsultaVer("ParaLlevarID", "ParaLlevar", "Nombre ='Moto'").Rows[0][0], 5, TextCompare: false))
					{
						Interaction.MsgBox("Creo mal el 5.Moto en tabla ParaLlevar");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 95;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Orden from DetalleCuenta") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta ADD Orden int");
					BD.ConsultaModificar("DetalleCuenta", "Orden=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 96;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 ICE from Gastos_Facturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Gastos_Facturas ADD ICE float");
					BD.ConsultaModificar("Gastos_Facturas", "ICE=0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Gastos_Facturas ADD Excento float");
					BD.ConsultaModificar("Gastos_Facturas", "Excento=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 98;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
				{
					BD.ConsultaInsertar("'Pedidos ya','',0,NULL,'','',0", "ParaLlevar(Nombre,NombreFactura, NIT,HoraRecoger,Telefono,Direccion,Motociclista)");
					if (Operators.ConditionalCompareObjectNotEqual(BD.ConsultaVer("ParaLlevarID", "ParaLlevar", "Nombre ='Pedidos ya'").Rows[0][0], 6, TextCompare: false))
					{
						Interaction.MsgBox("Creo mal el 6.PedidosYa en tabla ParaLlevar");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 100;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("CREATE TABLE TigoMoney(    TigoMoneyID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tComercio varchar(50), \t\tLlaveIdentificadora varchar(255), \t\tLlavePrivada varchar(50), \t\taConfirmacion varchar(50), \t\tConfiguracionID integer, \t\tPRIMARY KEY (TigoMoneyID))");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 101;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 EncargadoID from Traspasos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Traspasos ADD EncargadoID int");
					BD.ConsultaModificar("Traspasos", "EncargadoID=0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Compras ADD MeseroID int");
					BD.ConsultaModificar("Compras", "MeseroID=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 102;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 104;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("update DetalleCuenta set Costo=0 where Borrada =" + armarBolean(1));
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 109;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Interno from Almacenes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Almacenes ADD Interno bit");
					BD.ConsultaModificar("Almacenes", "Interno=" + armarBolean(0), "2=2");
					int num4 = Conversions.ToInteger(NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select min(AlmacenId) from Almacenes").Rows[0][0]), 0));
					if (num4 == 0)
					{
						Interaction.MsgBox("no hay ningun almacen, cree uno y reinicie el sistema ");
						return false;
					}
					BD.ConsultaModificar("Almacenes", "Interno=" + armarBolean(1), "almacenID=" + Conversions.ToString(num4));
					BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD AlmacenID int");
					BD.ConsultaModificar("TiposProductos", "AlmacenID=" + Conversions.ToString(num4), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Compras ADD AlmacenID int");
					BD.ConsultaModificar("Compras", "AlmacenID=" + Conversions.ToString(num4), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Ajustes ADD AlmacenID int");
					BD.ConsultaModificar("Ajustes", "AlmacenID=" + Conversions.ToString(num4), "2=2");
					BD.ConsultaModificar("Compras", "AlmacenID=" + Conversions.ToString(num4), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Stock" + Conversions.ToString(num4) + " float");
					BD.ConsultaModificar("Productos", "Stock" + Conversions.ToString(num4) + "= Stock" + Conversions.ToString(num4), "Stock" + Conversions.ToString(num4) + " is null");
					BD.ConsultWithOutAlerts("ALTER TABLE Productos drop COLUMN Stock;");
					BD.ConsultWithOutAlerts("CREATE TABLE Categorias_Almacenes(    categoriaAlmacenID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tSalonID  integer, \t\tCategoriaID  integer, \t\tAlmacenID  integer, \t\tPRIMARY KEY (categoriaAlmacenID))");
					BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				}
			}
			num = 110;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 AlmacenID from PreProcesamientos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE PreProcesamientos ADD AlmacenID int");
					BD.ConsultaModificar("PreProcesamientos", "AlmacenID=0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Traspasos ADD AlmacenID2 int");
					BD.ConsultaModificar("Traspasos", "AlmacenID2= AlmacenID", "Entrante=" + armarBolean(1));
					BD.ConsultaModificar("Traspasos", "AlmacenID=0", "Entrante=" + armarBolean(1));
					BD.ConsultWithOutAlerts("ALTER TABLE Traspasos drop COLUMN Entrante;");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 111;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 DobleFactura from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD DobleFactura bit ");
					BD.ConsultaModificar("Configuraciones", ("DobleFactura =" + armarBolean(0)) ?? "", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 113;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 TipoUsuarioID from TiposProductos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD TipoUsuarioID int");
					BD.ConsultaModificar("TiposProductos", "TipoUsuarioID=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 114;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("CREATE TABLE TipoEnvios(    TipoEnvioID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tNombre varchar(150), \t\tOrden integer, \t\tActivo bit, \t\tPRIMARY KEY (TipoEnvioID))");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 115;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (~BD.ConsultWithOutAlerts("select TipoEnvioID from Visitas where 1=0") != 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Visitas ADD TipoEnvioID int");
					BD.ConsultaModificar("Visitas", "TipoEnvioID=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 116;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY && Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("count(*)", "TipoEnvios", "nombre='MESA'").Rows[0][0], 0, TextCompare: false))
				{
					BD.ConsultaInsertar("'MESA',1,'1'", "TipoEnvios(nombre,Orden,Activo)");
					BD.ConsultaInsertar("'LLEVAR',2,'1'", "TipoEnvios(nombre,Orden,Activo)");
					BD.ConsultaInsertar("'AUTO',3,'1'", "TipoEnvios(nombre,Orden,Activo)");
					BD.ConsultaInsertar("'PATIO SERVICE',4,'1'", "TipoEnvios(nombre,Orden,Activo)");
					BD.ConsultaInsertar("'PEDIDOS YA',5,'1'", "TipoEnvios(nombre,Orden,Activo)");
					BD.ConsultaInsertar("'PEDIDOS ONLINE',6,'1'", "TipoEnvios(nombre,Orden,Activo)");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 118;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 SSL from Email") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Email ADD SSL bit ");
					BD.ConsultaModificar("Email", ("SSL =" + armarBolean(0)) ?? "", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 119;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Activo from CodigosFacturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CodigosFacturas ADD Activo bit ");
					BD.ConsultaModificar("CodigosFacturas", ("Activo =" + armarBolean(1)) ?? "", "2=2");
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE  TiposProductos_Fotos (\t[ID] [int] NOT NULL,\t[foto] [image] NULL)");
					BD.ConsultWithOutAlerts("CREATE PROCEDURE Modificar_Foto_TipoProducto @ID integer, @Foto image  as  update [dbo].[TiposProductos_Fotos]  set Foto =  @Foto    where ID =  @ID;  select 1;  GO");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 120;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 ImpresoraFactura from Salones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Salones ADD ImpresoraFactura varchar(100) ");
					BD.ConsultaModificar("Salones", "ImpresoraFactura =''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 121;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Grupo from Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Grupo varchar(100) ");
					BD.ConsultaModificar("Productos", "Grupo =''", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD GrupoCantidad float ");
					BD.ConsultaModificar("Productos", "GrupoCantidad =0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 122;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Borrado from Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Borrado bit ");
					BD.ConsultaModificar("Productos", "Borrado =" + armarBolean(0), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 124;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 id from Intermedia") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Intermedia(ID integer NULL ) ");
					BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 125;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("CREATE TABLE TiposErrores(    TipoErrorID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tDescripcion varchar(150), \t\tActivo bit, \t\tPRIMARY KEY (TipoErrorID))");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 126;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS != 1 && BD.ConsultWithOutAlerts("select top 1 Extras from DetalleCuentaIntermediaria") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuentaIntermediaria ADD Extras nvarchar(200) ");
				}
				if (BD.ConsultWithOutAlerts("select top 1 CerrarTurnoMesasAbiertas from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD CerrarTurnoMesasAbiertas bit ");
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.IrishPub) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.EspigaDeOro) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.LosLomitos) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Cheers) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Naoki) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Serendipity) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Renaissance) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Rokani) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CafeAme))
					{
						BD.ConsultaModificar("Configuraciones", "CerrarTurnoMesasAbiertas= " + armarBolean(1), "2=2");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "CerrarTurnoMesasAbiertas= " + armarBolean(0), "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 127;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("CREATE TABLE DetalleCuenta_Asistentes( \t\tAsistenteID integer NOT NULL, \t\tDetalleCuentaID integer NOT NULL, \t\tComision money)");
				if ((configuration.styleBolichesId.Paradise == configuration.gStyleBoliches1) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Luxos) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.HabibiShow) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Casa22) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Dollhouse))
				{
					BD.ConsultWithOutAlerts("insert into DetalleCuenta_Asistentes select MeseroID, DetalleCuenta.ID, Productos.Comision   from DetalleCuenta inner join Productos on Productos.ID=ProductoID   where TomoPedidoMeseroID <> MeseroID and MeseroID in (Select id from Asistentes )");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 128;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 FamiliaGasto from TiposGastos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE TiposGastos ADD FamiliaGasto varchar(50) ");
					BD.ConsultaModificar("TiposGastos", "FamiliaGasto =''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 129;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Orden  from  Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Orden float ");
					BD.ConsultaModificar("Productos", "Orden =0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD CostoBruto float ");
					BD.ConsultaModificar("Productos", "CostoBruto =0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 130;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CostoBruto from  DetallesAjustes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetallesAjustes ADD CostoBruto float ");
					BD.ConsultaModificar("DetallesAjustes", "CostoBruto =0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 131;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CantidadMaxima  from  Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD CantidadMaxima float ");
					BD.ConsultaModificar("Productos", "CantidadMaxima =0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 132;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Fecha from   ProductosUsos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ProductosUsos ADD Fecha Datetime ");
					BD.ConsultaModificar("ProductosUsos", "Fecha =" + ArmarFecha(DateAndTime.Today), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 133;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 MontoTotal from  Gastos_Facturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Gastos_Facturas ADD MontoTotal float ");
					BD.ConsultaModificar("Gastos_Facturas", "MontoTotal = MontoFacturado+Descuento", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 135;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS == 1)
				{
					BD.ConsultWithOutAlerts("update ProductosUsos inner join DetalleCuenta on ProductosUsos.DetalleCuentaID=DetalleCuenta.ID set ProductosUsos.Fecha=DetalleCuenta.Hora ");
				}
				else
				{
					BD.ConsultWithOutAlerts("update ProductosUsos set ProductosUsos.Fecha=DetalleCuenta.Hora from ProductosUsos inner join DetalleCuenta on ProductosUsos.DetalleCuentaID=DetalleCuenta.ID ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 136;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Tiempo from  Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD Tiempo integer ");
					BD.ConsultaModificar("Productos", "Tiempo =0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 137;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 ConfiguracionID from  TiposProductos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD ConfiguracionID integer ");
					BD.ConsultaModificar("TiposProductos", "ConfiguracionID =1", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 140;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 NombreFactura from  Clientes") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD NombreFactura varchar(250)");
					BD.ConsultaModificar("Clientes", "NombreFactura = Nombre + ' ' + apellidos", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select top 1 Productos from  ICE_Fijo") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD ICE_Fijo Float ");
					BD.ConsultaModificar("Productos", "ICE_Fijo =0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD ICE_porcentual float ");
					BD.ConsultaModificar("Productos", "ICE_porcentual =0", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select top 1 ConfiguracionID from  ManejaICE") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD ManejaICE bit ");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD ICE float ");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD ICE float ");
					BD.ConsultaModificar("Configuraciones", "ManejaICE =0", "2=2");
					BD.ConsultaModificar("Facturas", "ICE =0", "2=2");
					BD.ConsultaModificar("Facturas2", "ICE =0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 143;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 TarjetaCuentaID from  TipoEnvios") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE TipoEnvios ADD TarjetaCuentaID integer ");
					BD.ConsultaModificar("TipoEnvios", "TarjetaCuentaID =0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE TipoEnvios ADD CuponesCuentaID integer ");
					BD.ConsultaModificar("TipoEnvios", "CuponesCuentaID =0", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select top 1 NOTAS from  ParaLlevar") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ParaLlevar ADD NOTAS varchar(250) ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 144;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select count(*) from CredencialesPedidosYa") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE CredencialesPedidosYa( PedidosYaID integer Not NULL,  ClientId varchar(50) NULL, ClientSecret varchar(50) NULL, Username varchar(50) NULL, Password1 varchar(50) NULL, Environment varchar(50) NULL, StoreID integer NULL, ConfiguracionID integer NULL, PRIMARY KEY (PedidosYaID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 145;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select FacturaBackupArchivo from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD FacturaBackupArchivo bit ");
					BD.ConsultaModificar("Configuraciones", "FacturaBackupArchivo= " + armarBolean(0), "2=2");
					if (!Directory.Exists(MyProject.Application.Info.DirectoryPath + "\\Facturas\\"))
					{
						Directory.CreateDirectory(MyProject.Application.Info.DirectoryPath + "\\Facturas\\");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 147;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Categoria_ImpresoraID from Categorias_Salones") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Categorias_Salones(       CategoriaSalonID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + "   SalonID integer NULL,     CategoriaID integer  NULL,     PRIMARY KEY(CategoriaSalonID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 148;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("update Productos set Habilitado=" + armarBolean(0) + " where Borrado=" + armarBolean(1) + " ");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 149;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select ConfiguracionFacturaID from Impresoras") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Impresoras ADD ConfiguracionFacturaID integer NULL ");
					BD.ConsultaModificar("Impresoras", "ConfiguracionFacturaID= " + Conversions.ToString(gConfiguracionID), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 150;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select ProveedorID from Gastos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Gastos ADD ProveedorID integer NULL ");
					BD.ConsultaModificar("Gastos", "ProveedorID= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 151;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Recetas_Migracion ADD CodigoMP varchar(50) NULL ");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 152;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD LinkFoto varchar(255) NULL ");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 154;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select HabilitadoPY from Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD HabilitadoPY bit NULL ");
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY)
					{
						BD.ConsultWithOutAlerts("update Productos set habilitadoPY=1   where (codigoPY<>'' and codigoPY <> '0') and habilitado=1");
					}
				}
				if (BD.ConsultWithOutAlerts("select top 1 id from CredencialesFidelizacionLatam") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE CredencialesFidelizacionLatam(      ID integer  NOT NULL,     SucursalID varchar(50),     commerceId varchar(50),     commerceName varchar(50),     apiKey varchar(50),     ConfiguracionID integer NULL,     PRIMARY KEY(ID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 155;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CodigoPY from  Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD CodigoPY integer null");
					BD.ConsultaModificar("Productos", "CodigoPY= ID", " id>3");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 156;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CostoBruto from  DetalleProductosCompra") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetalleProductosCompra ADD CostoBruto float NULL ");
					BD.ConsultaModificar("DetalleProductosCompra", "CostoBruto= CostoNeto", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE DetalleProductosCompra drop column CostoNeto ");
					BD.ConsultWithOutAlerts("ALTER TABLE PreparacionesComodines drop column CodigoPY ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 157;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 ModificaPrecio from  PreparacionesComodines") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE PreparacionesComodines ADD ModificaPrecio bit NULL ");
					BD.ConsultaModificar("PreparacionesComodines", "ModificaPrecio= " + armarBolean(0), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 158;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 LayoutSymbolDescripcion from  Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD LayoutSymbolDescripcion varchar(50) NULL ");
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.SirFrancis)
					{
						BD.ConsultaModificar("Configuraciones", "LayoutSymbolDescripcion= 'Nombre'", "2=2");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "LayoutSymbolDescripcion= 'Codigo'", "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 159;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Simbolo", "tamanoY =75 , tamanoX = 95", "tipo Like 'mesa'");
				BD.ConsultWithOutAlerts("ALTER TABLE Produccion ADD AlmacenID integer NULL");
				if (BD.ConsultWithOutAlerts("select ImprimirTicket from CredencialesPedidosYa") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesPedidosYa ADD ImprimirTicket bit NULL ");
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.ElCuartito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bestial))
					{
						BD.ConsultaModificar("CredencialesPedidosYa", "ImprimirTicket= " + armarBolean(1), "2=2");
					}
					else
					{
						BD.ConsultaModificar("CredencialesPedidosYa", "ImprimirTicket= " + armarBolean(0), "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 161;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp_Detalle_Combo ADD MODIFICA_PRECIO bit NULL ");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 162;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 ProduccionID from  Produccion") == 0)
				{
					if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
					{
						BD.ConsultWithOutAlerts("CREATE TABLE Produccion(ProduccionID integer NOT NULL,Fecha datetime NULL,Observacion varchar(200) NULL)");
						BD.ConsultWithOutAlerts("CREATE TABLE DetallesProduccion( DetalleProduccionID integer Not NULL, Producida float NULL, Eliminada float NULL, Reciclada float NULL, Observacion varchar(200) NULL, ProduccionID integer NULL, ProductoID integer NULL)");
					}
					else
					{
						BD.ConsultWithOutAlerts("CREATE TABLE Produccion(ProduccionID integer IDENTITY(1,1) NOT NULL,Fecha datetime NULL,Observacion varchar(200) NULL)");
						BD.ConsultWithOutAlerts("CREATE TABLE DetallesProduccion( DetalleProduccionID integer IDENTITY(1, 1) Not NULL, Producida float NULL, Eliminada float NULL, Reciclada float NULL, Observacion varchar(200) NULL, ProduccionID integer NULL, ProductoID integer NULL)");
					}
				}
				if (BD.ConsultWithOutAlerts("select top 1 AlmacenID from  Produccion") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Produccion ADD AlmacenID integer NULL ");
					BD.ConsultaModificar("Produccion", "AlmacenID= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 163;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select Cant from ProductosCombos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ProductosCombos ADD Cant integer NULL ");
					BD.ConsultaModificar("ProductosCombos", "Cant= 1", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select PuedeDisminuir from Preparaciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Preparaciones ADD PuedeDisminuir bit NULL ");
					BD.ConsultaModificar("Preparaciones", "PuedeDisminuir= " + armarBolean(0), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 164;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("delete from PreparacionesComodines where PreparacionID in (select PreparacionID from Preparaciones where ParaProductoID in ( select id from Productos where Borrado =1  ));");
				BD.ConsultWithOutAlerts("delete  from Preparaciones where ParaProductoID in ( select id from Productos where Borrado =1  );");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 165;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 MODIFICA_PRECIO from  DeliveryApp_Detalle_Combo") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp_Detalle_Combo ADD MODIFICA_PRECIO bit NULL ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 166;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("TiposProductos", "ConfiguracionID =1", "ConfiguracionID is null");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 167;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("CREATE TABLE CredencialesRestomenu( RestomenuID integer IDENTITY(1, 1) Not NULL, Token1 varchar(50) NULL, AcceptedOrdersKey varchar(50) NULL, FetchMenuKey varchar(50) NULL, ConfiguracionID integer NULL )");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 168;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 PLATAFORMA from  DeliveryApp") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD PLATAFORMA integer NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD LATY_NUM float NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD LONX_NUM float NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD PIN_SKIPPED BIT NULL");
					BD.ConsultWithOutAlerts("update DeliveryApp set PLATAFORMA=1 where 2=2");
				}
				BD.ConsultWithOutAlerts("ALTER TABLE ParaLlevar ALTER COLUMN Direccion varchar(250)");
				BD.ConsultWithOutAlerts("ALTER TABLE ParaLlevar ADD Laty_num float NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE ParaLlevar ADD Lonx_num float NULL");
				if (BD.ConsultWithOutAlerts("select top 1 MontoDelivery from  ParaLlevar") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ParaLlevar ADD MontoDelivery float NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE TipoEnvios ADD DeliveryExterno bit NULL");
					BD.ConsultaModificar("TipoEnvios", "DeliveryExterno= " + armarBolean(0), "2=2");
					BD.ConsultaModificar("ParaLlevar", "MontoDelivery= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 169;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 DeliveryID from DeliveryApp;") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE DeliveryApp(     DeliveryID integer IDENTITY(1,1) NOT NULL,    PLATAFORMA integer NULL,    ORDER_NO integer NULL,    PLACE_TIME datetime NULL,    ORDER_TOTAL float NULL,    CHECKOUT_METHOD varchar(50) NULL,    ORDER_TYPE varchar(50) NULL,    DELIVERY_NAME varchar(100) NULL,    DELIVERY_PHONE varchar(100) NULL,    DELIVERY_ADDRESS_1 varchar(255) NULL,    DELIVERY_ADDRESS_2 varchar(255) NULL,    DELIVERY_ZIP varchar(50) NULL,    NIT varchar(50) NULL,    BILL_NAME varchar(100) NULL,    PICKUP_TIME datetime NULL,    DELIVERY_TIME datetime NULL,    DELIVERY_NOTES varchar(250) NULL,    DISCOUNTS float NULL,    REJECTED_ID integer NULL,    LATY_NUM float null,    LONX_NUM float null,    PIN_SKIPPED BIT NULL,    VISITA_ID integer NULL)  ");
					BD.ConsultWithOutAlerts("CREATE TABLE DeliveryApp_detalle(     DeliveryDetalleID integer IDENTITY(1,1) NOT NULL,    QUANTITY integer NULL,    ORDER_SKU varchar(50) NULL,    ORDER_NAME varchar(100) NULL,    ORDER_NOTE varchar(250) NULL,    ORDER_DISCOUNT float NULL,    ORDER_PRICE float NULL,    DeliveryAppID integer NULL)   ");
					BD.ConsultWithOutAlerts("CREATE TABLE DeliveryApp_Detalle_Combo(     DeliveryApp_Detalle_Combo integer IDENTITY(1,1) NOT NULL,    QUANTITY integer NULL,    ORDER_SKU varchar(50) NULL,    ORDER_NAME varchar(100) NULL,    PRICE float NULL,    MODIFICA_PRECIO bit null,    DeliveryApp_DetalleID integer NULL) ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 171;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 DobleCuentaParaLlevar from Configuraciones;") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Preparaciones ADD ParaCategoriaID integer null");
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD DobleCuentaParaLlevar bit null");
					BD.ConsultWithOutAlerts("ALTER TABLE Turnos ADD TurnoAm bit NULL");
					if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.LoNuestro) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.PizzaGo))
					{
						BD.ConsultaModificar("Configuraciones", "DobleCuentaParaLlevar=" + armarBolean(1), "2=2");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "DobleCuentaParaLlevar=" + armarBolean(0), "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 172;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 DeshabilitadoID from ProductosDeshabilitados;") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE ProductosDeshabilitados(     DeshabilitadoID integer IDENTITY(1,1) NOT NULL,    ProductoID integer NULL,    Fecha datetime NULL,    UsuarioID integer NULL)   ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 173;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 ORDER_EMAIL from DeliveryApp;") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD ORDER_EMAIL VARCHAR(200) NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE ParaLLevar ADD email VARCHAR(200) NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD GuardarClienteParaLlevar bit NULL");
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Naoki)
					{
						BD.ConsultaModificar("Configuraciones", "GuardarClienteParaLlevar=" + armarBolean(1), "2=2");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "GuardarClienteParaLlevar=" + armarBolean(0), "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 174;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 TamanoEsProd from CredencialesRestomenu;") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD TamanoEsProd bit NULL");
					BD.ConsultaModificar("CredencialesRestomenu", "TamanoEsProd=" + armarBolean(0), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 175;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select DatosBancarios from CredencialesRestomenu") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD DatosBancarios varchar(200) NULL");
					BD.ConsultaModificar("CredencialesRestomenu", "DatosBancarios=''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 182;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select DELIVERY_TOTAL from DeliveryApp") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD DELIVERY_TOTAL float NULL");
				}
				if (BD.ConsultWithOutAlerts("select REPARTIDOR_ID from DeliveryApp") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD REPARTIDOR_ID integer NULL");
				}
				if (BD.ConsultWithOutAlerts("select QTkey from CredencialesRestomenu") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD QTkey varchar(50) NULL");
				}
				if (BD.ConsultWithOutAlerts("select QT_ID from DeliveryApp") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD QT_ID integer NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD QT_STATE integer NULL");
				}
				if (BD.ConsultWithOutAlerts("select qtID from Meseros") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Meseros ADD qtID integer NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE Meseros ADD Email varchar(75) NULL");
				}
				if (configuration.gMODO_ACCESS == 1)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ParaLLevar  ALTER COLUMN Direccion LONGTEXT");
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp  ALTER COLUMN DELIVERY_ADDRESS_1 LONGTEXT");
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes  ALTER COLUMN Direccion LONGTEXT");
				}
				else
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ParaLLevar  ALTER COLUMN Direccion varchar(500)");
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp  ALTER COLUMN DELIVERY_ADDRESS_1 varchar(500)");
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes  ALTER COLUMN Direccion varchar(500)");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 184;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select MetodoPago from ParaLLevar") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ParaLLevar ADD MetodoPago varchar(20) NULL");
					BD.ConsultaModificar("ParaLLevar", "MetodoPago=''", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select MensajeTransferencia from CredencialesRestomenu") == 0)
				{
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD MensajeTransferencia LONGTEXT NULL");
					}
					else
					{
						BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD MensajeTransferencia varchar(500) NULL");
					}
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
					{
						BD.ConsultaModificar("CredencialesRestomenu", "MensajeTransferencia='Saludos {cliente}, Tiene un pedido en {resto}, Pedido {monto_pedido} | Delivery {monto_delivery} (se paga en efectivo) | *TOTAL para transferir {monto_pedido} Bs.* Puede realizar la transferencia al {datos_banco}  *Por favor envíenos el comprobante por aquí.*'", "MensajeTransferencia is null");
					}
					else
					{
						BD.ConsultaModificar("CredencialesRestomenu", "MensajeTransferencia='Saludos {cliente}, Tiene un pedido en {resto}, Pedido {monto_pedido} | Delivery {monto_delivery} | *TOTAL {monto_total} Bs.* Puede realizar la transferencia al {datos_banco}  *Por favor envíenos el comprobante por aquí.*'", "MensajeTransferencia is null");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 185;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("CREATE TABLE CredencialesQR( CredencialesQRid integer IDENTITY(1, 1) Not NULL, CompanyID integer NULL, accountId varchar(50) NULL, authorizationId varchar(50) NULL, IncluirDelivery bit NULL)");
				if (BD.ConsultWithOutAlerts("select IncluirDelivery from CredencialesQR") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesQR ADD IncluirDelivery bit NULL");
					BD.ConsultaModificar("CredencialesQR", "IncluirDelivery=" + armarBolean(aux: true), "2=2");
				}
				BD.ConsultWithOutAlerts("CREATE TABLE CobrosQR( OrdenesQRid integer IDENTITY(1, 1) Not NULL, qrID integer NULL, Fecha datetime NULL, Monto float NULL, Estado integer NULL, visitaID integer NULL , DeliveryID integer NULL)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 187;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD Direccion varchar(250) NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD Telefono integer NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD DELIVERY_NOTES varchar(250) NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD BILL_NAME varchar(100) NULL");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 188;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("EXEC sp_configure 'clr enabled', 1;  ");
					BD.ConsultWithOutAlerts("RECONFIGURE");
				}
				if (BD.ConsultWithOutAlerts("select Fecha from CobrosQR") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CobrosQR ADD Fecha datetime NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE CobrosQR ADD Monto float NULL");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 189;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select PuedeAsignarMotosPropias from CredencialesRestomenu") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD PuedeAsignarMotosPropias bit NULL");
					BD.ConsultaModificar("CredencialesRestomenu", "PuedeAsignarMotosPropias= " + armarBolean(0), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 191;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select DELIVERY_DSCTO from DeliveryApp") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD DELIVERY_DSCTO money NULL");
				}
				if (BD.ConsultWithOutAlerts("select QT_DRIVER_ID from DeliveryApp") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD QT_DRIVER_ID integer NULL");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 192;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select ExtraParaLlevarID from Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD ExtraParaLlevarID integer NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD ExtraEnMesaID integer NULL");
					BD.ConsultaModificar("Productos", "ExtraParaLlevarID=0,ExtraEnMesaID=0 ", "2=2");
					BD.ConsultaModificar("Productos", "ExtraParaLlevarID= " + Conversions.ToString(2), "ConRecipienteLlevar=" + armarBolean(1));
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 193;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select Fecha from CobrosQR") == 0)
				{
					BD.ConsultWithOutAlerts("drop TABLE CobrosQR");
					BD.ConsultWithOutAlerts("CREATE TABLE CobrosQR( OrdenesQRid integer IDENTITY(1, 1) Not NULL, qrID integer NULL, Fecha datetime NULL, Monto float NULL, Estado integer NULL, visitaID integer NULL , DeliveryID integer NULL)");
				}
			}
			num = 194;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 lat from CredencialesRestomenu;") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD Lat float NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesRestomenu ADD Lon float NULL");
					BD.ConsultaModificar("CredencialesRestomenu", "lat=0, lon=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 195;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				DataTable dataTable2 = BD.ConsultaVer("TipoEnvioID", "TipoEnvios", "2=2", "TipoEnvioID");
				int num5 = dataTable2.Rows.Count - 1;
				for (int j = 0; j <= num5; j++)
				{
					BD.ConsultWithOutAlerts(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("ALTER TABLE Productos ADD PrecioExtraTipoEnvio", dataTable2.Rows[j][0]), " money")));
					if (Conversions.ToBoolean(Operators.AndObject(configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY, Operators.CompareObjectGreaterEqual(dataTable2.Rows[j][0], 4, TextCompare: false))))
					{
						BD.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("PrecioExtraTipoEnvio", dataTable2.Rows[j][0]), "= 1")), Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("PrecioExtraTipoEnvio", dataTable2.Rows[j][0]), " is null")));
					}
					else
					{
						BD.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("PrecioExtraTipoEnvio", dataTable2.Rows[j][0]), "= 0")), Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("PrecioExtraTipoEnvio", dataTable2.Rows[j][0]), " is null")));
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 197;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Cantidad from Borrados;") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Borrados ADD Cantidad float NULL");
					BD.ConsultaModificar("Borrados", "Cantidad=1", "2=2");
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.TartinaFactura)
				{
					BD.ConsultWithOutAlerts("update facturas set Descuento=0 where Descuento >0");
					BD.ConsultWithOutAlerts("update DetalleCuenta set Pago=cantidad*precioUnit where borrada=0");
					BD.ConsultWithOutAlerts("update TiposProductos set Descripcion= 'Varios ' & Cstr(TipoProductoID )");
				}
				BD.ConsultaModificar("Cuentas", "Activa=1", "CuentaID =3");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 198;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 _OtrasCajas from Arqueo") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Arqueo ADD _OtrasCajas float");
					BD.ConsultaModificar("Arqueo", "_OtrasCajas=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 199;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select TipoProductoPYid from TiposProductosPY") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE TiposProductosPY( TipoProductoPYid integer IDENTITY(1, 1) Not NULL, SKU varchar(50) NULL, Nombre varchar(100) NULL, Orden integer NULL )\r\n                      ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 200;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select CredencialYaigoID from CredencialesYaigo") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE CredencialesYaigo( CredencialYaigoID integer IDENTITY(1, 1) Not NULL, Descripcion varchar(50) NULL, sCodigoSucursal varchar(50) NULL, sToken varchar(50) NULL )");
				}
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta  ALTER COLUMN Comentarios varchar(250)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 201;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select esMonitorDigital from Impresoras") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Impresoras ADD esMonitorDigital bit null");
					BD.ConsultaModificar("Impresoras", "esMonitorDigital= " + armarBolean(0), "2=2");
				}
				if (BD.ConsultWithOutAlerts("select Terminado from DetalleCuenta") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta ADD Terminado datetime null");
					BD.ConsultaModificar("DetalleCuenta", "Terminado= Hora", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 202;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select Identificador from Visitas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Visitas ADD Identificador varchar(50) null");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 203;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select date1 from _TestDate") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE _TestDate ADD date1 datetime null");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 204;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 FacturacionExpress from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD FacturacionExpress bit");
					BD.ConsultaModificar("Configuraciones", "FacturacionExpress=0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 205;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select Comentarios from Borrados where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Borrados ADD Comentarios varchar(150) ");
					BD.ConsultaModificar("Borrados", "Comentarios= ''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 206;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select PC from Borrados where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Borrados ADD PC varchar(150) ");
					BD.ConsultaModificar("Borrados", "PC= ''", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD PC varchar(150) ");
					BD.ConsultaModificar("Facturas", "PC= ''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 207;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select MeseroID from Pagos where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Pagos ADD MeseroID integer ");
					BD.ConsultaModificar("Pagos", "MeseroID= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 208;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select PC from Facturas where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD PC varchar(150) ");
					BD.ConsultaModificar("Facturas2", "PC= ''", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD PC varchar(150) ");
					BD.ConsultaModificar("Facturas", "PC= ''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 209;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select VentaNoSincronizadaID from VentasNoSincronizadas") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE VentasNoSincronizadas( VentaNoSincronizadaID integer IDENTITY(1, 1) Not NULL, VisitaID integer NULL, Observacion " + text2 + " NULL, PRIMARY KEY(VentaNoSincronizadaID) )");
				}
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta ALTER COLUMN Comentarios varchar(250)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 210;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select FacturaID from VentasNoSincronizadas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE VentasNoSincronizadas ADD FacturaID integer ");
					BD.ConsultaModificar("VentasNoSincronizadas", "FacturaID= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 211;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select CuentaID from Anticipos where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Anticipos ADD CuentaID integer ");
					BD.ConsultaModificar("Anticipos", "CuentaID= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			if (BD.ConsultWithOutAlerts("select FactElectConfiguracionID from FactElectConfiguracion") == 0)
			{
				gBdVersion = 211;
			}
			num = 212;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select CodigoSIN from Productos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD CodigoSIN integer ");
					BD.ConsultaModificar("Productos", "CodigoSIN= 0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD UnidadSIN integer ");
					BD.ConsultaModificar("Productos", "UnidadSIN= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 213;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select kitchenDisplayID from TiposProductos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD kitchenDisplayID integer ");
					BD.ConsultaModificar("TiposProductos", "kitchenDisplayID= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 214;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select Codigo from FactElectUnidadesMedidas") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE  FactElectUnidadesMedidas( Codigo integer, Descripcion varchar(100)Then NULL, PRIMARY KEY(Codigo) )");
				}
				if (BD.ConsultWithOutAlerts("select FactElectConfiguracionID from FactElectConfiguracion") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE FactElectConfiguracion( FactElectConfiguracionID integer IDENTITY(1, 1) Not NULL, TokenDelegado " + text2 + " NULL, TokenDesde datetime NULL, TokenVigencia datetime NULL, CodigoAmbiente integer NULL, CodigoModalidad integer NULL, CodigoSistema varchar(30) NULL, codigoSucursal integer NULL, CodigoPuntoVenta integer NULL, Nit " + ((configuration.gMODO_ACCESS == 1) ? "decimal" : "bigint") + " NULL , ConfiguracionID integer NULL, NroFactura integer NULL, NroFactura2 integer NULL, CodigoDocumentoSector integer NULL, PRIMARY KEY(FactElectConfiguracionID) )");
				}
				if (BD.ConsultWithOutAlerts("select FactElectCUFDID from FactElectCUFD") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE FactElectCUFD( FactElectCUFDID integer IDENTITY(1, 1) Not NULL, CodigoCUFD varchar(100) NULL, CodigoControl varchar(250) NULL, FechaDesde datetime NULL, FechaHasta datetime NULL, CodigoPuntoVenta integer, ConfiguracionID integer NULL,  PRIMARY KEY(FactElectCUFDID) )");
				}
				if (BD.ConsultWithOutAlerts("select FactElectCUISID from FactElectCUIS") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE FactElectCUIS( FactElectCUISID integer IDENTITY(1, 1) Not NULL, CodigoCUIS varchar(250) , FechaDesde datetime , FechaHasta datetime , CodigoPuntoVenta integer,  ConfiguracionID integer ,   PRIMARY KEY(FactElectCUISID) )");
				}
				if (BD.ConsultWithOutAlerts("select CodigoActividad from FactElectProductosServicios") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE FactElectProductosServicios( CodigoActividad integer , Codigo integer , Descripcion " + text2 + "  )");
				}
				if (BD.ConsultWithOutAlerts("select ID from FactElectLeyes") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE FactElectLeyes( ID integer Not NULL, Codigo integer NULL, Descripcion varchar(250) , PRIMARY KEY(ID))");
				}
				if (BD.ConsultWithOutAlerts("select FactElectFueraLineaID from FactElectFueraLinea") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE FactElectFueraLinea( FactElectFueraLineaID integer IDENTITY(1, 1) Not NULL, Inicio DateTime NULL, CUFD varchar(100) NULL, Cantidad integer NULL, Motivo varchar(250) , Final datetime NULL, Estado bit NULL, CAFC varchar(20) NULL, CodigoRecepcionEvento varchar(20) NULL, Obs varchar(250) NULL, PRIMARY KEY(FactElectFueraLineaID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 215;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select ID from FactElectActividades") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE FactElectActividades( ID integer , Codigo integer , Descripcion " + text2 + "  )");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 216;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas ALTER COLUMN  codigo varchar(80)");
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ALTER COLUMN  codigo varchar(80)");
				if (BD.ConsultWithOutAlerts("select EsGiftCard from Cuentas where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Cuentas ADD EsGiftCard bit ");
					BD.ConsultaModificar("Cuentas", "EsGiftCard= " + armarBolean(0), "2=2");
					if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
					{
						int num6 = Conversions.ToInteger(Operators.AddObject(BD.ConsultaVer("max(CuentaID)", "Cuentas").Rows[0][0], 1));
						BD.ConsultaInsertar(Conversions.ToString(num6) + ",'GiftCard','','',0,''," + armarBolean(1) + "," + armarBolean(0) + "," + armarBolean(0) + "," + armarBolean(1), "Cuentas(cuentaid, nombre,banco, tipoCuenta,nro,Observacion,Activa,Moneda,EsDeposito,EsGiftCard)");
					}
					else
					{
						string data = "'GiftCard','','',0,''," + armarBolean(1) + "," + armarBolean(0) + "," + armarBolean(0) + "," + armarBolean(1);
						int id2 = 0;
						BD.ConsultaInsertar3(data, "Cuentas(nombre,banco, tipoCuenta,nro,Observacion,Activa,Moneda,EsDeposito,EsGiftCard)", ref id2);
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 217;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select ActividadSIN from Productos where 1=0") == 0 && BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD ActividadSIN integer ") != 0)
				{
					BD.ConsultaModificar("Productos", "ActividadSIN= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 218;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (~BD.ConsultWithOutAlerts("select Tipo from FactElectActividades where 1=0") != 0 && BD.ConsultWithOutAlerts("ALTER TABLE FactElectActividades ADD  Tipo varchar(100) ") != 0)
				{
					BD.ConsultaModificar("FactElectActividades", "Tipo=''", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select CAFC from FactElectFueraLinea") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectFueraLinea ADD CAFC varchar(20)");
					BD.ConsultWithOutAlerts("ALTER TABLE CodigoRecepcionEvento ADD CAFC varchar(50)");
					BD.ConsultWithOutAlerts("ALTER TABLE Obs ADD CAFC varchar(250)");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 220;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select leyID from Facturas where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD montoGiftCard money");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD montoGiftCard money");
					BD.ConsultaModificar("Facturas", "montoGiftCard=0", "2=2");
					BD.ConsultaModificar("Facturas2", "montoGiftCard=0", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD Correo varchar(100)");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD Correo varchar(100)");
					BD.ConsultaModificar("Facturas", "Correo=''", "2=2");
					BD.ConsultaModificar("Facturas2", "Correo=''", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD Complemento varchar(5)");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD Complemento varchar(5)");
					BD.ConsultaModificar("Facturas", "Complemento=''", "2=2");
					BD.ConsultaModificar("Facturas2", "Complemento=''", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD Telefono varchar(20)");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD Telefono varchar(20)");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD TipoDocumentoID int");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD TipoDocumentoID int");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD leyID int");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD leyID int");
					BD.ConsultWithOutAlerts("ALTER TABLE Pagos ADD NroTarjeta varchar(20)");
					BD.ConsultWithOutAlerts("ALTER TABLE Cuentas ADD MetodoPagoSIN integer");
					BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=1", "cuentaID=1");
					BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=1", "cuentaID=2");
					BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=2", "cuentaID=3");
					BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=27", "esGiftCard = " + armarBolean(1));
					BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=7", "nombre like 'Cuenta QR'");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 221;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select FueraLineaID from Facturas") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD FueraLineaID int");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD FueraLineaID int");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD Enviada bit");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD Enviada bit");
					BD.ConsultaModificar("Facturas", "Enviada=" + armarBolean(1), "2=2");
					BD.ConsultaModificar("Facturas2", "Enviada=" + armarBolean(1), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Visitas ADD Descuento money");
					BD.ConsultaModificar("Visitas", "Descuento=0", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select Codigo from FactElectUnidadesMedidas") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE  FactElectUnidadesMedidas( Codigo integer, Descripcion varchar(100) NULL  )");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 223;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectFueraLinea ALTER COLUMN CUFD varchar(100)");
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectFueraLinea ALTER COLUMN CodigoRecepcionEvento varchar(50)");
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ALTER COLUMN CodigoPY varchar(50)");
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PerformancePHP)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD NIT_TYPE int");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 224;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (~BD.ConsultWithOutAlerts("select PC from Anticipos where 1=0") != 0 && BD.ConsultWithOutAlerts("ALTER TABLE Anticipos ADD PC  varchar(50) ") != 0)
				{
					BD.ConsultaModificar("Anticipos", "PC= ''", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 225;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (~BD.ConsultWithOutAlerts("select EsAdmin from Cuentas where 1=0") != 0 && BD.ConsultWithOutAlerts("ALTER TABLE Cuentas ADD EsAdmin  bit ") != 0)
				{
					BD.ConsultaModificar("Cuentas", "EsAdmin= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 226;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas ALTER COLUMN NIT varchar(50)");
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ALTER COLUMN NIT varchar(50)");
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas ALTER COLUMN Telefono varchar(20)");
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ALTER COLUMN Telefono varchar(20)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 227;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectFueraLinea ADD FacturaEmitida int");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 230;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (~BD.ConsultWithOutAlerts("select EstadoSiat from Facturas where 1=0") != 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD EstadoSiat int");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD EstadoSiat int");
					BD.ConsultaModificar("Facturas", "EstadoSiat= " + Conversions.ToString(0), "2=2");
					BD.ConsultaModificar("Facturas2", "EstadoSiat= " + Conversions.ToString(0), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectFueraLinea ADD CodigoEventoRespuesta varchar(50)");
					BD.ConsultWithOutAlerts("delete From Observaciones Where Observacion =''");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 231;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select Cuentas from ConfiguracionesDelfinNet") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ConfiguracionesDelfinNet ADD Cuentas " + text2);
					BD.ConsultWithOutAlerts("update ConfiguracionesDelfinNet set Cuentas= '' where 1=1");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 234;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (~BD.ConsultWithOutAlerts("select Municipio from Configuraciones where 1=0") != 0 && BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD Municipio varchar(50)") != 0)
				{
					BD.ConsultaModificar("Configuraciones", "Municipio= 'Santa Cruz'", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select PCpedidos from Configuraciones") == 0 && BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD PCpedidos varchar(50)") != 0)
				{
					if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini)
					{
						BD.ConsultaModificar("Configuraciones", "PCpedidos = 'CAJA1'", "2=2");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "PCpedidos = '" + MyProject.Computer.Name + "'", "2=2");
					}
				}
				if (BD.ConsultWithOutAlerts("select TokenDesde from FactElectConfiguracion") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD TokenDesde date");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectCUFD ADD CodigoControl varchar(50)");
				}
				if (BD.ConsultWithOutAlerts("select nroFactura2 from FactElectConfiguracion") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD nroFactura int");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD nroFactura2 int");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD CantFactura2 int");
					BD.ConsultaModificar("FactElectConfiguracion", "nroFactura2=0 ", "2=2");
					BD.ConsultaModificar("FactElectConfiguracion", "CantFactura2=0 ", "2=2");
					BD.ConsultaModificar("FactElectConfiguracion", "nroFactura=0 ", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select CantFactura2 from FactElectConfiguracion") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD CantFactura2 int");
					BD.ConsultaModificar("FactElectConfiguracion", "CantFactura2=0 ", "2=2");
				}
				new ctlProductos().limpiarStockProductosConPreparacion(0);
				if (configuration.gMODO_ACCESS != 1)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas DROP CONSTRAINT  IX_Facturas;");
					BD.ConsultWithOutAlerts("EXEC sp_configure 'clr enabled', 1;  ");
					BD.ConsultWithOutAlerts("RECONFIGURE;");
					BD.ConsultWithOutAlerts("SELECT is_trustworthy_on FROM sys.databases WHERE name = '" + configuration.db_file + "'");
					BD.ConsultWithOutAlerts("ALTER DATABASE '" + configuration.db_file + "' SET TRUSTWORTHY ON; ");
					BD.ConsultWithOutAlerts("EXEC sp_changedbowner 'sa'");
				}
				if (BD.ConsultWithOutAlerts("select commerceId from CredencialesFidelizacionLatam") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesFidelizacionLatam ADD CommerceId varchar(50)");
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesFidelizacionLatam ADD CommerceName varchar(50)");
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesFidelizacionLatam ADD SucursalID varchar(50)");
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesFidelizacionLatam drop COLUMN codSucursal");
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesFidelizacionLatam drop COLUMN codPos");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 235;
			if ((gBdVersion < num) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.lavasecoUniversal1))
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select cufdID from facturas where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE facturas ADD CufdID int");
					BD.ConsultWithOutAlerts("ALTER TABLE facturas2 ADD CufdID int");
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultWithOutAlerts("update Facturas INNER JOIN FactElectCUFD ON Right(Facturas.Codigo,15) = FactElectCUFD.codigoControl set facturas.cufdId= FactElectCUFD.FactElectCUFDID");
					}
					else
					{
						BD.ConsultWithOutAlerts("update Facturas set facturas.cufdId= FactElectCUFD.FactElectCUFDID from Facturas INNER JOIN FactElectCUFD ON Right(Facturas.Codigo,15) = FactElectCUFD.codigoControl");
					}
				}
				if (BD.ConsultWithOutAlerts("select cufdID from FactElectFueraLinea where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectFueraLinea ADD CufdID int");
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultWithOutAlerts("update FactElectFueraLinea INNER JOIN FactElectCUFD ON FactElectFueraLinea.CUFD = FactElectCUFD.CodigoCUFD set FactElectFueraLinea.cufdId= FactElectCUFD.FactElectCUFDID");
					}
					else
					{
						BD.ConsultWithOutAlerts("update FactElectFueraLinea set FactElectFueraLinea.cufdId= FactElectCUFD.FactElectCUFDID from FactElectFueraLinea INNER JOIN FactElectCUFD ON CUFD = FactElectCUFD.CodigoCUFD");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 236;
			if ((gBdVersion < num) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.lavasecoUniversal1))
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select FirmaDir from FactElectConfiguracion") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD FirmaDir varchar(250)");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD FirmaClave varchar(150)");
					if (File.Exists(Environment.CurrentDirectory + "\\certDigicert.p12"))
					{
						BD.ConsultaModificar("FactElectConfiguracion", "FirmaDir=  '" + Environment.CurrentDirectory + "\\certDigicert.p12',FirmaClave='225922026'", "2=2");
					}
					else
					{
						BD.ConsultaModificar("FactElectConfiguracion", "FirmaDir=  '',FirmaClave=''", "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 237;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select FirmaDir from FactElectConfiguracion where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD FirmaDir varchar(250)");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD FirmaClave varchar(150)");
					if (File.Exists("certDigicert.p12"))
					{
						BD.ConsultaModificar("FactElectConfiguracion", "FirmaDir='certDigicert.p12',FirmaClave='225922026'", "2=2");
					}
					else
					{
						BD.ConsultaModificar("FactElectConfiguracion", "FirmaDir='',FirmaClave=''", "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 241;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (File.Exists("certDigicert.p12"))
				{
					BD.ConsultaModificar("FactElectConfiguracion", "FirmaDir='certDigicert.p12',FirmaClave='225922026'", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select cufdID from FactElectFueraLinea where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectFueraLinea ADD CufdID int");
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultWithOutAlerts("update FactElectFueraLinea INNER JOIN FactElectCUFD ON FactElectFueraLinea.CUFD = FactElectCUFD.CodigoCUFD set FactElectFueraLinea.cufdId= FactElectCUFD.FactElectCUFDID");
					}
					else
					{
						BD.ConsultWithOutAlerts("update FactElectFueraLinea set FactElectFueraLinea.cufdId= FactElectCUFD.FactElectCUFDID from FactElectFueraLinea INNER JOIN FactElectCUFD ON CUFD = FactElectCUFD.CodigoCUFD");
					}
				}
				BD.ConsultaModificar("FactElectFueraLinea", "final=null", "inicio=final");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 248;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("DROP PROCEDURE backupBD");
					BD.ConsultWithOutAlerts("CREATE PROCEDURE backupBD @Name nvarchar(100) AS BEGIN DECLARE @SQLStatement VARCHAR(2000) SET @SQLStatement = '" + MyProject.Application.Info.DirectoryPath + "\\Backups\\' + CONVERT(nvarchar(30), GETDATE(), 110) +'.bak' BACKUP DATABASE @Name TO  DISK = @SQLStatement  WITH INIT END");
					BD.ConsultWithOutAlerts("ALTER TABLE Observaciones  WITH CHECK ADD  CONSTRAINT FK_Observaciones_DetalleCuenta FOREIGN KEY(DetalleCuentaID) REFERENCES DetalleCuenta (ID)");
					BD.ConsultWithOutAlerts("ALTER TABLE Observaciones CHECK CONSTRAINT FK_Observaciones_DetalleCuenta");
					BD.ConsultWithOutAlerts("ALTER TABLE Pagos  WITH CHECK ADD  CONSTRAINT FK_Pagos_DetalleCuenta FOREIGN KEY(DetalleCuentaID)REFERENCES DetalleCuenta (ID)");
					BD.ConsultWithOutAlerts("ALTER TABLE Pagos CHECK CONSTRAINT FK_Pagos_DetalleCuenta");
					BD.ConsultWithOutAlerts("ALTER TABLE Pagos  WITH CHECK ADD  CONSTRAINT FK_Pagos_Pagos FOREIGN KEY(CuentaID)REFERENCES Cuentas (CuentaID)");
					BD.ConsultWithOutAlerts("ALTER TABLE Pagos CHECK CONSTRAINT FK_Pagos_Pagos");
					BD.ConsultWithOutAlerts("ALTER TABLE ProductosCombos  WITH CHECK ADD  CONSTRAINT FK_ProductosCombos_DetalleCuenta FOREIGN KEY(DetalleCuentaID) REFERENCES DetalleCuenta (ID)");
					BD.ConsultWithOutAlerts("ALTER TABLE ProductosCombos CHECK CONSTRAINT FK_ProductosCombos_DetalleCuenta");
					BD.ConsultWithOutAlerts("CREATE INDEX IX_Observaciones ON Observaciones (DetalleCuentaID)");
					BD.ConsultWithOutAlerts("CREATE INDEX IX_Pagos ON Pagos(DetalleCuentaID)");
					BD.ConsultWithOutAlerts("CREATE INDEX IX_ProductosCombos ON ProductosCombos(DetalleCuentaID)");
					BD.ConsultWithOutAlerts("ALTER TABLE Cuentas  ADD CONSTRAINT CuentasKey  PRIMARY KEY (CuentaID)");
				}
				if (!Directory.Exists(MyProject.Application.Info.DirectoryPath + "\\Backups"))
				{
					Directory.CreateDirectory(MyProject.Application.Info.DirectoryPath + "\\Backups");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 249;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select ConfiguracionID from FactElectActividades where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectActividades ADD ConfiguracionID int");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectLeyes ADD ConfiguracionID int");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ADD ConfiguracionID int");
					if (gConfiguracionID == 0)
					{
						gConfiguracionID = 1;
					}
					BD.ConsultaModificar("FactElectActividades", "ConfiguracionID= " + Conversions.ToString(gConfiguracionID), "2=2");
					BD.ConsultaModificar("FactElectLeyes", "ConfiguracionID= " + Conversions.ToString(gConfiguracionID), "2=2");
					BD.ConsultaModificar("FactElectProductosServicios", "ConfiguracionID= " + Conversions.ToString(gConfiguracionID), "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 250;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas ALTER COLUMN Correo varchar(200)");
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ALTER COLUMN Correo varchar(200)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 252;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				int num7 = 235;
				do
				{
					if (File.Exists("restotech" + Conversions.ToString(num7) + ".zip"))
					{
						File.Delete("restotech" + Conversions.ToString(num7) + ".zip");
					}
					num7++;
				}
				while (num7 <= 251);
				string[] files = Directory.GetFiles(MyProject.Application.Info.DirectoryPath, "*.ToBeDeleted", SearchOption.TopDirectoryOnly);
				for (int id2 = 0; id2 < files.Length; id2++)
				{
					File.Delete(files[id2]);
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 253;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (File.Exists("restotech" + Conversions.ToString(num - 1) + ".zip"))
				{
					File.Delete("restotech" + Conversions.ToString(num - 1) + ".zip");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 255;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select TipoDocumentoID from Clientes where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD TipoDocumentoID int");
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultWithOutAlerts("update Clientes INNER JOIN Facturas ON Clientes.CI = Facturas.NIT set Clientes.TipoDocumentoID= Facturas.TipoDocumentoID where Facturas.codigoID is null");
					}
					else
					{
						BD.ConsultWithOutAlerts("update Clientes set  Clientes.TipoDocumentoID= Facturas.TipoDocumentoID from Clientes INNER JOIN Facturas ON (Clientes.CI = Facturas.NIT and Facturas.codigoID is null)");
					}
				}
				if (File.Exists("restotech" + Conversions.ToString(num - 1) + ".zip"))
				{
					File.Delete("restotech" + Conversions.ToString(num - 1) + ".zip");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 256;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select Orden from FactElectUnidadesMedidas where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectUnidadesMedidas ADD Orden int");
					BD.ConsultaModificar("FactElectUnidadesMedidas", "Orden= 1", "2=2");
					BD.ConsultaModificar("FactElectUnidadesMedidas", "Orden=0", "codigo in (5,26,57, 58, 97)");
				}
				if (File.Exists("restotech" + Conversions.ToString(num - 1) + ".zip"))
				{
					File.Delete("restotech" + Conversions.ToString(num - 1) + ".zip");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 259;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaEliminar("Logg", "Accion like '% se cambio el precio de 0 a %'");
				BD.ConsultaModificar("Productos", "esCombo = " + armarBolean(0), "esCombo =" + armarBolean(1) + " and id not in (select ParaProductoID from Preparaciones)");
				BD.ConsultaModificar("Productos", "TienePreparacion = " + armarBolean(0), "TienePreparacion =" + armarBolean(1) + " and id not in (select ParaProductoID from Preparaciones)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 262;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD manejaSerie bit");
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ADD manejaImei bit");
				BD.ConsultaModificar("Productos", "manejaImei=" + armarBolean(0), "2=2");
				BD.ConsultaModificar("Productos", "manejaserie=" + armarBolean(0), "2=2");
				BD.ConsultaModificar("detalleCuenta", "Cerrada=" + armarBolean(1), "Debe=0 and Cerrada=" + armarBolean(0));
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 265;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("detalleCuenta", "Cerrada=" + armarBolean(1), "Debe=0 and Cerrada=" + armarBolean(0));
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Ajustes ALTER COLUMN Observacion varchar(500)");
					BD.ConsultWithOutAlerts("ALTER TABLE Compras ALTER COLUMN Comentarios varchar(500)");
					BD.ConsultWithOutAlerts("ALTER TABLE DetallesAjustes ALTER COLUMN Observacion varchar(500)");
					BD.ConsultWithOutAlerts("ALTER TABLE Gastos ALTER COLUMN Observacion varchar(500)");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 266;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select ResponsableID from Almacenes where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Almacenes ADD ResponsableID int");
					BD.ConsultaModificar("Almacenes", "ResponsableID= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 267;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select ConfiguracionID from FactElectFueraLinea where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectFueraLinea ADD ConfiguracionID int");
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultaModificar("FactElectFueraLinea INNER JOIN  FactElectCUFD ON FactElectFueraLinea.CUFD = FactElectCUFD.CodigoCUFD", "FactElectFueraLinea.ConfiguracionID =FactElectCUFD.ConfiguracionID");
					}
					else
					{
						BD.ConsultaModificar("FactElectFueraLinea", "FactElectFueraLinea.ConfiguracionID =FactElectCUFD.ConfiguracionID from FactElectFueraLinea INNER JOIN  FactElectCUFD ON FactElectFueraLinea.CUFD = FactElectCUFD.CodigoCUFD");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 271;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select NombreLocal from CredencialesPedidosYa") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CredencialesPedidosYa ADD NombreLocal varchar(50)");
					BD.ConsultaModificar("CredencialesPedidosYa", "NombreLocal='PeYa'");
				}
				BD.ConsultWithOutAlerts("ALTER TABLE Gastos_Facturas ALTER COLUMN Codigo varchar(80)");
				try
				{
					if (Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("count(*)", "Cuentas", "nombre like 'Pago en linea' or MetodoPagoSIN=33").Rows[0][0], 0, TextCompare: false))
					{
						if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
						{
							int num8 = Conversions.ToInteger(Operators.AddObject(BD.ConsultaVer("max(CuentaID)", "Cuentas").Rows[0][0], 1));
							BD.ConsultaInsertar(Conversions.ToString(num8) + ",'Pago en linea','','',0,''," + armarBolean(1) + "," + armarBolean(0) + "," + armarBolean(0) + "," + armarBolean(0) + ",33," + armarBolean(0), "Cuentas(cuentaid, nombre, banco, tipoCuenta, nro, Observacion, Activa, Moneda, EsDeposito, EsGiftCard, MetodoPagoSIN, esAdmin)");
						}
						else
						{
							string data2 = "'Pago en linea','','',0,''," + armarBolean(1) + "," + armarBolean(0) + "," + armarBolean(0) + "," + armarBolean(0) + ",33," + armarBolean(0);
							int id3 = 0;
							BD.ConsultaInsertar3(data2, "Cuentas(nombre,banco, tipoCuenta,nro,Observacion,Activa,Moneda,EsDeposito,EsGiftCard,MetodoPagoSIN, esAdmin)", ref id3);
						}
					}
				}
				catch (Exception ex)
				{
					ProjectData.SetProjectError(ex);
					Exception ex2 = ex;
					ProjectData.ClearProjectError();
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 277;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Visitas", "TipoEnvioID= NULL", "TipoEnvioID=0");
				BD.ConsultaModificar("Cuentas", "EsAdmin= " + armarBolean(0), "EsAdmin is null");
				if (BD.ConsultWithOutAlerts("select ModificaProductoID from PreparacionesComodines where 1=0") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE PreparacionesComodines ADD ModificaProductoID int");
					BD.ConsultaModificar("PreparacionesComodines", "ModificaProductoID= 0", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 282;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=33", "MetodoPagoSIN is null");
				BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=33", "MetodoPagoSIN =0");
				BD.ConsultaModificar("CredencialesPedidosYa", "NombreLocal= 'PeYA'", "NombreLocal is null");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 284;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Cuentas", "Nombre= 'Cupon PeYA'", "Nombre like 'Cupones Pedidos Ya'");
				BD.ConsultaModificar("Cuentas", "Nombre= 'Cupon PeYA'", "Nombre like 'CuponesPedidos Ya'");
				if (configuration.gTipoFacturacion == 2)
				{
					clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
					if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
					{
						if ((clsFactElecConfig2.NIT == 148718022) | (clsFactElecConfig2.NIT == 150078029) | (clsFactElecConfig2.NIT == 169598020) | (clsFactElecConfig2.NIT == 1986427015) | (clsFactElecConfig2.NIT == 260726022) | (clsFactElecConfig2.NIT == 374696026) | (clsFactElecConfig2.NIT == 225922026) | (clsFactElecConfig2.NIT == 333618027) | (clsFactElecConfig2.NIT == 353658024) | (clsFactElecConfig2.NIT == 368768022) | (clsFactElecConfig2.NIT == 5898777013L))
						{
							BD.ConsultaModificar("Productos", "UnidadSIN=47", "UnidadSIN=57");
						}
						else
						{
							BD.ConsultaModificar("Productos", "UnidadSIN=58", "UnidadSIN=57");
						}
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 285;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas DROP CONSTRAINT  IX_Facturas;");
				DataTable dataTable3 = BD.ConsultaVer("select * from (select count(*) as cant, min(facturaID) as FacturaID from Facturas where CodigoID is null group by NroFactura,CufdID  ) as tab1 where cant>1");
				if (dataTable3.Rows.Count > 0)
				{
					ctlConfiguraciones obj = new ctlConfiguraciones();
					string sucursal = "";
					string Descripcion = "";
					bool conporcentaje = default(bool);
					double porcentaje = default(double);
					obj.DevolverPorcentaje(ref conporcentaje, ref porcentaje, ref sucursal, ref Descripcion);
					string RazonSocial = "";
					obj.DevolverRazonSocial(ref RazonSocial);
					ImprimiendoComandas.sendEmailAnularFactura("asoljancic@toptech.com.bo", "Tenia duplicadas " + Conversions.ToString(dataTable3.Rows.Count) + ", " + RazonSocial + " : " + Descripcion + " suc " + sucursal, "tenia duplicadas");
					int id3 = dataTable3.Rows.Count - 1;
					for (int k = 0; k <= id3; k++)
					{
						BD.ConsultaModificar("Facturas", "CufdID=0", Conversions.ToString(Operators.ConcatenateObject("FacturaId=", dataTable3.Rows[k]["FacturaID"])));
					}
				}
				if (BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD CONSTRAINT IX_Facturas UNIQUE (NroFactura,CodigoID, CufdID) ") == 0)
				{
					ctlConfiguraciones obj2 = new ctlConfiguraciones();
					string sucursal2 = "";
					string Descripcion2 = "";
					bool conporcentaje2 = default(bool);
					double porcentaje2 = default(double);
					obj2.DevolverPorcentaje(ref conporcentaje2, ref porcentaje2, ref sucursal2, ref Descripcion2);
					string RazonSocial2 = "";
					obj2.DevolverRazonSocial(ref RazonSocial2);
					ImprimiendoComandas.sendEmailAnularFactura("asoljancic@toptech.com.bo", "Mensaje de Restotech: No se creo la llave unica de 'CONSTRAINT IX_Facturas UNIQUE (NroFactura,CodigoID, CufdID)' para " + RazonSocial2 + " : " + Descripcion2 + " suc " + sucursal2, "Revisar llave unica para este cliente");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 287;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select CodigoRecepcion from Facturas where 0=1") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD CodigoRecepcion varchar(40) NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD CodigoRecepcion varchar(40) NULL");
					BD.ConsultaModificar("Facturas", "CodigoRecepcion='1'", "2=2");
					BD.ConsultaModificar("Facturas2", "CodigoRecepcion='1'", "2=2");
				}
				try
				{
					if (!Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("select count(*) from Cuentas where EsGiftCard=" + armarBolean(1)).Rows[0][0], 0, TextCompare: false))
					{
						if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
						{
							int num9 = Conversions.ToInteger(Operators.AddObject(BD.ConsultaVer("max(CuentaID)", "Cuentas").Rows[0][0], 1));
							BD.ConsultaInsertar(Conversions.ToString(num9) + ",'GiftCard','','',0,''," + armarBolean(1) + "," + armarBolean(0) + "," + armarBolean(0) + "," + armarBolean(1), "Cuentas(cuentaid, nombre,banco, tipoCuenta,nro,Observacion,Activa,Moneda,EsDeposito,EsGiftCard)");
						}
						else
						{
							string data3 = "'GiftCard','','',0,''," + armarBolean(1) + "," + armarBolean(0) + "," + armarBolean(0) + "," + armarBolean(1);
							int id4 = 0;
							BD.ConsultaInsertar3(data3, "Cuentas(nombre,banco, tipoCuenta,nro,Observacion,Activa,Moneda,EsDeposito,EsGiftCard)", ref id4);
						}
					}
				}
				catch (Exception ex3)
				{
					ProjectData.SetProjectError(ex3);
					Exception ex4 = ex3;
					ProjectData.ClearProjectError();
				}
				BD.ConsultaModificar("Cuentas", "Activa= " + armarBolean(0), "Nombre like 'Cuenta QR'");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 297;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select PeYaId from DeliveryApp where 0=1") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD PeYaId integer NULL");
					BD.ConsultaModificar("DeliveryApp", "PeYaId=1", "Plataforma=1");
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio)
				{
					BD.ConsultaModificar("Cuentas", "Activa= " + armarBolean(0), "Nombre like 'Pago en linea'");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 298;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.Jarana) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Hito) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.InesEspana) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Serafina))
				{
					ctlProductos ctlProductos2 = new ctlProductos();
					if (!ctlProductos2.ExisteProductoNombre("CANT PERSONAS"))
					{
						ctlProductos2.crearProducto("CANT PERSONAS");
						BD.ConsultaModificar("Configuraciones", "CantidadPersonas =" + armarBolean(1), "CantidadPersonas is null");
					}
				}
				if (BD.ConsultWithOutAlerts("select ImprimeOtrasCuentas from Configuraciones where 0=1") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD ImprimeOtrasCuentas bit NULL");
					if (configuration.gSoloFacturacionGrande | configuration.gFormatoFacturaGrande | configuration.gVersionLite)
					{
						BD.ConsultaModificar("Configuraciones", "ImprimeOtrasCuentas=" + armarBolean(0), "2=2");
					}
					else if (configuration.gManejaTurnos)
					{
						BD.ConsultaModificar("Configuraciones", "ImprimeOtrasCuentas=" + armarBolean(1), "2=2");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "ImprimeOtrasCuentas=" + armarBolean(0), "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 301;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("DetalleCuenta", "debe=0, pago=0", "borrada=" + armarBolean(1));
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleProductosCompra ALTER COLUMN CostoUnitario float");
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleProductosCompra ALTER COLUMN CostoBruto float");
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleProductosCompra ALTER COLUMN Cantidad float");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 303;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectActividades ALTER COLUMN Codigo varchar(10)");
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ALTER COLUMN Codigo varchar(10)");
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ALTER COLUMN CodigoActividad varchar(10)");
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ALTER COLUMN CodigoSIN varchar(10)");
				BD.ConsultWithOutAlerts("ALTER TABLE Productos ALTER COLUMN ActividadSIN varchar(10)");
				if (configuration.gTipoFacturacion == 2)
				{
					try
					{
						clsFactElecConfig clsFactElecConfig3 = new clsFactElecConfig();
						if (clsFactElecConfig3.devolverDatosSiTokenActivo1())
						{
							clsFacElecSyncDatos obj3 = new clsFacElecSyncDatos(unchecked((int)clsFactElecConfig3.CodigoAmbiente));
							obj3.SyncActividades(esTest: false);
							obj3.SyncCodigoProductos(esTest: false);
							DataTable dataTable4 = BD.ConsultaVer("select nombre from Productos where ActividadSIN not in (select Codigo  from FactElectActividades) and Borrado =" + armarBolean(0));
							string text3 = "";
							int id4 = dataTable4.Rows.Count - 1;
							for (int l = 0; l <= id4; l++)
							{
								text3 = Conversions.ToString(Operators.ConcatenateObject(text3, Operators.ConcatenateObject(dataTable4.Rows[l][0], ", ")));
							}
							if (text3.Length > 0)
							{
								Interaction.MsgBox("URGENTEMENTE Avisar al admin. Revisar la actividad SIN de estos productos  \r\n" + text3);
							}
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
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 304;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectLeyes ALTER COLUMN Codigo varchar(10)");
				if (BD.ConsultWithOutAlerts("select FacturaObligatoria from Cuentas where 0=1") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Cuentas ADD FacturaObligatoria bit NULL");
					BD.ConsultaModificar("Cuentas", "FacturaObligatoria=" + armarBolean(0), "CuentaId<=2");
					BD.ConsultaModificar("Cuentas", "FacturaObligatoria=" + armarBolean(1), "CuentaId>=3");
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Batos))
					{
						BD.ConsultaModificar("Cuentas", "FacturaObligatoria=" + armarBolean(0), " CuentaId=4");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 309;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Pagos ALTER COLUMN MaquinaPago varchar(30)");
				BD.ConsultWithOutAlerts("ALTER TABLE Visitas ALTER COLUMN Observacion varchar(100)");
				BD.ConsultWithOutAlerts("ALTER TABLE Visitas ALTER COLUMN Identificador varchar(50)");
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta ALTER COLUMN Comentarios varchar(150)");
				if (configuration.gFormatoFacturaGrande | configuration.gSoloFacturacionGrande)
				{
					BD.ConsultaModificar("Cuentas", "FacturaObligatoria=" + armarBolean(0), "CuentaId<>2");
				}
				BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=1", "CuentaID= 4");
				BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=7", "nombre like '%transf%'");
				BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=7", "nombre like '%banc%'");
				BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=33", "MetodoPagoSIN=0 or MetodoPagoSIN is null");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 312;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=7", "nombre like '%QR%'");
				BD.ConsultaModificar("Cuentas", "EsGiftCard= " + armarBolean(1), "nombre like '%gift%'");
				BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=27", "esGiftCard = " + armarBolean(1));
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio)
				{
					BD.ConsultaModificar("Cuentas", "FacturaObligatoria=" + armarBolean(0), " CuentaId=4");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 315;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS != 1 && ((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Batos)))
				{
					BD.ConsultaModificar("Productos", "nombre = UPPER(SUBSTRING(nombre, 1, 1)) + LOWER(SUBSTRING(nombre, 2, LEN(nombre))) ", "2=2");
					BD.ConsultaModificar("TiposProductos", "Descripcion = UPPER(SUBSTRING(Descripcion, 1, 1)) + LOWER(SUBSTRING(Descripcion, 2, LEN(Descripcion))) ", "2=2");
				}
				BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD SyncCentral bit NULL");
				BD.ConsultaModificar("Configuraciones", "SyncCentral=0", "2=2");
				BD.ConsultWithOutAlerts("ALTER TABLE Anticipos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE AnticiposCuentas ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Arqueo ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Asistentes ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Borrados ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Clientes ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE CobrosQR ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Compras ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Cuentas ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta_Asistentes ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuentas_Paquetes ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE DetalleProductosCompra ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE DetallesAjustes ADD flagSync integer NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE DetallesProduccion ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE DetallesTraspasos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Devoluciones ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectCUFD ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectCUIS ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE FactElectFueraLinea ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Gastos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Gastos_Facturas ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Logg ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Movimientos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Movimientos_Turnos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Observaciones ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE ObservacionesCocina ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Pagos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE ParaLLevar ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE PersonasSinMesa ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE PreparacionesComodines ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Preprocesamientos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE PreprocesamientosDe ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE PreprocesamientosPara ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Produccion ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE ProductosCombos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE ProductosUsos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Seguimiento ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Traspasos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE UsoPaquetes ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Visitas ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Turnos ADD flagSync bit NULL");
				BD.ConsultWithOutAlerts("ALTER TABLE Pagos  WITH CHECK ADD  CONSTRAINT FK_Pagos_Cuentas FOREIGN KEY(CuentaID) REFERENCES Cuentas (CuentaID)");
				BD.ConsultWithOutAlerts("CREATE INDEX ix_PagosCuenta ON Pagos (CuentaID)");
				BD.ConsultaModificar("Productos", "UnidadSIN=58", "UnidadSIN=57");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 318;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Pagos ALTER COLUMN maquinaPago varchar(30)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 319;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE DeliveryApp ADD PedidoListo bit NULL");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 321;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 323;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (!(configuration.gSoloFacturacionGrande | configuration.gFormatoFacturaGrande | configuration.gSupermercado))
				{
					BD.ConsultaModificar("Productos", "UnidadSIN=62", "UnidadSIN=58");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 325;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (!configuration.gComidaRapida && BD.ConsultWithOutAlerts("select top 1 PropinaID from Propinas") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Propinas(\t\t       PropinaID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFecha datetime,\t  \t\tMontoBs float,\t  \t\tMaquinaPropina varchar(30),\t  \t\tVisitaID integer,\t  \t\tcuentaID integer,\t  \t\tMeseroID integer,\t  \t\tPRIMARY KEY (PropinaID))");
				}
				if (BD.ConsultWithOutAlerts("select top 1 PagoAgrupadorID from PagosAgrupador") == 0)
				{
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultWithOutAlerts("CREATE TABLE PagosAgrupador(\t\t          PagoAgrupadorID AUTOINCREMENT, \t\tAgrupadorGUID varchar(50),\t  \t\tPRIMARY KEY (PagoAgrupadorID))");
					}
					else
					{
						int num10 = Conversions.ToInteger(Operators.AddObject(NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select max(AgruparPagoID) from Pagos").Rows[0][0]), 0), 1));
						BD.ConsultWithOutAlerts("CREATE TABLE PagosAgrupador(\t\t          PagoAgrupadorID integer IDENTITY(" + Conversions.ToString(num10) + ",1) Not NULL, \t\tAgrupadorGUID uniqueidentifier,\t  \t\tPRIMARY KEY (PagoAgrupadorID))");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 326;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Proveedores ADD DiasCredito integer NULL");
				BD.ConsultaModificar("Proveedores", "DiasCredito= 0", "2=2");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 331;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gComidaRapida && BD.ConsultWithOutAlerts("select top 1 PropinaID from Propinas") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Propinas(\t\t       PropinaID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFecha datetime,\t  \t\tMontoBs float,\t  \t\tMaquinaPropina varchar(30),\t  \t\tVisitaID integer,\t  \t\tcuentaID integer,\t  \t\tMeseroID integer,\t  \t\tPRIMARY KEY (PropinaID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 346;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS == 0)
				{
					DataTable dataTable5 = BD.ConsultaVer("COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH", "INFORMATION_SCHEMA.COLUMNS", "TABLE_NAME = 'Productos' AND COLUMN_NAME = 'Nombre'");
					if (dataTable5.Rows.Count > 0 && Operators.ConditionalCompareObjectLess(dataTable5.Rows[0]["CHARACTER_MAXIMUM_LENGTH"], 100, TextCompare: false))
					{
						BD.ConsultWithOutAlerts("ALTER TABLE Productos ALTER COLUMN Nombre varchar(100)");
					}
				}
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio)
				{
					BD.ConsultWithOutAlerts("drop table ProductosUsos");
					BD.ConsultWithOutAlerts("CREATE TABLE ProductosUsos(\t\t       ProductoUsoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tCantidad float NOT null,\t  \t\tProductoID integer NOT null,\t  \t\tDetalleCuentaID integer null,\t  \t\tproduccionID integer null,\t  \t\tpreProcesamientoID integer null,\t  \t\tfecha dateTime null,\t  \t\tflagSync bit null,\t  \t\tPRIMARY KEY (ProductoUsoID))");
					BD.ConsultWithOutAlerts("update DetalleCuenta set Comentarios= LEFT(Comentarios, 150) where len(Comentarios)>150");
					BD.ConsultWithOutAlerts("ALTER TABLE DetalleCuenta ALTER COLUMN Comentarios varchar(150)");
					BD.ConsultWithOutAlerts("UPDATE Anticipos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE AnticiposCuentas SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Arqueo SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Asistentes SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Borrados SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Clientes SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE CobrosQR SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Compras SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Cuentas SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE DetalleCuenta SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE DetalleCuenta_Asistentes SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE DetalleCuentas_Paquetes SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE DetalleProductosCompra SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE DetallesAjustes ADD flagSync integer NULL");
					BD.ConsultWithOutAlerts("UPDATE DetallesProduccion SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE DetallesTraspasos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Devoluciones SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE FactElectCUFD SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE FactElectCUIS SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE FactElectFueraLinea SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Facturas SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Facturas2 SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Gastos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Gastos_Facturas SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Logg SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Movimientos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Movimientos_Turnos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Observaciones SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE ObservacionesCocina SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Pagos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE ParaLLevar SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE PersonasSinMesa SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE PreparacionesComodines SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Preprocesamientos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE PreprocesamientosDe SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE PreprocesamientosPara SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Produccion SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE ProductosCombos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE ProductosUsos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Seguimiento SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Traspasos SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE UsoPaquetes SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Visitas SET flagSync=NULL");
					BD.ConsultWithOutAlerts("UPDATE Turnos SET flagSync=NULL");
				}
				BD.ConsultWithOutAlerts("update Visitas set Identificador= LEFT(Identificador, 50) where len(Identificador)>50");
				BD.ConsultWithOutAlerts("ALTER TABLE Visitas ALTER COLUMN Identificador varchar(50)");
				BD.ConsultaModificar("TiposProductos", "TipoUsuarioId=0 ", "TipoUsuarioId is null");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 350;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Batos))
				{
					BD.ConsultaModificar("TipoEnvios", "Nombre='SERVICIO DE MOTOS'", "Nombre='SERVICIO DE MOTO'");
					BD.ConsultaModificar("TipoEnvios", "Nombre='UBER EATS'", "Nombre='UBERT EATS'");
					BD.ConsultaModificar("TipoEnvios", "Nombre='PEDIDOS ONLINE'", "Nombre='PEDIDOS ON LINE'");
					BD.ConsultaModificar("TipoEnvios", "Orden=Orden+1", "1=1");
					BD.ConsultaModificar("TipoEnvios", "Orden=1", "Nombre='DIRECTO CLIENTE'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Patio Service' where nombre like 'Banco Patio Service'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Pedidos On Line' where nombre like 'Banco Pedido On Line'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Pedidos Ya Tarjeta' where nombre like 'Banco Pedidos Ya'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Ubert Eats' where nombre like 'Banco Uber Eats'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Yaigo' where nombre like 'Banco Yaigo'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Patio Service' where nombre like 'Bco. Patio Service'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Pedidos On Line' where nombre like 'Bco. Pedidos On Line'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Pedidos Ya Tarjeta' where nombre like 'Bco. Pedidos Ya'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Pedidos Ya Cupones' where nombre like 'Bco. Pedidos Ya Cupones'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Pedidos Ya Tarjeta' where nombre like 'Bco. Pedidos Ya Tarjeta'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Ubert Eats' where nombre like 'Bco. Uber Eats'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Ubert Eats' where nombre like 'Bco. Ubert Eats'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Yaigo' where nombre like 'Bco. Yaigo'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Pedidos Ya Cupones' where nombre like 'Cupon PeYA'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Pedidos Ya Cupones' where nombre like 'Cupones Pedido Ya'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Bco. Pedidos Ya Tarjeta' where nombre like 'Tarjeta Pedidos Ya'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Transferencias' where nombre like 'Transferencia'");
					BD.ConsultWithOutAlerts("update Cuentas set nombre='Transferencias' where nombre like 'Trnasferencia'");
					DataTable dataTable6 = BD.ConsultaVer("TipoEnvioID", "TipoEnvios", "Nombre='DIRECTO CLIENTE'");
					if (dataTable6.Rows.Count > 0)
					{
						int num11 = Conversions.ToInteger(dataTable6.Rows[0][0]);
						BD.ConsultaModificar("Visitas", "TipoEnvioID=" + Conversions.ToString(num11), "TipoEnvioID is null");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 355;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gTipoFacturacion == 2)
				{
					DataTable dataTable7 = BD.ConsultaVer("FacturaID,FechaEmision, CufdID ", "Facturas", "EstadoSiat=3 and FueraLineaID =0 and FechaEmision >" + ArmarFecha(new DateTime(2023, 9, 1)));
					if (dataTable7.Rows.Count > 0)
					{
						int num12 = dataTable7.Rows.Count - 1;
						for (int m = 0; m <= num12; m++)
						{
							DataTable dataTable8 = BD.ConsultaVer("select * from FactElectFueraLinea where " + ArmarFecha(Conversions.ToDate(dataTable7.Rows[m]["FechaEmision"])) + " between Inicio  and Final");
							if (dataTable8.Rows.Count > 0)
							{
								ctlFacturas obj4 = new ctlFacturas();
								obj4.SetFacturaID(Conversions.ToInteger(dataTable7.Rows[m]["FacturaID"]));
								obj4.setContingenciaID(Conversions.ToInteger(dataTable8.Rows[0][0]));
								continue;
							}
							clsFactElecContingencias obj5 = new clsFactElecContingencias();
							DateTime inicio = Conversions.ToDate(dataTable7.Rows[m]["FechaEmision"]).AddSeconds(-1.0);
							string cUFDactual = Conversions.ToString(BD.ConsultaVer(Conversions.ToString(Operators.ConcatenateObject("select CodigoCUFD from FactElectCUFD where FactElectCUFD.FactElectCUFDID=", dataTable7.Rows[m]["CufdID"]))).Rows[0][0]);
							int contingenciaID = obj5.CrearContingenciaFueraDeLinea(inicio, 1, cUFDactual, "", Conversions.ToInteger(dataTable7.Rows[m]["CufdID"]), desdeCelular: false);
							ctlFacturas obj6 = new ctlFacturas();
							obj6.SetFacturaID(Conversions.ToInteger(dataTable7.Rows[m]["FacturaID"]));
							obj6.setContingenciaID(contingenciaID);
						}
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 356;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 FacturarPropina from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD FacturarPropina bit NULL");
					if (configuration.gComidaRapida)
					{
						BD.ConsultaModificar("Configuraciones", "FacturarPropina=" + armarBolean(0), "2=2");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "FacturarPropina=" + armarBolean(1), "2=2");
					}
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.KAO) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Hapo))
					{
						BD.ConsultaModificar("Configuraciones", "FacturarPropina=" + armarBolean(0), "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 359;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 PrecioUni from ProductosCombos") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE ProductosCombos ADD PrecioUni money NULL");
					if (configuration.gMODO_ACCESS == 1)
					{
						BD.ConsultWithOutAlerts("SELECT ProductosCombos.ProductoComboID as ID,DeliveryApp_Detalle_Combo.PRICE\r\n                        INTO NuevaTabla\r\n                        FROM (((((DeliveryApp \r\n                            INNER JOIN DeliveryApp_detalle ON DeliveryApp.DeliveryID = DeliveryApp_detalle.DeliveryAppID)\r\n                            INNER JOIN DeliveryApp_Detalle_Combo ON DeliveryApp_detalle.DeliveryDetalleID = DeliveryApp_Detalle_Combo.DeliveryApp_DetalleID)\r\n                            INNER JOIN Productos ON (DeliveryApp_Detalle_Combo.ORDER_NAME = Productos.Nombre and Productos.Habilitado=" + armarBolean(1) + "))\r\n                            INNER JOIN Visitas ON DeliveryApp.VISITA_ID = Visitas.ID)\r\n                            INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID)\r\n                            INNER JOIN ProductosCombos ON DetalleCuenta.ID = ProductosCombos.DetalleCuentaID AND Productos.ID = ProductosCombos.ProductoID\r\n                            WHERE DeliveryApp_Detalle_Combo.PRICE > 0 and Plataforma=1");
						BD.ConsultWithOutAlerts("UPDATE ProductosCombos \r\n                            INNER JOIN NuevaTabla ON ProductosCombos.ProductoComboID = NuevaTabla.ID \r\n                            SET ProductosCombos.PrecioUni = NuevaTabla.PRICE");
						BD.ConsultWithOutAlerts("drop table NuevaTabla");
						BD.ConsultWithOutAlerts("SELECT ProductosCombos.ProductoComboID as ID,DeliveryApp_Detalle_Combo.PRICE\r\n                        INTO NuevaTabla\r\n                        FROM (((((DeliveryApp \r\n                            INNER JOIN DeliveryApp_detalle ON DeliveryApp.DeliveryID = DeliveryApp_detalle.DeliveryAppID)\r\n                            INNER JOIN DeliveryApp_Detalle_Combo ON DeliveryApp_detalle.DeliveryDetalleID = DeliveryApp_Detalle_Combo.DeliveryApp_DetalleID)\r\n                            INNER JOIN Productos ON (DeliveryApp_Detalle_Combo.ORDER_NAME = Productos.Nombre and Productos.Habilitado=" + armarBolean(1) + "))\r\n                            INNER JOIN Visitas ON DeliveryApp.VISITA_ID = Visitas.ID)\r\n                            INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID)\r\n                            INNER JOIN ProductosCombos ON DetalleCuenta.ID = ProductosCombos.DetalleCuentaID AND Productos.ID = ProductosCombos.ProductoID\r\n                            WHERE DeliveryApp_Detalle_Combo.PRICE > 0 and Plataforma<>1");
						BD.ConsultWithOutAlerts("UPDATE ProductosCombos \r\n                            INNER JOIN NuevaTabla ON ProductosCombos.ProductoComboID = NuevaTabla.ID \r\n                            SET ProductosCombos.PrecioUni = NuevaTabla.PRICE");
						BD.ConsultWithOutAlerts("drop table NuevaTabla");
					}
					else
					{
						BD.ConsultWithOutAlerts("UPDATE ProductosCombos\r\n                    SET PrecioUni = DeliveryApp_Detalle_Combo.PRICE\r\n                    FROM DeliveryApp \r\n                    INNER JOIN DeliveryApp_detalle ON DeliveryApp.DeliveryID = DeliveryApp_detalle.DeliveryAppID\r\n                    INNER JOIN DeliveryApp_Detalle_Combo ON DeliveryApp_detalle.DeliveryDetalleID = DeliveryApp_Detalle_Combo.DeliveryApp_DetalleID\r\n                    INNER JOIN Productos ON DeliveryApp_Detalle_Combo.ORDER_SKU = Productos.CodigoPY  and Productos.Habilitado=" + armarBolean(1) + "\r\n                    INNER JOIN Visitas ON DeliveryApp.VISITA_ID = Visitas.ID\r\n                    INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID\r\n                    INNER JOIN ProductosCombos ON DetalleCuenta.ID = ProductosCombos.DetalleCuentaID AND Productos.ID = ProductosCombos.ProductoID\r\n                    WHERE DeliveryApp_Detalle_Combo.PRICE > 0 and Plataforma=1");
						BD.ConsultWithOutAlerts("UPDATE ProductosCombos\r\n                    SET PrecioUni = DeliveryApp_Detalle_Combo.PRICE\r\n                    FROM DeliveryApp \r\n                    INNER JOIN DeliveryApp_detalle ON DeliveryApp.DeliveryID = DeliveryApp_detalle.DeliveryAppID\r\n                    INNER JOIN DeliveryApp_Detalle_Combo ON DeliveryApp_detalle.DeliveryDetalleID = DeliveryApp_Detalle_Combo.DeliveryApp_DetalleID\r\n                    INNER JOIN Productos ON DeliveryApp_Detalle_Combo.ORDER_NAME = Productos.Nombre and Productos.Habilitado=" + armarBolean(1) + "\r\n                    INNER JOIN Visitas ON DeliveryApp.VISITA_ID = Visitas.ID\r\n                    INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID\r\n                    INNER JOIN ProductosCombos ON DetalleCuenta.ID = ProductosCombos.DetalleCuentaID AND Productos.ID = ProductosCombos.ProductoID\r\n                    WHERE DeliveryApp_Detalle_Combo.PRICE > 0 and Plataforma<>1");
					}
					BD.ConsultaModificar("ProductosCombos", "PrecioUni= 0", "PrecioUni is null");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 360;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE Proveedores ADD Banco varchar(30)");
				BD.ConsultWithOutAlerts("ALTER TABLE Proveedores ADD TitularCuenta varchar(50)");
				BD.ConsultWithOutAlerts("ALTER TABLE Proveedores ADD NroCuenta varchar(20)");
				if (configuration.styleBolichesId.PizzaGrande == configuration.gStyleBoliches1)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Productos ALTER COLUMN Nombre varchar(100)");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 363;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS != 1)
				{
					BD.ConsultaModificar("Visitas", "DiaKey = CAST(CONVERT(VARCHAR, DiaKey, 1) + ' 8:00:00' AS DATETIME)", "2=2");
				}
				if (BD.ConsultWithOutAlerts("select top 1 NroDebitoCredito from FactElectConfiguracion") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD NroDebitoCredito int NULL");
					BD.ConsultaModificar("FactElectConfiguracion", "NroDebitoCredito= 0", "2=2");
					BD.ConsultWithOutAlerts("CREATE TABLE DebitoCredito(\t\t       DebitoCreditoID " + ((configuration.gMODO_ACCESS == 1) ? " AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tFechaEmision datetime NOT null,\t  \t\tNroNotaCreditoDebito integer NOT null,\t  \t\tCodigo varchar(80) null,  \t\tMontoDevuelto money null,  \t\tAnulada bit null,  \t\tFechaAnulacion dateTime null,\t  \t\tFacturaID integer NOT null,\t  \t\tEstadoSiat integer NOT null,\t  \t\tcufdID integer NOT null,  \t\tCodigoRecepcion varchar(50) null,  \t\tflagSync bit null,\t  \t\tPRIMARY KEY (DebitoCreditoID))");
					BD.ConsultWithOutAlerts("CREATE TABLE DebitoCreditoDetalle(\t\t          DebitoCreditoDetalleID " + ((configuration.gMODO_ACCESS == 1) ? "AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tCantidad FLOAT NOT null, \t\tDetalleCuentaID integer NOT null, \t\tflagSync bit null,\tDebitoCreditoID int not null,  \t\tPRIMARY KEY (DebitoCreditoDetalleID))");
				}
				if (BD.ConsultWithOutAlerts("select top 1 SectorCompraVenta from FactElectConfiguracion") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD SectorCompraVenta bit NULL");
					BD.ConsultaModificar("FactElectConfiguracion", "SectorCompraVenta= " + armarBolean(1), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD SectorCompraVentaBon bit NULL");
					BD.ConsultaModificar("FactElectConfiguracion", "SectorCompraVentaBon= " + armarBolean(0), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD SectorICE bit NULL");
					BD.ConsultaModificar("FactElectConfiguracion", "SectorICE= " + armarBolean(0), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD SectorTasaCero bit NULL");
					BD.ConsultaModificar("FactElectConfiguracion", "SectorTasaCero= " + armarBolean(0), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectConfiguracion ADD SectorNotaDebito bit NULL");
					BD.ConsultaModificar("FactElectConfiguracion", "SectorNotaDebito= " + armarBolean(0), "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD DocumentoSector int NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas2 ADD DocumentoSector int NULL");
					BD.ConsultaModificar("Facturas", "DocumentoSector=1 ", "2=2");
					BD.ConsultaModificar("Facturas2", "DocumentoSector=1 ", "2=2");
					BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD DocumentoSector int NULL");
					BD.ConsultaModificar("TiposProductos", "DocumentoSector=1 ", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 367;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CodigoRecepcion from DebitoCredito") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DebitoCredito ADD CodigoRecepcion varchar(50) NULL");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 369;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS == 1)
				{
					BD.ConsultWithOutAlerts("update Facturas INNER JOIN FactElectCUFD ON Right(Facturas.Codigo,15) = FactElectCUFD.codigoControl set facturas.cufdId= FactElectCUFD.FactElectCUFDID where Facturas.CufdID=0");
				}
				else
				{
					BD.ConsultWithOutAlerts("update Facturas set facturas.cufdId= FactElectCUFD.FactElectCUFDID from Facturas INNER JOIN FactElectCUFD ON Right(Facturas.Codigo,15) = FactElectCUFD.codigoControl where Facturas.CufdID=0");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 370;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS == 0)
				{
					DataTable dataTable9 = BD.ConsultaVer(" SELECT    distinct  tc.constraint_name\r\n                        FROM     information_schema.table_constraints AS tc    JOIN information_schema.key_column_usage AS kcu      ON tc.constraint_catalog = kcu.constraint_catalog\r\n                                  AND tc.constraint_schema = kcu.constraint_schema      AND tc.constraint_name = kcu.constraint_name\r\n                        WHERE     tc.constraint_type = 'UNIQUE'    AND tc.table_name = 'Facturas'");
					int num13 = dataTable9.Rows.Count - 1;
					for (int n = 0; n <= num13; n++)
					{
						BD.ConsultWithOutAlerts(Conversions.ToString(Operators.ConcatenateObject("ALTER TABLE Facturas DROP CONSTRAINT ", dataTable9.Rows[n][0])));
					}
					BD.ConsultWithOutAlerts("ALTER TABLE Facturas ADD CONSTRAINT IX_Facturas UNIQUE (NroFactura,CodigoID, CufdID) ");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 372;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 Subtotal from DebitoCreditoDetalle") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE DebitoCreditoDetalle ADD Subtotal FLOAT NULL");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 373;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Productos", "Costo=0,CostoBruto=0", "TienePreparacion=" + armarBolean(1));
				BD.ConsultaModificar("Productos", "Costo=0,CostoBruto=0", "esCombo=" + armarBolean(1));
				BD.ConsultaModificar("Facturas", "DocumentoSector=1 ", "DocumentoSector is null");
				BD.ConsultaModificar("Facturas", "DocumentoSector=1 ", "DocumentoSector =0");
				if (BD.ConsultWithOutAlerts("select top 1 Nombre from CredencialesQRupones") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE CredencialesQRupones(\t\t         ID " + ((configuration.gMODO_ACCESS == 1) ? "AUTOINCREMENT," : " integer IDENTITY(1,1) Not NULL,") + " \t\tNombre varchar(50) NOT null, \t\tClientSecret varchar(70) NOT null, \t\tClientUser varchar(50) NOT null, \t\tPassword1 varchar(50) NOT null, \t\tConfiguracionID integer NOT null, \t\tSucursalID int null, MarcasID varchar(10) null,  \t\tPRIMARY KEY (ID))");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 375;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Cuentas", "EsGiftCard= " + armarBolean(1), "nombre like '%gift%'");
				BD.ConsultaModificar("Cuentas", "MetodoPagoSIN=27", "esGiftCard = " + armarBolean(1));
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 380;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD CONSTRAINT FK_TiposProductos_Familias FOREIGN KEY(FamiliaId) REFERENCES Familias(FamiliaID)");
				BD.ConsultWithOutAlerts("ALTER TABLE TiposProductos ADD CONSTRAINT FK_TiposProductos_Almacen FOREIGN KEY(AlmacenID) REFERENCES Almacenes(AlmacenID)");
				BD.ConsultWithOutAlerts("DROP TABLE TiposProductosPY");
				BD.ConsultWithOutAlerts("CREATE INDEX PIndexFacturaVisitaID ON Facturas(VisitaID)");
				BD.ConsultWithOutAlerts("CREATE INDEX PIndexFacturaAnulada ON Facturas(Anulada)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 382;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				BD.ConsultaModificar("Cuentas", "Activa=" + armarBolean(0), "nombre like 'Cuenta QR'");
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Batos))
				{
					BD.ConsultaModificar("Cuentas", "nombre='Cuenta QR test'", "nombre like 'Cuenta QR'");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 386;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				actualizarMiWebServiceDll();
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 387;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 esManual from CobrosQR") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE CobrosQR ADD esManual bit NULL");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 393;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio)
				{
					BD.ConsultaModificar("TipoEnvios", "Activo=" + armarBolean(0), "Nombre like 'PEDIDOS YA'");
					BD.ConsultaModificar("TipoEnvios", "Activo=" + armarBolean(0), "Nombre like 'YAIGO'");
				}
				if (!(configuration.gSoloFacturacionGrande | configuration.gFormatoFacturaGrande | configuration.gSupermercado))
				{
					BD.ConsultaModificar("Productos", "UnidadSIN=62", "UnidadSIN=57");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 396;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 FacturaXWhatsapp from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD FacturaXWhatsapp bit NULL");
					if (configuration.gFormatoFacturaGrande)
					{
						BD.ConsultaModificar("Configuraciones", "FacturaXWhatsapp= " + armarBolean(1), "2=2");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "FacturaXWhatsapp= " + armarBolean(0), "2=2");
					}
				}
				if (BD.ConsultWithOutAlerts("select top 1 ProductoID from Forecast") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE Forecast(        ProductoID integer Not NULL, \t\tFecha Date NOT null, \t\tEstimado Float NOT null, \t\tPRIMARY KEY (ProductoID,Fecha))");
				}
				BD.ConsultaModificar("Productos", "esCombo = " + armarBolean(0), "esCombo =" + armarBolean(1) + " and id not in (select ParaProductoID from Preparaciones)");
				BD.ConsultaModificar("Productos", "TienePreparacion = " + armarBolean(0), "TienePreparacion =" + armarBolean(1) + " and id not in (select ParaProductoID from Preparaciones)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 397;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CuentaFormatoFactura from Configuraciones") == 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ADD CuentaFormatoFactura int NULL");
					BD.ConsultaModificar("Configuraciones", "CuentaFormatoFactura= 1", "2=2");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 400;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (new ctlConfiguraciones().devolverCuentaFormatoFactura1() != 0)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ALTER COLUMN CuentaFormatoFactura int");
					BD.ConsultaModificar("Configuraciones", "CuentaFormatoFactura=2", "2=2");
				}
				else
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ALTER COLUMN CuentaFormatoFactura int");
					if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.JardinPollos) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PollosBatman) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio))
					{
						BD.ConsultaModificar("Configuraciones", "CuentaFormatoFactura=0", "2=2");
					}
					else
					{
						BD.ConsultaModificar("Configuraciones", "CuentaFormatoFactura=1", "2=2");
					}
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 401;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select top 1 CredencialesId from CredencialesPagaTodo") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE CredencialesPagaTodo( CredencialesId integer IDENTITY(1, 1) Not NULL, EmpresaID integer NULL, Usuario varchar(50) NULL, Contrasena varchar(50) NULL, IncluirDelivery bit NULL)");
					BD.ConsultWithOutAlerts("CREATE TABLE CobrosPagaTodo( Id integer IDENTITY(1, 1) Not NULL, Fecha datetime NULL, Monto float NULL, Estado integer NULL, visitaID integer NULL , DeliveryID integer NULL, esManual bit NULL, flagSync bit null)");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 403;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				DataTable dataTable10 = BD.ConsultaVer("Nombre", "Productos", " esCombo=" + armarBolean(0) + " and TienePreparacion =" + armarBolean(0) + " and (select count(*) from Preparaciones where Preparaciones.ParaProductoID=id)>0\r\n                                                   and Borrado =" + armarBolean(0) + " and (CategoriaProduccionID is null or CategoriaProduccionID=0)");
				if (dataTable10.Rows.Count > 0)
				{
					string text4 = "";
					int num14 = dataTable10.Rows.Count - 1;
					for (int num15 = 0; num15 <= num14; num15++)
					{
						text4 = Conversions.ToString(Operators.ConcatenateObject(text4, Operators.ConcatenateObject(dataTable10.Rows[num15]["Nombre"], ", ")));
					}
					Interaction.MsgBox("Revisar estos productos, porque tiene preparaciones\r\npero no tienen marcado que usan recetas o son combos: \r\n" + text4);
				}
				BD.ConsultaModificar("Productos", "esCombo=" + armarBolean(0), "esCombo =" + armarBolean(1) + " and id not in (select ParaProductoID from Preparaciones)");
				BD.ConsultaModificar("Productos", "TienePreparacion=" + armarBolean(0), "TienePreparacion =" + armarBolean(1) + " and id not in (select ParaProductoID from Preparaciones)");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 404;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio)
				{
					BD.ConsultaModificar("TipoEnvios", "Activo=" + armarBolean(0), "Nombre like 'PEDIDOS YA'");
				}
				if (configuration.gTipoFacturacion == 2)
				{
					try
					{
						clsFactElecConfig clsFactElecConfig4 = new clsFactElecConfig();
						if (clsFactElecConfig4.devolverDatosSiTokenActivo1())
						{
							clsFacElecSyncDatos obj7 = new clsFacElecSyncDatos(unchecked((int)clsFactElecConfig4.CodigoAmbiente));
							obj7.SyncLeyes(esTest: false);
							obj7.SyncActividades(esTest: false);
							obj7.SyncUnidadMedida(esTest: false);
							obj7.SyncCodigoProductos(esTest: false);
						}
					}
					catch (Exception ex7)
					{
						ProjectData.SetProjectError(ex7);
						Exception ex8 = ex7;
						ProjectData.ClearProjectError();
					}
				}
				if (configuration.gMODO_ACCESS == 1)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectUnidadesMedidas ALTER COLUMN Codigo INT NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectActividades ALTER COLUMN Codigo varchar(10) NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectActividades ALTER COLUMN ConfiguracionID INT NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ALTER COLUMN ConfiguracionID INT NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ALTER COLUMN CodigoActividad varchar(10) NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ALTER COLUMN Codigo varchar(10) NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectUnidadesMedidas ADD CONSTRAINT PK_FactElectUnidadesMedidas PRIMARY KEY (Codigo)");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ADD CONSTRAINT PK_FactElectProductosServicios PRIMARY KEY (ConfiguracionID,CodigoActividad,Codigo)");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectActividades ADD CONSTRAINT PK_FactElectActividades PRIMARY KEY (ConfiguracionID,Codigo)");
				}
				else
				{
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectUnidadesMedidas ALTER COLUMN Codigo INT NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectActividades ALTER COLUMN Codigo varchar(10) NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectActividades ALTER COLUMN ConfiguracionID INT NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ALTER COLUMN ConfiguracionID INT NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ALTER COLUMN CodigoActividad varchar(10) NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ALTER COLUMN Codigo varchar(10) NOT NULL");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectUnidadesMedidas ADD PRIMARY KEY (Codigo)");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectProductosServicios ADD PRIMARY KEY (ConfiguracionID,CodigoActividad,Codigo)");
					BD.ConsultWithOutAlerts("ALTER TABLE FactElectActividades ADD PRIMARY KEY (ConfiguracionID,Codigo)");
				}
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 406;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (configuration.gMODO_ACCESS != 1)
				{
					BD.ConsultWithOutAlerts("ALTER TABLE Configuraciones ALTER COLUMN Email varchar(500) NOT NULL");
				}
				if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaRio) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.PizzaGrande))
				{
					updateSync();
				}
				updateActualizador();
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			num = 411;
			if (gBdVersion < num)
			{
				gBdVersion = num;
				if (BD.ConsultWithOutAlerts("select id from CredencialesTapeke") == 0)
				{
					BD.ConsultWithOutAlerts("CREATE TABLE CredencialesTapeke( ID integer IDENTITY(1, 1) Not NULL, keyRestaurante varchar(50) NULL, apiKey varchar(50) NULL)");
				}
				BD.ConsultaModificar("Cuentas", "EsGiftCard= " + armarBolean(1), "MetodoPagoSIN=27");
				BD.ConsultaModificar("Configuraciones", "AppVersion= " + Conversions.ToString(gBdVersion), "2=2");
			}
			deleteOldZip1(num - 1);
			deleteOldZip1(num - 2);
			if (configuration.gTipoFacturacion == 2)
			{
				try
				{
					if (new ctlImpresoras().DevolverImprimirFacturaFisico().Length > 0)
					{
						DataTable dataTable11 = BD.ConsultaVer("select nombre from Productos where ActividadSIN not in (select Codigo  from FactElectActividades) and Borrado =" + armarBolean(0));
						string text5 = "";
						int num16 = dataTable11.Rows.Count - 1;
						for (int num17 = 0; num17 <= num16; num17++)
						{
							text5 = Conversions.ToString(Operators.ConcatenateObject(text5, Operators.ConcatenateObject(dataTable11.Rows[num17][0], ", ")));
						}
						if (text5.Length > 0)
						{
							Interaction.MsgBox("URGENTEMENTE Avisar al admin. Revisar la actividad SIN de estos productos  \r\n" + text5);
						}
					}
				}
				catch (Exception ex9)
				{
					ProjectData.SetProjectError(ex9);
					Exception ex10 = ex9;
					Interaction.MsgBox(ex10.Message);
					ProjectData.ClearProjectError();
				}
				BD.ConsultaModificar("CodigosFacturas", "Activo=" + armarBolean(0), "Activo is null");
				BD.ConsultaModificar("Productos", "manejaSerie=" + armarBolean(0), "manejaSerie is null");
				BD.ConsultaModificar("Productos", "manejaImei=" + armarBolean(0), "manejaImei is null");
				if (BD.ConsultWithOutAlerts("select FactElectCUFDID from FactElectCUFD") == 0)
				{
					Interaction.MsgBox("Pida a su admin cambiar el Id de la tabla FactElectCUFD, el sistema se cerrara");
					Application.Exit();
				}
			}
			if (gBdVersion > num)
			{
				Interaction.MsgBox("Esta version " + Conversions.ToString(MyVersionProgram) + " del programa esta desactualizada, deberia tener la " + Conversions.ToString(gBdVersion) + ". Contacte a su administrador.");
				return false;
			}
			return true;
		}
	}

	public static bool deleteOldZip1(int UltimaVersionCodigo)
	{
		if (File.Exists("restotech" + Conversions.ToString(UltimaVersionCodigo) + ".zip"))
		{
			File.Delete("restotech" + Conversions.ToString(UltimaVersionCodigo) + ".zip");
			return true;
		}
		return false;
	}
}
