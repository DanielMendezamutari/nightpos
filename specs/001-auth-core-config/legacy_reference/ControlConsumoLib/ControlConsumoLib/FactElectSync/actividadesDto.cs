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
public class actividadesDto : INotifyPropertyChanged
{
	private string codigoCaebField;

	private string descripcionField;

	private string tipoActividadField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public string codigoCaeb
	{
		get
		{
			return codigoCaebField;
		}
		set
		{
			codigoCaebField = value;
			RaisePropertyChanged("codigoCaeb");
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public string tipoActividad
	{
		get
		{
			return tipoActividadField;
		}
		set
		{
			tipoActividadField = value;
			RaisePropertyChanged("tipoActividad");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
