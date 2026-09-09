using System.Data;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlImpresoras
{
	private readonly clsImpresoras clsImp;

	public ctlImpresoras()
	{
		clsImp = new clsImpresoras();
	}

	public void SetImpresora(int ID)
	{
		clsImp._ImpresoraID = ID;
	}

	public int getImpresoraID()
	{
		return clsImp._ImpresoraID;
	}

	public clsImpresoras LlenarClase1()
	{
		clsImp.llenarclase1();
		return clsImp;
	}

	public DataTable devolverImpresoras(string search, string field)
	{
		if (search.Length == 0)
		{
			return clsImp.Devolver();
		}
		return clsImp.Devolver(search, field);
	}

	public string devolverImpresoraCuentaFisico()
	{
		clsImp.DevolverImprimirCuenta();
		return clsImp._NombreFisico;
	}

	public string devolverNombreFisicoPorID()
	{
		clsImp.devolverNombreFisicoPorID();
		return clsImp._NombreFisico;
	}

	public string devolverNombreFisicoPorNombre(string nombre)
	{
		clsImp._Nombre = nombre;
		clsImp.devolverNombreFisicoPorNombre();
		return clsImp._NombreFisico;
	}

	public string DevolverAbrirCaja()
	{
		clsImp.DevolverAbrirCaja();
		return clsImp._NombreFisico;
	}

	public string DevolverImprimirFacturaFisico()
	{
		clsImp.DevolverImprimirFacturaFisico();
		return clsImp._NombreFisico;
	}

	public string DevolverImprimirFacturas()
	{
		clsImp.DevolverImprimirFacturas();
		return clsImp._Nombre;
	}

	public bool SoyKDS()
	{
		return clsImp.soyKDS();
	}

	public DataTable devolverImpresoras()
	{
		return clsImp.Devolver();
	}

	public DataTable devolverImpresorasPorDescripcion()
	{
		return clsImp.devolverImpresorasPorDescripcion();
	}

	public DataTable devolverImpresoraskitchenPorDescripcion()
	{
		return clsImp.devolverImpresoraskitchenPorDescripcion();
	}

	public DataTable devolverImpresorasUsadasEnVisitaSinDuplicados(int visitaId)
	{
		DataTable dataTable = clsImp.devolverImpresorasUsadasEnVisita(visitaId);
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				if (dataTable.Rows[i]["NombreFisico"].ToString().Contains(";"))
				{
					string[] array = (string[])NewLateBinding.LateGet(dataTable.Rows[i]["NombreFisico"], null, "Split", new object[1] { ";" }, null, null, null);
					int num2 = array.Length - 1;
					for (int j = 0; j <= num2; j++)
					{
						DataRow dataRow = dataTable.NewRow();
						dataRow["Nombre"] = array[j];
						dataRow["NombreFisico"] = array[j];
						dataTable.Rows.Add(dataRow);
					}
					dataTable.Rows.RemoveAt(i);
				}
			}
			return new DataView(dataTable)
			{
				Sort = "NombreFisico"
			}.ToTable(true, "NombreFisico");
		}
	}

	public void GuardarImpresoras(string Nombre, string NombreFisico, bool ImprimirCuenta, bool ImprimirFactura, bool AbrirCaja, int ConfiguracionFacturaID, bool esMonitor)
	{
		clsImp._Nombre = Nombre;
		clsImp._NombreFisico = NombreFisico;
		clsImp._ImprimirCuenta = ImprimirCuenta;
		clsImp._ImprimirFactura = ImprimirFactura;
		clsImp._AbrirCaja = AbrirCaja;
		clsImp._esMonitorDigital = esMonitor;
		clsImp._ConfiguracionFacturaID = ConfiguracionFacturaID;
		if (clsImp._ImpresoraID == 0)
		{
			clsImp.Insertar();
		}
		else
		{
			clsImp.Modificar();
		}
	}

	public void EliminarImpresoras()
	{
		clsImp.Eliminar();
	}

	public string DevolverImpresora()
	{
		clsImp.DevolverImpresora();
		return clsImp._NombreFisico;
	}

	public string DevolverImprimirCierreKiky()
	{
		clsImp.DevolverImprimirCierreKiky();
		return clsImp._NombreFisico;
	}
}
