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
[MessageContract(WrapperName = "validacionRecepcionMasivaFactura", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class validacionRecepcionMasivaFactura
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudValidacionRecepcion SolicitudServicioValidacionRecepcionMasiva;

	public validacionRecepcionMasivaFactura()
	{
	}

	public validacionRecepcionMasivaFactura(solicitudValidacionRecepcion SolicitudServicioValidacionRecepcionMasiva)
	{
		this.SolicitudServicioValidacionRecepcionMasiva = SolicitudServicioValidacionRecepcionMasiva;
	}
}
