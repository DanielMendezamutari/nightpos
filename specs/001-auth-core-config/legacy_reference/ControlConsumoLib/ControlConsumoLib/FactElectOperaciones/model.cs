using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectOperaciones;

[Serializable]
[XmlInclude(typeof(modelDto))]
[XmlInclude(typeof(respuestaCierrePuntoVenta))]
[XmlInclude(typeof(respuestaConsultaPuntoVenta))]
[XmlInclude(typeof(respuestaListaEventos))]
[XmlInclude(typeof(respuestaCierreSistemas))]
[XmlInclude(typeof(respuestaPuntoVentaComisionista))]
[XmlInclude(typeof(respuestaRegistroPuntoVenta))]
[XmlInclude(typeof(mensajeServicio))]
[XmlInclude(typeof(respuestaComunicacion))]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public abstract class model : INotifyPropertyChanged
{
	public event PropertyChangedEventHandler PropertyChanged;

	protected void RaisePropertyChanged(string propertyName)
	{
		PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
	}
}
