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
[MessageContract(WrapperName = "cuisMasivoResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class cuisMasivoResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaCuisMasivo RespuestaCuisMasivo;

	public cuisMasivoResponse()
	{
	}

	public cuisMasivoResponse(respuestaCuisMasivo RespuestaCuisMasivo)
	{
		this.RespuestaCuisMasivo = RespuestaCuisMasivo;
	}
}
