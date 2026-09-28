param(
    [Parameter(Mandatory=$true)][string]$PrinterName,
    [Parameter(Mandatory=$true)][string]$FilePath
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path -LiteralPath $FilePath)) {
    Write-Error "File not found: $FilePath"
    exit 1
}

$bytes = [System.IO.File]::ReadAllBytes($FilePath)

$source = @'
using System;
using System.Runtime.InteropServices;

public class Win32RawPrinter
{
    [StructLayout(LayoutKind.Sequential, CharSet = CharSet.Ansi)]
    public class DOCINFOA
    {
        [MarshalAs(UnmanagedType.LPStr)] public string pDocName;
        [MarshalAs(UnmanagedType.LPStr)] public string pOutputFile;
        [MarshalAs(UnmanagedType.LPStr)] public string pDataType;
    }

    [DllImport("winspool.drv", EntryPoint = "OpenPrinterA", SetLastError = true, CharSet = CharSet.Ansi)]
    public static extern bool OpenPrinter(string szPrinter, out IntPtr hPrinter, IntPtr pd);

    [DllImport("winspool.drv", EntryPoint = "ClosePrinter", SetLastError = true)]
    public static extern bool ClosePrinter(IntPtr hPrinter);

    [DllImport("winspool.drv", EntryPoint = "StartDocPrinterA", SetLastError = true, CharSet = CharSet.Ansi)]
    public static extern bool StartDocPrinter(IntPtr hPrinter, int level, [In, MarshalAs(UnmanagedType.LPStruct)] DOCINFOA di);

    [DllImport("winspool.drv", EntryPoint = "EndDocPrinter", SetLastError = true)]
    public static extern bool EndDocPrinter(IntPtr hPrinter);

    [DllImport("winspool.drv", EntryPoint = "StartPagePrinter", SetLastError = true)]
    public static extern bool StartPagePrinter(IntPtr hPrinter);

    [DllImport("winspool.drv", EntryPoint = "EndPagePrinter", SetLastError = true)]
    public static extern bool EndPagePrinter(IntPtr hPrinter);

    [DllImport("winspool.drv", EntryPoint = "WritePrinter", SetLastError = true)]
    public static extern bool WritePrinter(IntPtr hPrinter, IntPtr pBytes, int dwCount, out int dwWritten);

    public static int SendBytes(string printerName, byte[] bytes)
    {
        IntPtr hPrinter = IntPtr.Zero;

        if (!OpenPrinter(printerName, out hPrinter, IntPtr.Zero))
            throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "No se pudo abrir la impresora " + printerName);

        try
        {
            DOCINFOA di = new DOCINFOA();
            di.pDocName = "NightPOS Ticket";
            di.pOutputFile = null;
            di.pDataType = "RAW";

            if (!StartDocPrinter(hPrinter, 1, di))
                throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "Fallo al iniciar documento");

            if (!StartPagePrinter(hPrinter))
            {
                EndDocPrinter(hPrinter);
                throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "Fallo al iniciar pagina");
            }

            IntPtr pUnmanagedBytes = Marshal.AllocCoTaskMem(bytes.Length);
            try
            {
                Marshal.Copy(bytes, 0, pUnmanagedBytes, bytes.Length);
                int dwWritten = 0;
                bool ok = WritePrinter(hPrinter, pUnmanagedBytes, bytes.Length, out dwWritten);

                if (!ok)
                    throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "Fallo al escribir en la impresora");

                EndPagePrinter(hPrinter);
                EndDocPrinter(hPrinter);

                return dwWritten;
            }
            finally
            {
                Marshal.FreeCoTaskMem(pUnmanagedBytes);
            }
        }
        finally
        {
            ClosePrinter(hPrinter);
        }
    }
}
'@

try {
    Add-Type -TypeDefinition $source -Language CSharp -ErrorAction Stop
}
catch {
    if (-not ("Win32RawPrinter" -as [type])) {
        Write-Error "Fallo al compilar helper RAW: $_"
        exit 1
    }
}

try {
    $written = [Win32RawPrinter]::SendBytes($PrinterName, $bytes)
    Write-Output "OK: $written bytes enviados a $PrinterName"
    exit 0
}
catch {
    Write-Error $_.Exception.Message
    exit 2
}
