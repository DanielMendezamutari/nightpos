using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsCategorias_Impresoras
{
	private int categoriaImpresoraID;

	private int? SalonID;

	private int? CategoriaID;

	private int ImpresoraID;

	public int _categoriaImpresoraID
	{
		get
		{
			return categoriaImpresoraID;
		}
		set
		{
			categoriaImpresoraID = value;
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

	public int _ImpresoraID
	{
		get
		{
			return ImpresoraID;
		}
		set
		{
			ImpresoraID = value;
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
			obj[4] = ",ImpresoraID =";
			obj[5] = Conversions.ToString(ImpresoraID);
			BD.ConsultaModificar("Categorias_Impresoras", string.Concat(obj) ?? "", "categoriaImpresoraID=" + categoriaImpresoraID);
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
				categoriaImpresoraID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(categoriaImpresoraID)", "Categorias_Impresoras").Rows[0][0]), 0), 1));
				string text = Conversions.ToString(categoriaImpresoraID) + ",";
				int? salonID;
				int? num = (salonID = SalonID);
				string text2 = text + (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null) + ",";
				num = (salonID = CategoriaID);
				BD.ConsultaInsertar(string.Concat(text2 + (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null) + ",", Conversions.ToString(ImpresoraID)) ?? "", "Categorias_Impresoras(categoriaImpresoraID,SalonID,CategoriaID,ImpresoraID)");
				categoriaImpresoraID = Conversions.ToInteger(BD.ConsultaVer("max(categoriaImpresoraID)", "Categorias_Impresoras").Rows[0][0]);
				result = categoriaImpresoraID;
			}
			else
			{
				int? salonID;
				int? num = (salonID = SalonID);
				string text3 = (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null) + ",";
				num = (salonID = CategoriaID);
				BD.ConsultaInsertar3(string.Concat(text3 + (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null) + ",", Conversions.ToString(ImpresoraID)) ?? "", "Categorias_Impresoras(SalonID,CategoriaID,ImpresoraID)", ref categoriaImpresoraID);
				result = categoriaImpresoraID;
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
			if (BD.ConsultaEliminar("Categorias_Impresoras", "categoriaImpresoraID = " + categoriaImpresoraID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Categorias_Impresoras, se encuentra en uso");
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
		return BD.ConsultaVer("Categorias_Impresoras.categoriaImpresoraID, TiposProductos.Descripcion as categoria, Impresoras.Nombre as Impresoras", "(((Categorias_Impresoras left join Salones on Salones.SalonID=Categorias_Impresoras.SalonID) left join TiposProductos on TiposProductos.TipoProductoID = Categorias_Impresoras.CategoriaID) left join Impresoras on Impresoras.ImpresoraID= Categorias_Impresoras.ImpresoraID)", "Categorias_Impresoras.SalonID =" + (num.HasValue ? Conversions.ToString(salonID.GetValueOrDefault()) : null));
	}

	public DataTable devolverImpresoraOverride(int mesaID)
	{
		string text = Conversions.ToString(mesaID);
		int? categoriaID;
		int? num = (categoriaID = CategoriaID);
		return BD.ConsultaVer("Impresoras.Nombre as Impresoras", "(((Categorias_Impresoras left join Salones on Salones.SalonID=Categorias_Impresoras.SalonID) left join TiposProductos on TiposProductos.TipoProductoID = Categorias_Impresoras.CategoriaID) left join Impresoras on Impresoras.ImpresoraID= Categorias_Impresoras.ImpresoraID) left join Mesas on Mesas.SalonID = Salones.SalonID ", "Mesas.ID=" + text + " and Categorias_Impresoras.CategoriaID=" + (num.HasValue ? Conversions.ToString(categoriaID.GetValueOrDefault()) : null));
	}
}
