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

final class ApplyRefillRecipeController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $recipeId, Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json(['message' => 'Debe seleccionar sucursal activa.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $request->validate([
            'batches' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $recipe = DB::table('product_refill_recipes')
            ->where('id', $recipeId)
            ->where('site_id', $siteId)
            ->where('is_active', true)
            ->first();

        if (! $recipe) {
            return response()->json(['message' => 'Receta de relleno no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $batches = (int) $payload['batches'];
        $sourceOut = (int) $recipe->source_units * $batches;
        $targetIn = (int) $recipe->target_units * $batches;

        $srcTrack = (bool) DB::table('products')->where('id', $recipe->source_product_id)->value('track_stock');
        $tgtTrack = (bool) DB::table('products')->where('id', $recipe->target_product_id)->value('track_stock');
        if (! $srcTrack || ! $tgtTrack) {
            return response()->json([
                'message' => 'No se puede aplicar relleno si alguno de los productos no lleva control de stock.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::transaction(function () use ($recipe, $payload, $siteId, $sourceOut, $targetIn): void {
            DB::table('site_product_stocks')
                ->where('site_id', $siteId)
                ->where('product_id', $recipe->source_product_id)
                ->decrement('quantity', $sourceOut);
            DB::table('site_product_stocks')
                ->where('site_id', $siteId)
                ->where('product_id', $recipe->target_product_id)
                ->increment('quantity', $targetIn);

            ProductStockAggregator::syncBaseStock((int) $recipe->source_product_id);
            ProductStockAggregator::syncBaseStock((int) $recipe->target_product_id);

            DB::table('inventory_movements')->insert([
                [
                    'product_id' => $recipe->source_product_id,
                    'site_id' => $siteId,
                    'movement_type' => 'transfer_out',
                    'quantity' => $sourceOut,
                    'unit_cost' => null,
                    'reference_type' => 'refill_recipe',
                    'reference_id' => $recipe->id,
                    'notes' => $payload['notes'] ?? 'Salida por relleno',
                    'moved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'product_id' => $recipe->target_product_id,
                    'site_id' => $siteId,
                    'movement_type' => 'transfer_in',
                    'quantity' => $targetIn,
                    'unit_cost' => null,
                    'reference_type' => 'refill_recipe',
                    'reference_id' => $recipe->id,
                    'notes' => $payload['notes'] ?? 'Ingreso por relleno',
                    'moved_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });

        return response()->json([
            'data' => [
                'recipe_id' => $recipeId,
                'batches' => $batches,
                'source_out' => $sourceOut,
                'target_in' => $targetIn,
            ],
        ], Response::HTTP_CREATED);
    }
}
