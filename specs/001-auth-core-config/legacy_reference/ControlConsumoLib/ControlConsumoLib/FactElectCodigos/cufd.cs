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
[MessageContract(WrapperName = "cufd", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class cufd
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudCufd SolicitudCufd;

	public cufd()
	{
	}

	public cufd(solicitudCufd SolicitudCufd)
	{
		this.SolicitudCufd = SolicitudCufd;
	}
}
