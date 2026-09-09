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
public class productosDto : INotifyPropertyChanged
{
	private string codigoActividadField;

	private long codigoProductoField;

	private bool codigoProductoFieldSpecified;

	private string descripcionProductoField;

	private string[] nandinaField;

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
	public long codigoProducto
	{
		get
		{
			return codigoProductoField;
		}
		set
		{
			codigoProductoField = value;
			RaisePropertyChanged("codigoProducto");
		}
	}

	[XmlIgnore]
	public bool codigoProductoSpecified
	{
		get
		{
			return codigoProductoFieldSpecified;
		}
		set
		{
			codigoProductoFieldSpecified = value;
			RaisePropertyChanged("codigoProductoSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public string descripcionProducto
	{
		get
		{
			return descripcionProductoField;
		}
		set
		{
			descripcionProductoField = value;
			RaisePropertyChanged("descripcionProducto");
		}
	}

	[XmlElement("nandina", Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 3)]
	public string[] nandina
	{
		get
		{
			return nandinaField;
		}
		set
		{
			nandinaField = value;
			RaisePropertyChanged("nandina");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
