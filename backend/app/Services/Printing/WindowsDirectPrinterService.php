<?php

declare(strict_types=1);

namespace App\Services\Printing;

use Illuminate\Support\Facades\Log;

class WindowsDirectPrinterService
{
    private const DEFAULT_PRINTER = 'CAJA';

    public function __construct(
        private readonly TicketFormatterService $formatter
    ) {}

    /**
     * Imprime una factura o recibo formateado en ESC/POS RAW directo a la impresora de Windows.
     */
    public function imprimirFactura(array $factura, ?string $printerName = null, bool $abrirCajon = false): array
    {
        $rawBytes = $this->formatter->formatTicket($factura, $abrirCajon);
        return $this->imprimirRaw($rawBytes, $printerName);
    }

    /**
     * Imprime una precuenta formateada en ESC/POS RAW idéntica a RestoTech.
     */
    public function imprimirPrecuenta(array $cuenta, ?string $printerName = null): array
    {
        $rawBytes = $this->formatter->formatPrecuenta($cuenta);
        return $this->imprimirRaw($rawBytes, $printerName);
    }

    /**
     * Envía bytes RAW directamente al Spooler de Windows (winspool.drv) sin márgenes GDI.
     */
    public function imprimirRaw(string $rawBytes, ?string $printerName = null): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [
                'success' => false,
                'message' => 'Impresión directa por Windows Spooler sólo está disponible en servidor local Windows',
                'is_windows' => false,
            ];
        }

        $printer = $printerName ?: env('POS_PRINTER_NAME', self::DEFAULT_PRINTER);

        // Guardar bytes en archivo temporal binario
        $tempDir = storage_path('app');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'job_' . uniqid() . '.bin';

        try {
            file_put_contents($tempFile, $rawBytes);

            $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'raw_print.ps1';
            $escapedScript = addslashes($scriptPath);
            $escapedFile = addslashes($tempFile);
            $escapedPrinter = addslashes($printer);

            // Comando PowerShell para enviar vía winspool.drv RAW
            $cmd = "powershell -NoProfile -ExecutionPolicy Bypass -File \"{$escapedScript}\" -PrinterName \"{$escapedPrinter}\" -FilePath \"{$escapedFile}\"";

            $output = [];
            $returnCode = 0;
            exec($cmd, $output, $returnCode);

            // Limpiar archivo temporal
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }

            if ($returnCode === 0) {
                Log::info("Ticket RAW impreso directamente en {$printer}: " . implode(' ', $output));
                return [
                    'success' => true,
                    'message' => "Ticket enviado directamente a la impresora {$printer}",
                    'printer' => $printer,
                    'method' => 'windows_raw_spooler',
                    'bytes' => strlen($rawBytes),
                ];
            }

            $errorMsg = implode("\n", $output) ?: "Error al enviar a la impresora {$printer} (código {$returnCode})";
            Log::warning("Fallo al imprimir RAW en {$printer}: " . $errorMsg);

            return [
                'success' => false,
                'message' => $errorMsg,
                'printer' => $printer,
            ];
        } catch (\Throwable $e) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
            Log::error("Excepción en WindowsDirectPrinterService: " . $e->getMessage());

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'printer' => $printer,
            ];
        }
    }

    /**
     * Lista las impresoras instaladas en Windows.
     */
    public function listarImpresoras(): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [];
        }

        $cmd = "powershell -NoProfile -ExecutionPolicy Bypass -Command \"Get-Printer | Select-Object Name, DriverName, PortName, Default | ConvertTo-Json -Compress\"";
        $output = [];
        exec($cmd, $output);
        $json = implode('', $output);

        $printers = json_decode($json, true);
        if (!$printers) {
            return [];
        }

        if (isset($printers['Name'])) {
            return [$printers];
        }

        return $printers;
    }
}
