using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.Threading.Tasks;

namespace ControlConsumoLib.FactElectCodigos;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
public class ServicioFacturacionCodigosClient : ClientBase<ServicioFacturacionCodigos>, ServicioFacturacionCodigos
{
	public ServicioFacturacionCodigosClient()
	{
	}

	public ServicioFacturacionCodigosClient(string endpointConfigurationName)
		: base(endpointConfigurationName)
	{
	}

	public ServicioFacturacionCodigosClient(string endpointConfigurationName, string remoteAddress)
		: base(endpointConfigurationName, remoteAddress)
	{
	}

	public ServicioFacturacionCodigosClient(string endpointConfigurationName, EndpointAddress remoteAddress)
		: base(endpointConfigurationName, remoteAddress)
	{
	}

	public ServicioFacturacionCodigosClient(Binding binding, EndpointAddress remoteAddress)
		: base(binding, remoteAddress)
	{
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public verificarComunicacionResponse FactElectCodigos_ServicioFacturacionCodigos_verificarComunicacion(verificarComunicacion request)
	{
		return base.Channel.verificarComunicacion(request);
	}

	verificarComunicacionResponse ServicioFacturacionCodigos.verificarComunicacion(verificarComunicacion request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_verificarComunicacion
		return this.FactElectCodigos_ServicioFacturacionCodigos_verificarComunicacion(request);
	}

	public respuestaComunicacion verificarComunicacion()
	{
		verificarComunicacion request = new verificarComunicacion();
		return ((ServicioFacturacionCodigos)this).verificarComunicacion(request).RespuestaComunicacion;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<verificarComunicacionResponse> FactElectCodigos_ServicioFacturacionCodigos_verificarComunicacionAsync(verificarComunicacion request)
	{
		return base.Channel.verificarComunicacionAsync(request);
	}

	Task<verificarComunicacionResponse> ServicioFacturacionCodigos.verificarComunicacionAsync(verificarComunicacion request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_verificarComunicacionAsync
		return this.FactElectCodigos_ServicioFacturacionCodigos_verificarComunicacionAsync(request);
	}

	public Task<verificarComunicacionResponse> verificarComunicacionAsync()
	{
		verificarComunicacion request = new verificarComunicacion();
		return ((ServicioFacturacionCodigos)this).verificarComunicacionAsync(request);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public verificarNitResponse FactElectCodigos_ServicioFacturacionCodigos_verificarNit(verificarNit request)
	{
		return base.Channel.verificarNit(request);
	}

	verificarNitResponse ServicioFacturacionCodigos.verificarNit(verificarNit request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_verificarNit
		return this.FactElectCodigos_ServicioFacturacionCodigos_verificarNit(request);
	}

	public respuestaVerificarNit verificarNit(solicitudVerificarNit SolicitudVerificarNit)
	{
		verificarNit verificarNit2 = new verificarNit();
		verificarNit2.SolicitudVerificarNit = SolicitudVerificarNit;
		return ((ServicioFacturacionCodigos)this).verificarNit(verificarNit2).RespuestaVerificarNit;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<verificarNitResponse> FactElectCodigos_ServicioFacturacionCodigos_verificarNitAsync(verificarNit request)
	{
		return base.Channel.verificarNitAsync(request);
	}

	Task<verificarNitResponse> ServicioFacturacionCodigos.verificarNitAsync(verificarNit request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_verificarNitAsync
		return this.FactElectCodigos_ServicioFacturacionCodigos_verificarNitAsync(request);
	}

	public Task<verificarNitResponse> verificarNitAsync(solicitudVerificarNit SolicitudVerificarNit)
	{
		verificarNit verificarNit2 = new verificarNit();
		verificarNit2.SolicitudVerificarNit = SolicitudVerificarNit;
		return ((ServicioFacturacionCodigos)this).verificarNitAsync(verificarNit2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public cuisMasivoResponse FactElectCodigos_ServicioFacturacionCodigos_cuisMasivo(cuisMasivo request)
	{
		return base.Channel.cuisMasivo(request);
	}

	cuisMasivoResponse ServicioFacturacionCodigos.cuisMasivo(cuisMasivo request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_cuisMasivo
		return this.FactElectCodigos_ServicioFacturacionCodigos_cuisMasivo(request);
	}

	public respuestaCuisMasivo cuisMasivo(solicitudCuisMasivoSistemas SolicitudCuisMasivoSistemas)
	{
		cuisMasivo cuisMasivo2 = new cuisMasivo();
		cuisMasivo2.SolicitudCuisMasivoSistemas = SolicitudCuisMasivoSistemas;
		return ((ServicioFacturacionCodigos)this).cuisMasivo(cuisMasivo2).RespuestaCuisMasivo;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<cuisMasivoResponse> FactElectCodigos_ServicioFacturacionCodigos_cuisMasivoAsync(cuisMasivo request)
	{
		return base.Channel.cuisMasivoAsync(request);
	}

	Task<cuisMasivoResponse> ServicioFacturacionCodigos.cuisMasivoAsync(cuisMasivo request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_cuisMasivoAsync
		return this.FactElectCodigos_ServicioFacturacionCodigos_cuisMasivoAsync(request);
	}

	public Task<cuisMasivoResponse> cuisMasivoAsync(solicitudCuisMasivoSistemas SolicitudCuisMasivoSistemas)
	{
		cuisMasivo cuisMasivo2 = new cuisMasivo();
		cuisMasivo2.SolicitudCuisMasivoSistemas = SolicitudCuisMasivoSistemas;
		return ((ServicioFacturacionCodigos)this).cuisMasivoAsync(cuisMasivo2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public cufdResponse FactElectCodigos_ServicioFacturacionCodigos_cufd(cufd request)
	{
		return base.Channel.cufd(request);
	}

	cufdResponse ServicioFacturacionCodigos.cufd(cufd request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_cufd
		return this.FactElectCodigos_ServicioFacturacionCodigos_cufd(request);
	}

	public respuestaCufd cufd(solicitudCufd SolicitudCufd)
	{
		cufd cufd2 = new cufd();
		cufd2.SolicitudCufd = SolicitudCufd;
		return ((ServicioFacturacionCodigos)this).cufd(cufd2).RespuestaCufd;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<cufdResponse> FactElectCodigos_ServicioFacturacionCodigos_cufdAsync(cufd request)
	{
		return base.Channel.cufdAsync(request);
	}

	Task<cufdResponse> ServicioFacturacionCodigos.cufdAsync(cufd request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_cufdAsync
		return this.FactElectCodigos_ServicioFacturacionCodigos_cufdAsync(request);
	}

	public Task<cufdResponse> cufdAsync(solicitudCufd SolicitudCufd)
	{
		cufd cufd2 = new cufd();
		cufd2.SolicitudCufd = SolicitudCufd;
		return ((ServicioFacturacionCodigos)this).cufdAsync(cufd2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public notificaCertificadoRevocadoResponse FactElectCodigos_ServicioFacturacionCodigos_notificaCertificadoRevocado(notificaCertificadoRevocado request)
	{
		return base.Channel.notificaCertificadoRevocado(request);
	}

	notificaCertificadoRevocadoResponse ServicioFacturacionCodigos.notificaCertificadoRevocado(notificaCertificadoRevocado request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_notificaCertificadoRevocado
		return this.FactElectCodigos_ServicioFacturacionCodigos_notificaCertificadoRevocado(request);
	}

	public respuestaNotificaRevocado notificaCertificadoRevocado(solicitudNotifcaRevocado SolicitudNotificaRevocado)
	{
		notificaCertificadoRevocado notificaCertificadoRevocado2 = new notificaCertificadoRevocado();
		notificaCertificadoRevocado2.SolicitudNotificaRevocado = SolicitudNotificaRevocado;
		return ((ServicioFacturacionCodigos)this).notificaCertificadoRevocado(notificaCertificadoRevocado2).RespuestaNotificaRevocado;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<notificaCertificadoRevocadoResponse> FactElectCodigos_ServicioFacturacionCodigos_notificaCertificadoRevocadoAsync(notificaCertificadoRevocado request)
	{
		return base.Channel.notificaCertificadoRevocadoAsync(request);
	}

	Task<notificaCertificadoRevocadoResponse> ServicioFacturacionCodigos.notificaCertificadoRevocadoAsync(notificaCertificadoRevocado request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_notificaCertificadoRevocadoAsync
		return this.FactElectCodigos_ServicioFacturacionCodigos_notificaCertificadoRevocadoAsync(request);
	}

	public Task<notificaCertificadoRevocadoResponse> notificaCertificadoRevocadoAsync(solicitudNotifcaRevocado SolicitudNotificaRevocado)
	{
		notificaCertificadoRevocado notificaCertificadoRevocado2 = new notificaCertificadoRevocado();
		notificaCertificadoRevocado2.SolicitudNotificaRevocado = SolicitudNotificaRevocado;
		return ((ServicioFacturacionCodigos)this).notificaCertificadoRevocadoAsync(notificaCertificadoRevocado2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public cuisResponse FactElectCodigos_ServicioFacturacionCodigos_cuis(cuis request)
	{
		return base.Channel.cuis(request);
	}

	cuisResponse ServicioFacturacionCodigos.cuis(cuis request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_cuis
		return this.FactElectCodigos_ServicioFacturacionCodigos_cuis(request);
	}

	public respuestaCuis cuis(solicitudCuis SolicitudCuis)
	{
		cuis cuis2 = new cuis();
		cuis2.SolicitudCuis = SolicitudCuis;
		return ((ServicioFacturacionCodigos)this).cuis(cuis2).RespuestaCuis;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<cuisResponse> FactElectCodigos_ServicioFacturacionCodigos_cuisAsync(cuis request)
	{
		return base.Channel.cuisAsync(request);
	}

	Task<cuisResponse> ServicioFacturacionCodigos.cuisAsync(cuis request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_cuisAsync
		return this.FactElectCodigos_ServicioFacturacionCodigos_cuisAsync(request);
	}

	public Task<cuisResponse> cuisAsync(solicitudCuis SolicitudCuis)
	{
		cuis cuis2 = new cuis();
		cuis2.SolicitudCuis = SolicitudCuis;
		return ((ServicioFacturacionCodigos)this).cuisAsync(cuis2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public cufdMasivoResponse FactElectCodigos_ServicioFacturacionCodigos_cufdMasivo(cufdMasivo request)
	{
		return base.Channel.cufdMasivo(request);
	}

	cufdMasivoResponse ServicioFacturacionCodigos.cufdMasivo(cufdMasivo request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_cufdMasivo
		return this.FactElectCodigos_ServicioFacturacionCodigos_cufdMasivo(request);
	}

	public respuestaCufdMasivo cufdMasivo(solicitudCufdMasivo SolicitudCufdMasivo)
	{
		cufdMasivo cufdMasivo2 = new cufdMasivo();
		cufdMasivo2.SolicitudCufdMasivo = SolicitudCufdMasivo;
		return ((ServicioFacturacionCodigos)this).cufdMasivo(cufdMasivo2).RespuestaCufdMasivo;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<cufdMasivoResponse> FactElectCodigos_ServicioFacturacionCodigos_cufdMasivoAsync(cufdMasivo request)
	{
		return base.Channel.cufdMasivoAsync(request);
	}

	Task<cufdMasivoResponse> ServicioFacturacionCodigos.cufdMasivoAsync(cufdMasivo request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectCodigos_ServicioFacturacionCodigos_cufdMasivoAsync
		return this.FactElectCodigos_ServicioFacturacionCodigos_cufdMasivoAsync(request);
	}

	public Task<cufdMasivoResponse> cufdMasivoAsync(solicitudCufdMasivo SolicitudCufdMasivo)
	{
		cufdMasivo cufdMasivo2 = new cufdMasivo();
		cufdMasivo2.SolicitudCufdMasivo = SolicitudCufdMasivo;
		return ((ServicioFacturacionCodigos)this).cufdMasivoAsync(cufdMasivo2);
	}
}
