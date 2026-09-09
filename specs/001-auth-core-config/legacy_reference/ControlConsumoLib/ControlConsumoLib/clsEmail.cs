using System;
using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsEmail
{
	private int EmailID;

	private string Username;

	private string Passw;

	private string Port;

	private string Frm;

	private string Host;

	private bool SSL;

	public int _EmailID
	{
		get
		{
			return EmailID;
		}
		set
		{
			EmailID = value;
		}
	}

	public string _Username
	{
		get
		{
			return Username;
		}
		set
		{
			Username = value;
		}
	}

	public string _Passw
	{
		get
		{
			return Passw;
		}
		set
		{
			Passw = value;
		}
	}

	public string _Port
	{
		get
		{
			return Port;
		}
		set
		{
			Port = value;
		}
	}

	public string _Frm
	{
		get
		{
			return Frm;
		}
		set
		{
			Frm = value;
		}
	}

	public string _Host
	{
		get
		{
			return Host;
		}
		set
		{
			Host = value;
		}
	}

	public bool _SSL
	{
		get
		{
			return SSL;
		}
		set
		{
			SSL = value;
		}
	}

	public clsEmail()
	{
		Username = "";
		Passw = "";
		Port = "";
		Frm = "";
		Host = "";
		EmailID = 0;
		SSL = false;
	}

	public void Devolver()
	{
		DataTable dataTable = BD.ConsultaVer("Email.EmailID,Email.Username,Email.Passw,Email.Port,Email.Frm,Email.Host, Email.SSL", "Email");
		if (dataTable.Rows.Count > 0)
		{
			EmailID = Conversions.ToInteger(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["EmailID"])) ? ((object)0) : dataTable.Rows[0]["EmailID"]);
			Username = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Username"])) ? "" : dataTable.Rows[0]["Username"]);
			Passw = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Passw"])) ? "" : dataTable.Rows[0]["Passw"]);
			Port = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Port"])) ? "" : dataTable.Rows[0]["Port"]);
			Frm = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Frm"])) ? "" : dataTable.Rows[0]["Frm"]);
			Host = Conversions.ToString(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["Host"])) ? "" : dataTable.Rows[0]["Host"]);
			SSL = Conversions.ToBoolean(Information.IsDBNull(RuntimeHelpers.GetObjectValue(dataTable.Rows[0]["SSL"])) ? ((object)false) : dataTable.Rows[0]["SSL"]);
		}
		else
		{
			Username = "";
			Passw = "";
			Port = "";
			Frm = "";
			Host = "";
			EmailID = 0;
			SSL = false;
		}
	}

	public int Modificar()
	{
		int result;
		try
		{
			BD.ConsultaModificar("Email", "Username='" + Username + "',Passw='" + Passw + "',Port='" + Port + "',Frm='" + Frm + "',SSL=" + VariableGeneral.armarBolean(SSL) + ",Host='" + Host + "'", "EmailID=" + EmailID);
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
}
