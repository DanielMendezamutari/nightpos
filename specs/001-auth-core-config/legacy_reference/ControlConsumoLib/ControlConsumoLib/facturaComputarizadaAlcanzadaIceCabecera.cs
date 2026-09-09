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
public class facturaComputarizadaAlcanzadaIceCabecera
{
	private string nitEmisorField;

	private string razonSocialEmisorField;

	private string municipioField;

	private string telefonoField;

	private string numeroFacturaField;

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

	private string codigoMetodoPagoField;

	private string numeroTarjetaField;

	private decimal montoTotalField;

	private decimal? montoIceEspecificoField;

	private decimal? montoIcePorcentualField;

	private decimal montoTotalSujetoIvaField;

	private string codigoMonedaField;

	private decimal tipoCambioField;

	private decimal montoTotalMonedaField;

	private decimal? descuentoAdicionalField;

	private string codigoExcepcionField;

	private string cafcField;

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
	public string codigoMetodoPago
	{
		get
		{
			return codigoMetodoPagoField;
		}
		set
		{
			codigoMetodoPagoField = value;
		}
	}

	[XmlElement(DataType = "integer", IsNullable = true)]
	public string numeroTarjeta
	{
		get
		{
			return numeroTarjetaField;
		}
		set
		{
			numeroTarjetaField = value;
		}
	}

	public decimal montoTotal
	{
		get
		{
			return montoTotalField;
		}
		set
		{
			montoTotalField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public decimal? montoIceEspecifico
	{
		get
		{
			return montoIceEspecificoField;
		}
		set
		{
			montoIceEspecificoField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public decimal? montoIcePorcentual
	{
		get
		{
			return montoIcePorcentualField;
		}
		set
		{
			montoIcePorcentualField = value;
		}
	}

	public decimal montoTotalSujetoIva
	{
		get
		{
			return montoTotalSujetoIvaField;
		}
		set
		{
			montoTotalSujetoIvaField = value;
		}
	}

	[XmlElement(DataType = "integer")]
	public string codigoMoneda
	{
		get
		{
			return codigoMonedaField;
		}
		set
		{
			codigoMonedaField = value;
		}
	}

	public decimal tipoCambio
	{
		get
		{
			return tipoCambioField;
		}
		set
		{
			tipoCambioField = value;
		}
	}

	public decimal montoTotalMoneda
	{
		get
		{
			return montoTotalMonedaField;
		}
		set
		{
			montoTotalMonedaField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public decimal? descuentoAdicional
	{
		get
		{
			return descuentoAdicionalField;
		}
		set
		{
			descuentoAdicionalField = value;
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

	[XmlElement(IsNullable = true)]
	public string cafc
	{
		get
		{
			return cafcField;
		}
		set
		{
			cafcField = value;
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

	public facturaComputarizadaAlcanzadaIceCabecera()
	{
		codigoDocumentoSectorField = "14";
	}
}
