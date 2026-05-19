<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Concerns\ResolvesReportScope;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CompanionRankingReportController extends Controller
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
            ->join('companions', 'companions.id', '=', 'order_items.companion_id')
            ->whereNotNull('order_items.companion_id')
            ->where('orders.status', 'paid')
            ->when($siteId, fn ($q) => $q->where('shift_turns.site_id', (int) $siteId));
        $this->applyReportScopeToOrders($query, $scope);

        $ranking = $query->groupBy('companions.id', 'companions.stage_name')
            ->selectRaw('companions.id as companion_id, companions.stage_name, SUM(order_items.quantity) as drinks_count, SUM(order_items.subtotal) as total_generated')
            ->orderByDesc('total_generated');

        return response()->json([
            'data' => $ranking->get(),
        ]);
    }
}
