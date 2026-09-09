using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsConfiguraciones
{
	private int IdConfiguracion;

	private bool ConPorcentaje;

	private double Porcentaje;

	private string NombreEmpresa;

	private string Sucursal;

	private string Direccion;

	private string Municipio;

	private string PCpedidos;

	private string Telefono;

	private string Dueño;

	private int NroOrden;

	private string Email;

	private string ActividadEconomica;

	private string Comentario;

	private string Ley;

	private bool soloAdminBorra;

	public string Descripcion;

	public int _FacturaBucle;

	public bool _CantidadPersonas;

	public bool DatosFacturas;

	public bool DobleFactura;

	public bool FacturaBackupArchivo;

	public bool ImprimeOtrasCuentas;

	public bool CuentaFormatoFactura;

	public bool FacturacionExpress;

	public double _IdConfiguracion
	{
		get
		{
			return IdConfiguracion;
		}
		set
		{
			IdConfiguracion = checked((int)Math.Round(value));
		}
	}

	public double _Porcentaje
	{
		get
		{
			return Porcentaje;
		}
		set
		{
			Porcentaje = value;
		}
	}

	public bool _soloAdminBorra
	{
		get
		{
			return soloAdminBorra;
		}
		set
		{
			soloAdminBorra = value;
		}
	}

	public bool _ConPorcentaje
	{
		get
		{
			return ConPorcentaje;
		}
		set
		{
			ConPorcentaje = value;
		}
	}

	public string _NombreEmpresa
	{
		get
		{
			return NombreEmpresa;
		}
		set
		{
			NombreEmpresa = value;
		}
	}

	public string _PCpedidos
	{
		get
		{
			return PCpedidos;
		}
		set
		{
			PCpedidos = value;
		}
	}

	public string _Ley
	{
		get
		{
			return Ley;
		}
		set
		{
			Ley = value;
		}
	}

	public string _Descripcion
	{
		get
		{
			return Descripcion;
		}
		set
		{
			Descripcion = value;
		}
	}

	public string _Sucursal
	{
		get
		{
			return Sucursal;
		}
		set
		{
			Sucursal = value;
		}
	}

	public string _Municipio
	{
		get
		{
			return Municipio;
		}
		set
		{
			Municipio = value;
		}
	}

	public string _Direccion
	{
		get
		{
			return Direccion;
		}
		set
		{
			Direccion = value;
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

	public string _Dueño
	{
		get
		{
			return Dueño;
		}
		set
		{
			Dueño = value;
		}
	}

	public int _NroOrden
	{
		get
		{
			return NroOrden;
		}
		set
		{
			NroOrden = value;
		}
	}

	public string _Email
	{
		get
		{
			return Email;
		}
		set
		{
			Email = value;
		}
	}

	public string _ActividadEconomica
	{
		get
		{
			return ActividadEconomica;
		}
		set
		{
			ActividadEconomica = value;
		}
	}

	public string _Comentario
	{
		get
		{
			return Comentario;
		}
		set
		{
			Comentario = value;
		}
	}

	public bool _DobleFactura
	{
		get
		{
			return DobleFactura;
		}
		set
		{
			DobleFactura = value;
		}
	}

	public bool _FacturaBackupArchivo
	{
		get
		{
			return FacturaBackupArchivo;
		}
		set
		{
			FacturaBackupArchivo = value;
		}
	}

	public bool _FacturacionExpress
	{
		get
		{
			return FacturacionExpress;
		}
		set
		{
			FacturacionExpress = value;
		}
	}

	public DataTable devolverConfiguraciones()
	{
		new DataTable();
		return BD.ConsultaVer("idConfiguracion,Descripcion", "Configuraciones");
	}

	public int devolverCantConfirguraciones()
	{
		new DataTable();
		return BD.ConsultaVer("*", "Configuraciones").Rows.Count;
	}

	public bool CerrarTurnoMesasAbiertas()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("CerrarTurnoMesasAbiertas", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CerrarTurnoMesasAbiertas"]), false));
		}
		return false;
	}

	public bool DevolverImprimeOtrasCuentas()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("ImprimeOtrasCuentas", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ImprimeOtrasCuentas"]), false));
		}
		return false;
	}

	public bool DevolverSoloAdminBorra()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("soloAdminBorra", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["soloAdminBorra"]), false));
		}
		return false;
	}

	public void DevolverDatosFacturas()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("DatosFacturas", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			DatosFacturas = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DatosFacturas"]), false));
			return;
		}
		DatosFacturas = false;
		Interaction.MsgBox("No hay configuracion");
	}

	public void Devolver()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("IdConfiguracion,ConPorcentaje,Porcentaje,NombreEmpresa,Sucursal,Direccion,Telefono,Dueño,NroOrden,Email,ActividadEconomica,Comentario,Ley,soloAdminBorra,Descripcion,CantidadPersonas,FacturaBucle,DatosFacturas,DobleFactura,FacturaBackupArchivo,FacturacionExpress,municipio,PCpedidos,CuentaFormatoFactura", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			IdConfiguracion = Conversions.ToInteger(dataTable.Rows[0]["IdConfiguracion"]);
			ConPorcentaje = Conversions.ToBoolean(dataTable.Rows[0]["ConPorcentaje"]);
			Porcentaje = Conversions.ToDouble(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["porcentaje"]), 0));
			NombreEmpresa = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreEmpresa"]), ""));
			Sucursal = Conversions.ToString(dataTable.Rows[0]["Sucursal"]);
			Direccion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Direccion"]), ""));
			Telefono = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Telefono"]), ""));
			Dueño = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Dueño"]), ""));
			Email = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Email"]), ""));
			ActividadEconomica = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["ActividadEconomica"]), ""));
			Comentario = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Comentario"]), ""));
			Ley = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Ley"]), ""));
			_soloAdminBorra = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["soloAdminBorra"]), false));
			Descripcion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"]), ""));
			_CantidadPersonas = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CantidadPersonas"]), false));
			_FacturaBucle = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaBucle"]), 0));
			DatosFacturas = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DatosFacturas"]), false));
			DobleFactura = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DobleFactura"]), false));
			FacturaBackupArchivo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaBackupArchivo"]), false));
			FacturacionExpress = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturacionExpress"]), false));
			Municipio = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Municipio"]), ""));
			PCpedidos = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PCpedidos"]), ""));
			CuentaFormatoFactura = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CuentaFormatoFactura"]), ""));
		}
		else
		{
			IdConfiguracion = 0;
			ConPorcentaje = false;
			Porcentaje = 0.0;
			NombreEmpresa = "";
			Sucursal = "";
			Direccion = "";
			Telefono = "";
			Dueño = "";
			Email = "";
			ActividadEconomica = "";
			Comentario = "";
			Ley = "";
			soloAdminBorra = false;
			Descripcion = "";
			_CantidadPersonas = false;
			_FacturaBucle = 0;
			DatosFacturas = false;
			DobleFactura = false;
			FacturaBackupArchivo = false;
			FacturacionExpress = false;
			Municipio = "";
			PCpedidos = "";
			ImprimeOtrasCuentas = false;
			CuentaFormatoFactura = false;
			Interaction.MsgBox("No hay configuracion");
		}
	}

	public int devolverVersion()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("AppVersion", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(dataTable.Rows[0]["AppVersion"]);
		}
		return -1;
	}

	public string devolverPCpedidos()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("PCpedidos", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["PCpedidos"]), ""));
		}
		return "";
	}

	public int devolverCuentaFormatoFactura()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("CuentaFormatoFactura", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["CuentaFormatoFactura"]), 0));
		}
		return 0;
	}

	public string devolverDescripcion()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("Descripcion", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"]), ""));
		}
		return "";
	}

	public bool devolverFacturarPropina()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("FacturarPropina", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturarPropina"]), false));
		}
		return false;
	}

	public void DevolverNroOrden()
	{
		try
		{
			DataTable dataTable = new DataTable();
			dataTable = BD.ConsultaVer("NroOrden", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
			if (dataTable.Rows.Count > 0)
			{
				NroOrden = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NroOrden"]), 0));
			}
			else
			{
				NroOrden = 0;
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			NroOrden = 0;
			ProjectData.ClearProjectError();
		}
	}

	public void ModificarNroOrden()
	{
		BD.ConsultaModificar("Configuraciones", "NroOrden='" + Conversions.ToString(NroOrden) + "'", "1=1");
	}

	public int devolverFacturaBucle()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("FacturaBucle", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaBucle"]), 0));
		}
		return 0;
	}

	public void GuardarFacturaBucle(int cant)
	{
		BD.ConsultaModificar("Configuraciones", ("FacturaBucle=" + Conversions.ToString(cant)) ?? "", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public void GuardarFacturaXwhatsapp(bool doble)
	{
		BD.ConsultaModificar("Configuraciones", ("FacturaXwhatsapp=" + VariableGeneral.armarBolean(doble)) ?? "", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public void GuardarDobleCuenta(bool doble)
	{
		BD.ConsultaModificar("Configuraciones", ("DobleCuentaParaLlevar=" + VariableGeneral.armarBolean(doble)) ?? "", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public void GuardarFacturarPropina(bool si)
	{
		BD.ConsultaModificar("Configuraciones", ("FacturarPropina=" + VariableGeneral.armarBolean(si)) ?? "", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public void GuardarClienteParaLlevar(bool si)
	{
		BD.ConsultaModificar("Configuraciones", ("GuardarClienteParaLlevar=" + VariableGeneral.armarBolean(si)) ?? "", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public void GuardarCuentaFormatoFactura(int opcion, int config)
	{
		BD.ConsultaModificar("Configuraciones", ("CuentaFormatoFactura=" + Conversions.ToString(opcion)) ?? "", "IdConfiguracion=" + Conversions.ToString(config));
	}

	public void GuardarPCpedidos(string PCpedidos)
	{
		BD.ConsultaModificar("Configuraciones", "PCpedidos='" + PCpedidos + "'", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public bool devolverFacturaXWhatsapp()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("FacturaXWhatsapp", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["FacturaXWhatsapp"]), false));
		}
		return false;
	}

	public bool devolverDobleCuenta()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("DobleCuentaParaLlevar", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["DobleCuentaParaLlevar"]), false));
		}
		return false;
	}

	public bool devolverGuardarCliente()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("GuardarClienteParaLlevar", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["GuardarClienteParaLlevar"]), false));
		}
		return false;
	}

	public void Modificar()
	{
		BD.ConsultaModificar("Configuraciones", "ImprimeOtrasCuentas=" + VariableGeneral.armarBolean(ImprimeOtrasCuentas) + ",DatosFacturas=" + VariableGeneral.armarBolean(DatosFacturas) + ", CantidadPersonas =" + VariableGeneral.armarBolean(_CantidadPersonas) + " ,ConPorcentaje=" + VariableGeneral.armarBolean(ConPorcentaje) + " ,SoloAdminBorra=" + VariableGeneral.armarBolean(soloAdminBorra) + " ,Porcentaje=" + Conversion.Str(Porcentaje) + " ,NombreEmpresa='" + NombreEmpresa + "',Sucursal='" + Sucursal + "',Direccion='" + Direccion + "',Municipio='" + Municipio + "',Telefono='" + Telefono + "',Dueño='" + Dueño + "',Email='" + Email + "',ActividadEconomica='" + ActividadEconomica + "',Comentario='" + Comentario + "',Ley='" + Ley + "',DobleFactura=" + VariableGeneral.armarBolean(DobleFactura) + " ,FacturaBackupArchivo = " + VariableGeneral.armarBolean(FacturaBackupArchivo) + " ,FacturacionExpress = " + VariableGeneral.armarBolean(FacturacionExpress), "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
	}

	public void IniciarBaseDatos()
	{
		BD.ConsultaEliminar("Borrados", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Borrados, reseed, 1)");
		}
		BD.ConsultaEliminar("ProductosUsos", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (ProductosUsos, reseed, 1)");
		}
		BD.ConsultaEliminar("PreprocesamientosPara", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (PreprocesamientosPara, reseed, 1)");
		}
		BD.ConsultaEliminar("PreprocesamientosDe", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (PreprocesamientosDe, reseed, 1)");
		}
		BD.ConsultaEliminar("Preprocesamientos", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Preprocesamientos, reseed, 1)");
		}
		if (configuration.gStyleBoliches1 != configuration.styleBolichesId.EspigaDeOro)
		{
			BD.ConsultaEliminar("DetallesTraspasos", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DetallesTraspasos, reseed, 1)");
			}
			BD.ConsultaEliminar("Traspasos", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Traspasos, reseed, 1)");
			}
		}
		BD.ConsultaEliminar("Gastos_Facturas", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Gastos_Facturas, reseed, 1)");
		}
		BD.ConsultaEliminar("BlackList", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (BlackList, reseed, 1)");
		}
		BD.ConsultaEliminar("Pagos", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Pagos, reseed, 1)");
		}
		BD.ConsultaEliminar("Movimientos_Turnos", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Movimientos_Turnos, reseed, 1)");
		}
		BD.ConsultaEliminar("Movimientos", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Movimientos, reseed, 1)");
		}
		BD.ConsultaEliminar("Turnos", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Turnos, reseed, 1)");
		}
		BD.ConsultaEliminar("Facturas", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (facturas, reseed, 1)");
		}
		BD.ConsultaEliminar("Facturas2", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Facturas2, reseed, 1)");
		}
		BD.ConsultaEliminar("ProductosCombos", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (ProductosCombos, reseed, 1)");
		}
		BD.ConsultaEliminar("UsoPaquetes", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (UsoPaquetes, reseed, 1)");
		}
		BD.ConsultaEliminar("DetalleCuentas_Paquetes", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DetalleCuentas_Paquetes, reseed, 1)");
		}
		BD.ConsultaEliminar("DetalleCuenta_Asistentes", "1=1");
		BD.ConsultaEliminar("Observaciones", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Observaciones, reseed, 1)");
		}
		BD.ConsultaEliminar("ObservacionesCocina", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (ObservacionesCocina, reseed, 1)");
		}
		BD.ConsultaEliminar("DetalleCuenta", "1=1");
		if (configuration.gMODO_ACCESS == 0)
		{
			BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DetalleCuenta, reseed, 1)");
		}
		checked
		{
			if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.EspigaDeOro) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Ciberal))
			{
				DataTable dataTable = new ctlAlmacenes().DevolverTodosAlmacenesInternos(-1);
				int num = dataTable.Rows.Count - 1;
				for (int i = 0; i <= num; i++)
				{
					BD.ConsultaModificar("Productos", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject("Stock", dataTable.Rows[i][0]), "=0")), "1=1");
				}
			}
			if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.SanTelmo) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.MariaDeMolina) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.EspigaDeOro) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Ciberal) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Serendipity))
			{
				BD.ConsultaModificar("Productos", "Costo=0", "1=1");
			}
			BD.ConsultaEliminar("DetalleCuentaIntermediaria", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DetalleCuentaIntermediaria, reseed, 1)");
			}
			BD.ConsultaEliminar("ParaLlevar", "ParallevarID>5");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (ParallevarID, reseed, 6)");
			}
			BD.ConsultaEliminar("PersonasSinMesa", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (PersonasSinMesa, reseed, 1)");
			}
			BD.ConsultaEliminar("MesasAdicionales", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (MesasAdicionales, reseed, 1)");
			}
			BD.ConsultaEliminar("Visitas", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Visitas, reseed, 1)");
			}
			BD.ConsultaEliminar("ProgramacionServicio", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (ProgramacionServicio, reseed, 1)");
			}
			BD.ConsultaEliminar("Programacion", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Programacion, reseed, 1)");
			}
			if (!((configuration.gStyleBoliches1 == configuration.styleBolichesId.LaCastañuela) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.AguaViva1) | ((configuration.gStyleBoliches1 == configuration.styleBolichesId.FastTaste) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.CateringSacherCorp)) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.TorrezSoliz) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.GESA) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.EspigaDeOro) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.KIKY) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Boulangerie) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.SaintGeorge) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.Sacura)))
			{
				BD.ConsultaEliminar("Clientes", "1=1");
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Clientes, reseed, 1)");
				}
			}
			BD.ConsultaEliminar("Gastos", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Gastos, reseed, 1)");
			}
			if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.MicromercadoCercaTuyo) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.MicromercadoPasse) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.EspigaDeOro))
			{
				BD.ConsultaEliminar("DetalleProductosCompra", "1=1");
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DetalleProductosCompra, reseed, 1)");
				}
				BD.ConsultaEliminar("Compras ", "1=1");
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("DBCC CHECKIDENT (compras, reseed, 1)");
				}
				BD.ConsultaEliminar("Proveedores", "1=1");
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Proveedores, reseed, 1)");
				}
			}
			if (configuration.gStyleBoliches1 != configuration.styleBolichesId.EspigaDeOro)
			{
				BD.ConsultaEliminar("DetallesAjustes", "1=1");
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DetallesAjustes, reseed, 1)");
				}
				BD.ConsultaEliminar("Ajustes", "1=1");
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Ajustes, reseed, 1)");
				}
			}
			BD.ConsultaEliminar("DetallesProduccion", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DetallesProduccion, reseed, 1)");
			}
			BD.ConsultaEliminar("Produccion", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Produccion, reseed, 1)");
			}
			BD.ConsultaModificar("CodigosFacturas", "Nrofactura=0", "1=1");
			if (configuration.gPeluqueria | configuration.gGimnasio)
			{
				BD.ConsultaEliminar("ProgramacionServicio", "1=1");
				BD.ConsultaEliminar("Programacion", "1=1");
				BD.ConsultaEliminar("UsoPaquetes", "1=1");
				BD.ConsultaEliminar("DetalleCuentas_Paquetes", "1=1");
			}
			if (configuration.gStyleBoliches1 != configuration.styleBolichesId.GloboLoco && configuration.gSoloFacturacionGrande)
			{
				BD.ConsultaEliminar("Productos", "1=1");
			}
			if ((configuration.gStyleBoliches1 == configuration.styleBolichesId.MicromercadoCercaTuyo) | (configuration.gStyleBoliches1 == configuration.styleBolichesId.MicromercadoPasse))
			{
				BD.ConsultaModificar("DetalleProductosCompra", "FechaEntrega= " + VariableGeneral.ArmarFecha(DateAndTime.Today), "1=1");
				BD.ConsultaModificar("DetalleProductosCompra, Productos", "Productos.Stock =DetalleProductosCompra.Cantidad ,Productos.Costo=DetalleProductosCompra.costoUnitario", "DetalleProductosCompra.ProductoID=Productos.ID");
			}
			_ = configuration.gStyleBoliches1;
			_ = 101;
			BD.ConsultaEliminar("ObservacionesCocina", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (ObservacionesCocina, reseed, 1)");
			}
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Restomenu)
			{
				BD.ConsultaEliminar("Productos", "id>3");
			}
			if ((configuration.gStyleBoliches1 != configuration.styleBolichesId.Boulangerie) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.Sacura) & (configuration.gStyleBoliches1 != configuration.styleBolichesId.KIKY))
			{
				BD.ConsultaEliminar("Descuentos", "1=1");
				if (configuration.gMODO_ACCESS == 0)
				{
					BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Descuentos, reseed, 1)");
				}
			}
			BD.ConsultaEliminar("Logg", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Logg, reseed, 1)");
			}
			BD.ConsultaEliminar("Anticipos", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Anticipos, reseed, 1)");
			}
			BD.ConsultaEliminar("Arqueo", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (Arqueo, reseed, 1)");
			}
			BD.ConsultaEliminar("DeliveryApp_Detalle_Combo", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DeliveryApp_Detalle_Combo, reseed, 1)");
			}
			BD.ConsultaEliminar("DeliveryApp_Detalle", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DeliveryApp_Detalle, reseed, 1)");
			}
			BD.ConsultaEliminar("DeliveryApp", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (DeliveryApp, reseed, 1)");
			}
			BD.ConsultaEliminar("FactElectCUFD", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (FactElectCUFD, reseed, 1)");
			}
			BD.ConsultaEliminar("FactElectCUIS", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (FactElectCUIS, reseed, 1)");
			}
			BD.ConsultaEliminar("FactElectFueraLinea", "1=1");
			if (configuration.gMODO_ACCESS == 0)
			{
				BD.ConsultWithOutAlerts("DBCC CHECKIDENT (FactElectFueraLinea, reseed, 1)");
			}
		}
	}

	public DataTable DevolverCaberceraReporte1()
	{
		return BD.ConsultaVer("Configuraciones.NombreEmpresa, Configuraciones.Direccion, CodigosFacturas.NIT, Configuraciones.Sucursal", "  Configuraciones,  CodigosFacturas", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID), "", "Configuraciones.NombreEmpresa, Configuraciones.Direccion, CodigosFacturas.NIT, Configuraciones.Sucursal");
	}

	public DataTable DevolverCaberceraReporte2()
	{
		DataTable dataTable = BD.ConsultaVer("cuentaID", "Cuentas", "Nombre like 'DEPOSITOS'");
		if (dataTable.Rows.Count == 0)
		{
			Interaction.MsgBox("no existe cuenta 'DEPOSITOS'");
			return new DataTable();
		}
		return BD.ConsultaVer("Configuraciones.Dueño,Cuentas.Banco, Cuentas.TipoCuenta, Cuentas.Nro,Cuentas.Moneda, Configuraciones.Sucursal", " Cuentas,Configuraciones", Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(" CuentaID= ", dataTable.Rows[0][0]), " and IdConfiguracion="), VariableGeneral.gConfiguracionID)));
	}

	public void DevolverComentario()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("Comentario", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			Comentario = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Comentario"]), ""));
			Comentario = "";
			Interaction.MsgBox("No hay configuracion");
		}
	}

	public bool DevolverDobleFactura()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("DobleFactura", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToBoolean(dataTable.Rows[0]["DobleFactura"]);
		}
		return false;
	}

	public bool DevolverFacturacionExpress()
	{
		bool result;
		try
		{
			DataTable dataTable = new DataTable();
			dataTable = BD.ConsultaVer("FacturacionExpress", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
			result = dataTable.Rows.Count > 0 && Conversions.ToBoolean(dataTable.Rows[0]["FacturacionExpress"]);
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

	public bool DevolverFacturaBackupArchivo()
	{
		DataTable dataTable = new DataTable();
		dataTable = BD.ConsultaVer("FacturaBackupArchivo", "Configuraciones", "IdConfiguracion=" + Conversions.ToString(VariableGeneral.gConfiguracionID));
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToBoolean(dataTable.Rows[0]["FacturaBackupArchivo"]);
		}
		return false;
	}
}
