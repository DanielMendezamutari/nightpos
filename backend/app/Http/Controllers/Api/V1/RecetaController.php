<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\InsumoModel;
use App\Infrastructure\Persistence\Eloquent\Models\KardexMovimientoModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductoModel;
use App\Infrastructure\Persistence\Eloquent\Models\RecetaModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecetaController extends Controller
{
    public function getReceta(int $productoId): JsonResponse
    {
        $producto = ProductoModel::with('categoria')->findOrFail($productoId);
        $ingredientes = RecetaModel::with('insumo')
            ->where('producto_id', $productoId)
            ->get();

        $costoTeorico = 0;
        foreach ($ingredientes as $item) {
            $costoInsumo = (float) ($item->insumo?->costo_promedio ?? 0);
            $cantRequerida = (float) $item->cantidad;
            $merma = (float) $item->merma_porcentaje;
            $cantReal = $cantRequerida * (1 + ($merma / 100));
            $costoTeorico += ($cantReal * $costoInsumo);
        }

        $precioVenta = (float) $producto->precio;
        $margenContribucion = $precioVenta > 0 ? (($precioVenta - $costoTeorico) / $precioVenta) * 100 : 0;

        return response()->json([
            'producto' => $producto,
            'ingredientes' => $ingredientes,
            'costo_teorico' => round($costoTeorico, 2),
            'precio_venta' => $precioVenta,
            'margen_porcentaje' => round($margenContribucion, 1),
        ]);
    }

    public function guardarReceta(Request $request, int $productoId): JsonResponse
    {
        $producto = ProductoModel::findOrFail($productoId);

        $data = $request->validate([
            'ingredientes' => 'present|array',
            'ingredientes.*.insumo_id' => 'required|exists:insumos,id',
            'ingredientes.*.cantidad' => 'required|numeric|min:0.0001',
            'ingredientes.*.unidad_medida' => 'required|string|max:20',
            'ingredientes.*.merma_porcentaje' => 'numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($producto, $data) {
            // Reemplazar receta completa
            RecetaModel::where('producto_id', $producto->id)->delete();

            $costoTeorico = 0;
            foreach ($data['ingredientes'] as $item) {
                RecetaModel::create([
                    'producto_id' => $producto->id,
                    'insumo_id' => $item['insumo_id'],
                    'cantidad' => $item['cantidad'],
                    'unidad_medida' => $item['unidad_medida'],
                    'merma_porcentaje' => $item['merma_porcentaje'] ?? 0,
                ]);

                $insumo = InsumoModel::find($item['insumo_id']);
                if ($insumo) {
                    $cant = (float) $item['cantidad'] * (1 + (($item['merma_porcentaje'] ?? 0) / 100));
                    $costoTeorico += ($cant * (float) $insumo->costo_promedio);
                }
            }

            // Actualizar costo del producto
            $producto->update(['costo' => round($costoTeorico, 2)]);

            return response()->json([
                'message' => 'Ficha técnica / Receta guardada correctamente',
                'costo_teorico' => round($costoTeorico, 2),
            ]);
        });
    }

    /**
     * Servicio interno para descontar insumos de recetas al venderse o consumirse
     */
    public static function descontarInsumosPorVenta(int $productoId, float $cantidadVendida, string $referencia = ''): void
    {
        $ingredientes = RecetaModel::where('producto_id', $productoId)->get();

        foreach ($ingredientes as $rec) {
            $insumo = InsumoModel::find($rec->insumo_id);
            if (!$insumo) continue;

            $factorMerma = 1 + ((float) $rec->merma_porcentaje / 100);
            $cantDescontar = (float) $rec->cantidad * $cantidadVendida * $factorMerma;

            $stockAnterior = (float) $insumo->stock_actual;
            $stockNuevo = max(0, $stockAnterior - $cantDescontar);

            $insumo->update(['stock_actual' => $stockNuevo]);

            KardexMovimientoModel::create([
                'tenant_id' => $insumo->tenant_id,
                'insumo_id' => $insumo->id,
                'almacen_id' => $insumo->almacen_id ?? 1,
                'tipo' => 'CONSUMO_VENTA',
                'cantidad' => $cantDescontar,
                'costo_unitario' => $insumo->costo_promedio,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'referencia' => $referencia ?: 'Consumo Venta POS',
            ]);
        }
    }
}