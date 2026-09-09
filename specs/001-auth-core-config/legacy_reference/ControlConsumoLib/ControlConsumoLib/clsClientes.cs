using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsClientes
{
	private int ID;

	private string Nombre;

	private string Apellidos;

	private int Celular;

	private int Sexo;

	private string CI;

	private string Nacionalidad;

	private string Comentarios;

	private string correo;

	private DateTime cumpleanos;

	private double Saldo;

	private double Descuento;

	private int ReferidoPor;

	private bool FacturaCredito;

	private bool Activo;

	private string Direccion;

	private string Codigo;

	private double MaxDeuda;

	private string NombreFactura;

	private int TipoDocumentoId;

	public int _ID
	{
		get
		{
			return ID;
		}
		set
		{
			ID = value;
		}
	}

	public string _correo
	{
		get
		{
			return correo;
		}
		set
		{
			correo = value;
		}
	}

	public DateTime _cumpleanos
	{
		get
		{
			return cumpleanos;
		}
		set
		{
			cumpleanos = value;
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

	public string _Apellidos
	{
		get
		{
			return Apellidos;
		}
		set
		{
			Apellidos = value;
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

	public bool _Sexo
	{
		get
		{
			return Sexo != 0;
		}
		set
		{
			Sexo = 0 - (value ? 1 : 0);
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

	public string _Nacionalidad
	{
		get
		{
			return Nacionalidad;
		}
		set
		{
			Nacionalidad = value;
		}
	}

	public string _Comentarios
	{
		get
		{
			return Comentarios;
		}
		set
		{
			Comentarios = value;
		}
	}

	public double _Saldo
	{
		get
		{
			return Saldo;
		}
		set
		{
			Saldo = value;
		}
	}

	public double _Descuento
	{
		get
		{
			return Descuento;
		}
		set
		{
			Descuento = value;
		}
	}

	public int _ReferidoPor
	{
		get
		{
			return ReferidoPor;
		}
		set
		{
			ReferidoPor = value;
		}
	}

	public bool _FacturaCredito
	{
		get
		{
			return FacturaCredito;
		}
		set
		{
			FacturaCredito = value;
		}
	}

	public bool _Activo
	{
		get
		{
			return Activo;
		}
		set
		{
			Activo = value;
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

	public double _MaxDeuda
	{
		get
		{
			return MaxDeuda;
		}
		set
		{
			MaxDeuda = value;
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

	public int _TipoDocumentoId
	{
		get
		{
			return TipoDocumentoId;
		}
		set
		{
			TipoDocumentoId = value;
		}
	}

	public string Convertir()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return " str( ID )";
		}
		return "cast(ID as varchar)";
	}

	public DataTable DevolverCliente()
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer("distinct ID,concat(Clientes.Nombre , ' ' , Clientes.Apellidos , ' (' , " + Convertir() + " , ')') as name1", "Clientes", "", "name1");
		}
		if (configuration.gMODO_ACCESS == 0)
		{
			return BD.ConsultaVer("distinct ID,(Clientes.Nombre + ' ' + Clientes.Apellidos + ' (' + " + Convertir() + " + ')') as name1", "Clientes", "", "name1");
		}
		return BD.ConsultaVer("distinct ID,(Clientes.Nombre + ' ' + Clientes.Apellidos + ' (' + " + Convertir() + " + ')') as name1", "Clientes", "1=1", "(Clientes.Nombre + ' ' + Clientes.Apellidos + ' (' +  str( ID ) + ')') ");
	}

	public DataTable devolverCumpleañerosMes(int Mes)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo as Masculino,Clientes.CI, Correo, Cumpleanos  as FechaNacimiento,  Direccion", "Clientes", "activo=" + VariableGeneral.armarBolean(1) + "  and (MID(Aux,4,2) = '" + Conversions.ToString(Mes) + "')", " Day(MID(Aux,1,10))  ", "Cumpleanos");
		}
		return BD.ConsultaVer("Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo as Masculino,Clientes.CI, Correo, convert(varchar(10),cumpleanos,103) as FechaNacimiento,  Direccion ", "Clientes", "activo=" + VariableGeneral.armarBolean(1) + "  and  (month(cumpleanos) = " + Conversions.ToString(Mes) + ")", " cumpleanos ");
	}

	public DataTable DevolverClienteActivo()
	{
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.AguaViva1)
		{
			return BD.ConsultaVer("distinct ID,(Nombre + ' ' + Apellidos + ' - ' + TipoUbicacion + ' (' +  RTRIM(" + Convertir() + ") + ')') as name1", "Clientes", "Activo= " + VariableGeneral.armarBolean(1));
		}
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer("distinct ID,concat(Clientes.Nombre , ' ' , Clientes.Apellidos , ' (' , " + Convertir() + " , ')') as name1", "Clientes", "Activo= " + VariableGeneral.armarBolean(1), "name1");
		}
		if (configuration.gMODO_ACCESS == 0)
		{
			return BD.ConsultaVer("distinct ID,(Clientes.Nombre + ' ' + Clientes.Apellidos + ' (' + " + Convertir() + " + ')') as name1", "Clientes", "Activo= " + VariableGeneral.armarBolean(1), "name1");
		}
		return BD.ConsultaVer(" ID,(Clientes.Nombre + ' ' + Clientes.Apellidos + ' (' + RTRIM(" + Convertir() + ") + ')') as name1", "Clientes", "Activo= " + VariableGeneral.armarBolean(1), "Clientes.Nombre, Clientes.Apellidos", "Clientes.Nombre, Clientes.Apellidos, id");
	}

	public DataTable DevolverClienteActivoParaCajero()
	{
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.AguaViva1)
		{
			return BD.ConsultaVer("distinct ID,(Nombre + ' ' + Apellidos + ' - ' + TipoUbicacion + ' (' +  RTRIM(" + Convertir() + ") + ')') as name1", "Clientes", "Activo= " + VariableGeneral.armarBolean(1) + " and Nacionalidad = 'Cajero'");
		}
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer("distinct ID,concat(Clientes.Nombre , ' ' , Clientes.Apellidos , ' (' , " + Convertir() + " , ')') as name1", "Clientes", "Activo= " + VariableGeneral.armarBolean(1) + " and Nacionalidad = 'Cajero'", "name1");
		}
		if (configuration.gMODO_ACCESS == 0)
		{
			return BD.ConsultaVer("distinct ID, (Clientes.Nombre + ' ' + Clientes.Apellidos + ' (' + " + Convertir() + " + ')') as name1", "Clientes", "Activo= " + VariableGeneral.armarBolean(1) + " and Nacionalidad = 'Cajero'", "name1");
		}
		return BD.ConsultaVer(" ID, (Clientes.Nombre + ' ' + Clientes.Apellidos + ' (' + RTRIM(" + Convertir() + ") + ')') as name1", "Clientes", "Activo= " + VariableGeneral.armarBolean(1) + " and Nacionalidad = 'Cajero'", "Clientes.Nombre, Clientes.Apellidos", "Clientes.Nombre, Clientes.Apellidos, id");
	}

	public DataTable DevolverDireccionesCliente()
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer("distinct id, direccion", "Clientes", "concat(Clientes.Nombre , ' ' , Clientes.Apellidos) like '" + Nombre + "'");
		}
		return BD.ConsultaVer("distinct id, direccion", "Clientes", "(Clientes.Nombre + ' ' + Clientes.Apellidos) like '" + Nombre + "'");
	}

	public DataTable ToReturnUltimoCliente()
	{
		return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos,Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", "Clientes", "Clientes.ID = (select MAX(Clientes.ID) from Clientes)");
	}

	public DataTable ToReturn()
	{
		return BD.ConsultaVer("distinct Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", "Clientes");
	}

	public DataTable ToReturnNacionalidades()
	{
		return BD.ConsultaVer("distinct 0,Clientes.Nacionalidad", "Clientes", "", "Clientes.Nacionalidad");
	}

	public DataTable SearchClientByNameIni(string ini)
	{
		return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes ", "Clientes.Apellidos like '" + ini + "%'");
	}

	public DataTable SearchClientByName(string name, string search)
	{
		return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos,Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "Clientes.Nombre" + search + " like '%" + name + "%'");
	}

	public DataTable SearchClientByNameApellidoCI()
	{
		string text = "";
		if (Nombre.Length > 0)
		{
			text = "(Clientes.Nombre + ' ' + Clientes.Apellidos) like '%" + Nombre + "%'";
		}
		if (CI.Length > 0)
		{
			if (text.Length > 0)
			{
				text += " and ";
			}
			text = text + "Clientes.CI like '%" + CI + "%'";
		}
		if (text.Length == 0)
		{
			text += " 1=1 ";
		}
		return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", text);
	}

	public void getClienteNombreParaHuellaPorCodigo()
	{
		DataTable dataTable = BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos", "Clientes", "Clientes.Codigo = '" + Codigo + "'");
		if (dataTable.Rows.Count == 0)
		{
			ID = 0;
			Nombre = "";
			Apellidos = "";
		}
		else
		{
			ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"]), 0));
			Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""));
			Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Apellidos"]), ""));
		}
	}

	public void getClienteNombreParaFacial()
	{
		DataTable dataTable = BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,FacturaCredito, Direccion, Comentarios, Nacionalidad", "Clientes", "Clientes.codigo = '" + Codigo + "' and Activo=" + VariableGeneral.armarBolean(1));
		if (dataTable.Rows.Count == 0)
		{
			ID = 0;
			Nombre = "";
			Apellidos = "";
			Direccion = "";
			Comentarios = "";
			Nacionalidad = "";
			FacturaCredito = false;
		}
		else
		{
			ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"]), ""));
			Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""));
			Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Apellidos"]), ""));
			FacturaCredito = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaCredito"]), false));
			Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Direccion"]), ""));
			Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Comentarios"]), ""));
			Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nacionalidad"]), ""));
		}
	}

	public void getClientInfoByID()
	{
		DataTable dataTable = BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.CI,Clientes.Comentarios, correo, MaxDeuda", "Clientes", "Clientes.ID = " + ID);
		if (dataTable.Rows.Count == 0)
		{
			Interaction.MsgBox("No hay cliente con ese ID");
			return;
		}
		Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""));
		Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Apellidos"]), ""));
		Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Celular"]), 0));
		CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CI"]), ""));
		MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MaxDeuda"]), 0));
		Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Comentarios"]), ""));
	}

	public void getClientEmailByID()
	{
		DataTable dataTable = BD.ConsultaVer("correo", "Clientes", "Clientes.ID = " + ID);
		if (dataTable.Rows.Count == 0)
		{
			correo = "";
		}
		else
		{
			correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["correo"]), ""));
		}
	}

	public int countClientes()
	{
		return Conversions.ToInteger(BD.ConsultaVer("count(*)", "Clientes").Rows[0][0]);
	}

	public void NombreFacturaXci()
	{
		DataTable dataTable = BD.ConsultaVer("id,Clientes.NombreFactura,Celular,correo ", "Clientes", "Clientes.ci like '" + CI + "'");
		if (dataTable.Rows.Count == 0)
		{
			ID = 0;
			return;
		}
		ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["id"]), 0));
		NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFactura"]), ""));
		Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Celular"]), 0));
		correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["correo"]), ""));
	}

	public void NombreXci()
	{
		DataTable dataTable = BD.ConsultaVer("Clientes.Nombre,Clientes.Apellidos", "Clientes", "Clientes.ci like '" + CI + "'");
		if (dataTable.Rows.Count != 0)
		{
			Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""));
			Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Apellidos"]), ""));
		}
	}

	public bool CIexiste()
	{
		if (ID == 0)
		{
			return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "Clientes", "Clientes.CI like '" + CI + "'").Rows[0][0], 0, TextCompare: false);
		}
		return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "Clientes", "Clientes.CI like '" + CI + "' and Clientes.ID <> " + Conversions.ToString(ID)).Rows[0][0], 0, TextCompare: false);
	}

	public bool CodigoExiste()
	{
		if (ID == 0)
		{
			return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "Clientes", "codigo like '" + Codigo + "'").Rows[0][0], 0, TextCompare: false);
		}
		return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "Clientes", "codigo like '" + Codigo + "' and Clientes.ID <> " + Conversions.ToString(ID)).Rows[0][0], 0, TextCompare: false);
	}

	public bool CodigoClienteXLectorExiste()
	{
		return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "Clientes", "Clientes.codigo like '" + Codigo + "'").Rows[0][0], 0, TextCompare: false);
	}

	public int Modify()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Clientes", "Nombre='" + Nombre + "',Apellidos='" + Apellidos + "',Correo='" + correo + "',cumpleanos=" + VariableGeneral.ArmarFecha(cumpleanos) + ",Celular=" + Celular + ",Sexo=" + VariableGeneral.armarBolean(Sexo) + ",CI='" + CI + "',Nacionalidad='" + Nacionalidad + "',Comentarios='" + Comentarios + "',Descuento=" + Conversion.Str(Descuento) + ",Saldo=" + Conversion.Str(Saldo) + ",ReferidoPor=" + Conversions.ToString(ReferidoPor) + ",FacturaCredito=" + VariableGeneral.armarBolean(FacturaCredito) + ",Activo=" + VariableGeneral.armarBolean(Activo) + ",Direccion='" + Direccion + "',Codigo='" + Codigo + "',MaxDeuda=" + Conversion.Str(MaxDeuda) + ",TipoDocumentoId=" + Conversions.ToString(TipoDocumentoId) + ",NombreFactura='" + NombreFactura + "',flagSync=NULL", "ID=" + ID);
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

	public int Insert()
	{
		checked
		{
			int result;
			try
			{
				if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ID)", "Clientes").Rows[0][0]), 0));
					ID++;
					if (cumpleanos.Year < 1900)
					{
						cumpleanos = DateAndTime.Today;
					}
					BD.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(ID) + ",'" + Nombre + "','" + Apellidos + "',", Celular.ToString(), ","), VariableGeneral.armarBolean(Sexo), ",'", CI, "','", Nacionalidad, "','", Comentarios, "','", correo, "',", VariableGeneral.ArmarFecha(cumpleanos), ",", Conversion.Str(Saldo), ",", Conversion.Str(Descuento), ",", Conversions.ToString(ReferidoPor), ",", VariableGeneral.armarBolean(FacturaCredito), ",", VariableGeneral.armarBolean(Activo), ",'", Direccion, "','", Codigo, "',", Conversion.Str(MaxDeuda), ",'", NombreFactura, "',", Conversions.ToString(TipoDocumentoId)), "Clientes(ID,Nombre,Apellidos,Celular,Sexo,CI,Nacionalidad,Comentarios,Correo,Cumpleanos,Saldo, Descuento,ReferidoPor,FacturaCredito,Activo,Direccion,Codigo, MaxDeuda,NombreFactura,TipoDocumentoId)");
					result = ID;
				}
				else
				{
					if (cumpleanos.Year < 1900)
					{
						cumpleanos = DateAndTime.Today;
					}
					BD.ConsultaInsertar3(string.Concat(string.Concat("'" + Nombre + "','" + Apellidos + "',", Celular.ToString(), ","), VariableGeneral.armarBolean(Sexo), ",'", CI, "','", Nacionalidad, "','", Comentarios, "','", correo, "',", VariableGeneral.ArmarFecha(cumpleanos), ",", Conversion.Str(Saldo), ",", Conversion.Str(Descuento), ",", Conversions.ToString(ReferidoPor), ",", VariableGeneral.armarBolean(FacturaCredito), ",", VariableGeneral.armarBolean(Activo), ",'", Direccion, "','", Codigo, "',", Conversion.Str(MaxDeuda), ",'", NombreFactura, "',", Conversions.ToString(TipoDocumentoId)), "Clientes(Nombre,Apellidos,Celular,Sexo,CI,Nacionalidad,Comentarios,Correo,Cumpleanos,Saldo, Descuento,ReferidoPor,FacturaCredito,Activo,Direccion,Codigo, MaxDeuda,NombreFactura,TipoDocumentoId)", ref ID);
					result = ID;
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

	public void ReporteCuenta(ref DataSet data, string DatNombre, int visitaID)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			BD.ConsultaVerDataset(ref data, "select  Producto  , sum(tab1.Cantidad) as Cantidad, sum(tab1.Pago) as Pago, sum(tab1.Debe) as Debe from (select Productos.Nombre + iif(Pago=" + VariableGeneral.armarBolean(0) + " and Debe=" + VariableGeneral.armarBolean(0) + ", ' (Cortesia)', '') As Producto, DetalleCuenta.Cantidad, DetalleCuenta.Pago, DetalleCuenta.Debe from (DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID where Visitas.ID=" + Conversions.ToString(visitaID) + " and Borrada=" + VariableGeneral.armarBolean(0) + ") as tab1 group by tab1.Producto order by Producto", DatNombre);
		}
		else
		{
			BD.ConsultaVerDataset(ref data, "select  Producto, sum(tab1.Cantidad) as Cantidad, sum(tab1.Pago) as Pago, sum(tab1.Debe) as Debe from (select Productos.Nombre + CASE Pago WHEN  " + VariableGeneral.armarBolean(0) + "  THEN CASE Debe WHEN  " + VariableGeneral.armarBolean(0) + "  THEN   ' (Cortesia)' ELSE  ''  END ELSE  '' END , ' (Cortesia)', '') As Producto, DetalleCuenta.Cantidad, DetalleCuenta.Pago, DetalleCuenta.Debe from (DetalleCuenta INNER JOIN Visitas On DetalleCuenta.VisitaID = Visitas.ID) INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID where Visitas.ID=" + Conversions.ToString(visitaID) + " and Borrada=" + VariableGeneral.armarBolean(0) + ") as tab1 group by tab1.Producto order by Producto", DatNombre);
		}
	}

	public void ReporteVenta(ref DataSet data, string DatNombre, int visitaID)
	{
		BD.ConsultaVerDataset(ref data, "select Productos.Codigo as Descripcion, Productos.Nombre As Nombre, DetalleCuenta.Cantidad, ROUND(PrecioUnit,2) as precioUnitario, Pago+Debe as monto  from DetalleCuenta INNER JOIN Productos On DetalleCuenta.ProductoID = Productos.ID where visitaID=" + Conversions.ToString(visitaID) + " and Borrada=" + VariableGeneral.armarBolean(0) + " order by DetalleCuenta.ID", DatNombre);
	}

	public DataTable ObtenerDatosClientes1(int visitaID)
	{
		return BD.ConsultaVer("Mesas.Codigo, Mesas.Nombre, Visitas.Fecha, Clientes.Nombre, Clientes.Apellidos, Clientes.CI", "Clientes RIGHT JOIN (Mesas INNER JOIN Visitas ON Mesas.ID = Visitas.MesaID) ON Clientes.ID = Visitas.ClienteID", " Visitas.ID=" + Conversions.ToString(visitaID));
	}

	public DataTable ObtenerDatosClientesFactura(int visitaID)
	{
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.AguaViva1)
		{
			return BD.ConsultaVer("Visitas.Fecha, NombreFactura, Clientes.CI, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, Clientes.Correo, Clientes.Celular, tipoDocumentoID", "Clientes INNER JOIN  Visitas ON Clientes.ID = Visitas.ClienteID", " Visitas.ID=" + Conversions.ToString(visitaID));
		}
		return BD.ConsultaVer("Visitas.Fecha, Clientes.NombreFactura,  Clientes.CI, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, Clientes.Correo, Clientes.Celular, tipoDocumentoID", "Clientes INNER JOIN  Visitas ON Clientes.ID = Visitas.ClienteID", " Visitas.ID=" + Conversions.ToString(visitaID));
	}

	public DataTable ObtenerDatosNombreClientesID()
	{
		return BD.ConsultaVer("Nombre, Apellidos , CI", "Clientes ", " ID=" + Conversions.ToString(ID));
	}

	public DataTable ObtenerDatosNombreFacturaClientesID()
	{
		return BD.ConsultaVer("Clientes.NombreFactura , Clientes.CI, Celular, Correo,tipoDocumentoID", "Clientes ", " ID=" + Conversions.ToString(ID));
	}

	public DataTable ObtenerDatosClientesMolinete(string codigo)
	{
		return BD.ConsultaVer("Clientes.Nombre, Clientes.Apellidos, Clientes.CI, ID", "Clientes", "codigo like '" + codigo + "'");
	}

	public int Delete()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Clientes", "ID = " + ID) == 0)
			{
				Interaction.MsgBox("Can't delete , is in use");
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

	public DataTable CargarNombreNitClientes()
	{
		return BD.ConsultaVer("distinct (nit), Nombre", "Facturas");
	}

	public DataTable BuscarCliente(string id, string nombre, string apellido, string ci_)
	{
		string text = "";
		if (id.Length > 0)
		{
			text = text + "Clientes.ID like '" + id;
		}
		if (nombre.Length > 0)
		{
			if (text.Length > 0)
			{
				text += " and ";
			}
			text = text + "Clientes.Nombre like '%" + nombre + "%'";
		}
		if (apellido.Length > 0)
		{
			if (text.Length > 0)
			{
				text += " and ";
			}
			text = text + "Clientes.apellidos like '%" + apellido + "%'";
		}
		if (ci_.Length > 0)
		{
			if (text.Length > 0)
			{
				text += " and ";
			}
			text = text + "Clientes.CI like '%" + ci_ + "%'";
		}
		if (text.Length == 0)
		{
			text += " 1=1 ";
		}
		return BD.ConsultaVer("distinct " + ((configuration.gMODO_ACCESS == 1) ? "(false)" : "cast(0 as bit)") + "  as Seleccionar, Clientes.ID ,Clientes.Nombre,Clientes.Apellidos, Clientes.CI", "Clientes ", text);
	}

	public void DevolverDescuento(ref double Descuento, ref double MinimoMonto, ref double maximoMonto, ref string mensajeCajero)
	{
		DataTable dataTable = BD.ConsultaVer("Porcentaje, MinimoMonto, MaximoMonto, AvisoCajero", "clientes inner join Descuentos on Clientes.Descuento=Descuentos.DescuentoID", "Descuentos.Activo= " + VariableGeneral.armarBolean(1) + " and ID = " + Conversions.ToString(ID));
		if (dataTable.Rows.Count > 0)
		{
			Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
			MinimoMonto = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MinimoMonto"]), 0));
			maximoMonto = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MaximoMonto"]), 0));
			mensajeCajero = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AvisoCajero"]), ""));
		}
		else
		{
			Descuento = 0.0;
			mensajeCajero = "";
			MinimoMonto = 0.0;
			maximoMonto = 0.0;
		}
	}

	public bool DevolverFacturaCredito()
	{
		return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "Clientes", ("ID = " + Conversions.ToString(ID) + " and  FacturaCredito = " + VariableGeneral.armarBolean(1)) ?? "").Rows[0][0], 0, TextCompare: false);
	}

	public DataTable ToReturnActivos(bool habilitado)
	{
		return BD.ConsultaVer("distinct Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", "Clientes", "Activo= " + VariableGeneral.armarBolean(habilitado));
	}

	public DataTable SearchClientByNameApellidoCIActivos(bool habilitado)
	{
		if ((Operators.CompareString(CI, "", TextCompare: false) != 0) & (Operators.CompareString(Nombre, "", TextCompare: false) != 0))
		{
			if (configuration.gGimnasio)
			{
				return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( ( Clientes.Nombre + ' ' + Clientes.Apellidos) like '%" + Nombre + "%' and Clientes.CI like '%" + CI + "%' or Clientes.codigo like '%" + CI + "%') )");
			}
			return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( ( Clientes.Nombre + ' ' + Clientes.Apellidos) like '%" + Nombre + "%' and Clientes.CI like '%" + CI + "%') )");
		}
		if (Operators.CompareString(CI, "", TextCompare: false) != 0)
		{
			if (configuration.gGimnasio)
			{
				return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( Clientes.CI like '%" + CI + "%' or Clientes.codigo like '%" + CI + "%') )");
			}
			return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( Clientes.CI like '%" + CI + "%') )");
		}
		if (Operators.CompareString(Nombre, "", TextCompare: false) != 0)
		{
			return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( ( Clientes.Nombre + ' ' + Clientes.Apellidos) like '%" + Nombre + "%' ) )");
		}
		return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " )");
	}

	public DataTable SearchClientByNameApellidoNacionalidadActivos(bool habilitado)
	{
		if ((Operators.CompareString(Nacionalidad, "", TextCompare: false) != 0) & (Operators.CompareString(Nombre, "", TextCompare: false) != 0))
		{
			if (configuration.gGimnasio)
			{
				return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( ( Clientes.Nombre + ' ' + Clientes.Apellidos) like '%" + Nombre + "%' and Clientes.Nacionalidad like '%" + Nacionalidad + "%' or Clientes.codigo like '%" + Nacionalidad + "%') )");
			}
			return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( ( Clientes.Nombre + ' ' + Clientes.Apellidos) like '%" + Nombre + "%' and Clientes.Nacionalidad like '%" + Nacionalidad + "%') )");
		}
		if (Operators.CompareString(Nacionalidad, "", TextCompare: false) != 0)
		{
			if (configuration.gGimnasio)
			{
				return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( Clientes.Nacionalidad like '%" + Nacionalidad + "%' or Clientes.codigo like '%" + Nacionalidad + "%') )");
			}
			return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( Clientes.Nacionalidad like '%" + Nacionalidad + "%') )");
		}
		if (Operators.CompareString(Nombre, "", TextCompare: false) != 0)
		{
			return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " and ( ( Clientes.Nombre + ' ' + Clientes.Apellidos) like '%" + Nombre + "%' ) )");
		}
		return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "(Activo= " + VariableGeneral.armarBolean(habilitado) + " )");
	}

	public DataTable SearchClientByNameIniActivos(string ini, bool habilitado)
	{
		return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes ", " Clientes", "Activo= " + VariableGeneral.armarBolean(habilitado) + "  and   Clientes.Apellidos like '%" + ini + "%'");
	}

	public DataTable SearchClientByNameActivo(string name, string search, bool habilitado)
	{
		return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos,Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", " Clientes", "Activo= " + VariableGeneral.armarBolean(habilitado) + "  and   Clientes." + search + " like '%" + name + "%'");
	}

	public DataTable ToReturnUltimoClienteActivo(bool habilitado)
	{
		return BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos,Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", "Clientes", "Activo= " + VariableGeneral.armarBolean(habilitado) + "  and  Clientes.ID = (select MAX(Clientes.ID) from Clientes)");
	}

	public DataTable ObtenerDatosClientesNota(int nit)
	{
		return BD.ConsultaVer("Clientes.Comentarios, Clientes.Direccion", "Clientes ", " Clientes.CI='" + Conversions.ToString(nit) + "'");
	}

	public DataTable DevolverAlumnos()
	{
		return BD.ConsultaVer("Clientes.ID, Clientes.Nombre + ' ' + Clientes.Apellidos as NombreCompleto, Clientes.Comentarios, (select Sum(MontoRestante) from Anticipos where Anticipos.ClienteID=Clientes.ID) as Saldo, (select sum(DetalleCuenta.Debe) from DetalleCuenta where DetalleCuenta.VisitaID in (select Visitas.ID from Visitas where Visitas.ClienteID=Clientes.ID)) as Deuda, Activo", "Clientes", "Activo= " + VariableGeneral.armarBolean(1));
	}

	public DataTable DevolverAlumnosPreImpresos()
	{
		return BD.ConsultaVer("Clientes.ID, Clientes.Nombre + ' ' + Clientes.Apellidos as NombreCompleto, Clientes.Comentarios, (select Sum(MontoRestante) from Anticipos where Anticipos.ClienteID=Clientes.ID) as Saldo, (select sum(DetalleCuenta.Debe) from DetalleCuenta where DetalleCuenta.VisitaID in (select Visitas.ID from Visitas where Visitas.ClienteID=Clientes.ID)) as Deuda, Activo", "Clientes", "Activo= " + VariableGeneral.armarBolean(1) + " and ( Clientes.Comentarios= '1BR' or Clientes.Comentarios= '1BVI' OR Clientes.Comentarios='1BVII' OR Clientes.Comentarios= '2BR' or Clientes.Comentarios= '2BVI' OR Clientes.Comentarios='2BVII' OR Clientes.Comentarios= '3BR' or Clientes.Comentarios= '3BVI' OR Clientes.Comentarios='3BVII' ) ");
	}

	public DataTable DevolverPensionados(DateTime fecha)
	{
		return BD.ConsultaVer("select tab1.id, tab1.Nombre,tab1.Curso,sum(tab1.Almuerzos) as Almuerzos , \r\n                            case when  Convert(DATE, tab2.FechaUso) =Convert(DATE,  " + VariableGeneral.ArmarFecha(fecha) + ") then 1 else 0 end as ticket\r\n                            from \r\n                            (select clientes.id, clientes.Nombre + Clientes.Apellidos as nombre, Clientes.Comentarios as Curso , \r\n                            DetalleCuentas_Paquetes.Cantidad -DetalleCuentas_Paquetes.CantidadUsada as Almuerzos\r\n                             from clientes left join visitas on Visitas .ClienteID =Clientes.ID \r\n                            left join DetalleCuenta on Visitas.id=DetalleCuenta.VisitaID \r\n                            left join DetalleCuentas_Paquetes on DetalleCuenta.ID=DetalleCuentas_Paquetes.detallecuentaID\r\n                            where DetalleCuentas_Paquetes.Cantidad -DetalleCuentas_Paquetes.CantidadUsada >0) as tab1\r\n                            left join \r\n                            (select Clientes.id, FechaUso \r\n                             from clientes left join visitas on Visitas .ClienteID =Clientes.ID \r\n                            left join DetalleCuenta on Visitas.id=DetalleCuenta.VisitaID \r\n                            right join DetalleCuentas_Paquetes on DetalleCuenta.ID=DetalleCuentas_Paquetes.detallecuentaID\r\n                            right join UsoPaquetes on UsoPaquetes.DetalleCuenta_PaqueteID =DetalleCuentas_Paquetes.DetalleCuenta_PaqueteID )\r\n                             as tab2 on tab1.id=tab2 .id and Convert(DATE, tab2.FechaUso) =Convert(DATE, " + VariableGeneral.ArmarFecha(fecha) + ")\r\n                             group by tab1.id,tab1.Nombre, tab1.Curso ,tab2.FechaUso ");
	}

	public DataTable DevolverPensionadosMinimos(bool todos)
	{
		if (todos)
		{
			return BD.ConsultaVer("select tab1.id, tab1.Nombre,tab1.Curso,sum(tab1.Almuerzos) as Almuerzos, Celular_Madre ,Celular_Padre ,Telefono\r\n                                from (\r\n                                select clientes.id, clientes.Nombre + Clientes.Apellidos as nombre, Clientes.Comentarios as Curso , \r\n                                DetalleCuentas_Paquetes.Cantidad -DetalleCuentas_Paquetes.CantidadUsada as Almuerzos,\r\n                                Celular as Celular_Madre, Nacionalidad as Celular_Padre,Direccion as Telefono\r\n                                from clientes left join visitas on Visitas .ClienteID =Clientes.ID \r\n                                left join DetalleCuenta on Visitas.id=DetalleCuenta.VisitaID \r\n                                left join DetalleCuentas_Paquetes on DetalleCuenta.ID=DetalleCuentas_Paquetes.detallecuentaID\r\n                                where DetalleCuentas_Paquetes.Cantidad -DetalleCuentas_Paquetes.CantidadUsada >0\t\t\t\t\t\r\n                                ) as tab1         \r\n                                group by tab1.id,tab1.Nombre, tab1.Curso,Celular_Madre, Celular_Padre,Telefono");
		}
		return BD.ConsultaVer("Select * from (select tab1.id, tab1.Nombre,tab1.Curso,sum(tab1.Almuerzos) as Almuerzos, Celular_Madre ,Celular_Padre ,Telefono\r\n                                from (\r\n                                select clientes.id, clientes.Nombre + Clientes.Apellidos as nombre, Clientes.Comentarios as Curso , \r\n                                DetalleCuentas_Paquetes.Cantidad -DetalleCuentas_Paquetes.CantidadUsada as Almuerzos,\r\n                                Celular as Celular_Madre, Nacionalidad as Celular_Padre,Direccion as Telefono\r\n                                from clientes left join visitas on Visitas .ClienteID =Clientes.ID \r\n                                left join DetalleCuenta on Visitas.id=DetalleCuenta.VisitaID \r\n                                left join DetalleCuentas_Paquetes on DetalleCuenta.ID=DetalleCuentas_Paquetes.detallecuentaID\r\n                                where DetalleCuentas_Paquetes.Cantidad -DetalleCuentas_Paquetes.CantidadUsada >0\t\t\t\t\t\r\n                                ) as tab1         \r\n                                group by tab1.id,tab1.Nombre, tab1.Curso,Celular_Madre, Celular_Padre,Telefono) as tab2\r\n                                where Almuerzos <=2");
	}

	public DataTable ToReturnClientesZuchinni()
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer("distinct ID,concat(Clientes.Nombre , ' ' , Clientes.Apellidos , ' (' , Clientes.CI , ')') as name", "Clientes");
		}
		return BD.ConsultaVer("distinct ID,Clientes.Nombre + ' ' + Clientes.Apellidos + ' (' + Clientes.CI + ')' as name", "Clientes");
	}

	public DataTable ToReturnClientesDeudores()
	{
		return BD.ConsultaVer("distinct Clientes.ID,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Sexo,Clientes.CI,Clientes.Nacionalidad,Clientes.Comentarios, correo, cumpleanos, Saldo, Descuento, ReferidoPor,FacturaCredito,Activo,Direccion,Codigo,MaxDeuda,NombreFactura,TipoDocumentoID", "Clientes", "Clientes.ID in (select ClienteID from visitas left join DetalleCuenta on Visitas.ID=DetalleCuenta.VisitaID where DetalleCuenta.Debe > 0 group by ClienteID)");
	}

	public double ToReturnDeudaCliente()
	{
		DataTable dataTable = BD.ConsultaVer("ClienteID , sum(DetalleCuenta.Debe)", "visitas left join DetalleCuenta on Visitas.ID=DetalleCuenta.VisitaID ", " DetalleCuenta.Debe > 0 and ClienteID= " + Conversions.ToString(ID), "", "  ClienteID");
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), 0));
		}
		return 0.0;
	}

	public void DevolverMaxDeuda()
	{
		DataTable dataTable = BD.ConsultaVer("MaxDeuda", "Clientes", "Clientes.ID = " + ID);
		if (dataTable.Rows.Count > 0)
		{
			MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MaxDeuda"]), 0));
		}
		else
		{
			MaxDeuda = 0.0;
		}
	}

	public string proximoCodigo()
	{
		DataTable dataTable = new DataTable();
		if (configuration.gMODO_ACCESS == 2)
		{
			dataTable = BD.ConsultaVer("max(codigo)", "Clientes", "codigo REGEXP '^[0-9]+$'");
		}
		else if (configuration.gMODO_ACCESS == 1)
		{
			dataTable = BD.ConsultaVer("max(col)", "(select cdbl(Codigo) as col from Clientes where ISNUMERIC(Codigo)) as tab1");
		}
		else if (configuration.gMODO_ACCESS == 0)
		{
			dataTable = BD.ConsultaVer("max(col)", "(select convert(float,Codigo) as col from Clientes where ISNUMERIC(Codigo)=1) as tab1");
		}
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToLong(Operators.AddObject(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0])) ? ((object)0) : dataTable.Rows[0][0], 1)).ToString();
		}
		return Conversions.ToString(1);
	}

	public void getClienteParaLlevar()
	{
		DataTable dataTable = BD.ConsultaVer("Clientes.ID,Clientes.CI,Clientes.Nombre,Clientes.Apellidos,Clientes.Celular,Clientes.Direccion", "Clientes", "Clientes.ID = " + ID);
		if (dataTable.Rows.Count == 0)
		{
			Interaction.MsgBox("No hay cliente con ese ID");
			return;
		}
		ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"]), ""));
		Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""));
		Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Apellidos"]), ""));
		Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Celular"]), 0));
		CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CI"]), ""));
		Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Direccion"]), 0));
	}

	public int ActualizarNITcelularDireccion()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Clientes", "Celular=" + Celular + ",Direccion='" + Direccion + "',CI='" + CI + "'", "ID=" + ID);
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

	public void getClienteNombrIDCodigo()
	{
		DataTable dataTable = BD.ConsultaVer("Clientes.ID,Clientes.Nombre,Clientes.Apellidos", "Clientes", "Clientes.Codigo = '" + Codigo + "'");
		if (dataTable.Rows.Count == 0)
		{
			ID = 0;
			Nombre = "";
			Apellidos = "";
		}
		else
		{
			ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"]), 0));
			Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"]), ""));
			Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Apellidos"]), ""));
		}
	}

	public DataTable DevolverClienteXID()
	{
		return BD.ConsultaVer("distinct ID,(Clientes.Nombre + ' ' + Clientes.Apellidos ) as Nombre", "Clientes", "ID =" + Conversions.ToString(ID));
	}
}
