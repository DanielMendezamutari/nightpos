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
[MessageContract(WrapperName = "cuisMasivo", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class cuisMasivo
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudCuisMasivoSistemas SolicitudCuisMasivoSistemas;

	public cuisMasivo()
	{
	}

	public cuisMasivo(solicitudCuisMasivoSistemas SolicitudCuisMasivoSistemas)
	{
		this.SolicitudCuisMasivoSistemas = SolicitudCuisMasivoSistemas;
	}
}
