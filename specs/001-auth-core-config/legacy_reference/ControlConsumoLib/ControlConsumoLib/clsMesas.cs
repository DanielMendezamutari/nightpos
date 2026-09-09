using System;
using System.Data;
using System.Runtime.CompilerServices;
using ConfigToptech;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsMesas
{
	private int ID;

	private string Codigo;

	private string Nombre;

	private string Descripcion;

	private string responsableID;

	private string responsableNombre;

	private int SalonID;

	private bool Activo;

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

	public int _SalonID
	{
		get
		{
			return SalonID;
		}
		set
		{
			SalonID = value;
		}
	}

	public string _responsableNombre
	{
		get
		{
			return responsableNombre;
		}
		set
		{
			responsableNombre = value;
		}
	}

	public string _Codigo
	{
		get
		{
			return Codigo;
		}
		set
		{
			Codigo = value;
		}
	}

	public string _Nombre
	{
		get
		{
			return Nombre;
		}
		set
		{
			Nombre = value;
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

	public int _responsableID
	{
		get
		{
			if (Operators.CompareString(responsableID, "null", TextCompare: false) == 0)
			{
				return 0;
			}
			return Conversions.ToInteger(responsableID);
		}
		set
		{
			if (value == 0)
			{
				responsableID = "null";
			}
			else
			{
				responsableID = Conversions.ToString(value);
			}
		}
	}

	public bool _Activo
	{
		get
		{
			return Activo;
		}
		set
		{
			Activo = value;
		}
	}

	public clsMesas()
	{
		Nombre = "";
		Codigo = "";
		Descripcion = "";
		Activo = true;
		responsableID = Conversions.ToString(0);
		Activo = false;
		SalonID = 0;
	}

	public int getSimboloID()
	{
		DataTable dataTable = BD.ConsultaVer("Mesas.SimboloID", "Mesas", ("ID = " + Conversions.ToString(ID)) ?? "");
		if (dataTable.Rows.Count == 0)
		{
			return 0;
		}
		return Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0]), 0));
	}

	public DataTable ToreturnMesasPorCodigo1()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			if (configuration.gCodigosMesasNumericos)
			{
				if (!Versioned.IsNumeric(Codigo))
				{
					return new DataTable();
				}
				return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,ResponsableID as Responsable,Meseros.Nombre as NombreMesero, iif( tab1.EnMesa is null, " + VariableGeneral.armarBolean(0) + ", tab1.EnMesa) as Ocupada,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ")  group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID ) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", ("CDbl(Mesas.Codigo) = " + Conversion.Str(Codigo)) ?? "");
			}
			return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,ResponsableID as Responsable,Meseros.Nombre as NombreMesero, iif( tab1.EnMesa is null, " + VariableGeneral.armarBolean(0) + ", tab1.EnMesa) as Ocupada,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ")  group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID ) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", "Mesas.Codigo ='" + Codigo + "'");
		}
		if (configuration.gCodigosMesasNumericos)
		{
			if (!Versioned.IsNumeric(Codigo))
			{
				return new DataTable();
			}
			return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,ResponsableID as Responsable,Meseros.Nombre as NombreMesero, CASE WHEN  tab1.EnMesa  is null  THEN  " + VariableGeneral.armarBolean(0) + " ELSE  tab1.EnMesa  END as Ocupada,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ")  group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID ) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", ("cast(Mesas.Codigo as float) = " + Conversion.Str(Codigo)) ?? "");
		}
		return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,ResponsableID as Responsable,Meseros.Nombre as NombreMesero, CASE WHEN  tab1.EnMesa  is null  THEN  " + VariableGeneral.armarBolean(0) + " ELSE  tab1.EnMesa  END as Ocupada,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ")  group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID ) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", "Mesas.Codigo ='" + Codigo + "'");
	}

	public DataTable ToreturnMesaPorID()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,ResponsableID as Responsable, iif( tab1.EnMesa is null, " + VariableGeneral.armarBolean(0) + ", tab1.EnMesa)  as Ocupada,tab1.Cliente, tab1.visitaID,Meseros.Nombre as NombreMesero", "(Mesas left join  ( select Visitas.ID as visitaID,Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ")  group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID ) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", ("Mesas.ID =" + Conversions.ToString(ID)) ?? "");
		}
		return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,ResponsableID as Responsable, CASE WHEN  tab1.EnMesa  is null  THEN  " + VariableGeneral.armarBolean(0) + " ELSE  tab1.EnMesa  END  as Ocupada,tab1.Cliente, tab1.visitaID,Meseros.Nombre as NombreMesero", "(Mesas left join  ( select Visitas.ID as visitaID,Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ")  group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID ) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", ("Mesas.ID =" + Conversions.ToString(ID)) ?? "");
	}

	public DataTable ToReturnSoloMesasParaCambiar()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable,iif( tab1.EnMesa is null, " + VariableGeneral.armarBolean(0) + ", tab1.EnMesa)  as Ocupada ,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", "Mesas.Activo=" + VariableGeneral.armarBolean(1) + " and Mesas.ID<>" + Conversions.ToString(1) + " and Mesas.ID<>" + ID, "Mesas.Nombre");
		}
		return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable,CASE WHEN  tab1.EnMesa  is null  THEN  " + VariableGeneral.armarBolean(0) + " ELSE  tab1.EnMesa  END as Ocupada ,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", "Mesas.Activo=" + VariableGeneral.armarBolean(1) + " and Mesas.ID<>" + Conversions.ToString(1) + " and Mesas.ID<>" + ID, "Mesas.Nombre");
	}

	public DataTable ToReturnSoloMesasParaCambiar(int meseroID)
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable,iif( tab1.EnMesa is null, " + VariableGeneral.armarBolean(0) + ", tab1.EnMesa)  as Ocupada ,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", "Mesas.Activo=" + VariableGeneral.armarBolean(1) + " and Mesas.ResponsableId=" + Conversions.ToString(meseroID) + " and Mesas.ID<>" + Conversions.ToString(1) + " and Mesas.ID<>" + ID, "Mesas.Nombre");
		}
		return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable,CASE WHEN  tab1.EnMesa  is null  THEN  " + VariableGeneral.armarBolean(0) + " ELSE  tab1.EnMesa  END as Ocupada ,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", "Mesas.Activo=" + VariableGeneral.armarBolean(1) + " and Mesas.ResponsableId=" + Conversions.ToString(meseroID) + " and Mesas.ID<>" + Conversions.ToString(1) + " and Mesas.ID<>" + ID, "Mesas.Nombre");
	}

	public DataTable ToReturnTodasMesas()
	{
		return BD.ConsultaVer("Mesas.ID,Mesas.Nombre", "Mesas", "", "Nombre");
	}

	public DataTable ToReturnMesasLibres()
	{
		if (configuration.gMODO_ACCESS == 1)
		{
			return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable,iif( tab1.EnMesa is null, " + VariableGeneral.armarBolean(0) + ", tab1.EnMesa)  as Ocupada ,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", "tab1.EnMesa is null and Mesas.ID<>" + Conversions.ToString(1));
		}
		return BD.ConsultaVer("Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable, CASE WHEN  tab1.EnMesa  is null  THEN  " + VariableGeneral.armarBolean(0) + " ELSE  tab1.EnMesa  END as Ocupada ,tab1.Cliente", "(Mesas left join  ( select Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID ", "tab1.EnMesa is null and Mesas.ID<>" + Conversions.ToString(1));
	}

	public DataTable ToReturnMesasOcupada()
	{
		return BD.ConsultaVer("Mesas.Codigo, Mesas.Nombre as Mesa ", "Mesas", " Mesas.ID  in (select MEsaID from Visitas  where Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + " and Mesaid is not null) ");
	}

	public DataTable ToReturnMesasYvisitasActivasSinBarra(bool soloMesas, bool pedidoEnEspera, int meseroID1, int salonId)
	{
		string right = "Mesas.Nombre";
		string text = "Mesas.Codigo";
		if (configuration.gCodigosMesasNumericos)
		{
			right = "Codigo";
			if (configuration.gMODO_ACCESS == 1)
			{
				text = " Cdbl(Mesas.codigo)";
			}
			else if (configuration.gMODO_ACCESS == 0)
			{
				text = " cast(Mesas.codigo as float)";
			}
		}
		string text2 = "";
		if (salonId > 0)
		{
			text2 = " and Mesas.SalonId=" + Conversions.ToString(salonId);
		}
		string text3 = "";
		text3 = ((configuration.gMesasVisibilidad == configuration.MesasVisibilidad.SoloVeoMisMesas) ? (" (Mesas.responsableID=" + Conversions.ToString(meseroID1) + ") and ") : ((configuration.gMesasVisibilidad != configuration.MesasVisibilidad.VeoMesasLibresMasMisMesas) ? "" : (" (Mesas.responsableID=" + Conversions.ToString(meseroID1) + " or Mesas.responsableID is null ) and ")));
		if (configuration.gMODO_ACCESS == 1)
		{
			if (soloMesas)
			{
				return BD.ConsultaVer(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("select Mesas.ID," + text + " as Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable, iif( tab1.EnMesa is null, " + VariableGeneral.armarBolean(0) + ", tab1.EnMesa)  as Ocupada ,tab1.Cliente, tab1.visitaID,ImprimioCuenta from (Mesas left join  ( select Visitas.ID as visitaID,Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa,ImprimioCuenta from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID where " + text3 + " Mesas.ID<>" + Conversions.ToString(1) + " and Mesas.Activo=" + VariableGeneral.armarBolean(1) + text2 + " UNION  select 0,NULL,'Sin Mesa', PersonasSinMesa.NombreFamilia + ' / ' + ", Interaction.IIf(configuration.gMODO_ACCESS == 1, " CantidadPersonas ", "cast( CantidadPersonas  as varchar)")), "  ,'',"), VariableGeneral.armarBolean(1)), ",iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Clientes,Visitas.ID,ImprimioCuenta   "), " from (visitas inner join PersonasSinMesa on PersonasSinMesa.PersonaSinMesaID=Visitas.PersonaSinMesaID) left join Clientes on Visitas.ClienteID=Clientes.ID  "), " where MesaID is null and EnMesa="), VariableGeneral.armarBolean(1)), " and visitas.ParaLlevarID  is null and visitas.MesaAdicionalID   is null "), " UNION "), " select 0,NULL,'Mesas Adicionales', MesasAdicionales.Nombre ,'',"), VariableGeneral.armarBolean(1)), ", iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Clientes ,Visitas.ID,ImprimioCuenta   "), " from (visitas inner join MesasAdicionales  on MesasAdicionales.MesaAdicionalID=Visitas.MesaAdicionalID) left join Clientes on Visitas.ClienteID=Clientes.ID"), " where MesaID is null and EnMesa="), VariableGeneral.armarBolean(1)), " and visitas.PersonaSinMesaID   is null and visitas.ParaLlevarID  is null order by Codigo")));
			}
			if (pedidoEnEspera)
			{
				return BD.ConsultaVer("select 0 as ID,NULL as Codigo,'Pedido' as Nombre, ParaLLevar.Nombre as Descripcion,'' as Responsable," + VariableGeneral.armarBolean(1) + " as Ocupada, iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Cliente,Visitas.ID  as VisitaID,ImprimioCuenta  from (visitas inner join ParaLLevar  on ParaLLevar.ParaLLevarID=Visitas.ParaLLevarID) left join Clientes on Visitas.ClienteID=Clientes.ID  where MesaID is null and EnMesa=" + VariableGeneral.armarBolean(1) + " and visitas.PersonaSinMesaID   is null and visitas.MesaAdicionalID   is null  union select 0,NULL,'En Espera', MesasAdicionales.Nombre ,''," + VariableGeneral.armarBolean(1) + ", iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Clientes ,Visitas.ID,ImprimioCuenta    from (visitas inner join MesasAdicionales  on MesasAdicionales.MesaAdicionalID=Visitas.MesaAdicionalID) left join Clientes on Visitas.ClienteID=Clientes.ID  where MesaID is null and EnMesa=" + VariableGeneral.armarBolean(1) + " and visitas.PersonaSinMesaID  is null and visitas.ParaLlevarID  is null order by Codigo,VisitaID ");
			}
			return BD.ConsultaVer("select 0 as ID,NULL as Codigo,'Pedido' as Nombre, ParaLLevar.Nombre as Descripcion,'' as Responsable," + VariableGeneral.armarBolean(1) + " as Ocupada, iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Cliente,Visitas.ID  as VisitaID,ImprimioCuenta  from (visitas inner join ParaLLevar  on ParaLLevar.ParaLLevarID=Visitas.ParaLLevarID) left join Clientes on Visitas.ClienteID=Clientes.ID  where MesaID is null and EnMesa=" + VariableGeneral.armarBolean(1) + " and visitas.PersonaSinMesaID   is null and visitas.MesaAdicionalID   is null order by ParaLLevar.Nombre");
		}
		if (soloMesas)
		{
			return BD.ConsultaVer(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("select Mesas.ID," + text + " as Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable, CASE WHEN  tab1.EnMesa  is null  THEN  " + VariableGeneral.armarBolean(0) + " ELSE  tab1.EnMesa  END  as Ocupada ,tab1.Cliente, tab1.visitaID,ImprimioCuenta from (Mesas left join  ( select Visitas.ID as visitaID,Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa,ImprimioCuenta from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID where  " + text3 + " Mesas.ID<>" + Conversions.ToString(1) + " and Mesas.Activo=" + VariableGeneral.armarBolean(1) + text2 + " UNION  select 0,NULL,'Sin Mesa', PersonasSinMesa.NombreFamilia + ' / ' + ", Interaction.IIf(configuration.gMODO_ACCESS == 1, " CantidadPersonas ", "cast( CantidadPersonas  as varchar)")), "  ,'',"), VariableGeneral.armarBolean(1)), ",CASE WHEN  Visitas.ClienteID  is null  THEN '' ELSE Clientes.Nombre + ' ' + Clientes.Apellidos  END as Clientes,Visitas.ID,ImprimioCuenta   "), " from (visitas inner join PersonasSinMesa on PersonasSinMesa.PersonaSinMesaID=Visitas.PersonaSinMesaID) left join Clientes on Visitas.ClienteID=Clientes.ID  "), " where MesaID is null and EnMesa="), VariableGeneral.armarBolean(1)), " and visitas.ParaLlevarID  is null and visitas.MesaAdicionalID   is null "), " UNION "), " select 0,NULL,'Mesas Adicionales', MesasAdicionales.Nombre ,'',"), VariableGeneral.armarBolean(1)), ",CASE WHEN  Visitas.ClienteID  is null THEN '' ELSE Clientes.Nombre + ' ' + Clientes.Apellidos  END as Clientes ,Visitas.ID,ImprimioCuenta   "), " from (visitas inner join MesasAdicionales  on MesasAdicionales.MesaAdicionalID=Visitas.MesaAdicionalID) left join Clientes on Visitas.ClienteID=Clientes.ID"), " where MesaID is null and EnMesa="), VariableGeneral.armarBolean(1)), " and visitas.PersonaSinMesaID   is null and visitas.ParaLlevarID  is null order by "), right)));
		}
		return BD.ConsultaVer("select 0 as ID,NULL as Codigo,'Pedido' as Nombre, ParaLLevar.Nombre as Descripcion,'' as Responsable," + VariableGeneral.armarBolean(1) + " as Ocupada, CASE WHEN  Visitas.ClienteID  is null  THEN '' ELSE Clientes.Nombre + ' ' + Clientes.Apellidos  END as Cliente,Visitas.ID  as VisitaID,ImprimioCuenta  from (visitas inner join ParaLLevar  on ParaLLevar.ParaLLevarID=Visitas.ParaLLevarID) left join Clientes on Visitas.ClienteID=Clientes.ID  where MesaID is null and EnMesa=" + VariableGeneral.armarBolean(1) + " and visitas.PersonaSinMesaID   is null and visitas.MesaAdicionalID   is null order by Descripcion");
	}

	public DataTable ToReturnMesasYvisitasActivas(bool soloMesas, bool pedidoEnEspera, int salonId)
	{
		string text = "Mesas.Codigo";
		string right = ((!soloMesas) ? "Mesas.Codigo" : "Mesas.Nombre");
		if (configuration.gCodigosMesasNumericos)
		{
			right = "Codigo";
			if (configuration.gMODO_ACCESS == 1)
			{
				text = " Cdbl(Mesas.codigo)";
			}
			else if (configuration.gMODO_ACCESS == 0)
			{
				text = " cast(Mesas.codigo as float)";
			}
		}
		string text2 = "";
		text2 = ((salonId == -1) ? ("tab1.EnMesa= " + VariableGeneral.armarBolean(1)) : ((salonId <= 0) ? ("Mesas.Activo=" + VariableGeneral.armarBolean(1)) : ("Mesas.Activo=" + VariableGeneral.armarBolean(1) + "  and (Mesas.SalonId=" + Conversions.ToString(salonId) + " or Mesas.Id=" + Conversions.ToString(1) + " )")));
		if (configuration.gMODO_ACCESS == 1)
		{
			if (soloMesas)
			{
				return BD.ConsultaVer(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("select Mesas.ID," + text + " as Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable, iif( tab1.EnMesa is null, " + VariableGeneral.armarBolean(0) + ", tab1.EnMesa)  as Ocupada ,tab1.Cliente, tab1.visitaID,ImprimioCuenta from (Mesas left join  ( select Visitas.ID as visitaID,Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa,ImprimioCuenta from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID where " + text2 + " UNION  select 0,NULL,'Sin Mesa', PersonasSinMesa.NombreFamilia + ' / ' + ", Interaction.IIf(configuration.gMODO_ACCESS == 1, " CStr(CantidadPersonas) ", "cast( CantidadPersonas  as varchar)")), "  ,'',"), VariableGeneral.armarBolean(1)), ",iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Clientes,Visitas.ID,ImprimioCuenta   "), " from (visitas inner join PersonasSinMesa on PersonasSinMesa.PersonaSinMesaID=Visitas.PersonaSinMesaID)  left join Clientes on Visitas.ClienteID=Clientes.ID  "), " where MesaID is null and EnMesa="), VariableGeneral.armarBolean(1)), " and visitas.ParaLlevarID  is null and visitas.MesaAdicionalID   is null "), " union select 0,NULL,'Mesas Adicionales', MesasAdicionales.Nombre ,'',"), VariableGeneral.armarBolean(1)), ", iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Clientes ,Visitas.ID,ImprimioCuenta   "), " from (visitas inner join MesasAdicionales  on MesasAdicionales.MesaAdicionalID=Visitas.MesaAdicionalID) left join Clientes on Visitas.ClienteID=Clientes.ID "), " where MesaID is null and EnMesa="), VariableGeneral.armarBolean(1)), " and visitas.PersonaSinMesaID   is null and visitas.ParaLlevarID  is null order by Codigo")));
			}
			if (pedidoEnEspera)
			{
				return BD.ConsultaVer("select 0 as ID,ParaLLevar.HoraRecoger  as Codigo,'Pedido' as Nombre, ParaLLevar.Nombre as Descripcion, '' as Responsable," + VariableGeneral.armarBolean(1) + " as Ocupada, iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Cliente,Visitas.ID  as VisitaID,ImprimioCuenta  from (visitas inner join ParaLLevar  on ParaLLevar.ParaLLevarID=Visitas.ParaLLevarID) left join Clientes on Visitas.ClienteID=Clientes.ID  where MesaID is null and EnMesa=" + VariableGeneral.armarBolean(1) + " and visitas.PersonaSinMesaID   is null and visitas.MesaAdicionalID   is null  union select 0,NULL,'En Espera', MesasAdicionales.Nombre ,''," + VariableGeneral.armarBolean(1) + ", iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Clientes ,Visitas.ID ,ImprimioCuenta   from (visitas inner join MesasAdicionales  on MesasAdicionales.MesaAdicionalID=Visitas.MesaAdicionalID) left join Clientes on Visitas.ClienteID=Clientes.ID  where MesaID is null and EnMesa=" + VariableGeneral.armarBolean(1) + " and visitas.PersonaSinMesaID  is null and visitas.ParaLlevarID  is null order by Codigo,VisitaID ");
			}
			return BD.ConsultaVer("select 0 as ID,ParaLLevar.HoraRecoger as Codigo,TipoEnvios.Nombre as Nombre, ParaLLevar.Nombre as Descripcion,'' as Responsable," + VariableGeneral.armarBolean(1) + " as Ocupada, iif(Visitas.ClienteID is not null,Clientes.Nombre + ' ' + Clientes.Apellidos,''  ) as Cliente,Visitas.ID  as VisitaID ,ImprimioCuenta from ((visitas inner join ParaLLevar  on ParaLLevar.ParaLLevarID=Visitas.ParaLLevarID) left join Clientes on Visitas.ClienteID=Clientes.ID ) left join TipoEnvios on TipoEnvios.TipoEnvioID =Visitas.TipoEnvioID  where MesaID is null and EnMesa=" + VariableGeneral.armarBolean(1) + " and visitas.PersonaSinMesaID   is null and visitas.MesaAdicionalID   is null order by ParaLLevar.HoraRecoger");
		}
		if (soloMesas)
		{
			return BD.ConsultaVer(Conversions.ToString(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject(Operators.ConcatenateObject("select Mesas.ID," + text + " as Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable, CASE WHEN  tab1.EnMesa  is null  THEN  " + VariableGeneral.armarBolean(0) + " ELSE  tab1.EnMesa  END  as Ocupada ,tab1.Cliente, tab1.visitaID ,ImprimioCuenta from (Mesas left join  ( select Visitas.ID as visitaID,Visitas.MesaID, Clientes.Nombre + ' ' + Clientes.Apellidos as Cliente, EnMesa,ImprimioCuenta from ((select Visitas.MesaID, max(Visitas.Fecha) as Fecha1 from Visitas  inner join Mesas as mes1 on (mes1.ID=Visitas.MesaID and  Visitas.EnMesa=" + VariableGeneral.armarBolean(1) + ") group by Visitas.MesaID) as tab2 inner join Visitas on tab2.MesaID= Visitas.MesaID and tab2.Fecha1=Visitas.Fecha) left join Clientes on Clientes.ID=Visitas.ClienteId )  as tab1 on Mesas.Id=tab1.MesaID) left join Meseros on Meseros.MeseroID=Mesas.responsableID  where " + text2 + " UNION  select 0,NULL,'Sin Mesa', PersonasSinMesa.NombreFamilia + ' / ' + ", Interaction.IIf(configuration.gMODO_ACCESS == 1, "  CStr(CantidadPersonas) ", "cast( CantidadPersonas  as varchar)")), "  ,'',"), VariableGeneral.armarBolean(1)), ",CASE  WHEN Visitas.ClienteID is null  THEN '' ELSE Clientes.Nombre + ' ' + Clientes.Apellidos  END as Clientes,Visitas.ID,ImprimioCuenta   "), " from (visitas inner join PersonasSinMesa on PersonasSinMesa.PersonaSinMesaID=Visitas.PersonaSinMesaID)  left join Clientes on Visitas.ClienteID=Clientes.ID  "), " where MesaID is null and EnMesa="), VariableGeneral.armarBolean(1)), " and visitas.ParaLlevarID  is null and visitas.MesaAdicionalID   is null "), " union select 0,NULL,'Mesas Adicionales', MesasAdicionales.Nombre ,'',"), VariableGeneral.armarBolean(1)), ", CASE  WHEN Visitas.ClienteID is null THEN '' ELSE Clientes.Nombre + ' ' + Clientes.Apellidos  END as Clientes ,Visitas.ID,ImprimioCuenta   "), " from (visitas inner join MesasAdicionales  on MesasAdicionales.MesaAdicionalID=Visitas.MesaAdicionalID) left join Clientes on Visitas.ClienteID=Clientes.ID "), " where MesaID is null and EnMesa="), VariableGeneral.armarBolean(1)), " and visitas.PersonaSinMesaID   is null and visitas.ParaLlevarID  is null order by "), right)));
		}
		if (pedidoEnEspera)
		{
			Interaction.MsgBox("trabajar pedido en espera");
			return new DataTable();
		}
		return BD.ConsultaVer("select 0 as ID,ParaLLevar.HoraRecoger  as Codigo,TipoEnvios.Nombre as Nombre, ParaLLevar.Nombre as Descripcion,Meseros.Nombre as Responsable," + VariableGeneral.armarBolean(1) + " as Ocupada, CASE  WHEN Visitas.ClienteID is null THEN '' ELSE Clientes.Nombre + ' ' + Clientes.Apellidos  END as Cliente,Visitas.ID  as VisitaID,ImprimioCuenta  from (((visitas inner join ParaLLevar  on ParaLLevar.ParaLLevarID=Visitas.ParaLLevarID) left join Clientes on Visitas.ClienteID=Clientes.ID) left join Meseros on Meseros.MeseroID=ParaLLevar.Motociclista)  left join TipoEnvios on TipoEnvios.TipoEnvioID =Visitas.TipoEnvioID  where MesaID is null and EnMesa=" + VariableGeneral.armarBolean(1) + " and visitas.PersonaSinMesaID   is null and visitas.MesaAdicionalID   is null order by cast(ParaLLevar.HoraRecoger as date) ,ParaLLevar.Nombre ");
	}

	public DataTable ToReturnSoloMesas1()
	{
		return BD.ConsultaVer("select Mesas.ID,Mesas.Codigo,Mesas.Nombre,Mesas.Descripcion,Meseros.Nombre as Responsable,Salones.Nombre as SalonID, Mesas.Activo from (Mesas left join Meseros on Meseros.MeseroID=Mesas.responsableID) left join Salones on Mesas.SalonId=Salones.SalonID order by Mesas.Nombre");
	}

	public int cambiarMesero()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Mesas", "ResponsableID=" + responsableID, "ID=" + ID);
			result = 1;
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

	public int setDescripcion()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Mesas", "Descripcion='" + Descripcion.Replace("'", "`") + "'", "ID=" + ID);
			result = 1;
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

	public int setActivo(bool activo)
	{
		int result;
		try
		{
			BD.ConsultaModificar("Mesas", "Activo=" + VariableGeneral.armarBolean(activo) + ", Descripcion='" + Descripcion.Replace("'", "`") + "'", "ID=" + ID);
			result = 1;
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

	public int Modify()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Mesas", "Codigo='" + Codigo + "',Nombre='" + Nombre + "',Descripcion='" + Descripcion + "',ResponsableID=" + responsableID + ",SalonID=" + Conversions.ToString(SalonID) + ",Activo= " + VariableGeneral.armarBolean(Activo), "ID=" + ID);
			result = 1;
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

	public int Insert()
	{
		checked
		{
			int result;
			try
			{
				if (configuration.gStyleBoliches1 <= configuration.styleBolichesId.Bless)
				{
					ID = Conversions.ToInteger(VariableGeneral.NZ(RuntimeHelpers.GetObjectValue(BD.ConsultaVer("max(ID)", "Mesas").Rows[0][0]), 0));
					ID++;
					BD.ConsultaInsertar(Conversions.ToString(ID) + ",'" + Codigo + "','" + Nombre + "','" + Descripcion + "'," + responsableID + "," + Conversions.ToString(SalonID) + "," + VariableGeneral.armarBolean(Activo), "Mesas(ID, Codigo, Nombre, Descripcion, ResponsableID,SalonID,Activo)");
					result = ID;
				}
				else
				{
					BD.ConsultaInsertar3("'" + Codigo + "','" + Nombre + "','" + Descripcion + "'," + responsableID + "," + Conversions.ToString(SalonID) + "," + VariableGeneral.armarBolean(Activo), "Mesas(Codigo, Nombre, Descripcion, ResponsableID,SalonID,Activo)", ref ID);
					result = ID;
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

	public int Delete()
	{
		int result;
		try
		{
			if (BD.ConsultaEliminar("Mesas", "ID = " + ID) == 0)
			{
				Interaction.MsgBox("Can't delete , is in use");
			}
			result = 1;
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

	public void DevolverCodigoMesa(ref double cod)
	{
		DataTable dataTable = BD.ConsultaVer("Mesas.Codigo", "Mesas", ("ID = " + Conversions.ToString(ID)) ?? "");
		if (dataTable.Rows.Count > 0)
		{
			cod = Conversions.ToDouble(dataTable.Rows[0][0]);
		}
	}

	public string proximoCodigo()
	{
		DataTable dataTable = new DataTable();
		if (configuration.gMODO_ACCESS == 2)
		{
			dataTable = BD.ConsultaVer("max(codigo)", "Mesas", "codigo REGEXP '^[0-9]+$'");
		}
		else if (configuration.gMODO_ACCESS == 1)
		{
			dataTable = BD.ConsultaVer("max(col)", "(select cdbl(Codigo) as col from Mesas where ISNUMERIC(Codigo)) as tab1");
		}
		else if (configuration.gMODO_ACCESS == 0)
		{
			dataTable = BD.ConsultaVer("max(col)", "(select convert(float,Codigo) as col from Mesas where ISNUMERIC(Codigo)=1) as tab1");
		}
		if (dataTable.Rows.Count > 0)
		{
			return Conversions.ToLong(Operators.AddObject(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0][0])) ? ((object)0) : dataTable.Rows[0][0], 1)).ToString();
		}
		return Conversions.ToString(1);
	}

	public bool MesaOcupada()
	{
		bool result;
		try
		{
			result = BD.ConsultaVer("*", "Visitas", "MesaID = " + ID + " and EnMesa = 1").Rows.Count > 0;
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
