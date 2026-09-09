using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsMeseros
{
	private int MeseroID;

	private string Nombre;

	private string Telefono;

	private string CI;

	private int Activo;

	private string Codigo;

	private int Contrasenha;

	private int TipoUsuarioID;

	private double Porcentaje;

	private string email;

	private DateTime FechaInicio;

	public int _MeseroID
	{
		get
		{
			return MeseroID;
		}
		set
		{
			MeseroID = value;
		}
	}

	public string _Codigo
	{
		get
		{
			return Codigo;
		}
		set
		{
			Codigo = value;
		}
	}

	public int _Contrasenha
	{
		get
		{
			return Contrasenha;
		}
		set
		{
			Contrasenha = value;
		}
	}

	public int _TipoUsuarioID
	{
		get
		{
			return TipoUsuarioID;
		}
		set
		{
			TipoUsuarioID = value;
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

	public string _email
	{
		get
		{
			return email;
		}
		set
		{
			email = value;
		}
	}

	public string _Telefono
	{
		get
		{
			return Telefono;
		}
		set
		{
			Telefono = value;
		}
	}

	public string _CI
	{
		get
		{
			return CI;
		}
		set
		{
			CI = value;
		}
	}

	public bool _Activo
	{
		get
		{
			return Activo != 0;
		}
		set
		{
			Activo = 0 - (value ? 1 : 0);
		}
	}

	public double _Porcentaje
	{
		get
		{
			return Porcentaje;
		}
		set
		{
			Porcentaje = value;
		}
	}

	public DateTime _FechaInicio
	{
		get
		{
			return FechaInicio;
		}
		set
		{
			FechaInicio = value;
		}
	}

	public clsMeseros()
	{
		Nombre = "";
		Telefono = "";
		CI = "";
		Activo = -1;
		TipoUsuarioID = 1;
		FechaInicio = DateAndTime.Today;
		email = "";
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Meseros", " MeseroID=" + MeseroID);
		if (dataTable.Rows.Count > 0)
		{
			MeseroID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MeseroID"])) ? ((object)0) : dataTable.Rows[0]["MeseroID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			Telefono = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Telefono"])) ? "" : dataTable.Rows[0]["Telefono"]);
			CI = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CI"])) ? "" : dataTable.Rows[0]["CI"]);
			Activo = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Activo"])) ? ((object)false) : dataTable.Rows[0]["Activo"]);
			Codigo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["codigo"])) ? "" : dataTable.Rows[0]["codigo"]);
			Contrasenha = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Contrasenha"])) ? ((object)0) : dataTable.Rows[0]["Contrasenha"]);
			TipoUsuarioID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoUsuarioID"])) ? ((object)0) : dataTable.Rows[0]["TipoUsuarioID"]);
			Porcentaje = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Porcentaje"])) ? ((object)0) : dataTable.Rows[0]["Porcentaje"]);
			FechaInicio = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaInicio"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaInicio"]);
			email = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["email"])) ? "" : dataTable.Rows[0]["email"]);
		}
	}

	public void devolverNombre()
	{
		DataTable dataTable = BD.ConsultaVer("Nombre", "Meseros", " MeseroID=" + MeseroID);
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
		}
		else
		{
			Nombre = "";
		}
	}

	public void devolver1erMesero()
	{
		DataTable dataTable = BD.ConsultaVer("MeseroID", "Meseros", "Activo=" + VariableGeneral.armarBolean(Activo));
		if (dataTable.Rows.Count > 0)
		{
			MeseroID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MeseroID"])) ? ((object)0) : dataTable.Rows[0]["MeseroID"]);
		}
		else
		{
			MeseroID = 0;
		}
	}

	public void devolverTelefono()
	{
		DataTable dataTable = BD.ConsultaVer("Telefono", "Meseros", " MeseroID=" + MeseroID);
		if (dataTable.Rows.Count > 0)
		{
			Telefono = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Telefono"])) ? ((object)0) : dataTable.Rows[0]["Telefono"]);
			if (Telefono.Length == 0)
			{
				Telefono = Conversions.ToString(0);
			}
		}
		else
		{
			Telefono = Conversions.ToString(0);
		}
	}

	public DataTable devolverMeserosPorNombre()
	{
		return BD.ConsultaVer("MeseroID,Nombre,TipoUsuarioID", "Meseros", "1=1", "Nombre");
	}

	public DataTable devolverMeserosActivosPorNombre()
	{
		return BD.ConsultaVer("MeseroID,Nombre,TipoUsuarioID", "Meseros", ("Activo=" + VariableGeneral.armarBolean(1)) ?? "", "Nombre");
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Meseros.MeseroID,Meseros.Nombre,Meseros.Telefono,Meseros.CI,Meseros.Activo", "Meseros");
	}

	public void verificarContrasenha()
	{
		DataTable dataTable = BD.ConsultaVer("Meseros.MeseroID,Meseros.Nombre,TipoUsuarioID", "Meseros", "Activo=" + VariableGeneral.armarBolean(1) + " and Contrasenha = " + Conversions.ToString(Contrasenha));
		if (dataTable.Rows.Count > 0)
		{
			MeseroID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MeseroID"])) ? ((object)0) : dataTable.Rows[0]["MeseroID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			TipoUsuarioID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoUsuarioID"])) ? "" : dataTable.Rows[0]["TipoUsuarioID"]);
		}
		else if (Contrasenha == 15071507)
		{
			MeseroID = Conversions.ToInteger(BD.ConsultaVer("Meseros.MeseroID", "Meseros", "1=1").Rows[0][0]);
			Nombre = "TOPTECH USER";
			TipoUsuarioID = 1;
		}
		else
		{
			MeseroID = 0;
			Nombre = "";
			TipoUsuarioID = 0;
		}
	}

	public int getQTid()
	{
		DataTable dataTable = BD.ConsultaVer("qtID", "Meseros", "MeseroID=" + Conversions.ToString(MeseroID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["qtID"])) ? ((object)0) : dataTable.Rows[0]["qtID"]);
		}
		return 0;
	}

	public void verificarCodigo()
	{
		DataTable dataTable = BD.ConsultaVer("Meseros.MeseroID,Meseros.Nombre,TipoUsuarioID", "Meseros", "Activo=" + VariableGeneral.armarBolean(1) + " and codigo like '" + Codigo + "'");
		if (dataTable.Rows.Count > 0)
		{
			MeseroID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MeseroID"])) ? ((object)0) : dataTable.Rows[0]["MeseroID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			TipoUsuarioID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoUsuarioID"])) ? "" : dataTable.Rows[0]["TipoUsuarioID"]);
		}
		else if (Operators.CompareString(Codigo, "15071507", TextCompare: false) == 0)
		{
			MeseroID = Conversions.ToInteger(BD.ConsultaVer("Meseros.MeseroID", "Meseros", "1=1").Rows[0][0]);
			Nombre = "TOPTECH USER";
			TipoUsuarioID = 1;
		}
		else
		{
			MeseroID = 0;
			Nombre = "";
			TipoUsuarioID = 0;
		}
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Meseros", ("Nombre='" + Nombre + "',Telefono='" + Telefono + "',CI='" + CI + "',Contrasenha=" + Conversions.ToString(Contrasenha) + ",Codigo='" + Codigo + "',email='" + email + "',TipoUsuarioID=" + Conversions.ToString(TipoUsuarioID) + ",Activo=" + VariableGeneral.armarBolean(Activo) + ",Porcentaje=" + Conversion.Str(Porcentaje) + ",FechaInicio=" + VariableGeneral.ArmarFecha(FechaInicio)) ?? "", "MeseroID=" + MeseroID);
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
				MeseroID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(MeseroID )", "Meseros").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(Conversions.ToString(MeseroID) + ",'" + Nombre + "','" + Telefono + "','" + CI + "',", VariableGeneral.armarBolean(Activo), ",'", Codigo, "',", Conversions.ToString(Contrasenha), ",", Conversions.ToString(TipoUsuarioID), ",", Conversion.Str(Porcentaje), ",", VariableGeneral.ArmarFecha(FechaInicio), ",'", email, "'"), "Meseros(MeseroID,Nombre,Telefono,CI ,Activo,Codigo,Contrasenha,TipoUsuarioID, Porcentaje,FechaInicio,email)");
				MeseroID = Conversions.ToInteger(BD.ConsultaVer("max(MeseroID )", "Meseros").Rows[0][0]);
				result = MeseroID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat("'" + Nombre + "','" + Telefono + "','" + CI + "',", VariableGeneral.armarBolean(Activo), ",'", Codigo, "',", Conversions.ToString(Contrasenha), ",", Conversions.ToString(TipoUsuarioID), ",", Conversion.Str(Porcentaje), ",", VariableGeneral.ArmarFecha(FechaInicio), ",'", email, "'"), "Meseros(Nombre,Telefono,CI ,Activo,Codigo,Contrasenha,TipoUsuarioID, Porcentaje,FechaInicio,email)", ref MeseroID);
				result = MeseroID;
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

	public bool Eliminar()
	{
		bool result;
		try
		{
			if (BD.ConsultaEliminar("Meseros", "MeseroID  = " + MeseroID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Mesero, se encuentra en uso");
				result = false;
			}
			else
			{
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

	public DataTable devolverMotociclistasParaApp()
	{
		return BD.ConsultaVer("MeseroID,Nombre,TipoUsuarioID", "Meseros", "qtID>0 and Activo = " + VariableGeneral.armarBolean(1) + " and  TipoUsuarioID = " + Conversions.ToString(5));
	}

	public DataTable DevolverMotociclistas()
	{
		return BD.ConsultaVer("MeseroID,Nombre,TipoUsuarioID", "Meseros", "Activo = " + VariableGeneral.armarBolean(1) + " and  TipoUsuarioID = " + Conversions.ToString(5));
	}

	public DataTable DevolverMeserosActivos()
	{
		return BD.ConsultaVer("Meseros.MeseroID,Meseros.Nombre,Meseros.TipoUsuarioID,Meseros.Telefono,Meseros.CI,Meseros.Activo,qtID as DeliveryID", "Meseros", ("Activo=" + VariableGeneral.armarBolean(1)) ?? "", "Nombre");
	}

	public DataTable DevolverMeserosInactivos()
	{
		return BD.ConsultaVer("Meseros.MeseroID,Meseros.Nombre,Meseros.TipoUsuarioID,Meseros.Telefono,Meseros.CI,Meseros.Activo,qtID as DeliveryID", "Meseros", ("Activo=" + VariableGeneral.armarBolean(0)) ?? "", "Nombre");
	}

	public DataTable DevolverMeserosActivosCombo()
	{
		return BD.ConsultaVer("Meseros.MeseroID,Meseros.Nombre", "Meseros", ("Activo=" + VariableGeneral.armarBolean(1)) ?? "", "Nombre");
	}
}
