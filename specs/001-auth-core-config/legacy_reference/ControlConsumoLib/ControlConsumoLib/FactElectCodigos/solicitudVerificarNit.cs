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
public class solicitudVerificarNit : INotifyPropertyChanged
{
	private int codigoAmbienteField;

	private int codigoModalidadField;

	private string codigoSistemaField;

	private int codigoSucursalField;

	private string cuisField;

	private long nitField;

	private long nitParaVerificacionField;

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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 3)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 4)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 5)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 6)]
	public long nitParaVerificacion
	{
		get
		{
			return nitParaVerificacionField;
		}
		set
		{
			nitParaVerificacionField = value;
			RaisePropertyChanged("nitParaVerificacion");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
