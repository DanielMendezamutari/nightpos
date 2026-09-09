using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFacturas
{
	private int FacturaID;

	private string NIT;

	private string Nombre;

	private DateTime FechaEmision;

	private int NroFactura;

	private string Codigo;

	private double Monto;

	private double MontoICE;

	private double MontoGiftCard;

	private string VisitaID;

	private string CodigoID;

	private bool Anulada;

	private bool FechaAnulacionCh;

	private DateTime FechaAnulacion;

	private string Observacion;

	private int personalID;

	private string AgruparPagoID;

	private string Correo;

	private string Telefono;

	private int TipoDocumentoID;

	private string leyID;

	private double descuento;

	private string Aux;

	private string PC;

	private string Complemento;

	private int FueraLineaID;

	private string EstadoSiat;

	private int CUFDid;

	private int DocumentoSector;

	public int _FacturaID
	{
		get
		{
			return FacturaID;
		}
		set
		{
			FacturaID = value;
		}
	}

	public int _AgruparPagoID
	{
		get
		{
			if (Operators.CompareString(AgruparPagoID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(AgruparPagoID);
		}
		set
		{
			if (value == 0)
			{
				AgruparPagoID = "null";
			}
			else
			{
				AgruparPagoID = Conversions.ToString(value);
			}
		}
	}

	public bool _Anulada
	{
		get
		{
			return Anulada;
		}
		set
		{
			Anulada = value;
		}
	}

	public string _NIT
	{
		get
		{
			return NIT;
		}
		set
		{
			NIT = value;
		}
	}

	public string _Nombre
	{
		get
		{
			return Nombre;
		}
		set
		{
			Nombre = value;
		}
	}

	public string _Correo
	{
		get
		{
			return Correo;
		}
		set
		{
			Correo = value;
		}
	}

	public string _Telefono
	{
		get
		{
			return Telefono;
		}
		set
		{
			Telefono = value;
		}
	}

	public int _CUFDid
	{
		get
		{
			return CUFDid;
		}
		set
		{
			CUFDid = value;
		}
	}

	public int _FueraLineaID
	{
		get
		{
			return FueraLineaID;
		}
		set
		{
			FueraLineaID = value;
		}
	}

	public int _TipoDocumentoID
	{
		get
		{
			return TipoDocumentoID;
		}
		set
		{
			TipoDocumentoID = value;
		}
	}

	public int _leyID
	{
		get
		{
			return Conversions.ToInteger(leyID);
		}
		set
		{
			leyID = Conversions.ToString(value);
		}
	}

	public DateTime _FechaEmision
	{
		get
		{
			return FechaEmision;
		}
		set
		{
			FechaEmision = value;
		}
	}

	public int _NroFactura
	{
		get
		{
			return NroFactura;
		}
		set
		{
			NroFactura = value;
		}
	}

	public string _Codigo
	{
		get
		{
			return Codigo;
		}
		set
		{
			Codigo = value;
		}
	}

	public string _Complemento
	{
		get
		{
			return Complemento;
		}
		set
		{
			Complemento = value;
		}
	}

	public double _MontoGiftCard
	{
		get
		{
			return MontoGiftCard;
		}
		set
		{
			MontoGiftCard = value;
		}
	}

	public double _Descuento
	{
		get
		{
			return descuento;
		}
		set
		{
			descuento = value;
		}
	}

	public double _MontoICE
	{
		get
		{
			return MontoICE;
		}
		set
		{
			MontoICE = value;
		}
	}

	public double _Monto
	{
		get
		{
			return Monto;
		}
		set
		{
			Monto = value;
		}
	}

	public int _VisitaID
	{
		get
		{
			if (Operators.CompareString(VisitaID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(VisitaID);
		}
		set
		{
			if (value == 0)
			{
				VisitaID = "null";
			}
			else
			{
				VisitaID = Conversions.ToString(value);
			}
		}
	}

	public int _CodigoID
	{
		get
		{
			if (Operators.CompareString(CodigoID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(CodigoID);
		}
		set
		{
			if (value == 0)
			{
				CodigoID = "null";
			}
			else
			{
				CodigoID = Conversions.ToString(value);
			}
		}
	}

	public DateTime _FechaAnulacion
	{
		get
		{
			return FechaAnulacion;
		}
		set
		{
			FechaAnulacion = value;
		}
	}

	public bool _FechaAnulacionCh
	{
		get
		{
			return FechaAnulacionCh;
		}
		set
		{
			FechaAnulacionCh = value;
		}
	}

	public int _personalId
	{
		get
		{
			return personalID;
		}
		set
		{
			personalID = value;
		}
	}

	public int _EstadoSiat
	{
		get
		{
			return Conversions.ToInteger(EstadoSiat);
		}
		set
		{
			EstadoSiat = Conversions.ToString(value);
		}
	}

	public int _DocumentoSector
	{
		get
		{
			return DocumentoSector;
		}
		set
		{
			DocumentoSector = value;
		}
	}

	public string _Observacion
	{
		get
		{
			return Observacion;
		}
		set
		{
			Observacion = value;
		}
	}

	public string _Aux
	{
		get
		{
			return Aux;
		}
		set
		{
			Aux = value;
		}
	}

	public string _PC
	{
		get
		{
			return PC;
		}
		set
		{
			PC = value;
		}
	}

	public clsFacturas()
	{
		NIT = "0";
		Nombre = "";
		FechaEmision = DateAndTime.Now;
		NroFactura = 0;
		Codigo = "";
		Monto = 0.0;
		MontoICE = 0.0;
		MontoGiftCard = 0.0;
		VisitaID = Conversions.ToString(0);
		CodigoID = Conversions.ToString(0);
		Anulada = false;
		FechaAnulacionCh = false;
		FechaAnulacion = DateAndTime.Now;
		Observacion = "";
		personalID = 0;
		Aux = "";
		AgruparPagoID = Conversions.ToString(0);
		PC = "";
		Complemento = "";
		Correo = "";
		Telefono = "";
		leyID = Conversions.ToString(0);
		TipoDocumentoID = 0;
		FueraLineaID = 0;
		EstadoSiat = Conversions.ToString(0);
		CUFDid = 0;
		DocumentoSector = 0;
	}

	public bool visitatieneFactura(ref bool factura2, ref int DocumentoSector)
	{
		string text = "";
		if (DocumentoSector > 0)
		{
			text = " and DocumentoSector=" + Conversions.ToString(DocumentoSector);
		}
		if (configuration.gTipoFacturacion == 1)
		{
			factura2 = false;
			DataTable dataTable = BD.ConsultaVer("FacturaID,Facturas.NroFactura,DocumentoSector", "Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "  Anulada=" + VariableGeneral.armarBolean(0) + " and VisitaID=" + VisitaID.ToString() + text);
			if (dataTable.Rows.Count > 0)
			{
				FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? ((object)0) : dataTable.Rows[0]["FacturaID"]);
				NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroFactura"])) ? ((object)0) : dataTable.Rows[0]["NroFactura"]);
				DocumentoSector = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DocumentoSector"])) ? ((object)0) : dataTable.Rows[0]["DocumentoSector"]);
				return true;
			}
			if (BD.ConsultaVer("FacturaID,Facturas2.NroFactura,DocumentoSector", "Facturas2 inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas2.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " Anulada=" + VariableGeneral.armarBolean(0) + " and VisitaID=" + VisitaID.ToString() + text).Rows.Count > 0)
			{
				factura2 = true;
				FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? ((object)0) : dataTable.Rows[0]["FacturaID"]);
				NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroFactura"])) ? ((object)0) : dataTable.Rows[0]["NroFactura"]);
				DocumentoSector = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DocumentoSector"])) ? ((object)0) : dataTable.Rows[0]["DocumentoSector"]);
				return true;
			}
			return false;
		}
		factura2 = false;
		DataTable dataTable2 = BD.ConsultaVer("FacturaID,Facturas.NroFactura,DocumentoSector", "Facturas inner join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "  Anulada=" + VariableGeneral.armarBolean(0) + " and VisitaID=" + VisitaID.ToString() + text);
		if (dataTable2.Rows.Count > 0)
		{
			FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["FacturaID"])) ? ((object)0) : dataTable2.Rows[0]["FacturaID"]);
			NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["NroFactura"])) ? ((object)0) : dataTable2.Rows[0]["NroFactura"]);
			DocumentoSector = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["DocumentoSector"])) ? ((object)0) : dataTable2.Rows[0]["DocumentoSector"]);
			return true;
		}
		DataTable dataTable3 = BD.ConsultaVer("FacturaID,Facturas2.NroFactura,DocumentoSector", "Facturas2 inner join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas2.CufdID and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " Anulada=" + VariableGeneral.armarBolean(0) + " and VisitaID=" + VisitaID.ToString() + text);
		if (dataTable3.Rows.Count > 0)
		{
			factura2 = true;
			FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0]["FacturaID"])) ? ((object)0) : dataTable3.Rows[0]["FacturaID"]);
			NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0]["NroFactura"])) ? ((object)0) : dataTable3.Rows[0]["NroFactura"]);
			DocumentoSector = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0]["DocumentoSector"])) ? ((object)0) : dataTable3.Rows[0]["DocumentoSector"]);
			return true;
		}
		return false;
	}

	public void llenarclase(bool fact2)
	{
		DataTable dataTable = BD.ConsultaVer("*", "Facturas" + (fact2 ? "2" : ""), " FacturaID=" + FacturaID, "FacturaID desc");
		if (dataTable.Rows.Count > 0)
		{
			FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? ((object)0) : dataTable.Rows[0]["FacturaID"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? "0" : dataTable.Rows[0]["NIT"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			FechaEmision = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaEmision"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaEmision"]);
			NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroFactura"])) ? ((object)0) : dataTable.Rows[0]["NroFactura"]);
			Codigo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Codigo"])) ? "" : dataTable.Rows[0]["Codigo"]);
			Monto = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Monto"])) ? ((object)0) : dataTable.Rows[0]["Monto"]);
			VisitaID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["VisitaID"])) ? ((object)0) : dataTable.Rows[0]["VisitaID"]);
			CodigoID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoID"])) ? ((object)0) : dataTable.Rows[0]["CodigoID"]);
			Anulada = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Anulada"])) ? ((object)false) : dataTable.Rows[0]["Anulada"]);
			Correo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Correo"])) ? "" : dataTable.Rows[0]["Correo"]);
			FechaAnulacionCh = !Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaAnulacion"]));
			FechaAnulacion = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaAnulacion"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaAnulacion"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			personalID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["personalID"])) ? ((object)0) : dataTable.Rows[0]["personalID"]);
			MontoICE = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ICE"])) ? ((object)0) : dataTable.Rows[0]["ICE"]);
			MontoGiftCard = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MontoGiftCard"])) ? ((object)0) : dataTable.Rows[0]["MontoGiftCard"]);
			AgruparPagoID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["AgruparPagoID"])) ? ((object)0) : dataTable.Rows[0]["AgruparPagoID"]);
			Aux = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Aux"])) ? ((object)0) : dataTable.Rows[0]["Aux"]);
			PC = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PC"])) ? "" : dataTable.Rows[0]["PC"]);
			Complemento = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Complemento"])) ? "" : dataTable.Rows[0]["Complemento"]);
			TipoDocumentoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoDocumentoID"])) ? ((object)0) : dataTable.Rows[0]["TipoDocumentoID"]);
			leyID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["leyID"])) ? ((object)0) : dataTable.Rows[0]["leyID"]);
			Telefono = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Telefono"])) ? "" : dataTable.Rows[0]["Telefono"]);
			FueraLineaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FueraLineaID"])) ? ((object)0) : dataTable.Rows[0]["FueraLineaID"]);
			EstadoSiat = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EstadoSiat"])) ? ((object)0) : dataTable.Rows[0]["EstadoSiat"]);
			CUFDid = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CUFDid"])) ? ((object)0) : dataTable.Rows[0]["CUFDid"]);
			descuento = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["descuento"])) ? ((object)0) : dataTable.Rows[0]["descuento"]);
			DocumentoSector = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DocumentoSector"])) ? ((object)0) : dataTable.Rows[0]["DocumentoSector"]);
		}
	}

	public DataTable CargarNombreNitxNombreClientes(string Nombre)
	{
		if (Operators.CompareString(Nombre, "", TextCompare: false) == 0)
		{
			return BD.ConsultaVer("distinct *", "(select distinct Nit, Nombre  from Facturas where Anulada = " + VariableGeneral.armarBolean(0) + " and Nombre like '%" + Nombre + "%' UNION select distinct CI , Nombre + ' ' + Apellidos from Clientes where   Nombre + ' ' + Apellidos like  '%" + Nombre + "%' ) as tab1");
		}
		return BD.ConsultaVer("distinct *", "(select distinct Nit, Nombre  from Facturas where Anulada =  " + VariableGeneral.armarBolean(0) + " and Nombre like '%" + Nombre + "%' UNION select distinct CI , Nombre + ' ' + Apellidos from Clientes where   Nombre + ' ' + Apellidos like  '%" + Nombre + "%') as tab1");
	}

	public int CuantasFacturaEnContingenciaHay(bool todas)
	{
		DataTable dataTable = ((!todas) ? BD.ConsultaVer("count(*) as Cant", "Facturas", "Anulada=" + VariableGeneral.armarBolean(0) + " and CodigoID is null and (estadoSiat is null or EstadoSiat >=3) and fechaEmision<=" + VariableGeneral.ArmarFecha(DateAndTime.Today.AddDays(-1.0))) : BD.ConsultaVer("count(*) as Cant", "Facturas", "Anulada=" + VariableGeneral.armarBolean(0) + " and CodigoID is null and (estadoSiat is null or EstadoSiat >=3)"));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Cant"])) ? ((object)0) : dataTable.Rows[0]["Cant"]);
		}
		return 0;
	}

	public void getNombrePorNIT()
	{
		DataTable dataTable = BD.ConsultaVer(" top 1 facturaID,Nombre", "Facturas", "NIT='" + NIT.ToString() + "' and Anulada=" + VariableGeneral.armarBolean(0), "facturaID desc");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
		}
		else
		{
			Nombre = "";
		}
	}

	public void getUltimoNombreEmailNITPorCelular()
	{
		DataTable dataTable = BD.ConsultaVer("top 1 facturaID, Nombre, Correo, NIT,TipoDocumentoID", "Facturas", "Telefono='" + Telefono + "' and Anulada=" + VariableGeneral.armarBolean(0), "facturaID desc");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			Correo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Correo"])) ? "" : dataTable.Rows[0]["Correo"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? "" : dataTable.Rows[0]["NIT"]);
			TipoDocumentoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoDocumentoID"])) ? ((object)0) : dataTable.Rows[0]["TipoDocumentoID"]);
		}
		else
		{
			Nombre = "";
			Correo = "";
			NIT = "";
			TipoDocumentoID = 0;
		}
	}

	public void getUltimoNombreEmailTelefonoPorNIT()
	{
		DataTable dataTable = BD.ConsultaVer(" top 1 facturaID, Nombre, Correo, Telefono,TipoDocumentoID", "Facturas", "NIT='" + NIT.ToString() + "' and Anulada=" + VariableGeneral.armarBolean(0), "facturaID desc");
		if (dataTable.Rows.Count > 0)
		{
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			Correo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Correo"])) ? "" : dataTable.Rows[0]["Correo"]);
			Telefono = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Telefono"])) ? "" : dataTable.Rows[0]["Telefono"]);
			TipoDocumentoID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["TipoDocumentoID"])) ? ((object)0) : dataTable.Rows[0]["TipoDocumentoID"]);
		}
		else
		{
			Nombre = "";
			Correo = "";
			Telefono = "";
			TipoDocumentoID = 0;
		}
	}

	public void getCumpleañosPorNIT()
	{
		DataTable dataTable = BD.ConsultaVer(" top 1 facturaID,Aux", "Facturas", "NIT='" + NIT.ToString() + "' and Anulada=" + VariableGeneral.armarBolean(0), "facturaID desc");
		if (dataTable.Rows.Count > 0)
		{
			Aux = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Aux"])) ? "" : dataTable.Rows[0]["Aux"]);
		}
		else
		{
			Aux = "";
		}
	}

	public void getNitPorNombre()
	{
		DataTable dataTable = BD.ConsultaVer(" top 1 facturaID,NIT", "Facturas", "Nombre='" + Nombre.ToString() + "' and Anulada=" + VariableGeneral.armarBolean(0), "facturaID desc");
		if (dataTable.Rows.Count > 0)
		{
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? "" : dataTable.Rows[0]["NIT"]);
		}
		else
		{
			NIT = VariableGeneral._sinNit2;
		}
	}

	public bool HayFacturasDespuesDeFecha(DateTime fecha)
	{
		if (configuration.gTipoFacturacion == 1)
		{
			if (Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "FechaEmision>" + VariableGeneral.ArmarFecha(fecha.AddMinutes(2.0)) + " and Anulada=" + VariableGeneral.armarBolean(0)).Rows[0][0], 0, TextCompare: false))
			{
				return true;
			}
			return false;
		}
		if (Operators.ConditionalCompareObjectGreater(BD.ConsultaVer("count(*)", "Facturas inner join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "FechaEmision>" + VariableGeneral.ArmarFecha(fecha.AddMinutes(2.0)) + " and Anulada=" + VariableGeneral.armarBolean(0)).Rows[0][0], 0, TextCompare: false))
		{
			return true;
		}
		return false;
	}

	public void buscarPorVisitaIDConAnulada(ref bool fact2)
	{
		if (configuration.gTipoFacturacion == 1)
		{
			fact2 = false;
			DataTable dataTable = BD.ConsultaVer("FacturaID", "Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "Facturas.VisitaID=" + _VisitaID, "FacturaID desc");
			if (dataTable.Rows.Count > 0)
			{
				FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? ((object)0) : dataTable.Rows[0]["FacturaID"]);
				return;
			}
			dataTable = BD.ConsultaVer("FacturaID", "Facturas2 inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas2.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "Facturas2.VisitaID=" + _VisitaID, "FacturaID desc");
			if (dataTable.Rows.Count > 0)
			{
				fact2 = true;
				FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? ((object)0) : dataTable.Rows[0]["FacturaID"]);
			}
			else
			{
				FacturaID = 0;
			}
			return;
		}
		fact2 = false;
		DataTable dataTable2 = BD.ConsultaVer("FacturaID", "Facturas left join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "Facturas.VisitaID=" + _VisitaID, "FacturaID desc");
		if (dataTable2.Rows.Count > 0)
		{
			FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["FacturaID"])) ? ((object)0) : dataTable2.Rows[0]["FacturaID"]);
			return;
		}
		dataTable2 = BD.ConsultaVer("FacturaID", "Facturas2 left join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas2.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "Facturas2.VisitaID=" + _VisitaID, "FacturaID desc");
		if (dataTable2.Rows.Count > 0)
		{
			fact2 = true;
			FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["FacturaID"])) ? ((object)0) : dataTable2.Rows[0]["FacturaID"]);
		}
		else
		{
			FacturaID = 0;
		}
	}

	public void buscarPorVisitaID(ref bool fact2)
	{
		if (configuration.gTipoFacturacion == 1)
		{
			fact2 = false;
			DataTable dataTable = BD.ConsultaVer("FacturaID", "Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "Facturas.VisitaID=" + _VisitaID + " and anulada=" + VariableGeneral.armarBolean(0), "FacturaID desc");
			if (dataTable.Rows.Count > 0)
			{
				FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? ((object)0) : dataTable.Rows[0]["FacturaID"]);
				return;
			}
			dataTable = BD.ConsultaVer("FacturaID", "Facturas2 inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas2.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "Facturas2.VisitaID=" + _VisitaID + " and anulada=" + VariableGeneral.armarBolean(0), "FacturaID desc");
			if (dataTable.Rows.Count > 0)
			{
				fact2 = true;
				FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? ((object)0) : dataTable.Rows[0]["FacturaID"]);
			}
			else
			{
				FacturaID = 0;
			}
			return;
		}
		fact2 = false;
		DataTable dataTable2 = BD.ConsultaVer("FacturaID", "Facturas left join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "Facturas.VisitaID=" + _VisitaID + " and anulada=" + VariableGeneral.armarBolean(0), "FacturaID desc");
		if (dataTable2.Rows.Count > 0)
		{
			FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["FacturaID"])) ? ((object)0) : dataTable2.Rows[0]["FacturaID"]);
			return;
		}
		dataTable2 = BD.ConsultaVer("FacturaID", "Facturas2 left join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas2.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "Facturas2.VisitaID=" + _VisitaID + " and anulada=" + VariableGeneral.armarBolean(0), "FacturaID desc");
		if (dataTable2.Rows.Count > 0)
		{
			fact2 = true;
			FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["FacturaID"])) ? ((object)0) : dataTable2.Rows[0]["FacturaID"]);
		}
		else
		{
			FacturaID = 0;
		}
	}

	public void buscarPorNro()
	{
		if (configuration.gTipoFacturacion == 1)
		{
			DataTable dataTable = BD.ConsultaVer("FacturaID", "Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " )", "Facturas.NroFactura=" + NroFactura, "FacturaID desc");
			if (dataTable.Rows.Count > 0)
			{
				FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? ((object)0) : dataTable.Rows[0]["FacturaID"]);
			}
			else
			{
				FacturaID = 0;
			}
		}
		else
		{
			DataTable dataTable2 = BD.ConsultaVer("FacturaID", "Facturas left join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + " )", "Facturas.NroFactura=" + NroFactura, "FacturaID desc");
			if (dataTable2.Rows.Count > 0)
			{
				FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["FacturaID"])) ? ((object)0) : dataTable2.Rows[0]["FacturaID"]);
			}
			else
			{
				FacturaID = 0;
			}
		}
	}

	public DataTable devolverReporteFacturaFormatoImpuestos2022(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool fact2, bool consolidado)
	{
		string text = "";
		string text2 = "";
		string text3 = "";
		if (!chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = "1=2";
		}
		if (!chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " Facturas.Anulada= " + VariableGeneral.armarBolean(1);
		}
		if (!chbNombre & chbSnimbre & !chbAnulado)
		{
			text = "( Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + " )";
		}
		if (!chbNombre & chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit='0' or (Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = " (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit<>'0' or (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & chbSnimbre & !chbAnulado)
		{
			text = " ( Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & chbSnimbre & chbAnulado)
		{
			text = " 1=1 ";
		}
		BD.ConsultaModificar("Facturas", "Facturas.Descuento=0,flagSync=NULL", "Facturas.Descuento is null");
		string text4 = "";
		if (fact2)
		{
			text4 = "2";
		}
		string text5 = "";
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.ElMovima)
		{
			text5 = ",'' as TipoPago";
			text3 = " ,tab1.TipoPago ";
			text2 = " left join (select VisitaID, max(cuentas.nombre) as TipoPago from DetalleCuenta left join Pagos on DetalleCuenta.ID = Pagos.DetalleCuentaID left join Cuentas on Pagos.CuentaID = Cuentas.CuentaID group by VisitaID) as tab1 on Facturas.VisitaID = tab1.VisitaID ";
		}
		if (((configuration.gStyleBoliches1 == configuration.styleBolichesId.LogicTruck) & (VariableGeneral.gConfiguracionID == 4)) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.TorrezSoliz) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.MGTRAILER))
		{
			return null;
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			string query;
			if (configuration.gTipoFacturacion == 1)
			{
				query = "select 0 as Num, 2 as Especificacion, " + ((configuration.gMODO_ACCESS == 1) ? "DateValue(Facturas.FechaEmision)" : "CAST(Facturas.FechaEmision AS DATE)") + " AS Fecha_Factura,Facturas.NroFactura,CodigosFacturas.Autorizacion as No_Autorizacion,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.NIT) AS NIT, '0' as Complemento, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'ANULADA',Facturas.Nombre) as Razon_Social,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Importe_Total_Venta, 0 as Importe_Ice,0 as IMPORTE_IEHD, 0 as IMPORTE_IPJ, 0 as TASAS, 0 as OTROS_NO_SUJETOS_IVA,0 as Importe_Excento,0 as Ventas_Gravadas, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Subtotal,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Descuento)  as Descuentos, 0 as GIFTCARD, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto-MontoGiftCard) as Importe_Base,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0, round(((Facturas.Monto-MontoGiftCard)*0.13),2)) as Debito_Fiscal,iif(Anulada=" + VariableGeneral.armarBolean(1) + " ,'A','V') as Estado,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.Codigo)  as Codigo_Control, 0 as TipoVenta from Facturas" + text4 + " as Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ") where  " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " order by FechaEmision, Facturas.NroFactura";
			}
			else
			{
				query = "select 0 as Num, 2 as Especificacion, " + ((configuration.gMODO_ACCESS == 1) ? "(Facturas.FechaEmision)" : "(Facturas.FechaEmision )") + " AS Fecha_Factura,Facturas.NroFactura, 0 as No_Autorizacion,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.NIT) AS NIT, Complemento, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'ANULADA',Facturas.Nombre) as Razon_Social,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Importe_Total_Venta, 0 as Importe_Ice,0 as IMPORTE_IEHD, 0 as IMPORTE_IPJ, 0 as TASAS, 0 as OTROS_NO_SUJETOS_IVA,0 as Importe_Excento,0 as Ventas_Gravadas, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Subtotal,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Descuento)  as Descuentos, montoGiftCard as GIFTCARD, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto-MontoGiftCard) as Importe_Base,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0, round(((Facturas.Monto-MontoGiftCard)*0.13),2)) as Debito_Fiscal,iif(Anulada=" + VariableGeneral.armarBolean(1) + " ,'A','V') as Estado,  Facturas.Codigo  as Codigo_Control, 0 as TipoVenta from  Facturas" + text4 + " as Facturas  left join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ") where  " + text + "  and Facturas.CodigoID is null and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + "  and " + VariableGeneral.ArmarFecha(hasta);
				query += " UNION ";
				query = query + "select 0 as Num, 2 as Especificacion, " + ((configuration.gMODO_ACCESS == 1) ? "DateValue(Facturas.FechaEmision)" : "CAST(Facturas.FechaEmision AS DATE)") + " AS Fecha_Factura,Facturas.NroFactura,CodigosFacturas.Autorizacion as No_Autorizacion,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.NIT) AS NIT, '0' as Complemento, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'ANULADA',Facturas.Nombre) as Razon_Social,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Importe_Total_Venta, 0 as Importe_Ice,0 as IMPORTE_IEHD, 0 as IMPORTE_IPJ, 0 as TASAS, 0 as OTROS_NO_SUJETOS_IVA,0 as Importe_Excento,0 as Ventas_Gravadas, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Subtotal,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Descuento)  as Descuentos, 0 as GIFTCARD, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto-MontoGiftCard) as Importe_Base,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0, round(((Facturas.Monto-MontoGiftCard)*0.13),2)) as Debito_Fiscal,iif(Anulada=" + VariableGeneral.armarBolean(1) + " ,'A','V') as Estado,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.Codigo)  as Codigo_Control, 0 as TipoVenta from Facturas" + text4 + " as Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ") where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " order by Fecha_Factura, Facturas.NroFactura";
			}
			return BD.ConsultaVer(query);
		}
		string query2;
		if (configuration.gTipoFacturacion == 1)
		{
			query2 = "select 0 as Num,2 as Especificacion, CAST(Facturas.FechaEmision AS DATE)  AS Fecha_Factura,Facturas.NroFactura,CodigosFacturas.Autorizacion as No_Autorizacion,   case when Anulada=" + VariableGeneral.armarBolean(1) + " then '0' else Facturas.NIT end AS NIT, 0 as Complemento, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'ANULADA' else  Facturas.Nombre end as Razon_Social, case when Anulada=" + VariableGeneral.armarBolean(1) + "then 0 else Facturas.Monto+Facturas.Descuento+isnull (Facturas.ICE,0) end  as Importe_Total_Venta, isnull (Facturas.ICE,0) as Importe_Ice,0 as IMPORTE_IEHD, 0 as IMPORTE_IPJ, 0 as TASAS, 0 as OTROS_NO_SUJETOS_IVA,0 as Importe_Excento,0 as Ventas_Gravadas, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Monto+Facturas.Descuento end  as Subtotal,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Descuento end  as Descuentos, 0 as GIFTCARD, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0  else Facturas.Monto end as Importe_Base,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else round((((Facturas.Monto)-MontoGiftCard)*0.13),2) end as Debito_Fiscal, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'A' else 'V' end as Estado,   case when Anulada=" + VariableGeneral.armarBolean(1) + " then '0'  else Facturas.Codigo end  as Codigo_Control, 0 as TipoVenta from Facturas" + text4 + " as Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ") where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + "order by Facturas.FechaEmision";
		}
		else
		{
			query2 = "select 'Local' as Suc, 0 as Num,2 as Especificacion, Facturas.FechaEmision AS Fecha_Factura,Facturas.NroFactura,'' as No_Autorizacion,   case when Anulada=" + VariableGeneral.armarBolean(1) + " then '0' else Facturas.NIT end AS NIT,   Complemento, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'ANULADA' else  Facturas.Nombre end as Razon_Social, case when Anulada=" + VariableGeneral.armarBolean(1) + "then 0 else Facturas.Monto+Facturas.Descuento+isnull (Facturas.ICE,0) end  as Importe_Total_Venta,isnull (Facturas.ICE,0) as Importe_Ice,0 as IMPORTE_IEHD, 0 as IMPORTE_IPJ, 0 as TASAS, 0 as OTROS_NO_SUJETOS_IVA,0 as Importe_Excento,0 as Ventas_Gravadas, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Monto+Facturas.Descuento end  as Subtotal,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Descuento end  as Descuentos, montoGiftCard as GIFTCARD, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0  else Facturas.Monto end as Importe_Base,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else round((((Facturas.Monto)-MontoGiftCard)*0.13),2) end as Debito_Fiscal, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'A' else 'V' end as Estado,  Facturas.Codigo as Codigo_Control, 0 as TipoVenta " + text3 + " from Facturas" + text4 + " as Facturas inner join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ") " + text2 + " where " + text + " and Facturas.CodigoID is null and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " ";
			query2 += " UNION ";
			query2 = query2 + "select 'Local' as Suc, 0 as Num,2 as Especificacion, CAST(Facturas.FechaEmision AS DATE) AS Fecha_Factura,Facturas.NroFactura,CodigosFacturas.Autorizacion as No_Autorizacion,   case when Anulada=" + VariableGeneral.armarBolean(1) + " then '0' else Facturas.NIT end AS NIT, '0' as Complemento, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'ANULADA' else  Facturas.Nombre end as Razon_Social, case when Anulada=" + VariableGeneral.armarBolean(1) + "then 0 else Facturas.Monto+Facturas.Descuento+isnull (Facturas.ICE,0) end  as Importe_Total_Venta, isnull (Facturas.ICE,0) as Importe_Ice,0 as IMPORTE_IEHD, 0 as IMPORTE_IPJ, 0 as TASAS, 0 as OTROS_NO_SUJETOS_IVA,0 as Importe_Excento,0 as Ventas_Gravadas, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Monto+Facturas.Descuento end  as Subtotal,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Descuento end  as Descuentos, 0 as GIFTCARD, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0  else Facturas.Monto end as Importe_Base,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else round((((Facturas.Monto)-MontoGiftCard)*0.13),2) end as Debito_Fiscal, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'A' else 'V' end as Estado,   case when Anulada=" + VariableGeneral.armarBolean(1) + " then '0'  else Facturas.Codigo end  as Codigo_Control, 0 as TipoVenta" + text5 + " from Facturas" + text4 + " as Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ") where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + "order by Fecha_Factura";
		}
		return BD.ConsultaVerParaReporte(query2, consolidado);
	}

	public DataTable devolverReporteFacturaFormatoImpuestos2022fsv(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool fact2, bool consolidado)
	{
		string text = "";
		if (!chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = "1=2";
		}
		if (!chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " Facturas.Anulada= " + VariableGeneral.armarBolean(1);
		}
		if (!chbNombre & chbSnimbre & !chbAnulado)
		{
			text = "( Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + " )";
		}
		if (!chbNombre & chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit='0' or (Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = " (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit<>'0' or (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & chbSnimbre & !chbAnulado)
		{
			text = " ( Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & chbSnimbre & chbAnulado)
		{
			text = " 1=1 ";
		}
		BD.ConsultaModificar("Facturas", "Facturas.Descuento=0,flagSync=NULL", "Facturas.Descuento is null");
		string text2 = "";
		if (fact2)
		{
			text2 = "2";
		}
		if (((configuration.gStyleBoliches1 == configuration.styleBolichesId.LogicTruck) & (VariableGeneral.gConfiguracionID == 4)) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.TorrezSoliz) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.MGTRAILER))
		{
			return null;
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("0 as Num, 2 as Especificacion, " + ((configuration.gMODO_ACCESS == 1) ? "DateValue(Facturas.FechaEmision)" : "CAST(Facturas.FechaEmision AS DATE)") + " AS Fecha_Factura,Facturas.NroFactura,CodigosFacturas.Autorizacion as No_Autorizacion,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.NIT) AS NIT, 0 as Complemento, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'ANULADA',Facturas.Nombre) as Razon_Social,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Importe_Total_Venta, 0 as Importe_Ice,0 as IMPORTE_IEHD, 0 as IMPORTE_IPJ, 0 as TASAS, 0 as OTROS_NO_SUJETOS_IVA,0 as Importe_Excento,0 as Ventas_Gravadas, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Subtotal,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Descuento)  as Descuentos, 0 as GIFTCARD, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto-MontoGiftCard) as Importe_Base,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0, round(((Facturas.Monto-MontoGiftCard)*0.13),2)) as Debito_Fiscal,iif(Anulada=" + VariableGeneral.armarBolean(1) + " ,'A','V') as Estado,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.Codigo)  as Codigo_Control, 0 as TipoVenta", "Facturas" + text2 + " as Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision, Facturas.NroFactura");
		}
		return BD.ConsultaVerParaReporte("select 0 as Num,2 as Especificacion, CAST(Facturas.FechaEmision AS DATE) AS Fecha_Factura,Facturas.NroFactura,CodigosFacturas.Autorizacion as No_Autorizacion,   case when Anulada=" + VariableGeneral.armarBolean(1) + " then '0' else Facturas.NIT end AS NIT, 0 as Complemento, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'ANULADA' else  Facturas.Nombre end as Razon_Social, case when Anulada=" + VariableGeneral.armarBolean(1) + "then 0 else Facturas.Monto+Facturas.Descuento+isnull (Facturas.ICE,0) end  as Importe_Total_Venta, isnull (Facturas.ICE,0) as Importe_Ice,0 as IMPORTE_IEHD, 0 as IMPORTE_IPJ, 0 as TASAS, 0 as OTROS_NO_SUJETOS_IVA,0 as Importe_Excento,0 as Ventas_Gravadas, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Monto+Facturas.Descuento end  as Subtotal,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Descuento end  as Descuentos, 0 as GIFTCARD, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0  else Facturas.Monto end as Importe_Base,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else round((((Facturas.Monto)-MontoGiftCard)*0.13),2) end as Debito_Fiscal, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'A' else 'V' end as Estado,   case when Anulada=" + VariableGeneral.armarBolean(1) + " then '0'  else Facturas.Codigo end  as Codigo_Control, 0 as TipoVenta from Facturas" + text2 + " as Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ") where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + "order by Facturas.FechaEmision", consolidado);
	}

	public DataTable devolverReporteFacturaFormatoImpuestosOld(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool fact2, bool consolidado)
	{
		string text = "";
		if (!chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = "1=2";
		}
		if (!chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " Facturas.Anulada= " + VariableGeneral.armarBolean(1);
		}
		if (!chbNombre & chbSnimbre & !chbAnulado)
		{
			text = "( Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + " )";
		}
		if (!chbNombre & chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit='0' or (Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = " (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit<>'0' or (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & chbSnimbre & !chbAnulado)
		{
			text = " ( Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & chbSnimbre & chbAnulado)
		{
			text = " 1=1 ";
		}
		BD.ConsultaModificar("Facturas", "Facturas.Descuento=0,flagSync=NULL", "Facturas.Descuento is null");
		string text2 = "";
		if (fact2)
		{
			text2 = "2";
		}
		if (((configuration.gStyleBoliches1 == configuration.styleBolichesId.LogicTruck) & (VariableGeneral.gConfiguracionID == 4)) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.TorrezSoliz) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.MGTRAILER))
		{
			return BD.ConsultaVer("3 as Especificacion,0 as Num, " + ((configuration.gMODO_ACCESS == 1) ? "DateValue(Facturas.FechaEmision)" : "CAST(Facturas.FechaEmision AS DATE)") + " AS Fecha_Factura,Facturas.NroFactura,CodigosFacturas.Autorizacion as No_Autorizacion, iif(Anulada=" + VariableGeneral.armarBolean(1) + " ,'A','V') as Estado,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.NIT) AS NIT,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'ANULADA',Facturas.Nombre) as Razon_Social,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Importe_Total_Venta, 0 as Importe_Ice,0 as Importe_Excento,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0, round((Facturas.Monto),2)) as Ventas_Gravadas, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto) as Subtotal,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Descuento)  as Descuentos, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto) as Importe_Base, 0 as Debito_Fiscal,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.Codigo) as Codigo_Control", "Facturas" + text2 + " as Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision");
		}
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("3 as Especificacion,0 as Num, " + ((configuration.gMODO_ACCESS == 1) ? "DateValue(Facturas.FechaEmision)" : "CAST(Facturas.FechaEmision AS DATE)") + " AS Fecha_Factura,Facturas.NroFactura,CodigosFacturas.Autorizacion as No_Autorizacion, iif(Anulada=" + VariableGeneral.armarBolean(1) + " ,'A','V') as Estado,  iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.NIT) AS NIT,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'ANULADA',Facturas.Nombre) as Razon_Social,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Importe_Total_Venta, 0 as Importe_Ice,0 as Importe_Excento,0 as Ventas_Gravadas, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto+Facturas.Descuento) as Subtotal,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Descuento)  as Descuentos, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0,Facturas.Monto-MontoGiftCard) as Importe_Base,iif(Anulada=" + VariableGeneral.armarBolean(1) + ",0, round(((Facturas.Monto-MontoGiftCard)*0.13),2)) as Debito_Fiscal, iif(Anulada=" + VariableGeneral.armarBolean(1) + ",'0',Facturas.Codigo)  as Codigo_Control", "Facturas" + text2 + " as Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision, Facturas.NroFactura");
		}
		return BD.ConsultaVerParaReporte("select 3 as Especificacion,0 as Num, CAST(Facturas.FechaEmision AS DATE) AS Fecha_Factura,Facturas.NroFactura,CodigosFacturas.Autorizacion as No_Autorizacion, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'A' else 'V' end as Estado,  case when Anulada=" + VariableGeneral.armarBolean(1) + " then '0' else Facturas.NIT end AS NIT, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 'ANULADA' else  Facturas.Nombre end as Razon_Social, case when Anulada=" + VariableGeneral.armarBolean(1) + "then 0 else Facturas.Monto+Facturas.Descuento+Facturas.ICE end  as Importe_Total_Venta, Facturas.ICE as Importe_Ice,0 as Importe_Excento,0 as Ventas_Gravadas, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Monto+Facturas.Descuento end  as Subtotal,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else Facturas.Descuento end  as Descuentos, case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0  else Facturas.Monto end as Importe_Base,case when Anulada=" + VariableGeneral.armarBolean(1) + " then 0 else round(((Facturas.Monto)-MontoGiftCard*0.13),2) end as Debito_Fiscal,   case when Anulada=" + VariableGeneral.armarBolean(1) + " then '0'  else Facturas.Codigo end  as Codigo_Control from Facturas" + text2 + " as Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ") where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + "order by Facturas.FechaEmision", consolidado);
	}

	public DataTable devolverReporteFactura(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool pagoCon, bool consolidado, bool noEnSIAT)
	{
		string text = "";
		string text2 = "";
		if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.MangaRosa) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Bernadette) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Goss))
		{
			text2 = " CONVERT(char(10),Visitas.DiaKey, 103) as DiaKey,";
		}
		if (!chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = "1=2";
		}
		if (!chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " Facturas.Anulada= " + VariableGeneral.armarBolean(1);
		}
		if (!chbNombre & chbSnimbre & !chbAnulado)
		{
			text = ((configuration.gTipoFacturacion != 1) ? ("(Facturas.Nombre like '" + VariableGeneral._SinNombre2 + "' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + " )") : ("(Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + " )"));
		}
		if (!chbNombre & chbSnimbre & chbAnulado)
		{
			text = ((configuration.gTipoFacturacion != 1) ? (" (Facturas.Nombre like '" + VariableGeneral._SinNombre2 + "' or (Facturas.Nombre like '" + VariableGeneral._SinNombre2 + "' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))") : (" (Facturas.Nit='0' or (Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))"));
		}
		if (chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = ((configuration.gTipoFacturacion != 1) ? (" (not Facturas.Nombre like '" + VariableGeneral._SinNombre2 + "' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")") : (" (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")"));
		}
		if (chbNombre & !chbSnimbre & chbAnulado)
		{
			text = ((configuration.gTipoFacturacion != 1) ? (" (not Facturas.Nombre like '" + VariableGeneral._SinNombre2 + "' or (not Facturas.Nombre like '" + VariableGeneral._SinNombre2 + "' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))") : (" (Facturas.Nit<>'0' or (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))"));
		}
		if (chbNombre & chbSnimbre & !chbAnulado)
		{
			text = " ( Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & chbSnimbre & chbAnulado)
		{
			text = " 1=1 ";
		}
		if (noEnSIAT)
		{
			text += " and (EstadoSiat >=3 or EstadoSiat is null)";
		}
		if (configuration.gTipoFacturacion == 1)
		{
			if (consolidado)
			{
				string text3 = "";
				text3 = ((!pagoCon) ? ("select 'Local' as Suc, VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre,Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Observacion, Meseros.Nombre as 'Anulada Por' , AgruparPagoID, DocumentoSector, leyID  from (Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " order by Facturas.FechaEmision") : ("select 'Local' as Suc, VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre,Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Observacion, Meseros.Nombre as 'Anulada Por', PC, AgruparPagoID, DocumentoSector, leyID, (SELECT max(Cuentas.Nombre) FROM  (DetalleCuenta inner join Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN  Cuentas ON Pagos.CuentaID = Cuentas.CuentaID where DetalleCuenta.visitaID=Facturas.VisitaID) as Pago_Con from (Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " order by Facturas.FechaEmision"));
				return BD.ConsultaVerParaReporte(text3, consolidado);
			}
			if (pagoCon)
			{
				return BD.ConsultaVer("VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre,Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Facturas.Observacion, Meseros.Nombre as 'Anulada Por', PC, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, AgruparPagoID, DocumentoSector, leyID, (SELECT max(Cuentas.Nombre) FROM  (DetalleCuenta inner join Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN  Cuentas ON Pagos.CuentaID = Cuentas.CuentaID where DetalleCuenta.visitaID=Facturas.VisitaID) as Pago_Con", "(((Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID) left join Visitas on Visitas.Id=Facturas.VisitaID) left join Clientes on Clientes.ID=Visitas.ClienteID", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision");
			}
			return BD.ConsultaVer("VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre,Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Facturas.Observacion, Meseros.Nombre as 'Anulada Por', PC, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente , AgruparPagoID, DocumentoSector, leyID", "(((Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID) left join Visitas on Visitas.Id=Facturas.VisitaID) left join Clientes on Clientes.ID=Visitas.ClienteID", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision");
		}
		string text4 = "inner";
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Jardin)
		{
			text4 = "inner";
		}
		if (consolidado)
		{
			string text5 = "";
			text5 = ((!pagoCon) ? ("select 'Local' as Suc, EstadoSiat, VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre," + text2 + "Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento, Anulada,FechaAnulacion,Observacion, Meseros.Nombre as 'Anulada Por' , AgruparPagoID, DocumentoSector, leyID,  Facturas.Correo, Facturas.Enviada, Facturas.Telefono, Facturas.TipoDocumentoID  from (Facturas  " + text4 + " join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " order by Facturas.FechaEmision") : ("select 'Local' as Suc, EstadoSiat, VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre," + text2 + "Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento, Anulada,FechaAnulacion,Observacion, Meseros.Nombre as 'Anulada Por', PC, AgruparPagoID, DocumentoSector, leyID,  Facturas.Correo, Facturas.Enviada, Facturas.Telefono, Facturas.TipoDocumentoID, (SELECT max(Cuentas.Nombre) FROM  (DetalleCuenta inner join Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN  Cuentas ON Pagos.CuentaID = Cuentas.CuentaID where DetalleCuenta.visitaID=Facturas.VisitaID) as Pago_Con from (Facturas  " + text4 + " join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " order by Facturas.FechaEmision"));
			return BD.ConsultaVerParaReporte(text5, consolidado);
		}
		DataTable dataTable = ((!pagoCon) ? BD.ConsultaVer("EstadoSiat,VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre," + text2 + "Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento, Anulada,FechaAnulacion,Facturas.Observacion, Meseros.Nombre as 'Anulada Por', PC, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente , AgruparPagoID, DocumentoSector, leyID,  Facturas.Correo, Facturas.Enviada, Facturas.Telefono, Facturas.TipoDocumentoID, FueraLineaID as ContingenciaID, CufdID", "(((Facturas " + text4 + " join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID) left join Visitas on Visitas.Id=Facturas.VisitaID) left join Clientes on Clientes.ID=Visitas.ClienteID", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision") : BD.ConsultaVer("EstadoSiat,VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre," + text2 + "Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento, Anulada,FechaAnulacion,Facturas.Observacion, Meseros.Nombre as 'Anulada Por', PC, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, AgruparPagoID, DocumentoSector, leyID,  Facturas.Correo, Facturas.Enviada, Facturas.Telefono, Facturas.TipoDocumentoID, FueraLineaID as ContingenciaID, CufdID, (SELECT max(Cuentas.Nombre) FROM  (DetalleCuenta inner join Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN  Cuentas ON Pagos.CuentaID = Cuentas.CuentaID where DetalleCuenta.visitaID=Facturas.VisitaID) as Pago_Con", "(((Facturas  " + text4 + " join FactElectCUFD on (FactElectCUFD.FactElectCUFDID=Facturas.CUFDid and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID) left join Visitas on Visitas.Id=Facturas.VisitaID) left join Clientes on Clientes.ID=Visitas.ClienteID", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision"));
		DataTable table = ((!pagoCon) ? BD.ConsultaVer("0 as EstadoSiat,VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre," + text2 + "Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Facturas.Observacion, Meseros.Nombre as 'Anulada Por', PC, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente , AgruparPagoID, DocumentoSector, leyID", "(((Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID) left join Visitas on Visitas.Id=Facturas.VisitaID) left join Clientes on Clientes.ID=Visitas.ClienteID", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision") : BD.ConsultaVer("0 as EstadoSiat,VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre," + text2 + "Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo as Codigo_Control,Facturas.Monto,Facturas.Descuento,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Facturas.Observacion, Meseros.Nombre as 'Anulada Por', PC, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, AgruparPagoID, DocumentoSector, leyID, (SELECT max(Cuentas.Nombre) FROM  (DetalleCuenta inner join Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN  Cuentas ON Pagos.CuentaID = Cuentas.CuentaID where DetalleCuenta.visitaID=Facturas.VisitaID) as Pago_Con", "(((Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID) left join Visitas on Visitas.Id=Facturas.VisitaID) left join Clientes on Clientes.ID=Visitas.ClienteID", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision"));
		dataTable.Merge(table, preserveChanges: true);
		return dataTable;
	}

	public DataTable devolverReporteFactura2(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool pagoCon, bool consolidado)
	{
		string text = "";
		if (!chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = "1=2";
		}
		if (!chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " Facturas.Anulada= " + VariableGeneral.armarBolean(1);
		}
		if (!chbNombre & chbSnimbre & !chbAnulado)
		{
			text = "( Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + " )";
		}
		if (!chbNombre & chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit='0' or (Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = " (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit<>'0' or (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & chbSnimbre & !chbAnulado)
		{
			text = " ( Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & chbSnimbre & chbAnulado)
		{
			text = " 1=1 ";
		}
		if (consolidado)
		{
			string text2 = "";
			text2 = ((!pagoCon) ? ("select 'Local' as Suc, Facturas.VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre,Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo,Facturas.Monto,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Observacion, Meseros.Nombre as 'Anulada Por' , AgruparPagoID  from (Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " order by Facturas.FechaEmision") : ("select 'Local' as Suc, Facturas.VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre,Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo,Facturas.Monto,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Observacion, Meseros.Nombre as 'Anulada Por', AgruparPagoID, (SELECT max(Cuentas.Nombre) FROM  (DetalleCuenta inner join Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN  Cuentas ON Pagos.CuentaID = Cuentas.CuentaID where DetalleCuenta.visitaID=Facturas.VisitaID) as Pago_Con from (Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID where " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " order by Facturas.FechaEmision"));
			return BD.ConsultaVerParaReporte(text2, consolidado);
		}
		if (pagoCon)
		{
			return BD.ConsultaVer("Facturas.VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre,Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo,Facturas.Monto,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Facturas.Observacion, Meseros.Nombre as 'Anulada Por', Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, AgruparPagoID, (SELECT max(Cuentas.Nombre) FROM  (DetalleCuenta inner join Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN  Cuentas ON Pagos.CuentaID = Cuentas.CuentaID where DetalleCuenta.visitaID=Facturas.VisitaID) as Pago_Con , tab1.mesero as EmitioFactura", "((((Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID) left join Visitas on Visitas.Id=Facturas.VisitaID) left join Clientes on Clientes.ID=Visitas.ClienteID) left join (select DetalleCuenta.VisitaID, Max(Meseros.Nombre) as Mesero\r\n                           from DetalleCuenta left join meseros on DetalleCuenta.MeseroID =Meseros.MeseroID \r\n                             group by DetalleCuenta.VisitaID) as tab1 on Visitas.id=tab1.visitaID", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision");
		}
		return BD.ConsultaVer("Facturas.VisitaID,Facturas.FacturaID,Facturas.NIT,Facturas.Nombre,Facturas.FechaEmision,Facturas.NroFactura,Facturas.Codigo,Facturas.Monto,CodigosFacturas.Llave,CodigosFacturas.Autorizacion, Anulada,FechaAnulacion,Facturas.Observacion, Meseros.Nombre as 'Anulada Por', Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente , AgruparPagoID, tab1.mesero as EmitioFactura", "((((Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID) left join Visitas on Visitas.Id=Facturas.VisitaID) left join Clientes on Clientes.ID=Visitas.ClienteID) left join  (select DetalleCuenta.VisitaID, Max(Meseros.Nombre) as Mesero\r\n                                     from DetalleCuenta left join meseros on DetalleCuenta.MeseroID =Meseros.MeseroID \r\n                                     group by DetalleCuenta.VisitaID) as tab1 on Visitas.id=tab1.visitaID ", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision");
	}

	public DataTable devolverReporteFacturaLite(DateTime desde, DateTime hasta, bool chbNombre, bool chbSnimbre, bool chbAnulado, bool pagoCon, bool consolidado)
	{
		string text = "";
		if (!chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = "1=2";
		}
		if (!chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " Facturas.Anulada= " + VariableGeneral.armarBolean(1);
		}
		if (!chbNombre & chbSnimbre & !chbAnulado)
		{
			text = "( Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + " )";
		}
		if (!chbNombre & chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit='0' or (Facturas.Nit='0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & !chbSnimbre & !chbAnulado)
		{
			text = " (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & !chbSnimbre & chbAnulado)
		{
			text = " (Facturas.Nit<>'0' or (Facturas.Nit<>'0' and Facturas.Anulada= " + VariableGeneral.armarBolean(1) + "))";
		}
		if (chbNombre & chbSnimbre & !chbAnulado)
		{
			text = " ( Facturas.Anulada= " + VariableGeneral.armarBolean(0) + ")";
		}
		if (chbNombre & chbSnimbre & chbAnulado)
		{
			text = " 1=1 ";
		}
		return BD.ConsultaVer(" tab1.mesero as EmitioFactura, (SELECT max(Cuentas.Nombre) FROM  (DetalleCuenta inner join Pagos ON DetalleCuenta.ID = Pagos.DetalleCuentaID) INNER JOIN  Cuentas ON Pagos.CuentaID = Cuentas.CuentaID where DetalleCuenta.visitaID=Facturas.VisitaID) as Pago_Con ,Facturas.FechaEmision,Facturas.NroFactura,Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, Facturas.NIT,Facturas.Nombre,Facturas.Monto, Anulada ", "((((Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoID=Facturas.CodigoID and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")) left join Meseros on Meseros.MeseroId=Facturas.PersonalID) left join Visitas on Visitas.Id=Facturas.VisitaID) left join Clientes on Clientes.ID=Visitas.ClienteID) left join (select DetalleCuenta.VisitaID, Max(Meseros.Nombre) as Mesero\r\n                           from DetalleCuenta left join meseros on DetalleCuenta.MeseroID =Meseros.MeseroID \r\n                             group by DetalleCuenta.VisitaID) as tab1 on Visitas.id=tab1.visitaID", " " + text + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta), "Facturas.FechaEmision");
	}

	public DataTable devolverReporteFacturaPorHoras(DateTime desde, DateTime hasta)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer(" SELECT Format(FechaEmision,'mm/dd/yyyy') as Fecha,Format(FechaEmision,'hh') & ' a ' & (Format(FechaEmision,'hh')+1) as hora, sum(Monto) as Ventas, count(*) as Tc, round(sum(Monto) /count(*),2)  as Tp  FROM Facturas  where Facturas.Anulada = " + VariableGeneral.armarBolean(0) + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " group by Format(FechaEmision,'mm/dd/yyyy'),Format(FechaEmision,'hh')  UNION  SELECT Format(FechaEmision,'mm/dd/yyyy') as Fecha,'TOTAL' as hora, sum(Monto) as Ventas, count(*) as Tc, round(sum(Monto) /count(*),2)  as Tp  FROM Facturas  where Facturas.Anulada = " + VariableGeneral.armarBolean(0) + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " group by Format(FechaEmision,'mm/dd/yyyy')  order by Fecha ");
		}
		return BD.ConsultaVer(" SELECT Format(FechaEmision,'MM/dd/yyyy') as Fecha,concat(Format(FechaEmision,'hh') , ' a ' , (Format(FechaEmision,'hh')+1)) as hora, sum(Monto) as Ventas, count(*) as Tc, round(sum(Monto) /count(*),2)  as Tp  FROM Facturas  where Facturas.Anulada = " + VariableGeneral.armarBolean(0) + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " group by Format(FechaEmision,'MM/dd/yyyy'),Format(FechaEmision,'hh')  UNION  SELECT Format(FechaEmision,'MM/dd/yyyy') as Fecha,'TOTAL' as hora, sum(Monto) as Ventas, count(*) as Tc, round(sum(Monto) /count(*),2)  as Tp  FROM Facturas  where Facturas.Anulada = " + VariableGeneral.armarBolean(0) + " and FechaEmision between " + VariableGeneral.ArmarFecha(desde) + " and " + VariableGeneral.ArmarFecha(hasta) + " group by Format(FechaEmision,'MM/dd/yyyy')  order by Fecha ");
	}

	public void eliminarFactura()
	{
		BD.ConsultaEliminar("Facturas", ("FacturaID=" + Conversions.ToString(FacturaID)) ?? "");
	}

	public int AnularFactura1(bool fact2)
	{
		int result;
		try
		{
			BD.ConsultaModificar("Facturas" + (fact2 ? "2" : ""), "Anulada=" + VariableGeneral.armarBolean(Anulada) + ",FechaAnulacion=" + VariableGeneral.ArmarFecha(FechaAnulacion, FechaAnulacionCh) + ",PersonalID= " + Conversions.ToString(personalID) + ",Observacion='" + Observacion + "',flagSync=NULL", ("FacturaID=" + Conversions.ToString(FacturaID)) ?? "");
			result = 1;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool UpdateDescuento(double descto)
	{
		if (BD.ConsultaModificar("Facturas", "descuento=" + Conversion.Str(descto) + ",flagSync=NULL", "FacturaID= " + Conversions.ToString(FacturaID)) != 0)
		{
			return true;
		}
		return false;
	}

	public bool Insertar(double descto, bool factura2)
	{
		bool result;
		try
		{
			string text = "";
			if (factura2)
			{
				text = "2";
			}
			if (configuration.gStyleBoliches1 > configuration.styleBolichesId.Bless)
			{
				result = ((BD.ConsultaInsertar3(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat("'" + NIT.ToString() + "','" + Nombre + "',", VariableGeneral.ArmarFecha(FechaEmision), ","), NroFactura.ToString(), ",'", Codigo, "',"), Conversion.Str(Monto), ","), VisitaID.ToString(), ","), CodigoID.ToString(), ",", VariableGeneral.armarBolean(aux: false), ",'", Aux, "',", AgruparPagoID, ",", Conversion.Str(descto), ",", Conversion.Str(MontoICE), ",", Conversion.Str(MontoGiftCard), ",'", MyProject.Computer.Name, "','", Correo, "','", Complemento, "',", Conversions.ToString(TipoDocumentoID), ",", leyID, ",'", Telefono, "',", Conversions.ToString(FueraLineaID), ",", VariableGeneral.armarBolean(0), ",", Conversions.ToString(CUFDid), ",", Conversions.ToString(DocumentoSector)), "Facturas" + text + "( NIT, Nombre, FechaEmision, NroFactura, Codigo, Monto, VisitaID, CodigoID, Anulada,Aux,AgruparPagoID, descuento, ICE,MontoGiftCard,PC,Correo,Complemento, TipoDocumentoID,leyID, Telefono,FueraLineaID, enviada,CUFDid,documentoSector) ", ref FacturaID) != 0) ? true : false);
			}
			else
			{
				FacturaID = Conversions.ToInteger(Operators.AddObject(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(FacturaID)", "Facturas").Rows[0][0]), 0), 1));
				result = ((BD.ConsultaInsertar(string.Concat(string.Concat(string.Concat(string.Concat(string.Concat(Conversions.ToString(_FacturaID) + ",'" + NIT.ToString() + "','" + Nombre + "',", VariableGeneral.ArmarFecha(FechaEmision), ","), NroFactura.ToString(), ",'", Codigo, "',"), Conversion.Str(Monto), ","), VisitaID.ToString(), ","), CodigoID.ToString(), ",", VariableGeneral.armarBolean(aux: false), ",'", Aux, "',", AgruparPagoID, ",", Conversion.Str(descto), ",", Conversion.Str(MontoICE), ",", Conversion.Str(MontoGiftCard), ",'", MyProject.Computer.Name, "','", Correo, "','", Complemento, "',", Conversions.ToString(TipoDocumentoID), ",", leyID, ",'", Telefono, "',", Conversions.ToString(FueraLineaID), ",", VariableGeneral.armarBolean(0), ",", Conversions.ToString(CUFDid), ",", Conversions.ToString(DocumentoSector)), "Facturas" + text + "(FacturaID, NIT, Nombre, FechaEmision, NroFactura, Codigo, Monto, VisitaID, CodigoID, Anulada,Aux,AgruparPagoID, descuento, ICE,MontoGiftCard,PC,Correo,Complemento, TipoDocumentoID,leyID, Telefono,FueraLineaID, enviada,CUFDid,documentoSector) ") != 0) ? true : false);
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool getEstado(ref int estado)
	{
		bool result;
		try
		{
			estado = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("estadoSiat", "Facturas", "FacturaID=" + Conversions.ToString(FacturaID)).Rows[0][0]), 0));
			result = estado >= 0;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool setContingenciaID(int contiID)
	{
		bool result;
		try
		{
			result = ((BD.ConsultaModificar("Facturas", "FueraLineaID=" + Conversions.ToString(contiID) + ",flagSync=NULL", "FacturaID= " + Conversions.ToString(FacturaID)) != 0) ? true : false);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool setCodigoRecepcion(string codigoRecepcion)
	{
		bool result;
		try
		{
			result = ((BD.ConsultaModificar("Facturas", "codigoRecepcion='" + codigoRecepcion + "',flagSync=NULL", "FacturaID= " + Conversions.ToString(FacturaID)) != 0) ? true : false);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool setEstado(int estado)
	{
		bool result;
		try
		{
			result = ((BD.ConsultaModificar("Facturas", "EstadoSIAT=" + Conversions.ToString(estado) + ",flagSync=NULL", "FacturaID= " + Conversions.ToString(FacturaID)) != 0) ? true : false);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool EmailEnviado()
	{
		bool result;
		try
		{
			result = ((BD.ConsultaModificar("Facturas", "Enviada=" + VariableGeneral.armarBolean(1) + ",flagSync=NULL", "FacturaID= " + Conversions.ToString(FacturaID)) != 0) ? true : false);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool Modificar()
	{
		bool result;
		try
		{
			result = ((BD.ConsultaModificar("Facturas", "NIT='" + NIT.ToString() + "',nombre='" + Nombre + "',Codigo='" + Codigo + "',NroFactura =" + Conversions.ToString(NroFactura) + ",FechaEmision=" + VariableGeneral.ArmarFecha(FechaEmision) + ",Monto=" + Conversion.Str(Monto) + ",flagSync=NULL", "FacturaID= " + Conversions.ToString(FacturaID)) != 0) ? true : false);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public int Eliminar()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Facturas", "FacturaID = " + FacturaID) == 0)
			{
				Interaction.MsgBox("no se puede eliminar esa Factura, se encuentra en uso");
				result = 0;
			}
			else
			{
				result = 1;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			result = 0;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public DataTable DevoverReporte1(DateTime FechaIni, DateTime fechaFin)
	{
		if (configuration.gTipoFacturacion == 1)
		{
			return BD.ConsultaVer("Facturas.FechaEmision, Facturas.NIT, Facturas.Nombre, Facturas.NroFactura, CodigosFacturas.Autorizacion, Facturas.Codigo,Facturas.Monto, Facturas.Anulada", " Facturas inner join CodigosFacturas on (Facturas.CodigoID = CodigosFacturas.CodigoID  and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " Facturas.FechaEmision between " + VariableGeneral.ArmarFecha(FechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin), "FechaEmision");
		}
		return BD.ConsultaVer("Facturas.FechaEmision, Facturas.NIT, Facturas.Nombre, Facturas.NroFactura, CodigosFacturas.Autorizacion, Facturas.Codigo,Facturas.Monto, Facturas.Anulada", " Facturas inner join FactElectCUFD on (Facturas.FactElectCUFDID = FactElectCUFD.CUFDid  and FactElectCUFD.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " Facturas.FechaEmision between " + VariableGeneral.ArmarFecha(FechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin), "FechaEmision");
	}

	public DataTable devolverFueradeLineaNoEnviada()
	{
		return BD.ConsultaVer("Correo ,NroFactura,Nombre,FechaEmision,Codigo", "Facturas", "FueraLineaID = " + Conversions.ToString(FueraLineaID) + " and Anulada=" + VariableGeneral.armarBolean(0) + " and Enviada=" + VariableGeneral.armarBolean(0));
	}

	public DataTable DevolverReportePorMovimientos(DateTime FechaIni, DateTime fechaFin)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("MovimientoID,tab1.Fecha,tab1.Monto, MinDeFechaIni,MaxDeFechaFin, min(Facturas.NroFactura) as minNroFact, max(Facturas.NroFactura) as maxNroFact, sum( IIF(Anulada,0,  Facturas.Monto)) as TotalFact,tab1.Observacion", "((SELECT Movimientos.MovimientoID, Movimientos.Fecha,Movimientos.Monto,Movimientos.Observacion, Min(Turnos.FechaIni) AS MinDeFechaIni, Max(Turnos.FechaFin) AS MaxDeFechaFin FROM  ((Movimientos INNER JOIN Movimientos_Turnos ON Movimientos.MovimientoID = Movimientos_Turnos.MovimientoID) INNER JOIN Turnos ON Movimientos_Turnos.TurnoID = Turnos.TurnoID) GROUP BY Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto,Movimientos.Observacion) as tab1 inner join  Facturas on (Facturas.FechaEmision >= tab1.MinDeFechaIni  and  Facturas.FechaEmision <= tab1.MaxDeFechaFin))  inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " Facturas.FechaEmision between " + VariableGeneral.ArmarFecha(FechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin), "max(Facturas.NroFactura)", "MovimientoID, MinDeFechaIni,MaxDeFechaFin,tab1.Fecha,tab1.Monto,tab1.Observacion");
		}
		return BD.ConsultaVer("MovimientoID,tab1.Fecha,tab1.Monto, MinDeFechaIni,MaxDeFechaFin, min(Facturas.NroFactura) as minNroFact, max(Facturas.NroFactura) as maxNroFact, sum( case when Anulada=1  then 0 else Facturas.Monto end) as TotalFact,tab1.Observacion ,tab1.Observacion", "(SELECT Movimientos.MovimientoID, Movimientos.Fecha,Movimientos.Monto,Movimientos.Observacion, Min(Turnos.FechaIni) AS MinDeFechaIni, Max(Turnos.FechaFin) AS MaxDeFechaFin FROM  ((Movimientos INNER JOIN Movimientos_Turnos ON Movimientos.MovimientoID = Movimientos_Turnos.MovimientoID) INNER JOIN Turnos ON Movimientos_Turnos.TurnoID = Turnos.TurnoID) GROUP BY Movimientos.MovimientoID,Movimientos.Fecha,Movimientos.Monto,Movimientos.Observacion) as tab1 inner join  Facturas on (Facturas.FechaEmision >= tab1.MinDeFechaIni  and  Facturas.FechaEmision <= tab1.MaxDeFechaFin)  inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " Facturas.FechaEmision between " + VariableGeneral.ArmarFecha(FechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin), "max(Facturas.NroFactura)", "MovimientoID, MinDeFechaIni,MaxDeFechaFin,tab1.Fecha,tab1.Monto,tab1.Observacion");
	}

	public DataTable DevolverReportePorTurnos(DateTime FechaIni, DateTime fechaFin)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("FechaIni,  FechaFin, Turnos.Nro, min(Facturas.NroFactura) as minNroFact , max(Facturas.NroFactura) as maxNroFact , sum( IIF(Anulada,0,  Facturas.Monto)) as Monto", " (Turnos  inner join  Facturas on (Facturas.FechaEmision >= Turnos.FechaIni  and  Facturas.FechaEmision <= Turnos.FechaFin))  inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " Facturas.FechaEmision between " + VariableGeneral.ArmarFecha(FechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin), "FechaIni", "FechaIni,FechaFin, Turnos.Nro");
		}
		return BD.ConsultaVer("FechaIni,  FechaFin, Turnos.Nro, min(Facturas.NroFactura) as minNroFact , max(Facturas.NroFactura) as maxNroFact ,sum( case when Anulada=1  then 0 else Facturas.Monto end) as Monto", " (Turnos  inner join  Facturas on (Facturas.FechaEmision >= Turnos.FechaIni  and  Facturas.FechaEmision <= Turnos.FechaFin))  inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", " Facturas.FechaEmision between " + VariableGeneral.ArmarFecha(FechaIni) + " and " + VariableGeneral.ArmarFecha(fechaFin), "FechaIni", "FechaIni,FechaFin, Turnos.Nro");
	}

	public int VerificarCodigo()
	{
		if (configuration.gTipoFacturacion == 1)
		{
			return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("Count(*)", "Facturas inner join CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "CodigosFacturas.CodigoID =" + CodigoID.ToString() + " and anulada=" + VariableGeneral.armarBolean(0)).Rows[0][0]), 0));
		}
		return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("Count(*)", "Facturas leftjoin CodigosFacturas on (CodigosFacturas.CodigoId=Facturas.CodigoId and CodigosFacturas.ConfiguracionId=" + Conversions.ToString(VariableGeneral.gConfiguracionID) + ")", "CodigosFacturas.CodigoID =" + CodigoID.ToString() + " and anulada=" + VariableGeneral.armarBolean(0)).Rows[0][0]), 0));
	}

	public void getNitNombrePorAux(ref string nit1, ref string nombre1)
	{
		DataTable dataTable = BD.ConsultaVer(" top 1 facturaID,Nit, Nombre", "Facturas", "Aux='" + Aux.ToString() + "' and Anulada=" + VariableGeneral.armarBolean(0), "facturaID desc");
		if (dataTable.Rows.Count > 0)
		{
			nombre1 = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			nit1 = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nit"])) ? "" : dataTable.Rows[0]["Nit"]);
		}
		else
		{
			Nombre = "";
			Aux = "";
		}
	}

	public void DevolverFacturaXVisita()
	{
		DataTable dataTable = BD.ConsultaVer("*", "Facturas", " VisitaID=" + VisitaID.ToString(), "FacturaID desc");
		if (dataTable.Rows.Count > 0)
		{
			FacturaID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaID"])) ? ((object)0) : dataTable.Rows[0]["FacturaID"]);
			NIT = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NIT"])) ? ((object)0) : dataTable.Rows[0]["NIT"]);
			Nombre = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Nombre"])) ? "" : dataTable.Rows[0]["Nombre"]);
			FechaEmision = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaEmision"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaEmision"]);
			NroFactura = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroFactura"])) ? ((object)0) : dataTable.Rows[0]["NroFactura"]);
			Codigo = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Codigo"])) ? "" : dataTable.Rows[0]["Codigo"]);
			Monto = Conversions.ToDouble(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Monto"])) ? ((object)0) : dataTable.Rows[0]["Monto"]);
			VisitaID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["VisitaID"])) ? ((object)0) : dataTable.Rows[0]["VisitaID"]);
			CodigoID = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CodigoID"])) ? ((object)0) : dataTable.Rows[0]["CodigoID"]);
			CUFDid = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CUFDid"])) ? ((object)0) : dataTable.Rows[0]["CUFDid"]);
			Anulada = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Anulada"])) ? ((object)false) : dataTable.Rows[0]["Anulada"]);
			FechaAnulacionCh = !Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaAnulacion"]));
			FechaAnulacion = Conversions.ToDate(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FechaAnulacion"])) ? ((object)DateAndTime.Now) : dataTable.Rows[0]["FechaAnulacion"]);
			Observacion = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Observacion"])) ? "" : dataTable.Rows[0]["Observacion"]);
			personalID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["personalID"])) ? ((object)0) : dataTable.Rows[0]["personalID"]);
			Aux = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Aux"])) ? ((object)0) : dataTable.Rows[0]["Aux"]);
		}
		else
		{
			NIT = Conversions.ToString(0);
			Nombre = "";
			FechaEmision = DateAndTime.Now;
			NroFactura = 0;
			Codigo = Conversions.ToString(0);
			Monto = 0.0;
			VisitaID = Conversions.ToString(0);
			CodigoID = Conversions.ToString(0);
			Anulada = false;
			CUFDid = 0;
		}
	}

	public void getEstado()
	{
		DataTable dataTable = BD.ConsultaVer("EstadoSiat", "Facturas", " FacturaID=" + FacturaID);
		if (dataTable.Rows.Count > 0)
		{
			EstadoSiat = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EstadoSiat"])) ? ((object)0) : dataTable.Rows[0]["EstadoSiat"]);
		}
		else
		{
			EstadoSiat = Conversions.ToString(0);
		}
	}

	public DataTable devolverCumpleañerosFactura(string Mes)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("nit, Nombre,MID(Aux,1,10) as fechaCumpleaños, MID(Aux,12,Len(Aux)) as Telefono ", "facturas", "nit <> '0' and aux <> '' and (MID(Aux,4,2) = '" + Mes + "')", " Day(MID(Aux,1,10))  ", " nit, Nombre, Aux");
		}
		return BD.ConsultaVer("nit, Nombre, Aux as fechaCumpleaños, Substring(Aux,12,Len(Aux)) as Telefono ", "facturas", "nit <> '0' and aux  <> '' and (Substring(ISNULL(Aux,'00/00/0000'),4,2) = '" + Mes + "')", "", " nit, Nombre, Aux");
	}
}
