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
public class solicitudPuntoVentaComisionista : INotifyPropertyChanged
{
	private int codigoAmbienteField;

	private int codigoModalidadField;

	private string codigoSistemaField;

	private int codigoSucursalField;

	private string cuisField;

	private string descripcionField;

	private DateTime fechaFinField;

	private DateTime fechaInicioField;

	private long nitField;

	private long nitComisionistaField;

	private string nombrePuntoVentaField;

	private string numeroContratoField;

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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 6)]
	public DateTime fechaFin
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 7)]
	public DateTime fechaInicio
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 8)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 9)]
	public long nitComisionista
	{
		get
		{
			return nitComisionistaField;
		}
		set
		{
			nitComisionistaField = value;
			RaisePropertyChanged("nitComisionista");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 10)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 11)]
	public string numeroContrato
	{
		get
		{
			return numeroContratoField;
		}
		set
		{
			numeroContratoField = value;
			RaisePropertyChanged("numeroContrato");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
