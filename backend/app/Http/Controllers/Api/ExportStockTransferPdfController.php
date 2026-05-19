<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ValidatesTransferSiteAccess;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportStockTransferPdfController extends Controller
{
    use ValidatesTransferSiteAccess;

    public function __invoke(Request $request, int $transferId): SymfonyResponse
    {
        $row = DB::table('site_stock_transfers as t')
            ->join('sites as sf', 'sf.id', '=', 't.from_site_id')
            ->join('sites as st', 'st.id', '=', 't.to_site_id')
            ->leftJoin('users as u', 'u.id', '=', 't.created_by')
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
                't.created_at',
                'u.name as created_by_name',
            ]);

        if (! $row) {
            return response()->json(['message' => 'Traspaso no encontrado.'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        $fromId = (int) $row->from_site_id;
        $toId = (int) $row->to_site_id;
        if (! $this->userCanAccessSite($request, $fromId) && ! $this->userCanAccessSite($request, $toId)) {
            return response()->json(['message' => 'No autorizado.'], SymfonyResponse::HTTP_FORBIDDEN);
        }

        $lines = DB::table('site_stock_transfer_lines as l')
            ->join('products as p', 'p.id', '=', 'l.product_id')
            ->where('l.site_stock_transfer_id', $transferId)
            ->orderBy('l.id')
            ->get(['p.sku', 'p.name as product_name', 'l.quantity']);

        $pdf = Pdf::loadView('pdf.stock-transfer', [
            'appName' => config('app.name', 'NightPOS'),
            'transfer' => $row,
            'lines' => $lines,
            'transferredAt' => $row->transferred_at
                ? Carbon::parse((string) $row->transferred_at)->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i')
                : '—',
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        $fn = 'traspaso-'.$transferId.'.pdf';

        return $pdf->download($fn);
    }
}
