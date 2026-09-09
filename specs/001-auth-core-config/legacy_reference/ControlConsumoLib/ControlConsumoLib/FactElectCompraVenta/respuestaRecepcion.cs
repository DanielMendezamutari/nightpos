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
public class respuestaRecepcion : modelDto
{
	private string codigoDescripcionField;

	private int codigoEstadoField;

	private bool codigoEstadoFieldSpecified;

	private string codigoRecepcionField;

	private mensajeRecepcion[] mensajesListField;

	private bool transaccionField;

	private bool transaccionFieldSpecified;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public string codigoDescripcion
	{
		get
		{
			return codigoDescripcionField;
		}
		set
		{
			codigoDescripcionField = value;
			RaisePropertyChanged("codigoDescripcion");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public int codigoEstado
	{
		get
		{
			return codigoEstadoField;
		}
		set
		{
			codigoEstadoField = value;
			RaisePropertyChanged("codigoEstado");
		}
	}

	[XmlIgnore]
	public bool codigoEstadoSpecified
	{
		get
		{
			return codigoEstadoFieldSpecified;
		}
		set
		{
			codigoEstadoFieldSpecified = value;
			RaisePropertyChanged("codigoEstadoSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public string codigoRecepcion
	{
		get
		{
			return codigoRecepcionField;
		}
		set
		{
			codigoRecepcionField = value;
			RaisePropertyChanged("codigoRecepcion");
		}
	}

	[XmlElement("mensajesList", Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 3)]
	public mensajeRecepcion[] mensajesList
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 4)]
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
