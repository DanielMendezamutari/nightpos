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
     * Imprime una factura o recibo formateado directamente a la impresora de Windows.
     */
    public function imprimirFactura(array $factura, ?string $printerName = null): array
    {
        $texto = $this->formatter->formatTicket($factura);
        return $this->imprimirTexto($texto, $printerName);
    }

    /**
     * Envía texto plano al spooler de Windows hacia la impresora indicada.
     */
    public function imprimirTexto(string $texto, ?string $printerName = null): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [
                'success' => false,
                'message' => 'Impresión directa por Windows Spooler sólo está disponible en servidor local Windows',
                'is_windows' => false,
            ];
        }

        $printer = $printerName ?: env('POS_PRINTER_NAME', self::DEFAULT_PRINTER);

        // Guardar ticket en archivo temporal
        $tempDir = sys_get_temp_dir();
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'ticket_' . uniqid() . '.txt';

        try {
            // Guardamos con codificación UTF-8
            file_put_contents($tempFile, $texto);

            // Escapar rutas para PowerShell
            $escapedFile = addslashes($tempFile);
            $escapedPrinter = addslashes($printer);

            // Comando PowerShell para enviar directamente al spooler de la impresora
            $cmd = "powershell -NoProfile -ExecutionPolicy Bypass -Command \"Get-Content -LiteralPath '{$escapedFile}' -Encoding UTF8 | Out-Printer -Name '{$escapedPrinter}'\"";

            $output = [];
            $returnCode = 0;
            exec($cmd, $output, $returnCode);

            // Limpiar archivo temporal
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }

            if ($returnCode === 0) {
                Log::info("Ticket impreso directamente en impresora {$printer}");
                return [
                    'success' => true,
                    'message' => "Ticket enviado directamente a la impresora {$printer}",
                    'printer' => $printer,
                    'method' => 'windows_spooler',
                ];
            }

            $errorMsg = implode("\n", $output) ?: "Error al enviar a la impresora {$printer} (código {$returnCode})";
            Log::warning("Fallo al imprimir en {$printer}: " . $errorMsg);

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

        // Si sólo hay una, PowerShell a veces devuelve un objeto simple en lugar de un array
        if (isset($printers['Name'])) {
            return [$printers];
        }

        return $printers;
    }
}
