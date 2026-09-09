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
[MessageContract(WrapperName = "verificarNitResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class verificarNitResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaVerificarNit RespuestaVerificarNit;

	public verificarNitResponse()
	{
	}

	public verificarNitResponse(respuestaVerificarNit RespuestaVerificarNit)
	{
		this.RespuestaVerificarNit = RespuestaVerificarNit;
	}
}
