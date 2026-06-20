param(
    [string]$PrinterName = '',
    [string]$FilePath = '',
    [switch]$ListPrinters,
    [switch]$Info
)

$ErrorActionPreference = 'Stop'

function Out-Json($obj) {
    $obj | ConvertTo-Json -Compress
}

if ($ListPrinters) {
    Get-Printer | ForEach-Object { $_.Name }
    exit 0
}

if ($Info) {
    if (-not $PrinterName) {
        Out-Json @{ found = $false; message = 'PrinterName required' }
        exit 1
    }

    $printer = Get-Printer -Name $PrinterName -ErrorAction SilentlyContinue
    if (-not $printer) {
        Out-Json @{ found = $false; name = $PrinterName }
        exit 0
    }

    Out-Json @{
        found = $true
        name = $printer.Name
        status = [string]$printer.PrinterStatus
        driver = $printer.DriverName
        port = $printer.PortName
        shared = [bool]$printer.Shared
        type = [string]$printer.Type
    }
    exit 0
}

if (-not $PrinterName -or -not $FilePath) {
    Write-Error 'PrinterName and FilePath are required for RAW print'
    exit 1
}

if (-not (Test-Path -LiteralPath $FilePath)) {
    Write-Error "File not found: $FilePath"
    exit 1
}

$bytes = [System.IO.File]::ReadAllBytes($FilePath)

$source = @'
using System;
using System.Runtime.InteropServices;

public class NightPosRawPrinter
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

    public static string SendBytesToPrinter(string printerName, byte[] bytes)
    {
        IntPtr hPrinter = IntPtr.Zero;

        if (!OpenPrinter(printerName, out hPrinter, IntPtr.Zero))
            throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "OpenPrinter failed");

        try
        {
            DOCINFOA di = new DOCINFOA();
            di.pDocName = "NightPOS Ticket";
            di.pOutputFile = null;
            di.pDataType = "RAW";

            if (!StartDocPrinter(hPrinter, 1, di))
                throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "StartDocPrinter failed");

            if (!StartPagePrinter(hPrinter))
            {
                EndDocPrinter(hPrinter);
                throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "StartPagePrinter failed");
            }

            IntPtr pUnmanagedBytes = Marshal.AllocCoTaskMem(bytes.Length);
            try
            {
                Marshal.Copy(bytes, 0, pUnmanagedBytes, bytes.Length);
                int dwWritten = 0;
                bool ok = WritePrinter(hPrinter, pUnmanagedBytes, bytes.Length, out dwWritten);

                if (!ok)
                    throw new System.ComponentModel.Win32Exception(Marshal.GetLastWin32Error(), "WritePrinter failed");

                if (dwWritten != bytes.Length)
                    throw new Exception("WritePrinter incomplete: wrote " + dwWritten + " of " + bytes.Length);

                EndPagePrinter(hPrinter);
                EndDocPrinter(hPrinter);

                return "RAW job submitted, bytes=" + dwWritten;
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
    if (-not ("NightPosRawPrinter" -as [type])) {
        Write-Error "Failed to compile RawPrinter helper: $_"
        exit 1
    }
}

try {
    $message = [NightPosRawPrinter]::SendBytesToPrinter($PrinterName, $bytes)
    Out-Json @{
        success = $true
        bytesSent = $bytes.Length
        message = $message
        printer = $PrinterName
    }
    exit 0
}
catch {
    $win32 = $_.Exception.InnerException
    if ($win32 -and $win32.NativeErrorCode) {
        Out-Json @{
            success = $false
            win32Error = $win32.NativeErrorCode
            message = $_.Exception.Message
            printer = $PrinterName
        }
        exit 2
    }

    Out-Json @{
        success = $false
        message = $_.Exception.Message
        printer = $PrinterName
    }
    exit 2
}
