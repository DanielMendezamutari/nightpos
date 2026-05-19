<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Concerns\ValidatesTransferSiteAccess;
use App\Http\Controllers\Controller;
use App\Support\ProductStockAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class CreateSiteStockTransferController extends Controller
{
    use ResolvesBranchSiteId;
    use ValidatesTransferSiteAccess;

    public function __invoke(Request $request): JsonResponse
    {
        $fromSiteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $fromSiteId) {
            return response()->json(['message' => 'No hay sucursal de origen.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $this->userCanAccessSite($request, $fromSiteId) || ! $this->userOperatesFromResolvedBranch($request, $fromSiteId)) {
            return response()->json(['message' => 'No tenes acceso a la sucursal de origen.'], Response::HTTP_FORBIDDEN);
        }

        $payload = $request->validate([
            'to_site_id' => ['required', 'integer', 'exists:sites,id'],
            'document_ref' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string', 'max:400'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $toSiteId = (int) $payload['to_site_id'];
        if ($toSiteId === $fromSiteId) {
            return response()->json(['message' => 'La sucursal destino debe ser distinta del origen.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! $this->userCanAccessSite($request, $toSiteId)) {
            return response()->json(['message' => 'No tenes acceso a la sucursal destino.'], Response::HTTP_FORBIDDEN);
        }

        $merged = [];
        foreach ($payload['lines'] as $line) {
            $pid = (int) $line['product_id'];
            $qty = (int) $line['quantity'];
            $merged[$pid] = ($merged[$pid] ?? 0) + $qty;
        }

        $hasTracked = false;
        foreach (array_keys($merged) as $productId) {
            if ((bool) DB::table('products')->where('id', $productId)->value('track_stock')) {
                $hasTracked = true;
                break;
            }
        }
        if (! $hasTracked) {
            return response()->json([
                'message' => 'Al menos un producto debe tener control de stock para el traspaso.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fromSiteName = (string) DB::table('sites')->where('id', $fromSiteId)->value('name');
        $toSiteName = (string) DB::table('sites')->where('id', $toSiteId)->value('name');

        foreach ($merged as $productId => $qty) {
            $trackStock = (bool) DB::table('products')->where('id', $productId)->value('track_stock');
            if (! $trackStock) {
                continue;
            }
            $available = (int) DB::table('site_product_stocks')
                ->where('site_id', $fromSiteId)
                ->where('product_id', $productId)
                ->value('quantity');
            if ($available < $qty) {
                return response()->json([
                    'message' => 'Stock insuficiente en origen para un producto del traspaso.',
                    'product_id' => $productId,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $transferredAt = now();
        $userId = $request->user()?->id;

        try {
            $transferId = DB::transaction(function () use ($merged, $fromSiteId, $toSiteId, $payload, $transferredAt, $userId, $fromSiteName, $toSiteName): int {
            $transferId = DB::table('site_stock_transfers')->insertGetId([
                'from_site_id' => $fromSiteId,
                'to_site_id' => $toSiteId,
                'document_ref' => $payload['document_ref'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'transferred_at' => $transferredAt,
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($merged as $productId => $qty) {
                $trackStock = (bool) DB::table('products')->where('id', $productId)->value('track_stock');
                if (! $trackStock) {
                    continue;
                }

                $lineId = DB::table('site_stock_transfer_lines')->insertGetId([
                    'site_stock_transfer_id' => $transferId,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $decremented = DB::table('site_product_stocks')
                    ->where('site_id', $fromSiteId)
                    ->where('product_id', $productId)
                    ->where('quantity', '>=', $qty)
                    ->decrement('quantity', $qty);
                if ($decremented === 0) {
                    throw new \RuntimeException('Stock insuficiente en origen.');
                }

                $unitCost = (int) DB::table('products')->where('id', $productId)->value('purchase_price');

                $updated = DB::table('site_product_stocks')
                    ->where('site_id', $toSiteId)
                    ->where('product_id', $productId)
                    ->increment('quantity', $qty);

                if (! $updated) {
                    DB::table('site_product_stocks')->insert([
                        'site_id' => $toSiteId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                ProductStockAggregator::syncBaseStock($productId);

                DB::table('inventory_movements')->insert([
                    'product_id' => $productId,
                    'site_id' => $fromSiteId,
                    'movement_type' => 'transfer_out',
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'reference_type' => 'site_stock_transfer_line',
                    'reference_id' => $lineId,
                    'notes' => 'Traspaso a '.$toSiteName,
                    'moved_at' => $transferredAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('inventory_movements')->insert([
                    'product_id' => $productId,
                    'site_id' => $toSiteId,
                    'movement_type' => 'transfer_in',
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'reference_type' => 'site_stock_transfer_line',
                    'reference_id' => $lineId,
                    'notes' => 'Traspaso desde '.$fromSiteName,
                    'moved_at' => $transferredAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $transferId;
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'data' => [
                'site_stock_transfer_id' => $transferId,
            ],
        ], Response::HTTP_CREATED);
    }
}
