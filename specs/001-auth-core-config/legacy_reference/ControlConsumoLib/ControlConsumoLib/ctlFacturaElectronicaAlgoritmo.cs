using System;
using System.Globalization;
using System.IO;
using System.IO.Compression;
using System.Numerics;
using System.Security.Cryptography;
using System.Security.Cryptography.X509Certificates;
using System.Security.Cryptography.Xml;
using System.Text;
using System.Xml;
using Force.Crc32;
using ICSharpCode.SharpZipLib.Tar;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlFacturaElectronicaAlgoritmo
{
	public string calculaDigitoMod11(string cadena, int numDig, int limMult, bool x10)
	{
		if (!x10)
		{
			numDig = 1;
		}
		int num = numDig;
		checked
		{
			for (int i = 1; i <= num; i++)
			{
				int num2 = 0;
				int num3 = 2;
				for (int num4 = cadena.Length - 1; num4 >= 0; num4--)
				{
					num2 += num3 * int.Parse(cadena.Substring(num4, 1));
					num3++;
					if (num3 > limMult)
					{
						num3 = 2;
					}
				}
				unchecked
				{
					int num5 = ((!x10) ? (num2 % 11) : (checked(num2 * 10) % 11 % 10));
					if (num5 == 10)
					{
						cadena += "1";
					}
					if (num5 == 11)
					{
						cadena += "0";
					}
					if (num5 < 10)
					{
						cadena += num5;
					}
				}
			}
			return cadena.Substring(cadena.Length - numDig, 1);
		}
	}

	public string getAlgoritmoHashSHA256(byte[] mensajeBytesArray)
	{
		string text = "";
		try
		{
			SHA256Managed sHA256Managed = new SHA256Managed();
			text = BitConverter.ToString(sHA256Managed.ComputeHash(mensajeBytesArray), 0).ToLower();
			sHA256Managed.Clear();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			text = "";
			ProjectData.ClearProjectError();
		}
		return text;
	}

	public string getAlgoritmoHashCRC32(byte[] mensajeBytesArray)
	{
		string text = "";
		try
		{
			text = BitConverter.ToString(new Crc32Algorithm().ComputeHash(mensajeBytesArray), 0).ToUpper();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			text = "";
			ProjectData.ClearProjectError();
		}
		return text;
	}

	public string RemoverCerosDelPrincipio(string str)
	{
		while (str.StartsWith("0"))
		{
			str = str.Remove(0, 1);
		}
		return str;
	}

	public string CompleteCero(string pString, short pMaxChar, bool pRigth = false)
	{
		string text = pString;
		checked
		{
			if (pString.Length < pMaxChar)
			{
				int length = pString.Length;
				int num = pMaxChar - 1;
				for (int i = length; i <= num; i++)
				{
					text = "0" + text;
				}
			}
			return text;
		}
	}

	public string Base16(string pString)
	{
		return BigInteger.Parse(pString).ToString("X");
	}

	public string Base10(string pString)
	{
		return BigInteger.Parse(pString, NumberStyles.HexNumber).ToString();
	}

	public bool TARcompress(string origen, string destino)
	{
		bool result;
		try
		{
			Stream outputStream = File.Create(destino);
			TarOutputStream tarOutputStream = new TarOutputStream(outputStream, Encoding.UTF8);
			bool num = compressTar(tarOutputStream, origen);
			tarOutputStream.Close();
			result = num;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox("Tar compress. " + ex2.Message);
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	private bool compressTar(TarOutputStream tarOutputStream, string sourceDirectory)
	{
		bool result;
		try
		{
			string[] files = Directory.GetFiles(sourceDirectory, "*.xml");
			foreach (string text in files)
			{
				using (Stream stream = File.OpenRead(text))
				{
					string name = new FileInfo(text).Name;
					long length = stream.Length;
					TarEntry tarEntry = TarEntry.CreateTarEntry(name);
					tarEntry.Size = length;
					tarOutputStream.PutNextEntry(tarEntry);
					byte[] array = new byte[32768];
					while (true)
					{
						int num = stream.Read(array, 0, array.Length);
						if (num > 0)
						{
							tarOutputStream.Write(array, 0, num);
							continue;
						}
						break;
					}
				}
				tarOutputStream.CloseEntry();
			}
			result = true;
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

	public bool CompressGZIP(FileInfo fileToCompress)
	{
		bool result;
		try
		{
			using FileStream fileStream = fileToCompress.OpenRead();
			if (File.GetAttributes(fileToCompress.FullName) != 0)
			{
				using FileStream stream = File.Create(fileToCompress.FullName + ".zip");
				using GZipStream destination = new GZipStream(stream, CompressionMode.Compress);
				fileStream.CopyTo(destination);
			}
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox("CompressGZIP. " + ex2.Message);
			result = false;
			ProjectData.ClearProjectError();
			goto IL_008e;
		}
		result = true;
		goto IL_008e;
		IL_008e:
		return result;
	}

	public bool CompressGZIPmemory(byte[] Data, ref byte[] streamResult)
	{
		bool result;
		try
		{
			using MemoryStream memoryStream = new MemoryStream();
			using GZipStream gZipStream = new GZipStream(memoryStream, CompressionMode.Compress);
			gZipStream.Write(Data, 0, Data.Length);
			gZipStream.Close();
			streamResult = memoryStream.ToArray();
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			Interaction.MsgBox("CompressGZIPmemory." + ex2.Message);
			result = false;
			ProjectData.ClearProjectError();
			goto IL_006b;
		}
		result = true;
		goto IL_006b;
		IL_006b:
		return result;
	}

	public string GenerarCUF(long NitEmisor, DateTime Fecha1, int Sucursal1, int Modalidad1, int TipoEmision1, int TipoFactura1, int TipoDocumentoSector1, int NroFactura1, int PuntoVenta1, string codigoControlCUFD)
	{
		string text = CompleteCero(Conversions.ToString(NitEmisor), 13);
		string text2 = CompleteCero(Fecha1.ToString("yyyyMMddHHmmss000"), 17);
		string text3 = CompleteCero(Conversions.ToString(Sucursal1), 4);
		string text4 = Modalidad1.ToString();
		string text5 = TipoEmision1.ToString();
		string text6 = TipoFactura1.ToString();
		string text7 = CompleteCero(Conversions.ToString(TipoDocumentoSector1), 2);
		string text8 = CompleteCero(Conversions.ToString(NroFactura1), 10);
		string text9 = CompleteCero(Conversions.ToString(PuntoVenta1), 4);
		string text10 = text + text2 + text3 + text4 + text5 + text6 + text7 + text8 + text9;
		string text11 = calculaDigitoMod11(text10, 1, 9, x10: false);
		text10 += text11;
		text10 = Base16(text10);
		text10 += codigoControlCUFD;
		return RemoverCerosDelPrincipio(text10);
	}

	public bool FirmarXML(ref XmlDocument doc, ref string error1, ref DateTime Expiracion, bool desdeCelular)
	{
		bool result;
		try
		{
			string file = "";
			string clave = "";
			if (!new clsFactElectConfiguracion().devolverDatosFirma(ref file, ref clave))
			{
				result = false;
			}
			else if (file.Length == 0)
			{
				result = false;
			}
			else
			{
				string path = ((!desdeCelular) ? Path.Combine(Environment.CurrentDirectory, file) : Path.Combine("C:\\Restotech\\", file));
				bool flag = false;
				if (File.Exists(path))
				{
					try
					{
						X509Certificate2 x509Certificate = new X509Certificate2(File.ReadAllBytes(path), clave, X509KeyStorageFlags.Exportable);
						Expiracion = Conversions.ToDate(x509Certificate.GetExpirationDateString());
						flag = SignXmlFile(ref doc, x509Certificate);
					}
					catch (Exception ex)
					{
						ProjectData.SetProjectError(ex);
						Exception ex2 = ex;
						if (!desdeCelular)
						{
							Interaction.MsgBox("FirmarXML. " + ex2.Message);
						}
						ProjectData.ClearProjectError();
					}
				}
				else if (!desdeCelular)
				{
					Interaction.MsgBox("No existe el archivo de firma Digital: " + file);
				}
				result = flag;
			}
		}
		catch (Exception ex3)
		{
			ProjectData.SetProjectError(ex3);
			Exception ex4 = ex3;
			if (!desdeCelular)
			{
				Interaction.MsgBox(ex4.Message);
			}
			error1 = ex4.Message.ToString();
			result = false;
			ProjectData.ClearProjectError();
		}
		return result;
	}

	public bool SignXmlFile(ref XmlDocument doc, X509Certificate2 cert)
	{
		string xmlString = cert.PrivateKey.ToXmlString(includePrivateParameters: true);
		RSACryptoServiceProvider rSACryptoServiceProvider = new RSACryptoServiceProvider(new CspParameters(24));
		rSACryptoServiceProvider.PersistKeyInCsp = false;
		rSACryptoServiceProvider.FromXmlString(xmlString);
		SignedXml signedXml = new SignedXml(doc);
		signedXml.SigningKey = rSACryptoServiceProvider;
		signedXml.SignedInfo.SignatureMethod = "http://www.w3.org/2001/04/xmldsig-more#rsa-sha256";
		Reference reference = new Reference
		{
			Uri = ""
		};
		reference.AddTransform(new XmlDsigEnvelopedSignatureTransform());
		reference.AddTransform(new XmlDsigC14NWithCommentsTransform());
		reference.DigestMethod = "http://www.w3.org/2001/04/xmlenc#sha256";
		signedXml.AddReference(reference);
		KeyInfo keyInfo = new KeyInfo();
		keyInfo.AddClause(new KeyInfoX509Data(cert));
		signedXml.KeyInfo = keyInfo;
		signedXml.ComputeSignature();
		XmlElement xml = signedXml.GetXml();
		doc.DocumentElement.AppendChild(doc.ImportNode(xml, deep: true));
		return true;
	}

	private bool EscribirXML_Disco(string strXML, string strRutaAbs_CompletaFile)
	{
		try
		{
			using (StreamWriter streamWriter = new StreamWriter(strRutaAbs_CompletaFile, append: false, new UTF8Encoding(encoderShouldEmitUTF8Identifier: false)))
			{
				streamWriter.Write(strXML);
				streamWriter.Close();
			}
			return File.Exists(strRutaAbs_CompletaFile);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			throw ex2;
		}
	}
}
