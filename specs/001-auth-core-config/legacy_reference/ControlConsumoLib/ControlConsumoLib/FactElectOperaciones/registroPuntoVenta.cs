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
[MessageContract(WrapperName = "registroPuntoVenta", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class registroPuntoVenta
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public solicitudRegistroPuntoVenta SolicitudRegistroPuntoVenta;

	public registroPuntoVenta()
	{
	}

	public registroPuntoVenta(solicitudRegistroPuntoVenta SolicitudRegistroPuntoVenta)
	{
		this.SolicitudRegistroPuntoVenta = SolicitudRegistroPuntoVenta;
	}
}
