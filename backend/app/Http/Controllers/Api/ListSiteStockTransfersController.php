<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ListSiteStockTransfersController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'Sin sucursal.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rows = DB::table('site_stock_transfers as t')
            ->join('sites as sf', 'sf.id', '=', 't.from_site_id')
            ->join('sites as st', 'st.id', '=', 't.to_site_id')
            ->where(function ($q) use ($siteId): void {
                $q->where('t.from_site_id', $siteId)->orWhere('t.to_site_id', $siteId);
            })
            ->orderByDesc('t.transferred_at')
            ->orderByDesc('t.id')
            ->get([
                't.id',
                't.from_site_id',
                't.to_site_id',
                'sf.name as from_site_name',
                'st.name as to_site_name',
                't.document_ref',
                't.notes',
                't.transferred_at',
                't.created_at',
            ]);

        return response()->json(['data' => $rows]);
    }
}
