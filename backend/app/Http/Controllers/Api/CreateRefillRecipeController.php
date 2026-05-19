<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class CreateRefillRecipeController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json(['message' => 'Debe seleccionar sucursal activa.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $request->validate([
            'source_product_id' => ['required', 'integer', 'exists:products,id'],
            'target_product_id' => ['required', 'integer', 'exists:products,id', 'different:source_product_id'],
            'source_units' => ['required', 'integer', 'min:1'],
            'target_units' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $src = DB::table('products')->where('id', $payload['source_product_id'])->value('track_stock');
        $tgt = DB::table('products')->where('id', $payload['target_product_id'])->value('track_stock');
        if (! $src || ! $tgt) {
            return response()->json([
                'message' => 'Solo productos con control de stock pueden usarse en recetas de relleno.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $id = DB::table('product_refill_recipes')->insertGetId([
            'site_id' => $siteId,
            'source_product_id' => $payload['source_product_id'],
            'target_product_id' => $payload['target_product_id'],
            'source_units' => $payload['source_units'],
            'target_units' => $payload['target_units'],
            'is_active' => true,
            'notes' => $payload['notes'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => ['id' => $id],
        ], Response::HTTP_CREATED);
    }
}
