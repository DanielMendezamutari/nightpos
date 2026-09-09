using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Serialization;

namespace ControlConsumoLib;

[Serializable]
[GeneratedCode("xsd", "4.8.3928.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(AnonymousType = true)]
[XmlRoot(Namespace = "", IsNullable = false)]
public class facturaComputarizadaCompraVentaBon
{
	private facturaComputarizadaCompraVentaBonCabecera cabeceraField;

	private facturaComputarizadaCompraVentaBonDetalle[] detalleField;

	public facturaComputarizadaCompraVentaBonCabecera cabecera
	{
		get
		{
			return cabeceraField;
		}
		set
		{
			cabeceraField = value;
		}
	}

	[XmlElement("detalle")]
	public facturaComputarizadaCompraVentaBonDetalle[] detalle
	{
		get
		{
			return detalleField;
		}
		set
		{
			detalleField = value;
		}
	}
}
