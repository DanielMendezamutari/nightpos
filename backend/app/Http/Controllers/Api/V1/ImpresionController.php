<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\FacturaModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
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
     * Imprime una factura o recibo existente directamente a la impresora térmica en formato RAW 80mm.
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
        $mesaNumero = 'BARRA';
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
     * Imprime la PRECUENTA de una mesa activa idéntica a RestoTech (printCuentaTotalFactura).
     */
    public function imprimirPrecuenta(Request $request, string|int $mesaId): JsonResponse
    {
        $isSinMesa = str_starts_with((string)$mesaId, 'sin_mesa_') || $request->filled('visita_id');

        if ($isSinMesa) {
            $visitaId = $request->filled('visita_id')
                ? (int)$request->input('visita_id')
                : (int)str_replace('sin_mesa_', '', (string)$mesaId);
            $visita = VisitaModel::with(['detalles.producto', 'mesero', 'cliente'])->find($visitaId);
            $mesaNombre = 'SIN MESA / LLEVAR';
        } else {
            $mesa = MesaModel::with(['visitaActiva.detalles.producto', 'visitaActiva.mesero', 'visitaActiva.cliente'])->find((int)$mesaId);
            if (!$mesa) {
                return response()->json([
                    'success' => false,
                    'message' => "Mesa ID {$mesaId} no encontrada",
                ], 404);
            }
            $visita = $mesa->visitaActiva;
            $mesaNombre = $mesa->codigo ?? ('MESA ' . ($mesa->numero ?? $mesa->id));
        }

        if (!$visita || $visita->detalles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'La mesa no tiene consumos registrados para generar precuenta',
            ], 422);
        }

        $items = [];
        $totalCalculado = 0.0;
        foreach ($visita->detalles as $det) {
            $subtotal = (float)$det->subtotal;
            $totalCalculado += $subtotal;
            $items[] = [
                'cantidad' => $det->cantidad,
                'producto_nombre' => $det->producto ? $det->producto->nombre : ($det->descripcion ?? 'Producto'),
                'precio_unitario' => $det->precio_unitario,
                'subtotal' => $subtotal,
            ];
        }

        $meseroNombre = 'Garzon';
        if ($visita->mesero) {
            $meseroNombre = $visita->mesero->name ?? $visita->mesero->nombre ?? 'Garzon';
        }

        $clienteNombre = $visita->cliente_nombre ?: ($visita->cliente ? trim(($visita->cliente->nombre ?? '') . ' ' . ($visita->cliente->apellidos ?? '')) : '');

        $cuentaData = [
            'mesa_numero' => $mesaNombre,
            'mesero' => $meseroNombre,
            'cliente' => $clienteNombre,
            'fecha' => now()->format('Y-m-d H:i:s'),
            'monto_total' => (float)$visita->total > 0 ? (float)$visita->total : $totalCalculado,
            'detalles' => $items,
        ];

        $printerName = $request->input('impresora') ?: env('POS_PRINTER_NAME', 'CAJA');
        $printResult = $this->printerService->imprimirPrecuenta($cuentaData, $printerName);

        return response()->json([
            'success' => $printResult['success'],
            'message' => $printResult['success']
                ? "Precuenta enviada a impresora {$printerName} con exito"
                : "Fallo al imprimir precuenta: " . ($printResult['message'] ?? 'Error desconocido'),
            'data' => [
                'mesa' => $mesaNombre,
                'mesero' => $meseroNombre,
                'total' => $cuentaData['monto_total'],
                'items_count' => count($items),
                'impresion' => $printResult,
            ],
        ], $printResult['success'] ? 200 : 500);
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
     * Imprime un ticket de prueba y diagnóstico rápido en modo RAW 80mm.
     */
    public function test(Request $request): JsonResponse
    {
        $printerName = $request->input('impresora') ?: env('POS_PRINTER_NAME', 'CAJA');

        $cuentaTest = [
            'mesa_numero' => 'TEST MESA 1',
            'mesero' => 'Cajero Principal',
            'cliente' => 'Test Cliente',
            'fecha' => date('Y-m-d H:i:s'),
            'monto_total' => 150.00,
            'detalles' => [
                ['cantidad' => 1, 'producto_nombre' => 'Pique Macho Ribersoft', 'subtotal' => 95.00],
                ['cantidad' => 1, 'producto_nombre' => 'Silpancho Cochabambino', 'subtotal' => 55.00],
            ],
        ];

        $result = $this->printerService->imprimirPrecuenta($cuentaTest, $printerName);

        return response()->json($result, $result['success'] ? 200 : 500);
    }
}
