<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Concerns\ResolvesReportScope;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListSalesSummaryReportController extends Controller
{
    use ResolvesBranchSiteId;
    use ResolvesReportScope;

    public function __invoke(Request $request): JsonResponse
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
            ->get()
            ->map(static function ($r): array {
                return [
                    'day' => (string) $r->day,
                    'payments_count' => (int) $r->payments_count,
                    'total_amount' => (int) $r->total_amount,
                ];
            })
            ->values()
            ->all();

        $totalsQuery = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->join('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->when($siteId, fn ($q) => $q->where('shift_turns.site_id', (int) $siteId));
        $this->applyReportScopeToPayments($totalsQuery, $scope);

        $totalsRow = $totalsQuery->selectRaw('COUNT(payments.id) as payments_count, SUM(payments.amount) as total_amount')
            ->first();

        return response()->json([
            'data' => [
                'by_day' => $byDay,
                'totals' => [
                    'payments_count' => (int) ($totalsRow->payments_count ?? 0),
                    'total_amount' => (int) ($totalsRow->total_amount ?? 0),
                ],
            ],
        ]);
    }
}
