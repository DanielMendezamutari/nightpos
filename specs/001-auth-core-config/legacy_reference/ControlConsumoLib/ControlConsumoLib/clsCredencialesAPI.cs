using System;
using System.Collections.Generic;
using System.Data;
using System.IO;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Runtime.CompilerServices;
using System.Text;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;

namespace ControlConsumoLib;

public class clsCredencialesAPI
{
	public class registros
	{
		[JsonProperty("success")]
		public bool success { get; set; }

		[JsonProperty("carrier")]
		public carrier carrier { get; set; }
	}

	public class carrier
	{
		[JsonProperty("name")]
		public string name { get; set; }

		[JsonProperty("phoneNumber")]
		public string phoneNumber { get; set; }
	}

	public class Item
	{
		public string name;

		public double unitPrice;

		public int quantity;

		public List<string> addOns;
	}

	public class DetallePedido
	{
		public string sProducto;

		public int iCantidad;

		public string sObservacion;

		public double dPrecio;

		public double dMedida;

		public string sUnidadMedida;
	}

	public class Ubicacion
	{
		public string sLat;

		public string sLng;

		public string sDireccion;

		public string sReferencia;
	}

	public class Facturacion
	{
		public string sNombre;

		public string sNit;
	}

	public bool DevolverDataTigoMoney(ref string LlaveIdenf, ref string llavePrivada, ref string aConfirmacion)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("LlaveIdentificadora, LlavePrivada,aConfirmacion", "TigoMoney", "1=1");
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		LlaveIdenf = Conversions.ToString(dataTable.Rows[0][0]);
		llavePrivada = Conversions.ToString(dataTable.Rows[0][1]);
		aConfirmacion = Conversions.ToString(dataTable.Rows[0][2]);
		return true;
	}

	public DataTable DevolverPedidosYa()
	{
		return BD.ConsultaVer("PedidosYaID, NombreLocal", "CredencialesPedidosYa", "1=1");
	}

	public bool DevolverDataPedidosYa(ref string ClientID, ref string ClientSecret, ref string UserName, ref string Password, ref string Environment, ref string StoreID, int pedidosyaId)
	{
		DataTable dataTable = new DataTable();
		dataTable = ((pedidosyaId != 0) ? BD.ConsultaVer("ClientID, ClientSecret,UserName, Password1,Environment , StoreID", "CredencialesPedidosYa", "pedidosyaId=" + Conversions.ToString(pedidosyaId)) : BD.ConsultaVer("ClientID, ClientSecret,UserName, Password1,Environment , StoreID", "CredencialesPedidosYa", "1=1"));
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		ClientID = Conversions.ToString(dataTable.Rows[0][0]);
		ClientSecret = Conversions.ToString(dataTable.Rows[0][1]);
		UserName = Conversions.ToString(dataTable.Rows[0][2]);
		Password = Conversions.ToString(dataTable.Rows[0][3]);
		Environment = Conversions.ToString(dataTable.Rows[0][4]);
		StoreID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][5]), ""));
		return true;
	}

	public bool DevolverDataFidelizacionLatam(ref string SucursalID, ref string commerceId, ref string apiKey)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("SucursalID, commerceId, apiKey", "CredencialesFidelizacionLatam", "1=1");
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		SucursalID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), ""));
		commerceId = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), ""));
		apiKey = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][2]), ""));
		return true;
	}

	public bool DevolverDataQRupones(ref string SucursalID, ref string ClienteSecret, ref string ClientUser, ref string Password1, ref string MarcasID)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("SucursalID, ClientSecret, ClientUser,Password1,MarcasID", "CredencialesQRupones", "1=1");
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		SucursalID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), "SucursalID"));
		ClienteSecret = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ClientSecret"]), ""));
		ClientUser = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ClientUser"]), ""));
		Password1 = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Password1"]), ""));
		MarcasID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["MarcasID"]), ""));
		return true;
	}

	public bool DevolverDataRestomenu(ref string ClientID, ref string ClientSecret, ref string FetchMenuKey, ref bool TamanoEsProd, ref string DatosBancarios, ref string gQuestTag)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("Token1, AcceptedOrdersKey,FetchMenuKey,TamanoEsProd,DatosBancarios,qtKey", "CredencialesRestomenu", "1=1");
		if (dataTable.Rows.Count == 0)
		{
			gQuestTag = "";
			return false;
		}
		ClientID = Conversions.ToString(dataTable.Rows[0][0]);
		ClientSecret = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), ""));
		FetchMenuKey = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][2]), ""));
		TamanoEsProd = Conversions.ToBoolean(dataTable.Rows[0][3]);
		DatosBancarios = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][4]), ""));
		gQuestTag = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][5]), ""));
		return true;
	}

	public bool DevolverDataYaigo(ref string keyRestaurante, ref string apiKey)
	{
		DataTable dataTable = BD.ConsultaVer("select * from CredencialesYaigo");
		if (dataTable.Rows.Count > 0)
		{
			keyRestaurante = Conversions.ToString(dataTable.Rows[0]["sCodigoSucursal"]);
			apiKey = Conversions.ToString(dataTable.Rows[0]["sToken"]);
			return true;
		}
		return false;
	}

	public bool DevolverDataTapeke(ref string keyRestaurante, ref string apiKey)
	{
		DataTable dataTable = BD.ConsultaVer("select * from CredencialesTapeke");
		if (dataTable.Rows.Count > 0)
		{
			keyRestaurante = Conversions.ToString(dataTable.Rows[0]["keyRestaurante"]);
			apiKey = Conversions.ToString(dataTable.Rows[0]["apiKey"]);
			return true;
		}
		return false;
	}

	public bool DevolverDataGastroVentures()
	{
		new DataTable();
		if (BD.ConsultaVer("*", "CredencialesRestomenu", "1=1").Rows.Count == 0)
		{
			return false;
		}
		return true;
	}

	public bool DevolverDireccionRestoParaRestomenu(ref string Nombre, ref string Direccion, ref int Telefono, ref double lat, ref double lon, ref string gQuestTag)
	{
		DataTable dataTable = BD.ConsultaVer("Token1, Direccion,Telefono,lat,lon,qtKey", "CredencialesRestomenu", "1=1");
		if (dataTable.Rows.Count == 0)
		{
			gQuestTag = "";
			return false;
		}
		Nombre = Conversions.ToString(dataTable.Rows[0][0]);
		Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), ""));
		Telefono = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][2]), 0));
		lat = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][3]), 0));
		lon = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][4]), 0));
		gQuestTag = Conversions.ToString(dataTable.Rows[0][5]);
		return true;
	}

	public bool DevolverDataQR(ref int CompanyID, ref string accountId, ref string authorizationId, ref bool IncluirDelivery)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("CompanyID, accountId,authorizationId,IncluirDelivery", "CredencialesQR", "1=1");
		if (dataTable.Rows.Count == 0)
		{
			CompanyID = 0;
			accountId = "";
			IncluirDelivery = false;
			return false;
		}
		CompanyID = Conversions.ToInteger(dataTable.Rows[0][0]);
		accountId = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), ""));
		authorizationId = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][2]), ""));
		IncluirDelivery = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][3]), true));
		return true;
	}

	public bool DevolverDataPagotodo(ref int empresaID, ref string usuario, ref string contrasena, ref bool IncluirDelivery)
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("empresaID, usuario,contrasena,IncluirDelivery", "CredencialesPagaTodo", "1=1");
		if (dataTable.Rows.Count == 0)
		{
			empresaID = 0;
			usuario = "";
			contrasena = "";
			IncluirDelivery = true;
			return false;
		}
		empresaID = Conversions.ToInteger(dataTable.Rows[0][0]);
		usuario = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][1]), ""));
		contrasena = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][2]), ""));
		IncluirDelivery = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][3]), true));
		return true;
	}

	public void getQuestTagOrdenesActivas()
	{
		if (VariableGeneral.gQuestTag.Length <= 0)
		{
			return;
		}
		HttpWebRequest httpWebRequest = (HttpWebRequest)WebRequest.Create("https://api.shipday.com/orders");
		httpWebRequest.Headers.Add("Authorization: Basic " + VariableGeneral.gQuestTag);
		httpWebRequest.Method = "GET";
		BD.ConsultaModificar("DeliveryApp", "QT_STATE=6", "plataforma=2 and qt_ID >0 and QT_STATE<6 and PLACE_TIME>" + VariableGeneral.ArmarFecha(DateAndTime.Today));
		try
		{
			using WebResponse webResponse = httpWebRequest.GetResponse();
			using StreamReader streamReader = new StreamReader(webResponse.GetResponseStream());
			string text = streamReader.ReadToEnd();
			text = "{\"registros\":" + text + "}";
			List<JToken> list = JObject.Parse(text).Children().ToList();
			foreach (JProperty item in list)
			{
				item.CreateReader();
				string name = item.Name;
				if (Operators.CompareString(name, "registros", TextCompare: false) != 0)
				{
					continue;
				}
				foreach (JObject item2 in item.Values())
				{
					int num = (int)item2["orderId"];
					int num2 = (int)item2["orderNumber"];
					string text2 = "";
					int num3 = 0;
					text2 = item2["orderStatus"]["orderState"].ToString();
					string text3 = Conversions.ToString(VariableGeneral.NZ(item2["assignedCarrierId"].ToString(), ""));
					if (Operators.CompareString(text3, "", TextCompare: false) == 0)
					{
						text3 = Conversions.ToString(0);
					}
					switch (text2)
					{
					case "NOT_ASSIGNED":
						num3 = 1;
						break;
					case "STARTED":
						num3 = 2;
						break;
					case "PICKED_UP":
						num3 = 3;
						break;
					case "READY_TO_DELIVER":
						num3 = 4;
						break;
					case "ALREADY_DELIVERED":
						num3 = 5;
						break;
					}
					if (num2 > 0)
					{
						BD.ConsultaModificar("DeliveryApp", "qt_ID=" + Conversions.ToString(num) + ",QT_STATE=" + Conversions.ToString(num3) + ",QT_DRIVER_ID=" + text3, ("plataforma=2 and ORDER_NO = " + Conversions.ToString(num2)) ?? "");
					}
					else
					{
						BD.ConsultaModificar("DeliveryApp", "QT_STATE=" + Conversions.ToString(num3) + ",QT_DRIVER_ID=" + text3, ("plataforma=2 and QT_ID = " + Conversions.ToString(num)) ?? "");
					}
				}
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
	}

	public bool getRepartidorInfo(int repartidorID, ref string Nombre, ref string telefono)
	{
		bool result;
		if (VariableGeneral.gQuestTag.Length > 0)
		{
			HttpWebRequest httpWebRequest = (HttpWebRequest)WebRequest.Create("https://dispatch.shipday.com/Carrier/Profile/" + Conversions.ToString(repartidorID));
			httpWebRequest.Headers.Add("Authorization: Basic " + VariableGeneral.gQuestTag);
			httpWebRequest.Method = "GET";
			try
			{
				using WebResponse webResponse = httpWebRequest.GetResponse();
				using StreamReader streamReader = new StreamReader(webResponse.GetResponseStream());
				registros registros2 = JsonHelper.ToClass<registros>(streamReader.ReadToEnd());
				if (registros2.success)
				{
					string phoneNumber = registros2.carrier.phoneNumber;
					telefono = phoneNumber.Replace("+", "");
					Nombre = registros2.carrier.name;
					result = true;
					goto IL_00da;
				}
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				result = false;
				ProjectData.ClearProjectError();
				goto IL_00da;
			}
		}
		result = false;
		goto IL_00da;
		IL_00da:
		return result;
	}

	public bool getRepartidorInfoYaigo(int orderId, ref string Nombre, ref string telefono)
	{
		DataTable dataTable = BD.ConsultaVer("select * from CredencialesYaigo");
		if (dataTable.Rows.Count > 0)
		{
			string text = Conversions.ToString(dataTable.Rows[0]["sCodigoSucursal"]);
			string text2 = Conversions.ToString(dataTable.Rows[0]["sToken"]);
			string text3 = "{\r\"sCodigoSucursal\":\"" + text + "\",\r\"sToken\":\"" + text2 + "\",\r\"sCodTransaccion\":\"" + Conversions.ToString(orderId) + "\",\r}";
			WebClient obj = new WebClient
			{
				Headers = { ["content-type"] = "application/json" }
			};
			byte[] bytes = Encoding.Default.GetBytes(JsonConvert.SerializeObject(text3, Formatting.None));
			bytes = Encoding.UTF8.GetBytes(text3);
			byte[] bytes2 = obj.UploadData("http://207.180.235.65:8181/integ98k/api/integracion/solicitudestado", "post", bytes);
			string text4 = Encoding.Default.GetString(bytes2);
			text4 = "{\"registros\":" + text4 + "}";
			List<JToken> list = JObject.Parse(text4).Children().ToList();
			foreach (JProperty item in list)
			{
				item.CreateReader();
				string name = item.Name;
				if (Operators.CompareString(name, "registros", TextCompare: false) != 0)
				{
					continue;
				}
				foreach (JProperty item2 in item.Values())
				{
					string name2 = item2.Name;
					if (Operators.CompareString(name2, "Titulo", TextCompare: false) != 0)
					{
						if (Operators.CompareString(name2, "Data", TextCompare: false) != 0)
						{
							continue;
						}
						List<JToken> source = item2.Children().ToList();
						try
						{
							foreach (JProperty item3 in source.Values())
							{
								string name3 = item3.Name;
								if (Operators.CompareString(name3, "Conductor", TextCompare: false) != 0)
								{
									continue;
								}
								try
								{
									List<JToken> source2 = item3.Children().ToList();
									foreach (JProperty item4 in source2.Values())
									{
										string name4 = item4.Name;
										if (Operators.CompareString(name4, "sNombre", TextCompare: false) != 0)
										{
											if (Operators.CompareString(name4, "sTelefono", TextCompare: false) == 0)
											{
												telefono = (string?)item4.Value;
											}
										}
										else
										{
											Nombre = (string?)item4.Value;
										}
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
						catch (Exception ex3)
						{
							ProjectData.SetProjectError(ex3);
							Exception ex4 = ex3;
							ProjectData.ClearProjectError();
						}
					}
					else if (!((string?)item2.Value == "Exito"))
					{
					}
				}
			}
			if (Nombre.Length > 0)
			{
				return true;
			}
			return false;
		}
		return false;
	}

	public void asignarRepartidorAPedido(string orderId, int carrierID)
	{
		if (VariableGeneral.gQuestTag.Length <= 0)
		{
			return;
		}
		HttpWebRequest obj = (HttpWebRequest)WebRequest.Create("https://api.shipday.com/orders/assign/" + orderId + "/" + Conversions.ToString(carrierID));
		obj.Headers.Add("Authorization: Basic " + VariableGeneral.gQuestTag);
		obj.Method = "PUT";
		using WebResponse webResponse = obj.GetResponse();
		using StreamReader streamReader = new StreamReader(webResponse.GetResponseStream());
		string text = streamReader.ReadToEnd();
		text = "{\"registros\":" + text + "}";
		List<JToken> list = JObject.Parse(text).Children().ToList();
		foreach (JProperty item in list)
		{
			item.CreateReader();
			string name = item.Name;
			if (Operators.CompareString(name, "registros", TextCompare: false) != 0)
			{
				continue;
			}
			foreach (JProperty item2 in item.Values())
			{
				string name2 = item2.Name;
				if (Operators.CompareString(name2, "success", TextCompare: false) == 0 && !(bool)item2.Value)
				{
					Interaction.MsgBox("Hubo un error al asignar");
				}
			}
		}
	}

	public bool cancelarOrden(int orderId, ref string error1)
	{
		try
		{
			if (VariableGeneral.gQuestTag.Length > 0)
			{
				HttpWebRequest obj = (HttpWebRequest)WebRequest.Create("https://dispatch.shipday.com/Orders/" + Conversions.ToString(orderId));
				obj.Headers.Add("Authorization: Basic " + VariableGeneral.gQuestTag);
				obj.Method = "DELETE";
				using WebResponse webResponse = obj.GetResponse();
				using StreamReader streamReader = new StreamReader(webResponse.GetResponseStream());
				string text = streamReader.ReadToEnd();
				text = "{\"registros\":" + text + "}";
				List<JToken> list = JObject.Parse(text).Children().ToList();
				foreach (JProperty item in list)
				{
					item.CreateReader();
					string name = item.Name;
					if (Operators.CompareString(name, "registros", TextCompare: false) != 0)
					{
						continue;
					}
					foreach (JProperty item2 in item.Values())
					{
						string name2 = item2.Name;
						if (Operators.CompareString(name2, "success", TextCompare: false) == 0)
						{
							if (!(bool)item2.Value)
							{
								Interaction.MsgBox("No se pudo cancelar esa orden");
								return false;
							}
							return true;
						}
					}
				}
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox(ex2.Message);
			ProjectData.ClearProjectError();
		}
		return false;
	}

	public bool crearNuevaCarrera(int ordenNro, ref string orderId, string cliente, string telefono, string direccion, double montoPedido, ref double delivery, DateTime HoraEntrega, double deliveryLat, double deliveryLon, string nombreFact, string Nit, ref string error1)
	{
		if (VariableGeneral.gQuestTag.Length > 0)
		{
			return crearNuevaCarrera_iVoy(ordenNro, ref orderId, cliente, telefono, direccion, montoPedido, ref delivery, HoraEntrega, deliveryLat, deliveryLon);
		}
		Interaction.MsgBox("No tiene con quien enviar");
		return false;
	}

	private bool crearNuevaCarrera_iVoy(int ordenNro, ref string orderId, string cliente, string telefono, string direccion, double montoPedido, ref double delivery, DateTime HoraEntrega, double deliveryLat, double deliveryLon)
	{
		string Nombre = "";
		int Telefono = 0;
		string Direccion = "";
		double lat = 0.0;
		double lon = 0.0;
		DevolverDireccionRestoParaRestomenu(ref Nombre, ref Direccion, ref Telefono, ref lat, ref lon, ref VariableGeneral.gQuestTag);
		if (VariableGeneral.gQuestTag.Length > 0)
		{
			Item item = new Item
			{
				name = "Sin detalle1",
				unitPrice = montoPedido,
				quantity = 1,
				addOns = new List<string> { "sin nada", "sin nada2" }
			};
			string value = JsonConvert.SerializeObject(new List<Item> { item });
			object obj = ((lat == 0.0) ? new Dictionary<string, string>
			{
				{
					"orderNumber",
					Conversions.ToString(ordenNro)
				},
				{ "customerName", cliente },
				{
					"customerPhoneNumber",
					"+591" + telefono
				},
				{ "customerEmail", "" },
				{ "customerAddress", direccion },
				{ "restaurantName", Nombre },
				{ "restaurantAddress", Direccion },
				{
					"restaurantPhoneNumber",
					"+591" + Conversions.ToString(Telefono)
				},
				{ "orderItem", value },
				{
					"totalOrderCost",
					Conversion.Str(montoPedido)
				},
				{ "tax", "0" },
				{ "tips", "0" },
				{
					"expectedDeliveryDate",
					HoraEntrega.ToString("yyyy-MM-dd")
				},
				{
					"expectedDeliveryTime",
					HoraEntrega.ToUniversalTime().ToString("HH:mm:ss")
				},
				{
					"deliveryFee",
					Conversion.Str(delivery)
				},
				{ "deliveryInstruction", "" },
				{
					"deliveryLatitude",
					Conversion.Str(deliveryLat)
				},
				{
					"deliveryLongitude",
					Conversion.Str(deliveryLon)
				}
			} : new Dictionary<string, string>
			{
				{
					"orderNumber",
					Conversions.ToString(ordenNro)
				},
				{ "customerName", cliente },
				{
					"customerPhoneNumber",
					"+591" + telefono
				},
				{ "customerEmail", "" },
				{ "customerAddress", direccion },
				{ "restaurantName", Nombre },
				{ "restaurantAddress", Direccion },
				{
					"restaurantPhoneNumber",
					"+591" + Conversions.ToString(Telefono)
				},
				{ "orderItem", value },
				{
					"totalOrderCost",
					Conversion.Str(montoPedido)
				},
				{ "tax", "0" },
				{ "tips", "0" },
				{
					"expectedDeliveryDate",
					HoraEntrega.ToString("yyyy-MM-dd")
				},
				{
					"expectedDeliveryTime",
					HoraEntrega.ToUniversalTime().ToString("HH:mm:ss")
				},
				{
					"deliveryFee",
					Conversion.Str(delivery)
				},
				{ "deliveryInstruction", "" },
				{
					"pickupLatitude",
					Conversion.Str(lat)
				},
				{
					"pickupLongitude",
					Conversion.Str(lon)
				},
				{
					"deliveryLatitude",
					Conversion.Str(deliveryLat)
				},
				{
					"deliveryLongitude",
					Conversion.Str(deliveryLon)
				}
			});
			HttpClient httpClient = new HttpClient();
			httpClient.DefaultRequestHeaders.Add("Accept", "application/*+xml;version=5.1");
			httpClient.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Basic", VariableGeneral.gQuestTag);
			FormUrlEncodedContent content = new FormUrlEncodedContent((IEnumerable<KeyValuePair<string, string>>)obj);
			string result = httpClient.PostAsync("https://dispatch.shipday.com/Orders", content).Result.Content.ReadAsStringAsync().Result;
			result = "{\"registros\":" + result + "}";
			List<JToken> list = JObject.Parse(result).Children().ToList();
			bool result2 = false;
			foreach (JProperty item2 in list)
			{
				item2.CreateReader();
				string name = item2.Name;
				if (Operators.CompareString(name, "registros", TextCompare: false) != 0)
				{
					continue;
				}
				foreach (JProperty item3 in item2.Values())
				{
					string name2 = item3.Name;
					if (Operators.CompareString(name2, "success", TextCompare: false) != 0)
					{
						if (Operators.CompareString(name2, "orderId", TextCompare: false) == 0)
						{
							orderId = (string?)item3.Value;
						}
					}
					else
					{
						result2 = (bool)item3.Value;
					}
				}
			}
			return result2;
		}
		return false;
	}

	public bool crearNuevaCarrera_Yaigo(int ordenNro, ref int orderId, string cliente, string telefono, string direccion, double montoPedido, ref double delivery, DateTime HoraEntrega, double deliveryLat, double deliveryLon, string nombreFact, string nit, ref string error1)
	{
		DataTable dataTable = BD.ConsultaVer("select * from CredencialesYaigo");
		if (dataTable.Rows.Count > 0)
		{
			string text = Conversions.ToString(dataTable.Rows[0]["sCodigoSucursal"]);
			string text2 = Conversions.ToString(dataTable.Rows[0]["sToken"]);
			if (text.Length > 0)
			{
				DetallePedido item = new DetallePedido
				{
					sProducto = "Sin detalle1",
					dPrecio = montoPedido,
					iCantidad = 1,
					sUnidadMedida = "",
					dMedida = 0.0,
					sObservacion = ""
				};
				string text3 = JsonConvert.SerializeObject(new List<DetallePedido> { item });
				string text4 = JsonConvert.SerializeObject(new Ubicacion
				{
					sLat = Conversion.Str(deliveryLat),
					sLng = Conversion.Str(deliveryLon),
					sDireccion = direccion,
					sReferencia = ""
				});
				string text5 = JsonConvert.SerializeObject(new Facturacion
				{
					sNombre = nombreFact,
					sNit = nit
				});
				string text6 = "{\r\"sCodigoSucursal\":\"" + text + "\",\r\"sToken\":\"" + text2 + "\",\r\"sCodTransaccion\":\"" + Conversions.ToString(orderId) + "\",\r\"sClienteNombre\":\"" + cliente + "\",\r\"sClienteMovil\":" + telefono + ",\r\"sClienteCod\":\"\",\r\"DetallePedido\":" + text3 + ",\r\"Ubicacion\":" + text4 + ",\r\"Facturacion\":" + text5 + ",\r}";
				WebClient obj = new WebClient
				{
					Headers = { ["content-type"] = "application/json" }
				};
				byte[] bytes = Encoding.Default.GetBytes(JsonConvert.SerializeObject(text6, Formatting.None));
				bytes = Encoding.UTF8.GetBytes(text6);
				byte[] bytes2 = obj.UploadData("http://207.180.235.65:8181/integ98k/api/integracion/pedidosolicitar", "post", bytes);
				string text7 = Encoding.Default.GetString(bytes2);
				text7 = "{\"registros\":" + text7 + "}";
				List<JToken> list = JObject.Parse(text7).Children().ToList();
				bool result = false;
				foreach (JProperty item2 in list)
				{
					item2.CreateReader();
					string name = item2.Name;
					if (Operators.CompareString(name, "registros", TextCompare: false) != 0)
					{
						continue;
					}
					foreach (JProperty item3 in item2.Values())
					{
						switch (item3.Name)
						{
						case "Titulo":
							if ((string?)item3.Value == "Exito")
							{
								result = true;
							}
							break;
						case "Data":
						{
							List<JToken> source = item3.Children().ToList();
							try
							{
								foreach (JProperty item4 in source.Values())
								{
									string name2 = item4.Name;
									if (Operators.CompareString(name2, "sCodigoTransaccion", TextCompare: false) != 0)
									{
										if (Operators.CompareString(name2, "dCostoEnvio", TextCompare: false) == 0)
										{
											delivery = (double)item4.Value;
										}
									}
									else
									{
										orderId = ordenNro;
									}
								}
							}
							catch (Exception ex)
							{
								ProjectData.SetProjectError(ex);
								Exception ex2 = ex;
								ProjectData.ClearProjectError();
							}
							break;
						}
						case "Mensaje":
							error1 = (string?)item3.Value;
							break;
						}
					}
				}
				return result;
			}
			return false;
		}
		return false;
	}
}
