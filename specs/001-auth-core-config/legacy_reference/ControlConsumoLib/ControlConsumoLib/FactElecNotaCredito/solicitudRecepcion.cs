using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElecNotaCredito;

[Serializable]
[XmlInclude(typeof(solicitudVerificacionEstado))]
[XmlInclude(typeof(solicitudRecepcionFactura))]
[XmlInclude(typeof(solicitudReversionAnulacion))]
[XmlInclude(typeof(solicitudAnulacion))]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public class solicitudRecepcion : modelDto
{
	private int codigoAmbienteField;

	private int codigoDocumentoSectorField;

	private int codigoEmisionField;

	private int codigoModalidadField;

	private int codigoPuntoVentaField;

	private bool codigoPuntoVentaFieldSpecified;

	private string codigoSistemaField;

	private int codigoSucursalField;

	private string cufdField;

	private string cuisField;

	private long nitField;

	private int tipoFacturaDocumentoField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public int codigoAmbiente
	{
		get
		{
			return codigoAmbienteField;
		}
		set
		{
			codigoAmbienteField = value;
			RaisePropertyChanged("codigoAmbiente");
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public int codigoEmision
	{
		get
		{
			return codigoEmisionField;
		}
		set
		{
			codigoEmisionField = value;
			RaisePropertyChanged("codigoEmision");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 3)]
	public int codigoModalidad
	{
		get
		{
			return codigoModalidadField;
		}
		set
		{
			codigoModalidadField = value;
			RaisePropertyChanged("codigoModalidad");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 4)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 5)]
	public string codigoSistema
	{
		get
		{
			return codigoSistemaField;
		}
		set
		{
			codigoSistemaField = value;
			RaisePropertyChanged("codigoSistema");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 6)]
	public int codigoSucursal
	{
		get
		{
			return codigoSucursalField;
		}
		set
		{
			codigoSucursalField = value;
			RaisePropertyChanged("codigoSucursal");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 7)]
	public string cufd
	{
		get
		{
			return cufdField;
		}
		set
		{
			cufdField = value;
			RaisePropertyChanged("cufd");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 8)]
	public string cuis
	{
		get
		{
			return cuisField;
		}
		set
		{
			cuisField = value;
			RaisePropertyChanged("cuis");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 9)]
	public long nit
	{
		get
		{
			return nitField;
		}
		set
		{
			nitField = value;
			RaisePropertyChanged("nit");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 10)]
	public int tipoFacturaDocumento
	{
		get
		{
			return tipoFacturaDocumentoField;
		}
		set
		{
			tipoFacturaDocumentoField = value;
			RaisePropertyChanged("tipoFacturaDocumento");
		}
	}
}
