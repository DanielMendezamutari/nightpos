using System;
using System.CodeDom.Compiler;
using System.Collections;
using System.ComponentModel;
using System.ComponentModel.Design;
using System.Data;
using System.Diagnostics;
using System.IO;
using System.Runtime.CompilerServices;
using System.Runtime.Serialization;
using System.Xml;
using System.Xml.Schema;
using System.Xml.Serialization;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

[Serializable]
[DesignerCategory("code")]
[ToolboxItem(true)]
[XmlSchemaProvider("GetTypedDataSetSchema")]
[XmlRoot("dtsClientes")]
[HelpKeyword("vs.data.DataSet")]
public class dtsClientes : DataSet
{
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	public delegate void ClientesRowChangeEventHandler(object sender, ClientesRowChangeEvent e);

	[Serializable]
	[XmlSchemaProvider("GetTypedTableSchema")]
	public class ClientesDataTable : TypedTableBase<ClientesRow>
	{
		private DataColumn columnID;

		private DataColumn columnNombre;

		private DataColumn columnApellidos;

		private DataColumn columnCelular;

		private DataColumn columnSexo;

		private DataColumn columnCI;

		private DataColumn columnNacionalidad;

		private DataColumn columnComentarios;

		private DataColumn columnCorreo;

		private DataColumn columnCumpleanos;

		private DataColumn columnSaldo;

		private DataColumn columnDescuento;

		private DataColumn columnReferidoPor;

		private DataColumn columnFacturaCredito;

		private DataColumn columnActivo;

		private DataColumn columnDireccion;

		private DataColumn columnCodigo;

		private DataColumn columnMaxDeuda;

		private DataColumn columnNombreFactura;

		private DataColumn columnNombreApellido;

		private DataColumn columnTipoDocumentoID;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn IDColumn => columnID;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn NombreColumn => columnNombre;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn ApellidosColumn => columnApellidos;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn CelularColumn => columnCelular;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn SexoColumn => columnSexo;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn CIColumn => columnCI;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn NacionalidadColumn => columnNacionalidad;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn ComentariosColumn => columnComentarios;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn CorreoColumn => columnCorreo;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn CumpleanosColumn => columnCumpleanos;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn SaldoColumn => columnSaldo;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn DescuentoColumn => columnDescuento;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn ReferidoPorColumn => columnReferidoPor;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn FacturaCreditoColumn => columnFacturaCredito;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn ActivoColumn => columnActivo;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn DireccionColumn => columnDireccion;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn CodigoColumn => columnCodigo;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn MaxDeudaColumn => columnMaxDeuda;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn NombreFacturaColumn => columnNombreFactura;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn NombreApellidoColumn => columnNombreApellido;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataColumn TipoDocumentoIDColumn => columnTipoDocumentoID;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		[Browsable(false)]
		public int Count => base.Rows.Count;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public ClientesRow this[int index] => (ClientesRow)base.Rows[index];

		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public event ClientesRowChangeEventHandler ClientesRowChanging;

		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public event ClientesRowChangeEventHandler ClientesRowChanged;

		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public event ClientesRowChangeEventHandler ClientesRowDeleting;

		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public event ClientesRowChangeEventHandler ClientesRowDeleted;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public ClientesDataTable()
		{
			base.TableName = "Clientes";
			BeginInit();
			InitClass();
			EndInit();
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		internal ClientesDataTable(DataTable table)
		{
			base.TableName = table.TableName;
			if (table.CaseSensitive != table.DataSet.CaseSensitive)
			{
				base.CaseSensitive = table.CaseSensitive;
			}
			if (Operators.CompareString(table.Locale.ToString(), table.DataSet.Locale.ToString(), TextCompare: false) != 0)
			{
				base.Locale = table.Locale;
			}
			if (Operators.CompareString(table.Namespace, table.DataSet.Namespace, TextCompare: false) != 0)
			{
				base.Namespace = table.Namespace;
			}
			base.Prefix = table.Prefix;
			base.MinimumCapacity = table.MinimumCapacity;
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		protected ClientesDataTable(SerializationInfo info, StreamingContext context)
			: base(info, context)
		{
			InitVars();
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void AddClientesRow(ClientesRow row)
		{
			base.Rows.Add(row);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public ClientesRow AddClientesRow(string Nombre, string Apellidos, int Celular, bool Sexo, string CI, string Nacionalidad, string Comentarios, string Correo, DateTime Cumpleanos, double Saldo, double Descuento, int ReferidoPor, byte FacturaCredito, bool Activo, string Direccion, string Codigo, double MaxDeuda, string NombreFactura, string NombreApellido, string TipoDocumentoID)
		{
			ClientesRow clientesRow = (ClientesRow)NewRow();
			object[] itemArray = new object[21]
			{
				null, Nombre, Apellidos, Celular, Sexo, CI, Nacionalidad, Comentarios, Correo, Cumpleanos,
				Saldo, Descuento, ReferidoPor, FacturaCredito, Activo, Direccion, Codigo, MaxDeuda, NombreFactura, NombreApellido,
				TipoDocumentoID
			};
			clientesRow.ItemArray = itemArray;
			base.Rows.Add(clientesRow);
			return clientesRow;
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public ClientesRow FindByID(int ID)
		{
			return (ClientesRow)base.Rows.Find(new object[1] { ID });
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public override DataTable Clone()
		{
			ClientesDataTable obj = (ClientesDataTable)base.Clone();
			obj.InitVars();
			return obj;
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		protected override DataTable CreateInstance()
		{
			return new ClientesDataTable();
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		internal void InitVars()
		{
			columnID = base.Columns["ID"];
			columnNombre = base.Columns["Nombre"];
			columnApellidos = base.Columns["Apellidos"];
			columnCelular = base.Columns["Celular"];
			columnSexo = base.Columns["Sexo"];
			columnCI = base.Columns["CI"];
			columnNacionalidad = base.Columns["Nacionalidad"];
			columnComentarios = base.Columns["Comentarios"];
			columnCorreo = base.Columns["Correo"];
			columnCumpleanos = base.Columns["Cumpleanos"];
			columnSaldo = base.Columns["Saldo"];
			columnDescuento = base.Columns["Descuento"];
			columnReferidoPor = base.Columns["ReferidoPor"];
			columnFacturaCredito = base.Columns["FacturaCredito"];
			columnActivo = base.Columns["Activo"];
			columnDireccion = base.Columns["Direccion"];
			columnCodigo = base.Columns["Codigo"];
			columnMaxDeuda = base.Columns["MaxDeuda"];
			columnNombreFactura = base.Columns["NombreFactura"];
			columnNombreApellido = base.Columns["NombreApellido"];
			columnTipoDocumentoID = base.Columns["TipoDocumentoID"];
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		private void InitClass()
		{
			columnID = new DataColumn("ID", typeof(int), null, MappingType.Element);
			base.Columns.Add(columnID);
			columnNombre = new DataColumn("Nombre", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnNombre);
			columnApellidos = new DataColumn("Apellidos", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnApellidos);
			columnCelular = new DataColumn("Celular", typeof(int), null, MappingType.Element);
			base.Columns.Add(columnCelular);
			columnSexo = new DataColumn("Sexo", typeof(bool), null, MappingType.Element);
			base.Columns.Add(columnSexo);
			columnCI = new DataColumn("CI", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnCI);
			columnNacionalidad = new DataColumn("Nacionalidad", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnNacionalidad);
			columnComentarios = new DataColumn("Comentarios", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnComentarios);
			columnCorreo = new DataColumn("Correo", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnCorreo);
			columnCumpleanos = new DataColumn("Cumpleanos", typeof(DateTime), null, MappingType.Element);
			base.Columns.Add(columnCumpleanos);
			columnSaldo = new DataColumn("Saldo", typeof(double), null, MappingType.Element);
			base.Columns.Add(columnSaldo);
			columnDescuento = new DataColumn("Descuento", typeof(double), null, MappingType.Element);
			base.Columns.Add(columnDescuento);
			columnReferidoPor = new DataColumn("ReferidoPor", typeof(int), null, MappingType.Element);
			base.Columns.Add(columnReferidoPor);
			columnFacturaCredito = new DataColumn("FacturaCredito", typeof(byte), null, MappingType.Element);
			base.Columns.Add(columnFacturaCredito);
			columnActivo = new DataColumn("Activo", typeof(bool), null, MappingType.Element);
			base.Columns.Add(columnActivo);
			columnDireccion = new DataColumn("Direccion", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnDireccion);
			columnCodigo = new DataColumn("Codigo", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnCodigo);
			columnMaxDeuda = new DataColumn("MaxDeuda", typeof(double), null, MappingType.Element);
			base.Columns.Add(columnMaxDeuda);
			columnNombreFactura = new DataColumn("NombreFactura", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnNombreFactura);
			columnNombreApellido = new DataColumn("NombreApellido", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnNombreApellido);
			columnTipoDocumentoID = new DataColumn("TipoDocumentoID", typeof(string), null, MappingType.Element);
			base.Columns.Add(columnTipoDocumentoID);
			base.Constraints.Add(new UniqueConstraint("Constraint1", new DataColumn[1] { columnID }, isPrimaryKey: true));
			columnID.AutoIncrement = true;
			columnID.AutoIncrementSeed = -1L;
			columnID.AutoIncrementStep = -1L;
			columnID.AllowDBNull = false;
			columnID.ReadOnly = true;
			columnID.Unique = true;
			columnNombre.AllowDBNull = false;
			columnNombre.MaxLength = 200;
			columnApellidos.AllowDBNull = false;
			columnApellidos.MaxLength = 200;
			columnCelular.AllowDBNull = false;
			columnSexo.AllowDBNull = false;
			columnCI.AllowDBNull = false;
			columnCI.MaxLength = 50;
			columnNacionalidad.AllowDBNull = false;
			columnNacionalidad.MaxLength = 50;
			columnComentarios.AllowDBNull = false;
			columnComentarios.MaxLength = 200;
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public ClientesRow NewClientesRow()
		{
			return (ClientesRow)NewRow();
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		protected override DataRow NewRowFromBuilder(DataRowBuilder builder)
		{
			return new ClientesRow(builder);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		protected override Type GetRowType()
		{
			return typeof(ClientesRow);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		protected override void OnRowChanged(DataRowChangeEventArgs e)
		{
			base.OnRowChanged(e);
			if (ClientesRowChanged != null)
			{
				ClientesRowChanged?.Invoke(this, new ClientesRowChangeEvent((ClientesRow)e.Row, e.Action));
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		protected override void OnRowChanging(DataRowChangeEventArgs e)
		{
			base.OnRowChanging(e);
			if (ClientesRowChanging != null)
			{
				ClientesRowChanging?.Invoke(this, new ClientesRowChangeEvent((ClientesRow)e.Row, e.Action));
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		protected override void OnRowDeleted(DataRowChangeEventArgs e)
		{
			base.OnRowDeleted(e);
			if (ClientesRowDeleted != null)
			{
				ClientesRowDeleted?.Invoke(this, new ClientesRowChangeEvent((ClientesRow)e.Row, e.Action));
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		protected override void OnRowDeleting(DataRowChangeEventArgs e)
		{
			base.OnRowDeleting(e);
			if (ClientesRowDeleting != null)
			{
				ClientesRowDeleting?.Invoke(this, new ClientesRowChangeEvent((ClientesRow)e.Row, e.Action));
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void RemoveClientesRow(ClientesRow row)
		{
			base.Rows.Remove(row);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public static XmlSchemaComplexType GetTypedTableSchema(XmlSchemaSet xs)
		{
			XmlSchemaComplexType xmlSchemaComplexType = new XmlSchemaComplexType();
			XmlSchemaSequence xmlSchemaSequence = new XmlSchemaSequence();
			dtsClientes dtsClientes2 = new dtsClientes();
			XmlSchemaAny xmlSchemaAny = new XmlSchemaAny();
			xmlSchemaAny.Namespace = "http://www.w3.org/2001/XMLSchema";
			xmlSchemaAny.MinOccurs = 0m;
			xmlSchemaAny.MaxOccurs = decimal.MaxValue;
			xmlSchemaAny.ProcessContents = XmlSchemaContentProcessing.Lax;
			xmlSchemaSequence.Items.Add(xmlSchemaAny);
			XmlSchemaAny xmlSchemaAny2 = new XmlSchemaAny();
			xmlSchemaAny2.Namespace = "urn:schemas-microsoft-com:xml-diffgram-v1";
			xmlSchemaAny2.MinOccurs = 1m;
			xmlSchemaAny2.ProcessContents = XmlSchemaContentProcessing.Lax;
			xmlSchemaSequence.Items.Add(xmlSchemaAny2);
			XmlSchemaAttribute xmlSchemaAttribute = new XmlSchemaAttribute();
			xmlSchemaAttribute.Name = "namespace";
			xmlSchemaAttribute.FixedValue = dtsClientes2.Namespace;
			xmlSchemaComplexType.Attributes.Add(xmlSchemaAttribute);
			XmlSchemaAttribute xmlSchemaAttribute2 = new XmlSchemaAttribute();
			xmlSchemaAttribute2.Name = "tableTypeName";
			xmlSchemaAttribute2.FixedValue = "ClientesDataTable";
			xmlSchemaComplexType.Attributes.Add(xmlSchemaAttribute2);
			xmlSchemaComplexType.Particle = xmlSchemaSequence;
			XmlSchema schemaSerializable = dtsClientes2.GetSchemaSerializable();
			if (xs.Contains(schemaSerializable.TargetNamespace))
			{
				MemoryStream memoryStream = new MemoryStream();
				MemoryStream memoryStream2 = new MemoryStream();
				try
				{
					schemaSerializable.Write(memoryStream);
					IEnumerator enumerator = xs.Schemas(schemaSerializable.TargetNamespace).GetEnumerator();
					while (enumerator.MoveNext())
					{
						XmlSchema obj = (XmlSchema)enumerator.Current;
						memoryStream2.SetLength(0L);
						obj.Write(memoryStream2);
						if (memoryStream.Length == memoryStream2.Length)
						{
							memoryStream.Position = 0L;
							memoryStream2.Position = 0L;
							while (memoryStream.Position != memoryStream.Length && memoryStream.ReadByte() == memoryStream2.ReadByte())
							{
							}
							if (memoryStream.Position == memoryStream.Length)
							{
								return xmlSchemaComplexType;
							}
						}
					}
				}
				finally
				{
					memoryStream?.Close();
					memoryStream2?.Close();
				}
			}
			xs.Add(schemaSerializable);
			return xmlSchemaComplexType;
		}
	}

	public class ClientesRow : DataRow
	{
		private ClientesDataTable tableClientes;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public int ID
		{
			get
			{
				return Conversions.ToInteger(base[tableClientes.IDColumn]);
			}
			set
			{
				base[tableClientes.IDColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string Nombre
		{
			get
			{
				return Conversions.ToString(base[tableClientes.NombreColumn]);
			}
			set
			{
				base[tableClientes.NombreColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string Apellidos
		{
			get
			{
				return Conversions.ToString(base[tableClientes.ApellidosColumn]);
			}
			set
			{
				base[tableClientes.ApellidosColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public int Celular
		{
			get
			{
				return Conversions.ToInteger(base[tableClientes.CelularColumn]);
			}
			set
			{
				base[tableClientes.CelularColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool Sexo
		{
			get
			{
				return Conversions.ToBoolean(base[tableClientes.SexoColumn]);
			}
			set
			{
				base[tableClientes.SexoColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string CI
		{
			get
			{
				return Conversions.ToString(base[tableClientes.CIColumn]);
			}
			set
			{
				base[tableClientes.CIColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string Nacionalidad
		{
			get
			{
				return Conversions.ToString(base[tableClientes.NacionalidadColumn]);
			}
			set
			{
				base[tableClientes.NacionalidadColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string Comentarios
		{
			get
			{
				return Conversions.ToString(base[tableClientes.ComentariosColumn]);
			}
			set
			{
				base[tableClientes.ComentariosColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string Correo
		{
			get
			{
				try
				{
					return Conversions.ToString(base[tableClientes.CorreoColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'Correo' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.CorreoColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DateTime Cumpleanos
		{
			get
			{
				try
				{
					return Conversions.ToDate(base[tableClientes.CumpleanosColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'Cumpleanos' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.CumpleanosColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public double Saldo
		{
			get
			{
				try
				{
					return Conversions.ToDouble(base[tableClientes.SaldoColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'Saldo' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.SaldoColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public double Descuento
		{
			get
			{
				try
				{
					return Conversions.ToDouble(base[tableClientes.DescuentoColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'Descuento' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.DescuentoColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public int ReferidoPor
		{
			get
			{
				try
				{
					return Conversions.ToInteger(base[tableClientes.ReferidoPorColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'ReferidoPor' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.ReferidoPorColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public byte FacturaCredito
		{
			get
			{
				try
				{
					return Conversions.ToByte(base[tableClientes.FacturaCreditoColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'FacturaCredito' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.FacturaCreditoColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool Activo
		{
			get
			{
				try
				{
					return Conversions.ToBoolean(base[tableClientes.ActivoColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'Activo' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.ActivoColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string Direccion
		{
			get
			{
				try
				{
					return Conversions.ToString(base[tableClientes.DireccionColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'Direccion' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.DireccionColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string Codigo
		{
			get
			{
				try
				{
					return Conversions.ToString(base[tableClientes.CodigoColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'Codigo' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.CodigoColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public double MaxDeuda
		{
			get
			{
				try
				{
					return Conversions.ToDouble(base[tableClientes.MaxDeudaColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'MaxDeuda' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.MaxDeudaColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string NombreFactura
		{
			get
			{
				try
				{
					return Conversions.ToString(base[tableClientes.NombreFacturaColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'NombreFactura' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.NombreFacturaColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string NombreApellido
		{
			get
			{
				try
				{
					return Conversions.ToString(base[tableClientes.NombreApellidoColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'NombreApellido' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.NombreApellidoColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public string TipoDocumentoID
		{
			get
			{
				try
				{
					return Conversions.ToString(base[tableClientes.TipoDocumentoIDColumn]);
				}
				catch (InvalidCastException ex)
				{
					ProjectData.SetProjectError(ex);
					InvalidCastException innerException = ex;
					throw new StrongTypingException("The value for column 'TipoDocumentoID' in table 'Clientes' is DBNull.", innerException);
				}
			}
			set
			{
				base[tableClientes.TipoDocumentoIDColumn] = value;
			}
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		internal ClientesRow(DataRowBuilder rb)
			: base(rb)
		{
			tableClientes = (ClientesDataTable)base.Table;
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsCorreoNull()
		{
			return IsNull(tableClientes.CorreoColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetCorreoNull()
		{
			base[tableClientes.CorreoColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsCumpleanosNull()
		{
			return IsNull(tableClientes.CumpleanosColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetCumpleanosNull()
		{
			base[tableClientes.CumpleanosColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsSaldoNull()
		{
			return IsNull(tableClientes.SaldoColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetSaldoNull()
		{
			base[tableClientes.SaldoColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsDescuentoNull()
		{
			return IsNull(tableClientes.DescuentoColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetDescuentoNull()
		{
			base[tableClientes.DescuentoColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsReferidoPorNull()
		{
			return IsNull(tableClientes.ReferidoPorColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetReferidoPorNull()
		{
			base[tableClientes.ReferidoPorColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsFacturaCreditoNull()
		{
			return IsNull(tableClientes.FacturaCreditoColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetFacturaCreditoNull()
		{
			base[tableClientes.FacturaCreditoColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsActivoNull()
		{
			return IsNull(tableClientes.ActivoColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetActivoNull()
		{
			base[tableClientes.ActivoColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsDireccionNull()
		{
			return IsNull(tableClientes.DireccionColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetDireccionNull()
		{
			base[tableClientes.DireccionColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsCodigoNull()
		{
			return IsNull(tableClientes.CodigoColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetCodigoNull()
		{
			base[tableClientes.CodigoColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsMaxDeudaNull()
		{
			return IsNull(tableClientes.MaxDeudaColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetMaxDeudaNull()
		{
			base[tableClientes.MaxDeudaColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsNombreFacturaNull()
		{
			return IsNull(tableClientes.NombreFacturaColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetNombreFacturaNull()
		{
			base[tableClientes.NombreFacturaColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsNombreApellidoNull()
		{
			return IsNull(tableClientes.NombreApellidoColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetNombreApellidoNull()
		{
			base[tableClientes.NombreApellidoColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public bool IsTipoDocumentoIDNull()
		{
			return IsNull(tableClientes.TipoDocumentoIDColumn);
		}

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public void SetTipoDocumentoIDNull()
		{
			base[tableClientes.TipoDocumentoIDColumn] = RuntimeHelpers.GetObjectValue(Convert.DBNull);
		}
	}

	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	public class ClientesRowChangeEvent : EventArgs
	{
		private ClientesRow eventRow;

		private DataRowAction eventAction;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public ClientesRow Row => eventRow;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public DataRowAction Action => eventAction;

		[DebuggerNonUserCode]
		[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
		public ClientesRowChangeEvent(ClientesRow row, DataRowAction action)
		{
			eventRow = row;
			eventAction = action;
		}
	}

	private ClientesDataTable tableClientes;

	private SchemaSerializationMode _schemaSerializationMode;

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	[Browsable(false)]
	[DesignerSerializationVisibility(DesignerSerializationVisibility.Content)]
	public ClientesDataTable Clientes => tableClientes;

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	[Browsable(true)]
	[DesignerSerializationVisibility(DesignerSerializationVisibility.Visible)]
	public override SchemaSerializationMode SchemaSerializationMode
	{
		get
		{
			return _schemaSerializationMode;
		}
		set
		{
			_schemaSerializationMode = value;
		}
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	[DesignerSerializationVisibility(DesignerSerializationVisibility.Hidden)]
	public new DataTableCollection Tables => base.Tables;

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	[DesignerSerializationVisibility(DesignerSerializationVisibility.Hidden)]
	public new DataRelationCollection Relations => base.Relations;

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	public dtsClientes()
	{
		_schemaSerializationMode = SchemaSerializationMode.IncludeSchema;
		BeginInit();
		InitClass();
		CollectionChangeEventHandler value = SchemaChanged;
		base.Tables.CollectionChanged += value;
		base.Relations.CollectionChanged += value;
		EndInit();
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	protected dtsClientes(SerializationInfo info, StreamingContext context)
		: base(info, context, ConstructSchema: false)
	{
		_schemaSerializationMode = SchemaSerializationMode.IncludeSchema;
		if (IsBinarySerialized(info, context))
		{
			InitVars(initTable: false);
			CollectionChangeEventHandler value = SchemaChanged;
			Tables.CollectionChanged += value;
			Relations.CollectionChanged += value;
			return;
		}
		string s = Conversions.ToString(info.GetValue("XmlSchema", typeof(string)));
		if (DetermineSchemaSerializationMode(info, context) == SchemaSerializationMode.IncludeSchema)
		{
			DataSet dataSet = new DataSet();
			dataSet.ReadXmlSchema(new XmlTextReader(new StringReader(s)));
			if (dataSet.Tables["Clientes"] != null)
			{
				base.Tables.Add(new ClientesDataTable(dataSet.Tables["Clientes"]));
			}
			base.DataSetName = dataSet.DataSetName;
			base.Prefix = dataSet.Prefix;
			base.Namespace = dataSet.Namespace;
			base.Locale = dataSet.Locale;
			base.CaseSensitive = dataSet.CaseSensitive;
			base.EnforceConstraints = dataSet.EnforceConstraints;
			Merge(dataSet, preserveChanges: false, MissingSchemaAction.Add);
			InitVars();
		}
		else
		{
			ReadXmlSchema(new XmlTextReader(new StringReader(s)));
		}
		GetSerializationData(info, context);
		CollectionChangeEventHandler value2 = SchemaChanged;
		base.Tables.CollectionChanged += value2;
		Relations.CollectionChanged += value2;
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	protected override void InitializeDerivedDataSet()
	{
		BeginInit();
		InitClass();
		EndInit();
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	public override DataSet Clone()
	{
		dtsClientes obj = (dtsClientes)base.Clone();
		obj.InitVars();
		obj.SchemaSerializationMode = SchemaSerializationMode;
		return obj;
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	protected override bool ShouldSerializeTables()
	{
		return false;
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	protected override bool ShouldSerializeRelations()
	{
		return false;
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	protected override void ReadXmlSerializable(XmlReader reader)
	{
		if (DetermineSchemaSerializationMode(reader) == SchemaSerializationMode.IncludeSchema)
		{
			Reset();
			DataSet dataSet = new DataSet();
			dataSet.ReadXml(reader);
			if (dataSet.Tables["Clientes"] != null)
			{
				base.Tables.Add(new ClientesDataTable(dataSet.Tables["Clientes"]));
			}
			base.DataSetName = dataSet.DataSetName;
			base.Prefix = dataSet.Prefix;
			base.Namespace = dataSet.Namespace;
			base.Locale = dataSet.Locale;
			base.CaseSensitive = dataSet.CaseSensitive;
			base.EnforceConstraints = dataSet.EnforceConstraints;
			Merge(dataSet, preserveChanges: false, MissingSchemaAction.Add);
			InitVars();
		}
		else
		{
			ReadXml(reader);
			InitVars();
		}
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	protected override XmlSchema GetSchemaSerializable()
	{
		MemoryStream memoryStream = new MemoryStream();
		WriteXmlSchema(new XmlTextWriter(memoryStream, null));
		memoryStream.Position = 0L;
		return XmlSchema.Read(new XmlTextReader(memoryStream), null);
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	internal void InitVars()
	{
		InitVars(initTable: true);
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	internal void InitVars(bool initTable)
	{
		tableClientes = (ClientesDataTable)base.Tables["Clientes"];
		if (initTable && tableClientes != null)
		{
			tableClientes.InitVars();
		}
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	private void InitClass()
	{
		base.DataSetName = "dtsClientes";
		base.Prefix = "";
		base.Namespace = "http://tempuri.org/dtsClientes.xsd";
		base.EnforceConstraints = true;
		SchemaSerializationMode = SchemaSerializationMode.IncludeSchema;
		tableClientes = new ClientesDataTable();
		base.Tables.Add(tableClientes);
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	private bool ShouldSerializeClientes()
	{
		return false;
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	private void SchemaChanged(object sender, CollectionChangeEventArgs e)
	{
		if (e.Action == CollectionChangeAction.Remove)
		{
			InitVars();
		}
	}

	[DebuggerNonUserCode]
	[GeneratedCode("System.Data.Design.TypedDataSetGenerator", "17.0.0.0")]
	public static XmlSchemaComplexType GetTypedDataSetSchema(XmlSchemaSet xs)
	{
		dtsClientes dtsClientes2 = new dtsClientes();
		XmlSchemaComplexType xmlSchemaComplexType = new XmlSchemaComplexType();
		XmlSchemaSequence xmlSchemaSequence = new XmlSchemaSequence();
		XmlSchemaAny xmlSchemaAny = new XmlSchemaAny();
		xmlSchemaAny.Namespace = dtsClientes2.Namespace;
		xmlSchemaSequence.Items.Add(xmlSchemaAny);
		xmlSchemaComplexType.Particle = xmlSchemaSequence;
		XmlSchema schemaSerializable = dtsClientes2.GetSchemaSerializable();
		if (xs.Contains(schemaSerializable.TargetNamespace))
		{
			MemoryStream memoryStream = new MemoryStream();
			MemoryStream memoryStream2 = new MemoryStream();
			try
			{
				schemaSerializable.Write(memoryStream);
				IEnumerator enumerator = xs.Schemas(schemaSerializable.TargetNamespace).GetEnumerator();
				while (enumerator.MoveNext())
				{
					XmlSchema obj = (XmlSchema)enumerator.Current;
					memoryStream2.SetLength(0L);
					obj.Write(memoryStream2);
					if (memoryStream.Length == memoryStream2.Length)
					{
						memoryStream.Position = 0L;
						memoryStream2.Position = 0L;
						while (memoryStream.Position != memoryStream.Length && memoryStream.ReadByte() == memoryStream2.ReadByte())
						{
						}
						if (memoryStream.Position == memoryStream.Length)
						{
							return xmlSchemaComplexType;
						}
					}
				}
			}
			finally
			{
				memoryStream?.Close();
				memoryStream2?.Close();
			}
		}
		xs.Add(schemaSerializable);
		return xmlSchemaComplexType;
	}
}
