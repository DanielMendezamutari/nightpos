using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;
using MySql.Data.MySqlClient;

namespace ControlConsumoLib;

[StandardModule]
public sealed class BD_Mysql
{
	private static MySqlConnection Cnx;

	private static string s;

	private static string bd;

	private static string SOURCE;

	private static string UID;

	private static string PWD;

	private static int PORT;

	public static int erroresCount;

	private const int default_command_timeout = 30000;

	private static string Line;

	public static void initialize()
	{
		configuration.styleBolichesId gStyleBoliches = configuration.gStyleBoliches1;
		bd = "ToptechDataBase";
		SOURCE = "roundhouse.proxy.rlwy.net";
		UID = "root";
		PWD = "fzuxNYtbGRFuYuCbhaBRugFIkdCMCUNN";
		PORT = 48681;
	}

	static BD_Mysql()
	{
		initialize();
	}

	public static string getConnecionString()
	{
		return s;
	}

	private static string getBDname()
	{
		return bd;
	}

	public static void ChangeBD(string name, string SOURCE1, string UID1, string PWD1, int PORT1)
	{
		bd = name;
		SOURCE = SOURCE1;
		UID = UID1;
		PWD = PWD1;
		PORT = PORT1;
	}

	public static DataTable RealizarConsulta(string Cadena)
	{
		new DataTable();
		return ExecuteQueryDataTable1(Cadena);
	}

	private static DataTable ExecuteQueryDataTable1(string strRealizarConsult)
	{
		DataTable result;
		try
		{
			if (strRealizarConsult.Contains("top 1"))
			{
				strRealizarConsult = strRealizarConsult.Replace("top 1", "");
				strRealizarConsult += " limit 1";
			}
			strRealizarConsult = strRealizarConsult.Replace("varchar", "char");
			s = "Data Source=" + SOURCE + ";uid=" + UID + ";Password=" + PWD + ";Database=" + bd + ";default command timeout=" + Conversions.ToString(30000) + ";Port=" + Conversions.ToString(PORT) + ";character set=utf8;Connection Timeout=10;TreatTinyAsBoolean=true;SslMode=0";
			DataSet dataSet = new DataSet();
			using (MySqlConnection mySqlConnection = new MySqlConnection(s))
			{
				using MySqlCommand mySqlCommand = new MySqlCommand(strRealizarConsult);
				mySqlConnection.Open();
				mySqlCommand.Connection = mySqlConnection;
				new MySqlDataAdapter(mySqlCommand).Fill(dataSet, "DT" + Conversions.ToString(DateAndTime.Now.Hour) + Conversions.ToString(DateAndTime.Now.Minute) + Conversions.ToString(DateAndTime.Now.Second) + Conversions.ToString(DateAndTime.Now.Millisecond));
				mySqlCommand.Dispose();
				mySqlConnection.Close();
				mySqlConnection.Dispose();
			}
			result = dataSet.Tables[0];
		}
		catch (MySqlException ex)
		{
			ProjectData.SetProjectError(ex);
			MySqlException ex2 = ex;
			Interaction.MsgBox("MySQL Error: " + ex2.Message);
			Interaction.MsgBox("Error Code: " + Conversions.ToString(ex2.Number));
			if (ex2.InnerException != null)
			{
				Interaction.MsgBox("Inner Exception: " + ex2.InnerException.Message);
			}
			result = null;
			ProjectData.ClearProjectError();
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			Interaction.MsgBox("General Error: " + ex4.Message);
			if (ex4.InnerException != null)
			{
				Interaction.MsgBox("Inner Exception: " + ex4.InnerException.Message);
			}
			result = null;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	private static DataSet ExecuteQueryDataSet(string strRealizarConsult)
	{
		DataSet result;
		try
		{
			s = "Data Source=" + SOURCE + ";uid=" + UID + ";Password=" + PWD + ";Database=" + bd + ";default command timeout=" + Conversions.ToString(30000) + ";Port=" + Conversions.ToString(PORT) + ";character set=utf8;Connection Timeout=10;SslMode=0;";
			DataSet dataSet = new DataSet();
			using (MySqlConnection mySqlConnection = new MySqlConnection(s))
			{
				using MySqlCommand mySqlCommand = new MySqlCommand(strRealizarConsult);
				mySqlConnection.Open();
				mySqlCommand.Connection = mySqlConnection;
				new MySqlDataAdapter(mySqlCommand).Fill(dataSet, "NuevaTabla");
				mySqlCommand.Dispose();
				mySqlConnection.Close();
				mySqlConnection.Dispose();
			}
			result = dataSet;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message);
			result = new DataSet();
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public static int ExecuteQuerytInsert(string strrealizarConsulta, ref int ID, ref string error2)
	{
		int result;
		try
		{
			s = "Data Source=" + SOURCE + ";uid=" + UID + ";Password=" + PWD + ";Database=" + bd + ";default command timeout=" + Conversions.ToString(30000) + ";Port=" + Conversions.ToString(PORT) + ";character set=utf8;Connection Timeout=10;SslMode=0;";
			using MySqlConnection mySqlConnection = new MySqlConnection(s);
			using MySqlCommand mySqlCommand = new MySqlCommand(strrealizarConsulta);
			mySqlConnection.Open();
			mySqlCommand.Connection = mySqlConnection;
			mySqlCommand.ExecuteNonQuery();
			mySqlCommand.CommandText = "SELECT LAST_INSERT_ID()";
			ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(mySqlCommand.ExecuteScalar()), 0));
			mySqlConnection.Close();
			mySqlConnection.Dispose();
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			error2 = ex2.Message;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public static int ExecuteQuerytAlter(string strrealizarConsult)
	{
		int result;
		try
		{
			s = "Data Source=" + SOURCE + ";uid=" + UID + ";Password=" + PWD + ";Database=" + bd + ";default command timeout=" + Conversions.ToString(30000) + ";Port=" + Conversions.ToString(PORT) + ";character set=utf8;Connection Timeout=10;SslMode=0;";
			using MySqlConnection mySqlConnection = new MySqlConnection(s);
			using MySqlCommand mySqlCommand = new MySqlCommand(strrealizarConsult);
			mySqlConnection.Open();
			mySqlCommand.Connection = mySqlConnection;
			mySqlCommand.ExecuteNonQuery();
			mySqlConnection.Close();
			mySqlConnection.Dispose();
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			Interaction.MsgBox("mysql alter-" + ex2.Message);
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public static int ConsultWithOutAlerts(string strrealizarConsult)
	{
		int result;
		try
		{
			s = "Data Source=" + SOURCE + ";uid=" + UID + ";Password=" + PWD + ";Database=" + bd + ";default command timeout=" + Conversions.ToString(30000) + ";Port=" + Conversions.ToString(PORT) + ";character set=utf8;Connection Timeout=10;SslMode=0;";
			new DataSet();
			using MySqlConnection mySqlConnection = new MySqlConnection(s);
			using MySqlCommand mySqlCommand = new MySqlCommand(strrealizarConsult);
			mySqlConnection.Open();
			mySqlCommand.Connection = mySqlConnection;
			mySqlCommand.ExecuteNonQuery();
			mySqlConnection.Close();
			mySqlConnection.Dispose();
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

	public static DataTable ExecuteQueryDataTableWithOutAlerts(string strRealizarConsult)
	{
		DataTable result;
		try
		{
			s = "Data Source=" + SOURCE + ";uid=" + UID + ";Password=" + PWD + ";Database=" + bd + ";default command timeout=" + Conversions.ToString(30000) + ";Port=" + Conversions.ToString(PORT) + ";character set=utf8;Connection Timeout=10;TreatTinyAsBoolean=true;SslMode=0;";
			DataSet dataSet = new DataSet();
			using (MySqlConnection mySqlConnection = new MySqlConnection(s))
			{
				using MySqlCommand mySqlCommand = new MySqlCommand(strRealizarConsult);
				mySqlConnection.Open();
				mySqlCommand.Connection = mySqlConnection;
				new MySqlDataAdapter(mySqlCommand).Fill(dataSet, "DT" + Conversions.ToString(DateAndTime.Now.Hour) + Conversions.ToString(DateAndTime.Now.Minute) + Conversions.ToString(DateAndTime.Now.Second) + Conversions.ToString(DateAndTime.Now.Millisecond));
				mySqlCommand.Dispose();
				mySqlConnection.Close();
				mySqlConnection.Dispose();
			}
			result = dataSet.Tables[0];
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = new DataTable();
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public static void UpdateDatasetToMysqlTable(DataSet myds, string TableName)
	{
		string connectionString = s;
		string cmdText = "Select * from " + TableName + ";";
		MySqlConnection mySqlConnection = new MySqlConnection(connectionString);
		try
		{
			MySqlDataAdapter mySqlDataAdapter = new MySqlDataAdapter();
			mySqlDataAdapter.SelectCommand = new MySqlCommand(cmdText, mySqlConnection);
			new MySqlCommandBuilder(mySqlDataAdapter).ConflictOption = ConflictOption.OverwriteChanges;
			mySqlDataAdapter.Update(myds);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Console.WriteLine("Error: " + Convert.ToString(ex2));
			ProjectData.ClearProjectError();
		}
		finally
		{
			mySqlConnection.Close();
		}
	}

	public static void EjecutarRealizarConsultaDataSet(ref DataSet myds, string strRealizarConsulta, string TableName)
	{
		try
		{
			s = "Data Source=" + SOURCE + ";uid=" + UID + ";Password=" + PWD + ";Database=" + bd + ";default command timeout=" + Conversions.ToString(30000) + ";Port=" + Conversions.ToString(PORT) + ";character set=utf8;Connection Timeout=10;SslMode=0;";
			using MySqlConnection mySqlConnection = new MySqlConnection(s);
			using MySqlCommand mySqlCommand = new MySqlCommand(strRealizarConsulta);
			mySqlConnection.Open();
			mySqlCommand.Connection = mySqlConnection;
			new MySqlDataAdapter(mySqlCommand).Fill(myds, TableName);
			mySqlCommand.Dispose();
			mySqlConnection.Close();
			mySqlConnection.Dispose();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message);
			ProjectData.ClearProjectError();
		}
	}

	public static bool ConsultaInsertarTemplate(object template, int clienteId)
	{
		int num;
		try
		{
			s = "Data Source=" + SOURCE + ";uid=" + UID + ";Password=" + PWD + ";Database=" + bd + ";default command timeout=" + Conversions.ToString(30000) + ";Port=" + Conversions.ToString(PORT) + ";character set=utf8;Connection Timeout=10;SslMode=0;";
			using MySqlConnection mySqlConnection = new MySqlConnection(s);
			using MySqlCommand mySqlCommand = new MySqlCommand("Replace INTO ClientesHuellas set clienteId=@ClienteId, Huella=@Huella");
			mySqlConnection.Open();
			mySqlCommand.Connection = mySqlConnection;
			mySqlCommand.Parameters.Add(new MySqlParameter("@ClienteId", clienteId));
			mySqlCommand.Parameters.Add(new MySqlParameter("@Huella", RuntimeHelpers.GetObjectValue(NewLateBinding.LateGet(template, null, "Bytes", new object[0], null, null, null))));
			mySqlCommand.ExecuteNonQuery();
			mySqlConnection.Close();
			mySqlConnection.Dispose();
			num = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			num = 0;
			Interaction.MsgBox(ex2.Message);
			ProjectData.ClearProjectError();
		}
		return num != 0;
	}

	public static int ConsultaInsertar(string Datos, string tabla, ref int Id, ref string error2)
	{
		Line = "insert into " + tabla + " values ( " + Datos + ")";
		return ExecuteQuerytInsert(Line, ref Id, ref error2);
	}

	public static int ConsultaModificar(string tabla, string valor1, string from, string restriccion)
	{
		Line = "UPDATE  " + tabla + " SET " + valor1 + " FROM " + from + " WHERE " + restriccion;
		return ExecuteQuerytAlter(Line);
	}

	public static int ConsultaModificar(string tabla, string valor1, string restriccion)
	{
		Line = "UPDATE  " + tabla + " SET " + valor1 + " WHERE " + restriccion;
		return ExecuteQuerytAlter(Line);
	}

	public static int ConsultaModificar(string tabla, string valor1)
	{
		Line = "UPDATE  " + tabla + " SET " + valor1;
		return ExecuteQuerytAlter(Line);
	}

	public static int ConsultaEliminar(string tabla, string restriccion)
	{
		Line = "delete from " + tabla + " where " + restriccion;
		return ExecuteQuerytAlter(Line);
	}

	public static DataTable ConsultaVer(string datos, string tabla, string restriccion, string orderBy, string groupBy)
	{
		if ((restriccion.Length > 0) & (orderBy.Length > 0) & (groupBy.Length > 0))
		{
			Line = "select " + datos + "  from " + tabla + " where " + restriccion + " group by " + groupBy + " order by " + orderBy;
		}
		else if ((restriccion.Length > 0) & (orderBy.Length == 0) & (groupBy.Length > 0))
		{
			Line = "select " + datos + "  from " + tabla + " where " + restriccion + " group by " + groupBy;
		}
		else if ((restriccion.Length == 0) & (orderBy.Length > 0) & (groupBy.Length > 0))
		{
			Line = "select " + datos + "  from " + tabla + " group by " + groupBy + " order by " + orderBy;
		}
		else if ((restriccion.Length == 0) & (orderBy.Length == 0) & (groupBy.Length > 0))
		{
			Line = "select " + datos + "  from " + tabla + " group by " + groupBy;
		}
		return RealizarConsulta(Line);
	}

	public static DataTable ConsultaVer(string datos, string tabla, string restriccion, string orderBy)
	{
		if (restriccion.Length == 0)
		{
			Line = "select " + datos + "  from " + tabla + " order by " + orderBy;
		}
		else
		{
			Line = "select " + datos + "  from " + tabla + " where " + restriccion + " order by " + orderBy;
		}
		return RealizarConsulta(Line);
	}

	public static DataTable ConsultaVer(string datos, string tabla, string restriccion)
	{
		Line = "select " + datos + "  from " + tabla + " where " + restriccion;
		return RealizarConsulta(Line);
	}

	public static DataTable ConsultaVer(string datos, string tabla)
	{
		Line = "select " + datos + "  from " + tabla;
		return RealizarConsulta(Line);
	}

	public static DataTable ConsultaVer(string datos)
	{
		Line = datos;
		return RealizarConsulta(Line);
	}

	public static void ConsultaVerDataSet(ref DataSet dtsAux1, string query, string Nombretabla)
	{
		EjecutarRealizarConsultaDataSet(ref dtsAux1, query, Nombretabla);
	}
}
