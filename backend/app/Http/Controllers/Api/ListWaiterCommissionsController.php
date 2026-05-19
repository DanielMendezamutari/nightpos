<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Concerns\ResolvesReportScope;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListWaiterCommissionsController extends Controller
{
    use ResolvesBranchSiteId;
    use ResolvesReportScope;

    public function __invoke(Request $request): JsonResponse
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
                'users.id as waiter_id',
                'users.name as waiter_name',
                DB::raw('SUM(waiter_commissions.base_amount) as billed_base'),
                DB::raw('SUM(waiter_commissions.commission_amount) as commission_total'),
            ])
            ->get()
            ->map(static function ($r): array {
                return [
                    'waiter_id' => (int) $r->waiter_id,
                    'waiter_name' => $r->waiter_name,
                    'billed_base' => (int) $r->billed_base,
                    'commission_total' => (int) $r->commission_total,
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $rows]);
    }
}

