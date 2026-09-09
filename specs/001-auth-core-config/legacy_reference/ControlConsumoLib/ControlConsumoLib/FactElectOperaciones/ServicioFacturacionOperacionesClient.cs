using System.CodeDom.Compiler;
using System.ComponentModel;
using System.Diagnostics;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.Threading.Tasks;

namespace ControlConsumoLib.FactElectOperaciones;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
public class ServicioFacturacionOperacionesClient : ClientBase<ServicioFacturacionOperaciones>, ServicioFacturacionOperaciones
{
	public ServicioFacturacionOperacionesClient()
	{
	}

	public ServicioFacturacionOperacionesClient(string endpointConfigurationName)
		: base(endpointConfigurationName)
	{
	}

	public ServicioFacturacionOperacionesClient(string endpointConfigurationName, string remoteAddress)
		: base(endpointConfigurationName, remoteAddress)
	{
	}

	public ServicioFacturacionOperacionesClient(string endpointConfigurationName, EndpointAddress remoteAddress)
		: base(endpointConfigurationName, remoteAddress)
	{
	}

	public ServicioFacturacionOperacionesClient(Binding binding, EndpointAddress remoteAddress)
		: base(binding, remoteAddress)
	{
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public verificarComunicacionResponse FactElectOperaciones_ServicioFacturacionOperaciones_verificarComunicacion(verificarComunicacion request)
	{
		return base.Channel.verificarComunicacion(request);
	}

	verificarComunicacionResponse ServicioFacturacionOperaciones.verificarComunicacion(verificarComunicacion request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_verificarComunicacion
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_verificarComunicacion(request);
	}

	public respuestaComunicacion verificarComunicacion()
	{
		verificarComunicacion request = new verificarComunicacion();
		return ((ServicioFacturacionOperaciones)this).verificarComunicacion(request).@return;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<verificarComunicacionResponse> FactElectOperaciones_ServicioFacturacionOperaciones_verificarComunicacionAsync(verificarComunicacion request)
	{
		return base.Channel.verificarComunicacionAsync(request);
	}

	Task<verificarComunicacionResponse> ServicioFacturacionOperaciones.verificarComunicacionAsync(verificarComunicacion request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_verificarComunicacionAsync
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_verificarComunicacionAsync(request);
	}

	public Task<verificarComunicacionResponse> verificarComunicacionAsync()
	{
		verificarComunicacion request = new verificarComunicacion();
		return ((ServicioFacturacionOperaciones)this).verificarComunicacionAsync(request);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public registroPuntoVentaResponse FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVenta(registroPuntoVenta request)
	{
		return base.Channel.registroPuntoVenta(request);
	}

	registroPuntoVentaResponse ServicioFacturacionOperaciones.registroPuntoVenta(registroPuntoVenta request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVenta
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVenta(request);
	}

	public respuestaRegistroPuntoVenta registroPuntoVenta(solicitudRegistroPuntoVenta SolicitudRegistroPuntoVenta)
	{
		registroPuntoVenta registroPuntoVenta2 = new registroPuntoVenta();
		registroPuntoVenta2.SolicitudRegistroPuntoVenta = SolicitudRegistroPuntoVenta;
		return ((ServicioFacturacionOperaciones)this).registroPuntoVenta(registroPuntoVenta2).RespuestaRegistroPuntoVenta;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<registroPuntoVentaResponse> FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVentaAsync(registroPuntoVenta request)
	{
		return base.Channel.registroPuntoVentaAsync(request);
	}

	Task<registroPuntoVentaResponse> ServicioFacturacionOperaciones.registroPuntoVentaAsync(registroPuntoVenta request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVentaAsync
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVentaAsync(request);
	}

	public Task<registroPuntoVentaResponse> registroPuntoVentaAsync(solicitudRegistroPuntoVenta SolicitudRegistroPuntoVenta)
	{
		registroPuntoVenta registroPuntoVenta2 = new registroPuntoVenta();
		registroPuntoVenta2.SolicitudRegistroPuntoVenta = SolicitudRegistroPuntoVenta;
		return ((ServicioFacturacionOperaciones)this).registroPuntoVentaAsync(registroPuntoVenta2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public registroPuntoVentaComisionistaResponse FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVentaComisionista(registroPuntoVentaComisionista request)
	{
		return base.Channel.registroPuntoVentaComisionista(request);
	}

	registroPuntoVentaComisionistaResponse ServicioFacturacionOperaciones.registroPuntoVentaComisionista(registroPuntoVentaComisionista request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVentaComisionista
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVentaComisionista(request);
	}

	public respuestaPuntoVentaComisionista registroPuntoVentaComisionista(solicitudPuntoVentaComisionista SolicitudPuntoVentaComisionista)
	{
		registroPuntoVentaComisionista registroPuntoVentaComisionista2 = new registroPuntoVentaComisionista();
		registroPuntoVentaComisionista2.SolicitudPuntoVentaComisionista = SolicitudPuntoVentaComisionista;
		return ((ServicioFacturacionOperaciones)this).registroPuntoVentaComisionista(registroPuntoVentaComisionista2).RespuestaPuntoVentaComisionista;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<registroPuntoVentaComisionistaResponse> FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVentaComisionistaAsync(registroPuntoVentaComisionista request)
	{
		return base.Channel.registroPuntoVentaComisionistaAsync(request);
	}

	Task<registroPuntoVentaComisionistaResponse> ServicioFacturacionOperaciones.registroPuntoVentaComisionistaAsync(registroPuntoVentaComisionista request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVentaComisionistaAsync
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_registroPuntoVentaComisionistaAsync(request);
	}

	public Task<registroPuntoVentaComisionistaResponse> registroPuntoVentaComisionistaAsync(solicitudPuntoVentaComisionista SolicitudPuntoVentaComisionista)
	{
		registroPuntoVentaComisionista registroPuntoVentaComisionista2 = new registroPuntoVentaComisionista();
		registroPuntoVentaComisionista2.SolicitudPuntoVentaComisionista = SolicitudPuntoVentaComisionista;
		return ((ServicioFacturacionOperaciones)this).registroPuntoVentaComisionistaAsync(registroPuntoVentaComisionista2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public cierreOperacionesSistemaResponse FactElectOperaciones_ServicioFacturacionOperaciones_cierreOperacionesSistema(cierreOperacionesSistema request)
	{
		return base.Channel.cierreOperacionesSistema(request);
	}

	cierreOperacionesSistemaResponse ServicioFacturacionOperaciones.cierreOperacionesSistema(cierreOperacionesSistema request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_cierreOperacionesSistema
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_cierreOperacionesSistema(request);
	}

	public respuestaCierreSistemas cierreOperacionesSistema(solicitudOperaciones SolicitudOperaciones)
	{
		cierreOperacionesSistema cierreOperacionesSistema2 = new cierreOperacionesSistema();
		cierreOperacionesSistema2.SolicitudOperaciones = SolicitudOperaciones;
		return ((ServicioFacturacionOperaciones)this).cierreOperacionesSistema(cierreOperacionesSistema2).RespuestaCierreSistemas;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<cierreOperacionesSistemaResponse> FactElectOperaciones_ServicioFacturacionOperaciones_cierreOperacionesSistemaAsync(cierreOperacionesSistema request)
	{
		return base.Channel.cierreOperacionesSistemaAsync(request);
	}

	Task<cierreOperacionesSistemaResponse> ServicioFacturacionOperaciones.cierreOperacionesSistemaAsync(cierreOperacionesSistema request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_cierreOperacionesSistemaAsync
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_cierreOperacionesSistemaAsync(request);
	}

	public Task<cierreOperacionesSistemaResponse> cierreOperacionesSistemaAsync(solicitudOperaciones SolicitudOperaciones)
	{
		cierreOperacionesSistema cierreOperacionesSistema2 = new cierreOperacionesSistema();
		cierreOperacionesSistema2.SolicitudOperaciones = SolicitudOperaciones;
		return ((ServicioFacturacionOperaciones)this).cierreOperacionesSistemaAsync(cierreOperacionesSistema2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public consultaEventoSignificativoResponse FactElectOperaciones_ServicioFacturacionOperaciones_consultaEventoSignificativo(consultaEventoSignificativo request)
	{
		return base.Channel.consultaEventoSignificativo(request);
	}

	consultaEventoSignificativoResponse ServicioFacturacionOperaciones.consultaEventoSignificativo(consultaEventoSignificativo request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_consultaEventoSignificativo
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_consultaEventoSignificativo(request);
	}

	public respuestaListaEventos consultaEventoSignificativo(solicitudConsultaEvento SolicitudConsultaEvento)
	{
		consultaEventoSignificativo consultaEventoSignificativo2 = new consultaEventoSignificativo();
		consultaEventoSignificativo2.SolicitudConsultaEvento = SolicitudConsultaEvento;
		return ((ServicioFacturacionOperaciones)this).consultaEventoSignificativo(consultaEventoSignificativo2).RespuestaListaEventos;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<consultaEventoSignificativoResponse> FactElectOperaciones_ServicioFacturacionOperaciones_consultaEventoSignificativoAsync(consultaEventoSignificativo request)
	{
		return base.Channel.consultaEventoSignificativoAsync(request);
	}

	Task<consultaEventoSignificativoResponse> ServicioFacturacionOperaciones.consultaEventoSignificativoAsync(consultaEventoSignificativo request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_consultaEventoSignificativoAsync
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_consultaEventoSignificativoAsync(request);
	}

	public Task<consultaEventoSignificativoResponse> consultaEventoSignificativoAsync(solicitudConsultaEvento SolicitudConsultaEvento)
	{
		consultaEventoSignificativo consultaEventoSignificativo2 = new consultaEventoSignificativo();
		consultaEventoSignificativo2.SolicitudConsultaEvento = SolicitudConsultaEvento;
		return ((ServicioFacturacionOperaciones)this).consultaEventoSignificativoAsync(consultaEventoSignificativo2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public consultaPuntoVentaResponse FactElectOperaciones_ServicioFacturacionOperaciones_consultaPuntoVenta(consultaPuntoVenta request)
	{
		return base.Channel.consultaPuntoVenta(request);
	}

	consultaPuntoVentaResponse ServicioFacturacionOperaciones.consultaPuntoVenta(consultaPuntoVenta request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_consultaPuntoVenta
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_consultaPuntoVenta(request);
	}

	public respuestaConsultaPuntoVenta consultaPuntoVenta(solicitudConsultaPuntoVenta SolicitudConsultaPuntoVenta)
	{
		consultaPuntoVenta consultaPuntoVenta2 = new consultaPuntoVenta();
		consultaPuntoVenta2.SolicitudConsultaPuntoVenta = SolicitudConsultaPuntoVenta;
		return ((ServicioFacturacionOperaciones)this).consultaPuntoVenta(consultaPuntoVenta2).RespuestaConsultaPuntoVenta;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<consultaPuntoVentaResponse> FactElectOperaciones_ServicioFacturacionOperaciones_consultaPuntoVentaAsync(consultaPuntoVenta request)
	{
		return base.Channel.consultaPuntoVentaAsync(request);
	}

	Task<consultaPuntoVentaResponse> ServicioFacturacionOperaciones.consultaPuntoVentaAsync(consultaPuntoVenta request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_consultaPuntoVentaAsync
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_consultaPuntoVentaAsync(request);
	}

	public Task<consultaPuntoVentaResponse> consultaPuntoVentaAsync(solicitudConsultaPuntoVenta SolicitudConsultaPuntoVenta)
	{
		consultaPuntoVenta consultaPuntoVenta2 = new consultaPuntoVenta();
		consultaPuntoVenta2.SolicitudConsultaPuntoVenta = SolicitudConsultaPuntoVenta;
		return ((ServicioFacturacionOperaciones)this).consultaPuntoVentaAsync(consultaPuntoVenta2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public registroEventoSignificativoResponse FactElectOperaciones_ServicioFacturacionOperaciones_registroEventoSignificativo(registroEventoSignificativo request)
	{
		return base.Channel.registroEventoSignificativo(request);
	}

	registroEventoSignificativoResponse ServicioFacturacionOperaciones.registroEventoSignificativo(registroEventoSignificativo request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_registroEventoSignificativo
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_registroEventoSignificativo(request);
	}

	public respuestaListaEventos registroEventoSignificativo(solicitudEventoSignificativo SolicitudEventoSignificativo)
	{
		registroEventoSignificativo registroEventoSignificativo2 = new registroEventoSignificativo();
		registroEventoSignificativo2.SolicitudEventoSignificativo = SolicitudEventoSignificativo;
		return ((ServicioFacturacionOperaciones)this).registroEventoSignificativo(registroEventoSignificativo2).RespuestaListaEventos;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<registroEventoSignificativoResponse> FactElectOperaciones_ServicioFacturacionOperaciones_registroEventoSignificativoAsync(registroEventoSignificativo request)
	{
		return base.Channel.registroEventoSignificativoAsync(request);
	}

	Task<registroEventoSignificativoResponse> ServicioFacturacionOperaciones.registroEventoSignificativoAsync(registroEventoSignificativo request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_registroEventoSignificativoAsync
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_registroEventoSignificativoAsync(request);
	}

	public Task<registroEventoSignificativoResponse> registroEventoSignificativoAsync(solicitudEventoSignificativo SolicitudEventoSignificativo)
	{
		registroEventoSignificativo registroEventoSignificativo2 = new registroEventoSignificativo();
		registroEventoSignificativo2.SolicitudEventoSignificativo = SolicitudEventoSignificativo;
		return ((ServicioFacturacionOperaciones)this).registroEventoSignificativoAsync(registroEventoSignificativo2);
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public cierrePuntoVentaResponse FactElectOperaciones_ServicioFacturacionOperaciones_cierrePuntoVenta(cierrePuntoVenta request)
	{
		return base.Channel.cierrePuntoVenta(request);
	}

	cierrePuntoVentaResponse ServicioFacturacionOperaciones.cierrePuntoVenta(cierrePuntoVenta request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_cierrePuntoVenta
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_cierrePuntoVenta(request);
	}

	public respuestaCierrePuntoVenta cierrePuntoVenta(solicitudCierrePuntoVenta SolicitudCierrePuntoVenta)
	{
		cierrePuntoVenta cierrePuntoVenta2 = new cierrePuntoVenta();
		cierrePuntoVenta2.SolicitudCierrePuntoVenta = SolicitudCierrePuntoVenta;
		return ((ServicioFacturacionOperaciones)this).cierrePuntoVenta(cierrePuntoVenta2).RespuestaCierrePuntoVenta;
	}

	[EditorBrowsable(EditorBrowsableState.Advanced)]
	public Task<cierrePuntoVentaResponse> FactElectOperaciones_ServicioFacturacionOperaciones_cierrePuntoVentaAsync(cierrePuntoVenta request)
	{
		return base.Channel.cierrePuntoVentaAsync(request);
	}

	Task<cierrePuntoVentaResponse> ServicioFacturacionOperaciones.cierrePuntoVentaAsync(cierrePuntoVenta request)
	{
		//ILSpy generated this explicit interface implementation from .override directive in FactElectOperaciones_ServicioFacturacionOperaciones_cierrePuntoVentaAsync
		return this.FactElectOperaciones_ServicioFacturacionOperaciones_cierrePuntoVentaAsync(request);
	}

	public Task<cierrePuntoVentaResponse> cierrePuntoVentaAsync(solicitudCierrePuntoVenta SolicitudCierrePuntoVenta)
	{
		cierrePuntoVenta cierrePuntoVenta2 = new cierrePuntoVenta();
		cierrePuntoVenta2.SolicitudCierrePuntoVenta = SolicitudCierrePuntoVenta;
		return ((ServicioFacturacionOperaciones)this).cierrePuntoVentaAsync(cierrePuntoVenta2);
	}
}
