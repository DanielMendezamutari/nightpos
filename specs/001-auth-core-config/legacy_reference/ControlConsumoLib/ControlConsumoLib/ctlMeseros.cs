using System;
using System.Data;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlMeseros
{
	public enum TipoUsuarioID
	{
		Administrador = 1,
		Mesero = 2,
		Cajero = 3,
		Cliente = 4,
		Supervisor = 7,
		Almacenero = 8,
		Contador = 13
	}

	public enum TipoUsuarioPeluqueriaID
	{
		Administrador = 1,
		Peluquero = 2,
		Cajero = 3,
		Manicurista = 10,
		maquilladora = 11,
		Asistente = 12
	}

	public enum TipoUsuarioOtrosID
	{
		Administrador = 1,
		Operador,
		Cajero
	}

	public enum TipoUsuarioLlevarID
	{
		Administrador = 1,
		Mesero = 2,
		Cajero = 3,
		Cliente = 4,
		Repartidor = 5,
		Supervisor = 7,
		Almacenero = 8,
		Contador = 13
	}

	public enum TipoUsuarioCateringID
	{
		Administrador = 1,
		Produccion = 2,
		Secretaria = 3,
		Venta = 9
	}

	private readonly clsMeseros clsMes;

	private readonly clsAsistentes clsAsist;

	public ctlMeseros()
	{
		clsMes = new clsMeseros();
		clsAsist = new clsAsistentes();
	}

	public DataTable devolverTiposUsuariosCatering()
	{
		string[] names = Enum.GetNames(typeof(TipoUsuarioCateringID));
		Array values = Enum.GetValues(typeof(TipoUsuarioCateringID));
		DataTable dataTable = new DataTable();
		dataTable.Columns.Add("Value", typeof(int));
		dataTable.Columns.Add("Key", typeof(string));
		for (int i = 0; i < names.Length; i = checked(i + 1))
		{
			DataRow dataRow = dataTable.NewRow();
			dataRow["Value"] = Conversions.ToInteger(values.GetValue(i));
			dataRow["Key"] = names[i];
			dataTable.Rows.Add(dataRow);
		}
		return dataTable;
	}

	public DataTable devolverTiposUsuariosOtros()
	{
		string[] names = Enum.GetNames(typeof(TipoUsuarioOtrosID));
		Array values = Enum.GetValues(typeof(TipoUsuarioOtrosID));
		DataTable dataTable = new DataTable();
		dataTable.Columns.Add("Value", typeof(int));
		dataTable.Columns.Add("Key", typeof(string));
		for (int i = 0; i < names.Length; i = checked(i + 1))
		{
			DataRow dataRow = dataTable.NewRow();
			dataRow["Value"] = Conversions.ToInteger(values.GetValue(i));
			dataRow["Key"] = names[i];
			dataTable.Rows.Add(dataRow);
		}
		return dataTable;
	}

	public DataTable devolverTiposUsuariosPeluqueria()
	{
		string[] names = Enum.GetNames(typeof(TipoUsuarioPeluqueriaID));
		Array values = Enum.GetValues(typeof(TipoUsuarioPeluqueriaID));
		DataTable dataTable = new DataTable();
		dataTable.Columns.Add("Value", typeof(int));
		dataTable.Columns.Add("Key", typeof(string));
		for (int i = 0; i < names.Length; i = checked(i + 1))
		{
			DataRow dataRow = dataTable.NewRow();
			dataRow["Value"] = Conversions.ToInteger(values.GetValue(i));
			dataRow["Key"] = names[i];
			dataTable.Rows.Add(dataRow);
		}
		return dataTable;
	}

	public DataTable devolverTiposUsuarios()
	{
		string[] names = Enum.GetNames(typeof(TipoUsuarioID));
		Array values = Enum.GetValues(typeof(TipoUsuarioID));
		DataTable dataTable = new DataTable();
		dataTable.Columns.Add("Value", typeof(int));
		dataTable.Columns.Add("Key", typeof(string));
		for (int i = 0; i < names.Length; i = checked(i + 1))
		{
			DataRow dataRow = dataTable.NewRow();
			dataRow["Value"] = Conversions.ToInteger(values.GetValue(i));
			dataRow["Key"] = names[i];
			dataTable.Rows.Add(dataRow);
		}
		return dataTable;
	}

	public DataTable devolverTiposUsuariosLLevar()
	{
		string[] names = Enum.GetNames(typeof(TipoUsuarioLlevarID));
		Array values = Enum.GetValues(typeof(TipoUsuarioLlevarID));
		DataTable dataTable = new DataTable();
		dataTable.Columns.Add("Value", typeof(int));
		dataTable.Columns.Add("Key", typeof(string));
		for (int i = 0; i < names.Length; i = checked(i + 1))
		{
			DataRow dataRow = dataTable.NewRow();
			dataRow["Value"] = Conversions.ToInteger(values.GetValue(i));
			dataRow["Key"] = names[i];
			dataTable.Rows.Add(dataRow);
		}
		return dataTable;
	}

	public bool verificarContrasenha(int Password, ref int MeseroID, ref string Nombre, ref int TipoUsuarioID)
	{
		clsMes._Contrasenha = Password;
		clsMes.verificarContrasenha();
		MeseroID = clsMes._MeseroID;
		Nombre = clsMes._Nombre;
		TipoUsuarioID = clsMes._TipoUsuarioID;
		return clsMes._MeseroID > 0;
	}

	public bool verificarCodigo(string Codigo, ref int MeseroID, ref string Nombre, ref int TipoUsuarioID)
	{
		clsMes._Codigo = Codigo;
		clsMes.verificarCodigo();
		MeseroID = clsMes._MeseroID;
		Nombre = clsMes._Nombre;
		TipoUsuarioID = clsMes._TipoUsuarioID;
		return clsMes._MeseroID > 0;
	}

	public int GetMeseroID()
	{
		return clsMes._MeseroID;
	}

	public int getqtID()
	{
		return clsMes.getQTid();
	}

	public void SetMeseroID(int ID)
	{
		clsMes._MeseroID = ID;
	}

	public clsMeseros LlenarClase()
	{
		clsMes.llenarclase();
		return clsMes;
	}

	public int devolverTelefono()
	{
		clsMes.devolverTelefono();
		return Conversions.ToInteger(clsMes._Telefono);
	}

	public int devolver1erMesero()
	{
		clsMes.devolver1erMesero();
		return clsMes._MeseroID;
	}

	public DataTable devolverMeseros()
	{
		return clsMes.Devolver();
	}

	public DataTable devolverMeserosActivosPorNombre()
	{
		return clsMes.devolverMeserosActivosPorNombre();
	}

	public DataTable devolverMeserosPorNombre()
	{
		return clsMes.devolverMeserosPorNombre();
	}

	public string devolverNombre()
	{
		clsMes.devolverNombre();
		return clsMes._Nombre;
	}

	public void GuardarMesero(string Nombre, string Telefono, string CI, bool Activo, string codigo, string contrasenha, string tipoUsuarioID, bool esAsistente, double porcentaje, DateTime fechaInicio, string email)
	{
		clsMes._Nombre = Nombre;
		clsMes._Telefono = Telefono;
		clsMes._CI = CI;
		clsMes._Activo = Activo;
		clsMes._Codigo = codigo;
		clsMes._Contrasenha = Conversions.ToInteger(contrasenha);
		clsMes._TipoUsuarioID = Conversions.ToInteger(tipoUsuarioID);
		clsMes._Porcentaje = porcentaje;
		clsMes._FechaInicio = fechaInicio;
		clsMes._email = email;
		if (!Activo)
		{
			esAsistente = false;
		}
		if (clsMes._MeseroID == 0)
		{
			clsMes.Insertar();
		}
		else
		{
			clsMes.Modificar();
		}
		if (esAsistente)
		{
			clsAsist._NombreCorto = Nombre;
			clsAsist._NombreCompleto = Nombre;
			clsAsist._ID = clsMes._MeseroID;
			clsAsist.Eliminar();
			clsAsist.Insertar();
		}
		else
		{
			clsAsist._ID = clsMes._MeseroID;
			clsAsist.Eliminar();
		}
	}

	public void EliminarMesero()
	{
		if (clsMes.Eliminar())
		{
			clsAsist._ID = clsMes._MeseroID;
			clsAsist.Eliminar();
		}
	}

	public bool esAsistenteXid()
	{
		clsAsist._ID = clsMes._MeseroID;
		return clsAsist.DevolverXid().Rows.Count > 0;
	}

	public bool hayAsistentes()
	{
		return clsAsist.hayAsistentes();
	}

	public DataTable devolverMotociclistasParaApp()
	{
		return clsMes.devolverMotociclistasParaApp();
	}

	public DataTable devolverMotociclistas()
	{
		return clsMes.DevolverMotociclistas();
	}

	public DataTable devolverMeserosActivos()
	{
		return clsMes.DevolverMeserosActivos();
	}

	public DataTable devolverMeserosInactivos()
	{
		return clsMes.DevolverMeserosInactivos();
	}

	public DataTable devolverMeserosCombo()
	{
		return clsMes.DevolverMeserosActivosCombo();
	}
}
