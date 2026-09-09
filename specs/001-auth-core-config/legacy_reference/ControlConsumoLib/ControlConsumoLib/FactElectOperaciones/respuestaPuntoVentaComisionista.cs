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
public class respuestaPuntoVentaComisionista : modelDto
{
	private int codigoPuntoVentaField;

	private bool codigoPuntoVentaFieldSpecified;

	private mensajeServicio[] mensajesListField;

	private bool transaccionField;

	private bool transaccionFieldSpecified;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public int codigoPuntoVenta
	{
		get
		{
			return codigoPuntoVentaField;
		}
		set
		{
			codigoPuntoVentaField = value;
			RaisePropertyChanged("codigoPuntoVenta");
		}
	}

	[XmlIgnore]
	public bool codigoPuntoVentaSpecified
	{
		get
		{
			return codigoPuntoVentaFieldSpecified;
		}
		set
		{
			codigoPuntoVentaFieldSpecified = value;
			RaisePropertyChanged("codigoPuntoVentaSpecified");
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
