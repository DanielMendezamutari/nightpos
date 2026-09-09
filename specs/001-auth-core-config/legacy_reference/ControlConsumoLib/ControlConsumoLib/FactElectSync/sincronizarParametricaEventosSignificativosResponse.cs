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
[MessageContract(WrapperName = "sincronizarParametricaEventosSignificativosResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class sincronizarParametricaEventosSignificativosResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaListaParametricas RespuestaListaParametricas;

	public sincronizarParametricaEventosSignificativosResponse()
	{
	}

	public sincronizarParametricaEventosSignificativosResponse(respuestaListaParametricas RespuestaListaParametricas)
	{
		this.RespuestaListaParametricas = RespuestaListaParametricas;
	}
}
