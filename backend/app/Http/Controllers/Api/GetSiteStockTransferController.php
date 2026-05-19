<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ValidatesTransferSiteAccess;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class GetSiteStockTransferController extends Controller
{
    use ValidatesTransferSiteAccess;

    public function __invoke(Request $request, int $transferId): JsonResponse
    {
        $row = DB::table('site_stock_transfers as t')
            ->join('sites as sf', 'sf.id', '=', 't.from_site_id')
            ->join('sites as st', 'st.id', '=', 't.to_site_id')
            ->where('t.id', $transferId)
            ->first([
                't.id',
                't.from_site_id',
                't.to_site_id',
                'sf.name as from_site_name',
                'sf.code as from_site_code',
                'st.name as to_site_name',
                'st.code as to_site_code',
                't.document_ref',
                't.notes',
                't.transferred_at',
                't.created_by',
                't.created_at',
            ]);

        if (! $row) {
            return response()->json(['message' => 'Traspaso no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $fromId = (int) $row->from_site_id;
        $toId = (int) $row->to_site_id;
        if (! $this->userCanAccessSite($request, $fromId) && ! $this->userCanAccessSite($request, $toId)) {
            return response()->json(['message' => 'No autorizado.'], Response::HTTP_FORBIDDEN);
        }

        $lines = DB::table('site_stock_transfer_lines as l')
            ->join('products as p', 'p.id', '=', 'l.product_id')
            ->where('l.site_stock_transfer_id', $transferId)
            ->orderBy('l.id')
            ->get([
                'l.id',
                'l.product_id',
                'p.sku',
                'p.name as product_name',
                'l.quantity',
            ]);

        return response()->json([
            'data' => [
                'transfer' => $row,
                'lines' => $lines,
            ],
        ]);
    }
}
