using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElecNotaCredito;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[EditorBrowsable(EditorBrowsableState.Advanced)]
[MessageContract(WrapperName = "reversionAnulacionDocumentoAjusteResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class reversionAnulacionDocumentoAjusteResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaRecepcion RespuestaServicioFacturacion;

	public reversionAnulacionDocumentoAjusteResponse()
	{
	}

	public reversionAnulacionDocumentoAjusteResponse(respuestaRecepcion RespuestaServicioFacturacion)
	{
		this.RespuestaServicioFacturacion = RespuestaServicioFacturacion;
	}
}
