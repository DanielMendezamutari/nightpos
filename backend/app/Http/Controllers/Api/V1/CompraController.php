<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\CompraModel;
use App\Infrastructure\Persistence\Eloquent\Models\CompraDetalleModel;
use App\Infrastructure\Persistence\Eloquent\Models\InsumoModel;
use App\Infrastructure\Persistence\Eloquent\Models\KardexMovimientoModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProveedorModel;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = CompraModel::with(['proveedor', 'almacen', 'detalles.insumo'])->orderByDesc('id');

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        $compras = $query->limit(50)->get();
        return response()->json($compras);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'turno_id' => 'nullable|exists:turnos,id',
            'nro_factura' => 'nullable|string|max:50',
            'fecha_compra' => 'nullable|date',
            'descuento' => 'numeric|min:0',
            'ice' => 'numeric|min:0',
            'metodo_pago' => 'required|in:CONTADO,CREDITO',
            'observaciones' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.insumo_id' => 'required|exists:insumos,id',
            'items.*.cantidad' => 'required|numeric|min:0.001',
            'items.*.costo_unitario' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            $turno = TurnoModel::where('estado', 'ABIERTO')->first();
            $turnoId = $data['turno_id'] ?? ($turno?->id);

            $montoTotal = 0;
            foreach ($data['items'] as $item) {
                $montoTotal += ($item['cantidad'] * $item['costo_unitario']);
            }
            $descuento = $data['descuento'] ?? 0;
            $ice = $data['ice'] ?? 0;
            $montoFinal = max(0, $montoTotal - $descuento + $ice);

            $compra = CompraModel::create([
                'proveedor_id' => $data['proveedor_id'],
                'almacen_id' => $data['almacen_id'],
                'turno_id' => $turnoId,
                'nro_factura' => $data['nro_factura'] ?? null,
                'fecha_compra' => $data['fecha_compra'] ?? now(),
                'monto_total' => $montoFinal,
                'descuento' => $descuento,
                'ice' => $ice,
                'metodo_pago' => $data['metodo_pago'],
                'estado' => 'COMPLETADA',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $subtotal = $item['cantidad'] * $item['costo_unitario'];
                CompraDetalleModel::create([
                    'compra_id' => $compra->id,
                    'insumo_id' => $item['insumo_id'],
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo_unitario'],
                    'subtotal' => $subtotal,
                ]);

                // Actualizar Insumo: Stock y Costo Promedio Ponderado
                $insumo = InsumoModel::lockForUpdate()->findOrFail($item['insumo_id']);
                $stockAnterior = (float) $insumo->stock_actual;
                $costoAnterior = (float) $insumo->costo_promedio;
                $cantComprada = (float) $item['cantidad'];
                $costoCompra = (float) $item['costo_unitario'];

                $stockNuevo = $stockAnterior + $cantComprada;
                $nuevoCostoPromedio = $stockNuevo > 0 
                    ? (($stockAnterior * $costoAnterior) + ($cantComprada * $costoCompra)) / $stockNuevo
                    : $costoCompra;

                $insumo->update([
                    'stock_actual' => $stockNuevo,
                    'costo_promedio' => round($nuevoCostoPromedio, 4),
                    'ultimo_costo' => $costoCompra,
                ]);

                // Registrar en Kardex
                KardexMovimientoModel::create([
                    'insumo_id' => $insumo->id,
                    'almacen_id' => $data['almacen_id'],
                    'tipo' => 'COMPRA',
                    'cantidad' => $cantComprada,
                    'costo_unitario' => $costoCompra,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'referencia' => 'Compra #' . $compra->id . ' ' . ($compra->nro_factura ? 'Fact: ' . $compra->nro_factura : ''),
                ]);
            }

            // Si la compra es a crÃ©dito, sumar al saldo de deuda del proveedor
            if ($data['metodo_pago'] === 'CREDITO') {
                $proveedor = ProveedorModel::findOrFail($data['proveedor_id']);
                $proveedor->increment('saldo_deuda', $montoFinal);
            }

            return response()->json($compra->load(['proveedor', 'almacen', 'detalles.insumo']), 201);
        });
    }
}