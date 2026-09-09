using System;
using System.Data;
using System.Data.OleDb;
using System.Data.SqlClient;
using System.Drawing;
using System.Runtime.CompilerServices;
using System.Runtime.InteropServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class BD_SQL
{
	private SqlConnection Cnx;

	private string s;

	private readonly string Name;

	private string bd;

	public int veces;

	public BD_SQL()
	{
		Name = MyProject.Computer.Name;
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Toptech)
		{
			Name = "ToptechFact";
		}
		switch (Name)
		{
		case "ALEJANDRO-HOME":
			if (configuration.gModo_Remoto)
			{
				Name = configuration._PublicIP;
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=" + configuration.db_user + ";Password=" + configuration.db_file_password + ";";
			}
			else if (Operators.CompareString(configuration.db_user, "", TextCompare: false) == 0)
			{
				s = "workstation id=" + configuration.db_instance + ";packet size=4096;data source=" + configuration.db_instance + ";persist security info=False;initial catalog=" + configuration.db_file + ";";
			}
			else
			{
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=sa;Password=toptech;";
			}
			break;
		case "ALEJANDRO-ALIEN":
			if (configuration.gModo_Remoto)
			{
				Name = configuration._PublicIP;
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=" + configuration.db_user + ";Password=" + configuration.db_file_password + ";";
			}
			else if (Operators.CompareString(configuration.db_user, "", TextCompare: false) == 0)
			{
				s = "workstation id=" + configuration.db_instance + ";packet size=4096;data source=" + configuration.db_instance + ";persist security info=False;initial catalog=" + configuration.db_file + ";";
			}
			else
			{
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=sa;Password=toptech;";
			}
			break;
		case "ALEJANDRO-HP":
			Name = "ALEJANDRO-HP\\SQLEXPRESS";
			if (configuration.gModo_Remoto)
			{
				Name = configuration._PublicIP;
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=" + configuration.db_user + ";Password=" + configuration.db_file_password + ";";
			}
			else
			{
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=sa;Password=toptech;";
			}
			break;
		case "ALEJANDRO-DELL":
			Name = "ALEJANDRO-DELL\\SQLEXPRESS2022";
			if (configuration.gModo_Remoto)
			{
				Name = configuration._PublicIP;
				Interaction.MsgBox("Estoy probando con remoto");
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=" + configuration.db_user + ";Password=" + configuration.db_file_password + ";";
			}
			else
			{
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=sa;Password=toptech;";
			}
			break;
		case "SRV-TOPTECH":
			Name = "SRV-TOPTECH\\SQLEXPRESS";
			if (configuration.gModo_Remoto)
			{
				Interaction.MsgBox("Estoy probando con remoto");
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=" + configuration.db_user + ";Password=" + configuration.db_file_password + ";";
			}
			else
			{
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=sa;Password=toptech;";
			}
			break;
		case "TOPTECH":
			Name = "TOPTECH\\SQLEXPRESS2019";
			if (configuration.gModo_Remoto)
			{
				Interaction.MsgBox("Estoy probando con remoto");
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=" + configuration.db_user + ";Password=" + configuration.db_file_password + ";";
			}
			else
			{
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=sa;Password=toptech;";
			}
			break;
		default:
			if (configuration.styleBolichesId.Restomenu == configuration.gStyleBoliches1)
			{
				s = configuration._PublicIP;
			}
			else if (configuration.gModo_Remoto | (configuration.styleBolichesId.Soboce == configuration.gStyleBoliches1) | (configuration.styleBolichesId.KIKY == configuration.gStyleBoliches1))
			{
				Name = configuration._PublicIP;
				s = "workstation id=" + Name + ";packet size=4096;data source=" + Name + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=" + configuration.db_user + ";Password=" + configuration.db_file_password + ";";
			}
			else
			{
				s = "workstation id=" + configuration.db_instance + ";packet size=4096;data source=" + configuration.db_instance + ";persist security info=False;initial catalog=" + configuration.db_file + ";User Id=" + configuration.db_user + ";Password=" + configuration.db_file_password + ";";
			}
			break;
		}
	}

	private bool Conectar([Optional][DefaultParameterValue("")] ref string error1)
	{
		Cnx = new SqlConnection();
		Cnx.ConnectionString = s;
		return Abrir(ref error1);
	}

	public void setSQLs(object s1)
	{
		s = Conversions.ToString(s1);
	}

	public string getSQLs()
	{
		return s;
	}

	public bool testConectar(ref string error1)
	{
		string error2 = error1 + "\r\n" + s.Replace(configuration.db_file_password, "*****");
		return Conectar(ref error2);
	}

	private bool Abrir([Optional][DefaultParameterValue("")] ref string error1)
	{
		bool result;
		try
		{
			Cnx.Open();
			error1 = "";
			result = true;
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

	private void Cerrar()
	{
		try
		{
			Cnx.Close();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
		Cnx.Dispose();
	}

	public int ConsultWithOutAlerts(string strrealizarConsult)
	{
		string error = "";
		Conectar(ref error);
		SqlCommand sqlCommand = new SqlCommand(strrealizarConsult, Cnx);
		int result;
		try
		{
			sqlCommand.ExecuteNonQuery();
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		sqlCommand.Dispose();
		Cerrar();
		return result;
	}

	public DataTable ConsultQueryWithOutAlerts(string strrealizarConsult, ref string error1)
	{
		DataSet dataSet = new DataSet();
		DataTable result;
		if (!Conectar(ref error1))
		{
			error1 = "No conecto";
			result = dataSet.Tables[0];
		}
		else
		{
			SqlCommand sqlCommand = new SqlCommand(strrealizarConsult, Cnx);
			try
			{
				new SqlDataAdapter(sqlCommand).Fill(dataSet, "NuevaTabla");
				sqlCommand.Dispose();
				Cerrar();
				error1 = "";
				result = dataSet.Tables[0];
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				error1 = ex2.Message;
				result = dataSet.Tables[0];
				ProjectData.ClearProjectError();
			}
		}
		return result;
	}

	public DataTable RealizarConsulta(string Cadena)
	{
		new DataTable();
		string error = "";
		Conectar(ref error);
		DataTable result = EjecutarRealizarConsulta(Cadena);
		Cerrar();
		return result;
	}

	private DataTable EjecutarRealizarConsulta(string strRealizarConsulta)
	{
		DataSet dataSet = new DataSet();
		DataTable result;
		try
		{
			SqlCommand sqlCommand = new SqlCommand(strRealizarConsulta, Cnx);
			sqlCommand.CommandTimeout = 60;
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Ottimo)
			{
				sqlCommand.CommandTimeout = 190;
			}
			new SqlDataAdapter(sqlCommand).Fill(dataSet, "NuevaTabla");
			sqlCommand.Dispose();
			result = dataSet.Tables[0];
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox("bd realizar consulta - " + ex2.Message);
			result = new DataTable();
			ProjectData.ClearProjectError();
		}
		return result;
	}

	private void RealizarConsultaDataSet(ref DataSet dtsAux1, string Cadena, string nombre)
	{
		string error = "";
		Conectar(ref error);
		EjecutarRealizarConsultaDataSet(ref dtsAux1, Cadena, nombre);
		Cerrar();
	}

	private void EjecutarRealizarConsultaDataSet(ref DataSet dtsAux1, string strRealizarConsulta, string nombre)
	{
		try
		{
			SqlCommand sqlCommand = new SqlCommand(strRealizarConsulta, Cnx);
			new SqlDataAdapter(sqlCommand).Fill(dtsAux1, nombre);
			sqlCommand.Dispose();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox("bd dataset - " + ex2.Message);
			ProjectData.ClearProjectError();
		}
	}

	public int realizarConsultaAlteraciones(string strrealizarConsulta)
	{
		string error = "";
		Conectar(ref error);
		SqlCommand sqlCommand = new SqlCommand(strrealizarConsulta, Cnx);
		int result;
		try
		{
			result = sqlCommand.ExecuteNonQuery();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox("bd alter - " + ex2.Message);
			result = -1;
			ProjectData.ClearProjectError();
		}
		sqlCommand.Dispose();
		Cerrar();
		return result;
	}

	public int realizarConsultaInsert(string strrealizarConsulta, ref int ID)
	{
		string error = "";
		Conectar(ref error);
		SqlCommand sqlCommand = new SqlCommand(strrealizarConsulta, Cnx);
		int result;
		try
		{
			sqlCommand.ExecuteNonQuery();
			sqlCommand.CommandText = "SELECT SCOPE_IDENTITY()";
			ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(sqlCommand.ExecuteScalar()), 0));
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox("bd insert - " + ex2.Message);
			ID = 0;
			result = 0;
			ProjectData.ClearProjectError();
		}
		sqlCommand.Dispose();
		Cerrar();
		return result;
	}

	public int realizarConsultaEliminar(string strrealizarConsulta)
	{
		string error = "";
		Conectar(ref error);
		SqlCommand sqlCommand = new SqlCommand(strrealizarConsulta, Cnx);
		int result;
		try
		{
			sqlCommand.ExecuteNonQuery();
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		sqlCommand.Dispose();
		Cerrar();
		return result;
	}

	public int ConsultaInsertar(string Datos, string tabla, ref int Id)
	{
		string strrealizarConsulta = "insert into " + tabla + " values ( " + Datos + ")";
		return realizarConsultaInsert(strrealizarConsulta, ref Id);
	}

	public int ConsultaInsertarQuery(string query)
	{
		return realizarConsultaAlteraciones(query);
	}

	public int ConsultaModificar(string tabla, string valor1, string from, string restriccion)
	{
		string strrealizarConsulta = "UPDATE  " + tabla + " SET " + valor1 + " FROM " + from + " WHERE " + restriccion;
		return realizarConsultaAlteraciones(strrealizarConsulta);
	}

	public int ConsultaModificar(string tabla, string valor1, string restriccion)
	{
		string strrealizarConsulta = "UPDATE  " + tabla + " SET " + valor1 + " WHERE " + restriccion;
		return realizarConsultaAlteraciones(strrealizarConsulta);
	}

	public int ConsultaModificar(string tabla, string valor1)
	{
		string strrealizarConsulta = "UPDATE  " + tabla + " SET " + valor1;
		return realizarConsultaAlteraciones(strrealizarConsulta);
	}

	public int ConsultaEliminar(string tabla, string restriccion)
	{
		string strrealizarConsulta = "delete from " + tabla + " where " + restriccion;
		return realizarConsultaEliminar(strrealizarConsulta);
	}

	public DataTable ConsultaVer(string datos, string tabla, string restriccion, string orderBy, string groupBy)
	{
		string cadena = "";
		if ((restriccion.Length > 0) & (orderBy.Length > 0) & (groupBy.Length > 0))
		{
			cadena = "select " + datos + "  from " + tabla + " where " + restriccion + " group by " + groupBy + " order by " + orderBy;
		}
		else if ((restriccion.Length > 0) & (orderBy.Length == 0) & (groupBy.Length > 0))
		{
			cadena = "select " + datos + "  from " + tabla + " where " + restriccion + " group by " + groupBy;
		}
		else if ((restriccion.Length == 0) & (orderBy.Length > 0) & (groupBy.Length > 0))
		{
			cadena = "select " + datos + "  from " + tabla + " group by " + groupBy + " order by " + orderBy;
		}
		else if ((restriccion.Length == 0) & (orderBy.Length == 0) & (groupBy.Length > 0))
		{
			cadena = "select " + datos + "  from " + tabla + " group by " + groupBy;
		}
		return RealizarConsulta(cadena);
	}

	public DataTable ConsultaVer(string datos, string tabla, string restriccion, string orderBy)
	{
		string text = "";
		text = ((restriccion.Length != 0) ? ("select " + datos + "  from " + tabla + " where " + restriccion + " order by " + orderBy) : ("select " + datos + "  from " + tabla + " order by " + orderBy));
		return RealizarConsulta(text);
	}

	public DataTable ConsultaVer(string datos, string tabla, string restriccion)
	{
		string cadena = "select " + datos + "  from " + tabla + " where " + restriccion;
		return RealizarConsulta(cadena);
	}

	public DataTable ConsultaVer(string datos, string tabla)
	{
		string cadena = "select " + datos + "  from " + tabla;
		return RealizarConsulta(cadena);
	}

	public DataTable ConsultaVerSinAlertas(string datos, string tabla, string restriccion, ref string error1)
	{
		string strrealizarConsult = "select " + datos + "  from " + tabla + " where " + restriccion;
		return ConsultQueryWithOutAlerts(strrealizarConsult, ref error1);
	}

	public DataTable ConsultaVerSinAlertas(string datos, string tabla, string restriccion, string groupby, ref string error1)
	{
		string strrealizarConsult = "select " + datos + "  from " + tabla + " where " + restriccion + " group by " + groupby;
		return ConsultQueryWithOutAlerts(strrealizarConsult, ref error1);
	}

	public DataTable ConsultaVerSinAlertas(string datos, string tabla, string restriccion, string groupby, string orderby, ref string error1)
	{
		string strrealizarConsult = ((groupby.Length <= 0) ? ("select " + datos + "  from " + tabla + " where " + restriccion + " order by " + orderby) : ("select " + datos + "  from " + tabla + " where " + restriccion + " group by " + groupby + " order by " + orderby));
		return ConsultQueryWithOutAlerts(strrealizarConsult, ref error1);
	}

	public DataTable ConsultaVer(string datos)
	{
		return RealizarConsulta(datos);
	}

	public void ConsultaVerDataSet(ref DataSet dtsAux1, string query, string Nombretabla)
	{
		RealizarConsultaDataSet(ref dtsAux1, query, Nombretabla);
	}

	public void ConsultaHacerProcedAlmacenado(string NombreProcedimiento)
	{
		realizarConsultaAlteraciones(NombreProcedimiento);
	}

	public DataTable ConsultaProcedAlmacenado(string NombreProcedimiento, string datos)
	{
		string cadena = NombreProcedimiento + " " + datos;
		return RealizarConsulta(cadena);
	}

	public void realizarConsultaModificarFoto(string NombreProcedimiento, int ID, Image Foto)
	{
		if (Foto != null)
		{
			OleDbConnection oleDbConnection = new OleDbConnection();
			oleDbConnection.ConnectionString = "Provider= SQLOLEDB;" + s;
			oleDbConnection.Open();
			OleDbCommand oleDbCommand = new OleDbCommand(NombreProcedimiento, oleDbConnection);
			oleDbCommand.CommandType = CommandType.StoredProcedure;
			OleDbParameter value = new OleDbParameter("@ID", ID);
			OleDbParameter value2 = new OleDbParameter("@Foto", SqlDbType.Image)
			{
				Value = TrabajarConImagenes.Image2Bytes(Foto)
			};
			oleDbCommand.Parameters.Add(value);
			oleDbCommand.Parameters.Add(value2);
			oleDbCommand.ExecuteNonQuery();
			oleDbCommand.Dispose();
			oleDbConnection.Close();
		}
	}
}
