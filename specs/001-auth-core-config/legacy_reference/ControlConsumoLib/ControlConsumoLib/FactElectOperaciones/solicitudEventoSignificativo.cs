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
public class solicitudEventoSignificativo : INotifyPropertyChanged
{
	private int codigoAmbienteField;

	private int codigoMotivoEventoField;

	private int? codigoPuntoVentaField;

	private bool codigoPuntoVentaFieldSpecified;

	private string codigoSistemaField;

	private int codigoSucursalField;

	private string cufdField;

	private string cufdEventoField;

	private string cuisField;

	private string descripcionField;

	private DateTime fechaHoraFinEventoField;

	private DateTime fechaHoraInicioEventoField;

	private long nitField;

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
	public int codigoMotivoEvento
	{
		get
		{
			return codigoMotivoEventoField;
		}
		set
		{
			codigoMotivoEventoField = value;
			RaisePropertyChanged("codigoMotivoEvento");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 2)]
	public int? codigoPuntoVenta
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 3)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 4)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 5)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 6)]
	public string cufdEvento
	{
		get
		{
			return cufdEventoField;
		}
		set
		{
			cufdEventoField = value;
			RaisePropertyChanged("cufdEvento");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 7)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 8)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 9)]
	public DateTime fechaHoraFinEvento
	{
		get
		{
			return fechaHoraFinEventoField;
		}
		set
		{
			fechaHoraFinEventoField = value;
			RaisePropertyChanged("fechaHoraFinEvento");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 10)]
	public DateTime fechaHoraInicioEvento
	{
		get
		{
			return fechaHoraInicioEventoField;
		}
		set
		{
			fechaHoraInicioEventoField = value;
			RaisePropertyChanged("fechaHoraInicioEvento");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 11)]
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

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
