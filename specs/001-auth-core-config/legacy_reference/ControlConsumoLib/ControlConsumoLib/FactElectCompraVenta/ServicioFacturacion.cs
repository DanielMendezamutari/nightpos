using System.CodeDom.Compiler;
using System.ServiceModel;
using System.Threading.Tasks;

namespace ControlConsumoLib.FactElectCompraVenta;

[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[ServiceContract(Namespace = "https://siat.impuestos.gob.bo/", ConfigurationName = "FactElectCompraVenta.ServicioFacturacion")]
public interface ServicioFacturacion
{
	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	recepcionPaqueteFacturaResponse recepcionPaqueteFactura(recepcionPaqueteFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<recepcionPaqueteFacturaResponse> recepcionPaqueteFacturaAsync(recepcionPaqueteFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "return")]
	verificarComunicacionResponse verificarComunicacion(verificarComunicacion request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<verificarComunicacionResponse> verificarComunicacionAsync(verificarComunicacion request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	recepcionFacturaResponse recepcionFactura(recepcionFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<recepcionFacturaResponse> recepcionFacturaAsync(recepcionFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	reversionAnulacionFacturaResponse reversionAnulacionFactura(reversionAnulacionFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<reversionAnulacionFacturaResponse> reversionAnulacionFacturaAsync(reversionAnulacionFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	validacionRecepcionMasivaFacturaResponse validacionRecepcionMasivaFactura(validacionRecepcionMasivaFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<validacionRecepcionMasivaFacturaResponse> validacionRecepcionMasivaFacturaAsync(validacionRecepcionMasivaFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaRecepcionAnexos")]
	recepcionAnexosResponse recepcionAnexos(recepcionAnexos request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<recepcionAnexosResponse> recepcionAnexosAsync(recepcionAnexos request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	recepcionMasivaFacturaResponse recepcionMasivaFactura(recepcionMasivaFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<recepcionMasivaFacturaResponse> recepcionMasivaFacturaAsync(recepcionMasivaFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	verificacionEstadoFacturaResponse verificacionEstadoFactura(verificacionEstadoFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<verificacionEstadoFacturaResponse> verificacionEstadoFacturaAsync(verificacionEstadoFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	validacionRecepcionPaqueteFacturaResponse validacionRecepcionPaqueteFactura(validacionRecepcionPaqueteFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<validacionRecepcionPaqueteFacturaResponse> validacionRecepcionPaqueteFacturaAsync(validacionRecepcionPaqueteFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	anulacionFacturaResponse anulacionFactura(anulacionFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<anulacionFacturaResponse> anulacionFacturaAsync(anulacionFactura request);
}
