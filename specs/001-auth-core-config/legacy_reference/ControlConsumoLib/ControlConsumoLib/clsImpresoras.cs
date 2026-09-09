using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsImpresoras
{
	private int ImpresoraID;

	private string Nombre;

	private string NombreFisico;

	private bool ImprimirCuenta;

	private bool ImprimirFactura;

	private bool AbrirCaja;

	private bool esMonitorDigital;

	private int ConfiguracionFacturaID;

	public int _ImpresoraID
	{
		get
		{
			return ImpresoraID;
		}
		set
		{
			ImpresoraID = value;
		}
	}

	public string _Nombre
	{
		get
		{
			return Nombre;
		}
		set
		{
			Nombre = value;
		}
	}

	public string _NombreFisico
	{
		get
		{
			return NombreFisico;
		}
		set
		{
			NombreFisico = value;
		}
	}

	public bool _esMonitorDigital
	{
		get
		{
			return esMonitorDigital;
		}
		set
		{
			esMonitorDigital = value;
		}
	}

	public bool _ImprimirCuenta
	{
		get
		{
			return ImprimirCuenta;
		}
		set
		{
			ImprimirCuenta = value;
		}
	}

	public bool _ImprimirFactura
	{
		get
		{
			return ImprimirFactura;
		}
		set
		{
			ImprimirFactura = value;
		}
	}

	public bool _AbrirCaja
	{
		get
		{
			return AbrirCaja;
		}
		set
		{
			AbrirCaja = value;
		}
	}

	public int _ConfiguracionFacturaID
	{
		get
		{
			return ConfiguracionFacturaID;
		}
		set
		{
			ConfiguracionFacturaID = value;
		}
	}

	public clsImpresoras()
	{
		Nombre = "";
		NombreFisico = "";
		ImprimirCuenta = false;
		ImprimirFactura = false;
		AbrirCaja = false;
		ConfiguracionFacturaID = 0;
		esMonitorDigital = false;
	}

	public void llenarclase1()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Impresoras", " ImpresoraID=" + ImpresoraID);
		if (dataTable.Rows.Count > 0)
		{
			ImpresoraID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImpresoraID"])) ? ((object)0) : dataTable.Rows[0]["ImpresoraID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			NombreFisico = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? ((object)false) : dataTable.Rows[0]["NombreFisico"]);
			ImprimirCuenta = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImprimirCuenta"])) ? ((object)false) : dataTable.Rows[0]["ImprimirCuenta"]);
			ImprimirFactura = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImprimirFactura"])) ? ((object)false) : dataTable.Rows[0]["ImprimirFactura"]);
			AbrirCaja = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AbrirCaja"])) ? ((object)false) : dataTable.Rows[0]["AbrirCaja"]);
			esMonitorDigital = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["esMonitorDigital"])) ? ((object)false) : dataTable.Rows[0]["esMonitorDigital"]);
			ConfiguracionFacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ConfiguracionFacturaID"])) ? ((object)0) : dataTable.Rows[0]["ConfiguracionFacturaID"]);
		}
	}

	public void devolverNombreFisicoPorID()
	{
		DataTable dataTable = BD.ConsultaVer("Impresoras.NombreFisico", "Impresoras", " ImpresoraID=" + ImpresoraID);
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? "" : dataTable.Rows[0]["NombreFisico"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			NombreFisico = ((text == null) ? "" : text);
		}
		else
		{
			NombreFisico = "";
		}
	}

	public void devolverNombreFisicoPorNombre()
	{
		DataTable dataTable = BD.ConsultaVer("Impresoras.NombreFisico", "Impresoras", "Nombre like '" + Nombre + "' and esMonitorDigital=" + VariableGeneral.armarBolean(0));
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? "" : dataTable.Rows[0]["NombreFisico"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			NombreFisico = ((text == null) ? "" : text);
		}
		else
		{
			NombreFisico = "";
		}
	}

	public void DevolverAbrirCaja()
	{
		DataTable dataTable = BD.ConsultaVer("Impresoras.NombreFisico", "Impresoras", "AbrirCaja = " + VariableGeneral.armarBolean(aux: true));
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? "" : dataTable.Rows[0]["NombreFisico"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			NombreFisico = ((text == null) ? "" : text);
		}
		else
		{
			NombreFisico = "";
		}
	}

	public void DevolverImprimirCuenta()
	{
		DataTable dataTable = BD.ConsultaVer("Impresoras.NombreFisico,ImpresoraID", "Impresoras", "ImprimirCuenta = " + VariableGeneral.armarBolean(aux: true));
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? "" : dataTable.Rows[0]["NombreFisico"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			NombreFisico = ((text == null) ? "" : text);
			ImpresoraID = Conversions.ToInteger(dataTable.Rows[0]["ImpresoraID"]);
		}
		else
		{
			NombreFisico = "";
		}
	}

	public void DevolverImprimirFacturaFisico()
	{
		DataTable dataTable = BD.ConsultaVer("Impresoras.NombreFisico,ImpresoraID", "Impresoras", "ImprimirFactura= " + VariableGeneral.armarBolean(aux: true) + " and ConfiguracionFacturaID=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? "" : dataTable.Rows[0]["NombreFisico"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			NombreFisico = ((text == null) ? "" : text);
			ImpresoraID = Conversions.ToInteger(dataTable.Rows[0]["ImpresoraID"]);
		}
		else
		{
			NombreFisico = "";
		}
	}

	public void DevolverImprimirFacturas()
	{
		DataTable dataTable = BD.ConsultaVer("Impresoras.Nombre,ImpresoraID", "Impresoras", "ImprimirFactura= " + VariableGeneral.armarBolean(aux: true));
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(dataTable.Rows[0]["Nombre"]);
			ImpresoraID = Conversions.ToInteger(dataTable.Rows[0]["ImpresoraID"]);
		}
		else
		{
			Nombre = "";
		}
	}

	public bool soyKDS()
	{
		bool result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("count(*)", "Impresoras", "esMonitorDigital= " + VariableGeneral.armarBolean(aux: true) + " and NombreFisico like '" + MyProject.Computer.Name + "'");
			result = dataTable.Rows.Count != 0 && (Operators.ConditionalCompareObjectGreater(dataTable.Rows[0][0], 0, TextCompare: false) ? true : false);
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

	public DataTable devolverImpresorasUsadasEnVisita(int visitaID)
	{
		return BD.ConsultaVer("distinct Impresoras.Nombre, Impresoras.NombreFisico", "((DetalleCuenta INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID) INNER JOIN TiposProductos ON Productos.TipoProductoID = TiposProductos.TipoProductoID) INNER JOIN Impresoras ON TiposProductos.ImpresoraID = Impresoras.ImpresoraID", "DetalleCuenta.VisitaID = " + Conversions.ToString(visitaID));
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Impresoras.ImpresoraID,Impresoras.Nombre,Impresoras.NombreFisico, ImprimirCuenta,ImprimirFactura,AbrirCaja", "Impresoras");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select Impresoras.ImpresoraID,Impresoras.Nombre,Impresoras.NombreFisico,ImprimirCuenta,ImprimirFactura,AbrirCaja", "Impresoras) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public DataTable devolverImpresorasPorDescripcion()
	{
		return BD.ConsultaVer("ImpresoraID,Nombre", "Impresoras");
	}

	public DataTable devolverImpresoraskitchenPorDescripcion()
	{
		return BD.ConsultaVer("ImpresoraID,Nombre", "Impresoras", "esMonitorDigital=" + VariableGeneral.armarBolean(1));
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Impresoras", "Nombre='" + Nombre + "',NombreFisico='" + NombreFisico + "',ImprimirCuenta=" + VariableGeneral.armarBolean(ImprimirCuenta) + ",ImprimirFactura=" + VariableGeneral.armarBolean(ImprimirFactura) + ",AbrirCaja=" + VariableGeneral.armarBolean(AbrirCaja) + ",ConfiguracionFacturaID=" + Conversions.ToString(ConfiguracionFacturaID) + ",esMonitorDigital=" + VariableGeneral.armarBolean(esMonitorDigital), "ImpresoraID=" + ImpresoraID);
			result = 1;
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

	public int Insertar()
	{
		int result;
		try
		{
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				ImpresoraID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ImpresoraID)", "Impresoras").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(Conversions.ToString(ImpresoraID) + ",'" + Nombre + "','" + NombreFisico + "'," + VariableGeneral.armarBolean(ImprimirCuenta) + "," + VariableGeneral.armarBolean(ImprimirFactura) + "," + VariableGeneral.armarBolean(AbrirCaja) + "," + Conversions.ToString(ConfiguracionFacturaID) + "," + VariableGeneral.armarBolean(esMonitorDigital), "Impresoras(ImpresoraID,Nombre,NombreFisico,ImprimirCuenta,ImprimirFactura,AbrirCaja,ConfiguracionFacturaID,esMonitorDigital)");
				ImpresoraID = Conversions.ToInteger(BD.ConsultaVer("max(ImpresoraID)", "Impresoras").Rows[0][0]);
				result = ImpresoraID;
			}
			else
			{
				BD.ConsultaInsertar3("'" + Nombre + "','" + NombreFisico + "'," + VariableGeneral.armarBolean(ImprimirCuenta) + "," + VariableGeneral.armarBolean(ImprimirFactura) + "," + VariableGeneral.armarBolean(AbrirCaja) + "," + Conversions.ToString(ConfiguracionFacturaID) + "," + VariableGeneral.armarBolean(esMonitorDigital), "Impresoras(Nombre,NombreFisico,ImprimirCuenta,ImprimirFactura,AbrirCaja,ConfiguracionFacturaID,esMonitorDigital)", ref ImpresoraID);
				result = ImpresoraID;
			}
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

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Impresoras", "ImpresoraID = " + ImpresoraID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Impresoras, se encuentra en uso");
				result = 0;
			}
			else
			{
				result = 1;
			}
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

	public void DevolverImpresora()
	{
		DataTable dataTable = BD.ConsultaVer("top 1 Impresoras.NombreFisico,ImpresoraID", "Impresoras", "esMonitorDigital=" + VariableGeneral.armarBolean(0));
		if (dataTable.Rows.Count > 0)
		{
			NombreFisico = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? "" : dataTable.Rows[0]["NombreFisico"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			ImpresoraID = Conversions.ToInteger(dataTable.Rows[0]["ImpresoraID"]);
		}
		else
		{
			NombreFisico = "";
		}
	}

	public void DevolverImprimirCierreKiky()
	{
		DataTable dataTable = BD.ConsultaVer("Impresoras.NombreFisico", "Impresoras", "Nombre like 'Cierre%' ");
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFisico"])) ? "" : dataTable.Rows[0]["NombreFisico"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			NombreFisico = ((text == null) ? "" : text);
			string[] array = NombreFisico.Split(';');
			if (array.Length > 0)
			{
				NombreFisico = array[0];
			}
		}
		else
		{
			NombreFisico = "";
		}
	}
}
