using System.CodeDom.Compiler;
using System.ServiceModel;
using System.Threading.Tasks;

namespace ControlConsumoLib.FactElecNotaCredito;

[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[ServiceContract(Namespace = "https://siat.impuestos.gob.bo/", ConfigurationName = "FactElecNotaCredito.ServicioFacturacion")]
public interface ServicioFacturacion
{
	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	anulacionDocumentoAjusteResponse anulacionDocumentoAjuste(anulacionDocumentoAjuste request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<anulacionDocumentoAjusteResponse> anulacionDocumentoAjusteAsync(anulacionDocumentoAjuste request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	reversionAnulacionDocumentoAjusteResponse reversionAnulacionDocumentoAjuste(reversionAnulacionDocumentoAjuste request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<reversionAnulacionDocumentoAjusteResponse> reversionAnulacionDocumentoAjusteAsync(reversionAnulacionDocumentoAjuste request);

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
	recepcionDocumentoAjusteResponse recepcionDocumentoAjuste(recepcionDocumentoAjuste request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<recepcionDocumentoAjusteResponse> recepcionDocumentoAjusteAsync(recepcionDocumentoAjuste request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaServicioFacturacion")]
	verificacionEstadoDocumentoAjusteResponse verificacionEstadoDocumentoAjuste(verificacionEstadoDocumentoAjuste request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<verificacionEstadoDocumentoAjusteResponse> verificacionEstadoDocumentoAjusteAsync(verificacionEstadoDocumentoAjuste request);
}
