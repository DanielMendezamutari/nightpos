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
public class parametricasDto : INotifyPropertyChanged
{
	private int codigoClasificadorField;

	private bool codigoClasificadorFieldSpecified;

	private string descripcionField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public int codigoClasificador
	{
		get
		{
			return codigoClasificadorField;
		}
		set
		{
			codigoClasificadorField = value;
			RaisePropertyChanged("codigoClasificador");
		}
	}

	[XmlIgnore]
	public bool codigoClasificadorSpecified
	{
		get
		{
			return codigoClasificadorFieldSpecified;
		}
		set
		{
			codigoClasificadorFieldSpecified = value;
			RaisePropertyChanged("codigoClasificadorSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public string descripcion
	{
		get
		{
			return descripcionField;
		}
		set
		{
			descripcionField = value;
			RaisePropertyChanged("descripcion");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
