using System;
using System.Data;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDescuentos
{
	private int DescuentoID;

	private string Nombre;

	private double Porcentaje;

	private double MinimoMonto;

	private double MaximoMonto;

	private string Observacion;

	private bool Activo;

	private string AvisoCajero;

	public int _DescuentoID
	{
		get
		{
			return DescuentoID;
		}
		set
		{
			DescuentoID = value;
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

	public double _Porcentaje
	{
		get
		{
			return Porcentaje;
		}
		set
		{
			Porcentaje = value;
		}
	}

	public double _MinimoMonto
	{
		get
		{
			return MinimoMonto;
		}
		set
		{
			MinimoMonto = value;
		}
	}

	public double _MaximoMonto
	{
		get
		{
			return MaximoMonto;
		}
		set
		{
			MaximoMonto = value;
		}
	}

	public string _Observacion
	{
		get
		{
			return Observacion;
		}
		set
		{
			Observacion = value;
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

	public string _AvisoCajero
	{
		get
		{
			return AvisoCajero;
		}
		set
		{
			AvisoCajero = value;
		}
	}

	public DataTable Devolver()
	{
		return BD.ConsultaVer("Descuentos.DescuentoID,Descuentos.Nombre,Descuentos.Porcentaje,Descuentos.MinimoMonto,Descuentos.MaximoMonto,Descuentos.Observacion,Descuentos.Activo,Descuentos.AvisoCajero", "Descuentos");
	}

	public int Insertar()
	{
		BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat("'" + Nombre + "',", Conversion.Str(Porcentaje), ","), Conversion.Str(MinimoMonto), ","), Conversion.Str(MaximoMonto), ",'", Observacion, "',"), VariableGeneral.armarBolean(Activo), ",'", AvisoCajero, "'"), "Descuentos(Nombre,Porcentaje,MinimoMonto,MaximoMonto,Observacion,Activo,AvisoCajero)", ref DescuentoID);
		return DescuentoID;
	}

	public void Modificar()
	{
		BD.ConsultaModificar("Descuentos", "Nombre='" + Nombre + "',Porcentaje=" + Conversion.Str(Porcentaje) + ",MinimoMonto=" + Conversion.Str(MinimoMonto) + ",MaximoMonto=" + Conversion.Str(MaximoMonto) + ",Observacion='" + Observacion + "',Activo=" + VariableGeneral.armarBolean(Activo) + ",AvisoCajero='" + AvisoCajero + "'", "DescuentoID= " + DescuentoID);
	}

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Descuentos", "DescuentoID = " + DescuentoID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar el Descuento, se encuentra en uso");
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

	public DataTable DevolverDescuentos()
	{
		if (configuration.gMODO_ACCESS == 2)
		{
			return BD.ConsultaVer("DescuentoID,Nombre", "Descuentos", "Activo = " + VariableGeneral.armarBolean(1));
		}
		if (configuration.gMODO_ACCESS == 0)
		{
			return BD.ConsultaVer("DescuentoID,Nombre", "Descuentos", "Activo = " + VariableGeneral.armarBolean(1));
		}
		return BD.ConsultaVer("DescuentoID,Nombre", "Descuentos", "Activo = " + VariableGeneral.armarBolean(1));
	}
}
