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
public class notaFiscalElectronicaCreditoDebitoDetalle
{
	private string actividadEconomicaField;

	private string codigoProductoSinField;

	private string codigoProductoField;

	private string descripcionField;

	private decimal cantidadField;

	private string unidadMedidaField;

	private decimal precioUnitarioField;

	private decimal? montoDescuentoField;

	private decimal subTotalField;

	private string codigoDetalleTransaccionField;

	public string actividadEconomica
	{
		get
		{
			return actividadEconomicaField;
		}
		set
		{
			actividadEconomicaField = value;
		}
	}

	[XmlElement(DataType = "integer")]
	public string codigoProductoSin
	{
		get
		{
			return codigoProductoSinField;
		}
		set
		{
			codigoProductoSinField = value;
		}
	}

	public string codigoProducto
	{
		get
		{
			return codigoProductoField;
		}
		set
		{
			codigoProductoField = value;
		}
	}

	public string descripcion
	{
		get
		{
			return descripcionField;
		}
		set
		{
			descripcionField = value;
		}
	}

	public decimal cantidad
	{
		get
		{
			return cantidadField;
		}
		set
		{
			cantidadField = value;
		}
	}

	[XmlElement(DataType = "integer")]
	public string unidadMedida
	{
		get
		{
			return unidadMedidaField;
		}
		set
		{
			unidadMedidaField = value;
		}
	}

	public decimal precioUnitario
	{
		get
		{
			return precioUnitarioField;
		}
		set
		{
			precioUnitarioField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public decimal? montoDescuento
	{
		get
		{
			return montoDescuentoField;
		}
		set
		{
			montoDescuentoField = value;
		}
	}

	public decimal subTotal
	{
		get
		{
			return subTotalField;
		}
		set
		{
			subTotalField = value;
		}
	}

	[XmlElement(DataType = "integer")]
	public string codigoDetalleTransaccion
	{
		get
		{
			return codigoDetalleTransaccionField;
		}
		set
		{
			codigoDetalleTransaccionField = value;
		}
	}
}
