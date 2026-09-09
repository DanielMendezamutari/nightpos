using System;
using System.Data;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsReglas
{
	private int ReglaID;

	private int Dia;

	private DateTime HoraIni;

	private DateTime HoraFin;

	private int ProductoID;

	private bool Habilitado;

	public int _ReglaID
	{
		get
		{
			return ReglaID;
		}
		set
		{
			ReglaID = value;
		}
	}

	public int _Dia
	{
		get
		{
			return Dia;
		}
		set
		{
			Dia = value;
		}
	}

	public DateTime _HoraIni
	{
		get
		{
			return HoraIni;
		}
		set
		{
			HoraIni = value;
		}
	}

	public DateTime _HoraFin
	{
		get
		{
			return HoraFin;
		}
		set
		{
			HoraFin = value;
		}
	}

	public int _ProductoID
	{
		get
		{
			return ProductoID;
		}
		set
		{
			ProductoID = value;
		}
	}

	public bool _Habilitado
	{
		get
		{
			return Habilitado;
		}
		set
		{
			Habilitado = value;
		}
	}

	public DataTable devolver()
	{
		return BD.ConsultaVer("Reglas.ReglaID, Reglas.Dia,'' as DiaSemana,Reglas.HoraIni,Reglas.HoraFin,Reglas.ProductoID, Productos.Nombre,Reglas.Habilitado ", "Reglas left join productos on Reglas.ProductoID=Productos.ID");
	}

	public int GetproductoVigenteEnEstehorario()
	{
		DataTable dataTable = BD.ConsultaVer("select productoID from REGLAS where  DATEPART(dw,(" + VariableGeneral.ArmarFecha(DateAndTime.Now) + "))=Dia and  cast('" + VariableGeneral.armarSoloLaHora(DateAndTime.Now) + "' as time) between cast(HoraIni as time) and cast(horaFin as time) and Habilitado=" + VariableGeneral.armarBolean(1));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(dataTable.Rows[0][0]);
		}
		return 0;
	}

	public int Insertar()
	{
		BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(Dia) + ",", VariableGeneral.ArmarFecha(HoraIni), ","), VariableGeneral.ArmarFecha(HoraFin), ","), Conversions.ToString(ProductoID), ","), VariableGeneral.armarBolean(Habilitado)) ?? "", "Reglas(Dia,HoraIni,HoraFin,ProductoID,Habilitado)", ref ReglaID);
		return ReglaID;
	}

	public void Modificar()
	{
		BD.ConsultaModificar("Reglas", ("Dia=" + Conversions.ToString(Dia) + ",HoraIni=" + VariableGeneral.ArmarFecha(HoraIni) + ",HoraFin=" + VariableGeneral.ArmarFecha(HoraFin) + ",ProductoID=" + Conversions.ToString(ProductoID) + ",Habilitado=" + VariableGeneral.armarBolean(Habilitado)) ?? "", "ReglaID= " + ReglaID);
	}

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Reglas", "ReglaID = " + ReglaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar el Regla, se encuentra en uso");
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

	public DataTable DevolverReglas()
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer("ReglaID,Nombre", "Reglas", "Habilitado = " + VariableGeneral.armarBolean(1));
		}
		if (configuration.gMODO_ACCESS == 0)
		{
			return BD.ConsultaVer("ReglaID,Nombre", "Reglas", "Habilitado = " + VariableGeneral.armarBolean(1));
		}
		return BD.ConsultaVer("ReglaID,Nombre", "Reglas", "Habilitado = " + VariableGeneral.armarBolean(1));
	}
}
