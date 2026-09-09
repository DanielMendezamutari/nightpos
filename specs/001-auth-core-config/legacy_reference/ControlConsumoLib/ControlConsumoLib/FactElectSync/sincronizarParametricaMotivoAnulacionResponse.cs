using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectSync;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[EditorBrowsable(EditorBrowsableState.Advanced)]
[MessageContract(WrapperName = "sincronizarParametricaMotivoAnulacionResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class sincronizarParametricaMotivoAnulacionResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaListaParametricas RespuestaListaParametricas;

	public sincronizarParametricaMotivoAnulacionResponse()
	{
	}

	public sincronizarParametricaMotivoAnulacionResponse(respuestaListaParametricas RespuestaListaParametricas)
	{
		this.RespuestaListaParametricas = RespuestaListaParametricas;
	}
}
