using System.CodeDom.Compiler;
using System.ServiceModel;
using System.Threading.Tasks;

namespace ControlConsumoLib.FactElectCodigos;

[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[ServiceContract(Namespace = "https://siat.impuestos.gob.bo/", ConfigurationName = "FactElectCodigos.ServicioFacturacionCodigos")]
public interface ServicioFacturacionCodigos
{
	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaComunicacion")]
	verificarComunicacionResponse verificarComunicacion(verificarComunicacion request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<verificarComunicacionResponse> verificarComunicacionAsync(verificarComunicacion request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaVerificarNit")]
	verificarNitResponse verificarNit(verificarNit request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<verificarNitResponse> verificarNitAsync(verificarNit request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaCuisMasivo")]
	cuisMasivoResponse cuisMasivo(cuisMasivo request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<cuisMasivoResponse> cuisMasivoAsync(cuisMasivo request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaCufd")]
	cufdResponse cufd(cufd request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<cufdResponse> cufdAsync(cufd request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaNotificaRevocado")]
	notificaCertificadoRevocadoResponse notificaCertificadoRevocado(notificaCertificadoRevocado request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<notificaCertificadoRevocadoResponse> notificaCertificadoRevocadoAsync(notificaCertificadoRevocado request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaCuis")]
	cuisResponse cuis(cuis request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<cuisResponse> cuisAsync(cuis request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaCufdMasivo")]
	cufdMasivoResponse cufdMasivo(cufdMasivo request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<cufdMasivoResponse> cufdMasivoAsync(cufdMasivo request);
}
