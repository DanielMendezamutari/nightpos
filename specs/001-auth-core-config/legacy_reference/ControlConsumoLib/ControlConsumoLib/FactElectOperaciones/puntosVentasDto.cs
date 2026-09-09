using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectOperaciones;

[Serializable]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public class puntosVentasDto : INotifyPropertyChanged
{
	private int codigoPuntoVentaField;

	private bool codigoPuntoVentaFieldSpecified;

	private string nombrePuntoVentaField;

	private string tipoPuntoVentaField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public string nombrePuntoVenta
	{
		get
		{
			return nombrePuntoVentaField;
		}
		set
		{
			nombrePuntoVentaField = value;
			RaisePropertyChanged("nombrePuntoVenta");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
	public string tipoPuntoVenta
	{
		get
		{
			return tipoPuntoVentaField;
		}
		set
		{
			tipoPuntoVentaField = value;
			RaisePropertyChanged("tipoPuntoVenta");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
