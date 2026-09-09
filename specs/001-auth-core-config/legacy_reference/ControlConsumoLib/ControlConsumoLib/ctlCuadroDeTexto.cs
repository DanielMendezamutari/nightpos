using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlCuadroDeTexto
{
	private readonly clsCuadroDeTexto cls;

	public ctlCuadroDeTexto()
	{
		cls = new clsCuadroDeTexto();
	}

	public bool Validar(int ID, string Campo, string CampoValor, string CampoID, string Tabla)
	{
		cls._ID = ID;
		cls._Campo = Campo;
		cls._CampoValor = CampoValor;
		cls._CampoID = CampoID;
		cls._Tabla = Tabla;
		if (ID == 0)
		{
			return Conversions.ToBoolean(cls.Validar());
		}
		if (Conversions.ToBoolean(cls.EsMio()))
		{
			return false;
		}
		return Conversions.ToBoolean(cls.Validar());
	}
}
