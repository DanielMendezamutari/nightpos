using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElecNotaCredito;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[EditorBrowsable(EditorBrowsableState.Advanced)]
[MessageContract(WrapperName = "recepcionDocumentoAjuste", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class recepcionDocumentoAjuste
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudRecepcionFactura SolicitudServicioRecepcionDocumentoAjuste;

	public recepcionDocumentoAjuste()
	{
	}

	public recepcionDocumentoAjuste(solicitudRecepcionFactura SolicitudServicioRecepcionDocumentoAjuste)
	{
		this.SolicitudServicioRecepcionDocumentoAjuste = SolicitudServicioRecepcionDocumentoAjuste;
	}
}
