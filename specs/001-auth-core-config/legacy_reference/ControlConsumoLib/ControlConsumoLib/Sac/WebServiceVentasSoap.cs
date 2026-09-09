using System.CodeDom.Compiler;
using System.Data;
using System.ServiceModel;
using System.Threading.Tasks;

namespace ControlConsumoLib.Sac;

[GeneratedCode("System.ServiceModel", "4.0.0.0")]
[ServiceContract(Namespace = "http://sac.net.com/", ConfigurationName = "Sac.WebServiceVentasSoap")]
public interface WebServiceVentasSoap
{
	[OperationContract(Action = "http://sac.net.com/VentaCabecera", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	string VentaCabecera(string Fecha, string Ord, string ID_CLIE, string NIT, string RAZONSOCIAL, int Sucursal, string CODCONTROL, decimal ImporteTotal, int TIPOFORMAPAGO);

	[OperationContract(Action = "http://sac.net.com/VentaCabecera", ReplyAction = "*")]
	Task<string> VentaCabeceraAsync(string Fecha, string Ord, string ID_CLIE, string NIT, string RAZONSOCIAL, int Sucursal, string CODCONTROL, decimal ImporteTotal, int TIPOFORMAPAGO);

	[OperationContract(Action = "http://sac.net.com/VentaDetalle", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	bool VentaDetalle(string ID_VEN, string IDPRD, decimal CANTIDAD, decimal DESCUENTO);

	[OperationContract(Action = "http://sac.net.com/VentaDetalle", ReplyAction = "*")]
	Task<bool> VentaDetalleAsync(string ID_VEN, string IDPRD, decimal CANTIDAD, decimal DESCUENTO);

	[OperationContract(Action = "http://sac.net.com/VentaFlete", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	bool VentaFlete(string ID_VEN, decimal Precio);

	[OperationContract(Action = "http://sac.net.com/VentaFlete", ReplyAction = "*")]
	Task<bool> VentaFleteAsync(string ID_VEN, decimal Precio);

	[OperationContract(Action = "http://sac.net.com/View_data", ReplyAction = "*")]
	[XmlSerializerFormat(SupportFaults = true)]
	DataTable View_data();

	[OperationContract(Action = "http://sac.net.com/View_data", ReplyAction = "*")]
	Task<DataTable> View_dataAsync();
}
