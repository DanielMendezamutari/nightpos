using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectCodigos;

[Serializable]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public class respuestaCuisMasivo : modelDto
{
	private respuestaListaRegistroCuisSoapDto[] listaRespuestasCuisField;

	private mensajeServicio[] mensajesListField;

	private bool transaccionField;

	private bool transaccionFieldSpecified;

	[XmlElement("listaRespuestasCuis", Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 0)]
	public respuestaListaRegistroCuisSoapDto[] listaRespuestasCuis
	{
		get
		{
			return listaRespuestasCuisField;
		}
		set
		{
			listaRespuestasCuisField = value;
			RaisePropertyChanged("listaRespuestasCuis");
		}
	}

	[XmlElement("mensajesList", Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 1)]
	public mensajeServicio[] mensajesList
	{
		get
		{
			return mensajesListField;
		}
		set
		{
			mensajesListField = value;
			RaisePropertyChanged("mensajesList");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public bool transaccion
	{
		get
		{
			return transaccionField;
		}
		set
		{
			transaccionField = value;
			RaisePropertyChanged("transaccion");
		}
	}

	[XmlIgnore]
	public bool transaccionSpecified
	{
		get
		{
			return transaccionFieldSpecified;
		}
		set
		{
			transaccionFieldSpecified = value;
			RaisePropertyChanged("transaccionSpecified");
		}
	}
}
