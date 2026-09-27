<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\FacturaModel;
use App\Services\Printing\WindowsDirectPrinterService;
use App\Services\Printing\TicketFormatterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImpresionController extends Controller
{
    public function __construct(
        private readonly WindowsDirectPrinterService $printerService,
        private readonly TicketFormatterService $formatterService
    ) {}

    /**
     * Imprime una factura o recibo existente directamente a la impresora térmica.
     */
    public function imprimirTicket(Request $request, int $facturaId): JsonResponse
    {
        $factura = FacturaModel::with(['detalles.producto', 'visita.mesa', 'cajero'])->find($facturaId);

        if (!$factura) {
            return response()->json([
                'success' => false,
                'message' => "Factura o comprobante ID {$facturaId} no encontrado",
            ], 404);
        }

        // Armar estructura de factura para el formateador
        $mesaNumero = 'SIN MESA';
        if ($factura->visita && $factura->visita->mesa) {
            $mesaNumero = $factura->visita->mesa->codigo ?? $factura->visita->mesa->numero ?? 'MESA';
        } elseif ($factura->visita) {
            $mesaNumero = $factura->visita->tipo_despacho ?? 'PARA LLEVAR';
        }

        $items = [];
        if ($factura->detalles) {
            foreach ($factura->detalles as $det) {
                $items[] = [
                    'cantidad' => $det->cantidad,
                    'producto_nombre' => $det->producto ? $det->producto->nombre : ($det->descripcion ?? 'Producto'),
                    'subtotal' => $det->subtotal,
                ];
            }
        }

        $facturaData = [
            'id' => $factura->id,
            'tipo_comprobante' => $factura->tipo_comprobante ?? 'FACTURA',
            'nro_comprobante' => $factura->nro_comprobante,
            'nro_factura' => $factura->nro_factura,
            'cuf' => $factura->cuf,
            'razon_social' => $factura->razon_social,
            'numero_documento' => $factura->numero_documento,
            'fecha_emision' => $factura->fecha_emision,
            'mesa_numero' => $mesaNumero,
            'cajero' => $factura->cajero ? $factura->cajero->name : 'Caja',
            'metodo_pago' => $factura->metodo_pago,
            'monto_total' => $factura->monto_total,
            'monto_recibido' => $factura->monto_recibido ?? $factura->monto_total,
            'cambio' => $factura->monto_cambio ?? 0,
            'monto_efectivo' => $factura->monto_efectivo ?? 0,
            'monto_tarjeta' => $factura->monto_tarjeta ?? 0,
            'monto_qr' => $factura->monto_qr ?? 0,
            'detalles' => $items,
        ];

        $printerName = $request->query('impresora') ?? $request->input('impresora');
        $result = $this->printerService->imprimirFactura($facturaData, $printerName);

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    /**
     * Lista las impresoras instaladas en el sistema.
     */
    public function listarImpresoras(): JsonResponse
    {
        $impresoras = $this->printerService->listarImpresoras();

        return response()->json([
            'success' => true,
            'data' => $impresoras,
            'default_pos_printer' => env('POS_PRINTER_NAME', 'CAJA'),
        ]);
    }

    /**
     * Imprime un ticket de prueba y diagnóstico rápido.
     */
    public function test(Request $request): JsonResponse
    {
        $printerName = $request->input('impresora') ?: env('POS_PRINTER_NAME', 'CAJA');

        $testText = "========================================\r\n"
                  . "             RIBERRESTO POS             \r\n"
                  . "       PRUEBA DE IMPRESION DIRECTA      \r\n"
                  . "----------------------------------------\r\n"
                  . "Impresora: {$printerName}\r\n"
                  . "Fecha: " . date('Y-m-d H:i:s') . "\r\n"
                  . "Estado: COMUNICACION DIRECTA OK\r\n"
                  . "----------------------------------------\r\n"
                  . "Impresion termica 80mm configurada!\r\n"
                  . "Desarrollado por Ribersoft\r\n"
                  . "========================================\r\n\r\n\r\n";

        $result = $this->printerService->imprimirTexto($testText, $printerName);

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}
