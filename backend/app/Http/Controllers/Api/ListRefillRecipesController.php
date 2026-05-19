<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ListRefillRecipesController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json(['message' => 'Debe seleccionar sucursal activa.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rows = DB::table('product_refill_recipes as r')
            ->join('products as p_source', 'p_source.id', '=', 'r.source_product_id')
            ->join('products as p_target', 'p_target.id', '=', 'r.target_product_id')
            ->where('r.site_id', $siteId)
            ->orderByDesc('r.id')
            ->get([
                'r.id',
                'r.source_product_id',
                'r.target_product_id',
                'r.source_units',
                'r.target_units',
                'r.is_active',
                'r.notes',
                'p_source.name as source_name',
                'p_target.name as target_name',
            ])
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'source_product_id' => (int) $r->source_product_id,
                'source_name' => $r->source_name,
                'target_product_id' => (int) $r->target_product_id,
                'target_name' => $r->target_name,
                'source_units' => (int) $r->source_units,
                'target_units' => (int) $r->target_units,
                'is_active' => (bool) $r->is_active,
                'notes' => $r->notes,
            ])
            ->all();

        return response()->json(['data' => $rows]);
    }
}
