using System.Data;

namespace ControlConsumoLib;

public class clsAlumnos
{
	private int _ID;

	private string _nombre;

	private string _apellidos;

	private string _curso;

	private double _monto;

	private double _deuda;

	private bool _activo;

	public int ID
	{
		get
		{
			return _ID;
		}
		set
		{
			_ID = value;
		}
	}

	public string Nombre
	{
		get
		{
			return _nombre;
		}
		set
		{
			_nombre = value;
		}
	}

	public string Apellidos
	{
		get
		{
			return _apellidos;
		}
		set
		{
			_apellidos = value;
		}
	}

	public string Curso
	{
		get
		{
			return _curso;
		}
		set
		{
			_curso = value;
		}
	}

	public double Monto
	{
		get
		{
			return _monto;
		}
		set
		{
			_monto = value;
		}
	}

	public double Deuda
	{
		get
		{
			return _deuda;
		}
		set
		{
			_deuda = value;
		}
	}

	public bool Activo
	{
		get
		{
			return _activo;
		}
		set
		{
			_activo = value;
		}
	}

	public DataTable DevolverProductos()
	{
		return BD.ConsultaVer("ID,Nombre", "Productos", "Habilitado<>0");
	}
}
