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
[MessageContract(WrapperName = "verificarNit", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class verificarNit
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudVerificarNit SolicitudVerificarNit;

	public verificarNit()
	{
	}

	public verificarNit(solicitudVerificarNit SolicitudVerificarNit)
	{
		this.SolicitudVerificarNit = SolicitudVerificarNit;
	}
}
