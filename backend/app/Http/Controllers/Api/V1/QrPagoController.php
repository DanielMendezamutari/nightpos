<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\PagoQrModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrPagoController extends Controller
{
    /**
     * Generar un nuevo cÃ³digo QR dinÃ¡mico para una mesa o visita.
     */
    public function generar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mesa_id' => 'nullable|integer|exists:mesas,id',
            'visita_id' => 'nullable|integer|exists:visitas,id',
            'monto' => 'required|numeric|min:0.50',
            'glosa' => 'nullable|string|max:150',
        ]);

        $mesaId = $validated['mesa_id'] ?? null;
        $visitaId = $validated['visita_id'] ?? null;

        if (!$visitaId && $mesaId) {
            $visita = VisitaModel::where('mesa_id', $mesaId)
                ->where('estado', 'ACTIVA')
                ->latest()
                ->first();
            if ($visita) {
                $visitaId = $visita->id;
            }
        }

        $codigoTransaccion = 'QR-' . strtoupper(Str::random(8)) . '-' . time();
        $monto = (float) $validated['monto'];
        $glosa = $validated['glosa'] ?? 'Consumo RiberResto POS - Mesa ' . ($mesaId ?? 'Barra');

        // Formato EMVCo Simple QR Bolivia (ASOBAN estÃ¡ndar interbancario)
        $qrPayload = sprintf(
            '00020101021226460014bo.gob.sin.qr011010284560230216%s520458125303BOB54%02d%.2f5802BO5917RIBERSOFT BOLIVIA6010SANTA CRUZ62%02d01%02d%s6304ABCD',
            $codigoTransaccion,
            strlen(number_format($monto, 2, '.', '')),
            $monto,
            strlen($glosa) + 4,
            strlen($glosa),
            $glosa
        );

        $pagoQr = PagoQrModel::create([
            'visita_id' => $visitaId,
            'mesa_id' => $mesaId,
            'codigo_transaccion' => $codigoTransaccion,
            'monto' => $monto,
            'moneda' => 'BOB',
            'glosa' => $glosa,
            'estado' => 'PENDIENTE',
            'qr_payload' => $qrPayload,
            'vence_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'CÃ³digo QR dinÃ¡mico generado con Ã©xito',
            'data' => [
                'id' => $pagoQr->id,
                'codigo_transaccion' => $pagoQr->codigo_transaccion,
                'monto' => (float) $pagoQr->monto,
                'moneda' => $pagoQr->moneda,
                'glosa' => $pagoQr->glosa,
                'estado' => $pagoQr->estado,
                'qr_payload' => $pagoQr->qr_payload,
                'vence_at' => $pagoQr->vence_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Consultar el estado en tiempo real (Polling cada 2s desde POS).
     */
    public function estado(string $codigo): JsonResponse
    {
        $pagoQr = PagoQrModel::where('codigo_transaccion', $codigo)
            ->orWhere('id', is_numeric($codigo) ? (int)$codigo : 0)
            ->first();

        if (!$pagoQr) {
            return response()->json([
                'success' => false,
                'message' => 'CÃ³digo QR no encontrado',
            ], 404);
        }

        // Si expirÃ³ y sigue pendiente, actualizar
        if ($pagoQr->estado === 'PENDIENTE' && $pagoQr->vence_at && $pagoQr->vence_at->isPast()) {
            $pagoQr->update(['estado' => 'EXPIRADO']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pagoQr->id,
                'codigo_transaccion' => $pagoQr->codigo_transaccion,
                'estado' => $pagoQr->estado,
                'monto' => (float) $pagoQr->monto,
                'moneda' => $pagoQr->moneda,
                'banco' => $pagoQr->banco,
                'referencia_bancaria' => $pagoQr->referencia_bancaria,
                'pagado_at' => $pagoQr->pagado_at ? $pagoQr->pagado_at->toIso8601String() : null,
            ],
        ]);
    }

    /**
     * Webhook bancario para recepciÃ³n de confirmaciones de pago (ASOBAN / Pasarelas BCP, BNB, etc.).
     */
    public function webhook(Request $request): JsonResponse
    {
        $codigo = $request->input('codigo_transaccion') ?? $request->input('transaction_id') ?? $request->input('id');
        $referencia = $request->input('referencia_bancaria') ?? $request->input('reference') ?? 'REF-' . rand(100000, 999999);
        $banco = $request->input('banco') ?? $request->input('bank') ?? 'ASOBAN INTERBANCARIO';
        $estado = strtoupper($request->input('estado') ?? 'PAGADO');

        $pagoQr = PagoQrModel::where('codigo_transaccion', $codigo)->first();

        if (!$pagoQr) {
            return response()->json([
                'success' => false,
                'message' => 'TransacciÃ³n QR no encontrada en el sistema',
            ], 404);
        }

        if ($pagoQr->estado === 'PAGADO') {
            return response()->json([
                'success' => true,
                'message' => 'El pago ya habÃ­a sido procesado previamente',
            ]);
        }

        if ($estado === 'PAGADO' || $estado === 'COMPLETED' || $estado === 'CONFIRMED') {
            $pagoQr->update([
                'estado' => 'PAGADO',
                'banco' => $banco,
                'referencia_bancaria' => $referencia,
                'pagado_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pago confirmado exitosamente mediante Webhook bancario',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Estado no reconocido o rechazado por el banco',
        ], 400);
    }

    /**
     * Endpoint de simulaciÃ³n para pruebas en local y validaciÃ³n de UI de caja.
     */
    public function simular(string $codigo): JsonResponse
    {
        $pagoQr = PagoQrModel::where('codigo_transaccion', $codigo)
            ->orWhere('id', is_numeric($codigo) ? (int)$codigo : 0)
            ->first();

        if (!$pagoQr) {
            return response()->json([
                'success' => false,
                'message' => 'CÃ³digo QR no encontrado',
            ], 404);
        }

        $bancos = ['BANCO BCP', 'BANCO NACIONAL DE BOLIVIA (BNB)', 'BANCO GANADERO', 'BANCO UNION', 'BANCO MERCANTIL SANTA CRUZ'];
        $bancoElegido = $bancos[array_rand($bancos)];

        $pagoQr->update([
            'estado' => 'PAGADO',
            'banco' => $bancoElegido,
            'referencia_bancaria' => 'SIM-' . strtoupper(Str::random(6)) . '-' . rand(1000, 9999),
            'pagado_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pago simulado y confirmado con Ã©xito por ' . $bancoElegido,
            'data' => [
                'codigo_transaccion' => $pagoQr->codigo_transaccion,
                'estado' => $pagoQr->estado,
                'banco' => $pagoQr->banco,
                'referencia_bancaria' => $pagoQr->referencia_bancaria,
                'pagado_at' => $pagoQr->pagado_at->toIso8601String(),
            ],
        ]);
    }
}
