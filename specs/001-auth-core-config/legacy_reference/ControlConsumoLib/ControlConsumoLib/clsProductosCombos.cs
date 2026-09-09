using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsProductosCombos
{
	public void guardar(int prodID, int DetalleCuentaID, int Cant, double PrecioUnit)
	{
		if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
		{
			int num = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ProductoComboID)", "ProductosCombos").Rows[0][0]), 0), 1));
			BD.ConsultaInsertar(Conversions.ToString(num) + "," + Conversions.ToString(prodID) + "," + Conversions.ToString(DetalleCuentaID) + "," + Conversions.ToString(Cant) + "," + Conversion.Str(PrecioUnit), "ProductosCombos(ProductoComboID,ProductoID,DetalleCuentaID, Cant, PrecioUni)");
		}
		else
		{
			string data = Conversions.ToString(prodID) + "," + Conversions.ToString(DetalleCuentaID) + "," + Conversions.ToString(Cant) + "," + Conversion.Str(PrecioUnit);
			int id = 0;
			BD.ConsultaInsertar3(data, "ProductosCombos(ProductoID,DetalleCuentaID, Cant, PrecioUni)", ref id);
		}
	}
}
