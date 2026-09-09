<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\ProveedorModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProveedorPagoModel;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProveedorModel::where('activo', true);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('nit', 'like', "%{$s}%")
                  ->orWhere('contacto', 'like', "%{$s}%");
            });
        }

        if ($request->boolean('solo_con_deuda')) {
            $query->where('saldo_deuda', '>', 0);
        }

        $proveedores = $query->orderBy('nombre')->get();
        return response()->json($proveedores);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'razon_social' => 'nullable|string|max:150',
            'nit' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:50',
            'celular' => 'nullable|string|max:50',
            'contacto' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:100',
            'banco' => 'nullable|string|max:100',
            'nro_cuenta' => 'nullable|string|max:50',
            'titular_cuenta' => 'nullable|string|max:150',
        ]);

        $proveedor = ProveedorModel::create($data);
        return response()->json($proveedor, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $proveedor = ProveedorModel::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'razon_social' => 'nullable|string|max:150',
            'nit' => 'nullable|string|max:30',
            'telefono' => 'nullable|string|max:50',
            'celular' => 'nullable|string|max:50',
            'contacto' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'correo' => 'nullable|email|max:100',
            'banco' => 'nullable|string|max:100',
            'nro_cuenta' => 'nullable|string|max:50',
            'titular_cuenta' => 'nullable|string|max:150',
            'activo' => 'boolean',
        ]);

        $proveedor->update($data);
        return response()->json($proveedor);
    }

    public function delete(int $id): JsonResponse
    {
        $proveedor = ProveedorModel::findOrFail($id);
        $proveedor->update(['activo' => false]);
        return response()->json(['message' => 'Proveedor desactivado correctamente']);
    }

    public function registrarPago(Request $request, int $id): JsonResponse
    {
        $proveedor = ProveedorModel::findOrFail($id);

        $data = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string|max:30',
            'referencia' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($proveedor, $data) {
            $turno = TurnoModel::where('estado', 'ABIERTO')->first();
            $monto = (float) $data['monto'];
            $saldoAnterior = (float) $proveedor->saldo_deuda;
            $saldoNuevo = max(0, $saldoAnterior - $monto);

            $pago = ProveedorPagoModel::create([
                'proveedor_id' => $proveedor->id,
                'turno_id' => $turno?->id,
                'monto' => $monto,
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo' => $saldoNuevo,
                'metodo_pago' => $data['metodo_pago'],
                'referencia' => $data['referencia'] ?? 'Abono a cuenta',
            ]);

            $proveedor->update(['saldo_deuda' => $saldoNuevo]);

            return response()->json([
                'message' => 'Pago a proveedor registrado exitosamente',
                'pago' => $pago,
                'proveedor' => $proveedor->fresh(),
            ]);
        });
    }
}