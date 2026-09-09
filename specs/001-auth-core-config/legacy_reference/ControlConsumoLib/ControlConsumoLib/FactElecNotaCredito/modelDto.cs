using System;
using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElecNotaCredito;

[Serializable]
[XmlInclude(typeof(respuestaComunicacion))]
[XmlInclude(typeof(mensajeServicio))]
[XmlInclude(typeof(mensajeRecepcion))]
[XmlInclude(typeof(respuestaRecepcion))]
[XmlInclude(typeof(solicitudRecepcion))]
[XmlInclude(typeof(solicitudVerificacionEstado))]
[XmlInclude(typeof(solicitudRecepcionFactura))]
[XmlInclude(typeof(solicitudReversionAnulacion))]
[XmlInclude(typeof(solicitudAnulacion))]
[GeneratedCode("System.Xml", "4.8.9037.0")]
[DebuggerStepThrough]
[DesignerCategory("code")]
[XmlType(Namespace = "https://siat.impuestos.gob.bo/")]
public abstract class modelDto : model
{
}
