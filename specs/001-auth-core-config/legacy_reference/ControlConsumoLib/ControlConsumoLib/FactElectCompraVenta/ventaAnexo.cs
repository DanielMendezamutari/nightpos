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
public class ventaAnexo : modelDto
{
	private string codigoField;

	private string codigoProductoField;

	private long codigoProductoSinField;

	private string tipoCodigoField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public string codigo
	{
		get
		{
			return codigoField;
		}
		set
		{
			codigoField = value;
			RaisePropertyChanged("codigo");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public string codigoProducto
	{
		get
		{
			return codigoProductoField;
		}
		set
		{
			codigoProductoField = value;
			RaisePropertyChanged("codigoProducto");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public long codigoProductoSin
	{
		get
		{
			return codigoProductoSinField;
		}
		set
		{
			codigoProductoSinField = value;
			RaisePropertyChanged("codigoProductoSin");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 3)]
	public string tipoCodigo
	{
		get
		{
			return tipoCodigoField;
		}
		set
		{
			tipoCodigoField = value;
			RaisePropertyChanged("tipoCodigo");
		}
	}
}
