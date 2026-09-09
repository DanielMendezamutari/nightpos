using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElecNotaCredito;

[Serializable]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public class solicitudAnulacion : solicitudRecepcion
{
	private int codigoMotivoField;

	private string cufField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public int codigoMotivo
	{
		get
		{
			return codigoMotivoField;
		}
		set
		{
			codigoMotivoField = value;
			RaisePropertyChanged("codigoMotivo");
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
