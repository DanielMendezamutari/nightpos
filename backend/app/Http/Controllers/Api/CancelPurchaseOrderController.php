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

final class CancelPurchaseOrderController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $purchaseOrderId, Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'Sin sucursal.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = DB::table('purchase_orders')
            ->where('id', $purchaseOrderId)
            ->where('site_id', $siteId)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Compra no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        if (($order->status ?? 'received') === 'cancelled') {
            return response()->json(['message' => 'Esta compra ya está anulada.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $lines = DB::table('purchase_order_lines')
            ->where('purchase_order_id', $purchaseOrderId)
            ->get();

        if ($lines->isEmpty()) {
            DB::table('purchase_orders')->where('id', $purchaseOrderId)->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Compra anulada (sin líneas de stock).',
                'data' => ['purchase_order_id' => $purchaseOrderId, 'status' => 'cancelled'],
            ]);
        }

        foreach ($lines as $line) {
            $productId = (int) $line->product_id;
            $qty = (int) $line->quantity;

            $trackStock = (bool) DB::table('products')->where('id', $productId)->value('track_stock');
            if (! $trackStock || $qty < 1) {
                continue;
            }

            $current = (int) DB::table('site_product_stocks')
                ->where('site_id', $siteId)
                ->where('product_id', $productId)
                ->value('quantity');

            if ($current < $qty) {
                return response()->json([
                    'message' => 'No se puede anular: el stock actual ('.$current.') es insuficiente para revertir '
                        .$qty.' unidades de un producto de esta compra (posiblemente ya se vendió parte).',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        DB::transaction(function () use ($lines, $siteId, $purchaseOrderId, $request): void {
            foreach ($lines as $line) {
                $productId = (int) $line->product_id;
                $qty = (int) $line->quantity;

                $trackStock = (bool) DB::table('products')->where('id', $productId)->value('track_stock');
                if (! $trackStock || $qty < 1) {
                    continue;
                }

                DB::table('site_product_stocks')
                    ->where('site_id', $siteId)
                    ->where('product_id', $productId)
                    ->decrement('quantity', $qty);

                ProductStockAggregator::syncBaseStock($productId);

                DB::table('inventory_movements')->insert([
                    'product_id' => $productId,
                    'site_id' => $siteId,
                    'movement_type' => 'transfer_out',
                    'quantity' => $qty,
                    'unit_cost' => (int) $line->unit_cost,
                    'reference_type' => 'purchase_order_cancel',
                    'reference_id' => $purchaseOrderId,
                    'notes' => 'Anulación compra #'.$purchaseOrderId,
                    'moved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('purchase_orders')->where('id', $purchaseOrderId)->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()?->id,
                'updated_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Compra anulada. Stock revertido y movimientos registrados.',
            'data' => ['purchase_order_id' => $purchaseOrderId, 'status' => 'cancelled'],
        ]);
    }
}
