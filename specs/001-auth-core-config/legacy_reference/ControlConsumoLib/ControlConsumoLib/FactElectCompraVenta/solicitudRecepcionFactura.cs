using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectCompraVenta;

[Serializable]
[XmlInclude(typeof(solicitudRecepcionMasiva))]
[XmlInclude(typeof(solicitudRecepcionPaquete))]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public class solicitudRecepcionFactura : solicitudRecepcion
{
	private byte[] archivoField;

	private string fechaEnvioField;

	private string hashArchivoField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, DataType = "base64Binary", Order = 0)]
	public byte[] archivo
	{
		get
		{
			return archivoField;
		}
		set
		{
			archivoField = value;
			RaisePropertyChanged("archivo");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public string fechaEnvio
	{
		get
		{
			return fechaEnvioField;
		}
		set
		{
			fechaEnvioField = value;
			RaisePropertyChanged("fechaEnvio");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public string hashArchivo
	{
		get
		{
			return hashArchivoField;
		}
		set
		{
			hashArchivoField = value;
			RaisePropertyChanged("hashArchivo");
		}
	}
}
