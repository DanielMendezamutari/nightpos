<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListPosOrdersController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);

        $rows = DB::table('orders')
            ->join('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->join('customer_sessions', 'customer_sessions.id', '=', 'orders.customer_session_id')
            ->leftJoin('order_items', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['pending', 'served'])
            ->when($siteId, fn ($q) => $q->where('shift_turns.site_id', (int) $siteId))
            ->groupBy([
                'orders.id',
                'orders.status',
                'orders.shift_turn_id',
                'orders.customer_session_id',
                'orders.waiter_user_id',
                'orders.ordered_at',
                'customer_sessions.table_code',
                'customer_sessions.zone_code',
            ])
            ->orderByDesc('orders.id')
            ->select([
                'orders.id',
                'orders.status',
                'orders.shift_turn_id',
                'orders.customer_session_id',
                'orders.waiter_user_id',
                'orders.ordered_at',
                'customer_sessions.table_code',
                'customer_sessions.zone_code',
                DB::raw('COALESCE(SUM(order_items.subtotal),0) as subtotal'),
            ])
            ->get()
            ->map(static function ($r): array {
                return [
                    'id' => (int) $r->id,
                    'status' => $r->status,
                    'shift_turn_id' => (int) $r->shift_turn_id,
                    'customer_session_id' => (int) $r->customer_session_id,
                    'waiter_user_id' => (int) $r->waiter_user_id,
                    'table_code' => $r->table_code,
                    'zone_code' => $r->zone_code,
                    'ordered_at' => $r->ordered_at,
                    'subtotal' => (int) $r->subtotal,
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $rows]);
    }
}

