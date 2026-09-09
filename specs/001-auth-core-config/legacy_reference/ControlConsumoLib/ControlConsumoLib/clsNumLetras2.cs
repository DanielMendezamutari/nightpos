using System.Runtime.CompilerServices;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class clsNumLetras2
{
	public string Convertir(object nCifra)
	{
		string text = Strings.Format(Conversions.ToDecimal(nCifra), "###############0.#0");
		checked
		{
			string text2 = Strings.Mid(text, Strings.Len(text) - 1, 2);
			text = Strings.Left(text, Strings.Len(text) - 3);
			if (Operators.CompareString(text, "0", TextCompare: false) == 0)
			{
				return "CERO 00/100";
			}
			if (Strings.Len(text) < 3)
			{
				text = Rellenar(text, 3);
			}
			text = Invertir(text);
			byte b = 1;
			byte b2 = 0;
			string text3 = "";
			while (b <= Strings.Len(text))
			{
				string cadena = Strings.Mid(text, b, 3);
				text3 = Convertir(cadena, b2) + " " + text3.Trim();
				b += 3;
				b2++;
			}
			return text3.Trim().ToUpper() + " " + text2 + "/100";
		}
	}

	private string Convertir(string cadena, byte unidadmil)
	{
		cadena = Invertir(cadena);
		if (Strings.Len(cadena) < 3)
		{
			cadena = Rellenar(cadena, 3);
		}
		if (Operators.CompareString(cadena, "000", TextCompare: false) == 0)
		{
			return "";
		}
		byte b = Conversions.ToByte(cadena.Substring(0, 1));
		byte b2 = Conversions.ToByte(cadena.Substring(1, 1));
		byte b3 = Conversions.ToByte(cadena.Substring(2, 1));
		cadena = "";
		if (b != 0)
		{
			cadena = (new string[10]
			{
				"",
				Conversions.ToString(Interaction.IIf((b2 == 0) & (b3 == 0), "cien", "ciento")),
				"doscientos",
				"trescientos",
				"cuatrocientos",
				"quinientos",
				"seiscientos",
				"setecientos",
				"ochocientos",
				"novecientos"
			})[b];
		}
		if (b2 != 0)
		{
			string[] array = new string[10]
			{
				"",
				Conversions.ToString(Interaction.IIf(b3 == 0, "diez", RuntimeHelpers.GetObjectValue(Interaction.IIf(b3 >= 6, "dieci", RuntimeHelpers.GetObjectValue(Interaction.IIf(b3 == 1, "once", RuntimeHelpers.GetObjectValue(Interaction.IIf(b3 == 2, "doce", RuntimeHelpers.GetObjectValue(Interaction.IIf(b3 == 3, "trece", RuntimeHelpers.GetObjectValue(Interaction.IIf(b3 == 4, "catorce", "quince")))))))))))),
				Conversions.ToString(Interaction.IIf(b3 == 0, "veinte", "veinti")),
				"treinta",
				"cuarenta",
				"cincuenta",
				"sesenta",
				"setenta",
				"ochenta",
				"noventa"
			};
			cadena = cadena + " " + array[b2];
		}
		if (!((b2 == 1) & (b3 < 6)))
		{
			string[] array2 = new string[10]
			{
				"",
				Conversions.ToString(Interaction.IIf(b2 != 1, RuntimeHelpers.GetObjectValue(Interaction.IIf(unidadmil == 1, "un", "uno")), "")),
				"dos",
				"tres",
				"cuatro",
				"cinco",
				"seis",
				"siete",
				"ocho",
				"nueve"
			};
			if ((b2 >= 3) & (b3 != 0))
			{
				cadena = cadena.Trim() + " y ";
			}
			if (b2 == 0)
			{
				cadena = cadena.Trim() + " ";
			}
			cadena += array2[b3];
		}
		if (unidadmil != 0)
		{
			string[] array3 = new string[6]
			{
				"",
				"mil",
				Conversions.ToString(Interaction.IIf((b == 0) & (b2 == 0) & (b3 == 1), "millón", "millones")),
				"mil millones",
				"billones",
				"mil billones"
			};
			if ((b == 0) & (b2 == 0) & (b3 == 1) & (unidadmil == 2))
			{
				cadena = "un";
			}
			cadena = cadena + " " + array3[unidadmil];
		}
		return cadena.Trim();
	}

	public string Invertir(string cadena)
	{
		string text = "";
		checked
		{
			for (short num = (short)cadena.Length; num >= 1; num = (short)unchecked(num + -1))
			{
				text += cadena.Substring(num - 1, 1);
			}
			return text;
		}
	}

	public string Rellenar(object valor, byte cifras)
	{
		valor = (Versioned.IsNumeric(RuntimeHelpers.GetObjectValue(valor)) ? ((object)Conversions.ToInteger(valor)) : ((object)0));
		string text = valor.ToString().Trim();
		checked
		{
			byte num = (byte)(Strings.Len(text) + 1);
			byte b = cifras;
			byte b2 = num;
			while (unchecked((uint)b2 <= (uint)b))
			{
				text = "0" + text;
				b2 = (byte)unchecked((uint)(b2 + 1));
			}
			return text;
		}
	}
}
