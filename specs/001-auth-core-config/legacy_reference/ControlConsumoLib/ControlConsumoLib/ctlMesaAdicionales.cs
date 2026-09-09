using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlMesaAdicionales
{
	private readonly clsMesaAdicionales clsPar;

	public ctlMesaAdicionales()
	{
		clsPar = new clsMesaAdicionales();
	}

	public int GetMesaAdicionalID()
	{
		return clsPar._MesaAdicionalID;
	}

	public void SetMesaAdicionalID(int ID)
	{
		clsPar._MesaAdicionalID = ID;
	}

	public clsMesaAdicionales LlenarClase()
	{
		clsPar.llenarclase();
		return clsPar;
	}

	public DataTable devolverParaLLevar(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsPar.Devolver();
		}
		return clsPar.Devolver(search, field);
	}

	public DataTable devolverParaLLevar()
	{
		return clsPar.Devolver();
	}

	public void GuardarMesaAdicionales(string Nombre, string Descripcion)
	{
		clsPar._Nombre = Nombre;
		clsPar._Descripcion = Descripcion;
		if (clsPar._MesaAdicionalID == 0)
		{
			clsPar.Insertar();
		}
		else
		{
			clsPar.Modificar();
		}
	}

	public void EliminarParaLlevar()
	{
		clsPar.Eliminar();
	}

	public string GetNombre()
	{
		return clsPar._Nombre;
	}

	public string GetDescripcion()
	{
		return clsPar._Descripcion;
	}

	public bool ToreturnMesasAdicionalesPorID()
	{
		DataTable dataTable = clsPar.ToreturnMesasAdicionalesPorID();
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		clsPar._MesaAdicionalID = Conversions.ToInteger(dataTable.Rows[0]["MesaAdicionalID"]);
		clsPar._Nombre = Conversions.ToString(dataTable.Rows[0]["Nombre"]);
		clsPar._Descripcion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"]), ""));
		return true;
	}
}
