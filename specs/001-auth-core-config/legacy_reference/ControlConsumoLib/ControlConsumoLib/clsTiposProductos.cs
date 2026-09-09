using System;
using System.Data;
using System.Drawing;
using System.IO;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsTiposProductos
{
	private int TipoProductoID;

	private string Descripcion;

	private string codigo;

	private int? ImpresoraID;

	private bool ManejarStock;

	private int orden;

	private int? FamiliaID;

	private int AlmacenID;

	private int TipoUsuarioID;

	private int ConfiguracionID;

	private int DocumentoSector;

	private int kitchenDisplayID;

	public int _TipoProductoID
	{
		get
		{
			return TipoProductoID;
		}
		set
		{
			TipoProductoID = value;
		}
	}

	public int _orden
	{
		get
		{
			return orden;
		}
		set
		{
			orden = value;
		}
	}

	public int _ImpresoraID
	{
		get
		{
			if (ImpresoraID.HasValue)
			{
				return ImpresoraID.Value;
			}
			return 0;
		}
		set
		{
			if (value == 0)
			{
				ImpresoraID = null;
			}
			else
			{
				ImpresoraID = value;
			}
		}
	}

	public int _FamiliaID
	{
		get
		{
			if (FamiliaID.HasValue)
			{
				return FamiliaID.Value;
			}
			return 0;
		}
		set
		{
			if (value == 0)
			{
				FamiliaID = null;
			}
			else
			{
				FamiliaID = value;
			}
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

	public string _codigo
	{
		get
		{
			return codigo;
		}
		set
		{
			codigo = value;
		}
	}

	public bool _ManejarStock
	{
		get
		{
			return ManejarStock;
		}
		set
		{
			ManejarStock = value;
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

	public int _TipoUsuarioId
	{
		get
		{
			return TipoUsuarioID;
		}
		set
		{
			TipoUsuarioID = value;
		}
	}

	public int _DocumentoSector
	{
		get
		{
			return DocumentoSector;
		}
		set
		{
			DocumentoSector = value;
		}
	}

	public int _ConfiguracionID
	{
		get
		{
			return ConfiguracionID;
		}
		set
		{
			ConfiguracionID = value;
		}
	}

	public int _kitchenDisplayID
	{
		get
		{
			return kitchenDisplayID;
		}
		set
		{
			kitchenDisplayID = value;
		}
	}

	public clsTiposProductos()
	{
		Descripcion = "";
		TipoProductoID = 0;
		ImpresoraID = 0;
		ManejarStock = false;
		codigo = "";
		FamiliaID = 0;
		orden = 0;
		AlmacenID = 0;
		TipoUsuarioID = 0;
		ConfiguracionID = 1;
		kitchenDisplayID = 0;
		DocumentoSector = 0;
	}

	public Image getImage()
	{
		Image result;
		try
		{
			DataTable dataTable = BD.ConsultaVer("foto", "TiposProductos_Fotos", "ID=" + TipoProductoID);
			if (dataTable.Rows.Count > 0)
			{
				if (Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0])))
				{
					result = null;
				}
				else
				{
					byte[] buffer = (byte[])dataTable.Rows[0][0];
					result = Image.FromStream(new MemoryStream(buffer));
				}
			}
			else
			{
				result = null;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = null;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public void llenarclase()
	{
		DataTable dataTable = BD.ConsultaVer("*", "TiposProductos", " TipoProductoID=" + TipoProductoID);
		if (dataTable.Rows.Count > 0)
		{
			TipoProductoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoProductoID"])) ? ((object)0) : dataTable.Rows[0]["TipoProductoID"]);
			Descripcion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"])) ? "" : dataTable.Rows[0]["Descripcion"]);
			ImpresoraID = (int?)(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImpresoraID"])) ? ((object)0) : dataTable.Rows[0]["ImpresoraID"]);
			ManejarStock = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ManejarStock"])) ? ((object)false) : dataTable.Rows[0]["ManejarStock"]);
			codigo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["codigo"])) ? "" : dataTable.Rows[0]["codigo"]);
			FamiliaID = (int?)(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FamiliaID"])) ? ((object)0) : dataTable.Rows[0]["FamiliaID"]);
			orden = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["orden"])) ? ((object)0) : dataTable.Rows[0]["orden"]);
			AlmacenID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AlmacenID"])) ? ((object)0) : dataTable.Rows[0]["AlmacenID"]);
			TipoUsuarioID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoUsuarioID"])) ? ((object)0) : dataTable.Rows[0]["TipoUsuarioID"]);
			kitchenDisplayID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["kitchenDisplayID"])) ? ((object)0) : dataTable.Rows[0]["kitchenDisplayID"]);
			ConfiguracionID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ConfiguracionID"])) ? ((object)1) : dataTable.Rows[0]["ConfiguracionID"]);
			DocumentoSector = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DocumentoSector"])) ? ((object)1) : dataTable.Rows[0]["DocumentoSector"]);
		}
	}

	public DataTable devolverTiposProductosFacturacion(string descrip)
	{
		return BD.ConsultaVer("TipoProductoID,Descripcion", "TiposProductos", "Descripcion like '" + descrip + "'");
	}

	public DataTable devolverFamiliasQtenganProductosOverrideAsociadosAlSalon(object mesaID)
	{
		DataTable dataTable = BD.ConsultaVer("distinct Familias.FamiliaID, Familias.Descripcion", "(((Mesas inner join Salones on Mesas.SalonID  = Salones.SalonID) inner join Categorias_Salones on Categorias_Salones.SalonId= Salones.SalonID) inner join TiposProductos on (TiposProductos.TipoProductoID= Categorias_Salones.CategoriaID )) INNER JOIN Familias ON Familias.FamiliaID = TiposProductos.FamiliaId", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("Mesas.Id=", mesaID), " and  TiposProductos.TipoProductoID in (select distinct TipoProductoID from Productos where Habilitado="), VariableGeneral.armarBolean(1)), ")")), "Familias.Descripcion");
		if (dataTable.Rows.Count == 0)
		{
			dataTable = BD.ConsultaVer("distinct Familias.FamiliaID, Familias.Descripcion", "Familias INNER JOIN TiposProductos ON Familias.FamiliaID = TiposProductos.FamiliaId", "TiposProductos.TipoProductoID in (select distinct TipoProductoID from Productos where Habilitado=" + VariableGeneral.armarBolean(1) + ")", "Familias.Descripcion");
		}
		return dataTable;
	}

	public DataTable devolverFamiliasQtenganProductos()
	{
		return BD.ConsultaVer("distinct Familias.FamiliaID, Familias.Descripcion", "Familias INNER JOIN TiposProductos ON Familias.FamiliaID = TiposProductos.FamiliaId", "TipoProductoID in (select distinct TipoProductoID from Productos where Habilitado=" + VariableGeneral.armarBolean(1) + ")", "Familias.Descripcion");
	}

	public DataTable devolverTiposProductosQtenganProductosOverrideAsociadosAlSalon(int mesaID, string familia, int TipoUsuarioID)
	{
		DataTable dataTable;
		if (TipoUsuarioID > 1)
		{
			dataTable = BD.ConsultaVer("TiposProductos.TipoProductoID,TiposProductos.Descripcion", "((Mesas inner join Salones on Mesas.SalonID  = Salones.SalonID) inner join Categorias_Salones on Categorias_Salones.SalonId= Salones.SalonID) inner join TiposProductos on (TiposProductos.TipoProductoID= Categorias_Salones.CategoriaID )", "Mesas.Id=" + Conversions.ToString(mesaID) + " and (TiposProductos.TipoUsuarioId=0 or TiposProductos.TipoUsuarioId is null or TiposProductos.TipoUsuarioId=" + Conversions.ToString(TipoUsuarioID) + ")  and  TiposProductos.TipoProductoID in (select distinct TipoProductoID from Productos where Habilitado=" + VariableGeneral.armarBolean(1) + ")", "Orden");
			if (dataTable.Rows.Count == 0)
			{
				dataTable = BD.ConsultaVer("TiposProductos.TipoProductoID,TiposProductos.Descripcion", "Familias INNER JOIN TiposProductos ON Familias.FamiliaID = TiposProductos.FamiliaId", "Familias.Descripcion like '" + familia + "' and (TiposProductos.TipoUsuarioId=0 or TiposProductos.TipoUsuarioId is null or TiposProductos.TipoUsuarioId=" + Conversions.ToString(TipoUsuarioID) + ") and TipoProductoID in (select distinct TipoProductoID from Productos where Habilitado=" + VariableGeneral.armarBolean(1) + ")", "orden");
			}
		}
		else
		{
			dataTable = BD.ConsultaVer("TiposProductos.TipoProductoID,TiposProductos.Descripcion", "((Mesas inner join Salones on Mesas.SalonID  = Salones.SalonID) inner join Categorias_Salones on Categorias_Salones.SalonId= Salones.SalonID) inner join TiposProductos on (TiposProductos.TipoProductoID= Categorias_Salones.CategoriaID )", "Mesas.Id=" + Conversions.ToString(mesaID) + " and  TiposProductos.TipoProductoID in (select distinct TipoProductoID from Productos where Habilitado=" + VariableGeneral.armarBolean(1) + ")", "Orden");
			if (dataTable.Rows.Count == 0)
			{
				dataTable = BD.ConsultaVer("TiposProductos.TipoProductoID,TiposProductos.Descripcion", "Familias INNER JOIN TiposProductos ON Familias.FamiliaID = TiposProductos.FamiliaId", "Familias.Descripcion like '" + familia + "' and TipoProductoID in (select distinct TipoProductoID from Productos where Habilitado=" + VariableGeneral.armarBolean(1) + ")", "orden");
			}
		}
		return dataTable;
	}

	public DataTable devolverTiposProductosQtenganProductos1(string familia)
	{
		if (familia.Length > 0)
		{
			return BD.ConsultaVer("TiposProductos.TipoProductoID,TiposProductos.Descripcion", "Familias INNER JOIN TiposProductos ON Familias.FamiliaID = TiposProductos.FamiliaId", "Familias.Descripcion like '" + familia + "' and TipoProductoID in (select distinct TipoProductoID from Productos where  Habilitado=" + VariableGeneral.armarBolean(1) + ")", "orden");
		}
		return BD.ConsultaVer("TipoProductoID,Descripcion", " TiposProductos ", "TipoProductoID in (select distinct TipoProductoID from Productos where Habilitado=" + VariableGeneral.armarBolean(1) + ")", "orden");
	}

	public DataTable devolverImagenesTiposProductosQtenganProductos1()
	{
		return BD.ConsultaVer("Descripcion,Foto", "TiposProductos inner join TiposProductos_Fotos on TiposProductos_Fotos.id=TipoProductoID", " TipoProductoID in (select distinct TipoProductoID from Productos where Habilitado=" + VariableGeneral.armarBolean(1) + " and esPorPeso=" + VariableGeneral.armarBolean(1) + ")", "orden");
	}

	public DataTable devolverCategoriaProduccionPorDescripcion1()
	{
		return BD.ConsultaVer("CategoriaProduccionID,Nombre", "CategoriasProduccion");
	}

	public string devolverTipoProductoPorCodigo(string codigo)
	{
		DataTable dataTable = BD.ConsultaVer("Descripcion", "TiposProductos", "Codigo like '" + codigo + "'", "Descripcion");
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToString(dataTable.Rows[0][0]);
		}
		return "";
	}

	public string devolverTipoProductoPorID(int id)
	{
		DataTable dataTable = BD.ConsultaVer("Descripcion", "TiposProductos", ("TipoProductoID = " + Conversions.ToString(id)) ?? "", "Descripcion");
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToString(dataTable.Rows[0][0]);
		}
		return "";
	}

	public DataTable devolverTiposProductosPorDescripcion1()
	{
		return BD.ConsultaVer("TipoProductoID,Descripcion", "TiposProductos", "", "Descripcion");
	}

	public DataTable devolverTiposProductosPorDescripcionXConfig()
	{
		return BD.ConsultaVer("TipoProductoID,Descripcion", "TiposProductos", "ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID), "Descripcion");
	}

	public bool devolverManejaICE()
	{
		if (TipoProductoID == 0)
		{
			return false;
		}
		DataTable dataTable = BD.ConsultaVer("DocumentoSector", "TiposProductos", "TiposProductos.TipoProductoID=" + Conversions.ToString(TipoProductoID));
		if (dataTable.Rows.Count > 0)
		{
			if (Operators.ConditionalCompareObjectEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0), clsFactElecConfig.FactSectores.ICE, TextCompare: false))
			{
				return true;
			}
			return false;
		}
		return false;
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("TiposProductos.TipoProductoID,TiposProductos.Descripcion,Impresoras.Nombre,TiposProductos.ManejarStock  ", "TiposProductos left join Impresoras on TiposProductos.ImpresoraID =  Impresoras.ImpresoraID  ", "", "Orden");
	}

	public DataTable Devolver(string search, string field)
	{
		return BD.ConsultaVer("* from (TiposProductos.TipoProductoID,TiposProductos.Descripcion,Impresoras.Nombre,TiposProductos.ManejarStock  ", "TiposProductos left join Impresoras on TiposProductos.ImpresoraID =  Impresoras.ImpresoraID  ) as tab1", (field + " " + ((field.Contains("as date") | field.Contains("CDate")) ? (VariableGeneral.ArmarFecha(Conversions.ToDate(search)) + "))") : search)) ?? "");
	}

	public DataTable devolverTipoProductosPorDescripcion()
	{
		return BD.ConsultaVer("TipoProductoID,Descripcion", "TiposProductos", "1=1", "Descripcion");
	}

	public int Modificar(BD_SQL bd1 = null)
	{
		int result;
		try
		{
			if (bd1 != null)
			{
				bd1.ConsultaModificar("TiposProductos", ("Descripcion='" + Descripcion + "',codigo='" + codigo + "',orden=" + Conversions.ToString(orden) + ",ImpresoraID=" + VariableGeneral.DevolverValorLlaveForanea(ImpresoraID) + ",FamiliaID =" + VariableGeneral.DevolverValorLlaveForanea(FamiliaID) + ",AlmacenID =" + Conversions.ToString(AlmacenID) + ",TipoUsuarioID =" + Conversions.ToString(TipoUsuarioID) + ",ConfiguracionID =" + Conversions.ToString(ConfiguracionID) + ",DocumentoSector =" + Conversions.ToString(DocumentoSector) + ",ManejarStock=" + VariableGeneral.armarBolean(ManejarStock) + ",kitchenDisplayID =" + Conversions.ToString(kitchenDisplayID)) ?? "", "TipoProductoID=" + TipoProductoID);
			}
			else
			{
				BD.ConsultaModificar("TiposProductos", ("Descripcion='" + Descripcion + "',codigo='" + codigo + "',orden=" + Conversions.ToString(orden) + ",ImpresoraID=" + VariableGeneral.DevolverValorLlaveForanea(ImpresoraID) + ",FamiliaID =" + VariableGeneral.DevolverValorLlaveForanea(FamiliaID) + ",AlmacenID =" + Conversions.ToString(AlmacenID) + ",TipoUsuarioID =" + Conversions.ToString(TipoUsuarioID) + ",ConfiguracionID =" + Conversions.ToString(ConfiguracionID) + ",DocumentoSector =" + Conversions.ToString(DocumentoSector) + ",ManejarStock=" + VariableGeneral.armarBolean(ManejarStock) + ",kitchenDisplayID =" + Conversions.ToString(kitchenDisplayID)) ?? "", "TipoProductoID=" + TipoProductoID);
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
		int result;
		try
		{
			if (bd1 != null)
			{
				if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					TipoProductoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(bd1.ConsultaVer("max(TipoProductoID)", "TiposProductos").Rows[0][0]), 0), 1));
					bd1.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(TipoProductoID) + ",'" + Descripcion + "',", VariableGeneral.DevolverValorLlaveForanea(ImpresoraID), ","), VariableGeneral.armarBolean(ManejarStock), ",'", codigo, "',", VariableGeneral.DevolverValorLlaveForanea(FamiliaID), ",", Conversions.ToString(orden), ", ", Conversions.ToString(AlmacenID), ",", Conversions.ToString(TipoUsuarioID), ",", Conversions.ToString(ConfiguracionID), ",", Conversions.ToString(kitchenDisplayID), ",", Conversions.ToString(DocumentoSector)), "TiposProductos(TipoProductoID,Descripcion,ImpresoraID ,ManejarStock,Codigo,FamiliaID,orden,AlmacenID,TipoUsuarioID,ConfiguracionID,kitchenDisplayID,DocumentoSector )", ref TipoProductoID);
					result = TipoProductoID;
				}
				else
				{
					bd1.ConsultaInsertar(string.Concat(string.Concat("'" + Descripcion + "',", VariableGeneral.DevolverValorLlaveForanea(ImpresoraID), ","), VariableGeneral.armarBolean(ManejarStock), ",'", codigo, "',", VariableGeneral.DevolverValorLlaveForanea(FamiliaID), ",", Conversions.ToString(orden), ", ", Conversions.ToString(AlmacenID), ",", Conversions.ToString(TipoUsuarioID), ",", Conversions.ToString(ConfiguracionID), ",", Conversions.ToString(kitchenDisplayID), ",", Conversions.ToString(DocumentoSector)), "TiposProductos (Descripcion,ImpresoraID ,ManejarStock,Codigo,FamiliaID,orden,AlmacenID,TipoUsuarioID,ConfiguracionID,kitchenDisplayID,DocumentoSector)", ref TipoProductoID);
					result = TipoProductoID;
				}
			}
			else if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
			{
				TipoProductoID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(TipoProductoID)", "TiposProductos").Rows[0][0]), 0), 1));
				BD.ConsultaInsertar(string.Concat(string.Concat(Conversions.ToString(TipoProductoID) + ",'" + Descripcion + "',", VariableGeneral.DevolverValorLlaveForanea(ImpresoraID), ","), VariableGeneral.armarBolean(ManejarStock), ",'", codigo, "',", VariableGeneral.DevolverValorLlaveForanea(FamiliaID), ",", Conversions.ToString(orden), ", ", Conversions.ToString(AlmacenID), ",", Conversions.ToString(TipoUsuarioID), ",", Conversions.ToString(ConfiguracionID), ",", Conversions.ToString(kitchenDisplayID), ",", Conversions.ToString(DocumentoSector)), "TiposProductos(TipoProductoID,Descripcion,ImpresoraID ,ManejarStock,Codigo,FamiliaID,orden,AlmacenID,TipoUsuarioID,ConfiguracionID,kitchenDisplayID,DocumentoSector)");
				TipoProductoID = Conversions.ToInteger(BD.ConsultaVer("max(TipoProductoID)", "TiposProductos").Rows[0][0]);
				result = TipoProductoID;
			}
			else
			{
				BD.ConsultaInsertar3(string.Concat(string.Concat("'" + Descripcion + "',", VariableGeneral.DevolverValorLlaveForanea(ImpresoraID), ","), VariableGeneral.armarBolean(ManejarStock), ",'", codigo, "',", VariableGeneral.DevolverValorLlaveForanea(FamiliaID), ",", Conversions.ToString(orden), ", ", Conversions.ToString(AlmacenID), ",", Conversions.ToString(TipoUsuarioID), ",", Conversions.ToString(ConfiguracionID), ",", Conversions.ToString(kitchenDisplayID), ",", Conversions.ToString(DocumentoSector)), "TiposProductos (Descripcion,ImpresoraID ,ManejarStock,Codigo,FamiliaID,orden,AlmacenID,TipoUsuarioID,ConfiguracionID,kitchenDisplayID,DocumentoSector)", ref TipoProductoID);
				result = TipoProductoID;
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
			if (BD.ConsultaEliminar("TiposProductos", "TipoProductoID = " + TipoProductoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar TiposProductos, se encuentra en uso");
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
