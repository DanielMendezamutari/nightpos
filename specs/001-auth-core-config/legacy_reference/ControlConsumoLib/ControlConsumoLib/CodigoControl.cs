using System;
using System.Text;
using System.Windows.Forms;
using Microsoft.VisualBasic;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class CodigoControl
{
	private readonly int[,] d;

	private readonly int[,] p;

	private readonly int[] inv;

	public CodigoControl()
	{
		d = new int[10, 10]
		{
			{ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 },
			{ 1, 2, 3, 4, 0, 6, 7, 8, 9, 5 },
			{ 2, 3, 4, 0, 1, 7, 8, 9, 5, 6 },
			{ 3, 4, 0, 1, 2, 8, 9, 5, 6, 7 },
			{ 4, 0, 1, 2, 3, 9, 5, 6, 7, 8 },
			{ 5, 9, 8, 7, 6, 0, 4, 3, 2, 1 },
			{ 6, 5, 9, 8, 7, 1, 0, 4, 3, 2 },
			{ 7, 6, 5, 9, 8, 2, 1, 0, 4, 3 },
			{ 8, 7, 6, 5, 9, 3, 2, 1, 0, 4 },
			{ 9, 8, 7, 6, 5, 4, 3, 2, 1, 0 }
		};
		p = new int[8, 10]
		{
			{ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 },
			{ 1, 5, 7, 6, 2, 8, 3, 0, 9, 4 },
			{ 5, 8, 0, 3, 7, 9, 6, 1, 4, 2 },
			{ 8, 9, 1, 6, 0, 4, 3, 5, 2, 7 },
			{ 9, 4, 5, 3, 1, 2, 6, 8, 7, 0 },
			{ 4, 2, 8, 6, 5, 7, 3, 9, 0, 1 },
			{ 2, 7, 9, 3, 8, 0, 6, 4, 1, 5 },
			{ 7, 0, 4, 6, 9, 1, 3, 2, 5, 8 }
		};
		inv = new int[10] { 0, 4, 3, 2, 1, 5, 6, 7, 8, 9 };
	}

	public string generar1(string autorizacion, string numero, string nitci, string fecha, string monto, string llave)
	{
		numero = verhoeff_add_recursive(numero, 2);
		nitci = verhoeff_add_recursive(nitci, 2);
		fecha = verhoeff_add_recursive(fecha, 2);
		monto = verhoeff_add_recursive(monto, 2);
		checked
		{
			string number = (long.Parse(numero) + long.Parse(nitci) + long.Parse(fecha) + long.Parse(monto)).ToString();
			number = verhoeff_add_recursive(number, 5);
			string text = number.Substring(number.Length - 5, 5) ?? "";
			int[] array = new int[5];
			string[] array2 = new string[5] { "", "", "", "", "" };
			int num = 0;
			int num2 = 0;
			char[] array3 = text.ToCharArray();
			for (int i = 0; i < array3.Length; i++)
			{
				char c = array3[i];
				array[num2] = int.Parse(c.ToString()) + 1;
				array2[num2] = llave.Substring(num, int.Parse(c.ToString()) + 1);
				num += int.Parse(c.ToString()) + 1;
				num2++;
			}
			autorizacion += array2[0];
			numero += array2[1];
			nitci += array2[2];
			fecha += array2[3];
			monto += array2[4];
			string text2 = RC4(autorizacion + numero + nitci + fecha + monto, llave + text, guion: false);
			long num3 = 0L;
			long[] array4 = new long[5];
			int num4 = text2.Length - 1;
			for (int j = 0; j <= num4; j++)
			{
				num2 = Strings.Asc(text2[j]);
				array4[unchecked(j % 5)] += num2;
				num3 += num2;
			}
			long num5 = 0L;
			int num6 = array4.Length - 1;
			for (int k = 0; k <= num6; k++)
			{
				num5 = (long)Math.Round((double)num5 + Math.Truncate((double)(num3 * array4[k]) / (double)array[k]));
			}
			string expression = BASE64(Conversions.ToString(num5));
			string text3 = RC4(expression, llave + text, guion: false).Insert(2, "-").Insert(5, "-").Insert(8, "-");
			if (text3.Length > 11)
			{
				text3 = text3.Insert(11, "-");
			}
			return text3;
		}
	}

	public string BASE64(string sNum)
	{
		string text = "";
		string text2 = "";
		string Resto = "";
		string text3 = "";
		object instance = new object[64]
		{
			"0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
			"A", "B", "C", "D", "E", "F", "G", "H", "I", "J",
			"K", "L", "M", "N", "O", "P", "Q", "R", "S", "T",
			"U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d",
			"e", "f", "g", "h", "i", "j", "k", "l", "m", "n",
			"o", "p", "q", "r", "s", "t", "u", "v", "w", "x",
			"y", "z", "+", "/"
		};
		while (Operators.CompareString(sNum, "0", TextCompare: false) != 0)
		{
			text3 = IntDivide(sNum, "64", ref Resto);
			short num = Conversions.ToShort(Resto);
			text = Conversions.ToString(Operators.ConcatenateObject(text, NewLateBinding.LateIndexGet(instance, new object[1] { num }, null)));
			sNum = text3;
		}
		checked
		{
			for (short num2 = Conversions.ToShort(sNum); num2 > 0; num2 = (short)Math.Round((double)num2 / 64.0))
			{
				short num = (short)unchecked(num2 % 64);
				text = Conversions.ToString(Operators.ConcatenateObject(text, NewLateBinding.LateIndexGet(instance, new object[1] { num }, null)));
			}
			short num3 = (short)Strings.Len(text);
			for (short num4 = 1; num4 <= num3; num4 = (short)unchecked(num4 + 1))
			{
				text2 += Strings.Mid(text, Strings.Len(text) - num4 + 1, 1);
			}
			return text2;
		}
	}

	public string IntDivide(string FirstNum, string SecondNum, ref string Resto)
	{
		if ((Strings.Len(FirstNum) < Strings.Len(SecondNum)) | (Strings.InStr(1, IntSubtract(FirstNum, SecondNum), "-") > 0))
		{
			Resto = FirstNum;
			return "0";
		}
		if (Operators.CompareString(TrimZeros(SecondNum), "", TextCompare: false) == 0)
		{
			Interaction.MsgBox("Fault: Cannot divide by Zero.");
			return "NaN";
		}
		checked
		{
			if (Operators.CompareString(TrimZeros(SecondNum), "", TextCompare: false) != 0)
			{
				string text = FirstNum;
				string text2 = "0";
				int num = Strings.Len(text) - Strings.Len(SecondNum);
				string text3 = new string('0', num);
				string text4 = SecondNum + text3;
				while (!((Strings.Len(text) < Strings.Len(SecondNum)) | (Operators.CompareString(text, "0", TextCompare: false) == 0) | ((Strings.InStr(1, IntSubtract(text, SecondNum), "-") > 0) & (Operators.CompareString(text3, "", TextCompare: false) == 0))))
				{
					if (num >= 0)
					{
						text3 = new string('0', num);
					}
					text4 = SecondNum + text3;
					if (Strings.InStr(1, IntSubtract(text, text4), "-") > 0)
					{
						if (num <= 0)
						{
							break;
						}
						text3 = new string('0', num - 1);
						text4 = SecondNum + text3;
					}
					text2 = IntAddition(text2, "1" + text3);
					text = IntSubtract(text, text4);
					num = Strings.Len(text) - Strings.Len(SecondNum);
					Application.DoEvents();
				}
				string result = text2;
				Resto = text;
				return result;
			}
			return "NaN";
		}
	}

	public string IntAddition(string FirstNum, string SecondNum)
	{
		string text = "";
		string text2;
		string text3;
		if (Strings.Len(FirstNum) >= Strings.Len(SecondNum))
		{
			text2 = FirstNum;
			text3 = SecondNum;
		}
		else
		{
			text3 = FirstNum;
			text2 = SecondNum;
		}
		short num2;
		int num3;
		checked
		{
			int num = Strings.Len(text2) - Strings.Len(text3);
			num2 = 0;
			num3 = num;
			for (int i = Strings.Len(text3); i >= 1; i += -1)
			{
				short num4 = (short)Conversion.Int(Conversions.ToDouble(Strings.Mid(text2, i + num, 1)));
				short num5 = (short)Conversion.Int(Conversions.ToDouble(Strings.Mid(text3, i, 1)));
				short num6 = (short)unchecked(checked((short)unchecked(num4 + num5)) + num2);
				num2 = (short)unchecked(num6 / 10);
				text = Conversions.ToString(num6 - num2 * 10) + text;
				Application.DoEvents();
			}
			while (!((num2 == 0) | (num3 == 0)))
			{
				short num7 = (short)Math.Round(Conversion.Int(Conversions.ToDouble(Strings.Mid(text2, num3, 1))) + (double)num2);
				num2 = (short)unchecked(num7 / 10);
				text = Conversions.ToString(num7 - num2 * 10) + text;
				num3--;
			}
		}
		if (num2 > 0)
		{
			text = Conversions.ToString((int)num2) + text;
		}
		if (num3 > 0)
		{
			text = Strings.Left(text2, num3) + text;
		}
		return TrimZeros(text);
	}

	public string IntSubtract(string FirstNum, string SecondNum)
	{
		string text = "";
		checked
		{
			string text2;
			bool flag;
			string text3;
			if (Strings.Len(FirstNum) > Strings.Len(SecondNum))
			{
				text2 = FirstNum;
				text3 = SecondNum;
				flag = false;
			}
			else if (Strings.Len(FirstNum) < Strings.Len(SecondNum))
			{
				text2 = SecondNum;
				text3 = FirstNum;
				flag = true;
			}
			else
			{
				int num = Strings.Len(FirstNum);
				int num2 = 1;
				while (true)
				{
					if (num2 <= num)
					{
						if (Conversion.Int(Conversions.ToDouble(Strings.Mid(FirstNum, num2, 1))) > Conversion.Int(Conversions.ToDouble(Strings.Mid(SecondNum, num2, 1))))
						{
							text2 = FirstNum;
							text3 = SecondNum;
							flag = false;
							break;
						}
						if (Conversion.Int(Conversions.ToDouble(Strings.Mid(FirstNum, num2, 1))) < Conversion.Int(Conversions.ToDouble(Strings.Mid(SecondNum, num2, 1))))
						{
							text2 = SecondNum;
							text3 = FirstNum;
							flag = true;
							break;
						}
						Application.DoEvents();
						num2++;
						continue;
					}
					return Conversions.ToString(0);
				}
			}
			int count = Strings.Len(text2) - Strings.Len(text3);
			text3 = new string('0', count) + text3;
			byte b = 0;
			short num5 = default(short);
			for (int num2 = Strings.Len(text3); num2 >= 1; num2 += -1)
			{
				short num3 = (short)Math.Round(Conversion.Int(Conversions.ToDouble(Strings.Mid(text2, num2, 1))) - (double)unchecked((int)b));
				short num4 = (short)Conversion.Int(Conversions.ToDouble(Strings.Mid(text3, num2, 1)));
				b = 0;
				if (num3 >= num4)
				{
					num5 = (short)unchecked(num3 - num4);
				}
				else if (num3 < num4)
				{
					num5 = (short)(num3 + 10 - num4);
					b = 1;
				}
				text = Conversions.ToString(unchecked((int)num5)) + text;
				Application.DoEvents();
			}
			if (flag)
			{
				return "-" + TrimZeros(Strings.Trim(text));
			}
			return TrimZeros(Strings.Trim(text));
		}
	}

	public string TrimZeros(string num)
	{
		int num2 = Strings.Len(num);
		int num3 = 1;
		checked
		{
			while (true)
			{
				if (num3 <= num2)
				{
					if (Conversions.ToDouble(Strings.Mid(num, num3, 1)) != 0.0)
					{
						break;
					}
					num3++;
					continue;
				}
				return "0";
			}
			return Strings.Mid(num, num3, Strings.Len(num) - num3 + 1);
		}
	}

	public string RC4(string Expression, string Password, bool guion = true)
	{
		int try0000_dispatch = -1;
		checked
		{
			int num3 = default(int);
			int num = default(int);
			int num2 = default(int);
			int num5 = default(int);
			short[] array = default(short[]);
			string text = default(string);
			byte[] bytes = default(byte[]);
			int num6 = default(int);
			int num7 = default(int);
			byte b = default(byte);
			byte[] bytes2 = default(byte[]);
			int num8 = default(int);
			string result = default(string);
			while (true)
			{
				try
				{
					/*Note: ILSpy has introduced the following switch to emulate a goto from catch-block to try-block*/;
					switch (try0000_dispatch)
					{
					default:
						ProjectData.ClearProjectError();
						num3 = 1;
						goto IL_0007;
					case 714:
						{
							num = num2;
							switch (num3)
							{
							case 1:
								break;
							default:
								goto end_IL_0000;
							}
							int num4 = unchecked(num + 1);
							num = 0;
							switch (num4)
							{
							case 1:
								break;
							case 2:
								goto IL_0007;
							case 3:
								goto IL_0015;
							case 4:
								goto IL_001e;
							case 6:
								goto IL_002b;
							case 8:
								goto IL_0038;
							case 9:
								goto IL_0047;
							case 11:
								goto IL_0068;
							case 10:
							case 12:
								goto IL_007d;
							case 13:
								goto IL_0083;
							case 14:
								goto IL_008e;
							case 15:
								goto IL_00a0;
							case 16:
								goto IL_00a6;
							case 17:
								goto IL_00ac;
							case 18:
								goto IL_00b2;
							case 19:
								goto IL_00b8;
							case 20:
								goto IL_00d8;
							case 21:
								goto IL_00e3;
							case 22:
								goto IL_00f0;
							case 23:
								goto IL_00fa;
							case 24:
								goto IL_010c;
							case 25:
								goto IL_0112;
							case 26:
								goto IL_0118;
							case 27:
								goto IL_011e;
							case 28:
								goto IL_0133;
							case 29:
								goto IL_0148;
							case 30:
								goto IL_0157;
							case 31:
								goto IL_016a;
							case 32:
								goto IL_0175;
							case 33:
								goto IL_0182;
							case 34:
								goto IL_018c;
							case 35:
								goto IL_01b0;
							case 36:
								goto IL_01d9;
							case 37:
								goto IL_01ec;
							case 38:
								goto IL_01fd;
							case 39:
								goto end_IL_0000_2;
							default:
								goto end_IL_0000;
							case 5:
							case 7:
							case 40:
								goto end_IL_0000_3;
							}
							goto default;
						}
						IL_01fd:
						num2 = 38;
						num5++;
						goto IL_0206;
						IL_0007:
						num2 = 2;
						array = new short[256];
						goto IL_0015;
						IL_0015:
						num2 = 3;
						text = "";
						goto IL_001e;
						IL_001e:
						num2 = 4;
						if (Strings.Len(Password) == 0)
						{
							goto end_IL_0000_3;
						}
						goto IL_002b;
						IL_002b:
						num2 = 6;
						if (Strings.Len(Expression) == 0)
						{
							goto end_IL_0000_3;
						}
						goto IL_0038;
						IL_0038:
						num2 = 8;
						if (Strings.Len(Password) > 256)
						{
							goto IL_0047;
						}
						goto IL_0068;
						IL_0047:
						num2 = 9;
						bytes = Encoding.GetEncoding(1252).GetBytes(Strings.Left(Password, 256));
						goto IL_007d;
						IL_0068:
						num2 = 11;
						bytes = Encoding.GetEncoding(1252).GetBytes(Password);
						goto IL_007d;
						IL_007d:
						num2 = 12;
						num5 = 0;
						goto IL_0083;
						IL_0083:
						num2 = 13;
						array[num5] = (short)num5;
						goto IL_008e;
						IL_008e:
						num2 = 14;
						num5++;
						if (num5 <= 255)
						{
							goto IL_0083;
						}
						goto IL_00a0;
						IL_00a0:
						num2 = 15;
						num5 = 0;
						goto IL_00a6;
						IL_00a6:
						num2 = 16;
						num6 = 0;
						goto IL_00ac;
						IL_00ac:
						num2 = 17;
						num7 = 0;
						goto IL_00b2;
						IL_00b2:
						num2 = 18;
						num5 = 0;
						goto IL_00b8;
						IL_00b8:
						num2 = 19;
						num6 = unchecked(checked(num6 + array[num5] + bytes[unchecked(num5 % Strings.Len(Password))]) % 256);
						goto IL_00d8;
						IL_00d8:
						num2 = 20;
						b = (byte)array[num5];
						goto IL_00e3;
						IL_00e3:
						num2 = 21;
						array[num5] = array[num6];
						goto IL_00f0;
						IL_00f0:
						num2 = 22;
						array[num6] = b;
						goto IL_00fa;
						IL_00fa:
						num2 = 23;
						num5++;
						if (num5 <= 255)
						{
							goto IL_00b8;
						}
						goto IL_010c;
						IL_010c:
						num2 = 24;
						num5 = 0;
						goto IL_0112;
						IL_0112:
						num2 = 25;
						num6 = 0;
						goto IL_0118;
						IL_0118:
						num2 = 26;
						num7 = 0;
						goto IL_011e;
						IL_011e:
						num2 = 27;
						bytes2 = Encoding.GetEncoding(1252).GetBytes(Expression);
						goto IL_0133;
						IL_0133:
						num2 = 28;
						num8 = Strings.Len(Expression) - 1;
						num5 = 0;
						goto IL_0206;
						IL_0206:
						if (num5 > num8)
						{
							break;
						}
						goto IL_0148;
						IL_0148:
						num2 = 29;
						num6 = unchecked(checked(num6 + 1) % 256);
						goto IL_0157;
						IL_0157:
						num2 = 30;
						num7 = unchecked(checked(num7 + array[num6]) % 256);
						goto IL_016a;
						IL_016a:
						num2 = 31;
						b = (byte)array[num6];
						goto IL_0175;
						IL_0175:
						num2 = 32;
						array[num6] = array[num7];
						goto IL_0182;
						IL_0182:
						num2 = 33;
						array[num7] = b;
						goto IL_018c;
						IL_018c:
						num2 = 34;
						bytes2[num5] = (byte)(bytes2[num5] ^ array[unchecked(checked((short)unchecked(array[num6] + array[num7])) % 256)]);
						goto IL_01b0;
						IL_01b0:
						num2 = 35;
						text += Strings.Right(new string('0', 2) + Conversion.Hex(bytes2[num5]), 2);
						goto IL_01d9;
						IL_01d9:
						num2 = 36;
						if (guion & (num5 < Strings.Len(Expression) - 1))
						{
							goto IL_01ec;
						}
						goto IL_01fd;
						IL_01ec:
						num2 = 37;
						text += "-";
						goto IL_01fd;
						end_IL_0000_2:
						break;
					}
					num2 = 39;
					result = text;
					break;
					end_IL_0000:;
				}
				catch (object obj) when ((obj is Exception) & (num3 != 0) & (num == 0))
				{
					ProjectData.SetProjectError((Exception)obj);
					try0000_dispatch = 714;
					continue;
				}
				throw ProjectData.CreateProjectError(-2146828237);
				continue;
				end_IL_0000_3:
				break;
			}
			if (num != 0)
			{
				ProjectData.ClearProjectError();
			}
			return result;
		}
	}

	public bool validateVerhoeff(string num)
	{
		int num2 = 0;
		int[] array = StringToReversedIntArray(num);
		int num3 = checked(array.Length - 1);
		for (int i = 0; i <= num3; i = checked(i + 1))
		{
			num2 = d[num2, p[i % 8, array[i]]];
		}
		return num2.Equals(0);
	}

	private string verhoeff_add_recursive(string number, int digits)
	{
		string text = number;
		while (digits > 0)
		{
			text += generateVerhoeff(text);
			digits = checked(digits - 1);
		}
		return text;
	}

	public string generateVerhoeff(string num)
	{
		int num2 = 0;
		int[] array = StringToReversedIntArray(num);
		int num3 = checked(array.Length - 1);
		for (int i = 0; i <= num3; i = checked(i + 1))
		{
			num2 = d[num2, p[checked(i + 1) % 8, array[i]]];
		}
		return inv[num2].ToString();
	}

	private int[] StringToReversedIntArray(string str)
	{
		checked
		{
			int[] array = new int[str.Length - 1 + 1];
			int num = str.Length - 1;
			for (int i = 0; i <= num; i++)
			{
				array[i] = Convert.ToInt16(str.Substring(i, 1));
			}
			Array.Reverse(array);
			return array;
		}
	}
}
