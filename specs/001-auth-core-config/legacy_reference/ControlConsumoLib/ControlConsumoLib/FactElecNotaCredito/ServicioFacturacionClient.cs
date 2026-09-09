using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.Threading.Tasks;

namespace ControlConsumoLib.FactElecNotaCredito;

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
	public anulacionDocumentoAjusteResponse FactElecNotaCredito_ServicioFacturacion_anulacionDocumentoAjuste(anulacionDocumentoAjuste request)
	{
		return base.Channel.anulacionDocumentoAjuste(request);
	}

	anulacionDocumentoAjusteResponse ServicioFacturacion.anulacionDocumentoAjuste(anulacionDocumentoAjuste request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_anulacionDocumentoAjuste
		return this.FactElecNotaCredito_ServicioFacturacion_anulacionDocumentoAjuste(request);
	}

	public respuestaRecepcion anulacionDocumentoAjuste(solicitudAnulacion SolicitudServicioAnulacionDocumentoAjuste)
	{
		anulacionDocumentoAjuste anulacionDocumentoAjuste2 = new anulacionDocumentoAjuste();
		anulacionDocumentoAjuste2.SolicitudServicioAnulacionDocumentoAjuste = SolicitudServicioAnulacionDocumentoAjuste;
		return ((ServicioFacturacion)this).anulacionDocumentoAjuste(anulacionDocumentoAjuste2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<anulacionDocumentoAjusteResponse> FactElecNotaCredito_ServicioFacturacion_anulacionDocumentoAjusteAsync(anulacionDocumentoAjuste request)
	{
		return base.Channel.anulacionDocumentoAjusteAsync(request);
	}

	Task<anulacionDocumentoAjusteResponse> ServicioFacturacion.anulacionDocumentoAjusteAsync(anulacionDocumentoAjuste request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_anulacionDocumentoAjusteAsync
		return this.FactElecNotaCredito_ServicioFacturacion_anulacionDocumentoAjusteAsync(request);
	}

	public Task<anulacionDocumentoAjusteResponse> anulacionDocumentoAjusteAsync(solicitudAnulacion SolicitudServicioAnulacionDocumentoAjuste)
	{
		anulacionDocumentoAjuste anulacionDocumentoAjuste2 = new anulacionDocumentoAjuste();
		anulacionDocumentoAjuste2.SolicitudServicioAnulacionDocumentoAjuste = SolicitudServicioAnulacionDocumentoAjuste;
		return ((ServicioFacturacion)this).anulacionDocumentoAjusteAsync(anulacionDocumentoAjuste2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public reversionAnulacionDocumentoAjusteResponse FactElecNotaCredito_ServicioFacturacion_reversionAnulacionDocumentoAjuste(reversionAnulacionDocumentoAjuste request)
	{
		return base.Channel.reversionAnulacionDocumentoAjuste(request);
	}

	reversionAnulacionDocumentoAjusteResponse ServicioFacturacion.reversionAnulacionDocumentoAjuste(reversionAnulacionDocumentoAjuste request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_reversionAnulacionDocumentoAjuste
		return this.FactElecNotaCredito_ServicioFacturacion_reversionAnulacionDocumentoAjuste(request);
	}

	public respuestaRecepcion reversionAnulacionDocumentoAjuste(solicitudReversionAnulacion SolicitudServicioReversionAnulacionDocumentoAjuste)
	{
		reversionAnulacionDocumentoAjuste reversionAnulacionDocumentoAjuste2 = new reversionAnulacionDocumentoAjuste();
		reversionAnulacionDocumentoAjuste2.SolicitudServicioReversionAnulacionDocumentoAjuste = SolicitudServicioReversionAnulacionDocumentoAjuste;
		return ((ServicioFacturacion)this).reversionAnulacionDocumentoAjuste(reversionAnulacionDocumentoAjuste2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<reversionAnulacionDocumentoAjusteResponse> FactElecNotaCredito_ServicioFacturacion_reversionAnulacionDocumentoAjusteAsync(reversionAnulacionDocumentoAjuste request)
	{
		return base.Channel.reversionAnulacionDocumentoAjusteAsync(request);
	}

	Task<reversionAnulacionDocumentoAjusteResponse> ServicioFacturacion.reversionAnulacionDocumentoAjusteAsync(reversionAnulacionDocumentoAjuste request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_reversionAnulacionDocumentoAjusteAsync
		return this.FactElecNotaCredito_ServicioFacturacion_reversionAnulacionDocumentoAjusteAsync(request);
	}

	public Task<reversionAnulacionDocumentoAjusteResponse> reversionAnulacionDocumentoAjusteAsync(solicitudReversionAnulacion SolicitudServicioReversionAnulacionDocumentoAjuste)
	{
		reversionAnulacionDocumentoAjuste reversionAnulacionDocumentoAjuste2 = new reversionAnulacionDocumentoAjuste();
		reversionAnulacionDocumentoAjuste2.SolicitudServicioReversionAnulacionDocumentoAjuste = SolicitudServicioReversionAnulacionDocumentoAjuste;
		return ((ServicioFacturacion)this).reversionAnulacionDocumentoAjusteAsync(reversionAnulacionDocumentoAjuste2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public verificarComunicacionResponse FactElecNotaCredito_ServicioFacturacion_verificarComunicacion(verificarComunicacion request)
	{
		return base.Channel.verificarComunicacion(request);
	}

	verificarComunicacionResponse ServicioFacturacion.verificarComunicacion(verificarComunicacion request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_verificarComunicacion
		return this.FactElecNotaCredito_ServicioFacturacion_verificarComunicacion(request);
	}

	public respuestaComunicacion verificarComunicacion()
	{
		verificarComunicacion request = new verificarComunicacion();
		return ((ServicioFacturacion)this).verificarComunicacion(request).@return;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<verificarComunicacionResponse> FactElecNotaCredito_ServicioFacturacion_verificarComunicacionAsync(verificarComunicacion request)
	{
		return base.Channel.verificarComunicacionAsync(request);
	}

	Task<verificarComunicacionResponse> ServicioFacturacion.verificarComunicacionAsync(verificarComunicacion request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_verificarComunicacionAsync
		return this.FactElecNotaCredito_ServicioFacturacion_verificarComunicacionAsync(request);
	}

	public Task<verificarComunicacionResponse> verificarComunicacionAsync()
	{
		verificarComunicacion request = new verificarComunicacion();
		return ((ServicioFacturacion)this).verificarComunicacionAsync(request);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public recepcionDocumentoAjusteResponse FactElecNotaCredito_ServicioFacturacion_recepcionDocumentoAjuste(recepcionDocumentoAjuste request)
	{
		return base.Channel.recepcionDocumentoAjuste(request);
	}

	recepcionDocumentoAjusteResponse ServicioFacturacion.recepcionDocumentoAjuste(recepcionDocumentoAjuste request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_recepcionDocumentoAjuste
		return this.FactElecNotaCredito_ServicioFacturacion_recepcionDocumentoAjuste(request);
	}

	public respuestaRecepcion recepcionDocumentoAjuste(solicitudRecepcionFactura SolicitudServicioRecepcionDocumentoAjuste)
	{
		recepcionDocumentoAjuste recepcionDocumentoAjuste2 = new recepcionDocumentoAjuste();
		recepcionDocumentoAjuste2.SolicitudServicioRecepcionDocumentoAjuste = SolicitudServicioRecepcionDocumentoAjuste;
		return ((ServicioFacturacion)this).recepcionDocumentoAjuste(recepcionDocumentoAjuste2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<recepcionDocumentoAjusteResponse> FactElecNotaCredito_ServicioFacturacion_recepcionDocumentoAjusteAsync(recepcionDocumentoAjuste request)
	{
		return base.Channel.recepcionDocumentoAjusteAsync(request);
	}

	Task<recepcionDocumentoAjusteResponse> ServicioFacturacion.recepcionDocumentoAjusteAsync(recepcionDocumentoAjuste request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_recepcionDocumentoAjusteAsync
		return this.FactElecNotaCredito_ServicioFacturacion_recepcionDocumentoAjusteAsync(request);
	}

	public Task<recepcionDocumentoAjusteResponse> recepcionDocumentoAjusteAsync(solicitudRecepcionFactura SolicitudServicioRecepcionDocumentoAjuste)
	{
		recepcionDocumentoAjuste recepcionDocumentoAjuste2 = new recepcionDocumentoAjuste();
		recepcionDocumentoAjuste2.SolicitudServicioRecepcionDocumentoAjuste = SolicitudServicioRecepcionDocumentoAjuste;
		return ((ServicioFacturacion)this).recepcionDocumentoAjusteAsync(recepcionDocumentoAjuste2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public verificacionEstadoDocumentoAjusteResponse FactElecNotaCredito_ServicioFacturacion_verificacionEstadoDocumentoAjuste(verificacionEstadoDocumentoAjuste request)
	{
		return base.Channel.verificacionEstadoDocumentoAjuste(request);
	}

	verificacionEstadoDocumentoAjusteResponse ServicioFacturacion.verificacionEstadoDocumentoAjuste(verificacionEstadoDocumentoAjuste request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_verificacionEstadoDocumentoAjuste
		return this.FactElecNotaCredito_ServicioFacturacion_verificacionEstadoDocumentoAjuste(request);
	}

	public respuestaRecepcion verificacionEstadoDocumentoAjuste(solicitudVerificacionEstado SolicitudServicioVerificacionEstadoDocumentoAjuste)
	{
		verificacionEstadoDocumentoAjuste verificacionEstadoDocumentoAjuste2 = new verificacionEstadoDocumentoAjuste();
		verificacionEstadoDocumentoAjuste2.SolicitudServicioVerificacionEstadoDocumentoAjuste = SolicitudServicioVerificacionEstadoDocumentoAjuste;
		return ((ServicioFacturacion)this).verificacionEstadoDocumentoAjuste(verificacionEstadoDocumentoAjuste2).RespuestaServicioFacturacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<verificacionEstadoDocumentoAjusteResponse> FactElecNotaCredito_ServicioFacturacion_verificacionEstadoDocumentoAjusteAsync(verificacionEstadoDocumentoAjuste request)
	{
		return base.Channel.verificacionEstadoDocumentoAjusteAsync(request);
	}

	Task<verificacionEstadoDocumentoAjusteResponse> ServicioFacturacion.verificacionEstadoDocumentoAjusteAsync(verificacionEstadoDocumentoAjuste request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElecNotaCredito_ServicioFacturacion_verificacionEstadoDocumentoAjusteAsync
		return this.FactElecNotaCredito_ServicioFacturacion_verificacionEstadoDocumentoAjusteAsync(request);
	}

	public Task<verificacionEstadoDocumentoAjusteResponse> verificacionEstadoDocumentoAjusteAsync(solicitudVerificacionEstado SolicitudServicioVerificacionEstadoDocumentoAjuste)
	{
		verificacionEstadoDocumentoAjuste verificacionEstadoDocumentoAjuste2 = new verificacionEstadoDocumentoAjuste();
		verificacionEstadoDocumentoAjuste2.SolicitudServicioVerificacionEstadoDocumentoAjuste = SolicitudServicioVerificacionEstadoDocumentoAjuste;
		return ((ServicioFacturacion)this).verificacionEstadoDocumentoAjusteAsync(verificacionEstadoDocumentoAjuste2);
	}
}
