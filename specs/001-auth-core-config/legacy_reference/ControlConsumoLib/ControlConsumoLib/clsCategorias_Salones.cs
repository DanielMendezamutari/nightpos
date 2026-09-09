using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCategorias_Salones
{
	private int CategoriaSalonID;

	private int? SalonID;

	private int? CategoriaID;

	public int _CategoriaSalonID
	{
		get
		{
			return CategoriaSalonID;
		}
		set
		{
			CategoriaSalonID = value;
		}
	}

	public int _SalonID
	{
		get
		{
			return SalonID.Value;
		}
		set
		{
			SalonID = value;
		}
	}

	public int _CategoriaID
	{
		get
		{
			return CategoriaID.Value;
		}
		set
		{
			CategoriaID = value;
		}
	}

	public int Modificar()
	{
		int result;
		try
		{
			int? salonID;
			int? num = (salonID = SalonID);
			string obj = (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null);
			num = (salonID = CategoriaID);
			BD.ConsultaModificar("Categorias_Salones", "SalonID=" + obj + ",CategoriaID=" + (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null), "CategoriaSalonID=" + CategoriaSalonID);
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
			int? salonID;
			int? num = (salonID = SalonID);
			string text = (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null) + ",";
			num = (salonID = CategoriaID);
			BD.ConsultaInsertar3(text + (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null), "Categorias_Salones(SalonID,CategoriaID)", ref CategoriaSalonID);
			result = CategoriaSalonID;
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
			if (BD.ConsultaEliminar("Categorias_Salones", "CategoriaSalonID = " + CategoriaSalonID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Categorias_Salones, se encuentra en uso");
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

	public DataTable Devolver()
	{
		int? salonID;
		int? num = (salonID = SalonID);
		return BD.ConsultaVer("Categorias_Salones.CategoriaSalonID, TiposProductos.Descripcion as Categoria", "(((Categorias_Salones left join Salones on Salones.SalonID=Categorias_Salones.SalonID) left join TiposProductos on TiposProductos.TipoProductoID = Categorias_Salones.CategoriaID) )", "Categorias_Salones.SalonID =" + (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null));
	}
}
