<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportValuedKardexPdfController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'No hay sucursal configurada.'], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rows = DB::table('products')
            ->leftJoin('site_product_stocks as sps', function ($join) use ($siteId): void {
                $join->on('sps.product_id', '=', 'products.id')
                    ->where('sps.site_id', '=', $siteId);
            })
            ->leftJoin('inventory_movements', function ($join) use ($siteId): void {
                $join->on('inventory_movements.product_id', '=', 'products.id')
                    ->where('inventory_movements.site_id', '=', $siteId);
            })
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('shift_turns', function ($join) use ($siteId): void {
                $join->on('shift_turns.id', '=', 'orders.shift_turn_id')
                    ->where('shift_turns.site_id', '=', $siteId);
            })
            ->groupBy(['products.id', 'products.sku', 'products.name', 'products.purchase_price', 'products.track_stock'])
            ->select([
                'products.id',
                'products.sku',
                'products.name',
                'products.purchase_price',
                'products.track_stock',
                DB::raw('COALESCE(MAX(sps.quantity), 0) as branch_qty'),
                DB::raw("COALESCE(SUM(CASE WHEN inventory_movements.movement_type = 'transfer_in' THEN ABS(inventory_movements.quantity) WHEN inventory_movements.movement_type = 'adjustment' AND inventory_movements.quantity > 0 THEN inventory_movements.quantity ELSE 0 END), 0) as entradas_qty"),
                DB::raw("COALESCE(SUM(CASE WHEN inventory_movements.movement_type IN ('sale_out','transfer_out') THEN ABS(inventory_movements.quantity) WHEN inventory_movements.movement_type = 'adjustment' AND inventory_movements.quantity < 0 THEN ABS(inventory_movements.quantity) ELSE 0 END), 0) as salidas_qty"),
                DB::raw('COALESCE(MAX(inventory_movements.unit_cost), 0) as ref_unit_cost'),
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as sold_units'),
            ])
            ->orderBy('products.name')
            ->get()
            ->map(function ($r): array {
                $stock = (int) $r->branch_qty;
                $fromMovements = (int) $r->ref_unit_cost;
                $purchase = (int) $r->purchase_price;
                $track = (bool) $r->track_stock;
                $unitCost = $fromMovements > 0 ? $fromMovements : $purchase;

                return [
                    'sku' => $r->sku,
                    'name' => $r->name,
                    'entradas_qty' => (int) $r->entradas_qty,
                    'salidas_qty' => (int) $r->salidas_qty,
                    'sold_units' => (int) $r->sold_units,
                    'stock_actual' => $stock,
                    'track_stock' => $track,
                    'ref_unit_cost' => $unitCost,
                    'stock_valorizado' => $track ? $stock * $unitCost : 0,
                ];
            })
            ->all();

        $site = DB::table('sites')->where('id', $siteId)->first(['code', 'name']);

        $pdf = Pdf::loadView('pdf.valued-kardex', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'rows' => $rows,
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        return $pdf->download('kardex-valorizado-'.$site->code.'.pdf');
    }
}
