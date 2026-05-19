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

final class ExportWaiterCommissionsPdfController extends Controller
{
    use ResolvesBranchSiteId;
    use ResolvesReportScope;

    public function __invoke(Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        $scope = $this->resolveReportScope($request, $siteId);

        $query = DB::table('waiter_commissions')
            ->join('users', 'users.id', '=', 'waiter_commissions.waiter_user_id')
            ->join('payments', 'payments.id', '=', 'waiter_commissions.payment_id')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->join('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->when($siteId, fn ($q) => $q->where('shift_turns.site_id', (int) $siteId));
        $this->applyReportScopeToPayments($query, $scope);

        $rows = $query->groupBy(['users.id', 'users.name'])
            ->orderByDesc(DB::raw('SUM(waiter_commissions.commission_amount)'))
            ->select([
                'users.name as waiter_name',
                DB::raw('SUM(waiter_commissions.base_amount) as billed_base'),
                DB::raw('SUM(waiter_commissions.commission_amount) as commission_total'),
            ])
            ->get();

        $site = $siteId
            ? DB::table('sites')->where('id', $siteId)->first(['code', 'name'])
            : null;

        $pdf = Pdf::loadView('pdf.waiter-commissions', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'rows' => $rows,
            'filterLabel' => $scope['filter_label'],
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        $suffix = $site?->code ?? 'todas';

        return $pdf->download('reporte-comisiones-mozos-'.$suffix.'.pdf');
    }
}
