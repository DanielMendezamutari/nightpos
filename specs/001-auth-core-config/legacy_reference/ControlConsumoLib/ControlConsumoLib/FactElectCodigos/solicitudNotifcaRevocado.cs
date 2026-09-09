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
public class solicitudNotifcaRevocado : INotifyPropertyChanged
{
	private string certificadoField;

	private int codigoAmbienteField;

	private string codigoSistemaField;

	private int codigoSucursalField;

	private string cuisField;

	private DateTime? fechaRevocacionField;

	private bool fechaRevocacionFieldSpecified;

	private long nitField;

	private string razonRevocacionField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public string certificado
	{
		get
		{
			return certificadoField;
		}
		set
		{
			certificadoField = value;
			RaisePropertyChanged("certificado");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 5)]
	public DateTime? fechaRevocacion
	{
		get
		{
			return fechaRevocacionField;
		}
		set
		{
			fechaRevocacionField = value;
			RaisePropertyChanged("fechaRevocacion");
		}
	}

	[XmlIgnore]
	public bool fechaRevocacionSpecified
	{
		get
		{
			return fechaRevocacionFieldSpecified;
		}
		set
		{
			fechaRevocacionFieldSpecified = value;
			RaisePropertyChanged("fechaRevocacionSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 6)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 7)]
	public string razonRevocacion
	{
		get
		{
			return razonRevocacionField;
		}
		set
		{
			razonRevocacionField = value;
			RaisePropertyChanged("razonRevocacion");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
