using System.CodeDom.Compiler;
using System.ServiceModel;
using System.Threading.Tasks;

namespace ControlConsumoLib.FactElectOperaciones;

[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[ServiceContract(Namespace = "https://siat.impuestos.gob.bo/", ConfigurationName = "FactElectOperaciones.ServicioFacturacionOperaciones")]
public interface ServicioFacturacionOperaciones
{
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
	[return: MessageParameter(Name = "RespuestaRegistroPuntoVenta")]
	registroPuntoVentaResponse registroPuntoVenta(registroPuntoVenta request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<registroPuntoVentaResponse> registroPuntoVentaAsync(registroPuntoVenta request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaPuntoVentaComisionista")]
	registroPuntoVentaComisionistaResponse registroPuntoVentaComisionista(registroPuntoVentaComisionista request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<registroPuntoVentaComisionistaResponse> registroPuntoVentaComisionistaAsync(registroPuntoVentaComisionista request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaCierreSistemas")]
	cierreOperacionesSistemaResponse cierreOperacionesSistema(cierreOperacionesSistema request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<cierreOperacionesSistemaResponse> cierreOperacionesSistemaAsync(cierreOperacionesSistema request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaEventos")]
	consultaEventoSignificativoResponse consultaEventoSignificativo(consultaEventoSignificativo request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<consultaEventoSignificativoResponse> consultaEventoSignificativoAsync(consultaEventoSignificativo request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaConsultaPuntoVenta")]
	consultaPuntoVentaResponse consultaPuntoVenta(consultaPuntoVenta request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<consultaPuntoVentaResponse> consultaPuntoVentaAsync(consultaPuntoVenta request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaEventos")]
	registroEventoSignificativoResponse registroEventoSignificativo(registroEventoSignificativo request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<registroEventoSignificativoResponse> registroEventoSignificativoAsync(registroEventoSignificativo request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaCierrePuntoVenta")]
	cierrePuntoVentaResponse cierrePuntoVenta(cierrePuntoVenta request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<cierrePuntoVentaResponse> cierrePuntoVentaAsync(cierrePuntoVenta request);
}
