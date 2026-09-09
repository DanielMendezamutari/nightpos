using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsSalones
{
	private int SalonId;

	private string Nombre;

	private string ImpresoraCuenta;

	private string ImpresoraFactura;

	public int _SalonId
	{
		get
		{
			return SalonId;
		}
		set
		{
			SalonId = value;
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

	public string _ImpresoraCuenta
	{
		get
		{
			return ImpresoraCuenta;
		}
		set
		{
			ImpresoraCuenta = value;
		}
	}

	public string _ImpresoraFactura
	{
		get
		{
			return ImpresoraFactura;
		}
		set
		{
			ImpresoraFactura = value;
		}
	}

	public clsSalones()
	{
		Nombre = "";
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Salones", " SalonId=" + SalonId);
		if (dataTable.Rows.Count > 0)
		{
			SalonId = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["SalonId"])) ? ((object)0) : dataTable.Rows[0]["SalonId"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			ImpresoraCuenta = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImpresoraCuenta"])) ? "" : dataTable.Rows[0]["ImpresoraCuenta"]);
			ImpresoraFactura = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImpresoraFactura"])) ? "" : dataTable.Rows[0]["ImpresoraFactura"]);
		}
	}

	public void devolerImpresoraCuentaOverride(int mesaID)
	{
		DataTable dataTable = BD.ConsultaVer("ImpresoraCuenta", "Salones inner join Mesas on Mesas.SalonId=Salones.SalonId", "Mesas.ID=" + mesaID);
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImpresoraCuenta"])) ? "" : dataTable.Rows[0]["ImpresoraCuenta"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			ImpresoraCuenta = ((text == null) ? "" : text);
		}
		else
		{
			ImpresoraCuenta = "";
		}
	}

	public void devolverImpresoraFActuraOverride(int mesaID)
	{
		DataTable dataTable = BD.ConsultaVer("ImpresoraFactura", "Salones inner join Mesas on Mesas.SalonId=Salones.SalonId", "Mesas.ID=" + mesaID);
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImpresoraFactura"])) ? "" : dataTable.Rows[0]["ImpresoraFactura"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			ImpresoraFactura = ((text == null) ? "" : text);
		}
		else
		{
			ImpresoraFactura = "";
		}
	}

	public void devolverImpresoraFacturaOverrideXvisitaID(int visitaID)
	{
		DataTable dataTable = BD.ConsultaVer("ImpresoraFactura", "(Salones inner join Mesas on Mesas.SalonId=Salones.SalonId) inner join Visitas on Visitas.MesaId=Mesas.ID", "Visitas.ID=" + visitaID);
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImpresoraFactura"])) ? "" : dataTable.Rows[0]["ImpresoraFactura"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			ImpresoraFactura = ((text == null) ? "" : text);
		}
		else
		{
			ImpresoraFactura = "";
		}
	}

	public void devolverImpresoraFacturaOverrideXCategoria(int VisitaID)
	{
		DataTable dataTable = BD.ConsultaVer("ImpresoraFactura", " ( (Productos INNER JOIN DetalleCuenta ON Productos.ID = DetalleCuenta.ProductoID) INNER JOIN Categorias_Salones ON Categorias_Salones.CategoriaID = Productos.TipoProductoID) INNER JOIN Salones on Salones.SalonId=  Categorias_Salones.SalonID ", "DetalleCuenta.VisitaID =" + VisitaID);
		if (dataTable.Rows.Count > 0)
		{
			string text = Strings.Replace(Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImpresoraFactura"])) ? "" : dataTable.Rows[0]["ImpresoraFactura"]), "\\\\" + MyProject.Computer.Name + "\\", "", 1, -1, CompareMethod.Text);
			ImpresoraFactura = ((text == null) ? "" : text);
		}
		else
		{
			ImpresoraFactura = "";
		}
	}

	public DataTable devolverSalonesPorNombre()
	{
		return BD.ConsultaVer("SalonId,Nombre", "Salones", "", "Nombre");
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Salones.SalonId,Salones.Nombre,Salones.ImpresoraCuenta,ImpresoraFactura", "Salones");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (select Salones.SalonId,Salones.Nombre,Salones.ImpresoraCuenta", "Salones) as tab1", (field + " " + (field.Contains("as date") ? VariableGeneral.ArmarFecha(Conversions.ToDate(search)) : search)) ?? "");
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Salones", "Nombre='" + Nombre + "',ImpresoraCuenta='" + ImpresoraCuenta + "', ImpresoraFactura='" + ImpresoraFactura + "'", "SalonId=" + SalonId);
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
		checked
		{
			int result;
			try
			{
				if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					SalonId = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(SalonId)", "Salones").Rows[0][0]), 0));
					SalonId++;
					BD.ConsultaInsertar(Conversions.ToString(SalonId) + ",'" + Nombre + "','" + ImpresoraCuenta + "','" + ImpresoraFactura + "'", "Salones(SalonId,Nombre,ImpresoraCuenta,ImpresoraFactura)");
					SalonId = Conversions.ToInteger(BD.ConsultaVer("max(SalonId)", "Salones").Rows[0][0]);
					result = SalonId;
				}
				else
				{
					BD.ConsultaInsertar3("'" + Nombre + "','" + ImpresoraCuenta + "','" + ImpresoraFactura + "'", "Salones(Nombre,ImpresoraCuenta,ImpresoraFactura)", ref SalonId);
					result = SalonId;
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

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Salones", "SalonId = " + SalonId) == 0)
			{
				Interaction.MsgBox("no se puede eliminar Salon, se encuentra en uso");
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
