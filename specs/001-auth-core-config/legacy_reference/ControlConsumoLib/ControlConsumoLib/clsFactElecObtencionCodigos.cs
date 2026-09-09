using System;
using System.Net;
using System.ServiceModel;
using System.ServiceModel.Channels;
using System.Threading;
using ControlConsumoLib.FactElectCodigos;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElecObtencionCodigos
{
	public string _EndPointURLFacturacionCodigos;

	public clsFactElecObtencionCodigos(int Ambiente)
	{
		_EndPointURLFacturacionCodigos = null;
		ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
		switch (Ambiente)
		{
		case 2:
			_EndPointURLFacturacionCodigos = "https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos";
			break;
		case 0:
			_EndPointURLFacturacionCodigos = null;
			break;
		default:
			_EndPointURLFacturacionCodigos = "https://siatrest.impuestos.gob.bo/v2/FacturacionCodigos";
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
				ServicioFacturacionCodigosClient servicioFacturacionCodigosClient = new ServicioFacturacionCodigosClient();
				servicioFacturacionCodigosClient.Endpoint.Address = new EndpointAddress(_EndPointURLFacturacionCodigos);
				using (new OperationContextScope(servicioFacturacionCodigosClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					result = servicioFacturacionCodigosClient.verificarComunicacion().transaccion;
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
			if (Operators.CompareString(ex2.Message, "API KEY NO VALIDO", TextCompare: false) == 0)
			{
				Interaction.MsgBox("API KEY NO VALIDO");
			}
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool ObtenerCUIS(ref string codigoCUIS, int codigoPuntoVenta)
	{
		bool result;
		try
		{
			clsCUIS clsCUIS2 = new clsCUIS();
			if (clsCUIS2.obtenerCUISvigente(codigoPuntoVenta))
			{
				codigoCUIS = clsCUIS2.codigoCUIS;
				result = true;
			}
			else
			{
				clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
				if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
				{
					ServicioFacturacionCodigosClient servicioFacturacionCodigosClient = new ServicioFacturacionCodigosClient();
					servicioFacturacionCodigosClient.Endpoint.Address = new EndpointAddress(_EndPointURLFacturacionCodigos);
					solicitudCuis solicitudCuis2 = new solicitudCuis
					{
						codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
						codigoModalidad = (int)clsFactElecConfig2.CodigoModalidad,
						codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
						codigoPuntoVentaSpecified = true,
						codigoSistema = clsFactElecConfig2.CodigoSistema,
						codigoSucursal = clsFactElecConfig2.codigoSucursal,
						nit = clsFactElecConfig2.NIT
					};
					servicioFacturacionCodigosClient.Endpoint.Address.ToString();
					checked
					{
						using (new OperationContextScope(servicioFacturacionCodigosClient.InnerChannel))
						{
							HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
							httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
							OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
							respuestaCuis respuestaCuis2 = servicioFacturacionCodigosClient.cuis(solicitudCuis2);
							if (respuestaCuis2.transaccion)
							{
								codigoCUIS = respuestaCuis2.codigo;
								clsCUIS2.guardarCUIS(codigoCUIS, respuestaCuis2.fechaVigencia, clsFactElecConfig2.CodigoPuntoVenta);
								new clsCUFD().dshabilitarCUFDvigente();
							}
							else if (respuestaCuis2.codigo == null)
							{
								string text = "";
								int num = respuestaCuis2.mensajesList.Length - 1;
								for (int i = 0; i <= num; i++)
								{
									text = text + respuestaCuis2.mensajesList[i].descripcion + ";";
								}
								if (text.Length > 249)
								{
									text = text.Substring(0, 249);
								}
								Interaction.MsgBox(text);
							}
							else
							{
								codigoCUIS = respuestaCuis2.codigo;
								clsCUIS2.guardarCUIS(codigoCUIS, respuestaCuis2.fechaVigencia, clsFactElecConfig2.CodigoPuntoVenta);
							}
							result = respuestaCuis2.transaccion;
						}
					}
				}
				else
				{
					result = false;
				}
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message);
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool HayCUFDvigente(DateTime fecha)
	{
		clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
		if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
		{
			return new clsCUFD().obtenerCUFDvigente(clsFactElecConfig2.CodigoPuntoVenta, fecha);
		}
		return false;
	}

	public bool ObtenerCUFD(ref string codigoCUFD, ref string CodigoControl, ref DateTime fecha, ref int cufdID, bool desdeCelular = false, bool test = false)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			clsCUFD clsCUFD2;
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				clsCUFD2 = new clsCUFD();
				if (test)
				{
					goto IL_00a8;
				}
				if (clsCUFD2.obtenerCUFDvigente(clsFactElecConfig2.CodigoPuntoVenta, fecha))
				{
					codigoCUFD = clsCUFD2.codigoCUFD;
					CodigoControl = clsCUFD2.codigoControl;
					cufdID = clsCUFD2.FactElectCUFDID;
					result = true;
				}
				else
				{
					if (!clsCUFD2.obtenerUltimoCUFD(clsFactElecConfig2.CodigoPuntoVenta) || !((DateTime.Compare(DateTime.Now, clsCUFD2.FechaDesde) >= 0) & (DateTime.Compare(DateTime.Now, clsCUFD2.FechaHasta) <= 0)))
					{
						goto IL_00a8;
					}
					codigoCUFD = "";
					CodigoControl = "";
					cufdID = 0;
					result = false;
				}
			}
			else
			{
				result = false;
			}
			goto end_IL_0000;
			IL_014c:
			ServicioFacturacionCodigosClient servicioFacturacionCodigosClient = new ServicioFacturacionCodigosClient();
			servicioFacturacionCodigosClient.Endpoint.Address = new EndpointAddress(_EndPointURLFacturacionCodigos);
			string codigoCUIS;
			solicitudCufd solicitudCufd2 = new solicitudCufd
			{
				codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
				codigoModalidad = (int)clsFactElecConfig2.CodigoModalidad,
				codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
				codigoPuntoVentaSpecified = true,
				codigoSistema = clsFactElecConfig2.CodigoSistema,
				codigoSucursal = clsFactElecConfig2.codigoSucursal,
				nit = clsFactElecConfig2.NIT,
				cuis = codigoCUIS
			};
			checked
			{
				using (new OperationContextScope(servicioFacturacionCodigosClient.InnerChannel))
				{
					HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
					httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
					OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
					respuestaCufd respuestaCufd2 = servicioFacturacionCodigosClient.cufd(solicitudCufd2);
					if (respuestaCufd2.transaccion)
					{
						codigoCUFD = respuestaCufd2.codigo;
						CodigoControl = respuestaCufd2.codigoControl;
						clsCUFD2.guardarCUFD(codigoCUFD, CodigoControl, respuestaCufd2.fechaVigencia, clsFactElecConfig2.CodigoPuntoVenta, VariableGeneral.gConfiguracionID);
						cufdID = clsCUFD2.FactElectCUFDID;
						if (!test)
						{
							int num = 60 - DateTime.Now.Second + 1;
							if (num > 0)
							{
								Thread.Sleep(num * 1000);
							}
							else
							{
								Thread.Sleep(5000);
							}
						}
						fecha = DateTime.Now;
						fecha = fecha.AddMilliseconds(fecha.Millisecond * -1);
					}
					else if (respuestaCufd2.mensajesList.Length > 0 && !desdeCelular)
					{
						Interaction.MsgBox(respuestaCufd2.mensajesList[0].descripcion);
					}
					result = respuestaCufd2.transaccion;
				}
				goto end_IL_0000;
			}
			IL_00a8:
			codigoCUIS = "";
			if (!ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta))
			{
				result = false;
			}
			else if (!verificarComunicacion())
			{
				result = false;
			}
			else
			{
				if (desdeCelular)
				{
					goto IL_014c;
				}
				clsFacElecSyncDatos clsFacElecSyncDatos2 = new clsFacElecSyncDatos((int)clsFactElecConfig2.CodigoAmbiente);
				string error = "";
				if (clsFacElecSyncDatos2.checkFechaHora(ref error) == 1)
				{
					goto IL_014c;
				}
				Thread.Sleep(1000);
				if (clsFacElecSyncDatos2.checkFechaHora(ref error) == 1)
				{
					goto IL_014c;
				}
				Thread.Sleep(1000);
				if (clsFacElecSyncDatos2.checkFechaHora(ref error) != 0)
				{
					goto IL_014c;
				}
				Interaction.MsgBox("La fecha/hora de Impuestos es diferente a la de esta computadora, no podra generar nuevo CUFDiario.\r\n" + error);
				codigoCUFD = "";
				CodigoControl = "";
				result = false;
			}
			end_IL_0000:;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			if (!desdeCelular)
			{
				Interaction.MsgBox("Problema obteniendo el codigo CUFDiario.\r\n" + ex2.Message);
			}
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public void obtenerFechaFinCUFD(int cufdID, ref DateTime fechaHAsta)
	{
		clsCUFD obj = new clsCUFD();
		obj.FactElectCUFDID = cufdID;
		obj.obtenerFechaFinCUFD(ref fechaHAsta);
	}

	public int verificarNIT1(string NIT, ref string error1)
	{
		int result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				string codigoCUIS = "";
				ObtenerCUIS(ref codigoCUIS, clsFactElecConfig2.CodigoPuntoVenta);
				ServicioFacturacionCodigosClient servicioFacturacionCodigosClient = new ServicioFacturacionCodigosClient();
				servicioFacturacionCodigosClient.Endpoint.Address = new EndpointAddress(_EndPointURLFacturacionCodigos);
				solicitudVerificarNit solicitudVerificarNit2 = new solicitudVerificarNit
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoModalidad = (int)clsFactElecConfig2.CodigoModalidad,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					cuis = codigoCUIS,
					nitParaVerificacion = Conversions.ToLong(NIT)
				};
				checked
				{
					using (new OperationContextScope(servicioFacturacionCodigosClient.InnerChannel))
					{
						HttpRequestMessageProperty httpRequestMessageProperty = new HttpRequestMessageProperty();
						httpRequestMessageProperty.Headers.Add("apikey", "TokenApi " + clsFactElecConfig2.TokenDelegado);
						OperationContext.Current.OutgoingMessageProperties[HttpRequestMessageProperty.Name] = httpRequestMessageProperty;
						respuestaVerificarNit respuestaVerificarNit2 = servicioFacturacionCodigosClient.verificarNit(solicitudVerificarNit2);
						if (respuestaVerificarNit2.transaccion)
						{
							result = 1;
						}
						else
						{
							string text = "";
							int num = respuestaVerificarNit2.mensajesList.Length - 1;
							for (int i = 0; i <= num; i++)
							{
								text = text + respuestaVerificarNit2.mensajesList[i].descripcion + ";";
							}
							if (text.Length > 249)
							{
								text = text.Substring(0, 249);
							}
							if (text.ToUpper().Contains("ERROR SERVICIO PADRON"))
							{
								error1 = "Verificando NIT: " + text;
								result = -1;
							}
							else
							{
								error1 = "Verificando NIT - " + text;
								result = 0;
							}
						}
					}
				}
			}
			else
			{
				error1 = "";
				result = -1;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			error1 = "";
			result = -1;
			ProjectData.ClearProjectError();
		}
		return result;
	}
}
