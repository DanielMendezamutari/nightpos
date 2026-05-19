<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Concerns\ResolvesReportScope;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportProductSalesPdfController extends Controller
{
    use ResolvesBranchSiteId;
    use ResolvesReportScope;

    public function __invoke(Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        $scope = $this->resolveReportScope($request, $siteId);

        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', 'paid')
            ->when($siteId, fn ($q) => $q->where('shift_turns.site_id', (int) $siteId));
        $this->applyReportScopeToOrders($query, $scope);

        $rows = $query->groupBy('products.id', 'products.sku', 'products.name')
            ->orderByDesc(DB::raw('SUM(order_items.subtotal)'))
            ->select([
                'products.sku',
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.subtotal) as total_amount'),
            ])
            ->get();

        $site = $siteId
            ? DB::table('sites')->where('id', $siteId)->first(['code', 'name'])
            : null;

        $pdf = Pdf::loadView('pdf.product-sales', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'rows' => $rows,
            'filterLabel' => $scope['filter_label'],
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        $suffix = $site?->code ?? 'todas';

        return $pdf->download('reporte-productos-vendidos-'.$suffix.'.pdf');
    }
}
