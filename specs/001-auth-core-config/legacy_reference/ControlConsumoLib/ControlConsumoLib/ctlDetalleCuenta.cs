using System;
using System.Data;
using System.Globalization;
using System.Runtime.CompilerServices;
using System.Runtime.InteropServices;
using ConfigToptech;
using ControlConsumoLib.My;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlDetalleCuenta
{
	private readonly clsDetalleCuenta clsDet;

	private readonly clsObservaciones clsObs;

	private readonly ctlAnticipos ctlAnt;

	private readonly clsAnticiposCuentas clsAntCuenta;

	private bool _antic;

	public double _Desc;

	public ctlDetalleCuenta()
	{
		clsDet = new clsDetalleCuenta();
		clsObs = new clsObservaciones();
		ctlAnt = new ctlAnticipos();
		clsAntCuenta = new clsAnticiposCuentas();
		_antic = false;
		_Desc = 1.0;
	}

	public int GetID()
	{
		return clsDet._ID;
	}

	public void SetID(int ID)
	{
		clsDet._ID = ID;
	}

	public DataTable TieneDiferentesDosificacionesYsectores(int visitaID)
	{
		return clsDet.TieneDiferentesDosificacionesYsectores(visitaID);
	}

	public DataTable TieneDiferentesDosificacionesAgrupadorIDYsectores(int agrupadorID)
	{
		return clsDet.TieneDiferentesDosificacionesAgrupadorIDYsectores(agrupadorID);
	}

	public bool HayAlgunaVentaPorProductoID(int prodID)
	{
		return clsDet.HayAlgunaVentaPorProductoID(prodID);
	}

	public bool ClienteTieneDeuda(int clienteID)
	{
		return clsDet.ClienteTieneDeuda(clienteID);
	}

	public int getNroOrden(int visitaID)
	{
		return clsDet.getNroOrden(visitaID);
	}

	public bool restar1ytraspasar1(int visitaID)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.restar1ytraspasar1();
	}

	public bool traspasarAvisitaId(int visitaID)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.traspasarAvisitaId();
	}

	public bool traspasarAvisitaId1(int visitaID, double descuento)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.traspasarAvisitaId1(descuento);
	}

	public DataTable ToReturnCovers(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCovers();
	}

	public DataTable ToReturnCombosVisita(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCombosVisita();
	}

	public DataTable ToReturnProductosVendidosAnteriormente(int visitaID)
	{
		return clsDet.ToReturnProductosVendidosAnteriormente(visitaID);
	}

	public void BorrandoTemporal(bool Borrando)
	{
		clsDet.BorrandoTemporal(Borrando);
	}

	public void Eliminar1(double cantidad, int ProductoID, string obs, bool ParaLLevar)
	{
		clsDet.Eliminar(obs.Replace("'", "`"));
		DevolverProds(ProductoID, cantidad, ParaLLevar);
	}

	public void DevolverProds(int ProductoID, double cantidad, bool ParaLLevar)
	{
		checked
		{
			try
			{
				ctlProductos ctlProductos2 = new ctlProductos();
				ctlProductos2.SetProductoID(ProductoID);
				ctlProductos2.cargarDatosSinStock();
				ctlProductos2.setTipoProductos(ctlProductos2.getProductoTipoProductoID());
				ctlProductos2.LlenarClaseTiposProductos();
				ctlProductos2.cargarDatos(ctlProductos2.GetTipoProductoAlmacenID());
				int num = 0;
				DataTable dataTable = BD.ConsultaVer("ExtraEnMesaID,ExtraParaLlevarID", "Productos", "id=" + Conversions.ToString(ProductoID));
				num = ((!ParaLLevar) ? Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ExtraEnMesaID"]), 0)) : Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ExtraParaLlevarID"]), 0)));
				if (ctlProductos2.GetTipoProductoManejaStock() == 0)
				{
					return;
				}
				if (ctlProductos2.tienePreparacion())
				{
					ctlPreparacionesComodines ctlPreparacionesComodines2 = new ctlPreparacionesComodines();
					DataTable tbFinal = ctlPreparacionesComodines2.DevolverPreparacionesParaProducto(ProductoID, ctlProductos2.GetTipoProductoAlmacenID());
					tbFinal.Rows.Clear();
					ctlPreparacionesComodines2.getProductsRecursive(ProductoID, ref tbFinal, cantidad, telefono: false, ctlProductos2.GetTipoProductoAlmacenID(), 0);
					if (num > 0)
					{
						ctlPreparacionesComodines2.getProductsRecursive(num, ref tbFinal, cantidad, telefono: false, ctlProductos2.GetTipoProductoAlmacenID(), 0);
					}
					if (tbFinal.Rows.Count == 0)
					{
						Interaction.MsgBox("ERROR! este producto no tiene seteada sus preparaciones!");
						BD.ConsultaModificar("Productos", "TienePreparacion = " + VariableGeneral.armarBolean(0), "TienePreparacion =" + VariableGeneral.armarBolean(1) + " and id not in (select ParaProductoID from Preparaciones)");
						return;
					}
					int num2 = tbFinal.Rows.Count - 1;
					for (int i = 0; i <= num2; i++)
					{
						double num3 = Conversions.ToDouble(tbFinal.Rows[i]["Cantidad"]);
						DataTable dataTable2 = ctlPreparacionesComodines2.DevolverPreparacionesComodinesParaOrdenPedido(Conversions.ToInteger(tbFinal.Rows[i]["PreparacionID"]), ctlProductos2.GetTipoProductoAlmacenID());
						if (dataTable2.Rows.Count > 0)
						{
							ctlProductos2.SetProductoID(Conversions.ToInteger(dataTable2.Rows[0]["ProductoID"]));
							ctlProductos2.modificarStock(Conversions.ToDouble(Operators.DivideObject(num3, dataTable2.Rows[0]["CantidadML"])), clsDet._ID, 0, 0, ctlProductos2.GetTipoProductoAlmacenID());
						}
					}
				}
				else if (ctlProductos2.EsCombo())
				{
					DataTable dataTable3 = (DataTable)ctlProductos2.DevolvoverProdUsosXDetalle(clsDet._ID);
					int num4 = dataTable3.Rows.Count - 1;
					for (int j = 0; j <= num4; j++)
					{
						ctlProductos2.SetProductoID(Conversions.ToInteger(dataTable3.Rows[j]["ProductoID"]));
						ctlProductos2.modificarStock(Conversions.ToDouble(dataTable3.Rows[j]["Cantidad"]), clsDet._ID, 0, 0, ctlProductos2.GetTipoProductoAlmacenID());
					}
				}
				else
				{
					ctlProductos2.modificarStock(cantidad, clsDet._ID, 0, 0, ctlProductos2.GetTipoProductoAlmacenID());
				}
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				ProjectData.ClearProjectError();
			}
		}
	}

	public bool PagoTotalCuentaVisita2(int visitaID, double montoBs, int CuentaID, int idCliente, int transaccionId, int ResponsableID, bool debucle, string desdeDondePago, string NroTarjeta)
	{
		if (montoBs == 0.0)
		{
			return true;
		}
		DataTable dataTable = BD.ConsultaVer("DetalleCuenta.ID,Debe, ProductoID, Cantidad,PrecioUnit, Productos.Precio ", "DetalleCuenta inner join Productos on Productos.ID=DetalleCuenta.ProductoID", "DetalleCuenta.visitaID=" + Conversions.ToString(visitaID) + " and DetalleCuenta.borrada=" + VariableGeneral.armarBolean(0));
		double num = 0.0;
		double num2 = 0.0;
		if (!debucle)
		{
			ctlAnticipos ctlAnticipos2 = new ctlAnticipos();
			if (idCliente > 0)
			{
				num2 = ctlAnticipos2.devolverSaldo(idCliente);
				if (num2 > 0.0)
				{
					if (Interaction.MsgBox("El Cliente tiene " + Conversions.ToString(num2) + " Bs de anticipo, desea utilizarlo??", MsgBoxStyle.YesNo | MsgBoxStyle.DefaultButton2, " Anticipos ") == MsgBoxResult.Yes)
					{
						_antic = true;
						if (montoBs > num2)
						{
							Interaction.MsgBox("Saldo  : " + Conversions.ToString(montoBs - num2));
						}
					}
					else
					{
						num2 = 0.0;
					}
				}
			}
		}
		int maxAgruparPagoID = new clsPagos().getMaxAgruparPagoID();
		double num3 = 0.0;
		checked
		{
			int num4 = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num4; i++)
			{
				num3 = Conversions.ToDouble(Operators.MultiplyObject(dataTable.Rows[i]["Precio"], dataTable.Rows[i]["Cantidad"]));
				clsPagos clsPagos2 = new clsPagos();
				clsPagos2._Fecha = DateAndTime.Now;
				clsPagos2._MaquinaPago = desdeDondePago;
				clsPagos2._DetalleCuentaID = Conversions.ToInteger(dataTable.Rows[i]["ID"]);
				if (CuentaID == 3)
				{
					clsPagos2._MontoBs = Conversions.ToDouble(dataTable.Rows[i]["Debe"]);
					clsPagos2._nroTarjeta = NroTarjeta;
					clsPagos2._cuentaID = 3;
				}
				else
				{
					clsPagos2._MontoBs = Conversions.ToDouble(dataTable.Rows[i]["Debe"]);
					clsPagos2._cuentaID = CuentaID;
				}
				num = Conversions.ToDouble(dataTable.Rows[i]["Debe"]);
				if ((num2 > 0.0) & _antic)
				{
					double monto = Conversions.ToDouble(dataTable.Rows[i]["Debe"]);
					if (Operators.ConditionalCompareObjectLess(num2, dataTable.Rows[i]["Debe"], TextCompare: false))
					{
						double montoBs2 = clsPagos2._MontoBs - num2;
						int cuentaID = clsPagos2._cuentaID;
						clsPagos2._cuentaID = 4;
						clsPagos2._MontoBs = num2;
						num2 = 0.0;
						clsPagos2._transaccionId = 0;
						clsPagos2.Insertar(maxAgruparPagoID, ResponsableID);
						clsPagos2._cuentaID = cuentaID;
						clsPagos2._MontoBs = montoBs2;
					}
					else
					{
						clsPagos2._cuentaID = 4;
						num2 = Conversions.ToDouble(Operators.SubtractObject(num2, dataTable.Rows[i]["Debe"]));
						clsPagos2._MontoBs = Conversions.ToDouble(dataTable.Rows[i]["Debe"]);
					}
					clsDet._ID = Conversions.ToInteger(dataTable.Rows[i]["ID"]);
					ReducirAnticipos(monto, idCliente);
				}
				if (clsPagos2._MontoBs > 0.0)
				{
					if (Operators.ConditionalCompareObjectLess(dataTable.Rows[i]["precioUnit"], dataTable.Rows[i]["precio"], TextCompare: false))
					{
						clsPagos2._Descuento = num3 - num;
					}
					else
					{
						clsPagos2._Descuento = 0.0;
					}
					if (clsPagos2.Insertar(maxAgruparPagoID, ResponsableID) <= 0)
					{
						return false;
					}
				}
			}
			if (!debucle)
			{
				double value = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select sum(MontoBs) from Pagos where AgruparPagoID =" + Conversions.ToString(maxAgruparPagoID)).Rows[0][0]), 0));
				if (Math.Round(value, 0) != Math.Round(montoBs, 0))
				{
					new clsPagos().EliminarXAgruparPagoID(maxAgruparPagoID, ResponsableID, "bucle 0, los montos no coinciden: " + Conversions.ToString(Math.Round(value, 0)) + " vs " + Conversions.ToString(Math.Round(montoBs, 0)) + " , revisar!! visitaID " + Conversions.ToString(visitaID));
					return PagoTotalCuentaVisita2(visitaID, montoBs, CuentaID, idCliente, transaccionId, ResponsableID, debucle: true, desdeDondePago, NroTarjeta);
				}
			}
			else
			{
				double value2 = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("select sum(MontoBs) from Pagos where AgruparPagoID =" + Conversions.ToString(maxAgruparPagoID)).Rows[0][0]), 0));
				if (Math.Round(value2, 0) != Math.Round(montoBs, 0))
				{
					new clsPagos().EliminarXAgruparPagoID(maxAgruparPagoID, ResponsableID, "bucle 1, los montos no coinciden: " + Conversions.ToString(Math.Round(value2, 0)) + " vs " + Conversions.ToString(Math.Round(montoBs, 0)) + " , revisar!! visitaID " + Conversions.ToString(visitaID));
					Interaction.MsgBox("Algo paso mal con el pago, los montos no coinciden: " + Conversions.ToString(Math.Round(value2, 0)) + " vs " + Conversions.ToString(Math.Round(montoBs, 0)) + " , revisar!! visitaID " + Conversions.ToString(visitaID));
					return false;
				}
			}
			clsDet._VisitaID = visitaID;
			int num5 = clsDet.PagoTotalCuentaPorVisita();
			if (configuration.gStyleBoliches1 != configuration.styleBolichesId.malegria1 && num5 > 0)
			{
				DataTable dataTable2 = BD.ConsultaVer("sum(pago) as pago", "DetalleCuenta", "DetalleCuenta.Visitaid = " + Conversions.ToString(visitaID));
				double a = 0.0;
				if (dataTable2.Rows.Count > 0)
				{
					a = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0][0]), 0));
				}
				double a2 = new clsPagos().MontoPagado(visitaID);
				if (dataTable2.Rows.Count > 0 && Math.Round(a) != Math.Round(a2))
				{
					DataTable dataTable3 = new ctlDetalleCuenta().ToReturnDetalleCuentaFromVisitaIDparaBorrar1(visitaID, MyProject.Computer.Name);
					int num6 = dataTable3.Rows.Count - 1;
					for (int j = 0; j <= num6; j++)
					{
						clsPagos obj = new clsPagos();
						obj._DetalleCuentaID = Conversions.ToInteger(dataTable3.Rows[j]["ID"]);
						obj.EliminarXDetalleCuentaID1("Pago total cuenta visita 2", ResponsableID);
					}
					BD.ConsultaModificar("DetalleCuenta", "debe=debe+Pago", "VisitaID=" + Conversions.ToString(visitaID));
					BD.ConsultaModificar("DetalleCuenta", "Pago=0,Cerrada=" + VariableGeneral.armarBolean(0), "VisitaID=" + Conversions.ToString(visitaID));
					BD.ConsultaModificar("visitas", "Descuento=0", "id=" + Conversions.ToString(visitaID));
					Interaction.MsgBox("Diff en pagos, vuelva a gestionar TODO el pago");
				}
			}
			return num5 > 0;
		}
	}

	public void cambiarCantidad(int prodId, double cantNueva, string porque, double diferencia, bool ParaLLevar)
	{
		clsDet._Cantidad = cantNueva;
		clsDet._Comentarios = porque;
		clsDet.cambiarCantidad();
		DevolverProds(prodId, diferencia, ParaLLevar);
	}

	public bool PagoParcialCuenta(double Pago, double Debe, bool Cerrada, string Comentarios)
	{
		clsDet._Pago = Pago;
		clsDet._Debe = Debe;
		clsDet._Cerrada = Cerrada;
		clsDet._Comentarios = Comentarios;
		return clsDet.PagoParcialCuenta1() > 0;
	}

	public DataTable ToReturnDetalleCuentaFromVisitaWithChange(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnDetalleCuentaFromVisitaWithChange();
	}

	public DataTable ToReturnDetalleCuentaFromVisitaID(int visitaID)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.ToReturnDetalleCuentaFromVisitaID();
	}

	public DataTable ToReturnDetalleCuentaFromVisitaIDparaBorrar1(int visitaID, string pagoMiPc)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.ToReturnDetalleCuentaFromVisitaIDparaBorrar(pagoMiPc);
	}

	public double ToReturnTotalDeudaCliente(int clienteID)
	{
		return clsDet.ToReturnTotalDeudaCliente(clienteID);
	}

	public DataTable ToReturnDetalleCuentaFromClienteWithChangeDeuda(int clienteID)
	{
		return clsDet.ToReturnDetalleCuentaFromClienteWithChangeDeuda(clienteID);
	}

	public DataTable ToReturnDetalleCuentaFromVisitaParaSepara(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnDetalleCuentaFromVisitaParaSepara();
	}

	public DataTable ToReturnDetalleCuentaFromVisita(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnDetalleFromVisita();
	}

	public DataTable ToReturnCuentaDeudaFaltanteFromVisita(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnDeudaFaltanteFromVisita();
	}

	public DataTable ToReturnCuentaDeudaFaltanteFromVisitaDelivery(int visitaId, bool tieneCombo)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCuentaDeudaFaltanteFromVisitaDelivery(tieneCombo);
	}

	public DataTable ToReturnCuentaDeudaFaltanteFromVisitaCuenta(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnDeudaFaltanteFromVisitaCuenta();
	}

	public DataTable ToReturnDeudaFaltanteFromVisitaParaAndroid(int visitaId, bool tieneCombo)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnDeudaFaltanteFromVisitaParaAndroid(tieneCombo);
	}

	public DataTable ToReturnPedidoCompleto(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnPedidoCompleto();
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionYconfiguracion(int visitaId, int config1, int codigoDocumentoSector)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCuentaTotalFromVisitaFacturacionYconfiguracion(config1, codigoDocumentoSector);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacion(int visitaId, int DocumentoSector)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCuentaTotalFromVisitaFacturacion(DocumentoSector);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionXML(int visitaId, bool desdeContingencia, bool paraXml, int DocumentoSector)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCuentaTotalFromVisitaFacturacionXML(desdeContingencia, paraXml, DocumentoSector);
	}

	public DataTable ToReturnCuentaTotalFromVisitaNotaCreditoXML(int visitaID, int debitoID)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.ToReturnCuentaTotalFromVisitaNotaCreditoXML(debitoID);
	}

	public void ToReturnCuentaTotalFromVisitaNotaCreditoXML(ref DataSet data, string DatNombre, int visitaID, int debitoID)
	{
		clsDet._VisitaID = visitaID;
		clsDet.ToReturnCuentaTotalFromVisitaNotaCreditoXML(ref data, DatNombre, debitoID);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionConBorrados(int visitaId, int DocumentoSector)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCuentaTotalFromVisitaFacturacionConBorrados(DocumentoSector);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionElectronica(int visitaId, int DocumentoSector)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCuentaTotalFromVisitaFacturacionElectronica(DocumentoSector);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionStigma(int visitaId, DateTime fecha)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCuentaTotalFromVisitaFacturacionStigma(fecha);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionStigma2(int visitaId, DateTime fecha, DateTime fechaIni)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnCuentaTotalFromVisitaFacturacionStigma2(fecha, fechaIni);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(int AgruparPagoID, int DocumentoSector)
	{
		return clsDet.ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoID(AgruparPagoID, DocumentoSector);
	}

	public DataTable ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoIDConBorrados(int AgruparPagoID, int DocumentoSector)
	{
		return clsDet.ToReturnCuentaTotalFromVisitaFacturacionPorAgruparPagoIDConBorrados(AgruparPagoID, DocumentoSector);
	}

	public double ReturnMontoTotalVisita(int visitaID)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.ReturnMontoTotalVisita();
	}

	public bool TieneDescuento(int visitaID)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.TieneDescuento() != 0.0;
	}

	public double ReturnMontoTotalVisitaYconfiguracion(int visitaID, int IdConfiguracion, int DocumentoSector)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.ReturnMontoTotalVisitaYconfiguracion(IdConfiguracion, DocumentoSector);
	}

	public double ReturnICEVisitaYconfiguracion1(int visitaID, int IdConfiguracion, int agruparPedidoID, int DocumentoSector)
	{
		clsDet._VisitaID = visitaID;
		return clsDet.ReturnICEVisitaYconfiguracion1(IdConfiguracion, agruparPedidoID, DocumentoSector);
	}

	public void ReturnCuentaTotalFromVisitaFacturacionGrande(ref DataSet data, string DatNombre, int visitaID, int documentoSector)
	{
		clsDet._VisitaID = visitaID;
		clsDet.ReturnCuentaTotalFromVisitaFacturacionGrande(ref data, DatNombre, documentoSector);
	}

	public void ReturnCuentaTotalFromVisitaFacturacionGrandeElectronica(ref DataSet data, string DatNombre, int visitaID, int documentoSector, int agrupadorID)
	{
		clsDet._VisitaID = visitaID;
		clsDet.ReturnCuentaTotalFromVisitaFacturacionGrandeElectronica(ref data, DatNombre, documentoSector, agrupadorID);
	}

	public void SetComentarios(string obs)
	{
		clsDet._Comentarios = obs;
	}

	public void Save1(ref double Cantidad, DateTime Hora, int VisitaID, int ProductoID, ref double PrecioUnit, ref double Pago, ref double Debe, bool Cerrada, int meseroID, bool manejarStock, string ProductosCombo, bool pedidoEspera, int cuentaID, int MetioPedidoMeseroID, string ObsCocina, int clienteID, bool esAnticipo, int transaccionId, int orden, int id_para_que_es_esto, int almacenID, bool ParaLLevar, int AgruparPagoID, string NroTarjeta)
	{
		double num = 0.0;
		if (almacenID == 0)
		{
			ctlProductos obj = new ctlProductos();
			obj.SetProductoID(ProductoID);
			clsProductos clsProductos2 = obj.LlenarClase();
			obj.SetTipoProductoID(clsProductos2._TipoProductoID.Value);
			obj.LlenarClaseTiposProductos();
			almacenID = obj.GetTipoProductoAlmacenID();
		}
		clsDet._Cantidad = Cantidad;
		clsDet._Hora = Hora;
		clsDet._VisitaID = VisitaID;
		clsDet._ProductoID = ProductoID;
		clsDet._Orden = orden;
		if (configuration.gFormatoFacturaGrande | configuration.gSoloFacturacionGrande)
		{
			clsDet._precioUnit = PrecioUnit;
			num = PrecioUnit * Cantidad;
			ctlProductos ctlProductos2 = new ctlProductos();
			ctlProductos2.SetProductoID(ProductoID);
			ctlProductos2.CargarPrecio();
			if (PrecioUnit > ctlProductos2.getPrecio())
			{
				clsDet._precioUnit = PrecioUnit;
			}
			else
			{
				clsDet._precioUnit = ctlProductos2.getPrecio();
			}
		}
		else
		{
			ctlProductos ctlProductos3 = new ctlProductos();
			ctlProductos3.SetProductoID(ProductoID);
			ctlProductos3.CargarPrecio();
			num = PrecioUnit * Cantidad;
			if (ParaLLevar | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Aerocruz))
			{
				clsDet._precioUnit = PrecioUnit;
			}
			else if (PrecioUnit > ctlProductos3.getPrecio())
			{
				clsDet._precioUnit = PrecioUnit;
			}
			else
			{
				clsDet._precioUnit = ctlProductos3.getPrecio();
			}
			if (ProductosCombo.Length > 0)
			{
				clsDet._precioUnit = PrecioUnit;
			}
		}
		clsDet._Pago = Pago;
		clsDet._Debe = Debe;
		clsDet._Cerrada = Cerrada;
		clsDet._meseroID = meseroID;
		clsDet._TomoPedidoMeseroID = MetioPedidoMeseroID;
		double Costo = 0.0;
		if (clsDet._ID != 0)
		{
			DevolverProds(ProductoID, Cantidad, ParaLLevar);
			clsPagos obj2 = new clsPagos();
			obj2._DetalleCuentaID = clsDet._ID;
			obj2.EliminarXDetalleCuentaID1("save 1, mejor borrarla y meterla de nuevo", meseroID);
			EliminarObservacion(clsDet._ID);
			clsDet.EliminarFisicamente();
			clsDet._ID = 0;
		}
		ctlProductos ctlProductos4 = new ctlProductos();
		ctlProductos4.SetProductoID(ProductoID);
		ctlProductos4.cargarDatos(almacenID);
		checked
		{
			if (ProductosCombo.Length > 0)
			{
				double num2 = 0.0;
				if (manejarStock && !pedidoEspera)
				{
					reducirStock(ctlProductos4, ParaLLevar, ref Cantidad, ref Pago, ref Debe, deCombo: false, telefono: false, ref Costo, 0, almacenID, desdeReporte: false, ProductosCombo.Length > 0);
					num2 = Costo;
				}
				string[] array = ProductosCombo.Split(',');
				clsDet._costo = ctlProductos4.getCosto() + num2;
				clsDet.Insertar();
				string[] array2 = array;
				foreach (string obj3 in array2)
				{
					ctlProductos ctlProductos5 = new ctlProductos();
					string[] array3 = obj3.ToString().Split('-');
					ctlProductos5.SetProductoID(Conversions.ToInteger(array3[0]));
					ctlProductos5.cargarDatos(almacenID);
					bool flag = Conversions.ToBoolean(array3[2]);
					ctlProductos5.SetTipoProductoID(ctlProductos5.getProductoTipoProductoID());
					ctlProductos5.LlenarClaseTiposProductos();
					if (ctlProductos5.GetTipoProductoManejaStock() != 0)
					{
						int num3 = ((!flag) ? ((int)Math.Round(Cantidad * -1.0)) : ((int)Math.Round(Cantidad)));
						double Cantidad2 = num3;
						double pago = 0.0;
						double debe = 0.0;
						reducirStock(ctlProductos5, ParaLLevar, ref Cantidad2, ref pago, ref debe, deCombo: true, telefono: false, ref Costo, 0, ctlProductos5.GetTipoProductoAlmacenID(), desdeReporte: false, soyCombo: false);
						num3 = (int)Math.Round(Cantidad2);
						if (Cantidad == 0.0)
						{
							Pago = 0.0;
							break;
						}
					}
				}
				if (Cantidad > 0.0)
				{
					clsDet.guardarCosto(Costo);
					string[] array4 = array;
					foreach (string text in array4)
					{
						if (clsDet._ID > 0)
						{
							string[] array5 = text.ToString().Split('-');
							int num4 = 0;
							double precioUnit = 0.0;
							num4 = ((Conversions.ToDouble(array5[2]) != 0.0) ? 1 : (-1));
							if (array5.Length > 2)
							{
								precioUnit = float.Parse(array5[3], CultureInfo.InvariantCulture);
							}
							new clsProductosCombos().guardar(Conversions.ToInteger(array5[0]), clsDet._ID, num4, precioUnit);
						}
					}
				}
				else
				{
					clsDet.EliminarFisicamente();
				}
			}
			else if (manejarStock)
			{
				if (!pedidoEspera)
				{
					reducirStock(ctlProductos4, ParaLLevar, ref Cantidad, ref Pago, ref Debe, deCombo: false, telefono: false, ref Costo, 0, almacenID, desdeReporte: false, ProductosCombo.Length > 0);
					clsDet.guardarCosto(Costo);
				}
				else
				{
					clsDet.Insertar();
				}
			}
			else if (ProductosCombo.Length == 0)
			{
				clsDet._costo = ctlProductos4.getCosto();
				clsDet._costo = ctlProductos4.getCosto() * Cantidad;
				clsDet.Insertar();
			}
			if (Pago > 0.0)
			{
				clsPagos clsPagos2 = new clsPagos();
				if (AgruparPagoID == 0)
				{
					AgruparPagoID = clsPagos2.getMaxAgruparPagoID();
				}
				if (esAnticipo)
				{
					double monto = Pago;
					double num5 = ctlAnt.devolverSaldo(clienteID);
					if (num5 >= Pago)
					{
						clsPagos obj4 = new clsPagos();
						obj4._Fecha = DateAndTime.Now;
						obj4._MaquinaPago = MyProject.Computer.Name;
						obj4._DetalleCuentaID = clsDet._ID;
						obj4._Descuento = num - Pago;
						obj4._cuentaID = 4;
						obj4._MontoBs = Pago;
						obj4._transaccionId = 0;
						obj4.Insertar(AgruparPagoID, meseroID);
						Pago = 0.0;
					}
					else
					{
						if (num5 > 0.0)
						{
							clsPagos obj5 = new clsPagos();
							obj5._Fecha = DateAndTime.Now;
							obj5._MaquinaPago = MyProject.Computer.Name;
							obj5._DetalleCuentaID = clsDet._ID;
							obj5._Descuento = 0.0;
							obj5._cuentaID = 4;
							obj5._MontoBs = num5;
							obj5._transaccionId = 0;
							obj5.Insertar(AgruparPagoID, meseroID);
							Pago -= num5;
						}
						num -= num5;
					}
					ReducirAnticipos(monto, clienteID);
				}
				if (Pago > 0.0)
				{
					clsPagos clsPagos3 = new clsPagos();
					clsPagos3._Fecha = DateAndTime.Now;
					clsPagos3._MaquinaPago = MyProject.Computer.Name;
					clsPagos3._DetalleCuentaID = clsDet._ID;
					clsPagos3._Descuento = num - Pago;
					if (cuentaID == 0)
					{
						clsPagos3._cuentaID = 1;
					}
					else
					{
						clsPagos3._cuentaID = cuentaID;
					}
					clsPagos3._MontoBs = Pago;
					clsPagos3._transaccionId = transaccionId;
					clsPagos3._nroTarjeta = NroTarjeta;
					if (clsPagos3.Insertar(AgruparPagoID, meseroID) <= 0)
					{
						PagoParcialCuenta(0.0, Pago, Cerrada: false, "");
					}
				}
			}
			if (Operators.CompareString(ObsCocina, "", TextCompare: false) != 0)
			{
				GuardarObservacion(ObsCocina, clsDet._ID);
			}
		}
	}

	public void ReducirAnticipos(double monto, int idcliente)
	{
		double monto2 = 0.0;
		int anticipoID = ctlAnt.DevolverMontoRestante(idcliente, ref monto2);
		if ((monto2 > 0.0) & (monto > 0.0))
		{
			if (monto2 >= monto)
			{
				ctlAnt.ReducirAnticipo(idcliente, monto);
				clsAntCuenta._AnticipoID = anticipoID;
				clsAntCuenta._DetalleCuentaID = clsDet._ID;
				clsAntCuenta._Monto = monto;
				clsAntCuenta.Insertar();
			}
			else
			{
				ctlAnt.ReducirAnticipo(idcliente, monto2);
				clsAntCuenta._AnticipoID = anticipoID;
				clsAntCuenta._DetalleCuentaID = clsDet._ID;
				clsAntCuenta._Monto = monto2;
				clsAntCuenta.Insertar();
				ReducirAnticipos(monto - monto2, idcliente);
			}
		}
	}

	public void guardarCosto(double CostoTotal)
	{
		clsDet.guardarCosto(CostoTotal);
	}

	public bool reducirStock(ctlProductos ctlProd, bool ParaLlevar, ref double Cantidad, ref double pago, ref double debe, bool deCombo, bool telefono, ref double Costo1, int ProduccionID, int almacenID, bool desdeReporte, bool soyCombo)
	{
		if (almacenID == 0)
		{
			DataTable dataTable = new ctlAlmacenes().DevolverTodosAlmacenesInternos(-1);
			if (dataTable.Rows.Count > 0)
			{
				almacenID = Conversions.ToInteger(dataTable.Rows[0][0]);
			}
		}
		int num = 0;
		string text = "";
		if (ProduccionID == 0)
		{
			DataTable dataTable2 = BD.ConsultaVer("ExtraEnMesaID,ExtraParaLlevarID", "Productos", "id=" + Conversions.ToString(ctlProd.GetProductoID()));
			if (ParaLlevar)
			{
				num = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["ExtraParaLlevarID"]), 0));
				if (num > 0)
				{
					ctlProductos ctlProductos2 = new ctlProductos();
					ctlProductos2.SetProductoID(num);
					if (ctlProductos2.llenarByID())
					{
						text = ((!ctlProductos2.tienePreparacion()) ? (ctlProductos2.getNombre() + " extra") : "");
					}
					else
					{
						if (!telefono)
						{
							Interaction.MsgBox(ctlProd.getNombre() + " no tiene bien seteada su extra para llevar");
						}
						text = "";
					}
				}
			}
			else
			{
				num = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable2.Rows[0]["ExtraEnMesaID"]), 0));
				if (num > 0)
				{
					ctlProductos ctlProductos3 = new ctlProductos();
					ctlProductos3.SetProductoID(num);
					if (ctlProductos3.llenarByID())
					{
						text = ((!ctlProductos3.tienePreparacion()) ? (ctlProductos3.getNombre() + " extra") : "");
					}
					else
					{
						if (!telefono)
						{
							Interaction.MsgBox(ctlProd.getNombre() + " no tiene bien seteada su extra en mesa");
						}
						text = "";
					}
				}
			}
		}
		checked
		{
			if (ctlProd.tienePreparacion() | (num > 0) | (ProduccionID > 0) | desdeReporte)
			{
				clsDet._costo = 0.0;
				ctlPreparacionesComodines ctlPreparacionesComodines2 = new ctlPreparacionesComodines();
				DataTable tbFinal = ctlPreparacionesComodines2.DevolverPreparacionesParaProducto(ctlProd.GetProductoID(), almacenID);
				tbFinal.Rows.Clear();
				if (ctlProd.tienePreparacion() | (ProduccionID > 0))
				{
					ctlPreparacionesComodines2.getProductsRecursive(ctlProd.GetProductoID(), ref tbFinal, Cantidad, telefono, almacenID, 0);
				}
				if (num > 0)
				{
					ctlPreparacionesComodines2.getProductsRecursive(num, ref tbFinal, Cantidad, telefono, almacenID, ctlProd.GetProductoID());
				}
				if (tbFinal.Rows.Count == 0)
				{
					pago = 0.0;
					debe = 0.0;
					if (!telefono)
					{
						Interaction.MsgBox("ERROR! este producto: " + ctlProd.getNombre() + " no tiene configurada sus preparaciones!");
					}
					BD.ConsultaModificar("Productos", "TienePreparacion = " + VariableGeneral.armarBolean(0), "TienePreparacion =" + VariableGeneral.armarBolean(1) + " and id not in (select ParaProductoID from Preparaciones)");
					return false;
				}
				bool flag = true;
				if (!telefono)
				{
					int num2 = tbFinal.Rows.Count - 1;
					for (int i = 0; i <= num2; i++)
					{
						if (Operators.ConditionalCompareObjectGreaterEqual(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(tbFinal.Rows[i]["enStock"]), 0), tbFinal.Rows[i]["Cantidad"], TextCompare: false) || configuration.gStyleBoliches1 != configuration.styleBolichesId.Sansha)
						{
							continue;
						}
						if (ProduccionID == 0)
						{
							if (Interaction.MsgBox(Operators.ConcatenateObject(Operators.ConcatenateObject("No hay suficiente ", tbFinal.Rows[i]["Concepto"]), " en stock, todavia quiere pedirlo?"), MsgBoxStyle.YesNo | MsgBoxStyle.DefaultButton2, " Problema en inventario ") != MsgBoxResult.Yes)
							{
								flag = false;
							}
						}
						else if (!telefono)
						{
							Interaction.MsgBox(Operators.ConcatenateObject(Operators.ConcatenateObject("No hay suficiente ", tbFinal.Rows[i]["Concepto"]), " en stock, Se reducira el stock"));
						}
					}
				}
				if (flag)
				{
					if (!deCombo && ((ProduccionID == 0) & !desdeReporte))
					{
						clsDet.Insertar();
					}
					int num3 = tbFinal.Rows.Count - 1;
					for (int j = 0; j <= num3; j++)
					{
						double num4 = Conversions.ToDouble(tbFinal.Rows[j]["Cantidad"]);
						if (Operators.ConditionalCompareObjectGreater(tbFinal.Rows[j]["PreparacionID"], 0, TextCompare: false))
						{
							DataTable dataTable3 = ctlPreparacionesComodines2.DevolverPreparacionesComodinesParaOrdenPedido(Conversions.ToInteger(tbFinal.Rows[j]["PreparacionID"]), almacenID);
							if (dataTable3.Rows.Count == 0)
							{
								if (!telefono)
								{
									Interaction.MsgBox("ERROR! esta preracion no esta bien configurada!");
								}
								new clsLogg().Insertar("Reducir Stock 2", Conversions.ToString(Operators.ConcatenateObject("Error en prep,preparacion ID: ", tbFinal.Rows[j]["PreparacionID"])), 1);
							}
							int num5 = dataTable3.Rows.Count - 1;
							for (int k = 0; k <= num5; k++)
							{
								clsDetalleCuenta clsDetalleCuenta2;
								if (k == dataTable3.Rows.Count - 1)
								{
									ctlProductos ctlProductos4 = new ctlProductos();
									ctlProductos4.SetProductoID(Conversions.ToInteger(dataTable3.Rows[k]["ProductoID"]));
									if (Operators.ConditionalCompareObjectEqual(dataTable3.Rows[k]["CantidadML"], 0, TextCompare: false))
									{
										clsDet._costo = 0.0;
										new clsLogg().Insertar("Reducir Stock", Conversions.ToString(Operators.ConcatenateObject("Error en Receta,Prod ID: ", dataTable3.Rows[k]["ProductoID"])), 1);
										continue;
									}
									double num6 = Conversions.ToDouble(Operators.DivideObject(num4, dataTable3.Rows[k]["CantidadML"]));
									if (!desdeReporte)
									{
										ctlProductos4.modificarStock(num6 * -1.0, clsDet._ID, ProduccionID, 0, almacenID);
									}
									(clsDetalleCuenta2 = clsDet)._costo = Conversions.ToDouble(Operators.AddObject(clsDetalleCuenta2._costo, Operators.MultiplyObject(num6, dataTable3.Rows[k]["Costo"])));
									Costo1 = Conversions.ToDouble(Operators.AddObject(Costo1, Operators.MultiplyObject(num6, dataTable3.Rows[k]["Costo"])));
									continue;
								}
								if (Operators.ConditionalCompareObjectEqual(dataTable3.Rows[k]["CantidadML"], 0, TextCompare: false))
								{
									if (!telefono)
									{
										Interaction.MsgBox(Operators.ConcatenateObject(Operators.ConcatenateObject("El ", dataTable3.Rows[k]["Nombre"]), " no tiene configurada su cantidad"));
									}
									return false;
								}
								if (!Operators.ConditionalCompareObjectGreater(dataTable3.Rows[k]["Stock"], 0, TextCompare: false))
								{
									continue;
								}
								double num7 = Conversions.ToDouble(Operators.MultiplyObject(dataTable3.Rows[k]["Stock"], dataTable3.Rows[k]["CantidadML"]));
								if (num7 >= num4)
								{
									if (Operators.ConditionalCompareObjectEqual(dataTable3.Rows[k]["CantidadML"], 0, TextCompare: false))
									{
										new clsLogg().Insertar("Reducir Stock", Conversions.ToString(Operators.ConcatenateObject("Error en Receta,Prod ID: ", dataTable3.Rows[k]["ProductoID"])), 1);
										clsDet._costo = 0.0;
									}
									else
									{
										double num8 = Conversions.ToDouble(Operators.DivideObject(num4, dataTable3.Rows[k]["CantidadML"]));
										ctlProductos ctlProductos5 = new ctlProductos();
										ctlProductos5.SetProductoID(Conversions.ToInteger(dataTable3.Rows[k]["ProductoID"]));
										if (!desdeReporte)
										{
											ctlProductos5.modificarStock(num8 * -1.0, clsDet._ID, ProduccionID, 0, almacenID);
										}
										(clsDetalleCuenta2 = clsDet)._costo = Conversions.ToDouble(Operators.AddObject(clsDetalleCuenta2._costo, Operators.MultiplyObject(num8, dataTable3.Rows[k]["Costo"])));
										Costo1 = Conversions.ToDouble(Operators.AddObject(Costo1, Operators.MultiplyObject(num8, dataTable3.Rows[k]["Costo"])));
									}
									k = dataTable3.Rows.Count + 2;
									break;
								}
								if (Operators.ConditionalCompareObjectEqual(dataTable3.Rows[k]["CantidadML"], 0, TextCompare: false))
								{
									clsDet._costo = 0.0;
									continue;
								}
								double num9 = Conversions.ToDouble(Operators.DivideObject(num7, dataTable3.Rows[k]["CantidadML"]));
								ctlProductos ctlProductos6 = new ctlProductos();
								ctlProductos6.SetProductoID(Conversions.ToInteger(dataTable3.Rows[k]["ProductoID"]));
								if (!desdeReporte)
								{
									ctlProductos6.modificarStock(num9 * -1.0, clsDet._ID, ProduccionID, 0, almacenID);
								}
								(clsDetalleCuenta2 = clsDet)._costo = Conversions.ToDouble(Operators.AddObject(clsDetalleCuenta2._costo, Operators.MultiplyObject(num9, dataTable3.Rows[k]["Costo"])));
								Costo1 = Conversions.ToDouble(Operators.AddObject(Costo1, Operators.MultiplyObject(num9, dataTable3.Rows[k]["Costo"])));
								num4 -= num7;
							}
						}
						else
						{
							if (!Operators.ConditionalCompareObjectEqual(text, tbFinal.Rows[j]["Concepto"], TextCompare: false))
							{
								continue;
							}
							ctlProductos ctlProductos7 = new ctlProductos();
							ctlProductos7.SetProductoID(num);
							if (ctlProductos7.llenarByID())
							{
								if (ctlProductos7.tienePreparacion())
								{
									continue;
								}
								double num10 = 0.0;
								num10 = ctlProductos7.getCantidadML();
								if (num10 == 0.0)
								{
									if (!telefono)
									{
										Interaction.MsgBox("el producto " + ctlProductos7.getNombre() + " no tiene seteada su cantidad de contenido");
									}
									num10 = 1.0;
								}
								double num11 = num4 / num10;
								if (!desdeReporte)
								{
									ctlProductos7.modificarStock(num11 * -1.0, clsDet._ID, ProduccionID, 0, almacenID);
								}
								clsDet._costo += num11 * ctlProductos7.getStock();
								Costo1 += num11 * ctlProductos7.getCosto();
							}
							else if (!telefono)
							{
								Interaction.MsgBox(text + " esta mal configurado");
							}
						}
					}
					if ((!ctlProd.tienePreparacion() & (ProduccionID == 0) & (num > 0)) && !disminuirProducto(ctlProd, Cantidad, ref Costo1, deCombo, ProduccionID, desdeReporte, almacenID, telefono))
					{
						return false;
					}
				}
				else
				{
					Cantidad = 0.0;
					pago = 0.0;
					debe = 0.0;
				}
			}
			else if (ProduccionID <= 0 && !soyCombo && !disminuirProducto(ctlProd, Cantidad, ref Costo1, deCombo, ProduccionID, desdeReporte, almacenID, telefono))
			{
				return false;
			}
			return true;
		}
	}

	private bool disminuirProducto(ctlProductos ctlProd, double Cantidad, ref double Costo1, bool deCombo, int ProduccionID, bool desdeReporte, int almacenID, bool telefono)
	{
		clsDet._costo = ctlProd.getCosto();
		Costo1 += ctlProd.getCosto() * Cantidad;
		if (ctlProd.getStock() >= Cantidad)
		{
			if (!deCombo && ProduccionID == 0)
			{
				clsDet.Insertar();
			}
			if (!desdeReporte)
			{
				ctlProd.modificarStock(Cantidad * -1.0, clsDet._ID, ProduccionID, 0, almacenID);
			}
		}
		else if (!telefono)
		{
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.LocosAsar)
			{
				if (!desdeReporte)
				{
					if (!deCombo && ProduccionID == 0)
					{
						clsDet.Insertar();
					}
					ctlProd.modificarStock(Cantidad * -1.0, clsDet._ID, ProduccionID, 0, almacenID);
				}
			}
			else if ((configuration.gComidaRapida | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Zucchini) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Ottimo)) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Mongaru))
			{
				if (!desdeReporte)
				{
					if (!deCombo && ProduccionID == 0)
					{
						clsDet.Insertar();
					}
					ctlProd.modificarStock(Cantidad * -1.0, clsDet._ID, ProduccionID, 0, almacenID);
				}
			}
			else
			{
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Sansha)
				{
					if (ProduccionID == 0)
					{
						if (Interaction.MsgBox("No hay suficiente en stock, todavia quiere pedirlo?", MsgBoxStyle.YesNo | MsgBoxStyle.DefaultButton2, " Problema en inventario ") != MsgBoxResult.Yes)
						{
							return false;
						}
					}
					else
					{
						Interaction.MsgBox("No hay suficiente en stock, Se reducira el stock");
					}
				}
				if (!desdeReporte)
				{
					if (!deCombo && ProduccionID == 0)
					{
						clsDet.Insertar();
					}
					ctlProd.modificarStock(Cantidad * -1.0, clsDet._ID, ProduccionID, 0, almacenID);
				}
			}
		}
		else if (!desdeReporte)
		{
			if (!deCombo && ProduccionID == 0)
			{
				clsDet.Insertar();
			}
			ctlProd.modificarStock(Cantidad * -1.0, clsDet._ID, ProduccionID, 0, almacenID);
		}
		return true;
	}

	public void SaveCelular(ref double Cantidad, DateTime Hora, int VisitaID, int ProductoID, ref double Precio, ref double Pago, ref double Debe, bool Cerrada, string Comentarios, string AsistenteID, bool manejarStock, int tomoPedidoID, string ProductosCombo, int almacenID, bool adicionaPrecioExtraCombos, int orden, bool ParaLLevar)
	{
		clsDet._Cantidad = Cantidad;
		clsDet._Hora = Hora;
		clsDet._VisitaID = VisitaID;
		clsDet._ProductoID = ProductoID;
		clsDet._precioUnit = Precio;
		clsDet._Pago = Pago;
		clsDet._Debe = Debe;
		clsDet._Cerrada = Cerrada;
		clsDet._Orden = orden;
		clsDet._Comentarios = Comentarios;
		clsDet._meseroID = tomoPedidoID;
		clsDet._TomoPedidoMeseroID = tomoPedidoID;
		if (clsDet._ID != 0)
		{
			clsDet.Modify();
		}
		ctlProductos ctlProductos2 = new ctlProductos();
		ctlProductos2.SetProductoID(ProductoID);
		ctlProductos2.cargarDatos(almacenID);
		double Costo = 0.0;
		checked
		{
			if (ProductosCombo.Length > 0)
			{
				string[] array = ProductosCombo.Split(',');
				double num = 0.0;
				string[] array2 = array;
				foreach (string obj in array2)
				{
					new ctlProductos();
					string[] array3 = obj.ToString().Split('-');
					if (array3[0].Length != 0 && adicionaPrecioExtraCombos)
					{
						DataTable dataTable = BD.ConsultaVer("Precio", "Preparaciones inner join PreparacionesComodines on PreparacionesComodines.PreparacionID=  Preparaciones.PreparacionID ", "ParaProductoID =" + Conversions.ToString(ProductoID) + " and DeProductoID =" + array3[0] + " and ModificaPrecio=" + VariableGeneral.armarBolean(1));
						if (dataTable.Rows.Count > 0 && Operators.ConditionalCompareObjectGreater(dataTable.Rows[0][0], num, TextCompare: false))
						{
							num = Conversions.ToDouble(dataTable.Rows[0][0]);
						}
					}
				}
				if (num > 0.0)
				{
					clsDet._precioUnit = num;
					if (clsDet._Pago > 0.0)
					{
						clsDet._Pago += num * Cantidad;
					}
					else
					{
						clsDet._Debe += num * Cantidad;
					}
				}
				string[] array4 = array;
				foreach (string obj2 in array4)
				{
					new ctlProductos();
					string[] array5 = obj2.ToString().Split('-');
					if (array5[0].Length == 0 || !adicionaPrecioExtraCombos)
					{
						continue;
					}
					DataTable dataTable2 = BD.ConsultaVer("Precio", "Preparaciones inner join PreparacionesComodines on PreparacionesComodines.PreparacionID=  Preparaciones.PreparacionID ", "ParaProductoID =" + Conversions.ToString(ProductoID) + " and DeProductoID =" + array5[0] + " and ModificaPrecio=" + VariableGeneral.armarBolean(0));
					if (dataTable2.Rows.Count > 0)
					{
						clsDetalleCuenta clsDetalleCuenta2;
						(clsDetalleCuenta2 = clsDet)._precioUnit = Conversions.ToDouble(Operators.AddObject(clsDetalleCuenta2._precioUnit, dataTable2.Rows[0][0]));
						if (clsDet._Pago > 0.0)
						{
							(clsDetalleCuenta2 = clsDet)._Pago = Conversions.ToDouble(Operators.AddObject(clsDetalleCuenta2._Pago, Operators.MultiplyObject(dataTable2.Rows[0][0], Cantidad)));
						}
						else
						{
							(clsDetalleCuenta2 = clsDet)._Debe = Conversions.ToDouble(Operators.AddObject(clsDetalleCuenta2._Debe, Operators.MultiplyObject(dataTable2.Rows[0][0], Cantidad)));
						}
					}
				}
				if (manejarStock)
				{
					reducirStock(ctlProductos2, ParaLLevar, ref Cantidad, ref Pago, ref Debe, deCombo: false, telefono: false, ref Costo, 0, almacenID, desdeReporte: false, ProductosCombo.Length > 0);
				}
				string[] array6 = array;
				foreach (string obj3 in array6)
				{
					ctlProductos ctlProductos3 = new ctlProductos();
					string[] array7 = obj3.ToString().Split('-');
					if (array7[0].Length == 0)
					{
						continue;
					}
					ctlProductos3.SetProductoID(Conversions.ToInteger(array7[0]));
					ctlProductos3.cargarDatos(almacenID);
					bool flag = manejarStock;
					ctlProductos3.SetTipoProductoID(ctlProductos3.getProductoTipoProductoID());
					ctlProductos3.LlenarClaseTiposProductos();
					if (ctlProductos3.GetTipoProductoManejaStock() != 0)
					{
						int num2 = ((!flag) ? ((int)Math.Round(Cantidad * -1.0)) : ((int)Math.Round(Cantidad)));
						double Cantidad2 = num2;
						double pago = 0.0;
						double debe = 0.0;
						reducirStock(ctlProductos3, ParaLLevar, ref Cantidad2, ref pago, ref debe, deCombo: true, telefono: false, ref Costo, 0, ctlProductos3.GetTipoProductoAlmacenID(), desdeReporte: false, soyCombo: false);
						num2 = (int)Math.Round(Cantidad2);
						if (Cantidad == 0.0)
						{
							Pago = 0.0;
							break;
						}
					}
				}
				if (clsDet._costo == 0.0)
				{
					clsDet._costo = ctlProductos2.getCosto();
				}
				clsDet.Insertar();
				if (Cantidad > 0.0)
				{
					clsDet.guardarCosto(Costo);
					string[] array8 = array;
					foreach (string text in array8)
					{
						if (clsDet._ID <= 0)
						{
							continue;
						}
						string[] array9 = text.ToString().Split('-');
						if (array9[0].Length <= 0)
						{
							continue;
						}
						int cant = 1;
						double precioUnit = 0.0;
						if (array9.Length >= 2)
						{
							precioUnit = float.Parse(array9[2], CultureInfo.InvariantCulture);
						}
						else
						{
							ctlProductos obj4 = new ctlProductos();
							obj4.SetProductoID(Conversions.ToInteger(array9[0]));
							obj4.cargarDatos(almacenID);
							DataTable dataTable3 = BD.ConsultaVer("Precio", "Preparaciones inner join PreparacionesComodines on PreparacionesComodines.PreparacionID=  Preparaciones.PreparacionID ", "ParaProductoID =" + Conversions.ToString(ProductoID) + " and DeProductoID =" + array9[0]);
							if (dataTable3.Rows.Count > 0)
							{
								precioUnit = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable3.Rows[0][0]), 0));
							}
						}
						new clsProductosCombos().guardar(Conversions.ToInteger(array9[0]), clsDet._ID, cant, precioUnit);
					}
				}
				else
				{
					clsDet.EliminarFisicamente();
				}
			}
			else if (manejarStock)
			{
				double Costo2 = 0.0;
				reducirStock(ctlProductos2, ParaLLevar, ref Cantidad, ref Pago, ref Debe, deCombo: false, telefono: true, ref Costo2, 0, almacenID, desdeReporte: false, soyCombo: false);
				if (clsDet._ID == 0)
				{
					clsDet._costo = ctlProductos2.getCosto();
					clsDet.Insertar();
				}
				else
				{
					clsDet.guardarCosto(Costo2);
				}
			}
			else if (ProductosCombo.Length == 0)
			{
				clsDet._costo = ctlProductos2.getCosto() - Cantidad;
				clsDet.Insertar();
			}
			else
			{
				clsDet.guardarCosto(Costo);
			}
			if (AsistenteID.Length > 0 && Operators.CompareString(AsistenteID, "0", TextCompare: false) != 0)
			{
				string[] array10 = AsistenteID.Split(',');
				if (array10.Length > 0)
				{
					string[] array11 = array10;
					foreach (string text2 in array11)
					{
						if (Conversions.ToDouble(text2) > 0.0)
						{
							string data = text2 + "," + Conversions.ToString(clsDet._ID) + ",'" + Conversion.Str(ctlProductos2.getComision() / (double)array10.Length) + "'";
							int id = 0;
							BD.ConsultaInsertar3(data, "DetalleCuenta_Asistentes(AsistenteID, DetalleCuentaID, Comision)", ref id);
						}
					}
				}
			}
			GuardarObservacion(Comentarios, clsDet._ID);
		}
	}

	public double getPago()
	{
		return clsDet._Pago;
	}

	public double getDebe()
	{
		return clsDet._Debe;
	}

	public void recalcularElServicio(int visitaId, bool con10porcent, [Optional][DefaultParameterValue(0.0)] ref double montoDevuelto, [Optional][DefaultParameterValue(0.0)] ref double CuentaID)
	{
		if (configuration.gManejaElServicio)
		{
			if (visitaId > 0)
			{
				clsDet._VisitaID = visitaId;
				clsDet._ProductoID = 1;
				clsDetalleCuenta obj = clsDet;
				int CuentaId = checked((int)Math.Round(CuentaID));
				obj.modificarServicio(con10porcent, ref montoDevuelto, ref CuentaId);
				CuentaID = CuentaId;
			}
			recalcularHoraKaraoke(visitaId);
		}
	}

	public void recalcularHoraKaraoke(int visitaId)
	{
		if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Dubai && visitaId > 0)
		{
			clsDet._VisitaID = visitaId;
			clsDet._ProductoID = 2;
			clsDet.modificarKaraoke();
		}
	}

	public int SemaforoVentas(ref double dif)
	{
		int result;
		try
		{
			clsTurnos obj = new clsTurnos();
			double montoInibs = 0.0;
			double montoInidol = 0.0;
			int RespArqueo = 0;
			DateTime FechaIni = default(DateTime);
			obj.getInfoTurnoActual(ref FechaIni, ref montoInibs, ref montoInidol, ref RespArqueo);
			if (DateTime.Compare(FechaIni, DateTime.MinValue) == 0)
			{
				result = 0;
			}
			else
			{
				DataTable dataTable = BD.ConsultaVer("sum(Pago+Debe)", "DetalleCuenta", "Hora between " + VariableGeneral.ArmarFecha(FechaIni.AddDays(-7.0)) + " and " + VariableGeneral.ArmarFecha(DateAndTime.Now.AddDays(-7.0)));
				double num = Conversions.ToDouble(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
				{
					VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0),
					0
				}, null, null, null));
				double num2 = Conversions.ToDouble(NewLateBinding.LateGet(null, typeof(Math), "Round", new object[2]
				{
					VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("sum(Pago+Debe)", "DetalleCuenta", "Hora between " + VariableGeneral.ArmarFecha(FechaIni) + " and " + VariableGeneral.ArmarFecha(DateAndTime.Now)).Rows[0][0]), 0),
					0
				}, null, null, null));
				dif = num2 - num;
				result = ((num > num2) ? (-1) : ((num != num2) ? 1 : 0));
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

	public DataTable DevolverObservacionesXvisitaID(int visitaID)
	{
		return BD.ConsultaVer("detalleCuenta.ProductoID, Observaciones.Observacion", "Observaciones inner join detalleCuenta on detalleCuenta.Id=Observaciones.DetalleCuentaID", "detalleCuenta.visitaId=" + Conversions.ToString(visitaID), "ObservacionId desc");
	}

	public string getCombosYobservaciones(int DetalleCuentaID)
	{
		clsObs._DetalleCuentaID = DetalleCuentaID;
		DataTable dataTable = BD.ConsultaVer("Productos.Nombre, ProductosCombos.Cant", "ProductosCombos INNER JOIN Productos ON ProductosCombos.ProductoID = Productos.ID", "ProductosCombos.DetalleCuentaID =" + Conversions.ToString(DetalleCuentaID));
		string text = "";
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				text = Conversions.ToString(Operators.ConcatenateObject(text, Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(dataTable.Rows[i]["Cant"], " "), dataTable.Rows[i]["Nombre"]), "\r")));
			}
			return text + clsObs.DevolverObservaciones();
		}
	}

	private void GuardarObservacion(string Observacion, int DetalleCuentaID)
	{
		clsObs._Observacion = Observacion;
		clsObs._DetalleCuentaID = DetalleCuentaID;
		clsObs.Insertar();
	}

	private void EliminarObservacion(int DetalleCuentaID)
	{
		clsObs._DetalleCuentaID = DetalleCuentaID;
		clsObs.EliminarXdetalleID();
	}

	public int AplicarDescuento(int idVisita, double desc)
	{
		clsDet._VisitaID = idVisita;
		return clsDet.AplicarDescuento(desc);
	}

	public void DevolverDataSetNotaPedidos(ref DataSet data, string DatNombre, int VisitaID)
	{
		clsDet._VisitaID = VisitaID;
		clsDet.DevolverDataSetNotaPedidos(ref data, DatNombre);
	}

	public DataTable ToReturnPagoCuentas(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnPagoCuentas();
	}

	public DataTable devolverPagosXClienteID(int ClienteID)
	{
		return new clsPagos().devolverPagosXCliente(ClienteID);
	}

	public DataTable ToReturnCuentaDeudaTotalFromVisita(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.ToReturnDeudaTotalFromVisita();
	}

	public DataTable DevolverCuentaCliente(int clienteID)
	{
		return clsDet.DevolverCuentaClientes(clienteID);
	}

	public DataTable DevolverReporteDeudasClientes()
	{
		return clsDet.DevolverReporteDeudasCLientes();
	}

	public void DevolverDataSetNotaEntrega(ref DataSet data, string DatNombre, int VisitaID)
	{
		clsDet._ID = VisitaID;
		clsDet.DevolverDataSetNotaEntrega(ref data, DatNombre);
	}

	public void DevolverDataSetNotaPago(ref DataSet data, string DatNombre, int agrupadorID)
	{
		clsDet.DevolverDataSetNotapagos(ref data, DatNombre, agrupadorID);
	}

	public void ActualizarDevolucion(int id, double cant, double debe)
	{
		clsDet._ID = id;
		clsDet._Cantidad = cant;
		clsDet._Debe = debe;
		clsDet.ActualizarDevolucion();
	}

	public DataTable DevolverReporteGrupal(DateTime dtpDesdeDate, DateTime dtpHastaDate)
	{
		return clsDet.DevolverReporteGrupal(dtpDesdeDate, dtpHastaDate);
	}

	public DataTable DevolverReporteKikyTipoEntrega(DateTime dtpDesdeDate, DateTime dtpHastaDate)
	{
		return clsDet.DevolverReporteKikyTipoEntrega(dtpDesdeDate, dtpHastaDate);
	}

	public DataTable DevolverFacturasConTarjeta(DateTime dtpDesdeDate, DateTime dtpHastaDate, string PC)
	{
		return clsDet.DevolverFacturasConTarjeta(dtpDesdeDate, dtpHastaDate, PC);
	}

	public DataTable DevolverPedidoTotal(int visitaId)
	{
		clsDet._VisitaID = visitaId;
		return clsDet.DevolverPedidoTotal();
	}

	public DataTable DevolverDetalleSacNet(int visitaID, int facturaID)
	{
		return clsDet.DevolverDetalleSacNet(visitaID, facturaID);
	}

	public DataTable DevolverTipoPago(int visitaid)
	{
		clsDet._VisitaID = visitaid;
		return clsDet.DevolverTipoPago();
	}

	public DataTable DevolverVentasTotales(int idFamilia, int mes, int anho)
	{
		return clsDet.DevolverVentasTotales(idFamilia, mes, anho);
	}
}
