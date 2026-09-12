<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\AlmacenModel;
use App\Infrastructure\Persistence\Eloquent\Models\InsumoModel;
use App\Infrastructure\Persistence\Eloquent\Models\KardexMovimientoModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    // ==========================================
    // ALMACENES
    // ==========================================
    public function getAlmacenes(): JsonResponse
    {
        $almacenes = AlmacenModel::withCount('insumos')->orderBy('nombre')->get();
        return response()->json($almacenes);
    }

    public function storeAlmacen(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'es_interno' => 'boolean',
            'responsable' => 'nullable|string|max:150',
            'activo' => 'boolean',
        ]);

        $almacen = AlmacenModel::create($data);
        return response()->json($almacen, 201);
    }

    public function updateAlmacen(Request $request, int $id): JsonResponse
    {
        $almacen = AlmacenModel::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
            'es_interno' => 'boolean',
            'responsable' => 'nullable|string|max:150',
            'activo' => 'boolean',
        ]);

        $almacen->update($data);
        return response()->json($almacen);
    }

    public function deleteAlmacen(int $id): JsonResponse
    {
        $almacen = AlmacenModel::findOrFail($id);
        $almacen->update(['activo' => false]);
        return response()->json(['message' => 'Almacén desactivado correctamente']);
    }

    // ==========================================
    // INSUMOS
    // ==========================================
    public function getInsumos(Request $request): JsonResponse
    {
        $query = InsumoModel::with('almacen')->where('activo', true);

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('codigo', 'like', "%{$s}%");
            });
        }

        if ($request->boolean('solo_bajo_stock')) {
            $query->whereColumn('stock_actual', '<=', 'stock_minimo');
        }

        $insumos = $query->orderBy('nombre')->get();
        return response()->json($insumos);
    }

    public function storeInsumo(Request $request): JsonResponse
    {
        $data = $request->validate([
            'almacen_id' => 'nullable|exists:almacenes,id',
            'codigo' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:150',
            'unidad_medida' => 'required|string|max:20',
            'costo_promedio' => 'numeric|min:0',
            'ultimo_costo' => 'numeric|min:0',
            'stock_actual' => 'numeric|min:0',
            'stock_minimo' => 'numeric|min:0',
            'stock_maximo' => 'numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            $insumo = InsumoModel::create($data);

            // Si se inició con stock > 0, crear registro en Kardex
            if (!empty($data['stock_actual']) && $data['stock_actual'] > 0 && !empty($data['almacen_id'])) {
                KardexMovimientoModel::create([
                    'tenant_id' => $insumo->tenant_id,
                    'insumo_id' => $insumo->id,
                    'almacen_id' => $data['almacen_id'],
                    'tipo' => 'AJUSTE_POSITIVO',
                    'cantidad' => $data['stock_actual'],
                    'costo_unitario' => $data['costo_promedio'] ?? 0,
                    'stock_anterior' => 0,
                    'stock_nuevo' => $data['stock_actual'],
                    'referencia' => 'Inventario Inicial',
                ]);
            }

            return response()->json($insumo->load('almacen'), 201);
        });
    }

    public function updateInsumo(Request $request, int $id): JsonResponse
    {
        $insumo = InsumoModel::findOrFail($id);
        $data = $request->validate([
            'almacen_id' => 'nullable|exists:almacenes,id',
            'codigo' => 'nullable|string|max:50',
            'nombre' => 'required|string|max:150',
            'unidad_medida' => 'required|string|max:20',
            'costo_promedio' => 'numeric|min:0',
            'ultimo_costo' => 'numeric|min:0',
            'stock_minimo' => 'numeric|min:0',
            'stock_maximo' => 'numeric|min:0',
            'activo' => 'boolean',
        ]);

        $insumo->update($data);
        return response()->json($insumo->load('almacen'));
    }

    public function deleteInsumo(int $id): JsonResponse
    {
        $insumo = InsumoModel::findOrFail($id);
        $insumo->update(['activo' => false]);
        return response()->json(['message' => 'Insumo desactivado correctamente']);
    }

    // ==========================================
    // AJUSTES MANUALES DE STOCK (INVENTARIO FÍSICO / MERMA)
    // ==========================================
    public function ajustarStock(Request $request, int $id): JsonResponse
    {
        $insumo = InsumoModel::findOrFail($id);

        $data = $request->validate([
            'nuevo_stock' => 'required|numeric|min:0',
            'tipo' => 'required|in:AJUSTE_POSITIVO,AJUSTE_NEGATIVO,MERMA',
            'motivo' => 'required|string|max:255',
            'almacen_id' => 'nullable|exists:almacenes,id',
        ]);

        return DB::transaction(function () use ($insumo, $data) {
            $stockAnterior = (float) $insumo->stock_actual;
            $stockNuevo = (float) $data['nuevo_stock'];
            $diferencia = abs($stockNuevo - $stockAnterior);
            $almacenId = $data['almacen_id'] ?? $insumo->almacen_id ?? 1;

            $insumo->update(['stock_actual' => $stockNuevo]);

            KardexMovimientoModel::create([
                'tenant_id' => $insumo->tenant_id,
                'insumo_id' => $insumo->id,
                'almacen_id' => $almacenId,
                'tipo' => $data['tipo'],
                'cantidad' => $diferencia,
                'costo_unitario' => $insumo->costo_promedio,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'referencia' => $data['motivo'],
            ]);

            return response()->json([
                'message' => 'Stock ajustado correctamente',
                'insumo' => $insumo->fresh()->load('almacen'),
            ]);
        });
    }

    // ==========================================
    // KARDEX AUDITABLE
    // ==========================================
    public function getKardex(Request $request): JsonResponse
    {
        $query = KardexMovimientoModel::with(['insumo', 'almacen'])->orderByDesc('id');

        if ($request->filled('insumo_id')) {
            $query->where('insumo_id', $request->insumo_id);
        }

        if ($request->filled('almacen_id')) {
            $query->where('almacen_id', $request->almacen_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $movimientos = $query->limit(100)->get();
        return response()->json($movimientos);
    }
}