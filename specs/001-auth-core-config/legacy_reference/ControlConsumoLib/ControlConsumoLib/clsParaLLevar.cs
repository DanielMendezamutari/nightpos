using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsParaLLevar
{
	private int ParaLlevarID;

	private string Nombre;

	private string NombreFactura;

	private string NIT;

	private DateTime HoraRecoger;

	private string Telefono;

	private string Direccion;

	private string notas;

	private int Motociclista;

	private double laty_num;

	private double lonx_num;

	public double MontoDelivery;

	public string MetodoPago;

	public string Email;

	public int _ParaLlevarID
	{
		get
		{
			return ParaLlevarID;
		}
		set
		{
			ParaLlevarID = value;
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

	public string _NombreFactura
	{
		get
		{
			return NombreFactura;
		}
		set
		{
			NombreFactura = value;
		}
	}

	public string _NIT
	{
		get
		{
			return NIT;
		}
		set
		{
			NIT = value;
		}
	}

	public DateTime _HoraRecoger
	{
		get
		{
			return HoraRecoger;
		}
		set
		{
			HoraRecoger = value;
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

	public string _MetodoPago
	{
		get
		{
			return MetodoPago;
		}
		set
		{
			MetodoPago = value;
		}
	}

	public string _notas
	{
		get
		{
			return notas;
		}
		set
		{
			notas = value;
		}
	}

	public int _Motociclista
	{
		get
		{
			return Motociclista;
		}
		set
		{
			Motociclista = value;
		}
	}

	public double _lonx_num
	{
		get
		{
			return lonx_num;
		}
		set
		{
			lonx_num = value;
		}
	}

	public double _laty_num
	{
		get
		{
			return laty_num;
		}
		set
		{
			laty_num = value;
		}
	}

	public string _email
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

	public clsParaLLevar()
	{
		Nombre = "";
		NombreFactura = "";
		NIT = "";
		HoraRecoger = DateAndTime.Now;
		Telefono = "";
		Direccion = "";
		notas = "";
		laty_num = 0.0;
		lonx_num = 0.0;
		MetodoPago = "";
		Email = "";
	}

	public void cargarPorNombre()
	{
		DataTable dataTable = BD.ConsultaVer("top 1 *", "ParaLLevar", " Nombre like '" + Nombre + "'", "ParaLlevarId Desc");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			NombreFactura = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFactura"])) ? "" : dataTable.Rows[0]["NombreFactura"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? "" : dataTable.Rows[0]["NIT"]);
			Telefono = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Telefono"])) ? "" : dataTable.Rows[0]["Telefono"]);
			Direccion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Direccion"])) ? "" : dataTable.Rows[0]["Direccion"]);
			Motociclista = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Motociclista"])) ? "" : dataTable.Rows[0]["Motociclista"]);
			notas = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["notas"])) ? "" : dataTable.Rows[0]["notas"]);
			laty_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["laty_num"])) ? "0" : dataTable.Rows[0]["laty_num"]);
			lonx_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["lonx_num"])) ? "0" : dataTable.Rows[0]["lonx_num"]);
			Email = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Email"])) ? "" : dataTable.Rows[0]["Email"]);
		}
	}

	public DataTable devolverDireccionesPorNombre()
	{
		return BD.ConsultaVer("distinct Direccion", "ParaLLevar", " Nombre like '" + Nombre + "'");
	}

	public void cargarPorNit()
	{
		DataTable dataTable = BD.ConsultaVer("top 1 *", "ParaLLevar", " Nit like '" + NIT + "'", "ParaLlevarId Desc");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			NombreFactura = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFactura"])) ? "" : dataTable.Rows[0]["NombreFactura"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? "" : dataTable.Rows[0]["NIT"]);
			Telefono = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Telefono"])) ? "" : dataTable.Rows[0]["Telefono"]);
			Direccion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Direccion"])) ? "" : dataTable.Rows[0]["Direccion"]);
			Motociclista = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Motociclista"])) ? "" : dataTable.Rows[0]["Motociclista"]);
			notas = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["notas"])) ? "" : dataTable.Rows[0]["notas"]);
			laty_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["laty_num"])) ? "0" : dataTable.Rows[0]["laty_num"]);
			lonx_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["lonx_num"])) ? "0" : dataTable.Rows[0]["lonx_num"]);
			Email = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Email"])) ? "" : dataTable.Rows[0]["Email"]);
		}
	}

	public DataTable cargarDetallePorNombre()
	{
		return BD.ConsultaVer(" top 50 Visitas.Fecha, sum(DetalleCuenta.Cantidad) as Cant, Productos.Nombre as Prod, DetalleCuenta.Comentarios", "((Visitas INNER JOIN ParaLLevar ON Visitas.ParaLlevarID = ParaLLevar.ParaLlevarID) INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID", " ParaLLevar.Nombre Like '" + Nombre + "' and Borrada=" + VariableGeneral.armarBolean(0), " Visitas.Fecha desc", "Visitas.Fecha, Productos.Nombre,DetalleCuenta.Comentarios");
	}

	public DataTable cargarDetallePorNIT()
	{
		return BD.ConsultaVer(" top 50 Visitas.Fecha,sum(DetalleCuenta.Cantidad) as Cant, Productos.Nombre as Prod, DetalleCuenta.Comentarios", "((Visitas INNER JOIN ParaLLevar ON Visitas.ParaLlevarID = ParaLLevar.ParaLlevarID) INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID) INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID", " Nit like '" + NIT + "' and Borrada=" + VariableGeneral.armarBolean(0), " Visitas.Fecha desc", "Visitas.Fecha, Productos.Nombre,DetalleCuenta.Comentarios");
	}

	public DataTable cargarDetallePorTelefono()
	{
		return BD.ConsultaVer(" top 50 Visitas.Fecha,sum(DetalleCuenta.Cantidad) as Cant, Productos.Nombre as Prod, DetalleCuenta.Comentarios", "((Visitas INNER JOIN ParaLLevar ON Visitas.ParaLlevarID = ParaLLevar.ParaLlevarID) INNER JOIN DetalleCuenta ON Visitas.ID = DetalleCuenta.VisitaID) INNER JOIN Productos ON DetalleCuenta.ProductoID = Productos.ID", " Telefono like '" + Telefono + "' and Borrada=" + VariableGeneral.armarBolean(0), " Visitas.Fecha desc", "Visitas.Fecha, Productos.Nombre,DetalleCuenta.Comentarios");
	}

	public void cargarPorTelefono()
	{
		DataTable dataTable = BD.ConsultaVer("top 1 *", "ParaLLevar", " Telefono like '" + Telefono + "'", "ParaLlevarId Desc");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			NombreFactura = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFactura"])) ? "" : dataTable.Rows[0]["NombreFactura"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? "" : dataTable.Rows[0]["NIT"]);
			Telefono = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Telefono"])) ? "" : dataTable.Rows[0]["Telefono"]);
			Direccion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Direccion"])) ? "" : dataTable.Rows[0]["Direccion"]);
			notas = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["notas"])) ? "" : dataTable.Rows[0]["notas"]);
			Motociclista = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Motociclista"])) ? "" : dataTable.Rows[0]["Motociclista"]);
			laty_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["laty_num"])) ? ((object)0) : dataTable.Rows[0]["laty_num"]);
			lonx_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["lonx_num"])) ? ((object)0) : dataTable.Rows[0]["lonx_num"]);
			MontoDelivery = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoDelivery"])) ? ((object)0) : dataTable.Rows[0]["MontoDelivery"]);
			MetodoPago = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MetodoPago"])) ? "" : dataTable.Rows[0]["MetodoPago"]);
			laty_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["laty_num"])) ? "0" : dataTable.Rows[0]["laty_num"]);
			lonx_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["lonx_num"])) ? "0" : dataTable.Rows[0]["lonx_num"]);
			Email = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Email"])) ? "" : dataTable.Rows[0]["Email"]);
		}
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "ParaLLevar", " ParaLlevarID=" + ParaLlevarID);
		if (dataTable.Rows.Count > 0)
		{
			ParaLlevarID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ParaLlevarID"])) ? ((object)0) : dataTable.Rows[0]["ParaLlevarID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			NombreFactura = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFactura"])) ? "" : dataTable.Rows[0]["NombreFactura"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? "" : dataTable.Rows[0]["NIT"]);
			HoraRecoger = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["HoraRecoger"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["HoraRecoger"]);
			Telefono = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Telefono"])) ? "" : dataTable.Rows[0]["Telefono"]);
			Direccion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Direccion"])) ? "" : dataTable.Rows[0]["Direccion"]);
			notas = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["notas"])) ? "" : dataTable.Rows[0]["notas"]);
			Motociclista = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Motociclista"])) ? ((object)0) : dataTable.Rows[0]["Motociclista"]);
			laty_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["laty_num"])) ? ((object)0) : dataTable.Rows[0]["laty_num"]);
			lonx_num = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["lonx_num"])) ? ((object)0) : dataTable.Rows[0]["lonx_num"]);
			MontoDelivery = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoDelivery"])) ? ((object)0) : dataTable.Rows[0]["MontoDelivery"]);
			MetodoPago = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MetodoPago"])) ? "" : dataTable.Rows[0]["MetodoPago"]);
			Email = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Email"])) ? "" : dataTable.Rows[0]["Email"]);
		}
	}

	public DataTable devolverNombresParaLLevar()
	{
		return BD.ConsultaVer("distinct ParaLLevar.Nombre,ParaLLevar.Nombre", "ParaLLevar", "Nombre <> ''", "ParaLLevar.Nombre");
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("ParaLLevar.ParaLlevarID,ParaLLevar.Nombre,ParaLLevar.NombreFactura,ParaLLevar.NIT,ParaLLevar.HoraRecoger,ParaLLevar.Telefono,Direccion", "ParaLLevar");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select ParaLLevar.ParaLlevarID,ParaLLevar.Nombre,ParaLLevar.NombreFactura,ParaLLevar.NIT,ParaLLevar.HoraRecoger,ParaLLevar.Telefono,Direccion", "ParaLLevar) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("ParaLLevar", "Nombre='" + Nombre + "',NombreFactura='" + NombreFactura + "',NIT='" + NIT + "',Direccion='" + Direccion + "',HoraRecoger=" + VariableGeneral.ArmarFecha(HoraRecoger) + ",Telefono='" + Telefono + "',Email='" + Email + "',Notas='" + notas + "',MetodoPago='" + MetodoPago + "',laty_num='" + Conversion.Str(laty_num) + "',lonx_num='" + Conversion.Str(lonx_num) + "',Motociclista=" + Conversions.ToString(Motociclista) + ",flagSync=NULL", "ParaLlevarID=" + ParaLlevarID);
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

	public int UpdateMontoDelivery(double MontoDelivery)
	{
		int result;
		try
		{
			BD.ConsultaModificar("ParaLLevar", "MontoDelivery = " + Conversion.Str(MontoDelivery) + ",flagSync=NULL", "ParaLlevarID=" + ParaLlevarID);
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

	public int UpdateEmail(string email)
	{
		int result;
		try
		{
			BD.ConsultaModificar("ParaLLevar", "email = '" + email + "',flagSync=NULL", "ParaLlevarID=" + ParaLlevarID);
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

	public int UpdateRepartidor()
	{
		int result;
		try
		{
			BD.ConsultaModificar("ParaLLevar", "Motociclista = " + Conversions.ToString(Motociclista) + ",flagSync=NULL", "ParaLlevarID=" + ParaLlevarID);
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

	public int UpdateMetodoPago()
	{
		int result;
		try
		{
			BD.ConsultaModificar("ParaLLevar", "MetodoPago = '" + MetodoPago + "',flagSync=NULL", "ParaLlevarID=" + ParaLlevarID);
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

	public double getMontoDelivery()
	{
		double result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("MontoDelivery", "ParaLLevar", "ParaLlevarID = " + ParaLlevarID);
			result = ((dataTable.Rows.Count <= 0) ? 0.0 : Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0)));
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0.0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int Insertar(string email)
	{
		int result;
		try
		{
			if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				ParaLlevarID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ParaLlevarID)", "ParaLLevar").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(Conversions.ToString(ParaLlevarID) + ",'" + Nombre + "','" + NombreFactura + "','" + NIT + "',", VariableGeneral.ArmarFecha(HoraRecoger), ",'", Telefono, "','", Direccion, "', ", Conversions.ToString(Motociclista), ",'", notas, "',", Conversion.Str(laty_num), ",", Conversion.Str(lonx_num), ",", Conversion.Str(0), ",'", email, "','", MetodoPago, "'"), "ParaLLevar(ParaLlevarID,Nombre,NombreFactura,NIT,HoraRecoger,Telefono,Direccion, Motociclista,Notas, laty_num, lonx_num, MontoDelivery,email,MetodoPago)");
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat("'" + Nombre + "','" + NombreFactura + "','" + NIT + "',", VariableGeneral.ArmarFecha(HoraRecoger), ",'", Telefono, "','", Direccion, "',", Conversions.ToString(Motociclista), ",'", notas, "',", Conversion.Str(laty_num), ",", Conversion.Str(lonx_num), ",", Conversion.Str(0), ",'", email, "','", MetodoPago, "'"), "ParaLLevar(Nombre,NombreFactura,NIT,HoraRecoger,Telefono,Direccion, Motociclista,Notas, laty_num, lonx_num, MontoDelivery,email,MetodoPago)", ref ParaLlevarID);
			}
			if (new ctlConfiguraciones().devolverGuardarCliente())
			{
				bool flag = false;
				if (email.Length > 0)
				{
					if (Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("count(*) ", "Clientes", "NombreFactura = '" + NombreFactura + "' or correo ='" + email + "'").Rows[0][0], 0, TextCompare: false))
					{
						flag = true;
					}
				}
				else if (Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("count(*) ", "Clientes", "NombreFactura = '" + NombreFactura + "'").Rows[0][0], 0, TextCompare: false))
				{
					flag = true;
				}
				if (!flag && Operators.ConditionalCompareObjectEqual(BD.ConsultaVer("count(*) ", "Clientes", "Nombre + ' ' + Apellidos = '" + Nombre + "'").Rows[0][0], 0, TextCompare: false))
				{
					flag = true;
				}
				if (flag)
				{
					clsClientes clsClientes2 = new clsClientes();
					int num = Nombre.IndexOf(" ");
					string nombre = Nombre.Substring(0, num);
					string apellidos = Nombre.Substring(checked(num + 1));
					clsClientes2._Nombre = nombre;
					clsClientes2._Apellidos = apellidos;
					clsClientes2._NombreFactura = NombreFactura;
					clsClientes2._CI = NIT;
					if (Versioned.IsNumeric(Telefono))
					{
						clsClientes2._Celular = Conversions.ToInteger((Telefono.Length > 0) ? Telefono : ((object)0));
					}
					clsClientes2._Direccion = Direccion;
					clsClientes2._correo = email;
					clsClientes2._Activo = true;
					clsClientes2.Insert();
				}
			}
			result = ParaLlevarID;
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
			if (BD.ConsultaEliminar("ParaLLevar", "ParaLlevarID = " + ParaLlevarID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar ParaLlevar, se encuentra en uso");
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
}
