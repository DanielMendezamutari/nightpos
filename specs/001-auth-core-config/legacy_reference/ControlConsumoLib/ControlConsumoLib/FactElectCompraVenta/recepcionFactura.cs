using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectCompraVenta;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[EditorBrowsable(EditorBrowsableState.Advanced)]
[MessageContract(WrapperName = "recepcionFactura", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class recepcionFactura
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudRecepcionFactura SolicitudServicioRecepcionFactura;

	public recepcionFactura()
	{
	}

	public recepcionFactura(solicitudRecepcionFactura SolicitudServicioRecepcionFactura)
	{
		this.SolicitudServicioRecepcionFactura = SolicitudServicioRecepcionFactura;
	}
}
