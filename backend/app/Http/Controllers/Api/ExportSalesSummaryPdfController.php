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

final class ExportSalesSummaryPdfController extends Controller
{
    use ResolvesBranchSiteId;
    use ResolvesReportScope;

    public function __invoke(Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        $scope = $this->resolveReportScope($request, $siteId);

        $byDayQuery = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->join('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->when($siteId, fn ($q) => $q->where('shift_turns.site_id', (int) $siteId));
        $this->applyReportScopeToPayments($byDayQuery, $scope);

        $byDay = $byDayQuery->groupBy(DB::raw('DATE(payments.paid_at)'))
            ->orderByDesc(DB::raw('DATE(payments.paid_at)'))
            ->select([
                DB::raw('DATE(payments.paid_at) as day'),
                DB::raw('COUNT(payments.id) as payments_count'),
                DB::raw('SUM(payments.amount) as total_amount'),
            ])
            ->get();

        $totalsQuery = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->join('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->when($siteId, fn ($q) => $q->where('shift_turns.site_id', (int) $siteId));
        $this->applyReportScopeToPayments($totalsQuery, $scope);

        $totalsRow = $totalsQuery->selectRaw('COUNT(payments.id) as payments_count, SUM(payments.amount) as total_amount')
            ->first();

        $site = $siteId
            ? DB::table('sites')->where('id', $siteId)->first(['code', 'name'])
            : null;

        $pdf = Pdf::loadView('pdf.sales-summary', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'byDay' => $byDay,
            'paymentsCount' => (int) ($totalsRow->payments_count ?? 0),
            'grandTotal' => (int) ($totalsRow->total_amount ?? 0),
            'filterLabel' => $scope['filter_label'],
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        $suffix = $site?->code ?? 'todas';

        return $pdf->download('reporte-ventas-'.$suffix.'.pdf');
    }
}
