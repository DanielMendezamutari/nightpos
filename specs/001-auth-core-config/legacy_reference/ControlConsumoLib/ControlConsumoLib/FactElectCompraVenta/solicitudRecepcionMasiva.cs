using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectCompraVenta;

[Serializable]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public class solicitudRecepcionMasiva : solicitudRecepcionFactura
{
	private int cantidadFacturasField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public int cantidadFacturas
	{
		get
		{
			return cantidadFacturasField;
		}
		set
		{
			cantidadFacturasField = value;
			RaisePropertyChanged("cantidadFacturas");
		}
	}
}
