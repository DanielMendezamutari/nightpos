using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectSync;

[Serializable]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public class respuestaFechaHora : respuestaConfiguracion
{
	private string fechaHoraField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public string fechaHora
	{
		get
		{
			return fechaHoraField;
		}
		set
		{
			fechaHoraField = value;
			RaisePropertyChanged("fechaHora");
		}
	}
}
