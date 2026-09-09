using System;
using System.Collections.Generic;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.Drawing.Printing;
using System.Drawing.Text;
using System.IO;
using System.Windows.Forms;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class PrinterClass
{
	public enum TextAlignment : byte
	{
		Default,
		Left,
		Center,
		Right
	}

	public abstract class PrintItem
	{
		public float? CurrentX { get; set; }

		public abstract void Print(PrintPageEventArgs e, ref float currentY);
	}

	public class TextPrintItem : PrintItem
	{
		public string Text { get; set; }

		public float FontSize { get; set; }

		public double SixthMargin { get; set; }

		public bool Bold { get; set; }

		public bool NewLineAfter { get; set; }

		public bool FontUnderline { get; set; }

		public TextPrintItem(string text, float fontSize, double sixthMargin, bool bold, bool newLineAfter = true, bool fontUnderline = false, float? currentX = null)
		{
			Text = text;
			FontSize = fontSize;
			SixthMargin = sixthMargin;
			Bold = bold;
			NewLineAfter = newLineAfter;
			FontUnderline = fontUnderline;
			float? num = currentX;
			if ((num.HasValue ? new bool?(num.GetValueOrDefault() != 0f) : ((bool?)null)) == true)
			{
				base.CurrentX = currentX;
			}
		}

		public override void Print(PrintPageEventArgs e, ref float currentY)
		{
			e.Graphics.CompositingQuality = CompositingQuality.HighQuality;
			e.Graphics.InterpolationMode = InterpolationMode.HighQualityBicubic;
			e.Graphics.SmoothingMode = SmoothingMode.AntiAlias;
			e.Graphics.TextRenderingHint = TextRenderingHint.AntiAliasGridFit;
			FontStyle style = (FontStyle)((Bold ? 1 : 0) | (FontUnderline ? 4 : 0));
			Font font = new Font("Helvetica", FontSize, style);
			_ = (float)((double)e.MarginBounds.Width / 6.0);
			float x = (float)(base.CurrentX.HasValue ? ((double)base.CurrentX.Value) : ((double)e.MarginBounds.Left + (double)e.MarginBounds.Width / 6.0 * (SixthMargin - 1.0)));
			string[] array = Text.Split(new string[3]
			{
				Environment.NewLine,
				"\n",
				"\r"
			}, StringSplitOptions.None);
			int num = 1;
			string[] array2 = array;
			foreach (string s in array2)
			{
				e.Graphics.DrawString(s, font, Brushes.Black, x, currentY);
				if ((NewLineAfter | (array.Length > 1)) & (num < array.Length))
				{
					currentY += font.GetHeight(e.Graphics);
				}
				else if (NewLineAfter)
				{
					currentY += font.GetHeight(e.Graphics);
				}
				num = checked(num + 1);
			}
		}
	}

	public class LinePrintItem : PrintItem
	{
		public float width { get; set; }

		public LinePrintItem(float width)
		{
			this.width = width;
		}

		public override void Print(PrintPageEventArgs e, ref float currentY)
		{
			e.Graphics.CompositingQuality = CompositingQuality.HighQuality;
			e.Graphics.InterpolationMode = InterpolationMode.HighQualityBicubic;
			e.Graphics.SmoothingMode = SmoothingMode.AntiAlias;
			e.Graphics.TextRenderingHint = TextRenderingHint.AntiAliasGridFit;
			Pen pen = new Pen(Color.Black, width);
			float num = (base.CurrentX.HasValue ? base.CurrentX.Value : ((float)e.MarginBounds.Left));
			float x = num + (float)e.MarginBounds.Width;
			e.Graphics.DrawLine(pen, num, currentY, x, currentY);
		}
	}

	public class ImagePrintItem : PrintItem
	{
		public Image Image { get; set; }

		public float? CustomWidth { get; set; }

		public ImagePrintItem(Image image, float? currentX = null, float? customWidth = null)
		{
			Image = image;
			base.CurrentX = currentX;
			CustomWidth = customWidth;
		}

		public override void Print(PrintPageEventArgs e, ref float currentY)
		{
			try
			{
				e.Graphics.CompositingQuality = CompositingQuality.HighQuality;
				e.Graphics.InterpolationMode = InterpolationMode.HighQualityBicubic;
				e.Graphics.SmoothingMode = SmoothingMode.AntiAlias;
				e.Graphics.TextRenderingHint = TextRenderingHint.AntiAliasGridFit;
				float num = (CustomWidth.HasValue ? CustomWidth.Value : ((float)e.MarginBounds.Width));
				float num2 = num / (float)Image.Width;
				float num3 = (float)Image.Height * num2;
				float num4 = e.MarginBounds.Width;
				base.CurrentX = (num4 - num) / 2f + (float)e.MarginBounds.Left;
				float x = (base.CurrentX.HasValue ? base.CurrentX.Value : ((float)e.MarginBounds.Left));
				e.Graphics.DrawImage(Image, x, currentY, num, num3);
				currentY += num3;
			}
			catch (Exception ex)
			{
				ProjectData.SetProjectError(ex);
				Exception ex2 = ex;
				ProjectData.ClearProjectError();
			}
		}
	}

	private readonly PrintDocument printDoc;

	private TextAlignment _Align;

	private readonly string filePath;

	public string Texto;

	public bool Bold;

	public double _FontSize;

	private double _nSixth;

	private bool _fontUnderline;

	private int currentPageIndex;

	private readonly List<PrintItem> printItems;

	public void UnderlineOn()
	{
		_fontUnderline = true;
	}

	public void UnderlineOff()
	{
		_fontUnderline = false;
	}

	public void AlignRight()
	{
		_Align = TextAlignment.Right;
	}

	public void AlignLeft()
	{
		_Align = TextAlignment.Left;
		GotoSixth(1.0);
	}

	public void AlignCenter()
	{
		_Align = TextAlignment.Center;
	}

	public void tinnyFont()
	{
		_FontSize = 6.0;
	}

	public void smallFont()
	{
		_FontSize = 7.5;
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

	public void NormalBigFont()
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

	public void setFont(double Size1)
	{
		_FontSize = Size1;
	}

	public void GotoSixth(double nSixth)
	{
		_nSixth = nSixth;
	}

	public bool IsPrinterInstalled(string printerName)
	{
		foreach (object installedPrinter in PrinterSettings.InstalledPrinters)
		{
			if (string.Equals(Conversions.ToString(installedPrinter), printerName, StringComparison.OrdinalIgnoreCase))
			{
				return true;
			}
		}
		return false;
	}

	public PrinterClass(string AppPath, string PrinterName, ref bool encontro, string nombreDocto, bool formatoPequeno = false)
	{
		_Align = TextAlignment.Default;
		Texto = "";
		Bold = false;
		_FontSize = 10.0;
		_nSixth = 1.0;
		_fontUnderline = false;
		currentPageIndex = 0;
		printItems = new List<PrintItem>();
		filePath = AppPath;
		if (IsPrinterInstalled(PrinterName))
		{
			encontro = true;
			printDoc = new PrintDocument();
			printDoc.PrintPage += OnPrintPage;
			printDoc.PrinterSettings.PrinterName = PrinterName;
			printDoc.DocumentName = nombreDocto;
			int width = 270;
			if (formatoPequeno)
			{
				width = 250;
			}
			int height = 1000;
			printDoc.DefaultPageSettings.PaperSize = new PaperSize("Custom", width, height);
			printDoc.DefaultPageSettings.Landscape = false;
			printDoc.DefaultPageSettings.PaperSize.Height = height;
			printDoc.DefaultPageSettings.PaperSize.Width = width;
			printDoc.DefaultPageSettings.PrinterResolution = new PrinterResolution
			{
				Kind = PrinterResolutionKind.High
			};
			printDoc.DefaultPageSettings.Margins = new Margins(0, 0, 0, 0);
		}
		else
		{
			encontro = false;
		}
	}

	private void OnPrintPage(object sender, PrintPageEventArgs e)
	{
		double num = new Font("Helvetica", 10f).GetHeight(e.Graphics);
		float currentY = e.MarginBounds.Top;
		checked
		{
			while (currentPageIndex < printItems.Count)
			{
				PrintItem printItem = printItems[currentPageIndex];
				if ((double)currentY + num > (double)e.MarginBounds.Bottom)
				{
					e.HasMorePages = true;
					return;
				}
				printItem.Print(e, ref currentY);
				currentPageIndex++;
			}
			e.HasMorePages = false;
			ResetPrinting();
		}
	}

	private void ResetPrinting()
	{
		currentPageIndex = 0;
		printItems.Clear();
	}

	public void WriteLine(string Text)
	{
		int num = 0;
		checked
		{
			switch (_Align)
			{
			case TextAlignment.Default:
				num = 0;
				break;
			case TextAlignment.Left:
				num = 0;
				break;
			case TextAlignment.Center:
			{
				Font font2 = new Font("FontA1x1", (float)_FontSize);
				double num3 = TextRenderer.MeasureText(Text, font2).Width;
				num = (int)Math.Round((float)((270.0 - num3) / 2.0));
				break;
			}
			case TextAlignment.Right:
			{
				Font font = new Font("FontA1x1", (float)_FontSize);
				double num2 = TextRenderer.MeasureText(Text, font).Width;
				num = (int)Math.Round((float)(270.0 - num2));
				break;
			}
			}
			printItems.Add(new TextPrintItem(Text, (float)_FontSize, _nSixth, Bold, newLineAfter: true, _fontUnderline, num));
			if (Operators.CompareString(Text, "", TextCompare: false) == 0)
			{
				GotoSixth(1.0);
			}
			ref string texto = ref Texto;
			texto = texto + Text + "\r\n";
		}
	}

	public void WriteChars(string Text)
	{
		printItems.Add(new TextPrintItem(Text, (float)_FontSize, _nSixth, Bold, newLineAfter: false, _fontUnderline));
		Texto += Text;
	}

	public void DrawLine()
	{
		LinePrintItem item = new LinePrintItem(2f);
		printItems.Add(item);
		Texto += "\r\n___________________________________________\r\n";
	}

	public void Draw2Line()
	{
		LinePrintItem item = new LinePrintItem(2f);
		printItems.Add(item);
		printItems.Add(item);
		Texto += "\r\n===========================================\r\n";
	}

	public void PrintLogo()
	{
		if (File.Exists(filePath + "\\Logo.bmp"))
		{
			Image image = Image.FromFile(filePath + "\\Logo.bmp");
			printItems.Add(new ImagePrintItem(image));
		}
		else if (File.Exists("C:\\Restotech\\Logo.bmp"))
		{
			Image image2 = Image.FromFile("C:\\Restotech\\Logo.bmp");
			printItems.Add(new ImagePrintItem(image2));
		}
	}

	public void CutPaper()
	{
	}

	public void EndDoc()
	{
		printDoc.Print();
		printDoc.Dispose();
	}

	public void PrintQR(string imagePath)
	{
		Image image = Image.FromFile(imagePath);
		float value = (float)((double)printDoc.DefaultPageSettings.PaperSize.Width / 2.0);
		printItems.Add(new ImagePrintItem(image, null, value));
	}

	public void PrintQR(Image image1)
	{
		printItems.Add(new ImagePrintItem(image1));
	}

	public void PrintQRfactura1(Image image1)
	{
		float value = (float)((double)printDoc.DefaultPageSettings.PaperSize.Width / 2.0);
		printItems.Add(new ImagePrintItem(image1, null, value));
	}

	public void FeedPaper(int nlines = 3)
	{
		for (int i = 1; i <= nlines; i = checked(i + 1))
		{
			WriteLine("");
		}
	}

	public void Print()
	{
		printDoc.Print();
	}
}
