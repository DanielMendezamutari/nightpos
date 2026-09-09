using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlVisitas
{
	public enum Visitas
	{
		ParaLlevar = 1,
		Recoge = 2,
		Auto = 4,
		Moto = 5,
		PedidosYa = 6
	}

	private readonly clsVisitas clsVis;

	public ctlVisitas()
	{
		clsVis = new clsVisitas();
	}

	public int GetID()
	{
		return clsVis._ID;
	}

	public DateTime GetFecha()
	{
		return clsVis._Fecha;
	}

	public int GetTipoEnvioId()
	{
		return clsVis._TipoEnvioID;
	}

	public void SetID(int ID)
	{
		clsVis._ID = ID;
	}

	public DataTable ToReturnVisitas()
	{
		return clsVis.ToReturn();
	}

	public void setParaLlevarID(int ID)
	{
		clsVis._ParaLlevarID = ID;
	}

	public int GetParaLlevarID()
	{
		return clsVis._ParaLlevarID;
	}

	public int GetPersonaSinMesaID()
	{
		return clsVis._PersonaSinMesaID;
	}

	public int GetMesasAdicionales()
	{
		return clsVis._MesaAdicionalID;
	}

	public bool hayCuentasAbiertas(ref bool ParaLlevar)
	{
		return clsVis.hayCuentasAbiertas(ref ParaLlevar);
	}

	public string NroCuentasAbiertas()
	{
		return clsVis.NroCuentasAbiertas();
	}

	public DataTable ToReturnVisitas(DateTime inicio, DateTime fin, bool Pedidos)
	{
		return clsVis.ToReturn(inicio, fin, Pedidos);
	}

	public DataTable ToReturnVisitasCompletas(DateTime inicio, DateTime fin, bool Pedidos)
	{
		return clsVis.ToReturnCompleto(inicio, fin, Pedidos);
	}

	public DataTable ToReturnVisitasNatulife(DateTime inicio, DateTime fin)
	{
		return clsVis.ToReturnNatulife(inicio, fin);
	}

	public DataTable ToReturnVisitasXcliente(int clienteID)
	{
		return clsVis.ToReturnVisitasXcliente(clienteID);
	}

	public void llenarclase(ref int MesaID, ref int ClienteID, ref string Obs)
	{
		string ClienteNombre = "";
		string ClienteApellido = "";
		clsVisitas obj = clsVis;
		string ClienteCodigo = "";
		obj.llenarclase(ref ClienteNombre, ref ClienteApellido, ref ClienteCodigo);
		MesaID = clsVis._MesaID;
		ClienteID = clsVis.ClienteID;
		Obs = clsVis._Observacion;
	}

	public string getCodigoCliente()
	{
		string ClienteCodigo = "";
		clsVisitas obj = clsVis;
		string ClienteNombre = "";
		string ClienteApellido = "";
		obj.llenarclase(ref ClienteNombre, ref ClienteApellido, ref ClienteCodigo);
		return ClienteCodigo;
	}

	public void CabecerSacNet(int visitaID, int agruparId, int facturaID, ref string NIT, ref string Nombre, ref int clientID, ref DateTime fecha, ref string email, ref int TipoDocumento, ref int formaPago, ref string nroTarjeta, ref string codigo, ref string Monto)
	{
		clsVis.CabecerSacNet(visitaID, agruparId, facturaID, ref NIT, ref Nombre, ref clientID, ref fecha, ref email, ref TipoDocumento, ref formaPago, ref nroTarjeta, ref codigo, ref Monto);
	}

	public bool DescuentoEnFactura(double descuento)
	{
		return clsVis.DescuentoEnFactura(descuento);
	}

	public bool Ocupar1(bool ocu)
	{
		clsVis._enMesa = ocu;
		return clsVis.Ocupar() != 0;
	}

	public bool estaenMesa()
	{
		return clsVis.estaenMesa();
	}

	public bool ToLoadLastVisitaActivaByMesaID(int mesaId)
	{
		DataTable dataTable = clsVis.ToReturnLastVisitaActivaByMesaID(mesaId);
		if (dataTable.Rows.Count == 0)
		{
			clsVis._ID = 0;
			return false;
		}
		clsVis._ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ID"]), 0));
		return true;
	}

	public int ToReturnLastVisitaActivaByMesaCodigo1(string CodigoMesa)
	{
		DataTable dataTable = clsVis.ToReturnLastVisitaActivaByMesaCodigo(CodigoMesa);
		if (dataTable.Rows.Count == 0)
		{
			clsVis._ID = 0;
			return 0;
		}
		clsVis._ID = Conversions.ToInteger(dataTable.Rows[0]["ID"]);
		clsVis._Fecha = Conversions.ToDate(dataTable.Rows[0]["Fecha"]);
		return 1;
	}

	public bool ClienteTieneVisitaHoy(int clienteID, int prodID)
	{
		clsVis.ClienteID = clienteID;
		DataTable dataTable = clsVis.ClienteTieneVisitaHoy(prodID);
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		if (DateTime.Compare(Conversions.ToDate(dataTable.Rows[0]["Fecha"]), DateAndTime.Today) < 0)
		{
			return false;
		}
		return true;
	}

	public bool ClienteTieneVisitaHoyPensionados(int clienteID)
	{
		clsVis.ClienteID = clienteID;
		DataTable dataTable = clsVis.ClienteTieneVisitaHoyPensionados();
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		if (DateTime.Compare(Conversions.ToDate(dataTable.Rows[0]["FechaUso"]), DateAndTime.Today) < 0)
		{
			return false;
		}
		return true;
	}

	public void responsabilizar(int clienteId)
	{
		clsVis.ClienteID = clienteId;
		clsVis.responsabilizar();
	}

	public bool esNuevoDia()
	{
		return clsVis.esNuevoDia();
	}

	public void UpdateFechaVisita()
	{
		clsVis.UpdateFechaVisita();
	}

	public void Save(DateTime Fecha, int mesaID, int PersonaSinMesaID, int ParaLlevarID, int MesaAdicionlesID, string observacion, int meseroID)
	{
		clsVis._Fecha = Fecha;
		clsVis._MesaID = mesaID;
		clsVis._MesaAdicionalID = MesaAdicionlesID;
		clsVis._PersonaSinMesaID = PersonaSinMesaID;
		clsVis._ParaLlevarID = ParaLlevarID;
		clsVis._Observacion = observacion;
		clsVis._TipoEnvioID = 0;
		clsVis.ClienteID = 0;
		if (clsVis._ID == 0)
		{
			clsVis.Insert();
			if (((configuration.gMesasVisibilidad == configuration.MesasVisibilidad.VeoMesasLibresMasMisMesas) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Hapo)) && ((mesaID > 0) & (meseroID > 0)))
			{
				clsMesas obj = new clsMesas();
				obj._ID = mesaID;
				obj._responsableID = meseroID;
				obj.cambiarMesero();
			}
			if (VariableGeneral.gSgteMesaVisible && mesaID > 0)
			{
				ctlMesas obj2 = new ctlMesas();
				obj2.SetID(mesaID);
				obj2.habilitarSgteMesa();
			}
		}
		else
		{
			clsVis.Modify();
		}
	}

	public bool TipoEnvioesMesa(int IDtipoenvio)
	{
		return clsVis.TipoEnvioesMesa(IDtipoenvio);
	}

	public bool TipoEnvioesPeYa()
	{
		return clsVis.TipoEnvioesPeYa();
	}

	public void ChangePAraLlevarId(object ParaLlevarID)
	{
		clsVis._ParaLlevarID = Conversions.ToInteger(ParaLlevarID);
		clsVis.ChangePAraLlevarId();
	}

	public void cambiarMesa(int mesaID)
	{
		if (clsVis._ID > 0)
		{
			clsVis._MesaID = mesaID;
			clsVis.cambiarMesa();
		}
	}

	public void juntarCuentas(int visitaId)
	{
		if (clsVis._ID > 0)
		{
			clsVis.juntarCuentas(visitaId);
			clsVis._enMesa = false;
			clsVis.Ocupar();
			if (new ctlConfiguraciones().manejaServicio())
			{
				clsDetalleCuenta obj = new clsDetalleCuenta();
				obj._VisitaID = visitaId;
				obj.eliminarServicio();
				obj._ProductoID = 1;
				double MontoDevuelto = 0.0;
				int CuentaId = 0;
				obj.modificarServicio(con10porcent: true, ref MontoDevuelto, ref CuentaId);
			}
		}
	}

	public void Delete()
	{
		clsVis.Delete();
	}

	public bool VerificarFecha()
	{
		return clsVis.VerificarFecha();
	}

	public void UpdateImprimioCuenta(int idVisita, int meseroID)
	{
		clsVis._ID = idVisita;
		clsVis.UpdateImprimioCuenta(meseroID);
	}

	public void UpdateImprimioFactura(int idVisita, int meseroID)
	{
		clsVis._ID = idVisita;
		clsVis.UpdateImprimioFactura(meseroID);
	}

	public bool DevolverImprimioCuenta(int idVisita)
	{
		clsVis._ID = idVisita;
		return clsVis.DevolverImprimioCuenta();
	}

	public bool DevolverUsoServicio(int idVisita)
	{
		clsVis._ID = idVisita;
		return clsVis.DevolverUsoServicio();
	}

	public string DevolverQuienImprimioCuenta(int idVisita)
	{
		clsVis._ID = idVisita;
		return clsVis.DevolverQuienImprimioCuenta();
	}

	public int DevolverclienteID(int idVisita)
	{
		clsVis._ID = idVisita;
		return clsVis.DevolverclienteID();
	}

	public int yaAbrioHoy(int idMesa)
	{
		DateTime FechaIni = default(DateTime);
		new clsTurnos().getultimaInfoCualquierPC(ref FechaIni);
		clsVis._MesaID = idMesa;
		return 0 - (clsVis.yaAbrioHoy(FechaIni) ? 1 : 0);
	}

	public int BuscarParaLlevarID(int idVisita)
	{
		clsVis._ID = idVisita;
		return clsVis.BuscarParaLLevarID();
	}

	public void ModificarObservacion(string observacion)
	{
		clsVis._Observacion = observacion;
		if (clsVis._ID > 0)
		{
			clsVis.ModificarObservacion();
		}
	}

	public void ModificarIdentificador(string Identificador)
	{
		if (clsVis._ID > 0)
		{
			clsVis.ModificarIdentificador(Identificador);
		}
	}

	public string getIdentificadorCelularXnroTurno(string nroTurno)
	{
		string[] array = clsVis.getIdentificadorXnroTurno(nroTurno).Split('|');
		if (array.Length >= 2)
		{
			return array[1];
		}
		return "";
	}

	public string DevolverTipoEnvio(int IDvisita)
	{
		clsVis._ID = IDvisita;
		return clsVis.DevolverTipoEnvio();
	}

	public string DevolverTipoEnvioxTipoID(int IDtipoenvio)
	{
		clsVis._TipoEnvioID = IDtipoenvio;
		return clsVis.DevolverTipoEnvioxTipoID();
	}

	public int DevolverObservacionNroCuenta(DateTime fecha)
	{
		return clsVis.DevolverObservacionNroCuenta(fecha);
	}

	public void setTipoEnvio(int tipoEnvio)
	{
		clsVis._TipoEnvioID = tipoEnvio;
		clsVis.ModificarTipoEnvio();
	}

	public int DevolverMeseroID()
	{
		return clsVis.DevolverMeseroID();
	}
}
