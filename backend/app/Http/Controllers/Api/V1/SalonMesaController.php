<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Mesa\Repositories\MesaRepositoryInterface;
use App\Domain\Salon\Repositories\SalonRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SalonMesaController extends Controller
{
    public function __construct(
        private readonly SalonRepositoryInterface $salonRepository,
        private readonly MesaRepositoryInterface $mesaRepository,
    ) {}

    public function getSalones(Request $request): JsonResponse
    {
        $tenantSlug = $request->query('tenant_slug', 'casa-demo');
        $branchCode = $request->query('branch_code', 'CENTRO');

        $tenant = TenantModel::where('slug', $tenantSlug)->first();
        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Restaurante no encontrado'], 404);
        }

        $branch = BranchModel::where('tenant_id', $tenant->id)->where('code', $branchCode)->first();
        if (!$branch) {
            return response()->json(['success' => false, 'message' => 'Sucursal no encontrada'], 404);
        }

        $salones = $this->salonRepository->getSalonesByBranch($tenant->id, $branch->id);

        $data = array_map(fn ($s) => [
            'id' => $s->id(),
            'codigo' => $s->codigo(),
            'nombre' => $s->nombre(),
            'orden' => $s->orden(),
            'total_mesas' => $s->totalMesas(),
            'mesas_ocupadas' => $s->mesasOcupadas(),
            'porcentaje_ocupacion' => $s->porcentajeOcupacion(),
        ], $salones);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getMesasBySalon(int $salonId): JsonResponse
    {
        $mesas = $this->mesaRepository->getMesasBySalon($salonId);

        $data = array_map(fn ($m) => [
            'id' => $m->id(),
            'salon_id' => $m->salonId(),
            'codigo' => $m->codigo(),
            'nombre' => $m->nombre(),
            'capacidad' => $m->capacidad(),
            'posicion_x' => $m->posicionX(),
            'posicion_y' => $m->posicionY(),
            'ancho' => $m->ancho(),
            'alto' => $m->alto(),
            'forma' => $m->forma(),
            'estado' => $m->estado()->value,
            'estado_label' => $m->estado()->label(),
            'estado_color' => $m->estado()->color(),
            'visita_id' => $m->visitaId(),
            'mesero_nombre' => $m->meseroNombre(),
            'cliente_nombre' => $m->clienteNombre(),
            'personas' => $m->personas(),
            'total_consumo' => $m->totalConsumo(),
            'minutos_abierta' => $m->minutosAbierta(),
            'imprimio_cuenta' => $m->imprimioCuenta(),
        ], $mesas);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getMesaDetails(int $mesaId): JsonResponse
    {
        $mesa = MesaModel::with(['salon', 'visitaActiva.mesero', 'visitaActiva.detalles'])->find($mesaId);
        if (!$mesa) {
            return response()->json(['success' => false, 'message' => 'Mesa no encontrada'], 404);
        }

        $visita = $mesa->visitaActiva;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $mesa->id,
                'codigo' => $mesa->codigo,
                'nombre' => $mesa->nombre,
                'salon_nombre' => $mesa->salon?->nombre,
                'capacidad' => $mesa->capacidad,
                'estado' => $visita ? $visita->estado : 'LIBRE',
                'visita' => $visita ? [
                    'id' => $visita->id,
                    'mesero_id' => $visita->mesero_id,
                    'mesero_nombre' => $visita->mesero?->name,
                    'cliente_nombre' => $visita->cliente_nombre,
                    'personas' => $visita->personas,
                    'estado' => $visita->estado,
                    'imprimio_cuenta' => $visita->imprimio_cuenta,
                    'fecha_apertura' => $visita->fecha_apertura?->toIso8601String(),
                    'total' => (float) $visita->total,
                    'notas' => $visita->notas,
                    'detalles' => $visita->detalles->map(fn ($d) => [
                        'id' => $d->id,
                        'producto_id' => $d->producto_id,
                        'producto_nombre' => $d->producto_nombre,
                        'cantidad' => (float) $d->cantidad,
                        'precio_unitario' => (float) $d->precio_unitario,
                        'subtotal' => (float) $d->subtotal,
                        'observaciones' => $d->observaciones,
                        'estado' => $d->estado,
                    ]),
                ] : null,
            ],
        ]);
    }

    public function abrirMesa(Request $request, int $mesaId): JsonResponse
    {
        $validated = $request->validate([
            'mesero_id' => 'required|string|exists:users,id',
            'personas' => 'nullable|integer|min:1|max:50',
            'cliente_nombre' => 'nullable|string|max:150',
            'notas' => 'nullable|string',
        ]);

        try {
            $visitaId = $this->mesaRepository->abrirMesa(
                mesaId: $mesaId,
                meseroId: (string) $validated['mesero_id'],
                personas: (int) ($validated['personas'] ?? 2),
                clienteNombre: $validated['cliente_nombre'] ?? null,
                notas: $validated['notas'] ?? null,
            );

            return response()->json([
                'success' => true,
                'message' => 'Mesa abierta exitosamente',
                'data' => ['visita_id' => $visitaId],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cambiarMesa(Request $request, int $mesaId): JsonResponse
    {
        $validated = $request->validate([
            'mesa_destino_id' => 'required|integer|exists:mesas,id|different:'.$mesaId,
        ]);

        try {
            $this->mesaRepository->cambiarMesa($mesaId, (int) $validated['mesa_destino_id']);

            return response()->json([
                'success' => true,
                'message' => 'Mesa movida exitosamente',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function solicitarPrecuenta(int $mesaId): JsonResponse
    {
        try {
            $this->mesaRepository->solicitarPrecuenta($mesaId);

            return response()->json([
                'success' => true,
                'message' => 'Pre-cuenta solicitada e impresa',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function liberarMesa(int $mesaId): JsonResponse
    {
        try {
            $this->mesaRepository->liberarMesa($mesaId);

            return response()->json([
                'success' => true,
                'message' => 'Mesa liberada correctamente',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}