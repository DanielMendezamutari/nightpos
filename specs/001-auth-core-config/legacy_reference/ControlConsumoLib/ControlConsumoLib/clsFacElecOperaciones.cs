using System;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.Threading.Tasks;
using ControlConsumoLib.FactElectOperaciones;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFacElecOperaciones
{
	private readonly string _EndPointURLOperaciones;

	public clsFacElecOperaciones(int Ambiente)
	{
		_EndPointURLOperaciones = null;
		switch (Ambiente)
		{
		case 2:
			_EndPointURLOperaciones = "https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionOperaciones";
			break;
		case 0:
			_EndPointURLOperaciones = null;
			break;
		default:
			_EndPointURLOperaciones = "https://siatrest.impuestos.gob.bo/v2/FacturacionOperaciones";
			break;
		}
	}

	public bool verificarComunicacion()
	{
		bool result;
		try
		{
			ServicioFacturacionOperacionesClient servicioFacturacionOperacionesClient = new ServicioFacturacionOperacionesClient();
			servicioFacturacionOperacionesClient.Endpoint.Address = new EndpointAddress(_EndPointURLOperaciones);
			result = servicioFacturacionOperacionesClient.verificarComunicacion().transaccion;
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

	public bool RegistroEventoSignificativo(ref string CodigoEventoRespuesta, int codigoMotivo, string CUIS, string CUFD, string CUFDevento, DateTime inicio, DateTime fin, string descripcion, int FactElectFueraLineaID)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionOperacionesClient servicioFacturacionOperacionesClient = new ServicioFacturacionOperacionesClient();
				servicioFacturacionOperacionesClient.Endpoint.Address = new EndpointAddress(_EndPointURLOperaciones);
				solicitudEventoSignificativo solicitudEventoSignificativo2 = new solicitudEventoSignificativo
				{
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					nit = clsFactElecConfig2.NIT,
					cuis = CUIS,
					cufd = CUFD,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					codigoMotivoEvento = codigoMotivo,
					descripcion = descripcion,
					fechaHoraInicioEvento = Conversions.ToDate(VariableGeneral.ArmarFechaSIN(inicio)),
					fechaHoraFinEvento = Conversions.ToDate(VariableGeneral.ArmarFechaSIN(fin)),
					cufdEvento = CUFDevento
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionOperacionesClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						Task<registroEventoSignificativoResponse> task = null;
						int num = 3;
						int i = 0;
						bool flag;
						for (flag = true; flag & (i < num); i++)
						{
							try
							{
								task = servicioFacturacionOperacionesClient.registroEventoSignificativoAsync(solicitudEventoSignificativo2);
								task.Wait();
								flag = false;
							}
							catch (Exception ex)
							{
								ProjectData.SetProjectError(ex);
								Exception ex2 = ex;
								ProjectData.ClearProjectError();
							}
						}
						if (flag)
						{
							Interaction.MsgBox("Conexion Fallida - registrando evento significativo\r\n" + _EndPointURLOperaciones);
							result = false;
						}
						else if (task.Result != null)
						{
							if (task.Result.RespuestaListaEventos != null)
							{
								respuestaListaEventos respuestaListaEventos2 = task.Result.RespuestaListaEventos;
								if (respuestaListaEventos2.transaccion)
								{
									CodigoEventoRespuesta = Conversions.ToString(respuestaListaEventos2.codigoRecepcionEventoSignificativo);
									BD.ConsultaModificar("FactElectFueraLinea", "CodigoEventoRespuesta='" + CodigoEventoRespuesta + "',Obs=''", "FactElectFueraLineaID=" + Conversions.ToString(FactElectFueraLineaID));
								}
								else
								{
									CodigoEventoRespuesta = "";
									string text = "";
									if (respuestaListaEventos2.mensajesList == null)
									{
										Interaction.MsgBox("error, sin mensaje");
										text = "sin mensaje";
									}
									else if (respuestaListaEventos2.mensajesList.Length > 0)
									{
										int num2 = respuestaListaEventos2.mensajesList.Length - 1;
										for (int j = 0; j <= num2; j++)
										{
											text = text + respuestaListaEventos2.mensajesList[j].descripcion + ";";
										}
										if (text.Length > 249)
										{
											text = text.Substring(0, 249);
										}
										Interaction.MsgBox(text);
									}
									else
									{
										text = "sin mensaje";
										Interaction.MsgBox("error, sin mensaje");
									}
									BD.ConsultaModificar("FactElectFueraLinea", "Obs='" + text + "'", "FactElectFueraLineaID=" + Conversions.ToString(FactElectFueraLineaID));
								}
								result = respuestaListaEventos2.transaccion;
							}
							else
							{
								BD.ConsultaModificar("FactElectFueraLinea", "Obs='Sin Mensaje'", "FactElectFueraLineaID=" + Conversions.ToString(FactElectFueraLineaID));
								result = false;
							}
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
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool consultaEventoSignificativo1(ref string CodigoRecepcionRespuesta, int codigoMotivo, string CUIS, string CUFD, DateTime inicio, int FactElectFueraLineaID)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionOperacionesClient servicioFacturacionOperacionesClient = new ServicioFacturacionOperacionesClient();
				servicioFacturacionOperacionesClient.Endpoint.Address = new EndpointAddress(_EndPointURLOperaciones);
				solicitudConsultaEvento solicitudConsultaEvento2 = new solicitudConsultaEvento
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					cuis = CUIS,
					cufd = CUFD,
					nit = clsFactElecConfig2.NIT,
					fechaEvento = Conversions.ToDate(VariableGeneral.ArmarFechaSIN(inicio))
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionOperacionesClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						Task<consultaEventoSignificativoResponse> task = null;
						int num = 3;
						int i = 0;
						bool flag;
						for (flag = true; flag & (i < num); i++)
						{
							try
							{
								task = servicioFacturacionOperacionesClient.consultaEventoSignificativoAsync(solicitudConsultaEvento2);
								task.Wait();
								flag = false;
							}
							catch (Exception ex)
							{
								ProjectData.SetProjectError(ex);
								Exception ex2 = ex;
								ProjectData.ClearProjectError();
							}
						}
						if (flag)
						{
							Interaction.MsgBox("Conexion Fallida - Consulta evento significativo");
							result = false;
						}
						else if (task.Result != null)
						{
							if (task.Result.RespuestaListaEventos != null)
							{
								respuestaListaEventos respuestaListaEventos2 = task.Result.RespuestaListaEventos;
								if (respuestaListaEventos2.transaccion)
								{
									bool flag2 = false;
									string text = "";
									int num2 = respuestaListaEventos2.listaCodigos.Length - 1;
									for (int j = 0; j <= num2; j++)
									{
										eventosSignificativosDto eventosSignificativosDto2 = respuestaListaEventos2.listaCodigos[j];
										DateTime fechaFin = Conversions.ToDate(eventosSignificativosDto2.fechaFin);
										DateTime fechaIni = Conversions.ToDate(eventosSignificativosDto2.fechaInicio);
										string text2 = eventosSignificativosDto2.descripcion.ToString().Replace("ContingenciaID ", "");
										if (Versioned.IsNumeric(text2))
										{
											text = text + text2 + ",";
											clsFactElecContingencias obj = new clsFactElecContingencias();
											obj.updateContingenciaFechas(Conversions.ToInteger(text2), fechaIni, fechaFin);
											obj.updateContingenciaCodigoCreacionEvento(Conversions.ToInteger(text2), Conversions.ToString(eventosSignificativosDto2.codigoRecepcionEventoSignificativo));
											if ((double)FactElectFueraLineaID == Conversions.ToDouble(text2))
											{
												flag2 = true;
												CodigoRecepcionRespuesta = Conversions.ToString(eventosSignificativosDto2.codigoRecepcionEventoSignificativo);
											}
										}
									}
									if (!flag2)
									{
										new clsFactElecContingencias().updateContingenciaCodigoCreacionEvento(FactElectFueraLineaID, "");
										Interaction.MsgBox("No encontro, La Contingencia de esa fecha : " + text);
										CodigoRecepcionRespuesta = "";
										result = false;
									}
									else
									{
										Interaction.MsgBox("Encontro! La Contingencia de esa fecha : " + text);
										result = true;
									}
								}
								else
								{
									CodigoRecepcionRespuesta = "";
									string text3 = "";
									if (respuestaListaEventos2.mensajesList == null)
									{
										Interaction.MsgBox("error, sin mensaje");
										text3 = "sin mensaje";
									}
									else if (respuestaListaEventos2.mensajesList.Length > 0)
									{
										int num3 = respuestaListaEventos2.mensajesList.Length - 1;
										for (int k = 0; k <= num3; k++)
										{
											text3 = text3 + respuestaListaEventos2.mensajesList[k].descripcion + ";";
										}
										if (text3.Length > 249)
										{
											text3 = text3.Substring(0, 249);
										}
										Interaction.MsgBox(text3);
									}
									else
									{
										text3 = "sin mensaje";
										Interaction.MsgBox("error, sin mensaje");
									}
									result = respuestaListaEventos2.transaccion;
								}
							}
							else
							{
								result = false;
							}
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
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool RegistroPuntoVenta(ref string CodigoEventoRespuesta, string CUIS, string nombre, string descripcion, int codigoPuntoVenta)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionOperacionesClient servicioFacturacionOperacionesClient = new ServicioFacturacionOperacionesClient();
				servicioFacturacionOperacionesClient.Endpoint.Address = new EndpointAddress(_EndPointURLOperaciones);
				solicitudRegistroPuntoVenta solicitudRegistroPuntoVenta2 = new solicitudRegistroPuntoVenta
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					nit = clsFactElecConfig2.NIT,
					cuis = CUIS,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					descripcion = descripcion,
					codigoModalidad = (int)clsFactElecConfig2.CodigoModalidad,
					codigoTipoPuntoVenta = codigoPuntoVenta,
					nombrePuntoVenta = nombre
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionOperacionesClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaRegistroPuntoVenta respuestaRegistroPuntoVenta2 = servicioFacturacionOperacionesClient.registroPuntoVenta(solicitudRegistroPuntoVenta2);
						if (respuestaRegistroPuntoVenta2.transaccion)
						{
							CodigoEventoRespuesta = Conversions.ToString(respuestaRegistroPuntoVenta2.codigoPuntoVenta);
						}
						else
						{
							CodigoEventoRespuesta = "";
							if (respuestaRegistroPuntoVenta2.mensajesList == null)
							{
								Interaction.MsgBox("error, sin mensaje");
							}
							else if (respuestaRegistroPuntoVenta2.mensajesList.Length > 0)
							{
								string text = "";
								int num = respuestaRegistroPuntoVenta2.mensajesList.Length - 1;
								for (int i = 0; i <= num; i++)
								{
									text = text + respuestaRegistroPuntoVenta2.mensajesList[i].descripcion + ";";
								}
								if (text.Length > 249)
								{
									text = text.Substring(0, 249);
								}
								Interaction.MsgBox(text);
							}
							else
							{
								Interaction.MsgBox("error, sin mensaje");
							}
						}
						result = respuestaRegistroPuntoVenta2.transaccion;
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
}
