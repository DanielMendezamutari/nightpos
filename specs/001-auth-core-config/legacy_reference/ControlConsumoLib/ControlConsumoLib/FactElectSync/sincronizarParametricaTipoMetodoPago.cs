using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.Xml.Schema;
using System.Xml.Serialization;

namespace ControlConsumoLib.FactElectSync;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[EditorBrowsable(EditorBrowsableState.Advanced)]
[MessageContract(WrapperName = "sincronizarParametricaTipoMetodoPago", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class sincronizarParametricaTipoMetodoPago
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudSincronizacion SolicitudSincronizacion;

	public sincronizarParametricaTipoMetodoPago()
	{
	}

	public sincronizarParametricaTipoMetodoPago(solicitudSincronizacion SolicitudSincronizacion)
	{
		this.SolicitudSincronizacion = SolicitudSincronizacion;
	}
}
