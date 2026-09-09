using System;
using System.Data;
using System.Runtime.CompilerServices;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsArqueo
{
	private int _arqueoID;

	private int _b200;

	private int _b100;

	private int _b50;

	private int _b20;

	private int _b10;

	private int _b5;

	private int _b2;

	private int _b1;

	private int _c50;

	private int _c20;

	private int _c10;

	private int _d100;

	private int _d50;

	private int _d20;

	private int _d10;

	private float _Tarjetas;

	private int _personalID;

	private DateTime _fecha;

	private int _turnoID;

	private int _d5;

	private int _d1;

	private double _OtrasCajas;

	public int ArqueoID
	{
		get
		{
			return _arqueoID;
		}
		set
		{
			_arqueoID = value;
		}
	}

	public DateTime Fecha
	{
		get
		{
			return _fecha;
		}
		set
		{
			_fecha = value;
		}
	}

	public int TurnoID
	{
		get
		{
			return _turnoID;
		}
		set
		{
			_turnoID = value;
		}
	}

	public int B200
	{
		get
		{
			return _b200;
		}
		set
		{
			_b200 = value;
		}
	}

	public int B100
	{
		get
		{
			return _b100;
		}
		set
		{
			_b100 = value;
		}
	}

	public int B50
	{
		get
		{
			return _b50;
		}
		set
		{
			_b50 = value;
		}
	}

	public int B20
	{
		get
		{
			return _b20;
		}
		set
		{
			_b20 = value;
		}
	}

	public int B10
	{
		get
		{
			return _b10;
		}
		set
		{
			_b10 = value;
		}
	}

	public int B5
	{
		get
		{
			return _b5;
		}
		set
		{
			_b5 = value;
		}
	}

	public int B2
	{
		get
		{
			return _b2;
		}
		set
		{
			_b2 = value;
		}
	}

	public int B1
	{
		get
		{
			return _b1;
		}
		set
		{
			_b1 = value;
		}
	}

	public int C50
	{
		get
		{
			return _c50;
		}
		set
		{
			_c50 = value;
		}
	}

	public int C20
	{
		get
		{
			return _c20;
		}
		set
		{
			_c20 = value;
		}
	}

	public int C10
	{
		get
		{
			return _c10;
		}
		set
		{
			_c10 = value;
		}
	}

	public int D50
	{
		get
		{
			return _d50;
		}
		set
		{
			_d50 = value;
		}
	}

	public int D100
	{
		get
		{
			return _d100;
		}
		set
		{
			_d100 = value;
		}
	}

	public int D20
	{
		get
		{
			return _d20;
		}
		set
		{
			_d20 = value;
		}
	}

	public int D10
	{
		get
		{
			return _d10;
		}
		set
		{
			_d10 = value;
		}
	}

	public int D5
	{
		get
		{
			return _d5;
		}
		set
		{
			_d5 = value;
		}
	}

	public int D1
	{
		get
		{
			return _d1;
		}
		set
		{
			_d1 = value;
		}
	}

	public float Tarjetas
	{
		get
		{
			return _Tarjetas;
		}
		set
		{
			_Tarjetas = value;
		}
	}

	public int PersonalID
	{
		get
		{
			return _personalID;
		}
		set
		{
			_personalID = value;
		}
	}

	public double OtrasCajas
	{
		get
		{
			return _OtrasCajas;
		}
		set
		{
			_OtrasCajas = value;
		}
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Arqueo", "TurnoID=" + Conversions.ToString(TurnoID) + ",flagSync=NULL", "ArqueoID=" + ArqueoID);
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message);
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
			string data = string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(VariableGeneral.ArmarFecha(Fecha) + ",", Conversions.ToString(B200), ","), Conversions.ToString(B100), ","), Conversions.ToString(B50), ","), Conversions.ToString(B20), ","), Conversions.ToString(B10), ","), Conversions.ToString(B5), ","), Conversions.ToString(B2), ","), Conversions.ToString(B1), ","), Conversions.ToString(C50), ","), Conversions.ToString(C20), ","), Conversions.ToString(C10), ","), Conversions.ToString(D100), ","), Conversions.ToString(D50), ","), Conversions.ToString(D20), ","), Conversions.ToString(D10), ","), Conversion.Str(Tarjetas), ","), Conversions.ToString(PersonalID), ",NULL ,"), Conversions.ToString(D5), ","), Conversions.ToString(D1), ","), Conversion.Str(_OtrasCajas)) ?? "";
			int id = ArqueoID;
			BD.ConsultaInsertar3(data, "Arqueo(Fecha,B200,B100,B50,B20,B10,B5,B2,B1,C50,C20,C10,D100,D50,D20,D10,Tarjetas,PersonalID,TurnoID,D5,D1,_OtrasCajas)", ref id);
			ArqueoID = id;
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message);
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
			if (BD.ConsultaEliminar("Arqueo", "ArqueoID=" + ArqueoID) == 0)
			{
				Interaction.MsgBox("No se pudo eliminar Arqueo de caja");
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
			Interaction.MsgBox(ex2.Message);
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("*", "Arqueo");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("*", "Arqueo", "Arqueo." + field + "=" + search);
	}

	public string DevolverNombrePersonal()
	{
		DataTable dataTable = BD.ConsultaVer("Nombre", "Meseros", "Meseros.MeseroID=" + PersonalID);
		if (dataTable.Rows.Count == 1)
		{
			return Conversions.ToString(dataTable.Rows[0][0]);
		}
		return "";
	}

	public void devolverMontosUltimoTurno(ref double bs, ref double dolar)
	{
		DataTable dataTable = BD.ConsultaVer(" top 1 turnos.TurnoID, B200*200+B100*100+B50*50+B20*20+B10*10+B5*5+B2*2+B1+C50*0.5+C20*0.2+C10*0.10 AS BS, D100*100+D50*50+D20*20+D10*10+D5*5+D1 AS DOLAR", " turnos left join Arqueo on Arqueo.TurnoID = Turnos.TurnoID  ", "  pc like '" + MyProject.Computer.Name + "'", "turnos.TurnoID desc, Arqueo.ArqueoID desc");
		if (dataTable.Rows.Count > 0)
		{
			bs = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["bs"]), 0));
			dolar = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["dolar"]), 0));
		}
	}
}
