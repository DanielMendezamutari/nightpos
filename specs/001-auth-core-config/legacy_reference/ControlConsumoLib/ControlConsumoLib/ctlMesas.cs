using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlMesas
{
	public enum Mesas
	{
		Barra = 1
	}

	private readonly clsMesas clsMes;

	public ctlMesas()
	{
		clsMes = new clsMesas();
	}

	public int GetID()
	{
		return clsMes._ID;
	}

	public void SetID(int ID)
	{
		clsMes._ID = ID;
	}

	public string GetResponsableNombre()
	{
		return clsMes._responsableNombre;
	}

	public string GetNombre()
	{
		return clsMes._Nombre;
	}

	public string GetDescripcion()
	{
		return clsMes._Descripcion;
	}

	public void setDescripcion(string desc)
	{
		clsMes._Descripcion = desc;
		clsMes.setDescripcion();
	}

	public void setActivo(string desc, bool activo)
	{
		clsMes._Descripcion = desc;
		clsMes.setActivo(activo);
	}

	public void SetCodigo(string cod)
	{
		clsMes._Codigo = cod;
	}

	public int GetResponsable()
	{
		return clsMes._responsableID;
	}

	public string getCodigo()
	{
		return clsMes._Codigo;
	}

	public int getSimboloID()
	{
		return clsMes.getSimboloID();
	}

	public bool ToreturnMesasPorCodigo()
	{
		DataTable dataTable = clsMes.ToreturnMesasPorCodigo1();
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		clsMes._ID = Conversions.ToInteger(dataTable.Rows[0]["ID"]);
		clsMes._Codigo = Conversions.ToString(dataTable.Rows[0]["Codigo"]);
		clsMes._Nombre = Conversions.ToString(dataTable.Rows[0]["Nombre"]);
		clsMes._Descripcion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"]), ""));
		clsMes._responsableID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Responsable"]), 0));
		clsMes._responsableNombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreMesero"]), ""));
		return true;
	}

	public void habilitarSgteMesa()
	{
		loadMesaPorID();
		string[] array = GetNombre().Split('-');
		if (array.Length > 1)
		{
			string text = array[0];
			if (Versioned.IsNumeric(array[1]))
			{
				int num = checked((int)Math.Round(Conversions.ToDouble(array[1]) + 1.0));
				BD.ConsultaModificar("Mesas", "Activo=" + VariableGeneral.armarBolean(1), "Nombre like '" + text + "-" + Conversions.ToString(num) + "'");
			}
		}
	}

	public bool loadMesaPorID()
	{
		DataTable dataTable = clsMes.ToreturnMesaPorID();
		if (dataTable.Rows.Count == 0)
		{
			return false;
		}
		clsMes._ID = Conversions.ToInteger(dataTable.Rows[0]["ID"]);
		clsMes._Codigo = Conversions.ToString(dataTable.Rows[0]["Codigo"]);
		clsMes._Nombre = Conversions.ToString(dataTable.Rows[0]["Nombre"]);
		clsMes._Descripcion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Descripcion"]), ""));
		clsMes._responsableID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Responsable"]), 0));
		clsMes._responsableNombre = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["NombreMesero"]), ""));
		return true;
	}

	public DataTable ToReturnMesasOcupada()
	{
		return clsMes.ToReturnMesasOcupada();
	}

	public DataTable ToReturnMesasLibres()
	{
		return clsMes.ToReturnMesasLibres();
	}

	public DataTable ToReturnTodasMesas()
	{
		return clsMes.ToReturnTodasMesas();
	}

	public DataTable ToReturnSoloMesasParaCambiar()
	{
		return clsMes.ToReturnSoloMesasParaCambiar();
	}

	public DataTable ToReturnSoloMesasParaCambiar(int meseroID)
	{
		return clsMes.ToReturnSoloMesasParaCambiar(meseroID);
	}

	public DataTable ToReturnMesasYvisitasActivas(bool soloMesas, bool pedidoEnEspera, int salonId)
	{
		return clsMes.ToReturnMesasYvisitasActivas(soloMesas, pedidoEnEspera, salonId);
	}

	public DataTable ToReturnMesasYvisitasActivasSinBarra(bool soloMesas, bool pedidoEnEspera, int meseroID, int salonId)
	{
		return clsMes.ToReturnMesasYvisitasActivasSinBarra(soloMesas, pedidoEnEspera, meseroID, salonId);
	}

	public void ToReturnSoloMesas(ref dtsMesas.Mesa1DataTable dts)
	{
		DataTable dataTable = new DataTable();
		dataTable = clsMes.ToReturnSoloMesas1();
		dts.Clear();
		int i = default(int);
		for (; i < dataTable.Rows.Count; i = checked(i + 1))
		{
			dtsMesas.Mesa1Row mesa1Row = dts.NewMesa1Row();
			mesa1Row.ID = Conversions.ToInteger(dataTable.Rows[i][0]);
			mesa1Row.Codigo = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][1]), ""));
			mesa1Row.Nombre = Conversions.ToString(dataTable.Rows[i][2]);
			mesa1Row.Descripcion = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][3]), ""));
			mesa1Row.Responsable = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][4]), ""));
			mesa1Row.SalonID = Conversions.ToString(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][5]), ""));
			mesa1Row.Activo = Conversions.ToBoolean(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[i][6]), 0));
			dts.AddMesa1Row(mesa1Row);
			mesa1Row = null;
		}
	}

	public void Save(string Codigo, string Nombre, string Descripcion, int respon, int salonID, int SimboloID, bool activo)
	{
		clsMes._Codigo = Codigo;
		clsMes._Nombre = Nombre;
		clsMes._Descripcion = Descripcion;
		clsMes._SalonID = salonID;
		clsMes._Activo = activo;
		clsMes._responsableID = respon;
		if (clsMes._ID == 0)
		{
			clsMes.Insert();
		}
		else
		{
			clsMes.Modify();
		}
		BD.ConsultaModificar("Simbolo", "salonID =" + Conversions.ToString(salonID), "SimboloID=" + Conversions.ToString(SimboloID));
	}

	public void cambiarMesero(int respon)
	{
		clsMes._responsableID = respon;
		clsMes.cambiarMesero();
	}

	public void Delete()
	{
		clsMes.Delete();
	}

	public void DevolverCodigoMesa(int id, ref double cod)
	{
		clsMes._ID = id;
		clsMes.DevolverCodigoMesa(ref cod);
	}

	public string proximoCodigo()
	{
		return clsMes.proximoCodigo();
	}

	public bool MesaOcupada()
	{
		return clsMes.MesaOcupada();
	}
}
