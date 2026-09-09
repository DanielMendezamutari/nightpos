using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectCompraVenta;

[Serializable]
[XmlInclude(typeof(modelDto))]
[XmlInclude(typeof(respuestaComunicacion))]
[XmlInclude(typeof(mensajeServicio))]
[XmlInclude(typeof(mensajeRecepcion))]
[XmlInclude(typeof(respuestaRecepcion))]
[XmlInclude(typeof(ventaAnexo))]
[XmlInclude(typeof(solicitudRecepcion))]
[XmlInclude(typeof(solicitudAnulacion))]
[XmlInclude(typeof(solicitudVerificacionEstado))]
[XmlInclude(typeof(solicitudRecepcionAnexos))]
[XmlInclude(typeof(solicitudValidacionRecepcion))]
[XmlInclude(typeof(solicitudReversionAnulacion))]
[XmlInclude(typeof(solicitudRecepcionFactura))]
[XmlInclude(typeof(solicitudRecepcionMasiva))]
[XmlInclude(typeof(solicitudRecepcionPaquete))]
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
