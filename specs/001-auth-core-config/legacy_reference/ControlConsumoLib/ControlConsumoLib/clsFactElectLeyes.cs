using System;
using System.Data;
using System.Net;
using System.Runtime.CompilerServices;
using System.Threading;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsFactElectLeyes
{
	private int ID;

	private string Codigo1;

	private string Descripcion;

	[SpecialName]
	private Random _0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator;

	[SpecialName]
	private StaticLocalInitFlag _0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator_0024Init;

	public int _ID
	{
		get
		{
			return ID;
		}
		set
		{
			ID = value;
		}
	}

	public string _Codigo1
	{
		get
		{
			return Codigo1;
		}
		set
		{
			Codigo1 = value;
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

	public clsFactElectLeyes()
	{
		ServicePointManager.SecurityProtocol = SecurityProtocolType.Tls12;
	}

	public int getMAxId()
	{
		DataTable dataTable = BD.ConsultaVer("max(id)", "FactElectLeyes");
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
		}
		return 0;
	}

	public void DevolverLey()
	{
		if (configuration.gTipoFacturacion == 1)
		{
			ID = 0;
			Codigo1 = "0";
			Descripcion = "";
			return;
		}
		DataTable dataTable = BD.ConsultaVer("id, Codigo, Descripcion", "FactElectLeyes", "id=" + Conversions.ToString(ID));
		if (dataTable.Rows.Count > 0)
		{
			ID = Conversions.ToInteger(dataTable.Rows[0]["id"]);
			Codigo1 = Conversions.ToString(dataTable.Rows[0]["Codigo"]);
			Descripcion = Conversions.ToString(dataTable.Rows[0]["Descripcion"]);
		}
		else
		{
			ID = 0;
			Codigo1 = "0";
			Descripcion = "";
		}
	}

	public void DevolverRandomLey(int CodigoAmbiente)
	{
		DataTable dataTable = BD.ConsultaVer("id, Codigo, Descripcion", "FactElectLeyes", (Operators.CompareString(Codigo1, "0", TextCompare: false) != 0) ? ("codigo like '" + Codigo1 + "'") : ("ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID)));
		if (dataTable.Rows.Count == 0)
		{
			Interaction.MsgBox("No hay leyes, intentaremos actualizar los catalogos");
			clsFacElecSyncDatos clsFacElecSyncDatos2 = new clsFacElecSyncDatos(CodigoAmbiente);
			if (clsFacElecSyncDatos2.verificarComunicacion())
			{
				clsFacElecSyncDatos2.SyncLeyes(esTest: false);
				clsFacElecSyncDatos2.SyncActividades(esTest: false);
				clsFacElecSyncDatos2.SyncUnidadMedida(esTest: false);
				clsFacElecSyncDatos2.SyncCodigoProductos(esTest: false);
				dataTable = BD.ConsultaVer("id, Codigo, Descripcion", "FactElectLeyes", (Operators.CompareString(Codigo1, "0", TextCompare: false) != 0) ? ("codigo like '" + Codigo1 + "'") : ("ConfiguracionID=" + Conversions.ToString(VariableGeneral.gConfiguracionID)));
			}
		}
		if (dataTable.Rows.Count == 0)
		{
			Interaction.MsgBox("No hay leyes");
			return;
		}
		int index = GenRandomInt(0, checked(dataTable.Rows.Count - 1));
		ID = Conversions.ToInteger(dataTable.Rows[index]["id"]);
		Codigo1 = Conversions.ToString(dataTable.Rows[index]["Codigo"]);
		Descripcion = Conversions.ToString(dataTable.Rows[index]["Descripcion"]);
	}

	private int GenRandomInt(int min, int max)
	{
		if (_0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator_0024Init == null)
		{
			Interlocked.CompareExchange(ref _0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator_0024Init, new StaticLocalInitFlag(), null);
		}
		bool lockTaken = false;
		try
		{
			Monitor.Enter(_0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator_0024Init, ref lockTaken);
			if (_0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator_0024Init.State == 0)
			{
				_0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator_0024Init.State = 2;
				_0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator = new Random();
			}
			else if (_0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator_0024Init.State == 2)
			{
				throw new IncompleteInitialization();
			}
		}
		finally
		{
			_0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator_0024Init.State = 1;
			if (lockTaken)
			{
				Monitor.Exit(_0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator_0024Init);
			}
		}
		return _0024STATIC_0024GenRandomInt_0024202888_0024staticRandomGenerator.Next(min, checked(max + 1));
	}

	public int Insertar()
	{
		BD.ConsultaInsertar3(Conversions.ToString(ID) + ",'" + Codigo1 + "','" + Descripcion + "'," + Conversions.ToString(VariableGeneral.gConfiguracionID), "FactElectLeyes(id,Codigo,Descripcion,ConfiguracionID)", ref ID);
		return ID;
	}

	public int EliminarTodo()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("FactElectLeyes", "1=1") == 0)
			{
				Interaction.MsgBox("no se puede eliminar el Borrado, se encuentra en uso");
				result = 0;
			}
			else
			{
				result = 1;
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
}
