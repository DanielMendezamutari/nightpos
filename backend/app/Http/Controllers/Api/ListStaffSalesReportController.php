<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Concerns\ResolvesReportScope;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListStaffSalesReportController extends Controller
{
    use ResolvesBranchSiteId;
    use ResolvesReportScope;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        $scope = $this->resolveReportScope($request, $siteId);

        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->join('users', 'users.id', '=', 'order_items.waiter_user_id')
            ->where('orders.status', 'paid')
            ->when($siteId, fn ($q) => $q->where('shift_turns.site_id', (int) $siteId));
        $this->applyReportScopeToOrders($query, $scope);

        $rows = $query->groupBy('users.id', 'users.name')
            ->orderByDesc(DB::raw('SUM(order_items.subtotal)'))
            ->select([
                'users.id as user_id',
                'users.name as staff_name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.subtotal) as total_amount'),
            ])
            ->get()
            ->map(static function ($r): array {
                return [
                    'user_id' => (int) $r->user_id,
                    'staff_name' => $r->staff_name,
                    'quantity_sold' => (int) $r->quantity_sold,
                    'total_amount' => (int) $r->total_amount,
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $rows]);
    }
}
