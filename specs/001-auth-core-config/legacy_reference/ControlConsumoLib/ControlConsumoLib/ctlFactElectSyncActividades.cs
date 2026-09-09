using System.Data;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlFactElectSyncActividades
{
	private readonly clsFactElectSyncActividades1 clsFact;

	public ctlFactElectSyncActividades()
	{
		clsFact = new clsFactElectSyncActividades1();
	}

	public int GetCodigo()
	{
		return Conversions.ToInteger(clsFact._Codigo1);
	}

	public void SetCodigo(int Codigo)
	{
		clsFact._Codigo1 = Conversions.ToString(Codigo);
	}

	public int GetID()
	{
		return clsFact._ID;
	}

	public void SetID(int ID)
	{
		clsFact._ID = ID;
	}

	public void GuardarFactElectActividades1(string Codigo, string Descripcion, string tipo)
	{
		clsFact._Codigo1 = Codigo;
		clsFact._Descripcion = Descripcion;
		clsFact._tipo = tipo;
		clsFact.Insertar();
	}

	public DataTable Devolver()
	{
		return clsFact.Devolver();
	}

	public DataTable DevolverConCodigo()
	{
		return clsFact.DevolverConCodigo();
	}

	public void EliminarFactElectActividades()
	{
		clsFact.EliminarTodo();
	}
}
