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
public class parametricaLeyendasDto : INotifyPropertyChanged
{
	private string codigoActividadField;

	private string descripcionLeyendaField;

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
	public string descripcionLeyenda
	{
		get
		{
			return descripcionLeyendaField;
		}
		set
		{
			descripcionLeyendaField = value;
			RaisePropertyChanged("descripcionLeyenda");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
