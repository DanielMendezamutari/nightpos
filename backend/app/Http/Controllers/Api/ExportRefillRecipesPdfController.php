<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportRefillRecipesPdfController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json(['message' => 'Debe seleccionar sucursal activa.'], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rows = DB::table('product_refill_recipes as r')
            ->join('products as p_source', 'p_source.id', '=', 'r.source_product_id')
            ->join('products as p_target', 'p_target.id', '=', 'r.target_product_id')
            ->where('r.site_id', $siteId)
            ->orderByDesc('r.id')
            ->get([
                'r.id',
                'r.source_units',
                'r.target_units',
                'r.is_active',
                'r.notes',
                'p_source.name as source_name',
                'p_target.name as target_name',
            ]);

        $site = DB::table('sites')->where('id', $siteId)->first(['code', 'name']);

        $pdf = Pdf::loadView('pdf.refill-recipes', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'rows' => $rows,
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        return $pdf->download('recetas-relleno-'.$site->code.'.pdf');
    }
}
