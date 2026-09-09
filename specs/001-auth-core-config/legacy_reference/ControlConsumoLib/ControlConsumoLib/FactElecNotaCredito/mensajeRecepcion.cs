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
public class mensajeRecepcion : mensajeServicio
{
	private bool advertenciaField;

	private bool advertenciaFieldSpecified;

	private int numeroArchivoField;

	private bool numeroArchivoFieldSpecified;

	private int numeroDetalleField;

	private bool numeroDetalleFieldSpecified;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public bool advertencia
	{
		get
		{
			return advertenciaField;
		}
		set
		{
			advertenciaField = value;
			RaisePropertyChanged("advertencia");
		}
	}

	[XmlIgnore]
	public bool advertenciaSpecified
	{
		get
		{
			return advertenciaFieldSpecified;
		}
		set
		{
			advertenciaFieldSpecified = value;
			RaisePropertyChanged("advertenciaSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public int numeroArchivo
	{
		get
		{
			return numeroArchivoField;
		}
		set
		{
			numeroArchivoField = value;
			RaisePropertyChanged("numeroArchivo");
		}
	}

	[XmlIgnore]
	public bool numeroArchivoSpecified
	{
		get
		{
			return numeroArchivoFieldSpecified;
		}
		set
		{
			numeroArchivoFieldSpecified = value;
			RaisePropertyChanged("numeroArchivoSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public int numeroDetalle
	{
		get
		{
			return numeroDetalleField;
		}
		set
		{
			numeroDetalleField = value;
			RaisePropertyChanged("numeroDetalle");
		}
	}

	[XmlIgnore]
	public bool numeroDetalleSpecified
	{
		get
		{
			return numeroDetalleFieldSpecified;
		}
		set
		{
			numeroDetalleFieldSpecified = value;
			RaisePropertyChanged("numeroDetalleSpecified");
		}
	}
}
