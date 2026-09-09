using System.CodeDom.Compiler;
using System.ServiceModel;
using System.Threading.Tasks;

namespace ControlConsumoLib.FactElectSync;

[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[ServiceContract(Namespace = "https://siat.impuestos.gob.bo/", ConfigurationName = "FactElectSync.ServicioFacturacionSincronizacion")]
public interface ServicioFacturacionSincronizacion
{
	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaMotivoAnulacionResponse sincronizarParametricaMotivoAnulacion(sincronizarParametricaMotivoAnulacion request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaMotivoAnulacionResponse> sincronizarParametricaMotivoAnulacionAsync(sincronizarParametricaMotivoAnulacion request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaActividades")]
	sincronizarActividadesResponse sincronizarActividades(sincronizarActividades request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarActividadesResponse> sincronizarActividadesAsync(sincronizarActividades request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaFechaHora")]
	sincronizarFechaHoraResponse sincronizarFechaHora(sincronizarFechaHora request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarFechaHoraResponse> sincronizarFechaHoraAsync(sincronizarFechaHora request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricasLeyendas")]
	sincronizarListaLeyendasFacturaResponse sincronizarListaLeyendasFactura(sincronizarListaLeyendasFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarListaLeyendasFacturaResponse> sincronizarListaLeyendasFacturaAsync(sincronizarListaLeyendasFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaTipoHabitacionResponse sincronizarParametricaTipoHabitacion(sincronizarParametricaTipoHabitacion request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaTipoHabitacionResponse> sincronizarParametricaTipoHabitacionAsync(sincronizarParametricaTipoHabitacion request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaActividadesDocumentoSector")]
	sincronizarListaActividadesDocumentoSectorResponse sincronizarListaActividadesDocumentoSector(sincronizarListaActividadesDocumentoSector request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarListaActividadesDocumentoSectorResponse> sincronizarListaActividadesDocumentoSectorAsync(sincronizarListaActividadesDocumentoSector request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaTipoDocumentoIdentidadResponse sincronizarParametricaTipoDocumentoIdentidad(sincronizarParametricaTipoDocumentoIdentidad request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaTipoDocumentoIdentidadResponse> sincronizarParametricaTipoDocumentoIdentidadAsync(sincronizarParametricaTipoDocumentoIdentidad request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaUnidadMedidaResponse sincronizarParametricaUnidadMedida(sincronizarParametricaUnidadMedida request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaUnidadMedidaResponse> sincronizarParametricaUnidadMedidaAsync(sincronizarParametricaUnidadMedida request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaTipoDocumentoSectorResponse sincronizarParametricaTipoDocumentoSector(sincronizarParametricaTipoDocumentoSector request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaTipoDocumentoSectorResponse> sincronizarParametricaTipoDocumentoSectorAsync(sincronizarParametricaTipoDocumentoSector request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaTiposFacturaResponse sincronizarParametricaTiposFactura(sincronizarParametricaTiposFactura request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaTiposFacturaResponse> sincronizarParametricaTiposFacturaAsync(sincronizarParametricaTiposFactura request);

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
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarListaMensajesServiciosResponse sincronizarListaMensajesServicios(sincronizarListaMensajesServicios request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarListaMensajesServiciosResponse> sincronizarListaMensajesServiciosAsync(sincronizarListaMensajesServicios request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaTipoMetodoPagoResponse sincronizarParametricaTipoMetodoPago(sincronizarParametricaTipoMetodoPago request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaTipoMetodoPagoResponse> sincronizarParametricaTipoMetodoPagoAsync(sincronizarParametricaTipoMetodoPago request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaEventosSignificativosResponse sincronizarParametricaEventosSignificativos(sincronizarParametricaEventosSignificativos request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaEventosSignificativosResponse> sincronizarParametricaEventosSignificativosAsync(sincronizarParametricaEventosSignificativos request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaTipoPuntoVentaResponse sincronizarParametricaTipoPuntoVenta(sincronizarParametricaTipoPuntoVenta request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaTipoPuntoVentaResponse> sincronizarParametricaTipoPuntoVentaAsync(sincronizarParametricaTipoPuntoVenta request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaProductos")]
	sincronizarListaProductosServiciosResponse sincronizarListaProductosServicios(sincronizarListaProductosServicios request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarListaProductosServiciosResponse> sincronizarListaProductosServiciosAsync(sincronizarListaProductosServicios request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaTipoEmisionResponse sincronizarParametricaTipoEmision(sincronizarParametricaTipoEmision request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaTipoEmisionResponse> sincronizarParametricaTipoEmisionAsync(sincronizarParametricaTipoEmision request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaPaisOrigenResponse sincronizarParametricaPaisOrigen(sincronizarParametricaPaisOrigen request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaPaisOrigenResponse> sincronizarParametricaPaisOrigenAsync(sincronizarParametricaPaisOrigen request);

	[OperationContract(Action = "", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	[ServiceKnownType(typeof(model))]
	[return: MessageParameter(Name = "RespuestaListaParametricas")]
	sincronizarParametricaTipoMonedaResponse sincronizarParametricaTipoMoneda(sincronizarParametricaTipoMoneda request);

	[OperationContract(Action = "", ReplyAction = "*")]
	Task<sincronizarParametricaTipoMonedaResponse> sincronizarParametricaTipoMonedaAsync(sincronizarParametricaTipoMoneda request);
}
