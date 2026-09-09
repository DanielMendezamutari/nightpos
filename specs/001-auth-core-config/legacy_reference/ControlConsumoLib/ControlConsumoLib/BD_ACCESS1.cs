using System;
using System.Data;
using System.Data.OleDb;
using System.Runtime.CompilerServices;
using System.Windows.Forms;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class BD_ACCESS1
{
	private OleDbConnection Cnx1;

	private string s;

	private void Connect(string db = "", string psw = "")
	{
		string text = configuration.db_file;
		if (Operators.CompareString(db, "", TextCompare: false) != 0)
		{
			text = db;
		}
		string text2 = configuration.db_file_password;
		if (Operators.CompareString(psw, "", TextCompare: false) != 0)
		{
			text2 = psw;
		}
		if (text.Contains(":"))
		{
			if (text2.Length == 0)
			{
				s = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" + text;
			}
			else
			{
				s = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" + text + ";Jet OLEDB:Database Password=" + text2;
			}
		}
		else if (text2.Length == 0)
		{
			s = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" + Application.StartupPath + "\\" + text;
		}
		else
		{
			s = "Provider=Microsoft.Jet.OLEDB.4.0;Data Source=" + Application.StartupPath + "\\" + text + ";Jet OLEDB:Database Password=" + text2;
		}
		Cnx1 = new OleDbConnection(s);
		Open();
	}

	private void Open()
	{
		try
		{
			Cnx1.Open();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message);
			ProjectData.ClearProjectError();
		}
	}

	private void Close()
	{
		try
		{
			Cnx1.Close();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message);
			ProjectData.ClearProjectError();
		}
		Cnx1.Dispose();
	}

	private void queryDataSet(ref DataSet data, string Cadena, string nombre, string db)
	{
		if (Operators.CompareString(db, "", TextCompare: false) == 0)
		{
			Connect();
		}
		else
		{
			Connect(db);
		}
		ExecuteQueryDataSet(ref data, Cadena, nombre);
		Close();
	}

	private DataTable queryDataTable(string Cadena, string db, string pwd)
	{
		new DataTable();
		if (Operators.CompareString(db, "", TextCompare: false) == 0)
		{
			Connect();
		}
		else
		{
			Connect(db, pwd);
		}
		DataTable result = ExecuteQueryDataTable(Cadena);
		Close();
		return result;
	}

	private DataTable ConsultaVerSinAlertas(string Cadena, string db)
	{
		new DataTable();
		if (Operators.CompareString(db, "", TextCompare: false) == 0)
		{
			Connect();
		}
		else
		{
			Connect(db);
		}
		DataTable result = ExecuteQueryDataTableSinAlertas(Cadena);
		Close();
		return result;
	}

	private DataTable ExecuteQueryDataTable(string strRealizarConsult)
	{
		DataTable result;
		try
		{
			DataSet dataSet = new DataSet();
			OleDbCommand oleDbCommand = new OleDbCommand(strRealizarConsult, Cnx1);
			new OleDbDataAdapter(oleDbCommand).Fill(dataSet, "NuevaTabla");
			oleDbCommand.Dispose();
			Close();
			result = dataSet.Tables[0];
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message);
			result = new DataTable();
			ProjectData.ClearProjectError();
		}
		return result;
	}

	private DataTable ExecuteQueryDataTableSinAlertas(string strRealizarConsult)
	{
		DataTable result;
		try
		{
			DataSet dataSet = new DataSet();
			OleDbCommand oleDbCommand = new OleDbCommand(strRealizarConsult, Cnx1);
			new OleDbDataAdapter(oleDbCommand).Fill(dataSet, "NuevaTabla");
			oleDbCommand.Dispose();
			Close();
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

	private void ExecuteQueryDataSet(ref DataSet data, string strRealizarConsult, string nombre)
	{
		OleDbCommand oleDbCommand = new OleDbCommand(strRealizarConsult, Cnx1);
		new OleDbDataAdapter(oleDbCommand).Fill(data, nombre);
		oleDbCommand.Dispose();
	}

	private void ExecuteQueryDataSet2(ref DataSet dts, string strRealizarConsult, string db)
	{
		if (Operators.CompareString(db, "", TextCompare: false) == 0)
		{
			Connect();
		}
		else
		{
			Connect(db);
		}
		OleDbCommand oleDbCommand = new OleDbCommand(strRealizarConsult, Cnx1);
		new OleDbDataAdapter(oleDbCommand).Fill(dts);
		oleDbCommand.Dispose();
		Close();
	}

	public int ExecuteQuerytAlter(string strrealizarConsult, string db)
	{
		if (Operators.CompareString(db, "", TextCompare: false) == 0)
		{
			Connect();
		}
		else
		{
			Connect(db);
		}
		OleDbCommand oleDbCommand = new OleDbCommand(strrealizarConsult, Cnx1);
		int result;
		try
		{
			oleDbCommand.ExecuteNonQuery();
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message + "\r" + strrealizarConsult);
			result = 0;
			ProjectData.ClearProjectError();
		}
		oleDbCommand.Dispose();
		Close();
		return result;
	}

	public int ExecuteQuerytInsert(string strrealizarConsult, string db, ref int ID)
	{
		if (Operators.CompareString(db, "", TextCompare: false) == 0)
		{
			Connect();
		}
		else
		{
			Connect(db);
		}
		OleDbCommand oleDbCommand = new OleDbCommand(strrealizarConsult, Cnx1);
		int result;
		try
		{
			oleDbCommand.ExecuteNonQuery();
			oleDbCommand.CommandText = "SELECT @@IDENTITY";
			ID = Conversions.ToInteger(oleDbCommand.ExecuteScalar());
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message + "\r" + strrealizarConsult);
			ID = 0;
			result = 0;
			ProjectData.ClearProjectError();
		}
		oleDbCommand.Dispose();
		Close();
		return result;
	}

	public int ConsultWithOutAlerts(string strrealizarConsult, string db = "")
	{
		if (Operators.CompareString(db, "", TextCompare: false) == 0)
		{
			Connect();
		}
		else
		{
			Connect(db);
		}
		OleDbCommand oleDbCommand = new OleDbCommand(strrealizarConsult, Cnx1);
		int result;
		try
		{
			oleDbCommand.ExecuteNonQuery();
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		oleDbCommand.Dispose();
		Close();
		return result;
	}

	public bool ConsultaInsertarTemplate(object template, int clienteId, string db = "")
	{
		int num;
		try
		{
			ConsultaEliminar("ClientesHuellas", "clienteId=" + Conversions.ToString(clienteId));
			if (Operators.CompareString(db, "", TextCompare: false) == 0)
			{
				Connect();
			}
			else
			{
				Connect(db);
			}
			using OleDbCommand oleDbCommand = new OleDbCommand(s);
			using OleDbCommand oleDbCommand2 = new OleDbCommand("INSERT INTO ClientesHuellas(clienteId,Huella) values (@ClienteId, @Huella)");
			oleDbCommand2.Connection = Cnx1;
			oleDbCommand2.Parameters.Add(new OleDbParameter("@ClienteId", clienteId));
			oleDbCommand2.Parameters.Add(new OleDbParameter("@Huella", RuntimeHelpers.GetObjectValue(NewLateBinding.LateGet(template, null, "Bytes", new object[0], null, null, null))));
			oleDbCommand2.ExecuteNonQuery();
			oleDbCommand.Dispose();
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

	public int ConsultaInsertar(string Data, string Table, ref int Id)
	{
		string strrealizarConsult = "insert into " + Table + " values ( " + Data + ")";
		return ExecuteQuerytInsert(strrealizarConsult, "", ref Id);
	}

	public int ConsultaModificar(string Table, string valor1, string Restriction)
	{
		string strrealizarConsult = "update  " + Table + " set " + valor1 + " where " + Restriction;
		return ExecuteQuerytAlter(strrealizarConsult, "");
	}

	public int ConsultaModificar(string Table, string valor1)
	{
		string strrealizarConsult = "update  " + Table + " set " + valor1;
		return ExecuteQuerytAlter(strrealizarConsult, "");
	}

	public int ConsultaEliminar(string Table, string Restriction)
	{
		string strrealizarConsult = "delete from " + Table + " where " + Restriction;
		return ExecuteQuerytAlter(strrealizarConsult, "");
	}

	public DataTable ConsultaVer(string Data, string Table, string Restriction, string orderBy)
	{
		string cadena = ((Restriction.Length != 0) ? ("select " + Data + "  from " + Table + " where " + Restriction + " order by " + orderBy) : ("select " + Data + "  from " + Table + " order by " + orderBy));
		return queryDataTable(cadena, "", "");
	}

	public DataTable ConsultaVerDeOtraBD(string query, string bd, string pwd)
	{
		return queryDataTable(query, bd, pwd);
	}

	public DataTable ConsultaVer(string query)
	{
		return queryDataTable(query, "", "");
	}

	public void ConsultaVerDataset(ref DataSet data, string query, string nombre)
	{
		queryDataSet(ref data, query, nombre, "");
	}

	public DataTable ConsultaVer(string Data, string Table, string Restriction, string orderBy, string groupBy)
	{
		string cadena = ((Restriction.Length == 0) ? ((orderBy.Length != 0) ? ("select " + Data + "  from " + Table + " group by " + groupBy + " order by " + orderBy) : ("select " + Data + "  from " + Table + " group by " + groupBy)) : ((orderBy.Length != 0) ? ("select " + Data + "  from " + Table + " where " + Restriction + " group by " + groupBy + " order by " + orderBy) : ("select " + Data + "  from " + Table + " where " + Restriction + " group by " + groupBy)));
		return queryDataTable(cadena, "", "");
	}

	public DataTable ConsultaVer(string Data, string Table, string Restriction)
	{
		string cadena = "select " + Data + "  from " + Table + " where " + Restriction;
		return queryDataTable(cadena, "", "");
	}

	public DataTable ConsultaVerSinAlertas(string Data, string Table, string Restriction)
	{
		string cadena = "select " + Data + "  from " + Table + " where " + Restriction;
		return ConsultaVerSinAlertas(cadena, "");
	}

	public DataTable ConsultaVer(string Data, string Table)
	{
		string cadena = "select " + Data + "  from " + Table;
		return queryDataTable(cadena, "", "");
	}

	public DataTable ConsultProcedAlmacenado(string NombreProcedimiento)
	{
		return queryDataTable(NombreProcedimiento, "", "");
	}

	public DataTable ConsultProcedAlmacenado(string NombreProcedimiento, string Data)
	{
		string cadena = NombreProcedimiento + " " + Data;
		return queryDataTable(cadena, "", "");
	}
}
