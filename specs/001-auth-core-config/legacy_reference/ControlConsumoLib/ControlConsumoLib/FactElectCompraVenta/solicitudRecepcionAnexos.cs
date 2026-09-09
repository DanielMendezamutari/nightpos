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
public class solicitudRecepcionAnexos : solicitudRecepcion
{
	private ventaAnexo[] anexosListField;

	private string cufField;

	[XmlElement("anexosList", Form = XmlSchemaForm.Unqualified, Order = 0)]
	public ventaAnexo[] anexosList
	{
		get
		{
			return anexosListField;
		}
		set
		{
			anexosListField = value;
			RaisePropertyChanged("anexosList");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public string cuf
	{
		get
		{
			return cufField;
		}
		set
		{
			cufField = value;
			RaisePropertyChanged("cuf");
		}
	}
}
