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
[MessageContract(WrapperName = "cierreOperacionesSistema", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class cierreOperacionesSistema
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudOperaciones SolicitudOperaciones;

	public cierreOperacionesSistema()
	{
	}

	public cierreOperacionesSistema(solicitudOperaciones SolicitudOperaciones)
	{
		this.SolicitudOperaciones = SolicitudOperaciones;
	}
}
