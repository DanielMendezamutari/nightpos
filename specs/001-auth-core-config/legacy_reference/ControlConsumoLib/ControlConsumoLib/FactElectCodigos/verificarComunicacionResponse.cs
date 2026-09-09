using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectCodigos;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[EditorBrowsable(EditorBrowsableState.Advanced)]
[MessageContract(WrapperName = "verificarComunicacionResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class verificarComunicacionResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaComunicacion RespuestaComunicacion;

	public verificarComunicacionResponse()
	{
	}

	public verificarComunicacionResponse(respuestaComunicacion RespuestaComunicacion)
	{
		this.RespuestaComunicacion = RespuestaComunicacion;
	}
}
