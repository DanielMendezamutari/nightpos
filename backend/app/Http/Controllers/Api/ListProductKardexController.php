<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ListProductKardexController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $productId, Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'No hay sucursal configurada.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $movements = DB::table('inventory_movements')
            ->where('product_id', $productId)
            ->where('site_id', $siteId)
            ->orderBy('moved_at')
            ->orderBy('id')
            ->get();

        $running = 0;
        $rows = $movements->map(function ($m) use (&$running): array {
            $quantity = (int) $m->quantity;
            $delta = match ($m->movement_type) {
                'sale_out', 'transfer_out' => -abs($quantity),
                'transfer_in' => abs($quantity),
                default => $quantity,
            };
            $running += $delta;

            return [
                'id' => (int) $m->id,
                'movement_type' => $m->movement_type,
                'quantity' => $quantity,
                'delta' => $delta,
                'running_stock' => $running,
                'unit_cost' => $m->unit_cost !== null ? (int) $m->unit_cost : null,
                'reference_type' => $m->reference_type,
                'reference_id' => $m->reference_id !== null ? (int) $m->reference_id : null,
                'notes' => $m->notes,
                'moved_at' => $m->moved_at,
            ];
        })->all();

        $product = DB::table('products')->where('id', $productId)->first(['id', 'name', 'sku']);
        $qty = (int) DB::table('site_product_stocks')
            ->where('site_id', $siteId)
            ->where('product_id', $productId)
            ->value('quantity');

        return response()->json([
            'data' => [
                'site_id' => $siteId,
                'product' => [
                    'id' => (int) $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'stock_actual' => $qty,
                ],
                'movements' => $rows,
            ],
        ]);
    }
}
