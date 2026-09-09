using System;
using System.Net;
using System.Runtime.InteropServices;
using System.ServiceModel;
using System.Threading;
using ControlConsumoLib.FactElectCompraVenta;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElecSectorCompraVenta
{
	private readonly string _EndPointURLCompraVenta;

	public clsFactElecSectorCompraVenta(int Ambiente, int sector, int modalidad)
	{
		_EndPointURLCompraVenta = null;
		ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
		switch (Ambiente)
		{
		case 2:
			if ((sector == 1) | (sector == 35))
			{
				_EndPointURLCompraVenta = "https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta";
			}
			else if (modalidad == 1)
			{
				_EndPointURLCompraVenta = "https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionElectronica";
			}
			else
			{
				_EndPointURLCompraVenta = "https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionComputarizada";
			}
			break;
		case 0:
			_EndPointURLCompraVenta = null;
			break;
		default:
			if ((sector == 1) | (sector == 35))
			{
				_EndPointURLCompraVenta = "https://siatrest.impuestos.gob.bo/v2/ServicioFacturacionCompraVenta";
			}
			else if (modalidad == 1)
			{
				_EndPointURLCompraVenta = "https://siatrest.impuestos.gob.bo/v2/ServicioFacturacionElectronica";
			}
			else
			{
				_EndPointURLCompraVenta = "https://siatrest.impuestos.gob.bo/v2/ServicioFacturacionComputarizada";
			}
			break;
		}
	}

	public bool verificarComunicacion([Optional][DefaultParameterValue("")] ref string error1)
	{
		bool result;
		try
		{
			ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
			servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLCompraVenta);
			result = servicioFacturacionClient.verificarComunicacion().transaccion;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			error1 = _EndPointURLCompraVenta + ". " + ex2.Message;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public object RecepcionFactura1(int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, int codigoDocumentoSector, byte[] archivo, string hashArchivo, DateTime fechaemision, ref int errorID, ref string error1, ref string codigoRecepcion)
	{
		object result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLCompraVenta);
				solicitudRecepcionFactura solicitudServicioRecepcionFactura = new solicitudRecepcionFactura
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
					archivo = archivo,
					fechaEnvio = VariableGeneral.ArmarFechaSIN(fechaemision),
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
							respuestaRecepcion2 = servicioFacturacionClient.recepcionFactura(solicitudServicioRecepcionFactura);
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
								error1 = "sin mensaje";
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

	public bool AnularFactura1(int codigoMotivo, int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, int DocumentoSector, string cuf, ref bool ExisteEnSiat, ref string mensaje1)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			respuestaRecepcion respuestaRecepcion2;
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLCompraVenta);
				solicitudAnulacion solicitudServicioAnulacionFactura = new solicitudAnulacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoDocumentoSector = DocumentoSector,
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
							respuestaRecepcion2 = servicioFacturacionClient.anulacionFactura(solicitudServicioAnulacionFactura);
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
								mensaje1 = "No se pudo anular, sin mensaje";
								goto IL_027c;
							}
							if (respuestaRecepcion2.mensajesList.Length <= 0)
							{
								mensaje1 = "No se pudo anular, sin mensaje";
								goto IL_027c;
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
							mensaje1 = text;
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
							else if (respuestaRecepcion2.mensajesList[0].codigo == 3010)
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
			IL_027c:
			result = respuestaRecepcion2.transaccion;
			end_IL_0000:;
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			mensaje1 = "No se pudo anular," + ex4.Message;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool reversionAnulacionFactura(int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, int DocumentoSector, string cuf, ref bool ExisteEnSiat, ref string mensaje1)
	{
		bool result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			respuestaRecepcion respuestaRecepcion2;
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLCompraVenta);
				solicitudReversionAnulacion solicitudServicioReversionAnulacionFactura = new solicitudReversionAnulacion
				{
					codigoAmbiente = (int)clsFactElecConfig2.CodigoAmbiente,
					codigoPuntoVenta = clsFactElecConfig2.CodigoPuntoVenta,
					codigoPuntoVentaSpecified = true,
					codigoSistema = clsFactElecConfig2.CodigoSistema,
					codigoSucursal = clsFactElecConfig2.codigoSucursal,
					nit = clsFactElecConfig2.NIT,
					codigoDocumentoSector = DocumentoSector,
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
							respuestaRecepcion2 = servicioFacturacionClient.reversionAnulacionFactura(solicitudServicioReversionAnulacionFactura);
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
						throw new Exception("Conexion Fallida - desanular");
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
								mensaje1 = "No se pudo desanular, sin mensaje";
								goto IL_01da;
							}
							if (respuestaRecepcion2.mensajesList.Length <= 0)
							{
								mensaje1 = "No se pudo desanular, sin mensaje";
								goto IL_01da;
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
							mensaje1 = text;
							if (respuestaRecepcion2.mensajesList[0].codigo == 981)
							{
								ExisteEnSiat = true;
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
			IL_01da:
			result = respuestaRecepcion2.transaccion;
			end_IL_0000:;
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			mensaje1 = "No se pudo desanular," + ex4.Message;
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public object ValidacionFactura1(int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, int codigoDocumentoSector, string CUF, ref int estado, ref string error1)
	{
		object result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			respuestaRecepcion respuestaRecepcion2;
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLCompraVenta);
				solicitudVerificacionEstado solicitudServicioVerificacionEstadoFactura = new solicitudVerificacionEstado
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
				respuestaRecepcion2 = servicioFacturacionClient.verificacionEstadoFactura(solicitudServicioVerificacionEstadoFactura);
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

	public object RecepcionFacturaPaqueteFueraLinea(int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, int codigoDocumentoSector, byte[] archivo, string hashArchivo, string cafc, int cantFact, string codigoEvento, int ContingenciaId, ref string CodigoRecepcionEventoRespuesta, DateTime fechaFin, ref string error1)
	{
		object result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLCompraVenta);
				solicitudRecepcionPaquete solicitudServicioRecepcionPaquete = new solicitudRecepcionPaquete
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
					archivo = archivo,
					fechaEnvio = VariableGeneral.ArmarFechaSIN(DateAndTime.Now),
					hashArchivo = hashArchivo,
					cantidadFacturas = cantFact,
					codigoEvento = Conversions.ToLong(codigoEvento),
					cafc = cafc
				};
				respuestaRecepcion respuestaRecepcion2 = null;
				int num = 3;
				int i = 0;
				bool flag = true;
				string text = "";
				checked
				{
					for (; flag & (i < num); i++)
					{
						try
						{
							respuestaRecepcion2 = servicioFacturacionClient.recepcionPaqueteFactura(solicitudServicioRecepcionPaquete);
							flag = false;
						}
						catch (Exception ex)
						{
							ProjectData.SetProjectError(ex);
							Exception ex2 = ex;
							text = ex2.Message;
							Thread.Sleep(1000);
							ProjectData.ClearProjectError();
						}
					}
					if (flag)
					{
						throw new Exception("Conexion Fallida - recepcion paquete fuera linea\r\n" + text);
					}
					if (respuestaRecepcion2 != null)
					{
						if (respuestaRecepcion2.transaccion)
						{
							CodigoRecepcionEventoRespuesta = respuestaRecepcion2.codigoRecepcion;
							new clsFactElecContingencias().TerminarContingenciaFueraDeLinea(fechaFin, cantFact, ContingenciaId, CodigoRecepcionEventoRespuesta);
							result = true;
						}
						else
						{
							if (respuestaRecepcion2.mensajesList == null)
							{
								if (respuestaRecepcion2.codigoDescripcion == null)
								{
									error1 = "recepcion, sin mensaje";
								}
								else
								{
									error1 = "recepcion, sin mensaje, " + respuestaRecepcion2.codigoDescripcion;
								}
							}
							else if (respuestaRecepcion2.mensajesList.Length > 0)
							{
								string text2 = "";
								int num2 = respuestaRecepcion2.mensajesList.Length - 1;
								for (int j = 0; j <= num2; j++)
								{
									text2 = ((!respuestaRecepcion2.mensajesList[j].numeroArchivoSpecified) ? (text2 + respuestaRecepcion2.mensajesList[j].descripcion + ";") : (text2 + respuestaRecepcion2.mensajesList[j].descripcion + " archivo " + Conversions.ToString(respuestaRecepcion2.mensajesList[j].numeroArchivo) + ";"));
								}
								if (text2.Length > 249)
								{
									text2 = text2.Substring(0, 249);
								}
								error1 += text2;
							}
							else
							{
								error1 = "sin mensaje, recepecion";
							}
							result = respuestaRecepcion2.transaccion;
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
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			Interaction.MsgBox(ex4.Message);
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public object ValidacionFacturaPaqueteFueraLinea(int codigoEmision, string cufd, string cuis, int tipoFacturaDocumento, int codigoDocumentoSector, int ContingenciaId, string codigoEvento, string codigoRecepcion, ref string error1)
	{
		object result;
		try
		{
			clsFactElecConfig clsFactElecConfig2 = new clsFactElecConfig();
			if (clsFactElecConfig2.devolverDatosSiTokenActivo1())
			{
				ServicioFacturacionClient servicioFacturacionClient = new ServicioFacturacionClient();
				servicioFacturacionClient.Endpoint.Address = new EndpointAddress(_EndPointURLCompraVenta);
				solicitudValidacionRecepcion solicitudServicioValidacionRecepcionPaquete = new solicitudValidacionRecepcion
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
					codigoRecepcion = codigoRecepcion
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
							respuestaRecepcion2 = servicioFacturacionClient.validacionRecepcionPaqueteFactura(solicitudServicioValidacionRecepcionPaquete);
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
						throw new Exception("Conexion Fallida - validacion paquete");
					}
					clsFactElecContingencias clsFactElecContingencias2 = new clsFactElecContingencias();
					if (respuestaRecepcion2 != null)
					{
						if (respuestaRecepcion2.transaccion)
						{
							switch (respuestaRecepcion2.codigoEstado)
							{
							case 908:
								clsFactElecContingencias2.ValidarContingenciaFueraDeLinea(ContingenciaId);
								result = true;
								goto end_IL_0000;
							case 904:
							{
								bool flag2 = false;
								string text = "";
								string text2 = "";
								int num2 = respuestaRecepcion2.mensajesList.Length - 1;
								for (int j = 0; j <= num2; j++)
								{
									if (respuestaRecepcion2.mensajesList[j].codigo == 1000)
									{
										text2 = "CUF ya existe en SIN";
										flag2 = true;
										continue;
									}
									if (respuestaRecepcion2.mensajesList[j].codigo == 983)
									{
										text2 = "LA FECHA DE ENVIO DEL PAQUETE ESTA FUERA DE PLAZO";
										flag2 = true;
										continue;
									}
									text = text + respuestaRecepcion2.mensajesList[j].descripcion + " #Archivo:" + Conversions.ToString(respuestaRecepcion2.mensajesList[j].numeroArchivo) + " #Detalle:" + Conversions.ToString(respuestaRecepcion2.mensajesList[j].numeroDetalle) + ";";
								}
								string text3 = text;
								if (text.Length > 249)
								{
									text3 = text.Substring(0, 249);
								}
								if (text3.Length > 0)
								{
									clsFactElecContingencias2.updateContingenciaObs(ContingenciaId, text3);
									error1 = text;
									result = flag2;
								}
								else
								{
									if (text2.Length > 0)
									{
										clsFactElecContingencias2.updateContingenciaObs(ContingenciaId, text2);
									}
									else
									{
										clsFactElecContingencias2.updateContingenciaObs(ContingenciaId, "Verificar");
									}
									result = flag2;
								}
								goto end_IL_0000;
							}
							case 901:
								clsFactElecContingencias2.updateContingenciaObs(ContingenciaId, "Validacion Pendiente");
								error1 = "Validacion Pendiente";
								result = false;
								goto end_IL_0000;
							}
						}
						else if (respuestaRecepcion2.mensajesList == null)
						{
							error1 = "sin mensaje, validando";
						}
						else if (respuestaRecepcion2.mensajesList.Length > 0)
						{
							string text4 = "";
							int num3 = respuestaRecepcion2.mensajesList.Length - 1;
							for (int k = 0; k <= num3; k++)
							{
								text4 = text4 + respuestaRecepcion2.mensajesList[k].descripcion + ";";
							}
							if (text4.Length > 249)
							{
								text4 = text4.Substring(0, 249);
							}
							error1 += text4;
						}
						else
						{
							error1 = "sin mensaje, validando";
						}
						result = respuestaRecepcion2.transaccion;
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
			end_IL_0000:;
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			Interaction.MsgBox(ex4.Message);
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}
}
