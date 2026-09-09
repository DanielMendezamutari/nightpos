using System;
using System.Collections;
using System.Drawing;
using System.Drawing.Printing;
using ConfigToptech;
using Microsoft.VisualBasic.CompilerServices;
using Microsoft.VisualBasic.PowerPacks.Printing.Compatibility.VB6;

namespace ControlConsumoLib;

public class PrinterClassOLD
{
	public enum TextAlignment : byte
	{
		Default,
		Left,
		Center,
		Right
	}

	private Printer p;

	private string _Path;

	private TextAlignment _Align;

	private bool bIsDebug;

	public string Texto;

	public string Path
	{
		get
		{
			return _Path;
		}
		set
		{
			_Path = value;
		}
	}

	public TextAlignment Alignment
	{
		get
		{
			return _Align;
		}
		set
		{
			_Align = value;
		}
	}

	public string FontName
	{
		get
		{
			return p.FontName;
		}
		set
		{
			p.FontName = value;
		}
	}

	public double _FontSize
	{
		get
		{
			return p.FontSize;
		}
		set
		{
			p.FontSize = (float)value;
		}
	}

	public bool Bold
	{
		get
		{
			return p.FontBold;
		}
		set
		{
			p.FontBold = value;
		}
	}

	public bool RTL
	{
		get
		{
			return p.RightToLeft;
		}
		set
		{
			p.RightToLeft = value;
		}
	}

	public PrinterClassOLD(string AppPath, ref string nombre, ref bool encontro, string nombreDocto, bool formatoPequeno = false)
	{
		_Align = TextAlignment.Default;
		bIsDebug = true;
		Texto = "";
		Texto = "";
		if (SetPrinterName(ref nombre, AppPath, nombreDocto))
		{
			if (formatoPequeno)
			{
				if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Aerocruz)
				{
					p.Width = 3500;
				}
				else
				{
					p.Width = 3000;
				}
			}
			else if (configuration.gStyleBoliches1 == configuration.styleBolichesId.Tapekua)
			{
				p.Width = 3900;
			}
			else
			{
				p.Width = 3800;
			}
			p.PrintQuality = -4;
			encontro = true;
		}
		else
		{
			encontro = false;
		}
	}

	public PrinterClassOLD(string nombreFile)
	{
		_Align = TextAlignment.Default;
		bIsDebug = true;
		Texto = "";
		p = new Printer();
		p.Width = 3900;
		p.PrintQuality = -4;
		p.PrintAction = PrintAction.PrintToFile;
		p.PrintFileName = nombreFile;
	}

	private bool SetPrinterName(ref string PrinterName, string AppPath, string nombreDocto)
	{
		bool flag = false;
		if (!flag)
		{
			foreach (Printer item in (IEnumerable)GlobalModule.Printers)
			{
				if (Operators.CompareString(item.DeviceName.ToLower(), PrinterName.ToLower(), TextCompare: false) == 0)
				{
					p = item;
					flag = true;
					break;
				}
			}
		}
		if (!flag)
		{
			return false;
		}
		p.DocumentName = nombreDocto.Replace(" ", "");
		Path = AppPath;
		return true;
	}

	public void PrintLogo()
	{
		try
		{
			PrintImage(_Path + "\\Logo.bmp");
			p.CurrentY += 0f;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
	}

	private void PrintImage(string FileName)
	{
		try
		{
			Image image = Image.FromFile(FileName);
			if (configuration.gStyleBoliches1 == configuration.styleBolichesId.PedroDelBrete)
			{
				p.PaintPicture(image, p.CurrentX, p.CurrentY, 18000f, 7000f);
			}
			else
			{
				p.PaintPicture(image, p.CurrentX + 200f, p.CurrentY);
			}
			p.CurrentY += (float)image.Height;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
	}

	public void AlignLeft()
	{
		_Align = TextAlignment.Left;
	}

	public void AlignCenter()
	{
		_Align = TextAlignment.Center;
	}

	public void AlignRight()
	{
		_Align = TextAlignment.Right;
	}

	public void DrawLine()
	{
		p.DrawWidth = 2;
		p.Line(p.Width, p.CurrentY);
		p.CurrentY += 20f;
		Texto += "\r\n___________________________________________\r\n";
	}

	public void Draw2Line()
	{
		p.DrawWidth = 10;
		p.Line(p.Width, p.CurrentY);
		p.CurrentY += 60f;
		Texto += "\r\n===========================================\r\n";
	}

	public void tinnyFont()
	{
		_FontSize = 6.0;
	}

	public void smallFont()
	{
		_FontSize = 7.5;
	}

	public void setFont(double Size)
	{
		_FontSize = Size;
	}

	public void smallFont1()
	{
		_FontSize = 8.0;
	}

	public void smallFont2()
	{
		_FontSize = 8.5;
	}

	public void NormalFont()
	{
		_FontSize = 9.0;
	}

	public void NormalFont10()
	{
		_FontSize = 10.0;
	}

	public void NormalPlusFont()
	{
		_FontSize = 11.0;
	}

	public void NormalBiggerFont()
	{
		_FontSize = 12.5;
	}

	public void BigSmallerFont()
	{
		_FontSize = 14.0;
	}

	public void BigFont()
	{
		_FontSize = 15.0;
	}

	public void MaxFont()
	{
		_FontSize = 16.0;
	}

	public void setFont(double FontSize = 9.5, string FontName = "FontA1x1", bool BoldType = false)
	{
		_FontSize = FontSize;
		this.FontName = FontName;
		Bold = BoldType;
	}

	public void NewPage()
	{
		p.NewPage();
	}

	public void FeedPaper(int nlines = 3)
	{
		for (int i = 1; i <= nlines; i = checked(i + 1))
		{
			WriteLine("");
		}
	}

	public double TextWidth(string text)
	{
		return p.TextWidth(text);
	}

	public void PrintQRfactura(Image pic)
	{
		try
		{
			p.PaintPicture(pic, 1200f, p.CurrentY);
			p.CurrentY += (float)pic.Height;
			p.CurrentY += 1300f;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
	}

	public void PrintQRBanco(Image pic)
	{
		try
		{
			p.PrintQuality = -4;
			p.PaintPicture(pic, 300f, p.CurrentY);
			p.CurrentY += (float)pic.Height;
			p.CurrentY += 1700f;
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
	}

	public void GotoCol(int ColNumber = 0)
	{
		double num = (double)p.Width / 48.0;
		p.CurrentX = (float)(num * (double)ColNumber);
	}

	public void GotoSixth(double nSixth = 1.0)
	{
		double num = (double)p.Width / 6.0;
		p.CurrentX = (float)(num * (nSixth - 1.0));
	}

	public int getWidth()
	{
		return p.Width;
	}

	public void UnderlineOn()
	{
		p.FontUnderline = true;
	}

	public void UnderlineOff()
	{
		p.FontUnderline = false;
	}

	public void EndDoc()
	{
		p.EndDoc();
	}

	public void EndDoc(short copies)
	{
		p.Copies = copies;
		p.EndDoc();
	}

	public void WriteLine(string Text)
	{
		double num = p.TextWidth(Text);
		switch (_Align)
		{
		case TextAlignment.Left:
			p.CurrentX = 0f;
			break;
		case TextAlignment.Center:
			p.CurrentX = (float)(((double)p.Width - num) / 2.0);
			break;
		case TextAlignment.Right:
			p.CurrentX = (float)((double)p.Width - num);
			break;
		}
		p.Print(Text);
		ref string texto = ref Texto;
		texto = texto + Text + "\r\n";
	}

	public void WriteChars(string Text)
	{
		p.Write(Text);
		Texto += Text;
	}

	public void CutPaper()
	{
		p.NewPage();
	}
}
