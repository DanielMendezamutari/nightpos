using System.CodeDom.Compiler;
using System.Data;
using System.Diagnostics;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.Threading.Tasks;

namespace ControlConsumoLib.Sac;

[DebuggerStepThrough]
[GeneratedCode("System.ServiceModel", "4.0.0.0")]
public class WebServiceVentasSoapClient : ClientBase<WebServiceVentasSoap>, WebServiceVentasSoap
{
	public WebServiceVentasSoapClient()
	{
	}

	public WebServiceVentasSoapClient(string endpointConfigurationName)
		: base(endpointConfigurationName)
	{
	}

	public WebServiceVentasSoapClient(string endpointConfigurationName, string remoteAddress)
		: base(endpointConfigurationName, remoteAddress)
	{
	}

	public WebServiceVentasSoapClient(string endpointConfigurationName, EndpointAddress remoteAddress)
		: base(endpointConfigurationName, remoteAddress)
	{
	}

	public WebServiceVentasSoapClient(Binding binding, EndpointAddress remoteAddress)
		: base(binding, remoteAddress)
	{
	}

	public string VentaCabecera(string Fecha, string Ord, string ID_CLIE, string NIT, string RAZONSOCIAL, int Sucursal, string CODCONTROL, decimal ImporteTotal, int TIPOFORMAPAGO)
	{
		return base.Channel.VentaCabecera(Fecha, Ord, ID_CLIE, NIT, RAZONSOCIAL, Sucursal, CODCONTROL, ImporteTotal, TIPOFORMAPAGO);
	}

	string WebServiceVentasSoap.VentaCabecera(string Fecha, string Ord, string ID_CLIE, string NIT, string RAZONSOCIAL, int Sucursal, string CODCONTROL, decimal ImporteTotal, int TIPOFORMAPAGO)
	{
		//ILSpy generated this explicit interface implementation from .override directive in VentaCabecera
		return this.VentaCabecera(Fecha, Ord, ID_CLIE, NIT, RAZONSOCIAL, Sucursal, CODCONTROL, ImporteTotal, TIPOFORMAPAGO);
	}

	public Task<string> VentaCabeceraAsync(string Fecha, string Ord, string ID_CLIE, string NIT, string RAZONSOCIAL, int Sucursal, string CODCONTROL, decimal ImporteTotal, int TIPOFORMAPAGO)
	{
		return base.Channel.VentaCabeceraAsync(Fecha, Ord, ID_CLIE, NIT, RAZONSOCIAL, Sucursal, CODCONTROL, ImporteTotal, TIPOFORMAPAGO);
	}

	Task<string> WebServiceVentasSoap.VentaCabeceraAsync(string Fecha, string Ord, string ID_CLIE, string NIT, string RAZONSOCIAL, int Sucursal, string CODCONTROL, decimal ImporteTotal, int TIPOFORMAPAGO)
	{
		//ILSpy generated this explicit interface implementation from .override directive in VentaCabeceraAsync
		return this.VentaCabeceraAsync(Fecha, Ord, ID_CLIE, NIT, RAZONSOCIAL, Sucursal, CODCONTROL, ImporteTotal, TIPOFORMAPAGO);
	}

	public bool VentaDetalle(string ID_VEN, string IDPRD, decimal CANTIDAD, decimal DESCUENTO)
	{
		return base.Channel.VentaDetalle(ID_VEN, IDPRD, CANTIDAD, DESCUENTO);
	}

	bool WebServiceVentasSoap.VentaDetalle(string ID_VEN, string IDPRD, decimal CANTIDAD, decimal DESCUENTO)
	{
		//ILSpy generated this explicit interface implementation from .override directive in VentaDetalle
		return this.VentaDetalle(ID_VEN, IDPRD, CANTIDAD, DESCUENTO);
	}

	public Task<bool> VentaDetalleAsync(string ID_VEN, string IDPRD, decimal CANTIDAD, decimal DESCUENTO)
	{
		return base.Channel.VentaDetalleAsync(ID_VEN, IDPRD, CANTIDAD, DESCUENTO);
	}

	Task<bool> WebServiceVentasSoap.VentaDetalleAsync(string ID_VEN, string IDPRD, decimal CANTIDAD, decimal DESCUENTO)
	{
		//ILSpy generated this explicit interface implementation from .override directive in VentaDetalleAsync
		return this.VentaDetalleAsync(ID_VEN, IDPRD, CANTIDAD, DESCUENTO);
	}

	public bool VentaFlete(string ID_VEN, decimal Precio)
	{
		return base.Channel.VentaFlete(ID_VEN, Precio);
	}

	bool WebServiceVentasSoap.VentaFlete(string ID_VEN, decimal Precio)
	{
		//ILSpy generated this explicit interface implementation from .override directive in VentaFlete
		return this.VentaFlete(ID_VEN, Precio);
	}

	public Task<bool> VentaFleteAsync(string ID_VEN, decimal Precio)
	{
		return base.Channel.VentaFleteAsync(ID_VEN, Precio);
	}

	Task<bool> WebServiceVentasSoap.VentaFleteAsync(string ID_VEN, decimal Precio)
	{
		//ILSpy generated this explicit interface implementation from .override directive in VentaFleteAsync
		return this.VentaFleteAsync(ID_VEN, Precio);
	}

	public DataTable View_data()
	{
		return base.Channel.View_data();
	}

	DataTable WebServiceVentasSoap.View_data()
	{
		//ILSpy generated this explicit interface implementation from .override directive in View_data
		return this.View_data();
	}

	public Task<DataTable> View_dataAsync()
	{
		return base.Channel.View_dataAsync();
	}

	Task<DataTable> WebServiceVentasSoap.View_dataAsync()
	{
		//ILSpy generated this explicit interface implementation from .override directive in View_dataAsync
		return this.View_dataAsync();
	}
}
