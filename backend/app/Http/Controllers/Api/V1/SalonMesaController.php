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

    public function storeSalon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:20',
            'impresora_cuenta' => 'nullable|string|max:100',
            'impresora_factura' => 'nullable|string|max:100',
            'orden' => 'nullable|integer',
        ]);

        $tenantSlug = $request->query('tenant_slug', 'casa-demo');
        $branchCode = $request->query('branch_code', 'CENTRO');

        $tenant = TenantModel::where('slug', $tenantSlug)->firstOrFail();
        $branch = BranchModel::where('tenant_id', $tenant->id)->where('code', $branchCode)->firstOrFail();

        $salon = SalonModel::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'codigo' => $validated['codigo'] ?? ('SAL-' . strtoupper(substr(uniqid(), -4))),
            'nombre' => $validated['nombre'],
            'impresora_cuenta' => $validated['impresora_cuenta'] ?? 'Termica-Salon',
            'impresora_factura' => $validated['impresora_factura'] ?? 'Termica-Caja-Central',
            'orden' => (int) ($validated['orden'] ?? (SalonModel::where('branch_id', $branch->id)->max('orden') + 1)),
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Salón creado exitosamente',
            'data' => $salon,
        ], 201);
    }

    public function updateSalon(Request $request, int $salonId): JsonResponse
    {
        $salon = SalonModel::findOrFail($salonId);
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'codigo' => 'nullable|string|max:20',
            'impresora_cuenta' => 'nullable|string|max:100',
            'impresora_factura' => 'nullable|string|max:100',
            'orden' => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $salon->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Salón actualizado exitosamente',
            'data' => $salon,
        ]);
    }

    public function storeMesa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'salon_id' => 'required|integer|exists:salones,id',
            'codigo' => 'nullable|string|max:20',
            'nombre' => 'required|string|max:100',
            'capacidad' => 'nullable|integer|min:1|max:50',
            'forma' => 'nullable|string|in:cuadrada,redonda,rectangular',
            'posicion_x' => 'nullable|integer',
            'posicion_y' => 'nullable|integer',
            'ancho' => 'nullable|integer',
            'alto' => 'nullable|integer',
        ]);

        $codigo = $validated['codigo'] ?? (string) (MesaModel::where('salon_id', $validated['salon_id'])->count() + 1);

        $mesa = MesaModel::create([
            'salon_id' => $validated['salon_id'],
            'codigo' => $codigo,
            'nombre' => $validated['nombre'],
            'capacidad' => (int) ($validated['capacidad'] ?? 4),
            'forma' => $validated['forma'] ?? 'cuadrada',
            'posicion_x' => (int) ($validated['posicion_x'] ?? 20),
            'posicion_y' => (int) ($validated['posicion_y'] ?? 20),
            'ancho' => (int) ($validated['ancho'] ?? 110),
            'alto' => (int) ($validated['alto'] ?? 110),
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mesa creada exitosamente',
            'data' => $mesa,
        ], 201);
    }

    public function updateMesa(Request $request, int $mesaId): JsonResponse
    {
        $mesa = MesaModel::findOrFail($mesaId);
        $validated = $request->validate([
            'salon_id' => 'sometimes|integer|exists:salones,id',
            'codigo' => 'nullable|string|max:20',
            'nombre' => 'sometimes|string|max:100',
            'capacidad' => 'nullable|integer|min:1|max:50',
            'forma' => 'nullable|string|in:cuadrada,redonda,rectangular',
            'posicion_x' => 'nullable|integer',
            'posicion_y' => 'nullable|integer',
            'ancho' => 'nullable|integer',
            'alto' => 'nullable|integer',
            'activo' => 'nullable|boolean',
        ]);

        $mesa->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mesa actualizada exitosamente',
            'data' => $mesa,
        ]);
    }

    public function deleteMesa(int $mesaId): JsonResponse
    {
        $mesa = MesaModel::findOrFail($mesaId);
        
        // Verificar que no tenga visita activa
        if ($mesa->visitaActiva) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una mesa que tiene una cuenta o visita activa.',
            ], 422);
        }

        $mesa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mesa eliminada exitosamente',
        ]);
    }
}
