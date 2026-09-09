using System;
using System.Data;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsProductosDeshabilitados
{
	private int DeshabilitadoID;

	private DateTime Fecha;

	private int productoId;

	private int UsuarioID;

	public int _DeshabilitadoID
	{
		get
		{
			return DeshabilitadoID;
		}
		set
		{
			DeshabilitadoID = value;
		}
	}

	public DateTime _Fecha
	{
		get
		{
			return Fecha;
		}
		set
		{
			Fecha = value;
		}
	}

	public int _productoId
	{
		get
		{
			return productoId;
		}
		set
		{
			productoId = value;
		}
	}

	public int _usuarioID
	{
		get
		{
			return UsuarioID;
		}
		set
		{
			UsuarioID = value;
		}
	}

	public clsProductosDeshabilitados()
	{
		Fecha = DateAndTime.Now;
		productoId = 0;
		UsuarioID = 0;
	}

	public int Insertar()
	{
		int result;
		try
		{
			BD.ConsultaInsertar3(string.Concat(VariableGeneral.ArmarFecha(Fecha) + ",", Conversions.ToString(productoId), ",", Conversions.ToString(UsuarioID)), "ProductosDeshabilitados(Fecha,ProductoID,UsuarioID)", ref DeshabilitadoID);
			result = DeshabilitadoID;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			DeshabilitadoID = 0;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int EliminarXprodID()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("ProductosDeshabilitados", "productoId = " + productoId) == 0)
			{
				Interaction.MsgBox("no se puede eliminar, se encuentra en uso");
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

	public DataTable devolver()
	{
		return BD.ConsultaVer("distinct ProductosDeshabilitados.ProductoID,Productos.CodigoPY , Productos.Nombre", "ProductosDeshabilitados INNER JOIN Productos ON ProductosDeshabilitados.ProductoID = Productos.ID", "Borrado=" + VariableGeneral.armarBolean(0), "Nombre");
	}
}
