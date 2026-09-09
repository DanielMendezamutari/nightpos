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
[MessageContract(WrapperName = "cierrePuntoVentaResponse", WrapperNamespace = "https://siat.impuestos.gob.bo/", IsWrapped = true)]
public class cierrePuntoVentaResponse
{
	[MessageBodyMember(Namespace = "https://siat.impuestos.gob.bo/", Order = 0)]
	[XmlElement(Form = XmlSchemaForm.Unqualified)]
	public respuestaCierrePuntoVenta RespuestaCierrePuntoVenta;

	public cierrePuntoVentaResponse()
	{
	}

	public cierrePuntoVentaResponse(respuestaCierrePuntoVenta RespuestaCierrePuntoVenta)
	{
		this.RespuestaCierrePuntoVenta = RespuestaCierrePuntoVenta;
	}
}
