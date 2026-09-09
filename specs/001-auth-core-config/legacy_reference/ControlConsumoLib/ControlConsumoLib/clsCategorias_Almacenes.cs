using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCategorias_Almacenes
{
	private int categoriaAlmacenID;

	private int? SalonID;

	private int? CategoriaID;

	private int AlmacenID;

	public int _categoriaAlmacenID
	{
		get
		{
			return categoriaAlmacenID;
		}
		set
		{
			categoriaAlmacenID = value;
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

	public int Modificar()
	{
		int result;
		try
		{
			string[] obj = new string[6] { "SalonID=", null, null, null, null, null };
			int? salonID;
			int? num = (salonID = SalonID);
			obj[1] = (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null);
			obj[2] = ",CategoriaID=";
			num = (salonID = CategoriaID);
			obj[3] = (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null);
			obj[4] = ",AlmacenID =";
			obj[5] = Conversions.ToString(AlmacenID);
			BD.ConsultaModificar("Categorias_Almacenes", string.Concat(obj) ?? "", "categoriaAlmacenID=" + categoriaAlmacenID);
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
			BD.ConsultaInsertar3(string.Concat(text + (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null) + ",", Conversions.ToString(AlmacenID)) ?? "", "Categorias_Almacenes(SalonID,CategoriaID,AlmacenID)", ref categoriaAlmacenID);
			result = categoriaAlmacenID;
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
			if (BD.ConsultaEliminar("Categorias_Almacenes", "categoriaAlmacenID = " + categoriaAlmacenID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Categorias_Almacenes, se encuentra en uso");
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
		return BD.ConsultaVer("Categorias_Almacenes.categoriaAlmacenID, TiposProductos.Descripcion as categoria, Almacenes.Nombre as Almacenes", "(((Categorias_Almacenes left join Salones on Salones.SalonID=Categorias_Almacenes.SalonID) left join TiposProductos on TiposProductos.TipoProductoID = Categorias_Almacenes.CategoriaID) left join Almacenes on Almacenes.AlmacenID= Categorias_Almacenes.AlmacenID)", "Categorias_Almacenes.SalonID =" + (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null));
	}

	public DataTable devolverAlmacenOverride(int mesaID)
	{
		string text = Conversions.ToString(mesaID);
		int? categoriaID;
		int? num = (categoriaID = CategoriaID);
		return BD.ConsultaVer("Almacenes.AlmacenID", "(((Categorias_Almacenes left join Salones on Salones.SalonID=Categorias_Almacenes.SalonID) left join TiposProductos on TiposProductos.TipoProductoID = Categorias_Almacenes.CategoriaID) left join Almacenes on Almacenes.AlmacenID= Categorias_Almacenes.AlmacenID) left join Mesas on Mesas.SalonID = Salones.SalonID ", "Mesas.ID=" + text + " and Categorias_Almacenes.CategoriaID=" + (num.HasValue ? Conversions.ToString(categoriaID.GetValueOrDefault()) : null));
	}
}
