using System.Data;
using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlPreparacionesComodines
{
	private readonly clsPreparacionesComodines clsPre;

	public ctlPreparacionesComodines()
	{
		clsPre = new clsPreparacionesComodines();
	}

	public int GetPreparacionComodinID()
	{
		return clsPre._PreparacionComodinID;
	}

	public void SetPreparacionComodinID(int ID)
	{
		clsPre._PreparacionComodinID = ID;
	}

	public clsPreparacionesComodines LlenarClase()
	{
		clsPre.llenarclase();
		return clsPre;
	}

	public DataTable DevolverPreparacionesParaProducto(int ParaProductoID, int almacenID)
	{
		return BD.ConsultaVer("Preparaciones.PreparacionID,Preparaciones.Cantidad as Cantidad, Preparaciones.Concepto , sum(Productos.Stock" + Conversions.ToString(almacenID) + "*Productos.CantidadML) as enStock,Productos.TienePreparacion, avg(Productos.CantidadML) as cantML", "((Preparaciones inner join PreparacionesComodines on PreparacionesComodines.PreparacionID=Preparaciones.PreparacionID) inner join Productos on Productos.ID =PreparacionesComodines.DeProductoID) ", "Preparaciones.ParaProductoID =" + ParaProductoID, "Preparaciones.PreparacionID", "Preparaciones.PreparacionID,Preparaciones.Cantidad, Preparaciones.Concepto,Productos.TienePreparacion");
	}

	public DataTable DevolverPreparacionesComodinesParaOrdenPedido(int preparacionID, int almacenID)
	{
		return BD.ConsultaVer("Productos.ID as ProductoID, Productos.Stock" + Conversions.ToString(almacenID) + " as stock, Productos.CantidadML, Productos.Costo, Productos.Nombre", "PreparacionesComodines inner join Productos on Productos.ID =PreparacionesComodines.DeProductoID", "PreparacionID=" + preparacionID, "PreparacionesComodines.PreparacionComodinID");
	}

	public DataTable DevolverPreparacionesParaProductoDeTablas(int ParaProductoID)
	{
		return BD.ConsultaVer("Preparaciones.PreparacionId, Preparaciones.Concepto , Cantidad, PuedeDisminuir,Productos.ID as deProductoID, Productos.Nombre as deProducto , PreparacionesComodines.Precio, PreparacionesComodines.ModificaPrecio,PreparacionesComodines.ModificaProductoID, P2.Nombre as ModificaProducto ", "(((Preparaciones inner join PreparacionesComodines on PreparacionesComodines.PreparacionID=Preparaciones.PreparacionID) inner join Productos on Productos.ID =PreparacionesComodines.DeProductoID)  left join Productos as P2 on P2.ID =PreparacionesComodines.ModificaProductoID) ", "Preparaciones.ParaProductoID =" + ParaProductoID, "Preparaciones.PreparacionID");
	}

	public DataTable devolverPreparacionesComodinesParaPreparaciones1(int preparacionID)
	{
		clsPre._PreparacionID = preparacionID;
		return clsPre.devolverPreparacionesComodinesParaPreparaciones();
	}

	public void GuardarPreparacionComodin(int deProductoID, int preparacionID, double precio, bool modificaPrecio, int ModificaProductoID, BD_SQL bd1 = null)
	{
		clsPre._PreparacionID = preparacionID;
		clsPre._deProductoID = deProductoID;
		clsPre._Precio = precio;
		clsPre._modificaPrecio = modificaPrecio;
		clsPre._ModificaProductoID = ModificaProductoID;
		if (clsPre._PreparacionComodinID == 0)
		{
			clsPre.Insertar(bd1);
		}
		else
		{
			clsPre.Modificar();
		}
	}

	public void EliminarPreparacionComodin()
	{
		clsPre.Eliminar();
	}

	public void EliminarPreparacionesID(int preparacionID)
	{
		clsPre._PreparacionID = preparacionID;
		clsPre.EliminarPreparacionID();
	}

	public void getProductsRecursive(int prodID, ref DataTable tbFinal, double Cantidad, bool telefono, int almancenID, int prodPadreID)
	{
		DataTable dataTable = new ctlPreparacionesComodines().DevolverPreparacionesParaProducto(prodID, almancenID);
		if (((prodID != prodPadreID) & (prodPadreID > 0)) && dataTable.Rows.Count == 0)
		{
			ctlProductos ctlProductos2 = new ctlProductos();
			ctlProductos2.SetProductoID(prodID);
			if (ctlProductos2.llenarByID() && !ctlProductos2.tienePreparacion())
			{
				DataRow dataRow = tbFinal.NewRow();
				dataRow[0] = 0;
				dataRow[1] = 1.0 * Cantidad;
				dataRow[2] = ctlProductos2.getNombre() + " extra";
				dataRow[3] = ctlProductos2.getStock();
				dataRow[4] = ctlProductos2.tienePreparacion();
				dataRow[5] = 0;
				tbFinal.Rows.Add(dataRow);
				return;
			}
		}
		checked
		{
			int num = dataTable.Rows.Count - 1;
			for (int i = 0; i <= num; i++)
			{
				if (Conversions.ToBoolean(Operators.NotObject(dataTable.Rows[i]["TienePreparacion"])))
				{
					DataRow dataRow2 = tbFinal.NewRow();
					dataRow2[0] = RuntimeHelpers.GetObjectValue(dataTable.Rows[i][0]);
					dataRow2[1] = Operators.MultiplyObject(dataTable.Rows[i][1], Cantidad);
					dataRow2[2] = RuntimeHelpers.GetObjectValue(dataTable.Rows[i][2]);
					dataRow2[3] = RuntimeHelpers.GetObjectValue(dataTable.Rows[i][3]);
					dataRow2[4] = RuntimeHelpers.GetObjectValue(dataTable.Rows[i][4]);
					dataRow2[5] = 0;
					tbFinal.Rows.Add(dataRow2);
					continue;
				}
				double cantidad = Conversions.ToDouble(Operators.DivideObject(Operators.MultiplyObject(Cantidad, dataTable.Rows[i]["Cantidad"]), dataTable.Rows[i]["cantML"]));
				DataTable dataTable2 = BD.ConsultaVer("DeProductoID", "PreparacionesComodines", Conversions.ToString(Operators.ConcatenateObject("PreparacionId=", dataTable.Rows[i]["PreparacionId"])));
				if (dataTable2.Rows.Count > 1)
				{
					ctlProductos ctlProductos3 = new ctlProductos();
					ctlProductos3.SetProductoID(prodID);
					if (ctlProductos3.llenarByID())
					{
						if (!ctlProductos3.EsCombo())
						{
							if (!telefono)
							{
								Interaction.MsgBox("Un producto con preparacion no puede tener mas de una posible opcion");
							}
							break;
						}
						getProductsRecursive(Conversions.ToInteger(dataTable2.Rows[0]["DeProductoID"]), ref tbFinal, cantidad, telefono, almancenID, prodID);
					}
					else if (!telefono)
					{
						Interaction.MsgBox("Un producto con preparacion mal seteado");
					}
				}
				else
				{
					if (Operators.ConditionalCompareObjectEqual(prodPadreID, dataTable2.Rows[0]["DeProductoID"], TextCompare: false))
					{
						clsProductos clsProductos2 = new clsProductos();
						clsProductos2._ID = Conversions.ToInteger(dataTable2.Rows[0]["DeProductoID"]);
						clsProductos2.CargarDatos();
						Interaction.MsgBox("El producto apunta a el mismo en una receta, prod ID " + clsProductos2._Nombre);
						break;
					}
					getProductsRecursive(Conversions.ToInteger(dataTable2.Rows[0]["DeProductoID"]), ref tbFinal, cantidad, telefono, almancenID, prodID);
				}
			}
		}
	}
}
