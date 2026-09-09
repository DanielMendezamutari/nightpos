using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectOperaciones;

[Serializable]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public class mensajeServicio : modelDto
{
	private int codigoField;

	private bool codigoFieldSpecified;

	private string descripcionField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public int codigo
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

	[XmlIgnore]
	public bool codigoSpecified
	{
		get
		{
			return codigoFieldSpecified;
		}
		set
		{
			codigoFieldSpecified = value;
			RaisePropertyChanged("codigoSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public string descripcion
	{
		get
		{
			return descripcionField;
		}
		set
		{
			descripcionField = value;
			RaisePropertyChanged("descripcion");
		}
	}
}
