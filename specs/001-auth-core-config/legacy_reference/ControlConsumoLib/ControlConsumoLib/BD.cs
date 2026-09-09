using System.Data;
using System.Drawing;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

[StandardModule]
public sealed class BD
{
	public static void ConsultaInsertarTemplate(object huellas, int clienteId)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			BD_Mysql.ConsultaInsertarTemplate(RuntimeHelpers.GetObjectValue(huellas), clienteId);
		}
		else if (configuration.gMODO_ACCESS == 1)
		{
			new BD_ACCESS1().ConsultaInsertarTemplate(RuntimeHelpers.GetObjectValue(huellas), clienteId);
		}
	}

	public static int ConsultaInsertar(string Data, string Table)
	{
		int Id = 0;
		if (configuration.gMODO_ACCESS == 2)
		{
			string error = "";
			return BD_Mysql.ConsultaInsertar(Data, Table, ref Id, ref error);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaInsertar(Data, Table, ref Id);
		}
		return new BD_SQL().ConsultaInsertar(Data, Table, ref Id);
	}

	public static void realizarConsultaModificarFoto(string NombreProcedimiento, int ID, Image Foto)
	{
		new BD_SQL().realizarConsultaModificarFoto(NombreProcedimiento, ID, Foto);
	}

	public static int ConsultaInsertar3(string Data, string Table, ref int id)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			string error = "";
			return 0 - ((BD_Mysql.ConsultaInsertar(Data, Table, ref id, ref error) != 0) ? 1 : 0);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaInsertar(Data, Table, ref id);
		}
		return new BD_SQL().ConsultaInsertar(Data, Table, ref id);
	}

	public static int ConsultaModificar(string Table, string valor1, string Restriction)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultaModificar(Table, valor1, Restriction);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaModificar(Table, valor1, Restriction);
		}
		return new BD_SQL().ConsultaModificar(Table, valor1, Restriction);
	}

	public static int ConsultaModificar(string Table, string valor1)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultaModificar(Table, valor1);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaModificar(Table, valor1);
		}
		return new BD_SQL().ConsultaModificar(Table, valor1);
	}

	public static int ConsultaEliminar(string Table, string Restriction)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultaEliminar(Table, Restriction);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaEliminar(Table, Restriction);
		}
		return new BD_SQL().ConsultaEliminar(Table, Restriction);
	}

	public static DataTable ConsultaVer(string Data, string Table, string Restriction, string orderBy)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultaVer(Data, Table, Restriction, orderBy);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaVer(Data, Table, Restriction, orderBy);
		}
		return new BD_SQL().ConsultaVer(Data, Table, Restriction, orderBy);
	}

	public static DataTable ConsultaVerParaReporte(string query, bool consolidado)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultaVer(query);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaVer(query);
		}
		checked
		{
			if (consolidado)
			{
				DataTable dataTable = new BD_SQL().ConsultaVer(query);
				ctlAlmacenes ctlAlmacenes2 = new ctlAlmacenes();
				DataTable dataTable2 = ctlAlmacenes2.DevolverTodosAlmacenesExternosConConexion();
				int num = dataTable2.Rows.Count - 1;
				for (int i = 0; i <= num; i++)
				{
					BD_SQL bD_SQL = new BD_SQL();
					ctlAlmacenes2.SetAlmacenID(Conversions.ToInteger(dataTable2.Rows[i][0]));
					bD_SQL.setSQLs(ctlAlmacenes2.devolverSQLs());
					new ctlProductos();
					string error = "";
					if (bD_SQL.testConectar(ref error))
					{
						string datos = query.Replace("'Local' as Suc,", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("'", dataTable2.Rows[i][1]), "' as Suc,")));
						DataTable table = bD_SQL.ConsultaVer(datos);
						dataTable.Merge(table);
					}
					else
					{
						Interaction.MsgBox(Operators.ConcatenateObject("No se pudo conectar con ", dataTable2.Rows[i][1]));
					}
				}
				return dataTable;
			}
			return new BD_SQL().ConsultaVer(query);
		}
	}

	public static DataTable ConsultaVerParaReporte2(string query, bool consolidado)
	{
		DataTable dataTable = new BD_SQL().ConsultaVer(query);
		ctlAlmacenes ctlAlmacenes2 = new ctlAlmacenes();
		DataTable dataTable2 = ctlAlmacenes2.DevolverTodosAlmacenesExternosConConexion();
		checked
		{
			int num = dataTable2.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				BD_SQL bD_SQL = new BD_SQL();
				ctlAlmacenes2.SetAlmacenID(Conversions.ToInteger(dataTable2.Rows[i][0]));
				bD_SQL.setSQLs(ctlAlmacenes2.devolverSQLs());
				new ctlProductos();
				string error = "";
				if (bD_SQL.testConectar(ref error))
				{
					DataTable table = bD_SQL.ConsultaVer(query);
					dataTable.Merge(table);
				}
				else
				{
					Interaction.MsgBox(Operators.ConcatenateObject("No se pudo conectar con ", dataTable2.Rows[i][1]));
				}
			}
			return dataTable;
		}
	}

	public static DataTable ConsultaVer(string query)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultaVer(query);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaVer(query);
		}
		return new BD_SQL().ConsultaVer(query);
	}

	public static void ConsultaVerDataset(ref DataSet data, string query, string nombre)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			BD_Mysql.ConsultaVerDataSet(ref data, query, nombre);
		}
		else if (configuration.gMODO_ACCESS == 1)
		{
			new BD_ACCESS1().ConsultaVerDataset(ref data, query, nombre);
		}
		else
		{
			new BD_SQL().ConsultaVerDataSet(ref data, query, nombre);
		}
	}

	public static DataTable ConsultaVer(string Data, string Table, string Restriction, string orderBy, string groupBy)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultaVer(Data, Table, Restriction, orderBy, groupBy);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaVer(Data, Table, Restriction, orderBy, groupBy);
		}
		return new BD_SQL().ConsultaVer(Data, Table, Restriction, orderBy, groupBy);
	}

	public static DataTable ConsultaVer(string Data, string Table, string Restriction)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultaVer(Data, Table, Restriction);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaVer(Data, Table, Restriction);
		}
		return new BD_SQL().ConsultaVer(Data, Table, Restriction);
	}

	public static DataTable ConsultaVerSinAlertas(string Data, string Table, string Restriction, ref string error1)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ExecuteQueryDataTableWithOutAlerts("select " + Data + " from " + Table + " where " + Restriction);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaVerSinAlertas(Data, Table, Restriction);
		}
		return new BD_SQL().ConsultaVerSinAlertas(Data, Table, Restriction, ref error1);
	}

	public static DataTable ConsultaVerSinAlertas(string Data, string Table, string Restriction, string groupby, string orderby, ref string error1)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ExecuteQueryDataTableWithOutAlerts("select " + Data + " from " + Table + " where " + Restriction + " group by" + groupby + " order by " + orderby);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaVer(Data, Table, Restriction);
		}
		return new BD_SQL().ConsultaVerSinAlertas(Data, Table, Restriction, groupby, orderby, ref error1);
	}

	public static DataTable ConsultaVerSinAlertas(string Data, string Table, string Restriction, string groupby, ref string error1)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ExecuteQueryDataTableWithOutAlerts("select " + Data + " from " + Table + " where " + Restriction + " group by" + groupby);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaVer(Data, Table, Restriction, groupby);
		}
		return new BD_SQL().ConsultaVerSinAlertas(Data, Table, Restriction, groupby, ref error1);
	}

	public static DataTable ConsultaVer(string Data, string Table)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultaVer(Data, Table);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultaVer(Data, Table);
		}
		return new BD_SQL().ConsultaVer(Data, Table);
	}

	public static int ConsultWithOutAlerts(string strrealizarConsult)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD_Mysql.ConsultWithOutAlerts(strrealizarConsult);
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultWithOutAlerts(strrealizarConsult);
		}
		return new BD_SQL().ConsultWithOutAlerts(strrealizarConsult);
	}

	public static void ConsultWithOutAlertsConsolidado(string query)
	{
		new BD_SQL().ConsultWithOutAlerts(query);
		ctlAlmacenes ctlAlmacenes2 = new ctlAlmacenes();
		DataTable dataTable = ctlAlmacenes2.DevolverTodosAlmacenesExternosConConexion();
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				BD_SQL bD_SQL = new BD_SQL();
				ctlAlmacenes2.SetAlmacenID(Conversions.ToInteger(dataTable.Rows[i][0]));
				bD_SQL.setSQLs(ctlAlmacenes2.devolverSQLs());
				string error = "";
				if (bD_SQL.testConectar(ref error))
				{
					bD_SQL.ConsultWithOutAlerts(query);
				}
				else
				{
					Interaction.MsgBox(Operators.ConcatenateObject("No se pudo conectar con ", dataTable.Rows[i][1]));
				}
			}
		}
	}

	public static bool TestBD(ref string error1)
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return true;
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return new BD_ACCESS1().ConsultWithOutAlerts("update Cuentas set Banco='' where cuentaID=1", configuration.db_file) > 0;
		}
		return new BD_SQL().testConectar(ref error1);
	}
}
