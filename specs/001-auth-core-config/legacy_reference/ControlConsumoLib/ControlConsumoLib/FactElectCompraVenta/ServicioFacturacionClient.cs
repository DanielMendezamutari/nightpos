using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.Threading.Tasks;

namespace ControlConsumoLib.FactElectCompraVenta;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
public class ServicioFacturacionClient : ClientBase<ServicioFacturacion>, ServicioFacturacion
{
	public ServicioFacturacionClient()
	{
	}

	public ServicioFacturacionClient(string endpointConfigurationName)
		: base(endpointConfigurationName)
	{
	}

	public ServicioFacturacionClient(string endpointConfigurationName, string remoteAddress)
		: base(endpointConfigurationName, remoteAddress)
	{
	}

	public ServicioFacturacionClient(string endpointConfigurationName, EndpointAddress remoteAddress)
		: base(endpointConfigurationName, remoteAddress)
	{
	}

	public ServicioFacturacionClient(Binding binding, EndpointAddress remoteAddress)
		: base(binding, remoteAddress)
	{
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public recepcionPaqueteFacturaResponse FactElectCompraVenta_ServicioFacturacion_recepcionPaqueteFactura(recepcionPaqueteFactura request)
	{
		return base.Channel.recepcionPaqueteFactura(request);
	}

	recepcionPaqueteFacturaResponse ServicioFacturacion.recepcionPaqueteFactura(recepcionPaqueteFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_recepcionPaqueteFactura
		return this.FactElectCompraVenta_ServicioFacturacion_recepcionPaqueteFactura(request);
	}

	public respuestaRecepcion recepcionPaqueteFactura(solicitudRecepcionPaquete SolicitudServicioRecepcionPaquete)
	{
		recepcionPaqueteFactura recepcionPaqueteFactura2 = new recepcionPaqueteFactura();
		recepcionPaqueteFactura2.SolicitudServicioRecepcionPaquete = SolicitudServicioRecepcionPaquete;
		return ((ServicioFacturacion)this).recepcionPaqueteFactura(recepcionPaqueteFactura2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<recepcionPaqueteFacturaResponse> FactElectCompraVenta_ServicioFacturacion_recepcionPaqueteFacturaAsync(recepcionPaqueteFactura request)
	{
		return base.Channel.recepcionPaqueteFacturaAsync(request);
	}

	Task<recepcionPaqueteFacturaResponse> ServicioFacturacion.recepcionPaqueteFacturaAsync(recepcionPaqueteFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_recepcionPaqueteFacturaAsync
		return this.FactElectCompraVenta_ServicioFacturacion_recepcionPaqueteFacturaAsync(request);
	}

	public Task<recepcionPaqueteFacturaResponse> recepcionPaqueteFacturaAsync(solicitudRecepcionPaquete SolicitudServicioRecepcionPaquete)
	{
		recepcionPaqueteFactura recepcionPaqueteFactura2 = new recepcionPaqueteFactura();
		recepcionPaqueteFactura2.SolicitudServicioRecepcionPaquete = SolicitudServicioRecepcionPaquete;
		return ((ServicioFacturacion)this).recepcionPaqueteFacturaAsync(recepcionPaqueteFactura2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public verificarComunicacionResponse FactElectCompraVenta_ServicioFacturacion_verificarComunicacion(verificarComunicacion request)
	{
		return base.Channel.verificarComunicacion(request);
	}

	verificarComunicacionResponse ServicioFacturacion.verificarComunicacion(verificarComunicacion request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_verificarComunicacion
		return this.FactElectCompraVenta_ServicioFacturacion_verificarComunicacion(request);
	}

	public respuestaComunicacion verificarComunicacion()
	{
		verificarComunicacion request = new verificarComunicacion();
		return ((ServicioFacturacion)this).verificarComunicacion(request).@return;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<verificarComunicacionResponse> FactElectCompraVenta_ServicioFacturacion_verificarComunicacionAsync(verificarComunicacion request)
	{
		return base.Channel.verificarComunicacionAsync(request);
	}

	Task<verificarComunicacionResponse> ServicioFacturacion.verificarComunicacionAsync(verificarComunicacion request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_verificarComunicacionAsync
		return this.FactElectCompraVenta_ServicioFacturacion_verificarComunicacionAsync(request);
	}

	public Task<verificarComunicacionResponse> verificarComunicacionAsync()
	{
		verificarComunicacion request = new verificarComunicacion();
		return ((ServicioFacturacion)this).verificarComunicacionAsync(request);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public recepcionFacturaResponse FactElectCompraVenta_ServicioFacturacion_recepcionFactura(recepcionFactura request)
	{
		return base.Channel.recepcionFactura(request);
	}

	recepcionFacturaResponse ServicioFacturacion.recepcionFactura(recepcionFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_recepcionFactura
		return this.FactElectCompraVenta_ServicioFacturacion_recepcionFactura(request);
	}

	public respuestaRecepcion recepcionFactura(solicitudRecepcionFactura SolicitudServicioRecepcionFactura)
	{
		recepcionFactura recepcionFactura2 = new recepcionFactura();
		recepcionFactura2.SolicitudServicioRecepcionFactura = SolicitudServicioRecepcionFactura;
		return ((ServicioFacturacion)this).recepcionFactura(recepcionFactura2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<recepcionFacturaResponse> FactElectCompraVenta_ServicioFacturacion_recepcionFacturaAsync(recepcionFactura request)
	{
		return base.Channel.recepcionFacturaAsync(request);
	}

	Task<recepcionFacturaResponse> ServicioFacturacion.recepcionFacturaAsync(recepcionFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_recepcionFacturaAsync
		return this.FactElectCompraVenta_ServicioFacturacion_recepcionFacturaAsync(request);
	}

	public Task<recepcionFacturaResponse> recepcionFacturaAsync(solicitudRecepcionFactura SolicitudServicioRecepcionFactura)
	{
		recepcionFactura recepcionFactura2 = new recepcionFactura();
		recepcionFactura2.SolicitudServicioRecepcionFactura = SolicitudServicioRecepcionFactura;
		return ((ServicioFacturacion)this).recepcionFacturaAsync(recepcionFactura2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public reversionAnulacionFacturaResponse FactElectCompraVenta_ServicioFacturacion_reversionAnulacionFactura(reversionAnulacionFactura request)
	{
		return base.Channel.reversionAnulacionFactura(request);
	}

	reversionAnulacionFacturaResponse ServicioFacturacion.reversionAnulacionFactura(reversionAnulacionFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_reversionAnulacionFactura
		return this.FactElectCompraVenta_ServicioFacturacion_reversionAnulacionFactura(request);
	}

	public respuestaRecepcion reversionAnulacionFactura(solicitudReversionAnulacion SolicitudServicioReversionAnulacionFactura)
	{
		reversionAnulacionFactura reversionAnulacionFactura2 = new reversionAnulacionFactura();
		reversionAnulacionFactura2.SolicitudServicioReversionAnulacionFactura = SolicitudServicioReversionAnulacionFactura;
		return ((ServicioFacturacion)this).reversionAnulacionFactura(reversionAnulacionFactura2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<reversionAnulacionFacturaResponse> FactElectCompraVenta_ServicioFacturacion_reversionAnulacionFacturaAsync(reversionAnulacionFactura request)
	{
		return base.Channel.reversionAnulacionFacturaAsync(request);
	}

	Task<reversionAnulacionFacturaResponse> ServicioFacturacion.reversionAnulacionFacturaAsync(reversionAnulacionFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_reversionAnulacionFacturaAsync
		return this.FactElectCompraVenta_ServicioFacturacion_reversionAnulacionFacturaAsync(request);
	}

	public Task<reversionAnulacionFacturaResponse> reversionAnulacionFacturaAsync(solicitudReversionAnulacion SolicitudServicioReversionAnulacionFactura)
	{
		reversionAnulacionFactura reversionAnulacionFactura2 = new reversionAnulacionFactura();
		reversionAnulacionFactura2.SolicitudServicioReversionAnulacionFactura = SolicitudServicioReversionAnulacionFactura;
		return ((ServicioFacturacion)this).reversionAnulacionFacturaAsync(reversionAnulacionFactura2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public validacionRecepcionMasivaFacturaResponse FactElectCompraVenta_ServicioFacturacion_validacionRecepcionMasivaFactura(validacionRecepcionMasivaFactura request)
	{
		return base.Channel.validacionRecepcionMasivaFactura(request);
	}

	validacionRecepcionMasivaFacturaResponse ServicioFacturacion.validacionRecepcionMasivaFactura(validacionRecepcionMasivaFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_validacionRecepcionMasivaFactura
		return this.FactElectCompraVenta_ServicioFacturacion_validacionRecepcionMasivaFactura(request);
	}

	public respuestaRecepcion validacionRecepcionMasivaFactura(solicitudValidacionRecepcion SolicitudServicioValidacionRecepcionMasiva)
	{
		validacionRecepcionMasivaFactura validacionRecepcionMasivaFactura2 = new validacionRecepcionMasivaFactura();
		validacionRecepcionMasivaFactura2.SolicitudServicioValidacionRecepcionMasiva = SolicitudServicioValidacionRecepcionMasiva;
		return ((ServicioFacturacion)this).validacionRecepcionMasivaFactura(validacionRecepcionMasivaFactura2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<validacionRecepcionMasivaFacturaResponse> FactElectCompraVenta_ServicioFacturacion_validacionRecepcionMasivaFacturaAsync(validacionRecepcionMasivaFactura request)
	{
		return base.Channel.validacionRecepcionMasivaFacturaAsync(request);
	}

	Task<validacionRecepcionMasivaFacturaResponse> ServicioFacturacion.validacionRecepcionMasivaFacturaAsync(validacionRecepcionMasivaFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_validacionRecepcionMasivaFacturaAsync
		return this.FactElectCompraVenta_ServicioFacturacion_validacionRecepcionMasivaFacturaAsync(request);
	}

	public Task<validacionRecepcionMasivaFacturaResponse> validacionRecepcionMasivaFacturaAsync(solicitudValidacionRecepcion SolicitudServicioValidacionRecepcionMasiva)
	{
		validacionRecepcionMasivaFactura validacionRecepcionMasivaFactura2 = new validacionRecepcionMasivaFactura();
		validacionRecepcionMasivaFactura2.SolicitudServicioValidacionRecepcionMasiva = SolicitudServicioValidacionRecepcionMasiva;
		return ((ServicioFacturacion)this).validacionRecepcionMasivaFacturaAsync(validacionRecepcionMasivaFactura2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public recepcionAnexosResponse FactElectCompraVenta_ServicioFacturacion_recepcionAnexos(recepcionAnexos request)
	{
		return base.Channel.recepcionAnexos(request);
	}

	recepcionAnexosResponse ServicioFacturacion.recepcionAnexos(recepcionAnexos request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_recepcionAnexos
		return this.FactElectCompraVenta_ServicioFacturacion_recepcionAnexos(request);
	}

	public respuestaRecepcion recepcionAnexos(solicitudRecepcionAnexos SolicitudRecepcionAnexos)
	{
		recepcionAnexos recepcionAnexos2 = new recepcionAnexos();
		recepcionAnexos2.SolicitudRecepcionAnexos = SolicitudRecepcionAnexos;
		return ((ServicioFacturacion)this).recepcionAnexos(recepcionAnexos2).RespuestaRecepcionAnexos;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<recepcionAnexosResponse> FactElectCompraVenta_ServicioFacturacion_recepcionAnexosAsync(recepcionAnexos request)
	{
		return base.Channel.recepcionAnexosAsync(request);
	}

	Task<recepcionAnexosResponse> ServicioFacturacion.recepcionAnexosAsync(recepcionAnexos request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_recepcionAnexosAsync
		return this.FactElectCompraVenta_ServicioFacturacion_recepcionAnexosAsync(request);
	}

	public Task<recepcionAnexosResponse> recepcionAnexosAsync(solicitudRecepcionAnexos SolicitudRecepcionAnexos)
	{
		recepcionAnexos recepcionAnexos2 = new recepcionAnexos();
		recepcionAnexos2.SolicitudRecepcionAnexos = SolicitudRecepcionAnexos;
		return ((ServicioFacturacion)this).recepcionAnexosAsync(recepcionAnexos2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public recepcionMasivaFacturaResponse FactElectCompraVenta_ServicioFacturacion_recepcionMasivaFactura(recepcionMasivaFactura request)
	{
		return base.Channel.recepcionMasivaFactura(request);
	}

	recepcionMasivaFacturaResponse ServicioFacturacion.recepcionMasivaFactura(recepcionMasivaFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_recepcionMasivaFactura
		return this.FactElectCompraVenta_ServicioFacturacion_recepcionMasivaFactura(request);
	}

	public respuestaRecepcion recepcionMasivaFactura(solicitudRecepcionMasiva SolicitudServicioRecepcionMasiva)
	{
		recepcionMasivaFactura recepcionMasivaFactura2 = new recepcionMasivaFactura();
		recepcionMasivaFactura2.SolicitudServicioRecepcionMasiva = SolicitudServicioRecepcionMasiva;
		return ((ServicioFacturacion)this).recepcionMasivaFactura(recepcionMasivaFactura2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<recepcionMasivaFacturaResponse> FactElectCompraVenta_ServicioFacturacion_recepcionMasivaFacturaAsync(recepcionMasivaFactura request)
	{
		return base.Channel.recepcionMasivaFacturaAsync(request);
	}

	Task<recepcionMasivaFacturaResponse> ServicioFacturacion.recepcionMasivaFacturaAsync(recepcionMasivaFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_recepcionMasivaFacturaAsync
		return this.FactElectCompraVenta_ServicioFacturacion_recepcionMasivaFacturaAsync(request);
	}

	public Task<recepcionMasivaFacturaResponse> recepcionMasivaFacturaAsync(solicitudRecepcionMasiva SolicitudServicioRecepcionMasiva)
	{
		recepcionMasivaFactura recepcionMasivaFactura2 = new recepcionMasivaFactura();
		recepcionMasivaFactura2.SolicitudServicioRecepcionMasiva = SolicitudServicioRecepcionMasiva;
		return ((ServicioFacturacion)this).recepcionMasivaFacturaAsync(recepcionMasivaFactura2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public verificacionEstadoFacturaResponse FactElectCompraVenta_ServicioFacturacion_verificacionEstadoFactura(verificacionEstadoFactura request)
	{
		return base.Channel.verificacionEstadoFactura(request);
	}

	verificacionEstadoFacturaResponse ServicioFacturacion.verificacionEstadoFactura(verificacionEstadoFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_verificacionEstadoFactura
		return this.FactElectCompraVenta_ServicioFacturacion_verificacionEstadoFactura(request);
	}

	public respuestaRecepcion verificacionEstadoFactura(solicitudVerificacionEstado SolicitudServicioVerificacionEstadoFactura)
	{
		verificacionEstadoFactura verificacionEstadoFactura2 = new verificacionEstadoFactura();
		verificacionEstadoFactura2.SolicitudServicioVerificacionEstadoFactura = SolicitudServicioVerificacionEstadoFactura;
		return ((ServicioFacturacion)this).verificacionEstadoFactura(verificacionEstadoFactura2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<verificacionEstadoFacturaResponse> FactElectCompraVenta_ServicioFacturacion_verificacionEstadoFacturaAsync(verificacionEstadoFactura request)
	{
		return base.Channel.verificacionEstadoFacturaAsync(request);
	}

	Task<verificacionEstadoFacturaResponse> ServicioFacturacion.verificacionEstadoFacturaAsync(verificacionEstadoFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_verificacionEstadoFacturaAsync
		return this.FactElectCompraVenta_ServicioFacturacion_verificacionEstadoFacturaAsync(request);
	}

	public Task<verificacionEstadoFacturaResponse> verificacionEstadoFacturaAsync(solicitudVerificacionEstado SolicitudServicioVerificacionEstadoFactura)
	{
		verificacionEstadoFactura verificacionEstadoFactura2 = new verificacionEstadoFactura();
		verificacionEstadoFactura2.SolicitudServicioVerificacionEstadoFactura = SolicitudServicioVerificacionEstadoFactura;
		return ((ServicioFacturacion)this).verificacionEstadoFacturaAsync(verificacionEstadoFactura2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public validacionRecepcionPaqueteFacturaResponse FactElectCompraVenta_ServicioFacturacion_validacionRecepcionPaqueteFactura(validacionRecepcionPaqueteFactura request)
	{
		return base.Channel.validacionRecepcionPaqueteFactura(request);
	}

	validacionRecepcionPaqueteFacturaResponse ServicioFacturacion.validacionRecepcionPaqueteFactura(validacionRecepcionPaqueteFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_validacionRecepcionPaqueteFactura
		return this.FactElectCompraVenta_ServicioFacturacion_validacionRecepcionPaqueteFactura(request);
	}

	public respuestaRecepcion validacionRecepcionPaqueteFactura(solicitudValidacionRecepcion SolicitudServicioValidacionRecepcionPaquete)
	{
		validacionRecepcionPaqueteFactura validacionRecepcionPaqueteFactura2 = new validacionRecepcionPaqueteFactura();
		validacionRecepcionPaqueteFactura2.SolicitudServicioValidacionRecepcionPaquete = SolicitudServicioValidacionRecepcionPaquete;
		return ((ServicioFacturacion)this).validacionRecepcionPaqueteFactura(validacionRecepcionPaqueteFactura2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<validacionRecepcionPaqueteFacturaResponse> FactElectCompraVenta_ServicioFacturacion_validacionRecepcionPaqueteFacturaAsync(validacionRecepcionPaqueteFactura request)
	{
		return base.Channel.validacionRecepcionPaqueteFacturaAsync(request);
	}

	Task<validacionRecepcionPaqueteFacturaResponse> ServicioFacturacion.validacionRecepcionPaqueteFacturaAsync(validacionRecepcionPaqueteFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_validacionRecepcionPaqueteFacturaAsync
		return this.FactElectCompraVenta_ServicioFacturacion_validacionRecepcionPaqueteFacturaAsync(request);
	}

	public Task<validacionRecepcionPaqueteFacturaResponse> validacionRecepcionPaqueteFacturaAsync(solicitudValidacionRecepcion SolicitudServicioValidacionRecepcionPaquete)
	{
		validacionRecepcionPaqueteFactura validacionRecepcionPaqueteFactura2 = new validacionRecepcionPaqueteFactura();
		validacionRecepcionPaqueteFactura2.SolicitudServicioValidacionRecepcionPaquete = SolicitudServicioValidacionRecepcionPaquete;
		return ((ServicioFacturacion)this).validacionRecepcionPaqueteFacturaAsync(validacionRecepcionPaqueteFactura2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public anulacionFacturaResponse FactElectCompraVenta_ServicioFacturacion_anulacionFactura(anulacionFactura request)
	{
		return base.Channel.anulacionFactura(request);
	}

	anulacionFacturaResponse ServicioFacturacion.anulacionFactura(anulacionFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_anulacionFactura
		return this.FactElectCompraVenta_ServicioFacturacion_anulacionFactura(request);
	}

	public respuestaRecepcion anulacionFactura(solicitudAnulacion SolicitudServicioAnulacionFactura)
	{
		anulacionFactura anulacionFactura2 = new anulacionFactura();
		anulacionFactura2.SolicitudServicioAnulacionFactura = SolicitudServicioAnulacionFactura;
		return ((ServicioFacturacion)this).anulacionFactura(anulacionFactura2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<anulacionFacturaResponse> FactElectCompraVenta_ServicioFacturacion_anulacionFacturaAsync(anulacionFactura request)
	{
		return base.Channel.anulacionFacturaAsync(request);
	}

	Task<anulacionFacturaResponse> ServicioFacturacion.anulacionFacturaAsync(anulacionFactura request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCompraVenta_ServicioFacturacion_anulacionFacturaAsync
		return this.FactElectCompraVenta_ServicioFacturacion_anulacionFacturaAsync(request);
	}

	public Task<anulacionFacturaResponse> anulacionFacturaAsync(solicitudAnulacion SolicitudServicioAnulacionFactura)
	{
		anulacionFactura anulacionFactura2 = new anulacionFactura();
		anulacionFactura2.SolicitudServicioAnulacionFactura = SolicitudServicioAnulacionFactura;
		return ((ServicioFacturacion)this).anulacionFacturaAsync(anulacionFactura2);
	}
}
