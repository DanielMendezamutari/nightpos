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
public class solicitudRecepcionPaquete : solicitudRecepcionFactura
{
	private string cafcField;

	private int cantidadFacturasField;

	private long codigoEventoField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public string cafc
	{
		get
		{
			return cafcField;
		}
		set
		{
			cafcField = value;
			RaisePropertyChanged("cafc");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public long codigoEvento
	{
		get
		{
			return codigoEventoField;
		}
		set
		{
			codigoEventoField = value;
			RaisePropertyChanged("codigoEvento");
		}
	}
}
