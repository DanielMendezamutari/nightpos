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
public class actividadesDocumentoSectorDto : modelDto
{
	private string codigoActividadField;

	private int codigoDocumentoSectorField;

	private bool codigoDocumentoSectorFieldSpecified;

	private string tipoDocumentoSectorField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public string codigoActividad
	{
		get
		{
			return codigoActividadField;
		}
		set
		{
			codigoActividadField = value;
			RaisePropertyChanged("codigoActividad");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public int codigoDocumentoSector
	{
		get
		{
			return codigoDocumentoSectorField;
		}
		set
		{
			codigoDocumentoSectorField = value;
			RaisePropertyChanged("codigoDocumentoSector");
		}
	}

	[XmlIgnore]
	public bool codigoDocumentoSectorSpecified
	{
		get
		{
			return codigoDocumentoSectorFieldSpecified;
		}
		set
		{
			codigoDocumentoSectorFieldSpecified = value;
			RaisePropertyChanged("codigoDocumentoSectorSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public string tipoDocumentoSector
	{
		get
		{
			return tipoDocumentoSectorField;
		}
		set
		{
			tipoDocumentoSectorField = value;
			RaisePropertyChanged("tipoDocumentoSector");
		}
	}
}
