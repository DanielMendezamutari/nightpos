using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsTipoCambio
{
	public double returnTipoCambio()
	{
		DataTable dataTable = BD.ConsultaVer("select TipoCambio from TipoCambio");
		if (dataTable.Rows.Count == 0)
		{
			Interaction.MsgBox("No hay tipo de Cambio");
			return 0.0;
		}
		return Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
	}

	public void guardar(double monto)
	{
		BD.ConsultaModificar("TipoCambio", "TipoCambio=" + Conversion.Str(monto), "1=1");
	}
}
