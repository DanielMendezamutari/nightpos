using System.Data;

namespace ControlConsumoLib;

public class ctlFactElectProductosServicios
{
	private readonly clsFactElectProductosServicios clsFact;

	public ctlFactElectProductosServicios()
	{
		clsFact = new clsFactElectProductosServicios();
	}

	public string GetCodigo()
	{
		return clsFact._Codigo1;
	}

	public void SetCodigo(string ID)
	{
		clsFact._Codigo1 = ID;
	}

	public DataTable DevolverXActividad(string codigoactividad)
	{
		return clsFact.DevolverXActividad(codigoactividad);
	}

	public DataTable DevolverXActividadConCodigo(string codigoactividad)
	{
		return clsFact.DevolverXActividadConCodigo(codigoactividad);
	}

	public void GuardarFactElectProductosServicios1(string Codigo, string Descripcion, string CodigoActividad)
	{
		clsFact._Codigo1 = Codigo;
		clsFact._Descripcion = Descripcion;
		clsFact._codigoActividad1 = CodigoActividad;
		clsFact.Insertar();
	}

	public void EliminarFactElectProductosServicios()
	{
		clsFact.EliminarTodo();
	}
}
