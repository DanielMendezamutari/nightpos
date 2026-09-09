<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Menu\Repositories\MenuRepositoryInterface;
use App\Domain\Pedido\Repositories\PedidoRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ComandaController extends Controller
{
    public function __construct(
        private readonly MenuRepositoryInterface $menuRepository,
        private readonly PedidoRepositoryInterface $pedidoRepository,
    ) {}

    public function getCategorias(Request $request): JsonResponse
    {
        $tenantSlug = $request->query('tenant_slug', 'casa-demo');
        $tenant = TenantModel::where('slug', $tenantSlug)->first();

        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Restaurante no encontrado'], 404);
        }

        $categorias = $this->menuRepository->getCategorias($tenant->id);

        $data = array_map(fn ($c) => [
            'id' => $c->id(),
            'codigo' => $c->codigo(),
            'nombre' => $c->nombre(),
            'icono' => $c->icono(),
            'color' => $c->color(),
            'total_productos' => $c->totalProductos(),
        ], $categorias);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getProductos(Request $request): JsonResponse
    {
        $tenantSlug = $request->query('tenant_slug', 'casa-demo');
        $categoriaId = $request->query('categoria_id') ? (int) $request->query('categoria_id') : null;
        $busqueda = $request->query('busqueda');

        $tenant = TenantModel::where('slug', $tenantSlug)->first();
        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Restaurante no encontrado'], 404);
        }

        $productos = $this->menuRepository->getProductos($tenant->id, $categoriaId, $busqueda);

        $data = array_map(fn ($p) => [
            'id' => $p->id(),
            'categoria_id' => $p->categoriaId(),
            'categoria_nombre' => $p->categoriaNombre(),
            'codigo' => $p->codigo(),
            'nombre' => $p->nombre(),
            'descripcion' => $p->descripcion(),
            'precio' => $p->precio(),
            'tiempo_preparacion' => $p->tiempoPreparacion(),
            'destino_impresion' => $p->destinoImpresion(),
            'imagen' => $p->imagen(),
        ], $productos);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getObservacionesCocina(Request $request): JsonResponse
    {
        $tenantSlug = $request->query('tenant_slug', 'casa-demo');
        $tenant = TenantModel::where('slug', $tenantSlug)->first();

        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Restaurante no encontrado'], 404);
        }

        $observaciones = $this->menuRepository->getObservacionesCocina($tenant->id);

        return response()->json([
            'success' => true,
            'data' => $observaciones,
        ]);
    }

    public function agregarComanda(Request $request, int $mesaId): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.25',
            'items.*.observaciones' => 'nullable|string|max:255',
        ]);

        try {
            $resultado = $this->pedidoRepository->agregarItemsMesa($mesaId, $validated['items']);

            return response()->json([
                'success' => true,
                'message' => 'Comanda enviada a cocina exitosamente',
                'data' => $resultado,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function eliminarItem(int $detalleId): JsonResponse
    {
        try {
            $this->pedidoRepository->eliminarItemMesa($detalleId);

            return response()->json([
                'success' => true,
                'message' => 'Ítem eliminado de la comanda',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}