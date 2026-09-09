using System;
using System.Data;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.Windows.Forms;
using ControlConsumoLib.FactElectSync;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFacElecSyncDatos
{
	private readonly string _EndPointURLSincronizacion;

	public clsFacElecSyncDatos(int Ambiente)
	{
		_EndPointURLSincronizacion = null;
		switch (Ambiente)
		{
		case 2:
			_EndPointURLSincronizacion = "https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion";
			break;
		case 0:
			_EndPointURLSincronizacion = null;
			break;
		default:
			_EndPointURLSincronizacion = "https://siatrest.impuestos.gob.bo/v2/FacturacionSincronizacion";
			break;
		}
	}

	public bool verificarComunicacion()
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					result = servicioFacturacionSincronizacionClient.verificarComunicacion().transaccion;
				}
			}
			else
			{
				result = false;
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

	public int checkFechaHora(ref string error1)
	{
		int result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					cuis = codigoCUIS
				};
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					DateTime now = DateAndTime.Now;
					respuestaFechaHora respuestaFechaHora2 = servicioFacturacionSincronizacionClient.sincronizarFechaHora(solicitudSincronizacion2);
					if (respuestaFechaHora2.transaccion)
					{
						DateTime fecha = Conversions.ToDate(respuestaFechaHora2.fechaHora);
						if ((DateTime.Compare(fecha.Date, now.Date) == 0) & (fecha.Hour == now.Hour) & (fecha.Minute == now.Minute))
						{
							result = 1;
						}
						else
						{
							error1 = "PC " + VariableGeneral.ArmarFechaSTR(now) + " vs SIAT " + VariableGeneral.ArmarFechaSTR(fecha);
							result = 0;
						}
					}
					else
					{
						if (respuestaFechaHora2.mensajesList.Length > 0)
						{
							if (respuestaFechaHora2.mensajesList[0].codigo == 913)
							{
								new clsCUIS().deshabilitarCUISDvigente();
							}
							else
							{
								Interaction.MsgBox(respuestaFechaHora2.mensajesList[0].descripcion);
							}
						}
						error1 = "Error en transaccion";
						result = -1;
					}
				}
			}
			else
			{
				error1 = "Sin token";
				result = -1;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			error1 = ex2.Message;
			result = -1;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool SyncCodigoProductos(bool esTest)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					cuis = codigoCUIS
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaListaProductos respuestaListaProductos2 = servicioFacturacionSincronizacionClient.sincronizarListaProductosServicios(solicitudSincronizacion2);
						if (respuestaListaProductos2.transaccion)
						{
							if (!esTest)
							{
								ctlFactElectProductosServicios ctlFactElectProductosServicios2 = new ctlFactElectProductosServicios();
								ctlFactElectProductosServicios2.EliminarFactElectProductosServicios();
								productosDto[] listaCodigos = respuestaListaProductos2.listaCodigos;
								string text = "";
								int num = listaCodigos.Length - 1;
								for (int i = 0; i <= num; i++)
								{
									text = text + listaCodigos[i].codigoActividad + "   " + Conversions.ToString(listaCodigos[i].codigoProducto) + "\t" + listaCodigos[i].descripcionProducto + "\r\n";
									ctlFactElectProductosServicios2.GuardarFactElectProductosServicios1(Conversions.ToString(listaCodigos[i].codigoProducto), listaCodigos[i].descripcionProducto, listaCodigos[i].codigoActividad);
								}
							}
							result = true;
						}
						else
						{
							result = false;
						}
					}
				}
			}
			else
			{
				result = false;
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

	public bool SyncLeyes(bool esTest)
	{
		checked
		{
			bool result;
			try
			{
				DataTable dataTable = BD.ConsultaVer("distinct ConfiguracionID", "FactElectConfiguracion", "1=1", "ConfiguracionID");
				int gConfiguracionID = VariableGeneral.gConfiguracionID;
				ctlFactElectLeyes ctlFactElectLeyes2 = new ctlFactElectLeyes();
				bool flag = false;
				int num = dataTable.Rows.Count - 1;
				int num2 = default(int);
				for (int i = 0; i <= num; i++)
				{
					VariableGeneral.gConfiguracionID = Conversions.ToInteger(dataTable.Rows[i][0]);
					clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
					if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
					{
						string codigoCUIS = "";
						ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient;
						solicitudSincronizacion solicitudSincronizacion2;
						unchecked
						{
							new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
							servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
							servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
							solicitudSincronizacion2 = new solicitudSincronizacion
							{
								codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
								codigoSistema = clsFactElecConfig2.CodigoSistema,
								codigoSucursal = clsFactElecConfig2.codigoSucursal,
								nit = clsFactElecConfig2.NIT,
								codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
								codigoPuntoVentaSpecified = true,
								cuis = codigoCUIS
							};
						}
						using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
						{
							HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
							httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
							OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
							respuestaListaParametricasLeyendas respuestaListaParametricasLeyendas2 = servicioFacturacionSincronizacionClient.sincronizarListaLeyendasFactura(solicitudSincronizacion2);
							if (respuestaListaParametricasLeyendas2.transaccion)
							{
								if (i == 0)
								{
									ctlFactElectLeyes2.EliminarFactElectLeyes();
									num2 = ctlFactElectLeyes2.getMAxId();
								}
								if (!esTest)
								{
									parametricaLeyendasDto[] listaLeyendas = respuestaListaParametricasLeyendas2.listaLeyendas;
									int num3 = listaLeyendas.Length - 1;
									for (int j = 0; j <= num3; j++)
									{
										num2++;
										ctlFactElectLeyes2.GuardarFactElectLeyes1(num2, listaLeyendas[j].codigoActividad, listaLeyendas[j].descripcionLeyenda);
									}
								}
							}
							else
							{
								flag = true;
							}
						}
					}
					else
					{
						flag = true;
					}
				}
				VariableGeneral.gConfiguracionID = gConfiguracionID;
				result = !flag;
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
	}

	public bool SyncActividades(bool esTest)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					cuis = codigoCUIS
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaListaActividades respuestaListaActividades2 = servicioFacturacionSincronizacionClient.sincronizarActividades(solicitudSincronizacion2);
						if (respuestaListaActividades2.transaccion)
						{
							if (!esTest)
							{
								actividadesDto[] listaActividades = respuestaListaActividades2.listaActividades;
								ctlFactElectSyncActividades ctlFactElectSyncActividades2 = new ctlFactElectSyncActividades();
								ctlFactElectSyncActividades2.EliminarFactElectActividades();
								string text = "";
								int num = listaActividades.Length - 1;
								for (int i = 0; i <= num; i++)
								{
									text = text + listaActividades[i].codigoCaeb + "\t" + listaActividades[i].descripcion + "\t" + listaActividades[i].tipoActividad + "\r\n";
									ctlFactElectSyncActividades2.GuardarFactElectActividades1(listaActividades[i].codigoCaeb, listaActividades[i].descripcion, listaActividades[i].tipoActividad);
								}
								Clipboard.SetText(text);
							}
							result = true;
						}
						else
						{
							result = false;
						}
					}
				}
			}
			else
			{
				result = false;
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

	public bool SyncEventos()
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					cuis = codigoCUIS
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaEventosSignificativos(solicitudSincronizacion2);
						if (respuestaListaParametricas2.transaccion)
						{
							parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
							string text = "";
							int num = listaCodigos.Length - 1;
							for (int i = 0; i <= num; i++)
							{
								text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\t\r\n";
							}
							Clipboard.SetText(text);
							result = true;
						}
						else
						{
							result = false;
						}
					}
				}
			}
			else
			{
				result = false;
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

	public bool SyncActvividadDocumentoSectores()
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					cuis = codigoCUIS
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaListaActividadesDocumentoSector respuestaListaActividadesDocumentoSector2 = servicioFacturacionSincronizacionClient.sincronizarListaActividadesDocumentoSector(solicitudSincronizacion2);
						if (respuestaListaActividadesDocumentoSector2.transaccion)
						{
							actividadesDocumentoSectorDto[] listaActividadesDocumentoSector = respuestaListaActividadesDocumentoSector2.listaActividadesDocumentoSector;
							string text = "";
							text += "codigoActividad\tcodigoDocumentoSector\ttipoDocumentoSector\r\n";
							int num = listaActividadesDocumentoSector.Length - 1;
							for (int i = 0; i <= num; i++)
							{
								text = text + listaActividadesDocumentoSector[i].codigoActividad + "\t" + Conversions.ToString(listaActividadesDocumentoSector[i].codigoDocumentoSector) + "\t" + listaActividadesDocumentoSector[i].tipoDocumentoSector + "\r\n";
							}
							Clipboard.SetText(text);
							result = true;
						}
						else
						{
							result = false;
						}
					}
				}
			}
			else
			{
				result = false;
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

	public bool SyncTipoDocumentoSector()
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					cuis = codigoCUIS
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaTipoDocumentoSector(solicitudSincronizacion2);
						if (respuestaListaParametricas2.transaccion)
						{
							parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
							string text = "";
							int num = listaCodigos.Length - 1;
							for (int i = 0; i <= num; i++)
							{
								text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
							}
							Clipboard.SetText(text);
							result = true;
						}
						else
						{
							result = false;
						}
					}
				}
			}
			else
			{
				result = false;
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

	public bool SyncUnidadMedida(bool esTest)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					cuis = codigoCUIS
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaUnidadMedida(solicitudSincronizacion2);
						if (respuestaListaParametricas2.transaccion)
						{
							if (!esTest)
							{
								ctlFactElectUnidadesMedidas ctlFactElectUnidadesMedidas2 = new ctlFactElectUnidadesMedidas();
								ctlFactElectUnidadesMedidas2.EliminarFactElectUnidadesMedidas();
								parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
								string text = "";
								text += "Codigo Descripcion\r\n";
								int num = listaCodigos.Length - 1;
								for (int i = 0; i <= num; i++)
								{
									ctlFactElectUnidadesMedidas2.GuardarFactElectUnidadesMedidas1(listaCodigos[i].codigoClasificador, listaCodigos[i].descripcion);
									text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
								}
								ctlFactElectUnidadesMedidas2.ordernarMasUsadas();
							}
							result = true;
						}
						else
						{
							result = false;
						}
					}
				}
			}
			else
			{
				result = false;
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

	public bool SyncMensajesServicio()
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					cuis = codigoCUIS
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarListaMensajesServicios(solicitudSincronizacion2);
						if (respuestaListaParametricas2.transaccion)
						{
							parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
							string text = "";
							int num = listaCodigos.Length - 1;
							for (int i = 0; i <= num; i++)
							{
								text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
							}
							Clipboard.SetText(text);
							result = true;
						}
						else
						{
							result = false;
						}
					}
				}
			}
			else
			{
				result = false;
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

	public bool SyncMotivosAnulacion()
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
		{
			string codigoCUIS = "";
			new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
			servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
			solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
			{
				codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
				codigoSistema = clsFactElecConfig2.CodigoSistema,
				codigoSucursal = clsFactElecConfig2.codigoSucursal,
				nit = clsFactElecConfig2.NIT,
				codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
				codigoPuntoVentaSpecified = true,
				cuis = codigoCUIS
			};
			checked
			{
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaMotivoAnulacion(solicitudSincronizacion2);
					if (respuestaListaParametricas2.transaccion)
					{
						parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
						string text = "";
						int num = listaCodigos.Length - 1;
						for (int i = 0; i <= num; i++)
						{
							text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
						}
						Clipboard.SetText(text);
						return true;
					}
					return false;
				}
			}
		}
		return false;
	}

	public bool SyncPaises()
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
		{
			string codigoCUIS = "";
			new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
			servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
			solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
			{
				codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
				codigoSistema = clsFactElecConfig2.CodigoSistema,
				codigoSucursal = clsFactElecConfig2.codigoSucursal,
				nit = clsFactElecConfig2.NIT,
				codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
				codigoPuntoVentaSpecified = true,
				cuis = codigoCUIS
			};
			checked
			{
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaPaisOrigen(solicitudSincronizacion2);
					if (respuestaListaParametricas2.transaccion)
					{
						parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
						string text = "";
						int num = listaCodigos.Length - 1;
						for (int i = 0; i <= num; i++)
						{
							text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
						}
						Clipboard.SetText(text);
						return true;
					}
					return false;
				}
			}
		}
		return false;
	}

	public bool SyncTiposDocumentosIdentidad()
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
		{
			string codigoCUIS = "";
			new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
			servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
			solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
			{
				codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
				codigoSistema = clsFactElecConfig2.CodigoSistema,
				codigoSucursal = clsFactElecConfig2.codigoSucursal,
				nit = clsFactElecConfig2.NIT,
				codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
				codigoPuntoVentaSpecified = true,
				cuis = codigoCUIS
			};
			checked
			{
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaTipoDocumentoIdentidad(solicitudSincronizacion2);
					if (respuestaListaParametricas2.transaccion)
					{
						parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
						string text = "";
						int num = listaCodigos.Length - 1;
						for (int i = 0; i <= num; i++)
						{
							text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
						}
						Clipboard.SetText(text);
						return true;
					}
					return false;
				}
			}
		}
		return false;
	}

	public bool SyncTiposEmision()
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
		{
			string codigoCUIS = "";
			new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
			servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
			solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
			{
				codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
				codigoSistema = clsFactElecConfig2.CodigoSistema,
				codigoSucursal = clsFactElecConfig2.codigoSucursal,
				nit = clsFactElecConfig2.NIT,
				codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
				codigoPuntoVentaSpecified = true,
				cuis = codigoCUIS
			};
			checked
			{
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaTipoEmision(solicitudSincronizacion2);
					if (respuestaListaParametricas2.transaccion)
					{
						parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
						string text = "";
						int num = listaCodigos.Length - 1;
						for (int i = 0; i <= num; i++)
						{
							text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
						}
						Clipboard.SetText(text);
						return true;
					}
					return false;
				}
			}
		}
		return false;
	}

	public bool SyncTipoHabitacion()
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
		{
			string codigoCUIS = "";
			new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
			servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
			solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
			{
				codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
				codigoSistema = clsFactElecConfig2.CodigoSistema,
				codigoSucursal = clsFactElecConfig2.codigoSucursal,
				nit = clsFactElecConfig2.NIT,
				codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
				codigoPuntoVentaSpecified = true,
				cuis = codigoCUIS
			};
			checked
			{
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaTipoHabitacion(solicitudSincronizacion2);
					if (respuestaListaParametricas2.transaccion)
					{
						parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
						string text = "";
						int num = listaCodigos.Length - 1;
						for (int i = 0; i <= num; i++)
						{
							text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
						}
						Clipboard.SetText(text);
						return true;
					}
					return false;
				}
			}
		}
		return false;
	}

	public bool SyncMetodoPago(ref DataTable dtMetodos, bool esTest)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
				servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
				solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					cuis = codigoCUIS
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaTipoMetodoPago(solicitudSincronizacion2);
						if (respuestaListaParametricas2.transaccion)
						{
							parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
							string text = "";
							int num = listaCodigos.Length - 1;
							for (int i = 0; i <= num; i++)
							{
								text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
								if (!esTest)
								{
									DataRow dataRow = dtMetodos.NewRow();
									dataRow[0] = listaCodigos[i].codigoClasificador;
									dataRow[1] = listaCodigos[i].descripcion;
									dtMetodos.Rows.Add(dataRow);
								}
							}
							result = true;
						}
						else
						{
							result = false;
						}
					}
				}
			}
			else
			{
				result = false;
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

	public bool SyncTipoMoneda()
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
		{
			string codigoCUIS = "";
			new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
			servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
			solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
			{
				codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
				codigoSistema = clsFactElecConfig2.CodigoSistema,
				codigoSucursal = clsFactElecConfig2.codigoSucursal,
				nit = clsFactElecConfig2.NIT,
				codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
				codigoPuntoVentaSpecified = true,
				cuis = codigoCUIS
			};
			checked
			{
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaTipoMoneda(solicitudSincronizacion2);
					if (respuestaListaParametricas2.transaccion)
					{
						parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
						string text = "";
						int num = listaCodigos.Length - 1;
						for (int i = 0; i <= num; i++)
						{
							text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
						}
						Clipboard.SetText(text);
						return true;
					}
					return false;
				}
			}
		}
		return false;
	}

	public bool SyncTipoPuntoVenta()
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
		{
			string codigoCUIS = "";
			new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
			servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
			solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
			{
				codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
				codigoSistema = clsFactElecConfig2.CodigoSistema,
				codigoSucursal = clsFactElecConfig2.codigoSucursal,
				nit = clsFactElecConfig2.NIT,
				codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
				codigoPuntoVentaSpecified = true,
				cuis = codigoCUIS
			};
			checked
			{
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaTipoPuntoVenta(solicitudSincronizacion2);
					if (respuestaListaParametricas2.transaccion)
					{
						parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
						string text = "";
						int num = listaCodigos.Length - 1;
						for (int i = 0; i <= num; i++)
						{
							text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
						}
						Clipboard.SetText(text);
						return true;
					}
					return false;
				}
			}
		}
		return false;
	}

	public bool SyncTiposFactura()
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
		{
			string codigoCUIS = "";
			new clsFactElecObtencionCodigos((int)clsFactElecConfig2.CodigoAmbiente).ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
			ServicioFacturacionSincronizacionClient servicioFacturacionSincronizacionClient = new ServicioFacturacionSincronizacionClient();
			servicioFacturacionSincronizacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLSincronizacion);
			solicitudSincronizacion solicitudSincronizacion2 = new solicitudSincronizacion
			{
				codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
				codigoSistema = clsFactElecConfig2.CodigoSistema,
				codigoSucursal = clsFactElecConfig2.codigoSucursal,
				nit = clsFactElecConfig2.NIT,
				codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
				codigoPuntoVentaSpecified = true,
				cuis = codigoCUIS
			};
			checked
			{
				using (new OperationContextScope(servicioFacturacionSincronizacionClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					respuestaListaParametricas respuestaListaParametricas2 = servicioFacturacionSincronizacionClient.sincronizarParametricaTiposFactura(solicitudSincronizacion2);
					if (respuestaListaParametricas2.transaccion)
					{
						parametricasDto[] listaCodigos = respuestaListaParametricas2.listaCodigos;
						string text = "";
						int num = listaCodigos.Length - 1;
						for (int i = 0; i <= num; i++)
						{
							text = text + Conversions.ToString(listaCodigos[i].codigoClasificador) + "\t" + listaCodigos[i].descripcion + "\r\n";
						}
						Clipboard.SetText(text);
						return true;
					}
					return false;
				}
			}
		}
		return false;
	}
}
