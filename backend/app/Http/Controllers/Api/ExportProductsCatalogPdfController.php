<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportProductsCatalogPdfController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'No hay sucursal configurada.'], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $siteIdForSales = (int) $siteId;
        $soldUnitsExpr = 'COALESCE(SUM(CASE WHEN shift_turns.site_id = '.$siteIdForSales.' THEN COALESCE(order_items.quantity, 0) ELSE 0 END), 0)';

        $rows = DB::table('products')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->leftJoin('site_product_stocks as sps', function ($join) use ($siteId): void {
                $join->on('sps.product_id', '=', 'products.id')
                    ->where('sps.site_id', '=', $siteId);
            })
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->groupBy([
                'products.id',
                'products.sku',
                'products.name',
                'products.category_id',
                'products.price_solo',
                'products.price_with_companion',
                'products.purchase_price',
                'products.stock_min',
                'products.stock_max',
                'products.track_stock',
                'products.purchase_units_per_box',
                'products.purchase_units_per_basket',
                'products.is_active',
                'product_categories.name',
            ])
            ->orderBy('products.name')
            ->select([
                'products.sku',
                'products.name',
                'product_categories.name as category_name',
                DB::raw('COALESCE(MAX(sps.quantity), 0) as stock_actual'),
                'products.price_solo',
                'products.price_with_companion',
                'products.purchase_price',
                'products.stock_min',
                'products.stock_max',
                'products.track_stock',
                'products.is_active',
                DB::raw($soldUnitsExpr.' as sold_units'),
            ])
            ->get();

        $site = DB::table('sites')->where('id', $siteId)->first(['code', 'name']);

        $pdf = Pdf::loadView('pdf.products-catalog', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'rows' => $rows,
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        return $pdf->download('catalogo-productos-'.$site->code.'.pdf');
    }
}
