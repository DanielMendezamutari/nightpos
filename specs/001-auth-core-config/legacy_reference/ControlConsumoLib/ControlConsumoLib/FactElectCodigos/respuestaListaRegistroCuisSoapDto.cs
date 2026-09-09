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
public class respuestaListaRegistroCuisSoapDto : INotifyPropertyChanged
{
	private string codigoField;

	private int codigoPuntoVentaField;

	private bool codigoPuntoVentaFieldSpecified;

	private int codigoSucursalField;

	private bool codigoSucursalFieldSpecified;

	private DateTime fechaVigenciaField;

	private bool fechaVigenciaFieldSpecified;

	private mensajeServicio[] mensajeServicioListField;

	private bool transaccionField;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
	public string codigo
	{
		get
		{
			return codigoField;
		}
		set
		{
			codigoField = value;
			RaisePropertyChanged("codigo");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 1)]
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

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 2)]
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

	[XmlIgnore]
	public bool codigoSucursalSpecified
	{
		get
		{
			return codigoSucursalFieldSpecified;
		}
		set
		{
			codigoSucursalFieldSpecified = value;
			RaisePropertyChanged("codigoSucursalSpecified");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 3)]
	public DateTime fechaVigencia
	{
		get
		{
			return fechaVigenciaField;
		}
		set
		{
			fechaVigenciaField = value;
			RaisePropertyChanged("fechaVigencia");
		}
	}

	[XmlIgnore]
	public bool fechaVigenciaSpecified
	{
		get
		{
			return fechaVigenciaFieldSpecified;
		}
		set
		{
			fechaVigenciaFieldSpecified = value;
			RaisePropertyChanged("fechaVigenciaSpecified");
		}
	}

	[XmlElement("mensajeServicioList", Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 4)]
	public mensajeServicio[] mensajeServicioList
	{
		get
		{
			return mensajeServicioListField;
		}
		set
		{
			mensajeServicioListField = value;
			RaisePropertyChanged("mensajeServicioList");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 5)]
	public bool transaccion
	{
		get
		{
			return transaccionField;
		}
		set
		{
			transaccionField = value;
			RaisePropertyChanged("transaccion");
		}
	}

	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
