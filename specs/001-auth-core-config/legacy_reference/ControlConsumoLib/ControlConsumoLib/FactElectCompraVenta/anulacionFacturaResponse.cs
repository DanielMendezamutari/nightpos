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
[MessageContract(WrapperName = "anulacionFacturaResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class anulacionFacturaResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaRecepcion RespuestaServicioFacturacion;

	public anulacionFacturaResponse()
	{
	}

	public anulacionFacturaResponse(respuestaRecepcion RespuestaServicioFacturacion)
	{
		this.RespuestaServicioFacturacion = RespuestaServicioFacturacion;
	}
}
