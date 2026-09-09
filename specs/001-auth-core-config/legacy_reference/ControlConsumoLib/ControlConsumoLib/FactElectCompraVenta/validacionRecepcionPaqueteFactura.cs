using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectCompraVenta;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[EditorBrowsable(EditorBrowsableState.Advanced)]
[MessageContract(WrapperName = "validacionRecepcionPaqueteFactura", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class validacionRecepcionPaqueteFactura
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudValidacionRecepcion SolicitudServicioValidacionRecepcionPaquete;

	public validacionRecepcionPaqueteFactura()
	{
	}

	public validacionRecepcionPaqueteFactura(solicitudValidacionRecepcion SolicitudServicioValidacionRecepcionPaquete)
	{
		this.SolicitudServicioValidacionRecepcionPaquete = SolicitudServicioValidacionRecepcionPaquete;
	}
}
