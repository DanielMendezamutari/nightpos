using System.Data;

namespace ControlConsumoLib;

public class ctlDescuentos
{
	private readonly clsDescuentos clsDesc;

	public ctlDescuentos()
	{
		clsDesc = new clsDescuentos();
	}

	public int GetDescuentoID()
	{
		return clsDesc._DescuentoID;
	}

	public void SetDescuentoID(int ID)
	{
		clsDesc._DescuentoID = ID;
	}

	public DataTable DevolverDescuentos()
	{
		return clsDesc.Devolver();
	}

	public void GuardarDescuentos(string Nombre, double Porcentaje, double MinimoMonto, double MaximoMonto, string Observacion, bool Activo, string AvisoCajero)
	{
		clsDesc._Nombre = Nombre;
		clsDesc._Porcentaje = Porcentaje;
		clsDesc._MinimoMonto = MinimoMonto;
		clsDesc._MaximoMonto = MaximoMonto;
		clsDesc._Observacion = Observacion;
		clsDesc._Activo = Activo;
		clsDesc._AvisoCajero = AvisoCajero;
		if (clsDesc._DescuentoID == 0)
		{
			clsDesc.Insertar();
		}
		else
		{
			clsDesc.Modificar();
		}
	}

	public void EliminarDescuentos()
	{
		clsDesc.Eliminar();
	}

	public DataTable DevolverDescuentosCombo()
	{
		return clsDesc.DevolverDescuentos();
	}
}
