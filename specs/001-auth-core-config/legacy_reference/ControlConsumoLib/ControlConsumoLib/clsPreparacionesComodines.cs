using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsPreparacionesComodines
{
	private int PreparacionComodinID;

	private int DeProductoID;

	private int PreparacionID;

	private bool modificaPrecio;

	private double Precio;

	private int ModificaProductoID;

	public int _PreparacionComodinID
	{
		get
		{
			return PreparacionComodinID;
		}
		set
		{
			PreparacionComodinID = value;
		}
	}

	public int _PreparacionID
	{
		get
		{
			return PreparacionID;
		}
		set
		{
			PreparacionID = value;
		}
	}

	public int _ModificaProductoID
	{
		get
		{
			return ModificaProductoID;
		}
		set
		{
			ModificaProductoID = value;
		}
	}

	public bool _modificaPrecio
	{
		get
		{
			return modificaPrecio;
		}
		set
		{
			modificaPrecio = value;
		}
	}

	public double _Precio
	{
		get
		{
			return Precio;
		}
		set
		{
			Precio = value;
		}
	}

	public int _deProductoID
	{
		get
		{
			return DeProductoID;
		}
		set
		{
			DeProductoID = value;
		}
	}

	public clsPreparacionesComodines()
	{
		PreparacionComodinID = 0;
		DeProductoID = 0;
		PreparacionID = 0;
		Precio = 0.0;
		modificaPrecio = false;
		ModificaProductoID = 0;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "PreparacionesComodines", " PreparacionComodinID=" + PreparacionComodinID);
		if (dataTable.Rows.Count > 0)
		{
			PreparacionComodinID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PreparacionComodinID"])) ? ((object)0) : dataTable.Rows[0]["PreparacionComodinID"]);
			PreparacionID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PreparacionID"])) ? ((object)0) : dataTable.Rows[0]["PreparacionID"]);
			DeProductoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DeProductoID"])) ? ((object)0) : dataTable.Rows[0]["DeProductoID"]);
			Precio = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Precio"])) ? ((object)0) : dataTable.Rows[0]["Precio"]);
			modificaPrecio = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["modificaPrecio"])) ? ((object)0) : dataTable.Rows[0]["modificaPrecio"]);
			ModificaProductoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ModificaProductoID"])) ? ((object)0) : dataTable.Rows[0]["ModificaProductoID"]);
		}
	}

	public DataTable devolverPreparacionesComodinesParaPreparaciones()
	{
		return BD.ConsultaVer("PreparacionComodinID,DeProductoID, Productos.Nombre, TienePreparacion, Productos.UnidadContenido", "Productos INNER JOIN PreparacionesComodines ON Productos.ID = PreparacionesComodines.DeProductoID", "PreparacionID =" + PreparacionID, "PreparacionesComodines.PreparacionComodinID");
	}

	public int Modificar(BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (bd1 != null)
			{
				bd1.ConsultaModificar("PreparacionesComodines", "PreparacionID=" + Conversions.ToString(PreparacionID) + ",DeProductoID=" + DeProductoID + ",ModificaProductoID =" + Conversions.ToString(ModificaProductoID) + ",Precio=" + Conversion.Str(Precio) + ",modificaPrecio=" + VariableGeneral.armarBolean(modificaPrecio), "PreparacionComodinID=" + PreparacionComodinID);
			}
			else
			{
				BD.ConsultaModificar("PreparacionesComodines", "PreparacionID=" + Conversions.ToString(PreparacionID) + ",DeProductoID=" + DeProductoID + ",ModificaProductoID =" + Conversions.ToString(ModificaProductoID) + ",Precio=" + Conversion.Str(Precio) + ",modificaPrecio=" + VariableGeneral.armarBolean(modificaPrecio), "PreparacionComodinID=" + PreparacionComodinID);
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

	public int Insertar(BD_SQL bd1 = null)
	{
		checked
		{
			int result;
			try
			{
				if (bd1 != null)
				{
					if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
					{
						PreparacionComodinID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(bd1.ConsultaVer("max(PreparacionComodinID)", "PreparacionesComodines").Rows[0][0]), 0));
						PreparacionComodinID++;
						bd1.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(PreparacionComodinID) + ",", Conversions.ToString(DeProductoID), ","), Conversions.ToString(PreparacionID), ",", Conversion.Str(Precio), ",", VariableGeneral.armarBolean(modificaPrecio), ",", Conversions.ToString(ModificaProductoID)), "PreparacionesComodines(PreparacionComodinID,DeProductoID,PreparacionID,Precio,modificaPrecio,ModificaProductoID)", ref PreparacionComodinID);
						result = PreparacionComodinID;
					}
					else
					{
						bd1.ConsultaInsertar(string.Concat(Conversions.ToString(DeProductoID) + ",", Conversions.ToString(PreparacionID), ",", Conversion.Str(Precio), ",", VariableGeneral.armarBolean(modificaPrecio), ",", Conversions.ToString(ModificaProductoID)), "PreparacionesComodines(DeProductoID,PreparacionID,Precio,modificaPrecio,ModificaProductoID)", ref PreparacionComodinID);
						result = PreparacionComodinID;
					}
				}
				else if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					PreparacionComodinID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(PreparacionComodinID)", "PreparacionesComodines").Rows[0][0]), 0));
					PreparacionComodinID++;
					BD.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(PreparacionComodinID) + ",", Conversions.ToString(DeProductoID), ","), Conversions.ToString(PreparacionID), ",", Conversion.Str(Precio), ",", VariableGeneral.armarBolean(modificaPrecio), ",", Conversions.ToString(ModificaProductoID)), "PreparacionesComodines(PreparacionComodinID,DeProductoID,PreparacionID,Precio,modificaPrecio,ModificaProductoID)");
					result = PreparacionComodinID;
				}
				else
				{
					BD.ConsultaInsertar3(string.Concat(Conversions.ToString(DeProductoID) + ",", Conversions.ToString(PreparacionID), ",", Conversion.Str(Precio), ",", VariableGeneral.armarBolean(modificaPrecio), ",", Conversions.ToString(ModificaProductoID)), "PreparacionesComodines(DeProductoID,PreparacionID,Precio,modificaPrecio,ModificaProductoID)", ref PreparacionComodinID);
					result = PreparacionComodinID;
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
	}

	public int EliminarPreparacionID()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("PreparacionesComodines", "PreparacionID = " + PreparacionID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Preparaciones Comodines, se encuentra en uso");
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

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("PreparacionesComodines", "PreparacionComodinID = " + PreparacionComodinID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Preparaciones Comodines, se encuentra en uso");
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
}
