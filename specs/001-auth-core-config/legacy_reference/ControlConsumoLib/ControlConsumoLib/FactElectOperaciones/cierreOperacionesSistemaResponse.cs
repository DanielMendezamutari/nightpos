using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectOperaciones;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[EditorBrowsable(EditorBrowsableState.Advanced)]
[MessageContract(WrapperName = "cierreOperacionesSistemaResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class cierreOperacionesSistemaResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaCierreSistemas RespuestaCierreSistemas;

	public cierreOperacionesSistemaResponse()
	{
	}

	public cierreOperacionesSistemaResponse(respuestaCierreSistemas RespuestaCierreSistemas)
	{
		this.RespuestaCierreSistemas = RespuestaCierreSistemas;
	}
}
