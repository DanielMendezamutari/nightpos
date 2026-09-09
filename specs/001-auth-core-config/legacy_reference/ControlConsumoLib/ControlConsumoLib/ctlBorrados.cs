using System;

namespace ControlConsumoLib;

public class ctlBorrados
{
	private readonly clsBorrados clsBorr;

	public ctlBorrados()
	{
		clsBorr = new clsBorrados();
	}

	public int GetBorradoID()
	{
		return clsBorr._BorradoID;
	}

	public void SetBorradoID(int ID)
	{
		clsBorr._BorradoID = ID;
	}

	public void GuardarBorrados(DateTime Fecha, int DetalleCuentaID, double Precio, double Cantidad, string Comentarios)
	{
		clsBorr._Fecha = Fecha;
		clsBorr._DetalleCuentaID = DetalleCuentaID;
		clsBorr._Precio = Precio;
		clsBorr._Cantidad = Cantidad;
		clsBorr._Comentarios = Comentarios;
		if (clsBorr._BorradoID == 0)
		{
			clsBorr.Insertar();
		}
		else
		{
			clsBorr.Modificar();
		}
	}

	public void EliminarBorrados()
	{
		clsBorr.Eliminar();
	}
}
