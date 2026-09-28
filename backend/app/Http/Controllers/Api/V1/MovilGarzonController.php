<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Menu\Repositories\MenuRepositoryInterface;
use App\Domain\Pedido\Repositories\PedidoRepositoryInterface;
use App\Domain\Mesa\Repositories\MesaRepositoryInterface;
use App\Domain\Salon\Repositories\SalonRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\MesaModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Models\VisitaModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MovilGarzonController extends Controller
{
    public function __construct(
        private readonly SalonRepositoryInterface $salonRepository,
        private readonly MesaRepositoryInterface $mesaRepository,
        private readonly MenuRepositoryInterface $menuRepository,
        private readonly PedidoRepositoryInterface $pedidoRepository,
    ) {}

    /**
     * Login rápido para garzones por PIN (US-01 / WServiceRestaurant UsersController)
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string',
            'tenant_slug' => 'nullable|string',
            'branch_code' => 'nullable|string',
        ]);

        $tenantSlug = $validated['tenant_slug'] ?? 'casa-demo';
        $branchCode = $validated['branch_code'] ?? 'CENTRO';

        $tenant = TenantModel::where('slug', $tenantSlug)->where('is_active', true)->first();
        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Restaurante no encontrado'], 404);
        }

        $branch = BranchModel::where('tenant_id', $tenant->id)
            ->where('code', $branchCode)
            ->where('is_active', true)
            ->first();

        if (!$branch) {
            return response()->json(['success' => false, 'message' => 'Sucursal no encontrada'], 404);
        }

        $users = UserModel::where('tenant_id', $tenant->id)
            ->where('branch_id', $branch->id)
            ->where('is_active', true)
            ->get();

        $matchingUser = null;
        foreach ($users as $user) {
            if ($user->pin_hash && Hash::check($validated['pin'], $user->pin_hash)) {
                $matchingUser = $user;
                break;
            }
        }

        if (!$matchingUser) {
            return response()->json(['success' => false, 'message' => 'PIN incorrecto'], 401);
        }

        $token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($matchingUser);

        // Comprobar existencia de mesas libres (TableController.ExistFreeTables)
        $tieneMesasLibres = MesaModel::whereHas('salon', function ($q) use ($tenant, $branch) {
            $q->where('tenant_id', $tenant->id)->where('branch_id', $branch->id);
        })->whereDoesntHave('visitaActiva')->exists();

        return response()->json([
            'success' => true,
            'message' => 'Autenticación móvil exitosa',
            'data' => [
                'token' => $token,
                'tiene_mesas_libres' => $tieneMesasLibres,
                'user' => [
                    'id' => $matchingUser->id,
                    'name' => $matchingUser->name,
                    'username' => $matchingUser->username,
                    'role' => $matchingUser->role,
                    'branch_id' => $matchingUser->branch_id,
                    'branch_code' => $branch->code,
                ],
            ],
        ]);
    }

    /**
     * Listado móvil de mesas con regla de visibilidad de rol (TableController.AllTable)
     * Garzones: solo operan sus mesas ocupadas (SoloVeoMisMesas).
     * Admin/Cajero: operan todas.
     */
    public function getMesas(Request $request): JsonResponse
    {
        $user = $request->user();
        $salonId = $request->query('salon_id') ? (int) $request->query('salon_id') : null;
        $estadoFiltro = $request->query('estado'); // LIBRE, OCUPADA, PRECUENTA

        $query = MesaModel::with(['salon', 'visitaActiva.mesero']);

        if ($salonId) {
            $query->where('salon_id', $salonId);
        }

        $mesas = $query->orderBy('codigo')->get();

        $esAdminOCajero = in_array(strtolower($user->role ?? ''), ['admin', 'cajero']);

        $data = $mesas->map(function ($m) use ($user, $esAdminOCajero) {
            $visita = $m->visitaActiva;
            $estado = $visita ? $visita->estado : 'LIBRE';
            
            // Regla SoloVeoMisMesas
            $esMiMesa = $esAdminOCajero || ($visita && $visita->mesero_id === $user->id) || !$visita;

            return [
                'id' => $m->id,
                'salon_id' => $m->salon_id,
                'salon_nombre' => $m->salon?->nombre,
                'codigo' => $m->codigo,
                'nombre' => $m->nombre,
                'capacidad' => $m->capacidad,
                'forma' => $m->forma,
                'estado' => $estado,
                'es_mi_mesa' => (bool) $esMiMesa,
                'visita' => $visita ? [
                    'id' => $visita->id,
                    'mesero_id' => $visita->mesero_id,
                    'mesero_nombre' => $visita->mesero?->name,
                    'cliente_nombre' => $visita->cliente_nombre,
                    'personas' => $visita->personas,
                    'total' => (float) $visita->total,
                    'imprimio_cuenta' => (bool) $visita->imprimio_cuenta,
                ] : null,
            ];
        });

        if ($estadoFiltro) {
            $data = $data->filter(fn ($item) => strtoupper($item['estado']) === strtoupper($estadoFiltro))->values();
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Catálogo móvil para toma rápida de comanda
     */
    public function getMenu(Request $request): JsonResponse
    {
        $tenantSlug = $request->query('tenant_slug', 'casa-demo');
        $tenant = TenantModel::where('slug', $tenantSlug)->first();

        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Restaurante no encontrado'], 404);
        }

        $categorias = $this->menuRepository->getCategorias($tenant->id);
        $productos = $this->menuRepository->getProductos($tenant->id);

        return response()->json([
            'success' => true,
            'data' => [
                'categorias' => array_map(fn ($c) => [
                    'id' => $c->id(),
                    'nombre' => $c->nombre(),
                    'icono' => $c->icono(),
                ], $categorias),
                'productos' => array_map(fn ($p) => [
                    'id' => $p->id(),
                    'categoria_id' => $p->categoriaId(),
                    'codigo' => $p->codigo(),
                    'nombre' => $p->nombre(),
                    'precio' => $p->precio(),
                    'destino_impresion' => $p->destinoImpresion(),
                ], $productos),
            ],
        ]);
    }

    /**
     * Apertura de mesa desde el móvil
     */
    public function abrirMesa(Request $request, int $mesaId): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'personas' => 'nullable|integer|min:1|max:50',
            'cliente_nombre' => 'nullable|string|max:150',
            'notas' => 'nullable|string',
        ]);

        try {
            $visitaId = $this->mesaRepository->abrirMesa(
                mesaId: $mesaId,
                meseroId: (string) $user->id,
                personas: (int) ($validated['personas'] ?? 2),
                clienteNombre: $validated['cliente_nombre'] ?? null,
                notas: $validated['notas'] ?? null,
            );

            return response()->json([
                'success' => true,
                'message' => 'Mesa abierta exitosamente desde el móvil',
                'data' => ['visita_id' => $visitaId],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Envío de comanda inalámbrica desde el móvil (OrdersController.SendOrder)
     */
    public function enviarComanda(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mesa_id' => 'required',
            'visita_id' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.25',
            'items.*.observaciones' => 'nullable|string|max:255',
        ]);

        try {
            $mesaId = $validated['mesa_id'];
            $isSinMesa = str_starts_with((string)$mesaId, 'sin_mesa_') || (!empty($validated['visita_id']) && (int)$validated['visita_id'] > 0);

            if ($isSinMesa) {
                $visitaId = $validated['visita_id'] ?? (int)str_replace('sin_mesa_', '', (string)$mesaId);
                $resultado = $this->pedidoRepository->agregarItemsVisita($visitaId, $validated['items']);
                $descRef = "Comanda Móvil Sin Mesa #{$visitaId}";
            } else {
                $resultado = $this->pedidoRepository->agregarItemsMesa((int)$mesaId, $validated['items']);
                $descRef = "Comanda Móvil Mesa #{$mesaId}";
            }

            // Descuento automático de recetas/insumos
            foreach ($validated['items'] as $item) {
                \App\Http\Controllers\Api\V1\RecetaController::descontarInsumosPorVenta(
                    (int) $item['producto_id'],
                    (float) $item['cantidad'],
                    $descRef
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Comanda enviada exitosamente a cocina y bar',
                'data' => $resultado,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Ver consumo acumulado en mesa desde el móvil (OrdersController.VerOrder)
     */
    public function verCuenta(int $mesaId): JsonResponse
    {
        $mesa = MesaModel::with(['visitaActiva.detalles'])->find($mesaId);
        if (!$mesa) {
            return response()->json(['success' => false, 'message' => 'Mesa no encontrada'], 404);
        }

        $visita = $mesa->visitaActiva;
        if (!$visita) {
            return response()->json(['success' => false, 'message' => 'La mesa no tiene una cuenta activa'], 404);
        }

        $detalles = $visita->detalles->map(fn ($d) => [
            'id' => $d->id,
            'producto_nombre' => $d->producto_nombre,
            'cantidad' => (float) $d->cantidad,
            'precio_unitario' => (float) $d->precio_unitario,
            'subtotal' => (float) $d->subtotal,
            'observaciones' => $d->observaciones,
            'estado' => $d->estado,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'mesa_id' => $mesa->id,
                'mesa_nombre' => $mesa->nombre,
                'visita_id' => $visita->id,
                'cliente_nombre' => $visita->cliente_nombre,
                'personas' => $visita->personas,
                'total' => (float) $visita->total,
                'detalles' => $detalles,
            ],
        ]);
    }

    /**
     * Solicitar impresión de pre-cuenta desde el móvil (OrdersController.PrintOrder)
     */
    public function solicitarPrecuenta(int $mesaId): JsonResponse
    {
        try {
            $mesa = MesaModel::with('visitaActiva')->findOrFail($mesaId);
            $visita = $mesa->visitaActiva;

            if (!$visita) {
                return response()->json(['success' => false, 'message' => 'La mesa no tiene cuenta abierta'], 422);
            }

            $visita->estado = 'PRECUENTA';
            $visita->imprimio_cuenta = true;
            $visita->save();

            // Disparar impresión directa a impresora física CAJA si está en Windows
            try {
                app(\App\Http\Controllers\Api\V1\ImpresionController::class)->imprimirPrecuenta(request(), $mesaId);
            } catch (\Throwable $e) {
                // Silencioso para no romper la respuesta del garzón si la impresora física está apagada
            }

            return response()->json([
                'success' => true,
                'message' => 'Pre-cuenta solicitada e impresa en salón exitosamente',
                'data' => [
                    'mesa_id' => $mesa->id,
                    'visita_id' => $visita->id,
                    'estado' => 'PRECUENTA',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * =========================================================================
     * ENDPOINTS REVERSADOS PARA COMPATIBILIDAD CON APK (base.apk / cu.lex.android.bi)
     * =========================================================================
     */

    /**
     * Reversión de GET /users/login/{query}
     */
    public function loginLegacy(string $query): JsonResponse
    {
        $tenant = TenantModel::where('slug', 'casa-demo')->first() ?? TenantModel::first();
        if (!$tenant) {
            return response()->json(['loginSuccessful' => false, 'existFreeTables' => false], 404);
        }

        $users = UserModel::where('tenant_id', $tenant->id)->where('is_active', true)->get();
        $matchingUser = null;

        foreach ($users as $user) {
            if ($user->pin_hash && Hash::check($query, $user->pin_hash)) {
                $matchingUser = $user;
                break;
            }
        }

        if (!$matchingUser) {
            return response()->json(['loginSuccessful' => false, 'existFreeTables' => false], 401);
        }

        $token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($matchingUser);
        $tieneMesasLibres = MesaModel::whereDoesntHave('visitaActiva')->exists();

        return response()->json([
            'loginSuccessful' => true,
            'existFreeTables' => $tieneMesasLibres,
            'waiterId' => (string) $matchingUser->id,
            'waiterName' => $matchingUser->name,
            'token' => $token,
        ]);
    }

    /**
     * Reversión de POST /table/allTable
     */
    public function mesasLegacy(Request $request): JsonResponse
    {
        $mesas = MesaModel::with('visitaActiva')->orderBy('codigo')->get();

        $data = $mesas->map(function ($m) {
            $isBusy = $m->visitaActiva !== null;
            return [
                'id' => $m->id,
                'name' => $m->nombre ?? "Mesa {$m->codigo}",
                'state' => $isBusy ? 'TABLES_BUSY' : 'TABLES_FREE',
            ];
        });

        return response()->json($data);
    }

    /**
     * Reversión de GET /Assistants/All
     */
    public function asistentesLegacy(): JsonResponse
    {
        $asistentes = [
            ['mId' => 1, 'mName' => 'Sin Sal', 'mFullName' => 'Sin Sal'],
            ['mId' => 2, 'mName' => 'Sin Cebolla', 'mFullName' => 'Sin Cebolla'],
            ['mId' => 3, 'mName' => 'Picante Extra', 'mFullName' => 'Picante Extra'],
            ['mId' => 4, 'mName' => 'Término Medio', 'mFullName' => 'Carne Término Medio'],
            ['mId' => 5, 'mName' => 'Bien Cocido', 'mFullName' => 'Carne Bien Cocida'],
            ['mId' => 6, 'mName' => 'Con Hielo', 'mFullName' => 'Con Hielo'],
            ['mId' => 7, 'mName' => 'Sin Hielo', 'mFullName' => 'Sin Hielo'],
            ['mId' => 8, 'mName' => 'Para Llevar', 'mFullName' => 'Empaque Para Llevar'],
        ];

        return response()->json($asistentes);
    }

    /**
     * Reversión de POST /orders/SendOrder
     */
    public function sendOrderLegacy(Request $request): JsonResponse
    {
        $tableId = (int) $request->input('tableId');
        $cartItems = $request->input('cartItemList', []);
        $waiterId = $request->input('waiterId');

        $mesa = MesaModel::with('visitaActiva')->find($tableId);
        if (!$mesa) {
            return response()->json(['success' => false, 'message' => 'Mesa no encontrada'], 404);
        }

        // Si la mesa no tiene visita activa, abrirla
        if (!$mesa->visitaActiva) {
            $this->mesaRepository->abrirMesa(
                mesaId: $tableId,
                meseroId: (string) ($waiterId ?? 1),
                personas: 2,
                clienteNombre: null,
                notas: 'Comanda Móvil'
            );
        }

        $items = [];
        foreach ($cartItems as $item) {
            $prodId = $item['productId'] ?? ($item['mProduct']['mId'] ?? null);
            $count = (float) ($item['count'] ?? ($item['mCount'] ?? 1));
            $obsParts = [];
            if (!empty($item['assistants']) || !empty($item['mAssistants'])) {
                $obsParts[] = $item['assistants'] ?? $item['mAssistants'];
            }
            if (!empty($item['comment']) || !empty($item['mComment'])) {
                $obsParts[] = $item['comment'] ?? $item['mComment'];
            }

            $items[] = [
                'producto_id' => (int) $prodId,
                'cantidad' => $count,
                'observaciones' => implode(' | ', $obsParts),
            ];
        }

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Carrito vacío'], 422);
        }

        $resultado = $this->pedidoRepository->agregarItemsMesa($tableId, $items);

        // Descontar insumos
        foreach ($items as $item) {
            \App\Http\Controllers\Api\V1\RecetaController::descontarInsumosPorVenta(
                (int) $item['producto_id'],
                (float) $item['cantidad'],
                "Comanda Móvil Legacy Mesa #{$tableId}"
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Comanda procesada correctamente',
            'data' => $resultado,
        ]);
    }

    /**
     * Reversión de POST /orders/PrintOrder
     */
    public function printOrderLegacy(Request $request): JsonResponse
    {
        $tableId = (int) $request->input('tableId');
        return $this->solicitarPrecuenta($tableId);
    }

    /**
     * Reversión de POST /orders/OrderByTable y /orders/VerOrder
     */
    public function orderByTableLegacy(Request $request): JsonResponse
    {
        $tableId = (int) $request->input('tableId');
        $mesa = MesaModel::with('visitaActiva.detalles.producto')->find($tableId);

        if (!$mesa || !$mesa->visitaActiva) {
            return response()->json([]);
        }

        $orders = [];
        foreach ($mesa->visitaActiva->detalles as $det) {
            $orders[] = [
                'productId' => $det->producto_id,
                'productName' => $det->producto ? $det->producto->nombre : ($det->descripcion ?? 'Producto'),
                'count' => (int) $det->cantidad,
            ];
        }

        return response()->json($orders);
    }
}
