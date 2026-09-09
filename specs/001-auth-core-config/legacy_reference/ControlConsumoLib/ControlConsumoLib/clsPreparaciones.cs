using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsPreparaciones
{
	private int PreparacionID;

	private string Concepto;

	private double cantidad;

	private string ParaProductoID;

	private string paraCategoriaID;

	private bool PuedeDisminuir;

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

	public bool _PuedeDisminuir
	{
		get
		{
			return PuedeDisminuir;
		}
		set
		{
			PuedeDisminuir = value;
		}
	}

	public string _Concepto
	{
		get
		{
			return Concepto;
		}
		set
		{
			Concepto = value;
		}
	}

	public double _cantidad
	{
		get
		{
			return cantidad;
		}
		set
		{
			cantidad = value;
		}
	}

	public int _paraProductoID1
	{
		get
		{
			if (Operators.CompareString(ParaProductoID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(ParaProductoID);
		}
		set
		{
			if (value == 0)
			{
				ParaProductoID = "null";
			}
			else
			{
				ParaProductoID = Conversions.ToString(value);
			}
		}
	}

	public int _paraCategoriaID
	{
		get
		{
			if (Operators.CompareString(paraCategoriaID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(paraCategoriaID);
		}
		set
		{
			if (value == 0)
			{
				paraCategoriaID = "null";
			}
			else
			{
				paraCategoriaID = Conversions.ToString(value);
			}
		}
	}

	public clsPreparaciones()
	{
		Concepto = "";
		cantidad = 0.0;
		ParaProductoID = Conversions.ToString(0);
		PuedeDisminuir = false;
		paraCategoriaID = Conversions.ToString(0);
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Preparaciones", " PreparacionID=" + PreparacionID);
		if (dataTable.Rows.Count > 0)
		{
			PreparacionID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PreparacionID"])) ? ((object)0) : dataTable.Rows[0]["PreparacionID"]);
			Concepto = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Concepto"])) ? "" : dataTable.Rows[0]["Concepto"]);
			ParaProductoID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ParaProductoID"])) ? ((object)0) : dataTable.Rows[0]["ParaProductoID"]);
			cantidad = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["cantidad"])) ? ((object)0) : dataTable.Rows[0]["cantidad"]);
			PuedeDisminuir = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PuedeDisminuir"])) ? ((object)false) : dataTable.Rows[0]["PuedeDisminuir"]);
			paraCategoriaID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["paraCategoriaID"])) ? ((object)0) : dataTable.Rows[0]["paraCategoriaID"]);
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Preparaciones.PreparacionID,Preparaciones.Cantidad,Preparaciones.DeProductoID,Preparaciones.ParaProductoID,PuedeDisminuir", "Preparaciones");
	}

	public DataTable devolverPreparacionesParTodosProductos(bool combos, bool chbSoloHabilitadoVenta)
	{
		string text = "";
		string text2 = "";
		if (VariableGeneral.gPedidosYa)
		{
			text = " Productos1.CodigoPY,";
			text2 = " Productos.CodigoPY,";
		}
		if (combos)
		{
			return BD.ConsultaVer("productos.TienePreparacion,Productos1.Codigo," + text + " Productos1.Nombre as Producto ,  Preparaciones.Concepto," + text2 + "  Productos.Nombre as PosibleInsumo, Productos.Presentacion, Productos.Costo, Productos.CantidadML as Contenido,Productos.UnidadContenido,  Preparaciones.Cantidad as ComposicionReceta , (Productos.Costo/Productos.CantidadML)* Preparaciones.Cantidad as CostoTotal , 0 as Precio", "((Preparaciones inner join PreparacionesComodines on PreparacionesComodines.PreparacionID=Preparaciones.PreparacionID) inner join Productos on Productos.ID=PreparacionesComodines.DeProductoID) inner join Productos as Productos1 on Productos1.ID=Preparaciones.ParaProductoID ", (chbSoloHabilitadoVenta ? ("Productos1.Habilitado =" + VariableGeneral.armarBolean(1) + " and ") : "") + " Productos1.Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.CantidadML >0 and Productos1.esCombo= " + VariableGeneral.armarBolean(1), "Productos1.Codigo,Productos1.Nombre,Preparaciones.Concepto,Preparaciones.Cantidad, Productos.Nombre ");
		}
		return BD.ConsultaVer("productos.TienePreparacion,Productos1.Codigo," + text + "  Productos1.Nombre as Producto ,  Preparaciones.Concepto," + text2 + "  Productos.Nombre as PosibleInsumo, Productos.Presentacion, Productos.Costo, Productos.CantidadML as Contenido,Productos.UnidadContenido,  Preparaciones.Cantidad as ComposicionReceta , (Productos.Costo/Productos.CantidadML)* Preparaciones.Cantidad as CostoTotal  , 0 as Precio", "((Preparaciones inner join PreparacionesComodines on PreparacionesComodines.PreparacionID=Preparaciones.PreparacionID) inner join Productos on Productos.ID=PreparacionesComodines.DeProductoID) inner join Productos as Productos1 on Productos1.ID=Preparaciones.ParaProductoID ", (chbSoloHabilitadoVenta ? ("Productos1.Habilitado =" + VariableGeneral.armarBolean(1) + " and ") : "") + "  Productos1.Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.CantidadML >0 and Productos1.tienePreparacion= " + VariableGeneral.armarBolean(1), "Productos1.Codigo,Productos1.Nombre,Preparaciones.Concepto,Preparaciones.Cantidad, Productos.Nombre ");
	}

	public DataTable devolverPreparacionesParaTodosProductosSubReceta()
	{
		return BD.ConsultaVer("Productos.TienePreparacion,Productos1.Codigo, Productos1.Nombre as Producto ,  Preparaciones.Concepto, Productos.Nombre as PosibleInsumo, Productos.Presentacion, Productos.Costo, Productos.CantidadML as Contenido,Productos.UnidadContenido,  Preparaciones.Cantidad as ComposicionReceta , (Productos.Costo/Productos.CantidadML)* Preparaciones.Cantidad as CostoTotal  , 0 as Precio", "((Preparaciones inner join PreparacionesComodines on PreparacionesComodines.PreparacionID=Preparaciones.PreparacionID) inner join Productos on Productos.ID=PreparacionesComodines.DeProductoID) inner join Productos as Productos1 on Productos1.ID=Preparaciones.ParaProductoID ", "Productos.Borrado=" + VariableGeneral.armarBolean(0) + " and Productos.CantidadML >0 and Productos1.CategoriaProduccionID>0 and Productos1.tienePreparacion= " + VariableGeneral.armarBolean(0) + " and Productos1.esCombo= " + VariableGeneral.armarBolean(0), "Productos1.Codigo, Productos1.Nombre, Preparaciones.Concepto, Preparaciones.Cantidad, Productos.Nombre ");
	}

	public bool tieneComboParaTipoProducto()
	{
		return Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "Preparaciones inner join preparacionescomodines On Preparaciones.PreparacionID =PreparacionesComodines.PreparacionID", "Preparaciones.paraCategoriaID =" + paraCategoriaID.ToString()).Rows[0][0], 0, TextCompare: false);
	}

	public DataTable DevolverParaProducto()
	{
		if (_paraCategoriaID > 0)
		{
			return BD.ConsultaVer("Preparaciones.PreparacionID, Preparaciones.Cantidad, Preparaciones.Concepto, max(Productos.UnidadContenido) As UnidadContenido", "(Preparaciones left join preparacionescomodines On Preparaciones.PreparacionID =PreparacionesComodines.PreparacionID) left join productos On PreparacionesComodines.DeProductoID =Productos.ID", "Preparaciones.paraCategoriaID =" + paraCategoriaID.ToString(), "Preparaciones.PreparacionID", "Preparaciones.PreparacionID, Preparaciones.Cantidad, Preparaciones.Concepto");
		}
		return BD.ConsultaVer("Preparaciones.PreparacionID, Preparaciones.Cantidad, Preparaciones.Concepto, max(Productos.UnidadContenido) As UnidadContenido", "(Preparaciones left join preparacionescomodines On Preparaciones.PreparacionID =PreparacionesComodines.PreparacionID) left join productos On PreparacionesComodines.DeProductoID =Productos.ID", "Preparaciones.ParaProductoID =" + ParaProductoID.ToString(), "Preparaciones.PreparacionID", "Preparaciones.PreparacionID, Preparaciones.Cantidad, Preparaciones.Concepto");
	}

	public DataTable DevolverSiSoyUsadoComoPreparacion(int deProductoID)
	{
		return BD.ConsultaVer("Productos.Nombre", "(Productos inner join Preparaciones on Preparaciones.ParaProductoID=Productos.id) inner join PreparacionesComodines on PreparacionesComodines.PreparacionID=Preparaciones.PreparacionID", "PreparacionesComodines.DeProductoID=" + Conversions.ToString(deProductoID) + " and Productos.Borrado =" + VariableGeneral.armarBolean(0));
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Preparaciones", ("cantidad=" + Conversion.Str(cantidad) + ",Concepto='" + Concepto + "',PuedeDisminuir=" + VariableGeneral.armarBolean(PuedeDisminuir) + ",ParaProductoID=" + ParaProductoID + ", paraCategoriaID=" + paraCategoriaID) ?? "", "PreparacionID=" + PreparacionID);
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
						PreparacionID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(bd1.ConsultaVer("max(PreparacionID)", "Preparaciones").Rows[0][0]), 0));
						PreparacionID++;
						bd1.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(PreparacionID) + ",'" + Concepto + "',", Conversion.Str(cantidad), ","), ParaProductoID.ToString(), ",", VariableGeneral.armarBolean(PuedeDisminuir), ", ", paraCategoriaID), "Preparaciones(PreparacionID,Concepto,Cantidad,ParaProductoID,PuedeDisminuir,paraCategoriaID)", ref PreparacionID);
						result = PreparacionID;
					}
					else
					{
						bd1.ConsultaInsertar(string.Concat(string.Concat("'" + Concepto + "',", Conversion.Str(cantidad), ","), ParaProductoID.ToString(), ",", VariableGeneral.armarBolean(PuedeDisminuir), ", ", paraCategoriaID), "Preparaciones(Concepto,Cantidad,ParaProductoID,PuedeDisminuir,paraCategoriaID)", ref PreparacionID);
						result = PreparacionID;
					}
				}
				else if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					PreparacionID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(PreparacionID)", "Preparaciones").Rows[0][0]), 0));
					PreparacionID++;
					BD.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(PreparacionID) + ",'" + Concepto + "',", Conversion.Str(cantidad), ","), ParaProductoID.ToString(), ",", VariableGeneral.armarBolean(PuedeDisminuir), ", ", paraCategoriaID), "Preparaciones(PreparacionID,Concepto,Cantidad,ParaProductoID,PuedeDisminuir,paraCategoriaID)");
					result = PreparacionID;
				}
				else
				{
					BD.ConsultaInsertar3(string.Concat(string.Concat("'" + Concepto + "',", Conversion.Str(cantidad), ","), ParaProductoID.ToString(), ",", VariableGeneral.armarBolean(PuedeDisminuir), ", ", paraCategoriaID), "Preparaciones(Concepto,Cantidad,ParaProductoID,PuedeDisminuir,paraCategoriaID)", ref PreparacionID);
					result = PreparacionID;
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

	public int EliminarXpara()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Preparaciones", "ParaProductoID = " + ParaProductoID.ToString()) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Preparacion, se encuentra en uso");
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
			if (BD.ConsultaEliminar("Preparaciones", "PreparacionID = " + PreparacionID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Preparacion, se encuentra en uso");
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
