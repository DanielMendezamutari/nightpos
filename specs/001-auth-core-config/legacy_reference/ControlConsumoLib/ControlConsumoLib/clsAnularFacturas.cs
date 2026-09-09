using System;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsAnularFacturas
{
	private readonly clsFacturas clsFac;

	public clsAnularFacturas()
	{
		clsFac = new clsFacturas();
	}

	public bool AnularFactura1(int facturaID, bool Anulada, DateTime FechaAnulacion, bool FechaAnulacionCh, int personalID, string observacion, bool fact2, int razon, int TipoDocumentoSector)
	{
		clsFac._FacturaID = facturaID;
		clsFac._FechaAnulacion = FechaAnulacion;
		clsFac._FechaAnulacionCh = FechaAnulacionCh;
		clsFac._Observacion = observacion;
		clsFac._personalId = personalID;
		clsFac._Anulada = Anulada;
		if (configuration.gTipoFacturacion == 1)
		{
			clsFac.AnularFactura1(fact2);
			return true;
		}
		if (Anulada)
		{
			int codigoEmision = 1;
			int codigoMotivo = razon;
			if (razon == 0)
			{
				codigoMotivo = 2;
			}
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			clsFactElecConfig2.devolverDatosSiTokenActivo1();
			clsFactElecObtencionCodigos clsFactElecObtencionCodigos2 = new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente);
			string codigoCUIS = "";
			string codigoCUFD = "";
			string CodigoControl = "";
			if (codigoCUIS.Length == 0)
			{
				clsFactElecObtencionCodigos2.ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			}
			if (codigoCUFD.Length == 0)
			{
				DateTime fecha = DateAndTime.Now;
				int cufdID = 0;
				clsFactElecObtencionCodigos2.ObtenerCUFD(ref codigoCUFD, ref CodigoControl, ref fecha, ref cufdID);
			}
			if (codigoCUFD.Length == 0)
			{
				Interaction.MsgBox("No se pudo obtener codigo CUFD");
				return false;
			}
			string text = Conversions.ToString(BD.ConsultaVer("codigo", "Facturas" + (fact2 ? "2" : ""), "Facturaid=" + Conversions.ToString(clsFac._FacturaID)).Rows[0][0]);
			clsFactElecSectorCompraVenta clsFactElecSectorCompraVenta2 = new clsFactElecSectorCompraVenta((int)clsFactElecConfig2.CodigoAmbiente, TipoDocumentoSector, (int)clsFactElecConfig2.CodigoModalidad);
			if (fact2)
			{
				clsFac.AnularFactura1(fact2);
				clsFac.setEstado(2);
				return true;
			}
			bool ExisteEnSiat = false;
			int num = 1;
			switch (TipoDocumentoSector)
			{
			case 8:
				num = 2;
				break;
			case 24:
				num = 3;
				break;
			}
			string mensaje = "";
			if (clsFactElecSectorCompraVenta2.AnularFactura1(codigoMotivo, codigoEmision, codigoCUFD, codigoCUIS, num, TipoDocumentoSector, text, ref ExisteEnSiat, ref mensaje))
			{
				if (mensaje.Length > 0)
				{
					Interaction.MsgBox(mensaje);
				}
				clsFactElecSectorCompraVenta obj = new clsFactElecSectorCompraVenta((int)clsFactElecConfig2.CodigoAmbiente, TipoDocumentoSector, (int)clsFactElecConfig2.CodigoModalidad);
				int codigoEmision2 = 1;
				int estado = 0;
				string cufd = codigoCUFD;
				string cuis = codigoCUIS;
				int tipoFacturaDocumento = num;
				string error = "";
				if (Conversions.ToBoolean(obj.ValidacionFactura1(codigoEmision2, cufd, cuis, tipoFacturaDocumento, TipoDocumentoSector, text, ref estado, ref error)))
				{
					clsFac.setEstado(estado);
					if (estado == 2)
					{
						clsFac.AnularFactura1(fact2);
						return true;
					}
					Interaction.MsgBox("Se verifico en el SIAT y esa factura no esta bien anuladada, intente de nuevo");
					return false;
				}
				return false;
			}
			if (!ExisteEnSiat)
			{
				if (Interaction.MsgBox("No se pudo anular en SIAT, Desea anularla en restotech?\r\n" + mensaje, MsgBoxStyle.YesNo, " Anular ") == MsgBoxResult.Yes)
				{
					clsFac.AnularFactura1(fact2);
					return true;
				}
				return false;
			}
			int estado2 = 0;
			clsFac.getEstado(ref estado2);
			if (estado2 == 1)
			{
				Interaction.MsgBox("No se pudo anular en SIAT. \r\n" + mensaje);
				return false;
			}
			Interaction.MsgBox("Estado Actual: " + Conversions.ToString(estado2) + "\r\n" + mensaje);
			clsFac.AnularFactura1(fact2);
			return true;
		}
		int codigoEmision3 = 1;
		clsFactElecConfig clsFactElecConfig3 = new clsFactElecConfig();
		clsFactElecConfig3.devolverDatosSiTokenActivo1();
		clsFactElecObtencionCodigos clsFactElecObtencionCodigos3 = new clsFactElecObtencionCodigos((int)clsFactElecConfig3.CodigoAmbiente);
		string codigoCUIS2 = "";
		string codigoCUFD2 = "";
		string CodigoControl2 = "";
		if (codigoCUIS2.Length == 0)
		{
			clsFactElecObtencionCodigos3.ObtenerCUIS(ref codigoCUIS2, clsFactElecConfig3.CodigoPuntoVenta);
		}
		if (codigoCUFD2.Length == 0)
		{
			DateTime fecha = DateAndTime.Now;
			int cufdID = 0;
			clsFactElecObtencionCodigos3.ObtenerCUFD(ref codigoCUFD2, ref CodigoControl2, ref fecha, ref cufdID);
		}
		if (codigoCUFD2.Length == 0)
		{
			Interaction.MsgBox("No se pudo obtener codigo CUFD");
			return false;
		}
		string text2 = Conversions.ToString(BD.ConsultaVer("codigo", "Facturas" + (fact2 ? "2" : ""), "Facturaid=" + Conversions.ToString(clsFac._FacturaID)).Rows[0][0]);
		clsFactElecSectorCompraVenta clsFactElecSectorCompraVenta3 = new clsFactElecSectorCompraVenta((int)clsFactElecConfig3.CodigoAmbiente, TipoDocumentoSector, (int)clsFactElecConfig3.CodigoModalidad);
		if (fact2)
		{
			clsFac.AnularFactura1(fact2);
			clsFac.setEstado(1);
			return true;
		}
		bool ExisteEnSiat2 = false;
		int num2 = 1;
		switch (TipoDocumentoSector)
		{
		case 8:
			num2 = 2;
			break;
		case 24:
			num2 = 3;
			break;
		}
		string mensaje2 = "";
		if (clsFactElecSectorCompraVenta3.reversionAnulacionFactura(codigoEmision3, codigoCUFD2, codigoCUIS2, num2, TipoDocumentoSector, text2, ref ExisteEnSiat2, ref mensaje2))
		{
			if (mensaje2.Length > 0)
			{
				Interaction.MsgBox(mensaje2);
			}
			clsFactElecSectorCompraVenta obj2 = new clsFactElecSectorCompraVenta((int)clsFactElecConfig3.CodigoAmbiente, TipoDocumentoSector, (int)clsFactElecConfig3.CodigoModalidad);
			int codigoEmision4 = 1;
			int estado3 = 0;
			string cufd2 = codigoCUFD2;
			string cuis2 = codigoCUIS2;
			int tipoFacturaDocumento2 = num2;
			string error = "";
			if (Conversions.ToBoolean(obj2.ValidacionFactura1(codigoEmision4, cufd2, cuis2, tipoFacturaDocumento2, TipoDocumentoSector, text2, ref estado3, ref error)))
			{
				clsFac.setEstado(estado3);
				if (estado3 == 1)
				{
					clsFac.AnularFactura1(fact2);
					return true;
				}
				Interaction.MsgBox("Se verifico en el SIAT y esa factura sigue anuladada, intente de nuevo");
				return false;
			}
			return false;
		}
		if (!ExisteEnSiat2)
		{
			if (Interaction.MsgBox("No se pudo desanular en SIAT, no la encuentra, desea desanularla en Restotech", MsgBoxStyle.YesNo, " Desanular ") == MsgBoxResult.Yes)
			{
				clsFac.AnularFactura1(fact2);
				return true;
			}
			return false;
		}
		int estado4 = 0;
		clsFac.getEstado(ref estado4);
		if (estado4 != 1)
		{
			Interaction.MsgBox("No se pudo desanular en SIAT.\r\n" + mensaje2);
			return false;
		}
		Interaction.MsgBox(mensaje2);
		clsFac.AnularFactura1(fact2);
		return true;
	}
}
