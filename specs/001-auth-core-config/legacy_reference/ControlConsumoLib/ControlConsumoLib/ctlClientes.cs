using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlClientes
{
	private readonly clsClientes clsCli;

	public ctlClientes()
	{
		clsCli = new clsClientes();
	}

	public int GetID()
	{
		return clsCli._ID;
	}

	public void SetID(int ID)
	{
		clsCli._ID = ID;
	}

	public DataTable ToReturnClientes()
	{
		return clsCli.ToReturn();
	}

	public int countClientes()
	{
		return clsCli.countClientes();
	}

	public DataTable DevolverAlumnosActivos()
	{
		return clsCli.DevolverAlumnos();
	}

	public void getClienteNombreParaFacial1(string ID, ref string name, ref string lastname, ref bool masDe1ticket, ref string Direccion, ref string Curso, ref int ProdID)
	{
		clsCli._Codigo = ID;
		clsCli.getClienteNombreParaFacial();
		name = clsCli._Nombre;
		lastname = clsCli._Apellidos;
		masDe1ticket = clsCli._FacturaCredito;
		Direccion = clsCli._Direccion;
		Curso = clsCli._Comentarios;
		if (Versioned.IsNumeric(RuntimeHelpers.GetObjectValue(VariableGeneral.NZ(clsCli._Nacionalidad, ""))))
		{
			ProdID = Conversions.ToInteger(clsCli._Nacionalidad);
		}
		else
		{
			ProdID = 0;
		}
	}

	public int getClienteNombreParaHuellaPorCodigo(string codigo, ref string name, ref string lastname)
	{
		clsCli._Codigo = codigo;
		clsCli.getClienteNombreParaHuellaPorCodigo();
		name = clsCli._Nombre;
		lastname = clsCli._Apellidos;
		return clsCli._ID;
	}

	public void getClientEmailByID(ref string correo)
	{
		clsCli.getClientEmailByID();
		correo = clsCli._correo;
	}

	public void getClientInfoByID(ref string name, ref string lastname)
	{
		clsCli.getClientInfoByID();
		name = clsCli._Nombre;
		lastname = clsCli._Apellidos;
	}

	public void getClientCursoByIDComentarios(ref string Comentario)
	{
		clsCli.getClientInfoByID();
		Comentario = clsCli._Comentarios;
	}

	public DataTable DevolverCliente()
	{
		return clsCli.DevolverCliente();
	}

	public DataTable DevolverCumpleañerosMes(int mes)
	{
		return clsCli.devolverCumpleañerosMes(mes);
	}

	public DataTable DevolverClienteActivos()
	{
		return clsCli.DevolverClienteActivo();
	}

	public DataTable DevolverClienteActivoParaCajero()
	{
		return clsCli.DevolverClienteActivoParaCajero();
	}

	public DataTable DevolverDireccionesClienteXnombre(string nombre)
	{
		clsCli._Nombre = nombre;
		return clsCli.DevolverDireccionesCliente();
	}

	public void returnUltimoCliente(ref dtsClientes.ClientesDataTable dts)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsCli.ToReturnUltimoCliente();
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsClientes.ClientesRow clientesRow = dts.NewClientesRow();
			clientesRow.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			clientesRow.Nombre = Conversions.ToString(dataTable.Rows[i][1]);
			clientesRow.Apellidos = Conversions.ToString(dataTable.Rows[i][2]);
			clientesRow.Celular = Conversions.ToInteger(dataTable.Rows[i][3]);
			clientesRow.Sexo = Conversions.ToBoolean(dataTable.Rows[i][4]);
			clientesRow.CI = Conversions.ToString(dataTable.Rows[i][5]);
			clientesRow.Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), ""));
			clientesRow.Comentarios = Conversions.ToString(dataTable.Rows[i][7]);
			clientesRow.Correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][8]), ""));
			clientesRow.Cumpleanos = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][9]), null));
			clientesRow.Saldo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][10]), 0));
			clientesRow.Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][11]), 0));
			clientesRow.ReferidoPor = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][12]), 0));
			clientesRow.FacturaCredito = Conversions.ToByte(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][13]), 0));
			clientesRow.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][14]), 0));
			clientesRow.Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][15]), ""));
			clientesRow.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][16]), ""));
			clientesRow.MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][17]), 0));
			clientesRow.NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][18]), ""));
			clientesRow.TipoDocumentoID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][19]), "0"));
			clientesRow.NombreApellido = (clientesRow.Nombre + " " + clientesRow.Apellidos).Trim();
			dts.AddClientesRow(clientesRow);
			clientesRow = null;
		}
	}

	public DataTable ToReturnNacionalidades()
	{
		return clsCli.ToReturnNacionalidades();
	}

	public void ReturnAllClientes(ref dtsClientes.ClientesDataTable dts)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsCli.ToReturn();
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsClientes.ClientesRow clientesRow = dts.NewClientesRow();
			clientesRow.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			clientesRow.Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][1]), ""));
			clientesRow.Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][2]), ""));
			clientesRow.Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][3]), 0));
			clientesRow.Sexo = Conversions.ToBoolean(dataTable.Rows[i][4]);
			clientesRow.CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][5]), ""));
			clientesRow.Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), ""));
			clientesRow.Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][7]), ""));
			clientesRow.Correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][8]), ""));
			clientesRow.Cumpleanos = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][9]), null));
			clientesRow.Saldo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][10]), 0));
			clientesRow.Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][11]), 0));
			clientesRow.ReferidoPor = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][12]), 0));
			clientesRow.FacturaCredito = Conversions.ToByte(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][13]), 0));
			clientesRow.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][14]), 0));
			clientesRow.Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][15]), ""));
			clientesRow.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][16]), ""));
			clientesRow.MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][17]), 0));
			clientesRow.NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][18]), ""));
			clientesRow.TipoDocumentoID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][19]), "0"));
			clientesRow.NombreApellido = (clientesRow.Nombre + " " + clientesRow.Apellidos).Trim();
			dts.AddClientesRow(clientesRow);
			clientesRow = null;
		}
	}

	public void ReturnAllClients(ref dtsClientes.ClientesDataTable dts, string byName, string search, string letter, bool Menu)
	{
		DataTable dataTable = new DataTable();
		dataTable = (Menu ? clsCli.SearchClientByNameIni(letter) : ((Operators.CompareString(byName, "", TextCompare: false) == 0) ? clsCli.ToReturn() : clsCli.SearchClientByName(byName, search)));
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsClientes.ClientesRow clientesRow = dts.NewClientesRow();
			clientesRow.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			clientesRow.Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][1]), ""));
			clientesRow.Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][2]), ""));
			clientesRow.Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][3]), 0));
			clientesRow.Sexo = Conversions.ToBoolean(dataTable.Rows[i][4]);
			clientesRow.CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][5]), ""));
			clientesRow.Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), ""));
			clientesRow.Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][7]), ""));
			clientesRow.Correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][8]), ""));
			clientesRow.Cumpleanos = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][9]), null));
			clientesRow.Saldo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][10]), 0));
			clientesRow.Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][11]), 0));
			clientesRow.ReferidoPor = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][12]), 0));
			clientesRow.FacturaCredito = Conversions.ToByte(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][13]), 0));
			clientesRow.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][14]), 0));
			clientesRow.Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][15]), ""));
			clientesRow.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][16]), ""));
			clientesRow.MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][17]), 0));
			clientesRow.NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][18]), ""));
			clientesRow.TipoDocumentoID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][19]), "0"));
			clientesRow.NombreApellido = (clientesRow.Nombre + " " + clientesRow.Apellidos).Trim();
			dts.AddClientesRow(clientesRow);
			clientesRow = null;
		}
	}

	public void ReturnAllClientsParaCaja(ref dtsClientes.ClientesDataTable dts, string byName, string byCI, string letter, bool Menu)
	{
		DataTable dataTable = new DataTable();
		if (Menu)
		{
			dataTable = clsCli.SearchClientByNameIni(letter);
		}
		else if ((Operators.CompareString(byName, "", TextCompare: false) != 0) | (Operators.CompareString(byCI, "", TextCompare: false) != 0))
		{
			clsCli._Nombre = byName;
			clsCli._CI = byCI;
			dataTable = clsCli.SearchClientByNameApellidoCI();
		}
		else
		{
			dataTable = clsCli.ToReturn();
		}
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsClientes.ClientesRow clientesRow = dts.NewClientesRow();
			clientesRow.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			clientesRow.Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][1]), ""));
			clientesRow.Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][2]), ""));
			clientesRow.Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][3]), 0));
			clientesRow.Sexo = Conversions.ToBoolean(dataTable.Rows[i][4]);
			clientesRow.CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][5]), ""));
			clientesRow.Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), ""));
			clientesRow.Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][7]), ""));
			clientesRow.Correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][8]), ""));
			clientesRow.Cumpleanos = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][9]), null));
			clientesRow.Saldo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][10]), 0));
			clientesRow.Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][11]), 0));
			clientesRow.ReferidoPor = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][12]), 0));
			clientesRow.FacturaCredito = Conversions.ToByte(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][13]), 0));
			clientesRow.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][14]), 0));
			clientesRow.Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][15]), ""));
			clientesRow.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][16]), ""));
			clientesRow.MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][17]), 0));
			clientesRow.NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][18]), ""));
			clientesRow.TipoDocumentoID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][19]), "0"));
			clientesRow.NombreApellido = (clientesRow.Nombre + " " + clientesRow.Apellidos).Trim();
			dts.AddClientesRow(clientesRow);
			clientesRow = null;
		}
	}

	public void Save(string Nombre, string Apellidos, int Celular, bool Sexo, string CI, string Nacionalidad, string Comentarios, string correo, DateTime cumple, double Saldo, double Descuento, int ReferidoPor, bool FacturaCredito, bool Activo, string Direccion, string Codigo, double MaxDeuda, string nombreFactura, int tipoDocumentoID)
	{
		clsCli._Nombre = Nombre;
		clsCli._Apellidos = Apellidos;
		clsCli._Celular = Celular;
		clsCli._Sexo = Sexo;
		clsCli._CI = CI;
		clsCli._Nacionalidad = Nacionalidad;
		clsCli._Comentarios = Comentarios;
		clsCli._correo = correo;
		clsCli._cumpleanos = cumple;
		clsCli._Saldo = Saldo;
		clsCli._Descuento = Descuento;
		clsCli._ReferidoPor = ReferidoPor;
		clsCli._FacturaCredito = FacturaCredito;
		clsCli._Activo = Activo;
		clsCli._Direccion = Direccion;
		clsCli._Codigo = Codigo;
		clsCli._MaxDeuda = MaxDeuda;
		clsCli._NombreFactura = nombreFactura;
		clsCli._TipoDocumentoId = tipoDocumentoID;
		if (clsCli._ID == 0)
		{
			clsCli.Insert();
		}
		else
		{
			clsCli.Modify();
		}
	}

	public bool CodigoClienteXLectorExiste(string codigo, string id)
	{
		if (codigo.Length > 0)
		{
			clsCli._Codigo = codigo;
			clsCli._CI = id;
			return clsCli.CodigoClienteXLectorExiste();
		}
		return false;
	}

	public bool CIexiste(string CI)
	{
		if (CI.Length > 0)
		{
			clsCli._CI = CI;
			return clsCli.CIexiste();
		}
		return false;
	}

	public bool CodigoExiste(string codigo)
	{
		if (codigo.Length > 0)
		{
			clsCli._Codigo = codigo;
			return clsCli.CodigoExiste();
		}
		return false;
	}

	public string NombreXci(string CI)
	{
		if (CI.Length > 0)
		{
			clsCli._CI = CI;
			clsCli.NombreXci();
			return clsCli._Nombre + " " + clsCli._Apellidos;
		}
		return "";
	}

	public string NombreFacturaXci(string CI, ref string celular, ref string correo)
	{
		if (CI.Length > 0)
		{
			clsCli._CI = CI;
			clsCli.NombreFacturaXci();
			celular = Conversions.ToString(clsCli._Celular);
			correo = clsCli._correo;
			return clsCli._NombreFactura;
		}
		return "";
	}

	public void Delete()
	{
		clsCli.Delete();
	}

	public void ReporteCuenta(ref DataSet data, string DatNombre, int visitaID)
	{
		clsCli.ReporteCuenta(ref data, DatNombre, visitaID);
	}

	public void ReporteVenta(ref DataSet data, string DatNombre, int visitaID)
	{
		clsCli.ReporteVenta(ref data, DatNombre, visitaID);
	}

	public void ObtenerDatosClientesFactura(ref DateTime fecha, ref string NombreFactura, ref string CI, ref string nombreCliente, int visitaID, ref string telefono, ref string correo, ref int tipoDocumentoID)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsCli.ObtenerDatosClientesFactura(visitaID);
		if (dataTable.Rows.Count > 0)
		{
			fecha = Conversions.ToDate(dataTable.Rows[0]["Fecha"]);
			NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFactura"]), ""));
			CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CI"]), ""));
			nombreCliente = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Cliente"]), ""));
			telefono = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Celular"]), ""));
			correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Correo"]), ""));
			tipoDocumentoID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["tipoDocumentoID"]), 0));
		}
		else
		{
			fecha = DateAndTime.Now;
			NombreFactura = "";
			nombreCliente = "";
			CI = "";
			correo = "";
			telefono = "";
			tipoDocumentoID = 0;
		}
	}

	public void ObtenerDatosNombreFacturaClientesID(ref string NombreFactura, ref string CI, int ClienteID, ref string telefono, ref string correo, ref int tipoDocumentoID)
	{
		DataTable dataTable = new DataTable();
		clsCli._ID = ClienteID;
		dataTable = clsCli.ObtenerDatosNombreFacturaClientesID();
		if (dataTable.Rows.Count > 0)
		{
			NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreFactura"]), ""));
			CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CI"]), ""));
			telefono = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Celular"]), ""));
			correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Correo"]), ""));
			tipoDocumentoID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["tipoDocumentoID"]), 0));
		}
		else
		{
			NombreFactura = "";
			CI = "";
			correo = "";
			telefono = "";
			tipoDocumentoID = 0;
		}
	}

	public void ObtenerDatosNombreClientesID(ref string Nombre, ref string apellido, ref string CI, int ClienteID)
	{
		DataTable dataTable = new DataTable();
		clsCli._ID = ClienteID;
		dataTable = clsCli.ObtenerDatosNombreClientesID();
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), ""));
			apellido = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), ""));
			CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][2]), ""));
		}
		else
		{
			Nombre = "";
			apellido = "";
			CI = "";
		}
	}

	public void ObtenerDatosClientesMolinete(ref DateTime fechaExp, ref string Nombre, ref string apellido, ref string CI, string Codigo)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsCli.ObtenerDatosClientesMolinete(Codigo);
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), ""));
			apellido = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), ""));
			CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][2]), ""));
			clsCli._ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][3]), ""));
		}
		else
		{
			fechaExp = DateAndTime.Now;
			Nombre = "";
			apellido = "";
			CI = "";
			clsCli._ID = 0;
		}
	}

	public void ObtenerDatosClientesCuenta(ref string codigo, ref string mesa, ref DateTime fecha, ref string Nombre, ref string apellido, ref string CI, int visitaID)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsCli.ObtenerDatosClientes1(visitaID);
		if (dataTable.Rows.Count > 0)
		{
			codigo = Conversions.ToString(dataTable.Rows[0][0]);
			mesa = Conversions.ToString(dataTable.Rows[0][1]);
			fecha = Conversions.ToDate(dataTable.Rows[0][2]);
			Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][3]), ""));
			apellido = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][4]), ""));
			CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][5]), ""));
		}
		else
		{
			codigo = Conversions.ToString(0);
			mesa = "";
			fecha = DateAndTime.Now;
			Nombre = "";
			apellido = "";
			CI = "";
		}
	}

	public DataTable BuscarClientes(string codigoCl, string nombre, string apellido, string ci)
	{
		return clsCli.BuscarCliente(codigoCl, nombre, apellido, ci);
	}

	public double DevolverDescuento(int id, ref double Descuento, ref double MinimoMonto, ref double maximoMonto, ref string mensajeCajero)
	{
		clsCli._ID = id;
		clsCli.DevolverDescuento(ref Descuento, ref MinimoMonto, ref maximoMonto, ref mensajeCajero);
		if ((maximoMonto > 0.0) | (MinimoMonto > 0.0))
		{
			return 0.0;
		}
		return Descuento;
	}

	public double DevolverDescuento(int id)
	{
		clsCli._ID = id;
		clsClientes obj = clsCli;
		string mensajeCajero = "";
		double Descuento = default(double);
		double MinimoMonto = default(double);
		double maximoMonto = default(double);
		obj.DevolverDescuento(ref Descuento, ref MinimoMonto, ref maximoMonto, ref mensajeCajero);
		if ((maximoMonto > 0.0) | (MinimoMonto > 0.0))
		{
			return 0.0;
		}
		return Descuento;
	}

	public bool DevolverFacturaCredito(int id)
	{
		clsCli._ID = id;
		return clsCli.DevolverFacturaCredito();
	}

	public void ReturnAllClientesActivos(ref dtsClientes.ClientesDataTable dts, bool habilitado)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsCli.ToReturnActivos(habilitado);
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsClientes.ClientesRow clientesRow = dts.NewClientesRow();
			clientesRow.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			clientesRow.Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][1]), ""));
			clientesRow.Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][2]), ""));
			clientesRow.Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][3]), 0));
			clientesRow.Sexo = Conversions.ToBoolean(dataTable.Rows[i][4]);
			clientesRow.CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][5]), ""));
			clientesRow.Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), ""));
			clientesRow.Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][7]), ""));
			clientesRow.Correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][8]), ""));
			clientesRow.Cumpleanos = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][9]), null));
			clientesRow.Saldo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][10]), 0));
			clientesRow.Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][11]), 0));
			clientesRow.ReferidoPor = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][12]), 0));
			clientesRow.FacturaCredito = Conversions.ToByte(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][13]), 0));
			clientesRow.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][14]), 0));
			clientesRow.Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][15]), ""));
			clientesRow.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][16]), ""));
			clientesRow.MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][17]), 0));
			clientesRow.NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][18]), ""));
			clientesRow.TipoDocumentoID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][19]), "0"));
			clientesRow.NombreApellido = (clientesRow.Nombre + " " + clientesRow.Apellidos).Trim();
			dts.AddClientesRow(clientesRow);
			clientesRow = null;
		}
	}

	public void ReturnAllClientsParaCajaActivos(ref dtsClientes.ClientesDataTable dts, string byName, string byCI, string letter, bool Menu, bool habilitado)
	{
		DataTable dataTable = new DataTable();
		if (Menu)
		{
			dataTable = clsCli.SearchClientByNameIniActivos(letter, habilitado);
		}
		else if ((Operators.CompareString(byName, "", TextCompare: false) != 0) | (Operators.CompareString(byCI, "", TextCompare: false) != 0))
		{
			clsCli._Nombre = byName;
			clsCli._CI = byCI;
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.SaintGeorge)
			{
				clsCli._Nacionalidad = byCI;
				dataTable = clsCli.SearchClientByNameApellidoNacionalidadActivos(habilitado);
			}
			else
			{
				dataTable = clsCli.SearchClientByNameApellidoCIActivos(habilitado);
			}
		}
		else
		{
			dataTable = clsCli.ToReturnActivos(habilitado);
		}
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsClientes.ClientesRow clientesRow = dts.NewClientesRow();
			clientesRow.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			clientesRow.Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][1]), ""));
			clientesRow.Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][2]), ""));
			clientesRow.Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][3]), 0));
			clientesRow.Sexo = Conversions.ToBoolean(dataTable.Rows[i][4]);
			clientesRow.CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][5]), ""));
			clientesRow.Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), ""));
			clientesRow.Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][7]), ""));
			clientesRow.Correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][8]), ""));
			clientesRow.Cumpleanos = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][9]), null));
			clientesRow.Saldo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][10]), 0));
			clientesRow.Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][11]), 0));
			clientesRow.ReferidoPor = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][12]), 0));
			clientesRow.FacturaCredito = Conversions.ToByte(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][13]), 0));
			clientesRow.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][14]), 0));
			clientesRow.Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][15]), ""));
			clientesRow.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][16]), ""));
			clientesRow.MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][17]), 0));
			clientesRow.NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][18]), ""));
			clientesRow.NombreApellido = (clientesRow.Nombre + " " + clientesRow.Apellidos).Trim();
			clientesRow.TipoDocumentoID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][19]), "0"));
			dts.AddClientesRow(clientesRow);
			clientesRow = null;
		}
	}

	public void ReturnAllClientsActivos(ref dtsClientes.ClientesDataTable dts, string byName, string search, string letter, bool Menu, bool habilitado)
	{
		DataTable dataTable = new DataTable();
		dataTable = (Menu ? clsCli.SearchClientByNameIniActivos(letter, habilitado) : ((Operators.CompareString(byName, "", TextCompare: false) == 0) ? clsCli.ToReturnActivos(habilitado) : clsCli.SearchClientByNameActivo(byName, search, habilitado)));
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsClientes.ClientesRow clientesRow = dts.NewClientesRow();
			clientesRow.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			clientesRow.Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][1]), ""));
			clientesRow.Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][2]), ""));
			clientesRow.Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][3]), 0));
			clientesRow.Sexo = Conversions.ToBoolean(dataTable.Rows[i][4]);
			clientesRow.CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][5]), ""));
			clientesRow.Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), ""));
			clientesRow.Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][7]), ""));
			clientesRow.Correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][8]), ""));
			clientesRow.Cumpleanos = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][9]), null));
			clientesRow.Saldo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][10]), 0));
			clientesRow.Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][11]), 0));
			clientesRow.ReferidoPor = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][12]), 0));
			clientesRow.FacturaCredito = Conversions.ToByte(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][13]), 0));
			clientesRow.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][14]), 0));
			clientesRow.Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][15]), ""));
			clientesRow.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][16]), ""));
			clientesRow.MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][17]), 0));
			clientesRow.NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][18]), ""));
			clientesRow.NombreApellido = (clientesRow.Nombre + " " + clientesRow.Apellidos).Trim();
			clientesRow.TipoDocumentoID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][19]), "0"));
			dts.AddClientesRow(clientesRow);
			clientesRow = null;
		}
	}

	public void returnUltimoClienteActivos(ref dtsClientes.ClientesDataTable dts, bool habilitado)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsCli.ToReturnUltimoClienteActivo(habilitado);
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsClientes.ClientesRow clientesRow = dts.NewClientesRow();
			clientesRow.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			clientesRow.Nombre = Conversions.ToString(dataTable.Rows[i][1]);
			clientesRow.Apellidos = Conversions.ToString(dataTable.Rows[i][2]);
			clientesRow.Celular = Conversions.ToInteger(dataTable.Rows[i][3]);
			clientesRow.Sexo = Conversions.ToBoolean(dataTable.Rows[i][4]);
			clientesRow.CI = Conversions.ToString(dataTable.Rows[i][5]);
			clientesRow.Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), ""));
			clientesRow.Comentarios = Conversions.ToString(dataTable.Rows[i][7]);
			clientesRow.Correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][8]), ""));
			clientesRow.Cumpleanos = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][9]), null));
			clientesRow.Saldo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][10]), 0));
			clientesRow.Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][11]), 0));
			clientesRow.ReferidoPor = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][12]), 0));
			clientesRow.FacturaCredito = Conversions.ToByte(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][13]), 0));
			clientesRow.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][14]), 0));
			clientesRow.Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][15]), ""));
			clientesRow.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][16]), ""));
			clientesRow.MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][17]), 0));
			clientesRow.NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][18]), ""));
			clientesRow.NombreApellido = (clientesRow.Nombre + " " + clientesRow.Apellidos).Trim();
			clientesRow.TipoDocumentoID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][19]), "0"));
			dts.AddClientesRow(clientesRow);
			clientesRow = null;
		}
	}

	public void ObtenerDatosClientesNota(ref string NumProv, ref string Direccion, int nit)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsCli.ObtenerDatosClientesNota(nit);
		if (dataTable.Rows.Count > 0)
		{
			NumProv = Conversions.ToString(dataTable.Rows[0][0]);
			Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), ""));
		}
		else
		{
			NumProv = "";
			Direccion = "";
		}
	}

	public DataTable DevolverAlumnosActivosPreimpresos()
	{
		return clsCli.DevolverAlumnosPreImpresos();
	}

	public DataTable DevolverPensionados(DateTime fecha)
	{
		return clsCli.DevolverPensionados(fecha);
	}

	public DataTable DevolverPensionadosMinimos(bool todos)
	{
		return clsCli.DevolverPensionadosMinimos(todos);
	}

	public void ReturnClientesDeudores(ref dtsClientes.ClientesDataTable dts)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsCli.ToReturnClientesDeudores();
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsClientes.ClientesRow clientesRow = dts.NewClientesRow();
			clientesRow.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			clientesRow.Nombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][1]), ""));
			clientesRow.Apellidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][2]), ""));
			clientesRow.Celular = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][3]), 0));
			clientesRow.Sexo = Conversions.ToBoolean(dataTable.Rows[i][4]);
			clientesRow.CI = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][5]), ""));
			clientesRow.Nacionalidad = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), ""));
			clientesRow.Comentarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][7]), ""));
			clientesRow.Correo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][8]), ""));
			clientesRow.Cumpleanos = Conversions.ToDate(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][9]), null));
			clientesRow.Saldo = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][10]), 0));
			clientesRow.Descuento = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][11]), 0));
			clientesRow.ReferidoPor = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][12]), 0));
			clientesRow.FacturaCredito = Conversions.ToByte(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][13]), 0));
			clientesRow.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][14]), 0));
			clientesRow.Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][15]), ""));
			clientesRow.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][16]), ""));
			clientesRow.MaxDeuda = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][17]), 0));
			clientesRow.NombreFactura = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][18]), ""));
			clientesRow.TipoDocumentoID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][19]), "0"));
			clientesRow.NombreApellido = (clientesRow.Nombre + " " + clientesRow.Apellidos).Trim();
			dts.AddClientesRow(clientesRow);
			clientesRow = null;
		}
	}

	public double ToReturnDeudaCliente(int id)
	{
		clsCli._ID = id;
		return clsCli.ToReturnDeudaCliente();
	}

	public void DevolverMaxDeuda(int id, ref double deuda)
	{
		clsCli._ID = id;
		clsCli.DevolverMaxDeuda();
		deuda = clsCli._MaxDeuda;
	}

	public string proximoCodigo()
	{
		return clsCli.proximoCodigo();
	}

	public void getClienteParaLlevar(string ID, ref string NIT, ref string nombre, ref string apellido, ref string telf, ref string direccion)
	{
		clsCli._ID = Conversions.ToInteger(ID);
		clsCli.getClienteParaLlevar();
		nombre = clsCli._Nombre;
		apellido = clsCli._Apellidos;
		direccion = clsCli._Direccion;
		telf = Conversions.ToString(clsCli._Celular);
		NIT = clsCli._CI;
	}

	public void ActualizarNITcelularDireccion(string CI, int telf, string direccion)
	{
		clsCli._CI = CI;
		clsCli._Celular = telf;
		clsCli._Direccion = direccion;
		clsCli.ActualizarNITcelularDireccion();
	}

	public DataTable DevolverClienteXID()
	{
		return clsCli.DevolverClienteXID();
	}
}
