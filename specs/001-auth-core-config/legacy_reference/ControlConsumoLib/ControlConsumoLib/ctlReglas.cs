using System;
using System.Data;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlReglas
{
	public enum Dias
	{
		Lunes = 2,
		Martes = 3,
		Miercoles = 4,
		Jueves = 5,
		Viernes = 6,
		Sabado = 7,
		Domingo = 1
	}

	private readonly clsReglas clsDesc;

	public ctlReglas()
	{
		clsDesc = new clsReglas();
	}

	public DataTable devolverDias()
	{
		string[] names = Enum.GetNames(typeof(Dias));
		Array values = Enum.GetValues(typeof(Dias));
		DataTable dataTable = new DataTable();
		dataTable.Columns.Add("Value", typeof(int));
		dataTable.Columns.Add("Key", typeof(string));
		for (int i = 0; i < names.Length; i = checked(i + 1))
		{
			DataRow dataRow = dataTable.NewRow();
			dataRow["Value"] = Conversions.ToInteger(values.GetValue(i));
			dataRow["Key"] = names[i];
			dataTable.Rows.Add(dataRow);
		}
		return dataTable;
	}

	public int GetReglaID()
	{
		return clsDesc._ReglaID;
	}

	public void SetReglaID(int ID)
	{
		clsDesc._ReglaID = ID;
	}

	public DataTable DevolverReglas()
	{
		DataTable dataTable = clsDesc.devolver();
		string[] names = Enum.GetNames(typeof(Dias));
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				dataTable.Rows[i]["DiaSemana"] = names[Conversions.ToInteger(Operators.SubtractObject(dataTable.Rows[i]["Dia"], 1))];
			}
			return dataTable;
		}
	}

	public void GuardarReglas(int Dia, DateTime HoraIni, DateTime HoraFin, int productoId, bool habilitado)
	{
		clsDesc._Dia = Dia;
		clsDesc._HoraIni = HoraIni;
		clsDesc._HoraFin = HoraFin;
		clsDesc._ProductoID = productoId;
		clsDesc._Habilitado = habilitado;
		if (clsDesc._ReglaID == 0)
		{
			clsDesc.Insertar();
		}
		else
		{
			clsDesc.Modificar();
		}
	}

	public void EliminarReglas()
	{
		clsDesc.Eliminar();
	}

	public int GetproductoVigenteEnEstehorario()
	{
		return clsDesc.GetproductoVigenteEnEstehorario();
	}

	public DataTable DevolverReglasCombo()
	{
		return clsDesc.DevolverReglas();
	}
}
