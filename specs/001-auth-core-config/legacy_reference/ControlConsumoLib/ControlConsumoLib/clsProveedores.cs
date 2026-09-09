using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsProveedores
{
	private int ProveedorID;

	private string NombreEmpresa;

	private string NombreContacto;

	private string Nit;

	private int TelefonoDomicilio;

	private int TelefonoOficina;

	private int Celular;

	private string Email;

	private string Direccion;

	private string Observaciones;

	private int DiasCredito;

	private string Banco;

	private string TitularCuenta;

	private string NroCuenta;

	public int _ProveedorID
	{
		get
		{
			return ProveedorID;
		}
		set
		{
			ProveedorID = value;
		}
	}

	public string _NombreEmpresa
	{
		get
		{
			return NombreEmpresa;
		}
		set
		{
			NombreEmpresa = value;
		}
	}

	public string _NombreContacto
	{
		get
		{
			return NombreContacto;
		}
		set
		{
			NombreContacto = value;
		}
	}

	public string _Nit
	{
		get
		{
			return Nit;
		}
		set
		{
			Nit = value;
		}
	}

	public int _TelefonoDomicilio
	{
		get
		{
			return TelefonoDomicilio;
		}
		set
		{
			TelefonoDomicilio = value;
		}
	}

	public int _TelefonoOficina
	{
		get
		{
			return TelefonoOficina;
		}
		set
		{
			TelefonoOficina = value;
		}
	}

	public int _Celular
	{
		get
		{
			return Celular;
		}
		set
		{
			Celular = value;
		}
	}

	public string _Email
	{
		get
		{
			return Email;
		}
		set
		{
			Email = value;
		}
	}

	public string _Direccion
	{
		get
		{
			return Direccion;
		}
		set
		{
			Direccion = value;
		}
	}

	public string _Observaciones
	{
		get
		{
			return Observaciones;
		}
		set
		{
			Observaciones = value;
		}
	}

	public int _DiasCredito
	{
		get
		{
			return DiasCredito;
		}
		set
		{
			DiasCredito = value;
		}
	}

	public string _Banco
	{
		get
		{
			return Banco;
		}
		set
		{
			Banco = value;
		}
	}

	public string _TitularCuenta
	{
		get
		{
			return TitularCuenta;
		}
		set
		{
			TitularCuenta = value;
		}
	}

	public string _NroCuenta
	{
		get
		{
			return NroCuenta;
		}
		set
		{
			NroCuenta = value;
		}
	}

	public DataTable devolverProveedoresPorNombreEmpresa()
	{
		return BD.ConsultaVer("ProveedorID,NombreEmpresa,DiasCredito", "Proveedores", "", "NombreEmpresa");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select Proveedores.ProveedorID,Proveedores.NombreEmpresa,Proveedores.NombreContacto,Proveedores.Nit,Proveedores.TelefonoDomicilio,Proveedores.TelefonoOficina,Proveedores.Celular,Proveedores.Email,Proveedores.Direccion,Proveedores.Observaciones", "Proveedores) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Proveedores.ProveedorID,Proveedores.NombreEmpresa,Proveedores.NombreContacto,Proveedores.Nit,Proveedores.TelefonoDomicilio,Proveedores.TelefonoOficina,Proveedores.Celular,Proveedores.Email,Proveedores.Direccion,Proveedores.Observaciones,Proveedores.DiasCredito,Banco,TitularCuenta,NroCuenta", "Proveedores");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Proveedores", "NombreEmpresa='" + NombreEmpresa + "',NombreContacto='" + NombreContacto + "',Nit='" + Nit + "',TelefonoDomicilio=" + TelefonoDomicilio + ",TelefonoOficina=" + TelefonoOficina + ",Celular=" + Celular + ",Email='" + Email + "',Direccion='" + Direccion + "',Observaciones='" + Observaciones + "',DiasCredito=" + DiasCredito + ",Banco='" + Banco + "',TitularCuenta='" + TitularCuenta + "',NroCuenta='" + NroCuenta + "'", "ProveedorID=" + ProveedorID);
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
				ProveedorID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ProveedorID)", "Proveedores").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(ProveedorID) + ",'" + NombreEmpresa + "','" + NombreContacto + "','" + Nit + "',", TelefonoDomicilio.ToString(), ","), TelefonoOficina.ToString(), ","), Celular.ToString(), ",'", Email, "','", Direccion, "','", Observaciones, "',"), DiasCredito.ToString(), ",'", Banco, "','", TitularCuenta, "','", NroCuenta, "'"), "Proveedores(ProveedorID,NombreEmpresa,NombreContacto,Nit ,TelefonoDomicilio,TelefonoOficina ,Celular ,Email,Direccion,Observaciones,DiasCredito,Banco,TitularCuenta,NroCuenta)");
				ProveedorID = Conversions.ToInteger(BD.ConsultaVer("max(ProveedorID)", "Proveedores").Rows[0][0]);
				result = ProveedorID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat("'" + NombreEmpresa + "','" + NombreContacto + "','" + Nit + "',", TelefonoDomicilio.ToString(), ","), TelefonoOficina.ToString(), ","), Celular.ToString(), ",'", Email, "','", Direccion, "','", Observaciones, "',"), DiasCredito.ToString(), ",'", Banco, "','", TitularCuenta, "','", NroCuenta, "'"), "Proveedores(NombreEmpresa,NombreContacto,Nit ,TelefonoDomicilio,TelefonoOficina ,Celular ,Email,Direccion,Observaciones,DiasCredito,Banco,TitularCuenta,NroCuenta)", ref ProveedorID);
				result = ProveedorID;
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
			if (BD.ConsultaEliminar("Proveedores", "ProveedorID = " + ProveedorID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Proveedor, se encuentra en uso");
			}
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

	public void devolverProveedoresPorID()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Proveedores", "ProveedorID =" + ProveedorID);
		if (dataTable.Rows.Count > 0)
		{
			NombreEmpresa = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreEmpresa"])) ? "" : dataTable.Rows[0]["NombreEmpresa"]);
			Nit = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nit"])) ? ((object)0) : dataTable.Rows[0]["Nit"]);
		}
	}

	public DataTable BuscarProveedores(string id, string nombre)
	{
		string text = "";
		if (id.Length > 0)
		{
			text = text + "Proveedores.Nit like '%" + id + "%'";
		}
		if (nombre.Length > 0)
		{
			if (text.Length > 0)
			{
				text += " and ";
			}
			text = text + "Proveedores.NombreEmpresa like '%" + nombre + "%'";
		}
		if (text.Length == 0)
		{
			text += " 1=1 ";
		}
		return BD.ConsultaVer("distinct " + ((configuration.gMODO_ACCESS == 1) ? "(false)" : "cast(0 as bit)") + "  as Seleccionar, Proveedores.ProveedorID ,Proveedores.NombreEmpresa,Proveedores.Nit", "Proveedores ", text);
	}

	public void devolverProveedoresReporte()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Proveedores", "ProveedorID =" + ProveedorID);
		if (dataTable.Rows.Count > 0)
		{
			Direccion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Direccion"])) ? "" : dataTable.Rows[0]["Direccion"]);
			TelefonoDomicilio = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TelefonoDomicilio"])) ? ((object)0) : dataTable.Rows[0]["TelefonoDomicilio"]);
			TelefonoOficina = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TelefonoOficina"])) ? ((object)0) : dataTable.Rows[0]["TelefonoOficina"]);
			Celular = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Celular"])) ? ((object)0) : dataTable.Rows[0]["Celular"]);
		}
		else
		{
			Direccion = "";
			TelefonoDomicilio = 0;
			TelefonoOficina = 0;
			Celular = 0;
		}
	}

	public DataTable devolverDiasCredito()
	{
		return BD.ConsultaVer("DiasCredito", "Proveedores", "ProveedorID = " + ProveedorID);
	}

	public DataTable DevolverBancos()
	{
		return BD.ConsultaVer("distinct 0,  Banco", "Proveedores", "banco is not null");
	}
}
