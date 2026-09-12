<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\GastoModel;
use App\Infrastructure\Persistence\Eloquent\Models\MovimientoCajaModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\TipoGastoModel;
use App\Infrastructure\Persistence\Eloquent\Models\TurnoModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GastoController extends Controller
{
    /**
     * Asegura categorías por defecto de RestoTech
     */
    private function asegurarTiposPorDefecto(string $tenantId): void
    {
        if (TipoGastoModel::where('tenant_id', $tenantId)->count() === 0) {
            $defaults = [
                ['codigo' => 'MERCADO', 'nombre' => 'Compras Menores de Mercado', 'icono' => 'ri-shopping-basket-line'],
                ['codigo' => 'HIELO_BEB', 'nombre' => 'Hielo y Bebidas Urgentes', 'icono' => 'ri-cup-line'],
                ['codigo' => 'LIMPIEZA', 'nombre' => 'Limpieza y Suministros', 'icono' => 'ri-brush-line'],
                ['codigo' => 'MANTENIMIENTO', 'nombre' => 'Mantenimiento y Reparaciones', 'icono' => 'ri-tools-line'],
                ['codigo' => 'ADELANTO', 'nombre' => 'Adelanto de Sueldo / Personal', 'icono' => 'ri-user-shared-line'],
                ['codigo' => 'TRANSPORTE', 'nombre' => 'Fletes, Taxi y Transporte', 'icono' => 'ri-truck-line'],
                ['codigo' => 'SERVICIOS', 'nombre' => 'Servicios Básicos y Gas', 'icono' => 'ri-fire-line'],
                ['codigo' => 'RETIRO', 'nombre' => 'Retiro a Caja Fuerte / Administración', 'icono' => 'ri-safe-2-line'],
                ['codigo' => 'OTROS', 'nombre' => 'Otros Gastos Imprevistos', 'icono' => 'ri-more-line'],
            ];

            foreach ($defaults as $d) {
                TipoGastoModel::create([
                    'tenant_id' => $tenantId,
                    'codigo' => $d['codigo'],
                    'nombre' => $d['nombre'],
                    'icono' => $d['icono'],
                    'activo' => true,
                ]);
            }
        }
    }

    /**
     * Listar categorías de gasto
     */
    public function getTiposGastos(Request $request): JsonResponse
    {
        $tenant = TenantModel::first();
        $tenantId = $tenant ? $tenant->id : '';

        if ($tenantId) {
            $this->asegurarTiposPorDefecto($tenantId);
        }

        $tipos = TipoGastoModel::where('activo', true)
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tipos,
        ]);
    }

    /**
     * Crear nueva categoría de gasto
     */
    public function storeTipoGasto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:20',
            'descripcion' => 'nullable|string|max:255',
            'icono' => 'nullable|string|max:50',
        ]);

        $tenant = TenantModel::first();

        $tipo = TipoGastoModel::create([
            'tenant_id' => $tenant->id,
            'codigo' => $validated['codigo'] ?? strtoupper(substr($validated['nombre'], 0, 8)),
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'icono' => $validated['icono'] ?? 'ri-money-dollar-circle-line',
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Categoría de gasto creada exitosamente',
            'data' => $tipo,
        ], 201);
    }

    /**
     * Listar gastos del turno activo o con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $turnoId = $request->query('turno_id');

        $query = GastoModel::with(['tipoGasto', 'usuario', 'turno'])
            ->orderBy('created_at', 'desc');

        if ($turnoId) {
            $query->where('turno_id', $turnoId);
        } else {
            // Por defecto, si hay turno activo, mostrar los del turno activo
            $turnoActivo = TurnoModel::where('estado', 'ABIERTO')->first();
            if ($turnoActivo) {
                $query->where('turno_id', $turnoActivo->id);
            }
        }

        $gastos = $query->get();

        $activos = $gastos->where('estado', 'ACTIVO');
        $totalEfectivo = (float) $activos->where('forma_pago', 'EFECTIVO')->sum('monto');
        $totalOtros = (float) $activos->where('forma_pago', '!=', 'EFECTIVO')->sum('monto');
        $totalGastos = $totalEfectivo + $totalOtros;

        return response()->json([
            'success' => true,
            'data' => $gastos,
            'summary' => [
                'total_gastos' => round($totalGastos, 2),
                'total_efectivo' => round($totalEfectivo, 2),
                'total_otros' => round($totalOtros, 2),
                'cantidad' => $activos->count(),
            ],
        ]);
    }

    /**
     * Registrar un nuevo gasto de caja chica (frmGastos de RestoTech)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipo_gasto_id' => 'required|integer|exists:tipos_gastos,id',
            'monto' => 'required|numeric|min:0.50',
            'beneficiario' => 'required|string|max:150',
            'forma_pago' => 'nullable|string|in:EFECTIVO,QR,TRANSFERENCIA',
            'comprobante_nro' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $turno = TurnoModel::where('estado', 'ABIERTO')->first();

        if (!$turno) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un turno de caja abierto para registrar egresos.',
            ], 422);
        }

        $usuario = auth('api')->user() ?? UserModel::first();
        $formaPago = $validated['forma_pago'] ?? 'EFECTIVO';
        $monto = (float) $validated['monto'];

        return DB::transaction(function () use ($validated, $turno, $usuario, $formaPago, $monto) {
            $gasto = GastoModel::create([
                'tenant_id' => $turno->tenant_id,
                'branch_id' => $turno->branch_id,
                'turno_id' => $turno->id,
                'tipo_gasto_id' => $validated['tipo_gasto_id'],
                'usuario_id' => $usuario->id,
                'beneficiario' => $validated['beneficiario'],
                'monto' => $monto,
                'forma_pago' => $formaPago,
                'comprobante_nro' => $validated['comprobante_nro'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null,
                'estado' => 'ACTIVO',
            ]);

            // Si es en efectivo, se descuenta de caja chica actualizando el turno
            if ($formaPago === 'EFECTIVO') {
                $turno->total_gastos = (float) $turno->total_gastos + $monto;
                $turno->save();

                // Compatibilidad con movimientos_caja
                MovimientoCajaModel::create([
                    'tenant_id' => $turno->tenant_id,
                    'branch_id' => $turno->branch_id,
                    'turno_id' => $turno->id,
                    'usuario_id' => $usuario->id,
                    'tipo' => 'EGRESO_GASTO',
                    'monto' => $monto,
                    'motivo' => "Gasto #{$gasto->id}: {$gasto->beneficiario}",
                    'comprobante_nro' => $gasto->comprobante_nro,
                    'observaciones' => $gasto->observaciones,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Gasto registrado exitosamente en caja chica',
                'data' => $gasto->load('tipoGasto'),
                'turno_total_gastos' => (float) $turno->total_gastos,
            ], 201);
        });
    }

    /**
     * Anular un gasto
     */
    public function anular(Request $request, int $id): JsonResponse
    {
        $gasto = GastoModel::findOrFail($id);

        if ($gasto->estado === 'ANULADO') {
            return response()->json([
                'success' => false,
                'message' => 'El gasto ya se encuentra anulado.',
            ], 422);
        }

        return DB::transaction(function () use ($gasto) {
            $gasto->estado = 'ANULADO';
            $gasto->save();

            if ($gasto->forma_pago === 'EFECTIVO') {
                $turno = TurnoModel::find($gasto->turno_id);
                if ($turno) {
                    // Recalcular total_gastos activos del turno
                    $totalActivos = GastoModel::where('turno_id', $turno->id)
                        ->where('estado', 'ACTIVO')
                        ->where('forma_pago', 'EFECTIVO')
                        ->sum('monto');

                    $turno->total_gastos = (float) $totalActivos;
                    $turno->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Gasto anulado exitosamente',
                'data' => $gasto,
            ]);
        });
    }
}
