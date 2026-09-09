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
[MessageContract(WrapperName = "recepcionMasivaFactura", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class recepcionMasivaFactura
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudRecepcionMasiva SolicitudServicioRecepcionMasiva;

	public recepcionMasivaFactura()
	{
	}

	public recepcionMasivaFactura(solicitudRecepcionMasiva SolicitudServicioRecepcionMasiva)
	{
		this.SolicitudServicioRecepcionMasiva = SolicitudServicioRecepcionMasiva;
	}
}
