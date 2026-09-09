using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectSync;

[Serializable]
[XmlInclude(typeof(modelDto))]
[XmlInclude(typeof(respuestaComunicacion))]
[XmlInclude(typeof(actividadesDocumentoSectorDto))]
[XmlInclude(typeof(mensajeServicio))]
[XmlInclude(typeof(respuestaConfiguracion))]
[XmlInclude(typeof(respuestaListaProductos))]
[XmlInclude(typeof(respuestaListaActividadesDocumentoSector))]
[XmlInclude(typeof(respuestaListaParametricasLeyendas))]
[XmlInclude(typeof(respuestaFechaHora))]
[XmlInclude(typeof(respuestaListaActividades))]
[XmlInclude(typeof(respuestaListaParametricas))]
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
