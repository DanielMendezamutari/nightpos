using System;
using System.Net;
using System.Runtime.InteropServices;
using System.ServiceModel;
using System.Threading;
using ControlConsumoLib.FactElecNotaCredito;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElecSectorDoctoAjuste
{
	private readonly string _EndPointURLFactElecNotaCreditoDebito;

	public clsFactElecSectorDoctoAjuste(int Ambiente)
	{
		_EndPointURLFactElecNotaCreditoDebito = null;
		ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
		switch (Ambiente)
		{
		case 2:
			_EndPointURLFactElecNotaCreditoDebito = "https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionDocumentoAjuste";
			break;
		case 0:
			_EndPointURLFactElecNotaCreditoDebito = null;
			break;
		default:
			_EndPointURLFactElecNotaCreditoDebito = "https://siatrest.impuestos.gob.bo/v2/ServicioFacturacionDocumentoAjuste";
			break;
		}
	}

	public bool verificarComunicacion([Optional][DefaultParameterValue("")] ref string error1)
	{
		bool result;
		try
		{
			ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
			servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLFactElecNotaCreditoDebito);
			result = servicioFacturacionClient.verificarComunicacion().transaccion;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			error1 = _EndPointURLFactElecNotaCreditoDebito + ". " + ex2.Message;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public object RecepcionDocumentoAjuste(int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, byte[] archivo, string hashArchivo, int codigoDocumentoSector, DateTime fechaemision, ref int errorID, ref string error1, ref string codigoRecepcion)
	{
		object result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLFactElecNotaCreditoDebito);
				solicitudRecepcionFactura solicitudServicioRecepcionDocumentoAjuste = new solicitudRecepcionFactura
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoDocumentoSector = codigoDocumentoSector,
					codigoEmision = codigoEmision,
					codigoModalidad = (int)clsFactElecConfig2.CodigoModalidad,
					cufd = cufd,
					cuis = cuis,
					tipoFacturaDocumento = tipoFacturaDocumento,
					fechaEnvio = VariableGeneral.ArmarFechaSIN(fechaemision),
					archivo = archivo,
					hashArchivo = hashArchivo
				};
				respuestaRecepcion respuestaRecepcion2 = null;
				int num = 3;
				int i = 0;
				checked
				{
					bool flag;
					for (flag = true; flag & (i < num); i++)
					{
						try
						{
							respuestaRecepcion2 = servicioFacturacionClient.recepcionDocumentoAjuste(solicitudServicioRecepcionDocumentoAjuste);
							flag = false;
						}
						catch (Exception ex)
						{
							ProjectData.SetProjectError(ex);
							Exception ex2 = ex;
							Thread.Sleep(1000);
							ProjectData.ClearProjectError();
						}
					}
					if (flag)
					{
						errorID = 1;
						error1 = "Conexion fallida con servidor de impuestos";
						result = false;
					}
					else if (respuestaRecepcion2 != null)
					{
						if (respuestaRecepcion2.transaccion)
						{
							codigoRecepcion = respuestaRecepcion2.codigoRecepcion;
							result = true;
						}
						else
						{
							if (respuestaRecepcion2.mensajesList == null)
							{
								if (respuestaRecepcion2.codigoDescripcion.ToString().Length > 0)
								{
									error1 = respuestaRecepcion2.codigoDescripcion;
								}
								else
								{
									error1 = "sin mensaje";
								}
							}
							else if (respuestaRecepcion2.mensajesList.Length > 0)
							{
								string text = "";
								int num2 = respuestaRecepcion2.mensajesList.Length - 1;
								for (int j = 0; j <= num2; j++)
								{
									text = text + respuestaRecepcion2.mensajesList[j].descripcion + ";";
								}
								if (text.Length > 249)
								{
									text = text.Substring(0, 249);
								}
								if (respuestaRecepcion2.mensajesList[0].codigo == 953)
								{
									error1 = text + "\r\nSe generara un nuevo CUFD la proxima vez que intente generar una factura";
									new clsCUFD().modificarFechaHastaCUFD(cufd, DateAndTime.Now);
								}
								else if (respuestaRecepcion2.mensajesList[0].codigo == 123)
								{
									error1 = text + "\r\nREVISE LA HORA Y LA ZONA HORARIA DE SU PC!!!\r\nSe generara un nuevo CUFD la proxima vez que intente generar una factura";
									new clsCUFD().modificarFechaHastaCUFD(cufd, DateAndTime.Now);
								}
								else if (respuestaRecepcion2.mensajesList[0].codigo == 1016)
								{
									error1 = text;
								}
								else
								{
									error1 = text;
								}
							}
							else
							{
								error1 = "sin mensaje";
							}
							result = respuestaRecepcion2.transaccion;
						}
					}
					else
					{
						errorID = 2;
						result = false;
					}
				}
			}
			else
			{
				errorID = 3;
				result = false;
			}
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			errorID = 2;
			error1 = ex4.Message;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool AnularDocumentoAjuste(int codigoMotivo, int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, int codigoDocumentoSector, string cuf, ref bool ExisteEnSiat)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			respuestaRecepcion respuestaRecepcion2;
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLFactElecNotaCreditoDebito);
				solicitudAnulacion solicitudServicioAnulacionDocumentoAjuste = new solicitudAnulacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoDocumentoSector = codigoDocumentoSector,
					codigoEmision = codigoEmision,
					codigoModalidad = (int)clsFactElecConfig2.CodigoModalidad,
					cufd = cufd,
					cuis = cuis,
					cuf = cuf,
					tipoFacturaDocumento = tipoFacturaDocumento,
					codigoMotivo = codigoMotivo
				};
				respuestaRecepcion2 = null;
				int num = 3;
				int i = 0;
				checked
				{
					bool flag;
					for (flag = true; flag & (i < num); i++)
					{
						try
						{
							respuestaRecepcion2 = servicioFacturacionClient.anulacionDocumentoAjuste(solicitudServicioAnulacionDocumentoAjuste);
							flag = false;
						}
						catch (Exception ex)
						{
							ProjectData.SetProjectError(ex);
							Exception ex2 = ex;
							Thread.Sleep(1000);
							ProjectData.ClearProjectError();
						}
					}
					if (flag)
					{
						throw new Exception("Conexion Fallida - anular");
					}
					if (respuestaRecepcion2 != null)
					{
						if (respuestaRecepcion2.transaccion)
						{
							_ = respuestaRecepcion2.codigoEstado;
							ExisteEnSiat = true;
							result = true;
						}
						else
						{
							if (respuestaRecepcion2.mensajesList == null)
							{
								Interaction.MsgBox("No se pudo anular, sin mensaje");
								goto IL_026e;
							}
							if (respuestaRecepcion2.mensajesList.Length <= 0)
							{
								Interaction.MsgBox("No se pudo anular, sin mensaje");
								goto IL_026e;
							}
							string text = "";
							int num2 = respuestaRecepcion2.mensajesList.Length - 1;
							for (int j = 0; j <= num2; j++)
							{
								text = text + respuestaRecepcion2.mensajesList[j].descripcion + ";";
							}
							if (text.Length > 249)
							{
								text = text.Substring(0, 249);
							}
							Interaction.MsgBox(text);
							if (respuestaRecepcion2.mensajesList[0].codigo == 981)
							{
								ExisteEnSiat = true;
								result = false;
							}
							else if (respuestaRecepcion2.mensajesList[0].codigo == 941)
							{
								ExisteEnSiat = true;
								result = false;
							}
							else if (respuestaRecepcion2.mensajesList[0].codigo == 934)
							{
								ExisteEnSiat = true;
								result = false;
							}
							else if (respuestaRecepcion2.mensajesList[0].codigo == 936)
							{
								ExisteEnSiat = true;
								result = true;
							}
							else if (respuestaRecepcion2.mensajesList[0].codigo == 924)
							{
								ExisteEnSiat = false;
								result = false;
							}
							else
							{
								result = false;
							}
						}
					}
					else
					{
						result = false;
					}
				}
			}
			else
			{
				result = false;
			}
			goto end_IL_0000;
			IL_026e:
			result = respuestaRecepcion2.transaccion;
			end_IL_0000:;
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			Interaction.MsgBox("No se pudo anular," + ex4.Message);
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool revertirAnulacionDocumentoAjuste(int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, int codigoDocumentoSector, string cuf, ref bool ExisteEnSiat)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			respuestaRecepcion respuestaRecepcion2;
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLFactElecNotaCreditoDebito);
				solicitudReversionAnulacion solicitudServicioReversionAnulacionDocumentoAjuste = new solicitudReversionAnulacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoDocumentoSector = codigoDocumentoSector,
					codigoEmision = codigoEmision,
					codigoModalidad = (int)clsFactElecConfig2.CodigoModalidad,
					cufd = cufd,
					cuis = cuis,
					cuf = cuf,
					tipoFacturaDocumento = tipoFacturaDocumento
				};
				respuestaRecepcion2 = null;
				int num = 3;
				int i = 0;
				checked
				{
					bool flag;
					for (flag = true; flag & (i < num); i++)
					{
						try
						{
							respuestaRecepcion2 = servicioFacturacionClient.reversionAnulacionDocumentoAjuste(solicitudServicioReversionAnulacionDocumentoAjuste);
							flag = false;
						}
						catch (Exception ex)
						{
							ProjectData.SetProjectError(ex);
							Exception ex2 = ex;
							Thread.Sleep(1000);
							ProjectData.ClearProjectError();
						}
					}
					if (flag)
					{
						throw new Exception("Conexion Fallida - anular");
					}
					if (respuestaRecepcion2 != null)
					{
						if (respuestaRecepcion2.transaccion)
						{
							_ = respuestaRecepcion2.codigoEstado;
							ExisteEnSiat = true;
							result = true;
						}
						else
						{
							if (respuestaRecepcion2.mensajesList == null)
							{
								Interaction.MsgBox("No se pudo anular, sin mensaje");
								goto IL_0246;
							}
							if (respuestaRecepcion2.mensajesList.Length <= 0)
							{
								Interaction.MsgBox("No se pudo anular, sin mensaje");
								goto IL_0246;
							}
							string text = "";
							int num2 = respuestaRecepcion2.mensajesList.Length - 1;
							for (int j = 0; j <= num2; j++)
							{
								text = text + respuestaRecepcion2.mensajesList[j].descripcion + ";";
							}
							if (text.Length > 249)
							{
								text = text.Substring(0, 249);
							}
							Interaction.MsgBox(text);
							if (respuestaRecepcion2.mensajesList[0].codigo == 941)
							{
								ExisteEnSiat = true;
								result = false;
							}
							else if (respuestaRecepcion2.mensajesList[0].codigo == 934)
							{
								ExisteEnSiat = true;
								result = false;
							}
							else if (respuestaRecepcion2.mensajesList[0].codigo == 936)
							{
								ExisteEnSiat = true;
								result = true;
							}
							else if (respuestaRecepcion2.mensajesList[0].codigo == 924)
							{
								ExisteEnSiat = false;
								result = false;
							}
							else
							{
								result = false;
							}
						}
					}
					else
					{
						result = false;
					}
				}
			}
			else
			{
				result = false;
			}
			goto end_IL_0000;
			IL_0246:
			result = respuestaRecepcion2.transaccion;
			end_IL_0000:;
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			Interaction.MsgBox("No se pudo anular," + ex4.Message);
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public object ValidacionDocumentoAjuste(int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, int codigoDocumentoSector, string CUF, ref int estado, ref string error1)
	{
		object result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			respuestaRecepcion respuestaRecepcion2;
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLFactElecNotaCreditoDebito);
				solicitudVerificacionEstado solicitudServicioVerificacionEstadoDocumentoAjuste = new solicitudVerificacionEstado
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoDocumentoSector = codigoDocumentoSector,
					codigoModalidad = (int)clsFactElecConfig2.CodigoModalidad,
					cufd = cufd,
					cuis = cuis,
					tipoFacturaDocumento = tipoFacturaDocumento,
					codigoEmision = codigoEmision,
					cuf = CUF
				};
				respuestaRecepcion2 = servicioFacturacionClient.verificacionEstadoDocumentoAjuste(solicitudServicioVerificacionEstadoDocumentoAjuste);
				if (respuestaRecepcion2.transaccion)
				{
					switch (respuestaRecepcion2.codigoEstado)
					{
					case 690:
						estado = 1;
						result = true;
						break;
					case 691:
						estado = 2;
						result = true;
						break;
					default:
						estado = 4;
						result = true;
						break;
					}
				}
				else
				{
					if (respuestaRecepcion2.mensajesList == null)
					{
						error1 = "sin mensaje, validando";
						goto IL_01a0;
					}
					if (respuestaRecepcion2.codigoEstado != 902)
					{
						goto IL_01a0;
					}
					string descripcion = respuestaRecepcion2.mensajesList[0].descripcion;
					error1 = descripcion;
					if (respuestaRecepcion2.mensajesList[0].codigo == 915)
					{
						estado = 4;
						result = true;
					}
					else if (respuestaRecepcion2.mensajesList[0].codigo == 924)
					{
						estado = 3;
						result = true;
					}
					else
					{
						_ = respuestaRecepcion2.mensajesList[0].codigo;
						_ = 953;
						estado = 3;
						result = true;
					}
				}
			}
			else
			{
				result = false;
			}
			goto end_IL_0000;
			IL_01a0:
			result = respuestaRecepcion2.transaccion;
			end_IL_0000:;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			estado = 4;
			error1 = ex2.Message;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}
}
