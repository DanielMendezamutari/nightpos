<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Support\ProductStockAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class RegisterManualInventoryMovementController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json(['message' => 'Debe seleccionar sucursal activa.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'movement_kind' => ['required', 'in:ingreso,salida,ajuste'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $trackStock = (bool) DB::table('products')->where('id', $payload['product_id'])->value('track_stock');
        if (! $trackStock) {
            return response()->json([
                'message' => 'Este producto no lleva control de stock: no se pueden registrar movimientos manuales.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($payload, $siteId): void {
            $kind = $payload['movement_kind'];
            $qty = (int) $payload['quantity'];

            $movementType = match ($kind) {
                'ingreso' => 'transfer_in',
                'salida' => 'transfer_out',
                default => 'adjustment',
            };
            $stockDelta = match ($kind) {
                'ingreso' => $qty,
                'salida' => -$qty,
                default => $qty,
            };

            DB::table('site_product_stocks')
                ->where('site_id', $siteId)
                ->where('product_id', $payload['product_id'])
                ->increment('quantity', $stockDelta);

            ProductStockAggregator::syncBaseStock((int) $payload['product_id']);

            DB::table('inventory_movements')->insert([
                'product_id' => $payload['product_id'],
                'site_id' => $siteId,
                'movement_type' => $movementType,
                'quantity' => $kind === 'ajuste' ? $stockDelta : $qty,
                'unit_cost' => $payload['unit_cost'] ?? null,
                'reference_type' => 'manual',
                'reference_id' => null,
                'notes' => $payload['notes'] ?? null,
                'moved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Movimiento registrado.'], Response::HTTP_CREATED);
    }
}
