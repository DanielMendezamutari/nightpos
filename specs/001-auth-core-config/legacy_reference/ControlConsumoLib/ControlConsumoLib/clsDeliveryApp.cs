using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsDeliveryApp
{
	public string getOrderNroByVisitaId(int visitaID, int plataforma, ref int PeYaid)
	{
		DataTable dataTable = BD.ConsultaVer("ORDER_NO, PeYaid", "DeliveryApp", ("PLATAFORMA=" + Conversions.ToString(plataforma) + " and VISITA_ID = " + Conversions.ToString(visitaID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			PeYaid = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), 0));
			return Conversions.ToString(dataTable.Rows[0][0]);
		}
		return Conversions.ToString(0);
	}
}
