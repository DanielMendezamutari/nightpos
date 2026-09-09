using System;
using System.Data;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsBorrados
{
	private int BorradoID;

	private DateTime Fecha;

	private int DetalleCuentaID;

	private double Precio;

	private double Cantidad;

	private string Comentarios;

	private string PC;

	public int _BorradoID
	{
		get
		{
			return BorradoID;
		}
		set
		{
			BorradoID = value;
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

	public int _DetalleCuentaID
	{
		get
		{
			return DetalleCuentaID;
		}
		set
		{
			DetalleCuentaID = value;
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

	public double _Cantidad
	{
		get
		{
			return Cantidad;
		}
		set
		{
			Cantidad = value;
		}
	}

	public string _Comentarios
	{
		get
		{
			return Comentarios;
		}
		set
		{
			Comentarios = value;
		}
	}

	public string _PC
	{
		get
		{
			return PC;
		}
		set
		{
			PC = value;
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Borrados.BorradoID, Borrados.Fecha,Borrados.DetalleCuentaID,Borrados.Precio,Borrados.Cantidad, Comentarios,PC", "Borrados ");
	}

	public int Insertar()
	{
		BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(VariableGeneral.ArmarFecha(Fecha) + ",", Conversions.ToString(DetalleCuentaID), ","), Conversion.Str(Precio), ","), Conversion.Str(Cantidad), ",'", Comentarios, "','", MyProject.Computer.Name, "'"), "Borrados(Fecha,DetalleCuentaID,Precio,Cantidad,Comentarios,PC)", ref BorradoID);
		return BorradoID;
	}

	public void Modificar()
	{
		BD.ConsultaModificar("Borrados", "Fecha=" + VariableGeneral.ArmarFecha(Fecha) + ",DetalleCuentaID=" + Conversions.ToString(DetalleCuentaID) + ",Precio=" + Conversion.Str(Precio) + ",Cantidad=" + Conversion.Str(Cantidad) + ",PC='" + MyProject.Computer.Name + "',Comentarios='" + Comentarios + "',flagSync=NULL", "BorradoID= " + BorradoID);
	}

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Borrados", "BorradoID = " + BorradoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar el Borrado, se encuentra en uso");
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
