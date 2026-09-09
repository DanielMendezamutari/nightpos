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
public class respuestaListaParametricasLeyendas : respuestaConfiguracion
{
	private parametricaLeyendasDto[] listaLeyendasField;

	[XmlElement("listaLeyendas", Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 0)]
	public parametricaLeyendasDto[] listaLeyendas
	{
		get
		{
			return listaLeyendasField;
		}
		set
		{
			listaLeyendasField = value;
			RaisePropertyChanged("listaLeyendas");
		}
	}
}
