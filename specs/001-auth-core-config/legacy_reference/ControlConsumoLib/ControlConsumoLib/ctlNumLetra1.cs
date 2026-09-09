using System;
using System.Text.RegularExpressions;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class ctlNumLetra1
{
	private readonly string[] UNIDADES;

	private readonly string[] DECENAS;

	private readonly string[] CENTENAS;

	private Regex r;

	public ctlNumLetra1()
	{
		UNIDADES = new string[10] { "", "un ", "dos ", "tres ", "cuatro ", "cinco ", "seis ", "siete ", "ocho ", "nueve " };
		DECENAS = new string[18]
		{
			"diez ", "once ", "doce ", "trece ", "catorce ", "quince ", "dieciseis ", "diecisiete ", "dieciocho ", "diecinueve",
			"veinte ", "treinta ", "cuarenta ", "cincuenta ", "sesenta ", "setenta ", "ochenta ", "noventa "
		};
		CENTENAS = new string[10] { "", "ciento ", "doscientos ", "trecientos ", "cuatrocientos ", "quinientos ", "seiscientos ", "setecientos ", "ochocientos ", "novecientos " };
	}

	public void NumLetra()
	{
	}

	public string Convertir(string numero, bool mayusculas)
	{
		string text = "";
		string text2 = "";
		numero = Strings.Replace(numero, ".", ",");
		if ((numero.IndexOf(",") == -1) | (numero.IndexOf(".") == -1))
		{
			numero += ",00";
		}
		r = new Regex("\\d{1,9},\\d{1,2}");
		if (r.Matches(numero).Count > 0)
		{
			string[] array = numero.Split(',');
			text2 = ((array[1].Length != 1) ? (array[1] + "/100 ") : (array[1] + "0/100 "));
			text = ((Conversions.ToDouble(array[0]) == 0.0) ? "cero " : ((Conversions.ToDouble(array[0]) > 999999.0) ? getMillones(array[0]) : ((Conversions.ToDouble(array[0]) > 999.0) ? getMiles(array[0]) : ((Conversions.ToDouble(array[0]) > 99.0) ? getCentenas(array[0]) : ((!(Conversions.ToDouble(array[0]) > 9.0)) ? getUnidades(array[0]) : getDecenas(array[0]))))));
			if (mayusculas)
			{
				return (text + " " + text2).ToUpper();
			}
			return text + " " + text2;
		}
		return "";
	}

	private string getUnidades(string numero)
	{
		string value = numero.Substring(checked(numero.Length - 1));
		return UNIDADES[Conversions.ToInteger(value)];
	}

	private string getDecenas(string numero)
	{
		if (Conversions.ToDouble(numero) < 10.0)
		{
			return getUnidades(numero);
		}
		checked
		{
			if (Conversions.ToDouble(numero) > 19.0)
			{
				string unidades = getUnidades(numero);
				if (unidades.Equals(""))
				{
					return DECENAS[(int)Math.Round(Conversions.ToDouble(numero.Substring(0, 1)) + 8.0)];
				}
				return DECENAS[(int)Math.Round(Conversions.ToDouble(numero.Substring(0, 1)) + 8.0)] + "y " + unidades;
			}
			return DECENAS[(int)Math.Round(Conversions.ToDouble(numero) - 10.0)];
		}
	}

	private string getCentenas(string numero)
	{
		if (Conversions.ToDouble(numero) > 99.0)
		{
			if (Conversions.ToDouble(numero) == 100.0)
			{
				return "cien ";
			}
			return CENTENAS[Conversions.ToInteger(numero.Substring(0, 1))] + getDecenas(numero.Substring(1));
		}
		int num = Conversions.ToInteger(numero);
		return getDecenas(Conversions.ToString(num));
	}

	private string getMiles(string numero)
	{
		checked
		{
			string numero2 = numero.Substring(numero.Length - 3);
			string text = numero.Substring(0, numero.Length - 3);
			if (Conversions.ToDouble(text) > 0.0)
			{
				return getCentenas(text) + " mil " + getCentenas(numero2);
			}
			return getCentenas(numero2) ?? "";
		}
	}

	private string getMillones(string numero)
	{
		checked
		{
			string numero2 = numero.Substring(numero.Length - 6);
			string text = numero.Substring(0, numero.Length - 6);
			string text2 = "";
			text2 = ((!(Conversions.ToDouble(text) > 9.0)) ? (getUnidades(text) + " millon ") : (getCentenas(text) + " millones "));
			return text2 + getMiles(numero2);
		}
	}
}
