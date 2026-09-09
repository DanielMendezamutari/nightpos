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
[MessageContract(WrapperName = "sincronizarParametricaTipoEmisionResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class sincronizarParametricaTipoEmisionResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaListaParametricas RespuestaListaParametricas;

	public sincronizarParametricaTipoEmisionResponse()
	{
	}

	public sincronizarParametricaTipoEmisionResponse(respuestaListaParametricas RespuestaListaParametricas)
	{
		this.RespuestaListaParametricas = RespuestaListaParametricas;
	}
}
