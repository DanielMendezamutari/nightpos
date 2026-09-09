using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsAlmacenes
{
	private int AlmacenID;

	private string Nombre;

	private string Descripcion;

	private string IP;

	private string Instancia;

	private string Usuario;

	private string Pass;

	private bool Interno;

	private int ResponsableID;

	public int _AlmacenID
	{
		get
		{
			return AlmacenID;
		}
		set
		{
			AlmacenID = value;
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

	public string _Descripcion
	{
		get
		{
			return Descripcion;
		}
		set
		{
			Descripcion = value;
		}
	}

	public string _IP
	{
		get
		{
			return IP;
		}
		set
		{
			IP = value;
		}
	}

	public string _Instancia
	{
		get
		{
			return Instancia;
		}
		set
		{
			Instancia = value;
		}
	}

	public string _Usuario
	{
		get
		{
			return Usuario;
		}
		set
		{
			Usuario = value;
		}
	}

	public string _Pass
	{
		get
		{
			return Pass;
		}
		set
		{
			Pass = value;
		}
	}

	public bool _Interno
	{
		get
		{
			return Interno;
		}
		set
		{
			Interno = value;
		}
	}

	public int _ResponsableID
	{
		get
		{
			return ResponsableID;
		}
		set
		{
			ResponsableID = value;
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre,Almacenes.Descripcion,Almacenes.IP,Almacenes.Instancia,Almacenes.Usuario,Almacenes.Pass,Almacenes.Interno,ResponsableID", "Almacenes");
	}

	public DataTable DevolverMiData()
	{
		return BD.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre,Almacenes.Descripcion,Almacenes.IP,Almacenes.Instancia,Almacenes.Usuario,Almacenes.Pass,Almacenes.Interno", "Almacenes", "AlmacenID=" + Conversions.ToString(AlmacenID));
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre,Almacenes.Descripcion,Almacenes.IP,Almacenes.Instancia,Almacenes.Usuario,Almacenes.Pass,Almacenes.Interno,Almacenes.ResponsableID", "Almacenes", "AlmacenID=" + Conversions.ToString(AlmacenID));
		if (dataTable.Rows.Count > 0)
		{
			AlmacenID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AlmacenID"])) ? ((object)0) : dataTable.Rows[0]["AlmacenID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			Descripcion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"])) ? "" : dataTable.Rows[0]["Descripcion"]);
			IP = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["IP"])) ? "" : dataTable.Rows[0]["IP"]);
			Instancia = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Instancia"])) ? "" : dataTable.Rows[0]["Instancia"]);
			Usuario = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Usuario"])) ? "" : dataTable.Rows[0]["Usuario"]);
			Pass = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Pass"])) ? "" : dataTable.Rows[0]["Pass"]);
			AlmacenID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AlmacenID"])) ? ((object)0) : dataTable.Rows[0]["AlmacenID"]);
			Interno = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Interno"])) ? ((object)false) : dataTable.Rows[0]["Interno"]);
			ResponsableID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ResponsableID"])) ? ((object)0) : dataTable.Rows[0]["ResponsableID"]);
		}
		else
		{
			AlmacenID = 0;
			Nombre = "";
			Descripcion = "";
			IP = "";
			Instancia = "";
			Pass = "";
			ResponsableID = 0;
		}
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select Almacenes.AlmacenID,Almacenes.Nombre,Almacenes.Descripcion,Almacenes.IP,Almacenes.Instancia,Almacenes.Usuario,Almacenes.Pass,Almacenes.Interno,ResponsableID", "Almacenes) as tab1");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Almacenes", ("Nombre='" + Nombre + "',Descripcion='" + Descripcion + "',IP='" + IP + "',Instancia='" + Instancia + "',Usuario='" + Usuario + "',Pass='" + Pass + "',ResponsableID=" + Conversions.ToString(ResponsableID) + ",Interno=" + VariableGeneral.armarBolean(Interno)) ?? "", "AlmacenID=" + AlmacenID);
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
			BD.ConsultaInsertar3(string.Concat("'" + Nombre + "','" + Descripcion + "','" + IP + "','" + Instancia + "','" + Usuario + "','" + Pass + "',", VariableGeneral.armarBolean(Interno), ",", Conversions.ToString(ResponsableID)), "Almacenes(Nombre,Descripcion,IP,Instancia,Usuario,Pass,Interno,ResponsableID)", ref AlmacenID);
			result = AlmacenID;
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
			if (BD.ConsultaEliminar("Almacenes", "AlmacenID = " + AlmacenID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Almacen, se encuentra en uso");
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

	public DataTable DevolverTodosAlmacenes()
	{
		if (ResponsableID == -1)
		{
			return BD.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre,Almacenes.Descripcion,Almacenes.IP,Almacenes.Instancia,Almacenes.Usuario,Almacenes.Pass,Almacenes.Interno,ResponsableID", "Almacenes");
		}
		return BD.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre,Almacenes.Descripcion,Almacenes.IP,Almacenes.Instancia,Almacenes.Usuario,Almacenes.Pass,Almacenes.Interno,ResponsableID", "Almacenes", "(ResponsableId=0 or ResponsableId=" + Conversions.ToString(ResponsableID) + ")", "Nombre");
	}

	public DataTable DevolverTodosAlmacenesExternosConConexion()
	{
		return BD.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre", "Almacenes", "Interno=" + VariableGeneral.armarBolean(0) + " and Usuario <>''");
	}

	public DataTable DevolverTodosAlmacenesInternos(BD_SQL bd1 = null)
	{
		if (bd1 != null)
		{
			return bd1.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre", "Almacenes", "Interno=" + VariableGeneral.armarBolean(1), "Nombre");
		}
		if (ResponsableID == -1)
		{
			return BD.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre", "Almacenes", "Interno=" + VariableGeneral.armarBolean(1), "Nombre");
		}
		return BD.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre", "Almacenes", "Interno=" + VariableGeneral.armarBolean(1) + " and (ResponsableId=0 or ResponsableId=" + Conversions.ToString(ResponsableID) + ")", "Nombre");
	}

	public DataTable DevolverSoloMisAlmacenesInternos()
	{
		return BD.ConsultaVer("Almacenes.AlmacenID,Almacenes.Nombre", "Almacenes", "Interno=" + VariableGeneral.armarBolean(1) + " and ResponsableId=" + Conversions.ToString(ResponsableID), "Nombre");
	}

	public int getIDporNombreAlmacen(string nombre, BD_SQL bd1)
	{
		if (bd1 != null)
		{
			DataTable dataTable = bd1.ConsultaVer("AlmacenID", "Almacenes", "nombre like '" + nombre + "'");
			if (dataTable.Rows.Count > 0)
			{
				return Conversions.ToInteger(dataTable.Rows[0][0]);
			}
			return 0;
		}
		return 0;
	}

	public bool TieneIP()
	{
		DataTable dataTable = BD.ConsultaVer("IP", "Almacenes", "AlmacenID = " + Conversions.ToString(AlmacenID));
		if (dataTable.Rows.Count > 0)
		{
			if (Operators.ConditionalCompareObjectNotEqual(dataTable.Rows[0][0], "", TextCompare: false))
			{
				return true;
			}
			return false;
		}
		return false;
	}

	public bool TieneInstancia()
	{
		DataTable dataTable = BD.ConsultaVer("Instancia", "Almacenes", "AlmacenID = " + Conversions.ToString(AlmacenID));
		if (dataTable.Rows.Count > 0)
		{
			if (Operators.ConditionalCompareObjectNotEqual(dataTable.Rows[0][0], "", TextCompare: false))
			{
				return true;
			}
			return false;
		}
		return false;
	}
}
