using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectCodigos;

[Serializable]
[XmlInclude(typeof(modelDto))]
[XmlInclude(typeof(respuestaCufdMasivo))]
[XmlInclude(typeof(respuestaCuis))]
[XmlInclude(typeof(respuestaCufd))]
[XmlInclude(typeof(respuestaCuisMasivo))]
[XmlInclude(typeof(respuestaConfiguracion))]
[XmlInclude(typeof(respuestaNotificaRevocado))]
[XmlInclude(typeof(respuestaVerificarNit))]
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
