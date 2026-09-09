using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Serialization;

namespace ControlConsumoLib;

[Serializable]
[GeneratedCode("xsd", "4.8.3928.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(AnonymousType = true)]
public class notaFiscalElectronicaCreditoDebitoCabecera
{
	private string nitEmisorField;

	private string razonSocialEmisorField;

	private string municipioField;

	private string telefonoField;

	private string numeroNotaCreditoDebitoField;

	private string cufField;

	private string cufdField;

	private string codigoSucursalField;

	private string direccionField;

	private string codigoPuntoVentaField;

	private string fechaEmisionField;

	private string nombreRazonSocialField;

	private string codigoTipoDocumentoIdentidadField;

	private string numeroDocumentoField;

	private string complementoField;

	private string codigoClienteField;

	private string numeroFacturaField;

	private string numeroAutorizacionCufField;

	private string fechaEmisionFacturaField;

	private decimal montoTotalOriginalField;

	private decimal montoTotalDevueltoField;

	private decimal? montoDescuentoCreditoDebitoField;

	private decimal montoEfectivoCreditoDebitoField;

	private string codigoExcepcionField;

	private string leyendaField;

	private string usuarioField;

	private string codigoDocumentoSectorField;

	[XmlElement(DataType = "integer")]
	public string nitEmisor
	{
		get
		{
			return nitEmisorField;
		}
		set
		{
			nitEmisorField = value;
		}
	}

	public string razonSocialEmisor
	{
		get
		{
			return razonSocialEmisorField;
		}
		set
		{
			razonSocialEmisorField = value;
		}
	}

	public string municipio
	{
		get
		{
			return municipioField;
		}
		set
		{
			municipioField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public string telefono
	{
		get
		{
			return telefonoField;
		}
		set
		{
			telefonoField = value;
		}
	}

	[XmlElement(DataType = "integer")]
	public string numeroNotaCreditoDebito
	{
		get
		{
			return numeroNotaCreditoDebitoField;
		}
		set
		{
			numeroNotaCreditoDebitoField = value;
		}
	}

	public string cuf
	{
		get
		{
			return cufField;
		}
		set
		{
			cufField = value;
		}
	}

	public string cufd
	{
		get
		{
			return cufdField;
		}
		set
		{
			cufdField = value;
		}
	}

	[XmlElement(DataType = "integer")]
	public string codigoSucursal
	{
		get
		{
			return codigoSucursalField;
		}
		set
		{
			codigoSucursalField = value;
		}
	}

	public string direccion
	{
		get
		{
			return direccionField;
		}
		set
		{
			direccionField = value;
		}
	}

	[XmlElement(DataType = "integer", IsNullable = true)]
	public string codigoPuntoVenta
	{
		get
		{
			return codigoPuntoVentaField;
		}
		set
		{
			codigoPuntoVentaField = value;
		}
	}

	public string fechaEmision
	{
		get
		{
			return fechaEmisionField;
		}
		set
		{
			fechaEmisionField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public string nombreRazonSocial
	{
		get
		{
			return nombreRazonSocialField;
		}
		set
		{
			nombreRazonSocialField = value;
		}
	}

	[XmlElement(DataType = "integer")]
	public string codigoTipoDocumentoIdentidad
	{
		get
		{
			return codigoTipoDocumentoIdentidadField;
		}
		set
		{
			codigoTipoDocumentoIdentidadField = value;
		}
	}

	public string numeroDocumento
	{
		get
		{
			return numeroDocumentoField;
		}
		set
		{
			numeroDocumentoField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public string complemento
	{
		get
		{
			return complementoField;
		}
		set
		{
			complementoField = value;
		}
	}

	public string codigoCliente
	{
		get
		{
			return codigoClienteField;
		}
		set
		{
			codigoClienteField = value;
		}
	}

	[XmlElement(DataType = "integer")]
	public string numeroFactura
	{
		get
		{
			return numeroFacturaField;
		}
		set
		{
			numeroFacturaField = value;
		}
	}

	public string numeroAutorizacionCuf
	{
		get
		{
			return numeroAutorizacionCufField;
		}
		set
		{
			numeroAutorizacionCufField = value;
		}
	}

	public string fechaEmisionFactura
	{
		get
		{
			return fechaEmisionFacturaField;
		}
		set
		{
			fechaEmisionFacturaField = value;
		}
	}

	public decimal montoTotalOriginal
	{
		get
		{
			return montoTotalOriginalField;
		}
		set
		{
			montoTotalOriginalField = value;
		}
	}

	public decimal montoTotalDevuelto
	{
		get
		{
			return montoTotalDevueltoField;
		}
		set
		{
			montoTotalDevueltoField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public decimal? montoDescuentoCreditoDebito
	{
		get
		{
			return montoDescuentoCreditoDebitoField;
		}
		set
		{
			montoDescuentoCreditoDebitoField = value;
		}
	}

	public decimal montoEfectivoCreditoDebito
	{
		get
		{
			return montoEfectivoCreditoDebitoField;
		}
		set
		{
			montoEfectivoCreditoDebitoField = value;
		}
	}

	[XmlElement(DataType = "integer", IsNullable = true)]
	public string codigoExcepcion
	{
		get
		{
			return codigoExcepcionField;
		}
		set
		{
			codigoExcepcionField = value;
		}
	}

	public string leyenda
	{
		get
		{
			return leyendaField;
		}
		set
		{
			leyendaField = value;
		}
	}

	public string usuario
	{
		get
		{
			return usuarioField;
		}
		set
		{
			usuarioField = value;
		}
	}

	[XmlElement(DataType = "integer")]
	public string codigoDocumentoSector
	{
		get
		{
			return codigoDocumentoSectorField;
		}
		set
		{
			codigoDocumentoSectorField = value;
		}
	}

	public notaFiscalElectronicaCreditoDebitoCabecera()
	{
		codigoDocumentoSectorField = "24";
	}
}
