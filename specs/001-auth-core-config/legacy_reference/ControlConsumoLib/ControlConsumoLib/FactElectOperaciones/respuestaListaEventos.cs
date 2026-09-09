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
public class respuestaListaEventos : modelDto
{
	private long codigoRecepcionEventoSignificativoField;

	private bool codigoRecepcionEventoSignificativoFieldSpecified;

	private eventosSignificativosDto[] listaCodigosField;

	private mensajeServicio[] mensajesListField;

	private bool transaccionField;

	private bool transaccionFieldSpecified;

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 0)]
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

	[XmlElement("listaCodigos", Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 1)]
	public eventosSignificativosDto[] listaCodigos
	{
		get
		{
			return listaCodigosField;
		}
		set
		{
			listaCodigosField = value;
			RaisePropertyChanged("listaCodigos");
		}
	}

	[XmlElement("mensajesList", Form = XmlSchemaForm.Unqualified, IsNullable = true, Order = 2)]
	public mensajeServicio[] mensajesList
	{
		get
		{
			return mensajesListField;
		}
		set
		{
			mensajesListField = value;
			RaisePropertyChanged("mensajesList");
		}
	}

	[XmlElement(Form = XmlSchemaForm.Unqualified, Order = 3)]
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

	[XmlIgnore]
	public bool transaccionSpecified
	{
		get
		{
			return transaccionFieldSpecified;
		}
		set
		{
			transaccionFieldSpecified = value;
			RaisePropertyChanged("transaccionSpecified");
		}
	}
}
