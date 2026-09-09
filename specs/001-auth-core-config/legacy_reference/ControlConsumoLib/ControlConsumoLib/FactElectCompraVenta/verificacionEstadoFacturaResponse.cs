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
[MessageContract(WrapperName = "verificacionEstadoFacturaResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class verificacionEstadoFacturaResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaRecepcion RespuestaServicioFacturacion;

	public verificacionEstadoFacturaResponse()
	{
	}

	public verificacionEstadoFacturaResponse(respuestaRecepcion RespuestaServicioFacturacion)
	{
		this.RespuestaServicioFacturacion = RespuestaServicioFacturacion;
	}
}
