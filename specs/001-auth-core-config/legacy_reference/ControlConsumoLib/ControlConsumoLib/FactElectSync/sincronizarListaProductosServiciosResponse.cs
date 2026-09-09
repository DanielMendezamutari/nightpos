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
[MessageContract(WrapperName = "sincronizarListaProductosServiciosResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class sincronizarListaProductosServiciosResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaListaProductos RespuestaListaProductos;

	public sincronizarListaProductosServiciosResponse()
	{
	}

	public sincronizarListaProductosServiciosResponse(respuestaListaProductos RespuestaListaProductos)
	{
		this.RespuestaListaProductos = RespuestaListaProductos;
	}
}
