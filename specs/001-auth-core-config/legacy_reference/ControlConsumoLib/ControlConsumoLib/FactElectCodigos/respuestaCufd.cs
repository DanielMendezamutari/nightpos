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
public class respuestaCufd : modelDto
{
	private string codigoField;

	private string codigoControlField;

	private string direccionField;

	private DateTime fechaVigenciaField;

	private bool fechaVigenciaFieldSpecified;

	private mensajeServicio[] mensajesListField;

	private bool transaccionField;

	private bool transaccionFieldSpecified;

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
	public string codigoControl
	{
		get
		{
			return codigoControlField;
		}
		set
		{
			codigoControlField = value;
			RaisePropertyChanged("codigoControl");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public string direccion
	{
		get
		{
			return direccionField;
		}
		set
		{
			direccionField = value;
			RaisePropertyChanged("direccion");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 3)]
	public DateTime fechaVigencia
	{
		get
		{
			return fechaVigenciaField;
		}
		set
		{
			fechaVigenciaField = value;
			RaisePropertyChanged("fechaVigencia");
		}
	}

	[XmlIgnore]
	public bool fechaVigenciaSpecified
	{
		get
		{
			return fechaVigenciaFieldSpecified;
		}
		set
		{
			fechaVigenciaFieldSpecified = value;
			RaisePropertyChanged("fechaVigenciaSpecified");
		}
	}

	[XmlElement("mensajesList", Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 4)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 5)]
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
