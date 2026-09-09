using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsMesaAdicionales
{
	private int MesaAdicionalID;

	private string Nombre;

	private string Descripcion;

	public int _MesaAdicionalID
	{
		get
		{
			return MesaAdicionalID;
		}
		set
		{
			MesaAdicionalID = value;
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

	public clsMesaAdicionales()
	{
		Nombre = "";
		Descripcion = "";
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "MesasAdicionales", " MesaAdicionalID=" + MesaAdicionalID);
		if (dataTable.Rows.Count > 0)
		{
			MesaAdicionalID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MesaAdicionalID"])) ? ((object)0) : dataTable.Rows[0]["MesaAdicionalID"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			Descripcion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"])) ? "" : dataTable.Rows[0]["Descripcion"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("MesasAdicionales.MesaAdicionalID,MesasAdicionales.Nombre,MesasAdicionales.Descripcion", "MesasAdicionales");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select MesasAdicionales.MesaAdicionalID,MesasAdicionales.Nombre,MesasAdicionales.Descripcion", "MesasAdicionales) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("MesasAdicionales", "Nombre='" + Nombre + "',Descripcion='" + Descripcion + "'", "MesaAdicionalID=" + MesaAdicionalID);
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
				MesaAdicionalID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(MesaAdicionalID)", "MesasAdicionales").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(Conversions.ToString(MesaAdicionalID) + ",'" + Nombre + "','" + Descripcion + "'", "MesasAdicionales(MesaAdicionalID,Nombre,Descripcion)");
				MesaAdicionalID = Conversions.ToInteger(BD.ConsultaVer("max(MesaAdicionalID)", "MesasAdicionales").Rows[0][0]);
				result = MesaAdicionalID;
			}
			else
			{
				BD.ConsultaInsertar3("'" + Nombre + "','" + Descripcion + "'", "MesasAdicionales(Nombre,Descripcion)", ref MesaAdicionalID);
				result = MesaAdicionalID;
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

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("MesasAdicionales", "MesaAdicionalID = " + MesaAdicionalID) == 0)
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

	public DataTable ToreturnMesasAdicionalesPorID()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("MesasAdicionales.MesaAdicionalID ,MesasAdicionales.Nombre,MesasAdicionales.Descripcion, iif( tab1.EnMesa is null, " + VariableGeneral.armarBolean(0) + ", tab1.EnMesa)  as Ocupada,tab1.Cliente, tab1.visitaID  ", "(MesasAdicionales left join  ( select Visitas.ID as visitaID,Visitas.MesaAdicionalID , Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaAdicionalID , max(Visitas.Fecha) as Fecha1 from Visitas  inner join MesasAdicionales as mes1 on (mes1.MesaAdicionalID=Visitas.MesaAdicionalID  and Visitas.EnMesa='TRUE')  group by Visitas.MesaAdicionalID ) as tab2 inner join Visitas on tab2.MesaAdicionalID= Visitas.MesaAdicionalID  and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on MesasAdicionales.MesaAdicionalID =tab1.MesaAdicionalID  )", ("MesasAdicionales.MesaAdicionalID =" + Conversions.ToString(MesaAdicionalID)) ?? "");
		}
		return BD.ConsultaVer("MesasAdicionales.MesaAdicionalID ,MesasAdicionales.Nombre,MesasAdicionales.Descripcion,CASE WHEN  tab1.EnMesa  is null  THEN  " + VariableGeneral.armarBolean(0) + " ELSE  tab1.EnMesa  END  as Ocupada,tab1.Cliente, tab1.visitaID  ", "(MesasAdicionales left join  ( select Visitas.ID as visitaID,Visitas.MesaAdicionalID , Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaAdicionalID , max(Visitas.Fecha) as Fecha1 from Visitas  inner join MesasAdicionales as mes1 on (mes1.MesaAdicionalID=Visitas.MesaAdicionalID  and Visitas.EnMesa='TRUE')  group by Visitas.MesaAdicionalID ) as tab2 inner join Visitas on tab2.MesaAdicionalID= Visitas.MesaAdicionalID  and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on MesasAdicionales.MesaAdicionalID =tab1.MesaAdicionalID  )", ("MesasAdicionales.MesaAdicionalID =" + Conversions.ToString(MesaAdicionalID)) ?? "");
	}
}
