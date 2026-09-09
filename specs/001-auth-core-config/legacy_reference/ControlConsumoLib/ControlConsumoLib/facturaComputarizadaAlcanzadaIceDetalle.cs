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
public class facturaComputarizadaAlcanzadaIceDetalle
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

	private string marcaIceField;

	private decimal? alicuotaIvaField;

	private decimal? precioNetoVentaIceField;

	private decimal? alicuotaEspecificaField;

	private decimal? alicuotaPorcentualField;

	private decimal? montoIceEspecificoField;

	private decimal? montoIcePorcentualField;

	private decimal? cantidadIceField;

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
	public string marcaIce
	{
		get
		{
			return marcaIceField;
		}
		set
		{
			marcaIceField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public decimal? alicuotaIva
	{
		get
		{
			return alicuotaIvaField;
		}
		set
		{
			alicuotaIvaField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public decimal? precioNetoVentaIce
	{
		get
		{
			return precioNetoVentaIceField;
		}
		set
		{
			precioNetoVentaIceField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public decimal? alicuotaEspecifica
	{
		get
		{
			return alicuotaEspecificaField;
		}
		set
		{
			alicuotaEspecificaField = value;
		}
	}

	[XmlElement(IsNullable = true)]
	public decimal? alicuotaPorcentual
	{
		get
		{
			return alicuotaPorcentualField;
		}
		set
		{
			alicuotaPorcentualField = value;
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

	[XmlElement(IsNullable = true)]
	public decimal? cantidadIce
	{
		get
		{
			return cantidadIceField;
		}
		set
		{
			cantidadIceField = value;
		}
	}
}
