using System;
using System.Drawing;
using System.Drawing.Imaging;
using System.IO;
using Microsoft.VisualBasic.CompilerServices;

namespace ControlConsumoLib;

public class TrabajarConImagenes
{
	public static byte[] Image2Bytes(Image img)
	{
		string tempFileName = Path.GetTempFileName();
		FileStream fileStream = new FileStream(tempFileName, FileMode.OpenOrCreate, FileAccess.ReadWrite);
		img.Save(fileStream, ImageFormat.Png);
		fileStream.Position = 0L;
		checked
		{
			int num = (int)fileStream.Length;
			byte[] array = new byte[num - 1 + 1];
			fileStream.Read(array, 0, num);
			fileStream.Close();
			return array;
		}
	}

	public static Image Bytes2Image(byte[] bytes)
	{
		if (bytes == null)
		{
			return null;
		}
		MemoryStream stream = new MemoryStream(bytes);
		Bitmap result = null;
		try
		{
			result = new Bitmap(stream);
		}
		catch (Exception ex)
		{
			ProjectData.SetProjectError(ex);
			Exception ex2 = ex;
			ProjectData.ClearProjectError();
		}
		return result;
	}
}
