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
[MessageContract(WrapperName = "registroEventoSignificativo", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class registroEventoSignificativo
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudEventoSignificativo SolicitudEventoSignificativo;

	public registroEventoSignificativo()
	{
	}

	public registroEventoSignificativo(solicitudEventoSignificativo SolicitudEventoSignificativo)
	{
		this.SolicitudEventoSignificativo = SolicitudEventoSignificativo;
	}
}
