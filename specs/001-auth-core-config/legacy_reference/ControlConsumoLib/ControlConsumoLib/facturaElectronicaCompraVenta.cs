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
public class facturaElectronicaCompraVenta
{
	private facturaElectronicaCompraVentaCabecera cabeceraField;

	private facturaElectronicaCompraVentaDetalle[] detalleField;

	private Signature signatureField;

	public facturaElectronicaCompraVentaCabecera cabecera
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
	public facturaElectronicaCompraVentaDetalle[] detalle
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

	[XmlElement(Namespace = "http://www.w3.org/2000/09/xmldsig#")]
	public Signature Signature
	{
		get
		{
			return signatureField;
		}
		set
		{
			signatureField = value;
		}
	}
}
