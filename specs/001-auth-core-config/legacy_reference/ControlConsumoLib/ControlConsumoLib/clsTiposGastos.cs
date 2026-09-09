using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsTiposGastos
{
	private int TipoGastoID;

	private string Descripcion;

	private string SubTipo;

	private bool Activo;

	private string FamiliaGasto;

	public int _TipoGastoID
	{
		get
		{
			return TipoGastoID;
		}
		set
		{
			TipoGastoID = value;
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

	public string _SubTipo
	{
		get
		{
			return SubTipo;
		}
		set
		{
			SubTipo = value;
		}
	}

	public bool _Activo
	{
		get
		{
			return Activo;
		}
		set
		{
			Activo = value;
		}
	}

	public string _FamiliaGasto
	{
		get
		{
			return FamiliaGasto;
		}
		set
		{
			FamiliaGasto = value;
		}
	}

	public DataTable devolverTiposGastosPorDescripcion()
	{
		return BD.ConsultaVer("TipoGastoID,Descripcion,SubTipo,FamiliaGasto", "TiposGastos", "Activo= " + VariableGeneral.armarBolean(1), "Descripcion");
	}

	public string devolverTiposGastosporID()
	{
		DataTable dataTable = BD.ConsultaVer("Descripcion,SubTipo,FamiliaGasto", "TiposGastos", "TipoGastoID=" + Conversions.ToString(TipoGastoID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToString(dataTable.Rows[0][0]);
		}
		return "";
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("TiposGastos.TipoGastoID,TiposGastos.Descripcion, TiposGastos.SubTipo, TiposGastos.FamiliaGasto, TiposGastos.Activo", "TiposGastos");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("TiposGastos", "Descripcion='" + Descripcion + "',SubTipo='" + SubTipo + "',Activo=" + VariableGeneral.armarBolean(Activo) + ",FamiliaGasto='" + FamiliaGasto + "'", "TipoGastoID=" + TipoGastoID);
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
				TipoGastoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(TipoGastoID)", "TiposGastos").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(Conversions.ToString(TipoGastoID) + ",'" + Descripcion + "','" + SubTipo + "'," + VariableGeneral.armarBolean(Activo) + ",'" + FamiliaGasto + "'", "TiposGastos(TipoGastoID,Descripcion,SubTipo,Activo,FamiliaGasto)");
				TipoGastoID = Conversions.ToInteger(BD.ConsultaVer("max(TipoGastoID)", "TiposGastos").Rows[0][0]);
				result = TipoGastoID;
			}
			else
			{
				BD.ConsultaInsertar3("'" + Descripcion + "','" + SubTipo + "'," + VariableGeneral.armarBolean(Activo) + ",'" + FamiliaGasto + "'", "TiposGastos(Descripcion,SubTipo,Activo,FamiliaGasto)", ref TipoGastoID);
				result = TipoGastoID;
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
			if (BD.ConsultaEliminar("TiposGastos", "TipoGastoID = " + TipoGastoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar TipoGasto, se encuentra en uso");
			}
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

	public DataTable devolverSubTipo()
	{
		return BD.ConsultaVer("distinct TiposGastos.SubTipo,TiposGastos.SubTipo", "TiposGastos", " SubTipo <>''", "TiposGastos.SubTipo");
	}

	public DataTable devolverTiposGastosPorDescripcionFiltrado()
	{
		return BD.ConsultaVer("TipoGastoID,Descripcion", "TiposGastos", "SubTipo= '" + SubTipo + "'", "Descripcion");
	}

	public DataTable devolverFamilia()
	{
		return BD.ConsultaVer("distinct TiposGastos.FamiliaGasto as familiaGastoID,TiposGastos.FamiliaGasto", "TiposGastos", " FamiliaGasto <>''", "TiposGastos.FamiliaGasto");
	}

	public DataTable devolverCategoriaPorFamilia()
	{
		return BD.ConsultaVer("distinct SubTipo as SubtipoID,SubTipo", "TiposGastos", "FamiliaGasto= '" + FamiliaGasto + "'", "SubTipo");
	}
}
