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
[MessageContract(WrapperName = "registroPuntoVentaComisionistaResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class registroPuntoVentaComisionistaResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaPuntoVentaComisionista RespuestaPuntoVentaComisionista;

	public registroPuntoVentaComisionistaResponse()
	{
	}

	public registroPuntoVentaComisionistaResponse(respuestaPuntoVentaComisionista RespuestaPuntoVentaComisionista)
	{
		this.RespuestaPuntoVentaComisionista = RespuestaPuntoVentaComisionista;
	}
}
