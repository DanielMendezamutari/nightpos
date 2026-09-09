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
public class eventosSignificativosDto : INotifyPropertyChanged
{
	private int codigoEventoField;

	private bool codigoEventoFieldSpecified;

	private long codigoRecepcionEventoSignificativoField;

	private bool codigoRecepcionEventoSignificativoFieldSpecified;

	private string descripcionField;

	private string fechaFinField;

	private string fechaInicioField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public int codigoEvento
	{
		get
		{
			return codigoEventoField;
		}
		set
		{
			codigoEventoField = value;
			RaisePropertyChanged("codigoEvento");
		}
	}

	[XmlIgnore]
	public bool codigoEventoSpecified
	{
		get
		{
			return codigoEventoFieldSpecified;
		}
		set
		{
			codigoEventoFieldSpecified = value;
			RaisePropertyChanged("codigoEventoSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
	public long codigoRecepcionEventoSignificativo
	{
		get
		{
			return codigoRecepcionEventoSignificativoField;
		}
		set
		{
			codigoRecepcionEventoSignificativoField = value;
			RaisePropertyChanged("codigoRecepcionEventoSignificativo");
		}
	}

	[XmlIgnore]
	public bool codigoRecepcionEventoSignificativoSpecified
	{
		get
		{
			return codigoRecepcionEventoSignificativoFieldSpecified;
		}
		set
		{
			codigoRecepcionEventoSignificativoFieldSpecified = value;
			RaisePropertyChanged("codigoRecepcionEventoSignificativoSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 3)]
	public string fechaFin
	{
		get
		{
			return fechaFinField;
		}
		set
		{
			fechaFinField = value;
			RaisePropertyChanged("fechaFin");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 4)]
	public string fechaInicio
	{
		get
		{
			return fechaInicioField;
		}
		set
		{
			fechaInicioField = value;
			RaisePropertyChanged("fechaInicio");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
